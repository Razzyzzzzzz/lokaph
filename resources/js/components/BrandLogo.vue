<script setup>
import { ref } from 'vue'
defineProps({ size: { type: String, default: 'h-8' } })
const LOGO = '/assets/uploads/logo.png' // dynamic binding: resolved at runtime, not bundled
const failed = ref(false) // falls back to the text logo if the image is missing
</script>
<template>
  <img v-if="!failed" :src="LOGO" alt="LokalPH" :class="[size, 'w-auto object-contain']" @error="failed = true">
  <span v-else class="text-xl font-extrabold tracking-tight">Lokal<span class="text-brand">PH</span></span>
</template>