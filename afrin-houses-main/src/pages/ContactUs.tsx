import React, { useState } from 'react';
import { Phone, Mail, Clock, Send, User, MessageSquare, AtSign, MapPin } from 'lucide-react';
import { useTranslation } from 'react-i18next';

const ContactForm: React.FC = () => {
  const { t } = useTranslation();
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: ''
  });

  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
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
    alert(t('contact.successMessage'));
  };

  return (
    <div className="bg-gray-50 rounded-lg p-6">
      <h3 className="text-2xl font-semibold text-gray-900 mb-6">{t('contact.sendMessage')}</h3>
      
      <form onSubmit={handleSubmit}>
        <div className="space-y-4 mb-6">
          {/* Name Field */}
          <div>
            <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-1">
              {t('contact.fullName')} *
            </label>
            <input
              type="text"
              id="name"
              name="name"
              value={formData.name}
              onChange={handleChange}
              required
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-[#067977] focus:border-[#067977]"
              placeholder={t('contact.fullNamePlaceholder')}
            />
          </div>

          {/* Email Field */}
          <div>
            <label htmlFor="email" className="block text-sm font-medium text-gray-700 mb-1">
              {t('contact.emailAddress')} *
            </label>
            <input
              type="email"
              id="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              required
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-[#067977] focus:border-[#067977]"
              placeholder={t('contact.emailPlaceholder')}
            />
          </div>

          {/* Phone Field */}
          <div>
            <label htmlFor="phone" className="block text-sm font-medium text-gray-700 mb-1">
              {t('contact.phoneNumber')}
            </label>
            <input
              type="tel"
              id="phone"
              name="phone"
              value={formData.phone}
              onChange={handleChange}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-[#067977] focus:border-[#067977]"
              placeholder={t('contact.phonePlaceholder')}
            />
          </div>

          {/* Subject Field */}
          <div>
            <label htmlFor="subject" className="block text-sm font-medium text-gray-700 mb-1">
              {t('contact.subject')} *
            </label>
            <select
              id="subject"
              name="subject"
              value={formData.subject}
              onChange={handleChange}
              required
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-[#067977] focus:border-[#067977] bg-white"
            >
              <option value="">{t('contact.selectSubject')}</option>
              <option value="general">{t('contact.subjects.general')}</option>
              <option value="property">{t('contact.subjects.property')}</option>
              <option value="rent">{t('contact.subjects.rent')}</option>
              <option value="sale">{t('contact.subjects.sale')}</option>
              <option value="support">{t('contact.subjects.support')}</option>
              <option value="other">{t('contact.subjects.other')}</option>
            </select>
          </div>
        </div>

        {/* Message Field */}
        <div className="mb-6">
          <label htmlFor="message" className="block text-sm font-medium text-gray-700 mb-1">
            {t('contact.message')} *
          </label>
          <textarea
            id="message"
            name="message"
            value={formData.message}
            onChange={handleChange}
            required
            rows={4}
            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-[#067977] focus:border-[#067977] resize-none"
            placeholder={t('contact.messagePlaceholder')}
          />
        </div>

        {/* Submit Button */}
        <button
          type="submit"
          disabled={isSubmitting}
          className="w-full bg-[#067977] text-white py-2 px-4 rounded-md hover:bg-[#055a5c] focus:outline-none focus:ring-2 focus:ring-[#067977] focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          {isSubmitting ? (
            <>
              <div className="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
              {t('contact.sending')}
            </>
          ) : (
            t('contact.sendButton')
          )}
        </button>
      </form>
    </div>
  );
};

const ContactUs: React.FC = () => {
  const { t } = useTranslation();
  
  return (
    <div className="min-h-screen bg-white py-16 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl mx-auto">
        {/* Page Header */}
        <div className="text-center mb-12">
          <h1 className="text-4xl font-bold text-gray-900 mb-4">
            {t('contact.pageTitle')}
          </h1>
          <p className="text-lg text-gray-600 max-w-2xl mx-auto">
            {t('contact.pageSubtitle')}
          </p>
        </div>

        {/* Main Content */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          {/* Contact Information */}
          <div className="space-y-6">
            <h2 className="text-2xl font-semibold text-gray-900 mb-6">{t('contact.contactInfo')}</h2>
            
            {/* Phone */}
            <div className="flex items-center space-x-4 rtl:space-x-reverse">
              <div className="flex-shrink-0">
                <div className="bg-[#067977] p-3 rounded-lg">
                  <Phone className="h-5 w-5 text-white" />
                </div>
              </div>
              <div>
                <h3 className="font-medium text-gray-900">{t('contact.phone')}</h3>
                <p className="text-[#067977] font-medium">+1 (555) 123-4567</p>
              </div>
            </div>

            {/* Email */}
            <div className="flex items-center space-x-4 rtl:space-x-reverse">
              <div className="flex-shrink-0">
                <div className="bg-[#067977] p-3 rounded-lg">
                  <Mail className="h-5 w-5 text-white" />
                </div>
              </div>
              <div>
                <h3 className="font-medium text-gray-900">{t('contact.email')}</h3>
                <p className="text-[#067977] font-medium">info@realestate.com</p>
              </div>
            </div>

            {/* Business Hours */}
            <div className="flex items-center space-x-4 rtl:space-x-reverse">
              <div className="flex-shrink-0">
                <div className="bg-[#067977] p-3 rounded-lg">
                  <Clock className="h-5 w-5 text-white" />
                </div>
              </div>
              <div>
                <h3 className="font-medium text-gray-900">{t('contact.businessHours')}</h3>
                <p className="text-gray-600 whitespace-pre-line">
                  {t('contact.businessHoursText')}
                </p>
              </div>
            </div>

            {/* Location */}
            <div className="flex items-center space-x-4 rtl:space-x-reverse">
              <div className="flex-shrink-0">
                <div className="bg-[#067977] p-3 rounded-lg">
                  <MapPin className="h-5 w-5 text-white" />
                </div>
              </div>
              <div>
                <h3 className="font-medium text-gray-900">{t('contact.location')}</h3>
                <p className="text-gray-600 whitespace-pre-line">
                  {t('contact.locationText')}
                </p>
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
