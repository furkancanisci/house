// Configuration utility to access environment variables
export const config = {
  // API Configuration
  apiBaseUrl: import.meta.env.VITE_API_BASE_URL,

  // Application Configuration
  appName: import.meta.env.VITE_APP_NAME || 'Best Trend',
  appVersion: import.meta.env.VITE_APP_VERSION || '1.0.0',

  // Development Settings
  devTools: import.meta.env.VITE_DEV_TOOLS === 'true',

  // Environment detection
  isDevelopment: import.meta.env.DEV,
  isProduction: import.meta.env.PROD,
} as const;

// Validation function to ensure required environment variables are set
export const validateEnvironmentVariables = (): void => {
  const requiredVars = [
    'VITE_API_BASE_URL',
  ];

  const missingVars = requiredVars.filter(varName => !import.meta.env[varName]);

  if (missingVars.length > 0) {
    throw new Error(
      `Missing required environment variables: ${missingVars.join(', ')}.\n` +
      'Please check your .env file and ensure all required variables are set.'
    );
  }

  console.log('✅ Environment configuration validated successfully');
  console.log('🔗 API Base URL:', config.apiBaseUrl);
  console.log('🏠 App Name:', config.appName);
  console.log('📦 App Version:', config.appVersion);
  console.log('🔧 Development Mode:', config.isDevelopment);
};

export default config;