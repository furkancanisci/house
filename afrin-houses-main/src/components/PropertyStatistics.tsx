import React, { useState, useEffect } from 'react';
import { Eye, MessageCircle, Heart, TrendingUp, Calendar, BarChart3 } from 'lucide-react';
import propertyStatisticsService, { DashboardStatistics, PopularProperty } from '../services/propertyStatisticsService';
import { useToast } from '../hooks/use-toast';

interface PropertyStatisticsProps {
  propertyId?: string;
  showDashboard?: boolean;
  showPopular?: boolean;
}

const PropertyStatistics: React.FC<PropertyStatisticsProps> = ({
  propertyId,
  showDashboard = false,
  showPopular = false
}) => {
  const [dashboardStats, setDashboardStats] = useState<DashboardStatistics | null>(null);
  const [popularProperties, setPopularProperties] = useState<PopularProperty[]>([]);
  const [loading, setLoading] = useState(false);
  const [activeTab, setActiveTab] = useState<'views' | 'inquiries' | 'favorites' | 'engagement'>('views');
  const { toast } = useToast();

  useEffect(() => {
    if (showDashboard) {
      loadDashboardStatistics();
    }
    if (showPopular) {
      loadPopularProperties();
    }
  }, [showDashboard, showPopular]);

  useEffect(() => {
    if (showPopular) {
      loadPopularProperties();
    }
  }, [activeTab, showPopular]);

  const loadDashboardStatistics = async () => {
    setLoading(true);
    try {
      const response = await propertyStatisticsService.getDashboardStatistics();
      if (response.success && response.data) {
        setDashboardStats(response.data);
      } else {
        toast({
          title: 'Error',
          description: response.message || 'Failed to load dashboard statistics',
          variant: 'destructive'
        });
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to load dashboard statistics',
        variant: 'destructive'
      });
    } finally {
      setLoading(false);
    }
  };

  const loadPopularProperties = async () => {
    setLoading(true);
    try {
      const response = await propertyStatisticsService.getPopularProperties(10, activeTab);
      if (response.success && response.data) {
        setPopularProperties(response.data);
      } else {
        toast({
          title: 'Error',
          description: response.message || 'Failed to load popular properties',
          variant: 'destructive'
        });
      }
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to load popular properties',
        variant: 'destructive'
      });
    } finally {
      setLoading(false);
    }
  };

  const formatNumber = (num: number): string => {
    if (num >= 1000000) {
      return (num / 1000000).toFixed(1) + 'M';
    }
    if (num >= 1000) {
      return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
  };

  const formatDate = (dateString: string | null): string => {
    if (!dateString) return 'Never';
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
      {/* Dashboard Statistics */}
      {showDashboard && dashboardStats && (
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center mb-6">
            <BarChart3 className="h-6 w-6 text-blue-600 mr-2" />
            <h2 className="text-xl font-semibold text-gray-900">Property Statistics</h2>
          </div>

          {/* Summary Cards */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div className="bg-blue-50 rounded-lg p-4">
              <div className="flex items-center">
                <div className="p-2 bg-blue-100 rounded-lg">
                  <Eye className="h-5 w-5 text-blue-600" />
                </div>
                <div className="ml-3">
                  <p className="text-sm font-medium text-blue-600">Total Views</p>
                  <p className="text-2xl font-bold text-blue-900">
                    {formatNumber(dashboardStats.summary.total_views)}
                  </p>
                </div>
              </div>
            </div>

            <div className="bg-green-50 rounded-lg p-4">
              <div className="flex items-center">
                <div className="p-2 bg-green-100 rounded-lg">
                  <MessageCircle className="h-5 w-5 text-green-600" />
                </div>
                <div className="ml-3">
                  <p className="text-sm font-medium text-green-600">Total Inquiries</p>
                  <p className="text-2xl font-bold text-green-900">
                    {formatNumber(dashboardStats.summary.total_inquiries)}
                  </p>
                </div>
              </div>
            </div>

            <div className="bg-red-50 rounded-lg p-4">
              <div className="flex items-center">
                <div className="p-2 bg-red-100 rounded-lg">
                  <Heart className="h-5 w-5 text-red-600" />
                </div>
                <div className="ml-3">
                  <p className="text-sm font-medium text-red-600">Total Favorites</p>
                  <p className="text-2xl font-bold text-red-900">
                    {formatNumber(dashboardStats.summary.total_favorites)}
                  </p>
                </div>
              </div>
            </div>

            <div className="bg-purple-50 rounded-lg p-4">
              <div className="flex items-center">
                <div className="p-2 bg-purple-100 rounded-lg">
                  <TrendingUp className="h-5 w-5 text-purple-600" />
                </div>
                <div className="ml-3">
                  <p className="text-sm font-medium text-purple-600">Avg Views/Property</p>
                  <p className="text-2xl font-bold text-purple-900">
                    {dashboardStats.summary.average_views_per_property.toFixed(1)}
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* Top Performing Properties */}
          {dashboardStats.top_performing.length > 0 && (
            <div>
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Top Performing Properties</h3>
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Property
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Views
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Inquiries
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Favorites
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Engagement Score
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Last Viewed
                      </th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {dashboardStats.top_performing.map((property) => (
                      <tr key={property.id} className="hover:bg-gray-50">
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="text-sm font-medium text-gray-900">
                            {property.title}
                          </div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="flex items-center">
                            <Eye className="h-4 w-4 text-blue-500 mr-1" />
                            <span className="text-sm text-gray-900">{property.views_count}</span>
                          </div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="flex items-center">
                            <MessageCircle className="h-4 w-4 text-green-500 mr-1" />
                            <span className="text-sm text-gray-900">{property.inquiries_count}</span>
                          </div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="flex items-center">
                            <Heart className="h-4 w-4 text-red-500 mr-1" />
                            <span className="text-sm text-gray-900">{property.favorites_count}</span>
                          </div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="flex items-center">
                            <TrendingUp className="h-4 w-4 text-purple-500 mr-1" />
                            <span className="text-sm text-gray-900">{property.engagement_score.toFixed(1)}</span>
                          </div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="flex items-center">
                            <Calendar className="h-4 w-4 text-gray-400 mr-1" />
                            <span className="text-sm text-gray-500">{formatDate(property.last_viewed_at)}</span>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>
      )}

      {/* Popular Properties */}
      {showPopular && (
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex items-center justify-between mb-6">
            <div className="flex items-center">
              <TrendingUp className="h-6 w-6 text-blue-600 mr-2" />
              <h2 className="text-xl font-semibold text-gray-900">Popular Properties</h2>
            </div>
            
            {/* Tab Navigation */}
            <div className="flex space-x-1 bg-gray-100 rounded-lg p-1">
              {(['views', 'inquiries', 'favorites', 'engagement'] as const).map((tab) => (
                <button
                  key={tab}
                  onClick={() => setActiveTab(tab)}
                  className={`px-3 py-1 rounded-md text-sm font-medium transition-colors ${
                    activeTab === tab
                      ? 'bg-white text-blue-600 shadow-sm'
                      : 'text-gray-600 hover:text-gray-900'
                  }`}
                >
                  {tab.charAt(0).toUpperCase() + tab.slice(1)}
                </button>
              ))}
            </div>
          </div>

          {popularProperties.length > 0 ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {popularProperties.map((item, index) => (
                <div key={item.property.id} className="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                  <div className="relative">
                    <img
                      src={item.property.main_image_url || '/placeholder-property.jpg'}
                      alt={item.property.title}
                      className="w-full h-48 object-cover"
                    />
                    <div className="absolute top-2 left-2 bg-blue-600 text-white px-2 py-1 rounded text-sm font-medium">
                      #{index + 1}
                    </div>
                  </div>
                  <div className="p-4">
                    <h3 className="font-semibold text-gray-900 mb-2 line-clamp-2">
                      {item.property.title}
                    </h3>
                    <p className="text-sm text-gray-600 mb-2">{item.property.location}</p>
                    <p className="text-lg font-bold text-blue-600 mb-3">
                      ${item.property.price.toLocaleString()}
                    </p>
                    
                    <div className="flex justify-between text-sm text-gray-600">
                      <div className="flex items-center">
                        <Eye className="h-4 w-4 mr-1" />
                        <span>{item.statistics.views_count}</span>
                      </div>
                      <div className="flex items-center">
                        <MessageCircle className="h-4 w-4 mr-1" />
                        <span>{item.statistics.inquiries_count}</span>
                      </div>
                      <div className="flex items-center">
                        <Heart className="h-4 w-4 mr-1" />
                        <span>{item.statistics.favorites_count}</span>
                      </div>
                      <div className="flex items-center">
                        <TrendingUp className="h-4 w-4 mr-1" />
                        <span>{item.statistics.engagement_score.toFixed(1)}</span>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="text-center py-8">
              <TrendingUp className="h-12 w-12 text-gray-400 mx-auto mb-4" />
              <p className="text-gray-500">No popular properties found</p>
            </div>
          )}
        </div>
      )}
    </div>
  );
};

export default PropertyStatistics;