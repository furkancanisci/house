import React, { useState, useRef, useEffect } from 'react';
import { ChevronLeft, ChevronRight, Play, Pause, Volume2, VolumeX, Maximize2, X, ImageIcon, Loader2 } from 'lucide-react';
import Lightbox from 'yet-another-react-lightbox';
import 'yet-another-react-lightbox/styles.css';
import { Card } from './ui/card';
import { Button } from './ui/button';

interface MediaItem {
  id: string | number;
  type: 'image' | 'video';
  url: string;
  original_url?: string;
  thumbnail_url?: string;
  duration?: number;
  size?: number;
  mime_type?: string;
  alt?: string;
}

interface MediaGalleryProps {
  images?: string[];
  videos?: any[];
  alt: string;
  className?: string;
  containerClassName?: string;
  enableZoom?: boolean;
  showThumbnails?: boolean;
  showLoadingSpinner?: boolean;
  propertyId?: string | number;
  title?: string;
}

// Utility function to fix image URLs
const fixImageUrl = (url: string | undefined | null | any): string => {
  if (!url || typeof url !== 'string') return '';
  
  if (url.startsWith('http://') || 
      url.startsWith('https://') ||
      url.startsWith('data:') ||
      url.startsWith('/images/')) {
    return url;
  }
  
  if (url.startsWith('/storage/') || url.startsWith('/media/')) {
    const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api/v1', '') || 'http://127.0.0.1:8000';
    return `${baseUrl}${url}`;
  }
  
  if (url.startsWith('/') && !url.startsWith('/images/')) {
    const baseUrl = import.meta.env.VITE_API_BASE_URL?.replace('/api/v1', '') || 'http://127.0.0.1:8000';
    return `${baseUrl}${url}`;
  }
  
  return url;
};

const MediaGallery: React.FC<MediaGalleryProps> = ({
  images = [],
  videos = [],
  alt,
  className = '',
  containerClassName = '',
  enableZoom = true,
  showThumbnails = true,
  showLoadingSpinner = true,
  propertyId,
  title = 'Property Media'
}) => {
  const [currentMediaIndex, setCurrentMediaIndex] = useState(0);
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [lightboxIndex, setLightboxIndex] = useState(0);
  const [mediaLoadStates, setMediaLoadStates] = useState<Record<number, { loaded: boolean; error: boolean; loading: boolean }>>({});
  
  // Video-specific states
  const [isPlaying, setIsPlaying] = useState(false);
  const [isMuted, setIsMuted] = useState(true);
  const [isFullscreen, setIsFullscreen] = useState(false);
  const [showVideoControls, setShowVideoControls] = useState(true);
  const videoRef = useRef<HTMLVideoElement>(null);
  const containerRef = useRef<HTMLDivElement>(null);

  // Process and combine media items
  const processedImages = (images && Array.isArray(images) && images.length > 0) 
    ? images.map(img => {
        if (typeof img === 'string' && (img.startsWith('http://') || img.startsWith('https://'))) {
          return img;
        }
        return fixImageUrl(img);
      }).filter(url => url !== '')
    : [];

  const processedVideos = (videos && Array.isArray(videos) && videos.length > 0)
    ? videos.map(video => ({
        ...video,
        url: fixImageUrl(video.url || video.original_url)
      })).filter(video => video.url !== '')
    : [];

  // Combine all media items
  const mediaItems: MediaItem[] = [
    ...processedImages.map((url, index) => ({
      id: `image-${index}`,
      type: 'image' as const,
      url,
      alt: `${alt} - Image ${index + 1}`
    })),
    ...processedVideos.map((video, index) => ({
      id: `video-${index}`,
      type: 'video' as const,
      url: video.url,
      original_url: video.original_url,
      thumbnail_url: video.thumbnail_url,
      duration: video.duration,
      size: video.size,
      mime_type: video.mime_type,
      alt: `${alt} - Video ${index + 1}`
    }))
  ];

  // Don't show placeholder if no media exists - return empty array
  const finalMediaItems = mediaItems;

  // Debug logging
  console.log('MediaGallery - images:', images);
  console.log('MediaGallery - videos:', videos);
  console.log('MediaGallery - finalMediaItems:', finalMediaItems);
  console.log('MediaGallery - finalMediaItems.length:', finalMediaItems.length);
  
  // If no media items exist, don't render the gallery
  if (finalMediaItems.length === 0) {
    console.log('MediaGallery - returning null because no media items');
    return null;
  }

  const currentMedia = finalMediaItems[currentMediaIndex];
  const currentMediaState = mediaLoadStates[currentMediaIndex] || { loaded: false, error: false, loading: true };

  // Initialize loading states
  useEffect(() => {
    const initialStates: Record<number, { loaded: boolean; error: boolean; loading: boolean }> = {};
    finalMediaItems.forEach((_, index) => {
      if (!mediaLoadStates[index]) {
        initialStates[index] = { loaded: false, error: false, loading: true };
      }
    });
    
    if (Object.keys(initialStates).length > 0) {
      setMediaLoadStates(prev => ({ ...prev, ...initialStates }));
    }
  }, [finalMediaItems.length]);

  // Reset video state when switching media
  useEffect(() => {
    if (currentMedia.type === 'video') {
      setIsPlaying(false);
      if (videoRef.current) {
        videoRef.current.pause();
        videoRef.current.currentTime = 0;
      }
    }
  }, [currentMediaIndex]);

  const handlePrevMedia = () => {
    setCurrentMediaIndex((prev) => 
      prev === 0 ? finalMediaItems.length - 1 : prev - 1
    );
  };

  const handleNextMedia = () => {
    setCurrentMediaIndex((prev) => 
      prev === finalMediaItems.length - 1 ? 0 : prev + 1
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

  const handleImageClick = () => {
    if (enableZoom && currentMedia.type === 'image') {
      setLightboxIndex(currentMediaIndex);
      setLightboxOpen(true);
    }
  };

  const handleThumbnailClick = (index: number) => {
    setCurrentMediaIndex(index);
  };

  // Video controls
  const togglePlay = () => {
    if (videoRef.current) {
      if (isPlaying) {
        videoRef.current.pause();
      } else {
        videoRef.current.play();
      }
      setIsPlaying(!isPlaying);
    }
  };

  const toggleMute = () => {
    if (videoRef.current) {
      videoRef.current.muted = !isMuted;
      setIsMuted(!isMuted);
    }
  };

  const toggleFullscreen = () => {
    if (!isFullscreen) {
      if (containerRef.current?.requestFullscreen) {
        containerRef.current.requestFullscreen();
        setIsFullscreen(true);
      }
    } else {
      if (document.exitFullscreen) {
        document.exitFullscreen();
        setIsFullscreen(false);
      }
    }
  };

  const formatDuration = (seconds?: number) => {
    if (!seconds) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  const formatFileSize = (bytes?: number) => {
    if (!bytes) return '';
    const mb = bytes / (1024 * 1024);
    return `${mb.toFixed(1)} MB`;
  };

  // Prepare slides for lightbox (images only)
  const lightboxSlides = finalMediaItems
    .filter(item => item.type === 'image')
    .map((item) => ({
      src: item.url,
      alt: item.alt || alt,
    }));

  return (
    <div className={`relative ${containerClassName}`}>
      {/* Main Media Display */}
      <div 
        ref={containerRef}
        className={`relative group overflow-hidden rounded-lg shadow-md border border-gray-200 ${
          isFullscreen ? 'fixed inset-0 z-50' : 'aspect-video'
        }`}
        onMouseEnter={() => setShowVideoControls(true)}
        onMouseLeave={() => setShowVideoControls(false)}
      >
        {/* Loading Spinner */}
        {currentMediaState.loading && showLoadingSpinner && (
          <div className="absolute inset-0 bg-gray-100 flex items-center justify-center z-10">
            <Loader2 className="w-8 h-8 text-gray-400 animate-spin" />
          </div>
        )}

        {/* Error State */}
        {currentMediaState.error && (
          <img
            src="/images/placeholder-property.svg"
            alt="Property placeholder"
            className={`w-full h-full object-cover ${className}`}
            style={{ filter: 'opacity(0.7)' }}
          />
        )}

        {/* Image Display */}
        {currentMedia.type === 'image' && (
          <img
            src={currentMedia.url}
            alt={currentMedia.alt || alt}
            className={`w-full h-full object-cover transition-all duration-300 ${
              enableZoom ? 'cursor-pointer hover:scale-105' : ''
            } ${currentMediaState.loading ? 'opacity-0' : 'opacity-100'} ${className}`}
            onLoad={() => handleMediaLoad(currentMediaIndex)}
            onError={() => handleMediaError(currentMediaIndex)}
            onClick={handleImageClick}
          />
        )}

        {/* Video Display */}
        {currentMedia.type === 'video' && (
          <>
            <video
              ref={videoRef}
              src={currentMedia.url || currentMedia.original_url}
              className={`w-full h-full object-contain bg-black ${
                currentMediaState.loading ? 'opacity-0' : 'opacity-100'
              }`}
              muted={isMuted}
              onPlay={() => setIsPlaying(true)}
              onPause={() => setIsPlaying(false)}
              onEnded={() => setIsPlaying(false)}
              onLoadedData={() => handleMediaLoad(currentMediaIndex)}
              onError={() => handleMediaError(currentMediaIndex)}
              poster={currentMedia.thumbnail_url}
            />
            
            {/* Video Controls Overlay */}
            <div 
              className={`absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center transition-opacity duration-300 ${
                showVideoControls ? 'opacity-100' : 'opacity-0'
              }`}
            >
              <Button
                variant="ghost"
                size="lg"
                onClick={togglePlay}
                className="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-4"
              >
                {isPlaying ? (
                  <Pause className="h-8 w-8" />
                ) : (
                  <Play className="h-8 w-8" />
                )}
              </Button>
            </div>
            
            {/* Video Bottom Controls */}
            <div 
              className={`absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-4 transition-opacity duration-300 ${
                showVideoControls ? 'opacity-100' : 'opacity-0'
              }`}
            >
              <div className="flex items-center justify-between text-white">
                <div className="flex items-center space-x-2">
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={toggleMute}
                    className="text-white hover:bg-white hover:bg-opacity-20 p-2"
                  >
                    {isMuted ? (
                      <VolumeX className="h-4 w-4" />
                    ) : (
                      <Volume2 className="h-4 w-4" />
                    )}
                  </Button>
                  <span className="text-sm">
                    {formatDuration(currentMedia.duration)}
                  </span>
                  {currentMedia.size && (
                    <span className="text-sm text-gray-300">
                      {formatFileSize(currentMedia.size)}
                    </span>
                  )}
                </div>
                
                <div className="flex items-center space-x-2">
                  <Button
                    variant="ghost"
                    size="sm"
                    onClick={toggleFullscreen}
                    className="text-white hover:bg-white hover:bg-opacity-20 p-2"
                  >
                    {isFullscreen ? (
                      <X className="h-4 w-4" />
                    ) : (
                      <Maximize2 className="h-4 w-4" />
                    )}
                  </Button>
                </div>
              </div>
            </div>
          </>
        )}

        {/* Navigation Arrows */}
        {finalMediaItems.length > 1 && (
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
        {finalMediaItems.length > 1 && (
          <div className="absolute bottom-2 right-2 bg-black bg-opacity-60 text-white text-sm px-3 py-1 rounded-full">
            {currentMediaIndex + 1} / {finalMediaItems.length}
          </div>
        )}
      </div>

      {/* Thumbnails */}
      {showThumbnails && finalMediaItems.length > 1 && (
        <div className="flex gap-3 mt-4 overflow-x-auto pb-2">
          {finalMediaItems.map((media, index) => {
            const thumbnailState = mediaLoadStates[index] || { loaded: false, error: false, loading: true };
            return (
              <button
                key={media.id}
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
                  
                  {media.type === 'image' ? (
                    <img
                      src={media.url}
                      alt={media.alt || `${alt} thumbnail ${index + 1}`}
                      className={`w-full h-full object-cover transition-opacity duration-200 ${
                        thumbnailState.loading ? 'opacity-0' : 'opacity-100'
                      }`}
                      onLoad={() => handleMediaLoad(index)}
                      onError={() => handleMediaError(index)}
                    />
                  ) : (
                    <>
                      {media.thumbnail_url ? (
                        <img
                          src={media.thumbnail_url}
                          alt={media.alt || `Video ${index + 1}`}
                          className={`w-full h-full object-cover transition-opacity duration-200 ${
                            thumbnailState.loading ? 'opacity-0' : 'opacity-100'
                          }`}
                          onLoad={() => handleMediaLoad(index)}
                          onError={() => handleMediaError(index)}
                        />
                      ) : (
                        <div className="w-full h-full bg-gray-200 flex items-center justify-center">
                          <Play className="h-6 w-6 text-gray-500" />
                        </div>
                      )}
                      <div className="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center">
                        <Play className="h-4 w-4 text-white" />
                      </div>
                      {media.duration && (
                        <div className="absolute bottom-1 right-1 bg-black bg-opacity-70 text-white text-xs px-1 rounded">
                          {formatDuration(media.duration)}
                        </div>
                      )}
                    </>
                  )}
                </div>
              </button>
            );
          })}
        </div>
      )}

      {/* Lightbox for images */}
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

export default MediaGallery;