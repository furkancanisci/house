import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { Property } from '../types';
import { updateProperty as updatePropertyAPI, getProperty } from '../services/propertyService';
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
  User,
  Upload,
  X,
  Image as ImageIcon,
  File
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
import { useTranslation } from 'react-i18next';
import i18n from '../i18n';
import FixedImage from '../components/FixedImage';
import LocationSelector from '../components/LocationSelector';
import EnhancedDocumentTypeSelect from '../components/EnhancedDocumentTypeSelect';
import { propertyDocumentTypeService, PropertyDocumentType } from '../services/propertyDocumentTypeService';
import { priceTypeService, PriceType } from '../services/priceTypeService';

const propertySchema = z.object({
  title: z.string().min(1, 'Property title is required'),
  address: z.string().min(1, 'Address is required'),
  city: z.string().min(1, 'City is required').max(100, 'City cannot exceed 100 characters'),
  state: z.string().min(1, 'State is required').max(100, 'State cannot exceed 100 characters'),
  price: z.number().min(1, 'Price must be greater than 0'),
  priceType: z.string().min(1, 'Price type is required'),
  listingType: z.enum(['rent', 'sale']),
  propertyType: z.enum(['apartment', 'house', 'condo', 'townhouse', 'studio', 'loft', 'villa', 'commercial', 'land']),
  documentTypeId: z.string().optional(),
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
  const { t } = useTranslation();
  const [property, setProperty] = useState<Property | null>(null);
  const [selectedFeatures, setSelectedFeatures] = useState<string[]>([]);
  const [selectedImages, setSelectedImages] = useState<File[]>([]);
  const [imagePreviewUrls, setImagePreviewUrls] = useState<string[]>([]);
  const [existingImages, setExistingImages] = useState<any[]>([]);
  const [imagesToRemove, setImagesToRemove] = useState<string[]>([]);
  const [selectedCity, setSelectedCity] = useState<string>('');
  const [selectedState, setSelectedState] = useState<string>('');
  
  // Document types state
  const [documentTypes, setDocumentTypes] = useState<PropertyDocumentType[]>([]);
  const [loadingDocumentTypes, setLoadingDocumentTypes] = useState(false);
  
  // Price types state
  const [priceTypes, setPriceTypes] = useState<PriceType[]>([]);
  const [priceTypesLoading, setPriceTypesLoading] = useState(false);
  const [priceTypesError, setPriceTypesError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    control,
    watch,
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

    const loadProperty = async () => {
      if (!id) {
        toast.error('Property ID is required');
        navigate('/dashboard');
        return;
      }

      try {
        // First try to find the property in the state
        let foundProperty = null;
        if (properties.length > 0) {
          foundProperty = properties.find(p => p.id.toString() === id);
        }

        // If not found in state, fetch from API
        if (!foundProperty) {
  
          try {
            foundProperty = await getProperty(id);
  
          } catch (apiError) {
  
            toast.error('Property not found');
            navigate('/dashboard');
            return;
          }
        }

        if (foundProperty) {
          // Check if user owns this property
          const propertyOwnerEmail = foundProperty.contact?.email || foundProperty.contact_email;
          if (propertyOwnerEmail && propertyOwnerEmail !== user.email) {
            toast.error('You can only edit your own properties');
            navigate('/dashboard');
            return;
          }

          setProperty(foundProperty);
          setSelectedFeatures(foundProperty.features || []);
          setSelectedCity(foundProperty.city || '');
          setSelectedState(foundProperty.state || '');
          
          // Load existing images - handle different image formats safely
          if (foundProperty.images) {
            // Handle case where images is an object with gallery property
            if (foundProperty.images && typeof foundProperty.images === 'object' && 'gallery' in foundProperty.images) {
              const gallery = (foundProperty.images as any).gallery;
              setExistingImages(Array.isArray(gallery) ? gallery : []);
            } 
            // Handle case where images is directly an array
            else if (Array.isArray(foundProperty.images)) {
              setExistingImages(foundProperty.images);
            }
            // Handle case where images is a string (single image URL)
            else if (typeof foundProperty.images === 'string') {
              setExistingImages([foundProperty.images]);
            }
          }
          
          // Reset form with property data
          reset({
            title: foundProperty.title || '',
            address: foundProperty.address || foundProperty.full_address || '',
            city: foundProperty.city || '',
            state: foundProperty.state || '',
            price: Number(foundProperty.price) || 0,
            priceType: foundProperty.priceType || foundProperty.price_type || 'total',
            listingType: foundProperty.listingType || foundProperty.listing_type || 'sale',
            propertyType: foundProperty.propertyType || foundProperty.property_type || 'apartment',
            documentTypeId: foundProperty.document_type_id ? String(foundProperty.document_type_id) : '',
            bedrooms: Number(foundProperty.bedrooms) || 0,
            bathrooms: Number(foundProperty.bathrooms) || 0,
            squareFootage: Number(foundProperty.squareFootage || foundProperty.square_feet) || 0,
            description: foundProperty.description || '',
            yearBuilt: foundProperty.yearBuilt || foundProperty.year_built || new Date().getFullYear(),
            availableDate: foundProperty.availableDate || foundProperty.available_from || '',
            petPolicy: foundProperty.petPolicy || '',
            parking: foundProperty.parking || '',
            utilities: foundProperty.utilities || '',
            lotSize: foundProperty.lotSize ? String(foundProperty.lotSize) : '',
            garage: foundProperty.garage || '',
            heating: foundProperty.heating || '',
            hoaFees: foundProperty.hoaFees || '',
            building: foundProperty.building || '',
            pool: foundProperty.pool || '',
            contactName: foundProperty.contact?.name || foundProperty.contact_name || '',
            contactPhone: foundProperty.contact?.phone || foundProperty.contact_phone || '',
            contactEmail: foundProperty.contact?.email || foundProperty.contact_email || '',
          });
        } else {
          toast.error('Property not found');
          navigate('/dashboard');
        }
      } catch (error) {
  
        toast.error('Failed to load property');
        navigate('/dashboard');
      }
    };

    loadProperty();
  }, [id, properties, user, navigate, reset]);

  // Load property document types
  useEffect(() => {
    const loadDocumentTypes = async () => {
      setLoadingDocumentTypes(true);
      try {
        const types = await propertyDocumentTypeService.getPropertyDocumentTypes({
          lang: 'ar' // Default to Arabic for edit form
        });
        setDocumentTypes(types);
      } catch (error) {
  
        // Use fallback data if API fails
        const fallbackTypes = propertyDocumentTypeService.getFallbackDocumentTypes('ar');
        setDocumentTypes(fallbackTypes);
      } finally {
        setLoadingDocumentTypes(false);
      }
    };

    loadDocumentTypes();
  }, []);

  // Fetch price types from API
  const fetchPriceTypes = async (listingType?: 'rent' | 'sale') => {
    setPriceTypesLoading(true);
    setPriceTypesError(null);
    try {
      const priceTypesData = await priceTypeService.getPriceTypes(listingType);
      setPriceTypes(priceTypesData);
    } catch (error) {

      setPriceTypesError('Failed to load price types');
      toast.error('Failed to load price types');
    } finally {
      setPriceTypesLoading(false);
    }
  };

  // Load price types on component mount and when listing type changes
  useEffect(() => {
    fetchPriceTypes();
  }, []);

  useEffect(() => {
    const listingType = watch('listingType');
    if (listingType) {
      fetchPriceTypes(listingType);
    }
  }, [watch('listingType')]);

  const handleFeatureToggle = (feature: string) => {
    setSelectedFeatures(prev =>
      prev.includes(feature)
        ? prev.filter(f => f !== feature)
        : [...prev, feature]
    );
  };

  const handleLocationChange = (location: { state?: string; city?: string }) => {
    if (location.city) {
      setSelectedCity(location.city);
    }
    if (location.state) {
      setSelectedState(location.state);
    }
  };

  const handleImageUpload = (event: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(event.target.files || []);
    const totalImages = existingImages.length + selectedImages.length + files.length - imagesToRemove.length;
    
    if (totalImages > 10) {
      toast.error('Maximum 10 images allowed');
      return;
    }

    setSelectedImages(prev => [...prev, ...files]);
    
    // Create preview URLs
    files.forEach(file => {
      const reader = new FileReader();
      reader.onload = (e) => {
        setImagePreviewUrls(prev => [...prev, e.target?.result as string]);
      };
      reader.readAsDataURL(file);
    });
  };

  const removeNewImage = (index: number) => {
    setSelectedImages(prev => prev.filter((_, i) => i !== index));
    setImagePreviewUrls(prev => prev.filter((_, i) => i !== index));
  };

  const removeExistingImage = (imageId: string) => {
    setImagesToRemove(prev => [...prev, imageId]);
  };

  const restoreExistingImage = (imageId: string) => {
    setImagesToRemove(prev => prev.filter(id => id !== imageId));
  };

  const onSubmit = async (data: PropertyFormData) => {
    if (!property) return;

    try {
      const updatedProperty: any = {
        title: data.title,
        address: data.address,
        city: selectedCity || data.city,
        state: selectedState || data.state,
        price: data.price,
        priceType: data.priceType,
        listingType: data.listingType,
        propertyType: data.propertyType,
        documentTypeId: data.documentTypeId ? Number(data.documentTypeId) : undefined,
        bedrooms: data.bedrooms,
        bathrooms: data.bathrooms,
        squareFootage: data.squareFootage,
        description: data.description,
        amenities: selectedFeatures,
        yearBuilt: data.yearBuilt,
        availableDate: data.availableDate,
        petPolicy: data.petPolicy,
        parking: data.parking,
        utilities: data.utilities,
        lotSize: data.lotSize ? Number(data.lotSize) : undefined,
        hoaFees: data.hoaFees,
        contactName: data.contactName,
        contactPhone: data.contactPhone,
        contactEmail: data.contactEmail,
        // Image handling
        images: selectedImages, // New images to upload
        imagesToRemove: imagesToRemove, // Existing images to remove
      };

      // Update the property using the API service
      await updatePropertyAPI(Number(property.id), updatedProperty);
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
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#067977] mx-auto mb-4"></div>
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
            Back to Profile
          </Button>
          <div className="flex items-center space-x-2">
            <Home className="h-6 w-6 text-[#067977]" />
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

              <div>
                <LocationSelector
                  onLocationChange={handleLocationChange}
                  selectedCity={selectedCity}
                  selectedState={selectedState}
                />
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
                          <SelectItem key="rent" value="rent">For Rent</SelectItem>
                          <SelectItem key="sale" value="sale">For Sale</SelectItem>
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
                          <SelectItem key="apartment" value="apartment">Apartment</SelectItem>
                          <SelectItem key="house" value="house">House</SelectItem>
                          <SelectItem key="condo" value="condo">Condo</SelectItem>
                          <SelectItem key="townhouse" value="townhouse">Townhouse</SelectItem>
                          <SelectItem key="studio" value="studio">Studio</SelectItem>
                          <SelectItem key="loft" value="loft">Loft</SelectItem>
                          <SelectItem key="villa" value="villa">Villa</SelectItem>
                          <SelectItem key="commercial" value="commercial">Commercial</SelectItem>
                          <SelectItem key="land" value="land">Land</SelectItem>
                        </SelectContent>
                      </Select>
                    )}
                  />
                </div>
              </div>
              
              {/* Enhanced Document Type Section */}
              <div>
                <Label>نوع التابو</Label>
                <Controller
                  name="documentTypeId"
                  control={control}
                  render={({ field }) => (
                    <EnhancedDocumentTypeSelect
                      value={field.value}
                      onValueChange={field.onChange}
                      placeholder={t('addProperty.placeholders.selectDocumentType')}
                      loading={loadingDocumentTypes}
                      documentTypes={documentTypes}
                      showDescriptions={true}
                      className="w-full"
                    />
                  )}
                />
              </div>
            </CardContent>
          </Card>

          {/* Property Details */}
          <Card>
            <CardHeader>
              <CardTitle>Property Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="price">Price *</Label>
                  <div className="relative">
                    <DollarSign className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                      id="price"
                      type="number"
                      placeholder="Monthly rent or sale price"
                      className={`pl-10 ${errors.price ? 'border-red-500' : ''}`}
                      {...register('price', { 
                  setValueAs: (value) => value === '' ? undefined : Number(value)
                })}
                    />
                  </div>
                  {errors.price && (
                    <p className="text-sm text-red-600 mt-1">{errors.price.message}</p>
                  )}
                </div>

                <div>
                  <Label htmlFor="priceType">{t('property.priceType')} *</Label>
                  <Controller
                    name="priceType"
                    control={control}
                    render={({ field }) => (
                      <Select onValueChange={field.onChange} value={field.value}>
                        <SelectTrigger className={`h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-emerald-100 hover:border-emerald-300 ${errors.priceType ? 'border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-emerald-500'}`}>
                          <SelectValue placeholder={t('property.selectPriceType')} />
                        </SelectTrigger>
                        <SelectContent className="bg-white border-2 border-gray-100 rounded-xl shadow-lg">
                          {priceTypesLoading ? (
                            <SelectItem value="loading" disabled className="text-sm py-3 px-4">
                              {t('common.loading')}
                            </SelectItem>
                          ) : priceTypesError ? (
                            <SelectItem value="error" disabled className="text-sm py-3 px-4 text-red-500">
                              {t('common.error')}
                            </SelectItem>
                          ) : (
                            priceTypes.map((priceType) => (
                              <SelectItem 
                                key={priceType.id} 
                                value={priceType.key} 
                                className="text-sm py-3 px-4 hover:bg-emerald-50 focus:bg-emerald-100 transition-colors duration-150 cursor-pointer"
                              >
                                {i18n.language === 'ar' ? priceType.name_ar : 
                                 i18n.language === 'ku' ? priceType.name_ku : 
                                 priceType.name_en}
                              </SelectItem>
                            ))
                          )}
                        </SelectContent>
                      </Select>
                    )}
                  />
                  {errors.priceType && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <span className="text-red-500">⚠</span>
                      {errors.priceType.message}
                    </p>
                  )}
                </div>
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
                      {...register('bedrooms', { 
                  setValueAs: (value) => value === '' ? undefined : Number(value)
                })}
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
                      {...register('bathrooms', { 
                  setValueAs: (value) => value === '' ? undefined : Number(value)
                })}
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
                      {...register('squareFootage', { 
                  setValueAs: (value) => value === '' ? undefined : Number(value)
                })}
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
                      {...register('yearBuilt', { 
                   setValueAs: (value) => value === '' ? undefined : Number(value)
                 })}
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
                      <Select onValueChange={field.onChange} value={field.value || 'none'}>
                        <SelectTrigger>
                          <SelectValue placeholder="Select parking type" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem key="none" value="none">No Parking</SelectItem>
                          <SelectItem key="street" value="street">Street Parking</SelectItem>
                          <SelectItem key="garage" value="garage">Garage</SelectItem>
                          <SelectItem key="driveway" value="driveway">Driveway</SelectItem>
                          <SelectItem key="carport" value="carport">Carport</SelectItem>
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

          {/* Image Management */}
          <Card>
            <CardHeader>
              <CardTitle>Property Images</CardTitle>
            </CardHeader>
            <CardContent className="space-y-6">
              {/* Existing Images */}
              {existingImages.length > 0 && (
                <div>
                  <Label>Current Images</Label>
                  <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-3">
                    {existingImages.map((image, index) => (
                      <div key={image.id || index} className="relative group">
                        <FixedImage
                          src={image.url || image.original_url}
                          alt={`Property image ${index + 1}`}
                          className={`w-full h-32 object-cover rounded-lg border shadow-sm ${
                            imagesToRemove.includes(image.id) ? 'opacity-50 grayscale' : ''
                          }`}
                          showLoadingSpinner={true}
                        />
                        {!imagesToRemove.includes(image.id) ? (
                          <button
                            type="button"
                            onClick={() => removeExistingImage(image.id)}
                            className="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                          >
                            <X className="h-4 w-4" />
                          </button>
                        ) : (
                          <button
                            type="button"
                            onClick={() => restoreExistingImage(image.id)}
                            className="absolute top-2 right-2 bg-green-500 text-white rounded-full p-1 opacity-100"
                          >
                            <Upload className="h-4 w-4" />
                          </button>
                        )}
                        {index === 0 && !imagesToRemove.includes(image.id) && (
                          <div className="absolute bottom-2 left-2 bg-[#067977] text-white text-xs px-2 py-1 rounded">
                            Main Photo
                          </div>
                        )}
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* Upload New Images */}
              <div>
                <Label>Add New Images</Label>
                <p className="text-sm text-gray-600 mb-4">
                  Upload additional high-quality images of your property (Max 10 total images).
                </p>
                
                <div className="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                  <input
                    type="file"
                    multiple
                    accept="image/*"
                    onChange={handleImageUpload}
                    className="hidden"
                    id="image-upload"
                  />
                  <label htmlFor="image-upload" className="cursor-pointer">
                    <Upload className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                    <p className="text-lg font-medium text-gray-900 mb-2">
                      Click to upload images
                    </p>
                    <p className="text-sm text-gray-500">
                      PNG, JPG, GIF up to 10MB each
                    </p>
                  </label>
                </div>

                {/* New Images Preview */}
                {selectedImages.length > 0 && (
                  <div className="mt-6">
                    <h4 className="font-medium text-gray-900 mb-3">
                      New Images ({selectedImages.length})
                    </h4>
                    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                      {imagePreviewUrls.map((url, index) => (
                        <div key={index} className="relative group">
                          <FixedImage
                            src={url}
                            alt={`New image ${index + 1}`}
                            className="w-full h-32 object-cover rounded-lg border shadow-sm"
                            showLoadingSpinner={true}
                          />
                          <button
                            type="button"
                            onClick={() => removeNewImage(index)}
                            className="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                          >
                            <X className="h-4 w-4" />
                          </button>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
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
