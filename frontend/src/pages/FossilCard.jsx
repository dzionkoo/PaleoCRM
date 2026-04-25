import React from 'react';

/**
 * FossilCard — Individual fossil display card.
 *
 * NOTE: Tailwind is loaded once in index.html — never inline <script> tags here.
 */
export const FossilCard = ({ fossil, onAISuggest }) => {
  const statusColors = {
    extinct:           'bg-red-100 text-red-800 border-red-200',
    documented:        'bg-blue-100 text-blue-800 border-blue-200',
    active:            'bg-green-100 text-green-800 border-green-200',
    'pending-analysis':'bg-yellow-100 text-yellow-800 border-yellow-200',
  };

  const velocityIcon = (v) => {
    if (v === 'raptor-speed (40 km/h)') return '⚡';
    if (typeof v === 'number' && v >= 18)  return '🏃';
    if (typeof v === 'number' && v >= 10)  return '🚶';
    return '🦕';
  };

  const statusClass = statusColors[fossil.status] ?? 'bg-stone-100 text-stone-800 border-stone-200';

  return (
    <div className="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-amber-100 hover:border-amber-300 flex flex-col">

      {/* Card header */}
      <div className="bg-gradient-to-r from-amber-700 to-orange-700 px-5 py-4 flex items-start justify-between">
        <div className="min-w-0 pr-2">
          <h3 className="text-lg font-bold text-white truncate">{fossil.species}</h3>
          <p className="text-amber-200 text-xs mt-0.5 truncate">{fossil.collectionName}</p>
        </div>
        <span className={`shrink-0 px-2 py-0.5 rounded-full text-xs font-semibold border ${statusClass}`}>
          {fossil.status}
        </span>
      </div>

      {/* Card body */}
      <div className="px-5 py-4 flex-1 space-y-3">

        {/* Age + Weight */}
        <div className="grid grid-cols-2 gap-3">
          <div>
            <p className="text-stone-500 text-xs font-semibold uppercase tracking-wide">Age</p>
            <p className="text-2xl font-bold text-amber-900">{fossil.estimatedAge}M</p>
            <p className="text-xs text-stone-400">million years ago</p>
          </div>
          <div>
            <p className="text-stone-500 text-xs font-semibold uppercase tracking-wide">Weight</p>
            <p className="text-2xl font-bold text-amber-900">{fossil.weight.toLocaleString()} kg</p>
            <p className="text-xs text-stone-400">{fossil.weightCategory}</p>
          </div>
        </div>

        {/* Era */}
        <div className="px-3 py-2 bg-stone-100 rounded-lg">
          <p className="text-stone-500 text-xs font-semibold uppercase tracking-wide">Era</p>
          <p className="text-stone-800 font-semibold">{fossil.era}</p>
        </div>

        {/* Location */}
        <div>
          <p className="text-stone-500 text-xs font-semibold uppercase tracking-wide">Discovery Location</p>
          <p className="text-stone-700 text-sm">{fossil.discoveryLocation}</p>
        </div>

        {/* Velocity */}
        <div className="flex items-center gap-2 px-3 py-2 bg-orange-50 rounded-lg border border-orange-100">
          <span className="text-xl">{velocityIcon(fossil.theoreticalVelocity)}</span>
          <div>
            <p className="text-stone-500 text-xs font-semibold uppercase tracking-wide">Velocity</p>
            <p className="text-stone-800 text-sm font-semibold">
              {typeof fossil.theoreticalVelocity === 'number'
                ? `${fossil.theoreticalVelocity} m/s`
                : fossil.theoreticalVelocity}
            </p>
          </div>
        </div>

        {/* AI description preview (if already generated) */}
        {fossil.aiDescription && (
          <div className="px-3 py-2 bg-blue-50 rounded-lg border border-blue-100">
            <p className="text-blue-700 text-xs font-semibold mb-1">🤖 AI DESCRIPTION</p>
            <p className="text-blue-900 text-sm line-clamp-3">{fossil.aiDescription}</p>
          </div>
        )}
      </div>

      {/* Card actions */}
      <div className="px-5 py-3 bg-stone-50 border-t border-stone-100 flex gap-2">
        <button
          onClick={() => onAISuggest(fossil)}
          className="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-1.5"
        >
          <span>🤖</span>
          <span>AI Suggest</span>
        </button>
        <button className="flex-1 bg-stone-200 hover:bg-stone-300 text-stone-800 px-3 py-2 rounded-lg text-sm font-semibold transition-colors duration-200">
          View Details →
        </button>
      </div>

      {/* ID footer */}
      <div className="px-5 py-1.5 bg-stone-900 text-center">
        <p className="text-stone-500 text-xs font-mono">{fossil.id}</p>
      </div>
    </div>
  );
};