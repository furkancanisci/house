import React, { useEffect, useState, useCallback, useRef, useMemo } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { getProperties, PropertyFilters } from '../services/propertyService';
import PropertyCard from '../components/PropertyCard';
import SearchFilters from '../components/SearchFilters';
import { useTranslation } from 'react-i18next';
import { ExtendedProperty } from '../types';
import { Button } from '../components/ui/button';
import { LayoutGrid, List, Loader2 } from 'lucide-react';

type ViewMode = 'grid' | 'list';

const Search: React.FC = () => {
  const [properties, setProperties] = useState<ExtendedProperty[]>([]);
  console.log(properties);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [viewMode, setViewMode] = useState<ViewMode>('grid');
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { t } = useTranslation();
  const isInitialMount = useRef(true);
  const previousFilters = useRef<PropertyFilters | null>(null);

  const filtersFromParams = useMemo(() => {
    const params = Object.fromEntries(searchParams.entries());
    const filters: PropertyFilters = {
      listingType: (params.listingType as 'rent' | 'sale' | 'all') || 'all',
      propertyType: params.propertyType || '',
      location: params.location || '',
      minPrice: params.minPrice ? Number(params.minPrice) : undefined,
      maxPrice: params.maxPrice ? Number(params.maxPrice) : undefined,
      bedrooms: params.bedrooms ? Number(params.bedrooms) : undefined,
      bathrooms: params.bathrooms ? Number(params.bathrooms) : undefined,
      minSquareFootage: params.minSquareFootage ? Number(params.minSquareFootage) : undefined,
      maxSquareFootage: params.maxSquareFootage ? Number(params.maxSquareFootage) : undefined,
      features: params.features ? params.features.split(',') : [],
      sortBy: (() => {
        const sort = params.sort;
        if (sort === 'price-asc' || sort === 'price-desc') return 'price';
        if (sort === 'date-asc' || sort === 'date-desc') return 'created_at';
        if (sort === 'sqft-asc' || sort === 'sqft-desc') return 'square_feet';
        return undefined;
      })(),
      sortOrder: (() => {
        if (params.sort?.endsWith('asc')) return 'asc';
        if (params.sort?.endsWith('desc')) return 'desc';
        return undefined;
      })(),
    };
    return filters;
  }, [searchParams]);

  const haveFiltersChanged = useCallback((newFilters: PropertyFilters): boolean => {
    if (!previousFilters.current) return true;
    return JSON.stringify(newFilters) !== JSON.stringify(previousFilters.current);
  }, []);

  const updateURL = useCallback((filters: PropertyFilters) => {
    if (!haveFiltersChanged(filters)) return;

    const params = new URLSearchParams();
    const setIfDefined = (key: string, value: any) => {
      if (value !== undefined && value !== '' && value !== null) {
        if (Array.isArray(value)) {
          if (value.length > 0) {
            params.set(key, value.join(','));
          }
        } else {
          params.set(key, String(value));
        }
      }
    };

    Object.entries(filters).forEach(([key, value]) => {
      if (key !== 'features' || (Array.isArray(value) && value.length > 0)) {
        setIfDefined(key, value);
      }
    });

    const sortParam =
      filters.sortBy && filters.sortOrder
        ? `${filters.sortBy === 'created_at' ? 'date' : filters.sortBy}-${filters.sortOrder}`
        : null;

    if (sortParam) {
      params.set('sort', sortParam);
    }

    navigate(`/search?${params.toString()}`, { replace: true });
    previousFilters.current = { ...filters };
  }, [navigate, haveFiltersChanged]);

  const fetchProperties = useCallback(async (filters: PropertyFilters) => {
    try {
      setLoading(true);
      setError(null);

      const params: PropertyFilters = {
        ...filters,
        page: 1,
        perPage: 12,
      };

      const response = await getProperties(params);
      if (!response) throw new Error('No response received from server');

      let propertiesData: any[] = [];
      if (Array.isArray(response)) {
        propertiesData = response;
      } else if (response?.data?.data && Array.isArray(response.data.data)) {
        propertiesData = response.data.data;
      }

      setProperties(propertiesData);
    } catch (err) {
      const errorMsg = err instanceof Error ? err.message : 'Failed to load properties. Please try again.';
      console.error('Error fetching properties:', err);
      setError(errorMsg);
      setProperties([]);
    } finally {
      setLoading(false);
    }
  }, []);

  const handleFilterChange = useCallback((newFilters: PropertyFilters) => {
    if (haveFiltersChanged(newFilters)) {
      updateURL(newFilters);
    }
  }, [updateURL, haveFiltersChanged]);

  useEffect(() => {
    if (isInitialMount.current || haveFiltersChanged(filtersFromParams)) {
      fetchProperties(filtersFromParams);
      previousFilters.current = { ...filtersFromParams };
      isInitialMount.current = false;
    }
  }, [filtersFromParams, fetchProperties, haveFiltersChanged]);

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[50vh]">
        <div className="flex flex-col items-center gap-4">
          <Loader2 className="h-8 w-8 animate-spin" />
          <p>{t('common.loading') || 'Loading...'}</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="container mx-auto px-4 py-8">
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
          <p>{error}</p>
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
                    },
                    squareFootage: property.square_feet || 0,
                    yearBuilt: (property as any).year_built || new Date().getFullYear(),
                    mainImage: Array.isArray(property.media) && property.media[0]?.url
                      ? property.media[0].url
                      : '/placeholder-property.jpg',
                    images: Array.isArray(property.media)
                      ? property.media.map((m: any) => m?.url).filter(Boolean)
                      : [],
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
