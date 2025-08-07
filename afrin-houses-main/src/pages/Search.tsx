import React, { useEffect, useState, useCallback, useRef, useMemo } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { getProperties } from '../services/propertyService';
import PropertyCard from '../components/PropertyCard';
import SearchFilters from '../components/SearchFilters';
import { useTranslation } from 'react-i18next';
import { ExtendedProperty, SearchFilters as SearchFiltersType } from '../types';
import { Button } from '../components/ui/button';
import { LayoutGrid, List, Loader2, X } from 'lucide-react';
import { processPropertyImages } from '../lib/imageUtils';

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
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [properties, setProperties] = useState<ExtendedProperty[]>([]);
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
  const previousFilters = useRef<SearchFiltersType | null>(null);
  const isInitialMount = useRef(true);

  // Get search params from URL
  const [searchParams] = useSearchParams();
  const filtersFromParams = useMemo<SearchFiltersType>(() => {
    const filters: any = {}; // Use any here to bypass TypeScript errors for dynamic properties

    // Parse filters from URL params
    searchParams.forEach((value, key) => {
      if (key === 'q') {
        // Handle search query
        filters.searchQuery = value;
      } else if (['minPrice', 'maxPrice', 'bedrooms', 'bathrooms', 'minSquareFootage', 'maxSquareFootage'].includes(key)) {
        // Handle numeric filters
        const numValue = parseFloat(value);
        if (!isNaN(numValue)) {
          filters[key] = numValue;
        }
      } else if (key === 'features' && value) {
        // Handle features array
        filters.features = value.split(',').filter(Boolean);
      } else if (key === 'propertyType' || key === 'listingType' || key === 'location') {
        // Handle string filters
        filters[key] = value;
      }
    });

    return filters as SearchFiltersType;
  }, [searchParams]);

  const haveFiltersChanged = useCallback((newFilters: SearchFiltersType): boolean => {
    if (!previousFilters.current) return true;

    // Check if any filter values have changed
    return Object.keys(newFilters).some(key => {
      const currentValue = newFilters[key as keyof SearchFiltersType];
      const previousValue = previousFilters.current?.[key as keyof SearchFiltersType];

      // Special handling for arrays to compare their stringified versions
      if (Array.isArray(currentValue)) {
        return JSON.stringify(currentValue) !== JSON.stringify(previousValue);
      }
      return currentValue !== previousValue;
    });
  }, []);

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

  const fetchProperties = useCallback(async (filters: SearchFiltersType, page = 1) => {
    try {
      setLoading(true);
      setError(null);

      // Create a new object with only defined properties
      const queryParams: Record<string, any> = {
        ...filters,
        page,
        perPage: 10
      };

      // Remove undefined values
      Object.keys(queryParams).forEach(key =>
        queryParams[key] === undefined && delete queryParams[key]
      );

      const response = await getProperties(queryParams as SearchFiltersType);

      if (!response) throw new Error('No response received from server');

      let propertiesData: ExtendedProperty[] = [];
      if (Array.isArray(response)) {
        propertiesData = response;
      } else if (response?.data?.data && Array.isArray(response.data.data)) {
        propertiesData = response.data.data;
      }

      setProperties(propertiesData);
    } catch (err) {
      console.error('Error fetching properties:', err);
      setError('Failed to fetch properties. Please try again.');
      setProperties([]);
    } finally {
      setLoading(false);
    }
  }, []);

  // Handle filter changes
  const handleFilterChange = useCallback((newFilters: SearchFiltersType) => {
    const updatedFilters: SearchFiltersType = {
      ...filtersFromParams,
      ...newFilters
    };

    // Only update if filters have actually changed
    if (haveFiltersChanged(updatedFilters)) {
      previousFilters.current = updatedFilters;
      fetchProperties(updatedFilters);
    }
  }, [filtersFromParams, fetchProperties, haveFiltersChanged]);

  // Handle pagination
  const handlePageChange = useCallback((page: number) => {
    fetchProperties(filtersFromParams, page);
  }, [filtersFromParams, fetchProperties]);

  // Update URL when filters change
  useEffect(() => {
    if (Object.keys(filtersFromParams).length > 0) {
      updateURL(filtersFromParams);
    }
  }, [filtersFromParams, updateURL]);

  // Initial data fetch
  useEffect(() => {
    if (isInitialMount.current) {
      isInitialMount.current = false;

      // Only fetch if we have filters to apply
      if (Object.keys(filtersFromParams).length > 0) {
        fetchProperties(filtersFromParams);
      }
    }
  }, [filtersFromParams, fetchProperties]);

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
          {properties.length > 0 
            ? t('search.resultsCount', { count: properties.length })
            : t('search.noResults')}
        </h1>

        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div className="md:col-span-1">
            <SearchFilters
              initialFilters={filtersFromParams}
              onFiltersChange={handleFilterChange}
            />
          </div>
          <div className="md:col-span-3">
            <div className="flex justify-between items-center mb-6">
              <p className="text-gray-600">
                {t('search.showingCount', { count: properties.length })}
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

                    const newFilters = {
                      ...filtersFromParams,
                      sortBy: sortBy === 'date' ? 'created_at' : sortBy,
                      sortOrder, // literal tip garantili
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

            {properties.length === 0 ? (
              <div className="text-center py-12">
                <h3 className="text-lg font-medium text-gray-900">{t('search.noResults')}</h3>
                <p className="mt-2 text-sm text-gray-500">{t('search.tryAdjustingFilters')}</p>
              </div>
            ) : (
              <div className="grid grid-cols-1 gap-6">
                {properties.map((property) => {
                  const price = typeof property.price === 'object'
                    ? Number((property.price as any)?.amount) || 0
                    : Number(property.price) || 0;

                  // Get square footage from various possible locations in the API response
                  const squareFootage = 
                    property.square_feet || 
                    (property as any).details?.square_feet || 
                    (property as any).square_footage || 
                    (property as any).details?.square_footage || 
                    0;

                  // Process images to ensure we have proper images
                  const { mainImage, images } = processPropertyImages(property, property.property_type || 'apartment');

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
                      square_feet: squareFootage, // Ensure square footage is included in details
                    },
                    squareFootage: squareFootage,
                    yearBuilt: (property as any).year_built || new Date().getFullYear(),
                    mainImage: mainImage,
                    images: images,
                    features: (property as any).features || [],
                    address: (property as any).address || `${property.city || ''} ${property.state || ''}`.trim(),
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
                    city: property.city || '',
                    state: property.state || '',
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
