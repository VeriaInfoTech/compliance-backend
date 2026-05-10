<?php

namespace Erm\Handler\Api\Rule;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RuleLightListHandler implements RequestHandlerInterface
{
    protected ResponseFactoryInterface $responseFactory;

    protected StreamFactoryInterface $streamFactory;

    protected TaskService $taskService;

    protected array $config;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        TaskService $taskService,
        array $config
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->taskService     = $taskService;
        $this->config          = $config;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $account = $request->getAttribute('account');

        $stream    = $this->streamFactory->createStreamFromFile('php://input');
        $rawData   = $stream->getContents();
        $requestBody = json_decode($rawData, true);

        $params = is_array($requestBody) ? $requestBody : [];

        if (isset($requestBody['target'])) {
            $params['target'] = $requestBody['target'];
        } else {
            $params['target'] = $this->config['type'];
        }

        $result = $this->taskService->getRulesLightList($params, $account);

        return new JsonResponse($result);
    }
}
