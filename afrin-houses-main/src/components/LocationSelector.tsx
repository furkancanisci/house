import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from './ui/select';
import { Label } from './ui/label';
import { MapPin, Globe, Building } from 'lucide-react';
import { cityService, City } from '../services/cityService';

interface LocationSelectorProps {
  onLocationChange?: (location: {
    country?: string;
    state?: string;
    city?: string;
  }) => void;
  initialCountry?: string;
  initialState?: string;
  initialCity?: string;
  selectedCountry?: string;
  selectedState?: string;
  selectedCity?: string;
  onCountryChange?: (value: string) => void;
  onStateChange?: (value: string) => void;
  onCityChange?: (value: string) => void;
  showCountry?: boolean;
  showState?: boolean;
  showCity?: boolean;
  className?: string;
}

const LocationSelector: React.FC<LocationSelectorProps> = ({
  onLocationChange,
  initialCountry,
  initialState,
  initialCity,
  selectedCountry,
  selectedState,
  selectedCity,
  onCountryChange,
  onStateChange,
  onCityChange,
  showCountry = true,
  showState = true,
  showCity = true,
  className = '',
}) => {
  const { t, i18n } = useTranslation();
  const locale = i18n.language === 'ar' ? 'ar' : 'en';

  const [countries, setCountries] = useState<string[]>([]);
  const [states, setStates] = useState<string[]>([]);
  const [cities, setCities] = useState<City[]>([]);
  
  // Set default values: Syria as country and Damascus as state
  const getDefaultCountry = () => {
    if (initialCountry) return initialCountry;
    return locale === 'ar' ? 'سوريا' : 'Syria';
  };
  
  const getDefaultState = () => {
    if (initialState) return initialState;
    return locale === 'ar' ? 'دمشق' : 'Damascus';
  };

  const [internalCountry, setInternalCountry] = useState<string>(selectedCountry || getDefaultCountry());
  const [internalState, setInternalState] = useState<string>(selectedState || getDefaultState());
  const [internalCity, setInternalCity] = useState<string>(selectedCity || initialCity || '');
  
  // Update internal state when props change
  useEffect(() => {
    if (selectedCountry) setInternalCountry(selectedCountry);
  }, [selectedCountry]);
  
  useEffect(() => {
    if (selectedState) setInternalState(selectedState);
  }, [selectedState]);
  
  useEffect(() => {
    if (selectedCity) setInternalCity(selectedCity);
  }, [selectedCity]);
  
  const [loading, setLoading] = useState({
    countries: false,
    states: false,
    cities: false,
  });

  // Load countries on component mount and set defaults
  useEffect(() => {
    if (showCountry) {
      loadCountries();
    }
    // Always ensure Syria is selected as default
    if (!internalCountry || internalCountry === '') {
      const defaultCountry = locale === 'ar' ? 'سوريا' : 'Syria';
      setInternalCountry(defaultCountry);
      // Update parent if callback is provided
      if (onLocationChange) {
        onLocationChange({
          country: defaultCountry,
          state: internalState,
          city: internalCity
        });
      }
    }
  }, [locale, showCountry]);

  // Load states for default country (Syria) on mount
  useEffect(() => {
    const defaultCountry = locale === 'ar' ? 'سوريا' : 'Syria';
    if (internalCountry === defaultCountry && showState) {
      loadStates(defaultCountry);
    }
  }, [locale, internalCountry, showState]);

  // Load states when country changes
  useEffect(() => {
    if (internalCountry && showState) {
      loadStates(internalCountry);
    } else if (internalCountry && !showState) {
      // If not showing state selector, load all cities for the country
      if (showCity) {
        loadCitiesForCountry(internalCountry);
      }
    }
  }, [internalCountry, showState, showCity]);

  // Load cities when state changes
  useEffect(() => {
    if (internalState && showCity) {
      loadCitiesForState(internalState);
    }
  }, [internalState, showCity]);

  // Notify parent component of location changes
  useEffect(() => {
    if (onLocationChange) {
      onLocationChange({
        country: internalCountry || undefined,
        state: internalState || undefined,
        city: internalCity || undefined,
      });
    }
  }, [internalCountry, internalState, internalCity, onLocationChange]);

  const loadCountries = async () => {
    setLoading(prev => ({ ...prev, countries: true }));
    try {
      const data = await cityService.getCountries(locale);
      setCountries(data);
    } catch (error) {
      console.error('Error loading countries:', error);
    } finally {
      setLoading(prev => ({ ...prev, countries: false }));
    }
  };

  const loadStates = async (country: string) => {
    setLoading(prev => ({ ...prev, states: true }));
    setInternalCity('');
    setCities([]);
    
    try {
      const data = await cityService.getStates({ locale, country });
      setStates(data);
      
      // Set Damascus as default state for Syria
      const isSyria = country === 'سوريا' || country === 'Syria';
      const damascusName = locale === 'ar' ? 'دمشق' : 'Damascus';
      
      if (isSyria && data.includes(damascusName) && !internalState) {
        setInternalState(damascusName);
      } else if (!internalState && !initialState) {
        // If no initial state and not Syria, keep the default from getDefaultState
        setInternalState(getDefaultState());
      }
    } catch (error) {
      console.error('Error loading states:', error);
    } finally {
      setLoading(prev => ({ ...prev, states: false }));
    }
  };

  const loadCitiesForCountry = async (country: string) => {
    setLoading(prev => ({ ...prev, cities: true }));
    setInternalCity('');
    
    try {
      const data = await cityService.getCities({ locale, country });
      setCities(data);
    } catch (error) {
      console.error('Error loading cities:', error);
    } finally {
      setLoading(prev => ({ ...prev, cities: false }));
    }
  };

  const loadCitiesForState = async (state: string) => {
    if (!internalCountry) return;
    
    setLoading(prev => ({ ...prev, cities: true }));
    
    try {
      const data = await cityService.getCities({
        locale,
        country: internalCountry,
        state,
      });
      setCities(data);
    } catch (error) {
      console.error('Error loading cities:', error);
    } finally {
      setLoading(prev => ({ ...prev, cities: false }));
    }
  };

  const handleCountryChange = (value: string) => {
    setInternalCountry(value);
    setInternalState('');
    setInternalCity('');
    setStates([]);
    setCities([]);
    if (onCountryChange) {
      onCountryChange(value);
    }
  };

  const handleStateChange = (value: string) => {
    setInternalState(value);
    if (onStateChange) {
      onStateChange(value);
    }
  };

  const handleCityChange = (value: string) => {
    setInternalCity(value);
    if (onCityChange) {
      onCityChange(value);
    }
  };

  return (
    <div className={`space-y-4 ${className}`}>
      {showCountry && (
        <div className="space-y-2">
          <Label htmlFor="country" className="flex items-center gap-2">
            <Globe className="h-4 w-4" />
            {t('location.country')}
          </Label>
          <Select
            value={internalCountry}
            onValueChange={handleCountryChange}
            disabled={loading.countries}
          >
            <SelectTrigger>
              <SelectValue 
                placeholder={
                  loading.countries 
                    ? t('common.loading') 
                    : t('location.selectCountry')
                } 
              />
            </SelectTrigger>
            <SelectContent>
              {countries.map((country) => (
                <SelectItem key={country} value={country}>
                  {country}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      )}

      {/* State and City in the same row - always visible when enabled */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {showState && (
          <div className="space-y-2">
            <Label htmlFor="state" className="flex items-center gap-2">
              <Building className="h-4 w-4" />
              {t('location.state')}
            </Label>
            <Select
              value={internalState}
              onValueChange={handleStateChange}
              disabled={!internalCountry || loading.states}
            >
              <SelectTrigger>
                <SelectValue 
                  placeholder={
                   !internalCountry
                     ? t('common.selectCountryFirst')
                     : loading.states 
                       ? t('common.loading') 
                       : t('location.selectState')
                 } 
                />
              </SelectTrigger>
              <SelectContent>
                {states.map((state) => (
                  <SelectItem key={state} value={state}>
                    {state}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        )}

        {showCity && (
          <div className="space-y-2">
            <Label htmlFor="city" className="flex items-center gap-2">
              <MapPin className="h-4 w-4" />
              {t('location.city')}
            </Label>
            <Select
              value={internalCity}
              onValueChange={handleCityChange}
              disabled={!internalState || loading.cities}
            >
              <SelectTrigger>
                <SelectValue 
                  placeholder={
                    !internalCountry
                      ? t('common.selectCountryFirst')
                      : (showState && !internalState)
                        ? t('common.selectStateFirst')
                        : loading.cities 
                          ? t('common.loading') 
                          : t('location.selectCity')
                  } 
                />
              </SelectTrigger>
              <SelectContent>
                {cities.map((city) => (
                  <SelectItem key={city.id} value={locale === 'ar' ? city.name_ar : city.name_en}>
                    {locale === 'ar' ? city.name_ar : city.name_en}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        )}
      </div>
    </div>
  );
};

export default LocationSelector;