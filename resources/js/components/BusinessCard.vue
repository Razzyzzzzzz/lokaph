<script setup>
import PhImg from './PhImg.vue'
import { store } from '@/store'
import { km, dirUrl, peso } from '@/utils'
defineProps({ b: Object })
</script>
<template>
  <div class="card flex flex-col overflow-hidden">
    <RouterLink :to="'/business/' + b.id" class="relative block h-36">
      <PhImg :src="b.cover || b.logo" :name="b.name" />
      <span v-if="b.is_sample" class="absolute left-2 top-2 rounded-full bg-amber-300 px-2 text-[10px]">Sample</span>
      <span class="absolute right-2 top-2 rounded-full px-2 py-0.5 text-xs text-white" :class="b.is_open ? 'bg-green-600' : 'bg-slate-500'">{{ b.is_open ? 'Open' : 'Closed' }}</span>
    </RouterLink>
    <div class="flex flex-1 flex-col gap-1 p-3">
      <div class="flex justify-between gap-2"><h3 class="font-bold leading-tight">{{ b.name }}</h3>
        <button class="text-xl leading-none" :aria-label="(store.favs.businesses.includes(b.id) ? 'Remove ' : 'Save ') + b.name" :aria-pressed="store.favs.businesses.includes(b.id)" @click="store.toggleFav('business', b.id)">{{ store.favs.businesses.includes(b.id) ? '❤️' : '🤍' }}</button></div>
      <p class="text-xs text-slate-500">{{ b.category }} · {{ b.address }}, {{ b.city }}</p>
      <p class="text-sm"><b class="text-brand">{{ km(b.distance) }}</b> <span v-if="b.distance != null">·</span> ★ {{ b.rating || 'New' }} ({{ b.reviews }}) · {{ b.products }} products<span v-if="b.min_price != null"> · from {{ peso(b.min_price) }}</span></p>
      <div class="mt-auto flex gap-2 pt-2">
        <RouterLink :to="'/business/' + b.id" class="btn btn-primary flex-1 !py-2">View business</RouterLink>
        <a :href="dirUrl(b)" target="_blank" rel="noopener" class="btn btn-outline flex-1 !py-2">Directions</a></div>
    </div>
  </div>
</template>