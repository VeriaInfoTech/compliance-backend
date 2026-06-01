<?php

namespace Content\Handler\Api\Dashboard;

use Content\Service\ItemService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DashboardGetHandler implements RequestHandlerInterface
{
    protected ItemService $itemService;

    // ─── Pillar config ────────────────────────────────────────────────────────
    private const PILLAR_CONFIG = [
        'environmental' => [
            'i18n_key'    => 'esg.pillar.environmental',
            'color_theme' => 'green',
            'color_hex'   => '#6BCB77',
            'icon'        => 'leaf',
            'order'       => 1,
        ],
        'social' => [
            'i18n_key'    => 'esg.pillar.social',
            'color_theme' => 'blue',
            'color_hex'   => '#4D96FF',
            'icon'        => 'people',
            'order'       => 2,
        ],
        'governance' => [
            'i18n_key'    => 'esg.pillar.governance',
            'color_theme' => 'purple',
            'color_hex'   => '#9B59B6',
            'icon'        => 'shield',
            'order'       => 3,
        ],
    ];

    // ─── Component registry ───────────────────────────────────────────────────
    // برای اضافه کردن چارت جدید فقط اینجا یه ردیف اضافه کن
    private const COMPONENT_MAP = [
        'gauge'          => 'EsgGaugeChart',
        'radar'          => 'EsgRadarChart',
        'bar'            => 'EsgBarChart',
        'bar_mixed'      => 'EsgBarMixedChart',
        'bar_horizontal' => 'EsgBarHorizontalChart',
        'donut'          => 'EsgCompletionDonut',
    ];

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    // =========================================================================
    // ENTRY POINT
    // =========================================================================
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $raw = $this->itemService->getItemList(['type' => ['domain', 'control']]);

        // ── 1. جدا کردن domain ها و control ها از لیست خام ─────────────────
        $domainMap        = [];   // [slug => item]
        $controlsByDomain = [];   // [domain_slug => [item, ...]]

        foreach ($raw['data']['list'] as $item) {
            if ($item['type'] === 'domain') {
                $domainMap[$item['slug']] = $item;
            } elseif ($item['type'] === 'control') {
                $controlsByDomain[$item['domain_slug']][] = $item;
            }
        }

        // ── 2. ساخت pillars ──────────────────────────────────────────────────
        $pillars = $this->buildPillars($domainMap, $controlsByDomain);

        // ── 3. summary کلی ───────────────────────────────────────────────────
        $summary = $this->buildGlobalSummary($pillars);

        return new JsonResponse([
            'result' => true,
            'data'   => [
                'meta'    => [
                    'version'       => '2.0',
                    'generated_at'  => date('c'),
                    'chart_library' => 'echarts',
                    'rtl'           => true,
                ],
                'summary' => $summary,
                'pillars' => $pillars,
            ],
            'error' => [],
        ]);
    }

    // =========================================================================
    // PILLAR BUILDER
    // =========================================================================
    private function buildPillars(array $domainMap, array $controlsByDomain): array
    {
        $pillars = [];

        foreach (self::PILLAR_CONFIG as $pillarKey => $cfg) {

            // فقط دامین‌های این pillar
            $pillarDomains = array_filter(
                $domainMap,
                fn($d) => $d['source'] === $pillarKey
            );

            $builtDomains      = [];
            $pillarTotalCtrl   = 0;
            $pillarAnsweredCtrl = 0;
            $scoreSum          = 0.0;
            $scoreCount        = 0;

            foreach ($pillarDomains as $slug => $domain) {
                $controls = $controlsByDomain[$slug] ?? [];

                // مرتب‌سازی کنترل‌ها بر اساس order
                usort($controls, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

                $built = $this->buildDomain($domain, $controls, $cfg);
                $builtDomains[] = $built;

                $pillarTotalCtrl    += $built['stats']['total_count'];
                $pillarAnsweredCtrl += $built['stats']['answered_count'];

                if ($built['stats']['completion_score'] !== null) {
                    $scoreSum += $built['stats']['completion_score'];
                    $scoreCount++;
                }
            }

            // مرتب‌سازی دامین‌ها
            usort($builtDomains, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

            $pillarScore = $scoreCount > 0 ? round($scoreSum / $scoreCount, 1) : null;

            $pillars[] = [
                'key'         => $pillarKey,
                'i18n_key'    => $cfg['i18n_key'],
                'color_theme' => $cfg['color_theme'],
                'color_hex'   => $cfg['color_hex'],
                'icon'        => $cfg['icon'],
                'order'       => $cfg['order'],
                'stats'       => [
                    'domain_count'      => count($builtDomains),
                    'total_controls'    => $pillarTotalCtrl,
                    'answered_controls' => $pillarAnsweredCtrl,
                    'completion_score'  => $pillarScore,
                    'completion_pct'    => $pillarTotalCtrl > 0
                        ? round(($pillarAnsweredCtrl / $pillarTotalCtrl) * 100, 1)
                        : 0,
                ],
                // چارت radar: مقایسه score همه دامین‌های این pillar
                'radar_chart' => $this->buildPillarRadarChart($builtDomains, $cfg),
                'domains'     => $builtDomains,
            ];
        }

        usort($pillars, fn($a, $b) => $a['order'] <=> $b['order']);

        return $pillars;
    }

    // =========================================================================
    // DOMAIN BUILDER
    // =========================================================================
    private function buildDomain(array $domain, array $controls, array $pillarCfg): array
    {
        // ── آمار پایه ────────────────────────────────────────────────────────
        $totalCount    = count($controls);
        $answeredCount = 0;
        $pctValues     = [];

        foreach ($controls as $ctrl) {
            if ($ctrl['answer_status'] === 'answered' && $ctrl['answer'] !== null) {
                $answeredCount++;
                if ($ctrl['answer_type'] === 'percentage') {
                    $pctValues[] = (float) $ctrl['answer'];
                }
            }
        }

        // score دامین = میانگین percentage ها (اگر وجود داره) یا درصد تکمیل
        $score = null;
        if (!empty($pctValues)) {
            $score = round(array_sum($pctValues) / count($pctValues), 1);
        } elseif ($totalCount > 0) {
            $score = round(($answeredCount / $totalCount) * 100, 1);
        }

        // ── تشخیص نوع چارت ───────────────────────────────────────────────────
        $chartType = $this->resolveChartType($controls);

        // ── ساخت سری داده چارت ───────────────────────────────────────────────
        $chartData = $this->extractChartData($controls);

        return [
            'id'          => (int) $domain['id'],
            'slug'        => $domain['slug'],
            'code'        => $domain['code'],
            'order'       => (int) $domain['order'],
            'title'       => $domain['title'],
            'description' => $domain['description'],
            'i18n_key'    => 'esg.domain.' . str_replace('-', '_', $domain['slug']),
            'source'      => $domain['source'],
            'stats'       => [
                'total_count'      => $totalCount,
                'answered_count'   => $answeredCount,
                'unanswered_count' => $totalCount - $answeredCount,
                'completion_pct'   => $totalCount > 0
                    ? round(($answeredCount / $totalCount) * 100, 1)
                    : 0,
                'completion_score' => $score,
            ],
            'chart'    => $this->buildDomainChart(
                $domain,
                $chartData,
                $chartType,
                $pillarCfg
            ),
            'controls' => $this->buildControlList($controls),
        ];
    }

    // =========================================================================
    // CHART DATA EXTRACTION
    // استخراج داده چارت از کنترل‌های پاسخ‌داده‌شده
    // =========================================================================
    private function extractChartData(array $controls): array
    {
        $labels      = [];
        $labelsI18n  = [];
        $series      = [];

        foreach ($controls as $ctrl) {
            if ($ctrl['answer_status'] !== 'answered' || $ctrl['answer'] === null) {
                continue;
            }

            $labels[]     = $ctrl['summary'];
            $labelsI18n[] = 'esg.metric.' . str_replace('-', '_', $ctrl['slug']);
            $series[]     = [
                'slug'        => $ctrl['slug'],
                'value'       => $ctrl['answer'],
                'answer_type' => $ctrl['answer_type'],
                'answer_unit' => $ctrl['answer_unit'],
                'metric_code' => $ctrl['metric_code'],
                'unit_i18n'   => 'esg.unit.' . $ctrl['answer_unit'],
            ];
        }

        return [
            'labels'     => $labels,
            'labels_i18n'=> $labelsI18n,
            'series'     => $series,
        ];
    }

    // =========================================================================
    // DOMAIN CHART BUILDER
    // =========================================================================
    private function buildDomainChart(
        array  $domain,
        array  $chartData,
        string $chartType,
        array  $pillarCfg
    ): array {
        return [
            'chart_type'     => $chartType,
            'component_name' => self::COMPONENT_MAP[$chartType] ?? 'EsgBarChart',
            'labels'         => $chartData['labels'],
            'labels_i18n'    => $chartData['labels_i18n'],
            'series'         => $chartData['series'],
            'echarts_config' => $this->buildEchartsConfig(
                $chartType,
                $chartData['labels'],
                $chartData['series'],
                $domain['title'],
                $pillarCfg['color_hex']
            ),
        ];
    }

    // =========================================================================
    // PILLAR RADAR CHART  (مقایسه score دامین‌ها)
    // =========================================================================
    private function buildPillarRadarChart(array $domains, array $pillarCfg): array
    {
        $indicators  = [];
        $values      = [];
        $i18nKeys    = [];

        foreach ($domains as $d) {
            $indicators[] = ['name' => $d['title'], 'max' => 100];
            $values[]     = $d['stats']['completion_score'] ?? 0;
            $i18nKeys[]   = $d['i18n_key'];
        }

        return [
            'chart_type'      => 'radar',
            'component_name'  => 'EsgRadarChart',
            'indicators'      => $indicators,
            'indicators_i18n' => $i18nKeys,
            'values'          => $values,
            'echarts_config'  => $this->buildRadarEchartsConfig(
                $indicators,
                $values,
                $pillarCfg['color_hex']
            ),
        ];
    }

    // =========================================================================
    // GLOBAL SUMMARY
    // =========================================================================
    private function buildGlobalSummary(array $pillars): array
    {
        $totalDomains    = 0;
        $totalControls   = 0;
        $totalAnswered   = 0;
        $pillarScores    = [];
        $pillarScoreVals = [];

        foreach ($pillars as $p) {
            $totalDomains  += $p['stats']['domain_count'];
            $totalControls += $p['stats']['total_controls'];
            $totalAnswered += $p['stats']['answered_controls'];

            $pillarScores[$p['key']] = [
                'score'       => $p['stats']['completion_score'],
                'i18n_key'    => $p['i18n_key'],
                'color_theme' => $p['color_theme'],
                'color_hex'   => $p['color_hex'],
                'icon'        => $p['icon'],
                'completion_pct' => $p['stats']['completion_pct'],
            ];

            if ($p['stats']['completion_score'] !== null) {
                $pillarScoreVals[] = $p['stats']['completion_score'];
            }
        }

        $overallPct   = $totalControls > 0
            ? round(($totalAnswered / $totalControls) * 100, 1)
            : 0;

        $overallScore = !empty($pillarScoreVals)
            ? round(array_sum($pillarScoreVals) / count($pillarScoreVals), 1)
            : null;

        $unanswered = $totalControls - $totalAnswered;

        return [
            'overall_score'       => $overallScore,
            'overall_completion'  => $overallPct,
            'total_domains'       => $totalDomains,
            'total_controls'      => $totalControls,
            'answered_controls'   => $totalAnswered,
            'unanswered_controls' => $unanswered,
            'pillar_scores'       => $pillarScores,

            // ── Donut: تکمیل کلی ─────────────────────────────────────────────
            'completion_chart' => [
                'chart_type'     => 'donut',
                'component_name' => 'EsgCompletionDonut',
                'series'         => [
                    [
                        'i18n_key' => 'esg.status.answered',
                        'value'    => $totalAnswered,
                        'color'    => '#6BCB77',
                    ],
                    [
                        'i18n_key' => 'esg.status.unanswered',
                        'value'    => $unanswered,
                        'color'    => '#e5e7eb',
                    ],
                ],
                'echarts_config' => $this->buildDonutEchartsConfig(
                    $totalAnswered,
                    $unanswered
                ),
            ],

            // ── Bar horizontal: مقایسه سه pillar ─────────────────────────────
            'pillar_compare_chart' => [
                'chart_type'     => 'bar_horizontal',
                'component_name' => 'EsgPillarCompare',
                'series'         => array_values(array_map(
                    fn($key, $ps) => [
                        'key'        => $key,
                        'i18n_key'   => $ps['i18n_key'],
                        'score'      => $ps['score'],
                        'color'      => $ps['color_hex'],
                    ],
                    array_keys($pillarScores),
                    array_values($pillarScores)
                )),
                'echarts_config' => $this->buildPillarCompareEchartsConfig($pillarScores),
            ],
        ];
    }

    // =========================================================================
    // CONTROL LIST  (برای جدول / drill-down)
    // =========================================================================
    private function buildControlList(array $controls): array
    {
        $list = [];

        foreach ($controls as $ctrl) {
            $list[] = [
                'id'              => (int) $ctrl['id'],
                'slug'            => $ctrl['slug'],
                'metric_code'     => $ctrl['metric_code'],
                'kpi_code'        => $ctrl['kpi_code'],
                'order'           => (int) $ctrl['order'],
                // فارسی مستقیم
                'title'           => $ctrl['title'],
                'summary'         => $ctrl['summary'],
                'description'     => $ctrl['description'],
                // i18n
                'i18n_key'        => 'esg.metric.' . str_replace('-', '_', $ctrl['slug']),
                'unit_i18n'       => 'esg.unit.' . $ctrl['answer_unit'],
                'status_i18n'     => 'esg.status.' . $ctrl['answer_status'],
                // داده
                'answer'          => $ctrl['answer'],
                'answer_type'     => $ctrl['answer_type'],
                'answer_unit'     => $ctrl['answer_unit'],
                'answer_status'   => $ctrl['answer_status'],
                'frameworks'      => $ctrl['frameworks'] ?? [],
                'dashboard_usage' => $ctrl['dashboard_usage'] ?? null,
            ];
        }

        return $list;
    }

    // =========================================================================
    // CHART TYPE RESOLVER
    // =========================================================================
    private function resolveChartType(array $controls): string
    {
        $answered = array_values(array_filter(
            $controls,
            fn($c) => $c['answer_status'] === 'answered' && $c['answer'] !== null
        ));

        if (empty($answered)) {
            return 'bar';
        }

        $types = array_unique(array_column($answered, 'answer_type'));

        $allPct = count($types) === 1 && $types[0] === 'percentage';

        if ($allPct) {
            // ≤4 کنترل → gauge  |  >4 کنترل → radar
            return count($answered) <= 4 ? 'gauge' : 'radar';
        }

        $hasPct = in_array('percentage', $types, true);
        $hasNum = count(array_filter($types, fn($t) => $t !== 'percentage')) > 0;

        return ($hasPct && $hasNum) ? 'bar_mixed' : 'bar';
    }

    // =========================================================================
    // ECHARTS CONFIG BUILDERS
    // =========================================================================

    private function buildEchartsConfig(
        string $chartType,
        array  $labels,
        array  $series,
        string $title,
        string $color
    ): array {
        return match ($chartType) {
            'gauge'     => $this->echartsGauge($labels, $series, $color),
            'radar'     => $this->echartsRadar($labels, $series, $title, $color),
            'bar_mixed' => $this->echartsBarMixed($labels, $series, $color),
            default     => $this->echartsBar($labels, $series, $color),
        };
    }

    // ── Gauge ─────────────────────────────────────────────────────────────────
    private function echartsGauge(array $labels, array $series, string $color): array
    {
        $count = count($series);

        // هر gauge در یک ربع صفحه
        $positions = match (true) {
            $count === 1 => [['50%', '55%']],
            $count === 2 => [['25%', '55%'], ['75%', '55%']],
            $count === 3 => [['20%', '55%'], ['50%', '55%'], ['80%', '55%']],
            default      => [['25%', '30%'], ['75%', '30%'], ['25%', '75%'], ['75%', '75%']],
        };

        $gaugeSeries = [];
        foreach ($series as $i => $s) {
            [$cx, $cy] = $positions[$i] ?? ['50%', '55%'];

            $gaugeSeries[] = [
                'type'       => 'gauge',
                'center'     => [$cx, $cy],
                'radius'     => $count === 1 ? '75%' : '42%',
                'startAngle' => 200,
                'endAngle'   => -20,
                'min'        => 0,
                'max'        => 100,
                'splitNumber'=> 4,
                'axisLine'   => [
                    'lineStyle' => [
                        'width' => 10,
                        'color' => [[0.3, '#FF6B6B'], [0.7, '#FFD93D'], [1, '#6BCB77']],
                    ],
                ],
                'pointer'    => ['show' => false],
                'axisTick'   => ['show' => false],
                'splitLine'  => ['show' => false],
                'axisLabel'  => ['show' => false],
                'title'      => [
                    'fontSize'   => 11,
                    'color'      => '#64748b',
                    'offsetCenter'=> ['0%', '72%'],
                ],
                'detail'     => [
                    'fontSize'    => $count === 1 ? 28 : 18,
                    'fontWeight'  => 'bold',
                    'color'       => $color,
                    'formatter'   => '{value}%',
                    'offsetCenter'=> ['0%', '40%'],
                ],
                'data' => [[
                               'value' => (float) $s['value'],
                               'name'  => $labels[$i] ?? '',
                           ]],
            ];
        }

        return ['series' => $gaugeSeries];
    }

    // ── Radar ─────────────────────────────────────────────────────────────────
    private function echartsRadar(
        array  $labels,
        array  $series,
        string $title,
        string $color
    ): array {
        $indicators = [];
        $values     = [];

        foreach ($series as $i => $s) {
            $max = $s['answer_type'] === 'percentage'
                ? 100
                : (float) max($s['value'] * 1.5, 1);

            $indicators[] = ['name' => $labels[$i] ?? '', 'max' => $max];
            $values[]     = (float) $s['value'];
        }

        return [
            'tooltip' => ['trigger' => 'item'],
            'radar'   => [
                'indicator'  => $indicators,
                'shape'      => 'polygon',
                'splitArea'  => ['show' => true],
                'splitLine'  => ['lineStyle' => ['color' => 'rgba(0,0,0,0.08)']],
                'axisName'   => ['fontSize' => 10, 'color' => '#64748b'],
            ],
            'series'  => [[
                              'type'      => 'radar',
                              'name'      => $title,
                              'data'      => [[
                                                  'value'     => $values,
                                                  'name'      => $title,
                                                  'areaStyle' => ['color' => $color, 'opacity' => 0.25],
                                                  'lineStyle' => ['color' => $color, 'width' => 2],
                                                  'itemStyle' => ['color' => $color],
                                              ]],
                          ]],
        ];
    }

    // ── Bar (عدد یا ترکیب) ────────────────────────────────────────────────────
    private function echartsBar(array $labels, array $series, string $color): array
    {
        $values = array_map(fn($s) => (float) $s['value'], $series);

        // رنگ‌بندی بر اساس مقدار (برای percentage)
        $itemStyle = ['borderRadius' => [4, 4, 0, 0]];

        return [
            'tooltip'  => ['trigger' => 'axis', 'confine' => true],
            'grid'     => ['containLabel' => true, 'left' => 8, 'right' => 8, 'top' => 20, 'bottom' => 8],
            'xAxis'    => [
                'type'      => 'category',
                'data'      => $labels,
                'axisLabel' => [
                    'rotate'   => count($labels) > 4 ? 35 : 0,
                    'fontSize' => 10,
                    'color'    => '#64748b',
                    'overflow' => 'truncate',
                    'width'    => 80,
                ],
                'axisLine'  => ['lineStyle' => ['color' => '#e2e8f0']],
            ],
            'yAxis'    => [
                'type'       => 'value',
                'axisLabel'  => ['fontSize' => 10, 'color' => '#64748b'],
                'splitLine'  => ['lineStyle' => ['color' => '#f1f5f9']],
            ],
            'series'   => [[
                               'type'        => 'bar',
                               'data'        => $values,
                               'barMaxWidth' => 48,
                               'itemStyle'   => array_merge($itemStyle, ['color' => $color]),
                               'label'       => [
                                   'show'     => true,
                                   'position' => 'top',
                                   'fontSize' => 10,
                                   'color'    => '#475569',
                               ],
                           ]],
        ];
    }

    // ── Bar Mixed (درصد + عدد روی دو محور) ───────────────────────────────────
    private function echartsBarMixed(array $labels, array $series, string $color): array
    {
        $pctData = [];
        $numData = [];

        foreach ($series as $s) {
            if ($s['answer_type'] === 'percentage') {
                $pctData[] = (float) $s['value'];
                $numData[] = null;
            } else {
                $numData[] = (float) $s['value'];
                $pctData[] = null;
            }
        }

        return [
            'tooltip' => ['trigger' => 'axis', 'confine' => true],
            'legend'  => ['bottom' => 0, 'fontSize' => 11],
            'grid'    => ['containLabel' => true, 'left' => 8, 'right' => 8, 'top' => 20, 'bottom' => 28],
            'xAxis'   => [
                'type'      => 'category',
                'data'      => $labels,
                'axisLabel' => [
                    'rotate'   => count($labels) > 4 ? 35 : 0,
                    'fontSize' => 10,
                    'color'    => '#64748b',
                    'overflow' => 'truncate',
                    'width'    => 80,
                ],
            ],
            'yAxis'   => [
                ['type' => 'value', 'name' => 'درصد', 'max' => 100,
                 'axisLabel' => ['formatter' => '{value}%', 'fontSize' => 10]],
                ['type' => 'value', 'name' => 'عدد',
                 'axisLabel' => ['fontSize' => 10]],
            ],
            'series'  => [
                [
                    'name'        => 'درصد',
                    'type'        => 'bar',
                    'yAxisIndex'  => 0,
                    'data'        => $pctData,
                    'barMaxWidth' => 32,
                    'itemStyle'   => ['borderRadius' => [4, 4, 0, 0], 'color' => $color],
                ],
                [
                    'name'        => 'عدد',
                    'type'        => 'bar',
                    'yAxisIndex'  => 1,
                    'data'        => $numData,
                    'barMaxWidth' => 32,
                    'itemStyle'   => ['borderRadius' => [4, 4, 0, 0], 'color' => '#94a3b8'],
                ],
            ],
        ];
    }

    // ── Pillar Radar (مقایسه دامین‌ها) ───────────────────────────────────────
    private function buildRadarEchartsConfig(
        array  $indicators,
        array  $values,
        string $color
    ): array {
        return [
            'tooltip' => ['trigger' => 'item'],
            'radar'   => [
                'indicator' => $indicators,
                'shape'     => 'polygon',
                'splitArea' => ['show' => true],
                'axisName'  => ['fontSize' => 10, 'color' => '#64748b'],
            ],
            'series'  => [[
                              'type' => 'radar',
                              'data' => [[
                                             'value'     => $values,
                                             'areaStyle' => ['color' => $color, 'opacity' => 0.2],
                                             'lineStyle' => ['color' => $color, 'width' => 2],
                                             'itemStyle' => ['color' => $color],
                                         ]],
                          ]],
        ];
    }

    // ── Donut: تکمیل کلی ─────────────────────────────────────────────────────
    private function buildDonutEchartsConfig(int $answered, int $unanswered): array
    {
        return [
            'tooltip' => ['trigger' => 'item'],
            'series'  => [[
                              'type'       => 'pie',
                              'radius'     => ['55%', '80%'],
                              'avoidLabelOverlap' => false,
                              'label'      => [
                                  'show'     => true,
                                  'position' => 'center',
                                  'formatter'=> "{$answered}\nپاسخ‌داده",
                                  'fontSize' => 14,
                                  'fontWeight' => 'bold',
                                  'color'    => '#1e293b',
                              ],
                              'data'       => [
                                  ['value' => $answered,   'name' => 'پاسخ‌داده‌شده', 'itemStyle' => ['color' => '#6BCB77']],
                                  ['value' => $unanswered, 'name' => 'بدون پاسخ',    'itemStyle' => ['color' => '#e2e8f0']],
                              ],
                          ]],
        ];
    }

    // ── Bar Horizontal: مقایسه pillars ───────────────────────────────────────
    private function buildPillarCompareEchartsConfig(array $pillarScores): array
    {
        $names  = [];
        $scores = [];
        $colors = [];

        foreach (self::PILLAR_CONFIG as $key => $cfg) {
            if (!isset($pillarScores[$key])) continue;
            $ps = $pillarScores[$key];
            // برای ترجمه سمت فرانت، اینجا i18n_key میذاریم
            // فرانت میتونه از i18n_key بجای name استفاده کنه
            $names[]  = $ps['i18n_key'];   // فرانت ترجمه میکنه
            $scores[] = $ps['score'] ?? 0;
            $colors[] = $cfg['color_hex'];
        }

        return [
            'tooltip' => ['trigger' => 'axis', 'confine' => true],
            'grid'    => ['containLabel' => true, 'left' => 8, 'right' => 8, 'top' => 12, 'bottom' => 8],
            'xAxis'   => ['type' => 'value', 'max' => 100,
                          'axisLabel' => ['formatter' => '{value}%', 'fontSize' => 11]],
            'yAxis'   => ['type' => 'category', 'data' => $names,
                          'axisLabel' => ['fontSize' => 11]],
            'series'  => [[
                              'type'        => 'bar',
                              'data'        => array_map(
                                  fn($s, $c) => ['value' => $s, 'itemStyle' => ['color' => $c, 'borderRadius' => [0, 6, 6, 0]]],
                                  $scores,
                                  $colors
                              ),
                              'barMaxWidth' => 36,
                              'label'       => ['show' => true, 'position' => 'right',
                                                'formatter' => '{c}%', 'fontSize' => 11],
                          ]],
        ];
    }
}