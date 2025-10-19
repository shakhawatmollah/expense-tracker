<template>
  <span class="animated-counter">{{ displayValue }}</span>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'

const props = defineProps({
  value: {
    type: Number,
    required: true
  },
  duration: {
    type: Number,
    default: 2000
  },
  prefix: {
    type: String,
    default: ''
  },
  suffix: {
    type: String,
    default: ''
  },
  decimals: {
    type: Number,
    default: 0
  }
})

const displayValue = ref('0')
const animatedValue = ref(0)

const formatNumber = (num) => {
  const formatted = num.toFixed(props.decimals)
  return props.prefix + formatted + props.suffix
}

const animateCounter = (target) => {
  const startValue = animatedValue.value
  const startTime = Date.now()
  
  const animate = () => {
    const now = Date.now()
    const elapsed = now - startTime
    const progress = Math.min(elapsed / props.duration, 1)
    
    // Easing function for smooth animation
    const easeOut = 1 - Math.pow(1 - progress, 3)
    
    const currentValue = startValue + (target - startValue) * easeOut
    animatedValue.value = currentValue
    displayValue.value = formatNumber(currentValue)
    
    if (progress < 1) {
      requestAnimationFrame(animate)
    }
  }
  
  requestAnimationFrame(animate)
}

watch(() => props.value, (newValue) => {
  animateCounter(newValue)
}, { immediate: true })

onMounted(() => {
  animateCounter(props.value)
})
</script>

<style scoped>
.animated-counter {
  font-feature-settings: 'tnum';
  font-variant-numeric: tabular-nums;
}
</style>