import React, { useState, useEffect } from 'react';
import { Search, Bell, BellOff, Edit, Trash2, Play, Plus, Eye } from 'lucide-react';
import savedSearchService, { SavedSearch, SavedSearchProperty } from '../services/savedSearchService';
import { useToast } from '../hooks/use-toast';
import { useNavigate } from 'react-router-dom';

interface SavedSearchesProps {
  onCreateNew?: () => void;
  onEditSearch?: (search: SavedSearch) => void;
}

const SavedSearches: React.FC<SavedSearchesProps> = ({ onCreateNew, onEditSearch }) => {
  const [savedSearches, setSavedSearches] = useState<SavedSearch[]>([]);
  const [loading, setLoading] = useState(false);
  const [executingSearch, setExecutingSearch] = useState<string | null>(null);
  const [searchResults, setSearchResults] = useState<{ [key: string]: SavedSearchProperty[] }>({});
  const [expandedSearch, setExpandedSearch] = useState<string | null>(null);
  const { toast } = useToast();
  const navigate = useNavigate();

  useEffect(() => {
    loadSavedSearches();
  }, []);

  const loadSavedSearches = async () => {
    setLoading(true);
    try {
      const response = await savedSearchService.getSavedSearches();
      if (response.success && response.data) {
        setSavedSearches(response.data);
        // Load counts for each search
        response.data.forEach(search => {
          loadSearchCount(search.id);
        });
      } else {
        toast({
          title: 'Error',
          description: response.message || 'Failed to load saved searches',
          variant: 'destructive'
        });
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to load saved searches',
        variant: 'destructive'
      });
    } finally {
      setLoading(false);
    }
  };

  const loadSearchCount = async (searchId: string) => {
    try {
      const response = await savedSearchService.getSavedSearchCount(searchId);
      if (response.success && response.data) {
        setSavedSearches(prev => prev.map(search => 
          search.id === searchId 
            ? { ...search, matching_properties_count: response.data!.count }
            : search
        ));
      }
    } catch (error) {
      
    }
  };

  const handleToggleNotifications = async (searchId: string) => {
    try {
      const response = await savedSearchService.toggleNotifications(searchId);
      if (response.success && response.data) {
        setSavedSearches(prev => prev.map(search => 
          search.id === searchId ? response.data! : search
        ));
        toast({
          title: 'Success',
          description: `Notifications ${response.data.notification_enabled ? 'enabled' : 'disabled'}`,
        });
      } else {
        toast({
          title: 'Error',
          description: response.message || 'Failed to toggle notifications',
          variant: 'destructive'
        });
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to toggle notifications',
        variant: 'destructive'
      });
    }
  };

  const handleDeleteSearch = async (searchId: string, searchName: string) => {
    if (!confirm(`Are you sure you want to delete "${searchName}"?`)) {
      return;
    }

    try {
      const response = await savedSearchService.deleteSavedSearch(searchId);
      if (response.success) {
        setSavedSearches(prev => prev.filter(search => search.id !== searchId));
        toast({
          title: 'Success',
          description: 'Saved search deleted successfully',
        });
      } else {
        toast({
          title: 'Error',
          description: response.message || 'Failed to delete saved search',
          variant: 'destructive'
        });
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to delete saved search',
        variant: 'destructive'
      });
    }
  };

  const handleExecuteSearch = async (searchId: string) => {
    setExecutingSearch(searchId);
    try {
      const response = await savedSearchService.executeSavedSearch(searchId, 1, 10);
      if (response.success && response.data) {
        setSearchResults(prev => ({
          ...prev,
          [searchId]: response.data!.properties
        }));
        setExpandedSearch(expandedSearch === searchId ? null : searchId);
      } else {
        toast({
          title: 'Error',
          description: response.message || 'Failed to execute search',
          variant: 'destructive'
        });
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to execute search',
        variant: 'destructive'
      });
    } finally {
      setExecutingSearch(null);
    }
  };

  const handleViewAllResults = (search: SavedSearch) => {
    // Navigate to search page with the saved search criteria
    const searchParams = new URLSearchParams();
    Object.entries(search.search_criteria).forEach(([key, value]) => {
      if (value !== null && value !== undefined && value !== '') {
        if (Array.isArray(value)) {
          value.forEach(v => searchParams.append(key, v.toString()));
        } else {
          searchParams.set(key, value.toString());
        }
      }
    });
    navigate(`/search?${searchParams.toString()}`);
  };

  const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString();
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center p-8">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div className="flex items-center">
          <Search className="h-6 w-6 text-blue-600 mr-2" />
          <h2 className="text-xl font-semibold text-gray-900">Saved Searches</h2>
        </div>
        {onCreateNew && (
          <button
            onClick={onCreateNew}
            className="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            <Plus className="h-4 w-4 mr-2" />
            Create New Search
          </button>
        )}
      </div>

      {savedSearches.length === 0 ? (
        <div className="text-center py-12">
          <Search className="h-12 w-12 text-gray-400 mx-auto mb-4" />
          <h3 className="text-lg font-medium text-gray-900 mb-2">No saved searches yet</h3>
          <p className="text-gray-500 mb-4">
            Save your search criteria to get notified when new matching properties are available.
          </p>
          {onCreateNew && (
            <button
              onClick={onCreateNew}
              className="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <Plus className="h-4 w-4 mr-2" />
              Create Your First Search
            </button>
          )}
        </div>
      ) : (
        <div className="space-y-4">
          {savedSearches.map((search) => (
            <div key={search.id} className="bg-white rounded-lg shadow-md border border-gray-200">
              <div className="p-6">
                <div className="flex items-start justify-between">
                  <div className="flex-1">
                    <div className="flex items-center mb-2">
                      <h3 className="text-lg font-semibold text-gray-900 mr-3">{search.name}</h3>
                      <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {search.matching_properties_count || 0} matches
                      </span>
                    </div>
                    <p className="text-sm text-gray-600 mb-3">
                      {savedSearchService.formatSearchCriteria(search.search_criteria)}
                    </p>
                    <div className="flex items-center text-xs text-gray-500">
                      <span>Created: {formatDate(search.created_at)}</span>
                      <span className="mx-2">•</span>
                      <span>Updated: {formatDate(search.updated_at)}</span>
                    </div>
                  </div>
                  
                  <div className="flex items-center space-x-2 ml-4">
                    <button
                      onClick={() => handleToggleNotifications(search.id)}
                      className={`p-2 rounded-lg transition-colors ${
                        search.notification_enabled
                          ? 'bg-green-100 text-green-600 hover:bg-green-200'
                          : 'bg-gray-100 text-gray-400 hover:bg-gray-200'
                      }`}
                      title={search.notification_enabled ? 'Disable notifications' : 'Enable notifications'}
                    >
                      {search.notification_enabled ? (
                        <Bell className="h-4 w-4" />
                      ) : (
                        <BellOff className="h-4 w-4" />
                      )}
                    </button>
                    
                    {onEditSearch && (
                      <button
                        onClick={() => onEditSearch(search)}
                        className="p-2 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-colors"
                        title="Edit search"
                      >
                        <Edit className="h-4 w-4" />
                      </button>
                    )}
                    
                    <button
                      onClick={() => handleDeleteSearch(search.id, search.name)}
                      className="p-2 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors"
                      title="Delete search"
                    >
                      <Trash2 className="h-4 w-4" />
                    </button>
                  </div>
                </div>
                
                <div className="flex items-center space-x-3 mt-4 pt-4 border-t border-gray-200">
                  <button
                    onClick={() => handleExecuteSearch(search.id)}
                    disabled={executingSearch === search.id}
                    className="flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                  >
                    {executingSearch === search.id ? (
                      <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    ) : (
                      <Play className="h-4 w-4 mr-2" />
                    )}
                    {expandedSearch === search.id ? 'Hide Results' : 'Preview Results'}
                  </button>
                  
                  <button
                    onClick={() => handleViewAllResults(search)}
                    className="flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                  >
                    <Eye className="h-4 w-4 mr-2" />
                    View All Results
                  </button>
                </div>
              </div>
              
              {/* Search Results Preview */}
              {expandedSearch === search.id && searchResults[search.id] && (
                <div className="border-t border-gray-200 bg-gray-50 p-6">
                  <h4 className="text-sm font-medium text-gray-900 mb-4">Recent Matches (Preview)</h4>
                  {searchResults[search.id].length > 0 ? (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                      {searchResults[search.id].slice(0, 6).map((property) => (
                        <div key={property.id} className="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                          <img
                            src={property.main_image_url || '/placeholder-property.jpg'}
                            alt={property.title}
                            className="w-full h-32 object-cover"
                          />
                          <div className="p-3">
                            <h5 className="font-medium text-gray-900 text-sm mb-1 line-clamp-2">
                              {property.title}
                            </h5>
                            <p className="text-xs text-gray-600 mb-2">{property.location}</p>
                            <div className="flex justify-between items-center">
                              <span className="text-sm font-bold text-blue-600">
                                ${property.price.toLocaleString()}
                              </span>
                              <span className="text-xs text-gray-500">
                                {property.bedrooms}bed • {property.bathrooms}bath
                              </span>
                            </div>
                          </div>
                        </div>
                      ))}
                    </div>
                  ) : (
                    <div className="text-center py-8">
                      <Search className="h-8 w-8 text-gray-400 mx-auto mb-2" />
                      <p className="text-sm text-gray-500">No matching properties found</p>
                    </div>
                  )}
                  
                  {searchResults[search.id].length > 6 && (
                    <div className="mt-4 text-center">
                      <button
                        onClick={() => handleViewAllResults(search)}
                        className="text-sm text-blue-600 hover:text-blue-700 font-medium"
                      >
                        View all {search.matching_properties_count} results →
                      </button>
                    </div>
                  )}
                </div>
              )}
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default SavedSearches;