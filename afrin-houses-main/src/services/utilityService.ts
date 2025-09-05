import { Utility } from '../types';
import api from './api';

/**
 * Service for managing property utilities
 */
export class UtilityService {
  private static readonly BASE_URL = '/utilities';

  /**
   * Get localized name for a utility based on language
   * @param utility - Utility object
   * @param language - Language code (ar, en, ku)
   * @returns Localized name string
   */
  static getLocalizedName(utility: Utility, language: string): string {
    switch (language) {
      case 'ar':
        return utility.name_ar || utility.name_en || '';
      case 'ku':
        return utility.name_ku || utility.name_en || '';
      default:
        return utility.name_en || utility.name_ar || '';
    }
  }

  /**
   * Fetch all active utilities
   * @param language - Language code (ar, en, ku)
   * @returns Promise<Utility[]>
   */
  static async getUtilities(language: string = 'en'): Promise<Utility[]> {
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

      console.warn('Unexpected utilities API response structure:', response.data);
      return [];
    } catch (error) {
      console.error('Error fetching utilities:', error);
      throw new Error('Failed to fetch utilities');
    }
  }

  /**
   * Fetch a specific utility by ID
   * @param id - Utility ID
   * @param language - Language code (ar, en, ku)
   * @returns Promise<Utility | null>
   */
  static async getUtilityById(id: number, language: string = 'en'): Promise<Utility | null> {
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
      console.error(`Error fetching utility ${id}:`, error);
      return null;
    }
  }

  /**
   * Fetch utilities by category
   * @param category - Utility category
   * @param language - Language code (ar, en, ku)
   * @returns Promise<Utility[]>
   */
  static async getUtilitiesByCategory(category: string, language: string = 'en'): Promise<Utility[]> {
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
      console.error(`Error fetching utilities for category ${category}:`, error);
      return [];
    }
  }

  /**
   * Search utilities by name
   * @param query - Search query
   * @param language - Language code (ar, en, ku)
   * @returns Promise<Utility[]>
   */
  static async searchUtilities(query: string, language: string = 'en'): Promise<Utility[]> {
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
      console.error(`Error searching utilities with query "${query}":`, error);
      return [];
    }
  }
}

// Export default instance for convenience
export default UtilityService;

// Export individual methods for direct import
export const getUtilities = UtilityService.getUtilities;
export const getUtilityById = UtilityService.getUtilityById;
export const getUtilitiesByCategory = UtilityService.getUtilitiesByCategory;
export const searchUtilities = UtilityService.searchUtilities;
export const getLocalizedName = UtilityService.getLocalizedName;