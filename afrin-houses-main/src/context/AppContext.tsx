import React, { createContext, useContext, useReducer, useEffect, ReactNode } from 'react';
import { Property, User, SearchFilters } from '../types';
import { useTranslation } from 'react-i18next';
import { 
  getProperties, 
  createProperty as createPropertyAPI, 
  updateProperty as updatePropertyAPI, 
  deleteProperty as deletePropertyAPI,
  toggleFavorite as toggleFavoriteAPI,
  getFavoriteProperties,
  getUserProperties
} from '../services/propertyService';
import authService from '../services/authService';

// Update the AppState interface to use the correct User type
interface AppState {
  properties: Property[];
  filteredProperties: Property[];
  user: User | null;
  favorites: string[];
  searchFilters: SearchFilters;
  loading: boolean;
  error: string | null;
  language: string;
}

// Update the initialState to include language
const initialState: AppState = {
  properties: [],
  filteredProperties: [],
  user: null,
  favorites: [],
  searchFilters: {},
  loading: false,
  error: null,
  language: 'ar', 
};

// Add SET_LANGUAGE to AppAction type
type AppAction =
  | { type: 'SET_PROPERTIES'; payload: Property[] }
  | { type: 'ADD_PROPERTY'; payload: Property }
  | { type: 'UPDATE_PROPERTY'; payload: Property }
  | { type: 'DELETE_PROPERTY'; payload: string }
  | { type: 'SET_FILTERED_PROPERTIES'; payload: Property[] }
  | { type: 'SET_USER'; payload: User | null }
  | { type: 'ADD_FAVORITE'; payload: string }
  | { type: 'REMOVE_FAVORITE'; payload: string }
  | { type: 'SET_FAVORITES'; payload: string[] }
  | { type: 'SET_SEARCH_FILTERS'; payload: SearchFilters }
  | { type: 'SET_LOADING'; payload: boolean }
  | { type: 'SET_ERROR'; payload: string | null }
  | { type: 'SET_LANGUAGE'; payload: string };

const appReducer = (state: AppState, action: AppAction): AppState => {
  switch (action.type) {
    case 'SET_PROPERTIES':
      return { ...state, properties: action.payload, filteredProperties: action.payload };
    case 'ADD_PROPERTY':
      const newProperties = [...state.properties, action.payload];
      return { ...state, properties: newProperties, filteredProperties: newProperties };
    case 'UPDATE_PROPERTY':
      const updatedProperties = state.properties.map(p => 
        p.id === action.payload.id ? action.payload : p
      );
      return { ...state, properties: updatedProperties, filteredProperties: updatedProperties };
    case 'DELETE_PROPERTY':
      const filteredProps = state.properties.filter(p => p.id !== action.payload);
      return { ...state, properties: filteredProps, filteredProperties: filteredProps };
    case 'SET_FILTERED_PROPERTIES':
      return { ...state, filteredProperties: action.payload };
    case 'SET_USER':
      return { ...state, user: action.payload };
    case 'ADD_FAVORITE':
      const newFavorites = [...state.favorites, action.payload];
      localStorage.setItem('favorites', JSON.stringify(newFavorites));
      return { ...state, favorites: newFavorites };
    case 'REMOVE_FAVORITE':
      const updatedFavorites = state.favorites.filter(id => id !== action.payload);
      localStorage.setItem('favorites', JSON.stringify(updatedFavorites));
      return { ...state, favorites: updatedFavorites };
    case 'SET_FAVORITES':
      localStorage.setItem('favorites', JSON.stringify(action.payload));
      return { ...state, favorites: action.payload };
    case 'SET_SEARCH_FILTERS':
      return { ...state, searchFilters: action.payload };
    case 'SET_LOADING':
      return { ...state, loading: action.payload };
    case 'SET_ERROR':
      return { ...state, error: action.payload };
    case 'SET_LANGUAGE':
      return { ...state, language: action.payload };

    default:
      return state;
  }
};

interface AppContextType {
  state: AppState;
  dispatch: React.Dispatch<AppAction>;
  loadProperties: () => Promise<void>;
  filterProperties: (filters: SearchFilters) => void;
  addProperty: (property: Omit<Property, 'id' | 'datePosted'>) => void;
  updateProperty: (property: Property) => void;
  deleteProperty: (id: string) => void;
  toggleFavorite: (propertyId: string) => Promise<boolean>;
  login: (email: string, password: string) => Promise<boolean>;
  logout: () => void;
  register: (userData: Omit<User, 'id' | 'properties' | 'favorites'>) => Promise<boolean>;
  changeLanguage: (lang: string) => void;
  updateUser: (userData: Partial<User>) => Promise<void>;
}

const AppContext = createContext<AppContextType | undefined>(undefined);

export const useApp = () => {
  const context = useContext(AppContext);
  if (context === undefined) {
    throw new Error('useApp must be used within an AppProvider');
  }
  return context;
};

interface AppProviderProps {
  children: ReactNode;
}

export const AppProvider: React.FC<AppProviderProps> = ({ children }) => {
  const [state, dispatch] = useReducer(appReducer, initialState);
  const { i18n } = useTranslation();
  
  // Helper to normalize values that may be localized objects { name_ar, name_en }
  const getLocaleName = (val: any): string => {
    if (!val) return '';
    if (typeof val === 'string') return val;
    if (typeof val === 'object') {
      const locale = i18n.language === 'ar' ? 'ar' : 'en';
      const ar = (val as any).name_ar ?? (val as any).ar ?? (val as any).name;
      const en = (val as any).name_en ?? (val as any).en ?? (val as any).name;
      return locale === 'ar' ? (ar || en || '') : (en || ar || '');
    }
    return String(val);
  };

  // Load properties from API
  const loadProperties = async () => {
    try {
      dispatch({ type: 'SET_LOADING', payload: true });
      console.log('DEBUG: Fetching properties from API...');
      
      const response = await getProperties();
      console.log('DEBUG: Properties API response:', response);
      
      // Handle different response structures safely with proper type checking
      let propertiesData: any[] = [];
      
      // Check if response is an array
      if (Array.isArray(response)) {
        propertiesData = response;
      } 
      // Check if response has a data property that is an array
      else if (response && typeof response === 'object' && 'data' in response && Array.isArray((response as any).data)) {
        propertiesData = (response as any).data;
      } 
      // Check if response has a nested data.data array (pagination structure)
      else if (response && typeof response === 'object' && 'data' in response && 
               (response as any).data && typeof (response as any).data === 'object' && 
               'data' in (response as any).data && Array.isArray((response as any).data.data)) {
        propertiesData = (response as any).data.data;
      } 
      // Handle unexpected response structure
      else {
        console.warn('Unexpected properties API response structure:', response);
        propertiesData = [];
      }
      
      console.log('DEBUG: Extracted properties data:', propertiesData);

      // Normalize location fields coming from API (they may be objects with name_ar/name_en)
      const locale = i18n.language === 'ar' ? 'ar' : 'en';
      const normalizeName = (val: any): string => {
        if (!val) return '';
        if (typeof val === 'string') return val;
        if (typeof val === 'object') {
          const ar = (val as any).name_ar ?? (val as any).ar ?? (val as any).name;
          const en = (val as any).name_en ?? (val as any).en ?? (val as any).name;
          return locale === 'ar' ? (ar || en || '') : (en || ar || '');
        }
        return String(val);
      };
      
      const properties: Property[] = propertiesData.map((property: any) => {
        // Extract address components
        const streetAddress = property.street_address || '';
        const city = normalizeName(property.city);
        const stateVal = normalizeName(property.state);
        const zipCode = property.postal_code || property.zip_code || '';
        const country = normalizeName(property.country);
        const fullAddress = property.location?.full_address || 
          [streetAddress, city, stateVal, zipCode].filter(Boolean).join(', ');
        
        return {
          // Required fields
          id: property.id.toString(),
          title: property.title || 'Untitled Property',
          description: property.description || '',
          price: property.price?.amount || property.price || 0,
          address: fullAddress,
          city: city,
          state: stateVal,
          zip_code: zipCode,
          country: country,
          property_type: property.property_type || 'house',
          listing_type: property.listing_type || 'sale',
          
          // Optional fields with defaults
          bedrooms: property.details?.bedrooms || property.bedrooms || 0,
          bathrooms: property.details?.bathrooms || property.bathrooms || 0,
          square_feet: property.details?.square_feet || property.square_feet || 0,
          year_built: property.details?.year_built || property.year_built,
          
          // Status and metadata
          status: property.status || 'available',
          is_featured: property.is_featured || false,
          created_at: property.created_at || new Date().toISOString(),
          updated_at: property.updated_at || new Date().toISOString(),
          user_id: property.user_id || null,
          
          // Media
          media: (property.images?.gallery || []).map((img: any, index: number) => ({
            id: img.id || index,
            url: img.url || img,
            type: 'image',
            is_featured: img.is_featured || false
          })),
          
          // Additional fields
          slug: property.slug || `property-${property.id}`,
          features: property.amenities || property.features || [],
          latitude: property.location?.coordinates?.latitude || property.latitude,
          longitude: property.location?.coordinates?.longitude || property.longitude,
          
          // Extended fields (will be ignored by TypeScript but kept for backward compatibility)
          ...(property as any)
        } as Property;
      });
      
      console.log('DEBUG: Transformed properties:', properties);
      dispatch({ type: 'SET_PROPERTIES', payload: properties });
    } catch (error: any) {
      console.error('Failed to load properties:', error);
      dispatch({ type: 'SET_ERROR', payload: 'Failed to load properties' });
    } finally {
      dispatch({ type: 'SET_LOADING', payload: false });
    }
  };

  // Filter properties based on search criteria - memoized with useCallback
  const filterProperties = React.useCallback((filters: SearchFilters) => {
    console.log('filterProperties called with:', filters);
    dispatch({ type: 'SET_SEARCH_FILTERS', payload: filters });
    
    // Get current properties from state
    let filtered = [...state.properties];

    // Handle search query - check both search and searchQuery for maximum compatibility
    const searchQuery = filters.search || filters.searchQuery;
    if (searchQuery) {
      const query = searchQuery.toString().toLowerCase().trim();
      if (query) {
        filtered = filtered.filter(property => {
          // Check various fields for matches
          const searchableFields = [
            property.title,
            property.description,
            property.property_type,
            property.propertyType,
            property.address,
            property.city,
            property.state,
            property.postalCode,
            property.country,
            // Check if any feature matches
            ...(property.features || [])
          ];

          // Check if any field includes the search query
          return searchableFields.some(field => 
            field && field.toString().toLowerCase().includes(query)
          );
        });
      }
    }

    if (filters.listingType && filters.listingType !== 'all') {
      filtered = filtered.filter(p => p.listingType === filters.listingType);
    }

    if (filters.propertyType && filters.propertyType !== 'all') {
      filtered = filtered.filter(p => p.propertyType === filters.propertyType);
    }

    if (filters.minPrice !== undefined) {
      filtered = filtered.filter(p => {
        const price = typeof p.price === 'string' ? parseFloat(p.price) || 0 : p.price || 0;
        return price >= (filters.minPrice || 0);
      });
    }

    if (filters.maxPrice !== undefined) {
      filtered = filtered.filter(p => {
        const price = typeof p.price === 'string' ? parseFloat(p.price) || 0 : p.price || 0;
        return price <= (filters.maxPrice || Number.MAX_SAFE_INTEGER);
      });
    }

    if (filters.bedrooms !== undefined) {
      filtered = filtered.filter(p => (p.bedrooms || 0) >= (filters.bedrooms || 0));
    }

    if (filters.bathrooms !== undefined) {
      filtered = filtered.filter(p => (p.bathrooms || 0) >= (filters.bathrooms || 0));
    }

    if (filters.minSquareFootage !== undefined) {
      filtered = filtered.filter(p => p.squareFootage >= filters.minSquareFootage!);
    }

    if (filters.maxSquareFootage !== undefined) {
      filtered = filtered.filter(p => p.squareFootage <= filters.maxSquareFootage!);
    }

    if (filters.features && filters.features.length > 0) {
      filtered = filtered.filter(p => 
        filters.features!.every(feature => p.features.includes(feature))
      );
    }

    if (filters.location) {
      filtered = filtered.filter(p => 
        p.address.toLowerCase().includes(filters.location!.toLowerCase()) ||
        p.title.toLowerCase().includes(filters.location!.toLowerCase())
      );
    }

    dispatch({ type: 'SET_FILTERED_PROPERTIES', payload: filtered });
  }, [state.properties, dispatch]); // Add dependencies here

  // Add property using API
  const addProperty = async (propertyData: any) => {
    try {
      dispatch({ type: 'SET_LOADING', payload: true });
      
      // The images and mainImage are already properly structured as File objects
      // No need to extract imageFiles anymore since AddProperty.tsx now sends the correct structure
      
      console.log('Sending property data to API service:', propertyData);
      const response = await createPropertyAPI(propertyData);
      
      // Transform API response back to frontend format
      const newProperty: Property = {
        id: response.property.id.toString(),
        slug: response.property.slug,
        title: response.property.title,
        description: response.property.description || '',
        address: response.property.location?.full_address || `${response.property.address || ''}`,
        city: getLocaleName(response.property.city),
        state: getLocaleName(response.property.state),
        zip_code: response.property.postalCode || response.property.zip_code || '',
        country: getLocaleName(response.property.country),
        price: response.property.price || 0,
        listingType: (response.property.listing_type as 'rent' | 'sale') || 'sale',
        propertyType: (response.property.property_type as Property['propertyType']) || 'apartment',
        bedrooms: response.property.bedrooms || 0,
        bathrooms: response.property.bathrooms || 0,
        squareFootage: response.property.square_feet || 0,
        property_type: response.property.propertyType || response.property.property_type || '',
        listing_type: response.property.listingType || 'sale',
        square_feet: response.property.squareFootage || response.property.square_feet,
        features: response.property.amenities || response.property.features || [],
        images: response.property.images?.gallery?.map((img: any) => img.url) || 
               (Array.isArray(response.property.images) ? response.property.images : []) || [],
        mainImage: response.property.images?.main || 
                 (Array.isArray(response.property.images) && response.property.images[0]) || 
                 '/placeholder-property.jpg',
        yearBuilt: response.property.yearBuilt,
        coordinates: {
          lat: response.property.latitude || 0,
          lng: response.property.longitude || 0
        },
        contact: {
          name: response.property.contactName || 'Agent',
          phone: response.property.contactPhone || '',
          email: response.property.contactEmail || ''
        },
        datePosted: response.property.created_at,
        availableDate: response.property.availableDate,
        petPolicy: response.property.petPolicy,
        parking: response.property.parking,
        lotSize: response.property.lotSize,
        garage: response.property.parking === 'garage' ? 'Yes' : 'No',
        building: response.property.building
      };

      dispatch({ type: 'ADD_PROPERTY', payload: newProperty });
      
      // Reload user properties from backend to ensure synchronization
      if (state.user) {
        await loadUserProperties();
      }
    } catch (error: any) {
      console.error('Failed to add property:', error);
      dispatch({ type: 'SET_ERROR', payload: 'Failed to add property' });
      throw error;
    } finally {
      dispatch({ type: 'SET_LOADING', payload: false });
    }
  };

  // Update property using API
  const updateProperty = async (property: Property) => {
    try {
      dispatch({ type: 'SET_LOADING', payload: true });
      
      // Transform frontend data to API format
      const apiData: any = {
        title: property.title,
        description: property.description,
        price: property.price,
        propertyType: property.propertyType,
        listingType: property.listingType,
        street_address: property.address.split(',')[0]?.trim() || property.address,
        city: property.address.split(',')[1]?.trim() || 'Unknown',
        state: property.address.split(',')[2]?.trim() || 'Unknown',
        postal_code: property.address.split(',')[3]?.trim() || '00000',
        bedrooms: property.bedrooms,
        bathrooms: property.bathrooms,
        square_feet: property.squareFootage,
        year_built: property.yearBuilt,
        lot_size: property.lotSize,
        amenities: property.features,
        latitude: property.coordinates.lat,
        longitude: property.coordinates.lng,
        available_from: property.availableDate,
        parking_type: property.parking,
        is_available: true,
        status: 'published',
        is_featured: false, 
        slug: property.slug 
      };

      const response = await updatePropertyAPI(Number(property.id), apiData);
      
      // Transform API response back to frontend format with all required fields
      const updatedProperty: Property = {
        // Required fields
        id: response.property.id.toString(),
        title: response.property.title || 'Untitled Property',
        description: response.property.description || '',
        price: response.property.price?.amount || response.property.price || 0,
        address: response.property.location?.full_address || `${property.street_address || ''}, ${getLocaleName(property.city) || ''}, ${getLocaleName(property.state) || ''} ${property.postal_code || ''}`,
        city: getLocaleName(response.property.location?.city ?? response.property.city),
        state: getLocaleName(response.property.location?.state ?? response.property.state),
        zip_code: response.property.location?.postal_code || response.property.postal_code || response.property.zip_code || '',
        country: getLocaleName(response.property.location?.country ?? response.property.country),
        property_type: response.property.property_type || 'house',
        listing_type: response.property.listing_type || 'sale',
        
        // Optional fields with defaults
        bedrooms: response.property.details?.bedrooms || response.property.bedrooms || 0,
        bathrooms: response.property.details?.bathrooms || response.property.bathrooms || 0,
        square_feet: response.property.details?.square_feet || response.property.square_feet || 0,
        year_built: response.property.details?.year_built || response.property.year_built,
        
        // Status and metadata
        status: response.property.status || 'available',
        is_featured: response.property.is_featured || false,
        created_at: response.property.created_at || new Date().toISOString(),
        updated_at: response.property.updated_at || new Date().toISOString(),
        user_id: response.property.user_id || null,
        
        // Media
        media: (response.property.images?.gallery || []).map((img: any, index: number) => ({
          id: img.id || index,
          url: typeof img === 'string' ? img : (img.url || ''),
          type: 'image',
          is_featured: img.is_featured || false
        })),
        
        // Additional fields
        slug: response.property.slug || `property-${response.property.id}`,
        features: response.property.amenities || response.property.features || [],
        latitude: response.property.location?.coordinates?.latitude || response.property.latitude,
        longitude: response.property.location?.coordinates?.longitude || response.property.longitude,
        
        // Include all other properties from the response for backward compatibility
        ...(response.property as any),
        contact: {
          name: response.property.owner?.full_name || 'Agent',
          phone: response.property.owner?.phone || '',
          email: response.property.owner?.email || ''
        },
        datePosted: response.property.created_at,
        availableDate: response.property.available_from,
        petPolicy: response.property.details?.pet_policy,
        parking: response.property.details?.parking?.type,
        lotSize: response.property.details?.lot_size,
        garage: response.property.details?.parking?.type === 'garage' ? 'Yes' : 'No',
        building: response.property.details?.building_name
      };

      dispatch({ type: 'UPDATE_PROPERTY', payload: updatedProperty });
    } catch (error: any) {
      console.error('Failed to update property:', error);
      dispatch({ type: 'SET_ERROR', payload: 'Failed to update property' });
      throw error;
    } finally {
      dispatch({ type: 'SET_LOADING', payload: false });
    }
  };

  // Delete property using API
  const deleteProperty = async (id: string) => {
    try {
      dispatch({ type: 'SET_LOADING', payload: true });
      await deletePropertyAPI(Number(id));
      dispatch({ type: 'DELETE_PROPERTY', payload: id });
      
      // Update user's properties list
      if (state.user) {
        const updatedUser = {
          ...state.user,
          properties: state.user.properties.filter(propId => propId !== id)
        };
        dispatch({ type: 'SET_USER', payload: updatedUser });
      }
    } catch (error: any) {
      console.error('Failed to delete property:', error);
      dispatch({ type: 'SET_ERROR', payload: 'Failed to delete property' });
      throw error;
    } finally {
      dispatch({ type: 'SET_LOADING', payload: false });
    }
  };

  // Toggle favorite using API
  const toggleFavorite = async (propertyId: string | number) => {
    try {
      const id = propertyId.toString();
      if (!state.user) {
        throw new Error('User must be logged in to favorite properties');
      }

      const response = await toggleFavoriteAPI(Number(propertyId));
      
      if (response.is_favorited) {
        dispatch({ type: 'ADD_FAVORITE', payload: id });
      } else {
        dispatch({ type: 'REMOVE_FAVORITE', payload: id });
      }
      
      return response.is_favorited;
    } catch (error: any) {
      console.error('Failed to toggle favorite:', error);
      dispatch({ type: 'SET_ERROR', payload: 'Failed to update favorite' });
      throw error;
    }
  };

  // Login using API
  const login = async (email: string, password: string): Promise<boolean> => {
    try {
      dispatch({ type: 'SET_LOADING', payload: true });
      console.log('DEBUG: Starting login process for:', email);
      
      const response = await authService.login({ email, password });
      console.log('DEBUG: Login response received:', response);
      
      // Transform API response to frontend user format
      const user: User = {
        id: response.user?.id?.toString() || '',
        name: (response.user?.name || (response.user as any)?.full_name || '').toString(),
        first_name: (response.user as any)?.first_name || response.user?.name?.split(' ')[0] || '',
        last_name: (response.user as any)?.last_name || response.user?.name?.split(' ').slice(1).join(' ') || '',
        email: response.user?.email || '',
        phone: response.user?.phone || '',
        avatar: (() => {
          if (!response.user?.avatar) return '';
          return typeof response.user.avatar === 'string' 
            ? response.user.avatar 
            : (response.user.avatar as any)?.url || '';
        })() as string | { url: string },
        properties: [], 
        favorites: [], 
        is_verified: Boolean((response.user as any)?.is_verified), 
        user_type: (response.user as any)?.user_type || 'user',
        date_joined: (response.user as any)?.date_joined || (response.user as any)?.created_at || new Date().toISOString(),
        created_at: (response.user as any)?.created_at || new Date().toISOString(),
        updated_at: (response.user as any)?.updated_at || new Date().toISOString()
      };
      
      console.log('DEBUG: Setting user in state:', user);
      dispatch({ type: 'SET_USER', payload: user });
      
      // Load user's properties and favorites
      console.log('DEBUG: Loading properties...');
      await loadProperties();
      console.log('DEBUG: Loading user properties...');
      await loadUserProperties();
      console.log('DEBUG: Loading user favorites...');
      await loadUserFavorites();
      console.log('DEBUG: Login process completed');
      
      return true;
    } catch (error: any) {
      console.error('Login failed:', error);
      dispatch({ type: 'SET_ERROR', payload: 'Login failed' });
      return false;
    } finally {
      dispatch({ type: 'SET_LOADING', payload: false });
    }
  };

  // Logout using API
  const logout = async () => {
    try {
      await authService.logout();
      dispatch({ type: 'SET_USER', payload: null });
      dispatch({ type: 'SET_PROPERTIES', payload: [] });
    } catch (error: any) {
      console.error('Logout failed:', error);
      // Still clear local state even if API call fails
      dispatch({ type: 'SET_USER', payload: null });
      dispatch({ type: 'SET_PROPERTIES', payload: [] });
    }
  };

  // Register using API
  const register = async (userData: {
    first_name: string;
    last_name: string;
    email: string;
    password: string;
    password_confirmation?: string;
    phone?: string;
    terms_accepted: boolean;
  }) => {
    try {
      dispatch({ type: 'SET_LOADING', payload: true });
      
      // Debug: Log the incoming userData
      console.log('Registration form data:', userData);
      
      // Ensure password_confirmation is set and include terms_accepted
      const registrationData = {
        first_name: userData.first_name,
        last_name: userData.last_name,
        email: userData.email,
        phone: userData.phone || '',
        password: userData.password,
        password_confirmation: userData.password_confirmation || userData.password,
        terms_accepted: userData.terms_accepted
      };
      
      // Debug: Log the registration data being sent
      console.log('Registration data being sent:', registrationData);

      const response = await authService.register(registrationData);
      
      if (response?.user) {
        const fullName = `${userData.first_name} ${userData.last_name}`.trim();
        const user: User = {
          id: response.user?.id?.toString() || '',
          name: fullName,
          first_name: userData.first_name,
          last_name: userData.last_name,
          email: response.user?.email || '',
          phone: response.user?.phone || '',
          avatar: (() => {
            if (!response.user?.avatar) return '';
            return typeof response.user.avatar === 'string' 
              ? response.user.avatar 
              : (response.user.avatar as any)?.url || '';
          })() as string | { url: string },
          properties: [],
          favorites: [],
          is_verified: (response.user as any)?.is_verified || false,
          user_type: (response.user as any)?.user_type || 'user',
          date_joined: (response.user as any)?.date_joined || (response.user as any)?.created_at || new Date().toISOString(),
          created_at: (response.user as any)?.created_at || new Date().toISOString()
        } as User;
        
        dispatch({ type: 'SET_USER', payload: user });
        return true;
      }
      
      return false;
    } catch (error: any) {
      console.error('Registration failed:', error);
      dispatch({ type: 'SET_ERROR', payload: 'Registration failed' });
      return false;
    } finally {
      dispatch({ type: 'SET_LOADING', payload: false });
    }
  };

  // Load user's favorites
  const loadUserFavorites = async () => {
    try {
      if (!state.user) return;
      
      const favorites = await getFavoriteProperties();
      const favoriteIds = favorites.map((prop: any) => prop.id.toString());
      dispatch({ type: 'SET_FAVORITES', payload: favoriteIds });
    } catch (error: any) {
      console.error('Failed to load user favorites:', error);
    }
  };

  // Load user's properties
  const loadUserProperties = async () => {
    try {
      if (!state.user) {
        console.log('DEBUG: No user found, skipping loadUserProperties');
        return;
      }
      
      console.log('DEBUG: Loading user properties for user:', state.user.id);
      const userProperties = await getUserProperties();
      console.log('DEBUG: Received user properties from API:', userProperties);
      
      // Transform user properties to frontend format
      const transformedUserProperties = userProperties.map((property: any) => ({
        id: property.id.toString(),
        slug: property.slug,
        title: property.title,
        address: property.location?.full_address || `${property.street_address || ''}, ${getLocaleName(property.city) || ''}, ${getLocaleName(property.state) || ''} ${property.postal_code || ''}`,
        price: property.price?.amount || property.price || 0,
        propertyType: property.property_type,
        listingType: property.listing_type,
        bedrooms: property.details?.bedrooms || property.bedrooms || 0,
        bathrooms: property.details?.bathrooms || property.bathrooms || 0,
        squareFootage: property.details?.square_feet || property.square_feet || 0,
        description: property.description || '',
        features: property.amenities || [],
        images: property.images?.gallery?.map((img: any) => img.url) || [],
        mainImage: property.images?.main || property.main_image || '/placeholder-property.jpg',
        yearBuilt: property.details?.year_built || property.year_built,
        coordinates: {
          lat: property.location?.coordinates?.latitude || property.latitude || 0,
          lng: property.location?.coordinates?.longitude || property.longitude || 0
        },
        contact: {
          name: property.owner?.full_name || property.contact_name || 'Agent',
          phone: property.owner?.phone || property.contact_phone || '',
          email: property.owner?.email || property.contact_email || ''
        },
        datePosted: property.created_at,
        availableDate: property.available_from,
        petPolicy: property.details?.pet_policy || property.pet_policy,
        parking: property.details?.parking?.type || property.parking_type,
        lotSize: property.details?.lot_size || property.lot_size,
        garage: (property.details?.parking?.type === 'garage' || property.parking_type === 'garage') ? 'Yes' : 'No',
        building: property.details?.building_name || property.building_name
      }));
      
      const propertyIds = transformedUserProperties.map((prop: any) => prop.id.toString());
      console.log('DEBUG: Extracted property IDs:', propertyIds);
      
      // Add user properties to main properties array if they don't exist
      const existingPropertyIds = state.properties.map(p => p.id);
      const newProperties = transformedUserProperties.filter(prop => !existingPropertyIds.includes(prop.id));
      
      if (newProperties.length > 0) {
        console.log('DEBUG: Adding new user properties to main array:', newProperties);
        const updatedProperties = [...state.properties, ...newProperties];
        dispatch({ type: 'SET_PROPERTIES', payload: updatedProperties });
      }
      
      // Update user with their property IDs
      const updatedUser = { 
        ...state.user, 
        properties: propertyIds 
      };
      
      console.log('DEBUG: Updating user with properties:', updatedUser);
      dispatch({ 
        type: 'SET_USER', 
        payload: updatedUser
      });
    } catch (error: any) {
      console.error('Failed to load user properties:', error);
    }
  };

  // Initialize app - check for existing session and load data
  useEffect(() => {
    let isMounted = true;
    
    const initializeApp = async () => {
      try {
        console.log('DEBUG: Initializing app...');
        
        // Set initial loading state
        if (isMounted) {
          dispatch({ type: 'SET_LOADING', payload: true });
        }
        
        // First check if we have a stored user in localStorage
        const storedUser = authService.getStoredUser();
        const token = authService.getToken();
        
        if (token && storedUser) {
          // Immediately set the stored user to prevent flash of unauthenticated content
          console.log('DEBUG: Found stored user, setting initial state');
          const initialUser: User = {
            id: storedUser.id?.toString() || '',
            name: (storedUser.name || `${storedUser.first_name || ''} ${storedUser.last_name || ''}`.trim() || 'User').toString(),
            first_name: storedUser.first_name || storedUser.name?.split(' ')[0] || '',
            last_name: storedUser.last_name || storedUser.name?.split(' ').slice(1).join(' ') || '',
            email: storedUser.email || '',
            phone: storedUser.phone || '',
            avatar: (() => {
              if (!storedUser.avatar) return '';
              return typeof storedUser.avatar === 'string' 
                ? storedUser.avatar 
                : (storedUser.avatar as any)?.url || '';
            })() as string | { url: string },
            properties: [],
            favorites: [],
            is_verified: storedUser.is_verified || false,
            user_type: storedUser.user_type || 'user',
            date_joined: storedUser.date_joined || storedUser.created_at || new Date().toISOString(),
            created_at: storedUser.created_at || new Date().toISOString(),
            updated_at: storedUser.updated_at || new Date().toISOString()
          };
          
          dispatch({ 
            type: 'SET_USER', 
            payload: initialUser
          });
        }
        
        // Then try to refresh the session
        if (token) {
          try {
            console.log('DEBUG: Refreshing user session...');
            const user = await authService.getCurrentUser();
            
            if (user) {
              console.log('DEBUG: Successfully refreshed user session');
              // Transform API response to frontend user format
              const frontendUser: User = {
                id: user.id?.toString() || '',
                name: (user.name || `${user.first_name || ''} ${user.last_name || ''}`.trim() || 'User').toString(),
                first_name: user.first_name || user.name?.split(' ')[0] || '',
                last_name: user.last_name || user.name?.split(' ').slice(1).join(' ') || '',
                email: user.email || '',
                phone: user.phone || '',
                avatar: (() => {
                  if (!user.avatar) return '';
                  return typeof user.avatar === 'string' 
                    ? user.avatar 
                    : (user.avatar as any)?.url || '';
                })() as string | { url: string },
                properties: [],
                favorites: [],
                is_verified: user.is_verified || false,
                user_type: user.user_type || 'user',
                date_joined: user.date_joined || user.created_at || new Date().toISOString(),
                created_at: user.created_at || new Date().toISOString()
              };
              
              console.log('DEBUG: Setting authenticated user in state:', frontendUser);
              dispatch({ type: 'SET_USER', payload: frontendUser });
              
              // Load user's favorites and properties in parallel
              console.log('DEBUG: Loading user data...');
              await Promise.all([
                loadUserFavorites(),
                loadUserProperties()
              ]);
            } else {
              console.log('DEBUG: No user data found, clearing auth data');
              authService.clearAuthData();
              dispatch({ type: 'SET_USER', payload: null });
            }
          } catch (error) {
            console.error('Error during session refresh:', error);
            // If we get a 401, clear the invalid token
            if ((error as any)?.response?.status === 401) {
              console.log('DEBUG: Invalid token, clearing auth data');
              authService.clearAuthData();
              dispatch({ type: 'SET_USER', payload: null });
            }
          }
        } else {
          console.log('DEBUG: No authentication token found');
          dispatch({ type: 'SET_USER', payload: null });
        }
        
        // Load properties regardless of authentication status
        console.log('DEBUG: Loading all properties...');
        await loadProperties();
        console.log('DEBUG: App initialization completed');
      } catch (error) {
        console.error('Failed to initialize app:', error);
        // Still load properties even if user auth fails
        if (isMounted) {
          await loadProperties();
        }
      } finally {
        if (isMounted) {
          dispatch({ type: 'SET_LOADING', payload: false });
        }
      }
    };

    initializeApp();
    
    // Cleanup function to prevent state updates after unmount
    return () => {
      isMounted = false;
    };
  }, []);

  // Move changeLanguage function inside the component
  const changeLanguage = (lang: string) => {
    i18n.changeLanguage(lang);
    document.documentElement.lang = lang;
    document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
    dispatch({ type: 'SET_LANGUAGE', payload: lang });
  };

  // Set initial language
  useEffect(() => {
    // Get language from localStorage or use default
    const savedLanguage = localStorage.getItem('language') || 'ar';
    changeLanguage(savedLanguage);
  }, []);

  const updateUser = async (userData: Partial<User>) => {
    try {
      if (!state.user) {
        throw new Error('User must be logged in to update profile');
      }

      const response = await authService.updateUser(userData);
      
      if (response.user) {
        const updatedUser: User = {
          ...state.user,
          ...userData
        };
        
        dispatch({ type: 'SET_USER', payload: updatedUser });
      }
    } catch (error: any) {
      console.error('Failed to update user:', error);
      dispatch({ type: 'SET_ERROR', payload: 'Failed to update user' });
      throw error;
    }
  };

  const value: AppContextType = {
    state,
    dispatch,
    loadProperties,
    filterProperties,
    addProperty,
    updateProperty,
    deleteProperty,
    toggleFavorite,
    login,
    logout,
    register,
    changeLanguage,
    updateUser
  };

  return <AppContext.Provider value={value}>{children}</AppContext.Provider>;
};
