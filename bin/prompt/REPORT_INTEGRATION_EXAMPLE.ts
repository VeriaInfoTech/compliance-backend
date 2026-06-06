/**
 * INTEGRATION GUIDE: ReportPdfContainer.vue
 * 
 * Quick reference for integrating the ESG Report component into your Vue 3 application.
 */

// ════════════════════════════════════════════════════════════════════════════════════
// STEP 1: Import the component
// ════════════════════════════════════════════════════════════════════════════════════

import ReportPdfContainer from '@/components/ReportPdfContainer.vue'

// ════════════════════════════════════════════════════════════════════════════════════
// STEP 2: Create a report page/dashboard component
// ════════════════════════════════════════════════════════════════════════════════════

// File: src/views/ReportDashboard.vue
`
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading state -->
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
        <p class="text-gray-600 mt-4">در حال بارگذاری گزارش...</p>
      </div>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="p-8 bg-red-50 border border-red-200 rounded-lg m-6">
      <h3 class="text-red-900 font-semibold mb-2">خطا در بارگذاری گزارش</h3>
      <p class="text-red-700">{{ error }}</p>
      <button
        @click="loadReport"
        class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
      >
        تلاش دوباره
      </button>
    </div>

    <!-- Report view -->
    <ReportPdfContainer v-else-if="response?.data" :response="response" />

    <!-- Empty state -->
    <div v-else class="p-8 text-center text-gray-500">
      <p>داده‌ای برای نمایش وجود ندارد</p>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import ReportPdfContainer from '@/components/ReportPdfContainer.vue'
import { esgRepo } from '@/core/repositories/esgRepo'

const response = ref({})
const loading = ref(false)
const error = ref('')

async function loadReport() {
  loading.value = true
  error.value = ''
  
  try {
    response.value = await esgRepo.report({})
    
    if (!response.value?.data) {
      error.value = 'ساختار پاسخ API نادرست است'
    }
  } catch (err) {
    error.value = err.message || 'خطا در بارگذاری گزارش'
    console.error('Report load error:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => loadReport())
</script>
`

// ════════════════════════════════════════════════════════════════════════════════════
// STEP 3: Setup the API repository
// ════════════════════════════════════════════════════════════════════════════════════

// File: src/core/repositories/esgRepo.ts
export const esgRepo = {
  /**
   * Fetch ESG report data from backend
   * @param {Object} params - Query parameters
   * @returns {Promise<Object>} Report response with structure:
   *   {
   *     result: boolean,
   *     data: ReportData,
   *     error?: ErrorDetail[]
   *   }
   */
  report: async (params = {}) => {
    try {
      const response = await fetch('/api/esg/report', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
        },
        body: JSON.stringify(params)
      })

      if (!response.ok) {
        throw new Error(`API Error: ${response.status} ${response.statusText}`)
      }

      const data = await response.json()

      if (!data.result) {
        throw new Error(data.error?.[0]?.message || 'Failed to generate report')
      }

      return data
    } catch (error) {
      console.error('ESG Report API Error:', error)
      throw error
    }
  }
}

// ════════════════════════════════════════════════════════════════════════════════════
// STEP 4: Register in router (optional)
// ════════════════════════════════════════════════════════════════════════════════════

// File: src/router/index.ts
import ReportDashboard from '@/views/ReportDashboard.vue'

export const routes = [
  {
    path: '/report',
    name: 'report',
    component: ReportDashboard,
    meta: { title: 'گزارش پایداری ESG' }
  }
]

// ════════════════════════════════════════════════════════════════════════════════════
// STEP 5: Add required libraries to index.html
// ════════════════════════════════════════════════════════════════════════════════════

`
<!DOCTYPE html>
<html lang="fa" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ESG Report</title>
  </head>
  <body>
    <div id="app"></div>

    <!-- PDF Export Libraries (must load before component) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script type="module" src="/src/main.ts"></script>
  </body>
</html>
`

// ════════════════════════════════════════════════════════════════════════════════════
// STEP 6: Tailwind CSS Configuration (if not already configured)
// ════════════════════════════════════════════════════════════════════════════════════

// File: tailwind.config.js
module.exports = {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}'
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Vazirmatn', 'sans-serif']
      },
      colors: {
        green: {
          600: '#16a34a',
          700: '#15803d',
          800: '#166534'
        },
        blue: {
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af'
        },
        yellow: {
          700: '#b45309'
        }
      }
    }
  },
  plugins: []
}

// ════════════════════════════════════════════════════════════════════════════════════
// STEP 7: Font inclusion (add to index.html head or import)
// ════════════════════════════════════════════════════════════════════════════════════

`
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
  * {
    font-family: 'Vazirmatn', sans-serif;
  }
</style>
`

// ════════════════════════════════════════════════════════════════════════════════════
// ADVANCED: Custom Styling (Optional)
// ════════════════════════════════════════════════════════════════════════════════════

// File: src/assets/report-overrides.css
`
/* Override component colors if needed */
:root {
  --report-green: #0a5c3a;
  --report-green-mid: #1a8c5a;
  --report-green-light: #d6f0e3;
  --report-blue: #0d3460;
  --report-gold: #c9973a;
}

/* Custom print styles */
@media print {
  body {
    background: white;
  }

  .no-print {
    display: none !important;
  }

  .page {
    page-break-after: always;
    box-shadow: none !important;
    margin: 0 !important;
  }
}

/* Smooth transitions */
.report-enter-active,
.report-leave-active {
  transition: opacity 0.3s ease;
}

.report-enter-from,
.report-leave-to {
  opacity: 0;
}
`

// ════════════════════════════════════════════════════════════════════════════════════
// CACHING STRATEGY (Optional but recommended)
// ════════════════════════════════════════════════════════════════════════════════════

export const reportCache = {
  CACHE_KEY_PREFIX: 'esg_report_',
  CACHE_DURATION: 24 * 60 * 60 * 1000, // 24 hours

  getCacheKey: () => {
    const today = new Date().toISOString().split('T')[0]
    return `${reportCache.CACHE_KEY_PREFIX}${today}`
  },

  get: () => {
    const cacheKey = reportCache.getCacheKey()
    const cached = localStorage.getItem(cacheKey)
    
    if (!cached) return null

    try {
      const data = JSON.parse(cached)
      const age = Date.now() - data.timestamp

      // Check if cache is still valid
      if (age > reportCache.CACHE_DURATION) {
        localStorage.removeItem(cacheKey)
        return null
      }

      return data.report
    } catch (error) {
      console.error('Cache parse error:', error)
      return null
    }
  },

  set: (report) => {
    const cacheKey = reportCache.getCacheKey()
    localStorage.setItem(
      cacheKey,
      JSON.stringify({
        report,
        timestamp: Date.now()
      })
    )
  },

  clear: () => {
    const cacheKey = reportCache.getCacheKey()
    localStorage.removeItem(cacheKey)
  }
}

// Updated loadReport function with caching:
async function loadReportWithCache() {
  // Check cache first
  const cached = reportCache.get()
  if (cached) {
    console.log('Using cached report')
    response.value = { data: cached }
    return
  }

  // Fetch from API
  loading.value = true
  try {
    response.value = await esgRepo.report({})
    if (response.value?.data) {
      reportCache.set(response.value.data)
    }
  } catch (err) {
    error.value = err.message
  } finally {
    loading.value = false
  }
}

// ════════════════════════════════════════════════════════════════════════════════════
// TESTING EXAMPLE
// ════════════════════════════════════════════════════════════════════════════════════

// File: src/__tests__/ReportPdfContainer.spec.ts
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ReportPdfContainer from '@/components/ReportPdfContainer.vue'

// Mock sample data
const mockReportData = {
  data: {
    meta: {
      generated_at: '2026-06-06 12:00:00',
      reporting_year: 1405,
      total_items: 224,
      total_domains: 32,
      total_controls: 192,
      answered_controls: 191,
      note: 'از مجموع ۱۹۲ کنترل، ۱۹۱ کنترل answered پوشش داده شد.'
    },
    key_figures: [
      {
        slug: 'total-ghg',
        title: 'کل انتشار GHG',
        answer: 11700,
        answer_unit: 'تن CO₂e'
      }
    ],
    environmental: {
      'greenhouse-gas-emissions': [
        {
          type: 'control',
          parent_slug: 'greenhouse-gas-emissions',
          slug: 'scope1',
          title: 'Scope 1',
          answer: 1200,
          answer_unit: 'تن'
        }
      ]
    },
    social: {},
    governance: {},
    narratives: {
      environmental: {
        intro: { title: 'مقدمه', body: 'متن مقدمه' }
      },
      social: { intro: { title: 'مقدمه', body: 'متن' } },
      governance: { intro: { title: 'مقدمه', body: 'متن' } },
      report_conclusion: { title: 'نتیجه‌گیری', body: 'متن نتیجه‌گیری' }
    }
  }
}

describe('ReportPdfContainer', () => {
  it('renders with valid response', () => {
    const wrapper = mount(ReportPdfContainer, {
      props: { response: mockReportData }
    })
    expect(wrapper.exists()).toBe(true)
  })

  it('handles empty response gracefully', () => {
    const wrapper = mount(ReportPdfContainer, {
      props: { response: {} }
    })
    expect(wrapper.find('[id="page1"]').exists()).toBe(false)
  })

  it('formats numbers correctly', async () => {
    const wrapper = mount(ReportPdfContainer, {
      props: { response: mockReportData }
    })
    
    // Test number formatting
    const formatted = wrapper.vm.formatNumber(11700)
    expect(formatted).toContain('11')
  })

  it('shows report year', async () => {
    const wrapper = mount(ReportPdfContainer, {
      props: { response: mockReportData }
    })
    
    expect(wrapper.text()).toContain('1405')
  })
})

// ════════════════════════════════════════════════════════════════════════════════════
// ENVIRONMENT VARIABLES (Optional)
// ════════════════════════════════════════════════════════════════════════════════════

// File: .env.local
VITE_API_ENDPOINT=http://localhost:8080/api
VITE_ESG_REPORT_ENDPOINT=/esg/report

// Usage in component:
const API_URL = import.meta.env.VITE_API_ENDPOINT
const reportEndpoint = `${API_URL}${import.meta.env.VITE_ESG_REPORT_ENDPOINT}`

// ════════════════════════════════════════════════════════════════════════════════════
// TYPESCRIPT TYPES (Optional but recommended)
// ════════════════════════════════════════════════════════════════════════════════════

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
  total_items?: number
  total_domains?: number
  total_controls?: number
  answered_controls?: number
  note?: string
  sections?: Record<string, SectionStats>
}

export interface SectionStats {
  domains: number
  answered_controls: number
}

export interface KeyFigure {
  slug: string
  title: string
  parent_slug?: string
  answer: string | number | null
  answer_unit?: string
  metric_code?: string
  kpi_code?: string
}

export interface Control {
  type: 'control'
  parent_slug: string
  slug: string
  title: string
  summary?: string
  answer?: string | number | null
  answer_unit?: string
  answer_type?: 'number' | 'text' | 'percentage' | 'date' | 'choice'
  metric_code?: string
  kpi_code?: string
  frameworks?: string[]
}

export type SectionData = Record<string, Control[] | null>

export interface NarrativeSection {
  title: string
  body: string
}

export interface NarrativesData {
  about_report?: NarrativeSection
  environmental: Record<string, NarrativeSection>
  social: Record<string, NarrativeSection>
  governance: Record<string, NarrativeSection>
  report_conclusion: NarrativeSection
}

export interface ErrorDetail {
  message: string
  code?: string | number
}

// Use in component:
import type { ReportResponse } from '@/types/report'

defineProps<{
  response: ReportResponse
}>()
