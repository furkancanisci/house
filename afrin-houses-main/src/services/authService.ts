import api from './api';
import { User } from '../types';

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  first_name: string;
  last_name: string;
  email: string;
  phone?: string;
  password: string;
  password_confirmation: string;
  terms_accepted: boolean;
}

export interface AuthResponse {
  user: User;
  token: string;
  message: string;
}

interface AuthService {
  login(credentials: LoginCredentials): Promise<AuthResponse>;
  register(userData: RegisterData): Promise<AuthResponse>;
  logout(): Promise<void>;
  isAuthenticated(): boolean;
  getToken(): string | null;
  getCurrentUser(): Promise<User | null>;
  getStoredUser(): User | null;
  clearAuthData(): void;
  forgotPassword(email: string): Promise<{ message: string }>;
  resetPassword(data: { token: string; email: string; password: string; password_confirmation: string }): Promise<{ message: string }>;
  isTokenValid(userData: any): boolean;
  updateUser(userData: Partial<User>): Promise<{ user: User; message: string }>;
  resendVerificationEmail(email?: string): Promise<{ message: string }>;
}

export const authService: AuthService = {
  // Login user
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    try {
      const response = await api.post('/auth/login', credentials);
      
      // API returns access_token, not token
      const token = response.data.access_token || response.data.token;
      if (token) {
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(response.data.user));
      }
      
      return {
        ...response.data,
        token: token // Normalize to token for consistency
      };
    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  },

  // Register user
  async register(userData: RegisterData): Promise<AuthResponse> {
    try {
      const response = await api.post('/auth/register', userData);
      
      // API returns access_token, not token
      const token = response.data.access_token || response.data.token;
      if (token) {
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(response.data.user));
      }
      
      return {
        ...response.data,
        token: token // Normalize to token for consistency
      };
    } catch (error) {
      console.error('Registration error:', error);
      throw error;
    }
  },

  // Logout user
  async logout(): Promise<void> {
    try {
      await api.post('/auth/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      this.clearAuthData();
    }
  },
  
  // Get stored user data from localStorage
  getStoredUser(): User | null {
    const userStr = localStorage.getItem('user');
    return userStr ? JSON.parse(userStr) : null;
  },
  
  // Clear authentication data
  clearAuthData(): void {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
  },
  
  // Check if user is authenticated
  isAuthenticated(): boolean {
    return !!this.getToken();
  },

  // Get stored token
  getToken(): string | null {
    return localStorage.getItem('token');
  },

  // Get current user from backend with proper token handling
  async getCurrentUser(): Promise<User | null> {
    const token = this.getToken();
    
    // If no token exists, ensure we're logged out
    if (!token) {
      this.clearAuthData();
      return null;
    }

    try {
      // Make sure to include the token in the request
      const response = await api.get('/auth/me', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        withCredentials: true // Important for sending cookies
      });

      // If we get a successful response but no user data, clear auth
      if (!response.data?.user) {
        console.warn('No user data in response');
        this.clearAuthData();
        return null;
      }

      // Update the stored user data
      const userData = response.data.user;
      localStorage.setItem('user', JSON.stringify(userData));
      
      // Also ensure the token is still valid
      if (!this.isTokenValid(userData)) {
        console.warn('Token is no longer valid');
        this.clearAuthData();
        return null;
      }

      return userData;
      
    } catch (error: any) {
      console.error('Failed to fetch current user:', error);
      
      // Handle different types of errors
      if (error.response) {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        console.error('Response data:', error.response.data);
        console.error('Response status:', error.response.status);
        
        // Clear auth data for authentication errors
        if ([401, 403, 419].includes(error.response.status)) {
          console.warn('Authentication error, clearing auth data');
          this.clearAuthData();
        }
      } else if (error.request) {
        // The request was made but no response was received
        console.error('No response received from server');
      } else {
        // Something happened in setting up the request that triggered an Error
        console.error('Error setting up request:', error.message);
      }
      
      return null;
    }
  },
  
  // Helper method to check if token is still valid
  isTokenValid(userData: any): boolean {
    if (!userData) return false;
    
    // You can add additional token validation logic here
    // For example, check if token is expired based on its claims
    
    return true; // Default to true if no specific validation fails
  },
  

  // Forgot password
  async forgotPassword(email: string): Promise<{ message: string }> {
    try {
      const response = await api.post('/auth/forgot-password', { email });
      return response.data;
    } catch (error) {
      console.error('Forgot password error:', error);
      throw error;
    }
  },

  // Reset password
  async resetPassword(data: {
    token: string;
    email: string;
    password: string;
    password_confirmation: string;
  }): Promise<{ message: string }> {
    try {
      const response = await api.post('/auth/reset-password', data);
      return response.data;
    } catch (error) {
      console.error('Reset password error:', error);
      throw error;
    }
  },

  // Update user profile
  async updateUser(userData: Partial<User>): Promise<{ user: User; message: string }> {
    try {
      console.log('Sending profile update data:', userData);
      console.log('API Base URL:', process.env?.VITE_API_BASE_URL || 'https://api.besttrend-sy.com/api/v1');
      
      // Let's log the full request details
      const requestUrl = `${process.env?.VITE_API_BASE_URL || 'https://api.besttrend-sy.com/api/v1'}/dashboard/profile`;
      console.log('Full request URL:', requestUrl);
      
      const response = await api.post('/dashboard/profile', userData);
      console.log('Profile update response:', response.data);
      
      // Update the stored user data in localStorage
      const currentUser = this.getStoredUser();
      if (currentUser) {
        const updatedUser = { ...currentUser, ...userData };
        localStorage.setItem('user', JSON.stringify(updatedUser));
      }
      
      return response.data;
    } catch (error: any) {
      console.error('Update user error:', error);
      console.error('Error response:', error.response?.data);
      console.error('Error status:', error.response?.status);
      console.error('Error headers:', error.response?.headers);
      throw error;
    }
  },

  // Resend email verification
  async resendVerificationEmail(email?: string): Promise<{ message: string }> {
    try {
      const requestData = email ? { email } : {};
      const response = await api.post('/auth/resend-verification', requestData);
      return response.data;
    } catch (error) {
      console.error('Resend verification email error:', error);
      throw error;
    }
  },
};

export default authService;