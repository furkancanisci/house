import React from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import { Button } from '../components/ui/button';

const CookiePolicy: React.FC = () => {
  const { t } = useTranslation();
  
  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-gray-900">{t('cookiePolicy.title')}</h1>
          <p className="mt-2 text-gray-600">{t('cookiePolicy.lastUpdated')}</p>
        </div>
        
        <div className="prose max-w-none">
          <h2>{t('cookiePolicy.whatAreCookies.title')}</h2>
          <p>{t('cookiePolicy.whatAreCookies.content')}</p>
          
          <h2>{t('cookiePolicy.howWeUseCookies.title')}</h2>
          <p>{t('cookiePolicy.howWeUseCookies.content')}</p>
          
          <h2>{t('cookiePolicy.typesOfCookies.title')}</h2>
          <p>{t('cookiePolicy.typesOfCookies.content')}</p>
          
          <h2>{t('cookiePolicy.managingCookies.title')}</h2>
          <p>{t('cookiePolicy.managingCookies.content')}</p>
          
          <h2>{t('cookiePolicy.contactUs.title')}</h2>
          <p>{t('cookiePolicy.contactUs.content')}</p>
        </div>
        
        <div className="mt-8 text-center">
          <Link to="/">
            <Button variant="outline">
              {t('common.backToHome')}
            </Button>
          </Link>
        </div>
      </div>
    </div>
  );
};

export default CookiePolicy;
