<?php

declare(strict_types=1);

namespace PaleoCRM\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PaleoCRM\Service\FossilService;
use PaleoCRM\Service\AIDescriptionService;
use PaleoCRM\Repository\FossilRepository;
use PaleoCRM\Entity\DinoFossil;
use InvalidArgumentException;
use DomainException;

/**
 * FossilServiceTest - Unit Tests for Business Logic
 * 
 * Demonstrates:
 * ✨ PHPUnit 10+ [Test] attributes
 * ✨ Mocking external dependencies
 * ✨ Data providers for multiple test variants
 * ✨ Proper test naming (behavior-driven)
 * ✨ Given-When-Then structure
 * ✨ Coverage of normal + edge cases
 */
final class FossilServiceTest extends TestCase
{
    private FossilService $service;
    private MockObject|FossilRepository $repositoryMock;
    private MockObject|AIDescriptionService $aiServiceMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(FossilRepository::class);
        $this->aiServiceMock = $this->createMock(AIDescriptionService::class);

        $this->service = new FossilService(
            $this->repositoryMock,
            $this->aiServiceMock
        );
    }

    /**
     * Test: Create fossil with valid data succeeds
     * 
     * Given: Valid fossil creation data
     * When: Creating a new fossil
     * Then: Fossil is persisted and returned with ID
     */
    #[Test]
    public function testCreateFossilWithValidDataSucceeds(): void
    {
        // Arrange
        $validData = [
            'species' => 'Tyrannosaurus rex',
            'collectionName' => 'Prehistoric Museum Collection',
            'estimatedAge' => 66,
            'weight' => 9000.0,
            'discoveryLocation' => 'Hell Creek Formation',
            'status' => 'documented',
            'generateAIDescription' => false,
        ];

        $this->repositoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn('FOSSIL_123456');

        // Act
        $fossil = $this->service->createFossilWithAIDescription($validData);

        // Assert
        $this->assertInstanceOf(DinoFossil::class, $fossil);
        $this->assertEquals('Tyrannosaurus rex', $fossil->species);
        $this->assertEquals(66, $fossil->estimatedAge);
        $this->assertEquals('documented', $fossil->status);
    }

    /**
     * Test: Create fossil without species throws exception
     * 
     * Given: Data with missing species
     * When: Creating a fossil
     * Then: InvalidArgumentException is thrown
     */
    #[Test]
    public function testCreateFossilThrowsOnMissingSpecies(): void
    {
        // Arrange
        $invalidData = [
            'estimatedAge' => 66,
            'weight' => 9000.0,
            // Missing 'species' field
        ];

        // Assert & Act
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Species name is required');

        $this->service->createFossilWithAIDescription($invalidData);
    }

    /**
     * Test: Create fossil with negative age throws exception
     * 
     * Given: Fossil data with negative age
     * When: Creating a fossil
     * Then: InvalidArgumentException is thrown
     */
    #[Test]
    public function testCreateFossilThrowsOnNegativeAge(): void
    {
        // Arrange
        $invalidData = [
            'species' => 'Velociraptor',
            'collectionName' => 'Test Collection',
            'estimatedAge' => -50, // ← Invalid
            'weight' => 100.0,
        ];

        // Assert & Act
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Estimated age must be a positive number');

        $this->service->createFossilWithAIDescription($invalidData);
    }

    /**
     * Test: Create fossil with zero weight throws exception
     */
    #[Test]
    public function testCreateFossilThrowsOnZeroWeight(): void
    {
        // Arrange
        $invalidData = [
            'species' => 'Archaeopteryx',
            'collectionName' => 'Test',
            'estimatedAge' => 150,
            'weight' => 0, // ← Invalid
        ];

        // Assert & Act
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Weight must be a positive number');

        $this->service->createFossilWithAIDescription($invalidData);
    }

    /**
     * Test: Create fossil with invalid status throws exception
     */
    #[Test]
    public function testCreateFossilThrowsOnInvalidStatus(): void
    {
        // Arrange
        $invalidData = [
            'species' => 'Triceratops',
            'collectionName' => 'Test',
            'estimatedAge' => 66,
            'weight' => 6000.0,
            'status' => 'super-mega-extinct', // ← Not in allowed list
        ];

        // Assert & Act
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid fossil status');

        $this->service->createFossilWithAIDescription($invalidData);
    }

    /**
     * Test: Update fossil status with valid transition succeeds
     */
    #[Test]
    public function testUpdateStatusWithValidTransitionSucceeds(): void
    {
        // Arrange
        $fossil = $this->createTestFossil(status: 'documented');

        $this->repositoryMock
            ->expects($this->once())
            ->method('findById')
            ->with('fossil_123')
            ->willReturn($fossil);

        $this->repositoryMock
            ->expects($this->once())
            ->method('update');

        // Act
        $this->service->updateStatus('fossil_123', 'pending-analysis');

        // Assert - no exception thrown
        $this->assertTrue(true);
    }

    /**
     * Test: Update fossil with invalid status transition throws exception
     * 
     * Transition rule: 'extinct' can only transition to 'documented'
     * But we try: 'extinct' → 'pending-analysis' (invalid)
     */
    #[Test]
    public function testUpdateStatusThrowsOnInvalidTransition(): void
    {
        // Arrange
        $fossil = $this->createTestFossil(status: 'extinct');

        $this->repositoryMock
            ->expects($this->once())
            ->method('findById')
            ->willReturn($fossil);

        // Act & Assert
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid transition');

        $this->service->updateStatus('fossil_123', 'pending-analysis');
    }

    /**
     * Test: Update non-existent fossil throws exception
     */
    #[Test]
    public function testUpdateStatusThrowsOnFossilNotFound(): void
    {
        // Arrange
        $this->repositoryMock
            ->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not found');

        $this->service->updateStatus('non_existent', 'extinct');
    }

    /**
     * Test: GetAllFossils with pagination
     * 
     * Data Provider test: multiple page sizes
     */
    #[Test]
    #[DataProvider('paginationProvider')]
    public function testGetAllFossilsWithPaginationProvider(int $page, int $pageSize): void
    {
        // Arrange
        $mockFossils = [
            $this->createTestFossil('fossil_1'),
            $this->createTestFossil('fossil_2'),
        ];

        $this->repositoryMock
            ->expects($this->once())
            ->method('findAll')
            ->with($pageSize, ($page - 1) * $pageSize)
            ->willReturn($mockFossils);

        // Act
        $result = $this->service->getAllFossils($page, $pageSize, false);

        // Assert
        $this->assertCount(2, $result);
        $this->assertInstanceOf(DinoFossil::class, $result[0]);
    }

    /**
     * Data provider: various pagination scenarios
     * 
     * @return array<string, array<int>>
     */
    public static function paginationProvider(): array
    {
        return [
            'page_1_size_10' => [1, 10],
            'page_1_size_50' => [1, 50],
            'page_2_size_20' => [2, 20],
            'page_5_size_100' => [5, 100],
        ];
    }

    /**
     * Test: GetAllFossils with invalid page number throws exception
     */
    #[Test]
    public function testGetAllFossilsThrowsOnInvalidPage(): void
    {
        // Act & Assert
        $this->expectException(InvalidArgumentException::class);

        $this->service->getAllFossils(0, 20); // Page < 1
    }

    /**
     * Test: Velocity report calculations
     */
    #[Test]
    public function testGetVelocityReportCalculatesCorrectly(): void
    {
        // Arrange
        $mockFossils = [
            $this->createTestFossil('t-rex', 'Tyrannosaurus rex'),
            $this->createTestFossil('raptor', 'Velociraptor'),
            $this->createTestFossil('triceratops', 'Triceratops'),
        ];

        $this->repositoryMock
            ->expects($this->once())
            ->method('findAll')
            ->with(limit: 1000)
            ->willReturn($mockFossils);

        // Act
        $report = $this->service->getVelocityReport();

        // Assert
        $this->assertIsArray($report);
        $this->assertArrayHasKey('total_fossils', $report);
        $this->assertArrayHasKey('by_velocity', $report);
        $this->assertEquals(3, $report['total_fossils']);
        $this->assertArrayHasKey('raptor-speed', $report['by_velocity']);
    }

    /**
     * Test: Batch update status with valid updates
     */
    #[Test]
    public function testBatchUpdateStatusSucceeds(): void
    {
        // Arrange
        $updates = [
            'fossil_1' => 'extinct',
            'fossil_2' => 'pending-analysis',
        ];

        $this->repositoryMock
            ->expects($this->exactly(2))
            ->method('findById')
            ->withConsecutive(['fossil_1'], ['fossil_2'])
            ->willReturnOnConsecutiveCalls(
                $this->createTestFossil('fossil_1', 'Species1', 'documented'),
                $this->createTestFossil('fossil_2', 'Species2', 'documented'),
            );

        $this->repositoryMock
            ->expects($this->once())
            ->method('batchUpdateStatus')
            ->with($updates)
            ->willReturn(2);

        // Act
        $result = $this->service->batchUpdateStatus($updates);

        // Assert
        $this->assertEquals(2, $result);
    }

    /**
     * Test: Batch update with non-existent fossil throws exception
     */
    #[Test]
    public function testBatchUpdateThrowsOnMissingFossil(): void
    {
        // Arrange
        $updates = [
            'fossil_exists' => 'extinct',
            'fossil_not_exists' => 'extinct', // This one doesn't exist
        ];

        $this->repositoryMock
            ->expects($this->any())
            ->method('findById')
            ->willReturnCallback(fn($id) => $id === 'fossil_exists' ? $this->createTestFossil($id) : null);

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);

        $this->service->batchUpdateStatus($updates);
    }

    /**
     * Test: Export fossils to CSV format
     */
    #[Test]
    public function testExportFossilsToCSVReturnsValidCSV(): void
    {
        // Arrange
        $mockFossils = [
            $this->createTestFossil('fossil_1', 'Tyrannosaurus rex'),
            $this->createTestFossil('fossil_2', 'Velociraptor'),
        ];

        $this->repositoryMock
            ->expects($this->once())
            ->method('findAll')
            ->with(limit: 5000)
            ->willReturn($mockFossils);

        // Act
        $csv = $this->service->exportFossilsToCSV('');

        // Assert
        $this->assertIsString($csv);
        $this->assertStringContainsString('ID', $csv); // Header
        $this->assertStringContainsString('Species', $csv);
        $lines = explode("\n", $csv);
        $this->assertGreaterThanOrEqual(2, count($lines)); // Header + at least 1 data row
    }

    /**
     * Test: Easter egg function
     */
    #[Test]
    public function testAreTheyTrulyExtinctAlwaysConsultsClaude(): void
    {
        // Given the philosophical nature of the question...
        $result = $this->service->areTheyTrulyExtinct();

        // Assert: Always returns true (consult Claude first!)
        $this->assertTrue($result);
    }

    /**
     * Helper: Create test fossil with configurable properties
     * 
     * @param string $id Fossil ID
     * @param string $species Species name
     * @param string $status Fossil status
     * @return DinoFossil
     */
    private function createTestFossil(
        string $id = 'test_fossil',
        string $species = 'Test Species',
        string $status = 'documented'
    ): DinoFossil {
        return new DinoFossil(
            id: $id,
            species: $species,
            collectionName: 'Test Collection',
            estimatedAge: 66,
            weight: 1000.0,
            status: $status,
            discoveryLocation: 'Test Location',
        );
    }
}
