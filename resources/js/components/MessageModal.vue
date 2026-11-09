<script setup>
import { ref } from 'vue'
import ModalSheet from './ModalSheet.vue'
import { api } from '@/api'
import { store } from '@/store'
const msg = ref('')
const isReport = store.modal.t === 'report'
async function send() {
  const m = store.modal
  try {
    isReport ? await api('/reports', 'POST', { business_id: m.business_id, product_id: m.product_id, reason: msg.value })
             : await api('/inquiries', 'POST', { business_id: m.business_id, product_id: m.product_id, message: msg.value })
    store.modal = null; store.flash(isReport ? 'Report sent. Thank you.' : 'Message sent to the seller.')
  } catch (e) { store.flash(e.message, true) }
}
</script>
<template>
  <ModalSheet @close="store.modal = null">
    <h3 class="text-lg font-bold">{{ isReport ? 'Report this listing' : 'Message ' + store.modal.name }}</h3>
    <textarea v-model="msg" rows="4" :placeholder="isReport ? 'What is wrong?' : 'Hi! Is this still available?'" />
    <div class="flex gap-2"><button class="btn btn-primary flex-1" @click="send">{{ isReport ? 'Send report' : 'Send message' }}</button><button class="btn btn-outline" @click="store.modal = null">Cancel</button></div>
  </ModalSheet>
</template>
