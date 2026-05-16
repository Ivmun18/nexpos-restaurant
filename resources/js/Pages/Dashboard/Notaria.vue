<template>
    <AppLayout title="Dashboard" :subtitle="`Resumen de ${props.industry_name}`">

        <!-- KPIs PRINCIPALES -->
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">

            <div style="background:linear-gradient(135deg,#7C3AED,#5B21B6); border-radius:20px; padding:24px; color:white; box-shadow:0 8px 24px rgba(124,58,237,0.3);">
                <p style="font-size:13px; font-weight:600; opacity:0.8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">📋 Actos hoy</p>
                <p style="font-size:36px; font-weight:800; margin:0; line-height:1;">{{ stats.actos_hoy }}</p>
                <p style="font-size:13px; opacity:0.7; margin:10px 0 0;">Ingresados hoy</p>
            </div>

            <div style="background:linear-gradient(135deg,#F59E0B,#D97706); border-radius:20px; padding:24px; color:white; box-shadow:0 8px 24px rgba(245,158,11,0.3);">
                <p style="font-size:13px; font-weight:600; opacity:0.8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">⏳ Pendientes</p>
                <p style="font-size:36px; font-weight:800; margin:0; line-height:1;">{{ stats.actos_pendientes }}</p>
                <p style="font-size:13px; opacity:0.7; margin:10px 0 0;">Por entregar</p>
            </div>

            <div style="background:linear-gradient(135deg,#10B981,#059669); border-radius:20px; padding:24px; color:white; box-shadow:0 8px 24px rgba(16,185,129,0.3);">
                <p style="font-size:13px; font-weight:600; opacity:0.8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">💰 Ingresos del mes</p>
                <p style="font-size:36px; font-weight:800; margin:0; line-height:1;">S/ {{ Number(stats.ingresos_mes).toFixed(2) }}</p>
                <p style="font-size:13px; opacity:0.7; margin:10px 0 0;">S/ {{ Number(stats.ingresos_hoy).toFixed(2) }} hoy</p>
            </div>

            <div :style="{
                background: stats.por_cobrar > 0 ? 'linear-gradient(135deg,#EF4444,#DC2626)' : 'white',
                borderRadius: '20px', padding: '24px',
                color: stats.por_cobrar > 0 ? 'white' : '#1E293B',
                border: stats.por_cobrar > 0 ? 'none' : '1px solid #E2E8F0',
                boxShadow: stats.por_cobrar > 0 ? '0 8px 24px rgba(239,68,68,0.3)' : '0 4px 12px rgba(0,0,0,0.06)',
            }">
                <p :style="{ fontSize:'13px', fontWeight:'600', opacity: stats.por_cobrar > 0 ? '0.8':'1', color: stats.por_cobrar > 0 ? 'white':'#94A3B8', margin:'0 0 12px', textTransform:'uppercase', letterSpacing:'1px' }">💵 Por cobrar</p>
                <p style="font-size:36px; font-weight:800; margin:0; line-height:1;">S/ {{ Number(stats.por_cobrar).toFixed(2) }}</p>
                <p :style="{ fontSize:'13px', opacity: stats.por_cobrar > 0 ? '0.7':'1', color: stats.por_cobrar > 0 ? 'white':'#94A3B8', margin:'10px 0 0' }">{{ stats.actos_por_cobrar }} actos con saldo</p>
            </div>
        </div>

        <!-- GRAFICO + TOP TIPOS -->
        <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:24px;">

            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px;">📊 Actos por día (últimos 7 días)</h3>
                <div style="display:flex; align-items:flex-end; justify-content:space-between; height:200px; gap:8px;">
                    <div v-for="(d, i) in actos_por_dia" :key="i" style="flex:1; display:flex; flex-direction:column; align-items:center; gap:8px;">
                        <div style="flex:1; width:100%; display:flex; align-items:flex-end;">
                            <div :style="{
                                width: '100%',
                                height: maxActos > 0 ? `${(d.total / maxActos) * 100}%` : '0%',
                                background: 'linear-gradient(180deg, #7C3AED, #5B21B6)',
                                borderRadius: '8px 8px 0 0',
                                minHeight: d.total > 0 ? '6px' : '0',
                                transition: 'height 0.3s',
                            }" :title="`${d.total} actos`"></div>
                        </div>
                        <p style="font-size:11px; color:#64748B; margin:0; font-weight:600;">{{ d.dia }}</p>
                        <p style="font-size:13px; color:#1E293B; margin:0; font-weight:700;">{{ d.total }}</p>
                    </div>
                </div>
            </div>

            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px;">🔥 Tipos de acto más solicitados</h3>
                <div v-if="top_tipos.length === 0" style="text-align:center; color:#94A3B8; font-size:13px; padding:40px 0;">
                    Sin datos aún
                </div>
                <div v-else style="display:flex; flex-direction:column; gap:12px;">
                    <div v-for="(t, i) in top_tipos" :key="i" style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; background:#F8FAFC; border-radius:10px;">
                        <div style="flex:1; min-width:0;">
                            <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0; text-transform:capitalize; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ t.tipo_acto }}</p>
                            <p style="font-size:11px; color:#64748B; margin:2px 0 0;">{{ t.total_cantidad }} actos</p>
                        </div>
                        <p style="font-size:13px; font-weight:700; color:#7C3AED; margin:0;">S/ {{ Number(t.total_monto).toFixed(2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ULTIMOS ACTOS -->
        <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
            <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px;">📂 Últimos actos registrados</h3>
            <div v-if="ultimos_actos.length === 0" style="text-align:center; color:#94A3B8; font-size:13px; padding:40px 0;">
                No hay actos registrados aún
            </div>
            <div v-else style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid #E2E8F0;">
                            <th style="text-align:left; padding:10px; font-size:12px; color:#64748B; text-transform:uppercase; letter-spacing:0.5px;">Expediente</th>
                            <th style="text-align:left; padding:10px; font-size:12px; color:#64748B; text-transform:uppercase; letter-spacing:0.5px;">Cliente</th>
                            <th style="text-align:left; padding:10px; font-size:12px; color:#64748B; text-transform:uppercase; letter-spacing:0.5px;">Tipo</th>
                            <th style="text-align:left; padding:10px; font-size:12px; color:#64748B; text-transform:uppercase; letter-spacing:0.5px;">Estado</th>
                            <th style="text-align:right; padding:10px; font-size:12px; color:#64748B; text-transform:uppercase; letter-spacing:0.5px;">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="a in ultimos_actos" :key="a.id" style="border-bottom:1px solid #F1F5F9;">
                            <td style="padding:12px 10px; font-size:13px; font-weight:600; color:#1E293B;">{{ a.numero_expediente }}</td>
                            <td style="padding:12px 10px; font-size:13px; color:#475569;">{{ a.cliente?.nombre_completo || a.cliente?.razon_social || '—' }}</td>
                            <td style="padding:12px 10px; font-size:13px; color:#475569; text-transform:capitalize;">{{ a.tipo_acto }}</td>
                            <td style="padding:12px 10px;">
                                <span :style="{
                                    fontSize: '11px',
                                    fontWeight: '600',
                                    padding: '4px 10px',
                                    borderRadius: '999px',
                                    background: estadoColor(a.estado).bg,
                                    color: estadoColor(a.estado).color,
                                    textTransform: 'capitalize',
                                }">{{ a.estado }}</span>
                            </td>
                            <td style="padding:12px 10px; font-size:13px; font-weight:700; color:#1E293B; text-align:right;">S/ {{ Number(a.monto_cobrar).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    industry_name:  { type: String, default: 'Notaría' },
    stats: {
        type: Object,
        default: () => ({
            actos_hoy: 0,
            actos_mes: 0,
            actos_pendientes: 0,
            actos_total: 0,
            ingresos_hoy: 0,
            ingresos_mes: 0,
            por_cobrar: 0,
            actos_por_cobrar: 0,
        }),
    },
    actos_por_dia: { type: Array, default: () => [] },
    top_tipos:     { type: Array, default: () => [] },
    ultimos_actos: { type: Array, default: () => [] },
})

const maxActos = computed(() => Math.max(...props.actos_por_dia.map(d => d.total), 1))

function estadoColor(estado) {
    const map = {
        pendiente:  { bg: '#FEF3C7', color: '#92400E' },
        en_tramite: { bg: '#DBEAFE', color: '#1E40AF' },
        listo:      { bg: '#D1FAE5', color: '#065F46' },
        entregado:  { bg: '#E0E7FF', color: '#3730A3' },
        concluido:  { bg: '#D1FAE5', color: '#065F46' },
        anulado:    { bg: '#FEE2E2', color: '#991B1B' },
    }
    return map[estado] || { bg: '#F1F5F9', color: '#475569' }
}
</script>
