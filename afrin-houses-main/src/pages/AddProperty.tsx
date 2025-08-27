import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Property, SearchFilters } from '../types';
import { useTranslation } from 'react-i18next';
import {
  ArrowLeft,
  ArrowRight,
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
  Plus,
  Image as ImageIcon,
  FileText,
  Building,
  Camera,
  Star,
  CheckCircle,
  Car,
  File
} from 'lucide-react';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { InputWithIcon } from '../components/ui/input-with-icon';
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
import FixedImage from '../components/FixedImage';
import LocationSelector from '../components/LocationSelector';
import PropertyLocationMap from '../components/PropertyLocationMap';
import EnhancedDocumentTypeSelect from '../components/EnhancedDocumentTypeSelect';
import { propertyDocumentTypeService, PropertyDocumentType } from '../services/propertyDocumentTypeService';

const propertySchema = z.object({
  title: z.string().min(1, 'Property title is required').max(255, 'Title cannot exceed 255 characters'),
  address: z.string().min(1, 'Address is required'),
  city: z.string().min(1, 'City is required').max(100, 'City cannot exceed 100 characters'),
  state: z.string().min(1, 'State is required').max(100, 'State cannot exceed 100 characters'),
  postalCode: z.string().min(1, 'Postal code is required').max(20, 'Postal code cannot exceed 20 characters'),
  price: z.number().min(0, 'Price must be 0 or greater').max(99999999.99, 'Price is too high'),
  listingType: z.enum(['rent', 'sale']),
  propertyType: z.enum(['apartment', 'house', 'condo', 'townhouse', 'studio', 'loft', 'villa', 'commercial', 'land']),
  documentTypeId: z.string().optional(),
  bedrooms: z.number().min(0, 'Bedrooms must be 0 or greater').max(20, 'Bedrooms cannot exceed 20'),
  bathrooms: z.number().min(0, 'Bathrooms must be 0 or greater').max(20, 'Bathrooms cannot exceed 20'),
  squareFootage: z.number().min(1, 'Square footage must be greater than 0').max(50000, 'Square footage cannot exceed 50,000').optional(),
  description: z.string().min(10, 'Description must be at least 10 characters').max(5000, 'Description cannot exceed 5000 characters'),
  yearBuilt: z.number().min(1800, 'Year built must be valid').max(new Date().getFullYear() + 2, 'Year built cannot be in the future').optional(),
  availableDate: z.string().optional(),
  petPolicy: z.string().optional(),
  parking: z.string().min(1, 'Parking type is required'),
  utilities: z.string().optional(),
  lotSize: z.union([
    z.string()
      .refine(val => val === '' || /^\d+$/.test(val), {
        message: 'Lot size must be a positive integer'
      })
      .transform((val) => val === '' ? undefined : parseInt(val, 10))
      .refine(val => val === undefined || val > 0, {
        message: 'Lot size must be greater than 0'
      })
      .refine(val => val === undefined || val <= 1000000, {
        message: 'Lot size is too large'
      })
      .optional(),
    z.number()
      .int('Lot size must be an integer')
      .positive('Lot size must be greater than 0')
      .max(1000000, 'Lot size is too large')
      .optional()
  ]).optional().default(undefined),
  garage: z.string().optional(),
  heating: z.string().optional(),
  hoaFees: z.string().optional(),
  building: z.string().optional(),
  pool: z.string().optional(),
  contactName: z.string().min(1, 'Contact name is required'),
  contactPhone: z.string().min(1, 'Contact phone is required'),
  contactEmail: z.string().email('Valid email is required'),
  latitude: z.number().optional(),
  longitude: z.number().optional(),
});

type PropertyFormData = z.infer<typeof propertySchema>;

const AddProperty: React.FC = () => {
  const { state, addProperty } = useApp();
  const { user, language, loading } = state;
  const navigate = useNavigate();
  const { t, i18n } = useTranslation();
  const isRTL = i18n.language === 'ar';
  const [selectedFeatures, setSelectedFeatures] = useState<string[]>(() => {
    // Restore selected features from localStorage
    const savedFeatures = localStorage.getItem('addProperty_selectedFeatures');
    return savedFeatures ? JSON.parse(savedFeatures) : [];
  });
  const [selectedUtilities, setSelectedUtilities] = useState<string[]>(() => {
    // Restore selected utilities from localStorage
    const savedUtilities = localStorage.getItem('addProperty_selectedUtilities');
    return savedUtilities ? JSON.parse(savedUtilities) : [];
  });
  const [selectedImages, setSelectedImages] = useState<File[]>([]);
  const [imagePreviewUrls, setImagePreviewUrls] = useState<string[]>(() => {
    // Restore image preview URLs from localStorage
    const savedPreviewUrls = localStorage.getItem('addProperty_imagePreviewUrls');
    return savedPreviewUrls ? JSON.parse(savedPreviewUrls) : [];
  });
  const [currentStep, setCurrentStep] = useState(() => {
    // Restore current step from localStorage on page refresh
    const savedStep = localStorage.getItem('addProperty_currentStep');
    return savedStep ? parseInt(savedStep, 10) : 1;
  });
  const totalSteps = 5;

  // Document types state
  const [documentTypes, setDocumentTypes] = useState<PropertyDocumentType[]>([]);
  const [loadingDocumentTypes, setLoadingDocumentTypes] = useState(false);

  // Location state
  const [selectedCity, setSelectedCity] = useState<string>(() => {
    // Restore selected city from localStorage
    return localStorage.getItem('addProperty_selectedCity') || '';
  });
  const [selectedState, setSelectedState] = useState<string>(() => {
    // Restore selected state from localStorage
    return localStorage.getItem('addProperty_selectedState') || '';
  });
  const [coordinates, setCoordinates] = useState<{ lat: number; lng: number } | null>(() => {
    // Restore coordinates from localStorage
    const savedCoordinates = localStorage.getItem('addProperty_coordinates');
    return savedCoordinates ? JSON.parse(savedCoordinates) : null;
  });

  const {
    register,
    handleSubmit,
    control,
    formState: { errors, isSubmitting },
    watch,
    trigger,
    setValue,
    getValues,
  } = useForm<PropertyFormData>({
    resolver: zodResolver(propertySchema),
    defaultValues: (() => {
      // Try to restore form data from localStorage
      const savedFormData = localStorage.getItem('addProperty_formData');
      if (savedFormData) {
        try {
          const parsedData = JSON.parse(savedFormData);
          return {
            ...parsedData,
            contactName: parsedData.contactName || user?.name || '',
            contactEmail: parsedData.contactEmail || user?.email || '',
            contactPhone: parsedData.contactPhone || user?.phone || '',
          };
        } catch (error) {
          console.error('Error parsing saved form data:', error);
        }
      }
      
      // Default values if no saved data
      return {
        title: '',
        address: '',
        city: '',
        state: '',
        postalCode: '',
        listingType: 'rent',
        propertyType: 'apartment',
        bedrooms: 1,
        bathrooms: 1,
        squareFootage: 500,
        yearBuilt: 2020,
        price: 0,
        description: '',
        parking: 'none',
        availableDate: new Date().toISOString().split('T')[0], // Today's date in YYYY-MM-DD format
        contactName: user?.name || '',
        contactEmail: user?.email || '',
        contactPhone: user?.phone || '',
        latitude: undefined,
        longitude: undefined,
      };
    })(),
  });

  React.useEffect(() => {
    // Only redirect if loading is complete and user is still null
    if (!loading && !user) {
      navigate('/auth');
    }
  }, [user, navigate, loading]);

  // Save form data to localStorage whenever form values change
  React.useEffect(() => {
    const subscription = watch((data) => {
      try {
        localStorage.setItem('addProperty_formData', JSON.stringify(data));
      } catch (error) {
        console.error('Error saving form data to localStorage:', error);
      }
    });
    return () => subscription.unsubscribe();
  }, [watch]);

  // Save current step to localStorage
  React.useEffect(() => {
    localStorage.setItem('addProperty_currentStep', currentStep.toString());
  }, [currentStep]);

  // Save selected features to localStorage
  React.useEffect(() => {
    localStorage.setItem('addProperty_selectedFeatures', JSON.stringify(selectedFeatures));
  }, [selectedFeatures]);

  // Save selected utilities to localStorage
  React.useEffect(() => {
    localStorage.setItem('addProperty_selectedUtilities', JSON.stringify(selectedUtilities));
  }, [selectedUtilities]);

  // Save image preview URLs to localStorage
  React.useEffect(() => {
    localStorage.setItem('addProperty_imagePreviewUrls', JSON.stringify(imagePreviewUrls));
  }, [imagePreviewUrls]);

  // Save location data to localStorage
  React.useEffect(() => {
    localStorage.setItem('addProperty_selectedCity', selectedCity);
  }, [selectedCity]);

  React.useEffect(() => {
    localStorage.setItem('addProperty_selectedState', selectedState);
  }, [selectedState]);

  React.useEffect(() => {
    if (coordinates) {
      localStorage.setItem('addProperty_coordinates', JSON.stringify(coordinates));
    }
  }, [coordinates]);

  // Note: Removed beforeunload warning since data is now automatically saved to localStorage

  // Load property document types
  React.useEffect(() => {
    const loadDocumentTypes = async () => {
      setLoadingDocumentTypes(true);
      try {
        const types = await propertyDocumentTypeService.getPropertyDocumentTypes({
          lang: i18n.language
        });
        setDocumentTypes(types);
      } catch (error) {
        console.error('Failed to load document types:', error);
        // Use fallback data if API fails
        const fallbackTypes = propertyDocumentTypeService.getFallbackDocumentTypes(i18n.language);
        setDocumentTypes(fallbackTypes);
      } finally {
        setLoadingDocumentTypes(false);
      }
    };

    loadDocumentTypes();
  }, [i18n.language]);

  const availableFeatures = [
    'Air Conditioning',
    'Heating',
    'Dishwasher',
    'Laundry in Unit',
    'Laundry in Building',
    'Balcony',
    'Patio',
    'Garden',
    'Roof Deck',
    'Terrace',
    'Fireplace',
    'Hardwood Floors',
    'Carpet',
    'Tile Floors',
    'High Ceilings',
    'Walk-in Closet',
    'Storage',
    'Basement',
    'Attic',
    'Garage',
    'Parking',
    'Elevator',
    'Doorman',
    'Concierge',
    'Security System',
    'Intercom',
    'Video Security',
    'Gym',
    'Pool',
    'Hot Tub',
    'Sauna',
    'Tennis Court',
    'Basketball Court',
    'Playground',
    'Dog Park',
    'Pet Friendly',
    'No Pets',
    'Furnished',
    'Unfurnished',
    'Internet',
    'Cable TV',
    'Utilities Included',
    'Recently Renovated',
    'New Construction',
    'Outdoor Kitchen',
    'Master Suite',
    'Updated Kitchen',
    'Updated Bathroom',
    'Close to Transit',
    'Ocean View',
    'City View',
    'Private Elevator',
    'Spa',
    'Wine Cellar',
    'Smart Home',
    'Historic Details',
    'Bay Windows',
    'Crown Molding',
    'Community Pool',
    'Washer/Dryer',
    'In-Unit Laundry',
    'Rooftop Deck',
    'Fitness Center',
    'Single Story',
    'Large Backyard',
    'Desert Landscaping',
  ];

  const availableUtilities = [
    'Electricity',
    'Water',
    'Gas',
    'Internet',
    'Cable TV',
    'Trash Collection',
    'Sewer',
    'Heat',
    'Air Conditioning',
    'Hot Water',
    'Electricity Included',
    'Water Included',
    'Gas Included',
    'Internet Included',
    'Cable TV Included',
    'All Utilities Included',
  ];

  const handleFeatureToggle = (feature: string) => {
    setSelectedFeatures(prev => {
      const newFeatures = prev.includes(feature)
        ? prev.filter(f => f !== feature)
        : [...prev, feature];
      return newFeatures;
    });
  };

  // Function to clear form data from localStorage
  const clearFormDataFromStorage = () => {
    const keysToRemove = [
      'addProperty_formData',
      'addProperty_currentStep',
      'addProperty_selectedFeatures',
      'addProperty_selectedUtilities',
      'addProperty_imagePreviewUrls',
      'addProperty_selectedCity',
      'addProperty_selectedState',
      'addProperty_coordinates'
    ];
    
    keysToRemove.forEach(key => {
      localStorage.removeItem(key);
    });
  };

  const handleUtilityToggle = (utility: string) => {
    setSelectedUtilities(prev => {
      const newUtilities = prev.includes(utility)
        ? prev.filter(u => u !== utility)
        : [...prev, utility];
      return newUtilities;
    });
  };

  const handleLocationChange = (location: { state?: string; city?: string }) => {
    if (location.city) {
      setSelectedCity(location.city);
      setValue('city', location.city); // Update form field for validation
    }
    if (location.state) {
      setSelectedState(location.state);
      setValue('state', location.state); // Update form field for validation
    }
  };

  const handleCoordinatesChange = (location: { latitude: number; longitude: number; address?: string }) => {
    const coords = { lat: location.latitude, lng: location.longitude };
    setCoordinates(coords);
    setValue('latitude', location.latitude);
    setValue('longitude', location.longitude);
  };

  // Helper function to get feature translation with fallback
  const getFeatureTranslation = (feature: string): string => {
    // Convert feature name to translation key format
    const translationKey = feature.toLowerCase().replace(/\s+/g, '').replace(/[^a-z0-9]/g, '');

    // Try to get translation, fallback to original feature name if not found
    const translation = t(`property.features.${translationKey}`, { defaultValue: feature });

    // Ensure we always return a string
    return typeof translation === 'string' ? translation : feature;
  };

  const nextStep = async () => {
    console.log('Next button clicked, current step:', currentStep);
    const fieldsToValidate = getFieldsForStep(currentStep);
    console.log('Fields to validate:', fieldsToValidate);

    try {
      const isValid = await trigger(fieldsToValidate);
      console.log('Validation result:', isValid);
      console.log('Current errors:', errors);

      if (isValid) {
        console.log('Validation passed, moving to next step');
        setCurrentStep(prev => Math.min(prev + 1, totalSteps));
      } else {
        console.log('Validation failed, staying on current step');
        // Show validation errors to user
        toast.error('Please fill in all required fields correctly');
      }
    } catch (error) {
      console.error('Error during validation:', error);
      toast.error('Validation error occurred');
    }
  };

  const prevStep = () => {
    setCurrentStep(prev => Math.max(prev - 1, 1));
  };

  const handleImageUpload = (event: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(event.target.files || []);
    if (files.length + selectedImages.length > 10) {
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

  const removeImage = (index: number) => {
    setSelectedImages(prev => prev.filter((_, i) => i !== index));
    setImagePreviewUrls(prev => prev.filter((_, i) => i !== index));
  };

  const getFieldsForStep = (step: number) => {
    switch (step) {
      case 1:
        return ['title', 'address', 'city', 'state', 'postalCode', 'listingType', 'propertyType'] as (keyof PropertyFormData)[];
      case 2:
        return ['price', 'bedrooms', 'bathrooms', 'squareFootage', 'yearBuilt', 'parking'] as (keyof PropertyFormData)[];
      case 3:
        return [] as (keyof PropertyFormData)[]; // Image upload step - no form validation needed
      case 4:
        return ['description'] as (keyof PropertyFormData)[];
      case 5:
        return ['contactName', 'contactPhone', 'contactEmail'] as (keyof PropertyFormData)[];
      default:
        return [];
    }
  };

  const onSubmit = async (data: PropertyFormData) => {
    if (isSubmitting) {
      console.log('Form submission already in progress');
      return; // Prevent multiple submissions
    }

    // Ensure numeric fields are properly converted
    const formData = {
      ...data,
      price: Number(data.price) || 0,
      bedrooms: Number(data.bedrooms) || 0,
      bathrooms: Number(data.bathrooms) || 0,
      squareFootage: Number(data.squareFootage) || 0,
      yearBuilt: data.yearBuilt ? Number(data.yearBuilt) : undefined,
      lotSize: data.lotSize ? Number(data.lotSize) : undefined,
      parking: data.parking ? Number(data.parking) : 0
    };

    console.log('Processed form data:', formData);

    try {
      console.log('Starting form submission with data:', data);

      // Parse address components
      const addressParts = formData.address?.split(',').map(part => part.trim()) || [];
      const street = addressParts[0] || '';
      let city = selectedCity || formData.city || addressParts[1] || '';
      let state = selectedState || formData.state || '';
      let postalCode = formData.postalCode || '';

      console.log('Parsed address:', { street, city, state, postalCode });

      // If city, state, postalCode are not provided, try to parse from address
      if (!city || !state || !postalCode) {
        if (addressParts.length >= 3) {
          city = city || addressParts[1];
          const lastPart = addressParts[addressParts.length - 1];
          const stateZipMatch = lastPart.match(/^(.+?)\s+(\d{5}(?:-\d{4})?)$/);
          if (stateZipMatch) {
            state = state || stateZipMatch[1];
            postalCode = postalCode || stateZipMatch[2];
          }
        }
      }

      // Create the property data object
      const propertyData: any = {
        title: data.title,
        description: data.description || '',
        propertyType: data.propertyType,
        listingType: data.listingType,
        price: parseFloat(data.price.toString()),
        address: street,
        city: city,
        state: state,
        postalCode: postalCode,
        latitude: coordinates?.lat,
        longitude: coordinates?.lng,
        bedrooms: Number(data.bedrooms || 0),
        bathrooms: Number(data.bathrooms || 0),
        squareFootage: Number(data.squareFootage || 0),
        lotSize: Number(data.lotSize || 0),
        yearBuilt: Number(data.yearBuilt || new Date().getFullYear()),
        parking: data.parking || 'none',
        status: 'active',
        is_featured: false,
        is_available: true,
        availableDate: data.availableDate || new Date().toISOString(),
        amenities: Array.isArray(selectedFeatures) ? selectedFeatures : [],
        contactName: data.contactName || '',
        contactPhone: data.contactPhone || '',
        contactEmail: data.contactEmail || '',
        document_type_id: data.documentTypeId ? Number(data.documentTypeId) : undefined,
      };

      // Create FormData for file uploads
      const formDataToSend = new FormData();

      // Add all property data fields to FormData
      Object.keys(propertyData).forEach(key => {
        const value = propertyData[key];
        if (value !== null && value !== undefined) {
          if (key === 'amenities' && Array.isArray(value)) {
            // Handle amenities array
            value.forEach((amenity: string, index: number) => {
              formDataToSend.append(`amenities[${index}]`, amenity);
            });
          } else if (typeof value === 'boolean') {
            // Handle boolean values
            formDataToSend.append(key, value ? '1' : '0');
          } else {
            // Handle all other values
            formDataToSend.append(key, String(value));
          }
        }
      });

      // Add images to FormData
      if (selectedImages[0]) {
        formDataToSend.append('main_image', selectedImages[0]);
      }

      // Add gallery images (excluding the first one which is main)
      for (let i = 1; i < selectedImages.length; i++) {
        formDataToSend.append('images[]', selectedImages[i]);
      }

      console.log('Prepared property data for submission');

      try {
        console.log('Calling addProperty API...');
        const result = await addProperty(formDataToSend);
        console.log('Property added successfully:', result);

        // Clear localStorage after successful submission
        clearFormDataFromStorage();
        
        toast.success('تمت إضافة العقار بنجاح!');
        console.log('Navigating to dashboard...');
        navigate('/dashboard');
      } catch (apiError) {
        console.error('Error in addProperty API call:', apiError);
        throw apiError; // Re-throw to be caught by the outer catch
      }
    } catch (error: any) {
      console.error('Error adding property:', error);

      // Log the full error for debugging
      console.error('Full error object:', JSON.stringify(error, null, 2));

      // Extract error message from different possible locations in the error object
      const errorMessage = error?.response?.data?.message ||
        error?.response?.data?.error ||
        error?.message ||
        'فشل في إضافة العقار. يرجى المحاولة مرة أخرى.';

      // Show detailed error in console and toast
      console.error('Error details:', errorMessage);
      toast.error(errorMessage, {
        duration: 5000,
        position: 'top-center',
        style: { direction: 'rtl' }
      });
    }
  };

  if (!user) {
    return null;
  }

  const renderStep = () => {
    switch (currentStep) {
      case 1:
        return (
          <div className="space-y-8">
            {/* Property Title Section */}
            <div className="bg-gradient-to-r from-[#067977]/10 to-[#067977]/20 rounded-lg p-4 border border-[#067977]/30">
              <h3 className="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2 font-['Cairo',_'Tajawal',_sans-serif]">
                <Home className="h-4 w-4 text-[#067977]" />
                {t('addProperty.sectionTitles.propertyTitle')}
              </h3>
              <div>
                <Label htmlFor="title" className="text-sm font-medium text-gray-700 mb-2 block">
                  {t('forms.propertyTitle')} <span className="text-red-500">*</span>
                </Label>
                <InputWithIcon
                  id="title"
                  icon={FileText}
                  placeholder={t('forms.propertyTitlePlaceholder')}
                  className={`h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-[#067977]/20 hover:border-[#067977]/50 ${errors.title ? 'border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-[#067977]'
                    }`}
                  {...register('title')}
                />
                {errors.title && (
                  <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                    <X className="h-4 w-4" />
                    {errors.title.message}
                  </p>
                )}
              </div>
            </div>

            {/* Address Section */}
            <div className="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4 border border-green-100">
              <h3 className="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2 font-['Cairo',_'Tajawal',_sans-serif]">
                <MapPin className="h-4 w-4 text-green-600" />
                {t('addProperty.sectionTitles.addressLocation')}
              </h3>
              <div className="space-y-4">
                <div>
                  <Label htmlFor="address" className="text-sm font-medium text-gray-700 mb-2 block">
                    {t('forms.address')} <span className="text-red-500">*</span>
                  </Label>
                  <InputWithIcon
                    id="address"
                    icon={MapPin}
                    placeholder={t('forms.addressPlaceholder')}
                    className={`h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-100 hover:border-green-300 ${errors.address ? 'border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-green-500'
                      }`}
                    {...register('address')}
                  />
                  {errors.address && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <X className="h-4 w-4" />
                      {errors.address.message}
                    </p>
                  )}
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                  <div className="lg:col-span-2">
                    <LocationSelector
                      onLocationChange={handleLocationChange}
                      initialCity={selectedCity}
                      initialState={selectedState}
                    />
                  </div>

                  <div>
                    <Label htmlFor="postalCode" className="text-sm font-medium text-gray-700 mb-2 block">
                      {t('forms.postalCode')} <span className="text-red-500">*</span>
                    </Label>
                    <Input
                      id="postalCode"
                      placeholder={t('forms.postalCodePlaceholder')}
                      className={`h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-green-100 hover:border-green-300 ${errors.postalCode ? 'border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-green-500'
                        }`}
                      {...register('postalCode')}
                    />
                    {errors.postalCode && (
                      <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                        <X className="h-4 w-4" />
                        {errors.postalCode.message}
                      </p>
                    )}
                  </div>
                </div>
              </div>
            </div>

            {/* Map Section */}
            <div className="bg-gradient-to-r from-orange-50 to-amber-50 rounded-lg p-4 border border-orange-100">
              <h3 className="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2 font-['Cairo',_'Tajawal',_sans-serif]">
                <MapPin className="h-4 w-4 text-orange-600" />
                {t('map.selectPropertyLocation')}
              </h3>
              <PropertyLocationMap
                onLocationChange={handleCoordinatesChange}
                initialCoordinates={coordinates}
              />
              {coordinates && coordinates.lat !== undefined && coordinates.lng !== undefined && (
                <div className="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                  <p className="text-sm text-green-700 flex items-center gap-2">
                    <CheckCircle className="h-4 w-4" />
                    {t('map.locationSelected')}: {coordinates.lat.toFixed(6)}, {coordinates.lng.toFixed(6)}
                  </p>
                </div>
              )}
            </div>

            {/* Property Type Section */}
            <div className="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-100">
              <h3 className="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2 font-['Cairo',_'Tajawal',_sans-serif]">
                <Building className="h-4 w-4 text-purple-600" />
                {t('addProperty.sectionTitles.propertyTypeAd')}
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label className="text-sm font-medium text-gray-700 mb-3 block">
                    {t('filters.listingType')} <span className="text-red-500">*</span>
                  </Label>
                  <Controller
                    name="listingType"
                    control={control}
                    render={({ field }) => (
                      <Select onValueChange={field.onChange} defaultValue={field.value}>
                        <SelectTrigger className="h-8 text-sm border rounded-lg hover:border-purple-300 focus:ring-2 focus:ring-purple-100">
                          <div className="flex items-center gap-1">
                            <DollarSign className="h-3 w-3 text-purple-600" />
                            <SelectValue placeholder={t('addProperty.placeholders.selectAdType')} />
                          </div>
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="rent" className="text-sm py-2">
                            <div className="flex items-center gap-2">
                              <div className="w-1.5 h-1.5 bg-[#067977] rounded-full"></div>
                              {t('property.listingTypes.forRent')}
                            </div>
                          </SelectItem>
                          <SelectItem value="sale" className="text-sm py-2">
                            <div className="flex items-center gap-2">
                              <div className="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                              {t('property.listingTypes.forSale')}
                            </div>
                          </SelectItem>
                        </SelectContent>
                      </Select>
                    )}
                  />
                </div>

                <div>
                  <Label className="text-sm font-medium text-gray-700 mb-3 block">
                    {t('filters.propertyType')} <span className="text-red-500">*</span>
                  </Label>
                  <Controller
                    name="propertyType"
                    control={control}
                    render={({ field }) => (
                      <Select onValueChange={field.onChange} defaultValue={field.value}>
                        <SelectTrigger className="h-8 text-sm border rounded-lg hover:border-purple-300 focus:ring-2 focus:ring-purple-100">
                          <div className="flex items-center gap-1">
                            <Home className="h-3 w-3 text-purple-600" />
                            <SelectValue placeholder={t('addProperty.placeholders.selectPropertyType')} />
                          </div>
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="apartment" className="text-sm py-2">
                            <div className="flex items-center gap-2">
                              <Building className="h-3 w-3 text-[#067977]" />
                              {t('property.types.apartment')}
                            </div>
                          </SelectItem>
                          <SelectItem value="house" className="text-sm py-2">
                            <div className="flex items-center gap-2">
                              <Home className="h-3 w-3 text-green-600" />
                              {t('property.types.house')}
                            </div>
                          </SelectItem>
                          <SelectItem value="villa" className="text-sm py-2">
                            <div className="flex items-center gap-2">
                              <Star className="h-3 w-3 text-yellow-600" />
                              {t('property.types.villa')}
                            </div>
                          </SelectItem>
                          <SelectItem value="condo" className="text-sm py-2">
                            <div className="flex items-center gap-2">
                              <Building className="h-3 w-3 text-purple-600" />
                              {t('property.types.condo')}
                            </div>
                          </SelectItem>
                          <SelectItem value="townhouse" className="text-sm py-2">
                            <div className="flex items-center gap-2">
                              <Home className="h-3 w-3 text-indigo-600" />
                              {t('property.types.townhouse')}
                            </div>
                          </SelectItem>
                          <SelectItem value="studio" className="text-sm py-2">
                            <div className="flex items-center gap-2">
                              <Square className="h-3 w-3 text-orange-600" />
                              {t('property.types.studio')}
                            </div>
                          </SelectItem>
                          <SelectItem value="loft" className="text-sm py-2">
                            <div className="flex items-center gap-2">
                              <Building className="h-3 w-3 text-red-600" />
                              {t('property.types.loft')}
                            </div>
                          </SelectItem>
                          <SelectItem value="commercial" className="text-sm py-2">
                            <div className="flex items-center gap-2">
                              <Building className="h-3 w-3 text-gray-600" />
                              {t('property.types.commercial')}
                            </div>
                          </SelectItem>
                          <SelectItem value="land" className="text-sm py-2">
                            <div className="flex items-center gap-2">
                              <Square className="h-3 w-3 text-brown-600" />
                              {t('property.types.land')}
                            </div>
                          </SelectItem>
                        </SelectContent>
                      </Select>
                    )}
                  />
                </div>
              </div>

              {/* Enhanced Document Type Section */}
              <div className="mt-4">
                <Label className="text-xs font-medium text-gray-700 mb-2 block">
                  {t('property.documentType')}
                </Label>
                <Controller
                  name="documentTypeId"
                  control={control}
                  render={({ field }) => (
                    <EnhancedDocumentTypeSelect
                      value={field.value}
                      onValueChange={field.onChange}
                      placeholder="اختر نوع الطابو"
                      loading={loadingDocumentTypes}
                      documentTypes={documentTypes}
                      showDescriptions={false}
                      className="w-full h-8 text-sm"
                    />
                  )}
                />
              </div>
            </div>
          </div>
        );

      case 2:
        return (
          <div className="space-y-8">
            {/* Price Section */}
            <div className="bg-gradient-to-r from-emerald-50 to-green-50 rounded-lg p-4 border border-emerald-100">
              <h3 className="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2 font-['Cairo',_'Tajawal',_sans-serif]">
                <DollarSign className="h-4 w-4 text-emerald-600" />
                {t('addProperty.sectionTitles.priceAndCost')}
              </h3>
              <div>
                <Label htmlFor="price" className="text-sm font-medium text-gray-700 mb-2 block">
                  {t('forms.price')} <span className="text-red-500">*</span>
                </Label>
                <InputWithIcon
                  id="price"
                  type="number"
                  icon={DollarSign}
                  placeholder={watch('listingType') === 'rent' ? t('forms.monthlyRent') : t('forms.salePrice')}
                  className={`h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-emerald-100 hover:border-emerald-300 ${errors.price ? 'border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-emerald-500'
                    }`}
                  {...register('price', { valueAsNumber: true })}
                />
                {errors.price && (
                  <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                    <X className="h-4 w-4" />
                    {errors.price.message}
                  </p>
                )}
              </div>
            </div>

            {/* Property Specifications */}
            <div className="bg-gradient-to-r from-[#067977]/10 to-[#067977]/15 rounded-lg p-4 border border-[#067977]/30">
              <h3 className="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2 font-['Cairo',_'Tajawal',_sans-serif]">
                <Building className="h-4 w-4 text-[#067977]" />
                {t('addProperty.sectionTitles.propertySpecs')}
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <Label htmlFor="bedrooms" className="text-sm font-medium text-gray-700 mb-2 block">
                    {t('property.details.bedrooms')} <span className="text-red-500">*</span>
                  </Label>
                  <InputWithIcon
                    id="bedrooms"
                    type="number"
                    min="0"
                    icon={Bed}
                    placeholder={t('addProperty.placeholders.bedroomCount')}
                    className={`h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-[#067977]/20 hover:border-[#067977]/50 ${errors.bedrooms ? 'border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-[#067977]'
                      }`}
                    {...register('bedrooms', { valueAsNumber: true })}
                  />
                  {errors.bedrooms && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <X className="h-4 w-4" />
                      {errors.bedrooms.message}
                    </p>
                  )}
                </div>

                <div>
                  <Label htmlFor="bathrooms" className="text-sm font-medium text-gray-700 mb-2 block">
                    {t('property.details.bathrooms')} <span className="text-red-500">*</span>
                  </Label>
                  <InputWithIcon
                    id="bathrooms"
                    type="number"
                    min="0"
                    step="0.5"
                    icon={Bath}
                    placeholder={t('addProperty.placeholders.bathroomCount')}
                    className={`h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-[#067977]/20 hover:border-[#067977]/50 ${errors.bathrooms ? 'border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-[#067977]'
                      }`}
                    {...register('bathrooms', { valueAsNumber: true })}
                  />
                  {errors.bathrooms && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <X className="h-4 w-4" />
                      {errors.bathrooms.message}
                    </p>
                  )}
                </div>

                <div>
                  <Label htmlFor="squareFootage" className="text-sm font-medium text-gray-700 mb-2 block">
                    {t('property.details.squareFootage')} <span className="text-red-500">*</span>
                  </Label>
                  <InputWithIcon
                    id="squareFootage"
                    type="number"
                    min="1"
                    icon={Square}
                    placeholder={t('addProperty.placeholders.areaSquareMeters')}
                    className={`h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-[#067977]/20 hover:border-[#067977]/50 ${errors.squareFootage ? 'border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-[#067977]'
                      }`}
                    {...register('squareFootage', { valueAsNumber: true })}
                  />
                  {errors.squareFootage && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <X className="h-4 w-4" />
                      {errors.squareFootage.message}
                    </p>
                  )}
                </div>
              </div>
            </div>

            {/* Additional Details */}
            <div className="bg-gradient-to-r from-orange-50 to-amber-50 rounded-lg p-4 border border-orange-100">
              <h3 className="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2 font-['Cairo',_'Tajawal',_sans-serif]">
                <Calendar className="h-4 w-4 text-orange-600" />
                {t('addProperty.sectionTitles.additionalDetails')}
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label htmlFor="yearBuilt" className="text-sm font-medium text-gray-700 mb-2 block">
                    {t('property.details.yearBuilt')} <span className="text-red-500">*</span>
                  </Label>
                  <InputWithIcon
                    id="yearBuilt"
                    type="number"
                    min="1800"
                    max={new Date().getFullYear()}
                    icon={Calendar}
                    placeholder={t('addProperty.placeholders.yearBuilt')}
                    className={`h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-orange-100 hover:border-orange-300 ${errors.yearBuilt ? 'border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-orange-500'
                      }`}
                    {...register('yearBuilt', { valueAsNumber: true })}
                  />
                  {errors.yearBuilt && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <X className="h-4 w-4" />
                      {errors.yearBuilt.message}
                    </p>
                  )}
                </div>

                <div>
                  <Label htmlFor="availableDate" className="text-sm font-medium text-gray-700 mb-2 block">
                    {t('property.details.availableDate')}
                  </Label>
                  <Input
                    id="availableDate"
                    type="date"
                    className="h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-orange-100 hover:border-orange-300 border-gray-200 focus:border-orange-500"
                    {...register('availableDate')}
                  />
                </div>

                <div>
                  <Label htmlFor="lotSize" className="text-sm font-medium text-gray-700 mb-2 block">
                    {t('property.details.lotSize')}
                  </Label>
                  <Input
                    id="lotSize"
                    type="number"
                    min="1"
                    max="1000000"
                    placeholder={t('forms.lotSizePlaceholder')}
                    className={`h-12 text-lg border-2 rounded-xl transition-all duration-200 focus:ring-4 focus:ring-orange-100 hover:border-orange-300 ${errors.lotSize ? 'border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-orange-500'
                      }`}
                    {...register('lotSize', { valueAsNumber: true })}
                  />
                  {errors.lotSize && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <X className="h-4 w-4" />
                      {errors.lotSize.message}
                    </p>
                  )}
                </div>

                <div>
                  <Label htmlFor="petPolicy" className="text-sm font-medium text-gray-700 mb-2 block">
                    {t('property.details.petPolicy')}
                  </Label>
                  <Input
                    id="petPolicy"
                    placeholder={t('forms.petPolicyPlaceholder')}
                    className="h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-orange-100 hover:border-orange-300 border-gray-200 focus:border-orange-500"
                    {...register('petPolicy')}
                  />
                </div>
              </div>

              <div className="mt-4">
                <Label htmlFor="parking" className="text-xs font-medium text-gray-700 mb-2 block">
                  {t('property.details.parking')}
                </Label>
                <Controller
                  name="parking"
                  control={control}
                  render={({ field }) => (
                    <Select onValueChange={field.onChange} defaultValue={field.value || 'none'}>
                      <SelectTrigger className="h-8 text-sm border rounded-lg hover:border-orange-300 focus:ring-2 focus:ring-orange-100">
                        <div className="flex items-center gap-1">
                          <Car className="h-3 w-3 text-orange-600" />
                          <SelectValue placeholder={t('addProperty.placeholders.selectParkingType')} />
                        </div>
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="none" className="text-sm py-2">
                          <div className="flex items-center gap-2">
                            <div className="w-1.5 h-1.5 bg-red-500 rounded-full"></div>
                            {t('addProperty.parkingTypes.noParking')}
                          </div>
                        </SelectItem>
                        <SelectItem value="street" className="text-sm py-2">
                          <div className="flex items-center gap-2">
                            <div className="w-1.5 h-1.5 bg-yellow-500 rounded-full"></div>
                            {t('addProperty.parkingTypes.streetParking')}
                          </div>
                        </SelectItem>
                        <SelectItem value="garage" className="text-sm py-2">
                          <div className="flex items-center gap-2">
                            <div className="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                            {t('addProperty.parkingTypes.closedGarage')}
                          </div>
                        </SelectItem>
                        <SelectItem value="driveway" className="text-sm py-2">
                          <div className="flex items-center gap-2">
                            <div className="w-1.5 h-1.5 bg-[#067977] rounded-full"></div>
                            {t('addProperty.parkingTypes.privateDriveway')}
                          </div>
                        </SelectItem>
                        <SelectItem value="carport" className="text-sm py-2">
                          <div className="flex items-center gap-2">
                            <div className="w-1.5 h-1.5 bg-purple-500 rounded-full"></div>
                            {t('addProperty.parkingTypes.carShelter')}
                          </div>
                        </SelectItem>
                      </SelectContent>
                    </Select>
                  )}
                />
              </div>
            </div>
          </div>
        );

      case 3:
        return (
          <div className="space-y-6">
            {/* Image Upload Section */}
            <div className="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-100">
              <h3 className="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2 font-['Cairo',_'Tajawal',_sans-serif]">
                <Camera className="h-4 w-4 text-purple-600" />
                {t('addProperty.sectionTitles.propertyImages')}
              </h3>
              <p className="text-sm text-gray-600 mb-6 font-['Cairo',_'Tajawal',_sans-serif]">
                {t('addProperty.imageUpload.uploadHighQuality')}
              </p>

              <div className="border-2 border-dashed border-purple-300 rounded-lg p-6 text-center hover:border-purple-400 hover:bg-purple-50 transition-all duration-300 bg-white/50">
                <input
                  type="file"
                  multiple
                  accept="image/*"
                  onChange={handleImageUpload}
                  className="hidden"
                  id="image-upload"
                />
                <label htmlFor="image-upload" className="cursor-pointer block">
                  <div className="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3 hover:bg-purple-200 transition-colors">
                    <Upload className="h-8 w-8 text-purple-600" />
                  </div>
                  <p className="text-base font-semibold text-gray-900 mb-2 font-['Cairo',_'Tajawal',_sans-serif]">
                    {t('addProperty.imageUpload.clickToUpload')}
                  </p>
                  <p className="text-sm text-gray-500 font-['Cairo',_'Tajawal',_sans-serif]">
                    PNG, JPG, GIF حتى 10 ميجابايت لكل صورة (حد أقصى 10 صور)
                  </p>
                </label>
              </div>

              {selectedImages.length > 0 && (
                <div className="mt-6">
                  <div className="flex items-center justify-between mb-4">
                    <h4 className="text-base font-semibold text-gray-900 flex items-center gap-2 font-['Cairo',_'Tajawal',_sans-serif]">
                      <ImageIcon className="h-4 w-4 text-purple-600" />
                      {t('addProperty.imageUpload.selectedImages')} ({selectedImages.length}/10)
                    </h4>
                    <div className="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm font-medium">
                      {selectedImages.length} من 10
                    </div>
                  </div>
                  <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    {imagePreviewUrls.map((url, index) => (
                      <div key={index} className="relative group">
                        <div className="relative overflow-hidden rounded-xl border-2 border-gray-200 hover:border-purple-300 transition-all duration-200 shadow-sm hover:shadow-md">
                          <FixedImage
                            src={url}
                            alt={`Preview ${index + 1}`}
                            className="w-full h-32 object-cover"
                            showLoadingSpinner={true}
                          />
                          <div className="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-200"></div>
                          <button
                            type="button"
                            onClick={() => removeImage(index)}
                            className="absolute top-3 right-3 bg-red-500 hover:bg-red-600 text-white rounded-full p-2 opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-lg"
                          >
                            <X className="h-4 w-4" />
                          </button>
                          {index === 0 && (
                            <div className="absolute bottom-3 left-3 bg-gradient-to-r from-[#067977] to-[#067977]/80 text-white text-xs px-3 py-1.5 rounded-full font-medium shadow-lg flex items-center gap-1">
                              <Star className="h-3 w-3 fill-current" />
                              {t('addProperty.imageUpload.mainImage')}
                            </div>
                          )}
                          <div className="absolute top-3 left-3 bg-white/90 text-gray-700 text-xs px-2 py-1 rounded-full font-medium">
                            {index + 1}
                          </div>
                        </div>
                      </div>
                    ))}

                    {/* Add more images placeholder */}
                    {selectedImages.length < 10 && (
                      <div className="relative">
                        <label htmlFor="image-upload" className="cursor-pointer block">
                          <div className="w-full h-32 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center hover:border-purple-400 hover:bg-purple-50 transition-all duration-200">
                            <Plus className="h-6 w-6 text-gray-400 mb-2" />
                            <span className="text-xs text-gray-500 font-medium">{t('addProperty.imageUpload.addMore')}</span>
                          </div>
                        </label>
                      </div>
                    )}
                  </div>

                  {/* Image Upload Tips */}
                  <div className="mt-4 bg-[#067977]/10 border border-[#067977]/30 rounded-lg p-3">
                    <h5 className="font-semibold text-[#067977] mb-2 flex items-center gap-2 text-sm">
                      <Camera className="h-3 w-3" />
                      {t('addProperty.imageUpload.tips.title')}
                    </h5>
                    <ul className="text-[#067977]/80 text-xs space-y-1 font-['Cairo',_'Tajawal',_sans-serif]">
                      <li>• {t('addProperty.imageUpload.tips.naturalLight')}</li>
                      <li>• {t('addProperty.imageUpload.tips.differentAngles')}</li>
                      <li>• {t('addProperty.imageUpload.tips.showFeatures')}</li>
                      <li>• {t('addProperty.imageUpload.tips.ensureQuality')}</li>
                    </ul>
                  </div>
                </div>
              )}
            </div>
          </div>
        );

      case 4:
        return (
          <div className="space-y-4">
            <div>
              <Label htmlFor="description" className="text-sm">{t('forms.propertyDescription')} *</Label>
              <Textarea
                id="description"
                placeholder={t('forms.propertyDescriptionPlaceholder')}
                rows={4}
                className={`text-sm ${errors.description ? 'border-red-500' : ''}`}
                {...register('description')}
              />
              {errors.description && (
                <p className="text-sm text-red-600 mt-1">{errors.description.message}</p>
              )}
            </div>

            <div>
              <Label className="text-sm">{t('steps.features')}</Label>
              <p className="text-xs text-gray-600 mb-2">
                {t('forms.selectAllFeatures')}
              </p>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto border rounded-lg p-3">
                {availableFeatures.map((feature) => (
                  <div key={feature} className="flex items-center space-x-2">
                    <Checkbox
                      id={feature}
                      checked={selectedFeatures.includes(feature)}
                      onCheckedChange={() => handleFeatureToggle(feature)}
                    />
                    <Label htmlFor={feature} className="text-xs">
                      {getFeatureTranslation(feature)}
                    </Label>
                  </div>
                ))}
              </div>
              <p className="text-xs text-gray-500 mt-2">
                {t('forms.selectedFeatures')}: {selectedFeatures.length} {t('forms.features')}
              </p>
            </div>

            <div>
              <Label className="text-sm font-medium text-gray-700 mb-3 block">
                {t('property.details.utilities')}
              </Label>
              <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 mb-3">
                {availableUtilities.map((utility) => (
                  <div key={utility} className="flex items-center space-x-2 rtl:space-x-reverse">
                    <Checkbox
                      id={`utility-${utility}`}
                      checked={selectedUtilities.includes(utility)}
                      onCheckedChange={() => handleUtilityToggle(utility)}
                      className="data-[state=checked]:bg-[#067977] data-[state=checked]:border-[#067977]"
                    />
                    <Label
                      htmlFor={`utility-${utility}`}
                      className="text-xs font-medium text-gray-700 cursor-pointer hover:text-[#067977] transition-colors"
                    >
                      {utility}
                    </Label>
                  </div>
                ))}
              </div>
              <p className="text-xs text-gray-500 mt-2">
                {t('addProperty.selectedUtilities')}: {selectedUtilities.length}
              </p>
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
        );

      case 5:
        return (
          <div className="space-y-4">
            <div>
              <h3 className="text-base font-semibold mb-3">{t('steps.contactInformation')}</h3>
              <p className="text-gray-600 mb-4 text-sm">
                {t('forms.contactInfoDescription')}
              </p>
            </div>

            <div>
              <Label htmlFor="contactName" className="text-sm">{t('forms.contactName')} *</Label>
              <InputWithIcon
                id="contactName"
                icon={User}
                placeholder={t('forms.yourFullName')}
                className={`text-sm h-9 ${errors.contactName ? 'border-red-500' : ''}`}
                {...register('contactName')}
              />
              {errors.contactName && (
                <p className="text-sm text-red-600 mt-1">{errors.contactName.message}</p>
              )}
            </div>

            <div>
              <Label htmlFor="contactPhone" className="text-sm">{t('forms.phoneNumber')} *</Label>
              <InputWithIcon
                id="contactPhone"
                icon={Phone}
                placeholder={t('forms.phoneNumberPlaceholder')}
                className={`text-sm h-9 ${errors.contactPhone ? 'border-red-500' : ''}`}
                {...register('contactPhone')}
              />
              {errors.contactPhone && (
                <p className="text-sm text-red-600 mt-1">{errors.contactPhone.message}</p>
              )}
            </div>

            <div>
              <Label htmlFor="contactEmail" className="text-sm">{t('forms.emailAddress')} *</Label>
              <InputWithIcon
                id="contactEmail"
                type="email"
                icon={Mail}
                placeholder={t('forms.emailAddressPlaceholder')}
                className={`text-sm h-9 ${errors.contactEmail ? 'border-red-500' : ''}`}
                {...register('contactEmail')}
              />
              {errors.contactEmail && (
                <p className="text-sm text-red-600 mt-1">{errors.contactEmail.message}</p>
              )}
            </div>

            <div className="bg-[#067977]/10 p-3 rounded-lg">
              <h4 className="font-semibold text-[#067977] mb-2 text-sm">{t('forms.note')}</h4>
              <p className="text-[#067977] text-xs">
                Property listing will be created with the uploaded images.
              </p>
            </div>
          </div>
        );

      default:
        return null;
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
      {/* Background Pattern */}
      <div className="absolute inset-0 opacity-30">
        <div className="absolute inset-0 bg-gradient-to-br from-blue-50/50 to-indigo-50/50"></div>
        <div className="absolute inset-0" style={{
          backgroundImage: `radial-gradient(circle at 1px 1px, rgba(59, 130, 246, 0.15) 1px, transparent 0)`,
          backgroundSize: '20px 20px'
        }}></div>
      </div>

      <div className="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {/* Enhanced Header */}
        <div className="flex items-center justify-between mb-6">
          <Button
            variant="ghost"
            onClick={() => navigate('/dashboard')}
            className="flex items-center gap-2 text-gray-600 hover:text-gray-900 hover:bg-white/60 transition-all duration-200 rounded-lg px-3 py-2 text-sm"
          >
            {isRTL ? <ArrowRight className="h-4 w-4" /> : <ArrowLeft className="h-4 w-4" />}
            <span className="font-medium">{t('dashboard.myProperties')}</span>
          </Button>
          <div className="flex items-center gap-2">
            <div className="p-2 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg">
              <Home className="h-5 w-5 text-white" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900 font-['Cairo',_'Tajawal',_sans-serif]">
                {t('navigation.listProperty')}
              </h1>
              <p className="text-xs text-gray-600 mt-1">{t('addProperty.progress.addYourNewProperty')}</p>
            </div>
          </div>
        </div>

        {/* Enhanced Progress Bar with Step Indicators */}
        <div className="mb-6">
          <div className="flex items-center justify-between mb-3">
            <div className="flex items-center gap-2">
              <span className="text-base font-semibold text-gray-800 font-['Cairo',_'Tajawal',_sans-serif]">
                {t('forms.step')} {currentStep} {t('forms.of')} {totalSteps}
              </span>
              <div className="px-2 py-1 bg-[#067977]/20 text-[#067977] rounded-full text-xs font-medium">
                {Math.round((currentStep / totalSteps) * 100)}% {t('forms.complete')}
              </div>
            </div>
          </div>

          {/* Step Indicators */}
          <div className="flex items-center justify-between mb-3">
            {[1, 2, 3, 4, 5].map((step) => (
              <div key={step} className="flex flex-col items-center">
                <div className={`w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 ${
                  step <= currentStep
                    ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg'
                    : 'bg-gray-200 text-gray-500'
                  }`}>
                  {step <= currentStep ? (
                    step < currentStep ? '✓' : step
                  ) : step}
                </div>
                <span className={`text-xs mt-1 font-medium ${
                  step <= currentStep ? 'text-[#067977]' : 'text-gray-400'
                  }`}>
                  {step === 1 && t('addProperty.progress.basic')}
                  {step === 2 && t('addProperty.progress.details')}
                  {step === 3 && t('addProperty.progress.images')}
                  {step === 4 && t('addProperty.progress.features')}
                  {step === 5 && t('addProperty.progress.contact')}
                </span>
              </div>
            ))}
          </div>

          {/* Progress Bar */}
          <div className="w-full bg-gray-200 rounded-full h-2 shadow-inner">
            <div
              className="bg-gradient-to-r from-[#067977] to-[#067977]/80 h-2 rounded-full transition-all duration-500 ease-out shadow-sm relative overflow-hidden"
              style={{ width: `${(currentStep / totalSteps) * 100}%` }}
            >
              <div className="absolute inset-0 bg-white/20 animate-pulse"></div>
            </div>
          </div>
        </div>

        {/* Enhanced Form */}
        <form onSubmit={handleSubmit(onSubmit)}>
          <Card className="shadow-xl border-0 bg-white/80 backdrop-blur-sm rounded-xl overflow-hidden">
            <CardHeader className="bg-gradient-to-r from-[#067977]/10 to-[#067977]/15 border-b border-[#067977]/20 pb-4">
              <div className="flex items-center gap-3">
                <div className="p-2 bg-gradient-to-r from-[#067977] to-[#067977]/80 rounded-lg">
                  {currentStep === 1 && <FileText className="h-4 w-4 text-white" />}
                  {currentStep === 2 && <Building className="h-4 w-4 text-white" />}
                  {currentStep === 3 && <Camera className="h-4 w-4 text-white" />}
                  {currentStep === 4 && <Star className="h-4 w-4 text-white" />}
                  {currentStep === 5 && <User className="h-4 w-4 text-white" />}
                </div>
                <div>
                  <CardTitle className="text-xl font-bold text-gray-900 font-['Cairo',_'Tajawal',_sans-serif]">
                    {currentStep === 1 && (
                      <>
                        <span className="text-[#067977]">{t('addProperty.stepTitles.basicInfo')}</span>
                        <p className="text-xs font-normal text-gray-600 mt-1">{t('addProperty.stepDescriptions.basicInfo')}</p>
                      </>
                    )}
                    {currentStep === 2 && (
                      <>
                        <span className="text-[#067977]">{t('addProperty.stepTitles.propertySpecs')}</span>
                        <p className="text-xs font-normal text-gray-600 mt-1">{t('addProperty.stepDescriptions.propertySpecs')}</p>
                      </>
                    )}
                    {currentStep === 3 && (
                      <>
                        <span className="text-[#067977]">{t('addProperty.stepTitles.images')}</span>
                        <p className="text-xs font-normal text-gray-600 mt-1">{t('addProperty.stepDescriptions.images')}</p>
                      </>
                    )}
                    {currentStep === 4 && (
                      <>
                        <span className="text-[#067977]">{t('addProperty.stepTitles.features')}</span>
                        <p className="text-xs font-normal text-gray-600 mt-1">{t('addProperty.stepDescriptions.features')}</p>
                      </>
                    )}
                    {currentStep === 5 && (
                      <>
                        <span className="text-[#067977]">{t('addProperty.stepTitles.contact')}</span>
                        <p className="text-xs font-normal text-gray-600 mt-1">{t('addProperty.stepDescriptions.contact')}</p>
                      </>
                    )}
                  </CardTitle>
                </div>
              </div>
            </CardHeader>
            <CardContent className="p-6">{renderStep()}</CardContent>
          </Card>

          {/* Enhanced Navigation Buttons */}
          <div className="flex justify-between items-center mt-6">
            <Button
              type="button"
              variant="outline"
              onClick={prevStep}
              disabled={currentStep === 1}
              className="px-6 py-2 rounded-lg border-2 border-gray-300 hover:border-gray-400 hover:bg-gray-50 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed font-medium flex items-center gap-2 text-sm"
            >
              {isRTL ? <ArrowLeft className="h-4 w-4" /> : <ArrowRight className="h-4 w-4" />}
              {t('buttons.previous')}
            </Button>

            <div className="flex gap-3">
              {currentStep < totalSteps ? (
                <Button
                  type="button"
                  onClick={nextStep}
                  className="px-8 py-2 bg-gradient-to-r from-[#067977] to-[#067977]/80 hover:from-[#067977]/90 hover:to-[#067977] text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 flex items-center gap-2 text-sm"
                >
                  {t('buttons.next')}
                  {isRTL ? <ArrowRight className="h-4 w-4" /> : <ArrowLeft className="h-4 w-4" />}
                </Button>
              ) : (
                <Button
                  type="submit"
                  disabled={isSubmitting}
                  onClick={() => {
                    console.log('Create Listing button clicked');
                    console.log('Form isSubmitting:', isSubmitting);
                    console.log('Form errors:', errors);
                    console.log('Current step:', currentStep);
                  }}
                  className="px-8 py-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 text-sm"
                >
                  {isSubmitting ? (
                    <>
                      <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                      {t('buttons.creatingListing')}
                    </>
                  ) : (
                    <>
                      <CheckCircle className="h-4 w-4" />
                      {t('buttons.createListing')}
                    </>
                  )}
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
