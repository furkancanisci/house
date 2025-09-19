import React, { useState, useEffect } from 'react';
import { Phone, Mail, Clock, Send, User, AtSign, MapPin, Facebook, Twitter, Linkedin } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { contactService, ContactFormData, ContactSettings } from '../services/contactService';

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
  const [submitMessage, setSubmitMessage] = useState<{ type: 'success' | 'error'; message: string } | null>(null);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    // Clear any previous messages when user starts typing
    if (submitMessage) {
      setSubmitMessage(null);
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setSubmitMessage(null);
    
    try {
      const contactData: ContactFormData = {
        name: formData.name,
        email: formData.email,
        phone: formData.phone || undefined,
        subject: formData.subject,
        message: formData.message
      };

      const response = await contactService.submitContactForm(contactData);
      
      if (response.success) {
        // Reset form on success
        setFormData({
          name: '',
          email: '',
          phone: '',
          subject: '',
          message: ''
        });
        setSubmitMessage({
          type: 'success',
          message: response.message
        });
      } else {
        setSubmitMessage({
          type: 'error',
          message: response.message || 'Failed to send message'
        });
      }
    } catch (error: any) {
      setSubmitMessage({
        type: 'error',
        message: 'An error occurred while sending your message. Please try again.'
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="bg-gray-50 rounded-lg p-6">
      <h3 className="text-2xl font-semibold text-gray-900 mb-6">{t('contact.sendMessage')}</h3>
      
      {/* Display success/error messages */}
      {submitMessage && (
        <div className={`mb-6 p-4 rounded-md ${
          submitMessage.type === 'success' 
            ? 'bg-green-50 border border-green-200 text-green-800'
            : 'bg-red-50 border border-red-200 text-red-800'
        }`}>
          <p className="text-sm font-medium">{submitMessage.message}</p>
        </div>
      )}
      
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
  const [contactSettings, setContactSettings] = useState<ContactSettings>({});
  const [settingsLoading, setSettingsLoading] = useState(true);
  
  useEffect(() => {
    const fetchContactSettings = async () => {
      try {
        const response = await contactService.getContactSettings();
        if (response.success) {
          setContactSettings(response.data);
        }
      } catch (error) {

      } finally {
        setSettingsLoading(false);
      }
    };

    fetchContactSettings();
  }, []);
  
  if (settingsLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-white">
        <div className="text-center">
          <div className="w-16 h-16 border-4 border-[#067977] border-t-transparent rounded-full animate-spin mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading contact information...</p>
        </div>
      </div>
    );
  }
  
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
            {contactSettings.phone && (
              <div className="flex items-center space-x-4 rtl:space-x-reverse">
                <div className="flex-shrink-0">
                  <div className="bg-[#067977] p-3 rounded-lg">
                    <Phone className="h-5 w-5 text-white" />
                  </div>
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">{t('contact.phone')}</h3>
                  <a href={`tel:${contactSettings.phone}`} className="text-[#067977] font-medium hover:text-[#055a5c] transition-colors">
                    {contactSettings.phone}
                  </a>
                </div>
              </div>
            )}

            {/* Email */}
            {contactSettings.email && (
              <div className="flex items-center space-x-4 rtl:space-x-reverse">
                <div className="flex-shrink-0">
                  <div className="bg-[#067977] p-3 rounded-lg">
                    <Mail className="h-5 w-5 text-white" />
                  </div>
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">{t('contact.email')}</h3>
                  <a href={`mailto:${contactSettings.email}`} className="text-[#067977] font-medium hover:text-[#055a5c] transition-colors">
                    {contactSettings.email}
                  </a>
                </div>
              </div>
            )}

            {/* Business Hours */}
            {contactSettings.business_hours && (
              <div className="flex items-center space-x-4 rtl:space-x-reverse">
                <div className="flex-shrink-0">
                  <div className="bg-[#067977] p-3 rounded-lg">
                    <Clock className="h-5 w-5 text-white" />
                  </div>
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">{t('contact.businessHours')}</h3>
                  <p className="text-gray-600 whitespace-pre-line">
                    {contactSettings.business_hours}
                  </p>
                </div>
              </div>
            )}

            {/* Location */}
            {contactSettings.address && (
              <div className="flex items-center space-x-4 rtl:space-x-reverse">
                <div className="flex-shrink-0">
                  <div className="bg-[#067977] p-3 rounded-lg">
                    <MapPin className="h-5 w-5 text-white" />
                  </div>
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">{t('contact.location')}</h3>
                  <p className="text-gray-600 whitespace-pre-line">
                    {contactSettings.address}
                  </p>
                </div>
              </div>
            )}

            {/* WhatsApp (if available) */}
            {contactSettings.whatsapp && (
              <div className="flex items-center space-x-4 rtl:space-x-reverse">
                <div className="flex-shrink-0">
                  <div className="bg-[#067977] p-3 rounded-lg">
                    <svg className="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                    </svg>
                  </div>
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">WhatsApp</h3>
                  <a href={`https://wa.me/${contactSettings.whatsapp.replace(/[^\d+]/g, '')}`} target="_blank" rel="noopener noreferrer" className="text-[#067977] font-medium hover:text-[#055a5c] transition-colors">
                    {contactSettings.whatsapp}
                  </a>
                </div>
              </div>
            )}

            {/* Facebook (if available) */}
            {contactSettings.facebook && (
              <div className="flex items-center space-x-4 rtl:space-x-reverse">
                <div className="flex-shrink-0">
                  <div className="bg-[#067977] p-3 rounded-lg">
                    <Facebook className="h-5 w-5 text-white" />
                  </div>
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">Facebook</h3>
                  <a href={contactSettings.facebook} target="_blank" rel="noopener noreferrer" className="text-[#067977] font-medium hover:text-[#055a5c] transition-colors">
                    {contactSettings.facebook}
                  </a>
                </div>
              </div>
            )}

            {/* Twitter (if available) */}
            {contactSettings.twitter && (
              <div className="flex items-center space-x-4 rtl:space-x-reverse">
                <div className="flex-shrink-0">
                  <div className="bg-[#067977] p-3 rounded-lg">
                    <Twitter className="h-5 w-5 text-white" />
                  </div>
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">Twitter</h3>
                  <a href={contactSettings.twitter} target="_blank" rel="noopener noreferrer" className="text-[#067977] font-medium hover:text-[#055a5c] transition-colors">
                    {contactSettings.twitter}
                  </a>
                </div>
              </div>
            )}

            {/* LinkedIn (if available) */}
            {contactSettings.linkedin && (
              <div className="flex items-center space-x-4 rtl:space-x-reverse">
                <div className="flex-shrink-0">
                  <div className="bg-[#067977] p-3 rounded-lg">
                    <Linkedin className="h-5 w-5 text-white" />
                  </div>
                </div>
                <div>
                  <h3 className="font-medium text-gray-900">LinkedIn</h3>
                  <a href={contactSettings.linkedin} target="_blank" rel="noopener noreferrer" className="text-[#067977] font-medium hover:text-[#055a5c] transition-colors">
                    {contactSettings.linkedin}
                  </a>
                </div>
              </div>
            )}
          </div>

          {/* Contact Form Section */}
          <ContactForm />
        </div>
      </div>
    </div>
  );
};

export default ContactUs;
