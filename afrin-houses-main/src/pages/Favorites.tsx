import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { Property, ExtendedProperty } from '../types';
import PropertyCard from '../components/PropertyCard';
import { 
  Heart, 
  Search, 
  Grid, 
  List,
  Filter,
  SortAsc,
  Loader2
} from 'lucide-react';
import { Button } from '../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '../components/ui/select';
import { getFavoriteProperties } from '../services/propertyService';

type ViewMode = 'grid' | 'list';
type SortOption = 'date-added' | 'price-asc' | 'price-desc' | 'title-asc';

const Favorites: React.FC = () => {
  const { state } = useApp();
  const { user } = state;
  const navigate = useNavigate();
  const [favoriteProperties, setFavoriteProperties] = useState<ExtendedProperty[]>([]);
  const [viewMode, setViewMode] = useState<ViewMode>('grid');
  const [sortBy, setSortBy] = useState<SortOption>('date-added');
  const [filterType, setFilterType] = useState<'all' | 'rent' | 'sale'>('all');
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    if (!user) {
      navigate('/auth');
      return;
    }

    const loadFavorites = async () => {
      try {
        setIsLoading(true);
  
        
        // Fetch favorite properties from the API
        const favoritesFromAPI = await getFavoriteProperties();
  
        
        // Transform the properties to ExtendedProperty format
        let favProps = favoritesFromAPI.map((property: any) => ({
        ...property,
        propertyType: property.property_type,
        listingType: property.listing_type,
        squareFootage: property.square_feet || 0,
        zipCode: property.zip_code || '',
        formattedPrice: typeof property.price === 'number' ? `$${property.price.toLocaleString()}` : property.price,
        formattedBeds: property.bedrooms ? `${property.bedrooms} ${property.bedrooms === 1 ? 'bed' : 'beds'}` : '0 beds',
        formattedBaths: property.bathrooms ? `${property.bathrooms} ${property.bathrooms === 1 ? 'bath' : 'baths'}` : '0 baths',
        formattedSquareFootage: property.square_feet ? `${property.square_feet.toLocaleString()} sq ft` : '0 sq ft',
        formattedAddress: [property.address, property.city, property.state, property.zip_code].filter(Boolean).join(', '),
        formattedPropertyType: property.property_type || 'Property',
        formattedDate: property.created_at ? new Date(property.created_at).toLocaleDateString() : 'N/A',
        isFavorite: true,
        images: property.media ? property.media.map(m => m.url) : [],
        mainImage: property.media?.find(m => m.is_featured)?.url || (property.media?.[0]?.url || ''),
        details: {
          bedrooms: property.bedrooms || 0,
          bathrooms: property.bathrooms || 0,
          squareFootage: property.square_feet || 0,
          yearBuilt: property.year_built,
        },
        slug: (property as any).slug || `property-${property.id}`,
        property_type: property.propertyType,
        listing_type: property.listingType,
        square_feet: property.square_feet || 0,
        year_built: property.year_built || new Date().getFullYear(),
        media: (property.images || []).map((url: string, index: number) => ({
          id: index,
          url,
          type: 'image'
        }))
      }));

      // Apply filter
      if (filterType !== 'all') {
        favProps = favProps.filter(p => p.listingType === filterType);
      }

      // Apply sort
      switch (sortBy) {
      case 'price-asc':
        favProps.sort((a, b) => {
          const priceA = typeof a.price === 'number' ? a.price : parseFloat(a.price as string) || 0;
          const priceB = typeof b.price === 'number' ? b.price : parseFloat(b.price as string) || 0;
          return priceA - priceB;
        });
        break;
      case 'price-desc':
        favProps.sort((a, b) => {
          const priceA = typeof a.price === 'number' ? a.price : parseFloat(a.price as string) || 0;
          const priceB = typeof b.price === 'number' ? b.price : parseFloat(b.price as string) || 0;
          return priceB - priceA;
        });
        break;
      case 'title-asc':
        favProps.sort((a, b) => (a.title || '').toString().localeCompare((b.title || '').toString()));
        break;
      case 'date-added':
      default:
        // Sort by when they were added to favorites (most recent first)
        // Fallback to current date if created_at is not available
        favProps.sort((a, b) => {
          const dateA = a.created_at ? new Date(a.created_at).getTime() : 0;
          const dateB = b.created_at ? new Date(b.created_at).getTime() : 0;
          return dateB - dateA;
        });
        break;
    }

    setFavoriteProperties(favProps);
    } catch (error) {
      setFavoriteProperties([]);
    } finally {
      setIsLoading(false);
    }
  };

    loadFavorites();
  }, [user, sortBy, filterType, navigate]);

  const getSortLabel = (option: SortOption): string => {
    const labels: Record<SortOption, string> = {
      'date-added': 'Recently Added',
      'price-asc': 'Price: Low to High',
      'price-desc': 'Price: High to Low',
      'title-asc': 'Title: A to Z',
    };
    return labels[option];
  };

  if (!user) {
    return null;
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Header */}
        <div className="mb-8">
          <div className="flex items-center space-x-2 mb-4">
            <Heart className="h-8 w-8 text-red-500" />
            <h1 className="text-3xl font-bold text-gray-900">Favorite Properties</h1>
          </div>
          <p className="text-gray-600">
            Properties you've saved for later viewing
          </p>
        </div>

        {isLoading ? (
          /* Loading State */
          <Card>
            <CardContent className="text-center py-16">
              <Loader2 className="h-16 w-16 text-gray-400 animate-spin mx-auto mb-4" />
              <h3 className="text-xl font-semibold text-gray-900 mb-2">
                Loading your favorites...
              </h3>
              <p className="text-gray-600">
                Please wait while we fetch your saved properties.
              </p>
            </CardContent>
          </Card>
        ) : favoriteProperties.length === 0 ? (
          /* Empty State */
          <Card>
            <CardContent className="text-center py-16">
              <Heart className="h-16 w-16 text-gray-300 mx-auto mb-4" />
              <h3 className="text-xl font-semibold text-gray-900 mb-2">
                No favorites yet
              </h3>
              <p className="text-gray-600 mb-6 max-w-md mx-auto">
                Start browsing properties and click the heart icon to save your favorites. 
                They'll appear here for easy access later.
              </p>
              <div className="flex flex-col sm:flex-row gap-4 justify-center">
                <Link to="/search">
                  <Button size="lg">
                    <Search className="h-5 w-5 mr-2" />
                    Browse Properties
                  </Button>
                </Link>
                <Link to="/">
                  <Button variant="outline" size="lg">
                    Back to Home
                  </Button>
                </Link>
              </div>
            </CardContent>
          </Card>
        ) : (
          <>
            {/* Filters and Controls */}
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
              <div>
                <p className="text-gray-600">
                  {favoriteProperties.length} {favoriteProperties.length === 1 ? 'property' : 'properties'} saved
                </p>
              </div>

              <div className="flex items-center space-x-4">
                {/* Filter by Type */}
                <Select value={filterType} onValueChange={(value: 'all' | 'rent' | 'sale') => setFilterType(value)}>
                  <SelectTrigger className="w-32">
                    <Filter className="h-4 w-4 mr-2" />
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem key="all" value="all">All Types</SelectItem>
                    <SelectItem key="rent" value="rent">For Rent</SelectItem>
                    <SelectItem key="sale" value="sale">For Sale</SelectItem>
                  </SelectContent>
                </Select>

                {/* Sort Dropdown */}
                <Select value={sortBy} onValueChange={(value: SortOption) => setSortBy(value)}>
                  <SelectTrigger className="w-48">
                    <SortAsc className="h-4 w-4 mr-2" />
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem key="date-added" value="date-added">Recently Added</SelectItem>
                    <SelectItem key="price-asc" value="price-asc">Price: Low to High</SelectItem>
                    <SelectItem key="price-desc" value="price-desc">Price: High to Low</SelectItem>
                    <SelectItem key="title-asc" value="title-asc">Title: A to Z</SelectItem>
                  </SelectContent>
                </Select>

                {/* View Mode Toggle */}
                <div className="flex border rounded-lg overflow-hidden">
                  <Button
                    variant={viewMode === 'grid' ? 'default' : 'ghost'}
                    size="sm"
                    onClick={() => setViewMode('grid')}
                    className="rounded-none"
                  >
                    <Grid className="h-4 w-4" />
                  </Button>
                  <Button
                    variant={viewMode === 'list' ? 'default' : 'ghost'}
                    size="sm"
                    onClick={() => setViewMode('list')}
                    className="rounded-none"
                  >
                    <List className="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </div>

            {/* Properties Grid/List */}
            <div className={
              viewMode === 'grid'
                ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6'
                : 'space-y-6'
            }>
              {favoriteProperties.map((property) => (
                <PropertyCard 
                  key={property.id} 
                  property={property} 
                  view={viewMode}
                  useGallery={true}
                />
              ))}
            </div>

            {/* Quick Actions */}
            <Card className="mt-8">
              <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="flex flex-col sm:flex-row gap-4">
                  <Link to="/search" className="flex-1">
                    <Button variant="outline" className="w-full">
                      <Search className="h-4 w-4 mr-2" />
                      Find More Properties
                    </Button>
                  </Link>
                  <Link to="/add-property" className="flex-1">
                    <Button className="w-full">
                      List Your Property
                    </Button>
                  </Link>
                </div>
              </CardContent>
            </Card>

            {/* Recommendations */}
            {favoriteProperties.length > 0 && (
              <Card className="mt-8">
                <CardHeader>
                  <CardTitle>💡 Tips for Your Favorites</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                    <div className="bg-[#067977]/10 p-4 rounded-lg">
              <h4 className="font-semibold text-[#067977] mb-2">Stay Updated</h4>
              <p className="text-[#067977]">
                        Check back regularly as property details and availability may change.
                      </p>
                    </div>
                    <div className="bg-green-50 p-4 rounded-lg">
                      <h4 className="font-semibold text-green-900 mb-2">Contact Owners</h4>
                      <p className="text-green-800">
                        Reach out to property owners directly to schedule viewings and ask questions.
                      </p>
                    </div>
                    <div className="bg-purple-50 p-4 rounded-lg">
                      <h4 className="font-semibold text-purple-900 mb-2">Compare Options</h4>
                      <p className="text-purple-800">
                        Use your favorites list to compare different properties and make informed decisions.
                      </p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            )}
          </>
        )}
      </div>
    </div>
  );
};

export default Favorites;
