<?php

namespace Pi\Notification\Repository;

use Laminas\Db\ResultSet\HydratingResultSet;

interface NotificationRepositoryInterface
{
    public function getNotificationList(array $params = []): HydratingResultSet|array;

    public function getNotificationCount(array $params = []): int;

    public function getUnreadNotificationCount(array $params = []): int;

    public function addNotification(array $params): object|array;

    public function getNotification($parameter, $type = 'id'): object|array;

    public function updateNotification(int $notificationId, array $params): object|array;

    public function deleteNotification(array $params): void;
}