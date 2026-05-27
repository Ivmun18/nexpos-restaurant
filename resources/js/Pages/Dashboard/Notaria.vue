<template>
    <AppLayout :title="`Dashboard ${industry_name}`">
        <div style="padding:30px; max-width:1400px; margin:0 auto;">
            
            <!-- Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
                <div>
                    <h1 style="font-size:28px; font-weight:800; color:#1E293B; margin:0 0 6px;">Dashboard {{ industry_name }}</h1>
                    <p style="font-size:14px; color:#64748B; margin:0;">Resumen de operaciones notariales</p>
                </div>
            </div>

            <!-- KPIs -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:20px; margin-bottom:30px;">
                <div style="background:#FFFFFF; padding:24px; border-radius:16px; border:1px solid #E2E8F0;">
                    <div style="display:flex; align-items:center; gap:14px; margin-bottom:14px;">
                        <div style="width:48px; height:48px; background:#ECFDF5; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px;">📄</div>
                        <div>
                            <p style="font-size:13px; color:#64748B; margin:0 0 4px; font-weight:500;">Actos Hoy</p>
                            <p style="font-size:36px; font-weight:800; margin:0; line-height:1; color:#14B8A6;">{{ stats.actos_hoy || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div style="background:#FFFFFF; padding:24px; border-radius:16px; border:1px solid #E2E8F0;">
                    <div style="display:flex; align-items:center; gap:14px; margin-bottom:14px;">
                        <div style="width:48px; height:48px; background:#FEF3C7; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px;">💰</div>
                        <div>
                            <p style="font-size:13px; color:#64748B; margin:0 0 4px; font-weight:500;">Ingresos Hoy</p>
                            <p style="font-size:36px; font-weight:800; margin:0; line-height:1; color:#14B8A6;">S/ {{ Number(stats.ingresos_hoy || 0).toFixed(2) }}</p>
                        </div>
                    </div>
                    <p style="font-size:13px; opacity:0.7; margin:10px 0 0;">{{ stats.ingresos_hoy_count || 0 }} transacciones</p>
                </div>

                <div style="background:#FFFFFF; padding:24px; border-radius:16px; border:1px solid #E2E8F0;">
                    <div style="display:flex; align-items:center; gap:14px; margin-bottom:14px;">
                        <div style="width:48px; height:48px; background:#DBEAFE; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px;">📅</div>
                        <div>
                            <p style="font-size:13px; color:#64748B; margin:0 0 4px; font-weight:500;">Mes Actual</p>
                            <p style="font-size:36px; font-weight:800; margin:0; line-height:1; color:#14B8A6;">S/ {{ Number(stats.ingresos_mes || 0).toFixed(2) }}</p>
                        </div>
                    </div>
                </div>

                <div style="background:#FFFFFF; padding:24px; border-radius:16px; border:1px solid #E2E8F0;">
                    <div style="display:flex; align-items:center; gap:14px; margin-bottom:14px;">
                        <div style="width:48px; height:48px; background:#FEE2E2; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px;">⏳</div>
                        <div>
                            <p style="font-size:13px; color:#64748B; margin:0 0 4px; font-weight:500;">En Proceso</p>
                            <p style="font-size:36px; font-weight:800; margin:0; line-height:1; color:#14B8A6;">{{ stats.actos_proceso || 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico de ingresos por día -->
            <div style="background:#FFFFFF; padding:30px; border-radius:16px; border:1px solid #E2E8F0; margin-bottom:30px;">
                <h2 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 24px;">Ingresos últimos 7 días</h2>
                <div style="display:flex; align-items:flex-end; gap:12px; height:220px;">
                    <div v-for="d in ingresos_por_dia" :key="d.dia" style="flex:1; display:flex; flex-direction:column; align-items:center; gap:8px;">
                        <p style="font-size:12px; font-weight:600; color:#14B8A6; margin:0;">S/ {{ d.total.toFixed(0) }}</p>
                        <div :style="{
                            width:'100%', background:'linear-gradient(180deg, #14B8A6 0%, #0D9488 100%)',
                            borderRadius:'8px 8px 0 0',
                            height: (d.total / maxDia * 100) + '%',
                            minHeight:'6px',
                            transition:'all 0.3s'
                        }"></div>
                        <p style="font-size:11px; color:#94A3B8; margin:0; font-weight:600;">{{ d.dia }}</p>
                    </div>
                </div>
            </div>

            <!-- Top actos notariales -->
            <div style="background:#FFFFFF; padding:30px; border-radius:16px; border:1px solid #E2E8F0; margin-bottom:30px;">
                <h2 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 20px;">Top Actos Notariales</h2>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px;">
                    <div v-for="(acto, i) in top_actos" :key="i"
                        style="text-align:center; padding:16px; border:1px solid #E2E8F0; border-radius:14px;">
                        <div :style="{
                            width:'40px', height:'40px', borderRadius:'12px', display:'flex', alignItems:'center', justifyContent:'center',
                            fontSize:'18px', fontWeight:'800', margin:'0 auto 10px',
                            background: i===0?'#FEF3C7':i===1?'#F1F5F9':i===2?'#FEE2E2':'#F0FDF4',
                            color: i===0?'#92400E':i===1?'#475569':i===2?'#991B1B':'#166534',
                        }">{{ i+1 }}</div>
                        <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ acto.tipo_acto_label }}</p>
                        <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">{{ Number(acto.total_cantidad).toFixed(0) }} actos</p>
                        <p style="font-size:14px; font-weight:800; color:#14B8A6; margin:0;">S/ {{ Number(acto.total_monto).toFixed(0) }}</p>
                    </div>
                </div>
            </div>

            <!-- Últimos actos notariales -->
            <div style="background:#FFFFFF; padding:30px; border-radius:16px; border:1px solid #E2E8F0;">
                <h2 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 20px;">Últimos Actos Registrados</h2>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="border-bottom:2px solid #E2E8F0;">
                                <th style="text-align:left; padding:12px 8px; font-size:12px; font-weight:600; color:#64748B; text-transform:uppercase;">Expediente</th>
                                <th style="text-align:left; padding:12px 8px; font-size:12px; font-weight:600; color:#64748B; text-transform:uppercase;">Cliente</th>
                                <th style="text-align:left; padding:12px 8px; font-size:12px; font-weight:600; color:#64748B; text-transform:uppercase;">Tipo</th>
                                <th style="text-align:left; padding:12px 8px; font-size:12px; font-weight:600; color:#64748B; text-transform:uppercase;">Estado</th>
                                <th style="text-align:right; padding:12px 8px; font-size:12px; font-weight:600; color:#64748B; text-transform:uppercase;">Monto</th>
                                <th style="text-align:right; padding:12px 8px; font-size:12px; font-weight:600; color:#64748B; text-transform:uppercase;">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="acto in ultimos_actos" :key="acto.id" style="border-bottom:1px solid #F1F5F9;">
                                <td style="padding:14px 8px; font-size:13px; font-weight:600; color:#1E293B;">{{ acto.numero_expediente }}</td>
                                <td style="padding:14px 8px; font-size:13px; color:#64748B;">{{ acto.cliente?.nombre || '-' }}</td>
                                <td style="padding:14px 8px; font-size:13px; color:#64748B;">{{ acto.tipo_acto_label || '-' }}</td>
                                <td style="padding:14px 8px;">
                                    <span :style="{
                                        padding:'4px 10px', borderRadius:'6px', fontSize:'11px', fontWeight:'600',
                                        background: acto.estado==='solicitado'?'#FEF3C7':acto.estado==='proceso'?'#DBEAFE':acto.estado==='firmado'?'#D1FAE5':'#F1F5F9',
                                        color: acto.estado==='solicitado'?'#92400E':acto.estado==='proceso'?'#1E40AF':acto.estado==='firmado'?'#065F46':'#475569',
                                    }">{{ acto.estado }}</span>
                                </td>
                                <td style="padding:14px 8px; text-align:right; font-size:14px; font-weight:700; color:#14B8A6;">S/ {{ Number(acto.monto_total || 0).toFixed(2) }}</td>
                                <td style="padding:14px 8px; text-align:right; font-size:13px; color:#94A3B8;">{{ formatFecha(acto.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    industry_name:     { type: String, default: 'Notaría' },
    stats:             { type: Object, default: () => ({}) },
    ingresos_por_dia:  { type: Array,  default: () => [] },
    top_actos:         { type: Array,  default: () => [] },
    ultimos_actos:     { type: Array,  default: () => [] },
})

const maxDia = computed(() => {
    if (!props.ingresos_por_dia.length) return 1
    return Math.max(...props.ingresos_por_dia.map(d => d.total)) || 1
})

const formatFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}
</script>
