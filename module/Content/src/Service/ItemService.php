<?php

namespace Content\Service;

use Content\Repository\ItemRepositoryInterface;
use Pi\Core\Service\UtilityService;
use function explode;
use function is_object;
use function json_decode;

class ItemService implements ServiceInterface
{
    protected ItemRepositoryInterface $itemRepository;
    protected UtilityService $utilityService;

    private const ALLOWED_LIST_PARAMS = ['type', 'source', 'status', 'limit', 'page', 'order'];

    public function __construct(
        ItemRepositoryInterface $itemRepository,
        UtilityService          $utilityService
    ) {
        $this->itemRepository = $itemRepository;
        $this->utilityService = $utilityService;
    }

    public function getItemList(array $params): array
    {
        $params = $this->filterListParams($params);
        $params = $this->sanitizeListParams($params);

        $limit = $params['limit'] ?? 125;
        $page = $params['page'] ?? 1;
        $order = $params['order'] ?? ['priority desc', 'id desc'];
        $offset = ($page - 1) * $limit;

        $listParams = [
            'order' => $order,
            'offset' => $offset,
            'limit' => $limit,
            'type' => $params['type'],
            'status' => isset($params['status']) ? $params['status'] : 1,
        ];

        if (isset($params['source'])) {
            $listParams['source'] = $params['source'];
        }

        $list = [];
        $rowSet = $this->itemRepository->getItemList($listParams);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeItem($row, $params['type']);
        }

        $count = $this->itemRepository->getItemCount($listParams);

        return [
            'result' => true,
            'data' => [
                'list' => $list,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page' => $page,
                ],
            ],
            'error' => [],
        ];
    }

    public function canonizeItem(object|array $item, $type = 'global'): array
    {
        if (empty($item)) {
            return [];
        }

        if (is_object($item)) {
            $item = [
                'id' => $item->getId(),
                'title' => $item->getTitle(),
                'slug' => $item->getSlug(),
                'type' => $item->getType(),
                'status' => $item->getStatus(),
                'user_id' => $item->getUserId(),
                'time_create' => $item->getTimeCreate(),
                'time_update' => $item->getTimeUpdate(),
                'time_delete' => $item->getTimeDelete(),
                'information' => $item->getInformation(),
                'priority' => $item->getPriority(),
            ];
        } else {
            $item = [
                'id' => $item['id'],
                'title' => $item['title'],
                'slug' => $item['slug'],
                'type' => $item['type'],
                'status' => $item['status'],
                'user_id' => $item['user_id'],
                'time_create' => $item['time_create'],
                'time_update' => $item['time_update'],
                'time_delete' => $item['time_delete'],
                'information' => $item['information'],
                'priority' => $item['priority'],
            ];
        }

        $data = !empty($item['information']) ? json_decode($item['information'], true) : [];

        if ($type == 'product') {
            $data['thumbnail'] = $data['image'] ?? null;
        }

        $data['time_create_view'] = $this->utilityService->date($item['time_create']);
        $data['id'] = $item['id'];
        if (isset($data['image']))
            if (!isset($data['thumbnail']))
                $data['thumbnail'] = $data['image'];
        return $data;
    }

    public function getItem(string $parameter, string $type = 'id', $params = []): array
    {
        $item = $this->itemRepository->getItem($parameter, $type, $params);
        return $this->canonizeItem($item, (isset($params['type'])) ? $params['type'] : 'global');
    }

    private function filterListParams(array $params): array
    {
        $filtered = [];

        foreach ($params as $key => $value) {
            if (in_array($key, self::ALLOWED_LIST_PARAMS, true)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    private function sanitizeListParams(array $params): array
    {
        $params['limit'] = $this->sanitizeLimit($params['limit'] ?? 125);
        $params['page'] = $this->sanitizePage($params['page'] ?? 1);
        $params['status'] = isset($params['status']) ? (int) $params['status'] : 1;
        $params['source'] = isset($params['source']) ? (string) $params['source'] : null;
        $params['type'] = isset($params['type']) ? (string) $params['type'] : 'global';

        return $params;
    }

    private function sanitizeLimit(mixed $limit): int
    {
        $limit = (int) $limit;
        return $limit > 0 && $limit <= 1000 ? $limit : 125;
    }

    private function sanitizePage(mixed $page): int
    {
        $page = (int) $page;
        return $page > 0 ? $page : 1;
    }

    public function addItem(array $params): array
    {
        $validation = $this->validateItemParams($params);
        if (!$validation['valid']) {
            return ['result' => false, 'data' => [], 'error' => $validation['errors']];
        }

        $params['time_create'] = $params['time_create'] ?? time();
        $params['time_update'] = $params['time_update'] ?? time();

        $item = $this->itemRepository->addItem($params);

        return [
            'result' => true,
            'data' => $this->canonizeItem($item),
            'error' => []
        ];
    }

    private function validateItemParams(array $params): array
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
