import { useState } from 'react';
import { useApp } from '../context/AppContext';

export interface AuthCheckResult {
  isAuthenticated: boolean;
  isEmailVerified: boolean;
  isCheckingAuth: boolean;
  showAuthModal: boolean;
  showActivationModal: boolean;
  closeAuthModal: () => void;
  closeActivationModal: () => void;
  requireAuth: (action: () => void) => void;
  requireVerifiedEmail: (action: () => void) => void;
}

export const useAuthCheck = (): AuthCheckResult => {
  const { state } = useApp();
  const { user } = state;
  const [showAuthModal, setShowAuthModal] = useState(false);
  const [showActivationModal, setShowActivationModal] = useState(false);
  const [isCheckingAuth, setIsCheckingAuth] = useState(false);

  const isAuthenticated = !!user;
  // Fix: Handle both boolean and undefined/null values properly
  const isEmailVerified = user ? Boolean(user.is_verified) : false;



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

    if (isAuthenticated) {
      action();
    } else {
      openAuthModal();
    }
  };

  const requireVerifiedEmail = (action: () => void) => {
    // Prevent multiple simultaneous checks
    if (isCheckingAuth) return;
    
    setIsCheckingAuth(true);
    
    try {
      if (!isAuthenticated) {
        openAuthModal();
      } else if (!isEmailVerified) {
        openActivationModal();
      } else {
        action();
      }
    } finally {
      // Reset checking state after a short delay to prevent rapid clicks
      setTimeout(() => setIsCheckingAuth(false), 500);
    }
  };

  return {
    isAuthenticated,
    isEmailVerified,
    isCheckingAuth,
    requireAuth,
    requireVerifiedEmail,
    showAuthModal,
    showActivationModal,
    closeAuthModal,
    closeActivationModal,
  };
};

export default useAuthCheck;