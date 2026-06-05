# ESG Report Generation System - Frontend Implementation Guide

## Overview

The backend ESG Report Generation API provides a comprehensive, dynamic sustainability report with narratives, key figures, and structured sections based on real data from the database.

## API Endpoint

```
POST /api/content/report
```

## Response Structure

The API returns a single JSON object with the following structure:

```json
{
  "result": true,
  "data": {
    "meta": {
      "generated_at": "2026-06-05 14:00:00",
      "reporting_year": 2026
    },
    "key_figures": [
      {
        "slug": "total-ghg",
        "title": "Total GHG Emissions",
        "parent_slug": "greenhouse-gas-emissions",
        "answer": "125000",
        "answer_unit": "tonnes CO2e",
        "metric_code": "ENV-GHG-004",
        "kpi_code": "GHG-004"
      }
    ],
    "environmental": {
      "climate": [...],
      "ghg": [...],
      "energy": [...],
      "water": [...],
      "waste": [...]
    },
    "social": {
      "workforce": [...],
      "dei": [...],
      "health_safety": [...]
    },
    "governance": {
      "board": [...],
      "ethics": [...],
      "compliance": [...]
    },
    "narratives": {
      "about_report": {
        "title": "درباره این گزارش",
        "body": "متن معرفی گزارش..."
      },
      "environmental": {
        "intro": {
          "title": "عملکرد زیست‌محیطی",
          "body": "پاراگراف مقدمه..."
        },
        "climate": {
          "title": "تغییرات اقلیمی",
          "body": "پاراگراف اقلیم با اعداد..."
        },
        "ghg": {
          "title": "انتشار گازهای گلخانه‌ای",
          "body": "پاراگراف GHG..."
        },
        "energy": {
          "title": "مدیریت انرژی",
          "body": "پاراگراف انرژی..."
        },
        "water": {
          "title": "مدیریت آب",
          "body": "پاراگراف آب..."
        },
        "waste": {
          "title": "مدیریت پسماند",
          "body": "پاراگراف پسماند..."
        }
      },
      "social": {
        "intro": {
          "title": "عملکرد اجتماعی",
          "body": "پاراگراف مقدمه..."
        },
        "workforce": {
          "title": "ساختار نیروی کار",
          "body": "پاراگراف نیروی کار..."
        },
        "dei": {
          "title": "تنوع، برابری و شمول",
          "body": "پاراگراف DEI..."
        },
        "health_safety": {
          "title": "سلامت و ایمنی",
          "body": "پاراگراف سلامت..."
        }
      },
      "governance": {
        "intro": {
          "title": "عملکرد حاکمیتی",
          "body": "پاراگراف مقدمه..."
        },
        "board": {
          "title": "هیات مدیره",
          "body": "پاراگراف هیات..."
        },
        "ethics": {
          "title": "اخلاقیات و تعارضات منافع",
          "body": "پاراگراف اخلاقیات..."
        },
        "compliance": {
          "title": "انطباق با مقررات",
          "body": "پاراگراف انطباق..."
        }
      },
      "report_conclusion": {
        "title": "نتیجه‌گیری",
        "body": "پاراگراف پایانی..."
      }
    }
  },
  "error": []
}
```

## Key Data Types

### Meta Object
- `generated_at` (string): ISO datetime of report generation
- `reporting_year` (integer): Year of the report

### Key Figures Array
- Array of numeric KPIs filtered from all controls with `answer_type: 'number'`
- Each includes: slug, title, parent_slug, answer (value), unit, metric_code, kpi_code

### Section Objects (environmental, social, governance)
- Contain grouped controls by domain
- Structure: `{ "domain_slug": [ control1, control2, ... ] }`
- Returns `null` if no data available for that domain

### Narratives Object
- **Dynamic text** generated from actual control values
- **Persian formal language** suitable for corporate reports
- **Two-level structure** with title and body
- Values are extracted from controls, **no hardcoded numbers**
- Missing sections are omitted gracefully

### Control Object Structure
```json
{
  "type": "control",
  "parent_slug": "greenhouse-gas-emissions",
  "slug": "scope1-emissions",
  "title": "Scope 1 GHG Emissions",
  "summary": "Description...",
  "answer": "45000",
  "answer_unit": "tonnes CO2e",
  "answer_type": "number",
  "metric_code": "ENV-GHG-001",
  "kpi_code": "GHG-001",
  "frameworks": ["GRI", "SASB", "TCFD"]
}
```

---

# Step-by-Step Frontend Implementation Pipeline

## Phase 1: API Data Fetching

### Prompt 1A: Setup Report Service

```
Create a TypeScript/JavaScript service that:

1. Has method fetchReport(): Promise<ReportData>
   - POST to /api/content/report
   - Returns the response.data object
   - Handles errors gracefully

2. Define interface ReportData with:
   - meta: { generated_at, reporting_year }
   - key_figures: KeyFigure[]
   - environmental, social, governance: SectionData
   - narratives: NarrativesData

3. Add error handling:
   - Check response.result === true
   - Show error from response.error if result is false
   - Handle network errors

4. Add loading state management
```

---

## Phase 2: Layout & Navigation

### Prompt 2A: Create Main Report Container

```
Create a React/Vue component ReportContainer that:

1. Fetches report on mount using the service
2. Shows loading state while fetching
3. Shows error message if fetch fails
4. Once loaded, displays:
   - Report header with generated_at and reporting_year
   - Main navigation tabs: [Overview, Environmental, Social, Governance, Key Figures]
   - Active section content below tabs
5. Manage active tab state

Structure:
- Header section: title, date, year
- Tabs navigation
- Content area for current tab
```

---

## Phase 3: Report Header & Overview

### Prompt 3A: Report Header Component

```
Create ReportHeader component that displays:

1. Title: "گزارش پایداری {reporting_year}"
2. Generated date: "تهیه‌شده در {generated_at}"
3. Organization name (from data or config)
4. Report introduction narrative:
   - narratives.about_report.title
   - narratives.about_report.body
5. Visual styling with Persian RTL support
```

### Prompt 3B: Key Figures Overview

```
Create KeyFiguresOverview component that:

1. Displays key_figures array
2. Shows top 8-12 most important KPIs
3. For each KPI display:
   - Title
   - Value (answer)
   - Unit (answer_unit)
   - Optional: metric_code badge
4. Grid layout (2-4 columns)
5. Use color coding:
   - Green: high values (targets met)
   - Orange: medium values
   - Red: low values (needs attention)
```

---

## Phase 4: Environmental Section

### Prompt 4A: Environmental Overview

```
Create EnvironmentalIntro component that displays:

1. narratives.environmental.intro.title
2. narratives.environmental.intro.body
3. Navigation sub-tabs:
   - Climate
   - GHG Emissions
   - Energy
   - Water
   - Waste
   - Biodiversity (if available)
   - Pollution (if available)
4. Manage which sub-section is shown
```

### Prompt 4B: Climate Section

```
Create ClimateSection component that:

1. Displays narratives.environmental.climate:
   - title: "تغییرات اقلیمی"
   - body: (dynamic text with numbers)

2. Shows controls from environmental.climate array:
   - List or cards view
   - For each control: title, answer, unit, frameworks

3. Charts (if data available):
   - Climate risk distribution
   - Investment timeline
   - Goal achievement progress bar
```

### Prompt 4C: GHG Emissions Section

```
Create GHGSection component that:

1. Displays narratives.environmental.ghg:
   - title: "انتشار گازهای گلخانه‌ای"
   - body: (dynamic text with Scope 1, 2, 3 values)

2. Shows Scope breakdown:
   - Scope 1: X tonnes CO2e
   - Scope 2: Y tonnes CO2e
   - Scope 3: Z tonnes CO2e
   - Total: X+Y+Z tonnes CO2e

3. Visualization:
   - Pie chart: Scope 1 vs 2 vs 3 distribution
   - Line chart: Historical trend (if multiple years available)
   - Progress toward reduction target

4. Details table: All GHG-related controls
```

### Prompt 4D: Energy Section

```
Create EnergySection component that:

1. Displays narratives.environmental.energy:
   - title: "مدیریت انرژی"
   - body: (dynamic text with consumption values)

2. Shows breakdown:
   - Electricity consumption: X MWh
   - Natural gas: Y m³
   - Liquid fuel: Z liters
   - Renewable %: N%

3. Visualization:
   - Stacked bar chart: Energy sources
   - Gauge chart: Renewable energy percentage
   - KPI cards: Each energy type with trend

4. Details table: All energy-related controls
```

### Prompt 4E: Water Section

```
Create WaterSection component that:

1. Displays narratives.environmental.water:
   - title: "مدیریت آب"
   - body: (dynamic text with consumption values)

2. Shows metrics:
   - Total water consumption: X m³
   - Wastewater discharge: Y m³
   - Recycled water %: Z%
   - Water intensity (per unit)

3. Visualization:
   - Sankey diagram: Water flow (consumption → recycling → discharge)
   - Progress bar: Recycling percentage
   - Water stress regions highlight

4. Details table: All water-related controls
```

### Prompt 4F: Waste Section

```
Create WasteSection component that:

1. Displays narratives.environmental.waste:
   - title: "مدیریت پسماند و اقتصاد چرخشی"
   - body: (dynamic text with waste values)

2. Shows breakdown:
   - Total waste: X tonnes
   - Hazardous waste: Y tonnes
   - Recycled %: Z%
   - Final disposal %: W%
   - Circular economy projects: N

3. Visualization:
   - Pie chart: Waste by type
   - Waterfall chart: Waste → Recycling → Disposal
   - Circular economy metrics cards

4. Details table: All waste-related controls
```

---

## Phase 5: Social Section

### Prompt 5A: Social Overview

```
Create SocialIntro component that displays:

1. narratives.social.intro.title
2. narratives.social.intro.body
3. Navigation sub-tabs:
   - Workforce
   - Diversity, Equity & Inclusion
   - Health & Safety
4. Manage which sub-section is shown
```

### Prompt 5B: Workforce Section

```
Create WorkforceSection component that:

1. Displays narratives.social.workforce:
   - title: "ساختار نیروی کار"
   - body: (dynamic text)

2. Shows metrics:
   - Total employees
   - By job category
   - By location
   - Full-time vs part-time
   - Contract types

3. Visualization:
   - Breakdown charts
   - Org structure (if available)
   - Growth trends

4. Details table: Workforce-related controls
```

### Prompt 5C: Diversity, Equity & Inclusion

```
Create DEISection component that:

1. Displays narratives.social.dei:
   - title: "تنوع، برابری و شمول"
   - body: (dynamic text with gap percentages)

2. Shows metrics:
   - Wage gap %
   - Underrepresented groups %
   - Female leadership %
   - Gender ratios by level
   - Minority representation

3. Visualization:
   - Comparison bars: Gender/ethnicity distribution
   - Progress toward targets
   - Heatmap: Representation by department

4. Details table: DEI-related controls
```

### Prompt 5D: Health & Safety

```
Create HealthSafetySection component that:

1. Displays narratives.social.health_safety:
   - title: "سلامت و ایمنی کارکنان"
   - body: (dynamic text with injury rates)

2. Shows metrics:
   - Injury rate (per million hours)
   - Fatalities
   - Safety training completion %
   - Lost time injuries
   - Near-miss incidents

3. Visualization:
   - Trend line: Injury rate over time
   - Progress bar: Training completion
   - Safety incident cards
   - Risk heatmap by department

4. Details table: Health & safety controls
```

---

## Phase 6: Governance Section

### Prompt 6A: Governance Overview

```
Create GovernanceIntro component that displays:

1. narratives.governance.intro.title
2. narratives.governance.intro.body
3. Navigation sub-tabs:
   - Board of Directors
   - Ethics & Compliance
   - Regulatory Compliance (if available)
4. Manage which sub-section is shown
```

### Prompt 6B: Board of Directors

```
Create BoardSection component that:

1. Displays narratives.governance.board:
   - title: "هیات مدیره"
   - body: (dynamic text with board size, diversity, meetings)

2. Shows metrics:
   - Board size
   - Female board members %
   - Meeting attendance rate
   - ESG-focused meetings count
   - Committee structure

3. Visualization:
   - Board composition cards
   - Diversity indicator
   - Meeting frequency chart
   - Committee overview

4. Details table: Board-related controls
```

### Prompt 6C: Ethics & Compliance

```
Create EthicsComplianceSection component that:

1. Displays narratives.governance.ethics:
   - title: "اخلاقیات و مدیریت منافع"
   - body: (dynamic text)

2. Shows metrics:
   - Code of conduct coverage %
   - Compliance training completion %
   - Ethical violations reported
   - Whistleblower reports
   - Conflict of interest disclosures

3. Visualization:
   - Compliance metrics cards
   - Training completion progress
   - Incident timeline

4. Details table: Ethics controls
```

---

## Phase 7: Report Conclusion & Export

### Prompt 7A: Report Conclusion

```
Create ReportConclusion component that:

1. Displays narratives.report_conclusion:
   - title: "نتیجه‌گیری کلی"
   - body: (dynamic text about organization's ESG commitment)

2. Next steps section:
   - List key goals for next year
   - Link to detailed roadmap (if available)

3. Contact information:
   - Sustainability team contact
   - Report feedback form link
```

### Prompt 7B: Export Functionality

```
Create ExportControls component that:

1. Provides buttons:
   - Download as PDF
   - Download as Excel
   - Share link
   - Print report

2. PDF generation:
   - Include all sections
   - Proper styling for Persian RTL
   - Metadata: date, year, organization
   - Page breaks between sections

3. Excel export:
   - Separate sheets per section
   - Key figures sheet
   - Controls detail sheet
   - Charts as images

4. Print optimization:
   - Hide navigation elements
   - Optimize colors for B&W printing
   - Ensure page breaks
```

---

## Phase 8: Additional Features

### Prompt 8A: Data Tables & Details

```
Create DataTable component that:

1. Shows control data in tabular format:
   - Title
   - Answer (value)
   - Unit
   - Answer type
   - Metric code
   - KPI code
   - Frameworks

2. Features:
   - Sortable columns
   - Filterable by framework
   - Search functionality
   - Expandable rows for details

3. Responsive:
   - Desktop: full table
   - Mobile: cards view
```

### Prompt 8B: Responsive Design

```
Ensure report is responsive across devices:

1. Desktop (1200+px):
   - Full layout with sidebars
   - Multi-column grids
   - All charts visible

2. Tablet (768-1199px):
   - Single column
   - Stacked elements
   - Touch-friendly buttons

3. Mobile (<768px):
   - Full width
   - Vertical stacking
   - Vertical scroll navigation
   - Hamburger menu for tabs
```

### Prompt 8C: Accessibility & Performance

```
Optimize for accessibility and performance:

1. Accessibility:
   - Semantic HTML
   - ARIA labels for charts
   - Keyboard navigation
   - Color contrast WCAG AA
   - Persian language support (RTL)

2. Performance:
   - Lazy load images/charts
   - Virtualized lists for large tables
   - Chart debouncing on resize
   - Minimize bundle size
   - Cache report data

3. Analytics:
   - Track section views
   - Chart interaction tracking
   - Export tracking
   - Error reporting
```

---

## Implementation Checklist

- [ ] Phase 1: API Service & Data Fetching
- [ ] Phase 2: Layout & Navigation
- [ ] Phase 3: Report Header & Overview
- [ ] Phase 4A-F: Environmental Sections
- [ ] Phase 5A-D: Social Sections
- [ ] Phase 6A-C: Governance Sections
- [ ] Phase 7A-B: Conclusion & Export
- [ ] Phase 8A-C: Tables, Responsiveness, Accessibility
- [ ] Testing: Unit, Integration, E2E
- [ ] Performance optimization
- [ ] User testing & feedback
- [ ] Deployment & monitoring

---

## API Consumption Tips

1. **Null Handling**: Sections can be `null` if no data. Always check before rendering.
2. **Numbers in Narratives**: All numbers in narrative text are extracted from controls. Display as-is without formatting.
3. **Persian RTL**: Ensure CSS has `direction: rtl` for body/main elements.
4. **Frameworks**: Use control's `frameworks` array to show badges (GRI, SASB, TCFD, etc.)
5. **Performance**: Cache the entire report response for 1 day or until data changes.
6. **Customization**: Narrative text is fully dynamic - customize section order/visibility by hiding components.

---

## Example API Call (JavaScript)

```javascript
const response = await fetch('/api/content/report', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    // Optional: request-specific parameters
  })
});

if (!response.ok) {
  throw new Error('Report generation failed');
}

const result = await response.json();

if (result.result) {
  const report = result.data;
  console.log('Report generated at:', report.meta.generated_at);
  console.log('Reporting year:', report.meta.reporting_year);
  console.log('Key figures count:', report.key_figures.length);
  // Render UI with report data
} else {
  console.error('Error:', result.error);
}
```

---

## Summary

This report system provides:
- ✅ Dynamic narratives in Persian with real data
- ✅ Structured sections: Environmental, Social, Governance
- ✅ Key figures extracted from controls
- ✅ Null-safe data handling
- ✅ Multiple export formats (PDF, Excel)
- ✅ Responsive design
- ✅ Full accessibility support

Follow the step-by-step prompts above in your frontend Copilot to implement each section progressively!
