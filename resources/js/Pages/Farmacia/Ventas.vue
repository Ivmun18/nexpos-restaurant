<template>
    <AppLayout title="Ventas" subtitle="Historial de ventas del minimarket">

        <!-- Header -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
            <div>
                <p style="font-size:24px; font-weight:800; color:#1E293B; margin:0;">🧾 Historial de Ventas</p>
                <p style="font-size:14px; color:#94A3B8; margin:4px 0 0;">{{ ventas.total }} ventas registradas</p>
            </div>
            <a href="/farmacia/pos" style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border-radius:10px; font-size:14px; font-weight:600; text-decoration:none;">
                + Nueva Venta
            </a>
        </div>

        <!-- Filtros -->
        <div style="background:white; border-radius:16px; padding:16px 20px; border:1px solid #E2E8F0; margin-bottom:16px; display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap;">
            <div>
                <label style="font-size:12px; font-weight:600; color:#64748B;">Desde</label>
                <input v-model="filtros.desde" type="date"
                    style="display:block; margin-top:4px; padding:8px 12px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none;"
                    @focus="$event.target.style.borderColor='#14B8A6'"
                    @blur="$event.target.style.borderColor='#E2E8F0'" />
            </div>
            <div>
                <label style="font-size:12px; font-weight:600; color:#64748B;">Hasta</label>
                <input v-model="filtros.hasta" type="date"
                    style="display:block; margin-top:4px; padding:8px 12px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none;"
                    @focus="$event.target.style.borderColor='#14B8A6'"
                    @blur="$event.target.style.borderColor='#E2E8F0'" />
            </div>
            <button @click="filtrar"
                style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                🔍 Filtrar
            </button>
            <button @click="setHoy"
                style="padding:10px 16px; background:#F0FDFA; color:#0F766E; border:1px solid #CCFBF1; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                Hoy
            </button>
            <button @click="setMes"
                style="padding:10px 16px; background:#F0FDFA; color:#0F766E; border:1px solid #CCFBF1; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                Este mes
            </button>
        </div>

        <!-- Tabla -->
        <div style="background:white; border-radius:20px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06); overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:14px 20px; text-align:left; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">#</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Comprobante</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Fecha</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Método</th>
                        <th style="padding:14px 20px; text-align:right; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Total</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">SUNAT</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!ventas.data.length">
                        <td colspan="6" style="text-align:center; padding:60px; color:#CBD5E1;">
                            <p style="font-size:40px; margin:0 0 8px;">🧾</p>
                            <p style="font-size:15px;">Sin ventas registradas</p>
                        </td>
                    </tr>
                    <tr v-for="venta in ventas.data" :key="venta.id"
                        style="border-top:1px solid #F1F5F9; transition:background 0.15s;"
                        @mouseenter="e => e.currentTarget.style.background='#F8FAFC'"
                        @mouseleave="e => e.currentTarget.style.background='white'">
                        <td style="padding:14px 20px; font-size:14px; color:#94A3B8;">{{ venta.id }}</td>
                        <td style="padding:14px 20px;">
                            <span style="font-size:14px; font-weight:600; color:#1E293B;">{{ venta.numero_completo }}</span>
                            <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ venta.tipo_comprobante === '03' ? 'Boleta' : 'Factura' }}</p>
                        </td>
                        <td style="padding:14px 20px; font-size:14px; color:#475569;">{{ formatFecha(venta.fecha_emision) }}</td>
                        <td style="padding:14px 20px;">
                            <span :style="{
                                padding: '4px 10px',
                                borderRadius: '20px',
                                fontSize: '12px',
                                fontWeight: '600',
                                background: metodoBg(venta.metodo_pago),
                                color: metodoColor(venta.metodo_pago),
                            }">{{ iconMetodo(venta.metodo_pago) }} {{ venta.metodo_pago || 'efectivo' }}</span>
                        </td>
                        <td style="padding:14px 20px; text-align:right; font-size:16px; font-weight:800; color:#14B8A6;">
                            S/ {{ (Number(venta.total_gravado) + Number(venta.total_igv) + Number(venta.total_inafecto) + Number(venta.total_exonerado)).toFixed(2) }}
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <span v-if="venta.nubefact_estado === 'aceptado'"
                                style="padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#dcfce7; color:#166534;">
                                ✅ Aceptado
                            </span>
                            <span v-else-if="venta.nubefact_estado === 'rechazado'"
                                style="padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#fee2e2; color:#dc2626;">
                                ❌ Rechazado
                            </span>
                            <span v-else-if="venta.nubefact_estado === 'anulado'"
                                style="padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#f1f5f9; color:#64748b;">
                                🚫 Anulado
                            </span>
                            <span v-else style="font-size:11px; color:#cbd5e1;">—</span>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <div style="display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
                                <a :href="`/minimarket/ventas/${venta.id}`"
                                    style="padding:6px 14px; background:#F0FDFA; color:#0F766E; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; border:1px solid #CCFBF1;">
                                    👁 Ver
                                </a>
                                <button @click="enviarWhatsApp(venta)"
                                    style="padding:6px 12px; background:#dcfce7; color:#16a34a; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                                    📱 WhatsApp
                                </button>
                                <button v-if="venta.nubefact_estado === 'rechazado'" @click="reintentar(venta.id)"
                                    style="padding:6px 12px; background:#e0f2fe; color:#0369a1; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                                    🔄 Reintentar
                                </button>
                                <button v-if="venta.nubefact_estado && venta.nubefact_estado !== 'anulado'" @click="anular(venta.id)"
                                    style="padding:6px 12px; background:#fee2e2; color:#dc2626; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                                    ❌ Anular
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div v-if="ventas.last_page > 1" style="display:flex; justify-content:center; gap:8px; margin-top:20px;">
            <a v-for="page in ventas.last_page" :key="page"
                :href="`/minimarket/ventas?page=${page}`"
                :style="{
                    padding: '8px 14px',
                    borderRadius: '8px',
                    fontSize: '14px',
                    fontWeight: '600',
                    textDecoration: 'none',
                    background: page === ventas.current_page ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : 'white',
                    color: page === ventas.current_page ? 'white' : '#64748B',
                    border: '1px solid #E2E8F0',
                }">{{ page }}</a>
        </div>

    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'

const reintentar = (id) => {
    if (confirm('¿Reintentar envío a SUNAT?')) {
        router.post(`/minimarket/ventas/${id}/reintentar`)
    }
}

const enviarWhatsApp = async (venta) => {
    const tel = prompt('📱 Número WhatsApp del cliente (ej: 987654321):')
    if (!tel) return
    const numero = '51' + tel.replace(/[^0-9]/g, '').slice(-9)

    const total = (Number(venta.total_gravado) + Number(venta.total_igv) + Number(venta.total_inafecto) + Number(venta.total_exonerado)).toFixed(2)
    const mensaje = `🧾 *Comprobante NEXPOS*\n\n📋 *${venta.numero_completo}*\n📅 Fecha: ${venta.fecha_emision}\n\n💰 *Total: S/ ${total}*\n\nGracias por su compra 🙏`

    try {
        const token = document.cookie.split(';').find(c => c.trim().startsWith('XSRF-TOKEN='))
        const csrfToken = token ? decodeURIComponent(token.split('=')[1]) : ''
        const res = await fetch('/api/whatsapp/enviar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-XSRF-TOKEN': csrfToken },
            body: JSON.stringify({ telefono: numero, mensaje })
        })
        const data = await res.json()
        alert(data.ok ? '✅ Mensaje enviado' : '❌ Error: ' + data.error)
    } catch(e) {
        alert('❌ Error de conexión')
    }
}

const anular = (id) => {
    if (confirm('¿Anular este comprobante en SUNAT?')) {
        router.post(`/minimarket/ventas/${id}/anular`)
    }
}

const props = defineProps({
    ventas: { type: Object, default: () => ({ data: [], total: 0, last_page: 1, current_page: 1 }) },
})

const props = defineProps({
    ventas: Object,
    desde:  { type: String, default: '' },
    hasta:  { type: String, default: '' },
})

const filtros = ref({ desde: props.desde, hasta: props.hasta })

const filtrar = () => router.get('/farmacia/ventas', filtros.value, { preserveState: false })

const setHoy = () => {
    const hoy = new Date().toISOString().split('T')[0]
    filtros.value = { desde: hoy, hasta: hoy }
    filtrar()
}

const setMes = () => {
    const now = new Date()
    filtros.value = {
        desde: new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0],
        hasta: now.toISOString().split('T')[0]
    }
    filtrar()
}

const formatFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const iconMetodo = (m) => ({ efectivo: '💵', yape: '📱', plin: '📲', tarjeta: '💳' })[m] || '💵'
const metodoBg   = (m) => ({ efectivo: '#F0FDF4', yape: '#EFF6FF', plin: '#F5F3FF', tarjeta: '#FFF7ED' })[m] || '#F0FDF4'
const metodoColor= (m) => ({ efectivo: '#166534', yape: '#1D4ED8', plin: '#6D28D9', tarjeta: '#C2410C' })[m] || '#166534'
</script>