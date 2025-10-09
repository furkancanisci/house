// Utility functions for handling property images

export interface PropertyImageData {
  mainImage: string;
  images: string[];
}

// Available property images in the public folder
const PROPERTY_IMAGES = [
  '/images/properties/apartment_balcony_1.jpg',
  '/images/properties/apartment_highrise_1.jpeg',
  '/images/properties/apartment_interior_1.JPG',
  '/images/properties/apartment_luxury_1.jpg',
  '/images/properties/bathroom_1.jpg',
  '/images/properties/bedroom_1.webp',
  '/images/properties/condo_luxury_1.jpg',
  '/images/properties/house_kitchen_1.jpg',
  '/images/properties/house_modern_1.jpg',
  '/images/properties/house_ranch_1.jpeg',
  '/images/properties/house_suburban_1.jpg',
  '/images/properties/house_victorian_1.jpg',
  '/images/properties/penthouse_1.jpg',
  '/images/properties/studio_1.webp',
  '/images/properties/townhouse_1.jpg',
];

// Fallback placeholder image
const PLACEHOLDER_IMAGE = '/images/placeholder-property.svg';

// Property type to image mapping for better fallbacks
const PROPERTY_TYPE_IMAGES: Record<string, string[]> = {
  apartment: [
    '/images/properties/apartment_balcony_1.jpg',
    '/images/properties/apartment_highrise_1.jpeg',
    '/images/properties/apartment_interior_1.JPG',
    '/images/properties/apartment_luxury_1.jpg',
  ],
  house: [
    '/images/properties/house_kitchen_1.jpg',
    '/images/properties/house_modern_1.jpg',
    '/images/properties/house_ranch_1.jpeg',
    '/images/properties/house_suburban_1.jpg',
    '/images/properties/house_victorian_1.jpg',
  ],
  condo: [
    '/images/properties/condo_luxury_1.jpg',
    '/images/properties/apartment_luxury_1.jpg',
  ],
  townhouse: [
    '/images/properties/townhouse_1.jpg',
    '/images/properties/house_suburban_1.jpg',
  ],
  studio: [
    '/images/properties/studio_1.webp',
    '/images/properties/apartment_interior_1.JPG',
  ],
  loft: [
    '/images/properties/apartment_luxury_1.jpg',
    '/images/properties/apartment_interior_1.JPG',
  ],
  villa: [
    '/images/properties/house_modern_1.jpg',
    '/images/properties/house_victorian_1.jpg',
  ],
  penthouse: [
    '/images/properties/penthouse_1.jpg',
    '/images/properties/apartment_luxury_1.jpg',
  ],
  commercial: [
    '/images/properties/apartment_highrise_1.jpeg',
  ],
  land: [
    '/images/properties/house_suburban_1.jpg',
  ],
};

/**
 * Fix image URL to ensure it's properly formatted
 */
export const fixImageUrl = (url: string | undefined | null | any): string => {
  // Check if url is not a string or is empty
  if (!url || typeof url !== 'string') return '';
  
  // Don't process already processed URLs - be more thorough
  if (url.startsWith('http://') || 
      url.startsWith('https://') ||
      url.startsWith('data:') ||
      url.startsWith('/images/')) {
    return url;
  }
  
  // Handle relative URLs from backend (e.g., /storage/media/...)
  if (url.startsWith('/storage/') || url.startsWith('/media/')) {
    const fixedUrl = `http://localhost:8000${url}`;

    return fixedUrl;
  }
  
  // If it looks like a valid URL path, assume it's from the backend
  if (url.startsWith('/') && !url.startsWith('/images/')) {
    const fixedUrl = `http://localhost:8000${url}`;

    return fixedUrl;
  }
  

  return url;
};

/**
 * Get a fallback image based on property type
 */
export const getFallbackImage = (propertyType: string, propertyId?: string | number): string => {
  const normalizedType = propertyType.toLowerCase();
  const typeImages = PROPERTY_TYPE_IMAGES[normalizedType] || PROPERTY_IMAGES;
  
  // Use property ID to get a consistent image for the same property
  if (propertyId) {
    const index = Math.abs(Number(propertyId) || 0) % typeImages.length;
    return typeImages[index];
  }
  
  // Return first image of the type, or placeholder if none available
  return typeImages[0] || PROPERTY_IMAGES[0] || PLACEHOLDER_IMAGE;
};

/**
 * Process property images and ensure there's always a main image
 */
export const processPropertyImages = (
  property: any,
  propertyType?: string
): PropertyImageData => {
  let mainImage = '';
  let images: string[] = [];
  let foundMainImage = false;
  let hasRealImages = false;

  // 1. PRIORITY: Check property.media array for real uploaded images
  if (property.media && Array.isArray(property.media) && property.media.length > 0) {
    console.log('🎯 Processing property.media with', property.media.length, 'items');
    
    const mainImageCandidates: Array<{url: string, priority: number, source: string}> = [];
    const regularImages: string[] = [];
    
    property.media.forEach((mediaItem: any) => {
      console.log('📷 Processing media item:', {
        id: mediaItem.id,
        collection_name: mediaItem.collection_name,
        file_name: mediaItem.file_name,
        mime_type: mediaItem.mime_type,
        original_url: mediaItem.original_url,
        url: mediaItem.url
      });
      
      // Only process image files
      if (mediaItem.mime_type && mediaItem.mime_type.startsWith('image/')) {
        let imageUrl = '';
        
        // Use original URL or url field directly (no conversions)
        imageUrl = mediaItem.original_url || mediaItem.url || '';
        
        if (imageUrl) {
          const fixedUrl = fixImageUrl(imageUrl);
          
          // Check for main image indicators - PRIORITIZE collection_name
          const fileName = mediaItem.file_name || mediaItem.filename || mediaItem.name || '';
          const isMainImageByCollection = mediaItem.collection_name === 'main_image' || 
                                        mediaItem.collection_name === 'main';
          const isMainImageByFilename = fileName.toLowerCase().includes('main');
          const isFeaturedImage = fileName.toLowerCase().includes('featured');
          
          console.log('🔍 Image analysis:', {
            fileName,
            isMainImageByFilename,
            isMainImageByCollection,
            isFeaturedImage,
            fixedUrl,
            collection: mediaItem.collection_name
          });
          
          // Categorize images by priority - MAIN_IMAGE COLLECTION HAS HIGHEST PRIORITY
          if (isMainImageByCollection) {
            mainImageCandidates.push({ url: fixedUrl, priority: 1, source: 'collection_main_image' });
            console.log('✅ Found MAIN image by collection name (main_image):', fixedUrl);
          } else if (isMainImageByFilename) {
            mainImageCandidates.push({ url: fixedUrl, priority: 2, source: 'filename_main' });
            console.log('✅ Found MAIN image by filename (contains "main"):', fixedUrl);
          } else if (isFeaturedImage) {
            mainImageCandidates.push({ url: fixedUrl, priority: 3, source: 'featured' });
            console.log('✅ Found featured image:', fixedUrl);
          } else {
            // Only add to regular images if it's not in main_image collection
            if (mediaItem.collection_name !== 'main_image' && mediaItem.collection_name !== 'main') {
              regularImages.push(fixedUrl);
              console.log('📷 Added regular image:', fixedUrl);
            }
          }
          
          // Add to general images array only if not main image
          if (!isMainImageByCollection) {
            images.push(fixedUrl);
          }
        }
      }
    });
    
    // Select the best main image candidate
    if (mainImageCandidates.length > 0) {
      // Sort by priority (lower number = higher priority)
      mainImageCandidates.sort((a, b) => a.priority - b.priority);
      mainImage = mainImageCandidates[0].url;
      foundMainImage = true;
      hasRealImages = true; // Mark that we found real images
      console.log('✅ Selected main image:', mainImage, 'from source:', mainImageCandidates[0].source);
    } else if (regularImages.length > 0) {
      // Use first regular image as fallback
      mainImage = regularImages[0];
      foundMainImage = true;
      hasRealImages = true; // Mark that we found real images
      console.log('✅ Using first regular image as main image fallback:', mainImage);
    }
    
    // If we found any images from media, mark as having real images
    if (images.length > 0 || mainImageCandidates.length > 0) {
      hasRealImages = true;
      console.log('✅ Found', images.length + mainImageCandidates.length, 'real images from property.media');
    }
  }

  // If no main image found from nested structure, try flat structure
  if (!mainImage) {
    // Try to get main image from various sources
    const possibleMainImages = [
      property.mainImage,
      property.main_image_url,
      property.main_image,
      property.image,
      property.thumbnail,
    ].filter(Boolean);

    if (possibleMainImages.length > 0) {
      mainImage = fixImageUrl(possibleMainImages[0]);
    }
  }

  // If images array is still empty, try to get from flat structure
  if (images.length === 0) {
    // Try to get images array from various sources
    const possibleImageArrays = [
      Array.isArray(property.images) ? property.images : null,
      property.gallery_urls,
      property.gallery,
      property.media,
    ].filter(Array.isArray);

    if (possibleImageArrays.length > 0) {
      const imageArray = possibleImageArrays[0];
      images = imageArray
        .map((item: any) => {
          if (typeof item === 'string') return fixImageUrl(item);
          if (item && typeof item === 'object') {
            return fixImageUrl(item.url || item.src || item.image);
          }
          return null;
        })
        .filter(Boolean);
    }
  }

  // If no main image, try to use first image from array
  if (!mainImage && images.length > 0) {
    mainImage = images[0];
  }

  // Only use fallback images if NO real images exist at all
  if (!mainImage && images.length === 0) {
    // Use simple placeholder instead of random images for consistency
    mainImage = PLACEHOLDER_IMAGE;
    images = [PLACEHOLDER_IMAGE];
  } else if (!mainImage && images.length > 0) {
    // If we have gallery images but no main image, use the first gallery image as main
    mainImage = images[0];
  }

  // If no images array but we have a main image, add it to the array
  if (images.length === 0 && mainImage) {
    images = [mainImage];
  }

  // REMOVED: Do not add fallback images that override real uploaded images
  // Only use fallback images if NO images exist at all
  // This prevents mixing real uploaded images with random stock images

  return {
    mainImage,
    images: [...new Set(images)], // Remove duplicates
  };
};

/**
 * Get a consistent property image for demo purposes
 */
export const getRandomPropertyImage = (propertyId?: string | number): string => {
  if (PROPERTY_IMAGES.length === 0) {
    return PLACEHOLDER_IMAGE;
  }
  
  // If propertyId is provided, use it to get a consistent image
  if (propertyId) {
    const index = Math.abs(Number(propertyId) || 0) % PROPERTY_IMAGES.length;
    return PROPERTY_IMAGES[index];
  }
  
  // Fallback to first image instead of random
  return PROPERTY_IMAGES[0];
};

/**
 * Compress an image file to reduce its size
 */
export const compressImage = (
  file: File,
  maxWidth: number = 1920,
  maxHeight: number = 1080,
  quality: number = 0.8
): Promise<File> => {
  return new Promise((resolve, reject) => {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const img = new Image();

    img.onload = () => {
      // Calculate new dimensions while maintaining aspect ratio
      let { width, height } = img;
      
      if (width > maxWidth || height > maxHeight) {
        const aspectRatio = width / height;
        
        if (width > height) {
          width = maxWidth;
          height = width / aspectRatio;
        } else {
          height = maxHeight;
          width = height * aspectRatio;
        }
      }

      // Set canvas dimensions
      canvas.width = width;
      canvas.height = height;

      // Draw and compress the image
      ctx?.drawImage(img, 0, 0, width, height);
      
      canvas.toBlob(
        (blob) => {
          if (blob) {
            const compressedFile = new File([blob], file.name, {
              type: file.type,
              lastModified: Date.now(),
            });
            resolve(compressedFile);
          } else {
            reject(new Error('Failed to compress image'));
          }
        },
        file.type,
        quality
      );
    };

    img.onerror = () => reject(new Error('Failed to load image'));
    img.src = URL.createObjectURL(file);
  });
};

/**
 * Compress multiple images
 */
export const compressImages = async (
  files: File[],
  maxWidth: number = 1920,
  maxHeight: number = 1080,
  quality: number = 0.8
): Promise<File[]> => {
  const compressedFiles: File[] = [];
  
  for (const file of files) {
    try {
      const compressedFile = await compressImage(file, maxWidth, maxHeight, quality);
      compressedFiles.push(compressedFile);
    } catch (error) {

      // If compression fails, use original file
      compressedFiles.push(file);
    }
  }
  
  return compressedFiles;
};

/**
 * Format file size for display
 */
export const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};