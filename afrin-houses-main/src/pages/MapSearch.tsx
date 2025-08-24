import React, { useEffect, useState, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Property } from '../types';
import { SearchFilters } from '../types';
import MapSearchView from '../components/MapSearchView';
import { ArrowLeft, X } from 'lucide-react';
import { Button } from '../components/ui/button';
import { useApp } from '../context/AppContext';
import { getProperties } from '../services/propertyService';

const MapSearch: React.FC = () => {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { state, filterProperties } = useApp();
  const [allProperties, setAllProperties] = useState<Property[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [searchFilters, setSearchFilters] = useState<SearchFilters>({
    searchQuery: '',
    listingType: 'all',
    propertyType: 'all',
    location: ''
  });

  useEffect(() => {
    const fetchProperties = async () => {
      try {
        setLoading(true);
        setError(null);
        const response = await getProperties();
        console.log('Properties response:', response);
        // Handle the response structure from getProperties
        let properties = [];
        if (response && typeof response === 'object') {
          if (Array.isArray(response)) {
            properties = response;
          } else if (response.data && Array.isArray(response.data)) {
            properties = response.data;
          }
        }
        console.log('Processed properties:', properties);
        
        // Add some test properties if no properties are returned
        if (!properties || properties.length === 0) {
          console.log('No properties found, adding test data');

        } else {
          setAllProperties(properties);
        }
        

      } catch (err) {
        console.error('Error fetching properties:', err);
   
        setError(null); // Clear any error since we have fallback data
      } finally {
        setLoading(false);
      }
    };

    fetchProperties();
  }, []);

  // Update properties when state.properties changes from AppContext
  useEffect(() => {
    if (state.properties && state.properties.length > 0) {
      console.log('MapSearch: Updating properties from context:', state.properties);
      setAllProperties(state.properties);
    }
  }, [state.properties]);

  const handleClose = () => {
    navigate('/search');
  };

  const handleBackToSearch = () => {
    navigate('/search');
  };

  // Handle filters change and call API
  const handleFiltersChange = useCallback(async (newFilters: SearchFilters) => {
    console.log('MapSearch: Filters changed:', newFilters);
    
    // Update local filters state
    setSearchFilters(newFilters);
    
    // Call filterProperties from AppContext to fetch filtered data from API
    try {
      console.log('MapSearch: Calling filterProperties with:', newFilters);
      await filterProperties(newFilters);
      
      // Update properties from the context state
      if (state.properties) {
        setAllProperties(state.properties);
      }
    } catch (error) {
      console.error('MapSearch: Error filtering properties:', error);
      setError(t('search.errorLoadingProperties'));
    }
  }, [filterProperties, state.properties]);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">{t('map.loadingProperties')}</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
            <div className="flex">
              <div className="flex-shrink-0">
                <X className="h-5 w-5 text-red-400" />
              </div>
              <div className="ml-3">
                <p className="text-sm text-red-700">{error}</p>
              </div>
            </div>
          </div>
          <Button onClick={handleBackToSearch} variant="outline">
            <ArrowLeft className="h-4 w-4 mr-2" />
            {t('search.backToSearch')}
          </Button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Map Search View */}
      <div className="h-[calc(100vh-80px)]">
        <MapSearchView
          properties={allProperties}
          initialFilters={searchFilters}
          onFiltersChange={handleFiltersChange}
          onClose={handleClose}
        />
      </div>
    </div>
  );
};

export default MapSearch;