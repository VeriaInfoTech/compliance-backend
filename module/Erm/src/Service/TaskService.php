<?php

namespace Erm\Service;

use Erm\Repository\TaskRepositoryInterface;
use Exception;
use IntlDateFormatter;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Cache\Service\StorageAdapterFactoryInterface;
use Laminas\Cache\Storage\Plugin\Serializer;
use Pi\Logger\Service\LoggerService;
use Pi\Media\Service\MediaService;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Writer;
use PhpParser\Node\Expr\Array_;
use stdClass;
use Transliterator;
use Pi\User\Model\Permission\Role;
use Pi\User\Service\AccountService;
use Pi\User\Service\HistoryService;
 use Pi\User\Service\RoleService;
use Pi\Core\Service\UtilityService;
use function array_values;
use function is_numeric;
use function is_object;
use function sprintf;
use function time;

class TaskService implements ServiceInterface
{
    /** @var RoleService */
    protected RoleService $roleService;

    /** @var AccountService */
    protected AccountService $accountService;

    /** @var LoggerService */
    protected LoggerService $loggerService;

    /* @var TaskRepositoryInterface */
    private TaskRepositoryInterface $taskRepository;

    protected UtilityService $utilityService;
    protected MediaService $mediaService;

    protected array $roadMap = [
        'reject' => ['doing', 'done', 'approve'],
        'todo' => ['doing', 'done', 'approve'],
        'doing' => ['done', 'approve'],
        'done' => ['approve'],
        'approve' => [], // No transitions from 'approve'
    ];

    protected array $config;
    protected array $cacheConfig;

    /* @var SimpleCacheDecorator */
    protected SimpleCacheDecorator $cache;

    public function __construct(
        StorageAdapterFactoryInterface $storageFactory,
        TaskRepositoryInterface $taskRepository,
        RoleService             $roleService,
        AccountService          $accountService,
        LoggerService           $loggerService,
        UtilityService          $utilityService,
        MediaService            $mediaService,
        array                   $config,
        array                   $cacheConfig,
    )
    {
        ini_set('error_reporting', E_ERROR);
        $this->taskRepository = $taskRepository;
        $this->roleService = $roleService;
        $this->accountService = $accountService;
        $this->loggerService = $loggerService;
        $this->utilityService = $utilityService;
        $this->mediaService = $mediaService;
        $this->config = $config;
        $cache = $storageFactory->create($cacheConfig['storage'], $cacheConfig['options'], $cacheConfig['plugins']);
        $cache->addPlugin(new Serializer());
        $this->cache = new SimpleCacheDecorator($cache);
    }

    public
    function getTaskDashboardData(array $params): array
    {
        return [
            'result' => true,
            'data' => [],
            'error' => [],
        ];
    }

    public
    function getTaskTree(array $params): array
    {
        $list = [];
        $sectionList = [];
        $progressList = [];

        $rowsSection = $this->taskRepository->getTaskSectionList(['standard_id' => 1]);
        $rowsTask = $this->taskRepository->getTaskListOld(['standard_id' => 1]);
        $rowsProgress = $this->taskRepository->getProgressList(['standard_id' => 1]);

        foreach ($rowsSection as $sectionSingle) {
            $sectionSingle = $this->canonizeTaskSection($sectionSingle);

            $sectionList[$sectionSingle['id']] = $sectionSingle;
        }


        foreach ($rowsProgress as $progressSingle) {
            $progressSingle = $this->canonizeTaskProgress($progressSingle);

            $progressList[$progressSingle['slug']] = $progressSingle;
        }

        $members = $this->listMember([]);
        foreach ($rowsTask as $taskSingle) {
            $taskSingle = $this->canonizeTaskList($taskSingle, $members);

            $slug = $this->generateSlug($taskSingle, $params);

            $taskSingle['progress'] = [];
            if (isset($progressList[$slug]) && !empty($progressList[$slug])) {
                $taskSingle['progress'] = $progressList[$slug];
            }

            $sectionList[$taskSingle['section_id']]['children'][] = $taskSingle;
        }

        // Make tree
        foreach ($sectionList as $sectionSingle) {
            if ($sectionSingle['parent_id'] == 0) {
                $sectionSingle['type'] = 'domain';
                $sectionSingle['child_type'] = 'sub_domain';
                $sectionSingle['children'] = [];
                $list[$sectionSingle['id']] = $sectionSingle;
            } else {
                $sectionSingle['type'] = 'sub_domain';
                $sectionSingle['child_type'] = 'task';
                $list[$sectionSingle['parent_id']]['children'][$sectionSingle['id']] = $sectionSingle;
            }
        }

        foreach ($list as $single) {
            $single['children'] = array_values($single['children']);
            $list[$single['id']] = $single;
        }

        return [
            'result' => true,
            'data' => array_values($list),
            'error' => [],
        ];
    }

    public
    function getTaskProgress(array $params): array
    {
        $task = $this->taskRepository->getTask(['id' => $params['task_id']]);
        $task = $this->canonizeTaskList($task, $this->listMember([]));

        $slug = $this->generateSlug($task, $params);

        $progress = $this->taskRepository->getProgress(['slug' => $slug]);
        $task['progress'] = $this->canonizeTaskProgress($progress);

        return [
            'result' => true,
            'data' => [
                $task,
            ],
            'error' => [],
        ];
    }

    public
    function updateTaskProgress(array $params): array
    {
        $task = $this->taskRepository->getTask(['id' => $params['task_id']]);
        $task = $this->canonizeTaskList($task, $this->listMember([]));

        $slug = $this->generateSlug($task, $params);

        $progress = $this->taskRepository->getProgress(['slug' => $slug]);
        $progress = $this->canonizeTaskProgress($progress);

        if (empty($progress)) {
            $paramsProgress = [
                'slug' => $slug,
                'standard_id' => $params['standard_id'],
                'section_id' => $task['section_id'],
                'task_id' => $task['id'],
                'user_id' => $params['user_id'],
                'company_id' => $params['company_id'],
                'time_create' => time(),
                'time_update' => time(),
                'level' => $params['level'],
                'answer_score' => $params['answer_score'],
                'answer_value' => $params['answer_value'],
                'answer_note' => $params['answer_note'],
            ];

            $progress = $this->taskRepository->addProgress($paramsProgress);
            $task['progress'] = $this->canonizeTaskProgress($progress);
        } else {
            $where = [
                'slug' => $progress['slug'],
            ];

            $set = [];
            if (isset($params['level']) && !empty($params['level'])) {
                $set['level'] = $params['level'];
            }
            if (
                isset($params['answer_score'])
                && is_numeric($params['answer_score'])
                && isset($params['answer_value'])
                && !empty($params['answer_value'])
            ) {
                $set['answer_score'] = $params['answer_score'];
                $set['answer_value'] = $params['answer_value'];
            }
            if (isset($params['answer_note']) && !empty($params['answer_note'])) {
                $set['answer_note'] = $params['answer_note'];
            }

            if (!empty($set)) {
                $set['time_update'] = time();

                $this->taskRepository->updateProgress($where, $set);
            }

            $progress = $this->taskRepository->getProgress(['slug' => $slug]);
            $task['progress'] = $this->canonizeTaskProgress($progress);
        }

        return [
            'result' => true,
            'data' => [
                $task,
            ],
            'error' => [],
        ];
    }

    public
    function getRiskDashboardData(array $params): array
    {
        return [
            'result' => true,
            'data' => [],
            'error' => [],
        ];
    }

    public
    function getRiskTree(array $params): array
    {
        $list = [];
        $sectionList = [];
        $riskList = [];
        $progressList = [];

        $rowsSection = $this->taskRepository->getTaskSectionList(['standard_id' => 1]);
        $rowsTask = $this->taskRepository->getTaskListOld(['standard_id' => 1]);
        $rowsRisk = $this->taskRepository->getRiskList(['standard_id' => 1]);
        $rowsProgress = $this->taskRepository->getProgressList(['standard_id' => 1]);

        foreach ($rowsSection as $sectionSingle) {
            $sectionSingle = $this->canonizeTaskSection($sectionSingle);
            $sectionList[$sectionSingle['id']] = $sectionSingle;
        }

        foreach ($rowsRisk as $riskSingle) {
            $riskSingle = $this->canonizeTaskRisk($riskSingle);
            $riskList[$riskSingle['slug']] = $riskSingle;
        }

        foreach ($rowsProgress as $progressSingle) {
            $progressSingle = $this->canonizeTaskProgress($progressSingle);
            $progressList[$progressSingle['slug']] = $progressSingle;
        }

        $members = $this->listMember([]);
        foreach ($rowsTask as $taskSingle) {
            $taskSingle = $this->canonizeTaskList($taskSingle, $members);
            $slug = $this->generateSlug($taskSingle, $params);
            $taskSingle['risk'] = [];
            if (isset($riskList[$slug]) && !empty($riskList[$slug])) {
                $taskSingle['risk'] = $riskList[$slug];
            }

            $taskSingle['progress'] = [];
            if (isset($progressList[$slug]) && !empty($progressList[$slug])) {
                $taskSingle['progress'] = $progressList[$slug];
            }

            $sectionList[$taskSingle['section_id']]['children'][] = $taskSingle;
        }


        // Make tree
        foreach ($sectionList as $sectionSingle) {
            if ($sectionSingle['parent_id'] == 0) {
                $sectionSingle['type'] = 'domain';
                $sectionSingle['child_type'] = 'sub_domain';
                $sectionSingle['children'] = [];
                $list[$sectionSingle['id']] = $sectionSingle;
            } else {
                $sectionSingle['type'] = 'sub_domain';
                $sectionSingle['child_type'] = 'task';
                $list[$sectionSingle['parent_id']]['children'][$sectionSingle['id']] = $sectionSingle;
            }
        }

        foreach ($list as $single) {
            $single['children'] = array_values($single['children']);
            $list[$single['id']] = $single;
        }


        return array_values($list);

        ///TODO: remove and migrate resource from db
        return $this->sample(array_values($list));

        return [
            'result' => true,
            'data' => $this->sample(array_values($list)),
            'error' => [],
        ];
    }

    public
    function getTaskRisk(array $params): array
    {
        $task = $this->taskRepository->getTask(['id' => $params['task_id']]);
        $members = $this->listMember([]);
        $task = $this->canonizeTaskList($task, $members);

        $slug = $this->generateSlug($task, $params);

        $risk = $this->taskRepository->getRisk(['slug' => $slug]);
        $task['risk'] = $this->canonizeTaskRisk($risk);
        $progress = $this->taskRepository->getProgress(['slug' => $slug]);
        $task['progress'] = $this->canonizeTaskProgress($progress);

        return [
            'result' => true,
            'data' => [
                $task,
            ],
            'error' => [],
        ];
    }

    public
    function updateTaskRisk(array $params): array
    {
        $task = $this->taskRepository->getTask(['id' => $params['task_id']]);
        $members = $this->listMember([]);
        $task = $this->canonizeTaskList($task, $members);

        $slug = $this->generateSlug($task, $params);

        $risk = $this->taskRepository->getRisk(['slug' => $slug]);
        $risk = $this->canonizeTaskRisk($risk);

        if (empty($risk)) {
            $paramsRisk = [
                'slug' => $slug,
                'standard_id' => $params['standard_id'],
                'section_id' => $task['section_id'],
                'task_id' => $task['id'],
                'user_id' => $params['user_id'],
                'company_id' => $params['company_id'],
                'time_create' => time(),
                'time_update' => time(),
                'level' => $params['level'],
                'risk_intensity' => $params['risk_intensity'],
                'risk_effect' => $params['risk_effect'],
                'risk_data' => $params['risk_data'],
                'risk_threat' => $params['risk_threat'],
                'risk_damage' => $params['risk_damage'],
                'risk_response_type' => $params['risk_response_type'],
                'risk_execution_percent' => $params['risk_execution_percent'],
                'risk_proposed_action' => $params['risk_proposed_action'],
                'risk_scenario' => $params['risk_scenario'],
            ];

            $risk = $this->taskRepository->addRisk($paramsRisk);
            $task['risk'] = $this->canonizeTaskRisk($risk);
        } else {
            $where = [
                'slug' => $risk['slug'],
            ];

            $set = [];
            if (isset($params['level']) && !empty($params['level'])) {
                $set['level'] = $params['level'];
            }
            if (
                isset($params['risk_intensity'])
                && is_numeric($params['risk_intensity'])
                && isset($params['risk_effect'])
                && is_numeric($params['risk_effect'])
            ) {
                $set['risk_intensity'] = $params['risk_intensity'];
                $set['risk_effect'] = $params['risk_effect'];
                $set['risk_data'] = $params['risk_data'];
            }
//            if (isset($params['risk_scenario']) && !empty($params['risk_scenario'])) {
//                $set['risk_scenario'] = $params['risk_scenario'];
//            }

            $set['risk_threat'] = $params['risk_threat'];
            $set['risk_damage'] = $params['risk_damage'];
            $set['risk_scenario'] = $params['risk_scenario'];

            $set['risk_data'] = $params['risk_data'];
            if (isset($params['risk_execution_percent']) && !empty($params['risk_execution_percent'])) {
                $set['risk_execution_percent'] = $params['risk_execution_percent'];
            }
            if (isset($params['risk_proposed_action']) && !empty($params['risk_proposed_action'])) {
                $set['risk_proposed_action'] = $params['risk_proposed_action'];
            }
            if (isset($params['risk_response_type']) && !empty($params['risk_response_type'])) {
                $set['risk_response_type'] = $params['risk_response_type'];
            }

            if (!empty($set)) {
                $set['time_update'] = time();

                $this->taskRepository->updateRisk($where, $set);
            }

            $risk = $this->taskRepository->getRisk(['slug' => $slug]);
            $task['risk'] = $this->canonizeTaskRisk($risk);
        }

        return [
            'result' => true,
            'data' => [
                $task,
            ],
            'error' => [],
        ];
    }


    public
    function getAuditDashboardData(array $params): array
    {
        return [
            'result' => true,
            'data' => [],
            'error' => [],
        ];
    }

    public
    function getAuditTree(array $params): array
    {
        $list = [];
        $sectionList = [];
        $riskList = [];
        $progressList = [];
        $auditList = [];

        $rowsSection = $this->taskRepository->getTaskSectionList(['standard_id' => 1]);
        $rowsTask = $this->taskRepository->getTaskListOld(['standard_id' => 1]);
        $rowsRisk = $this->taskRepository->getRiskList(['standard_id' => 1]);
        $rowsProgress = $this->taskRepository->getProgressList(['standard_id' => 1]);
        $rowsAudit = $this->taskRepository->getAuditList(['standard_id' => 1]);

        foreach ($rowsSection as $sectionSingle) {
            $sectionSingle = $this->canonizeTaskSection($sectionSingle);
            $sectionList[$sectionSingle['id']] = $sectionSingle;
        }

        foreach ($rowsRisk as $riskSingle) {
            $riskSingle = $this->canonizeTaskRisk($riskSingle);
            $riskList[$riskSingle['slug']] = $riskSingle;
        }

        foreach ($rowsProgress as $progressSingle) {
            $progressSingle = $this->canonizeTaskProgress($progressSingle);
            $progressList[$progressSingle['slug']] = $progressSingle;
        }

        foreach ($rowsAudit as $auditSingle) {
            $auditSingle = $this->canonizeTaskAudit($auditSingle);
            $auditList[$auditSingle['slug']] = $auditSingle;
        }

        $members = $this->listMember([]);
        foreach ($rowsTask as $taskSingle) {
            $taskSingle = $this->canonizeTaskList($taskSingle, $members);
            $slug = $this->generateSlug($taskSingle, $params);
            $taskSingle['risk'] = [];
            if (isset($riskList[$slug]) && !empty($riskList[$slug])) {
                $taskSingle['risk'] = $riskList[$slug];
            }

            $taskSingle['progress'] = [];
            if (isset($progressList[$slug]) && !empty($progressList[$slug])) {
                $taskSingle['progress'] = $progressList[$slug];
            }

            $taskSingle['audit'] = [];
            if (isset($auditList[$slug]) && !empty($auditList[$slug])) {
                $taskSingle['audit'] = $auditList[$slug];
            }

            $sectionList[$taskSingle['section_id']]['children'][] = $taskSingle;
        }


        // Make tree
        foreach ($sectionList as $sectionSingle) {
            if ($sectionSingle['parent_id'] == 0) {
                $sectionSingle['type'] = 'domain';
                $sectionSingle['child_type'] = 'sub_domain';
                $sectionSingle['children'] = [];
                $list[$sectionSingle['id']] = $sectionSingle;
            } else {
                $sectionSingle['type'] = 'sub_domain';
                $sectionSingle['child_type'] = 'task';
                $list[$sectionSingle['parent_id']]['children'][$sectionSingle['id']] = $sectionSingle;
            }
        }

        foreach ($list as $single) {
            $single['children'] = array_values($single['children']);
            $list[$single['id']] = $single;
        }


        return array_values($list);

        ///TODO: remove and migrate resource from db
        return $this->sample(array_values($list));

        return [
            'result' => true,
            'data' => $this->sample(array_values($list)),
            'error' => [],
        ];
    }

    public
    function getTaskAudit(array $params): array
    {
        $task = $this->taskRepository->getTask(['id' => $params['task_id']]);
        $task = $this->canonizeTaskList($task, $this->listMember([]));

        $slug = $this->generateSlug($task, $params);

        $risk = $this->taskRepository->getRisk(['slug' => $slug]);
        $task['risk'] = $this->canonizeTaskRisk($risk);
        $progress = $this->taskRepository->getProgress(['slug' => $slug]);
        $task['progress'] = $this->canonizeTaskProgress($progress);
        $audit = $this->taskRepository->getAudit(['slug' => $slug]);
        $task['audit'] = $this->canonizeTaskAudit($audit);

        return [
            'result' => true,
            'data' => [
                $task,
            ],
            'error' => [],
        ];
    }

    public
    function updateTaskAudit(array $params): array
    {
        $task = $this->taskRepository->getTask(['id' => $params['task_id']]);
        $task = $this->canonizeTaskList($task, $this->listMember([]));

        $slug = $this->generateSlug($task, $params);

        $audit = $this->taskRepository->getAudit(['slug' => $slug]);
        $audit = $this->canonizeTaskAudit($audit);

        if (empty($audit)) {
            $paramsAudit = [
                'slug' => $slug,
                'standard_id' => $params['standard_id'],
                'section_id' => $task['section_id'],
                'task_id' => $task['id'],
                'user_id' => $params['user_id'],
                'company_id' => $params['company_id'],
                'time_create' => time(),
                'time_update' => time(),
                'level' => $params['level'],
                'answer_score' => $params['answer_score'],
                'answer_value' => $params['answer_value'],
                'answer_note' => $params['answer_note'],
            ];

            $audit = $this->taskRepository->addAudit($paramsAudit);
            $task['audit'] = $this->canonizeTaskAudit($audit);
        } else {
            $where = [
                'slug' => $audit['slug'],
            ];

            $set = [];
            if (isset($params['level']) && !empty($params['level'])) {
                $set['level'] = $params['level'];
            }
            if (
                isset($params['answer_score'])
                && is_numeric($params['answer_score'])
                && isset($params['answer_value'])
                && !empty($params['answer_value'])
            ) {
                $set['answer_score'] = $params['answer_score'];
                $set['answer_value'] = $params['answer_value'];
            }
            if (isset($params['answer_note']) && !empty($params['answer_note'])) {
                $set['answer_note'] = $params['answer_note'];
            }

            if (!empty($set)) {
                $set['time_update'] = time();

                $this->taskRepository->updateAudit($where, $set);
            }

            $audit = $this->taskRepository->getAudit(['slug' => $slug]);
            $task['audit'] = $this->canonizeTaskAudit($audit);


            $progress = $this->taskRepository->getProgress(['slug' => $slug]);
            $task['progress'] = $this->canonizeTaskProgress($progress);
            $risk = $this->taskRepository->getRisk(['slug' => $slug]);
            $task['risk'] = $this->canonizeTaskRisk($risk);
        }

        return [
            'result' => true,
            'data' => [
                $task,
            ],
            'error' => [],
        ];
    }


    public
    function listMember($params): array
    {
        $limit = $params['limit'] ?? 300;
        $page = $params['page'] ?? 1;
        $key = $params['key'] ?? '';
        $order = $params['order'] ?? ['time_created DESC', 'id DESC'];
        $offset = ((int)$page - 1) * (int)$limit;

        $listParams = [
            'page' => (int)$page,
            'limit' => (int)$limit,
            'order' => $order,
            'offset' => $offset,
            'key' => $key,
        ];

        if (isset($params['name']) && !empty($params['name'])) {
            $listParams['name'] = $params['name'];
        }
        if (isset($params['identity']) && !empty($params['identity'])) {
            $listParams['identity'] = $params['identity'];
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $listParams['email'] = $params['email'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $listParams['mobile'] = $params['mobile'];
        }
        if (isset($params['status']) && in_array($params['status'], [0, 1])) {
            $listParams['status'] = $params['status'];
        }
        if (isset($params['data_from']) && !empty($params['data_from'])) {
            $listParams['data_from'] = $params['data_from'];
        }
        if (isset($params['data_to']) && !empty($params['data_to'])) {
            $listParams['data_to'] = $params['data_to'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $listParams['user_id'] = $params['user_id'];
        }

        $listApiRoles = $this->roleService->getApiRoleList();
        $listAdminRoles = $this->roleService->getAdminRoleList();
        if (isset($params['roles']) && !empty($params['roles'])) {
            $listParams['roles'] = array_diff(explode(',', $params['roles']), $listAdminRoles);
            if (empty($listParams['roles'])) {
                $listParams['roles'] = 'without_any_roles';
            } else {
                $listParams['roles'] = implode(',', $listParams['roles']);
            }
        } else {
            $listParams['roles'] = implode(',', $listApiRoles);
        }
        $data = $this->accountService->getAccountListByOperator($listParams);
        $i = 0;
        foreach ($data['list'] as $user) {
            $rolesList = $data['roles'][$user['id']];

            if (isset($rolesList['api'])) {
                foreach ($rolesList['api'] as $role) {
                    if ($role['role'] == 'member') {
                        unset($rolesList['api'][array_search($role, $rolesList['api'])]);
                    }
                }
                $rolesList['api'] = array_values($rolesList['api']);
            }

            $data['list'][$i]['roles'] = $rolesList;
            $i++;
        }
        unset($data['roles']);

        $mandatoryUnitMemberList = [];
        $this->taskRepository->getMandatoryUnitMemberList([]);

        foreach ($this->taskRepository->getMandatoryUnitMemberList([]) as $item) {
            $mandatoryUnitMemberList[$item->getUserId()] = json_decode($item->getMandatoryUnit());
        }
        $members = [];
        foreach ($data['list'] as $member) {
            $member['mandatory_unit'] = $mandatoryUnitMemberList[(string)$member['id']] ?? [];
            $members[] = $member;
        }
        $data['list'] = $members;
        return $data;

    }

    /**
     * Last 1000 members (id DESC), same filters as listMember; each row only id + name.
     */
    public function getMembersLightList($params, $account): array
    {
        $limit  = 1000;
        $page   = 1;
        $key    = $params['key'] ?? '';
        $order  = ['id DESC'];
        $offset = ((int)$page - 1) * (int)$limit;

        $listParams = [
            'page'   => (int)$page,
            'limit'  => (int)$limit,
            'order'  => $order,
            'offset' => $offset,
            'key'    => $key,
        ];

        if (isset($params['name']) && !empty($params['name'])) {
            $listParams['name'] = $params['name'];
        }
        if (isset($params['identity']) && !empty($params['identity'])) {
            $listParams['identity'] = $params['identity'];
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $listParams['email'] = $params['email'];
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $listParams['mobile'] = $params['mobile'];
        }
        if (isset($params['status']) && in_array($params['status'], [0, 1], true)) {
            $listParams['status'] = $params['status'];
        }
        if (isset($params['data_from']) && !empty($params['data_from'])) {
            $listParams['data_from'] = $params['data_from'];
        }
        if (isset($params['data_to']) && !empty($params['data_to'])) {
            $listParams['data_to'] = $params['data_to'];
        }
        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $listParams['user_id'] = $params['user_id'];
        }

        $listApiRoles   = $this->roleService->getApiRoleList();
        $listAdminRoles = $this->roleService->getAdminRoleList();
        if (isset($params['roles']) && !empty($params['roles'])) {
            $listParams['roles'] = array_diff(explode(',', $params['roles']), $listAdminRoles);
            if (empty($listParams['roles'])) {
                $listParams['roles'] = 'without_any_roles';
            } else {
                $listParams['roles'] = implode(',', $listParams['roles']);
            }
        } else {
            $listParams['roles'] = implode(',', $listApiRoles);
        }

        $data = $this->accountService->getAccountListByOperator($listParams);

        $lightList = [];
        foreach ($data['list'] as $user) {
            $lightList[] = [
                'id'   => (int)($user['id'] ?? 0),
                'name' => $user['name'] ?? '',
            ];
        }

        $count = (int)($data['paginator']['count'] ?? 0);

        return [
            'result' => true,
            'data'   => [
                'list'      => $lightList,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page'  => $page,
                ],
                'filters'   => null,
            ],
            'error'  => [],
        ];
    }

    public
    function getMember($params): array|object|null
    {
        $members = $this->listMember([]);
        if (isset($params['id'])) {
            foreach ($members['list'] as $member) {
                if ($member['id'] == $params['id'])
                    return $member;
            }
        }
        return new stdClass();
    }

    public function addMember(array $params, $operator = []): array
    {

        $user = $this->accountService->addAccount($params, $operator);
        if (isset($user['id']) && !empty($user)) {
            if (isset($params['roles'])) {
                $roles = explode(',', $params['roles']);
                $forbiddenRoles = $this->roleService->getAdminRoleList();
                $forbiddenRoles[] = 'member';
                $roles = array_diff($roles, $forbiddenRoles);
                foreach ($roles as $role) {
                    $this->roleService->addRoleAccount($user, $role, 'api', $operator);

                }
            }

            $mandatoryUnit = $this->taskRepository->storeMandatoryUnitMember([
                'user_id' => $user['id'],
                'mandatory_unit' => json_encode($params['mandatory_unit'] ?? []),
                'time_create' => time()
            ]);

            $user['mandatory_unit'] = $this->canonizeMandatoryUnitMember($mandatoryUnit)['mandatory_unit'];

            $user = [
                'result' => true,
                'data' => $user,
                'error' => new \stdClass(),
            ];
        }
        return $user;
    }


///TODO:replace it by new method
    public
    function generateSlug($task, $params): string
    {
        return md5(
            sprintf(
                '%s-%s-%s',
                $task['standard_id'],
                $task['section_id'],
                $task['id']
            )
        );
    }

    public
    function canonizeTaskSection($section): array
    {
        if (empty($section)) {
            return [];
        }

        if (is_object($section)) {
            $section = [
                'id' => $section->getId(),
                'slug' => $section->getSlug(),
                'standard_id' => $section->getStandardId(),
                'type' => $section->getType(),
                'parent_id' => $section->getParentId(),
                'code' => $section->getCode(),
                'title' => $section->getTitle(),
                'status' => $section->getStatus(),
                'time_create' => $section->getTimeCreate(),
                'time_update' => $section->getTimeUpdate(),
            ];
        } else {
            $section = [
                'id' => $section['id'],
                'slug' => $section['slug'],
                'standard_id' => $section['standard_id'],
                'tpye' => $section['type'],
                'parent_id' => $section['parent_id'],
                'code' => $section['code'],
                'title' => $section['title'],
                'status' => $section['status'],
                'time_create' => $section['time_create'],
                'time_update' => $section['time_create'],
            ];
        }

        return $section;
    }

    public
    function canonizeTaskList($task, $members): array
    {
        if (empty($task)) {
            return [];
        }

        if (is_object($task)) {
            $task = [
                'id' => $task->getId(),
                'parent_id' => $task->getParentId(),
                'type' => $task->getType(),
                'user_id' => $task->getUserId(),
                'standard_id' => $task->getStandardId(),
                'section_id' => $task->getSectionId(),
                'code' => $task->getCode(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'rule_id' => $task->getRuleId(),
                'warranty_id' => $task->getWarrantyId(),
                'mandatory_unit' => $task->getMandatoryUnit(),
                'status' => $task->getStatus(),
                'has_clause' => $task->getHasClause(),
                'reference_id' => $task->getReferenceId(),
                'time_create' => $task->getTimeCreate(),
                'time_update' => $task->getTimeUpdate(),
            ];
        } else {
            $task = [
                'id' => $task['id'],
                'parent_id' => $task['parent_id'],
                'type' => $task['type'],
                'user_id' => $task['user_id'],
                'standard_id' => $task['standard_id'],
                'section_id' => $task['section_id'],
                'code' => $task['code'],
                'title' => $task['title'],
                'description' => $task['description'],
                'status' => $task['status'],
                'rule_id' => $task['rule_id'],
                'warranty_id' => $task['warranty_id'],
                'mandatory_unit' => $task['mandatory_unit'],
                'has_clause' => $task['has_clause'],
                'reference_id' => $task['reference_id'],
                'time_create' => $task['time_create'],
                'time_update' => $task['time_create'],
            ];
        }

        $task['value'] = $this->getAnswersList(['type' => $task['type']], [])['data']['list'];


        $task["rule"] = $this->findObjectById($this->getRulesTree(), $task["rule_id"]);
        $task["warranty"] = $this->findObjectById($this->getWarrantiesTree(), $task["warranty_id"]);
        $task["mandatory_unit"] = json_decode($task['mandatory_unit'], true);

        $time = time();
        $task["current_time"] = $time;
        $task["current_time_view"] = $this->utilityService->date($time, ['local' => 'en_US', 'pattern' => 'yyyy/MM/dd']);

        $task["user"] = new stdClass();
        if (isset($task['user_id'])) {
            foreach ($members['list'] as $member) {
                if ($member['id'] == $task['user_id'])
                    $task["user"] = $member;
            }
        }
        return $task;
    }

    public function findObjectById($array, $id)
    {

        foreach ($array as $element) {
            if ($id == $element["id"]) {
                return $element;
            }
        }
        return null;
    }

    ///TODO: remove get account from canonizeTaskProgress
    public
    function canonizeTaskProgress($progress): array
    {
        if (empty($progress)) {
            return [];
        }


        if (is_object($progress)) {
            $progress = [
                'id' => $progress->getId(),
                'slug' => $progress->getSlug(),
                'standard_id' => $progress->getStandardId(),
                'section_id' => $progress->getSectionId(),
                'task_id' => $progress->getTaskId(),
                'user_id' => $progress->getUserId(),
                'assigner_id' => $progress->getAssignerId(),
                'company_id' => $progress->getCompanyId(),
                'time_create' => $progress->getTimeCreate(),
                'time_update' => $progress->getTimeUpdate(),
                'level' => $progress->getLevel(),
                'status' => $progress->getStatus(),
                'answer_score' => $progress->getAnswerScore(),
                'answer_value' => $progress->getAnswerValue(),
                'answer_note' => $progress->getAnswerNote(),
                'type' => $progress->getType(),
                'parent_id' => $progress->getParentId(),
                'time_deadline' => $progress->getTimeDeadline(),
                'history' => $progress->getHistory(),
                'information' => $progress->getInformation(),
            ];
        } else {
            $progress = [
                'id' => $progress['id'],
                'slug' => $progress['slug'],
                'standard_id' => $progress['standard_id'],
                'section_id' => $progress['section_id'],
                'task_id' => $progress['task_id'],
                'user_id' => $progress['user_id'],
                'assigner_id' => $progress['assigner_id'],
                'company_id' => $progress['company_id'],
                'time_create' => $progress['time_create'],
                'time_update' => $progress['time_create'],
                'level' => $progress['level'],
                'status' => $progress['status'],
                'answer_score' => $progress['answer_score'],
                'answer_value' => $progress['answer_value'],
                'answer_note' => $progress['answer_note'],
                'type' => $progress['type'],
                'parent_id' => $progress['parent_id'],
                'time_deadline' => $progress['time_deadline'],
                'history' => $progress['history'],
                'information' => $progress['information'],
            ];
        }
        $progress['information'] = json_decode($progress['information'], true);
        $progress['history'] = json_decode($progress['history'], true);
        if (!empty($progress['time_deadline']) && is_numeric($progress['time_deadline'])) {
            $progress['time_deadline_view'] = $this->utilityService->date($progress['time_deadline']);
        } else {
            $progress['time_deadline_view'] = '-';
        }
        $time = time();
        $progress["current_time"] = $time;
        $progress["current_time_view"] = $this->utilityService->date($time, ['local' => 'en_US', 'pattern' => 'yyyy/MM/dd']);

        $user = $this->accountService->getAccount(['id' => $progress['user_id']]);
        $progress['user'] = (sizeof($user) > 0) ? $user : new stdClass();

        $progress['next_actions'] = $this->roadMap[$progress['level']];

        return $progress;
    }

    public
    function canonizeTaskAudit($audit): array
    {
        if (empty($audit)) {
            return [];
        }

        if (is_object($audit)) {
            $audit = [
                'id' => $audit->getId(),
                'slug' => $audit->getSlug(),
                'standard_id' => $audit->getStandardId(),
                'section_id' => $audit->getSectionId(),
                'task_id' => $audit->getTaskId(),
                'user_id' => $audit->getUserId(),
                'company_id' => $audit->getCompanyId(),
                'time_create' => $audit->getTimeCreate(),
                'time_update' => $audit->getTimeUpdate(),
                'level' => $audit->getLevel(),
                'answer_score' => $audit->getAnswerScore(),
                'answer_value' => $audit->getAnswerValue(),
                'answer_note' => $audit->getAnswerNote(),
            ];
        } else {
            $audit = [
                'id' => $audit['id'],
                'slug' => $audit['slug'],
                'standard_id' => $audit['standard_id'],
                'section_id' => $audit['section_id'],
                'task_id' => $audit['task_id'],
                'user_id' => $audit['user_id'],
                'company_id' => $audit['company_id'],
                'time_create' => $audit['time_create'],
                'time_update' => $audit['time_create'],
                'level' => $audit['level'],
                'answer_score' => $audit['answer_score'],
                'answer_value' => $audit['answer_value'],
                'answer_note' => $audit['answer_note'],
            ];
        }

        return $audit;
    }

    public
    function canonizeTaskRisk($risk, $members): array
    {
        if (empty($risk)) {
            return [];
        }

        if (is_object($risk)) {
            $risk = [
                'id' => $risk->getId(),
                'slug' => $risk->getSlug(),
                'standard_id' => $risk->getStandardId(),
                'section_id' => $risk->getSectionId(),
                'task_id' => $risk->getTaskId(),
                'user_id' => $risk->getUserId(),
                'company_id' => $risk->getCompanyId(),
                'time_create' => $risk->getTimeCreate(),
                'time_update' => $risk->getTimeUpdate(),
                'level' => $risk->getLevel(),
                'risk_intensity' => $risk->getRiskIntensity(),
                'risk_data' => $risk->getRiskData(),
                'risk_threat' => $risk->getRiskThreat(),
                'risk_damage' => $risk->getRiskDamage(),
                'risk_response_type' => $risk->getRiskResponseType(),
                'risk_execution_percent' => $risk->getRiskExecutionPercent(),
                'risk_proposed_action' => $risk->getRiskProposedAction(),
                'risk_effect' => $risk->getRiskEffect(),
                'risk_scenario' => $risk->getRiskScenario(),
                'type' => $risk->getType(),
                'parent_id' => $risk->getParentId(),
                'time_deadline' => $risk->getTimeDeadline(),
                'history' => $risk->getHistory(),
                'assigner_id' => $risk->getAssignerId(),
            ];
        } else {
            $risk = [
                'id' => $risk['id'],
                'slug' => $risk['slug'],
                'standard_id' => $risk['standard_id'],
                'section_id' => $risk['section_id'],
                'task_id' => $risk['task_id'],
                'user_id' => $risk['user_id'],
                'company_id' => $risk['company_id'],
                'time_create' => $risk['time_create'],
                'time_update' => $risk['time_create'],
                'level' => $risk['level'],
                'risk_intensity' => $risk['risk_intensity'],
                'risk_effect' => $risk['risk_effect'],
                'risk_data' => $risk['risk_data'],
                'risk_threat' => $risk['risk_threat'],
                'risk_damage' => $risk['risk_damage'],
                'risk_response_type' => $risk['risk_response_type'],
                'risk_execution_percent' => $risk['risk_execution_percent'],
                'risk_proposed_action' => $risk['risk_proposed_action'],
                'risk_scenario' => $risk['risk_scenario'],
                'type' => $risk['type'],
                'parent_id' => $risk['parent_id'],
                'time_deadline' => $risk['time_deadline'],
                'history' => $risk['history'],
                'assigner_id' => $risk['assigner_id'],
            ];
        }

        $memberList = is_array($members) ? ($members['list'] ?? []) : [];
        $risk["user"] = new stdClass();
        if (isset($risk['user_id'])) {
            $resolvedUser = $this->resolveErmMemberFromMembersOrAccount((int) $risk['user_id'], $memberList);
            if (!($resolvedUser instanceof stdClass)) {
                $risk["user"] = $resolvedUser;
            }
        }

        $risk['assigner'] = new stdClass();
        if (isset($risk['assigner_id']) && (int) $risk['assigner_id'] > 0) {
            $resolvedAssigner = $this->resolveErmMemberFromMembersOrAccount((int) $risk['assigner_id'], $memberList);
            if (!($resolvedAssigner instanceof stdClass)) {
                $risk['assigner'] = $resolvedAssigner;
            }
        }

        $risk['history'] = json_decode($risk['history'], true);
        if (!empty($risk['time_deadline']) && is_numeric($risk['time_deadline'])) {
            $risk['time_deadline_view'] = $this->utilityService->date($risk['time_deadline']);
        } else {
            $risk['time_deadline_view'] = '-';
        }
        $time = time();
        $risk["current_time"] = $time;
        $risk["current_time_view"] = $this->utilityService->date($time, ['local' => 'en_US', 'pattern' => 'yyyy/MM/dd']);
        $risk['next_actions'] = $this->roadMap[$risk['level']];

        return $risk;
    }

    /**
     * Match listMember() row shape: prefer members from listMember() cache; if missing (e.g. admin-only
     * users excluded by getAccountListByOperator), load account + roles + mandatory_unit.
     *
     * @param array<int, array<string, mixed>> $memberList
     * @return array<string, mixed>|stdClass
     */
    private function resolveErmMemberFromMembersOrAccount(int $userId, array $memberList): array|stdClass
    {
        if ($userId <= 0) {
            return new stdClass();
        }
        foreach ($memberList as $member) {
            if ((int) ($member['id'] ?? 0) === $userId) {
                return $member;
            }
        }
        $account = $this->accountService->getAccount(['id' => $userId]);
        if (empty($account['id'])) {
            return new stdClass();
        }
        $roleList = $this->roleService->getRoleAccountList([$userId], 'full');
        $rolesList = array_replace_recursive(
            ['api' => [], 'admin' => []],
            $roleList[$userId] ?? []
        );
        if (isset($rolesList['api'])) {
            foreach ($rolesList['api'] as $idx => $role) {
                if (($role['role'] ?? '') === 'member') {
                    unset($rolesList['api'][$idx]);
                }
            }
            $rolesList['api'] = array_values($rolesList['api']);
        }
        $account['roles'] = $rolesList;
        $mandatoryUnit = [];
        foreach ($this->taskRepository->getMandatoryUnitMemberList(['user_id' => $userId]) as $item) {
            $decoded = json_decode($item->getMandatoryUnit(), true);
            $mandatoryUnit = is_array($decoded) ? $decoded : [];
            break;
        }
        $account['mandatory_unit'] = $mandatoryUnit;

        return $account;
    }

    /**
     * Parent risk rows store assigner in user_id; after canonize, expose real assignee(s) from comma-separated user ids.
     *
     * @param array $risk Canonized risk (mutated)
     */
    private function applyRiskParentAssigneesToUserDisplay(array &$risk, array $members, string $assigneeUserIdsCsv): void
    {
        $ids = array_values(array_filter(array_unique(array_map(
            static fn ($v) => (int) trim((string) $v),
            explode(',', $assigneeUserIdsCsv)
        )), static fn (int $id) => $id > 0));
        if ($ids === []) {
            return;
        }
        $resolved = [];
        foreach ($ids as $uid) {
            foreach ($members['list'] as $member) {
                if ((int) ($member['id'] ?? 0) === $uid) {
                    $resolved[] = $member;
                    break;
                }
            }
        }
        if ($resolved === []) {
            return;
        }
        $risk['user'] = count($resolved) === 1 ? $resolved[0] : $resolved;
    }

    public
    function canonizeRule($rule, $members, $data): array
    {
        if (empty($rule)) {
            return [];
        }

        if (is_object($rule)) {
            $rule = [
                'id' => $rule->getId(),
                'user_id' => $rule->getUserId(),
                'target' => $rule->getTarget(),
                'code' => $rule->getCode(),
                'rule' => $rule->getRule(),
                'author' => $rule->getAuthor(),
                'approval_at' => $rule->getApprovalAt(),
                'cancellation_at' => $rule->getCancellationAt(),
                'promulgation_at' => $rule->getPromulgationAt(),
                'is_creditable' => $rule->getIsCreditable(),
                'type' => $rule->getType(),
                'category' => $rule->getCategory(),
                'requirement' => $rule->getRequirement(),
                'validity' => $rule->getValidity(),
            ];
        } else {
            $rule = [
                'id' => $rule['id'],
                'user_id' => $rule['user_id'],
                'target' => $rule['target'],
                'code' => $rule['code'],
                'rule' => $rule['rule'],
                'author' => $rule['author'],
                'approval_at' => $rule['approval_at'],
                'cancellation_at' => $rule['cancellation_at'],
                'promulgation_at' => $rule['promulgation_at'],
                'is_creditable' => $rule['is_creditable'],
                'type' => $rule['type'],
                'category' => $rule['category'],
                'requirement' => $rule['requirement'],
                'validity' => $rule['validity'],
            ];
        }

        $rule["user"] = new stdClass();
        if (isset($rule['user_id'])) {
            foreach ($members['list'] as $member) {
                if ($member['id'] == $rule['user_id'])
                    $rule["user"] = $member;
            }
        }

        $rule['approval_at_view'] = $rule['approval_at'] ? $this->utilityService->date(strtotime(
            sprintf('%s 00:00:00', $rule['approval_at'])), ['pattern' => 'yyyy/MM/dd']) : '';
        $rule['cancellation_at_view'] = $rule['cancellation_at'] ? $this->utilityService->date(strtotime(
            sprintf('%s 00:00:00', $rule['cancellation_at'])), ['pattern' => 'yyyy/MM/dd']) : '';
        $rule['promulgation_at_view'] = $rule['promulgation_at'] ? $this->utilityService->date(strtotime(
            sprintf('%s 00:00:00', $rule['promulgation_at'])), ['pattern' => 'yyyy/MM/dd']) : '';
        $rule['author_information'] = new stdClass();
        $rule['type_information'] = new stdClass();
        $rule['category_information'] = new stdClass();

        if (isset($data['author_list'])) {
            foreach ($data['author_list'] as $object) {
                if ($object['slug'] === $rule['author']) {
                    $rule['author_information'] = $object;
                    break;
                }
            }
        }
        if (isset($data['type_list'])) {
            foreach ($data['type_list'] as $object) {
                if ($object['slug'] === $rule['type']) {
                    $rule['type_information'] = $object;
                    break;
                }
            }
        }
        if (isset($data['category_list'])) {
            foreach ($data['category_list'] as $object) {
                if ($object['slug'] === $rule['category']) {
                    $rule['category_information'] = $object;
                    break;
                }
            }
        }
        return $rule;
    }

    public
    function canonizeWarranty($warranty): array
    {
        if (empty($warranty)) {
            return [];
        }

        if (is_object($warranty)) {
            $warranty = [
                'id' => $warranty->getId(),
                'slug' => $warranty->getSlug(),
                'title' => $warranty->getTitle(),
            ];
        } else {
            $warranty = [
                'id' => $warranty->getId(),
                'slug' => $warranty->getSlug(),
                'title' => $warranty->getTitle(),
            ];
        }

        return $warranty;
    }

///Move to bottom for migrate ro rule service and add filter ability
    public
    function getRulesTree()
    {
        $rulesTree = array();
        $members = $this->listMember([]);
        foreach ($this->taskRepository->getRulesOld() as $rule) {
            array_push(
                $rulesTree,
                $this->canonizeRule(
                    $rule,
                    $members,
                    [
                        'author_list' => $this->getRuleAuthorList([], []),
                        'type_list' => $this->getRuleTypeList([], []),
                        'category_list' => $this->getRuleCategoryList([], []),
                    ]
                )
            );
        }
        return $rulesTree;
    }

    public
    function storeRule(array $params): array
    {
        $params['time_create'] = time();
        $members = $this->listMember([]);
        return $this->canonizeRule(
            $this->taskRepository->storeRule($params),
            $members,
            [
                'author_list' => $this->getRuleAuthorList([], []),
                'type_list' => $this->getRuleTypeList([], []),
                'category_list' => $this->getRuleCategoryList([], []),
            ]
        );
    }

    public
    function updateRule(array $params): array
    {
        $members = $this->listMember([]);
        return $this->canonizeRule(
            $this->taskRepository->updateRule($params),
            $members,
            [
                'author_list' => $this->getRuleAuthorList([], []),
                'type_list' => $this->getRuleTypeList([], []),
                'category_list' => $this->getRuleCategoryList([], []),
            ]
        );
    }

    public
    function deleteRule(array $params): array|object
    {
        $params = [
            "id" => $params["id"],
            "status" => 0,
            "time_delete" => time(),
        ];

        return $this->taskRepository->updateRule($params);
    }

    public
    function getWarrantiesTree(): array
    {
        $warrantiesTree = array();
        foreach ($this->taskRepository->getWarranties() as $warranty) {
            array_push($warrantiesTree, $this->canonizeWarranty($warranty));
        }
        return $warrantiesTree;
    }

    public
    function storeTask(array $params): array
    {
        $params['time_create'] = time();
        return $this->canonizeTaskList($this->taskRepository->storeTask($params), $this->listMember([]));
    }

    public
    function updateTask(array $params): array
    {
        return $this->canonizeTaskList($this->taskRepository->updateTask($params), $this->listMember([]));
    }

    public
    function deleteTask(array $params): array|object
    {
        $params = [
            "id" => $params["id"],
            "status" => 0,
            "time_delete" => time(),
        ];
        return $this->taskRepository->updateTask($params);
    }


/// CHANGED BY KERLOPER
///TODO : move bottom methods to ruleService

//rules
    public
    function getRulesTreeWhitFilter($params, $account): array
    {

        $limit = $params['limit'] ?? 3000;
        $page = $params['page'] ?? 1;
        $order = $params['order'] ?? ['id DESC'];
        $offset = ($page - 1) * $limit;


        // Set params
        $listParams = [
            'order' => $order,
            'offset' => $offset,
            'limit' => $limit,
            'status' => 1,
        ];

        if (isset($params['data_from']) && $params['data_from'] != null) {
            $listParams['data_from'] = strtotime(
                sprintf('%s 00:00:00', $params['data_from'])
            );
        }
        if (isset($params['data_to']) && ($params['data_to']) != null) {
            $listParams['data_to'] = strtotime(
                sprintf('%s 00:00:00', $params['data_to'])
            );
        }

        if (isset($params['approval_at_from']) && $params['approval_at_from'] != null) {
            $listParams['approval_at_from'] = $params['approval_at_from'];
        }
        if (isset($params['approval_at_to']) && ($params['approval_at_to']) != null) {
            $listParams['approval_at_to'] = $params['approval_at_to'];
        }

        if (isset($params['cancellation_at_from']) && $params['cancellation_at_from'] != null) {
            $listParams['cancellation_at_from'] = $params['cancellation_at_from'];
        }
        if (isset($params['cancellation_at_to']) && ($params['cancellation_at_to']) != null) {
            $listParams['cancellation_at_to'] = $params['cancellation_at_to'];
        }

        if (isset($params['promulgation_at_from']) && $params['promulgation_at_from'] != null) {
            $listParams['promulgation_at_from'] = $params['promulgation_at_from'];
        }
        if (isset($params['promulgation_at_to']) && ($params['promulgation_at_to']) != null) {
            $listParams['promulgation_at_to'] = $params['promulgation_at_to'];
        }


        if (isset($params['user_id']))
            $listParams['user_id'] = $params['user_id'];
        if (isset($params['validity']))
            $listParams['validity'] = $params['validity'];
        if (isset($params['requirement']))
            $listParams['requirement'] = $params['requirement'];
        if (isset($params['author']))
            $listParams['author'] = $params['author'];
        if (isset($params['code']) && $params['code'] !== '' && $params['code'] !== null) {
            $listParams['code'] = $params['code'];
        }
        if (isset($params['rule']))
            $listParams['rule'] = $params['rule'];
        if (isset($params['category']))
            $listParams['category'] = $params['category'];
        if (isset($params['type']))
            $listParams['type'] = $params['type'];
        if (isset($params['is_creditable']))
            $listParams['is_creditable'] = $params['is_creditable'];
        if (isset($params['status'])) {
            $listParams['status'] = $params['status'];
        } else {
            $listParams['status'] = 1;
        }
        if (isset($params['target']))
            $listParams['target'] = $params['target'];

        $rulesTree = array();

        $members = $this->listMember([]);
        foreach ($this->taskRepository->getRules($listParams) as $rule) {
            $rulesTree[] = $this->canonizeRule(
                $rule,
                $members,
                [
                    'author_list' => $this->getRuleAuthorList([], []),
                    'type_list' => $this->getRuleTypeList([], []),
                    'category_list' => $this->getRuleCategoryList([], []),
                ]
            );
        }

        // Get count
        $count = $this->taskRepository->getRulesCount($listParams);

        return [
            'result' => true,
            'data' => [
                'list' => $rulesTree,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page' => $page,
                ],
                'filters' => null,
            ],
            'error' => [],
        ];
    }

    /**
     * Last 1000 rules (by id DESC), minimal canonized rows: id + rule only.
     * Accepts the same filter keys as getRulesTreeWhitFilter (body JSON).
     */
    public function getRulesLightList($params, $account): array
    {
        $limit  = 1000;
        $page   = 1;
        $order  = ['id DESC'];
        $offset = 0;

        $listParams = [
            'order'  => $order,
            'offset' => $offset,
            'limit'  => $limit,
            'status' => 1,
        ];

        if (isset($params['data_from']) && $params['data_from'] != null) {
            $listParams['data_from'] = strtotime(
                sprintf('%s 00:00:00', $params['data_from'])
            );
        }
        if (isset($params['data_to']) && ($params['data_to']) != null) {
            $listParams['data_to'] = strtotime(
                sprintf('%s 00:00:00', $params['data_to'])
            );
        }

        if (isset($params['approval_at_from']) && $params['approval_at_from'] != null) {
            $listParams['approval_at_from'] = $params['approval_at_from'];
        }
        if (isset($params['approval_at_to']) && ($params['approval_at_to']) != null) {
            $listParams['approval_at_to'] = $params['approval_at_to'];
        }

        if (isset($params['cancellation_at_from']) && $params['cancellation_at_from'] != null) {
            $listParams['cancellation_at_from'] = $params['cancellation_at_from'];
        }
        if (isset($params['cancellation_at_to']) && ($params['cancellation_at_to']) != null) {
            $listParams['cancellation_at_to'] = $params['cancellation_at_to'];
        }

        if (isset($params['promulgation_at_from']) && $params['promulgation_at_from'] != null) {
            $listParams['promulgation_at_from'] = $params['promulgation_at_from'];
        }
        if (isset($params['promulgation_at_to']) && ($params['promulgation_at_to']) != null) {
            $listParams['promulgation_at_to'] = $params['promulgation_at_to'];
        }

        if (isset($params['user_id'])) {
            $listParams['user_id'] = $params['user_id'];
        }
        if (isset($params['validity'])) {
            $listParams['validity'] = $params['validity'];
        }
        if (isset($params['requirement'])) {
            $listParams['requirement'] = $params['requirement'];
        }
        if (isset($params['author'])) {
            $listParams['author'] = $params['author'];
        }
        if (isset($params['code']) && $params['code'] !== '' && $params['code'] !== null) {
            $listParams['code'] = $params['code'];
        }
        if (isset($params['rule'])) {
            $listParams['rule'] = $params['rule'];
        }
        if (isset($params['category'])) {
            $listParams['category'] = $params['category'];
        }
        if (isset($params['type'])) {
            $listParams['type'] = $params['type'];
        }
        if (isset($params['is_creditable'])) {
            $listParams['is_creditable'] = $params['is_creditable'];
        }
        if (isset($params['status'])) {
            $listParams['status'] = $params['status'];
        } else {
            $listParams['status'] = 1;
        }
        if (isset($params['target'])) {
            $listParams['target'] = $params['target'];
        }

        $lightList = [];
        foreach ($this->taskRepository->getRules($listParams) as $rule) {
            $lightList[] = [
                'id'   => is_object($rule) ? $rule->getId() : $rule['id'],
                'rule' => is_object($rule) ? $rule->getRule() : $rule['rule'],
            ];
        }

        $count = $this->taskRepository->getRulesCount($listParams);

        return [
            'result' => true,
            'data'   => [
                'list'      => $lightList,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page'  => $page,
                ],
                'filters'   => null,
            ],
            'error'  => [],
        ];
    }


//domain
    public
    function getDomainTree(array $params, mixed $account): array
    {
        $list = [];
        $requestParams = [];
        if (isset($params['type'])) {
            $type = $params['type'];
            $requestParams['type'] = $type;
        } else {
            $type = $this->config['type'];
        }
        if (isset($params['status'])) {
            $requestParams['status'] = $params['status'];
        }
        $rows = $this->taskRepository->getTaskSectionList($requestParams);
        foreach ($rows as $row) {
            $list[] = $this->canonizeTaskSection($row);
        }
        return $this->buildTree($list, 0);
    }

    private
    function buildTree(array $data, $parentId = 0): array
    {
        $tree = [];

        foreach ($data as $key => &$item) {
            if ($item['parent_id'] == $parentId) {
                $item['children'] = $this->buildTree($data, $item['id']); // Use $this->buildTree() here
                $tree[] = $item;
                unset($data[$key]);
            }
        }

        return $tree;
    }


    ///TODO: remove db connection form canonize
    public function canonizeTaskTreeList($bucket): array
    {
        $task = isset($bucket['task']) ? $bucket['task'] : [];
        $domainTree = isset($bucket['domain_tree']) ? $bucket['domain_tree'] : [];
        $members = isset($bucket['members']) ? $bucket['members'] : [];
        $progressList = isset($bucket['progress_list']) ? $bucket['progress_list'] : [];
        $answerList = isset($bucket['answer_list']) ? $bucket['answer_list'] : [];
        $rules = isset($bucket['rules']) ? $bucket['rules'] : [];
        $warranties = isset($bucket['warranties']) ? $bucket['warranties'] : [];

        if (empty($task)) {
            return [];
        }

        if (is_object($task)) {
            $task = [
                'id' => $task->getId(),
                'parent_id' => $task->getParentId(),
                'type' => $task->getType(),
                'user_id' => $task->getUserId(),
                'standard_id' => $task->getStandardId(),
                'section_id' => $task->getSectionId(),
                'code' => $task->getCode(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'rule_id' => $task->getRuleId(),
                'warranty_id' => $task->getWarrantyId(),
                'mandatory_unit' => $task->getMandatoryUnit(),
                'status' => $task->getStatus(),
                'has_clause' => $task->getHasClause(),
                'reference_id' => $task->getReferenceId(),
                'time_create' => $task->getTimeCreate(),
                'time_update' => $task->getTimeUpdate(),
            ];
        } else {
            $task = [
                'id' => $task['id'],
                'parent_id' => $task['parent_id'],
                'type' => $task['type'],
                'user_id' => $task['user_id'],
                'standard_id' => $task['standard_id'],
                'section_id' => $task['section_id'],
                'code' => $task['code'],
                'title' => $task['title'],
                'description' => $task['description'],
                'status' => $task['status'],
                'rule_id' => $task['rule_id'],
                'warranty_id' => $task['warranty_id'],
                'mandatory_unit' => $task['mandatory_unit'],
                'has_clause' => $task['has_clause'],
                'reference_id' => $task['reference_id'],
                'time_create' => $task['time_create'],
                'time_update' => $task['time_create'],
            ];
        }

        if (empty($answerList)) {
            $answerList = $this->getAnswersList(['type' => $task['type']], [])['data']['list'];
        }
        $task['value'] = $answerList;
        $task["rule"] = $this->findObjectById($rules, $task["rule_id"]);
        $task["warranty"] = isset($task['warranty_id']) ? $this->findObjectById($warranties, $task["warranty_id"]) : [];
        //$task["mandatory_unit"] = isset($task['mandatory_unit']) ? is_object($task['mandatory_unit']) ? json_decode($task['mandatory_unit'], true) : [] : [];
        $task["mandatory_unit"] = (isset($task['mandatory_unit'])&&!empty($task['mandatory_unit']))?json_decode($task['mandatory_unit'], true):[];

        $time = time();
        $task["current_time"] = $time;
        $task["current_time_view"] = $this->utilityService->date($time, ['local' => 'en_US', 'pattern' => 'yyyy/MM/dd']);

        if ($task['type'] == 'maturity') {

            foreach ($domainTree as $domain) {
                if ($domain['id'] == $task["section_id"]) {
                    $dm = $domain;
                    $dm['children'] = $domain;
                    $task['section'] = $dm;
                }
            }
        } else {

            foreach ($domainTree as $domain) {
                if ($domain['id'] == $task["section_id"]) {
                    $dm = $domain;
                    $dm['children'] = $domain;
                    $task['section'] = $dm;
                } else {
                    foreach ($domain['children'] as $subDomain) {
                        if ($subDomain['id'] == $task["section_id"]) {
                            $dm = $domain;
                            $dm['children'] = $subDomain;
                            $task['section'] = $dm;
                        }
                    }
                }
            }
        }
        $task["user"] = new stdClass();
        if (isset($task['user_id'])) {
            foreach ($members['list'] as $member) {
                if ($member['id'] == $task['user_id'])
                    $task["user"] = $member;
            }
        }

        if (empty($progressList)) {
            $progress = $this->getComplianceTaskProgress(['task_id' => $task["id"]]);
            $task["progress"] = (sizeof($progress) > 0) ? $progress : (new stdClass());
        } else {

            $conditionPrams = $task['type'] == 'maturity' ?
                [
                    [
                        'field' => 'task_id',
                        'value' => $task['id']
                    ]
                ] :
                [
                    [
                        'field' => 'task_id',
                        'value' => $task['id']
                    ],
                    [
                        'field' => 'parent_id',
                        'value' => 0
                    ]
                ];

            /// TODO: handle it if a user is admin and has a child task
            $task["progress"] = $this->findElements(
                $progressList,
                $conditionPrams
            )[0] ?? (new stdClass());

            $task["progress_child"] = $this->findElements(
                $progressList,
                [
                    [
                        'field' => 'task_id',
                        'value' => $task['id']
                    ],
                    [
                        'field' => 'type',
                        'value' => 'child'
                    ]
                ]
            )[0] ?? (new stdClass());
        }

        if (($task['type'] ?? '') === 'risk' && !empty($task['id'])) {
            $stored = $this->taskRepository->getTaskInformationDecoded((int) $task['id']);
            if (is_array($stored)) {
                foreach (['mandatory_unit', 'progress', 'progress_child'] as $snapshotKey) {
                    if (array_key_exists($snapshotKey, $stored)) {
                        $task[$snapshotKey] = $stored[$snapshotKey];
                    }
                }
            }
        }

        return $task;
    }

    public
    function getTaskTreeWhitFilter(mixed $params, mixed $account): array
    {
        $limit = $params['limit'] ?? 2000;
        $page = $params['page'] ?? 1;
        $order = $params['order'] ?? ['time_create DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;

        // Set params
        $listParams = [
            'order' => $order,
            'offset' => $offset,
            'limit' => $limit,
            'status' => 1,
            'reference_id' => $params['reference_id'],
        ];
        ///default type
        $listParams['type'] = 'compliance';
        $listParams['parent_id'] = 0;

        if (isset($params['type'])) {
            $listParams['type'] = $params['type'];
        }
        if (isset($params['parent_id'])) {
            $listParams['parent_id'] = $params['parent_id'];
        }

        if (isset($params['id'])) {
            $listParams['id'] = $params['id'];
        }

        if (isset($params['mandatory_unit'])) {
            $listParams['mandatory_unit'] = $params['mandatory_unit'];
        }

        $filters = $this->prepareProgressFilter($params);

        // for handle when only send date for filter progress list
        if ((isset($params['enforce_data_from']) && $params['enforce_data_from'] != null) || (isset($params['enforce_data_to']) && ($params['enforce_data_to']) != null)) {
            if (empty($filters)) {
                $filters['level'] = ["todo", "done", "approve", "doing", "reject"];
            }
        }

        if (!empty($filters)) {
            $isFresh = true;
            foreach ($filters as $filter) {
                $hasProgressWaitingFilter = false;
                $notWaitingId = [];
                $waitingId = [];
                if ($filter['type'] == 'value') {
                    $index = array_search('waiting', $filter['value']);
                    if ($index !== false) {
                        $hasProgressWaitingFilter = true;
                        unset($filter['value'][$index]);
                    }
                }
                if ($hasProgressWaitingFilter) {
                    $taskProgressList = $this->taskRepository->getProgressList();
                    foreach ($taskProgressList as $taskProgress) {
                        $notWaitingId[] = $taskProgress->getTaskId();
                    }
                    $allTask = $this->taskRepository->getTaskList([
                        'order' => 'id ASC',
                        'offset' => 0,
                        'limit' => 3000,
                        'status' => 1,
                    ]);
                    foreach ($allTask as $task) {
                        if (!in_array($task->getId(), $notWaitingId)) {
                            $waitingId[] = $task->getId();
                        }
                    }
                }
                $itemIdList = [];

                if (isset($params['enforce_data_from']) && $params['enforce_data_from'] != null) {
                    $filter['data_from'] = strtotime(
                        sprintf('%s 00:00:00', $params['enforce_data_from'])
                    );
                }

                if (isset($params['enforce_data_to']) && ($params['enforce_data_to']) != null) {
                    $filter['data_to'] = strtotime(
                        sprintf('%s 00:00:00', $params['enforce_data_to'])
                    );
                }

                $rowSet = $this->taskRepository->getTaskIdFromComplianceProgress($filter);
                foreach ($rowSet as $row) {
                    $itemIdList[] = $this->canonizeTaskId($row);
                }
                if ($filter['type'] == 'value' && $hasProgressWaitingFilter) {
                    $itemIdList = array_unique(array_merge($itemIdList, $waitingId));
                }
                if ($isFresh) {
                    $listParams['id'] = $itemIdList;
                    $isFresh = false;
                } else {
                    $listParams['id'] = array_intersect($listParams['id'], $itemIdList);
                }
            }

        }

        if (isset($params['data_from']) && $params['data_from'] != null) {
            $listParams['data_from'] = strtotime(
                sprintf('%s 00:00:00', $params['data_from'])
            );
        }

        if (isset($params['data_to']) && ($params['data_to']) != null) {
            $listParams['data_to'] = strtotime(
                sprintf('%s 00:00:00', $params['data_to'])
            );
        }


        if (!empty($params['section_id']))
            $listParams['section_id'] = explode(',', $params['section_id']);
        if (!empty($params['warranty_id']))
            $listParams['warranty_id'] = explode(',', $params['warranty_id']);
        if (!empty($params['rule_id']))
            $listParams['rule_id'] = explode(',', $params['rule_id']);
        if (!empty($params['user_id']))
            $listParams['user_id'] = explode(',', $params['user_id']);
        if (isset($params['title']))
            $listParams['title'] = $params['title'];
        if (isset($params['code']))
            $listParams['code'] = $params['code'];
        if (isset($params['mandatory_unit']))
            $listParams['mandatory_unit'] = $params['mandatory_unit'];

        $taskList = array();
        $domainTree = $this->getDomainTree([], []);
        $members = $this->listMember([]);
        $progressParentList = [];

        $condition = $params['type'] == 'maturity' ?
            [
            ] :
            [
                'parent_id' => 0
            ];
        $progressObjectList = $this->taskRepository->getProgressList(
            $condition
        );
        foreach ($progressObjectList as $progressObject) {
            $progressParentList[] = $this->canonizeTaskProgress($progressObject);
        }
        $progressChildList = [];
        $progressObjectList = $this->taskRepository->getProgressList(
            [
                'type' => 'child',
                'user_id' => $account['id']
            ]
        );
        foreach ($progressObjectList as $progressObject) {
            $progressChildList[] = $this->canonizeTaskProgress($progressObject);
        }


        $progressList = array_merge($progressParentList, $progressChildList);
        $warranties = $this->getWarrantiesTree();
        $rules = $this->getRulesTree();
        $answerList = $this->getAnswersList(['type' => $listParams['type']], [])['data']['list'];

        $listData = $this->taskRepository->getTaskList($listParams);
        foreach ($listData as $task) {
            $taskList[] = $this->canonizeTaskTreeList([
                'task' => $task,
                'domain_tree' => $domainTree,
                'members' => $members,
                'rules' => $rules,
                'warranties' => $warranties,
                'answer_list' => $answerList,
                'progress_list' => $progressList
            ]);
        }

        // Get count
        $count = $this->taskRepository->getTaskCount($listParams);

        return [
            'result' => true,
            'data' => [
                'list' => $taskList,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page' => $page,
                ],
                'filters' => null,
            ],
            'error' => [],
        ];
    }

    public
    function getTask(mixed $params, mixed $account): array
    {
        return $this->canonizeTaskList($this->taskRepository->getTask($params), $this->listMember([]));
    }

    public function getRoleResourceList(mixed $params): array
    {
        $roles = $this->roleService->getRoleResourceList();
        return array_values(array_filter($roles, function ($item) {
            return $item["section"] === "api";
        }));
    }

    /**
     * Persistable JSON for erm_task_list.mandatory_unit (avoids [] when DB holds valid JSON string).
     */
    private function encodeMandatoryUnitForTaskStorage(mixed $rawMandatoryUnit, array $canonizedFallback): string
    {
        if (is_string($rawMandatoryUnit) && $rawMandatoryUnit !== '') {
            $decoded = json_decode($rawMandatoryUnit, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }

            return $rawMandatoryUnit;
        }
        if (is_array($rawMandatoryUnit)) {
            return json_encode($rawMandatoryUnit, JSON_UNESCAPED_UNICODE);
        }

        return json_encode($canonizedFallback ?: [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Same shape as list/tree: full progress_list so mandatory_unit, progress, progress_child match the compliance task.
     */
    private function buildComplianceTaskSnapshotForRisk(int $taskId, array $domainTree, array $members, array $rules, array $warranties): array
    {
        $row = $this->taskRepository->getTask(['id' => $taskId]);
        if (empty($row)) {
            return [
                'mandatory_unit' => [],
                'progress' => new stdClass(),
                'progress_child' => new stdClass(),
            ];
        }
        $progressList = [];
        foreach ($this->taskRepository->getProgressList(['task_id' => $taskId]) as $progressObject) {
            $progressList[] = $this->canonizeTaskProgress($progressObject);
        }

        return $this->canonizeTaskTreeList([
            'task' => $row,
            'domain_tree' => $domainTree,
            'members' => $members,
            'rules' => $rules,
            'warranties' => $warranties,
            'answer_list' => [],
            'progress_list' => $progressList,
        ]);
    }

    public
    function complianceProgressTask(array $params, array $account): array
    {
        ///TODO:check this . this set for bank shahr
        if ($params['level'] == 'todo') {
            $params['level'] = 'doing';
        }

        $domainTree = $this->getDomainTree([], []);
        $members = $this->listMember([]);
        $result = $this->taskRepository->getTask(['id' => $params['task_id']]);
        $warranties = $this->getWarrantiesTree();
        $rules = $this->getRulesTree();
        $task = $this->canonizeTaskTreeList([
            'task' => $result,
            'domain_tree' => $domainTree,
            'members' => $members,
            'rules' => $rules,
            'warranties' => $warranties,
            'answer_list' => [],
            'progress_list' => []
        ]);

        $slug = $this->generateComplianceProgressSlug($task, $params);

        $progress = $this->taskRepository->getProgress(['slug' => $slug]);
        $progress = $this->canonizeTaskProgress($progress);
        $type = $params['type'] == 'parent' ? sizeof(explode(',', $params['user_id'])) == 1 ? 'single' : $params['type'] : $params['type'];
        $level = $type == 'parent' ? 'doing' : $params['level'];
        $history[] = [
            'time' => time(),
            'user_id' => $account['id'],
            'level' => $level,
            'status' => 'doing',
            'answer_score' => $params['answer_score'],
            'answer_value' => $params['answer_value'],
            'answer_note' => $params['answer_note'],
            'comment' => $params['comment'] ?? ''
        ];
        if (empty($progress)) {
            $paramsProgress = [
                'slug' => $slug,
                'standard_id' => $params['standard_id'],
                'section_id' => $task['section_id'],
                'task_id' => $task['id'],
                'assigner_id' => $params['assigner_id'],
                'type' => $type,
                'user_id' => $type == 'parent' ? $params['assigner_id'] : $params['user_id'],
                'parent_id' => $type != 'child' ? 0 : $params['parent_id'] ?? 0,
                'company_id' => $params['company_id'],
                'time_create' => time(),
                'time_update' => time(),
                'level' => $level,
                'history' => json_encode($history),
                'time_deadline' => strtotime(
                    sprintf('%s 00:00:00', $params['time_deadline'])
                ),
            ];
            $progress = $this->taskRepository->addProgress($paramsProgress);

            if ($type == 'parent') {
                $users = explode(',', $params['user_id']);
                foreach ($users as $user) {
                    $childParams = $params;
                    $childParams['type'] = 'child';
                    $childParams['user_id'] = $user;
                    $childParams['parent_id'] = $progress->getId();
                    if ($params['assigner_id'] != $user) {
                        $this->complianceProgressTask($childParams, $account);
                    }
                }
            }

        } else {

            $where = ['slug' => $progress['slug'],];

            $set = [];
            if (isset($params['level']) && !empty($params['level'])) {
                $set['level'] = $params['level'];
            }
            if (
                isset($params['answer_score'])
                && is_numeric($params['answer_score'])
                && isset($params['answer_value'])
                && !empty($params['answer_value'])
            ) {
                $set['answer_score'] = $params['answer_score'];
                $set['answer_value'] = $params['answer_value'];
            }
            if (isset($params['answer_note']) && !empty($params['answer_note'])) {
                $set['answer_note'] = $params['answer_note'];
            }

            if (!empty($set)) {
                $set['time_update'] = time();
                $history = $progress['history'];
                $history[] = [
                    'time' => time(),
                    'user_id' => $account['id'],
                    'level' => $level,
                    'status' => $params['answer_value'],
                    'answer_score' => $params['answer_score'],
                    'answer_value' => $params['answer_value'],
                    'answer_note' => $params['answer_note'],
                    'comment' => $params['comment'] ?? ''
                ];
                $set['history'] = json_encode($history);

                $this->taskRepository->updateProgress($where, $set);
            }

            $progress = $this->taskRepository->getProgress(['slug' => $slug]);

            if ($progress->getType() == 'parent') {
                $childProgressList = $this->taskRepository->getProgressList(['parent_id' => $progress->getId()]);
                foreach ($childProgressList as $childProgressObject) {
                    $level = in_array($progress->getLevel(), ['done', 'approve']) ? 'approve' : 'reject';
                    $status = $progress->getLevel();
                    $childProgress = $this->canonizeTaskProgress($childProgressObject);
                    $set['time_update'] = time();
                    $history = $childProgress['history'];
                    $history[] = [
                        'time' => time(),
                        'user_id' => $account['id'],
                        'level' => $level,
                        'status' => $status,
                        'answer_score' => $params['answer_score'],
                        'answer_value' => $params['answer_value'],
                        'answer_note' => $params['answer_note'],
                        'comment' => $params['comment'] ?? ''
                    ];
                    $set['history'] = json_encode($history);
                    $set['level'] = $level;
                    $set['status'] = $status;
                    $where = ['id' => $childProgress['id']];
                    $this->taskRepository->updateProgress($where, $set);

                }
            }

        }
        $task['progress'] = $this->canonizeTaskProgress($progress);
        $approvedComplianceProgressId = (int)($task['progress']['id'] ?? 0);

        if (($task['parent_id'] == 0) && ($task['progress']['level'] == 'approve')) {
            $sourceSnapshot = $this->buildComplianceTaskSnapshotForRisk(
                (int) $task['id'],
                $domainTree,
                $members,
                $rules,
                $warranties
            );
            $sourceRow = $this->taskRepository->getTask(['id' => $task['id']]);
            $sourceMandatoryRaw = is_object($sourceRow) ? $sourceRow->getMandatoryUnit() : ($sourceRow['mandatory_unit'] ?? null);
            $mandatoryUnitJson = $this->encodeMandatoryUnitForTaskStorage(
                $sourceMandatoryRaw,
                is_array($sourceSnapshot['mandatory_unit'] ?? null) ? $sourceSnapshot['mandatory_unit'] : []
            );

            $task['mandatory_unit'] = $sourceSnapshot['mandatory_unit'] ?? [];
            $task['progress'] = $sourceSnapshot['progress'] ?? $task['progress'];
            $task['progress_child'] = $sourceSnapshot['progress_child'] ?? new stdClass();

            $information = [
                "from" => "statement",
                "parent" => $task['id'],
                "compliance_progress_id" => is_array($task['progress']) && isset($task['progress']['id'])
                    ? (int)$task['progress']['id']
                    : $approvedComplianceProgressId,
                "maturity_progress_id" => 0,
                "risk_progress_id" => 0,
                "audit_progress_id" => 0,
                "mandatory_unit" => $task['mandatory_unit'],
                "progress" => $task['progress'],
                "progress_child" => $task['progress_child'],
            ];

            $referenceId = 0;
            if ($task['reference_id'] > 0) {

                $taskReference = $this->getTask(['id' => $task['reference_id']], $account);

                //if stored reference
                $reference = $this->getTask(['code' => 'risk-' . $taskReference["code"]], $account);
                //if reference not stored
                if (!isset($reference['id'])) {
                    $refSnapshot = $this->buildComplianceTaskSnapshotForRisk(
                        (int) $taskReference['id'],
                        $domainTree,
                        $members,
                        $rules,
                        $warranties
                    );
                    $refRow = $this->taskRepository->getTask(['id' => $taskReference['id']]);
                    $refMandatoryRaw = is_object($refRow) ? $refRow->getMandatoryUnit() : ($refRow['mandatory_unit'] ?? null);
                    $refMandatoryJson = $this->encodeMandatoryUnitForTaskStorage(
                        $refMandatoryRaw,
                        is_array($refSnapshot['mandatory_unit'] ?? null) ? $refSnapshot['mandatory_unit'] : []
                    );
                    $informationRef = array_merge($information, [
                        'mandatory_unit' => $refSnapshot['mandatory_unit'] ?? [],
                        'progress' => $refSnapshot['progress'] ?? new stdClass(),
                        'progress_child' => $refSnapshot['progress_child'] ?? new stdClass(),
                    ]);
                    $riskTask = [
                        "standard_id" => 1,
                        "code" => 'risk-' . $taskReference["code"],
                        "title" => $taskReference["title"],
                        "section_id" => $taskReference["section_id"],
                        "rule_id" => $taskReference["rule_id"],
                        "warranty_id" => $taskReference["warranty_id"],
                        "mandatory_unit" => $refMandatoryJson,
                        'user_id' => $account['id'],
                        'information' => json_encode($informationRef),
                        'parent_id' => $taskReference['id'],
                        'type' => 'risk',
                        "reference_id" => 0,
                        "has_clause" => 1
                    ];
                    $storedData = $this->storeTask($riskTask);
                    $referenceId = $storedData['id'];
                } else {
                    $referenceId = $reference['id'];
                }
            }


            $riskTask = [
                "standard_id" => 1,
                "code" => 'risk-' . $task["code"],
                "title" => $task["title"],
                "section_id" => $task["section_id"],
                "rule_id" => $task["rule_id"],
                "warranty_id" => $task["warranty_id"],
                "mandatory_unit" => $mandatoryUnitJson,
                'user_id' => $account['id'],
                'information' => json_encode($information),
                'parent_id' => $task['id'],
                'type' => 'risk',
                "reference_id" => $referenceId,
                "has_clause" => 0
            ];

            $this->storeTask($riskTask);


            ///TODO: check this for grc panel
//            $auditTask = $riskTask;
//            $auditTask['type'] = 'audit';
//            $this->storeTask($auditTask);
        }

        return $task;
    }


    public function generateComplianceProgressSlug($task, $params): string
    {
        $userId = $params['type'] == 'parent' ? $params['assigner_id'] : $params['user_id'];
        return md5(
            sprintf(
                '%d-%d-%d-%d-%d-%d',
                (int)$task['standard_id'],
                (int)$task['section_id'],
                (int)$task['id'],
                (int)$params['company_id'],
                (int)($userId),
                (int)$params['assigner_id']
            )
        );
    }

    public
    function getComplianceTaskProgress(array $params): array
    {
        return $this->canonizeTaskProgress($this->taskRepository->getProgress($params));
    }

    public function getComplianceTaskProgressDetail(array $params, $account): array
    {
        $progressObjectList = $this->taskRepository->getProgressList(['parent_id' => $params['progress_id'], 'task_id' => $params['task_id']]);
        $progressList = [];
        $report = [
            'todo' => 0,
            'doing' => 0,
            'done' => 0,
            'approve' => 0,
            'reject' => 0,
            'average' => 0
        ];
        foreach ($progressObjectList as $progressObject) {
            $progress = $this->canonizeTaskProgress($progressObject);
            $report[$progress['level']]++;
            if ($progress['level'] == 'done') {
                $report['average'] = $report['average'] + $progress['answer_score'];
            }
            $progressList[] = $progress;
        }
        $report['average'] = $report['done'] > 0 ? $report['average'] / $report['done'] : 0;
        $task = $this->getTask(['id' => $params['task_id']], []);
        return [
            'task' => $task,
            'progress_list' => $progressList,
            'report' => $report
        ];
    }

    public function getRiskTaskProgressDetail(array $params, $account): array
    {
        $members = $this->listMember([]);
        $progressObjectList = $this->taskRepository->getRiskProgressList(['parent_id' => $params['progress_id'], 'task_id' => $params['task_id']]);
        $progressList = [];
        $report = [
            'todo' => 0,
            'doing' => 0,
            'done' => 0,
            'approve' => 0,
            'reject' => 0,
            'risk_data' => 0,
            'risk_intensity' => 0,
            'risk_effect' => 0,
            'risk_execution_percent' => 0,
        ];
        foreach ($progressObjectList as $progressObject) {
            $progress = $this->canonizeTaskRisk($progressObject, $members);
            $report[$progress['level']]++;
            if ($progress['level'] == 'done') {
                $report['risk_data'] = $report['risk_data'] + $progress['risk_data'];
                $report['risk_intensity'] = $report['risk_intensity'] + $progress['risk_intensity'];
                $report['risk_effect'] = $report['risk_effect'] + $progress['risk_effect'];
                $report['risk_execution_percent'] = $report['risk_execution_percent'] + $progress['risk_execution_percent'];
            }
            $progressList[] = $progress;
        }
        $report['risk_intensity'] = intval($report['done'] > 0 ? $report['risk_intensity'] / $report['done'] : 0);
        $report['risk_effect'] = intval($report['done'] > 0 ? $report['risk_effect'] / $report['done'] : 0);
        $report['risk_data'] = $report['risk_intensity'] * $report['risk_effect'];
        $report['risk_execution_percent'] = intval($report['done'] > 0 ? $report['risk_execution_percent'] / $report['done'] : 0);

        $task = $this->getTask(['id' => $params['task_id']], []);
        return [
            'task' => $task,
            'progress_list' => $progressList,
            'report' => $report
        ];
    }


    public function prepareProgressFilter($params): array
    {
        // Set filter list
        $filters = [];
        foreach ($params as $key => $value) {
            if (in_array($key, ['enforcer', 'level', 'max_risk', 'min_risk', 'risk_response_type'])) {
                switch ($key) {
                    case 'max_risk':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'field' => 'risk_data',
                                'value' => $value,
                                'type' => 'rangeMax',
                            ];
                        break;

                    case 'min_risk':
                        if (($value != '') && !empty($value) && ($value != null))
                            $filters[$key] = [
                                'field' => 'risk_data',
                                'value' => $value,
                                'type' => 'rangeMin',
                            ];
                        break;

                    case 'risk_response_type':
                        if (($value != '') && !empty($value) && ($value != null) && sizeof(explode(',', $value)) > 0)
                            $filters[$key] = [
                                'field' => 'risk_response_type',
                                'value' => explode(',', $value),
                                'type' => 'value',
                            ];
                        break;
                    case 'enforcer':
                        if (($value != '') && !empty($value) && ($value != null) && sizeof(explode(',', $value)) > 0)
                            $filters[$key] = [
                                'field' => 'user_id',
                                'value' => explode(',', $value),
                                'type' => 'value',
                            ];
                        break;
                    case 'level':
                        if (($value != '') && !empty($value) && ($value != null) && sizeof(explode(',', $value)) > 0)
                            $filters[$key] = [
                                'field' => 'level',
                                'value' => explode(',', $value),
                                'type' => 'value',
                            ];
                        break;
                }
            }
        }
        return $filters;
    }

    public function canonizeTaskId(object|array $filter): int|null
    {
        if (empty($filter)) {
            return 0;
        }

        if (is_object($filter)) {
            $taskId = $filter->getTaskId();
        } else {
            $taskId = $filter['task_id'];
        }

        return $taskId;
    }


    /// risk services section

    public function getRiskList(mixed $params, mixed $account): array
    {
        $limit = $params['limit'] ?? 1250;
        $page = $params['page'] ?? 1;
        $order = $params['order'] ?? ['time_create DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;


        // Set params
        $listParams = [
            'order' => $order,
            'offset' => $offset,
            'limit' => $limit,
            'status' => 1,
            'reference_id' => $params['reference_id'],
        ];

        $complianceFilterParams = [];
        $hasComplianceFilter = true;

        if (
            isset($params['compliance_enforcer'])
            && $params['compliance_enforcer'] != ''
            && $params['compliance_enforcer'] != null
            && !empty($params['compliance_enforcer'])
        ) {
            $hasComplianceFilter = true;
            $complianceFilterParams['enforcer'] = $params['compliance_enforcer'];
        } else {
            $complianceFilterParams['enforcer'] = null;
        }

        $complianceFilter = $this->prepareProgressFilter($complianceFilterParams);


        if (!empty($complianceFilter)) {
            $isFresh = true;
            foreach ($complianceFilter as $filter) {

                $itemIdList = [];
                $rowSet = $this->taskRepository->getTaskIdFromComplianceProgress($filter);
                foreach ($rowSet as $row) {
                    $itemIdList[] = $this->canonizeTaskId($row);
                }
                if ($isFresh) {
                    $complianceFilter['id'] = $itemIdList;
                    $isFresh = false;
                } else {
                    $complianceFilter['id'] = array_intersect($complianceFilter['id'], $itemIdList);

                }
            }

        }
        /// end check compliance progress level


        $filters = $this->prepareProgressFilter($params);

        if (!empty($filters)) {
            $isFresh = true;
            foreach ($filters as $filter) {
                $hasRiskWaitingFilter = false;
                $notWaitingId = [];
                $waitingId = [];
                if ($filter['type'] == 'value' && $filter['field'] != 'risk_response_type') {
                    $index = array_search('waiting', $filter['value']);
                    if ($index !== false) {
                        $hasRiskWaitingFilter = true;
                        unset($filter['value'][$index]);
                    }
                }
                if ($hasRiskWaitingFilter) {
                    $riskProgressList = $this->taskRepository->getRiskProgressList(['parent_id'=>0]);
                    foreach ($riskProgressList as $riskProgress) {
                        $notWaitingId[] = $riskProgress->getTaskId();
                    }
                    $allTask = $this->taskRepository->getTaskList([
                        'order' => 'id ASC',
                        'type' => 'risk',
                        'offset' => 0,
                        'limit' => 1250,
                        'status' => 1,
                    ]);
                    foreach ($allTask as $task) {
                        if (!in_array($task->getId(), $notWaitingId)) {
                            $waitingId[] = $task->getId();
                        }
                    }
                }
                $itemIdList = [];
                $rowSet = $this->taskRepository->getTaskIdFromRiskProgress($filter);
                foreach ($rowSet as $row) {
                    $itemIdList[] = $this->canonizeTaskId($row);
                }
                if ($filter['type'] == 'value' && $hasRiskWaitingFilter) {
                    $itemIdList = array_unique(array_merge($itemIdList, $waitingId));
                }
                if ($isFresh) {
                    $listParams['id'] = $itemIdList;
                    $isFresh = false;
                } else {
                    $listParams['id'] = array_intersect($listParams['id'], $itemIdList);
                }
            }
        }

        if ($hasComplianceFilter && !empty($filters)) {
            if (!empty($complianceFilter)) {
                $listParams['id'] = array_intersect($listParams['id'], $complianceFilter['id']);

            }
        } else if ($hasComplianceFilter) {
            $listParams['id'] = $complianceFilter['id'];
        }

        if (isset($params['data_from']) && $params['data_from'] != null) {
            $listParams['data_from'] = strtotime(
                sprintf('%s 00:00:00', $params['data_from'])
            );
        }

        if (isset($params['data_to']) && ($params['data_to']) != null) {
            $listParams['data_to'] = strtotime(
                sprintf('%s 00:00:00', $params['data_to'])
            );
        }
        if (!empty($params['section_id']))
            $listParams['section_id'] = explode(',', $params['section_id']);
        if (!empty($params['warranty_id']))
            $listParams['warranty_id'] = explode(',', $params['warranty_id']);
        if (!empty($params['rule_id']))
            $listParams['rule_id'] = explode(',', $params['rule_id']);
        if (!empty($params['user_id']))
            $listParams['user_id'] = explode(',', $params['user_id']);
        if (isset($params['title']))
            $listParams['title'] = $params['title'];
        if (isset($params['code']))
            $listParams['code'] = $params['code'];
        if (isset($params['id']))
            $listParams['id'] = $params['id'];

        $taskList = array();
        $domainTree = $this->getDomainTree([], []);
        $members = $this->listMember([]);

        $progressParentList = [];
        $progressObjectList = $this->taskRepository->getProgressList(
            [
                'parent_id' => 0
            ]
        );
        foreach ($progressObjectList as $progressObject) {
            $progressParentList[] = $this->canonizeTaskProgress($progressObject);
        }
        $progressChildList = [];
        $progressObjectList = $this->taskRepository->getProgressList(
            [
                'type' => 'child',
                'user_id' => $account['id']
            ]
        );
        foreach ($progressObjectList as $progressObject) {
            $progressChildList[] = $this->canonizeTaskProgress($progressObject);
        }

        $progressList = array_merge($progressParentList, $progressChildList);

        $warranties = $this->getWarrantiesTree();
        $rules = $this->getRulesTree();
        $answerList = $this->getAnswersList(['type' => $listParams['type']], [])['data']['list'];

        $listParams['type'] = 'risk';

        $riskTaskList = $this->taskRepository->getTaskList($listParams);
        foreach ($riskTaskList as $task) {
            $taskList[] = $this->canonizeTaskTreeList([
                'task' => $task,
                'domain_tree' => $domainTree,
                'members' => $members,
                'rules' => $rules,
                'warranties' => $warranties,
                'answer_list' => $answerList,
                'progress_list' => $progressList
            ]);
        }


        $riskProgressRow = $this->taskRepository->getRiskList([]);
        $riskProgressList = [];
        foreach ($riskProgressRow as $item) {
            $riskProgressList[] = $this->canonizeTaskRisk($item, $members);
        }


        $progressParentList = [];
        $progressObjectList = $this->taskRepository->getRiskProgressList(
            [
                'parent_id' => 0
            ]
        );
        foreach ($progressObjectList as $progressObject) {
            $progressParentList[] = $this->canonizeTaskRisk($progressObject, $members);
        }
        $progressChildList = [];
        $progressObjectList = $this->taskRepository->getRiskProgressList(
            [
                'type' => 'child',
                'user_id' => $account['id']
            ]
        );
        foreach ($progressObjectList as $progressObject) {
            $progressChildList[] = $this->canonizeTaskRisk($progressObject, $members);
        }

        $riskProgressList = array_merge($progressParentList, $progressChildList);


        for ($i = 0; $i < sizeof($taskList); $i++) {
            $taskList[$i]['risk'] = new stdClass();
            /// TODO: handle it if a user is admin and has a child task
            $taskList[$i]['risk'] = $this->findElements(
                $riskProgressList,
                [
                    [
                        'field' => 'task_id',
                        'value' => $taskList[$i]['id']
                    ],
                    [
                        'field' => 'parent_id',
                        'value' => 0
                    ]
                ]
            )[0] ?? (new stdClass());

            $taskList[$i]['risk_child'] = $this->findElements(
                $riskProgressList,
                [
                    [
                        'field' => 'task_id',
                        'value' => $taskList[$i]['id']
                    ],
                    [
                        'field' => 'type',
                        'value' => 'child'
                    ]
                ]
            )[0] ?? (new stdClass());
        }


        // Get count
        $count = $this->taskRepository->getTaskCount($listParams);

        return [
            'result' => true,
            'data' => [
                'list' => $taskList,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page' => $page,
                ],
                'filters' => null,
            ],
            'error' => [],
        ];
    }

    public function getRiskTaskProgress($params)
    {
        return $this->canonizeTaskRisk($this->taskRepository->getRisk($params), $this->listMember([]));
    }

    public function riskProgressTask($params, $account): array
    {
        $domainTree = $this->getDomainTree([], []);
        $members = $this->listMember([]);
        $result = $this->taskRepository->getTask(['id' => $params['task_id']]);
        $warranties = $this->getWarrantiesTree();
        $rules = $this->getRulesTree();

        $task = $this->canonizeTaskTreeList([
            'task' => $result,
            'domain_tree' => $domainTree,
            'members' => $members,
            'rules' => $rules,
            'warranties' => $warranties,
            'answer_list' => [],
            'progress_list' => []
        ]);

        $slug = $this->generateRiskProgressSlug($task, $params);
        $risk = $this->taskRepository->getRisk(['slug' => $slug]);
        $risk = $this->canonizeTaskRisk($risk, $members);
        $type = $params['type'] == 'parent' ? sizeof(explode(',', $params['user_id'])) == 1 ? 'single' : $params['type'] : $params['type'];
        $level = $type == 'parent' ? 'doing' : $params['level'];
        $history[] = [
            'time' => time(),
            'user_id' => $account['id'],
            'level' => $level,
            'status' => 'doing',
            'risk_intensity' => $params['risk_intensity'],
            'risk_effect' => $params['risk_effect'],
            'risk_threat' => $params['risk_threat'],
            'risk_damage' => $params['risk_damage'],
            'risk_execution_percent' => $params['risk_execution_percent'],
            'risk_proposed_action' => $params['risk_proposed_action'],
            'risk_response_type' => $params['risk_response_type'],
            'risk_data' => $params['risk_data'],
            'risk_scenario' => $params['risk_scenario'],
            'comment' => $params['comment'] ?? ''
        ];
        if (empty($risk)) {
            $paramsRisk = [
                'assigner_id' => $params['assigner_id'],
                'type' => $type,
                'parent_id' => $type != 'child' ? 0 : $params['parent_id'] ?? 0,
                'time_create' => time(),
                'time_update' => time(),
                'level' => $level,
                'history' => json_encode($history),
                'time_deadline' => strtotime(
                    sprintf('%s 00:00:00', $params['time_deadline'])
                ),
                'slug' => $slug,
                'standard_id' => $params['standard_id'],
                'section_id' => $task['section_id'],
                'task_id' => $task['id'],
                'user_id' => $type == 'parent' ? $params['assigner_id'] : $params['user_id'],
                'company_id' => $params['company_id'],
                'risk_intensity' => $params['risk_intensity'],
                'risk_effect' => $params['risk_effect'],
                'risk_data' => $params['risk_data'],
                'risk_threat' => $params['risk_threat'],
                'risk_damage' => $params['risk_damage'],
                'risk_response_type' => $params['risk_response_type'],
                'risk_execution_percent' => $params['risk_execution_percent'],
                'risk_proposed_action' => $params['risk_proposed_action'],
                'risk_scenario' => $params['risk_scenario'],
            ];
            $risk = $this->taskRepository->addRisk($paramsRisk);
            $task['risk'] = $this->canonizeTaskRisk($risk, $members);

            if ($type == 'parent') {
                $users = explode(',', $params['user_id']);
                foreach ($users as $user) {
                    $childParams = $params;
                    $childParams['type'] = 'child';
                    $childParams['user_id'] = $user;
                    $childParams['parent_id'] = $risk->getId();
                    if ($params['assigner_id'] != $user) {
                        $this->riskProgressTask($childParams, $account);
                    }
                }
            }

        } else {

            $where = ['slug' => $risk['slug'],];

            $set = [];
            if (isset($params['level']) && !empty($params['level'])) {
                $set['level'] = $params['level'];
            }
            if (
                isset($params['risk_intensity'])
                && is_numeric($params['risk_intensity'])
                && isset($params['risk_effect'])
                && is_numeric($params['risk_effect'])
            ) {
                $set['risk_intensity'] = $params['risk_intensity'];
                $set['risk_effect'] = $params['risk_effect'];
            }
            if (
                isset($params['risk_data'])
                && is_numeric($params['risk_data'])
            ) {
                $set['risk_data'] = $params['risk_data'];
            }

            $set['risk_threat'] = $params['risk_threat'];
            $set['risk_damage'] = $params['risk_damage'];
            $set['risk_scenario'] = $params['risk_scenario'];

            if (isset($params['risk_execution_percent']) && !empty($params['risk_execution_percent'])) {
                $set['risk_execution_percent'] = $params['risk_execution_percent'];
            }
            if (isset($params['risk_proposed_action']) && !empty($params['risk_proposed_action'])) {
                $set['risk_proposed_action'] = $params['risk_proposed_action'];
            }
            if (isset($params['risk_response_type']) && !empty($params['risk_response_type'])) {
                $set['risk_response_type'] = $params['risk_response_type'];
            }


            if (!empty($set)) {
                $set['time_update'] = time();
                $history = $risk['history'];
                $history[] = [
                    'time' => time(),
                    'user_id' => $account['id'],
                    'level' => $level,
                    'status' => 'doing',
                    'risk_intensity' => $params['risk_intensity'],
                    'risk_effect' => $params['risk_effect'],
                    'risk_threat' => $params['risk_threat'],
                    'risk_damage' => $params['risk_damage'],
                    'risk_execution_percent' => $params['risk_execution_percent'],
                    'risk_proposed_action' => $params['risk_proposed_action'],
                    'risk_response_type' => $params['risk_response_type'],
                    'risk_data' => $params['risk_data'],
                    'risk_scenario' => $params['risk_scenario'],
                    'comment' => $params['comment'] ?? ''
                ];
                $set['history'] = json_encode($history);
                $this->taskRepository->updateRisk($where, $set);
            }

            $risk = $this->taskRepository->getRisk(['slug' => $slug]);

            if ($risk->getType() == 'parent') {
                $childProgressList = $this->taskRepository->getRiskProgressList(['parent_id' => $risk->getId()]);
                foreach ($childProgressList as $childProgressObject) {
                    $level = in_array($risk->getLevel(), ['done', 'approve']) ? 'approve' : 'reject';
                    $status = $risk->getLevel();
                    $childProgress = $this->canonizeTaskRisk($childProgressObject, $members);
                    $set['time_update'] = time();
                    $history = $childProgress['history'];
                    $history[] = [
                        'time' => time(),
                        'user_id' => $account['id'],
                        'level' => $level,
                        'status' => $status,
                        'risk_intensity' => $params['risk_intensity'],
                        'risk_effect' => $params['risk_effect'],
                        'risk_threat' => $params['risk_threat'],
                        'risk_damage' => $params['risk_damage'],
                        'risk_execution_percent' => $params['risk_execution_percent'],
                        'risk_proposed_action' => $params['risk_proposed_action'],
                        'risk_response_type' => $params['risk_response_type'],
                        'risk_data' => $params['risk_data'],
                        'risk_scenario' => $params['risk_scenario'],
                        'comment' => $params['comment'] ?? ''
                    ];
                    $set['history'] = json_encode($history);
                    $set['level'] = $level;
                    $set['status'] = $status;
                    $where = ['id' => $childProgress['id']];
                    $this->taskRepository->updateRisk($where, $set);
                }
            }

        }
        $task['risk'] = $this->canonizeTaskRisk($risk, $members);

        $assigneeCsv = isset($params['user_id']) ? (string) $params['user_id'] : '';
        $multiAssign = str_contains($assigneeCsv, ',');
        if (
            !empty($task['risk'])
            && $assigneeCsv !== ''
            && (($params['type'] ?? '') === 'parent' || $multiAssign)
        ) {
            $this->applyRiskParentAssigneesToUserDisplay(
                $task['risk'],
                $members,
                $assigneeCsv
            );
        }

        if (($task['parent_id'] == 0) && ($task['risk']['level'] == 'approve')) {
            $information = [
                "from" => "statement",
                "parent" => $task['id'],
                "compliance_progress_id" => isset($task['progress']) ? isset($task['progress']['id']) ? $task['progress']['id'] : 0 : 0,
                "maturity_progress_id" => 0,
                "risk_progress_id" => $task['risk']['id'],
                "audit_progress_id" => 0,
            ];
            $auditTask = [
                "standard_id" => 1,
                "code" => 'risk-' . $task["code"],
                "title" => $task["title"],
                "section_id" => $task["section_id"],
                "rule_id" => $task["rule_id"],
                "warranty_id" => $task["warranty_id"],
                "mandatory_unit" => json_encode($task["mandatory_unit"] ?? []),
                'user_id' => $account['id'],
                'information' => json_encode($information),
                'parent_id' => $task['id'],
                'type' => 'audit',
            ];
            $this->storeTask($auditTask);
        }

        return $task;

    }

    private function generateRiskProgressSlug(array $task, $params)
    {
        $userId = $params['type'] == 'parent' ? $params['assigner_id'] : $params['user_id'];
        return md5(
            sprintf(
                '%d-%d-%d-%d-%d-%d',
                (int)$task['standard_id'],
                (int)$task['section_id'],
                (int)$task['id'],
                (int)$params['company_id'],
                (int)($userId),
                (int)$params['assigner_id']
            )
        );
    }


    ///find a element in a array via conditions
    public function findElements($array, $conditions): array
    {
        $result = [];
        foreach ($array as $element) {
            $matchesAllConditions = true;

            foreach ($conditions as $condition) {
                $value = $condition['value'];
                $field = $condition['field'];

                if ($element[$field] !== $value) {
                    $matchesAllConditions = false;
                    break;
                }
            }

            if ($matchesAllConditions) {
                $result[] = $element;
            }
        }
        return $result;
    }

    public function viewMember(mixed $params, mixed $operator): array
    {
        $userLog = $this->loggerService->getUserLog([
            'user_id' => $params['user_id'],
            'limit' => 25,
            'page' => 1,
        ]);
        $userInventory = $this->loggerService->readInventoryLog([
            'user_id' => $params['user_id'],
            'limit' => 25,
            'page' => 1,
        ]);
        $account = $this->accountService->getAccountByOperator(['id' => (int)$params['user_id']]);
        $profile = $this->accountService->getProfileByOperator(['user_id' => (int)$params['user_id']]);
        $member = array_merge($account, $profile);
        $member['user_log'] = $userLog['data']['list'];
        $member['user_inventory'] = $userInventory['data']['list'];


        $member['mandatory_unit'] = $this->canonizeMandatoryUnitMember(
            $this->taskRepository->getMandatoryUnitMember(
                [
                    'user_id' => $params['user_id']
                ]
            )
        )['mandatory_unit'];
        return $member;
    }

    public function updateMember(mixed $params, mixed $operator): array
    {
        $account = $this->accountService->getAccountByOperator(['id' => (int)$params['user_id']]);
        if (empty($account)) {
            return [
                'result' => false,
                'data' => new stdClass(),
                'error' => [
                    'message' => 'Account not found!',
                    'code' => 404
                ]
            ];
        }

        $this->taskRepository->destroyMandatoryUnitMember(['user_id' => $params['user_id']]);
        $profile = $this->accountService->updateAccount($params, $account, $operator);
        if (isset($params['roles'])) {
            $roles = explode(',', $params['roles']);
            $forbiddenRoles = $this->roleService->getAdminRoleList();
            $forbiddenRoles[] = 'member';
            $roles = array_diff($roles, $forbiddenRoles);
            foreach ($roles as $role) {
                $this->roleService->addRoleAccount($account, $role, 'api', $operator);
            }
        }
        $mandatoryUnit = $this->taskRepository->storeMandatoryUnitMember([
            'user_id' => $params['user_id'],
            'mandatory_unit' => json_encode($params['mandatory_unit'] ?? []),
            'time_create' => time()
        ]);

        $profile['mandatory_unit'] = $this->canonizeMandatoryUnitMember($mandatoryUnit)['mandatory_unit'];

        return [
            'result' => true,
            'data' => $profile,
            'error' => new stdClass(),
        ];
    }

    public function updateStatusMember(mixed $params, mixed $operator)
    {
        $account = $this->accountService->getAccountByOperator(['id' => (int)$params['user_id']]);
        if (empty($account)) {
            return [
                'result' => false,
                'data' => new stdClass(),
                'error' => [
                    'message' => 'Account not found!',
                    'code' => 404
                ]
            ];
        }
        $listParams = [
            'status' => $params['status'] ?? 0,
            'user_id' => $account['id']
        ];
        return $this->accountService->updateStatusByOperator($listParams, $operator);
    }

    public function deleteMember(mixed $params, mixed $operator): array
    {
        $account = $this->accountService->getAccountByOperator(['id' => (int)$params['user_id']]);
        if (empty($account)) {
            return [
                'result' => false,
                'data' => new stdClass(),
                'error' => [
                    'message' => 'Account not found!',
                    'code' => 404
                ]
            ];
        }
        $listParams = [
            'user_id' => $account['id']
        ];
        $this->taskRepository->destroyMandatoryUnitMember($listParams);
        return $this->accountService->deleteUserByOperator($listParams, $operator);
    }

    public function updatePasswordMember(mixed $params, mixed $operator): array
    {
        $account = $this->accountService->getAccountByOperator(['id' => (int)$params['user_id']]);
        if (empty($account)) {
            return [
                'result' => false,
                'data' => new stdClass(),
                'error' => [
                    'message' => 'Account not found!',
                    'code' => 404
                ]
            ];
        }
        $listParams = [
            'user_id' => $account['id'],
            'credential' => $params['credential'],
        ];
        return $this->accountService->updatePasswordByOperator($listParams, $operator);
    }

    public function getRuleCategoryList(mixed $params, mixed $account): array
    {
        ///TODO: move to db

        if(in_array('insurance',$this->config['type'])){
            return [
                [
                    "id" => 4,
                    "slug" => "external-organization",
                    "value" => "external-organization",
                    "title" => "برون سازمانی"
                ],
            ];
        }else{
            return [
                [
                    "id" => 1,
                    "slug" => "domestic-bank",
                    "value" => "domestic-bank",
                    "title" => "داخلی بانک"
                ],
                [
                    "id" => 2,
                    "slug" => "upper-domestic",
                    "value" => "upper-domestic",
                    "title" => "داخلی بالادستی"
                ],
                [
                    "id" => 3,
                    "slug" => "international-foreign",
                    "value" => "international-foreign",
                    "title" => "خارجی بین المللی"
                ]
            ];
        }

    }

    public function getRuleTypeList(mixed $params, mixed $account): array
    {
        return $this->getErmMetaList(
            [
                'type' => ['type'],
                'target' => ['rule']
            ],
            $account
        )['data']['list'];
    }

    public function getRuleAuthorList(mixed $params, mixed $account): array
    {
        return $this->getErmMetaList(
            [
                'type' => ['author'],
                'target' => ['rule']
            ],
            $account
        )['data']['list'];
    }

    public function getMandatoryUnitList(): array
    {
        $list = [];
        foreach ($this->taskRepository->getMandatoryUnitList() as $mandatoryUnit) {
            $list[] = $this->canonizeMandatoryUnit($mandatoryUnit);
        }
        return $list;
    }

    public function canonizeMandatoryUnit($mandatoryUnit): array
    {
        if (empty($mandatoryUnit)) {
            return [];
        }

//        return [
//            'id' => $mandatoryUnit->getId(),
//            'slug' => $mandatoryUnit->getSlug(),
//            'title' => $mandatoryUnit->getTitle(),
//            'information' => $mandatoryUnit->getInformation(),
//        ];
        return json_decode($mandatoryUnit->getInformation(), true);
    }

    public function canonizeMandatoryUnitMember($item): array
    {
        if (empty($item)) {
            return array('mandatory_unit' => []);
        }

        return [
            'id' => $item->getId(),
            'user_id' => $item->getUserId(),
            'mandatory_unit' => json_decode($item->getMandatoryUnit()),
        ];
    }

    public function getAnswersList($params, $account): array
    {
        $answerList = [];
        $type = '';
        if (isset($params['type'])) {
            $type = is_array($params['type']) ? $params['type'][0] : $params['type'];
        } else {
            $type = $params['type'];
        }
        if (isset($this->config['answers'][$type])) {
            $answerList = $this->config['answers'][$type];
        }
        return [
            'result' => true,
            'data' => [
                'list' => $answerList,
                'paginator' => [
                    'count' => sizeof($answerList),
                    'limit' => 1000,
                    'page' => 1,
                ],
                'filters' => null,
            ],
            'error' => [],
        ];
    }

    function getTaskDetail(mixed $params, mixed $account): array
    {
        $task = $this->getTaskTreeWhitFilter(['parent_id' => 0, 'id' => $params['id'], 'type' => $params['type']], $account)['data']['list'][0];
        $task['children'] = $this->getTaskTreeWhitFilter(['parent_id' => $params['id'], 'type' => $params['type']], $account)['data']['list'];
        return $task;
    }

    public function maturityTaskProgress(array $params, mixed $account): array
    {
        $progress = null;
        ///TODO:check this . this set for bank shahr
        if ($params['level'] == 'todo') {
            $params['level'] = 'doing';
        }

        $domainTree = $this->getDomainTree(['type' => 'maturity'], []);
        $members = $this->listMember([]);
        $parent = $this->getTaskDetail(['id' => $params['task_id'], 'type' => 'maturity'], $account);

        $type = $params['type'];
        $level = $params['level'];

        $warranties = $this->getWarrantiesTree();
        $rules = $this->getRulesTree();

        ///TODO:check this logic
        if (!in_array($level, ['done', 'reject', 'approve']) || true) {

            $task = $this->canonizeTaskTreeList([
                'task' => $parent,
                'domain_tree' => $domainTree,
                'members' => $members,
                'rules' => $rules,
                'warranties' => $warranties,
                'answer_list' => [],
                'progress_list' => []
            ]);

            $slug = $this->generateComplianceProgressSlug($task, $params);
            $progress = $this->taskRepository->getProgress(['slug' => $slug]);
            $progress = $this->canonizeTaskProgress($progress);

            /////TODO:add this for parent
            //$level = $type == 'parent' ? 'doing' : $params['level'];
            $history[] = [
                'time' => time(),
                'user_id' => $params['user_id'],
                'level' => $level,
                'status' => 'doing',
                'answer_score' => $params['answer_score'],
                'answer_value' => $params['answer_value'],
                'answer_note' => $params['answer_note'],
                'comment' => $params['comment'] ?? ''
            ];
            if (empty($progress)) {
                $paramsProgress = [
                    'slug' => $slug,
                    'standard_id' => $params['standard_id'],
                    'section_id' => $task['section_id'],
                    'task_id' => $task['id'],
                    'assigner_id' => $params['assigner_id'],
                    'type' => $type,
                    'user_id' => $params['user_id'],
                    'parent_id' => 0,
                    'company_id' => $params['company_id'],
                    'time_create' => time(),
                    'time_update' => time(),
                    'level' => $level,
                    'answer_note' => $params['answer_note'] ?? '',
                    'history' => json_encode($history),
                    'time_deadline' => strtotime(
                        sprintf('%s 00:00:00', $params['time_deadline'])
                    ),
                ];
                $progress = $this->taskRepository->addProgress($paramsProgress);


            } else {
                $where = ['slug' => $progress['slug'],];
                $set = [];
                if (isset($params['level']) && !empty($params['level'])) {
                    $set['level'] = $params['level'];
                }

                //check if the level of progress is done then calculate score from children scores average
                if ($level == 'done') {
                    $sumScores = 0;
                    $score = null;
                    $index = 0;
                    if (isset($params['data'])) {
                        for ($index = 0; $index < sizeof($params['data']); $index++) {
                            $sumScores += (int)$params['data'][$index]['answer_score'];
                        }
                    }
                    $averageScore = $sumScores / $index;

                    foreach (array_column($this->config['answers']['maturity'], 'score') as $number) {
                        if ($score === null || abs($averageScore - $score) >= abs($score - $number)) {
                            $score = $number;
                        }
                    }
                    $value_list = [];
                    foreach ($this->config['answers']['maturity'] as $answer) {
                        $value_list[(string)$answer['score']] = $answer['value'];
                    }

                    $set['answer_score'] = $score;
                    $set['answer_value'] = $value_list[(string)$score] ?? '';
                }


                if (isset($params['answer_note']) && !empty($params['answer_note'])) {
                    $set['answer_note'] = $params['answer_note'];
                }

                if (!empty($set)) {
                    $set['time_update'] = time();
                    $history = $progress['history'];
                    $history[] = [
                        'time' => time(),
                        'user_id' => $params['user_id'],
                        'level' => $level,
                        'status' => $params['answer_value'],
                        'answer_score' => $params['answer_score'],
                        'answer_value' => $params['answer_value'],
                        'answer_note' => $params['answer_note'],
                        'comment' => $params['comment'] ?? ''
                    ];
                    $set['history'] = json_encode($history);

                    $this->taskRepository->updateProgress($where, $set);
                }

                $progress = $this->taskRepository->getProgress(['slug' => $slug]);

                if ($progress->getType() == 'maturity' && $progress->getParentId() == 0) {
                    $childProgressList = $this->taskRepository->getProgressList(['parent_id' => $progress->getId()]);
                    foreach ($childProgressList as $childProgressObject) {
                        $level = in_array($progress->getLevel(), ['done', 'approve']) ? 'approve' : 'reject';
                        $status = $progress->getLevel();
                        $childProgress = $this->canonizeTaskProgress($childProgressObject);
                        $set['time_update'] = time();
                        $history = $childProgress['history'];
                        $history[] = [
                            'time' => time(),
                            'user_id' => $account['id'],
                            'level' => $level,
                            'status' => $status,
                            'answer_score' => $params['answer_score'],
                            'answer_value' => $params['answer_value'],
                            'answer_note' => $params['answer_note'],
                            'comment' => $params['comment'] ?? ''
                        ];
                        $set['history'] = json_encode($history);
                        $set['level'] = $level;
                        $set['status'] = $status;
                        $where = ['id' => $childProgress['id']];
                        $this->taskRepository->updateProgress($where, $set);
                    }
                }

            }
        }

        $parentId = $progress ? $progress->getId() : 0;

        //set or update progress of child
        foreach ($parent['children'] as $task) {

            $answer_score = null;
            $answer_value = null;
            $answer_note = null;
            $comment = null;


            if (isset($params['data'])) {
                $data = $this->findObjectById($params['data'], $task['id']);
                $answer_score = $data['answer_score'] ?? null;
                $answer_value = $data['answer_value'] ?? null;
                $answer_note = $data['answer_note'] ?? null;
                $comment = $data['comment'] ?? null;
            }


            $task = $this->canonizeTaskTreeList([
                'task' => $task,
                'domain_tree' => $domainTree,
                'members' => $members,
                'rules' => $rules,
                'warranties' => $warranties,
                'answer_list' => [],
                'progress_list' => []
            ]);
            $slug = $this->generateComplianceProgressSlug($task, $params);

            $progress = $this->taskRepository->getProgress(['slug' => $slug]);
            $progress = $this->canonizeTaskProgress($progress);


            /////TODO:add this for parent
            //$level = $type == 'parent' ? 'doing' : $params['level'];
            $history[] = [
                'time' => time(),
                'user_id' => $account['id'],
                'level' => $level,
                'status' => 'doing',
                'answer_score' => $answer_score,
                'answer_value' => $answer_value,
                'answer_note' => $answer_note,
                'comment' => $comment,
            ];
            if (empty($progress)) {
                $paramsProgress = [
                    'slug' => $slug,
                    'standard_id' => $params['standard_id'],
                    'section_id' => $task['section_id'],
                    'task_id' => $task['id'],
                    'assigner_id' => $params['assigner_id'],
                    'type' => $type,
                    'user_id' => $params['user_id'],
                    'parent_id' => $parentId,
                    'company_id' => $params['company_id'],
                    'time_create' => time(),
                    'time_update' => time(),
                    'level' => $level,
                    'history' => json_encode($history),
                    'time_deadline' => strtotime(
                        sprintf('%s 00:00:00', $params['time_deadline'])
                    ),
                ];
                $progress = $this->taskRepository->addProgress($paramsProgress);
            } else {

                $where = ['slug' => $progress['slug'],];

                $set = [];
                if (isset($params['level']) && !empty($params['level'])) {
                    $set['level'] = $params['level'];
                }
                if (
                    isset($answer_score)
                    && is_numeric($answer_score)
                    && isset($answer_value)
                    && !empty($answer_value)
                ) {
                    $set['answer_score'] = $answer_score;
                    $set['answer_value'] = $answer_value;
                }
                if (isset($answer_note) && !empty($answer_note)) {
                    $set['answer_note'] = $answer_note;
                }

                if (!empty($set)) {
                    $set['time_update'] = time();
                    $history = $progress['history'];
                    $history[] = [
                        'time' => time(),
                        'user_id' => $account['id'],
                        'level' => $level,
                        'status' => $params['answer_value'],
                        'answer_score' => $params['answer_score'],
                        'answer_value' => $params['answer_value'],
                        'answer_note' => $params['answer_note'],
                        'comment' => $params['comment'] ?? ''
                    ];
                    $set['history'] = json_encode($history);

                    $this->taskRepository->updateProgress($where, $set);
                }

                $progress = $this->taskRepository->getProgress(['slug' => $slug]);

                if ($progress->getType() == 'parent') {
                    $childProgressList = $this->taskRepository->getProgressList(['parent_id' => $progress->getId()]);
                    foreach ($childProgressList as $childProgressObject) {
                        $level = in_array($progress->getLevel(), ['done', 'approve']) ? 'approve' : 'reject';
                        $status = $progress->getLevel();
                        $childProgress = $this->canonizeTaskProgress($childProgressObject);
                        $set['time_update'] = time();
                        $history = $childProgress['history'];
                        $history[] = [
                            'time' => time(),
                            'user_id' => $account['id'],
                            'level' => $level,
                            'status' => $status,
                            'answer_score' => $params['answer_score'],
                            'answer_value' => $params['answer_value'],
                            'answer_note' => $params['answer_note'],
                            'comment' => $params['comment'] ?? ''
                        ];
                        $set['history'] = json_encode($history);
                        $set['level'] = $level;
                        $set['status'] = $status;
                        $where = ['id' => $childProgress['id']];
                        $this->taskRepository->updateProgress($where, $set);

                    }
                }

            }
            $task['progress'] = $this->canonizeTaskProgress($progress);
        }


        //define risk control and audit control after approve maturity control
        if ($level == 'approve') {
            $task = $parent;
            $information = [
                "from" => "statement",
                "parent" => $task['id'],
                "compliance_progress_id" => $task['progress']['id'],
                "maturity_progress_id" => 0,
                "risk_progress_id" => 0,
                "audit_progress_id" => 0,
            ];
            $riskTask = [
                "standard_id" => 1,
                "code" => 'risk-' . $task["code"],
                "title" => $task["title"],
                "section_id" => $task["section_id"],
                "rule_id" => $task["rule_id"],
                "warranty_id" => $task["warranty_id"],
                "mandatory_unit" => json_encode($task["mandatory_unit"] ?? []),
                'user_id' => $account['id'],
                'information' => json_encode($information),
                'parent_id' => $task['id'],
                'type' => 'risk',
            ];
            $this->storeTask($riskTask);

            $auditTask = $riskTask;
            $auditTask['type'] = 'audit';
            $this->storeTask($auditTask);

        }

        return $this->getTaskDetail(['id' => $params['task_id'], 'type' => 'maturity'], $account);

    }

    public function insuranceTaskProgressUpload(object|array $params, mixed $account)
    {

        $media = $this->mediaService->getMedia(['id' => $params['file_id']]);
        if (isset($media['extension'])) {
            switch ($media['extension']) {
                case 'csv':
                    $reader = new Csv();
                    $reader->setInputEncoding('UTF-8');
                    $reader->setDelimiter(',');
                    $reader->setEnclosure('"');
                    $reader->setSheetIndex(0);
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load(realpath(__DIR__ . $this->config['directory']['download_path'] . $media['information']['storage']['file_path']));
                    $dataSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                    break;

                case 'xlsx':
                    $reader = new Xlsx();
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load(realpath(__DIR__ . $this->config['directory']['download_path'] . $media['information']['storage']['file_path']));
                    $dataSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                    break;

                case 'xls':
                    $reader = new Xls();
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load(realpath(__DIR__ . $this->config['directory']['download_path'] . $media['information']['storage']['file_path']));
                    $dataSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                    break;

                default:
                    $data = [];
                    break;
            }


            // Set array keys by first row title
            $i = 1;
            $data = [];
            $keys = array_shift($dataSheet);
            $keys = ['code', 'acceptance', 'acceptance_ratio', 'assignment', 'assignment_ratio', 'mount'];
            foreach ($dataSheet as $dataSingle) {
                $dataSingle = array_combine($keys, $dataSingle);
                foreach ($dataSingle as $key => $value) {
                    if (!empty($key) && !empty($value)) {
                        $data[$i][$key] = $value;
                    }
                }
                $i++;
            }

            $data = array_values($data);


            $listParams = [
                'order' => 'id desc',
                'offset' => 0,
                'limit' => 5000,
                'type' => ['insurance', 'insurance-comparison']
            ];

            $taskListObject = $this->getTaskTreeWhitFilter($listParams, $account)['data']['list'];
            $taskList = [];
            foreach ($taskListObject as $task) {
                if (isset($task['code']) && !empty($task['code']) && $task['code'] != '') {
                    $taskList[$task['code']] = $task;
                }
            }

            foreach ($data as $row) {
                if (isset($taskList[$row['code']])) {
                    $task = $taskList[$row['code']];
                    // Check is task type comparison
                    $isComparison = $task['type'] == 'insurance-comparison';

                    $conditionLevel1 = $isComparison
                        ?
                        (isset($row['mount']) && isset($row['acceptance']))
                        :
                        (isset($row['mount']) && isset($row['acceptance_ratio']) && isset($row['acceptance']) && isset($row['assignment']) && isset($row['assignment_ratio']));

                    $conditionLevel2 = $isComparison
                        ?
                        ((int)$row['mount'] >= 0 && (int)$row['acceptance'] > 0)
                        :
                        ((int)$row['mount'] >= 0 && (int)$row['acceptance'] > 0 && (int)$row['acceptance_ratio'] > 0 && (int)$row['assignment'] > 0 && (int)$row['assignment_ratio'] > 0);;

                    if ($conditionLevel1) {
                        if ($conditionLevel2) {

                            $mount = (int)$row['mount'];
                            $acceptance = (int)$row['acceptance'];
                            $acceptance_ratio = (int)$row['acceptance_ratio'];
                            $assignment = (int)$row['assignment'];
                            $assignment_ratio = (int)$row['assignment_ratio'];

                            $standard = (($acceptance * ($acceptance_ratio / 100)) - ($assignment)) * ($assignment_ratio / 100);

                            if (!$isComparison && $standard <= 0)
                                continue;

                            if ($isComparison) {
                                $score_value = ($mount > $acceptance ? 100 : 0);
                            } else {
                                // Choosing the smaller integer value between mount and value
                                $range = ($mount < $standard) ? $mount : $standard;
                                $score_value = ($range / $standard) * 100;
                            }

                            $progressData = [
                                'standard_id' => 1,
                                'task_id' => $task['id'],
                                'level' => $params['level'] ?? 'done',
                                'type' => 'insurance',
                                'comment' => $params['comment'] ?? '',
                                'answer_score' => $isComparison ? ($mount > $acceptance ? 100 : 0) : ($score_value < 90 ? 0 : ($score_value < 100 ? 50 : 100)),
                                'answer_value' => $isComparison ? ($mount > $acceptance ? 'رعایت می شود' : 'رعایت نمی شود') : ($score_value < 90 ? 'رعایت نمی شود' : ($score_value < 100 ? 'تا حدودی رعایت می شود' : 'رعایت می شود')),
                                'answer_note' => '',
                                'company_id' => 1,
                                'information' => json_encode([
                                    'code' => $row['code'],
                                    'acceptance' => $row['acceptance'],
                                    'acceptance_ratio' => $row['acceptance_ratio'],
                                    'assignment' => $row['assignment'],
                                    'assignment_ratio' => $row['assignment_ratio'],
                                    'mount' => $row['mount'],
                                    'standard' => $standard,
                                    'score' => round($score_value)
                                ]),
                            ];

                            $progressData['assigner_id'] = $account['id'];
                            $progressData['user_id'] = $account['id'];
                            $this->insuranceTaskProgress($progressData, $task, $account);
                        }
                    }
                }


            }
        }
        return $media;
    }


    public function insuranceTaskProgress(array $params, mixed $task, mixed $account): array
    {
        $progress = null;
        $task['mandatory_unit'] = json_encode($task['mandatory_unit']);

        $type = $params['type'];
        $level = $params['level'];


        $warranties = $this->getWarrantiesTree();
        $rules = $this->getRulesTree();
        $task = $this->canonizeTaskTreeList([
            'task' => $task,
            'domain_tree' => [],
            'members' => [],
            'rules' => $rules,
            'warranties' => $warranties,
            'answer_list' => [],
            'progress_list' => []
        ]);
        $slug = $this->generateComplianceProgressSlug($task, $params);

        $progress = $this->taskRepository->getProgress(['slug' => $slug]);
        $progress = $this->canonizeTaskProgress($progress);

        $history[] = [
            'time' => time(),
            'user_id' => $account['id'],
            'level' => $level,
            'status' => 'doing',
            'answer_score' => $params['answer_score'],
            'answer_value' => $params['answer_value'],
            'answer_note' => $params['answer_note'],
            'information' => $params['information'],
            'comment' => $params['comment'] ?? ''
        ];
        if (empty($progress)) {
            $paramsProgress = [
                'slug' => $slug,
                'standard_id' => $params['standard_id'],
                'section_id' => $task['section_id'],
                'task_id' => $task['id'],
                'assigner_id' => $params['assigner_id'],
                'type' => $type,
                'user_id' => $params['assigner_id'],
                'parent_id' => 0,
                'company_id' => $params['company_id'],
                'time_create' => time(),
                'time_update' => time(),
                'level' => $level,
                'information' => $params['information'],
                'answer_score' => $params['answer_score'],
                'answer_value' => $params['answer_value'],
                'answer_note' => $params['answer_note'],
                'history' => json_encode($history),
                'time_deadline' => strtotime(
                    sprintf('%s 00:00:00', $params['time_deadline'] ?? 0)
                ),
            ];
            $progress = $this->taskRepository->addProgress($paramsProgress);


        } else {

            $where = ['slug' => $progress['slug'],];

            $set = [];
            if (isset($params['level']) && !empty($params['level'])) {
                $set['level'] = $params['level'];
            }
            if (
                isset($params['answer_score'])
                && is_numeric($params['answer_score'])
                && isset($params['answer_value'])
                && !empty($params['answer_value'])
            ) {
                $set['answer_score'] = $params['answer_score'];
                $set['answer_value'] = $params['answer_value'];
            }
            if (isset($params['answer_note']) && !empty($params['answer_note'])) {
                $set['answer_note'] = $params['answer_note'];
            }
            if (isset($params['information']) && !empty($params['information'])) {
                $set['information'] = $params['information'];
            }

            if (!empty($set)) {
                $set['time_update'] = time();
                $history = $progress['history'];
                $history[] = [
                    'time' => time(),
                    'user_id' => $account['id'],
                    'level' => $level,
                    'status' => $params['answer_value'],
                    'answer_score' => $params['answer_score'],
                    'answer_value' => $params['answer_value'],
                    'answer_note' => $params['answer_note'],
                    'comment' => $params['comment'] ?? ''
                ];
                $set['history'] = json_encode($history);

                $this->taskRepository->updateProgress($where, $set);
            }

            $progress = $this->taskRepository->getProgress(['slug' => $slug]);

            if ($progress->getType() == 'parent') {
                $childProgressList = $this->taskRepository->getProgressList(['parent_id' => $progress->getId()]);
                foreach ($childProgressList as $childProgressObject) {
                    $level = in_array($progress->getLevel(), ['done', 'approve']) ? 'approve' : 'reject';
                    $status = $progress->getLevel();
                    $childProgress = $this->canonizeTaskProgress($childProgressObject);
                    $set['time_update'] = time();
                    $history = $childProgress['history'];
                    $history[] = [
                        'time' => time(),
                        'user_id' => $account['id'],
                        'level' => $level,
                        'status' => $status,
                        'answer_score' => $params['answer_score'],
                        'answer_value' => $params['answer_value'],
                        'answer_note' => $params['answer_note'],
                        'comment' => $params['comment'] ?? ''
                    ];
                    $set['history'] = json_encode($history);
                    $set['level'] = $level;
                    $set['status'] = $status;
                    $where = ['id' => $childProgress['id']];
                    $this->taskRepository->updateProgress($where, $set);

                }
            }

        }
        return $this->getTaskDetail(['id' => $params['task_id'], 'type' => 'insurance'], $account);

    }

    /**
     * @throws Exception
     */
    public function insuranceTaskExport(mixed $filter, mixed $account): array
    {
        $list = $this->getTaskTreeWhitFilter($filter, $account);

        $account['hash'] = hash('sha256', sprintf('%s-%s', $account['id'], $account['time_created']));
        $authorization = [
            'company' => [],
            'user' => $account,
            'access' => 'private',
            'user_id' => $account['id'],
            'company_id' => 0,
            'is_admin' => 0,
        ];


        $mediaList = $this->mediaService->getMediaList($authorization, ['relation_module' => 'erm', 'relation_section' => 'compliance-progress'])['data']['list'];
        $hasAttachment = !empty($mediaList);
        $fileList = [];
        if ($hasAttachment) {
            foreach ($mediaList as $item) {
                if (isset($item['relation'])) {
                    if (isset($item['relation'][0])) {
                        if (isset($item['relation'][0]['relation_item'])) {
                            $fileList[] = $item['relation'][0]['relation_item'];
                        }

                    }
                }

            }
        }

        $storageParams['filename'] = 'insurance-task';
        $storageParams['access'] = 'private';
        $storageParams['extension'] = 'xlsx';
        $storageParams['local_path'] = 'export';
        $media = $this->mediaService->createMedia($authorization, ['access' => 'private', 'title' => 'title'], $storageParams);
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();

        // Set header names for each column
        $headers = [
            "A" => "شناسه",
            "B" => "کد",
            "C" => "متن",
            "E" => "شماره بخشنامه",
            "F" => "قانون",
            "G" => "وضعیت",
            "H" => "شناسه رابط",
            "I" => "نام رابط",
            "J" => "وضعیت رعایت",
            "K" => "اظهار نظر ثبت شده",
            "L" => "فایل ضمیمه",
            "M" => "تاریخ ارجاع",
            "N" => "آخرین به روز رسانی",
        ];
        $key = [
            "id",
            "code",
            "title",
            "rule_code",
            "rule_text",
            "level",
            "enforcer_id",
            "enforcer_name",
            "progress_value",
            "progress_text",
            "has_attachment",
            "time_create",
            "time_update",
        ];
        $columnIndex = 1;
        foreach ($headers as $index => $column) {
            $activeWorksheet->setCellValue($index . $columnIndex, $column);
        }

        foreach ($list['data']['list'] as $record) {
            $columnIndex++;
            $record['rule_code'] = is_array($record['rule']) && isset($record['rule']['code']) ? $record['rule']['code'] : '';
            $record['rule_text'] = is_array($record['rule']) && isset($record['rule']['rule']) ? $record['rule']['rule'] : '';
            $record['time_create'] = is_array($record['progress']) && isset($record['progress']['time_create']) ? $this->utilityService->date($record['progress']['time_create']) : '';
            $record['time_update'] = is_array($record['progress']) && isset($record['progress']['time_update']) ? $this->utilityService->date($record['progress']['time_update']) : '';
            $record['enforcer_name'] = (is_array($record['progress']) && isset($record['progress']['user']) && is_array($record['progress']['user'])) ? $record['progress']['user']['name'] : '-';
            $record['enforcer_id'] = (is_array($record['progress']) && isset($record['progress']['user']) && is_array($record['progress']['user'])) ? $record['progress']['user']['identity'] : '-';
            $record['progress_value'] = (is_array($record['progress']) && isset($record['progress']['answer_value'])) ? $record['progress']['answer_value'] : '-';
            $record['progress_text'] = (is_array($record['progress']) && isset($record['progress']['answer_note'])) ? $record['progress']['answer_note'] : '-';
            $record['has_attachment'] = (is_array($record['progress']) && isset($record['progress']['id'])) ? (in_array($record['progress']['id'], $fileList) ? 'دارد' : 'ندارد') : 'ندراد';
            $record['level'] = $this->levelTranslate(is_array($record['progress']) && isset($record['progress']['level']) ? $record['progress']['level'] : '');
            $i = 0;
            foreach ($headers as $index => $column) {
                $activeWorksheet->setCellValue($index . $columnIndex, $record[$key[$i]]);
                $i++;
            }
        }

        $activeWorksheet->setRightToLeft(true);
        $writer = new Writer($spreadsheet);
        $writer->save($media['information']['history'][0]['storage']['file_path']);
        return $media;
    }

    public function maturityTaskExport(mixed $filter, mixed $account): array
    {
        $list = $this->getTaskTreeWhitFilter($filter, $account);

        $account['hash'] = hash('sha256', sprintf('%s-%s', $account['id'], $account['time_created']));
        $authorization = [
            'company' => [],
            'user' => $account,
            'access' => 'private',
            'user_id' => $account['id'],
            'company_id' => 0,
            'is_admin' => 0,
        ];
        $storageParams['filename'] = 'maturity-task';
        $storageParams['access'] = 'private';
        $storageParams['extension'] = 'xlsx';
        $storageParams['local_path'] = 'export';
        $media = $this->mediaService->createMedia($authorization, ['access' => 'private', 'title' => 'title'], $storageParams);
        $spreadsheet = new Spreadsheet();


        $activeWorksheet = $spreadsheet->getActiveSheet();

        // Set header names for each column
        $headers = [
            "A" => "شناسه",
            "B" => "کد",
            "C" => "متن",
        ];
        $key = [
            "id",
            "code",
            "title",
        ];
        $columnIndex = 1;
        foreach ($headers as $index => $column) {
            $activeWorksheet->setCellValue($index . $columnIndex, $column);
        }

        foreach ($list['data']['list'] as $record) {
            $columnIndex++;
            $i = 0;
            foreach ($headers as $index => $column) {
                $activeWorksheet->setCellValue($index . $columnIndex, $record[$key[$i]]);
                $i++;
            }
        }

        $activeWorksheet->setRightToLeft(true);
        $writer = new Writer($spreadsheet);
        $writer->save($media['information']['history'][0]['storage']['file_path']);
        return $media;
    }

    public function complianceTaskExport(mixed $filter, mixed $account): array
    {
        $list = $this->getTaskTreeWhitFilter($filter, $account);

        $account['hash'] = hash('sha256', sprintf('%s-%s', $account['id'], $account['time_created']));
        $authorization = [
            'company' => [],
            'user' => $account,
            'access' => 'private',
            'user_id' => $account['id'],
            'company_id' => 0,
            'is_admin' => 0,
        ];


        $mediaList = $this->mediaService->getMediaList($authorization, ['relation_module' => 'erm', 'relation_section' => 'compliance-progress'])['data']['list'];
        $hasAttachment = !empty($mediaList);
        $fileList = [];
        if ($hasAttachment) {
            foreach ($mediaList as $item) {
                if (isset($item['relation'])) {
                    if (isset($item['relation'][0])) {
                        if (isset($item['relation'][0]['relation_item'])) {
                            $fileList[] = $item['relation'][0]['relation_item'];
                        }

                    }
                }

            }
        }

        $storageParams['filename'] = 'compliance-task';
        $storageParams['access'] = 'private';
        $storageParams['extension'] = 'xlsx';
        $storageParams['local_path'] = 'export';
        $media = $this->mediaService->createMedia($authorization, ['access' => 'private', 'title' => 'title'], $storageParams);
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();

        // Set header names for each column
        $headers = [
            "A" => "شناسه",
            "B" => "کد",
            "C" => "متن",
            "E" => "شماره بخشنامه",
            "F" => "قانون",
            "G" => "وضعیت",
            "H" => "شناسه رابط",
            "I" => "نام رابط",
            "J" => "وضعیت رعایت",
            "K" => "اظهار نظر ثبت شده",
            "L" => "فایل ضمیمه",
            "M" => "تاریخ ارجاع",
            "N" => "آخرین به روز رسانی",
        ];
        $key = [
            "id",
            "code",
            "title",
            "rule_code",
            "rule_text",
            "level",
            "enforcer_id",
            "enforcer_name",
            "progress_value",
            "progress_text",
            "has_attachment",
            "time_create",
            "time_update",
        ];
        $columnIndex = 1;
        foreach ($headers as $index => $column) {
            $activeWorksheet->setCellValue($index . $columnIndex, $column);
        }

        foreach ($list['data']['list'] as $record) {
            $columnIndex++;
            $record['rule_code'] = is_array($record['rule']) && isset($record['rule']['code']) ? $record['rule']['code'] : '';
            $record['rule_text'] = is_array($record['rule']) && isset($record['rule']['rule']) ? $record['rule']['rule'] : '';
            $record['time_create'] = is_array($record['progress']) && isset($record['progress']['time_create']) ? $this->utilityService->date($record['progress']['time_create']) : '';
            $record['time_update'] = is_array($record['progress']) && isset($record['progress']['time_update']) ? $this->utilityService->date($record['progress']['time_update']) : '';
            $record['enforcer_name'] = (is_array($record['progress']) && isset($record['progress']['user']) && is_array($record['progress']['user'])) ? $record['progress']['user']['name'] : '-';
            $record['enforcer_id'] = (is_array($record['progress']) && isset($record['progress']['user']) && is_array($record['progress']['user'])) ? $record['progress']['user']['identity'] : '-';
            $record['progress_value'] = (is_array($record['progress']) && isset($record['progress']['answer_value'])) ? $record['progress']['answer_value'] : '-';
            $record['progress_text'] = (is_array($record['progress']) && isset($record['progress']['answer_note'])) ? $record['progress']['answer_note'] : '-';
            $record['has_attachment'] = (is_array($record['progress']) && isset($record['progress']['id'])) ? (in_array($record['progress']['id'], $fileList) ? 'دارد' : 'ندارد') : 'ندراد';
            $record['level'] = $this->levelTranslate(is_array($record['progress']) && isset($record['progress']['level']) ? $record['progress']['level'] : '');
            $i = 0;
            foreach ($headers as $index => $column) {
                $activeWorksheet->setCellValue($index . $columnIndex, $record[$key[$i]]);
                $i++;
            }
        }

        $activeWorksheet->setRightToLeft(true);
        $writer = new Writer($spreadsheet);
        $writer->save($media['information']['history'][0]['storage']['file_path']);
        return $media;
    }

    private function levelTranslate($level): string
    {
        $value = 'ارجاع داده نشده';
        switch ($level) {
            case 'todo':
                $value = 'در انتظار اجرا';
                break;
            case 'doing':
                $value = 'در حال انجام';
                break;
            case 'done':
                $value = 'انجام شده';
                break;
            case 'approve':
                $value = 'تایید شده';
                break;
            case 'reject':
                $value = 'رد شده';
                break;
        }
        return $value;
    }

    public function ruleExport(mixed $filter, mixed $account): array
    {
        $list = $this->getRulesTreeWhitFilter($filter, $account);
        $account['hash'] = hash('sha256', sprintf('%s-%s', $account['id'], $account['time_created']));
        $authorization = [
            'company' => [],
            'user' => $account,
            'access' => 'private',
            'user_id' => $account['id'],
            'company_id' => 0,
            'is_admin' => 0,
        ];
        $storageParams['filename'] = 'rule';
        $storageParams['access'] = 'private';
        $storageParams['extension'] = 'xlsx';
        $storageParams['local_path'] = 'export';
        $media = $this->mediaService->createMedia($authorization, ['access' => 'private', 'title' => 'title'], $storageParams);
        $spreadsheet = new Spreadsheet();


        $activeWorksheet = $spreadsheet->getActiveSheet();

        // Set header names for each column
        $headers = [
            "A" => "شناسه",
            "B" => "شماره بخشنامه",
            "C" => "قانون",
            "D" => "مرجع قانون گذار",
            "E" => "نوع",
            "F" => "دسته بندی",
            "G" => "تاریخ تصویب",
            "H" => "تاریخ ابلاغ",
            "I" => "تاریخ نسخ",
        ];
        $key = [
            "id",
            "code",
            "rule",
            "author",
            "type",
            "category",
            "approval_at",
            "promulgation_at",
            "cancellation_at",
        ];
        $columnIndex = 1;
        foreach ($headers as $index => $column) {
            $activeWorksheet->setCellValue($index . $columnIndex, $column);
        }

        foreach ($list['data']['list'] as $record) {
            $record['author'] = (is_array($record['author_information']) && isset($record['author_information']['title'])) ? $record['author_information']['title'] : '-';
            $record['type'] = (is_array($record['type_information']) && isset($record['type_information']['title'])) ? $record['type_information']['title'] : '-';
            $record['category'] = (is_array($record['category_information']) && isset($record['category_information']['title'])) ? $record['category_information']['title'] : '-';
            $record['approval_at'] = $record['approval_at'] ? explode(' ', $this->gregorianToJalali($record['approval_at']))[0] : '-';
            $record['cancellation_at'] = $record['cancellation_at'] ? explode(' ', $this->gregorianToJalali($record['cancellation_at']))[0] : '-';
            $record['promulgation_at'] = $record['promulgation_at'] ? explode(' ', $this->gregorianToJalali($record['promulgation_at']))[0] : '-';
            $columnIndex++;
            $i = 0;
            foreach ($headers as $index => $column) {
                $activeWorksheet->setCellValue($index . $columnIndex, $record[$key[$i]]);
                $i++;
            }
        }

        $activeWorksheet->setRightToLeft(true);
        $writer = new Writer($spreadsheet);
        $writer->save($media['information']['history'][0]['storage']['file_path']);
        return $media;
    }

    public function ruleImport(mixed $params, mixed $account): array
    {
        $ruleTypesList = [];
        $ruleTypes = $this->getRuleTypeList([], []);
        foreach ($ruleTypes as $ruleType) {
            $ruleTypesList[$ruleType['title']] = $ruleType;
        }

        $ruleAuthorsList = [];
        $ruleAuthors = $this->getRuleAuthorList([], []);
        foreach ($ruleAuthors as $ruleAuthor) {
            $ruleAuthorsList[$ruleAuthor['title']] = $ruleAuthor;
        }

        $ruleCategoriesList = [];
        $ruleCategories = $this->getRuleCategoryList([], []);
        foreach ($ruleCategories as $category) {
            $ruleCategoriesList[$category['title']] = $category;
        }

        $media = $this->mediaService->getMedia(['id' => $params['file_id']]);
        if (isset($media['extension'])) {
            switch ($media['extension']) {
                case 'csv':
                    $reader = new Csv();
                    $reader->setInputEncoding('UTF-8');
                    $reader->setDelimiter(',');
                    $reader->setEnclosure('"');
                    $reader->setSheetIndex(0);
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load(realpath(__DIR__ . $this->config['directory']['download_path'] . $media['information']['storage']['file_path']));
                    $dataSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                    break;

                case 'xlsx':
                    $reader = new Xlsx();
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load(realpath(__DIR__ . $this->config['directory']['download_path'] . $media['information']['storage']['file_path']));
                    $dataSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                    break;

                case 'xls':
                    $reader = new Xls();
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load(realpath(__DIR__ . $this->config['directory']['download_path'] . $media['information']['storage']['file_path']));
                    $dataSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                    break;

                default:
                    $data = [];
                    break;
            }


            // Set array keys by first row title
            $i = 1;
            $data = [];
            $keys = array_shift($dataSheet);
            $keys = ['code', 'rule', 'author', 'type', 'category', 'approval_at', 'promulgation_at', 'cancellation_at', 'is_creditable'];
            foreach ($dataSheet as $dataSingle) {
                $array = [];
                $dataSingle = array_values($dataSingle);
                for ($j = 0; $j < sizeof($keys); $j++) {
                    $array[$j] = $dataSingle[$j];
                }
                $dataSingle = $array;
                $dataSingle = array_combine($keys, $dataSingle);
                foreach ($dataSingle as $key => $value) {
                    if (!empty($key) && !empty($value)) {
                        $data[$i][$key] = $value;
                    }
                }
                $i++;
            }
            $data = array_values($data);
            foreach ($data as $row) {
                $row['target'] = $this->config['rule']['target'];
                $row['status'] = 1;
                ///TODO: fix this bug , the target must only send as type of rule not send as file owner type
                //if (isset($params['target'])) {
                //    $row['target'] = $params['target'];
                //}

                $row['type'] = $ruleTypesList[$row['type']]['slug'];
                $row['author'] = $ruleAuthorsList[$row['author']]['slug'];
                $row['category'] = $ruleCategoriesList[$row['category']]['slug'];
                $row['approval_at'] = $row['approval_at'] ? $this->jalaliToGregorian($row['approval_at']) : '';
                $row['promulgation_at'] = $row['promulgation_at'] ? $this->jalaliToGregorian($row['promulgation_at']) : '';
                $row['cancellation_at'] = $row['cancellation_at'] ? $this->jalaliToGregorian($row['cancellation_at']) : '';
                $row['time_create'] = time();

                $this->storeRule($row);
            }
        }
        return $media;
    }

    ///TODO:move this to utility off user module and merge with gregorian to jalali method
    private function jalaliToGregorian($JalaliDate): bool|string
    {
        // Split the Jalali date into year, month, and day
        list($hYear, $hMonth, $hDay) = explode('-', $JalaliDate);
        // Create an IntlDateFormatter for the Jalali calendar
        $JalaliFormatter = new IntlDateFormatter(
            'fa_IR@calendar=persian',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'UTC',
            IntlDateFormatter::TRADITIONAL,
            'yyyy-MM-dd'
        );

        // Parse the Jalali date into a timestamp
        $timestamp = $JalaliFormatter->parse("$hYear-$hMonth-$hDay");

        if ($timestamp === false) {
            return "Failed to parse the Jalali date.";
        } else {
            // Create a new IntlDateFormatter for the Gregorian calendar
            $gregorianFormatter = new IntlDateFormatter(
                'en_US',
                IntlDateFormatter::FULL,
                IntlDateFormatter::FULL,
                'UTC',
                IntlDateFormatter::GREGORIAN,
                'yyyy-MM-dd'
            );

            // Format the timestamp into a Gregorian date
            return $gregorianFormatter->format($timestamp);
        }
    }

    ///TODO:move this to utility off user module and merge with jalali to gregorian method
    private function gregorianToJalali($JalaliDate): bool|string
    {
        // Split the Gregorian date into year, month, and day
        list($hYear, $hMonth, $hDay) = explode('-', $JalaliDate);
        // Create an IntlDateFormatter for the Gregorian calendar
        $gregorianFormatter = new IntlDateFormatter(
            'en_US',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'UTC',
            IntlDateFormatter::TRADITIONAL,
            'yyyy-MM-dd'
        );

        // Parse the Gregorian date into a timestamp
        $timestamp = $gregorianFormatter->parse("$hYear-$hMonth-$hDay");

        return $this->utilityService->date($timestamp);
        if ($timestamp === false) {
            return "Failed to parse the Gregorian date.";
        } else {
            return $this->utilityService->date($timestamp);
        }
    }


    public function taskImport(mixed $params, mixed $account): array
    {

        $sectionArrayList = [];
        $sectionList = $this->getDomainTree([], []);
        foreach ($sectionList as $sectionParent) {
            foreach ($sectionParent['children'] as $section) {
                $sectionArrayList[$section['title']] = $section;
            }
            $sectionParent['children'] = [];
            $sectionArrayList[$sectionParent['title']] = $sectionParent;
        }

        $ruleArrayList = [];
        $ruleList = $this->getRulesTreeWhitFilter(['target' => $this->config['type']], $account)['data']['list'];
        foreach ($ruleList as $rule) {
            $ruleArrayList[$rule['rule']] = $rule;
        }

        $warrantyArrayList = [];
        $warrantyList = $this->getWarrantiesTree();
        foreach ($warrantyList as $warranty) {
            $warrantyArrayList[$warranty['title']] = $warranty;
        }

        $mandatoryArrayList = [];
        $mandatoryList = $this->getMandatoryUnitList();
        foreach ($mandatoryList as $mandatory) {
            $mandatoryArrayList[$mandatory['title']] = $mandatory;
        }

        $media = $this->mediaService->getMedia(['id' => $params['file_id']]);
        if (isset($media['extension'])) {
            switch ($media['extension']) {
                case 'csv':
                    $reader = new Csv();
                    $reader->setInputEncoding('UTF-8');
                    $reader->setDelimiter(',');
                    $reader->setEnclosure('"');
                    $reader->setSheetIndex(0);
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load(realpath(__DIR__ . $this->config['directory']['download_path'] . $media['information']['storage']['file_path']));
                    $dataSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                    break;

                case 'xlsx':
                    $reader = new Xlsx();
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load(realpath(__DIR__ . $this->config['directory']['download_path'] . $media['information']['storage']['file_path']));
                    $dataSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                    break;

                case 'xls':
                    $reader = new Xls();
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load(realpath(__DIR__ . $this->config['directory']['download_path'] . $media['information']['storage']['file_path']));
                    $dataSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                    break;

                default:
                    $data = [];
                    break;
            }


            // Set array keys by first row title
            $i = 1;
            $data = [];
            ///TODO: dynamic keys of data
            $keys = array_shift($dataSheet);
            $keys = ['code', 'title', 'section_id', 'rule_id', 'warranty_id', 'mandatory_unit'];
            foreach ($dataSheet as $dataSingle) {
                $array = [];
                $dataSingle = array_values($dataSingle);
                for ($j = 0; $j < sizeof($keys); $j++) {
                    $array[$j] = $dataSingle[$j];
                }
                $dataSingle = $array;
                $dataSingle = array_combine($keys, $dataSingle);
                foreach ($dataSingle as $key => $value) {
                    if (!empty($key) && !empty($value)) {
                        $data[$i][$key] = $value;
                    }
                }
                $i++;
            }
            $data = array_values($data);
            foreach ($data as $row) {
                //check for type in excel file
                if (!isset($row['type'])) {
                    $row['type'] = $this->config['task']['type'];;
                }
                //check for type in parameters set
                if (isset($params['type'])) {
                    $row['type'] = $params['type'];
                }
                $row['status'] = 1;
                $row['standard_id'] = 1;
                $row['section_id'] = $sectionArrayList[$row['section_id']]['id'];
                $row['rule_id'] = $ruleArrayList[$row['rule_id']]['id'];
                $row['warranty_id'] = $warrantyArrayList[$row['warranty_id']]['id'];

                $mandatoryUnitList = explode(',', $row['mandatory_unit']);
                $list = [];
                foreach ($mandatoryUnitList as $object) {
                    if (isset($mandatoryArrayList[trim($object)])) {
                        $list[] = $mandatoryArrayList[trim($object)];
                    }
                }

                $row['mandatory_unit'] = json_encode($list);
                $row['time_create'] = time();
                $this->storeTask($row);
            }
        }
        return $media;
    }

    public function addErmMeta(mixed $params, mixed $account): array
    {
        $slug = $this->generateUniqueSlug($params['title'] ?? '');
        $value = $slug;

        if (isset($params['slug'])) {
            $slug = $params['slug'];
        }
        if (isset($params['value'])) {
            $value = $params['value'];
        }

        $params['time_create'] = time();
        $params['slug'] = $slug;
        $params['value'] = $value;

        ///TODO: find solution for set id and in update meta
        //$params['information'] = json_encode($params);

        $params['type'] = json_encode($params['type']);
        $params['target'] = json_encode($params['target']);

        $ermMetaRow = $this->taskRepository->addErmMeta($params);
        return $this->canonizeErmMeta($ermMetaRow);
    }

    public function getErmMetaList(mixed $params, mixed $account): array
    {
        $limit = $params['limit'] ?? 100;
        $page = $params['page'] ?? 1;
        $key = $params['key'] ?? '';
        $order = $params['order'] ?? ['time_create DESC', 'id DESC'];
        $offset = ((int)$page - 1) * (int)$limit;

        $listParams = [
            'page' => (int)$page,
            'limit' => (int)$limit,
            'order' => $order,
            'offset' => $offset,
            'key' => $key,
            'type' => $params['type'],
            'target' => $params['target'],
        ];

        if (isset($params['title']) && !empty($params['title'])) {
            $listParams['title'] = $params['title'];
        }
        if (isset($params['status'])) {
            $listParams['status'] = $params['status'];
        }

        $ermMetaListRow = $this->taskRepository->getErmMetaList($listParams);
        $ermMetaList = [];
        foreach ($ermMetaListRow as $ermMetaRow) {
            $ermMetaList[] = $this->canonizeErmMeta($ermMetaRow);
        }

        $count = $this->taskRepository->getErmMetaCount($params);

        return [
            'result' => true,
            'data' => [
                'list' => $ermMetaList,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page' => $page,
                ],
                'filters' => null,
            ],
            'error' => [],
        ];
    }

    public function canonizeErmMeta($meta): array
    {

        if (empty($meta)) {
            return [];
        }

        if (is_object($meta)) {
            $meta = [
                'id' => $meta->getId(),
                'slug' => $meta->getSlug(),
                'value' => $meta->getValue(),
                'title' => $meta->getTitle()
            ];
        } else {
            $meta = [
                'id' => $meta['id'],
                'slug' => $meta['slug'],
                'value' => $meta['value'],
                'title' => $meta['title'],
            ];
        }
        return $meta;
    }

    private function generateUniqueSlug($persianTitle): string
    {
        $slug = uniqid();
        // Check if intl extension is enabled
        if (extension_loaded('intl')) {
            // Transliterate Persian title to English
            $transliterator = Transliterator::create('Any-Latin; Latin-ASCII');
            $englishTitle = $transliterator->transliterate($persianTitle);
            // Convert to lowercase and remove non-alphanumeric characters
            $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($englishTitle));
            // Append a unique identifier (timestamp)
            $slug .= '-' . time(); // You can use a random string generator instead of time()
        }
        return $slug;
    }

    public function editErmMeta(mixed $params, mixed $account): array
    {
        if (isset($params['type'])) {
            $params['type'] = json_encode($params['type']);
        }
        if (isset($params['target'])) {
            $params['target'] = json_encode($params['target']);
        }
        $ermMetaRow = $this->taskRepository->editErmMeta($params);
        return $this->canonizeErmMeta($ermMetaRow);
    }

    public function deleteErmMeta(mixed $params, mixed $account)
    {
        $params = [
            "id" => $params["id"],
            "status" => 0,
            "time_delete" => time(),
        ];
        return $this->taskRepository->editErmMeta($params);
    }

    public function getAuditList(mixed $params, mixed $account): array
    {
        $limit = $params['limit'] ?? 1250;
        $page = $params['page'] ?? 1;
        $order = $params['order'] ?? ['time_create DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;


        // Set params
        $listParams = [
            'order' => $order,
            'offset' => $offset,
            'limit' => $limit,
            'status' => 1,
        ];

        $complianceFilterParams = [];
        $hasComplianceFilter = true;

        if (
            isset($params['compliance_enforcer'])
            && $params['compliance_enforcer'] != ''
            && $params['compliance_enforcer'] != null
            && !empty($params['compliance_enforcer'])
        ) {
            $hasComplianceFilter = true;
            $complianceFilterParams['enforcer'] = $params['compliance_enforcer'];
        } else {
            $hasComplianceFilter = false;
            $complianceFilterParams['enforcer'] = null;
        }

        $complianceFilter = $this->prepareProgressFilter($complianceFilterParams);


        if (!empty($complianceFilter)) {
            $isFresh = true;
            foreach ($complianceFilter as $filter) {

                $itemIdList = [];
                $rowSet = $this->taskRepository->getTaskIdFromComplianceProgress($filter);
                foreach ($rowSet as $row) {
                    $itemIdList[] = $this->canonizeTaskId($row);
                }
                if ($isFresh) {
                    $complianceFilter['id'] = $itemIdList;
                    $isFresh = false;
                } else {
                    $complianceFilter['id'] = array_intersect($complianceFilter['id'], $itemIdList);

                }
            }

        }
        /// end check compliance progress level

        $filters = $this->prepareProgressFilter($params);

        if (!empty($filters)) {
            $isFresh = true;
            foreach ($filters as $filter) {
                $hasRiskWaitingFilter = false;
                $notWaitingId = [];
                $waitingId = [];
                if ($filter['type'] == 'value' && $filter['field'] != 'risk_response_type') {
                    $index = array_search('waiting', $filter['value']);
                    if ($index !== false) {
                        $hasRiskWaitingFilter = true;
                        unset($filter['value'][$index]);
                    }
                }
                if ($hasRiskWaitingFilter) {
                    $riskProgressList = $this->taskRepository->getRiskList();
                    foreach ($riskProgressList as $riskProgress) {
                        $notWaitingId[] = $riskProgress->getTaskId();
                    }
                    $allTask = $this->taskRepository->getTaskList([
                        'order' => 'id ASC',
                        'offset' => 0,
                        'limit' => 1250,
                        'status' => 1,
                    ]);
                    foreach ($allTask as $task) {
                        if (!in_array($task->getId(), $notWaitingId)) {
                            $waitingId[] = $task->getId();
                        }
                    }
                }
                $itemIdList = [];
                $rowSet = $this->taskRepository->getTaskIdFromRiskProgress($filter);
                foreach ($rowSet as $row) {
                    $itemIdList[] = $this->canonizeTaskId($row);
                }
                if ($filter['type'] == 'value' && $hasRiskWaitingFilter) {
                    $itemIdList = array_unique(array_merge($itemIdList, $waitingId));
                }
                if ($isFresh) {
                    $listParams['id'] = $itemIdList;
                    $isFresh = false;
                } else {
                    $listParams['id'] = array_intersect($listParams['id'], $itemIdList);
                }
            }
        }

        if ($hasComplianceFilter && !empty($filters)) {
            $listParams['id'] = array_intersect($listParams['id'], $complianceFilter['id']);
        } else if ($hasComplianceFilter) {
            $listParams['id'] = $complianceFilter['id'];
        }

        if (isset($params['data_from']) && $params['data_from'] != null) {
            $listParams['data_from'] = strtotime(
                sprintf('%s 00:00:00', $params['data_from'])
            );
        }

        if (isset($params['data_to']) && ($params['data_to']) != null) {
            $listParams['data_to'] = strtotime(
                sprintf('%s 00:00:00', $params['data_to'])
            );
        }
        if (!empty($params['section_id']))
            $listParams['section_id'] = explode(',', $params['section_id']);
        if (!empty($params['warranty_id']))
            $listParams['warranty_id'] = explode(',', $params['warranty_id']);
        if (!empty($params['rule_id']))
            $listParams['rule_id'] = explode(',', $params['rule_id']);
        if (!empty($params['user_id']))
            $listParams['user_id'] = explode(',', $params['user_id']);
        if (isset($params['title']))
            $listParams['title'] = $params['title'];
        if (isset($params['code']))
            $listParams['code'] = $params['code'];
        if (isset($params['id']))
            $listParams['id'] = $params['id'];

        $taskList = array();
        $domainTree = $this->getDomainTree([], []);
        $members = $this->listMember([]);

        $progressParentList = [];
        $progressObjectList = $this->taskRepository->getProgressList(
            [
                'parent_id' => 0
            ]
        );
        foreach ($progressObjectList as $progressObject) {
            $progressParentList[] = $this->canonizeTaskProgress($progressObject);
        }
        $progressChildList = [];
        $progressObjectList = $this->taskRepository->getProgressList(
            [
                'type' => 'child',
                'user_id' => $account['id']
            ]
        );
        foreach ($progressObjectList as $progressObject) {
            $progressChildList[] = $this->canonizeTaskProgress($progressObject);
        }

        $progressList = array_merge($progressParentList, $progressChildList);

        $warranties = $this->getWarrantiesTree();
        $rules = $this->getRulesTree();
        $answerList = $this->getAnswersList(['type' => $listParams['type']], [])['data']['list'];

        $listParams['type'] = 'audit';
        $riskTaskList = $this->taskRepository->getTaskList($listParams);
        foreach ($riskTaskList as $task) {
            $taskList[] = $this->canonizeTaskTreeList([
                'task' => $task,
                'domain_tree' => $domainTree,
                'members' => $members,
                'rules' => $rules,
                'warranties' => $warranties,
                'answer_list' => $answerList,
                'progress_list' => $progressList
            ]);
        }


        $riskProgressRow = $this->taskRepository->getRiskList([]);
        $riskProgressList = [];
        foreach ($riskProgressRow as $item) {
            $riskProgressList[] = $this->canonizeTaskRisk($item, $members);
        }


        $progressParentList = [];
        $progressObjectList = $this->taskRepository->getRiskProgressList(
            [
                'parent_id' => 0
            ]
        );
        foreach ($progressObjectList as $progressObject) {
            $progressParentList[] = $this->canonizeTaskRisk($progressObject, $members);
        }
        $progressChildList = [];
        $progressObjectList = $this->taskRepository->getRiskProgressList(
            [
                'type' => 'child',
                'user_id' => $account['id']
            ]
        );
        foreach ($progressObjectList as $progressObject) {
            $progressChildList[] = $this->canonizeTaskRisk($progressObject, $members);
        }

        $riskProgressList = array_merge($progressParentList, $progressChildList);

        $auditProgressList = [];
        $auditProgressObjectList = $this->taskRepository->getAuditProgressList(['type' => 'single', 'parent_id' => 0, 'task_id' => array_column($taskList, 'id')]);
        foreach ($auditProgressObjectList as $object) {
            $auditProgressList[$object->getTaskId()] = $this->canonizeAuditProgress($object);
        }

        for ($i = 0; $i < sizeof($taskList); $i++) {
            $taskList[$i]['risk'] = new stdClass();
            /// TODO: handle it if a user is admin and has a child task
            $taskList[$i]['risk'] = $this->findElements(
                $riskProgressList,
                [
                    [
                        'field' => 'task_id',
                        'value' => $taskList[$i]['id']
                    ],
                    [
                        'field' => 'parent_id',
                        'value' => 0
                    ]
                ]
            )[0] ?? (new stdClass());

            $taskList[$i]['risk_child'] = $this->findElements(
                $riskProgressList,
                [
                    [
                        'field' => 'task_id',
                        'value' => $taskList[$i]['id']
                    ],
                    [
                        'field' => 'type',
                        'value' => 'child'
                    ]
                ]
            )[0] ?? (new stdClass());

            $taskList[$i]['audit'] = $auditProgressList[$taskList[$i]['id']];
        }


        // Get count
        $count = $this->taskRepository->getTaskCount($listParams);

        return [
            'result' => true,
            'data' => [
                'list' => $taskList,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page' => $page,
                ],
                'filters' => null,
            ],
            'error' => [],
        ];
    }

    public function getAuditTaskProgress(array $params): array
    {
        return $this->canonizeAuditProgress($this->taskRepository->getAuditProgress($params));
    }

    public function AuditProgressTask(array $params, mixed $account): array
    {

        if ($params['level'] == 'todo') {
            $params['level'] = 'doing';
        }

        $domainTree = $this->getDomainTree([], []);
        $members = $this->listMember([]);
        $result = $this->taskRepository->getTask(['id' => $params['task_id']]);
        $warranties = $this->getWarrantiesTree();
        $rules = $this->getRulesTree();
        $task = $this->canonizeTaskTreeList([
            'task' => $result,
            'domain_tree' => $domainTree,
            'members' => $members,
            'rules' => $rules,
            'warranties' => $warranties,
            'answer_list' => [],
            'progress_list' => []
        ]);

        $slug = $this->generateAuditProgressSlug($task, $params);

        $progress = $this->taskRepository->getAuditProgress(['slug' => $slug]);
        $progress = $this->canonizeAuditProgress($progress);
        $type = $params['type'] == 'parent' ? sizeof(explode(',', $params['user_id'])) == 1 ? 'single' : $params['type'] : $params['type'];
        $level = $type == 'parent' ? 'doing' : $params['level'];
        $history[] = [
            'time' => time(),
            'user_id' => $account['id'],
            'level' => $level,
            'status' => 'doing',
            'answer_score' => $params['answer_score'],
            'answer_value' => $params['answer_value'],
            'answer_note' => $params['answer_note'],
            'comment' => $params['comment'] ?? ''
        ];
        if (empty($progress)) {
            $paramsProgress = [
                'slug' => $slug,
                'standard_id' => $params['standard_id'],
                'section_id' => $task['section_id'],
                'task_id' => $task['id'],
                'assigner_id' => $params['assigner_id'],
                'type' => $type,
                'user_id' => $type == 'parent' ? $params['assigner_id'] : $params['user_id'],
                'parent_id' => $type != 'child' ? 0 : $params['parent_id'] ?? 0,
                'company_id' => $params['company_id'],
                'time_create' => time(),
                'time_update' => time(),
                'level' => $level,
                'history' => json_encode($history),
                'time_deadline' => strtotime(
                    sprintf('%s 00:00:00', $params['time_deadline'])
                ),
            ];
            $progress = $this->taskRepository->addAuditProgress($paramsProgress);

            if ($type == 'parent') {
                $users = explode(',', $params['user_id']);
                foreach ($users as $user) {
                    $childParams = $params;
                    $childParams['type'] = 'child';
                    $childParams['user_id'] = $user;
                    $childParams['parent_id'] = $progress->getId();
                    if ($params['assigner_id'] != $user) {
                        $this->complianceProgressTask($childParams, $account);
                    }
                }
            }

        } else {

            $where = ['slug' => $progress['slug'],];
            $set = [];
            if (isset($params['level']) && !empty($params['level'])) {
                $set['level'] = $params['level'];
            }
            if (
                isset($params['answer_score'])
                && is_numeric($params['answer_score'])
                && isset($params['answer_value'])
                && !empty($params['answer_value'])
            ) {
                $set['answer_score'] = $params['answer_score'];
                $set['answer_value'] = $params['answer_value'];
            }
            if (isset($params['answer_note']) && !empty($params['answer_note'])) {
                $set['answer_note'] = $params['answer_note'];
            }

            if (!empty($set)) {
                $set['time_update'] = time();
                $history = $progress['history'];
                $history[] = [
                    'time' => time(),
                    'user_id' => $account['id'],
                    'level' => $level,
                    'status' => $params['answer_value'],
                    'answer_score' => $params['answer_score'],
                    'answer_value' => $params['answer_value'],
                    'answer_note' => $params['answer_note'],
                    'comment' => $params['comment'] ?? ''
                ];
                $set['history'] = json_encode($history);
                $this->taskRepository->updateAuditProgress($where, $set);
            }

            $progress = $this->taskRepository->getAuditProgress(['slug' => $slug]);

            if ($progress->getType() == 'parent') {
                $childProgressList = $this->taskRepository->getAuditProgressList(['parent_id' => $progress->getId()]);
                foreach ($childProgressList as $childProgressObject) {
                    $level = in_array($progress->getLevel(), ['done', 'approve']) ? 'approve' : 'reject';
                    $status = $progress->getLevel();
                    $childProgress = $this->canonizeAuditProgress($childProgressObject);
                    $set['time_update'] = time();
                    $history = $childProgress['history'];
                    $history[] = [
                        'time' => time(),
                        'user_id' => $account['id'],
                        'level' => $level,
                        'status' => $status,
                        'answer_score' => $params['answer_score'],
                        'answer_value' => $params['answer_value'],
                        'answer_note' => $params['answer_note'],
                        'comment' => $params['comment'] ?? ''
                    ];
                    $set['history'] = json_encode($history);
                    $set['level'] = $level;
                    $set['status'] = $status;
                    $where = ['id' => $childProgress['id']];
                    $this->taskRepository->updateAuditProgress($where, $set);

                }
            }
        }
        $task['audit'] = $this->canonizeAuditProgress($progress);
        return $task;
    }

    private function canonizeAuditProgress(object|array $progress): array
    {
        if (empty($progress)) {
            return [];
        }

        if (is_object($progress)) {
            $progress = [
                'id' => $progress->getId(),
                'slug' => $progress->getSlug(),
                'standard_id' => $progress->getStandardId(),
                'section_id' => $progress->getSectionId(),
                'task_id' => $progress->getTaskId(),
                'user_id' => $progress->getUserId(),
                'assigner_id' => $progress->getAssignerId(),
                'company_id' => $progress->getCompanyId(),
                'time_create' => $progress->getTimeCreate(),
                'time_update' => $progress->getTimeUpdate(),
                'level' => $progress->getLevel(),
                'status' => $progress->getStatus(),
                'answer_score' => $progress->getAnswerScore(),
                'answer_value' => $progress->getAnswerValue(),
                'answer_note' => $progress->getAnswerNote(),
                'type' => $progress->getType(),
                'parent_id' => $progress->getParentId(),
                'time_deadline' => $progress->getTimeDeadline(),
                'history' => $progress->getHistory(),
                'information' => $progress->getInformation(),
            ];
        } else {
            $progress = [
                'id' => $progress['id'],
                'slug' => $progress['slug'],
                'standard_id' => $progress['standard_id'],
                'section_id' => $progress['section_id'],
                'task_id' => $progress['task_id'],
                'user_id' => $progress['user_id'],
                'assigner_id' => $progress['assigner_id'],
                'company_id' => $progress['company_id'],
                'time_create' => $progress['time_create'],
                'time_update' => $progress['time_create'],
                'level' => $progress['level'],
                'status' => $progress['status'],
                'answer_score' => $progress['answer_score'],
                'answer_value' => $progress['answer_value'],
                'answer_note' => $progress['answer_note'],
                'type' => $progress['type'],
                'parent_id' => $progress['parent_id'],
                'time_deadline' => $progress['time_deadline'],
                'history' => $progress['history'],
                'information' => $progress['information'],
            ];
        }
        $progress['information'] = json_decode($progress['information'], true);
        $progress['history'] = json_decode($progress['history'], true);
        if (!empty($progress['time_deadline']) && is_numeric($progress['time_deadline'])) {
            $progress['time_deadline_view'] = $this->utilityService->date($progress['time_deadline']);
        } else {
            $progress['time_deadline_view'] = '-';
        }
        $time = time();
        $progress["current_time"] = $time;
        $progress["current_time_view"] = $this->utilityService->date($time, ['local' => 'en_US', 'pattern' => 'yyyy/MM/dd']);

        $user = $this->accountService->getAccount(['id' => $progress['user_id']]);
        $progress['user'] = (sizeof($user) > 0) ? $user : new stdClass();

        $progress['next_actions'] = $this->roadMap[$progress['level']];

        return $progress;
    }

    private function generateAuditProgressSlug(array $task, array $params): string
    {
        $userId = $params['type'] == 'parent' ? $params['assigner_id'] : $params['user_id'];
        return md5(
            sprintf(
                '%d-%d-%d-%d-%d-%d',
                (int)$task['standard_id'],
                (int)$task['section_id'],
                (int)$task['id'],
                (int)$params['company_id'],
                (int)($userId),
                (int)$params['assigner_id']
            )
        );
    }

}
