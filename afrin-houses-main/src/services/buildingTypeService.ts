import api from './api';

export interface BuildingType {
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

export interface BuildingTypeResponse {
  success: boolean;
  data: BuildingType[];
  message?: string;
  meta?: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  };
}

class BuildingTypeService {
  private baseUrl = '/building-types';

  /**
   * Get all building types
   */
  async getBuildingTypes(): Promise<BuildingType[]> {
    try {
      const response = await api.get<BuildingTypeResponse>(this.baseUrl);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch building types');
    } catch (error) {
      console.error('Error fetching building types:', error);
      throw error;
    }
  }

  /**
   * Get building types as simple options for dropdowns
   */
  async getBuildingTypeOptions(): Promise<BuildingType[]> {
    try {
      const response = await api.get<BuildingTypeResponse>(`${this.baseUrl}/options`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch building type options');
    } catch (error) {
      console.error('Error fetching building type options:', error);
      throw error;
    }
  }

  /**
   * Get building types with property counts
   */
  async getBuildingTypesWithCounts(): Promise<BuildingType[]> {
    try {
      const response = await api.get<BuildingTypeResponse>(`${this.baseUrl}/with-counts`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch building types with counts');
    } catch (error) {
      console.error('Error fetching building types with counts:', error);
      throw error;
    }
  }

  /**
   * Get a specific building type by ID or slug
   */
  async getBuildingType(identifier: string | number): Promise<BuildingType> {
    try {
      const response = await api.get<{ success: boolean; data: BuildingType; message?: string }>(`${this.baseUrl}/${identifier}`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch building type');
    } catch (error) {
      console.error('Error fetching building type:', error);
      throw error;
    }
  }

  /**
   * Get display name based on current language
   */
  getDisplayName(buildingType: BuildingType, language: string = 'en'): string {
    switch (language) {
      case 'ar':
        return buildingType.name_ar || buildingType.name;
      case 'ku':
        return buildingType.name_ku || buildingType.name;
      default:
        return buildingType.name;
    }
  }

  /**
   * Get display description based on current language
   */
  getDisplayDescription(buildingType: BuildingType, language: string = 'en'): string {
    switch (language) {
      case 'ar':
        return buildingType.description_ar || buildingType.description || '';
      case 'ku':
        return buildingType.description_ku || buildingType.description || '';
      default:
        return buildingType.description || '';
    }
  }
}

// Export singleton instance
export const buildingTypeService = new BuildingTypeService();
export default buildingTypeService;