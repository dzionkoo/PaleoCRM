import React from 'react';

/**
 * FossilCard Component - Individual fossil display card
 * 
 * Displays:
 * - Fossil name and species
 * - Age and weight
 * - Theoretical velocity
 * - Status badge
 * - AI description button
 */
<script src="https://cdn.tailwindcss.com"></script>
export const FossilCard = ({ fossil, onAISuggest }) => {
  const getStatusColor = (status) => {
    const colors = {
      'extinct': 'bg-red-100 text-red-800',
      'documented': 'bg-blue-100 text-blue-800',
      'active': 'bg-green-100 text-green-800',
      'pending-analysis': 'bg-yellow-100 text-yellow-800',
    };
    return colors[status] || 'bg-stone-100 text-stone-800';
  };

  const getVelocityIcon = (velocity) => {
    if (velocity === 'raptor-speed (40 km/h)') return '⚡';
    if (typeof velocity === 'number') {
      if (velocity >= 18) return '🏃';
      if (velocity >= 10) return '🚶';
    }
    return '🦕';
  };

  return (
    <div className="bg-white rounded-lg shadow-lg hover:shadow-2xl transition-shadow duration-300 overflow-hidden border-2 border-amber-100 hover:border-amber-300">
      {/* Header */}
      <div className="bg-gradient-to-r from-amber-600 to-orange-600 px-6 py-4">
        <div className="flex items-start justify-between">
          <div>
            <h3 className="text-xl font-bold text-white">
              {fossil.species}
            </h3>
            <p className="text-amber-100 text-sm">{fossil.collectionName}</p>
          </div>
          <span className={`px-3 py-1 rounded-full font-semibold text-xs ${getStatusColor(fossil.status)}`}>
            {fossil.status}
          </span>
        </div>
      </div>

      {/* Body */}
      <div className="px-6 py-4">
        {/* Age & Weight */}
        <div className="grid grid-cols-2 gap-4 mb-4">
          <div>
            <p className="text-stone-600 text-sm font-semibold uppercase">Age</p>
            <p className="text-2xl font-bold text-amber-900">{fossil.estimatedAge}M</p>
            <p className="text-xs text-stone-500">million years ago</p>
          </div>
          <div>
            <p className="text-stone-600 text-sm font-semibold uppercase">Weight</p>
            <p className="text-2xl font-bold text-amber-900">{fossil.weight.toLocaleString()}kg</p>
            <p className="text-xs text-stone-500">{fossil.weightCategory}</p>
          </div>
        </div>

        {/* Era */}
        <div className="mb-4 p-3 bg-stone-100 rounded">
          <p className="text-stone-600 text-xs font-semibold">ERA</p>
          <p className="text-lg font-bold text-stone-800">{fossil.era}</p>
        </div>

        {/* Discovery Location */}
        <div className="mb-4">
          <p className="text-stone-600 text-xs font-semibold">DISCOVERY LOCATION</p>
          <p className="text-stone-700">{fossil.discoveryLocation}</p>
        </div>

        {/* Velocity */}
        <div className="flex items-center gap-2 mb-4 p-3 bg-orange-50 rounded border border-orange-200">
          <span className="text-2xl">{getVelocityIcon(fossil.theoreticalVelocity)}</span>
          <div>
            <p className="text-stone-600 text-xs font-semibold">VELOCITY</p>
            <p className="text-stone-700 font-semibold">
              {typeof fossil.theoreticalVelocity === 'string'
                ? fossil.theoreticalVelocity
                : `${fossil.theoreticalVelocity} m/s`}
            </p>
          </div>
        </div>

        {/* AI Description Preview */}
        {fossil.aiDescription && (
          <div className="mb-4 p-3 bg-blue-50 rounded border border-blue-200">
            <p className="text-xs font-semibold text-blue-900 mb-1">🤖 AI DESCRIPTION</p>
            <p className="text-sm text-blue-900 line-clamp-3">{fossil.aiDescription}</p>
          </div>
        )}
      </div>

      {/* Footer with Actions */}
      <div className="px-6 py-4 bg-stone-50 border-t border-stone-200 flex gap-2">
        <button
          onClick={() => onAISuggest(fossil)}
          className="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-4 py-2 rounded font-semibold hover:from-blue-700 hover:to-purple-700 transition-all duration-200 text-sm flex items-center justify-center gap-2"
        >
          <span>🤖</span>
          <span>AI Suggest</span>
        </button>
        <button
          className="flex-1 bg-stone-200 text-stone-800 px-4 py-2 rounded font-semibold hover:bg-stone-300 transition-colors duration-200 text-sm"
        >
          View Details →
        </button>
      </div>

      {/* Easter Egg - Hover indicator */}
      <div className="px-6 py-2 bg-gradient-to-r from-stone-900 to-black text-center">
        <p className="text-xs text-stone-400">ID: {fossil.id}</p>
      </div>
    </div>
  );
};
