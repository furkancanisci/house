import api from './api';

export interface HomeStat {
  id: number;
  key: string;
  icon: string;
  number: string;
  label_ar: string;
  label_en: string;
  label_ku: string;
  color: string;
  is_active: boolean;
  order: number;
}

export interface HomeStatsResponse {
  status: string;
  data: HomeStat[];
}

/**
 * Fetch home statistics from the API
 */
export const getHomeStats = async (): Promise<HomeStat[]> => {
  try {
    const response = await api.get('/home-stats');

    if (response.data.status === 'success' && Array.isArray(response.data.data)) {
      return response.data.data;
    } else {
      throw new Error('Invalid response format');
    }
  } catch (error) {

    // Return empty array as fallback
    return [];
  }
};

/**
 * Get localized label for a statistic
 */
export const getLocalizedLabel = (stat: HomeStat, language: string): string => {
  switch (language) {
    case 'ar':
      return stat.label_ar;
    case 'ku':
      return stat.label_ku;
    case 'en':
    default:
      return stat.label_en;
  }
};