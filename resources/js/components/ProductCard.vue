<script setup>
import PhImg from './PhImg.vue'
import { store } from '@/store'
import { km, peso } from '@/utils'
defineProps({ p: Object })
</script>
<template>
  <div class="card flex flex-col overflow-hidden">
    <button class="block h-32 w-full" @click="store.openProduct(p)"><PhImg :src="p.image" :name="p.name" /></button>
    <div class="flex flex-1 flex-col gap-1 p-3 text-sm">
      <h3 class="font-bold leading-tight">{{ p.name }}</h3><p class="font-bold text-brand">{{ peso(p.price) }}</p>
      <p class="text-xs text-slate-500">{{ p.business }} · {{ p.category }}</p><p class="line-clamp-2 text-xs">{{ p.description }}</p>
      <p class="text-xs"><span :class="p.available && p.stock > 0 ? 'text-brand' : 'text-red-600'">{{ p.available && p.stock > 0 ? 'In stock (' + p.stock + ')' : 'Unavailable' }}</span> <span v-if="p.distance != null">· {{ km(p.distance) }}</span></p>
      <div class="mt-auto flex gap-2 pt-2">
        <button class="btn btn-primary flex-1 !py-2 !text-xs" @click="store.openProduct(p)">View</button>
        <button class="btn btn-outline flex-1 !py-2 !text-xs" @click="store.openContact(p)">Contact seller</button></div>
    </div>
  </div>
</template>
