import { reactive } from 'vue'
import { api, session } from './api'
import { isInPH, formatPlace } from './utils'

const toLogin = () => import('./router').then((m) => m.default.push({ path: '/login', query: { redirect: m.default.currentRoute.value.fullPath } }))
const key = (l) => l.lat.toFixed(4) + ',' + l.lng.toFixed(4)

export const store = reactive({
  user: null, cats: { business: [], product: [] }, favs: { businesses: [], products: [] },
  loc: JSON.parse(localStorage.getItem('loc') || 'null'), recent: JSON.parse(localStorage.getItem('recentLocs') || '[]'),
  locOpen: false, toast: null, modal: null, unread: 0,

  async init() {
    try { const m = await api('/me'); session.csrf = m.csrf; this.user = m.user; this.cats = await api('/categories'); await this.loadFavs(); setInterval(() => this.user && this.loadUnread(), 30000) }
    catch (e) { this.flash('Cannot reach the server: ' + e.message, true) }
  },
  flash(m, bad = false) { this.toast = { m, bad }; setTimeout(() => (this.toast = null), 3200) },
  async loadFavs() { if (this.user) { this.favs = await api('/favorites'); this.loadUnread() } },
  async loadUnread() { try { this.unread = (await api('/messages/unread')).count } catch { /* ignore */ } },
  /** Fire-and-forget analytics event (directions / call). */
  track(business_id, event) { api('/track', 'POST', { business_id, event }).catch(() => {}) },
  setSession(r) { this.user = r.user; session.csrf = r.csrf },
  async logout() { await api('/logout', 'POST'); const m = await api('/me'); this.user = null; session.csrf = m.csrf; this.favs = { businesses: [], products: [] }; this.unread = 0 },
  setLoc(l) {
    this.loc = { lat: l.lat, lng: l.lng, label: l.label }; localStorage.setItem('loc', JSON.stringify(this.loc)); this.locOpen = false
    this.recent = [this.loc, ...this.recent.filter((r) => key(r) !== key(this.loc))].slice(0, 5); localStorage.setItem('recentLocs', JSON.stringify(this.recent))
  },
  /** Replaces "My location" with the street/barangay/city found by reverse geocoding. */
  async nameLocation() {
    const cur = this.loc
    try {
      const r = await (await fetch('https://nominatim.openstreetmap.org/reverse?' + new URLSearchParams({ format: 'jsonv2', lat: cur.lat, lon: cur.lng, zoom: '18', addressdetails: '1', 'accept-language': 'en' }))).json()
      if (r.address && this.loc === cur) { cur.label = formatPlace(r).short.slice(0, 30); localStorage.setItem('loc', JSON.stringify(cur)) }
    } catch { /* keep the generic label */ }
  },
  locate() {
    if (!navigator.geolocation) return this.flash('Location is not available. Pick a city instead.', true)
    navigator.geolocation.getCurrentPosition(
      (p) => {
        if (!isInPH(p.coords.latitude, p.coords.longitude)) { this.flash('LokalPH covers the Philippines only. Pick a city instead.', true); this.locOpen = true; return }
        this.setLoc({ lat: p.coords.latitude, lng: p.coords.longitude, label: 'My location' }); this.nameLocation()
      },
      () => { this.flash('Location permission denied. Pick a city instead.', true); this.locOpen = true }, { timeout: 10000 })
  },
  async toggleFav(type, id) {
    if (!this.user) return toLogin()
    try { const r = await api('/favorites/toggle', 'POST', { type, id }); const a = this.favs[type === 'product' ? 'products' : 'businesses']; r.saved ? a.push(id) : a.splice(a.indexOf(id), 1) }
    catch (e) { this.flash(e.message, true) }
  },
  async openProduct(p) { try { this.modal = { t: 'product', p: await api('/products/' + p.id) } } catch (e) { this.flash(e.message, true) } },
  openContact(p) { if (!this.user) { this.modal = null; return toLogin() } this.modal = { t: 'contact', name: p.business, business_id: p.business_id, product_id: p.price !== undefined ? p.id : null } },
  openReport(o) { if (!this.user) return toLogin(); this.modal = { t: 'report', ...o } }
})