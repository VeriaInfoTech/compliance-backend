<?php

namespace Erm\Handler\Api\Risk;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;
 use Pi\User\Service\RoleService;

class RiskDashboardHandler implements RequestHandlerInterface
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

        // role access control
        $roles = ($this->roleService->getRoleAccount((int)$account['id']));
        if (!in_array('grc_admin', $roles)) {
            $params['enforcer'] = $account['id'];
        }

        $params['level'] = 'approve';


        $baseHeatmap = [
            "0-5" => [
                "from" => 0,
                "to" => 5,
                "name" => "0-5",
                "color" => "#53c665",
                "1" => 0,
                "2" => 0,
                "3" => 0,
                "4" => 0,
                "5" => 0,
            ],
            "5-10" => [
                "from" => 5,
                "to" => 10,
                "name" => "5-10",
                "color" => "#cef33b",
                "6" => 0,
                "7" => 0,
                "8" => 0,
                "9" => 0,
                "10" => 0,
            ],
            "10-15" => [
                "from" => 10,
                "to" => 15,
                "name" => "10-15",
                "color" => "#fff000",
                "11" => 0,
                "12" => 0,
                "13" => 0,
                "14" => 0,
                "15" => 0,
            ],
            "15-20" => [
                "from" => 15,
                "to" => 20,
                "name" => "15-20",
                "color" => "#ffa200",
                "16" => 0,
                "17" => 0,
                "18" => 0,
                "19" => 0,
                "20" => 0,
            ],
            "20-25" => [
                "from" => 20,
                "to" => 25,
                "name" => "20-25",
                "color" => "#ff0000",
                "21" => 0,
                "22" => 0,
                "23" => 0,
                "24" => 0,
                "25" => 0,
            ],
            'section_id' => null
        ];
        $takList = $this->taskService->getRiskList($params, $account)['data']['list'];
        $domain = $this->taskService->getDomainTree(['status'=>[1]], []);

        $heatmap['total'] = $baseHeatmap;
        $heatmap['total']['title'] = 'کل';
        $heatmap['total']['slug'] = 'total';

        foreach ($domain as $node) {
            $heatmap[$node['slug']] = $baseHeatmap;
            $heatmap[$node['slug']]['title'] = $node['title'];
            $heatmap[$node['slug']]['slug'] = $node['slug'];
        }

        foreach ($takList as $task) {

            $hasRiskProgress = (!empty($task['risk']) && ($task['risk'] != new stdClass()));
            if ($hasRiskProgress) {
                $number = $task['risk']['risk_data'];
                switch (true) {
                    case ($number < 6):
                        $heatmap['total']['0-5'][$number]++;
                        $heatmap[$task['section']['slug']]['0-5'][$number]++;
                        $heatmap[$task['section']['slug']]['0-5']['section_id'][] = $task['section_id'];
                        $heatmap[$task['section']['slug']]['0-5']['section_id'] = array_unique($heatmap[$task['section']['slug']]['0-5']['section_id']);
                        break;
                    case ($number < 11):
                        $heatmap['total']['5-10'][$number]++;
                        $heatmap[$task['section']['slug']]['5-10'][$number]++;
                        $heatmap[$task['section']['slug']]['5-10']['section_id'][] = $task['section_id'];
                        $heatmap[$task['section']['slug']]['5-10']['section_id'] = array_unique($heatmap[$task['section']['slug']]['5-10']['section_id']);
                        break;
                    case ($number < 16):
                        $heatmap['total']['10-15'][$number]++;
                        $heatmap[$task['section']['slug']]['10-15'][$number]++;
                        $heatmap[$task['section']['slug']]['10-15']['section_id'][] = $task['section_id'];
                        $heatmap[$task['section']['slug']]['10-15']['section_id'] = array_unique($heatmap[$task['section']['slug']]['10-15']['section_id']);
                        break;
                    case ($number < 21):
                        $heatmap['total']['15-20'][$number]++;
                        $heatmap[$task['section']['slug']]['15-20'][$number]++;
                        $heatmap[$task['section']['slug']]['15-20']['section_id'] [] = $task['section_id'];
                        $heatmap[$task['section']['slug']]['15-20']['section_id'] = array_unique($heatmap[$task['section']['slug']]['15-20']['section_id']);
                        break;
                    case ($number < 26):
                        $heatmap['total']['20-25'][$number]++;
                        $heatmap[$task['section']['slug']]  ['20-25'][$number]++;
                        $heatmap[$task['section']['slug']]['20-25']['section_id'][] = $task['section_id'];
                        $heatmap[$task['section']['slug']]['20-25']['section_id'] = array_unique($heatmap[$task['section']['slug']]['20-25']['section_id']);
                        break;
                }


            }

            foreach ($domain as $key => $section) {
                foreach ($section['children'] as $ck => $child) {
                    if (!isset($domain[$key]['children'][$ck]['risk_count'])) {
                        $domain[$key]['children'][$ck]['risk_count'] = 0;
                        $domain[$key]['children'][$ck]['risk_data'] = 0;
                        $domain[$key]['children'][$ck]['ration'] = 1;
                        $domain[$key]['children'][$ck]['x'] = 0;
                        $domain[$key]['children'][$ck]['y'] = 0;
                    }
                    if ($child['id'] == $task['section_id']) {
                        $domain[$key]['children'][$ck]['risk_count']++;
                        $domain[$key]['children'][$ck]['risk_data'] += ($task['risk']['risk_data']);
                    }
                }
            }
        }

        foreach ($domain as $key => $section) {
            foreach ($section['children'] as $ck => $child) {
                if ($domain[$key]['children'][$ck]['risk_count'] > 0) {
                    $randomNum = rand(0, (25 - $domain[$key]['children'][$ck]['risk_data']));
                    $domain[$key]['children'][$ck]['risk_data'] = floatval(number_format(($domain[$key]['children'][$ck]['risk_data']) / $domain[$key]['children'][$ck]['risk_count'], 2));
                    $domain[$key]['children'][$ck]['ration'] = ceil((1 + ($domain[$key]['children'][$ck]['risk_count'] / 10)) * 100);
                    $domain[$key]['children'][$ck]['x'] = abs(number_format(($domain[$key]['children'][$ck]['risk_data'] + $randomNum), 2) * 100);
                    $domain[$key]['children'][$ck]['y'] = abs(number_format(($domain[$key]['children'][$ck]['risk_data']) / ($domain[$key]['children'][$ck]['risk_data'] + $randomNum), 2) * 100);
                }else{
                    $domain[$key]['children'][$ck]['risk_data'] = 0;

                }
            }
        }
        $result['heatmap'] = $heatmap;
        $result['heatmap_dynamic'] = array_values($heatmap);
        $result['radar'] = $domain;

        return new JsonResponse($result);

    }

}
