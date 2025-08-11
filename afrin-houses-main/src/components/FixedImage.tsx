import React, { useState, useEffect } from 'react';
import { ImageIcon, Loader2 } from 'lucide-react';
import Lightbox from 'yet-another-react-lightbox';
import 'yet-another-react-lightbox/styles.css';
import { getRandomPropertyImage } from '../lib/imageUtils';

interface FixedImageProps extends React.ImgHTMLAttributes<HTMLImageElement> {
  src?: string;
  alt: string;
  enableZoom?: boolean;
  containerClassName?: string;
  showLoadingSpinner?: boolean;
  fallbackClassName?: string;
  propertyId?: string | number;
}

// Utility function to fix image URLs
const fixImageUrl = (url: string | undefined | null | any, propertyId?: string | number): string => {
  // Check if url is not a string or is empty
  if (!url || typeof url !== 'string') return getRandomPropertyImage(propertyId);
  
  // Don't process already processed URLs
  if (url.startsWith('http://localhost:8000/') ||
      url.startsWith('https://') ||
      url.startsWith('data:') ||
      url.startsWith('/images/')) {
    return url;
  }
  
  // Replace localhost URLs with localhost:8000
  if (url.startsWith('http://localhost/')) {
    return url.replace('http://localhost/', 'http://localhost:8000/');
  }
  
  return url;
};

const FixedImage: React.FC<FixedImageProps> = ({ 
  src, 
  alt, 
  enableZoom = true, 
  containerClassName = '',
  className = '',
  showLoadingSpinner = true,
  fallbackClassName = '',
  propertyId,
  onClick,
  ...props 
}) => {
  const [hasError, setHasError] = useState(false);
  const [currentSrc, setCurrentSrc] = useState(() => fixImageUrl(src, propertyId));
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [imageLoaded, setImageLoaded] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  
  // Update src when prop changes
  useEffect(() => {
    if (!hasError) {
      setCurrentSrc(fixImageUrl(src, propertyId));
      setImageLoaded(false);
      setIsLoading(true);
    }
  }, [src, hasError, propertyId]);

  const handleError = () => {
    if (!hasError && !currentSrc.startsWith('/images/properties/')) {
      setHasError(true);
      setCurrentSrc(getRandomPropertyImage(propertyId));
      setImageLoaded(false);
      setIsLoading(true);
    }
  };

  const handleLoad = () => {
    setImageLoaded(true);
    setIsLoading(false);
  };

  const handleImageClick = (e: React.MouseEvent<HTMLImageElement>) => {
    if (enableZoom && imageLoaded) {
      e.preventDefault();
      e.stopPropagation();
      setLightboxOpen(true);
    }
    if (onClick) {
      onClick(e);
    }
  };

  const isZoomable = enableZoom && imageLoaded;

  // If image failed to load and we can't get a fallback
  if (hasError && currentSrc.startsWith('/images/properties/')) {
    return (
      <div className={`${className} ${containerClassName} ${fallbackClassName} bg-gray-100 border border-gray-200 rounded-lg shadow-sm flex items-center justify-center`}>
        <div className="text-center p-4">
          <ImageIcon className="w-8 h-8 text-gray-400 mx-auto mb-2" />
          <p className="text-sm text-gray-500">Image not available</p>
        </div>
      </div>
    );
  }

  return (
    <div className={`relative overflow-hidden rounded-lg shadow-sm border border-gray-200 ${containerClassName}`}>
      {/* Loading Spinner */}
      {isLoading && showLoadingSpinner && (
        <div className="absolute inset-0 bg-gray-100 flex items-center justify-center z-10">
          <Loader2 className="w-6 h-6 text-gray-400 animate-spin" />
        </div>
      )}
      
      <img
        {...props}
        src={currentSrc}
        alt={alt}
        className={`${className} transition-all duration-300 ${isZoomable ? 'cursor-pointer hover:scale-105 hover:shadow-lg' : ''} ${isLoading ? 'opacity-0' : 'opacity-100'}`}
        onError={handleError}
        onLoad={handleLoad}
        onClick={handleImageClick}
      />

      {/* Lightbox */}
      {enableZoom && (
        <Lightbox
          open={lightboxOpen}
          close={() => setLightboxOpen(false)}
          slides={[
            {
              src: currentSrc,
              alt: alt,
            },
          ]}
          carousel={{ finite: true }}
          render={{
            buttonPrev: () => null,
            buttonNext: () => null,
          }}
        />
      )}
    </div>
  );
};

export default FixedImage;