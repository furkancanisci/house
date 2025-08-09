export interface Property {
  // Core property fields
  id: string | number;
  title: string;
  description: string;
  price: string | number;
  address: string;
  city: string;
  state: string;
  zip_code: string;
  country: string;
  
  // Property details
  property_type: string;
  listing_type: 'rent' | 'sale';
  bedrooms?: number;
  bathrooms?: number;
  square_feet?: number;
  year_built?: number;
  
  // Status and metadata
  status?: 'available' | 'pending' | 'sold' | 'rented';
  is_featured?: boolean;
  created_at?: string;
  updated_at?: string;
  user_id?: string | number;
  
  // Media
  media?: Array<{
    id: number;
    url: string;
    type: string;
    is_featured?: boolean;
  }>;
  
  // Additional fields
  slug?: string;
  features?: string[];
  latitude?: number | string;
  longitude?: number | string;
  
  // For type safety with dynamic properties
  [key: string]: any;
}

export interface ExtendedProperty extends Omit<Property, 'property_type' | 'listing_type' | 'square_feet' | 'zip_code' | 'created_at'> {
  // Original property fields with overrides for consistent naming
  propertyType: string;
  listingType: 'rent' | 'sale';
  squareFootage: number;
  zipCode: string;
  
  // Formatted display values
  formattedPrice: string;
  formattedBeds: string;
  formattedBaths: string;
  formattedSquareFootage: string;
  formattedAddress: string;
  formattedPropertyType: string;
  formattedDate: string;
  
  // UI state
  isFavorite: boolean;
  
  // Additional display properties
  mainImage?: string;
  images: string[];
  
  // Details object for additional property information
  details: {
    bedrooms: number;
    bathrooms: number;
    squareFootage: number;
    yearBuilt?: number;
    [key: string]: any;
  };
}

export interface User {
  id: string;
  name: string; // For backward compatibility
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  avatar: string | { url: string };
  properties: string[]; // Property IDs owned by user
  favorites: string[]; // Property IDs favorited by user
  is_verified: boolean;
  user_type: string;
  date_joined: string;
  created_at: string;
  [key: string]: any; // Allow additional properties from the API
}

export interface SearchFilters {
  listingType?: 'rent' | 'sale' | 'all';
  propertyType?: string;
  minPrice?: number;
  maxPrice?: number;
  bedrooms?: number;
  bathrooms?: number;
  minSquareFootage?: number;
  maxSquareFootage?: number;
  features?: string[];
  location?: string;
  sortBy?: 'price' | 'date' | 'created_at';
  sortOrder?: 'asc' | 'desc';
  searchQuery?: string; // Added to support search functionality
  search?: string; // For API compatibility
  // Pagination support
  page?: number;
  perPage?: number;
}

export interface PropertyFormData {
  title: string;
  address: string;
  price: number;
  listingType: 'rent' | 'sale';
  propertyType: 'apartment' | 'house' | 'condo' | 'townhouse';
  bedrooms: number;
  bathrooms: number;
  squareFootage: number;
  description: string;
  features: string[];
  yearBuilt: number;
  availableDate?: string;
  petPolicy?: string;
  parking?: string;
  utilities?: string;
  lotSize?: number;
  garage?: string;
  heating?: string;
  hoaFees?: string;
  building?: string;
  pool?: string;
  contact: {
    name: string;
    phone: string;
    email: string;
  };
}
