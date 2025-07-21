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
    // Default language
    lng: 'ar',
    fallbackLng: 'en',
    debug: false,
    // Common namespace used around the app
    ns: ['translation'],
    defaultNS: 'translation',
    supportedLngs: ['en', 'ar'],
    interpolation: {
      escapeValue: false, // React already safes from XSS
    },
    // Remove the dir: 'rtl' line - we'll handle this in the changeLanguage function
  });

export default i18n;