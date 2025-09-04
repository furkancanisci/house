import { Feature } from '../types';
import api from './api';

/**
 * Service for managing property features
 */
export class FeatureService {
  private static readonly BASE_URL = '/features';

  /**
   * Fetch all active features
   * @param language - Language code (ar, en, ku)
   * @returns Promise<Feature[]>
   */
  static async getFeatures(language: string = 'en'): Promise<Feature[]> {
    try {
      const response = await api.get(this.BASE_URL, {
        params: {
          language,
          status: 'active'
        }
      });

      if (response.data && Array.isArray(response.data.data)) {
        return response.data.data;
      }

      // Handle different response structures
      if (response.data && Array.isArray(response.data)) {
        return response.data;
      }

      console.warn('Unexpected features API response structure:', response.data);
      return [];
    } catch (error) {
      console.error('Error fetching features:', error);
      throw new Error('Failed to fetch features');
    }
  }

  /**
   * Fetch a specific feature by ID
   * @param id - Feature ID
   * @param language - Language code (ar, en, ku)
   * @returns Promise<Feature | null>
   */
  static async getFeatureById(id: number, language: string = 'en'): Promise<Feature | null> {
    try {
      const response = await api.get(`${this.BASE_URL}/${id}`, {
        params: { language }
      });

      if (response.data && response.data.data) {
        return response.data.data;
      }

      if (response.data) {
        return response.data;
      }

      return null;
    } catch (error) {
      console.error(`Error fetching feature ${id}:`, error);
      return null;
    }
  }

  /**
   * Fetch features by category
   * @param category - Feature category
   * @param language - Language code (ar, en, ku)
   * @returns Promise<Feature[]>
   */
  static async getFeaturesByCategory(category: string, language: string = 'en'): Promise<Feature[]> {
    try {
      const response = await api.get(this.BASE_URL, {
        params: {
          language,
          category,
          status: 'active'
        }
      });

      if (response.data && Array.isArray(response.data.data)) {
        return response.data.data;
      }

      if (response.data && Array.isArray(response.data)) {
        return response.data;
      }

      return [];
    } catch (error) {
      console.error(`Error fetching features for category ${category}:`, error);
      return [];
    }
  }

  /**
   * Search features by name
   * @param query - Search query
   * @param language - Language code (ar, en, ku)
   * @returns Promise<Feature[]>
   */
  static async searchFeatures(query: string, language: string = 'en'): Promise<Feature[]> {
    try {
      const response = await api.get(`${this.BASE_URL}/search`, {
        params: {
          q: query,
          language,
          status: 'active'
        }
      });

      if (response.data && Array.isArray(response.data.data)) {
        return response.data.data;
      }

      if (response.data && Array.isArray(response.data)) {
        return response.data;
      }

      return [];
    } catch (error) {
      console.error(`Error searching features with query "${query}":`, error);
      return [];
    }
  }
}

// Export default instance for convenience
export default FeatureService;

// Export individual methods for direct import
export const getFeatures = FeatureService.getFeatures;
export const getFeatureById = FeatureService.getFeatureById;
export const getFeaturesByCategory = FeatureService.getFeaturesByCategory;
export const searchFeatures = FeatureService.searchFeatures;