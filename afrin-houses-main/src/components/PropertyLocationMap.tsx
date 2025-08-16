import React, { useState, useCallback, useRef, useEffect } from 'react';
import { GoogleMap, LoadScript, Marker, Autocomplete } from '@react-google-maps/api';
import { useTranslation } from 'react-i18next';
import { MapPin, Search, RotateCcw } from 'lucide-react';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { toast } from 'sonner';

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

const GOOGLE_MAPS_API_KEY = 'AIzaSyCO0kKndUNlmQi3B5mxy4dblg_8WYcuKuk';

// Google Maps libraries to load
const libraries: ("places" | "geometry" | "drawing" | "visualization")[] = ["places"];

// Default center (Damascus, Syria)
const DEFAULT_CENTER = {
  lat: 33.5138,
  lng: 36.2765
};

const mapContainerStyle = {
  width: '100%',
  height: '400px',
  borderRadius: '8px'
};

const mapOptions = {
  disableDefaultUI: false,
  zoomControl: true,
  streetViewControl: false,
  mapTypeControl: false,
  fullscreenControl: false,
};

const PropertyLocationMap: React.FC<PropertyLocationMapProps> = ({
  onLocationChange,
  initialCoordinates,
  initialAddress,
  className = ''
}) => {
  const { t } = useTranslation();
  
  // Map state
  const [map, setMap] = useState<google.maps.Map | null>(null);
  const [marker, setMarker] = useState<{lat: number, lng: number} | null>(
    initialCoordinates && initialCoordinates.lat !== undefined && initialCoordinates.lng !== undefined
      ? { lat: initialCoordinates.lat, lng: initialCoordinates.lng }
      : null
  );
  const [searchValue, setSearchValue] = useState(initialAddress || '');
  const [isLoaded, setIsLoaded] = useState(false);
  
  // Autocomplete refs
  const autocompleteRef = useRef<google.maps.places.Autocomplete | null>(null);
  const searchInputRef = useRef<HTMLInputElement>(null);

  // Geocoder for reverse geocoding
  const geocoderRef = useRef<google.maps.Geocoder | null>(null);

  // Initialize geocoder when maps API is loaded
  useEffect(() => {
    if (isLoaded && window.google) {
      geocoderRef.current = new window.google.maps.Geocoder();
    }
  }, [isLoaded]);

  // Handle map load
  const onLoad = useCallback((map: google.maps.Map) => {
    setMap(map);
    setIsLoaded(true);
  }, []);

  // Handle map unmount
  const onUnmount = useCallback(() => {
    setMap(null);
    setIsLoaded(false);
  }, []);

  // Handle autocomplete load
  const onAutocompleteLoad = useCallback((autocomplete: google.maps.places.Autocomplete) => {
    autocompleteRef.current = autocomplete;
  }, []);

  // Handle place selection from autocomplete
  const onPlaceChanged = useCallback(() => {
    if (autocompleteRef.current) {
      const place = autocompleteRef.current.getPlace();
      
      if (place.geometry && place.geometry.location) {
        const lat = place.geometry.location.lat();
        const lng = place.geometry.location.lng();
        const address = place.formatted_address || '';
        
        setMarker({ lat, lng });
        setSearchValue(address);
        
        // Center map on selected location
        if (map) {
          map.panTo({ lat, lng });
          map.setZoom(16);
        }
        
        // Notify parent component
        onLocationChange({
          latitude: lat,
          longitude: lng,
          address
        });
        
        toast.success(t('map.locationSelected'));
      }
    }
  }, [map, onLocationChange, t]);

  // Handle map click to place marker
  const onMapClick = useCallback((event: google.maps.MapMouseEvent) => {
    if (event.latLng) {
      const lat = event.latLng.lat();
      const lng = event.latLng.lng();
      
      setMarker({ lat, lng });
      
      // Reverse geocode to get address
      if (geocoderRef.current) {
        geocoderRef.current.geocode(
          { location: { lat, lng } },
          (results, status) => {
            if (status === 'OK' && results && results[0]) {
              const address = results[0].formatted_address;
              setSearchValue(address);
              
              // Notify parent component
              onLocationChange({
                latitude: lat,
                longitude: lng,
                address
              });
            } else {
              // Notify parent component without address
              onLocationChange({
                latitude: lat,
                longitude: lng
              });
            }
          }
        );
      } else {
        // Notify parent component without address
        onLocationChange({
          latitude: lat,
          longitude: lng
        });
      }
      
      toast.success(t('map.markerPlaced'));
    }
  }, [onLocationChange, t]);

  // Reset to default location
  const resetToDefault = useCallback(() => {
    setMarker(null);
    setSearchValue('');
    
    if (map) {
      map.panTo(DEFAULT_CENTER);
      map.setZoom(12);
    }
    
    toast.info(t('map.locationReset'));
  }, [map, t]);

  // Get current location
  const getCurrentLocation = useCallback(() => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          
          setMarker({ lat, lng });
          
          if (map) {
            map.panTo({ lat, lng });
            map.setZoom(16);
          }
          
          // Reverse geocode to get address
          if (geocoderRef.current) {
            geocoderRef.current.geocode(
              { location: { lat, lng } },
              (results, status) => {
                if (status === 'OK' && results && results[0]) {
                  const address = results[0].formatted_address;
                  setSearchValue(address);
                  
                  onLocationChange({
                    latitude: lat,
                    longitude: lng,
                    address
                  });
                } else {
                  onLocationChange({
                    latitude: lat,
                    longitude: lng
                  });
                }
              }
            );
          } else {
            onLocationChange({
              latitude: lat,
              longitude: lng
            });
          }
          
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
  }, [map, onLocationChange, t]);

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
                <LoadScript
                  googleMapsApiKey={GOOGLE_MAPS_API_KEY}
                  libraries={libraries}
                >
                  <Autocomplete
                    onLoad={onAutocompleteLoad}
                    onPlaceChanged={onPlaceChanged}
                  >
                    <Input
                      ref={searchInputRef}
                      id="location-search"
                      type="text"
                      placeholder={t('map.searchPlaceholder')}
                      value={searchValue}
                      onChange={(e) => setSearchValue(e.target.value)}
                      className="w-full"
                    />
                  </Autocomplete>
                </LoadScript>
              </div>
              <Button
                type="button"
                variant="outline"
                size="sm"
                onClick={getCurrentLocation}
                title={t('map.useCurrentLocation')}
              >
                <Search className="h-4 w-4" />
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
            <LoadScript
              googleMapsApiKey={GOOGLE_MAPS_API_KEY}
              libraries={libraries}
            >
              <GoogleMap
                mapContainerStyle={mapContainerStyle}
                center={marker || DEFAULT_CENTER}
                zoom={marker ? 16 : 12}
                onLoad={onLoad}
                onUnmount={onUnmount}
                onClick={onMapClick}
                options={mapOptions}
              >
                {marker && (
                  <Marker
                    position={marker}
                    draggable={true}
                    onDragEnd={(event) => {
                      if (event.latLng) {
                        const lat = event.latLng.lat();
                        const lng = event.latLng.lng();
                        
                        setMarker({ lat, lng });
                        
                        // Reverse geocode to get address
                        if (geocoderRef.current) {
                          geocoderRef.current.geocode(
                            { location: { lat, lng } },
                            (results, status) => {
                              if (status === 'OK' && results && results[0]) {
                                const address = results[0].formatted_address;
                                setSearchValue(address);
                                
                                onLocationChange({
                                  latitude: lat,
                                  longitude: lng,
                                  address
                                });
                              } else {
                                onLocationChange({
                                  latitude: lat,
                                  longitude: lng
                                });
                              }
                            }
                          );
                        } else {
                          onLocationChange({
                            latitude: lat,
            longitude: lng
                          });
                        }
                      }
                    }}
                  />
                )}
              </GoogleMap>
            </LoadScript>
          </div>

          {/* Coordinates Display */}
          {marker && (
            <div className="grid grid-cols-2 gap-4 p-3 bg-gray-50 rounded-lg">
              <div>
                <Label className="text-sm text-gray-600">{t('map.latitude')}</Label>
                <p className="font-mono text-sm">{marker.lat.toFixed(6)}</p>
              </div>
              <div>
                <Label className="text-sm text-gray-600">{t('map.longitude')}</Label>
                <p className="font-mono text-sm">{marker.lng.toFixed(6)}</p>
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