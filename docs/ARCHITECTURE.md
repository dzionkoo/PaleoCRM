# 🏗️ PaleoCRM Architecture Documentation

## System Design

```
┌─────────────────────────────────────────────────────────────┐
│                    Frontend (React 18)                      │
│  Dashboard • FossilCard • AIDescriptionModal • useFossilData │
│  Tailwind CSS • Component-based architecture                 │
└──────────────────────┬──────────────────────────────────────┘
                       │ HTTP/REST
                       │
┌──────────────────────▼──────────────────────────────────────┐
│               API Layer (HTTP Endpoints)                    │
│            FossilController.php                             │
│  GET/POST/PUT/DELETE /api/fossils routes                    │
└──────────────────────┬──────────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────────┐
│            Service Layer (Business Logic)                   │
│  FossilService.php                                          │
│  - Validation & Rules  - Orchestration  - AI Integration     │
│  AIDescriptionService.php                                   │
│  - Claude API Communication                                 │
└──────────────────────┬──────────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────────┐
│         Repository Layer (Data Access)                      │
│  FossilRepository.php                                       │
│  - SQL Query Abstraction  - Transaction Management           │
│  - Prepared Statements (SQL Injection Safe)                 │
└──────────────────────┬──────────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────────┐
│          Entity Layer (Domain Models)                       │
│  DinoFossil.php (PHP 8.3 Modern Features)                   │
│  - Readonly Properties  - Constructor Promotion             │
│  - Union Types  - Attributes  - Validation                  │
└──────────────────────┬──────────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────────┐
│           Database Layer (SQLite/PostgreSQL)                │
│  fossils table • audit_logs table                           │
│  PDO abstraction layer (database agnostic)                  │
└──────────────────────────────────────────────────────────────┘
```

---

## Layer Responsibilities

### 1. Controller Layer (`FossilController.php`)
- **Responsibility**: HTTP request/response handling
- **Does NOT**:
  - Perform complex business logic
  - Access database directly
  - Handle domain validation
- **Delegates to**: FossilService
- **Returns**: JSON responses with proper HTTP status codes

### 2. Service Layer (`FossilService.php`)
- **Responsibility**: Business logic orchestration
- **Handles**:
  - Complex workflows (create with AI description)
  - Validation rules
  - State machine enforcement (status transitions)
  - Dependency coordination
- **Does NOT**:
  - Handle HTTP requests
  - Access database directly
  - Throw framework-specific exceptions
- **Delegates to**: FossilRepository, AIDescriptionService

### 3. Repository Layer (`FossilRepository.php`)
- **Responsibility**: Data persistence abstraction
- **Handles**:
  - SQL query preparation
  - Prepared statements (security)
  - Transaction management
  - Entity hydration
- **Does NOT**:
  - Perform business logic
  - Handle HTTP concerns
  - Know about controllers

### 4. Entity Layer (`DinoFossil.php`)
- **Responsibility**: Domain model representation
- **Handles**:
  - Data validation
  - Value object calculations
  - Read-only properties guarantee immutability
  - Attribute metadata
- **Uses Modern PHP 8.3 Features**:
  - Constructor property promotion
  - Typed properties
  - Union types
  - Attributes
  - Match expressions
  - Named arguments

---

## PHP 8.3 Features Utilized

### 1. Constructor Property Promotion ✨

**Before (PHP 7.4):**
```php
public function __construct(
    $species,
    $age,
    $weight
) {
    $this->species = $species;
    $this->age = $age;
    $this->weight = $weight;
}
```

**After (PHP 8.3):**
```php
public function __construct(
    public readonly string $species,
    public readonly int $age,
    public readonly float $weight,
) {}
```

**Benefit**: 70% less boilerplate code

### 2. Readonly Properties ✨

```php
public readonly string $id;
public readonly DateTime $createdAt;
```

**Benefit**: Immutability guarantee - property can only be set once during object creation

### 3. Union Types ✨

```php
public function getTheoreticalVelocity(): int|string
{
    return match(true) {
        str_contains($this->species, 'Velociraptor') => 'raptor-speed (40 km/h)',
        default => 18,
    };
}
```

**Benefit**: Type safety for methods that can return multiple types

### 4. Named Arguments ✨

```php
// Instead of positional args
$fossil = new DinoFossil('ID', 'T-rex', 'Collection', 66, 9000, 'documented');

// Use named arguments  
$fossil = new DinoFossil(
    id: 'ID',
    species: 'T-rex',
    collectionName: 'Collection',
    estimatedAge: 66,
    weight: 9000,
    status: 'documented'
);
```

**Benefit**: Self-documenting code, refactoring-safe

### 5. Attributes ✨

```php
#[Dinosaur(period: 'Cretaceous', velocity: 'raptor-speed')]
#[ExcavationSite(location: 'Paleocene Valley')]
final class DinoFossil { }
```

**Benefit**: Structured metadata without comments, runtime intrectable

### 6. Match Expressions ✨

```php
$era = match(true) {
    $this->estimatedAge >= 66 && $this->estimatedAge < 145 => 'Cretaceous',
    $this->estimatedAge >= 145 && $this->estimatedAge < 201 => 'Jurassic',
    default => 'Unknown'
};
```

**Benefit**: More concise and type-safe than switch

---

## Modern Patterns Implemented

### 1. Repository Pattern
- Data access abstraction
- Database-agnostic queries
- Prepared statements for security

### 2. Service Layer Pattern
- Business logic isolation
- Transaction management
- External service coordination

### 3. Dependency Injection
- Constructor injection for all dependencies
- No global state
- Testability guaranteed

### 4. Value Objects
- DinoFossil as domain model
- Encapsulated validation
- Immutable readonly properties

### 5. Exception-Based Error Handling
- InvalidArgumentException for validation
- DomainException for business rules
- PDOException for database errors

### 6. Data Hydration
- `DinoFossil::fromArray()` for database→object
- `toArray()` for object→JSON
- Consistent serialization

---

## Testing Strategy

### Unit Tests (PHPUnit)
- Mock all external dependencies
- Test business logic in isolation
- >80% code coverage target
- Data providers for multiple scenarios

### Test File Structure
```
tests/
├── Unit/
│   ├── Service/
│   │   └── FossilServiceTest.php
│   └── Entity/
│       └── DinoFossilTest.php
```

### Running Tests
```bash
# Run all tests
vendor/bin/phpunit

# With coverage report
vendor/bin/phpunit --coverage-html=coverage
```

---

## Legacy to Modern Refactoring

### Legacy Code (PHP 5.6/7.4)
**Location**: `backend/src/Legacy/old_fossil_manager.php`

**Problems**:
```
❌ mysql_* functions (DEPRECATED)
❌ String interpolation in SQL (INJECTION VULNERABLE)
❌ Global state ($conn)
❌ Weak typing (no type hints)
❌ N+1 query problems
❌ Error handling via die()
❌ Untestable procedural code
```

### Modern Code (PHP 8.3)
**Location**: `backend/src/` (Repository, Service, Entity, Controller)

**Improvements**:
```
✅ PDO with prepared statements
✅ Dependency injection (no globals)
✅ Strict typing (union types, readonly)
✅ Exception-based error handling
✅ Optimized queries (batch operations)
✅ Testable architecture (PHPUnit compatible)
✅ PHP 8.3 features (attributes, match, etc.)
```

### Refactoring Prompt
See `docs/REFACTORING_PROMPT.md` for detailed Claude Code instructions to refactor any legacy code.

---

## Security Considerations

### 1. SQL Injection Prevention
- **Method**: PDO prepared statements with parameterized queries
- **Example**:
  ```php
  $stmt = $this->pdo->prepare('SELECT * FROM fossils WHERE id = :id');
  $stmt->bindValue(':id', $id, PDO::PARAM_STR);
  $stmt->execute();
  ```

### 2. Input Validation
- Constructor parameters validated in Entity
- Service layer validates before database operations
- Type hints enforce compile-time safety

### 3. Authentication & Authorization
- (Not implemented in MVP)
- Recommend: JWT tokens + role-based access control

### 4. API Response Security
- Never expose stack traces in production
- Proper HTTP status codes
- Semantic error messages (no over-disclosure)

---

## Performance Optimizations

### 1. Pagination
- Limit default: 20 items
- Configurable page size: 1-1000

### 2. Batch Operations
- `batchUpdateStatus()` uses transactions
- All-or-nothing semantics prevent data inconsistency

### 3. Prepared Statements
- Query compilation cached by database
- Reduced query execution overhead

### 4. Caching Strategy (Future)
- Redis for fossil list caching
- Cache invalidation on updates
- Recommended: 5-minute TTL

### 5. Query Optimization
- Avoid N+1 queries
- Use `findByStatus()` instead of looping
- Index suggestions:
  - `CREATE INDEX idx_fossils_status ON fossils(status)`
  - `CREATE INDEX idx_fossils_created_at ON fossils(created_at)`

---

## Database Schema

```sql
CREATE TABLE fossils (
    id VARCHAR(50) PRIMARY KEY,
    species VARCHAR(255) NOT NULL,
    collection_name VARCHAR(255),
    estimated_age INTEGER CHECK (estimated_age >= 0),
    weight FLOAT CHECK (weight > 0),
    status VARCHAR(20) DEFAULT 'documented',
    discovery_location VARCHAR(255),
    ai_description TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE audit_logs (
    id AUTOINCREMENT PRIMARY KEY,
    action VARCHAR(100),
    entity_id VARCHAR(50),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## Deployment Architecture

### Development
- Local PHP 8.3 + SQLite
- Vite dev server for React

### Staging
- Docker containerization recommended
- PostgreSQL for data persistence
- Claude API key from environment

### Production
- Optimize frontend build (code splitting, minification)
- Database connection pooling
- Rate limiting & caching layer
- CI/CD pipeline (GitHub Actions)
- Monitoring & logging (APM tool)

---

## Monitoring & Logging

**Recommended Tools**:
- **Error Tracking**: Sentry
- **Performance**: New Relic or Datadog
- **Logging**: ELK Stack or CloudWatch
- **APM**: Tideways or Scout

**Metrics to Track**:
- API response times
- Database query performance
- Error rate and type distribution
- AI description generation latency
- Cache hit rate

---

## Future Enhancements

1. **GraphQL API** alternative to REST
2. **Real-time updates** with WebSockets
3. **Advanced filtering** (date range, weight range, etc.)
4. **Search functionality** (Elasticsearch)
5. **User authentication** (OAuth2/JWT)
6. **Role-based access control** (RBAC)
7. **Soft deletes** for data recovery
8. **Database versioning** (Doctrine Migrations)
9. **Background job queue** (AI processing)
10. **API documentation** (OpenAPI/Swagger)

---

## Key Takeaways for Portfolio

✨ **This project demonstrates your ability to**:
1. Architect layered applications (Controller → Service → Repository → Entity)
2. Refactor legacy code using modern practices
3. Write testable, production-ready PHP code
4. Leverage PHP 8.3 features appropriately
5. Build attractive React frontends with Tailwind
6. Integrate external APIs (Claude AI)
7. Implement security best practices
8. Design database schemas
9. Document technical decisions
10. Work with Claude Code for efficient development

🦖 Happy refactoring!
