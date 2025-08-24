import React, { useState, useCallback, useRef, useEffect } from 'react';
import { MapContainer, TileLayer, Marker, useMap, useMapEvents } from 'react-leaflet';
import { useTranslation } from 'react-i18next';
import { useLeafletMap, DEFAULT_CENTER, DEFAULT_ZOOM, OSM_TILE_LAYER } from '../context/LeafletMapProvider';
import { MapPin, Search, RotateCcw, Navigation } from 'lucide-react';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { toast } from 'sonner';
import L from 'leaflet';

interface PropertyLocationMapProps {
  onLocationChange: (location: {
    latitude: number;
    longitude: number;
    address?: string;
  }) => void;
  initialCoordinates?: {
    lat: number;
    lng: number;
  } | null;
  initialAddress?: string;
  className?: string;
}

// Map bounds for Syria region
const SYRIA_BOUNDS: [[number, number], [number, number]] = [
  [32.0, 35.5], // Southwest
  [37.5, 42.5]  // Northeast
];

// Custom marker icon
const createLocationIcon = () => {
  return L.divIcon({
    html: `
      <div style="
        background-color: #ef4444;
        width: 24px;
        height: 24px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
      ">
        <div style="
          width: 8px;
          height: 8px;
          background-color: white;
          border-radius: 50%;
          transform: rotate(45deg);
        "></div>
      </div>
    `,
    className: 'custom-location-marker',
    iconSize: [24, 24],
    iconAnchor: [12, 24],
    popupAnchor: [0, -24]
  });
};

// Component to handle map events
const MapEventHandler: React.FC<{
  onMapClick: (lat: number, lng: number) => void;
}> = ({ onMapClick }) => {
  useMapEvents({
    click: (e) => {
      const { lat, lng } = e.latlng;
      onMapClick(lat, lng);
    }
  });
  
  return null;
};

// Component to control map center
const MapController: React.FC<{
  center: [number, number] | null;
  zoom: number;
}> = ({ center, zoom }) => {
  const map = useMap();
  
  useEffect(() => {
    if (center) {
      map.setView(center, zoom, { animate: true });
    }
  }, [center, zoom, map]);
  
  return null;
};

// Draggable marker component
const DraggableMarker: React.FC<{
  position: [number, number];
  onDragEnd: (lat: number, lng: number) => void;
}> = ({ position, onDragEnd }) => {
  const markerRef = useRef<L.Marker>(null);
  
  const eventHandlers = {
    dragend: () => {
      const marker = markerRef.current;
      if (marker) {
        const { lat, lng } = marker.getLatLng();
        onDragEnd(lat, lng);
      }
    }
  };
  
  return (
    <Marker
      ref={markerRef}
      position={position}
      icon={createLocationIcon()}
      draggable={true}
      eventHandlers={eventHandlers}
    />
  );
};

const PropertyLocationMap: React.FC<PropertyLocationMapProps> = ({
  onLocationChange,
  initialCoordinates,
  initialAddress,
  className = ''
}) => {
  const { t } = useTranslation();
  const { isLoaded: isMapLoaded, loadError } = useLeafletMap();
  
  // Map state
  const [marker, setMarker] = useState<[number, number] | null>(
    initialCoordinates && initialCoordinates.lat !== undefined && initialCoordinates.lng !== undefined
      ? [initialCoordinates.lat, initialCoordinates.lng]
      : null
  );
  const [searchValue, setSearchValue] = useState(initialAddress || '');
  const [mapCenter, setMapCenter] = useState<[number, number] | null>(null);
  const [mapZoom, setMapZoom] = useState(marker ? 16 : DEFAULT_ZOOM);
  
  // Refs
  const mapRef = useRef<L.Map | null>(null);
  
  // Reverse geocoding using Nominatim (OpenStreetMap)
  const reverseGeocode = useCallback(async (lat: number, lng: number): Promise<string | null> => {
    try {
      const response = await fetch(
        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
      );
      
      if (response.ok) {
        const data = await response.json();
        return data.display_name || null;
      }
    } catch (error) {
      console.error('Reverse geocoding error:', error);
    }
    
    return null;
  }, []);
  
  // Forward geocoding using Nominatim
  const forwardGeocode = useCallback(async (query: string): Promise<{lat: number, lng: number, address: string} | null> => {
    try {
      const response = await fetch(
        `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1&addressdetails=1&countrycodes=sy`
      );
      
      if (response.ok) {
        const data = await response.json();
        if (data.length > 0) {
          const result = data[0];
          return {
            lat: parseFloat(result.lat),
            lng: parseFloat(result.lon),
            address: result.display_name
          };
        }
      }
    } catch (error) {
      console.error('Forward geocoding error:', error);
    }
    
    return null;
  }, []);
  
  // Handle map click to place marker
  const onMapClick = useCallback(async (lat: number, lng: number) => {
    setMarker([lat, lng]);
    setMapCenter([lat, lng]);
    setMapZoom(16);
    
    // Reverse geocode to get address
    const address = await reverseGeocode(lat, lng);
    if (address) {
      setSearchValue(address);
    }
    
    // Notify parent component
    onLocationChange({
      latitude: lat,
      longitude: lng,
      address: address || undefined
    });
    
    toast.success(t('map.markerPlaced'));
  }, [onLocationChange, reverseGeocode, t]);
  
  // Handle marker drag
  const onMarkerDragEnd = useCallback(async (lat: number, lng: number) => {
    setMarker([lat, lng]);
    
    // Reverse geocode to get address
    const address = await reverseGeocode(lat, lng);
    if (address) {
      setSearchValue(address);
    }
    
    // Notify parent component
    onLocationChange({
      latitude: lat,
      longitude: lng,
      address: address || undefined
    });
    
    toast.success(t('map.locationUpdated'));
  }, [onLocationChange, reverseGeocode, t]);
  
  // Handle search
  const handleSearch = useCallback(async () => {
    if (!searchValue.trim()) {
      toast.error(t('map.enterSearchTerm'));
      return;
    }
    
    const result = await forwardGeocode(searchValue.trim());
    if (result) {
      setMarker([result.lat, result.lng]);
      setMapCenter([result.lat, result.lng]);
      setMapZoom(16);
      setSearchValue(result.address);
      
      // Notify parent component
      onLocationChange({
        latitude: result.lat,
        longitude: result.lng,
        address: result.address
      });
      
      toast.success(t('map.locationFound'));
    } else {
      toast.error(t('map.locationNotFound'));
    }
  }, [searchValue, forwardGeocode, onLocationChange, t]);
  
  // Handle search on Enter key
  const handleKeyPress = useCallback((e: React.KeyboardEvent) => {
    if (e.key === 'Enter') {
      handleSearch();
    }
  }, [handleSearch]);
  
  // Reset to default location
  const resetToDefault = useCallback(() => {
    setMarker(null);
    setSearchValue('');
    setMapCenter(DEFAULT_CENTER);
    setMapZoom(DEFAULT_ZOOM);
    
    toast.info(t('map.locationReset'));
  }, [t]);
  
  // Get current location
  const getCurrentLocation = useCallback(() => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        async (position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          
          setMarker([lat, lng]);
          setMapCenter([lat, lng]);
          setMapZoom(16);
          
          // Reverse geocode to get address
          const address = await reverseGeocode(lat, lng);
          if (address) {
            setSearchValue(address);
          }
          
          onLocationChange({
            latitude: lat,
            longitude: lng,
            address: address || undefined
          });
          
          toast.success(t('map.currentLocationFound'));
        },
        (error) => {
          console.error('Error getting current location:', error);
          toast.error(t('map.currentLocationError'));
        }
      );
    } else {
      toast.error(t('map.geolocationNotSupported'));
    }
  }, [onLocationChange, reverseGeocode, t]);
  
  return (
    <div className={`space-y-4 ${className}`}>
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <MapPin className="h-5 w-5" />
            {t('map.selectPropertyLocation')}
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          {/* Search and Controls */}
          <div className="space-y-3">
            <Label htmlFor="location-search">{t('map.searchLocation')}</Label>
            <div className="flex gap-2">
              <div className="flex-1">
                <Input
                  id="location-search"
                  type="text"
                  placeholder={t('map.searchPlaceholder')}
                  value={searchValue}
                  onChange={(e) => setSearchValue(e.target.value)}
                  onKeyPress={handleKeyPress}
                  className="w-full"
                />
              </div>
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={handleSearch}
                title={t('map.searchLocation')}
              >
                <Search className="h-4 w-4" />
              </Button>
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={getCurrentLocation}
                title={t('map.useCurrentLocation')}
              >
                <Navigation className="h-4 w-4" />
              </Button>
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={resetToDefault}
                title={t('map.resetLocation')}
              >
                <RotateCcw className="h-4 w-4" />
              </Button>
            </div>
          </div>
          
          {/* Map */}
          <div className="relative">
            {loadError ? (
              <div className="h-64 flex items-center justify-center bg-gray-100 rounded-lg">
                <div className="text-red-500">Failed to load map</div>
              </div>
            ) : !isMapLoaded ? (
              <div className="h-64 flex items-center justify-center bg-gray-100 rounded-lg">
                <div>Loading map...</div>
              </div>
            ) : (
              <div className="h-96 rounded-lg overflow-hidden border">
                <MapContainer
                  center={mapCenter || marker || DEFAULT_CENTER}
                  zoom={mapZoom}
                  style={{ height: '100%', width: '100%' }}
                  maxBounds={SYRIA_BOUNDS}
                  maxBoundsViscosity={1.0}
                  ref={mapRef}
                >
                  <TileLayer
                    url={OSM_TILE_LAYER.url}
                    attribution={OSM_TILE_LAYER.attribution}
                  />
                  
                  <MapEventHandler onMapClick={onMapClick} />
                  <MapController center={mapCenter} zoom={mapZoom} />
                  
                  {marker && (
                    <DraggableMarker
                      position={marker}
                      onDragEnd={onMarkerDragEnd}
                    />
                  )}
                </MapContainer>
              </div>
            )}
          </div>
          
          {/* Coordinates Display */}
          {marker && (
            <div className="grid grid-cols-2 gap-4 p-3 bg-gray-50 rounded-lg">
              <div>
                <Label className="text-sm text-gray-600">{t('map.latitude')}</Label>
                <p className="font-mono text-sm">{marker[0].toFixed(6)}</p>
              </div>
              <div>
                <Label className="text-sm text-gray-600">{t('map.longitude')}</Label>
                <p className="font-mono text-sm">{marker[1].toFixed(6)}</p>
              </div>
            </div>
          )}
          
          {/* Instructions */}
          <div className="text-sm text-gray-600 space-y-1">
            <p>• {t('map.clickToPlace')}</p>
            <p>• {t('map.dragToMove')}</p>
            <p>• {t('map.searchToFind')}</p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default PropertyLocationMap;