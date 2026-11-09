<script setup>
import { ref } from 'vue'
import PhImg from '@/components/PhImg.vue'
import { api } from '@/api'
import { store } from '@/store'
import { peso } from '@/utils'
defineProps({ products: Array })
const emit = defineEmits(['changed'])
const form = ref(null)
const add = () => (form.value = { name: '', category_id: store.cats.product[0]?.id, price: '', stock: 0, description: '', available: true, image: '' })
const edit = (p) => (form.value = { ...p, available: !!+p.available, image: '' })
const guard = async (fn) => { try { await fn(); emit('changed') } catch (e) { store.flash(e.message, true) } }
const save = () => guard(async () => { await api('/owner/products', 'POST', form.value); form.value = null; store.flash('Product saved.') })
const del = (id) => confirm('Delete this product?') && guard(() => api(`/owner/products/${id}/delete`, 'POST'))
async function upload(e) { const fd = new FormData(); fd.append('file', e.target.files[0])
  try { form.value.image = (await api('/owner/upload', 'POST', fd)).url; store.flash('Image uploaded.') } catch (err) { store.flash(err.message, true) } }
</script>
<template>
  <div class="space-y-3">
    <button class="btn btn-primary" @click="add">Add product</button>
    <form v-if="form" class="card grid gap-3 p-4 text-sm md:grid-cols-2" @submit.prevent="save">
      <label>Name<input v-model="form.name" required></label><label>Category<select v-model="form.category_id" required><option v-for="c in store.cats.product" :key="c.id" :value="c.id">{{ c.name }}</option></select></label>
      <label>Price (₱)<input v-model="form.price" type="number" step="0.01" min="0" required></label><label>Stock<input v-model="form.stock" type="number" min="0"></label>
      <label class="md:col-span-2">Description<textarea v-model="form.description" rows="2" /></label><label>Photo<input type="file" accept="image/*" @change="upload"></label>
      <label class="mt-5 flex items-center gap-2"><input v-model="form.available" type="checkbox">Available for sale</label>
      <div class="flex gap-2 md:col-span-2"><button class="btn btn-primary">Save product</button><button type="button" class="btn btn-outline" @click="form = null">Cancel</button></div></form>
    <div v-for="p in products" :key="p.id" class="flex items-center gap-3 rounded-2xl border p-3 text-sm"><div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl"><PhImg :src="p.image" :name="p.name" /></div>
      <div class="flex-1"><b>{{ p.name }}</b><p>{{ peso(p.price) }} · stock {{ p.stock }} · {{ +p.available ? 'available' : 'hidden' }}</p></div>
      <button class="underline" @click="edit(p)">Edit</button><button class="text-red-600 underline" @click="del(p.id)">Delete</button></div>
  </div>
</template>
