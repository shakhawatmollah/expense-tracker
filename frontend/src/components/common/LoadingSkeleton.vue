<template>
  <div class="skeleton-container" :class="[variant, size]">
    <!-- Card Skeleton -->
    <div v-if="type === 'card'" class="skeleton-card">
      <div class="skeleton-header">
        <div class="skeleton-avatar"></div>
        <div class="skeleton-text-group">
          <div class="skeleton-text skeleton-title"></div>
          <div class="skeleton-text skeleton-subtitle"></div>
        </div>
      </div>
      <div class="skeleton-content">
        <div v-for="i in lines" :key="i" class="skeleton-text" :style="{ width: getLineWidth(i) }"></div>
      </div>
      <div class="skeleton-footer" v-if="showFooter">
        <div class="skeleton-button"></div>
        <div class="skeleton-button skeleton-secondary"></div>
      </div>
    </div>

    <!-- Chart Skeleton -->
    <div v-else-if="type === 'chart'" class="skeleton-chart">
      <div class="skeleton-chart-header">
        <div class="skeleton-text skeleton-chart-title"></div>
        <div class="skeleton-controls">
          <div class="skeleton-control"></div>
          <div class="skeleton-control"></div>
        </div>
      </div>
      <div class="skeleton-chart-body">
        <div class="skeleton-chart-bars">
          <div v-for="i in 6" :key="i" class="skeleton-bar" :style="{ height: getBarHeight(i) }"></div>
        </div>
        <div class="skeleton-chart-axis">
          <div v-for="i in 6" :key="i" class="skeleton-axis-label"></div>
        </div>
      </div>
    </div>

    <!-- Table Skeleton -->
    <div v-else-if="type === 'table'" class="skeleton-table">
      <div class="skeleton-table-header">
        <div v-for="i in columns" :key="i" class="skeleton-table-cell skeleton-header-cell"></div>
      </div>
      <div v-for="row in rows" :key="row" class="skeleton-table-row">
        <div v-for="col in columns" :key="col" class="skeleton-table-cell"></div>
      </div>
    </div>

    <!-- List Skeleton -->
    <div v-else-if="type === 'list'" class="skeleton-list">
      <div v-for="i in items" :key="i" class="skeleton-list-item">
        <div class="skeleton-list-icon"></div>
        <div class="skeleton-list-content">
          <div class="skeleton-text skeleton-list-title"></div>
          <div class="skeleton-text skeleton-list-subtitle"></div>
        </div>
        <div class="skeleton-list-action"></div>
      </div>
    </div>

    <!-- Metric Skeleton -->
    <div v-else-if="type === 'metric'" class="skeleton-metric">
      <div class="skeleton-metric-icon"></div>
      <div class="skeleton-metric-content">
        <div class="skeleton-text skeleton-metric-value"></div>
        <div class="skeleton-text skeleton-metric-label"></div>
        <div class="skeleton-metric-trend"></div>
      </div>
    </div>

    <!-- Custom Skeleton -->
    <div v-else-if="type === 'custom'" class="skeleton-custom">
      <slot name="skeleton"></slot>
    </div>

    <!-- Default Text Skeleton -->
    <div v-else class="skeleton-text" :style="customStyle"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'text',
    validator: (value) => ['text', 'card', 'chart', 'table', 'list', 'metric', 'custom'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'pulse', 'wave', 'shimmer'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  width: {
    type: [String, Number],
    default: '100%'
  },
  height: {
    type: [String, Number],
    default: 'auto'
  },
  lines: {
    type: Number,
    default: 3
  },
  items: {
    type: Number,
    default: 5
  },
  rows: {
    type: Number,
    default: 5
  },
  columns: {
    type: Number,
    default: 4
  },
  showFooter: {
    type: Boolean,
    default: false
  },
  rounded: {
    type: Boolean,
    default: true
  }
})

const customStyle = computed(() => ({
  width: typeof props.width === 'number' ? `${props.width}px` : props.width,
  height: typeof props.height === 'number' ? `${props.height}px` : props.height
}))

const getLineWidth = (index) => {
  const widths = ['100%', '85%', '92%', '78%', '95%']
  return widths[(index - 1) % widths.length]
}

const getBarHeight = (index) => {
  const heights = ['60%', '80%', '45%', '90%', '70%', '55%']
  return heights[(index - 1) % heights.length]
}
</script>

<style scoped>
.skeleton-container {
  --skeleton-color: #e5e7eb;
  --skeleton-highlight: #f3f4f6;
  --skeleton-radius: 8px;
}

/* Size variants */
.skeleton-container.xs {
  --skeleton-height: 12px;
  --skeleton-spacing: 0.25rem;
}

.skeleton-container.sm {
  --skeleton-height: 16px;
  --skeleton-spacing: 0.5rem;
}

.skeleton-container.md {
  --skeleton-height: 20px;
  --skeleton-spacing: 0.75rem;
}

.skeleton-container.lg {
  --skeleton-height: 24px;
  --skeleton-spacing: 1rem;
}

.skeleton-container.xl {
  --skeleton-height: 32px;
  --skeleton-spacing: 1.5rem;
}

/* Base skeleton element */
.skeleton-base {
  background: var(--skeleton-color);
  border-radius: var(--skeleton-radius);
  position: relative;
  overflow: hidden;
}

/* Animation variants */
.skeleton-container.pulse .skeleton-base,
.skeleton-container.pulse .skeleton-text,
.skeleton-container.pulse .skeleton-avatar,
.skeleton-container.pulse .skeleton-button,
.skeleton-container.pulse .skeleton-bar,
.skeleton-container.pulse .skeleton-table-cell,
.skeleton-container.pulse .skeleton-list-icon,
.skeleton-container.pulse .skeleton-metric-icon {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.skeleton-container.wave .skeleton-base::before,
.skeleton-container.wave .skeleton-text::before,
.skeleton-container.wave .skeleton-avatar::before,
.skeleton-container.wave .skeleton-button::before,
.skeleton-container.wave .skeleton-bar::before,
.skeleton-container.wave .skeleton-table-cell::before,
.skeleton-container.wave .skeleton-list-icon::before,
.skeleton-container.wave .skeleton-metric-icon::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, var(--skeleton-highlight), transparent);
  animation: wave 1.6s linear infinite;
}

.skeleton-container.shimmer .skeleton-base,
.skeleton-container.shimmer .skeleton-text,
.skeleton-container.shimmer .skeleton-avatar,
.skeleton-container.shimmer .skeleton-button,
.skeleton-container.shimmer .skeleton-bar,
.skeleton-container.shimmer .skeleton-table-cell,
.skeleton-container.shimmer .skeleton-list-icon,
.skeleton-container.shimmer .skeleton-metric-icon {
  background: linear-gradient(90deg, var(--skeleton-color) 25%, var(--skeleton-highlight) 50%, var(--skeleton-color) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s ease-in-out infinite;
}

/* Text skeleton */
.skeleton-text {
  background: linear-gradient(90deg, var(--skeleton-color) 25%, var(--skeleton-highlight) 50%, var(--skeleton-color) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s ease-in-out infinite;
  height: var(--skeleton-height);
  background: var(--skeleton-color);
  border-radius: var(--skeleton-radius);
  margin-bottom: var(--skeleton-spacing);
  position: relative;
  overflow: hidden;
}

.skeleton-title {
  height: calc(var(--skeleton-height) * 1.5);
  width: 60%;
}

.skeleton-subtitle {
  height: calc(var(--skeleton-height) * 0.8);
  width: 40%;
}

/* Card skeleton */
.skeleton-card {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.skeleton-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.skeleton-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--skeleton-color);
  flex-shrink: 0;
  position: relative;
  overflow: hidden;
}

.skeleton-text-group {
  flex: 1;
}

.skeleton-content {
  margin-bottom: 1.5rem;
}

.skeleton-footer {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.skeleton-button {
  height: 36px;
  width: 80px;
  background: var(--skeleton-color);
  border-radius: 8px;
  position: relative;
  overflow: hidden;
}

.skeleton-secondary {
  width: 60px;
  opacity: 0.7;
}

/* Chart skeleton */
.skeleton-chart {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  padding: 1.5rem;
  height: 300px;
}

.skeleton-chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.skeleton-chart-title {
  width: 200px;
  height: 24px;
}

.skeleton-controls {
  display: flex;
  gap: 0.5rem;
}

.skeleton-control {
  width: 80px;
  height: 32px;
  background: var(--skeleton-color);
  border-radius: 6px;
  position: relative;
  overflow: hidden;
}

.skeleton-chart-body {
  height: 200px;
  display: flex;
  flex-direction: column;
}

.skeleton-chart-bars {
  flex: 1;
  display: flex;
  align-items: end;
  gap: 1rem;
  padding: 1rem 0;
}

.skeleton-bar {
  flex: 1;
  background: var(--skeleton-color);
  border-radius: 4px 4px 0 0;
  position: relative;
  overflow: hidden;
  min-height: 20px;
}

.skeleton-chart-axis {
  display: flex;
  gap: 1rem;
  padding-top: 0.5rem;
}

.skeleton-axis-label {
  flex: 1;
  height: 16px;
  background: var(--skeleton-color);
  border-radius: 4px;
  position: relative;
  overflow: hidden;
}

/* Table skeleton */
.skeleton-table {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  overflow: hidden;
}

.skeleton-table-header {
  display: grid;
  grid-template-columns: repeat(var(--columns, 4), 1fr);
  gap: 1rem;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.02);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.skeleton-table-row {
  display: grid;
  grid-template-columns: repeat(var(--columns, 4), 1fr);
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.skeleton-table-cell {
  height: var(--skeleton-height);
  background: var(--skeleton-color);
  border-radius: var(--skeleton-radius);
  position: relative;
  overflow: hidden;
}

.skeleton-header-cell {
  height: calc(var(--skeleton-height) * 1.2);
  opacity: 0.8;
}

/* List skeleton */
.skeleton-list {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  overflow: hidden;
}

.skeleton-list-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.skeleton-list-item:last-child {
  border-bottom: none;
}

.skeleton-list-icon {
  width: 40px;
  height: 40px;
  background: var(--skeleton-color);
  border-radius: 8px;
  flex-shrink: 0;
  position: relative;
  overflow: hidden;
}

.skeleton-list-content {
  flex: 1;
}

.skeleton-list-title {
  width: 70%;
  margin-bottom: 0.5rem;
}

.skeleton-list-subtitle {
  width: 50%;
  height: calc(var(--skeleton-height) * 0.8);
}

.skeleton-list-action {
  width: 24px;
  height: 24px;
  background: var(--skeleton-color);
  border-radius: 4px;
  position: relative;
  overflow: hidden;
}

/* Metric skeleton */
.skeleton-metric {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 16px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.skeleton-metric-icon {
  width: 60px;
  height: 60px;
  background: var(--skeleton-color);
  border-radius: 12px;
  flex-shrink: 0;
  position: relative;
  overflow: hidden;
}

.skeleton-metric-content {
  flex: 1;
}

.skeleton-metric-value {
  height: 32px;
  width: 80%;
  margin-bottom: 0.5rem;
}

.skeleton-metric-label {
  height: 16px;
  width: 60%;
  margin-bottom: 0.75rem;
}

.skeleton-metric-trend {
  height: 20px;
  width: 50%;
  background: var(--skeleton-color);
  border-radius: 10px;
  position: relative;
  overflow: hidden;
}

/* Animations */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

@keyframes wave {
  0% {
    left: -100%;
  }
  100% {
    left: 100%;
  }
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .skeleton-container {
    --skeleton-color: #374151;
    --skeleton-highlight: #4b5563;
  }
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .skeleton-chart-bars {
    gap: 0.5rem;
  }
  
  .skeleton-table-header,
  .skeleton-table-row {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }
  
  .skeleton-metric {
    flex-direction: column;
    text-align: center;
  }
  
  .skeleton-card {
    padding: 1rem;
  }
}
</style>