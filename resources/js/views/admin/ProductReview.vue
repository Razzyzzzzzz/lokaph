<script setup>
import { ref, reactive, onMounted } from 'vue'
import PhImg from '@/components/PhImg.vue'
import { api } from '@/api'
import { store } from '@/store'
import { peso } from '@/utils'
const emit = defineEmits(['changed'])
const status = ref('pending'), list = ref([]), notes = reactive({}), loading = ref(true)
async function load() { loading.value = true; try { list.value = await api('/admin/products?status=' + status.value) } catch (e) { store.flash(e.message, true) } finally { loading.value = false } }
async function review(p, action) {
  if (action === 'reject' && !(notes[p.id] || '').trim()) return store.flash('Write a reason before rejecting.', true)
  try { await api(`/admin/products/${p.id}/review`, 'POST', { action, note: notes[p.id] || '' }); store.flash(action === 'approve' ? 'Product approved.' : 'Product rejected.'); await load(); emit('changed') } catch (e) { store.flash(e.message, true) }
}
onMounted(load)
</script>
<template>
  <div class="space-y-3">
    <div class="flex gap-2 text-sm"><button v-for="s in ['pending', 'approved', 'rejected']" :key="s" class="rounded-full border px-4 py-1.5 capitalize" :class="status === s && 'bg-navy text-white'" @click="status = s; load()">{{ s }}</button></div>
    <p v-if="loading" class="text-sm text-slate-500">Loading…</p>
    <p v-else-if="!list.length" class="rounded-2xl border border-dashed p-8 text-center text-sm text-slate-500">No {{ status }} products.</p>
    <div v-for="p in list" :key="p.id" class="card flex gap-3 p-3 text-sm">
      <div class="h-24 w-24 shrink-0 overflow-hidden rounded-xl"><PhImg :src="p.image" :name="p.name" /></div>
      <div class="min-w-0 flex-1 space-y-1"><b class="text-base">{{ p.name }}</b> <span class="font-bold text-brand">{{ peso(p.price) }}</span>
        <p class="text-slate-500">{{ p.business }} · {{ p.created_at }}</p><p class="line-clamp-2">{{ p.description }}</p>
        <template v-if="status === 'pending'">
          <input v-model="notes[p.id]" maxlength="255" placeholder="Reason (required to reject)">
          <div class="flex gap-2"><button class="btn btn-primary !py-1.5" @click="review(p, 'approve')">Approve</button><button class="btn btn-outline !py-1.5 text-red-600" @click="review(p, 'reject')">Reject</button></div></template>
        <p v-else-if="p.review_note" class="text-red-600">Reason: {{ p.review_note }}</p>
      </div>
    </div>
  </div>
</template>