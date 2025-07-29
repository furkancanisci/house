import api from './api';

export const testApiConnection = async () => {
  const results = {
    health: false,
    properties: false,
    featured: false,
    auth: false,
    errors: [] as string[],
  };

  try {
    // Test health endpoint
    console.log('Testing API health...');
    const healthResponse = await fetch('http://localhost:8000/api/health');
    if (healthResponse.ok) {
      results.health = true;
      console.log('✅ Health check passed');
    } else {
      results.errors.push('Health check failed');
      console.log('❌ Health check failed');
    }
  } catch (error) {
    results.errors.push(`Health check error: ${error}`);
    console.log('❌ Health check error:', error);
  }

  try {
    // Test properties endpoint
    console.log('Testing properties endpoint...');
    const propertiesResponse = await api.get('/properties');
    results.properties = true;
    console.log('✅ Properties endpoint working');
    console.log('Properties data:', propertiesResponse.data);
  } catch (error) {
    results.errors.push(`Properties endpoint error: ${error}`);
    console.log('❌ Properties endpoint error:', error);
  }

  try {
    // Test featured properties endpoint
    console.log('Testing featured properties endpoint...');
    const featuredResponse = await api.get('/properties/featured');
    results.featured = true;
    console.log('✅ Featured properties endpoint working');
    console.log('Featured properties data:', featuredResponse.data);
  } catch (error) {
    results.errors.push(`Featured properties endpoint error: ${error}`);
    console.log('❌ Featured properties endpoint error:', error);
  }

  try {
    // Test auth endpoints (without actual login)
    console.log('Testing auth endpoints structure...');
    // This will likely fail with validation errors, but that means the endpoint exists
    const authResponse = await api.post('/auth/login', {});
    results.auth = true;
  } catch (error: any) {
    if (error.response && error.response.status === 422) {
      // Validation error means the endpoint exists
      results.auth = true;
      console.log('✅ Auth endpoint exists (validation error expected)');
    } else {
      results.errors.push(`Auth endpoint error: ${error}`);
      console.log('❌ Auth endpoint error:', error);
    }
  }

  console.log('\n=== API Connection Test Results ===');
  console.log('Health:', results.health ? '✅' : '❌');
  console.log('Properties:', results.properties ? '✅' : '❌');
  console.log('Featured:', results.featured ? '✅' : '❌');
  console.log('Auth:', results.auth ? '✅' : '❌');
  
  if (results.errors.length > 0) {
    console.log('\nErrors:');
    results.errors.forEach(error => console.log('❌', error));
  }

  return results;
};

// Export for use in components
export default testApiConnection;