import api from '../services/api';

export interface ChunkUploadOptions {
  chunkSize?: number; // Size of each chunk in bytes (default: 1MB)
  maxRetries?: number; // Maximum retry attempts per chunk
  onProgress?: (progress: number) => void; // Progress callback
  onChunkProgress?: (chunkIndex: number, totalChunks: number) => void; // Chunk progress callback
  onRateLimitDetected?: (retryAfter: number) => void; // Rate limit callback
  onError?: (error: string, isRateLimit: boolean) => void; // Error callback
  throttleDelay?: number; // Delay between chunk uploads (default: 100ms)
}

export interface ChunkUploadResult {
  success: boolean;
  fileId?: string;
  error?: string;
  isRateLimited?: boolean;
}

export interface RateLimitInfo {
  isRateLimited: boolean;
  retryAfter?: number;
  remainingRequests?: number;
  resetTime?: number;
}

class ChunkedUploader {
  private static readonly DEFAULT_CHUNK_SIZE = 1024 * 1024; // 1MB
  private static readonly DEFAULT_MAX_RETRIES = 5; // Increased for rate limiting
  private static readonly DEFAULT_THROTTLE_DELAY = 100; // 100ms between chunks
  private static readonly RATE_LIMIT_MAX_DELAY = 60000; // Max 60 seconds wait for rate limits
  private static requestQueue: Array<() => Promise<any>> = [];
  private static isProcessingQueue = false;
  private static lastRequestTime = 0;

  /**
   * Detect rate limiting from error response
   */
  private static detectRateLimit(error: any): RateLimitInfo {
    const status = error.response?.status;
    const headers = error.response?.headers || {};
    
    if (status === 429) {
      const retryAfter = parseInt(headers['retry-after'] || headers['x-ratelimit-reset']) || 60;
      const remainingRequests = parseInt(headers['x-ratelimit-remaining']) || 0;
      const resetTime = parseInt(headers['x-ratelimit-reset']) || Date.now() + (retryAfter * 1000);
      
      return {
        isRateLimited: true,
        retryAfter,
        remainingRequests,
        resetTime
      };
    }
    
    return { isRateLimited: false };
  }

  /**
   * Apply throttling between requests
   */
  private static async applyThrottling(throttleDelay: number): Promise<void> {
    const now = Date.now();
    const timeSinceLastRequest = now - this.lastRequestTime;
    
    if (timeSinceLastRequest < throttleDelay) {
      const waitTime = throttleDelay - timeSinceLastRequest;
      await this.delay(waitTime);
    }
    
    this.lastRequestTime = Date.now();
  }

  /**
   * Calculate smart retry delay based on rate limiting
   */
  private static calculateRetryDelay(attempt: number, rateLimitInfo?: RateLimitInfo): number {
    if (rateLimitInfo?.isRateLimited) {
      // For rate limits, use the retry-after header or exponential backoff with jitter
      const baseDelay = rateLimitInfo.retryAfter ? rateLimitInfo.retryAfter * 1000 : Math.pow(2, attempt) * 2000;
      const jitter = Math.random() * 1000;
      return Math.min(baseDelay + jitter, this.RATE_LIMIT_MAX_DELAY);
    }
    
    // Regular exponential backoff for other errors
    return Math.pow(2, attempt) * 1000 + Math.random() * 1000;
  }

  /**
   * Upload a file in chunks
   */
  static async uploadFile(
    file: File,
    uploadPath: string,
    options: ChunkUploadOptions = {}
  ): Promise<ChunkUploadResult> {
    const {
      chunkSize = this.DEFAULT_CHUNK_SIZE,
      maxRetries = this.DEFAULT_MAX_RETRIES,
      onProgress,
      onChunkProgress,
      onRateLimitDetected,
      onError,
      throttleDelay = this.DEFAULT_THROTTLE_DELAY
    } = options;

    try {
      const totalChunks = Math.ceil(file.size / chunkSize);
      const uploadId = this.generateUploadId();
      
      console.log(`Starting chunked upload for ${file.name}: ${totalChunks} chunks`);

      // Initialize upload
      await this.initializeUpload(uploadId, file.name, file.size, totalChunks, chunkSize, uploadPath);

      // Upload chunks with rate limiting and throttling
      for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
        const start = chunkIndex * chunkSize;
        const end = Math.min(start + chunkSize, file.size);
        const chunk = file.slice(start, end);

        let retries = 0;
        let chunkUploaded = false;
        let lastRateLimitInfo: RateLimitInfo | undefined;

        while (!chunkUploaded && retries < maxRetries) {
          try {
            // Apply throttling between requests
            await this.applyThrottling(throttleDelay);
            
            await this.uploadChunk(uploadId, chunkIndex, chunk, totalChunks, uploadPath);
            chunkUploaded = true;
            
            // Update progress
            const progress = ((chunkIndex + 1) / totalChunks) * 100;
            onProgress?.(progress);
            onChunkProgress?.(chunkIndex + 1, totalChunks);
            
            console.log(`Chunk ${chunkIndex + 1}/${totalChunks} uploaded successfully`);
          } catch (error: any) {
            retries++;
            
            // Detect rate limiting
            const rateLimitInfo = this.detectRateLimit(error);
            lastRateLimitInfo = rateLimitInfo;
            
            const isRateLimit = rateLimitInfo.isRateLimited;
            const errorMessage = isRateLimit 
              ? `Rate limited - retry after ${rateLimitInfo.retryAfter}s` 
              : error.message || 'Upload failed';
            
            console.warn(`Chunk ${chunkIndex} failed (attempt ${retries}/${maxRetries}):`, errorMessage);
            
            // Notify about rate limiting
            if (isRateLimit && rateLimitInfo.retryAfter) {
              onRateLimitDetected?.(rateLimitInfo.retryAfter);
            }
            
            // Notify about error
            onError?.(errorMessage, isRateLimit);
            
            if (retries >= maxRetries) {
              const finalError = isRateLimit 
                ? `Rate limited: Failed to upload chunk ${chunkIndex} after ${maxRetries} retries. Server is busy, please try again later.`
                : `Failed to upload chunk ${chunkIndex} after ${maxRetries} retries: ${errorMessage}`;
              throw new Error(finalError);
            }
            
            // Calculate smart retry delay
            const retryDelay = this.calculateRetryDelay(retries, rateLimitInfo);
            console.log(`Waiting ${retryDelay}ms before retry...`);
            await this.delay(retryDelay);
          }
        }
      }

      // Finalize upload
      const result = await this.finalizeUpload(uploadId, uploadPath);
      console.log('Chunked upload completed successfully');
      
      return {
        success: true,
        fileId: result.fileId
      };
    } catch (error: any) {
      console.error('Chunked upload failed:', error);
      
      // Check if the error is rate limiting related
      const isRateLimited = error.message?.includes('Rate limited') || error.message?.includes('429');
      
      return {
        success: false,
        error: error.message || 'Upload failed',
        isRateLimited
      };
    }
  }

  /**
   * Upload multiple files with chunked upload
   */
  static async uploadMultipleFiles(
    files: File[],
    uploadPath: string,
    options: ChunkUploadOptions = {}
  ): Promise<ChunkUploadResult[]> {
    const results: ChunkUploadResult[] = [];
    
    for (let i = 0; i < files.length; i++) {
      const file = files[i];
      console.log(`Uploading file ${i + 1}/${files.length}: ${file.name}`);
      
      const result = await this.uploadFile(file, uploadPath, {
        ...options,
        onProgress: (progress) => {
          // Calculate overall progress across all files
          const overallProgress = ((i / files.length) * 100) + (progress / files.length);
          options.onProgress?.(overallProgress);
        }
      });
      
      results.push(result);
      
      // If any file fails, we might want to continue or stop based on requirements
      if (!result.success) {
        console.error(`Failed to upload ${file.name}:`, result.error);
      }
    }
    
    return results;
  }

  /**
   * Initialize upload session on server
   */
  private static async initializeUpload(
    uploadId: string,
    fileName: string,
    fileSize: number,
    totalChunks: number,
    chunkSize: number = this.DEFAULT_CHUNK_SIZE,
    uploadPath: string
  ): Promise<void> {
    // Use the generic chunked upload endpoint
    const endpoint = '/upload/initialize';
    
    await api.post(endpoint, {
      filename: fileName,
      filesize: fileSize,
      total_chunks: totalChunks,
      chunk_size: chunkSize
    });
  }

  /**
   * Upload a single chunk
   */
  private static async uploadChunk(
    uploadId: string,
    chunkIndex: number,
    chunk: Blob,
    totalChunks: number,
    uploadPath: string
  ): Promise<void> {
    const formData = new FormData();
    formData.append('upload_id', uploadId);
    formData.append('chunk_number', chunkIndex.toString());
    formData.append('chunk', chunk);

    // Use the generic chunked upload endpoint
    const endpoint = '/upload/chunk';

    await api.post(endpoint, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      timeout: 60000, // 60 second timeout for chunk upload
    });
  }

  /**
   * Finalize upload and get file ID
   */
  private static async finalizeUpload(uploadId: string, uploadPath: string): Promise<{ fileId: string }> {
    // Use the generic chunked upload endpoint
    const endpoint = '/upload/complete';
    
    const response = await api.post(endpoint, {
      upload_id: uploadId
    });
    
    return {
      fileId: response.data.file_path
    };
  }

  /**
   * Generate unique upload ID
   */
  private static generateUploadId(): string {
    return `upload_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  /**
   * Delay utility for retries
   */
  private static delay(ms: number): Promise<void> {
    return new Promise(resolve => setTimeout(resolve, ms));
  }

  /**
   * Check if file should use chunked upload (files larger than 10MB)
   */
  static shouldUseChunkedUpload(file: File, threshold: number = 10 * 1024 * 1024): boolean {
    return file.size > threshold;
  }

  /**
   * Get human readable file size
   */
  static formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }
}

export default ChunkedUploader;