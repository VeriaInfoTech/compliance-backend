<?php

namespace Erm\Repository;

use Erm\Model\ErmMeta;
use Erm\Model\MandatoryUnit;
use Erm\Model\MandatoryUnitMember;
use Erm\Model\TaskAudit;
use Erm\Model\TaskList;
use Erm\Model\TaskProgress;
use Erm\Model\TaskRisk;
use Erm\Model\TaskSection;
use Erm\Model\Rule;
use Erm\Model\Warranty;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;
use Laminas\Hydrator\HydratorInterface;
use RuntimeException;
use function sprintf;

class TaskRepository implements TaskRepositoryInterface
{
    /**
     * Task List Table name
     *
     * @var string
     */
    private string $tableTaskList = 'erm_task_list';

    /**
     * Task Section Table name
     *
     * @var string
     */
    private string $tableTaskSection = 'erm_task_section';

    /**
     * Task Progress Table name
     *
     * @var string
     */
    private string $tableTaskProgress = 'erm_task_progress';

    /**
     * Audit Progress Table name
     *
     * @var string
     */
    private string $tableAuditTaskProgress = 'erm_task_audit';

    /**
     * Task Risk Table name
     *
     * @var string
     */
    private string $tableTaskRisk = 'erm_task_risk';

    /**
     * Task Audit Table name
     *
     * @var string
     */
    private string $tableTaskAudit = 'erm_task_audit';

    /**
     * Account Table name
     *
     * @var string
     */
    private string $tableAccount = 'user_account';

    /**
     * Mandatory Table name
     *
     * @var string
     */
    private string $tableMandatoryUnit = 'erm_mandatory';

    /**
     * Mandatory Member Table name
     *
     * @var string
     */
    private string $tableMandatoryUnitMember = 'erm_mandatory_member';
    /**
     * Rule Table name
     *
     * @var string
     */
    private string $tableRule = 'erm_rule';

    /**
     * Warranty Table name
     *
     * @var string
     */
    private string $tableWarranty = 'erm_warranty_types';

    private string $tableErmMeta = 'erm_meta';

    /**
     * @var AdapterInterface
     */
    private AdapterInterface $db;

    /**
     * @var HydratorInterface
     */
    private HydratorInterface $hydrator;

    /**
     * @var TaskList
     */
    private TaskList $taskListPrototype;

    /**
     * @var TaskSection
     */
    private TaskSection $taskSectionPrototype;

    /**
     * @var TaskProgress
     */
    private TaskProgress $taskProgressPrototype;

    /**
     * @var TaskRisk
     */
    private TaskRisk $taskRiskPrototype;

    /**
     * @var TaskAudit
     */
    private TaskAudit $taskAuditPrototype;

    /**
     * @var
     */
    private Rule $rulePrototype;

    /**
     * @var Warranty
     */
    private Warranty $warrantyPrototype;
    private MandatoryUnit $mandatoryUnitPrototype;
    private MandatoryUnitMember $mandatoryUnitMemberPrototype;
    private ErmMeta $ermMetaPortotype;

    public function __construct(
        AdapterInterface    $db,
        HydratorInterface   $hydrator,
        TaskList            $taskListPrototype,
        TaskSection         $taskSectionPrototype,
        TaskProgress        $taskProgressPrototype,
        TaskRisk            $taskRiskPrototype,
        TaskAudit           $taskAuditPrototype,
        Rule                $rulePrototype,
        Warranty            $warrantyPrototype,
        MandatoryUnit       $mandatoryUnitPrototype,
        MandatoryUnitMember $mandatoryUnitMemberPrototype,
        ErmMeta             $ermMetaPortotype,
    )
    {
        $this->db = $db;
        $this->hydrator = $hydrator;
        $this->taskListPrototype = $taskListPrototype;
        $this->taskSectionPrototype = $taskSectionPrototype;
        $this->taskProgressPrototype = $taskProgressPrototype;
        $this->taskRiskPrototype = $taskRiskPrototype;
        $this->taskAuditPrototype = $taskAuditPrototype;
        $this->rulePrototype = $rulePrototype;
        $this->warrantyPrototype = $warrantyPrototype;
        $this->mandatoryUnitPrototype = $mandatoryUnitPrototype;
        $this->mandatoryUnitMemberPrototype = $mandatoryUnitMemberPrototype;
        $this->ermMetaPortotype = $ermMetaPortotype;
    }

    /**
     * @param array $params
     *
     * @return HydratingResultSet|array
     */
    public function getTaskSectionList(array $params = []): HydratingResultSet|array
    {
        $where = [];
        if (isset($params['standard_id']) && !empty($params['standard_id'])) {
            $where['standard_id'] = $params['standard_id'];
        }
        if (isset($params['parent_id']) && !empty($params['parent_id'])) {
            $where['parent_id'] = $params['parent_id'];
        }
        if (isset($params['status']) && !empty($params['status'])) {
            $where['status'] = $params['status'];
        }
        if (isset($params['code']) && !empty($params['code'])) {
            $where['code'] = $params['code'];
        }
        if (isset($params['type']) && !empty($params['type'])) {
            $where['type'] = $params['type'];
        }
        $order = ['id ASC'];

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableTaskSection)->where($where)->order($order);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->taskSectionPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    /**
     * @param array $params
     *
     * @return HydratingResultSet|array
     */
    public function getTaskListOld(array $params = []): HydratingResultSet|array
    {
        $where = [];
        $where['time_delete'] = 0;
        if (isset($params['standard_id']) && !empty($params['standard_id'])) {
            $where['standard_id'] = $params['standard_id'];
        }
        if (isset($params['section_id']) && !empty($params['section_id'])) {
            $where['section_id'] = $params['section_id'];
        }
        if (isset($params['status']) && !empty($params['status'])) {
            $where['status'] = $params['status'];
        }
        if (isset($params['code']) && !empty($params['code'])) {
            $where['code'] = $params['code'];
        }
        if (isset($params['id']) && !empty($params['id'])) {
            $where['id'] = $params['id'];
        }
        $order = ['id ASC'];

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableTaskList)->where($where)->order($order);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->taskListPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    /**
     * @param array $params
     *
     * @return array|object
     */
    public function getTask(array $params = []): array|object
    {
        // Set
        $where = [];
        if (isset($params['id']) && (int)$params['id'] > 0) {
            $where['id'] = (int)$params['id'];
        }
        if (isset($params['code'])) {
            $where['code'] = $params['code'];
        }
        if (isset($params['type'])) {
            $where['type'] = $params['type'];
        }

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableTaskList)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving blog post with identifier "%s"; unknown database error.',
                    $params
                )
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->taskListPrototype);
        $resultSet->initialize($result);
        $task = $resultSet->current();

        if (!$task) {
            return [];
        }

        return $task;
    }

    public function getTaskInformationDecoded(int $taskId): ?array
    {
        if ($taskId < 1) {
            return null;
        }
        $sql = new Sql($this->db);
        $select = $sql->select($this->tableTaskList)->columns(['information'])->where(['id' => $taskId]);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return null;
        }
        $row = $result->current();
        if ($row === false || empty($row['information'])) {
            return null;
        }
        $decoded = json_decode((string) $row['information'], true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param array $params
     *
     * @return array|object
     */
    public function addProgress(array $params = []): array|object
    {
        $insert = new Insert($this->tableTaskProgress);
        $insert->values($params);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }

        $id = $result->getGeneratedValue();

        return $this->getProgress(['id' => $id]);
    }

    /**
     * @param array $where
     * @param array $set
     */
    public function updateProgress(array $where, array $set): void
    {
        $update = new Update($this->tableTaskProgress);
        $update->set($set);
        $update->where($where);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    /**
     * @param array $params
     *
     * @return array|object
     */
    public function getProgress(array $params = []): array|object
    {
        $where = [];
//        if (isset($params['id']) && (int)$params['id'] > 0) {
        if (isset($params['id'])) {
            $where['id'] = (int)$params['id'];
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
                    'Failed retrieving blog post with identifier "%s"; unknown database error.',
                    $params
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

    /**
     * @param array $params
     *
     * @return HydratingResultSet|array
     */
    public function getProgressList(array $params = []): HydratingResultSet|array
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

    /**
     * @param array $params
     *
     * @return HydratingResultSet|array
     */
    public function getRiskProgressList(array $params = []): HydratingResultSet|array
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
        $select = $sql->select($this->tableTaskRisk)->where($where)->order($order);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->taskRiskPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }


    /**
     * @param array $params
     *
     * @return array|object
     */
    public function addRisk(array $params = []): array|object
    {
        $insert = new Insert($this->tableTaskRisk);
        $insert->values($params);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }

        $id = $result->getGeneratedValue();

        return $this->getRisk(['id' => $id]);
    }

    /**
     * @param array $where
     * @param array $set
     */
    public function updateRisk(array $where, array $set): void
    {
        $update = new Update($this->tableTaskRisk);
        $update->set($set);
        $update->where($where);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    /**
     * @param array $params
     *
     * @return array|object
     */
    public function getRisk(array $params = []): array|object
    {
        $where = [];
        if (isset($params['id'])) {
            $where['id'] = (int)$params['id'];
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
        $select = $sql->select($this->tableTaskRisk)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving blog post with identifier "%s"; unknown database error.',
                    $params
                )
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->taskRiskPrototype);
        $resultSet->initialize($result);
        $risk = $resultSet->current();

        if (!$risk) {
            return [];
        }

        return $risk;
    }

    /**
     * @param array $params
     *
     * @return HydratingResultSet
     */
    public function getRiskList(array $params = []): HydratingResultSet|array
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

        $order = ['time_create ASC', 'id ASC'];

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableTaskRisk)->where($where)->order($order);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->taskRiskPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }


    /**
     * @param array $params
     *
     * @return array|object
     */
    public function addAudit(array $params = []): array|object
    {
        $insert = new Insert($this->tableTaskAudit);
        $insert->values($params);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }

        $id = $result->getGeneratedValue();

        return $this->getAudit(['id' => $id]);
    }

    /**
     * @param array $where
     * @param array $set
     */
    public function updateAudit(array $where, array $set): void
    {
        $update = new Update($this->tableTaskAudit);
        $update->set($set);
        $update->where($where);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    /**
     * @param array $params
     *
     * @return array|object
     */
    public function getAudit(array $params = []): array|object
    {
        $where = [];
        if (isset($params['id']) && (int)$params['id'] > 0) {
            $where['id'] = (int)$params['id'];
        } elseif (isset($params['slug']) && !empty($params['slug'])) {
            $where['slug'] = $params['slug'];
        }

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableTaskAudit)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving blog post with identifier "%s"; unknown database error.',
                    $params
                )
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->taskAuditPrototype);
        $resultSet->initialize($result);
        $audit = $resultSet->current();

        if (!$audit) {
            return [];
        }

        return $audit;
    }

    /**
     * @param array $params
     *
     * @return HydratingResultSet
     */
    public function getAuditList(array $params = []): HydratingResultSet|array
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

        $order = ['time_create ASC', 'id ASC'];

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableTaskAudit)->where($where)->order($order);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->taskAuditPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }


    /**
     * @param array $params
     *
     * @return int
     */
    public function getRulesCount(array $params = []): int
    {
        // Set where
        $columns = ['count' => new Expression('count(*)')];

        $where = $this->createConditional($params);

        if (isset($params['author']) && !empty($params['author']))
            $where['author'] = $params['author'];
        if (isset($params['category']) && !empty($params['category']))
            $where['category'] = $params['category'];
        if (isset($params['type']) && !empty($params['type']))
            $where['type'] = $params['type'];
        if (isset($params['target']) && !empty($params['target']))
            $where['target'] = $params['target'];
        if (isset($params['is_creditable']))
            $where['is_creditable'] = $params['is_creditable'];
        if (isset($params['status']))
            $where['status'] = $params['status'];

        if (!empty($params['approval_at_from'])) {
            $where['time_create >= ?'] = $params['approval_at_from'];
        }
        if (!empty($params['approval_at_to'])) {
            $where['time_create <= ?'] = $params['approval_at_to'];
        }
        if (!empty($params['cancellation_at_from'])) {
            $where['time_create >= ?'] = $params['cancellation_at_from'];
        }
        if (!empty($params['cancellation_at_to'])) {
            $where['time_create <= ?'] = $params['cancellation_at_to'];
        }
        if (!empty($params['promulgation_at_from'])) {
            $where['time_create >= ?'] = $params['promulgation_at_from'];
        }
        if (!empty($params['promulgation_at_to'])) {
            $where['time_create <= ?'] = $params['promulgation_at_to'];
        }

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableRule)->columns($columns)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute()->current();

        return (int)$row['count'];
    }


    public function getRulesOld(): HydratingResultSet|array
    {
        $where = [];
        $sql = new Sql($this->db);
        $select = $sql->select($this->tableRule)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }
        $resultSet = new HydratingResultSet($this->hydrator, $this->rulePrototype);
        $rules = $resultSet->initialize($result);
        return $rules;
    }

    public function getRules($params): HydratingResultSet|array
    {
        $where = $this->createConditional($params);
        if (isset($params['author']) && !empty($params['author']))
            $where['author'] = $params['author'];
        if (isset($params['category']) && !empty($params['category']))
            $where['category'] = $params['category'];
        if (isset($params['type']) && !empty($params['type']))
            $where['type'] = $params['type'];
        if (isset($params['target']) && !empty($params['target']))
            $where['target'] = $params['target'];
        if (isset($params['is_creditable']))
            $where['is_creditable'] = $params['is_creditable'];

        if (isset($params['status']))
            $where['status'] = $params['status'];

        if (!empty($params['approval_at_from'])) {
            $where['approval_at >= ?'] = $params['approval_at_from'];
        }
        if (!empty($params['approval_at_to'])) {
            $where['approval_at <= ?'] = $params['approval_at_to'];
        }
        if (!empty($params['cancellation_at_from'])) {
            $where['cancellation_at >= ?'] = $params['cancellation_at_from'];
        }
        if (!empty($params['cancellation_at_to'])) {
            $where['cancellation_at <= ?'] = $params['cancellation_at_to'];
        }
        if (!empty($params['promulgation_at_from'])) {
            $where['promulgation_at >= ?'] = $params['promulgation_at_from'];
        }
        if (!empty($params['promulgation_at_to'])) {
            $where['promulgation_at <= ?'] = $params['promulgation_at_to'];
        }


        $sql = new Sql($this->db);
        $select = $sql->select($this->tableRule)->where($where)->order($params['order'])->offset($params['offset'])->limit($params['limit']);;
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }
        $resultSet = new HydratingResultSet($this->hydrator, $this->rulePrototype);
        $rules = $resultSet->initialize($result);
        return $rules;
    }

    /**
     * @param $params
     * @return object|array
     */
    public function getRule($params): object|array
    {
        $where = $params;
        $sql = new Sql($this->db);
        $select = $sql->select($this->tableRule)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving blog post with identifier "%s"; unknown database error.',
                    $params
                )
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->rulePrototype);
        $resultSet->initialize($result);
        $rule = $resultSet->current();

        if (!$rule) {
            return [];
        }
        return $rule;
    }


    public function storeRule(array $params = []): object|array
    {

        $where = [];
        $sql = new Sql($this->db);
        $insert = new Insert($this->tableRule);
        $insert->values($params);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }
        $id = $result->getGeneratedValue();
        return $this->getRule(['id' => $id]);
    }

    public function updateRule(array $params = []): array|object
    {

        $where['id'] = $params['id'];
        $update = new Update($this->tableRule);
        $update->set($params);
        $update->where($where);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();

        return $this->getRule(['id' => $params['id']]);
    }


    public function getWarranties()
    {
        $where = [];
        $order = ['id ASC'];
        $sql = new Sql($this->db);
        $select = $sql->select($this->tableWarranty)->where($where)->order($order);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }
        $resultSet = new HydratingResultSet($this->hydrator, $this->warrantyPrototype);
        $warranties = $resultSet->initialize($result);
        return $warranties;
    }

    public function getMandatoryUnitList(): HydratingResultSet|array
    {
        $where = [];
        $order = ['id ASC'];
        $sql = new Sql($this->db);
        $select = $sql->select($this->tableMandatoryUnit)->where($where)->order($order);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }
        $resultSet = new HydratingResultSet($this->hydrator, $this->mandatoryUnitPrototype);
        return $resultSet->initialize($result);
    }

    public function storeTask(array $params = [])
    {
        $where = [];
        $sql = new Sql($this->db);
        $insert = new Insert($this->tableTaskList);
        $insert->values($params);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();
        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }
        $id = $result->getGeneratedValue();
        return $this->getTask(['id' => $id]);
    }

    public function updateTask(array $params = [])
    {
        $where['id'] = $params['id'];
        $update = new Update($this->tableTaskList);
        $update->set($params);
        $update->where($where);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();
        return $this->getTask(['id' => $params['id']]);
    }


    private function createConditional(array $params): array
    {
        $where = [];
        //$where['time_delete'] = 0;
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
            $where['validity'] = (int)$params['validity'];
        }
        if (isset($params['requirement'])) {
            $where['requirement'] = (int)$params['requirement'];
        }

        if (isset($params['warranty_id'])) {
            if (!empty($params['warranty_id']))
                $where['warranty_id'] = $params['warranty_id'];
        }
        if (isset($params['rule_id'])) {
            if (!empty($params['rule_id']))
                $where['rule_id'] = $params['rule_id'];
        }
        if (isset($params['section_id'])) {
            if (!empty($params['section_id']))
                $where['section_id'] = $params['section_id'];
        }
        if (isset($params['reference_id'])) {
            $where['reference_id'] = $params['reference_id'];
        }
        return $where;
    }


    /**
     * @param array $params
     *
     * @return HydratingResultSet
     */
    public function getTaskList(array $params = []): HydratingResultSet|array
    {


        $where = $this->createConditional($params);
        // Add condition for searching within JSON field
        if (isset($params['mandatory_unit']) && !empty($params['mandatory_unit'])) {
            $jsonSearchConditions = [];
            foreach ($params['mandatory_unit'] as $slug) {
                $jsonSearchConditions[] = "JSON_SEARCH(mandatory_unit, 'one', '$slug', NULL, '$[*].slug') IS NOT NULL";
            }
            $where[] = '(' . implode(' OR ', $jsonSearchConditions) . ')';
        }

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableTaskList)->where($where)->order($params['order'])->offset($params['offset'])->limit($params['limit']);;
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }
        $resultSet = new HydratingResultSet($this->hydrator, $this->taskListPrototype);
        return $resultSet->initialize($result);
    }


    /**
     * @param array $params
     *
     * @return int
     */
    public function getTaskCount(array $params = []): int
    {
        // Set where
        $columns = ['count' => new Expression('count(*)')];

        $where = $this->createConditional($params);
        // Add condition for searching within JSON field
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

        return (int)$row['count'];
    }

    public function getTaskIdFromComplianceProgress(array $filter = []): HydratingResultSet|array
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

    public function getTaskIdFromRiskProgress(array $filter = []): HydratingResultSet|array
    {
        $where = [];
        $sql = new Sql($this->db);
        $select = $sql->select($this->tableTaskRisk)->where($where);

        if ($filter['type'] == 'value') {
            $select->where([$filter['field'] => $filter['value']]);
        }
        if ($filter['type'] == 'rangeMax') {
            $select->where([$filter['field'] . '  <= ?' => $filter['value']]);
        }
        if ($filter['type'] == 'rangeMin') {
            $select->where([$filter['field'] . '  >= ?' => $filter['value']]);

        }


        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->taskRiskPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }


    public function getMandatoryUnitMemberList($params): object|array
    {
        $where = $this->createConditional($params);
        // Add condition for searching within JSON field
        if (isset($params['mandatory_unit']) && !empty($params['mandatory_unit'])) {
            $jsonSearchConditions = [];
            foreach ($params['mandatory_unit'] as $slug) {
                $jsonSearchConditions[] = "JSON_SEARCH(mandatory_unit, 'one', '$slug', NULL, '$[*].slug') IS NOT NULL";
            }
            $where[] = '(' . implode(' OR ', $jsonSearchConditions) . ')';
        }


        $sql = new Sql($this->db);
        $select = $sql->select($this->tableMandatoryUnitMember)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }
        $resultSet = new HydratingResultSet($this->hydrator, $this->mandatoryUnitMemberPrototype);
        return $resultSet->initialize($result);
    }

    public function getMandatoryUnitMember($params): object|array
    {
        $where = $params;
        $sql = new Sql($this->db);
        $select = $sql->select($this->tableMandatoryUnitMember)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving blog post with identifier "%s"; unknown database error.',
                    $params
                )
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->mandatoryUnitMemberPrototype);
        $resultSet->initialize($result);
        $result = $resultSet->current();

        if (!$result) {
            return [];
        }
        return $result;
    }


    public function storeMandatoryUnitMember(array $params = []): object|array
    {
        $insert = new Insert($this->tableMandatoryUnitMember);
        $insert->values($params);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }
        $id = $result->getGeneratedValue();
        return $this->getMandatoryUnitMember(['id' => $id]);
    }

    public function updateMandatoryUnitMember(array $params = []): array|object
    {
        $where['id'] = $params['id'];
        $update = new Update($this->tableMandatoryUnitMember);
        $update->set($params);
        $update->where($where);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();

        return $this->getRule(['id' => $params['id']]);
    }

    public function destroyMandatoryUnitMember(array $params = []): void
    {
        $where['user_id'] = $params['user_id'];
        $delete = new Delete($this->tableMandatoryUnitMember);
        $delete->where($where);
        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($delete);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    public function addErmMeta($params): object|array
    {

        $insert = new Insert($this->tableErmMeta);
        $insert->values($params);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();
        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }
        $id = $result->getGeneratedValue();
        return $this->getErmMeta(['id' => $id]);
    }


    /**
     * @param array $params
     *
     * @return array|object
     */
    public function getErmMeta(array $params = []): array|object
    {
        // Set
        $where = [];
        if (isset($params['id']) && (int)$params['id'] > 0) {
            $where['id'] = (int)$params['id'];
        }

        $sql = new Sql($this->db);
        $select = $sql->select($this->tableErmMeta)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving blog post with identifier "%s"; unknown database error.',
                    $params
                )
            );
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->ermMetaPortotype);
        $resultSet->initialize($result);
        $meta = $resultSet->current();

        if (!$meta) {
            return [];
        }

        return $meta;
    }

    /**
     * @param array $params
     *
     * @return HydratingResultSet|array
     */
    public function getErmMetaList(array $params = []): HydratingResultSet|array
    {
        $where = $this->createConditional($params);
        if (isset($params['type'])) {
            // remove type condition
            unset($where['type']);
            $where[] = " JSON_CONTAINS(type, '" . json_encode($params['type']) . "' ) ";
        }
        if (isset($params['target'])) {
            $where[] = " JSON_CONTAINS(target, '" . json_encode($params['target']) . "' ) ";
        }
        $sql = new Sql($this->db);
        $select = $sql->select($this->tableErmMeta)->where($where)->order($params['order'])->offset($params['offset'])->limit($params['limit']);;
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }
        $resultSet = new HydratingResultSet($this->hydrator, $this->ermMetaPortotype);
        return $resultSet->initialize($result);
    }


    /**
     * @param array $params
     *
     * @return int
     */
    public function getErmMetaCount(array $params = []): int
    {
        $columns = ['count' => new Expression('count(*)')];
        $where = $this->createConditional($params);
        if (isset($params['type'])) {
            // remove type condition
            unset($where['type']);
            $where[] = " JSON_CONTAINS(type, '" . json_encode($params['type']) . "' ) ";
        }
        if (isset($params['target'])) {
            $where[] = " JSON_CONTAINS(target, '" . json_encode($params['target']) . "' ) ";
        }
        $sql = new Sql($this->db);
        $select = $sql->select($this->tableErmMeta)->columns($columns)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute()->current();
        return (int)$row['count'];
    }

    /**
     * @param array $params
     *
     * @return object|array
     */
    public function editErmMeta(array $params = []): object|array|int
    {
        $where = [];
        if (isset($params['slug']) && !empty($params['slug'])) {
            $where['slug'] = $params['slug'];
        }
        if (isset($params['id']) && !empty($params['id'])) {
            $where['id'] = $params['id'];
        }
        $update = new Update($this->tableErmMeta);
        $update->set($params);
        $update->where($where);
        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();
        return $this->getErmMeta($where);
    }


    public function getAuditProgress(array $params = []): array|object
    {
        $where = [];
        if (isset($params['id'])) {
            $where['id'] = (int)$params['id'];
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
        $select = $sql->select($this->tableAuditTaskProgress)->where($where);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            throw new RuntimeException(
                sprintf(
                    'Failed retrieving blog post with identifier "%s"; unknown database error.',
                    $params
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

    /**
     * @param array $params
     *
     * @return array|object
     */
    public function addAuditProgress(array $params = []): array|object
    {
        $insert = new Insert($this->tableAuditTaskProgress);
        $insert->values($params);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }

        $id = $result->getGeneratedValue();

        return $this->getProgress(['id' => $id]);
    }

    /**
     * @param array $where
     * @param array $set
     */
    public function updateAuditProgress(array $where, array $set): void
    {
        $update = new Update($this->tableAuditTaskProgress);
        $update->set($set);
        $update->where($where);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during update operation'
            );
        }
    }

    /**
     * @param array $params
     *
     * @return HydratingResultSet|array
     */
    public function getAuditProgressList(array $params = []): HydratingResultSet|array
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
        $select = $sql->select($this->tableAuditTaskProgress)->where($where)->order($order);
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->taskProgressPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }
}