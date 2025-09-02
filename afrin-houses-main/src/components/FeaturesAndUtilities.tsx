import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Badge } from './ui/badge';
import { Button } from './ui/button';
import { Separator } from './ui/separator';
import { Feature, Utility } from '../types';
import {
  Wifi,
  Car,
  Waves,
  TreePine,
  Shield,
  Zap,
  Wind,
  Flame,
  Camera,
  Dumbbell,
  Users,
  Baby,
  PawPrint,
  Utensils,
  Tv,
  WashingMachine,
  Refrigerator,
  Microwave,
  AirVent,
  Sun,
  Moon,
  Home,
  Building,
  MapPin,
  Star,
  CheckCircle,
  Settings,
  Droplets,
  Phone,
  Trash2,
  Snowflake,
  Radio,
  Satellite,
  Cable,
  Globe,
  Power,
  Lightbulb,
  Thermometer,
  Bell,
  ChevronDown,
  ChevronUp,
  Grid3X3,
  Wrench
} from 'lucide-react';

interface FeaturesAndUtilitiesProps {
  features: Feature[];
  utilities: Utility[];
  propertyFeatures?: any[];
  propertyUtilities?: any[];
  loading?: boolean;
  className?: string;
}

// Enhanced icon mapping for features
const getFeatureIcon = (iconName?: string, featureName?: string) => {
  const iconMap: { [key: string]: React.ComponentType<any> } = {
    'wifi': Wifi,
    'internet': Wifi,
    'parking': Car,
    'garage': Car,
    'pool': Waves,
    'swimming': Waves,
    'garden': TreePine,
    'yard': TreePine,
    'security': Shield,
    'alarm': Shield,
    'electricity': Zap,
    'power': Zap,
    'ac': Wind,
    'air-conditioning': Wind,
    'heating': Flame,
    'heat': Flame,
    'camera': Camera,
    'cctv': Camera,
    'gym': Dumbbell,
    'fitness': Dumbbell,
    'community': Users,
    'social': Users,
    'playground': Baby,
    'kids': Baby,
    'pets': PawPrint,
    'pet-friendly': PawPrint,
    'kitchen': Utensils,
    'dining': Utensils,
    'tv': Tv,
    'television': Tv,
    'laundry': WashingMachine,
    'washing': WashingMachine,
    'fridge': Refrigerator,
    'refrigerator': Refrigerator,
    'microwave': Microwave,
    'ventilation': AirVent,
    'balcony': Sun,
    'terrace': Sun,
    'night': Moon,
    'lighting': Moon,
    'furnished': Home,
    'furniture': Home,
    'elevator': Building,
    'lift': Building,
    'location': MapPin,
    'premium': Star,
    'luxury': Star,
    'verified': CheckCircle,
    'approved': CheckCircle
  };

  if (iconName && iconMap[iconName.toLowerCase()]) {
    return iconMap[iconName.toLowerCase()];
  }

  if (featureName) {
    const lowerName = featureName.toLowerCase();
    for (const [key, IconComponent] of Object.entries(iconMap)) {
      if (lowerName.includes(key)) {
        return IconComponent;
      }
    }
  }

  return Settings;
};

// Enhanced icon mapping for utilities
const getUtilityIcon = (iconName?: string, utilityName?: string) => {
  const iconMap: { [key: string]: React.ComponentType<any> } = {
    'electricity': Zap,
    'electric': Zap,
    'power': Power,
    'water': Droplets,
    'plumbing': Droplets,
    'gas': Flame,
    'natural-gas': Flame,
    'internet': Wifi,
    'wifi': Wifi,
    'broadband': Wifi,
    'trash': Trash2,
    'garbage': Trash2,
    'waste': Trash2,
    'phone': Phone,
    'telephone': Phone,
    'landline': Phone,
    'cable': Cable,
    'cable-tv': Tv,
    'television': Tv,
    'tv': Tv,
    'satellite': Satellite,
    'satellite-tv': Satellite,
    'heating': Flame,
    'heat': Thermometer,
    'cooling': Wind,
    'ac': Wind,
    'air-conditioning': Snowflake,
    'solar': Sun,
    'solar-power': Sun,
    'radio': Radio,
    'internet-radio': Radio,
    'fiber': Globe,
    'fiber-optic': Globe,
    'lighting': Lightbulb,
    'lights': Lightbulb,
    'laundry': WashingMachine,
    'washing': WashingMachine,
    'appliances': Refrigerator,
    'parking': Car,
    'garage': Car,
    'security': Shield,
    'alarm': Shield,
    'surveillance': Camera,
    'cctv': Camera,
    'doorbell': Bell,
    'intercom': Bell,
    'maintenance': Settings,
    'service': Settings,
    'included': CheckCircle,
    'available': CheckCircle
  };

  if (iconName && iconMap[iconName.toLowerCase()]) {
    return iconMap[iconName.toLowerCase()];
  }

  if (utilityName) {
    const lowerName = utilityName.toLowerCase();
    for (const [key, IconComponent] of Object.entries(iconMap)) {
      if (lowerName.includes(key)) {
        return IconComponent;
      }
    }
  }

  return Wrench;
};

const FeaturesAndUtilities: React.FC<FeaturesAndUtilitiesProps> = ({
  features,
  utilities,
  propertyFeatures = [],
  propertyUtilities = [],
  loading = false,
  className = ''
}) => {
  const { t, i18n } = useTranslation();
  const currentLanguage = i18n.language;
  const isRTL = currentLanguage === 'ar' || currentLanguage === 'ku';
  const [showAllFeatures, setShowAllFeatures] = useState(false);
  const [showAllUtilities, setShowAllUtilities] = useState(false);

  // Get the appropriate name based on current language
  const getFeatureName = (feature: Feature): string => {
    switch (currentLanguage) {
      case 'ar':
        return feature.name_ar || feature.name_en || feature.name_ku;
      case 'ku':
        return feature.name_ku || feature.name_en || feature.name_ar;
      case 'en':
      default:
        return feature.name_en || feature.name_ar || feature.name_ku;
    }
  };

  const getUtilityName = (utility: Utility): string => {
    switch (currentLanguage) {
      case 'ar':
        return utility.name_ar || utility.name_en || utility.name_ku;
      case 'ku':
        return utility.name_ku || utility.name_en || utility.name_ar;
      case 'en':
      default:
        return utility.name_en || utility.name_ar || utility.name_ku;
    }
  };

  // Get features that are associated with this property
  const getPropertyFeatures = (): Feature[] => {
    if (!propertyFeatures || propertyFeatures.length === 0) {
      return [];
    }

    if (typeof propertyFeatures[0] === 'number' || typeof propertyFeatures[0] === 'string') {
      return features.filter(feature => 
        propertyFeatures.includes(feature.id) || propertyFeatures.includes(String(feature.id))
      );
    }

    if (typeof propertyFeatures[0] === 'object') {
      return propertyFeatures.filter(feature => feature && feature.id);
    }

    return [];
  };

  // Get utilities that are associated with this property
  const getPropertyUtilities = (): Utility[] => {
    if (!propertyUtilities || propertyUtilities.length === 0) {
      return [];
    }

    if (typeof propertyUtilities[0] === 'number' || typeof propertyUtilities[0] === 'string') {
      return utilities.filter(utility => 
        propertyUtilities.includes(utility.id) || propertyUtilities.includes(String(utility.id))
      );
    }

    if (typeof propertyUtilities[0] === 'object') {
      return propertyUtilities.filter(utility => utility && utility.id);
    }

    return [];
  };

  const propertyFeaturesList = getPropertyFeatures().filter(feature => feature.is_active !== false);
  const propertyUtilitiesList = getPropertyUtilities().filter(utility => utility.is_active !== false);

  // Compact view: show only 4 items by default instead of 6
  const displayFeatures = showAllFeatures ? propertyFeaturesList : propertyFeaturesList.slice(0, 4);
  const displayUtilities = showAllUtilities ? propertyUtilitiesList : propertyUtilitiesList.slice(0, 4);

  if (loading) {
    return (
      <Card className={`overflow-hidden ${className}`}>
        <CardHeader className="bg-gradient-to-r from-blue-50 to-green-50 border-b p-3">
          <CardTitle className="flex items-center gap-2 text-base font-bold text-gray-800">
            <div className="p-1.5 bg-white rounded-md shadow-xs">
              <Grid3X3 className="h-4 w-4 text-blue-600" />
            </div>
            <span className="text-sm">{t('property.featuresAndUtilities', 'Features & Utilities')}</span>
          </CardTitle>
        </CardHeader>
        <CardContent className="p-3">
          <div className="flex items-center justify-center py-4">
            <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
            <span className="ml-2 text-gray-500 text-xs font-medium">{t('common.loading', 'Loading...')}</span>
          </div>
        </CardContent>
      </Card>
    );
  }

  if (!propertyFeaturesList.length && !propertyUtilitiesList.length) {
    return (
      <Card className={`overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-200 ${className}`}>
        <CardHeader className="bg-gradient-to-r from-blue-50 to-green-50 border-b p-3">
          <CardTitle className="flex items-center gap-2 text-base font-bold text-gray-800">
            <div className="p-1.5 bg-white rounded-md shadow-xs">
              <Grid3X3 className="h-4 w-4 text-blue-600" />
            </div>
            <span className="text-sm">{t('property.featuresAndUtilities', 'Features & Utilities')}</span>
          </CardTitle>
        </CardHeader>
        <CardContent className="p-3">
          <div className="text-center py-4">
            <div className="text-gray-400 mb-1">
              <Grid3X3 className="h-8 w-8 mx-auto mb-2 opacity-50" />
            </div>
            <p className="text-gray-500 text-xs">
              {t('property.noFeaturesUtilities', 'No features or utilities information available for this property.')}
            </p>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card className={`overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-200 ${className}`}>
      <CardHeader className="bg-gradient-to-r from-blue-50 to-green-50 border-b p-3">
        <CardTitle className="flex items-center gap-2 text-base font-bold text-gray-800">
          <div className="p-1.5 bg-white rounded-md shadow-xs">
            <Grid3X3 className="h-4 w-4 text-blue-600" />
          </div>
          <span className="text-sm">{t('property.featuresAndUtilities', 'Features & Utilities')}</span>
        </CardTitle>
      </CardHeader>
      
      <CardContent className="p-3 space-y-4">
        {/* Features Section */}
        {propertyFeaturesList.length > 0 && (
          <div className="space-y-3">
            <div className="flex items-center gap-2 mb-2">
              <div className="p-1.5 bg-blue-100 rounded-md">
                <Star className="h-3.5 w-3.5 text-blue-600" />
              </div>
              <h3 className="text-sm font-semibold text-gray-800">
                {t('property.features', 'Property Features')}
              </h3>
              <div className="flex-1 h-px bg-gradient-to-r from-blue-200 to-transparent"></div>
            </div>
            
            <div className={`grid grid-cols-2 gap-2 ${
              isRTL ? 'text-right' : 'text-left'
            }`}>
              {displayFeatures.map((feature) => {
                const IconComponent = getFeatureIcon(feature.icon, getFeatureName(feature));
                const featureName = getFeatureName(feature);
                
                return (
                  <div
                    key={feature.id}
                    className={`group flex items-center gap-2 p-2 bg-blue-50 hover:bg-blue-100 
                      rounded-lg border border-blue-200 hover:border-blue-300 
                      transition-all duration-150 hover:shadow-xs cursor-default text-xs
                      ${isRTL ? 'flex-row-reverse' : 'flex-row'}
                    `}
                  >
                    <div className="p-1.5 bg-blue-200 group-hover:bg-blue-300 rounded-md transition-colors duration-150">
                      <IconComponent className="w-3 h-3 text-blue-700" />
                    </div>
                    <span className="font-medium text-blue-800 truncate flex-1" title={featureName}>
                      {featureName}
                    </span>
                  </div>
                );
              })}
            </div>
            
            {propertyFeaturesList.length > 4 && (
              <div className="flex justify-center mt-2">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setShowAllFeatures(!showAllFeatures)}
                  className="flex items-center gap-1 text-blue-600 border-blue-200 hover:bg-blue-50 h-7 px-2 text-xs"
                >
                  {showAllFeatures ? (
                    <>
                      <ChevronUp className="w-3 h-3" />
                      {t('common.showLess', 'Show Less')}
                    </>
                  ) : (
                    <>
                      <ChevronDown className="w-3 h-3" />
                      {t('common.showMore', 'Show More')} ({propertyFeaturesList.length - 4})
                    </>
                  )}
                </Button>
              </div>
            )}
          </div>
        )}

        {/* Separator */}
        {propertyFeaturesList.length > 0 && propertyUtilitiesList.length > 0 && (
          <Separator className="my-3" />
        )}

        {/* Utilities Section */}
        {propertyUtilitiesList.length > 0 && (
          <div className="space-y-3">
            <div className="flex items-center gap-2 mb-2">
              <div className="p-1.5 bg-green-100 rounded-md">
                <Wrench className="h-3.5 w-3.5 text-green-600" />
              </div>
              <h3 className="text-sm font-semibold text-gray-800">
                {t('property.utilities', 'Utilities & Services')}
              </h3>
              <div className="flex-1 h-px bg-gradient-to-r from-green-200 to-transparent"></div>
            </div>
            
            <div className={`grid grid-cols-2 gap-2 ${
              isRTL ? 'text-right' : 'text-left'
            }`}>
              {displayUtilities.map((utility) => {
                const IconComponent = getUtilityIcon(utility.icon, getUtilityName(utility));
                const utilityName = getUtilityName(utility);
                
                return (
                  <div
                    key={utility.id}
                    className={`group flex items-center gap-2 p-2 bg-green-50 hover:bg-green-100 
                      rounded-lg border border-green-200 hover:border-green-300 
                      transition-all duration-150 hover:shadow-xs cursor-default text-xs
                      ${isRTL ? 'flex-row-reverse' : 'flex-row'}
                    `}
                  >
                    <div className="p-1.5 bg-green-200 group-hover:bg-green-300 rounded-md transition-colors duration-150">
                      <IconComponent className="w-3 h-3 text-green-700" />
                    </div>
                    <span className="font-medium text-green-800 truncate flex-1" title={utilityName}>
                      {utilityName}
                    </span>
                  </div>
                );
              })}
            </div>
            
            {propertyUtilitiesList.length > 4 && (
              <div className="flex justify-center mt-2">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setShowAllUtilities(!showAllUtilities)}
                  className="flex items-center gap-1 text-green-600 border-green-200 hover:bg-green-50 h-7 px-2 text-xs"
                >
                  {showAllUtilities ? (
                    <>
                      <ChevronUp className="w-3 h-3" />
                      {t('common.showLess', 'Show Less')}
                    </>
                  ) : (
                    <>
                      <ChevronDown className="w-3 h-3" />
                      {t('common.showMore', 'Show More')} ({propertyUtilitiesList.length - 4})
                    </>
                  )}
                </Button>
              </div>
            )}
          </div>
        )}
      </CardContent>
    </Card>
  );
};

export default FeaturesAndUtilities;