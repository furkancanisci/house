import React, { useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Property } from '../services/propertyService';
import PropertyCard from '../components/PropertyCard';
import { 
  Search, 
  TrendingUp, 
  Users, 
  Award,
  ArrowRight,
  MapPin,
  Home as HomeIcon,
  Loader2
} from 'lucide-react';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Card, CardContent } from '../components/ui/card';
import { useTranslation } from 'react-i18next';
import { getProperties, getFeaturedProperties } from '../services/propertyService';

const Home: React.FC = () => {
  const navigate = useNavigate();
  const [searchQuery, setSearchQuery] = useState('');
  const [featuredProperties, setFeaturedProperties] = useState<Property[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const { t } = useTranslation();

  useEffect(() => {
    const fetchFeaturedProperties = async () => {
      try {
        setLoading(true);
        const data = await getFeaturedProperties();
        console.log('Featured properties response:', data);
        
        if (data && Array.isArray(data)) {
          setFeaturedProperties(data);
        } else {
          console.error('Unexpected data format from getFeaturedProperties:', data);
          setError(t('errors.invalidDataFormat'));
        }
      } catch (err) {
        console.error('Error fetching featured properties:', err);
        setError(t('errors.failedToLoadProperties'));
      } finally {
        setLoading(false);
      }
    };

    fetchFeaturedProperties();
  }, [t]);

  const handleSearch = () => {
    if (searchQuery.trim()) {
      navigate(`/search?q=${encodeURIComponent(searchQuery)}`);
    }
  };

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter') {
      handleSearch();
    }
  };

  const stats = [
    {
      icon: HomeIcon,
      number: featuredProperties.length.toString(),
      label: t('home.stats.propertiesListed'),
      color: 'text-blue-600',
    },
    {
      icon: Users,
      number: '500+',
      label: t('home.stats.happyCustomers'),
      color: 'text-green-600',
    },
    {
      icon: TrendingUp,
      number: '95%',
      label: t('home.stats.successRate'),
      color: 'text-purple-600',
    },
    {
      icon: Award,
      number: '10+',
      label: t('home.stats.yearsExperience'),
      color: 'text-orange-600',
    },
  ];

  const quickSearches = [
    t('home.quickSearches.downtownApartments'),
    t('home.quickSearches.familyHomes'),
    t('home.quickSearches.luxuryCondos'),
    t('home.quickSearches.petFriendly'),
    t('home.quickSearches.swimmingPool'),
    t('home.quickSearches.garageParking'),
  ];

  return (
    <div className="min-h-screen">
      {/* Hero Section */}
      <section className="relative bg-gradient-to-r from-blue-600 to-blue-800 text-white py-20">
        <div className="absolute inset-0 bg-black opacity-20"></div>
        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h1 className="text-4xl md:text-6xl font-bold mb-6">
            {t('home.hero.title')}
          </h1>
          <p className="text-xl md:text-2xl mb-8 max-w-3xl mx-auto">
            {t('home.hero.subtitle')}
          </p>
          
          {/* Search Bar */}
          <div className="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-2">
            <div className="flex">
              <Input
                type="text"
                placeholder={t('home.hero.searchPlaceholder')}
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                onKeyPress={handleKeyPress}
                className="flex-grow"
              />
              <Button onClick={handleSearch} className="ms-2">
                <Search className="h-4 w-4 mr-2" />
                {t('home.hero.searchButton')}
              </Button>
            </div>
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-12 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
            {stats.map((stat, index) => (
              <div key={index} className="text-center p-4 bg-white rounded-lg shadow">
                <stat.icon className={`h-8 w-8 mx-auto mb-2 ${stat.color}`} />
                <h3 className="text-2xl font-bold">{stat.number}</h3>
                <p className="text-gray-600">{stat.label}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Featured Properties */}
      <section className="py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center mb-8">
            <h2 className="text-2xl font-bold text-gray-900">
              {t('home.featuredProperties')}
            </h2>
            <Link to="/search" className="text-blue-600 hover:underline flex items-center">
              {t('home.viewAll')} <ArrowRight className="h-4 w-4 ml-1" />
            </Link>
          </div>

          {loading ? (
            <div className="flex justify-center items-center py-12">
              <Loader2 className="h-8 w-8 animate-spin text-primary" />
            </div>
          ) : error ? (
            <div className="bg-red-50 border-l-4 border-red-500 p-4 my-4">
              <div className="flex">
                <div className="flex-shrink-0">
                  <svg className="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clipRule="evenodd" />
                  </svg>
                </div>
                <div className="ml-3">
                  <p className="text-sm text-red-700">
                    {error}
                  </p>
                </div>
              </div>
            </div>
          ) : (
            <>
              {featuredProperties.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  {featuredProperties.map((property) => {
                    // Safely extract price, handling both number and object formats
                    const price = typeof property.price === 'object' 
                      ? (property.price as any)?.amount || 0 
                      : Number(property.price) || 0;
                    
                    // Map property to ExtendedProperty interface
                    const extendedProperty = {
                      id: property.id,
                      slug: (property as any).slug || `property-${property.id}`,
                      title: property.title || 'No Title',
                      address: (property as any).address || `${property.city || ''} ${property.state || ''}`.trim(),
                      price: price,
                      listingType: (property.listing_type === 'rent' || property.listing_type === 'sale' 
                        ? property.listing_type 
                        : 'sale') as 'rent' | 'sale',
                      propertyType: property.property_type || 'apartment',
                      details: {
                        bedrooms: property.bedrooms || 0,
                        bathrooms: property.bathrooms || 0
                      },
                      squareFootage: property.square_feet || 0,
                      description: property.description || '',
                      features: (property as any).features || [],
                      images: Array.isArray(property.media) 
                        ? property.media.map((m: any) => m.url || '').filter(Boolean)
                        : [],
                      mainImage: Array.isArray(property.media) && property.media[0]?.url 
                        ? property.media[0].url 
                        : '',
                      coordinates: {
                        lat: property.latitude || 0,
                        lng: property.longitude || 0
                      },
                      contact: {
                        name: '', // These should come from the API or user context
                        phone: '',
                        email: ''
                      },
                      datePosted: property.created_at || new Date().toISOString(),
                      // Add any other fields required by ExtendedProperty
                      yearBuilt: (property as any).year_built || new Date().getFullYear(),
                      availableDate: (property as any).available_date || '',
                      petPolicy: (property as any).pet_policy || '',
                      parking: (property as any).parking || '',
                      utilities: (property as any).utilities || '',
                      lotSize: (property as any).lot_size || '',
                      garage: (property as any).garage || '',
                      heating: (property as any).heating || '',
                      hoaFees: (property as any).hoa_fees || '',
                      building: (property as any).building || '',
                      pool: (property as any).pool || ''
                    };
                    
                    return (
                      <PropertyCard 
                        key={property.id} 
                        property={extendedProperty}
                        view="grid" 
                      />
                    );
                  })}
                </div>
              ) : (
                <div className="text-center py-12">
                  <p className="text-gray-500">{t('home.noPropertiesFound')}</p>
                </div>
              )}
            </>
          )}

          {/* Quick Search Tags */}
          <div className="mt-12">
            <h3 className="text-lg font-medium text-gray-900 mb-4">
              {t('home.quickSearches.title')}
            </h3>
            <div className="flex flex-wrap gap-2">
              {quickSearches.map((term, index) => (
                <button
                  key={index}
                  onClick={() => {
                    setSearchQuery(term);
                    navigate(`/search?q=${encodeURIComponent(term)}`);
                  }}
                  className="px-4 py-2 bg-white border border-gray-200 rounded-full text-sm font-medium hover:bg-gray-50 transition-colors"
                >
                  {term}
                </button>
              ))}
            </div>
          </div>
        </div>
      </section>
    </div>
  );
};

export default Home;
