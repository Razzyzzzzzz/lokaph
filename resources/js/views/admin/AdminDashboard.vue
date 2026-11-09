<script setup>
import { ref, reactive, onMounted } from 'vue'
import DashboardHeader from '@/components/DashboardHeader.vue'
import { api } from '@/api'
import { store } from '@/store'
const stats = ref({}), biz = ref([]), users = ref([]), reports = ref([]), nc = reactive({ type: 'business', name: '' })
async function load() { try { [stats.value, biz.value, users.value, reports.value] = await Promise.all([api('/admin/stats'), api('/admin/businesses'), api('/admin/users'), api('/admin/reports')]) } catch (e) { store.flash(e.message, true) } }
const run = async (fn, msg) => { try { await fn(); if (msg) store.flash(msg); await load() } catch (e) { store.flash(e.message, true) } }
const act = (id, action) => run(() => api('/admin/businesses/' + id, 'POST', { action }), 'Done.')
const remove = (id) => confirm('Remove this listing?') && act(id, 'delete')
const toggle = (id) => run(() => api(`/admin/users/${id}/toggle`, 'POST'))
const addCat = () => run(async () => { await api('/admin/categories', 'POST', nc); nc.name = ''; store.cats = await api('/categories') }, 'Category added.')
onMounted(load)
</script>
<template>
  <section class="space-y-5">
    <DashboardHeader title="Admin dashboard" />
    <div class="grid grid-cols-2 gap-3 md:grid-cols-5"><div v-for="(v, k) in stats" :key="k" class="stat"><b class="block text-xl">{{ v }}</b><span class="text-xs capitalize text-slate-500">{{ k.replace('_', ' ') }}</span></div></div>
    <h2 class="font-bold">Businesses</h2>
    <div v-for="b in biz" :key="b.id" class="rounded-2xl border p-3 text-sm"><b>{{ b.name }}</b> <span class="text-slate-500">{{ b.category }} · {{ b.city }} · by {{ b.owner }}</span>
      <span class="ml-1 rounded-full px-2 text-xs" :class="b.status === 'approved' ? 'bg-green-100' : b.status === 'pending' ? 'bg-amber-100' : 'bg-red-100'">{{ b.status }}</span> <span v-if="+b.is_sample" class="rounded-full bg-amber-300 px-2 text-xs">sample</span>
      <div class="mt-2 flex flex-wrap gap-2">
        <button v-if="b.status !== 'approved'" class="btn btn-outline !py-1" @click="act(b.id, 'approve')">Approve</button><button v-if="b.status !== 'rejected'" class="btn btn-outline !py-1" @click="act(b.id, 'reject')">Reject</button>
        <button class="btn btn-outline !py-1" @click="act(b.id, +b.verified ? 'unverify' : 'verify')">{{ +b.verified ? 'Unverify' : 'Verify' }}</button>
        <button class="btn btn-outline !py-1" @click="act(b.id, +b.featured ? 'unfeature' : 'feature')">{{ +b.featured ? 'Unfeature' : 'Feature' }}</button>
        <button class="btn btn-outline !py-1 text-red-600" @click="remove(b.id)">Remove</button></div></div>
    <h2 class="font-bold">Users</h2>
    <div v-for="u in users" :key="u.id" class="flex justify-between rounded-xl border p-2 text-sm"><span>{{ u.name }} · {{ u.email }} · {{ u.role }}</span><button class="underline" @click="toggle(u.id)">{{ +u.active ? 'Disable' : 'Enable' }}</button></div>
    <h2 class="font-bold">Categories</h2>
    <form class="flex gap-2" @submit.prevent="addCat"><select v-model="nc.type" class="!w-40"><option value="business">Business</option><option value="product">Product</option></select><input v-model="nc.name" placeholder="New category"><button class="btn btn-primary">Add</button></form>
    <h2 class="font-bold">Reports</h2><div v-for="r in reports" :key="r.id" class="rounded-xl border p-2 text-sm">{{ r.reporter }}: {{ r.reason }} <span class="text-slate-400">(business {{ r.business_id || '-' }}, product {{ r.product_id || '-' }})</span></div>
  </section>
</template>