import api from './api';

export interface ViewType {
  id: number;
  name: string;
  name_ar?: string;
  name_ku?: string;
  value: string;
  description?: string;
  description_ar?: string;
  description_ku?: string;
  is_active: boolean;
  sort_order: number;
  created_at?: string;
  updated_at?: string;
  properties_count?: number;
}

export interface ViewTypeResponse {
  success: boolean;
  data: ViewType[];
  message?: string;
  meta?: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  };
}

class ViewTypeService {
  private baseUrl = '/view-types';

  /**
   * Get all view types
   */
  async getViewTypes(): Promise<ViewType[]> {
    try {
      const response = await api.get<ViewTypeResponse>(this.baseUrl);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch view types');
    } catch (error) {
      console.error('Error fetching view types:', error);
      throw error;
    }
  }

  /**
   * Get view types as simple options for dropdowns
   */
  async getViewTypeOptions(): Promise<ViewType[]> {
    try {
      const response = await api.get<ViewTypeResponse>(`${this.baseUrl}/options`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch view type options');
    } catch (error) {
      console.error('Error fetching view type options:', error);
      throw error;
    }
  }

  /**
   * Get view types with property counts
   */
  async getViewTypesWithCounts(): Promise<ViewType[]> {
    try {
      const response = await api.get<ViewTypeResponse>(`${this.baseUrl}/with-counts`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch view types with counts');
    } catch (error) {
      console.error('Error fetching view types with counts:', error);
      throw error;
    }
  }

  /**
   * Get a specific view type by ID or value
   */
  async getViewType(identifier: string | number): Promise<ViewType> {
    try {
      const response = await api.get<{ success: boolean; data: ViewType; message?: string }>(`${this.baseUrl}/${identifier}`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch view type');
    } catch (error) {
      console.error('Error fetching view type:', error);
      throw error;
    }
  }

  /**
   * Get display name based on current language
   */
  getDisplayName(viewType: ViewType, language: string = 'en'): string {
    switch (language) {
      case 'ar':
        return viewType.name_ar || viewType.name;
      case 'ku':
        return viewType.name_ku || viewType.name;
      default:
        return viewType.name;
    }
  }

  /**
   * Get display description based on current language
   */
  getDisplayDescription(viewType: ViewType, language: string = 'en'): string {
    switch (language) {
      case 'ar':
        return viewType.description_ar || viewType.description || '';
      case 'ku':
        return viewType.description_ku || viewType.description || '';
      default:
        return viewType.description || '';
    }
  }
}

// Export singleton instance
export const viewTypeService = new ViewTypeService();
export default viewTypeService;