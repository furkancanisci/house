import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { Property } from '../types';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { 
  ArrowLeft, 
  Home, 
  MapPin, 
  DollarSign, 
  Bed, 
  Bath, 
  Square, 
  Calendar,
  Phone,
  Mail,
  User
} from 'lucide-react';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Textarea } from '../components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '../components/ui/select';
import { Checkbox } from '../components/ui/checkbox';
import { toast } from 'sonner';

const propertySchema = z.object({
  title: z.string().min(1, 'Property title is required'),
  address: z.string().min(1, 'Address is required'),
  price: z.number().min(1, 'Price must be greater than 0'),
  listingType: z.enum(['rent', 'sale']),
  propertyType: z.enum(['apartment', 'house', 'condo', 'townhouse']),
  bedrooms: z.number().min(0, 'Bedrooms must be 0 or greater'),
  bathrooms: z.number().min(0, 'Bathrooms must be 0 or greater'),
  squareFootage: z.number().min(1, 'Square footage must be greater than 0'),
  description: z.string().min(10, 'Description must be at least 10 characters'),
  yearBuilt: z.number().min(1800, 'Year built must be valid').max(new Date().getFullYear(), 'Year built cannot be in the future'),
  availableDate: z.string().optional(),
  petPolicy: z.string().optional(),
  parking: z.string().optional(),
  utilities: z.string().optional(),
  lotSize: z.string().optional(),
  garage: z.string().optional(),
  heating: z.string().optional(),
  hoaFees: z.string().optional(),
  building: z.string().optional(),
  pool: z.string().optional(),
  contactName: z.string().min(1, 'Contact name is required'),
  contactPhone: z.string().min(1, 'Contact phone is required'),
  contactEmail: z.string().email('Valid email is required'),
});

type PropertyFormData = z.infer<typeof propertySchema>;

const EditProperty: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const { state, updateProperty } = useApp();
  const { user, properties } = state;
  const navigate = useNavigate();
  const [property, setProperty] = useState<Property | null>(null);
  const [selectedFeatures, setSelectedFeatures] = useState<string[]>([]);

  const {
    register,
    handleSubmit,
    control,
    formState: { errors, isSubmitting },
    reset,
  } = useForm<PropertyFormData>({
    resolver: zodResolver(propertySchema),
  });

  const availableFeatures = [
    'Parking',
    'Pool',
    'Gym',
    'Pet Friendly',
    'Balcony',
    'Garden',
    'Fireplace',
    'Dishwasher',
    'Air Conditioning',
    'Laundry in Unit',
    'Elevator',
    'Garage',
    'Hardwood Floors',
    'Walk-in Closets',
    'Patio',
    'Storage',
    'High Ceilings',
    'Updated Kitchen',
    'Updated Bathroom',
    'Close to Transit',
    'Ocean View',
    'City View',
    'Private Elevator',
    'Concierge',
    'Spa',
    'Wine Cellar',
    'Smart Home',
    'Historic Details',
    'Bay Windows',
    'Crown Molding',
    'Community Pool',
    'Playground',
    'Washer/Dryer',
    'In-Unit Laundry',
    'Rooftop Deck',
    'Fitness Center',
    'Outdoor Kitchen',
    'Single Story',
    'Large Backyard',
    'Master Suite',
    'Desert Landscaping',
    'Tile Floors',
  ];

  useEffect(() => {
    if (!user) {
      navigate('/auth');
      return;
    }

    if (id && properties.length > 0) {
      const foundProperty = properties.find(p => p.id === id);
      if (foundProperty) {
        // Check if user owns this property
        if (foundProperty.contact.email !== user.email) {
          toast.error('You can only edit your own properties');
          navigate('/dashboard');
          return;
        }

        setProperty(foundProperty);
        setSelectedFeatures(foundProperty.features);
        
        // Reset form with property data
        reset({
          title: foundProperty.title,
          address: foundProperty.address,
          price: foundProperty.price,
          listingType: foundProperty.listingType,
          propertyType: foundProperty.propertyType,
          bedrooms: foundProperty.bedrooms,
          bathrooms: foundProperty.bathrooms,
          squareFootage: foundProperty.squareFootage,
          description: foundProperty.description,
          yearBuilt: foundProperty.yearBuilt,
          availableDate: foundProperty.availableDate || '',
          petPolicy: foundProperty.petPolicy || '',
          parking: foundProperty.parking || '',
          utilities: foundProperty.utilities || '',
          lotSize: foundProperty.lotSize || '',
          garage: foundProperty.garage || '',
          heating: foundProperty.heating || '',
          hoaFees: foundProperty.hoaFees || '',
          building: foundProperty.building || '',
          pool: foundProperty.pool || '',
          contactName: foundProperty.contact.name,
          contactPhone: foundProperty.contact.phone,
          contactEmail: foundProperty.contact.email,
        });
      } else {
        toast.error('Property not found');
        navigate('/dashboard');
      }
    }
  }, [id, properties, user, navigate, reset]);

  const handleFeatureToggle = (feature: string) => {
    setSelectedFeatures(prev =>
      prev.includes(feature)
        ? prev.filter(f => f !== feature)
        : [...prev, feature]
    );
  };

  const onSubmit = async (data: PropertyFormData) => {
    if (!property) return;

    try {
      const updatedProperty: Property = {
        ...property,
        title: data.title,
        address: data.address,
        price: data.price,
        listingType: data.listingType,
        propertyType: data.propertyType,
        bedrooms: data.bedrooms,
        bathrooms: data.bathrooms,
        squareFootage: data.squareFootage,
        description: data.description,
        features: selectedFeatures,
        yearBuilt: data.yearBuilt,
        availableDate: data.availableDate,
        petPolicy: data.petPolicy,
        parking: data.parking,
        utilities: data.utilities,
        lotSize: data.lotSize,
        garage: data.garage,
        heating: data.heating,
        hoaFees: data.hoaFees,
        building: data.building,
        pool: data.pool,
        contact: {
          name: data.contactName,
          phone: data.contactPhone,
          email: data.contactEmail,
        },
      };

     await updateProperty(updatedProperty);
      toast.success('Property updated successfully!');
      navigate('/dashboard');
    } catch (error) {
      toast.error('Failed to update property');
    }
  };

  if (!user || !property) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Loading property...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-8">
          <Button
            variant="ghost"
            onClick={() => navigate('/dashboard')}
            className="flex items-center"
          >
            <ArrowLeft className="h-4 w-4 mr-2" />
            Back to Dashboard
          </Button>
          <div className="flex items-center space-x-2">
            <Home className="h-6 w-6 text-blue-600" />
            <h1 className="text-2xl font-bold text-gray-900">Edit Property</h1>
          </div>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit(onSubmit)} className="space-y-8">
          {/* Basic Information */}
          <Card>
            <CardHeader>
              <CardTitle>Basic Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              <div>
                <Label htmlFor="title">Property Title *</Label>
                <Input
                  id="title"
                  placeholder="e.g., Luxury Downtown Apartment"
                  {...register('title')}
                  className={errors.title ? 'border-red-500' : ''}
                />
                {errors.title && (
                  <p className="text-sm text-red-600 mt-1">{errors.title.message}</p>
                )}
              </div>

              <div>
                <Label htmlFor="address">Address *</Label>
                <div className="relative">
                  <MapPin className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                  <Input
                    id="address"
                    placeholder="123 Main Street, City, State 12345"
                    className={`pl-10 ${errors.address ? 'border-red-500' : ''}`}
                    {...register('address')}
                  />
                </div>
                {errors.address && (
                  <p className="text-sm text-red-600 mt-1">{errors.address.message}</p>
                )}
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label>Listing Type *</Label>
                  <Controller
                    name="listingType"
                    control={control}
                    render={({ field }) => (
                      <Select onValueChange={field.onChange} value={field.value}>
                        <SelectTrigger>
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="rent">For Rent</SelectItem>
                          <SelectItem value="sale">For Sale</SelectItem>
                        </SelectContent>
                      </Select>
                    )}
                  />
                </div>

                <div>
                  <Label>Property Type *</Label>
                  <Controller
                    name="propertyType"
                    control={control}
                    render={({ field }) => (
                      <Select onValueChange={field.onChange} value={field.value}>
                        <SelectTrigger>
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="apartment">Apartment</SelectItem>
                          <SelectItem value="house">House</SelectItem>
                          <SelectItem value="condo">Condo</SelectItem>
                          <SelectItem value="townhouse">Townhouse</SelectItem>
                        </SelectContent>
                      </Select>
                    )}
                  />
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Property Details */}
          <Card>
            <CardHeader>
              <CardTitle>Property Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              <div>
                <Label htmlFor="price">Price *</Label>
                <div className="relative">
                  <DollarSign className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                  <Input
                    id="price"
                    type="number"
                    placeholder="Monthly rent or sale price"
                    className={`pl-10 ${errors.price ? 'border-red-500' : ''}`}
                    {...register('price', { valueAsNumber: true })}
                  />
                </div>
                {errors.price && (
                  <p className="text-sm text-red-600 mt-1">{errors.price.message}</p>
                )}
              </div>

              <div className="grid grid-cols-3 gap-4">
                <div>
                  <Label htmlFor="bedrooms">Bedrooms *</Label>
                  <div className="relative">
                    <Bed className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      id="bedrooms"
                      type="number"
                      min="0"
                      className={`pl-10 ${errors.bedrooms ? 'border-red-500' : ''}`}
                      {...register('bedrooms', { valueAsNumber: true })}
                    />
                  </div>
                  {errors.bedrooms && (
                    <p className="text-sm text-red-600 mt-1">{errors.bedrooms.message}</p>
                  )}
                </div>

                <div>
                  <Label htmlFor="bathrooms">Bathrooms *</Label>
                  <div className="relative">
                    <Bath className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      id="bathrooms"
                      type="number"
                      min="0"
                      step="0.5"
                      className={`pl-10 ${errors.bathrooms ? 'border-red-500' : ''}`}
                      {...register('bathrooms', { valueAsNumber: true })}
                    />
                  </div>
                  {errors.bathrooms && (
                    <p className="text-sm text-red-600 mt-1">{errors.bathrooms.message}</p>
                  )}
                </div>

                <div>
                  <Label htmlFor="squareFootage">Square Footage *</Label>
                  <div className="relative">
                    <Square className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      id="squareFootage"
                      type="number"
                      min="1"
                      className={`pl-10 ${errors.squareFootage ? 'border-red-500' : ''}`}
                      {...register('squareFootage', { valueAsNumber: true })}
                    />
                  </div>
                  {errors.squareFootage && (
                    <p className="text-sm text-red-600 mt-1">{errors.squareFootage.message}</p>
                  )}
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="yearBuilt">Year Built *</Label>
                  <div className="relative">
                    <Calendar className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      id="yearBuilt"
                      type="number"
                      min="1800"
                      max={new Date().getFullYear()}
                      className={`pl-10 ${errors.yearBuilt ? 'border-red-500' : ''}`}
                      {...register('yearBuilt', { valueAsNumber: true })}
                    />
                  </div>
                  {errors.yearBuilt && (
                    <p className="text-sm text-red-600 mt-1">{errors.yearBuilt.message}</p>
                  )}
                </div>

                <div>
                  <Label htmlFor="availableDate">Available Date</Label>
                  <Input
                    id="availableDate"
                    type="date"
                    {...register('availableDate')}
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="parking">Parking</Label>
                  <Controller
                    name="parking"
                    control={control}
                    render={({ field }) => (
                      <Select onValueChange={field.onChange} defaultValue={field.value || 'none'}>
                        <SelectTrigger>
                          <SelectValue placeholder="Select parking type" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="none">No Parking</SelectItem>
                          <SelectItem value="street">Street Parking</SelectItem>
                          <SelectItem value="garage">Garage</SelectItem>
                          <SelectItem value="driveway">Driveway</SelectItem>
                          <SelectItem value="carport">Carport</SelectItem>
                        </SelectContent>
                      </Select>
                    )}
                  />
                </div>

                <div>
                  <Label htmlFor="petPolicy">Pet Policy</Label>
                  <Input
                    id="petPolicy"
                    placeholder="e.g., Cats and small dogs allowed"
                    {...register('petPolicy')}
                  />
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Description & Features */}
          <Card>
            <CardHeader>
              <CardTitle>Description & Features</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              <div>
                <Label htmlFor="description">Property Description *</Label>
                <Textarea
                  id="description"
                  placeholder="Describe your property in detail..."
                  rows={6}
                  className={errors.description ? 'border-red-500' : ''}
                  {...register('description')}
                />
                {errors.description && (
                  <p className="text-sm text-red-600 mt-1">{errors.description.message}</p>
                )}
              </div>

              <div>
                <Label>Features & Amenities</Label>
                <p className="text-sm text-gray-600 mb-3">
                  Select all features that apply to your property
                </p>
                <div className="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-64 overflow-y-auto border rounded-lg p-4">
                  {availableFeatures.map((feature) => (
                    <div key={feature} className="flex items-center space-x-2">
                      <Checkbox
                        id={feature}
                        checked={selectedFeatures.includes(feature)}
                        onCheckedChange={() => handleFeatureToggle(feature)}
                      />
                      <Label htmlFor={feature} className="text-sm">
                        {feature}
                      </Label>
                    </div>
                  ))}
                </div>
                <p className="text-sm text-gray-500 mt-2">
                  Selected: {selectedFeatures.length} features
                </p>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="utilities">Utilities</Label>
                  <Input
                    id="utilities"
                    placeholder="e.g., Heat and hot water included"
                    {...register('utilities')}
                  />
                </div>

                <div>
                  <Label htmlFor="hoaFees">HOA Fees</Label>
                  <Input
                    id="hoaFees"
                    placeholder="e.g., $200/month"
                    {...register('hoaFees')}
                  />
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Contact Information */}
          <Card>
            <CardHeader>
              <CardTitle>Contact Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              <div>
                <Label htmlFor="contactName">Contact Name *</Label>
                <div className="relative">
                  <User className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                  <Input
                    id="contactName"
                    placeholder="Your full name"
                    className={`pl-10 ${errors.contactName ? 'border-red-500' : ''}`}
                    {...register('contactName')}
                  />
                </div>
                {errors.contactName && (
                  <p className="text-sm text-red-600 mt-1">{errors.contactName.message}</p>
                )}
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="contactPhone">Phone Number *</Label>
                  <div className="relative">
                    <Phone className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      id="contactPhone"
                      placeholder="(555) 123-4567"
                      className={`pl-10 ${errors.contactPhone ? 'border-red-500' : ''}`}
                      {...register('contactPhone')}
                    />
                  </div>
                  {errors.contactPhone && (
                    <p className="text-sm text-red-600 mt-1">{errors.contactPhone.message}</p>
                  )}
                </div>

                <div>
                  <Label htmlFor="contactEmail">Email Address *</Label>
                  <div className="relative">
                    <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      id="contactEmail"
                      type="email"
                      placeholder="your@email.com"
                      className={`pl-10 ${errors.contactEmail ? 'border-red-500' : ''}`}
                      {...register('contactEmail')}
                    />
                  </div>
                  {errors.contactEmail && (
                    <p className="text-sm text-red-600 mt-1">{errors.contactEmail.message}</p>
                  )}
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Submit Button */}
          <div className="flex justify-end space-x-4">
            <Button
              type="button"
              variant="outline"
              onClick={() => navigate('/dashboard')}
            >
              Cancel
            </Button>
            <Button type="submit" disabled={isSubmitting}>
              {isSubmitting ? 'Updating...' : 'Update Property'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default EditProperty;
