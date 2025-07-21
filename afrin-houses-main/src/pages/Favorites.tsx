import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { Property } from '../types';
import PropertyCard from '../components/PropertyCard';
import { 
  Heart, 
  Search, 
  Grid, 
  List,
  Filter,
  SortAsc
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

type ViewMode = 'grid' | 'list';
type SortOption = 'date-added' | 'price-asc' | 'price-desc' | 'title-asc';

const Favorites: React.FC = () => {
  const { state } = useApp();
  const { user, properties, favorites } = state;
  const navigate = useNavigate();
  const [favoriteProperties, setFavoriteProperties] = useState<Property[]>([]);
  const [viewMode, setViewMode] = useState<ViewMode>('grid');
  const [sortBy, setSortBy] = useState<SortOption>('date-added');
  const [filterType, setFilterType] = useState<'all' | 'rent' | 'sale'>('all');

  useEffect(() => {
    if (!user) {
      navigate('/auth');
      return;
    }

    // Get favorited properties
    let favProps = properties.filter(p => favorites.includes(p.id));

    // Apply filter
    if (filterType !== 'all') {
      favProps = favProps.filter(p => p.listingType === filterType);
    }

    // Apply sort
    switch (sortBy) {
      case 'price-asc':
        favProps.sort((a, b) => a.price - b.price);
        break;
      case 'price-desc':
        favProps.sort((a, b) => b.price - a.price);
        break;
      case 'title-asc':
        favProps.sort((a, b) => a.title.localeCompare(b.title));
        break;
      case 'date-added':
      default:
        // Sort by when they were added to favorites (most recent first)
        // For demo purposes, we'll sort by date posted
        favProps.sort((a, b) => new Date(b.datePosted).getTime() - new Date(a.datePosted).getTime());
        break;
    }

    setFavoriteProperties(favProps);
  }, [user, properties, favorites, sortBy, filterType, navigate]);

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

        {favoriteProperties.length === 0 ? (
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
                    <SelectItem value="all">All Types</SelectItem>
                    <SelectItem value="rent">For Rent</SelectItem>
                    <SelectItem value="sale">For Sale</SelectItem>
                  </SelectContent>
                </Select>

                {/* Sort Dropdown */}
                <Select value={sortBy} onValueChange={(value: SortOption) => setSortBy(value)}>
                  <SelectTrigger className="w-48">
                    <SortAsc className="h-4 w-4 mr-2" />
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="date-added">Recently Added</SelectItem>
                    <SelectItem value="price-asc">Price: Low to High</SelectItem>
                    <SelectItem value="price-desc">Price: High to Low</SelectItem>
                    <SelectItem value="title-asc">Title: A to Z</SelectItem>
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
                    <div className="bg-blue-50 p-4 rounded-lg">
                      <h4 className="font-semibold text-blue-900 mb-2">Stay Updated</h4>
                      <p className="text-blue-800">
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
