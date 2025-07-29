import api from './api';

export interface SearchSuggestion {
  type: 'city' | 'state' | 'neighborhood' | 'property';
  value: string;
  label: string;
  count?: number;
}

export interface LocationData {
  states: Array<{
    name: string;
    code: string;
    properties_count: number;
  }>;
  cities: Array<{
    name: string;
    state: string;
    properties_count: number;
  }>;
  neighborhoods: Array<{
    name: string;
    city: string;
    state: string;
    properties_count: number;
  }>;
}

export interface PriceRange {
  min: number;
  max: number;
  count: number;
  label: string;
}

export interface StatsOverview {
  total_properties: number;
  average_price: number;
  price_ranges: PriceRange[];
  popular_cities: Array<{
    name: string;
    state: string;
    count: number;
  }>;
  popular_property_types: Array<{
    type: string;
    count: number;
  }>;
}

export const searchService = {
  // Get search suggestions
  async getSuggestions(query: string): Promise<SearchSuggestion[]> {
    try {
      const response = await api.get('/search/suggestions', {
        params: { q: query }
      });
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching search suggestions:', error);
      throw error;
    }
  },

  // Get location data
  async getStates(): Promise<any[]> {
    try {
      const response = await api.get('/locations/states');
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching states:', error);
      throw error;
    }
  },

  async getCities(state?: string): Promise<any[]> {
    try {
      const params = state ? { state } : {};
      const response = await api.get('/locations/cities', { params });
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching cities:', error);
      throw error;
    }
  },

  async getNeighborhoods(city?: string, state?: string): Promise<any[]> {
    try {
      const params: any = {};
      if (city) params.city = city;
      if (state) params.state = state;
      
      const response = await api.get('/locations/neighborhoods', { params });
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching neighborhoods:', error);
      throw error;
    }
  },

  // Get statistics
  async getStatsOverview(): Promise<StatsOverview> {
    try {
      const response = await api.get('/stats/overview');
      return response.data;
    } catch (error) {
      console.error('Error fetching stats overview:', error);
      throw error;
    }
  },

  async getPriceRanges(): Promise<PriceRange[]> {
    try {
      const response = await api.get('/stats/price-ranges');
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching price ranges:', error);
      throw error;
    }
  },
};

export default searchService;