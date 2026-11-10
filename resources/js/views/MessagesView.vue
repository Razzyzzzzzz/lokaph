<script setup>
import { ref, watch, nextTick } from 'vue'
import { api } from '@/api'
import { store } from '@/store'

const props = defineProps({ id: String })
const list = ref([]), thread = ref(null), text = ref(''), busy = ref(false), box = ref(null)

async function load() {
  try {
    if (props.id) {
      thread.value = await api('/messages/' + props.id)
      store.loadUnread()
      await nextTick(); if (box.value) box.value.scrollTop = box.value.scrollHeight
    } else { thread.value = null; list.value = await api('/messages') }
  } catch (e) { store.flash(e.message, true) }
}
watch(() => props.id, load, { immediate: true })

async function send() {
  const m = text.value.trim(); if (!m || busy.value) return
  busy.value = true
  try {
    thread.value.messages.push(await api('/messages/' + props.id + '/reply', 'POST', { message: m }))
    text.value = ''; await nextTick(); if (box.value) box.value.scrollTop = box.value.scrollHeight
  } catch (e) { store.flash(e.message, true) } finally { busy.value = false }
}
const when = (d) => new Date(d.replace(' ', 'T')).toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' })
</script>
<template>
  <section class="space-y-4">
    <!-- Thread -->
    <template v-if="id && thread">
      <div class="flex items-center gap-3">
        <RouterLink to="/messages" class="rounded-lg border px-3 py-1.5 text-sm">&larr; Inbox</RouterLink>
        <div>
          <h1 class="font-extrabold">{{ thread.side === 'owner' ? thread.customer : thread.business }}</h1>
          <p v-if="thread.product" class="text-xs text-slate-500">About: {{ thread.product }}</p>
        </div>
      </div>
      <div ref="box" class="card max-h-[55vh] min-h-[40vh] space-y-2 overflow-y-auto p-4">
        <div v-for="m in thread.messages" :key="m.id" class="flex" :class="m.mine ? 'justify-end' : 'justify-start'">
          <div class="max-w-[80%] rounded-2xl px-3 py-2 text-sm" :class="m.mine ? 'bg-[#7c3aed] text-white' : 'bg-slate-100'">
            <p class="whitespace-pre-wrap break-words">{{ m.message }}</p>
            <span class="mt-1 block text-[10px] opacity-70">{{ when(m.created_at) }}</span>
          </div>
        </div>
        <p v-if="!thread.messages.length" class="text-center text-sm text-slate-500">No messages yet.</p>
      </div>
      <form class="flex gap-2" @submit.prevent="send">
        <input v-model="text" maxlength="1000" placeholder="Write a message..." class="flex-1">
        <button class="btn btn-primary" :disabled="busy || !text.trim()">Send</button>
      </form>
    </template>

    <!-- Inbox -->
    <template v-else-if="!id">
      <h1 class="text-xl font-extrabold">Messages</h1>
      <RouterLink v-for="c in list" :key="c.id" :to="'/messages/' + c.id" class="card block p-3 text-sm hover:border-[#7c3aed]" :class="+c.unread && 'border-[#7c3aed] bg-[#7c3aed]/5'">
        <div class="flex items-center justify-between gap-2">
          <b>{{ store.user?.role === 'owner' ? c.customer : c.business }}</b>
          <span v-if="+c.unread" class="rounded-full bg-[#7c3aed] px-2 text-xs text-white">{{ c.unread }} new</span>
        </div>
        <p v-if="c.product" class="text-xs text-slate-500">About: {{ c.product }}</p>
        <p class="line-clamp-1 text-slate-600">{{ c.preview }}</p>
      </RouterLink>
      <p v-if="!list.length" class="rounded-xl border p-6 text-center text-sm text-slate-500">No conversations yet.</p>
    </template>
  </section>
</template>