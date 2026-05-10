<?php

namespace Erm\Handler\Api\Member;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MemberAddHandler implements RequestHandlerInterface
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
        //$params['user_id'] = $account['id'];

        ///TODO:this is for fix bug , must be remove
        if(isset($params['idential'])){
            $params['identity']=$params['idential'];
        }
        $result = $this->taskService->addMember($params,$operator);
        return new JsonResponse($result);
    }
}
