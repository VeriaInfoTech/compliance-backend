<?php

namespace Risk\Repository;

use InvalidArgumentException;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;
use Laminas\Hydrator\HydratorInterface;
use RuntimeException;
use Risk\Model\Form\Data;
use Risk\Model\Form\Element;
use Risk\Model\Form\Inventory;
use Risk\Model\Form\Link;
use Risk\Model\Form\Record;

class FormRepository implements FormRepositoryInterface
{
    private string $tableFormInventory = 'form_inventory';
    private string $tableFormElement   = 'form_element';
    private string $tableFormLink      = 'form_link';
    private string $tableFormRecord    = 'form_record';
    private string $tableFormData      = 'form_data';

    /**
     * @var AdapterInterface
     */
    private AdapterInterface $db;

    /**
     * @var HydratorInterface
     */
    private HydratorInterface $hydrator;

    private Inventory $inventoryPrototype;
    private Element   $elementPrototype;
    private Link      $linkPrototype;
    private Record    $recordPrototype;
    private Data      $dataPrototype;

    public function __construct(
        AdapterInterface $db,
        HydratorInterface $hydrator,
        Inventory $inventoryPrototype,
        Element $elementPrototype,
        Link $linkPrototype,
        Record $recordPrototype,
        Data $dataPrototype
    ) {
        $this->db                 = $db;
        $this->hydrator           = $hydrator;
        $this->inventoryPrototype = $inventoryPrototype;
        $this->elementPrototype   = $elementPrototype;
        $this->linkPrototype      = $linkPrototype;
        $this->recordPrototype    = $recordPrototype;
        $this->dataPrototype      = $dataPrototype;
    }

    /**
     * @param array $params
     *
     * @return HydratingResultSet
     */
    public function getFormInventoryList(array $params = []): HydratingResultSet
    {
        $where = [];
        if (isset($params['status']) && !empty($params['status'])) {
            $where['status'] = $params['status'];
        }

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableFormInventory)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->inventoryPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    /**
     * @param array $params
     *
     * @return Inventory
     */
    public function getFormInventory(array $params = []): Inventory
    {
        // Set
        $where = [];
        if (isset($params['id']) && (int)$params['id'] > 0) {
            $where['id'] = (int)$params['id'];
        } elseif (isset($params['slug']) && !empty($params['slug'])) {
            $where['slug'] = $params['slug'];
        }

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableFormInventory)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving blog post with identifier "%s"; unknown database error.',
                    $params
                )
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->inventoryPrototype);
        $resultSet->initialize($result);
        $resource = $resultSet->current();

        if (!$resource) {
            throw new InvalidArgumentException(
                sprintf(
                    'Role with identifier "%s" not found.',
                    $params
                )
            );
        }

        return $resource;
    }

    /**
     * @param array $params
     *
     * @return Inventory
     */
    public function addFormInventory(array $params = []): Inventory
    {
        $insert = new Insert($this->tableFormInventory);
        $insert->values($params);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }

        $id = $result->getGeneratedValue();

        return $this->getFormInventory(['id' => $id]);
    }

    /**
     * @param int $id
     * @param array  $params
     */
    public function updateFormInventory(int $id, array $params = []): void
    {
        $update = new Update($this->tableFormInventory);
        $update->set($params);
        $update->where(['id' => $id]);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    /**
     * @param int $id
     */
    public function deleteFormInventory(array $params = []): void
    {
        $where = [];

        if (!empty($where)) {
            $delete = new Delete($this->tableFormInventory);
            $delete->where($where);

            $sql       = new Sql($this->db);
            $statement = $sql->prepareStatementForSqlObject($delete);
            $result    = $statement->execute();

            if (!$result instanceof ResultInterface) {
                throw new RuntimeException(
                    'Database error occurred during update operation'
                );
            }
        }
    }

    /**
     * @param array $params
     *
     * @return HydratingResultSet
     */
    public function getFormElementList(array $params = []): HydratingResultSet
    {
        $where = [];
        if (isset($params['status']) && !empty($params['status'])) {
            $where['status'] = $params['status'];
        }

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableFormElement)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->elementPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    /**
     * @param array $params
     *
     * @return Element
     */
    public function getFormElement(array $params = []): Element
    {
        // Set
        $where = [];
        if (isset($params['id']) && (int)$params['id'] > 0) {
            $where['id'] = (int)$params['id'];
        } elseif (isset($params['code']) && !empty($params['code'])) {
            $where['code'] = $params['code'];
        }

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableFormElement)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving blog post with identifier "%s"; unknown database error.',
                    $params
                )
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->elementPrototype);
        $resultSet->initialize($result);
        $resource = $resultSet->current();

        if (!$resource) {
            throw new InvalidArgumentException(
                sprintf(
                    'Role with identifier "%s" not found.',
                    $params
                )
            );
        }

        return $resource;
    }

    /**
     * @param array $params
     *
     * @return Element
     */
    public function addFormElement(array $params = []): Element
    {
        $insert = new Insert($this->tableFormElement);
        $insert->values($params);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }

        $id = $result->getGeneratedValue();

        return $this->getFormElement(['id' => $id]);
    }

    /**
     * @param int $id
     * @param array  $params
     */
    public function updateFormElement(int $id, array $params = []): void
    {
        $update = new Update($this->tableFormElement);
        $update->set($params);
        $update->where(['id' => $id]);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    /**
     * @param int $id
     */
    public function deleteFormElement(array $params = []): void
    {
        $where = [];

        if (!empty($where)) {
            $delete = new Delete($this->tableFormElement);
            $delete->where($where);

            $sql       = new Sql($this->db);
            $statement = $sql->prepareStatementForSqlObject($delete);
            $result    = $statement->execute();

            if (!$result instanceof ResultInterface) {
                throw new RuntimeException(
                    'Database error occurred during update operation'
                );
            }
        }
    }

    /**
     * @param array $params
     *
     * @return HydratingResultSet
     */
    public function getFormLinkList(array $params = []): HydratingResultSet
    {
        $where = [];
        if (isset($params['status']) && !empty($params['status'])) {
            $where['status'] = $params['status'];
        }

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableFormLink)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->linkPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    /**
     * @param array $params
     *
     * @return Link
     */
    public function getFormLink(array $params = []): Link
    {
        // Set
        $where = [];
        if (isset($params['id']) && (int)$params['id'] > 0) {
            $where['id'] = (int)$params['id'];
        }

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableFormLink)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving blog post with identifier "%s"; unknown database error.',
                    $params
                )
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->linkPrototype);
        $resultSet->initialize($result);
        $resource = $resultSet->current();

        if (!$resource) {
            throw new InvalidArgumentException(
                sprintf(
                    'Role with identifier "%s" not found.',
                    $params
                )
            );
        }

        return $resource;
    }

    /**
     * @param array $params
     *
     * @return Link
     */
    public function addFormLink(array $params = []): Link
    {
        $insert = new Insert($this->tableFormData);
        $insert->values($params);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }

        $id = $result->getGeneratedValue();

        return $this->getFormLink(['id' => $id]);
    }

    /**
     * @param int $id
     * @param array  $params
     */
    public function updateFormLink(int $id, array $params = []): void
    {
        $update = new Update($this->tableFormLink);
        $update->set($params);
        $update->where(['id' => $id]);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    public function deleteFormLink(array $params = []): void
    {
        $where = [];
        if (isset($params['form_id']) && !empty($params['form_id'])) {
            $where['form_id'] = $params['form_id'];
        }
        if (isset($params['element_id']) && !empty($params['element_id'])) {
            $where['element_id'] = $params['element_id'];
        }

        if (!empty($where)) {
            $delete = new Delete($this->tableFormLink);
            $delete->where($where);

            $sql       = new Sql($this->db);
            $statement = $sql->prepareStatementForSqlObject($delete);
            $result    = $statement->execute();

            if (!$result instanceof ResultInterface) {
                throw new RuntimeException(
                    'Database error occurred during update operation'
                );
            }
        }
    }

    /**
     * @param array $params
     *
     * @return HydratingResultSet
     */
    public function getFormRecordList(array $params = []): HydratingResultSet
    {
        $where = [];
        if (isset($params['status']) && !empty($params['status'])) {
            $where['status'] = $params['status'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['user_id'] = $params['user_id'];
        }
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['company_id'] = $params['company_id'];
        }
        if (isset($params['form_id']) && !empty($params['form_id'])) {
            $where['form_id'] = $params['form_id'];
        }
        if (isset($params['record_id']) && !empty($params['record_id'])) {
            $where['id'] = $params['record_id'];
        }

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableFormRecord)->where($where)->order($params['order'])->offset($params['offset'])->limit($params['limit']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->recordPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    /**
     * @param array $params
     *
     * @return Record
     */
    public function getFormRecord(array $params = []): Record
    {
        // Set
        $where = [];
        if (isset($params['id']) && (int)$params['id'] > 0) {
            $where['id'] = (int)$params['id'];
        } elseif (isset($params['slug']) && !empty($params['slug'])) {
            $where['slug'] = $params['slug'];
        }

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableFormRecord)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving blog post with identifier "%s"; unknown database error.',
                    $params
                )
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->recordPrototype);
        $resultSet->initialize($result);
        $resource = $resultSet->current();

        if (!$resource) {
            throw new InvalidArgumentException(
                sprintf(
                    'Role with identifier "%s" not found.',
                    $params
                )
            );
        }

        return $resource;
    }

    /**
     * @param array $params
     *
     * @return Record
     */
    public function addFormRecord(array $params = []): Record
    {
        $insert = new Insert($this->tableFormRecord);
        $insert->values($params);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }

        $id = $result->getGeneratedValue();

        return $this->getFormRecord(['id' => $id]);
    }

    /**
     * @param int $id
     * @param array  $params
     */
    public function updateFormRecord(int $id, array $params = []): void
    {
        $update = new Update($this->tableFormRecord);
        $update->set($params);
        $update->where(['id' => $id]);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    public function deleteFormRecord(array $params = []): void
    {
        $where = [];

        if (!empty($where)) {
            $delete = new Delete($this->tableFormRecord);
            $delete->where($where);

            $sql       = new Sql($this->db);
            $statement = $sql->prepareStatementForSqlObject($delete);
            $result    = $statement->execute();

            if (!$result instanceof ResultInterface) {
                throw new RuntimeException(
                    'Database error occurred during update operation'
                );
            }
        }
    }

    /**
     * @param array $params
     *
     * @return HydratingResultSet
     */
    public function getFormDataList(array $params = []): HydratingResultSet
    {
        $where = [];
        if (isset($params['record_id']) && !empty($params['record_id'])) {
            $where['record_id'] = $params['record_id'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['user_id'] = $params['user_id'];
        }
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['company_id'] = $params['company_id'];
        }
        if (isset($params['form_id']) && !empty($params['form_id'])) {
            $where['form_id'] = $params['form_id'];
        }
        if (isset($params['element_id']) && !empty($params['element_id'])) {
            $where['element_id'] = $params['element_id'];
        }
        if (isset($params['status']) && !empty($params['status'])) {
            $where['status'] = $params['status'];
        }

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableFormData)->where($where)->order($params['order'])->offset($params['offset'])->limit($params['limit']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->dataPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    /**
     * @param array $params
     *
     * @return Data
     */
    public function getFormData(array $params = []): Data
    {
// Set
        $where = [];
        if (isset($params['id']) && (int)$params['id'] > 0) {
            $where['id'] = (int)$params['id'];
        } elseif (isset($params['slug']) && !empty($params['slug'])) {
            $where['slug'] = $params['slug'];
        }

        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableFormData)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving blog post with identifier "%s"; unknown database error.',
                    $params
                )
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->dataPrototype);
        $resultSet->initialize($result);
        $resource = $resultSet->current();

        if (!$resource) {
            throw new InvalidArgumentException(
                sprintf(
                    'Role with identifier "%s" not found.',
                    $params
                )
            );
        }

        return $resource;
    }

    /**
     * @param array $params
     *
     * @return Data
     */
    public function addFormData(array $params = []): Data
    {
        $insert = new Insert($this->tableFormData);
        $insert->values($params);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }

        $id = $result->getGeneratedValue();

        return $this->getFormData(['id' => $id]);
    }

    /**
     * @param int $id
     * @param array  $params
     */
    public function updateFormData(int $id, array $params = []): void
    {
        $update = new Update($this->tableFormData);
        $update->set($params);
        $update->where(['id' => $id]);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    public function deleteFormData(array $params = []): void
    {
        $where = [];

        if (!empty($where)) {
            $delete = new Delete($this->tableFormData);
            $delete->where($where);

            $sql       = new Sql($this->db);
            $statement = $sql->prepareStatementForSqlObject($delete);
            $result    = $statement->execute();

            if (!$result instanceof ResultInterface) {
                throw new RuntimeException(
                    'Database error occurred during update operation'
                );
            }
        }
    }
}