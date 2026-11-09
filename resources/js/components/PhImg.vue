<script setup>defineProps({ src: String, name: String })</script>
<template>
  <img v-if="src" :src="src" loading="lazy" class="h-full w-full object-cover" :alt="name" />
  <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-emerald-200 to-green-600 text-3xl font-bold text-white">{{ (name || '?')[0] }}</div>
</template>
