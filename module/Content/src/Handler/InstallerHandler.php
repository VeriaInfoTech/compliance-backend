<?php

namespace Content\Handler;

use Laminas\Diactoros\Response\JsonResponse;
use Pi\Core\Service\InstallerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class InstallerHandler implements RequestHandlerInterface
{
    protected InstallerService $installerService;

    public function __construct(InstallerService $installerService)
    {
        $this->installerService = $installerService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $permissionFile = include realpath(__DIR__ . '/../../config/module.permission.php');
        $this->installerService->installPermission('content', $permissionFile);

        return new JsonResponse([
            'result' => true,
            'data'   => [],
            'error'  => [],
        ]);
    }
}
