import React, { useState, useEffect } from 'react';
import { FossilCard } from './FossilCard';
import { AIDescriptionModal } from './AIDescriptionModal';
import { CreateFossilModal } from './CreateFossilModal';
import { useFossilData } from '../hooks/useFossilData';

/**
 * Dashboard Component - Main UI for PaleoCRM
 * 
 * Features:
 * ✨ List of all fossils in grid layout
 * ✨ Pagination controls
 * ✨ AI description generation modal
 * ✨ Real-time status updates
 * ✨ Dino-themed UI with Tailwind CSS
 */
export const Dashboard = () => {
  const { 
    fossils, 
    loading, 
    error, 
    page, 
    setPage, 
    selectedFossil, 
    setSelectedFossil,
    status,
    setStatus,
    refetch
  } = useFossilData();
  const [showModal, setShowModal] = useState(false);
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [aiLoading, setAiLoading] = useState(false);

  const handleAISuggest = (fossil) => {
    setSelectedFossil(fossil);
    setShowModal(true);
    setAiLoading(true);

    // Simulate API call to Claude for AI description
    setTimeout(() => {
      setAiLoading(false);
    }, 2000);
  };

  const handleCloseModal = () => {
    setShowModal(false);
    setSelectedFossil(null);
  };

  const handleFossilCreated = () => {
    // Refresh the fossil list
    setPage(1);
    setStatus('');
    if (refetch) {
      refetch();
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-amber-50 via-stone-50 to-orange-50">
      {/* Header */}
      <header className="bg-gradient-to-r from-amber-900 via-orange-800 to-stone-800 text-white shadow-lg">
        <div className="max-w-7xl mx-auto px-6 py-8">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <span className="text-4xl">🦕</span>
              <h1 className="text-4xl font-bold">PaleoCRM</h1>
            </div>
            <div className="text-right">
              <p className="text-amber-100">Paleocene Collection Management System</p>
              <p className="text-sm text-amber-200">Powered by Claude AI & Modern PHP</p>
            </div>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-6 py-12">
        {/* Status Messages */}
        {error && (
          <div className="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
            <p className="font-bold">⚠️ Error</p>
            <p>{error}</p>
          </div>
        )}

        {loading && !fossils.length && (
          <div className="flex items-center justify-center py-12">
            <div className="animate-spin text-4xl">🦖</div>
            <p className="ml-4 text-lg text-stone-600">Loading fossils from the database...</p>
          </div>
        )}
        
        {/* Controls Section (Always Visible When Not Loading) */}
        {!loading && (
          <div className="mb-8 flex items-center justify-between">
            <div>
              <h2 className="text-3xl font-bold text-stone-800 mb-4">
                Fossil Collection ({fossils.length} items)
              </h2>
              <p className="text-stone-600 mb-6">
                Welcome to PaleoCRM - a modern demonstration of legacy code refactoring from PHP 5.6 to 8.3
              </p>
            </div>
            <button
              onClick={() => setShowCreateModal(true)}
              className="px-6 py-3 bg-amber-900 text-white rounded-lg font-semibold hover:bg-amber-800 transition-colors whitespace-nowrap"
            >
              ✨ Add Fossil
            </button>
          </div>
        )}

        {/* Status Filter (Always Visible When Not Loading) */}
        {!loading && (
          <div className="mb-6 flex items-center gap-4">
            <label className="text-stone-700 font-semibold">Filter by Status:</label>
            <select
              value={status}
              onChange={(e) => {
                setStatus(e.target.value);
                setPage(1); // Reset to first page when filtering
              }}
              className="px-4 py-2 border border-amber-300 rounded bg-white text-stone-700 focus:outline-none focus:ring-2 focus:ring-amber-500"
            >
              <option value="">All Statuses</option>
              <option value="discovered">Discovered</option>
              <option value="analyzed">Analyzed</option>
              <option value="extinct">Extinct</option>
              <option value="pending-analysis">Pending Analysis</option>
              <option value="documented">Documented</option>
            </select>
          </div>
        )}

        {/* Fossils Grid */}
        {!loading && fossils.length > 0 && (
          <>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            {fossils.map((fossil) => (
                <FossilCard
                  key={fossil.id}
                  fossil={fossil}
                  onAISuggest={handleAISuggest}
                />
              ))}
            </div>

            {/* Pagination */}
            <div className="flex justify-center items-center gap-4">
              <button
                onClick={() => setPage(Math.max(1, page - 1))}
                disabled={page === 1}
                className="px-6 py-2 bg-amber-900 text-white rounded hover:bg-amber-800 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                ← Previous
              </button>
              <span className="text-lg font-semibold text-stone-700">
                Page {page}
              </span>
              <button
                onClick={() => setPage(page + 1)}
                className="px-6 py-2 bg-amber-900 text-white rounded hover:bg-amber-800"
              >
                Next →
              </button>
            </div>
          </>
        )}

        {/* Empty State */}
        {!loading && fossils.length === 0 && (
          <div className="text-center py-12">
            <div className="text-6xl mb-4">🦕</div>
            <h3 className="text-2xl font-bold text-stone-800 mb-2">No Fossils Found</h3>
            <p className="text-stone-600 mb-8">The Paleocene Valley is quiet today...</p>
            <button
              onClick={() => setShowCreateModal(true)}
              className="px-8 py-3 bg-amber-900 text-white rounded-lg font-semibold hover:bg-amber-800 transition-colors"
            >
              ✨ Add First Fossil
            </button>
          </div>
        )}
      </main>

      {/* AI Description Modal */}
      {showModal && selectedFossil && (
        <AIDescriptionModal
          fossil={selectedFossil}
          loading={aiLoading}
          onClose={handleCloseModal}
        />
      )}

      {/* Create Fossil Modal */}
      {showCreateModal && (
        <CreateFossilModal
          onClose={() => setShowCreateModal(false)}
          onSuccess={handleFossilCreated}
        />
      )}

      {/* Footer */}
      <footer className="bg-stone-900 text-stone-300 mt-16 py-8">
        <div className="max-w-7xl mx-auto px-6 text-center">
          <p className="mb-2">
            🦖 PaleoCRM v1.0 - Senior Fullstack Developer Portfolio Project
          </p>
          <p className="text-sm text-stone-500">
            Legacy PHP to Modern Symfony Refactoring • React Frontend • Claude AI Integration
          </p>
        </div>
      </footer>
    </div>
  );
};
