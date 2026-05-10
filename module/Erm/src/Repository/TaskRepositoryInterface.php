<?php

namespace Erm\Repository;

use Laminas\Db\ResultSet\HydratingResultSet;

interface TaskRepositoryInterface
{
    public function getTaskSectionList(array $params = []): HydratingResultSet|array;

    public function getTaskListOld(array $params = []): HydratingResultSet|array;

    public function getTaskList(array $params = []): HydratingResultSet|array;

    public function getTask(array $params = []): array|object;

    /**
     * Decoded JSON from erm_task_list.information (e.g. compliance snapshot on risk tasks).
     */
    public function getTaskInformationDecoded(int $taskId): ?array;

    public function storeTask(array $params = []);

    public function updateTask(array $params = []);

    public function addProgress(array $params = []): array|object;

    public function updateProgress(array $where, array $set): void;

    public function getProgress(array $params = []): array|object;

    public function getProgressList(array $params = []): HydratingResultSet|array;

    public function getRiskProgressList(array $params = []): HydratingResultSet|array;

    public function addRisk(array $params = []): array|object;

    public function updateRisk(array $where, array $set): void;

    public function getRisk(array $params = []): array|object;

    public function getRiskList(array $params = []): HydratingResultSet|array;

    public function getRules($params);

    public function storeRule(array $params = []);

    public function updateRule(array $params = []);

    public function getWarranties();

    public function getRulesCount(array $params);

    public function addErmMeta(array $params);
    public function getErmMetaList(array $params);
    public function getErmMetaCount(array $params);
    public function editErmMeta(array $params);
    public function getAuditProgress(array $params = []): array|object;

    public function addAuditProgress(array $params);
    public function updateAuditProgress(array $where, array $set): void;
    public function getAuditProgressList(array $params);

}