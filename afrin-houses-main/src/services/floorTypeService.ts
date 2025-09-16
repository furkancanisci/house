import api from './api';

export interface FloorType {
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

export interface FloorTypeResponse {
  success: boolean;
  data: FloorType[];
  message?: string;
  meta?: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  };
}

class FloorTypeService {
  private baseUrl = '/floor-types';

  /**
   * Get all floor types
   */
  async getFloorTypes(): Promise<FloorType[]> {
    try {
      const response = await api.get<FloorTypeResponse>(this.baseUrl);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch floor types');
    } catch (error) {
      console.error('Error fetching floor types:', error);
      throw error;
    }
  }

  /**
   * Get floor types as simple options for dropdowns
   */
  async getFloorTypeOptions(): Promise<FloorType[]> {
    try {
      const response = await api.get<FloorTypeResponse>(`${this.baseUrl}/options`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch floor type options');
    } catch (error) {
      console.error('Error fetching floor type options:', error);
      throw error;
    }
  }

  /**
   * Get floor types with property counts
   */
  async getFloorTypesWithCounts(): Promise<FloorType[]> {
    try {
      const response = await api.get<FloorTypeResponse>(`${this.baseUrl}/with-counts`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch floor types with counts');
    } catch (error) {
      console.error('Error fetching floor types with counts:', error);
      throw error;
    }
  }

  /**
   * Get a specific floor type by ID or slug
   */
  async getFloorType(identifier: string | number): Promise<FloorType> {
    try {
      const response = await api.get<{ success: boolean; data: FloorType; message?: string }>(`${this.baseUrl}/${identifier}`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch floor type');
    } catch (error) {
      console.error('Error fetching floor type:', error);
      throw error;
    }
  }

  /**
   * Get display name based on current language
   */
  getDisplayName(floorType: FloorType, language: string = 'en'): string {
    switch (language) {
      case 'ar':
        return floorType.name_ar || floorType.name;
      case 'ku':
        return floorType.name_ku || floorType.name;
      default:
        return floorType.name;
    }
  }

  /**
   * Get display description based on current language
   */
  getDisplayDescription(floorType: FloorType, language: string = 'en'): string {
    switch (language) {
      case 'ar':
        return floorType.description_ar || floorType.description || '';
      case 'ku':
        return floorType.description_ku || floorType.description || '';
      default:
        return floorType.description || '';
    }
  }
}

// Export singleton instance
export const floorTypeService = new