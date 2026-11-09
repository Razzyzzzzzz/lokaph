<script setup>
import BrandLogo from './BrandLogo.vue'
import { store } from '@/store'
defineProps({ title: String, subtitle: String })
const ROLES = { admin: 'Administrator', owner: 'Business owner', customer: 'Customer' }
</script>
<template>
  <div class="flex items-center gap-4 rounded-3xl border bg-white p-4 shadow-sm md:p-5">
    <BrandLogo size="h-16 md:h-20" />
    <div class="min-w-0 flex-1 border-l pl-4">
      <h1 class="truncate text-lg font-extrabold md:text-xl">{{ title }}</h1>
      <p class="truncate text-sm text-slate-500">{{ subtitle || store.user?.name }}</p>
    </div>
    <span class="hidden rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700 sm:inline">{{ ROLES[store.user?.role] }}</span>
  </div>
</template>