import api from './api';

export interface WindowType {
  id: number;
  name: string;
  name_ar?: string;
  name_ku?: string;
  slug: string;
  description?: string;
  description_ar?: string;
  description_ku?: string;
  is_active: boolean;
  sort_order: number;
  created_at?: string;
  updated_at?: string;
  properties_count?: number;
}

export interface WindowTypeResponse {
  success: boolean;
  data: WindowType[];
  message?: string;
  meta?: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  };
}

class WindowTypeService {
  private baseUrl = '/window-types';

  /**
   * Get all window types
   */
  async getWindowTypes(): Promise<WindowType[]> {
    try {
      const response = await api.get<WindowTypeResponse>(this.baseUrl);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch window types');
    } catch (error) {
  
      throw error;
    }
  }

  /**
   * Get window types as simple options for dropdowns
   */
  async getWindowTypeOptions(): Promise<WindowType[]> {
    try {
      const response = await api.get<WindowTypeResponse>(`${this.baseUrl}/options`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch window type options');
    } catch (error) {
  
      throw error;
    }
  }

  /**
   * Get window types with property counts
   */
  async getWindowTypesWithCounts(): Promise<WindowType[]> {
    try {
      const response = await api.get<WindowTypeResponse>(`${this.baseUrl}/with-counts`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch window types with counts');
    } catch (error) {
  
      throw error;
    }
  }

  /**
   * Get a specific window type by ID or slug
   */
  async getWindowType(identifier: string | number): Promise<WindowType> {
    try {
      const response = await api.get<{ success: boolean; data: WindowType; message?: string }>(`${this.baseUrl}/${identifier}`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch window type');
    } catch (error) {
  
      throw error;
    }
  }

  /**
   * Get display name based on current language
   */
  getDisplayName(windowType: WindowType, language: string = 'en'): string {
    switch (language) {
      case 'ar':
        return windowType.name_ar || windowType.name;
      case 'ku':
        return windowType.name_ku || windowType.name;
      default:
        return windowType.name;
    }
  }

  /**
   * Get display description based on current language
   */
  getDisplayDescription(windowType: WindowType, language: string = 'en'): string {
    switch (language) {
      case 'ar':
        return windowType.description_ar || windowType.description || '';
      case 'ku':
        return windowType.description_ku || windowType.description || '';
      default:
        return windowType.description || '';
    }
  }
}

// Export singleton instance
export const windowTypeService = new WindowTypeService();
export default windowTypeService;