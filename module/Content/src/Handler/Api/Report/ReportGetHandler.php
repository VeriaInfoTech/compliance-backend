<?php

namespace Content\Handler\Api\Report;

use Content\Service\ReportGeneratorService;
use InvalidArgumentException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ReportGetHandler implements RequestHandlerInterface
{
    private ReportGeneratorService $reportGeneratorService;

    public function __construct(ReportGeneratorService $reportGeneratorService)
    {
        $this->reportGeneratorService = $reportGeneratorService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            // Get raw JSON data from request body
            $rawJson = $request->getParsedBody() ?? [];

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

