# ReportPdfContainer.vue - ESG Report Display Component

## Overview

**ReportPdfContainer.vue** is a professional Vue 3 component that displays comprehensive ESG (Environmental, Social, and Governance) reports with a modern, PDF-ready design. It handles dynamic data from the backend Report Generator Service and presents it beautifully using Tailwind CSS.

---

## Features

✅ **Multi-page layout** with 6 professional pages  
✅ **Responsive design** using Tailwind CSS  
✅ **RTL (Persian) support** with proper text direction  
✅ **Data-driven content** from backend API  
✅ **Graceful error handling** for missing data  
✅ **Beautiful cover page** with gradient background  
✅ **Dynamic key figures** grid with 12-item display  
✅ **Per-section organization** (Environmental, Social, Governance)  
✅ **Rich narratives** with data-driven insights  
✅ **Print-friendly** styling  
✅ **PDF download** button with html2canvas integration  

---

## Installation

### 1. Place the Component

Copy `ReportPdfContainer.vue` to your Vue project components folder:

```bash
src/components/ReportPdfContainer.vue
```

### 2. Ensure Required Libraries

The component requires:
- Vue 3.x
- Tailwind CSS (already configured)
- `html2canvas` for PDF export
- `jsPDF` for PDF generation

If not already installed, add them:

```bash
npm install html2canvas jspdf
```

### 3. Add to Global HTML (for PDF export)

In your main `index.html`, add these scripts **before closing `</body>` tag**:

```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
```

Or install via npm and import:

```javascript
import html2canvas from 'html2canvas'
import jsPDF from 'jspdf'
```

---

## Usage

### Basic Setup

In your parent component (e.g., `Dashboard.vue` or `ReportPage.vue`):

```vue
<template>
  <div>
    <ReportPdfContainer :response="response" v-if="response" />
    <div v-else class="p-8 text-center text-gray-500">
      در حال بارگذاری...
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import ReportPdfContainer from '@/components/ReportPdfContainer.vue'
import { esgRepo } from '@/core/repositories/esgRepo'

const response = ref({})

async function loadDashboard() {
  try {
    response.value = await esgRepo.report({})
  } catch (error) {
    console.error('Failed to load report:', error)
  }
}

onMounted(() => loadDashboard())
</script>
```

### Props

| Prop | Type | Required | Description |
|------|------|----------|-------------|
| `response` | `Object` | ✓ | Full API response containing `data` object with report structure |

### Response Structure

The component expects `response` to have this structure:

```javascript
{
  result: true,
  data: {
    meta: {
      generated_at: "2026-06-05 14:00:00",
      reporting_year: 1405,
      total_items: 224,
      total_domains: 32,
      total_controls: 192,
      answered_controls: 191,
      note: "از مجموع ۱۹۲ کنترل، ۱۹۱ کنترل answered پوشش داده شد.",
      sections: {
        environmental: { domains: 7, answered_controls: 50 },
        social: { domains: 3, answered_controls: 40 },
        governance: { domains: 3, answered_controls: 30 }
      }
    },
    key_figures: [
      { slug: "total-ghg", title: "کل انتشار GHG", answer: 11700, answer_unit: "تن CO₂e" },
      // ... more KPIs
    ],
    environmental: {
      "greenhouse-gas-emissions": [ { title: "Scope 1", answer: 1200, ... }, ... ],
      // ... more domains
    },
    social: {
      "workforce-structure": [ ... ],
      // ... more domains
    },
    governance: {
      "corporate-governance-structure": [ ... ],
      // ... more domains
    },
    narratives: {
      environmental: {
        intro: { title: "...", body: "..." },
        ghg: { title: "...", body: "..." },
        // ... more subsections
      },
      social: { ... },
      governance: { ... },
      report_conclusion: { title: "...", body: "..." }
    }
  }
}
```

---

## Pages Included

### Page 1: Cover Page
- ESG logo badge
- Report year and English translation
- Main title: "گزارش پایداری"
- Top 4 key statistics
- Supported frameworks
- Professional gradient background

### Page 2: Key Figures & Summary
- Report metadata (date, year, control counts)
- 12-item KPI grid (3 columns)
- Per-section statistics (Environmental/Social/Governance)
- Color-coded cards by metric type

### Page 3: Environmental Section
- Environmental domain controls
- GHG, Energy, Water, Waste, etc.
- Narratives for each subsection
- Control cards with values and units

### Page 4: Social Section
- Social domain controls
- Workforce, DEI, Health & Safety
- Narratives for each subsection
- Structured presentation

### Page 5: Governance Section
- Governance domain controls
- Board, Ethics, Compliance
- Narratives for each subsection
- Professional layout

### Page 6: Conclusion
- Comprehensive conclusion narrative
- Report footer with generation date
- Report metadata summary

---

## Styling & Customization

### Color Scheme

The component uses three primary colors:

| Section | Color | Tailwind | Hex |
|---------|-------|----------|-----|
| Environmental | Green | `green-600` | #16a34a |
| Social | Blue | `blue-600` | #2563eb |
| Governance | Gold | `yellow-700` | #b45309 |

### Customizing Colors

To change colors, edit the computed property classes or override in your stylesheet:

```css
/* Override section colors */
.environmental-header {
  @apply bg-teal-700; /* Change from green to teal */
}

.social-header {
  @apply bg-indigo-700; /* Change from blue to indigo */
}

.governance-header {
  @apply bg-amber-700; /* Change from gold to amber */
}
```

### Print Styles

The component includes print-optimized styles. To customize print behavior, modify the `@media print` section in the component's `<style>` tag.

---

## Handling Missing Data

The component gracefully handles missing or incomplete data:

```javascript
// Missing key_figures
v-if="response.data.key_figures"

// Missing narratives
v-if="response.data.narratives?.environmental?.intro"

// Empty sections
v-if="hasSection('environmental')"

// Null controls
v-if="controls && controls.length"

// Empty values
{{ formatNumber(value) }} // Returns '—' for null/empty
```

**Result:** No console errors or broken layouts even with incomplete data.

---

## Helper Methods

### `formatNumber(value)`

Formats numbers with Persian locale (comma separators):

```javascript
formatNumber(11700) // Returns: "۱۱,۷۰۰"
formatNumber(84500) // Returns: "۸۴,۵۰۰"
```

### `toPersianNumber(num)`

Converts digits to Persian numerals:

```javascript
toPersianNumber(1405) // Returns: "۱۴۰۵"
```

### `formatDateTime(dateStr)`

Formats datetime string to Persian date:

```javascript
formatDateTime("2026-06-05 14:00:00") // Returns: "۵ جون ۲۰۲۶"
```

### `domainLabel(slug)`

Maps domain slugs to Persian labels:

```javascript
domainLabel('greenhouse-gas-emissions')     // "انتشار گازهای گلخانه‌ای"
domainLabel('workforce-structure')          // "ساختار نیروی کار"
domainLabel('corporate-governance-structure') // "ساختار حاکمیت شرکتی"
```

### `hasSection(sectionName)`

Checks if a section has any controls:

```javascript
hasSection('environmental') // true if env controls exist
```

---

## PDF Export

### Manual Download

Click the **"⬇ دانلود PDF"** button in the toolbar.

### Programmatic Download

```javascript
// From parent component
const pdfRef = ref(null)

const handleDownloadPDF = async () => {
  await pdfRef.value.downloadPDF()
}
```

### Requirements

- `html2canvas` library loaded
- `jsPDF` library loaded
- Browser must allow canvas rendering

### Customizing PDF Filename

Edit the `downloadPDF()` method:

```javascript
// Change this line:
pdf.save(`ESG-Report-${reportYear.value}.pdf`)

// To:
pdf.save(`Sustainability-Report-${new Date().toISOString().split('T')[0]}.pdf`)
```

---

## Performance Tips

1. **Lazy Load Pages:** Only render visible pages if performance is critical

2. **Memoize Computed Properties:** For large datasets, use `useMemo`

3. **Debounce Updates:** Wrap response updates if called frequently

4. **Cache Report Data:** Store in localStorage to avoid repeated API calls

```javascript
const CACHE_KEY = `esg_report_${new Date().toISOString().split('T')[0]}`

async function loadDashboard() {
  const cached = localStorage.getItem(CACHE_KEY)
  if (cached) {
    response.value = JSON.parse(cached)
    return
  }
  
  response.value = await esgRepo.report({})
  localStorage.setItem(CACHE_KEY, JSON.stringify(response.value))
}
```

---

## Troubleshooting

### PDF Download Not Working

**Issue:** Click button does nothing  
**Solution:**
1. Ensure `html2canvas` and `jsPDF` scripts are loaded
2. Check browser console for errors
3. Verify canvas rendering is allowed

### Wrong Text Direction

**Issue:** Text displays left-to-right instead of right-to-left  
**Solution:** Component already has `dir="rtl"` in root. Check parent element doesn't override.

### Missing Narratives or Data

**Issue:** Some sections appear empty  
**Solution:**
1. Check backend response structure
2. Verify all required fields are present in `response.data`
3. Use browser DevTools to inspect API response

### Numbers Display Incorrectly

**Issue:** Persian numerals or formatting wrong  
**Solution:** Check the backend `formatAnswer()` method output. Component expects English numerals from API.

### Print/Print Preview Looks Wrong

**Issue:** Pages don't break correctly for printing  
**Solution:**
1. Use Chrome/Chromium (best print support)
2. Disable headers/footers in print dialog
3. Set margins to "None"
4. Use "Fit to page" scaling

---

## Integration with Backend

### API Endpoint

The component consumes the response from your ESG report API:

```javascript
// esgRepo.ts
export const esgRepo = {
  report: async (params = {}) => {
    const response = await fetch('/api/esg/report', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(params)
    })
    return response.json()
  }
}
```

### Expected API Response Format

```json
{
  "result": true,
  "data": {
    "meta": { /* ... */ },
    "key_figures": [ /* ... */ ],
    "environmental": { /* ... */ },
    "social": { /* ... */ },
    "governance": { /* ... */ },
    "narratives": { /* ... */ }
  }
}
```

---

## Browser Support

| Browser | Support | Notes |
|---------|---------|-------|
| Chrome 90+ | ✅ Full | Best performance |
| Firefox 88+ | ✅ Full | Good support |
| Safari 14+ | ✅ Full | RTL supported |
| Edge 90+ | ✅ Full | Chromium-based |
| IE 11 | ❌ Not supported | Use transpiler for older Vue |

---

## TypeScript Support

If using TypeScript, create an interface file:

```typescript
// types/Report.ts
export interface ReportResponse {
  result: boolean
  data: ReportData
  error?: ErrorDetail[]
}

export interface ReportData {
  meta: MetaData
  key_figures: KeyFigure[]
  environmental: SectionData
  social: SectionData
  governance: SectionData
  narratives: NarrativesData
}

export interface MetaData {
  generated_at: string
  reporting_year: number
  total_items: number
  total_domains: number
  total_controls: number
  answered_controls: number
  note: string
  sections: Record<string, SectionStats>
}

export interface SectionStats {
  domains: number
  answered_controls: number
}

// ... more interfaces
```

Then use in component:

```typescript
import type { ReportResponse } from '@/types/Report'

defineProps<{
  response: ReportResponse
}>()
```

---

## Future Enhancements

Possible improvements:

- [ ] Interactive charts using Chart.js
- [ ] Data table with sorting/filtering
- [ ] Year-over-year comparison
- [ ] Export to Excel/CSV
- [ ] Share report via email
- [ ] Custom branding/logo
- [ ] Multi-language support
- [ ] Accessibility (WCAG 2.1) improvements
- [ ] Mobile-optimized view
- [ ] Real-time data updates

---

## License

This component is part of the ESG Reporting System. For license details, see the main repository LICENSE file.

---

## Support

For issues or questions:

1. Check the **Troubleshooting** section above
2. Review the **REPORT_DATA_SCHEMA.md** for data structure details
3. Inspect browser console for error messages
4. Check backend logs if API integration fails

---

## Changelog

### v1.0.0 (2026-06-06)

- ✨ Initial release
- 6-page professional layout
- Multi-section narrative support
- PDF export capability
- Full Persian (RTL) support
- Tailwind CSS styling
- Graceful error handling
