import api from './api';

export interface Currency {
  id: number;
  code: string;
  name: string;
  name_ar: string;
  name_en: string;
  name_ku: string;
  symbol: string;
  is_active: boolean;
}

export interface CurrencyOption {
  value: string;
  label: string;
  symbol: string;
}

class CurrencyService {
  /**
   * Get all active currencies
   */
  async getCurrencies(): Promise<Currency[]> {
    try {
      const response = await api.get('/currencies');
      return response.data?.data || [];
    } catch (error) {
      console.error('Error fetching currencies:', error);
      throw error;
    }
  }

  /**
   * Get currencies formatted as options for Select components
   */
  async getCurrencyOptions(): Promise<CurrencyOption[]> {
    try {
      const response = await api.get('/currencies/options');
      return response.data?.data || [];
    } catch (error) {
      console.error('Error fetching currency options:', error);
      throw error;
    }
  }
}

export const currencyService = new CurrencyService();
