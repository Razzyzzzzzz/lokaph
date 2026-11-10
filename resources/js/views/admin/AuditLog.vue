<script setup>
import { ref, watch, onMounted } from 'vue'
import { apiPage } from '@/api'
import { store } from '@/store'
const PER = 25, q = ref(''), rows = ref([]), total = ref(0), loading = ref(true)
let timer
async function load(append = false) {
  loading.value = true
  try { const r = await apiPage(`/admin/audit?limit=${PER}&offset=${append ? rows.value.length : 0}&q=` + encodeURIComponent(q.value)); rows.value = append ? [...rows.value, ...r.items] : r.items; total.value = r.total }
  catch (e) { store.flash(e.message, true) } finally { loading.value = false }
}
watch(q, () => { clearTimeout(timer); timer = setTimeout(load, 300) })
onMounted(() => load())
const label = (a) => a.replace(/_/g, ' ')
const tone = (a) => /failed|rejected|removed|delete|disabled|toggled/.test(a) ? 'bg-red-50 text-red-700' : /approved|verify|verified|created|submitted|register/.test(a) ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600'
</script>
<template>
  <div class="space-y-3">
    <input v-model="q" type="search" placeholder="Search action, person or details" aria-label="Search audit logs">
    <p class="text-sm text-slate-500">{{ total.toLocaleString() }} events</p>
    <div class="overflow-x-auto rounded-2xl border">
      <table class="w-full min-w-[640px] text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="p-3">When</th><th class="p-3">Who</th><th class="p-3">Action</th><th class="p-3">Details</th><th class="p-3">IP</th></tr></thead>
        <tbody><tr v-for="r in rows" :key="r.id" class="border-t align-top">
          <td class="whitespace-nowrap p-3 text-slate-500">{{ r.created_at }}</td><td class="p-3">{{ r.user || 'Guest' }}</td>
          <td class="p-3"><span class="rounded-full px-2 py-0.5 text-xs capitalize" :class="tone(r.action)">{{ label(r.action) }}</span></td>
          <td class="p-3">{{ r.details }}<span v-if="r.entity_type" class="text-slate-400"> · {{ r.entity_type }}{{ r.entity_id ? ' #' + r.entity_id : '' }}</span></td><td class="p-3 text-slate-400">{{ r.ip }}</td></tr>
          <tr v-if="!rows.length && !loading"><td colspan="5" class="p-6 text-center text-slate-500">No events found.</td></tr></tbody>
      </table>
    </div>
    <button v-if="rows.length < total" class="btn btn-outline" :disabled="loading" @click="load(true)">{{ loading ? 'Loading…' : 'Load more' }}</button>
  </div>
</template>