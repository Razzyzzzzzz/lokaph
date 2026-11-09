<script setup>
import { ref, reactive, watch } from 'vue'
import ProductCard from '@/components/ProductCard.vue'
import { api } from '@/api'
import { store } from '@/store'
import { qs } from '@/utils'
const f = reactive({ q: '', category: '', sort: '' }), list = ref([])
async function load() { try { list.value = await api('/products?' + qs(f, store.loc)) } catch (e) { store.flash(e.message, true) } }
watch(() => store.loc, load, { immediate: true })
</script>
<template>
  <section class="space-y-4">
    <div class="grid grid-cols-2 gap-2 md:grid-cols-4"><input v-model="f.q" class="col-span-2" placeholder="Search products" @keyup.enter="load">
      <select v-model="f.category" @change="load"><option value="">All categories</option><option v-for="c in store.cats.product" :key="c.id" :value="c.id">{{ c.name }}</option></select>
      <select v-model="f.sort" @change="load"><option value="">Newest</option><option value="nearest">Nearest</option><option value="price_asc">Price: low to high</option><option value="price_desc">Price: high to low</option><option value="trending">Trending</option></select></div>
    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4"><ProductCard v-for="p in list" :key="p.id" :p="p" /></div>
    <p v-if="!list.length" class="text-slate-500">No products found.</p>
  </section>
</template>
