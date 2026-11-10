<script setup>
import { ref, computed } from 'vue'
// Dependency-free SVG line chart with hover tooltip. series: [{ name, color, values: number[] }]
const props = defineProps({ labels: Array, series: Array })
const W = 600, H = 200, L = 34, R = 8, T = 10, B = 22, hover = ref(-1)
const n = computed(() => props.labels.length)
const top = computed(() => Math.max(4, Math.ceil(Math.max(0, ...props.series.flatMap((s) => s.values)) / 4) * 4))
const x = (i) => L + (n.value > 1 ? (i * (W - L - R)) / (n.value - 1) : 0)
const y = (v) => H - B - (v / top.value) * (H - T - B)
const path = (vals) => vals.map((v, i) => `${i ? 'L' : 'M'}${x(i).toFixed(1)},${y(v).toFixed(1)}`).join('')
const fmt = (d) => new Date(d + 'T00:00:00').toLocaleDateString('en-PH', { month: 'short', day: 'numeric' })
const ticks = computed(() => [0, 1, 2, 3, 4].map((k) => (top.value * k) / 4))
function move(e) { const r = e.currentTarget.getBoundingClientRect(); const px = ((e.clientX - r.left) / r.width) * W
  hover.value = n.value > 1 ? Math.min(n.value - 1, Math.max(0, Math.round(((px - L) / (W - L - R)) * (n.value - 1)))) : 0 }
</script>
<template>
  <div>
    <div class="mb-2 flex flex-wrap gap-4 text-xs"><span v-for="s in series" :key="s.name" class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full" :style="{ background: s.color }"></span>{{ s.name }}</span></div>
    <div class="relative">
      <svg :viewBox="`0 0 ${W} ${H}`" class="w-full touch-none select-none" role="img" aria-label="Trend chart" @pointermove="move" @pointerleave="hover = -1">
        <g v-for="t in ticks" :key="t"><line :x1="L" :x2="W - R" :y1="y(t)" :y2="y(t)" stroke="#e2e8f0" /><text :x="L - 6" :y="y(t) + 3" text-anchor="end" font-size="10" fill="#94a3b8">{{ t }}</text></g>
        <text :x="L" :y="H - 6" font-size="10" fill="#94a3b8">{{ fmt(labels[0]) }}</text>
        <text :x="W - R" :y="H - 6" text-anchor="end" font-size="10" fill="#94a3b8">{{ fmt(labels[n - 1]) }}</text>
        <path v-for="s in series" :key="s.name" :d="path(s.values)" fill="none" :stroke="s.color" stroke-width="2" stroke-linejoin="round" />
        <template v-if="hover >= 0"><line :x1="x(hover)" :x2="x(hover)" :y1="T" :y2="H - B" stroke="#94a3b8" stroke-dasharray="3" /><circle v-for="s in series" :key="s.name" :cx="x(hover)" :cy="y(s.values[hover])" r="4" :fill="s.color" stroke="#fff" stroke-width="2" /></template>
      </svg>
      <div v-if="hover >= 0" class="pointer-events-none absolute top-0 z-10 -translate-x-1/2 rounded-lg bg-navy px-3 py-2 text-xs text-white shadow-lg" :style="{ left: Math.min(88, Math.max(12, (x(hover) / W) * 100)) + '%' }">
        <b>{{ fmt(labels[hover]) }}</b><div v-for="s in series" :key="s.name" class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full" :style="{ background: s.color }"></span>{{ s.name }}: {{ s.values[hover] }}</div></div>
    </div>
  </div>
</template>