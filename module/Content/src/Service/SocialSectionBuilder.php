<?php

namespace Content\Service;

class SocialSectionBuilder implements SectionBuilderInterface
{
    private ReportDataMapper $dataMapper;

    private const DOMAIN_MAPPING = [
        'workforce' => 'workforce-structure',
        'dei' => 'diversity-equity-inclusion',
        'health_safety' => 'health-safety-wellbeing',
    ];

    public function __construct(ReportDataMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    public function build(array $normalized): array
    {
        return [
            'workforce' => $this->getControls(self::DOMAIN_MAPPING['workforce'], $normalized),
            'dei' => $this->getControls(self::DOMAIN_MAPPING['dei'], $normalized),
            'health_safety' => $this->getControls(self::DOMAIN_MAPPING['health_safety'], $normalized),
        ];
    }

    private function getControls(string $parentSlug, array $normalized): ?array
    {
        $controls = $this->dataMapper->getControlsByDomain($parentSlug, $normalized);
        return !empty($controls) ? $controls : null;
    }
}
