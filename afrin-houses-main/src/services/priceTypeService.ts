// Get API base URL from environment variable or use default
const getApiBaseUrl = (): string => {
  // For browser environments, we can access process.env
  if (typeof process !== 'undefined' && process.env?.VITE_API_BASE_URL) {
    return process.env.VITE_API_BASE_URL;
  }
  
  // Always use the local development API URL
  return 'http://localhost:8000/api/v1';
};

const API_BASE_URL = getApiBaseUrl();

export interface PriceType {
  id: number;
  name_ar: string;
  name_en: string;
  name_ku: string;
  key: string;
  listing_type: 'rent' | 'sale' | 'both';
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface PriceTypesResponse {
  success: boolean;
  data: PriceType[];
}

class PriceTypeService {
  private baseUrl = `${API_BASE_URL}/properties/price-types`;

  async getPriceTypes(listingType?: 'rent' | 'sale'): Promise<PriceType[]> {
    try {
      const url = listingType 
        ? `${this.baseUrl}?listing_type=${listingType}`
        : this.baseUrl;
      
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result: PriceTypesResponse = await response.json();
      
      if (result.success) {
        return result.data;
      } else {
        throw new Error('Failed to fetch price types');
      }
    } catch (error) {
      console.error('Error fetching price types:', error);
      throw error;
    }
  }

  async getPriceTypesByListing(listingType: 'rent' | 'sale'): Promise<PriceType[]> {
    return this.getPriceTypes(listingType);
  }
}

export const priceTypeService = new PriceTypeService();
export default priceTypeService;