import React, { useState } from 'react';
import testApiConnection from '../services/apiTest';
import { getProperties, getFeaturedProperties } from '../services/propertyService';

const ApiTestComponent: React.FC = () => {
  const [testResults, setTestResults] = useState<any>(null);
  const [loading, setLoading] = useState(false);
  const [properties, setProperties] = useState<any[]>([]);

  const runTests = async () => {
    setLoading(true);
    try {
      const results = await testApiConnection();
      setTestResults(results);
    } catch (error) {
      console.error('Test failed:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadProperties = async () => {
    setLoading(true);
    try {
      const data = await getProperties({ page: 1, perPage: 5 });
      setProperties(data);
      console.log('Loaded properties:', data);
    } catch (error) {
      console.error('Failed to load properties:', error);
    } finally {
      setLoading(false);
    }
  };

  const loadFeaturedProperties = async () => {
    setLoading(true);
    try {
      const data = await getFeaturedProperties(3);
      setProperties(data);
      console.log('Loaded featured properties:', data);
    } catch (error) {
      console.error('Failed to load featured properties:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="p-6 max-w-4xl mx-auto">
      <h1 className="text-2xl font-bold mb-6">API Connection Test</h1>
      
      <div className="space-y-4 mb-6">
        <button
          onClick={runTests}
          disabled={loading}
          className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50"
        >
          {loading ? 'Testing...' : 'Test API Connection'}
        </button>
        
        <button
          onClick={loadProperties}
          disabled={loading}
          className="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 disabled:opacity-50 ml-2"
        >
          {loading ? 'Loading...' : 'Load Properties'}
        </button>
        
        <button
          onClick={loadFeaturedProperties}
          disabled={loading}
          className="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 disabled:opacity-50 ml-2"
        >
          {loading ? 'Loading...' : 'Load Featured Properties'}
        </button>
      </div>

      {testResults && (
        <div className="bg-gray-100 p-4 rounded mb-6">
          <h2 className="text-lg font-semibold mb-2">Test Results:</h2>
          <div className="space-y-1">
            <div>Health Check: {testResults.health ? '✅ Pass' : '❌ Fail'}</div>
            <div>Properties: {testResults.properties ? '✅ Pass' : '❌ Fail'}</div>
            <div>Featured: {testResults.featured ? '✅ Pass' : '❌ Fail'}</div>
            <div>Auth: {testResults.auth ? '✅ Pass' : '❌ Fail'}</div>
          </div>
          {testResults.errors.length > 0 && (
            <div className="mt-2">
              <h3 className="font-semibold text-red-600">Errors:</h3>
              <ul className="text-red-600 text-sm">
                {testResults.errors.map((error: string, index: number) => (
                  <li key={index}>• {error}</li>
                ))}
              </ul>
            </div>
          )}
        </div>
      )}

      {properties.length > 0 && (
        <div className="bg-white border rounded p-4">
          <h2 className="text-lg font-semibold mb-4">Properties Data:</h2>
          <div className="space-y-2">
            {properties.map((property, index) => (
              <div key={property.id || index} className="border-b pb-2">
                <h3 className="font-medium">{property.title}</h3>
                <p className="text-sm text-gray-600">
                  {property.formatted_price || `$${property.price?.toLocaleString()}`} • 
                  {property.city}, {property.state} • 
                  {property.bedrooms} bed, {property.bathrooms} bath
                </p>
                <p className="text-xs text-gray-500">
                  Type: {property.property_type} • Listing: {property.listing_type}
                </p>
              </div>
            ))}
          </div>
        </div>
      )}

      <div className="mt-6 text-sm text-gray-600">
        <p><strong>API Base URL:</strong> {import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'}</p>
        <p><strong>Note:</strong> Make sure the Laravel API server is running on port 8000</p>
        <p><strong>Start Laravel:</strong> <code>php artisan serve</code></p>
      </div>
    </div>
  );
};

export default ApiTestComponent;