import React, { useState } from 'react';
import { Phone, Mail, Clock, Send, User, MessageSquare, AtSign, MapPin, Heart } from 'lucide-react';

const ContactForm: React.FC = () => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: ''
  });

  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    
    // Simulate form submission
    await new Promise(resolve => setTimeout(resolve, 2000));
    
    // Reset form
    setFormData({
      name: '',
      email: '',
      phone: '',
      subject: '',
      message: ''
    });
    
    setIsSubmitting(false);
    alert('تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.');
  };

  return (
    <div className="mt-16 bg-white rounded-xl shadow-md overflow-hidden">
      <div className="bg-gradient-to-r from-[#067977] to-[#0a9b94] p-8">
        <h3 className="text-2xl font-bold text-white text-center mb-2">أرسل لنا رسالة</h3>
        <p className="text-white/90 text-center">نحن هنا للإجابة على جميع استفساراتك</p>
      </div>
      
      <form onSubmit={handleSubmit} className="p-8">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          {/* Name Field */}
          <div className="relative">
            <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
              الاسم الكامل *
            </label>
            <div className="relative">
              <User className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
              <input
                type="text"
                id="name"
                name="name"
                value={formData.name}
                onChange={handleChange}
                required
                className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#067977] focus:border-transparent transition-all duration-200 hover:border-[#067977]/50"
                placeholder="أدخل اسمك الكامل"
              />
            </div>
          </div>

          {/* Email Field */}
          <div className="relative">
            <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-2">
              البريد الإلكتروني *
            </label>
            <div className="relative">
              <AtSign className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
              <input
                type="email"
                id="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                required
                className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#067977] focus:border-transparent transition-all duration-200 hover:border-[#067977]/50"
                placeholder="example@email.com"
              />
            </div>
          </div>

          {/* Phone Field */}
          <div className="relative">
            <label htmlFor="phone" className="block text-sm font-medium text-gray-700 mb-2">
              رقم الهاتف
            </label>
            <div className="relative">
              <Phone className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
              <input
                type="tel"
                id="phone"
                name="phone"
                value={formData.phone}
                onChange={handleChange}
                className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#067977] focus:border-transparent transition-all duration-200 hover:border-[#067977]/50"
                placeholder="+1 (555) 123-4567"
              />
            </div>
          </div>

          {/* Subject Field */}
          <div className="relative">
            <label htmlFor="subject" className="block text-sm font-medium text-gray-700 mb-2">
              الموضوع *
            </label>
            <select
              id="subject"
              name="subject"
              value={formData.subject}
              onChange={handleChange}
              required
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#067977] focus:border-transparent transition-all duration-200 hover:border-[#067977]/50 bg-white"
            >
              <option value="">اختر الموضوع</option>
              <option value="general">استفسار عام</option>
              <option value="property">استفسار عن عقار</option>
              <option value="rent">استفسار عن الإيجار</option>
              <option value="sale">استفسار عن البيع</option>
              <option value="support">الدعم الفني</option>
              <option value="other">أخرى</option>
            </select>
          </div>
        </div>

        {/* Message Field */}
        <div className="mb-6">
          <label htmlFor="message" className="block text-sm font-medium text-gray-700 mb-2">
            الرسالة *
          </label>
          <div className="relative">
            <MessageSquare className="absolute left-3 top-3 h-5 w-5 text-gray-400" />
            <textarea
              id="message"
              name="message"
              value={formData.message}
              onChange={handleChange}
              required
              rows={5}
              className="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#067977] focus:border-transparent transition-all duration-200 hover:border-[#067977]/50 resize-none"
              placeholder="اكتب رسالتك هنا..."
            />
          </div>
        </div>

        {/* Submit Button */}
        <div className="text-center">
          <button
            type="submit"
            disabled={isSubmitting}
            className="inline-flex items-center px-8 py-3 bg-gradient-to-r from-[#067977] to-[#0a9b94] text-white font-medium rounded-lg hover:from-[#055a5c] hover:to-[#087d76] focus:outline-none focus:ring-2 focus:ring-[#067977] focus:ring-offset-2 transform transition-all duration-200 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
          >
            {isSubmitting ? (
              <>
                <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                جاري الإرسال...
              </>
            ) : (
              <>
                <Send className="h-5 w-5 mr-2" />
                إرسال الرسالة
              </>
            )}
          </button>
        </div>
      </form>
    </div>
  );
};

const ContactUs: React.FC = () => {
  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-7xl mx-auto">
        {/* Page Header */}
        <div className="text-center mb-16">
          <div className="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-[#067977] to-[#0a9b94] rounded-full mb-6 shadow-lg">
            <Heart className="h-10 w-10 text-white" />
          </div>
          <h1 className="text-5xl font-bold bg-gradient-to-r from-[#067977] to-[#0a9b94] bg-clip-text text-transparent mb-4">
            تواصل معنا
          </h1>
          <p className="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            نحن هنا لمساعدتك في العثور على منزل أحلامك. تواصل مع فريقنا المتخصص للحصول على أفضل الخدمات العقارية
          </p>
        </div>

        {/* Main Content - Side by Side Layout */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 max-w-7xl mx-auto">
          {/* Get in Touch Section */}
          <div className="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300">
            <div className="bg-gradient-to-r from-[#067977] to-[#0a9b94] p-8">
              <h2 className="text-3xl font-bold text-white text-center mb-2">تواصل معنا</h2>
              <p className="text-white/90 text-center text-lg">نحن في انتظار تواصلكم معنا</p>
            </div>
            
            <div className="p-8 space-y-8">
              {/* Phone */}
              <div className="group flex items-start space-x-4 rtl:space-x-reverse p-6 rounded-xl hover:bg-gradient-to-r hover:from-[#067977]/5 hover:to-[#0a9b94]/5 transition-all duration-300 border border-transparent hover:border-[#067977]/20">
                <div className="flex-shrink-0">
                  <div className="bg-gradient-to-r from-[#067977] to-[#0a9b94] p-4 rounded-full shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <Phone className="h-6 w-6 text-white" />
                  </div>
                </div>
                <div className="flex-1 min-w-0">
                  <h3 className="text-xl font-bold text-gray-900 mb-2">الهاتف</h3>
                  <p className="text-lg font-semibold text-[#067977] mb-1">+1 (555) 123-4567</p>
                  <p className="text-sm text-gray-500">الإثنين - الجمعة، 9 صباحاً - 6 مساءً</p>
                </div>
              </div>

              {/* Email */}
              <div className="group flex items-start space-x-4 rtl:space-x-reverse p-6 rounded-xl hover:bg-gradient-to-r hover:from-[#067977]/5 hover:to-[#0a9b94]/5 transition-all duration-300 border border-transparent hover:border-[#067977]/20">
                <div className="flex-shrink-0">
                  <div className="bg-gradient-to-r from-[#067977] to-[#0a9b94] p-4 rounded-full shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <Mail className="h-6 w-6 text-white" />
                  </div>
                </div>
                <div className="flex-1 min-w-0">
                  <h3 className="text-xl font-bold text-gray-900 mb-2">البريد الإلكتروني</h3>
                  <p className="text-lg font-semibold text-[#067977] mb-1">info@realestate.com</p>
                  <p className="text-sm text-gray-500">دعم على مدار الساعة</p>
                </div>
              </div>

              {/* Business Hours */}
              <div className="group flex items-start space-x-4 rtl:space-x-reverse p-6 rounded-xl hover:bg-gradient-to-r hover:from-[#067977]/5 hover:to-[#0a9b94]/5 transition-all duration-300 border border-transparent hover:border-[#067977]/20">
                <div className="flex-shrink-0">
                  <div className="bg-gradient-to-r from-[#067977] to-[#0a9b94] p-4 rounded-full shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <Clock className="h-6 w-6 text-white" />
                  </div>
                </div>
                <div className="flex-1 min-w-0">
                  <h3 className="text-xl font-bold text-gray-900 mb-2">ساعات العمل</h3>
                  <p className="text-gray-600 text-base leading-relaxed">
                    الإثنين - الجمعة: 9:00 ص - 6:00 م<br />
                    السبت: 10:00 ص - 4:00 م
                  </p>
                  <p className="text-[#067977] font-bold mt-3 text-base flex items-center">
                    <Heart className="h-4 w-4 mr-2 rtl:ml-2 rtl:mr-0" />
                    نحن دوماً في خدمتكم
                  </p>
                </div>
              </div>

              {/* Location */}
              <div className="group flex items-start space-x-4 rtl:space-x-reverse p-6 rounded-xl hover:bg-gradient-to-r hover:from-[#067977]/5 hover:to-[#0a9b94]/5 transition-all duration-300 border border-transparent hover:border-[#067977]/20">
                <div className="flex-shrink-0">
                  <div className="bg-gradient-to-r from-[#067977] to-[#0a9b94] p-4 rounded-full shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <MapPin className="h-6 w-6 text-white" />
                  </div>
                </div>
                <div className="flex-1 min-w-0">
                  <h3 className="text-xl font-bold text-gray-900 mb-2">الموقع</h3>
                  <p className="text-gray-600 text-base leading-relaxed">
                    عفرين، حلب<br />
                    سوريا
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* Contact Form Section */}
          <ContactForm />
        </div>
      </div>
    </div>
  );
};

export default ContactUs;
