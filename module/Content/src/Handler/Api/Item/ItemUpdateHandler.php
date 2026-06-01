<?php

namespace Content\Handler\Api\Item;

use Content\Service\ItemService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ItemUpdateHandler implements RequestHandlerInterface
{
    protected ItemService $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestBody = $request->getParsedBody();

        $account = $request->getAttribute('account');
        if ($account && isset($account['id'])) {
            $requestBody['user_id'] = $account['id'];
        }

        $result = $this->itemService->updateItem($requestBody);

        return new JsonResponse($result);
    }
}
