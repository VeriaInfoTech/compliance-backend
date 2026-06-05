<?php

namespace Content\Service;

class EnvironmentalSectionBuilder implements SectionBuilderInterface
{
    private ReportDataMapper $dataMapper;

    private const DOMAIN_MAPPING = [
        'ghg' => 'greenhouse-gas-emissions',
        'water' => 'water-management',
        'waste' => 'waste-management-circular-economy',
        'energy' => 'energy-resource-management',
    ];

    private const CLIMATE_PARENT_SLUG = 'climate-change-strategy';

    public function __construct(ReportDataMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    public function build(array $normalized): array
    {
        return [
            'climate' => $this->getControls(self::CLIMATE_PARENT_SLUG, $normalized),
            'ghg' => $this->getControls(self::DOMAIN_MAPPING['ghg'], $normalized),
            'water' => $this->getControls(self::DOMAIN_MAPPING['water'], $normalized),
            'waste' => $this->getControls(self::DOMAIN_MAPPING['waste'], $normalized),
            'energy' => $this->getControls(self::DOMAIN_MAPPING['energy'], $normalized),
        ];
    }

    private function getControls(string $parentSlug, array $normalized): ?array
    {
        $controls = $this->dataMapper->getControlsByDomain($parentSlug, $normalized);
        return !empty($controls) ? $controls : null;
    }
}
