<?php

namespace Content\Handler\Api\Report;

use Content\Service\ItemService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ReportGetHandler implements RequestHandlerInterface
{
    protected ItemService $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $result = $this->itemService->getItemList(["type"=>["domain","control"]]);

        return new JsonResponse($result);
    }
}