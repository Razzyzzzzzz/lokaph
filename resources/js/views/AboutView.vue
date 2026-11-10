<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { Search, Store, MessageCircle, Heart, ShieldCheck, MapPin, Mail, ShoppingBag, Star } from 'lucide-vue-next'
import { store } from '@/store'
const router = useRouter()
const CONTACT_EMAIL = 'Test1@gmail.com' // change to your support email
const steps = [
  [MapPin, 'Find what is nearby', 'Set your location and see local shops sorted by real distance.'],
  [ShoppingBag, 'Check products and reviews', 'Browse real products, prices, hours and customer ratings.'],
  [MessageCircle, 'Message or visit', 'Ask about stock first, get directions, then go to the store.']
]
const faqs = [
  ['Is it free to use?', 'Browsing businesses and products is free. A free account lets you save favorites, message sellers and write reviews.'],
  ['How does a business become Verified?', 'Every business is reviewed by an admin before it is listed. Approved businesses show a Verified badge.'],
  ['How is the distance computed?', 'We compare your location (set it manually or tap "Use my location") with the business address, in a straight line.'],
  ['How do I list my business?', 'Create an account and choose "I own a business". Then add your page, products, prices and opening hours.']
]

const role = computed(() => store.user?.role)
const ownerLabel = computed(() => (role.value === 'owner' ? 'Go to my business' : role.value === 'admin' ? 'Go to admin dashboard' : 'List your business'))

// Guest -> register as owner. Owner -> dashboard. Admin -> admin. Customer -> explain.
function listBusiness() {
  if (!store.user) return router.push('/register?role=owner')
  if (role.value === 'owner') return router.push('/owner')
  if (role.value === 'admin') return router.push('/admin')
  store.flash('You are logged in as a customer. Sign up with a business owner account to list a business.', true)
}
</script>
<template>
  <div class="space-y-10">
    <!-- Intro -->
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#0b1f3a] via-[#12307a] to-[#0b1f3a] p-6 text-white md:p-12">
      <div class="pointer-events-none absolute -right-20 -top-10 h-72 w-72 rounded-full bg-[#7c3aed]/30 blur-3xl"></div>
      <div class="relative max-w-2xl">
        <p class="text-2xl text-[#fbbf24] md:text-3xl" style="font-family: 'Kaushan Script', cursive">Shop Local, Grow Together!</p>
        <h1 class="mt-2 text-3xl font-extrabold leading-tight md:text-5xl">About Lokal<span class="text-[#fbbf24]">PH</span></h1>
        <p class="mt-4 text-slate-200 md:text-lg">LokalPH helps you find and support small Filipino businesses near you: the sari-sari store, the neighborhood bakery, the phone repair shop around the corner. Many of them are hard to find online, so we put them on one map with their products, hours and reviews.</p>
        <div class="mt-6 flex flex-wrap gap-3">
          <RouterLink to="/discover" class="inline-flex items-center gap-2 rounded-xl bg-[#fbbf24] px-6 py-3 text-sm font-bold text-[#0b1f3a]"><Search :size="16" /> Explore businesses</RouterLink>
          <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-white/40 px-6 py-3 text-sm font-semibold" @click="listBusiness">{{ ownerLabel }}</button>
        </div>
      </div>
    </section>

    <!-- How it works -->
    <section>
      <h2 class="text-xl font-extrabold">How it works</h2>
      <div class="mt-4 grid gap-4 md:grid-cols-3">
        <div v-for="([icon, t, d], i) in steps" :key="t" class="card p-5">
          <span class="grid h-11 w-11 place-items-center rounded-full bg-[#7c3aed]/10 text-[#7c3aed]"><component :is="icon" :size="22" /></span>
          <h3 class="mt-3 font-bold">{{ i + 1 }}. {{ t }}</h3><p class="mt-1 text-sm text-slate-500">{{ d }}</p>
        </div>
      </div>
    </section>

    <!-- Audiences -->
    <section class="grid gap-4 md:grid-cols-2">
      <div class="card p-6">
        <h2 class="flex items-center gap-2 text-lg font-extrabold"><Heart :size="20" class="text-[#7c3aed]" /> For customers</h2>
        <ul class="mt-3 space-y-2 text-sm text-slate-600">
          <li class="flex gap-2"><MapPin :size="16" class="mt-0.5 shrink-0 text-[#7c3aed]" /> See exactly how far each store is.</li>
          <li class="flex gap-2"><Star :size="16" class="mt-0.5 shrink-0 text-[#7c3aed]" /> Read ratings and reviews from other customers.</li>
          <li class="flex gap-2"><MessageCircle :size="16" class="mt-0.5 shrink-0 text-[#7c3aed]" /> Message sellers about stock and prices.</li>
        </ul>
        <RouterLink v-if="!store.user" to="/register" class="btn mt-5 bg-[#7c3aed] text-white">Create a free account</RouterLink>
        <RouterLink v-else to="/discover" class="btn mt-5 bg-[#7c3aed] text-white">Explore businesses</RouterLink>
      </div>
      <div class="card border-[#fbbf24] p-6">
        <h2 class="flex items-center gap-2 text-lg font-extrabold"><Store :size="20" class="text-[#7c3aed]" /> For business owners</h2>
        <ul class="mt-3 space-y-2 text-sm text-slate-600">
          <li class="flex gap-2"><Store :size="16" class="mt-0.5 shrink-0 text-[#7c3aed]" /> Create your page with products, prices and hours.</li>
          <li class="flex gap-2"><MapPin :size="16" class="mt-0.5 shrink-0 text-[#7c3aed]" /> Get found by people searching near you.</li>
          <li class="flex gap-2"><ShieldCheck :size="16" class="mt-0.5 shrink-0 text-[#7c3aed]" /> Earn a Verified badge after admin approval.</li>
        </ul>
        <button type="button" class="btn mt-5 bg-[#fbbf24] text-[#0b1f3a]" @click="listBusiness">{{ ownerLabel }}</button>
      </div>
    </section>

    <!-- FAQ -->
    <section>
      <h2 class="text-xl font-extrabold">Frequently asked questions</h2>
      <div class="mt-4 space-y-2">
        <details v-for="[q, a] in faqs" :key="q" class="card group p-4">
          <summary class="cursor-pointer list-none font-semibold marker:content-none">{{ q }}<span class="float-right text-[#7c3aed] transition group-open:rotate-45" aria-hidden="true">+</span></summary>
          <p class="mt-2 text-sm text-slate-600">{{ a }}</p>
        </details>
      </div>
    </section>

    <!-- Contact -->
    <section class="rounded-3xl bg-[#0b1f3a] p-6 text-center text-white md:p-8">
      <h2 class="text-xl font-extrabold">Questions or feedback?</h2>
      <p class="mt-1 text-sm text-white/75">We would love to hear from you.</p>
      <a :href="'mailto:' + CONTACT_EMAIL" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#7c3aed] px-6 py-3 text-sm font-semibold"><Mail :size="16" /> {{ CONTACT_EMAIL }}</a>
    </section>
  </div>
</template>