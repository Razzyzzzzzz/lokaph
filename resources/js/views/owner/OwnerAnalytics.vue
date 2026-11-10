<script setup>
import { ref, computed, onMounted } from 'vue'
import TrendChart from '@/components/TrendChart.vue'
import KpiCard from '@/components/KpiCard.vue'
import { api } from '@/api'
import { store } from '@/store'
const days = ref(30), data = ref(null), loading = ref(true)
async function load() { loading.value = true; try { data.value = await api('/owner/analytics?days=' + days.value) } catch (e) { store.flash(e.message, true) } finally { loading.value = false } }
const pick = (d) => { days.value = d; load() }
const t = computed(() => data.value.totals), p = computed(() => data.value.previous)
const conversion = computed(() => (t.value.profile_views ? Math.round((t.value.inquiries / t.value.profile_views) * 1000) / 10 : 0))
const labels = computed(() => data.value.series.map((r) => r.day))
const lines = computed(() => [['Profile views', '#16a34a', 'profile_views'], ['Product views', '#0b1f3a', 'product_views'], ['Direction clicks', '#f59e0b', 'direction_clicks']].map(([name, color, k]) => ({ name, color, values: data.value.series.map((r) => r[k]) })))
const empty = computed(() => !t.value.profile_views && !t.value.product_views && !t.value.direction_clicks && !t.value.inquiries)
const maxViews = computed(() => Math.max(1, ...data.value.top_products.map((x) => x.views)))
onMounted(load)
</script>
<template>
  <section class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
      <div class="flex gap-2 text-sm"><button v-for="d in [7, 30, 90]" :key="d" class="rounded-full border px-4 py-1.5" :class="days === d && 'bg-navy text-white'" @click="pick(d)">Last {{ d }} days</button></div>
      <a :href="`/api/owner/report?days=${days}`" class="btn btn-outline !py-1.5">Download CSV</a>
    </div>
    <p v-if="loading && !data" class="text-sm text-slate-500">Loading…</p>
    <template v-if="data">
      <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <KpiCard label="Profile views" :value="t.profile_views" :prev="p.profile_views" /><KpiCard label="Product views" :value="t.product_views" :prev="p.product_views" />
        <KpiCard label="Direction clicks" :value="t.direction_clicks" :prev="p.direction_clicks" /><KpiCard label="Calls" :value="t.call_clicks" :prev="p.call_clicks" />
        <KpiCard label="Messages started" :value="t.inquiries" :prev="p.inquiries" /><KpiCard label="New favorites" :value="t.favorites" :prev="p.favorites" />
        <KpiCard label="Visit → message rate" :value="conversion" suffix="%" />
      </div>
      <p v-if="empty" class="rounded-2xl border border-dashed p-4 text-center text-sm text-slate-500">No customer activity in this period yet. Visits by you are not counted.</p>
      <div class="card p-4"><h3 class="mb-2 font-bold">Activity over time</h3><TrendChart :labels="labels" :series="lines" /></div>
      <div class="card p-4"><h3 class="mb-3 font-bold">Most viewed products <span class="text-xs font-normal text-slate-400">(all time)</span></h3>
        <p v-if="!data.top_products.length" class="text-sm text-slate-500">No approved products yet.</p>
        <div v-for="x in data.top_products" :key="x.name" class="mb-2 text-sm"><div class="flex justify-between"><span>{{ x.name }}</span><b>{{ x.views }}</b></div><div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-brand" :style="{ width: (x.views / maxViews) * 100 + '%' }"></div></div></div></div>
    </template>
  </section>
</template>