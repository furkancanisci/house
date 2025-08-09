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
export const fixImageUrl = (url: string | undefined): string => {
  if (!url) return '';
  
  // Don't process already processed URLs
  if (url.startsWith('http://localhost:8000/') ||
      url.startsWith('https://') ||
      url.startsWith('data:') ||
      url.startsWith('/images/')) {
    return url;
  }
  
  // Replace localhost URLs with localhost:8000
  if (url.startsWith('http://localhost/')) {
    return url.replace('http://localhost/', 'http://localhost:8000/');
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

  // Try to get images array from various sources
  const possibleImageArrays = [
    property.images,
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

  // If no main image, try to use first image from array
  if (!mainImage && images.length > 0) {
    mainImage = images[0];
  }

  // If still no main image, use fallback based on property type
  if (!mainImage) {
    const type = propertyType || property.propertyType || property.property_type || property.type || 'apartment';
    mainImage = getFallbackImage(type, property.id);
  }

  // If no images array but we have a main image, add it to the array
  if (images.length === 0 && mainImage) {
    images = [mainImage];
  }

  // Add some additional fallback images for variety
  if (images.length < 3) {
    const type = propertyType || property.propertyType || property.property_type || property.type || 'apartment';
    const typeImages = PROPERTY_TYPE_IMAGES[type.toLowerCase()] || PROPERTY_IMAGES;
    const additionalImages = typeImages.slice(0, 3 - images.length);
    images = [...images, ...additionalImages];
  }

  return {
    mainImage,
    images: [...new Set(images)], // Remove duplicates
  };
};

/**
 * Get a random property image for demo purposes
 */
export const getRandomPropertyImage = (): string => {
  if (PROPERTY_IMAGES.length === 0) {
    return PLACEHOLDER_IMAGE;
  }
  const randomIndex = Math.floor(Math.random() * PROPERTY_IMAGES.length);
  return PROPERTY_IMAGES[randomIndex];
};