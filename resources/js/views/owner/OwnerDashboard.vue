<script setup>
import { ref, onMounted } from 'vue'
import BusinessForm from './BusinessForm.vue'
import ProductManager from './ProductManager.vue'
import DashboardHeader from '@/components/DashboardHeader.vue'
import { api } from '@/api'
import { store } from '@/store'
const od = ref(null), tab = ref('overview'), formKey = ref(0)
async function load() { try { od.value = await api('/owner/dashboard'); formKey.value++ } catch (e) { store.flash(e.message, true) } }
onMounted(load)
</script>
<template>
  <section v-if="od" class="space-y-4">
    <DashboardHeader title="Business dashboard" :subtitle="od.business?.name || store.user?.name" />
    <BusinessForm v-if="!od.business" :key="formKey" @saved="load" />
    <template v-else>
      <div class="flex gap-2 overflow-x-auto text-sm"><button v-for="t in ['overview', 'business', 'products', 'inquiries', 'reviews']" :key="t" class="shrink-0 rounded-full border px-4 py-2 capitalize" :class="tab === t && 'bg-navy text-white'" @click="tab = t">{{ t }}</button></div>
      <div v-if="tab === 'overview'" class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="stat"><b class="block text-xl capitalize">{{ od.business.status }}</b><span class="text-xs text-slate-500">Listing status</span></div>
        <div class="stat"><b class="block text-xl">{{ +od.business.verified ? 'Verified' : 'Not verified' }}</b><span class="text-xs text-slate-500">Verification</span></div>
        <div class="stat"><b class="block text-xl">{{ od.stats.visits }}</b><span class="text-xs text-slate-500">Profile visits</span></div>
        <div class="stat"><b class="block text-xl">{{ od.stats.product_views }}</b><span class="text-xs text-slate-500">Product views</span></div></div>
      <BusinessForm v-if="tab === 'business'" :key="formKey" :business="od.business" @saved="load" />
      <ProductManager v-if="tab === 'products'" :products="od.products" @changed="load" />
      <div v-if="tab === 'inquiries'"><div v-for="(i, k) in od.inquiries" :key="k" class="mb-2 rounded-2xl border p-3 text-sm"><b>{{ i.name }}</b> ({{ i.email }})<p>{{ i.message }}</p></div><p v-if="!od.inquiries.length" class="text-sm text-slate-500">No inquiries yet.</p></div>
      <div v-if="tab === 'reviews'"><div v-for="(r, k) in od.reviews" :key="k" class="mb-2 rounded-2xl border p-3 text-sm"><b>{{ r.name }}</b> {{ '★'.repeat(r.rating) }}<p>{{ r.comment }}</p></div><p v-if="!od.reviews.length" class="text-sm text-slate-500">No reviews yet.</p></div>
    </template>
  </section>
</template>