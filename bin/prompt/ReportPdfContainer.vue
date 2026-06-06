<template>
  <div v-if="response?.data" dir="rtl" class="bg-gray-100 min-h-screen pt-16 pb-8">
    <!-- Toolbar -->
    <div class="fixed top-0 left-0 right-0 bg-gray-900 text-white px-6 py-3 flex items-center justify-between z-50 shadow-lg">
      <span class="text-sm opacity-85">{{ toolbarTitle }}</span>
      <button
        @click="downloadPDF"
        :disabled="downloading"
        class="bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white px-5 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors"
      >
        <span>⬇</span>
        <span>{{ downloading ? 'در حال دانلود...' : 'دانلود PDF' }}</span>
      </button>
    </div>

    <!-- Report Pages -->
    <div class="flex flex-col items-center gap-3 px-6">
      <!-- PAGE 1: COVER -->
      <div class="w-full max-w-3xl bg-white shadow-xl rounded-sm" id="page1">
        <div
          class="w-full bg-gradient-to-b from-blue-900 via-blue-800 to-green-700 text-white p-16 flex flex-col items-center justify-center min-h-screen relative overflow-hidden"
        >
          <!-- Decorative circles -->
          <div
            class="absolute inset-0 opacity-10 pointer-events-none"
            style="
              background-image: radial-gradient(circle at 20% 80%, rgba(26,140,90,0.3) 0%, transparent 50%),
                                radial-gradient(circle at 80% 20%, rgba(26,95,168,0.3) 0%, transparent 50%);
            "
          />

          <!-- Logo -->
          <div
            class="w-24 h-24 border-4 border-white border-opacity-30 rounded-full flex items-center justify-center text-4xl font-bold mb-8 relative"
            style="color: #d6f0e3"
          >
            ESG
          </div>

          <!-- Year Badge -->
          <div
            class="bg-white bg-opacity-10 border border-white border-opacity-25 rounded-full px-6 py-2 text-sm tracking-widest mb-6 relative"
          >
            گزارش دوره {{ reportYear }} — {{ englishYear }}
          </div>

          <!-- Main Title -->
          <h1 class="text-5xl font-bold text-center leading-tight mb-3 relative">
            گزارش پایداری<br />زیست‌محیطی، اجتماعی و حاکمیتی
          </h1>

          <!-- Subtitle -->
          <h2 class="text-2xl font-light text-center opacity-75 mb-12 relative">
            Environmental · Social · Governance
          </h2>

          <!-- Divider -->
          <div class="w-20 h-1 bg-yellow-500 rounded mb-12 relative"></div>

          <!-- Stats -->
          <div class="flex gap-8 mb-16 relative" v-if="keyStats.length">
            <div
              v-for="(stat, idx) in keyStats"
              :key="idx"
              class="bg-white bg-opacity-8 border border-white border-opacity-15 rounded-lg p-5 text-center"
            >
              <div class="text-4xl font-bold" style="color: #9de8c4">
                {{ formatNumber(stat.value) }}
              </div>
              <div class="text-xs opacity-70 mt-1">{{ stat.label }}</div>
            </div>
          </div>

          <!-- Frameworks -->
          <div class="flex flex-wrap gap-2 justify-center mb-12 relative" v-if="frameworks.length">
            <span
              v-for="(fw, idx) in frameworks"
              :key="idx"
              class="bg-white bg-opacity-10 border border-white border-opacity-20 rounded px-3 py-1 text-xs opacity-80"
            >
              {{ fw }}
            </span>
          </div>

          <!-- Footer -->
          <div class="absolute bottom-8 left-0 right-0 text-center text-xs opacity-50 relative">
            این گزارش بر اساس استانداردهای بین‌المللی GRI و SASB تهیه شده است
          </div>
        </div>
      </div>

      <!-- PAGE 2: KEY FIGURES & SUMMARY -->
      <div class="w-full max-w-3xl bg-white shadow-xl rounded-sm" id="page2" v-if="response.data.key_figures">
        <div class="min-h-screen p-12 relative">
          <!-- Header Stripe -->
          <div
            class="bg-gray-900 text-white px-0 py-4 mb-8 -mx-12 px-12 flex items-center justify-between"
          >
            <h2 class="text-lg font-semibold">شاخص‌های کلیدی عملکرد</h2>
            <span class="text-sm opacity-50">صفحه ۲</span>
          </div>

          <!-- Meta Info -->
          <div
            v-if="response.data.meta"
            class="bg-gray-50 border-r-4 border-green-600 rounded-r p-4 mb-8"
          >
            <p class="text-sm text-gray-700 leading-relaxed">
              <strong>تاریخ تهیه:</strong> {{ formatDateTime(response.data.meta.generated_at) }}<br />
              <strong>سال گزارش:</strong> {{ response.data.meta.reporting_year }}<br />
              <strong>تعداد کنترل‌ها:</strong>
              {{ response.data.meta.total_controls || 'نامشخص' }}<br />
              <strong>کنترل‌های پاسخ‌شده:</strong>
              {{ response.data.meta.answered_controls || 'نامشخص' }}<br />
              <span v-if="response.data.meta.note" class="text-xs text-gray-600 block mt-3">
                {{ response.data.meta.note }}
              </span>
            </p>
          </div>

          <!-- Key Figures Grid -->
          <div class="grid grid-cols-3 gap-3 mb-8">
            <div
              v-for="(kf, idx) in response.data.key_figures.slice(0, 12)"
              :key="idx"
              class="border border-gray-300 rounded-lg p-4 bg-white"
              :class="getCardClass(idx)"
            >
              <div class="text-xs text-gray-500 mb-2 line-clamp-2">{{ kf.title }}</div>
              <div class="text-2xl font-bold text-gray-900">
                {{ formatNumber(kf.answer) }}
              </div>
              <div v-if="kf.answer_unit" class="text-xs text-gray-500 mt-1">
                {{ kf.answer_unit }}
              </div>
            </div>
          </div>

          <!-- Sections Overview -->
          <div v-if="response.data.meta.sections" class="grid grid-cols-3 gap-4">
            <div
              v-for="(section, name) in response.data.meta.sections"
              :key="name"
              class="bg-blue-50 border-r-4 border-blue-600 rounded-r p-4"
            >
              <h3 class="font-semibold text-blue-900 text-sm mb-2">
                {{ sectionLabel(name) }}
              </h3>
              <p class="text-xs text-gray-700">
                <strong>دامنه‌ها:</strong> {{ section.domains }}<br />
                <strong>کنترل‌ها:</strong> {{ section.answered_controls }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- PAGE 3: ENVIRONMENTAL SECTION -->
      <div
        v-if="response.data.environmental && hasSection('environmental')"
        class="w-full max-w-3xl bg-white shadow-xl rounded-sm"
        id="page3"
      >
        <div class="min-h-screen p-12">
          <!-- Header Stripe -->
          <div class="bg-green-800 text-white px-0 py-4 mb-8 -mx-12 px-12 flex items-center justify-between">
            <h2 class="text-lg font-semibold">عملکرد زیست‌محیطی</h2>
            <span class="text-sm opacity-50">صفحة ۳</span>
          </div>

          <!-- Intro Narrative -->
          <div
            v-if="response.data.narratives?.environmental?.intro"
            class="bg-gray-50 border-r-4 border-green-600 rounded-r p-4 mb-8"
          >
            <h3 class="font-semibold text-gray-900 mb-2">{{ response.data.narratives.environmental.intro.title }}</h3>
            <p class="text-sm text-gray-700 leading-relaxed">
              {{ response.data.narratives.environmental.intro.body }}
            </p>
          </div>

          <!-- Environmental Domains -->
          <div v-for="(controls, domain) in environmentalDomains" :key="domain" class="mb-8">
            <h3 class="text-sm font-bold text-gray-900 border-r-4 border-green-600 pr-3 mb-4">
              {{ domainLabel(domain) }}
            </h3>

            <!-- Controls Grid -->
            <div v-if="controls && controls.length" class="grid grid-cols-2 gap-3 mb-6">
              <div
                v-for="(control, cidx) in controls.slice(0, 6)"
                :key="cidx"
                class="border border-gray-300 rounded-lg p-3 bg-white"
              >
                <div class="text-xs text-gray-500 mb-2">{{ control.title }}</div>
                <div class="text-xl font-bold text-gray-900">
                  {{ formatNumber(control.answer) }}
                </div>
                <div v-if="control.answer_unit" class="text-xs text-gray-500 mt-1">
                  {{ control.answer_unit }}
                </div>
              </div>
            </div>

            <!-- Narrative -->
            <div
              v-if="response.data.narratives?.environmental?.[domain]"
              class="bg-green-50 border-r-4 border-green-600 rounded-r p-3 text-xs leading-relaxed text-gray-700"
            >
              {{ response.data.narratives.environmental[domain].body }}
            </div>
          </div>
        </div>
      </div>

      <!-- PAGE 4: SOCIAL SECTION -->
      <div
        v-if="response.data.social && hasSection('social')"
        class="w-full max-w-3xl bg-white shadow-xl rounded-sm"
        id="page4"
      >
        <div class="min-h-screen p-12">
          <!-- Header Stripe -->
          <div class="bg-blue-600 text-white px-0 py-4 mb-8 -mx-12 px-12 flex items-center justify-between">
            <h2 class="text-lg font-semibold">عملکرد اجتماعی</h2>
            <span class="text-sm opacity-50">صفحة ۴</span>
          </div>

          <!-- Intro Narrative -->
          <div
            v-if="response.data.narratives?.social?.intro"
            class="bg-blue-50 border-r-4 border-blue-600 rounded-r p-4 mb-8"
          >
            <h3 class="font-semibold text-gray-900 mb-2">{{ response.data.narratives.social.intro.title }}</h3>
            <p class="text-sm text-gray-700 leading-relaxed">
              {{ response.data.narratives.social.intro.body }}
            </p>
          </div>

          <!-- Social Domains -->
          <div v-for="(controls, domain) in socialDomains" :key="domain" class="mb-8">
            <h3 class="text-sm font-bold text-gray-900 border-r-4 border-blue-600 pr-3 mb-4">
              {{ domainLabel(domain) }}
            </h3>

            <!-- Controls Grid -->
            <div v-if="controls && controls.length" class="grid grid-cols-2 gap-3 mb-6">
              <div
                v-for="(control, cidx) in controls.slice(0, 6)"
                :key="cidx"
                class="border border-gray-300 rounded-lg p-3 bg-white"
              >
                <div class="text-xs text-gray-500 mb-2">{{ control.title }}</div>
                <div class="text-xl font-bold text-gray-900">
                  {{ formatNumber(control.answer) }}
                </div>
                <div v-if="control.answer_unit" class="text-xs text-gray-500 mt-1">
                  {{ control.answer_unit }}
                </div>
              </div>
            </div>

            <!-- Narrative -->
            <div
              v-if="response.data.narratives?.social?.[domain]"
              class="bg-blue-50 border-r-4 border-blue-600 rounded-r p-3 text-xs leading-relaxed text-gray-700"
            >
              {{ response.data.narratives.social[domain].body }}
            </div>
          </div>
        </div>
      </div>

      <!-- PAGE 5: GOVERNANCE SECTION -->
      <div
        v-if="response.data.governance && hasSection('governance')"
        class="w-full max-w-3xl bg-white shadow-xl rounded-sm"
        id="page5"
      >
        <div class="min-h-screen p-12">
          <!-- Header Stripe -->
          <div class="bg-yellow-700 text-white px-0 py-4 mb-8 -mx-12 px-12 flex items-center justify-between">
            <h2 class="text-lg font-semibold">حاکمیت شرکتی</h2>
            <span class="text-sm opacity-50">صفحة ۵</span>
          </div>

          <!-- Intro Narrative -->
          <div
            v-if="response.data.narratives?.governance?.intro"
            class="bg-yellow-50 border-r-4 border-yellow-700 rounded-r p-4 mb-8"
          >
            <h3 class="font-semibold text-gray-900 mb-2">{{ response.data.narratives.governance.intro.title }}</h3>
            <p class="text-sm text-gray-700 leading-relaxed">
              {{ response.data.narratives.governance.intro.body }}
            </p>
          </div>

          <!-- Governance Domains -->
          <div v-for="(controls, domain) in governanceDomains" :key="domain" class="mb-8">
            <h3 class="text-sm font-bold text-gray-900 border-r-4 border-yellow-700 pr-3 mb-4">
              {{ domainLabel(domain) }}
            </h3>

            <!-- Controls Grid -->
            <div v-if="controls && controls.length" class="grid grid-cols-2 gap-3 mb-6">
              <div
                v-for="(control, cidx) in controls.slice(0, 6)"
                :key="cidx"
                class="border border-gray-300 rounded-lg p-3 bg-white"
              >
                <div class="text-xs text-gray-500 mb-2">{{ control.title }}</div>
                <div class="text-xl font-bold text-gray-900">
                  {{ formatNumber(control.answer) }}
                </div>
                <div v-if="control.answer_unit" class="text-xs text-gray-500 mt-1">
                  {{ control.answer_unit }}
                </div>
              </div>
            </div>

            <!-- Narrative -->
            <div
              v-if="response.data.narratives?.governance?.[domain]"
              class="bg-yellow-50 border-r-4 border-yellow-700 rounded-r p-3 text-xs leading-relaxed text-gray-700"
            >
              {{ response.data.narratives.governance[domain].body }}
            </div>
          </div>
        </div>
      </div>

      <!-- PAGE 6: CONCLUSION -->
      <div v-if="response.data.narratives?.report_conclusion" class="w-full max-w-3xl bg-white shadow-xl rounded-sm" id="page6">
        <div class="min-h-screen p-12">
          <!-- Header Stripe -->
          <div class="bg-gray-800 text-white px-0 py-4 mb-8 -mx-12 px-12 flex items-center justify-between">
            <h2 class="text-lg font-semibold">نتیجه‌گیری</h2>
            <span class="text-sm opacity-50">صفحة ۶</span>
          </div>

          <!-- Conclusion Narrative -->
          <div class="bg-gray-50 border-r-4 border-gray-800 rounded-r p-6">
            <h3 class="font-bold text-gray-900 text-base mb-4">
              {{ response.data.narratives.report_conclusion.title }}
            </h3>
            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">
              {{ response.data.narratives.report_conclusion.body }}
            </p>
          </div>

          <!-- Footer -->
          <div class="mt-12 pt-8 border-t border-gray-300 text-center">
            <p class="text-xs text-gray-500">
              تهیه‌شده در: {{ formatDateTime(response.data.meta.generated_at) }}
            </p>
            <p class="text-xs text-gray-400 mt-2">
              گزارش پایداری ESG | سال {{ response.data.meta.reporting_year }}
            </p>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!response.data" class="w-full max-w-3xl bg-white shadow p-8 rounded text-center">
        <p class="text-gray-500">داده‌ای برای نمایش وجود ندارد</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps } from 'vue'

const props = defineProps({
  response: {
    type: Object,
    default: () => ({})
  }
})

const downloading = ref(false)

// Computed properties
const reportYear = computed(() => {
  const year = props.response?.data?.meta?.reporting_year || new Date().getFullYear()
  return toPersianNumber(year)
})

const englishYear = computed(() => {
  const year = props.response?.data?.meta?.reporting_year || new Date().getFullYear()
  return year
})

const toolbarTitle = computed(() => {
  return `پیش‌نمایش گزارش پایداری ESG – دوره گزارش‌دهی ${reportYear.value}`
})

const keyStats = computed(() => {
  if (!props.response?.data?.key_figures) return []
  
  // Highlight top stats from key_figures
  const stats = props.response.data.key_figures
    .filter(kf => kf.answer && kf.answer !== '')
    .slice(0, 4)
    .map(kf => ({
      label: kf.title,
      value: kf.answer,
      unit: kf.answer_unit
    }))
  
  return stats.length > 0 ? stats : []
})

const frameworks = computed(() => {
  return ['GRI Standards', 'ISSB Sustainability', 'ISO 14001', 'CDP Framework', 'TCFD Framework', 'GHG Protocol']
})

const environmentalDomains = computed(() => {
  const env = props.response?.data?.environmental || {}
  return Object.fromEntries(
    Object.entries(env).filter(([key, value]) => value && Array.isArray(value) && value.length > 0)
  )
})

const socialDomains = computed(() => {
  const social = props.response?.data?.social || {}
  return Object.fromEntries(
    Object.entries(social).filter(([key, value]) => value && Array.isArray(value) && value.length > 0)
  )
})

const governanceDomains = computed(() => {
  const gov = props.response?.data?.governance || {}
  return Object.fromEntries(
    Object.entries(gov).filter(([key, value]) => value && Array.isArray(value) && value.length > 0)
  )
})

// Methods
function formatNumber(value) {
  if (value === null || value === undefined || value === '') return '—'
  const num = Number(value)
  if (isNaN(num)) return String(value)
  return new Intl.NumberFormat('fa-IR').format(num)
}

function toPersianNumber(num) {
  const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹']
  return String(num)
    .split('')
    .map(d => (isNaN(d) ? d : persianDigits[d]))
    .join('')
}

function formatDateTime(dateStr) {
  if (!dateStr) return '—'
  const date = new Date(dateStr)
  return new Intl.DateTimeFormat('fa-IR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  }).format(date)
}

function getCardClass(index) {
  const colors = ['border-t-4 border-t-green-600', 'border-t-4 border-t-blue-600', 'border-t-4 border-t-yellow-600']
  return colors[index % 3]
}

function domainLabel(slug) {
  const labels = {
    'greenhouse-gas-emissions': 'انتشار گازهای گلخانه‌ای',
    'energy-resource-management': 'مدیریت انرژی و منابع',
    'water-management': 'مدیریت آب',
    'waste-management-circular-economy': 'مدیریت پسماند و اقتصاد دایره‌ای',
    'biodiversity-ecosystem-impact': 'تنوع زیستی و تأثیرات اکوسیستم',
    'climate-change-strategy': 'استراتژی تغییرات آب‌وهوایی',
    'pollution-environmental-impact': 'آلودگی و تأثیرات محیط‌زیستی',
    'workforce-structure': 'ساختار نیروی کار',
    'diversity-equity-inclusion': 'تنوع، برابری و فراگیری',
    'health-safety-wellbeing': 'سلامت، ایمنی و بهزیستی',
    'corporate-governance-structure': 'ساختار حاکمیت شرکتی',
    'ethics-compliance': 'اخلاق‌شناسی و انطباق',
    'regulatory-compliance': 'انطباق نظارتی'
  }
  return labels[slug] || slug
}

function sectionLabel(name) {
  const labels = {
    environmental: 'محیط‌زیستی',
    social: 'اجتماعی',
    governance: 'حاکمیتی'
  }
  return labels[name] || name
}

function hasSection(sectionName) {
  const section = props.response?.data?.[sectionName]
  if (!section) return false
  return Object.values(section).some(controls => controls && Array.isArray(controls) && controls.length > 0)
}

async function downloadPDF() {
  downloading.value = true
  try {
    // Wait for libraries to load
    if (!window.html2canvas || !window.jsPDF) {
      console.error('PDF libraries not loaded')
      alert('لطفاً صفحه را دوباره لود کنید و سپس تلاش کنید.')
      return
    }

    // Capture the report as image
    const element = document.getElementById('page1')
    const canvas = await window.html2canvas(element, {
      scale: 2,
      useCORS: true,
      logging: false
    })

    // Create PDF
    const { jsPDF } = window
    const pdf = new jsPDF({
      orientation: 'portrait',
      unit: 'mm',
      format: 'a4'
    })

    const imgData = canvas.toDataURL('image/png')
    pdf.addImage(imgData, 'PNG', 0, 0, 210, 297)

    // Download
    pdf.save(`ESG-Report-${reportYear.value}.pdf`)
  } catch (error) {
    console.error('Error downloading PDF:', error)
    alert('خطا در دانلود PDF. لطفاً دوباره تلاش کنید.')
  } finally {
    downloading.value = false
  }
}
</script>

<style scoped>
/* Print styles */
@media print {
  .fixed {
    display: none;
  }

  .shadow-xl {
    box-shadow: none !important;
  }

  .px-6 {
    padding-left: 0;
    padding-right: 0;
  }

  .gap-3 {
    gap: 0.5rem;
  }
}

/* Smooth page transitions */
.page-enter-active,
.page-leave-active {
  transition: opacity 0.3s ease;
}

.page-enter-from,
.page-leave-to {
  opacity: 0;
}

/* Better rendering */
* {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
</style>
