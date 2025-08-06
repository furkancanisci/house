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

interface PropertyCardProps {
  property: ExtendedProperty;
  view?: 'grid' | 'list';
}

const PropertyCard: React.FC<PropertyCardProps> = ({ property, view = 'grid' }) => {
  const { t } = useTranslation();
  const { state, toggleFavorite } = useApp();
  const { favorites, user } = state;
  // Ensure we use the slug for the property link
  const propertySlug = property.slug || property.id.toString();
  const isFavorite = favorites.includes(property.id.toString());

  const formatPrice = (price: any, listingType: string = 'sale') => {
    try {
      let priceValue = 0;
      
      // Handle different price formats
      if (price === null || price === undefined) {
        priceValue = 0;
      } else if (typeof price === 'number') {
        priceValue = price;
      } else if (typeof price === 'object' && price !== null) {
        // Handle price object with amount property
        priceValue = Number(price.amount) || 0;
      } else if (typeof price === 'string') {
        // Handle string price (remove any non-numeric characters except decimal point)
        const numericString = price.toString().replace(/[^0-9.]/g, '');
        priceValue = parseFloat(numericString) || 0;
      }
      
      const formattedPrice = Math.round(priceValue).toLocaleString();
      
      if (listingType === 'rent') {
        return `$${formattedPrice}/${t('property.perMonth')}`;
      }
      return `$${formattedPrice}`;
    } catch (error) {
      console.error('Error formatting price:', error, price);
      return listingType === 'rent' ? `$0/${t('property.perMonth')}` : '$0';
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
  };

  if (view === 'list') {
    return (
      <Card className="overflow-hidden hover:shadow-lg transition-shadow duration-300">
        <div className="md:flex">
          <div className="md:flex-shrink-0">
            <FixedImage
              className="h-48 w-full object-cover md:w-48"
              src={property.mainImage || '/placeholder-property.jpg'}
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
                    {property.address || `${property.city}, ${property.state}`}
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
          <FixedImage
            src={property.mainImage || '/placeholder-property.jpg'}
            alt={property.title}
            className="h-48 w-full object-cover"
          />
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
            <span>{t('property.details.bedrooms', { count: property.details?.bedrooms || property.details.bedrooms || property.beds || 0 })}</span>
          </div>
          <div className="flex items-center text-gray-600 text-sm">
            <Bath className="h-4 w-4 mr-1" />
            <span>{t('property.details.bathrooms', { count: property.details?.bathrooms || property.details.bathrooms || property.baths || 0 })}</span>
          </div>
          <div className="flex items-center text-gray-600 text-sm">
            <Square className="h-4 w-4 mr-1" />
            {(property.squareFootage || property.sqft || 0).toLocaleString()}
          </div>
        </div>
        
        <div className="flex flex-wrap gap-1 mb-3">
          {property.features?.slice(0, 2).map((feature) => (
            <Badge key={feature} variant="secondary" className="text-xs">
              {t(`property.features.${feature.toLowerCase().replace(/\s+/g, '')}`, feature)}
            </Badge>
          ))}

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
