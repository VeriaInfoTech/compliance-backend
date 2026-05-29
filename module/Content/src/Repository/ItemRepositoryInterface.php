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


}
