<?php

namespace Erm\Service;

use Erm\Repository\ComplianceTaskRepositoryInterface;
use Pi\Core\Service\UtilityService;
use Pi\User\Service\AccountService;
use stdClass;

/**
 * Compliance task list/tree logic uses {@see ComplianceTaskRepositoryInterface} (same DB tables as task).
 * Shared read helpers (domain, rules, answers, members) delegate to {@see TaskService}.
 */
class ComplianceTaskService
{
    private array $complianceRoadMap = [
        'reject' => ['doing', 'done', 'approve'],
        'todo' => ['doing', 'done', 'approve'],
        'doing' => ['done', 'approve'],
        'done' => ['approve'],
        'approve' => [],
    ];

    public function __construct(
        private ComplianceTaskRepositoryInterface $complianceTaskRepository,
        private TaskService $taskService,
        private AccountService $accountService,
        private UtilityService $utilityService,
    ) {
    }

    public function getComplianceTreeWithFilter(mixed $params, mixed $account): array
    {
        $limit = $params['limit'] ?? 2000;
        $page = $params['page'] ?? 1;
        $order = $params['order'] ?? ['time_create DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;

        $listParams = [
            'order' => $order,
            'offset' => $offset,
            'limit' => $limit,
            'status' => 1,
            'reference_id' => $params['reference_id'],
        ];
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

        $filters = $this->prepareComplianceProgressFilter($params);

        if ((isset($params['enforce_data_from']) && $params['enforce_data_from'] != null) || (isset($params['enforce_data_to']) && ($params['enforce_data_to']) != null)) {
            if (empty($filters)) {
                $filters['level'] = ['todo', 'done', 'approve', 'doing', 'reject'];
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
                    $taskProgressList = $this->complianceTaskRepository->getComplianceProgressList();
                    foreach ($taskProgressList as $taskProgress) {
                        $notWaitingId[] = $taskProgress->getTaskId();
                    }
                    $allTask = $this->complianceTaskRepository->getComplianceTaskList([
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

                $rowSet = $this->complianceTaskRepository->getComplianceTaskIdsFromProgress($filter);
                foreach ($rowSet as $row) {
                    $itemIdList[] = $this->canonizeComplianceTaskId($row);
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

        if (!empty($params['section_id'])) {
            $listParams['section_id'] = explode(',', $params['section_id']);
        }
        if (!empty($params['warranty_id'])) {
            $listParams['warranty_id'] = explode(',', $params['warranty_id']);
        }
        if (!empty($params['rule_id'])) {
            $listParams['rule_id'] = explode(',', $params['rule_id']);
        }
        if (!empty($params['user_id'])) {
            $listParams['user_id'] = explode(',', $params['user_id']);
        }
        if (isset($params['title'])) {
            $listParams['title'] = $params['title'];
        }
        if (isset($params['code'])) {
            $listParams['code'] = $params['code'];
        }
        if (isset($params['mandatory_unit'])) {
            $listParams['mandatory_unit'] = $params['mandatory_unit'];
        }

        $taskList = [];
        $domainTree = $this->taskService->getDomainTree([], []);
        $members = $this->taskService->listMember([]);
        $progressParentList = [];

        $condition = $params['type'] == 'maturity' ?
            [
            ] :
            [
                'parent_id' => 0,
            ];
        $progressObjectList = $this->complianceTaskRepository->getComplianceProgressList(
            $condition
        );
        foreach ($progressObjectList as $progressObject) {
            $progressParentList[] = $this->canonizeComplianceProgress($progressObject);
        }
        $progressChildList = [];
        $progressObjectList = $this->complianceTaskRepository->getComplianceProgressList(
            [
                'type' => 'child',
                'user_id' => $account['id'],
            ]
        );
        foreach ($progressObjectList as $progressObject) {
            $progressChildList[] = $this->canonizeComplianceProgress($progressObject);
        }

        $progressList = array_merge($progressParentList, $progressChildList);
        $warranties = $this->taskService->getWarrantiesTree();
        $rules = $this->taskService->getRulesTree();
        $answerList = $this->taskService->getAnswersList(['type' => $listParams['type']], [])['data']['list'];

        $listData = $this->complianceTaskRepository->getComplianceTaskList($listParams);
        foreach ($listData as $task) {
            $taskList[] = $this->canonizeComplianceTreeList([
                'task' => $task,
                'domain_tree' => $domainTree,
                'members' => $members,
                'rules' => $rules,
                'warranties' => $warranties,
                'answer_list' => $answerList,
                'progress_list' => $progressList,
            ]);
        }

        $count = $this->complianceTaskRepository->getComplianceTaskCount($listParams);

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

    public function prepareComplianceProgressFilter($params): array
    {
        $filters = [];
        foreach ($params as $key => $value) {
            if (in_array($key, ['enforcer', 'level', 'max_risk', 'min_risk', 'risk_response_type'])) {
                switch ($key) {
                    case 'max_risk':
                        if (($value != '') && !empty($value) && ($value != null)) {
                            $filters[$key] = [
                                'field' => 'risk_data',
                                'value' => $value,
                                'type' => 'rangeMax',
                            ];
                        }
                        break;

                    case 'min_risk':
                        if (($value != '') && !empty($value) && ($value != null)) {
                            $filters[$key] = [
                                'field' => 'risk_data',
                                'value' => $value,
                                'type' => 'rangeMin',
                            ];
                        }
                        break;

                    case 'risk_response_type':
                        if (($value != '') && !empty($value) && ($value != null) && sizeof(explode(',', $value)) > 0) {
                            $filters[$key] = [
                                'field' => 'risk_response_type',
                                'value' => explode(',', $value),
                                'type' => 'value',
                            ];
                        }
                        break;
                    case 'enforcer':
                        if (($value != '') && !empty($value) && ($value != null) && sizeof(explode(',', $value)) > 0) {
                            $filters[$key] = [
                                'field' => 'user_id',
                                'value' => explode(',', $value),
                                'type' => 'value',
                            ];
                        }
                        break;
                    case 'level':
                        if (($value != '') && !empty($value) && ($value != null) && sizeof(explode(',', $value)) > 0) {
                            $filters[$key] = [
                                'field' => 'level',
                                'value' => explode(',', $value),
                                'type' => 'value',
                            ];
                        }
                        break;
                }
            }
        }

        return $filters;
    }

    public function canonizeComplianceTaskId(object|array $filter): int|null
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

    private function findComplianceElements($array, $conditions): array
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

    public function canonizeComplianceProgress($progress): array
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
        $progress['current_time'] = $time;
        $progress['current_time_view'] = $this->utilityService->date($time, ['local' => 'en_US', 'pattern' => 'yyyy/MM/dd']);

        $user = $this->accountService->getAccount(['id' => $progress['user_id']]);
        $progress['user'] = (sizeof($user) > 0) ? $user : new stdClass();

        $progress['next_actions'] = $this->complianceRoadMap[$progress['level']];

        return $progress;
    }

    private function findObjectByIdForCompliance($array, $id)
    {
        foreach ($array as $element) {
            if ($id == $element['id']) {
                return $element;
            }
        }

        return null;
    }

    public function canonizeComplianceTreeList($bucket): array
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
            $answerList = $this->taskService->getAnswersList(['type' => $task['type']], [])['data']['list'];
        }
        $task['value'] = $answerList;
        $task['rule'] = $this->findObjectByIdForCompliance($rules, $task['rule_id']);
        $task['warranty'] = isset($task['warranty_id']) ? $this->findObjectByIdForCompliance($warranties, $task['warranty_id']) : [];
        $task['mandatory_unit'] = (isset($task['mandatory_unit']) && !empty($task['mandatory_unit'])) ? json_decode($task['mandatory_unit'], true) : [];

        $time = time();
        $task['current_time'] = $time;
        $task['current_time_view'] = $this->utilityService->date($time, ['local' => 'en_US', 'pattern' => 'yyyy/MM/dd']);

        if ($task['type'] == 'maturity') {
            foreach ($domainTree as $domain) {
                if ($domain['id'] == $task['section_id']) {
                    $dm = $domain;
                    $dm['children'] = $domain;
                    $task['section'] = $dm;
                }
            }
        } else {
            foreach ($domainTree as $domain) {
                if ($domain['id'] == $task['section_id']) {
                    $dm = $domain;
                    $dm['children'] = $domain;
                    $task['section'] = $dm;
                } else {
                    foreach ($domain['children'] as $subDomain) {
                        if ($subDomain['id'] == $task['section_id']) {
                            $dm = $domain;
                            $dm['children'] = $subDomain;
                            $task['section'] = $dm;
                        }
                    }
                }
            }
        }
        $task['user'] = new stdClass();
        if (isset($task['user_id'])) {
            foreach ($members['list'] as $member) {
                if ($member['id'] == $task['user_id']) {
                    $task['user'] = $member;
                }
            }
        }

        if (empty($progressList)) {
            $progress = $this->canonizeComplianceProgress(
                $this->complianceTaskRepository->getComplianceProgress(['task_id' => $task['id']])
            );
            $task['progress'] = (sizeof($progress) > 0) ? $progress : (new stdClass());
        } else {
            $conditionPrams = $task['type'] == 'maturity' ?
                [
                    [
                        'field' => 'task_id',
                        'value' => $task['id'],
                    ],
                ] :
                [
                    [
                        'field' => 'task_id',
                        'value' => $task['id'],
                    ],
                    [
                        'field' => 'parent_id',
                        'value' => 0,
                    ],
                ];

            $task['progress'] = $this->findComplianceElements(
                $progressList,
                $conditionPrams
            )[0] ?? (new stdClass());

            $task['progress_child'] = $this->findComplianceElements(
                $progressList,
                [
                    [
                        'field' => 'task_id',
                        'value' => $task['id'],
                    ],
                    [
                        'field' => 'type',
                        'value' => 'child',
                    ],
                ]
            )[0] ?? (new stdClass());
        }

        return $task;
    }
}
