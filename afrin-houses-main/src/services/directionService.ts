import api from './api';

export interface Direction {
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

export interface DirectionResponse {
  success: boolean;
  data: Direction[];
  message?: string;
  meta?: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  };
}

class DirectionService {
  private baseUrl = '/directions';

  /**
   * Get all directions
   */
  async getDirections(): Promise<Direction[]> {
    try {
      const response = await api.get<DirectionResponse>(this.baseUrl);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch directions');
    } catch (error) {

      throw error;
    }
  }

  /**
   * Get directions as simple options for dropdowns
   */
  async getDirectionOptions(): Promise<Direction[]> {
    try {
      const response = await api.get<DirectionResponse>(`${this.baseUrl}/options`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch direction options');
    } catch (error) {

      throw error;
    }
  }

  /**
   * Get directions with property counts
   */
  async getDirectionsWithCounts(): Promise<Direction[]> {
    try {
      const response = await api.get<DirectionResponse>(`${this.baseUrl}/with-counts`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch directions with counts');
    } catch (error) {

      throw error;
    }
  }

  /**
   * Get a specific direction by ID or value
   */
  async getDirection(identifier: string | number): Promise<Direction> {
    try {
      const response = await api.get<{ success: boolean; data: Direction; message?: string }>(`${this.baseUrl}/${identifier}`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch direction');
    } catch (error) {

      throw error;
    }
  }

  /**
   * Get display name based on current language
   */
  getDisplayName(direction: Direction, language: string = 'en'): string {
    switch (language) {
      case 'ar':
        return direction.name_ar || direction.name;
      case 'ku':
        return direction.name_ku || direction.name;
      default:
        return direction.name;
    }
  }

  /**
   * Get display description based on current language
   */
  getDisplayDescription(direction: Direction, language: string = 'en'): string {
    switch (language) {
      case 'ar':
        return direction.description_ar || direction.description || '';
      case 'ku':
        return direction.description_ku || direction.description || '';
      default:
        return direction.description || '';
    }
  }
}

// Export singleton instance
export const directionService = new DirectionService();
export default directionService;