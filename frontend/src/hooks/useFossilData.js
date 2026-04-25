import { useState, useEffect } from 'react';

/**
 * useFossilData Hook - Custom hook for fossil data management
 * 
 * Manages:
 * - Fetching fossils from real PHP API
 * - Pagination state
 * - Loading and error states
 * - Selected fossil state
 */
export const useFossilData = () => {
  const [fossils, setFossils] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [page, setPage] = useState(1);
  const [selectedFossil, setSelectedFossil] = useState(null);
  const [status, setStatus] = useState(''); // Status filter

  useEffect(() => {
    fetchFossils();
  }, [page, status]);

  const fetchFossils = async () => {
    try {
      setLoading(true);
      setError(null);

      // Build query parameters
      const params = new URLSearchParams({
        page: page.toString(),
        pageSize: '20',
      });

      if (status) {
        params.append('status', status);
      }

      // Call real API endpoint
      const response = await fetch(
        `http://localhost:8000/api/fossils?${params.toString()}`,
        {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
          },
        }
      );

      if (!response.ok) {
        throw new Error(`API error: ${response.status} ${response.statusText}`);
      }

      const data = await response.json();

      if (!data.success) {
        throw new Error(data.error || 'Failed to fetch fossils');
      }

      setFossils(data.data || []);
    } catch (err) {
      setError(err?.message || 'Failed to fetch fossils');
      console.error('Error fetching fossils:', err);
      // Fall back to mock data for demo purposes
      setFossils(getMockFossils(0, 20));
    } finally {
      setLoading(false);
    }
  };

  return {
    fossils,
    loading,
    error,
    page,
    setPage,
    selectedFossil,
    setSelectedFossil,
    status,
    setStatus,
    refetch: fetchFossils, // Allow manual refetch after create/update
  };
};

/**
 * Generate mock fossil data for demo fallback
 * Used when API is unavailable for testing
 */
const getMockFossils = (offset, limit) => {
  const allFossils = [
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
      aiDescription: 'Apex predator of the Late Cretaceous era, this Tyrannosaurus rex specimen represents...',
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
      aiDescription: 'Swift pack hunter with exceptional intelligence and cooperative hunting strategies...',
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
      aiDescription: 'Massive long-necked sauropod reaching heights up to 13 meters...',
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

  return allFossils.slice(offset, offset + limit);
};
