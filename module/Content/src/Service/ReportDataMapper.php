<?php

namespace Content\Service;

class ReportDataMapper
{
    private const TYPE_DOMAIN = 'domain';
    private const TYPE_CONTROL = 'control';

    /**
     * Normalize flat list of raw items into structured domains and controls
     *
     * @param array $rawList Flat list of items with type=domain or type=control
     * @return array Normalized data structure ['domains' => ['slug' => data], 'controls' => ['parent_slug' => [data, ...]]]
     */
    public function normalize(array $rawList): array
    {
        $normalized = [
            'domains' => [],
            'controls' => [],
        ];

        foreach ($rawList as $item) {
            if (!isset($item['type'])) {
                continue;
            }

            if ($item['type'] === self::TYPE_DOMAIN) {
                $this->processDomain($item, $normalized);
            } elseif ($item['type'] === self::TYPE_CONTROL) {
                $this->processControl($item, $normalized);
            }
        }

        return $normalized;
    }

    /**
     * Get domains filtered by source
     *
     * @param string $source Environmental|Social|Governance source
     * @param array $normalized Normalized data from normalize()
     * @return array List of domains matching the source
     */
    public function getDomainsBySource(string $source, array $normalized): array
    {
        $result = [];
        $source = strtolower($source);

        foreach ($normalized['domains'] ?? [] as $slug => $domain) {
            if (isset($domain['source']) && strtolower($domain['source']) === $source) {
                $result[$slug] = $domain;
            }
        }

        return $result;
    }

    /**
     * Get controls belonging to a domain
     *
     * @param string $parentSlug Domain slug
     * @param array $normalized Normalized data from normalize()
     * @return array List of controls for the domain
     */
    public function getControlsByDomain(string $parentSlug, array $normalized): array
    {
        return $normalized['controls'][$parentSlug] ?? [];
    }

    /**
     * Get a specific control by slug and parent domain
     *
     * @param string $slug Control slug
     * @param string $parentSlug Domain slug
     * @param array $normalized Normalized data from normalize()
     * @return array|null The control data or null if not found
     */
    public function getControlBySlug(string $slug, string $parentSlug, array $normalized): ?array
    {
        $controls = $this->getControlsByDomain($parentSlug, $normalized);

        foreach ($controls as $control) {
            if (isset($control['slug']) && $control['slug'] === $slug) {
                return $control;
            }
        }

        return null;
    }

    /**
     * Process domain item and add to normalized structure
     */
    private function processDomain(array $item, array &$normalized): void
    {
        if (!isset($item['slug'])) {
            return;
        }

        $normalized['domains'][$item['slug']] = [
            'type' => $item['type'],
            'source' => $item['source'] ?? null,
            'slug' => $item['slug'],
            'title' => $item['title'] ?? null,
            'description' => $item['description'] ?? null,
            'code' => $item['code'] ?? null,
            'order' => $item['order'] ?? null,
        ];
    }

    /**
     * Process control item and add to normalized structure
     */
    private function processControl(array $item, array &$normalized): void
    {
        if (!isset($item['slug'], $item['parent_slug'])) {
            return;
        }

        $parentSlug = $item['parent_slug'];

        if (!isset($normalized['controls'][$parentSlug])) {
            $normalized['controls'][$parentSlug] = [];
        }

        $normalized['controls'][$parentSlug][] = [
            'type' => $item['type'],
            'parent_slug' => $item['parent_slug'],
            'slug' => $item['slug'],
            'title' => $item['title'] ?? null,
            'summary' => $item['summary'] ?? null,
            'answer' => $item['answer'] ?? null,
            'answer_unit' => $item['answer_unit'] ?? null,
            'answer_type' => $item['answer_type'] ?? null,
            'metric_code' => $item['metric_code'] ?? null,
            'kpi_code' => $item['kpi_code'] ?? null,
            'frameworks' => $item['frameworks'] ?? [],
        ];
    }
}
