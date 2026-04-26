import React, { useState } from 'react';

/**
 * CreateFossilModal - Form for adding new fossils to the collection
 * 
 * Features:
 * - Input form for fossil details
 * - Submit to API
 * - Error handling
 * - Success confirmation
 */
export const CreateFossilModal = ({ onClose, onSuccess }) => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [formData, setFormData] = useState({
    species: '',
    collectionName: '',
    estimatedAge: '',
    weight: '',
    discoveryLocation: '',
    status: 'discovered',
  });

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      // Validate required fields
      if (!formData.species || !formData.collectionName || !formData.estimatedAge || !formData.weight) {
        throw new Error('Please fill in all required fields');
      }

      // Convert to proper types
      const payload = {
        species: formData.species,
        collectionName: formData.collectionName,
        estimatedAge: parseInt(formData.estimatedAge),
        weight: parseFloat(formData.weight),
        discoveryLocation: formData.discoveryLocation || 'Unknown',
        status: formData.status,
      };

      const response = await fetch('http://localhost:8000/api/fossils', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload),
      });

      if (!response.ok) {
        const data = await response.json();
        throw new Error(data.error || `HTTP ${response.status}`);
      }

      const data = await response.json();
      
      if (!data.success) {
        throw new Error(data.error || 'Failed to create fossil');
      }

      // Success - notify parent and close
      if (onSuccess) {
        onSuccess(data.data);
      }
      
      onClose();
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div className="bg-white rounded-lg shadow-2xl max-w-md w-full mx-4">
        {/* Header */}
        <div className="bg-gradient-to-r from-amber-900 to-orange-800 text-white px-6 py-4 rounded-t-lg">
          <h2 className="text-2xl font-bold">Add New Fossil</h2>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          {error && (
            <div className="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 rounded">
              <p className="text-sm">{error}</p>
            </div>
          )}

          {/* Species */}
          <div>
            <label className="block text-sm font-semibold text-stone-700 mb-1">
              Species *
            </label>
            <input
              type="text"
              name="species"
              value={formData.species}
              onChange={handleInputChange}
              placeholder="e.g., Tyrannosaurus rex"
              className="w-full px-3 py-2 border border-amber-300 rounded bg-white text-stone-700 focus:outline-none focus:ring-2 focus:ring-amber-500"
              disabled={loading}
            />
          </div>

          {/* Collection Name */}
          <div>
            <label className="block text-sm font-semibold text-stone-700 mb-1">
              Collection Name *
            </label>
            <input
              type="text"
              name="collectionName"
              value={formData.collectionName}
              onChange={handleInputChange}
              placeholder="e.g., Hell Creek Formation"
              className="w-full px-3 py-2 border border-amber-300 rounded bg-white text-stone-700 focus:outline-none focus:ring-2 focus:ring-amber-500"
              disabled={loading}
            />
          </div>

          {/* Estimated Age */}
          <div>
            <label className="block text-sm font-semibold text-stone-700 mb-1">
              Estimated Age (millions of years) *
            </label>
            <input
              type="number"
              name="estimatedAge"
              value={formData.estimatedAge}
              onChange={handleInputChange}
              placeholder="e.g., 66"
              className="w-full px-3 py-2 border border-amber-300 rounded bg-white text-stone-700 focus:outline-none focus:ring-2 focus:ring-amber-500"
              disabled={loading}
            />
          </div>

          {/* Weight */}
          <div>
            <label className="block text-sm font-semibold text-stone-700 mb-1">
              Weight (kg) *
            </label>
            <input
              type="number"
              name="weight"
              value={formData.weight}
              onChange={handleInputChange}
              placeholder="e.g., 9000"
              step="0.1"
              className="w-full px-3 py-2 border border-amber-300 rounded bg-white text-stone-700 focus:outline-none focus:ring-2 focus:ring-amber-500"
              disabled={loading}
            />
          </div>

          {/* Discovery Location */}
          <div>
            <label className="block text-sm font-semibold text-stone-700 mb-1">
              Discovery Location
            </label>
            <input
              type="text"
              name="discoveryLocation"
              value={formData.discoveryLocation}
              onChange={handleInputChange}
              placeholder="e.g., Montana, USA"
              className="w-full px-3 py-2 border border-amber-300 rounded bg-white text-stone-700 focus:outline-none focus:ring-2 focus:ring-amber-500"
              disabled={loading}
            />
          </div>

          {/* Status */}
          <div>
            <label className="block text-sm font-semibold text-stone-700 mb-1">
              Status
            </label>
            <select
              name="status"
              value={formData.status}
              onChange={handleInputChange}
              className="w-full px-3 py-2 border border-amber-300 rounded bg-white text-stone-700 focus:outline-none focus:ring-2 focus:ring-amber-500"
              disabled={loading}
            >
              <option value="discovered">Discovered</option>
              <option value="analyzed">Analyzed</option>
              <option value="extinct">Extinct</option>
              <option value="documented">Documented</option>
              <option value="pending-analysis">Pending Analysis</option>
            </select>
          </div>

          {/* Buttons */}
          <div className="flex gap-3 pt-4">
            <button
              type="submit"
              disabled={loading}
              className="flex-1 bg-amber-900 text-white py-2 rounded font-semibold hover:bg-amber-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {loading ? '🦖 Creating...' : '✨ Create Fossil'}
            </button>
            <button
              type="button"
              onClick={onClose}
              disabled={loading}
              className="flex-1 bg-stone-300 text-stone-700 py-2 rounded font-semibold hover:bg-stone-400 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
