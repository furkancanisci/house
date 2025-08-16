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

  // Debug: Log the property data to understand the structure (only in development)
  React.useEffect(() => {
    if (process.env.NODE_ENV === 'development') {
      console.log('PropertyCard received property:', property);
      console.log('Property bedrooms:', property.bedrooms);
      console.log('Property details:', property.details);
    }
  }, [property]);

  // Safety check: If property is null or undefined, return null
  if (!property) {
    console.error('PropertyCard: property is null or undefined');
    return null;
  }

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

  // Helper functions to safely get property values
  const getBedrooms = () => {
    return Number(property.bedrooms ?? property.details?.bedrooms ?? 0) || 0;
  };

  const getBathrooms = () => {
    return Number(property.bathrooms ?? property.details?.bathrooms ?? 0) || 0;
  };

  const getSquareFootage = () => {
    return Number(property.square_feet ?? property.squareFootage ?? property.sqft ?? property.details?.square_feet ?? 0) || 0;
  };

  const formatPrice = (price: number | string | { amount?: number } | undefined, type: string = 'sale') => {
    const numPrice = typeof price === 'object' && price !== null
      ? (price as any).amount || 0
      : Number(price) || 0;

    if (numPrice === 0) return type === 'rent' ? t('property.priceOnRequest') : t('property.priceOnRequest');

    const formattedPrice = new Intl.NumberFormat('ar-SA', {
      style: 'currency',
      currency: 'SAR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(numPrice);

    return type === 'rent' ? `${formattedPrice}/${t('property.month')}` : formattedPrice;
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
  };

  if (view === 'list') {
    return (
      <Card className="overflow-hidden hover:shadow-xl transition-all duration-300 hover:scale-[1.01] bg-white border border-gray-200 hover:border-blue-300 lg:min-h-[280px]">
        <div className="flex flex-col lg:flex-row lg:items-center lg:h-full">
          {/* Image Section */}
          <div className="relative lg:flex-shrink-0 lg:self-center lg:m-3">
            <FixedImage
              className="h-56 lg:h-48 w-full lg:w-72 object-cover rounded-t-lg lg:rounded-lg"
              src={mainImage}
              alt={property.title}
            />
            {/* Listing Type Badge */}
            <Badge
              className={`absolute top-3 left-3 ${property.listingType === 'rent' ? 'bg-green-500 hover:bg-green-600' : 'bg-blue-500 hover:bg-blue-600'
                } text-white font-medium`}
            >
              {property.listingType === 'rent' ? t('property.listingTypes.forRent') : t('property.listingTypes.forSale')}
            </Badge>

            {/* Favorite Button */}
            {user && (
              <Button
                variant="ghost"
                size="sm"
                onClick={handleFavoriteClick}
                className={`absolute top-3 right-3 ${isFavorite ? 'text-red-500 bg-white/90' : 'text-gray-600 bg-white/90'
                  } hover:text-red-500 hover:bg-white rounded-full p-2 shadow-md`}
              >
                <Heart className={`h-4 w-4 ${isFavorite ? 'fill-current' : ''}`} />
              </Button>
            )}
          </div>

          {/* Content Section */}
          <div className="flex-1 p-4 lg:p-6 lg:flex lg:items-center">
            <div className="flex flex-col h-full lg:w-full lg:justify-center">
              {/* Header */}
              <div className="flex flex-col lg:flex-row lg:justify-between lg:items-start mb-4 space-y-2 lg:space-y-0">
                <div className="flex-1 lg:pr-4">
                  <h3 className="text-lg lg:text-xl font-bold text-gray-900 hover:text-blue-600 transition-colors duration-200 line-clamp-2 mb-2">
                    <Link to={`/property/${propertySlug}`}>
                      {property.title}
                    </Link>
                  </h3>
                  <p className="text-gray-600 flex items-center text-sm">
                    <MapPin className="h-4 w-4 mr-2 text-gray-400 flex-shrink-0" />
                    <span className="line-clamp-1">
                      {property.address || `${normalizeName(property.city)}, ${normalizeName(property.state)}`}
                    </span>
                  </p>
                </div>
                <div className="text-left lg:text-right lg:ml-4 flex-shrink-0">
                  <p className="text-xl lg:text-2xl font-bold text-blue-600">
                    {formatPrice(property.price, property.listingType)}
                  </p>
                  <p className="text-xs lg:text-sm text-gray-500 mt-1">
                    {property.listingType === 'rent' ? t('property.perMonth') : t('property.totalPrice')}
                  </p>
                </div>
              </div>

              {/* Property Details */}
              <div className="flex flex-wrap gap-2 lg:gap-4 mb-4">
                <div className="flex items-center bg-blue-50 px-2 lg:px-3 py-1.5 lg:py-2 rounded-lg">
                  <Bed className="h-3 lg:h-4 w-3 lg:w-4 mr-1 lg:mr-2 text-blue-600" />
                  <span className="font-semibold text-gray-800 text-sm lg:text-base">
                    {getBedrooms()}
                  </span>
                  <span className="ml-1 text-gray-600 text-xs lg:text-sm hidden sm:inline">{t('property.details.bedrooms')}</span>
                </div>
                <div className="flex items-center bg-green-50 px-2 lg:px-3 py-1.5 lg:py-2 rounded-lg">
                  <Bath className="h-3 lg:h-4 w-3 lg:w-4 mr-1 lg:mr-2 text-green-600" />
                  <span className="font-semibold text-gray-800 text-sm lg:text-base">
                    {getBathrooms()}
                  </span>
                  <span className="ml-1 text-gray-600 text-xs lg:text-sm hidden sm:inline">{t('property.details.bathrooms')}</span>
                </div>
                <div className="flex items-center bg-purple-50 px-2 lg:px-3 py-1.5 lg:py-2 rounded-lg">
                  <Square className="h-3 lg:h-4 w-3 lg:w-4 mr-1 lg:mr-2 text-purple-600" />
                  <span className="font-semibold text-gray-800 text-sm lg:text-base">
                    {getSquareFootage().toLocaleString()}
                  </span>
                  <span className="ml-1 text-gray-600 text-xs lg:text-sm hidden sm:inline">{t('property.sqft')}</span>
                </div>
              </div>

              {/* Description */}
              <p className="text-gray-700 line-clamp-2 mb-3 lg:mb-4 leading-relaxed text-sm lg:text-base">
                {property.description}
              </p>

              {/* Features */}
              <div className="flex flex-wrap gap-1 lg:gap-2 mb-3 lg:mb-4">
                {property.features?.slice(0, 3).map((feature) => {
                  const translated = t(`property.features.${feature.toLowerCase().replace(/\s+/g, '')}`, { defaultValue: feature });
                  const displayText = typeof translated === 'string' ? translated : feature;
                  return (
                    <Badge key={feature} variant="secondary" className="text-xs bg-gray-100 text-gray-700 hover:bg-gray-200 px-2 py-1">
                      {displayText}
                    </Badge>
                  );
                })}
                {(property.features?.length ?? 0) > 3 && (
                  <Badge variant="outline" className="text-xs text-gray-500 px-2 py-1">
                    +{property.features.length - 3} {t('property.more')}
                  </Badge>
                )}
              </div>

              {/* Footer */}
              <div className="flex flex-col lg:flex-row lg:justify-between lg:items-center mt-auto pt-3 lg:pt-4 border-t border-gray-100 space-y-2 lg:space-y-0">
                <div className="flex items-center text-xs lg:text-sm text-gray-500">
                  <Calendar className="h-3 lg:h-4 w-3 lg:w-4 mr-1" />
                  <span>{t('property.listed')} {formatDate(property.created_at || property.datePosted)}</span>
                </div>
                <Link to={`/property/${propertySlug}`}>
                  <Button className="bg-blue-600 hover:bg-blue-700 text-white px-4 lg:px-6 py-2 rounded-lg transition-all duration-200 hover:shadow-md text-sm lg:text-base w-full lg:w-auto">
                    {t('property.actions.viewDetails')}
                  </Button>
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
              containerClassName="h-48"
              className="w-full h-full object-cover"
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
            className={`absolute top-2 left-2 ${property.listingType === 'rent' ? 'bg-green-500' : 'bg-blue-500'
              }`}
          >
            {property.listingType === 'rent' ? t('property.listingTypes.forRent') : t('property.listingTypes.forSale')}
          </Badge>
          {user && (
            <Button
              variant="ghost"
              size="sm"
              onClick={() => toggleFavorite(property.id.toString())}
              className={`absolute top-2 right-2 ${isFavorite ? 'text-red-500' : 'text-gray-400'
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
            <span>{getBedrooms()} {t('property.details.bedrooms')}</span>
          </div>
          <div className="flex items-center text-gray-600 text-sm">
            <Bath className="h-4 w-4 mr-1" />
            <span>{getBathrooms()} {t('property.details.bathrooms')}</span>
          </div>
          <div className="flex items-center text-gray-600 text-sm">
            <Square className="h-4 w-4 mr-1" />
            {getSquareFootage().toLocaleString()}
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
