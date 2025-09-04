import React from 'react';
import { useTranslation } from 'react-i18next';
import { Badge } from './ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Feature } from '../types';
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
  Settings
} from 'lucide-react';

interface PropertyFeaturesProps {
  features: Feature[];
  propertyFeatures?: any[]; // Features associated with this specific property
  loading?: boolean;
  className?: string;
  showTitle?: boolean;
  maxDisplay?: number;
}

// Icon mapping for features
const getFeatureIcon = (iconName?: string, featureName?: string) => {
  const iconMap: { [key: string]: React.ComponentType<any> } = {
    // Common feature icons
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

  // Try to match by icon name first
  if (iconName && iconMap[iconName.toLowerCase()]) {
    return iconMap[iconName.toLowerCase()];
  }

  // Try to match by feature name
  if (featureName) {
    const lowerName = featureName.toLowerCase();
    for (const [key, IconComponent] of Object.entries(iconMap)) {
      if (lowerName.includes(key)) {
        return IconComponent;
      }
    }
  }

  // Default icon
  return Settings;
};

const PropertyFeatures: React.FC<PropertyFeaturesProps> = ({
  features,
  propertyFeatures = [],
  loading = false,
  className = '',
  showTitle = true,
  maxDisplay
}) => {
  const { t, i18n } = useTranslation();
  const currentLanguage = i18n.language;
  const isRTL = currentLanguage === 'ar' || currentLanguage === 'ku';

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

  // Get features that are associated with this property
  const getPropertyFeatures = (): Feature[] => {
    if (!propertyFeatures || propertyFeatures.length === 0) {
      return [];
    }

    // If propertyFeatures contains feature IDs, match them with the features array
    if (typeof propertyFeatures[0] === 'number' || typeof propertyFeatures[0] === 'string') {
      return features.filter(feature => 
        propertyFeatures.includes(feature.id) || propertyFeatures.includes(String(feature.id))
      );
    }

    // If propertyFeatures contains feature objects, use them directly
    if (typeof propertyFeatures[0] === 'object') {
      return propertyFeatures.filter(feature => feature && feature.id);
    }

    return [];
  };

  // Filter active features and apply max display limit
  const propertyFeaturesList = getPropertyFeatures();
  const activeFeatures = propertyFeaturesList.filter(feature => feature.is_active !== false);
  const displayFeatures = maxDisplay 
    ? activeFeatures.slice(0, maxDisplay)
    : activeFeatures;

  if (loading) {
    return (
      <Card className={className}>
        <CardHeader>
          <CardTitle>{t('property.features', 'Features & Amenities')}</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-center justify-center py-4">
            <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span className="ml-2 text-gray-600">{t('common.loading', 'Loading...')}</span>
          </div>
        </CardContent>
      </Card>
    );
  }

  if (!displayFeatures.length) {
    return null;
  }

  return (
    <Card className={className}>
      <CardHeader>
        <CardTitle>{t('property.features', 'Features & Amenities')}</CardTitle>
      </CardHeader>
      <CardContent>
        <div className={`flex flex-wrap gap-2 ${
          isRTL ? 'justify-end' : 'justify-start'
        }`}>
          {displayFeatures.map((feature) => {
            const IconComponent = getFeatureIcon(feature.icon, getFeatureName(feature));
            const featureName = getFeatureName(feature);
            
            return (
              <Badge
                key={feature.id}
                variant="secondary"
                className={`flex items-center gap-2 px-3 py-2 text-sm font-medium 
                  bg-blue-50 text-blue-700 border border-blue-200 
                  hover:bg-blue-100 transition-colors duration-200
                  dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800
                  ${isRTL ? 'flex-row-reverse' : 'flex-row'}
                `}
              >
                <IconComponent className="w-4 h-4 flex-shrink-0" />
                <span className="truncate max-w-[120px]" title={featureName}>
                  {featureName}
                </span>
              </Badge>
            );
          })}
          
          {maxDisplay && activeFeatures.length > maxDisplay && (
            <Badge
              variant="outline"
              className={`px-3 py-2 text-sm font-medium text-gray-600 
                border-gray-300 hover:bg-gray-50 transition-colors duration-200
                dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-800
              `}
            >
              +{activeFeatures.length - maxDisplay} {t('common.more', 'more')}
            </Badge>
          )}
        </div>
      </CardContent>
    </Card>
  );
};

export default PropertyFeatures;