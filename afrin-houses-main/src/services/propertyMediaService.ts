import { api } from './api';

export interface PropertyMediaItem {
  filename: string;
  path: string;
  url: string;
  size: number;
  last_modified: string;
}

export interface PropertyMediaResponse {
  success: boolean;
  data: {
    images?: PropertyMediaItem[];
    videos?: PropertyMediaItem[];
  };
  message?: string;
}

export const propertyMediaService = {
  /**
   * Get all images for a specific property
   */
  async getPropertyImages(propertyId: string | number): Promise<PropertyMediaItem[]> {
    try {
      const response = await api.get<PropertyMediaResponse>(`/properties/${propertyId}/images`);
      return response.data.data.images || [];
    } catch (error) {
      console.error('Error fetching property images:', error);
      return [];
    }
  },

  /**
   * Get all videos for a specific property
   */
  async getPropertyVideos(propertyId: string | number): Promise<PropertyMediaItem[]> {
    try {
      const response = await api.get<PropertyMediaResponse>(`/properties/${propertyId}/videos`);
      return response.data.data.videos || [];
    } catch (error) {
      console.error('Error fetching property videos:', error);
      return [];
    }
  },

  /**
   * Get all media (images and videos) for a specific property
   */
  async getPropertyMedia(propertyId: string | number): Promise<{ images: PropertyMediaItem[]; videos: PropertyMediaItem[] }> {
    try {
      const response = await api.get<PropertyMediaResponse>(`/properties/${propertyId}/media`);
      return {
        images: response.data.data.images || [],
        videos: response.data.data.videos || []
      };
    } catch (error) {
      console.error('Error fetching property media:', error);
      return { images: [], videos: [] };
    }
  },

  /**
   * Delete a specific media file for a property
   */
  async deletePropertyMedia(propertyId: string | number, filePath: string, mediaType: 'image' | 'video'): Promise<boolean> {
    try {
      const response = await api.delete(`/properties/${propertyId}/media`, {
        data: {
          file_path: filePath,
          media_type: mediaType
        }
      });
      return response.data.success;
    } catch (error) {
      console.error('Error deleting property media:', error);
      return false;
    }
  }
};