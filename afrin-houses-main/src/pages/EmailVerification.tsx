import React, { useState, useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { CheckCircle, XCircle, Loader2, Mail, RefreshCw, Home, ArrowRight } from 'lucide-react';
import { Button } from '../components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { toast } from 'sonner';
import { useApp } from '../context/AppContext';
import { useTranslation } from 'react-i18next';
import api from '../services/api';

interface VerificationResponse {
  success: boolean;
  message: string;
  code: string;
  user?: any;
}

interface VerificationStatus {
  is_verified: boolean;
  total_attempts: number;
  sent_count: number;
  failed_count: number;
  expired_count: number;
  verified_count: number;
  last_attempt_at: string | null;
  can_resend: boolean;
  retry_after?: number;
  retry_after_minutes?: number;
}

const EmailVerification: React.FC = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { state, dispatch } = useApp();
  const { t } = useTranslation();
  const [verificationStatus, setVerificationStatus] = useState<'loading' | 'success' | 'error' | 'pending'>('loading');
  const [message, setMessage] = useState('');
  const [isResending, setIsResending] = useState(false);
  const [verificationStats, setVerificationStats] = useState<VerificationStatus | null>(null);

  // Extract verification parameters from URL
  const id = searchParams.get('id');
  const hash = searchParams.get('hash');
  const token = searchParams.get('token');
  const data = searchParams.get('data');
  const signature = searchParams.get('signature');

  useEffect(() => {
    console.log('EmailVerification component loaded with params:', {
      id, hash, token, data: data ? 'present' : 'missing', 
      signature: signature ? 'present' : 'missing',
      userVerified: state.user?.is_verified
    });
    
    // If user is already verified, don't attempt verification
    if (state.user?.is_verified) {
      console.log('User is already verified, skipping verification process');
      return;
    }
    
    if ((id && hash && token) || (data && signature)) {
      verifyEmail();
    } else {
      setVerificationStatus('error');
      setMessage(t('emailVerification.invalidLink'));
      toast.error(t('emailVerification.invalidLink'));
    }
  }, [id, hash, token, data, signature, state.user?.is_verified]);

  useEffect(() => {
    if (state.user && !state.user.is_verified) {
      loadVerificationStatus();
    }
  }, [state.user]);

  const verifyEmail = async () => {
    try {
      // Double check if user is already verified before making API call
      if (state.user?.is_verified) {
        console.log('User is already verified, aborting verification process');
        return;
      }
      
      setVerificationStatus('loading');
      
      let response;
      
      if (data && signature) {
        // New encrypted format
        response = await api.post('/auth/email/verify', {
          data,
          signature
        });
      } else {
        // Old format for backward compatibility
        response = await api.post('/auth/email/verify', {
          id,
          hash,
          token
        });
      }

      const result: VerificationResponse = response.data;

      if (result.success) {
        setVerificationStatus('success');
        setMessage(t('emailVerification.verificationSuccessMessage'));
        
        // Update user in context if verification was successful
        if (result.user) {
          dispatch({ type: 'SET_USER', payload: { ...state.user, ...result.user, is_verified: true } });
        }
        
        toast.success(t('emailVerification.verificationSuccessMessage'));
        
        // Redirect to home after 3 seconds
        setTimeout(() => {
          navigate('/', { replace: true });
        }, 3000);
      } else {
        setVerificationStatus('error');
        setMessage(result.message || t('emailVerification.verificationFailedMessage'));
        toast.error(result.message || t('emailVerification.verificationFailedMessage'));
      }
    } catch (error: any) {
      console.error('Email verification error:', error);
      console.error('Error details:', {
        status: error.response?.status,
        statusText: error.response?.statusText,
        data: error.response?.data,
        message: error.message
      });
      
      setVerificationStatus('error');
      
      let errorMessage = t('emailVerification.verificationFailedMessage');
      
      // Handle specific error cases
      if (error.response?.status === 422 && error.response?.data?.message?.includes('already verified')) {
        // User is already verified - this should not happen with our new logic, but just in case
        console.log('API returned already verified status');
        if (state.user) {
          dispatch({ type: 'SET_USER', payload: { ...state.user, is_verified: true } });
        }
        return;
      } else if (error.response?.status === 500) {
        errorMessage = t('emailVerification.serverError');
      } else if (error.response?.status === 400) {
        errorMessage = error.response?.data?.message || t('emailVerification.invalidLink');
      } else if (error.response?.data?.message) {
        errorMessage = error.response.data.message;
      } else if (error.code === 'NETWORK_ERROR' || error.message.includes('Network Error')) {
        errorMessage = t('emailVerification.networkError');
      }
      
      setMessage(errorMessage);
      toast.error(errorMessage);
    }
  };

  const loadVerificationStatus = async () => {
    try {
      const response = await api.get('/auth/email/verification-status');
      setVerificationStats(response.data);
    } catch (error) {
      console.error('Failed to load verification status:', error);
    }
  };

  const resendVerificationEmail = async () => {
    try {
      setIsResending(true);
      
      const response = await api.post('/auth/email/resend-verification');
      
      if (response.data.success) {
        toast.success(t('emailVerification.verificationSent'));
        loadVerificationStatus(); // Refresh status
      } else {
        toast.error(response.data.message || t('emailVerification.verificationFailedMessage'));
      }
    } catch (error: any) {
      console.error('Resend verification error:', error);
      
      if (error.response?.status === 429) {
        toast.error(t('emailVerification.tooManyAttempts'));
      } else {
        toast.error(error.response?.data?.message || t('emailVerification.verificationFailedMessage'));
      }
    } finally {
      setIsResending(false);
    }
  };

  const getStatusIcon = () => {
    switch (verificationStatus) {
      case 'loading':
        return <Loader2 className="h-12 w-12 text-white animate-spin" />;
      case 'success':
        return <CheckCircle className="h-12 w-12 text-white" />;
      case 'error':
        return <XCircle className="h-12 w-12 text-white" />;
      case 'pending':
        return <Mail className="h-12 w-12 text-white" />;
      default:
        return <Mail className="h-12 w-12 text-white" />;
    }
  };

  const getStatusColor = () => {
    switch (verificationStatus) {
      case 'success':
        return 'text-green-600';
      case 'error':
        return 'text-red-600';
      case 'pending':
        return 'text-yellow-600';
      default:
        return 'text-[#067977]';
    }
  };

  // If user is already verified, show success message
  if (state.user?.is_verified) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-green-50 via-blue-50 to-purple-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-lg w-full">
          <Card className="text-center shadow-2xl border-0 bg-white/80 backdrop-blur-sm">
            <CardHeader className="pb-4">
              <div className="flex justify-center mb-6">
                <div className="relative">
                  <div className="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-20"></div>
                  <div className="relative bg-gradient-to-r from-green-400 to-emerald-500 rounded-full p-4">
                    <CheckCircle className="h-12 w-12 text-white" />
                  </div>
                </div>
              </div>
              <CardTitle className="text-3xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
                ✅ {t('emailVerification.verificationSuccess')}
              </CardTitle>
              <p className="text-lg text-gray-600 mt-2">
                {t('emailVerification.welcomeMessage')}
              </p>
            </CardHeader>
            <CardContent className="pt-2">
              <div className="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6 mb-6">
                <p className="text-green-800 font-medium text-lg leading-relaxed">
                  🎉 {t('emailVerification.accountActivated')}
                </p>
                <p className="text-green-700 mt-2">
                  {t('emailVerification.enjoyAllFeatures')}
                </p>
              </div>
              <div className="space-y-3">
                <Button 
                  onClick={() => navigate('/')} 
                  className="w-full bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
                  size="lg"
                >
                  <Home className="ml-2 h-5 w-5" />
                  {t('emailVerification.goToHomepage')}
                  <ArrowRight className="mr-2 h-5 w-5" />
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-[#067977]/10 via-purple-50 to-pink-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-lg w-full">
        <Card className="text-center shadow-2xl border-0 bg-white/80 backdrop-blur-sm">
          <CardHeader className="pb-4">
            <div className="flex justify-center mb-6">
              <div className="relative">
                {verificationStatus === 'loading' && (
                  <div className="absolute inset-0 bg-[#067977] rounded-full animate-ping opacity-20"></div>
                )}
                {verificationStatus === 'success' && (
                  <div className="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-20"></div>
                )}
                {verificationStatus === 'error' && (
                  <div className="absolute inset-0 bg-red-400 rounded-full animate-pulse opacity-20"></div>
                )}
                <div className={`relative rounded-full p-4 ${
                  verificationStatus === 'loading' ? 'bg-gradient-to-r from-[#067977] to-[#067977]/80' :
                  verificationStatus === 'success' ? 'bg-gradient-to-r from-green-400 to-emerald-500' :
                  verificationStatus === 'error' ? 'bg-gradient-to-r from-red-400 to-red-500' :
                  'bg-gradient-to-r from-yellow-400 to-orange-500'
                }`}>
                  {getStatusIcon()}
                </div>
              </div>
            </div>
            <CardTitle className={`text-3xl font-bold ${
              verificationStatus === 'loading' ? 'bg-gradient-to-r from-[#067977] to-[#067977]/80 bg-clip-text text-transparent' :
              verificationStatus === 'success' ? 'bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent' :
              verificationStatus === 'error' ? 'bg-gradient-to-r from-red-600 to-red-700 bg-clip-text text-transparent' :
              'bg-gradient-to-r from-yellow-600 to-orange-600 bg-clip-text text-transparent'
            }`}>
              {verificationStatus === 'loading' && `⏳ ${t('emailVerification.verifying')}`}
               {verificationStatus === 'success' && `✅ ${t('emailVerification.verificationSuccess')}`}
               {verificationStatus === 'error' && `❌ ${t('emailVerification.verificationFailed')}`}
               {verificationStatus === 'pending' && `📧 ${t('emailVerification.pendingVerification')}`}
            </CardTitle>
            {verificationStatus === 'loading' && (
              <p className="text-lg text-gray-600 mt-2">
                {t('emailVerification.pleaseWait')}
              </p>
            )}
            {verificationStatus === 'success' && (
               <p className="text-lg text-gray-600 mt-2">
                 {t('emailVerification.welcomeMessage')}
               </p>
             )}
          </CardHeader>
          <CardContent>
            {verificationStatus === 'loading' && (
              <div className="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 rounded-xl p-6 mb-6 shadow-inner">
                <div className="flex items-center justify-center mb-3">
                  <div className="bg-[#067977]/20 rounded-full p-2">
                <Loader2 className="h-6 w-6 text-[#067977] animate-spin" />
                  </div>
                </div>
                <p className="text-[#067977] font-semibold text-lg mb-2">⏳ جاري التحقق من البريد الإلكتروني</p>
              <p className="text-[#067977]/80 text-sm leading-relaxed">
                  {message || 'يرجى الانتظار بينما نتحقق من بريدك الإلكتروني...'}
                </p>
              </div>
            )}
            
            {verificationStatus === 'pending' && (
              <div className="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl p-6 mb-6 shadow-inner">
                <div className="flex items-center justify-center mb-3">
                  <div className="bg-yellow-100 rounded-full p-2">
                    <Mail className="h-6 w-6 text-yellow-600" />
                  </div>
                </div>
                <p className="text-yellow-800 font-semibold text-lg mb-2">📧 {t('emailVerification.pendingVerification')}</p>
                <p className="text-yellow-700 text-sm leading-relaxed">
                  {message || t('emailVerification.checkEmailInstructions')}
                </p>
              </div>
            )}
            
            {(verificationStatus === 'error' || verificationStatus === 'success') && message && (
              <p className="text-gray-600 mb-4">
                {message}
              </p>
            )}
            
            {/* Success message with enhanced styling */}
            {verificationStatus === 'success' && (
              <div className="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6 mb-6 shadow-inner">
                <div className="flex items-center justify-center mb-3">
                  <div className="bg-green-100 rounded-full p-2">
                    <CheckCircle className="h-6 w-6 text-green-600" />
                  </div>
                </div>
                <p className="text-green-800 font-semibold text-lg mb-2">🎉 {t('emailVerification.accountActivated')}</p>
                <p className="text-green-700 text-sm leading-relaxed">
                  {t('emailVerification.enjoyAllFeatures')}
                </p>
              </div>
            )}
            
            {/* Error message with enhanced styling */}
            {verificationStatus === 'error' && (
              <div className="bg-gradient-to-r from-red-50 to-pink-50 border border-red-200 rounded-xl p-6 mb-6 shadow-inner">
                <div className="flex items-center justify-center mb-3">
                  <div className="bg-red-100 rounded-full p-2">
                    <XCircle className="h-6 w-6 text-red-600" />
                  </div>
                </div>
                <p className="text-red-800 font-semibold text-lg mb-2">⚠️ {t('emailVerification.verificationFailed')}</p>
                <p className="text-red-700 text-sm leading-relaxed">
                  {message || t('emailVerification.verificationFailedMessage')}
                </p>
              </div>
            )}
            
            {/* Show verification details for debugging */}
            {verificationStatus === 'error' && (data || id) && (
              <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-sm">
                <h4 className="font-semibold text-red-800 mb-2">تفاصيل التحقق:</h4>
                <div className="space-y-1 text-red-700 text-right">
                  {data && <div>تنسيق البيانات: مشفر</div>}
                  {id && <div>معرف المستخدم: {id}</div>}
                  <div>حالة الطلب: فشل</div>
                  <div className="mt-2 text-xs">
                    إذا استمرت المشكلة، يرجى الاتصال بالدعم الفني مع هذه المعلومات.
                  </div>
                </div>
              </div>
            )}

            {/* Show verification statistics if available */}
            {verificationStats && !state.user?.is_verified && (
              <div className="bg-gray-50 rounded-lg p-4 mb-6 text-sm">
                <h4 className="font-semibold mb-2">إحصائيات التحقق:</h4>
                <div className="space-y-1 text-right">
                  <div>إجمالي المحاولات: {verificationStats.total_attempts}</div>
                  <div>الرسائل المرسلة: {verificationStats.sent_count}</div>
                  <div>الرسائل الفاشلة: {verificationStats.failed_count}</div>
                  {verificationStats.last_attempt_at && (
                    <div>آخر محاولة: {new Date(verificationStats.last_attempt_at).toLocaleString('ar-SA')}</div>
                  )}
                </div>
              </div>
            )}

            <div className="space-y-3">
              {/* Show resend button if verification failed or pending */}
              {(verificationStatus === 'error' || verificationStatus === 'pending') && state.user && !state.user.is_verified && (
                <Button 
                  onClick={resendVerificationEmail}
                  disabled={isResending || (verificationStats && !verificationStats.can_resend)}
                  className="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                  size="lg"
                >
                  {isResending ? (
                    <>
                      <Loader2 className="ml-2 h-5 w-5 animate-spin" />
                      {t('emailVerification.sending')}
                    </>
                  ) : (
                    <>
                      <Mail className="ml-2 h-5 w-5" />
                      {t('emailVerification.resendVerification')}
                    </>
                  )}
                </Button>
              )}

              {/* Show retry info if rate limited */}
              {verificationStats && !verificationStats.can_resend && verificationStats.retry_after_minutes && (
                <p className="text-sm text-yellow-600">
                  يمكنك إعادة المحاولة خلال {verificationStats.retry_after_minutes} دقيقة
                </p>
              )}

              <Button 
                onClick={() => navigate('/')} 
                className={`w-full ${verificationStatus === 'success' ? 'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105' : 'border-2 border-gray-300 hover:border-gray-400 text-gray-700 hover:text-gray-800 font-semibold py-3 px-6 rounded-xl transition-all duration-300 hover:bg-gray-50'}`}
                variant={verificationStatus === 'success' ? 'default' : 'outline'}
                size="lg"
              >
                {verificationStatus === 'success' ? (
                  <>
                    <Home className="ml-2 h-5 w-5" />
                    {t('emailVerification.goToHomepage')}
                    <ArrowRight className="mr-2 h-5 w-5" />
                  </>
                ) : (
                  <>
                    <Home className="ml-2 h-5 w-5" />
                    {t('emailVerification.goToHomepage')}
                  </>
                )}
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default EmailVerification;