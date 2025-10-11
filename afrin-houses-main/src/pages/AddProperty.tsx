import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useApp } from '../context/AppContext';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Property, SearchFilters } from '../types';
import { useTranslation } from 'react-i18next';
import { useAuthCheck } from '../hooks/useAuthCheck';
import AuthModal from '../components/AuthModal';
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
  File,
  Eye,
  Settings
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
import { notification, notificationMessages } from '../services/notificationService';
import FixedImage from '../components/FixedImage';
import LocationSelector from '../components/LocationSelector';
import PropertyLocationMap from '../components/PropertyLocationMap';
import EnhancedDocumentTypeSelect from '../components/EnhancedDocumentTypeSelect';
import { propertyDocumentTypeService, PropertyDocumentType } from '../services/propertyDocumentTypeService';
import { priceTypeService, PriceType } from '../services/priceTypeService';
import { propertyTypeService, PropertyType } from '../services/propertyTypeService';
import { buildingTypeService, BuildingType } from '../services/buildingTypeService';
import { windowTypeService, WindowType } from '../services/windowTypeService';
import { floorTypeService, FloorType } from '../services/floorTypeService';
import { viewTypeService, ViewType } from '../services/viewTypeService';
import { directionService, Direction } from '../services/directionService';
import { currencyService, CurrencyOption } from '../services/currencyService';
import { compressImages, formatFileSize } from '../lib/imageUtils';
import api from '../services/api';
import { authService } from '../services/authService';
import config from '../utils/config';

// Types for features and utilities
interface Feature {
  id: number;
  name_ar: string;
  name_en: string;
  name_ku: string;
  description_ar?: string;
  description_en?: string;
  description_ku?: string;
  category?: string;
  category_label?: string;
  icon?: string;
  slug: string;
  sort_order?: number;
  is_active: boolean;
}

interface Utility {
  id: number;
  name_ar: string;
  name_en: string;
  name_ku: string;
  description_ar?: string;
  description_en?: string;
  description_ku?: string;
  category?: string;
  category_label?: string;
  icon?: string;
  slug: string;
  sort_order?: number;
  is_active: boolean;
}

// Create schema factory function to use translation
const createPropertySchema = (t: any) => z.object({
  title: z.string().min(1, t('validation.propertyTitleRequired')).max(255, t('validation.titleMaxLength')),
  address: z.string().min(1, t('validation.addressRequired')).max(500, t('validation.addressMaxLength')),
  city: z.string().min(1, t('validation.cityRequired')).max(100, t('validation.cityMaxLength')),
  state: z.string().min(1, t('validation.stateRequired')).max(100, t('validation.stateMaxLength')),
  price: z.number().min(1, t('validation.priceRequired')).max(99999999.99, t('validation.priceTooHigh')),
  currency: z.string().min(1, 'Currency is required').default('TRY'),
  priceType: z.string().min(1, t('validation.priceTypeRequired')),
  listingType: z.enum(['rent', 'sale']),
  propertyType: z.string().min(1, t('validation.propertyTypeRequired')),
  documentTypeId: z.string().min(1, t('validation.documentTypeRequired')),
  bedrooms: z.number().min(1, t('validation.bedroomsRequired')).max(20, t('validation.bedroomsMaxLimit')),
  bathrooms: z.number().min(1, t('validation.bathroomsRequired')).max(20, t('validation.bathroomsMaxLimit')),
  squareFootage: z.number().min(1, t('validation.squareFootageRequired')).max(50000, t('validation.squareFootageMaxLimit')),
  description: z.string().min(10, t('validation.descriptionMinLength')).max(5000, t('validation.descriptionMaxLength')),
  yearBuilt: z.number().min(1800, t('validation.yearBuiltRequired')).max(new Date().getFullYear() + 2, t('validation.yearBuiltFuture')),
  availableDate: z.string().optional(),
  petPolicy: z.string().optional(),
  parking: z.string().min(1, t('validation.parkingRequired')),
  utilities: z.string().optional(),
  lotSize: z.union([
    z.string()
      .min(1, t('validation.lotSizeRequired'))
      .refine(val => /^\d+$/.test(val), {
        message: t('validation.lotSizePositiveInteger')
      })
      .transform((val) => parseInt(val, 10))
      .refine(val => val > 0, {
        message: t('validation.lotSizeGreaterThanZero')
      })
      .refine(val => val <= 1000000, {
        message: t('validation.lotSizeTooLarge')
      }),
    z.number()
      .int(t('validation.lotSizeInteger'))
      .positive(t('validation.lotSizeGreaterThanZero'))
      .max(1000000, t('validation.lotSizeTooLarge')),
    z.literal('').transform(() => undefined)
  ]).optional(),
  garage: z.string().optional(),
  heating: z.string().optional(),
  hoaFees: z.string().optional(),
  building: z.string().optional(),
  pool: z.string().optional(),
  
  // Phase 1 Enhancement Fields
  floorNumber: z.number().min(0, t('validation.floorNumberMin')).max(200, t('validation.floorNumberMax')).optional(),
  totalFloors: z.number().min(1, t('validation.totalFloorsMin')).max(200, t('validation.totalFloorsMax')).optional(),
  balconyCount: z.number().min(0, t('validation.balconyCountMin')).max(20, t('validation.balconyCountMax')).optional(),
  orientation: z.string().optional(),
  viewType: z.string().optional(),
  
  // Phase 2 Advanced Enhancement Fields
  buildingAge: z.number().min(0, t('validation.buildingAgeMin')).max(200, t('validation.buildingAgeMax')).optional(),
  buildingType: z.string().optional(),
  buildingTypeId: z.number().optional(),
  floorType: z.string().optional(),
  floorTypeId: z.number().optional(),
  windowType: z.string().optional(),
  windowTypeId: z.number().optional(),
  maintenanceFee: z.number().min(0, t('validation.maintenanceFeeMin')).max(99999, t('validation.maintenanceFeeMax')).optional(),
  depositAmount: z.number().min(0, t('validation.depositAmountMin')).max(9999999, t('validation.depositAmountMax')).optional(),
  annualTax: z.number().min(0, t('validation.annualTaxMin')).max(999999, t('validation.annualTaxMax')).optional(),
  
  contactName: z.string().min(1, t('validation.contactNameRequired')),
  contactPhone: z.string().optional().or(z.literal('')),
  contactEmail: z.string().email(t('validation.validEmailRequired')),
  latitude: z.number().optional(),
  longitude: z.number().optional(),
});

const AddProperty: React.FC = () => {
  const { state, addProperty } = useApp();
  const { user, language, loading } = state;
  const navigate = useNavigate();
  const { t, i18n } = useTranslation();
  const isRTL = i18n.language === 'ar';
  const { isAuthenticated, showAuthModal, openAuthModal, closeAuthModal, requireAuth } = useAuthCheck();
  
  // Create the schema with translation function
  const propertySchema = React.useMemo(() => createPropertySchema(t), [t]);
  type PropertyFormData = z.infer<typeof propertySchema>;
  const [selectedFeatures, setSelectedFeatures] = useState<number[]>(() => {
    // Restore selected features from localStorage
    const savedFeatures = localStorage.getItem('addProperty_selectedFeatures');
    return savedFeatures ? JSON.parse(savedFeatures) : [];
  });
  const [selectedUtilities, setSelectedUtilities] = useState<number[]>(() => {
    // Restore selected utilities from localStorage
    const savedUtilities = localStorage.getItem('addProperty_selectedUtilities');
    return savedUtilities ? JSON.parse(savedUtilities) : [];
  });
  const [selectedImages, setSelectedImages] = useState<File[]>([]);
  const [imagePreviewUrls, setImagePreviewUrls] = useState<string[]>(() => {
    // Restore image preview URLs from localStorage
    const savedPreviewUrls = localStorage.getItem('addProperty_imagePreviewUrls');
    const urls = savedPreviewUrls ? JSON.parse(savedPreviewUrls) : [];

    return urls;
  });


  const [mainImageIndex, setMainImageIndex] = useState<number>(() => {
    // Restore main image index from localStorage
    const savedMainImageIndex = localStorage.getItem('addProperty_mainImageIndex');
    return savedMainImageIndex ? parseInt(savedMainImageIndex, 10) : 0;
  });

  // Video upload state
  const [selectedVideos, setSelectedVideos] = useState<File[]>([]);
  const [videoPreviewUrls, setVideoPreviewUrls] = useState<string[]>([]);

  const [currentStep, setCurrentStep] = useState(() => {
    // Restore current step from localStorage on page refresh
    const savedStep = localStorage.getItem('addProperty_currentStep');
    return savedStep ? parseInt(savedStep, 10) : 1;
  });
  const totalSteps = 6;

  // Document types state
  const [documentTypes, setDocumentTypes] = useState<PropertyDocumentType[]>([]);
  const [loadingDocumentTypes, setLoadingDocumentTypes] = useState(false);

  // Property types state
  const [propertyTypes, setPropertyTypes] = useState<PropertyType[]>([]);
  const [loadingPropertyTypes, setLoadingPropertyTypes] = useState(false);

  // Building types state
  const [buildingTypes, setBuildingTypes] = useState<BuildingType[]>([]);
  const [loadingBuildingTypes, setLoadingBuildingTypes] = useState(false);

  // Window types state
  const [windowTypes, setWindowTypes] = useState<WindowType[]>([]);
  const [loadingWindowTypes, setLoadingWindowTypes] = useState(false);

  // Floor types state
  const [floorTypes, setFloorTypes] = useState<FloorType[]>([]);
  const [loadingFloorTypes, setLoadingFloorTypes] = useState(false);

  // View types state
  const [viewTypes, setViewTypes] = useState<ViewType[]>([]);
  const [loadingViewTypes, setLoadingViewTypes] = useState(false);

  // Directions state
  const [directions, setDirections] = useState<Direction[]>([]);
  const [loadingDirections, setLoadingDirections] = useState(false);

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
            contactPhone: (parsedData.contactPhone && parsedData.contactPhone.trim() !== '') ? parsedData.contactPhone : (user?.phone || ''),
          };
        } catch (error) {

        }
      }
      
      // Default values if no saved data
      return {
        title: '',
        address: '',
        city: '',
        state: '',
        listingType: 'rent',
        propertyType: '',
        priceType: undefined, // Use undefined for proper Select component behavior
        documentTypeId: '', // Required field
        bedrooms: 1,
        bathrooms: 1,
        squareFootage: 500,
        yearBuilt: new Date().getFullYear(),
        price: 1, // Changed from 0 to 1 since price must be greater than 0
        currency: 'TRY', // Default currency
        description: '',
        parking: 'none',
        lotSize: 1000, // Default lot size value since it's now mandatory
        availableDate: new Date().toISOString().split('T')[0], // Today's date in YYYY-MM-DD format
        contactName: user?.name || '',
        contactEmail: user?.email || '',
        contactPhone: user?.phone || '',
        latitude: undefined,
        longitude: undefined,
        // Phase 1 new fields
        floorNumber: undefined,
        totalFloors: undefined,
        balconyCount: undefined,
        orientation: undefined,
        viewType: undefined,
        // Phase 2 advanced fields
        buildingAge: undefined,
        buildingType: undefined,
        buildingTypeId: undefined,
        floorType: undefined,
        floorTypeId: undefined,
        windowType: undefined,
        windowTypeId: undefined,
        maintenanceFee: undefined,
        depositAmount: undefined,
        annualTax: undefined,
      };
    })(),
  });

  React.useEffect(() => {
    // Only redirect if loading is complete and user is still null
    if (!loading && !user) {
      navigate('/auth');
    }
  }, [user, navigate, loading]);

  // Initialize contact fields when user data is available
  React.useEffect(() => {
    if (user && !loading) {
      const currentValues = getValues();
      // Only set values if they are currently empty
      if (!currentValues.contactName) {
        setValue('contactName', user.name || '');
      }
      if (!currentValues.contactEmail) {
        setValue('contactEmail', user.email || '');
      }
      if (!currentValues.contactPhone) {
        setValue('contactPhone', user.phone || '');
      }
    }
  }, [user, loading, setValue, getValues]);

  // Save form data to localStorage whenever form values change
  React.useEffect(() => {
    const subscription = watch((data) => {
      try {
        localStorage.setItem('addProperty_formData', JSON.stringify(data));
      } catch (error) {

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

  // Save main image index to localStorage
  React.useEffect(() => {
    localStorage.setItem('addProperty_mainImageIndex', mainImageIndex.toString());
  }, [mainImageIndex]);

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

  // Load property document types - only when user reaches step 1
  React.useEffect(() => {
    if (currentStep < 1) return;

    const loadDocumentTypes = async () => {
      setLoadingDocumentTypes(true);
      try {
        const types = await propertyDocumentTypeService.getPropertyDocumentTypes({
          lang: i18n.language
        });
        setDocumentTypes(types);
      } catch (error) {
        // Use fallback data if API fails
        const fallbackTypes = propertyDocumentTypeService.getFallbackDocumentTypes(i18n.language);
        setDocumentTypes(fallbackTypes);
      } finally {
        setLoadingDocumentTypes(false);
      }
    };

    loadDocumentTypes();
  }, [i18n.language, currentStep]);

  // Load property types - only when user reaches step 1
  React.useEffect(() => {
    if (currentStep < 1) return;

    const loadPropertyTypes = async () => {
      setLoadingPropertyTypes(true);
      try {
        const types = await propertyTypeService.getPropertyTypeOptions(true, true);
        setPropertyTypes(types);
      } catch (error) {
        // Use fallback empty array if API fails
        setPropertyTypes([]);
      } finally {
        setLoadingPropertyTypes(false);
      }
    };

    loadPropertyTypes();
  }, [i18n.language, currentStep]);

  // Load building types - only when user reaches advanced details (step 4)
  React.useEffect(() => {
    if (currentStep < 4) return;

    const loadBuildingTypes = async () => {
      setLoadingBuildingTypes(true);
      try {
        const types = await buildingTypeService.getBuildingTypeOptions();
        setBuildingTypes(types);
      } catch (error) {
        setBuildingTypes([]);
      } finally {
        setLoadingBuildingTypes(false);
      }
    };

    loadBuildingTypes();
  }, [i18n.language, currentStep]);

  // Load window types - only when user reaches advanced details (step 4)
  React.useEffect(() => {
    if (currentStep < 4) return;

    const loadWindowTypes = async () => {
      setLoadingWindowTypes(true);
      try {
        const types = await windowTypeService.getWindowTypeOptions();
        setWindowTypes(types);
      } catch (error) {
        setWindowTypes([]);
      } finally {
        setLoadingWindowTypes(false);
      }
    };

    loadWindowTypes();
  }, [i18n.language, currentStep]);

  // Load floor types - only when user reaches advanced details (step 4)
  React.useEffect(() => {
    if (currentStep < 4) return;

    const loadFloorTypes = async () => {
      setLoadingFloorTypes(true);
      try {
        const types = await floorTypeService.getFloorTypeOptions();
        setFloorTypes(types);
      } catch (error) {
        setFloorTypes([]);
      } finally {
        setLoadingFloorTypes(false);
      }
    };

    loadFloorTypes();
  }, [i18n.language, currentStep]);

  // Load view types - only when user reaches advanced details (step 4)
  React.useEffect(() => {
    if (currentStep < 4) return;

    const loadViewTypes = async () => {
      setLoadingViewTypes(true);
      try {
        const types = await viewTypeService.getViewTypeOptions();
        setViewTypes(types);
      } catch (error) {
        setViewTypes([]);
      } finally {
        setLoadingViewTypes(false);
      }
    };

    loadViewTypes();
  }, [i18n.language, currentStep]);

  // Load directions - only when user reaches advanced details (step 4)
  React.useEffect(() => {
    if (currentStep < 4) return;

    const loadDirections = async () => {
      setLoadingDirections(true);
      try {
        const directions = await directionService.getDirectionOptions();
        setDirections(directions);
      } catch (error) {
        setDirections([]);
      } finally {
        setLoadingDirections(false);
      }
    };

    loadDirections();
  }, [i18n.language, currentStep]);

  // Load features, utilities, and currencies only when reaching step 2 (Property Details) or language changes
  useEffect(() => {
    if (currentStep >= 2) {
      fetchFeatures();
      fetchUtilities();
      fetchCurrencies();
    }
  }, [currentStep, i18n.language]);

  // Load price types only when in step 2 (Property Details) and listing type is selected
  const watchedListingType = watch('listingType');
  useEffect(() => {
    if (currentStep === 2 && watchedListingType) {
      fetchPriceTypes(watchedListingType as any);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [currentStep, watchedListingType]);
  

  // State for features and utilities from API
  const [availableFeatures, setAvailableFeatures] = useState<Feature[]>([]);
  const [availableUtilities, setAvailableUtilities] = useState<Utility[]>([]);
  const [loadingFeatures, setLoadingFeatures] = useState(false);
  const [loadingUtilities, setLoadingUtilities] = useState(false);
  const [featuresError, setFeaturesError] = useState<string | null>(null);
  const [utilitiesError, setUtilitiesError] = useState<string | null>(null);

  // State for price types from API
  const [availablePriceTypes, setAvailablePriceTypes] = useState<PriceType[]>([]);
  const [loadingPriceTypes, setLoadingPriceTypes] = useState(false);
  const [priceTypesError, setPriceTypesError] = useState<string | null>(null);

  // State for currencies from API
  const [availableCurrencies, setAvailableCurrencies] = useState<CurrencyOption[]>([]);
  const [loadingCurrencies, setLoadingCurrencies] = useState(false);

  // Fetch features from API
  const fetchFeatures = async () => {
    setLoadingFeatures(true);
    setFeaturesError(null);
    try {
      const response = await api.get('/features', {
        params: { 
          active: 1,
          lang: i18n.language 
        },
        headers: {
          'Accept-Language': i18n.language,
        },
      });
      
      setAvailableFeatures(response.data.data || []);
    } catch (error) {

      setFeaturesError('Failed to load features');
      notification.error('Failed to load features');
    } finally {
      setLoadingFeatures(false);
    }
  };

  // Fetch utilities from API
  const fetchUtilities = async () => {
    setLoadingUtilities(true);
    setUtilitiesError(null);
    try {
      const response = await api.get('/utilities', {
        params: { 
          active: 1,
          lang: i18n.language 
        },
        headers: {
          'Accept-Language': i18n.language,
        },
      });
      
      setAvailableUtilities(response.data.data || []);
    } catch (error) {

      setUtilitiesError('Failed to load utilities');
      notification.error('Failed to load utilities');
    } finally {
      setLoadingUtilities(false);
    }
  };

  // Fetch price types from API
  const fetchPriceTypes = async (listingType?: 'rent' | 'sale') => {
    setLoadingPriceTypes(true);
    setPriceTypesError(null);
    try {
      const priceTypes = await priceTypeService.getPriceTypes(listingType);
      setAvailablePriceTypes(priceTypes);
      
      // Set default price type based on listing type
      if (priceTypes.length > 0) {
        let defaultPriceType = priceTypes[0]; // fallback to first option
        
        if (listingType === 'rent') {
          // For rent, prefer 'monthly' price type
          const monthlyType = priceTypes.find(pt => pt.key === 'monthly');
          if (monthlyType) {
            defaultPriceType = monthlyType;
          }
        } else if (listingType === 'sale') {
          // For sale, prefer 'total' price type
          const totalType = priceTypes.find(pt => pt.key === 'total');
          if (totalType) {
            defaultPriceType = totalType;
          }
        }
        
        // Set the default value in the form
        setValue('priceType', defaultPriceType.key);
      }
    } catch (error) {

      setPriceTypesError('Failed to load price types');
      notification.error('Failed to load price types');
    } finally {
      setLoadingPriceTypes(false);
    }
  };

  // Fetch currencies from API
  const fetchCurrencies = async () => {
    setLoadingCurrencies(true);
    try {
      const currencies = await currencyService.getCurrencyOptions();
      setAvailableCurrencies(currencies);
    } catch (error) {
      console.error('Error fetching currencies:', error);
      notification.error('Failed to load currencies');
    } finally {
      setLoadingCurrencies(false);
    }
  };

  const handleFeatureToggle = (featureId: number) => {
    setSelectedFeatures(prev => {
      const newFeatures = prev.includes(featureId)
        ? prev.filter(f => f !== featureId)
        : [...prev, featureId];
      // Save to localStorage
      localStorage.setItem('addProperty_selectedFeatures', JSON.stringify(newFeatures));
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

  const handleUtilityToggle = (utilityId: number) => {
    setSelectedUtilities(prev => {
      const newUtilities = prev.includes(utilityId)
        ? prev.filter(u => u !== utilityId)
        : [...prev, utilityId];
      // Save to localStorage
      localStorage.setItem('addProperty_selectedUtilities', JSON.stringify(newUtilities));
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



  const nextStep = async () => {
    // Prevent moving beyond the last step
    if (currentStep >= totalSteps) {
      return;
    }
    
    const fieldsToValidate = getFieldsForStep(currentStep);
    
    // Get current form values
    const currentValues = getValues();
    

    


    try {
      const isValid = await trigger(fieldsToValidate);

      if (isValid) {
        setCurrentStep(prev => {
          const newStep = Math.min(prev + 1, totalSteps);
          return newStep;
        });
      } else {
        const failedFields = fieldsToValidate.filter(field => errors[field]);
        
        // Focus on the first field with an error
        if (failedFields.length > 0) {
          const firstErrorField = failedFields[0];
          
          // Use setTimeout to ensure the DOM is updated
          setTimeout(() => {
            let element: HTMLElement | null = null;
            
            // Try different selectors based on field type
            if (firstErrorField === 'city' || firstErrorField === 'state') {
              // For Select components in LocationSelector, find the trigger button
              const selectTrigger = document.querySelector(`[data-field="${firstErrorField}"] button[role="combobox"]`) ||
                                  document.querySelector(`label[for="${firstErrorField}"] + div button[role="combobox"]`) ||
                                  document.querySelector(`div:has(label[for="${firstErrorField}"]) button[role="combobox"]`);
              element = selectTrigger as HTMLElement;

            } else {
              // For regular input fields
              element = document.getElementById(firstErrorField) || 
                       document.querySelector(`[name="${firstErrorField}"]`) ||
                       document.querySelector(`input[name="${firstErrorField}"]`) ||
                       document.querySelector(`select[name="${firstErrorField}"]`) ||
                       document.querySelector(`textarea[name="${firstErrorField}"]`) as HTMLElement;
            }
            
            if (element) {
              element.focus();
              element.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
              // Try a more generic approach - find any focusable element in the section with the error
              const errorSection = document.querySelector(`[data-field="${firstErrorField}"]`) ||
                                  document.querySelector(`label[for="${firstErrorField}"]`)?.closest('div');
              if (errorSection) {
                const focusableElement = errorSection.querySelector('input, select, textarea, button[role="combobox"]') as HTMLElement;
                if (focusableElement) {
                  focusableElement.focus();
                  focusableElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
              }
            }
          }, 100);
        }
      }
    } catch (error) {
      // Validation errors are already displayed below each input field with red borders
    }
  };

  const prevStep = () => {
    setCurrentStep(prev => Math.max(prev - 1, 1));
  };

  const handleImageUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(event.target.files || []);
    
    if (!files || files.length === 0) {
      return;
    }
    
    // Validate file types
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    const invalidFiles = files.filter(file => !validTypes.includes(file.type));
    if (invalidFiles.length > 0) {
      notification.error('Only JPG, PNG, GIF, and WebP images are allowed');
      return;
    }
    
    // Check total file count limit
    if (files.length + selectedImages.length > 10) {
      notification.error('Maximum 10 images allowed');
      return;
    }

    // Show initial file sizes for comparison
    const totalOriginalSize = files.reduce((sum, file) => sum + file.size, 0);
    notification.info(`Processing ${files.length} image(s)... Original size: ${formatFileSize(totalOriginalSize)}`);

    try {
      // Compress images before processing
      const compressedFiles = await compressImages(files, 1920, 1080, 0.8);
      
      // Show compression results
      const totalCompressedSize = compressedFiles.reduce((sum, file) => sum + file.size, 0);
      const compressionRatio = ((totalOriginalSize - totalCompressedSize) / totalOriginalSize * 100).toFixed(1);
      
      // Validate compressed file sizes (max 2MB per file after compression)
      const maxSize = 2 * 1024 * 1024; // 2MB
      const oversizedFiles = compressedFiles.filter(file => file.size > maxSize);
      if (oversizedFiles.length > 0) {
        notification.error(`Some images are still too large after compression. Please use smaller images or reduce quality.`);
        return;
      }

      notification.success(`Images compressed successfully! Reduced size by ${compressionRatio}%`);
      
      const newImages = [...selectedImages, ...compressedFiles];
      setSelectedImages(newImages);

      // If this is the first image, set it as main image
      if (selectedImages.length === 0 && compressedFiles.length > 0) {
        setMainImageIndex(0);
      }

      // Create preview URLs
      const promises: Promise<string>[] = [];
      
      compressedFiles.forEach((file, index) => {
      const promise = new Promise<string>((resolve, reject) => {
        const reader = new FileReader();
        
        reader.onload = (e) => {
          const result = e.target?.result as string;
          
          if (!result || !result.startsWith('data:')) {
            reject(new Error(`Invalid file result for ${file.name}`));
            return;
          }
          
          resolve(result);
        };
        
        reader.onerror = (error) => {
          reject(new Error(`Failed to read file: ${file.name}`));
        };
        
        reader.onabort = () => {
          reject(new Error(`Reading aborted for file: ${file.name}`));
        };
        
        reader.readAsDataURL(file);
      });
      
      promises.push(promise);
    });

    // Wait for all files to be processed, then update preview URLs
    Promise.all(promises)
      .then((urls) => {
        
        setImagePreviewUrls(prev => {
          const newUrls = [...prev, ...urls];
          
          // Save to localStorage
          try {
            localStorage.setItem('addProperty_imagePreviewUrls', JSON.stringify(newUrls));
          } catch (error) {
            // Handle localStorage error silently
          }
          
          return newUrls;
        });
        
        notification.success(`Successfully uploaded ${urls.length} image(s)`);
      })
      .catch((error) => {
        notification.error(`Error processing images: ${error.message}`);
      });
    } catch (compressionError) {
      notification.error(`Failed to compress images: ${compressionError instanceof Error ? compressionError.message : 'Unknown error'}`);
    }

    // Clear the input value to allow re-uploading the same file
    event.target.value = '';
  };

  const removeImage = (index: number) => {
    setSelectedImages(prev => prev.filter((_, i) => i !== index));
    setImagePreviewUrls(prev => prev.filter((_, i) => i !== index));
    
    // Adjust main image index if necessary
    if (index === mainImageIndex) {
      // If removing the main image, set the first remaining image as main
      setMainImageIndex(0);
    } else if (index < mainImageIndex) {
      // If removing an image before the main image, adjust the index
      setMainImageIndex(prev => prev - 1);
    }
    
    // If no images left, reset main image index
    if (selectedImages.length <= 1) {
      setMainImageIndex(0);
    }
  };

  const selectMainImage = (index: number) => {
    setMainImageIndex(index);
  };

  // Video upload handler
  const handleVideoUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(event.target.files || []);

    if (!files || files.length === 0) {
      return;
    }

    // Validate file types
    const validTypes = ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo', 'video/webm'];
    const invalidFiles = files.filter(file => !validTypes.includes(file.type));
    if (invalidFiles.length > 0) {
      notification.error('Only MP4, MPEG, MOV, AVI, and WebM videos are allowed');
      return;
    }

    // Check total file count limit
    if (files.length + selectedVideos.length > 5) {
      notification.error('Maximum 5 videos allowed');
      return;
    }

    // Check file size (max 50MB per video)
    const maxSize = 50 * 1024 * 1024; // 50MB
    const oversizedFiles = files.filter(file => file.size > maxSize);
    if (oversizedFiles.length > 0) {
      notification.error('Video files must be less than 50MB each');
      return;
    }

    const newVideos = [...selectedVideos, ...files];
    setSelectedVideos(newVideos);

    // Create preview URLs
    const newPreviewUrls = files.map(file => URL.createObjectURL(file));
    setVideoPreviewUrls(prev => [...prev, ...newPreviewUrls]);

    notification.success(`Successfully added ${files.length} video(s)`);

    // Clear the input value
    event.target.value = '';
  };

  const removeVideo = (index: number) => {
    // Revoke the object URL to free memory
    if (videoPreviewUrls[index]) {
      URL.revokeObjectURL(videoPreviewUrls[index]);
    }

    setSelectedVideos(prev => prev.filter((_, i) => i !== index));
    setVideoPreviewUrls(prev => prev.filter((_, i) => i !== index));
  };

  const getFieldsForStep = (step: number) => {
    switch (step) {
      case 1:
        return ['title', 'address', 'city', 'state', 'listingType', 'propertyType', 'documentTypeId'] as (keyof PropertyFormData)[];
      case 2:
        return ['price', 'priceType', 'bedrooms', 'bathrooms', 'squareFootage', 'yearBuilt', 'parking'] as (keyof PropertyFormData)[];
      case 3:
        return [] as (keyof PropertyFormData)[]; // Image upload step - no form validation needed
      case 4:
        return ['description'] as (keyof PropertyFormData)[];
      case 5:
        return ['contactName', 'contactPhone', 'contactEmail'] as (keyof PropertyFormData)[]; // Move contact validation to step 5
      case 6:
        return [] as (keyof PropertyFormData)[]; // Final step - no validation needed, user must click submit manually
      default:
        return [];
    }
  };

  const onSubmit = async (data: PropertyFormData) => {
    if (isSubmitting) {
      return; // Prevent multiple submissions
    }

    // Check authentication before proceeding
    if (!isAuthenticated) {
      openAuthModal();
      return;
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

    try {

      // Parse address components
      const addressParts = formData.address?.split(',').map(part => part.trim()) || [];
      const street = addressParts[0] || '';
      let city = selectedCity || formData.city || addressParts[1] || '';
      let state = selectedState || formData.state || '';



      // If city, state are not provided, try to parse from address
      if (!city || !state) {
        if (addressParts.length >= 2) {
          city = city || addressParts[1];
          if (addressParts.length >= 3) {
            const lastPart = addressParts[addressParts.length - 1];
            // Extract state from the last part (remove any postal code)
            const stateMatch = lastPart.match(/^(.+?)(?:\s+\d{5}(?:-\d{4})?)?$/);
            if (stateMatch) {
              state = state || stateMatch[1];
            }
          }
        }
      }

      // Create the property data object
      // Find the selected property type to get its ID
      const selectedPropertyType = propertyTypes.find(type => type.slug === data.propertyType);

      const propertyData: any = {
        title: data.title,
        description: data.description || '',
        propertyType: data.propertyType, // Keep for backward compatibility
        property_type_id: selectedPropertyType?.id, // New foreign key relationship
        listingType: data.listingType,
        price: parseFloat(data.price.toString()),
        currency: data.currency || 'TRY',
        priceType: data.priceType,
        address: street,
        city: city,
        state: state,
        latitude: coordinates?.lat,
        longitude: coordinates?.lng,
        bedrooms: Number(data.bedrooms || 0),
        bathrooms: Number(data.bathrooms || 0),
        squareFootage: Number(data.squareFootage || 0),
        lotSize: Number(data.lotSize || 0),
        yearBuilt: Number(data.yearBuilt || new Date().getFullYear()),
        parking: data.parking || 'none',
        is_featured: false,
        is_available: true,
        availableDate: data.availableDate || new Date().toISOString(),
        features: Array.isArray(selectedFeatures) ? selectedFeatures : [],
        utilities: Array.isArray(selectedUtilities) ? selectedUtilities : [],
        contactName: data.contactName || '',
        contactPhone: data.contactPhone || '',
        contactEmail: data.contactEmail || '',
        document_type_id: data.documentTypeId ? Number(data.documentTypeId) : undefined,
        // Advanced details
        buildingAge: data.buildingAge ? Number(data.buildingAge) : undefined,
        buildingType: data.buildingType || undefined,
        floorType: data.floorType || undefined,
        windowType: data.windowType || undefined,
        maintenanceFee: data.maintenanceFee ? Number(data.maintenanceFee) : undefined,
        depositAmount: data.depositAmount ? Number(data.depositAmount) : undefined,
        annualTax: data.annualTax ? Number(data.annualTax) : undefined,
      };

      // Create FormData for file uploads
      const formDataToSend = new FormData();

      // Add all property data fields to FormData
      Object.keys(propertyData).forEach(key => {
        const value = propertyData[key];
        if (value !== null && value !== undefined) {
          if ((key === 'features' || key === 'utilities') && Array.isArray(value)) {
            // Handle features and utilities arrays
            value.forEach((id: number, index: number) => {
              formDataToSend.append(`${key}[${index}]`, String(id));
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
      if (selectedImages[mainImageIndex]) {
        formDataToSend.append('main_image', selectedImages[mainImageIndex]);
      }

      // Add gallery images (excluding the main image)
      selectedImages.forEach((image, index) => {
        if (index !== mainImageIndex) {
          formDataToSend.append('images[]', image);
        }
      });

      // Add videos to FormData
      selectedVideos.forEach((video, index) => {
        formDataToSend.append('videos[]', video);
      });

      try {
        const result = await addProperty(formDataToSend);

        // Clear localStorage after successful submission
        clearFormDataFromStorage();
        
        notification.success(t('messages.propertyPendingApproval'));
        navigate('/dashboard');
      } catch (apiError) {
        throw apiError; // Re-throw to be caught by the outer catch
      }
    } catch (error: any) {
      // Extract error message from different possible locations in the error object
      const errorMessage = error?.response?.data?.message ||
        error?.response?.data?.error ||
        error?.message ||
        'فشل في إضافة العقار. يرجى المحاولة مرة أخرى.';

      notification.error(errorMessage, {
        duration: 5000
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

                <div>
                  <LocationSelector
                    onLocationChange={handleLocationChange}
                    initialCity={selectedCity}
                    initialState={selectedState}
                  />
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
                      <Select onValueChange={field.onChange} value={field.value}>
                        <SelectTrigger className="h-12 text-sm border-2 rounded-xl bg-gradient-to-r from-[#067977]/10 to-[#067977]/5 border-gray-200 hover:border-purple-500 focus:border-[#067977] focus:ring-2 focus:ring-[#067977]/20 transition-all duration-200">
                          <div className="flex items-center gap-1">
                            <DollarSign className="h-3 w-3 text-purple-600" />
                            <SelectValue placeholder={t('addProperty.placeholders.selectAdType')} />
                          </div>
                        </SelectTrigger>
                        <SelectContent className="bg-white border-2 border-gray-100 rounded-xl shadow-lg">
                          <SelectItem key="rent" value="rent" className="text-sm py-3 px-4 hover:bg-[#067977]/10 focus:bg-[#067977]/20 transition-colors duration-150 cursor-pointer">
                            <div className="flex items-center gap-2">
                              <div className="w-1.5 h-1.5 bg-[#067977] rounded-full"></div>
                              {t('property.listingTypes.forRent')}
                            </div>
                          </SelectItem>
                          <SelectItem key="sale" value="sale" className="text-sm py-3 px-4 hover:bg-[#067977]/10 focus:bg-[#067977]/20 transition-colors duration-150 cursor-pointer">
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
                      <Select onValueChange={field.onChange} value={field.value}>
                        <SelectTrigger className="h-12 text-sm border-2 rounded-xl bg-gradient-to-r from-[#067977]/10 to-[#067977]/5 border-gray-200 hover:border-purple-500 focus:border-[#067977] focus:ring-2 focus:ring-[#067977]/20 transition-all duration-200">
                          <div className="flex items-center gap-1">
                            <Home className="h-3 w-3 text-purple-600" />
                            <SelectValue placeholder={t('addProperty.placeholders.selectPropertyType')} />
                          </div>
                        </SelectTrigger>
                        
                        <SelectContent className="bg-white border-2 border-gray-100 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                          {loadingPropertyTypes ? (
                            <SelectItem value="loading" disabled className="text-sm py-3 px-4">
                              <div className="flex items-center gap-2">
                                <div className="animate-spin h-3 w-3 border border-gray-300 border-t-[#067977] rounded-full"></div>
                                {t('addProperty.loading.propertyTypes')}
                              </div>
                            </SelectItem>
                          ) : propertyTypes.length > 0 ? (
                            propertyTypes
                              .filter((type, index, array) => {
                                // Remove duplicates based on slug
                                return array.findIndex(t => t.slug === type.slug) === index;
                              })
                              .map((type, index) => {
                                const displayName = propertyTypeService.getDisplayName(type, i18n.language);
                                const iconClass = propertyTypeService.getIconClass(type);

                                return (
                                  <SelectItem
                                    key={`${type.slug}-${type.id || index}`}
                                    value={type.slug}
                                    className="text-sm py-3 px-4 hover:bg-[#067977]/10 focus:bg-[#067977]/20 transition-colors duration-150 cursor-pointer"
                                  >
                                    <div className="flex items-center gap-2">
                                      <i className={`${iconClass} h-3 w-3 text-[#067977]`}></i>
                                      {displayName}
                                      {type.parent && (
                                        <span className="text-xs text-gray-500 ml-1">
                                          ({propertyTypeService.getDisplayName(type.parent, i18n.language)})
                                        </span>
                                      )}
                                    </div>
                                  </SelectItem>
                                );
                              })
                          ) : (
                            <SelectItem value="no-types" disabled className="text-sm py-3 px-4">
                              <div className="flex items-center gap-2">
                                <Home className="h-3 w-3 text-gray-400" />
                                {t('addProperty.noPropertyTypes')}
                              </div>
                            </SelectItem>
                          )}
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
                      value={field.value || undefined}
                      onValueChange={(value) => {

                        field.onChange(value);
                      }}
                      placeholder={t('addProperty.placeholders.selectDocumentType')}
                      loading={loadingDocumentTypes}
                      documentTypes={documentTypes}
                      showDescriptions={false}
                      className="w-full h-8 text-sm"
                    />
                  )}
                />
                {errors.documentTypeId && (
                  <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                    <X className="h-4 w-4" />
                    {errors.documentTypeId.message}
                  </p>
                )}
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
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    {...register('price', {
                  setValueAs: (value) => value === '' ? undefined : Number(value)
                })}
                  />
                  {errors.price && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <X className="h-4 w-4" />
                      {errors.price.message}
                    </p>
                  )}
                </div>

                <div>
                  <Label htmlFor="currency" className="text-sm font-medium text-gray-700 mb-2 block">
                    {t('property.currency', 'Currency')} <span className="text-red-500">*</span>
                  </Label>
                  <Controller
                    name="currency"
                    control={control}
                    render={({ field }) => (
                      <Select onValueChange={field.onChange} value={field.value} disabled={loadingCurrencies}>
                        <SelectTrigger className={`h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-emerald-100 hover:border-emerald-300 ${errors.currency ? 'border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-emerald-500'}`}>
                          <SelectValue placeholder={t('property.selectCurrency', 'Select Currency')} />
                        </SelectTrigger>
                        <SelectContent className="bg-white border-2 border-gray-100 rounded-xl shadow-lg">
                          {loadingCurrencies ? (
                            <SelectItem key="loading" value="loading" disabled className="text-sm py-3 px-4">
                              {t('common.loading')}
                            </SelectItem>
                          ) : (
                            availableCurrencies.map((currency) => (
                              <SelectItem
                                key={currency.value}
                                value={currency.value}
                                className="text-sm py-3 px-4 hover:bg-emerald-50 focus:bg-emerald-100 transition-colors duration-150 cursor-pointer"
                              >
                                {currency.label}
                              </SelectItem>
                            ))
                          )}
                        </SelectContent>
                      </Select>
                    )}
                  />
                  {errors.currency && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <X className="h-4 w-4" />
                      {errors.currency.message}
                    </p>
                  )}
                </div>

                <div>
                  <Label htmlFor="priceType" className="text-sm font-medium text-gray-700 mb-2 block">
                    {t('property.priceType')} <span className="text-red-500">*</span>
                  </Label>
                  <Controller
                    name="priceType"
                    control={control}
                    render={({ field }) => (
                      <Select onValueChange={field.onChange} value={field.value}>
                        <SelectTrigger className={`h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-emerald-100 hover:border-emerald-300 ${errors.priceType ? 'border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-emerald-500'}`}>
                          <SelectValue placeholder={t('property.selectPriceType')} />
                        </SelectTrigger>
                        <SelectContent className="bg-white border-2 border-gray-100 rounded-xl shadow-lg">
                          {loadingPriceTypes ? (
                            <SelectItem key="loading" value="loading" disabled className="text-sm py-3 px-4">
                              {t('common.loading')}
                            </SelectItem>
                          ) : priceTypesError ? (
                            <SelectItem key="error" value="error" disabled className="text-sm py-3 px-4 text-red-500">
                              {t('common.error')}
                            </SelectItem>
                          ) : (
                            availablePriceTypes.map((priceType) => (
                              <SelectItem
                                key={priceType.id} 
                                value={priceType.key} 
                                className="text-sm py-3 px-4 hover:bg-emerald-50 focus:bg-emerald-100 transition-colors duration-150 cursor-pointer"
                              >
                                <span key={`price-type-${priceType.id}`}>
                                  {i18n.language === 'ar' ? priceType.name_ar : 
                                   i18n.language === 'ku' ? priceType.name_ku : 
                                   priceType.name_en}
                                </span>
                              </SelectItem>
                            ))
                          )}
                        </SelectContent>
                      </Select>
                    )}
                  />
                  {errors.priceType && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <X className="h-4 w-4" />
                      {errors.priceType.message}
                    </p>
                  )}
                </div>
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
                    {...register('bedrooms', { 
                  setValueAs: (value) => value === '' ? undefined : Number(value)
                })}
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
                    {...register('bathrooms', { 
                  setValueAs: (value) => value === '' ? undefined : Number(value)
                })}
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
                    {...register('squareFootage', { 
                  setValueAs: (value) => value === '' ? undefined : Number(value)
                })}
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
                    {...register('yearBuilt', { 
                   setValueAs: (value) => value === '' ? undefined : Number(value)
                 })}
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
                    {...register('lotSize', { 
                  setValueAs: (value) => value === '' ? undefined : Number(value)
                })}
                  />
                  {errors.lotSize && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <X className="h-4 w-4" />
                      {String(errors.lotSize?.message || 'Invalid lot size')}
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
                    <Select onValueChange={field.onChange} value={field.value || 'none'}>
                      <SelectTrigger className="h-12 text-sm border-2 rounded-xl bg-gradient-to-r from-[#067977]/10 to-[#067977]/5 border-gray-200 hover:border-purple-500 focus:border-[#067977] focus:ring-2 focus:ring-[#067977]/20 transition-all duration-200">
                        <div className="flex items-center gap-1">
                          <Car className="h-3 w-3 text-orange-600" />
                          <SelectValue placeholder={t('addProperty.placeholders.selectParkingType')} />
                        </div>
                      </SelectTrigger>
                      <SelectContent className="bg-white border-2 border-gray-100 rounded-xl shadow-lg">
                        <SelectItem key="none" value="none" className="text-sm py-3 px-4 hover:bg-[#067977]/10 focus:bg-[#067977]/20 transition-colors duration-150 cursor-pointer">
                          <div className="flex items-center gap-2">
                            <div className="w-1.5 h-1.5 bg-red-500 rounded-full"></div>
                            {t('addProperty.parkingTypes.noParking')}
                          </div>
                        </SelectItem>
                        <SelectItem key="street" value="street" className="text-sm py-3 px-4 hover:bg-[#067977]/10 focus:bg-[#067977]/20 transition-colors duration-150 cursor-pointer">
                          <div className="flex items-center gap-2">
                            <div className="w-1.5 h-1.5 bg-yellow-500 rounded-full"></div>
                            {t('addProperty.parkingTypes.streetParking')}
                          </div>
                        </SelectItem>
                        <SelectItem key="garage" value="garage" className="text-sm py-3 px-4 hover:bg-[#067977]/10 focus:bg-[#067977]/20 transition-colors duration-150 cursor-pointer">
                          <div className="flex items-center gap-2">
                            <div className="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                            {t('addProperty.parkingTypes.closedGarage')}
                          </div>
                        </SelectItem>
                        <SelectItem key="driveway" value="driveway" className="text-sm py-3 px-4 hover:bg-[#067977]/10 focus:bg-[#067977]/20 transition-colors duration-150 cursor-pointer">
                          <div className="flex items-center gap-2">
                            <div className="w-1.5 h-1.5 bg-[#067977] rounded-full"></div>
                            {t('addProperty.parkingTypes.privateDriveway')}
                          </div>
                        </SelectItem>
                        <SelectItem key="carport" value="carport" className="text-sm py-3 px-4 hover:bg-[#067977]/10 focus:bg-[#067977]/20 transition-colors duration-150 cursor-pointer">
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

            {/* Phase 1 Enhancement Fields */}
            <div className="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-100">
              <h3 className="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2 font-['Cairo',_'Tajawal',_sans-serif]">
                <Building className="h-4 w-4 text-blue-600" />
                {t('addProperty.sectionTitles.buildingDetails')}
              </h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label htmlFor="floorNumber" className="text-sm font-medium text-gray-700 mb-2 block">
                    {t('property.details.floorNumber')}
                  </Label>
                  <InputWithIcon
                    id="floorNumber"
                    type="number"
                    min="0"
                    max="200"
                    icon={Building}
                    placeholder={t('addProperty.placeholders.floorNumber')}
                    className={`h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-blue-100 hover:border-blue-300 ${
                      errors.floorNumber ? 'border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-blue-500'
                    }`}
                    {...register('floorNumber', { 
                      setValueAs: (value) => value === '' ? undefined : Number(value)
                    })}
                  />
                  {errors.floorNumber && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <X className="h-4 w-4" />
                      {errors.floorNumber.message}
                    </p>
                  )}
                </div>

                <div>
                  <Label htmlFor="totalFloors" className="text-sm font-medium text-gray-700 mb-2 block">
                    {t('property.details.totalFloors')}
                  </Label>
                  <InputWithIcon
                    id="totalFloors"
                    type="number"
                    min="1"
                    max="200"
                    icon={Building}
                    placeholder={t('addProperty.placeholders.totalFloors')}
                    className={`h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-blue-100 hover:border-blue-300 ${
                      errors.totalFloors ? 'border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-blue-500'
                    }`}
                    {...register('totalFloors', { 
                      setValueAs: (value) => value === '' ? undefined : Number(value)
                    })}
                  />
                  {errors.totalFloors && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <X className="h-4 w-4" />
                      {errors.totalFloors.message}
                    </p>
                  )}
                </div>

                <div>
                  <Label htmlFor="balconyCount" className="text-sm font-medium text-gray-700 mb-2 block">
                    {t('property.details.balconyCount')}
                  </Label>
                  <InputWithIcon
                    id="balconyCount"
                    type="number"
                    min="0"
                    max="20"
                    icon={Home}
                    placeholder={t('addProperty.placeholders.balconyCount')}
                    className={`h-10 text-base border-2 rounded-lg transition-all duration-200 focus:ring-2 focus:ring-blue-100 hover:border-blue-300 ${
                      errors.balconyCount ? 'border-red-500 focus:ring-red-100' : 'border-gray-200 focus:border-blue-500'
                    }`}
                    {...register('balconyCount', { 
                      setValueAs: (value) => value === '' ? undefined : Number(value)
                    })}
                  />
                  {errors.balconyCount && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <X className="h-4 w-4" />
                      {errors.balconyCount.message}
                    </p>
                  )}
                </div>

                <div>
                  <Label className="text-sm font-medium text-gray-700 mb-2 block">
                    {t('property.details.orientation')}
                  </Label>
                  <Controller
                    name="orientation"
                    control={control}
                    render={({ field }) => (
                      <Select onValueChange={field.onChange} value={field.value || ''}>
                        <SelectTrigger className="h-10 text-sm border-2 rounded-lg bg-white border-gray-200 hover:border-blue-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all duration-200">
                          <div className="flex items-center gap-2">
                            <MapPin className="h-4 w-4 text-blue-600" />
                            <SelectValue placeholder={t('addProperty.placeholders.selectOrientation')} />
                          </div>
                        </SelectTrigger>
                        <SelectContent className="bg-white border-2 border-gray-100 rounded-lg shadow-lg">
                          {loadingDirections ? (
                            <SelectItem value="loading" disabled className="text-sm py-2 px-3">
                              Loading...
                            </SelectItem>
                          ) : directions.length > 0 ? (
                            directions.map((direction) => (
                              <SelectItem
                                key={direction.id}
                                value={direction.value}
                                className="text-sm py-2 px-3 hover:bg-blue-50 focus:bg-blue-100 transition-colors cursor-pointer"
                              >
                                {directionService.getDisplayName(direction, i18n.language)}
                              </SelectItem>
                            ))
                          ) : (
                            <SelectItem value="no-data" disabled className="text-sm py-2 px-3">
                              No directions available
                            </SelectItem>
                          )}
                        </SelectContent>
                      </Select>
                    )}
                  />
                  {errors.orientation && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <X className="h-4 w-4" />
                      {errors.orientation.message}
                    </p>
                  )}
                </div>

                <div>
                  <Label className="text-sm font-medium text-gray-700 mb-2 block">
                    {t('property.details.viewType')}
                  </Label>
                  <Controller
                    name="viewType"
                    control={control}
                    render={({ field }) => (
                      <Select onValueChange={field.onChange} value={field.value || ''}>
                        <SelectTrigger className="h-10 text-sm border-2 rounded-lg bg-white border-gray-200 hover:border-blue-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all duration-200">
                          <div className="flex items-center gap-2">
                            <Eye className="h-4 w-4 text-blue-600" />
                            <SelectValue placeholder={t('addProperty.placeholders.selectViewType')} />
                          </div>
                        </SelectTrigger>
                        <SelectContent className="bg-white border-2 border-gray-100 rounded-lg shadow-lg">
                          {loadingViewTypes ? (
                            <SelectItem value="loading" disabled className="text-sm py-2 px-3">
                              Loading...
                            </SelectItem>
                          ) : viewTypes.length > 0 ? (
                            viewTypes.map((viewType) => (
                              <SelectItem
                                key={viewType.id}
                                value={viewType.value}
                                className="text-sm py-2 px-3 hover:bg-blue-50 focus:bg-blue-100 transition-colors cursor-pointer"
                              >
                                {viewTypeService.getDisplayName(viewType, i18n.language)}
                              </SelectItem>
                            ))
                          ) : (
                            <SelectItem value="no-data" disabled className="text-sm py-2 px-3">
                              No view types available
                            </SelectItem>
                          )}
                        </SelectContent>
                      </Select>
                    )}
                  />
                  {errors.viewType && (
                    <p className="text-sm text-red-600 mt-2 flex items-center gap-1">
                      <X className="h-4 w-4" />
                      {errors.viewType.message}
                    </p>
                  )}
                </div>
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
                    {imagePreviewUrls.map((url, index) => {

                      return (
                        <div key={index} className="relative group">
                          <div 
                            className={`relative overflow-hidden rounded-xl border-2 transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer ${
                              index === mainImageIndex 
                                ? 'border-[#067977] ring-2 ring-[#067977]/30 bg-[#067977]/5' 
                                : 'border-gray-200 hover:border-purple-300'
                            }`}
                            onClick={() => selectMainImage(index)}
                          >
                            <FixedImage
                              src={url}
                              alt={`Preview ${index + 1}`}
                              className="w-full h-32 object-cover"
                              showLoadingSpinner={true}
                            />
                            <div className="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-200"></div>
                        
                            {/* Remove button */}
                            <button
                              type="button"
                              onClick={(e) => {
                                e.stopPropagation();
                                removeImage(index);
                              }}
                              className="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-lg z-10"
                            >
                              <X className="h-3 w-3" />
                            </button>
                        
                            {/* Main image indicator */}
                            {index === mainImageIndex && (
                              <div className="absolute bottom-2 left-2 bg-gradient-to-r from-[#067977] to-[#067977]/90 text-white text-xs px-2 py-1 rounded-full font-medium shadow-lg flex items-center gap-1 z-10">
                                <Star className="h-3 w-3 fill-current" />
                                <span className="hidden sm:inline">{t('addProperty.imageUpload.mainImage')}</span>
                                <span className="sm:hidden">رئيسية</span>
                              </div>
                            )}
                        
                            {/* Image number */}
                            <div className="absolute top-2 left-2 bg-white/90 text-gray-700 text-xs px-2 py-1 rounded-full font-medium">
                              {index + 1}
                            </div>
                        
                            {/* Click to select indicator */}
                            {index !== mainImageIndex && (
                              <div className="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200">
                                <div className="bg-white/90 text-gray-700 text-xs px-3 py-1.5 rounded-full font-medium shadow-lg">
                                  انقر لجعلها رئيسية
                                </div>
                              </div>
                            )}
                          </div>
                        </div>
                      );
                    })}

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

            {/* Video Upload Section */}
            <div className="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg p-4 border border-blue-100">
              <h3 className="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2 font-['Cairo',_'Tajawal',_sans-serif]">
                <svg className="h-4 w-4 text-blue-600" fill="none" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" viewBox="0 0 24 24" stroke="currentColor">
                  <path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                Property Videos (Optional)
              </h3>
              <p className="text-sm text-gray-600 mb-6 font-['Cairo',_'Tajawal',_sans-serif]">
                Upload videos to showcase your property (Optional)
              </p>

              <div className="border-2 border-dashed border-blue-300 rounded-lg p-6 text-center hover:border-blue-400 hover:bg-blue-50 transition-all duration-300 bg-white/50">
                <input
                  type="file"
                  multiple
                  accept="video/*"
                  onChange={handleVideoUpload}
                  className="hidden"
                  id="video-upload"
                />
                <label htmlFor="video-upload" className="cursor-pointer block">
                  <div className="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3 hover:bg-blue-200 transition-colors">
                    <svg className="h-8 w-8 text-blue-600" fill="none" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" viewBox="0 0 24 24" stroke="currentColor">
                      <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                  </div>
                  <p className="text-base font-semibold text-gray-900 mb-2 font-['Cairo',_'Tajawal',_sans-serif]">
                    Click to upload videos
                  </p>
                  <p className="text-sm text-gray-500 font-['Cairo',_'Tajawal',_sans-serif]">
                    MP4, MOV, AVI up to 50MB each (max 5 videos)
                  </p>
                </label>
              </div>

              {selectedVideos.length > 0 && (
                <div className="mt-6">
                  <div className="flex items-center justify-between mb-4">
                    <h4 className="text-base font-semibold text-gray-900 flex items-center gap-2 font-['Cairo',_'Tajawal',_sans-serif]">
                      <svg className="h-4 w-4 text-blue-600" fill="none" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                      </svg>
                      Selected Videos ({selectedVideos.length}/5)
                    </h4>
                    <div className="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                      {selectedVideos.length} of 5
                    </div>
                  </div>
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    {videoPreviewUrls.map((url, index) => (
                      <div key={index} className="relative group">
                        <div className="relative overflow-hidden rounded-xl border-2 border-gray-200 hover:border-blue-300 transition-all duration-200 shadow-sm hover:shadow-md">
                          <video
                            src={url}
                            className="w-full h-40 object-cover bg-black"
                            controls
                          />
                          <button
                            type="button"
                            onClick={() => removeVideo(index)}
                            className="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-lg z-10"
                          >
                            <X className="h-3 w-3" />
                          </button>
                          <div className="absolute top-2 left-2 bg-white/90 text-gray-700 text-xs px-2 py-1 rounded-full font-medium">
                            {index + 1}
                          </div>
                        </div>
                      </div>
                    ))}

                    {selectedVideos.length < 5 && (
                      <div className="relative">
                        <label htmlFor="video-upload" className="cursor-pointer block">
                          <div className="w-full h-40 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center hover:border-blue-400 hover:bg-blue-50 transition-all duration-200">
                            <Plus className="h-6 w-6 text-gray-400 mb-2" />
                            <span className="text-xs text-gray-500 font-medium">Add more videos</span>
                          </div>
                        </label>
                      </div>
                    )}
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
              <div className="space-y-2">
                <Textarea
                  id="description"
                  placeholder={t('forms.propertyDescriptionPlaceholder')}
                  rows={4}
                  className={`text-sm ${errors.description ? 'border-red-500' : ''}`}
                  {...register('description')}
                />
                <div className="flex gap-2">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={() => {
                      const textarea = document.getElementById('description') as HTMLTextAreaElement;
                      if (textarea) {
                        const cursorPos = textarea.selectionStart;
                        const textBefore = textarea.value.substring(0, cursorPos);
                        const textAfter = textarea.value.substring(cursorPos);
                        const newValue = textBefore + '\n---\n' + textAfter;
                        textarea.value = newValue;
                        textarea.focus();
                        textarea.setSelectionRange(cursorPos + 5, cursorPos + 5);
                        // Trigger onChange to update form state
                        const event = new Event('input', { bubbles: true });
                        textarea.dispatchEvent(event);
                      }
                    }}
                    className="text-xs h-7"
                  >
                    Add Divider
                  </Button>
                  <span className="text-xs text-gray-500 flex items-center">
                    Insert "---" to create visual dividers in your description
                  </span>
                </div>
              </div>
              {errors.description && (
                <p className="text-sm text-red-600 mt-1">{errors.description.message}</p>
              )}
            </div>

            <div>
              <Label className="text-sm">{t('steps.features')}</Label>
              <p className="text-xs text-gray-600 mb-2">
                {t('forms.selectAllFeatures')}
              </p>
              {loadingFeatures ? (
                <div className="flex items-center justify-center p-8 border rounded-lg">
                  <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-[#067977]"></div>
                  <span className="ml-2 text-sm text-gray-600">{t('common.loading')}...</span>
                </div>
              ) : featuresError ? (
                <div className="p-4 border rounded-lg bg-red-50 border-red-200">
                  <p className="text-sm text-red-600">{featuresError}</p>
                </div>
              ) : (
                <div className="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto border rounded-lg p-3">
                  {availableFeatures.map((feature) => (
                    <div key={feature.id} className="flex items-center space-x-2">
                      <Checkbox
                        id={`feature-${feature.id}`}
                        checked={selectedFeatures.includes(feature.id)}
                        onCheckedChange={() => handleFeatureToggle(feature.id)}
                      />
                      <Label htmlFor={`feature-${feature.id}`} className="text-xs">
                        {i18n.language === 'ar' ? feature.name_ar : 
                         i18n.language === 'ku' ? feature.name_ku : feature.name_en}
                      </Label>
                    </div>
                  ))}
                </div>
              )}
              <p className="text-xs text-gray-500 mt-2">
                {t('forms.selectedFeatures')}: {selectedFeatures.length} {t('forms.features')}
              </p>
            </div>

            <div>
              <Label className="text-sm font-medium text-gray-700 mb-3 block">
                {t('property.details.utilities')}
              </Label>
              {loadingUtilities ? (
                <div className="flex items-center justify-center p-8 border rounded-lg">
                  <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-[#067977]"></div>
                  <span className="ml-2 text-sm text-gray-600">{t('common.loading')}...</span>
                </div>
              ) : utilitiesError ? (
                <div className="p-4 border rounded-lg bg-red-50 border-red-200">
                  <p className="text-sm text-red-600">{utilitiesError}</p>
                </div>
              ) : (
                <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 mb-3">
                  {availableUtilities.map((utility) => (
                    <div key={utility.id} className="flex items-center space-x-2 rtl:space-x-reverse">
                      <Checkbox
                        id={`utility-${utility.id}`}
                        checked={selectedUtilities.includes(utility.id)}
                        onCheckedChange={() => handleUtilityToggle(utility.id)}
                        className="data-[state=checked]:bg-[#067977] data-[state=checked]:border-[#067977]"
                      />
                      <Label
                        htmlFor={`utility-${utility.id}`}
                        className="text-xs font-medium text-gray-700 cursor-pointer hover:text-[#067977] transition-colors"
                      >
                        {i18n.language === 'ar' ? utility.name_ar : 
                         i18n.language === 'ku' ? utility.name_ku : utility.name_en}
                      </Label>
                    </div>
                  ))}
                </div>
              )}
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
              <h3 className="text-base font-semibold mb-3">{t('steps.advancedDetails')}</h3>
              <p className="text-gray-600 mb-4 text-sm">
                {t('forms.advancedDetailsDescription')}
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label htmlFor="buildingAge" className="text-sm">{t('property.details.buildingAge')}</Label>
                <Input
                  id="buildingAge"
                  type="number"
                  placeholder={t('forms.buildingAgePlaceholder')}
                  className={`text-sm h-9 ${errors.buildingAge ? 'border-red-500' : ''}`}
                  {...register('buildingAge', { 
                  setValueAs: (value) => value === '' ? undefined : Number(value)
                })}
                />
                {errors.buildingAge && (
                  <p className="text-sm text-red-600 mt-1">{errors.buildingAge.message}</p>
                )}
              </div>

              <div>
                <Label htmlFor="buildingType" className="text-sm">{t('property.details.buildingType')}</Label>
                {loadingBuildingTypes ? (
                  <div className="flex items-center justify-center p-2 border rounded-lg">
                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-[#067977]"></div>
                    <span className="ml-2 text-xs text-gray-600">{t('common.loading')}...</span>
                  </div>
                ) : (
                  <Select onValueChange={(value) => setValue('buildingTypeId', parseInt(value))}>
                    <SelectTrigger className={`text-sm h-9 ${errors.buildingType ? 'border-red-500' : ''}`}>
                      <SelectValue placeholder={t('forms.selectBuildingType')} />
                    </SelectTrigger>
                    <SelectContent>
                      {buildingTypes.map((type) => (
                        <SelectItem key={type.id} value={type.id.toString()}>
                          {buildingTypeService.getDisplayName(type, i18n.language)}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                )}
                {errors.buildingType && (
                  <p className="text-sm text-red-600 mt-1">{errors.buildingType.message}</p>
                )}
              </div>

              <div>
                <Label htmlFor="floorType" className="text-sm">{t('property.details.floorType')}</Label>
                {loadingFloorTypes ? (
                  <div className="flex items-center justify-center p-2 border rounded-lg">
                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-[#067977]"></div>
                    <span className="ml-2 text-xs text-gray-600">{t('common.loading')}...</span>
                  </div>
                ) : (
                  <Select onValueChange={(value) => setValue('floorTypeId', parseInt(value))}>
                    <SelectTrigger className={`text-sm h-9 ${errors.floorType ? 'border-red-500' : ''}`}>
                      <SelectValue placeholder={t('forms.selectFloorType')} />
                    </SelectTrigger>
                    <SelectContent>
                      {floorTypes.map((type) => (
                        <SelectItem key={type.id} value={type.id.toString()}>
                          {floorTypeService.getDisplayName(type, i18n.language)}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                )}
                {errors.floorType && (
                  <p className="text-sm text-red-600 mt-1">{errors.floorType.message}</p>
                )}
              </div>

              <div>
                <Label htmlFor="windowType" className="text-sm">{t('property.details.windowType')}</Label>
                {loadingWindowTypes ? (
                  <div className="flex items-center justify-center p-2 border rounded-lg">
                    <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-[#067977]"></div>
                    <span className="ml-2 text-xs text-gray-600">{t('common.loading')}...</span>
                  </div>
                ) : (
                  <Select onValueChange={(value) => setValue('windowTypeId', parseInt(value))}>
                    <SelectTrigger className={`text-sm h-9 ${errors.windowType ? 'border-red-500' : ''}`}>
                      <SelectValue placeholder={t('forms.selectWindowType')} />
                    </SelectTrigger>
                    <SelectContent>
                      {windowTypes.map((type) => (
                        <SelectItem key={type.id} value={type.id.toString()}>
                          {windowTypeService.getDisplayName(type, i18n.language)}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                )}
                {errors.windowType && (
                  <p className="text-sm text-red-600 mt-1">{errors.windowType.message}</p>
                )}
              </div>

              <div>
                <Label htmlFor="maintenanceFee" className="text-sm">{t('property.details.maintenanceFee')}</Label>
                <Input
                  id="maintenanceFee"
                  type="number"
                  placeholder={t('forms.maintenanceFeePlaceholder')}
                  className={`text-sm h-9 ${errors.maintenanceFee ? 'border-red-500' : ''}`}
                  {...register('maintenanceFee', { 
                  setValueAs: (value) => value === '' ? undefined : Number(value)
                })}
                />
                {errors.maintenanceFee && (
                  <p className="text-sm text-red-600 mt-1">{errors.maintenanceFee.message}</p>
                )}
              </div>

              <div>
                <Label htmlFor="depositAmount" className="text-sm">{t('property.details.depositAmount')}</Label>
                <Input
                  id="depositAmount"
                  type="number"
                  placeholder={t('forms.depositAmountPlaceholder')}
                  className={`text-sm h-9 ${errors.depositAmount ? 'border-red-500' : ''}`}
                  {...register('depositAmount', { 
                  setValueAs: (value) => value === '' ? undefined : Number(value)
                })}
                />
                {errors.depositAmount && (
                  <p className="text-sm text-red-600 mt-1">{errors.depositAmount.message}</p>
                )}
              </div>

              <div>
                <Label htmlFor="annualTax" className="text-sm">{t('property.details.annualTax')}</Label>
                <Input
                  id="annualTax"
                  type="number"
                  placeholder={t('forms.annualTaxPlaceholder')}
                  className={`text-sm h-9 ${errors.annualTax ? 'border-red-500' : ''}`}
                  {...register('annualTax', { 
                   setValueAs: (value) => value === '' ? undefined : Number(value)
                 })}
                />
                {errors.annualTax && (
                  <p className="text-sm text-red-600 mt-1">{errors.annualTax.message}</p>
                )}
              </div>
            </div>

            {/* Contact Information Section */}
            <div className="mt-8">
              <h3 className="text-base font-semibold mb-3">{t('steps.contactInformation')}</h3>
              <p className="text-gray-600 mb-4 text-sm">
                {t('forms.contactInfoDescription')}
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label htmlFor="contactName" className="text-sm">{t('forms.contactName')} *</Label>
                <InputWithIcon
                  id="contactName"
                  icon={User}
                  placeholder={t('forms.yourFullName')}
                  className={`text-sm h-9 ${errors.contactName ? 'border-red-500' : ''}`}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter') {
                      e.preventDefault();
                    }
                  }}
                  {...register('contactName')}
                />
                {errors.contactName && (
                  <p className="text-sm text-red-600 mt-1">{errors.contactName.message}</p>
                )}
              </div>

              <div>
                <Label htmlFor="contactPhone" className="text-sm">{t('forms.phoneNumber')}</Label>
                <InputWithIcon
                  id="contactPhone"
                  name="contactPhone"
                  icon={Phone}
                  placeholder={t('forms.phoneNumberPlaceholder')}
                  className={`text-sm h-9 ${errors.contactPhone ? 'border-red-500' : ''}`}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter') {
                      e.preventDefault();
                    }
                  }}
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
                  onKeyDown={(e) => {
                    if (e.key === 'Enter') {
                      e.preventDefault();
                    }
                  }}
                  {...register('contactEmail')}
                />
                {errors.contactEmail && (
                  <p className="text-sm text-red-600 mt-1">{errors.contactEmail.message}</p>
                )}
              </div>
            </div>
            
          </div>
        );

      case 6:
        return (
          <div className="space-y-6">
            <div className="text-center">
              <div className="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <h3 className="text-xl font-semibold mb-2">{t('addProperty.finalStep.title')}</h3>
              <p className="text-gray-600 text-sm">
                {t('addProperty.finalStep.description')}
              </p>
            </div>

            <div className="bg-gray-50 rounded-lg p-4">
              <h4 className="font-medium mb-3">{t('addProperty.finalStep.summary')}</h4>
              <div className="space-y-2 text-sm text-gray-600">
                <p><span className="font-medium">{t('forms.propertyType')}:</span> {watch('propertyType')}</p>
                <p><span className="font-medium">{t('forms.price')}:</span> {watch('price') ? `${watch('price')} ${t('common.currency')}` : t('common.notSpecified')}</p>
                <p><span className="font-medium">{t('forms.location')}:</span> {watch('address')}</p>
                <p><span className="font-medium">{t('forms.contactName')}:</span> {watch('contactName')}</p>
                <p><span className="font-medium">{t('forms.emailAddress')}:</span> {watch('contactEmail')}</p>
              </div>
            </div>

            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div className="flex items-start">
                <svg className="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                  <h5 className="font-medium text-blue-900 mb-1">{t('addProperty.finalStep.readyTitle')}</h5>
                  <p className="text-blue-700 text-sm">{t('addProperty.finalStep.readyDescription')}</p>
                </div>
              </div>
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
            {[1, 2, 3, 4, 5, 6].map((step) => (
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
                  {step === 5 && t('addProperty.progress.advanced')}
                  {step === 6 && t('addProperty.progress.contact')}
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
                  {currentStep === 5 && <Settings className="h-4 w-4 text-white" />}
                  {currentStep === 6 && <User className="h-4 w-4 text-white" />}
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
                        <span className="text-[#067977]">{t('addProperty.stepTitles.advanced')}</span>
                        <p className="text-xs font-normal text-gray-600 mt-1">{t('addProperty.stepDescriptions.advanced')}</p>
                      </>
                    )}
                    {currentStep === 6 && (
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
      
      {/* Authentication Modal */}
      <AuthModal
        isOpen={showAuthModal}
        onClose={closeAuthModal}
        title={t('auth.requireAuth.title')}
        message={t('auth.requireAuth.addPropertyMessage')}
        onSuccess={() => {
          closeAuthModal();
          // User is now authenticated and can manually submit the form by clicking the button
        }}
      />
    </div>
  );
};

export default AddProperty;
