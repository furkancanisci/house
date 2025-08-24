# Map Property Selector Documentation

## Overview

The Map Property Selector is a comprehensive React component that provides an interactive map interface for real estate property search and selection. It integrates Google Maps with advanced features like marker clustering, dynamic property loading, and visual property type distinction.

## Features

### 🗺️ Interactive Map
- **Google Maps Integration**: Powered by `@react-google-maps/api`
- **Responsive Design**: Adapts to different screen sizes
- **Multiple View Modes**: List, Map, and Split view
- **Gesture Handling**: Optimized for both desktop and mobile

### 🏠 Property Markers
- **Visual Distinction**: Different icons and colors for property types
  - **Apartments/Condos**: Building icon
  - **Houses/Villas**: House icon
  - **Commercial**: Office building icon
  - **Land**: Terrain icon
- **Listing Type Indicators**: 
  - Green markers with "$" badge for sale properties
  - Blue markers with "R" badge for rental properties
- **Interactive Markers**: Click, hover, and selection states

### 🔍 Advanced Clustering
- **MarkerClusterer Integration**: Handles high-density property areas
- **Custom Cluster Styles**: Three-tier clustering with different sizes and colors
- **Performance Optimized**: Reduces map clutter and improves performance

### 📍 Dynamic Loading
- **Viewport-Based Loading**: Properties load based on current map viewport
- **Throttled Updates**: Optimized performance with 500ms throttling
- **Bounds Filtering**: Automatic filtering based on visible map area

### 🎯 Property Selection
- **Enhanced Selection**: Click markers to select properties
- **Auto-Centering**: Map centers on selected property
- **Info Windows**: Detailed property information on selection
- **Event Propagation**: Selection events for external integration

## Component Architecture

### Props Interface

```typescript
interface MapSearchViewProps {
  properties: Property[];              // Array of properties to display
  onFiltersChange?: (filters: SearchFilters) => void;  // Filter change callback
  onPropertySelect?: (property: Property) => void;     // Property selection callback
  initialFilters?: SearchFilters;      // Initial filter state
  loading?: boolean;                   // Loading state
  onClose?: () => void;               // Close callback for modal mode
  fullScreen?: boolean;               // Full screen mode flag
}
```

### Key Dependencies

```json
{
  "@react-google-maps/api": "^2.x.x",
  "react": "^18.x.x",
  "react-i18next": "^13.x.x",
  "lodash": "^4.x.x",
  "lucide-react": "^0.x.x"
}
```

## Integration Guide

### 1. Basic Setup

```tsx
import MapSearchView from './components/MapSearchView';
import { Property, SearchFilters } from './types';

function App() {
  const [properties, setProperties] = useState<Property[]>([]);
  const [filters, setFilters] = useState<SearchFilters>({});

  const handleFiltersChange = (newFilters: SearchFilters) => {
    setFilters(newFilters);
    // Implement API call based on filters
    fetchProperties(newFilters);
  };

  const handlePropertySelect = (property: Property) => {
    console.log('Selected property:', property);
    // Handle property selection
  };

  return (
    <MapSearchView
      properties={properties}
      onFiltersChange={handleFiltersChange}
      onPropertySelect={handlePropertySelect}
      loading={loading}
    />
  );
}
```

### 2. Google Maps Provider Setup

Ensure your app is wrapped with the Google Maps provider:

```tsx
import { GoogleMapsProvider } from './context/GoogleMapsProvider';

function App() {
  return (
    <GoogleMapsProvider>
      <MapSearchView {...props} />
    </GoogleMapsProvider>
  );
}
```

### 3. Property Data Structure

Ensure your properties follow this structure:

```typescript
interface Property {
  id: string | number;
  title: string;
  address: string;
  price: number | string;
  listingType: 'rent' | 'sale';
  propertyType: 'apartment' | 'house' | 'condo' | 'townhouse' | 'studio' | 'loft' | 'villa' | 'commercial' | 'land';
  latitude: number | string;  // Required for map display
  longitude: number | string; // Required for map display
  bedrooms: number;
  bathrooms: number;
  squareFootage: number;
  // ... other fields
}
```

## API Integration

### Dynamic Property Loading

The component supports dynamic property loading based on viewport changes:

```typescript
const handleFiltersChange = async (filters: SearchFilters) => {
  if (filters.viewport) {
    // Load properties within viewport bounds
    const { north, south, east, west, zoom } = filters.viewport;
    
    const response = await fetch('/api/properties', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        bounds: { north, south, east, west },
        zoom,
        ...filters
      })
    });
    
    const properties = await response.json();
    setProperties(properties);
  }
};
```

### Backend API Recommendations

```javascript
// Express.js example
app.post('/api/properties', async (req, res) => {
  const { bounds, zoom, listingType, propertyType, minPrice, maxPrice } = req.body;
  
  let query = Property.find({
    latitude: { $gte: bounds.south, $lte: bounds.north },
    longitude: { $gte: bounds.west, $lte: bounds.east }
  });
  
  // Apply additional filters
  if (listingType && listingType !== 'all') {
    query = query.where('listingType').equals(listingType);
  }
  
  if (propertyType) {
    query = query.where('propertyType').equals(propertyType);
  }
  
  if (minPrice) {
    query = query.where('price').gte(minPrice);
  }
  
  if (maxPrice) {
    query = query.where('price').lte(maxPrice);
  }
  
  // Limit results based on zoom level for performance
  const limit = zoom > 15 ? 1000 : zoom > 12 ? 500 : 200;
  query = query.limit(limit);
  
  const properties = await query.exec();
  res.json(properties);
});
```

## Customization

### Marker Icons

Customize marker appearance by modifying the `getPropertyIcon` function:

```typescript
const getPropertyIcon = useCallback((property: Property) => {
  // Custom color scheme
  const colors = {
    sale: '#10B981',    // Green for sale
    rent: '#3B82F6',    // Blue for rent
    luxury: '#8B5CF6',  // Purple for luxury
  };
  
  // Custom icons based on price range
  const iconType = property.price > 1000000 ? 'luxury' : property.listingType;
  
  // Return custom icon configuration
}, []);
```

### Cluster Styles

Modify cluster appearance in the `clusterOptions`:

```typescript
const clusterOptions = {
  gridSize: 60,
  maxZoom: 15,
  minimumClusterSize: 2,
  styles: [
    // Small clusters
    {
      textColor: 'white',
      url: 'custom-cluster-small.svg',
      height: 40,
      width: 40,
      textSize: 12
    },
    // Medium clusters
    {
      textColor: 'white',
      url: 'custom-cluster-medium.svg',
      height: 50,
      width: 50,
      textSize: 14
    },
    // Large clusters
    {
      textColor: 'white',
      url: 'custom-cluster-large.svg',
      height: 60,
      width: 60,
      textSize: 16
    }
  ]
};
```

## Performance Optimization

### Best Practices

1. **Throttled Updates**: Map bounds changes are throttled to 500ms
2. **Property Caching**: Implement caching for frequently accessed properties
3. **Viewport Limiting**: Limit property count based on zoom level
4. **Marker Clustering**: Reduces DOM elements and improves performance
5. **Memoization**: Uses React.useMemo for expensive calculations

### Memory Management

```typescript
// Property cache implementation
const propertyCache = new Map<string, Property[]>();

const getCachedProperties = (cacheKey: string) => {
  return propertyCache.get(cacheKey);
};

const setCachedProperties = (cacheKey: string, properties: Property[]) => {
  // Limit cache size
  if (propertyCache.size > 10) {
    const firstKey = propertyCache.keys().next().value;
    propertyCache.delete(firstKey);
  }
  propertyCache.set(cacheKey, properties);
};
```

## Event System

### Available Events

1. **onFiltersChange**: Triggered when filters or viewport changes
2. **onPropertySelect**: Triggered when a property marker is clicked
3. **onClose**: Triggered when closing the map view

### Event Data Structure

```typescript
// Filter change event
interface FilterChangeEvent {
  filters: SearchFilters;
  viewport?: {
    north: number;
    south: number;
    east: number;
    west: number;
    zoom: number;
  };
  selectedPropertyId?: string | number;
  lastUpdated: number;
}

// Property selection event
interface PropertySelectEvent {
  property: Property;
  coordinates: { lat: number; lng: number };
  timestamp: number;
}
```

## Troubleshooting

### Common Issues

1. **Properties not showing**: Ensure latitude/longitude are valid numbers
2. **Map not loading**: Check Google Maps API key and network connectivity
3. **Clustering not working**: Verify MarkerClusterer is properly imported
4. **Performance issues**: Implement property limiting based on zoom level

### Debug Mode

Enable debug logging:

```typescript
const DEBUG = process.env.NODE_ENV === 'development';

if (DEBUG) {
  console.log('Properties loaded:', properties.length);
  console.log('Viewport bounds:', filters.viewport);
  console.log('Selected property:', selectedProperty);
}
```

## Browser Support

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## License

This component is part of the Afrin Houses project and follows the project's licensing terms.

---

**Last Updated**: December 2024
**Version**: 1.0.0
**Maintainer**: Development Team