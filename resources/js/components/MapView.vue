<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import L, { TILES, TILE_OPTS, pin, pinActive, phMap } from '@/lib/leaflet'
import { km, dirUrl, esc } from '@/utils'
const props = defineProps({ businesses: Array, loc: Object, activeId: Number, compact: Boolean })
const emit = defineEmits(['select'])
const el = ref(null), router = useRouter()
let map, layer, ro, pendingFit = false, markers = {}
const PH_CENTER = [12.88, 121.77]
function popup(b) {
  const d = document.createElement('div')
  d.innerHTML = `<b>${esc(b.name)}</b><br>${b.logo ? `<img src="${esc(b.logo)}" width="80"><br>` : ''}${km(b.distance)}<br><a href="#" class="v">View details</a> · <a target="_blank" rel="noopener" href="${dirUrl(b)}">Directions</a>`
  d.querySelector('.v').addEventListener('click', (e) => { e.preventDefault(); router.push('/business/' + b.id) })
  return d
}
// Fit the view to the pins; with no pins, fall back to the user's location or the whole Philippines.
function fit(pts) {
  if (pts.length) map.fitBounds(pts, { maxZoom: 15, padding: [30, 30] })
  else if (props.loc) map.setView([props.loc.lat, props.loc.lng], 13)
  else map.setView(PH_CENTER, 6)
}
function draw() {
  if (!map) return
  layer.clearLayers(); markers = {}; const pts = []
  props.businesses.forEach((b) => {
    pts.push([b.lat, b.lng])
    markers[b.id] = L.marker([b.lat, b.lng], { icon: pin }).addTo(layer).bindPopup(popup(b)).on('click', () => emit('select', b.id))
  })
  if (props.loc) L.circleMarker([props.loc.lat, props.loc.lng], { radius: 8, color: '#16a34a' }).addTo(layer).bindPopup('You are here')
  // The map can be display:none on mobile (List tab). Fitting a 0px map gives a wrong view, so wait until it is visible.
  if (!el.value.offsetWidth) { pendingFit = true; return }
  map.invalidateSize(); fit(pts)
}
onMounted(() => {
  map = L.map(el.value, phMap).setView(PH_CENTER, 6); L.tileLayer(TILES, TILE_OPTS).addTo(map); layer = L.layerGroup().addTo(map)
  // Runs whenever the container changes size, including when the Map tab / empty state makes it visible.
  ro = new ResizeObserver(() => {
    if (!el.value.offsetWidth) return
    map.invalidateSize()
    if (pendingFit) { pendingFit = false; fit(props.businesses.map((b) => [b.lat, b.lng])) }
  })
  ro.observe(el.value); draw()
})
watch(() => [props.businesses, props.loc], draw)
watch(() => props.activeId, (id) => Object.entries(markers).forEach(([k, m]) => { m.setIcon(+k === id ? pinActive : pin); m.setZIndexOffset(+k === id ? 1000 : 0) }))
onBeforeUnmount(() => { ro?.disconnect(); map && map.remove() })
</script>
<template><div ref="el" class="rounded-2xl border shadow-sm lg:sticky lg:top-20 lg:h-[calc(100vh-6.5rem)]" :class="compact ? 'h-64 sm:h-80' : 'h-[70vh]'"></div></template>