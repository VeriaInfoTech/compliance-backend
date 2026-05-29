<?php

namespace Content\Handler\Api\Item;

use Content\Service\ItemService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ItemDetailHandler implements RequestHandlerInterface
{
    protected ItemService $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestBody = $request->getParsedBody();
        $result = $this->itemService->getItem($requestBody['slug'], 'slug');

        return new JsonResponse([
            'result' => true,
            'data'   => $result,
            'error'  => [],
        ]);
    }
}
