<?php

namespace Erm\Handler\Api\Compliance;

use Erm\Service\ComplianceTaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pi\User\Service\RoleService;

class ComplianceTaskListHandler implements RequestHandlerInterface
{
    protected ResponseFactoryInterface $responseFactory;

    protected StreamFactoryInterface $streamFactory;

    protected ComplianceTaskService $complianceTaskService;

    protected RoleService $roleService;

    protected array $config;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        ComplianceTaskService $complianceTaskService,
        RoleService $roleService,
        $config
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->complianceTaskService = $complianceTaskService;
        $this->roleService = $roleService;
        $this->config = $config;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $account = $request->getAttribute('account');

        $stream = $this->streamFactory->createStreamFromFile('php://input');
        $rawData = $stream->getContents();

        $requestBody = json_decode($rawData, true);

        $params = $requestBody;

        $roles = ($this->roleService->getRoleAccount((int) $account['id']));
        if (!in_array('grc_admin', $roles)) {
            $params['enforcer'] = $account['id'];
        }

        if (isset($requestBody['type'])) {
            $params['type'] = $requestBody['type'];
        }

        if (in_array('insurance', $this->config['type'])) {
            $params['type'] = ['insurance', 'insurance-statement', 'insurance-comparison'];
        }

        if (isset($requestBody['reference_id'])) {
            $params['reference_id'] = $requestBody['reference_id'];
        } else {
            $params['reference_id'] = 0;
        }

        $result = $this->complianceTaskService->getComplianceTreeWithFilter($params, $account);

        return new JsonResponse($result);
    }
}
