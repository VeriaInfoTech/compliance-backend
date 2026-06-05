<?php

namespace Content\Service;

use InvalidArgumentException;
use DateTime;

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
     * Generate a comprehensive report from raw JSON data
     *
     * @param array $rawJson Raw data containing ['data']['list']
     * @return array Structured report with meta, key figures, and sections
     * @throws InvalidArgumentException If rawJson['data']['list'] is empty or invalid
     */
    public function generate(array $rawJson): array
    {
        $list = $rawJson['data']['list'] ?? null;

        if (!is_array($list) || empty($list)) {
            throw new InvalidArgumentException('Invalid or empty data list provided');
        }

        $normalized = $this->dataMapper->normalize($list);

        return [
            'meta' => [
                'generated_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'reporting_year' => (int) date('Y'),
            ],
            'key_figures' => $this->extractKeyFigures($normalized),
            'environmental' => $this->environmentalBuilder->build($normalized),
            'social' => $this->socialBuilder->build($normalized),
            'governance' => $this->governanceBuilder->build($normalized),
        ];
    }

    /**
     * Extract key figures (controls with answer_type=number) from all domains
     */
    private function extractKeyFigures(array $normalized): array
    {
        $keyFigures = [];

        foreach ($normalized['controls'] as $parentSlug => $controls) {
            foreach ($controls as $control) {
                if (isset($control['answer_type']) && $control['answer_type'] === 'number') {
                    $keyFigures[] = [
                        'slug' => $control['slug'] ?? null,
                        'title' => $control['title'] ?? null,
                        'parent_slug' => $control['parent_slug'] ?? null,
                        'answer' => $control['answer'] ?? null,
                        'answer_unit' => $control['answer_unit'] ?? null,
                        'metric_code' => $control['metric_code'] ?? null,
                        'kpi_code' => $control['kpi_code'] ?? null,
                    ];
                }
            }
        }

        return $keyFigures;
    }
}
