import axios, { AxiosInstance, AxiosError } from 'axios';
import { authService } from './authService';

const api: AxiosInstance = axios.create({
  baseURL: 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
  timeout: 60000, // Increased timeout to 60 seconds to handle image uploads
});

// Flag to prevent multiple token refresh attempts
let isRefreshing = false;
let failedQueue: Array<{resolve: (token: string) => void, reject: (error: any) => void}> = [];

const processQueue = (error: any, token: string | null = null) => {
  failedQueue.forEach(prom => {
    if (error) {
      prom.reject(error);
    } else {
      prom.resolve(token!);
    }
  });
  failedQueue = [];
};

// Request interceptor
api.interceptors.request.use(
  (config) => {
    const token = authService.getToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor
api.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const originalRequest = error.config as any;
    
    // If the error is 401 and we haven't tried to refresh the token yet
    if (error.response?.status === 401 && !originalRequest._retry) {
      if (isRefreshing) {
        // If we're already refreshing the token, add the request to the queue
        return new Promise((resolve, reject) => {
          failedQueue.push({ resolve, reject });
        })
          .then((token) => {
            originalRequest.headers['Authorization'] = 'Bearer ' + token;
            return api(originalRequest);
          })
          .catch((err) => {
            return Promise.reject(err);
          });
      }

      originalRequest._retry = true;
      isRefreshing = true;

      try {
        // Try to refresh the token
        const newToken = await refreshToken();
        if (newToken) {
          // Update the Authorization header
          api.defaults.headers.common['Authorization'] = 'Bearer ' + newToken;
          originalRequest.headers['Authorization'] = 'Bearer ' + newToken;
          
          // Process the queue
          processQueue(null, newToken);
          
          // Retry the original request
          return api(originalRequest);
        }
      } catch (refreshError) {
        // If refresh token fails, clear auth data and redirect to login
        authService.clearAuthData();
        processQueue(refreshError, null);
        
        // Redirect to login page if we're not already there
        if (window.location.pathname !== '/login') {
          window.location.href = '/login';
        }
        
        return Promise.reject(refreshError);
      } finally {
        isRefreshing = false;
      }
    }
    
    // For other errors, just reject
    return Promise.reject(error);
  }
);

// Function to refresh the access token
const refreshToken = async (): Promise<string | null> => {
  try {
    const response = await axios.post(
      'http://127.0.0.1:8000/api/v1/auth/refresh',
      {},
      {
        withCredentials: true,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      }
    );
    
    const { access_token } = response.data;
    if (access_token) {
      localStorage.setItem('token', access_token);
      return access_token;
    }
    return null;
  } catch (error) {
    console.error('Failed to refresh token:', error);
    throw error;
  }
};

export default api;
