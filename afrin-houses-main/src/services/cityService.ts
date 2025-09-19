import { api } from './api';

export interface City {
  id: number;
  name: string;
  name_en?: string;
  state: string;
  country: string;
}

export interface StateData {
  name: string;
  cities: City[];
}

export interface CityResponse {
  success: boolean;
  data: City[];
  message?: string;
}

// Rate limiting implementation with improved logic
class RateLimiter {
  private requests: Map<string, number[]> = new Map();
  private readonly maxRequests: number;
  private readonly timeWindow: number;

  constructor(maxRequests: number = 10, timeWindowMs: number = 60000) {
    this.maxRequests = maxRequests;
    this.timeWindow = timeWindowMs;
  }

  canMakeRequest(key: string): boolean {
    const now = Date.now();
    const requests = this.requests.get(key) || [];
    
    // Remove old requests outside the time window
    const validRequests = requests.filter(time => now - time < this.timeWindow);
    
    if (validRequests.length >= this.maxRequests) {
      return false;
    }
    
    // Add current request
    validRequests.push(now);
    this.requests.set(key, validRequests);
    
    return true;
  }

  getWaitTime(key: string): number {
    const requests = this.requests.get(key) || [];
    if (requests.length === 0) return 0;
    
    // Find the oldest request that's still within the time window
    const now = Date.now();
    const validRequests = requests.filter(time => now - time < this.timeWindow);
    
    if (validRequests.length < this.maxRequests) {
      return 0;
    }
    
    const oldestRequest = Math.min(...validRequests);
    const waitTime = this.timeWindow - (now - oldestRequest);
    
    return Math.max(0, waitTime);
  }

  // Reset rate limiter for a specific key
  reset(key: string): void {
    this.requests.delete(key);
  }
}

class CityService {
  private rateLimiter = new RateLimiter(5, 60000); // 5 requests per 60 seconds (more conservative)
  private cache = new Map<string, { data: any; timestamp: number }>();
  private readonly cacheTimeout = 10 * 60 * 1000; // 10 minutes cache (longer)
  private pendingRequests = new Map<string, Promise<any>>(); // Prevent duplicate requests
  /**
   * Get cached data if available and not expired
   */
  private getCachedData(key: string): any | null {
    const cached = this.cache.get(key);
    if (cached && Date.now() - cached.timestamp < this.cacheTimeout) {
      return cached.data;
    }
    return null;
  }

  /**
   * Set cache data
   */
  private setCacheData(key: string, data: any): void {
    this.cache.set(key, { data, timestamp: Date.now() });
  }



  /**
   * Get all cities with improved rate limiting and caching
   */
  async getCities(params?: {
    locale?: string;
    state?: string;
  }): Promise<City[]> {
    const cacheKey = `cities-${JSON.stringify(params || {})}`;
    
    // Check cache first
    const cachedData = this.getCachedData(cacheKey);
    if (cachedData) {
      return cachedData;
    }
    
    // Check if there's already a pending request for this key
    if (this.pendingRequests.has(cacheKey)) {
      return this.pendingRequests.get(cacheKey)!;
    }
    
    const requestKey = 'all-cities';

    // Create and store the request promise
    const requestPromise = this.makeApiRequest('/cities', params, cacheKey, requestKey);
    this.pendingRequests.set(cacheKey, requestPromise);
    
    try {
      const result = await requestPromise;
      return result;
    } finally {
      // Clean up pending request
      this.pendingRequests.delete(cacheKey);
    }
  }
  
  /**
   * Helper method to make API requests with proper error handling
   */
  private async makeApiRequest(endpoint: string, params: any, cacheKey: string, requestKey: string): Promise<City[]> {
    try {
      const response = await api.get(endpoint, { params });
      const cities = response.data?.data || response.data || [];
      
      // Cache successful response
      this.setCacheData(cacheKey, cities);
      
      return cities;
    } catch (error: any) {

    
    }
  }

  /**
   * Get fallback Syrian states (static data)
   */
  private getFallbackSyrianStates(locale: string = 'ar'): string[] {
    const states = [
      { ar: 'دمشق', en: 'Damascus' },
      { ar: 'حلب', en: 'Aleppo' },
      { ar: 'حمص', en: 'Homs' },
      { ar: 'حماة', en: 'Hama' },
      { ar: 'اللاذقية', en: 'Latakia' },
      { ar: 'دير الزور', en: 'Deir ez-Zor' },
      { ar: 'الرقة', en: 'Raqqa' },
      { ar: 'درعا', en: 'Daraa' },
      { ar: 'السويداء', en: 'As-Suwayda' },
      { ar: 'القنيطرة', en: 'Quneitra' },
      { ar: 'طرطوس', en: 'Tartus' },
      { ar: 'إدلب', en: 'Idlib' },
      { ar: 'الحسكة', en: 'Al-Hasakah' },
      { ar: 'ريف دمشق', en: 'Rif Dimashq' }
    ];
    
    return states.map(state => locale === 'ar' ? state.ar : state.en);
  }

  /**
   * Get all states for Syria with improved rate limiting and caching
   */
  async getStates(params?: {
    locale?: string;
  }): Promise<string[]> {
    const cacheKey = `states-${JSON.stringify(params || {})}`;
    
    // Check cache first
    const cachedData = this.getCachedData(cacheKey);
    if (cachedData) {
      return cachedData;
    }
    
    const requestKey = 'states';
    
    // Check rate limit
    if (!this.rateLimiter.canMakeRequest(requestKey)) {
      const waitTime = this.rateLimiter.getWaitTime(requestKey);
      
      
      // Return fallback data immediately
      return this.getFallbackSyrianStates(params?.locale);
    }
    
    try {
      const response = await api.get('/cities/states', { params });
      // Convert objects to strings based on locale
      const locale = params?.locale || 'ar';
      const states = response.data?.data?.map(state => {
        if (typeof state === 'string') return state;
        return locale === 'ar' ? state.name_ar : state.name_en;
      }) || [];
      
      // Cache successful response
      this.setCacheData(cacheKey, states);
      
      return states;
    } catch (error: any) {

      
      // Handle 429 specifically
      if (error.response?.status === 429) {

        // Reset rate limiter to allow retry sooner
        this.rateLimiter.reset(requestKey);
      }
      
      // Fallback to Syrian states if API fails
      return this.getFallbackSyrianStates(params?.locale);
    }
  }

  /**
   * Get cities by state with improved rate limiting and caching
   */
  async getCitiesByState(state: string): Promise<City[]> {
    const cacheKey = `cities-by-state-${state}`;
    
    // Check cache first
    const cachedData = this.getCachedData(cacheKey);
    if (cachedData) {
      return cachedData;
    }
    
    // Check if there's already a pending request for this key
    if (this.pendingRequests.has(cacheKey)) {
      return this.pendingRequests.get(cacheKey)!;
    }
    
    const requestKey = `cities-by-state-${state}`;
    
    // Check rate limit
    if (!this.rateLimiter.canMakeRequest(requestKey)) {
      const waitTime = this.rateLimiter.getWaitTime(requestKey);
      
      
     
    }
    
    // Create and store the request promise
    const requestPromise = this.makeCitiesByStateRequest(state, cacheKey, requestKey);
    this.pendingRequests.set(cacheKey, requestPromise);
    
    try {
      const result = await requestPromise;
      return result;
    } finally {
      // Clean up pending request
      this.pendingRequests.delete(cacheKey);
    }
  }
  
  /**
   * Helper method to make cities by state API requests
   */
  private async makeCitiesByStateRequest(state: string, cacheKey: string, requestKey: string): Promise<City[]> {
    try {
      const response = await api.get(`/cities/state/${encodeURIComponent(state)}`);
      const cities = response.data?.data || response.data || [];
      
      // Cache successful response
      this.setCacheData(cacheKey, cities);
      
      return cities;
    } catch (error: any) {

      
      // Handle 429 specifically
      if (error.response?.status === 429) {

        // Reset rate limiter to allow retry sooner
        this.rateLimiter.reset(requestKey);
      }
      
      
    }
  }

  /**
   * Search cities by name with caching
   */
  async searchCities(params: {
    q: string;
    locale?: string;
    limit?: number;
  }): Promise<City[]> {
    const cacheKey = `search-${JSON.stringify(params)}`;
    
    // Check cache first
    const cachedData = this.getCachedData(cacheKey);
    if (cachedData) {
      return cachedData;
    }
    
    try {
      const response = await api.get('/cities/search', { params });
      const cities = response.data?.data || response.data || [];
      
      // Cache successful response
      this.setCacheData(cacheKey, cities);
      
      return cities;
    } catch (error: any) {

      
    }
  }


  /**
   * Get Syrian states (uses fallback data to avoid circular dependency)
   */
  async getSyrianStates(locale: string = 'ar'): Promise<string[]> {
    // Try to get from API first, but fallback to static data if rate limited
    try {
      return await this.getStates({ locale });
    } catch (error) {

      return this.getFallbackSyrianStates(locale);
    }
  }

  /**
   * Clear all cache data
   */
  clearCache(): void {
    this.cache.clear();
  }

  /**
   * Reset rate limiter for all keys
   */
  resetRateLimiter(): void {
    this.rateLimiter = new RateLimiter(10, 60000);
  }
}

export const cityService = new CityService();