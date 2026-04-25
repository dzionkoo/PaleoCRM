# 🦖 PaleoCRM - Portfolio Project

> **Legacy PHP to Modern Symfony: A Comprehensive Refactoring Showcase**
>
> Demonstrating skills through a CRM system for managing archaeological/paleontological resources with emphasis on legacy code modernization using Claude Code.

## 🎯 Project Overview

**PaleoCRM** is a deliberate portfolio project showcasing the ability to:
- Refactor legacy PHP 5.6/7.4 code to PHP 8.3 with modern patterns
- Design layered architectures (MVC/Repository Pattern)
- Build production-ready backends with security best practices
- Create responsive React frontends with Tailwind CSS
- Integrate AI services (Claude API)
- Write testable code with 80%+ PHPUnit coverage
- Use Claude Code effectively for code generation and refactoring

### 🦕 Why "PaleoCRM"?

This project uses paleontology/fossil management as its domain because:
1. **Thematic Consistency**: Represents the "evolution" from legacy to modern code
2. **Engagement**: Easter eggs and dinosaur references make it memorable
3. **Complex Domain**: Rich business logic for status transitions, categorization
4. **Portfolio Appeal**: Unique angle that stands out from typical project management CRMs

---

## 🏗️ Technical Stack

```
BACKEND                  |  FRONTEND                | TOOLS
─────────────────────────┼──────────────────────────┼──────────
✨ PHP 8.3              |  React 18                | Docker
✨ Symfony Components   |  Tailwind CSS v3          | PHPUnit
✨ PDO/SQLite-Postgres  |  Vite                    | PHPStan
✨ Composer             |  Axios                   | Claude Code
```

### Backend Highlights
- **PHP 8.3 Features**: Constructor promotion, readonly properties, union types, attributes, match expressions
- **Modern Patterns**: Repository, Service, Controller layers; Dependency Injection
- **Security**: Prepared statements, input validation, exception handling
- **Testing**: PHPUnit with mocks, >80% coverage, behavior-driven tests
- **AI Integration**: Claude API for intelligent fossil descriptions

### Frontend Highlights
- **Component-Based**: Dashboard, FossilCard, AIDescriptionModal
- **State Management**: React Hooks (useState, useEffect, custom useFossilData)
- **Styling**: Tailwind CSS with custom animations
- **UX**: Responsive grid layout, pagination, loading states

---

## 📂 Project Structure

See [PROJECT_STRUCTURE.md](PROJECT_STRUCTURE.md) for complete structure overview.

**Key files to examine**:
- **Modern PHP 8.3 Entity**: [`backend/src/Entity/DinoFossil.php`](backend/src/Entity/DinoFossil.php) - Constructor promotion, readonly properties, union types, attributes
- **Business Logic**: [`backend/src/Service/FossilService.php`](backend/src/Service/FossilService.php) - Validation, workflows, AI integration
- **Data Access**: [`backend/src/Repository/FossilRepository.php`](backend/src/Repository/FossilRepository.php) - PDO prepared statements, transaction safety
- **REST API**: [`backend/src/Controller/FossilController.php`](backend/src/Controller/FossilController.php) - All endpoints with proper error handling
- **Legacy Example**: [`backend/src/Legacy/old_fossil_manager.php`](backend/src/Legacy/old_fossil_manager.php) - What we're modernizing from
- **Tests**: [`backend/tests/Unit/Service/FossilServiceTest.php`](backend/tests/Unit/Service/FossilServiceTest.php) - >80% coverage with mocks
- **Frontend**: [`frontend/src/components/Dashboard.jsx`](frontend/src/components/Dashboard.jsx) - React UI with Tailwind
- **Claude Integration Guide**: [`docs/REFACTORING_PROMPT.md`](docs/REFACTORING_PROMPT.md) - How to use Claude Code

---

## 🚀 Quick Start

### Backend Setup
```bash
cd backend

# Install dependencies
composer install

# Run tests
vendor/bin/phpunit

# Static analysis
vendor/bin/phpstan analyse src tests
```

### Frontend Setup
```bash
cd frontend

# Install dependencies
npm install

# Start dev server
npm run dev

# Build production
npm run build
```

---

## 📖 Documentation

1. **[PROJECT_STRUCTURE.md](PROJECT_STRUCTURE.md)** - Architecture overview
2. **[docs/REFACTORING_PROMPT.md](docs/REFACTORING_PROMPT.md)** - Claude Code integration guide
3. **[docs/API_SPECIFICATION.md](docs/API_SPECIFICATION.md)** - REST endpoints & usage
4. **[docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)** - System design & patterns

---

## ✨ Key Features

### PHP 8.3 Showcase
- Constructor property promotion (70% less boilerplate)
- Readonly properties (immutability guarantee)
- Union types (type safety for multiple returns)
- Named arguments (self-documenting code)
- Attributes (runtime metadata)
- Match expressions (cleaner than switch)

### Modern Architecture
- **Repository Pattern**: Data access abstraction, SQL injection safe
- **Service Layer**: Business logic isolation, transaction management
- **Dependency Injection**: No global state, fully testable
- **Value Objects**: Domain models with validation

### Security
✅ PDO prepared statements  
✅ Input validation with type hints  
✅ Exception-based error handling  
✅ Atomic transactions  
✅ Readonly properties (immutability)  

### Testing
- PHPUnit 10+ with modern attributes
- Mocking all external dependencies
- Data providers for parametrized tests
- >80% code coverage
- Behavior-driven names

### AI Integration
- Claude API for intelligent descriptions
- Prompt engineering for paleontology domain
- Async/graceful degradation

---

## 🎓 What This Demonstrates

| Skill | Example |
|-------|---------|
| **PHP 8.3** | Constructor promotion, readonly, union types, attributes, match |
| **Architecture** | Layered design (Controller→Service→Repository→Entity) |
| **Security** | Prepared statements, validation, exception handling |
| **Testing** | PHPUnit, mocking, data providers, 80%+ coverage |
| **Database** | Schema design, query optimization, transactions |
| **React/Frontend** | Hooks, composition, state mgmt, Tailwind CSS |
| **AI Integration** | Claude API, prompt engineering, async patterns |
| **Refactoring** | Legacy analysis, modernization, backward compat |
| **Documentation** | Technical specs, architecture, inline comments |
| **Code Quality** | PSR-12, PHPStan, SOLID principles |

---

## 🦖 Dino Easter Eggs

Throughout the codebase you'll find references to dinosaurs:
- Status: `extinct` 🦕
- Velocity: `raptor-speed` 🦖
- Header: `X-Dino-Powered: Velociraptors optimize this endpoint`
- Comments: "More extinct than Tuesday mornings"
- Variable names: `$velocity`, `$era`, `$fossilAge`

---

## 📊 Code Metrics

- **Backend**: ~1,500 LOC (PHP 8.3)
- **Frontend**: ~800 LOC (React JSX)
- **Test Coverage**: 80%+ (PHPUnit)
- **API Endpoints**: 8 documented
- **Database Queries**: 100% prepared statements
- **Security Issues**: 0 (no SQL injection vectors)

---

## 💡 Interview Highlights

**Q: Walk through the architecture**
- Layered design prevents coupling, enables testing
- Controller handles HTTP, delegates to Service
- Service orchestrates business logic, validates
- Repository abstracts database (PDO with prepared statements)
- Entity contains domain logic + immutability

**Q: Why PHP 8.3?**
- Modern language features reduce boilerplate
- Better type safety (union types, readonly)
- Demonstrates staying current

**Q: How did you refactor legacy code?**
- Identified security issues (SQL injection, globals)
- Used Claude Code to generate modern equivalents
- Maintained functionality with comprehensive tests
- Incremental approach for safety

**Q: Testing strategy?**
- >80% PHPUnit coverage targeting
- Mock external dependencies
- Data providers for scenario testing
- Behavior-driven naming

**Q: AI Integration?**
- Frontend form → Backend receives fossil data
- Backend calls Claude API with structured prompt
- Description generated based on paleontological domain
- Gracefully handles API failures

---

## 🔗 Related Documentation

See full documentation in `/docs` directory:
- `REFACTORING_PROMPT.md` - Complete Claude Code instructions
- `API_SPECIFICATION.md` - All endpoints with examples
- `ARCHITECTURE.md` - Deep dive into system design

---

**Made with 🦖 by Fullstack Developer**

*Showcasing: PHP 8.3 • React 18 • Modern Architecture • Testing Excellence • Claude Code Integration*
