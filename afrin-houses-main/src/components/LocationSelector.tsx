import React, { useState, useEffect, useCallback, FC } from 'react';
import { useTranslation } from 'react-i18next';
import { Globe } from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { toast } from 'sonner';
import { cityService } from '@/services/cityService';

type LoadingState = {
  states: boolean;
  cities: boolean;
};

interface City {
  id: number | string;
  name: string;
  state: string;
  name_ar?: string;
  name_en?: string;
}

interface LocationSelectorProps {
  onLocationChange?: (location: { state?: string; city?: string }) => void;
  selectedState?: string;
  selectedCity?: string;
  onStateChange?: (value: string) => void;
  onCityChange?: (value: string) => void;
  initialState?: string;
  initialCity?: string;
  showState?: boolean;
  showCity?: boolean;
  className?: string;
}

const LocationSelector: FC<LocationSelectorProps> = ({
  onLocationChange,
  selectedState: propSelectedState,
  selectedCity: propSelectedCity,
  onStateChange: propOnStateChange,
  onCityChange: propOnCityChange,
  initialState,
  initialCity,
  showState = true,
  showCity = true,
  className = ''
}) => {
  const { t, i18n } = useTranslation();
  const { language: locale } = i18n;

  // Set default values: Damascus as state
  const getDefaultState = () => {
    if (initialState) return initialState;
    return locale === 'ar' ? 'دمشق' : 'Damascus';
  };

  // State for internal management
  const [internalState, setInternalState] = useState<string>(getDefaultState());
  const [internalCity, setInternalCity] = useState<string>(initialCity || '');
  const [states, setStates] = useState<string[]>([]);
  const [cities, setCities] = useState<City[]>([]);
  const [loading, setLoading] = useState({
    states: false,
    cities: false
  });

  // Use controlled values if provided, otherwise use internal state
  const selectedState = propSelectedState !== undefined ? propSelectedState : internalState;
  const selectedCity = propSelectedCity !== undefined ? propSelectedCity : internalCity;
  
  // Ensure selectedCity is always a string
  const normalizedSelectedCity = selectedCity || t('location.selectCity');
  // Handle changes with callbacks if provided, otherwise update internal state
  const onStateChangeHandler = useCallback((value: string) => {
    // Prevent unnecessary updates if value hasn't changed
    if (selectedState === value) return;
    
    if (propOnStateChange) {
      propOnStateChange(value);
    } else {
      setInternalState(value);
    }
    // Don't call onLocationChange here to prevent infinite loops
    // The parent component will handle the location change through onStateChange
  }, [propOnStateChange, selectedState]);

  const onCityChangeHandler = useCallback((value: string) => {
    // Prevent unnecessary updates if value hasn't changed
    if (selectedCity === value) {
      return;
    }
    // Verify the city exists in our options (check both display name and original name)
    const cityExists = cities && cities.length > 0 ? cities.some(city => {
      const cityDisplayName = locale === 'ar' && city.name_ar ? city.name_ar : (city.name_en || city.name);
      const match = city.name === value || cityDisplayName === value;
      return match;
    }) : false;

    
    // If city doesn't exist in options, proceed silently
    if (!cityExists && cities && cities.length > 0) {
      // City not found in available options
    }
    
    // Always update internal state first to ensure UI responsiveness
    if (propOnCityChange) {
      propOnCityChange(value);
    } else {
      setInternalCity(value);
    }
    
    // Also notify via onLocationChange if provided
    if (onLocationChange) {
      onLocationChange({ state: selectedState, city: value });
    }
    
  }, [propOnCityChange, selectedCity, cities, onLocationChange, selectedState, locale]);

  // Load states on mount
  useEffect(() => {
    const loadStates = async () => {
      try {
        setLoading(prev => ({ ...prev, states: true }));
        const data = await cityService.getStates();
        setStates(data);
      } catch (error) {
        // Error loading states
      } finally {
        setLoading(prev => ({ ...prev, states: false }));
      }
    };

    loadStates();
  }, []);

  // Load cities when state changes
  const loadCities = useCallback(async (state: string) => {
    if (!state) return;
    
    try {
      setLoading(prev => ({ ...prev, cities: true }));
      // Use getCitiesByState instead of getCities to avoid rate limiting on 'all-cities'
      const data = await cityService.getCitiesByState(state);
      setCities(data);
    } catch (error) {
      // Error loading cities
      toast.error('خطأ في تحميل المدن. يرجى المحاولة مرة أخرى.');
      // Set empty array on error to prevent UI issues
      setCities([]);
    } finally {
      setLoading(prev => ({ ...prev, cities: false }));
    }
  }, []);



  // Load cities when state changes with debouncing to prevent rapid API calls
  useEffect(() => {
    if (selectedState && showCity) {
      // Add a small delay to prevent rapid API calls when state changes quickly
      const timeoutId = setTimeout(() => {
        loadCities(selectedState);
      }, 200); // Increased debounce time
      
      // Only reset city when state changes AND city is currently set
      // But preserve it if the user just selected a city for the current state
      if (selectedCity) {
        
        // Only clear the city if we're confident it's a new state selection
        // (not just a re-render with the same state)
        const shouldClearCity = selectedCity && (!cities || cities.length === 0); // Only clear if no cities are loaded yet
        
        if (shouldClearCity) {
          if (propOnCityChange) {
            propOnCityChange('');
          } else {
            setInternalCity('');
          }
        }
      }
      
      return () => clearTimeout(timeoutId);
    } else {
      // Clear cities if no state is selected
      setCities([]);
      // Also clear city selection only if no state is selected
      if (selectedCity && !selectedState) {
        if (propOnCityChange) {
          propOnCityChange('');
        } else {
          setInternalCity('');
        }
      }
    }
  }, [selectedState, showCity, propOnCityChange, loadCities]); // Removed selectedCity from dependencies to prevent loops

  // Verify selected city exists when cities are loaded
  useEffect(() => {
    if (cities && cities.length > 0 && selectedCity) {
      // Check if selectedCity exists in either name format
      const cityExists = cities.some(city => {
        const cityDisplayName = locale === 'ar' && city.name_ar ? city.name_ar : (city.name_en || city.name);
        return city.name === selectedCity || cityDisplayName === selectedCity;
      });
      

      
      if (!cityExists) {
        if (propOnCityChange) {
          propOnCityChange('');
        } else {
          setInternalCity('');
        }
      }
    }
  }, [cities, selectedCity, propOnCityChange, locale]);

  const handleStateChange = (value: string) => {
    onStateChangeHandler(value);
    // City will be reset by useEffect when selectedState changes
  };

  return (
    <div className={cn('space-y-4', className)}>
      <div className="space-y-4">
        {showState && (
          <div className="space-y-2" data-field="state">
            <Label htmlFor="state" className="text-sm font-semibold text-gray-700">
              {t('location.state')}
            </Label>
            <Select
              value={selectedState}
              onValueChange={handleStateChange}
              disabled={loading.states}
            >
              <SelectTrigger className="h-12 text-sm border-2 rounded-xl bg-gradient-to-r from-[#067977]/10 to-[#067977]/5 border-gray-200 hover:border-purple-500 focus:border-[#067977] focus:ring-2 focus:ring-[#067977]/20 transition-all duration-200">
                <SelectValue 
                  placeholder={t('location.selectState')} 
                  className="text-gray-700 font-medium"
                />
              </SelectTrigger>
              <SelectContent className="bg-white border-2 border-gray-100 rounded-xl shadow-lg">
                {states.map((state) => (
                  <SelectItem 
                    key={state} 
                    value={state}
                    className="text-sm py-3 px-4 hover:bg-[#067977]/10 focus:bg-[#067977]/20 transition-colors duration-150 cursor-pointer"
                  >
                    <span className="font-medium text-gray-800">{state}</span>
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        )}

        {showCity && (
          <div className="space-y-2" data-field="city">
            <Label htmlFor="city" className="text-sm font-semibold text-gray-700">
              {t('location.city')}
            </Label>
            <Select
              key={`city-select-${selectedState}`}
              value={normalizedSelectedCity}
              onValueChange={(value) => {
                onCityChangeHandler(value);
              }}
              disabled={!selectedState || loading.cities}
            >
              <SelectTrigger className="h-12 text-sm border-2 rounded-xl bg-gradient-to-r from-[#067977]/10 to-[#067977]/5 border-gray-200 hover:border-purple-500 focus:border-[#067977] focus:ring-2 focus:ring-[#067977]/20 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                <SelectValue>
                  {(() => {
                    if (normalizedSelectedCity) {
                      return (
                        <span className="font-medium text-gray-800">
                          {normalizedSelectedCity}
                        </span>
                      );
                    } else {
                      const placeholderText = selectedState 
                        ? t('location.selectCity')
                        : t('location.selectStateFirst');
                      return (
                        <span className="text-gray-500">
                          {placeholderText}
                        </span>
                      );
                    }
                  })()} 
                </SelectValue>
              </SelectTrigger>
              <SelectContent className="bg-white border-2 border-gray-100 rounded-xl shadow-lg">
                {(() => {
                  return cities && cities.length > 0 ? cities.map((city) => {
                    // Use the same logic for both display and value to ensure consistency
                    const cityDisplayName = locale === 'ar' && city.name_ar ? city.name_ar : (city.name_en || city.name);
                    const cityValue = cityDisplayName; // Use display name as value to maintain consistency
                    
                    return (
                      <SelectItem 
                        key={`city-${city.id}-${city.name}`} 
                        value={cityValue}
                        className="text-sm py-3 px-4 hover:bg-[#067977]/10 focus:bg-[#067977]/20 transition-colors duration-150 cursor-pointer"
                      >
                        <span className="font-medium text-gray-800">
                          {cityDisplayName}
                        </span>
                      </SelectItem>
                    );
                  }) : [];
                })()} 
              </SelectContent>
            </Select>
          </div>
        )}
      </div>
    </div>
  );
};

export default LocationSelector;