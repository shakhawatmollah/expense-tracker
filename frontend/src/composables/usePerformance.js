import { ref, onMounted, onUnmounted } from 'vue'

/**
 * Performance monitoring composable
 * Tracks page load times, component render times, and API call performance
 */
export function usePerformance() {
  const metrics = ref({
    pageLoadTime: 0,
    componentRenderTime: 0,
    apiCallTimes: [],
    memoryUsage: 0
  })

  const startTime = ref(0)
  const observers = ref([])

  // Start performance measurement
  const startMeasurement = (label = 'default') => {
    startTime.value = performance.now()
    performance.mark(`${label}-start`)
  }

  // End performance measurement
  const endMeasurement = (label = 'default') => {
    const endTime = performance.now()
    performance.mark(`${label}-end`)
    
    try {
      performance.measure(label, `${label}-start`, `${label}-end`)
      const measure = performance.getEntriesByName(label)[0]
      return measure.duration
    } catch (error) {
      return endTime - startTime.value
    }
  }

  // Track API call performance
  const trackApiCall = (url, duration, status) => {
    metrics.value.apiCallTimes.push({
      url,
      duration,
      status,
      timestamp: Date.now()
    })

    // Keep only last 50 API calls
    if (metrics.value.apiCallTimes.length > 50) {
      metrics.value.apiCallTimes = metrics.value.apiCallTimes.slice(-50)
    }
  }

  // Get memory usage if available
  const updateMemoryUsage = () => {
    if (performance.memory) {
      metrics.value.memoryUsage = {
        used: Math.round(performance.memory.usedJSHeapSize / 1024 / 1024),
        total: Math.round(performance.memory.totalJSHeapSize / 1024 / 1024),
        limit: Math.round(performance.memory.jsHeapSizeLimit / 1024 / 1024)
      }
    }
  }

  // Monitor Core Web Vitals
  const monitorWebVitals = () => {
    if ('PerformanceObserver' in window) {
      // Largest Contentful Paint
      const lcpObserver = new PerformanceObserver((list) => {
        const entries = list.getEntries()
        const lcp = entries[entries.length - 1]
        metrics.value.lcp = lcp.startTime
      })
      lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] })
      observers.value.push(lcpObserver)

      // First Input Delay
      const fidObserver = new PerformanceObserver((list) => {
        const entries = list.getEntries()
        entries.forEach((entry) => {
          metrics.value.fid = entry.processingStart - entry.startTime
        })
      })
      fidObserver.observe({ entryTypes: ['first-input'] })
      observers.value.push(fidObserver)

      // Cumulative Layout Shift
      let clsValue = 0
      const clsObserver = new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
          if (!entry.hadRecentInput) {
            clsValue += entry.value
            metrics.value.cls = clsValue
          }
        }
      })
      clsObserver.observe({ entryTypes: ['layout-shift'] })
      observers.value.push(clsObserver)
    }
  }

  // Get performance insights
  const getPerformanceInsights = () => {
    const insights = []

    // Check LCP
    if (metrics.value.lcp > 2500) {
      insights.push({
        type: 'warning',
        metric: 'LCP',
        message: 'Largest Contentful Paint is slower than recommended (>2.5s)',
        value: metrics.value.lcp
      })
    }

    // Check FID
    if (metrics.value.fid > 100) {
      insights.push({
        type: 'warning',
        metric: 'FID',
        message: 'First Input Delay is slower than recommended (>100ms)',
        value: metrics.value.fid
      })
    }

    // Check CLS
    if (metrics.value.cls > 0.1) {
      insights.push({
        type: 'warning',
        metric: 'CLS',
        message: 'Cumulative Layout Shift is higher than recommended (>0.1)',
        value: metrics.value.cls
      })
    }

    // Check API performance
    const avgApiTime = metrics.value.apiCallTimes.reduce((sum, call) => sum + call.duration, 0) / metrics.value.apiCallTimes.length
    if (avgApiTime > 1000) {
      insights.push({
        type: 'warning',
        metric: 'API',
        message: 'Average API response time is slower than recommended (>1s)',
        value: Math.round(avgApiTime)
      })
    }

    return insights
  }

  // Log performance data (development only)
  const logPerformanceData = () => {
    if (import.meta.env.DEV) {
      console.group('🚀 Performance Metrics')
      console.log('Page Load Time:', metrics.value.pageLoadTime, 'ms')
      console.log('LCP:', metrics.value.lcp, 'ms')
      console.log('FID:', metrics.value.fid, 'ms') 
      console.log('CLS:', metrics.value.cls)
      console.log('Memory Usage:', metrics.value.memoryUsage)
      console.log('API Calls:', metrics.value.apiCallTimes.length)
      console.groupEnd()
    }
  }

  onMounted(() => {
    // Track page load time
    if (document.readyState === 'complete') {
      metrics.value.pageLoadTime = performance.now()
    } else {
      window.addEventListener('load', () => {
        metrics.value.pageLoadTime = performance.now()
      })
    }

    // Start monitoring
    monitorWebVitals()
    updateMemoryUsage()

    // Update memory usage every 30 seconds
    const memoryInterval = setInterval(updateMemoryUsage, 30000)
    
    onUnmounted(() => {
      clearInterval(memoryInterval)
      observers.value.forEach(observer => observer.disconnect())
    })
  })

  return {
    metrics,
    startMeasurement,
    endMeasurement,
    trackApiCall,
    getPerformanceInsights,
    logPerformanceData,
    updateMemoryUsage
  }
}