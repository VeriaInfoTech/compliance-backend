<?php

namespace Content\Service;

use Content\Repository\ItemRepositoryInterface;
use Pi\User\Service\AccountService;
use Pi\Core\Service\UtilityService;
use function explode;
use function in_array;
use function is_object;
use function json_decode;

class ItemService implements ServiceInterface
{
    protected AccountService $accountService;
    protected UtilityService $utilityService;
    protected ItemRepositoryInterface $itemRepository;
    protected array $allowKey = [
        'category',
        'brand',
        'brand_list',
        'min_price',
        'max_price',
        'title',
        'color',
        'size',
        'categories',
        'category_list',
        'colors',
        'special_suggest',
        'shed_colors',
        'min_height',
        'max_height',
        'min_width',
        'max_width',
        'max_diagonal',
        'min_diagonal',
        'min_flames_count',
        'max_flames_count',
        'flames_count',
        'product_middle_section',
        'product_trend',
        'product_popular',
        'product_new',
        'product_special',
        'target-muscles',
        'type-muscles',
        'activity-types',
    ];

    protected array $config;

    public function __construct(
        ItemRepositoryInterface $itemRepository,
        AccountService          $accountService,
        UtilityService          $utilityService,
        $config
    ) {
        $this->itemRepository = $itemRepository;
        $this->accountService = $accountService;
        $this->utilityService = $utilityService;
        $this->config = $config;
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

    public function getItemFilter($where): array
    {
        $item = $this->itemRepository->getItemFilter($where);
        return $this->canonizeItem($item);
    }

    public function prepareFilter($params): array
    {
        $filters = [];
        foreach ($params as $key => $value) {
            if (in_array($key, $this->allowKey)) {
                switch ($key) {
                    case 'color':
                    case 'size':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => $key,
                                'value' => explode(',', $value),
                                'type' => 'string',
                            ];
                        break;

                    case 'brand':
                        $filters[$key] = [
                            'meta_key' => $key,
                            'value' => $value,
                            'type' => 'id',
                        ];
                        break;

                    case 'max_price':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'price',
                                'value' => $value,
                                'type' => 'rangeMax',
                            ];
                        break;

                    case 'min_price':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'price',
                                'value' => $value,
                                'type' => 'rangeMin',
                            ];
                        break;
                    case 'min_height':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'height',
                                'value' => $value,
                                'type' => 'rangeMin',
                            ];
                        break;
                    case 'max_height':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'height',
                                'value' => $value,
                                'type' => 'rangeMax',
                            ];
                        break;
                    case 'min_width':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'width',
                                'value' => $value,
                                'type' => 'rangeMin',
                            ];
                        break;
                    case 'max_width':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'width',
                                'value' => $value,
                                'type' => 'rangeMax',
                            ];
                        break;
                    case 'max_diagonal':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'diagonal',
                                'value' => $value,
                                'type' => 'rangeMax',
                            ];
                        break;
                    case 'min_diagonal':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'diagonal',
                                'value' => $value,
                                'type' => 'rangeMin',
                            ];
                        break;
                    case 'max_flames_count':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'flames-count',
                                'value' => $value,
                                'type' => 'rangeMax',
                            ];
                        break;
                    case 'min_flames_count':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'flames-count',
                                'value' => $value,
                                'type' => 'rangeMin',
                            ];
                        break;
                    case 'special_suggest':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'special-suggest',
                                'value' => $value,
                                'type' => 'slug',
                            ];
                        break;
                    case 'product_middle_section':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'product-middle-section',
                                'value' => $value,
                                'type' => 'slug',
                            ];
                        break;
                    case 'product_trend':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'product-trend',
                                'value' => $value,
                                'type' => 'slug',
                            ];
                        break;
                    case 'product_popular':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'product-popular',
                                'value' => $value,
                                'type' => 'slug',
                            ];
                        break;
                    case 'product_new':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'product-new',
                                'value' => $value,
                                'type' => 'slug',
                            ];
                        break;
                    case 'product_special':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'product-special',
                                'value' => $value,
                                'type' => 'slug',
                            ];
                        break;
                    case 'flames_count':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'flames-count',
                                'value' => $value,
                                'type' => 'int',
                            ];
                        break;
                    case 'categories':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'category',
                                'value' => explode(',', $value),
                                'type' => 'slug',
                            ];
                        break;
                    case 'category_list':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'category',
                                'value' => $value,
                                'type' => 'slug',
                            ];
                        break;
                    case 'brand_list':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'brand',
                                'value' => $value,
                                'type' => 'slug',
                            ];
                        break;
                    case 'colors':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'color',
                                'value' => explode(',', $value),
                                'type' => 'slug',
                            ];
                        break;
                    case 'shed_colors':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'shed_color',
                                'value' => explode(',', $value),
                                'type' => 'slug',
                            ];
                        break;
                    case 'target-muscles':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'target-muscles',
                                'value' => $value,
                                'type' => 'slug',
                            ];
                        break;
                    case 'activity-types':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'activity-types',
                                'value' => $value,
                                'type' => 'slug',
                            ];
                        break;
                    case 'type-muscles':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'meta_key' => 'type-muscles',
                                'value' => $value,
                                'type' => 'slug',
                            ];
                        break;
                }
            }
        }
        return $filters;
    }

    public function editItem($params, $account = null)
    {
        if (!isset($params["time_update"])) {
            $params["time_update"] = time();
        }
        return $this->itemRepository->editItem($params);
    }

    public function addItem($params, $account)
    {
        return $this->canonizeItem($this->itemRepository->addItem($params));
    }

    public function deleteItem($params, $account)
    {
        $params["time_delete"] = time();
        $params["status"] = 0;
        return $this->itemRepository->deleteItem($params, $account);
    }

    public function destroyItem($params): void
    {
        $this->itemRepository->destroyItem($params);
    }

    public function updateItemMeta(array $params)
    {
        $item = $this->itemRepository->getItem($params['id'], 'id');
        $information = json_decode($item->getInformation(), true);
        $information['meta'][$params['meta_key']] = $params['meta_value'];
        $editedMeta = [
            'id' => (int)$item->getId(),
            'time_update' => time(),
            'information' => json_encode($information, JSON_UNESCAPED_UNICODE),
        ];
        $newInformationObject = json_decode($this->itemRepository->editItem($editedMeta)->getInformation(), true);
        $newInformationObject['id'] = (int)$item->getId();

        if (str_contains($item->getTitle(), 'child_slug_')) {
            $parent = $this->itemRepository->getItem(str_replace("child_slug_", "", $item->getTitle()), 'slug');
            $oldInformation = json_decode($parent->getInformation(), true);
            $i = 0;
            foreach ($oldInformation["body"]["answer"] as $answer) {
                if (isset($answer["id"])) {
                    if ($answer["id"] == (int)$item->getId()) {
                        $oldInformation["body"]["answer"][$i] = $newInformationObject;
                    }
                }
                $i++;
            }

            $editedParent = [
                "id" => (int)$parent->getId(),
                "time_update" => time(),
                "information" => json_encode($oldInformation, JSON_UNESCAPED_UNICODE),
            ];
            $this->itemRepository->editItem($editedParent);
        }
    }

    public function getMarks($params, $account): array
    {
        $limit = (int)$params['limit'] ?? 1000;
        $page = (int)$params['page'] ?? 1;
        $order = $params['order'] ?? ['time_create DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;

        $listParams = [
            'page' => $page,
            'limit' => $limit,
            'order' => $order,
            'offset' => $offset,
            'type' => "location",
        ];

        $list = [];
        $rowSet = $this->itemRepository->getItemList($listParams);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeItem($row);
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
}
