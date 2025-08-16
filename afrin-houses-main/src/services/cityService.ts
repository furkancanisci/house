import api from './api';

export interface City {
  id: number;
  name: string;
  state: string;
  latitude?: number;
  longitude?: number;
  name_ar: string;
  name_en: string;
}

export interface CityResponse {
  success: boolean;
  data: City[];
  message: string;
}

export interface StateData {
  name: string;
  name_ar: string;
  name_en: string;
}

export interface StateResponse {
  success: boolean;
  data: StateData[];
  message: string;
}

class CityService {
  /**
   * Get all cities
   */
  async getCities(params?: {
    locale?: string;
    state?: string;
  }): Promise<City[]> {
    try {
      const response = await api.get<CityResponse>('/cities', { params });
      return response.data.data;
    } catch (error) {
      console.error('Error fetching cities:', error);
      throw error;
    }
  }

  /**
   * Get all states for Syria
   */
  async getStates(params?: {
    locale?: string;
  }): Promise<string[]> {
    try {
      const response = await api.get<StateResponse>('/cities/states', { params });
      // Convert objects to strings based on locale
      const locale = params?.locale || 'ar';
      return response.data.data.map(state => {
        if (typeof state === 'string') return state;
        return locale === 'ar' ? state.name_ar : state.name_en;
      });
    } catch (error) {
      console.error('Error fetching states:', error);
      throw error;
    }
  }

  /**
   * Get cities by state
   */
  async getCitiesByState(params?: {
    locale?: string;
    state?: string;
  }): Promise<City[]> {
    try {
      const response = await api.get<CityResponse>('/cities', { params });
      return response.data.data;
    } catch (error) {
      console.error('Error fetching cities by state:', error);
      throw error;
    }
  }

  /**
   * Search cities by name
   */
  async searchCities(params: {
    q: string;
    locale?: string;
    limit?: number;
  }): Promise<City[]> {
    try {
      const response = await api.get<CityResponse>('/cities/search', { params });
      return response.data.data;
    } catch (error) {
      console.error('Error searching cities:', error);
      throw error;
    }
  }

  /**
   * Get Syrian cities (default filter)
   */
  async getSyrianCities(locale: string = 'ar'): Promise<City[]> {
    return this.getCities({
      locale
    });
  }

  /**
   * Get Syrian states
   */
  async getSyrianStates(locale: string = 'ar'): Promise<string[]> {
    return this.getStates({
      locale
    });
  }
}

export const cityService = new CityService();