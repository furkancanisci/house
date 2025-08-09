import React, { useState } from 'react';
import { ChevronLeft, ChevronRight, ImageIcon, Loader2 } from 'lucide-react';
import Lightbox from 'yet-another-react-lightbox';
import 'yet-another-react-lightbox/styles.css';
import { getRandomPropertyImage } from '../lib/imageUtils';

interface PropertyImageGalleryProps {
  images?: string[];
  alt: string;
  className?: string;
  containerClassName?: string;
  enableZoom?: boolean;
  showThumbnails?: boolean;
  showLoadingSpinner?: boolean;
}

// Utility function to fix image URLs
const fixImageUrl = (url: string | undefined): string => {
  if (!url) return getRandomPropertyImage();
  
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

const PropertyImageGallery: React.FC<PropertyImageGalleryProps> = ({
  images,
  alt,
  className = '',
  containerClassName = '',
  enableZoom = true,
  showThumbnails = true,
  showLoadingSpinner = true,
}) => {
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [lightboxIndex, setLightboxIndex] = useState(0);
  const [imageLoadStates, setImageLoadStates] = useState<Record<number, { loaded: boolean; error: boolean; loading: boolean }>>({});

  // Process images to ensure they're valid - handle undefined/null images
  const processedImages = (images && Array.isArray(images) && images.length > 0) 
    ? images.map(img => fixImageUrl(img))
    : [getRandomPropertyImage()];

  const currentImage = processedImages[currentImageIndex];
  const currentImageState = imageLoadStates[currentImageIndex] || { loaded: false, error: false, loading: true };

  const handlePrevImage = () => {
    setCurrentImageIndex((prev) => 
      prev === 0 ? processedImages.length - 1 : prev - 1
    );
  };

  const handleNextImage = () => {
    setCurrentImageIndex((prev) => 
      prev === processedImages.length - 1 ? 0 : prev + 1
    );
  };

  const handleImageLoad = (index: number) => {
    setImageLoadStates(prev => ({
      ...prev,
      [index]: { loaded: true, error: false, loading: false }
    }));
  };

  const handleImageError = (index: number) => {
    setImageLoadStates(prev => ({
      ...prev,
      [index]: { loaded: false, error: true, loading: false }
    }));
  };

  const handleImageClick = () => {
    if (enableZoom && currentImageState.loaded) {
      setLightboxIndex(currentImageIndex);
      setLightboxOpen(true);
    }
  };

  const handleThumbnailClick = (index: number) => {
    setCurrentImageIndex(index);
  };

  // Prepare slides for lightbox
  const lightboxSlides = processedImages.map((src, index) => ({
    src,
    alt: `${alt} - Image ${index + 1}`,
  }));

  return (
    <div className={`relative ${containerClassName}`}>
      {/* Main Image */}
      <div className="relative group overflow-hidden rounded-lg shadow-md border border-gray-200">
        {/* Loading Spinner */}
        {currentImageState.loading && showLoadingSpinner && (
          <div className="absolute inset-0 bg-gray-100 flex items-center justify-center z-10">
            <Loader2 className="w-8 h-8 text-gray-400 animate-spin" />
          </div>
        )}

        {/* Error State */}
        {currentImageState.error && (
          <div className="absolute inset-0 bg-gray-100 flex items-center justify-center z-10">
            <div className="text-center p-4">
              <ImageIcon className="w-12 h-12 text-gray-400 mx-auto mb-2" />
              <p className="text-sm text-gray-500">Image not available</p>
            </div>
          </div>
        )}

        <img
          src={currentImage}
          alt={alt}
          className={`w-full h-full object-cover transition-all duration-300 ${
            enableZoom && currentImageState.loaded ? 'cursor-pointer hover:scale-105' : ''
          } ${currentImageState.loading ? 'opacity-0' : 'opacity-100'} ${className}`}
          onLoad={() => handleImageLoad(currentImageIndex)}
          onError={() => handleImageError(currentImageIndex)}
          onClick={handleImageClick}
        />

        {/* Navigation Arrows (only show if multiple images) */}
        {processedImages.length > 1 && (
          <>
            <button
              onClick={handlePrevImage}
              className="absolute left-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full p-2 transition-all duration-200 opacity-0 group-hover:opacity-100"
              aria-label="Previous image"
            >
              <ChevronLeft className="w-5 h-5" />
            </button>
            <button
              onClick={handleNextImage}
              className="absolute right-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full p-2 transition-all duration-200 opacity-0 group-hover:opacity-100"
              aria-label="Next image"
            >
              <ChevronRight className="w-5 h-5" />
            </button>
          </>
        )}

        {/* Image Counter */}
        {processedImages.length > 1 && (
          <div className="absolute bottom-2 right-2 bg-black bg-opacity-60 text-white text-sm px-3 py-1 rounded-full">
            {currentImageIndex + 1} / {processedImages.length}
          </div>
        )}
      </div>

      {/* Thumbnails */}
      {showThumbnails && processedImages.length > 1 && (
        <div className="flex gap-3 mt-4 overflow-x-auto pb-2">
          {processedImages.map((image, index) => {
            const thumbnailState = imageLoadStates[index] || { loaded: false, error: false, loading: true };
            return (
              <button
                key={index}
                onClick={() => handleThumbnailClick(index)}
                className={`flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-all duration-200 shadow-sm ${
                  index === currentImageIndex
                    ? 'border-blue-500 opacity-100 shadow-md'
                    : 'border-gray-300 opacity-70 hover:opacity-100 hover:border-gray-400'
                }`}
              >
                <div className="relative w-full h-full">
                  {thumbnailState.loading && (
                    <div className="absolute inset-0 bg-gray-100 flex items-center justify-center">
                      <Loader2 className="w-4 h-4 text-gray-400 animate-spin" />
                    </div>
                  )}
                  {thumbnailState.error && (
                    <div className="absolute inset-0 bg-gray-100 flex items-center justify-center">
                      <ImageIcon className="w-4 h-4 text-gray-400" />
                    </div>
                  )}
                  <img
                    src={image}
                    alt={`${alt} thumbnail ${index + 1}`}
                    className={`w-full h-full object-cover transition-opacity duration-200 ${
                      thumbnailState.loading ? 'opacity-0' : 'opacity-100'
                    }`}
                    onLoad={() => handleImageLoad(index)}
                    onError={() => handleImageError(index)}
                  />
                </div>
              </button>
            );
          })}
        </div>
      )}

      {/* Lightbox */}
      <Lightbox
        open={lightboxOpen}
        close={() => setLightboxOpen(false)}
        slides={lightboxSlides}
        index={lightboxIndex}
        carousel={{ finite: processedImages.length === 1 }}
        on={{
          view: ({ index }) => setLightboxIndex(index),
        }}
      />
    </div>
  );
};

export default PropertyImageGallery;