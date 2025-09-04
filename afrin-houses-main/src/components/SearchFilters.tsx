import React, { useState, useEffect, useMemo, useCallback, useRef } from 'react';
import { SearchFilters as SearchFiltersType } from '../types';
import { 
  Search, 
  Filter, 
  X,
  MapPin,
  DollarSign,
  Bed,
  Bath,
  Square
} from 'lucide-react';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from './ui/select';

import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from './ui/collapsible';
import { Checkbox } from './ui/checkbox';
import { useTranslation } from 'react-i18next';
import { notification } from '../services/notificationService';
import LocationSelector from './LocationSelector';
import { cityService } from '@/services/cityService';

interface SearchFiltersProps {
  onFiltersChange?: (filters: SearchFiltersType) => void;
  onApplyFilters?: (filters: SearchFiltersType) => void;
  showAdvanced?: boolean;
  initialFilters?: Partial<SearchFiltersType>;
  hideListingType?: boolean;
  isLoadingCities?: boolean;
  citiesError?: string | null;
  onRetryCities?: () => void;
}

const SearchFilters: React.FC<SearchFiltersProps> = (props) => {
  // Destructure props with defaults
  const { 
    onFiltersChange, 
    onApplyFilters,
    showAdvanced = true,
    initialFilters = {},
    hideListingType = false,
    isLoadingCities = false,
    citiesError = null,
    onRetryCities
  } = props;
  const { t } = useTranslation();
  const [formValues, setFormValues] = useState<SearchFiltersType>(() => ({
    listingType: initialFilters?.listingType || 'all',
    propertyType: initialFilters?.propertyType || '',
    location: initialFilters?.location || '',
    state: initialFilters?.state || '',
    city: initialFilters?.city || '',
    minPrice: initialFilters?.minPrice !== undefined ? Number(initialFilters.minPrice) : undefined,
    maxPrice: initialFilters?.maxPrice !== undefined ? Number(initialFilters.maxPrice) : undefined,
    bedrooms: initialFilters?.bedrooms !== undefined ? Number(initialFilters.bedrooms) : undefined,
    bathrooms: initialFilters?.bathrooms !== undefined ? Number(initialFilters.bathrooms) : undefined,
    minSquareFootage: initialFilters?.minSquareFootage !== undefined ? Number(initialFilters.minSquareFootage) : undefined,
    maxSquareFootage: initialFilters?.maxSquareFootage !== undefined ? Number(initialFilters.maxSquareFootage) : undefined,
    features: initialFilters?.features || []
  }));

  // Advanced filters state
  const [states, setStates] = useState<{ value: string; label: string }[]>([]);
  const [cities, setCities] = useState<{ value: string; label: string }[]>([]);
  const [isLoadingStates, setIsLoadingStates] = useState(false);
  const debounceTimerRef = useRef<NodeJS.Timeout | null>(null);

  const [isAdvancedOpen, setIsAdvancedOpen] = useState<boolean>(false);
  
  // Local state for input values that update as user types
  const [localValues, setLocalValues] = useState<{
    minPrice: string;
    maxPrice: string;
    [key: string]: any;
  }>({
    minPrice: initialFilters?.minPrice !== undefined ? initialFilters.minPrice.toString() : '',
    maxPrice: initialFilters?.maxPrice !== undefined ? initialFilters.maxPrice.toString() : ''
  });

  const propertyTypes = [
    { value: 'all', label: t('property.types.all') },
    { value: 'apartment', label: t('property.types.apartment') },
    { value: 'house', label: t('property.types.house') },
    { value: 'condo', label: t('property.types.condo') },
    { value: 'townhouse', label: t('property.types.townhouse') },
    { value: 'studio', label: t('property.types.studio') },
    { value: 'loft', label: t('property.types.loft') },
    { value: 'villa', label: t('property.types.villa') },
    { value: 'commercial', label: t('property.types.commercial') },
    { value: 'land', label: t('property.types.land') },
  ];

  const bedroomOptions = [
    { value: 'any', label: t('filters.any') },
    { value: '1', label: '1+' },
    { value: '2', label: '2+' },
    { value: '3', label: '3+' },
    { value: '4', label: '4+' },
    { value: '5', label: '5+' },
  ];

  const bathroomOptions = [
    { value: 'any', label: t('filters.any') },
    { value: '1', label: '1+' },
    { value: '2', label: '2+' },
    { value: '3', label: '3+' },
    { value: '4', label: '4+' },
  ];

  const commonFeatures = [
    'Parking',
    'Pool',
    'Gym',
    'Pet Friendly',
    'Balcony',
    'Garden',
    'Fireplace',
    'Dishwasher',
    'Air Conditioning',
    'Laundry in Unit',
    'Elevator',
    'Garage',
    'Hardwood Floors',
  ];

  // Update form values when initialFilters change - but only on mount or when there are no existing values
  const isInitialMount = useRef(true);
  
  useEffect(() => {
    // Only update form values on initial mount or when current form is empty
    if (isInitialMount.current && initialFilters) {
      console.log('SearchFilters: Setting initial form values from props:', initialFilters);
      setFormValues(prev => ({
        ...prev,
        ...initialFilters,
        state: initialFilters.state || '',
        city: initialFilters.city || '',
        minPrice: initialFilters.minPrice ? Number(initialFilters.minPrice) : undefined,
        maxPrice: initialFilters.maxPrice ? Number(initialFilters.maxPrice) : undefined,
        bedrooms: initialFilters.bedrooms ? Number(initialFilters.bedrooms) : undefined,
        bathrooms: initialFilters.bathrooms ? Number(initialFilters.bathrooms) : undefined,
        minSquareFootage: initialFilters.minSquareFootage ? Number(initialFilters.minSquareFootage) : undefined,
        maxSquareFootage: initialFilters.maxSquareFootage ? Number(initialFilters.maxSquareFootage) : undefined,
      }));
      
      // Update local price inputs
      setLocalValues({
        minPrice: initialFilters.minPrice ? initialFilters.minPrice.toString() : '',
        maxPrice: initialFilters.maxPrice ? initialFilters.maxPrice.toString() : ''
      });
      
      isInitialMount.current = false;
    }
  }, [initialFilters]);

  // Cleanup debounce timer on unmount
  useEffect(() => {
    return () => {
      if (debounceTimerRef.current) {
        clearTimeout(debounceTimerRef.current);
      }
    };
  }, []);

  // Handle price input changes - only update local state
  const handlePriceChange = (type: 'min' | 'max', value: string) => {
    // Only allow numbers and empty string
    if (value === '' || /^\d*$/.test(value)) {
      setLocalValues(prev => ({
        ...prev,
        [`${type}Price`]: value
      }));
    }
  };
  
  // Handle form submission
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    console.log('SearchFilters: Form submitted');
    console.log('SearchFilters: Current formValues:', formValues);
    console.log('SearchFilters: Current localValues:', localValues);
    
    // Create a copy of form values
    const newFilters = { ...formValues };
    
    // Process price inputs
    const minPrice = localValues.minPrice ? parseInt(localValues.minPrice, 10) : undefined;
    const maxPrice = localValues.maxPrice ? parseInt(localValues.maxPrice, 10) : undefined;
    
    // Update filters with processed price values
    if (minPrice !== undefined) newFilters.minPrice = minPrice;
    if (maxPrice !== undefined) newFilters.maxPrice = maxPrice;
    
    console.log('SearchFilters: Final filters to apply:', newFilters);
    
    // Location is already handled by handleLocationChange and stored in formValues.location
    
    // Call the appropriate callback
    if (onApplyFilters) {
      console.log('SearchFilters: Calling onApplyFilters');
      onApplyFilters(newFilters);
    } else if (onFiltersChange) {
      console.log('SearchFilters: Calling onFiltersChange');
      onFiltersChange(newFilters);
    } else {
      console.log('SearchFilters: No callback provided!');
    }
  };
  
  // Handle reset filters
  const handleReset = () => {
    const resetFilters: SearchFiltersType = {
      listingType: 'all',
      propertyType: '',
      location: '',
      state: '',
      city: '',
      minPrice: undefined,
      maxPrice: undefined,
      bedrooms: undefined,
      bathrooms: undefined,
      minSquareFootage: undefined,
      maxSquareFootage: undefined,
      features: [],
    };
    
    // Reset form values
    setFormValues(resetFilters);
    setLocalValues({ minPrice: '', maxPrice: '' });
    
    // Call the appropriate callback
    if (onApplyFilters) {
      onApplyFilters(resetFilters);
    } else if (onFiltersChange) {
      onFiltersChange(resetFilters);
    }
  };

  // Handle location change with debouncing to prevent excessive API calls
  const handleLocationChange = useCallback(async (type: 'state' | 'city', value: string) => {
    console.log(`SearchFilters: handleLocationChange called - ${type}:`, value);
    
    try {
      if (type === 'state') {
        // Use functional update to prevent stale closure issues
        setFormValues(prev => {
          if (prev.state === value) return prev; // Prevent unnecessary updates
          const newValues = { ...prev, state: value, city: '' };
          // Update location string
          newValues.location = value;
          console.log('SearchFilters: Updated formValues after state change:', newValues);
          return newValues;
        });
        
        // Clear existing timer
        if (debounceTimerRef.current) {
          clearTimeout(debounceTimerRef.current);
        }
        
        if (value) {
          // Debounce API call by 500ms (increased from 300ms)
          debounceTimerRef.current = setTimeout(async () => {
            try {
              console.log('SearchFilters: Loading cities for state:', value);
              const citiesData = await cityService.getCitiesByState(value);
              const newCities = citiesData.map(city => ({ value: city.name, label: city.name }));
              console.log('SearchFilters: Loaded cities:', newCities);
              setCities(prevCities => {
                // Only update if cities actually changed
                if (JSON.stringify(prevCities) === JSON.stringify(newCities)) {
                  return prevCities;
                }
                return newCities;
              });
            } catch (error) {
              console.error('Error loading cities:', error);
              notification.error('خطأ في تحميل المدن');
              setCities([]);
            }
          }, 500);
        } else {
          setCities([]);
        }
      } else {
        setFormValues(prev => {
          if (prev.city === value) return prev; // Prevent unnecessary updates
          const newValues = { ...prev, city: value };
          // Update location string to include both state and city
          const locationParts = [];
          if (value) locationParts.push(value);
          if (prev.state) locationParts.push(prev.state);
          newValues.location = locationParts.join(', ');
          console.log('SearchFilters: Updated formValues after city change:', newValues);
          return newValues;
        });
        
        console.log('SearchFilters: City updated in form only, waiting for Apply Filters button');
      }
    } catch (error) {
      console.error(`Error handling ${type} change:`, error);
      notification.error(`خطأ في معالجة ${type === 'state' ? 'الولاية' : 'المدينة'}`);
    }
  }, [onFiltersChange, formValues]);

  // Handle location changes from LocationSelector with debouncing
  const handleLocationSelectorChange = useCallback((location: { state?: string; city?: string }) => {
    console.log('SearchFilters: Location selector changed:', location);
    
    // Only update form values for display - don't update selectedState/selectedCity
    // to prevent infinite loops with LocationSelector
    const locationParts = [];
    if (location.city) locationParts.push(location.city);
    if (location.state) locationParts.push(location.state);
    
    const newLocation = locationParts.join(', ');
    
    console.log('SearchFilters: New location string:', newLocation);
    
    // Use functional update to prevent stale closure issues
    setFormValues(prev => {
      if (prev.location === newLocation) return prev; // Prevent unnecessary updates
      return {
        ...prev,
        location: newLocation
      };
    });
    
    // Don't notify parent component - wait for form submission
  }, []);

  // Handle filter changes - update local form values
  const handleFilterChange = (key: keyof SearchFiltersType, value: any) => {
    // Skip if this is a price change (handled by handlePriceChange)
    if (key === 'minPrice' || key === 'maxPrice') return;
    
    // Update local form values with simplified logic
    setFormValues(prev => {
      const newValues = { ...prev };
      
      // Handle specific cases to prevent infinite loops
      if (key === 'listingType') {
        newValues.listingType = value === 'all' ? 'all' : value;
      } else if (key === 'propertyType') {
        newValues.propertyType = (value === 'all' || value === 'all-property-types') ? '' : value;
      } else if (key === 'bedrooms' || key === 'bathrooms') {
        if (value === 'any' || value === '') {
          newValues[key] = undefined;
        } else {
          const numValue = Number(value);
          newValues[key] = !isNaN(numValue) ? numValue : undefined;
        }
      } else if (key === 'minSquareFootage' || key === 'maxSquareFootage') {
        const numValue = Number(value);
        newValues[key] = !isNaN(numValue) && numValue > 0 ? numValue : undefined;
      } else if (key === 'location') {
        newValues.location = typeof value === 'string' ? value : '';
      } else if (key === 'state') {
        newValues.state = typeof value === 'string' ? value : '';
      } else if (key === 'city') {
        newValues.city = typeof value === 'string' ? value : '';
      } else if (key === 'features') {
        newValues.features = Array.isArray(value) ? value : [];
      }
      
      return newValues;
    });
  };

  // Toggle feature in form values with useCallback to prevent infinite re-renders
  const handleFeatureToggle = useCallback((feature: string) => {
    setFormValues(prev => {
      const currentFeatures = prev.features || [];
      const isIncluded = currentFeatures.includes(feature);
      
      // Prevent unnecessary state updates if no change
      if (isIncluded && currentFeatures.length === 1 && currentFeatures[0] === feature) {
        const newFeatures = [];
        return prev.features?.length === 0 ? prev : { ...prev, features: newFeatures };
      }
      
      if (!isIncluded && currentFeatures.includes(feature)) {
        return prev; // No change needed
      }
      
      const newFeatures = isIncluded
        ? currentFeatures.filter(f => f !== feature)
        : [...currentFeatures, feature];
      
      // Only update if features actually changed
      if (JSON.stringify(newFeatures.sort()) === JSON.stringify(currentFeatures.sort())) {
        return prev;
      }
      
      return {
        ...prev,
        features: newFeatures
      };
    });
  }, []);

  const clearFilters = () => {
    const defaultFilters: SearchFiltersType = {
      listingType: 'all',
      propertyType: '',
      minPrice: undefined,
      maxPrice: undefined,
      bedrooms: undefined,
      bathrooms: undefined,
      minSquareFootage: undefined,
      maxSquareFootage: undefined,
      features: [],
      location: '',
      state: '',
      city: '',
    };
    
    // Reset all state in a single batch
    setFormValues(defaultFilters);
    setLocalValues({
      minPrice: '',
      maxPrice: ''
    });

    
    // Notify parent immediately without setTimeout
    if (onApplyFilters) {
      onApplyFilters(defaultFilters);
    } else if (onFiltersChange) {
      onFiltersChange(defaultFilters);
    }
  };

  const hasActiveFilters = useMemo(() => {
    return Object.entries(formValues).some(([key, value]) => {
      if (key === 'listingType') return value !== 'all' && value !== undefined;
      if (Array.isArray(value)) return value.length > 0;
      if (typeof value === 'string') return value !== '' && value !== 'all' && value !== 'all-property-types' && value !== 'any';
      return value !== undefined;
    });
  }, [formValues]);

  
  
 
  return (
    <div className="w-full">
      {/* Quick Clear Button */}
      {hasActiveFilters && (
        <div className="mb-2 sm:mb-3 p-2 sm:p-3 bg-[#067977]/10 border border-[#067977]/30 rounded-lg">
              <div className="flex items-center justify-between">
                <span className="text-xs sm:text-sm text-[#067977] font-medium">{t('filters.activeFilters')}</span>
            <Button 
              variant="outline" 
              size="sm" 
              onClick={clearFilters}
              type="button"
              className="text-[#067977] border-[#067977]/30 hover:bg-[#067977]/20 px-2 py-1 text-xs"
            >
              <X className="h-3 w-3 mr-1" />
              {t('filters.clearAll')}
            </Button>
          </div>
        </div>
      )}
      
      <div className="space-y-2 sm:space-y-3">
        <form onSubmit={handleSubmit} className="space-y-3 sm:space-y-4 lg:space-y-6">
        {/* Location Selector */}
        <div className="space-y-1.5 sm:space-y-2">
          <label className="text-xs sm:text-sm font-semibold text-gray-800 flex items-center gap-1 sm:gap-2">
            <div className="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-[#067977] rounded-full"></div>
            {t('filters.location')}
          </label>
          <div className="bg-gray-50 p-2 sm:p-3 rounded-lg border">
            <LocationSelector
              selectedState={formValues.state}
              selectedCity={formValues.city}
              onStateChange={(state) => {
                handleLocationChange('state', state);
              }}
              onCityChange={(city) => {
                handleLocationChange('city', city);
              }}
              showState={true}
              showCity={true}
            />
          </div>
        </div>

        {/* Listing Type - conditionally hidden */}
        {!hideListingType && (
          <div className="space-y-1.5 sm:space-y-2">
            <label className="text-xs sm:text-sm font-semibold text-gray-800 flex items-center gap-1 sm:gap-2">
              <div className="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-green-500 rounded-full"></div>
              {t('filters.listingType')}
            </label>
            <Select
              value={formValues.listingType || 'all'}
              onValueChange={(value) => handleFilterChange('listingType', value)}
            >
              <SelectTrigger className="bg-gray-50 border-gray-300">
                <SelectValue placeholder={t('filters.selectListingType')}>
                  {formValues.listingType === 'rent' ? t('property.listingTypes.rent') : 
                   formValues.listingType === 'sale' ? t('property.listingTypes.sale') : 
                   t('property.listingTypes.all')}
                </SelectValue>
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">{t('property.listingTypes.all')}</SelectItem>
                <SelectItem value="rent">{t('property.listingTypes.rent')}</SelectItem>
                <SelectItem value="sale">{t('property.listingTypes.sale')}</SelectItem>
              </SelectContent>
            </Select>
          </div>
        )}

        {/* Property Type */}
        <div className="space-y-1.5 sm:space-y-2">
          <label className="text-xs sm:text-sm font-semibold text-gray-800 flex items-center gap-1 sm:gap-2">
            <div className="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-purple-500 rounded-full"></div>
            {t('filters.propertyType')}
          </label>
          <Select
            value={formValues.propertyType || 'all-property-types'}
            onValueChange={(value) => handleFilterChange('propertyType', value)}
          >
            <SelectTrigger className="bg-gray-50 border-gray-300">
              <SelectValue placeholder={t('filters.selectPropertyType')}>
                {formValues.propertyType ? 
                  propertyTypes.find(t => t.value === formValues.propertyType)?.label : 
                  t('filters.selectPropertyType')}
              </SelectValue>
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all-property-types">{t('property.types.all')}</SelectItem>
              {propertyTypes.map((type) => (
                <SelectItem key={type.value} value={type.value}>
                  {type.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>

        {/* Price Range */}
        <div className="space-y-1.5 sm:space-y-2">
          <label className="text-xs sm:text-sm font-semibold text-gray-800 flex items-center gap-1 sm:gap-2">
            <div className="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-yellow-500 rounded-full"></div>
            {t('filters.priceRange')}
          </label>
          <div className="bg-gray-50 p-2 sm:p-3 rounded-lg border space-y-2 sm:space-y-3">
            <div className="grid grid-cols-2 gap-2 sm:gap-3">
              <div>
                <label className="text-xs text-gray-600 mb-0.5 sm:mb-1 block">{t('filters.minPrice')}</label>
                <div className="relative">
                  <DollarSign className="absolute left-2 sm:left-3 top-1/2 transform -translate-y-1/2 h-3 w-3 sm:h-4 sm:w-4 text-gray-400" />
                  <Input
                    type="number"
                    min="0"
                    placeholder="0"
                    value={localValues.minPrice}
                    onChange={(e) => handlePriceChange('min', e.target.value)}
                    className="pl-8 sm:pl-10 bg-white text-xs sm:text-sm h-8 sm:h-10"
                  />
                </div>
              </div>
              <div>
                <label className="text-xs text-gray-600 mb-0.5 sm:mb-1 block">{t('filters.maxPrice')}</label>
                <div className="relative">
                  <DollarSign className="absolute left-2 sm:left-3 top-1/2 transform -translate-y-1/2 h-3 w-3 sm:h-4 sm:w-4 text-gray-400" />
                  <Input
                    type="number"
                    min="0"
                    placeholder="∞"
                    value={localValues.maxPrice}
                    onChange={(e) => handlePriceChange('max', e.target.value)}
                    className="pl-8 sm:pl-10 bg-white text-xs sm:text-sm h-8 sm:h-10"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Bedrooms & Bathrooms */}
        <div className="space-y-2 sm:space-y-3">
          <div className="grid grid-cols-2 gap-2 sm:gap-3 lg:gap-4">
            <div className="space-y-1 sm:space-y-2">
              <label className="text-xs sm:text-sm font-semibold text-gray-800 flex items-center gap-1 sm:gap-2">
                <div className="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-red-500 rounded-full"></div>
                {t('filters.bedrooms')}
              </label>
              <Select
              value={formValues.bedrooms?.toString() || 'any'}
              onValueChange={(value) => handleFilterChange('bedrooms', value === 'any' ? '' : value)}
              >
                <SelectTrigger className="bg-gray-50 border-gray-300">
                  <SelectValue>{formValues.bedrooms ? `${formValues.bedrooms}+` : t('filters.any')}</SelectValue>
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="any">{t('filters.any')}</SelectItem>
                  {bedroomOptions.filter(opt => opt.value !== 'any').map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                      {option.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-1 sm:space-y-2">
              <label className="text-xs sm:text-sm font-semibold text-gray-800 flex items-center gap-1 sm:gap-2">
                <div className="w-1.5 h-1.5 sm:w-2 sm:h-2 bg-teal-500 rounded-full"></div>
                {t('filters.bathrooms')}
              </label>
              <Select
              value={formValues.bathrooms?.toString() || 'any'}
              onValueChange={(value) => handleFilterChange('bathrooms', value === 'any' ? '' : value)}
              >
                <SelectTrigger className="bg-gray-50 border-gray-300">
                  <SelectValue>{formValues.bathrooms ? `${formValues.bathrooms}+` : t('filters.any')}</SelectValue>
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="any">{t('filters.any')}</SelectItem>
                  {bathroomOptions.filter(opt => opt.value !== 'any').map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                      {option.label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>

        {/* Advanced Filters */}
        {showAdvanced && (
          <Collapsible open={isAdvancedOpen} onOpenChange={setIsAdvancedOpen}>
            <CollapsibleTrigger asChild>
              <Button type="button" variant="ghost" className="w-full justify-between">
                {t('filters.advancedFilters')}
                <Filter className="h-4 w-4" />
              </Button>
            </CollapsibleTrigger>
            
            <CollapsibleContent className="space-y-4 mt-4">
              {/* Square Footage */}
              <div className="space-y-2">
                <Label>{t('filters.squareFootage')}</Label>
                <div className="grid grid-cols-2 gap-2">
                  <div className="relative">
                    <Square className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      type="number"
                      placeholder={t('property.minSqFt')}
                      value={formValues.minSquareFootage || ''}
                      onChange={(e) => handleFilterChange('minSquareFootage', e.target.value ? Number(e.target.value) : undefined)}
                      className="pl-10"
                    />
                  </div>
                  <div className="relative">
                    <Square className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      type="number"
                      placeholder={t('property.maxSqFt')}
                      value={formValues.maxSquareFootage || ''}
                      onChange={(e) => handleFilterChange('maxSquareFootage', e.target.value ? Number(e.target.value) : undefined)}
                      className="pl-10"
                    />
                  </div>
                </div>
              </div>

              {/* Features */}
              <div className="space-y-2">
                <Label>{t('filters.features')}</Label>
                <div className="grid grid-cols-2 gap-2">
                  {commonFeatures.map((feature, index) => {
                    const featureId = `feature-${index}-${feature.replace(/\s+/g, '-').toLowerCase()}`;
                    const isChecked = formValues.features?.includes(feature) || false;
                    
                    return (
                      <div key={index} className="flex items-center space-x-2">
                        <Checkbox
                          id={featureId}
                          checked={isChecked}
                          onCheckedChange={() => handleFeatureToggle(feature)}
                        />
                        <Label htmlFor={featureId} className="text-sm">
                          {(() => {
                            // Ensure feature is a string before processing
                            const featureName = typeof feature === 'string' ? feature : '';
                            return featureName 
                              ? t(`property.features.${featureName.toLowerCase().replace(/\s+/g, '')}`, featureName)
                              : featureName;
                          })()}
                        </Label>
                      </div>
                    );
                  })}
                </div>
              </div>
            </CollapsibleContent>
          </Collapsible>
        )}

          {/* Apply Filters Button */}
          <div className="mt-4 sm:mt-6 lg:mt-8 pt-2 sm:pt-3 lg:pt-4 border-t border-gray-200">
            <div className="flex gap-2 sm:gap-3">
              <Button 
                type="button"
                variant="outline" 
                onClick={clearFilters}
                className="flex-1 py-2 sm:py-3 border-gray-300 text-gray-700 hover:bg-gray-50 text-xs sm:text-sm"
              >
                {t('common.reset')}
              </Button>
              <Button 
                type="submit"
                className="flex-1 py-2 sm:py-3 bg-gradient-to-r from-[#067977] to-[#067977]/80 hover:from-[#067977]/90 hover:to-[#067977]/70 text-white font-medium shadow-lg text-xs sm:text-sm"
              >
                {t('filters.applyFilters')}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
};

export default SearchFilters;
