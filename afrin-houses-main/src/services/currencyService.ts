import api from './api';

export interface Currency {
  id: number;
  code: string;
  name: string;
  name_en: string;
  name_ar: string;
  name_ku?: string;
  is_active: boolean;
  sort_order: number;
}

export interface CurrencyResponse {
  success: boolean;
  data: Currency[];
}

/**
 * Currency Service
 * Handles all currency-related API calls
 */
class CurrencyService {
  private baseUrl = '/currencies';

  /**
   * Get all active currencies
   */
  async getAllCurrencies(lang: string = 'en'): Promise<Currency[]> {
    try {
      const response = await api.get<CurrencyResponse>(`${this.baseUrl}?lang=${lang}`);
      if (response.data && response.data.success) {
        return response.data.data;
      }
      return [];
    } catch (error) {
      console.error('Error fetching currencies:', error);
      // Return default currencies as fallback
      return this.getDefaultCurrencies();
    }
  }

  /**
   * Get a specific currency by code or ID
   */
  async getCurrency(identifier: string): Promise<Currency | null> {
    try {
      const response = await api.get(`${this.baseUrl}/${identifier}`);
      if (response.data && response.data.success) {
        return response.data.data;
      }
      return null;
    } catch (error) {
      console.error('Error fetching currency:', error);
      return null;
    }
  }

  /**
   * Get default currencies (fallback when API fails)
   */
  private getDefaultCurrencies(): Currency[] {
    return [
      {
        id: 1,
        code: 'TRY',
        name: 'Turkish Lira',
        name_en: 'Turkish Lira',
        name_ar: 'الليرة التركية',
        name_ku: 'Lîra Tirkî',
        is_active: true,
        sort_order: 1,
      },
      {
        id: 2,
        code: 'USD',
        name: 'US Dollar',
        name_en: 'US Dollar',
        name_ar: 'الدولار الأمريكي',
        name_ku: 'Dolarê Amerîkî',
        is_active: true,
        sort_order: 2,
      },
      {
        id: 3,
        code: 'EUR',
        name: 'Euro',
        name_en: 'Euro',
        name_ar: 'اليورو',
        name_ku: 'Ewro',
        is_active: true,
        sort_order: 3,
      },
      {
        id: 4,
        code: 'SYP',
        name: 'Syrian Pound',
        name_en: 'Syrian Pound',
        name_ar: 'الليرة السورية',
        name_ku: 'Lîra Sûrî',
        is_active: true,
        sort_order: 4,
      },
    ];
  }
}

export const currencyService = new CurrencyService();
export default currencyService;