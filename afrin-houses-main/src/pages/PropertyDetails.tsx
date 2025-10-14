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
  , ZoomIn, ZoomOut, X
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
  const [propertyImages, setPropertyImages] = useState<any[]>([]);
  const [propertyVideos, setPropertyVideos] = useState<any[]>([]);
  const [loadingMedia, setLoadingMedia] = useState(false);
  const [combinedMedia, setCombinedMedia] = useState<Array<{type: 'image' | 'video'; url: string; id?: string}>>([]);
  const [mediaOrientations, setMediaOrientations] = useState<Record<number, 'portrait' | 'landscape' | 'square'>>({});
  const [mediaIndex, setMediaIndex] = useState(0);
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [lightboxZoom, setLightboxZoom] = useState(1);
  const [lightboxIndex, setLightboxIndex] = useState(0);

  const [showMap, setShowMap] = useState(false);

  // Calculate if current property is in favorites
  const propertyId = property?.id?.toString() || slug || '';
  const isFavorite = state.favorites.includes(propertyId);

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

        const propertyData = await getProperty(slug);
        
        if (!propertyData) {
          throw new Error('No property data found in response');
        }


        console.log('PropertyDetails - Raw propertyData:', propertyData);
        console.log('PropertyDetails - propertyData.features:', propertyData.features);
        console.log('PropertyDetails - propertyData.utilities:', propertyData.utilities);

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
          propertyType: propertyData.propertyType || propertyData.property_type || 'apartment',
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
          // Use mainImage and images directly from API
          mainImage: propertyData.mainImage || '/images/placeholder-property.svg',
          images: Array.isArray(propertyData.images?.gallery) ? propertyData.images.gallery : (Array.isArray(propertyData.images) ? propertyData.images : []),
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
          documentType: propertyData.document_type,
          // Currency information
          currency: propertyData.currency || propertyData.pricing?.currency || 'TRY',
          // Price type information
          priceType: propertyData.priceType || propertyData.price_type
        };
        

        // Transform property data to match frontend expectations
        const finalTransformedProperty = {
          ...transformedProperty,
          id: propertyData.id,
          title: propertyData.title,
          description: propertyData.description,
          price: propertyData.price,
          currency: propertyData.currency,
          listingType: propertyData.listingType || propertyData.listing_type,
          propertyType: propertyData.propertyType || propertyData.property_type,
          bedrooms: propertyData.bedrooms,
          bathrooms: propertyData.bathrooms,
          squareFootage: propertyData.squareFootage || propertyData.square_feet,
          yearBuilt: propertyData.yearBuilt || propertyData.year_built,
          address: propertyData.address || propertyData.street_address,
          city: propertyData.city,
          state: propertyData.state,
          country: propertyData.country,
          latitude: propertyData.latitude,
          longitude: propertyData.longitude,
          status: propertyData.status,
          isFeatured: propertyData.isFeatured || propertyData.is_featured,
          isAvailable: propertyData.isAvailable || propertyData.is_available,
          createdAt: propertyData.createdAt || propertyData.created_at,
          updatedAt: propertyData.updatedAt || propertyData.updated_at,
          publishedAt: propertyData.publishedAt || propertyData.published_at,
          viewsCount: propertyData.viewsCount || propertyData.views_count,
          rating: propertyData.rating,
          reviewsCount: propertyData.reviewsCount || propertyData.reviews_count,
          features: propertyData.features || [],
          utilities: propertyData.utilities || [],
          nearbyPlaces: propertyData.nearbyPlaces || propertyData.nearby_places || [],
          user: propertyData.user,
          media: propertyData.media || [],
          priceType: propertyData.priceType || propertyData.price_type,
          videos: propertyData.videos || [],
        };

        // Process videos from property data (already included in the API response)
        if (propertyData.videos && Array.isArray(propertyData.videos)) {
          const videos = propertyData.videos.map((video: any, index: number) => ({
            id: video.id || index + 1,
            url: video.url,
            original_url: video.url,
            thumbnail_url: undefined,
            duration: undefined,
            size: video.size,
            mime_type: video.mime_type,
            file_name: video.file_name
          }));
          setPropertyVideos(videos);
          finalTransformedProperty.videos = videos;
        }

        // Update property state with final data
        setProperty(finalTransformedProperty);
        setLoadingMedia(false);
      } catch (err: any) {
        console.error('Error loading property:', err);
        console.error('Error details:', {
          message: err.message,
          response: err.response,
          status: err.response?.status,
          data: err.response?.data
        });

        // Handle specific error types
        if (err.response?.status === 404) {
          setError('Property not found. This property may have been removed or the URL is incorrect.');
          notification.error('Property not found');
        } else if (err.response?.status >= 500) {
          setError('Server error. Please try again later.');
          notification.error('Server error occurred');
        } else {
          setError('Failed to load property details. Please check your connection and try again.');
          notification.error(`Failed to load property details: ${err.message || 'Unknown error'}`);
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

      
      if (property?.document_type_id && !property?.documentType) {
        setLoadingDocumentType(true);
        try {

          const docType = await propertyDocumentTypeService.getPropertyDocumentTypeById(
            Number(property.document_type_id),
            { lang: i18n.language }
          );

          if (docType) {
            setDocumentType(docType);
          }
        } catch (error) {

        } finally {
          setLoadingDocumentType(false);
        }
      } else if (property?.documentType) {
        // If document type is already included in property data

        setDocumentType(property.documentType);
      } else if (property?.document_type_id) {
        // If we have a document_type_id but no documentType, still try to fetch

        setLoadingDocumentType(true);
        try {
          const docType = await propertyDocumentTypeService.getPropertyDocumentTypeById(
            Number(property.document_type_id),
            { lang: i18n.language }
          );

          if (docType) {
            setDocumentType(docType);
          }
        } catch (error) {

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

      } finally {
        setLoadingFeatures(false);
        setLoadingUtilities(false);
      }
    };

    fetchFeaturesAndUtilities();
  }, [i18n.language]);

  // Build combined media array when property changes
  useEffect(() => {
    if (!property) return;
    const normalizeUrl = (item: any): string | null => {
      if (!item) return null;
      if (typeof item === 'string') return item;
      // Common url fields to check
      const candidates = [
        'url', 'original_url', 'cdn_url', 'path', 'file', 'file_name', 'thumbnail_url'
      ];
      for (const key of candidates) {
        if (item[key]) return item[key];
      }
      // Some video objects include a nested attributes object
      if (item.attributes && typeof item.attributes === 'object') {
        for (const key of candidates) {
          if (item.attributes[key]) return item.attributes[key];
        }
      }
      // Fallback: try to stringify a 'source' or 'src'
      if (item.source) return item.source;
      if (item.src) return item.src;
      return null;
    };

    const videoItems: Array<{type: 'video'; url: string; id?: string}> = [];
    const imageItems: Array<{type: 'image'; url: string; id?: string}> = [];

    // Collect videos
    if (Array.isArray(property.videos) && property.videos.length > 0) {
      property.videos.forEach((v: any, idx: number) => {
        const url = normalizeUrl(v) || v.url || v.original_url || v.source || v.src;
        if (!url) return;
        videoItems.push({ type: 'video', url, id: v.id || `vid-${idx}` });
      });
    }

    // Collect images from multiple possible sources: property.media, property.images, property.mainImage
    const seen = new Set<string>();

    if (Array.isArray(property.media)) {
      property.media.forEach((m: any, idx: number) => {
        if (!m) return;
        if ((m.type && m.type === 'video')) return; // skip videos here
        const url = normalizeUrl(m) || (m.type === 'image' && m.url) || null;
        if (!url) return;
        if (seen.has(url)) return;
        seen.add(url);
        imageItems.push({ type: 'image', url, id: m.id || `media-img-${idx}` });
      });
    }

    if (Array.isArray(property.images)) {
      property.images.forEach((img: any, idx: number) => {
        const url = normalizeUrl(img) || (typeof img === 'string' ? img : null);
        if (!url) return;
        if (seen.has(url)) return;
        seen.add(url);
        imageItems.push({ type: 'image', url, id: (img && img.id) || `img-${idx}` });
      });
    }

    // mainImage fallback: prefer it among images but after videos
    if (property.mainImage) {
      const url = typeof property.mainImage === 'string' ? property.mainImage : normalizeUrl(property.mainImage);
      if (url && !seen.has(url)) {
        seen.add(url);
        // place main image at the front of the images list
        imageItems.unshift({ type: 'image', url, id: 'main' });
      }
    }

    // If no combined found but we have some images via mainImage or others, ensure we still show them
    const combined = [...videoItems, ...imageItems];
    if (combined.length === 0 && property.mainImage) {
      const url = typeof property.mainImage === 'string' ? property.mainImage : normalizeUrl(property.mainImage);
      if (url) combined.push({ type: 'image', url, id: 'main' });
    }

    setCombinedMedia(combined);
    setMediaIndex(0);
  }, [property]);

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
  const formatPrice = (price: any, listingType: string, currency?: string, priceType?: any) => {
    try {
      
      // If price is null/undefined, return price on request
      if (price === null || price === undefined || price === '') {
        return t('property.priceOnRequest');
      }
      
      // Handle different price formats
      let numPrice = 0;
      let currencyCode = currency || property.currency || 'TRY';
      
      // Handle object with amount property
      if (typeof price === 'object' && price !== null) {
        // Check for common price object structures
        if (typeof price.amount !== 'undefined') {
          numPrice = Number(price.amount) || 0;
          currencyCode = price.currency || currency || 'USD';
        } else if (typeof price.price !== 'undefined') {
          numPrice = Number(price.price) || 0;
        } else if (typeof price.formatted !== 'undefined') {
          // Handle formatted price strings
          const cleanPrice = price.formatted.replace(/[^\d.-]/g, '');
          numPrice = Number(cleanPrice) || 0;
        } else {
          // Try to get the first numeric property
          const numericValue = Object.values(price).find(val => !isNaN(Number(val)));
          numPrice = Number(numericValue) || 0;
        }
      } 
      // Handle string (might be JSON or plain number)
      else if (typeof price === 'string') {
        // Try to parse as JSON first
        try {
          const parsed = JSON.parse(price);
          if (typeof parsed === 'object' && parsed !== null) {
            return formatPrice(parsed, listingType, currency, priceType); // Recursively handle the parsed object
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
      
      // Format the price
      const formattedNumber = new Intl.NumberFormat(i18n.language === 'ar' ? 'ar-SA' : 'en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(numPrice);

      // Add currency symbol
      const formattedPrice = `${formattedNumber} ${currencyCode}`;
      
      // Get price type text from database
      if (priceType && typeof priceType === 'object') {
        const priceTypeText = i18n.language === 'ar' ? priceType.name_ar :
                             i18n.language === 'ku' ? priceType.name_ku :
                             priceType.name_en;
        
        // Return formatted price with price type from database
        if (priceTypeText) {
          return `${formattedPrice} / ${priceTypeText}`;
        }
      }
      // If no priceType provided, return just the price
      return formattedPrice;
    } catch (error) {
      console.error('Error in formatPrice:', error);
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
            {/* Media Gallery (Videos & Images) */}
            <Card className="overflow-hidden">
              <div className="relative">
                {/* Main media display */}
                {combinedMedia && combinedMedia.length > 0 ? (
                  <div className="relative w-full h-96 bg-black">
                    {/* Prev button */}
                    {combinedMedia.length > 1 && (
                      <button
                        onClick={() => setMediaIndex((mediaIndex - 1 + combinedMedia.length) % combinedMedia.length)}
                        aria-label="Previous media"
                        className="absolute left-2 top-1/2 -translate-y-1/2 z-20 bg-black bg-opacity-40 hover:bg-opacity-60 text-white rounded-full w-8 h-8 flex items-center justify-center"
                      >
                        &lt;
                      </button>
                    )}

                    {/* Enlarge / open lightbox */}
                    <button
                      onClick={() => { setLightboxIndex(mediaIndex); setLightboxZoom(1); setLightboxOpen(true); }}
                      aria-label="Open fullscreen"
                      className="absolute top-2 right-12 z-30 bg-black bg-opacity-40 hover:bg-opacity-60 text-white rounded-full w-8 h-8 flex items-center justify-center"
                    >
                      <ZoomIn className="h-4 w-4" />
                    </button>

                    {/* Media */}
                    {combinedMedia[mediaIndex].type === 'video' ? (
                      <video
                        src={combinedMedia[mediaIndex].url}
                        controls
                        className="w-full h-96 object-contain bg-black"
                        preload="metadata"
                      />
                    ) : (
                      <img
                        src={combinedMedia[mediaIndex].url}
                        alt={property.title}
                        onLoad={(e) => {
                          try {
                            const img = e.currentTarget as HTMLImageElement;
                            const w = img.naturalWidth;
                            const h = img.naturalHeight;
                            const orient = w > h ? 'landscape' : (h > w ? 'portrait' : 'square');
                            setMediaOrientations(prev => ({ ...prev, [mediaIndex]: orient }));
                          } catch (err) {}
                        }}
                        className={`w-full h-96 object-center transition-all duration-200 ${
                          mediaOrientations[mediaIndex] === 'portrait' ? 'object-contain bg-black' : 'object-cover'
                        }`}
                      />
                    )}

                    {/* Next button */}
                    {combinedMedia.length > 1 && (
                      <button
                        onClick={() => setMediaIndex((mediaIndex + 1) % combinedMedia.length)}
                        aria-label="Next media"
                        className="absolute right-2 top-1/2 -translate-y-1/2 z-20 bg-black bg-opacity-40 hover:bg-opacity-60 text-white rounded-full w-8 h-8 flex items-center justify-center"
                      >
                        &gt;
                      </button>
                    )}
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

              {/* Additional Media Grid (thumbnails) - render from combinedMedia */}
              {combinedMedia && combinedMedia.length > 1 && (
                <CardContent className="pt-4">
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                    {combinedMedia.map((item, index) => (
                      <div key={item.id || `media-${index}`} className="relative rounded-lg overflow-hidden border border-gray-200 group cursor-pointer" onClick={() => { setMediaIndex(index); }} role="button" aria-label={`Show media ${index + 1}`}>
                        {item.type === 'video' ? (
                          <video
                            src={item.url}
                            className="w-full h-24 object-cover bg-black"
                            preload="metadata"
                          />
                        ) : (
                          <img
                            src={item.url}
                            alt={`${property.title} - ${index + 1}`}
                            className={`w-full h-24 transition-all duration-200 ${
                              mediaOrientations[index] === 'portrait' ? 'object-contain object-center bg-black' : 'object-cover object-center'
                            }`}
                            onLoad={(e) => {
                              try {
                                const img = e.currentTarget as HTMLImageElement;
                                const w = img.naturalWidth;
                                const h = img.naturalHeight;
                                const orient = w > h ? 'landscape' : (h > w ? 'portrait' : 'square');
                                setMediaOrientations(prev => ({ ...prev, [index]: orient }));
                              } catch (err) {}
                            }}
                          />
                        )}
                        <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 flex items-center justify-center">
                          {item.type === 'video' ? (
                            <svg className="h-8 w-8 text-white opacity-80" fill="currentColor" viewBox="0 0 20 20">
                              <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
                            </svg>
                          ) : null}
                        </div>
                      </div>
                    ))}
                  </div>
                </CardContent>
              )}
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
                      {formatPrice(property.price, property.listingType, property.currency, property.priceType)}
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
                      <span className="capitalize">
                        {property.propertyType && typeof property.propertyType === 'object'
                          ? (i18n.language === 'ar' ? (property.propertyType as any).name_ar :
                             i18n.language === 'ku' ? (property.propertyType as any).name_ku :
                             (property.propertyType as any).name_en || (property.propertyType as any).name)
                          : (property.propertyType || t('property.typeNotAvailable'))
                        }
                      </span>
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
                        <span className="capitalize">{normalizeName(property.orientation)}</span>
                      </div>
                    )}
                    {property.view_type && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.viewType')}</span>
                        <span className="capitalize">{normalizeName(property.view_type)}</span>
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
                        <span>{normalizeName(property.parking)}</span>
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
                        <span className="capitalize">{normalizeName(property.building_type)}</span>
                      </div>
                    )}
                    {property.floor_type && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.floorType')}</span>
                        <span className="capitalize">{normalizeName(property.floor_type)}</span>
                      </div>
                    )}
                    {property.window_type && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">{t('property.details.windowType')}</span>
                        <span className="capitalize">{normalizeName(property.window_type)}</span>
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
                        <span>{normalizeName(property.utilities)}</span>
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
                  <span className="capitalize">
                    {property.propertyType && typeof property.propertyType === 'object'
                      ? (i18n.language === 'ar' ? (property.propertyType as any).name_ar :
                         i18n.language === 'ku' ? (property.propertyType as any).name_ku :
                         (property.propertyType as any).name_en || (property.propertyType as any).name)
                      : (property.propertyType || t('property.typeNotAvailable'))
                    }
                  </span>
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
                      {formatPrice(property.price, property.listingType, property.currency, property.priceType)}
                    </span>

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
      {/* Lightbox Modal */}
      {lightboxOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-80">
          <div className="relative max-w-6xl w-full mx-4">
            <button
              onClick={() => setLightboxOpen(false)}
              aria-label="Close fullscreen"
              className="absolute top-3 right-3 z-40 bg-white bg-opacity-10 hover:bg-opacity-20 text-white rounded-full w-10 h-10 flex items-center justify-center"
            >
              <X className="h-5 w-5" />
            </button>

            <div className="flex items-center justify-center">
              <button
                onClick={() => setLightboxIndex((lightboxIndex - 1 + combinedMedia.length) % combinedMedia.length)}
                className="absolute left-4 top-1/2 -translate-y-1/2 z-40 bg-white bg-opacity-10 hover:bg-opacity-20 text-white rounded-full w-10 h-10 flex items-center justify-center"
                aria-label="Previous"
              >&lt;</button>

              <div className="bg-black rounded overflow-hidden max-h-[80vh] flex items-center justify-center">
                {combinedMedia[lightboxIndex].type === 'video' ? (
                  <video src={combinedMedia[lightboxIndex].url} controls className="max-h-[80vh]" />
                ) : (
                  <img src={combinedMedia[lightboxIndex].url} alt={`Fullscreen ${lightboxIndex+1}`} style={{ transform: `scale(${lightboxZoom})` }} className="max-h-[80vh] max-w-full" />
                )}
              </div>

              <button
                onClick={() => setLightboxIndex((lightboxIndex + 1) % combinedMedia.length)}
                className="absolute right-4 top-1/2 -translate-y-1/2 z-40 bg-white bg-opacity-10 hover:bg-opacity-20 text-white rounded-full w-10 h-10 flex items-center justify-center"
                aria-label="Next"
              >&gt;</button>
            </div>

            <div className="absolute bottom-6 left-1/2 -translate-x-1/2 z-40 flex items-center gap-3">
              <button onClick={() => setLightboxZoom(z => Math.max(0.5, +(z - 0.25).toFixed(2)))} className="bg-white bg-opacity-10 hover:bg-opacity-20 text-white rounded-full w-10 h-10 flex items-center justify-center" aria-label="Zoom out"><ZoomOut className="h-4 w-4"/></button>
              <div className="text-white text-sm">{Math.round(lightboxZoom * 100)}%</div>
              <button onClick={() => setLightboxZoom(z => Math.min(3, +(z + 0.25).toFixed(2)))} className="bg-white bg-opacity-10 hover:bg-opacity-20 text-white rounded-full w-10 h-10 flex items-center justify-center" aria-label="Zoom in"><ZoomIn className="h-4 w-4"/></button>
            </div>
          </div>
        </div>
      )}
      
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
