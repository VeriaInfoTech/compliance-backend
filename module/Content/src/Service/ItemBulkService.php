<?php

namespace Content\Service;

use Content\Repository\ItemRepositoryInterface;
use RuntimeException;
use function json_decode;
use function json_encode;
use function file_get_contents;
use function glob;
use function is_readable;
use function sprintf;

class ItemBulkService
{
    protected ItemService $itemService;
    protected ItemRepositoryInterface $itemRepository;

    public function __construct(
        ItemService $itemService,
        ItemRepositoryInterface $itemRepository
    ) {
        $this->itemService = $itemService;
        $this->itemRepository = $itemRepository;
    }

    /**
     * Import items from JSON files in controls directory
     */
    public function importFromJsonFiles(string $controlsPath, ?int $userId = null): array
    {
        if (!is_readable($controlsPath)) {
            throw new RuntimeException(sprintf('Controls path is not readable: %s', $controlsPath));
        }

        $results = [
            'success' => 0,
            'failed' => 0,
            'items' => [],
            'errors' => [],
        ];

        $jsonFiles = glob($controlsPath . '/*.json');

        foreach ($jsonFiles as $filePath) {
            try {
                $content = file_get_contents($filePath);

                if ($content === false) {
                    $results['errors'][] = "Failed to read file: $filePath";
                    $results['failed']++;
                    continue;
                }

                $data = json_decode($content, true);

                if (!is_array($data)) {
                    $results['errors'][] = "Invalid JSON: $filePath";
                    $results['failed']++;
                    continue;
                }

                foreach ($data as $object) {
                    try {
                        $item = $this->processJsonObject($object, $userId);

                        if ($item) {
                            $results['items'][] = $item;
                            $results['success']++;
                        }
                    } catch (RuntimeException $e) {
                        $results['errors'][] = $e->getMessage();
                        $results['failed']++;
                    }
                }
            } catch (RuntimeException $e) {
                $results['errors'][] = $e->getMessage();
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Process single JSON object
     */
    public function processJsonObject(array $object, ?int $userId = null): ?array
    {
        if (empty($object)) {
            return null;
        }

        [$dbParams, $extraData] = $this->splitObject($object, $userId);

        $validation = $this->validateItemParams($dbParams);

        if (!$validation['valid']) {
            throw new RuntimeException(
                sprintf('Validation failed: %s', json_encode($validation['errors']))
            );
        }

        $dbParams['time_create'] = $dbParams['time_create'] ?? time();
        $dbParams['time_update'] = $dbParams['time_update'] ?? time();

        // INSERT
        $insertedItem = $this->itemRepository->addItem($dbParams);



        $itemId = $insertedItem->getId();

        // enrich information with DB id
        $extraData['id'] = $itemId;
        $extraData['time_create_view'] = date('Y-m-d H:i', $dbParams['time_create'] ?? time());
        $extraData['time_update_view'] = date('Y-m-d H:i', $dbParams['time_update'] ?? time());

        // UPDATE information field AFTER insert
        $this->updateItemInformation($itemId, $extraData);

        return $this->itemService->canonizeItem($insertedItem);
    }

    /**
     * Split DB columns vs extra JSON data
     */
    protected function splitObject(array $object, ?int $userId): array
    {
        $dbParams = [];
        $extraData = $object;

        $allowedColumns = [
            'source',
            'title',
            'slug',
            'parent_slug',
            'type',
            'status',
            'priority',
            'user_id',
            'time_create',
            'time_update',
        ];

        foreach ($allowedColumns as $key) {
            if (isset($object[$key])) {
                $dbParams[$key] = $object[$key];
//                unset($extraData[$key]);
            }
        }

        if ($userId !== null) {
            $dbParams['user_id'] = $userId;
        }

        if (!isset($dbParams['status'])) {
            $dbParams['status'] = 1;
        }

        return [$dbParams, $extraData];
    }

    /**
     * Update information field after insert
     */
    protected function updateItemInformation(mixed $itemId, array $enrichedObject): void
    {
        $this->itemRepository->editItem([
            'id' => $itemId,
            'information' => json_encode($enrichedObject, JSON_UNESCAPED_UNICODE),
            'time_update' => time(),
        ]);
    }

    /**
     * Validate required DB fields
     */
    protected function validateItemParams(array $params): array
    {
        $errors = [];

        if (empty($params['title'])) {
            $errors['title'] = 'Title is required';
        }

        if (empty($params['slug'])) {
            $errors['slug'] = 'Slug is required';
        }

        if (empty($params['type'])) {
            $errors['type'] = 'Type is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}