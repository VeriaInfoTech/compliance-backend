<?php

namespace Erm\Handler\Api\Member;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

class MemberViewHandler implements RequestHandlerInterface
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
        $operator = $request->getAttribute('account');
        $stream = $this->streamFactory->createStreamFromFile('php://input');
        $rawData = $stream->getContents();
        $requestBody = json_decode($rawData, true);
        $params = $requestBody;
        ///TODO: review for role access control
        $params['user_id'] = (isset($params['user_id'])&&!empty($params['user_id']))?$params['user_id']:0;
        $result = [
            'result' => true,
            'data' => $this->taskService->viewMember($params,$operator),
            'error' => new stdClass(),
        ];
        return new JsonResponse($result);

    }
}
