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
    <section class="relative overflow-hidden bg-gradient-to-br from-[#0b1f3a] via-[#12307a] to-[#0b1f3a] pb-16 pt-10 text-white md:pb-24 md:pt-14">
      <div class="pointer-events-none absolute -right-20 top-0 h-96 w-96 rounded-full bg-[#7c3aed]/30 blur-3xl"></div>
      <div class="relative mx-auto grid max-w-7xl items-center gap-8 px-4 md:grid-cols-2">
        <div>
          <p class="text-2xl text-[#fbbf24] md:text-3xl" style="font-family: 'Kaushan Script', cursive">Discover the Best of Local!</p>
          <h1 class="mt-2 text-4xl font-extrabold leading-tight md:text-6xl">Support <span class="text-[#fbbf24]">Filipino</span><br>Small Businesses</h1>
          <p class="mt-4 max-w-md text-slate-200 md:text-lg">Find amazing local shops, products, and services near you. Shop local, help our community grow!</p>
          <form class="mt-6 flex max-w-xl overflow-hidden rounded-xl bg-white shadow-xl" @submit.prevent="go({ q })">
            <span class="grid place-items-center pl-3 text-[#7c3aed]"><Icon name="pin" :size="20" /></span>
            <input v-model="q" class="!rounded-none !border-0 text-[#0b1f3a] focus:outline-none" placeholder="Search businesses, products, or categories...">
            <button class="bg-[#7c3aed] px-5 text-white" aria-label="Search"><Icon name="search" :size="20" /></button>
          </form>
          <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
            <button class="flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5" @click="store.locOpen = true"><Icon name="pin" :size="13" /> {{ store.loc ? store.loc.label : 'Set location' }}</button>
            <span class="text-white/70">Popular:</span>
            <button v-for="p in ['Food', 'Coffee', 'Clothes', 'Bakery']" :key="p" class="rounded-full bg-white/15 px-3 py-1.5" @click="go({ q: p })">{{ p }}</button>
          </div>
          <button v-if="!store.loc" class="mt-3 text-sm text-[#fbbf24] underline" @click="store.locate">Use my location to see distances</button>
        </div>
        <div class="relative mx-auto w-full max-w-xl">
          <img src="/assets/uploads/1.png" alt="Shop local with LokalPH" class="w-full rounded-3xl object-cover shadow-2xl"
            style="-webkit-mask-image: linear-gradient(to right, transparent 0, #000 22%); mask-image: linear-gradient(to right, transparent 0, #000 22%)">
        </div>
      </div>
    </section>

    <div class="mx-auto max-w-7xl space-y-8 px-4">
      <!-- CATEGORIES -->
      <section class="relative z-10 -mt-8 rounded-2xl bg-white p-4 shadow-xl md:-mt-12">
        <div class="no-scrollbar flex gap-3 overflow-x-auto">
          <RouterLink v-for="c in cats" :key="c.id" :to="{ path: '/discover', query: { category: c.id } }" class="flex w-20 shrink-0 flex-col items-center gap-1.5 text-center text-xs font-medium">
            <span class="grid h-14 w-14 place-items-center rounded-2xl bg-[#7c3aed]/10 text-[#7c3aed]"><Icon :name="icon(c.name)" :size="26" /></span>{{ c.name }}</RouterLink>
          <RouterLink to="/discover" class="ml-auto flex shrink-0 items-center gap-1.5 rounded-2xl bg-[#7c3aed]/10 px-5 text-sm font-semibold text-[#7c3aed]">View All <Icon name="arrow" :size="14" /></RouterLink>
        </div>
      </section>

      <!-- FEATURED + MAP -->
      <section class="grid gap-5 lg:grid-cols-[1fr_300px]">
        <div>
          <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
            <div><h2 class="flex items-center gap-2 text-xl font-extrabold"><Icon name="star" :size="22" fill class="text-amber-400" /> Featured Local Businesses</h2><p class="text-sm text-slate-500">Discover trusted and highly rated local businesses near you.</p></div>
            <RouterLink to="/discover" class="flex items-center gap-1.5 rounded-lg bg-[#7c3aed]/10 px-3 py-1.5 text-xs font-semibold text-[#7c3aed]">View More Businesses <Icon name="arrow" :size="13" /></RouterLink>
          </div>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"><BusinessCard v-for="b in featured" :key="b.id" :b="b" /></div>
          <p v-if="!featured.length" class="rounded-xl border p-6 text-center text-sm text-slate-500">No businesses found yet.</p>
        </div>
        <aside class="card p-4">
          <div class="mb-3 flex items-center justify-between"><h3 class="flex items-center gap-1.5 font-bold"><Icon name="pin" :size="18" class="text-[#7c3aed]" /> Nearby Businesses</h3><RouterLink to="/discover" class="rounded-lg border px-2 py-1 text-xs">See All</RouterLink></div>
          <div class="map-bg relative h-64 overflow-hidden rounded-xl lg:h-[19rem]">
            <span v-for="(p, i) in pins" :key="i" class="absolute -translate-x-1/2 -translate-y-full text-[#7c3aed]" :style="{ left: p[0] + '%', top: p[1] + '%' }"><Icon name="pin" :size="24" fill /></span>
            <span class="absolute left-[42%] top-[45%] rounded-lg bg-white px-2 py-1 text-xs font-medium shadow">You are here</span>
            <span class="absolute bottom-3 left-1/2 flex -translate-x-1/2 items-center gap-1.5 rounded-full bg-white px-4 py-1.5 text-xs font-semibold shadow"><Icon name="pin" :size="13" class="text-[#7c3aed]" /> Within 5 km</span>
          </div>
        </aside>
      </section>
    </div>

    <!-- CTA BANNER -->
    <section class="mt-10 bg-gradient-to-r from-[#0b1f3a] via-[#4c1d95] to-[#7c3aed] text-white">
      <div class="mx-auto grid max-w-7xl items-center gap-6 px-4 py-10 md:grid-cols-[auto_1fr_auto]">
        <span class="mx-auto grid h-20 w-20 place-items-center rounded-3xl bg-white/15 shadow-lg"><Icon name="phone" :size="40" /></span>
        <div class="text-center md:text-left">
          <h2 class="text-2xl font-extrabold md:text-3xl">Be a Part of LokalPH Movement!</h2>
          <p class="mt-1 text-sm text-white/80">Whether you're a customer or business owner, LokalPH is here for you.</p>
        </div>
        <div class="text-center md:text-right">
          <p class="text-2xl text-[#fbbf24]" style="font-family: 'Kaushan Script', cursive">Shop Local, Grow Together!</p>
          <RouterLink to="/register?role=owner" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-[#fbbf24] px-8 py-3 text-sm font-bold text-[#0b1f3a] shadow-lg">Join Now <Icon name="arrow" :size="16" /></RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>