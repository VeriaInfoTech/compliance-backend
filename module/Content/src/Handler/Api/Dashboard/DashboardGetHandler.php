<?php

namespace Content\Handler\Api\Dashboard;

use Content\Service\ItemService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DashboardGetHandler implements RequestHandlerInterface
{
    private const DEFAULT_SECTION = 'governance';

    protected ItemService $itemService;

    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $raw = $this->itemService->getItemList([
            'type' => ['domain','control'], // Get all
        ]);

        $items = $raw['data']['list'] ?? [];

        $dashboardData = [
            'last_updated' => '2026-05-30',
            'reporting_period' => '2024 Annual',
            'total_kpis' => count(array_filter($items, fn($i) => ($i['type'] ?? '') === 'control')),
        ];

        // === GOVERNANCE (Most Detailed) ===
        $governanceItems = $this->filterBySourceAndTypes($items, 'governance', ['domain', 'control']);
        $dashboardData['governance'] = $this->buildGovernanceDashboard($governanceItems);

        // === SOCIAL ===
        $socialItems = $this->filterBySource($items, 'social');
        $dashboardData['social'] = $this->buildSourceDashboard('social', $socialItems);

        // === ENVIRONMENTAL ===
        $environmentalItems = $this->filterBySource($items, 'environmental');
        $dashboardData['environmental'] = $this->buildSourceDashboard('environmental', $environmentalItems);

        return new JsonResponse([
            'result' => true,
            'data'   => $dashboardData,
            'error'  => [],
        ]);
    }

    private function filterBySource(array $items, string $source): array
    {
        return array_values(array_filter($items, fn($i) => ($i['source'] ?? '') === $source));
    }

    private function filterBySourceAndTypes(array $items, string $source, array $types): array
    {
        return array_values(array_filter($items, fn($i) =>
            ($i['source'] ?? '') === $source && in_array($i['type'] ?? '', $types, true)
        ));
    }

    // =============================================
    // GOVERNANCE DASHBOARD (Matches your HTML template)
    // =============================================
    private function buildGovernanceDashboard(array $items): array
    {
        $controls = $this->filterByType($items, 'control');
        $domains  = $this->filterByType($items, 'domain');

        $answered = array_filter($controls, fn($c) => ($c['answer_status'] ?? '') === 'answered');
        $unansweredCount = count($controls) - count($answered);

        $domainStats = $this->calculateDomainStats($controls, $domains);

        return [
            'summary' => [
                'total_kpis' => count($controls),
                'answered'   => count($answered),
                'unanswered' => $unansweredCount,
                'completion' => count($controls) ? round(count($answered) / count($controls) * 100, 1) : 0,
                'avg_score'  => $this->calculateAveragePercentage($controls),
            ],
            'domains' => $domainStats,
            'all_kpis' => $this->formatAllKPIs($controls),
            'framework_coverage' => $this->buildFrameworkCoverage(),
            'charts' => [
                'radar_data' => $this->buildRadarData($domainStats),
                'domain_bar_percent' => $this->buildDomainBarPercent($domainStats),
                'domain_bar_count' => $this->buildDomainBarCount($domainStats),
            ],
            'detailed_sections' => $this->buildDetailedDomainSections($controls)
        ];
    }

    private function buildSourceDashboard(string $source, array $items): array
    {
        $controls = $this->filterByType($items, 'control');
        $domains  = $this->filterByType($items, 'domain');

        return [
            'summary' => [
                'total_kpis' => count($controls),
                'answered'   => $this->countAnswered($controls),
                'completion' => $this->calculateCompletion($controls),
                'avg_score'  => $this->calculateAveragePercentage($controls),
            ],
            'domains' => $this->calculateDomainStats($controls, $domains),
            'all_kpis' => $this->formatAllKPIs($controls),
        ];
    }

    // Helper Methods
    private function filterByType(array $items, string $type): array
    {
        return array_values(array_filter($items, fn($i) => ($i['type'] ?? '') === $type));
    }

    private function countAnswered(array $controls): int
    {
        return count(array_filter($controls, fn($c) => ($c['answer_status'] ?? '') === 'answered'));
    }

    private function calculateCompletion(array $controls): float
    {
        return count($controls) ? round($this->countAnswered($controls) / count($controls) * 100, 1) : 0;
    }

    private function calculateAveragePercentage(array $controls): float
    {
        $percentages = array_filter($controls, fn($c) =>
            ($c['answer_status'] ?? '') === 'answered' &&
            ($c['answer_type'] ?? '') === 'percentage' &&
            is_numeric($c['answer'] ?? null)
        );

        if (empty($percentages)) return 0;

        $sum = array_sum(array_map(fn($c) => (float)$c['answer'], $percentages));
        return round($sum / count($percentages), 1);
    }

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
                'code' => $domain['code'],
                'title' => $domain['title'],
                'slug' => $slug,
                'order' => $domain['order'],
                'kpi_count' => count($domainControls),
                'answered' => $this->countAnswered($domainControls),
                'avg_score' => $this->calculateAveragePercentage($domainControls),
                'kpis' => $this->formatDomainKPIs($domainControls)
            ];
        }

        usort($result, fn($a, $b) => $a['order'] <=> $b['order']);
        return $result;
    }

    private function formatAllKPIs(array $controls): array
    {
        return array_map(fn($c) => [
            'code' => $c['metric_code'] ?? $c['kpi_code'] ?? '',
            'title' => $c['title'],
            'domain' => $c['domain_title'] ?? '',
            'value' => $c['answer'],
            'unit' => $c['answer_unit'] ?? '',
            'type' => $c['answer_type'] ?? '',
            'status' => $c['answer_status'] ?? 'unanswered',
        ], $controls);
    }

    private function formatDomainKPIs(array $controls): array
    {
        return array_map(fn($c) => [
            'code' => $c['metric_code'] ?? '',
            'title' => $c['title'],
            'value' => $c['answer'],
            'unit' => $c['answer_unit'] ?? '',
            'status' => $c['answer_status'] ?? 'unanswered',
        ], $controls);
    }

    private function buildRadarData(array $domainStats): array
    {
        return array_map(fn($d) => [
            'domain' => $d['title'],
            'score' => $d['avg_score']
        ], $domainStats);
    }

    private function buildDomainBarPercent(array $domainStats): array
    {
        return array_map(fn($d) => [
            'domain' => $d['title'],
            'score' => $d['avg_score']
        ], $domainStats);
    }

    private function buildDomainBarCount(array $domainStats): array
    {
        return array_map(fn($d) => [
            'domain' => $d['title'],
            'count' => $d['kpi_count']
        ], $domainStats);
    }

    private function buildFrameworkCoverage(): array
    {
        return [
            ['name' => 'GRI Standards', 'count' => 84],
            ['name' => 'ISSB', 'count' => 84],
            ['name' => 'COSO ERM', 'count' => 84],
            ['name' => 'EcoVadis', 'count' => 84],
            ['name' => 'TCFD', 'count' => 18],
        ];
    }

    private function buildDetailedDomainSections(array $controls): array
    {
        $grouped = [];
        foreach ($controls as $c) {
            $domain = $c['domain_title'] ?? 'Other';
            $grouped[$domain][] = $c;
        }

        $sections = [];
        foreach ($grouped as $domainName => $items) {
            $sections[] = [
                'domain' => $domainName,
                'kpis' => array_slice(array_map(fn($c) => [
                    'code' => $c['metric_code'] ?? '',
                    'title' => $c['title'],
                    'value' => $c['answer'],
                    'unit' => $c['answer_unit'] ?? '',
                    'status' => $c['answer_status'] ?? ''
                ], $items), 0, 6)
            ];
        }

        return $sections;
    }
}