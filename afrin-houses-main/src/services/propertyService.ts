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
    
    // Handle search query - use multiple parameter names for compatibility
    const searchQuery = filters.searchQuery || filters.search;
    if (searchQuery) {
      console.log('Search query found:', searchQuery);
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
    
    if (filters.location) {
      params.location = filters.location;
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
      params.bedrooms = filters.minBeds;
    }
    
    if (filters.maxBeds !== undefined && filters.maxBeds !== 'any' && filters.maxBeds !== '') {
      params.maxBedrooms = filters.maxBeds;
    }
    
    // Bathrooms - handle 'any' values and numeric values
    if (filters.minBaths !== undefined && filters.minBaths !== 'any' && filters.minBaths !== '') {
      params.bathrooms = filters.minBaths;
    }
    
    if (filters.maxBaths !== undefined && filters.maxBaths !== 'any' && filters.maxBaths !== '') {
      params.maxBathrooms = filters.maxBaths;
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
  } catch (error) {
    console.error('Error fetching properties:', error);
    throw error;
  }
};

export const getProperty = async (slugOrId: string) => {
  try {
    // Try to determine if it's a slug or ID
    const isNumericId = /^\d+$/.test(slugOrId);
    const endpoint = isNumericId ? `/properties/${slugOrId}/show` : `/properties/${slugOrId}`;
    
    console.log(`Fetching property from: ${endpoint}`);
    const response = await api.get(endpoint);
    const property = response.data.property || response.data;
    
    console.log('Raw property data from API:', property);
    
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
    console.error(`Error fetching property ${slugOrId}:`, error);
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
        // Handle boolean values properly for Laravel validation
        if (typeof propertyData[key] === 'boolean') {
          formData.append(key, propertyData[key] ? '1' : '0');
        } else {
          formData.append(key, propertyData[key].toString());
        }
      }
    });
    
    console.log('Sending property data to API with FormData');
    const response = await api.post('/properties', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  } catch (error) {
    console.error('Error creating property:', error);
    throw error;
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
        // Handle boolean values properly for Laravel validation
        if (typeof propertyData[key] === 'boolean') {
          formData.append(key, propertyData[key] ? '1' : '0');
        } else {
          formData.append(key, propertyData[key].toString());
        }
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
    
    console.log('Raw user properties from API:', properties);
    
    // Transform and fix image URLs in all user properties
    return properties.map((property: any) => {
      // Fix image URLs
      let mainImage = '/placeholder-property.jpg';
      if (property.images) {
        const fixedImages = { ...property.images };
        
        // Fix main image URL
        if (fixedImages.main) {
          fixedImages.main = fixImageUrl(fixedImages.main);
          mainImage = fixedImages.main;
        }
        
        // Fix gallery image URLs
        if (fixedImages.gallery && Array.isArray(fixedImages.gallery)) {
          fixedImages.gallery = fixedImages.gallery.map(fixImageObject);
          if (fixedImages.gallery.length > 0 && !mainImage) {
            mainImage = fixedImages.gallery[0];
          }
        }
        
        property.images = fixedImages;
      }
      
      // Transform the property to match the expected format in Dashboard
      return {
        id: property.id,
        title: property.title || 'Untitled Property',
        description: property.description || '',
        price: typeof property.price === 'object' ? property.price.amount : property.price,
        address: property.location?.full_address || property.address || `${property.city || ''}, ${property.state || ''}`.trim(),
        city: property.city,
        state: property.state,
        listingType: property.listing_type || property.listingType,
        propertyType: property.property_type || property.propertyType,
        bedrooms: property.bedrooms || 0,
        bathrooms: property.bathrooms || 0,
        squareFootage: property.square_feet || property.squareFootage || 0,
        mainImage: mainImage,
        images: property.images,
        status: property.status,
        created_at: property.created_at,
        updated_at: property.updated_at,
        slug: property.slug
      };
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
