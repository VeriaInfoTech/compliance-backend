<?php

namespace Content\Repository;

use Laminas\Db\ResultSet\HydratingResultSet;

interface ItemRepositoryInterface
{
    public function getItem($parameter, $type): object|array;

    public function getItemList(array $params = []): HydratingResultSet|array;

    public function getItemCount(array $params = []): int;

    public function addItem(array $params): object|array;

    public function editItem(array $params): object|array;

    public function deleteItem(array $params): void;

    public function getIDFromFilter(array $filters = []): HydratingResultSet|array;

    public function addCartItem(array $params): object|array;

    public function getGroupList($params);

    public function getMetaKeyCount(array $params);

    public function getMetaKeyList(array $listParams);

    public function addMetaKey(array $params): object|array;

    public function updateMetaKey(array $params): object|array;

    public function addMetaValue(array $params): object|array;

}
