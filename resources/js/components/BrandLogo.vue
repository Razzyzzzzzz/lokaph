<script setup>
import { ref } from 'vue'
defineProps({ size: { type: String, default: 'h-8' }, tagline: Boolean })
const LOGO = '/assets/uploads/logo.png' // dynamic binding: resolved at runtime, not bundled
const failed = ref(false) // falls back to the built-in logo if the image is missing
</script>
<template>
  <img v-if="!failed" :src="LOGO" alt="LokalPH" :class="[size, 'w-auto object-contain']" @error="failed = true">
  <span v-else class="flex items-center gap-2">
    <svg viewBox="0 0 40 48" class="h-10 w-8 shrink-0"><path d="M20 47C8 33 2 25 2 17a18 18 0 1 1 36 0c0 8-6 16-18 30z" fill="#ef4444" stroke="#fff" stroke-width="2.5"/><rect x="9" y="11" width="22" height="7" rx="2" fill="#fff"/><path d="M9 11h5v7H9zM19 11h5v7h-5z" fill="#2563eb"/><rect x="11" y="19" width="18" height="10" rx="1" fill="#fff"/></svg>
    <span class="leading-none"><span class="block text-2xl font-extrabold tracking-tight">Lokal<span class="text-gold">PH</span></span>
      <span v-if="tagline" class="mt-1 block text-[10px] font-medium opacity-80">Find Local. Shop Local.</span></span>
  </span>
</template>