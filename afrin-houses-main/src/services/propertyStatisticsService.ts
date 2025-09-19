import api from './api';

export interface PropertyStatistics {
  property_id: string;
  views_count: number;
  inquiries_count: number;
  favorites_count: number;
  engagement_score: number;
  last_viewed_at: string | null;
}

export interface DashboardStatistics {
  summary: {
    total_properties: number;
    total_views: number;
    total_inquiries: number;
    total_favorites: number;
    average_views_per_property: number;
  };
  properties: Array<{
    id: string;
    title: string;
    views_count: number;
    inquiries_count: number;
    favorites_count: number;
    engagement_score: number;
    last_viewed_at: string | null;
  }>;
  top_performing: Array<{
    id: string;
    title: string;
    views_count: number;
    inquiries_count: number;
    favorites_count: number;
    engagement_score: number;
    last_viewed_at: string | null;
  }>;
}

export interface PopularProperty {
  property: {
    id: string;
    title: string;
    price: number;
    location: string;
    main_image_url: string;
  };
  statistics: PropertyStatistics;
}

class PropertyStatisticsService {
  /**
   * Track a property view
   */
  async trackView(propertyId: string): Promise<{ success: boolean; data?: any; message?: string }> {
    try {
      const response = await api.post(`/v1/property-statistics/${propertyId}/track-view`);
      return response.data;
    } catch (error: any) {
  
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to track view'
      };
    }
  }

  /**
   * Track a property inquiry
   */
  async trackInquiry(propertyId: string): Promise<{ success: boolean; data?: any; message?: string }> {
    try {
      const response = await api.post(`/v1/property-statistics/${propertyId}/track-inquiry`);
      return response.data;
    } catch (error: any) {
  
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to track inquiry'
      };
    }
  }

  /**
   * Track adding property to favorites
   */
  async trackFavoriteAdd(propertyId: string): Promise<{ success: boolean; data?: any; message?: string }> {
    try {
      const response = await api.post(`/v1/property-statistics/${propertyId}/track-favorite-add`);
      return response.data;
    } catch (error: any) {
  
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to track favorite add'
      };
    }
  }

  /**
   * Track removing property from favorites
   */
  async trackFavoriteRemove(propertyId: string): Promise<{ success: boolean; data?: any; message?: string }> {
    try {
      const response = await api.post(`/v1/property-statistics/${propertyId}/track-favorite-remove`);
      return response.data;
    } catch (error: any) {
  
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to track favorite remove'
      };
    }
  }

  /**
   * Get statistics for a specific property
   */
  async getPropertyStatistics(propertyId: string): Promise<{ success: boolean; data?: PropertyStatistics; message?: string }> {
    try {
      const response = await api.get(`/v1/property-statistics/${propertyId}/statistics`);
      return response.data;
    } catch (error: any) {

      return {
        success: false,
        message: error.response?.data?.message || 'Failed to get property statistics'
      };
    }
  }

  /**
   * Get dashboard statistics for property owner
   */
  async getDashboardStatistics(): Promise<{ success: boolean; data?: DashboardStatistics; message?: string }> {
    try {
      const response = await api.get('/v1/property-statistics/dashboard');
      return response.data;
    } catch (error: any) {
  
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to get dashboard statistics'
      };
    }
  }

  /**
   * Get popular properties based on statistics
   */
  async getPopularProperties(
    limit: number = 10,
    type: 'views' | 'inquiries' | 'favorites' | 'engagement' = 'views'
  ): Promise<{ success: boolean; data?: PopularProperty[]; message?: string }> {
    try {
      const response = await api.get('/v1/property-statistics/popular', {
        params: { limit, type }
      });
      return response.data;
    } catch (error: any) {
  
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to get popular properties'
      };
    }
  }
}

export default new PropertyStatisticsService();