<?php

declare(strict_types=1);

/**
 * PaleoCRM API Router
 * 
 * Entry point for all REST API requests
 * Usage: php -S localhost:8000 public/index.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PaleoCRM\Database;
use PaleoCRM\Controller\FossilController;
use PaleoCRM\Service\FossilService;
use PaleoCRM\Service\AIDescriptionService;
use PaleoCRM\Repository\FossilRepository;

// Load environment variables with proper error handling
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $env = @parse_ini_file($envFile);
    if ($env === false) {
        // Fallback: read .env file manually if parse_ini_file fails
        $env = [];
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') && !str_starts_with(trim($line), '#')) {
                [$key, $value] = explode('=', $line, 2);
                $env[trim($key)] = trim($value);
            }
        }
    }
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

// Enable error display in development
if ($_ENV['APP_DEBUG'] ?? false) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

/**
 * CORS Headers
 */
function addCorsHeaders(): void
{
    $allowedOrigins = $_ENV['CORS_ALLOWED_ORIGINS'] ?? 'http://localhost:5173';
    $origins = array_map('trim', explode(',', $allowedOrigins));
    $requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if (in_array($requestOrigin, $origins, true)) {
        header("Access-Control-Allow-Origin: {$requestOrigin}");
    }

    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
}

addCorsHeaders();

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Setup database connection
$pdo = Database::connect();

// Setup services with dependency injection
$fossilRepository = new FossilRepository($pdo);
$apiKey = $_ENV['ANTHROPIC_API_KEY'] ?? '';
$aiService = new AIDescriptionService($apiKey);
$fossilService = new FossilService($fossilRepository, $aiService);

// Create controller
$controller = new FossilController($fossilService);

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api', '', $path); // Remove /api prefix if present
$query = $_GET ?? [];

// Simple router
try {
    $response = match (true) {
        // List fossils
        $method === 'GET' && $path === '/fossils' => $controller->list($query),
        // Get single fossil
        $method === 'GET' && preg_match('#^/fossils/([^/]+)$#', $path, $matches) => $controller->show($matches[1]),
        // Create fossil
        $method === 'POST' && $path === '/fossils' => $controller->create(json_decode(file_get_contents('php://input'), true) ?? []),
        // Update fossil
        $method === 'PUT' && preg_match('#^/fossils/([^/]+)$#', $path, $matches) => $controller->update($matches[1], json_decode(file_get_contents('php://input'), true) ?? []),
        // Delete fossil
        $method === 'DELETE' && preg_match('#^/fossils/([^/]+)$#', $path, $matches) => $controller->delete($matches[1]),
        // Velocity report
        $method === 'GET' && $path === '/statistics/velocity' => $controller->velocityReport(),
        // Export CSV
        $method === 'POST' && $path === '/fossils/export/csv' => $controller->exportCSV(json_decode(file_get_contents('php://input'), true) ?? []),
        // 404
        default => [
            'statusCode' => 404,
            'contentType' => 'application/json',
            'body' => ['success' => false, 'error' => 'Endpoint not found']
        ]
    };
} catch (Exception $e) {
    $response = [
        'statusCode' => 500,
        'contentType' => 'application/json',
        'body' => [
            'success' => false,
            'error' => $_ENV['APP_DEBUG'] ? $e->getMessage() : 'Internal server error'
        ]
    ];
}

// Send response
http_response_code($response['statusCode']);
header('Content-Type: ' . ($response['contentType'] ?? 'application/json'));

// Add any custom headers
if (!empty($response['headers'])) {
    foreach ($response['headers'] as $header => $value) {
        header("{$header}: {$value}");
    }
}

// Send body
if ($response['contentType'] === 'application/json') {
    echo json_encode($response['body'], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
} else {
    echo $response['body'];
}
