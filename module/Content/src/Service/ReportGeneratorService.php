<?php

namespace Content\Service;

use InvalidArgumentException;
use DateTime;

/**
 * ReportGeneratorService
 *
 * Produces a structured, PDF-ready ESG report array from normalized input.
 * - Brings nearly all answered controls into key_figures
 * - Groups controls by source and domain (parent_slug)
 * - Generates strong data-driven narratives per subsection
 *
 * Notes:
 * - This class intentionally treats any non-empty answer as "answered" (including zero numeric values).
 * - If richer mapping (answer_status, dashboard_usage, etc.) is desired, extend ReportDataMapper::processControl to include those fields.
 */
class ReportGeneratorService
{
    private ReportDataMapper $dataMapper;
    private EnvironmentalSectionBuilder $environmentalBuilder;
    private SocialSectionBuilder $socialBuilder;
    private GovernanceSectionBuilder $governanceBuilder;

    public function __construct(
        ReportDataMapper $dataMapper,
        EnvironmentalSectionBuilder $environmentalBuilder,
        SocialSectionBuilder $socialBuilder,
        GovernanceSectionBuilder $governanceBuilder
    ) {
        $this->dataMapper = $dataMapper;
        $this->environmentalBuilder = $environmentalBuilder;
        $this->socialBuilder = $socialBuilder;
        $this->governanceBuilder = $governanceBuilder;
    }

    /**
     * Generate the full report array
     * @param array $rawJson
     * @return array
     */
    public function generate(array $rawJson): array
    {
        $list = $rawJson['data']['list'] ?? $rawJson['list'] ?? null;

        if (!is_array($list) || empty($list)) {
            throw new InvalidArgumentException('Invalid or empty data list provided');
        }

        $normalized = $this->dataMapper->normalize($list);

        // build report sections
        $report = [
            'meta' => [
                'generated_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'reporting_year' => (int) date('Y'),
            ],
            'key_figures' => $this->extractKeyFigures($normalized),
            'environmental' => $this->environmentalBuilder->build($normalized),
            'social' => $this->socialBuilder->build($normalized),
            'governance' => $this->governanceBuilder->build($normalized),
            'narratives' => $this->buildNarratives($normalized),
        ];

        // More precise verification: count answered controls from the normalized flat list and check coverage
        $actualAnswered = 0;
        $missingSlugs = [];

        $flatControls = $normalized['controls_flat'] ?? $normalized['all_controls'] ?? $this->getAllControls($normalized);

        // Build a quick lookup of key_figures by unique key (prefer id, fallback to parent::slug)
        $kfLookup = [];
        foreach ($report['key_figures'] as $kf) {
            $kfId = $kf['id'] ?? null;
            if ($kfId) {
                $kfLookup['id:' . $kfId] = true;
            }
            $kfLookup[($kf['parent_slug'] ?? 'ungrouped') . '::' . ($kf['slug'] ?? '')] = true;
            // also store slug-only presence to be forgiving
            $kfLookup['slug:' . ($kf['slug'] ?? '')] = true;
        }

        foreach ($flatControls as $control) {
            if (($control['answer_status'] ?? '') === 'answered') {
                $actualAnswered++;

                $controlKeyId = isset($control['id']) && $control['id'] !== null ? ('id:' . $control['id']) : null;
                $controlKeyPS = ($control['parent_slug'] ?? 'ungrouped') . '::' . ($control['slug'] ?? '');
                $controlKeySlug = 'slug:' . ($control['slug'] ?? '');

                $exists = false;
                if ($controlKeyId && isset($kfLookup[$controlKeyId])) {
                    $exists = true;
                } elseif (isset($kfLookup[$controlKeyPS])) {
                    $exists = true;
                } elseif (isset($kfLookup[$controlKeySlug])) {
                    $exists = true;
                }

                if (!$exists) {
                    $missingSlugs[] = ($control['slug'] ?? '') . (isset($control['parent_slug']) ? ' (' . $control['parent_slug'] . ')' : '');
                }
            }
        }

        // Populate meta with comprehensive counts
        $totalControls = $this->countTotalControls($normalized);
        $totalDomains = count($normalized['domains'] ?? []);

        $report['meta']['total_items'] = count($list);
        $report['meta']['total_domains'] = $totalDomains;
        $report['meta']['total_controls'] = $totalControls;
        $report['meta']['answered_controls'] = $actualAnswered;

        // Per section counts
        $report['meta']['sections'] = [
            'environmental' => $this->getSectionStats('environmental', $normalized),
            'social' => $this->getSectionStats('social', $normalized),
            'governance' => $this->getSectionStats('governance', $normalized),
        ];

        if (empty($missingSlugs)) {
            $report['meta']['note'] = "از مجموع {$totalControls} کنترل، {$actualAnswered} کنترل answered پوشش داده شد.";
        } else {
            $report['meta']['note'] = "هشدار: {$actualAnswered} کنترل answered وجود داشت اما " . count($missingSlugs) . " مورد در key_figures قرار نگرفت. Missing: " . implode(', ', $missingSlugs);
        }

        return $report;
    }

    /**
     * Count controls that appear to be answered (non-null, non-empty string)
     */
    private function countAnsweredControls(array $normalized): int
    {
        $count = 0;
        foreach ($this->getAllControls($normalized) as $control) {
            if ($this->isAnsweredControl($control)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Extract a rich set of key figures from almost all answered controls.
     * Returns an array of figures including formatted values and chart suggestions.
     */
    private function extractKeyFigures(array $normalized): array
    {
        // Build map keyed by control id when available, otherwise by parent_slug::slug
        $map = [];

        foreach ($this->getAllControls($normalized) as $control) {
            if (($control['answer_status'] ?? '') !== 'answered') {
                continue;
            }

            $domain = $normalized['domains'][$control['parent_slug']] ?? null;
            $source = $domain['source'] ?? ($control['source'] ?? 'unknown');

            $formatted = $this->formatAnswer($control['answer'] ?? null, $control['answer_unit'] ?? null, $control['answer_type'] ?? null);
            $chart = $this->suggestChart($control);
            $importance = $this->computeSectionImportance($control, $domain);

            $uniqueKey = null;
            if (!empty($control['id'])) {
                $uniqueKey = 'id:' . $control['id'];
            } else {
                $uniqueKey = ($control['parent_slug'] ?? 'ungrouped') . '::' . ($control['slug'] ?? '');
            }

            $map[$uniqueKey] = [
                'id' => $control['id'] ?? null,
                'unique_key' => $uniqueKey,
                'slug' => $control['slug'] ?? null,
                'title' => $control['title'] ?? $control['summary'] ?? null,
                'parent_slug' => $control['parent_slug'] ?? null,
                'domain_slug' => $control['parent_slug'] ?? null,
                'domain_title' => $domain['title'] ?? null,
                'source' => $source,
                'answer_raw' => $control['answer'] ?? null,
                'formatted_answer' => $formatted,
                'answer_unit' => $control['answer_unit'] ?? null,
                'answer_type' => $control['answer_type'] ?? null,
                'metric_code' => $control['metric_code'] ?? null,
                'kpi_code' => $control['kpi_code'] ?? null,
                'chart_suggestion' => $chart,
                'section_importance' => $importance,
            ];
        }

        // Preserve values and sort
        $figures = array_values($map);

        usort($figures, function ($a, $b) {
            $ai = $a['section_importance'] ?? 0;
            $bi = $b['section_importance'] ?? 0;
            if ($ai === $bi) {
                return strcmp($a['domain_slug'] ?? '', $b['domain_slug'] ?? '');
            }
            return $bi <=> $ai;
        });

        return $figures;
    }

    /**
     * Compute an integer importance for a control to prioritise key figures
     */
    private function computeSectionImportance(array $control, ?array $domain = null): int
    {
        $high = [
            'greenhouse-gas-emissions',
            'energy-resource-management',
            'water-management',
            'waste-management-circular-economy',
            'corporate-governance-structure',
            'workforce-structure-demographics',
            'risk-management',
        ];

        if ($domain && in_array($domain['slug'] ?? ($control['parent_slug'] ?? ''), $high, true)) {
            return 10;
        }

        $slug = $control['slug'] ?? '';
        if (stripos($slug, 'total') !== false || stripos($slug, 'total-') !== false || stripos($slug, 'count') !== false) {
            return 7;
        }

        if (($control['answer_type'] ?? '') === 'percentage') {
            return 5;
        }

        return 3;
    }

    /**
     * Group normalized controls by source (environmental, social, governance)
     * Returns ['environmental' => [domainSlug=>controls], ...]
     */
    private function groupBySource(array $normalized): array
    {
        $result = [
            'environmental' => [],
            'social' => [],
            'governance' => [],
        ];

        foreach ($normalized['domains'] ?? [] as $slug => $domain) {
            $src = strtolower($domain['source'] ?? '');
            if (!isset($result[$src])) {
                $result[$src] = [];
            }
            $result[$src][$slug] = $this->dataMapper->getControlsByDomain($slug, $normalized);
        }

        return $result;
    }

    /**
     * Build strong, data-driven narratives for the full report
     */
    private function buildNarratives(array $normalized): array
    {
        $year = (int) date('Y');

        return [
            'about_report' => [
                'title' => 'درباره این گزارش',
                'body' => $this->buildReportIntro($year),
            ],
            'environmental' => $this->buildSectionNarratives('environmental', $normalized),
            'social' => $this->buildSectionNarratives('social', $normalized),
            'governance' => $this->buildSectionNarratives('governance', $normalized),
            'report_conclusion' => [
                'title' => 'نتیجه‌گیری کلی',
                'body' => $this->buildReportConclusion($normalized),
            ],
        ];
    }

    /**
     * Paragraph-style intro using year and foundation
     */
    private function buildReportIntro(int $year): string
    {
        return "گزارش پایداری سال {$year} تصویر یکپارچه‌ای از عملکرد سازمان در حوزه‌های محیط‌زیست، اجتماعی و حاکمیتی است. " .
            "این گزارش بر مبنای داده‌های ثبت‌شده در سامانه فراهم شده و نقاط قوت، ریسک‌ها و پیشنهادهای کلیدی برای دوره آینده را برجسته می‌کند.";
    }

    /**
     * Build narratives for a top-level source (environmental/social/governance)
     */
    private function buildSectionNarratives(string $source, array $normalized): array
    {
        $domains = $this->dataMapper->getDomainsBySource($source, $normalized);

        $answeredControlsCount = 0;
        $controlsPool = [];
        foreach ($domains as $slug => $domain) {
            $controls = $this->dataMapper->getControlsByDomain($slug, $normalized);
            foreach ($controls as $c) {
                if ($this->isAnsweredControl($c)) {
                    $answeredControlsCount++;
                    $controlsPool[] = $c + ['parent_slug' => $slug];
                }
            }
        }

        $intro = sprintf('بخش %s: شامل %d حوزه با %d شاخص پاسخ‌شده است. در ادامه مهم‌ترین دستاوردها و شاخص‌های کلیدی این حوزه ارائه می‌شود.',
            ucfirst($source), count($domains), $answeredControlsCount
        );

        // Build per-domain narratives
        $domainNarratives = [];
        foreach ($domains as $slug => $domain) {
            $controls = $this->dataMapper->getControlsByDomain($slug, $normalized);
            $domNarr = $this->buildDomainNarrative($domain, $controls);
            if ($domNarr) {
                $domainNarratives[$slug] = $domNarr;
            }
        }

        // summary recommendations (chart types)
        $chartRecommendations = [];
        foreach ($controlsPool as $c) {
            $chart = $this->suggestChart($c);
            if ($chart['chart_type'] ?? null) {
                $chartRecommendations[$chart['chart_type']] = ($chartRecommendations[$chart['chart_type']] ?? 0) + 1;
            }
        }

        arsort($chartRecommendations);
        $topCharts = array_keys(array_slice($chartRecommendations, 0, 3, true));

        $recommendationSentence = count($topCharts)
            ? ('پیشنهاد نمودارهای پیشنهادی برای ارائه در گزارش: ' . implode(', ', $topCharts) . '.')
            : '';

        return [
            'intro' => $intro,
            'domains' => $domainNarratives,
            'chart_recommendations' => $topCharts,
            'recommendation_note' => $recommendationSentence,
        ];
    }

    /**
     * Build a narrative paragraph for a domain using its answered controls
     */
    private function buildDomainNarrative(array $domain, array $controls): ?array
    {
        $answered = array_filter($controls, function ($c) {
            return $this->isAnsweredControl($c);
        });

        if (empty($answered)) {
            return null;
        }

        $title = $domain['title'] ?? $domain['slug'] ?? 'نامشخص';
        $description = $domain['description'] ?? '';

        // Prepare summary sentences
        $sentences = [];
        $sentences[] = "{$title}: {$description}";

        // Pick up to 4 most relevant controls to mention (by computed importance and numeric values)
        usort($answered, function ($a, $b) use ($domain) {
            $ia = $this->computeSectionImportance($a, $domain);
            $ib = $this->computeSectionImportance($b, $domain);
            if ($ia === $ib) {
                $va = is_numeric($a['answer'] ?? null) ? (float)$a['answer'] : 0;
                $vb = is_numeric($b['answer'] ?? null) ? (float)$b['answer'] : 0;
                return $vb <=> $va;
            }
            return $ib <=> $ia;
        });

        $examples = array_slice($answered, 0, 6);

        foreach ($examples as $c) {
            $formatted = $this->formatAnswer($c['answer'] ?? null, $c['answer_unit'] ?? null, $c['answer_type'] ?? null);
            $titleCtrl = $c['title'] ?? $c['summary'] ?? $c['slug'];
            $sentences[] = "شاخص «{$titleCtrl}» برابر {$formatted} ثبت شده است.";
        }

        // Domain-level insight: if numeric totals make sense (e.g., greenhouse gases or water)
        $insight = $this->domainInsight($domain, $controls);
        if ($insight) {
            $sentences[] = $insight;
        }

        // closing recommendation for the domain
        $sentences[] = 'پیشنهاد: پایش دوره‌ای این شاخص‌ها، مشخص‌سازی اهداف کوتاه‌مدت و استفاده از نمودارهای روند برای نمایش تغییرات توصیه می‌شود.';

        return [
            'title' => $title,
            'body' => implode(' ', $sentences),
        ];
    }

    /**
     * Produce a short insight sentence for domains with special metrics
     */
    private function domainInsight(array $domain, array $controls): ?string
    {
        $slug = $domain['slug'] ?? '';

        if ($slug === 'greenhouse-gas-emissions') {
            // sum numeric answers whose units look like tons
            $total = 0.0;
            $scopes = [];
            foreach ($controls as $c) {
                if (!$this->isAnsweredControl($c)) {
                    continue;
                }
                $u = $c['answer_unit'] ?? '';
                if (is_numeric($c['answer']) && in_array($u, ['ton', 't', 'ton_co2e', 'co2e', 'ton_co2'], true)) {
                    $total += (float)$c['answer'];
                }
                if (stripos($c['slug'] ?? '', 'scope') !== false) {
                    $scopes[$c['slug']] = $c['answer'];
                }
            }

            if ($total > 0) {
                $fmtTotal = $this->formatAnswer($total, 'ton', 'number');
                $scopeParts = [];
                foreach ($scopes as $k => $v) {
                    $scopeParts[] = "{$k}: {$this->formatAnswer($v, 'ton', 'number')}";
                }
                $scopeText = $scopeParts ? (' (' . implode(', ', $scopeParts) . ')') : '';
                return "در مجموع، انتشارات گزارش‌شده معادل {$fmtTotal} است{$scopeText}. کاهش یا تثبیت این سطح در سال‌های آتی از اولویت‌های گزارش پیشنهاد می‌شود.";
            }
        }

        if ($slug === 'water-management') {
            $total = $this->getNumericFromControls($controls, ['total-water-withdrawal-consumption', 'total-water-withdrawal', 'total-water']);
            if ($total !== null) {
                return "کل برداشت/مصرف آب گزارش‌شده برابر {$this->formatAnswer($total, 'm3', 'number')} است؛ تمرکز بر بازچرخانی و مدیریت مصرف توصیه می‌شود.";
            }
        }

        if ($slug === 'waste-management-circular-economy') {
            $totalWaste = $this->getNumericFromControls($controls, ['total-waste-generated', 'total-waste']);
            if ($totalWaste !== null) {
                return "کل پسماند تولیدی برابر {$this->formatAnswer($totalWaste, 'ton', 'number')} است؛ افزایش نرخ بازیافت و کاهش ارسال به دفع نهایی می‌تواند تأثیر قابل‌توجهی داشته باشد.";
            }
        }

        if ($slug === 'workforce-structure-demographics') {
            $employees = $this->getNumericFromControls($controls, ['total-employees-count', 'employees-count']);
            if ($employees !== null) {
                return "نیروی انسانی سازمان شامل {$this->formatAnswer($employees, 'employee', 'number')} نفر است؛ سرمایه‌گذاری در توسعه مهارت و برنامه‌های نگهداشت توصیه می‌شود.";
            }
        }

        return null;
    }

    /**
     * Helper: find first numeric control by a list of candidate slugs
     */
    private function getNumericFromControls(array $controls, array $candidateSlugs)
    {
        foreach ($candidateSlugs as $s) {
            foreach ($controls as $c) {
                if (($c['slug'] ?? '') === $s && is_numeric($c['answer'] ?? null)) {
                    return (float)$c['answer'];
                }
            }
        }
        return null;
    }

    /**
     * Format numeric/percentage answers into human readable strings
     */
    private function formatAnswer($answer, ?string $unit = null, ?string $type = null): string
    {
        if ($answer === null || $answer === '') {
            return 'ناموجود';
        }

        // Keep numeric type if possible
        if (is_numeric($answer)) {
            $isFloat = (floor($answer) != $answer);
            $decimals = $isFloat ? 1 : 0;
            $formatted = number_format((float) $answer, $decimals, '.', ',');
        } else {
            $formatted = (string) $answer;
        }

        $unit = $unit ?? strtolower($type ?? '');

        // Map common unit codes to friendly Persian labels
        $map = [
            'percent' => '%',
            'percentage' => '%',
            'm3' => 'مترمکعب',
            'm3_per_unit' => 'مترمکعب/واحد',
            'ton' => 'تن',
            'employee' => 'نفر',
            'count' => 'مورد',
            'day' => 'روز',
        ];

        $label = $map[$unit] ?? $unit;

        if (in_array($unit, ['percent', 'percentage', '%'], true)) {
            return $formatted . '%';
        }

        if (!empty($label)) {
            return $formatted . ' ' . $label;
        }

        return $formatted;
    }

    /**
     * Suggest chart type for a control — used to advise visuals in the PDF
     */
    private function suggestChart(array $control): array
    {
        $unit = strtolower($control['answer_unit'] ?? '') ;
        $type = strtolower($control['answer_type'] ?? '');
        $slug = $control['slug'] ?? '';

        // Prefer gauge/donut for single-percentage KPIs
        if ($type === 'percentage' || in_array($unit, ['percent', '%'], true)) {
            if (stripos($slug, 'share') !== false || stripos($slug, 'ratio') !== false || stripos($slug, 'distribution') !== false) {
                return ['chart_type' => 'pie', 'reason' => 'Distribution/share indicator'];
            }
            return ['chart_type' => 'gauge', 'reason' => 'Single KPI percentage'];
        }

        // For totals and counts, bar or line (if time-series available)
        if (in_array($unit, ['ton', 'm3', 'count', 'employee', 'day'], true) || $type === 'number') {
            // intensity or trend indicators -> line
            if (stripos($slug, 'intensity') !== false || stripos($slug, 'trend') !== false) {
                return ['chart_type' => 'line', 'reason' => 'Trend/intensity metric'];
            }
            return ['chart_type' => 'bar', 'reason' => 'Absolute/count metric'];
        }

        // Fallback
        return ['chart_type' => 'table', 'reason' => 'Tabular presentation recommended'];
    }

    /**
     * Check whether control is considered answered. Treat numeric zero as valid.
     */
    private function isAnsweredControl(array $control): bool
    {
        if (!array_key_exists('answer', $control)) {
            return false;
        }

        $a = $control['answer'];

        if ($a === null) {
            return false;
        }

        if ($a === '') {
            return false;
        }

        // numeric zero and strings like "0" are valid answered values
        return true;
    }

    /**
     * Get a control answer value by parent and slug. Returns null when missing.
     * Unlike previous implementation this keeps zero numeric answers.
     */
    private function getControlValue(array $normalized, string $parentSlug, string $controlSlug): ?string
    {
        $control = $this->dataMapper->getControlBySlug($controlSlug, $parentSlug, $normalized);
        if (!$control || !array_key_exists('answer', $control)) {
            return null;
        }

        $answer = $control['answer'];

        if ($answer === null || $answer === '') {
            return null;
        }

        return (string)$answer;
    }

    /**
     * Return a flat list of all controls from normalized structure
     */
    private function getAllControls(array $normalized): array
    {
        // If mapper provided a flat controls list, use it directly (preserves original order and fields)
        if (!empty($normalized['controls_flat']) && is_array($normalized['controls_flat'])) {
            return $normalized['controls_flat'];
        }

        $all = [];
        foreach ($normalized['controls'] ?? [] as $parent => $controls) {
            foreach ($controls as $c) {
                // ensure parent_slug is always present
                if (!isset($c['parent_slug'])) {
                    $c['parent_slug'] = $parent;
                }
                $all[] = $c;
            }
        }
        return $all;
    }

    /**
     * Get statistics for a top-level section (environmental/social/governance)
     */
    private function getSectionStats(string $source, array $normalized): array
    {
        $domains = $this->dataMapper->getDomainsBySource($source, $normalized);
        $domainCount = count($domains);
        $answeredCount = 0;

        foreach ($domains as $slug => $domain) {
            $controls = $this->dataMapper->getControlsByDomain($slug, $normalized);
            foreach ($controls as $c) {
                if ($this->isAnsweredControl($c)) {
                    $answeredCount++;
                }
            }
        }

        return [
            'domains' => $domainCount,
            'answered_controls' => $answeredCount,
        ];
    }


    /**
     * Count total controls (items with type='control') in the normalized data
     * Counts across all groups and flat structures
     */
    private function countTotalControls(array $normalized): int
    {
        $count = 0;
        $flatControls = $normalized['controls_flat'] ?? $normalized['all_controls'] ?? [];
        
        if (!empty($flatControls)) {
            // If we have flat structure, use it
            foreach ($flatControls as $control) {
                if (($control['type'] ?? '') === 'control' || isset($control['answer_status'])) {
                    $count++;
                }
            }
        } else {
            // Fallback to grouped structure
            foreach ($this->getAllControls($normalized) as $control) {
                if (($control['type'] ?? '') === 'control' || isset($control['answer_status'])) {
                    $count++;
                }
            }
        }
        
        return $count;
    }

    /**
     * Build final, human-friendly conclusion using presence/absence of major signals
     */
    private function buildReportConclusion(array $normalized): string
    {
        $answered = $this->countAnsweredControls($normalized);
        $total = 0;
        foreach ($normalized['controls'] ?? [] as $p => $c) {
            $total += count($c);
        }

        $coverage = $total > 0 ? round($answered / $total * 100) : 0;

        return "گزارش حاضر شامل {$answered} شاخص پاسخ‌شده از مجموعه {$total} شاخص است (پوشش داده: {$coverage}%). " .
            "پیشنهاد می‌شود اهداف کمی برای مهم‌ترین شاخص‌های محیط‌زیستی، اجتماعی و حاکمیتی تدوین شده و پیگیری دوره‌ای با استفاده از نمودارهای روند انجام شود.";
    }
}

