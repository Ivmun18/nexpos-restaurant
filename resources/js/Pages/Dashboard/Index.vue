<template>
    <AppLayout title="Dashboard" subtitle="Resumen del restaurante">

        <!-- KPIs -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: linear-gradient(135deg, #4F46E5, #7C3AED); border-radius: 16px; padding: 1.4rem; color: white; box-shadow: 0 4px 16px rgba(79,70,229,0.3);">
                <p style="font-size: 12px; opacity: 0.85; margin: 0; font-weight: 600;">💰 Ventas Hoy</p>
                <h2 style="font-size: 30px; font-weight: 800; margin: 8px 0 0;">S/ {{ Number(kpis.ventas_hoy).toFixed(2) }}</h2>
                <p style="font-size: 12px; opacity: 0.8; margin: 6px 0 0;">{{ kpis.cantidad_ventas_hoy }} venta{{ kpis.cantidad_ventas_hoy !== 1 ? 's' : '' }}</p>
            </div>

            <div style="background: linear-gradient(135deg, #F59E0B, #EF4444); border-radius: 16px; padding: 1.4rem; color: white; box-shadow: 0 4px 16px rgba(245,158,11,0.3);">
                <p style="font-size: 12px; opacity: 0.85; margin: 0; font-weight: 600;">🪑 Mesas Ocupadas</p>
                <h2 style="font-size: 30px; font-weight: 800; margin: 8px 0 0;">
                    {{ kpis.mesas_ocupadas }}<span style="font-size: 16px; opacity: 0.85; font-weight: 600;"> / {{ kpis.total_mesas }}</span>
                </h2>
                <p style="font-size: 12px; opacity: 0.8; margin: 6px 0 0;">Ahora mismo</p>
            </div>

            <div style="background: linear-gradient(135deg, #10B981, #059669); border-radius: 16px; padding: 1.4rem; color: white; box-shadow: 0 4px 16px rgba(16,185,129,0.3);">
                <p style="font-size: 12px; opacity: 0.85; margin: 0; font-weight: 600;">🍳 Pedidos en Cocina</p>
                <h2 style="font-size: 30px; font-weight: 800; margin: 8px 0 0;">{{ kpis.pedidos_pendientes_cocina }}</h2>
                <p style="font-size: 12px; opacity: 0.8; margin: 6px 0 0;">Pendientes de preparar</p>
            </div>
        </div>

        <!-- Ventas por sucursal + Metodo de pago -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">

            <!-- Ventas por sucursal -->
            <div v-if="ventas_por_sucursal.length" style="background: white; border-radius: 16px; border: 1px solid #E2E8F0; padding: 1.4rem;">
                <p style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 0 0 1rem;">🏬 Ventas por sucursal hoy</p>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
                    <div v-for="s in ventas_por_sucursal" :key="s.id" style="background: #F8FAFC; border-radius: 12px; padding: 14px 16px; border: 1px solid #E2E8F0;">
                        <p style="font-size: 12px; color: #94A3B8; margin: 0; font-weight: 600; text-transform: uppercase;">{{ s.nombre }}</p>
                        <p style="font-size: 22px; font-weight: 800; color: #0F766E; margin: 6px 0 8px;">S/ {{ s.total.toFixed(2) }}</p>
                        <div style="background: #E2E8F0; border-radius: 999px; height: 8px; overflow: hidden;">
                            <div :style="{ width: barraSucursal(s.total) + '%', height: '100%', background: 'linear-gradient(90deg,#14B8A6,#0F766E)', borderRadius: '999px' }"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metodo de pago -->
            <div style="background: white; border-radius: 16px; border: 1px solid #E2E8F0; padding: 1.4rem;">
                <p style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 0 0 1rem;">💳 Método de pago hoy</p>
                <div v-if="Object.keys(por_metodo_pago).length === 0" style="color: #94A3B8; font-size: 13px; text-align: center; padding: 1.5rem;">
                    Sin ventas todavía
                </div>
                <div v-else style="display: flex; flex-direction: column; gap: 8px;">
                    <div v-for="(v, metodo) in por_metodo_pago" :key="metodo"
                        style="display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: #F8FAFC; border-radius: 10px; border: 1px solid #E2E8F0;">
                        <span style="font-size: 13px; font-weight: 600; color: #1E293B; text-transform: capitalize;">
                            {{ iconMetodo(metodo) }} {{ metodo }}
                        </span>
                        <span style="font-size: 13px; color: #64748B;">{{ v.cantidad }} · <strong style="color:#0F766E;">S/ {{ v.total.toFixed(2) }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ultimos pedidos + Top platos -->
        <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 1rem;">

            <!-- Ultimos pedidos -->
            <div style="background: white; border-radius: 16px; border: 1px solid #E2E8F0; overflow: hidden;">
                <p style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 0; padding: 1.2rem 1.4rem 0.8rem;">🧾 Últimos pedidos del día</p>
                <div v-if="ultimos_pedidos.length === 0" style="color: #94A3B8; font-size: 13px; text-align: center; padding: 1.5rem;">
                    Sin pedidos hoy todavía
                </div>
                <table v-else style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: #F8FAFC;">
                            <th style="padding: 8px 16px; text-align: left; font-size: 11px; color: #94A3B8; font-weight: 600; text-transform: uppercase;">Mesa</th>
                            <th style="padding: 8px 16px; text-align: center; font-size: 11px; color: #94A3B8; font-weight: 600; text-transform: uppercase;">Items</th>
                            <th style="padding: 8px 16px; text-align: right; font-size: 11px; color: #94A3B8; font-weight: 600; text-transform: uppercase;">Total</th>
                            <th style="padding: 8px 16px; text-align: center; font-size: 11px; color: #94A3B8; font-weight: 600; text-transform: uppercase;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in ultimos_pedidos" :key="p.id" style="border-top: 1px solid #F1F5F9;">
                            <td style="padding: 10px 16px; font-weight: 600; color: #1E293B;">Mesa {{ p.mesa }}</td>
                            <td style="padding: 10px 16px; text-align: center; color: #64748B;">{{ p.items }}</td>
                            <td style="padding: 10px 16px; text-align: right; font-weight: 700; color: #0F766E;">S/ {{ p.total.toFixed(2) }}</td>
                            <td style="padding: 10px 16px; text-align: center;">
                                <span :style="estiloEstado(p.estado)">{{ p.estado }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Top platos -->
            <div style="background: white; border-radius: 16px; border: 1px solid #E2E8F0; padding: 1.4rem;">
                <p style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 0 0 1rem;">🏆 Top 5 platos de hoy</p>
                <div v-if="top_platos.length === 0" style="color: #94A3B8; font-size: 13px; text-align: center; padding: 1.5rem;">
                    Sin pedidos hoy todavía
                </div>
                <div v-for="(plato, i) in top_platos" :key="plato.nombre_producto"
                    style="display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #F1F5F9;">
                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                        <div :style="{
                            width: '26px', height: '26px', borderRadius: '50%', flexShrink: 0,
                            background: i === 0 ? '#14B8A6' : i === 1 ? '#0F766E' : '#E2E8F0',
                            color: i < 2 ? 'white' : '#64748B',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            fontSize: '11px', fontWeight: '700'
                        }">{{ i + 1 }}</div>
                        <p style="font-size: 13px; font-weight: 600; color: #1E293B; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ plato.nombre_producto }}</p>
                    </div>
                    <span style="font-size: 13px; font-weight: 800; color: #0F766E; flex-shrink: 0; margin-left: 8px;">x{{ plato.total_cantidad }}</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    kpis: Object,
    ventas_por_sucursal: { type: Array, default: () => [] },
    ultimos_pedidos: { type: Array, default: () => [] },
    top_platos: { type: Array, default: () => [] },
    por_metodo_pago: { type: Object, default: () => ({}) },
})

const maxVentaSucursal = () => Math.max(...props.ventas_por_sucursal.map(s => s.total), 0)
const barraSucursal = (total) => {
    const max = maxVentaSucursal()
    return max > 0 ? Math.max((total / max) * 100, 4) : 4
}

const iconMetodo = (m) => ({ efectivo: '💵', tarjeta: '💳', yape: '📱', plin: '📲', transferencia: '🏦' }[m] ?? '💰')

const estiloEstado = (estado) => {
    const map = {
        abierto:        { background: '#F1F5F9', color: '#475569' },
        enviado:        { background: '#FEF3C7', color: '#92400E' },
        en_preparacion: { background: '#FEF3C7', color: '#92400E' },
        listo:          { background: '#DCFCE7', color: '#166534' },
        cerrado:        { background: '#F0FDF4', color: '#166534' },
    }
    return { ...(map[estado] || { background: '#F1F5F9', color: '#64748B' }), fontSize: '11px', padding: '3px 10px', borderRadius: '20px', fontWeight: '700', textTransform: 'capitalize' }
}
</script>
