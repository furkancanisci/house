import React, { useState, useCallback, useEffect, useMemo, startTransition } from 'react';
import { useNavigate } from 'react-router-dom';
import { throttle } from 'lodash';
import { MapContainer, TileLayer, Marker, Popup, useMap, useMapEvents } from 'react-leaflet';
import { useTranslation } from 'react-i18next';
import { useLeafletMap, DEFAULT_CENTER, DEFAULT_ZOOM, OSM_TILE_LAYER, createPropertyIcon } from '../context/LeafletMapProvider';
import {
  MapPin,
  List,
  Grid,
  Filter,
  Home,
  Loader2,
  X,
  RotateCcw,
  ChevronDown,
  Eye,
  Navigation
} from 'lucide-react'
import { Button } from './ui/button';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Badge } from './ui/badge';
import { Separator } from './ui/separator';
import { toast } from 'sonner';
import { Property, SearchFilters } from '../types';
import PropertyCard from './PropertyCard';
import SearchFiltersComponent from './SearchFilters';
import ErrorBoundary from './ErrorBoundary';
import L from 'leaflet';

interface MapSearchViewProps {
  properties: Property[];
  onFiltersChange?: (filters: SearchFilters) => void;
  onPropertySelect?: (property: Property) => void;
  initialFilters?: SearchFilters;
  loading?: boolean;
  onClose?: () => void;
  fullScreen?: boolean;
}

// Map bounds for Syria region
const SYRIA_BOUNDS: [[number, number], [number, number]] = [
  [32.0, 35.5], // Southwest
  [37.5, 42.5]  // Northeast
];

// Component to handle map events
const MapEventHandler: React.FC<{
  onBoundsChange: (bounds: L.LatLngBounds, zoom: number) => void;
  onMapClick: () => void;
}> = ({ onBoundsChange, onMapClick }) => {
  const map = useMap();
  
  useMapEvents({
    moveend: () => {
      const bounds = map.getBounds();
      const zoom = map.getZoom();
      onBoundsChange(bounds, zoom);
    },
    zoomend: () => {
      const bounds = map.getBounds();
      const zoom = map.getZoom();
      onBoundsChange(bounds, zoom);
    },
    click: () => {
      onMapClick();
    }
  });
  
  return null;
};

// Component to handle map resizing when view mode changes
const MapResizeHandler: React.FC<{
  viewMode: 'list' | 'map' | 'split';
}> = ({ viewMode }) => {
  const map = useMap();
  
  useEffect(() => {
    if (!map) return;
    
    // Delay map resize to ensure container has finished transitioning
    const resizeTimer = setTimeout(() => {
      map.invalidateSize({
        animate: true,
        pan: false
      });
    }, 300); // Wait for CSS transitions to complete
    
    return () => clearTimeout(resizeTimer);
  }, [viewMode, map]);
  
  return null;
};

// Component to center map on selected property
const MapController: React.FC<{
  selectedProperty: Property | null;
}> = ({ selectedProperty }) => {
  const map = useMap();
  
  useEffect(() => {
    if (!map) return;
    
    if (selectedProperty && selectedProperty.latitude && selectedProperty.longitude) {
      const lat = typeof selectedProperty.latitude === 'string' 
        ? parseFloat(selectedProperty.latitude) 
        : selectedProperty.latitude;
      const lng = typeof selectedProperty.longitude === 'string' 
        ? parseFloat(selectedProperty.longitude) 
        : selectedProperty.longitude;
      
      if (!isNaN(lat) && !isNaN(lng)) {
        // Use panTo for smooth animation to center, then setView for proper zoom
        map.panTo([lat, lng], {
          animate: true,
          duration: 1.0,
          easeLinearity: 0.5
        });
        
        // Delay zoom to ensure centering happens first
        setTimeout(() => {
          map.setView([lat, lng], Math.max(map.getZoom(), 15), {
            animate: true,
            duration: 0.5
          });
        }, 100);
      }
    }
  }, [selectedProperty, map]);
  
  return null;
};

const MapSearchView: React.FC<MapSearchViewProps> = ({
  properties,
  onFiltersChange,
  onPropertySelect,
  initialFilters,
  loading = false,
  onClose,
  fullScreen = false
}) => {
  const { t } = useTranslation();
  const { isLoaded: isMapLoaded, loadError } = useLeafletMap();
  const navigate = useNavigate();
  
  // View mode state
  const [viewMode, setViewMode] = useState<'list' | 'map' | 'split'>('split');
  const [isMapFullScreen, setIsMapFullScreen] = useState(false);
  
  const handleViewModeChange = useCallback((mode: 'list' | 'map' | 'split') => {
    startTransition(() => {
      setViewMode(mode);
    });
    
    // Force a re-render cycle for the map when switching to map mode
    if (mode === 'map') {
      // Small delay to ensure DOM has updated before triggering map resize
      setTimeout(() => {
        // This will trigger the MapResizeHandler useEffect
        setViewMode(mode);
      }, 50);
    }
  }, []);
  const [selectedProperty, setSelectedProperty] = useState<Property | null>(null);
  const [showFilters, setShowFilters] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [filters, setFilters] = useState<SearchFilters>(initialFilters || {});
  const [userLocation, setUserLocation] = useState<[number, number] | null>(null);
  const [isLocating, setIsLocating] = useState(false);
  const [locationError, setLocationError] = useState<string | null>(null);
  const [selectedState, setSelectedState] = useState<string>('');
  const [selectedCity, setSelectedCity] = useState<string>('');
  
  // Enhanced loading and error states
  const [isLoadingCities, setIsLoadingCities] = useState(false);
  const [citiesError, setCitiesError] = useState<string | null>(null);
  const [isLoadingProperties, setIsLoadingProperties] = useState(false);
  const [propertiesError, setPropertiesError] = useState<string | null>(null);
  
  // Map state
  const [mapCenter, setMapCenter] = useState<[number, number]>(DEFAULT_CENTER);
  const [mapZoom, setMapZoom] = useState(DEFAULT_ZOOM);
  
  // Function to get user's current location
  const getCurrentLocation = useCallback(() => {
    if (!navigator.geolocation) {
      setLocationError('الموقع الجغرافي غير مدعوم في هذا المتصفح');
      toast.error('الموقع الجغرافي غير مدعوم في هذا المتصفح');
      return;
    }
    
    setIsLocating(true);
    setLocationError(null);
    
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { latitude, longitude } = position.coords;
        const newLocation: [number, number] = [latitude, longitude];
        setUserLocation(newLocation);
        setMapCenter(newLocation);
        setMapZoom(15);
        setIsLocating(false);
        toast.success('تم تحديد موقعك بنجاح');
      },
      (error) => {
        let errorMessage = 'فشل في تحديد الموقع';
        switch (error.code) {
          case error.PERMISSION_DENIED:
            errorMessage = 'تم رفض الإذن للوصول إلى الموقع';
            break;
          case error.POSITION_UNAVAILABLE:
            errorMessage = 'الموقع غير متاح';
            break;
          case error.TIMEOUT:
            errorMessage = 'انتهت مهلة تحديد الموقع';
            break;
        }
        setLocationError(errorMessage);
        setIsLocating(false);
        toast.error(errorMessage);
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 300000 // 5 minutes
      }
    );
  }, []);
  
  // Constants
  const propertiesPerPage = 12;
  
  // Responsive view mode
  useEffect(() => {
    const handleResize = () => {
      if (window.innerWidth < 1024) {
        setViewMode('list');
      }
    };
    
    handleResize();
    window.addEventListener('resize', handleResize);
    return () => window.removeEventListener('resize', handleResize);
  }, []);
  
  // Get user location on component mount
  useEffect(() => {
    if (navigator.geolocation && !userLocation) {
      getCurrentLocation();
    }
  }, [getCurrentLocation, userLocation]);
  
  // Filter properties based on filters only
  const filteredProperties = useMemo(() => {
    let filtered = properties.filter(property => {
      // Validate coordinates
      const lat = typeof property.latitude === 'string' 
        ? parseFloat(property.latitude) 
        : property.latitude;
      const lng = typeof property.longitude === 'string' 
        ? parseFloat(property.longitude) 
        : property.longitude;
      
      return !isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0;
    });
    
    // Apply other filters
    if (filters.propertyType && filters.propertyType !== 'all') {
      filtered = filtered.filter(property => property.propertyType === filters.propertyType);
    }
    
    if (filters.listingType && filters.listingType !== 'all') {
      filtered = filtered.filter(property => property.listingType === filters.listingType);
    }
    
    if (filters.minPrice !== undefined) {
      filtered = filtered.filter(property => {
        const price = typeof property.price === 'string' ? parseFloat(property.price) : property.price;
        return price >= filters.minPrice!;
      });
    }
    
    if (filters.maxPrice !== undefined) {
      filtered = filtered.filter(property => {
        const price = typeof property.price === 'string' ? parseFloat(property.price) : property.price;
        return price <= filters.maxPrice!;
      });
    }
    
    if (filters.bedrooms !== undefined) {
      filtered = filtered.filter(property => property.bedrooms >= filters.bedrooms!);
    }
    
    if (filters.bathrooms !== undefined) {
      filtered = filtered.filter(property => property.bathrooms >= filters.bathrooms!);
    }
    
    return filtered;
  }, [properties, filters]);
  
  // Paginated properties for list view
  const paginatedProperties = useMemo(() => {
    const startIndex = (currentPage - 1) * propertiesPerPage;
    return filteredProperties.slice(startIndex, startIndex + propertiesPerPage);
  }, [filteredProperties, currentPage]);
  
  const totalPages = Math.ceil(filteredProperties.length / propertiesPerPage);
  
  // Handle location change
  const handleLocationChange = useCallback((location: { state?: string; city?: string }) => {
    if (location.state !== undefined) {
      setSelectedState(location.state);
    }
    if (location.city !== undefined) {
      setSelectedCity(location.city);
    }
  }, []);

  // Handle map bounds change (removed API call to prevent excessive requests)
  const handleBoundsChange = useCallback(
    throttle((bounds: L.LatLngBounds, zoom: number) => {
      // Store viewport data locally without triggering API calls
      const viewport = {
        north: bounds.getNorth(),
        south: bounds.getSouth(),
        east: bounds.getEast(),
        west: bounds.getWest(),
        zoom
      };
      // Only update local state, don't call API on map movement
      console.log('Map bounds changed:', viewport);
    }, 500),
    []
  );
  
  // Handle map click to toggle full screen
  const handleMapClick = useCallback(() => {
    setIsMapFullScreen(prev => !prev);
  }, []);
  
  // Handle property selection
  const handlePropertySelect = useCallback((property: Property) => {
    setSelectedProperty(property);
    if (onPropertySelect) {
      onPropertySelect(property);
    }
  }, [onPropertySelect]);
  
  // Handle filters change
  const handleFiltersChange = useCallback((newFilters: SearchFilters) => {
    startTransition(() => {
      setFilters(newFilters);
      setCurrentPage(1);
    });
    if (onFiltersChange) {
      onFiltersChange(newFilters);
    }
  }, [onFiltersChange]);
  
  // Reset filters
  const resetFilters = useCallback(() => {
    setFilters({});
    setCurrentPage(1);
    if (onFiltersChange) {
      onFiltersChange({});
    }
  }, [onFiltersChange]);
  
  // Error retry handlers
  const retryLoadProperties = useCallback(() => {
    setPropertiesError(null);
    setIsLoadingProperties(true);
    // Trigger properties reload
    if (onFiltersChange) {
      onFiltersChange(filters);
    }
    setTimeout(() => setIsLoadingProperties(false), 2000);
  }, [filters, onFiltersChange]);
  
  const retryLoadCities = useCallback(() => {
    setCitiesError(null);
    setIsLoadingCities(true);
    // Simulate cities reload
    setTimeout(() => {
      setIsLoadingCities(false);
      toast.success(t('map.searchUpdated'));
    }, 1500);
  }, []);
  
  // Error display component
  const ErrorDisplay = ({ error, onRetry, type }: { error: string; onRetry: () => void; type: string }) => (
    <div className="flex flex-col items-center justify-center py-8 px-4">
      <div className="bg-red-50 border border-red-200 rounded-lg p-6 max-w-md w-full text-center">
        <div className="text-red-600 mb-4">
          <X className="h-12 w-12 mx-auto" />
        </div>
        <h3 className="text-lg font-semibold text-red-800 mb-2">{t('map.errorLoadingType', { type })}</h3>
        <p className="text-red-600 text-sm mb-4">{error}</p>
        <Button 
          onClick={onRetry}
          variant="outline"
          className="border-red-300 text-red-700 hover:bg-red-50"
        >
          <RotateCcw className="h-4 w-4 mr-2" />
          {t('map.retryLoading')}
        </Button>
      </div>
    </div>
  );
  
  // Render property markers
  const renderMarkers = () => {
    return filteredProperties.map((property) => {
      const lat = typeof property.latitude === 'string' 
        ? parseFloat(property.latitude) 
        : property.latitude;
      const lng = typeof property.longitude === 'string' 
        ? parseFloat(property.longitude) 
        : property.longitude;
      
      if (isNaN(lat) || isNaN(lng)) return null;
      
      const icon = createPropertyIcon(property.propertyType, property.listingType);
      
      return (
        <Marker
          key={property.id}
          position={[lat, lng]}
          icon={icon}
          eventHandlers={{
            click: () => {
              console.log('Map marker clicked:', property.title);
              handlePropertySelect(property);
            }
          }}
          // Add special styling for selected property
          {...(selectedProperty?.id === property.id && {
            zIndexOffset: 1000 // Bring selected marker to front
          })}
        >
          <Popup className="custom-popup" maxWidth={340}>
            <div className="overflow-hidden rounded-lg">
              <div className="relative">
                <img 
                  src={property.images?.[0] || '/placeholder-property.jpg'} 
                  alt={property.title}
                  className="w-full h-36 object-cover"
                />
                <div className="absolute top-2 right-2">
                  <span className={`px-3 py-1 rounded-full text-xs font-bold shadow-lg ${
                    property.listingType === 'sale' 
                      ? 'bg-green-500 text-white' 
                      : 'bg-orange-500 text-white'
                  }`}>
                    {property.listingType === 'sale' ? t('property.forSale') : t('property.forRent')}
                  </span>
                </div>
              </div>
              <div className="p-4">
                <h3 className="font-bold text-gray-900 mb-2 line-clamp-2 text-lg">{property.title}</h3>
                <p className="text-sm text-gray-600 mb-3 flex items-center">
                  <MapPin className="h-4 w-4 mr-1 text-[#067977]" />
                  {property.address}
                </p>
                <div className="flex items-center justify-between mb-4">
                  <div className="flex space-x-4 rtl:space-x-reverse text-sm text-gray-600">
                    {property.bedrooms && (
                      <span className="flex items-center bg-gray-100 px-2 py-1 rounded-full">
                        <Home className="h-3 w-3 mr-1" />
                        {property.bedrooms}
                      </span>
                    )}
                    {property.bathrooms && (
                      <span className="bg-gray-100 px-2 py-1 rounded-full">{property.bathrooms} {t('map.bathroom')}</span>
                    )}
                    {property.squareFootage && (
                      <span className="bg-gray-100 px-2 py-1 rounded-full">{property.squareFootage} م²</span>
                    )}
                  </div>
                </div>
                <div className="flex justify-between items-center">
                  <span className="text-xl font-bold text-[#067977]">
                    ${typeof property.price === 'string' ? property.price : property.price?.toLocaleString()}
                    {property.listingType === 'rent' && <span className="text-sm text-gray-500">/{t('property.month')}</span>}
                  </span>
                  <Button 
                    size="sm" 
                    onClick={(e) => {
                      e.stopPropagation();
                      console.log('Popup button clicked for property:', property.id);
                      navigate(`/property/${property.id}`);
                    }}
                    className="bg-[#067977] hover:bg-[#067977]/90 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:shadow-lg hover:scale-105 font-medium"
                  >
                    {t('map.viewDetails')}
                  </Button>
                </div>
              </div>
            </div>
          </Popup>
        </Marker>
      );
    });
  };
  
  return (
    <ErrorBoundary>
      <div className={`flex flex-col h-full ${fullScreen || isMapFullScreen ? 'fixed inset-0 z-50 bg-white' : ''}`}>
      {/* Compact Header */}
      <div className="bg-gradient-to-r from-[#067977]/5 via-[#067977]/10 to-[#067977]/5 border-b border-gray-200 shadow-sm backdrop-blur-sm">
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between p-1 sm:p-2 gap-1 sm:gap-0">
          <div className="flex items-center gap-1 sm:gap-2 w-full sm:w-auto">
            {onClose && (
              <Button 
                variant="ghost" 
                size="sm"
                onClick={onClose}
                className="hover:bg-white/60 transition-colors duration-200 p-0.5 sm:p-1"
              >
                <X className="h-3 w-3 text-gray-600" />
              </Button>
            )}
            <div className="flex items-center gap-1">
              <div className="flex items-center gap-1">
                <div className="p-1 bg-gradient-to-br from-[#067977]/20 via-[#067977]/30 to-[#067977]/20 rounded-md shadow-sm ring-1 ring-[#067977]/30">
                  <MapPin className="h-3 w-3 text-[#067977]" />
                </div>
                <div>
                  <h1 className="text-sm font-bold bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">{t('map.title')}</h1>
                  <p className="text-xs text-gray-600 hidden sm:block font-medium">{t('map.explorePropertiesOnMap')}</p>
                </div>
              </div>
            </div>
          </div>
          
          <div className="flex items-center gap-1 w-full sm:w-auto justify-end">
            {/* Compact View mode toggle */}
            <div className="hidden lg:flex items-center gap-0.5 bg-gradient-to-r from-gray-50 via-gray-100 to-gray-50 rounded-md p-0.5 shadow-sm border border-gray-200 backdrop-blur-sm">
              <Button
                variant={viewMode === 'list' ? 'default' : 'ghost'}
                size="sm"
                onClick={() => handleViewModeChange('list')}
                className={`relative transition-all duration-300 ease-in-out transform hover:scale-105 px-1.5 py-1 rounded-sm text-xs ${
                  viewMode === 'list' 
                    ? 'bg-gradient-to-r from-[#067977] via-[#067977]/90 to-[#067977]/80 text-white shadow-md scale-105 ring-1 ring-[#067977]/30' 
                    : 'hover:bg-gradient-to-r hover:from-white hover:to-[#067977]/10 hover:shadow-sm text-gray-700 hover:text-[#067977]'
                }`}
              >
                <List className="h-3 w-3 mr-1" />
                <span className="hidden xl:inline font-medium">{t('map.listViewMode')}</span>
                {viewMode === 'list' && (
                  <div className="absolute inset-0 bg-[#067977]/40 rounded-md opacity-20 animate-pulse"></div>
                )}
              </Button>
              <Button
                variant={viewMode === 'split' ? 'default' : 'ghost'}
                size="sm"
                onClick={() => handleViewModeChange('split')}
                className={`relative transition-all duration-300 ease-in-out transform hover:scale-105 px-3 lg:px-4 py-2 rounded-lg ${
                  viewMode === 'split' 
                    ? 'bg-gradient-to-r from-[#067977] via-[#067977]/90 to-[#067977]/80 text-white shadow-xl scale-105 ring-2 ring-[#067977]/30' 
                    : 'hover:bg-gradient-to-r hover:from-white hover:to-[#067977]/10 hover:shadow-lg text-gray-700 hover:text-[#067977]'
                }`}
              >
                <Grid className="h-4 w-4 mr-2" />
                <span className="hidden xl:inline font-medium text-sm">{t('map.splitViewMode')}</span>
                {viewMode === 'split' && (
                  <div className="absolute inset-0 bg-[#067977]/40 rounded-lg opacity-20 animate-pulse"></div>
                )}
              </Button>
              <Button
                variant={viewMode === 'map' ? 'default' : 'ghost'}
                size="sm"
                onClick={() => handleViewModeChange('map')}
                className={`relative transition-all duration-300 ease-in-out transform hover:scale-105 px-3 lg:px-4 py-2 rounded-lg ${
                  viewMode === 'map' 
                    ? 'bg-gradient-to-r from-[#067977] via-[#067977]/90 to-[#067977]/80 text-white shadow-xl scale-105 ring-2 ring-[#067977]/30' 
                    : 'hover:bg-gradient-to-r hover:from-white hover:to-[#067977]/10 hover:shadow-lg text-gray-700 hover:text-[#067977]'
                }`}
              >
                <MapPin className="h-4 w-4 mr-2" />
                <span className="hidden xl:inline font-medium text-sm">{t('map.mapViewMode')}</span>
                {viewMode === 'map' && (
                  <div className="absolute inset-0 bg-[#067977]/40 rounded-lg opacity-20 animate-pulse"></div>
                )}
              </Button>
            </div>
            
            {/* Mobile View mode toggle */}
            <div className="flex lg:hidden items-center gap-1 bg-gradient-to-r from-gray-50 via-gray-100 to-gray-50 rounded-lg p-1 shadow-lg border border-gray-200 backdrop-blur-sm">
              <Button
                variant={viewMode === 'list' ? 'default' : 'ghost'}
                size="sm"
                onClick={() => handleViewModeChange('list')}
                className={`relative transition-all duration-300 ease-in-out px-2 py-1.5 rounded-md ${
                  viewMode === 'list' 
                    ? 'bg-gradient-to-r from-[#067977] via-[#067977]/90 to-[#067977]/80 text-white shadow-lg ring-1 ring-[#067977]/30' 
                    : 'hover:bg-gradient-to-r hover:from-white hover:to-[#067977]/10 text-gray-700 hover:text-[#067977]'
                }`}
              >
                <List className="h-3 w-3" />
              </Button>
              <Button
                variant={viewMode === 'split' ? 'default' : 'ghost'}
                size="sm"
                onClick={() => handleViewModeChange('split')}
                className={`relative transition-all duration-300 ease-in-out px-2 py-1.5 rounded-md ${
                  viewMode === 'split' 
                    ? 'bg-gradient-to-r from-[#067977] via-[#067977]/90 to-[#067977]/80 text-white shadow-lg ring-1 ring-[#067977]/30' 
                    : 'hover:bg-gradient-to-r hover:from-white hover:to-[#067977]/10 text-gray-700 hover:text-[#067977]'
                }`}
              >
                <Grid className="h-3 w-3" />
              </Button>
              <Button
                variant={viewMode === 'map' ? 'default' : 'ghost'}
                size="sm"
                onClick={() => handleViewModeChange('map')}
                className={`relative transition-all duration-300 ease-in-out px-2 py-1.5 rounded-md ${
                  viewMode === 'map' 
                    ? 'bg-gradient-to-r from-[#067977] via-[#067977]/90 to-[#067977]/80 text-white shadow-lg ring-1 ring-[#067977]/30' 
                    : 'hover:bg-gradient-to-r hover:from-white hover:to-[#067977]/10 text-gray-700 hover:text-[#067977]'
                }`}
              >
                <MapPin className="h-3 w-3" />
              </Button>
            </div>
            
            {/* Filters are now always visible - no toggle button needed */}
          </div>
        </div>
      </div>
      

      
      {/* Main content with integrated filters */}
      <div className="flex-1 flex overflow-hidden">
        {/* Wider Filters Sidebar - Better visibility on small screens */}
        {viewMode !== 'map' && (
          <div className="w-full sm:w-72 lg:w-80 bg-white border-r border-gray-200 flex-shrink-0 overflow-y-auto hidden sm:block">
            <div className="p-1 sm:p-2">
              <h3 className="text-sm font-semibold text-gray-900 mb-1 sm:mb-2 flex items-center gap-1">
                <Filter className="h-3 w-3" />
                {t('map.filtersTitle')}
              </h3>
              <SearchFiltersComponent
                initialFilters={filters}
                onFiltersChange={handleFiltersChange}
                isLoadingCities={isLoadingCities}
                citiesError={citiesError}
                onRetryCities={retryLoadCities}
              />
            </div>
          </div>
        )}
        
        {/* Mobile Filters - Always visible and compact on small screens */}
        {viewMode !== 'map' && (
          <div className="sm:hidden w-full bg-white border-b border-gray-200">
            <div className="p-2">
              <details className="group" open>
                <summary className="flex items-center justify-between cursor-pointer list-none">
                  <h3 className="text-sm font-semibold text-gray-900 flex items-center gap-2">
                    <Filter className="h-3 w-3" />
                    {t('map.filtersTitle')}
                  </h3>
                  <ChevronDown className="h-3 w-3 transition-transform group-open:rotate-180" />
                </summary>
                <div className="mt-2">
                  <SearchFiltersComponent
                    initialFilters={filters}
                    onFiltersChange={handleFiltersChange}
                    isLoadingCities={isLoadingCities}
                    citiesError={citiesError}
                    onRetryCities={retryLoadCities}
                  />
                </div>
              </details>
            </div>
          </div>
        )}
        
        {/* List view */}
        {(viewMode === 'list' || viewMode === 'split') && (
          <div className={`${viewMode === 'split' ? 'w-1/2' : 'flex-1'} flex flex-col border-r`}>
            <div className="flex-1 overflow-y-auto p-1 sm:p-2">
              {loading || isLoadingProperties ? (
                <div className="flex flex-col items-center justify-center py-6">
                  <div className="relative">
                    <div className="w-10 h-10 border-4 border-blue-100 border-t-blue-600 rounded-full animate-spin"></div>
                    <div className="absolute inset-0 w-10 h-10 border-4 border-transparent border-r-blue-400 rounded-full animate-spin" style={{animationDirection: 'reverse', animationDuration: '1.5s'}}></div>
                  </div>
                  <div className="mt-3 text-center">
                    <h3 className="text-sm font-semibold text-gray-900 mb-1">{t('map.loadingProperties')}</h3>
                    <p className="text-xs text-gray-600">{t('map.pleaseWaitLoading')}</p>
                  </div>
                  <div className="mt-2 flex space-x-1 rtl:space-x-reverse">
                    <div className="w-1.5 h-1.5 bg-[#067977] rounded-full animate-bounce"></div>
                <div className="w-1.5 h-1.5 bg-[#067977] rounded-full animate-bounce" style={{animationDelay: '0.1s'}}></div>
                <div className="w-1.5 h-1.5 bg-[#067977] rounded-full animate-bounce" style={{animationDelay: '0.2s'}}></div>
                  </div>
                </div>
              ) : propertiesError ? (
                <ErrorDisplay 
                  error={propertiesError} 
                  onRetry={retryLoadProperties} 
                  type={t('common.properties')} 
                />
              ) : paginatedProperties.length > 0 ? (
                <div className="space-y-1 sm:space-y-2">
                  {paginatedProperties.map((property) => (
                    <Card
                      key={property.id}
                      className={`group cursor-pointer transition-all duration-300 ease-in-out transform hover:scale-[1.01] hover:shadow-lg hover:-translate-y-0.5 border-0 shadow-sm ${
                        selectedProperty?.id === property.id 
                          ? 'ring-2 ring-[#067977] shadow-[#067977]/20 bg-[#067977]/5' 
                          : 'hover:shadow-gray-200'
                      }`}
                      onClick={() => {
                        console.log('Property card clicked:', property.title);
                        handlePropertySelect(property);
                      }}
                    >
                      <CardContent className="p-0">
                        <div className="flex flex-col sm:flex-row gap-0">
                          <div className="w-full sm:w-20 h-16 sm:h-20 bg-gradient-to-br from-gray-100 to-gray-200 flex-shrink-0 relative overflow-hidden rounded-t-lg sm:rounded-t-none sm:rounded-l-lg">
                            {property.images && property.images.length > 0 ? (
                              <img
                                src={property.images[0]}
                                alt={property.title}
                                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                              />
                            ) : (
                              <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#067977]/10 to-[#067977]/20">
                                <Home className="h-6 w-6 sm:h-8 sm:w-8 text-[#067977]" />
                              </div>
                            )}
                            <div className="absolute top-1 right-1">
                              <Badge 
                                variant={property.listingType === 'sale' ? 'default' : 'secondary'}
                                className={`text-xs font-medium px-1.5 py-0.5 ${
                                  property.listingType === 'sale' 
                                    ? 'bg-green-500 text-white' 
                                    : 'bg-orange-500 text-white'
                                }`}
                              >
                                {property.listingType === 'sale' ? t('property.forSale') : t('property.forRent')}
                              </Badge>
                            </div>
                          </div>
                            <div className="flex-1 p-1.5 sm:p-2">
                            <div className="flex flex-col sm:flex-row items-start justify-between mb-1 gap-1 sm:gap-0">
                              <div className="flex-1">
                                <h3 className="font-bold text-xs sm:text-sm text-gray-900 mb-0.5 group-hover:text-[#067977] transition-colors duration-200">
                                  {property.title}
                                </h3>
                                <p className="text-gray-600 text-xs flex items-center gap-0.5">
                                  <MapPin className="h-2.5 w-2.5 text-gray-400 flex-shrink-0" />
                                  <span className="truncate">{property.address}</span>
                                </p>
                              </div>
                              <div className="text-left sm:text-right">
                                <span className="text-sm sm:text-lg font-bold text-[#067977]">
                                  ${typeof property.price === 'string' ? property.price : property.price?.toLocaleString()}
                                </span>
                                {property.listingType === 'rent' && (
                                  <p className="text-xs text-gray-500">{t('map.monthly')}</p>
                                )}
                              </div>
                            </div>
                            
                            <div className="flex flex-wrap items-center gap-1 sm:gap-2 text-xs text-gray-600 mb-2">
                              {property.bedrooms && (
                                <div className="flex items-center gap-1 bg-gray-50 px-2 py-1 rounded-full">
                                  <Home className="h-3 w-3 text-gray-500" />
                                  <span className="font-medium">{property.bedrooms} {t('map.rooms')}</span>
                                </div>
                              )}
                              {property.bathrooms && (
                                <div className="flex items-center gap-1 bg-gray-50 px-2 py-1 rounded-full">
                                  <Home className="h-3 w-3 text-gray-500" />
                                  <span className="font-medium">{property.bathrooms} {t('map.bathroom')}</span>
                                </div>
                              )}
                              {property.squareFootage && (
                                <div className="flex items-center gap-1 bg-gray-50 px-2 py-1 rounded-full">
                                  <Home className="h-3 w-3 text-gray-500" />
                                  <span className="font-medium">{property.squareFootage}م²</span>
                                </div>
                              )}
                            </div>
                            
                            <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-0">
                              <div className="flex items-center gap-1 sm:gap-2 flex-wrap">
                                {property.features && property.features.slice(0, 2).map((feature, index) => (
                                  <Badge key={index} variant="outline" className="text-xs bg-[#067977]/10 text-[#067977] border-[#067977]/30 px-2 py-1">
                                    {feature}
                                  </Badge>
                                ))}
                                {property.features && property.features.length > 2 && (
                                  <Badge variant="outline" className="text-xs text-gray-500 px-2 py-1">
                                    +{property.features.length - 2}
                                  </Badge>
                                )}
                              </div>
                              <Button 
                                variant="ghost" 
                                size="sm" 
                                className="text-[#067977] hover:text-white hover:bg-[#067977] border border-[#067977]/30 hover:border-[#067977] transition-all duration-300 ease-in-out transform hover:scale-105 hover:shadow-lg text-xs sm:text-sm px-2 sm:px-3 py-1 sm:py-2 font-medium"
                                onClick={(e) => {
                                  e.stopPropagation();
                                  console.log('Navigating to property details:', property.id);
                                  navigate(`/property/${property.id}`);
                                }}
                              >
                                {t('map.viewDetails')}
                                <Eye className="h-3 w-3 sm:h-4 sm:w-4 mr-1" />
                              </Button>
                            </div>
                          </div>
                        </div>
                      </CardContent>
                    </Card>
                  ))}
                </div>
              ) : (
                <div className="text-center py-8">
                  <Home className="h-12 w-12 mx-auto mb-4 opacity-50" />
                  <p className="text-gray-500">{t('map.noProperties')}</p>
                </div>
              )}
            </div>
            
            {/* Compact Pagination */}
            {totalPages > 1 && (
              <div className="border-t p-1 sm:p-2 flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-0">
                <div className="flex items-center gap-0.5 bg-white rounded-md border border-gray-200 shadow-sm p-0.5">
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={() => startTransition(() => setCurrentPage(prev => Math.max(1, prev - 1)))}
                    disabled={currentPage === 1}
                    className="h-6 w-6 p-0 hover:bg-[#067977]/10 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 ease-in-out transform hover:scale-105"
                  >
                    <X className="h-2.5 w-2.5" />
                  </Button>
                  
                  <div className="flex items-center gap-1 mx-1 sm:mx-2">
                    {Array.from({ length: Math.min(window.innerWidth < 640 ? 3 : 5, totalPages) }, (_, i) => {
                      const maxVisible = window.innerWidth < 640 ? 3 : 5;
                      const pageNum = currentPage <= Math.ceil(maxVisible / 2) 
                        ? i + 1 
                        : currentPage >= totalPages - Math.floor(maxVisible / 2) 
                          ? totalPages - (maxVisible - 1) + i 
                          : currentPage - Math.floor(maxVisible / 2) + i;
                      
                      if (pageNum < 1 || pageNum > totalPages) return null;
                      
                      return (
                        <Button
                          key={pageNum}
                          variant={currentPage === pageNum ? 'default' : 'ghost'}
                          size="sm"
                          onClick={() => startTransition(() => setCurrentPage(pageNum))}
                          className={`h-8 w-8 sm:h-10 sm:w-10 p-0 font-medium transition-all duration-300 ease-in-out transform hover:scale-105 text-xs sm:text-sm ${
                            currentPage === pageNum 
                              ? 'bg-[#067977] text-white shadow-md hover:bg-[#067977]/90 hover:shadow-lg'
                    : 'hover:bg-[#067977]/10 text-gray-700 hover:shadow-md'
                          }`}
                        >
                          {pageNum}
                        </Button>
                      );
                    })}
                  </div>
                  
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={() => startTransition(() => setCurrentPage(prev => Math.min(totalPages, prev + 1)))}
                    disabled={currentPage === totalPages}
                    className="h-8 w-8 sm:h-10 sm:w-10 p-0 hover:bg-[#067977]/10 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 ease-in-out transform hover:scale-105"
                  >
                    <X className="h-3 w-3 sm:h-4 sm:w-4" />
                  </Button>
                </div>
                
                <div className="sm:mr-4 text-xs sm:text-sm text-gray-600 text-center sm:text-left">
                  {t('map.page')} {currentPage} {t('map.of')} {totalPages}
                </div>
              </div>
            )}
          </div>
        )}
        
        {/* Map view */}
        {(viewMode === 'map' || viewMode === 'split') && (
          <div className={`${viewMode === 'split' ? 'w-1/2' : viewMode === 'map' ? 'w-full h-full' : 'flex-1'} relative`}>
            {/* Compact Location and Exit Full Screen buttons */}
            <div className="absolute top-2 right-2 z-[1000] flex gap-1">
              {isMapFullScreen && (
                <Button
                  onClick={() => setIsMapFullScreen(false)}
                  className="bg-red-500 hover:bg-red-600 text-white border-0 shadow-md transition-all duration-200 hover:shadow-lg text-xs px-2 py-1"
                  size="sm"
                >
                  <X className="h-3 w-3 mr-1" />
                  <span className="font-medium hidden sm:inline">{t('map.closeFullScreen')}</span>
                </Button>
              )}
              <Button
                onClick={getCurrentLocation}
                disabled={isLocating}
                className="bg-[#067977] hover:bg-[#067977]/90 text-white border-0 shadow-md transition-all duration-200 hover:shadow-lg text-xs px-2 py-1"
                size="sm"
              >
                {isLocating ? (
                  <Loader2 className="h-3 w-3 animate-spin mr-1" />
                ) : (
                  <Navigation className="h-3 w-3 mr-1" />
                )}
                <span className="font-medium hidden sm:inline">
                  {isLocating ? t('map.locatingYou') : t('map.getCurrentLocation')}
                </span>
              </Button>
            </div>
            
            {loadError ? (
              <div className="flex items-center justify-center h-full bg-gray-50">
                <div className="text-center">
                  <MapPin className="h-12 w-12 mx-auto mb-4 opacity-50" />
                  <p className="text-gray-500 mb-4">{t('map.loadError')}</p>
                </div>
              </div>
            ) : !isMapLoaded ? (
              <div className="flex items-center justify-center h-full bg-gray-50">
                <div className="text-center">
                  <MapPin className="h-12 w-12 mx-auto mb-2 opacity-50" />
                  <div>{t('common.loading')}</div>
                </div>
              </div>
            ) : (
              <MapContainer
                center={mapCenter}
                zoom={mapZoom}
                style={{ height: '100%', width: '100%' }}
                maxBounds={SYRIA_BOUNDS}
                maxBoundsViscosity={1.0}
              >
                <TileLayer
                  url={OSM_TILE_LAYER.url}
                  attribution={OSM_TILE_LAYER.attribution}
                />
                
                <MapEventHandler onBoundsChange={handleBoundsChange} onMapClick={handleMapClick} />
                <MapController selectedProperty={selectedProperty} />
                <MapResizeHandler viewMode={viewMode} />
                
                {renderMarkers()}
                
                {/* User location marker */}
                {userLocation && (
                  <Marker
                    position={userLocation}
                    icon={L.divIcon({
                      className: 'user-location-marker',
                      html: `
                        <div style="
                          position: relative;
                          width: 24px;
                          height: 24px;
                        ">
                          <div style="
                            width: 24px;
                            height: 24px;
                            background: #2563eb;
                            border: 4px solid white;
                            border-radius: 50%;
                            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
                            animation: pulse 2s infinite;
                          "></div>
                          <div style="
                            position: absolute;
                            top: -8px;
                            left: -8px;
                            width: 40px;
                            height: 40px;
                            background: rgba(37, 99, 235, 0.2);
                            border-radius: 50%;
                            animation: ripple 2s infinite;
                          "></div>
                        </div>
                        <style>
                          @keyframes pulse {
                            0% { transform: scale(1); }
                            50% { transform: scale(1.1); }
                            100% { transform: scale(1); }
                          }
                          @keyframes ripple {
                            0% { transform: scale(0.8); opacity: 1; }
                            100% { transform: scale(2); opacity: 0; }
                          }
                        </style>
                      `,
                      iconSize: [40, 40],
                      iconAnchor: [20, 20]
                    })}
                  >
                    <Popup>
                      <div className="text-center p-2">
                        <div className="flex items-center justify-center mb-2">
                          <Navigation className="h-5 w-5 text-[#067977] ml-2" />
                <strong className="text-[#067977]">{t('map.yourCurrentLocation')}</strong>
                        </div>
                        <p className="text-sm text-gray-600">{t('map.locationAccurate')}</p>
                      </div>
                    </Popup>
                  </Marker>
                )}
              </MapContainer>
            )}
          </div>
        )}
      </div>
    </div>
    </ErrorBoundary>
  );
};

export default MapSearchView;