<template>
    <AppLayout>
        <div style="padding:24px; max-width:1200px; margin:0 auto;">
            <h1 style="font-size:22px; font-weight:700; color:#1E293B; margin:0 0 6px;">💪 Dashboard Gimnasio</h1>
            <p style="color:#64748B; margin:0 0 24px;">Resumen del día</p>

            <!-- KPIs -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px; margin-bottom:28px;">
                <div v-for="kpi in kpis" :key="kpi.label" :style="{ background: kpi.bg, borderRadius:'14px', padding:'20px', border:'1px solid ' + kpi.border }">
                    <p style="font-size:11px; font-weight:700; letter-spacing:1px; margin:0 0 8px;" :style="{ color: kpi.color }">{{ kpi.label }}</p>
                    <p style="font-size:28px; font-weight:800; margin:0;" :style="{ color: kpi.color }">{{ kpi.valor }}</p>
                </div>
            </div>

            <!-- Por vencer -->
            <div v-if="vencen_pronto.length" style="background:#FFFBEB; border:1px solid #FCD34D; border-radius:14px; padding:20px;">
                <p style="font-weight:700; color:#92400E; margin:0 0 12px;">⚠️ Membresías por vencer (próximos 7 días)</p>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="font-size:12px; color:#92400E; text-align:left;">
                            <th style="padding:6px 12px;">Miembro</th>
                            <th style="padding:6px 12px;">Plan</th>
                            <th style="padding:6px 12px;">Vence</th>
                            <th style="padding:6px 12px;">Días</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="m in vencen_pronto" :key="m.id" style="border-top:1px solid #FDE68A;">
                            <td style="padding:8px 12px; font-weight:600; color:#1E293B;">{{ m.nombre }} {{ m.apellidos }}</td>
                            <td style="padding:8px 12px; color:#64748B;">{{ m.plan?.nombre }}</td>
                            <td style="padding:8px 12px; color:#64748B;">{{ formatFecha(m.membrecia_vencimiento) }}</td>
                            <td style="padding:8px 12px;">
                                <span :style="{ background: diasRestantes(m) <= 2 ? '#FEE2E2' : '#FEF3C7', color: diasRestantes(m) <= 2 ? '#991B1B' : '#92400E', padding:'2px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'700' }">
                                    {{ diasRestantes(m) }} día(s)
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ stats: Object, vencen_pronto: Array })

const kpis = [
    { label: 'TOTAL MIEMBROS',  valor: props.stats.total_miembros, bg:'#EFF6FF', border:'#BFDBFE', color:'#1D4ED8' },
    { label: 'ACTIVOS',         valor: props.stats.activos,         bg:'#F0FDF4', border:'#BBF7D0', color:'#166534' },
    { label: 'VENCIDOS',        valor: props.stats.vencidos,        bg:'#FEF2F2', border:'#FECACA', color:'#991B1B' },
    { label: 'POR VENCER',      valor: props.stats.por_vencer,      bg:'#FFFBEB', border:'#FCD34D', color:'#92400E' },
    { label: 'INGRESOS MES',    valor: 'S/ ' + Number(props.stats.ingresos_mes).toFixed(2), bg:'#F5F3FF', border:'#DDD6FE', color:'#6D28D9' },
    { label: 'ACCESOS HOY',     valor: props.stats.accesos_hoy,     bg:'#ECFDF5', border:'#6EE7B7', color:'#065F46' },
]

const formatFecha = (f) => f ? new Date(f).toLocaleDateString('es-PE') : '-'
const diasRestantes = (m) => m.membrecia_vencimiento
    ? Math.ceil((new Date(m.membrecia_vencimiento) - new Date()) / 86400000)
    : 0
</script>
