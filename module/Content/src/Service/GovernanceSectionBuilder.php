<?php

namespace Content\Service;

class GovernanceSectionBuilder implements SectionBuilderInterface
{
    private ReportDataMapper $dataMapper;

    private const DOMAIN_MAPPING = [
        'board' => 'corporate-governance-structure',
        'ethics' => 'ethics-compliance',
        'compliance' => 'regulatory-compliance',
    ];

    public function __construct(ReportDataMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    public function build(array $normalized): array
    {
        return [
            'board' => $this->getControls(self::DOMAIN_MAPPING['board'], $normalized),
            'ethics' => $this->getControls(self::DOMAIN_MAPPING['ethics'], $normalized),
            'compliance' => $this->getControls(self::DOMAIN_MAPPING['compliance'], $normalized),
        ];
    }

    private function getControls(string $parentSlug, array $normalized): ?array
    {
        $controls = $this->dataMapper->getControlsByDomain($parentSlug, $normalized);
        return !empty($controls) ? $controls : null;
    }
}
