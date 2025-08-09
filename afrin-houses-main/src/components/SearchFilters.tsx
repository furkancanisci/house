import React, { useState, useEffect } from 'react';
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
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from './ui/card';
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from './ui/collapsible';
import { Checkbox } from './ui/checkbox';
import { useTranslation } from 'react-i18next';

interface SearchFiltersProps {
  onFiltersChange?: (filters: SearchFiltersType) => void;
  onApplyFilters?: (filters: SearchFiltersType) => void;
  showAdvanced?: boolean;
  initialFilters?: Partial<SearchFiltersType>;
}

const SearchFilters: React.FC<SearchFiltersProps> = (props) => {
  // Destructure props with defaults
  const { 
    onFiltersChange, 
    onApplyFilters,
    showAdvanced = true,
    initialFilters = {}
  } = props;
  const { t } = useTranslation();
  // Local form state that only updates when Apply is clicked
  const [formValues, setFormValues] = useState<SearchFiltersType>(() => ({
    listingType: initialFilters?.listingType || 'all',
    propertyType: initialFilters?.propertyType || '',
    location: initialFilters?.location || '',
    minPrice: initialFilters?.minPrice !== undefined ? Number(initialFilters.minPrice) : undefined,
    maxPrice: initialFilters?.maxPrice !== undefined ? Number(initialFilters.maxPrice) : undefined,
    bedrooms: initialFilters?.bedrooms !== undefined ? Number(initialFilters.bedrooms) : undefined,
    bathrooms: initialFilters?.bathrooms !== undefined ? Number(initialFilters.bathrooms) : undefined,
    minSquareFootage: initialFilters?.minSquareFootage !== undefined ? Number(initialFilters.minSquareFootage) : undefined,
    maxSquareFootage: initialFilters?.maxSquareFootage !== undefined ? Number(initialFilters.maxSquareFootage) : undefined,
    features: initialFilters?.features || []
  }));
  
  // Local state for input values that update as user types
  const [localValues, setLocalValues] = useState<{
    minPrice: string;
    maxPrice: string;
    [key: string]: any;
  }>({
    minPrice: initialFilters?.minPrice !== undefined ? initialFilters.minPrice.toString() : '',
    maxPrice: initialFilters?.maxPrice !== undefined ? initialFilters.maxPrice.toString() : ''
  });
  const [isAdvancedOpen, setIsAdvancedOpen] = useState(false);

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

  // Update form values when initialFilters change
  useEffect(() => {
    if (initialFilters) {
      setFormValues(prev => ({
        ...prev,
        ...initialFilters,
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
    }
  }, [initialFilters]);

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
    
    // Create a copy of form values
    const newFilters = { ...formValues };
    
    // Process price inputs
    const minPrice = localValues.minPrice ? parseInt(localValues.minPrice, 10) : undefined;
    const maxPrice = localValues.maxPrice ? parseInt(localValues.maxPrice, 10) : undefined;
    
    // Update filters with processed price values
    if (minPrice !== undefined) newFilters.minPrice = minPrice;
    if (maxPrice !== undefined) newFilters.maxPrice = maxPrice;
    
    // Call the appropriate callback
    if (onApplyFilters) {
      onApplyFilters(newFilters);
    } else if (onFiltersChange) {
      onFiltersChange(newFilters);
    }
  };
  
  // Handle reset filters
  const handleReset = () => {
    const resetFilters: SearchFiltersType = {
      listingType: 'all',
      propertyType: '',
      location: '',
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

  // Handle filter changes - update local form values
  const handleFilterChange = (key: keyof SearchFiltersType, value: any) => {
    // Skip if this is a price change (handled by handlePriceChange)
    if (key === 'minPrice' || key === 'maxPrice') return;
    
    // Update local form values
    setFormValues(prev => {
      const newValues = { ...prev };
      
      // Handle different filter types
      if (value === 'any' || value === 'all' || value === '') {
        // Set to undefined instead of deleting to maintain form state
        if (key === 'bedrooms' || key === 'bathrooms') {
          newValues[key] = undefined;
        } else if (key === 'listingType') {
          newValues.listingType = 'all';
        } else if (key in newValues) {
          delete newValues[key as keyof SearchFiltersType];
        }
      } else {
        // Update the filter value with proper type casting
        if (key === 'listingType' && (value === 'rent' || value === 'sale' || value === 'all')) {
          newValues.listingType = value === 'all' ? undefined : value;
        } else if (key === 'bedrooms' || key === 'bathrooms') {
          const numValue = Number(value);
          if (!isNaN(numValue)) {
            (newValues as any)[key] = numValue;
          }
        } else if (key === 'minSquareFootage' || key === 'maxSquareFootage') {
          const numValue = Number(value);
          if (!isNaN(numValue)) {
            (newValues as any)[key] = numValue;
          }
        } else if (key === 'propertyType' && typeof value === 'string') {
          newValues.propertyType = value;
        } else if (key === 'location' && typeof value === 'string') {
          newValues.location = value;
        } else if (key === 'features' && Array.isArray(value)) {
          newValues.features = value;
        }
      }
      
      return newValues;
    });
  };

  // Toggle feature in form values
  const handleFeatureToggle = (feature: string) => {
    setFormValues(prev => {
      const currentFeatures = prev.features || [];
      const newFeatures = currentFeatures.includes(feature)
        ? currentFeatures.filter(f => f !== feature)
        : [...currentFeatures, feature];
      
      return {
        ...prev,
        features: newFeatures
      };
    });
  };

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
    };
    
    setFormValues(defaultFilters);
    setLocalValues({
      minPrice: '',
      maxPrice: ''
    });
    
    // Notify parent component of changes
    if (onFiltersChange) {
      onFiltersChange(defaultFilters);
    }
  };

  const hasActiveFilters = Object.values(formValues).some(value => {
    if (Array.isArray(value)) return value.length > 0;
    if (typeof value === 'string') return value !== '' && value !== 'all' && value !== 'any';
    return value !== undefined;
  });

  return (
    <Card className="w-full">
      <CardHeader>
        <CardTitle className="flex items-center justify-between">
          <div className="flex items-center space-x-2">
            <Filter className="h-5 w-5" />
            <span>{t('filters.searchFilters')}</span>
          </div>
          {hasActiveFilters && (
            <Button variant="outline" size="sm" onClick={clearFilters}>
              <X className="h-4 w-4 mr-1" />
              {t('buttons.clear')}
            </Button>
          )}
        </CardTitle>
      </CardHeader>
      
      <CardContent className="space-y-4">
        {/* Location Search */}
        <div className="space-y-2">
          <Label htmlFor="location">{t('filters.location')}</Label>
          <div className="relative">
            <MapPin className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <Input
              id="location"
              placeholder={t('filters.enterLocation')}
              value={formValues.location || ''}
              onChange={(e) => handleFilterChange('location', e.target.value)}
              className="pl-10"
            />
          </div>
        </div>

        {/* Listing Type */}
        <div className="space-y-2">
          <Label>{t('filters.listingType')}</Label>
          <Select
            value={formValues.listingType || 'all'}
            onValueChange={(value) => handleFilterChange('listingType', value)}
          >
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">{t('property.listingTypes.all')}</SelectItem>
              <SelectItem value="rent">{t('property.listingTypes.forRent')}</SelectItem>
              <SelectItem value="sale">{t('property.listingTypes.forSale')}</SelectItem>
            </SelectContent>
          </Select>
        </div>

        {/* Property Type */}
        <div className="space-y-2">
          <Label>{t('filters.propertyType')}</Label>
          <Select
            value={formValues.propertyType || ''}
            onValueChange={(value) => handleFilterChange('propertyType', value)}
          >
            <SelectTrigger>
              <SelectValue placeholder={t('filters.propertyType')}>
                {formValues.propertyType ? 
                  propertyTypes.find(t => t.value === formValues.propertyType)?.label : 
                  t('filters.propertyType')}
              </SelectValue>
            </SelectTrigger>
            <SelectContent>
              {propertyTypes.map((type) => (
                <SelectItem key={type.value} value={type.value}>
                  {type.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>

        {/* Price Range */}
        <div className="space-y-2">
          <Label>{t('filters.priceRange')}</Label>
          <div className="grid grid-cols-2 gap-2">
            <div className="relative">
              <DollarSign className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
              <Input
                type="number"
                min="0"
                placeholder={t('filters.minPrice')}
                value={localValues.minPrice}
                onChange={(e) => handlePriceChange('min', e.target.value)}
                className="pl-10"
              />
            </div>
            <div className="relative">
              <DollarSign className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
              <Input
                type="number"
                min="0"
                placeholder={t('filters.maxPrice')}
                value={localValues.maxPrice}
                onChange={(e) => handlePriceChange('max', e.target.value)}
                className="pl-10"
              />
            </div>
          </div>
        </div>

        {/* Bedrooms & Bathrooms */}
        <div className="grid grid-cols-2 gap-4">
          <div className="space-y-2">
            <Label>{t('filters.bedrooms')}</Label>
            <Select
              value={formValues.bedrooms?.toString() || 'any'}
              onValueChange={(value) => handleFilterChange('bedrooms', value === 'any' ? '' : value)}
            >
              <SelectTrigger>
                <SelectValue>{formValues.bedrooms ? `${formValues.bedrooms}+` : t('filters.any')}</SelectValue>
              </SelectTrigger>
              <SelectContent>
                {bedroomOptions.map((option) => (
                  <SelectItem key={option.value} value={option.value}>
                    {option.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          <div className="space-y-2">
            <Label>{t('filters.bathrooms')}</Label>
            <Select
              value={formValues.bathrooms?.toString() || 'any'}
              onValueChange={(value) => handleFilterChange('bathrooms', value === 'any' ? '' : value)}
            >
              <SelectTrigger>
                <SelectValue>{formValues.bathrooms ? `${formValues.bathrooms}+` : t('filters.any')}</SelectValue>
              </SelectTrigger>
              <SelectContent>
                {bathroomOptions.map((option) => (
                  <SelectItem key={option.value} value={option.value}>
                    {option.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        </div>

        {/* Advanced Filters */}
        {showAdvanced && (
          <Collapsible open={isAdvancedOpen} onOpenChange={setIsAdvancedOpen}>
            <CollapsibleTrigger asChild>
              <Button variant="ghost" className="w-full justify-between">
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
                  {commonFeatures.map((feature) => (
                    <div key={feature} className="flex items-center space-x-2">
                      <Checkbox
                        id={feature}
                        checked={formValues.features?.includes(feature) || false}
                        onCheckedChange={() => handleFeatureToggle(feature)}
                      />
                      <Label htmlFor={feature} className="text-sm">
                        {t(`property.features.${feature.toLowerCase().replace(/\s+/g, '')}`, feature)}
                      </Label>
                    </div>
                  ))}
                </div>
              </div>
            </CollapsibleContent>
          </Collapsible>
        )}
        
        {/* Apply Filters Button */}
        <div className="mt-6 flex justify-end space-x-2">
          <Button 
            variant="outline" 
            onClick={handleReset}
            className="px-4 py-2"
          >
            Reset
          </Button>
          <Button 
            onClick={handleSubmit}
            className="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white"
          >
            Apply Filters
          </Button>
        </div>
      </CardContent>
    </Card>
  );
};

export default SearchFilters;
