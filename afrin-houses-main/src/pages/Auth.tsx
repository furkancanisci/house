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
import { toast } from 'sonner';

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
  const { t } = useTranslation();
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
        toast.success(t('auth.welcomeBack'));
        navigate(from, { replace: true });
      } else {
        toast.error(t('auth.invalidCredentials'));
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
      
      toast.error(errorMessage);
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
        terms_accepted: data.terms_accepted,
      });
      if (success) {
        toast.success(t('auth.accountCreatedSuccess'));
        // Don't navigate immediately, let user verify email first
        setActiveTab('login');
      } else {
        toast.error(t('auth.accountCreationFailed'));
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
      
      toast.error(errorMessage);
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
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl w-full grid grid-cols-1 lg:grid-cols-2 gap-8">
        {/* Welcome Section */}
        <div className="flex flex-col justify-center space-y-6">
          <div className="text-center lg:text-left">
            <div className="flex items-center justify-center lg:justify-start space-x-2 mb-4">
              <Building className="h-8 w-8 text-blue-600" />
              <span className="text-2xl font-bold text-gray-900">RealEstate</span>
            </div>
            <h1 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              {t('auth.welcome.title')}
            </h1>
            <p className="text-lg text-gray-600 mb-8">
              {t('auth.welcome.subtitle')}
            </p>
          </div>

          <div className="space-y-4">
            <h3 className="text-lg font-semibold text-gray-900">
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

          <div className="bg-blue-600 bg-opacity-10 rounded-lg p-6">
            <h4 className="font-semibold text-blue-900 mb-2">
              {t('auth.welcome.demoAccount.title')}
            </h4>
            <p className="text-blue-800 text-sm">
              {t('auth.welcome.demoAccount.description')}
            </p>
          </div>
        </div>

        {/* Auth Forms */}
        <div className="flex items-center justify-center">
          <Card className="w-full max-w-md">
            <CardHeader className="text-center">
              <CardTitle className="text-2xl">
                {activeTab === 'login' ? t('auth.signIn') : t('auth.createAccount')}
              </CardTitle>
            </CardHeader>
            
            <CardContent>
              <Tabs value={activeTab} onValueChange={setActiveTab}>
                <TabsList className="grid w-full grid-cols-2 mb-6">
                  <TabsTrigger value="login">{t('auth.signIn')}</TabsTrigger>
                  <TabsTrigger value="register">{t('auth.signUp')}</TabsTrigger>
                </TabsList>

                <TabsContent value="login">
                  <form onSubmit={loginForm.handleSubmit(onLogin)} className="space-y-4">
                    <div className="space-y-2">
                      <Label htmlFor="login-email">{t('auth.email')}</Label>
                      <div className="relative">
                        <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <Input
                          id="login-email"
                          type="email"
                          placeholder={t('auth.placeholders.enterEmail')}
                          className="pl-10"
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
                      <Label htmlFor="login-password">{t('auth.password')}</Label>
                      <div className="relative">
                        <Lock className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <Input
                          id="login-password"
                          type={showPassword ? 'text' : 'password'}
                          placeholder={t('auth.placeholders.enterPassword')}
                          className="pl-10 pr-10"
                          {...loginForm.register('password')}
                        />
                        <button
                          type="button"
                          onClick={() => setShowPassword(!showPassword)}
                          className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
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
                      className="w-full" 
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
                        <Label htmlFor="register-first-name">{t('auth.firstName')}</Label>
                        <div className="relative">
                          <User className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                          <Input
                            id="register-first-name"
                            type="text"
                            placeholder={t('auth.placeholders.enterFirstName')}
                            className="pl-10"
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
                        <Label htmlFor="register-last-name">{t('auth.lastName')}</Label>
                        <div className="relative">
                          <User className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                          <Input
                            id="register-last-name"
                            type="text"
                            placeholder={t('auth.placeholders.enterLastName')}
                            className="pl-10"
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
                      <Label htmlFor="register-email">{t('auth.email')}</Label>
                      <div className="relative">
                        <Mail className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <Input
                          id="register-email"
                          type="email"
                          placeholder={t('auth.placeholders.enterEmail')}
                          className="pl-10"
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
                      <Label htmlFor="register-phone">{t('auth.phoneOptional')}</Label>
                      <div className="relative">
                        <Phone className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <Input
                          id="register-phone"
                          type="tel"
                          placeholder={t('auth.placeholders.enterPhone')}
                          className="pl-10"
                          {...registerForm.register('phone')}
                        />
                      </div>
                    </div>

                    <div className="space-y-2">
                      <Label htmlFor="register-password">{t('auth.password')}</Label>
                      <div className="relative">
                        <Lock className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <Input
                          id="register-password"
                          type={showPassword ? 'text' : 'password'}
                          placeholder={t('auth.placeholders.createPassword')}
                          className="pl-10 pr-10"
                          {...registerForm.register('password')}
                        />
                        <button
                          type="button"
                          onClick={() => setShowPassword(!showPassword)}
                          className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
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
                      <Label htmlFor="register-confirm-password">{t('auth.confirmPassword')}</Label>
                      <div className="relative">
                        <Lock className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <Input
                          id="register-confirm-password"
                          type={showConfirmPassword ? 'text' : 'password'}
                          placeholder={t('auth.placeholders.confirmPassword')}
                          className="pl-10 pr-10"
                          {...registerForm.register('confirmPassword')}
                        />
                        <button
                          type="button"
                          onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                          className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
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
                          <a href="/terms" className="text-blue-600 hover:underline">
                            {t('auth.termsOfService')}
                          </a>{' '}
                          {t('auth.and')}{' '}
                          <a href="/privacy" className="text-blue-600 hover:underline">
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
                      className="w-full" 
                      disabled={isLoading}
                    >
                      {isLoading ? t('auth.creatingAccount') : t('auth.createAccount')}
                    </Button>
                    </div>
                  </form>
                </TabsContent>
              </Tabs>

              <div className="mt-6 text-center text-sm text-gray-600">
                By signing up, you agree to our{' '}
                <a href="#" className="text-blue-600 hover:underline">
                  Terms of Service
                </a>{' '}
                and{' '}
                <a href="#" className="text-blue-600 hover:underline">
                  Privacy Policy
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
