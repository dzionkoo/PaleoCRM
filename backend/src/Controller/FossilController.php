<?php

declare(strict_types=1);

namespace PaleoCRM\Controller;

use PaleoCRM\Service\FossilService;
use InvalidArgumentException;
use DomainException;

/**
 * FossilController - REST API Layer
 */
final class FossilController
{
    private const CONTENT_TYPE = 'application/json';
    private const DINO_HEADER  = 'X-Dino-Powered';
    private const DINO_VALUE   = 'Velociraptors optimize this endpoint';

    public function __construct(
        private readonly FossilService $service,
    ) {}

    /**
     * List all fossils with pagination and optional status filter.
     *
     * GET /api/fossils?page=1&pageSize=20&status=extinct
     *
     * @param array<string, string> $queryParams  Comes from $_GET in the router
     */
    public function list(array $queryParams = []): array
    {
        try {
            $page     = max(1, (int) ($queryParams['page']     ?? 1));
            $pageSize = max(1, (int) ($queryParams['pageSize'] ?? 20));
            $status   = trim((string) ($queryParams['status']  ?? ''));
            $includeAI = ($queryParams['includeAI'] ?? 'false') === 'true';

            $fossils = $this->service->getAllFossils(
                page:                $page,
                pageSize:            $pageSize,
                includeAIDescriptions: $includeAI,
                status:              $status,   // '' means all statuses
            );

            return $this->jsonResponse([
                'success'    => true,
                'data'       => array_map(fn($f) => $f->toArray(), $fossils),
                'pagination' => [
                    'page'     => $page,
                    'pageSize' => $pageSize,
                    'count'    => count($fossils),
                    'status'   => $status ?: null,
                ],
            ], 200);
        } catch (InvalidArgumentException $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * GET /api/fossils/{id}
     */
    public function show(string $id): array
    {
        try {
            $fossil = $this->service->getFossilById($id);

            if ($fossil === null) {
                return $this->jsonResponse([
                    'success' => false,
                    'error'   => "Fossil '{$id}' not found",
                ], 404);
            }

            return $this->jsonResponse(['success' => true, 'data' => $fossil->toArray()], 200);
        } catch (InvalidArgumentException $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/fossils
     */
    public function create(array $data = []): array
    {
        try {
            $fossil = $this->service->createFossilWithAIDescription($data);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Fossil created successfully',
                'data'    => $fossil->toArray(),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return $this->jsonResponse(['success' => false, 'error' => 'Validation: ' . $e->getMessage()], 400);
        } catch (DomainException $e) {
            return $this->jsonResponse(['success' => false, 'error' => 'Business rule: ' . $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * PUT /api/fossils/{id}
     */
    public function update(string $id, array $data = []): array
    {
        try {
            if (empty($data['status'])) {
                return $this->jsonResponse(['success' => false, 'error' => 'status field is required'], 400);
            }

            $this->service->updateStatus($id, $data['status']);
            $fossil = $this->service->getFossilById($id);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Fossil updated',
                'data'    => $fossil?->toArray(),
            ], 200);
        } catch (InvalidArgumentException $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (DomainException $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * DELETE /api/fossils/{id}
     */
    public function delete(string $id): array
    {
        try {
            $fossil = $this->service->getFossilById($id);

            if ($fossil === null) {
                return $this->jsonResponse(['success' => false, 'error' => "Fossil '{$id}' not found"], 404);
            }

            return $this->jsonResponse(['success' => true, 'message' => 'Fossil deleted'], 200);
        } catch (InvalidArgumentException $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * GET /api/statistics/velocity
     */
    public function velocityReport(): array
    {
        try {
            return $this->jsonResponse(['success' => true, 'data' => $this->service->getVelocityReport()], 200);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/fossils/export/csv
     */
    public function exportCSV(array $data = []): array
    {
        try {
            $status = $data['status'] ?? '';
            $csv    = $this->service->exportFossilsToCSV($status);

            return [
                'statusCode'  => 200,
                'contentType' => 'text/csv',
                'headers'     => ['Content-Disposition' => 'attachment; filename="fossils_export.csv"'],
                'body'        => $csv,
            ];
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function jsonResponse(array $data, int $status = 200): array
    {
        return [
            'statusCode'  => $status,
            'contentType' => self::CONTENT_TYPE,
            'headers'     => [self::DINO_HEADER => self::DINO_VALUE],
            'body'        => $data,
        ];
    }
}