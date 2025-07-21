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
  showAdvanced?: boolean;
  initialFilters?: Partial<SearchFiltersType>;
}

const SearchFilters: React.FC<SearchFiltersProps> = ({ 
  onFiltersChange, 
  showAdvanced = true,
  initialFilters = {}
}) => {
  const { t } = useTranslation();
  const [filters, setFilters] = useState<SearchFiltersType>(() => ({
    listingType: initialFilters?.listingType || 'all',
    propertyType: initialFilters?.propertyType || '',
    location: initialFilters?.location || '',
    minPrice: initialFilters?.minPrice ? Number(initialFilters.minPrice) : undefined,
    maxPrice: initialFilters?.maxPrice ? Number(initialFilters.maxPrice) : undefined,
    bedrooms: initialFilters?.bedrooms ? Number(initialFilters.bedrooms) : undefined,
    bathrooms: initialFilters?.bathrooms ? Number(initialFilters.bathrooms) : undefined,
    minSquareFootage: initialFilters?.minSquareFootage ? Number(initialFilters.minSquareFootage) : undefined,
    maxSquareFootage: initialFilters?.maxSquareFootage ? Number(initialFilters.maxSquareFootage) : undefined,
    features: initialFilters?.features || []
  }));
  const [isAdvancedOpen, setIsAdvancedOpen] = useState(false);

  const propertyTypes = [
    { value: 'all', label: t('property.types.all') },
    { value: 'apartment', label: t('property.types.apartment') },
    { value: 'house', label: t('property.types.house') },
    { value: 'condo', label: t('property.types.condo') },
    { value: 'townhouse', label: t('property.types.townhouse') },
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

  useEffect(() => {
    if (onFiltersChange) {
      onFiltersChange(filters);
    }
  }, [filters, onFiltersChange]);

  useEffect(() => {
    if (initialFilters) {
      setFilters(prev => ({
        ...prev,
        ...initialFilters,
        minPrice: initialFilters.minPrice ? Number(initialFilters.minPrice) : undefined,
        maxPrice: initialFilters.maxPrice ? Number(initialFilters.maxPrice) : undefined,
        bedrooms: initialFilters.bedrooms ? Number(initialFilters.bedrooms) : undefined,
        bathrooms: initialFilters.bathrooms ? Number(initialFilters.bathrooms) : undefined,
        minSquareFootage: initialFilters.minSquareFootage ? Number(initialFilters.minSquareFootage) : undefined,
        maxSquareFootage: initialFilters.maxSquareFootage ? Number(initialFilters.maxSquareFootage) : undefined,
      }));
    }
  }, [initialFilters]);

  const handleFilterChange = (key: keyof SearchFiltersType, value: any) => {
    setFilters(prev => ({
      ...prev,
      [key]: value
    }));
  };

  const handleFeatureToggle = (feature: string) => {
    setFilters(prev => ({
      ...prev,
      features: prev.features?.includes(feature)
        ? prev.features.filter(f => f !== feature)
        : [...(prev.features || []), feature],
    }));
  };

  const clearFilters = () => {
    setFilters({
      listingType: 'all',
      propertyType: 'all',
      minPrice: undefined,
      maxPrice: undefined,
      bedrooms: undefined,
      bathrooms: undefined,
      minSquareFootage: undefined,
      maxSquareFootage: undefined,
      features: [],
      location: '',
    });
  };

  const hasActiveFilters = Object.values(filters).some(value => {
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
              value={filters.location}
              onChange={(e) => handleFilterChange('location', e.target.value)}
              className="pl-10"
            />
          </div>
        </div>

        {/* Listing Type */}
        <div className="space-y-2">
          <Label>{t('filters.listingType')}</Label>
          <Select
            value={filters.listingType}
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
            value={filters.propertyType}
            onValueChange={(value) => handleFilterChange('propertyType', value)}
          >
            <SelectTrigger>
              <SelectValue placeholder={t('filters.propertyType')} />
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
                placeholder={t('filters.minPrice')}
                value={filters.minPrice || ''}
                onChange={(e) => handleFilterChange('minPrice', e.target.value ? Number(e.target.value) : undefined)}
                className="pl-10"
              />
            </div>
            <div className="relative">
              <DollarSign className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
              <Input
                type="number"
                placeholder={t('filters.maxPrice')}
                value={filters.maxPrice || ''}
                onChange={(e) => handleFilterChange('maxPrice', e.target.value ? Number(e.target.value) : undefined)}
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
              value={filters.bedrooms?.toString() || 'any'}
              onValueChange={(value) => handleFilterChange('bedrooms', value === 'any' ? undefined : Number(value))}
            >
              <SelectTrigger>
                <SelectValue placeholder={t('filters.any')} />
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
              value={filters.bathrooms?.toString() || 'any'}
              onValueChange={(value) => handleFilterChange('bathrooms', value === 'any' ? undefined : Number(value))}
            >
              <SelectTrigger>
                <SelectValue placeholder={t('filters.any')} />
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
                      value={filters.minSquareFootage || ''}
                      onChange={(e) => handleFilterChange('minSquareFootage', e.target.value ? Number(e.target.value) : undefined)}
                      className="pl-10"
                    />
                  </div>
                  <div className="relative">
                    <Square className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      type="number"
                      placeholder={t('property.maxSqFt')}
                      value={filters.maxSquareFootage || ''}
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
                        checked={filters.features?.includes(feature) || false}
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
      </CardContent>
    </Card>
  );
};

export default SearchFilters;
