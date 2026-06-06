# Common Customizations for ReportPdfContainer.vue

## 1. Change Color Scheme

### Option A: CSS Override

Create `src/assets/report-theme.css`:

```css
:root {
  --env-color: #2d7a4a;      /* Environmental (green) */
  --social-color: #1e5a96;   /* Social (blue) */
  --gov-color: #b5860e;      /* Governance (gold) */
}

.env-header {
  background-color: var(--env-color) !important;
}

.social-header {
  background-color: var(--social-color) !important;
}

.gov-header {
  background-color: var(--gov-color) !important;
}
```

Then import in component:

```javascript
import '@/assets/report-theme.css'
```

### Option B: Direct Color Change in Component

Edit the `getCardClass` method:

```javascript
function getCardClass(index) {
  const colors = [
    'border-t-4 border-t-teal-600',    // Change green to teal
    'border-t-4 border-t-indigo-600',  // Change blue to indigo
    'border-t-4 border-t-amber-600'    // Change gold to amber
  ]
  return colors[index % 3]
}
```

---

## 2. Add Custom Logo or Company Name

Edit the template's cover page section:

```vue
<!-- Replace this: -->
<div class="w-24 h-24 border-4 border-white border-opacity-30 rounded-full flex items-center justify-center text-4xl font-bold mb-8 relative" style="color: #d6f0e3">
  ESG
</div>

<!-- With this: -->
<img src="@/assets/company-logo.png" alt="Company Logo" class="h-24 mb-8" />

<!-- Or this: -->
<div class="text-center mb-8">
  <h4 class="text-2xl font-bold text-white">شرکت خودرو</h4>
  <p class="text-sm opacity-75">تقرير الاستدامة</p>
</div>
```

---

## 3. Customize Report Title and Subtitle

Edit in the template's cover page:

```vue
<!-- Change from: -->
<h1 class="text-5xl font-bold text-center leading-tight mb-3 relative">
  گزارش پایداری<br />زیست‌محیطی، اجتماعی و حاکمیتی
</h1>
<h2 class="text-2xl font-light text-center opacity-75 mb-12 relative">
  Environmental · Social · Governance
</h2>

<!-- To: -->
<h1 class="text-5xl font-bold text-center leading-tight mb-3 relative">
  گزارش پایداری و مسئولیت‌پذیری اجتماعی<br />{{ reportYear }}
</h1>
<h2 class="text-2xl font-light text-center opacity-75 mb-12 relative">
  Sustainability & CSR Report
</h2>
```

---

## 4. Modify Key Statistics Display

To show different KPIs on cover page:

```javascript
// Edit the computed property:
const keyStats = computed(() => {
  if (!props.response?.data?.key_figures) return []
  
  // Show SPECIFIC KPIs instead of first 4
  const priorityKPIs = [
    'total-employees',
    'total-ghg',
    'renewable-energy-percent',
    'female-leadership-percent'
  ]
  
  const stats = props.response.data.key_figures
    .filter(kf => priorityKPIs.includes(kf.slug))
    .map(kf => ({
      label: kf.title,
      value: kf.answer,
      unit: kf.answer_unit
    }))
  
  return stats
})
```

---

## 5. Add Filters or Parameters to Report

Modify parent component:

```vue
<template>
  <div>
    <!-- Filters -->
    <div class="p-4 bg-white border-b">
      <div class="flex gap-4">
        <select v-model="year" class="px-4 py-2 border rounded">
          <option value="1405">۱۴۰۵</option>
          <option value="1404">۱۴۰۴</option>
          <option value="1403">۱۴۰۳</option>
        </select>
        
        <select v-model="division" class="px-4 py-2 border rounded">
          <option value="">تمام بخش‌ها</option>
          <option value="manufacturing">تولید</option>
          <option value="sales">فروش</option>
        </select>
        
        <button @click="loadReport" class="px-4 py-2 bg-green-600 text-white rounded">
          بارگذاری
        </button>
      </div>
    </div>

    <!-- Report -->
    <ReportPdfContainer :response="response" v-if="response" />
  </div>
</template>

<script setup>
const year = ref('1405')
const division = ref('')

async function loadReport() {
  const params = {
    year: year.value,
    division: division.value
  }
  response.value = await esgRepo.report(params)
}
</script>
```

---

## 6. Add Company Details Footer

Add to the conclusion page section:

```vue
<!-- Add this after the conclusion narrative: -->
<div class="mt-12 pt-8 border-t border-gray-300">
  <!-- Report Metadata -->
  <div class="grid grid-cols-3 gap-4 mb-8">
    <div class="text-center">
      <p class="text-xs text-gray-500">تاریخ انتشار</p>
      <p class="text-sm font-semibold text-gray-900">{{ formatDateTime(response.data.meta.generated_at) }}</p>
    </div>
    <div class="text-center">
      <p class="text-xs text-gray-500">سال مالی</p>
      <p class="text-sm font-semibold text-gray-900">{{ response.data.meta.reporting_year }}</p>
    </div>
    <div class="text-center">
      <p class="text-xs text-gray-500">شاخص‌های پوشش‌داده‌شده</p>
      <p class="text-sm font-semibold text-gray-900">{{ response.data.meta.answered_controls }} از {{ response.data.meta.total_controls }}</p>
    </div>
  </div>

  <!-- Company Info -->
  <div class="text-center text-xs text-gray-500">
    <p><strong>شرکت:</strong> نام شرکت شما</p>
    <p><strong>آدرس:</strong> تهران، خیابان فلان</p>
    <p><strong>تماس:</strong> info@company.com</p>
  </div>

  <!-- Frameworks -->
  <div class="text-center mt-4">
    <p class="text-xs text-gray-500 mb-2">چارچوب‌های استفاده‌شده:</p>
    <div class="flex flex-wrap justify-center gap-2">
      <span v-for="fw in ['GRI', 'SASB', 'TCFD']" :key="fw" class="text-xs bg-gray-100 px-2 py-1 rounded">
        {{ fw }}
      </span>
    </div>
  </div>
</div>
```

---

## 7. Add Interactive PDF Annotations

```javascript
// Add to the downloadPDF method for annotations:
async function downloadPDFWithAnnotations() {
  downloading.value = true
  try {
    const { jsPDF } = window
    const pdf = new jsPDF({
      orientation: 'portrait',
      unit: 'mm',
      format: 'a4'
    })

    // Add metadata
    pdf.setProperties({
      title: `ESG Report ${reportYear.value}`,
      subject: 'Environmental, Social, and Governance Report',
      author: 'ESG Reporting System',
      keywords: 'ESG, Sustainability, Report',
      creator: 'ReportPdfContainer'
    })

    // Add content...
    
    pdf.save(`ESG-Report-${reportYear.value}.pdf`)
  } finally {
    downloading.value = false
  }
}
```

---

## 8. Multi-Language Support

Create a composable for translations:

```javascript
// composables/useReportTranslations.ts
export const translations = {
  fa: {
    coverTitle: 'گزارش پایداری',
    coverSubtitle: 'Environmental · Social · Governance',
    keyFigures: 'شاخص‌های کلیدی',
    environmental: 'محیط‌زیستی',
    social: 'اجتماعی',
    governance: 'حاکمیتی',
    downloadPDF: 'دانلود PDF',
    loading: 'در حال بارگذاری...'
  },
  en: {
    coverTitle: 'Sustainability Report',
    coverSubtitle: 'Environmental · Social · Governance',
    keyFigures: 'Key Figures',
    environmental: 'Environmental',
    social: 'Social',
    governance: 'Governance',
    downloadPDF: 'Download PDF',
    loading: 'Loading...'
  }
}

export function useReportTranslations(lang = 'fa') {
  return translations[lang]
}
```

Then use in component:

```javascript
import { useReportTranslations } from '@/composables/useReportTranslations'

const t = useReportTranslations('fa')

// Use: {{ t.coverTitle }}
```

---

## 9. Dark Mode Support

Add computed property:

```javascript
const isDarkMode = computed(() => {
  return window.matchMedia('(prefers-color-scheme: dark)').matches
})
```

Then conditionally apply styles:

```vue
<div :class="[isDarkMode ? 'bg-gray-900 text-white' : 'bg-white text-gray-900']">
  <!-- Content -->
</div>
```

---

## 10. Add Print Optimization

Override print styles:

```vue
<style scoped>
@media print {
  /* Optimize for printing */
  .shadow-xl {
    box-shadow: none !important;
  }

  .gap-3 {
    gap: 0.25rem !important;
  }

  .px-6 {
    padding-left: 0;
    padding-right: 0;
  }

  .py-3 {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
  }

  /* Force page breaks */
  .page {
    page-break-after: always;
    page-break-inside: avoid;
  }

  /* Hide interactive elements */
  button,
  .toolbar {
    display: none !important;
  }

  /* Reduce font sizes for better fit */
  body {
    font-size: 11px;
  }

  h1 {
    font-size: 24px;
  }

  h2 {
    font-size: 16px;
  }

  h3 {
    font-size: 13px;
  }

  /* Ensure white background */
  * {
    background-color: white !important;
    color: black !important;
  }
}
</style>
```

---

## 11. Add Data Refresh Button

Add to parent component:

```vue
<template>
  <div>
    <div class="flex justify-between items-center p-4 bg-white border-b">
      <h1 class="text-2xl font-bold">گزارش ESG</h1>
      <div class="flex gap-2">
        <button
          @click="loadReport"
          :disabled="loading"
          class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
        >
          {{ loading ? 'بارگذاری...' : 'بازخوانی' }}
        </button>
        <button
          @click="exportJSON"
          class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"
        >
          خروجی JSON
        </button>
      </div>
    </div>

    <ReportPdfContainer :response="response" />
  </div>
</template>

<script setup>
function exportJSON() {
  const dataStr = JSON.stringify(response.value, null, 2)
  const blob = new Blob([dataStr], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `report-${new Date().toISOString().split('T')[0]}.json`
  link.click()
}
</script>
```

---

## 12. Performance: Lazy Load Sections

```javascript
// Load sections on-demand instead of all at once
const visibleSections = ref(['page1', 'page2'])

const environmentalVisible = computed(() => visibleSections.value.includes('page3'))
const socialVisible = computed(() => visibleSections.value.includes('page4'))
const governanceVisible = computed(() => visibleSections.value.includes('page5'))
```

Then use:

```vue
<!-- PAGE 3: ENVIRONMENTAL SECTION (Lazy) -->
<Suspense v-if="environmentalVisible">
  <template #default>
    <!-- Environmental content -->
  </template>
  <template #fallback>
    <div class="p-8 text-center">بارگذاری بخش محیط‌زیستی...</div>
  </template>
</Suspense>
```

---

## 13. Custom Chart Integration

```javascript
// Add to dependencies:
import { Chart } from 'chart.js'

// Create chart for a KPI:
function createGHGChart(container, data) {
  const ctx = container.getContext('2d')
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Scope 1', 'Scope 2', 'Scope 3'],
      datasets: [{
        data: [1200, 2300, 5200],
        backgroundColor: ['#16a34a', '#3b82f6', '#f59e0b']
      }]
    }
  })
}
```

---

## 14. Responsive Design for Mobile

```vue
<style scoped>
/* Adjust for small screens */
@media (max-width: 768px) {
  .max-w-3xl {
    max-width: 100% !important;
  }

  .grid-cols-3 {
    @apply grid-cols-1 lg:grid-cols-2;
  }

  .text-5xl {
    @apply text-3xl;
  }

  .gap-8 {
    @apply gap-4;
  }

  /* Stack cover stats vertically */
  .flex.gap-8 {
    flex-direction: column;
  }
}
</style>
```

---

## 15. Accessibility Improvements

```vue
<!-- Add ARIA labels -->
<button
  @click="downloadPDF"
  :disabled="downloading"
  :aria-label="`${downloading ? 'در حال دانلود' : 'دانلود PDF'} گزارش ESG`"
  class="bg-green-600 text-white px-5 py-2 rounded-lg"
>
  ⬇ {{ downloading ? 'در حال دانلود...' : 'دانلود PDF' }}
</button>

<!-- Add semantic HTML -->
<article>
  <header class="bg-green-800 text-white">
    <h1>گزارش پایداری</h1>
  </header>
  
  <section aria-label="شاخص‌های کلیدی">
    <!-- Key figures -->
  </section>

  <section aria-label="بخش محیط‌زیستی">
    <!-- Environmental content -->
  </section>
</article>

<!-- Improve contrast -->
<style scoped>
/* Ensure WCAG AA compliance */
.text-gray-500 {
  @apply text-gray-600; /* Darker for better contrast */
}

/* Focus indicators -->
button:focus-visible {
  outline: 2px solid #0066cc;
  outline-offset: 2px;
}
</style>
```

---

## Quick Tips

✅ **Test on Different Devices:** Use Chrome DevTools device emulation  
✅ **Check Color Contrast:** Use WebAIM contrast checker  
✅ **Optimize Images:** Compress before using in report  
✅ **Monitor Performance:** Use Vue DevTools Profiler  
✅ **Document Changes:** Keep a changelog of customizations  
✅ **Version Components:** Tag component versions for easy rollback  

---

For more help, refer to:
- `REPORT_VUE_COMPONENT_GUIDE.md` — Complete component documentation
- `REPORT_INTEGRATION_EXAMPLE.ts` — Integration code samples
- `REPORT_DATA_SCHEMA.md` — Data structure reference
