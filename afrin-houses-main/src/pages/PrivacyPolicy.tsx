import React from 'react';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import { Button } from '../components/ui/button';

const PrivacyPolicy: React.FC = () => {
  const { t } = useTranslation();
  
  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-gray-900">{t('privacyPolicy.title')}</h1>
          <p className="mt-2 text-gray-600">{t('privacyPolicy.lastUpdated')}</p>
        </div>
        
        <div className="prose max-w-none">
          <h2>{t('privacyPolicy.introduction.title')}</h2>
          <p>{t('privacyPolicy.introduction.content')}</p>
          
          <h2>{t('privacyPolicy.informationWeCollect.title')}</h2>
          <p>{t('privacyPolicy.informationWeCollect.content')}</p>
          
          <h2>{t('privacyPolicy.howWeUseYourData.title')}</h2>
          <p>{t('privacyPolicy.howWeUseYourData.content')}</p>
          
          <h2>{t('privacyPolicy.dataSecurity.title')}</h2>
          <p>{t('privacyPolicy.dataSecurity.content')}</p>
          
          <h2>{t('privacyPolicy.contactUs.title')}</h2>
          <p>{t('privacyPolicy.contactUs.content')}</p>
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

export default PrivacyPolicy;
