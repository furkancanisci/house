import React, { useState } from 'react';
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
  DollarSign,
  ArrowRight,
  Play
} from 'lucide-react';
import { Button } from './ui/button';
import { Card, CardContent, CardFooter } from './ui/card';
import { Badge } from './ui/badge';
import { useTranslation } from 'react-i18next';
import i18n from '../i18n';
import FixedImage from './FixedImage';
import PropertyImageGallery from './PropertyImageGallery';
import { processPropertyImages } from '../lib/imageUtils';
import { notification } from '../services/notificationService';

interface PropertyCardProps {
  property: ExtendedProperty;
  view?: 'grid' | 'list';
  useGallery?: boolean; // Whether to use gallery for multiple images
}

const PropertyCard: React.FC<PropertyCardProps> = ({ property, view = 'grid', useGallery = false }) => {
  const { state, toggleFavorite } = useApp();
  const { t, i18n } = useTranslation();
  const isFavorite = state.favorites.includes(property.id.toString());
  const user = state.user;
  const [videoError, setVideoError] = useState(false);
  const [videoLoading, setVideoLoading] = useState(true);

  // Debug: Log the property data to understand the structure (only in development)
  React.useEffect(() => {
    if (process.env.NODE_ENV === 'development') {
      // Property data logging removed
    }
  }, [property]);

  // Safety check: If property is null or undefined, return null
  if (!property) {

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

  // Helper to get feature name from either string or object
  const getFeatureName = (feature: any): string => {
    // Handle null or undefined
    if (feature === null || feature === undefined) {
      return '';
    }

    // Handle string features
    if (typeof feature === 'string') {
      return feature;
    }

    // Handle object features
    if (typeof feature === 'object' && feature !== null) {
      try {
        // Try to get the name based on current language
        const locale = i18n.language;
        let name = '';

        if (locale === 'ar' && feature.name_ar) {
          name = feature.name_ar;
        } else if (locale === 'ku' && feature.name_ku) {
          name = feature.name_ku;
        } else if (feature.name_en) {
          name = feature.name_en;
        } else if (feature.name_ar) {
          name = feature.name_ar;
        } else if (feature.name) {
          name = feature.name;
        }

        // Ensure we return a string
        return typeof name === 'string' ? name : String(name || '');
      } catch (error) {
  
        return '';
      }
    }

    // Fallback: convert to string, but avoid [object Object]
    const stringValue = String(feature);
    return stringValue === '[object Object]' ? '' : stringValue;
  };

  const handleFavoriteClick = async (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    
    if (!user) {
      notification.info('Please login to save favorites');
      return;
    }
    
    try {
      const result = await toggleFavorite(property.id);
      if (isFavorite) {
        notification.success('Removed from favorites');
      } else {
        notification.success('Added to favorites', {
          action: {
            label: 'View Favorites',
            onClick: () => window.location.href = '/favorites'
          }
        });
      }
    } catch (error) {

      notification.error('Failed to update favorite. Please try again.');
    }
  };

  // Get the first video if available
  const getMainVideo = () => {
    if (property.videos && Array.isArray(property.videos) && property.videos.length > 0) {
      return property.videos[0];
    }
    return null;
  };

  // Always process property images to get the correct main image and gallery
  const processedImages = processPropertyImages(property, property.propertyType);
  const images = processedImages.images || [];
  const mainImage = processedImages.mainImage || '/images/placeholder-property.svg';
  const mainVideo = getMainVideo();

  // Enhanced debugging for property images
  console.log('🎯 PropertyCard - Enhanced debugging for property', property.id, ':', {
    // Raw property data
    rawPropertyData: {
      id: property.id,
      title: property.title,
      hasMedia: !!property.media,
      mediaLength: property.media?.length || 0,
      mediaItems: property.media?.map((item: any) => ({
        id: item.id,
        collection: item.collection_name,
        filename: item.file_name || item.filename,
        mime_type: item.mime_type,
        url: item.url,
        original_url: item.original_url
      })) || [],
      hasImages: !!property.images,
      imagesType: typeof property.images,
      hasMainImage: !!property.mainImage,
      hasMainImageUrl: !!property.main_image_url
    },
    // Processed results
    processedResults: {
      mainImage,
      imagesCount: images.length,
      hasMainVideo: !!mainVideo,
      hasRealImages: mainImage !== '/images/placeholder-property.svg' && 
                     !mainImage.includes('/images/properties/') && 
                     !mainImage.includes('placeholder')
    }
  });

  // Debug: Check if property.media exists and has items
  if (property.media && Array.isArray(property.media)) {
    console.log('🔍 PropertyCard - property.media exists with', property.media.length, 'items');
    property.media.forEach((item: any, index: number) => {
      console.log(`📷 PropertyCard - Media item ${index}:`, {
        id: item.id,
        collection_name: item.collection_name,
        file_name: item.file_name,
        mime_type: item.mime_type,
        original_url: item.original_url
      });
    });
  } else {
    console.log('❌ PropertyCard - property.media is missing or not an array:', property.media);
  }

  console.log('🎯 PropertyCard - Processed images for property', property.id, ':', {
    mainImage,
    imagesCount: images.length,
    hasMainVideo: !!mainVideo,
    propertyTitle: property.title,
    hasRealImages: mainImage !== '/images/placeholder-property.svg'
  });

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
    
    if (numPrice === 0) return t('property.priceOnRequest');
    
    // Format the price based on the current language
    const formattedPrice = new Intl.NumberFormat(i18n.language === 'ar' ? 'ar-SA' : 'en-US', {
      style: 'currency',
      currency: 'SAR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(numPrice);
    
    // Add rental period if needed
    if (type === 'rent') {
      return `${formattedPrice}/${t('property.month')}`;
    }
    
    return formattedPrice;
  };

  const formatDate = (dateString?: string) => {
    if (!dateString) return '';
    try {
      return new Date(dateString).toLocaleDateString(i18n.language);
    } catch (e) {
      return '';
    }
  };

  // Video component for property card
  const VideoPlayer = ({ video, className }: { video: any, className: string }) => {
    return (
      <div className={`relative ${className}`}>
        <video
          className="w-full h-full object-cover"
          muted
          loop
          playsInline
          onLoadStart={() => setVideoLoading(true)}
          onLoadedData={() => setVideoLoading(false)}
          onError={() => {
            setVideoError(true);
            setVideoLoading(false);
          }}
          onMouseEnter={(e) => {
            const video = e.target as HTMLVideoElement;
            video.play().catch(() => {});
          }}
          onMouseLeave={(e) => {
            const video = e.target as HTMLVideoElement;
            video.pause();
            video.currentTime = 0;
          }}
        >
          <source src={video.url} type={video.mime_type || 'video/mp4'} />
        </video>
        
        {/* Play button overlay */}
        <div className="absolute inset-0 flex items-center justify-center bg-black/20 opacity-100 hover:opacity-0 transition-opacity duration-200">
          <div className="bg-white/90 rounded-full p-3 shadow-lg">
            <Play className="h-6 w-6 text-gray-800 fill-current" />
          </div>
        </div>
        
        {/* Loading state */}
        {videoLoading && (
          <div className="absolute inset-0 flex items-center justify-center bg-gray-100">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-[#067977]"></div>
          </div>
        )}
      </div>
    );
  };

  if (view === 'list') {
    return (
      <Card className="overflow-hidden hover:shadow-lg transition-all duration-300 hover:scale-[1.01] bg-white border border-gray-200 hover:border-[#067977] lg:min-h-[200px] rounded-lg">
        <div className="flex flex-col lg:flex-row lg:items-center lg:h-full">
          {/* Image/Video Section */}
          <div className="relative lg:flex-shrink-0 lg:self-center lg:m-2">
            {mainImage ? (
              <FixedImage
                className="h-32 sm:h-40 lg:h-36 w-full lg:w-48 object-cover rounded-t-lg lg:rounded-lg shadow-sm"
                src={mainImage}
                alt={property.title}
              />
            ) : mainVideo && !videoError ? (
              <VideoPlayer 
                video={mainVideo}
                className="h-32 sm:h-40 lg:h-36 w-full lg:w-48 rounded-t-lg lg:rounded-lg shadow-sm"
              />
            ) : (
              <FixedImage
                className="h-32 sm:h-40 lg:h-36 w-full lg:w-48 object-cover rounded-t-lg lg:rounded-lg shadow-sm"
                src="/placeholder-property.jpg"
                alt={property.title}
              />
            )}
            {/* Listing Type Badge */}
            <Badge
              className={`absolute top-1.5 left-1.5 ${property.listingType === 'rent' ? 'bg-gradient-to-r from-green-500 to-green-600' : 'bg-gradient-to-r from-[#067977] to-[#067977]/80'
                } text-white font-medium shadow-lg border-0 px-2 py-1 rounded-full text-xs`}
            >
              {property.listingType === 'rent' ? t('property.listingTypes.forRent') : t('property.listingTypes.forSale')}
            </Badge>

            {/* Favorite Button - Always visible for better UX */}
            <Button
              variant="ghost"
              size="sm"
              onClick={handleFavoriteClick}
              className={`absolute top-1.5 right-1.5 ${isFavorite ? 'text-red-500 bg-white' : 'text-gray-600 bg-white/90'
                } hover:text-red-500 hover:bg-white rounded-full p-2 shadow-lg backdrop-blur-sm border border-gray-200 transition-all duration-200 hover:scale-110 hover:shadow-xl`}
              aria-label={isFavorite ? 'Remove from favorites' : 'Add to favorites'}
            >
              <Heart className={`h-4 w-4 ${isFavorite ? 'fill-current' : ''} transition-colors duration-200`} />
            </Button>
          </div>

          {/* Content Section */}
          <div className="flex-1 p-2 sm:p-3 lg:p-4 lg:flex lg:items-center">
            <div className="flex flex-col h-full lg:w-full lg:justify-center">
              {/* Header */}
              <div className="flex flex-col lg:flex-row lg:justify-between lg:items-start mb-2 sm:mb-3 space-y-1 lg:space-y-0">
                <div className="flex-1 lg:pr-3">
                  <h3 className="text-base lg:text-lg font-bold text-gray-900 hover:text-[#067977] transition-colors duration-200 line-clamp-2 mb-1">
                    <Link to={`/property/${propertySlug}`}>
                      {property.title}
                    </Link>
                  </h3>
                  <p className="text-gray-600 flex items-center text-xs sm:text-sm">
                    <MapPin className="h-3 w-3 mr-1 text-gray-400 flex-shrink-0" />
                    <span className="line-clamp-1">
                      {property.address || `${normalizeName(property.city)}, ${normalizeName(property.state)}`}
                    </span>
                  </p>
                </div>
                <div className="text-left lg:text-right lg:ml-3 flex-shrink-0">
                  <p className="text-lg lg:text-xl font-bold text-[#067977]">
                    {formatPrice(property.price, property.listingType)}
                  </p>
                  <p className="text-xs text-gray-500 mt-0.5">
                    {property.priceType ? (
                      typeof property.priceType === 'object' ? (
                        i18n.language === 'ar' ? property.priceType.name_ar :
                        i18n.language === 'ku' ? property.priceType.name_ku :
                        property.priceType.name_en
                      ) : t(`property.priceTypes.${property.priceType}`)
                    ) : (property.listingType === 'rent' ? t('property.perMonth') : t('property.totalPrice'))}
                  </p>
                </div>
              </div>

              {/* Property Details */}
              <div className="flex flex-wrap gap-2 lg:gap-4 mb-4">
                <div className="flex items-center bg-[#067977]/10 px-2 lg:px-3 py-1.5 lg:py-2 rounded-lg">
          <Bed className="h-3 lg:h-4 w-3 lg:w-4 mr-1 lg:mr-2 text-[#067977]" />
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
                {Array.isArray(property.features) && property.features.slice(0, 3).map((feature, index) => {
                  const featureName = getFeatureName(feature);
                  // Ensure featureName is a valid string before processing
                  const safeFeatureName = typeof featureName === 'string' ? featureName : '';

                  if (!safeFeatureName) return null;

                  const translated = t(`property.features.${safeFeatureName.toLowerCase().replace(/\s+/g, '')}`, { defaultValue: safeFeatureName });
                  // Ensure displayText is always a string
                  const displayText = typeof translated === 'string' ? translated :
                                    typeof translated === 'object' && translated !== null ? safeFeatureName :
                                    String(translated || safeFeatureName);

                  return (
                    <Badge key={index} variant="secondary" className="text-xs bg-gray-100 text-gray-700 hover:bg-gray-200 px-2 py-1">
                      {displayText}
                    </Badge>
                  );
                }).filter(Boolean)}
                {Array.isArray(property.features) && property.features.length > 3 && (
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
                  <Button className="bg-[#067977] hover:bg-[#067977]/90 text-white px-4 lg:px-6 py-2 rounded-lg transition-all duration-200 hover:shadow-md text-sm lg:text-base w-full lg:w-auto">
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
    <Card className="overflow-hidden hover:shadow-lg transition-all duration-300 hover:scale-[1.01] bg-white border border-gray-200 hover:border-[#067977] rounded-lg">
      <Link to={`/property/${propertySlug}`}>
        <div className="relative">
          {mainImage ? (
            useGallery && images && images.length > 1 ? (
              <PropertyImageGallery
                images={images}
                alt={property.title}
                containerClassName="h-32 sm:h-36"
                className="w-full h-full object-cover"
                showThumbnails={false}
                enableZoom={false}
              />
            ) : (
              <FixedImage
                src={mainImage}
                alt={property.title}
                className="h-32 sm:h-36 w-full object-cover"
              />
            )
          ) : mainVideo && !videoError ? (
            <VideoPlayer 
              video={mainVideo}
              className="h-32 sm:h-36 w-full"
            />
          ) : (
            <FixedImage
              src="/placeholder-property.jpg"
              alt={property.title}
              className="h-32 sm:h-36 w-full object-cover"
            />
          )}
          <Badge
            className={`absolute top-1.5 left-1.5 ${property.listingType === 'rent' ? 'bg-gradient-to-r from-green-500 to-green-600' : 'bg-gradient-to-r from-[#067977] to-[#067977]/80'
              } text-white font-medium shadow-lg border-0 px-2 py-1 rounded-full text-xs`}
          >
            {property.listingType === 'rent' ? t('property.listingTypes.forRent') : t('property.listingTypes.forSale')}
          </Badge>
          {user && (
            <Button
              variant="ghost"
              size="sm"
              onClick={handleFavoriteClick}
              className={`absolute top-1.5 right-1.5 ${isFavorite ? 'text-red-500 bg-white/95' : 'text-gray-600 bg-white/95'
                } hover:text-red-500 hover:bg-white rounded-full p-1.5 shadow-lg backdrop-blur-sm border border-white/20 transition-all duration-200 hover:scale-110`}
            >
              <Heart className={`h-3 w-3 ${isFavorite ? 'fill-current' : ''}`} />
            </Button>
          )}
        </div>
      </Link>

      <CardContent className="p-2 sm:p-3">
        <div className="mb-2">
          <h3 className="text-sm sm:text-base font-bold text-gray-900 hover:text-[#067977] line-clamp-2 mb-1 leading-tight">
            <Link to={`/property/${propertySlug}`}>
              {property.title}
            </Link>
          </h3>
          <p className="text-base sm:text-lg font-bold text-[#067977]">
            {formatPrice(property.price, property.listingType)}
          </p>
          <p className="text-xs text-gray-500 mt-0.5">
            {property.priceType ? t(`property.priceTypes.${property.priceType}`) : (property.listingType === 'rent' ? t('property.perMonth') : t('property.totalPrice'))}
          </p>
        </div>

        <p className="text-gray-600 mb-2 flex items-center text-xs sm:text-sm">
          <MapPin className="h-3 w-3 mr-1 text-gray-400 flex-shrink-0" />
          <span className="line-clamp-1">{property.address || `${normalizeName(property.city)}, ${normalizeName(property.state)}`}</span>
        </p>

        <div className="grid grid-cols-3 gap-1 mb-2">
          <div className="flex flex-col items-center bg-[#067977]/10 px-1.5 py-1.5 rounded-lg">
            <Bed className="h-3 w-3 text-[#067977] mb-0.5" />
            <span className="font-semibold text-gray-800 text-xs">{getBedrooms()}</span>
            <span className="text-xs text-gray-600 hidden sm:block">{t('property.details.bedrooms')}</span>
          </div>
          <div className="flex flex-col items-center bg-green-50 px-1.5 py-1.5 rounded-lg">
            <Bath className="h-3 w-3 text-green-600 mb-0.5" />
            <span className="font-semibold text-gray-800 text-xs">{getBathrooms()}</span>
            <span className="text-xs text-gray-600 hidden sm:block">{t('property.details.bathrooms')}</span>
          </div>
          <div className="flex flex-col items-center bg-purple-50 px-1.5 py-1.5 rounded-lg">
            <Square className="h-3 w-3 text-purple-600 mb-0.5" />
            <span className="font-semibold text-gray-800 text-xs">{getSquareFootage().toLocaleString()}</span>
            <span className="text-xs text-gray-600 hidden sm:block">{t('property.sqft')}</span>
          </div>
        </div>

        <div className="flex flex-wrap gap-1 mb-2">
          {Array.isArray(property.features) && property.features.slice(0, 2).map((feature, index) => {
            const featureName = getFeatureName(feature);
            // Ensure featureName is a valid string before processing
            const safeFeatureName = typeof featureName === 'string' ? featureName : '';

            if (!safeFeatureName) return null;

            const translated = t(`property.features.${safeFeatureName.toLowerCase().replace(/\s+/g, '')}`, { defaultValue: safeFeatureName });
            // Ensure displayText is always a string
            const displayText = typeof translated === 'string' ? translated :
                              typeof translated === 'object' && translated !== null ? safeFeatureName :
                              String(translated || safeFeatureName);

            return (
              <Badge key={index} variant="secondary" className="text-xs bg-gray-100 text-gray-700 hover:bg-gray-200 px-1.5 py-0.5">
                {displayText}
              </Badge>
            );
          }).filter(Boolean)}

          {(property.features?.length ?? 0) > 2 && (
            <Badge variant="outline" className="text-xs text-gray-500 px-1.5 py-0.5">
              +{property.features.length - 2} {t('property.more')}
            </Badge>
          )}
        </div>
      </CardContent>

      <CardFooter className="p-2 sm:p-3 pt-0">
        <Link to={`/property/${propertySlug}`} className="w-full">
          <Button className="w-full bg-[#067977] hover:bg-[#067977]/90 text-white rounded-lg transition-all duration-200 hover:shadow-md font-medium text-xs sm:text-sm py-2">
            {t('property.actions.viewDetails')}
          </Button>
        </Link>
      </CardFooter>
    </Card>
  );
};

export default React.memo(PropertyCard);