<?php

namespace Erm\Handler\Api\Compliance;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
 use Pi\User\Service\RoleService;

class ComplianceProgressHandler implements RequestHandlerInterface
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
            'standard_id' => 1,
            'task_id' => $requestBody['task_id'],
            'level' => $requestBody['level'] ?? '',
            'type' => $requestBody['type'] ?? 'single',
            'comment' => $requestBody['comment'] ?? '',
            'answer_score' => $requestBody['answer_score'] ?? 0,
            'answer_value' => $requestBody['answer_value'] ?? '',
            'answer_note' => $requestBody['answer_note'] ?? '',
            'company_id' => 1,
        ];


        if (isset($requestBody['user_id'])) {
            if ($requestBody['user_id']) {
                $params['user_id'] = $requestBody['user_id'];
            }
        }

        if ($params['level'] == 'todo') {
            $params['assigner_id'] = $account['id'];
            $params['time_deadline'] = $requestBody['time_deadline'];
        }

        $task = $this->taskService->getTask(['id' => $params['task_id']], []);

        if (empty($task)) {
            return new JsonResponse([
                'result' => false,
                'data' => new \stdClass(),
                'error' => [
                    'message' =>  'This task does not exist!' ,
                    'code' => 404
                ]
            ]);
        }


        if (!in_array($params['level'], ['todo', 'doing', 'done', 'approve', 'reject', 'unassign'])) {
            return new JsonResponse([
                'result' => false,
                'data' => new \stdClass(),
                'error' => [
                    'message' =>  'Bad request!' ,
                    'code' => 400
                ]
            ]);
        }


        $roles = ($this->roleService->getRoleAccount((int)$account['id']));
        if (!in_array('grc_admin', $roles)) {
            if (!in_array($params['level'], ['doing', 'done'])) {
                return new JsonResponse([
                    'result' => false,
                    'data' => new \stdClass(),
                    'error' => [
                        'message' => 'You have not access to this action!' ,
                        'code' => 403
                    ]
                ]);
            }
            $params['user_id'] = $account['id'];
        }

        ///TODO: check logic of this section
        // check the is task assign to other user
        if ($params['type'] == 'child') {
            $params['progress_id'] = $requestBody['progress_id'] ?? -1;
            $progress = $this->taskService->getComplianceTaskProgress(['id' => $params['progress_id'], 'task_id' => $params['task_id']]);
            if (!empty($progress)) {

                ///check that the task progress is for same user
                if ($progress['user_id'] != $params['user_id']) {
                    return new JsonResponse([
                        'result' => false,
                        'data' => new \stdClass(),
                        'error' => [
                            'message' =>  'This task assigned to another user !' ,
                            'code' => 403
                        ]
                    ]);
                }

//                if (!in_array('grc_admin', $roles)) {
                    if (in_array($params['level'], ['unassign', 'approve', 'reject'])) {
                        return new JsonResponse([
                            'result' => false,
                            'data' => new \stdClass(),
                            'error' => [
                                'message' =>  'You can not change level of this progress!' ,
                                'code' => 403
                            ]
                        ]);
                    }
//                }
                $params['assigner_id'] = $progress['assigner_id'];
            } else {
                return new JsonResponse([
                    'result' => false,
                    'data' => new \stdClass(),
                    'error' => [
                        'message' =>  'This task progress does not exist!' ,
                        'code' => 404
                    ]
                ]);
            }

        }
        else {
            $progress = $this->taskService->getComplianceTaskProgress(['task_id' => $params['task_id'], 'parent_id' => 0]);
            if (!empty($progress)) {
                if ($progress['user_id'] != $params['user_id']) {
                    return new JsonResponse([
                        'result' => false,
                        'data' => new \stdClass(),
                        'error' => [
                            'message' => 'This task assigned to another user !' ,
                            'code' => 403
                        ]
                    ]);
                }
                $params['assigner_id'] = $progress['assigner_id'];


                if (in_array($params['level'], ['approve', 'done'])) {
                    $childProgress = $this->taskService->getComplianceTaskProgress(['level' => ['todo', 'doing', 'reject', 'unassign'], 'parent_id' => $progress['id']]);
                    if(!empty($childProgress)){
                        return new JsonResponse([
                            'result' => false,
                            'data' => new \stdClass(),
                            'error' => [
                                'message' => 'This task has unfinished processing!' ,
                                'code' => 403
                            ]
                        ]);
                    }
                }
            }
        }

        //check this task is assigned as todo
        if (empty($progress) && $params['level'] != 'todo') {
            return new JsonResponse([
                'result' => false,
                'data' => new \stdClass(),
                'error' => [
                    'message' =>  'This task must assign for todo to any grc member!' ,
                    'code' => 400
                ]
            ]);
        }


        $data = $this->taskService->complianceProgressTask($params, $account);
        $result = [
            'result' => true,
            'data' => $data,
            'error' => new \stdClass()
        ];
        return new JsonResponse($result);

    }
}
