<?php

declare(strict_types=1);

namespace PaleoCRM\Repository;

use PaleoCRM\Entity\DinoFossil;
use PDO;
use PDOException;

/**
 * FossilRepository - Modern Data Access Layer
 * 
 * Modern patterns:
 * ✨ PDO with prepared statements (SQL injection safe)
 * ✨ Typed parameters and returns
 * ✨ Exception-based error handling
 * ✨ No globals or coupling to external code
 * ✨ Repository pattern for data abstraction
 */
final class FossilRepository
{
    private const TABLE_NAME = 'fossils';
    private const BATCH_SIZE = 100;

    public function __construct(
        private readonly PDO $pdo,
    ) {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Find all fossils with pagination
     * 
     * @param int $limit Results per page
     * @param int $offset Pagination offset
     * @return DinoFossil[] Array of fossil objects
     * @throws PDOException if query fails
     */
    public function findAll(int $limit = 50, int $offset = 0): array
    {
        try {
            $stmt = $this->pdo->prepare(
                sprintf(
                    'SELECT * FROM %s ORDER BY estimated_age DESC LIMIT :limit OFFSET :offset',
                    self::TABLE_NAME
                )
            );

            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $fossils = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $fossils[] = DinoFossil::fromArray($row);
            }

            return $fossils;
        } catch (PDOException $e) {
            throw new PDOException('Failed to retrieve fossils: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Find fossil by ID
     * 
     * @param string $id Unique fossil identifier
     * @return DinoFossil|null Fossil object or null if not found
     */
    public function findById(string $id): ?DinoFossil
    {
        try {
            $stmt = $this->pdo->prepare(
                sprintf('SELECT * FROM %s WHERE id = :id', self::TABLE_NAME)
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_STR);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? DinoFossil::fromArray($row) : null;
        } catch (PDOException $e) {
            throw new PDOException('Failed to find fossil by ID: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Find fossils by status with pagination
     * 
     * @param string $status Fossil status (extinct, documented, etc.)
     * @param int $limit Results per page
     * @param int $offset Pagination offset
     * @return DinoFossil[] Array of matching fossils
     */
    public function findByStatus(string $status, int $limit = 50, int $offset = 0): array
    {
        try {
            $stmt = $this->pdo->prepare(
                sprintf(
                    'SELECT * FROM %s WHERE status = :status ORDER BY created_at DESC LIMIT :limit OFFSET :offset',
                    self::TABLE_NAME
                )
            );

            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $fossils = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $fossils[] = DinoFossil::fromArray($row);
            }

            return $fossils;
        } catch (PDOException $e) {
            throw new PDOException('Failed to find fossils by status: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Create new fossil in database
     * 
     * @param DinoFossil $fossil Fossil entity to persist
     * @return string Generated fossil ID
     * @throws PDOException if insert fails
     */
    public function create(DinoFossil $fossil): string
    {
        try {
            $data = $fossil->toArray();

            $stmt = $this->pdo->prepare(
                sprintf(
                    'INSERT INTO %s (id, species, collection_name, estimated_age, weight, status, discovery_location, ai_description, metadata) 
                     VALUES (:id, :species, :collection_name, :estimated_age, :weight, :status, :discovery_location, :ai_description, :metadata)',
                    self::TABLE_NAME
                )
            );

            $stmt->bindValue(':id', $data['id'], PDO::PARAM_STR);
            $stmt->bindValue(':species', $data['species'], PDO::PARAM_STR);
            $stmt->bindValue(':collection_name', $data['collectionName'], PDO::PARAM_STR);
            $stmt->bindValue(':estimated_age', $data['estimatedAge'], PDO::PARAM_INT);
            $stmt->bindValue(':weight', $data['weight'], PDO::PARAM_STR);
            $stmt->bindValue(':status', $data['status'], PDO::PARAM_STR);
            $stmt->bindValue(':discovery_location', $data['discoveryLocation'], PDO::PARAM_STR);
            $stmt->bindValue(':ai_description', $data['aiDescription'], PDO::PARAM_STR | PDO::PARAM_NULL);
            $stmt->bindValue(':metadata', json_encode($data['metadata']), PDO::PARAM_STR);

            $stmt->execute();

            return $data['id'];
        } catch (PDOException $e) {
            throw new PDOException('Failed to create fossil: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Update existing fossil in database
     * 
     * @param DinoFossil $fossil Fossil entity with updated data
     * @return bool True if update was successful
     */
    public function update(DinoFossil $fossil): bool
    {
        try {
            $data = $fossil->toArray();

            $stmt = $this->pdo->prepare(
                sprintf(
                    'UPDATE %s SET ai_description = :ai_description, status = :status WHERE id = :id',
                    self::TABLE_NAME
                )
            );

            $stmt->bindValue(':id', $data['id'], PDO::PARAM_STR);
            $stmt->bindValue(':ai_description', $data['aiDescription'], PDO::PARAM_STR | PDO::PARAM_NULL);
            $stmt->bindValue(':status', $data['status'], PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException('Failed to update fossil: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Delete fossil from database
     * 
     * @param string $id Fossil ID to delete
     * @return bool True if delete was successful
     */
    public function delete(string $id): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                sprintf('DELETE FROM %s WHERE id = :id', self::TABLE_NAME)
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException('Failed to delete fossil: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Check if fossil exists by ID
     * 
     * @param string $id Fossil ID
     * @return bool True if fossil exists
     */
    public function existsById(string $id): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                sprintf('SELECT 1 FROM %s WHERE id = :id LIMIT 1', self::TABLE_NAME)
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            throw new PDOException('Failed to check fossil existence: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Count fossils by status
     * 
     * @param string $status Fossil status
     * @return int Count of fossils with given status
     */
    public function countByStatus(string $status): int
    {
        try {
            $stmt = $this->pdo->prepare(
                sprintf('SELECT COUNT(*) as total FROM %s WHERE status = :status', self::TABLE_NAME)
            );
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            throw new PDOException('Failed to count fossils: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Get extinct species report - Easter Egg
     * "More extinct than Tuesday mornings 🦖"
     * 
     * @return array Extinct species statistics
     */
    public function getExtinctSpeciesReport(): array
    {
        try {
            $stmt = $this->pdo->prepare(
                sprintf(
                    'SELECT species, COUNT(*) as count FROM %s WHERE status = :status GROUP BY species',
                    self::TABLE_NAME
                )
            );
            $stmt->bindValue(':status', 'extinct', PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException('Failed to generate extinct species report: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Batch operation: Update multiple fossils with new status
     * Transaction-safe: either all succeed or all fail
     * 
     * @param array<string, string> $updates Key: fossil_id, Value: new_status
     * @return int Number of successfully updated fossils
     * @throws PDOException if transaction fails
     */
    public function batchUpdateStatus(array $updates): int
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                sprintf('UPDATE %s SET status = :status WHERE id = :id', self::TABLE_NAME)
            );

            $count = 0;
            foreach ($updates as $id => $status) {
                $stmt->bindValue(':id', $id, PDO::PARAM_STR);
                $stmt->bindValue(':status', $status, PDO::PARAM_STR);
                
                if ($stmt->execute()) {
                    $count++;
                }
            }

            $this->pdo->commit();

            return $count;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw new PDOException('Batch update failed: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
