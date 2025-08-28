import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';
import Backend from 'i18next-http-backend';

i18n
  // Load translations from backend
  .use(Backend)
  // Detect user language
  .use(LanguageDetector)
  // Pass the i18n instance to react-i18next
  .use(initReactI18next)
  // Initialize i18next
  .init({
    // Default language - check localStorage first
    lng: localStorage.getItem('language') || 'ar',
    fallbackLng: 'en',
    debug: false,
    // Common namespace used around the app
    ns: ['translation'],
    defaultNS: 'translation',
    supportedLngs: ['en', 'ar', 'ku'],
    
    // Language detection options
    detection: {
      // Order of language detection - prioritize localStorage
      order: ['localStorage', 'navigator', 'htmlTag'],
      
      // Keys or params to lookup language from
      lookupLocalStorage: 'language',
      
      // Cache user language - only use localStorage
      caches: ['localStorage'],
      
      // Optional expire and domain for set cookie
      cookieMinutes: 10080, // 7 days
    },
    
    interpolation: {
      escapeValue: false, // React already safes from XSS
    },
  });

export default i18n;