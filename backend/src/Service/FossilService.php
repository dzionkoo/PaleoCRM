<?php

declare(strict_types=1);

namespace PaleoCRM\Service;

use PaleoCRM\Entity\DinoFossil;
use PaleoCRM\Repository\FossilRepository;
use InvalidArgumentException;
use DomainException;

/**
 * FossilService - Business Logic Layer
 */
final class FossilService
{
    public function __construct(
        private readonly FossilRepository $repository,
        private readonly AIDescriptionService $aiService,
    ) {}

    /**
     * Get fossils with optional status filter and AI descriptions.
     *
     * @param int    $page                  1-based page number
     * @param int    $pageSize              Results per page
     * @param bool   $includeAIDescriptions Auto-generate AI descriptions if missing
     * @param string $status                Filter by status; '' means all statuses
     * @return DinoFossil[]
     */
    public function getAllFossils(
        int    $page                  = 1,
        int    $pageSize              = 20,
        bool   $includeAIDescriptions = false,
        string $status                = '',        // ← NEW: '' = no filter
    ): array {
        if ($page < 1 || $pageSize < 1) {
            throw new InvalidArgumentException('page and pageSize must be positive integers');
        }

        $offset = ($page - 1) * $pageSize;

        // Delegate to the right repository method based on whether a status was requested
        $fossils = $status !== ''
            ? $this->repository->findByStatus($status, $pageSize, $offset)
            : $this->repository->findAll($pageSize, $offset);

        if ($includeAIDescriptions) {
            foreach ($fossils as $fossil) {
                if ($fossil->getAIDescription() === null) {
                    try {
                        $description = $this->aiService->generateDescription($fossil);
                        $fossil->setAIDescription($description);
                        $this->repository->update($fossil);
                    } catch (\Exception $e) {
                        error_log('AI description generation failed: ' . $e->getMessage());
                    }
                }
            }
        }

        return $fossils;
    }

    /**
     * Get single fossil by ID.
     */
    public function getFossilById(string $id): ?DinoFossil
    {
        if (empty($id)) {
            throw new InvalidArgumentException('Fossil ID cannot be empty');
        }

        return $this->repository->findById($id);
    }

    /**
     * Create a new fossil and optionally generate an AI description.
     *
     * @param array<string, mixed> $data
     */
    public function createFossilWithAIDescription(array $data): DinoFossil
    {
        $this->validateFossilData($data);

        $fossil = new DinoFossil(
            id:                $this->generateFossilId(),
            species:           $data['species'],
            collectionName:    $data['collectionName'] ?? $data['species'],
            estimatedAge:      (int)   $data['estimatedAge'],
            weight:            (float) $data['weight'],
            status:            $data['status']            ?? 'documented',
            discoveryLocation: $data['discoveryLocation'] ?? 'Unknown',
        );

        if ($data['generateAIDescription'] ?? true) {
            try {
                $fossil->setAIDescription($this->aiService->generateDescription($fossil));
            } catch (\Exception $e) {
                error_log('AI description generation failed: ' . $e->getMessage());
            }
        }

        $this->repository->create($fossil);

        return $fossil;
    }

    /**
     * Transition a fossil to a new status.
     */
    public function updateStatus(string $fossilId, string $newStatus): void
    {
        $fossil = $this->repository->findById($fossilId);

        if ($fossil === null) {
            throw new InvalidArgumentException("Fossil '{$fossilId}' not found");
        }

        $this->validateStatusTransition($fossil->status, $newStatus);

        $updated = new DinoFossil(
            id:                    $fossil->id,
            species:               $fossil->species,
            collectionName:        $fossil->collectionName,
            estimatedAge:          $fossil->estimatedAge,
            weight:                $fossil->weight,
            status:                $newStatus,
            discoveryLocation:     $fossil->discoveryLocation,
            aiGeneratedDescription: $fossil->getAIDescription(),
        );

        $this->repository->update($updated);
    }

    /**
     * Velocity report — classifies every fossil by theoretical velocity.
     */
    public function getVelocityReport(): array
    {
        $fossils = $this->repository->findAll(limit: 1000);

        $cats = ['raptor-speed' => 0, 'fast' => 0, 'medium' => 0, 'slow' => 0, 'immobile' => 0];

        foreach ($fossils as $fossil) {
            $v = $fossil->getTheoreticalVelocity();

            if ($v === 'raptor-speed (40 km/h)') {
                $cats['raptor-speed']++;
            } elseif (is_int($v)) {
                if ($v >= 18)     $cats['fast']++;
                elseif ($v >= 10) $cats['medium']++;
                elseif ($v > 0)   $cats['slow']++;
                else              $cats['immobile']++;
            } else {
                $cats['unknown'] = ($cats['unknown'] ?? 0) + 1;
            }
        }

        return [
            'total_fossils' => count($fossils),
            'by_velocity'   => $cats,
            'generated_at'  => (new \DateTime())->format(\DateTime::ATOM),
        ];
    }

    /**
     * Batch-update statuses (all-or-nothing).
     *
     * @param array<string, string> $updates  fossil_id => new_status
     */
    public function batchUpdateStatus(array $updates): int
    {
        foreach ($updates as $id => $status) {
            $fossil = $this->repository->findById($id);
            if ($fossil === null) {
                throw new InvalidArgumentException("Fossil '{$id}' not found");
            }
            $this->validateStatusTransition($fossil->status, $status);
        }

        return $this->repository->batchUpdateStatus($updates);
    }

    /**
     * Export fossils to CSV.
     */
    public function exportFossilsToCSV(string $status = ''): string
    {
        $fossils = $status !== ''
            ? $this->repository->findByStatus($status, limit: 5000)
            : $this->repository->findAll(limit: 5000);

        $mem = fopen('php://memory', 'r+');
        if ($mem === false) {
            throw new \RuntimeException('Failed to open memory stream');
        }

        fputcsv($mem, ['ID', 'Species', 'Collection Name', 'Estimated Age (MY)', 'Weight (kg)', 'Status', 'Discovery Location', 'Era']);

        foreach ($fossils as $fossil) {
            $d = $fossil->toArray();
            fputcsv($mem, [$d['id'], $d['species'], $d['collectionName'], $d['estimatedAge'], $d['weight'], $d['status'], $d['discoveryLocation'], $d['era']]);
        }

        rewind($mem);
        $csv = stream_get_contents($mem);
        fclose($mem);

        return $csv ?: '';
    }

    public function areTheyTrulyExtinct(): bool
    {
        return true; // Always consult Claude
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function validateFossilData(array $data): void
    {
        if (empty($data['species'])) {
            throw new InvalidArgumentException('Species name is required');
        }
        if (!isset($data['estimatedAge']) || (int) $data['estimatedAge'] < 0) {
            throw new InvalidArgumentException('Estimated age must be a positive number');
        }
        if (empty($data['weight']) || (float) $data['weight'] <= 0) {
            throw new InvalidArgumentException('Weight must be a positive number');
        }
        if (isset($data['status'])) {
            try {
                new DinoFossil('tmp', $data['species'], $data['species'], 0, 0.1, $data['status']);
            } catch (\InvalidArgumentException $e) {
                throw new InvalidArgumentException('Invalid fossil status: ' . $e->getMessage());
            }
        }
    }

    private function validateStatusTransition(string $current, string $next): void
    {
        $allowed = [
            'documented'       => ['pending-analysis', 'extinct', 'active'],
            'pending-analysis' => ['documented', 'extinct', 'active'],
            'active'           => ['documented', 'extinct'],
            'extinct'          => ['documented'],
        ];

        if (!isset($allowed[$current])) {
            throw new DomainException("Unknown status: {$current}");
        }
        if (!in_array($next, $allowed[$current], true)) {
            throw new DomainException(
                "Invalid transition '{$current}' → '{$next}'. Allowed: " . implode(', ', $allowed[$current])
            );
        }
    }

    private function generateFossilId(): string
    {
        return sprintf('FOSSIL_%s_%s', (new \DateTime())->format('YmdHis'), bin2hex(random_bytes(4)));
    }
}