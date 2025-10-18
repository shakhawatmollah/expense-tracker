<template>
  <aside class="w-64 bg-white shadow-sm border-r border-gray-200 min-h-screen">
    <nav class="mt-5 px-2">
      <div class="space-y-1">
        <router-link
          v-for="item in navigation"
          :key="item.name"
          :to="item.href"
          :class="[
            $route.name === item.name
              ? 'bg-indigo-100 text-indigo-700'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
            'group flex items-center px-2 py-2 text-sm font-medium rounded-md'
          ]"
        >
          <component
            :is="item.icon"
            :class="[
              $route.name === item.name ? 'text-indigo-500' : 'text-gray-400',
              'mr-3 h-6 w-6'
            ]"
            aria-hidden="true"
          />
          {{ item.name }}
        </router-link>
      </div>
    </nav>
  </aside>
</template>

<script setup>
import { 
  HomeIcon, 
  CreditCardIcon, 
  TagIcon,
  ChartBarIcon,
  ChartPieIcon
} from '@heroicons/vue/24/outline'

const navigation = [
  { name: 'Dashboard', href: '/', icon: HomeIcon },
  { name: 'Expenses', href: '/expenses', icon: CreditCardIcon },
  { name: 'Categories', href: '/categories', icon: TagIcon },
  { name: 'Budgets', href: '/budgets', icon: ChartBarIcon },
  { name: 'Analytics', href: '/analytics', icon: ChartPieIcon },
]
</script>