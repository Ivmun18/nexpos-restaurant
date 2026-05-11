<template>
    <AppLayout title="Dashboard" :subtitle="`Resumen del ${props.industry_name}`">

        <!-- ══ KPIs PRINCIPALES ══ -->
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">

            <div style="background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:20px; padding:24px; color:white; box-shadow:0 8px 24px rgba(20,184,166,0.3);">
                <p style="font-size:13px; font-weight:600; opacity:0.8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">💰 Ventas hoy</p>
                <p style="font-size:36px; font-weight:800; margin:0; line-height:1;">S/ {{ Number(stats.ventas_hoy).toFixed(2) }}</p>
                <p style="font-size:13px; opacity:0.7; margin:10px 0 0;">{{ stats.ventas_hoy_count }} transacciones</p>
            </div>

            <div style="background:linear-gradient(135deg,#6366F1,#4F46E5); border-radius:20px; padding:24px; color:white; box-shadow:0 8px 24px rgba(99,102,241,0.3);">
                <p style="font-size:13px; font-weight:600; opacity:0.8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">📅 Ventas del mes</p>
                <p style="font-size:36px; font-weight:800; margin:0; line-height:1;">S/ {{ Number(stats.ventas_mes).toFixed(2) }}</p>
                <p style="font-size:13px; opacity:0.7; margin:10px 0 0;">Este mes</p>
            </div>

            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <p style="font-size:13px; font-weight:600; color:#94A3B8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">🧾 Total ventas</p>
                <p style="font-size:36px; font-weight:800; color:#1E293B; margin:0; line-height:1;">{{ stats.total_ventas }}</p>
                <p style="font-size:13px; color:#94A3B8; margin:10px 0 0;">Historial completo</p>
            </div>

            <div :style="{
                background: stats.stock_bajo > 0 ? 'linear-gradient(135deg,#EF4444,#DC2626)' : 'white',
                borderRadius: '20px', padding: '24px',
                color: stats.stock_bajo > 0 ? 'white' : '#1E293B',
                border: stats.stock_bajo > 0 ? 'none' : '1px solid #E2E8F0',
                boxShadow: stats.stock_bajo > 0 ? '0 8px 24px rgba(239,68,68,0.3)' : '0 4px 12px rgba(0,0,0,0.06)',
            }">
                <p :style="{ fontSize:'13px', fontWeight:'600', opacity: stats.stock_bajo > 0 ? '0.8':'1', color: stats.stock_bajo > 0 ? 'white':'#94A3B8', margin:'0 0 12px', textTransform:'uppercase', letterSpacing:'1px' }">⚠️ Stock bajo</p>
                <p style="font-size:36px; font-weight:800; margin:0; line-height:1;">{{ stats.stock_bajo }}</p>
                <p :style="{ fontSize:'13px', opacity: stats.stock_bajo > 0 ? '0.7':'1', color: stats.stock_bajo > 0 ? 'white':'#94A3B8', margin:'10px 0 0' }">Productos por reponer</p>
            </div>
        </div>

        <!-- ══ GRÁFICO + ÚLTIMAS VENTAS ══ -->
        <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:24px;">

            <!-- Alertas vencimientos -->
            <div v-if="vencidos_count > 0 || por_vencer_count > 0"
                style="background:linear-gradient(135deg,#fef2f2,#fff7ed); border-radius:16px; padding:20px; border:1px solid #fca5a5; margin-bottom:16px;">
                <p style="font-size:15px; font-weight:700; color:#dc2626; margin:0 0 12px;">⚠️ Alertas de Vencimiento</p>
                <div style="display:flex; gap:12px;">
                    <div v-if="vencidos_count > 0" style="flex:1; background:white; border-radius:12px; padding:16px; border:1px solid #fca5a5; text-align:center;">
                        <p style="font-size:28px; font-weight:800; color:#dc2626; margin:0;">{{ vencidos_count }}</p>
                        <p style="font-size:12px; color:#64748b; margin:4px 0 0;">🚨 Productos vencidos</p>
                        <a href="/farmacia/vencimientos" style="font-size:12px; color:#dc2626; font-weight:600;">Ver detalle →</a>
                    </div>
                    <div v-if="por_vencer_count > 0" style="flex:1; background:white; border-radius:12px; padding:16px; border:1px solid #fed7aa; text-align:center;">
                        <p style="font-size:28px; font-weight:800; color:#ea580c; margin:0;">{{ por_vencer_count }}</p>
                        <p style="font-size:12px; color:#64748b; margin:4px 0 0;">⏰ Por vencer (30 días)</p>
                        <a href="/farmacia/vencimientos" style="font-size:12px; color:#ea580c; font-weight:600;">Ver detalle →</a>
                    </div>
                </div>
            </div>

            <!-- Ventas por día -->
            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 24px;">📈 Ventas últimos 7 días</p>
                <div style="display:flex; align-items:flex-end; gap:12px; height:180px;">
                    <div v-for="(dia, i) in ventas_por_dia" :key="i"
                        style="flex:1; display:flex; flex-direction:column; align-items:center; gap:8px;">
                        <span style="font-size:12px; font-weight:600; color:#475569;">S/ {{ Number(dia.total).toFixed(0) }}</span>
                        <div :style="{
                            height: maxDia > 0 ? (dia.total / maxDia * 140) + 'px' : '4px',
                            background: i === ventas_por_dia.length - 1 ? 'linear-gradient(180deg,#14B8A6,#0F766E)' : 'linear-gradient(180deg,#99F6E4,#5EEAD4)',
                            borderRadius: '8px 8px 0 0', width: '100%', minHeight: '6px',
                        }"></div>
                        <span style="font-size:12px; font-weight:600; color:#94A3B8;">{{ dia.dia }}</span>
                    </div>
                </div>
            </div>

            <!-- Últimas ventas -->
            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 20px;">🧾 Últimas ventas</p>
                <div v-if="!ultimas_ventas.length" style="text-align:center; padding:30px 0; color:#CBD5E1;">
                    <p style="font-size:32px; margin:0 0 8px;">📭</p>
                    <p style="font-size:15px;">Sin ventas aún</p>
                </div>
                <div v-for="v in ultimas_ventas" :key="v.id"
                    style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid #F1F5F9;">
                    <div>
                        <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ v.numero_completo }}</p>
                        <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ formatFecha(v.fecha_emision) }}</p>
                    </div>
                    <span style="font-size:15px; font-weight:800; color:#14B8A6;">S/ {{ Number(v.total_gravado).toFixed(2) }}</span>
                </div>
            </div>
        </div>

        <!-- ══ TOP PRODUCTOS ══ -->
        <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
            <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 20px;">🏆 Top productos más vendidos</p>
            <div v-if="!top_productos.length" style="text-align:center; padding:30px 0; color:#CBD5E1;">
                <p style="font-size:32px; margin:0 0 8px;">📦</p>
                <p style="font-size:15px;">Sin datos aún</p>
            </div>
            <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:12px;">
                <div v-for="(p, i) in top_productos" :key="i"
                    style="text-align:center; padding:16px; border:1px solid #E2E8F0; border-radius:14px;">
                    <div :style="{
                        width:'40px', height:'40px', borderRadius:'12px', display:'flex', alignItems:'center', justifyContent:'center',
                        fontSize:'18px', fontWeight:'800', margin:'0 auto 10px',
                        background: i===0?'#FEF3C7':i===1?'#F1F5F9':i===2?'#FEE2E2':'#F0FDF4',
                        color: i===0?'#92400E':i===1?'#475569':i===2?'#991B1B':'#166534',
                    }">{{ i+1 }}</div>
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ p.descripcion }}</p>
                    <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">{{ Number(p.total_cantidad).toFixed(0) }} uds</p>
                    <p style="font-size:14px; font-weight:800; color:#14B8A6; margin:0;">S/ {{ Number(p.total_monto).toFixed(0) }}</p>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    vencidos_count:   { type: Number, default: 0 },
    por_vencer_count: { type: Number, default: 0 },
    industry_name:  { type: String, default: 'Farmacia' },
    stats:          { type: Object, default: () => ({}) },
    ventas_por_dia: { type: Array,  default: () => [] },
    top_productos:  { type: Array,  default: () => [] },
    ultimas_ventas: { type: Array,  default: () => [] },
})

const maxDia = computed(() => {
    if (!props.ventas_por_dia.length) return 1
    return Math.max(...props.ventas_por_dia.map(d => d.total)) || 1
})

const formatFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}
</script>