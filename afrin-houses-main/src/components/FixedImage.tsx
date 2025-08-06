import React, { useState, useEffect } from 'react';
import { ZoomIn, X } from 'lucide-react';

interface FixedImageProps extends React.ImgHTMLAttributes<HTMLImageElement> {
  src?: string;
  alt: string;
  enableZoom?: boolean;
  containerClassName?: string;
}

// Utility function to fix image URLs
const fixImageUrl = (url: string | undefined): string => {
  if (!url) return '/placeholder-property.jpg';
  
  // Don't process placeholder images or already processed URLs
  if (url.startsWith('/placeholder-property.jpg') || 
      url.startsWith('http://localhost:8000/') ||
      url.startsWith('https://') ||
      url.startsWith('data:')) {
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
  onClick,
  ...props 
}) => {
  const [hasError, setHasError] = useState(false);
  const [currentSrc, setCurrentSrc] = useState(() => fixImageUrl(src));
  const [isZoomed, setIsZoomed] = useState(false);
  const [imageLoaded, setImageLoaded] = useState(false);
  
  // Update src when prop changes
  useEffect(() => {
    if (!hasError) {
      setCurrentSrc(fixImageUrl(src));
      setImageLoaded(false);
    }
  }, [src, hasError]);

  // Handle ESC key to close zoom
  useEffect(() => {
    const handleEscKey = (event: KeyboardEvent) => {
      if (event.key === 'Escape' && isZoomed) {
        setIsZoomed(false);
      }
    };

    if (isZoomed) {
      document.addEventListener('keydown', handleEscKey);
      document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    return () => {
      document.removeEventListener('keydown', handleEscKey);
      document.body.style.overflow = 'unset';
    };
  }, [isZoomed]);
  
  const handleError = () => {
    if (!hasError && currentSrc !== '/placeholder-property.jpg') {
      setHasError(true);
      setCurrentSrc('/placeholder-property.jpg');
      setImageLoaded(false);
    }
  };

  const handleLoad = () => {
    setImageLoaded(true);
  };

  const handleImageClick = (e: React.MouseEvent) => {
    if (enableZoom && currentSrc !== '/placeholder-property.jpg' && imageLoaded) {
      e.preventDefault();
      e.stopPropagation();
      setIsZoomed(true);
    }
    if (onClick) {
      onClick(e);
    }
  };

  const handleCloseZoom = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setIsZoomed(false);
  };

  const isZoomable = enableZoom && currentSrc !== '/placeholder-property.jpg' && imageLoaded;

  const imageElement = (
    <img
      {...props}
      src={currentSrc}
      alt={alt}
      className={`${className} transition-all duration-300 ${isZoomable ? 'cursor-pointer zoom-hover-effect' : ''}`}
      onError={handleError}
      onLoad={handleLoad}
      onClick={handleImageClick}
    />
  );

  if (!enableZoom) {
    return imageElement;
  }

  return (
    <>
      <div className={`relative group overflow-hidden rounded-lg ${containerClassName}`}>
        {imageElement}
        {isZoomable && (
          <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-300 flex items-center justify-center rounded-lg">
            <div className="bg-white bg-opacity-20 backdrop-blur-custom rounded-full p-4 opacity-0 group-hover:opacity-100 transform scale-75 group-hover:scale-100 transition-all duration-300 shadow-lg">
              <ZoomIn className="text-white w-7 h-7 drop-shadow-lg" />
            </div>
          </div>
        )}
      </div>

      {/* Zoom Modal */}
      {isZoomed && (
        <div 
          className="fixed inset-0 bg-black bg-opacity-95 z-[9999] flex items-center justify-center p-4 animate-fade-in"
          onClick={handleCloseZoom}
        >
          <div className="relative max-w-[95vw] max-h-[95vh] animate-zoom-in">
            {/* Close Button */}
            <button
              onClick={handleCloseZoom}
              className="absolute -top-14 right-0 text-white hover:text-red-400 z-10 bg-black bg-opacity-60 hover:bg-opacity-80 rounded-full p-3 transition-all duration-200 backdrop-blur-custom shadow-lg animate-slide-up"
              aria-label="Close zoom view"
              style={{ animationDelay: '0.1s' }}
            >
              <X className="w-7 h-7" />
            </button>
            
            {/* Zoomed Image */}
            <div className="relative">
              <img
                src={currentSrc}
                alt={alt}
                className="max-w-full max-h-full object-contain rounded-xl shadow-2xl border border-white border-opacity-10"
                onClick={(e) => e.stopPropagation()}
                draggable={false}
              />
              
              {/* Loading indicator for large images */}
              <div className="absolute inset-0 bg-gray-900 bg-opacity-50 rounded-xl flex items-center justify-center opacity-0 transition-opacity duration-200">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
              </div>
            </div>
            
            {/* Image Info */}
            <div className="absolute -bottom-14 left-0 right-0 text-center animate-slide-up" style={{ animationDelay: '0.2s' }}>
              <p className="text-white text-sm bg-black bg-opacity-60 rounded-full px-6 py-3 inline-block backdrop-blur-custom shadow-lg max-w-md mx-auto truncate">
                {alt}
              </p>
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default FixedImage;