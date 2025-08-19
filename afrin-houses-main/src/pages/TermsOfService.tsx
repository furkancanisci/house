import React from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import { Button } from '../components/ui/button';

const TermsOfService: React.FC = () => {
  const { t } = useTranslation();
  
  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-gray-900">{t('termsOfService.title')}</h1>
          <p className="mt-2 text-gray-600">{t('termsOfService.lastUpdated')}</p>
        </div>
        
        <div className="prose max-w-none">
          <h2>{t('termsOfService.introduction.title')}</h2>
          <p>{t('termsOfService.introduction.content')}</p>
          
          <h2>{t('termsOfService.accountTerms.title')}</h2>
          <p>{t('termsOfService.accountTerms.content')}</p>
          
          <h2>{t('termsOfService.userResponsibilities.title')}</h2>
          <p>{t('termsOfService.userResponsibilities.content')}</p>
          
          <h2>{t('termsOfService.limitationOfLiability.title')}</h2>
          <p>{t('termsOfService.limitationOfLiability.content')}</p>
          
          <h2>{t('termsOfService.contactUs.title')}</h2>
          <p>{t('termsOfService.contactUs.content')}</p>
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

export default TermsOfService;
