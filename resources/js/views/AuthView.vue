\<script setup>
import { reactive, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { User, Mail, Lock, Eye, EyeOff, MapPin, Heart, MessageCircle, ShieldCheck, Store, ArrowLeft } from 'lucide-vue-next'
import BrandLogo from '@/components/BrandLogo.vue'
import { api } from '@/api'
import { store } from '@/store'

const props = defineProps({ mode: String })
const route = useRoute(), router = useRouter()
const isLogin = computed(() => props.mode === 'login')
const f = reactive({ name: '', email: '', password: '', confirm: '', role: 'customer' })
const touched = reactive({}), show = ref(false), busy = ref(false), serverError = ref('')
const inputCls = 'h-12 !pl-10 focus:border-brand focus:outline-none focus:ring-2 focus:ring-green-200'

const errors = computed(() => {
  const e = {}
  if (!isLogin.value && !f.name.trim()) e.name = 'Enter your full name.'
  if (!/^\S+@\S+\.\S+$/.test(f.email)) e.email = 'Enter a valid email address.'
  if (isLogin.value ? !f.password : f.password.length < 8) e.password = isLogin.value ? 'Enter your password.' : 'Use at least 8 characters.'
  if (!isLogin.value && f.confirm !== f.password) e.confirm = 'Passwords do not match.'
  return e
})
const err = (k) => touched[k] && errors.value[k]
const rules = computed(() => [
  { ok: f.password.length >= 8, t: '8+ characters' }, { ok: /[A-Z]/.test(f.password) && /[a-z]/.test(f.password), t: 'Upper & lower case' }, { ok: /\d/.test(f.password), t: 'A number' }])
const strength = computed(() => { const p = f.password; let s = 0
  if (p.length >= 8) s++; if (p.length >= 12) s++; if (/[A-Z]/.test(p) && /[a-z]/.test(p)) s++; if (/\d/.test(p)) s++; if (/[^A-Za-z0-9]/.test(p)) s++
  return Math.min(s, 4) })
const STRENGTH = [['Too weak', 'bg-red-500'], ['Weak', 'bg-orange-500'], ['Fair', 'bg-amber-400'], ['Good', 'bg-lime-500'], ['Strong', 'bg-green-600']]

const customerPerks = [[MapPin, 'Real distances', 'Know exactly how far each store is from you.'], [Heart, 'Save your favorites', 'Keep the shops and products you love in one place.'], [MessageCircle, 'Message sellers', 'Ask about stock and prices before you go.']]
const ownerPerks = [[Store, 'List your business', 'Create your page, products, prices and hours.'], [MapPin, 'Get found nearby', 'Customers searching near you will see your store.'], [ShieldCheck, 'Verified listings', 'Admin-approved businesses build customer trust.']]
const panel = computed(() => !isLogin.value && f.role === 'owner'
  ? { title: 'Put your store in front of nearby customers.', perks: ownerPerks }
  : { title: isLogin.value ? 'Welcome back to your neighborhood marketplace.' : 'Find what you need from stores near you.', perks: customerPerks })
const year = new Date().getFullYear()

async function submit() {
  ;['name', 'email', 'password', 'confirm'].forEach((k) => (touched[k] = true)); serverError.value = ''
  if (Object.keys(errors.value).length) return
  busy.value = true
  try {
    const r = await api('/' + props.mode, 'POST', isLogin.value ? { email: f.email, password: f.password } : { name: f.name, email: f.email, password: f.password, role: f.role })
    store.setSession(r); await store.loadFavs(); store.flash('Welcome, ' + r.user.name + '!')
    const to = route.query.redirect // only allow in-app paths
    router.push(typeof to === 'string' && to.startsWith('/') && !to.startsWith('//') ? to : r.user.role === 'admin' ? '/admin' : r.user.role === 'owner' ? '/owner' : '/')
  } catch (e) { serverError.value = e.message } finally { busy.value = false }
}
</script>
<template>
  <div class="grid min-h-screen bg-white lg:grid-cols-[minmax(0,1.05fr)_minmax(0,1fr)]">
    <!-- Brand panel -->
    <aside class="relative hidden overflow-hidden bg-gradient-to-br from-navy via-[#0d2b4f] to-emerald-900 p-12 text-white lg:flex lg:flex-col lg:justify-between">
      <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-green-500/20 blur-3xl"></div>
      <div class="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-emerald-400/10 blur-3xl"></div>
      <p class="relative text-xs font-semibold uppercase tracking-[0.2em] text-green-300">Philippine small business finder</p>
      <div class="relative max-w-lg">
        <h2 class="text-4xl font-extrabold leading-tight">{{ panel.title }}</h2>
        <ul class="mt-10 space-y-6">
          <li v-for="[icon, t, d] in panel.perks" :key="t" class="flex gap-4">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20"><component :is="icon" :size="20" class="text-green-300" /></span>
            <span><b class="block">{{ t }}</b><span class="text-sm text-slate-300">{{ d }}</span></span>
          </li>
        </ul>
      </div>
      <p class="relative text-xs text-slate-400">© {{ year }} LokalPH · Made for Filipino small businesses</p>
    </aside>

    <!-- Form -->
    <main class="flex flex-col px-6 py-8 sm:px-10">
      <div class="flex items-center justify-between">
        <RouterLink to="/" class="flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-navy"><ArrowLeft :size="16" /> Back to LokalPH</RouterLink>
      </div>

      <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center py-8">
        <div class="mb-6 select-none"><BrandLogo size="h-16" /></div>
        <h1 class="text-3xl font-extrabold tracking-tight">{{ isLogin ? 'Log in' : 'Create your account' }}</h1>
        <p class="mb-6 mt-1 text-slate-500">{{ isLogin ? 'Welcome back! Enter your details to continue.' : 'Join LokalPH in less than a minute.' }}</p>

        <div v-if="serverError" class="mb-4 flex gap-2 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700" role="alert">{{ serverError }}</div>

        <form class="space-y-4" novalidate @submit.prevent="submit">
          <div v-if="!isLogin" role="radiogroup" aria-label="Account type" class="grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1 text-sm font-semibold">
            <button type="button" role="radio" :aria-checked="f.role === 'customer'" class="rounded-lg py-2.5 transition" :class="f.role === 'customer' ? 'bg-white text-navy shadow' : 'text-slate-500'" @click="f.role = 'customer'">I'm a customer</button>
            <button type="button" role="radio" :aria-checked="f.role === 'owner'" class="rounded-lg py-2.5 transition" :class="f.role === 'owner' ? 'bg-white text-navy shadow' : 'text-slate-500'" @click="f.role = 'owner'">I own a business</button>
          </div>

          <label v-if="!isLogin" class="block text-sm font-medium">Full name
            <span class="relative mt-1 block"><User :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input v-model="f.name" autocomplete="name" placeholder="Juan Dela Cruz" :class="[inputCls, err('name') && '!border-red-500']" :aria-invalid="!!err('name')" @blur="touched.name = true"></span>
            <span v-if="err('name')" class="text-xs font-normal text-red-600">{{ errors.name }}</span></label>

          <label class="block text-sm font-medium">Email
            <span class="relative mt-1 block"><Mail :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input v-model="f.email" type="email" autocomplete="email" inputmode="email" placeholder="you@example.com" :class="[inputCls, err('email') && '!border-red-500']" :aria-invalid="!!err('email')" @blur="touched.email = true"></span>
            <span v-if="err('email')" class="text-xs font-normal text-red-600">{{ errors.email }}</span></label>

          <label class="block text-sm font-medium">Password
            <span class="relative mt-1 block"><Lock :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input v-model="f.password" :type="show ? 'text' : 'password'" :autocomplete="isLogin ? 'current-password' : 'new-password'" placeholder="••••••••" :class="[inputCls, '!pr-12', err('password') && '!border-red-500']" :aria-invalid="!!err('password')" @blur="touched.password = true">
              <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-navy" :aria-label="show ? 'Hide password' : 'Show password'" @click="show = !show"><component :is="show ? EyeOff : Eye" :size="18" /></button></span>
            <span v-if="err('password')" class="text-xs font-normal text-red-600">{{ errors.password }}</span></label>

          <div v-if="!isLogin && f.password" class="-mt-1 space-y-2">
            <div class="flex gap-1"><span v-for="n in 4" :key="n" class="h-1.5 flex-1 rounded-full transition-colors" :class="n <= strength ? STRENGTH[strength][1] : 'bg-slate-200'"></span></div>
            <div class="flex flex-wrap gap-1.5 text-xs"><span v-for="r in rules" :key="r.t" class="rounded-full px-2 py-0.5" :class="r.ok ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500'">{{ r.ok ? '✓' : '○' }} {{ r.t }}</span></div>
          </div>

          <label v-if="!isLogin" class="block text-sm font-medium">Confirm password
            <span class="relative mt-1 block"><Lock :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
              <input v-model="f.confirm" :type="show ? 'text' : 'password'" autocomplete="new-password" placeholder="••••••••" :class="[inputCls, err('confirm') && '!border-red-500']" :aria-invalid="!!err('confirm')" @blur="touched.confirm = true"></span>
            <span v-if="err('confirm')" class="text-xs font-normal text-red-600">{{ errors.confirm }}</span></label>

          <button class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-brand font-semibold text-white shadow-sm transition hover:bg-green-700 disabled:opacity-60" :disabled="busy">
            <span v-if="busy" class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>{{ busy ? (isLogin ? 'Logging in…' : 'Creating account…') : isLogin ? 'Log in' : 'Create account' }}</button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">{{ isLogin ? "Don't have an account?" : 'Already have an account?' }}
          <RouterLink :to="{ path: isLogin ? '/register' : '/login', query: route.query }" class="font-semibold text-brand hover:underline">{{ isLogin ? 'Sign up' : 'Log in' }}</RouterLink></p>
        <p class="mt-4 flex items-center justify-center gap-1.5 text-xs text-slate-400"><ShieldCheck :size="14" /> Passwords are securely hashed and never stored in plain text.</p>
      </div>
    </main>
  </div>
</template>