import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { PH_BOUNDS } from '@/utils'
import icon from 'leaflet/dist/images/marker-icon.png'
import icon2x from 'leaflet/dist/images/marker-icon-2x.png'
import shadow from 'leaflet/dist/images/marker-shadow.png'

// Leaflet guesses image paths from its CSS, which breaks under bundlers. Remove that and set URLs explicitly.
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({ iconUrl: icon, iconRetinaUrl: icon2x, shadowUrl: shadow })

export const TILES = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'
export const ATTR = '© OpenStreetMap'
/** Keeps maps inside the Philippines: no panning or zooming out to other countries. */
export const phMap = { maxBounds: PH_BOUNDS, maxBoundsViscosity: 1, minZoom: 5 }
export const TILE_OPTS = { attribution: ATTR, bounds: PH_BOUNDS }
/** Brand-green map pin used for business markers. */
export const pin = L.divIcon({
  className: '', iconSize: [24, 24], iconAnchor: [12, 24], popupAnchor: [0, -24],
  html: '<div style="width:24px;height:24px;border-radius:50% 50% 50% 0;background:#16a34a;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.45);transform:rotate(-45deg)"></div>'
})
/** Larger navy pin for the hovered/selected business. */
export const pinActive = L.divIcon({
  className: '', iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -32],
  html: '<div style="width:32px;height:32px;border-radius:50% 50% 50% 0;background:#0b1f3a;border:3px solid #fff;box-shadow:0 3px 10px rgba(0,0,0,.5);transform:rotate(-45deg)"></div>'
})
export default L