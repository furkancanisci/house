export interface Property {
  // Core property fields
  id: string | number;
  title: string;
  address: string;
  price: number | string;
  priceType?: 'monthly' | 'yearly' | 'total' | 'fixed' | 'negotiable' | 'finalPrice' | 'folkSaying' | 'lastPrice' | {
    id: number;
    key: string;
    name_ar: string;
    name_en: string;
    name_ku: string;
    localized_name: string;
  };
  listingType: 'rent' | 'sale';
  propertyType: 'apartment' | 'house' | 'condo' | 'townhouse' | 'studio' | 'loft' | 'villa' | 'commercial' | 'land';
  bedrooms: number;
  bathrooms: number;
  squareFootage: number;
  description: string;
  city: string;
  state: string;
  zip_code: string;
  
  // Phase 1 Enhancement Fields
  floor_number?: number;
  total_floors?: number;
  balcony_count?: number;
  orientation?: string;
  view_type?: string;

  // Phase 2 Advanced Enhancement Fields
  building_age?: number;
  building_type?: string;
  floor_type?: string;
  window_type?: string;
  maintenance_fee?: number;
  deposit_amount?: number;
  annual_tax?: number;
  
  // Optional fields
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
  utilities?: string[];
  latitude?: number | string;
  longitude?: number | string;
  
  // Document type
  document_type_id?: number | string;
  documentType?: {
    id: number;
    name: string;
    description?: string;
    sort_order: number;
  };
  
  // For type safety with dynamic properties
  [key: string]: any;
}

export interface ExtendedProperty extends Omit<Property, 'property_type' | 'listing_type' | 'square_feet' | 'zip_code' | 'created_at'> {
  // Original property fields with overrides for consistent naming
  propertyType: string;
  listingType: 'rent' | 'sale';
  priceType?: 'monthly' | 'yearly' | 'total' | 'fixed' | 'negotiable' | 'finalPrice' | 'folkSaying' | 'lastPrice' | {
    id: number;
    key: string;
    name_ar: string;
    name_en: string;
    name_ku: string;
    localized_name: string;
  };
  squareFootage: number;
  zipCode: string;
  
  // Property details
  property_type?: string;
  listing_type?: 'rent' | 'sale';
  square_feet?: number;
  year_built?: number;
  price_per_square_foot?: number;
  lot_size?: number;
  garage?: boolean;
  pool?: boolean;
  air_conditioning?: boolean;
  heating?: string;
  hoa_fees?: number;
  mls_number?: string;
  virtual_tour_url?: string;
  featured?: boolean;
  status?: 'active' | 'available' | 'pending' | 'sold' | 'rented' | 'inactive';
  created_at?: string;
  updated_at?: string;
  
  // Location details - can be string or number to handle different API responses
  latitude?: number | string;
  longitude?: number | string;
  neighborhood?: string;
  county?: string;
  
  // Media
  media?: any[];
  main_image?: string;
  
  // Relationships
  user_id?: string | number;
  user?: User;
  
  // UI/Formatted fields
  formattedPrice?: string;
  formattedBeds?: string;
  formattedBaths?: string;
  formattedSquareFootage?: string;
  formattedAddress?: string;
  formattedPropertyType?: string;
  formattedDate?: string;
  
  // UI state
  isFavorite?: boolean;
  
  // Additional display properties
  mainImage?: string;
  images?: string[];
  
  // Details object for additional property information
  details?: {
    bedrooms?: number;
    bathrooms?: number;
    squareFootage?: number;
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
  priceType?: 'monthly' | 'yearly' | 'total' | 'fixed' | 'negotiable' | 'finalPrice' | 'folkSaying' | 'lastPrice' | 'all';
  currency?: string; // Added for currency filtering
  minPrice?: number;
  maxPrice?: number;
  bedrooms?: number;
  bathrooms?: number;
  minSquareFootage?: number;
  maxSquareFootage?: number;
  features?: string[];
  location?: string;
  state?: string; // Added for location filtering
  city?: string; // Added for location filtering
  
  // Phase 1 Enhancement Filters
  minFloorNumber?: number;
  maxFloorNumber?: number;
  minTotalFloors?: number;
  maxTotalFloors?: number;
  minBalconyCount?: number;
  maxBalconyCount?: number;
  orientation?: string;
  viewType?: string;

  // Phase 2 Advanced Enhancement Filters
  minBuildingAge?: number;
  maxBuildingAge?: number;
  buildingType?: string;
  floorType?: string;
  windowType?: string;
  minMaintenanceFee?: number;
  maxMaintenanceFee?: number;
  minDepositAmount?: number;
  maxDepositAmount?: number;
  minAnnualTax?: number;
  maxAnnualTax?: number;
  
  sortBy?: 'price' | 'date' | 'created_at' | 'squareFootage';
  sortOrder?: 'asc' | 'desc';
  searchQuery?: string; // Added to support search functionality
  search?: string; // For API compatibility
  // Map viewport for dynamic loading
  viewport?: {
    north: number;
    south: number;
    east: number;
    west: number;
    zoom: number;
  };
  selectedPropertyId?: string | number; // Currently selected property
  lastUpdated?: number; // Timestamp for forcing updates
  // Pagination
  page?: number;
  perPage?: number;
}

// Feature interface with multilingual support (Kurmanji dialect)
export interface Feature {
  id: number;
  name_ar: string;
  name_en: string;
  name_ku: string; // Kurmanji dialect
  description_ar?: string;
  description_en?: string;
  description_ku?: string; // Kurmanji dialect
  category?: string;
  category_label?: string;
  icon?: string;
  slug: string;
  sort_order?: number;
  is_active: boolean;
}

// Utility interface with multilingual support (Kurmanji dialect)
export interface Utility {
  id: number;
  name_ar: string;
  name_en: string;
  name_ku: string; // Kurmanji dialect
  description_ar?: string;
  description_en?: string;
  description_ku?: string; // Kurmanji dialect
  category?: string;
  category_label?: string;
  icon?: string;
  slug: string;
  sort_order?: number;
  is_active: boolean;
}

export interface PropertyFormData {
  title: string;
  address: string;
  price: number;
  listingType: 'rent' | 'sale';
  propertyType: 'apartment' | 'house' | 'condo' | 'townhouse' | 'studio' | 'loft' | 'villa' | 'commercial' | 'land';
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
  
  // Phase 1 Enhancement Fields
  floorNumber?: number;
  totalFloors?: number;
  balconyCount?: number;
  orientation?: string;
  viewType?: string;
  
  contact: {
    name: string;
    phone: string;
    email: string;
  };
}
