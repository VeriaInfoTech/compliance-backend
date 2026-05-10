<?php

namespace Risk\Repository;

use Laminas\Db\ResultSet\HydratingResultSet;
use Risk\Model\Form\Data;
use Risk\Model\Form\Element;
use Risk\Model\Form\Inventory;
use Risk\Model\Form\Link;
use Risk\Model\Form\Record;

interface FormRepositoryInterface
{
    public function getFormInventoryList(array $params = []): HydratingResultSet;

    public function getFormInventory(array $params = []): Inventory;

    public function addFormInventory(array $params = []): Inventory;

    public function updateFormInventory(int $id, array $params = []): void;

    public function deleteFormInventory(array $params = []): void;

    public function getFormElementList(array $params = []): HydratingResultSet;

    public function getFormElement(array $params = []): Element;

    public function addFormElement(array $params = []): Element;

    public function updateFormElement(int $id, array $params = []): void;

    public function deleteFormElement(array $params = []): void;

    public function getFormLinkList(array $params = []): HydratingResultSet;

    public function getFormLink(array $params = []): Link;

    public function addFormLink(array $params = []): Link;

    public function updateFormLink(int $id, array $params = []): void;

    public function deleteFormLink(array $params = []): void;

    public function getFormRecordList(array $params = []): HydratingResultSet;

    public function getFormRecord(array $params = []): Record;

    public function addFormRecord(array $params = []): Record;

    public function updateFormRecord(int $id, array $params = []): void;

    public function deleteFormRecord(array $params = []): void;

    public function getFormDataList(array $params = []): HydratingResultSet;

    public function getFormData(array $params = []): Data;

    public function addFormData(array $params = []): Data;

    public function updateFormData(int $id, array $params = []): void;

    public function deleteFormData(array $params = []): void;
}