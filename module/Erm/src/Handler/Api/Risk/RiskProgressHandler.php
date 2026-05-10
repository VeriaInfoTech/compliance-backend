<?php

namespace Erm\Handler\Api\Risk;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
 use Pi\User\Service\RoleService;

class RiskProgressHandler implements RequestHandlerInterface
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

        if(($requestBody['level'] ?? '')=='approve'){
            $params = [
                'task_id' => $requestBody['task_id'],
                'level' =>'approve',
            ];
        }else{
            $params = [
                'standard_id' => 1,
                'task_id' => $requestBody['task_id'],
                'level' => $requestBody['level'] ?? '',
                'type' => $requestBody['type'] ?? 'single',
                'risk_intensity' => $requestBody['risk_intensity'] ?? null,
                'risk_effect' => $requestBody['risk_effect'] ?? null,
                'risk_threat' => $requestBody['risk_threat'] ?? null,
                'risk_damage' => $requestBody['risk_damage'] ?? '',
                'risk_execution_percent' => $requestBody['risk_execution_percent'] ?? null,
                'risk_proposed_action' => $requestBody['risk_proposed_action'] ?? '',
                'risk_response_type' => $requestBody['risk_response_type'] ?? '',
                'risk_data' => ($requestBody['risk_effect']&&$requestBody['risk_intensity']) ?(($requestBody['risk_effect']) * ($requestBody['risk_intensity'])):null,
                'risk_scenario' => ($requestBody['risk_threat'] ?? '') . ' ' . ($requestBody['risk_damage'] ?? ''),
            ];
        }



        // TODO: handle this
        // set company_id only in assign task to user
        $params['company_id'] = 1;

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
                    'message' => 'This task does not exist!' ,
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



        // TODO: handle this
        ///role == grc_member && $params['user_id] != (int)$account['id']
        $roles = ($this->roleService->getRoleAccount((int)$account['id']));
        if (!in_array('grc_admin', $roles)) {
            if (!in_array($params['level'], ['doing', 'done'])) {
                return new JsonResponse([
                    'result' => false,
                    'data' => new \stdClass(),
                    'error' => [
                        'message' =>  'You have not access to this action!' ,
                        'code' => 403
                    ]
                ]);
            }
            $params['user_id'] = $account['id'];
        }


//        $complianceProgress = $this->taskService->getComplianceTaskProgress(['task_id' => $params['task_id']]);
//
//        //check this task is assigned in compliance or no
//        if (empty($complianceProgress)) {
//            return new JsonResponse([
//                'result' => false,
//                'data' => new \stdClass(),
//                'error' => [
//                    'message' =>  'This task must assign for todo in compliance mode , to any grc member!' ,
//                    'code' => 400
//                ]
//            ]);
//        }


          ///TODO: check logic of this section
        // check the is task assign to other user
        if ($params['type'] == 'child') {
            $params['progress_id'] = $requestBody['progress_id'] ?? -1;
            $riskProgress = $this->taskService->getRiskTaskProgress(['id' => $params['progress_id'], 'task_id' => $params['task_id']]);
            if (!empty($riskProgress)) {
                ///check that the task progress is for same user
                if ($riskProgress['user_id'] != $params['user_id']) {
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
                $params['assigner_id'] = $riskProgress['assigner_id'];
            } else {
                return new JsonResponse([
                    'result' => false,
                    'data' => new \stdClass(),
                    'error' => [
                        'message' =>   'This task progress does not exist!' ,
                        'code' => 404
                    ]
                ]);
            }

        } else {
            $riskProgress = $this->taskService->getRiskTaskProgress(['task_id' => $params['task_id'], 'parent_id' => 0]);
            if (!empty($riskProgress)) {
                if ($riskProgress['user_id'] != $params['user_id']) {
                    return new JsonResponse([
                        'result' => false,
                        'data' => new \stdClass(),
                        'error' => [
                            'message' =>  'This task assigned to another user !' ,
                            'code' => 403
                        ]
                    ]);
                }
                $params['assigner_id'] = $riskProgress['assigner_id'];


                if (in_array($params['level'], ['approve', 'done'])) {
                    $childProgress = $this->taskService->getRiskTaskProgress(['level' => ['todo', 'doing', 'reject', 'unassign'], 'parent_id' => $riskProgress['id']]);
                    if(!empty($childProgress)){
                        return new JsonResponse([
                            'result' => false,
                            'data' => new \stdClass(),
                            'error' => [
                                'message' =>  'This task has unfinished processing!' ,
                                'code' => 403
                            ]
                        ]);
                    }
                }
            }
        }

        $result = [
            'result' => true,
            'data' => $this->taskService->riskProgressTask($params, $account),
            'error' => new \stdClass()
        ];
        return new JsonResponse($result);

    }
}
