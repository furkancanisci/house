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

  const [selectedCountry, setSelectedCountry] = useState<string>(getDefaultCountry());
  const [selectedState, setSelectedState] = useState<string>(getDefaultState());
  const [selectedCity, setSelectedCity] = useState<string>(initialCity || '');
  
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
    if (!selectedCountry || selectedCountry === '') {
      setSelectedCountry(locale === 'ar' ? 'سوريا' : 'Syria');
    }
  }, [locale, showCountry]);

  // Load states for default country (Syria) on mount
  useEffect(() => {
    const defaultCountry = locale === 'ar' ? 'سوريا' : 'Syria';
    if (selectedCountry === defaultCountry && showState) {
      loadStates(defaultCountry);
    }
  }, [locale]);

  // Load states when country changes
  useEffect(() => {
    if (selectedCountry && showState) {
      loadStates(selectedCountry);
    } else if (selectedCountry && !showState) {
      // If not showing state selector, load all cities for the country
      if (showCity) {
        loadCitiesForCountry(selectedCountry);
      }
    }
  }, [selectedCountry, showState, showCity]);

  // Load cities when state changes
  useEffect(() => {
    if (selectedState && showCity) {
      loadCitiesForState(selectedState);
    }
  }, [selectedState, showCity]);

  // Notify parent component of location changes
  useEffect(() => {
    if (onLocationChange) {
      onLocationChange({
        country: selectedCountry || undefined,
        state: selectedState || undefined,
        city: selectedCity || undefined,
      });
    }
  }, [selectedCountry, selectedState, selectedCity, onLocationChange]);

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
    setSelectedCity('');
    setCities([]);
    
    try {
      const data = await cityService.getStates({ locale, country });
      setStates(data);
      
      // Set Damascus as default state for Syria
      const isSyria = country === 'سوريا' || country === 'Syria';
      const damascusName = locale === 'ar' ? 'دمشق' : 'Damascus';
      
      if (isSyria && data.includes(damascusName) && !selectedState) {
        setSelectedState(damascusName);
      } else if (!selectedState && !initialState) {
        // If no initial state and not Syria, keep the default from getDefaultState
        setSelectedState(getDefaultState());
      }
    } catch (error) {
      console.error('Error loading states:', error);
    } finally {
      setLoading(prev => ({ ...prev, states: false }));
    }
  };

  const loadCitiesForCountry = async (country: string) => {
    setLoading(prev => ({ ...prev, cities: true }));
    setSelectedCity('');
    
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
    setLoading(prev => ({ ...prev, cities: true }));
    setSelectedCity('');
    
    try {
      const data = await cityService.getCitiesByState({ 
        locale, 
        state, 
        country: selectedCountry 
      });
      setCities(data);
    } catch (error) {
      console.error('Error loading cities:', error);
    } finally {
      setLoading(prev => ({ ...prev, cities: false }));
    }
  };

  const handleCountryChange = (value: string) => {
    setSelectedCountry(value);
  };

  const handleStateChange = (value: string) => {
    setSelectedState(value);
  };

  const handleCityChange = (value: string) => {
    setSelectedCity(value);
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
            value={selectedCountry}
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
              value={selectedState}
              onValueChange={handleStateChange}
              disabled={loading.states || !selectedCountry}
            >
              <SelectTrigger>
                <SelectValue 
                  placeholder={
                   !selectedCountry
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
              value={selectedCity}
              onValueChange={handleCityChange}
              disabled={loading.cities || !selectedCountry || (showState && !selectedState)}
            >
              <SelectTrigger>
                <SelectValue 
                  placeholder={
                   !selectedCountry
                     ? t('common.selectCountryFirst')
                     : (showState && !selectedState)
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