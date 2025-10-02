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
    const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api/v1', '') || 'http://127.0.0.1:8000';
    const fixedUrl = `${baseUrl}${url}`;

    return fixedUrl;
  }
  
  // If it looks like a valid URL path, assume it's from the backend
  if (url.startsWith('/') && !url.startsWith('/images/')) {
    const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api/v1', '') || 'http://127.0.0.1:8000';
    const fixedUrl = `${baseUrl}${url}`;

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
  propertyType?: string,
  hasVideos?: boolean
): PropertyImageData => {
  console.log('🔍 Processing property images for property:', property?.id || 'unknown');
  console.log('📊 Property data structure:', {
    hasImages: !!property.images,
    imagesType: typeof property.images,
    isImagesArray: Array.isArray(property.images),
    hasMedia: !!property.media,
    mediaLength: property.media?.length || 0,
    hasMainImageUrl: !!property.main_image_url,
    hasMainImage: !!property.mainImage,
    propertyKeys: Object.keys(property || {}),
    mediaItems: property.media?.slice(0, 3), // Show first 3 media items for debugging
  });

  let mainImage = '';
  let images: string[] = [];
  let allMediaItems: any[] = [];
  let hasRealImages = false; // Track if we found real images from media
  let foundMainImage = false; // Track if we found a main image

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

  // 2. FALLBACK: Only use other sources if we didn't find real images from media
  if (!hasRealImages) {
    console.log('🔄 No real images found in media, checking other sources...');
    
    // From property.images object structure (fallback)
    if (property.images && typeof property.images === 'object' && !Array.isArray(property.images)) {
      console.log('🖼️ Found images object structure:', property.images);
      
      // Handle nested structure from API
      if (property.images.main && !foundMainImage) {
        console.log('✅ Found main image in images.main:', property.images.main);
        mainImage = fixImageUrl(property.images.main);
        foundMainImage = true;
      }
      
      if (property.images.gallery && Array.isArray(property.images.gallery)) {
        console.log('🖼️ Found gallery images:', property.images.gallery.length, property.images.gallery);
        const galleryImages = property.images.gallery.map((url: string) => fixImageUrl(url)).filter(Boolean);
        images = [...images, ...galleryImages];
      }

      // Handle other possible image fields in the images object
      if (property.images.main_image && !foundMainImage) {
        console.log('✅ Found main_image in images object:', property.images.main_image);
        mainImage = fixImageUrl(property.images.main_image);
        foundMainImage = true;
      }

      // Handle images array within images object
      if (property.images.images && Array.isArray(property.images.images)) {
        console.log('📋 Found nested images array:', property.images.images);
        allMediaItems = [...allMediaItems, ...property.images.images];
      }
    }

    // From property.images as array (fallback)
    if (Array.isArray(property.images)) {
      console.log('📋 Found images as array with', property.images.length, 'items:', property.images);
      const imageUrls = property.images
        .map((item: any) => {
          if (typeof item === 'string') return fixImageUrl(item);
          if (item && typeof item === 'object') {
            return fixImageUrl(item.url || item.src || item.image || item.original_url);
          }
          return null;
        })
        .filter(Boolean);
      
      images = [...images, ...imageUrls];
      if (!foundMainImage && imageUrls.length > 0) {
        mainImage = imageUrls[0];
        foundMainImage = true;
      }
    }

    // If still no main image, try other property fields
    if (!foundMainImage) {
      console.log('🔍 Looking for main image in other property fields');
      
      const possibleMainImages = [
        property.mainImage,
        property.main_image_url,
        property.main_image,
        property.image,
        property.thumbnail,
        property.featured_image,
        property.cover_image,
      ].filter(Boolean);

      console.log('📋 Possible main images found:', possibleMainImages);

      if (possibleMainImages.length > 0) {
        mainImage = fixImageUrl(possibleMainImages[0]);
        foundMainImage = true;
        console.log('✅ Using main image from property fields:', mainImage);
      }
    }

    // If still no main image but we have images in the array, use the first one
    if (!foundMainImage && images.length > 0) {
      mainImage = images[0];
      foundMainImage = true;
      console.log('✅ Using first image from array as main image:', mainImage);
    }

    // If still no images, try other array sources
    if (images.length === 0) {
      console.log('🔍 Looking for images in other array sources');
      
      const possibleImageArrays = [
        property.gallery_urls,
        property.gallery,
        property.image_urls,
        property.photos,
      ].filter(Array.isArray);

      console.log('📋 Found image arrays:', possibleImageArrays.length);

      if (possibleImageArrays.length > 0) {
        const imageArray = possibleImageArrays[0];
        const additionalImages = imageArray
          .map((item: any) => {
            if (typeof item === 'string') return fixImageUrl(item);
            if (item && typeof item === 'object') {
              return fixImageUrl(item.url || item.src || item.image || item.original_url);
            }
            return null;
          })
          .filter(Boolean);
        
        images = [...images, ...additionalImages];
        
        if (!foundMainImage && additionalImages.length > 0) {
          mainImage = additionalImages[0];
          foundMainImage = true;
          console.log('✅ Using first image from additional sources as main image:', mainImage);
        }
      }
    }
  }

  // 3. FINAL FALLBACK: Only use placeholder if no real images were found at all
  if (!foundMainImage) {
    console.log('🔄 No real images found anywhere, using fallback image');
    mainImage = getFallbackImage(propertyType || 'apartment', property.id);
    console.log('✅ Using fallback image:', mainImage);
  }

  // Remove duplicates from images array
  const uniqueImages = Array.from(new Set(images));
  
  // Ensure main image is not duplicated in the images array
  const finalImages = uniqueImages.filter(img => img !== mainImage);

  const result = {
    mainImage,
    images: finalImages
  };

  // Check if we're using real images or fallback images
  const isUsingRealImages = hasRealImages && !result.mainImage.includes('/images/properties/') && !result.mainImage.includes('placeholder');
  
  console.log('🎯 PropertyCard - Processed images for property', property?.id || 'unknown', ':', {
    mainImage: result.mainImage,
    imagesCount: result.images.length,
    hasMainVideo: hasVideos || false,
    propertyTitle: property?.title || 'Unknown',
    hasRealImages: isUsingRealImages,
    foundMainImage: foundMainImage,
    hasMediaArray: !!property.media && property.media.length > 0
  });

  return result;
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