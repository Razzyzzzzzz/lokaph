<script setup>
import { ref, reactive, onMounted } from 'vue'
import { api } from '@/api'
import { store } from '@/store'
const emit = defineEmits(['changed'])
const TYPES = { dti: 'DTI registration', sec: 'SEC registration', barangay_permit: 'Barangay clearance', mayor_permit: "Mayor's permit", other: 'Other' }
const status = ref('pending'), list = ref([]), notes = reactive({}), loading = ref(true)
async function load() { loading.value = true; try { list.value = await api('/admin/verifications?status=' + status.value) } catch (e) { store.flash(e.message, true) } finally { loading.value = false } }
async function review(v, action) {
  if (action === 'reject' && !(notes[v.id] || '').trim()) return store.flash('Write a reason before rejecting.', true)
  try { await api(`/admin/verifications/${v.id}/review`, 'POST', { action, note: notes[v.id] || '' }); store.flash(action === 'approve' ? 'Business verified.' : 'Request rejected.'); await load(); emit('changed') } catch (e) { store.flash(e.message, true) }
}
onMounted(load)
</script>
<template>
  <div class="space-y-3">
    <div class="flex gap-2 text-sm"><button v-for="s in ['pending', 'approved', 'rejected']" :key="s" class="rounded-full border px-4 py-1.5 capitalize" :class="status === s && 'bg-navy text-white'" @click="status = s; load()">{{ s }}</button></div>
    <p v-if="loading" class="text-sm text-slate-500">Loading…</p>
    <p v-else-if="!list.length" class="rounded-2xl border border-dashed p-8 text-center text-sm text-slate-500">No {{ status }} verification requests.</p>
    <div v-for="v in list" :key="v.id" class="card space-y-2 p-4 text-sm">
      <div class="flex flex-wrap items-start justify-between gap-2"><div><b class="text-base">{{ v.business }}</b><p class="text-slate-500">Owner: {{ v.owner }} · {{ v.created_at }}</p></div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs">{{ TYPES[v.doc_type] }}</span></div>
      <p v-if="v.note" class="rounded-xl bg-slate-50 p-2">“{{ v.note }}”</p>
      <a :href="`/api/admin/verifications/${v.id}/document`" target="_blank" rel="noopener" class="btn btn-outline">View document</a>
      <template v-if="v.status === 'pending'">
        <input v-model="notes[v.id]" maxlength="255" placeholder="Reason (required to reject)">
        <div class="flex gap-2"><button class="btn btn-primary" @click="review(v, 'approve')">Approve and verify</button><button class="btn btn-outline text-red-600" @click="review(v, 'reject')">Reject</button></div>
      </template>
      <p v-else class="text-slate-500">{{ v.status === 'approved' ? 'Approved' : 'Rejected' }} {{ v.reviewed_at }}<span v-if="v.review_note"> · {{ v.review_note }}</span></p>
    </div>
  </div>
</template>