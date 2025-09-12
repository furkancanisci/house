import { api } from './api';

export interface ContactFormData {
  name: string;
  email: string;
  phone?: string;
  subject: string;
  message: string;
}

export interface ContactMessage extends ContactFormData {
  id: number;
  ip_address?: string;
  user_agent?: string;
  is_spam: boolean;
  is_read: boolean;
  read_at?: string;
  created_at: string;
  updated_at: string;
}

export interface ContactResponse {
  success: boolean;
  message: string;
  data?: {
    id: number;
    created_at: string;
  };
  errors?: Record<string, string[]>;
}

export interface ContactListResponse {
  success: boolean;
  data: {
    data: ContactMessage[];
    current_page: number;
    total: number;
    per_page: number;
    last_page: number;
  };
}

export interface ContactSettings {
  phone?: string;
  email?: string;
  address?: string;
  business_hours?: string;
  whatsapp?: string;
  website?: string;
  facebook?: string;
  twitter?: string;
  linkedin?: string;
}

export interface ContactSettingsResponse {
  success: boolean;
  data: ContactSettings;
}

export const contactService = {
  // Submit contact form (public endpoint)
  async submitContactForm(formData: ContactFormData): Promise<ContactResponse> {
    try {
      const response = await api.post('/contact/submit', formData);
      return response.data;
    } catch (error: any) {
      if (error.response) {
        return error.response.data;
      }
      throw new Error('Failed to submit contact form');
    }
  },

  // Admin endpoints (require authentication)
  async getContactMessages(params?: {
    page?: number;
    per_page?: number;
    exclude_spam?: boolean;
    unread_only?: boolean;
    search?: string;
  }): Promise<ContactListResponse> {
    try {
      const response = await api.get('/contact/messages', { params });
      return response.data;
    } catch (error: any) {
      throw new Error('Failed to fetch contact messages');
    }
  },

  async getContactMessage(id: number): Promise<{ success: boolean; data: ContactMessage }> {
    try {
      const response = await api.get(`/contact/messages/${id}`);
      return response.data;
    } catch (error: any) {
      throw new Error('Failed to fetch contact message');
    }
  },

  async markAsSpam(id: number): Promise<{ success: boolean; message: string }> {
    try {
      const response = await api.patch(`/contact/messages/${id}/mark-spam`);
      return response.data;
    } catch (error: any) {
      throw new Error('Failed to mark message as spam');
    }
  },

  async markAsRead(id: number): Promise<{ success: boolean; message: string }> {
    try {
      const response = await api.patch(`/contact/messages/${id}/mark-read`);
      return response.data;
    } catch (error: any) {
      throw new Error('Failed to mark message as read');
    }
  },

  async deleteContactMessage(id: number): Promise<{ success: boolean; message: string }> {
    try {
      const response = await api.delete(`/contact/messages/${id}`);
      return response.data;
    } catch (error: any) {
      throw new Error('Failed to delete contact message');
    }
  },

  // Get contact settings (public endpoint)
  async getContactSettings(): Promise<ContactSettingsResponse> {
    try {
      const response = await api.get('/contact/settings');
      return response.data;
    } catch (error: any) {
      throw new Error('Failed to fetch contact settings');
    }
  },
};