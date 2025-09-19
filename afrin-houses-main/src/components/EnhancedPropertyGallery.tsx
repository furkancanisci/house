import React, { useState } from 'react';
import {
  ChevronLeft,
  ChevronRight,
  X,
  ZoomIn,
  ZoomOut,
  Download,
  Share2,
  Heart,
  Grid3x3,
  Maximize2
} from 'lucide-react';
import { Button } from './ui/button';
import { Badge } from './ui/badge';
import FixedImage from './FixedImage';
import { notification } from '../services/notificationService';

interface EnhancedPropertyGalleryProps {
  images: string[];
  title?: string;
  onFavorite?: () => void;
  isFavorite?: boolean;
}

/**
 * Enhanced Property Gallery following Instagram/Pinterest patterns
 * Provides familiar image viewing experience
 */
const EnhancedPropertyGallery: React.FC<EnhancedPropertyGalleryProps> = ({
  images,
  title = 'Property Images',
  onFavorite,
  isFavorite = false
}) => {
  const [currentIndex, setCurrentIndex] = useState(0);
  const [isFullscreen, setIsFullscreen] = useState(false);
  const [isZoomed, setIsZoomed] = useState(false);
  const [showThumbnails, setShowThumbnails] = useState(true);

  const handlePrevious = () => {
    setCurrentIndex((prev) => (prev === 0 ? images.length - 1 : prev - 1));
  };

  const handleNext = () => {
    setCurrentIndex((prev) => (prev === images.length - 1 ? 0 : prev + 1));
  };

  const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'ArrowLeft') handlePrevious();
    if (e.key === 'ArrowRight') handleNext();
    if (e.key === 'Escape') setIsFullscreen(false);
  };

  React.useEffect(() => {
    if (isFullscreen) {
      document.addEventListener('keydown', handleKeyDown);
      return () => document.removeEventListener('keydown', handleKeyDown);
    }
  }, [isFullscreen, currentIndex]);

  const handleShare = async () => {
    if (navigator.share) {
      try {
        await navigator.share({
          title: title,
          text: `Check out this property: ${title}`,
          url: window.location.href,
        });
      } catch (error) {
        // Error sharing
      }
    } else {
      // Fallback: Copy to clipboard
      navigator.clipboard.writeText(window.location.href);
      notification.success('Link copied to clipboard');
    }
  };

  const handleDownload = () => {
    const link = document.createElement('a');
    link.href = images[currentIndex];
    link.download = `property-image-${currentIndex + 1}.jpg`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    notification.success('Image downloaded');
  };

  if (!images || images.length === 0) {
    return (
      <div className="w-full h-96 bg-gray-100 rounded-lg flex items-center justify-center">
        <p className="text-gray-500">No images available</p>
      </div>
    );
  }

  return (
    <>
      {/* Main Gallery View */}
      <div className="relative w-full bg-white rounded-lg overflow-hidden shadow-lg">
        {/* Main Image Container */}
        <div className="relative aspect-[16/10] bg-gray-100">
          <FixedImage
            src={images[currentIndex]}
            alt={`${title} - Image ${currentIndex + 1}`}
            className={`w-full h-full object-contain transition-transform duration-300 ${
              isZoomed ? 'scale-150 cursor-zoom-out' : 'cursor-zoom-in'
            }`}
            onClick={() => setIsZoomed(!isZoomed)}
          />

          {/* Image Counter Badge */}
          <Badge className="absolute top-4 left-4 bg-black/70 text-white border-0">
            {currentIndex + 1} / {images.length}
          </Badge>

          {/* Control Buttons */}
          <div className="absolute top-4 right-4 flex gap-2">
            <Button
              variant="secondary"
              size="icon"
              onClick={handleShare}
              className="bg-white/90 hover:bg-white shadow-md"
              aria-label="Share"
            >
              <Share2 className="h-4 w-4" />
            </Button>
            
            {onFavorite && (
              <Button
                variant="secondary"
                size="icon"
                onClick={onFavorite}
                className="bg-white/90 hover:bg-white shadow-md"
                aria-label="Add to favorites"
              >
                <Heart className={`h-4 w-4 ${isFavorite ? 'fill-red-500 text-red-500' : ''}`} />
              </Button>
            )}

            <Button
              variant="secondary"
              size="icon"
              onClick={handleDownload}
              className="bg-white/90 hover:bg-white shadow-md"
              aria-label="Download"
            >
              <Download className="h-4 w-4" />
            </Button>

            <Button
              variant="secondary"
              size="icon"
              onClick={() => setIsFullscreen(true)}
              className="bg-white/90 hover:bg-white shadow-md"
              aria-label="Fullscreen"
            >
              <Maximize2 className="h-4 w-4" />
            </Button>
          </div>

          {/* Navigation Arrows - Instagram Style */}
          {images.length > 1 && (
            <>
              <Button
                variant="ghost"
                size="icon"
                onClick={handlePrevious}
                className="absolute left-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white rounded-full shadow-lg w-10 h-10"
                aria-label="Previous image"
              >
                <ChevronLeft className="h-5 w-5" />
              </Button>

              <Button
                variant="ghost"
                size="icon"
                onClick={handleNext}
                className="absolute right-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white rounded-full shadow-lg w-10 h-10"
                aria-label="Next image"
              >
                <ChevronRight className="h-5 w-5" />
              </Button>
            </>
          )}

          {/* Dots Indicator - Instagram Style */}
          {images.length > 1 && (
            <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5">
              {images.map((_, index) => (
                <button
                  key={index}
                  onClick={() => setCurrentIndex(index)}
                  className={`w-2 h-2 rounded-full transition-all duration-200 ${
                    index === currentIndex
                      ? 'bg-white w-6'
                      : 'bg-white/60 hover:bg-white/80'
                  }`}
                  aria-label={`Go to image ${index + 1}`}
                />
              ))}
            </div>
          )}
        </div>

        {/* Thumbnails Grid - Pinterest Style */}
        {showThumbnails && images.length > 1 && (
          <div className="p-4 border-t">
            <div className="flex items-center justify-between mb-3">
              <h3 className="text-sm font-medium text-gray-700">All Photos</h3>
              <Button
                variant="ghost"
                size="sm"
                onClick={() => setShowThumbnails(false)}
                className="text-xs"
              >
                Hide
              </Button>
            </div>
            <div className="grid grid-cols-6 gap-2">
              {images.map((image, index) => (
                <button
                  key={index}
                  onClick={() => setCurrentIndex(index)}
                  className={`relative aspect-square rounded-lg overflow-hidden border-2 transition-all duration-200 ${
                    index === currentIndex
                      ? 'border-[#067977] scale-105 shadow-md'
                      : 'border-transparent hover:border-gray-300'
                  }`}
                >
                  <FixedImage
                    src={image}
                    alt={`Thumbnail ${index + 1}`}
                    className="w-full h-full object-cover"
                  />
                  {index === currentIndex && (
                    <div className="absolute inset-0 bg-[#067977]/10" />
                  )}
                </button>
              ))}
            </div>
          </div>
        )}
      </div>

      {/* Fullscreen Modal - Instagram Style */}
      {isFullscreen && (
        <div className="fixed inset-0 z-[9999] bg-black">
          <div className="relative w-full h-full flex items-center justify-center">
            <FixedImage
              src={images[currentIndex]}
              alt={`${title} - Image ${currentIndex + 1}`}
              className="max-w-full max-h-full object-contain"
            />

            {/* Close Button */}
            <Button
              variant="ghost"
              size="icon"
              onClick={() => setIsFullscreen(false)}
              className="absolute top-4 right-4 text-white hover:bg-white/20"
              aria-label="Close fullscreen"
            >
              <X className="h-6 w-6" />
            </Button>

            {/* Navigation in Fullscreen */}
            {images.length > 1 && (
              <>
                <Button
                  variant="ghost"
                  size="icon"
                  onClick={handlePrevious}
                  className="absolute left-4 top-1/2 -translate-y-1/2 text-white hover:bg-white/20 w-12 h-12"
                  aria-label="Previous image"
                >
                  <ChevronLeft className="h-8 w-8" />
                </Button>

                <Button
                  variant="ghost"
                  size="icon"
                  onClick={handleNext}
                  className="absolute right-4 top-1/2 -translate-y-1/2 text-white hover:bg-white/20 w-12 h-12"
                  aria-label="Next image"
                >
                  <ChevronRight className="h-8 w-8" />
                </Button>
              </>
            )}

            {/* Counter in Fullscreen */}
            <div className="absolute bottom-4 left-1/2 -translate-x-1/2 text-white">
              {currentIndex + 1} / {images.length}
            </div>
          </div>
        </div>
      )}
    </>
  );
};

export default EnhancedPropertyGallery;