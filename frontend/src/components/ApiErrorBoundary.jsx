import React from 'react';

/**
 * ApiErrorBoundary — catches rendering errors and shows a friendly UI.
 *
 * Usage:
 *   <ApiErrorBoundary>
 *     <Dashboard />
 *   </ApiErrorBoundary>
 */
export class ApiErrorBoundary extends React.Component {
  constructor(props) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error) {
    return { hasError: true, error };
  }

  componentDidCatch(error, info) {
    console.error('[ApiErrorBoundary] Caught error:', error, info);
  }

  handleReset = () => {
    this.setState({ hasError: false, error: null });
  };

  render() {
    if (!this.state.hasError) return this.props.children;

    return (
      <div className="min-h-screen bg-amber-50 flex items-center justify-center p-6">
        <div className="max-w-md w-full bg-white rounded-2xl shadow-xl border border-red-100 overflow-hidden">

          {/* Header */}
          <div className="bg-gradient-to-r from-red-600 to-orange-600 px-6 py-6 text-white text-center">
            <div className="text-5xl mb-2">🦕💥</div>
            <h1 className="text-2xl font-bold">Something Went Extinct</h1>
            <p className="text-red-100 text-sm mt-1">An unexpected error occurred in PaleoCRM</p>
          </div>

          {/* Body */}
          <div className="px-6 py-6 space-y-4">
            <div className="bg-red-50 border border-red-200 rounded-lg p-4">
              <p className="text-red-800 text-sm font-mono break-words">
                {this.state.error?.message ?? 'Unknown error'}
              </p>
            </div>

            <p className="text-stone-600 text-sm">
              This could be a network issue or an API outage. The dinosaurs are investigating.
              Please try again — if the problem persists, check your backend connection.
            </p>

            <div className="flex gap-3">
              <button
                onClick={this.handleReset}
                className="flex-1 px-4 py-2 bg-amber-700 hover:bg-amber-800 text-white rounded-lg font-semibold text-sm transition-colors"
              >
                Try Again 🔄
              </button>
              <button
                onClick={() => window.location.reload()}
                className="flex-1 px-4 py-2 bg-stone-200 hover:bg-stone-300 text-stone-800 rounded-lg font-semibold text-sm transition-colors"
              >
                Full Reload
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }
}