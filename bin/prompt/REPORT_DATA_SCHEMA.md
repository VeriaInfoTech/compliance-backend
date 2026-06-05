# ESG Report Data Schema Reference

## Complete TypeScript Interfaces

```typescript
// Main Report Response
interface ReportResponse {
  result: boolean;
  data: ReportData;
  error: ErrorDetail[];
}

interface ErrorDetail {
  message: string;
  code?: string | number;
}

// Core Report Data
interface ReportData {
  meta: MetaData;
  key_figures: KeyFigure[];
  environmental: SectionData;
  social: SectionData;
  governance: SectionData;
  narratives: NarrativesData;
}

interface MetaData {
  generated_at: string; // ISO datetime: "2026-06-05 14:00:00"
  reporting_year: number;
}

// Key Figures (numeric KPIs)
interface KeyFigure {
  slug: string;
  title: string;
  parent_slug: string; // domain slug
  answer: string | number | null;
  answer_unit?: string;
  metric_code?: string;
  kpi_code?: string;
}

// Section Data (groups controls by domain)
interface SectionData {
  [parentSlug: string]: Control[] | null;
}

// Example structure:
// {
//   "greenhouse-gas-emissions": [control1, control2, ...],
//   "energy-resource-management": [control3, control4, ...],
//   "water-management": null  // no data for this domain
// }

// Individual Control
interface Control {
  type: 'control'; // always 'control' for this section
  parent_slug: string; // domain it belongs to
  slug: string; // unique identifier
  title: string; // display name
  summary?: string; // description
  answer?: string | number | null; // the actual value/data
  answer_unit?: string; // unit of measurement (tonnes, m³, %, etc.)
  answer_type?: 'number' | 'text' | 'percentage' | 'date' | 'choice';
  metric_code?: string; // code like ENV-GHG-001
  kpi_code?: string; // KPI code
  frameworks?: string[]; // ['GRI', 'SASB', 'TCFD']
}

// Narratives (dynamic text generated from controls)
interface NarrativesData {
  about_report: NarrativeSection;
  environmental: EnvironmentalNarratives;
  social: SocialNarratives;
  governance: GovernanceNarratives;
  report_conclusion: NarrativeSection;
}

interface NarrativeSection {
  title: string; // Persian heading
  body: string; // Persian paragraph with dynamic numbers
}

// Environmental Narratives
interface EnvironmentalNarratives {
  intro: NarrativeSection;
  climate?: NarrativeSection;
  ghg?: NarrativeSection;
  energy?: NarrativeSection;
  water?: NarrativeSection;
  waste?: NarrativeSection;
  biodiversity?: NarrativeSection;
  pollution?: NarrativeSection;
}

// Social Narratives
interface SocialNarratives {
  intro: NarrativeSection;
  workforce?: NarrativeSection;
  dei?: NarrativeSection;
  health_safety?: NarrativeSection;
  conclusion: NarrativeSection;
}

// Governance Narratives
interface GovernanceNarratives {
  intro: NarrativeSection;
  board?: NarrativeSection;
  ethics?: NarrativeSection;
  compliance?: NarrativeSection;
  conclusion: NarrativeSection;
}
```

---

## Data Path Examples

### Getting Environmental Data

```typescript
// Get all GHG controls
const ghgControls: Control[] = report.environmental['greenhouse-gas-emissions'];

// Get first GHG control
const firstGHG: Control = report.environmental['greenhouse-gas-emissions']?.[0];

// Get GHG narrative
const ghgNarrative: NarrativeSection = report.narratives.environmental.ghg;

// Build a sentence with numbers from controls
const scope1 = ghgControls?.find(c => c.slug === 'scope1')?.answer;
const scope2 = ghgControls?.find(c => c.slug === 'scope2')?.answer;
// Or use: report.narratives.environmental.ghg.body (already has numbers)
```

### Getting Social Data

```typescript
// Get all workforce controls
const workforceControls: Control[] = report.social['workforce-structure'];

// Get DEI controls
const deiControls: Control[] = report.social['diversity-equity-inclusion'];

// Get health & safety controls
const hsControls: Control[] = report.social['health-safety-wellbeing'];

// Get narrative with numbers
const hsNarrative = report.narratives.social.health_safety.body;
// Text already contains: "نرخ حوادث {value} برای هر میلیون ساعت کار"
```

### Getting Governance Data

```typescript
// Get board controls
const boardControls: Control[] = report.governance['corporate-governance-structure'];

// Get ethics controls
const ethicsControls: Control[] = report.governance['ethics-compliance'];

// Get compliance controls (if available)
const complianceControls: Control[] | null = report.governance['regulatory-compliance'];

// Check if compliance data exists
if (complianceControls) {
  // Render compliance section
}
```

### Getting Key Figures

```typescript
// Find specific KPI
const totalGHG = report.key_figures.find(kf => kf.slug === 'total-ghg');
console.log(`${totalGHG.title}: ${totalGHG.answer} ${totalGHG.answer_unit}`);

// Get top 12 KPIs for overview
const topKPIs = report.key_figures.slice(0, 12);

// Filter by domain
const envKPIs = report.key_figures.filter(kf => 
  kf.parent_slug.startsWith('greenhouse') ||
  kf.parent_slug.startsWith('energy') ||
  kf.parent_slug.startsWith('water') ||
  kf.parent_slug.startsWith('waste')
);
```

---

## Domain Slug Reference

### Environmental Domains
- `climate-change-strategy` → Climate & Climate Strategy
- `greenhouse-gas-emissions` → GHG Emissions
- `energy-resource-management` → Energy Management
- `water-management` → Water Management
- `waste-management-circular-economy` → Waste & Circular Economy
- `biodiversity-ecosystem-impact` → Biodiversity (optional)
- `pollution-environmental-impact` → Pollution (optional)

### Social Domains
- `workforce-structure` → Workforce
- `diversity-equity-inclusion` → DEI
- `health-safety-wellbeing` → Health & Safety

### Governance Domains
- `corporate-governance-structure` → Board of Directors
- `ethics-compliance` → Ethics & Compliance
- `regulatory-compliance` → Regulatory Compliance (optional)

---

## Null-Safety Patterns

### Pattern 1: Optional Chaining

```typescript
// Safe access
const ghgControls = report.environmental?.['greenhouse-gas-emissions'];
const ghgNarrative = report.narratives.environmental?.ghg?.body;

// In JSX/rendering
{report.environmental?.ghg && <GHGSection data={report.environmental.ghg} />}
```

### Pattern 2: Fallback Values

```typescript
// With fallback
const controls = report.environmental['greenhouse-gas-emissions'] ?? [];

// With OR operator
const narrative = report.narratives.environmental.ghg || {
  title: 'No Data',
  body: 'Ghg data not available'
};
```

### Pattern 3: Conditional Rendering

```typescript
// Check existence before rendering
if (!report.environmental.ghg) {
  return <div>GHG data not available</div>;
}

// Or use early returns
const renderGHGSection = () => {
  if (!report.environmental.ghg) return null;
  return <GHGSection data={report.environmental.ghg} />;
};
```

---

## Control Slug Reference (Common Examples)

### GHG Controls
- `scope1` / `scope1-emissions` → Scope 1 emissions
- `scope2` / `scope2-emissions` → Scope 2 emissions
- `scope3` / `scope3-emissions` → Scope 3 emissions
- `total-ghg` / `total-ghg-emissions` → Total GHG
- `ghg-intensity` → Carbon intensity
- `ghg-reduction-rate` → Reduction percentage

### Energy Controls
- `electricity-consumption` → Electricity MWh
- `gas-consumption` → Natural gas m³
- `liquid-fuel-consumption` → Fuel liters
- `renewable-energy-percent` → Renewable %
- `energy-per-employee` → Intensity
- `energy-reduction-rate` → Reduction %

### Water Controls
- `total-water-consumption` → Total m³
- `water-recycling-percent` → Recycled %
- `wastewater-discharge` → Discharge m³
- `water-intensity` → Per unit
- `water-stress-regions-percent` → % in stress areas
- `water-reduction-rate` → Reduction %

### Waste Controls
- `total-waste` → Total tonnes
- `hazardous-waste` → Hazardous tonnes
- `waste-recycling-percent` → Recycled %
- `waste-final-disposal-percent` → Disposal %
- `recycled-material-percent` → Recycled in production
- `circular-economy-projects` → Project count

### Workforce Controls
- `total-employees` → Employee count
- `full-time-employees` → FT count
- `part-time-employees` → PT count
- `employee-retention-rate` → Retention %
- `hiring-by-region` → Count per region
- `employee-turnover-rate` → Turnover %

### DEI Controls
- `wage-gap` → Pay gap %
- `underrepresented-groups-percent` → % in workforce
- `female-leadership-percent` → Female leaders %
- `female-board-members-percent` → Female board %
- `gender-pay-ratio` → M:F ratio
- `minority-representation` → % minorities

### H&S Controls
- `injury-rate` → Per million hours
- `fatalities` → Count
- `lost-time-injuries` → Count
- `safety-training-completion-percent` → %
- `incident-reporting-rate` → % of incidents reported
- `near-miss-incidents` → Count

### Board Controls
- `board-members-count` → Total members
- `female-board-members-percent` → Female %
- `board-meeting-attendance-rate` → Attendance %
- `board-independence-percent` → Independent %
- `esg-focused-meetings-count` → Count
- `board-diversity-index` → Diversity score

---

## Sample Data Queries

### Get all controls with numeric answers
```typescript
const numericControls = Object.values(report.key_figures)
  .filter(kf => kf.answer && !isNaN(Number(kf.answer)));
```

### Get all frameworks mentioned
```typescript
const allFrameworks = new Set<string>();
Object.values(report.environmental).forEach(controls => {
  controls?.forEach(c => {
    c.frameworks?.forEach(f => allFrameworks.add(f));
  });
});
```

### Flatten all controls
```typescript
const allControls: Control[] = [];
[report.environmental, report.social, report.governance].forEach(section => {
  Object.values(section).forEach(controls => {
    if (controls) allControls.push(...controls);
  });
});
```

### Get controls by metric code pattern
```typescript
const envControls = allControls.filter(c => c.metric_code?.startsWith('ENV-'));
const socialControls = allControls.filter(c => c.metric_code?.startsWith('SOC-'));
const govControls = allControls.filter(c => c.metric_code?.startsWith('GOV-'));
```

---

## Display Formatting Tips

### Numbers
- Keep original format from `answer` field
- Don't convert to Persian numbers (1 should stay 1, not ۱)
- Display with unit: `${control.answer} ${control.answer_unit}`

### Text
- Use `body` from narrative sections as-is
- Numbers in narrative already formatted
- Example: "میزان انتشار 125000 تن" (numbers already included)

### Dates
- `meta.generated_at` is ISO format: "2026-06-05 14:00:00"
- Format for Persian display: "۵ جون ۲۰۲۶" or "۱۴۰۵/۰۳/۱۵"

### Percentages
- Display as `${value}%`
- Example: `90.5%` (not 0.905)

### Units
- Common: tonnes, tonnes CO2e, m³, MWh, %, count, etc.
- Keep original from control.answer_unit

---

## Caching Strategy

### LocalStorage Key
```typescript
const CACHE_KEY = `esg_report_${new Date().toISOString().split('T')[0]}`;
```

### Cache Duration
- 1 day (24 hours) for normal operation
- Invalidate on: user refresh, data change event, manual update

### Cache Implementation
```typescript
const getReportFromCache = (): ReportData | null => {
  const cached = localStorage.getItem(CACHE_KEY);
  if (!cached) return null;
  
  const data = JSON.parse(cached);
  const age = Date.now() - data.timestamp;
  
  if (age > 24 * 60 * 60 * 1000) { // 24 hours
    localStorage.removeItem(CACHE_KEY);
    return null;
  }
  
  return data.report;
};

const cacheReport = (report: ReportData): void => {
  localStorage.setItem(CACHE_KEY, JSON.stringify({
    report,
    timestamp: Date.now()
  }));
};
```

---

## Error Codes (if available)

Common error scenarios:
- `422` → Invalid/empty data list
- `500` → Server error during generation
- Network errors → Connection issues
- Parse errors → Invalid JSON response

Always show user-friendly message and log details for debugging.

---

## Performance Tips

1. **Memoize Components**: Use React.memo() for section components
2. **Virtualize Tables**: For large datasets, use react-window
3. **Lazy Load Sections**: Load chart libraries on demand
4. **Image Optimization**: Use WebP with PNG fallback
5. **Code Splitting**: Separate chart library imports per section
6. **Debounce Resize**: Debounce chart resize handlers
7. **Compress Narratives**: Store as JSON, parse on demand

---

Ready to build! Use these schemas in your TypeScript interfaces.
