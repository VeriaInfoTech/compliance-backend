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

class AcceptHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var AdjustmentService */
    protected AdjustmentService $adjustmentService;

    /** @var FormService */
    protected FormService $formService;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        AdjustmentService $adjustmentService,
        FormService $formService
    ) {
        $this->responseFactory   = $responseFactory;
        $this->streamFactory     = $streamFactory;
        $this->adjustmentService = $adjustmentService;
        $this->formService       = $formService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $result = [
            'accept'
        ];

        return new JsonResponse($result);
    }
}