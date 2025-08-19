import React from 'react';
import { Link } from 'react-router-dom';
import { ExtendedProperty } from '../types';
import { useApp } from '../context/AppContext';
import { 
  Heart, 
  Bed, 
  Bath, 
  Square, 
  MapPin,
  Calendar,
  DollarSign
} from 'lucide-react';
import { Button } from './ui/button';
import { Card, CardContent, CardFooter } from './ui/card';
import { Badge } from './ui/badge';
import { useTranslation } from 'react-i18next';
import FixedImage from './FixedImage';
import PropertyImageGallery from './PropertyImageGallery';
import { processPropertyImages } from '../lib/imageUtils';

interface PropertyCardProps {
  property: ExtendedProperty;
  view?: 'grid' | 'list';
  useGallery?: boolean; // Whether to use gallery for multiple images
}

const PropertyCard: React.FC<PropertyCardProps> = ({ property, view = 'grid', useGallery = false }) => {
  const { state, toggleFavorite } = useApp();
  const { t, i18n } = useTranslation();
  const isFavorite = state.favorites.includes(property.id);
  const user = state.user;

  // Helper to normalize values that may be localized objects { name_ar, name_en }
  const normalizeName = (val: any): string => {
    if (!val) return '';
    if (typeof val === 'string') return val;
    if (typeof val === 'object') {
      const locale = i18n.language === 'ar' ? 'ar' : 'en';
      const ar = (val as any).name_ar ?? (val as any).ar ?? (val as any).name;
      const en = (val as any).name_en ?? (val as any).en ?? (val as any).name;
      return locale === 'ar' ? (ar || en || '') : (en || ar || '');
    }
    return String(val);
  };

  const handleFavoriteClick = async (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    await toggleFavorite(property.id);
  };

  const processedImages = useGallery ? processPropertyImages(property) : { mainImage: property.mainImage || '/placeholder-property.jpg', images: [] };
  const images = processedImages.images || [];
  const mainImage = processedImages.mainImage || property.mainImage || '/placeholder-property.jpg';

  const propertySlug = property.slug || `property-${property.id}`;

  const formatPrice = (price: number | string | { amount?: number } | undefined, type: string = 'sale') => {
    const numPrice = typeof price === 'object' && price !== null 
      ? (price as any).amount || 0 
      : Number(price) || 0;
    
    if (numPrice === 0) return t('property.priceOnRequest');
    
    // Format as a simple number with commas
    const formattedPrice = numPrice.toLocaleString(undefined, {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    });
    
    // Add currency symbol and rental period if needed
    if (type === 'rent') {
      return `${formattedPrice} ${t('common.currency')} / ${t('property.perMonth', 'month')}`;
    }
    
    return `${formattedPrice} ${t('common.currency')}`;
  };

  const formatDate = (dateString?: string) => {
    if (!dateString) return '';
    try {
      return new Date(dateString).toLocaleDateString(i18n.language);
    } catch (e) {
      return '';
    }
  };

  if (view === 'list') {
    return (
      <Card className="overflow-hidden hover:shadow-lg transition-shadow duration-300">
        <div className="md:flex">
          <div className="md:flex-shrink-0">
            <FixedImage
              className="h-48 w-full object-cover md:w-48"
              src={mainImage}
              alt={property.title}
            />
          </div>
          <div className="p-6 flex-1 flex flex-col justify-between">
            <div>
              <div className="flex justify-between items-start">
                <div>
                  <h3 className="text-xl font-semibold text-gray-900">
                    <Link to={`/property/${propertySlug}`}>
                      {property.title}
                    </Link>
                  </h3>
                  <p className="mt-1 text-gray-600 flex items-center">
                    <MapPin className="h-4 w-4 mr-1" />
                    {property.address || `${normalizeName(property.city)}, ${normalizeName(property.state)}`}
                  </p>
                </div>
                <div className="text-right">
                  <p className="text-lg font-bold text-primary">
                    {formatPrice(property.price, property.listingType)}
                  </p>
                </div>
              </div>

              <div className="mt-4 flex items-center space-x-6 text-sm text-gray-700">
                <div className="flex items-center bg-gray-50 px-3 py-1.5 rounded-md">
                  <Bed className="h-4 w-4 mr-2 text-primary" />
                  <span className="font-medium">
                    {Number(property.bedrooms ?? property.details?.bedrooms ?? 0) || 0} 
                    <span className="ml-1 text-gray-600">{t('property.details.bedrooms')}</span>
                  </span>
                </div>
                <div className="flex items-center bg-gray-50 px-3 py-1.5 rounded-md">
                  <Bath className="h-4 w-4 mr-2 text-primary" />
                  <span className="font-medium">
                    {Number(property.bathrooms ?? property.details?.bathrooms ?? 0) || 0} 
                    <span className="ml-1 text-gray-600">{t('property.details.bathrooms')}</span>
                  </span>
                </div>
                <div className="hidden sm:flex items-center bg-gray-50 px-3 py-1.5 rounded-md">
                  <Square className="h-4 w-4 mr-2 text-primary" />
                  <span className="font-medium">
                    {(Number(property.square_feet ?? property.squareFootage ?? property.sqft ?? property.details?.square_feet ?? 0) || 0).toLocaleString()}
                    <span className="ml-1 text-gray-600">{t('property.sqft')}</span>
                  </span>
                </div>
              </div>

              <p className="mt-3 text-gray-600 line-clamp-2">
                {property.description}
              </p>
            </div>

            <div className="mt-4 flex items-center justify-between">
              <div className="flex flex-wrap gap-2">
                <Badge variant="outline">
                  {property.type}
                </Badge>
                <Badge variant="outline">
                  {(property.listingType === 'rent') ? t('property.forRent') : t('property.forSale')}
                </Badge>
                {property.features && property.features.length > 3 && (
                  <Badge variant="outline">
                    +{property.features.length - 3} {t('property.more')}
                  </Badge>
                )}
              </div>
              
              <div className="flex justify-between items-center">
                <p className="text-sm text-gray-500 flex items-center">
                  <Calendar className="h-4 w-4 mr-1" />
                  {t('property.listed')} {formatDate(property.datePosted)}
                </p>
                <Link to={`/property/${propertySlug}`}>
                  <Button>{t('property.actions.viewDetails')}</Button>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </Card>
    );
  }

  // Grid view
  return (
    <Card className="overflow-hidden hover:shadow-lg transition-shadow duration-300">
      <Link to={`/property/${propertySlug}`}>
        <div className="relative">
          {useGallery && images && images.length > 1 ? (
            <PropertyImageGallery
              images={images}
              alt={property.title}
              className="h-48 w-full object-cover"
              showThumbnails={false}
              enableZoom={false}
              propertyId={property.id}
            />
          ) : (
            <FixedImage
              src={mainImage}
              alt={property.title}
              className="h-48 w-full object-cover"
            />
          )}
          <Badge 
            className={`absolute top-2 left-2 ${
              property.listingType === 'rent' ? 'bg-green-500' : 'bg-blue-500'
            }`}
          >
            {property.listingType === 'rent' ? t('property.listingTypes.forRent') : t('property.listingTypes.forSale')}
          </Badge>
          {user && (
            <Button
              variant="ghost"
              size="sm"
              onClick={() => toggleFavorite(property.id.toString())}
              className={`absolute top-2 right-2 ${
                isFavorite ? 'text-red-500' : 'text-gray-400'
              } hover:text-red-500`}
            >
              <Heart className={`h-5 w-5 ${isFavorite ? 'fill-current' : ''}`} />
            </Button>
          )}
        </div>
      </Link>

      <CardContent className="p-4">
        <div className="mb-2">
          <h3 className="text-lg font-semibold text-gray-900 hover:text-blue-600 line-clamp-1">
            <Link to={`/property/${propertySlug}`}>
              {property.title}
            </Link>
          </h3>
          <p className="text-xl font-bold text-blue-600">
            {formatPrice(property.price, property.listingType)}
          </p>
        </div>
        
        <p className="text-gray-600 mb-3 flex items-center text-sm">
          <MapPin className="h-4 w-4 mr-1" />
          {property.address}
        </p>
        
        <div className="flex justify-between items-center mb-3">
          <div className="flex items-center text-gray-600 text-sm">
            <Bed className="h-4 w-4 mr-1" />
            <span>{t('property.details.bedrooms', { count: property.details?.bedrooms || property.beds || 0 })}</span>
          </div>
          <div className="flex items-center text-gray-600 text-sm">
            <Bath className="h-4 w-4 mr-1" />
            <span>{t('property.details.bathrooms', { count: property.details?.bathrooms || property.baths || 0 })}</span>
          </div>
          <div className="flex items-center text-gray-600 text-sm">
            <Square className="h-4 w-4 mr-1" />
            {(property.squareFootage || property.sqft || 0).toLocaleString()}
          </div>
        </div>
        
        <div className="flex flex-wrap gap-1 mb-3">
          {property.features?.slice(0, 2).map((feature) => {
            const translated = t(`property.features.${feature.toLowerCase().replace(/\s+/g, '')}`, { defaultValue: feature });
            const displayText = typeof translated === 'string' ? translated : feature;
            return (
              <Badge key={feature} variant="secondary" className="text-xs">
                {displayText}
              </Badge>
            );
          })}

          {(property.features?.length ?? 0) > 2 && (
            <Badge variant="outline" className="text-xs">
              +{property.features.length - 2}
            </Badge>
          )}
        </div>
      </CardContent>
      
      <CardFooter className="p-4 pt-0">
        <Link to={`/property/${propertySlug}`} className="w-full">
          <Button className="w-full">{t('property.actions.viewDetails')}</Button>
        </Link>
      </CardFooter>
    </Card>
  );
};

export default PropertyCard;
