<!-- ========================================
     CLAUDE CODE REFACTORING PROMPT
     ======================================== -->

# 🦖 PaleoCRM Legacy Refactoring Task for Claude Code

## Context

We're modernizing a PHP 5.6/7.4 fossil management system (`old_fossil_manager.php`) to PHP 8.3 with Symfony components.

### Current State (LEGACY - DO NOT USE)
- File: `backend/src/Legacy/old_fossil_manager.php`
- Issues: SQL injection vulnerabilities, deprecated mysql_* functions, global state, weak typing, poor error handling
- Functions: `get_all_fossils()`, `create_fossil()`, `update_fossil_status()`, `get_fossil_with_stats()`, `delete_fossil()`, `export_fossils_csv()`

### Target State (MODERN - PRODUCTION READY)
- Create `FossilRepository.php` using Symfony/PDO
- Create `FossilService.php` with business logic
- Create `FossilController.php` with REST endpoints
- Create `DinoFossilTest.php` with PHPUnit tests
- Use PHP 8.3 features: typed properties, union types, attributes, constructor promotion

---

## Refactoring Prompt for Claude Code

### Part 1: Create Modern Repository Layer

**File to create:** `backend/src/Repository/FossilRepository.php`

**Requirements:**
1. Use prepared statements with PDO to eliminate SQL injection
2. Implement type hints for all parameters and returns
3. Single responsibility: data access operations only
4. Support pagination (limit, offset)
5. Add batch operations for performance
6. Include proper exception handling

**Sample method signatures (create the full implementation):**
```php
namespace PaleoCRM\Repository;

final class FossilRepository
{
    public function __construct(private \PDO $pdo) {}
    
    public function findAll(int $limit = 50, int $offset = 0): array
    
    public function findById(string $id): ?array
    
    public function findByStatus(string $status, int $limit = 50): array
    
    public function create(DinoFossil $fossil): string // returns id
    
    public function update(DinoFossil $fossil): bool
    
    public function delete(string $id): bool
    
    public function existsById(string $id): bool
    
    public function countByStatus(string $status): int
}
```

**Dino Easter Egg:** Add placeholder method `getExtinctSpeciesReport()` with comment "More extinct than Tuesday mornings"

---

### Part 2: Create Service Layer

**File to create:** `backend/src/Service/FossilService.php`

**Requirements:**
1. Implement business logic (validation, calculations, workflow)
2. Use dependency injection for repository
3. Add transactional operations where needed
4. Implement caching strategy for frequently accessed data
5. Use Result pattern for better error handling (or Exceptions for critical errors)
6. Add logging capabilities

**Sample method signatures:**
```php
namespace PaleoCRM\Service;

final class FossilService
{
    public function __construct(
        private FossilRepository $repository,
        private AIDescriptionService $aiService,
    ) {}
    
    /**
     * Get all fossils with their AI descriptions
     * @return DinoFossil[] With populated descriptions if available
     */
    public function getAllFossils(int $page = 1, int $pageSize = 20): array
    
    /**
     * Create a new fossil with AI description generation
     * @throws \InvalidArgumentException if data is invalid
     */
    public function createFossilWithAIDescription(array $data): DinoFossil
    
    /**
     * Update fossil status with audit logging
     * @throws \DomainException if status transition is invalid
     */
    public function updateStatus(string $fossilId, string $newStatus): void
    
    /**
     * Get velocity report for all dinosaurs
     * @return array Collection statistics by velocity category
     */
    public function getVelocityReport(): array
    
    /**
     * Batch update multiple fossils (transaction-safe)
     * @param array<string, mixed> $updates Key: fossil_id, Value: new_status
     * @throws \Exception if any update fails
     */
    public function batchUpdateStatus(array $updates): int // returns count updated
}
```

**Dino Easter Egg:** Add method `areTheyTrulyExtinct(): bool` with comment about checking with Claude first

---

### Part 3: Create REST Controller

**File to create:** `backend/src/Controller/FossilController.php`

**Requirements:**
1. RESTful endpoints: GET (list, single), POST (create), PUT (update), DELETE
2. Proper HTTP status codes (200, 201, 400, 404, 500)
3. Request validation using PHP 8.3 attributes or validation rules
4. JSON response formatting
5. Error responses with meaningful messages

**Endpoints to implement:**
```
GET    /api/fossils                    (list all with pagination)
GET    /api/fossils/{id}               (get single)
POST   /api/fossils                    (create)
PUT    /api/fossils/{id}               (update)
DELETE /api/fossils/{id}               (delete)
POST   /api/fossils/{id}/ai-describe   (trigger AI description)
GET    /api/statistics/velocity        (dino velocity report)
POST   /api/fossils/export/csv         (export to CSV)
```

**Sample controller structure:**
```php
namespace PaleoCRM\Controller;

require_once __DIR__ . '/../../vendor/autoload.php';

final class FossilController
{
    public function __construct(private FossilService $service) {}
    
    public function list(array $query): array // Returns JSON-ready array
    public function show(string $id): array
    public function create(array $data): array
    public function update(string $id, array $data): array
    public function delete(string $id): array
    private function validateRequest(array $data, string $action): void
}
```

**Dino Easter Egg:** Add response header or JSON field `X-Dino-Powered: "Velociraptors optimize this endpoint"`

---

### Part 4: Create Unit Tests

**File to create:** `backend/tests/Unit/Service/FossilServiceTest.php`

**Requirements:**
1. Use PHPUnit 10+
2. Mock all external dependencies (Repository, AIService)
3. Test normal flow, edge cases, and error scenarios
4. Aim for 80%+ code coverage
5. Use meaningful test names describing behavior (not just "testCreate")
6. Include Data Providers for multiple test cases

**Test cases to cover:**
```php
- testCreateFossilWithValidData()
- testCreateFossilThrowsOnInvalidStatus()
- testCreateFossilThrowsOnNegativeAge()
- testUpdateStatusTransitionValidation()
- testGetAllFossilsPagination()
- testGetVelocityReportCalculations()
- testBatchUpdateRolledBackOnFailure()
- testAIDependencyInjection()
```

**Sample test structure:**
```php
namespace PaleoCRM\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;

final class FossilServiceTest extends TestCase
{
    private FossilService $service;
    private MockObject|FossilRepository $repository;
    
    protected function setUp(): void
    {
        $this->repository = $this->createMock(FossilRepository::class);
        $this->service = new FossilService($this->repository);
    }
    
    #[Test]
    public function testCreateFossilWithValidData(): void
    {
        // Arrange, Act, Assert structure
    }
}
```

**Dino Easter Egg:** Add test helper method `generateRandomDinosaurSpecies(): string` that returns funny species names

---

### Part 5: Create Configuration & Setup Files

**File to create:** `backend/composer.json`

**Requirements:**
- PHP 8.3 minimum version
- Include: symfony/http-kernel, symfony/routing, PDO extension
- Include: PHPUnit for testing
- Include: Dev tools: phpstan (static analysis), phpcs (code style)

**PSR Standards:**
- PSR-12 for code style
- PSR-4 for autoloading

---

## Success Criteria

✅ All legacy `mysql_*` functions replaced with PDO prepared statements  
✅ All functions have return type declarations  
✅ All parameters have type hints (no weak typing)  
✅ No global variables or state  
✅ Proper exception handling instead of die()/echo() errors  
✅ PHPUnit tests with >80% coverage  
✅ Database queries optimized (no N+1 problems)  
✅ CSV export properly escaped using fputcsv()  
✅ Dino Easter eggs incorporated throughout  
✅ Modern PHP 8.3 features utilized where applicable  

---

## Bonus Features (Optional)

- Add caching layer (Redis/APCu) for fossil list
- Implement soft delete pattern
- Add database migration system (Doctrine)
- Add OpenAPI/Swagger documentation
- Add GraphQL endpoint alternative to REST
- Add background job queue for AI description generation

---

## How to Use This Prompt

1. Copy this entire PROMPT to Claude Code chat
2. Ask Claude to "refactor following this specification"
3. Claude will generate all required files with proper structure
4. Review generated code (focus on: type safety, error handling, test coverage)
5. Integrate into your project
6. Run: `vendor/bin/phpunit` to verify tests pass
7. Run: `vendor/bin/phpstan analyse src` to catch type errors

**Note:** This prompt demonstrates how to use Claude Code for major refactoring tasks systematically.

