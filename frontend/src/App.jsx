import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Dashboard from './pages/Dashboard';
import Properties from './pages/Properties';
import { ApiErrorBoundary } from './components/ApiErrorBoundary';
import './App.css';

function App() {
  return (
    <ApiErrorBoundary>
      <Router>
        <Routes>
          <Route path="/"           element={<Dashboard />} />
          <Route path="/properties" element={<Properties />} />
        </Routes>
      </Router>
    </ApiErrorBoundary>
  );
}

export default App;