export const DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
export const PRESETS = [
  { label: 'Manila', lat: 14.5995, lng: 120.9842 },
  { label: 'Quezon City', lat: 14.676, lng: 121.0437 },
  { label: 'Baguio', lat: 16.4023, lng: 120.596 },
  { label: 'Laoag', lat: 18.1978, lng: 120.5936 },
  { label: 'Tuguegarao', lat: 17.6132, lng: 121.727 },
  { label: 'Angeles', lat: 15.145, lng: 120.5887 },
  { label: 'Batangas City', lat: 13.7565, lng: 121.0583 },
  { label: 'Naga', lat: 13.6218, lng: 123.1948 },
  { label: 'Legazpi', lat: 13.1391, lng: 123.7438 },
  { label: 'Puerto Princesa', lat: 9.7392, lng: 118.7353 },
  { label: 'Iloilo City', lat: 10.7202, lng: 122.5621 },
  { label: 'Bacolod', lat: 10.6765, lng: 122.9509 },
  { label: 'Cebu City', lat: 10.3157, lng: 123.8854 },
  { label: 'Dumaguete', lat: 9.3068, lng: 123.3054 },
  { label: 'Tacloban', lat: 11.2543, lng: 124.963 },
  { label: 'Cagayan de Oro', lat: 8.4542, lng: 124.6319 },
  { label: 'Butuan', lat: 8.9475, lng: 125.5406 },
  { label: 'Davao City', lat: 7.1907, lng: 125.4553 },
  { label: 'General Santos', lat: 6.1164, lng: 125.1716 },
  { label: 'Zamboanga City', lat: 6.9214, lng: 122.079 }]
export const km = (d) => d == null ? '' : d < 1 ? Math.round(d * 1000) + ' m away' : d.toFixed(1) + ' km away'
export const peso = (n) => '₱' + Number(n).toLocaleString('en-PH', { minimumFractionDigits: 2 })
export const dirUrl = (b) => `https://www.google.com/maps/dir/?api=1&destination=${b.lat},${b.lng}`
export const esc = (t) => String(t).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]))
/** Query string builder that drops empty values and appends the user's location. */
export const qs = (o, loc) => new URLSearchParams(Object.entries({ ...o, ...(loc ? { lat: loc.lat, lng: loc.lng } : {}) })
  .filter(([, v]) => v !== '' && v != null && v !== false)).toString()
/** Rough bounding box of the Philippines. */
export const PH_BOUNDS = [[4.5, 116], [21.5, 127]]
export const isInPH = (lat, lng) => lat >= 4.5 && lat <= 21.5 && lng >= 116 && lng <= 127
/** Turns a Nominatim result into a readable title, a secondary line (street, barangay, city, province) and a short label. */
export function formatPlace(r) {
  const a = r.address || {}
  const road = a.road || a.pedestrian || a.footway || a.path
  const street = [a.house_number, road].filter(Boolean).join(' ')
  const area = a.quarter || a.neighbourhood || a.suburb || a.village || a.hamlet
  const city = a.city || a.town || a.municipality || a.city_district || a.county
  const prov = a.state || a.region || a.province
  const title = r.name || street || area || city || prov || (r.display_name || '').split(',')[0]
  const sub = [street !== title && street, area !== title && area, city !== title && city, prov !== title && prov !== city && prov].filter(Boolean).join(', ')
  return { title, sub, short: [title, city && city !== title ? city : ''].filter(Boolean).join(', ') }
}