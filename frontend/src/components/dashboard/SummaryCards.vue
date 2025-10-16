<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="card">
      <div class="card-body">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <CreditCardIcon class="h-8 w-8 text-indigo-600" />
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Total Expenses</dt>
              <dd class="text-lg font-medium text-gray-900">$0.00</dd>
            </dl>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <CalendarIcon class="h-8 w-8 text-green-600" />
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">This Month</dt>
              <dd class="text-lg font-medium text-gray-900">$0.00</dd>
            </dl>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <TagIcon class="h-8 w-8 text-yellow-600" />
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Categories</dt>
              <dd class="text-lg font-medium text-gray-900">0</dd>
            </dl>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ChartBarIcon class="h-8 w-8 text-red-600" />
          </div>
          <div class="ml-5 w-0 flex-1">
            <dl>
              <dt class="text-sm font-medium text-gray-500 truncate">Avg/Month</dt>
              <dd class="text-lg font-medium text-gray-900">$0.00</dd>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  CreditCardIcon, 
  CalendarIcon, 
  TagIcon, 
  ChartBarIcon 
} from '@heroicons/vue/24/outline'
</script>