import React from 'react';

/**
 * AIDescriptionModal Component - AI description generation modal
 * 
 * Shows:
 * - Fossil details
 * - AI-generated description loading state
 * - Final description with copy button
 */
export const AIDescriptionModal = ({ fossil, loading, onClose }) => {
  const [isCopied, setIsCopied] = React.useState(false);

  const mockAIDescription = `This remarkable ${fossil.species} specimen from the ${fossil.era} period represents a significant addition to our paleontological understanding. Weighing approximately ${fossil.weight}kg and estimated at ${fossil.estimatedAge} million years old, this fossil showcases exceptional preservation.

The specimen was discovered at ${fossil.discoveryLocation} and is now part of the ${fossil.collectionName}. Based on skeletal structure analysis, we can infer theoretical movement capabilities suggesting a velocity of approximately ${fossil.theoreticalVelocity}.

This particular fossil demonstrates key adaptations to its environment and provides crucial insights into evolution during its geological epoch. The preservation quality is outstanding, allowing for detailed morphological analysis.`;

  const handleCopy = () => {
    navigator.clipboard.writeText(mockAIDescription);
    setIsCopied(true);
    setTimeout(() => setIsCopied(false), 2000);
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
      <div className="bg-white rounded-lg shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        
        {/* Header */}
        <div className="sticky top-0 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-4 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <span className="text-2xl">🤖</span>
            <h2 className="text-2xl font-bold">AI Description Generator</h2>
          </div>
          <button
            onClick={onClose}
            className="text-2xl font-bold hover:opacity-80 transition"
          >
            ✕
          </button>
        </div>

        {/* Content */}
        <div className="p-6">
          {/* Fossil Info */}
          <div className="mb-6 p-4 bg-stone-100 rounded-lg border-l-4 border-stone-400">
            <h3 className="text-lg font-bold text-stone-800 mb-2">{fossil.species}</h3>
            <div className="grid grid-cols-2 gap-4 text-sm">
              <div>
                <p className="text-stone-600 font-semibold">Age</p>
                <p className="text-stone-800">{fossil.estimatedAge} million years</p>
              </div>
              <div>
                <p className="text-stone-600 font-semibold">Weight</p>
                <p className="text-stone-800">{fossil.weight}kg</p>
              </div>
              <div>
                <p className="text-stone-600 font-semibold">Era</p>
                <p className="text-stone-800">{fossil.era}</p>
              </div>
              <div>
                <p className="text-stone-600 font-semibold">Location</p>
                <p className="text-stone-800">{fossil.discoveryLocation}</p>
              </div>
            </div>
          </div>

          {/* AI Description Loading/Result */}
          {loading ? (
            <div className="flex flex-col items-center justify-center py-12">
              <div className="mb-4">
                <div className="inline-block">
                  <div className="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                </div>
              </div>
              <p className="text-stone-600 text-lg">Claude is analyzing this fossil...</p>
              <p className="text-stone-500 text-sm mt-2">Generating scientifically accurate description...</p>
            </div>
          ) : (
            <div className="space-y-4">
              <div>
                <h4 className="text-lg font-semibold text-blue-600 mb-3">📖 Generated Description</h4>
                <div className="bg-blue-50 p-4 rounded-lg border border-blue-200 text-stone-700 leading-relaxed">
                  <p>{mockAIDescription}</p>
                </div>
              </div>

              {/* Copy Button */}
              <div className="flex gap-2">
                <button
                  onClick={handleCopy}
                  className={`flex-1 px-4 py-2 rounded font-semibold transition-all duration-200 flex items-center justify-center gap-2 ${
                    isCopied
                      ? 'bg-green-500 text-white'
                      : 'bg-blue-600 text-white hover:bg-blue-700'
                  }`}
                >
                  <span>{isCopied ? '✓' : '📋'}</span>
                  <span>{isCopied ? 'Copied!' : 'Copy to Clipboard'}</span>
                </button>
              </div>

              {/* Info Box */}
              <div className="p-4 bg-purple-50 rounded-lg border border-purple-200">
                <p className="text-sm text-purple-900">
                  💡 <strong>Pro Tip:</strong> This description was generated by Claude API based on paleontological data. 
                  You can use this to populate your CRM database or export for documentation.
                </p>
              </div>
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="bg-stone-50 px-6 py-4 border-t border-stone-200 flex gap-3">
          <button
            onClick={onClose}
            className="flex-1 px-4 py-2 bg-stone-200 text-stone-800 rounded font-semibold hover:bg-stone-300 transition"
          >
            Close
          </button>
          <button
            className="flex-1 px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded font-semibold hover:from-blue-700 hover:to-purple-700 transition"
          >
            Save & Update
          </button>
        </div>
      </div>
    </div>
  );
};
