import { ref } from 'vue'

let idCounter = 0

export function useId(prefix = 'id') {
  const id = ref(`${prefix}-${++idCounter}`)
  return id.value
}

export function generateId(prefix = 'id') {
  return `${prefix}-${++idCounter}`
}
