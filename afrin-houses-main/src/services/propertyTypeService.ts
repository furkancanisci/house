import api from './api';

export interface PropertyType {
  id: number;
  name: string;
  name_ar?: string;
  name_ku?: string;
  slug: string;
  description?: string;
  icon?: string;
  parent_id?: number;
  sort_order: number;
  preferred_name: string;
  full_path?: string;
  children?: PropertyType[];
  parent?: {
    id: number;
    name: string;
    slug: string;
    preferred_name: string;
  };
}

export interface PropertyTypeResponse {
  success: boolean;
  data: PropertyType[];
  message?: string;
}

class PropertyTypeService {
  private baseUrl = '/property-types';

  /**
   * Get all property types in hierarchical structure
   */
  async getPropertyTypes(hierarchical: boolean = true): Promise<PropertyType[]> {
    try {
      const response = await api.get<PropertyTypeResponse>(`${this.baseUrl}?hierarchical=${hierarchical}`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch property types');
    } catch (error) {
      console.error('Error fetching property types:', error);
      throw error;
    }
  }

  /**
   * Get property types as simple options for dropdowns
   */
  async getPropertyTypeOptions(includeParents: boolean = true, includeChildren: boolean = true): Promise<PropertyType[]> {
    try {
      const response = await api.get<PropertyTypeResponse>(
        `${this.baseUrl}/options?include_parents=${includeParents}&include_children=${includeChildren}`
      );

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch property type options');
    } catch (error) {
      console.error('Error fetching property type options:', error);
      throw error;
    }
  }

  /**
   * Get only parent property types (root categories)
   */
  async getParentPropertyTypes(): Promise<PropertyType[]> {
    try {
      const response = await api.get<PropertyTypeResponse>(`${this.baseUrl}/parents`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch parent property types');
    } catch (error) {
      console.error('Error fetching parent property types:', error);
      throw error;
    }
  }

  /**
   * Get child property types for a specific parent
   */
  async getChildPropertyTypes(parentId?: number): Promise<PropertyType[]> {
    try {
      const url = parentId ? `${this.baseUrl}/children/${parentId}` : `${this.baseUrl}/children`;
      const response = await api.get<PropertyTypeResponse>(url);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch child property types');
    } catch (error) {
      console.error('Error fetching child property types:', error);
      throw error;
    }
  }

  /**
   * Get a specific property type by ID or slug
   */
  async getPropertyType(identifier: string | number): Promise<PropertyType> {
    try {
      const response = await api.get<{ success: boolean; data: PropertyType; message?: string }>(`${this.baseUrl}/${identifier}`);

      if (response.data?.success) {
        return response.data.data;
      }

      throw new Error(response.data?.message || 'Failed to fetch property type');
    } catch (error) {
      console.error('Error fetching property type:', error);
      throw error;
    }
  }

  /**
   * Get icon class for a property type
   */
  getIconClass(propertyType: PropertyType): string {
    if (propertyType.icon) {
      return propertyType.icon;
    }

    // Fallback icons based on property type name
    const name = propertyType.name.toLowerCase();
    if (name.includes('apartment') || name.includes('شقة')) return 'fas fa-building';
    if (name.includes('house') || name.includes('منزل')) return 'fas fa-home';
    if (name.includes('villa') || name.includes('فيلا')) return 'fas fa-home';
    if (name.includes('commercial') || name.includes('تجاري')) return 'fas fa-building';
    if (name.includes('land') || name.includes('أرض')) return 'fas fa-map';
    if (name.includes('studio') || name.includes('استوديو')) return 'fas fa-door-open';
    if (name.includes('office') || name.includes('مكتب')) return 'fas fa-briefcase';
    if (name.includes('industrial') || name.includes('صناعي')) return 'fas fa-industry';

    return 'fas fa-home'; // Default icon
  }

  /**
   * Get display name for a property type based on current language
   */
  getDisplayName(propertyType: PropertyType, language: string = 'en'): string {
    switch (language) {
      case 'ar':
        // For Arabic: prioritize Arabic name, fallback to English if Arabic is empty/null
        return (propertyType.name_ar && propertyType.name_ar.trim()) ? propertyType.name_ar : propertyType.name;
      case 'ku':
        // For Kurdish: prioritize Kurdish name, fallback to English if Kurdish is empty/null
        return (propertyType.name_ku && propertyType.name_ku.trim()) ? propertyType.name_ku : propertyType.name;
      case 'en':
      default:
        // For English or any other language: use English name
        return propertyType.name;
    }
  }
}

export const propertyTypeService = new PropertyTypeService();
export default propertyTypeService;