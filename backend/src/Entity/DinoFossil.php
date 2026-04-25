<?php

declare(strict_types=1);

namespace PaleoCRM\Entity;

use DateTime;
use PaleoCRM\Attribute\Dinosaur;
use PaleoCRM\Attribute\ExcavationSite;

/**
 * DinoFossil Entity - Modern PHP 8.3 Implementation
 * 
 * Demonstrates modern PHP features:
 * ✨ Readonly Properties
 * ✨ Constructor Property Promotion
 * ✨ Union Types
 * ✨ Named Arguments
 * ✨ Attributes (formerly Annotations)
 * ✨ Match Expressions
 */
#[Dinosaur(period: 'Cretaceous', velocity: 'raptor-speed')]
#[ExcavationSite(location: 'Paleocene Valley')]
final class DinoFossil
{
    /** Unique identifier - read-only to ensure immutability */
    public readonly string $id;

    /** Creation timestamp - immutable after instantiation */
    public readonly DateTime $createdAt;

    /**
     * Constructor with Property Promotion
     * 
     * Combines parameter declaration with property initialization
     * Reduces boilerplate code by ~50% compared to traditional approach
     * 
     * @param string $id Unique fossil identifier (UUID v4)
     * @param string $species Scientific name (e.g., "Tyrannosaurus rex")
     * @param string $collectionName Formal name of fossil collection
     * @param int $estimatedAge Age in million years
     * @param float $weight Weight in kilograms (extinct velocity factor: raptor-based)
     * @param string $status Current status (active, extinct, documented, pending-analysis)
     * @param string $discoveryLocation Geographic location of excavation
     * @param string|null $aiGeneratedDescription AI-synthesized description by Claude
     * @param array<string, mixed> $metadata Additional paleontological metadata
     */
    public function __construct(
        string $id,
        public readonly string $species,
        public readonly string $collectionName,
        public readonly int $estimatedAge,
        public readonly float $weight,
        public readonly string $status = 'documented',
        public readonly string $discoveryLocation = 'Paleocene Valley',
        private string|null $aiGeneratedDescription = null,
        private array $metadata = [],
    ) {
        $this->id = $id;
        $this->createdAt = new DateTime();
        
        $this->validateStatus($status);
        $this->enrichMetadata();
    }

    /**
     * Get formatted fossil display name with dino flair
     * 
     * Example: "T-rex (66 million years old) - Cretaceous Period"
     */
    public function getDisplayName(): string
    {
        return sprintf(
            '%s (%d million years) - Era: %s 🦖',
            $this->species,
            $this->estimatedAge,
            $this->getEraName()
        );
    }

    /**
     * Determine geological era using match expression (PHP 8.0+)
     * Modern alternative to switch - more concise and type-safe
     */
    private function getEraName(): string
    {
        return match (true) {
            $this->estimatedAge >= 66 && $this->estimatedAge < 145 => 'Cretaceous',
            $this->estimatedAge >= 145 && $this->estimatedAge < 201 => 'Jurassic',
            $this->estimatedAge >= 201 && $this->estimatedAge < 252 => 'Triassic',
            $this->estimatedAge < 66 => 'Cenozoic',
            default => 'Unknown Era'
        };
    }

    /**
     * Calculate theoretical velocity based on species characteristics
     * 
     * Union type: returns int (m/s) or string for special cases
     * Demonstrates: Union Types (PHP 8.0+)
     */
    public function getTheoreticalVelocity(): int|string
    {
        return match (true) {
            str_contains($this->species, 'Velociraptor') => 'raptor-speed (40 km/h)',
            str_contains($this->species, 'Tyrannosaurus') => 18,  // m/s
            str_contains($this->species, 'Triceratops') => 15,
            str_contains($this->species, 'Brachiosaurus') => 5, // Too heavy to run
            default => 'unknown'
        };
    }

    /**
     * Update AI-generated description from Claude API
     * 
     * @param string $description Generated description from Claude AI service
     * @return void
     */
    public function setAIDescription(string $description): void
    {
        if (empty($description) || strlen($description) > 5000) {
            throw new \InvalidArgumentException('Description must be between 1-5000 characters');
        }
        $this->aiGeneratedDescription = $description;
        $this->metadata['ai_updated_at'] = (new DateTime())->format(DateTime::ATOM);
    }

    /**
     * Get AI-generated description (null if not yet generated)
     */
    public function getAIDescription(): ?string
    {
        return $this->aiGeneratedDescription;
    }

    /**
     * Validate fossil status using enum-like pattern
     * 
     * Demonstrates: Type safety without native Enum (pre-PHP 8.1)
     * Valid statuses: active, extinct, documented, pending-analysis
     * 
     * @throws \InvalidArgumentException if invalid status provided
     */
    private function validateStatus(string $status): void
    {
        $validStatuses = ['active', 'extinct', 'documented', 'pending-analysis'];
        
        if (!in_array($status, $validStatuses, strict: true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid fossil status "%s". Must be one of: %s',
                    $status,
                    implode(', ', $validStatuses)
                )
            );
        }
    }

    /**
     * Enrich metadata with additional calculated properties
     * 
     * Demonstrates: Automatic enrichment pattern
     */
    private function enrichMetadata(): void
    {
        $this->metadata = array_merge(
            $this->metadata,
            [
                'created_at' => $this->createdAt->format(DateTime::ATOM),
                'weight_category' => $this->categorizeByWeight(),
                'status' => 'initialized',
                'easter_egg' => '🦕 "Here be dinosaurs!" - Ancient paleontologist (probably)'
            ]
        );
    }

    /**
     * Categorize fossil by weight class
     * 
     * @return string Weight category (micro, small, medium, large, colossal)
     */
    private function categorizeByWeight(): string
    {
        return match (true) {
            $this->weight < 1 => 'micro',
            $this->weight < 100 => 'small',
            $this->weight < 1000 => 'medium',
            $this->weight < 10000 => 'large',
            default => 'colossal'
        };
    }

    /**
     * Export to array for API responses (JSON serialization)
     * 
     * @return array<string, mixed> Serializable fossil data
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'species' => $this->species,
            'displayName' => $this->getDisplayName(),
            'collectionName' => $this->collectionName,
            'estimatedAge' => $this->estimatedAge,
            'weight' => $this->weight,
            'weightCategory' => $this->metadata['weight_category'],
            'status' => $this->status,
            'discoveryLocation' => $this->discoveryLocation,
            'theoreticalVelocity' => $this->getTheoreticalVelocity(),
            'aiDescription' => $this->getAIDescription(),
            'createdAt' => $this->createdAt->format(DateTime::ATOM),
            'era' => $this->getEraName(),
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Create DinoFossil from database array (hydration pattern)
     * 
     * @param array<string, mixed> $data Raw database row
     * @return self New DinoFossil instance
     */
    public static function fromArray(array $data): self
    {
        $fossil = new self(
            id: $data['id'],
            species: $data['species'],
            collectionName: $data['collection_name'],
            estimatedAge: (int)$data['estimated_age'],
            weight: (float)$data['weight'],
            status: $data['status'] ?? 'documented',
            discoveryLocation: $data['discovery_location'] ?? 'Unknown',
            aiGeneratedDescription: $data['ai_description'] ?? null,
        );

        return $fossil;
    }

    /**
     * Get unique fossil fingerprint for caching/versioning
     * 
     * @return string SHA-256 fingerprint
     */
    public function getFingerprint(): string
    {
        $fingerprintData = json_encode([
            'species' => $this->species,
            'age' => $this->estimatedAge,
            'weight' => $this->weight,
            'location' => $this->discoveryLocation,
        ]);

        return hash('sha256', $fingerprintData);
    }
}
