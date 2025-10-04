import React, { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { 
  Home, 
  Heart, 
  Plus, 
  User, 
  LogOut, 
  Menu, 
  X,
  Building,
  ChevronDown,
  Info,
  Mail
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
import { useAuthCheck } from '../hooks/useAuthCheck';
import AuthModal from './AuthModal';
import EmailActivationModal from './EmailActivationModal';

const Header: React.FC = () => {
  const { state, logout, changeLanguage, refreshUser } = useApp();
  const { user, language } = state;
  const navigate = useNavigate();
  const location = useLocation();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const { t } = useTranslation();
  const { 
    showAuthModal, 
    showActivationModal, 
    closeAuthModal, 
    closeActivationModal, 
    requireAuth, 
    requireVerifiedEmail,
    isCheckingAuth
  } = useAuthCheck();

  const handleLogout = () => {
    logout();
    navigate('/');
    // Show logout notification
    import('../services/notificationService').then(({ notification }) => {
      notification.success('Logged out successfully');
    });
  };

  const isActive = (path: string) => location.pathname === path;

  const navItems = [
    { path: '/', label: t('navigation.home'), icon: Home },
    { path: '/about', label: t('navigation.about'), icon: Info },
    { path: '/contact', label: t('navigation.contact'), icon: Mail },
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
            {/* Language Dropdown - Following standard dropdown patterns */}
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button
                  variant="ghost"
                  size="sm"
                  className="flex items-center space-x-1 text-gray-700 hover:text-[#067977] hover:bg-gray-50 border border-gray-200 hover:border-[#067977]/40 px-2 lg:px-3 transition-all duration-200"
                >
                  <Globe className="h-3 w-3 lg:h-4 lg:w-4" />
                  <span className="text-xs lg:text-sm font-medium">{getCurrentLanguage().nativeName}</span>
                  <ChevronDown className="h-2 w-2 lg:h-3 lg:w-3 ml-1 transition-transform duration-200 group-data-[state=open]:rotate-180" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" className="w-36 lg:w-40 bg-white border border-gray-200 shadow-lg rounded-lg overflow-hidden">
                {languages.map((lang) => (
                  <DropdownMenuItem
                    key={lang.code}
                    onClick={() => handleLanguageSelect(lang.code)}
                    className={`cursor-pointer flex items-center space-x-2 px-3 py-2 transition-colors duration-150 ${
                      language === lang.code
                        ? 'bg-[#067977]/10 text-[#067977] font-medium'
                        : 'hover:bg-gray-50 text-gray-700 hover:text-gray-900'
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

            {/* List Property Button - Following CTA patterns */}
            <Button
              variant="outline"
              size="sm"
              onClick={() => {
                requireVerifiedEmail(() => {
                  navigate('/add-property');
                });
              }}
              disabled={isCheckingAuth}
              className="flex items-center space-x-1 text-[#067977] border-[#067977] hover:bg-[#067977] hover:text-white transition-all duration-200 transform hover:scale-105 px-2 lg:px-3 text-xs lg:text-sm font-medium shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <Plus className="h-3 w-3 lg:h-4 lg:w-4" />
              <span className="hidden lg:block">{isCheckingAuth ? t('common.loading') : t('navigation.listProperty')}</span>
              <span className="lg:hidden">{isCheckingAuth ? '...' : 'List'}</span>
            </Button>

            {user ? (
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="ghost" size="sm" className="flex items-center space-x-1 lg:space-x-2 px-2 lg:px-3 hover:bg-gray-50 transition-colors duration-200">
                    <div className="w-7 h-7 lg:w-8 lg:h-8 bg-[#067977] rounded-full flex items-center justify-center">
                      <User className="h-3 w-3 lg:h-4 lg:w-4 text-white" />
                    </div>
                    <span className="text-xs lg:text-sm max-w-[80px] lg:max-w-none truncate font-medium">{user.name}</span>
                    <ChevronDown className="h-2 w-2 lg:h-3 lg:w-3 ml-1 transition-transform duration-200 group-data-[state=open]:rotate-180" />
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-44 lg:w-48 bg-white border border-gray-200 shadow-lg rounded-lg overflow-hidden">
                  <DropdownMenuItem onClick={() => navigate('/dashboard')} className="cursor-pointer hover:bg-gray-50 transition-colors duration-150 px-3 py-2">
                    <User className="mr-2 h-4 w-4 text-gray-500" />
                    <span className="text-gray-700">{t('navigation.dashboard')}</span>
                  </DropdownMenuItem>
                  <DropdownMenuItem onClick={() => navigate('/favorites')} className="cursor-pointer hover:bg-gray-50 transition-colors duration-150 px-3 py-2">
                    <Heart className="mr-2 h-4 w-4 text-gray-500" />
                    <span className="text-gray-700">{t('navigation.favorites')}</span>
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem onClick={handleLogout} className="cursor-pointer hover:bg-red-50 transition-colors duration-150 px-3 py-2">
                    <LogOut className="mr-2 h-4 w-4 text-red-500" />
                    <span className="text-red-600">{t('navigation.logout')}</span>
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            ) : (
              <Button onClick={() => navigate('/auth')} size="sm" className="bg-[#067977] hover:bg-[#067977]/90 text-white px-2 lg:px-4 text-xs lg:text-sm font-medium transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
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
                  requireVerifiedEmail(() => {
                    navigate('/add-property');
                  });
                  setMobileMenuOpen(false);
                }}
                disabled={isCheckingAuth}
                className="flex items-center space-x-2 px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-[#067977] hover:bg-gray-50 w-full text-left disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <Plus className="h-4 w-4" />
                <span>{isCheckingAuth ? t('common.loading') : t('navigation.listProperty')}</span>
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
      
      {/* Authentication Modal */}
      <AuthModal
        isOpen={showAuthModal}
        onClose={closeAuthModal}
        title={t('auth.requireAuth.title')}
        message={t('auth.requireAuth.addPropertyMessage')}
        onSuccess={() => {
          closeAuthModal();
          navigate('/add-property');
        }}
      />
      
      {/* Email Activation Modal */}
      <EmailActivationModal
        isOpen={showActivationModal}
        onClose={closeActivationModal}
        userEmail={user?.email}
      />
    </header>
  );
};

export default Header;
