import React, { createContext, useContext, useReducer, useEffect, ReactNode } from 'react';
import { Property, User, SearchFilters } from '../types';

// Add to the imports
import { useTranslation } from 'react-i18next';

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
  
  // Load properties from JSON file
  const loadProperties = async () => {
    try {
      dispatch({ type: 'SET_LOADING', payload: true });
      const response = await fetch('/data/properties.json');
      const properties = await response.json();
      dispatch({ type: 'SET_PROPERTIES', payload: properties });
    } catch (error) {
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

  // Add new property
  const addProperty = (propertyData: Omit<Property, 'id' | 'datePosted'>) => {
    const newProperty: Property = {
      ...propertyData,
      id: Date.now().toString(),
      datePosted: new Date().toISOString(),
    };
    dispatch({ type: 'ADD_PROPERTY', payload: newProperty });
    
    // Save to localStorage for persistence
    const savedProperties = JSON.parse(localStorage.getItem('userProperties') || '[]');
    savedProperties.push(newProperty);
    localStorage.setItem('userProperties', JSON.stringify(savedProperties));
  };

  // Update property
  const updateProperty = (property: Property) => {
    dispatch({ type: 'UPDATE_PROPERTY', payload: property });
    
    // Update in localStorage
    const savedProperties = JSON.parse(localStorage.getItem('userProperties') || '[]');
    const updatedProperties = savedProperties.map((p: Property) => 
      p.id === property.id ? property : p
    );
    localStorage.setItem('userProperties', JSON.stringify(updatedProperties));
  };

  // Delete property
  const deleteProperty = (id: string) => {
    dispatch({ type: 'DELETE_PROPERTY', payload: id });
    
    // Remove from localStorage
    const savedProperties = JSON.parse(localStorage.getItem('userProperties') || '[]');
    const filteredProperties = savedProperties.filter((p: Property) => p.id !== id);
    localStorage.setItem('userProperties', JSON.stringify(filteredProperties));
  };

  // Toggle favorite
  const toggleFavorite = (propertyId: string | number) => {
    const id = propertyId.toString();
    if (state.user) {
      if (state.favorites.includes(id)) {
        dispatch({ type: 'REMOVE_FAVORITE', payload: id });
      } else {
        dispatch({ type: 'ADD_FAVORITE', payload: id });
      }
    } else {
      // Redirect to login if user is not logged in
      // You can add navigation logic here if using react-router
      console.log('User must be logged in to add favorites');
    }
  };

  // Simple login system (for demo purposes)
  const login = async (email: string, password: string): Promise<boolean> => {
    try {
      // In a real app, this would make an API call
      // For demo, we'll create a user based on email
      const user: User = {
        id: Date.now().toString(),
        name: email.split('@')[0],
        email,
        properties: [],
        favorites: state.favorites,
      };
      
      dispatch({ type: 'SET_USER', payload: user });
      localStorage.setItem('currentUser', JSON.stringify(user));
      return true;
    } catch (error) {
      return false;
    }
  };

  // Logout
  const logout = () => {
    dispatch({ type: 'SET_USER', payload: null });
    localStorage.removeItem('currentUser');
  };

  // Register new user
  const register = async (userData: Omit<User, 'id' | 'properties' | 'favorites'>): Promise<boolean> => {
    try {
      const user: User = {
        ...userData,
        id: Date.now().toString(),
        properties: [],
        favorites: [],
      };
      
      dispatch({ type: 'SET_USER', payload: user });
      localStorage.setItem('currentUser', JSON.stringify(user));
      return true;
    } catch (error) {
      return false;
    }
  };

  // Load initial data
  useEffect(() => {
    loadProperties();
    
    // Load user from localStorage
    const savedUser = localStorage.getItem('currentUser');
    if (savedUser) {
      dispatch({ type: 'SET_USER', payload: JSON.parse(savedUser) });
    }
    
    // Load favorites from localStorage
    const savedFavorites = localStorage.getItem('favorites');
    if (savedFavorites) {
      const favorites = JSON.parse(savedFavorites);
      favorites.forEach((id: string) => {
        dispatch({ type: 'ADD_FAVORITE', payload: id });
      });
    }
    
    // Load user properties from localStorage
    const savedProperties = localStorage.getItem('userProperties');
    if (savedProperties) {
      const userProperties = JSON.parse(savedProperties);
      userProperties.forEach((property: Property) => {
        dispatch({ type: 'ADD_PROPERTY', payload: property });
      });
    }
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
