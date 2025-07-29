import api from './api';

export interface Property {
  id: number;
  title: string;
  description: string;
  price: number;
  price_type?: string;
  property_type: string;
  listing_type: string;
  street_address: string;
  city: string;
  state: string;
  postal_code: string;
  country?: string;
  latitude: number;
  longitude: number;
  neighborhood?: string;
  bedrooms: number;
  bathrooms: number;
  square_feet: number;
  lot_size?: number;
  year_built?: number;
  parking_type?: string;
  parking_spaces?: number;
  is_featured: boolean;
  is_available: boolean;
  available_from?: string;
  status: string;
  slug: string;
  amenities?: string[];
  nearby_places?: Array<{
    name: string;
    type: string;
    distance: number;
  }>;
  created_at: string;
  updated_at: string;
  published_at?: string;
  user_id: number;
  // Computed attributes
  full_address?: string;
  formatted_price?: string;
  main_image_url?: string;
  gallery_urls?: Array<{
    id: number;
    url: string;
    thumb: string;
    medium: string;
    large: string;
  }>;
  is_favorited_by_auth_user?: boolean;
  user?: {
    id: number;
    name: string;
    email: string;
    phone?: string;
    avatar?: string;
  };
}

export interface PropertyFilters {
  listingType?: 'rent' | 'sale' | 'all';
  propertyType?: string;
  location?: string;
  minPrice?: number;
  maxPrice?: number;
  bedrooms?: number;
  bathrooms?: number;
  minSquareFootage?: number;
  maxSquareFootage?: number;
  features?: string[];
  search?: string;
  page?: number;
  perPage?: number;
  sortBy?: string;
  sortOrder?: 'asc' | 'desc';
}

export const getProperties = async (filters: PropertyFilters = {}) => {
  try {
    // Map frontend filter names to backend API parameter names
    const params: Record<string, any> = {};
    
    // Map listing type
    if (filters.listingType && filters.listingType !== 'all') {
      params.listing_type = filters.listingType;
    }
    
    // Map property type
    if (filters.propertyType) {
      params.property_type = filters.propertyType;
    }
    
    // Map location (search by city, state, or address)
    if (filters.location) {
      params.search = filters.location;
    }
    
    // Map price range
    if (filters.minPrice !== undefined) {
      params.price_min = filters.minPrice;
    }
    if (filters.maxPrice !== undefined) {
      params.price_max = filters.maxPrice;
    }
    
    // Map bedrooms and bathrooms
    if (filters.bedrooms !== undefined) {
      params.bedrooms = filters.bedrooms;
    }
    if (filters.bathrooms !== undefined) {
      params.bathrooms = filters.bathrooms;
    }
    
    // Map square footage
    if (filters.minSquareFootage !== undefined) {
      params.square_footage_min = filters.minSquareFootage;
    }
    if (filters.maxSquareFootage !== undefined) {
      params.square_footage_max = filters.maxSquareFootage;
    }
    
    // Map features/amenities
    if (filters.features && filters.features.length > 0) {
      params.amenities = filters.features.join(',');
    }
    
    // Map pagination
    if (filters.page) {
      params.page = filters.page;
    }
    if (filters.perPage) {
      params.per_page = filters.perPage;
    }
    
    // Map sorting
    if (filters.sortBy) {
      const order = filters.sortOrder === 'desc' ? '-' : '';
      params.sort = `${order}${filters.sortBy}`;
    }
    
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
    } else if (Array.isArray(response.data)) {
      return response.data;
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

export const createProperty = async (propertyData: Partial<Property>) => {
  try {
    const response = await api.post('/properties', propertyData);
    return response.data;
  } catch (error) {
    console.error('Error creating property:', error);
    throw error;
  }
};

export const updateProperty = async (id: number, propertyData: Partial<Property>) => {
  try {
    const response = await api.put(`/properties/${id}`, propertyData);
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
