<?php

namespace Risk\Service;

use Risk\Repository\AdjustmentRepositoryInterface;

class AdjustmentService implements ServiceInterface
{
    /* @var AdjustmentRepositoryInterface */
    protected AdjustmentRepositoryInterface $adjustmentRepository;

    /* @var FormService */
    protected FormService $formService;

    /* @var SpreadsheetService */
    protected SpreadsheetService $spreadsheetService;

    /**
     * @param AdjustmentRepositoryInterface $adjustmentRepository
     * @param FormService                   $formService
     * @param SpreadsheetService            $spreadsheetService
     */
    public function __construct(
        AdjustmentRepositoryInterface $adjustmentRepository,
        FormService $formService,
        SpreadsheetService $spreadsheetService
    ) {
        $this->adjustmentRepository = $adjustmentRepository;
        $this->formService          = $formService;
        $this->spreadsheetService   = $spreadsheetService;
    }

    public function adjustmentResult($params)
    {
        return [
            $this->getTable1($params),
            $this->getTable2($params),
        ];
    }

    public function getTable1($params): array
    {
        return [
            'config' => [
                'has_header'  => 1,
                'has_side'    => 1,
                'sheet_key'   => 'sheet1',
                'sheet_title' => 'تحلیل کیفیت اعتباری',
                'table_title' => 'جدول تحلیل کیفیت اعتباری تسهیلات و تعهدات اعطایی و سرمایه‌گذاری‌ها بر اساس رتبه بندی اعتباری داخلی بانک',
                'file_name'   => 'sheet1',
            ],
            'header' => [
                'side' => '#',
                't1c1' => 'تسهيلات اعطایی به بانک‌ها',
                't1c2' => 'تسهيلات اعطایی به بانک‌ها',
                't1c3' => 'تسهيلات اعطایی به مشتريان',
                't1c4' => 'تسهيلات اعطایی به مشتريان',
                't1c5' => 'سرمایه‌گذاری‌ها',
                't1c6' => 'سرمایه‌گذاری‌ها',
                't1c7' => 'تعهدات بابت ضمانت‌ها و اعتبار اسنادی',
                't1c8' => 'تعهدات بابت ضمانت‌ها و اعتبار اسنادی',

            ],
            'data'   => [
                [
                    'side' => '#',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',
                    't1c7' => '',
                    't1c8' => '',
                ],
                [
                    'side' => 'درجه 1- ريسک کم',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',
                    't1c7' => '',
                    't1c8' => '',
                ],
                [
                    'side' => 'درجه 2- ريسک متوسط',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',
                    't1c7' => '',
                    't1c8' => '',
                ],
                [
                    'side' => 'درجه 3- ريسک زیاد',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',
                    't1c7' => '',
                    't1c8' => '',
                ],
                [
                    'side' => 'درجه 4- درآستانه سوخت شدن',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',
                    't1c7' => '',
                    't1c8' => '',
                ],
                [
                    'side' => 'جمع مبلغ ناخالص',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',
                    't1c7' => '',
                    't1c8' => '',
                ],
                [
                    'side' => 'ذخيره کاهش ارزش',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',
                    't1c7' => '',
                    't1c8' => '',
                ],
                [
                    'side' => 'خالص مبلغ دفتري',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',
                    't1c7' => '',
                    't1c8' => '',
                ],
            ],
        ];
    }

    public function getTable2($params): array
    {
        return [
            'config' => [
                'has_header'  => 1,
                'has_side'    => 1,
                'sheet_key'   => 'sheet2',
                'sheet_title' => 'تحلیل کیفیت اعتباری تسهیلات',
                'table_title' => 'جدول تحلیل کیفیت اعتباری تسهیلات و تعهدات اعطایی  بر اساس طبقات دارایی‌ها',
                'file_name'   => 'sheet2',
            ],
            'header' => [
                'side' => '#',
                't1c1' => 'تسهيلات اعطایی به بانک‌ها',
                't1c2' => 'تسهيلات اعطایی به بانک‌ها',
                't1c3' => 'تسهيلات اعطایی به مشتريان',
                't1c4' => 'تسهيلات اعطایی به مشتريان',
                't1c5' => 'سرمایه‌گذاری‌ها',
                't1c6' => 'سرمایه‌گذاری‌ها',

            ],
            'data'   => [
                [
                    'side' => '#',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',
                ],
                [
                    'side' => 'جاري',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',

                ],
                [
                    'side' => 'سررسيد گذشته',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',
                ],
                [
                    'side' => 'معوق',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',
                ],
                [
                    'side' => 'مشکوک',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',
                ],
                [
                    'side' => 'جمع مبلغ ناخالص',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',
                ],
                [
                    'side' => 'ذخيره کاهش ارزش',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',
                ],
                [
                    'side' => 'خالص مبلغ دفتري',
                    't1c1' => '',
                    't1c2' => '',
                    't1c3' => '',
                    't1c4' => '',
                    't1c5' => '',
                    't1c6' => '',
                ],
            ],
        ];
    }
}
