<?php

namespace Erm\Handler\Api;

use Erm\Service\TaskService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuditPerformanceHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var TaskService */
    protected TaskService $taskService;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        TaskService $taskService
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->taskService     = $taskService;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestBody = $request->getParsedBody();
        $account     = $request->getAttribute('account');

        $params = [
            'user_id'  => (int)$account['id'],
            'company_id' => (int)$account['id'],
            'standard_id' => 1,
        ];

        $result = $this->taskService->getAuditDashboardData($params);

        $jayParsedAry = [
            [
                "name" => "Jan",
                "data" => [
                    [
                        "x" => "w1",
                        "y" => 112
                    ],
                    [
                        "x" => "w2",
                        "y" => 3
                    ],
                    [
                        "x" => "w3",
                        "y" => 31
                    ],
                    [
                        "x" => "w4",
                        "y" => 60
                    ],
                    [
                        "x" => "w5",
                        "y" => 55
                    ],
                    [
                        "x" => "w6",
                        "y" => 84
                    ],
                    [
                        "x" => "w7",
                        "y" => 115
                    ],
                    [
                        "x" => "w8",
                        "y" => 16
                    ],
                    [
                        "x" => "w9",
                        "y" => 118
                    ],
                    [
                        "x" => "w10",
                        "y" => 3
                    ],
                    [
                        "x" => "w11",
                        "y" => 87
                    ],
                    [
                        "x" => "w12",
                        "y" => 99
                    ],
                    [
                        "x" => "w13",
                        "y" => 82
                    ],
                    [
                        "x" => "w14",
                        "y" => 82
                    ],
                    [
                        "x" => "w15",
                        "y" => 94
                    ],
                    [
                        "x" => "w16",
                        "y" => 62
                    ],
                    [
                        "x" => "w17",
                        "y" => 113
                    ],
                    [
                        "x" => "w18",
                        "y" => 55
                    ],
                    [
                        "x" => "w19",
                        "y" => 2
                    ],
                    [
                        "x" => "w20",
                        "y" => 67
                    ]
                ]
            ],
            [
                "name" => "Feb",
                "data" => [
                    [
                        "x" => "w1",
                        "y" => 41
                    ],
                    [
                        "x" => "w2",
                        "y" => 57
                    ],
                    [
                        "x" => "w3",
                        "y" => 29
                    ],
                    [
                        "x" => "w4",
                        "y" => 26
                    ],
                    [
                        "x" => "w5",
                        "y" => 40
                    ],
                    [
                        "x" => "w6",
                        "y" => 10
                    ],
                    [
                        "x" => "w7",
                        "y" => 80
                    ],
                    [
                        "x" => "w8",
                        "y" => 97
                    ],
                    [
                        "x" => "w9",
                        "y" => 28
                    ],
                    [
                        "x" => "w10",
                        "y" => 21
                    ],
                    [
                        "x" => "w11",
                        "y" => 40
                    ],
                    [
                        "x" => "w12",
                        "y" => 52
                    ],
                    [
                        "x" => "w13",
                        "y" => 69
                    ],
                    [
                        "x" => "w14",
                        "y" => 114
                    ],
                    [
                        "x" => "w15",
                        "y" => 67
                    ],
                    [
                        "x" => "w16",
                        "y" => 103
                    ],
                    [
                        "x" => "w17",
                        "y" => 69
                    ],
                    [
                        "x" => "w18",
                        "y" => 7
                    ],
                    [
                        "x" => "w19",
                        "y" => 21
                    ],
                    [
                        "x" => "w20",
                        "y" => 52
                    ]
                ]
            ],
            [
                "name" => "Mar",
                "data" => [
                    [
                        "x" => "w1",
                        "y" => 23
                    ],
                    [
                        "x" => "w2",
                        "y" => 57
                    ],
                    [
                        "x" => "w3",
                        "y" => 98
                    ],
                    [
                        "x" => "w4",
                        "y" => 14
                    ],
                    [
                        "x" => "w5",
                        "y" => 82
                    ],
                    [
                        "x" => "w6",
                        "y" => 52
                    ],
                    [
                        "x" => "w7",
                        "y" => 64
                    ],
                    [
                        "x" => "w8",
                        "y" => 91
                    ],
                    [
                        "x" => "w9",
                        "y" => 32
                    ],
                    [
                        "x" => "w10",
                        "y" => 67
                    ],
                    [
                        "x" => "w11",
                        "y" => 70
                    ],
                    [
                        "x" => "w12",
                        "y" => 31
                    ],
                    [
                        "x" => "w13",
                        "y" => 109
                    ],
                    [
                        "x" => "w14",
                        "y" => 45
                    ],
                    [
                        "x" => "w15",
                        "y" => 114
                    ],
                    [
                        "x" => "w16",
                        "y" => 80
                    ],
                    [
                        "x" => "w17",
                        "y" => 56
                    ],
                    [
                        "x" => "w18",
                        "y" => 53
                    ],
                    [
                        "x" => "w19",
                        "y" => 81
                    ],
                    [
                        "x" => "w20",
                        "y" => 47
                    ]
                ]
            ],
            [
                "name" => "Apr",
                "data" => [
                    [
                        "x" => "w1",
                        "y" => 36
                    ],
                    [
                        "x" => "w2",
                        "y" => 96
                    ],
                    [
                        "x" => "w3",
                        "y" => 105
                    ],
                    [
                        "x" => "w4",
                        "y" => 61
                    ],
                    [
                        "x" => "w5",
                        "y" => 109
                    ],
                    [
                        "x" => "w6",
                        "y" => 8
                    ],
                    [
                        "x" => "w7",
                        "y" => 104
                    ],
                    [
                        "x" => "w8",
                        "y" => 44
                    ],
                    [
                        "x" => "w9",
                        "y" => 45
                    ],
                    [
                        "x" => "w10",
                        "y" => 50
                    ],
                    [
                        "x" => "w11",
                        "y" => 103
                    ],
                    [
                        "x" => "w12",
                        "y" => 106
                    ],
                    [
                        "x" => "w13",
                        "y" => 98
                    ],
                    [
                        "x" => "w14",
                        "y" => 9
                    ],
                    [
                        "x" => "w15",
                        "y" => 97
                    ],
                    [
                        "x" => "w16",
                        "y" => 33
                    ],
                    [
                        "x" => "w17",
                        "y" => 118
                    ],
                    [
                        "x" => "w18",
                        "y" => 44
                    ],
                    [
                        "x" => "w19",
                        "y" => 70
                    ],
                    [
                        "x" => "w20",
                        "y" => 116
                    ]
                ]
            ],
            [
                "name" => "May",
                "data" => [
                    [
                        "x" => "w1",
                        "y" => 23
                    ],
                    [
                        "x" => "w2",
                        "y" => 39
                    ],
                    [
                        "x" => "w3",
                        "y" => 91
                    ],
                    [
                        "x" => "w4",
                        "y" => 90
                    ],
                    [
                        "x" => "w5",
                        "y" => 101
                    ],
                    [
                        "x" => "w6",
                        "y" => 93
                    ],
                    [
                        "x" => "w7",
                        "y" => 6
                    ],
                    [
                        "x" => "w8",
                        "y" => 98
                    ],
                    [
                        "x" => "w9",
                        "y" => 25
                    ],
                    [
                        "x" => "w10",
                        "y" => 54
                    ],
                    [
                        "x" => "w11",
                        "y" => 90
                    ],
                    [
                        "x" => "w12",
                        "y" => 39
                    ],
                    [
                        "x" => "w13",
                        "y" => 56
                    ],
                    [
                        "x" => "w14",
                        "y" => 105
                    ],
                    [
                        "x" => "w15",
                        "y" => 113
                    ],
                    [
                        "x" => "w16",
                        "y" => 117
                    ],
                    [
                        "x" => "w17",
                        "y" => 75
                    ],
                    [
                        "x" => "w18",
                        "y" => 110
                    ],
                    [
                        "x" => "w19",
                        "y" => 73
                    ],
                    [
                        "x" => "w20",
                        "y" => 112
                    ]
                ]
            ],
            [
                "name" => "Jun",
                "data" => [
                    [
                        "x" => "w1",
                        "y" => 21
                    ],
                    [
                        "x" => "w2",
                        "y" => 4
                    ],
                    [
                        "x" => "w3",
                        "y" => 62
                    ],
                    [
                        "x" => "w4",
                        "y" => 12
                    ],
                    [
                        "x" => "w5",
                        "y" => 51
                    ],
                    [
                        "x" => "w6",
                        "y" => 116
                    ],
                    [
                        "x" => "w7",
                        "y" => 101
                    ],
                    [
                        "x" => "w8",
                        "y" => 47
                    ],
                    [
                        "x" => "w9",
                        "y" => 74
                    ],
                    [
                        "x" => "w10",
                        "y" => 66
                    ],
                    [
                        "x" => "w11",
                        "y" => 54
                    ],
                    [
                        "x" => "w12",
                        "y" => 77
                    ],
                    [
                        "x" => "w13",
                        "y" => 67
                    ],
                    [
                        "x" => "w14",
                        "y" => 107
                    ],
                    [
                        "x" => "w15",
                        "y" => 60
                    ],
                    [
                        "x" => "w16",
                        "y" => 38
                    ],
                    [
                        "x" => "w17",
                        "y" => 19
                    ],
                    [
                        "x" => "w18",
                        "y" => 26
                    ],
                    [
                        "x" => "w19",
                        "y" => 76
                    ],
                    [
                        "x" => "w20",
                        "y" => 13
                    ]
                ]
            ],
            [
                "name" => "Jul",
                "data" => [
                    [
                        "x" => "w1",
                        "y" => 97
                    ],
                    [
                        "x" => "w2",
                        "y" => 73
                    ],
                    [
                        "x" => "w3",
                        "y" => 43
                    ],
                    [
                        "x" => "w4",
                        "y" => 24
                    ],
                    [
                        "x" => "w5",
                        "y" => 66
                    ],
                    [
                        "x" => "w6",
                        "y" => 37
                    ],
                    [
                        "x" => "w7",
                        "y" => 114
                    ],
                    [
                        "x" => "w8",
                        "y" => 47
                    ],
                    [
                        "x" => "w9",
                        "y" => 77
                    ],
                    [
                        "x" => "w10",
                        "y" => 86
                    ],
                    [
                        "x" => "w11",
                        "y" => 4
                    ],
                    [
                        "x" => "w12",
                        "y" => 37
                    ],
                    [
                        "x" => "w13",
                        "y" => 98
                    ],
                    [
                        "x" => "w14",
                        "y" => 91
                    ],
                    [
                        "x" => "w15",
                        "y" => 86
                    ],
                    [
                        "x" => "w16",
                        "y" => 64
                    ],
                    [
                        "x" => "w17",
                        "y" => 17
                    ],
                    [
                        "x" => "w18",
                        "y" => 29
                    ],
                    [
                        "x" => "w19",
                        "y" => 92
                    ],
                    [
                        "x" => "w20",
                        "y" => 90
                    ]
                ]
            ],
            [
                "name" => "Aug",
                "data" => [
                    [
                        "x" => "w1",
                        "y" => 33
                    ],
                    [
                        "x" => "w2",
                        "y" => 69
                    ],
                    [
                        "x" => "w3",
                        "y" => 99
                    ],
                    [
                        "x" => "w4",
                        "y" => 117
                    ],
                    [
                        "x" => "w5",
                        "y" => 96
                    ],
                    [
                        "x" => "w6",
                        "y" => 15
                    ],
                    [
                        "x" => "w7",
                        "y" => 54
                    ],
                    [
                        "x" => "w8",
                        "y" => 85
                    ],
                    [
                        "x" => "w9",
                        "y" => 106
                    ],
                    [
                        "x" => "w10",
                        "y" => 8
                    ],
                    [
                        "x" => "w11",
                        "y" => 103
                    ],
                    [
                        "x" => "w12",
                        "y" => 44
                    ],
                    [
                        "x" => "w13",
                        "y" => 90
                    ],
                    [
                        "x" => "w14",
                        "y" => 113
                    ],
                    [
                        "x" => "w15",
                        "y" => 58
                    ],
                    [
                        "x" => "w16",
                        "y" => 37
                    ],
                    [
                        "x" => "w17",
                        "y" => 45
                    ],
                    [
                        "x" => "w18",
                        "y" => 97
                    ],
                    [
                        "x" => "w19",
                        "y" => 85
                    ],
                    [
                        "x" => "w20",
                        "y" => 8
                    ]
                ]
            ],
            [
                "name" => "Sep",
                "data" => [
                    [
                        "x" => "w1",
                        "y" => 13
                    ],
                    [
                        "x" => "w2",
                        "y" => 17
                    ],
                    [
                        "x" => "w3",
                        "y" => 120
                    ],
                    [
                        "x" => "w4",
                        "y" => 70
                    ],
                    [
                        "x" => "w5",
                        "y" => 35
                    ],
                    [
                        "x" => "w6",
                        "y" => 62
                    ],
                    [
                        "x" => "w7",
                        "y" => 55
                    ],
                    [
                        "x" => "w8",
                        "y" => 24
                    ],
                    [
                        "x" => "w9",
                        "y" => 45
                    ],
                    [
                        "x" => "w10",
                        "y" => 50
                    ],
                    [
                        "x" => "w11",
                        "y" => 67
                    ],
                    [
                        "x" => "w12",
                        "y" => 52
                    ],
                    [
                        "x" => "w13",
                        "y" => 76
                    ],
                    [
                        "x" => "w14",
                        "y" => 14
                    ],
                    [
                        "x" => "w15",
                        "y" => 47
                    ],
                    [
                        "x" => "w16",
                        "y" => 98
                    ],
                    [
                        "x" => "w17",
                        "y" => 15
                    ],
                    [
                        "x" => "w18",
                        "y" => 14
                    ],
                    [
                        "x" => "w19",
                        "y" => 27
                    ],
                    [
                        "x" => "w20",
                        "y" => 97
                    ]
                ]
            ]
        ];
        $ranges =  [
            [
                "from" => 0,
                "to" => 10,
                "name" => "0-10",
                "color" => "#133d00"
            ],
            [
                "from" => 10,
                "to" => 20,
                "name" => "10-20",
                "color" => "#237a00"
            ],
            [
                "from" => 20,
                "to" => 30,
                "name" => "20-30",
                "color" => "#72e344"
            ],
            [
                "from" => 30,
                "to" => 40,
                "name" => "30-40",
                "color" => "#b5ff96"
            ],
            [
                "from" => 40,
                "to" => 50,
                "name" => "40-50",
                "color" => "#ececd9"
            ],
            [
                "from" => 50,
                "to" => 60,
                "name" => "50-60",
                "color" => "#eaeaa0"
            ],
            [
                "from" => 60,
                "to" => 70,
                "name" => "60-70",
                "color" => "#f6f658"
            ],
            [
                "from" => 70,
                "to" => 80,
                "name" => "70-80",
                "color" => "#FFFF00"
            ],
            [
                "from" => 80,
                "to" => 90,
                "name" => "80-90",
                "color" => "#ffb9b9"
            ],
            [
                "from" => 90,
                "to" => 100,
                "name" => "90-100",
                "color" => "#f87676"
            ],
            [
                "from" => 100,
                "to" => 110,
                "name" => "100-110",
                "color" => "#ff0000"
            ],
            [
                "from" => 110,
                "to" => 120,
                "name" => "110-120",
                "color" => "#7a0000"
            ]
        ];


        $result = [
            "domain" => [
                "total" => 100,
                "done" => 65
            ],
            "sub_domain" => [
                "total" => 100,
                "done" => 82
            ],
            "task" => [
                "total" => 100,
                "done" => 18
            ],
            "task_status" => [
                "labels" => [
                    'در انتظار انجام', 'درحال انجام', 'انجام شده', 'رد شده', 'تایید شده'
                ],
                "data" => [
                    44, 55, 41, 17, 17
                ]
            ],
            "daily_performance" => [

                "total" => 237,
                "labels" => [
                    "9/10",
                    "9/11",
                    "9/12",
                    "9/13",
                    "9/14",
                    "9/15",
                    "9/16",
                    "9/17",
                    "9/18",
                    "9/19",
                    "9/20",
                    "9/21"
                ],
                "data" => [
                    20, 59, 80, 90, 110, 140, 148, 148, 190, 200, 215, 237
                ]
            ],
            "top_domain" => [
                [
                    "title" => "Information Security Policies",
                    "subDomain" => "2",
                    "task" => "6",
                    "done" => "100%",
                    "id" => "A.5.1"
                ],
                [
                    "title" => "Organisation of information security",
                    "subDomain" => "7",
                    "task" => "14",
                    "done" => "63%",
                    "id" => "A.6.1."
                ],
                [
                    "title" => "Human resources security",
                    "subDomain" => "6",
                    "task" => "10",
                    "done" => "58%",
                    "id" => "A.7.1"
                ]
            ]
        ];

        $response = [
            'result' => true,
            'data' => $result,
            'error' => [],
        ];
        return new JsonResponse($response );
    }
}
