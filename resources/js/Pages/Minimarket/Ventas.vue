<template>
    <AppLayout title="Ventas" subtitle="Historial de ventas del minimarket">

        <!-- Header -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
            <div>
                <p style="font-size:24px; font-weight:800; color:#1E293B; margin:0;">🧾 Historial de Ventas</p>
                <p style="font-size:14px; color:#94A3B8; margin:4px 0 0;">{{ ventas.total }} ventas registradas</p>
            </div>
            <a href="/minimarket/pos" style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border-radius:10px; font-size:14px; font-weight:600; text-decoration:none;">
                + Nueva Venta
            </a>
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
                            S/ {{ Number(venta.total_gravado).toFixed(2) }}
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <a :href="`/minimarket/ventas/${venta.id}`"
                                style="padding:6px 14px; background:#F0FDFA; color:#0F766E; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; border:1px solid #CCFBF1;">
                                Ver ticket
                            </a>
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

const props = defineProps({
    ventas: { type: Object, default: () => ({ data: [], total: 0, last_page: 1, current_page: 1 }) },
})

const formatFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const iconMetodo = (m) => ({ efectivo: '💵', yape: '📱', plin: '📲', tarjeta: '💳' })[m] || '💵'
const metodoBg   = (m) => ({ efectivo: '#F0FDF4', yape: '#EFF6FF', plin: '#F5F3FF', tarjeta: '#FFF7ED' })[m] || '#F0FDF4'
const metodoColor= (m) => ({ efectivo: '#166534', yape: '#1D4ED8', plin: '#6D28D9', tarjeta: '#C2410C' })[m] || '#166534'
</script>