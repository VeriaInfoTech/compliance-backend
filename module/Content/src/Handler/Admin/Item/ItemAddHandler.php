<?php

namespace Content\Handler\Admin\Item;

use Content\Service\ItemBulkService;
use Content\Service\ItemService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ItemAddHandler implements RequestHandlerInterface
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
//        // Check if bulk import mode is requested via query parameter
//        $queryParams = $request->getQueryParams();
//        if (isset($queryParams['mode']) && $queryParams['mode'] === 'bulk_import') {
//            return $this->handleBulkImport($request);
//        }

        // Regular single item add
        $requestBody = $request->getParsedBody();

        if (empty($requestBody['user_id'])) {
            $account = $request->getAttribute('account');
            if ($account && isset($account['id'])) {
                $requestBody['user_id'] = $account['id'];
            }
        }

        $result = $this->itemService->addItem($requestBody);

        return new JsonResponse($result);
    }

    /**
     * Handle bulk import from JSON files
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function handleBulkImport(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $account = $request->getAttribute('account');
            $userId = $account['id'] ?? null;

            // Get the controls path from project root
            $controlsPath = dirname(__DIR__, 6) . '/bin/controls';

            // Execute bulk import
            $result = $this->itemBulkService->importFromJsonFiles($controlsPath, $userId);

            return new JsonResponse([
                'result' => true,
                'data' => $result,
                'error' => [],
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'result' => false,
                'data' => [],
                'error' => ['message' => $e->getMessage()],
            ]);
        }
    }
}
