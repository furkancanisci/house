import api from './api';

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
}

export const getProperties = async (filters: PropertyFilters = {}) => {
  try {
    // Map frontend filters to backend API parameters
    const params: Record<string, any> = {};
    
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
    if (filters.minBeds !== undefined) {
      params.minBedrooms = filters.minBeds;
    }
    
    if (filters.maxBeds !== undefined) {
      params.maxBedrooms = filters.maxBeds;
    }
    
    // Bathrooms
    if (filters.minBaths !== undefined) {
      params.minBathrooms = filters.minBaths;
    }
    
    if (filters.maxBaths !== undefined) {
      params.maxBathrooms = filters.maxBaths;
    }
    
    // Square footage
    if (filters.minSquareFeet !== undefined) {
      params.minSquareFootage = filters.minSquareFeet;
    }
    
    if (filters.maxSquareFeet !== undefined) {
      params.maxSquareFootage = filters.maxSquareFeet;
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
    
    // Ensure we always return a consistent response structure
    if (!response || !response.data) {
      return [];
    }
    
    // Handle different response structures
    if (Array.isArray(response.data)) {
      return response.data;
    } else if (response.data.data && Array.isArray(response.data.data)) {
      // Handle Laravel paginated response
      return response.data.data;
    }
    
    console.warn('Unexpected API response structure:', response.data);
    return [];
  } catch (error) {
    console.error('Error fetching properties:', error);
    throw error;
  }
};

export const getProperty = async (slug: string) => {
  try {
    const response = await api.get(`/properties/${slug}`);
    return response.data;
  } catch (error) {
    console.error(`Error fetching property ${slug}:`, error);
    throw error;
  }
};

export const getFeaturedProperties = async (limit = 6) => {
  try {
    const response = await api.get('/properties/featured', { params: { limit } });
    // The backend returns a paginated response with data in response.data.data
    return response.data.data || [];
  } catch (error) {
    console.error('Error fetching featured properties:', error);
    throw error;
  }
};

export const createProperty = async (propertyData: any) => {
  try {
    // Ensure we're sending the correct parameter names to the backend
    const formattedData = {
      ...propertyData,
      // Add any transformations needed here
      amenities: propertyData.amenities || [],
    };
    
    console.log('Sending property data to API:', formattedData);
    const response = await api.post('/properties', formattedData);
    return response.data;
  } catch (error) {
    console.error('Error creating property:', error);
    throw error;
  }
};

export const updateProperty = async (id: number, propertyData: any) => {
  try {
    // Ensure we're sending the correct parameter names to the backend
    const formattedData = {
      ...propertyData,
      // Add any transformations needed here
      amenities: propertyData.amenities || [],
    };
    
    console.log('Sending property update data to API:', formattedData);
    const response = await api.put(`/properties/${id}`, formattedData);
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
    return response.data.data || [];
  } catch (error) {
    console.error('Error fetching favorite properties:', error);
    throw error;
  }
};

export const getUserProperties = async () => {
  try {
    const response = await api.get('/dashboard/properties');
    return response.data.data || [];
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
    return response.data.data || [];
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
