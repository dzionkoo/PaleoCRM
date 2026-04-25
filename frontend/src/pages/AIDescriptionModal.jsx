import React, { useState, useEffect, useRef } from 'react';

const API_BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8000';

/**
 * AIDescriptionModal
 *
 * Streams the AI description from the PHP backend via Server-Sent Events.
 * Tokens appear word-by-word as Claude generates them.
 */
export const AIDescriptionModal = ({ fossil, onClose, onDescriptionSaved }) => {
  const [streamedText, setStreamedText] = useState('');
  const [isStreaming, setIsStreaming] = useState(true);
  const [isCopied, setIsCopied] = useState(false);
  const [streamError, setStreamError] = useState(null);
  const readerRef = useRef(null);

  // ── Kick off SSE stream when the modal mounts ──────────────────────────────
  useEffect(() => {
    let cancelled = false;

    const stream = async () => {
      try {
        const res = await fetch(
          `${API_BASE}/api/fossils/${fossil.id}/ai-describe`,
          {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
          }
        );

        if (!res.ok) {
          const body = await res.json().catch(() => ({}));
          throw new Error(body.error ?? `HTTP ${res.status}`);
        }

        const reader = res.body.getReader();
        readerRef.current = reader;
        const decoder = new TextDecoder();
        let buffer = '';

        while (!cancelled) {
          const { done, value } = await reader.read();
          if (done) break;

          buffer += decoder.decode(value, { stream: true });

          // Process complete SSE lines
          const lines = buffer.split('\n');
          buffer = lines.pop() ?? ''; // keep the incomplete last line

          for (const line of lines) {
            if (!line.startsWith('data: ')) continue;
            try {
              const json = JSON.parse(line.slice(6));
              if (json.error) throw new Error(json.error);
              if (json.text && !cancelled) {
                setStreamedText(prev => prev + json.text);
              }
              if (json.done && onDescriptionSaved) {
                onDescriptionSaved(json.fossilId);
              }
            } catch {
              // non-JSON or partial — skip
            }
          }
        }
      } catch (err) {
        if (!cancelled) {
          setStreamError(err?.message ?? 'Streaming failed');
          // Fall back: show a static description built from fossil data
          setStreamedText(buildFallbackDescription(fossil));
        }
      } finally {
        if (!cancelled) setIsStreaming(false);
      }
    };

    stream();

    return () => {
      cancelled = true;
      readerRef.current?.cancel().catch(() => {});
    };
  }, [fossil.id]); // eslint-disable-line react-hooks/exhaustive-deps

  const handleCopy = () => {
    navigator.clipboard.writeText(streamedText).then(() => {
      setIsCopied(true);
      setTimeout(() => setIsCopied(false), 2000);
    });
  };

  const handleClose = () => {
    readerRef.current?.cancel().catch(() => {});
    onClose();
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4">
      <div className="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">

        {/* Header */}
        <div className="sticky top-0 bg-gradient-to-r from-blue-700 to-purple-700 text-white px-6 py-4 flex items-center justify-between rounded-t-xl">
          <div className="flex items-center gap-3">
            <span className="text-2xl">🤖</span>
            <h2 className="text-xl font-bold">AI Description Generator</h2>
          </div>
          <button
            onClick={handleClose}
            className="text-white/80 hover:text-white text-2xl font-bold transition-colors"
            aria-label="Close"
          >
            ✕
          </button>
        </div>

        {/* Content */}
        <div className="p-6 space-y-5">

          {/* Fossil metadata */}
          <div className="p-4 bg-stone-100 rounded-lg border-l-4 border-amber-500">
            <h3 className="text-lg font-bold text-stone-800 mb-2">{fossil.species}</h3>
            <div className="grid grid-cols-2 gap-3 text-sm">
              {[
                ['Age',      `${fossil.estimatedAge} million years`],
                ['Weight',   `${fossil.weight} kg`],
                ['Era',       fossil.era],
                ['Location',  fossil.discoveryLocation],
              ].map(([label, value]) => (
                <div key={label}>
                  <p className="text-stone-500 font-semibold uppercase text-xs">{label}</p>
                  <p className="text-stone-800">{value}</p>
                </div>
              ))}
            </div>
          </div>

          {/* Streaming area */}
          <div>
            <div className="flex items-center gap-2 mb-2">
              <h4 className="text-base font-semibold text-blue-700">📖 Generated Description</h4>
              {isStreaming && (
                <span className="flex items-center gap-1 text-xs text-stone-400">
                  <span className="inline-block w-2 h-2 rounded-full bg-blue-500 animate-pulse" />
                  Claude is writing…
                </span>
              )}
            </div>

            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 min-h-[120px] text-stone-700 leading-relaxed whitespace-pre-wrap">
              {streamedText || (
                <span className="text-stone-400 italic">Waiting for Claude…</span>
              )}
              {/* blinking cursor while streaming */}
              {isStreaming && (
                <span className="inline-block w-0.5 h-4 bg-blue-500 ml-0.5 align-middle animate-pulse" />
              )}
            </div>

            {streamError && (
              <p className="mt-2 text-xs text-amber-600">
                ⚠️ {streamError} — showing fallback description.
              </p>
            )}
          </div>

          {/* Actions — shown only after streaming completes */}
          {!isStreaming && streamedText && (
            <div className="flex gap-3">
              <button
                onClick={handleCopy}
                className={`flex-1 px-4 py-2 rounded-lg font-semibold text-sm flex items-center justify-center gap-2 transition-all duration-200 ${
                  isCopied
                    ? 'bg-green-500 text-white'
                    : 'bg-blue-600 hover:bg-blue-700 text-white'
                }`}
              >
                <span>{isCopied ? '✓' : '📋'}</span>
                <span>{isCopied ? 'Copied!' : 'Copy to Clipboard'}</span>
              </button>
            </div>
          )}

          {/* Tip */}
          <div className="p-3 bg-purple-50 border border-purple-200 rounded-lg text-sm text-purple-900">
            💡 <strong>Pro Tip:</strong> Descriptions are generated by Claude and persisted to your CRM database automatically.
          </div>
        </div>

        {/* Footer */}
        <div className="bg-stone-50 px-6 py-4 border-t border-stone-200 rounded-b-xl">
          <button
            onClick={handleClose}
            className="w-full px-4 py-2 bg-stone-200 hover:bg-stone-300 text-stone-800 rounded-lg font-semibold transition-colors"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  );
};

// ── Fallback description if streaming fails ────────────────────────────────────
function buildFallbackDescription(fossil) {
  return `This remarkable ${fossil.species} specimen from the ${fossil.era} period represents ` +
    `a significant addition to our paleontological understanding. Weighing approximately ` +
    `${fossil.weight} kg and estimated at ${fossil.estimatedAge} million years old, this fossil ` +
    `showcases exceptional preservation. The specimen was discovered at ${fossil.discoveryLocation} ` +
    `and is now part of the ${fossil.collectionName} collection.`;
}