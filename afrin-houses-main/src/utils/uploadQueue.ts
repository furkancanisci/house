/**
 * Upload Queue Manager
 * Manages batch file uploads with rate limiting and queue processing
 */

export interface QueueItem {
  id: string;
  file: File;
  onProgress?: (progress: number) => void;
  onComplete?: (result: any) => void;
  onError?: (error: Error) => void;
  priority: 'high' | 'medium' | 'low';
  retryCount: number;
  maxRetries: number;
}

export interface QueueOptions {
  maxConcurrent: number;
  delayBetweenUploads: number;
  maxRetries: number;
  onQueueProgress?: (completed: number, total: number) => void;
  onRateLimitDetected?: (retryAfter: number) => void;
}

export class UploadQueue {
  private queue: QueueItem[] = [];
  private processing: QueueItem[] = [];
  private completed: QueueItem[] = [];
  private failed: QueueItem[] = [];
  private options: QueueOptions;
  private isProcessing = false;
  private lastUploadTime = 0;

  constructor(options: Partial<QueueOptions> = {}) {
    this.options = {
      maxConcurrent: 2,
      delayBetweenUploads: 500, // 500ms between uploads
      maxRetries: 3,
      ...options
    };
  }

  /**
   * Add files to the upload queue
   */
  addFiles(
    files: File[],
    uploadFn: (file: File, onProgress?: (progress: number) => void) => Promise<any>,
    options: Partial<Pick<QueueItem, 'onProgress' | 'onComplete' | 'onError' | 'priority'>> = {}
  ): string[] {
    const ids: string[] = [];
    
    files.forEach((file, index) => {
      const id = `${Date.now()}-${index}-${Math.random().toString(36).substr(2, 9)}`;
      
      const queueItem: QueueItem = {
        id,
        file,
        priority: options.priority || 'medium',
        retryCount: 0,
        maxRetries: this.options.maxRetries,
        onProgress: options.onProgress,
        onComplete: options.onComplete,
        onError: options.onError
      };
      
      this.queue.push(queueItem);
      ids.push(id);
    });
    
    // Sort queue by priority
    this.sortQueue();
    
    // Start processing if not already running
    if (!this.isProcessing) {
      this.processQueue(uploadFn);
    }
    
    return ids;
  }

  /**
   * Process the upload queue
   */
  private async processQueue(
    uploadFn: (file: File, onProgress?: (progress: number) => void) => Promise<any>
  ): Promise<void> {
    if (this.isProcessing) return;
    
    this.isProcessing = true;
    
    while (this.queue.length > 0 || this.processing.length > 0) {
      // Wait for available slots
      while (this.processing.length >= this.options.maxConcurrent && this.processing.length > 0) {
        await new Promise(resolve => setTimeout(resolve, 100));
      }
      
      // Get next item from queue
      const item = this.queue.shift();
      if (!item) {
        // No more items in queue, wait for processing to complete
        if (this.processing.length === 0) break;
        await new Promise(resolve => setTimeout(resolve, 100));
        continue;
      }
      
      // Apply rate limiting delay
      await this.applyRateLimit();
      
      // Start processing the item
      this.processing.push(item);
      this.processItem(item, uploadFn);
    }
    
    this.isProcessing = false;
    
    // Notify completion
    if (this.options.onQueueProgress) {
      this.options.onQueueProgress(this.completed.length, this.completed.length + this.failed.length);
    }
  }

  /**
   * Process a single queue item
   */
  private async processItem(
    item: QueueItem,
    uploadFn: (file: File, onProgress?: (progress: number) => void) => Promise<any>
  ): Promise<void> {
    try {
      const result = await uploadFn(item.file, item.onProgress);
      
      // Remove from processing and add to completed
      this.processing = this.processing.filter(p => p.id !== item.id);
      this.completed.push(item);
      
      if (item.onComplete) {
        item.onComplete(result);
      }
      
      // Update progress
      if (this.options.onQueueProgress) {
        this.options.onQueueProgress(
          this.completed.length,
          this.completed.length + this.failed.length + this.queue.length + this.processing.length
        );
      }
      
    } catch (error) {
      // Handle rate limiting
      if (this.isRateLimitError(error)) {
        const retryAfter = this.extractRetryAfter(error);
        
        if (this.options.onRateLimitDetected) {
          this.options.onRateLimitDetected(retryAfter);
        }
        
        // Remove from processing and retry later
        this.processing = this.processing.filter(p => p.id !== item.id);
        
        if (item.retryCount < item.maxRetries) {
          item.retryCount++;
          // Add back to queue with delay
          setTimeout(() => {
            this.queue.unshift(item); // Add to front for priority
            this.sortQueue();
          }, retryAfter * 1000);
        } else {
          this.failed.push(item);
          if (item.onError) {
            item.onError(error as Error);
          }
        }
      } else {
        // Handle other errors
        this.processing = this.processing.filter(p => p.id !== item.id);
        
        if (item.retryCount < item.maxRetries) {
          item.retryCount++;
          // Retry with exponential backoff
          const delay = Math.min(1000 * Math.pow(2, item.retryCount), 30000);
          setTimeout(() => {
            this.queue.push(item);
            this.sortQueue();
          }, delay);
        } else {
          this.failed.push(item);
          if (item.onError) {
            item.onError(error as Error);
          }
        }
      }
      
      // Update progress
      if (this.options.onQueueProgress) {
        this.options.onQueueProgress(
          this.completed.length,
          this.completed.length + this.failed.length + this.queue.length + this.processing.length
        );
      }
    }
  }

  /**
   * Apply rate limiting delay between uploads
   */
  private async applyRateLimit(): Promise<void> {
    const now = Date.now();
    const timeSinceLastUpload = now - this.lastUploadTime;
    
    if (timeSinceLastUpload < this.options.delayBetweenUploads) {
      const delay = this.options.delayBetweenUploads - timeSinceLastUpload;
      await new Promise(resolve => setTimeout(resolve, delay));
    }
    
    this.lastUploadTime = Date.now();
  }

  /**
   * Sort queue by priority
   */
  private sortQueue(): void {
    const priorityOrder = { high: 0, medium: 1, low: 2 };
    this.queue.sort((a, b) => priorityOrder[a.priority] - priorityOrder[b.priority]);
  }

  /**
   * Check if error is a rate limit error
   */
  private isRateLimitError(error: any): boolean {
    return (
      error?.response?.status === 429 ||
      error?.status === 429 ||
      error?.message?.includes('429') ||
      error?.message?.toLowerCase().includes('rate limit') ||
      error?.message?.toLowerCase().includes('too many requests')
    );
  }

  /**
   * Extract retry-after value from error
   */
  private extractRetryAfter(error: any): number {
    // Try to get retry-after from headers
    const retryAfter = error?.response?.headers?.['retry-after'] ||
                      error?.headers?.['retry-after'];
    
    if (retryAfter) {
      const seconds = parseInt(retryAfter, 10);
      return isNaN(seconds) ? 60 : Math.min(seconds, 300); // Max 5 minutes
    }
    
    // Default retry after 60 seconds
    return 60;
  }

  /**
   * Get queue status
   */
  getStatus() {
    return {
      pending: this.queue.length,
      processing: this.processing.length,
      completed: this.completed.length,
      failed: this.failed.length,
      total: this.queue.length + this.processing.length + this.completed.length + this.failed.length
    };
  }

  /**
   * Clear the queue
   */
  clear(): void {
    this.queue = [];
    this.completed = [];
    this.failed = [];
    // Don't clear processing items as they're already in progress
  }

  /**
   * Pause queue processing
   */
  pause(): void {
    this.isProcessing = false;
  }

  /**
   * Resume queue processing
   */
  resume(uploadFn: (file: File, onProgress?: (progress: number) => void) => Promise<any>): void {
    if (!this.isProcessing && (this.queue.length > 0 || this.processing.length > 0)) {
      this.processQueue(uploadFn);
    }
  }

  /**
   * Retry failed uploads
   */
  retryFailed(uploadFn: (file: File, onProgress?: (progress: number) => void) => Promise<any>): void {
    // Move failed items back to queue
    this.failed.forEach(item => {
      item.retryCount = 0; // Reset retry count
      this.queue.push(item);
    });
    
    this.failed = [];
    this.sortQueue();
    
    // Resume processing
    this.resume(uploadFn);
  }

  /**
   * Process all files in the queue
   */
  async processAll(uploadFn: (file: File, onProgress?: (progress: number) => void) => Promise<any>): Promise<void> {
    if (this.queue.length === 0) {
      return;
    }
    
    return new Promise((resolve, reject) => {
      const checkCompletion = () => {
        if (this.queue.length === 0 && this.processing.length === 0) {
          resolve();
        } else if (this.failed.length > 0 && this.queue.length === 0 && this.processing.length === 0) {
          reject(new Error(`Upload failed for ${this.failed.length} files`));
        } else {
          setTimeout(checkCompletion, 100);
        }
      };
      
      // Start processing
      this.processQueue(uploadFn);
      
      // Check for completion
      checkCompletion();
    });
  }
}

// Export a default instance
export const defaultUploadQueue = new UploadQueue();