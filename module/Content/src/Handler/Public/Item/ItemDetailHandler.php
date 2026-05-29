<?php

namespace Content\Handler\Public\Item;

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
        $requestBody['status'] = 1;
        $result = $this->itemService->getItem($requestBody['slug'] ?? '', 'slug', $requestBody);

        return new JsonResponse([
            'result' => true,
            'data'   => $result,
            'error'  => [],
        ]);
    }
}
