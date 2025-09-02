import React from 'react';
import { useTranslation } from 'react-i18next';
import { Badge } from './ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Utility } from '../types';
import {
  Zap,
  Droplets,
  Flame,
  Wifi,
  Trash2,
  Phone,
  Tv,
  Wind,
  Sun,
  Snowflake,
  Radio,
  Satellite,
  Cable,
  Globe,
  Power,
  Lightbulb,
  Thermometer,
  WashingMachine,
  Refrigerator,
  Car,
  Shield,
  Camera,
  Bell,
  Settings,
  CheckCircle
} from 'lucide-react';

interface PropertyUtilitiesProps {
  utilities: Utility[];
  propertyUtilities?: any[]; // Utilities associated with this specific property
  loading?: boolean;
  className?: string;
  showTitle?: boolean;
  maxDisplay?: number;
}

// Icon mapping for utilities
const getUtilityIcon = (iconName?: string, utilityName?: string) => {
  const iconMap: { [key: string]: React.ComponentType<any> } = {
    // Utility icons
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

  // Try to match by icon name first
  if (iconName && iconMap[iconName.toLowerCase()]) {
    return iconMap[iconName.toLowerCase()];
  }

  // Try to match by utility name
  if (utilityName) {
    const lowerName = utilityName.toLowerCase();
    for (const [key, IconComponent] of Object.entries(iconMap)) {
      if (lowerName.includes(key)) {
        return IconComponent;
      }
    }
  }

  // Default icon
  return Settings;
};

const PropertyUtilities: React.FC<PropertyUtilitiesProps> = ({
  utilities,
  propertyUtilities = [],
  loading = false,
  className = '',
  showTitle = true,
  maxDisplay
}) => {
  const { t, i18n } = useTranslation();
  const currentLanguage = i18n.language;
  const isRTL = currentLanguage === 'ar' || currentLanguage === 'ku';

  // Get the appropriate name based on current language
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

  // Get utilities that are associated with this property
  const getPropertyUtilities = (): Utility[] => {
    if (!propertyUtilities || propertyUtilities.length === 0) {
      return [];
    }

    // If propertyUtilities contains utility IDs, match them with the utilities array
    if (typeof propertyUtilities[0] === 'number' || typeof propertyUtilities[0] === 'string') {
      return utilities.filter(utility => 
        propertyUtilities.includes(utility.id) || propertyUtilities.includes(String(utility.id))
      );
    }

    // If propertyUtilities contains utility objects, use them directly
    if (typeof propertyUtilities[0] === 'object') {
      return propertyUtilities.filter(utility => utility && utility.id);
    }

    return [];
  };

  // Filter active utilities and apply max display limit
  const propertyUtilitiesList = getPropertyUtilities();
  const activeUtilities = propertyUtilitiesList.filter(utility => utility.is_active !== false);
  const displayUtilities = maxDisplay 
    ? activeUtilities.slice(0, maxDisplay)
    : activeUtilities;

  if (loading) {
    return (
      <Card className={className}>
        <CardHeader>
          <CardTitle>{t('property.utilities', 'Utilities & Services')}</CardTitle>
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

  if (!displayUtilities.length) {
    return null;
  }

  return (
    <Card className={className}>
      <CardHeader>
        <CardTitle>{t('property.utilities', 'Utilities & Services')}</CardTitle>
      </CardHeader>
      <CardContent>
        <div className={`flex flex-wrap gap-2 ${
          isRTL ? 'justify-end' : 'justify-start'
        }`}>
          {displayUtilities.map((utility) => {
            const IconComponent = getUtilityIcon(utility.icon, getUtilityName(utility));
            const utilityName = getUtilityName(utility);
            
            return (
              <Badge
                key={utility.id}
                variant="secondary"
                className={`flex items-center gap-2 px-3 py-2 text-sm font-medium 
                  bg-green-50 text-green-700 border border-green-200 
                  hover:bg-green-100 transition-colors duration-200
                  dark:bg-green-900/20 dark:text-green-300 dark:border-green-800
                  ${isRTL ? 'flex-row-reverse' : 'flex-row'}
                `}
              >
                <IconComponent className="w-4 h-4 flex-shrink-0" />
                <span className="truncate max-w-[120px]" title={utilityName}>
                  {utilityName}
                </span>
              </Badge>
            );
          })}
          
          {maxDisplay && activeUtilities.length > maxDisplay && (
            <Badge
              variant="outline"
              className={`px-3 py-2 text-sm font-medium text-gray-600 
                border-gray-300 hover:bg-gray-50 transition-colors duration-200
                dark:text-gray-400 dark:border-gray-600 dark:hover:bg-gray-800
              `}
            >
              +{activeUtilities.length - maxDisplay} {t('common.more', 'more')}
            </Badge>
          )}
        </div>
      </CardContent>
    </Card>
  );
};

export default PropertyUtilities;