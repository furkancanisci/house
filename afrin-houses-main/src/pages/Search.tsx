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
  const { state, filterProperties, loadProperties } = useApp();
  
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
    
    return filters;
  };

  // Sync filtered properties from context
  useEffect(() => {
    if (contextFilteredProperties && contextFilteredProperties.length > 0) {
      const extendedProperties = contextFilteredProperties.map(prop => toExtendedProperty(prop));
      setFilteredProperties(extendedProperties);
    } else if (allProperties && allProperties.length > 0) {
      // If no filters applied, show all properties
      const extendedProperties = allProperties.map(prop => toExtendedProperty(prop));
      setFilteredProperties(extendedProperties);
    }
  }, [contextFilteredProperties, allProperties]);

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
      zipCode: property.zip_code || '',
      // Ensure required fields are present
      city: property.city || '',
      state: property.state || '',
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
      console.log('No properties available to filter');
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
    
    console.log('Total properties before filtering:', extendedProperties.length);
    console.log('Sample property bathrooms:', extendedProperties[0]?.bathrooms);
    
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
        const bedroomsFilter = String(filters.bedrooms);
        if (bedroomsFilter.endsWith('+')) {
          // Handle 'N+' case - show properties with N or more bedrooms
          const minBedrooms = parseInt(bedroomsFilter);
          if (!isNaN(minBedrooms)) {
            result = result.filter(property => 
              (property.bedrooms || 0) >= minBedrooms
            );
          }
        } else {
          // Handle exact number case
          const exactBedrooms = parseInt(bedroomsFilter);
          if (!isNaN(exactBedrooms)) {
            result = result.filter(property => 
              (property.bedrooms || 0) === exactBedrooms
            );
          }
        }
      }

      // Apply bathroom filter
      if (filters.bathrooms) {
        console.log('Filtering by bathrooms:', filters.bathrooms);
        const bathroomsFilter = String(filters.bathrooms);
        
        // Log all property bathrooms for debugging
        console.log('All property bathrooms:', result.map(p => p.bathrooms));
        
        if (bathroomsFilter.endsWith('+')) {
          // Handle 'N+' case - show properties with N or more bathrooms
          const minBathrooms = parseInt(bathroomsFilter);
          console.log('Filtering for min bathrooms:', minBathrooms);
          
          if (!isNaN(minBathrooms)) {
            result = result.filter(property => {
              const propertyBathrooms = property.bathrooms || 0;
              console.log(`Property ${property.id} has ${propertyBathrooms} bathrooms`);
              return propertyBathrooms >= minBathrooms;
            });
            console.log(`After filtering for ${minBathrooms}+ bathrooms:`, result.length, 'properties');
          }
        } else {
          // Handle exact number case
          const exactBathrooms = parseInt(bathroomsFilter);
          console.log('Filtering for exact bathrooms:', exactBathrooms);
          
          if (!isNaN(exactBathrooms)) {
            result = result.filter(property => {
              const propertyBathrooms = property.bathrooms || 0;
              console.log(`Property ${property.id} has ${propertyBathrooms} bathrooms`);
              return propertyBathrooms === exactBathrooms;
            });
            console.log(`After filtering for exactly ${exactBathrooms} bathrooms:`, result.length, 'properties');
          }
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

  // Define filter change handler type
  interface IFilterChangeHandler {
    (filters: Partial<SearchFiltersType>): void;
  }

  // Handle filter changes - single implementation
  const handleFilterChange: IFilterChangeHandler = useCallback((newFilters) => {
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
    
    // Update filters state and apply them
    setFilters(processedFilters);
    applyFilters(processedFilters);
    updateURL(processedFilters);
    
    // Update filters in the context
    filterProperties(processedFilters);
    
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
    
    navigate(`?${params.toString()}`, { replace: true });
  }, [filterProperties, navigate]);

  // Handle pagination
  const handlePageChange = useCallback((page: number) => {
    // Create a new filters object with the updated page number
    const newFilters: SearchFiltersType = { 
      ...filtersFromParams,
      page: page,
    };
    
    filterProperties(newFilters);
  }, [filtersFromParams, filterProperties]);

  // Load properties when component mounts or filters change
  useEffect(() => {
    if (allProperties.length === 0) {
      console.log('No properties available yet, waiting for AppContext to load them...');
      return;
    }

    if (isInitialMount.current) {
      isInitialMount.current = false;
    }

    // Get filters from URL
    const urlParams = new URLSearchParams(window.location.search);
    const urlFilters: any = {};
    urlParams.forEach((value, key) => {
      urlFilters[key] = value;
    });

    if (Object.keys(urlFilters).length > 0) {
      console.log('Applying filters from URL:', urlFilters);
      applyFilters(urlFilters);
    } else {
      console.log('No filters in URL, showing all properties');
      setFilteredProperties(allProperties.map(toExtendedProperty));
    }
  }, [allProperties, applyFilters, toExtendedProperty]);

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

        <div className="flex flex-col md:flex-row gap-6">
          {/* Filters Sidebar */}
          <div className="w-full md:w-1/4">
            <SearchFilters 
              key={JSON.stringify(filters)} // Force re-render when filters change
              onFiltersChange={handleFilterChange}
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
                        filters: filtersFromParams,
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
