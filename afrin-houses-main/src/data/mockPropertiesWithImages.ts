// Mock properties with real images for testing
export const mockPropertiesWithImages = [
  {
    id: 1001,
    title: "فيلا فاخرة مع حديقة",
    description: "فيلا جميلة مع حديقة واسعة ومسبح",
    property_type: "villa",
    listing_type: "sale",
    price: 850000,
    bedrooms: 4,
    bathrooms: 3,
    square_feet: 3500,
    city: "دمشق",
    state: "دمشق",
    address: "شارع الثورة، دمشق",
    // Test with media array (Laravel Media Library format)
    media: [
      {
        id: 1,
        collection_name: "main_image",
        file_name: "villa_main.jpg",
        mime_type: "image/jpeg",
        original_url: "https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2075&q=80",

      },
      {
        id: 2,
        collection_name: "gallery",
        file_name: "villa_garden.jpg",
        mime_type: "image/jpeg",
        original_url: "https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2053&q=80",

      },
      {
        id: 3,
        collection_name: "gallery",
        file_name: "villa_interior.jpg",
        mime_type: "image/jpeg",
        original_url: "https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80"
      }
    ]
  },
  {
    id: 1002,
    title: "شقة حديثة في وسط المدينة",
    description: "شقة مفروشة بالكامل مع إطلالة رائعة",
    property_type: "apartment",
    listing_type: "rent",
    price: 1200,
    bedrooms: 2,
    bathrooms: 2,
    square_feet: 1200,
    city: "حلب",
    state: "حلب",
    address: "شارع النيل، حلب",
    // Test with images object structure
    images: {
      main: "https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80",
      gallery: [
        "https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80",
        "https://images.unsplash.com/photo-1484154218962-a197022b5858?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2074&q=80"
      ]
    }
  },
  {
    id: 1003,
    title: "بيت تراثي مرمم",
    description: "بيت تراثي جميل تم ترميمه بعناية",
    property_type: "house",
    listing_type: "sale",
    price: 450000,
    bedrooms: 3,
    bathrooms: 2,
    square_feet: 2200,
    city: "دمشق القديمة",
    state: "دمشق",
    address: "حي القيمرية، دمشق القديمة",
    // Test with main_image_url field
    main_image_url: "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80",
    gallery_urls: [
      "https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2084&q=80",
      "https://images.unsplash.com/photo-1600566753086-00f18fb6b3ea?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80"
    ]
  },
  {
    id: 1004,
    title: "استوديو مودرن للإيجار",
    description: "استوديو مفروش بالكامل في منطقة حيوية",
    property_type: "studio",
    listing_type: "rent",
    price: 800,
    bedrooms: 0,
    bathrooms: 1,
    square_feet: 600,
    city: "اللاذقية",
    state: "اللاذقية",
    address: "كورنيش اللاذقية",
    // Test with images array
    images: [
      "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2080&q=80",
      "https://images.unsplash.com/photo-1502672023488-70e25813eb80?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2080&q=80"
    ]
  },
  {
    id: 1005,
    title: "مكتب تجاري للبيع",
    description: "مكتب في برج تجاري حديث مع مواقف سيارات",
    property_type: "commercial",
    listing_type: "sale",
    price: 320000,
    bedrooms: 0,
    bathrooms: 2,
    square_feet: 800,
    city: "دمشق",
    state: "دمشق",
    address: "شارع بغداد، دمشق",
    // Test with mainImage field
    mainImage: "https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2069&q=80",
    photos: [
      "https://images.unsplash.com/photo-1497366811353-6870744d04b2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2069&q=80",
      "https://images.unsplash.com/photo-1497366754035-f200968a6e72?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2069&q=80"
    ]
  }
];