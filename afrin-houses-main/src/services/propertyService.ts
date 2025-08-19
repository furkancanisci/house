import api from './api';

// Utility function to fix image URLs
const fixImageUrl = (url: string | null | undefined | any): string => {
  // Check if url is not a string or is empty
  if (!url || typeof url !== 'string') return '/placeholder-property.jpg';
  
  // Replace localhost URLs with localhost:8000
  if (url.startsWith('http://localhost/')) {
    return url.replace('http://localhost/', 'http://localhost:8000/');
  }
  
  return url;
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
  minPrice?: number;
  maxPrice?: number;
  minBeds?: number;
  maxBeds?: number;
  minBaths?: number;
  maxBaths?: number;
  minSquareFeet?: number;
  maxSquareFeet?: number;
  features?: string[];
  location?: string;
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
    
    // Handle search query - use 'search' parameter to match backend expectation
    const searchQuery = filters.searchQuery || filters.search;
    if (searchQuery) {
      console.log('Search query found:', searchQuery);
      // Use 'search' parameter to match backend expectation
      params.search = searchQuery;
      // Also include 'q' parameter for backward compatibility
      params.q = searchQuery;
    }
    
    // Basic filters
    if (filters.listingType && filters.listingType !== 'all') {
      params.listingType = filters.listingType;
    }
    
    if (filters.propertyType && filters.propertyType !== 'all') {
      params.propertyType = filters.propertyType;
    }
    
    if (filters.location) {
      params.location = filters.location;
    }
    
    // Price range
    if (filters.minPrice !== undefined) {
      params.minPrice = filters.minPrice;
    }
    
    if (filters.maxPrice !== undefined) {
      params.maxPrice = filters.maxPrice;
    }
    
    // Bedrooms
    if ((filters as any).bedrooms !== undefined) {
      params.min_bedrooms = (filters as any).bedrooms;
    }
    
    
    // Bathrooms
    if ((filters as any).bathrooms !== undefined) {
      params.min_bathrooms = (filters as any).bathrooms;
    }
    
    
    // Square footage
    if ((filters as any).minSquareFootage !== undefined) {
      params.min_square_footage = (filters as any).minSquareFootage;
    }
    
    if ((filters as any).maxSquareFootage !== undefined) {
      params.max_square_footage = (filters as any).maxSquareFootage;
    }
    
    // Features/amenities
    if (filters.features && filters.features.length > 0) {
      params.amenities = filters.features.join(',');
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
    
    console.log('Sending property filters to API:', params);
    const response = await api.get('/properties', { params });
    
    // Log the full response for debugging
    console.log('API Response:', response);
    
    // Ensure we always return a consistent response structure
    if (!response || !response.data) {
      console.warn('No data in API response');
      return [];
    }
    
    // Handle different response structures and fix image URLs
    let properties = [];
    
    // Check if the response has a data property that contains the array of properties
    if (response.data.data && Array.isArray(response.data.data)) {
      // Handle paginated response (Laravel default)
      console.log('Found properties in response.data.data');
      properties = response.data.data;
    } else if (Array.isArray(response.data)) {
      // Handle direct array response
      console.log('Found properties directly in response.data');
      properties = response.data;
    } else if (response.data.properties && Array.isArray(response.data.properties)) {
      // Handle response with properties key (alternative format)
      console.log('Found properties in response.data.properties');
      properties = response.data.properties;
    } else {
      console.warn('Unexpected API response structure. Response data:', response.data);
      return [];
    }
    
    console.log(`Found ${properties.length} properties in API response`);
    
    // Fix image URLs in all properties
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
    console.error('Error fetching properties:', error);
    throw error;
  }
};

export const getProperty = async (slug: string) => {
  try {
    const response = await api.get(`/properties/${slug}`);
    const property = response.data;
    
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
    
    return property;
  } catch (error) {
    console.error(`Error fetching property ${slug}:`, error);
    throw error;
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
  } catch (error) {
    console.error('Error fetching featured properties:', error);
    throw error;
  }
};

export const createProperty = async (propertyData: any) => {
  try {
    console.log('Starting createProperty with data:', JSON.parse(JSON.stringify(propertyData, (key, value) => 
      value instanceof File ? `[File ${value.name}]` : value
    )));
    
    const formData = new FormData();
    
    // Add all property data to FormData
    Object.keys(propertyData).forEach(key => {
      if (key === 'amenities' && Array.isArray(propertyData[key])) {
        // Handle amenities array
        propertyData[key].forEach((amenity: string, index: number) => {
          formData.append(`amenities[${index}]`, amenity);
        });
      } else if (key === 'images' && Array.isArray(propertyData[key])) {
        // Handle image files
        propertyData[key].forEach((file: File) => {
          formData.append('images[]', file);
        });
      } else if (key === 'mainImage' && propertyData[key] instanceof File) {
        // Handle main image file
        formData.append('main_image', propertyData[key]);
      } else if (propertyData[key] !== null && propertyData[key] !== undefined) {
        // Convert nested objects to JSON strings
        if (typeof propertyData[key] === 'object') {
          formData.append(key, JSON.stringify(propertyData[key]));
        } else {
          formData.append(key, propertyData[key].toString());
        }
      }
    });
    
    // Log FormData contents for debugging
    console.log('FormData contents:');
    for (let pair of formData.entries()) {
      console.log(`${pair[0]}:`, pair[1]);
    }
    
    console.log('Sending property data to API with FormData');
    const response = await api.post('/properties', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      timeout: 30000, // 30 second timeout
    });
    
    console.log('Property created successfully:', response.data);
    return response.data;
  } catch (error: any) {
    console.error('Error in createProperty:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status,
      config: {
        url: error.config?.url,
        method: error.config?.method,
        headers: error.config?.headers,
        data: error.config?.data,
      },
    });
    
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
      } else if (key === 'images' && Array.isArray(propertyData[key])) {
        // Handle new image files
        propertyData[key].forEach((file: File) => {
          formData.append('images[]', file);
        });
      } else if (key === 'mainImage' && propertyData[key] instanceof File) {
        // Handle main image file
        formData.append('main_image', propertyData[key]);
      } else if (key === 'imagesToRemove' && Array.isArray(propertyData[key])) {
        // Handle images to remove
        propertyData[key].forEach((imageId: string, index: number) => {
          formData.append(`remove_images[${index}]`, imageId);
        });
      } else if (propertyData[key] !== null && propertyData[key] !== undefined) {
        formData.append(key, propertyData[key].toString());
      }
    });
    
    console.log('Sending property update data to API with FormData');
    const response = await api.put(`/properties/${id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  } catch (error) {
    console.error('Error updating property:', error);
    throw error;
  }
};

export const deleteProperty = async (id: number) => {
  try {
    const response = await api.delete(`/properties/${id}`);
    return response.data;
  } catch (error) {
    console.error('Error deleting property:', error);
    throw error;
  }
};

export const toggleFavorite = async (id: number) => {
  try {
    const response = await api.post(`/properties/${id}/favorite`);
    return response.data;
  } catch (error) {
    console.error('Error toggling favorite:', error);
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
  } catch (error) {
    console.error('Error fetching favorite properties:', error);
    throw error;
  }
};

export const getUserProperties = async () => {
  try {
    const response = await api.get('/dashboard/properties');
    const properties = response.data.data || [];
    
    // Fix image URLs in all user properties
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
    console.error('Error fetching user properties:', error);
    throw error;
  }
};

export const getPropertyAnalytics = async (id: number) => {
  try {
    const response = await api.get(`/properties/${id}/analytics`);
    return response.data;
  } catch (error) {
    console.error('Error fetching property analytics:', error);
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
    console.error('Error fetching similar properties:', error);
    throw error;
  }
};

export const getAmenities = async () => {
  try {
    const response = await api.get('/properties/amenities');
    return response.data || [];
  } catch (error) {
    console.error('Error fetching amenities:', error);
    throw error;
  }
};
