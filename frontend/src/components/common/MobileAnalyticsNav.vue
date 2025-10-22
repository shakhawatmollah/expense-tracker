<template>
  <div class="mobile-analytics-nav" :class="{ sticky: isSticky, collapsed: isCollapsed }">
    <!-- Navigation Header -->
    <div class="nav-header">
      <button
        @click="toggleCollapse"
        class="collapse-btn"
        :aria-label="isCollapsed ? 'Expand navigation' : 'Collapse navigation'"
      >
        <i :class="isCollapsed ? 'fas fa-chevron-down' : 'fas fa-chevron-up'"></i>
      </button>

      <div class="nav-title">Analytics Navigation</div>

      <div class="nav-indicator">
        <div class="section-dots">
          <span
            v-for="section in sections"
            :key="section.id"
            :class="['dot', { active: activeSection === section.id }]"
            @click="scrollToSection(section.id)"
          ></span>
        </div>
      </div>
    </div>

    <!-- Navigation Content -->
    <div class="nav-content" v-show="!isCollapsed">
      <!-- Quick Actions -->
      <div class="quick-actions">
        <button
          @click="$emit('refresh')"
          :disabled="loading"
          class="quick-action-btn refresh-btn"
          aria-label="Refresh data"
        >
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
          <span>Refresh</span>
        </button>

        <button @click="$emit('export')" class="quick-action-btn export-btn" aria-label="Export data">
          <i class="fas fa-download"></i>
          <span>Export</span>
        </button>

        <button @click="$emit('share')" class="quick-action-btn share-btn" aria-label="Share analytics">
          <i class="fas fa-share-alt"></i>
          <span>Share</span>
        </button>
      </div>

      <!-- Period Selector -->
      <div class="period-selector">
        <div class="selector-label">Time Period</div>
        <div class="period-buttons">
          <button
            v-for="period in periods"
            :key="period.value"
            :class="['period-btn', { active: selectedPeriod === period.value }]"
            @click="$emit('period-change', period.value)"
          >
            {{ period.label }}
          </button>
        </div>
      </div>

      <!-- Section Navigator -->
      <div class="section-navigator">
        <div class="navigator-label">Jump to Section</div>
        <div class="section-list">
          <button
            v-for="section in sections"
            :key="section.id"
            :class="['section-btn', { active: activeSection === section.id }]"
            @click="scrollToSection(section.id)"
          >
            <i :class="section.icon"></i>
            <span>{{ section.label }}</span>
            <div class="section-indicator" v-if="activeSection === section.id"></div>
          </button>
        </div>
      </div>

      <!-- Filter Options -->
      <div class="filter-options" v-if="showFilters">
        <div class="filter-label">Filters</div>
        <div class="filter-controls">
          <select
            v-model="categoryFilter"
            @change="$emit('filter-change', 'category', categoryFilter)"
            class="filter-select"
          >
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>

          <select v-model="amountFilter" @change="$emit('filter-change', 'amount', amountFilter)" class="filter-select">
            <option value="">All Amounts</option>
            <option value="low">&lt; $100</option>
            <option value="medium">$100 - $500</option>
            <option value="high">&gt; $500</option>
          </select>
        </div>
      </div>

      <!-- View Options -->
      <div class="view-options">
        <div class="option-label">View Mode</div>
        <div class="view-toggles">
          <button
            :class="['view-toggle', { active: viewMode === 'detailed' }]"
            @click="$emit('view-change', 'detailed')"
          >
            <i class="fas fa-list"></i>
            <span>Detailed</span>
          </button>

          <button :class="['view-toggle', { active: viewMode === 'compact' }]" @click="$emit('view-change', 'compact')">
            <i class="fas fa-th"></i>
            <span>Compact</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Progress Indicator -->
    <div class="scroll-progress">
      <div class="progress-bar" :style="{ width: scrollProgress + '%' }"></div>
    </div>
  </div>
</template>

<script>
  import { ref, computed, onMounted, onUnmounted } from 'vue'

  export default {
    name: 'MobileAnalyticsNav',
    props: {
      selectedPeriod: {
        type: String,
        default: 'monthly'
      },
      loading: {
        type: Boolean,
        default: false
      },
      showFilters: {
        type: Boolean,
        default: true
      },
      categories: {
        type: Array,
        default: () => []
      },
      viewMode: {
        type: String,
        default: 'detailed'
      }
    },
    emits: ['refresh', 'export', 'share', 'period-change', 'filter-change', 'view-change'],
    setup(props, { emit }) {
      // Reactive state
      const isCollapsed = ref(false)
      const isSticky = ref(false)
      const activeSection = ref('metrics')
      const scrollProgress = ref(0)
      const categoryFilter = ref('')
      const amountFilter = ref('')

      // Configuration
      const periods = ref([
        { label: 'Week', value: 'weekly' },
        { label: 'Month', value: 'monthly' },
        { label: 'Quarter', value: 'quarterly' },
        { label: 'Year', value: 'yearly' }
      ])

      const sections = ref([
        { id: 'metrics', label: 'Key Metrics', icon: 'fas fa-chart-bar' },
        { id: 'charts', label: 'Charts', icon: 'fas fa-chart-line' },
        { id: 'insights', label: 'Insights', icon: 'fas fa-lightbulb' },
        { id: 'comparison', label: 'Comparison', icon: 'fas fa-balance-scale' },
        { id: 'predictions', label: 'Predictions', icon: 'fas fa-crystal-ball' }
      ])

      // Methods
      const toggleCollapse = () => {
        isCollapsed.value = !isCollapsed.value
      }

      const scrollToSection = sectionId => {
        const element =
          document.querySelector(`[data-section="${sectionId}"]`) ||
          document.querySelector(`.${sectionId}-section`) ||
          document.getElementById(sectionId)

        if (element) {
          const offset = 100 // Account for sticky header
          const elementPosition = element.offsetTop - offset

          window.scrollTo({
            top: elementPosition,
            behavior: 'smooth'
          })

          // Update active section
          activeSection.value = sectionId

          // Auto-collapse on mobile after navigation
          if (window.innerWidth <= 768) {
            setTimeout(() => {
              isCollapsed.value = true
            }, 500)
          }
        }
      }

      const handleScroll = () => {
        const scrollTop = window.scrollY
        const docHeight = document.documentElement.scrollHeight - window.innerHeight

        // Update scroll progress
        scrollProgress.value = Math.min(100, (scrollTop / docHeight) * 100)

        // Update sticky state
        isSticky.value = scrollTop > 100

        // Update active section based on scroll position
        const sectionElements = sections.value
          .map(section => ({
            id: section.id,
            element:
              document.querySelector(`[data-section="${section.id}"]`) ||
              document.querySelector(`.${section.id}-section`) ||
              document.getElementById(section.id)
          }))
          .filter(item => item.element)

        let currentSection = sections.value[0]?.id || 'metrics'

        for (const { id, element } of sectionElements) {
          const rect = element.getBoundingClientRect()
          if (rect.top <= 200) {
            currentSection = id
          }
        }

        activeSection.value = currentSection
      }

      const handleResize = () => {
        // Auto-collapse on small screens
        if (window.innerWidth <= 480) {
          isCollapsed.value = true
        }
      }

      // Lifecycle
      onMounted(() => {
        window.addEventListener('scroll', handleScroll, { passive: true })
        window.addEventListener('resize', handleResize)
        handleResize()
        handleScroll()
      })

      onUnmounted(() => {
        window.removeEventListener('scroll', handleScroll)
        window.removeEventListener('resize', handleResize)
      })

      return {
        // State
        isCollapsed,
        isSticky,
        activeSection,
        scrollProgress,
        categoryFilter,
        amountFilter,

        // Data
        periods,
        sections,

        // Methods
        toggleCollapse,
        scrollToSection
      }
    }
  }
</script>

<style scoped>
  .mobile-analytics-nav {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 0 0 20px 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    margin-bottom: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 100;
  }

  .mobile-analytics-nav.sticky {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    margin-bottom: 0;
    border-radius: 0 0 20px 20px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    z-index: 1000;
  }

  .mobile-analytics-nav.collapsed .nav-content {
    max-height: 0;
    overflow: hidden;
    opacity: 0;
  }

  .nav-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  }

  .collapse-btn {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 10px;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .collapse-btn:hover {
    background: rgba(102, 126, 234, 0.2);
    transform: translateY(-1px);
  }

  .nav-title {
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    flex: 1;
    text-align: center;
  }

  .nav-indicator {
    width: 36px;
    display: flex;
    justify-content: center;
  }

  .section-dots {
    display: flex;
    gap: 0.25rem;
  }

  .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #d1d5db;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .dot.active {
    background: #667eea;
    transform: scale(1.2);
  }

  .nav-content {
    padding: 0 1.5rem 1.5rem;
    transition: all 0.3s ease;
    max-height: 1000px;
    opacity: 1;
    overflow: hidden;
  }

  .quick-actions {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
  }

  .quick-action-btn {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 0.5rem;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    min-height: 60px;
  }

  .quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
  }

  .quick-action-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
  }

  .quick-action-btn i {
    font-size: 1rem;
  }

  .period-selector,
  .section-navigator,
  .filter-options,
  .view-options {
    margin-bottom: 1.5rem;
  }

  .selector-label,
  .navigator-label,
  .filter-label,
  .option-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.75rem;
  }

  .period-buttons {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
  }

  .period-btn {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    color: #6b7280;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .period-btn.active {
    background: #667eea;
    border-color: #667eea;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  }

  .section-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .section-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 10px;
    background: transparent;
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    text-align: left;
  }

  .section-btn:hover {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
  }

  .section-btn.active {
    background: rgba(102, 126, 234, 0.15);
    color: #667eea;
  }

  .section-btn i {
    width: 16px;
    text-align: center;
  }

  .section-indicator {
    position: absolute;
    right: 1rem;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #667eea;
  }

  .filter-controls {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .filter-select {
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .filter-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  }

  .view-toggles {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
  }

  .view-toggle {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    color: #6b7280;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .view-toggle.active {
    background: #667eea;
    border-color: #667eea;
    color: white;
  }

  .view-toggle i {
    font-size: 1rem;
  }

  .scroll-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 0 0 20px 20px;
    overflow: hidden;
  }

  .progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.1s ease;
    border-radius: 0 0 20px 20px;
  }

  /* Mobile optimizations */
  @media (max-width: 768px) {
    .mobile-analytics-nav {
      margin: 0 -1rem 1rem -1rem;
      border-radius: 0 0 16px 16px;
    }

    .mobile-analytics-nav.sticky {
      margin: 0;
    }

    .nav-header {
      padding: 0.875rem 1.25rem;
    }

    .nav-title {
      font-size: 0.9rem;
    }

    .nav-content {
      padding: 0 1.25rem 1.25rem;
    }

    .quick-actions {
      gap: 0.5rem;
    }

    .quick-action-btn {
      padding: 0.75rem 0.375rem;
      min-height: 50px;
    }

    .quick-action-btn span {
      font-size: 0.7rem;
    }

    .period-buttons {
      grid-template-columns: repeat(2, 1fr);
      gap: 0.5rem;
    }

    .period-btn {
      padding: 0.625rem 0.5rem;
      font-size: 0.8rem;
    }
  }

  @media (max-width: 480px) {
    .nav-header {
      padding: 0.75rem 1rem;
    }

    .nav-title {
      font-size: 0.85rem;
    }

    .nav-content {
      padding: 0 1rem 1rem;
    }

    .quick-actions {
      margin-bottom: 1rem;
    }

    .quick-action-btn {
      padding: 0.625rem 0.25rem;
      min-height: 45px;
    }

    .quick-action-btn i {
      font-size: 0.875rem;
    }

    .quick-action-btn span {
      font-size: 0.65rem;
    }

    .section-btn {
      padding: 0.625rem 0.75rem;
      font-size: 0.8rem;
    }

    .selector-label,
    .navigator-label,
    .filter-label,
    .option-label {
      font-size: 0.8rem;
      margin-bottom: 0.5rem;
    }
  }

  /* Dark mode support */
  @media (prefers-color-scheme: dark) {
    .mobile-analytics-nav {
      background: rgba(30, 41, 59, 0.95);
      border-color: rgba(71, 85, 105, 0.3);
    }

    .nav-header {
      border-bottom-color: rgba(71, 85, 105, 0.2);
    }

    .nav-title,
    .selector-label,
    .navigator-label,
    .filter-label,
    .option-label {
      color: #e2e8f0;
    }

    .period-btn,
    .filter-select,
    .view-toggle {
      background: rgba(51, 65, 85, 0.5);
      border-color: rgba(71, 85, 105, 0.3);
      color: #cbd5e1;
    }

    .section-btn {
      color: #94a3b8;
    }

    .section-btn:hover {
      background: rgba(102, 126, 234, 0.2);
      color: #a5b4fc;
    }

    .section-btn.active {
      background: rgba(102, 126, 234, 0.3);
      color: #a5b4fc;
    }

    .scroll-progress {
      background: rgba(71, 85, 105, 0.2);
    }
  }
</style>
