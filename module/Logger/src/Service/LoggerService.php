<?php

namespace Pi\Logger\Service;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pi\Core\Service\UtilityService;
use Pi\Logger\Repository\LogRepositoryInterface;
use Psr\Log\LogLevel;

class LoggerService implements ServiceInterface
{
    /** @var LogRepositoryInterface */
    protected LogRepositoryInterface $logRepository;

    /** @var UtilityService */
    protected UtilityService $utilityService;

    /* @var array */
    protected array $config;

    /* @var array */
    protected static array $priorities
        = [
            LogLevel::EMERGENCY => 0,
            LogLevel::ALERT     => 1,
            LogLevel::CRITICAL  => 2,
            LogLevel::ERROR     => 3,
            LogLevel::WARNING   => 4,
            LogLevel::NOTICE    => 5,
            LogLevel::INFO      => 6,
            LogLevel::DEBUG     => 7,
        ];

    protected array $systemLogColumns
        = [
            'ip',
            'path',
            'message',
            'user_id',
            'company_id',
            'target',
            'module',
            'section',
            'package',
            'handler',
            'permissions',
        ];

    protected array $userLogColumns
        = [
            'ip',
            'method',
        ];

    public function __construct(
        LogRepositoryInterface $logRepository,
        UtilityService         $utilityService,
                               $config
    ) {
        $this->logRepository  = $logRepository;
        $this->utilityService = $utilityService;
        $this->config         = $config;
    }

    public function write(array $params = [], string $level = LogLevel::INFO): void
    {
        // Clean up
        $params = $this->cleanupForbiddenKeys($params);

        // Save log
        $storage = $this->config['storage'] ?? 'disable';
        switch ($storage) {
            case 'mysql':
                $this->writeToMysql($params, $level);
                break;

            case 'mongodb':
                $this->writeToMongo($params, $level);
                break;

            case 'file':
                $this->writeToFile($params, $level);
                break;

            case '':
            case 'disable':
            default:
                break;
        }
    }

    public function writeToMysql(array $params, string $level): void
    {
        // Set log params
        $addParams = [
            'path'        => $params['path'] ?? '',
            'message'     => $params['message'] ?? '',
            'priority'    => $this->getPriority($level),
            'level'       => $level,
            'user_id'     => (int)$params['user_id'],
            'timestamp'   => $this->utilityService->getTime(),
            'time_create' => time(),
            'information' => json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK),
        ];
        // Save log to local db (company_id omitted so INSERT works if table has no column; schema default is 0)
        $this->logRepository->addSystemLog($addParams);
    }

    public function writeToMongo(array $params, string $level): void
    {
        // Set log params
        $addParams = [
            'path'        => $params['path'] ?? '',
            'message'     => $params['message'] ?? '',
            'priority'    => $this->getPriority($level),
            'level'       => $level,
            'user_id'     => (int)$params['user_id'],
            'company_id'  => (int)$params['company_id'],
            'timestamp'   => $this->utilityService->getTime(),
            'time_create' => time(),
            'information' => $params,
        ];

        // Save log
        $bulk = new BulkWrite();
        $bulk->insert($addParams);

        $manager = new Manager($this->config['mongodb']['uri']);
        $manager->executeBulkWrite("{$this->config['mongodb']['database']}.{$this->config['mongodb']['collection']}", $bulk);
    }

    public function writeToFile(array $params, string $level): void
    {
        // Set file path
        $logFilePath = sprintf('%s/%s.json', $this->config['file']['path'], date($this->config['file']['date_format']));

        // Create a new Logger instance
        $logger = new Logger('logger_system');

        // Create a StreamHandler
        $streamHandler = new StreamHandler($logFilePath, $level);

        // Attach a JsonFormatter to the handler
        $streamHandler->setFormatter(new JsonFormatter());

        // Add the handler to the logger
        $logger->pushHandler($streamHandler);

        // Add log entries
        $message = $params['message'] ?? $params['path'] ?? '';
        switch ($level) {
            case LogLevel::EMERGENCY:
                $logger->emergency($message, $params);
                break;

            case LogLevel::ALERT:
                $logger->alert($message, $params);
                break;

            case LogLevel::CRITICAL:
                $logger->critical($message, $params);
                break;

            case LogLevel::ERROR:
                $logger->error($message, $params);
                break;

            case LogLevel::WARNING:
                $logger->warning($message, $params);
                break;

            case LogLevel::NOTICE:
                $logger->notice($message, $params);
                break;

            case LogLevel::DEBUG:
                $logger->debug($message, $params);
                break;

            default:
            case LogLevel::INFO:
                $logger->info($message, $params);
                break;
        }
    }

    public function read($params): array
    {
        // Save log
        $storage = $this->config['storage'] ?? 'disable';
        switch ($storage) {
            case 'mysql':
                return $this->readFromMysql($params);
                break;

            case 'mongodb':
                return $this->readFromMongo($params);
                break;

            case '':
            case 'disable':
            default:
                return [];
                break;
        }
    }

    /**
     * System/activity log entries for a user (wraps {@see read()} with a stable empty payload).
     * Used by ERM member view and admin inventory-style listings.
     */
    public function readInventoryLog(array $params): array
    {
        $out = $this->read($params);
        if (!is_array($out) || !isset($out['data']['list'])) {
            $limit = (int) ($params['limit'] ?? 25);
            $page = (int) ($params['page'] ?? 1);

            return [
                'result' => true,
                'data' => [
                    'list' => [],
                    'paginator' => [
                        'count' => 0,
                        'limit' => $limit,
                        'page' => $page,
                    ],
                ],
                'error' => [],
            ];
        }

        return $out;
    }

    public function readFromMysql($params): array
    {
        $limit  = (int)($params['limit'] ?? 25);
        $page   = (int)($params['page'] ?? 1);
        $order  = $params['order'] ?? ['log.time_create DESC', 'log.id DESC'];
        $offset = ($page - 1) * $limit;

        // Set params
        $listParams = [
            'order'  => $order,
            'offset' => $offset,
            'limit'  => $limit,
            'page'   => $page,
        ];

        if (isset($params['identity']) && !empty($params['identity'])) {
            $listParams['identity'] = $params['identity'];
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $listParams['name'] = $params['name'];
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $listParams['email'] = $params['email'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $listParams['mobile'] = $params['mobile'];
        }
        if (isset($params['priority']) && !empty($params['priority'])) {
            $listParams['priority'] = $params['priority'];
        }
        if (isset($params['level']) && !empty($params['level'])) {
            $listParams['level'] = $params['level'];
        }
        if (isset($params['message']) && !empty($params['message'])) {
            $listParams['message'] = $params['message'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $listParams['user_id'] = $params['user_id'];
        }
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $listParams['company_id'] = $params['company_id'];
        }
        if (isset($params['data_from']) && !empty($params['data_from'])) {
            $listParams['data_from'] = strtotime(sprintf('%s 00:00:00', $params['data_from']));
        }
        if (isset($params['data_to']) && !empty($params['data_to'])) {
            $listParams['data_to'] = strtotime(sprintf('%s 00:00:00', $params['data_to']));
        }

        // Set for information
        if (isset($params['ip']) && !empty($params['ip'])) {
            $listParams['information']['ip'] = $params['ip'];
        }
        if (isset($params['method']) && !empty($params['method'])) {
            $listParams['information']['request.method'] = $params['method'];
        }
        if (isset($params['target']) && !empty($params['target'])) {
            $listParams['information']['request.target'] = $params['target'];
        }
        if (isset($params['module']) && !empty($params['module'])) {
            $listParams['information']['route.module'] = $params['module'];
        }
        if (isset($params['section']) && !empty($params['section'])) {
            $listParams['information']['route.section'] = $params['section'];
        }
        if (isset($params['package']) && !empty($params['package'])) {
            $listParams['information']['route.package'] = $params['package'];
        }
        if (isset($params['handler']) && !empty($params['handler'])) {
            $listParams['information']['route.handler'] = $params['handler'];
        }
        if (isset($params['permissions']) && !empty($params['permissions'])) {
            $listParams['information']['route.permissions'] = $params['permissions'];
        }

        $list       = [];
        $systemList = $this->logRepository->getSystemLogList($listParams, $this->systemLogColumns);
        foreach ($systemList as $object) {
            $list[] = $this->canonizeSystemLogMysql($object);
        }

        // Get count
        $count = $this->logRepository->getSystemLogCount($listParams, $this->systemLogColumns);

        return [
            'result' => true,
            'data'   => [
                'list'      => $list,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page'  => $page,
                ],
            ],
            'error'  => [],
        ];
    }

    public function readFromMongo($params): array
    {
        $limit = (int)($params['limit'] ?? 25);
        $page  = (int)($params['page'] ?? 1);
        //$sort  = $params['order'] ?? ['time_create -1'];
        $skip = ($page - 1) * $limit;

        // Set options
        $options = [
            'skip'  => $skip,
            'limit' => $limit,
            //'sort' => $sort
        ];

        // Set filters
        $filter = [];
        /* if (isset($params['identity']) && !empty($params['identity'])) {
            $filter['identity'] = $params['identity'];
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $filter['name'] = $params['name'];
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $filter['email'] = $params['email'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $filter['mobile'] = $params['mobile'];
        }
        if (isset($params['priority']) && !empty($params['priority'])) {
            $filter['priority'] = $params['priority'];
        }
        if (isset($params['level']) && !empty($params['level'])) {
            $filter['level'] = $params['level'];
        }
        if (isset($params['message']) && !empty($params['message'])) {
            $filter['message'] = $params['message'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $filter['user_id'] = $params['user_id'];
        }
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $filter['company_id'] = $params['company_id'];
        }
        if (isset($params['data_from']) && !empty($params['data_from'])) {
            $filter['data_from'] = strtotime(sprintf('%s 00:00:00', $params['data_from']));
        }
        if (isset($params['data_to']) && !empty($params['data_to'])) {
            $filter['data_to'] = strtotime(sprintf('%s 00:00:00', $params['data_to']));
        } */
        if (isset($params['ip']) && !empty($params['ip'])) {
            $filter['information.ip'] = $params['ip'];
        }
        /* if (isset($params['method']) && !empty($params['method'])) {
            $filter['method'] = $params['method'];
        }
        if (isset($params['target']) && !empty($params['target'])) {
            $filter['target'] = $params['target'];
        }
        if (isset($params['module']) && !empty($params['module'])) {
            $filter['module'] = $params['module'];
        }
        if (isset($params['section']) && !empty($params['section'])) {
            $filter['section'] = $params['section'];
        }
        if (isset($params['package']) && !empty($params['package'])) {
            $filter['package'] = $params['package'];
        }
        if (isset($params['handler']) && !empty($params['handler'])) {
            $filter['handler'] = $params['handler'];
        }
        if (isset($params['permissions']) && !empty($params['permissions'])) {
            $filter['permissions'] = $params['permissions'];
        } */

        $query     = new Query($filter, $options);
        $namespace = "{$this->config['mongodb']['database']}.{$this->config['mongodb']['collection']}";

        $manager = new Manager($this->config['mongodb']['uri']);
        $cursor  = $manager->executeQuery($namespace, $query);

        $list = [];
        foreach ($cursor as $document) {
            $list[] = $this->canonizeSystemLogMongo($document);
        }

        // Execute the query to count the number of matching documents
        $count  = 0;
        $cursor = $manager->executeQuery($namespace, $query);
        foreach ($cursor as $document) {
            $count++;
        }

        return [
            'result' => true,
            'data'   => [
                'list'      => $list,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page'  => $page,
                ],
            ],
            'error'  => [],
        ];
    }

    public function cleanupForbiddenKeys(array $params): array
    {
        $forbiddenKeys = $this->config['forbidden_keys'] ?? [];

        // If no forbidden keys are defined, return the input unchanged
        if (empty($forbiddenKeys)) {
            return $params;
        }

        $forbidden = array_flip($forbiddenKeys);
        foreach ($params as $key => $value) {
            if (isset($forbidden[$key])) {
                unset($params[$key]);
            } elseif (is_array($value)) {
                $params[$key] = $this->cleanupForbiddenKeys($value);
            }
        }

        return $params;
    }


    public function addUserLog(string $state, array $params): void
    {
        $params = [
            'user_id'     => (int)($params['account']['id'] ?? 0),
            'operator_id' => (int)(isset($params['operator']) ? !empty($params['operator']) ? $params['operator']['id'] ?? 0 : 0 : 0),
            'time_create' => time(),
            'state'       => $state,
            'information' => json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK),
        ];

        $this->logRepository->addUserLog($params);
    }

    public function getUserLog($params): array
    {
        $limit  = (int)($params['limit'] ?? 25);
        $page   = (int)($params['page'] ?? 1);
        $order  = $params['order'] ?? ['time_create DESC'];
        $offset = ($page - 1) * $limit;

        $listParams = [
            'order'  => $order,
            'offset' => $offset,
            'limit'  => $limit,
        ];

        if (isset($params['identity']) && !empty($params['identity'])) {
            $listParams['identity'] = $params['identity'];
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $listParams['name'] = $params['name'];
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $listParams['email'] = $params['email'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $listParams['mobile'] = $params['mobile'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $listParams['user_id'] = $params['user_id'];
        }
        if (isset($params['operator_id']) && !empty($params['operator_id'])) {
            $listParams['operator_id'] = $params['operator_id'];
        }
        if (isset($params['state']) && !empty($params['state'])) {
            $listParams['state'] = $params['state'];
        }
        if (isset($params['data_from']) && !empty($params['data_from'])) {
            $listParams['data_from'] = strtotime(sprintf('%s 00:00:00', $params['data_from']));
        }
        if (isset($params['data_to']) && !empty($params['data_to'])) {
            $listParams['data_to'] = strtotime(sprintf('%s 00:00:00', $params['data_to']));
        }

        // Set for information
        if (isset($params['ip']) && !empty($params['ip'])) {
            $listParams['information']['ip'] = $params['ip'];
        }
        if (isset($params['method']) && !empty($params['method'])) {
            $listParams['information']['method'] = $params['method'];
        }

        // Get list
        $list   = [];
        $rowSet = $this->logRepository->getUserLogList($listParams, $this->userLogColumns);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeUserLog($row);
        }

        // Get count
        $count = $this->logRepository->getUserLogCount($listParams, $this->userLogColumns);

        return [
            'result' => true,
            'data'   => [
                'list'      => $list,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page'  => $page,
                ],
            ],
            'error'  => [],
        ];
    }

    public function addHistoryLog(array $params): void
    {
        $addParams = [
            'relation_module'  => $params['relation_module'],
            'relation_section' => $params['relation_section'],
            'relation_item'    => $params['relation_item'],
            'user_id'          => $params['user_id'] ?? 0,
            'time_create'      => time(),
            'state'            => $params['state'] ?? 'update',
            'information'      => json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK),
        ];
        $this->logRepository->addHistoryLog($addParams);
    }

    public function getHistoryLog($params): array
    {
        $limit  = (int)($params['limit'] ?? 25);
        $page   = (int)($params['page'] ?? 1);
        $order  = $params['order'] ?? ['time_create DESC'];
        $offset = ($page - 1) * $limit;

        $listParams = [
            'order'  => $order,
            'offset' => $offset,
            'limit'  => $limit,
        ];

        if (isset($params['identity']) && !empty($params['identity'])) {
            $listParams['identity'] = $params['identity'];
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $listParams['name'] = $params['name'];
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $listParams['email'] = $params['email'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $listParams['mobile'] = $params['mobile'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $listParams['user_id'] = $params['user_id'];
        }
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $listParams['company_id'] = $params['company_id'];
        }
        if (isset($params['company_id']) && !empty($params['company_id'])) {
            $listParams['company_id'] = $params['company_id'];
        }
        if (isset($params['relation_module']) && !empty($params['relation_module'])) {
            $listParams['relation_module'] = $params['relation_module'];
        }
        if (isset($params['relation_section']) && !empty($params['relation_section'])) {
            $listParams['relation_section'] = $params['relation_section'];
        }
        if (isset($params['relation_item']) && !empty($params['relation_item'])) {
            $listParams['relation_item'] = $params['relation_item'];
        }
        if (isset($params['state']) && !empty($params['state'])) {
            $listParams['state'] = $params['state'];
        }

        // Get list
        $list   = [];
        $rowSet = $this->logRepository->getHistoryLogList($listParams);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeHistoryLog($row);
        }

        // Get count
        $count = $this->logRepository->getHistoryLogCount($listParams);

        return [
            'result' => true,
            'data'   => [
                'list'      => $list,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page'  => $page,
                ],
            ],
            'error'  => [],
        ];
    }

    public function getPriority(string $level): ?int
    {
        return self::$priorities[$level] ?? null;
    }

    public function checkRepository(): array
    {
        // Set defaults
        $cleanup          = $this->config['limitation']['cleanup'] ?? true;
        $maximumAllowed   = $this->config['limitation']['maximum_allowed'] ?? 10000;
        $alertThreshold   = 80;
        $cleanupThreshold = 95;
        $cleanupAmount    = 30;
        $rowsToDelete     = 0;

        // Check and set alert_threshold
        if (
            isset($this->config['limitation']['alert_threshold'])
            && (int)$this->config['limitation']['alert_threshold'] > 79
            && (int)$this->config['limitation']['alert_threshold'] < 89
        ) {
            $alertThreshold = $this->config['limitation']['alert_threshold'];
        }

        // Check and set cleanup_threshold
        if (
            isset($this->config['limitation']['cleanup_threshold'])
            && (int)$this->config['limitation']['cleanup_threshold'] > 89
            && (int)$this->config['limitation']['cleanup_threshold'] < 100
        ) {
            $cleanupThreshold = $this->config['limitation']['cleanup_threshold'];
        }

        // Check and set cleanup_amount
        if (
            isset($this->config['limitation']['cleanup_amount'])
            && (int)$this->config['limitation']['cleanup_amount'] > 20
            && (int)$this->config['limitation']['cleanup_amount'] < 35
        ) {
            $cleanupAmount = $this->config['limitation']['cleanup_amount'];
        }

        // Get count
        $count = $this->logRepository->getSystemLogCount();

        // Set percentage
        $percentage = round(($count / $maximumAllowed) * 100, 0);
        if ($percentage >= $alertThreshold) {
            if ($percentage >= $cleanupThreshold) {

                // cleanup logs
                $rowsToDelete = (int)ceil(($cleanupAmount / 100) * $count);
                $this->logRepository->cleanupSystemLog($rowsToDelete);

                // Set message
                $message = "Log storage exceeded cleanup threshold ({$percentage}%). Performed cleanup of {$rowsToDelete} records.";
                $alert   = 'warning';
            } else {
                $message = "Log storage is critically high ({$percentage}%). Immediate action is recommended.";
                $alert   = 'danger';
            }
        } else {
            $message = "Log storage usage is within safe limits ({$percentage}%).";
            $alert   = 'success';
        }

        return [
            'result' => true,
            'data'   => [
                'message'           => $message,
                'alert'             => $alert,
                'total_logs'        => $count,
                'allowed_logs'      => $maximumAllowed,
                'percentage'        => $percentage,
                'alert_threshold'   => $alertThreshold,
                'cleanup_threshold' => $cleanupThreshold,
                'cleanup_amount'    => $cleanupAmount,
                'rows_to_delete'    => $rowsToDelete,
            ],
            'error'  => [],
        ];
    }

    public function canonizeSystemLogMysql($object): array
    {
        if (empty($object)) {
            return [];
        }

        if (is_object($object)) {
            $object = [
                'id'            => (int)$object->getId(),
                'timestamp'     => $object->getTimestamp(),
                'priority'      => $object->getPriority(),
                'level'         => $object->getLevel(),
                'message'       => $object->getMessage(),
                'path'          => $object->getPath(),
                'information'   => $object->getInformation(),
                'time_create'   => $object->getTimeCreate(),
                'user_id'       => $object->getUserId(),
                'company_id'    => $object->getCompanyId(),
                'user_identity' => $object->getUserIdentity(),
                'user_name'     => $object->getUserName(),
                'user_email'    => $object->getUserEmail(),
                'user_mobile'   => $object->getUserMobile(),
            ];
        } else {
            $object = [
                'id'            => (int)$object['id'],
                'timestamp'     => $object['timestamp'],
                'priority'      => $object['priority'],
                'level'         => $object['level'],
                'message'       => $object['message'],
                'path'          => $object['path'],
                'information'   => $object['information'],
                'time_create'   => $object['time_create'],
                'user_id'       => $object['user_id'],
                'company_id'    => $object['company_id'],
                'user_identity' => $object['user_identity'],
                'user_name'     => $object['user_name'],
                'user_email'    => $object['user_email'],
                'user_mobile'   => $object['user_mobile'],
            ];
        }

        // Set time
        $object['time_create_view'] = $this->utilityService->date($object['time_create']);

        // Set information
        $object['information'] = !empty($object['information']) ? json_decode($object['information'], true) : [];

        // Set output params
        $object['company_title']   = $object['information']['request']['attributes']['account']['company_title'] ?? null;
        $object['ip']              = $object['information']['ip'] ?? null;
        $object['title']           = $object['information']['route']['title'] ?? null;
        $object['method']          = $object['information']['request']['method'] ?? null;
        $object['target']          = $object['information']['request']['target'] ?? null;
        $object['section']         = $object['information']['route']['section'] ?? null;
        $object['module']          = $object['information']['route']['module'] ?? null;
        $object['package']         = $object['information']['route']['package'] ?? null;
        $object['handler']         = $object['information']['route']['handler'] ?? null;
        $object['package_id']      = $object['information']['request']['attributes']['company_authorization']['package']['id'] ?? null;
        $object['package_title']   = $object['information']['request']['attributes']['company_authorization']['package']['title'] ?? null;
        $object['request_body']    = $object['information']['request']['parsedBody'] ?? null;
        $object['security_stream'] = $object['information']['request']['attributes']['security_stream'] ?? null;

        unset($object['information']);
        unset($object['timestamp']);

        return $object;
    }

    public function canonizeSystemLogMongo($object): array
    {
        $object = json_decode(json_encode($object), true);

        // Set time
        $object['time_create_view'] = $this->utilityService->date($object['time_create']);

        // Set output params
        $object['company_title']   = $object['information']['request']['attributes']['company_authorization']['company']['title'] ?? null;
        $object['ip']              = $object['information']['ip'] ?? null;
        $object['title']           = $object['information']['route']['title'] ?? null;
        $object['method']          = $object['information']['request']['method'] ?? null;
        $object['target']          = $object['information']['request']['target'] ?? null;
        $object['section']         = $object['information']['route']['section'] ?? null;
        $object['module']          = $object['information']['route']['module'] ?? null;
        $object['package']         = $object['information']['route']['package'] ?? null;
        $object['handler']         = $object['information']['route']['handler'] ?? null;
        $object['package_id']      = $object['information']['request']['attributes']['company_authorization']['package']['id'] ?? null;
        $object['package_title']   = $object['information']['request']['attributes']['company_authorization']['package']['title'] ?? null;
        $object['request_body']    = $object['information']['request']['parsedBody'] ?? null;
        $object['security_stream'] = $object['information']['request']['attributes']['security_stream'] ?? null;

        unset($object['information']);
        unset($object['timestamp']);

        return $object;
    }

    public function canonizeUserLog($object): array
    {
        if (empty($object)) {
            return [];
        }

        if (is_object($object)) {
            $object = [
                'id'                => (int)$object->getId(),
                'user_id'           => $object->getUserId(),
                'user_identity'     => $object->getUserIdentity(),
                'user_name'         => $object->getUserName(),
                'user_email'        => $object->getUserEmail(),
                'user_mobile'       => $object->getUserMobile(),
                'operator_id'       => $object->getOperatorId(),
                'operator_identity' => $object->getOperatorIdentity(),
                'operator_name'     => $object->getOperatorName(),
                'operator_email'    => $object->getOperatorEmail(),
                'operator_mobile'   => $object->getOperatorMobile(),
                'time_create'       => $object->getTimeCreate(),
                'state'             => $object->getState(),
                'information'       => $object->getInformation(),
            ];
        } else {
            $object = [
                'id'                => (int)$object['id'],
                'user_id'           => $object['user_id'],
                'user_identity'     => $object['user_identity'],
                'user_name'         => $object['user_name'],
                'user_email'        => $object['user_email'],
                'user_mobile'       => $object['user_mobile'],
                'operator_id'       => $object['operator_id'],
                'operator_identity' => $object['operator_identity'],
                'operator_name'     => $object['operator_name'],
                'operator_email'    => $object['operator_email'],
                'operator_mobile'   => $object['operator_mobile'],
                'time_create'       => $object['time_create'],
                'state'             => $object['state'],
                'information'       => $object['information'],
            ];
        }

        // Set data
        $object['time_create_view'] = $this->utilityService->date($object['time_create']);

        // Set information
        $object['information'] = json_decode($object['information'], true);

        // Unset not used data
        unset($object['information']['params']['serverParams']);
        unset($object['information']['request']['security_stream']);

        return $object;
    }

    public function canonizeHistoryLog($object): array
    {
        if (empty($object)) {
            return [];
        }

        if (is_object($object)) {
            $object = [
                'id'               => (int)$object->getId(),
                'user_id'          => $object->getUserId(),
                'user_identity'    => $object->getUserIdentity(),
                'user_name'        => $object->getUserName(),
                'user_email'       => $object->getUserEmail(),
                'user_mobile'      => $object->getUserMobile(),
                'company_id'       => $object->getCompanyId(),
                'relation_module'  => $object->getRelationModule(),
                'relation_section' => $object->getRelationSection(),
                'relation_item'    => $object->getRelationItem(),
                'time_create'      => $object->getTimeCreate(),
                'state'            => $object->getState(),
                'information'      => $object->getInformation(),
            ];
        } else {
            $object = [
                'id'               => (int)$object['id'],
                'user_id'          => $object['user_id'],
                'user_identity'    => $object['user_identity'],
                'user_name'        => $object['user_name'],
                'user_email'       => $object['user_email'],
                'user_mobile'      => $object['user_mobile'],
                'company_id'       => $object['company_id'],
                'relation_module'  => $object['relation_module'],
                'relation_section' => $object['relation_section'],
                'relation_item'    => $object['relation_item'],
                'time_create'      => $object['time_create'],
                'state'            => $object['state'],
                'information'      => $object['information'],
            ];
        }

        // Set data
        $object['time_create_view'] = $this->utilityService->date($object['time_create']);

        // Set information
        $object['information'] = json_decode($object['information'], true);

        return $object;
    }
}