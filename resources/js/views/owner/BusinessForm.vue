<script setup>
import { reactive } from 'vue'
import PinMap from '@/components/PinMap.vue'
import { api } from '@/api'
import { store } from '@/store'
import { DAYS, isInPH } from '@/utils'
const props = defineProps({ business: Object })
const emit = defineEmits(['saved'])
const b = props.business, h0 = b?.hours?.[0]
const f = reactive({ name: '', category_id: '', description: '', phone: '', facebook: '', address: '', city: '', province: '', lat: store.loc?.lat ?? 14.5995, lng: store.loc?.lng ?? 120.9842, ...(b || {}), ...(b?.location || {}), logo: '', cover: '' })
const hours = reactive({ opens: h0 ? h0.opens.slice(0, 5) : '08:00', closes: h0 ? h0.closes.slice(0, 5) : '20:00', closed: b ? b.hours.filter((x) => +x.is_closed).map((x) => +x.day) : [] })
const pick = (p) => { f.lat = p.lat; f.lng = p.lng }
const pinMe = () => navigator.geolocation?.getCurrentPosition((p) => { if (!isInPH(p.coords.latitude, p.coords.longitude)) return store.flash('Your location is outside the Philippines.', true); f.lat = p.coords.latitude.toFixed(7); f.lng = p.coords.longitude.toFixed(7) }, () => store.flash('Location permission denied. Tap the map instead.', true))
async function upload(e, k) { const file = e.target.files[0]; if (!file) return; const fd = new FormData(); fd.append('file', file)
  try { f[k] = (await api('/owner/upload', 'POST', fd)).url; store.flash('Image uploaded.') } catch (err) { store.flash(err.message, true) } }
async function save() { try { const r = await api('/owner/business', 'POST', { ...f, hours }); store.flash(r.message); emit('saved') } catch (e) { store.flash(e.message, true) } }
</script>
<template>
  <form class="grid gap-3 text-sm md:grid-cols-2" @submit.prevent="save">
    <p v-if="!business" class="md:col-span-2">Register your business. An admin approves it before customers can see it.</p>
    <label>Business name<input v-model="f.name" required></label>
    <label>Category<select v-model="f.category_id" required><option v-for="c in store.cats.business" :key="c.id" :value="c.id">{{ c.name }}</option></select></label>
    <label class="md:col-span-2">Description<textarea v-model="f.description" rows="3" /></label>
    <label>Phone<input v-model="f.phone"></label><label>Facebook link<input v-model="f.facebook"></label>
    <label>Logo<input type="file" accept="image/*" @change="upload($event, 'logo')"></label><label>Cover photo<input type="file" accept="image/*" @change="upload($event, 'cover')"></label>
    <label class="md:col-span-2">Street address<input v-model="f.address" required></label><label>City<input v-model="f.city" required></label><label>Province<input v-model="f.province" required></label>
    <label>Opens<input v-model="hours.opens" type="time"></label><label>Closes<input v-model="hours.closes" type="time"></label>
    <div class="flex flex-wrap gap-3 md:col-span-2"><span>Closed on:</span><label v-for="(d, i) in DAYS" :key="d" class="flex items-center gap-1"><input v-model="hours.closed" type="checkbox" :value="i">{{ d }}</label></div>
    <div class="md:col-span-2"><p class="mb-1">Tap the map to pin your exact location <button type="button" class="text-brand underline" @click="pinMe">Use my location</button></p><PinMap :lat="f.lat" :lng="f.lng" editable @pick="pick" /></div>
    <button class="btn btn-primary md:col-span-2">{{ business ? 'Save changes' : 'Submit for approval' }}</button>
  </form>
</template>