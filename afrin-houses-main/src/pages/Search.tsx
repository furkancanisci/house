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
  const [filteredProperties, setFilteredProperties] = useState<ExtendedProperty[]>([]);
  const [activeFilters, setActiveFilters] = useState<SearchFiltersType>({});
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
      country: property.country || '',
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

  // Apply filters to properties from context
  const applyFilters = useCallback((filters: SearchFiltersType) => {
    if (!allProperties || allProperties.length === 0) {
      setFilteredProperties([]);
      return;
    }

    console.log('Applying filters:', filters);
    
    // Convert all properties to ExtendedProperty format first
    const extendedProperties = allProperties.map(prop => {
      try {
        return toExtendedProperty(prop);
      } catch (error) {
        console.error('Error converting property:', prop, error);
        return null;
      }
    }).filter((prop): prop is ExtendedProperty => prop !== null);
    
    try {
      let result = [...extendedProperties];

      // Apply search query filter
      if (filters.search || filters.searchQuery) {
        const searchTerm = (filters.search || filters.searchQuery || '').toLowerCase();
        result = result.filter(property => {
          const title = property.title || '';
          const description = property.description || '';
          const address = property.address || '';
          
          return title.toLowerCase().includes(searchTerm) ||
                 description.toLowerCase().includes(searchTerm) ||
                 address.toLowerCase().includes(searchTerm);
        });
      }

      // Apply property type filter
      if (filters.propertyType && filters.propertyType !== 'all') {
        result = result.filter(property => 
          property.propertyType?.toLowerCase() === filters.propertyType?.toLowerCase()
        );
      }

      // Apply listing type filter
      if (filters.listingType && filters.listingType !== 'all') {
        result = result.filter(property => 
          property.listingType?.toLowerCase() === filters.listingType?.toLowerCase()
        );
      }

      // Apply price range filter
      if (filters.minPrice !== undefined) {
        const minPrice = Number(filters.minPrice);
        if (!isNaN(minPrice)) {
          result = result.filter(property => {
            const price = typeof property.price === 'string' 
              ? parseFloat(property.price) 
              : Number(property.price);
            return price >= minPrice;
          });
        }
      }
      
      if (filters.maxPrice !== undefined) {
        const maxPrice = Number(filters.maxPrice);
        if (!isNaN(maxPrice)) {
          result = result.filter(property => {
            const price = typeof property.price === 'string' 
              ? parseFloat(property.price) 
              : Number(property.price);
            return price <= maxPrice;
          });
        }
      }

      // Apply bedroom filter
      if (filters.bedrooms) {
        const minBedrooms = Number(filters.bedrooms);
        if (!isNaN(minBedrooms)) {
          result = result.filter(property => 
            (property.bedrooms || 0) >= minBedrooms
          );
        }
      }

      // Apply bathroom filter
      if (filters.bathrooms) {
        const minBathrooms = Number(filters.bathrooms);
        if (!isNaN(minBathrooms)) {
          result = result.filter(property => 
            (property.bathrooms || 0) >= minBathrooms
          );
        }
      }

      // Apply square footage filters
      if (filters.minSquareFootage) {
        const minSqft = Number(filters.minSquareFootage);
        if (!isNaN(minSqft)) {
          result = result.filter(property => 
            (property.squareFootage || 0) >= minSqft
          );
        }
      }

      if (filters.maxSquareFootage) {
        const maxSqft = Number(filters.maxSquareFootage);
        if (!isNaN(maxSqft)) {
          result = result.filter(property => 
            (property.squareFootage || 0) <= maxSqft
          );
        }
      }

      // Apply features filter
      if (filters.features && filters.features.length > 0) {
        result = result.filter(property => {
          const propertyFeatures = property.features || [];
          return filters.features?.every(feature => 
            propertyFeatures.includes(feature)
          );
        });
      }

      // Apply location filter
      if (filters.location) {
        const locationTerm = filters.location.toLowerCase();
        result = result.filter(property => {
          const address = property.address || '';
          const city = property.city || '';
          const state = property.state || '';
          const zipCode = property.zipCode || '';
          
          return address.toLowerCase().includes(locationTerm) ||
                 city.toLowerCase().includes(locationTerm) ||
                 state.toLowerCase().includes(locationTerm) ||
                 zipCode.includes(locationTerm);
        });
      }

      // Apply sorting
      if (filters.sortBy) {
        const sortBy = filters.sortBy === 'date' ? 'created_at' : filters.sortBy;
        const sortOrder = filters.sortOrder || 'desc';
        
        result.sort((a, b) => {
          let aValue: any;
          let bValue: any;
          
          if (sortBy === 'price') {
            aValue = typeof a.price === 'string' ? parseFloat(a.price) : Number(a.price);
            bValue = typeof b.price === 'string' ? parseFloat(b.price) : Number(b.price);
          } else if (sortBy === 'created_at') {
            aValue = new Date(a.created_at || 0).getTime();
            bValue = new Date(b.created_at || 0).getTime();
          } else {
            aValue = a[sortBy as keyof ExtendedProperty];
            bValue = b[sortBy as keyof ExtendedProperty];
          }
          
          if (aValue === bValue) return 0;
          if (aValue == null) return sortOrder === 'asc' ? -1 : 1;
          if (bValue == null) return sortOrder === 'asc' ? 1 : -1;
          
          if (aValue < bValue) return sortOrder === 'asc' ? -1 : 1;
          if (aValue > bValue) return sortOrder === 'asc' ? 1 : -1;
          return 0;
        });
      }

      console.log(`Filtered ${result.length} properties`);
      setFilteredProperties(result);
    } catch (error) {
      console.error('Error applying filters:', error);
      // Fallback to showing all properties if there's an error
      setFilteredProperties(extendedProperties || []);
    }
  }, [allProperties, toExtendedProperty]);

  // Handle filter changes - single implementation
  const handleFilterChange = useCallback((newFilters: Partial<SearchFiltersType>) => {
    const currentParams = new URLSearchParams(window.location.search);

    // Create a new set of parameters
    const newParams = new URLSearchParams();

    // Add all new/updated filters to the new parameters
    Object.entries(newFilters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '' && value !== 'any' && value !== 'all') {
        if (Array.isArray(value) && value.length > 0) {
          newParams.set(key, value.join(','));
        } else if (!Array.isArray(value)) {
          newParams.set(key, String(value));
        }
      } else {
        // Remove the key if the value is empty/undefined
        newParams.delete(key);
      }
    });

    // Preserve existing params that are not part of the new filter set
    currentParams.forEach((value, key) => {
      if (!newFilters.hasOwnProperty(key)) {
        newParams.set(key, value);
      }
    });

    navigate(`?${newParams.toString()}`, { replace: true });
  }, [navigate]);

  // Handle pagination
  const handlePageChange = useCallback((page: number) => {
    const newFilters: SearchFiltersType = { 
      ...filtersFromParams,
      page: page,
    };
    handleFilterChange(newFilters);
  }, [filtersFromParams, handleFilterChange]);

  // Apply filters whenever allProperties or searchParams change
  useEffect(() => {
    if (allProperties.length === 0) {
      return;
    }

    const urlFilters: URLFilterType = {};
    searchParams.forEach((value, key) => {
      urlFilters[key as keyof URLFilterType] = value;
    });

    const filtersToApply = urlParamsToSearchFilters(urlFilters);
    setActiveFilters(filtersToApply);

    if (Object.keys(filtersToApply).length > 0) {
      applyFilters(filtersToApply);
    } else {
      setFilteredProperties(allProperties.map(toExtendedProperty));
    }
  }, [allProperties, searchParams, applyFilters, toExtendedProperty]);

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <Loader2 className="h-8 w-8 animate-spin text-blue-600" />
        <span className="ml-2">Loading properties...</span>
      </div>
    );
  }

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
          {filteredProperties.length > 0 
            ? t('search.resultsCount', { count: filteredProperties.length })
            : t('search.noResults')}
        </h1>

        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div className="md:col-span-1">
            <SearchFilters
              initialFilters={filtersFromParams}
              onApplyFilters={handleFilterChange}
            />
          </div>
          <div className="md:col-span-3">
            <div className="flex justify-between items-center mb-6">
              <p className="text-gray-600">
                {t('search.showingCount', { count: filteredProperties.length })}
              </p>
              <div className="flex items-center gap-2">
                <Button
                  variant={viewMode === 'grid' ? 'default' : 'outline'}
                  size="icon"
                  onClick={() => setViewMode('grid')}
                  aria-label="Grid view"
                >
                  <LayoutGrid className="h-5 w-5" />
                </Button>
                <Button
                  variant={viewMode === 'list' ? 'default' : 'outline'}
                  size="icon"
                  onClick={() => setViewMode('list')}
                  aria-label="List view"
                >
                  <List className="h-5 w-5" />
                </Button>
              </div>
              <div className="w-64">
                <select
                  className="w-full p-2 border rounded"
                  value={searchParams.get('sort') || 'date-desc'}
                  onChange={(e) => {
                    const sortValue = e.target.value;
                    const [sortBy, sortOrder] = sortValue.split('-') as ['price' | 'date' | 'sqft', 'asc' | 'desc'];

                    // Map the sortBy value to match the expected SearchFilters type
                    let mappedSortBy: 'price' | 'created_at' | 'date' = 'created_at';
                    if (sortBy === 'price') {
                      mappedSortBy = 'price';
                    } else if (sortBy === 'date') {
                      mappedSortBy = 'created_at';
                    }

                    const newFilters: SearchFiltersType = {
                      ...filtersFromParams,
                      sortBy: mappedSortBy,
                      sortOrder: sortOrder as 'asc' | 'desc',
                    };

                    handleFilterChange(newFilters);
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
                <h3 className="text-lg font-medium text-gray-900">{t('search.noResults')}</h3>
                <p className="mt-2 text-sm text-gray-500">
                  {t('search.tryAdjustingFilters')}
                  <br />
                  <span className="text-xs text-gray-400">
                    {t('search.debugInfo', { count: filteredProperties.length })}
                  </span>
                </p>
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
            ) : (
              <div className="grid grid-cols-1 gap-6">
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
                    <PropertyCard
                      key={mappedProperty.id.toString()}
                      property={mappedProperty}
                      view={viewMode}
                      useGallery={true}
                    />
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
