<script setup>
import ModalSheet from './ModalSheet.vue'
import PhImg from './PhImg.vue'
import { store } from '@/store'
import { km, peso } from '@/utils'
const p = store.modal.p
</script>
<template>
  <ModalSheet @close="store.modal = null">
    <div class="h-48 overflow-hidden rounded-2xl"><PhImg :src="p.image" :name="p.name" /></div>
    <h3 class="text-lg font-bold">{{ p.name }}</h3><p class="font-bold text-brand">{{ peso(p.price) }}</p><p class="text-sm">{{ p.description }}</p>
    <p class="text-sm text-slate-500">{{ p.category }} · sold by <RouterLink :to="'/business/' + p.business_id" class="underline" @click="store.modal = null">{{ p.business }}</RouterLink> · {{ km(p.distance) }}</p>
    <button class="btn btn-primary w-full" @click="store.openContact(p)">Contact seller</button>
  </ModalSheet>
</template>
