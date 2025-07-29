import React, { createContext, useContext, useReducer, useEffect, ReactNode } from 'react';
import { Property, User, SearchFilters } from '../types';
import { useTranslation } from 'react-i18next';
import {
  getProperties, 
  createProperty, 
  updateProperty as updatePropertyAPI, 
  deleteProperty as deletePropertyAPI,
  toggleFavorite as toggleFavoriteAPI,
  getFavoriteProperties
} from '../services/propertyService';
import authService from '../services/authService';

// First, update the AppState interface to include language property
interface AppState {
  properties: Property[];
  filteredProperties: Property[];
  user: User | null;
  favorites: string[];
  searchFilters: SearchFilters;
  loading: boolean;
  error: string | null;
  language: string; // Add this line
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
  language: 'ar', // Add this line with default language
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
  | { type: 'SET_SEARCH_FILTERS'; payload: SearchFilters }
  | { type: 'SET_LOADING'; payload: boolean }
  | { type: 'SET_ERROR'; payload: string | null }
  | { type: 'SET_LANGUAGE'; payload: string }; // Add this line

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
  toggleFavorite: (propertyId: string) => void;
  login: (email: string, password: string) => Promise<boolean>;
  logout: () => void;
  register: (userData: Omit<User, 'id' | 'properties' | 'favorites'>) => Promise<boolean>;
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

// Add to the AppContextType interface
interface AppContextType {
  state: AppState;
  dispatch: React.Dispatch<AppAction>;
  loadProperties: () => Promise<void>;
  filterProperties: (filters: SearchFilters) => void;
  addProperty: (property: Omit<Property, 'id' | 'datePosted'>) => void;
  updateProperty: (property: Property) => void;
  deleteProperty: (id: string) => void;
  toggleFavorite: (propertyId: string) => void;
  login: (email: string, password: string) => Promise<boolean>;
  logout: () => void;
  register: (userData: Omit<User, 'id' | 'properties' | 'favorites'>) => Promise<boolean>;
  changeLanguage: (lang: string) => void;
}

// Add to the AppProvider component
export const AppProvider: React.FC<AppProviderProps> = ({ children }) => {
  const [state, dispatch] = useReducer(appReducer, initialState);
  const { i18n } = useTranslation();
  
  // Load properties from API
  const loadProperties = async () => {
    try {
      dispatch({ type: 'SET_LOADING', payload: true });
      const response = await getProperties();
      const properties = response.data.map((property: any) => ({
        id: property.id.toString(),
        slug: property.slug,
        title: property.title,
        address: property.location.full_address,
        price: property.price.amount,
        propertyType: property.property_type,
        listingType: property.listing_type,
        bedrooms: property.details.bedrooms,
        bathrooms: property.details.bathrooms,
        squareFootage: property.details.square_feet,
        description: property.description,
        features: property.amenities || [],
        images: property.images.gallery?.map((img: any) => img.url) || [],
        mainImage: property.images.main || '/placeholder-property.jpg',
        yearBuilt: property.details.year_built,
        coordinates: {
          lat: property.location.coordinates.latitude,
          lng: property.location.coordinates.longitude
        },
        contact: {
          name: property.owner?.full_name || 'Agent',
          phone: property.owner?.phone || '',
          email: property.owner?.email || ''
        },
        datePosted: property.created_at,
        availableDate: property.available_from,
        petPolicy: property.details?.pet_policy,
        parking: property.details?.parking?.type,
        lotSize: property.details?.lot_size,
        garage: property.details?.parking?.type === 'garage' ? 'Yes' : 'No',
        building: property.details?.building_name
      }));
      dispatch({ type: 'SET_PROPERTIES', payload: properties });
    } catch (error) {
      console.error('Failed to load properties:', error);
      dispatch({ type: 'SET_ERROR', payload: 'Failed to load properties' });
    } finally {
      dispatch({ type: 'SET_LOADING', payload: false });
    }
  };

  // Filter properties based on search criteria
  const filterProperties = (filters: SearchFilters) => {
    dispatch({ type: 'SET_SEARCH_FILTERS', payload: filters });
    
    let filtered = [...state.properties];

    if (filters.listingType && filters.listingType !== 'all') {
      filtered = filtered.filter(p => p.listingType === filters.listingType);
    }

    if (filters.propertyType && filters.propertyType !== 'all') {
      filtered = filtered.filter(p => p.propertyType === filters.propertyType);
    }

    if (filters.minPrice !== undefined) {
      filtered = filtered.filter(p => p.price >= filters.minPrice!);
    }

    if (filters.maxPrice !== undefined) {
      filtered = filtered.filter(p => p.price <= filters.maxPrice!);
    }

    if (filters.bedrooms !== undefined) {
      filtered = filtered.filter(p => p.bedrooms >= filters.bedrooms!);
    }

    if (filters.bathrooms !== undefined) {
      filtered = filtered.filter(p => p.bathrooms >= filters.bathrooms!);
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
  };

  // Add property using API
  const addProperty = async (propertyData: Omit<Property, 'id' | 'datePosted'>) => {
    try {
      dispatch({ type: 'SET_LOADING', payload: true });
      
      // Transform frontend data to API format
      const apiData = {
        title: propertyData.title,
        description: propertyData.description,
        property_type: propertyData.propertyType,
        listing_type: propertyData.listingType,
        price: propertyData.price,
        street_address: propertyData.address,
        city: propertyData.address.split(',')[1]?.trim() || 'Default City',
        state: propertyData.address.split(',')[2]?.trim() || 'Default State',
        postal_code: propertyData.address.split(',')[3]?.trim() || '00000',
        country: 'US',
        bedrooms: propertyData.bedrooms,
        bathrooms: propertyData.bathrooms,
        square_feet: propertyData.squareFootage,
        year_built: propertyData.yearBuilt,
        amenities: propertyData.features,
        latitude: propertyData.coordinates?.lat,
        longitude: propertyData.coordinates?.lng,
        available_from: propertyData.availableDate,
        parking_type: propertyData.parking || 'none',
        lot_size: propertyData.lotSize
      };

      const response = await createProperty(apiData);
      
      // Transform API response back to frontend format
      const newProperty: Property = {
        id: response.property.id.toString(),
        slug: response.property.slug,
        title: response.property.title,
        address: response.property.location.full_address,
        price: response.property.price.amount,
        propertyType: response.property.property_type,
        listingType: response.property.listing_type,
        bedrooms: response.property.details.bedrooms,
        bathrooms: response.property.details.bathrooms,
        squareFootage: response.property.details.square_feet,
        description: response.property.description,
        features: response.property.amenities || [],
        images: response.property.images.gallery?.map((img: any) => img.url) || [],
        mainImage: response.property.images.main || '/placeholder-property.jpg',
        yearBuilt: response.property.details.year_built,
        coordinates: {
          lat: response.property.location.coordinates.latitude,
          lng: response.property.location.coordinates.longitude
        },
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

      dispatch({ type: 'ADD_PROPERTY', payload: newProperty });
      
      // Update user's properties list
      if (state.user) {
        const updatedUser = {
          ...state.user,
          properties: [...state.user.properties, newProperty.id]
        };
        dispatch({ type: 'SET_USER', payload: updatedUser });
      }
    } catch (error) {
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
      const apiData = {
        title: property.title,
        description: property.description,
        property_type: property.propertyType,
        listing_type: property.listingType,
        price: property.price,
        street_address: property.address,
        city: property.address.split(',')[1]?.trim() || 'Default City',
        state: property.address.split(',')[2]?.trim() || 'Default State',
        postal_code: property.address.split(',')[3]?.trim() || '00000',
        country: 'US',
        bedrooms: property.bedrooms,
        bathrooms: property.bathrooms,
        square_feet: property.squareFootage,
        year_built: property.yearBuilt,
        amenities: property.features,
        latitude: property.coordinates?.lat,
        longitude: property.coordinates?.lng,
        available_from: property.availableDate,
        parking_type: property.parking || 'none',
        lot_size: property.lotSize
      };

      const response = await updatePropertyAPI(property.id, apiData);
      
      // Transform API response back to frontend format
      const updatedProperty: Property = {
        id: response.property.id.toString(),
        slug: response.property.slug,
        title: response.property.title,
        address: response.property.location.full_address,
        price: response.property.price.amount,
        propertyType: response.property.property_type,
        listingType: response.property.listing_type,
        bedrooms: response.property.details.bedrooms,
        bathrooms: response.property.details.bathrooms,
        squareFootage: response.property.details.square_feet,
        description: response.property.description,
        features: response.property.amenities || [],
        images: response.property.images.gallery?.map((img: any) => img.url) || [],
        mainImage: response.property.images.main || '/placeholder-property.jpg',
        yearBuilt: response.property.details.year_built,
        coordinates: {
          lat: response.property.location.coordinates.latitude,
          lng: response.property.location.coordinates.longitude
        },
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
    } catch (error) {
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
      await deletePropertyAPI(id);
      dispatch({ type: 'DELETE_PROPERTY', payload: id });
      
      // Update user's properties list
      if (state.user) {
        const updatedUser = {
          ...state.user,
          properties: state.user.properties.filter(propId => propId !== id)
        };
        dispatch({ type: 'SET_USER', payload: updatedUser });
      }
    } catch (error) {
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

      const response = await toggleFavoriteAPI(id);
      
      if (response.favorited) {
        dispatch({ type: 'ADD_FAVORITE', payload: id });
      } else {
        dispatch({ type: 'REMOVE_FAVORITE', payload: id });
      }
      
      return response.favorited;
    } catch (error) {
      console.error('Failed to toggle favorite:', error);
      dispatch({ type: 'SET_ERROR', payload: 'Failed to update favorite' });
      throw error;
    }
  };

  // Login using API
  const login = async (email: string, password: string): Promise<boolean> => {
    try {
      dispatch({ type: 'SET_LOADING', payload: true });
      const response = await authService.login({ email, password });
      
      // Transform API response to frontend user format
      const user: User = {
        id: response.user.id.toString(),
        name: response.user.full_name,
        email: response.user.email,
        phone: response.user.phone || '',
        avatar: response.user.avatar?.url || '',
        properties: [], // Will be loaded separately
        favorites: [], // Will be loaded separately
        dateJoined: response.user.created_at,
        isVerified: response.user.is_verified,
        userType: response.user.user_type
      };
      
      dispatch({ type: 'SET_USER', payload: user });
      
      // Load user's properties and favorites
      await loadProperties();
      
      return true;
    } catch (error) {
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
    } catch (error) {
      console.error('Logout failed:', error);
      // Still clear local state even if API call fails
      dispatch({ type: 'SET_USER', payload: null });
      dispatch({ type: 'SET_PROPERTIES', payload: [] });
    }
  };

  // Register using API
  const register = async (userData: {
    name: string;
    email: string;
    password: string;
    password_confirmation?: string;
    phone?: string;
  }) => {
    try {
      dispatch({ type: 'SET_LOADING', payload: true });
      
      // Ensure password_confirmation is set
      const registrationData = {
        ...userData,
        password_confirmation: userData.password_confirmation || userData.password
      };
      
      const response = await authService.register(registrationData);
      
      // Transform API response to frontend user format
      const user: User = {
        id: response.user.id.toString(),
        name: response.user.full_name,
        email: response.user.email,
        phone: response.user.phone || '',
        avatar: response.user.avatar?.url || '',
        properties: [],
        favorites: [],
        dateJoined: response.user.created_at,
        isVerified: response.user.is_verified,
        userType: response.user.user_type
      };
      
      dispatch({ type: 'SET_USER', payload: user });
      
      return true;
    } catch (error) {
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
    } catch (error) {
      console.error('Failed to load user favorites:', error);
    }
  };

  // Initialize app - check for existing session and load data
  useEffect(() => {
    const initializeApp = async () => {
      try {
        // Check if user is already authenticated
        const user = await authService.getCurrentUser();
        if (user) {
          // Transform API response to frontend user format
          const frontendUser: User = {
            id: user.id.toString(),
            name: user.full_name,
            email: user.email,
            phone: user.phone || '',
            avatar: user.avatar?.url || '',
            properties: [],
            favorites: [],
            dateJoined: user.created_at,
            isVerified: user.is_verified,
            userType: user.user_type
          };
          
          dispatch({ type: 'SET_USER', payload: frontendUser });
          
          // Load user's favorites
          await loadUserFavorites();
        }
        
        // Load properties regardless of authentication status
        await loadProperties();
      } catch (error) {
        console.error('Failed to initialize app:', error);
        // Still load properties even if user auth fails
        await loadProperties();
      }
    };

    initializeApp();
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
    changeLanguage, // Add this line
  };

  return <AppContext.Provider value={value}>{children}</AppContext.Provider>;
};

// Remove the duplicate changeLanguage function and extra code block at the end of the file
