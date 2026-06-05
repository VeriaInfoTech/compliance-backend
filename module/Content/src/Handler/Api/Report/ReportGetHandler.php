<?php

namespace Content\Handler\Api\Report;

use Content\Service\ItemService;
use Content\Service\ReportGeneratorService;
use InvalidArgumentException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ReportGetHandler implements RequestHandlerInterface
{
    private ItemService $itemService;
    private ReportGeneratorService $reportGeneratorService;

    public function __construct(ItemService $itemService, ReportGeneratorService $reportGeneratorService)
    {
        $this->itemService = $itemService;
        $this->reportGeneratorService = $reportGeneratorService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            // Get raw JSON data from database - domains and controls
            $rawJson = $this->itemService->getItemList([
                'type' => ['domain', 'control'], // Get all
            ]);

            // Generate comprehensive report from raw data
            $report = $this->reportGeneratorService->generate($rawJson);

            return new JsonResponse([
                'result' => true,
                'data' => $report,
                'error' => [],
            ]);
        } catch (InvalidArgumentException $e) {
            return new JsonResponse([
                'result' => false,
                'data' => [],
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ], 422);
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
