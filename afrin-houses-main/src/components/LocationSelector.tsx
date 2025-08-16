import React, { useState, useEffect, FC } from 'react';
import { useTranslation } from 'react-i18next';
import { Globe, MapPin } from 'lucide-react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { cityService } from '@/services/cityService';

type LoadingState = {
  countries: boolean;
  states: boolean;
  cities: boolean;
};

interface City {
  id: number | string;
  name: string;
  state: string;
  country: string;
  name_ar?: string;
  name_en?: string;
}

interface LocationSelectorProps {
  onLocationChange?: (location: { country?: string; state?: string; city?: string }) => void;
  selectedCountry?: string;
  selectedState?: string;
  selectedCity?: string;
  onCountryChange?: (country: string) => void;
  onStateChange?: (state: string) => void;
  onCityChange?: (city: string) => void;
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

const LocationSelector: FC<LocationSelectorProps> = ({
  onLocationChange,
  selectedCountry: propSelectedCountry,
  selectedState: propSelectedState,
  selectedCity: propSelectedCity,
  onCountryChange: propOnCountryChange,
  onStateChange: propOnStateChange,
  onCityChange: propOnCityChange,
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
  className = ''
}) => {
  const { t, i18n } = useTranslation();
  const { language: locale } = i18n;

  // Set default values: Syria as country and Damascus as state
  const getDefaultCountry = () => {
    if (initialCountry) return initialCountry;
    return locale === 'ar' ? 'سوريا' : 'Syria';
  };

  const getDefaultState = () => {
    if (initialState) return initialState;
    return locale === 'ar' ? 'دمشق' : 'Damascus';
  };

<<<<<<< HEAD
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
  
=======
  // State for internal management
  const [internalCountry, setInternalCountry] = useState<string>(getDefaultCountry());
  const [internalState, setInternalState] = useState<string>(getDefaultState());
  const [internalCity, setInternalCity] = useState<string>(initialCity || '');
  const [countries, setCountries] = useState<string[]>([]);
  const [states, setStates] = useState<string[]>([]);
  const [cities, setCities] = useState<City[]>([]);
>>>>>>> 7b5a859 (some bug fix)
  const [loading, setLoading] = useState({
    countries: false,
    states: false,
    cities: false
  });

<<<<<<< HEAD
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
=======
  // Use controlled values if provided, otherwise use internal state
  const selectedCountry = propSelectedCountry !== undefined ? propSelectedCountry : internalCountry;
  const selectedState = propSelectedState !== undefined ? propSelectedState : internalState;
  const selectedCity = propSelectedCity !== undefined ? propSelectedCity : internalCity;

  // Handle changes with callbacks if provided, otherwise update internal state
  const onCountryChangeHandler = (value: string) => {
    if (propOnCountryChange) {
      propOnCountryChange(value);
    } else {
      setInternalCountry(value);
    }
    if (onLocationChange) {
      onLocationChange({ country: value });
    }
  };

  const onStateChangeHandler = (value: string) => {
    if (propOnStateChange) {
      propOnStateChange(value);
    } else {
      setInternalState(value);
    }
    if (onLocationChange) {
      onLocationChange({ country: selectedCountry, state: value });
    }
  };

  const onCityChangeHandler = (value: string) => {
    if (propOnCityChange) {
      propOnCityChange(value);
    } else {
      setInternalCity(value);
    }
    if (onLocationChange) {
      onLocationChange({ country: selectedCountry, state: selectedState, city: value });
    }
  };

  // Load countries on mount
  useEffect(() => {
    const loadCountries = async () => {
      try {
        setLoading(prev => ({ ...prev, countries: true }));
        const data = await cityService.getCountries();
        setCountries(data);
      } catch (error) {
        console.error('Error loading countries:', error);
      } finally {
        setLoading(prev => ({ ...prev, countries: false }));
      }
    };

    loadCountries();
  }, []);

  // Load cities when state changes
  const loadCities = async (country: string, state: string) => {
    if (!country || !state) return;
    
    try {
      setLoading(prev => ({ ...prev, cities: true }));
      const data = await cityService.getCities({ country, state });
      setCities(data);
    } catch (error) {
      console.error('Error loading cities:', error);
    } finally {
      setLoading(prev => ({ ...prev, cities: false }));
    }
  };

  // Load all cities for a country
  const loadCitiesForCountry = async (country: string) => {
    if (!country) return;
    
    try {
      setLoading(prev => ({ ...prev, cities: true }));
      const states = await cityService.getStates({ country });
      const allCities: City[] = [];
      
      // Load cities for each state
      for (const state of states) {
        try {
          const cities = await cityService.getCities({ country, state });
          allCities.push(...cities);
        } catch (error) {
          console.error(`Error loading cities for ${state}, ${country}:`, error);
        }
      }
      
      setCities(allCities);
    } catch (error) {
      console.error('Error loading cities for country:', error);
    } finally {
      setLoading(prev => ({ ...prev, cities: false }));
    }
  };

  // Load states when country changes
  useEffect(() => {
    const loadStates = async (country: string) => {
      if (!country) return;

      try {
        setLoading(prev => ({ ...prev, states: true }));
        const data = await cityService.getStates({ country });
        setStates(data);
      } catch (error) {
        console.error('Error loading states:', error);
      } finally {
        setLoading(prev => ({ ...prev, states: false }));
      }
    };

    if (selectedCountry && showState) {
      loadStates(selectedCountry);
    } else if (selectedCountry && !showState && showCity) {
      // If not showing state selector, load all cities for the country
      loadCitiesForCountry(selectedCountry);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedCountry, showState, showCity]);

  // Load cities when state changes
  useEffect(() => {
    if (selectedState && selectedCountry && showCity) {
      loadCities(selectedCountry, selectedState);
      // Reset city when state changes
      onCityChangeHandler('');
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedState, selectedCountry, showCity]);

  const handleCountryChange = (value: string) => {
    onCountryChangeHandler(value);
    // Reset state and city when country changes
    onStateChangeHandler('');
    onCityChangeHandler('');
  };

  const handleStateChange = (value: string) => {
    onStateChangeHandler(value);
    // Reset city when state changes
    onCityChangeHandler('');
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (onLocationChange) {
      onLocationChange({
        country: selectedCountry,
        state: selectedState,
        city: selectedCity,
      });
>>>>>>> 7b5a859 (some bug fix)
    }
  };

  return (
<<<<<<< HEAD
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
=======
    <div className={cn('space-y-4', className)}>
      <form onSubmit={handleSubmit} className="space-y-4">
        {showCountry && (
          <div className="space-y-2">
            <Label htmlFor="country">{t('location.country')}</Label>
            <Select
              value={selectedCountry}
              onValueChange={handleCountryChange}
              disabled={loading.countries}
            >
              <SelectTrigger>
                <span className="flex items-center">
                  <Globe className="h-4 w-4 mr-2" />
                  <SelectValue placeholder={t('location.selectCountry')} />
                </span>
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
>>>>>>> 7b5a859 (some bug fix)

        {showState && (
          <div className="space-y-2">
            <Label htmlFor="state">{t('location.state')}</Label>
            <Select
              value={internalState}
              onValueChange={handleStateChange}
<<<<<<< HEAD
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
=======
              disabled={!selectedCountry || loading.states}
            >
              <SelectTrigger>
                <span className="flex items-center">
                  <MapPin className="h-4 w-4 mr-2" />
                  <SelectValue placeholder={t('location.selectState')} />
                </span>
>>>>>>> 7b5a859 (some bug fix)
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
            <Label htmlFor="city">{t('location.city')}</Label>
            <Select
<<<<<<< HEAD
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
=======
              value={selectedCity}
              onValueChange={onCityChangeHandler}
              disabled={!selectedState || loading.cities}
            >
              <SelectTrigger>
                <SelectValue 
                  placeholder={selectedState 
                    ? t('location.selectCity')
                    : t('location.selectStateFirst')
>>>>>>> 7b5a859 (some bug fix)
                  } 
                />
              </SelectTrigger>
              <SelectContent>
                {cities.map((city) => (
                  <SelectItem key={city.id} value={city.name}>
                    {locale === 'ar' && city.name_ar ? city.name_ar : (city.name_en || city.name)}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        )}

          <Button 
          type="submit" 
          className="w-full" 
          disabled={loading.countries || loading.states || loading.cities}
        >
          {t('common.apply')}
        </Button>
      </form>
    </div>
  );
};

export default LocationSelector;