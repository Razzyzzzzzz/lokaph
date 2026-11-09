<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import BusinessCard from '@/components/BusinessCard.vue'
import ProductCard from '@/components/ProductCard.vue'
import { api } from '@/api'
import { store } from '@/store'
import { qs } from '@/utils'
const router = useRouter()
const q = ref(''), near = ref([]), newest = ref([]), prod = ref([])
const sections = computed(() => [
  { t: 'Businesses near you', l: near.value.slice(0, 4) }, { t: 'Featured local businesses', l: near.value.filter((b) => b.featured).slice(0, 4) },
  { t: 'Recently added', l: newest.value.slice(0, 4) }].filter((s) => s.l.length))
async function load() {
  try { [near.value, newest.value, prod.value] = await Promise.all([api('/businesses?' + qs({}, store.loc)), api('/businesses?' + qs({ sort: 'newest' }, store.loc)), api('/products?' + qs({ sort: 'trending' }, store.loc))]) }
  catch (e) { store.flash(e.message, true) }
}
watch(() => store.loc, load, { immediate: true })
</script>
<template>
  <div class="space-y-8">
    <div class="rounded-3xl bg-navy p-6 text-white md:p-10">
      <h1 class="max-w-xl text-2xl font-extrabold md:text-4xl">Shop small. Find the stores around you.</h1>
      <p class="mt-2 text-sm text-slate-300 md:text-base">See what local businesses sell, what it costs, and how far away they are.</p>
      <form class="mt-5 flex gap-2" @submit.prevent="router.push({ path: '/discover', query: { q } })"><input v-model="q" class="text-navy" placeholder="Search a store, city or province"><button class="btn btn-primary">Search</button></form>
      <button v-if="!store.loc" class="mt-3 text-sm text-green-300 underline" @click="store.locate">Use my location to see distances</button>
    </div>
    <div><h2 class="mb-3 text-lg font-bold">Popular categories</h2>
      <div class="flex gap-2 overflow-x-auto pb-1"><RouterLink v-for="c in store.cats.business" :key="c.id" :to="{ path: '/discover', query: { category: c.id } }" class="shrink-0 rounded-full border px-4 py-2 text-sm">{{ c.name }}</RouterLink></div></div>
    <div v-for="s in sections" :key="s.t"><div class="mb-3 flex items-end justify-between"><h2 class="text-lg font-bold">{{ s.t }}</h2><RouterLink to="/discover" class="text-sm text-brand">See all</RouterLink></div>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"><BusinessCard v-for="b in s.l" :key="b.id" :b="b" /></div></div>
    <div><h2 class="mb-3 text-lg font-bold">Trending products</h2><div class="grid grid-cols-2 gap-4 lg:grid-cols-4"><ProductCard v-for="p in prod.slice(0, 4)" :key="p.id" :p="p" /></div></div>
  </div>
</template>
