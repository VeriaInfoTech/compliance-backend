<?php

namespace Erm\Handler\Api\Compliance\Detail;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
 use Pi\User\Service\RoleService;

class ComplianceProgressDetailHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var TaskService */
    protected TaskService $taskService;

    /** @var RoleService */
    protected RoleService $roleService;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface   $streamFactory,
        TaskService              $taskService,
        RoleService              $roleService
    )
    {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->taskService = $taskService;
        $this->roleService = $roleService;
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
            'task_id' => $requestBody['task_id']??-1,
            'progress_id' => $requestBody['progress_id'] ??-1
        ];


        if (isset($requestBody['user_id'])) {
            if ($requestBody['user_id']) {
                $params['user_id'] = $requestBody['user_id'];
            }
        }


        $task = $this->taskService->getTask(['id' => $params['task_id']], []);
        if (empty($task)) {
            return new JsonResponse([
                'result' => false,
                'data' => new \stdClass(),
                'error' => [
                    'message' =>   'This task does not exist!' ,
                    'code' => 404
                ]
            ]);
        }


        $roles = ($this->roleService->getRoleAccount((int)$account['id']));
        if (!in_array('grc_admin', $roles)) {

                return new JsonResponse([
                    'result' => false,
                    'data' => new \stdClass(),
                    'error' => [
                        'message' => 'You have not access to this action!' ,
                        'code' => 403
                    ]
                ]);
        }


        $progress = $this->taskService->getComplianceTaskProgress(['id' => $params['progress_id'], 'task_id' => $params['task_id'], 'parent_id' => 0]);
        if ( empty($progress))  {
            return new JsonResponse([
                'result' => false,
                'data' => new \stdClass(),
                'error' => [
                    'message' => 'This task progress does not exist!' ,
                    'code' => 404
                ]
            ]);
        }



        $data = $this->taskService->getComplianceTaskProgressDetail($params, $account);
        $result = [
            'result' => true,
            'data' => $data,
            'error' => new \stdClass()
        ];
        return new JsonResponse($result);

    }
}
