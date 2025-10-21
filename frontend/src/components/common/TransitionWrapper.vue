<template>
  <transition
    :name="transitionName"
    :mode="mode"
    :duration="duration"
    @before-enter="onBeforeEnter"
    @enter="onEnter"
    @after-enter="onAfterEnter"
    @before-leave="onBeforeLeave"
    @leave="onLeave"
    @after-leave="onAfterLeave"
    appear
  >
    <slot></slot>
  </transition>
</template>

<script setup>
  import { computed } from 'vue'

  const props = defineProps({
    type: {
      type: String,
      default: 'fade',
      validator: value =>
        [
          'fade',
          'slide',
          'slide-up',
          'slide-down',
          'slide-left',
          'slide-right',
          'zoom',
          'flip',
          'bounce',
          'elastic',
          'scale',
          'rotate',
          'blur'
        ].includes(value)
    },
    mode: {
      type: String,
      default: 'out-in',
      validator: value => ['in-out', 'out-in'].includes(value)
    },
    duration: {
      type: [Number, Object],
      default: () => ({ enter: 300, leave: 300 })
    },
    delay: {
      type: Number,
      default: 0
    },
    easing: {
      type: String,
      default: 'ease-out',
      validator: value =>
        [
          'ease',
          'ease-in',
          'ease-out',
          'ease-in-out',
          'linear',
          'cubic-bezier(0.25, 0.46, 0.45, 0.94)', // ease-out-quad
          'cubic-bezier(0.55, 0.085, 0.68, 0.53)', // ease-in-quad
          'cubic-bezier(0.25, 0.46, 0.45, 0.94)', // ease-out-cubic
          'cubic-bezier(0.215, 0.61, 0.355, 1)', // ease-out-quart
          'cubic-bezier(0.19, 1, 0.22, 1)' // ease-out-expo
        ].includes(value)
    },
    group: {
      type: Boolean,
      default: false
    },
    stagger: {
      type: Number,
      default: 0
    }
  })

  const emit = defineEmits(['before-enter', 'enter', 'after-enter', 'before-leave', 'leave', 'after-leave'])

  const transitionName = computed(() => {
    return props.group ? `${props.type}-group` : props.type
  })

  // Transition event handlers
  const onBeforeEnter = el => {
    if (props.delay > 0) {
      el.style.animationDelay = `${props.delay}ms`
    }
    emit('before-enter', el)
  }

  const onEnter = (el, done) => {
    emit('enter', el, done)
    // Auto-call done if not handled by parent
    setTimeout(done, typeof props.duration === 'number' ? props.duration : props.duration.enter)
  }

  const onAfterEnter = el => {
    // Clean up any transition styles
    el.style.animationDelay = ''
    emit('after-enter', el)
  }

  const onBeforeLeave = el => {
    emit('before-leave', el)
  }

  const onLeave = (el, done) => {
    emit('leave', el, done)
    // Auto-call done if not handled by parent
    setTimeout(done, typeof props.duration === 'number' ? props.duration : props.duration.leave)
  }

  const onAfterLeave = el => {
    emit('after-leave', el)
  }
</script>

<style scoped>
  /* Base transition properties */
  .fade-enter-active,
  .fade-leave-active,
  .slide-enter-active,
  .slide-leave-active,
  .slide-up-enter-active,
  .slide-up-leave-active,
  .slide-down-enter-active,
  .slide-down-leave-active,
  .slide-left-enter-active,
  .slide-left-leave-active,
  .slide-right-enter-active,
  .slide-right-leave-active,
  .zoom-enter-active,
  .zoom-leave-active,
  .flip-enter-active,
  .flip-leave-active,
  .bounce-enter-active,
  .bounce-leave-active,
  .elastic-enter-active,
  .elastic-leave-active,
  .scale-enter-active,
  .scale-leave-active,
  .rotate-enter-active,
  .rotate-leave-active,
  .blur-enter-active,
  .blur-leave-active {
    transition-property: all;
    transition-timing-function: v-bind(easing);
  }

  .fade-enter-active {
    transition-duration: v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"');
  }

  .fade-leave-active {
    transition-duration: v-bind('typeof duration === "number" ? duration + "ms" : duration.leave + "ms"');
  }

  /* Fade Transition */
  .fade-enter-from,
  .fade-leave-to {
    opacity: 0;
  }

  .fade-enter-to,
  .fade-leave-from {
    opacity: 1;
  }

  /* Slide Transitions */
  .slide-enter-active,
  .slide-leave-active {
    transition-duration: v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"');
  }

  .slide-enter-from {
    transform: translateX(-100%);
    opacity: 0;
  }

  .slide-leave-to {
    transform: translateX(100%);
    opacity: 0;
  }

  /* Slide Up */
  .slide-up-enter-active,
  .slide-up-leave-active {
    transition-duration: v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"');
  }

  .slide-up-enter-from {
    transform: translateY(100%);
    opacity: 0;
  }

  .slide-up-leave-to {
    transform: translateY(-100%);
    opacity: 0;
  }

  /* Slide Down */
  .slide-down-enter-active,
  .slide-down-leave-active {
    transition-duration: v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"');
  }

  .slide-down-enter-from {
    transform: translateY(-100%);
    opacity: 0;
  }

  .slide-down-leave-to {
    transform: translateY(100%);
    opacity: 0;
  }

  /* Slide Left */
  .slide-left-enter-active,
  .slide-left-leave-active {
    transition-duration: v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"');
  }

  .slide-left-enter-from {
    transform: translateX(100%);
    opacity: 0;
  }

  .slide-left-leave-to {
    transform: translateX(-100%);
    opacity: 0;
  }

  /* Slide Right */
  .slide-right-enter-active,
  .slide-right-leave-active {
    transition-duration: v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"');
  }

  .slide-right-enter-from {
    transform: translateX(-100%);
    opacity: 0;
  }

  .slide-right-leave-to {
    transform: translateX(100%);
    opacity: 0;
  }

  /* Zoom Transition */
  .zoom-enter-active,
  .zoom-leave-active {
    transition-duration: v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"');
  }

  .zoom-enter-from {
    transform: scale(0.8);
    opacity: 0;
  }

  .zoom-leave-to {
    transform: scale(1.2);
    opacity: 0;
  }

  /* Flip Transition */
  .flip-enter-active,
  .flip-leave-active {
    transition-duration: v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"');
  }

  .flip-enter-from {
    transform: rotateY(-90deg);
    opacity: 0;
  }

  .flip-leave-to {
    transform: rotateY(90deg);
    opacity: 0;
  }

  /* Bounce Transition */
  .bounce-enter-active {
    animation: bounce-in v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"') v-bind(easing);
  }

  .bounce-leave-active {
    animation: bounce-out v-bind('typeof duration === "number" ? duration + "ms" : duration.leave + "ms"')
      v-bind(easing);
  }

  @keyframes bounce-in {
    0% {
      transform: scale(0.3);
      opacity: 0;
    }
    50% {
      transform: scale(1.05);
      opacity: 0.8;
    }
    70% {
      transform: scale(0.9);
      opacity: 1;
    }
    100% {
      transform: scale(1);
      opacity: 1;
    }
  }

  @keyframes bounce-out {
    0% {
      transform: scale(1);
      opacity: 1;
    }
    30% {
      transform: scale(1.05);
      opacity: 0.8;
    }
    100% {
      transform: scale(0.3);
      opacity: 0;
    }
  }

  /* Elastic Transition */
  .elastic-enter-active {
    animation: elastic-in v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"')
      v-bind(easing);
  }

  .elastic-leave-active {
    animation: elastic-out v-bind('typeof duration === "number" ? duration + "ms" : duration.leave + "ms"')
      v-bind(easing);
  }

  @keyframes elastic-in {
    0% {
      transform: scale(0) rotate(0deg);
      opacity: 0;
    }
    50% {
      transform: scale(1.25) rotate(180deg);
      opacity: 0.8;
    }
    75% {
      transform: scale(0.85) rotate(270deg);
      opacity: 1;
    }
    100% {
      transform: scale(1) rotate(360deg);
      opacity: 1;
    }
  }

  @keyframes elastic-out {
    0% {
      transform: scale(1) rotate(0deg);
      opacity: 1;
    }
    25% {
      transform: scale(1.15) rotate(-90deg);
      opacity: 0.8;
    }
    100% {
      transform: scale(0) rotate(-180deg);
      opacity: 0;
    }
  }

  /* Scale Transition */
  .scale-enter-active,
  .scale-leave-active {
    transition-duration: v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"');
  }

  .scale-enter-from {
    transform: scale(0);
    opacity: 0;
  }

  .scale-leave-to {
    transform: scale(0);
    opacity: 0;
  }

  /* Rotate Transition */
  .rotate-enter-active,
  .rotate-leave-active {
    transition-duration: v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"');
  }

  .rotate-enter-from {
    transform: rotate(-180deg) scale(0.8);
    opacity: 0;
  }

  .rotate-leave-to {
    transform: rotate(180deg) scale(0.8);
    opacity: 0;
  }

  /* Blur Transition */
  .blur-enter-active,
  .blur-leave-active {
    transition-duration: v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"');
  }

  .blur-enter-from {
    filter: blur(10px);
    transform: scale(1.1);
    opacity: 0;
  }

  .blur-leave-to {
    filter: blur(10px);
    transform: scale(0.9);
    opacity: 0;
  }

  /* Group Transitions */
  .fade-group-move,
  .slide-group-move,
  .slide-up-group-move,
  .slide-down-group-move,
  .slide-left-group-move,
  .slide-right-group-move,
  .zoom-group-move,
  .scale-group-move {
    transition: transform v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"')
      v-bind(easing);
  }

  .fade-group-enter-active,
  .fade-group-leave-active {
    transition: all v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"') v-bind(easing);
  }

  .fade-group-enter-from,
  .fade-group-leave-to {
    opacity: 0;
    transform: translateY(30px);
  }

  .fade-group-leave-active {
    position: absolute;
  }

  /* Staggered animations */
  .stagger-enter-active {
    transition: all v-bind('typeof duration === "number" ? duration + "ms" : duration.enter + "ms"') v-bind(easing);
    transition-delay: calc(var(--stagger-delay, 0) * v-bind('stagger + "ms"'));
  }

  /* Performance optimizations */
  .fade-enter-active,
  .fade-leave-active,
  .slide-enter-active,
  .slide-leave-active,
  .slide-up-enter-active,
  .slide-up-leave-active,
  .slide-down-enter-active,
  .slide-down-leave-active,
  .slide-left-enter-active,
  .slide-left-leave-active,
  .slide-right-enter-active,
  .slide-right-leave-active,
  .zoom-enter-active,
  .zoom-leave-active,
  .scale-enter-active,
  .scale-leave-active,
  .rotate-enter-active,
  .rotate-leave-active,
  .blur-enter-active,
  .blur-leave-active {
    will-change: transform, opacity;
  }

  /* Mobile optimizations */
  @media (max-width: 768px) {
    .bounce-enter-active,
    .bounce-leave-active,
    .elastic-enter-active,
    .elastic-leave-active {
      animation-duration: 200ms;
    }

    .fade-enter-active,
    .fade-leave-active,
    .slide-enter-active,
    .slide-leave-active,
    .slide-up-enter-active,
    .slide-up-leave-active,
    .slide-down-enter-active,
    .slide-down-leave-active,
    .slide-left-enter-active,
    .slide-left-leave-active,
    .slide-right-enter-active,
    .slide-right-leave-active,
    .zoom-enter-active,
    .zoom-leave-active,
    .scale-enter-active,
    .scale-leave-active,
    .rotate-enter-active,
    .rotate-leave-active,
    .blur-enter-active,
    .blur-leave-active {
      transition-duration: 200ms;
    }
  }

  /* Reduced motion support */
  @media (prefers-reduced-motion: reduce) {
    .fade-enter-active,
    .fade-leave-active,
    .slide-enter-active,
    .slide-leave-active,
    .slide-up-enter-active,
    .slide-up-leave-active,
    .slide-down-enter-active,
    .slide-down-leave-active,
    .slide-left-enter-active,
    .slide-left-leave-active,
    .slide-right-enter-active,
    .slide-right-leave-active,
    .zoom-enter-active,
    .zoom-leave-active,
    .scale-enter-active,
    .scale-leave-active,
    .rotate-enter-active,
    .rotate-leave-active,
    .blur-enter-active,
    .blur-leave-active {
      transition-duration: 0ms;
    }

    .bounce-enter-active,
    .bounce-leave-active,
    .elastic-enter-active,
    .elastic-leave-active {
      animation: none;
      transition: opacity 0ms;
    }

    .bounce-enter-from,
    .bounce-leave-to,
    .elastic-enter-from,
    .elastic-leave-to {
      opacity: 0;
    }
  }
</style>
