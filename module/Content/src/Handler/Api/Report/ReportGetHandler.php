<?php

namespace Content\Handler\Api\Report;

use Content\Service\ItemService;
use Content\Service\ReportService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ReportGetHandler implements RequestHandlerInterface
{
    protected ItemService $itemService;
    protected ReportService $reportService;

    public function __construct(ItemService $itemService, ReportService $reportService)
    {
        $this->itemService = $itemService;
        $this->reportService = $reportService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            // Get parameters from request body
            $params = $request->getParsedBody() ?? [];

            // Generate comprehensive report
            $report = $this->reportService->generateReport($params);

            return new JsonResponse([
                'result' => true,
                'data' => $report,
                'error' => [],
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'result' => false,
                'data' => [],
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ],
            ], 500);
        }
    }
}
