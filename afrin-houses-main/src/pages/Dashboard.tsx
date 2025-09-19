import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { Property, User } from '../types';
import { Plus, Edit, Trash2, Eye } from 'lucide-react';
import { Button } from '../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../components/ui/tabs';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '../components/ui/alert-dialog';
import { toast } from 'sonner';
import { deleteProperty } from '../services/propertyService';
import FixedImage from '../components/FixedImage';

const Dashboard: React.FC = () => {
  const { state } = useApp();
  const { user, properties } = state;
  const navigate = useNavigate();
  const [userProperties, setUserProperties] = useState<Property[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) {
      navigate('/auth');
      return;
    }

    // Filter properties to only show those belonging to the current user
    const currentUserProperties = properties.filter(property => 
      property.user_id && property.user_id.toString() === user.id.toString()
    );
    
    setUserProperties(currentUserProperties);
    setLoading(false);
  }, [user, properties, navigate]);

  const handleDeleteProperty = async (propertyId: string | number) => {
    try {
      await deleteProperty(Number(propertyId));
      // Update the local state to remove the deleted property
      setUserProperties(prev => prev.filter(p => p.id.toString() !== propertyId.toString()));
      toast.success('Property deleted successfully');
    } catch (error) {

      toast.error('Failed to delete property');
    }
  };

  if (!user) {
    return null;
  }

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="text-center">
          <div className="w-16 h-16 border-4 border-[#067977] border-t-transparent rounded-full animate-spin mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading your dashboard...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Header */}
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">
              Dashboard
            </h1>
            <p className="text-gray-600">
              Manage your properties
            </p>
          </div>
          <div className="flex gap-2 mt-4 md:mt-0">
            <Link to="/add-property">
              <Button size="lg">
                <Plus className="h-5 w-5 mr-2" />
                Add New Property
              </Button>
            </Link>
          </div>
        </div>

        {/* Tabs */}
        <Tabs defaultValue="properties" className="space-y-6">
          <TabsList className="grid w-full grid-cols-1">
            <TabsTrigger value="properties" className="flex items-center gap-2">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
              </svg>
              My Properties
            </TabsTrigger>
          </TabsList>

          {/* My Properties Tab */}
          <TabsContent value="properties">
            <Card>
              <CardHeader>
                <div className="flex justify-between items-center">
                  <CardTitle>My Properties</CardTitle>
                  <Link to="/add-property">
                    <Button>
                      <Plus className="h-4 w-4 mr-2" />
                      Add Property
                    </Button>
                  </Link>
                </div>
              </CardHeader>
              <CardContent>
                {userProperties.length === 0 ? (
                  <div className="text-center py-12">
                    <div className="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                      <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                      </svg>
                    </div>
                    <h3 className="text-lg font-semibold text-gray-900 mb-2">
                      No properties listed yet
                    </h3>
                    <p className="text-gray-600 mb-4">
                      Start by adding your first property listing
                    </p>
                    <Link to="/add-property">
                      <Button>
                        <Plus className="h-4 w-4 mr-2" />
                        Add Your First Property
                      </Button>
                    </Link>
                  </div>
                ) : (
                  <div className="space-y-6">
                    {userProperties.map((property) => (
                      <div key={property.id} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div className="flex flex-col md:flex-row gap-4">
                          <div className="md:w-1/4">
                            <FixedImage
                              src={property.mainImage || '/images/placeholder-property.svg'}
                              alt={property.title}
                              className="w-full h-32 object-cover rounded-lg shadow-sm border border-gray-200"
                              showLoadingSpinner={true}
                            />
                          </div>
                          <div className="flex-1">
                            <div className="flex justify-between items-start mb-2">
                              <div>
                                <h3 className="text-lg font-semibold text-gray-900">
                                  {property.title}
                                </h3>
                                <p className="text-gray-600">{property.address}</p>
                              </div>
                              <div className="flex items-center space-x-2">
                                <span className="text-xl font-bold text-[#067977]">
                                  ${typeof property.price === 'number' ? property.price.toLocaleString() : property.price}
                                  {property.listingType === 'rent' && '/month'}
                                </span>
                              </div>
                            </div>
                            <div className="flex items-center text-sm text-gray-600 mb-4">
                              <span>{property.bedrooms || 0} beds</span>
                              <span className="mx-2">•</span>
                              <span>{property.bathrooms || 0} baths</span>
                              <span className="mx-2">•</span>
                              <span>{property.squareFootage?.toLocaleString() || 'N/A'} sq ft</span>
                            </div>
                            <div className="flex space-x-2">
                              <Link to={`/property/${property.slug || property.id}`}>
                                <Button variant="outline" size="sm">
                                  <Eye className="h-4 w-4 mr-2" />
                                  View
                                </Button>
                              </Link>
                              <Link to={`/edit-property/${property.slug || property.id}`}>
                                <Button variant="outline" size="sm">
                                  <Edit className="h-4 w-4 mr-2" />
                                  Edit
                                </Button>
                              </Link>
                              <AlertDialog>
                                <AlertDialogTrigger asChild>
                                  <Button variant="outline" size="sm">
                                    <Trash2 className="h-4 w-4 mr-2" />
                                    Delete
                                  </Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                  <AlertDialogHeader>
                                    <AlertDialogTitle>Delete Property</AlertDialogTitle>
                                    <AlertDialogDescription>
                                      Are you sure you want to delete "{property.title}"? 
                                      This action cannot be undone.
                                    </AlertDialogDescription>
                                  </AlertDialogHeader>
                                  <AlertDialogFooter>
                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                    <AlertDialogAction
                                      onClick={() => handleDeleteProperty(property.id)}
                                      className="bg-red-600 hover:bg-red-700"
                                    >
                                      Delete
                                    </AlertDialogAction>
                                  </AlertDialogFooter>
                                </AlertDialogContent>
                              </AlertDialog>
                            </div>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
};

export default Dashboard;