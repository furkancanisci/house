import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { MapPin, ChevronDown, X } from 'lucide-react';
import { Button } from './ui/button';
import { Label } from './ui/label';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from './ui/select';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from './ui/card';
import { Badge } from './ui/badge';

interface District {
  id: string;
  name: string;
  nameAr: string;
}

interface City {
  name: string;
  nameAr: string;
  districts: District[];
}

interface Governorate {
  name: string;
  nameAr: string;
  cities: Record<string, City>;
}

type SyrianLocations = Record<string, Governorate>;

interface GeographicFiltersProps {
  onLocationChange?: (location: {
    governorate?: string;
    city?: string;
    district?: string;
  }) => void;
  initialLocation?: {
    governorate?: string;
    city?: string;
    district?: string;
  };
  className?: string;
}

// Syrian governorates, cities, and districts data
const syrianLocations: SyrianLocations = {
  'damascus': {
    name: 'Damascus',
    nameAr: 'دمشق',
    cities: {
      'damascus-city': {
        name: 'Damascus City',
        nameAr: 'مدينة دمشق',
        districts: [
          { id: 'old-damascus', name: 'Old Damascus', nameAr: 'دمشق القديمة' },
          { id: 'mezzeh', name: 'Mezzeh', nameAr: 'المزة' },
          { id: 'malki', name: 'Malki', nameAr: 'المالكي' },
          { id: 'abu-rummaneh', name: 'Abu Rummaneh', nameAr: 'أبو رمانة' },
          { id: 'qassaa', name: 'Qassaa', nameAr: 'القصاع' },
          { id: 'shaalan', name: 'Shaalan', nameAr: 'الشعلان' },
          { id: 'muhajreen', name: 'Muhajreen', nameAr: 'المهاجرين' },
          { id: 'salihiyeh', name: 'Salihiyeh', nameAr: 'الصالحية' }
        ]
      },
      'douma': {
        name: 'Douma',
        nameAr: 'دوما',
        districts: [
          { id: 'douma-center', name: 'Douma Center', nameAr: 'مركز دوما' },
          { id: 'harasta', name: 'Harasta', nameAr: 'حرستا' }
        ]
      }
    }
  },
  'aleppo': {
    name: 'Aleppo',
    nameAr: 'حلب',
    cities: {
      'aleppo-city': {
        name: 'Aleppo City',
        nameAr: 'مدينة حلب',
        districts: [
          { id: 'old-aleppo', name: 'Old Aleppo', nameAr: 'حلب القديمة' },
          { id: 'aziziyeh', name: 'Aziziyeh', nameAr: 'العزيزية' },
          { id: 'sulaymaniyeh', name: 'Sulaymaniyeh', nameAr: 'السليمانية' },
          { id: 'furqan', name: 'Furqan', nameAr: 'الفرقان' },
          { id: 'hamdaniyeh', name: 'Hamdaniyeh', nameAr: 'الحمدانية' },
          { id: 'new-aleppo', name: 'New Aleppo', nameAr: 'حلب الجديدة' }
        ]
      },
      'afrin': {
        name: 'Afrin',
        nameAr: 'عفرين',
        districts: [
          { id: 'afrin-center', name: 'Afrin Center', nameAr: 'مركز عفرين' },
          { id: 'jandairis', name: 'Jandairis', nameAr: 'جنديرس' },
          { id: 'rajo', name: 'Rajo', nameAr: 'راجو' },
          { id: 'bulbul', name: 'Bulbul', nameAr: 'بلبل' },
          { id: 'mabata', name: 'Mabata', nameAr: 'معبطلي' },
          { id: 'shaykh-hadid', name: 'Shaykh Hadid', nameAr: 'الشيخ حديد' }
        ]
      },
      'azaz': {
        name: 'Azaz',
        nameAr: 'أعزاز',
        districts: [
          { id: 'azaz-center', name: 'Azaz Center', nameAr: 'مركز أعزاز' },
          { id: 'mare', name: 'Mare', nameAr: 'مارع' },
          { id: 'tel-rifaat', name: 'Tel Rifaat', nameAr: 'تل رفعت' }
        ]
      }
    }
  },
  'homs': {
    name: 'Homs',
    nameAr: 'حمص',
    cities: {
      'homs-city': {
        name: 'Homs City',
        nameAr: 'مدينة حمص',
        districts: [
          { id: 'old-homs', name: 'Old Homs', nameAr: 'حمص القديمة' },
          { id: 'waer', name: 'Waer', nameAr: 'الوعر' },
          { id: 'karm-luz', name: 'Karm al-Luz', nameAr: 'كرم اللوز' },
          { id: 'inshaat', name: 'Inshaat', nameAr: 'الإنشاءات' }
        ]
      }
    }
  },
  'hama': {
    name: 'Hama',
    nameAr: 'حماة',
    cities: {
      'hama-city': {
        name: 'Hama City',
        nameAr: 'مدينة حماة',
        districts: [
          { id: 'hama-center', name: 'Hama Center', nameAr: 'مركز حماة' },
          { id: 'mahatta', name: 'Mahatta', nameAr: 'المحطة' }
        ]
      }
    }
  },
  'lattakia': {
    name: 'Lattakia',
    nameAr: 'اللاذقية',
    cities: {
      'lattakia-city': {
        name: 'Lattakia City',
        nameAr: 'مدينة اللاذقية',
        districts: [
          { id: 'lattakia-center', name: 'Lattakia Center', nameAr: 'مركز اللاذقية' },
          { id: 'raml-shamali', name: 'Raml Shamali', nameAr: 'الرمل الشمالي' },
          { id: 'raml-janubi', name: 'Raml Janubi', nameAr: 'الرمل الجنوبي' }
        ]
      },
      'jableh': {
        name: 'Jableh',
        nameAr: 'جبلة',
        districts: [
          { id: 'jableh-center', name: 'Jableh Center', nameAr: 'مركز جبلة' }
        ]
      }
    }
  },
  'tartus': {
    name: 'Tartus',
    nameAr: 'طرطوس',
    cities: {
      'tartus-city': {
        name: 'Tartus City',
        nameAr: 'مدينة طرطوس',
        districts: [
          { id: 'tartus-center', name: 'Tartus Center', nameAr: 'مركز طرطوس' }
        ]
      },
      'banias': {
        name: 'Banias',
        nameAr: 'بانياس',
        districts: [
          { id: 'banias-center', name: 'Banias Center', nameAr: 'مركز بانياس' }
        ]
      }
    }
  },
  'idlib': {
    name: 'Idlib',
    nameAr: 'إدلب',
    cities: {
      'idlib-city': {
        name: 'Idlib City',
        nameAr: 'مدينة إدلب',
        districts: [
          { id: 'idlib-center', name: 'Idlib Center', nameAr: 'مركز إدلب' }
        ]
      },
      'ariha': {
        name: 'Ariha',
        nameAr: 'أريحا',
        districts: [
          { id: 'ariha-center', name: 'Ariha Center', nameAr: 'مركز أريحا' }
        ]
      }
    }
  },
  'daraa': {
    name: 'Daraa',
    nameAr: 'درعا',
    cities: {
      'daraa-city': {
        name: 'Daraa City',
        nameAr: 'مدينة درعا',
        districts: [
          { id: 'daraa-center', name: 'Daraa Center', nameAr: 'مركز درعا' }
        ]
      }
    }
  },
  'as-suwayda': {
    name: 'As-Suwayda',
    nameAr: 'السويداء',
    cities: {
      'as-suwayda-city': {
        name: 'As-Suwayda City',
        nameAr: 'مدينة السويداء',
        districts: [
          { id: 'as-suwayda-center', name: 'As-Suwayda Center', nameAr: 'مركز السويداء' }
        ]
      }
    }
  },
  'quneitra': {
    name: 'Quneitra',
    nameAr: 'القنيطرة',
    cities: {
      'quneitra-city': {
        name: 'Quneitra City',
        nameAr: 'مدينة القنيطرة',
        districts: [
          { id: 'quneitra-center', name: 'Quneitra Center', nameAr: 'مركز القنيطرة' }
        ]
      }
    }
  },
  'raqqa': {
    name: 'Raqqa',
    nameAr: 'الرقة',
    cities: {
      'raqqa-city': {
        name: 'Raqqa City',
        nameAr: 'مدينة الرقة',
        districts: [
          { id: 'raqqa-center', name: 'Raqqa Center', nameAr: 'مركز الرقة' }
        ]
      }
    }
  },
  'deir-ez-zor': {
    name: 'Deir ez-Zor',
    nameAr: 'دير الزور',
    cities: {
      'deir-ez-zor-city': {
        name: 'Deir ez-Zor City',
        nameAr: 'مدينة دير الزور',
        districts: [
          { id: 'deir-ez-zor-center', name: 'Deir ez-Zor Center', nameAr: 'مركز دير الزور' }
        ]
      }
    }
  },
  'al-hasakah': {
    name: 'Al-Hasakah',
    nameAr: 'الحسكة',
    cities: {
      'al-hasakah-city': {
        name: 'Al-Hasakah City',
        nameAr: 'مدينة الحسكة',
        districts: [
          { id: 'al-hasakah-center', name: 'Al-Hasakah Center', nameAr: 'مركز الحسكة' }
        ]
      },
      'qamishli': {
        name: 'Qamishli',
        nameAr: 'القامشلي',
        districts: [
          { id: 'qamishli-center', name: 'Qamishli Center', nameAr: 'مركز القامشلي' }
        ]
      }
    }
  }
};

const GeographicFilters: React.FC<GeographicFiltersProps> = ({
  onLocationChange,
  initialLocation = {},
  className = ''
}) => {
  const { t, i18n } = useTranslation();
  const isArabic = i18n.language === 'ar';
  
  const [selectedGovernorate, setSelectedGovernorate] = useState<string>(initialLocation.governorate || '');
  const [selectedCity, setSelectedCity] = useState<string>(initialLocation.city || '');
  const [selectedDistrict, setSelectedDistrict] = useState<string>(initialLocation.district || '');
  const [selectedLocations, setSelectedLocations] = useState<string[]>([]);

  // Get available cities based on selected governorate
  const availableCities = selectedGovernorate && syrianLocations[selectedGovernorate]
    ? Object.entries(syrianLocations[selectedGovernorate].cities)
    : [];

  // Get available districts based on selected city
  const availableDistricts = selectedGovernorate && selectedCity ? (() => {
    const governorate = syrianLocations[selectedGovernorate];
    if (!governorate) return [];
    const city = governorate.cities[selectedCity];
    return city ? city.districts : [];
  })() : [];

  // Handle governorate change
  const handleGovernorateChange = (value: string) => {
    setSelectedGovernorate(value);
    setSelectedCity('');
    setSelectedDistrict('');
    
    onLocationChange?.({
      governorate: value,
      city: '',
      district: ''
    });
  };

  // Handle city change
  const handleCityChange = (value: string) => {
    setSelectedCity(value);
    setSelectedDistrict('');
    
    onLocationChange?.({
      governorate: selectedGovernorate,
      city: value,
      district: ''
    });
  };

  // Handle district change
  const handleDistrictChange = (value: string) => {
    setSelectedDistrict(value);
    
    onLocationChange?.({
      governorate: selectedGovernorate,
      city: selectedCity,
      district: value
    });
  };

  // Add location to selected list
  const addLocation = () => {
    if (!selectedGovernorate) return;
    
    const governorateName = isArabic 
      ? syrianLocations[selectedGovernorate].nameAr
      : syrianLocations[selectedGovernorate].name;
    
    let locationString = governorateName;
    
    if (selectedCity) {
      const governorate = syrianLocations[selectedGovernorate];
      const cityData = governorate.cities[selectedCity];
      if (cityData) {
        const cityName = isArabic ? cityData.nameAr : cityData.name;
        locationString += ` - ${cityName}`;
        
        if (selectedDistrict) {
          const districtData = cityData.districts.find(d => d.id === selectedDistrict);
          if (districtData) {
            const districtName = isArabic ? districtData.nameAr : districtData.name;
            locationString += ` - ${districtName}`;
          }
        }
      }
    }
    
    if (!selectedLocations.includes(locationString)) {
      const newLocations = [...selectedLocations, locationString];
      setSelectedLocations(newLocations);
    }
  };

  // Remove location from selected list
  const removeLocation = (location: string) => {
    const newLocations = selectedLocations.filter(loc => loc !== location);
    setSelectedLocations(newLocations);
  };

  // Clear all selections
  const clearAll = () => {
    setSelectedGovernorate('');
    setSelectedCity('');
    setSelectedDistrict('');
    setSelectedLocations([]);
    
    onLocationChange?.({
      governorate: '',
      city: '',
      district: ''
    });
  };

  return (
    <Card className={className}>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <MapPin className="h-5 w-5" />
          {t('filters.geographic.title')}
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        {/* Governorate Selection */}
        <div className="space-y-2">
          <Label htmlFor="governorate">{t('filters.geographic.governorate')}</Label>
          <Select value={selectedGovernorate} onValueChange={handleGovernorateChange}>
            <SelectTrigger>
              <SelectValue placeholder={t('filters.geographic.selectGovernorate')} />
            </SelectTrigger>
            <SelectContent>
              {Object.entries(syrianLocations).map(([key, governorate]) => (
                <SelectItem key={key} value={key}>
                  {isArabic ? governorate.nameAr : governorate.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>

        {/* City Selection */}
        {selectedGovernorate && (
          <div className="space-y-2">
            <Label htmlFor="city">{t('filters.geographic.city')}</Label>
            <Select value={selectedCity} onValueChange={handleCityChange}>
              <SelectTrigger>
                <SelectValue placeholder={t('filters.geographic.selectCity')} />
              </SelectTrigger>
              <SelectContent>
                {availableCities.map(([key, city]) => (
                  <SelectItem key={key} value={key}>
                    {isArabic ? city.nameAr : city.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        )}

        {/* District Selection */}
        {selectedCity && availableDistricts.length > 0 && (
          <div className="space-y-2">
            <Label htmlFor="district">{t('filters.geographic.district')}</Label>
            <Select value={selectedDistrict} onValueChange={handleDistrictChange}>
              <SelectTrigger>
                <SelectValue placeholder={t('filters.geographic.selectDistrict')} />
              </SelectTrigger>
              <SelectContent>
                {availableDistricts.map((district) => (
                  <SelectItem key={district.id} value={district.id}>
                    {isArabic ? district.nameAr : district.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        )}

        {/* Add Location Button */}
        {selectedGovernorate && (
          <Button onClick={addLocation} className="w-full">
            {t('filters.geographic.addLocation')}
          </Button>
        )}

        {/* Selected Locations */}
        {selectedLocations.length > 0 && (
          <div className="space-y-2">
            <Label>{t('filters.geographic.selectedLocations')}</Label>
            <div className="flex flex-wrap gap-2">
              {selectedLocations.map((location, index) => (
                <Badge key={index} variant="secondary" className="flex items-center gap-1">
                  {location}
                  <button
                    onClick={() => removeLocation(location)}
                    className="ml-1 hover:bg-gray-200 rounded-full p-0.5"
                  >
                    <X className="h-3 w-3" />
                  </button>
                </Badge>
              ))}
            </div>
          </div>
        )}

        {/* Clear All Button */}
        {(selectedGovernorate || selectedLocations.length > 0) && (
          <Button variant="outline" onClick={clearAll} className="w-full">
            <X className="h-4 w-4 mr-2" />
            {t('filters.geographic.clearAll')}
          </Button>
        )}
      </CardContent>
    </Card>
  );
};

export default GeographicFilters;