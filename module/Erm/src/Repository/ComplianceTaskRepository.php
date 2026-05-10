<?php

namespace Erm\Repository;

use Erm\Model\TaskList;
use Erm\Model\TaskProgress;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Hydrator\HydratorInterface;
use RuntimeException;

/**
 * Reads/writes the same tables as TaskRepository ({@see TaskRepository}) with compliance-specific API.
 */
class ComplianceTaskRepository implements ComplianceTaskRepositoryInterface
{
    private string $tableTaskList = 'erm_task_list';

    private string $tableTaskProgress = 'erm_task_progress';

    private AdapterInterface $db;

    private HydratorInterface $hydrator;

    private TaskList $taskListPrototype;

    private TaskProgress $taskProgressPrototype;

    public function __construct(
        AdapterInterface $db,
        HydratorInterface $hydrator,
        TaskList $taskListPrototype,
        TaskProgress $taskProgressPrototype,
    ) {
        $this->db = $db;
        $this->hydrator = $hydrator;
        $this->taskListPrototype = $taskListPrototype;
        $this->taskProgressPrototype = $taskProgressPrototype;
    }

    private function createComplianceTaskConditional(array $params): array
    {
        $where = [];
        if (!empty($params['title'])) {
            $where['title LIKE ?'] = '%' . $params['title'] . '%';
        }
        if (!empty($params['code'])) {
            $where['code LIKE ?'] = '%' . $params['code'] . '%';
        }
        if (!empty($params['user_id'])) {
            $where['user_id'] = $params['user_id'];
        }
        if (!empty($params['type'])) {
            $where['type'] = $params['type'];
        }
        if (!empty($params['status'])) {
            $where['status'] = $params['status'];
        }
        if (isset($params['id'])) {
            if (!empty($params['id'])) {
                $where['id'] = $params['id'];
            } else {
                $where['id IN (?) '] = -1;
            }
        }
        if (!empty($params['slug'])) {
            $where['slug'] = $params['slug'];
        }
        if (!empty($params['data_from'])) {
            $where['time_create >= ?'] = $params['data_from'];
        }
        if (!empty($params['data_to'])) {
            $where['time_create <= ?'] = $params['data_to'];
        }
        if (isset($params['parent_id'])) {
            $where['parent_id'] = $params['parent_id'];
        }

        if (!empty($params['rule'])) {
            $where['rule LIKE ?'] = '%' . $params['rule'] . '%';
        }

        if (isset($params['validity'])) {
            $where['validity'] = (int) $params['validity'];
        }
        if (isset($params['requirement'])) {
            $where['requirement'] = (int) $params['requirement'];
        }

        if (isset($params['warranty_id'])) {
            if (!empty($params['warranty_id'])) {
                $where['warranty_id'] = $params['warranty_id'];
            }
        }
        if (isset($params['rule_id'])) {
            if (!empty($params['rule_id'])) {
                $where['rule_id'] = $params['rule_id'];
            }
        }
        if (isset($params['section_id'])) {
            if (!empty($params['section_id'])) {
                $where['section_id'] = $params['section_id'];
            }
        }
        if (isset($params['reference_id'])) {
            $where['reference_id'] = $params['reference_id'];
        }

        return $where;
    }

    public function getComplianceTaskList(array $params = []): HydratingResultSet|array
    {
        $where = $this->createComplianceTaskConditional($params);
        if (isset($params['mandatory_unit']) && !empty($params['mandatory_unit'])) {
            $jsonSearchConditions = [];
            foreach ($params['mandatory_unit'] as $slug) {
                $jsonSearchConditions[] = "JSON_SEARCH(mandatory_unit, 'one', '$slug', NULL, '$[*].slug') IS NOT NULL";
            }
            $where[] = '(' . implode(' OR ', $jsonSearchConditions) . ')';
        }

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableTaskList)->where($where)->order($params['order'])->offset($params['offset'])->limit($params['limit']);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }
        $resultSet = new HydratingResultSet($this->hydrator, $this->taskListPrototype);

        return $resultSet->initialize($result);
    }

    public function getComplianceTaskCount(array $params = []): int
    {
        $columns = ['count' => new Expression('count(*)')];

        $where = $this->createComplianceTaskConditional($params);
        if (isset($params['mandatory_unit']) && !empty($params['mandatory_unit'])) {
            $jsonSearchConditions = [];
            foreach ($params['mandatory_unit'] as $slug) {
                $jsonSearchConditions[] = "JSON_SEARCH(mandatory_unit, 'one', '$slug', NULL, '$[*].slug') IS NOT NULL";
            }
            $where[] = '(' . implode(' OR ', $jsonSearchConditions) . ')';
        }
        $sql = new Sql($this->db);
        $select = $sql->select($this->tableTaskList)->columns($columns)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute()->current();

        return (int) $row['count'];
    }

    public function getComplianceProgressList(array $params = []): HydratingResultSet|array
    {
        $where = [];
        if (isset($params['standard_id']) && !empty($params['standard_id'])) {
            $where['standard_id'] = $params['standard_id'];
        }
        if (isset($params['section_id']) && !empty($params['section_id'])) {
            $where['section_id'] = $params['section_id'];
        }
        if (isset($params['task_id']) && !empty($params['task_id'])) {
            $where['task_id'] = $params['task_id'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['user_id'] = $params['user_id'];
        }
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['company_id'] = $params['company_id'];
        }
        if (isset($params['level']) && !empty($params['level'])) {
            $where['level'] = $params['level'];
        }

        if (isset($params['parent_id'])) {
            $where['parent_id'] = $params['parent_id'];
        }
        if (isset($params['level'])) {
            $where['level'] = $params['level'];
        }

        $order = ['time_create ASC', 'id ASC'];

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableTaskProgress)->where($where)->order($order);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->taskProgressPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function getComplianceTaskIdsFromProgress(array $filter = []): HydratingResultSet|array
    {
        $where = [];
        $sql = new Sql($this->db);
        $select = $sql->select($this->tableTaskProgress)->where($where);

        if ($filter['type'] == 'value') {
            $select->where([$filter['field'] => $filter['value']]);
        }

        if (!empty($filter['data_from'])) {
            $select->where(['time_create >= ?' => $filter['data_from']]);
        }
        if (!empty($filter['data_to'])) {
            $select->where(['time_create <= ?' => $filter['data_to']]);
        }

        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->taskProgressPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function getComplianceProgress(array $params = []): array|object
    {
        $where = [];
        if (isset($params['id'])) {
            $where['id'] = (int) $params['id'];
        } elseif (isset($params['slug']) && !empty($params['slug'])) {
            $where['slug'] = $params['slug'];
        }

        if (isset($params['task_id'])) {
            $where['task_id'] = $params['task_id'];
        }
        if (isset($params['parent_id'])) {
            $where['parent_id'] = $params['parent_id'];
        }
        if (isset($params['level'])) {
            $where['level'] = $params['level'];
        }

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableTaskProgress)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving compliance progress with identifier "%s"; unknown database error.',
                    json_encode($params)
                )
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->taskProgressPrototype);
        $resultSet->initialize($result);
        $progress = $resultSet->current();

        if (!$progress) {
            return [];
        }

        return $progress;
    }
}

