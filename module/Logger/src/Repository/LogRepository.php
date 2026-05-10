<?php

namespace Pi\Logger\Repository;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Predicate\PredicateSet;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Hydrator\HydratorInterface;
use Pi\Core\Repository\JsonQueryTrait;
use Pi\Logger\Model\History;
use Pi\Logger\Model\System;
use Pi\Logger\Model\User;
use RuntimeException;

class LogRepository implements LogRepositoryInterface
{
    use JsonQueryTrait;

    private string $tableSystem = 'logger_system';

    private string $tableUser = 'logger_user';

    private string $tableHistory = 'logger_history';

    private string $tableAccount = 'user_account';

    private AdapterInterface $db;

    private System $systemPrototype;

    private User $userPrototype;

    private History $historyPrototype;

    private HydratorInterface $hydrator;

    public function __construct(
        AdapterInterface  $db,
        HydratorInterface $hydrator,
        System            $systemPrototype,
        User              $userPrototype,
        History           $historyPrototype
    ) {
        $this->db               = $db;
        $this->hydrator         = $hydrator;
        $this->systemPrototype  = $systemPrototype;
        $this->userPrototype    = $userPrototype;
        $this->historyPrototype = $historyPrototype;
    }

    public function addSystemLog(array $params = []): void
    {
        $allowed = ['path', 'message', 'priority', 'level', 'user_id', 'timestamp', 'time_create', 'information'];
        $params  = array_intersect_key($params, array_flip($allowed));
        unset($params['company_id']);

        $insert = new Insert($this->tableSystem);
        $insert->values($params);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }
    }

    public function getSystemLogList(array $params = [], array $systemLogColumns = []): HydratingResultSet|array
    {
        $where = [];
        if (isset($params['identity']) & !empty($params['identity'])) {
            $where['account.identity like ?'] = '%' . $params['identity'] . '%';
        }
        if (isset($params['name']) & !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }
        if (isset($params['email']) & !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['mobile']) & !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['priority']) & !empty($params['priority'])) {
            $where['log.priority'] = $params['priority'];
        }
        if (isset($params['level']) & !empty($params['level'])) {
            $where['log.level like ?'] = '%' . $params['level'] . '%';
        }
        if (isset($params['message']) & !empty($params['message'])) {
            $where['log.message like ?'] = '%' . $params['message'] . '%';
        }
        if (isset($params['user_id']) & !empty($params['user_id'])) {
            $where['log.user_id'] = $params['user_id'];
        }
        if (isset($params['company_id']) & !empty($params['company_id'])) {
            $where['log.company_id'] = $params['company_id'];
        }
        if (!empty($params['data_from'])) {
            $where['log.time_create >= ?'] = $params['data_from'];
        }
        if (!empty($params['data_to'])) {
            $where['log.time_create <= ?'] = $params['data_to'];
        }

        // Set a where
        $whereMain = new Where();
        $whereMain->addPredicates($where);
        if (!empty($params['information']) && is_array($params['information'])) {
            $jsonWhere = $this->buildJsonWhere($params['information'], $systemLogColumns, 'information', 'log.');
            if ($jsonWhere !== null) {
                $whereMain->addPredicates($jsonWhere, PredicateSet::COMBINED_BY_AND);
            }
        }

        $sql    = new Sql($this->db);
        $from   = ['log' => $this->tableSystem];
        $select = $sql->select()->from($from)->where($whereMain);
        $select->join(
            ['account' => $this->tableAccount],
            'log.user_id=account.id',
            [
                'user_identity' => 'identity',
                'user_name'     => 'name',
                'user_email'    => 'email',
                'user_mobile'   => 'mobile',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        if (isset($params['order']) && !empty($params['order'])) {
            $select->order($params['order']);
        }
        if (isset($params['offset']) && !empty($params['offset'])) {
            $select->offset($params['offset']);
        }
        if (isset($params['limit']) && !empty($params['limit'])) {
            $select->limit($params['limit']);
        }

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->systemPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function getSystemLogCount(array $params = [], array $systemLogColumns = []): int
    {
        $columns = ['count' => new Expression('count(*)')];
        $where   = [];
        if (isset($params['identity']) & !empty($params['identity'])) {
            $where['account.identity like ?'] = '%' . $params['identity'] . '%';
        }
        if (isset($params['name']) & !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }
        if (isset($params['email']) & !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['mobile']) & !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['priority']) & !empty($params['priority'])) {
            $where['log.priority'] = $params['priority'];
        }
        if (isset($params['level']) & !empty($params['level'])) {
            $where['log.level like ?'] = '%' . $params['level'] . '%';
        }
        if (isset($params['message']) & !empty($params['message'])) {
            $where['log.message like ?'] = '%' . $params['message'] . '%';
        }
        if (isset($params['user_id']) & !empty($params['user_id'])) {
            $where['log.user_id'] = $params['user_id'];
        }
        if (isset($params['company_id']) & !empty($params['company_id'])) {
            $where['log.company_id'] = $params['company_id'];
        }
        if (!empty($params['data_from'])) {
            $where['log.time_create >= ?'] = $params['data_from'];
        }
        if (!empty($params['data_to'])) {
            $where['log.time_create <= ?'] = $params['data_to'];
        }

        // Set a where
        $whereMain = new Where();
        $whereMain->addPredicates($where);
        if (!empty($params['information']) && is_array($params['information'])) {
            $jsonWhere = $this->buildJsonWhere($params['information'], $systemLogColumns, 'information', 'log.');
            if ($jsonWhere !== null) {
                $whereMain->addPredicates($jsonWhere, PredicateSet::COMBINED_BY_AND);
            }
        }

        $sql    = new Sql($this->db);
        $from   = ['log' => $this->tableSystem];
        $select = $sql->select()->from($from)->columns($columns)->where($whereMain);
        $select->join(
            ['account' => $this->tableAccount],
            'log.user_id=account.id',
            [],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $statement = $sql->prepareStatementForSqlObject($select);
        $row       = $statement->execute()->current();
        return (int)$row['count'];
    }

    public function cleanupSystemLogOld(int $limitation = 10000): void
    {
        // Set columns
        $columns = ['count' => new Expression('count(*)')];

        // Get count
        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableSystem)->columns($columns);
        $statement = $sql->prepareStatementForSqlObject($select);
        $row       = $statement->execute()->current();
        $count     = (int)$row['count'];

        // Check count and delete
        if ($count > $limitation) {
            // Get first available id
            $select    = $sql->select($this->tableSystem)->columns(['id'])->order('id ASC')->limit(1);
            $statement = $sql->prepareStatementForSqlObject($select);
            $row       = $statement->execute()->current();

            // Check id and delete
            if (isset($row['id']) && (int)$row['id'] > 0) {
                // Do Delete
                $where     = ['id' => (int)$row['id']];
                $delete    = $sql->delete($this->tableSystem)->where($where);
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

    public function cleanupSystemLog(int $rowsToDelete): void
    {
        // Get the first X IDs to delete
        $sql       = new Sql($this->db);
        $select    = $sql->select($this->tableSystem)
            ->columns(['id'])
            ->order('id ASC')
            ->limit($rowsToDelete);
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();

        // Collect IDs to delete
        $idsToDelete = [];
        foreach ($resultSet as $row) {
            $idsToDelete[] = (int)$row['id'];
        }

        if (!empty($idsToDelete)) {
            // Perform batch delete
            $delete    = $sql->delete($this->tableSystem)->where(['id' => $idsToDelete]);
            $statement = $sql->prepareStatementForSqlObject($delete);
            $result    = $statement->execute();

            if (!$result instanceof ResultInterface) {
                throw new RuntimeException('Database error occurred during delete operation');
            }
        }
    }


    public function addUserLog(array $params = []): void
    {
        $insert = new Insert($this->tableUser);
        $insert->values($params);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }
    }

    public function getUserLogList(array $params = [], array $userLogColumns = []): HydratingResultSet|array
    {
        $where = [];
        if (isset($params['identity']) & !empty($params['identity'])) {
            $where['account.identity like ?'] = '%' . $params['identity'] . '%';
        }
        if (isset($params['name']) & !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }
        if (isset($params['email']) & !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['mobile']) & !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['log.user_id'] = $params['user_id'];
        }
        if (isset($params['operator_id']) && !empty($params['operator_id'])) {
            $where['log.operator_id'] = $params['operator_id'];
        }
        if (isset($params['state']) && !empty($params['state'])) {
            $where['log.state'] = $params['state'];
        }
        if (!empty($params['data_from'])) {
            $where['log.time_create >= ?'] = $params['data_from'];
        }
        if (!empty($params['data_to'])) {
            $where['log.time_create <= ?'] = $params['data_to'];
        }

        // Set a where
        $whereMain = new Where();
        $whereMain->addPredicates($where);
        if (!empty($params['information']) && is_array($params['information'])) {
            $jsonWhere = $this->buildJsonWhere($params['information'], $userLogColumns, 'information', 'log.');
            if ($jsonWhere !== null) {
                $whereMain->addPredicates($jsonWhere, PredicateSet::COMBINED_BY_AND);
            }
        }

        $sql    = new Sql($this->db);
        $from   = ['log' => $this->tableUser];
        $select = $sql->select()->from($from)->where($whereMain);
        $select->join(
            ['account' => $this->tableAccount],
            'log.user_id=account.id',
            [
                'user_identity' => 'identity',
                'user_name'     => 'name',
                'user_email'    => 'email',
                'user_mobile'   => 'mobile',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        if (isset($params['order']) && !empty($params['order'])) {
            $select->order($params['order']);
        }
        if (isset($params['offset']) && !empty($params['offset'])) {
            $select->offset($params['offset']);
        }
        if (isset($params['limit']) && !empty($params['limit'])) {
            $select->limit($params['limit']);
        }

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->userPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function getUserLogCount(array $params = [], array $userLogColumns = []): int
    {
        // Set where
        $columns = ['count' => new Expression('count(*)')];
        $where   = [];
        if (isset($params['identity']) & !empty($params['identity'])) {
            $where['account.identity like ?'] = '%' . $params['identity'] . '%';
        }
        if (isset($params['name']) & !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }
        if (isset($params['email']) & !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['mobile']) & !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['log.user_id'] = $params['user_id'];
        }
        if (isset($params['operator_id']) && !empty($params['operator_id'])) {
            $where['log.operator_id'] = $params['operator_id'];
        }
        if (isset($params['state']) && !empty($params['state'])) {
            $where['log.state'] = $params['state'];
        }
        if (!empty($params['data_from'])) {
            $where['log.time_create >= ?'] = $params['data_from'];
        }
        if (!empty($params['data_to'])) {
            $where['log.time_create <= ?'] = $params['data_to'];
        }

        // Set a where
        $whereMain = new Where();
        $whereMain->addPredicates($where);
        if (!empty($params['information']) && is_array($params['information'])) {
            $jsonWhere = $this->buildJsonWhere($params['information'], $userLogColumns, 'information', 'log.');
            if ($jsonWhere !== null) {
                $whereMain->addPredicates($jsonWhere, PredicateSet::COMBINED_BY_AND);
            }
        }

        $sql    = new Sql($this->db);
        $from   = ['log' => $this->tableUser];
        $select = $sql->select()->from($from)->columns($columns)->where($whereMain);
        $select->join(
            ['account' => $this->tableAccount],
            'log.user_id=account.id',
            [],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $statement = $sql->prepareStatementForSqlObject($select);
        $row       = $statement->execute()->current();

        return (int)$row['count'];
    }

    public function addHistoryLog(array $params = []): void
    {
        $allowed = ['relation_module', 'relation_section', 'relation_item', 'user_id', 'time_create', 'state', 'information'];
        $params  = array_intersect_key($params, array_flip($allowed));
        unset($params['company_id']);

        $insert = new Insert($this->tableHistory);
        $insert->values($params);

        $sql       = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }
    }

    public function getHistoryLogList(array $params = []): HydratingResultSet|array
    {
        $where = [];
        if (isset($params['identity']) & !empty($params['identity'])) {
            $where['account.identity like ?'] = '%' . $params['identity'] . '%';
        }
        if (isset($params['name']) & !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }
        if (isset($params['email']) & !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['mobile']) & !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['log.user_id'] = $params['user_id'];
        }
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['log.company_id'] = $params['company_id'];
        }
        if (isset($params['relation_module']) && !empty($params['relation_module'])) {
            $where['log.relation_module'] = $params['relation_module'];
        }
        if (isset($params['relation_section']) && !empty($params['relation_section'])) {
            $where['log.relation_section'] = $params['relation_section'];
        }
        if (isset($params['relation_item']) && !empty($params['relation_item'])) {
            $where['log.relation_item'] = $params['relation_item'];
        }
        if (isset($params['state']) && !empty($params['state'])) {
            $where['log.state'] = $params['state'];
        }

        $sql    = new Sql($this->db);
        $from   = ['log' => $this->tableHistory];
        $select = $sql->select()->from($from)->where($where);
        $select->join(
            ['account' => $this->tableAccount],
            'log.user_id=account.id',
            [
                'user_identity' => 'identity',
                'user_name'     => 'name',
                'user_email'    => 'email',
                'user_mobile'   => 'mobile',
            ],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        if (isset($params['order']) && !empty($params['order'])) {
            $select->order($params['order']);
        }
        if (isset($params['offset']) && !empty($params['offset'])) {
            $select->offset($params['offset']);
        }
        if (isset($params['limit']) && !empty($params['limit'])) {
            $select->limit($params['limit']);
        }

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->historyPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function getHistoryLogCount(array $params = []): int
    {
        // Set where
        $columns = ['count' => new Expression('count(*)')];
        $where   = [];
        if (isset($params['identity']) & !empty($params['identity'])) {
            $where['account.identity like ?'] = '%' . $params['identity'] . '%';
        }
        if (isset($params['name']) & !empty($params['name'])) {
            $where['account.name like ?'] = '%' . $params['name'] . '%';
        }
        if (isset($params['email']) & !empty($params['email'])) {
            $where['account.email like ?'] = '%' . $params['email'] . '%';
        }
        if (isset($params['mobile']) & !empty($params['mobile'])) {
            $where['account.mobile like ?'] = '%' . $params['mobile'] . '%';
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $where['log.user_id'] = $params['user_id'];
        }
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $where['log.company_id'] = $params['company_id'];
        }
        if (isset($params['relation_module']) && !empty($params['relation_module'])) {
            $where['log.relation_module'] = $params['relation_module'];
        }
        if (isset($params['relation_section']) && !empty($params['relation_section'])) {
            $where['log.relation_section'] = $params['relation_section'];
        }
        if (isset($params['relation_item']) && !empty($params['relation_item'])) {
            $where['log.relation_item'] = $params['relation_item'];
        }
        if (isset($params['state']) && !empty($params['state'])) {
            $where['log.state'] = $params['state'];
        }

        $sql    = new Sql($this->db);
        $from   = ['log' => $this->tableHistory];
        $select = $sql->select()->from($from)->columns($columns)->where($where);
        $select->join(
            ['account' => $this->tableAccount],
            'log.user_id=account.id',
            [],
            $select::JOIN_LEFT . ' ' . $select::JOIN_OUTER
        );
        $statement = $sql->prepareStatementForSqlObject($select);
        $row       = $statement->execute()->current();

        return (int)$row['count'];
    }
}