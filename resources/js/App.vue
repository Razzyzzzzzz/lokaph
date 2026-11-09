<script setup>
import { computed, watchEffect } from 'vue'
import { useRoute } from 'vue-router'
import { store } from '@/store'
import AppHeader from '@/components/AppHeader.vue'
import BottomNav from '@/components/BottomNav.vue'
import LocationModal from '@/components/LocationModal.vue'
import ProductModal from '@/components/ProductModal.vue'
import MessageModal from '@/components/MessageModal.vue'

const route = useRoute()
const bare = computed(() => !!route.meta.bare) // full-screen pages (login/register) hide the app chrome
watchEffect(() => { document.body.style.paddingBottom = bare.value ? '0' : '' })
</script>
<template>
  <AppHeader v-if="!bare" />
  <div v-if="store.toast" class="fixed inset-x-4 z-50 mx-auto max-w-md rounded-xl px-4 py-3 text-sm text-white shadow-lg" :class="[bare ? 'top-4' : 'top-16', store.toast.bad ? 'bg-red-600' : 'bg-brand']">{{ store.toast.m }}</div>
  <RouterView v-if="bare" />
  <main v-else class="mx-auto max-w-6xl space-y-6 px-4 py-5"><RouterView /></main>
  <LocationModal v-if="store.locOpen" />
  <ProductModal v-if="store.modal?.t === 'product'" />
  <MessageModal v-if="store.modal && store.modal.t !== 'product'" />
  <BottomNav v-if="!bare" />
</template>