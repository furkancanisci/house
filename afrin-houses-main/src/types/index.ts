export interface Property {
  id: string;
  slug: string;
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
  images: string[];
  mainImage: string;
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
  media?: Array<{
    id: number;
    url: string;
    type: string;
  }>;
  contact: {
    name: string;
    phone: string;
    email: string;
  };
  coordinates: {
    lat: number;
    lng: number;
  };
  datePosted: string;
}

// Extended property type with all possible fields
export interface ExtendedProperty extends Omit<Property, 'id' | 'address' | 'price' | 'propertyType' | 'listingType' | 'bedrooms' | 'bathrooms' | 'squareFootage' | 'features' | 'images' | 'mainImage' | 'contact' | 'datePosted'> {
  id: string | number;  // Allow both string and number IDs
  // Required fields from Property
  address: string;
  price: number;
  propertyType: string;
  listingType: 'rent' | 'sale';
  squareFootage: number;
  features: string[];
  images: string[];
  mainImage: string;
  contact: {
    name: string;
    phone: string;
    email: string;
  };
  datePosted: string;  // Now required to match base Property interface
  
  // Details object
  details: {
    bedrooms: number;
    bathrooms: number;
    square_feet?: number;
    [key: string]: any;
  };

  // Additional fields
  slug: string;
  // Backend fields (snake_case)
  property_type?: string;
  listing_type?: 'rent' | 'sale';
  square_feet?: number;
  year_built?: number;
  is_featured?: boolean;
  status?: string;
  created_at?: string;
  updated_at?: string;
  user_id?: number;
  media?: Array<{
    id: number;
    url: string;
    type: string;
  }>;
  
  // Aliases and computed fields
  beds?: number;
  baths?: number;
  sqft?: number;
  type?: string;
  city?: string;
  state?: string;
  postal_code?: string;
  latitude?: number;
  longitude?: number;
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
