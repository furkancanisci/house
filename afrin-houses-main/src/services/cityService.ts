import api from './api';

export interface City {
  id: number;
  name: string;
  country: string;
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

export interface CountryData {
  name: string;
  name_ar: string;
  name_en: string;
}

export interface StateData {
  name: string;
  name_ar: string;
  name_en: string;
}

export interface CountryResponse {
  success: boolean;
  data: CountryData[];
  message: string;
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
    country?: string;
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
   * Get all countries
   */
  async getCountries(locale: string = 'ar'): Promise<string[]> {
    try {
      const response = await api.get<CountryResponse>('/cities/countries', {
        params: { locale }
      });
      // Convert objects to strings based on locale
      return response.data.data.map(country => {
        if (typeof country === 'string') return country;
        return locale === 'ar' ? country.name_ar : country.name_en;
      });
    } catch (error) {
      console.error('Error fetching countries:', error);
      throw error;
    }
  }

  /**
   * Get all states for a specific country
   */
  async getStates(params?: {
    locale?: string;
    country?: string;
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
    country?: string;
  }): Promise<City[]> {
    try {
      const response = await api.get<CityResponse>('/cities/by-state', { params });
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
      locale,
      country: locale === 'ar' ? 'سوريا' : 'Syria'
    });
  }

  /**
   * Get Syrian states
   */
  async getSyrianStates(locale: string = 'ar'): Promise<string[]> {
    return this.getStates({
      locale,
      country: locale === 'ar' ? 'سوريا' : 'Syria'
    });
  }
}

export const cityService = new CityService();