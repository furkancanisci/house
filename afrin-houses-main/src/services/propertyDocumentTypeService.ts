import { api } from './api';

export interface PropertyDocumentType {
  id: number;
  name: string;
  description?: string;
  sort_order: number;
}

export interface PropertyDocumentTypeResponse {
  success: boolean;
  data: PropertyDocumentType[];
  message?: string;
}

class PropertyDocumentTypeService {
  private cache = new Map<string, { data: any; timestamp: number }>();
  private readonly cacheTimeout = 30 * 60 * 1000; // 30 minutes cache
  private pendingRequests = new Map<string, Promise<any>>();

  /**
   * Get cached data if available and not expired
   */
  private getCachedData(key: string): any | null {
    const cached = this.cache.get(key);
    if (cached && Date.now() - cached.timestamp < this.cacheTimeout) {
      return cached.data;
    }
    return null;
  }

  /**
   * Set cache data
   */
  private setCacheData(key: string, data: any): void {
    this.cache.set(key, { data, timestamp: Date.now() });
  }

  /**
   * Get all property document types
   */
  async getPropertyDocumentTypes(params?: {
    lang?: string;
  }): Promise<PropertyDocumentType[]> {
    const cacheKey = `property-document-types-${JSON.stringify(params || {})}`;
    
    // Check cache first
    const cachedData = this.getCachedData(cacheKey);
    if (cachedData) {
      return cachedData;
    }
    
    // Check if there's already a pending request for this key
    if (this.pendingRequests.has(cacheKey)) {
      return this.pendingRequests.get(cacheKey)!;
    }
    
    // Create and store the request promise
    const requestPromise = this.makeApiRequest('/property-document-types', params, cacheKey);
    this.pendingRequests.set(cacheKey, requestPromise);
    
    try {
      const result = await requestPromise;
      return result;
    } finally {
      // Clean up pending request
      this.pendingRequests.delete(cacheKey);
    }
  }

  /**
   * Get property document type by ID
   */
  async getPropertyDocumentTypeById(
    id: number,
    params?: { lang?: string }
  ): Promise<PropertyDocumentType | null> {
    const cacheKey = `property-document-type-${id}-${JSON.stringify(params || {})}`;
    
    // Check cache first
    const cachedData = this.getCachedData(cacheKey);
    if (cachedData) {
      return cachedData;
    }
    
    try {
      const response = await api.get(`/property-document-types/${id}`, { params });
      const documentType = response.data?.data || null;
      
      // Cache successful response
      if (documentType) {
        this.setCacheData(cacheKey, documentType);
      }
      
      return documentType;
    } catch (error: any) {
      console.error('Error fetching property document type:', {
        id,
        message: error.message,
        status: error.response?.status,
        statusText: error.response?.statusText
      });
      
      // Return null on error instead of throwing
      return null;
    }
  }

  /**
   * Get all property document types in all languages (for admin use)
   */
  async getAllLanguages(): Promise<any[]> {
    const cacheKey = 'property-document-types-all-languages';
    
    // Check cache first
    const cachedData = this.getCachedData(cacheKey);
    if (cachedData) {
      return cachedData;
    }
    
    try {
      const response = await api.get('/property-document-types/all-languages');
      const documentTypes = response.data?.data || [];
      
      // Cache successful response
      this.setCacheData(cacheKey, documentTypes);
      
      return documentTypes;
    } catch (error: any) {
      console.error('Error fetching property document types (all languages):', {
        message: error.message,
        status: error.response?.status,
        statusText: error.response?.statusText
      });
      
      // Return empty array on error
      return [];
    }
  }

  /**
   * Helper method to make API requests with proper error handling
   */
  private async makeApiRequest(
    endpoint: string, 
    params: any, 
    cacheKey: string
  ): Promise<PropertyDocumentType[]> {
    try {
      const response = await api.get(endpoint, { params });
      const documentTypes = response.data?.data || [];
      
      // Cache successful response
      this.setCacheData(cacheKey, documentTypes);
      
      return documentTypes;
    } catch (error: any) {
      console.error('Error fetching property document types:', {
        endpoint,
        params,
        message: error.message,
        status: error.response?.status,
        statusText: error.response?.statusText
      });
      
      // Return empty array on error instead of throwing
      return [];
    }
  }

  /**
   * Clear all cache
   */
  clearCache(): void {
    this.cache.clear();
    this.pendingRequests.clear();
  }

  /**
   * Clear specific cache entry
   */
  clearCacheEntry(key: string): void {
    this.cache.delete(key);
  }

  /**
   * Get fallback property document types (static data in case API fails)
   */
  getFallbackDocumentTypes(language: string = 'ar'): PropertyDocumentType[] {
    const documentTypes = [
      {
        id: 1,
        name: language === 'ar' ? 'الطابو العقاري (الطابع العادي)' : 'Real Estate Tabu (Regular Title)',
        description: language === 'ar' ? 'الطابو العقاري العادي للممتلكات السكنية والتجارية' : 'Regular real estate title for residential and commercial properties',
        sort_order: 1,
      },
      {
        id: 2,
        name: language === 'ar' ? 'الطابو العيني (الطابع المحدث)' : 'Updated Tabu (Modern Title)',
        description: language === 'ar' ? 'الطابو المحدث والمطور للعقارات الجديدة' : 'Updated and modernized title for new properties',
        sort_order: 2,
      },
      {
        id: 3,
        name: language === 'ar' ? 'الطابو الأخضر (الطابع الزراعي)' : 'Green Tabu (Agricultural Title)',
        description: language === 'ar' ? 'الطابو الزراعي للأراضي الزراعية والحقول' : 'Agricultural title for farmland and agricultural fields',
        sort_order: 3,
      },
      {
        id: 4,
        name: language === 'ar' ? 'أراضي البناء (الطابع العقاري)' : 'Construction Land (Building Title)',
        description: language === 'ar' ? 'طابو خاص بالأراضي المخصصة للبناء والتطوير' : 'Special title for land designated for construction and development',
        sort_order: 4,
      },
      {
        id: 5,
        name: language === 'ar' ? 'الطابو المؤقت' : 'Temporary Tabu',
        description: language === 'ar' ? 'طابو مؤقت في انتظار التسوية النهائية' : 'Temporary title pending final settlement',
        sort_order: 5,
      },
      {
        id: 6,
        name: language === 'ar' ? 'الطابو العائلي' : 'Family Tabu',
        description: language === 'ar' ? 'طابو مشترك للعائلة أو الورثة' : 'Shared family title or inheritance title',
        sort_order: 6,
      },
    ];

    return documentTypes;
  }
}

// Export singleton instance
export const propertyDocumentTypeService = new PropertyDocumentTypeService();
export default propertyDocumentTypeService;