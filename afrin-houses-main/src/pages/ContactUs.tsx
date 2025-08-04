import React from 'react';
import { MapPin, Phone, Mail, Clock } from 'lucide-react';

const ContactUs: React.FC = () => {
  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-7xl mx-auto">
        {/* Page Header */}
        <div className="text-center mb-16">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">Contact Us</h1>
          <p className="text-xl text-gray-600 max-w-2xl mx-auto">
            Have questions? We're here to help. Get in touch with our team for any inquiries.
          </p>
        </div>

        <div className="max-w-6xl mx-auto">
          <div className="bg-white rounded-xl shadow-md p-8">
            <h2 className="text-2xl font-bold text-gray-900 mb-8 text-center">Get in Touch</h2>
            
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
              {/* Office Address */}
              <div className="flex flex-col items-center text-center p-4 hover:bg-gray-50 rounded-lg transition-colors">
                <div className="bg-blue-100 p-3 rounded-full mb-3">
                  <MapPin className="h-6 w-6 text-blue-600" />
                </div>
                <h3 className="text-lg font-medium text-gray-900 mb-1">Our Office</h3>
                <p className="text-gray-600 text-sm">
                  123 Real Estate Ave<br />
                  New York, NY 10001
                </p>
              </div>

              {/* Phone */}
              <div className="flex flex-col items-center text-center p-4 hover:bg-gray-50 rounded-lg transition-colors">
                <div className="bg-blue-100 p-3 rounded-full mb-3">
                  <Phone className="h-6 w-6 text-blue-600" />
                </div>
                <h3 className="text-lg font-medium text-gray-900 mb-1">Phone</h3>
                <p className="text-gray-600 text-sm">+1 (555) 123-4567</p>
                <p className="text-xs text-gray-500 mt-1">Mon-Fri, 9am-6pm</p>
              </div>

              {/* Email */}
              <div className="flex flex-col items-center text-center p-4 hover:bg-gray-50 rounded-lg transition-colors">
                <div className="bg-blue-100 p-3 rounded-full mb-3">
                  <Mail className="h-6 w-6 text-blue-600" />
                </div>
                <h3 className="text-lg font-medium text-gray-900 mb-1">Email</h3>
                <p className="text-gray-600 text-sm">info@realestate.com</p>
                <p className="text-xs text-gray-500 mt-1">24/7 Support</p>
              </div>

              {/* Business Hours */}
              <div className="flex flex-col items-center text-center p-4 hover:bg-gray-50 rounded-lg transition-colors">
                <div className="bg-blue-100 p-3 rounded-full mb-3">
                  <Clock className="h-6 w-6 text-blue-600" />
                </div>
                <h3 className="text-lg font-medium text-gray-900 mb-1">Business Hours</h3>
                <p className="text-gray-600 text-sm">
                  Mon-Fri: 9:00 AM - 6:00 PM<br />
                  Sat: 10:00 AM - 4:00 PM
                </p>
              </div>
            </div>
          </div>
        </div>

        {/* Map */}
        <div className="mt-16 bg-white rounded-xl shadow-md overflow-hidden">
          <div className="h-96 w-full">
            <iframe
              width="100%"
              height="100%"
              frameBorder="0"
              scrolling="no"
              marginHeight={0}
              marginWidth={0}
              src="https://maps.google.com/maps?q=34.8021,38.9968&z=6&output=embed&hl=tr"
              allowFullScreen
            >
              <a href="https://www.maps.ie/coordinates.html">Harita koordinatları</a>
            </iframe>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ContactUs;
