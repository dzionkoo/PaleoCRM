<?php
// NO declare(strict_types) or blank lines before this - keep <?php on line 1
// ob_start() FIRST - captures any accidental output (notices, warnings, BOM)
// so header() calls never fail with "headers already sent"
ob_start();

// ── CORS headers ──────────────────────────────────────────────────────────────
// These run before any require/autoload, so they survive bootstrap errors.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(204);
    exit;
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function sendJson(array $data, int $status = 200): void
{
    ob_end_clean(); // discard any buffered output/errors
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function respond(array $result): void
{
    sendJson($result['body'], $result['statusCode']);
}

// ── Load .env ─────────────────────────────────────────────────────────────────
foreach ([__DIR__ . '/.env', dirname(__DIR__) . '/.env'] as $_envFile) {
    if (!file_exists($_envFile)) continue;
    foreach (file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
        $_line = trim($_line);
        if ($_line === '' || $_line[0] === '#' || !str_contains($_line, '=')) continue;
        [$_k, $_v] = explode('=', $_line, 2);
        $_ENV[trim($_k)] = trim($_v, " \t\n\r\0\x0B\"'");
    }
    break;
}

// ── Autoload ──────────────────────────────────────────────────────────────────
$_autoload = null;
foreach ([__DIR__ . '/vendor/autoload.php', dirname(__DIR__) . '/vendor/autoload.php'] as $_path) {
    if (file_exists($_path)) { $_autoload = $_path; break; }
}
if ($_autoload === null) {
    sendJson(['success' => false, 'error' => 'Run composer install first'], 500);
}
require_once $_autoload;

// ── Bootstrap ─────────────────────────────────────────────────────────────────
use PaleoCRM\Database;
use PaleoCRM\Repository\FossilRepository;
use PaleoCRM\Service\AIDescriptionService;
use PaleoCRM\Service\FossilService;
use PaleoCRM\Controller\FossilController;

try {
    $pdo        = Database::connect();
    $repository = new FossilRepository($pdo);
    $aiService  = new AIDescriptionService(
        apiKey: $_ENV['ANTHROPIC_API_KEY'] ?? '',
        model:  $_ENV['CLAUDE_MODEL'] ?? 'claude-sonnet-4-20250514',
    );
    $service    = new FossilService($repository, $aiService);
    $controller = new FossilController($service);
} catch (\Throwable $e) {
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}

// ── Routing ───────────────────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri    = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$body   = (array)(json_decode(file_get_contents('php://input') ?: '{}', true) ?? []);

if ($method === 'GET' && $uri === '/api/statistics/velocity') {
    respond($controller->velocityReport());
}

if ($method === 'POST' && $uri === '/api/fossils/export/csv') {
    $result = $controller->exportCSV($body);
    if (($result['contentType'] ?? '') === 'text/csv') {
        ob_end_clean();
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="fossils_export.csv"');
        http_response_code(200);
        echo $result['body'];
        exit;
    }
    respond($result);
}

if ($method === 'POST' && preg_match('#^/api/fossils/([^/]+)/ai-describe$#', $uri, $m)) {
    $fossil = $service->getFossilById($m[1]);
    if ($fossil === null) sendJson(['success' => false, 'error' => 'Fossil not found'], 404);

    ob_end_clean();
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: text/event-stream; charset=utf-8');
    header('Cache-Control: no-cache, no-store');
    header('X-Accel-Buffering: no');
    ob_implicit_flush(true);

    $fullText = '';
    try {
        $aiService->generateDescriptionStream($fossil, function (string $chunk) use (&$fullText): void {
            foreach (explode("\n", $chunk) as $line) {
                if (!str_starts_with($line, 'data: ')) continue;
                $json = json_decode(substr($line, 6), true);
                if (is_array($json) && ($json['type'] ?? '') === 'content_block_delta') {
                    $text = $json['delta']['text'] ?? '';
                    $fullText .= $text;
                    echo 'data: ' . json_encode(['text' => $text]) . "\n\n";
                    flush();
                }
            }
        });
        if ($fullText !== '') { $fossil->setAIDescription($fullText); $repository->update($fossil); }
        echo 'data: ' . json_encode(['done' => true, 'fossilId' => $fossil->id]) . "\n\n";
        flush();
    } catch (\Throwable $e) {
        echo 'data: ' . json_encode(['error' => $e->getMessage()]) . "\n\n";
        flush();
    }
    exit;
}

if (preg_match('#^/api/fossils/([^/]+)$#', $uri, $m)) {
    $id = $m[1];
    match ($method) {
        'GET'    => respond($controller->show($id)),
        'PUT'    => respond($controller->update($id, $body)),
        'DELETE' => respond($controller->delete($id)),
        default  => sendJson(['success' => false, 'error' => 'Method not allowed'], 405),
    };
}

if ($uri === '/api/fossils') {
    match ($method) {
        'GET'   => respond($controller->list($_GET)),
        'POST'  => respond($controller->create($body)),
        default => sendJson(['success' => false, 'error' => 'Method not allowed'], 405),
    };
}

sendJson(['success' => false, 'error' => "No route: $method $uri"], 404);