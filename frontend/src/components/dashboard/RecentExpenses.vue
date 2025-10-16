<template>
  <div class="card">
    <div class="card-header">
      <h3 class="text-lg font-medium text-gray-900">Recent Expenses</h3>
    </div>
    <div class="card-body">
      <div v-if="expenses.length === 0" class="text-center py-8">
        <CreditCardIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No expenses</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new expense.</p>
      </div>
      <div v-else class="space-y-3">
        <div 
          v-for="expense in expenses.slice(0, 5)" 
          :key="expense.id"
          class="flex items-center justify-between py-2"
        >
          <div>
            <p class="text-sm font-medium text-gray-900">{{ expense.description }}</p>
            <p class="text-sm text-gray-500">{{ expense.expense_date }}</p>
          </div>
          <div class="text-sm font-medium text-gray-900">
            ${{ expense.amount }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { CreditCardIcon } from '@heroicons/vue/24/outline'
import { useExpensesStore } from '@/stores/expenses'

const expensesStore = useExpensesStore()
const expenses = computed(() => expensesStore.expenses)
</script>