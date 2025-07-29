import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { Property, ExtendedProperty } from '../types';
import PropertyCard from '../components/PropertyCard';
import { 
  Plus, 
  Edit, 
  Trash2, 
  Eye, 
  TrendingUp, 
  Home as HomeIcon, 
  Heart,
  User,
  Mail,
  Phone
} from 'lucide-react';
import { Button } from '../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../components/ui/tabs';
import { Badge } from '../components/ui/badge';
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

const Dashboard: React.FC = () => {
  const { state, deleteProperty } = useApp();
  const { user, properties, favorites } = state;
  const navigate = useNavigate();
  const [userProperties, setUserProperties] = useState<Property[]>([]);
  const [favoriteProperties, setFavoriteProperties] = useState<ExtendedProperty[]>([]);

  useEffect(() => {
    if (!user) {
      navigate('/auth');
      return;
    }

    // Get user's properties
    const userProps = properties.filter(p => 
      user.properties.includes(p.id) || 
      p.contact.email === user.email
    );
    setUserProperties(userProps);

    // Get favorited properties
    const favProps = properties
      .filter(p => favorites.includes(p.id))
      .map(property => ({
        ...property,
        details: {
          bedrooms: property.bedrooms || 0,
          bathrooms: property.bathrooms || 0,
        },
        slug: (property as any).slug || `property-${property.id}`,
        property_type: property.propertyType,
        listing_type: property.listingType,
        square_feet: property.squareFootage,
        year_built: (property as any).yearBuilt || new Date().getFullYear(),
        media: property.images?.map((url, index) => ({
          id: index,
          url,
          type: 'image'
        })) || []
      }));
    setFavoriteProperties(favProps);
  }, [user, properties, favorites, navigate]);

  const handleDeleteProperty = async (propertyId: string) => {
    try {
      await deleteProperty(propertyId);
      toast.success('Property deleted successfully');
    } catch (error) {
      toast.error('Failed to delete property');
    }
  };

  const stats = [
    {
      title: 'Total Properties',
      value: userProperties.length,
      icon: HomeIcon,
      color: 'text-blue-600',
      bgColor: 'bg-blue-100',
    },
    {
      title: 'For Rent',
      value: userProperties.filter(p => p.listingType === 'rent').length,
      icon: TrendingUp,
      color: 'text-green-600',
      bgColor: 'bg-green-100',
    },
    {
      title: 'For Sale',
      value: userProperties.filter(p => p.listingType === 'sale').length,
      icon: TrendingUp,
      color: 'text-purple-600',
      bgColor: 'bg-purple-100',
    },
    {
      title: 'Favorites',
      value: favoriteProperties.length,
      icon: Heart,
      color: 'text-red-600',
      bgColor: 'bg-red-100',
    },
  ];

  if (!user) {
    return null;
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Header */}
        <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">
              Welcome back, {user.name}!
            </h1>
            <p className="text-gray-600">
              Manage your properties and track your favorites
            </p>
          </div>
          <Link to="/add-property">
            <Button size="lg" className="mt-4 md:mt-0">
              <Plus className="h-5 w-5 mr-2" />
              Add New Property
            </Button>
          </Link>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          {stats.map((stat, index) => (
            <Card key={index}>
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-gray-600 mb-1">
                      {stat.title}
                    </p>
                    <p className="text-3xl font-bold text-gray-900">
                      {stat.value}
                    </p>
                  </div>
                  <div className={`p-3 rounded-full ${stat.bgColor}`}>
                    <stat.icon className={`h-6 w-6 ${stat.color}`} />
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Main Content */}
        <Tabs defaultValue="properties" className="space-y-6">
          <TabsList className="grid w-full grid-cols-3">
            <TabsTrigger value="properties">My Properties</TabsTrigger>
            <TabsTrigger value="favorites">Favorites</TabsTrigger>
            <TabsTrigger value="profile">Profile</TabsTrigger>
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
                    <HomeIcon className="h-12 w-12 text-gray-400 mx-auto mb-4" />
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
                            <img
                              src={property.mainImage}
                              alt={property.title}
                              className="w-full h-32 object-cover rounded-lg"
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
                                <Badge variant={property.listingType === 'rent' ? 'default' : 'secondary'}>
                                  For {property.listingType === 'rent' ? 'Rent' : 'Sale'}
                                </Badge>
                                <span className="text-xl font-bold text-blue-600">
                                  ${property.price.toLocaleString()}
                                  {property.listingType === 'rent' && '/month'}
                                </span>
                              </div>
                            </div>
                            <div className="flex items-center text-sm text-gray-600 mb-4">
                              <span>{property.bedrooms} beds</span>
                              <span className="mx-2">•</span>
                              <span>{property.bathrooms} baths</span>
                              <span className="mx-2">•</span>
                              <span>{property.squareFootage.toLocaleString()} sq ft</span>
                            </div>
                            <div className="flex space-x-2">
                              <Link to={`/property/${property.id}`}>
                                <Button variant="outline" size="sm">
                                  <Eye className="h-4 w-4 mr-2" />
                                  View
                                </Button>
                              </Link>
                              <Link to={`/edit-property/${property.id}`}>
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

          {/* Favorites Tab */}
          <TabsContent value="favorites">
            <Card>
              <CardHeader>
                <CardTitle>Favorite Properties</CardTitle>
              </CardHeader>
              <CardContent>
                {favoriteProperties.length === 0 ? (
                  <div className="text-center py-12">
                    <Heart className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                    <h3 className="text-lg font-semibold text-gray-900 mb-2">
                      No favorites yet
                    </h3>
                    <p className="text-gray-600 mb-4">
                      Start browsing properties and save your favorites
                    </p>
                    <Link to="/search">
                      <Button>
                        Browse Properties
                      </Button>
                    </Link>
                  </div>
                ) : (
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {favoriteProperties.map((property) => (
                      <PropertyCard key={property.id} property={property} />
                    ))}
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>

          {/* Profile Tab */}
          <TabsContent value="profile">
            <Card>
              <CardHeader>
                <CardTitle>Profile Information</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-6">
                  <div className="flex items-center space-x-4">
                    <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                      <User className="h-8 w-8 text-blue-600" />
                    </div>
                    <div>
                      <h3 className="text-xl font-semibold text-gray-900">{user.name}</h3>
                      <p className="text-gray-600">Property Owner</p>
                    </div>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="space-y-4">
                      <div>
                        <label className="text-sm font-medium text-gray-700">Full Name</label>
                        <div className="mt-1 flex items-center space-x-2">
                          <User className="h-4 w-4 text-gray-400" />
                          <span className="text-gray-900">{user.name}</span>
                        </div>
                      </div>

                      <div>
                        <label className="text-sm font-medium text-gray-700">Email Address</label>
                        <div className="mt-1 flex items-center space-x-2">
                          <Mail className="h-4 w-4 text-gray-400" />
                          <span className="text-gray-900">{user.email}</span>
                        </div>
                      </div>

                      {user.phone && (
                        <div>
                          <label className="text-sm font-medium text-gray-700">Phone Number</label>
                          <div className="mt-1 flex items-center space-x-2">
                            <Phone className="h-4 w-4 text-gray-400" />
                            <span className="text-gray-900">{user.phone}</span>
                          </div>
                        </div>
                      )}
                    </div>

                    <div className="space-y-4">
                      <div>
                        <label className="text-sm font-medium text-gray-700">Account Statistics</label>
                        <div className="mt-2 space-y-2">
                          <div className="flex justify-between">
                            <span className="text-gray-600">Properties Listed</span>
                            <span className="font-semibold">{userProperties.length}</span>
                          </div>
                          <div className="flex justify-between">
                            <span className="text-gray-600">Favorite Properties</span>
                            <span className="font-semibold">{favoriteProperties.length}</span>
                          </div>
                          <div className="flex justify-between">
                            <span className="text-gray-600">Member Since</span>
                            <span className="font-semibold">
                              {new Date().toLocaleDateString('en-US', { 
                                year: 'numeric', 
                                month: 'long' 
                              })}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div className="pt-6 border-t">
                    <Button variant="outline">
                      Edit Profile
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </div>
  );
};

export default Dashboard;
