<?php

namespace Content\Handler\Admin\Item;

use Content\Service\ItemBulkService;
use Content\Service\ItemService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ItemUpdateHandler implements RequestHandlerInterface
{
    protected ItemService $itemService;
    protected ItemBulkService $itemBulkService;

    public function __construct(ItemService $itemService, ItemBulkService $itemBulkService)
    {
        $this->itemService = $itemService;
        $this->itemBulkService = $itemBulkService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestBody = $request->getParsedBody();

        if (empty($requestBody['user_id'])) {
            $account = $request->getAttribute('account');
            if ($account && isset($account['id'])) {
                $requestBody['user_id'] = $account['id'];
            }
        }

        $result = $this->itemService->updateItem($requestBody);

        return new JsonResponse($result);
    }
}
