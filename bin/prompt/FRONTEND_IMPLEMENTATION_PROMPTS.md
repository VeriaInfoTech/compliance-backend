# Frontend Implementation Quick Start

## Step-by-Step Copilot Prompts for Frontend Development

Use these prompts in sequence in your frontend Copilot to implement the report display.

---

## PROMPT 1: API Service Layer

```
Create a TypeScript service file `services/reportService.ts` that:

1. Fetches ESG report from POST /api/content/report
2. Handles response with structure:
   - meta: { generated_at, reporting_year }
   - key_figures: array of numeric KPIs
   - environmental: { climate, ghg, energy, water, waste }
   - social: { workforce, dei, health_safety }
   - governance: { board, ethics, compliance }
   - narratives: { about_report, environmental, social, governance, report_conclusion }

3. Error handling for:
   - Network errors
   - Missing data
   - Invalid response format

4. Implement caching (localStorage, 1 day TTL)

Types to define:
- ReportData
- MetaData
- KeyFigure
- NarrativeSection
- ControlData
- SectionData
```

---

## PROMPT 2: Main Layout Component

```
Create React/Vue component `components/ReportContainer.tsx` that:

1. Fetches report on mount
2. Shows loading spinner while fetching
3. Shows error message if fetch fails

4. Displays header:
   - Title: "گزارش پایداری {year}"
   - Generated date: "تهیه شده در {generated_at}"

5. Tab navigation with sections:
   - Overview
   - Environmental
   - Social
   - Governance
   - Key Figures

6. Content area changes based on active tab
7. RTL support for Persian language
8. Responsive layout (desktop, tablet, mobile)
```

---

## PROMPT 3: Overview/Dashboard Tab

```
Create component `components/OverviewTab.tsx` that displays:

1. Report introduction:
   - narratives.about_report.title
   - narratives.about_report.body

2. Key metrics grid (top 12 key_figures):
   - 3-4 columns on desktop
   - 2 columns on tablet
   - 1 column on mobile
   - Each card shows: title, value, unit
   - Add color coding: green (high), orange (medium), red (low)

3. Quick summary stats:
   - Total KPIs tracked
   - Reporting year
   - Report generation date

4. Navigation cards linking to detailed sections
```

---

## PROMPT 4: Environmental Section - Structure

```
Create component `components/sections/EnvironmentalSection.tsx` that:

1. Shows narratives.environmental.intro as header:
   - Title: "عملکرد زیست‌محیطی"
   - Body text

2. Sub-section tabs:
   - Climate
   - GHG Emissions
   - Energy
   - Water
   - Waste

3. Each tab shows relevant narrative with dynamic numbers
4. Manage active sub-tab state
5. Render appropriate detail component based on active tab
```

---

## PROMPT 5: Environmental - Climate Detail

```
Create component `components/sections/env/ClimateDetail.tsx` that:

1. Displays narrative:
   - narratives.environmental.climate.title: "تغییرات اقلیمی و استراتژی اقلیمی"
   - narratives.environmental.climate.body (with dynamic numbers)

2. Shows controls from environmental.climate:
   - List view with title, answer, unit
   - Group by metric_code (ENV-CCS-001, etc.)

3. Charts (if data available):
   - Investment amount progress
   - Goal achievement percentage
   - Number of climate risks identified

4. Data table:
   - All climate controls with details
   - Columns: title, answer, unit, metric_code, frameworks
```

---

## PROMPT 6: Environmental - GHG Emissions Detail

```
Create component `components/sections/env/GHGDetail.tsx` that:

1. Displays narrative:
   - narratives.environmental.ghg.title: "انتشار گازهای گلخانه‌ای"
   - narratives.environmental.ghg.body (with Scope 1, 2, 3, total values)

2. Scope breakdown cards:
   - Scope 1: {value} tonnes CO2e
   - Scope 2: {value} tonnes CO2e
   - Scope 3: {value} tonnes CO2e
   - Total: {value} tonnes CO2e

3. Visualizations:
   - Pie chart: Scope distribution
   - Reduction progress bar (if available)

4. Detailed table: All GHG controls
```

---

## PROMPT 7: Environmental - Energy Detail

```
Create component `components/sections/env/EnergyDetail.tsx` that:

1. Displays narrative:
   - narratives.environmental.energy.title: "مدیریت انرژی"
   - narratives.environmental.energy.body

2. Energy breakdown:
   - Electricity: {value} MWh
   - Natural gas: {value} m³
   - Liquid fuel: {value} liters
   - Renewable %: {value}%

3. Charts:
   - Stacked bar: Energy sources mix
   - Gauge: Renewable percentage

4. KPI cards for each energy type
5. Detailed table: All energy controls
```

---

## PROMPT 8: Environmental - Water Detail

```
Create component `components/sections/env/WaterDetail.tsx` that:

1. Displays narrative:
   - narratives.environmental.water.title: "مدیریت آب"
   - narratives.environmental.water.body

2. Water metrics:
   - Total consumption: {value} m³
   - Wastewater discharge: {value} m³
   - Recycled water %: {value}%
   - Water intensity: {value}

3. Visualization:
   - Sankey: Water flow (intake → use → recycling → discharge)
   - Progress bar: Recycling percentage

4. Water stress regions (if available)
5. Detailed table: All water controls
```

---

## PROMPT 9: Environmental - Waste Detail

```
Create component `components/sections/env/WasteDetail.tsx` that:

1. Displays narrative:
   - narratives.environmental.waste.title: "مدیریت پسماند و اقتصاد چرخشی"
   - narratives.environmental.waste.body

2. Waste breakdown:
   - Total waste: {value} tonnes
   - Hazardous waste: {value} tonnes
   - Recycled %: {value}%
   - Final disposal %: {value}%
   - Circular economy projects: {count}

3. Charts:
   - Pie: Waste by type
   - Waterfall: Waste processing flow

4. Circular economy metrics cards
5. Detailed table: All waste controls
```

---

## PROMPT 10: Social Section - Structure

```
Create component `components/sections/SocialSection.tsx` that:

1. Shows narratives.social.intro as header:
   - Title: "عملکرد اجتماعی"
   - Body text

2. Sub-section tabs:
   - Workforce
   - Diversity, Equity & Inclusion
   - Health & Safety

3. Manage active sub-tab state
4. Render appropriate detail component based on active tab
```

---

## PROMPT 11: Social - Workforce Detail

```
Create component `components/sections/social/WorkforceDetail.tsx` that:

1. Displays narrative:
   - narratives.social.workforce.title: "ساختار نیروی کار"
   - narratives.social.workforce.body

2. Shows workforce metrics:
   - Total employees
   - By gender, age group, region (if available)
   - Full-time vs part-time
   - Employment contract types

3. Charts:
   - Breakdown pie charts
   - Growth trend line

4. Detailed table: All workforce controls
```

---

## PROMPT 12: Social - DEI Detail

```
Create component `components/sections/social/DEIDetail.tsx` that:

1. Displays narrative:
   - narratives.social.dei.title: "تنوع، برابری و شمول"
   - narratives.social.dei.body (with percentages)

2. DEI metrics:
   - Wage gap: {value}%
   - Underrepresented groups: {value}%
   - Female leadership: {value}%
   - Gender ratios by level

3. Charts:
   - Comparison bars: Gender/minority distribution
   - Representation heatmap by department

4. Progress toward targets
5. Detailed table: All DEI controls
```

---

## PROMPT 13: Social - Health & Safety Detail

```
Create component `components/sections/social/HealthSafetyDetail.tsx` that:

1. Displays narrative:
   - narratives.social.health_safety.title: "سلامت و ایمنی"
   - narratives.social.health_safety.body

2. Safety metrics:
   - Injury rate: {value} per million hours
   - Fatalities: {count}
   - Training completion: {value}%
   - Lost time injuries: {count}

3. Charts:
   - Injury rate trend (over time)
   - Training completion progress bar
   - Incident type breakdown

4. Risk heatmap by department/location
5. Detailed table: All health & safety controls
```

---

## PROMPT 14: Governance Section - Structure

```
Create component `components/sections/GovernanceSection.tsx` that:

1. Shows narratives.governance.intro as header:
   - Title: "عملکرد حاکمیتی"
   - Body text

2. Sub-section tabs:
   - Board of Directors
   - Ethics & Compliance
   - (Optional) Regulatory Compliance

3. Manage active sub-tab state
4. Render appropriate detail component based on active tab
```

---

## PROMPT 15: Governance - Board Detail

```
Create component `components/sections/governance/BoardDetail.tsx` that:

1. Displays narrative:
   - narratives.governance.board.title: "هیات مدیره"
   - narratives.governance.board.body

2. Board metrics:
   - Board size: {count} members
   - Female members: {value}%
   - Meeting attendance: {value}%
   - ESG meetings: {count}

3. Charts:
   - Board composition (gender, diversity)
   - Meeting frequency
   - Committee overview

4. Board member list (if available)
5. Detailed table: All board-related controls
```

---

## PROMPT 16: Governance - Ethics & Compliance Detail

```
Create component `components/sections/governance/EthicsDetail.tsx` that:

1. Displays narrative:
   - narratives.governance.ethics.title: "اخلاقیات و مدیریت منافع"
   - narratives.governance.ethics.body

2. Compliance metrics:
   - Code of conduct coverage: {value}%
   - Compliance training: {value}%
   - Ethical violations: {count}
   - Whistleblower reports: {count}

3. Charts:
   - Compliance metrics progress
   - Training completion
   - Incident timeline

4. Details table: All ethics controls
```

---

## PROMPT 17: Report Conclusion & Export

```
Create component `components/ReportConclusion.tsx` that:

1. Displays conclusion narrative:
   - narratives.report_conclusion.title: "نتیجه‌گیری کلی"
   - narratives.report_conclusion.body

2. Export buttons:
   - Download PDF (uses html2pdf library)
   - Download Excel (uses xlsx library)
   - Print report (window.print())
   - Share link (copy to clipboard)

3. PDF generation:
   - Include all sections
   - Proper styling for Persian RTL
   - Page breaks between sections
   - Metadata: date, organization

4. Excel export:
   - Sheet 1: Key figures
   - Sheet 2: Environmental
   - Sheet 3: Social
   - Sheet 4: Governance
   - Sheet 5: All controls details

5. Contact/feedback section
```

---

## PROMPT 18: Reusable UI Components

```
Create utility components:

1. DataTable:
   - Accept columns config and data
   - Sortable columns
   - Filterable by framework
   - Search functionality
   - Responsive (table on desktop, cards on mobile)

2. NarrativeCard:
   - Takes title and body
   - Auto-detect language direction (RTL for Persian)
   - Syntax highlighting for numbers

3. MetricCard:
   - Shows title, value, unit
   - Optional: trend indicator
   - Color coding by value range

4. ChartContainer:
   - Wrapper for charts with responsive sizing
   - Handles null/empty data gracefully

5. TabNavigation:
   - Horizontal/vertical tab switcher
   - Active tab indicator
   - Mobile: dropdown or swiping
```

---

## PROMPT 19: Styling & Themes

```
Implement styling for report:

1. CSS/SCSS modules:
   - Color scheme: professional corporate
   - Font: Persian-friendly (Vazir, Tahoma, etc.)
   - Dark mode option

2. Layout:
   - max-width: 1200px
   - RTL support with CSS logical properties
   - Responsive breakpoints: 320px, 768px, 1024px, 1200px

3. Typography:
   - Headings: 28px, 24px, 20px, 16px
   - Body text: 16px
   - Code/data: monospace

4. Colors:
   - Primary: #2C5AA0 (professional blue)
   - Success: #27AE60
   - Warning: #F39C12
   - Error: #E74C3C
   - Neutral: #34495E
   - Light bg: #ECF0F1

5. Spacing: 8px, 16px, 24px, 32px grid
```

---

## PROMPT 20: Final Polish & Optimization

```
Finalize report with:

1. Accessibility:
   - Add ARIA labels to all interactive elements
   - Keyboard navigation for tabs
   - Screen reader friendly narratives
   - Color contrast check WCAG AA

2. Performance:
   - Lazy load images/charts
   - Virtualize long tables
   - Debounce resize handlers
   - Code splitting by section

3. Error handling:
   - Graceful null/undefined handling
   - User-friendly error messages
   - Fallback UI if data missing
   - Logging errors to monitoring service

4. Testing:
   - Unit tests for service layer
   - Component snapshot tests
   - Integration tests for data flow
   - E2E tests for full report viewing

5. Documentation:
   - Component prop documentation
   - Data shape documentation
   - Usage examples
   - Troubleshooting guide
```

---

## Implementation Order

Follow this order to build progressively:

1. **Foundation** (Prompts 1-3)
   - API service
   - Main layout
   - Overview tab

2. **Environmental** (Prompts 4-9)
   - Section structure
   - Climate, GHG, Energy, Water, Waste details

3. **Social** (Prompts 10-13)
   - Section structure
   - Workforce, DEI, Health & Safety

4. **Governance** (Prompts 14-16)
   - Section structure
   - Board, Ethics & Compliance

5. **Finalization** (Prompts 17-20)
   - Conclusion & export
   - Reusable components
   - Styling
   - Testing & optimization

---

## Notes for Implementation

- **Narrative Numbers**: All numbers in narratives come from controls. Never format or modify them in display.
- **Null Handling**: Sections can be null. Always check `if (data.environmental.ghg)` before rendering.
- **Persian Support**: Use `direction: rtl` on body, handle text alignment properly.
- **Responsive Images**: Use responsive image techniques for charts.
- **Accessibility**: Each data visualization must have accessible alternative text.
- **Performance**: Cache API response for 1 day or until user triggers refresh.
- **Mobile-First**: Start with mobile layout, then enhance for larger screens.

---

## Example Data Handling

```javascript
// In your component
if (report.environmental?.ghg) {
  renderGHGSection(report.environmental.ghg, report.narratives.environmental.ghg);
} else {
  // Show placeholder or skip section
}

// Extracting narrative numbers
const narrative = report.narratives.environmental.ghg.body;
// Text already contains all numbers from database
// e.g., "... Scope 1 معادل 45000 تن معادل دی‌اکسید کربن است"
```

---

## Success Criteria

✅ Report loads and displays all sections
✅ All narratives have real numbers from database
✅ Responsive design works on all devices
✅ Export to PDF and Excel working
✅ Persian language displays correctly (RTL)
✅ All sections null-safe
✅ Performance < 3s load time
✅ Accessibility WCAG AA compliant
✅ No console errors
✅ Unit tests passing

Ready to implement! Start with Prompt 1 in your Copilot 🚀
