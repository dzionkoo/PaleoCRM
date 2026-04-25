# PaleoCRM - Project Structure & Architecture

## 📊 Mapa Projektu

```
PaleoCRM/
├── backend/
│   ├── src/
│   │   ├── Entity/
│   │   │   ├── DinoFossil.php (Modern PHP 8.3)
│   │   │   └── AuditLog.php
│   │   ├── Controller/
│   │   │   ├── FossilController.php (REST API)
│   │   │   └── DashboardController.php
│   │   ├── Service/
│   │   │   ├── FossilService.php (Business Logic)
│   │   │   └── AIDescriptionService.php (Claude AI Integration)
│   │   ├── Repository/
│   │   │   └── FossilRepository.php (Data Access)
│   │   └── Legacy/
│   │       └── old_fossil_manager.php (PHP 5.6/7.4 Legacy Code)
│   ├── tests/
│   │   └── Unit/
│   │       └── Entity/
│   │           └── DinoFossilTest.php (PHPUnit)
│   ├── composer.json
│   ├── phpunit.xml
│   └── .env
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   │   ├── Dashboard.jsx
│   │   │   ├── FossilCard.jsx
│   │   │   └── AIDescriptionModal.jsx
│   │   ├── hooks/
│   │   │   └── useFossilData.js
│   │   ├── App.jsx
│   │   └── index.css
│   ├── package.json
│   └── tailwind.config.js
├── docs/
│   ├── REFACTORING_PROMPT.md (Claude Code Instructions)
│   ├── API_SPECIFICATION.md
│   └── ARCHITECTURE.md
├── PROJECT_STRUCTURE.md (this file)
└── README.md
```

## 🎯 Key Features

- **PHP 8.3 Modern Features**: Constructor Promotion, Readonly Properties, Union Types, Attributes
- **Legacy to Modern Refactoring**: Side-by-side comparison with migration guide
- **REST API**: JSON endpoints for React consumption
- **React Dashboard**: Real-time fossil management UI
- **AI Integration**: Claude API for intelligent descriptions
- **Unit Tests**: PHPUnit test suite demonstrating best practices
- **Dino Easter Eggs**: 🦖 Hidden throughout codebase

## 🛠 Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.3, Symfony Components, PDO |
| Frontend | React 18, Tailwind CSS, Axios |
| Testing | PHPUnit, Jest |
| API | REST/JSON |
| Database | SQLite (demo) or PostgreSQL |

