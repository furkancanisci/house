import React, { useEffect, useState, useCallback, useRef, useMemo } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { ExtendedProperty, Property, SearchFilters as SearchFiltersType } from '../types';
import SearchFilters from '../components/SearchFilters';
import { Button } from '../components/ui/button';
import { LayoutGrid, List, Loader2, X } from 'lucide-react';
import { useApp } from '../context/AppContext';
import PropertyCard from '../components/PropertyCard';

// Define a type for the media item
interface MediaItem {
  original_url?: string;
  url?: string;
  [key: string]: any;
}

// Process property images to ensure consistent format
const processPropertyImages = (property: Partial<Property | ExtendedProperty> & { 
  images?: string[]; 
  media?: MediaItem[];
  mainImage?: string;
  main_image?: string;
  [key: string]: any;
}): string[] => {
  if (!property) return [];
  
  // If property has a main image, use it as the first image
  const mainImage = property.mainImage || property.main_image || '';
  
  // If property has an images array, use it
  if (Array.isArray(property.images) && property.images.length > 0) {
    return [mainImage, ...property.images].filter((img): img is string => Boolean(img));
  }
  
  // If property has media array with URLs, extract them
  if (Array.isArray(property.media)) {
    const mediaUrls = property.media
      .filter((item: MediaItem) => item.original_url || item.url)
      .map((item: MediaItem) => item.original_url || item.url || '');
    return [mainImage, ...mediaUrls].filter((img): img is string => Boolean(img));
  }
  
  // Fallback to main image if available
  return mainImage ? [mainImage] : [];
};

interface SearchParams extends Record<string, string | undefined> {
  q?: string;
  search?: string;
  listingType?: string;
  propertyType?: string;
  location?: string;
  minPrice?: string;
  maxPrice?: string;
  bedrooms?: string;
  bathrooms?: string;
  minSquareFootage?: string;
  maxSquareFootage?: string;
  features?: string;
  sort?: string;
  page?: string;
  perPage?: string;
}

type ViewMode = 'grid' | 'list';

const Search: React.FC = () => {
  const { t, i18n } = useTranslation();
  const navigate = useNavigate();
  const { state, loadProperties } = useApp();
  
  // Helper to normalize values that may be localized objects { name_ar, name_en }
  const normalizeName = (val: any): string => {
    if (!val) return '';
    if (typeof val === 'string') return val;
    if (typeof val === 'object') {
      const locale = i18n.language === 'ar' ? 'ar' : 'en';
      const ar = (val as any).name_ar ?? (val as any).ar ?? (val as any).name;
      const en = (val as any).name_en ?? (val as any).en ?? (val as any).name;
      return locale === 'ar' ? (ar || en || '') : (en || ar || '');
    }
    return String(val);
  };
  const { properties: allProperties, filteredProperties: contextFilteredProperties, loading, error } = state;

  
  // Convert Property to ExtendedProperty
  const toExtendedProperty = useCallback((property: Property | ExtendedProperty): ExtendedProperty => {
    // If it's already an ExtendedProperty, return it as is
    if ('formattedPrice' in property) {
      return property as ExtendedProperty;
    }
    
    // Otherwise, convert Property to ExtendedProperty
    const details = property.details || {};
    const price = typeof property.price === 'string' ? parseFloat(property.price) || 0 : Number(property.price) || 0;
    const propertyType = property.property_type || 'house';
    const listingType = property.listing_type === 'rent' || property.listing_type === 'sale' ? property.listing_type : 'sale';
    
    // Calculate square footage from either details or property directly
    const squareFootage = details.square_footage || property.square_feet || 0;
    const bedrooms = details.bedrooms || property.bedrooms || 0;
    const bathrooms = details.bathrooms || property.bathrooms || 0;
    
    // Create base property with all required fields
    const baseProperty: Omit<Property, 'property_type' | 'listing_type' | 'square_feet' | 'zip_code' | 'created_at'> & {
      propertyType: string;
      listingType: 'rent' | 'sale';
      squareFootage: number;
      zipCode: string;
    } = {
      ...property,
      propertyType,
      listingType,
      squareFootage,
      zipCode: property.zip_code || '',
      // Ensure required fields are present
      city: property.city || '',
      state: property.state || '',
      // Ensure media is always an array
      media: Array.isArray(property.media) ? property.media : [],
    };

    // Create the extended property with all required fields
    const extendedProperty: ExtendedProperty = {
      ...baseProperty,
      formattedPrice: new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(price),
      bedrooms,
      bathrooms,
      squareFootage,
      formattedSquareFootage: squareFootage 
        ? `${squareFootage.toLocaleString()} sqft`
        : 'N/A',
      formattedBeds: bedrooms 
        ? `${bedrooms} ${bedrooms === 1 ? 'Bed' : 'Beds'}` 
        : 'N/A',
      formattedBaths: details.bathrooms 
        ? `${details.bathrooms} ${details.bathrooms === 1 ? 'Bath' : 'Baths'}` 
        : 'N/A',
      formattedAddress: property.address || 'N/A',
      formattedPropertyType: propertyType,
      formattedDate: property.created_at 
        ? new Date(property.created_at).toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
          })
        : 'N/A',
      isFavorite: false, // Will be updated by the parent component
      features: property.features || [],
      images: Array.isArray(property.media) 
        ? property.media.map(m => typeof m === 'string' ? m : m?.url || '').filter(Boolean)
        : [],
      details: {
        bedrooms: details.bedrooms || 0,
        bathrooms: details.bathrooms || 0,
        squareFootage: details.square_footage || 0,
        yearBuilt: details.year_built
      },
      mainImage: property.media?.find((m: any) => typeof m === 'object' && m.is_featured)?.url || 
                (Array.isArray(property.media) && property.media[0]?.url) || 
                '/placeholder-property.jpg'
    };

    return extendedProperty;
  }, []);
  
  // Use context's filtered properties directly
  const filteredProperties = contextFilteredProperties ? contextFilteredProperties.map(prop => toExtendedProperty(prop)) : [];

  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
  const isInitialMount = useRef(true);

  // Get search params from URL
  const [searchParams] = useSearchParams();

  // Define URL parameter filter type (all values are strings from URL)
  type URLFilterType = {
    search?: string;
    searchQuery?: string;
    propertyType?: string;
    listingType?: string;
    minPrice?: string;
    maxPrice?: string;
    page?: string;
    [key: string]: string | undefined;
  };
  
  // Convert URL params to SearchFilters type
  const urlParamsToSearchFilters = (params: URLFilterType): Partial<SearchFiltersType> => {
    const filters: Partial<SearchFiltersType> = {};
    
    // Handle search query from URL - prioritize 'q' parameter, then 'search', then 'searchQuery'
    if (params.q) {
      filters.search = params.q;
      filters.searchQuery = params.q;
    } else if (params.search) {
      filters.search = params.search;
      filters.searchQuery = params.search;
    } else if (params.searchQuery) {
      filters.search = params.searchQuery;
      filters.searchQuery = params.searchQuery;
    }
    
    if (params.propertyType) filters.propertyType = params.propertyType;
    
    // Handle listing type with type safety
    if (params.listingType && ['rent', 'sale', 'all'].includes(params.listingType)) {
      filters.listingType = params.listingType as 'rent' | 'sale' | 'all';
    }
    
    // Convert string numbers to actual numbers
    if (params.minPrice) {
      const minPrice = Number(params.minPrice);
      if (!isNaN(minPrice)) filters.minPrice = minPrice;
    }
    
    if (params.maxPrice) {
      const maxPrice = Number(params.maxPrice);
      if (!isNaN(maxPrice)) filters.maxPrice = maxPrice;
    }
    
    // Handle pagination
    if (params.page) {
      const page = Number(params.page);
      if (!isNaN(page) && page > 0) filters.page = page;
    }
    
    // Handle sort param in the form of "price-asc", "date-desc"
    if ((params as any).sort) {
      const sortVal = String((params as any).sort);
      const [by, order] = sortVal.split('-') as ['price' | 'date' | 'sqft', 'asc' | 'desc'];
      if (by) {
        filters.sortBy = by === 'date' ? 'created_at' : (by as any);
      }
      if (order === 'asc' || order === 'desc') {
        filters.sortOrder = order;
      }
    }

    if (params.bedrooms) {
        const bedrooms = Number(params.bedrooms);
        if (!isNaN(bedrooms)) filters.bedrooms = bedrooms;
    }

    if (params.bathrooms) {
        const bathrooms = Number(params.bathrooms);
        if (!isNaN(bathrooms)) filters.bathrooms = bathrooms;
    }

    if (params.minSquareFootage) {
        const minSqft = Number(params.minSquareFootage);
        if (!isNaN(minSqft)) filters.minSquareFootage = minSqft;
    }

    if (params.maxSquareFootage) {
        const maxSqft = Number(params.maxSquareFootage);
        if (!isNaN(maxSqft)) filters.maxSquareFootage = maxSqft;
    }

    if (params.features) {
        filters.features = params.features.split(',');
    }

    if (params.location) {
        filters.location = params.location;
    }
    
    return filters;
  };



  // Get filters from URL parameters
  const [filters, setFilters] = useState<SearchFiltersType>(() => {
    const urlFilters: URLFilterType = {};
    
    // Extract all parameters from URL
    searchParams.forEach((value, key) => {
      urlFilters[key as keyof URLFilterType] = value;
    });
    
    // Convert URL params to SearchFilters
    return urlParamsToSearchFilters(urlFilters);
  });
  
  // Store filters from URL params separately for reference
  const filtersFromParams = useMemo(() => {
    const urlFilters: URLFilterType = {};
    searchParams.forEach((value, key) => {
      urlFilters[key as keyof URLFilterType] = value;
    });
    return urlParamsToSearchFilters(urlFilters);
  }, [searchParams]);

  // Current sort value derived from filters state
  const currentSortValue = useMemo(() => {
    const by = filters?.sortBy === 'created_at' ? 'date' : (filters?.sortBy || 'date');
    const order = filters?.sortOrder || 'desc';
    return `${by}-${order}`;
  }, [filters]);

  const previousFilters = useRef<SearchFiltersType | null>(null);

  // Check if filters have changed
  const haveFiltersChanged = useCallback((newFilters: SearchFiltersType) => {
    if (!previousFilters.current) return true;
    
    const oldFilters = previousFilters.current;
    
    // Get all unique keys from both filters
    const allKeys = new Set([
      ...Object.keys(newFilters),
      ...Object.keys(oldFilters)
    ]);
    
    // Check if any filter value has changed
    for (const key of allKeys) {
      if (newFilters[key as keyof SearchFiltersType] !== oldFilters[key as keyof SearchFiltersType]) {
        return true;
      }
    }
    
    return false;
  }, []);

  // Update URL with current filters
  const updateURL = useCallback((filters: SearchFiltersType) => {
    const params = new URLSearchParams();

    // Add all non-empty filters to URL params
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== '' && value !== null) {
        if (Array.isArray(value)) {
          if (value.length > 0) {
            params.set(key, value.join(','));
          }
        } else if (key === 'search') {
          // Special handling for search to use 'q' in URL for better UX
          params.set('q', String(value));
        } else {
          params.set(key, String(value));
        }
      }
    });

    // Handle sorting
    if (filters.sortBy && filters.sortOrder) {
      const sortParam = `${filters.sortBy === 'created_at' ? 'date' : filters.sortBy}-${filters.sortOrder}`;
      params.set('sort', sortParam);
    }

    // Update URL without causing a page reload
    navigate(`?${params.toString()}`, { replace: true });
  }, [navigate]);

  // Convert Property to ExtendedProperty

  const toExtendedProperty = useCallback((property: Property | ExtendedProperty): ExtendedProperty => {
    // If it's already an ExtendedProperty, return it as is
    if ('formattedPrice' in property) {
      return property as ExtendedProperty;
    }
    
    // Otherwise, convert Property to ExtendedProperty
    const details = property.details || {};
    const price = typeof property.price === 'string' ? parseFloat(property.price) || 0 : Number(property.price) || 0;
    const propertyType = property.property_type || 'house';
    const listingType = property.listing_type === 'rent' || property.listing_type === 'sale' ? property.listing_type : 'sale';
    
    // Calculate square footage from either details or property directly
    const squareFootage = details.square_footage || property.square_feet || 0;
    const bedrooms = details.bedrooms || property.bedrooms || 0;
    const bathrooms = details.bathrooms || property.bathrooms || 0;
    
    // Create base property with all required fields
    const baseProperty: Omit<Property, 'property_type' | 'listing_type' | 'square_feet' | 'zip_code' | 'created_at'> & {
      propertyType: string;
      listingType: 'rent' | 'sale';
      squareFootage: number;
      zipCode: string;
    } = {
      ...property,
      propertyType,
      listingType,
      squareFootage,
      zipCode: property.zip_code || property.location?.postal_code || '',
      // Ensure required fields are present
      city: property.location?.city || property.city || '',
      state: property.location?.state || property.state || '',
      // Ensure media is always an array
      media: Array.isArray(property.media) ? property.media : [],
    };



  // Since we're now using API-based filtering, we don't need local filtering

  // Handle filter changes - single implementation
   const handleFilterChange: IFilterChangeHandler = useCallback(async (newFilters) => {
    // Convert string prices to numbers for the filter and ensure proper typing
    const processedFilters: SearchFiltersType = {
      ...newFilters,
      minPrice: newFilters.minPrice !== undefined ? Number(newFilters.minPrice) : undefined,
      maxPrice: newFilters.maxPrice !== undefined ? Number(newFilters.maxPrice) : undefined,
      // Keep bedrooms/bathrooms as provided to support values like '3+' or exact numbers
      bedrooms: newFilters.bedrooms,
      bathrooms: newFilters.bathrooms,
      minSquareFootage: newFilters.minSquareFootage !== undefined ? Number(newFilters.minSquareFootage) : undefined,
      maxSquareFootage: newFilters.maxSquareFootage !== undefined ? Number(newFilters.maxSquareFootage) : undefined,
      // Handle page as number
      page: newFilters.page !== undefined ? Number(newFilters.page) : 1, // Default to page 1
      // Ensure search and searchQuery are properly handled
      search: newFilters.search || newFilters.searchQuery || undefined,
      searchQuery: newFilters.searchQuery || newFilters.search || undefined,
    } as SearchFiltersType;
    
    // Clean up undefined values
    Object.keys(processedFilters).forEach(key => {
      if (processedFilters[key as keyof SearchFiltersType] === undefined) {
        delete processedFilters[key as keyof SearchFiltersType];

      }
    });
    
    // Update filters state and URL
    setFilters(processedFilters);
    updateURL(processedFilters);
     
    // Update filters in the context
    await filterProperties(processedFilters);
    
    // Update URL with the new filters
    const params = new URLSearchParams();
    Object.entries(processedFilters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        // Convert arrays to comma-separated strings
        if (Array.isArray(value)) {
          if (value.length > 0) {
            params.set(key, value.join(','));
          }
        } else {
          params.set(key, String(value));
        }

      }
    });
    // Also include compact 'sort' param for UX/back-compat
    if (processedFilters.sortBy && processedFilters.sortOrder) {
      const by = processedFilters.sortBy === 'created_at' ? 'date' : processedFilters.sortBy;
      params.set('sort', `${by}-${processedFilters.sortOrder}`);
    }

    navigate(`?${newParams.toString()}`, { replace: true });
  }, [navigate]);

  // Handle pagination
  const handlePageChange = useCallback(async (page: number) => {
    // Create a new filters object with the updated page number

    const newFilters: SearchFiltersType = { 
      ...filtersFromParams,
      page: page,
    };
    
    await filterProperties(newFilters);
  }, [filtersFromParams, filterProperties]);


  // Apply filters whenever allProperties or searchParams change
  useEffect(() => {
    const applyInitialFilters = async () => {
      if (isInitialMount.current) {
        isInitialMount.current = false;
        
        // Apply filters from URL parameters on initial load
        if (Object.keys(filtersFromParams).length > 0) {
          console.log('Applying filters from URL:', filtersFromParams);
          await filterProperties(filtersFromParams);
        } else {
          console.log('No filters in URL, loading all properties');
          await filterProperties({});
        }
      }
    };

    applyInitialFilters();
  }, [filtersFromParams, filterProperties]);

  // Remove the loading return statement to keep navbar and search interface visible

  if (error) {
    return (
      <div className="bg-red-50 border-l-4 border-red-400 p-4">
        <div className="flex">
          <div className="flex-shrink-0">
            <X className="h-5 w-5 text-red-400" />
          </div>
          <div className="ml-3">
            <p className="text-sm text-red-700">{error}</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <div className="mb-8">
        <h1 className="text-3xl font-bold mb-6">
          {loading 
            ? 'جاري البحث عن العقارات...'
            : filteredProperties.length > 0 
              ? t('search.resultsCount', { count: filteredProperties.length })
              : t('search.noResults')}
        </h1>
        <div className="flex flex-col md:flex-row gap-6">
          {/* Filters Sidebar */}
          <div className="w-full md:w-1/4">
            <SearchFilters 
              key={JSON.stringify(filters)} // Force re-render when filters change

              onApplyFilters={handleFilterChange}
              initialFilters={filters}
            />
          </div>
          
          {/* Results Section */}
          <div className="w-full md:w-3/4">
            <div className="flex justify-between items-center mb-6">
              <p className="text-gray-600">
                {t('search.showingCount', { count: filteredProperties.length })}
              </p>
              <div className="flex items-center gap-2">
                <div className="flex items-center bg-gray-100 rounded-lg p-1">
                  <Button
                    variant={viewMode === 'grid' ? 'default' : 'ghost'}
                    size="sm"
                    onClick={() => setViewMode('grid')}
                    className={`flex items-center space-x-2 transition-all duration-200 ${
                      viewMode === 'grid' 
                        ? 'bg-white shadow-sm text-gray-900' 
                        : 'text-gray-600 hover:text-gray-900'
                    }`}
                    aria-label="Grid view"
                  >
                    <LayoutGrid className="h-4 w-4" />
                    <span className="hidden sm:inline font-medium">شبكة</span>
                  </Button>
                  <Button
                    variant={viewMode === 'list' ? 'default' : 'ghost'}
                    size="sm"
                    onClick={() => setViewMode('list')}
                    className={`flex items-center space-x-2 transition-all duration-200 ${
                      viewMode === 'list' 
                        ? 'bg-white shadow-sm text-gray-900' 
                        : 'text-gray-600 hover:text-gray-900'
                    }`}
                    aria-label="List view"
                  >
                    <List className="h-4 w-4" />
                    <span className="hidden sm:inline font-medium">قائمة</span>
                  </Button>
                </div>
              </div>
              <div className="w-64">
                <select
                  className="w-full p-2 border rounded"
                  value={currentSortValue}
                  onChange={(e) => {
                    const sortValue = e.target.value;
                    if (sortValue === 'relevance') {
                      // Clear sorting
                      handleFilterChange({ ...filters, sortBy: undefined, sortOrder: undefined });
                      return;
                    }
                    const [sortBy, sortOrder] = sortValue.split('-') as ['price' | 'date' | 'sqft', 'asc' | 'desc'];

                    // Map the sortBy value to match the expected SearchFilters type
                    const mappedSortBy = sortBy === 'date' ? 'created_at' : sortBy;

                    handleFilterChange({
                      ...filters,
                      sortBy: mappedSortBy as any,
                      sortOrder: sortOrder as 'asc' | 'desc',
                    });
                  }}
                >
                  <option value="relevance">{t('search.sort.relevance', 'Relevance')}</option>
                  <option value="price-asc">{t('search.sort.priceAsc', 'Price: Low to High')}</option>
                  <option value="price-desc">{t('search.sort.priceDesc', 'Price: High to Low')}</option>
                  <option value="date-desc">{t('search.sort.newest', 'Newest First')}</option>
                  <option value="date-asc">{t('search.sort.oldest', 'Oldest First')}</option>
                </select>
              </div>
            </div>

            {filteredProperties.length === 0 ? (
              <div className="text-center py-12">
                <div className="mb-6">
                  <div className="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg className="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                  </div>
                  <h3 className="text-xl font-semibold text-gray-900 mb-2">
                    {filtersFromParams.location && filtersFromParams.location.includes(',') ? (
                      (() => {
                        const locationParts = filtersFromParams.location.split(',').map(part => part.trim());
                        const [city, state] = locationParts;
                        return `لا توجد عقارات حالياً في ${city}، ${state}`;
                      })()
                    ) : filtersFromParams.location ? (
                      `لا توجد عقارات حالياً في ${filtersFromParams.location}`
                    ) : (
                      t('search.noResults')
                    )}
                  </h3>
                  <p className="text-gray-600 mb-4">
                    {filtersFromParams.location ? (
                      'جرب البحث في محافظة أخرى أو قم بتعديل معايير البحث'
                    ) : (
                      t('search.tryAdjustingFilters')
                    )}
                  </p>
                  <div className="flex flex-col sm:flex-row gap-3 justify-center">
                    <Button 
                      onClick={() => {
                        handleFilterChange({ location: '' });
                      }}
                      variant="outline"
                      className="px-6 py-2"
                    >
                      مسح فلتر الموقع
                    </Button>
                    <Button 
                      onClick={() => {
                        handleFilterChange({
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
                          sortBy: undefined,
                          sortOrder: undefined
                        });
                      }}
                      className="px-6 py-2 bg-blue-600 hover:bg-blue-700"
                    >
                      مسح جميع الفلاتر
                    </Button>
                  </div>
                </div>
                {process.env.NODE_ENV === 'development' && (
                  <div className="mt-4 p-4 bg-gray-100 rounded-md text-left text-sm">
                    <h4 className="font-medium mb-2">Debug Information:</h4>
                    <pre className="text-xs overflow-auto max-h-40">
                      {JSON.stringify({
                        searchParams: Object.fromEntries(searchParams.entries()),
                        filters: activeFilters,
                        propertiesCount: filteredProperties.length,
                        hasError: !!error,
                        errorMessage: error
                      }, null, 2)}
                    </pre>
                  </div>
                )}
              </div>
            ) : loading ? (
              <div className="flex flex-col items-center justify-center py-16">
                <Loader2 className="h-12 w-12 animate-spin text-blue-600 mb-4" />
                <p className="text-gray-600 text-lg">جاري تحميل العقارات...</p>
                <p className="text-gray-500 text-sm mt-2">يرجى الانتظار قليلاً</p>
              </div>
            ) : (
              <div className={`
                ${viewMode === 'grid' 
                  ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6' 
                  : 'flex flex-col space-y-4'
                }
              `}>
                {filteredProperties.map((property) => {
                  const price = typeof property.price === 'object'
                    ? Number((property.price as any)?.amount) || 0
                    : Number(property.price) || 0;

                  // Get square footage from various possible locations in the API response
                  const squareFootage = 
                    property.square_feet || 
                    (property as any).details?.square_footage || 
                    (property as any).square_footage || 
                    (property as any).details?.square_footage || 
                    0;

                  // Process images to ensure we have proper images
                  const images = processPropertyImages(property);
                  const mainImage = images.length > 0 ? images[0] : '';

                  const mappedProperty: ExtendedProperty = {
                    ...property,
                    id: property.id,
                    slug: (property as any).slug || `property-${property.id}`,
                    title: property.title || 'No Title',
                    description: property.description || '',
                    price,
                    propertyType: property.property_type || 'apartment',
                    listingType: (property.listing_type === 'rent' || property.listing_type === 'sale'
                      ? property.listing_type
                      : 'sale') as 'rent' | 'sale',
                    details: {
                      bedrooms: (property as any).details?.bedrooms || 0,
                      bathrooms: (property as any).details?.bathrooms || 0,
                      squareFootage: squareFootage, // Changed from square_feet to squareFootage to match type
                      square_feet: squareFootage, // Keep for backward compatibility
                    },
                    squareFootage: squareFootage,
                    yearBuilt: (property as any).year_built || new Date().getFullYear(),
                    mainImage: mainImage,
                    images: images,
                    features: (property as any).features || [],
                    address: (property as any).address || `${normalizeName(property.city) || ''} ${normalizeName(property.state) || ''}`.trim(),
                    coordinates: {
                      lat: property.latitude || 0,
                      lng: property.longitude || 0
                    },
                    datePosted: property.created_at || new Date().toISOString(),
                    contact: {
                      name: (property as any).contact?.name || 'Agent',
                      phone: (property as any).contact?.phone || '',
                      email: (property as any).contact?.email || ''
                    },
                    property_type: property.property_type || 'apartment',
                    listing_type: (property.listing_type === 'rent' || property.listing_type === 'sale'
                      ? property.listing_type
                      : 'sale') as 'rent' | 'sale',
                    square_feet: property.square_feet || 0,
                    is_featured: (property as any).is_featured || false,
                    status: (property as any).status || 'active',
                    created_at: property.created_at || new Date().toISOString(),
                    updated_at: (property as any).updated_at || new Date().toISOString(),
                    user_id: (property as any).user_id || 0,
                    media: Array.isArray(property.media) ? property.media : [],
                    city: normalizeName(property.city) || '',
                    state: normalizeName(property.state) || '',
                    postal_code: (property as any).postal_code || '',
                  };

                  return (
                    <div
                      key={mappedProperty.id.toString()}
                      className={`
                        ${viewMode === 'grid' 
                          ? 'transform transition-all duration-300 hover:scale-105 hover:shadow-xl h-full bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 hover:border-blue-300'
                          : 'w-full'
                        }
                      `}
                    >
                      <PropertyCard
                        property={mappedProperty}
                        view={viewMode}
                        useGallery={true}
                      />
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default Search;
