<?php

namespace Erm\Handler\Api\Task;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TaskAddHandler implements RequestHandlerInterface
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

        // Retrieve the raw JSON data from the request body
        $stream = $this->streamFactory->createStreamFromFile('php://input');
        $rawData = $stream->getContents();

        // Decode the raw JSON data into an associative array
        $requestBody = json_decode($rawData, true);

        $params = [
            "id"=>$requestBody["id"]??null,
            "standard_id"=>1,
            "code"=>$requestBody["code"],
            "has_clause"=>$requestBody["has_clause"],
            "reference_id"=>$requestBody["reference_id"],
            "title"=>$requestBody["title"],
            "section_id"=>$requestBody["section_id"],
            "rule_id"=>$requestBody["rule_id"],
            "warranty_id"=>$requestBody["warranty_id"],
            "mandatory_unit"=>json_encode($requestBody["mandatory_unit"]??[]),
        ];

        ///TODO: review for role access control
        $params['user_id'] = $account['id'];
        if (isset($requestBody['type'])) {
            $params['type'] = $requestBody['type'];
        } else {
            $params['type'] = $this->config['task']['type'];
        }

        //kerloper : static config
        $requestBody['type'] = 'compliance';

        $result = [
            'result' => true,
            'data' => $this->taskService->storeTask($params),
            'error' => [],
        ];

        return new JsonResponse($result);
    }
}
