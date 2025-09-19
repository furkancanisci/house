import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { 
  Building, 
  Home, 
  MapPin, 
  Phone, 
  Mail, 
  Clock, 
  Users, 
  Award, 
  TrendingUp, 
  Star,
  CheckCircle,
  ArrowRight,
  Heart,
  Shield,
  Target,
  Zap,
  Loader2
} from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { Button } from '../components/ui/button';
import { Card, CardContent } from '../components/ui/card';
//import aboutUsService from '../services/aboutUsService';

const AboutUs: React.FC = () => {
  const { t, i18n } = useTranslation();
  const [aboutData, setAboutData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Fetch About Us data from backend
  useEffect(() => {
    const fetchAboutData = async () => {
      try {
        setLoading(true);
        const currentLanguage = i18n.language || 'en';
        //const data = await aboutUsService.getAboutUsData(currentLanguage);
        // For now, always use default data since API is commented out
        setAboutData(null); // This will trigger fallback to getDefaultAboutData()
        setError(null);
      } catch (err) {
  
        setError('Failed to load About Us data');
        setAboutData(null); // This will trigger fallback to getDefaultAboutData()
      } finally {
        setLoading(false);
      }
    };

    fetchAboutData();
  }, [i18n.language, t]); // Add 't' to dependencies to re-run when language changes

  // Default fallback data - use useMemo to recalculate when language changes
  const getDefaultAboutData = React.useMemo(() => ({
    hero: {
      title: t('about.hero.title') || 'About Afrin Houses',
      subtitle: t('about.hero.subtitle') || 'Your trusted partner in real estate',
      description: t('about.hero.description') || 'We are dedicated to helping you find your dream home with our extensive experience and personalized service.',
      image: '/images/about-hero.jpg'
    },
    stats: [
      {
        icon: 'Home',
        number: '1000+',
        label: t('about.stats.propertiesListed') || 'Properties Listed',
        color: 'text-primary-600',
      },
      {
        icon: 'Users',
        number: '500+',
        label: t('about.stats.happyClients') || 'Happy Clients',
        color: 'text-primary-700',
      },
      {
        icon: 'TrendingUp',
        number: '95%',
        label: t('about.stats.successRate') || 'Success Rate',
        color: 'text-primary-800',
      },
      {
        icon: 'Award',
        number: '10+',
        label: t('about.stats.yearsExperience') || 'Years Experience',
        color: 'text-primary-500',
      }
    ],
    values: [
      {
        icon: 'Shield',
        title: t('about.values.trust.title') || 'Trust & Integrity',
        description: t('about.values.trust.description') || 'We build lasting relationships based on honesty, transparency, and reliability.',
        color: 'from-blue-500 to-blue-700'
      },
      {
        icon: 'Target',
        title: t('about.values.excellence.title') || 'Excellence',
        description: t('about.values.excellence.description') || 'We strive for perfection in every service we provide to our clients.',
        color: 'from-green-500 to-green-700'
      },
      {
        icon: 'Heart',
        title: t('about.values.care.title') || 'Client Care',
        description: t('about.values.care.description') || 'Your satisfaction is our priority. We go above and beyond for every client.',
        color: 'from-red-500 to-red-700'
      },
      {
        icon: 'Zap',
        title: t('about.values.innovation.title') || 'Innovation',
        description: t('about.values.innovation.description') || 'We embrace technology to provide modern solutions for real estate needs.',
        color: 'from-purple-500 to-purple-700'
      }
    ],
    cta: {
      title: t('about.cta.title') || 'Ready to find your dream home?',
      description: t('about.cta.description') || 'Our team of experienced real estate agents is here to help you every step of the way.',
      contactButton: t('about.cta.contactButton') || 'Contact Us Today',
      browseButton: t('about.cta.browseButton') || 'Browse Properties'
    }
  }), [t, i18n.language]); // Dependencies for useMemo

  // Get icon component by name
  const getIconComponent = (iconName: string) => {
    const icons: { [key: string]: any } = {
      Home, Users, TrendingUp, Award, Shield, Target, Heart, Zap
    };
    return icons[iconName] || Home;
  };

  // Show loading state
  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <Loader2 className="h-12 w-12 animate-spin text-[#067977] mx-auto mb-4" />
          <p className="text-gray-600">{t('common.loading') || 'Loading...'}</p>
        </div>
      </div>
    );
  }

  // Get data with fallback
  const data = aboutData || getDefaultAboutData;
  const stats = data.stats || [];
  const values = data.values || [];
  const hero = data.hero || {};
  const cta = data.cta || {};

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Hero Section - Enhanced with modern design */}
      <section className="relative bg-gradient-to-br from-[#067977] via-[#067977] to-[#045a58] text-white py-20 lg:py-32 overflow-hidden">
        {/* Background decorative elements */}
        <div className="absolute inset-0 bg-black/10"></div>
        <div className="absolute top-0 left-0 w-full h-full">
          <div className="absolute top-10 left-10 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
          <div className="absolute bottom-10 right-10 w-48 h-48 bg-white/5 rounded-full blur-2xl"></div>
        </div>
        
        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <div className="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm font-medium mb-6 border border-white/30">
              <Building className="h-4 w-4 mr-2" />
              {t('about.hero.badge') || 'Your Trusted Real Estate Partner'}
            </div>
            <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
              {hero.title || t('about.hero.title') || 'About Us'}
            </h1>
            <p className="text-xl md:text-2xl text-white/90 max-w-4xl mx-auto mb-8 leading-relaxed">
              {hero.subtitle || t('about.hero.subtitle') || 'Your trusted partner in finding the perfect home. We connect buyers, sellers, and renters with quality properties and exceptional service.'}
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button 
                asChild
                size="lg"
                className="bg-white text-[#067977] hover:bg-gray-100 font-semibold px-8 py-3 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
              >
                <Link to="/contact">
                  {t('about.hero.contactUs') || 'Contact Us'}
                  <ArrowRight className="ml-2 h-5 w-5" />
                </Link>
              </Button>
              <Button 
                asChild
                variant="outline"
                size="lg"
                className="border-white text-[#067977]  hover:bg-white hover:text-[#067977] font-semibold px-8 py-3 rounded-lg transition-all duration-300 transform hover:-translate-y-1"
              >
                <Link to="/search">
                  {t('about.hero.viewProperties') || 'View Properties'}
                </Link>
              </Button>
            </div>
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-16 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              {t('about.stats.title') || 'Our Achievements'}
            </h2>
            <p className="text-lg text-gray-600 max-w-2xl mx-auto">
              {t('about.stats.subtitle') || 'Numbers that speak for our commitment to excellence'}
            </p>
          </div>
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-8">
            {stats.map((stat, index) => {
              const IconComponent = typeof stat.icon === 'string' ? getIconComponent(stat.icon) : stat.icon;
              return (
                <Card key={index} className="text-center p-6 border-0 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                  <CardContent className="p-0">
                    <div className={`inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-[#067977] to-[#045a58] text-white mb-4`}>
                      <IconComponent className="h-8 w-8" />
                    </div>
                    <div className="text-3xl font-bold text-gray-900 mb-2">{stat.number}</div>
                    <div className="text-sm font-medium text-gray-600">{stat.label}</div>
                  </CardContent>
                </Card>
              );
            })}
          </div>
        </div>
      </section>

      {/* Values Section */}
      <section className="py-16 bg-gray-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              {t('about.values.title') || 'Our Values'}
            </h2>
            <p className="text-lg text-gray-600 max-w-2xl mx-auto">
              {t('about.values.subtitle') || 'The principles that guide everything we do'}
            </p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {values.map((value, index) => {
              const IconComponent = typeof value.icon === 'string' ? getIconComponent(value.icon) : value.icon;
              return (
                <Card key={index} className="p-6 border-0 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 bg-white">
                  <CardContent className="p-0 text-center">
                    <div className={`inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br ${value.color} text-white mb-4`}>
                      <IconComponent className="h-8 w-8" />
                    </div>
                    <h3 className="text-xl font-bold text-gray-900 mb-3">{value.title}</h3>
                    <p className="text-gray-600 leading-relaxed">{value.description}</p>
                  </CardContent>
                </Card>
              );
            })}
          </div>
        </div>
      </section>





      {/* Call to Action */}
      <section className="py-16 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <Card className="bg-gradient-to-br from-[#067977] to-[#045a58] border-0 shadow-2xl overflow-hidden">
            <CardContent className="p-12 text-center text-white relative">
              {/* Background decorative elements */}
              <div className="absolute top-0 left-0 w-full h-full">
                <div className="absolute top-4 left-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
                <div className="absolute bottom-4 right-4 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
              </div>
              
              <div className="relative z-10">
                <h2 className="text-3xl md:text-4xl font-bold mb-4">
                  {cta.title || t('about.cta.title') || 'Ready to find your dream home?'}
                </h2>
                <p className="text-white/90 mb-8 max-w-2xl mx-auto text-lg leading-relaxed">
                  {cta.description || t('about.cta.description') || 'Our team of experienced real estate agents is here to help you every step of the way.'}
                </p>
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                  <Button 
                    asChild
                    size="lg"
                    className="bg-white text-[#067977] hover:bg-gray-100 font-semibold px-8 py-3 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
                  >
                    <Link to="/contact">
                      {cta.contactButton || t('about.cta.contactButton') || 'Contact Us Today'}
                      <ArrowRight className="ml-2 h-5 w-5" />
                    </Link>
                  </Button>
                  <Button 
                    asChild
                    variant="outline"
                    size="lg"
                    className="border-white text-[#067977] hover:bg-white hover:text-[#067977] font-semibold px-8 py-3 rounded-lg transition-all duration-300 transform hover:-translate-y-1"
                  >
                    <Link to="/search">
                      {cta.browseButton || t('about.cta.browseButton') || 'Browse Properties'}
                    </Link>
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </section>
    </div>
  );
};

export default AboutUs;
