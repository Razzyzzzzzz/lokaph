<script setup>
import { reactive, ref, computed } from 'vue'
import { api } from '@/api'
import { store } from '@/store'
const props = defineProps({ business: Object, request: Object })
const emit = defineEmits(['changed'])
const f = reactive({ doc_type: '', note: '' }), file = ref(null), busy = ref(false)
const TYPES = { dti: 'DTI business name registration', sec: 'SEC registration', barangay_permit: 'Barangay business clearance', mayor_permit: "Mayor's / business permit", other: 'Other supporting document' }
const verified = computed(() => !!+props.business.verified)
const pending = computed(() => props.request?.status === 'pending')
const approvedBiz = computed(() => props.business.status === 'approved')
async function submit() {
  if (!f.doc_type) return store.flash('Choose a document type.', true)
  if (!file.value) return store.flash('Attach your document.', true)
  const fd = new FormData(); fd.append('doc_type', f.doc_type); fd.append('note', f.note); fd.append('file', file.value)
  busy.value = true
  try { const r = await api('/owner/verification', 'POST', fd); store.flash(r.message); emit('changed') } catch (e) { store.flash(e.message, true) } finally { busy.value = false }
}
</script>
<template>
  <section class="max-w-2xl space-y-4">
    <div v-if="verified" class="rounded-2xl border border-green-200 bg-green-50 p-5"><b class="text-green-800">✓ Your business is verified</b><p class="text-sm text-green-700">Customers see a Verified badge on your page and listings.</p></div>
    <template v-else>
      <div><h2 class="text-lg font-bold">Get verified</h2><p class="text-sm text-slate-500">Upload a government-issued business document. Verified businesses earn customer trust. Your document is private and only visible to LokalPH admins.</p></div>
      <p v-if="!approvedBiz" class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm">Verification opens after an admin approves your business listing.</p>
      <div v-else-if="pending" class="rounded-2xl border border-amber-200 bg-amber-50 p-5"><b class="text-amber-800">Under review</b><p class="text-sm text-amber-700">You submitted a {{ TYPES[request.doc_type] }} on {{ request.created_at }}. We'll notify you once it's reviewed.</p></div>
      <template v-else>
        <div v-if="request?.status === 'rejected'" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><b>Your last request was rejected.</b><p>Reason: {{ request.review_note }}</p><p>Fix the issue and submit again below.</p></div>
        <form class="card space-y-3 p-5 text-sm" @submit.prevent="submit">
          <label class="block font-medium">Document type<select v-model="f.doc_type"><option value="" disabled>Select…</option><option v-for="(t, k) in TYPES" :key="k" :value="k">{{ t }}</option></select></label>
          <label class="block font-medium">File (PDF, JPG, PNG or WebP, max 5 MB)<input type="file" accept=".pdf,image/jpeg,image/png,image/webp" @change="file = $event.target.files[0]"></label>
          <label class="block font-medium">Note for the reviewer (optional)<textarea v-model="f.note" rows="2" maxlength="500" /></label>
          <button class="btn btn-primary" :disabled="busy">{{ busy ? 'Uploading…' : 'Submit for verification' }}</button>
        </form>
      </template>
    </template>
  </section>
</template>