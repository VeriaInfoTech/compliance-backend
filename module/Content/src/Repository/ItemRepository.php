<?php

namespace Content\Repository;

use Content\Model\Item;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;
use Laminas\Hydrator\HydratorInterface;
use RuntimeException;
use function sprintf;

class ItemRepository implements ItemRepositoryInterface
{
    private string $tableItem = 'content_item';
    private AdapterInterface $db;
    private Item $itemPrototype;
    private HydratorInterface $hydrator;

    public function __construct(
        AdapterInterface $db,
        HydratorInterface $hydrator,
        Item $itemPrototype
    ) {
        $this->db = $db;
        $this->hydrator = $hydrator;
        $this->itemPrototype = $itemPrototype;
    }

    public function getItemList(array $params = []): HydratingResultSet|array
    {
        $where = $this->buildWhereConditions($params);

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableItem)
            ->where($where)
            ->order($params['order'] ?? [])
            ->offset($params['offset'] ?? 0)
            ->limit($params['limit'] ?? 125);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->itemPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function getItemCount(array $params = []): int
    {
        $columns = ['count' => new Expression('count(*)')];
        $where = $this->buildWhereConditions($params);

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableItem)
            ->columns($columns)
            ->where($where);

        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute()->current();

        return (int) ($row['count'] ?? 0);
    }

    public function addItem(array $params): object|array
    {
        $insert = new Insert($this->tableItem);
        $insert->values($params);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException('Database error occurred during item insert operation');
        }

        $id = $result->getGeneratedValue();
        return $this->getItem($id);
    }

    public function getItem($parameter, $type = 'id', $params = []): object|array
    {
        $where = [$type => $parameter];

        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['user_id'] = $params['user_id'];
        }
        if (isset($params['type']) && !empty($params['type'])) {
            $where['type'] = $params['type'];
        }
        if (isset($params['status'])) {
            $where['status'] = $params['status'];
        }

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableItem)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf('Failed retrieving item with identifier "%s"; unknown database error.', $parameter)
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->itemPrototype);
        $resultSet->initialize($result);
        $item = $resultSet->current();

        return $item ?: [];
    }

    public function editItem(array $params): object|array
    {
        $update = new Update($this->tableItem);
        $update->set($params);

        if (isset($params['id'])) {
            $update->where(['id' => $params['id']]);
            $identifier = $params['id'];
            $type = 'id';
        } elseif (isset($params['slug'])) {
            $update->where(['slug' => $params['slug']]);
            $identifier = $params['slug'];
            $type = 'slug';
        } else {
            throw new RuntimeException('Neither id nor slug provided for item update');
        }

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException('Database error occurred during item update operation');
        }

        return $this->getItem($identifier, $type);
    }

    public function deleteItem(array $params): void
    {
        if (empty($params['id'])) {
            throw new RuntimeException('Item id required for delete operation');
        }

        $update = new Update($this->tableItem);
        $update->set($params);
        $update->where(['id' => $params['id']]);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }

    private function buildWhereConditions(array $params): array
    {
        $where = [];

        if (isset($params['type']) && !empty($params['type'])) {
            $where['type'] = $this->sanitizeString($params['type']);
        }

        if (isset($params['source']) && !empty($params['source'])) {
            $where['source'] = $this->sanitizeString($params['source']);
        }

        if (isset($params['status'])) {
            $where['status'] = $this->sanitizeInt($params['status']);
        }

        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['user_id'] = $this->sanitizeInt($params['user_id']);
        }

        if (isset($params['parent_id']) && !empty($params['parent_id'])) {
            $where['parent_id'] = $this->sanitizeInt($params['parent_id']);
        }

        if (isset($params['id']) && !empty($params['id'])) {
            $where['id'] = $this->sanitizeInt($params['id']);
        }

        if (isset($params['slug']) && !empty($params['slug'])) {
            $where['slug'] = $this->sanitizeString($params['slug']);
        }

        if (isset($params['title']) && !empty($params['title'])) {
            $where['title LIKE ?'] = '%' . $this->escapeLike($params['title']) . '%';
        }

        if (isset($params['data_from']) && !empty($params['data_from'])) {
            $where['time_create >= ?'] = $this->sanitizeTimestamp($params['data_from']);
        }

        if (isset($params['data_to']) && !empty($params['data_to'])) {
            $where['time_create <= ?'] = $this->sanitizeTimestamp($params['data_to']);
        }

        return $where;
    }

    private function sanitizeString(mixed $value): string
    {
        return (string) trim($value);
    }

    private function sanitizeInt(mixed $value): int
    {
        return (int) $value;
    }

    private function sanitizeTimestamp(mixed $value): int
    {
        $timestamp = strtotime($value);
        return $timestamp !== false ? $timestamp : time();
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '%_');
    }
}
