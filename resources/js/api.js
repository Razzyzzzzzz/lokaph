export const session = { csrf: '' }

async function request(path, method, body, retry, signal) {
  const opt = { method, signal, headers: { 'X-CSRF-Token': session.csrf } }
  if (body instanceof FormData) opt.body = body
  else if (body) { opt.headers['Content-Type'] = 'application/json'; opt.body = JSON.stringify(body) }
  const res = await fetch('/api' + path, opt)
  const data = await res.json().catch(() => ({}))
  // Session/CSRF token expired: fetch a fresh token and retry once.
  if (res.status === 419 && retry && path !== '/me') { session.csrf = (await api('/me')).csrf; return request(path, method, body, false, signal) }
  if (!res.ok) throw new Error(data.error || 'Something went wrong')
  return { data, res }
}

/** Thin fetch wrapper for the PHP REST API. Throws Error(message) on failure. */
export async function api(path, method = 'GET', body) { return (await request(path, method, body, true)).data }

/** Paginated GET: returns { items, total } using the X-Total-Count header. Supports AbortSignal. */
export async function apiPage(path, signal) {
  const { data, res } = await request(path, 'GET', undefined, true, signal)
  return { items: data, total: +res.headers.get('X-Total-Count') || data.length }
}