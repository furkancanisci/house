import React, { useEffect, useState, useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { useApp } from '../context/AppContext';
import { ExtendedProperty, Property, SearchFilters } from '../types';
import PropertyCard from '../components/PropertyCard';
import SearchFiltersComponent from '../components/SearchFilters';
import { Button } from '../components/ui/button';
import { LayoutGrid, List, Loader2, Key, Filter } from 'lucide-react';

/**
 * Houses for Rent Page Component
 * 
 * This component displays all properties available for rent with:
 * - Modern, elegant design with professional color palette
 * - Responsive grid layout for desktop, tablet, and mobile
 * - Advanced filtering capabilities
 * - Smooth hover animations and transitions
 * - SEO-friendly structure
 */
const HousesForRent: React.FC = () => {
  const { t } = useTranslation();
  const { state } = useApp();
  const { properties, loading, error } = state;
  
  // Component state for UI interactions
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
  const [showFilters, setShowFilters] = useState(false);
  const [localFilters, setLocalFilters] = useState<SearchFilters>({
    listingType: 'rent', // Always filter for rent properties
    sortBy: 'created_at',
    sortOrder: 'desc'
  });

  // Filter properties to show only rental properties
  const rentProperties = useMemo(() => {
    if (!properties || !Array.isArray(properties)) return [];
    
    return properties.filter((property: Property) => {
      // Check both possible field names for listing type
      const listingType = property.listingType || property.listing_type;
      return listingType === 'rent';
    }).map(property => ({
      ...property,
      // Map Property fields to ExtendedProperty fields
      zipCode: property.zip_code || '',
      squareFootage: property.squareFootage || property.square_feet || 0,
      propertyType: property.propertyType || 'apartment',
      listingType: (property.listingType || 'rent') as 'rent' | 'sale'
    } as ExtendedProperty));
  }, [properties]);

  // Apply additional filters to rental properties
  const filteredProperties = useMemo(() => {
    let filtered = [...rentProperties];

    // Apply property type filter
    if (localFilters.propertyType && localFilters.propertyType !== 'all') {
      filtered = filtered.filter(property => 
        property.propertyType === localFilters.propertyType ||
        property.property_type === localFilters.propertyType
      );
    }

    // Apply price range filter
    if (localFilters.minPrice) {
      filtered = filtered.filter(property => {
        const price = typeof property.price === 'string' ? 
          parseFloat(property.price.replace(/[^0-9.-]+/g, '')) : 
          property.price;
        return price >= localFilters.minPrice!;
      });
    }

    if (localFilters.maxPrice) {
      filtered = filtered.filter(property => {
        const price = typeof property.price === 'string' ? 
          parseFloat(property.price.replace(/[^0-9.-]+/g, '')) : 
          property.price;
        return price <= localFilters.maxPrice!;
      });
    }

    // Apply bedrooms filter
    if (localFilters.bedrooms && localFilters.bedrooms > 0) {
      filtered = filtered.filter(property => property.bedrooms >= localFilters.bedrooms!);
    }

    // Apply bathrooms filter
    if (localFilters.bathrooms && localFilters.bathrooms > 0) {
      filtered = filtered.filter(property => property.bathrooms >= localFilters.bathrooms!);
    }

    // Apply features filter
    if (localFilters.features && localFilters.features.length > 0) {
      filtered = filtered.filter(property => {
        const propertyFeatures = property.features || [];
        return localFilters.features!.some(feature => 
          propertyFeatures.includes(feature)
        );
      });
    }

    // Apply sorting
    if (localFilters.sortBy) {
      filtered.sort((a, b) => {
        let aValue, bValue;
        
        switch (localFilters.sortBy) {
          case 'price':
            aValue = typeof a.price === 'string' ? 
              parseFloat(a.price.replace(/[^0-9.-]+/g, '')) : a.price;
            bValue = typeof b.price === 'string' ? 
              parseFloat(b.price.replace(/[^0-9.-]+/g, '')) : b.price;
            break;
          case 'created_at':
          case 'date':
            aValue = new Date(a.created_at || a.updated_at || '').getTime();
            bValue = new Date(b.created_at || b.updated_at || '').getTime();
            break;
          default:
            return 0;
        }

        if (localFilters.sortOrder === 'desc') {
          return bValue - aValue;
        }
        return aValue - bValue;
      });
    }

    return filtered;
  }, [rentProperties, localFilters]);

  // Handle filter changes
  const handleFilterChange = (filters: SearchFilters) => {
    setLocalFilters(prev => ({
      ...prev,
      ...filters,
      listingType: 'rent' // Always maintain rent filter
    }));
  };

  // Remove loading state that hides entire page

  // Error state
  if (error) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center">
        <div className="text-center">
          <div className="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg">
            <p className="font-medium">Error loading properties</p>
            <p className="text-sm mt-1">{error}</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">

      {/* Main Content */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Controls Bar */}
        <div className="bg-white rounded-xl shadow-lg p-6 mb-8">
          <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            {/* Results Count */}
            <div className="flex items-center space-x-4">
              <h2 className="text-lg font-semibold text-gray-900">
                {filteredProperties.length} {filteredProperties.length === 1 ? 'Property' : 'Properties'} for Rent
              </h2>
            </div>

            {/* View Controls */}
            <div className="flex items-center space-x-4">
              {/* Filter Toggle */}
              <Button
                variant={showFilters ? "default" : "outline"}
                size="sm"
                onClick={() => setShowFilters(!showFilters)}
                className="flex items-center space-x-2 transition-all duration-200 hover:scale-105"
              >
                <Filter className="h-4 w-4" />
                <span>Filters</span>
              </Button>

              {/* View Mode Toggle */}
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
                >
                  <List className="h-4 w-4" />
                  <span className="hidden sm:inline font-medium">قائمة</span>
                </Button>
              </div>
            </div>
          </div>
        </div>

        {/* Filters Panel */}
        {showFilters && (
          <div className="bg-white rounded-xl shadow-lg p-6 mb-8 transform transition-all duration-300 ease-in-out">
            <SearchFiltersComponent
              initialFilters={localFilters}
              onApplyFilters={handleFilterChange}
              hideListingType={true} // Hide listing type since we're already filtering for rent
            />
          </div>
        )}

        {/* Properties Grid/List */}
        {loading ? (
          <div className="flex flex-col items-center justify-center py-16">
            <Loader2 className="h-12 w-12 animate-spin text-blue-600 mb-4" />
            <p className="text-gray-600 text-lg">جاري تحميل العقارات للإيجار...</p>
            <p className="text-gray-500 text-sm mt-2">يرجى الانتظار قليلاً</p>
          </div>
        ) : filteredProperties.length > 0 ? (
          <div className={`
            ${viewMode === 'grid' 
              ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6' 
              : 'flex flex-col space-y-4'
            }
          `}>
            {filteredProperties.map((property) => (
              <div
                key={property.id}
                className={`
                  ${viewMode === 'grid' 
                    ? 'transform transition-all duration-300 hover:scale-105 hover:shadow-xl h-full bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 hover:border-blue-300'
                    : 'w-full'
                  }
                `}
              >
                <PropertyCard
                  property={property}
                  view={viewMode}
                />
              </div>
            ))}
          </div>
        ) : (
          /* No Properties Found */
          <div className="bg-white rounded-xl shadow-lg p-12 text-center">
            <Key className="h-16 w-16 text-gray-400 mx-auto mb-4" />
            <h3 className="text-2xl font-semibold text-gray-900 mb-2">
              No Rental Properties Found
            </h3>
            <p className="text-gray-600 mb-6 max-w-md mx-auto">
              We couldn't find any properties matching your criteria. Try adjusting your filters or check back later for new listings.
            </p>
            <Button
              onClick={() => setLocalFilters({ listingType: 'rent', sortBy: 'created_at', sortOrder: 'desc' })}
              className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-all duration-200 hover:scale-105"
            >
              Clear All Filters
            </Button>
          </div>
        )}
      </div>
    </div>
  );
};

export default HousesForRent;