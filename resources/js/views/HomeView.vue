<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import BusinessCard from '@/components/BusinessCard.vue'
import Icon from '@/components/Icon.vue'
import { api } from '@/api'
import { store } from '@/store'
import { qs } from '@/utils'
const router = useRouter()
const q = ref(''), near = ref([])
const featured = computed(() => { const f = near.value.filter((b) => b.featured); return (f.length >= 4 ? f : near.value).slice(0, 4) })
const ICONS = [['food', 'utensils'], ['drink', 'utensils'], ['coffee', 'coffee'], ['fashion', 'shirt'], ['cloth', 'shirt'], ['beauty', 'sparkles'], ['electron', 'laptop'], ['home', 'home'], ['pet', 'paw'], ['bake', 'cake'], ['shop', 'bag'], ['service', 'wrench']]
const icon = (n) => (ICONS.find(([k]) => n.toLowerCase().includes(k)) || [0, 'store'])[1]
const cats = computed(() => store.cats.business.slice(0, 9))
const pins = [[20, 25], [60, 15], [78, 40], [30, 62], [72, 72], [12, 80]]
async function load() {
  try { near.value = await api('/businesses?' + qs({}, store.loc)) }
  catch (e) { store.flash(e.message, true) }
}
watch(() => store.loc, load, { immediate: true })
const go = (query) => router.push({ path: '/discover', query })
</script>
<template>
  <div>
    <!-- HERO -->
    <section class="relative overflow-hidden bg-gradient-to-br from-[#0b1f3a] via-[#12307a] to-[#0b1f3a] pb-16 pt-8 text-white md:pb-24 md:pt-14">
      <div class="pointer-events-none absolute -right-20 top-0 h-96 w-96 rounded-full bg-[#7c3aed]/30 blur-3xl"></div>
      <div class="relative mx-auto grid max-w-7xl items-center gap-6 px-4 md:grid-cols-2 md:gap-8">
        <div>
          <p class="text-2xl text-[#fbbf24] md:text-3xl" style="font-family: 'Kaushan Script', cursive">Discover the Best of Local!</p>
          <h1 class="mt-2 text-3xl font-extrabold leading-tight sm:text-4xl md:text-6xl">Support <span class="text-[#fbbf24]">Filipino</span><br>Small Businesses</h1>
          <p class="mt-3 max-w-md text-sm text-slate-200 md:mt-4 md:text-lg">Find amazing local shops, products, and services near you. Shop local, help our community grow!</p>
          <form class="mt-5 flex w-full max-w-xl overflow-hidden rounded-xl bg-white shadow-xl md:mt-6" @submit.prevent="go({ q })">
            <span class="grid place-items-center pl-3 text-[#7c3aed]"><Icon name="pin" :size="20" /></span>
            <input v-model="q" class="min-w-0 flex-1 !rounded-none !border-0 text-[#0b1f3a] focus:outline-none" placeholder="Search businesses, products, or categories...">
            <button class="shrink-0 bg-[#7c3aed] px-5 text-white" aria-label="Search"><Icon name="search" :size="20" /></button>
          </form>
          <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
            <button class="flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5" @click="store.locOpen = true"><Icon name="pin" :size="13" /> {{ store.loc ? store.loc.label : 'Set location' }}</button>
            <span class="text-white/70">Popular:</span>
            <button v-for="p in ['Food', 'Coffee', 'Clothes', 'Bakery']" :key="p" class="rounded-full bg-white/15 px-3 py-1.5" @click="go({ q: p })">{{ p }}</button>
          </div>
          <button v-if="!store.loc" class="mt-3 text-sm text-[#fbbf24] underline" @click="store.locate">Use my location to see distances</button>
        </div>
        <div class="relative mx-auto w-full max-w-xl">
          <!-- left fade only on desktop, where the image sits next to the text -->
          <img src="/assets/uploads/1.png" alt="Shop local with LokalPH" class="w-full rounded-3xl object-cover shadow-2xl md:[-webkit-mask-image:linear-gradient(to_right,transparent_0,#000_22%)] md:[mask-image:linear-gradient(to_right,transparent_0,#000_22%)]">
        </div>
      </div>
    </section>

    <div class="mx-auto max-w-7xl space-y-8 px-4 pb-10">
      <!-- CATEGORIES: even 4-column grid on mobile, scrolling row on desktop -->
      <section class="relative z-10 -mt-8 rounded-2xl bg-white p-4 shadow-xl md:-mt-12">
        <div class="no-scrollbar grid grid-cols-4 gap-x-2 gap-y-4 md:flex md:gap-3 md:overflow-x-auto">
          <RouterLink v-for="(c, i) in cats" :key="c.id" :to="{ path: '/discover', query: { category: c.id } }"
            class="flex flex-col items-center gap-1.5 text-center text-xs font-medium md:w-20 md:shrink-0" :class="i >= 8 && 'hidden md:flex'">
            <span class="grid h-14 w-14 place-items-center rounded-2xl bg-[#7c3aed]/10 text-[#7c3aed]"><Icon :name="icon(c.name)" :size="26" /></span>
            <span class="line-clamp-2 leading-tight">{{ c.name }}</span></RouterLink>
        </div>
      </section>

      <!-- FEATURED + MAP -->
      <section class="grid gap-5 lg:grid-cols-[1fr_300px]">
        <div>
          <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div><h2 class="flex items-center gap-2 text-xl font-extrabold"><Icon name="star" :size="22" fill class="text-amber-400" /> Featured Local Businesses</h2><p class="text-sm text-slate-500">Discover trusted and highly rated local businesses near you.</p></div>
            <RouterLink to="/discover" class="flex items-center justify-center gap-1.5 rounded-lg bg-[#7c3aed]/10 px-3 py-2 text-xs font-semibold text-[#7c3aed] sm:py-1.5">View More Businesses <Icon name="arrow" :size="13" /></RouterLink>
          </div>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"><BusinessCard v-for="b in featured" :key="b.id" :b="b" /></div>
          <p v-if="!featured.length" class="rounded-xl border p-6 text-center text-sm text-slate-500">No businesses found yet.</p>
        </div>
        <aside class="card p-4">
          <div class="mb-3"><h3 class="flex items-center gap-1.5 font-bold"><Icon name="pin" :size="18" class="text-[#7c3aed]" /> Nearby Businesses</h3></div>
          <div class="map-bg relative h-64 overflow-hidden rounded-xl lg:h-[19rem]">
            <span v-for="(p, i) in pins" :key="i" class="absolute -translate-x-1/2 -translate-y-full text-[#7c3aed]" :style="{ left: p[0] + '%', top: p[1] + '%' }"><Icon name="pin" :size="24" fill /></span>
            <span class="absolute left-[42%] top-[45%] rounded-lg bg-white px-2 py-1 text-xs font-medium shadow">You are here</span>
            <span class="absolute bottom-3 left-1/2 flex -translate-x-1/2 items-center gap-1.5 rounded-full bg-white px-4 py-1.5 text-xs font-semibold shadow"><Icon name="pin" :size="13" class="text-[#7c3aed]" /> Within 5 km</span>
          </div>
        </aside>
      </section>
    </div>
  </div>
</template>