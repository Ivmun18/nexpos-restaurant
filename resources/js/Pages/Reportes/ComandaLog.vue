<template>
    <AppLayout title="Reporte cocina" subtitle="Historial de envíos a cocina">

        <!-- SELECTOR DE SUCURSAL -->
        <div v-if="sucursales.length > 1" style="display:flex; gap:8px; margin-bottom:1rem; flex-wrap:wrap;">
            <button @click="seleccionarSucursal('')"
                :style="{padding:'8px 18px', borderRadius:'10px', border:'none', cursor:'pointer', fontSize:'13px', fontWeight:'700', background: !filtros.sucursal_id ? '#1E293B' : '#F1F5F9', color: !filtros.sucursal_id ? 'white' : '#475569'}">
                🏬 Todos los locales
            </button>
            <button v-for="s in sucursales" :key="s.id" @click="seleccionarSucursal(s.id)"
                :style="{padding:'8px 18px', borderRadius:'10px', border:'none', cursor:'pointer', fontSize:'13px', fontWeight:'700', background: String(filtros.sucursal_id)===String(s.id) ? '#1E293B' : '#F1F5F9', color: String(filtros.sucursal_id)===String(s.id) ? 'white' : '#475569'}">
                {{ s.nombre }}
            </button>
        </div>

        <!-- FILTROS -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.2rem 1.5rem; margin-bottom:1.5rem; display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap;">
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Desde</label>
                <input v-model="filtros.desde" type="date" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none;" />
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Hasta</label>
                <input v-model="filtros.hasta" type="date" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none;" />
            </div>
            <div style="display:flex; gap:8px;">
                <button @click="buscar" style="padding:8px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                    Buscar
                </button>
                <button @click="hoy" style="padding:8px 14px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#64748B; cursor:pointer; background:white;">Hoy</button>
                <button @click="esteMes" style="padding:8px 14px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#64748B; cursor:pointer; background:white;">Este mes</button>
            </div>
        </div>

        <!-- TABLA -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                        <th style="text-align:left; padding:10px 16px; font-size:11px; color:#94A3B8; font-weight:700; text-transform:uppercase;">Fecha / Hora</th>
                        <th style="text-align:left; padding:10px 16px; font-size:11px; color:#94A3B8; font-weight:700; text-transform:uppercase;">Mesa</th>
                        <th style="text-align:left; padding:10px 16px; font-size:11px; color:#94A3B8; font-weight:700; text-transform:uppercase;">Mozo</th>
                        <th style="text-align:left; padding:10px 16px; font-size:11px; color:#94A3B8; font-weight:700; text-transform:uppercase;">Items</th>
                        <th style="text-align:right; padding:10px 16px; font-size:11px; color:#94A3B8; font-weight:700; text-transform:uppercase;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!logs.data.length">
                        <td colspan="5" style="text-align:center; padding:2rem; color:#94A3B8;">No hay envíos a cocina en este período.</td>
                    </tr>
                    <tr v-for="log in logs.data" :key="log.id" style="border-bottom:1px solid #F1F5F9;">
                        <td style="padding:10px 16px; color:#1E293B;">{{ formatFecha(log.created_at) }}</td>
                        <td style="padding:10px 16px; color:#1E293B; font-weight:600;">{{ log.mesa_nombre || '—' }}</td>
                        <td style="padding:10px 16px; color:#1E293B;">{{ log.mozo_nombre || '—' }}</td>
                        <td style="padding:10px 16px; color:#475569;">{{ resumenItems(log.items) }}</td>
                        <td style="padding:10px 16px; text-align:right;">
                            <button @click="verTicket(log)" style="padding:6px 14px; background:#F1F5F9; color:#1E293B; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                                🎫 Ver ticket
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        <div v-if="logs.last_page > 1" style="display:flex; justify-content:center; gap:6px; margin-top:1rem; flex-wrap:wrap;">
            <button v-for="(link, i) in logs.links" :key="i" :disabled="!link.url" @click="irAPagina(link.url)"
                v-html="link.label"
                :style="{padding:'6px 12px', borderRadius:'8px', border:'1px solid #E2E8F0', fontSize:'12px', fontWeight:'600', cursor: link.url ? 'pointer' : 'default', background: link.active ? '#1E293B' : 'white', color: link.active ? 'white' : (link.url ? '#475569' : '#CBD5E1')}">
            </button>
        </div>

        <!-- MODAL TICKET -->
        <div v-if="ticketVisible" class="ticket-overlay" @click.self="ticketVisible = false">
            <div class="ticket-modal">
                <div class="ticket-sep-doble"></div>
                <div class="ticket-titulo">Pedido a cocina</div>
                <div class="ticket-sep-doble"></div>

                <div class="ticket-meta">
                    <span>{{ ticketActual?.mesa_nombre || '—' }}</span>
                    <span>{{ formatFecha(ticketActual?.created_at) }}</span>
                </div>

                <div class="ticket-sep"></div>
                <div class="ticket-tabla-header">
                    <span class="ticket-col-cant">Cant.</span>
                    <span class="ticket-col-prod">Producto</span>
                </div>
                <div class="ticket-sep"></div>

                <div v-for="(item, i) in (ticketActual?.items || [])" :key="i" class="ticket-item">
                    <div class="ticket-item-fila">
                        <span class="ticket-col-cant">{{ item.cantidad }}</span>
                        <span class="ticket-col-prod">{{ nombreComanda(item.nombre_producto) }}</span>
                    </div>
                    <div v-for="(v, vi) in item.variantes" :key="'v'+vi" class="ticket-detalle">
                        <span v-if="esCon(v.opcion)">✓ </span>{{ v.opcion }}
                    </div>
                    <div v-if="item.nota_variante" class="ticket-detalle">{{ item.nota_variante }}</div>
                    <div v-for="(m, mi) in item.modificadores" :key="'m'+mi" class="ticket-detalle">
                        <span v-if="esCon(m)">✓ </span>{{ m }}
                    </div>
                    <div v-if="item.notas" class="ticket-detalle">{{ item.notas }}</div>
                </div>

                <div class="ticket-sep-doble"></div>
                <button @click="ticketVisible = false" style="width:100%; padding:10px; background:#F1F5F9; color:#1E293B; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                    Cerrar
                </button>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    logs:       { type: Object, default: () => ({ data: [], links: [], last_page: 1 }) },
    sucursales: { type: Array,  default: () => [] },
    filtros:    { type: Object, default: () => ({}) },
})

const filtros = ref({ ...props.filtros })

const ticketVisible = ref(false)
const ticketActual   = ref(null)

function verTicket(log) {
    ticketActual.value = log
    ticketVisible.value = true
}

// Mismo criterio que la comanda impresa en el POS: solo el nombre corto
// (ej. "COMBO 5 - CEVICHE + ARROZ" -> "COMBO 5").
function nombreComanda(nombre) {
    return (nombre || '').split(' - ')[0]
}

function esCon(texto) {
    return /^con\b/i.test((texto || '').trim())
}

function resumenItems(items) {
    if (!items || !items.length) return '—'
    const texto = items.map(it => `${it.cantidad}x ${nombreComanda(it.nombre_producto)}`).join(', ')
    return texto.length > 80 ? texto.slice(0, 80) + '…' : texto
}

function buscar() {
    const params = new URLSearchParams()
    params.set('desde',       filtros.value.desde       || '')
    params.set('hasta',       filtros.value.hasta       || '')
    params.set('sucursal_id', filtros.value.sucursal_id || '')
    router.visit('/reportes/comandas?' + params.toString(), { preserveScroll: true })
}

function irAPagina(url) {
    if (url) router.visit(url, { preserveScroll: true })
}

function seleccionarSucursal(id) {
    filtros.value.sucursal_id = id
    buscar()
}

function hoy() {
    const d = new Date().toISOString().slice(0, 10)
    filtros.value.desde = d
    filtros.value.hasta = d
    buscar()
}

function esteMes() {
    const now = new Date()
    filtros.value.desde = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0, 10)
    filtros.value.hasta = now.toISOString().slice(0, 10)
    buscar()
}

function formatFecha(f) {
    if (!f) return '—'
    const d = new Date(f)
    return d.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: '2-digit' }) +
           ' ' + d.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })
}
</script>

<style scoped>
.ticket-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}
.ticket-modal {
    background: white;
    border-radius: 12px;
    padding: 20px;
    width: 320px;
    max-height: 85vh;
    overflow-y: auto;
    font-family: 'Courier New', monospace;
    color: #000;
    text-transform: uppercase;
}
.ticket-titulo {
    font-size: 18px;
    font-weight: 900;
    text-align: center;
}
.ticket-meta {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    font-weight: 700;
    margin: 6px 0;
}
.ticket-tabla-header, .ticket-item-fila {
    display: flex;
    gap: 6px;
    font-size: 15px;
    font-weight: 900;
}
.ticket-col-cant { width: 40px; flex-shrink: 0; }
.ticket-col-prod { flex: 1; }
.ticket-item { margin-bottom: 14px; }
.ticket-detalle { font-size: 13px; font-weight: 700; padding-left: 46px; }
.ticket-sep { border-top: 1px dashed #000; margin: 6px 0; }
.ticket-sep-doble { border-top: 3px double #000; margin: 6px 0; }
</style>
