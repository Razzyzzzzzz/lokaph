<script setup>
import { ref, computed, onMounted } from 'vue'
import TrendChart from '@/components/TrendChart.vue'
import KpiCard from '@/components/KpiCard.vue'
import { api } from '@/api'
import { store } from '@/store'
const days = ref(30), data = ref(null)
async function load() { try { data.value = await api('/admin/analytics?days=' + days.value) } catch (e) { store.flash(e.message, true) } }
const pick = (d) => { days.value = d; load() }
const t = computed(() => data.value.totals), p = computed(() => data.value.previous)
const labels = computed(() => data.value.series.map((r) => r.day))
const mk = (cfg) => cfg.map(([name, color, k]) => ({ name, color, values: data.value.series.map((r) => r[k]) }))
const traffic = computed(() => mk([['Profile views', '#16a34a', 'profile_views'], ['Messages started', '#f59e0b', 'inquiries']]))
const growth = computed(() => mk([['New users', '#0b1f3a', 'new_users'], ['New businesses', '#16a34a', 'new_businesses']]))
const maxCat = computed(() => Math.max(1, ...data.value.categories.map((c) => c.n)))
onMounted(load)
</script>
<template>
  <section class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
      <div class="flex gap-2 text-sm"><button v-for="d in [7, 30, 90]" :key="d" class="rounded-full border px-4 py-1.5" :class="days === d && 'bg-navy text-white'" @click="pick(d)">Last {{ d }} days</button></div>
      <a :href="`/api/admin/report?days=${days}`" class="btn btn-outline !py-1.5">Download CSV</a>
    </div>
    <template v-if="data">
      <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
        <KpiCard label="New users" :value="t.new_users" :prev="p.new_users" /><KpiCard label="New businesses" :value="t.new_businesses" :prev="p.new_businesses" />
        <KpiCard label="Messages started" :value="t.inquiries" :prev="p.inquiries" /><KpiCard label="Profile views" :value="t.profile_views" :prev="p.profile_views" />
        <KpiCard label="Product views" :value="t.product_views" :prev="p.product_views" /><KpiCard label="Direction clicks" :value="t.direction_clicks" :prev="p.direction_clicks" />
      </div>
      <div class="card p-4"><h3 class="mb-2 font-bold">Traffic</h3><TrendChart :labels="labels" :series="traffic" /></div>
      <div class="card p-4"><h3 class="mb-2 font-bold">Growth</h3><TrendChart :labels="labels" :series="growth" /></div>
      <div class="grid gap-4 md:grid-cols-2">
        <div class="card p-4"><h3 class="mb-3 font-bold">Top businesses by views</h3><p v-if="!data.top_businesses.length" class="text-sm text-slate-500">No views in this period.</p>
          <div v-for="b in data.top_businesses" :key="b.id" class="flex justify-between border-b py-2 text-sm last:border-0"><RouterLink :to="'/business/' + b.id" class="hover:underline">{{ b.name }}</RouterLink><span><b>{{ b.views }}</b> views · {{ b.directions }} directions</span></div></div>
        <div class="card p-4"><h3 class="mb-3 font-bold">Businesses by category</h3>
          <div v-for="c in data.categories" :key="c.name" class="mb-2 text-sm"><div class="flex justify-between"><span>{{ c.name }}</span><b>{{ c.n }}</b></div><div class="h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-navy" :style="{ width: (c.n / maxCat) * 100 + '%' }"></div></div></div></div>
      </div>
    </template>
  </section>
</template>