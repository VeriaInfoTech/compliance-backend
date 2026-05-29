<?php

namespace Content\Handler\Api;

use Content\Service\ItemService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MainHandler implements RequestHandlerInterface
{
    protected ItemService $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $account = $request->getAttribute('account');

        $result = [
            "slider" => [
                "type" => 'slider',
                "title" => "",
                "subtitle" => "",
                "list" => $this->itemService->getMarks(["type" => "location", "limit" => 5, "page" => 1], $account)["data"]["list"],
            ],
            "sections" => [
                [
                    "type" => 'location',
                    "title" => "Best promotion for you",
                    "subtitle" => "Best promotion for you",
                    "list" => $this->itemService->getMarks(["type" => "location", "limit" => 5, "page" => 2], $account)["data"]["list"],
                ],
                [
                    "type" => 'location',
                    "title" => "VIP",
                    "subtitle" => "Vip",
                    "list" => $this->itemService->getMarks(["type" => "location", "limit" => 5, "page" => 3], $account)["data"]["list"],
                ]
            ]
        ];

        return new JsonResponse([
            'result' => true,
            'data' => $result,
            'error' => [],
        ]);
    }
}
