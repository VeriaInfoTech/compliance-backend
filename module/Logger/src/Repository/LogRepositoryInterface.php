<?php

namespace Pi\Logger\Repository;

use Laminas\Db\ResultSet\HydratingResultSet;

interface LogRepositoryInterface
{
    public function addSystemLog(array $params = []): void;

    public function getSystemLogList(array $params = [], array $systemLogColumns = []): HydratingResultSet|array;

    public function getSystemLogCount(array $params = [], array $systemLogColumns = []): int;

    public function cleanupSystemLog(int $rowsToDelete): void;

    public function addUserLog(array $params = []): void;

    public function getUserLogList(array $params = [], array $userLogColumns = []): HydratingResultSet|array;

    public function getUserLogCount(array $params = [], array $userLogColumns = []): int;

    public function addHistoryLog(array $params = []): void;

    public function getHistoryLogList(array $params = []): HydratingResultSet|array;

    public function getHistoryLogCount(array $params = []): int;
}