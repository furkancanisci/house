import React, { useState, useEffect, useRef } from 'react';
import { ChevronLeft, ChevronRight, Loader2, ImageIcon, Play, Maximize2, Minimize2 } from 'lucide-react';
import Lightbox from 'yet-another-react-lightbox';
import 'yet-another-react-lightbox/styles.css';
import { fixImageUrl } from '../lib/imageUtils';

interface Video {
  id: number;
  url: string;
  original_url?: string;
  thumbnail_url?: string;
  duration?: number;
  size?: number;
  mime_type?: string;
}

interface PropertyImageGalleryProps {
  images: string[];
  videos?: Video[];
  alt?: string;
  className?: string;
  containerClassName?: string;
  enableZoom?: boolean;
  showThumbnails?: boolean;
  showLoadingSpinner?: boolean;
}



const PropertyImageGallery: React.FC<PropertyImageGalleryProps> = ({
  images,
  videos = [],
  alt = 'Property image',
  className = 'aspect-[4/3]',
  containerClassName = '',
  enableZoom = true,
  showThumbnails = true,
  showLoadingSpinner = true,
}) => {
  const [currentMediaIndex, setCurrentMediaIndex] = useState(0);
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [lightboxIndex, setLightboxIndex] = useState(0);
  const [mediaLoadStates, setMediaLoadStates] = useState<Record<number, { loaded: boolean; error: boolean; loading: boolean }>>({});
  const [isVideoFullscreen, setIsVideoFullscreen] = useState(false);
  
  const videoRef = useRef<HTMLVideoElement>(null);
  const videoContainerRef = useRef<HTMLDivElement>(null);

  // Process images to ensure they're valid - handle undefined/null images
  const processedImages = (images && Array.isArray(images) && images.length > 0) 
    ? images.map(img => {
        // If URL is already complete (starts with http), don't process it at all
        if (typeof img === 'string' && (img.startsWith('http://') || img.startsWith('https://'))) {
          return img;
        }
        
        const result = fixImageUrl(img);
        return result;
      }).filter(url => url !== '') // Remove empty URLs
    : [];

  // Process videos
  const processedVideos = (videos && Array.isArray(videos) && videos.length > 0)
    ? videos.map(video => ({
        ...video,
        url: video.url || video.original_url || '',
        type: 'video' as const
      })).filter(video => video.url !== '')
    : [];

  // Combine videos and images into a single media array (videos first)
  const allMedia = [
    ...processedVideos,
    ...processedImages.map(url => ({ url, type: 'image' as const }))
  ];
  
  // Only use fallback if NO valid media exists at all
  const finalMedia = allMedia.length > 0 ? allMedia : [{ url: '/images/placeholder-property.svg', type: 'image' as const }];

  const currentMedia = finalMedia[currentMediaIndex];
  const currentMediaState = mediaLoadStates[currentMediaIndex] || { loaded: false, error: false, loading: true };
  const isCurrentVideo = currentMedia.type === 'video';

  // Initialize loading states for all media when component mounts or media changes
  useEffect(() => {
    const initialStates: Record<number, { loaded: boolean; error: boolean; loading: boolean }> = {};
    finalMedia.forEach((_, index) => {
      if (!mediaLoadStates[index]) {
        initialStates[index] = { loaded: false, error: false, loading: true };
      }
    });
    
    if (Object.keys(initialStates).length > 0) {
      setMediaLoadStates(prev => ({ ...prev, ...initialStates }));
    }
  }, [finalMedia.length]);

  // Handle fullscreen change events
  useEffect(() => {
    const handleFullscreenChange = () => {
      if (!document.fullscreenElement) {
        setIsVideoFullscreen(false);
      }
    };

    document.addEventListener('fullscreenchange', handleFullscreenChange);
    document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
    document.addEventListener('mozfullscreenchange', handleFullscreenChange);
    document.addEventListener('MSFullscreenChange', handleFullscreenChange);

    return () => {
      document.removeEventListener('fullscreenchange', handleFullscreenChange);
      document.removeEventListener('webkitfullscreenchange', handleFullscreenChange);
      document.removeEventListener('mozfullscreenchange', handleFullscreenChange);
      document.removeEventListener('MSFullscreenChange', handleFullscreenChange);
    };
  }, []);

  const toggleVideoFullscreen = async () => {
    if (!videoContainerRef.current) return;

    try {
      if (!isVideoFullscreen) {
        // Enter fullscreen
        if (videoContainerRef.current.requestFullscreen) {
          await videoContainerRef.current.requestFullscreen();
        } else if ((videoContainerRef.current as any).webkitRequestFullscreen) {
          await (videoContainerRef.current as any).webkitRequestFullscreen();
        } else if ((videoContainerRef.current as any).mozRequestFullScreen) {
          await (videoContainerRef.current as any).mozRequestFullScreen();
        } else if ((videoContainerRef.current as any).msRequestFullscreen) {
          await (videoContainerRef.current as any).msRequestFullscreen();
        }
        setIsVideoFullscreen(true);
      } else {
        // Exit fullscreen
        if (document.exitFullscreen) {
          await document.exitFullscreen();
        } else if ((document as any).webkitExitFullscreen) {
          await (document as any).webkitExitFullscreen();
        } else if ((document as any).mozCancelFullScreen) {
          await (document as any).mozCancelFullScreen();
        } else if ((document as any).msExitFullscreen) {
          await (document as any).msExitFullscreen();
        }
        setIsVideoFullscreen(false);
      }
    } catch (error) {
      console.error('Error toggling fullscreen:', error);
    }
  };

  const handlePrevMedia = () => {
    setCurrentMediaIndex((prev) => 
      prev === 0 ? finalMedia.length - 1 : prev - 1
    );
  };

  const handleNextMedia = () => {
    setCurrentMediaIndex((prev) => 
      prev === finalMedia.length - 1 ? 0 : prev + 1
    );
  };

  const handleMediaLoad = (index: number) => {
    setMediaLoadStates(prev => ({
      ...prev,
      [index]: { loaded: true, error: false, loading: false }
    }));
  };

  const handleMediaError = (index: number) => {
    setMediaLoadStates(prev => ({
      ...prev,
      [index]: { loaded: false, error: true, loading: false }
    }));
  };

  const handleMediaClick = () => {
    if (enableZoom && !isCurrentVideo) {
      setLightboxIndex(currentMediaIndex);
      setLightboxOpen(true);
    }
  };

  const handleThumbnailClick = (index: number) => {
    setCurrentMediaIndex(index);
  };

  // Prepare slides for lightbox (only images)
  const lightboxSlides = finalMedia
    .filter(media => media.type === 'image')
    .map((media, index) => ({
      src: media.url,
      alt: `${alt} - Image ${index + 1}`,
    }));

  return (
    <div className={`relative ${containerClassName}`}>
      {/* Main Media */}
      <div className="relative group overflow-hidden rounded-lg shadow-md border border-gray-200 w-full h-96 min-h-96 max-h-96">
        {/* Loading Spinner */}
        {currentMediaState.loading && showLoadingSpinner && (
          <div className="absolute inset-0 bg-gray-100 flex items-center justify-center z-10">
            <Loader2 className="w-8 h-8 text-gray-400 animate-spin" />
          </div>
        )}

        {/* Error State - Show placeholder image instead of error icon */}
        {currentMediaState.error && (
          <img
            src="/images/placeholder-property.svg"
            alt="Property placeholder"
            className="w-full h-full object-cover"
            style={{ filter: 'opacity(0.7)' }}
          />
        )}

        {/* Render Video or Image */}
        {isCurrentVideo ? (
          <div 
            ref={videoContainerRef}
            className={`relative w-full h-full ${isVideoFullscreen ? 'bg-black' : ''}`}
          >
            <video
              ref={videoRef}
              src={currentMedia.url}
              className={`w-full h-full object-cover transition-all duration-300 ${
                currentMediaState.loading ? 'opacity-0' : 'opacity-100'
              } ${isVideoFullscreen ? 'object-contain' : ''}`}
              onLoadedData={() => handleMediaLoad(currentMediaIndex)}
              onError={() => handleMediaError(currentMediaIndex)}
              controls
              preload="metadata"
            />
            
            {/* Video Controls Overlay */}
            <div className="absolute top-2 right-2 flex gap-2">
              {/* Fullscreen Toggle Button */}
              <button
                onClick={toggleVideoFullscreen}
                className="bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full p-2 transition-all duration-200"
                aria-label={isVideoFullscreen ? "Exit fullscreen" : "Enter fullscreen"}
              >
                {isVideoFullscreen ? (
                  <Minimize2 className="w-5 h-5" />
                ) : (
                  <Maximize2 className="w-5 h-5" />
                )}
              </button>
            </div>
            
            {/* Video Play Icon Overlay - Only show when not in fullscreen */}
            {!isVideoFullscreen && (
              <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div className="bg-black bg-opacity-50 rounded-full p-3">
                  <Play className="w-8 h-8 text-white fill-white" />
                </div>
              </div>
            )}
          </div>
        ) : (
          <img
            src={currentMedia.url}
            alt={alt}
            className={`w-full h-full object-cover transition-all duration-300 ${
              enableZoom ? 'cursor-pointer hover:scale-105' : ''
            } ${currentMediaState.loading ? 'opacity-0' : 'opacity-100'}`}
            onLoad={() => handleMediaLoad(currentMediaIndex)}
            onError={() => handleMediaError(currentMediaIndex)}
            onClick={handleMediaClick}
          />
        )}

        {/* Navigation Arrows (only show if multiple media items) */}
        {finalMedia.length > 1 && (
          <>
            <button
              onClick={handlePrevMedia}
              className="absolute left-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full p-2 transition-all duration-200 opacity-0 group-hover:opacity-100"
              aria-label="Previous media"
            >
              <ChevronLeft className="w-5 h-5" />
            </button>
            <button
              onClick={handleNextMedia}
              className="absolute right-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-70 text-white rounded-full p-2 transition-all duration-200 opacity-0 group-hover:opacity-100"
              aria-label="Next media"
            >
              <ChevronRight className="w-5 h-5" />
            </button>
          </>
        )}

        {/* Media Counter */}
        {finalMedia.length > 1 && (
          <div className="absolute bottom-2 right-2 bg-black bg-opacity-60 text-white text-sm px-3 py-1 rounded-full">
            {currentMediaIndex + 1} / {finalMedia.length}
          </div>
        )}
      </div>

      {/* Thumbnails */}
      {showThumbnails && finalMedia.length > 1 && (
        <div className="flex gap-3 mt-4 overflow-x-auto pb-2">
          {finalMedia.map((media, index) => {
            const thumbnailState = mediaLoadStates[index] || { loaded: false, error: false, loading: true };
            const isVideo = media.type === 'video';
            return (
              <button
                key={index}
                onClick={() => handleThumbnailClick(index)}
                className={`flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-all duration-200 shadow-sm ${
                  index === currentMediaIndex
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
                  {isVideo ? (
                    <>
                      <video
                        src={media.url}
                        className={`w-full h-full object-cover transition-opacity duration-200 ${
                          thumbnailState.loading ? 'opacity-0' : 'opacity-100'
                        }`}
                        onLoadedData={() => handleMediaLoad(index)}
                        onError={() => handleMediaError(index)}
                        preload="metadata"
                      />
                      {/* Video indicator */}
                      <div className="absolute inset-0 flex items-center justify-center">
                        <div className="bg-black bg-opacity-50 rounded-full p-1">
                          <Play className="w-3 h-3 text-white fill-white" />
                        </div>
                      </div>
                    </>
                  ) : (
                    <img
                      src={media.url}
                      alt={`${alt} thumbnail ${index + 1}`}
                      className={`w-full h-full object-cover transition-opacity duration-200 ${
                        thumbnailState.loading ? 'opacity-0' : 'opacity-100'
                      }`}
                      onLoad={() => handleMediaLoad(index)}
                      onError={() => handleMediaError(index)}
                    />
                  )}
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
        carousel={{ finite: lightboxSlides.length === 1 }}
        on={{
          view: ({ index }) => setLightboxIndex(index),
        }}
      />
    </div>
  );
};

export default PropertyImageGallery;