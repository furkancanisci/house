import api from './api';

export interface SavedSearch {
  id: string;
  user_id: string;
  name: string;
  search_criteria: {
    property_type?: string;
    price_type?: string;
    min_price?: number;
    max_price?: number;
    governorate?: string;
    city?: string;
    neighborhood?: string;
    bedrooms?: number;
    bathrooms?: number;
    area_min?: number;
    area_max?: number;
    features?: string[];
    utilities?: string[];
    orientation?: string;
    view_type?: string;
    building_type?: string;
    floor_type?: string;
    window_type?: string;
    [key: string]: any;
  };
  notification_enabled: boolean;
  created_at: string;
  updated_at: string;
  matching_properties_count?: number;
}

export interface SavedSearchProperty {
  id: string;
  title: string;
  price: number;
  location: string;
  main_image_url: string;
  property_type: string;
  bedrooms: number;
  bathrooms: number;
  area: number;
  created_at: string;
}

class SavedSearchService {
  /**
   * Get all saved searches for the authenticated user
   */
  async getSavedSearches(): Promise<{ success: boolean; data?: SavedSearch[]; message?: string }> {
    try {
      const response = await api.get('/v1/saved-searches');
      return response.data;
    } catch (error: any) {
  
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to get saved searches'
      };
    }
  }

  /**
   * Get a specific saved search by ID
   */
  async getSavedSearch(id: string): Promise<{ success: boolean; data?: SavedSearch; message?: string }> {
    try {
      const response = await api.get(`/v1/saved-searches/${id}`);
      return response.data;
    } catch (error: any) {
  
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to get saved search'
      };
    }
  }

  /**
   * Create a new saved search
   */
  async createSavedSearch(data: {
    name: string;
    search_criteria: Record<string, any>;
    notification_enabled?: boolean;
  }): Promise<{ success: boolean; data?: SavedSearch; message?: string }> {
    try {
      const response = await api.post('/v1/saved-searches', data);
      return response.data;
    } catch (error: any) {
  
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to create saved search'
      };
    }
  }

  /**
   * Update an existing saved search
   */
  async updateSavedSearch(
    id: string,
    data: {
      name?: string;
      search_criteria?: Record<string, any>;
      notification_enabled?: boolean;
    }
  ): Promise<{ success: boolean; data?: SavedSearch; message?: string }> {
    try {
      const response = await api.put(`/v1/saved-searches/${id}`, data);
      return response.data;
    } catch (error: any) {
  
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to update saved search'
      };
    }
  }

  /**
   * Delete a saved search
   */
  async deleteSavedSearch(id: string): Promise<{ success: boolean; message?: string }> {
    try {
      const response = await api.delete(`/v1/saved-searches/${id}`);
      return response.data;
    } catch (error: any) {
  
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to delete saved search'
      };
    }
  }

  /**
   * Execute a saved search and get matching properties
   */
  async executeSavedSearch(
    id: string,
    page: number = 1,
    limit: number = 20
  ): Promise<{ 
    success: boolean; 
    data?: {
      properties: SavedSearchProperty[];
      pagination: {
        current_page: number;
        total_pages: number;
        total_count: number;
        per_page: number;
      };
    }; 
    message?: string 
  }> {
    try {
      const response = await api.get(`/v1/saved-searches/${id}/execute`, {
        params: { page, limit }
      });
      return response.data;
    } catch (error: any) {
  
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to execute saved search'
      };
    }
  }

  /**
   * Get count of matching properties for a saved search
   */
  async getSavedSearchCount(id: string): Promise<{ success: boolean; data?: { count: number }; message?: string }> {
    try {
      const response = await api.get(`/v1/saved-searches/${id}/count`);
      return response.data;
    } catch (error: any) {
  
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to get saved search count'
      };
    }
  }

  /**
   * Toggle notifications for a saved search
   */
  async toggleNotifications(id: string): Promise<{ success: boolean; data?: SavedSearch; message?: string }> {
    try {
      const response = await api.post(`/v1/saved-searches/${id}/toggle-notifications`);
      return response.data;
    } catch (error: any) {
  
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to toggle notifications'
      };
    }
  }

  /**
   * Format search criteria for display
   */
  formatSearchCriteria(criteria: Record<string, any>): string {
    const parts: string[] = [];

    if (criteria.property_type) {
      parts.push(`Type: ${criteria.property_type}`);
    }

    if (criteria.min_price || criteria.max_price) {
      const priceRange = [];
      if (criteria.min_price) priceRange.push(`$${criteria.min_price.toLocaleString()}+`);
      if (criteria.max_price) priceRange.push(`up to $${criteria.max_price.toLocaleString()}`);
      parts.push(`Price: ${priceRange.join(' ')}`);
    }

    if (criteria.city) {
      parts.push(`City: ${criteria.city}`);
    }

    if (criteria.neighborhood) {
      parts.push(`Area: ${criteria.neighborhood}`);
    }

    if (criteria.bedrooms) {
      parts.push(`${criteria.bedrooms} bed${criteria.bedrooms > 1 ? 's' : ''}`);
    }

    if (criteria.bathrooms) {
      parts.push(`${criteria.bathrooms} bath${criteria.bathrooms > 1 ? 's' : ''}`);
    }

    if (criteria.area_min || criteria.area_max) {
      const areaRange = [];
      if (criteria.area_min) areaRange.push(`${criteria.area_min}m²+`);
      if (criteria.area_max) areaRange.push(`up to ${criteria.area_max}m²`);
      parts.push(`Area: ${areaRange.join(' ')}`);
    }

    if (criteria.features && criteria.features.length > 0) {
      parts.push(`Features: ${criteria.features.slice(0, 2).join(', ')}${criteria.features.length > 2 ? '...' : ''}`);
    }

    return parts.length > 0 ? parts.join(' • ') : 'All properties';
  }
}

export default new SavedSearchService();