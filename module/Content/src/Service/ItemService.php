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

    private const FILTER_CONFIG = [
        'color' => ['meta_key' => 'color', 'type' => 'string', 'explode' => true],
        'size' => ['meta_key' => 'size', 'type' => 'string', 'explode' => true],
        'brand' => ['meta_key' => 'brand', 'type' => 'id'],
        'min_price' => ['meta_key' => 'price', 'type' => 'rangeMin'],
        'max_price' => ['meta_key' => 'price', 'type' => 'rangeMax'],
        'min_height' => ['meta_key' => 'height', 'type' => 'rangeMin'],
        'max_height' => ['meta_key' => 'height', 'type' => 'rangeMax'],
        'min_width' => ['meta_key' => 'width', 'type' => 'rangeMin'],
        'max_width' => ['meta_key' => 'width', 'type' => 'rangeMax'],
        'min_diagonal' => ['meta_key' => 'diagonal', 'type' => 'rangeMin'],
        'max_diagonal' => ['meta_key' => 'diagonal', 'type' => 'rangeMax'],
        'min_flames_count' => ['meta_key' => 'flames-count', 'type' => 'rangeMin'],
        'max_flames_count' => ['meta_key' => 'flames-count', 'type' => 'rangeMax'],
        'flames_count' => ['meta_key' => 'flames-count', 'type' => 'int'],
        'special_suggest' => ['meta_key' => 'special-suggest', 'type' => 'slug'],
        'product_middle_section' => ['meta_key' => 'product-middle-section', 'type' => 'slug'],
        'product_trend' => ['meta_key' => 'product-trend', 'type' => 'slug'],
        'product_popular' => ['meta_key' => 'product-popular', 'type' => 'slug'],
        'product_new' => ['meta_key' => 'product-new', 'type' => 'slug'],
        'product_special' => ['meta_key' => 'product-special', 'type' => 'slug'],
        'categories' => ['meta_key' => 'category', 'type' => 'slug', 'explode' => true],
        'category_list' => ['meta_key' => 'category', 'type' => 'slug'],
        'brand_list' => ['meta_key' => 'brand', 'type' => 'slug'],
        'colors' => ['meta_key' => 'color', 'type' => 'slug', 'explode' => true],
        'shed_colors' => ['meta_key' => 'shed_color', 'type' => 'slug', 'explode' => true],
        'target-muscles' => ['meta_key' => 'target-muscles', 'type' => 'slug'],
        'activity-types' => ['meta_key' => 'activity-types', 'type' => 'slug'],
        'type-muscles' => ['meta_key' => 'type-muscles', 'type' => 'slug'],
    ];

    public function __construct(
        ItemRepositoryInterface $itemRepository,
        UtilityService          $utilityService
    ) {
        $this->itemRepository = $itemRepository;
        $this->utilityService = $utilityService;
    }

    public function getItemList(array $params): array
    {
        $limit = $params['limit'] ?? 125;
        $page = $params['page'] ?? 1;
        $order = $params['order'] ?? ['priority desc', 'id desc'];
        $offset = ($page - 1) * $limit;

        $filters = $this->prepareFilter($params);

        $listParams = [
            'order' => $order,
            'offset' => $offset,
            'limit' => $limit,
            'type' => $params['type'],
            'status' => isset($params['status']) ? $params['status'] : 1,
        ];

        if (array_key_exists('support_follow_up_date', $params)) {
            if ($params['support_follow_up_date']) {
                $listParams['support_follow_up_date'] = $params['support_follow_up_date'];
            }
        }

        if (array_key_exists('support_title', $params)) {
            if ($params['support_title']) {
                $listParams['support_title'] = $params['support_title'];
            }
        }

        if (array_key_exists('support_product_title', $params)) {
            if ($params['support_product_title']) {
                $listParams['support_product_title'] = $params['support_product_title'];
            }
        }

        if (array_key_exists('support_customer_name', $params)) {
            if ($params['support_customer_name']) {
                $listParams['support_customer_name'] = $params['support_customer_name'];
            }
        }

        if (array_key_exists('support_customer_email', $params)) {
            if ($params['support_customer_email']) {
                $listParams['support_customer_email'] = $params['support_customer_email'];
            }
        }

        if (array_key_exists('support_customer_id', $params)) {
            if ($params['support_customer_id']) {
                $listParams['support_customer_id'] = $params['support_customer_id'];
            }
        }

        if (array_key_exists('support_status', $params)) {
            if (isset($params['support_status']['value'])) {
                if (in_array($params['support_status']['value'], [0, 1])) {
                    $listParams['status'] = $params['support_status']['value'];
                }
            }
        }

        if (array_key_exists('support_order_status', $params)) {
            if (isset($params['support_order_status']['value'])) {
                $listParams['support_order_status'] = $params['support_order_status']['value'];
            }
        }

        if (isset($params['data_from'])) {
            $listParams['data_from'] = strtotime(
                ($params['data_from']) != null
                    ? sprintf('%s 00:00:00', $params['data_from'])
                    : sprintf('%s 00:00:00', date('Y-m-d', strtotime('-1 month')))
            );
        }

        if (isset($params['data_to'])) {
            $listParams['data_to'] = strtotime(
                ($params['data_to']) != null
                    ? sprintf('%s 00:00:00', $params['data_to'])
                    : sprintf('%s 23:59:59', date('Y-m-d'))
            );
        }

        if (isset($params['user_id'])) {
            $listParams['user_id'] = $params['user_id'];
        }

        if (isset($params['parent_id'])) {
            $listParams['parent_id'] = $params['parent_id'];
        }

        if (isset($params['title'])) {
            $listParams['title'] = $params['title'];
        }

        if (!empty($filters)) {
            $isFresh = true;
            foreach ($filters as $filter) {
                $itemIdList = [];
                $rowSet = $this->itemRepository->getIDFromFilter($filter);
                foreach ($rowSet as $row) {
                    $itemIdList[] = $this->canonizeMetaItemID($row);
                }
                if ($isFresh) {
                    $listParams['id'] = $itemIdList;
                    $isFresh = false;
                } else {
                    $listParams['id'] = array_intersect($listParams['id'], $itemIdList);
                }
            }
        }

        if (isset($params['id']) && !empty($params['id'])) {
            $listParams['id'] = isset($listParams['id']) ? array_intersect($listParams['id'], $params['id']) : $params['id'];
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
                'filters' => $filters,
            ],
            'error' => [],
        ];
    }

    public function canonizeMetaItemID(object|array $meta): int|null
    {
        if (empty($meta)) {
            return 0;
        }

        if (is_object($meta)) {
            $itemID = $meta->getItemID();
        } else {
            $itemID = $meta['item_id'];
        }

        return $itemID;
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

    public function prepareFilter(array $params): array
    {
        $filters = [];

        foreach ($params as $key => $value) {
            if (!isset(self::FILTER_CONFIG[$key]) || !$this->isValidFilterValue($value)) {
                continue;
            }

            $config = self::FILTER_CONFIG[$key];
            $filterValue = $config['explode'] ?? false ? explode(',', $value) : $value;

            $filters[$key] = [
                'meta_key' => $config['meta_key'],
                'value' => $filterValue,
                'type' => $config['type'],
            ];
        }

        return $filters;
    }

    private function isValidFilterValue(mixed $value): bool
    {
        return !empty($value) && $value !== '';
    }
}
