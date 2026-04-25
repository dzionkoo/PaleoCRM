# Running PaleoCRM

This guide explains how to run the PaleoCRM project locally.

## Prerequisites

- **PHP 8.3+** - Download from [php.net](https://www.windows.php.net/download/) or install via package manager
- **Node.js 18+** - Download from [nodejs.org](https://nodejs.org/)
- **Composer** - Download from [getcomposer.org](https://getcomposer.org/)
- **Claude API Key** - Get from [Anthropic Console](https://console.anthropic.com)

## Environment Setup

### Windows PATH Configuration

Ensure PHP and Composer are in your system PATH:

```powershell
# Check if php is available
php --version

# Check if composer is available  
composer --version

# Check if node is available
node --version
```

If any command is not found, add the installation directories to your PATH environment variable.

### Environment Variables

1. Copy `.env.example` to `.env`:
```bash
copy backend\.env.example backend\.env
```

2. Edit `backend/.env` and add your Claude API key:
```
DATABASE_URL=sqlite:fossil.db
ANTHROPIC_API_KEY=sk-ant-... # Your actual API key from Anthropic
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://localhost:3000
APP_ENV=development
APP_DEBUG=true
```

## Installation

### Backend Setup

```bash
cd backend

# Install PHP dependencies
composer install

# Initialize the database (creates SQLite file)
php migrations/001_create_fossils_table.php
```

### Frontend Setup

```bash
cd frontend

# Install Node dependencies
npm install
```

## Running the Application

### Start Backend Server (PHP Built-in Server)

```bash
cd backend

# Start on port 8000
php -S localhost:8000 public/index.php
```

The API will be available at `http://localhost:8000/api/`

**Available Endpoints:**
- `GET /api/fossils?page=1&pageSize=20` - List fossils
- `GET /api/fossils/{id}` - Get single fossil
- `POST /api/fossils` - Create fossil
- `PUT /api/fossils/{id}` - Update fossil status
- `DELETE /api/fossils/{id}` - Delete fossil
- `GET /api/statistics/velocity` - Velocity statistics
- `POST /api/fossils/export/csv` - Export to CSV

### Start Frontend Development Server (in another terminal)

```bash
cd frontend

# Start Vite dev server on port 5173
npm run dev
```

The UI will be available at `http://localhost:5173`

## Testing

### Backend Tests

```bash
cd backend

# Run PHPUnit tests
vendor/bin/phpunit tests/

# Run with coverage
vendor/bin/phpunit tests/ --coverage-html coverage/
```

### Manual API Testing

Use curl or Postman to test endpoints:

```bash
# Get all fossils
curl http://localhost:8000/api/fossils

# Create a fossil
curl -X POST http://localhost:8000/api/fossils \
  -H "Content-Type: application/json" \
  -d '{
    "species": "Tyrannosaurus rex",
    "collectionName": "T-rex Collection",
    "estimatedAge": 66,
    "weight": 9000,
    "discoveryLocation": "Hell Creek Formation",
    "status": "discovered"
  }'
```

## Database

### SQLite Files

- **Development:** `backend/fossil.db` (created automatically)
- **Schema:** Defined in `backend/migrations/001_create_fossils_table.php`

### Running Migrations

```bash
cd backend
php migrations/001_create_fossils_table.php
```

### Resetting Database

```bash
# Delete the database file
rm backend/fossil.db

# Re-run migrations
php backend/migrations/001_create_fossils_table.php
```

## Troubleshooting

### "Command not found: php"
- Verify PHP is installed: `php --version`
- Add PHP installation directory to Windows PATH
- Restart terminal after updating PATH

### "Could not open input file: migrations/001_create_fossils_table.php"
- Ensure you're in the `backend/` directory
- Use full path: `php backend/migrations/001_create_fossils_table.php`

### CORS Errors in Browser
- Verify backend server is running on port 8000
- Check `CORS_ALLOWED_ORIGINS` in `.env` includes `http://localhost:5173`
- Restart backend server after changing `.env`

### "ANTHROPIC_API_KEY is required"
- Ensure `.env` file exists in `backend/` directory
- API key must be set and non-empty
- Restart PHP server after updating `.env`

### Frontend shows "Failed to fetch"
- Backend server must be running: `php -S localhost:8000 public/index.php`
- Check browser console for exact error
- Verify CORS headers are being sent (use Firefox Developer Tools)

## Performance Tips

- Use SQLite for development, MySQL for production
- Enable query caching in Service layer for high-traffic endpoints
- Consider Redis for session management with multiple server instances
- Profile with `xdebug` for PHP performance analysis

## Next Steps

1. ✅ Backend API running on port 8000
2. ✅ Frontend UI running on port 5173
3. 🔄 Frontend components connecting to real API (update `useFossilData.js`)
4. 🔄 Implement real Claude streaming in `AIDescriptionModal.jsx`
5. 🔄 Add error boundary component
6. 🔄 Add status filtering to Dashboard

See `ARCHITECTURE.md` for system design and `API_SPECIFICATION.md` for endpoint details.
