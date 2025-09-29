import { createProperty as originalCreateProperty, updateProperty as originalUpdateProperty } from './propertyService';
import ChunkedUploader, { ChunkUploadOptions } from '../utils/chunkedUpload';

// Helper function to safely check if object is a File
const isFile = (obj: any): obj is File => {
  try {
    return obj && 
           typeof obj === 'object' && 
           obj.constructor && 
           obj.constructor.name === 'File' &&
           typeof obj.size === 'number' &&
           typeof obj.name === 'string' &&
           typeof obj.type === 'string';
  } catch (error) {
    return false;
  }
};

export interface EnhancedUploadOptions {
  onProgress?: (progress: number) => void;
  onFileProgress?: (fileName: string, progress: number) => void;
  onRateLimitDetected?: (retryAfter: number) => void;
  onError?: (error: string, isRateLimited: boolean) => void;
  chunkThreshold?: number; // Files larger than this will use chunked upload (default: 10MB)
  enableCompression?: boolean; // Enable image compression before upload
  maxImageSize?: number; // Maximum image dimension for compression
  compressionQuality?: number; // Image compression quality (0-1)
  throttleDelay?: number; // Delay between requests to prevent rate limiting
}

class EnhancedPropertyService {
  private static readonly DEFAULT_CHUNK_THRESHOLD = 10 * 1024 * 1024; // 10MB
  private static readonly DEFAULT_MAX_IMAGE_SIZE = 1920; // Max width/height for images
  private static readonly DEFAULT_COMPRESSION_QUALITY = 0.8;

  /**
   * Create property with enhanced upload capabilities
   */
  static async createProperty(propertyData: any, options: EnhancedUploadOptions = {}) {
    const {
      onProgress,
      onFileProgress,
      chunkThreshold = this.DEFAULT_CHUNK_THRESHOLD,
      enableCompression = true,
      maxImageSize = this.DEFAULT_MAX_IMAGE_SIZE,
      compressionQuality = this.DEFAULT_COMPRESSION_QUALITY
    } = options;

    try {
      // Separate files that need chunked upload vs regular upload
      const { largeFiles, regularData } = await this.processPropertyData(
        propertyData,
        chunkThreshold,
        enableCompression,
        maxImageSize,
        compressionQuality,
        onFileProgress
      );

      // For property creation, we now send all files (including large ones) to the main property endpoint
      // The backend handles file processing during property creation
      console.log('🔧 EnhancedPropertyService: Creating property with all files via main endpoint');
      console.log('📊 Large files detected:', largeFiles.length);
      console.log('📋 Regular data keys:', Object.keys(regularData));
      
      // Merge large files back into regularData for the main property creation endpoint
      const finalData = { ...regularData };
      
      // Add large files back to their respective arrays
      largeFiles.forEach(({ file, key, index }) => {
        if (key === 'mainImage') {
          finalData.mainImage = file;
        } else if (key === 'images') {
          if (!Array.isArray(finalData.images)) {
            finalData.images = [];
          }
          if (typeof index === 'number') {
            finalData.images[index] = file;
          } else {
            finalData.images.push(file);
          }
        } else if (key === 'videos') {
          if (!Array.isArray(finalData.videos)) {
            finalData.videos = [];
          }
          if (typeof index === 'number') {
            finalData.videos[index] = file;
          } else {
            finalData.videos.push(file);
          }
        }
      });
      
      onProgress?.(20); // Starting property creation
      
      console.log('📤 About to call originalCreateProperty with finalData keys:', Object.keys(finalData));
      console.log('🎯 Final data structure:', {
        hasMainImage: !!finalData.mainImage,
        imagesCount: Array.isArray(finalData.images) ? finalData.images.length : 0,
        videosCount: Array.isArray(finalData.videos) ? finalData.videos.length : 0
      });
      
      // Create property with all files
      const result = await originalCreateProperty(finalData);
      
      onProgress?.(100); // Complete
      
      return result;
    } catch (error: any) {
      console.error('Enhanced property creation failed:', error);
      
      // Provide more specific error messages
      if (error.message?.includes('Failed to upload')) {
        throw new Error(`Upload failed: ${error.message}`);
      } else if (error.message?.includes('Network')) {
        throw new Error('Network error occurred during upload. Please check your connection and try again.');
      } else if (error.message?.includes('413') || error.message?.includes('Content Too Large')) {
        throw new Error('File size too large. Please reduce file sizes or try uploading fewer files at once.');
      } else {
        throw new Error(`Property creation failed: ${error.message || 'Unknown error occurred'}`);
      }
    }
  }

  /**
   * Update property with enhanced upload capabilities
   */
  static async updateProperty(id: number, propertyData: any, options: EnhancedUploadOptions = {}) {
    const {
      onProgress,
      onFileProgress,
      chunkThreshold = this.DEFAULT_CHUNK_THRESHOLD,
      enableCompression = true,
      maxImageSize = this.DEFAULT_MAX_IMAGE_SIZE,
      compressionQuality = this.DEFAULT_COMPRESSION_QUALITY
    } = options;

    try {
      // Process property data similar to create
      const { largeFiles, regularData } = await this.processPropertyData(
        propertyData,
        chunkThreshold,
        enableCompression,
        maxImageSize,
        compressionQuality,
        onFileProgress
      );

      // For property updates, we also send all files to the main property endpoint
      // The backend handles file processing during property update
      console.log('Updating property with all files via main endpoint');
      
      // Merge large files back into regularData for the main property update endpoint
      const finalData = { ...regularData };
      
      // Add large files back to their respective arrays
      largeFiles.forEach(({ file, key, index }) => {
        if (key === 'mainImage') {
          finalData.mainImage = file;
        } else if (key === 'images') {
          if (!Array.isArray(finalData.images)) {
            finalData.images = [];
          }
          if (typeof index === 'number') {
            finalData.images[index] = file;
          } else {
            finalData.images.push(file);
          }
        } else if (key === 'videos') {
          if (!Array.isArray(finalData.videos)) {
            finalData.videos = [];
          }
          if (typeof index === 'number') {
            finalData.videos[index] = file;
          } else {
            finalData.videos.push(file);
          }
        }
      });
      
      onProgress?.(20); // Starting property update
      
      // Update property with all files
      const result = await originalUpdateProperty(id, finalData);
      
      onProgress?.(100); // Complete
      
      return result;
    } catch (error: any) {
      console.error('Enhanced property update failed:', error);
      
      // Provide more specific error messages
      if (error.message?.includes('Failed to upload')) {
        throw new Error(`Upload failed: ${error.message}`);
      } else if (error.message?.includes('Network')) {
        throw new Error('Network error occurred during upload. Please check your connection and try again.');
      } else if (error.message?.includes('413') || error.message?.includes('Content Too Large')) {
        throw new Error('File size too large. Please reduce file sizes or try uploading fewer files at once.');
      } else {
        throw new Error(`Property update failed: ${error.message || 'Unknown error occurred'}`);
      }
    }
  }

  /**
   * Process property data and separate large files
   */
  private static async processPropertyData(
    propertyData: any,
    chunkThreshold: number,
    enableCompression: boolean,
    maxImageSize: number,
    compressionQuality: number,
    onFileProgress?: (fileName: string, progress: number) => void
  ) {
    const largeFiles: Array<{ file: File; key: string; index?: number }> = [];
    const regularData = { ...propertyData };

    // Process main image
    if (isFile(regularData.mainImage)) {
      const file = regularData.mainImage;
      
      if (enableCompression && this.isImage(file)) {
        onFileProgress?.(file.name, 0);
        const compressedFile = await this.compressImage(file, maxImageSize, compressionQuality);
        onFileProgress?.(file.name, 100);
        regularData.mainImage = compressedFile;
      }

      if (ChunkedUploader.shouldUseChunkedUpload(regularData.mainImage, chunkThreshold)) {
        largeFiles.push({ file: regularData.mainImage, key: 'mainImage' });
        delete regularData.mainImage;
      }
    }

    // Process images array
    if (Array.isArray(regularData.images)) {
      const processedImages: File[] = [];
      
      for (let i = 0; i < regularData.images.length; i++) {
        const file = regularData.images[i];
        
        if (isFile(file)) {
          let processedFile = file;
          
          if (enableCompression && this.isImage(file)) {
            onFileProgress?.(file.name, 0);
            processedFile = await this.compressImage(file, maxImageSize, compressionQuality);
            onFileProgress?.(file.name, 100);
          }

          if (ChunkedUploader.shouldUseChunkedUpload(processedFile, chunkThreshold)) {
            largeFiles.push({ file: processedFile, key: 'images', index: i });
          } else {
            processedImages.push(processedFile);
          }
        }
      }
      
      regularData.images = processedImages;
    }

    // Process videos array
    if (Array.isArray(regularData.videos)) {
      const processedVideos: File[] = [];
      
      for (let i = 0; i < regularData.videos.length; i++) {
        const file = regularData.videos[i];
        
        if (isFile(file)) {
          if (ChunkedUploader.shouldUseChunkedUpload(file, chunkThreshold)) {
            largeFiles.push({ file, key: 'videos', index: i });
          } else {
            processedVideos.push(file);
          }
        }
      }
      
      regularData.videos = processedVideos;
    }

    return { largeFiles, regularData };
  }

  /**
   * Upload large files using chunked upload with rate limiting handling
   * This is now disabled for property creation - files are handled by the main property endpoint
   */
  private static async uploadLargeFiles(
    largeFiles: Array<{ file: File; key: string; index?: number }>,
    options: ChunkUploadOptions
  ) {
    // For property creation, we no longer upload files separately
    // All files are handled by the main property creation endpoint
    console.warn('uploadLargeFiles called during property creation - this should not happen');
    
    // Return success for all files to prevent errors
    return largeFiles.map((largeFile) => ({
      success: true,
      fileId: `temp_${Date.now()}_${Math.random()}`, // Temporary ID
      fileName: largeFile.file.name
    }));
  }

  /**
   * Replace file objects with uploaded file IDs
   */
  private static replaceFilesWithIds(
    regularData: any,
    largeFiles: Array<{ file: File; key: string; index?: number }>,
    uploadResults: Array<{ success: boolean; fileId?: string }>
  ) {
    const finalData = { ...regularData };
    
    // Add uploaded file IDs to the data
    const uploadedFileIds: string[] = [];
    
    largeFiles.forEach((largeFile, index) => {
      const result = uploadResults[index];
      if (result.success && result.fileId) {
        uploadedFileIds.push(result.fileId);
      }
    });
    
    // Add uploaded file IDs as a separate field for backend processing
    if (uploadedFileIds.length > 0) {
      finalData.uploadedFileIds = uploadedFileIds;
    }
    
    return finalData;
  }

  /**
   * Check if file is an image
   */
  private static isImage(file: File): boolean {
    return file.type.startsWith('image/');
  }

  /**
   * Compress image file
   */
  private static async compressImage(
    file: File,
    maxSize: number,
    quality: number
  ): Promise<File> {
    return new Promise((resolve) => {
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d')!;
      const img = new Image();
      
      img.onload = () => {
        // Calculate new dimensions
        let { width, height } = img;
        
        if (width > maxSize || height > maxSize) {
          if (width > height) {
            height = (height * maxSize) / width;
            width = maxSize;
          } else {
            width = (width * maxSize) / height;
            height = maxSize;
          }
        }
        
        canvas.width = width;
        canvas.height = height;
        
        // Draw and compress
        ctx.drawImage(img, 0, 0, width, height);
        
        canvas.toBlob(
          (blob) => {
            if (blob) {
              const compressedFile = new File([blob], file.name, {
                type: file.type,
                lastModified: Date.now()
              });
              resolve(compressedFile);
            } else {
              resolve(file); // Fallback to original
            }
          },
          file.type,
          quality
        );
      };
      
      img.onerror = () => resolve(file); // Fallback to original
      img.src = URL.createObjectURL(file);
    });
  }

  /**
   * Check if file is a video
   */
  private static isVideo(file: File): boolean {
    return file.type.startsWith('video/');
  }

  /**
   * Upload a single file (for existing properties only)
   * This method should NOT be used during property creation
   */
  static async uploadSingleFile(
    file: File,
    propertyId: number,
    onProgress?: (progress: number) => void,
    onError?: (error: any) => void
  ): Promise<{ success: boolean; fileId?: string; error?: string }> {
    try {
      // Determine upload endpoint and field name based on file type
      const isImage = EnhancedPropertyService.isImage(file);
      const isVideo = EnhancedPropertyService.isVideo(file);
      
      if (!isImage && !isVideo) {
        throw new Error('Unsupported file type. Only images and videos are allowed.');
      }
      
      const endpoint = isImage ? `/properties/${propertyId}/images` : `/properties/${propertyId}/videos`;
      const fieldName = isImage ? 'image' : 'video';
      
      // Check if file should use chunked upload
      const chunkThreshold = 10 * 1024 * 1024; // 10MB
      
      if (ChunkedUploader.shouldUseChunkedUpload(file, chunkThreshold)) {
        // Use chunked upload for large files
        const result = await ChunkedUploader.uploadFile(file, endpoint, {
          onProgress: onProgress,
          onError: (error, isRateLimited) => {
            console.error(`Upload error for ${file.name}:`, error);
            onError?.(error);
          },
          onRateLimitDetected: (retryAfter) => {
            console.warn(`Rate limit detected for ${file.name}, retrying after ${retryAfter}ms`);
          }
        });
        
        return {
          success: result.success,
          fileId: result.fileId,
          error: result.error
        };
      } else {
        // Use regular upload for smaller files
        const formData = new FormData();
        formData.append(fieldName, file);
        
        const response = await fetch(`${import.meta.env.VITE_API_BASE_URL}/api/v1${endpoint}`, {
          method: 'POST',
          body: formData,
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token') || ''}`,
          },
        });
        
        if (!response.ok) {
          throw new Error(`Upload failed: ${response.statusText}`);
        }
        
        const result = await response.json();
        onProgress?.(100);
        
        return {
          success: true,
          fileId: result.fileId || result.id,
        };
      }
    } catch (error: any) {
      console.error(`Failed to upload file ${file.name}:`, error);
      onError?.(error);
      
      return {
        success: false,
        error: error.message || 'Upload failed'
      };
    }
  }

  /**
   * Get upload progress for multiple files
   */
  static calculateTotalProgress(fileProgresses: Record<string, number>): number {
    const progresses = Object.values(fileProgresses);
    if (progresses.length === 0) return 0;
    
    return progresses.reduce((sum, progress) => sum + progress, 0) / progresses.length;
  }
}

export default EnhancedPropertyService;