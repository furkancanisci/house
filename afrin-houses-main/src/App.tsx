import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AppProvider } from './context/AppContext';
import { LeafletMapProvider } from './context/LeafletMapProvider';
import { Toaster } from 'sonner';
import Layout from './components/Layout';
import Home from './pages/Home';
import PropertyDetails from './pages/PropertyDetails';
import Search from './pages/Search';
import MapSearch from './pages/MapSearch';
import HousesForRent from './pages/HousesForRent';
import HousesForSale from './pages/HousesForSale';
import Dashboard from './pages/Dashboard';
import AddProperty from './pages/AddProperty';
import EditProperty from './pages/EditProperty';
import Favorites from './pages/Favorites';
import Auth from './pages/Auth';
import AboutUs from './pages/AboutUs';
import ContactUs from './pages/ContactUs';
import PrivacyPolicy from './pages/PrivacyPolicy';
import TermsOfService from './pages/TermsOfService';
import CookiePolicy from './pages/CookiePolicy';
import EmailVerification from './pages/EmailVerification';
import './App.css';

function App() {
  return (
    <AppProvider>
      <LeafletMapProvider>
        <Router>
          <div className="min-h-screen bg-gray-50" dir="rtl">
            <Layout>
              <Routes>
              <Route path="/" element={<Home />} />
              <Route path="/search" element={<Search />} />
              <Route path="/search/map" element={<MapSearch />} />
              <Route path="/houses-for-rent" element={<HousesForRent />} />
              <Route path="/houses-for-sale" element={<HousesForSale />} />
              <Route path="/property/:id" element={<PropertyDetails />} />
              <Route path="/dashboard" element={<Dashboard />} />
              <Route path="/add-property" element={<AddProperty />} />
              <Route path="/edit-property/:id" element={<EditProperty />} />
              <Route path="/favorites" element={<Favorites />} />
              <Route path="/auth" element={<Auth />} />
              <Route path="/email/verify" element={<EmailVerification />} />
              <Route path="/verify-email" element={<EmailVerification />} />
              <Route path="/about" element={<AboutUs />} />
              <Route path="/contact" element={<ContactUs />} />
              <Route path="/privacy-policy" element={<PrivacyPolicy />} />
              <Route path="/terms-of-service" element={<TermsOfService />} />
              <Route path="/cookie-policy" element={<CookiePolicy />} />
              </Routes>
            </Layout>
            <Toaster position="top-right" />
          </div>
        </Router>
      </LeafletMapProvider>
    </AppProvider>
  );
}

export default App;
