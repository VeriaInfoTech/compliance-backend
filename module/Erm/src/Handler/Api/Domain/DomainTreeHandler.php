<?php

namespace Erm\Handler\Api\Domain;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DomainTreeHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var TaskService */
    protected TaskService $taskService;

    protected array $config;
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface   $streamFactory,
        TaskService              $taskService,
                                 $config
    )
    {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->taskService = $taskService;
        $this->config =$config;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get account
        $account = $request->getAttribute('account');
        // Pass the decoded JSON data to the Task Service

        $params = [];
        if (isset($requestBody['type'])) {
            $params['type'] = $requestBody['type'];
        } else {
            $params['type'] = $this->config['domain']['type'];
        }

        $result = [
            'result' => true,
            'data' =>  $this->taskService->getDomainTree($params, $account),
            'error' => [],
        ];
        return new JsonResponse($result);


    }
}
