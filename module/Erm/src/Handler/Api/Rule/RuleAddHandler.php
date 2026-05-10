<?php

namespace Erm\Handler\Api\Rule;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RuleAddHandler implements RequestHandlerInterface
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


        $params = $requestBody;

        $params["status"] = 1;
        $params["time_create"] = time();

        ///TODO: review for role access control
        $params['user_id'] = $account['id'];

        if (isset($requestBody['target'])) {
            $params['target'] = $requestBody['target'];
        } else {
            $params['target'] = $this->config['rule']['target'];
        }

        ///TODO:remove this from production version - this add for handle rule curd in insurance panel
        $params['target'] = $this->config['rule']['target'];


        // Pass the decoded JSON data to the Task Service
        $result = [
            'result' => true,
            'data' => $this->taskService->storeRule($params),
            'error' => [],
        ];

        return new JsonResponse($result);

    }
}
