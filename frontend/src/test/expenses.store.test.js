import { describe, it, expect, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useExpensesStore } from '@/stores/expenses'

describe('Expenses Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('should initialize with default values', () => {
    const store = useExpensesStore()

    expect(store.expenses).toEqual([])
    expect(store.loading).toBe(false)
    expect(store.error).toBe(null)
    expect(store.totalExpenses).toBe(0)
  })

  it('should calculate monthly total correctly', () => {
    const store = useExpensesStore()

    // Mock expenses data
    store.expenses = [
      { id: 1, amount: 100, date: '2025-10-01' },
      { id: 2, amount: 200, date: '2025-10-15' },
      { id: 3, amount: 50, date: '2025-09-30' } // Different month
    ]

    const monthlyTotal = store.getMonthlyTotal(2025, 10)
    expect(monthlyTotal).toBe(300) // Only October expenses
  })

  it('should filter expenses by category', () => {
    const store = useExpensesStore()

    store.expenses = [
      { id: 1, category_id: 1, amount: 100 },
      { id: 2, category_id: 2, amount: 200 },
      { id: 3, category_id: 1, amount: 150 }
    ]

    const categoryExpenses = store.getExpensesByCategory(1)
    expect(categoryExpenses).toHaveLength(2)
    expect(categoryExpenses.every(e => e.category_id === 1)).toBe(true)
  })
})
