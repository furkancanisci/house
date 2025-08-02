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
      const response = await api.get('/dashboard/stats');
      return response.data;
    } catch (error) {
      console.error('Error fetching dashboard stats:', error);
      throw error;
    }
  },

  // Get user's properties
  async getDashboardStatsRaw(): Promise<DashboardStats> {
      try {
          const response = await api.get('/dashboard/stats-raw');
          return response.data;
      } catch (error) {
          console.error('Error fetching dashboard stats:', error);
          throw error;
      }
  },

  // Get user's favorite properties
  async getFavoriteProperties(): Promise<any[]> {
    try {
      const response = await api.get('/dashboard/favorites');
      return response.data;
    } catch (error) {
      console.error('Error fetching favorite properties:', error);
      throw error;
    }
  },

  // Update user profile
  async updateProfile(profileData: UserProfile): Promise<User> {
    try {
      const response = await api.put('/user/profile', profileData);
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