import api from './api';

// Utility function to fix image URLs with fallback support
const fixImageUrl = (url: string | null | undefined | any, fallbackType?: string): string => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api/v1', '') || 'https://api.besttrend-sy.com/';
  

  
  // Return appropriate placeholder if no URL is provided
  if (!url || typeof url !== 'string') {
    // For local development, use local placeholder
    if (baseUrl.includes('localhost')) {
      return '/images/placeholder-property.svg';
    }
    // For production, use production placeholder
    return '/images/placeholder-property.svg';
  }
  
  // If URL is already complete and correct, return as-is (MOST IMPORTANT FIX)
  if (url.startsWith('http://') || url.startsWith('https://')) {
    return url;
  }
  
  // Handle relative paths
  if (url.startsWith('/')) {
    const result = `${baseUrl}${url}`;

    return result;
  }
  
  // Handle storage paths
  if (url.startsWith('storage/')) {
    const result = `${baseUrl}/${url}`;

    return result;
  }
  
  // Default case - prepend base URL
  const result = `${baseUrl}/${url}`;

  return result;
};

// Utility function to fix image objects
const fixImageObject = (imageObj: any): any => {
  if (!imageObj) return null;
  
  if (typeof imageObj === 'string') {
    return fixImageUrl(imageObj);
  }
  
  if (typeof imageObj === 'object') {
    const fixed = { ...imageObj };
    if (fixed.url) fixed.url = fixImageUrl(fixed.url);
    if (fixed.thumb) fixed.thumb = fixImageUrl(fixed.thumb);
    if (fixed.medium) fixed.medium = fixImageUrl(fixed.medium);
    if (fixed.large) fixed.large = fixImageUrl(fixed.large);
    return fixed;
  }
  
  return imageObj;
};

export interface Property {
  id?: number;
  title: string;
  description: string;
  price: number;
  propertyType: string;
  listingType: string;
  address: string;
  city: string;
  state: string;
  postalCode: string;
  bedrooms: number;
  bathrooms: number;
  squareFootage: number;
  yearBuilt: number;
  amenities?: string[];
  images?: any;
  contactName?: string;
  contactPhone?: string;
  contactEmail?: string;
  latitude?: number;
  longitude?: number;
  availableDate?: string;
  petPolicy?: string;
  parking?: string;
  lotSize?: string;
  isAvailable?: boolean;
  status?: string;
  isFeatured?: boolean;
  slug?: string;
}

export interface PropertyFilters {
  listingType?: string;
  propertyType?: string;
  priceType?: string;
  minPrice?: number;
  maxPrice?: number;
  minBeds?: number | string;
  maxBeds?: number | string;
  minBaths?: number | string;
  maxBaths?: number | string;
  bedrooms?: number;
  bathrooms?: number;
  minSquareFeet?: number;
  maxSquareFeet?: number;
  minSquareFootage?: number;
  maxSquareFootage?: number;
  features?: string[];
  location?: string;
  city?: string;
  state?: string;
  latitude?: number;
  longitude?: number;
  radius?: number;
  sortBy?: string;
  sortOrder?: 'asc' | 'desc';
  page?: number;
  perPage?: number;
  limit?: number;
  search?: string;
  searchQuery?: string;
}

export const getProperties = async (params: PropertyFilters = {}) => {
  try {
    console.log('🚀 API Request params:', params);
    
    // Map frontend filters to backend API parameters
    const apiParams: any = {};
    
    // Search query
    if (params.search) {
      apiParams.search = params.search;
    }
    
    // Listing type (rent/sale)
    if (params.listingType) {
      apiParams.listing_type = params.listingType;
    }
    
    // Property type
    if (params.propertyType) {
      apiParams.property_type = params.propertyType;
    }
    
    // Price range
    if (params.minPrice !== undefined) {
      apiParams.min_price = params.minPrice;
    }
    if (params.maxPrice !== undefined) {
      apiParams.max_price = params.maxPrice;
    }
    
    // Bedrooms
    if (params.bedrooms !== undefined) {
      apiParams.bedrooms = params.bedrooms;
    }
    
    // Bathrooms
    if (params.bathrooms !== undefined) {
      apiParams.bathrooms = params.bathrooms;
    }
    
    // Square footage
    if (params.minSquareFootage !== undefined) {
      apiParams.min_square_footage = params.minSquareFootage;
    }
    if (params.maxSquareFootage !== undefined) {
      apiParams.max_square_footage = params.maxSquareFootage;
    }
    
    // Features
    if (params.features && params.features.length > 0) {
      apiParams.features = params.features;
    }
    
    // Location and radius
    if (params.latitude && params.longitude) {
      apiParams.latitude = params.latitude;
      apiParams.longitude = params.longitude;
      if (params.radius) {
        apiParams.radius = params.radius;
      }
    }
    
    // Pagination
    if (params.page) {
      apiParams.page = params.page;
    }
    if (params.perPage) {
      apiParams.per_page = params.perPage;
    }
    
    // Sorting
    if (params.sortBy) {
      // Map frontend sort options to backend
      const sortMapping: { [key: string]: string } = {
        'price_asc': 'price',
        'price_desc': 'price',
        'date_desc': 'created_at',
        'date_asc': 'created_at',
        'title_asc': 'title',
        'title_desc': 'title'
      };
      
      const sortField = sortMapping[params.sortBy] || 'created_at';
      const sortDirection = params.sortBy.includes('_desc') ? 'desc' : 'asc';
      
      apiParams.sort_by = sortField;
      apiParams.sort_direction = sortDirection;
    }
    
    const response = await api.get('/properties', { params: apiParams });
    
    console.log('📥 Raw API Response:', response.data);
    console.log('📊 Response structure:', {
      hasData: !!response.data.data,
      dataType: typeof response.data.data,
      isDataArray: Array.isArray(response.data.data),
      dataLength: response.data.data?.length,
      hasProperties: !!response.data.properties,
      propertiesType: typeof response.data.properties,
      isPropertiesArray: Array.isArray(response.data.properties)
    });
    
    // Handle different response structures
    let properties = [];
    
    if (response.data.data && Array.isArray(response.data.data)) {
      properties = response.data.data;
    } else if (response.data.properties && Array.isArray(response.data.properties)) {
      properties = response.data.properties;
    } else if (Array.isArray(response.data)) {
      properties = response.data;
    }
    
    console.log('🏠 Properties found:', properties.length);
    if (properties.length > 0) {
      console.log('🔍 All properties media info:', properties.map(p => ({
        id: p.id,
        title: p.title,
        hasMedia: !!p.media,
        mediaLength: p.media?.length || 0,
        hasImages: !!p.images,
        imagesKeys: p.images ? Object.keys(p.images) : []
      })));
    }
    
    // Fix image URLs in all properties
    const processedProperties = properties.map((property: any) => {
      console.log('🔧 Processing property:', {
        id: property.id,
        title: property.title,
        originalMainImage: property.mainImage,
        imagesObject: property.images
      });

      // Fix top-level mainImage field (MOST IMPORTANT)
      if (property.mainImage && property.mainImage !== '/images/placeholder-property.svg') {
        property.mainImage = fixImageUrl(property.mainImage);
        console.log('✅ Fixed mainImage:', property.mainImage);
      }

      if (property.images) {
        const fixedImages = { ...property.images };

        // Fix main image URL
        if (fixedImages.main && fixedImages.main !== '/images/placeholder-property.svg') {
          fixedImages.main = fixImageUrl(fixedImages.main);
          // Also set top-level mainImage if not already set
          if (!property.mainImage || property.mainImage === '/images/placeholder-property.svg') {
            property.mainImage = fixedImages.main;
            console.log('✅ Set mainImage from images.main:', property.mainImage);
          }
        }

        // Fix gallery image URLs
        if (fixedImages.gallery && Array.isArray(fixedImages.gallery)) {
          fixedImages.gallery = fixedImages.gallery.map(fixImageObject);
        }

        property.images = fixedImages;
      }

      // Fix video URLs in the property
      if (property.videos && Array.isArray(property.videos)) {
        property.videos = property.videos.map((video: any) => {
          if (video && typeof video === 'object' && video.url) {
            return {
              ...video,
              url: fixImageUrl(video.url)
            };
          }
          return video;
        });
      }

      console.log('✅ Final processed property:', {
        id: property.id,
        title: property.title,
        mainImage: property.mainImage
      });

      return property;
    });
    
    // Return data in the expected format for AppContext
    return {
      data: processedProperties,
      meta: response.data.meta || {},
      links: response.data.links || {},
      filters: response.data.filters || {}
    };
  } catch (error: any) {
    console.error('❌ API Error:', error);
    
    // Return fallback data instead of throwing
    return {
      data: [],
      meta: {},
      links: {},
      filters: {},
      error: 'فشل في تحميل العقارات. يرجى المحاولة مرة أخرى.'
    };
  }
};

export const getProperty = async (slugOrId: string) => {
  try {
    // Try to determine if it's a slug or ID
    const isNumericId = /^\d+$/.test(slugOrId);
    const endpoint = isNumericId ? `/properties/${slugOrId}/show` : `/properties/${slugOrId}`;
    

    const response = await api.get(endpoint);
    const property = response.data.property || response.data;
    

    
    // Fix image URLs in the property
    if (property && property.images) {
      const fixedImages = { ...property.images };
      
      // Fix main image URL
      if (fixedImages.main) {
        fixedImages.main = fixImageUrl(fixedImages.main);
      }
      
      // Fix gallery image URLs
      if (fixedImages.gallery && Array.isArray(fixedImages.gallery)) {
        fixedImages.gallery = fixedImages.gallery.map(fixImageObject);
      }
      
      property.images = fixedImages;
    }
    
    return property;
  } catch (error: any) {

    
    // Return null for not found properties
    if (error.response?.status === 404) {
      return null;
    }
    
    throw new Error('فشل في تحميل تفاصيل العقار. يرجى المحاولة مرة أخرى.');
  }
};

interface FeaturedPropertiesParams {
  limit?: number;
  search?: string;
  q?: string;
}

export const getFeaturedProperties = async (params: FeaturedPropertiesParams = {}) => {
  try {
    const { limit = 6, search, q } = params;
    const response = await api.get('/properties/featured', { 
      params: { 
        limit,
        search: search || q || undefined // Send search query if provided
      } 
    });
    
    // The backend returns a paginated response with data in response.data.data
    const properties = response.data.data || [];
    
    // Fix image URLs in all featured properties
    return properties.map((property: any) => {
      if (property.images) {
        const fixedImages = { ...property.images };
        
        // Fix main image URL
        if (fixedImages.main) {
          fixedImages.main = fixImageUrl(fixedImages.main);
        }
        
        // Fix gallery image URLs
        if (fixedImages.gallery && Array.isArray(fixedImages.gallery)) {
          fixedImages.gallery = fixedImages.gallery.map(fixImageObject);
        }
        
        property.images = fixedImages;
      }
      
      return property;
    });
  } catch (error: any) {

    
    // Return empty array as fallback
    return [];
  }
};

export const createProperty = async (propertyData: any) => {
  try {
    // Check if propertyData is already FormData
    let formData: FormData;
    
    if (propertyData instanceof FormData) {
      // If it's already FormData, use it directly
      formData = propertyData;
  
    } else {
      // If it's a plain object, convert it to FormData
      formData = new FormData();
      
      // Add all property data to FormData
      Object.keys(propertyData).forEach(key => {
        if (key === 'amenities' && Array.isArray(propertyData[key])) {
          // Handle amenities array
          propertyData[key].forEach((amenity: string, index: number) => {
            formData.append(`amenities[${index}]`, amenity);
          });
        } else if (key === 'images' && Array.isArray(propertyData[key])) {
          // Handle image files
          propertyData[key].forEach((file: File) => {
            formData.append('images[]', file);
          });
        } else if (key === 'mainImage' && propertyData[key] instanceof File) {
          // Handle main image file
          formData.append('main_image', propertyData[key]);
        } else if (propertyData[key] !== null && propertyData[key] !== undefined) {
          // Handle boolean values properly for Laravel validation
          if (typeof propertyData[key] === 'boolean') {
            formData.append(key, propertyData[key] ? '1' : '0');
          } else {
            formData.append(key, propertyData[key].toString());
          }
        }
      });
    }
    
    // Log FormData contents for debugging

    for (const pair of formData.entries()) {

    }
    

    const response = await api.post('/properties', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      timeout: 30000, // 30 second timeout
    });
    

    return response.data;
  } catch (error: any) {

    
    // Create a more detailed error object
    const enhancedError = new Error(error.message || 'Failed to create property');
    (enhancedError as any).response = error.response;
    throw enhancedError;
  }
};

export const updateProperty = async (id: number, propertyData: any) => {
  try {
    const formData = new FormData();
    
    // Add all property data to FormData
    Object.keys(propertyData).forEach(key => {
      if (key === 'amenities' && Array.isArray(propertyData[key])) {
        // Handle amenities array
        propertyData[key].forEach((amenity: string, index: number) => {
          formData.append(`amenities[${index}]`, amenity);
        });
      } else if (key === 'images' && Array.isArray(propertyData[key])) {
        // Handle new image files
        propertyData[key].forEach((file: File) => {
          formData.append('images[]', file);
        });
      } else if (key === 'mainImage' && propertyData[key] instanceof File) {
        // Handle main image file
        formData.append('main_image', propertyData[key]);
      } else if (key === 'imagesToRemove' && Array.isArray(propertyData[key])) {
        // Handle images to remove
        propertyData[key].forEach((imageId: string, index: number) => {
          formData.append(`remove_images[${index}]`, imageId);
        });
      } else if (propertyData[key] !== null && propertyData[key] !== undefined) {
        // Handle boolean values properly for Laravel validation
        if (typeof propertyData[key] === 'boolean') {
          formData.append(key, propertyData[key] ? '1' : '0');
        } else {
          formData.append(key, propertyData[key].toString());
        }
      }
    });
    

    const response = await api.put(`/properties/${id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  } catch (error) {

    throw error;
  }
};

export const deleteProperty = async (id: number) => {
  try {
    const response = await api.delete(`/properties/${id}`);
    return response.data;
  } catch (error) {

    throw error;
  }
};

export const toggleFavorite = async (id: number) => {
  try {

    
    // Get current token for debugging
    const token = localStorage.getItem('token');

    
    const response = await api.post(`/properties/${id}/favorite`);

    
    return response.data;
  } catch (error: any) {



    
    // If it's an auth error, clear local auth data
    if (error.response?.status === 401) {

      localStorage.removeItem('token');
      localStorage.removeItem('user');
    }
    
    throw error;
  }
};

export const getFavoriteProperties = async () => {
  try {
    const response = await api.get('/dashboard/favorites');
    const properties = response.data.data || [];
    
    // Fix image URLs in all favorite properties
    return properties.map((property: any) => {
      if (property.images) {
        const fixedImages = { ...property.images };
        
        // Fix main image URL
        if (fixedImages.main) {
          fixedImages.main = fixImageUrl(fixedImages.main);
        }
        
        // Fix gallery image URLs
        if (fixedImages.gallery && Array.isArray(fixedImages.gallery)) {
          fixedImages.gallery = fixedImages.gallery.map(fixImageObject);
        }
        
        property.images = fixedImages;
      }
      
      return property;
    });
  } catch (error: any) {

    // Return empty array as fallback
    return [];
  }
};

export const getPropertyAnalytics = async (id: number) => {
  try {
    const response = await api.get(`/properties/${id}/analytics`);
    return response.data;
  } catch (error) {

    throw error;
  }
};

export const getSimilarProperties = async (slug: string) => {
  try {
    const response = await api.get(`/properties/${slug}/similar`);
    const properties = response.data.data || [];
    
    // Fix image URLs in all similar properties
    return properties.map((property: any) => {
      if (property.images) {
        const fixedImages = { ...property.images };
        
        // Fix main image URL
        if (fixedImages.main) {
          fixedImages.main = fixImageUrl(fixedImages.main);
        }
        
        // Fix gallery image URLs
        if (fixedImages.gallery && Array.isArray(fixedImages.gallery)) {
          fixedImages.gallery = fixedImages.gallery.map(fixImageObject);
        }
        
        property.images = fixedImages;
      }
      
      return property;
    });
  } catch (error) {

    throw error;
  }
};

export const getAmenities = async () => {
  try {
    const response = await api.get('/properties/amenities');
    return response.data || [];
  } catch (error) {

    throw error;
  }
};