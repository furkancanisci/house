import React, { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { 
  Home, 
  Search, 
  Heart, 
  Plus, 
  User, 
  LogOut, 
  Menu, 
  X,
  Building,
  Key,
  DollarSign,
  ChevronDown
} from 'lucide-react';
import logo from '../assets/logo.png';
import { Button } from './ui/button';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from './ui/dropdown-menu';
// Add to the imports
import { useTranslation } from 'react-i18next';
import { Globe } from 'lucide-react';

const Header: React.FC = () => {
  const { state, logout, changeLanguage } = useApp();
  const { user, language } = state;
  const navigate = useNavigate();
  const location = useLocation();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const { t } = useTranslation();

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  const isActive = (path: string) => location.pathname === path;

  const navItems = [
    { path: '/', label: t('navigation.home'), icon: Home },
    { path: '/search', label: t('navigation.search'), icon: Search },
    { path: '/houses-for-rent', label: t('navigation.housesForRent'), icon: Key },
    { path: '/houses-for-sale', label: t('navigation.housesForSale'), icon: DollarSign },
    ...(user ? [{ path: '/favorites', label: t('navigation.favorites'), icon: Heart }] : []),
  ];

  // Language options
  const languages = [
    { code: 'en', name: 'English', nativeName: 'English' },
    { code: 'ar', name: 'Arabic', nativeName: 'العربية' },
    { code: 'ku', name: 'Kurdish', nativeName: 'Kurdî' }
  ];

  // Get current language info
  const getCurrentLanguage = () => {
    return languages.find(lang => lang.code === language) || languages[0];
  };

  // Handle language selection from dropdown
  const handleLanguageSelect = (languageCode: string) => {
    changeLanguage(languageCode);
  };

  return (
    <header className="bg-white shadow-lg sticky top-0 z-50">
      <nav className="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6">
        <div className="flex justify-between items-center h-14 sm:h-16">
          {/* Logo */}
          <Link to="/" className="flex items-center space-x-1.5 sm:space-x-2 flex-shrink-0">
            <img src={logo} alt="Logo" className="h-8 sm:h-10 w-auto" />
            <span className="text-lg sm:text-xl lg:text-2xl font-bold text-[#067977] whitespace-nowrap">
              {language === 'ar' ? 'بيست ترند' : 
               language === 'ku' ? 'Trend Baş' : 
               'Best Trend'}
            </span>
          </Link>

          {/* Desktop Navigation - Hidden on smaller screens */}
          <div className="hidden lg:flex items-center space-x-6">
            {navItems.map(({ path, label, icon: Icon }) => (
              <Link
                key={path}
                to={path}
                className={`flex items-center space-x-1 px-2 py-1.5 rounded-md text-sm font-medium transition-colors ${
                  isActive(path)
                    ? 'text-[#067977] bg-[#067977]/10'
                  : 'text-gray-700 hover:text-[#067977] hover:bg-gray-50'
                }`}
              >
                <Icon className="h-4 w-4" />
                <span className="hidden xl:block">{label}</span>
              </Link>
            ))}
          </div>

          {/* User Actions - Responsive */}
          <div className="hidden md:flex items-center space-x-2 lg:space-x-3 flex-shrink-0">
            {/* Language Dropdown - Compact on smaller screens */}
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button
                  variant="ghost"
                  size="sm"
                  className="flex items-center space-x-1 text-[#067977] hover:text-[#067977]/80 hover:bg-[#067977]/10 border border-[#067977]/20 hover:border-[#067977]/40 px-2 lg:px-3"
                >
                  <Globe className="h-3 w-3 lg:h-4 lg:w-4 text-[#067977]" />
                  <span className="text-xs lg:text-sm">{getCurrentLanguage().nativeName}</span>
                  <ChevronDown className="h-2 w-2 lg:h-3 lg:w-3 text-[#067977] ml-1" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" className="w-36 lg:w-40">
                {languages.map((lang) => (
                  <DropdownMenuItem
                    key={lang.code}
                    onClick={() => handleLanguageSelect(lang.code)}
                    className={`cursor-pointer flex items-center space-x-2 ${
                      language === lang.code
                        ? 'bg-[#067977]/10 text-[#067977] font-medium'
                        : 'hover:bg-gray-50'
                    }`}
                  >
                    <Globe className="h-3 w-3" />
                    <div className="flex flex-col">
                      <span className="text-sm">{lang.nativeName}</span>
                      <span className="text-xs text-gray-500">{lang.name}</span>
                    </div>
                    {language === lang.code && (
                      <div className="ml-auto h-2 w-2 rounded-full bg-[#067977]"></div>
                    )}
                  </DropdownMenuItem>
                ))}
              </DropdownMenuContent>
            </DropdownMenu>

            {/* List Property Button - More compact */}
            <Button
              variant="outline"
              size="sm"
              onClick={() => {
                if (user) {
                  navigate('/add-property');
                } else {
                  navigate('/auth');
                }
              }}
              className="flex items-center space-x-1 text-[#067977] border-[#067977] hover:bg-[#067977] hover:text-white transition-colors px-2 lg:px-3 text-xs lg:text-sm"
            >
              <Plus className="h-3 w-3 lg:h-4 lg:w-4" />
              <span className="hidden lg:block">{t('navigation.listProperty')}</span>
              <span className="lg:hidden">List</span>
            </Button>

            {user ? (
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="ghost" size="sm" className="flex items-center space-x-1 lg:space-x-2 px-2 lg:px-3">
                    <User className="h-3 w-3 lg:h-4 lg:w-4" />
                    <span className="text-xs lg:text-sm max-w-[80px] lg:max-w-none truncate">{user.name}</span>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-44 lg:w-48">
                  <DropdownMenuItem onClick={() => navigate('/dashboard')}>
                    <User className="mr-2 h-4 w-4" />
                    {t('navigation.dashboard')}
                  </DropdownMenuItem>
                  <DropdownMenuItem onClick={() => navigate('/favorites')}>
                    <Heart className="mr-2 h-4 w-4" />
                    {t('navigation.favorites')}
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem onClick={handleLogout}>
                    <LogOut className="mr-2 h-4 w-4" />
                    {t('navigation.logout')}
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            ) : (
              <Button onClick={() => navigate('/auth')} size="sm" className="bg-[#067977] hover:bg-[#067977]/90 text-white px-2 lg:px-4 text-xs lg:text-sm">
                {t('navigation.login')}
              </Button>
            )}
          </div>

          {/* Mobile menu button */}
          <div className="md:hidden flex-shrink-0">
            <Button
              variant="ghost"
              size="sm"
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="p-2"
            >
              {mobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
            </Button>
          </div>
        </div>

        {/* Mobile Navigation */}
        {mobileMenuOpen && (
          <div className="md:hidden border-t border-gray-200 py-3">
            <div className="flex flex-col space-y-1.5">
              {navItems.map(({ path, label, icon: Icon }) => (
                <Link
                  key={path}
                  to={path}
                  onClick={() => setMobileMenuOpen(false)}
                  className={`flex items-center space-x-2 px-3 py-2 rounded-md text-sm font-medium transition-colors ${
                    isActive(path)
                      ? 'text-[#067977] bg-[#067977]/10'
                  : 'text-gray-700 hover:text-[#067977] hover:bg-gray-50'
                  }`}
                >
                  <Icon className="h-4 w-4" />
                  <span>{label}</span>
                </Link>
              ))}
              
              {/* List Property Button - Mobile */}
              <button
                onClick={() => {
                  if (user) {
                    navigate('/add-property');
                  } else {
                    navigate('/auth');
                  }
                  setMobileMenuOpen(false);
                }}
                className="flex items-center space-x-2 px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-[#067977] hover:bg-gray-50 w-full text-left"
              >
                <Plus className="h-4 w-4" />
                <span>{t('navigation.listProperty')}</span>
              </button>
              
              {/* Language Selection in Mobile */}
              <div className="px-3 py-2">
                <div className="text-xs font-medium text-gray-500 mb-2">Language / اللغة / Ziman</div>
                <div className="space-y-1">
                  {languages.map((lang) => (
                    <button
                      key={lang.code}
                      onClick={() => {
                        handleLanguageSelect(lang.code);
                        setMobileMenuOpen(false);
                      }}
                      className={`flex items-center space-x-2 px-2 py-1.5 rounded-md text-sm font-medium w-full text-left transition-colors ${
                        language === lang.code
                          ? 'bg-[#067977]/10 text-[#067977] font-medium'
                          : 'text-gray-700 hover:text-[#067977] hover:bg-gray-50'
                      }`}
                    >
                      <Globe className="h-3 w-3" />
                      <div className="flex flex-col">
                        <span className="text-sm">{lang.nativeName}</span>
                        <span className="text-xs text-gray-400">{lang.name}</span>
                      </div>
                      {language === lang.code && (
                        <div className="ml-auto h-2 w-2 rounded-full bg-[#067977]"></div>
                      )}
                    </button>
                  ))}
                </div>
              </div>
              
              {user ? (
                <>
                  <Link
                    to="/dashboard"
                    onClick={() => setMobileMenuOpen(false)}
                    className="flex items-center space-x-2 px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-[#067977] hover:bg-gray-50"
                  >
                    <User className="h-4 w-4" />
                    <span>{t('navigation.dashboard')}</span>
                  </Link>
                  <button
                    onClick={() => {
                      handleLogout();
                      setMobileMenuOpen(false);
                    }}
                    className="flex items-center space-x-2 px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-[#067977] hover:bg-gray-50 w-full text-left"
                  >
                    <LogOut className="h-4 w-4" />
                    <span>{t('navigation.logout')}</span>
                  </button>
                </>
              ) : (
                <button
                  onClick={() => {
                    navigate('/auth');
                    setMobileMenuOpen(false);
                  }}
                  className="flex items-center space-x-2 px-3 py-2 mx-3 rounded-md text-sm font-medium text-white bg-[#067977] hover:bg-[#067977]/90 transition-colors"
                >
                  <User className="h-4 w-4" />
                  <span>{t('navigation.login')}</span>
                </button>
              )}
            </div>
          </div>
        )}
      </nav>
    </header>
  );
};

export default Header;
