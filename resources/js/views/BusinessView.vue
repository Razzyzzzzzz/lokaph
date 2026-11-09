<script setup>
import { ref, reactive, watch } from 'vue'
import PhImg from '@/components/PhImg.vue'
import PinMap from '@/components/PinMap.vue'
import ProductCard from '@/components/ProductCard.vue'
import { api } from '@/api'
import { store } from '@/store'
import { DAYS, km, dirUrl, qs } from '@/utils'
const props = defineProps({ id: String })
const b = ref(null), rev = reactive({ rating: 5, comment: '' })
async function load() { try { b.value = await api(`/businesses/${props.id}?` + qs({}, store.loc)) } catch (e) { store.flash(e.message, true) } }
async function review() { try { await api('/reviews', 'POST', { business_id: b.value.id, ...rev }); rev.comment = ''; store.flash('Review posted.'); load() } catch (e) { store.flash(e.message, true) } }
watch(() => [props.id, store.loc], load, { immediate: true })
</script>
<template>
  <section v-if="b" class="space-y-5">
    <div class="overflow-hidden rounded-3xl border"><div class="h-44 md:h-64"><PhImg :src="b.cover" :name="b.name" /></div>
      <div class="flex items-start gap-4 p-4 md:p-6"><div class="-mt-12 h-16 w-16 shrink-0 overflow-hidden rounded-2xl border-4 border-white shadow"><PhImg :src="b.logo" :name="b.name" /></div>
        <div class="flex-1"><h1 class="text-xl font-extrabold md:text-2xl">{{ b.name }} <span v-if="b.verified" class="text-sm text-brand">Verified</span> <span v-if="b.is_sample" class="rounded-full bg-amber-300 px-2 text-xs">Sample data</span></h1>
          <p class="text-sm text-slate-500">{{ b.category }} · {{ b.address }}, {{ b.city }}, {{ b.province }}</p>
          <p class="mt-1 text-sm"><b class="text-brand">{{ km(b.distance) }}</b> · Rating {{ b.rating || 'New' }} ({{ b.reviews }}) · <b :class="b.is_open ? 'text-brand' : 'text-slate-500'">{{ b.is_open ? 'Open now' : 'Closed' }}</b></p></div>
        <button class="text-2xl" @click="store.toggleFav('business', b.id)">{{ store.favs.businesses.includes(b.id) ? '❤️' : '🤍' }}</button></div></div>
    <p>{{ b.description }}</p>
    <div class="flex flex-wrap gap-2"><a :href="dirUrl(b)" target="_blank" rel="noopener" class="btn btn-primary">Get directions</a><button class="btn btn-outline" @click="store.openContact({ business_id: b.id, business: b.name })">Message seller</button>
      <a v-if="b.phone" :href="'tel:' + b.phone" class="btn btn-outline">Call {{ b.phone }}</a><a v-if="b.facebook" :href="b.facebook" target="_blank" rel="noopener" class="btn btn-outline">Facebook</a>
      <button class="btn text-slate-500" @click="store.openReport({ business_id: b.id })">Report</button></div>
    <div class="grid gap-4 md:grid-cols-2"><PinMap :lat="b.lat" :lng="b.lng" />
      <div class="rounded-2xl border p-4 text-sm"><h3 class="mb-2 font-bold">Opening hours</h3><div v-for="h in b.hours" :key="h.day" class="flex justify-between py-0.5"><span>{{ DAYS[h.day] }}</span><span>{{ +h.is_closed ? 'Closed' : h.opens.slice(0, 5) + ' – ' + h.closes.slice(0, 5) }}</span></div></div></div>
    <h2 class="text-lg font-bold">Products and services</h2>
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4"><ProductCard v-for="p in b.items" :key="p.id" :p="p" /></div>
    <h2 class="text-lg font-bold">Reviews</h2>
    <form v-if="store.user" class="card space-y-2 p-4" @submit.prevent="review"><div class="flex gap-1 text-2xl"><button v-for="n in 5" :key="n" type="button" @click="rev.rating = n">{{ n <= rev.rating ? '★' : '☆' }}</button></div>
      <textarea v-model="rev.comment" rows="2" placeholder="Share your experience" /><button class="btn btn-primary">Post review</button></form>
    <p v-else class="text-sm"><RouterLink to="/login" class="text-brand underline">Log in</RouterLink> to leave a review.</p>
    <div v-for="(r, i) in b.review_list" :key="i" class="rounded-2xl border p-3 text-sm"><b>{{ r.name }}</b> <span class="text-amber-500">{{ '★'.repeat(r.rating) }}</span><p>{{ r.comment }}</p></div>
  </section>
</template>
