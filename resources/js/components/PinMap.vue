<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import L, { TILES, TILE_OPTS, phMap } from '@/lib/leaflet'
import { store } from '@/store'
import { isInPH } from '@/utils'
const props = defineProps({ lat: [Number, String], lng: [Number, String], editable: Boolean })
const emit = defineEmits(['pick'])
const el = ref(null)
let map, marker
const pos = () => [+props.lat || 14.5995, +props.lng || 120.9842]
onMounted(() => {
  map = L.map(el.value, phMap).setView(pos(), 15); L.tileLayer(TILES, TILE_OPTS).addTo(map); marker = L.marker(pos()).addTo(map)
  if (props.editable) map.on('click', (e) => { if (!isInPH(e.latlng.lat, e.latlng.lng)) return store.flash('Pick a location inside the Philippines.', true); marker.setLatLng(e.latlng); emit('pick', { lat: e.latlng.lat.toFixed(7), lng: e.latlng.lng.toFixed(7) }) })
  setTimeout(() => map.invalidateSize(), 150)
})
watch(() => [props.lat, props.lng], () => { marker.setLatLng(pos()); map.setView(pos()) })
onBeforeUnmount(() => map && map.remove())
</script>
<template><div ref="el" class="h-56 rounded-2xl border"></div></template>
