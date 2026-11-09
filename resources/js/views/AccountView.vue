<script setup>
import { ref, computed, onMounted } from 'vue'
import BusinessCard from '@/components/BusinessCard.vue'
import ProductCard from '@/components/ProductCard.vue'
import DashboardHeader from '@/components/DashboardHeader.vue'
import { api } from '@/api'
import { store } from '@/store'
import { qs } from '@/utils'
const allB = ref([]), allP = ref([]), notes = ref([])
const savedB = computed(() => allB.value.filter((b) => store.favs.businesses.includes(b.id)))
const savedP = computed(() => allP.value.filter((p) => store.favs.products.includes(p.id)))
onMounted(async () => {
  try { [allB.value, allP.value, notes.value] = await Promise.all([api('/businesses?' + qs({}, store.loc)), api('/products?' + qs({}, store.loc)), api('/notifications')]); api('/notifications/read', 'POST') }
  catch (e) { store.flash(e.message, true) }
})
</script>
<template>
  <section class="space-y-5">
    <DashboardHeader title="My account" :subtitle="'Hi, ' + store.user.name" />
    <h2 class="font-bold">Saved businesses</h2><div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"><BusinessCard v-for="b in savedB" :key="b.id" :b="b" /></div><p v-if="!savedB.length" class="text-sm text-slate-500">Tap the heart on a business to save it.</p>
    <h2 class="font-bold">Saved products</h2><div class="grid grid-cols-2 gap-4 lg:grid-cols-4"><ProductCard v-for="p in savedP" :key="p.id" :p="p" /></div>
    <h2 class="font-bold">Notifications</h2><div v-for="(n, i) in notes" :key="i" class="rounded-xl border p-3 text-sm" :class="!+n.is_read && 'border-brand'">{{ n.message }} <span class="text-xs text-slate-400">{{ n.created_at }}</span></div><p v-if="!notes.length" class="text-sm text-slate-500">No notifications yet.</p>
  </section>
</template>