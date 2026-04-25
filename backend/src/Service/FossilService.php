<?php

declare(strict_types=1);

namespace PaleoCRM\Service;

use PaleoCRM\Entity\DinoFossil;
use PaleoCRM\Repository\FossilRepository;
use InvalidArgumentException;
use DomainException;

/**
 * FossilService - Business Logic Layer
 * 
 * Orchestrates operations combining:
 * ✨ Repository access
 * ✨ Validation
 * ✨ Business rules
 * ✨ External service integration (AI)
 * ✨ Error handling and logging
 */
final class FossilService
{
    public function __construct(
        private readonly FossilRepository $repository,
        private readonly AIDescriptionService $aiService,
    ) {}

    /**
     * Get all fossils with optional AI descriptions
     * 
     * @param int $page Page number (1-based)
     * @param int $pageSize Results per page
     * @param bool $includeAIDescriptions Auto-populate AI descriptions if missing
     * @return DinoFossil[] Array of fossils with descriptions
     * @throws InvalidArgumentException if pagination parameters invalid
     */
    public function getAllFossils(
        int $page = 1,
        int $pageSize = 20,
        bool $includeAIDescriptions = false
    ): array {
        if ($page < 1 || $pageSize < 1) {
            throw new InvalidArgumentException('Page and pageSize must be positive integers');
        }

        $offset = ($page - 1) * $pageSize;
        $fossils = $this->repository->findAll($pageSize, $offset);

        if ($includeAIDescriptions) {
            foreach ($fossils as $fossil) {
                if ($fossil->getAIDescription() === null) {
                    $description = $this->aiService->generateDescription($fossil);
                    $fossil->setAIDescription($description);
                    $this->repository->update($fossil);
                }
            }
        }

        return $fossils;
    }

    /**
     * Get single fossil by ID with all related data
     * 
     * @param string $id Fossil identifier
     * @return DinoFossil|null Fossil object or null if not found
     */
    public function getFossilById(string $id): ?DinoFossil
    {
        if (empty($id)) {
            throw new InvalidArgumentException('Fossil ID cannot be empty');
        }

        return $this->repository->findById($id);
    }

    /**
     * Create new fossil from user input
     * 
     * @param array<string, mixed> $data User-provided fossil data
     * @return DinoFossil Created fossil with generated ID
     * @throws InvalidArgumentException if data validation fails
     * @throws DomainException if business rules violated
     */
    public function createFossilWithAIDescription(array $data): DinoFossil
    {
        $this->validateFossilData($data);

        // Generate unique ID and timestamp
        $fossilId = $this->generateFossilId();

        // Create fossil entity
        $fossil = new DinoFossil(
            id: $fossilId,
            species: $data['species'],
            collectionName: $data['collectionName'] ?? $data['species'],
            estimatedAge: (int)$data['estimatedAge'],
            weight: (float)$data['weight'],
            status: $data['status'] ?? 'documented',
            discoveryLocation: $data['discoveryLocation'] ?? 'Unknown',
        );

        // Generate AI description if enabled
        if ($data['generateAIDescription'] ?? true) {
            try {
                $aiDescription = $this->aiService->generateDescription($fossil);
                $fossil->setAIDescription($aiDescription);
            } catch (\Exception $e) {
                // Log error but don't fail - creation succeeds without AI
                error_log('AI description generation failed: ' . $e->getMessage());
            }
        }

        // Persist to database
        $this->repository->create($fossil);

        return $fossil;
    }

    /**
     * Update fossil status with validation
     * 
     * @param string $fossilId Fossil to update
     * @param string $newStatus New status value
     * @return void
     * @throws InvalidArgumentException if fossil not found or status invalid
     * @throws DomainException if status transition not allowed
     */
    public function updateStatus(string $fossilId, string $newStatus): void
    {
        $fossil = $this->repository->findById($fossilId);

        if ($fossil === null) {
            throw new InvalidArgumentException("Fossil with ID '{$fossilId}' not found");
        }

        // Validate status transition (prevent invalid transitions)
        $this->validateStatusTransition($fossil->status, $newStatus);

        // Create new fossil with updated status
        $updated = new DinoFossil(
            id: $fossil->id,
            species: $fossil->species,
            collectionName: $fossil->collectionName,
            estimatedAge: $fossil->estimatedAge,
            weight: $fossil->weight,
            status: $newStatus, // ← New status
            discoveryLocation: $fossil->discoveryLocation,
            aiGeneratedDescription: $fossil->getAIDescription(),
        );

        $this->repository->update($updated);
    }

    /**
     * Get velocity report for all dinosaurs
     * Classifies by theoretical velocity
     * 
     * @return array Statistics grouped by velocity category
     */
    public function getVelocityReport(): array
    {
        $fossils = $this->repository->findAll(limit: 1000);

        $velocityCategories = [
            'raptor-speed' => 0,
            'fast' => 0,
            'medium' => 0,
            'slow' => 0,
            'immobile' => 0,
        ];

        foreach ($fossils as $fossil) {
            $velocity = $fossil->getTheoreticalVelocity();

            if ($velocity === 'raptor-speed (40 km/h)') {
                $velocityCategories['raptor-speed']++;
            } elseif (is_int($velocity)) {
                if ($velocity >= 18) {
                    $velocityCategories['fast']++;
                } elseif ($velocity >= 10) {
                    $velocityCategories['medium']++;
                } elseif ($velocity > 0) {
                    $velocityCategories['slow']++;
                } else {
                    $velocityCategories['immobile']++;
                }
            } else {
                $velocityCategories['unknown'] = ($velocityCategories['unknown'] ?? 0) + 1;
            }
        }

        return [
            'total_fossils' => count($fossils),
            'by_velocity' => $velocityCategories,
            'generated_at' => (new \DateTime())->format(\DateTime::ATOM),
            'easter_egg' => '🦕 "If they could travel back in time, they would"',
        ];
    }

    /**
     * Batch update multiple fossils (transaction-safe)
     * 
     * @param array<string, string> $updates Key: fossil_id, Value: new_status
     * @return int Number of successfully updated fossils
     * @throws InvalidArgumentException if any update would be invalid
     */
    public function batchUpdateStatus(array $updates): int
    {
        // Pre-validate all updates before executing
        foreach ($updates as $id => $status) {
            $fossil = $this->repository->findById($id);
            if ($fossil === null) {
                throw new InvalidArgumentException("Fossil with ID '{$id}' not found");
            }
            $this->validateStatusTransition($fossil->status, $status);
        }

        // Execute batch update (all or nothing)
        return $this->repository->batchUpdateStatus($updates);
    }

    /**
     * Export fossils to CSV format
     * Properly escapes data to prevent injection attacks
     * 
     * @param string $status Filter by status (or '' for all)
     * @return string CSV formatted data
     */
    public function exportFossilsToCSV(string $status = ''): string
    {
        $fossils = empty($status)
            ? $this->repository->findAll(limit: 5000)
            : $this->repository->findByStatus($status, limit: 5000);

        $output = '';
        $memoryFile = fopen('php://memory', 'r+');

        if ($memoryFile === false) {
            throw new \RuntimeException('Failed to create memory stream for CSV');
        }

        // Write CSV header
        fputcsv($memoryFile, [
            'ID',
            'Species',
            'Collection Name',
            'Estimated Age (years)',
            'Weight (kg)',
            'Status',
            'Discovery Location',
            'ERA',
        ]);

        // Write fossil data rows (fputcsv handles escaping automatically)
        foreach ($fossils as $fossil) {
            $data = $fossil->toArray();
            fputcsv($memoryFile, [
                $data['id'],
                $data['species'],
                $data['collectionName'],
                $data['estimatedAge'],
                $data['weight'],
                $data['status'],
                $data['discoveryLocation'],
                $data['era'],
            ]);
        }

        // Read from memory stream
        rewind($memoryFile);
        $output = stream_get_contents($memoryFile);
        fclose($memoryFile);

        return $output ?: '';
    }

    /**
     * Are they truly extinct? (Easter Egg Easter Egg!)
     * "Better ask Claude first 🤖"
     * 
     * @return bool True if we should probably ask Claude
     */
    public function areTheyTrulyExtinct(): bool
    {
        // Philosophical question: asks Claude before confirming
        return true; // Always consult Claude
    }

    /**
     * Validate fossil creation data
     * 
     * @param array<string, mixed> $data User input
     * @throws InvalidArgumentException if validation fails
     */
    private function validateFossilData(array $data): void
    {
        if (empty($data['species'])) {
            throw new InvalidArgumentException('Species name is required');
        }

        if (empty($data['estimatedAge']) || (int)$data['estimatedAge'] < 0) {
            throw new InvalidArgumentException('Estimated age must be a positive number');
        }

        if (empty($data['weight']) || (float)$data['weight'] <= 0) {
            throw new InvalidArgumentException('Weight must be a positive number');
        }

        if (isset($data['status'])) {
            try {
                new DinoFossil('temp', $data['species'], $data['species'], 0, 0.1, $data['status']);
            } catch (\InvalidArgumentException $e) {
                throw new InvalidArgumentException('Invalid fossil status: ' . $e->getMessage());
            }
        }
    }

    /**
     * Validate status transition (prevent invalid state changes)
     * 
     * @param string $currentStatus Current status
     * @param string $newStatus Target status
     * @throws DomainException if transition invalid
     */
    private function validateStatusTransition(string $currentStatus, string $newStatus): void
    {
        // Define allowed transitions
        $allowedTransitions = [
            'documented' => ['pending-analysis', 'extinct', 'active'],
            'pending-analysis' => ['documented', 'extinct', 'active'],
            'active' => ['documented', 'extinct'],
            'extinct' => ['documented'], // Can be re-classified
        ];

        if (!isset($allowedTransitions[$currentStatus])) {
            throw new DomainException("Unknown status: {$currentStatus}");
        }

        if (!in_array($newStatus, $allowedTransitions[$currentStatus], true)) {
            throw new DomainException(
                "Invalid transition from '{$currentStatus}' to '{$newStatus}'. Allowed: " .
                implode(', ', $allowedTransitions[$currentStatus])
            );
        }
    }

    /**
     * Generate unique fossil ID
     * Format: FOSSIL_<timestamp>_<random>
     * 
     * @return string Generated UUID-like identifier
     */
    private function generateFossilId(): string
    {
        return sprintf(
            'FOSSIL_%s_%s',
            (new \DateTime())->format('YmdHis'),
            bin2hex(random_bytes(4))
        );
    }
}
