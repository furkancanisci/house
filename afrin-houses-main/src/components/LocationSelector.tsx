import React, { useState, useEffect, FC } from 'react';
import { useTranslation } from 'react-i18next';
import { Globe } from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
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

  // Handle changes with callbacks if provided, otherwise update internal state
  const onStateChangeHandler = (value: string) => {
    if (propOnStateChange) {
      propOnStateChange(value);
    } else {
      setInternalState(value);
    }
    if (onLocationChange) {
      onLocationChange({ state: value });
    }
  };

  const onCityChangeHandler = (value: string) => {
    if (propOnCityChange) {
      propOnCityChange(value);
    } else {
      setInternalCity(value);
    }
    if (onLocationChange) {
      onLocationChange({ state: selectedState, city: value });
    }
  };

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
  const loadCities = async (state: string) => {
    if (!state) return;
    
    try {
      setLoading(prev => ({ ...prev, cities: true }));
      const data = await cityService.getCities({ state });
      setCities(data);
    } catch (error) {
      console.error('Error loading cities:', error);
    } finally {
      setLoading(prev => ({ ...prev, cities: false }));
    }
  };



  // Load cities when state changes
  useEffect(() => {
    if (selectedState && showCity) {
      loadCities(selectedState);
      // Reset city when state changes
      onCityChangeHandler('');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedState, showCity]);

  const handleStateChange = (value: string) => {
    onStateChangeHandler(value);
    // Reset city when state changes
    onCityChangeHandler('');
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (onLocationChange) {
      onLocationChange({
        state: selectedState,
        city: selectedCity,
      });
    }
  };

  return (
    <div className={cn('space-y-4', className)}>
      <form onSubmit={handleSubmit} className="space-y-4">
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
              <SelectTrigger className="h-12 border-2 border-gray-200 hover:border-blue-400 focus:border-blue-500 transition-all duration-200 bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100">
                <SelectValue 
                  placeholder={t('location.selectState')} 
                  className="text-gray-700 font-medium"
                />
              </SelectTrigger>
              <SelectContent className="bg-white border-2 border-gray-100 shadow-lg">
                {states.map((state) => (
                  <SelectItem 
                    key={state} 
                    value={state}
                    className="hover:bg-blue-50 focus:bg-blue-100 transition-colors duration-150 py-3 px-4 cursor-pointer"
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
              value={selectedCity}
              onValueChange={onCityChangeHandler}
              disabled={!selectedState || loading.cities}
            >
              <SelectTrigger className="h-12 border-2 border-gray-200 hover:border-green-400 focus:border-green-500 transition-all duration-200 bg-gradient-to-r from-green-50 to-emerald-50 hover:from-green-100 hover:to-emerald-100 disabled:opacity-50 disabled:cursor-not-allowed">
                <SelectValue 
                  placeholder={selectedState 
                    ? t('location.selectCity')
                    : t('location.selectStateFirst')
                  } 
                  className="text-gray-700 font-medium"
                />
              </SelectTrigger>
              <SelectContent className="bg-white border-2 border-gray-100 shadow-lg">
                {cities.map((city) => (
                  <SelectItem 
                    key={city.id} 
                    value={city.name}
                    className="hover:bg-green-50 focus:bg-green-100 transition-colors duration-150 py-3 px-4 cursor-pointer"
                  >
                    <span className="font-medium text-gray-800">
                      {locale === 'ar' && city.name_ar ? city.name_ar : (city.name_en || city.name)}
                    </span>
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        )}
      </form>
    </div>
  );
};

export default LocationSelector;