import api from './api';
import { User } from '../types';

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
  totalProperties: number;
  forRent: number;
  forSale: number;
  favoriteProperties: number;
  myProperties: number;
}

export type UserProfile = Partial<Omit<User, 'properties' | 'favorites'>>;

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

  // Get dashboard statistics
  async getDashboardStats(): Promise<DashboardStats> {
    try {
      // Use the available overview endpoint
      const response = await api.get('/dashboard/overview');
      
      // Map the response to match the expected DashboardStats interface
      return {
        totalProperties: response.data.stats.total_properties || 0,
        forRent: response.data.stats.active_properties || 0,
        forSale: response.data.stats.sold_rented_properties || 0,
        favoriteProperties: response.data.stats.total_favorites || 0,
        myProperties: response.data.stats.total_properties || 0
      };
    } catch (error) {
      console.error('Error fetching dashboard stats:', error);
      // Return default values in case of error
      return {
        totalProperties: 0,
        forRent: 0,
        forSale: 0,
        favoriteProperties: 0,
        myProperties: 0
      };
    }
  },

  // Get user's properties
  async getDashboardStatsRaw(): Promise<DashboardStats> {
    try {
      const response = await api.get('/dashboard/stats-raw');
      // Map the response to match the DashboardStats interface
      return {
        totalProperties: response.data.totalProperties || 0,
        forRent: response.data.forRent || 0,
        forSale: response.data.forSale || 0,
        favoriteProperties: response.data.favoriteProperties || 0,
        myProperties: response.data.myProperties || 0
      };
    } catch (error) {
      console.error('Error fetching dashboard stats:', error);
      // Return default values in case of error
      return {
        totalProperties: 0,
        forRent: 0,
        forSale: 0,
        favoriteProperties: 0,
        myProperties: 0
      };
    }
  },

  // Get user's favorite properties
  async getFavoriteProperties(): Promise<any[]> {
    try {
      const response = await api.get('/dashboard/favorites');
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching favorite properties:', error);
      return [];
    }
  },

  // Update user profile
  async updateProfile(profileData: UserProfile): Promise<User> {
    try {
      const response = await api.post('/profile', profileData, { baseURL: (import.meta.env.VITE_API_BASE_URL || 'https://besttrend-sy.com/api/v1').replace('/v1', '') });
      return response.data.user;
    } catch (error) {
      console.error('Error updating profile:', error);
      throw error;
    }
  },

  // Get notifications
  async getNotifications(): Promise<any[]> {
    try {
      const response = await api.get('/notifications');
      return response.data;
    } catch (error) {
      console.error('Error fetching notifications:', error);
      throw error;
    }
  }
};

export default dashboardService;