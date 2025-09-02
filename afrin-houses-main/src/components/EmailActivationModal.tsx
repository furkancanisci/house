import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Mail, X, RefreshCw } from 'lucide-react';
import { Button } from './ui/button';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { authService } from '../services/authService';

interface EmailActivationModalProps {
  isOpen: boolean;
  onClose: () => void;
  title?: string;
  userEmail?: string;
}

const EmailActivationModal: React.FC<EmailActivationModalProps> = ({ 
  isOpen, 
  onClose, 
  title = 'Email Activation Required',
  userEmail
}) => {
  const { t } = useTranslation();
  const [isResending, setIsResending] = useState(false);
  const [resendSuccess, setResendSuccess] = useState(false);
  const [resendError, setResendError] = useState('');

  const handleResendEmail = async () => {
    setIsResending(true);
    setResendError('');
    setResendSuccess(false);
    
    try {
      await authService.resendVerificationEmail(userEmail);
      setResendSuccess(true);
      setTimeout(() => setResendSuccess(false), 5000);
    } catch (error) {
       setResendError(t('auth.emailActivation.resendError') || 'فشل في إرسال البريد. يرجى المحاولة مرة أخرى.');
       setTimeout(() => setResendError(''), 5000);
    } finally {
      setIsResending(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <Card className="w-full max-w-md mx-auto bg-white shadow-xl">
        <CardHeader className="relative pb-4">
          <button
            onClick={onClose}
            className="absolute right-4 top-4 text-gray-400 hover:text-gray-600 transition-colors"
          >
            <X className="h-5 w-5" />
          </button>
          <div className="flex items-center gap-3 mb-2">
            <div className="bg-orange-100 p-2 rounded-full">
              <Mail className="h-6 w-6 text-orange-600" />
            </div>
            <CardTitle className="text-xl font-semibold text-gray-900">
              {t('auth.emailActivation.title') || title}
            </CardTitle>
          </div>
        </CardHeader>
        
        <CardContent className="space-y-4">
          <div className="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <p className="text-gray-700 leading-relaxed mb-3">
              {t('auth.emailActivation.message') || 'يرجى تفعيل حسابك للمتابعة. لقد أرسلنا رسالة تفعيل إلى بريدك الإلكتروني.'}
            </p>
            <p className="text-sm text-gray-600">
              <strong>ملاحظة:</strong> {t('auth.emailActivation.spamNote') || 'يرجى التحقق من مجلد الرسائل غير المرغوب فيها (Spam) في حال عدم ظهور البريد في صندوق الوارد الرئيسي.'}
            </p>
          </div>
          
          {resendSuccess && (
            <div className="bg-green-50 border border-green-200 rounded-lg p-3">
              <p className="text-green-700 text-sm">
                ✓ {t('auth.emailActivation.resendSuccess') || 'تم إرسال بريد التفعيل بنجاح! يرجى التحقق من بريدك الإلكتروني.'}
              </p>
            </div>
          )}
          
          {resendError && (
            <div className="bg-red-50 border border-red-200 rounded-lg p-3">
              <p className="text-red-700 text-sm">
                {resendError}
              </p>
            </div>
          )}
          
          <div className="flex flex-col gap-3 pt-2">
            <Button
              onClick={handleResendEmail}
              disabled={isResending}
              className="w-full bg-[#067977] hover:bg-[#067977]/90 text-white disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isResending ? (
                <>
                  <RefreshCw className="h-4 w-4 mr-2 animate-spin" />
                  {t('auth.emailActivation.resending') || 'جاري الإرسال...'}
                </>
              ) : (
                <>
                  <Mail className="h-4 w-4 mr-2" />
                  {t('auth.emailActivation.resendButton') || 'إعادة إرسال بريد التفعيل'}
                </>
              )}
            </Button>
            
            <Button
              onClick={onClose}
              variant="outline"
              className="w-full border-gray-300 text-gray-700 hover:bg-gray-50"
            >
              {t('auth.emailActivation.close') || 'إغلاق'}
            </Button>
            
            <div className="text-center">
              <p className="text-sm text-gray-600">
                {t('auth.emailActivation.needHelp') || 'تحتاج مساعدة؟'}{' '}
                <a 
                  href="mailto:support@afrinhouses.com" 
                  className="text-[#067977] hover:underline font-medium"
                >
                  {t('auth.emailActivation.contactSupport') || 'تواصل معنا'}
                </a>
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
};

export default EmailActivationModal;