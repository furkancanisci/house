import api from './api';

export interface DashboardOverview {
  total_properties: number;
  active_properties: number;
  total_views: number;
  total_favorites: number;
  recent_properties: any[];
  recent_views: any[];
  monthly_stats: {
    month: string;
    views: number;
    favorites: number;
  }[];
}

export interface DashboardStats {
  properties_count: number;
  favorites_count: number;
  views_count: number;
  inquiries_count: number;
}

export interface UserProfile {
  id: number;
  name: string;
  email: string;
  phone?: string;
  bio?: string;
  avatar?: string;
  is_active: boolean;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
}

export const dashboardService = {
  // Get dashboard overview
  async getOverview(): Promise<DashboardOverview> {
    try {
      const response = await api.get('/dashboard/overview');
      return response.data;
    } catch (error) {
      console.error('Error fetching dashboard overview:', error);
      throw error;
    }
  },

  // Get dashboard analytics
  async getAnalytics(): Promise<any> {
    try {
      const response = await api.get('/dashboard/analytics');
      return response.data;
    } catch (error) {
      console.error('Error fetching dashboard analytics:', error);
      throw error;
    }
  },

  // Update user profile
  async updateProfile(profileData: Partial<UserProfile>): Promise<UserProfile> {
    try {
      const response = await api.post('/dashboard/profile', profileData);
      return response.data.user;
    } catch (error) {
      console.error('Error updating profile:', error);
      throw error;
    }
  },

  // Get notifications
  async getNotifications(): Promise<any[]> {
    try {
      const response = await api.get('/dashboard/notifications');
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching notifications:', error);
      throw error;
    }
  },
};

export default dashboardService;