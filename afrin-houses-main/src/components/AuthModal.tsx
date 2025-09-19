import React, { useState } from 'react';
import { useApp } from '../context/AppContext';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useTranslation } from 'react-i18next';
import { 
  User, 
  Mail, 
  Lock, 
  Phone, 
  Eye, 
  EyeOff,
  X
} from 'lucide-react';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Checkbox } from './ui/checkbox';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from './ui/tabs';
import { notification, notificationMessages } from '../services/notificationService';

const createLoginSchema = (t: any) => z.object({
  email: z.string().email(t('auth.validation.emailRequired')),
  password: z.string().min(8, t('auth.validation.passwordMinLength')),
});

const createRegisterSchema = (t: any) => z.object({
  first_name: z.string().min(1, t('auth.validation.firstNameRequired')),
  last_name: z.string().min(1, t('auth.validation.lastNameRequired')),
  email: z.string().email(t('auth.validation.emailRequired')),
  phone: z.string().optional(),
  password: z.string().min(8, t('auth.validation.passwordMinLength')),
  confirmPassword: z.string(),
  terms_accepted: z.boolean()
    .refine(val => val === true, {
      message: t('auth.validation.termsRequired'),
    }),
}).refine((data) => data.password === data.confirmPassword, {
  message: t('auth.validation.passwordsNoMatch'),
  path: ['confirmPassword'],
});

type LoginFormData = z.infer<ReturnType<typeof createLoginSchema>>;
type RegisterFormData = z.infer<ReturnType<typeof createRegisterSchema>>;

interface AuthModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess?: () => void;
  title?: string;
  message?: string;
}

const AuthModal: React.FC<AuthModalProps> = ({ 
  isOpen, 
  onClose, 
  onSuccess,
  title = 'Login Required',
  message = 'Please log in to continue with this action.'
}) => {
  const { t, i18n } = useTranslation();
  const { login, register } = useApp();
  const [activeTab, setActiveTab] = useState<'login' | 'register'>('login');
  const [isLoading, setIsLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);

  const loginSchema = createLoginSchema(t);
  const registerSchema = createRegisterSchema(t);

  const loginForm = useForm<LoginFormData>({
    resolver: zodResolver(loginSchema),
    defaultValues: {
      email: '',
      password: '',
    },
  });

  const registerForm = useForm<RegisterFormData>({
    resolver: zodResolver(registerSchema),
    defaultValues: {
      first_name: '',
      last_name: '',
      email: '',
      phone: '',
      password: '',
      confirmPassword: '',
      terms_accepted: false,
    },
  });

  const onLogin = async (data: LoginFormData) => {
    setIsLoading(true);
    try {
      const success = await login(data.email, data.password);
      if (success) {
        notification.success(notificationMessages.loginSuccess);
        onSuccess?.();
        onClose();
      } else {
        notification.error(notificationMessages.loginError);
      }
    } catch (error: any) {

      
      // Extract error message from API response
      let errorMessage = t('auth.errorOccurred');
      
      if (error?.response?.data?.message) {
        errorMessage = error.response.data.message;
      } else if (error?.response?.data?.error) {
        errorMessage = error.response.data.error;
      } else if (error?.response?.data?.errors) {
        // Handle validation errors
        const errors = error.response.data.errors;
        const firstError = Object.values(errors)[0];
        if (Array.isArray(firstError) && firstError.length > 0) {
          errorMessage = firstError[0];
        }
      } else if (error?.message) {
        errorMessage = error.message;
      }
      
      notification.error(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  const onRegisterSubmit = async (data: RegisterFormData) => {
    setIsLoading(true);
    try {
      const success = await register({
        first_name: data.first_name,
        last_name: data.last_name,
        email: data.email,
        phone: data.phone || '',
        password: data.password,
        password_confirmation: data.confirmPassword,
        terms_accepted: data.terms_accepted,
      });
      
      if (success) {
        notification.success(notificationMessages.registerSuccess);
        onSuccess?.();
        onClose();
      } else {
        notification.error(notificationMessages.actionError);
      }
    } catch (error: any) {

      
      // Extract error message from API response
      let errorMessage = t('auth.errorOccurred');
      
      if (error?.response?.data?.message) {
        errorMessage = error.response.data.message;
      } else if (error?.response?.data?.error) {
        errorMessage = error.response.data.error;
      } else if (error?.response?.data?.errors) {
        // Handle validation errors
        const errors = error.response.data.errors;
        const firstError = Object.values(errors)[0];
        if (Array.isArray(firstError) && firstError.length > 0) {
          errorMessage = firstError[0];
        }
      } else if (error?.message) {
        errorMessage = error.message;
      }
      
      notification.error(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div className="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
        <Card className="border-0 shadow-none">
          <CardHeader className="text-center bg-[#067977]/5 relative">
            <button
              onClick={onClose}
              className="absolute right-4 top-4 text-gray-500 hover:text-gray-700"
            >
              <X className="h-5 w-5" />
            </button>
            <CardTitle className="text-xl text-[#067977] mb-2">
              {title}
            </CardTitle>
            <p className="text-sm text-gray-600">{message}</p>
          </CardHeader>
          
          <CardContent className="p-6">
            <Tabs value={activeTab} onValueChange={(value) => setActiveTab(value as 'login' | 'register')}>
              <TabsList className="grid w-full grid-cols-2 mb-6 bg-[#067977]/10">
                <TabsTrigger value="login" className="data-[state=active]:bg-[#067977] data-[state=active]:text-white">
                  {t('auth.signIn')}
                </TabsTrigger>
                <TabsTrigger value="register" className="data-[state=active]:bg-[#067977] data-[state=active]:text-white">
                  {t('auth.signUp')}
                </TabsTrigger>
              </TabsList>

              <TabsContent value="login">
                <form onSubmit={loginForm.handleSubmit(onLogin)} className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="modal-login-email" className="text-[#067977] font-medium">
                      {t('auth.email')}
                    </Label>
                    <div className="relative">
                      <Mail className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                      <Input
                        id="modal-login-email"
                        type="email"
                        placeholder={t('auth.placeholders.enterEmail')}
                        className={`${i18n.language === 'ar' ? 'pr-10 pl-10' : 'pl-10 pr-10'} border-[#067977]/20 focus:border-[#067977] focus:ring-[#067977]/20`}
                        {...loginForm.register('email')}
                      />
                    </div>
                    {loginForm.formState.errors.email && (
                      <p className="text-sm text-red-600">
                        {loginForm.formState.errors.email.message}
                      </p>
                    )}
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="modal-login-password" className="text-[#067977] font-medium">
                      {t('auth.password')}
                    </Label>
                    <div className="relative">
                      <Lock className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                      <Input
                        id="modal-login-password"
                        type={showPassword ? 'text' : 'password'}
                        placeholder={t('auth.placeholders.enterPassword')}
                        className={`${i18n.language === 'ar' ? 'pr-10 pl-10' : 'pl-10 pr-10'} border-[#067977]/20 focus:border-[#067977] focus:ring-[#067977]/20`}
                        {...loginForm.register('password')}
                      />
                      <button
                        type="button"
                        onClick={() => setShowPassword(!showPassword)}
                        className={`absolute ${i18n.language === 'ar' ? 'left-3' : 'right-3'} top-1/2 transform -translate-y-1/2 text-[#067977]/60 hover:text-[#067977]`}
                      >
                        {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                      </button>
                    </div>
                    {loginForm.formState.errors.password && (
                      <p className="text-sm text-red-600">
                        {loginForm.formState.errors.password.message}
                      </p>
                    )}
                  </div>

                  <Button 
                    type="submit" 
                    className="w-full bg-[#067977] hover:bg-[#067977]/90 text-white" 
                    disabled={isLoading}
                  >
                    {isLoading ? t('auth.signingIn') : t('auth.signIn')}
                  </Button>
                </form>
              </TabsContent>

              <TabsContent value="register">
                <form onSubmit={registerForm.handleSubmit(onRegisterSubmit)} className="space-y-4">
                  <div className="grid grid-cols-2 gap-4">
                    <div className="space-y-2">
                      <Label htmlFor="modal-register-first-name" className="text-[#067977] font-medium">
                        {t('auth.firstName')}
                      </Label>
                      <div className="relative">
                        <User className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                        <Input
                          id="modal-register-first-name"
                          type="text"
                          placeholder={t('auth.placeholders.enterFirstName')}
                          className={`${i18n.language === 'ar' ? 'pr-10 pl-10' : 'pl-10 pr-10'} border-[#067977]/20 focus:border-[#067977] focus:ring-[#067977]/20`}
                          {...registerForm.register('first_name')}
                        />
                      </div>
                      {registerForm.formState.errors.first_name && (
                        <p className="text-sm text-red-600">
                          {registerForm.formState.errors.first_name.message}
                        </p>
                      )}
                    </div>
                    <div className="space-y-2">
                      <Label htmlFor="modal-register-last-name" className="text-[#067977] font-medium">
                        {t('auth.lastName')}
                      </Label>
                      <div className="relative">
                        <User className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                        <Input
                          id="modal-register-last-name"
                          type="text"
                          placeholder={t('auth.placeholders.enterLastName')}
                          className={`${i18n.language === 'ar' ? 'pr-10 pl-10' : 'pl-10 pr-10'} border-[#067977]/20 focus:border-[#067977] focus:ring-[#067977]/20`}
                          {...registerForm.register('last_name')}
                        />
                      </div>
                      {registerForm.formState.errors.last_name && (
                        <p className="text-sm text-red-600">
                          {registerForm.formState.errors.last_name.message}
                        </p>
                      )}
                    </div>
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="modal-register-email" className="text-[#067977] font-medium">
                      {t('auth.email')}
                    </Label>
                    <div className="relative">
                      <Mail className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                      <Input
                        id="modal-register-email"
                        type="email"
                        placeholder={t('auth.placeholders.enterEmail')}
                        className={`${i18n.language === 'ar' ? 'pr-10 pl-10' : 'pl-10 pr-10'} border-[#067977]/20 focus:border-[#067977] focus:ring-[#067977]/20`}
                        {...registerForm.register('email')}
                      />
                    </div>
                    {registerForm.formState.errors.email && (
                      <p className="text-sm text-red-600">
                        {registerForm.formState.errors.email.message}
                      </p>
                    )}
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="modal-register-phone" className="text-[#067977] font-medium">
                      {t('auth.phoneOptional')}
                    </Label>
                    <div className="relative">
                      <Phone className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                      <Input
                        id="modal-register-phone"
                        type="tel"
                        placeholder={t('auth.placeholders.enterPhone')}
                        className={`${i18n.language === 'ar' ? 'pr-10 pl-10' : 'pl-10 pr-10'} border-[#067977]/20 focus:border-[#067977] focus:ring-[#067977]/20`}
                        {...registerForm.register('phone')}
                      />
                    </div>
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="modal-register-password" className="text-[#067977] font-medium">
                      {t('auth.password')}
                    </Label>
                    <div className="relative">
                      <Lock className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                      <Input
                        id="modal-register-password"
                        type={showPassword ? 'text' : 'password'}
                        placeholder={t('auth.placeholders.createPassword')}
                        className={`${i18n.language === 'ar' ? 'pr-10 pl-10' : 'pl-10 pr-10'} border-[#067977]/20 focus:border-[#067977] focus:ring-[#067977]/20`}
                        {...registerForm.register('password')}
                      />
                      <button
                        type="button"
                        onClick={() => setShowPassword(!showPassword)}
                        className={`absolute ${i18n.language === 'ar' ? 'left-3' : 'right-3'} top-1/2 transform -translate-y-1/2 text-[#067977]/60 hover:text-[#067977]`}
                      >
                        {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                      </button>
                    </div>
                    {registerForm.formState.errors.password && (
                      <p className="text-sm text-red-600">
                        {registerForm.formState.errors.password.message}
                      </p>
                    )}
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="modal-register-confirm-password" className="text-[#067977] font-medium">
                      {t('auth.confirmPassword')}
                    </Label>
                    <div className="relative">
                      <Lock className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                      <Input
                        id="modal-register-confirm-password"
                        type={showConfirmPassword ? 'text' : 'password'}
                        placeholder={t('auth.placeholders.confirmPassword')}
                        className={`${i18n.language === 'ar' ? 'pr-10 pl-10' : 'pl-10 pr-10'} border-[#067977]/20 focus:border-[#067977] focus:ring-[#067977]/20`}
                        {...registerForm.register('confirmPassword')}
                      />
                      <button
                        type="button"
                        onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                        className={`absolute ${i18n.language === 'ar' ? 'left-3' : 'right-3'} top-1/2 transform -translate-y-1/2 text-[#067977]/60 hover:text-[#067977]`}
                      >
                        {showConfirmPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                      </button>
                    </div>
                    {registerForm.formState.errors.confirmPassword && (
                      <p className="text-sm text-red-600">
                        {registerForm.formState.errors.confirmPassword.message}
                      </p>
                    )}
                  </div>

                  <div className="space-y-4">
                    <div className="flex items-center space-x-2">
                      <Checkbox 
                        id="modal-terms" 
                        checked={registerForm.watch('terms_accepted')}
                        onCheckedChange={(checked) => registerForm.setValue('terms_accepted', !!checked)}
                      />
                      <Label htmlFor="modal-terms" className="text-sm">
                        {t('auth.agreeToTerms')}{' '}
                        <a href="/terms" className="text-[#067977] hover:underline">
                          {t('auth.termsOfService')}
                        </a>{' '}
                        {t('auth.and')}{' '}
                        <a href="/privacy" className="text-[#067977] hover:underline">
                          {t('auth.privacyPolicy')}
                        </a>
                      </Label>
                    </div>
                    {registerForm.formState.errors.terms_accepted && (
                      <p className="text-sm text-red-600">
                        {registerForm.formState.errors.terms_accepted.message}
                      </p>
                    )}
                    <Button 
                      type="submit" 
                      className="w-full bg-[#067977] hover:bg-[#067977]/90 text-white" 
                      disabled={isLoading}
                    >
                      {isLoading ? t('auth.creatingAccount') : t('auth.createAccount')}
                    </Button>
                  </div>
                </form>
              </TabsContent>
            </Tabs>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};

export default AuthModal;