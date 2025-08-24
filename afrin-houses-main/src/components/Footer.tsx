import React from 'react';
import { Link } from 'react-router-dom';
import { Building, Facebook, Twitter, Instagram, Mail, Phone, MapPin } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import logo from '../assets/logo.png';

const Footer: React.FC = () => {
  const { t } = useTranslation();
  return (
    <footer className="bg-gray-900 text-white">
      <div className="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6 py-4 sm:py-6">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 sm:gap-6">
          {/* Company Info */}
          <div className="col-span-1 md:col-span-2">
            <div className="flex items-center space-x-2 mb-2">
              <img src={logo} alt="Logo" className="h-6 sm:h-8 w-auto" />
              <span className="text-lg sm:text-xl font-bold">{t('language') === 'ar' ? 'بيست ترند' : 'Best Trend'}</span>
            </div>
            <p className="text-gray-300 mb-2 max-w-md text-sm">
              {t('footer.description')}
            </p>
            <div className="flex space-x-3">
              <a href="#" className="text-gray-400 hover:text-[#067977] transition-colors">
                <Facebook className="h-4 w-4" />
              </a>
              <a href="#" className="text-gray-400 hover:text-[#067977] transition-colors">
                <Twitter className="h-4 w-4" />
              </a>
              <a href="#" className="text-gray-400 hover:text-[#067977] transition-colors">
                <Instagram className="h-4 w-4" />
              </a>
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="text-base sm:text-lg font-semibold mb-2">{t('footer.quickLinks')}</h3>
            <ul className="space-y-1">
              <li>
                <Link to="/" className="text-gray-300 hover:text-white transition-colors text-sm">
                  {t('footer.home')}
                </Link>
              </li>
              <li>
                <Link to="/search" className="text-gray-300 hover:text-white transition-colors text-sm">
                  {t('footer.searchProperties')}
                </Link>
              </li>
              <li>
                <Link to="/add-property" className="text-gray-300 hover:text-white transition-colors text-sm">
                  {t('footer.listProperty')}
                </Link>
              </li>
              <li>
                <Link to="/about" className="text-gray-300 hover:text-white transition-colors text-sm">
                  {t('footer.aboutUs')}
                </Link>
              </li>
              <li>
                <Link to="/contact" className="text-gray-300 hover:text-white transition-colors text-sm">
                  {t('footer.contactUs')}
                </Link>
              </li>
            </ul>
          </div>

          {/* Contact Info */}
          <div>
            <h3 className="text-base sm:text-lg font-semibold mb-2">{t('footer.contactUs')}</h3>
            <ul className="space-y-2">
              <li className="flex items-center space-x-2">
                <MapPin className="h-3 w-3 text-[#067977] flex-shrink-0" />
                <span className="text-gray-300 text-xs">
                  {t('footer.address')}
                </span>
              </li>
              <li className="flex items-center space-x-2">
                <Phone className="h-3 w-3 text-[#067977] flex-shrink-0" />
                <span className="text-gray-300 text-xs">
                  (555) 123-4567
                </span>
              </li>
              <li className="flex items-center space-x-2">
                <Mail className="h-3 w-3 text-[#067977] flex-shrink-0" />
                <span className="text-gray-300 text-xs">
                  info@realestate.com
                </span>
              </li>
            </ul>
          </div>
        </div>

        <div className="border-t border-gray-800 mt-4 pt-4">
          <div className="flex flex-col md:flex-row justify-between items-center">
            <p className="text-gray-400 text-xs">
              © 2025 {t('language') === 'ar' ? 'بيست ترند' : 'Best Trend'}. {t('footer.allRightsReserved')}.
            </p>
            <div className="flex space-x-4 mt-2 md:mt-0">
              <Link to="/privacy-policy" className="text-gray-400 hover:text-white text-xs transition-colors">
                {t('privacyPolicy.title')}
              </Link>
              <Link to="/terms-of-service" className="text-gray-400 hover:text-white text-xs transition-colors">
                {t('termsOfService.title')}
              </Link>
              <Link to="/cookie-policy" className="text-gray-400 hover:text-white text-xs transition-colors">
                {t('cookiePolicy.title')}
              </Link>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
