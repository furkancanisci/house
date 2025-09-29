import React, { useState, useRef } from 'react';
import { Upload, Play, Pause, Volume2, VolumeX, X, FileVideo } from 'lucide-react';
import { Button } from './ui/button';
import { Progress } from './ui/progress';
import { toast } from 'sonner';
import { useTranslation } from 'react-i18next';
import { formatFileSize } from '../lib/imageUtils';

export interface VideoFile {
  file: File;
  url: string;
  thumbnail?: string;
  duration?: number;
}

interface VideoUploadProps {
  selectedVideos: VideoFile[];
  onVideosChange: (videos: VideoFile[]) => void;
  maxVideos?: number;
  maxSizePerVideo?: number; // in bytes
  acceptedFormats?: string[];
  className?: string;
}

const VideoUpload: React.FC<VideoUploadProps> = ({
  selectedVideos = [],
  onVideosChange,
  maxVideos = 1,
  maxSizePerVideo = 100 * 1024 * 1024, // 100MB
  acceptedFormats = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/webm'],
  className = ''
}) => {
  const { t } = useTranslation();
  const [uploadProgress, setUploadProgress] = useState<{ [key: string]: number }>({});
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [isProcessing, setIsProcessing] = useState(false);

  const generateVideoThumbnail = (file: File): Promise<string> => {
    return new Promise((resolve, reject) => {
      const video = document.createElement('video');
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d');
      
      video.preload = 'metadata';
      video.muted = true;
      
      video.onloadedmetadata = () => {
        canvas.width = 320;
        canvas.height = (video.videoHeight / video.videoWidth) * 320;
        
        video.currentTime = Math.min(2, video.duration / 4); // Seek to 2 seconds or 1/4 of duration
      };
      
      video.onseeked = () => {
        if (ctx) {
          ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
          const thumbnail = canvas.toDataURL('image/jpeg', 0.8);
          resolve(thumbnail);
        } else {
          reject(new Error('Failed to get canvas context'));
        }
      };
      
      video.onerror = () => reject(new Error('Failed to load video'));
      
      video.src = URL.createObjectURL(file);
    });
  };

  const getVideoDuration = (file: File): Promise<number> => {
    return new Promise((resolve, reject) => {
      const video = document.createElement('video');
      video.preload = 'metadata';
      video.muted = true;
      
      video.onloadedmetadata = () => {
        resolve(video.duration);
      };
      
      video.onerror = () => reject(new Error('Failed to get video duration'));
      
      video.src = URL.createObjectURL(file);
    });
  };

  const handleFileSelect = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(event.target.files || []);
    
    if (!files.length) return;

    // Check total video count
    const currentVideoCount = selectedVideos?.length || 0;
    if (currentVideoCount + files.length > maxVideos) {
      toast.error(t('addProperty.videoUpload.errors.tooManyFiles'));
      return;
    }

    // Validate file types
    const wrongTypeFiles = files.filter(file => !acceptedFormats.includes(file.type));
    if (wrongTypeFiles.length > 0) {
      wrongTypeFiles.forEach(file => {
        toast.error(t('addProperty.videoUpload.errors.invalidFormat') + `: ${file.name}`);
      });
      return;
    }

    // Check individual file sizes (max 100MB per video)
    const maxIndividualSize = 100 * 1024 * 1024; // 100MB per video
    const oversizedFiles = files.filter(file => file.size > maxIndividualSize);
    if (oversizedFiles.length > 0) {
      oversizedFiles.forEach(file => {
        const fileSize = (file.size / (1024 * 1024)).toFixed(1);
        toast.error(`Video too large: ${file.name} (${fileSize}MB). Max size is 100MB per video.`);
      });
      return;
    }

    // Check for large files and warn about chunked upload
    const totalSize = files.reduce((sum, file) => sum + file.size, 0);
    const existingSize = (selectedVideos || []).reduce((sum, video) => sum + video.file.size, 0);
    const combinedSize = totalSize + existingSize;
    
    if (combinedSize > 10 * 1024 * 1024) { // 10MB threshold
      const totalSizeMB = (combinedSize / (1024 * 1024)).toFixed(1);
      toast.info(`Large videos detected (${totalSizeMB}MB total). Will use chunked upload for better reliability.`);
    }

    // Check against maxSizePerVideo for backward compatibility
    const legacyOversizedFiles = files.filter(file => file.size > maxSizePerVideo);
    if (legacyOversizedFiles.length > 0) {
      legacyOversizedFiles.forEach(file => {
        toast.error(t('addProperty.videoUpload.errors.fileTooLarge') + `: ${file.name}`);
      });
      return;
    }

    setIsProcessing(true);
    const newVideos: VideoFile[] = [];

    try {
      for (const file of files) {
        const fileId = `${file.name}-${Date.now()}`;
        setUploadProgress(prev => ({ ...prev, [fileId]: 0 }));

        // Simulate upload progress
        const progressInterval = setInterval(() => {
          setUploadProgress(prev => {
            const currentProgress = prev[fileId] || 0;
            if (currentProgress >= 90) {
              clearInterval(progressInterval);
              return prev;
            }
            return { ...prev, [fileId]: currentProgress + 10 };
          });
        }, 200);

        try {
          const [thumbnail, duration] = await Promise.all([
            generateVideoThumbnail(file),
            getVideoDuration(file)
          ]);

          const videoFile: VideoFile = {
            file,
            url: URL.createObjectURL(file),
            thumbnail,
            duration
          };

          newVideos.push(videoFile);
          
          // Complete progress
          setUploadProgress(prev => ({ ...prev, [fileId]: 100 }));
          clearInterval(progressInterval);
          
        } catch (error) {
          console.error(`Error processing video ${file.name}:`, error);
          toast.error(t('addProperty.videoUpload.errors.processingFailed'));
          clearInterval(progressInterval);
          setUploadProgress(prev => {
            const newProgress = { ...prev };
            delete newProgress[fileId];
            return newProgress;
          });
        }
      }

      onVideosChange([...(selectedVideos || []), ...newVideos]);
      
    } finally {
      setIsProcessing(false);
      // Clear progress after a delay
      setTimeout(() => {
        setUploadProgress({});
      }, 2000);
    }

    // Clear input
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  const removeVideo = (index: number) => {
    if (!selectedVideos || selectedVideos.length === 0) return;
    
    const videoToRemove = selectedVideos[index];
    if (videoToRemove?.url) {
      URL.revokeObjectURL(videoToRemove.url);
    }
    
    const updatedVideos = selectedVideos.filter((_, i) => i !== index);
    onVideosChange(updatedVideos);
  };

  const formatDuration = (seconds: number): string => {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  return (
    <div className={`space-y-4 ${className}`}>
      {/* Upload Area */}
      <div className="border-2 border-dashed border-purple-300 rounded-lg p-6 text-center hover:border-purple-400 hover:bg-purple-50 transition-all duration-300 bg-white/50">
        <input
          ref={fileInputRef}
          type="file"
          multiple
          accept={acceptedFormats.join(',')}
          onChange={handleFileSelect}
          className="hidden"
          id="video-upload"
          disabled={isProcessing || (selectedVideos?.length || 0) >= maxVideos}
        />
        <label htmlFor="video-upload" className="cursor-pointer block">
          <div className="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3 hover:bg-purple-200 transition-colors">
            <FileVideo className="h-8 w-8 text-purple-600" />
          </div>
          <p className="text-base font-semibold text-gray-900 mb-2 font-['Cairo',_'Tajawal',_sans-serif]">
            {isProcessing ? t('addProperty.videoUpload.processing') : t('addProperty.videoUpload.clickToUpload')}
          </p>
          <p className="text-sm text-gray-500 font-['Cairo',_'Tajawal',_sans-serif]">
            {t('addProperty.videoUpload.supportedFormats')}
          </p>
        </label>
      </div>

      {/* Upload Progress */}
      {Object.keys(uploadProgress).length > 0 && (
        <div className="space-y-2">
          {Object.entries(uploadProgress).map(([fileId, progress]) => (
            <div key={fileId} className="bg-white rounded-lg p-3 border">
              <div className="flex items-center justify-between mb-2">
                <span className="text-sm font-medium text-gray-700">
                  {fileId.split('-')[0]}
                </span>
                <span className="text-sm text-gray-500">{progress}%</span>
              </div>
              <Progress value={progress} className="h-2" />
            </div>
          ))}
        </div>
      )}

      {/* Selected Videos */}
      {selectedVideos && selectedVideos.length > 0 && (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h4 className="text-base font-semibold text-gray-900 flex items-center gap-2 font-['Cairo',_'Tajawal',_sans-serif]">
              <FileVideo className="h-4 w-4 text-purple-600" />
              {t('addProperty.videoUpload.selectedVideos')} ({selectedVideos.length}/{maxVideos})
            </h4>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {selectedVideos.map((video, index) => (
              <VideoPreview
                key={index}
                video={video}
                onRemove={() => removeVideo(index)}
              />
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

// Video Preview Component
interface VideoPreviewProps {
  video: VideoFile;
  onRemove: () => void;
}

const VideoPreview: React.FC<VideoPreviewProps> = ({ video, onRemove }) => {
  const { t } = useTranslation();
  const [isPlaying, setIsPlaying] = useState(false);
  const [isMuted, setIsMuted] = useState(true);
  const videoRef = useRef<HTMLVideoElement>(null);

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

  const formatDuration = (seconds: number): string => {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  return (
    <div className="relative group bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-all duration-200">
      {/* Video or Thumbnail */}
      <div className="relative aspect-video bg-gray-100">
        {video.thumbnail ? (
          <>
            <img
              src={video.thumbnail}
              alt="Video thumbnail"
              className="w-full h-full object-cover"
            />
            <video
              ref={videoRef}
              src={video.url}
              className={`absolute inset-0 w-full h-full object-cover transition-opacity duration-300 ${
                isPlaying ? 'opacity-100' : 'opacity-0'
              }`}
              muted={isMuted}
              onEnded={() => setIsPlaying(false)}
            />
          </>
        ) : (
          <div className="w-full h-full flex items-center justify-center">
            <FileVideo className="h-12 w-12 text-gray-400" />
          </div>
        )}

        {/* Video Controls Overlay */}
        <div className="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-200 flex items-center justify-center">
          <div className="opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center gap-2">
            <Button
              type="button"
              variant="secondary"
              size="sm"
              onClick={togglePlay}
              className="bg-white/90 hover:bg-white text-gray-700"
              title={isPlaying ? t('addProperty.videoUpload.pause') : t('addProperty.videoUpload.play')}
            >
              {isPlaying ? <Pause className="h-4 w-4" /> : <Play className="h-4 w-4" />}
            </Button>
            <Button
              type="button"
              variant="secondary"
              size="sm"
              onClick={toggleMute}
              className="bg-white/90 hover:bg-white text-gray-700"
              title={isMuted ? t('addProperty.videoUpload.unmute') : t('addProperty.videoUpload.mute')}
            >
              {isMuted ? <VolumeX className="h-4 w-4" /> : <Volume2 className="h-4 w-4" />}
            </Button>
          </div>
        </div>

        {/* Remove Button */}
        <button
          type="button"
          onClick={onRemove}
          className="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-all duration-200 shadow-lg z-10"
          title={t('addProperty.videoUpload.remove')}
        >
          <X className="h-3 w-3" />
        </button>

        {/* Duration Badge */}
        {video.duration && (
          <div className="absolute bottom-2 right-2 bg-black/70 text-white text-xs px-2 py-1 rounded">
            {formatDuration(video.duration)}
          </div>
        )}
      </div>

      {/* Video Info */}
      <div className="p-3">
        <p className="text-sm font-medium text-gray-900 truncate">
          {video.file.name}
        </p>
        <p className="text-xs text-gray-500 mt-1">
          {formatFileSize(video.file.size)}
          {video.duration && ` • ${formatDuration(video.duration)}`}
        </p>
      </div>
    </div>
  );
};

export default VideoUpload;
export type { VideoFile };