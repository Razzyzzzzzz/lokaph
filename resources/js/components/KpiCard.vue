<script setup>
import { computed } from 'vue'
const props = defineProps({ label: String, value: [Number, String], prev: Number, suffix: { type: String, default: '' } })
// Percentage change versus the previous period of the same length.
const delta = computed(() => props.prev == null ? null : props.prev === 0 ? (props.value > 0 ? 100 : 0) : Math.round(((props.value - props.prev) / props.prev) * 100))
</script>
<template>
  <div class="stat"><span class="text-xs text-slate-500">{{ label }}</span><b class="block text-2xl">{{ value }}{{ suffix }}</b>
    <span v-if="delta !== null" class="text-xs font-semibold" :class="delta > 0 ? 'text-green-600' : delta < 0 ? 'text-red-600' : 'text-slate-400'">{{ delta > 0 ? '▲' : delta < 0 ? '▼' : '–' }} {{ Math.abs(delta) }}% <span class="font-normal text-slate-400">vs previous</span></span></div>
</template>