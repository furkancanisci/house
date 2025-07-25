import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useTranslation } from 'react-i18next';
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
  Plus
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

const AddProperty: React.FC = () => {
  const { state, addProperty } = useApp();
  const { user } = state;
  const navigate = useNavigate();
  const { t } = useTranslation(); // Add this line
  const [selectedFeatures, setSelectedFeatures] = useState<string[]>([]);
  const [currentStep, setCurrentStep] = useState(1);
  const totalSteps = 4;

  const {
    register,
    handleSubmit,
    control,
    formState: { errors, isSubmitting },
    watch,
    trigger,
  } = useForm<PropertyFormData>({
    resolver: zodResolver(propertySchema),
    defaultValues: {
      listingType: 'rent',
      propertyType: 'apartment',
      bedrooms: 1,
      bathrooms: 1,
      squareFootage: 500,
      yearBuilt: 2020,
      contactName: user?.name || '',
      contactEmail: user?.email || '',
      contactPhone: user?.phone || '',
    },
  });

  React.useEffect(() => {
    if (!user) {
      navigate('/auth');
    }
  }, [user, navigate]);

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

  const handleFeatureToggle = (feature: string) => {
    setSelectedFeatures(prev =>
      prev.includes(feature)
        ? prev.filter(f => f !== feature)
        : [...prev, feature]
    );
  };

  const nextStep = async () => {
    const fieldsToValidate = getFieldsForStep(currentStep);
    const isValid = await trigger(fieldsToValidate);
    
    if (isValid) {
      setCurrentStep(prev => Math.min(prev + 1, totalSteps));
    }
  };

  const prevStep = () => {
    setCurrentStep(prev => Math.max(prev - 1, 1));
  };

  const getFieldsForStep = (step: number) => {
    switch (step) {
      case 1:
        return ['title', 'address', 'listingType', 'propertyType'] as (keyof PropertyFormData)[];
      case 2:
        return ['price', 'bedrooms', 'bathrooms', 'squareFootage', 'yearBuilt'] as (keyof PropertyFormData)[];
      case 3:
        return ['description'] as (keyof PropertyFormData)[];
      case 4:
        return ['contactName', 'contactPhone', 'contactEmail'] as (keyof PropertyFormData)[];
      default:
        return [];
    }
  };

  const onSubmit = async (data: PropertyFormData) => {
    try {
      // Generate sample coordinates (in a real app, you'd geocode the address)
      const coordinates = {
        lat: 40.7128 + (Math.random() - 0.5) * 0.1,
        lng: -74.0060 + (Math.random() - 0.5) * 0.1,
      };

      // Use sample images (in a real app, user would upload images)
      const sampleImages = [
        '/images/properties/apartment_luxury_1.jpg',
        '/images/properties/apartment_interior_1.JPG',
        '/images/properties/apartment_balcony_1.jpg',
      ];

      const newProperty = {
        title: data.title,
        slug: data.title.toLowerCase()
          .replace(/[^a-z0-9]+/g, '-')
          .replace(/^-+|-+$/g, ''), // Generate slug from title
        address: data.address,
        price: data.price,
        listingType: data.listingType,
        propertyType: data.propertyType,
        bedrooms: data.bedrooms,
        bathrooms: data.bathrooms,
        squareFootage: data.squareFootage,
        description: data.description,
        features: selectedFeatures,
        images: sampleImages,
        mainImage: sampleImages[0],
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
        coordinates,
        datePosted: new Date().toISOString(),
      };

      addProperty(newProperty);
      toast.success('Property listed successfully!');
      navigate('/dashboard');
    } catch (error) {
      toast.error('Failed to create property listing');
    }
  };

  if (!user) {
    return null;
  }

  const renderStep = () => {
    switch (currentStep) {
      case 1:
        return (
          <div className="space-y-6">
            <div>
              <Label htmlFor="title">{t('forms.propertyTitle')} *</Label>
              <Input
                id="title"
                placeholder={t('forms.propertyTitlePlaceholder')}
                {...register('title')}
                className={errors.title ? 'border-red-500' : ''}
              />
              {errors.title && (
                <p className="text-sm text-red-600 mt-1">{errors.title.message}</p>
              )}
            </div>

            <div>
              <Label htmlFor="address">{t('forms.address')} *</Label>
              <div className="relative">
                <MapPin className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <Input
                  id="address"
                  placeholder={t('forms.addressPlaceholder')}
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
                <Label>{t('filters.listingType')} *</Label>
                <Controller
                  name="listingType"
                  control={control}
                  render={({ field }) => (
                    <Select onValueChange={field.onChange} defaultValue={field.value}>
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="rent">{t('property.listingTypes.forRent')}</SelectItem>
                        <SelectItem value="sale">{t('property.listingTypes.forSale')}</SelectItem>
                      </SelectContent>
                    </Select>
                  )}
                />
              </div>

              <div>
                <Label>{t('filters.propertyType')} *</Label>
                <Controller
                  name="propertyType"
                  control={control}
                  render={({ field }) => (
                    <Select onValueChange={field.onChange} defaultValue={field.value}>
                      <SelectTrigger>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="apartment">{t('property.types.apartment')}</SelectItem>
                        <SelectItem value="house">{t('property.types.house')}</SelectItem>
                        <SelectItem value="condo">{t('property.types.condo')}</SelectItem>
                        <SelectItem value="townhouse">{t('property.types.townhouse')}</SelectItem>
                      </SelectContent>
                    </Select>
                  )}
                />
              </div>
            </div>
          </div>
        );

      case 2:
        return (
          <div className="space-y-6">
            <div>
              <Label htmlFor="price">{t('forms.price')} *</Label>
              <div className="relative">
                <DollarSign className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <Input
                  id="price"
                  type="number"
                  placeholder={watch('listingType') === 'rent' ? t('forms.monthlyRent') : t('forms.salePrice')}
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
                <Label htmlFor="bedrooms">{t('property.details.bedrooms')} *</Label>
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
                <Label htmlFor="bathrooms">{t('property.details.bathrooms')} *</Label>
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
                <Label htmlFor="squareFootage">{t('property.details.squareFootage')} *</Label>
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

            <div>
              <Label htmlFor="yearBuilt">{t('property.details.yearBuilt')} *</Label>
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

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="availableDate">{t('property.details.availableDate')}</Label>
                <Input
                  id="availableDate"
                  type="date"
                  {...register('availableDate')}
                />
              </div>

              <div>
                <Label htmlFor="lotSize">{t('property.details.lotSize')}</Label>
                <Input
                  id="lotSize"
                  placeholder={t('forms.lotSizePlaceholder')}
                  {...register('lotSize')}
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="parking">{t('property.details.parking')}</Label>
                <Input
                  id="parking"
                  placeholder={t('forms.parkingPlaceholder')}
                  {...register('parking')}
                />
              </div>

              <div>
                <Label htmlFor="petPolicy">{t('property.details.petPolicy')}</Label>
                <Input
                  id="petPolicy"
                  placeholder={t('forms.petPolicyPlaceholder')}
                  {...register('petPolicy')}
                />
              </div>
            </div>
          </div>
        );

      case 3:
        return (
          <div className="space-y-6">
            <div>
              <Label htmlFor="description">{t('forms.propertyDescription')} *</Label>
              <Textarea
                id="description"
                placeholder={t('forms.propertyDescriptionPlaceholder')}
                rows={6}
                className={errors.description ? 'border-red-500' : ''}
                {...register('description')}
              />
              {errors.description && (
                <p className="text-sm text-red-600 mt-1">{errors.description.message}</p>
              )}
            </div>

            <div>
              <Label>{t('steps.features')}</Label>
              <p className="text-sm text-gray-600 mb-3">
                {t('forms.selectAllFeatures')}
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
                      {t(`property.features.${feature}`)}
                    </Label>
                  </div>
                ))}
              </div>
              <p className="text-sm text-gray-500 mt-2">
                {t('forms.selectedFeatures')}: {selectedFeatures.length} {t('forms.features')}
              </p>
            </div>

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="utilities">{t('property.details.utilities')}</Label>
                <Input
                  id="utilities"
                  placeholder={t('forms.utilitiesPlaceholder')}
                  {...register('utilities')}
                />
              </div>

              <div>
                <Label htmlFor="hoaFees">{t('property.details.hoaFees')}</Label>
                <Input
                  id="hoaFees"
                  placeholder={t('forms.hoaFeesPlaceholder')}
                  {...register('hoaFees')}
                />
              </div>
            </div>
          </div>
        );

      case 4:
        return (
          <div className="space-y-6">
            <div>
              <h3 className="text-lg font-semibold mb-4">{t('steps.contactInformation')}</h3>
              <p className="text-gray-600 mb-6">
                {t('forms.contactInfoDescription')}
              </p>
            </div>

            <div>
              <Label htmlFor="contactName">{t('forms.contactName')} *</Label>
              <div className="relative">
                <User className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <Input
                  id="contactName"
                  placeholder={t('forms.yourFullName')}
                  className={`pl-10 ${errors.contactName ? 'border-red-500' : ''}`}
                  {...register('contactName')}
                />
              </div>
              {errors.contactName && (
                <p className="text-sm text-red-600 mt-1">{errors.contactName.message}</p>
              )}
            </div>

            <div>
              <Label htmlFor="contactPhone">{t('forms.phoneNumber')} *</Label>
              <div className="relative">
                <Phone className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <Input
                  id="contactPhone"
                  placeholder={t('forms.phoneNumberPlaceholder')}
                  className={`pl-10 ${errors.contactPhone ? 'border-red-500' : ''}`}
                  {...register('contactPhone')}
                />
              </div>
              {errors.contactPhone && (
                <p className="text-sm text-red-600 mt-1">{errors.contactPhone.message}</p>
              )}
            </div>

            <div>
              <Label htmlFor="contactEmail">{t('forms.emailAddress')} *</Label>
              <div className="relative">
                <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <Input
                  id="contactEmail"
                  type="email"
                  placeholder={t('forms.emailAddressPlaceholder')}
                  className={`pl-10 ${errors.contactEmail ? 'border-red-500' : ''}`}
                  {...register('contactEmail')}
                />
              </div>
              {errors.contactEmail && (
                <p className="text-sm text-red-600 mt-1">{errors.contactEmail.message}</p>
              )}
            </div>

            <div className="bg-blue-50 p-4 rounded-lg">
              <h4 className="font-semibold text-blue-900 mb-2">{t('forms.note')}</h4>
              <p className="text-blue-800 text-sm">
                {t('forms.demoImagesNote')}
              </p>
            </div>
          </div>
        );

      default:
        return null;
    }
  };

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
            {t('dashboard.myProperties')}
          </Button>
          <div className="flex items-center space-x-2">
            <Home className="h-6 w-6 text-blue-600" />
            <h1 className="text-2xl font-bold text-gray-900">{t('navigation.listProperty')}</h1>
          </div>
        </div>

        {/* Progress Bar */}
        <div className="mb-8">
          <div className="flex items-center justify-between mb-2">
            <span className="text-sm font-medium text-gray-700">
              {t('forms.step')} {currentStep} {t('forms.of')} {totalSteps}
            </span>
            <span className="text-sm text-gray-500">
              {Math.round((currentStep / totalSteps) * 100)}% {t('forms.complete')}
            </span>
          </div>
          <div className="w-full bg-gray-200 rounded-full h-2">
            <div
              className="bg-blue-600 h-2 rounded-full transition-all duration-300"
              style={{ width: `${(currentStep / totalSteps) * 100}%` }}
            />
          </div>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit(onSubmit)}>
          <Card>
            <CardHeader>
              <CardTitle>
                {currentStep === 1 && t('steps.basicInformation')}
                {currentStep === 2 && t('steps.propertyDetails')}
                {currentStep === 3 && t('steps.features')}
                {currentStep === 4 && t('steps.contactInformation')}
              </CardTitle>
            </CardHeader>
            <CardContent>{renderStep()}</CardContent>
          </Card>

          {/* Navigation Buttons */}
          <div className="flex justify-between mt-8">
            <Button
              type="button"
              variant="outline"
              onClick={prevStep}
              disabled={currentStep === 1}
            >
              {t('buttons.previous')}
            </Button>

            <div className="flex space-x-4">
              {currentStep < totalSteps ? (
                <Button type="button" onClick={nextStep}>
                  {t('buttons.next')}
                </Button>
              ) : (
                <Button type="submit" disabled={isSubmitting}>
                  {isSubmitting ? t('buttons.creatingListing') : t('buttons.createListing')}
                </Button>
              )}
            </div>
          </div>
        </form>
      </div>
    </div>
  );
};

export default AddProperty;
