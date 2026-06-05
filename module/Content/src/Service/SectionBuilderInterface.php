<?php

namespace Content\Service;

interface SectionBuilderInterface
{
    /**
     * Build section data from normalized report data
     *
     * @param array $normalized Normalized data from ReportDataMapper::normalize()
     * @return array Section data with controls grouped by categories
     */
    public function build(array $normalized): array;
}
