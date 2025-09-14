import React, { useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
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
  Building,
  CheckCircle
} from 'lucide-react';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Checkbox } from '../components/ui/checkbox';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../components/ui/tabs';
import { notification, notificationMessages } from '../services/notificationService';
import logo from '../assets/logo.png';
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

const Auth: React.FC = () => {
  const { t, i18n } = useTranslation();
  const { login, register, state } = useApp();
  const navigate = useNavigate();
  const location = useLocation();
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [activeTab, setActiveTab] = useState('login');

  const from = (location.state as any)?.from?.pathname || '/';

  // Redirect to home if user is already logged in
  useEffect(() => {
    if (state.user) {
      navigate('/', { replace: true });
    }
  }, [state.user, navigate]);

  const loginForm = useForm<LoginFormData>({
    resolver: zodResolver(createLoginSchema(t)),
    defaultValues: {
      email: '',
      password: '',
    },
  });

  const registerForm = useForm<RegisterFormData>({
    resolver: zodResolver(createRegisterSchema(t)),
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
        navigate(from, { replace: true });
      } else {
        notification.error(notificationMessages.loginError);
      }
    } catch (error: any) {
      console.error('Login error:', error);
      
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
    console.log('Form data:', data); // Debug log
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
        notification.success(t('auth.accountCreatedSuccess'));
        // Don't navigate immediately, let user verify email first
        setActiveTab('login');
      } else {
        notification.error(t('auth.accountCreationFailed'));
      }
    } catch (error: any) {
      console.error('Registration error:', error);
      
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

  const features = [
    t('auth.welcome.features.saveProperties'),
    t('auth.welcome.features.getNotifications'),
    t('auth.welcome.features.contactOwners'),
    t('auth.welcome.features.manageListings'),
    t('auth.welcome.features.exclusiveDeals'),
  ];

  return (
    <div className="min-h-screen bg-white flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl w-full grid grid-cols-1 lg:grid-cols-2 gap-8">
        {/* Welcome Section */}
        <div className="flex flex-col justify-center space-y-6">
          <div className="text-center lg:text-left">
            <div className="flex items-center justify-center lg:justify-start space-x-2 mb-4">
              <img src={logo} alt="Logo" className="h-10 w-auto" />
            <span className="text-2xl font-bold text-[#067977] whitespace-nowrap">
              {i18n.language === 'ar' ? 'بيست ترند' : 
               i18n.language === 'ku' ? 'Trend Baş' : 
               'Best Trend'}
            </span>
            </div>
            <h1 className="text-3xl md:text-4xl font-bold text-[#067977] mb-4">
              {t('auth.welcome.title')}
            </h1>
            <p className="text-lg text-gray-600 mb-8">
              {t('auth.welcome.subtitle')}
            </p>
          </div>

          <div className="space-y-4">
            <h3 className="text-lg font-semibold text-[#067977]">
              {t('auth.welcome.whyCreateAccount')}
            </h3>
            <ul className="space-y-3">
              {features.map((feature, index) => (
                <li key={index} className="flex items-center space-x-3">
                  <CheckCircle className="h-5 w-5 text-green-500 flex-shrink-0" />
                  <span className="text-gray-600">{feature}</span>
                </li>
              ))}
            </ul>
          </div>

          <div className="bg-[#067977]/10 rounded-lg p-6 border border-[#067977]/20">
            <h4 className="font-semibold text-[#067977] mb-2">
              {t('auth.welcome.demoAccount.title')}
            </h4>
            <p className="text-[#067977]/80 text-sm">
              {t('auth.welcome.demoAccount.description')}
            </p>
          </div>
        </div>

        {/* Auth Forms */}
        <div className="flex items-center justify-center">
          <Card className="w-full max-w-md border-[#067977]/20 shadow-lg">
            <CardHeader className="text-center bg-[#067977]/5">
              <CardTitle className="text-2xl text-[#067977]">
                {activeTab === 'login' ? t('auth.signIn') : t('auth.createAccount')}
              </CardTitle>
            </CardHeader>
            
            <CardContent>
              <Tabs value={activeTab} onValueChange={setActiveTab}>
                <TabsList className="grid w-full grid-cols-2 mb-6 bg-[#067977]/10">
                  <TabsTrigger value="login" className="data-[state=active]:bg-[#067977] data-[state=active]:text-white">{t('auth.signIn')}</TabsTrigger>
                  <TabsTrigger value="register" className="data-[state=active]:bg-[#067977] data-[state=active]:text-white">{t('auth.signUp')}</TabsTrigger>
                </TabsList>

                <TabsContent value="login">
                  <form onSubmit={loginForm.handleSubmit(onLogin)} className="space-y-4">
                    <div className="space-y-2">
                      <Label htmlFor="login-email" className="text-[#067977] font-medium">{t('auth.email')}</Label>
                      <div className="relative">
                        <Mail className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                        <Input
                          id="login-email"
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
                      <Label htmlFor="login-password" className="text-[#067977] font-medium">{t('auth.password')}</Label>
                      <div className="relative">
                        <Lock className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                        <Input
                          id="login-password"
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
                        <Label htmlFor="register-first-name" className="text-[#067977] font-medium">{t('auth.firstName')}</Label>
                        <div className="relative">
                          <User className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                          <Input
                            id="register-first-name"
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
                        <Label htmlFor="register-last-name" className="text-[#067977] font-medium">{t('auth.lastName')}</Label>
                        <div className="relative">
                          <User className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                          <Input
                            id="register-last-name"
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
                      <Label htmlFor="register-email" className="text-[#067977] font-medium">{t('auth.email')}</Label>
                      <div className="relative">
                        <Mail className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                        <Input
                          id="register-email"
                          type="email"
                          placeholder={t('auth.placeholders.enterEmail')}
                          className={`${i18n.language === 'ar' ?'pr-10 pl-10' : 'pl-10 pr-10'} border-[#067977]/20 focus:border-[#067977] focus:ring-[#067977]/20`}
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
                      <Label htmlFor="register-phone" className="text-[#067977] font-medium">{t('auth.phoneOptional')}</Label>
                      <div className="relative">
                        <Phone className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                        <Input
                          id="register-phone"
                          type="tel"
                          placeholder={t('auth.placeholders.enterPhone')}
                          className={`${i18n.language === 'ar' ? 'pr-10 pl-10' : 'pl-10 pr-10'} border-[#067977]/20 focus:border-[#067977] focus:ring-[#067977]/20`}
                          {...registerForm.register('phone')}
                        />
                      </div>
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="register-password" className="text-[#067977] font-medium">{t('auth.password')}</Label>
                      <div className="relative">
                        <Lock className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                        <Input
                          id="register-password"
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
                      <Label htmlFor="register-confirm-password" className="text-[#067977] font-medium">{t('auth.confirmPassword')}</Label>
                      <div className="relative">
                        <Lock className={`absolute ${i18n.language === 'ar' ? 'right-3' : 'left-3'} top-1/2 transform -translate-y-1/2 h-4 w-4 text-[#067977]/60`} />
                        <Input
                          id="register-confirm-password"
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
                          id="terms" 
                          checked={registerForm.watch('terms_accepted')}
                          onCheckedChange={(checked) => registerForm.setValue('terms_accepted', !!checked)}
                        />
                        <Label htmlFor="terms" className="text-sm">
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

              <div className="mt-6 text-center text-sm text-gray-600">
                {t('auth.terms.bySigningUp')}{' '}
                <a href="#" className="text-[#067977] hover:underline">
                  {t('auth.terms.termsOfService')}
                </a>{' '}
                {t('auth.terms.and')}{' '}
                <a href="#" className="text-[#067977] hover:underline">
                  {t('auth.terms.privacyPolicy')}
                </a>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
};

export default Auth;
