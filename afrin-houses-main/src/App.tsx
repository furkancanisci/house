import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AppProvider } from './context/AppContext';
import { Toaster } from 'sonner';
import Layout from './components/Layout';
import Home from './pages/Home';
import PropertyDetails from './pages/PropertyDetails';
import Search from './pages/Search';
import HousesForRent from './pages/HousesForRent';
import HousesForSale from './pages/HousesForSale';
import Dashboard from './pages/Dashboard';
import AddProperty from './pages/AddProperty';
import EditProperty from './pages/EditProperty';
import Favorites from './pages/Favorites';
import Auth from './pages/Auth';
import AboutUs from './pages/AboutUs';
import ContactUs from './pages/ContactUs';
import './App.css';

function App() {
  return (
    <AppProvider>
      <Router>
        <div className="min-h-screen bg-gray-50">
          <Layout>
            <Routes>
              <Route path="/" element={<Home />} />
              <Route path="/search" element={<Search />} />
              <Route path="/houses-for-rent" element={<HousesForRent />} />
              <Route path="/houses-for-sale" element={<HousesForSale />} />
              <Route path="/property/:id" element={<PropertyDetails />} />
              <Route path="/dashboard" element={<Dashboard />} />
              <Route path="/add-property" element={<AddProperty />} />
              <Route path="/edit-property/:id" element={<EditProperty />} />
              <Route path="/favorites" element={<Favorites />} />
              <Route path="/auth" element={<Auth />} />
              <Route path="/about" element={<AboutUs />} />
              <Route path="/contact" element={<ContactUs />} />
            </Routes>
          </Layout>
          <Toaster position="top-right" />
        </div>
      </Router>
    </AppProvider>
  );
}

export default App;
