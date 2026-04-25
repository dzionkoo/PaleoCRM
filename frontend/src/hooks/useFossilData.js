import { useState, useEffect, useCallback } from 'react';

const API_BASE = import.meta.env.VITE_API_URL ?? 'http://localhost:8000';

/**
 * useFossilData — Custom hook for fossil data management.
 *
 * Fetches real data from the PHP backend.
 * Falls back to mock data only in development when the API is unreachable,
 * so the UI stays useful without a running server.
 */
export const useFossilData = () => {
  const [fossils, setFossils] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [page, setPage] = useState(1);
  const [pageSize] = useState(20);
  const [selectedFossil, setSelectedFossil] = useState(null);
  const [status, setStatus] = useState('');         // '' = all statuses
  const [hasNextPage, setHasNextPage] = useState(false);

  const fetchFossils = useCallback(async () => {
    setLoading(true);
    setError(null);

    try {
      const params = new URLSearchParams({
        page: String(page),
        pageSize: String(pageSize),
      });
      if (status) params.append('status', status);

      const res = await fetch(`${API_BASE}/api/fossils?${params}`, {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' },
        signal: AbortSignal.timeout(10_000),
      });

      if (!res.ok) {
        const body = await res.json().catch(() => ({}));
        throw new Error(body.error ?? `HTTP ${res.status}: ${res.statusText}`);
      }

      const data = await res.json();

      if (!data.success) {
        throw new Error(data.error ?? 'Unknown API error');
      }

      const items = data.data ?? [];
      setFossils(items);
      // Infer whether there's a next page from the returned count
      setHasNextPage(items.length === pageSize);
    } catch (err) {
      const message = err?.message ?? 'Failed to fetch fossils';
      setError(message);
      console.warn('[useFossilData] API unavailable, falling back to mock data:', message);

      // Development fallback — removed in production builds via VITE_USE_MOCK
      if (import.meta.env.DEV || import.meta.env.VITE_USE_MOCK === 'true') {
        const offset = (page - 1) * pageSize;
        setFossils(getMockFossils(offset, pageSize));
        setHasNextPage(offset + pageSize < MOCK_FOSSILS.length);
      }
    } finally {
      setLoading(false);
    }
  }, [page, pageSize, status]);

  useEffect(() => {
    fetchFossils();
  }, [fetchFossils]);

  return {
    fossils,
    loading,
    error,
    page,
    setPage,
    pageSize,
    hasNextPage,
    selectedFossil,
    setSelectedFossil,
    status,
    setStatus,
    refetch: fetchFossils,
  };
};

// ── Mock data (dev / demo fallback only) ──────────────────────────────────────

const MOCK_FOSSILS = [
  {
    id: 'FOSSIL_20260425001',
    species: 'Tyrannosaurus rex',
    displayName: 'T-rex (66 million years) - Era: Cretaceous 🦖',
    collectionName: 'Hell Creek Formation Collection',
    estimatedAge: 66,
    weight: 9000,
    weightCategory: 'colossal',
    status: 'documented',
    discoveryLocation: 'Hell Creek Formation',
    theoreticalVelocity: 18,
    aiDescription: null,
    era: 'Cretaceous',
    createdAt: '2026-04-25T10:00:00+00:00',
  },
  {
    id: 'FOSSIL_20260425002',
    species: 'Velociraptor',
    displayName: 'Velociraptor (75 million years) - Era: Cretaceous 🦖',
    collectionName: 'Deinonychus Study Collection',
    estimatedAge: 75,
    weight: 15,
    weightCategory: 'small',
    status: 'extinct',
    discoveryLocation: 'Paleocene Valley',
    theoreticalVelocity: 'raptor-speed (40 km/h)',
    aiDescription: null,
    era: 'Cretaceous',
    createdAt: '2026-04-24T14:30:00+00:00',
  },
  {
    id: 'FOSSIL_20260425003',
    species: 'Triceratops',
    displayName: 'Triceratops (68 million years) - Era: Cretaceous 🦖',
    collectionName: 'Horned Dinosaur Archive',
    estimatedAge: 68,
    weight: 6000,
    weightCategory: 'large',
    status: 'documented',
    discoveryLocation: 'Lance Formation',
    theoreticalVelocity: 15,
    aiDescription: null,
    era: 'Cretaceous',
    createdAt: '2026-04-23T09:15:00+00:00',
  },
  {
    id: 'FOSSIL_20260425004',
    species: 'Stegosaurus',
    displayName: 'Stegosaurus (150 million years) - Era: Jurassic 🦖',
    collectionName: 'Plated Dinosaur Collection',
    estimatedAge: 150,
    weight: 2700,
    weightCategory: 'large',
    status: 'pending-analysis',
    discoveryLocation: 'Morrison Formation',
    theoreticalVelocity: 10,
    aiDescription: null,
    era: 'Jurassic',
    createdAt: '2026-04-22T11:45:00+00:00',
  },
  {
    id: 'FOSSIL_20260425005',
    species: 'Brachiosaurus',
    displayName: 'Brachiosaurus (155 million years) - Era: Jurassic 🦖',
    collectionName: 'Sauropod Exhibit',
    estimatedAge: 155,
    weight: 56000,
    weightCategory: 'colossal',
    status: 'active',
    discoveryLocation: 'Western Interior Basin',
    theoreticalVelocity: 5,
    aiDescription: null,
    era: 'Jurassic',
    createdAt: '2026-04-21T16:20:00+00:00',
  },
  {
    id: 'FOSSIL_20260425006',
    species: 'Archaeopteryx',
    displayName: 'Archaeopteryx (150 million years) - Era: Jurassic 🦖',
    collectionName: 'Transitional Species Archive',
    estimatedAge: 150,
    weight: 1,
    weightCategory: 'micro',
    status: 'documented',
    discoveryLocation: 'Solnhofen Limestone',
    theoreticalVelocity: 'unknown',
    aiDescription: null,
    era: 'Jurassic',
    createdAt: '2026-04-20T13:00:00+00:00',
  },
];

const getMockFossils = (offset, limit) => MOCK_FOSSILS.slice(offset, offset + limit);