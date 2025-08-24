import React from 'react';
import { Link } from 'react-router-dom';
import { Building, Home, MapPin, Phone, Mail, Clock } from 'lucide-react';
import FixedImage from '../components/FixedImage';

const AboutUs: React.FC = () => {
  return (
    <div className="min-h-screen bg-gray-50">
      {/* Hero Section */}
      <div className="bg-[#067977] text-white py-20">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h1 className="text-4xl font-bold mb-4">About Us</h1>
          <p className="text-xl text-[#067977]/70 max-w-3xl mx-auto">
            Your trusted partner in finding the perfect home. We connect buyers, sellers, and renters with quality properties and exceptional service.
          </p>
        </div>
      </div>

      {/* Main Content */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        {/* Our Story */}
        <div className="mb-16">
          <h2 className="text-3xl font-bold mb-6 text-gray-900">Our Story</h2>
          <div className="grid md:grid-cols-2 gap-8 items-center">
            <div>
              <p className="text-gray-600 mb-4">
                Founded in 2023, our real estate agency has been helping clients find their dream homes and make smart investment decisions.
                What started as a small team of passionate real estate professionals has grown into a trusted name in the industry.
              </p>
              <p className="text-gray-600 mb-4">
                Our mission is to provide exceptional service, expert advice, and personalized solutions to meet all your real estate needs.
                We believe in building lasting relationships with our clients based on trust, integrity, and outstanding results.
              </p>
            </div>
            <div className="bg-gray-200 h-64 rounded-lg overflow-hidden">
              <FixedImage 
                src="https://images.unsplash.com/photo-1580587771525-78b9dba3b914?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80" 
                alt="Our Office" 
                className="w-full h-full object-cover"
              />
            </div>
          </div>
        </div>

        {/* Our Team */}
        <div className="mb-16">
          <h2 className="text-3xl font-bold mb-8 text-gray-900 text-center">Our Team</h2>
          <div className="grid md:grid-cols-3 gap-8">
            {[
              { name: 'John Doe', role: 'CEO & Founder', image: 'https://randomuser.me/api/portraits/men/1.jpg' },
              { name: 'Jane Smith', role: 'Real Estate Agent', image: 'https://randomuser.me/api/portraits/women/1.jpg' },
              { name: 'Mike Johnson', role: 'Property Manager', image: 'https://randomuser.me/api/portraits/men/2.jpg' },
            ].map((member, index) => (
              <div key={index} className="bg-white rounded-lg shadow-md overflow-hidden">
                <div className="h-64 bg-gray-200">
                  <FixedImage src={member.image} alt={member.name} className="w-full h-full object-cover" />
                </div>
                <div className="p-6">
                  <h3 className="text-xl font-semibold text-gray-900">{member.name}</h3>
                  <p className="text-[#067977]">{member.role}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Call to Action */}
        <div className="bg-[#067977] rounded-lg shadow-lg p-8 text-center text-white">
          <h2 className="text-2xl font-bold mb-4">Ready to find your dream home?</h2>
          <p className="text-[#067977]/70 mb-6 max-w-2xl mx-auto">
            Our team of experienced real estate agents is here to help you every step of the way.
          </p>
          <Link
            to="/contact"
            className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-[#067977] bg-white hover:bg-[#067977]/10 transition-colors"
          >
            Contact Us Today
          </Link>
        </div>
      </div>
    </div>
  );
};

export default AboutUs;
