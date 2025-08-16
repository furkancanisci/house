import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { Property } from '../types';
import { GoogleMap, LoadScript, Marker } from '@react-google-maps/api';
import { useTranslation } from 'react-i18next';
import { 
  Heart, 
  Share2, 
  Bed, 
  Bath, 
  Square, 
  MapPin, 
  Calendar, 
  Phone, 
  Mail, 
  User,
  ArrowLeft,
  CheckCircle,
  Home,
  Car,
  Zap,
  TreePine
} from 'lucide-react';
import { Button } from '../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Badge } from '../components/ui/badge';
import { Separator } from '../components/ui/separator';
import { toast } from 'sonner';
import { getProperty } from '../services/propertyService';
import FixedImage from '../components/FixedImage';
import PropertyImageGallery from '../components/PropertyImageGallery';

const PropertyDetails: React.FC = () => {
  const { t, i18n } = useTranslation();
  const { id: slug } = useParams<{ id: string }>(); 
  const navigate = useNavigate();
  const { state, toggleFavorite } = useApp();
  const { favorites, user } = state;
  const [property, setProperty] = useState<Property | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [showMap, setShowMap] = useState(false);

  const GOOGLE_MAPS_API_KEY = 'AIzaSyCO0kKndUNlmQi3B5mxy4dblg_8WYcuKuk';

  // Helper function to normalize location fields that might be objects
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

  useEffect(() => {
    const fetchProperty = async () => {
      if (!slug) return;
      
      try {
        setLoading(true);
        console.log('Fetching property with slug:', slug);
        const propertyData = await getProperty(slug);
        
        if (!propertyData) {
          throw new Error('No property data found in response');
        }

        // Log the raw API response with all its properties
        console.group('Raw API Response');
        console.log('Full response:', propertyData);
        console.log('Property ID:', propertyData.id);
        console.log('Price:', propertyData.price);
        console.log('Bedrooms:', propertyData.details?.bedrooms);
        console.log('Bathrooms:', propertyData.details?.bathrooms);
        console.log('Square Feet:', propertyData.details?.square_feet);
        console.log('Property Type:', propertyData.property_type);
        console.log('Listing Type:', propertyData.listing_type);
        console.log('All response keys:', Object.keys(propertyData));
        
        // Debug image data specifically
        console.group('Image Data Debug');
        console.log('propertyData.media:', propertyData.media);
        console.log('propertyData.images:', propertyData.images);
        console.log('propertyData.media?.main_image_url:', propertyData.media?.main_image_url);
        console.log('propertyData.media?.gallery_urls:', propertyData.media?.gallery_urls);
        console.log('propertyData.images?.main:', propertyData.images?.main);
        console.log('propertyData.images?.gallery:', propertyData.images?.gallery);
        console.groupEnd();
        
        console.groupEnd();
        
        // Transform the property data to match the expected format
        const transformedProperty: Property = {
          id: propertyData.id?.toString() || '',
          slug: propertyData.slug || `property-${propertyData.id || ''}`,
          title: propertyData.title || 'No Title',
          description: propertyData.description || '',
          address: propertyData.address || propertyData.location?.street_address || '',
          city: normalizeName(propertyData.city),
          state: normalizeName(propertyData.state),
          zip_code: propertyData.zip_code || propertyData.zipCode || '',
          country: normalizeName(propertyData.country),
          price: typeof propertyData.price === 'string' ? parseFloat(propertyData.price) : (propertyData.price || 0),
          listingType: propertyData.listing_type === 'rent' ? 'rent' : 'sale',
          propertyType: propertyData.property_type || 'apartment',
          bedrooms: propertyData.bedrooms ? parseInt(propertyData.bedrooms, 10) : 0,
          bathrooms: propertyData.bathrooms ? parseFloat(propertyData.bathrooms) : 0,
          squareFootage: propertyData.square_feet || propertyData.squareFootage || Number(propertyData.details?.square_feet) || 0,
          year_built: propertyData.year_built || propertyData.yearBuilt,
          features: propertyData.features || (Array.isArray(propertyData.amenities) ? propertyData.amenities : []),
          media: (() => {
            // Handle both the old format (images.gallery) and new format (media array)
            if (propertyData.media && Array.isArray(propertyData.media)) {
              return propertyData.media;
            }
            
            // Fallback to the old image format if media is not available
            const galleryImages = propertyData.images?.gallery?.map((img: any) => ({
              id: img.id || Math.random().toString(36).substr(2, 9),
              url: img.url || img,
              type: 'image',
              is_featured: img.is_featured || false
            })) || [];
            
            const mainImage = propertyData.images?.main || propertyData.mainImage;
            if (mainImage) {
              galleryImages.unshift({
                id: 'main',
                url: typeof mainImage === 'string' ? mainImage : mainImage.url,
                type: 'image',
                is_featured: true
              });
            }
            
            return galleryImages.length > 0 ? galleryImages : [];
          })(),
          images: (() => {
            // Extract image URLs for the PropertyImageGallery component
            const imageUrls: string[] = [];
            
            // First, try to get images from the media.gallery_urls array (new backend format)
            if (propertyData.media?.gallery_urls && Array.isArray(propertyData.media.gallery_urls)) {
              imageUrls.push(...propertyData.media.gallery_urls);
            }
            
            // Add main image URL if available
            if (propertyData.media?.main_image_url) {
              // Add main image at the beginning if not already included
              if (!imageUrls.includes(propertyData.media.main_image_url)) {
                imageUrls.unshift(propertyData.media.main_image_url);
              }
            }
            
            // Fallback to old format if new format is not available
            if (imageUrls.length === 0) {
              // Try old images.gallery format
              if (propertyData.images?.gallery && Array.isArray(propertyData.images.gallery)) {
                imageUrls.push(...propertyData.images.gallery.map((img: any) => 
                  typeof img === 'string' ? img : img.url || img
                ));
              }
              
              // Add main image from old format
              const mainImage = propertyData.images?.main || propertyData.mainImage;
              if (mainImage) {
                const mainImageUrl = typeof mainImage === 'string' ? mainImage : mainImage.url;
                if (mainImageUrl && !imageUrls.includes(mainImageUrl)) {
                  imageUrls.unshift(mainImageUrl);
                }
              }
            }
            
            return imageUrls;
          })(),
          mainImage: propertyData.images?.main || '/placeholder-property.jpg',
          yearBuilt: Number(propertyData.details?.year_built) || new Date().getFullYear(),
          coordinates: {
            lat: Number(propertyData.location?.latitude) || 0,
            lng: Number(propertyData.location?.longitude) || 0
          },
          contact: {
            name: propertyData.owner?.full_name || 'Agent',
            phone: propertyData.owner?.phone || '',
            email: propertyData.owner?.email || ''
          },
          datePosted: propertyData.created_at || new Date().toISOString(),
          // Optional fields
          availableDate: propertyData.available_from,
          petPolicy: propertyData.details?.pet_policy,
          parking: propertyData.details?.parking?.type,
          lotSize: propertyData.details?.lot_size,
          garage: propertyData.details?.parking?.type === 'garage' ? 'Yes' : 'No',
          building: propertyData.details?.building_name
        };
        
        // Log the transformed property
        console.group('Transformed Property');
        console.log('Transformed property:', transformedProperty);
        console.log('Price after transform:', transformedProperty.price);
        console.log('Bedrooms after transform:', transformedProperty.bedrooms);
        console.log('Bathrooms after transform:', transformedProperty.bathrooms);
        console.log('Square footage after transform:', transformedProperty.squareFootage);
        console.log('Images array for gallery:', transformedProperty.images);
        console.log('Media array:', transformedProperty.media);
        console.log('Main image:', transformedProperty.mainImage);
        console.groupEnd();
        
        setProperty(transformedProperty);
      } catch (err) {
        console.error('Error fetching property:', err);
        setError('Failed to load property details');
        toast.error('Failed to load property details');
      } finally {
        setLoading(false);
      }
    };

    fetchProperty();
  }, [slug, i18n.language]);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">{t('common.loading')}</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="container mx-auto p-4 text-red-500">{error}</div>
    );
  }

  if (!property) {
    return (
      <div className="container mx-auto p-4">{t('property.notFound')}</div>
    );
  }

  // Ensure property.id is treated as a string for favorites comparison
  const propertyId = String(property.id);
  const isFavorite = favorites.includes(propertyId);

  const formatPrice = (price: number | string | undefined, listingType: string) => {
    // Handle both string and number prices safely
    const priceValue = typeof price === 'string' ? parseFloat(price) || 0 : price || 0;
    if (listingType === 'rent') {
      return `$${priceValue.toLocaleString()}/month`;
    }
    return `$${priceValue.toLocaleString()}`;
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  const handleToggleFavorite = async () => {
    if (!user) {
      toast.error(t('messages.signInToSaveFavorites'));
      navigate('/auth');
      return;
    }
    
    try {
      // Ensure property.id is passed as a string
      const wasFavorited = await toggleFavorite(propertyId);
      toast.success(wasFavorited ? t('messages.addedToFavorites') : t('messages.removedFromFavorites'));
    } catch (error) {
      toast.error('Failed to update favorite');
    }
  };

  const handleShare = async () => {
    if (navigator.share) {
      try {
        await navigator.share({
          title: property.title,
          text: property.description,
          url: window.location.href,
        });
      } catch (error) {
        navigator.clipboard.writeText(window.location.href);
        toast.success(t('messages.linkCopied'));
      }
    } else {
      navigator.clipboard.writeText(window.location.href);
      toast.success(t('messages.linkCopied'));
    }
  };

  const handleContactOwner = () => {
    if (!user) {
      toast.error(t('messages.signInToContact'));
      navigate('/auth');
      return;
    }
    toast.success(t('messages.contactInfoDisplayed'));
  };



  const mapContainerStyle = {
    width: '100%',
    height: '400px'
  };

  const center = {
    lat: property.coordinates.lat,
    lng: property.coordinates.lng
  };

  const getFeatureIcon = (feature: string) => {
    const iconMap: Record<string, any> = {
      'Parking': Car,
      'Garage': Car,
      'Garden': TreePine,
      'Pool': TreePine,
      'Gym': TreePine,
      'Air Conditioning': Zap,
      'Heating': Zap,
      'Elevator': Home,
      'Balcony': Home,
      'Fireplace': Home,
    };
    return iconMap[feature] || CheckCircle;
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <Button
          variant="ghost"
          onClick={() => navigate(-1)}
          className="mb-4"
        >
          <ArrowLeft className="h-4 w-4 mr-2" />
          {t('navigation.search')}
        </Button>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div className="lg:col-span-2 space-y-6">
            {/* Image Gallery */}
            <Card className="overflow-hidden">
              <div className="relative">
                <PropertyImageGallery
                  images={property.images}
                  alt={property.title}
                  className="w-full h-96"
                  propertyId={property.id}
                />

                {/* Badges */}
                <Badge 
                  className={`absolute top-4 left-4 ${
                    property.listingType === 'rent' ? 'bg-green-500' : 'bg-blue-500'
                  }`}
                >
                  {property.listingType === 'rent' ? t('property.listingTypes.forRent') : t('property.listingTypes.forSale')}
                </Badge>

                {/* Action Buttons */}
                <div className="absolute top-4 right-4 flex space-x-2">
                  {user && (
                    <Button
                      variant="ghost"
                      size="sm"
                      onClick={handleToggleFavorite}
                      className={`${
                        isFavorite ? 'text-red-500' : 'text-white'
                      } bg-black bg-opacity-50 hover:bg-opacity-70`}
                    >
                      <Heart className={`h-5 w-5 ${isFavorite ? 'fill-current' : ''}`} />
                    </Button>
                  )}
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={handleShare}
                    className="text-white bg-black bg-opacity-50 hover:bg-opacity-70"
                  >
                    <Share2 className="h-5 w-5" />
                  </Button>
                </div>
              </div>
            </Card>

            {/* Property Overview */}
            <Card>
              <CardHeader>
                <div className="flex justify-between items-start">
                  <div>
                    <CardTitle className="text-2xl mb-2">{property.title}</CardTitle>
                    <p className="text-gray-600 flex items-center">
                      <MapPin className="h-4 w-4 mr-1" />
                      {property.address}
                    </p>
                  </div>
                  <div className="text-right">
                    <p className="text-3xl font-bold text-blue-600">
                      {formatPrice(property.price, property.listingType)}
                    </p>
                  </div>
                </div>
              </CardHeader>
              
              <CardContent>
                <div className="grid grid-cols-3 gap-6 mb-6">
                  <div className="text-center">
                    <div className="flex items-center justify-center mb-2">
                      <Bed className="h-6 w-6 text-gray-600 mr-2" />
                      <span className="text-2xl font-semibold">{property.bedrooms}</span>
                    </div>
                    <p className="text-gray-600">{t('property.details.bedrooms')}</p>
                  </div>
                  <div className="text-center">
                    <div className="flex items-center justify-center mb-2">
                      <Bath className="h-6 w-6 text-gray-600 mr-2" />
                      <span className="text-2xl font-semibold">{property.bathrooms}</span>
                    </div>
                    <p className="text-gray-600">{t('property.details.bathrooms')}</p>
                  </div>
                  <div className="text-center">
                    <div className="flex items-center justify-center mb-2">
                      <Square className="h-6 w-6 text-gray-600 mr-2" />
                      <span className="text-2xl font-semibold">{(property.squareFootage || 0).toLocaleString()}</span>
                    </div>
                    <p className="text-gray-600">{t('property.details.squareFootage')}</p>
                  </div>
                </div>

                <Separator className="my-6" />

                <div>
                  <h3 className="text-lg font-semibold mb-3">{t('property.types.description')}</h3>
                  <p className="text-gray-700 leading-relaxed">{property.description}</p>
                </div>
              </CardContent>
            </Card>

            {/* Features & Amenities */}
            <Card>
              <CardHeader>
                <CardTitle>{t('steps.features')}</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                  {(property.features || [])
                    .filter(feature => {
                      // Skip empty or null features
                      if (!feature) return false;
                      // Check if translation exists for this feature
                      const featureKey = feature.toLowerCase().replace(/\s+/g, '');
                      const translation = t(`property.features.${featureKey}`, {defaultValue: ''});
                      return translation !== '';
                    })
                    .map((feature) => {
                      const featureKey = feature.toLowerCase().replace(/\s+/g, '');
                      const Icon = getFeatureIcon(feature);
                      const translation = t(`property.features.${featureKey}`, { defaultValue: feature });
                      
                      return (
                        <div key={feature} className="flex items-center space-x-2">
                          <Icon className="h-5 w-5 text-green-600" />
                          <span className="text-gray-700">{typeof translation === 'string' ? translation : feature}</span>
                        </div>
                      );
                    })}
                </div>
              </CardContent>
            </Card>

            {/* Property Details */}
            <Card>
              <CardHeader>
                <CardTitle>{t('property.propertyDetails')}</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div className="space-y-3">
                    <div className="flex justify-between">
                      <span className="text-gray-600">{t('filters.propertyType')}</span>
                      <span className="capitalize">{t(`property.types.${property.propertyType}`)}</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-gray-600">{t('property.details.yearBuilt')}</span>
                      <span>{property.yearBuilt}</span>
                    </div>
                    {property.lotSize && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.lotSize')}</span>
                        <span>{property.lotSize}</span>
                      </div>
                    )}
                    {property.parking && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.parking')}</span>
                        <span>{property.parking}</span>
                      </div>
                    )}
                  </div>
                  <div className="space-y-3">
                    {property.availableDate && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.availableDate')}</span>
                        <span>{formatDate(property.availableDate)}</span>
                      </div>
                    )}
                    {property.petPolicy && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.petPolicy')}</span>
                        <span>{property.petPolicy}</span>
                      </div>
                    )}
                    {property.utilities && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.utilities')}</span>
                        <span>{property.utilities}</span>
                      </div>
                    )}
                    <div className="flex justify-between">
                      <span className="text-gray-600">{t('property.listed')}</span>
                      <span>{formatDate(property.datePosted)}</span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Map */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center">
                  <MapPin className="h-5 w-5 mr-2" />
                  {t('property.location')}
                </CardTitle>
              </CardHeader>
              <CardContent>
                <LoadScript googleMapsApiKey={GOOGLE_MAPS_API_KEY}>
                  <GoogleMap
                    mapContainerStyle={mapContainerStyle}
                    center={center}
                    zoom={15}
                  >
                    <Marker position={center} />
                  </GoogleMap>
                </LoadScript>
              </CardContent>
            </Card>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Contact Card */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center">
                  <User className="h-5 w-5 mr-2" />
                  {t('property.actions.contactOwner')}
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {/* ... existing contact code ... */}
                
                <Button 
                  onClick={handleContactOwner}
                  className="w-full"
                  size="lg"
                >
                  {t('property.actions.contactOwner')}
                </Button>
                
                {!user && (
                  <p className="text-sm text-gray-500 text-center">
                    {t('messages.signInToContact')}
                  </p>
                )}
              </CardContent>
            </Card>

            {/* Quick Actions */}
            <Card>
              <CardHeader>
                <CardTitle>{t('property.quickActions')}</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <Button 
                  variant="outline" 
                  onClick={handleToggleFavorite}
                  className="w-full"
                  disabled={!user}
                >
                  <Heart className={`h-4 w-4 mr-2 ${isFavorite ? 'fill-current text-red-500' : ''}`} />
                  {isFavorite ? t('property.actions.removeFromFavorites') : t('property.actions.addToFavorites')}
                </Button>
                
                <Button 
                  variant="outline" 
                  onClick={handleShare}
                  className="w-full"
                >
                  <Share2 className="h-4 w-4 mr-2" />
                  {t('property.actions.shareProperty')}
                </Button>
                
                <Button 
                  variant="outline" 
                  onClick={() => window.print()}
                  className="w-full"
                >
                  {t('property.printDetails')}
                </Button>
              </CardContent>
            </Card>

            {/* Property Summary */}
            <Card>
              <CardHeader>
                <CardTitle>{t('property.summary')}</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="flex justify-between items-center">
                  <span className="text-gray-600">{t('filters.listingType')}</span>
                  <Badge variant={property.listingType === 'rent' ? 'default' : 'secondary'}>
                    {property.listingType === 'rent' ? t('property.listingTypes.forRent') : t('property.listingTypes.forSale')}
                  </Badge>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">{t('filters.propertyType')}</span>
                  <span className="capitalize">{t(`property.types.${property.propertyType}`)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">{t('property.details.bedrooms')}</span>
                  <span>{property.bedrooms}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">{t('property.details.bathrooms')}</span>
                  <span>{property.bathrooms}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">{t('property.details.squareFootage')}</span>
                  <span>{(property.squareFootage || 0).toLocaleString()}</span>
                </div>
                <Separator />
                <div className="flex justify-between items-center">
                  <span className="text-gray-600 font-semibold">{t('forms.price')}</span>
                  <span className="text-xl font-bold text-blue-600">
                    {formatPrice(property.price, property.listingType)}
                  </span>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PropertyDetails;
