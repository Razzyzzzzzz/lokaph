<script setup>
import { computed } from 'vue'
import PhImg from './PhImg.vue'
import Icon from './Icon.vue'
import { store } from '@/store'
import { km, dirUrl } from '@/utils'
const props = defineProps({ b: Object })
const badge = computed(() => {
  const b = props.b
  if (b.rating >= 4.8) return { t: 'Top Rated', c: 'bg-pink-500' }
  if (b.reviews >= 100) return { t: 'Popular', c: 'bg-blue-500' }
  if (b.featured) return { t: 'Verified', c: 'bg-green-600' }
  return null
})
const saved = computed(() => store.favs.businesses.includes(props.b.id))
</script>
<template>
  <div class="card relative flex flex-col overflow-hidden">
    <RouterLink :to="'/business/' + b.id" class="relative block h-32">
      <PhImg :src="b.cover || b.logo" :name="b.name" />
      <span v-if="badge" class="absolute left-2 top-2 rounded-full px-2.5 py-0.5 text-[11px] font-semibold text-white" :class="badge.c">{{ badge.t }}</span>
      <span v-if="b.is_sample" class="absolute bottom-2 left-2 rounded-full bg-amber-300 px-2 text-[10px]">Sample</span>
    </RouterLink>
    <button class="absolute right-2 top-2 grid h-8 w-8 place-items-center rounded-full bg-white shadow" :class="saved ? 'text-red-500' : 'text-slate-500'" :aria-label="(saved ? 'Remove ' : 'Save ') + b.name" :aria-pressed="saved" @click="store.toggleFav('business', b.id)"><Icon name="heart" :size="16" :fill="saved" /></button>
    <div class="flex flex-1 flex-col gap-1.5 p-3">
      <h3 class="font-bold leading-tight">{{ b.name }}</h3>
      <div class="flex items-center justify-between text-xs">
        <span class="flex items-center gap-1"><Icon name="star" :size="14" fill class="text-amber-400" /><b class="text-amber-600">{{ b.rating || 'New' }}</b><span class="text-slate-500">({{ b.reviews }} reviews)</span></span>
        <b v-if="b.distance != null" class="flex items-center gap-1 text-[#0b1f3a]"><Icon name="pin" :size="13" />{{ km(b.distance) }}</b>
      </div>
      <span class="w-fit rounded-md bg-[#7c3aed]/10 px-2 py-0.5 text-[11px] text-[#7c3aed]">{{ b.category }}</span>
      <div class="mt-auto flex gap-2 pt-2">
        <RouterLink :to="'/business/' + b.id" class="flex-1 rounded-lg bg-[#0b1f3a] px-2 py-2 text-center text-xs font-semibold text-white">View Details</RouterLink>
        <a :href="dirUrl(b)" target="_blank" rel="noopener" class="flex-1 rounded-lg border border-[#7c3aed]/40 px-2 py-2 text-center text-xs font-semibold text-[#7c3aed]">Get Directions</a>
      </div>
    </div>
  </div>
</template>