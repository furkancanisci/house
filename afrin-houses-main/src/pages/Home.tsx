import React, { useEffect, useState, useRef } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Property } from '../services/propertyService';
import { ExtendedProperty, SearchFilters } from '../types';
import PropertyCard from '../components/PropertyCard';
// MapSearchView moved to separate page
import { 
  Search, 
  TrendingUp, 
  Users, 
  Award,
  ArrowRight,
  ArrowLeft,
  MapPin,
  Home as HomeIcon,
  Loader2,
  ChevronLeft,
  ChevronRight
} from 'lucide-react';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Card, CardContent } from '../components/ui/card';
import { useTranslation } from 'react-i18next';
import { getProperties, getFeaturedProperties } from '../services/propertyService';
import { getHomeStats, getLocalizedLabel, HomeStat } from '../services/homeStatsService';
import { processPropertyImages } from '../lib/imageUtils';
import leftTopOrnament from '../assets/left top_bb.png';
import rightBottomOrnament from '../assets/right_bottom_bb.png';

const Home: React.FC = () => {
  const navigate = useNavigate();
  const { t, i18n } = useTranslation();
  const [searchQuery, setSearchQuery] = useState('');
  const [rentalProperties, setRentalProperties] = useState<Property[]>([]);
  const [saleProperties, setSaleProperties] = useState<Property[]>([]);
  const [trendingProperties, setTrendingProperties] = useState<Property[]>([]);
  const [featuredProperties, setFeaturedProperties] = useState<Property[]>([]);
  const [allProperties, setAllProperties] = useState<Property[]>([]);
  const [homeStats, setHomeStats] = useState<HomeStat[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  // showMapSearch state removed - now using separate page
  const [searchFilters, setSearchFilters] = useState<SearchFilters>({
    searchQuery: '',
    listingType: 'all',
    propertyType: 'all',
    location: '',
    bedrooms: undefined,
    bathrooms: undefined
  });

  // Function to fetch home statistics
  const fetchHomeStats = async () => {
    try {
      const stats = await getHomeStats();
      setHomeStats(stats);
    } catch (error) {
      
    }
  };

  useEffect(() => {
    const fetchPropertiesByCategory = async () => {
      try {
        setLoading(true);

        // Fetch home statistics
        await fetchHomeStats();

        // Get search query from URL if present
        const searchParams = new URLSearchParams(window.location.search);
        const searchQuery = searchParams.get('q') || '';

        // If there's a search query, update the search input
        if (searchQuery) {
          setSearchQuery(searchQuery);
        }
        
        // Fetch all properties in a single request with higher limit
        const response = await getProperties({
          limit: 50, // Get more properties for the grid
          sortBy: 'createdAt',
          sortOrder: 'desc'
        });
        
        // Fetch featured properties
        const featuredResponse = await getFeaturedProperties({
          limit: 10
        });
        


        
        // Handle the response structure - getProperties might return an array or an object with data
        const allProps = Array.isArray(response) ? response : (response?.data || []);
        const featuredProps = Array.isArray(featuredResponse) ? featuredResponse : (featuredResponse?.data || []);
        
        if (!Array.isArray(allProps)) {

          return;
        }
        
        // Filter properties by type for individual sections
        const rental = allProps
          .filter(p => (p.listingType === 'rent' || (p as any).listing_type === 'rent'))
          .slice(0, 3);
          
        const sale = allProps
          .filter(p => (p.listingType === 'sale' || (p as any).listing_type === 'sale'))
          .slice(0, 3);
          
        const trending = allProps.slice(0, 3); // Most recent properties as trending
        
        setRentalProperties(rental);
        setSaleProperties(sale);
        setTrendingProperties(trending);
        setFeaturedProperties(featuredProps);
        
        // Use all properties for the grid
        setAllProperties(allProps);
        
      } catch (err) {

        setError(t('errors.failedToLoadProperties'));
      } finally {
        setLoading(false);
      }
    };

    fetchPropertiesByCategory();
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


  // Helper function to create ExtendedProperty from Property
  const createExtendedProperty = (property: Property): ExtendedProperty => {
    // Safely extract price, handling both number and object formats
    const price = typeof property.price === 'object' && property.price !== null
      ? (property as any).price?.amount || (property as any).price
      : property.price;
    
    // Map property to ExtendedProperty interface
    const listingType = (property.listingType === 'sale' || property.listingType === 'rent' || 
                      (property as any).listing_type === 'sale' || (property as any).listing_type === 'rent')
      ? (property.listingType || (property as any).listing_type || 'sale') as 'sale' | 'rent'
      : 'sale';
    
    // Extract values from property and details object
    const propertyAny = property as any;
    const details = propertyAny.details || {};
    
    // Get values with fallbacks - ensure we're getting numbers
    const bedrooms = Number(propertyAny.bedrooms || details.bedrooms || 0);
    const bathrooms = Number(propertyAny.bathrooms || details.bathrooms || 0);
    const squareFeet = Number(propertyAny.square_feet || details.square_feet || propertyAny.squareFootage || 0);
    const yearBuilt = Number(propertyAny.year_built || details.year_built || new Date().getFullYear());
    
    // Process images to ensure we have proper images
    const { mainImage, images } = processPropertyImages(propertyAny, propertyAny.property_type || 'apartment');
    
    // Create the extended property object with all required fields
    return {
      ...propertyAny,
      id: propertyAny.id?.toString() || '',
      slug: propertyAny.slug || `property-${propertyAny.id || ''}`,
      address: propertyAny.street_address || propertyAny.address || '',
      price: price,
      listingType: listingType,
      listing_type: listingType,
      propertyType: propertyAny.property_type || 'apartment',
      bedrooms: bedrooms,
      bathrooms: bathrooms,
      square_feet: squareFeet,
      squareFootage: squareFeet,
      year_built: yearBuilt,
      details: {
        ...details,
        bedrooms: bedrooms,
        bathrooms: bathrooms,
        square_feet: squareFeet,
        year_built: yearBuilt
      },
      description: property.description || '',
      features: (property as any).features || [],
      images: images,
      mainImage: mainImage,
      coordinates: {
        lat: property.latitude || 0,
        lng: property.longitude || 0
      },
      contact: {
        name: '',
        phone: '',
        email: ''
      },
      datePosted: (property as any).created_at || new Date().toISOString(),
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
      pool: (property as any).pool || '',
      isFavorite: false,
      distance: '',
      matchScore: 0
    };
  };

  // Map icon names to actual icon components
  const iconMap: { [key: string]: React.ComponentType<any> } = {
    HomeIcon: HomeIcon,
    Users: Users,
    TrendingUp: TrendingUp,
    Award: Award,
    Search: Search,
    MapPin: MapPin,
    Home: HomeIcon,
    User: Users,
    Chart: TrendingUp,
    Trophy: Award,
  };

  // Generate stats array from API data with fallback to default values
  const stats = homeStats.length > 0 ? homeStats.map((stat) => ({
    icon: iconMap[stat.icon] || HomeIcon,
    number: stat.number,
    label: getLocalizedLabel(stat, i18n.language),
    color: stat.color || 'text-primary-600',
  })) : [
    // Fallback stats if API fails
    {
      icon: HomeIcon,
      number: (rentalProperties.length + saleProperties.length + trendingProperties.length).toString(),
      label: t('home.stats.propertiesListed'),
      color: 'text-primary-600',
    },
    {
      icon: Users,
      number: '500+',
      label: t('home.stats.happyCustomers'),
      color: 'text-primary-700',
    },
    {
      icon: TrendingUp,
      number: '95%',
      label: t('home.stats.successRate'),
      color: 'text-primary-800',
    },
    {
      icon: Award,
      number: '10+',
      label: t('home.stats.yearsExperience'),
      color: 'text-primary-500',
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

  // Property Grid Component with Auto-Scrolling
  const PropertyGrid: React.FC<{
    allProperties: Property[];
    featuredProperties: Property[];
    createExtendedProperty: (property: Property) => ExtendedProperty;
  }> = ({ allProperties, featuredProperties, createExtendedProperty }) => {
    const scrollRefs = [useRef<HTMLDivElement>(null), useRef<HTMLDivElement>(null), useRef<HTMLDivElement>(null)];
    
    // Shuffle array function
    const shuffleArray = (array: Property[]) => {
      const shuffled = [...array];
      for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
      }
      return shuffled;
    };

    // Create rows - first row is featured properties if available
    const firstRowProperties = featuredProperties.length > 0 ? featuredProperties : 
                             (allProperties.length > 0 ? allProperties.slice(0, Math.min(5, allProperties.length)) : []);
    
    const remainingProperties = allProperties.filter(p => !firstRowProperties.includes(p));
    const propertiesPerRow = Math.max(1, Math.ceil(remainingProperties.length / 2));
    
    const rows = [
      firstRowProperties,
      remainingProperties.slice(0, propertiesPerRow),
      remainingProperties.slice(propertiesPerRow)
    ];

    // Auto-scroll functionality
    useEffect(() => {
      const intervals = scrollRefs.map((ref, index) => {
        return setInterval(() => {
          if (ref.current) {
            const container = ref.current;
            const cardWidth = container.children[0]?.clientWidth || 300;
            const gap = 24; // 1.5rem gap
            const scrollAmount = cardWidth + gap;
            
            if (container.scrollLeft + container.clientWidth >= container.scrollWidth - 10) {
              // Reset to beginning
              container.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
              // Scroll to next item
              container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
          }
        }, 10000); // 10 seconds
      });

      return () => {
        intervals.forEach(interval => clearInterval(interval));
      };
    }, []);

    const rowTitles = [
      featuredProperties.length > 0 ? (t('home.sections.featured') || 'Featured Properties') : (t('home.sections.recommended') || 'Recommended for You'),
      t('home.sections.trending') || 'Trending Now', 
      t('home.sections.popularChoices') || 'Popular Choices'
    ];
    
    const rowSubtitles = [
      featuredProperties.length > 0 ? t('home.sections.featuredSubtitle') : t('home.sections.recommendedSubtitle'),
      t('home.sections.trendingSubtitle'),
      t('home.sections.popularChoicesSubtitle')
    ];
    const rowIcons = [HomeIcon, TrendingUp, Award];
    const rowColors = ['text-primary-600', 'text-primary-700', 'text-primary-800'];

    return (
      <div className="space-y-12">
        {rows.map((rowProperties, rowIndex) => (
          <div key={rowIndex} className="">
            <div className="mb-8">
              <div className="flex justify-between items-start mb-3">
                <div className="flex items-center">
                  <div className={`p-2 rounded-lg bg-gradient-to-br from-primary-100 to-primary-200 mr-3`}>
                    {React.createElement(rowIcons[rowIndex], { 
                      className: `h-6 w-6 ${rowColors[rowIndex]}` 
                    })}
                  </div>
                  <div>
                    <h3 className="text-2xl font-bold text-gray-900">{rowTitles[rowIndex]}</h3>
                    <p className="text-gray-600 text-sm mt-1">{rowSubtitles[rowIndex]}</p>
                  </div>
                </div>
                <Link to="/search" className={`${rowColors[rowIndex]} hover:opacity-80 hover:underline flex items-center text-sm font-medium px-4 py-2 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors`}>
                  {t('common.viewAll') || 'View All'} <ArrowRight className="h-4 w-4 ml-1" />
                </Link>
              </div>
            </div>
            
            <div className="relative group">
              {/* Left scroll button */}
              <button
                onClick={() => {
                  const container = scrollRefs[rowIndex].current;
                  if (container) {
                    container.scrollBy({ left: -300, behavior: 'smooth' });
                  }
                }}
                className="absolute left-2 top-1/2 -translate-y-1/2 z-20 bg-gradient-to-r from-white to-gray-50 hover:from-primary-50 hover:to-primary-100 shadow-xl hover:shadow-2xl rounded-full p-3 opacity-0 group-hover:opacity-100 transition-all duration-500 ease-out transform hover:scale-110 hover:-translate-x-1 border border-gray-200 hover:border-primary-300"
              >
                <ChevronLeft className="h-6 w-6 text-gray-700 hover:text-primary-600 transition-colors duration-300" />
              </button>
              
              {/* Right scroll button */}
              <button
                onClick={() => {
                  const container = scrollRefs[rowIndex].current;
                  if (container) {
                    container.scrollBy({ left: 300, behavior: 'smooth' });
                  }
                }}
                className="absolute right-2 top-1/2 -translate-y-1/2 z-20 bg-gradient-to-l from-white to-gray-50 hover:from-primary-50 hover:to-primary-100 shadow-xl hover:shadow-2xl rounded-full p-3 opacity-0 group-hover:opacity-100 transition-all duration-500 ease-out transform hover:scale-110 hover:translate-x-1 border border-gray-200 hover:border-primary-300"
              >
                <ChevronRight className="h-6 w-6 text-gray-700 hover:text-primary-600 transition-colors duration-300" />
              </button>
              
              <div 
                ref={scrollRefs[rowIndex]}
                className="flex gap-4 sm:gap-6 overflow-x-auto scrollbar-hide pb-4 property-grid-container scroll-smooth hover:scroll-auto transition-all duration-300"
                style={{
                  scrollbarWidth: 'none',
                  msOverflowStyle: 'none'
                } as React.CSSProperties}
              >
                {rowProperties.length > 0 ? (
                  rowProperties.map((property) => (
                    <div key={property.id} className="flex-none w-72 sm:w-80 md:w-96 property-grid-card">
                      <PropertyCard 
                        property={createExtendedProperty(property)}
                        view="grid"
                        useGallery={true}
                      />
                    </div>
                  ))
                ) : (
                  <div className="w-full text-center py-8">
                    <p className="text-gray-500">{t('home.noPropertiesFound')}</p>
                  </div>
                )}
              </div>
              
              {/* Enhanced custom scrollbar with progress indicator */}
              <div className="mt-4 h-3 bg-gradient-to-r from-gray-100 to-gray-200 rounded-full overflow-hidden relative shadow-inner">
                <div 
                  className={`h-full bg-gradient-to-r ${rowColors[rowIndex].replace('text-', 'from-').replace('-600', '-400').replace('-700', '-500').replace('-800', '-600')} ${rowColors[rowIndex].replace('text-', 'to-').replace('-600', '-600').replace('-700', '-700').replace('-800', '-800')} rounded-full scroll-indicator transition-all duration-700 ease-out shadow-lg`}
                  style={{ width: '20%' }}
                ></div>
                <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent rounded-full"></div>
              </div>
            </div>
          </div>
        ))}
      </div>
    );
  };

  return (
    <div className="min-h-screen">
      {/* Hero Section */}
      <section className="relative bg-gradient-to-r from-primary-600 to-primary-800 text-white py-20">
        <div className="absolute inset-0 bg-[#067977] opacity-20"></div>
        {/* Decorative ornaments */}
        <div className="absolute top-0 left-0 w-44 h-44 md:w-60 md:h-60 opacity-100">
          <img 
            src={leftTopOrnament} 
            alt="Decorative ornament" 
            className="w-full h-full object-contain drop-shadow-lg"
           
          />
        </div>
        <div className="absolute bottom-0 right-0 w-44 h-44 md:w-60 md:h-60 opacity-100  -mr-2">
          <img 
            src={rightBottomOrnament} 
            alt="Decorative ornament" 
            className="w-full h-full object-contain drop-shadow-lg"
            
          />
        </div>
        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h1 className="hero-title text-4xl md:text-6xl font-bold mb-6">
            {t('home.hero.title')}
          </h1>
          <p className="hero-subtitle text-xl md:text-2xl mb-8 max-w-3xl mx-auto">
            {t('home.hero.subtitle')}
          </p>
          
          {/* Search Bar */}
          <div className="max-w-3xl mx-auto bg-white rounded-lg shadow-lg p-2">
            <div className="flex flex-col sm:flex-row gap-2">
              <div className="flex flex-1">
                <Input
                  type="text"
                  placeholder={t('home.hero.searchPlaceholder')}
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  onKeyPress={handleKeyPress}
                  className="flex-grow"
                />
                <Button onClick={handleSearch} className="ms-2 professional-button focus-enhanced">
                  <Search className="h-4 w-4 mr-2" />
                  {t('home.hero.searchButton')}
                </Button>
              </div>
              <Button 
                onClick={() => navigate('/search/map')}
                variant="outline"
                className="bg-white border-primary-600 text-primary-600 hover:bg-primary-50 professional-button focus-enhanced"
              >
                <MapPin className="h-4 w-4 mr-2" />
                {t('home.hero.searchByMap')}
              </Button>
            </div>
          </div>
        </div>
      </section>

      {/* Map Search View moved to separate page /search/map */}

      {/* Stats Section */}
      <section className="stats-section py-16 bg-gradient-to-br from-primary-50 to-primary-100 relative overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-r from-primary-600/5 to-primary-800/5"></div>
        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="section-title text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              {t('home.stats.sectionTitle') || 'Why Choose Our Platform'}
            </h2>
            <p className="section-subtitle text-xl text-gray-600 max-w-3xl mx-auto">
              {t('home.stats.sectionSubtitle') || 'Trusted by thousands of property seekers and agents across the region'}
            </p>
          </div>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-6">
            {stats.map((stat, index) => (
              <div key={index} className="stats-card enhanced-card text-center p-3 md:p-6 bg-white rounded-lg md:rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-primary-100">
                <div className="bg-gradient-to-br from-primary-500 to-primary-700 rounded-full w-10 h-10 md:w-16 md:h-16 mx-auto mb-2 md:mb-4 flex items-center justify-center">
                  <stat.icon className="h-5 w-5 md:h-8 md:w-8 text-white" />
                </div>
                <h3 className="text-xl md:text-3xl font-bold text-gray-900 mb-1 md:mb-2">{stat.number}</h3>
                <p className="text-xs md:text-base text-gray-600 font-medium">{stat.label}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Premium Listings */}
      <section className="py-16 bg-gradient-to-b from-white to-primary-50/30">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              {t('home.listings.sectionTitle') || 'Discover Premium Properties'}
            </h2>
            <p className="text-xl text-gray-600 max-w-3xl mx-auto mb-8">
              {t('home.listings.sectionSubtitle') || 'Explore our carefully curated selection of exceptional properties, from luxury apartments to family homes'}
            </p>
            <div className="flex justify-center space-x-4 mb-8">
              <Link 
                to="/houses-for-rent" 
                className="px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg hover:from-primary-700 hover:to-primary-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
              >
                {t('home.listings.viewRentals') || 'View Rentals'}
              </Link>
              <Link 
                to="/houses-for-sale" 
                className="px-6 py-3 bg-gradient-to-r from-primary-700 to-primary-800 text-white rounded-lg hover:from-primary-800 hover:to-primary-900 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
              >
                {t('home.listings.viewSales') || 'View Sales'}
              </Link>
            </div>
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
            <PropertyGrid 
              allProperties={allProperties}
              featuredProperties={featuredProperties}
              createExtendedProperty={createExtendedProperty}
            />
          )}

          {/* Quick Search Tags */}
          <div className="mt-16 bg-gradient-to-r from-primary-600/10 to-primary-800/10 rounded-xl p-8">
            <div className="text-center mb-6">
              <h3 className="text-2xl font-bold text-gray-900 mb-2">
                {t('home.quickSearches.title') || 'Popular Searches'}
              </h3>
              <p className="text-gray-600">
                {t('home.quickSearches.subtitle') || 'Find your ideal property with these trending searches'}
              </p>
            </div>
            <div className="flex flex-wrap justify-center gap-3">
              {quickSearches.map((term, index) => (
                <button
                  key={index}
                  onClick={() => {
                    setSearchQuery(term);
                    navigate(`/search?q=${encodeURIComponent(term)}`);
                  }}
                  className="px-6 py-3 bg-white border-2 border-primary-200 rounded-full text-sm font-medium hover:bg-primary-50 hover:border-primary-300 transition-all duration-300 shadow-sm hover:shadow-md transform hover:-translate-y-0.5 text-gray-700 hover:text-primary-700"
                >
                  {term}
                </button>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-16 bg-gradient-to-br from-primary-600 to-primary-800 text-white relative overflow-hidden">
        <div className="absolute inset-0 bg-black opacity-10"></div>
        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl md:text-4xl font-bold mb-4">
              {t('home.features.sectionTitle') || 'Why Choose Our Platform'}
            </h2>
            <p className="text-xl opacity-90 max-w-3xl mx-auto">
              {t('home.features.sectionSubtitle') || 'Experience the future of real estate with our advanced features designed for modern property seekers'}
            </p>
          </div>
          
          <div className="grid md:grid-cols-3 gap-8">
            <div className="relative text-center p-6 bg-white/10 backdrop-blur-sm rounded-xl border border-white/20 hover:bg-white/15 transition-all duration-300">
              <div className="absolute top-3 right-3 bg-gradient-to-r from-yellow-400 to-orange-500 text-xs font-bold text-white px-3 py-1 rounded-full shadow-lg">
                {t('common.comingSoon') || 'Coming Soon'}
              </div>
              <div className="w-16 h-16 mx-auto mb-4 bg-white/20 rounded-full flex items-center justify-center">
                <Search className="h-8 w-8 text-white" />
              </div>
              <h3 className="text-xl font-bold mb-3">
                {t('home.features.smartSearch.title') || 'Smart Search'}
              </h3>
              <p className="opacity-90">
                {t('home.features.smartSearch.description') || 'Advanced AI-powered search that understands your preferences and finds the perfect match'}
              </p>
            </div>

            <div className="text-center p-6 bg-white/10 backdrop-blur-sm rounded-xl border border-white/20 hover:bg-white/15 transition-all duration-300">
              <div className="w-16 h-16 mx-auto mb-4 bg-white/20 rounded-full flex items-center justify-center">
                <MapPin className="h-8 w-8 text-white" />
              </div>
              <h3 className="text-xl font-bold mb-3">
                {t('home.features.mapView.title') || 'Interactive Map'}
              </h3>
              <p className="opacity-90">
                {t('home.features.mapView.description') || 'Explore properties with our interactive map featuring neighborhoods, amenities, and transport links'}
              </p>
            </div>

            <div className="text-center p-6 bg-white/10 backdrop-blur-sm rounded-xl border border-white/20 hover:bg-white/15 transition-all duration-300">
              <div className="w-16 h-16 mx-auto mb-4 bg-white/20 rounded-full flex items-center justify-center">
                <Award className="h-8 w-8 text-white" />
              </div>
              <h3 className="text-xl font-bold mb-3">
                {t('home.features.verifiedListings.title') || 'Verified Listings'}
              </h3>
              <p className="opacity-90">
                {t('home.features.verifiedListings.description') || 'All properties are verified by our team to ensure accuracy and authenticity'}
              </p>
            </div>
          </div>

          <div className="text-center mt-12">
            <Link 
              to="/about" 
              className="inline-flex items-center px-8 py-4 bg-white text-primary-700 rounded-lg font-semibold hover:bg-gray-50 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
            >
              {t('home.features.learnMore') || 'Learn More About Us'}
              <ArrowRight className="h-5 w-5 ml-2" />
            </Link>
          </div>
        </div>
      </section>
    </div>
  );
};

export default Home;
