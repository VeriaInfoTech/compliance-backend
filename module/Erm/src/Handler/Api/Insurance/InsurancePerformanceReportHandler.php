<?php

namespace Erm\Handler\Api\Insurance;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;
 use Pi\User\Service\RoleService;

class InsurancePerformanceReportHandler implements RequestHandlerInterface
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


        ///TODO: improve performance via move process on webserver
        $allTaskCount = 1;
        $waitingProgressTaskCount = 0;
        $todoProgressTaskCount = 0;
        $doingProgressTaskCount = 0;
        $doneProgressTaskCount = 0;
        $rejectProgressTaskCount = 0;
        $approveProgressTaskCount = 0;

        // role access control
        $roles = ($this->roleService->getRoleAccount((int)$account['id']));
        if (!in_array('grc_admin', $roles)) {
            $params['enforcer'] = $account['id'];
        }


        foreach ($this->taskService->getTaskTreeWhitFilter($params, $account)['data']['list'] as $task) {
            $allTaskCount++;
            if (!empty($task['progress']) && ($task['progress'] != new stdClass())) {
                switch ($task['progress']['level']) {
                    case "todo":
                        $todoProgressTaskCount++;
                        break;
                    case "doing":
                        $doingProgressTaskCount++;
                        break;
                    case "done":
                        $doneProgressTaskCount++;
                        break;
                    case "reject":
                        $rejectProgressTaskCount++;
                        break;
                    case "approve":
                        $approveProgressTaskCount++;
                        break;

                }
            } else {
                $waitingProgressTaskCount++;
            }
        }


        $performance = [
            "done" => [
                "title" => "تکالیف اظهار نظر شده",
                "description" => "درصد تکالیف اظهار نظر شده توسط رابطین",
                "total" => $allTaskCount,
                "count" => $doneProgressTaskCount,
                "percent" => number_format((($doneProgressTaskCount / $allTaskCount) * 100), 2),
            ],
            "reject" => [
                "title" => "تکالیف رد شده",
                "description" => "در صد تکالیف رد شده توسط اداره تطبیق",
                "total" => $allTaskCount,
                "count" => $rejectProgressTaskCount,
                "percent" => number_format((($rejectProgressTaskCount / $allTaskCount) * 100), 2),
            ],
            "approve" => [
                "title" => "تکالیف تایید شده",
                "description" => "در صد تکالیف تایید شده توسط اداره تطبیق",
                "total" => $allTaskCount,
                "count" => $approveProgressTaskCount,
                "percent" => number_format((($approveProgressTaskCount / $allTaskCount) * 100), 2),
            ],
            "pie" => [
                [
                    "title" => "درانتظار ارجاع",
                    "value" => "waiting",
                    "count" => $waitingProgressTaskCount,
                    "color" => "",
                ],
                [
                    "title" => "در انتظار انجام",
                    "value" => "todo",
                    "count" => $todoProgressTaskCount,
                    "color" => "",
                ],
                [
                    "title" => "در حال انجام",
                    "value" => "doing",
                    "count" => $doingProgressTaskCount,
                    "color" => "",
                ],
                [
                    "title" => "انجام شده",
                    "value" => "done",
                    "count" => $doingProgressTaskCount,
                    "color" => "",
                ],
                [
                    "title" => "تایید شده",
                    "value" => "approve",
                    "count" => $approveProgressTaskCount,
                    "color" => "",
                ],
                [
                    "title" => "رد شده",
                    "value" => "reject",
                    "count" => $rejectProgressTaskCount,
                    "color" => "",
                ],
            ]
        ];

        $params['level'] = 'todo,done,doing,reject,approve';
        $taskList = $this->taskService->getTaskTreeWhitFilter($params, $account)['data']['list'];

        $performance['daily']['title']= 'گزارش عملکرد روزانه در یک ماه اخیر';
        $performance['daily']['count']= sizeof($taskList);
        $dates = [];
        $endDate = strtotime('today');
        $startDate = strtotime('-1 month', $endDate);

        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            $date = date('Y/m/d', $currentDate);
            $dates[] = $date;
            $currentDate = strtotime('+1 day', $currentDate);
        }

        foreach ($dates as $date) {
            $approveCount = 0;
            $todoCount = 0;
            foreach ($taskList as $progress) {
                $progress = $progress['progress'];
                $progressDate = date('Y/m/d', $progress['time_create']);
                if ($progressDate == $date) {
                    $todoCount++;
                }

                if ($progress['level'] == 'approve' && date('Y/m/d', $progress['time_update']) == $date) {
                    $approveCount++;
                }
            }
            $performance['daily']['list'][] = [
                'title' => $date,
                'todo' => $todoCount,
                'approve' => $approveCount,
            ];
        }

        return new JsonResponse($performance);


    }
}
