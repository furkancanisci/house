import { toast } from 'sonner';

/**
 * Notification Service following Jakob's Law
 * Provides consistent notification patterns similar to popular applications
 */

interface NotificationOptions {
  duration?: number;
  action?: {
    label: string;
    onClick: () => void;
  };
}

class NotificationService {
  /**
   * Success notification - Used for completed actions
   * Following pattern: Green accent, checkmark icon, auto-dismiss
   */
  success(message: string, options?: NotificationOptions) {
    return toast.success(message, {
      duration: options?.duration || 4000,
      action: options?.action,
    });
  }

  /**
   * Error notification - Used for failed actions
   * Following pattern: Red accent, error icon, longer duration
   */
  error(message: string, options?: NotificationOptions) {
    return toast.error(message, {
      duration: options?.duration || 6000,
      action: options?.action || {
        label: 'Retry',
        onClick: () => console.log('Retry clicked'),
      },
    });
  }

  /**
   * Info notification - Used for informational messages
   * Following pattern: Blue accent, info icon, medium duration
   */
  info(message: string, options?: NotificationOptions) {
    return toast.info(message, {
      duration: options?.duration || 5000,
      action: options?.action,
    });
  }

  /**
   * Warning notification - Used for warnings
   * Following pattern: Yellow accent, warning icon, longer duration
   */
  warning(message: string, options?: NotificationOptions) {
    return toast.warning(message, {
      duration: options?.duration || 5000,
      action: options?.action,
    });
  }

  /**
   * Loading notification - Used for async operations
   * Following pattern: Shows loading state, then success/error
   */
  promise<T>(
    promise: Promise<T>,
    messages: {
      loading: string;
      success: string | ((data: T) => string);
      error: string | ((error: any) => string);
    }
  ) {
    return toast.promise(promise, messages);
  }

  /**
   * Custom notification with HTML content
   */
  custom(content: React.ReactNode, options?: NotificationOptions) {
    return toast.custom(content as any, {
      duration: options?.duration || 5000,
    });
  }

  /**
   * Dismiss a specific toast
   */
  dismiss(toastId?: string | number) {
    if (toastId) {
      toast.dismiss(toastId);
    } else {
      toast.dismiss();
    }
  }
}

// Export singleton instance
export const notification = new NotificationService();

// Common notification messages following UX best practices
export const notificationMessages = {
  // Property actions
  propertyAdded: 'Property added successfully',
  propertyUpdated: 'Property updated successfully',
  propertyDeleted: 'Property deleted successfully',
  propertyFavorited: 'Added to favorites',
  propertyUnfavorited: 'Removed from favorites',
  
  // Authentication
  loginSuccess: 'Welcome back!',
  loginError: 'Invalid email or password',
  logoutSuccess: 'Logged out successfully',
  registerSuccess: 'Account created successfully',
  emailVerified: 'Email verified successfully',
  passwordResetSent: 'Password reset link sent to your email',
  
  // Form actions
  changesSaved: 'Changes saved successfully',
  formSubmitted: 'Form submitted successfully',
  fieldRequired: 'Please fill in all required fields',
  
  // Network errors
  networkError: 'Connection error. Please check your internet',
  serverError: 'Something went wrong. Please try again',
  timeout: 'Request timed out. Please try again',
  
  // File uploads
  uploadSuccess: 'File uploaded successfully',
  uploadError: 'Failed to upload file',
  fileTooLarge: 'File size exceeds the limit',
  invalidFileType: 'Invalid file type',
  
  // Search
  searchNoResults: 'No results found',
  searchError: 'Search failed. Please try again',
  
  // Generic
  actionSuccess: 'Action completed successfully',
  actionError: 'Action failed. Please try again',
  loading: 'Loading...',
  processing: 'Processing...',
  copied: 'Copied to clipboard',
};

