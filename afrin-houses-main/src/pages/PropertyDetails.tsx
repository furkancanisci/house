import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { Property } from '../types';
import { MapContainer, TileLayer, Marker } from 'react-leaflet';
import { useTranslation } from 'react-i18next';
import { useLeafletMap, DEFAULT_CENTER, OSM_TILE_LAYER, createPropertyIcon } from '../context/LeafletMapProvider';
import { useAuthCheck } from '../hooks/useAuthCheck';
import AuthModal from '../components/AuthModal';
import EmailActivationModal from '../components/EmailActivationModal';
import L from 'leaflet';
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
  TreePine,
  File
} from 'lucide-react';
import { Button } from '../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Badge } from '../components/ui/badge';
import { Separator } from '../components/ui/separator';
import { notification, notificationMessages } from '../services/notificationService';
import { getProperty } from '../services/propertyService';
import { propertyDocumentTypeService, PropertyDocumentType } from '../services/propertyDocumentTypeService';
import FixedImage from '../components/FixedImage';
import PropertyImageGallery from '../components/PropertyImageGallery';
import { processPropertyImages } from '../lib/imageUtils';
import FeaturesAndUtilities from '../components/FeaturesAndUtilities';
import { FeatureService } from '../services/featureService';
import { UtilityService } from '../services/utilityService';
import { Feature, Utility } from '../types';

const PropertyDetails: React.FC = () => {
  const { t, i18n } = useTranslation();
  const { id: slug } = useParams<{ id: string }>(); 
  const navigate = useNavigate();
  const { state, toggleFavorite } = useApp();
  const { favorites, user } = state;
  const { isAuthenticated, showAuthModal, openAuthModal, closeAuthModal, requireAuth, showActivationModal, closeActivationModal, requireVerifiedEmail } = useAuthCheck();
  const [property, setProperty] = useState<Property | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [documentType, setDocumentType] = useState<PropertyDocumentType | null>(null);
  const [loadingDocumentType, setLoadingDocumentType] = useState(false);
  const { isLoaded: isMapLoaded, loadError } = useLeafletMap();
  const [features, setFeatures] = useState<Feature[]>([]);
  const [utilities, setUtilities] = useState<Utility[]>([]);
  const [loadingFeatures, setLoadingFeatures] = useState(false);
  const [loadingUtilities, setLoadingUtilities] = useState(false);
  const [minLoadingTime, setMinLoadingTime] = useState(true);

  const [showMap, setShowMap] = useState(false);

  // Helper function to normalize location fields that might be objects
  const normalizeName = (val: any): string => {
    if (!val) return '';
    if (typeof val === 'string') return val;
    if (typeof val === 'object') {
      const currentLang = i18n.language;
      const ar = (val as any).name_ar ?? (val as any).ar ?? (val as any).name;
      const en = (val as any).name_en ?? (val as any).en;
      const ku = (val as any).name_ku ?? (val as any).ku;
      
      // Priority order: current language > English > Arabic > Kurdish > any available
      if (currentLang === 'ar' && ar) return ar;
      if (currentLang === 'en' && en) return en;
      if (currentLang === 'ku' && ku) return ku;
      
      // Fallback priority: English > Arabic > Kurdish > any available
      return en || ar || ku || (val as any).name || '';
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
        console.log('Document Type ID:', propertyData.document_type_id);
        console.log('Document Type Object:', propertyData.document_type);
        console.log('All response keys:', Object.keys(propertyData));
        
        // Debug image data specifically
        console.group('Image Data Debug');
        console.log('propertyData.media:', propertyData.media);
        console.log('propertyData.images:', propertyData.images);
        console.log('propertyData.images?.main (raw from API):', propertyData.images?.main);
        console.log('propertyData.images?.gallery (raw from API):', propertyData.images?.gallery);
        console.log('propertyData.mainImage (raw from API):', propertyData.mainImage);
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
          price: typeof propertyData.price === 'string' ? parseFloat(propertyData.price) : (propertyData.price || 0),
          listingType: propertyData.listing_type === 'rent' ? 'rent' : 'sale',
          propertyType: propertyData.property_type || 'apartment',
          bedrooms: propertyData.bedrooms ? parseInt(propertyData.bedrooms, 10) : 0,
          bathrooms: propertyData.bathrooms ? parseFloat(propertyData.bathrooms) : 0,
          squareFootage: propertyData.square_feet || propertyData.squareFootage || Number(propertyData.details?.square_feet) || 0,
          year_built: propertyData.year_built || propertyData.yearBuilt,
          // Phase 1 Enhancement Fields
          floor_number: propertyData.floor_number,
          total_floors: propertyData.total_floors,
          balcony_count: propertyData.balcony_count,
          orientation: propertyData.orientation,
          view_type: propertyData.view_type,
          // Phase 2 Advanced Enhancement Fields
          building_age: propertyData.building_age,
          building_type: propertyData.building_type,
          floor_type: propertyData.floor_type,
          window_type: propertyData.window_type,
          maintenance_fee: propertyData.maintenance_fee,
          deposit_amount: propertyData.deposit_amount,
          annual_tax: propertyData.annual_tax,
          features: (() => {
            // Handle features that come as objects with id, name_ar, name_en, etc.
            if (Array.isArray(propertyData.features)) {
              return propertyData.features.map((feature: any) => {
                if (typeof feature === 'object' && feature.id) {
                  return feature.id; // Extract just the ID for matching
                }
                return feature;
              });
            }
            // Fallback to amenities if features not available
            if (Array.isArray(propertyData.amenities)) {
              return propertyData.amenities.map((amenity: any) => {
                if (typeof amenity === 'object' && amenity.id) {
                  return amenity.id;
                }
                return amenity;
              });
            }
            return [];
          })(),
          utilities: (() => {
            // Handle utilities that come as objects with id, name_ar, name_en, etc.
            if (Array.isArray(propertyData.utilities)) {
              return propertyData.utilities.map((utility: any) => {
                if (typeof utility === 'object' && utility.id) {
                  return utility.id; // Extract just the ID for matching
                }
                return utility;
              });
            }
            return [];
          })(),
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
          // Process images once for both images and mainImage
          ...(() => {
            const processedImages = processPropertyImages(propertyData);
            
            console.log('Raw image data from API:', {
              images: propertyData.images,
              mainImage: propertyData.mainImage,
              media: propertyData.media
            });

            console.log('Processed image data:', {
              mainImage: processedImages.mainImage,
              images: processedImages.images,
              imageCount: processedImages.images.length
            });
            
            return {
              images: processedImages.images,
              mainImage: processedImages.mainImage
            };
          })(),
          yearBuilt: Number(propertyData.details?.year_built) || new Date().getFullYear(),
          coordinates: {
            lat: Number(propertyData.location.coordinates?.latitude) || 0,
            lng: Number(propertyData.location.coordinates?.longitude) || 0
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
          building: propertyData.details?.building_name,
          // Document type information
          document_type_id: propertyData.document_type_id,
          documentType: propertyData.document_type
        };
        
        // Log the transformed property

        console.groupEnd();
        
        setProperty(transformedProperty);
      } catch (err: any) {
        console.error('Error fetching property:', err);
        
        // Handle specific error types
        if (err.response?.status === 404) {
          setError('Property not found. This property may have been removed or the URL is incorrect.');
          notification.error('Property not found');
        } else if (err.response?.status >= 500) {
          setError('Server error. Please try again later.');
          notification.error('Server error occurred');
        } else {
          setError('Failed to load property details. Please check your connection and try again.');
          notification.error('Failed to load property details');
        }
      } finally {
        setLoading(false);
      }
    };

    fetchProperty();
  }, [slug, i18n.language, t]);

  // Fetch document type if property has document_type_id but no document type object
  useEffect(() => {
    const fetchDocumentType = async () => {
      console.log('fetchDocumentType called with:', {
        property_document_type_id: property?.document_type_id,
        property_documentType: property?.documentType,
        currentLanguage: i18n.language
      });
      
      if (property?.document_type_id && !property?.documentType) {
        setLoadingDocumentType(true);
        try {
          console.log('Fetching document type with ID:', property.document_type_id);
          const docType = await propertyDocumentTypeService.getPropertyDocumentTypeById(
            Number(property.document_type_id),
            { lang: i18n.language }
          );
          console.log('Fetched document type:', docType);
          if (docType) {
            setDocumentType(docType);
          }
        } catch (error) {
          console.error('Error fetching document type:', error);
        } finally {
          setLoadingDocumentType(false);
        }
      } else if (property?.documentType) {
        // If document type is already included in property data
        console.log('Using document type from property data:', property.documentType);
        setDocumentType(property.documentType);
      } else if (property?.document_type_id) {
        // If we have a document_type_id but no documentType, still try to fetch
        console.log('Property has document_type_id but no documentType object, forcing fetch');
        setLoadingDocumentType(true);
        try {
          const docType = await propertyDocumentTypeService.getPropertyDocumentTypeById(
            Number(property.document_type_id),
            { lang: i18n.language }
          );
          console.log('Force fetched document type:', docType);
          if (docType) {
            setDocumentType(docType);
          }
        } catch (error) {
          console.error('Error force fetching document type:', error);
        } finally {
          setLoadingDocumentType(false);
        }
      }
    };

    if (property) {
      fetchDocumentType();
    }
  }, [property, i18n.language]);

  // Minimum loading time effect
  useEffect(() => {
    const timer = setTimeout(() => {
      setMinLoadingTime(false);
    }, 800); // Show loading for at least 800ms

    return () => clearTimeout(timer);
  }, []);

  // Fetch features and utilities
  useEffect(() => {
    const fetchFeaturesAndUtilities = async () => {
      try {
        setLoadingFeatures(true);
        setLoadingUtilities(true);
        
        const [featuresData, utilitiesData] = await Promise.all([
          FeatureService.getFeatures(i18n.language),
          UtilityService.getUtilities(i18n.language)
        ]);
        
        setFeatures(featuresData);
        setUtilities(utilitiesData);
      } catch (error) {
        console.error('Error fetching features and utilities:', error);
      } finally {
        setLoadingFeatures(false);
        setLoadingUtilities(false);
      }
    };

    fetchFeaturesAndUtilities();
  }, [i18n.language]);

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
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center max-w-md mx-auto p-6">
          <div className="text-red-500 text-6xl mb-4">⚠️</div>
          <h1 className="text-2xl font-bold text-gray-800 mb-4">
            {error.includes('not found') ? 'Property Not Found' : 'Error Loading Property'}
          </h1>
          <p className="text-gray-600 mb-6">{error}</p>
          <div className="space-y-3">
            <button
              onClick={() => window.history.back()}
              className="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
            >
              Go Back
            </button>
            <button
              onClick={() => window.location.href = '/'}
              className="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors"
            >
              Go to Home
            </button>
          </div>
        </div>
      </div>
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

  const formatPrice = (price: any, listingType: string) => {
    try {
      // If price is null/undefined, return price on request
      if (price === null || price === undefined || price === '') {
        return t('property.priceOnRequest');
      }
      
      // Handle different price formats
      let numPrice = 0;
      
      // Handle object with amount property
      if (typeof price === 'object' && price !== null) {
        // Check for common price object structures
        if (typeof price.amount !== 'undefined') {
          numPrice = Number(price.amount) || 0;
        } else if (typeof price.price !== 'undefined') {
          numPrice = Number(price.price) || 0;
        } else if (typeof price.formatted !== 'undefined') {
          // If there's already a formatted price, return it directly
          return price.formatted;
        } else {
          // Try to get the first numeric value from the object
          const numericValue = Object.values(price).find((val: any) => {
            const num = Number(val);
            return !isNaN(num) && isFinite(num);
          });
          numPrice = numericValue ? Number(numericValue) : 0;
        }
      } 
      // Handle string price (could be number string or JSON string)
      else if (typeof price === 'string') {
        try {
          // Try to parse as JSON first
          const parsed = JSON.parse(price);
          if (typeof parsed === 'object' && parsed !== null) {
            return formatPrice(parsed, listingType); // Recursively handle the parsed object
          }
          numPrice = Number(price) || 0;
        } catch (e) {
          // If not JSON, try to parse as number
          numPrice = isNaN(Number(price)) ? 0 : Number(price);
        }
      } 
      // Handle number directly
      else if (typeof price === 'number') {
        numPrice = price;
      }
      
      // If we couldn't determine a valid price
      if (numPrice === 0 || isNaN(numPrice) || !isFinite(numPrice)) {
        return t('property.priceOnRequest');
      }
      
      // Format the price with currency
      const formattedPrice = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      }).format(numPrice);
      
      // Add /month for rent listings
      return listingType === 'rent' ? `${formattedPrice}/${t('property.month')}` : formattedPrice;
    } catch (error) {
      console.error('Error formatting price:', error, price);
      return t('property.priceOnRequest');
    }
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
      notification.error(t('messages.signInToSaveFavorites'));
      navigate('/auth');
      return;
    }
    
    try {
      // Ensure property.id is passed as a string
      const wasFavorited = await toggleFavorite(propertyId);
      notification.success(wasFavorited ? t('messages.addedToFavorites') : t('messages.removedFromFavorites'));
    } catch (error) {
      notification.error('Failed to update favorite');
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
        notification.success(t('messages.linkCopied'));
      }
    } else {
      navigator.clipboard.writeText(window.location.href);
      notification.success(t('messages.linkCopied'));
    }
  };

  const handleContactOwner = () => {
    if (!user) {
      // Show auth modal when user is not logged in
      openAuthModal();
    } else {
      // When user is logged in, scroll to the contact information
      const contactCard = document.getElementById('property-contact-card');
      if (contactCard) {
        contactCard.scrollIntoView({ behavior: 'smooth' });
        // Add a temporary highlight effect
        contactCard.classList.add('ring-2', 'ring-blue-500');
        setTimeout(() => {
          contactCard.classList.remove('ring-2', 'ring-blue-500');
        }, 2000);
      }
    }
  };



  // Map configuration
  const mapCenter: [number, number] = [
    property.coordinates.lat,
    property.coordinates.lng
  ];
  
  // Create custom marker icon
  const propertyMarkerIcon = createPropertyIcon(property.propertyType, property.listingType);

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
                {property.images && property.images.length > 1 ? (
                  <PropertyImageGallery
                    images={property.images}
                    alt={property.title}
                    className="w-full h-96"
                    propertyId={property.id}
                  />
                ) : property.mainImage ? (
                  <div className="relative w-full h-96">
                    <img 
                      src={property.mainImage}
                      alt={property.title}
                      className="w-full h-96 object-cover"
                      onLoad={() => console.log('Main image loaded:', property.mainImage)}
                      onError={(e) => console.log('Main image failed to load:', property.mainImage, e)}
                    />
                  </div>
                ) : (
                  <div className="relative w-full h-96 bg-gray-100 flex items-center justify-center">
                    <img 
                      src="/images/placeholder-property.svg"
                      alt={t('property.noImagesAvailable', 'لا توجد صور متاحة')}
                      className="w-full h-96 object-contain opacity-60"
                    />
                    <div className="absolute bottom-4 left-4 bg-black bg-opacity-60 text-white px-3 py-1 rounded text-sm">
                      {t('property.noImagesAvailable', 'لا توجد صور متاحة')}
                    </div>
                  </div>
                )}

                {/* Badges */}
                <Badge 
                  className={`absolute top-4 left-4 ${
                    property.listingType === 'rent' ? 'bg-green-500' : 'bg-[#067977]'
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
                    <p className="text-3xl font-bold text-[#067977]">
                      {formatPrice(property.price, property.listingType)}
                    </p>
                    <p className="text-sm text-gray-500 mt-1">
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

            {/* Enhanced Features and Utilities Section */}
            <FeaturesAndUtilities 
              features={features}
              utilities={utilities}
              propertyFeatures={property.features || []}
              propertyUtilities={property.utilities || []}
              loading={(loadingFeatures || loadingUtilities) || minLoadingTime}
            />

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
                    {property.floor_number && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.floorNumber')}</span>
                        <span>{property.floor_number}</span>
                      </div>
                    )}
                    {property.total_floors && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.totalFloors')}</span>
                        <span>{property.total_floors}</span>
                      </div>
                    )}
                    {property.balcony_count && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.balconyCount')}</span>
                        <span>{property.balcony_count}</span>
                      </div>
                    )}
                    {property.orientation && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.orientation')}</span>
                        <span className="capitalize">{property.orientation}</span>
                      </div>
                    )}
                    {property.view_type && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.viewType')}</span>
                        <span className="capitalize">{property.view_type}</span>
                      </div>
                    )}
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
                    {property.building_age && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.buildingAge')}</span>
                        <span>{property.building_age} {t('property.details.years')}</span>
                      </div>
                    )}
                    {property.building_type && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.buildingType')}</span>
                        <span className="capitalize">{property.building_type}</span>
                      </div>
                    )}
                    {property.floor_type && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.floorType')}</span>
                        <span className="capitalize">{property.floor_type}</span>
                      </div>
                    )}
                    {property.window_type && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.windowType')}</span>
                        <span className="capitalize">{property.window_type}</span>
                      </div>
                    )}
                    {property.maintenance_fee && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.maintenanceFee')}</span>
                        <span>${property.maintenance_fee.toLocaleString()}</span>
                      </div>
                    )}
                    {property.deposit_amount && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.depositAmount')}</span>
                        <span>${property.deposit_amount.toLocaleString()}</span>
                      </div>
                    )}
                    {property.annual_tax && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.annualTax')}</span>
                        <span>${property.annual_tax.toLocaleString()}</span>
                      </div>
                    )}
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
                {loadError ? (
                  <div className="text-red-500 text-center p-4">
                    {t('map.failedToLoad', 'Failed to load map')}
                  </div>
                ) : !isMapLoaded ? (
                  <div className="text-center p-4">
                    <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
                    {t('map.loading', 'Loading map...')}
                  </div>
                ) : (
                  <div className="h-96 rounded-lg overflow-hidden border">
                    <MapContainer
                      center={mapCenter}
                      zoom={15}
                      style={{ height: '100%', width: '100%' }}
                      scrollWheelZoom={false}
                    >
                      <TileLayer
                        url={OSM_TILE_LAYER.url}
                        attribution={OSM_TILE_LAYER.attribution}
                      />
                      <Marker position={mapCenter} icon={propertyMarkerIcon} />
                    </MapContainer>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>

          {/* Sidebar */}
          <div className="space-y-6">
            {/* Contact Card */}
            <Card id="property-contact-card">
              <CardHeader>
                <CardTitle className="flex items-center">
                  <User className="h-5 w-5 mr-2" />
                  {t('property.actions.contactOwner')}
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {user ? (
                  // Show contact information when user is logged in
                  <div className="space-y-4">
                    <div className="flex items-center space-x-3">
                      <div className="bg-gray-200 border-2 border-dashed rounded-xl w-16 h-16 flex items-center justify-center">
                        <User className="h-8 w-8 text-gray-500" />
                      </div>
                      <div>
                        <p className="font-semibold text-lg">{property.contact.name}</p>
                        <p className="text-sm text-gray-500">{t('property.owner')}</p>
                      </div>
                    </div>
                    
                    <div className="space-y-3">
                      {property.contact.phone && (
                        <div className="flex items-start p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                          <div className="flex items-center mr-3 mt-1">
                            <Phone className="h-5 w-5 text-gray-500" />
                          </div>
                          <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-gray-500">{t('property.contact.phone')}</p>
                            <a 
                              href={`tel:${property.contact.phone}`} 
                              className="text-blue-600 hover:underline break-all"
                            >
                              {property.contact.phone}
                            </a>
                          </div>
                        </div>
                      )}
                      
                      {property.contact.email && (
                        <div className="flex items-start p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                          <div className="flex items-center mr-3 mt-1">
                            <Mail className="h-5 w-5 text-gray-500" />
                          </div>
                          <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-gray-500">{t('property.contact.email')}</p>
                            <a 
                              href={`mailto:${property.contact.email}`} 
                              className="text-blue-600 hover:underline break-words"
                            >
                              {property.contact.email}
                            </a>
                          </div>
                        </div>
                      )}
                    </div>
                  </div>
                ) : (
                  // Show login prompt when user is not logged in
                  <div className="text-center space-y-4 py-4">
                    <div className="bg-gray-100 rounded-full p-4 w-20 h-20 mx-auto flex items-center justify-center">
                      <User className="h-10 w-10 text-gray-400" />
                    </div>
                    <div>
                      <h3 className="text-lg font-medium text-gray-900 mb-1">
                        {t('auth.signIn')}
                      </h3>
                      <p className="text-gray-600">
                        {t('messages.signInToViewContactInfo')}
                      </p>
                    </div>
                  </div>
                )}
                
                <Button 
                  onClick={handleContactOwner}
                  className="w-full"
                  size="lg"
                >
                  {user ? t('property.actions.contactOwner') : t('auth.signIn')}
                </Button>
                
                {!user && (
                  <p className="text-sm text-gray-500 text-center mt-2">
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
              <CardContent className="space-y-4">
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
                {(property.document_type_id || documentType || loadingDocumentType) && (
                  <div className="flex justify-between">
                    <span className="text-gray-600 flex items-center gap-2">
                      <File className="h-4 w-4" />
                      {t('property.documentType')}
                    </span>
                    <span className="text-right">
                      {loadingDocumentType ? (
                        <div className="flex items-center gap-2">
                          <div className="animate-spin rounded-full h-3 w-3 border-b border-gray-600"></div>
                          <span className="text-sm text-gray-500">{t('common.loading')}</span>
                        </div>
                      ) : (
                        documentType?.name || t('property.documentTypes.unspecified')
                      )}
                    </span>
                  </div>
                )}
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
                  <div className="text-right">
                    <span className="text-xl font-bold text-[#067977]">
                      {formatPrice(property.price, property.listingType)}
                    </span>
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
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
      
      {/* Authentication Modal */}
      <AuthModal
        isOpen={showAuthModal}
        onClose={closeAuthModal}
        title={t('auth.requireAuth.title')}
        message={t('auth.requireAuth.viewOwnerContactMessage')}
        onSuccess={() => {
          closeAuthModal();
          // After successful login, show contact information
          notification.success(t('auth.requireAuth.loginSuccess'));
        }}
      />
      
      {/* Email Activation Modal */}
      <EmailActivationModal
        isOpen={showActivationModal}
        onClose={closeActivationModal}
        userEmail={user?.email}
      />
    </div>
  );
};

export default PropertyDetails;
