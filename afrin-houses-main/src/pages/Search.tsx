import React, { useEffect, useState, useCallback, useRef, useMemo } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { ExtendedProperty, Property, SearchFilters as SearchFiltersType } from '../types';
import SearchFilters from '../components/SearchFilters';
import { Button } from '../components/ui/button';
import { LayoutGrid, List, Loader2, X, MapPin } from 'lucide-react';
import { useApp } from '../context/AppContext';
import PropertyCard from '../components/PropertyCard';

// Define types for better type safety

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
  const { state, loadProperties, filterProperties } = useApp();
  
  // Helper to normalize values that may be localized objects { name_ar, name_en, name_ku }
  const normalizeName = (val: any): string => {
    if (!val) return '';
    if (typeof val === 'string') return val;
    if (typeof val === 'object') {
      const currentLang = i18n.language;
      const ar = (val as any).name_ar ?? (val as any).ar ?? (val as any).name;
      const en = (val as any).name_en ?? (val as any).en;
      const ku = (val as any).name_ku ?? (val as any).ku;
      
      // Priority order: current language > English > Arabic > Kurdish > any available
      if (currentLang === 'ar' && ar) return ar;
      if (currentLang === 'en' && en) return en;
      if (currentLang === 'ku' && ku) return ku;
      
      // Fallback priority: English > Arabic > Kurdish > any available
      return en || ar || ku || (val as any).name || '';
    }
    return String(val);
  };
  const { properties: allProperties, filteredProperties: contextFilteredProperties, loading, error } = state;
  const [activeFilters, setActiveFilters] = useState<SearchFiltersType>({});
  const [currentParams, setCurrentParams] = useState<URLSearchParams>(new URLSearchParams());
  const [newParams, setNewParams] = useState<URLSearchParams>(new URLSearchParams());

  // Convert Property to ExtendedProperty - stable function to prevent re-renders
  const toExtendedProperty = useMemo(() => {
    return (property: Property | ExtendedProperty): ExtendedProperty => {
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
                '/images/placeholder-property.svg'
    };

      return extendedProperty;
    };
  }, []);

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

    // Handle Phase 1 fields
    if (params.minFloorNumber) {
        const minFloor = Number(params.minFloorNumber);
        if (!isNaN(minFloor)) filters.minFloorNumber = minFloor;
    }

    if (params.maxFloorNumber) {
        const maxFloor = Number(params.maxFloorNumber);
        if (!isNaN(maxFloor)) filters.maxFloorNumber = maxFloor;
    }

    if (params.minTotalFloors) {
        const minTotal = Number(params.minTotalFloors);
        if (!isNaN(minTotal)) filters.minTotalFloors = minTotal;
    }

    if (params.maxTotalFloors) {
        const maxTotal = Number(params.maxTotalFloors);
        if (!isNaN(maxTotal)) filters.maxTotalFloors = maxTotal;
    }

    if (params.minBalconyCount) {
        const minBalcony = Number(params.minBalconyCount);
        if (!isNaN(minBalcony)) filters.minBalconyCount = minBalcony;
    }

    if (params.maxBalconyCount) {
        const maxBalcony = Number(params.maxBalconyCount);
        if (!isNaN(maxBalcony)) filters.maxBalconyCount = maxBalcony;
    }

    if (params.orientation) {
        filters.orientation = params.orientation;
    }

    if (params.viewType) {
        filters.viewType = params.viewType;
    }
    
    return filters;
  };



  // Get filters from URL parameters with memoization
  const [filters, setFilters] = useState<SearchFiltersType>(() => {
    const urlFilters: URLFilterType = {};
    
    // Extract all parameters from URL
    searchParams.forEach((value, key) => {
      urlFilters[key as keyof URLFilterType] = value;
    });
    
    // Convert URL params to SearchFilters
    return urlParamsToSearchFilters(urlFilters);
  });

  // Create a stable hash for filters to prevent unnecessary re-renders
  const filtersHash = useMemo(() => {
    const keys = Object.keys(filters).sort();
    return keys.map(key => `${key}:${filters[key as keyof SearchFiltersType]}`).join('|');
  }, [filters]);
  
  // Memoize the active filters using stable hash
  const memoizedFilters = useMemo(() => filters, [filtersHash]);
  
  // Store filters from URL params separately for reference
  const filtersFromParams = useMemo(() => {
    const urlFilters: URLFilterType = {};
    searchParams.forEach((value, key) => {
      urlFilters[key as keyof URLFilterType] = value;
    });
    return urlParamsToSearchFilters(urlFilters);
  }, [searchParams]);

  // Stable filter properties function - optimized with memoization
  const stableFilterProperties = useCallback((properties: ExtendedProperty[], filters: SearchFiltersType): ExtendedProperty[] => {
    return properties.filter(property => {
      // Search filter
      if (filters.search) {
        const searchTerm = filters.search.toLowerCase();
        const searchableText = [
          property.title,
          property.description,
          property.location,
          property.details
        ].join(' ').toLowerCase();
        
        if (!searchableText.includes(searchTerm)) {
          return false;
        }
      }

      // Property type filter
      if (filters.propertyType && filters.propertyType.length > 0) {
        if (!filters.propertyType.includes(property.type)) {
          return false;
        }
      }

      // Listing type filter
      if (filters.listingType && filters.listingType !== 'all') {
        if (property.listingType !== filters.listingType) {
          return false;
        }
      }

      // Price range filter
      if (filters.minPrice !== undefined && property.price < filters.minPrice) {
        return false;
      }
      if (filters.maxPrice !== undefined && property.price > filters.maxPrice) {
        return false;
      }

      // Bedrooms filter
      if (filters.bedrooms !== undefined && property.bedrooms < filters.bedrooms) {
        return false;
      }

      // Bathrooms filter
      if (filters.bathrooms !== undefined && property.bathrooms < filters.bathrooms) {
        return false;
      }

      // Square footage filter
      if (filters.minSquareFootage !== undefined && property.squareFootage < filters.minSquareFootage) {
        return false;
      }
      if (filters.maxSquareFootage !== undefined && property.squareFootage > filters.maxSquareFootage) {
        return false;
      }

      // Floor number filter
      if (filters.minFloorNumber !== undefined && property.floorNumber !== undefined && property.floorNumber < filters.minFloorNumber) {
        return false;
      }
      if (filters.maxFloorNumber !== undefined && property.floorNumber !== undefined && property.floorNumber > filters.maxFloorNumber) {
        return false;
      }

      // Total floors filter
      if (filters.minTotalFloors !== undefined && property.totalFloors !== undefined && property.totalFloors < filters.minTotalFloors) {
        return false;
      }
      if (filters.maxTotalFloors !== undefined && property.totalFloors !== undefined && property.totalFloors > filters.maxTotalFloors) {
        return false;
      }

      // Balcony count filter
      if (filters.minBalconyCount !== undefined && property.balconyCount !== undefined && property.balconyCount < filters.minBalconyCount) {
        return false;
      }
      if (filters.maxBalconyCount !== undefined && property.balconyCount !== undefined && property.balconyCount > filters.maxBalconyCount) {
        return false;
      }

      // Orientation filter
      if (filters.orientation && property.orientation && property.orientation !== filters.orientation) {
        return false;
      }

      // View type filter
      if (filters.viewType && property.viewType && property.viewType !== filters.viewType) {
        return false;
      }

      return true;
    });
  }, []);

  // Memoized filtered and sorted properties for performance
  const filteredProperties = useMemo(() => {
    if (!contextFilteredProperties) return [];
    
    const extendedProperties = contextFilteredProperties.map(prop => toExtendedProperty(prop));
    
    // Apply filtering
    const filtered = stableFilterProperties(extendedProperties, memoizedFilters);
    
    // Apply sorting
    const sorted = [...filtered].sort((a, b) => {
      const sortBy = memoizedFilters.sortBy || 'created_at';
      const sortOrder = memoizedFilters.sortOrder || 'desc';
      
      let aValue: any, bValue: any;
      
      switch (sortBy) {
        case 'price':
          aValue = a.price;
          bValue = b.price;
          break;
        case 'created_at':
        case 'date':
          aValue = new Date(a.created_at || 0).getTime();
          bValue = new Date(b.created_at || 0).getTime();
          break;
        case 'squareFootage':
          aValue = a.squareFootage;
          bValue = b.squareFootage;
          break;
        default:
          aValue = a.price;
          bValue = b.price;
      }
      
      if (sortOrder === 'asc') {
        return aValue - bValue;
      } else {
        return bValue - aValue;
      }
    });
    
    return sorted;
  }, [contextFilteredProperties, memoizedFilters, toExtendedProperty, stableFilterProperties]);

  // Current sort value derived from filters state
  const currentSortValue = useMemo(() => {
    const by = memoizedFilters?.sortBy === 'created_at' ? 'date' : (memoizedFilters?.sortBy || 'date');
    const order = memoizedFilters?.sortOrder || 'desc';
    return `${by}-${order}`;
  }, [memoizedFilters]);

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

  // Update URL with current filters - memoized to prevent unnecessary re-renders
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



  // Handle filter changes with proper typing
  const handleFilterChange = useCallback(async (newFilters: Partial<SearchFiltersType>) => {
    console.log('Search: handleFilterChange called with:', newFilters);
    
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

    console.log('Search: Processed filters:', processedFilters);

    // Update filters state
    setFilters(prevFilters => ({
      ...prevFilters,
      ...newFilters
    }));

    // Update URL with the new parameters
    const currentFilters = { ...filters, ...newFilters };
    updateURL(currentFilters);
    
    // Call filterProperties from AppContext to fetch filtered data from API
    try {
      console.log('Search: Calling filterProperties with:', processedFilters);
      await filterProperties(processedFilters);
    } catch (error) {
      console.error('Search: Error filtering properties:', error);
    }
  }, [filters, updateURL, filterProperties]);

  // Handle pagination
  const handlePageChange = useCallback((page: number) => {
    // Create a new filters object with the updated page number
    const newFilters: SearchFiltersType = { 
      ...filters,
      page: page,
    };
    
    // Update URL which will trigger re-filtering through useMemo
    updateURL(newFilters);
  }, [filters, updateURL]);


  // Track initial mount for logging purposes
  useEffect(() => {
    if (isInitialMount.current) {
      isInitialMount.current = false;
      
      // Log initial filters for debugging
      if (Object.keys(filtersFromParams).length > 0) {
        console.log('Initial filters from URL:', filtersFromParams);
      } else {
        console.log('No initial filters in URL');
      }
    }
  }, [filtersFromParams]);

  // Loading state component
  const LoadingState: React.FC = () => (
    <div className="flex flex-col items-center justify-center py-8 sm:py-12">
      <Loader2 className="h-8 w-8 sm:h-10 sm:w-10 animate-spin text-[#067977] mb-3 sm:mb-4" />
      <p className="text-gray-600 text-sm sm:text-base">يتم تحميل العقارات...</p>
    </div>
  );



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
    <div className="container mx-auto px-2 sm:px-4 py-4 sm:py-6">
      <div className="mb-4 sm:mb-6">
        <div className="flex flex-col md:flex-row gap-3 sm:gap-4 lg:gap-6">
          {/* Filters Sidebar */}
          <div className="w-full md:w-1/4">
            <SearchFilters
              initialFilters={filtersFromParams}
              onApplyFilters={handleFilterChange}
            />
          </div>
          
          {/* Results Section */}
          <div className="w-full md:w-3/4">
            <div className="flex justify-between items-center mb-3 sm:mb-4 lg:mb-6">
              <div className="flex items-center gap-2 sm:gap-3 lg:gap-4">
                <Button
                  onClick={() => navigate('/search/map')}
                  variant="outline"
                  size="sm"
                  className="flex items-center space-x-1 sm:space-x-2 bg-[#067977]/10 border-[#067977]/30 text-[#067977] hover:bg-[#067977]/20 px-2 sm:px-3 py-1 sm:py-2 text-xs sm:text-sm"
                >
                  <MapPin className="h-3 w-3 sm:h-4 sm:w-4" />
                  <span className="hidden sm:inline font-medium">{t('search.mapSearch')}</span>
                </Button>
                <div className="flex items-center bg-gray-100 rounded-md p-0.5 sm:p-1">
                  <Button
                    variant={viewMode === 'grid' ? 'default' : 'ghost'}
                    size="sm"
                    onClick={() => setViewMode('grid')}
                    className={`flex items-center space-x-1 sm:space-x-2 transition-all duration-200 px-1.5 sm:px-2 py-1 text-xs sm:text-sm ${
                      viewMode === 'grid' 
                        ? 'bg-white shadow-sm text-gray-900' 
                        : 'text-gray-600 hover:text-gray-900'
                    }`}
                    aria-label="Grid view"
                  >
                    <LayoutGrid className="h-3 w-3 sm:h-4 sm:w-4" />
                    <span className="hidden sm:inline font-medium">{t('search.view.grid')}</span>
                  </Button>
                  <Button
                    variant={viewMode === 'list' ? 'default' : 'ghost'}
                    size="sm"
                    onClick={() => setViewMode('list')}
                    className={`flex items-center space-x-1 sm:space-x-2 transition-all duration-200 px-1.5 sm:px-2 py-1 text-xs sm:text-sm ${
                      viewMode === 'list' 
                        ? 'bg-white shadow-sm text-gray-900' 
                        : 'text-gray-600 hover:text-gray-900'
                    }`}
                    aria-label="List view"
                  >
                    <List className="h-3 w-3 sm:h-4 sm:w-4" />
                    <span className="hidden sm:inline font-medium">{t('search.view.list')}</span>
                  </Button>
                </div>
              </div>
              <div className="w-48 sm:w-56 lg:w-64">
                <select
                  className="w-full p-1.5 sm:p-2 border rounded text-xs sm:text-sm"
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

            {loading ? (
              <LoadingState />
            ) : filteredProperties.length === 0 ? (
              <div className="text-center py-6 sm:py-8 lg:py-12">
                <div className="mb-4 sm:mb-6">
                  <div className="w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 mx-auto mb-3 sm:mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <svg className="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                  </div>
                  <h3 className="text-lg sm:text-xl font-semibold text-gray-900 mb-1 sm:mb-2">
                    {filtersFromParams.location && filtersFromParams.location.includes(',') ? (
                      (() => {
                        const locationParts = filtersFromParams.location.split(',').map(part => part.trim());
                        const [city, state] = locationParts;
                        return t('search.messages.noPropertiesInCityState', { city, state });
                      })()
                    ) : filtersFromParams.location ? (
                      t('search.messages.noPropertiesInLocation', { location: filtersFromParams.location })
                    ) : (
                      t('search.noResults')
                    )}
                  </h3>
                  <p className="text-gray-600 mb-3 sm:mb-4 text-sm sm:text-base">
                    {filtersFromParams.location ? (
                      t('search.messages.tryDifferentLocation')
                    ) : (
                      t('search.tryAdjustingFilters')
                    )}
                  </p>
                  <div className="flex flex-col sm:flex-row gap-2 sm:gap-3 justify-center">
                    <Button 
                      onClick={() => {
                        handleFilterChange({ location: '' });
                      }}
                      variant="outline"
                      className="px-3 sm:px-4 lg:px-6 py-1.5 sm:py-2 text-xs sm:text-sm"
                    >
                      {t('search.messages.clearLocationFilter')}
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
                      className="px-3 sm:px-4 lg:px-6 py-1.5 sm:py-2 bg-[#067977] hover:bg-[#067977]/90 text-xs sm:text-sm"
                    >
                      {t('search.messages.clearAllFilters')}
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
            ) : (
              <div className={`
                ${viewMode === 'grid' 
                  ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 lg:gap-6' 
                  : 'flex flex-col space-y-2 sm:space-y-3 lg:space-y-4'
                }
              `}>
                {filteredProperties.map((property, index) => {
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

                  // Ensure unique ID for React keys
                  const uniqueId = property.id || `property-${index}-${Date.now()}`;

                  const mappedProperty: ExtendedProperty = {
                    ...property,
                    id: uniqueId,
                    slug: (property as any).slug || `property-${uniqueId}`,
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
                      key={`${uniqueId}-${index}`}
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
