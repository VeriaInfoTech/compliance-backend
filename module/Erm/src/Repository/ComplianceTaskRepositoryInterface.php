<?php

namespace Erm\Repository;

use Laminas\Db\ResultSet\HydratingResultSet;

interface ComplianceTaskRepositoryInterface
{
    public function getComplianceTaskList(array $params = []): HydratingResultSet|array;

    public function getComplianceTaskCount(array $params = []): int;

    public function getComplianceProgressList(array $params = []): HydratingResultSet|array;

    public function getComplianceTaskIdsFromProgress(array $filter = []): HydratingResultSet|array;

    public function getComplianceProgress(array $params = []): array|object;
}
