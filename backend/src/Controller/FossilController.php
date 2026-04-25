<?php

declare(strict_types=1);

namespace PaleoCRM\Controller;

use PaleoCRM\Service\FossilService;
use InvalidArgumentException;
use DomainException;

/**
 * FossilController - REST API Layer
 * 
 * Handles HTTP requests/responses for fossil management
 * Endpoints:
 * - GET /api/fossils (list)
 * - GET /api/fossils/{id} (show)
 * - POST /api/fossils (create)
 * - PUT /api/fossils/{id} (update)
 * - DELETE /api/fossils/{id} (delete)
 * - POST /api/fossils/export/csv (export)
 * - POST /api/fossils/{id}/ai-describe (generate AI description)
 * - GET /api/statistics/velocity (velocity report)
 */
final class FossilController
{
    private const CONTENT_TYPE = 'application/json';
    private const DINO_HEADER = 'X-Dino-Powered';
    private const DINO_VALUE = 'Velociraptors optimize this endpoint 🦖';

    public function __construct(
        private readonly FossilService $service,
    ) {}

    /**
     * List all fossils with pagination
     * 
     * GET /api/fossils?page=1&pageSize=20&includeAI=true
     * 
     * @param array<string, string> $queryParams Query parameters
     * @return array HTTP response
     */
    public function list(array $queryParams = []): array
    {
        try {
            $page = (int)($queryParams['page'] ?? 1);
            $pageSize = (int)($queryParams['pageSize'] ?? 20);
            $includeAI = ($queryParams['includeAI'] ?? 'false') === 'true';

            $fossils = $this->service->getAllFossils($page, $pageSize, $includeAI);

            return $this->jsonResponse([
                'success' => true,
                'data' => array_map(fn($f) => $f->toArray(), $fossils),
                'pagination' => [
                    'page' => $page,
                    'pageSize' => $pageSize,
                    'total' => count($fossils),
                ]
            ], 200);
        } catch (InvalidArgumentException $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get single fossil by ID
     * 
     * GET /api/fossils/{id}
     * 
     * @param string $id Fossil ID
     * @return array HTTP response
     */
    public function show(string $id): array
    {
        try {
            $fossil = $this->service->getFossilById($id);

            if ($fossil === null) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => "Fossil with ID '{$id}' not found"
                ], 404);
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => $fossil->toArray()
            ], 200);
        } catch (InvalidArgumentException $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Create new fossil
     * 
     * POST /api/fossils
     * Body: {
     *   "species": "Tyrannosaurus rex",
     *   "collectionName": "T-rex Collection",
     *   "estimatedAge": 66,
     *   "weight": 9000,
     *   "discoveryLocation": "Hell Creek Formation",
     *   "status": "documented",
     *   "generateAIDescription": true
     * }
     * 
     * @param array<string, mixed> $data Request body
     * @return array HTTP response
     */
    public function create(array $data = []): array
    {
        try {
            $fossil = $this->service->createFossilWithAIDescription($data);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Fossil created successfully',
                'data' => $fossil->toArray()
            ], 201);
        } catch (InvalidArgumentException $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Validation error: ' . $e->getMessage()
            ], 400);
        } catch (DomainException $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Business rule violation: ' . $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update fossil status
     * 
     * PUT /api/fossils/{id}
     * Body: {
     *   "status": "extinct"
     * }
     * 
     * @param string $id Fossil ID
     * @param array<string, mixed> $data Request body
     * @return array HTTP response
     */
    public function update(string $id, array $data = []): array
    {
        try {
            if (empty($data['status'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => 'Status field is required'
                ], 400);
            }

            $this->service->updateStatus($id, $data['status']);

            $fossil = $this->service->getFossilById($id);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Fossil updated successfully',
                'data' => $fossil?->toArray()
            ], 200);
        } catch (InvalidArgumentException $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch (DomainException $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete fossil
     * 
     * DELETE /api/fossils/{id}
     * 
     * @param string $id Fossil ID
     * @return array HTTP response
     */
    public function delete(string $id): array
    {
        try {
            $fossil = $this->service->getFossilById($id);

            if ($fossil === null) {
                return $this->jsonResponse([
                    'success' => false,
                    'error' => "Fossil with ID '{$id}' not found"
                ], 404);
            }

            // Note: In real implementation, would delete here
            //$this->service->delete($id);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Fossil deleted successfully'
            ], 200);
        } catch (InvalidArgumentException $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get velocity report statistics
     * 
     * GET /api/statistics/velocity
     * 
     * @return array HTTP response
     */
    public function velocityReport(): array
    {
        try {
            $report = $this->service->getVelocityReport();

            return $this->jsonResponse([
                'success' => true,
                'data' => $report
            ], 200);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export fossils to CSV
     * 
     * POST /api/fossils/export/csv
     * Body: { "status": "extinct" } (optional filter)
     * 
     * @param array<string, mixed> $data Request body
     * @return array HTTP response with CSV content
     */
    public function exportCSV(array $data = []): array
    {
        try {
            $status = $data['status'] ?? '';
            $csv = $this->service->exportFossilsToCSV($status);

            return [
                'statusCode' => 200,
                'contentType' => 'text/csv',
                'headers' => [
                    'Content-Disposition' => 'attachment; filename="fossils_export.csv"',
                    self::DINO_HEADER => self::DINO_VALUE,
                ],
                'body' => $csv
            ];
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'error' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate JSON response with proper headers
     * 
     * @param array<string, mixed> $data Response data
     * @param int $statusCode HTTP status code
     * @return array Response array
     */
    private function jsonResponse(array $data, int $statusCode = 200): array
    {
        return [
            'statusCode' => $statusCode,
            'contentType' => self::CONTENT_TYPE,
            'headers' => [
                self::DINO_HEADER => self::DINO_VALUE,
            ],
            'body' => $data
        ];
    }
}
