<?php

namespace Erm\Handler\Api\Member;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MemberLightListHandler implements RequestHandlerInterface
{
    protected ResponseFactoryInterface $responseFactory;

    protected StreamFactoryInterface $streamFactory;

    protected TaskService $taskService;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        TaskService $taskService
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->taskService     = $taskService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $account = $request->getAttribute('account');

        $stream      = $this->streamFactory->createStreamFromFile('php://input');
        $rawData     = $stream->getContents();
        $requestBody = json_decode($rawData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(
                [
                    'result' => false,
                    'data'   => json_last_error(),
                    'error'  => 'Invalid JSON data',
                ],
                400
            );
        }

        $params = is_array($requestBody) ? $requestBody : [];

        $result = $this->taskService->getMembersLightList($params, $account);

        return new JsonResponse($result);
    }
}
