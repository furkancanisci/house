import api from './api';

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

// Utility function to fix image URLs with fallback support
const fixImageUrl = (url: string | null | undefined | any, fallbackType?: string): string => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api/v1', '') || 'https://api.besttrend-sy.com/';
  

  
  // Return appropriate placeholder if no URL is provided
  if (!url || typeof url !== 'string') {
    // For local development, use local placeholder
    if (baseUrl.includes('localhost')) {
      return '/images/placeholder-property.svg';
    }
    // For production, use production placeholder
    return '/images/placeholder-property.svg';
  }
  
  // If URL is already complete and correct, return as-is (MOST IMPORTANT FIX)
  if (url.startsWith('http://') || url.startsWith('https://')) {
    return url;
  }
  
  // Handle relative paths
  if (url.startsWith('/')) {
    const result = `${baseUrl}${url}`;

    return result;
  }
  
  // Handle storage paths
  if (url.startsWith('storage/')) {
    const result = `${baseUrl}/${url}`;

    return result;
  }
  
  // Default case - prepend base URL
  const result = `${baseUrl}/${url}`;

  return result;
};

// Utility function to fix image objects
const fixImageObject = (imageObj: any): any => {
  if (!imageObj) return null;
  
  if (typeof imageObj === 'string') {
    return fixImageUrl(imageObj);
  }
  
  if (typeof imageObj === 'object') {
    const fixed = { ...imageObj };
    if (fixed.url) fixed.url = fixImageUrl(fixed.url);
    if (fixed.thumb) fixed.thumb = fixImageUrl(fixed.thumb);
    if (fixed.medium) fixed.medium = fixImageUrl(fixed.medium);
    if (fixed.large) fixed.large = fixImageUrl(fixed.large);
    return fixed;
  }
  
  return imageObj;
};

// Utility function to fix video objects
const fixVideoObject = (videoObj: any): any => {
  if (!videoObj) return null;
  
  if (typeof videoObj === 'string') {
    return fixImageUrl(videoObj); // Reuse the same URL fixing logic
  }
  
  if (typeof videoObj === 'object') {
    const fixed = { ...videoObj };
    if (fixed.url) fixed.url = fixImageUrl(fixed.url);
    if (fixed.thumbnail) fixed.thumbnail = fixImageUrl(fixed.thumbnail);
    if (fixed.preview) fixed.preview = fixImageUrl(fixed.preview);
    return fixed;
  }
  
  return videoObj;
};

export interface Property {
  id?: number;
  title: string;
  description: string;
  price: number;
  propertyType: string;
  listingType: string;
  address: string;
  city: string;
  state: string;
  postalCode: string;
  bedrooms: number;
  bathrooms: number;
  squareFootage: number;
  yearBuilt: number;
  amenities?: string[];
  images?: any;
  videos?: any;
  contactName?: string;
  contactPhone?: string;
  contactEmail?: string;
  latitude?: number;
  longitude?: number;
  availableDate?: string;
  petPolicy?: string;
  parking?: string;
  lotSize?: string;
  isAvailable?: boolean;
  status?: string;
  isFeatured?: boolean;
  slug?: string;
}

export interface PropertyFilters {
  listingType?: string;
  propertyType?: string;
  priceType?: string;
  minPrice?: number;
  maxPrice?: number;
  minBeds?: number | string;
  maxBeds?: number | string;
  minBaths?: number | string;
  maxBaths?: number | string;
  minSquareFeet?: number;
  maxSquareFeet?: number;
  features?: string[];
  location?: string;
  city?: string;
  state?: string;
  radius?: number;
  sortBy?: string;
  sortOrder?: 'asc' | 'desc';
  page?: number;
  limit?: number;
  search?: string;
  searchQuery?: string;
}

export const getProperties = async (filters: PropertyFilters = {}) => {
  try {
    // Map frontend filters to backend API parameters
    const params: Record<string, any> = {};
    
    // Handle search query - use multiple parameter names for compatibility
    const searchQuery = filters.searchQuery || filters.search;
    if (searchQuery) {
  
      params.searchQuery = searchQuery;
      params.search = searchQuery;
      params.q = searchQuery;
    }
    
    // Basic filters
    if (filters.listingType && filters.listingType !== 'all' && filters.listingType !== 'any') {
      params.listingType = filters.listingType;
    }
    
    if (filters.propertyType && filters.propertyType !== 'all' && filters.propertyType !== 'any') {
      params.propertyType = filters.propertyType;
    }
    
    if (filters.priceType && filters.priceType !== 'all' && filters.priceType !== 'any') {
      params.priceType = filters.priceType;
    }
    
    if (filters.location) {
      params.location = filters.location;
    }
    
    // City and state filters
    if (filters.city) {
      params.city = filters.city;
    }
    
    if (filters.state) {
      params.state = filters.state;
    }
    
    // Price range - only send if values are greater than 0
    if (filters.minPrice !== undefined && filters.minPrice > 0) {
      params.minPrice = filters.minPrice;
    }
    
    if (filters.maxPrice !== undefined && filters.maxPrice > 0) {
      params.maxPrice = filters.maxPrice;
    }
    
    // Bedrooms - handle 'any' values and numeric values
    if (filters.minBeds !== undefined && filters.minBeds !== 'any' && filters.minBeds !== '') {
      params.bedrooms = Number(filters.minBeds);
    }
    
    if (filters.maxBeds !== undefined && filters.maxBeds !== 'any' && filters.maxBeds !== '') {
      params.maxBedrooms = Number(filters.maxBeds);
    }
    
    // Bathrooms - handle 'any' values and numeric values
    if (filters.minBaths !== undefined && filters.minBaths !== 'any' && filters.minBaths !== '') {
      params.bathrooms = Number(filters.minBaths);
    }
    
    if (filters.maxBaths !== undefined && filters.maxBaths !== 'any' && filters.maxBaths !== '') {
      params.maxBathrooms = Number(filters.maxBaths);
    }
    
    // Square footage - only send if values are greater than 0
    if (filters.minSquareFeet !== undefined && filters.minSquareFeet > 0) {
      params.minSquareFootage = filters.minSquareFeet;
    }
    
    if (filters.maxSquareFeet !== undefined && filters.maxSquareFeet > 0) {
      params.maxSquareFootage = filters.maxSquareFeet;
    }
    
    // Features/amenities - send as array for better backend processing
    if (filters.features && filters.features.length > 0) {
      params.features = filters.features;
      params.amenities = filters.features; // Also send as amenities for backward compatibility
    }
    
    // Radius for location search
    if (filters.radius) {
      params.radius = filters.radius;
    }
    
    // Pagination
    if (filters.page) {
      params.page = filters.page;
    }
    
    if (filters.limit) {
      params.limit = filters.limit;
    }
    
    // Sorting
    if (filters.sortBy) {
      params.sortBy = filters.sortBy;
      if (filters.sortOrder) {
        params.sortOrder = filters.sortOrder;
      }
    }
    

    const response = await api.get('/properties', { params });
    
    // Log the full response for debugging

    
    // Ensure we always return a consistent response structure
    if (!response || !response.data) {

      return [];
    }
    
    // Handle different response structures and fix image URLs
    let properties = [];
    
    // Check if the response has a data property that contains the array of properties
    if (response.data.data && Array.isArray(response.data.data)) {
      // Handle paginated response (Laravel default)

      properties = response.data.data;
    } else if (Array.isArray(response.data)) {
      // Handle direct array response

      properties = response.data;
    } else if (response.data.properties && Array.isArray(response.data.properties)) {
      // Handle response with properties key (alternative format)

      properties = response.data.properties;
    } else {

      return [];
    }
    

    
    // Fix image URLs in all properties
    const processedProperties = properties.map((property: any) => {
      if (property.images) {
        const fixedImages = { ...property.images };
        
        // Fix main image URL
        if (fixedImages.main) {
          fixedImages.main = fixImageUrl(fixedImages.main);
        }
        
        // Fix gallery image URLs
        if (fixedImages.gallery && Array.isArray(fixedImages.gallery)) {
          fixedImages.gallery = fixedImages.gallery.map(fixImageObject);
        }
        
        property.images = fixedImages;
      }
      
      return property;
    });
    
    // Return data in the expected format for AppContext
    return {
      data: processedProperties,
      meta: response.data.meta || {},
      links: response.data.links || {},
      filters: response.data.filters || {}
    };
  } catch (error: any) {

    
    // Return fallback data instead of throwing
    return {
      data: [],
      meta: {},
      links: {},
      filters: {},
      error: 'فشل في تحميل العقارات. يرجى المحاولة مرة أخرى.'
    };
  }
};

export const getProperty = async (slugOrId: string) => {
  try {
    // Try to determine if it's a slug or ID
    const isNumericId = /^\d+$/.test(slugOrId);
    const endpoint = isNumericId ? `/properties/${slugOrId}/show` : `/properties/${slugOrId}`;
    

    const response = await api.get(endpoint);
    const property = response.data.property || response.data;
    

    
    // Fix image URLs in the property
    if (property && property.images) {
      const fixedImages = { ...property.images };
      
      // Fix main image URL
      if (fixedImages.main) {
        fixedImages.main = fixImageUrl(fixedImages.main);
      }
      
      // Fix gallery image URLs
      if (fixedImages.gallery && Array.isArray(fixedImages.gallery)) {
        fixedImages.gallery = fixedImages.gallery.map(fixImageObject);
      }
      
      property.images = fixedImages;
    }
    
    // Fix video URLs in the property
    if (property && property.videos) {
      const fixedVideos = { ...property.videos };
      
      // Fix video gallery URLs
      if (fixedVideos.gallery && Array.isArray(fixedVideos.gallery)) {
        fixedVideos.gallery = fixedVideos.gallery.map(fixVideoObject);
      }
      
      property.videos = fixedVideos;
    }
    
    return property;
  } catch (error: any) {

    
    // Return null for not found properties
    if (error.response?.status === 404) {
      return null;
    }
    
    throw new Error('فشل في تحميل تفاصيل العقار. يرجى المحاولة مرة أخرى.');
  }
};

interface FeaturedPropertiesParams {
  limit?: number;
  search?: string;
  q?: string;
}

export const getFeaturedProperties = async (params: FeaturedPropertiesParams = {}) => {
  try {
    const { limit = 6, search, q } = params;
    const response = await api.get('/properties/featured', { 
      params: { 
        limit,
        search: search || q || undefined // Send search query if provided
      } 
    });
    
    // The backend returns a paginated response with data in response.data.data
    const properties = response.data.data || [];
    
    // Fix image URLs in all featured properties
    return properties.map((property: any) => {
      if (property.images) {
        const fixedImages = { ...property.images };
        
        // Fix main image URL
        if (fixedImages.main) {
          fixedImages.main = fixImageUrl(fixedImages.main);
        }
        
        // Fix gallery image URLs
        if (fixedImages.gallery && Array.isArray(fixedImages.gallery)) {
          fixedImages.gallery = fixedImages.gallery.map(fixImageObject);
        }
        
        property.images = fixedImages;
      }
      
      return property;
    });
  } catch (error: any) {

    
    // Return empty array as fallback
    return [];
  }
};

export const createProperty = async (propertyData: any) => {
  try {
    // Check if propertyData is already FormData
    let formData: FormData;
    
    if (propertyData instanceof FormData) {
      // If it's already FormData, use it directly
      formData = propertyData;
  
    } else {
      // If it's a plain object, convert it to FormData
      formData = new FormData();
      
      // Add all property data to FormData
      Object.keys(propertyData).forEach(key => {
        if (key === 'amenities' && Array.isArray(propertyData[key])) {
          // Handle amenities array
          propertyData[key].forEach((amenity: string, index: number) => {
            formData.append(`amenities[${index}]`, amenity);
          });
        } else if (key === 'features' && Array.isArray(propertyData[key])) {
          // Handle features array
          propertyData[key].forEach((feature: number, index: number) => {
            formData.append(`features[${index}]`, feature.toString());
          });
        } else if (key === 'utilities' && Array.isArray(propertyData[key])) {
          // Handle utilities array
          propertyData[key].forEach((utility: number, index: number) => {
            formData.append(`utilities[${index}]`, utility.toString());
          });
        } else if (key === 'features' && Array.isArray(propertyData[key])) {
          // Handle features array
          propertyData[key].forEach((feature: number, index: number) => {
            formData.append(`features[${index}]`, feature.toString());
          });
        } else if (key === 'utilities' && Array.isArray(propertyData[key])) {
          // Handle utilities array
          propertyData[key].forEach((utility: number, index: number) => {
            formData.append(`utilities[${index}]`, utility.toString());
          });
        } else if (key === 'images' && Array.isArray(propertyData[key])) {
          // Handle image files
          propertyData[key].forEach((file: File, index: number) => {
            formData.append(`images[${index}]`, file);
          });
        } else if (key === 'videos' && Array.isArray(propertyData[key])) {
          // Handle video files
          propertyData[key].forEach((file: File, index: number) => {
            formData.append(`videos[${index}]`, file);
          });
        } else if (key === 'mainImage' && isFile(propertyData[key])) {
          // Handle main image file
          formData.append('main_image', propertyData[key]);
        } else if (propertyData[key] !== null && propertyData[key] !== undefined) {
          // Handle boolean values properly for Laravel validation
          if (typeof propertyData[key] === 'boolean') {
            formData.append(key, propertyData[key] ? '1' : '0');
          } else {
            formData.append(key, propertyData[key].toString());
          }
        }
      });
    }
    
    // Log FormData contents for debugging
    console.log('🔍 PropertyService: About to POST to /properties');
    console.log('📋 FormData entries:');
    for (const pair of formData.entries()) {
      if (pair[1] instanceof File) {
        console.log(`  ${pair[0]}: File(${pair[1].name}, ${pair[1].size} bytes)`);
      } else {
        console.log(`  ${pair[0]}: ${pair[1]}`);
      }
    }
    
    console.log('🚀 Making API call to POST /properties...');
    const response = await api.post('/properties', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      timeout: 30000, // 30 second timeout
    });
    

    return response.data;
  } catch (error: any) {

    
    // Create a more detailed error object
    const enhancedError = new Error(error.message || 'Failed to create property');
    (enhancedError as any).response = error.response;
    throw enhancedError;
  }
};

export const updateProperty = async (id: number, propertyData: any) => {
  try {
    const formData = new FormData();
    
    // Add all property data to FormData
    Object.keys(propertyData).forEach(key => {
      if (key === 'amenities' && Array.isArray(propertyData[key])) {
        // Handle amenities array
        propertyData[key].forEach((amenity: string, index: number) => {
          formData.append(`amenities[${index}]`, amenity);
        });
      } else if (key === 'features' && Array.isArray(propertyData[key])) {
        // Handle features array
        propertyData[key].forEach((feature: number, index: number) => {
          formData.append(`features[${index}]`, feature.toString());
        });
      } else if (key === 'utilities' && Array.isArray(propertyData[key])) {
        // Handle utilities array
        propertyData[key].forEach((utility: number, index: number) => {
          formData.append(`utilities[${index}]`, utility.toString());
        });
      } else if (key === 'images' && Array.isArray(propertyData[key])) {
        // Handle new image files
        propertyData[key].forEach((file: File, index: number) => {
          formData.append(`images[${index}]`, file);
        });
      } else if (key === 'videos' && Array.isArray(propertyData[key])) {
        // Handle new video files
        propertyData[key].forEach((file: File, index: number) => {
          formData.append(`videos[${index}]`, file);
        });
      } else if (key === 'mainImage' && isFile(propertyData[key])) {
        // Handle main image file
        formData.append('main_image', propertyData[key]);
      } else if (key === 'imagesToRemove' && Array.isArray(propertyData[key])) {
        // Handle images to remove
        propertyData[key].forEach((imageId: string, index: number) => {
          formData.append(`remove_images[${index}]`, imageId);
        });
      } else if (key === 'videosToRemove' && Array.isArray(propertyData[key])) {
        // Handle videos to remove
        propertyData[key].forEach((videoId: string, index: number) => {
          formData.append(`remove_videos[${index}]`, videoId);
        });
      } else if (propertyData[key] !== null && propertyData[key] !== undefined) {
        // Handle boolean values properly for Laravel validation
        if (typeof propertyData[key] === 'boolean') {
          formData.append(key, propertyData[key] ? '1' : '0');
        } else {
          formData.append(key, propertyData[key].toString());
        }
      }
    });
    

    const response = await api.put(`/properties/${id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  } catch (error) {

    throw error;
  }
};

export const deleteProperty = async (id: number) => {
  try {
    const response = await api.delete(`/properties/${id}`);
    return response.data;
  } catch (error) {

    throw error;
  }
};

export const toggleFavorite = async (id: number) => {
  try {

    
    // Get current token for debugging
    const token = localStorage.getItem('token');

    
    const response = await api.post(`/properties/${id}/favorite`);

    
    return response.data;
  } catch (error: any) {



    
    // If it's an auth error, clear local auth data
    if (error.response?.status === 401) {

      localStorage.removeItem('token');
      localStorage.removeItem('user');
    }
    
    throw error;
  }
};

export const getFavoriteProperties = async () => {
  try {
    const response = await api.get('/dashboard/favorites');
    const properties = response.data.data || [];
    
    // Fix image URLs in all favorite properties
    return properties.map((property: any) => {
      if (property.images) {
        const fixedImages = { ...property.images };
        
        // Fix main image URL
        if (fixedImages.main) {
          fixedImages.main = fixImageUrl(fixedImages.main);
        }
        
        // Fix gallery image URLs
        if (fixedImages.gallery && Array.isArray(fixedImages.gallery)) {
          fixedImages.gallery = fixedImages.gallery.map(fixImageObject);
        }
        
        property.images = fixedImages;
      }
      
      return property;
    });
  } catch (error: any) {

    // Return empty array as fallback
    return [];
  }
};

export const getPropertyAnalytics = async (id: number) => {
  try {
    const response = await api.get(`/properties/${id}/analytics`);
    return response.data;
  } catch (error) {

    throw error;
  }
};

export const getSimilarProperties = async (slug: string) => {
  try {
    const response = await api.get(`/properties/${slug}/similar`);
    const properties = response.data.data || [];
    
    // Fix image URLs in all similar properties
    return properties.map((property: any) => {
      if (property.images) {
        const fixedImages = { ...property.images };
        
        // Fix main image URL
        if (fixedImages.main) {
          fixedImages.main = fixImageUrl(fixedImages.main);
        }
        
        // Fix gallery image URLs
        if (fixedImages.gallery && Array.isArray(fixedImages.gallery)) {
          fixedImages.gallery = fixedImages.gallery.map(fixImageObject);
        }
        
        property.images = fixedImages;
      }
      
      return property;
    });
  } catch (error) {

    throw error;
  }
};

export const getAmenities = async () => {
  try {
    const response = await api.get('/properties/amenities');
    return response.data || [];
  } catch (error) {

    throw error;
  }
};