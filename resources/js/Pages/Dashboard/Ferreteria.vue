<template>
    <AppLayout title="Dashboard" :subtitle="`Bienvenido, ${$page.props.auth.user.name}`">

        <!-- Stats principales -->
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
            <div style="background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:20px; padding:24px; color:white;">
                <p style="font-size:13px; font-weight:600; opacity:0.8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">💰 Ventas Hoy</p>
                <p style="font-size:36px; font-weight:800; margin:0; line-height:1;">S/ {{ Number(stats.ventas_hoy).toFixed(2) }}</p>
                <p style="font-size:13px; opacity:0.7; margin:10px 0 0;">{{ stats.ventas_hoy_count }} transacciones</p>
            </div>
            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0;">
                <p style="font-size:13px; font-weight:600; color:#94A3B8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">📅 Ventas del Mes</p>
                <p style="font-size:36px; font-weight:800; color:#1E293B; margin:0; line-height:1;">S/ {{ Number(stats.ventas_mes).toFixed(0) }}</p>
                <p style="font-size:13px; color:#94A3B8; margin:10px 0 0;">Este mes</p>
            </div>
            <div :style="{background: stats.stock_bajo > 0 ? 'linear-gradient(135deg,#EF4444,#DC2626)' : 'white', borderRadius:'20px', padding:'24px', color: stats.stock_bajo > 0 ? 'white' : '#1E293B', border: stats.stock_bajo > 0 ? 'none' : '1px solid #E2E8F0'}">
                <p :style="{fontSize:'13px', fontWeight:'600', opacity: stats.stock_bajo > 0 ? '0.8' : '1', color: stats.stock_bajo > 0 ? 'white' : '#94A3B8', margin:'0 0 12px', textTransform:'uppercase', letterSpacing:'1px'}">⚠️ Stock Bajo</p>
                <p style="font-size:36px; font-weight:800; margin:0; line-height:1;">{{ stats.stock_bajo }}</p>
                <p :style="{fontSize:'13px', opacity: stats.stock_bajo > 0 ? '0.7' : '1', color: stats.stock_bajo > 0 ? 'white' : '#94A3B8', margin:'10px 0 0'}">Productos críticos</p>
            </div>
            <div :style="{background: stats.ordenes_pendientes > 0 ? 'linear-gradient(135deg,#F59E0B,#D97706)' : 'white', borderRadius:'20px', padding:'24px', color: stats.ordenes_pendientes > 0 ? 'white' : '#1E293B', border: stats.ordenes_pendientes > 0 ? 'none' : '1px solid #E2E8F0'}">
                <p :style="{fontSize:'13px', fontWeight:'600', opacity: stats.ordenes_pendientes > 0 ? '0.8' : '1', color: stats.ordenes_pendientes > 0 ? 'white' : '#94A3B8', margin:'0 0 12px', textTransform:'uppercase', letterSpacing:'1px'}">🔧 Órdenes Pendientes</p>
                <p style="font-size:36px; font-weight:800; margin:0; line-height:1;">{{ stats.ordenes_pendientes }}</p>
                <p :style="{fontSize:'13px', opacity: stats.ordenes_pendientes > 0 ? '0.7' : '1', color: stats.ordenes_pendientes > 0 ? 'white' : '#94A3B8', margin:'10px 0 0'}">Por atender</p>
            </div>
        </div>

        <!-- Gráfico ventas + Cotizaciones -->
        <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:24px;">
            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0;">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 24px;">📈 Ventas últimos 7 días</p>
                <div style="display:flex; align-items:flex-end; gap:12px; height:180px;">
                    <div v-for="(dia, i) in ventas_por_dia" :key="i" style="flex:1; display:flex; flex-direction:column; align-items:center; gap:8px;">
                        <span style="font-size:12px; font-weight:600; color:#475569;">S/ {{ Number(dia.total).toFixed(0) }}</span>
                        <div :style="{
                            width: '100%',
                            borderRadius: '8px 8px 0 0',
                            background: 'linear-gradient(180deg,#14B8A6,#0F766E)',
                            height: maxVenta > 0 ? Math.max((dia.total / maxVenta) * 150, 4) + 'px' : '4px',
                            minHeight: '4px'
                        }"></div>
                        <span style="font-size:11px; color:#94A3B8;">{{ dia.dia }}</span>
                    </div>
                </div>
            </div>

            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0;">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 20px;">📋 Cotizaciones</p>
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; background:#F8FAFC; border-radius:10px;">
                        <span style="font-size:13px; color:#64748B;">Total</span>
                        <span style="font-size:18px; font-weight:700; color:#1E293B;">{{ stats.cotizaciones_total }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; background:#F0FDF4; border-radius:10px;">
                        <span style="font-size:13px; color:#166534;">Aprobadas</span>
                        <span style="font-size:18px; font-weight:700; color:#16A34A;">{{ stats.cotizaciones_aprobadas }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; background:#FEF3C7; border-radius:10px;">
                        <span style="font-size:13px; color:#92400E;">Pendientes</span>
                        <span style="font-size:18px; font-weight:700; color:#F59E0B;">{{ stats.cotizaciones_enviadas }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; background:#F0FDFA; border-radius:10px;">
                        <span style="font-size:13px; color:#0F766E;">Monto aprobado</span>
                        <span style="font-size:16px; font-weight:700; color:#14B8A6;">S/ {{ Number(stats.cotizaciones_monto).toFixed(2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top productos + Órdenes recientes -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0;">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 20px;">🏆 Top Productos</p>
                <div v-if="top_productos.length === 0" style="text-align:center; padding:20px; color:#94A3B8;">Sin ventas aún</div>
                <div v-for="(p, i) in top_productos" :key="i" style="display:flex; align-items:center; gap:12px; margin-bottom:14px;">
                    <div :style="{width:'32px', height:'32px', borderRadius:'10px', background:'linear-gradient(135deg,#14B8A6,#0F766E)', display:'flex', alignItems:'center', justifyContent:'center', color:'white', fontWeight:'800', fontSize:'14px', flexShrink:0}">{{ i+1 }}</div>
                    <div style="flex:1; min-width:0;">
                        <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ p.descripcion }}</p>
                        <p style="font-size:12px; color:#94A3B8; margin:0;">{{ p.total_cantidad }} unidades</p>
                    </div>
                    <span style="font-size:13px; font-weight:700; color:#14B8A6; flex-shrink:0;">S/ {{ Number(p.total_monto).toFixed(2) }}</span>
                </div>
            </div>

            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0;">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 20px;">🔧 Órdenes Recientes</p>
                <div v-if="ordenes_recientes.length === 0" style="text-align:center; padding:20px; color:#94A3B8;">Sin órdenes aún</div>
                <div v-for="o in ordenes_recientes" :key="o.id" style="display:flex; align-items:center; gap:12px; margin-bottom:14px; padding-bottom:14px; border-bottom:1px solid #F1F5F9;">
                    <div style="flex:1; min-width:0;">
                        <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ o.titulo }}</p>
                        <p style="font-size:12px; color:#94A3B8; margin:0;">{{ o.cliente_nombre }}</p>
                    </div>
                    <span :style="{padding:'3px 8px', borderRadius:'20px', fontSize:'11px', fontWeight:'700', background: o.estado==='completada' ? '#F0FDF4' : o.estado==='en_proceso' ? '#EFF6FF' : '#FEF3C7', color: o.estado==='completada' ? '#166534' : o.estado==='en_proceso' ? '#1D4ED8' : '#92400E'}">{{ o.estado }}</span>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    industry_name:    { type: String, default: 'Ferretería' },
    stats:            { type: Object, default: () => ({}) },
    ventas_por_dia:   { type: Array,  default: () => [] },
    top_productos:    { type: Array,  default: () => [] },
    ordenes_recientes:{ type: Array,  default: () => [] },
})

const maxVenta = computed(() => Math.max(...props.ventas_por_dia.map(d => d.total), 1))
</script>
