<?php

namespace Content\Service;

use Content\Repository\ItemRepositoryInterface;
use mysql_xdevapi\Exception;
use Pi\User\Service\AccountService;
use Pi\Core\Service\UtilityService;
use function explode;
use function in_array;
use function is_object;
use function json_decode;

class ItemService implements ServiceInterface
{


    /** @var AccountService */
    protected AccountService $accountService;

    /** @var UtilityService */
    protected UtilityService $utilityService;

    /* @var ItemRepositoryInterface */
    protected ItemRepositoryInterface $itemRepository;
    protected array $allowKey
        = [
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
            'min_price',
            'max_price',
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

    // ToDo: get it from DB and cache

    /* @var array */
    protected array $config;

    /**
     * @param ItemRepositoryInterface $itemRepository
     */
    public function __construct(
        ItemRepositoryInterface $itemRepository,
        AccountService          $accountService,
        UtilityService          $utilityService,
        $config
    )
    {
        $this->itemRepository = $itemRepository;
        $this->accountService = $accountService;
        $this->utilityService = $utilityService;
        $this->config = $config;
    }


    /**
     * @param array $params
     *
     * @return array
     */
    public function getItemList(array $params): array
    {
        ///TODO:update limit count
        $limit = $params['limit'] ?? 125;
        $page = $params['page'] ?? 1;
        $order = $params['order'] ?? ['priority desc', 'id desc'];
        $offset = ($page - 1) * $limit;

        // Set filters
        $filters = $this->prepareFilter($params);

        // Set params
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


        // Get count
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


    /**
     * @param array $params
     *
     * @return array
     */
    public function getCartList(array $params): array
    {
        $limit = $params['limit'] ?? 25;
        $page = $params['page'] ?? 1;
        $order = $params['order'] ?? ['time_create DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;


        // Set params
        $params['order'] = $order;
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $params['status'] = 1;


        $rowSet = $this->itemRepository->getItemList($params);
        foreach ($rowSet as $row) {
            ///TODO: review this codes
//            $list[] = $this->canonizeItem($row);
            $list = $this->canonizeCartItem($row);
        }


        $count = $this->itemRepository->getItemCount($params);

        return [
            'result' => true,
            'data' => [
                'list' => $list ?? [],
                'paginator' => [
                    'count' => $count,
                ],
            ],
            'error' => [],
        ];
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function getCart(array $params): array
    {
        $limit = $params['limit'] ?? 25;
        $page = $params['page'] ?? 1;
        $order = $params['order'] ?? ['time_create DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;


        // Set params
        $params['order'] = $order;
        $params['offset'] = $offset;
        $params['limit'] = $limit;
//        $params['type'] = $params['type'];
        $params['status'] = 1;

        $list = [];
        $rowSet = $this->itemRepository->getItemList($params);
        foreach ($rowSet as $row) {
            $list = $this->canonizeItem($row);
        }
        return $list;
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

    /**
     * @param object|array $item
     *
     * @return array
     */
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
//            $data['price'] = $this->calculateTotalPrice($data);
//            $data['price_view'] = number_format($data['price']) . " تومان";;
//            $data['stock_status'] = 1;
//            $data['stock_status_view'] = 'موجود در انبار';
            $data['thumbnail'] = $data['image'];
        }
        ///TODO:resolve this
        $data['time_create_view'] = $this->utilityService->date($item['time_create']);
        $data['id'] = $item['id'];
        if (isset($data['image']))
            if (!isset($data['thumbnail']))
                $data['thumbnail'] = $data['image'];
        return $data;
    }

    public function canonizeCartItem(object|array $item, $type = 'global'): array
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
            ];
        }

        $data = !empty($item['information']) ? json_decode($item['information'], true) : [];

        switch ($type) {
            case 'tour':
//                $data['cost_dollar'] = 670;
//                $data['cost_dollar_view'] = '670 دلار';
                break;
            case 'product':
//                $data['price'] = $this->calculateTotalPrice($data);
//                $data['price_view'] = number_format($data['price']) . " تومان";;
//                $data['stock_status'] = 1;
//                $data['stock_status_view'] = 'موجود در انبار';
        }
        // Set information
        return $data;
    }

    /**
     * @param string $parameter
     * @param string $type
     *
     * @return array
     */
    public function getItem(string $parameter, string $type = 'id', $params = []): array
    {
        $item = $this->itemRepository->getItem($parameter, $type, $params);
        return $this->canonizeItem($item, (isset($params['type'])) ? $params['type'] : 'global');
    }


    /**
     * @param string $parameter
     * @param string $type
     *
     * @return array
     */
    public function getItemFilter($where): array
    {
        $item = $this->itemRepository->getItemFilter($where);
        return $this->canonizeItem($item);
    }

    public function prepareFilter($params): array
    {
        // Set filter list
        $filters = [];
        foreach ($params as $key => $value) {
            if (in_array($key, $this->allowKey)) {
                // TODO: get this info from DB
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
//                    case 'category':
                        $filters[$key] = [
                            'meta_key' => $key,
                            'value' => $value,
                            'type' => 'id',
                        ];
                        break;

                    /*case 'title':
                            $filters[$key] = [
                                'key' => $key,
                                'value' => $value,
                                'type' => 'search',
                            ];
                            break;*/

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

    // TODO: update it
    public function editItem($params, $account = null)
    {
        if (!isset($params["time_update"])) {
            $params["time_update"] = time();
        }
        return $this->itemRepository->editItem($params);
    }

    // TODO: update it
    public function addItem($params, $account)
    {
        return $this->canonizeItem($this->itemRepository->addItem($params));
    }

    // TODO: update it
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

    ///TODO: update it
    public function addCartItem($params, $account)
    {
        $product = $this->getItem($params["information"], "slug");
        $cart = $this->getItemFilter(["type" => "cart", "user_id" => $account["id"]]);
        $product["count"] = (int)$params["count"];

        $orderProperty = json_decode($params["property"], true);
        /// replace order property with original property (
        if (isset($params['property'])) {
            if (isset($product['property'])) {
                $originalProperty = $product["property"];
                $product['property'] = $orderProperty;
                if (isset($orderProperty['meta_selected_data'])) {
                    if (!empty($orderProperty['meta_selected_data'])) {
                        $product["property"]['meta_selected_data'] = $originalProperty[$orderProperty['meta_selected']['meta_key'] . '-' . $orderProperty['meta_selected']['meta_value']];
                    }
                }
            } else {
                $product['property'] = $orderProperty;
            }
        }


        $product["count"] = (int)$params["count"];
        $product["cart_slug"] = uniqid();
        if (sizeof($cart) == 0) {
            $param = [
                "id" => null,
                "title" => "cart",
                "slug" => "cart-{$account["id"]}",
                "type" => "cart",
                "status" => 1,
                "user_id" => $params["user_id"],
                "information" => json_encode([$product]),
            ];
            $this->itemRepository->addCartItem($param);
        } else {
            $index = $this->checkKeyValueInArray($cart, $params['cart_slug'], 'cart_slug');

            if ($index > -1 && (json_decode($params['property'], true) == $cart[$index]['property'])) {
                $cart[$index]["count"] = (int)$cart[$index]["count"] + (int)$params["count"];
            } else {
                $cart[] = $product;
            }


            $param = [
                "id" => null,
                "title" => "cart",
                "slug" => "cart-{$account["id"]}",
                "type" => "cart",
                "status" => 1,
                "user_id" => $params["user_id"],
                "information" => json_encode($cart),
            ];

            $this->itemRepository->destroyItem(["type" => "cart", "user_id" => $params["user_id"]]);
            $this->itemRepository->addCartItem($param);
        }
    }

    // ToDo: update it
    public function updateCart($params, $account)
    {
        $product = $this->getItem($params["slug"], "slug");
        $cart = $this->getItemFilter(["type" => "cart", "user_id" => $account["id"]]);
        $product["count"] = (int)$params["count"];

//        $index = $this->checkObjectInArray($cart, $product);
        $index = $this->checkKeyValueInArray($cart, $params["cart_slug"], 'cart_slug');

        if ($index > -1) {
            $cart[$index]["count"] = $params["count"];
        } else {
            $product['cart_slug'] = uniqid();
            $cart[] = $product;
        }
        $param = [
            "id" => null,
            "title" => "cart",
            "slug" => "cart-{$account["id"]}",
            "type" => "cart",
            "status" => 1,
            "user_id" => $params["user_id"],
            "information" => json_encode($cart),
        ];

        $this->itemRepository->destroyItem(["type" => "cart", "user_id" => $params["user_id"]]);
        $this->itemRepository->addCartItem($param);
    }

    // ToDo: update it
    public function deleteCartItem($params, $account)
    {
        $product = $this->getItem($params["slug"], "slug");
        $cart = $this->getItemFilter(["type" => "cart", "user_id" => $account["id"]]);
        if (sizeof($cart) < 2) {
            $this->itemRepository->destroyItem(["type" => "cart", "user_id" => $account["id"]]);
        } else {
//            $index = $this->checkObjectInArray($cart, $product);
            $index = $this->checkKeyValueInArray($cart, $params["cart_slug"], 'cart_slug');
            if ($index > -1) {
                unset($cart[$index]);
                $cart = array_values($cart);
            } else {
                $cart[] = [];
            }
            $param = [
                "id" => null,
                "title" => "cart",
                "slug" => "cart-{$account["id"]}",
                "type" => "cart",
                "status" => 1,
                "user_id" => $params["user_id"],
                "information" => json_encode($cart),
            ];

            $this->itemRepository->destroyItem(["type" => "cart", "user_id" => $account["id"]]);
            if (sizeof($cart)) {
                $this->itemRepository->addCartItem($param);
            }
        }
    }

    private function checkObjectInArray(array $array, array $object, $key = "id")
    {
        $index = -1;
        foreach ($array as $item) {
            $index++;
            if ($item[$key] == $object[$key]) {
                return $index;
            }
        }
        return $index;
    }

    private function checkKeyValueInArray(array $array, $value, $key = 'id')
    {
        $index = -1;
        foreach ($array as $item) {
            $index++;
            if ($item[$key] == $value) {
                return $index;
            }
        }
        return $index;
    }


    ///TODO: update it/ remove from content module
    public function addOrderItem($params, $account): array
    {

        $limit = $params['limit'] ?? 25;
        $page = $params['page'] ?? 1;
        $order = $params['order'] ?? ['time_create DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;
        $cart_request = [
            "user_id" => $account["id"],
            "type" => "cart",
            "order" => $order,
            "offset" => $offset,
            "limit" => $limit,
            "status" => 1,

        ];
        $cart = $this->getCart($cart_request);

        if (sizeof($cart) == 0)
            return [];

        $items = [];
        $index = 0;
        foreach ($cart as $item) {
            $items[$index] = $this->getItem($item['slug'], 'slug', ['type' => 'product']);
            $items[$index]['count'] = $item['count'];
            $items[$index]['property'] = $item['property'];
            $index++;
        }


        ///TODO: get address from database from old address if send address_id
//        if (!isset($params["address_id"]) || $params["address_id"] == null || $params["address_id"] == "null") {
        $address = [
            "id" => null,
            "name" => $params["name"],
            "phone" => $params["phone"],
            "address" => $params["address"],
            "state" => $params["state"],
            "city" => $params["city"],
            "zip_code" => $params["zip_code"],
            "description" => $params["description"],
            "day" => $params["day"],
            "time" => $params["time"],
        ];
        $address_request = [
            "type" => "address",
            "slug" => "address-{$account["id"]}-" . time(),
            "user_id" => $account["id"],
            "status" => 1,
            "title" => "address-{$account["id"]}",
            "information" => json_encode($address),
        ];
        $address["id"] = $this->addItem($address_request, $account)->getId();
        $total_price = 0;
        // Calculate total price for each product
        foreach ($items as $product) {
            $total_price += $this->calculateTotalPrice($product);
        }


        $order_information = [
            "user_id" => $account["id"],
            "status" => "created",
            "date_time" => date('m/d/Y h:i', time()),
            "description" => $params["description"],
            "items" => $items,
            "total_price" => $total_price,
            "address" => $address,
        ];

        $slug = "order-{$account["id"]}-" . time();
        $order_information['slug'] = $slug;

        $order_request = [
            "type" => "module_order",
            "slug" => $slug,
            "user_id" => $account["id"],
            "status" => 1,
            "title" => "order-{$account["id"]}",
            "information" => json_encode($order_information),
        ];

        $this->itemRepository->destroyItem(["type" => "cart", "user_id" => $account["id"]]);
        return $this->canonizeItem($this->addItem($order_request, $account));
//        }
    }

    public function calculateTotalPrice($product): float|int
    {
        $metaList = $product["meta"] ?? [];
        if (isset($product['property'])) {
            if (isset($product['property']['meta_selected_data'])) {
                $metaList = $product['property']['meta_selected_data'];
            }
        }
        foreach ($metaList as $meta) {
            if ($meta["meta_key"] == "price") {
                return $meta["meta_value"] * (int)((isset($product["count"])) ? $product["count"] : 1);
            }
        }
        return 0; // Return 0 if no valid price found
    }




///// Start Question Section /////
    /// services of question type

    public function getGroupItem($params, $type = "id"): array
    {
        $list = [];
        $params = "'" . str_replace(',', "','", $params) . "'";
        $rowSet = $this->itemRepository->getGroupList($params, $type);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeItem($row);
        }
        return $list;
    }


    // TODO: update it
    public function addQuestion($requestBody): object|array
    {
        $nullObject = [];// new \stdClass();

        $hasCategories = isset($requestBody['categories']);
        $categoryKeyType = $requestBody['category_key_type'] ?? 'id';

        $params = [
            'user_id' => $requestBody['user_id'] ?? 0,
            'title' => $requestBody['title'],
            'slug' => uniqid(),
            'status' => 1,
            'type' => $requestBody['type'] ?? 'question',
            'time_create' => time(),
        ];

        $information = $params;
        $information['time_created_view'] = $this->utilityService->date($params['time_create']);
        $information['extra'] = isset($requestBody['extra']) ? json_decode($requestBody['extra']) : new \stdClass();
        $information['body'] = $nullObject;
        $information['body']['user'] = $params['user_id'] == 0 ? $nullObject : $this->accountService->getProfile($params);
        $information['body']['name'] = $params['user_id'] == 0 ? ''
            : $this->accountService->getProfile($params)["first_name"] . ' ' . $this->accountService->getProfile($params)["last_name"];
        $information['body'] ['description'] = $requestBody['description'] ?? '';
        $information['body']['answer'] = $nullObject;
        $information['meta'] = $nullObject;
        $information['meta']['categories'] = $hasCategories ? $this->getGroupItem($requestBody['categories'], $categoryKeyType) : [];
        $information['meta']['like'] = 0;
        $information['meta']['dislike'] = 0;
        $params['information'] = json_encode($information, JSON_UNESCAPED_UNICODE);

        $question = $this->itemRepository->addItem($params);
        $information = $this->canonizeItem($question);
        $information["id"] = (int)$question->getId();
        $editedQuestion = [
            "id" => (int)$question->getId(),
            "time_update" => time(),
            "information" => json_encode($information, JSON_UNESCAPED_UNICODE),
        ];

        $question = $this->itemRepository->editItem($editedQuestion);

        // add meta record for this question if isset categories parameter
        if (isset($requestBody['categories'])) {
            $metaParams = [
                'item_id' => $question->getId(),
                'meta_key' => 'category',
                'time_create' => time(),
            ];

            $categories = explode(',', $requestBody['categories']);
            foreach ($categories as $category) {
                $metaParams['value_id'] = $category;
                $this->itemRepository->addMetaValue($metaParams);
            }
        }

        return $this->canonizeItem($question);
    }

    public function replyQuestion($params): object|array
    {
        $nullObject = [];// new \stdClass();
        $hasCategories = isset($params['categories']);

        $answer = [
            "user_id" => $params['user_id'] ?? 0,
            "title" => $params['question_slug'],
            "slug" => $params['slug'],
            "status" => 1,
            "type" => $requestBody['type'] ?? 'answer',
            'time_create' => time(),
        ];

        $answerInformation = $answer;
        $answerInformation['time_created_view'] = $this->utilityService->date($answer['time_create']);
        $answerInformation['title'] = $params['title'];
        $information['meta']['categories'] = $hasCategories ? $this->canonizeItem($this->itemRepository->getItem($requestBody['categories'], 'id')) : [];
        $answerInformation['meta']['like'] = 0;
        $answerInformation['meta']['dislike'] = 0;


        $answer["information"] = json_encode($answerInformation, JSON_UNESCAPED_UNICODE);
        $answer = $this->itemRepository->addItem($answer);


        $params["user"] = $params["user_id"] == 0 ? $nullObject : $this->accountService->getProfile($params);
        $question = $this->itemRepository->getItem(str_replace("child_slug_", "", $params["question_slug"]), "slug");


        $information = $this->canonizeItem($question);
        if (sizeof($information) == 0) {
            return [];
        }

        $answerInformation["id"] = (int)$answer->getId();
        array_unshift($information["body"]["answer"], $answerInformation);
        $editedQuestion = [
            "id" => (int)$question->getId(),
            "time_update" => time(),
            "information" => json_encode($information, JSON_UNESCAPED_UNICODE),
        ];

        return $this->canonizeItem($this->itemRepository->editItem($editedQuestion));
    }

    ///// End Question Section /////


    // TODO: update it
    public function addSupport($requestBody, $notificationTypes = []): object|array
    {
        $nullObject = [];// new \stdClass();

        $hasCategories = isset($requestBody['categories']);
        $categoryKeyType = $requestBody['category_key_type'] ?? 'id';
        $item_slug = uniqid();
        $params = [
            'user_id' => $requestBody['user_id'] ?? 0,
            'title' => $requestBody['title'],
            'slug' => $item_slug,
            'status' => 1,
            'type' => $requestBody['type'] ?? 'question',
            'time_create' => time(),
        ];

        $information = $params;
        $information['time_created_view'] = $this->utilityService->date($params['time_create']);
        $information['extra'] = isset($requestBody['extra']) ? json_decode($requestBody['extra']) : new \stdClass();
        $information['body'] = $nullObject;
        $information['body']['user'] = $params['user_id'] == 0 ? $nullObject : $this->accountService->getProfile($params);
        $information['body']['name'] = $params['user_id'] == 0 ? ''
            : $this->accountService->getProfile($params)["first_name"] . ' ' . $this->accountService->getProfile($params)["last_name"];
        $information['body'] ['description'] = $requestBody['description'] ?? '';
        $information['body']['answer'] = $nullObject;
        $information['meta'] = $nullObject;
        $information['meta']['categories'] = $hasCategories ? $this->getGroupItem($requestBody['categories'], $categoryKeyType) : [];
        $information['meta']['like'] = 0;
        $information['meta']['dislike'] = 0;
        $params['information'] = json_encode($information, JSON_UNESCAPED_UNICODE);

        $support = $this->itemRepository->addItem($params);
        $information = $this->canonizeItem($support);
        $information["id"] = (int)$support->getId();
        $editedSupport = [
            "id" => (int)$support->getId(),
            "time_update" => time(),
            "information" => json_encode($information, JSON_UNESCAPED_UNICODE),
        ];

        $support = $this->itemRepository->editItem($editedSupport);

        // add meta record for this question if isset categories parameter
        if (isset($requestBody['categories'])) {
            $metaParams = [
                'item_id' => $support->getId(),
                'value_slug' => $item_slug,
                'meta_key' => 'category',
                'time_create' => time(),
            ];

            $categories = explode(',', $requestBody['categories']);
            foreach ($categories as $category) {
                $metaParams['value_id'] = $category;
                $this->itemRepository->addMetaValue($metaParams);
            }
        }

        if ($requestBody['categories'] == 'course') {

            $course = json_decode($requestBody['extra'], true);
            //user side
            $email = [
                'to' => [
                    'email' => $this->accountService->getAccount(['id' => $requestBody['user_id']])['email'],
                    'name' => $this->config['admin']['name'],
                ],
                'subject' => $this->config['admin']['subject'],
                'body' => sprintf($this->config['admin']['template']['course'],
                    $course['course_title'],
                    $course['thumbnail'],
                    $course['course_title'],
                    $course['course_schedule'],
                    ($course['course_fee'] + ($course['course_fee'] * 0.09)),
                    "ثبت نام شما بعد از پرداخت هزینه دوره نهایی خواهد شد.",
                    $this->config['admin']['template']['logo']
                ),
            ];
            $this->sendNotification(
                ['email'],
                [
                    'email' => $email,
                ],

                ''
            );
            $email = [
                'to' => [
                    'email' => $this->config['admin']['email'],
                    'name' => $this->config['admin']['name'],
                ],
                'subject' => $this->config['admin']['subject'],
                'body' => sprintf($this->config['admin']['template']['admin'],
                    $course['course_title'],
                    $course['thumbnail'],
                    $course['course_title'],
                    $course['course_schedule'] . ' ' . $course['course_fee_view'],
                    ($course['course_fee'] + ($course['course_fee'] * 0.09)),
                    $information['body']['user']['first_name'],
                    $information['body']['user']['last_name'],
                    $information['body']['user']['phone'],
                    $this->accountService->getAccount(['id' => $requestBody['user_id']])['email'],
                    $information['time_created_view'],
                    $this->config['admin']['template']['logo']
                ),
            ];
            $this->sendNotification(
                ['email'],
                [
                    'email' => $email,
                ],

                ''
            );

        } else {
            $email = [
                'to' => [
                    'email' => $this->config['admin']['email'],
                    'name' => $this->config['admin']['name'],
                ],
                'subject' => "Support",
                'body' => sprintf(
                    "
                        <p style='text-align: right;direction: rtl' dir='rtl'>
                        درخواست پشتیبانی با شناسه %s از نوع %s , در تاریخ %s , در سامانه ثبت شد.
                        <br/>
                        مشخصات کاربر : %s
                        </p>
                        ",
                    $support->getId(),
                    $requestBody['categories'],
                    $information['time_created_view'],
                    $information['body']['name']
                ),
            ];
            $this->sendNotification(
                ['email'],
                [
                    'email' => $email,
                ],

                ''
            );
        }


        return $this->canonizeItem($support);
    }

    public function replySupport($params, $notificationTypes): object|array
    {
        $support = $this->itemRepository->getItem(str_replace("child_slug_", "", $params["support_slug"]), "slug");
        if ($support == null) {
            return [];
        }

        $nullObject = [];// new \stdClass();
        $hasCategories = isset($params['categories']);

        $answer = [
            "user_id" => $params['user_id'] ?? 0,
            "title" => $params['support_slug'],
            "slug" => $params['slug'],
            "status" => 1,
            "type" => $params['type'] ?? 'answer',
            'time_create' => time(),
        ];

        $answerInformation = $answer;
        $answerInformation['time_created_view'] = $this->utilityService->date($answer['time_create']);
        $answerInformation['user'] = $params['user_id'] == 0 ? $nullObject : $this->accountService->getProfile($params);
        $answerInformation['name'] = $params['user_id'] == 0 ? ''
            : $this->accountService->getProfile($params)["first_name"] . ' ' . $this->accountService->getProfile($params)["last_name"];
        $answerInformation['title'] = $params['title'];
        $information['meta']['categories'] = $hasCategories ? $this->canonizeItem($this->itemRepository->getItem($params['categories'], 'id')) : [];
        $answerInformation['meta']['like'] = 0;
        $answerInformation['meta']['dislike'] = 0;

        $answer["information"] = json_encode($answerInformation, JSON_UNESCAPED_UNICODE);
        $answer = $this->itemRepository->addItem($answer);


        $params["user"] = $params["user_id"] == 0 ? $nullObject : $this->accountService->getProfile($params);


        $information = $this->canonizeItem($support);
        if (sizeof($information) == 0) {
            return [];
        }

        $answerInformation["id"] = (int)$answer->getId();
        array_unshift($information["body"]["answer"], $answerInformation);
        $editedSupport = [
            "id" => (int)$support->getId(),
            "time_update" => time(),
            "information" => json_encode($information, JSON_UNESCAPED_UNICODE),
        ];

        if (sizeof($notificationTypes) > 0) {
            $userAccount = $this->accountService->getAccount(['id' => $information['user_id']]);
            $this->sendNotification($notificationTypes, $userAccount, $params['title']);
        }


//        return $this->accountService->getAccount(['id' => $information['user_id']]);
        return $this->canonizeItem($this->itemRepository->editItem($editedSupport));
    }


    /**
     * @param string $parameter
     * @param string $type
     *
     * @return array
     */
    public function getSupport(string $parameter, string $type = 'id', $params = []): array
    {

        $item = $this->itemRepository->getItem($parameter, $type, $params);

        $limit = $params['limit'] ?? 1000;
        $page = $params['page'] ?? 1;
        $order = $params['order'] ?? ['time_create DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;

        $item = $this->canonizeItem($item);

        ///TODO: get answers from db
        if (sizeof($item) > 0) {
            // Set params
            $listParams = [
                'page' => $page,
                'limit' => $limit,
                'order' => $order,
                'offset' => $offset,
                'type' => "answer",
                'title' => 'child_slug_' . $item['slug'],
            ];
            $item['body']['answer'] = [];
            $answers = $this->itemRepository->getItemList($listParams);
            foreach ($answers as $answer) {
                $item['body']['answer'][] = $this->canonizeItem($answer);
            }
        }


        return $item;
    }

    ///// Start Location Section /////
    ///// services of location type
    ///
    public function getMarks($params, $account): array
    {
        $limit = (int)$params['limit'] ?? 1000;
        $page = (int)$params['page'] ?? 1;
        $order = $params['order'] ?? ['time_create DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;

        // Set params
        $listParams = [
            'page' => $page,
            'limit' => $limit,
            'order' => $order,
            'offset' => $offset,
            'type' => "location",
        ];

        //TODO: set filters OR Merge this service with getItemList
        $filters = $this->prepareFilter($params);

        $scores = [];
        foreach ($this->scoreService->getScoreListGroupByItem() as $score) {
            $scores[$score["item_id"]] = $score;
        }
        // Get list
        $list = [];
        $rowSet = $this->itemRepository->getItemList($listParams);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeItem($row);
        }


        $reserves = $this->getReserveList([], $account);
        $sortedReserves = [];
        if (!empty($reserves)) {
            foreach ($reserves as $reserve) {
                $sortedReserves[$reserve['item_id']][] = $reserve;
            }

        }

        $packages = $this->scoreService->getCustomList([]);
        $sortedPackages = [];
        if (!empty($packages)) {
            if (isset($packages['data']['list'])) {
                $packageList = $packages['data']['list'];
                if (!empty($packageList)) {
                    foreach ($packageList as $package) {
                        $sortedPackages[$package['item_id']][] = $package;
                    }
                }
            }
        }

        $ll = [];
        for ($i = 0; $i < sizeof($list); $i++) {
            $ll[$i] = $list[$i];
            $ll[$i]["score"] = isset($scores[$ll[$i]["id"]]) ? $scores[$ll[$i]["id"]]["score"] : 0;
            $ll[$i]["has_reserve"] = isset($sortedReserves[$list[$i]['id']]) && !empty($sortedReserves[$list[$i]['id']]);
            $ll[$i]["reserves"] = $sortedReserves[$list[$i]['id']] ?? [];
            $ll[$i]["has_package"] = isset($sortedPackages[$list[$i]['id']]) && !empty($sortedPackages[$list[$i]['id']]);
            $ll[$i]["packages"] = $sortedPackages[$list[$i]['id']] ?? [];
            $ll[$i]["classification"] = $this->calculateClassification((int)$ll[$i]["score"]);
        }

        // Get count
        $count = $this->itemRepository->getItemCount($listParams);

        return [
            'result' => true,
            'data' => [
                'list' => $ll,
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

    public function getMark(array $params, $account): array|object
    {
        $item = $this->itemRepository->getItem($params['slug'], 'slug');
        $item = $this->canonizeItem($item);
        $scores = [];
        foreach ($this->scoreService->getScoreListGroupByItem() as $score) {
            $scores[$score["item_id"]] = $score;
        }
        $item["score"] = isset($scores[$item["id"]]) ? $scores[$item["id"]]["score"] : 0;
        $item["classification"] = $this->calculateClassification((int)$item["score"]);

        $reserves = $this->getReserveList([], $account);
        $sortedReserves = [];
        if (!empty($reserves)) {
            foreach ($reserves as $reserve) {
                $sortedReserves[$reserve['item_id']][] = $reserve;
            }
        }

        $packages = $this->scoreService->getCustomList([]);
        $sortedPackages = [];
        if (!empty($packages)) {
            if (isset($packages['data']['list'])) {
                $packageList = $packages['data']['list'];
                if (!empty($packageList)) {
                    foreach ($packageList as $package) {
                        $sortedPackages[$package['item_id']][] = $package;
                    }
                }
            }
        }

        $item["score"] = isset($scores[$item["id"]]) ? $scores[$item["id"]]["score"] : 0;
        $item["has_reserve"] = isset($sortedReserves[$item['id']]) && !empty($sortedReserves[$item['id']]);
        $item["reserves"] = $sortedReserves[$item['id']] ?? [];
        $item["has_package"] = isset($sortedPackages[$item['id']]) && !empty($sortedPackages[$item['id']]);
        $item["packages"] = $sortedPackages[$item['id']] ?? [];
        $item["classification"] = $this->calculateClassification((int)$item["score"]);

        return $item;
    }
    ///// End Location Section /////


    ///// Start Category Section /////
    ///// services of location type
    ///
    public function getCategories($params): array
    {
        $limit = (int)$params['limit'] ?? 1000;
        $page = (int)$params['page'] ?? 1;
        $order = $params['order'] ?? ['time_create DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;

        // Set params
        $listParams = [
            'page' => $page,
            'limit' => $limit,
            'order' => $order,
            'offset' => $offset,
            'type' => "categories",
        ];

        // Get list
        $list = [];
        $rowSet = $this->itemRepository->getItemList($listParams);
        foreach ($rowSet as $row) {
            $list = $this->canonizeItem($row);
        }
        return $list;
    }
    ///// End Category Section /////

    ///// Start Setting Section /////
    ///// Config variable of engin
    ///
    public function getVersion($params): object|array
    {
        try {
            $platform = $params["platform"];
            $config = $platform == "api"
                ?
                $this->config[$platform]
                :
                $this->config["application"][$platform];
            return [
                "status" => $config["status"],
                "last_version" => $config["last_version"],
                "url" => $config["url"],
                "is_force" => !in_array($params["version"], $config["authorized_versions"]),
                "message" => $config["message"],
                "current_version" => $params["version"],
                "button_title" => $config["button_title"],
                "title" => $config["title"],
                "stn" => $config["stn"] ?? '',

            ];
        } catch (Exception $error) {
            return [];
        }
    }

    ///// End Setting Section /////
    private function calculateClassification(mixed $score): string
    {
        if ($score < 100) {
            return 1;
        } elseif ($score < 300) {
            return 2;
        } elseif ($score < 700) {
            return 3;
        } else {
            return 4;
        }
    }


    ///// Start Reservation Section /////
    ///// Config variable of engin
    ///

    /**
     * @param array $params
     *
     * @return array
     */
    public function getReserveList(array $params, $account): array
    {
        ///TODO:update limit count
        $limit = $params['limit'] ?? 125;
        $page = $params['page'] ?? 1;
        $order = $params['order'] ?? ['time_create DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;
        if (isset($params["role"]) && $params["role"] == "owner") {
            $title = "reservation_owner_" . $this->accountService->getProfile(["user_id" => $account["id"]])["item_id"] . "_";
        } else {
            $title = "reservation_customer_" . $account["id"] . "_";
        }


        // Set params
        $listParams = [
            'order' => $order,
            'offset' => $offset,
            'limit' => $limit,
            'type' => $params['type'] ?? 'reservation',
            'status' => 1,
            'title' => $title,
        ];

        // Get list
        $list = [];
        $rowSet = $this->itemRepository->getItemList($listParams);

        foreach ($rowSet as $row) {
            $list[] = $this->canonizeItem($row);
        }

        return $list;
    }


    ///TODO: set title for slug =>
    public function reserve(object|array|null $params, $account): array
    {
        $sameCodeFlag = false;

        ///check code is valid
        $customList = $this->scoreService->getActiveCustomList(
            [
                "item_id" => $params["item_id"],
                "code" => $params["code"]
            ]
        );

        ///TODO: set error handler for when the code or item_id is not valid
        if (sizeof($customList) == 0) {
            return ["id" => -1];
        }
        $custom = $customList[0];

        $customerTitle = $params["type"] . "_customer_" . $params["user_id"] . "_owner_" . $params["item_id"] . "_";
        $customerSlug = $params["type"] . "_customer_" . $params["user_id"] . "_" . $custom["code"];


        $ownerTitle = $params["type"] . "_owner_" . $params["item_id"] . "_";
        $ownerSlug = $customerTitle;

        $ownerReserved = $this->canonizeItem($this->itemRepository->getItem($ownerSlug, 'slug'));
        $customerReserved = $this->canonizeItem($this->itemRepository->getItem($customerTitle, 'title'));

        $expired = strtotime("+1 hour");

        $customerParam = $this->getReserveParams($customerSlug, $params, $custom["code"], $expired, $customerTitle);
        $ownerParam = $this->getReserveParams($ownerSlug, $params, $custom["code"], $expired, $ownerTitle);


        /// if array have any user with the same slug
        if (!empty($ownerReserved)) {
            if ($custom['code'] !== $customerReserved['code']) {

                /// get data of old score code from customer
                $oldCustomList = $this->scoreService->getActiveCustomList(
                    [
                        "item_id" => $params["item_id"],
                        "code" => $customerReserved["code"]
                    ]
                );
                /// remove the used report of score for this customer
                $this->scoreService->updateCustom(
                    [
                        'code' => $oldCustomList[0]['code'],
                    ],
                    [
                        'count_used' => $oldCustomList[0]['count_used'] - 1,
                    ]
                );
                $this->itemRepository->destroyItem(['slug' => $customerReserved['slug']]);
                $this->itemRepository->destroyItem(['slug' => $ownerReserved['slug']]);
            } else {
                $sameCodeFlag = true;
            }
        }


        /// check that the code sent not equal the old value
        if (!$sameCodeFlag) {
            $this->itemRepository->addItem($ownerParam);
            $this->itemRepository->addItem($customerParam);
            $this->scoreService->updateCustom(
                [
                    'code' => $custom['code'],
                ],
                [
                    'count_used' => $custom['count_used'] + 1,
                ]
            );

            /// send notification
            ///Send notification to owner
            $ownerProfile = $this->accountService->getProfile(['item_id' => $params["item_id"]]);
            if (isset($ownerProfile['user_id'])) {
                $owner = $this->accountService->getUserFromCacheFull($ownerProfile['user_id']);

                $notificationParams = [
                    'information' =>
                        [
                            "device_token" => $owner['device_tokens'],
                            "in_app" => true,
                            "in_app_title" => 'Reservation',
                            "title" => 'Reservation',
                            "in_app_body" => 'You have been reserved by a user . package code is ' . $custom['code'] . ' ',
                            "body" => 'You have been reserved by a user . package code is ' . $custom['code'] . ' ',
                            "event" => 'reservation',
                            "user_id" => (int)$ownerProfile['user_id'],
                            "item_id" => (int)$params['item_id'],
                            "sender_id" => 0,
                            "type" => 'info',
                            "image_url" => '',
                            "receiver_id" => (int)$ownerProfile['user_id'],
                        ],
                ];
                $notificationParams['push'] = $notificationParams['information'];
                $this->notificationService->send($notificationParams, 'owner');
            }


            ///Send notification to customer
            $customer = $this->accountService->getUserFromCacheFull($params["user_id"]);

            $notificationParams = [
                'information' =>
                    [
                        "device_token" => $customer['device_tokens'],
                        "in_app" => true,
                        "in_app_title" => 'Reservation',
                        "title" => 'Reservation',
                        "in_app_body" => 'You have successfully booked the ' . $custom['code'] . ' package . This reservation is only valid for one hour . ',
                        "body" => 'You have successfully booked the ' . $custom['code'] . ' package . This reservation is only valid for one hour . ',
                        "event" => 'reservation',
                        "user_id" => (int)$params["user_id"],
                        "item_id" => (int)$params['item_id'],
                        "sender_id" => 0,
                        "type" => 'info',
                        "image_url" => '',
                        "receiver_id" => (int)$params["user_id"],
                    ],
            ];
            $notificationParams['push'] = $notificationParams['information'];
            $this->notificationService->send($notificationParams, 'customer');
        }
        $params = [
            "slug" => $params["user_id"],
        ];
        return $this->getReserveList($params, $account);
    }


    /**
     * @param string $ownerSlug
     * @param object|array|null $params
     * @param $code
     * @param bool|int $expired
     * @param string $ownerTitle
     * @return array
     */
    private function getReserveParams(string $ownerSlug, object|array|null $params, $code, bool|int $expired, string $ownerTitle): array
    {
        $ownerInfo = [
            "slug" => $ownerSlug,
            "time" => date('Y / m / d H:i', time()),
            "user_id" => $params["user_id"],
            "item_id" => $params["item_id"],
            "user" => $this->accountService->getProfile(['user_id' => $params["user_id"]]),
            "item" => $this->getItem($params["item_id"], 'slug'),
            "code" => $code,
            "expired_at" => date('Y / m / d H:i', $expired),
        ];
        $ownerInfo['user']['avatar'] = 'https://cdn.seylaneh.co/general/avatar.png';

        $ownerParam = [
            "id" => null,
            "title" => $ownerTitle,
            "slug" => $ownerSlug,
            "type" => "reservation",
            "status" => 1,
            "user_id" => $params["user_id"],
            "information" => json_encode($ownerInfo),
        ];
        $ownerInfo['user']['avatar'] = 'https://cdn.seylaneh.co/general/avatar.png';
        return $ownerParam;
    }


    public function removeReserve(array $params, array $account, bool $apply = false): array
    {
        ///check code is valid
        $customList = $this->scoreService->getActiveCustomList(
            [
                "item_id" => $params["item_id"],
                "code" => $params["code"]
            ]
        );


        ///TODO: set error handler for when the code or item_id is not valid
        if (sizeof($customList) == 0) {
            return ["id" => -1];
        }
        $custom = $customList[0];

        $ownerSlug = $params["type"] . "_customer_" . $params["user_id"] . "_owner_" . $params["item_id"] . "_";
        $customerSlug = $params["type"] . "_customer_" . $params["user_id"] . "_" . $params["code"];

        $this->itemRepository->destroyItem(['slug' => $ownerSlug]);
        $this->itemRepository->destroyItem(['slug' => $customerSlug]);

        /// if this reserve removed by customer (called by remove reserve handler) - Else : this reserve removed by earn Score (called from earn score handler)
        if (!$apply) {
            $this->scoreService->updateCustom(
                [
                    'code' => $custom['code'],
                ],
                [
                    'count_used' => $custom['count_used'] - 1,
                ]
            );
        }

        return $this->getReserveList([], $account);
    }

    //// End Reservation Section /////


    public function updateItemMeta(array $params)
    {
        $item = $this->itemRepository->getItem($params['id'], 'id');
        $information = json_decode($item->getInformation(), true);
//        $information['meta'] = $nullObject;
        $information['meta'][$params['meta_key']] = $params['meta_value'];
        $editedMeta = [
            'id' => (int)$item->getId(),
            'time_update' => time(),
            'information' => json_encode($information, JSON_UNESCAPED_UNICODE),
        ];
        $newInformationObject = json_decode($this->itemRepository->editItem($editedMeta)->getInformation(), true);
        $newInformationObject['id'] = (int)$item->getId();
        /// check that this record has a parent or no
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


    /// TODO: move to  independent service
    public function getReportClubScoreList(array $params, mixed $account)
    {
        $scoreList = $this->scoreService->getScoreList($params, $account);
        $list = array();
        $canonized = array();
        if (isset($scoreList['data'])) {
            if (isset($scoreList['data']['list'])) {
                foreach ($scoreList['data']['list'] as $record) {
                    $list[] = $this->canonizeReportScoreList($record);
                }
                $scoreList['data']['list'] = $list;
            }
        }
        return $scoreList;
    }

    private function canonizeReportScoreList(mixed $record)
    {
        $user = $this->accountService->getAccount(['id' => $record['user_id']]);
        $user['avatar'] = 'https://cdn.seylaneh.co/general/avatar.png';
        $item = $this->getItem($record['item_id'], 'slug');
        $record['user'] = $user;
        $record['item'] = $item;
        return $record;
    }

    private function sendNotification($notificationTypes, array $userAccount, $title): void
    {
        foreach ($notificationTypes as $type) {
            if ($type == 'sms') {
                if (isset($userAccount['mobile']))
                    $this->notificationService->send(
                        [
                            'sms' => [
                                'message' => $title,
                                'mobile' => $userAccount['mobile'],
                                'source' => '',
                            ],
                        ]
                    );
            }
            if ($type == 'email') {
                if (isset($userAccount['email']))
                    $this->notificationService->send(
                        [
                            'email' => $userAccount['email'],
                        ]
                    );
            }
        }
    }

    /**
     * @param string $parameter
     * @param string $type
     *
     * @return array
     */
    public function getTour($params, $account): array
    {
        $item = $this->itemRepository->getItem($params[$params['parameter_type']], $params['parameter_type'], $params);
        return $this->canonizeItem($item);
    }

    public function getDestination($params, $account): array
    {
        $item = $this->itemRepository->getItem($params[$params['parameter_type']], $params['parameter_type'], $params);
        return $this->canonizeItem($item);
    }


    public function getTourismMainDashboard($params, $account): array
    {
        $new_sections = array();
        $suggest_sections = array();


        $top_sections = $this->getItemList(
            [
                'type' => 'destination',
                'order' => ['priority DESC', 'time_create DESC', 'id DESC'],
                'page' => 1,
                'limit' => 6,
            ]
        )['data']['list'];
        $top_sections_africa = $this->getItemList(
            [
                'type' => 'blog',
                'order' => ['priority DESC', 'time_create DESC', 'id DESC'],
                'page' => 1,
                'limit' => 6,
                'categories' => 'meta-category-kenya',
            ]
        )['data']['list'];

        $top_sections_india = $this->getItemList(
            [
                'type' => 'blog',
                'order' => ['priority DESC', 'time_create DESC', 'id DESC'],
                'page' => 1,
                'limit' => 6,
                'categories' => 'meta-category-india',
            ]
        )['data']['list'];


        $new_sections_caller = $this->itemRepository->getItemList(
            [
                'type' => 'tour',
                'order' => ['priority DESC', 'time_create DESC', 'id DESC'],
                'offset' => 0,
                'page' => 1,
                'limit' => 5,
                'status' => 1,
            ]
        );
        foreach ($new_sections_caller as $row) {
            $new_sections[] = $this->canonizeItem($row);
        }

        $suggest_sections_caller = $this->itemRepository->getItemList(
            [
                'type' => 'tour',
                'order' => ['priority DESC', 'time_create DESC', 'id DESC'],
                'offset' => 0,
                'page' => 1,
                'limit' => 4,
                'status' => 1,
            ]
        );
        foreach ($suggest_sections_caller as $row) {
            $suggest_sections[] = $this->canonizeItem($row);
        }
        $sliders = $this->getItem('yademan-slider-2023', 'slug');
        $jayParsedAry = [
            "sliders" => isset($sliders['banner_list']) ? $sliders['banner_list'] : [],
            "main_slider" => [
                "video" => "",
                "banner" => "https://yadapi.kerloper.com/upload/banners/home.jpg",
                "has_video" => false
            ],
            "middle_mode_banner" => [
                "title" => "آژانس طبیعت گردی یادمان",
                "abstract" => "آژانس طبیعت گردی یادمان یک شرکت گردشگری است که در زمینه برگزاری تورهای  خاص طبیعت گردی فعالیت می‌کند. این شرکت در سال 1393 در شهر تهران تاسیس شد و در حال حاضر به صورت تخصصی در زمینه برگزاری تورهای خاص طبیعت گردی داخلی و خارجی فعالیت می‌کند.",
                "button_title" => "درخواست مشاوره رایگان",
                "button_link" => "/contact-us/",
                "video" => "",
                "banner" => "https://yadapi.kerloper.com/upload/images/church-gh.jpg",
                "has_video" => false
            ],
            "top_section" => [
                "list" => $new_sections,
                "type" => "tour",
                "title" => "تورهای پرطرفدار یادمان",
                "abstract" => ""
            ],
            "blog_section" => [
                "list" => $top_sections,
                "type" => "blog",
                "title" => "ایران را بهتر بشناسید",
                "abstract" => "در اینجا چند چیز وجود دارد که به شما کمک می کند تا با این کشور منحصر به فرد آشنا شوید."
            ],
            "blog_section1" => [
                "list" => $top_sections_africa,
                "type" => "blog",
                "title" => "آفریقا را بهتر بشناسید",
                "abstract" => "آفریقا مقصدی عالی برای گردشگری است و می‌تواند تجربه‌ای فراموش‌نشدنی را برای گردشگران رقم بزند. این قاره برای علاقه‌مندان به طبیعت، حیات وحش، تاریخ و فرهنگ، مقصدی جذاب است."
            ],

            "blog_section2" => [
                "list" => $top_sections_india,
                "type" => "blog",
                "title" => "هندوستان  را بهتر بشناسید",
                "abstract" => "سفر به هندوستان می‌تواند تجربه‌ای فراموش‌نشدنی باشد. این کشور با تنوع جغرافیایی،غذا یی، تاریخی و فرهنگ غنی و طبیعت زیبا، مقصدی عالی برای گردشگران علاقه مند به تورهای خاص است."
            ],
            "middle_slider" => [
                "banners" => [
                    [
                        "url" => "https://yadapi.kerloper.com/upload/banners/banner-01.jpg",
                        "thumbnail" => "https://yadapi.kerloper.com/upload/logo-orange-no-padding.png",
                        "top_title" => "متن",
                        "title" => "عنوان اصلی تصویر",
                        "sub_title" => "متن",
                        "button_title" => "مشاهده بیشتر",
                        "button_link" => "/",
                    ],
                    [
                        "url" => "https://yadapi.kerloper.com/upload/banners/banner-02.jpg",
                        "thumbnail" => "https://yadapi.kerloper.com/upload/logo-orange-no-padding.png",
                        "top_title" => "متن",
                        "title" => "عنوان اصلی تصویر",
                        "sub_title" => "متن",
                        "button_title" => "مشاهده بیشتر",
                        "button_link" => "/",
                    ],
                    [
                        "url" => "https://yadapi.kerloper.com/upload/banners/banner-03.jpg",
                        "thumbnail" => "https://yadapi.kerloper.com/upload/logo-orange-no-padding.png",
                        "top_title" => "متن",
                        "title" => "عنوان اصلی تصویر",
                        "sub_title" => "متن",
                        "button_title" => "مشاهده بیشتر",
                        "button_link" => "/",
                    ],
                    [
                        "url" => "https://yadapi.kerloper.com/upload/banners/banner-04.jpg",
                        "thumbnail" => "https://yadapi.kerloper.com/upload/logo-orange-no-padding.png",
                        "top_title" => "متن",
                        "title" => "عنوان اصلی تصویر",
                        "sub_title" => "متن",
                        "button_title" => "مشاهده بیشتر",
                        "button_link" => "/",
                    ]
                ]
            ],
            "bottom_section" => [
                "list" => $suggest_sections,
                "type" => "tour",
                "title" => "مکان‌های پیشنهادی برای سفر بعدی شما",
                "abstract" => "انتخاب مکان سفر به عوامل مختلفی مانند علاقه‌مندی‌ها، بودجه و زمان سفر بستگی دارد. اگر شما هم قصد سفر دارید، توصیه می‌کنم قبل از سفر، تحقیقات لازم را انجام دهید و مقصدی را انتخاب کنید که مناسب شما باشد. در اینجا منتخبی از پر طرفدارترین سفرهای خاص یادمان را گردهم آوردیم."
            ],
            "freq_questions" => [
                "banner" => "https://yadapi.kerloper.com/upload/logo-orange.png",
                "questions" => [
                    [
                        "answer" => "آژانس طبیعت گردی یادمان تورهای طبیعت گردی خاص و متنوعی را برگزار می‌کند. این تورها شامل تورهای یک روزه، چند روزه، و چند هفته‌ای هستند و به مناطق مختلف ایران و جهان سفر می‌کند.",
                        "question" => "آژانس طبیعت گردی یادمان چه نوع تورهایی برگزار می‌کند؟"
                    ],
                    [
                        "answer" => "كسانی می‌توانند از برنامه‌های تورهای خاص یادمان  لذت واقعی ببرند كه از روحیه‌ی طبیعت‌گردی و هیجانی مناسبی برخوردار باشند، زیرا طبیعت‌گرد ممكن است با مسائل غیر منتظره‌ای مانند باران، برف، گرما  ویا سرما رو به‌رو شود.",
                        "question" => "شرایط شرکت در تورهای آژانس یادمان چیست؟"
                    ],
                    [
                        "answer" => "بله، آژانس یادمان تورهای طبیعت گردی و خاص را برای افراد مبتدی نیز برگزار می‌کند. این تورها با توجه به شرایط افراد مبتدی طراحی شده‌اند و در آن‌ها به نکات ایمنی لازم برای سفرهای خاص پرداخته می‌شود.",
                        "question" => "آیا آژانس یادمان تورهای خاص را برای افراد مبتدی هم برگزار می‌کند؟"
                    ],
                    [
                        "answer" => "به علت وجود هدف‌های نزدیك و مشترك میان همسفران و وجود گروه‌های سنی مختلف در تورهای طبیعت‌گردی به راحتی ارتباط متقابل میان افراد ایجاد و شانس یافتن همسفر و دوستان خوب میسر می‌شود. در واقع، تورهای طبیعت‌گردی، بسترسازِ گسترش روابط اجتماعی با افراد گوناگون است.",
                        "question" => "من می‌خواهم به تنهایی در تورهای خاص یادمان شركت نمایم، آیا از برنامه لذت خواهم برد؟"
                    ],
                    [
                        "answer" => "قبل از هر گونه اقدامی برای ثبت نام، شما پس از مطالعه سفرنامه ها و مطالب مرتبط با تورهای خاص که در صفحات اصلی وب سایت یادمان در دشترس می باشد ،اطلاعات خود را نسبت به تور انتخابی بالا می برید و همچنین می توانید از مشاوره رایگان تلفنی یادمان برای انتخاب تور مناسب بهره مند شوید و در نهایت از طریق ثبت نام در سایت و خرید یا رزرو تور خاص ، همسفر یادمان شوید. ",
                        "question" => "ثبت نام در تورهای خاص یادمان چگونه صورت می‌پذیرد؟"
                    ],
                ]
            ],
            "client_information" => [
                "weather" => "ابری",
                "location" => "تهران",
                "temperature" => "5°C",
                "weather_icon" => "https://yadapi.kerloper.com/upload/icon/cloudy.jpg"
            ],
            "bottom_slider_banner" => [
                "video" => "",
                "banner" => "https://yadapi.kerloper.com/upload/banners/banner-01.jpg",
                "has_video" => false
            ],
            "middle_slider_banner" => [
                "video" => "",
                "banner" => "https://yadapi.kerloper.com/upload/banners/banner-02.jpg",
                "has_video" => false
            ]
        ];
        return $jayParsedAry;

    }

    public function addEntity(object|array|null $request, mixed $account): array
    {


        if(!isset($request['priority'])){
            $request['priority']=null;
        }

        $request['slug'] = $request['slug'] ?? uniqid();
        $request['time_create'] = time();
        $request['status'] = $request['status'] ?? 0;
        $request['priority'] = (int)$request['priority'] ?? null;
        $request['type'] = $request['type'] ?? 'entity';
        $request['user_id'] = $account['id'] ?? 0;
        $request['body'] = isset($request['body']) ? $request['body'] : [];
        $params = [
            'user_id' => $request['user_id'],
            'title' => $request['title'],
            'slug' => $request['slug'],
            'status' => $request['status'],
            'priority' => $request['priority'],
            'type' => $request['type'],
            'time_create' => $request['time_create'],
            "information" => json_encode($request),
        ];


        $item = ($this->itemRepository->addItem($params));
        ///TODO : handel store meta key and value in meta_value table (for filter search and ...)
        if (isset($request['meta'])) {
            $request['id'] = $item->getId();
            $this->addMetaData($request);
        }
        $information = $this->canonizeItem($item);
        $information['id'] = $item->getId();
        return $this->canonizeItem($this->editItem(['id' => $item->getId(), 'information' => json_encode($information)]));
    }

    public function updateEntity(object|array|null $request, mixed $account): array
    {
        ///TODO : handel store meta key and value in meta_value table (for filter search and ...)
        $this->itemRepository->destroyMetaValue(['item_slug' => $request['slug']]);
        ///TODO: remove this . this section for old panel
        if (isset($request['mode']) && $request['mode'] != 'entity') {
            $entity = $this->getItem($request[$request['type']] ?? -1, $request['type']);
            $object = $request['body'];
            $params = [];
            if ($request['mode'] == 'body') {
                $object['id'] = time();
                $entity['body'][sizeof($entity['body'])] = $object;

                usort($entity['body'], function ($a, $b) {
                    if (is_numeric($a['index']) && is_numeric($b['index'])) {
                        if ($a['index'] === $b['index']) {
                            return $b['id'] - $a['id'];
                        }
                        return $a['index'] - $b['index'];
                    } else {
                        // Handle string indices
                        return strcmp($a['index'], $b['index']);
                    }
                });
                $params = [
                    'information' => json_encode($entity)
                ];
            }

            $params[$request['type']] = $request[$request['type']];
            if (isset($request['meta'])) {
                $request['id'] = $entity['id'] ?? 0;
                $this->addMetaData($request);
            }

        } else {
            $entity = $this->getItem($request['slug'], 'slug');

            $request['type'] = $request['type'] ?? 'entity';
            $params = [
                'title' => $request['title'],
                'information' => json_encode($request),
            ];

            if (isset($request['slug']))
                $params['slug'] = $request['slug'];
            if (isset($request['status']))
                $params['status'] = $request['status'];

            if (isset($request['priority'])) {
                $params['priority'] = $request['priority'];
            } else {
                $params['priority'] = null;
            }

            if (isset($request['meta'])) {
                $request['id'] = $entity['id'] ?? 0;
                $this->addMetaData($request);
            }

        }

        return $this->canonizeItem($this->editItem($params));
    }

    public function replaceEntity(mixed $request, mixed $account): array
    {
        $entity = $this->getItem($request[$request['type']] ?? -1, $request['type']);
        $object = $request['body'];
//        $entity['body'] = array_filter($entity['body'], function ($element) use ($object) {
//            return $element['id'] !== $object['id'];
//        });

        $temp = [];
        $i = 0;
        foreach ($entity['body'] as $obj) {
            if ($obj['id'] !== $object['id']) {
                $temp [$i] = $obj;
                $i++;
            }
        }
        $entity['body'] = $temp;


        $params = [
            $request['type'] => $request[$request['type']],
            'information' => json_encode($entity)
        ];
        $this->editItem($params);
        return $entity;
    }

    private function addMetaData(object|array $request): void
    {

        foreach ($request['meta'] as $meta) {
            $params = [];
            $value = $this->getItem($meta['meta_value'], 'slug');
            if (sizeof($value) > 0) {
                $params = [
                    "item_slug" => $request['slug'] ?? null,
                    "meta_key" => $meta['meta_key'] ?? null,
                    "value_slug" => $value['slug'] ?? null,
                    "value_string" => $value['title'] ?? null,
                    "value_number" => $value['number'] ?? null,
                    "value_id" => $value['id'],
                    'time_create' => time()
                ];
            } else {
                $params = [
                    "item_slug" => $request['slug'] ?? null,
                    "meta_key" => $meta['meta_key'] ?? null,
                    "value_string" => $meta['meta_value'] ?? null,
                    "value_slug" => $meta['meta_value'] ?? null,
                    "value_number" => $meta['meta_value'] ?? null,
                    'time_create' => time()
                ];
            }
            if (isset($request['id'])) {
                if ($request['id'] != null)
                    ///TODO:resolve this
                    $params["item_id"] = $request['id'];
            }
            $params["status"] = 1;
            $this->itemRepository->addMetaValue($params);
        }
    }
}
