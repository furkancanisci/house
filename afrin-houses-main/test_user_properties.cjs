const axios = require('axios');

const API_BASE_URL = 'http://127.0.0.1:8000/api/v1';

async function testUserProperties() {
  console.log('Testing user properties functionality...');
  
  try {
    // Generate unique email for testing
    const timestamp = Date.now();
    const testEmail = `test${timestamp}@example.com`;
    
    // 1. Register a test user
        console.log('1. Registering test user...');
        const registerResponse = await axios.post(`${API_BASE_URL}/auth/register`, {
            first_name: 'Test',
            last_name: 'User',
            email: testEmail,
            password: 'password123',
            password_confirmation: 'password123',
            phone: '1234567890',
            user_type: 'general_user',
            terms_accepted: true
        });
    
    console.log('✅ User registered successfully');
    const token = registerResponse.data.access_token;
    
    // 2. Login
    console.log('2. Logging in...');
    const loginResponse = await axios.post(`${API_BASE_URL}/auth/login`, {
      email: testEmail,
      password: 'password123'
    });
    
    console.log('✅ Login successful');
    const authToken = loginResponse.data.access_token;
    
    // 3. Create a test property
    console.log('3. Creating a test property...');
    
    // Get tomorrow's date for available_from
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const availableFromDate = tomorrow.toISOString().split('T')[0]; // YYYY-MM-DD format
    
    const propertyData = {
            title: 'Test Property',
            description: 'A beautiful test property',
            price: 250000,
            property_type: 'house',
            listing_type: 'sale',
            bedrooms: 3,
            bathrooms: 2,
            square_feet: 1500,
            lot_size: 5000, // Integer value
            street_address: '123 Test Street',
            city: 'Test City',
            state: 'Test State',
            postal_code: '12345',
            country: 'Test Country',
            latitude: 40.7128,
            longitude: -74.0060,
            available_from: availableFromDate,
            amenities: ['Parking', 'Garden']
        };
    
    const createPropertyResponse = await axios.post(`${API_BASE_URL}/properties`, propertyData, {
      headers: {
        'Authorization': `Bearer ${authToken}`,
        'Content-Type': 'application/json'
      }
    });
    
    console.log('✅ Property created successfully');
        const propertyId = createPropertyResponse.data.data.id;
    
    // 4. Fetch user properties
    console.log('4. Fetching user properties...');
    const userPropertiesResponse = await axios.get(`${API_BASE_URL}/dashboard/properties`, {
      headers: {
        'Authorization': `Bearer ${authToken}`
      }
    });
    
    console.log('✅ User properties fetched successfully');
    const userProperties = userPropertiesResponse.data.data;
    
    // 5. Verify the property is in user's properties
    console.log('5. Verifying property ownership...');
    const foundProperty = userProperties.find(prop => prop.id === propertyId);
    
    if (foundProperty) {
      console.log('✅ SUCCESS: Property found in user\'s properties!');
      console.log(`Property ID: ${foundProperty.id}`);
      console.log(`Property Title: ${foundProperty.title}`);
      console.log(`Total user properties: ${userProperties.length}`);
    } else {
      console.log('❌ FAILED: Property not found in user\'s properties');
      console.log(`Created property ID: ${propertyId}`);
      console.log(`User properties count: ${userProperties.length}`);
      console.log('User properties:', userProperties.map(p => ({ id: p.id, title: p.title })));
    }
    
    // 6. Clean up - delete the test property
    console.log('6. Cleaning up test property...');
    await axios.delete(`${API_BASE_URL}/properties/${propertyId}`, {
      headers: {
        'Authorization': `Bearer ${authToken}`
      }
    });
    console.log('✅ Test property deleted');
    
  } catch (error) {
    console.log('❌ Test failed:', error.response?.data || error.message);
    if (error.response) {
      console.log('Status:', error.response.status);
      console.log('Data:', error.response.data);
    }
  }
}

testUserProperties();