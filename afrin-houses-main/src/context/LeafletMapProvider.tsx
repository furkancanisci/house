import React, { ReactNode } from 'react';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

// Fix for default markers in react-leaflet
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

// Simple hook without context to avoid render2 error
export const useLeafletMap = () => {
  return {
    isLoaded: true,
    loadError: null,
  };
};

interface LeafletMapProviderProps {
  children: ReactNode;
}

export const LeafletMapProvider: React.FC<LeafletMapProviderProps> = ({ children }) => {
  // Simple wrapper without context
  return <>{children}</>;
};

// Export Leaflet for direct use in components
export { L };

// Common map configurations
export const DEFAULT_CENTER: [number, number] = [33.5138, 36.2765]; // Damascus, Syria
export const DEFAULT_ZOOM = 13;

// OpenStreetMap tile layer configuration
export const OSM_TILE_LAYER = {
  url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
  attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
};

// Alternative tile layers
export const TILE_LAYERS = {
  osm: {
    url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  },
  cartodb: {
    url: 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>'
  },
  satellite: {
    url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
    attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
  }
};

// Custom marker icons for different property types
export const createPropertyIcon = (type: string, listingType: 'rent' | 'sale') => {
  const color = listingType === 'sale' ? '#10B981' : '#3B82F6'; // Green for sale, Blue for rent
  const symbol = listingType === 'sale' ? '$' : 'R';
  
  const iconHtml = `
    <div style="
      background-color: ${color};
      width: 30px;
      height: 30px;
      border-radius: 50% 50% 50% 0;
      transform: rotate(-45deg);
      border: 2px solid white;
      box-shadow: 0 2px 4px rgba(0,0,0,0.3);
      display: flex;
      align-items: center;
      justify-content: center;
    ">
      <span style="
        color: white;
        font-weight: bold;
        font-size: 12px;
        transform: rotate(45deg);
      ">${symbol}</span>
    </div>
  `;
  
  return L.divIcon({
    html: iconHtml,
    className: 'custom-property-marker',
    iconSize: [30, 30],
    iconAnchor: [15, 30],
    popupAnchor: [0, -30]
  });
};

// Cluster icon creation function
export const createClusterIcon = (cluster: any) => {
  const count = cluster.getChildCount();
  let size = 'small';
  let color = '#3B82F6';
  
  if (count >= 100) {
    size = 'large';
    color = '#EF4444';
  } else if (count >= 10) {
    size = 'medium';
    color = '#F59E0B';
  }
  
  const iconSize = size === 'large' ? 50 : size === 'medium' ? 40 : 30;
  
  return L.divIcon({
    html: `<div style="
      background-color: ${color};
      color: white;
      border-radius: 50%;
      width: ${iconSize}px;
      height: ${iconSize}px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: ${iconSize > 40 ? '14px' : '12px'};
      border: 2px solid white;
      box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    ">${count}</div>`,
    className: 'custom-cluster-marker',
    iconSize: [iconSize, iconSize],
    iconAnchor: [iconSize / 2, iconSize / 2]
  });
};