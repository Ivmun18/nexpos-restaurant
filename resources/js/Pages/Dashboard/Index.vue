<template>
    <AppLayout title="Dashboard" :subtitle="`Resumen del ${props.industry_name}`">

        <!-- ══ KPIs PRINCIPALES ══ -->
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">

            <div style="background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:20px; padding:24px; color:white; box-shadow:0 8px 24px rgba(20,184,166,0.3);">
                <p style="font-size:13px; font-weight:600; opacity:0.8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">💰 Ventas hoy</p>
                <p style="font-size:36px; font-weight:800; margin:0; line-height:1;">S/ {{ Number(stats.ventas_hoy).toFixed(2) }}</p>
                <p style="font-size:13px; opacity:0.7; margin:10px 0 0;">{{ stats.mesas_hoy }} mesas atendidas</p>
            </div>

            <div style="background:linear-gradient(135deg,#6366F1,#4F46E5); border-radius:20px; padding:24px; color:white; box-shadow:0 8px 24px rgba(99,102,241,0.3);">
                <p style="font-size:13px; font-weight:600; opacity:0.8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">📅 Ventas del mes</p>
                <p style="font-size:36px; font-weight:800; margin:0; line-height:1;">S/ {{ Number(stats.ventas_mes).toFixed(2) }}</p>
                <p style="font-size:13px; opacity:0.7; margin:10px 0 0;">Este mes</p>
            </div>

            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <p style="font-size:13px; font-weight:600; color:#94A3B8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">🪑 Mesas</p>
                <p style="font-size:36px; font-weight:800; color:#1E293B; margin:0; line-height:1;">
                    {{ stats.mesas_ocupadas }}<span style="font-size:18px; color:#94A3B8;">/{{ stats.total_mesas }}</span>
                </p>
                <p style="font-size:13px; color:#94A3B8; margin:10px 0 0;">{{ stats.mesas_libres }} libres ahora</p>
            </div>

            <div :style="{
                background: stats.pedidos_cocina > 0 ? 'linear-gradient(135deg,#F59E0B,#D97706)' : 'white',
                borderRadius: '20px',
                padding: '24px',
                color: stats.pedidos_cocina > 0 ? 'white' : '#1E293B',
                border: stats.pedidos_cocina > 0 ? 'none' : '1px solid #E2E8F0',
                boxShadow: stats.pedidos_cocina > 0 ? '0 8px 24px rgba(245,158,11,0.3)' : '0 4px 12px rgba(0,0,0,0.06)',
            }">
                <p :style="{ fontSize:'13px', fontWeight:'600', opacity: stats.pedidos_cocina > 0 ? '0.8' : '1', color: stats.pedidos_cocina > 0 ? 'white' : '#94A3B8', margin:'0 0 12px', textTransform:'uppercase', letterSpacing:'1px' }">🍳 En cocina</p>
                <p style="font-size:36px; font-weight:800; margin:0; line-height:1;">{{ stats.pedidos_cocina }}</p>
                <p :style="{ fontSize:'13px', opacity: stats.pedidos_cocina > 0 ? '0.7' : '1', color: stats.pedidos_cocina > 0 ? 'white' : '#94A3B8', margin:'10px 0 0' }">Pedidos pendientes</p>
            </div>
        </div>

        <!-- ══ ESTADO MESAS ══ -->
        <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:24px;">

            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 24px;">📈 Ventas últimos 7 días</p>
                <div style="display:flex; align-items:flex-end; gap:12px; height:180px;">
                    <div v-for="(dia, i) in ventas_por_dia" :key="i"
                        style="flex:1; display:flex; flex-direction:column; align-items:center; gap:8px;">
                        <span style="font-size:12px; font-weight:600; color:#475569;">S/ {{ Number(dia.total).toFixed(0) }}</span>
                        <div :style="{
                            height: maxDia > 0 ? (dia.total / maxDia * 140) + 'px' : '4px',
                            background: i === ventas_por_dia.length - 1
                                ? 'linear-gradient(180deg,#14B8A6,#0F766E)'
                                : 'linear-gradient(180deg,#99F6E4,#5EEAD4)',
                            borderRadius: '8px 8px 0 0',
                            width: '100%',
                            minHeight: '6px',
                            boxShadow: i === ventas_por_dia.length - 1 ? '0 4px 12px rgba(20,184,166,0.3)' : 'none',
                        }"></div>
                        <span style="font-size:12px; font-weight:600; color:#94A3B8;">{{ dia.dia }}</span>
                    </div>
                </div>
            </div>

            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 20px;">💳 Pagos hoy</p>
                <div v-if="!metodos_pago.length" style="text-align:center; padding:30px 0; color:#CBD5E1;">
                    <p style="font-size:32px; margin:0 0 8px;">💳</p>
                    <p style="font-size:15px;">Sin cobros hoy</p>
                </div>
                <div v-for="m in metodos_pago" :key="m.metodo_pago"
                    style="display:flex; align-items:center; justify-content:space-between; padding:12px 0; border-bottom:1px solid #F1F5F9;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="font-size:24px;">{{ iconMetodo(m.metodo_pago) }}</span>
                        <div>
                            <p style="font-size:15px; font-weight:600; color:#1E293B; margin:0; text-transform:capitalize;">{{ m.metodo_pago }}</p>
                            <p style="font-size:13px; color:#94A3B8; margin:2px 0 0;">{{ m.cantidad }} cobros</p>
                        </div>
                    </div>
                    <span style="font-size:16px; font-weight:800; color:#14B8A6;">S/ {{ Number(m.total).toFixed(2) }}</span>
                </div>
            </div>
        </div>

        <!-- ══ FILA INFERIOR ══ -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 20px;">🏆 Top platos</p>
                <div v-if="!top_platos.length" style="text-align:center; padding:30px 0; color:#CBD5E1;">
                    <p style="font-size:32px; margin:0 0 8px;">🍽️</p>
                    <p style="font-size:15px;">Sin datos aún</p>
                </div>
                <div v-for="(p, i) in top_platos" :key="i"
                    style="display:flex; align-items:center; gap:14px; margin-bottom:16px;">
                    <div :style="{
                        width: '38px', height: '38px', borderRadius: '12px',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        fontSize: '16px', fontWeight: '800', flexShrink: '0',
                        background: i === 0 ? '#FEF3C7' : i === 1 ? '#F1F5F9' : i === 2 ? '#FEE2E2' : '#F0FDF4',
                        color: i === 0 ? '#92400E' : i === 1 ? '#475569' : i === 2 ? '#991B1B' : '#166534',
                    }">{{ i + 1 }}</div>
                    <div style="flex:1; min-width:0;">
                        <p style="font-size:16px; font-weight:600; color:#1E293B; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ p.nombre_producto }}</p>
                        <p style="font-size:13px; color:#94A3B8; margin:3px 0 0;">{{ Number(p.total_cantidad).toFixed(0) }} pedidos</p>
                    </div>
                    <span style="font-size:16px; font-weight:800; color:#14B8A6; white-space:nowrap;">S/ {{ Number(p.total_monto).toFixed(0) }}</span>
                </div>
            </div>

            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                    <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0;">🧾 Últimos cobros</p>
                </div>
                <div v-if="!ultimos_cobros.length" style="text-align:center; padding:30px 0; color:#CBD5E1;">
                    <p style="font-size:32px; margin:0 0 8px;">📭</p>
                    <p style="font-size:15px;">Sin cobros aún</p>
                </div>
                <div v-for="cobro in ultimos_cobros" :key="cobro.id"
                    style="display:flex; align-items:center; justify-content:space-between; padding:12px 0; border-bottom:1px solid #F1F5F9;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span style="font-size:24px;">{{ iconMetodo(cobro.metodo_pago) }}</span>
                        <div>
                            <p style="font-size:15px; font-weight:700; color:#1E293B; margin:0;">Mesa {{ cobro.mesa?.numero }}</p>
                            <p style="font-size:13px; color:#94A3B8; margin:3px 0 0; text-transform:capitalize;">{{ cobro.metodo_pago }}</p>
                        </div>
                    </div>
                    <span style="font-size:16px; font-weight:800; color:#14B8A6;">S/ {{ Number(cobro.total).toFixed(2) }}</span>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    industry_name:  { type: String, default: 'Restaurante' },
    stats:          { type: Object, default: () => ({}) },
    ventas_por_dia: { type: Array,  default: () => [] },
    ventas_por_mes: { type: Array,  default: () => [] },
    top_platos:     { type: Array,  default: () => [] },
    metodos_pago:   { type: Array,  default: () => [] },
    ultimos_cobros: { type: Array,  default: () => [] },
})

const maxDia = computed(() => {
    if (!props.ventas_por_dia.length) return 1
    return Math.max(...props.ventas_por_dia.map(d => d.total)) || 1
})

const iconMetodo = (metodo) => {
    const map = { efectivo: '💵', yape: '📱', plin: '📲', tarjeta: '💳' }
    return map[metodo] || '💵'
}
</script>