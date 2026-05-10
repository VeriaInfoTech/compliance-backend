<?php

namespace Erm\Handler\Api\Task;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pi\User\Model\Role\Account;
use Pi\User\Service\AccountService;
 use Pi\User\Service\RoleService;

class TaskListHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var TaskService */
    protected TaskService $taskService;

    /** @var RoleService */
    protected RoleService $roleService;

    protected array $config;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface   $streamFactory,
        TaskService              $taskService,
        RoleService              $roleService,
                                 $config
    )
    {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->taskService = $taskService;
        $this->roleService = $roleService;
        $this->config = $config;
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
        ///TODO: review for role access control
//        $params['user_id'] = $account['id'];

        // role access control
        $roles = ($this->roleService->getRoleAccount((int)$account['id']));
        if (!in_array('grc_admin', $roles)) {
            $params['enforcer'] = $account['id'];
        }

        ///TODO:check this comment - comment bottom for change compliance to multi type task
        if (isset($requestBody['type'])) {
            $params['type'] = $requestBody['type'];
        }

        ///TODO: think about bottom condition - this set for handle insurance panel
        if (in_array('insurance', $this->config['type'])) {
            $params['type'] = ['insurance', 'insurance-statement', 'insurance-comparison'];
        }

        //kerloper : static config
        if (isset($requestBody['reference_id'])) {
            $params['reference_id'] = $requestBody['reference_id'];
        } else {
            $params['reference_id'] = 0;
        }


        $result = $this->taskService->getTaskTreeWhitFilter($params, $account);
        return new JsonResponse($result);

    }
}
