import React, { useState } from 'react';
import { FossilCard } from './FossilCard';
import { AIDescriptionModal } from './AIDescriptionModal';
import { useFossilData } from '../hooks/useFossilData';

const STATUSES = [
  { value: '',                 label: 'All Statuses' },
  { value: 'documented',      label: '📋 Documented' },
  { value: 'active',          label: '✅ Active' },
  { value: 'extinct',         label: '💀 Extinct' },
  { value: 'pending-analysis',label: '🔬 Pending Analysis' },
];

/**
 * Dashboard — Main UI for PaleoCRM
 *
 * Features:
 * ✨ Fossil grid with pagination
 * ✨ Status filter dropdown
 * ✨ Real-time AI description streaming modal
 * ✨ Friendly error + empty states
 */
const Dashboard = () => {
  const {
    fossils, loading, error,
    page, setPage, hasNextPage,
    selectedFossil, setSelectedFossil,
    status, setStatus,
    refetch,
  } = useFossilData();

  const [showModal, setShowModal] = useState(false);

  const handleAISuggest = (fossil) => {
    setSelectedFossil(fossil);
    setShowModal(true);
  };

  const handleCloseModal = () => {
    setShowModal(false);
    setSelectedFossil(null);
  };

  const handleStatusChange = (e) => {
    setStatus(e.target.value);
    setPage(1); // reset to page 1 on filter change
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-amber-50 via-stone-50 to-orange-50">

      {/* ── Header ── */}
      <header className="bg-gradient-to-r from-amber-900 via-orange-800 to-stone-800 text-white shadow-lg">
        <div className="max-w-7xl mx-auto px-6 py-8">
          <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div className="flex items-center gap-3">
              <span className="text-4xl">🦕</span>
              <div>
                <h1 className="text-3xl font-bold leading-tight">PaleoCRM</h1>
                <p className="text-amber-200 text-sm">Paleocene Collection Management System</p>
              </div>
            </div>
            <p className="text-amber-300 text-xs text-right">Powered by Claude AI &amp; Modern PHP 8.3</p>
          </div>
        </div>
      </header>

      {/* ── Main ── */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 py-10">

        {/* Toolbar: status filter + result count */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-8">
          <div>
            <h2 className="text-2xl font-bold text-stone-800">
              Fossil Collection
              {fossils.length > 0 && (
                <span className="ml-2 text-base font-normal text-stone-500">
                  ({fossils.length} shown)
                </span>
              )}
            </h2>
            <p className="text-stone-500 text-sm mt-0.5">
              Legacy PHP to Modern PHP 8.3 refactoring demo · React 18 · Claude AI
            </p>
          </div>

          {/* Status filter */}
          <div className="flex items-center gap-2">
            <label htmlFor="status-filter" className="text-stone-600 text-sm font-medium whitespace-nowrap">
              Filter by status:
            </label>
            <select
              id="status-filter"
              value={status}
              onChange={handleStatusChange}
              className="border border-stone-300 rounded-lg px-3 py-2 text-sm bg-white text-stone-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
            >
              {STATUSES.map(s => (
                <option key={s.value} value={s.value}>{s.label}</option>
              ))}
            </select>
          </div>
        </div>

        {/* Error banner */}
        {error && (
          <div className="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg flex items-start gap-3">
            <span className="text-xl">⚠️</span>
            <div>
              <p className="font-semibold">API Error</p>
              <p className="text-sm">{error}</p>
              {import.meta.env.DEV && (
                <p className="text-xs mt-1 text-red-500">Showing mock data (dev mode)</p>
              )}
            </div>
            <button
              onClick={refetch}
              className="ml-auto text-red-600 hover:text-red-800 text-sm font-medium"
            >
              Retry
            </button>
          </div>
        )}

        {/* Loading */}
        {loading && (
          <div className="flex items-center justify-center py-16">
            <span className="text-4xl animate-spin mr-3">🦖</span>
            <p className="text-stone-500 text-lg">Excavating fossils…</p>
          </div>
        )}

        {/* Grid */}
        {!loading && fossils.length > 0 && (
          <>
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-10">
              {fossils.map(fossil => (
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
                onClick={() => setPage(p => Math.max(1, p - 1))}
                disabled={page === 1}
                className="px-5 py-2 bg-amber-900 text-white rounded-lg font-semibold hover:bg-amber-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
              >
                ← Previous
              </button>
              <span className="text-stone-600 font-semibold">Page {page}</span>
              <button
                onClick={() => setPage(p => p + 1)}
                disabled={!hasNextPage}
                className="px-5 py-2 bg-amber-900 text-white rounded-lg font-semibold hover:bg-amber-800 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
              >
                Next →
              </button>
            </div>
          </>
        )}

        {/* Empty state */}
        {!loading && fossils.length === 0 && (
          <div className="text-center py-20">
            <div className="text-6xl mb-4">🦕</div>
            <h3 className="text-2xl font-bold text-stone-700 mb-2">No Fossils Found</h3>
            <p className="text-stone-500">
              {status
                ? `No fossils with status "${status}" in the valley.`
                : 'The Paleocene Valley is quiet today…'}
            </p>
            {status && (
              <button
                onClick={() => { setStatus(''); setPage(1); }}
                className="mt-4 px-4 py-2 bg-amber-700 text-white rounded-lg text-sm font-semibold hover:bg-amber-800 transition-colors"
              >
                Clear Filter
              </button>
            )}
          </div>
        )}
      </main>

      {/* AI Modal */}
      {showModal && selectedFossil && (
        <AIDescriptionModal
          fossil={selectedFossil}
          onClose={handleCloseModal}
          onDescriptionSaved={() => refetch()}
        />
      )}

      {/* Footer */}
      <footer className="bg-stone-900 text-stone-400 py-8 mt-16">
        <div className="max-w-7xl mx-auto px-6 text-center">
          <p className="mb-1">🦖 PaleoCRM v1.0 — Senior Fullstack Developer Portfolio Project</p>
          <p className="text-stone-600 text-sm">
            Legacy PHP → Modern PHP 8.3 Refactoring · React 18 · Claude AI Integration
          </p>
        </div>
      </footer>
    </div>
  );
};

export default Dashboard;