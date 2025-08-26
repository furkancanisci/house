<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class PropertySeeder extends Seeder
{
    /**
     * Available sample images for seeding
     */
    private $sampleImages = [
        'house1.svg',
        'apartment1.svg',
        'villa1.svg',
        'condo1.svg',
        'studio1.svg'
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all property owners
        $propertyOwners = User::where('user_type', 'property_owner')->get();
        
        if ($propertyOwners->isEmpty()) {
            $this->command->info('No property owners found. Please run UserSeeder first.');
            return;
        }

        // Sample properties data
        $properties = [
            [
                'title' => 'Modern Downtown Apartment',
                'description' => 'Stunning modern apartment in the heart of downtown with floor-to-ceiling windows, hardwood floors, and amazing city views. Features include granite countertops, stainless steel appliances, in-unit laundry, and building amenities like fitness center and rooftop deck.',
                'property_type' => 'apartment',
                'listing_type' => 'rent',
                'price' => 2500.00,
                'price_type' => 'monthly',
                'street_address' => '123 Main Street, Unit 15A',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'latitude' => 40.7506,
                'longitude' => -73.9756,
                'neighborhood' => 'Midtown',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'square_feet' => 1200,
                'year_built' => 2020,
                'document_type_id' => 1,
                'parking_type' => 'garage',
                'parking_spaces' => 1,
                'amenities' => ['Air Conditioning', 'Dishwasher', 'Laundry in Unit', 'Balcony', 'Hardwood Floors', 'High Ceilings', 'Elevator', 'Gym', 'Roof Deck', 'Internet'],
                'status' => 'active'
            ],
            [
                'title' => 'شقة فاخرة في دمشق',
                'description' => 'شقة فاخرة في قلب العاصمة دمشق مع إطلالة رائعة على جبل قاسيون. تحتوي على 3 غرف نوم وحمامين ومطبخ حديث وصالة واسعة. موقع مميز في منطقة المالكي.',
                'property_type' => 'apartment',
                'listing_type' => 'rent',
                'price' => 500.00,
                'price_type' => 'monthly',
                'street_address' => 'شارع المالكي، دمشق',
                'city' => 'دمشق',
                'state' => 'دمشق',
                'document_type_id' => 2,
                'postal_code' => '11000',
                'latitude' => 33.5138,
                'longitude' => 36.2765,
                'neighborhood' => 'المالكي',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'square_feet' => 1500,
                'year_built' => 2019,
                'parking_type' => 'garage',
                'parking_spaces' => 1,
                'amenities' => ['تكييف', 'تدفئة', 'مطبخ مجهز', 'إطلالة جبلية', 'انترنت', 'أمان'],
                'status' => 'active'
            ],
            [
                'title' => 'فيلا راقية في دمشق',
                'description' => 'فيلا راقية في منطقة أبو رمانة بدمشق. تتكون من 5 غرف نوم و4 حمامات وصالتين ومطبخ كبير وحديقة خاصة ومسبح. مناسبة للعائلات الكبيرة والراغبين في الفخامة.',
                'property_type' => 'villa',
                'listing_type' => 'sale',
                'price' => 300000.00,
                'price_type' => 'total',
                'street_address' => 'حي أبو رمانة، دمشق',
                'city' => 'دمشق',
                'state' => 'دمشق',
                'postal_code' => '11001',
                'latitude' => 33.5020,
                'longitude' => 36.2920,
                'neighborhood' => 'أبو رمانة',
                'bedrooms' => 5,
                'bathrooms' => 4,
                'square_feet' => 4000,
                'lot_size' => 8000,
                'year_built' => 2021,
                'parking_type' => 'garage',
                'parking_spaces' => 3,
                'amenities' => ['تكييف', 'تدفئة', 'حديقة', 'مسبح', 'مدفأة', 'أرضية رخام', 'مخزن', 'كراج', 'أمان', 'انترنت'],
                'status' => 'active',
                'document_type_id' => 3
            ],
            [
                'title' => 'بيت عائلي في دمشق',
                'description' => 'بيت عائلي مريح في منطقة الميدان بدمشق. يحتوي على 4 غرف نوم و3 حمامات وصالة كبيرة ومطبخ وفناء داخلي. قريب من الخدمات والمواصلات.',
                'property_type' => 'house',
                'listing_type' => 'rent',
                'price' => 400.00,
                'price_type' => 'monthly',
                'street_address' => 'حي الميدان، دمشق',
                'city' => 'دمشق',
                'state' => 'دمشق',
                'postal_code' => '11002',
                'latitude' => 33.4951,
                'longitude' => 36.3067,
                'neighborhood' => 'الميدان',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'square_feet' => 2200,
                'lot_size' => 4000,
                'year_built' => 2017,
                'parking_type' => 'driveway',
                'parking_spaces' => 2,
                'amenities' => ['تدفئة', 'فناء', 'قريب من المواصلات', 'هدوء', 'مخزن'],
                'status' => 'active',
                'document_type_id' => 4
            ],
            [
                'title' => 'Luxury Family Villa',
                'description' => 'Breathtaking 4-bedroom luxury villa with private pool, landscaped gardens, and panoramic mountain views. This executive home features gourmet kitchen, formal dining room, family room with fireplace, master suite with walk-in closet, and 3-car garage.',
                'property_type' => 'villa',
                'listing_type' => 'sale',
                'price' => 850000.00,
                'price_type' => 'total',
                'street_address' => '456 Oak Hill Drive',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'postal_code' => '90210',
                'latitude' => 34.0901,
                'longitude' => -118.4065,
                'neighborhood' => 'Beverly Hills',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'square_feet' => 3500,
                'lot_size' => 8000,
                'year_built' => 2018,
                'parking_type' => 'garage',
                'parking_spaces' => 3,
                'amenities' => ['Air Conditioning', 'Heating', 'Dishwasher', 'Garden', 'Fireplace', 'Hardwood Floors', 'Walk-in Closet', 'Storage', 'Garage', 'Pool', 'Security System'],
                'status' => 'active',
                'document_type_id' => 5
            ],
            [
                'title' => 'Cozy Studio Loft',
                'description' => 'Charming studio loft in trendy arts district. Open floor plan with exposed brick walls, high ceilings, and large windows. Perfect for young professionals or artists. Walking distance to galleries, cafes, and public transportation.',
                'property_type' => 'studio',
                'listing_type' => 'rent',
                'price' => 1200.00,
                'price_type' => 'monthly',
                'street_address' => '789 Artist Way',
                'city' => 'Austin',
                'state' => 'TX',
                'postal_code' => '78701',
                'latitude' => 30.2672,
                'longitude' => -97.7431,
                'neighborhood' => 'Arts District',
                'bedrooms' => 0,
                'bathrooms' => 1,
                'square_feet' => 650,
                'year_built' => 2015,
                'parking_type' => 'street',
                'parking_spaces' => 0,
                'amenities' => ['Air Conditioning', 'Hardwood Floors', 'High Ceilings', 'Internet', 'Recently Renovated'],
                'status' => 'active',
                'document_type_id' => 6
            ],
            [
                'title' => 'Suburban Family Home',
                'description' => 'Perfect family home in quiet suburban neighborhood. Features spacious living areas, updated kitchen, fenced backyard, and excellent school district. Great for families with children. Close to parks, shopping, and recreational facilities.',
                'property_type' => 'house',
                'listing_type' => 'sale',
                'price' => 415000.00,
                'price_type' => 'total',
                'street_address' => '321 Maple Lane',
                'city' => 'Phoenix',
                'state' => 'AZ',
                'postal_code' => '85001',
                'latitude' => 33.4484,
                'longitude' => -112.0740,
                'neighborhood' => 'Sunset Valley',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'square_feet' => 1800,
                'lot_size' => 6000,
                'year_built' => 2010,
                'parking_type' => 'driveway',
                'parking_spaces' => 2,
                'amenities' => ['Air Conditioning', 'Heating', 'Dishwasher', 'Garden', 'Fireplace', 'Carpet', 'Storage', 'Pet Friendly'],
                'status' => 'active',
                'document_type_id' => 7
            ],
            [
                'title' => 'Waterfront Condo',
                'description' => 'Stunning waterfront condominium with breathtaking ocean views. Features floor-to-ceiling windows, modern kitchen, spacious balcony, and access to private beach. Building amenities include concierge, valet parking, and infinity pool.',
                'property_type' => 'condo',
                'listing_type' => 'rent',
                'price' => 3200.00,
                'price_type' => 'monthly',
                'street_address' => '555 Ocean Drive, Unit 22B',
                'city' => 'Miami',
                'state' => 'FL',
                'postal_code' => '33139',
                'latitude' => 25.7617,
                'longitude' => -80.1918,
                'neighborhood' => 'South Beach',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'square_feet' => 1400,
                'year_built' => 2019,
                'parking_type' => 'garage',
                'parking_spaces' => 1,
                'amenities' => ['Air Conditioning', 'Dishwasher', 'Balcony', 'Hardwood Floors', 'High Ceilings', 'Elevator', 'Doorman', 'Concierge', 'Pool', 'Internet'],
                'status' => 'active',
                'document_type_id' => 8
            ],
            [
                'title' => 'Historic Townhouse',
                'description' => 'Beautifully restored historic townhouse in charming old town district. Original hardwood floors, exposed beams, updated kitchen and bathrooms while maintaining historic character. Walking distance to restaurants and shops.',
                'property_type' => 'townhouse',
                'listing_type' => 'sale',
                'price' => 650000.00,
                'price_type' => 'total',
                'street_address' => '876 Heritage Street',
                'city' => 'Boston',
                'state' => 'MA',
                'postal_code' => '02101',
                'latitude' => 42.3601,
                'longitude' => -71.0589,
                'neighborhood' => 'Historic District',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'square_feet' => 2200,
                'year_built' => 1890,
                'parking_type' => 'street',
                'parking_spaces' => 0,
                'amenities' => ['Heating', 'Fireplace', 'Hardwood Floors', 'High Ceilings', 'Storage', 'Recently Renovated'],
                'status' => 'active',
                'document_type_id' => 8
            ],
            [
                'title' => 'Modern Loft Space',
                'description' => 'Industrial-style loft in converted warehouse. Open floor plan with exposed brick, concrete floors, and oversized windows. Perfect for creative professionals. Includes dedicated workspace area and modern kitchen.',
                'property_type' => 'loft',
                'listing_type' => 'rent',
                'price' => 1800.00,
                'price_type' => 'monthly',
                'street_address' => '999 Factory Street, Unit 5',
                'city' => 'Portland',
                'state' => 'OR',
                'postal_code' => '97201',
                'latitude' => 45.5152,
                'longitude' => -122.6784,
                'neighborhood' => 'Industrial District',
                'bedrooms' => 1,
                'bathrooms' => 1,
                'square_feet' => 1000,
                'year_built' => 2016,
                'parking_type' => 'garage',
                'parking_spaces' => 1,
                'amenities' => ['Air Conditioning', 'High Ceilings', 'Storage', 'Internet', 'New Construction'],
                'status' => 'active',
                'document_type_id' => 9
            ],
            [
                'title' => 'Commercial Office Space',
                'description' => 'Premium commercial office space in business district. Open floor plan suitable for various business types. Features include conference room, reception area, kitchenette, and ample parking. Ready for immediate occupancy.',
                'property_type' => 'commercial',
                'listing_type' => 'rent',
                'price' => 4500.00,
                'price_type' => 'monthly',
                'street_address' => '1200 Business Plaza',
                'city' => 'Chicago',
                'state' => 'IL',
                'postal_code' => '60601',
                'latitude' => 41.8781,
                'longitude' => -87.6298,
                'neighborhood' => 'Financial District',
                'bedrooms' => 0,
                'bathrooms' => 2,
                'square_feet' => 2500,
                'year_built' => 2012,
                'parking_type' => 'garage',
                'parking_spaces' => 4,
                'amenities' => ['Air Conditioning', 'Heating', 'Elevator', 'Security System', 'Internet'],
                'status' => 'active',
                'document_type_id' => 10
            ],
            // Syrian Properties
            [
                'title' => 'شقة حديثة في عفرين',
                'description' => 'شقة حديثة ومجهزة بالكامل في قلب مدينة عفرين. تحتوي على غرفتي نوم وحمامين ومطبخ مجهز وصالة واسعة. موقع ممتاز قريب من الخدمات والمواصلات.',
                'property_type' => 'apartment',
                'listing_type' => 'rent',
                'price' => 300.00,
                'price_type' => 'monthly',
                'street_address' => 'شارع الشهداء، عفرين',
                'city' => 'عفرين',
                'state' => 'حلب',
                'postal_code' => '21000',
                'latitude' => 36.5167,
                'longitude' => 36.8667,
                'neighborhood' => 'وسط المدينة',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'square_feet' => 1000,
                'year_built' => 2018,
                'parking_type' => 'street',
                'parking_spaces' => 1,
                'amenities' => ['تكييف', 'تدفئة', 'مطبخ مجهز', 'انترنت', 'أمان'],
                'status' => 'active',
                'document_type_id' => 3
            ],
            [
                'title' => 'فيلا فاخرة في حلب',
                'description' => 'فيلا فاخرة في أحد أرقى أحياء حلب. تتكون من 4 غرف نوم و3 حمامات وصالتين ومطبخ كبير وحديقة خاصة. مناسبة للعائلات الكبيرة.',
                'property_type' => 'villa',
                'listing_type' => 'sale',
                'price' => 150000.00,
                'price_type' => 'total',
                'street_address' => 'حي الفرقان، حلب',
                'city' => 'حلب',
                'state' => 'حلب',
                'postal_code' => '21001',
                'latitude' => 36.2021,
                'longitude' => 37.1343,
                'neighborhood' => 'الفرقان',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'square_feet' => 2500,
                'lot_size' => 5000,
                'year_built' => 2020,
                'parking_type' => 'garage',
                'parking_spaces' => 2,
                'amenities' => ['تكييف', 'تدفئة', 'حديقة', 'مدفأة', 'أرضية رخام', 'مخزن', 'كراج', 'أمان'],
                'status' => 'active',
                'document_type_id' => 3
            ],
            [
                'title' => 'بيت تراثي في عفرين',
                'description' => 'بيت تراثي جميل في عفرين مع إطلالة رائعة على الجبال. يحتوي على 3 غرف نوم وحمامين وصالة كبيرة وفناء داخلي. مثالي للعائلات التي تحب الطابع التراثي.',
                'property_type' => 'house',
                'listing_type' => 'rent',
                'price' => 250.00,
                'price_type' => 'monthly',
                'street_address' => 'حي الزيتون، عفرين',
                'city' => 'عفرين',
                'state' => 'حلب',
                'postal_code' => '21002',
                'latitude' => 36.5200,
                'longitude' => 36.8700,
                'neighborhood' => 'الزيتون',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'square_feet' => 1800,
                'lot_size' => 3000,
                'year_built' => 2015,
                'parking_type' => 'driveway',
                'parking_spaces' => 1,
                'amenities' => ['تدفئة', 'فناء', 'إطلالة جبلية', 'هدوء', 'قريب من الطبيعة'],
                'status' => 'active',
                'document_type_id' => 1
            ],
        ];

        // Create properties using raw SQL with proper boolean values
        foreach ($properties as $index => $data) {
            $userId = $propertyOwners->random()->id;
            $slug = Str::slug($data['title']) . '-' . ($index + 1);
            $publishedAt = now()->subDays(rand(1, 30));
            $viewsCount = rand(0, 500);
            
            // Prepare data with proper boolean values
            $data['user_id'] = $userId;
            $data['slug'] = $slug;
            $data['published_at'] = $publishedAt;
            $data['views_count'] = $viewsCount;
            $data['amenities'] = json_encode($data['amenities']);
            $data['nearby_places'] = json_encode([
                ['name' => 'Starbucks', 'type' => 'cafe', 'distance' => 0.2],
                ['name' => 'Metro Station', 'type' => 'transport', 'distance' => 0.3],
                ['name' => 'Whole Foods', 'type' => 'grocery', 'distance' => 0.5],
                ['name' => 'Central Park', 'type' => 'park', 'distance' => 0.8],
                ['name' => 'ABC Elementary', 'type' => 'school', 'distance' => 1.2],
            ]);
            
            // Mark first 3 properties as featured
            $data['is_featured'] = $index < 3;
            
  
            
            // Build and execute raw SQL
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $values = array_values($data);
            
            $sql = "INSERT INTO properties ($columns, created_at, updated_at) 
                    VALUES ($placeholders, NOW(), NOW())";
                    
            \DB::insert($sql, $values);
            
            // Get the created property ID
            $propertyId = \DB::getPdo()->lastInsertId();
            
            // Add images to the property
            $this->addImagesToProperty($propertyId);
        }

        // Create some draft properties using raw SQL
        $draftProperties = [
            [
                'title' => 'Draft Property - Luxury Penthouse',
                'description' => 'This is a draft property that hasn\'t been published yet.',
                'property_type' => 'apartment',
                'listing_type' => 'sale',
                'price' => 1200000.00,
                'status' => 'draft',
            ],
            [
                'title' => 'Draft Property - Traditional House',
                'description' => 'Another draft property in progress.',
                'property_type' => 'house',
                'listing_type' => 'rent',
                'price' => 1500.00,
                'status' => 'draft',
            ],
        ];

        foreach ($draftProperties as $index => $data) {
            $data['user_id'] = $propertyOwners->random()->id;
            $data['slug'] = Str::slug($data['title']) . '-draft-' . ($index + 1);
            $data['street_address'] = '123 Draft Street';
            $data['city'] = 'Draft City';
            $data['state'] = 'CA';
            $data['postal_code'] = '90000';
            $data['bedrooms'] = 2;
            $data['bathrooms'] = 1;
            $data['square_feet'] = 1000;
            $data['year_built'] = 2020;
            $data['amenities'] = json_encode(['Air Conditioning', 'Heating']);
            
            // Build and execute raw SQL
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $values = array_values($data);
            
            $sql = "INSERT INTO properties ($columns, created_at, updated_at) 
                    VALUES ($placeholders, NOW(), NOW())";
                    
            \DB::insert($sql, $values);
        }
    }

    /**
     * Add fake images to a property
     */
    private function addImagesToProperty($propertyId): void
    {
        $property = Property::find($propertyId);
        if (!$property) {
            return;
        }

        $imagesPath = database_path('seeders/images');
        
        // Check if images directory exists
        if (!File::exists($imagesPath)) {
            $this->command->warn("Images directory not found at: $imagesPath");
            return;
        }

        // Get random number of images (3-5)
        $imageCount = rand(3, 5);
        $selectedImages = collect($this->sampleImages)->random($imageCount);
        
        $isFirstImage = true;
        
        foreach ($selectedImages as $imageName) {
            $imagePath = $imagesPath . DIRECTORY_SEPARATOR . $imageName;
            
            if (File::exists($imagePath)) {
                try {
                    // Create a temporary copy of the image with a unique name
                    $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . '_' . $imageName;
                    File::copy($imagePath, $tempPath);
                    
                    // Add to appropriate collection
                    if ($isFirstImage) {
                        // First image goes to main_image collection
                        $property->addMedia($tempPath)
                            ->usingName("Main image for {$property->title}")
                            ->usingFileName(uniqid() . '_main_' . $imageName)
                            ->toMediaCollection('main_image');
                        $isFirstImage = false;
                    } else {
                        // Rest go to images collection
                        $property->addMedia($tempPath)
                            ->usingName("Gallery image for {$property->title}")
                            ->usingFileName(uniqid() . '_gallery_' . $imageName)
                            ->toMediaCollection('images');
                    }
                    
                    // Clean up temporary file
                    if (File::exists($tempPath)) {
                        File::delete($tempPath);
                    }
                    
                } catch (\Exception $e) {
                    $this->command->warn("Failed to add image $imageName to property {$property->id}: " . $e->getMessage());
                }
            }
        }
        
        $this->command->info("Added {$imageCount} images to property: {$property->title}");
    }
}
