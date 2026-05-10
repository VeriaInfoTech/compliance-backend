<?php

namespace Erm\Handler\Api;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuditUpdateHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var TaskService */
    protected TaskService $taskService;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface   $streamFactory,
        TaskService              $taskService
    )
    {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->taskService = $taskService;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestBody = $request->getParsedBody();
        $account = $request->getAttribute('account');


        $params = [
//            'user_id'      => (int)$account['id'],
            'company_id' => (int)$account['id'],
            'standard_id' => 1,
            'task_id' => $requestBody['task_id'],
            'level' => $requestBody['level'] ?? '',
            'answer_score' => $requestBody['answer_score'] ?? '',
            'answer_value' => $requestBody['answer_value'] ?? '',
            'answer_note' => $requestBody['answer_note'] ?? '',
        ];

        if (isset($requestBody['user_id'])) {
            if ($requestBody['user_id']) {
                $params['user_id'] = $requestBody['user_id'];
            }
        }


        $result = $this->taskService->updateTaskAudit($params);

        return new JsonResponse($result);
    }
}