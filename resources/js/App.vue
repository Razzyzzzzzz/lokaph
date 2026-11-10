<script setup>
import { computed, watchEffect } from 'vue'
import { useRoute } from 'vue-router'
import { store } from '@/store'
import AppHeader from '@/components/AppHeader.vue'
import AppFooter from '@/components/AppFooter.vue'
import BottomNav from '@/components/BottomNav.vue'
import LocationModal from '@/components/LocationModal.vue'
import ProductModal from '@/components/ProductModal.vue'
import MessageModal from '@/components/MessageModal.vue'

const route = useRoute()
const bare = computed(() => !!route.meta.bare) // full-screen pages (login/register) hide the app chrome
const home = computed(() => route.path === '/') // home lays out its own full-width sections
watchEffect(() => { document.body.style.paddingBottom = bare.value ? '0' : '' })
</script>
<template>
  <AppHeader v-if="!bare" />
  <div v-if="store.toast" class="fixed inset-x-4 z-50 mx-auto max-w-md rounded-xl px-4 py-3 text-sm text-white shadow-lg" :class="[bare ? 'top-4' : 'top-16', store.toast.bad ? 'bg-red-600' : 'bg-[#7c3aed]']">{{ store.toast.m }}</div>
  <RouterView v-if="bare || home" />
  <!-- Explore re-mounts when its query changes (category/search), so it re-reads the URL filters -->
  <main v-else class="mx-auto max-w-6xl space-y-6 px-4 py-5"><RouterView :key="route.path === '/discover' ? route.fullPath : route.path" /></main>

  <AppFooter v-if="!bare" />

  <LocationModal v-if="store.locOpen" />
  <ProductModal v-if="store.modal?.t === 'product'" />
  <MessageModal v-if="store.modal && store.modal.t !== 'product'" />
  <BottomNav v-if="!bare" />
</template>