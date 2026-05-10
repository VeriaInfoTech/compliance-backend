<?php

namespace Risk\Handler\Api\Adjustment;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Risk\Service\AdjustmentService;
use Risk\Service\FormService;
use Risk\Service\SpreadsheetService;

class DashboardHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var AdjustmentService */
    protected AdjustmentService $adjustmentService;

    /** @var FormService */
    protected FormService $formService;

    /* @var SpreadsheetService */
    protected SpreadsheetService $spreadsheetService;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        AdjustmentService $adjustmentService,
        FormService $formService,
        SpreadsheetService $spreadsheetService
    ) {
        $this->responseFactory    = $responseFactory;
        $this->streamFactory      = $streamFactory;
        $this->adjustmentService  = $adjustmentService;
        $this->formService        = $formService;
        $this->spreadsheetService = $spreadsheetService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get account
        $account = $request->getAttribute('account');

        // Set record params
        $params = [
            'user_id' => $account['id'],
        ];

        // Get record
        $result = $this->formService->getRecordData($params);

        // Set result
        $result = [
            'result' => true,
            'data'   => $result,
            'error'  => [],
        ];

        return new JsonResponse($result);
    }
}