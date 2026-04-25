<?php

declare(strict_types=1);

namespace PaleoCRM\Tests\Integration\Repository;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PaleoCRM\Repository\FossilRepository;
use PaleoCRM\Entity\DinoFossil;
use PDO;

/**
 * FossilRepositoryIntegrationTest
 *
 * Runs against a real SQLite :memory: database — no mocks.
 * Proves the SQL, parameter binding, and hydration all work together.
 */
final class FossilRepositoryIntegrationTest extends TestCase
{
    private PDO $pdo;
    private FossilRepository $repo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->pdo->exec(<<<SQL
            CREATE TABLE fossils (
                id                 TEXT    PRIMARY KEY,
                species            TEXT    NOT NULL,
                collection_name    TEXT    NOT NULL,
                estimated_age      INTEGER NOT NULL,
                weight             REAL    NOT NULL,
                status             TEXT    NOT NULL DEFAULT 'documented',
                discovery_location TEXT    NOT NULL DEFAULT 'Unknown',
                ai_description     TEXT,
                metadata           TEXT,
                created_at         TEXT    DEFAULT (strftime('%Y-%m-%dT%H:%M:%SZ','now'))
            )
        SQL);

        $this->repo = new FossilRepository($this->pdo);
    }

    // ── create / findById ─────────────────────────────────────────────────────

    #[Test]
    public function createPersistsFossilAndFindByIdReturnsIt(): void
    {
        $fossil = $this->makeFossil('INT_001', 'Tyrannosaurus rex', 'documented');

        $returnedId = $this->repo->create($fossil);

        $this->assertSame('INT_001', $returnedId);

        $found = $this->repo->findById('INT_001');

        $this->assertNotNull($found);
        $this->assertInstanceOf(DinoFossil::class, $found);
        $this->assertSame('Tyrannosaurus rex', $found->species);
        $this->assertSame(66, $found->estimatedAge);
        $this->assertSame(9000.0, $found->weight);
        $this->assertSame('documented', $found->status);
    }

    #[Test]
    public function findByIdReturnsNullForMissingFossil(): void
    {
        $result = $this->repo->findById('DOES_NOT_EXIST');

        $this->assertNull($result);
    }

    // ── findAll / pagination ──────────────────────────────────────────────────

    #[Test]
    public function findAllReturnsFossilsInDescendingAgeOrder(): void
    {
        $this->repo->create($this->makeFossil('A', 'Young Dino',  'documented', 10));
        $this->repo->create($this->makeFossil('B', 'Old Dino',    'documented', 200));
        $this->repo->create($this->makeFossil('C', 'Medium Dino', 'documented', 100));

        $all = $this->repo->findAll();

        $this->assertCount(3, $all);
        $this->assertSame(200, $all[0]->estimatedAge); // oldest first
        $this->assertSame(100, $all[1]->estimatedAge);
        $this->assertSame(10,  $all[2]->estimatedAge);
    }

    #[Test]
    public function findAllRespectsPaginationLimitAndOffset(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->repo->create($this->makeFossil("ID_{$i}", "Species {$i}", 'documented', $i * 10));
        }

        $page2 = $this->repo->findAll(limit: 2, offset: 2);

        $this->assertCount(2, $page2);
    }

    // ── findByStatus ──────────────────────────────────────────────────────────

    #[Test]
    public function findByStatusFiltersCorrectly(): void
    {
        $this->repo->create($this->makeFossil('E1', 'Extinct Dino',   'extinct'));
        $this->repo->create($this->makeFossil('E2', 'Active Dino',    'active'));
        $this->repo->create($this->makeFossil('E3', 'Extinct Dino 2', 'extinct'));

        $extinct = $this->repo->findByStatus('extinct');

        $this->assertCount(2, $extinct);
        foreach ($extinct as $f) {
            $this->assertSame('extinct', $f->status);
        }
    }

    // ── update ────────────────────────────────────────────────────────────────

    #[Test]
    public function updatePersistsStatusAndAIDescription(): void
    {
        $fossil = $this->makeFossil('UPD_001', 'Velociraptor', 'documented');
        $this->repo->create($fossil);

        // Build updated version
        $updated = new DinoFossil(
            id:                'UPD_001',
            species:           $fossil->species,
            collectionName:    $fossil->collectionName,
            estimatedAge:      $fossil->estimatedAge,
            weight:            $fossil->weight,
            status:            'extinct',
            discoveryLocation: $fossil->discoveryLocation,
            aiGeneratedDescription: 'A very fast predator.',
        );

        $this->repo->update($updated);

        $refetched = $this->repo->findById('UPD_001');

        $this->assertSame('extinct', $refetched->status);
        $this->assertSame('A very fast predator.', $refetched->getAIDescription());
    }

    // ── delete ────────────────────────────────────────────────────────────────

    #[Test]
    public function deleteRemovesFossilFromDatabase(): void
    {
        $this->repo->create($this->makeFossil('DEL_001', 'Doomed Dino', 'documented'));

        $this->repo->delete('DEL_001');

        $this->assertNull($this->repo->findById('DEL_001'));
    }

    // ── existsById ────────────────────────────────────────────────────────────

    #[Test]
    public function existsByIdReturnsTrueForExistingFossil(): void
    {
        $this->repo->create($this->makeFossil('EX_001', 'Exists Dino', 'documented'));

        $this->assertTrue($this->repo->existsById('EX_001'));
        $this->assertFalse($this->repo->existsById('GHOST'));
    }

    // ── countByStatus ─────────────────────────────────────────────────────────

    #[Test]
    public function countByStatusReturnsCorrectCount(): void
    {
        $this->repo->create($this->makeFossil('C1', 'Dino A', 'extinct'));
        $this->repo->create($this->makeFossil('C2', 'Dino B', 'extinct'));
        $this->repo->create($this->makeFossil('C3', 'Dino C', 'documented'));

        $this->assertSame(2, $this->repo->countByStatus('extinct'));
        $this->assertSame(1, $this->repo->countByStatus('documented'));
        $this->assertSame(0, $this->repo->countByStatus('active'));
    }

    // ── batchUpdateStatus ─────────────────────────────────────────────────────

    #[Test]
    public function batchUpdateStatusUpdatesAllFossilsAtomically(): void
    {
        $this->repo->create($this->makeFossil('B1', 'Batch Dino 1', 'documented'));
        $this->repo->create($this->makeFossil('B2', 'Batch Dino 2', 'documented'));

        $updated = $this->repo->batchUpdateStatus(['B1' => 'extinct', 'B2' => 'active']);

        $this->assertSame(2, $updated);
        $this->assertSame('extinct', $this->repo->findById('B1')->status);
        $this->assertSame('active',  $this->repo->findById('B2')->status);
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function makeFossil(
        string $id,
        string $species,
        string $status,
        int    $age    = 66,
        float  $weight = 9000.0,
    ): DinoFossil {
        return new DinoFossil(
            id:                $id,
            species:           $species,
            collectionName:    'Test Collection',
            estimatedAge:      $age,
            weight:            $weight,
            status:            $status,
            discoveryLocation: 'Test Formation',
        );
    }
}