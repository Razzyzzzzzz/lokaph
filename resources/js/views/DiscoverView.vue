<script setup>
import { ref, reactive, computed, watch, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import BusinessCard from '@/components/BusinessCard.vue'
import MapView from '@/components/MapView.vue'
import { apiPage } from '@/api'
import { store } from '@/store'
import { qs } from '@/utils'

const PER_PAGE = 12
const KEYS = ['q', 'category', 'city', 'radius', 'sort', 'max_price']
const route = useRoute(), router = useRouter()
// Filters live in the URL so searches can be shared, bookmarked and used with the back button.
const f = reactive({ q: '', category: '', city: '', radius: '', open: false, sort: '', max_price: '' })
Object.assign(f, Object.fromEntries(KEYS.map((k) => [k, route.query[k] || ''])), { open: route.query.open === '1' })

const list = ref([]), total = ref(0), loading = ref(true), loadingMore = ref(false), error = ref('')
const active = ref(null), showFilters = ref(false), view = ref(localStorage.getItem('discoverView') || 'list')
let ctrl, seq = 0, timer

async function load(append = false) {
  ctrl?.abort(); ctrl = new AbortController(); const id = ++seq
  append ? (loadingMore.value = true) : (loading.value = true); error.value = ''
  try {
    const r = await apiPage('/businesses?' + qs({ ...f, limit: PER_PAGE, offset: append ? list.value.length : 0 }, store.loc), ctrl.signal)
    if (id !== seq) return
    list.value = append ? [...list.value, ...r.items] : r.items; total.value = r.total
  } catch (e) { if (e.name !== 'AbortError' && id === seq) error.value = e.message }
  finally { if (id === seq) loading.value = loadingMore.value = false }
}
function sync() {
  const query = Object.fromEntries([...KEYS.map((k) => [k, f[k]]), ['open', f.open ? '1' : '']].filter(([, v]) => v !== ''))
  router.replace({ query }); load()
}
const schedule = (ms = 350) => { clearTimeout(timer); timer = setTimeout(sync, ms) }
watch(f, () => schedule())
watch(() => store.loc, () => load())
watch(view, (v) => localStorage.setItem('discoverView', v))
onBeforeUnmount(() => { clearTimeout(timer); ctrl?.abort() })
load()

// Hover a card to highlight its pin; click a pin to jump to its card.
const select = (id) => { active.value = id; document.getElementById('biz-' + id)?.scrollIntoView({ behavior: 'smooth', block: 'center' }) }
const catName = (id) => store.cats.business.find((c) => String(c.id) === String(id))?.name
const SORTS = { rating: 'Highest rated', newest: 'Newest' }
const chips = computed(() => [
  f.q && { k: 'q', t: `"${f.q}"` }, f.category && { k: 'category', t: catName(f.category) }, f.city && { k: 'city', t: f.city },
  f.radius && { k: 'radius', t: `Within ${f.radius} km` }, f.max_price && { k: 'max_price', t: `Under ₱${f.max_price}` },
  f.open && { k: 'open', t: 'Open now' }, f.sort && { k: 'sort', t: SORTS[f.sort] }].filter(Boolean))
const remove = (k) => { f[k] = k === 'open' ? false : '' }
const reset = () => Object.assign(f, { q: '', category: '', city: '', radius: '', open: false, sort: '', max_price: '' })
const setCat = (id) => { f.category = String(f.category) === String(id) ? '' : id }
// No results (and not loading/failed): the map is still useful, so it stays visible on mobile too.
const empty = computed(() => !loading.value && !error.value && !list.value.length)
const filterCount = computed(() => chips.value.filter((c) => !['q', 'category'].includes(c.k)).length)
</script>
<template>
  <section class="space-y-4">
    <div class="flex flex-wrap items-end justify-between gap-2">
      <div>
        <h1 class="text-2xl font-extrabold">Discover local businesses</h1>
        <p class="text-sm text-slate-500" aria-live="polite">{{ loading ? 'Searching…' : total.toLocaleString() + (total === 1 ? ' business' : ' businesses') }}<span v-if="store.loc"> · nearest to {{ store.loc.label }} first</span></p>
      </div>
      <button class="text-sm text-brand underline" @click="store.locOpen = true">Change location</button>
    </div>

    <form class="flex gap-2" role="search" @submit.prevent="schedule(0)">
      <input v-model="f.q" type="search" aria-label="Search businesses" placeholder="Search name, category, city, province">
      <button class="btn btn-primary">Search</button>
      <button type="button" class="btn btn-outline relative" :aria-expanded="showFilters" @click="showFilters = !showFilters">Filters<span v-if="filterCount" class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-brand text-[11px] text-white">{{ filterCount }}</span></button>
    </form>

    <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 pb-1" role="group" aria-label="Categories">
      <button class="shrink-0 rounded-full border px-4 py-1.5 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-brand" :class="!f.category && 'border-brand bg-brand text-white'" :aria-pressed="!f.category" @click="setCat('')">All</button>
      <button v-for="c in store.cats.business" :key="c.id" class="shrink-0 rounded-full border px-4 py-1.5 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-brand" :class="String(f.category) === String(c.id) && 'border-brand bg-brand text-white'" :aria-pressed="String(f.category) === String(c.id)" @click="setCat(c.id)">{{ c.name }}</button>
    </div>

    <div v-if="showFilters" class="card grid grid-cols-2 gap-3 p-4 text-sm md:grid-cols-4">
      <label>Sort by<select v-model="f.sort"><option value="">Nearest to me</option><option value="rating">Highest rated</option><option value="newest">Newest</option></select></label>
      <label>Distance<select v-model="f.radius"><option value="">Any distance</option><option v-for="r in [1, 5, 10, 25, 50]" :key="r" :value="r">Within {{ r }} km</option></select></label>
      <label>City / province<input v-model="f.city" placeholder="e.g. Cebu"></label>
      <label>Lowest price up to (₱)<input v-model="f.max_price" type="number" min="0"></label>
      <label class="flex items-center gap-2"><input v-model="f.open" type="checkbox">Open now</label>
    </div>

    <div v-if="chips.length" class="flex flex-wrap items-center gap-2 text-sm">
      <button v-for="c in chips" :key="c.k" class="flex items-center gap-1 rounded-full bg-green-50 px-3 py-1 text-green-800" :aria-label="'Remove filter ' + c.t" @click="remove(c.k)">{{ c.t }} <span aria-hidden="true">×</span></button>
      <button class="text-slate-500 underline" @click="reset">Clear all</button>
    </div>

    <p v-if="!store.loc" class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm">Set your location to see distances and sort by nearest. <button class="underline" @click="store.locate">Use my location</button></p>

    <div class="flex overflow-hidden rounded-xl border text-sm lg:hidden" role="tablist"><button class="flex-1 py-2" :class="view === 'list' && 'bg-navy text-white'" role="tab" :aria-selected="view === 'list'" @click="view = 'list'">List</button><button class="flex-1 py-2" :class="view === 'map' && 'bg-navy text-white'" role="tab" :aria-selected="view === 'map'" @click="view = 'map'">Map</button></div>

    <div class="grid items-start gap-4 lg:grid-cols-[minmax(360px,440px)_1fr]">
      <div class="space-y-4" :class="view === 'map' && 'hidden lg:block'">
        <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50 p-6 text-center"><p class="font-semibold text-red-700">Couldn't load businesses</p><p class="text-sm text-red-600">{{ error }}</p><button class="btn btn-outline mt-3" @click="load()">Try again</button></div>
        <div v-else class="grid content-start gap-4 sm:grid-cols-2 lg:grid-cols-1">
          <template v-if="loading"><div v-for="n in 4" :key="n" class="card h-72 animate-pulse bg-slate-100"></div></template>
          <template v-else>
            <div v-for="b in list" :id="'biz-' + b.id" :key="b.id" class="rounded-2xl transition" :class="active === b.id && 'ring-2 ring-brand'" @mouseenter="active = b.id" @mouseleave="active = null"><BusinessCard :b="b" /></div>
            <div v-if="!list.length" class="col-span-full rounded-2xl border border-dashed p-8 text-center text-slate-500"><p class="font-semibold text-navy">No businesses found</p><p class="text-sm">Try a larger radius, another category, or clear the filters.</p><button class="btn btn-outline mt-3" @click="reset">Clear filters</button></div>
          </template>
        </div>
        <div v-if="!loading && !error && list.length" class="text-center text-sm text-slate-500">
          <p>Showing {{ list.length }} of {{ total }}</p>
          <button v-if="list.length < total" class="btn btn-outline mt-2" :disabled="loadingMore" @click="load(true)">{{ loadingMore ? 'Loading…' : 'Load more' }}</button>
        </div>
      </div>
      <div :class="[view === 'list' && !empty && 'hidden lg:block', empty && 'order-first lg:order-none']"><MapView :businesses="list" :loc="store.loc" :active-id="active" :compact="empty" @select="select" /></div>
    </div>
  </section>
</template>