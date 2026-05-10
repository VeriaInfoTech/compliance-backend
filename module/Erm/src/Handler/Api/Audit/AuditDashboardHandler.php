<?php

namespace Erm\Handler\Api\Audit;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;
 use Pi\User\Service\RoleService;

class AuditDashboardHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var TaskService */
    protected TaskService $taskService;

    /** @var RoleService */
    protected RoleService $roleService;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface   $streamFactory,
        TaskService              $taskService,
        RoleService              $roleService
    )
    {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->taskService = $taskService;
        $this->roleService = $roleService;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        ini_set('error_reporting', E_ERROR);

        // Get account
        $account = $request->getAttribute('account');

        // Retrieve the raw JSON data from the request body
        $stream = $this->streamFactory->createStreamFromFile('php://input');
        $rawData = $stream->getContents();

        // Decode the raw JSON data into an associative array
        $requestBody = json_decode($rawData, true);

        $params = $requestBody;
        ///TODO: review for role access control
//        $params['user_id'] = $account['id'];

        // role access control
        $roles = ($this->roleService->getRoleAccount((int)$account['id']));
        if (!in_array('grc_admin', $roles)) {
            $params['enforcer'] = $account['id'];
        }

        // role access control
        $roles = ($this->roleService->getRoleAccount((int)$account['id']));
        if (!in_array('grc_admin', $roles)) {
            $params['enforcer'] = $account['id'];
        }
        $params['type'] = ['audit'];

        $allTask = $this->taskService->getAuditList($params, $account)['data']['list'];
        $domainTree = $this->taskService->getDomainTree(['status'=>[1],'type'=>['audit','risk','compliance']], []);

        $taskObject = [];
        foreach ($allTask as $task) {
            if(isset($taskObject[$task['section']['children']['slug']]['total_task'])){
                $taskObject[$task['section']['children']['slug']]['total_task']++;
            }else{
                $taskObject[$task['section']['children']['slug']]['total_task']= 1;
            }
            if ($task['progress'] != (object)[]) {
                $taskObject[$task['section']['children']['slug']][$task['progress']['answer_score']]++;
                if(isset($taskObject[$task['section']['children']['slug']]['in_progress_count'])){
                    $taskObject[$task['section']['children']['slug']]['in_progress_count']++;
                }else{
                    $taskObject[$task['section']['children']['slug']]['in_progress_count']= 1;
                }

                if (isset($taskObject[$task['section']['children']['children']['slug']]['total'])) {
                    $taskObject[$task['section']['children']['slug']]['total_score'] = $task['progress']['answer_score'];
                } else {
                    $taskObject[$task['section']['children']['slug']]['total_score'] =$taskObject[$task['section']['children']['slug']]['total_score']+ $task['progress']['answer_score'];
                }

            }
        }
        foreach ($allTask as $task) {
            if(isset($taskObject[$task['section']['slug']]['total_task'])){
                $taskObject[$task['section']['slug']]['total_task']++;
            }else{
                $taskObject[$task['section']['slug']]['total_task']= 1;
            }
            if ($task['progress'] != (object)[]) {
                $taskObject[$task['section']['slug']][$task['progress']['answer_score']]++;
                if(isset($taskObject[$task['section']['slug']]['in_progress_count'])){
                    $taskObject[$task['section']['slug']]['in_progress_count']++;
                }else{
                    $taskObject[$task['section']['slug']]['in_progress_count']= 1;
                }

                if (isset($taskObject[$task['section']['slug']]['total'])) {
                    $taskObject[$task['section']['slug']]['total_score'] = $task['progress']['answer_score'];
                } else {
                    $taskObject[$task['section']['slug']]['total_score'] =$taskObject[$task['section']['children']['slug']]['total_score']+ $task['progress']['answer_score'];
                }

            }
        }
        $dashboard = [];

        $tableHeader = [
            [
                "title" => "تایید نمی شود"
            ],
            [
                "title" => "تاحدودی تایید می شود"
            ],
            [
                "title" => "کاملا تایید می شود"
            ],
        ];

        $table = [];

        $gages = [];
        foreach ($domainTree as $domainParent) {
            $table['title'] = $domainParent['title'];
            $rows = [];
            $gage = [];
            $rows[0]['title'] = "فرایند";
            $rows[0]['cells'] = $tableHeader;
            foreach ($domainParent['children'] as $domain) {
                $cells = [];
                $cells['title'] = $domain['title'];
                $cells['cells'] = [
                    [
                        "title" => (string)$taskObject[$domain['slug']]["0"]
                    ],
                    [
                        "title" => (string)$taskObject[$domain['slug']]["50"]
                    ],
                    [
                        "title" => (string)$taskObject[$domain['slug']]["100"]
                    ],
                ];
                $rows[] = $cells;

                $gage['title'] = $domain['title'];
                $gage['total_tasks'] = $taskObject[$domain['slug']]["total_task"]??0;
                $gage['in_progress'] = $taskObject[$domain['slug']]["in_progress_count"]??0;
                $base = isset($taskObject[$domain['slug']]["in_progress_count"])?(int)$taskObject[$domain['slug']]["in_progress_count"]>0?$taskObject[$domain['slug']]["in_progress_count"]:1:1 ;
                $gage['average'] =((int)$taskObject[$domain['slug']]["total_score"])/$base;

                $gages[]=$gage;
            }
            $table['rows'] = array_values($rows);
            $dashboard['tables'][] = $table;
            $dashboard['gages']  = $gages;
        }


        $table['title'] = 'بلوغ';
        $rows = [];
        $gage = [];
        $rows[0]['title'] = "فرایند";
        $rows[0]['cells'] = $tableHeader;
        foreach ($domainTree as $domain) {
            $cells = [];
            $cells['title'] = $domain['title'];
            $cells['cells'] = [
                [
                    "title" => (string)$taskObject[$domain['slug']]["0"]
                ],
                [
                    "title" => (string)$taskObject[$domain['slug']]["25"]
                ],
                [
                    "title" => (string)$taskObject[$domain['slug']]["50"]
                ],
                [
                    "title" => (string)$taskObject[$domain['slug']]["75"]
                ],
                [
                    "title" => (string)$taskObject[$domain['slug']]["100"]
                ],
            ];
            $rows[] = $cells;


            $gage['title'] = $domain['title'];
            $gage['total_tasks'] = $taskObject[$domain['slug']]["total_task"] ?? 0;
            $gage['in_progress'] = $taskObject[$domain['slug']]["in_progress_count"] ?? 0;
            $base = isset($taskObject[$domain['slug']]["in_progress_count"]) ? (int)$taskObject[$domain['slug']]["in_progress_count"] > 0 ? $taskObject[$domain['slug']]["in_progress_count"] : 1 : 1;
            $gage['average'] = ((int)$taskObject[$domain['slug']]["total_score"]) / $base;

            $gages[] = $gage;
        }
        $table['rows'] = array_values($rows);
        $dashboard[]['tables'][] = $table;
        $dashboard[]['gages'] = $gages;

        $domain = [];
        $radar = [];
        foreach ($allTask as $task) {
            if ($task['progress'] != (object)[]) {
                $domain[$task['section']['slug']][$task['progress']['answer_score']]++;
                $domain[$task['section']['slug']]['in_progress_count']++;
                $radar[$task['section']['children']['slug']][$task['progress']['answer_score']]++;
                $radar[$task['section']['children']['slug']]['in_progress_count']++;

            }
            $domain[$task['section']['slug']]['total_count']++;
            $domain[$task['section']['slug']]['list'][] = $task;
            $domain[$task['section']['slug']]['title'] = $task['section']['title'];
            $domain[$task['section']['slug']]['slug'] = $task['section']['slug'];

            $radar[$task['section']['children']['slug']]['title'] = $task['section']['children']['title'];
            $radar[$task['section']['children']['slug']]['slug'] = $task['section']['children']['slug'];
            $radar[$task['section']['children']['slug']]['slug'] = $task['section']['children']['slug'];
            $radar[$task['section']['children']['slug']]['total_count']++;

        }

        foreach ($domain as $section) {
            $financial = [];
            foreach ($section['list'] as $task) {
                if ($task['warranty']['slug'] != '') {
                    if ($task['progress'] != (object)[]) {
                        $financial[$task['warranty']['slug']][$task['progress']['answer_score']]++;
                        $financial[$task['warranty']['slug']]['in_progress_count']++;
                    }
                    $financial[$task['warranty']['slug']]['total_count']++;
                    $financial[$task['warranty']['slug']]['title'] = $task['warranty']['title'];
                    $financial[$task['warranty']['slug']]['slug'] = $task['warranty']['slug'];
                }
            }
            unset($domain[$section['slug']]['list']);
            $domain[$section['slug']]['financial'] = $financial;
        }

        foreach ($domain as $k => $parent) {
            if ($parent['in_progress_count'])
                $domain[$k]['average'] = number_format($this->calculateValue($parent) / $parent['in_progress_count'], 2);

            foreach ($parent['financial'] as $ck => $child) {
                if ($child['in_progress_count'])
                    $domain[$k]['financial'][$ck]['average'] = number_format($this->calculateValue($child) / $child['in_progress_count'], 2);
            }
        }

        $report = $this->addMissingKeys($domain);

        foreach ($domainTree as $key => $node) {

            foreach ($node['children'] as $nodeKey => $child) {
                $flag = isset($radar[$domainTree[$key]['children'][$nodeKey]['slug']]);
                $slug = $domainTree[$key]['children'][$nodeKey]['slug'];
                $title = $domainTree[$key]['children'][$nodeKey]['title'];
                $domainTree[$key]['children'][$nodeKey] = [];

                $domainTree[$key]['children'][$nodeKey]['slug'] = $slug;
                $domainTree[$key]['children'][$nodeKey]['title'] = $title;
                $domainTree[$key]['children'][$nodeKey]['0'] =
                    $flag ?
                        $radar[$domainTree[$key]['children'][$nodeKey]['slug']]['0'] ?? 0 :
                        0;
                $domainTree[$key]['children'][$nodeKey]['50'] =
                    $flag ?
                        $radar[$domainTree[$key]['children'][$nodeKey]['slug']]['50'] ?? 0 :
                        0;
                $domainTree[$key]['children'][$nodeKey]['100'] =
                    $flag ?
                        $radar[$domainTree[$key]['children'][$nodeKey]['slug']]['100'] ?? 0 :
                        0;
                $domainTree[$key]['children'][$nodeKey]['in_progress_count'] =
                    $flag ?
                        $radar[$domainTree[$key]['children'][$nodeKey]['slug']]['in_progress_count'] ?? 0 :
                        0;
                $domainTree[$key]['children'][$nodeKey]['total_count'] =
                    $flag ?
                        $radar[$domainTree[$key]['children'][$nodeKey]['slug']]['total_count'] ?? 0 :
                        0;

            }
        }



        $dashboard['radar'] = $domainTree;
        return new JsonResponse($dashboard);

    }

    public function calculateValue($object): float|int
    {
        $total = 0;
        foreach ($object as $key => $value) {
            if (is_numeric($key)) {
                $total += $value * $key;
            }
        }
        return $total;
    }

    public function addMissingKeys($data): array
    {

        ///TODO: set dynamic bottom line
        $domainKeys = [];
        $title = [];
        foreach ($this->taskService->getDomainTree([], []) as $item) {
            $domainKeys[] = $item['slug'];
            $title[$item['slug']] = $item['title'];
        }
        $defaultKeys = ["0", "100", "50", "average", "title", "slug", "in_progress_count", "total_count"];
        $financialKeys = ["rial", "rial_currency", "none_financial", "currency"];

        $missingKeys = array_diff($domainKeys, array_keys($data));
        foreach ($missingKeys as $missingKey) {
            $data[$missingKey] = [];
            $data[$missingKey]['financial'] = [];
            $data[$missingKey]['slug'] = $missingKey;
            $data[$missingKey]['title'] = $title[$missingKey];

        }

        foreach ($data as $key => &$value) {

            $missingKeys = array_diff($defaultKeys, array_keys($value));
            foreach ($missingKeys as $missingKey) {
                $data[$key][$missingKey] = null;
            }

            $missingFinancialKeys = array_diff($financialKeys, array_keys($value['financial']));
            foreach ($missingFinancialKeys as $missingKey) {
                $data[$key]['financial'][$missingKey] = [];
            }


            foreach ($data[$key]['financial'] as $fk => $fValue) {
                $missingKeys = array_diff($defaultKeys, array_keys($fValue));
                foreach ($missingKeys as $missingKey) {
                    $data[$key]['financial'][$fk][$missingKey] = null;
                }
            }
        }

        return $data;
    }
}
