# 🦖 PaleoCRM API Specification

## Base URL
```
http://localhost:8000/api
```

## Authentication
(Not implemented in demo, but would use JWT/OAuth2)

---

## Endpoints

### 1. List Fossils

**GET** `/fossils`

Retrieve paginated list of all fossils with optional AI descriptions.

**Query Parameters:**
```
page=1          (integer, default: 1) - Page number
pageSize=20     (integer, default: 20) - Results per page
includeAI=false (boolean, default: false) - Include AI descriptions
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": "FOSSIL_20260425001",
      "species": "Tyrannosaurus rex",
      "displayName": "T-rex (66 million years) - Era: Cretaceous 🦖",
      "collectionName": "Hell Creek Formation Collection",
      "estimatedAge": 66,
      "weight": 9000,
      "weightCategory": "colossal",
      "status": "documented",
      "discoveryLocation": "Hell Creek Formation",
      "theoreticalVelocity": 18,
      "aiDescription": "Apex predator of the Late Cretaceous...",
      "era": "Cretaceous",
      "createdAt": "2026-04-25T10:00:00+00:00",
      "metadata": {}
    }
  ],
  "pagination": {
    "page": 1,
    "pageSize": 20,
    "total": 120
  }
}
```

**Error (400 Bad Request):**
```json
{
  "success": false,
  "error": "Page and pageSize must be positive integers"
}
```

---

### 2. Get Single Fossil

**GET** `/fossils/{id}`

Retrieve detailed information for a specific fossil.

**Path Parameters:**
```
id (string) - Fossil identifier (e.g., FOSSIL_20260425001)
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": "FOSSIL_20260425001",
    "species": "Tyrannosaurus rex",
    ...
  }
}
```

**Error (404 Not Found):**
```json
{
  "success": false,
  "error": "Fossil with ID 'FOSSIL_99999999' not found"
}
```

---

### 3. Create Fossil

**POST** `/fossils`

Create a new fossil in the database.

**Request Body:**
```json
{
  "species": "Velociraptor",
  "collectionName": "Deinonychus Study Collection",
  "estimatedAge": 75,
  "weight": 15.5,
  "discoveryLocation": "Paleocene Valley",
  "status": "documented",
  "generateAIDescription": true
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Fossil created successfully",
  "data": {
    "id": "FOSSIL_20260425_a1b2c3d4",
    "species": "Velociraptor",
    ...
  }
}
```

**Validation Errors (400 Bad Request):**
```json
{
  "success": false,
  "error": "Validation error: Species name is required"
}
```

**Business Rule Violations (422 Unprocessable Entity):**
```json
{
  "success": false,
  "error": "Business rule violation: Invalid fossil status"
}
```

---

### 4. Update Fossil Status

**PUT** `/fossils/{id}`

Update the status of an existing fossil.

**Path Parameters:**
```
id (string) - Fossil identifier
```

**Request Body:**
```json
{
  "status": "extinct"
}
```

**Valid Status Transitions:**
```
documented      → pending-analysis, extinct, active
pending-analysis → documented, extinct, active
active          → documented, extinct
extinct         → documented
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Fossil updated successfully",
  "data": {
    "id": "FOSSIL_20260425001",
    "status": "extinct",
    ...
  }
}
```

**Error (422 Unprocessable Entity):**
```json
{
  "success": false,
  "error": "Invalid transition from 'extinct' to 'pending-analysis'. Allowed: documented"
}
```

---

### 5. Delete Fossil

**DELETE** `/fossils/{id}`

Remove a fossil from the database.

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Fossil deleted successfully"
}
```

---

### 6. Velocity Report

**GET** `/statistics/velocity`

Get statistical report of all fossils grouped by velocity category.

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "total_fossils": 127,
    "by_velocity": {
      "raptor-speed": 3,
      "fast": 15,
      "medium": 28,
      "slow": 45,
      "immobile": 36
    },
    "generated_at": "2026-04-25T15:30:00+00:00",
    "easter_egg": "🦕 If they could travel back in time, they would"
  }
}
```

---

### 7. Export to CSV

**POST** `/fossils/export/csv`

Export fossils to CSV format. Can filter by status.

**Request Body (Optional):**
```json
{
  "status": "extinct"
}
```

**Response (200 OK):**
- Content-Type: `text/csv`
- Content-Disposition: `attachment; filename="fossils_export.csv"`

**CSV Format:**
```csv
ID,Species,Collection Name,Estimated Age (years),Weight (kg),Status,Discovery Location,ERA
FOSSIL_20260425001,Tyrannosaurus rex,Hell Creek Collection,66,9000,documented,Hell Creek Formation,Cretaceous
FOSSIL_20260425002,Velociraptor,Dino Study,75,15,extinct,Paleocene Valley,Cretaceous
```

---

## Common Response Headers

All responses include:
```
X-Dino-Powered: Velociraptors optimize this endpoint 🦖
Content-Type: application/json (or text/csv for exports)
```

---

## Error Responses

### 400 Bad Request
Invalid query parameters or request body format

### 404 Not Found
Fossil ID doesn't exist

### 422 Unprocessable Entity
Request is well-formed but violates business rules

### 500 Internal Server Error
Server-side error

---

## Rate Limiting
(Not implemented in demo)

---

## Pagination Best Practices

- Default page size: 20 items
- Maximum page size: 1000 items
- Use cursor-based pagination for large datasets (recommended future improvement)

---

## Integration with React Frontend

Here's how to consume these endpoints in React:

```javascript
// Fetch fossils with AI descriptions
const response = await fetch('/api/fossils?page=1&pageSize=20&includeAI=true');
const data = await response.json();

// Create new fossil
const createResponse = await fetch('/api/fossils', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    species: 'New Species',
    estimatedAge: 65,
    weight: 5000,
    collectionName: 'My Collection',
    generateAIDescription: true
  })
});

// Update fossil status
const updateResponse = await fetch('/api/fossils/FOSSIL_123', {
  method: 'PUT',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ status: 'extinct' })
});
```

---

## Claude AI Integration

The `/fossils` POST endpoint with `generateAIDescription: true` will:

1. Create fossil record
2. Send fossil data to Claude API
3. Generate scientifically accurate description
4. Store description in database
5. Return complete fossil object with description

Example prompt sent to Claude:
```
Generate a detailed paleontological description for this fossil:

Species: Tyrannosaurus rex
Estimated Age: 66 million years ago (Cretaceous)
Weight: 9000 kg
Discovery Location: Hell Creek Formation
Collection: Hell Creek Formation Collection
Theoretical Velocity: 18 m/s

Include:
1. Physical characteristics and adaptations
2. Ecological role and habitat
3. Evolutionary significance
4. Preservation quality assessment

Write in an engaging, scientifically accurate tone suitable for museum display.
```

---

## Development Notes

- All endpoints validate input using PHP 8.3 union types and strict typing
- Database queries use prepared statements (PDO) - SQL injection safe
- Transaction support for batch operations
- Proper HTTP status codes and semantic error messages
- Tested with >80% PHPUnit coverage
