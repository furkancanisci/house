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
    const cityExists = cities.some(city => {
      const cityDisplayName = locale === 'ar' && city.name_ar ? city.name_ar : (city.name_en || city.name);
      const match = city.name === value || cityDisplayName === value;
      console.log(`LocationSelector: Checking city '${city.name}' (display: '${cityDisplayName}') against '${value}': ${match}`);
      return match;
    });
    console.log('LocationSelector: Does selected city exist in options?', cityExists);
    
    // If city doesn't exist in options, log warning but still proceed
    if (!cityExists && cities.length > 0) {
      console.warn('LocationSelector: Selected city not found in available cities:', {
        selectedCity: value,
        availableCities: cities.map(c => {
          const displayName = locale === 'ar' && c.name_ar ? c.name_ar : (c.name_en || c.name);
          return { original: c.name, display: displayName };
        }),
        citiesCount: cities.length
      });
    }
    
    // Always update internal state first to ensure UI responsiveness
    if (propOnCityChange) {
      console.log('LocationSelector: Calling propOnCityChange with:', value);
      propOnCityChange(value);
    } else {
      console.log('LocationSelector: Updating internal city state to:', value);
      setInternalCity(value);
    }
    
    // Also notify via onLocationChange if provided
    if (onLocationChange) {
      console.log('LocationSelector: Calling onLocationChange with state and city');
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
        console.error('Error loading states:', error);
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
      console.error('Error loading cities:', error);
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
        console.log('LocationSelector: State changed, checking if city should be cleared');
        console.log('LocationSelector: Current state:', selectedState, 'Current city:', selectedCity);
        
        // Only clear the city if we're confident it's a new state selection
        // (not just a re-render with the same state)
        const shouldClearCity = selectedCity && cities.length === 0; // Only clear if no cities are loaded yet
        
        if (shouldClearCity) {
          if (propOnCityChange) {
            propOnCityChange('');
          } else {
            setInternalCity('');
          }
        } else {
          console.log('LocationSelector: Preserving city selection:', selectedCity);
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
    if (cities.length > 0 && selectedCity) {
      // Check if selectedCity exists in either name format
      const cityExists = cities.some(city => {
        const cityDisplayName = locale === 'ar' && city.name_ar ? city.name_ar : (city.name_en || city.name);
        return city.name === selectedCity || cityDisplayName === selectedCity;
      });
      
      console.log('LocationSelector: Verifying selected city after cities loaded:');
      console.log('LocationSelector: Selected city:', selectedCity);
      console.log('LocationSelector: Available cities:', cities.map(c => {
        const displayName = locale === 'ar' && c.name_ar ? c.name_ar : (c.name_en || c.name);
        return { original: c.name, display: displayName };
      }));
      console.log('LocationSelector: City exists in list:', cityExists);
      
      if (!cityExists) {
        console.warn('LocationSelector: Selected city not found in loaded cities, clearing selection');
        if (propOnCityChange) {
          propOnCityChange('');
        } else {
          setInternalCity('');
        }
      } else {
        console.log('LocationSelector: Selected city is valid, preserving selection');
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
          <div className="space-y-2">
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
          <div className="space-y-2">
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
                      console.log('LocationSelector SelectValue: Displaying selectedCity:', normalizedSelectedCity);
                      return (
                        <span className="font-medium text-gray-800">
                          {normalizedSelectedCity}
                        </span>
                      );
                    } else {
                      const placeholderText = selectedState 
                        ? t('location.selectCity')
                        : t('location.selectStateFirst');
                      console.log('LocationSelector SelectValue: Displaying placeholder:', placeholderText);
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
                  console.log('LocationSelector: Rendering SelectContent with cities:', cities.length);
                  console.log('LocationSelector: Cities data:', cities.map(c => ({ id: c.id, name: c.name, state: c.state })));
                  
                  return cities.map((city) => {
                    console.log('LocationSelector: Rendering city option:', {
                      id: city.id,
                      name: city.name,
                      name_ar: city.name_ar,
                      name_en: city.name_en,
                      state: city.state
                    });
                    
                    // Use the same logic for both display and value to ensure consistency
                    const cityDisplayName = locale === 'ar' && city.name_ar ? city.name_ar : (city.name_en || city.name);
                    const cityValue = cityDisplayName; // Use display name as value to maintain consistency
                    
                    console.log('LocationSelector: City display name for', city.name, ':', cityDisplayName);
                    
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
                  });
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