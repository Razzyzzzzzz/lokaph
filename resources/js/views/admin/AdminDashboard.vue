<script setup>
import { ref, reactive, onMounted } from 'vue'
import DashboardHeader from '@/components/DashboardHeader.vue'
import VerificationQueue from './VerificationQueue.vue'
import ProductReview from './ProductReview.vue'
import AuditLog from './AuditLog.vue'
import AdminAnalytics from './AdminAnalytics.vue'
import { api } from '@/api'
import { store } from '@/store'
const tab = ref('overview'), stats = ref({}), biz = ref([]), users = ref([]), reports = ref([]), nc = reactive({ type: 'business', name: '' })
const TABS = [['overview', 'Overview'], ['analytics', 'Analytics'], ['businesses', 'Businesses'], ['verifications', 'Verifications'], ['products', 'Product approval'], ['users', 'Users'], ['categories', 'Categories'], ['reports', 'Reports'], ['audit', 'Audit logs']]
const badge = (k) => ({ verifications: stats.value.pending_verifications, products: stats.value.pending_products }[k] || 0)
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
    <div class="no-scrollbar -mx-4 flex gap-2 overflow-x-auto px-4 text-sm">
      <button v-for="[k, t] in TABS" :key="k" class="relative shrink-0 rounded-full border px-4 py-2" :class="tab === k && 'bg-navy text-white'" @click="tab = k">{{ t }}<span v-if="badge(k)" class="ml-1.5 rounded-full bg-red-500 px-1.5 text-[11px] text-white">{{ badge(k) }}</span></button>
    </div>

    <div v-if="tab === 'overview'" class="space-y-4">
      <div v-if="stats.pending_verifications || stats.pending_products || stats.pending" class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm"><b>Needs your attention</b>
        <ul class="mt-1 list-inside list-disc"><li v-if="stats.pending">{{ stats.pending }} business(es) awaiting approval (Businesses tab)</li><li v-if="stats.pending_verifications">{{ stats.pending_verifications }} verification request(s)</li><li v-if="stats.pending_products">{{ stats.pending_products }} product(s) awaiting approval</li></ul></div>
      <div class="grid grid-cols-2 gap-3 md:grid-cols-4"><div v-for="(v, k) in stats" :key="k" class="stat"><b class="block text-xl">{{ v }}</b><span class="text-xs capitalize text-slate-500">{{ k.replace(/_/g, ' ') }}</span></div></div>
    </div>

    <div v-if="tab === 'businesses'" class="space-y-2">
      <div v-for="b in biz" :key="b.id" class="rounded-2xl border p-3 text-sm"><b>{{ b.name }}</b> <span class="text-slate-500">{{ b.category }} · {{ b.city }} · by {{ b.owner }}</span>
        <span class="ml-1 rounded-full px-2 text-xs" :class="b.status === 'approved' ? 'bg-green-100' : b.status === 'pending' ? 'bg-amber-100' : 'bg-red-100'">{{ b.status }}</span> <span v-if="+b.verified" class="rounded-full bg-blue-100 px-2 text-xs">verified</span> <span v-if="+b.is_sample" class="rounded-full bg-amber-300 px-2 text-xs">sample</span>
        <div class="mt-2 flex flex-wrap gap-2">
          <button v-if="b.status !== 'approved'" class="btn btn-outline !py-1" @click="act(b.id, 'approve')">Approve</button><button v-if="b.status !== 'rejected'" class="btn btn-outline !py-1" @click="act(b.id, 'reject')">Reject</button>
          <button class="btn btn-outline !py-1" @click="act(b.id, +b.featured ? 'unfeature' : 'feature')">{{ +b.featured ? 'Unfeature' : 'Feature' }}</button>
          <button v-if="+b.verified" class="btn btn-outline !py-1" @click="act(b.id, 'unverify')">Remove verified badge</button>
          <button class="btn btn-outline !py-1 text-red-600" @click="remove(b.id)">Remove</button></div></div>
    </div>

    <VerificationQueue v-if="tab === 'verifications'" @changed="load" />
    <ProductReview v-if="tab === 'products'" @changed="load" />

    <div v-if="tab === 'users'" class="space-y-2"><div v-for="u in users" :key="u.id" class="flex justify-between rounded-xl border p-2 text-sm"><span>{{ u.name }} · {{ u.email }} · {{ u.role }}</span><button class="underline" @click="toggle(u.id)">{{ +u.active ? 'Disable' : 'Enable' }}</button></div></div>

    <form v-if="tab === 'categories'" class="flex gap-2" @submit.prevent="addCat"><select v-model="nc.type" class="!w-40"><option value="business">Business</option><option value="product">Product</option></select><input v-model="nc.name" placeholder="New category"><button class="btn btn-primary">Add</button></form>

    <div v-if="tab === 'reports'" class="space-y-2"><div v-for="r in reports" :key="r.id" class="rounded-xl border p-2 text-sm">{{ r.reporter }}: {{ r.reason }} <span class="text-slate-400">(business {{ r.business_id || '-' }}, product {{ r.product_id || '-' }})</span></div><p v-if="!reports.length" class="text-sm text-slate-500">No reports.</p></div>

    <AdminAnalytics v-if="tab === 'analytics'" />
    <AuditLog v-if="tab === 'audit'" />
  </section>
</template>