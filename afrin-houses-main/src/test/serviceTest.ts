// Test file for feature and utility services
import featureService, { Feature } from '../services/featureService';
import utilityService, { Utility } from '../services/utilityService';

/**
 * Test function for feature service
 */
async function testFeatureService() {
  console.log('🧪 Testing Feature Service...');
  
  try {
    // Test fetching features in Arabic
    console.log('📋 Fetching features in Arabic...');
    const featuresAr = await featureService.getFeatures('ar');
    console.log(`✅ Found ${featuresAr.length} features in Arabic`);
    
    if (featuresAr.length > 0) {
      const firstFeature = featuresAr[0];
      console.log('🔍 First feature:', {
        id: firstFeature.id,
        name_ar: firstFeature.name_ar,
        name_en: firstFeature.name_en,
        name_ku: firstFeature.name_ku,
        icon: firstFeature.icon
      });
      
      // Test localized name function
      console.log('🌐 Localized names:');
      console.log('  Arabic:', featureService.getLocalizedName(firstFeature, 'ar'));
      console.log('  English:', featureService.getLocalizedName(firstFeature, 'en'));
      console.log('  Kurdish (Kurmanji):', featureService.getLocalizedName(firstFeature, 'ku'));
    }
    
    // Test fetching features in English
    console.log('\n📋 Fetching features in English...');
    const featuresEn = await featureService.getFeatures('en');
    console.log(`✅ Found ${featuresEn.length} features in English`);
    
    // Test fetching features in Kurdish (Kurmanji)
    console.log('\n📋 Fetching features in Kurdish (Kurmanji)...');
    const featuresKu = await featureService.getFeatures('ku');
    console.log(`✅ Found ${featuresKu.length} features in Kurdish`);
    
  } catch (error) {
    console.error('❌ Feature service test failed:', error);
  }
}

/**
 * Test function for utility service
 */
async function testUtilityService() {
  console.log('\n🧪 Testing Utility Service...');
  
  try {
    // Test fetching utilities in Arabic
    console.log('📋 Fetching utilities in Arabic...');
    const utilitiesAr = await utilityService.getUtilities('ar');
    console.log(`✅ Found ${utilitiesAr.length} utilities in Arabic`);
    
    if (utilitiesAr.length > 0) {
      const firstUtility = utilitiesAr[0];
      console.log('🔍 First utility:', {
        id: firstUtility.id,
        name_ar: firstUtility.name_ar,
        name_en: firstUtility.name_en,
        name_ku: firstUtility.name_ku,
        icon: firstUtility.icon
      });
      
      // Test localized name function
      console.log('🌐 Localized names:');
      console.log('  Arabic:', utilityService.getLocalizedName(firstUtility, 'ar'));
      console.log('  English:', utilityService.getLocalizedName(firstUtility, 'en'));
      console.log('  Kurdish (Kurmanji):', utilityService.getLocalizedName(firstUtility, 'ku'));
    }
    
    // Test fetching utilities in English
    console.log('\n📋 Fetching utilities in English...');
    const utilitiesEn = await utilityService.getUtilities('en');
    console.log(`✅ Found ${utilitiesEn.length} utilities in English`);
    
    // Test fetching utilities in Kurdish (Kurmanji)
    console.log('\n📋 Fetching utilities in Kurdish (Kurmanji)...');
    const utilitiesKu = await utilityService.getUtilities('ku');
    console.log(`✅ Found ${utilitiesKu.length} utilities in Kurdish`);
    
  } catch (error) {
    console.error('❌ Utility service test failed:', error);
  }
}

/**
 * Main test function
 */
export async function runServiceTests() {
  console.log('🚀 Starting Service Tests...');
  console.log('=' .repeat(50));
  
  await testFeatureService();
  await testUtilityService();
  
  console.log('\n' + '='.repeat(50));
  console.log('✨ Service tests completed!');
}

// Export individual test functions for selective testing
export { testFeatureService, testUtilityService };