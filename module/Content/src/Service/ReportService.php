<?php

namespace Content\Service;

use Pi\Core\Service\UtilityService;

class ReportService
{
    protected ItemService $itemService;
    protected UtilityService $utilityService;

    public function __construct(
        ItemService $itemService,
        UtilityService $utilityService
    ) {
        $this->itemService = $itemService;
        $this->utilityService = $utilityService;
    }

    /**
     * Generate comprehensive ESG report from dynamic data
     */
    public function generateReport(array $params = []): array
    {
        // Fetch all items (domains and controls) from database
        $rawData = $this->itemService->getItemList(['type' => ['domain', 'control']]);
        $items = $rawData['data']['list'] ?? [];

        // Build report structure
        $report = [
            'last_updated' => date('Y-m-d H:i:s'),
            'reporting_period' => $this->getCurrentReportingPeriod(),
            'total_kpis' => count(array_filter($items, fn($i) => ($i['type'] ?? '') === 'control')),
            'total_domains' => count(array_filter($items, fn($i) => ($i['type'] ?? '') === 'domain')),
        ];

        // === BUILD GOVERNANCE SECTION ===
        $governanceItems = $this->filterBySourceAndTypes($items, 'governance', ['domain', 'control']);
        $report['governance'] = $this->buildComprehensiveSection('governance', $governanceItems);

        // === BUILD SOCIAL SECTION ===
        $socialItems = $this->filterBySource($items, 'social');
        $report['social'] = $this->buildComprehensiveSection('social', $socialItems);

        // === BUILD ENVIRONMENTAL SECTION ===
        $environmentalItems = $this->filterBySource($items, 'environmental');
        $report['environmental'] = $this->buildComprehensiveSection('environmental', $environmentalItems);

        // === CROSS-SECTION ANALYTICS ===
        $report['cross_section_analytics'] = $this->buildCrossAnalytics([
            'governance' => $report['governance'],
            'social' => $report['social'],
            'environmental' => $report['environmental'],
        ]);

        // === FRAMEWORK COVERAGE ===
        $report['framework_coverage'] = $this->buildFrameworkCoverage($items);

        return $report;
    }

    /**
     * Build comprehensive section with all details
     */
    private function buildComprehensiveSection(string $source, array $items): array
    {
        $controls = $this->filterByType($items, 'control');
        $domains = $this->filterByType($items, 'domain');

        $answered = array_filter($controls, fn($c) => ($c['answer_status'] ?? '') === 'answered');
        $unansweredCount = count($controls) - count($answered);

        $domainStats = $this->calculateDomainStats($controls, $domains);

        return [
            'summary' => [
                'total_kpis' => count($controls),
                'answered' => count($answered),
                'unanswered' => $unansweredCount,
                'completion_percentage' => count($controls) ? round(count($answered) / count($controls) * 100, 1) : 0,
                'avg_score' => $this->calculateAveragePercentage($controls),
                'last_updated' => date('Y-m-d H:i:s'),
            ],
            'domains' => $domainStats,
            'all_controls' => $this->formatAllControls($controls),
            'charts' => [
                'radar_data' => $this->buildRadarData($domainStats),
                'domain_bar_scores' => $this->buildDomainBarPercent($domainStats),
                'domain_bar_counts' => $this->buildDomainBarCount($domainStats),
                'completion_chart' => [
                    'answered' => count($answered),
                    'unanswered' => $unansweredCount,
                    'percentage' => count($controls) ? round(count($answered) / count($controls) * 100, 1) : 0,
                ],
            ],
            'detailed_domain_sections' => $this->buildDetailedDomainSections($controls, $domains),
            'metrics_by_category' => $this->groupMetricsByCategory($controls),
        ];
    }

    /**
     * Build cross-section analytics (E, S, G comparison)
     */
    private function buildCrossAnalytics(array $sections): array
    {
        $esgScores = [];
        $esgCompletion = [];

        foreach ($sections as $key => $section) {
            $esgScores[$key] = $section['summary']['avg_score'] ?? 0;
            $esgCompletion[$key] = $section['summary']['completion_percentage'] ?? 0;
        }

        $avgScore = count($esgScores) > 0 ? round(array_sum($esgScores) / count($esgScores), 1) : 0;

        return [
            'overall_esg_score' => $avgScore,
            'esg_scores' => [
                'governance' => $esgScores['governance'] ?? 0,
                'social' => $esgScores['social'] ?? 0,
                'environmental' => $esgScores['environmental'] ?? 0,
            ],
            'esg_completion' => [
                'governance' => $esgCompletion['governance'] ?? 0,
                'social' => $esgCompletion['social'] ?? 0,
                'environmental' => $esgCompletion['environmental'] ?? 0,
            ],
            'score_interpretation' => $this->interpretScore($avgScore),
            'trend_analysis' => $this->calculateTrend(),
        ];
    }

    /**
     * Format all controls with detailed information
     */
    private function formatAllControls(array $controls): array
    {
        return array_map(fn($c) => [
            'id' => $c['id'] ?? null,
            'code' => $c['metric_code'] ?? $c['kpi_code'] ?? '',
            'title' => $c['title'] ?? '',
            'description' => $c['description'] ?? '',
            'domain_title' => $c['domain_title'] ?? '',
            'domain_slug' => $c['domain_slug'] ?? $c['parent_slug'] ?? '',
            'answer' => $c['answer'] ?? null,
            'answer_status' => $c['answer_status'] ?? 'unanswered',
            'answer_type' => $c['answer_type'] ?? '',
            'answer_unit' => $c['answer_unit'] ?? '',
            'answer_date' => $c['answer_date'] ?? null,
            'evidence' => $c['evidence'] ?? null,
            'notes' => $c['notes'] ?? null,
            'priority' => $c['priority'] ?? null,
            'source' => $c['source'] ?? '',
            'order' => $c['order'] ?? null,
            'time_create_view' => $c['time_create_view'] ?? null,
            'time_update_view' => $c['time_update_view'] ?? null,
        ], $controls);
    }

    /**
     * Group metrics by category
     */
    private function groupMetricsByCategory(array $controls): array
    {
        $grouped = [];

        foreach ($controls as $control) {
            $category = $control['category'] ?? 'Uncategorized';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [
                    'name' => $category,
                    'metrics' => [],
                    'total' => 0,
                    'answered' => 0,
                    'avg_score' => 0,
                ];
            }

            $grouped[$category]['metrics'][] = [
                'code' => $control['metric_code'] ?? '',
                'title' => $control['title'] ?? '',
                'value' => $control['answer'] ?? null,
                'unit' => $control['answer_unit'] ?? '',
                'status' => $control['answer_status'] ?? 'unanswered',
            ];

            $grouped[$category]['total']++;

            if (($control['answer_status'] ?? '') === 'answered') {
                $grouped[$category]['answered']++;
            }
        }

        // Calculate category statistics
        foreach ($grouped as &$category) {
            $category['completion'] = $category['total'] > 0 ? round(($category['answered'] / $category['total']) * 100, 1) : 0;

            $percentages = array_filter(
                $category['metrics'],
                fn($m) => $m['status'] === 'answered' && is_numeric($m['value'])
            );

            if (!empty($percentages)) {
                $sum = array_sum(array_map(fn($m) => (float)$m['value'], $percentages));
                $category['avg_score'] = round($sum / count($percentages), 1);
            }
        }

        return array_values($grouped);
    }

    /**
     * Build detailed domain sections with full metrics
     */
    private function buildDetailedDomainSections(array $controls, array $domains): array
    {
        $grouped = [];

        foreach ($controls as $c) {
            $domain = $c['domain_slug'] ?? $c['parent_slug'] ?? 'unknown';
            if (!isset($grouped[$domain])) {
                $grouped[$domain] = [
                    'domain_title' => $c['domain_title'] ?? '',
                    'domain_slug' => $domain,
                    'metrics' => [],
                ];
            }

            $grouped[$domain]['metrics'][] = [
                'code' => $c['metric_code'] ?? '',
                'title' => $c['title'] ?? '',
                'description' => $c['description'] ?? '',
                'value' => $c['answer'] ?? null,
                'unit' => $c['answer_unit'] ?? '',
                'type' => $c['answer_type'] ?? '',
                'status' => $c['answer_status'] ?? 'unanswered',
                'evidence' => $c['evidence'] ?? null,
                'notes' => $c['notes'] ?? null,
            ];
        }

        // Order by domain order from database
        $result = [];
        $domainMap = [];
        foreach ($domains as $domain) {
            $domainMap[$domain['slug']] = $domain;
        }

        foreach ($grouped as $slug => $section) {
            $order = $domainMap[$slug]['order'] ?? 999;
            $section['order'] = $order;
            $result[] = $section;
        }

        usort($result, fn($a, $b) => $a['order'] <=> $b['order']);
        return array_values($result);
    }

    /**
     * Calculate domain statistics
     */
    private function calculateDomainStats(array $controls, array $domains): array
    {
        $byDomain = [];

        foreach ($controls as $control) {
            $slug = $control['domain_slug'] ?? $control['parent_slug'] ?? 'unknown';
            $byDomain[$slug][] = $control;
        }

        $result = [];

        foreach ($domains as $domain) {
            $slug = $domain['slug'];
            $domainControls = $byDomain[$slug] ?? [];

            $result[] = [
                'code' => $domain['code'] ?? '',
                'title' => $domain['title'] ?? '',
                'slug' => $slug,
                'description' => $domain['description'] ?? '',
                'order' => $domain['order'] ?? 999,
                'kpi_count' => count($domainControls),
                'answered' => $this->countAnswered($domainControls),
                'completion' => count($domainControls) > 0 ? round($this->countAnswered($domainControls) / count($domainControls) * 100, 1) : 0,
                'avg_score' => $this->calculateAveragePercentage($domainControls),
                'metrics' => $this->formatDomainMetrics($domainControls),
            ];
        }

        usort($result, fn($a, $b) => $a['order'] <=> $b['order']);
        return $result;
    }

    /**
     * Format domain metrics
     */
    private function formatDomainMetrics(array $controls): array
    {
        return array_map(fn($c) => [
            'code' => $c['metric_code'] ?? '',
            'title' => $c['title'] ?? '',
            'value' => $c['answer'] ?? null,
            'unit' => $c['answer_unit'] ?? '',
            'status' => $c['answer_status'] ?? 'unanswered',
            'evidence' => $c['evidence'] ?? null,
        ], $controls);
    }

    /**
     * Build framework coverage from items
     */
    private function buildFrameworkCoverage(array $items): array
    {
        $frameworks = [
            'GRI' => 0,
            'ISSB' => 0,
            'COSO' => 0,
            'TCFD' => 0,
            'EcoVadis' => 0,
        ];

        foreach ($items as $item) {
            $frameworks_text = $item['frameworks'] ?? $item['framework'] ?? '';

            // Convert array to string if needed
            if (is_array($frameworks_text)) {
                $frameworks_text = implode(' ', $frameworks_text);
            }

            $frameworks_text = (string)$frameworks_text;

            if (strpos(strtoupper($frameworks_text), 'GRI') !== false) $frameworks['GRI']++;
            if (strpos(strtoupper($frameworks_text), 'ISSB') !== false) $frameworks['ISSB']++;
            if (strpos(strtoupper($frameworks_text), 'COSO') !== false) $frameworks['COSO']++;
            if (strpos(strtoupper($frameworks_text), 'TCFD') !== false) $frameworks['TCFD']++;
            if (strpos(strtoupper($frameworks_text), 'ECOVADIS') !== false) $frameworks['EcoVadis']++;
        }

        return array_map(fn($name, $count) => [
            'name' => $name,
            'count' => $count,
            'coverage_percentage' => count($items) > 0 ? round(($count / count($items)) * 100, 1) : 0,
        ], array_keys($frameworks), array_values($frameworks));
    }

    /**
     * Helper: Filter by source
     */
    private function filterBySource(array $items, string $source): array
    {
        return array_values(array_filter($items, fn($i) => ($i['source'] ?? '') === $source));
    }

    /**
     * Helper: Filter by source and types
     */
    private function filterBySourceAndTypes(array $items, string $source, array $types): array
    {
        return array_values(array_filter($items, fn($i) =>
            ($i['source'] ?? '') === $source && in_array($i['type'] ?? '', $types, true)
        ));
    }

    /**
     * Helper: Filter by type
     */
    private function filterByType(array $items, string $type): array
    {
        return array_values(array_filter($items, fn($i) => ($i['type'] ?? '') === $type));
    }

    /**
     * Helper: Count answered items
     */
    private function countAnswered(array $items): int
    {
        return count(array_filter($items, fn($i) => ($i['answer_status'] ?? '') === 'answered'));
    }

    /**
     * Helper: Calculate average percentage
     */
    private function calculateAveragePercentage(array $items): float
    {
        $percentages = array_filter($items, fn($i) =>
            ($i['answer_status'] ?? '') === 'answered' &&
            ($i['answer_type'] ?? '') === 'percentage' &&
            is_numeric($i['answer'] ?? null)
        );

        if (empty($percentages)) return 0;

        $sum = array_sum(array_map(fn($i) => (float)$i['answer'], $percentages));
        return round($sum / count($percentages), 1);
    }

    /**
     * Helper: Build radar chart data
     */
    private function buildRadarData(array $domainStats): array
    {
        return array_map(fn($d) => [
            'domain' => $d['title'],
            'score' => $d['avg_score'],
            'code' => $d['code'],
        ], $domainStats);
    }

    /**
     * Helper: Build domain bar chart (percentages)
     */
    private function buildDomainBarPercent(array $domainStats): array
    {
        return array_map(fn($d) => [
            'domain' => $d['title'],
            'code' => $d['code'],
            'score' => $d['avg_score'],
        ], $domainStats);
    }

    /**
     * Helper: Build domain bar chart (counts)
     */
    private function buildDomainBarCount(array $domainStats): array
    {
        return array_map(fn($d) => [
            'domain' => $d['title'],
            'code' => $d['code'],
            'count' => $d['kpi_count'],
            'answered' => $d['answered'],
        ], $domainStats);
    }

    /**
     * Get current reporting period
     */
    private function getCurrentReportingPeriod(): string
    {
        $currentYear = date('Y');
        return (int)$currentYear . ' Annual Report';
    }

    /**
     * Interpret score
     */
    private function interpretScore(float $score): array
    {
        if ($score >= 85) {
            return ['level' => 'عالی', 'color' => '#059669', 'description' => 'عملکرد بسیار خوب'];
        } elseif ($score >= 70) {
            return ['level' => 'خوب', 'color' => '#2563eb', 'description' => 'عملکرد خوب'];
        } elseif ($score >= 55) {
            return ['level' => 'متوسط', 'color' => '#d97706', 'description' => 'عملکرد متوسط'];
        } else {
            return ['level' => 'نیاز به بهبود', 'color' => '#dc2626', 'description' => 'نیاز به بهبود فوری'];
        }
    }

    /**
     * Calculate trend (placeholder for historical comparison)
     */
    private function calculateTrend(): array
    {
        return [
            'direction' => 'stable', // up, down, stable
            'percentage_change' => 0,
            'compared_to' => 'last period',
        ];
    }
}
