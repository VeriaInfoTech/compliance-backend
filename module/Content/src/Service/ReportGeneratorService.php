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
     * @param array $rawJson Raw data containing ['data']['list'] or ['list']
     * @return array Structured report with meta, key figures, sections, and narratives
     * @throws InvalidArgumentException If rawJson['data']['list'] is empty or invalid
     */
    public function generate(array $rawJson): array
    {
        // Support multiple input formats
        $list = $rawJson['data']['list'] ?? $rawJson['list'] ?? null;

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
            'narratives' => $this->buildNarratives($normalized),
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

    /**
     * Build dynamic narratives from normalized data
     * All values are extracted from controls, no hardcoded numbers
     */
    private function buildNarratives(array $normalized): array
    {
        $year = (int) date('Y');
        
        return [
            'report_intro' => $this->buildReportIntro($year),
            'environmental' => $this->buildEnvironmentalNarratives($normalized),
            'social' => $this->buildSocialNarratives($normalized),
            'governance' => $this->buildGovernanceNarratives($normalized),
            'report_conclusion' => $this->buildReportConclusion(),
        ];
    }

    /**
     * Build report introduction narrative
     */
    private function buildReportIntro(int $year): string
    {
        return "گزارش پایداری سال {$year} حاضر، توصیف جامعی از عملکرد سازمان در حوزه‌های محیط‌زیست، اجتماعی و حاکمیتی (ESG) است. " .
               "این گزارش بر اساس استاندارهای بین‌المللی تهیه شده و شفافیت کامل در ارائه داده‌ها را منعکس می‌کند.";
    }

    /**
     * Build environmental section narratives
     */
    private function buildEnvironmentalNarratives(array $normalized): array
    {
        $narratives = [
            'intro' => 'سازمان در حوزه‌های انرژی، منابع آبی، مدیریت پسماند و تغییرات اقلیمی توجه ویژه داشته است. ' .
                      'اقدامات محسوسی برای کاهش تأثیرات محیط‌زیستی و ارتقاء سطح پایداری انجام گرفته است.',
        ];

        // GHG Emissions narrative
        if ($ghgNarrative = $this->buildGHGNarrative($normalized)) {
            $narratives['ghg'] = $ghgNarrative;
        }

        // Energy narrative
        if ($energyNarrative = $this->buildEnergyNarrative($normalized)) {
            $narratives['energy'] = $energyNarrative;
        }

        // Water narrative
        if ($waterNarrative = $this->buildWaterNarrative($normalized)) {
            $narratives['water'] = $waterNarrative;
        }

        // Waste narrative
        if ($wasteNarrative = $this->buildWasteNarrative($normalized)) {
            $narratives['waste'] = $wasteNarrative;
        }

        $narratives['conclusion'] = 'عملکرد محیط‌زیستی سازمان بیانگر تعهد به کاهش پیامدهای نابرخوانی با محیط است. ' .
                                   'تلاش‌های مستمر برای بهینه‌سازی مصرف منابع و کاهش انتشارات تداوم خواهد داشت.';

        return $narratives;
    }

    /**
     * Build GHG emissions narrative from controls
     */
    private function buildGHGNarrative(array $normalized): ?string
    {
        $scope1 = $this->getControlValue($normalized, 'greenhouse-gas-emissions', 'scope1');
        $scope2 = $this->getControlValue($normalized, 'greenhouse-gas-emissions', 'scope2');
        $scope3 = $this->getControlValue($normalized, 'greenhouse-gas-emissions', 'scope3');
        $total = $this->getControlValue($normalized, 'greenhouse-gas-emissions', 'total-ghg');
        $reduction = $this->getControlValue($normalized, 'greenhouse-gas-emissions', 'ghg-reduction-rate');

        if (!$scope1 && !$scope2 && !$scope3 && !$total) {
            return null;
        }

        $narrative = 'میزان انتشارات گازهای گلخانه‌ای سازمان';
        
        if ($scope1) {
            $narrative .= " در Scope 1 معادل {$scope1} تن معادل دی‌اکسید کربن است،";
        }
        if ($scope2) {
            $narrative .= " Scope 2 برابر {$scope2} تن معادل دی‌اکسید کربن و";
        }
        if ($scope3) {
            $narrative .= " Scope 3 معادل {$scope3} تن معادل دی‌اکسید کربن را شامل می‌شود.";
        } else {
            $narrative .= ".";
        }
        
        if ($total) {
            $narrative .= " کل انتشارات معادل {$total} تن معادل دی‌اکسید کربن است.";
        }
        
        if ($reduction) {
            $narrative .= " نرخ کاهش انتشارات نسبت به سال قبل {$reduction}% بوده است.";
        }

        return $narrative;
    }

    /**
     * Build energy narrative from controls
     */
    private function buildEnergyNarrative(array $normalized): ?string
    {
        $electricity = $this->getControlValue($normalized, 'energy-resource-management', 'electricity-consumption');
        $gas = $this->getControlValue($normalized, 'energy-resource-management', 'gas-consumption');
        $renewablePercent = $this->getControlValue($normalized, 'energy-resource-management', 'renewable-energy-percent');
        $energyReduction = $this->getControlValue($normalized, 'energy-resource-management', 'energy-reduction-rate');

        if (!$electricity && !$gas && !$renewablePercent) {
            return null;
        }

        $narrative = 'مصرف انرژی سازمان';
        
        if ($electricity) {
            $narrative .= " {$electricity} مگاوات ساعت برق مصرف کرد و";
        }
        if ($gas) {
            $narrative .= " مصرف گاز طبیعی معادل {$gas} مکعب بوده است.";
        } elseif ($electricity) {
            $narrative .= ".";
        }

        if ($renewablePercent) {
            $narrative .= " {$renewablePercent}% از انرژی مصرفی از منابع تجدیدپذیر تأمین شده است.";
        }

        if ($energyReduction) {
            $narrative .= " میزان کاهش مصرف انرژی {$energyReduction}% بوده است.";
        }

        return $narrative;
    }

    /**
     * Build water narrative from controls
     */
    private function buildWaterNarrative(array $normalized): ?string
    {
        $totalWater = $this->getControlValue($normalized, 'water-management', 'total-water-consumption');
        $recycledPercent = $this->getControlValue($normalized, 'water-management', 'water-recycling-percent');
        $waterReduction = $this->getControlValue($normalized, 'water-management', 'water-reduction-rate');

        if (!$totalWater && !$recycledPercent) {
            return null;
        }

        $narrative = 'مدیریت منابع آبی';
        
        if ($totalWater) {
            $narrative .= " کل مصرف آب سازمان {$totalWater} مترمکعب بود.";
        }

        if ($recycledPercent) {
            $narrative .= " {$recycledPercent}% از آب مصرفی بازیافت و دوباره استفاده شد.";
        }

        if ($waterReduction) {
            $narrative .= " میزان کاهش مصرف آب {$waterReduction}% بوده است.";
        }

        return $narrative;
    }

    /**
     * Build waste narrative from controls
     */
    private function buildWasteNarrative(array $normalized): ?string
    {
        $totalWaste = $this->getControlValue($normalized, 'waste-management-circular-economy', 'total-waste');
        $recycledPercent = $this->getControlValue($normalized, 'waste-management-circular-economy', 'waste-recycling-percent');
        $hazardousWaste = $this->getControlValue($normalized, 'waste-management-circular-economy', 'hazardous-waste');

        if (!$totalWaste && !$recycledPercent && !$hazardousWaste) {
            return null;
        }

        $narrative = 'مدیریت پسماند سازمان';
        
        if ($totalWaste) {
            $narrative .= " کل پسماند تولیدی {$totalWaste} تن بود.";
        }

        if ($recycledPercent) {
            $narrative .= " {$recycledPercent}% از پسماند به روش‌های بازیافت و بازاستفاده اداره شد.";
        }

        if ($hazardousWaste) {
            $narrative .= " میزان پسماند خطرناک {$hazardousWaste} تن بود و تحت نظارت ویژه مدیریت شد.";
        }

        return $narrative;
    }

    /**
     * Build social section narratives
     */
    private function buildSocialNarratives(array $normalized): array
    {
        $narratives = [
            'intro' => 'سازمان در حوزه اجتماعی بر ایجاد محیط کار ایمن، عادلانه و شامل‌کننده متمرکز است. ' .
                      'سرمایه‌گذاری در توسعه نیروی انسانی و بهبود رفاه کارکنان از اولویت‌های کلیدی است.',
        ];

        // Health & Safety narrative
        if ($hsNarrative = $this->buildHealthSafetyNarrative($normalized)) {
            $narratives['health_safety'] = $hsNarrative;
        }

        // Diversity, Equity, Inclusion narrative
        if ($deiNarrative = $this->buildDEINarrative($normalized)) {
            $narratives['dei'] = $deiNarrative;
        }

        $narratives['conclusion'] = 'عملکرد اجتماعی سازمان نمایانگر تعهد به بهبود شرایط کارکنان و ایجاد فرصت‌های برابر است. ' .
                                   'ادامه تلاش برای کاهش حوادث و افزایش فرصت‌های شغلی برای گروه‌های کمترنماینده پیش خواهد رفت.';

        return $narratives;
    }

    /**
     * Build health and safety narrative
     */
    private function buildHealthSafetyNarrative(array $normalized): ?string
    {
        $injuryRate = $this->getControlValue($normalized, 'health-safety-wellbeing', 'injury-rate');
        $fatalitiesCount = $this->getControlValue($normalized, 'health-safety-wellbeing', 'fatalities');
        $safetyTrainingCompletion = $this->getControlValue($normalized, 'health-safety-wellbeing', 'safety-training-completion-percent');

        if (!$injuryRate && !$fatalitiesCount && !$safetyTrainingCompletion) {
            return null;
        }

        $narrative = 'سلامت و ایمنی کارکنان';
        
        if ($injuryRate) {
            $narrative .= " نرخ حوادث و صدمات {$injuryRate} برای هر میلیون ساعت کار ثبت شد.";
        }

        if ($fatalitiesCount) {
            $narrative .= " متأسفانه {$fatalitiesCount} حادثه با پیامد مرگبار رخ داد.";
        }

        if ($safetyTrainingCompletion) {
            $narrative .= " {$safetyTrainingCompletion}% کارکنان برنامه‌های آموزش ایمنی را تکمیل کردند.";
        }

        return $narrative;
    }

    /**
     * Build diversity, equity, inclusion narrative
     */
    private function buildDEINarrative(array $normalized): ?string
    {
        $wageGap = $this->getControlValue($normalized, 'diversity-equity-inclusion', 'wage-gap');
        $underrepresentedGroupPercent = $this->getControlValue($normalized, 'diversity-equity-inclusion', 'underrepresented-groups-percent');
        $femaleLeadershipPercent = $this->getControlValue($normalized, 'diversity-equity-inclusion', 'female-leadership-percent');

        if (!$wageGap && !$underrepresentedGroupPercent && !$femaleLeadershipPercent) {
            return null;
        }

        $narrative = 'تنوع، برابری و شمول در سازمان';
        
        if ($wageGap) {
            $narrative .= " شکاف پرداختی بین گروه‌های مختلف {$wageGap}% است.";
        }

        if ($underrepresentedGroupPercent) {
            $narrative .= " {$underrepresentedGroupPercent}% از پست‌های مختلف توسط گروه‌های کمترنماینده سازمانی اشغال شده است.";
        }

        if ($femaleLeadershipPercent) {
            $narrative .= " {$femaleLeadershipPercent}% از مسئولیت‌های رهبری توسط زنان برعهده است.";
        }

        return $narrative;
    }

    /**
     * Build governance section narratives
     */
    private function buildGovernanceNarratives(array $normalized): array
    {
        return [
            'intro' => 'حاکمیت شرکتی و نظارت بر اخلاقیات و انطباق با مقررات از اساس استراتژی سازمان است. ' .
                      'هیات مدیره نقش مهمی در هدایت گذاری‌های سازمان و نظارت بر عملکرد دارد.',
            'board' => $this->buildBoardNarrative($normalized) ?? 
                      'هیات مدیره سازمان نقش کلیدی در تصمیم‌گیری‌های استراتژیک و نظارت بر عملکرد سازمان را ایفا می‌کند.',
            'conclusion' => 'ساختار حاکمیتی سازمان بر اساس بهترین شیوه‌های بین‌المللی شکل گرفته است. ' .
                          'تعهد به شفافیت، اخلاقیات و رعایت تمام مقررات قانونی تداوم خواهد داشت.',
        ];
    }

    /**
     * Build board narrative
     */
    private function buildBoardNarrative(array $normalized): ?string
    {
        $boardSize = $this->getControlValue($normalized, 'corporate-governance-structure', 'board-members-count');
        $femalePercent = $this->getControlValue($normalized, 'corporate-governance-structure', 'female-board-members-percent');
        $attendanceRate = $this->getControlValue($normalized, 'corporate-governance-structure', 'board-meeting-attendance-rate');
        $esgMeetings = $this->getControlValue($normalized, 'corporate-governance-structure', 'esg-focused-meetings-count');

        if (!$boardSize && !$femalePercent && !$attendanceRate) {
            return null;
        }

        $narrative = 'هیات مدیره';
        
        if ($boardSize) {
            $narrative .= " {$boardSize} نفر عضو دارد و";
        }

        if ($femalePercent) {
            $narrative .= " {$femalePercent}% از اعضا زن هستند.";
        } elseif ($boardSize) {
            $narrative .= ".";
        }

        if ($attendanceRate) {
            $narrative .= " نرخ حضور اعضا در جلسات {$attendanceRate}% بوده است.";
        }

        if ($esgMeetings) {
            $narrative .= " در این دوره {$esgMeetings} جلسه تخصصی درخصوص مسائل پایداری برگزار شد.";
        }

        return $narrative;
    }

    /**
     * Build final report conclusion
     */
    private function buildReportConclusion(): string
    {
        return 'سازمان متعهد است که در مسیر توسعه پایدار و بهبود مستمر عملکرد ESG خود پیش‌رود. ' .
               'تعامل مثمرثمر با ذی‌نفعان و سرمایه‌گذاری در فناوری‌های نو پایه‌های استراتژی سازمان را تشکیل می‌دهند. ' .
               'انتظار می‌رود که نتایج گزارش حاضر مبنای تعیین اهداف و برنامه‌های بهتری برای سال‌های آتی باشد.';
    }

    /**
     * Helper method to get control value by parent slug and control slug
     */
    private function getControlValue(array $normalized, string $parentSlug, string $controlSlug): ?string
    {
        $control = $this->dataMapper->getControlBySlug($controlSlug, $parentSlug, $normalized);
        
        if (!$control || !isset($control['answer'])) {
            return null;
        }

        $answer = $control['answer'];
        
        // Return empty string if answer is empty/zero/null
        if ($answer === '' || $answer === null || (is_numeric($answer) && $answer == 0)) {
            return null;
        }

        return (string) $answer;
    }
}

