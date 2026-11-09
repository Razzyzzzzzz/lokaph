<script setup>
import { ref, watch, onMounted } from 'vue'
import ModalSheet from './ModalSheet.vue'
import { store } from '@/store'

const KINDS = { house: 'Address', street: 'Street', locality: 'Area', district: 'Barangay', city: 'City', county: 'Province', state: 'Region', other: 'Place' }
const input = ref(null), q = ref(''), results = ref([]), busy = ref(false), empty = ref(false), active = ref(-1)
let timer, seq = 0, ctrl

function toPlace(f) {
  const p = f.properties, [lng, lat] = f.geometry.coordinates
  const street = [p.housenumber, p.street].filter(Boolean).join(' ')
  const title = p.name || street || p.district || p.city || p.county || p.state || 'Unnamed place'
  const parts = [street, p.district, p.locality, p.city, p.county, p.state].filter((x, i, a) => x && x !== title && a.indexOf(x) === i)
  const kind = ['town', 'municipality'].includes(p.osm_value) ? 'Town' : KINDS[p.type] || 'Place'
  return { key: lat.toFixed(4) + ',' + lng.toFixed(4) + title, lat, lng, title, sub: parts.join(', '), kind,
    short: [title, p.city && p.city !== title ? p.city : p.county && p.county !== title ? p.county : ''].filter(Boolean).join(', ') }
}

// Search-as-you-type (Photon, built for autocomplete). Results start from the first letter and favor places near the current location.
watch(q, (v) => {
  clearTimeout(timer); empty.value = false; active.value = -1
  if (!v.trim()) { ctrl?.abort(); results.value = []; busy.value = false; return }
  timer = setTimeout(async () => {
    ctrl?.abort(); ctrl = new AbortController(); const id = ++seq; busy.value = true
    try {
      const params = new URLSearchParams({ q: v.trim(), limit: '10', lang: 'en', bbox: '116,4.5,127,21.5' })
      if (store.loc) { params.set('lat', store.loc.lat); params.set('lon', store.loc.lng) }
      const data = await (await fetch('https://photon.komoot.io/api/?' + params, { signal: ctrl.signal })).json()
      if (id !== seq) return
      const seen = new Set()
      results.value = (data.features || []).filter((f) => f.properties.countrycode === 'PH').map(toPlace).filter((p) => !seen.has(p.key) && seen.add(p.key))
      empty.value = !results.value.length
    } catch (e) { if (e.name !== 'AbortError') store.flash('Search failed. Check your internet connection.', true) }
    finally { if (id === seq) busy.value = false }
  }, 180)
})
const pick = (r) => store.setLoc({ lat: r.lat, lng: r.lng, label: r.short.slice(0, 30) })
const enter = () => { const r = results.value[active.value >= 0 ? active.value : 0]; if (r) pick(r) }
onMounted(() => input.value?.focus())
</script>
<template>
  <ModalSheet @close="store.locOpen = false">
    <h3 class="text-lg font-bold">Your location</h3>
    <button class="btn btn-primary w-full" @click="store.locate">Use my device location</button>
    <input ref="input" v-model="q" type="search" autocomplete="off" placeholder="Search street, barangay, city or province" aria-label="Search a place in the Philippines"
      @keydown.down.prevent="active = Math.min(active + 1, results.length - 1)" @keydown.up.prevent="active = Math.max(active - 1, 0)" @keydown.enter.prevent="enter">
    <p v-if="!q" class="-mt-1 text-xs text-slate-500">Start typing. Add the street and town for exact spots, e.g. “Mabini St Ermita”.</p>
    <p v-if="busy && !results.length" class="text-sm text-slate-500">Searching…</p>
    <p v-if="empty" class="text-sm text-slate-500">No place found. Try fewer words or a different spelling.</p>
    <button v-for="(r, i) in results" :key="r.key" class="flex w-full items-start gap-3 rounded-xl border p-3 text-left text-sm hover:border-brand" :class="i === active && 'border-brand bg-green-50'" @click="pick(r)" @mouseenter="active = i">
      <span class="mt-0.5 shrink-0 rounded-full bg-green-50 px-2 py-0.5 text-[11px] text-green-700">{{ r.kind }}</span>
      <span><b class="block">{{ r.title }}</b><span class="text-slate-500">{{ r.sub }}</span></span>
    </button>
    <template v-if="!q && store.recent.length">
      <p class="text-sm text-slate-500">Recent</p>
      <button v-for="r in store.recent" :key="r.lat + ',' + r.lng" class="w-full rounded-xl border p-3 text-left text-sm hover:border-brand" @click="store.setLoc(r)">{{ r.label }}</button>
    </template>
  </ModalSheet>
</template>