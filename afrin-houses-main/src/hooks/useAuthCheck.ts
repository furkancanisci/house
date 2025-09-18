import { useState } from 'react';
import { useApp } from '../context/AppContext';

export interface AuthCheckResult {
  isAuthenticated: boolean;
  isEmailVerified: boolean;
  showAuthModal: boolean;
  showActivationModal: boolean;
  openAuthModal: () => void;
  closeAuthModal: () => void;
  openActivationModal: () => void;
  closeActivationModal: () => void;
  requireAuth: (action: () => void) => void;
  requireVerifiedEmail: (action: () => void) => void;
}

export const useAuthCheck = (): AuthCheckResult => {
  const { state } = useApp();
  const { user } = state;
  const [showAuthModal, setShowAuthModal] = useState(false);
  const [showActivationModal, setShowActivationModal] = useState(false);

  const isAuthenticated = !!user;
  // Fix: Handle both boolean and undefined/null values properly
  const isEmailVerified = user ? Boolean(user.is_verified) : false;

  // Debug logging
  console.log('DEBUG useAuthCheck:', {
    user: user ? { 
      id: user.id, 
      name: user.name, 
      email: user.email, 
      is_verified: user.is_verified,
      is_verified_type: typeof user.is_verified,
      is_verified_boolean: Boolean(user.is_verified)
    } : null,
    isAuthenticated,
    isEmailVerified
  });

  const openAuthModal = () => {
    setShowAuthModal(true);
  };

  const closeAuthModal = () => {
    setShowAuthModal(false);
  };

  const openActivationModal = () => {
    setShowActivationModal(true);
  };

  const closeActivationModal = () => {
    setShowActivationModal(false);
  };

  const requireAuth = (action: () => void) => {
    console.log('DEBUG requireAuth called:', { isAuthenticated });
    if (isAuthenticated) {
      action();
    } else {
      openAuthModal();
    }
  };

  const requireVerifiedEmail = (action: () => void) => {
    console.log('DEBUG requireVerifiedEmail called:', { 
      isAuthenticated, 
      isEmailVerified,
      user_is_verified: user?.is_verified,
      user_is_verified_type: typeof user?.is_verified,
      user_is_verified_boolean: Boolean(user?.is_verified)
    });
    
    if (!isAuthenticated) {
      console.log('DEBUG: User not authenticated, showing auth modal');
      openAuthModal();
    } else if (!isEmailVerified) {
      console.log('DEBUG: User authenticated but email not verified, showing activation modal');
      openActivationModal();
    } else {
      console.log('DEBUG: User authenticated and email verified, executing action');
      action();
    }
  };

  return {
    isAuthenticated,
    isEmailVerified,
    showAuthModal,
    showActivationModal,
    openAuthModal,
    closeAuthModal,
    openActivationModal,
    closeActivationModal,
    requireAuth,
    requireVerifiedEmail,
  };
};

export default useAuthCheck;