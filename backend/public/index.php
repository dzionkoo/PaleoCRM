<?php

declare(strict_types=1);

/**
 * PaleoCRM — PHP Router / Entry Point
 *
 * Routes:
 *   GET    /api/fossils                  → FossilController::list
 *   GET    /api/fossils/{id}             → FossilController::show
 *   POST   /api/fossils                  → FossilController::create
 *   PUT    /api/fossils/{id}             → FossilController::update
 *   DELETE /api/fossils/{id}             → FossilController::delete
 *   GET    /api/statistics/velocity      → FossilController::velocityReport
 *   POST   /api/fossils/export/csv       → FossilController::exportCSV
 *   POST   /api/fossils/{id}/ai-describe → generate & return AI description
 */

require_once __DIR__ . '/../vendor/autoload.php';

// ── 1. Load .env ──────────────────────────────────────────────────────────────
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }
}

// ── 2. CORS headers (must be sent before any output) ─────────────────────────
$allowedOrigins = explode(',', $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:5173,http://localhost:3000');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true) || ($_ENV['APP_ENV'] ?? 'production') === 'development') {
    header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
} else {
    header('Access-Control-Allow-Origin: ' . ($allowedOrigins[0] ?? '*'));
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');
header('X-Dino-Powered: Velociraptors optimize this endpoint 🦖');

// Handle pre-flight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Helper functions ──────────────────────────────────────────────────────────

function respond(array $result): void
{
    sendJson($result['body'], $result['statusCode']);
}

function sendJson(array $data, int $status = 200): void
{
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

// ── 3. Bootstrap services ─────────────────────────────────────────────────────
use PaleoCRM\Database;
use PaleoCRM\Repository\FossilRepository;
use PaleoCRM\Service\AIDescriptionService;
use PaleoCRM\Service\FossilService;
use PaleoCRM\Controller\FossilController;

try {
    $pdo        = Database::connect();
    $repository = new FossilRepository($pdo);
    $aiService  = new AIDescriptionService(
        apiKey: $_ENV['ANTHROPIC_API_KEY'] ?? throw new \RuntimeException('ANTHROPIC_API_KEY not set'),
        model:  $_ENV['CLAUDE_MODEL'] ?? 'claude-sonnet-4-20250514',
    );
    $service    = new FossilService($repository, $aiService);
    $controller = new FossilController($service);
} catch (\RuntimeException $e) {
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
    exit;
}

// ── 4. Route matching ─────────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/');
$body   = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];

// /api/statistics/velocity
if ($method === 'GET' && $uri === '/api/statistics/velocity') {
    respond($controller->velocityReport());
    exit;
}

// /api/fossils/export/csv
if ($method === 'POST' && $uri === '/api/fossils/export/csv') {
    $result = $controller->exportCSV($body);
    if ($result['contentType'] === 'text/csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="fossils_export.csv"');
        http_response_code(200);
        echo $result['body'];
        exit;
    }
    respond($result);
    exit;
}

// /api/fossils/{id}/ai-describe
if ($method === 'POST' && preg_match('#^/api/fossils/([^/]+)/ai-describe$#', $uri, $m)) {
    $fossil = $service->getFossilById($m[1]);
    if ($fossil === null) {
        sendJson(['success' => false, 'error' => 'Fossil not found'], 404);
        exit;
    }

    // Stream SSE response so frontend can show tokens as they arrive
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no'); // nginx: disable proxy buffering
    ob_implicit_flush(true);

    $fullText = '';
    try {
        $aiService->generateDescriptionStream($fossil, function (string $chunk) use (&$fullText) {
            // Each chunk may contain multiple SSE lines from Anthropic
            foreach (explode("\n", $chunk) as $line) {
                if (!str_starts_with($line, 'data: ')) {
                    continue;
                }
                $json = json_decode(substr($line, 6), true);
                if (($json['type'] ?? '') === 'content_block_delta') {
                    $text = $json['delta']['text'] ?? '';
                    $fullText .= $text;
                    // Forward to browser
                    echo 'data: ' . json_encode(['text' => $text]) . "\n\n";
                }
            }
        });
        // Persist finished description
        $fossil->setAIDescription($fullText);
        $repository->update($fossil);
        echo 'data: ' . json_encode(['done' => true, 'fossilId' => $fossil->id]) . "\n\n";
    } catch (\Exception $e) {
        echo 'data: ' . json_encode(['error' => $e->getMessage()]) . "\n\n";
    }
    exit;
}

// /api/fossils/{id}
if (preg_match('#^/api/fossils/([^/]+)$#', $uri, $m)) {
    $id = $m[1];
    if ($method === 'GET') {
        respond($controller->show($id));
    } elseif ($method === 'PUT') {
        respond($controller->update($id, $body));
    } elseif ($method === 'DELETE') {
        respond($controller->delete($id));
    } else {
        sendJson(['success' => false, 'error' => 'Method not allowed'], 405);
    }
    exit;
}

// /api/fossils
if ($uri === '/api/fossils') {
    if ($method === 'GET') {
        respond($controller->list($_GET));
    } elseif ($method === 'POST') {
        respond($controller->create($body));
    } else {
        sendJson(['success' => false, 'error' => 'Method not allowed'], 405);
    }
    exit;
}

// 404 fallback
sendJson(['success' => false, 'error' => "Route not found: {$method} {$uri}"], 404);