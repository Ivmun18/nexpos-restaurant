<template>
    <AppLayout :title="`Dashboard ${industry_name}`">
        <div style="padding:24px; max-width:1400px; margin:0 auto;">

            <!-- Header -->
            <div style="margin-bottom:24px;">
                <h1 style="font-size:22px; font-weight:700; color:#1E293B; margin:0 0 4px;">Dashboard {{ industry_name }}</h1>
                <p style="font-size:13px; color:#94A3B8; margin:0;">{{ fechaHoy }}</p>
            </div>

            <!-- KPIs fila 1 -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:12px; margin-bottom:12px;">

                <!-- Actos hoy -->
                <div style="background:white; border:1px solid #E0F2FE; border-radius:14px; padding:1.1rem 1.25rem;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                        <div style="width:36px; height:36px; background:#EFF6FF; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                            <svg width="18" height="18" fill="none" stroke="#3B82F6" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        </div>
                        <span style="font-size:12px; font-weight:600; color:#64748B; text-transform:uppercase; letter-spacing:.5px;">Actos hoy</span>
                    </div>
                    <p style="font-size:32px; font-weight:800; color:#3B82F6; margin:0; line-height:1;">{{ stats.actos_hoy || 0 }}</p>
                </div>

                <!-- Ingresos hoy -->
                <div style="background:white; border:1px solid #D1FAE5; border-radius:14px; padding:1.1rem 1.25rem;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                        <div style="width:36px; height:36px; background:#ECFDF5; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                            <svg width="18" height="18" fill="none" stroke="#10B981" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </div>
                        <span style="font-size:12px; font-weight:600; color:#64748B; text-transform:uppercase; letter-spacing:.5px;">Ingresos hoy</span>
                    </div>
                    <p style="font-size:24px; font-weight:800; color:#10B981; margin:0 0 4px; line-height:1;">S/ {{ Number(stats.ingresos_hoy || 0).toFixed(2) }}</p>
                    <p style="font-size:11px; color:#6EE7B7; margin:0;">{{ stats.ingresos_hoy_count || 0 }} transacciones · solo contado</p>
                </div>

                <!-- Mes actual -->
                <div style="background:white; border:1px solid #E2E8F0; border-radius:14px; padding:1.1rem 1.25rem;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                        <div style="width:36px; height:36px; background:#F8FAFC; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                            <svg width="18" height="18" fill="none" stroke="#64748B" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                        <span style="font-size:12px; font-weight:600; color:#64748B; text-transform:uppercase; letter-spacing:.5px;">Mes actual</span>
                    </div>
                    <p style="font-size:24px; font-weight:800; color:#1E293B; margin:0; line-height:1;">S/ {{ Number(stats.ingresos_mes || 0).toFixed(2) }}</p>
                </div>

                <!-- En proceso -->
                <div style="background:white; border:1px solid #FEF3C7; border-radius:14px; padding:1.1rem 1.25rem;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                        <div style="width:36px; height:36px; background:#FFFBEB; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                            <svg width="18" height="18" fill="none" stroke="#F59E0B" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <span style="font-size:12px; font-weight:600; color:#64748B; text-transform:uppercase; letter-spacing:.5px;">En proceso</span>
                    </div>
                    <p style="font-size:32px; font-weight:800; color:#F59E0B; margin:0; line-height:1;">{{ stats.actos_proceso || 0 }}</p>
                </div>
            </div>

            <!-- Card créditos -->
            <div style="background:white; border:1px solid #E0E7FF; border-radius:14px; padding:1.1rem 1.5rem; margin-bottom:16px; display:flex; align-items:center; gap:24px; flex-wrap:wrap;">
                <div style="width:40px; height:40px; background:#EEF2FF; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="20" height="20" fill="none" stroke="#6366F1" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <div style="flex:1;">
                    <p style="font-size:12px; font-weight:600; color:#64748B; text-transform:uppercase; letter-spacing:.5px; margin:0 0 2px;">Cuentas por cobrar</p>
                    <p style="font-size:11px; color:#94A3B8; margin:0;">{{ creditos.facturas_activas || 0 }} factura(s) a crédito con cuotas pendientes</p>
                </div>
                <div style="text-align:center; padding:0 16px; border-left:1px solid #E2E8F0;">
                    <p style="font-size:11px; color:#94A3B8; margin:0 0 2px; text-transform:uppercase; font-weight:600;">Total pendiente</p>
                    <p style="font-size:22px; font-weight:800; color:#6366F1; margin:0;">S/ {{ Number(creditos.total_por_cobrar || 0).toFixed(2) }}</p>
                </div>
                <div v-if="creditos.proxima_fecha" style="text-align:center; padding:0 16px; border-left:1px solid #E2E8F0;">
                    <p style="font-size:11px; color:#94A3B8; margin:0 0 2px; text-transform:uppercase; font-weight:600;">Próximo vencimiento</p>
                    <p style="font-size:14px; font-weight:700; margin:0;" :style="{ color: vencimientoProximo ? '#EF4444' : '#F59E0B' }">
                        {{ creditos.proxima_fecha }} · S/ {{ Number(creditos.proxima_monto || 0).toFixed(2) }}
                    </p>
                </div>
                <a href="/notaria/cuentas-cobrar" style="padding:10px 18px; background:#6366F1; color:white; border:none; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; text-decoration:none; white-space:nowrap;">
                    Ver cuentas →
                </a>
            </div>

            <!-- Gráfico -->
            <div style="background:white; border:1px solid #E2E8F0; border-radius:14px; padding:1.25rem 1.5rem; margin-bottom:16px;">
                <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 1.25rem;">Ingresos últimos 7 días</p>
                <div style="display:flex; align-items:flex-end; gap:10px; height:160px;">
                    <div v-for="(d, i) in ingresos_por_dia" :key="i" style="flex:1; display:flex; flex-direction:column; align-items:center; gap:6px;">
                        <span style="font-size:10px; color:#94A3B8; font-weight:600;">{{ d.total > 0 ? 'S/'+Number(d.total).toLocaleString() : '' }}</span>
                        <div :style="{
                            width: '100%',
                            background: i === ingresos_por_dia.length - 1 ? '#10B981' : '#D1FAE5',
                            borderRadius: '6px 6px 0 0',
                            height: d.total > 0 ? Math.max((d.total / maxDia * 120), 6) + 'px' : '3px',
                            transition: 'all 0.4s ease',
                        }"></div>
                        <span :style="{ fontSize:'11px', fontWeight: i === ingresos_por_dia.length-1 ? '700' : '500', color: i === ingresos_por_dia.length-1 ? '#10B981' : '#94A3B8' }">{{ d.dia }}</span>
                    </div>
                </div>
            </div>

            <!-- Bottom grid -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

                <!-- Top actos -->
                <div style="background:white; border:1px solid #E2E8F0; border-radius:14px; padding:1.25rem 1.5rem;">
                    <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 1rem;">Top actos notariales</p>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <div v-for="(acto, i) in top_actos" :key="i"
                            style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; background:#F8FAFC; border-radius:10px;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span :style="{
                                    width:'24px', height:'24px', borderRadius:'50%', display:'flex', alignItems:'center', justifyContent:'center',
                                    fontSize:'11px', fontWeight:'800',
                                    background: i===0?'#FEF3C7':i===1?'#F1F5F9':'#FEE2E2',
                                    color: i===0?'#92400E':i===1?'#475569':'#991B1B',
                                }">{{ i+1 }}</span>
                                <div>
                                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0;">{{ acto.tipo_acto_label }}</p>
                                    <p style="font-size:11px; color:#94A3B8; margin:0;">{{ acto.total_cantidad }} actos</p>
                                </div>
                            </div>
                            <span style="font-size:14px; font-weight:800; color:#10B981;">S/ {{ Number(acto.total_monto).toLocaleString() }}</span>
                        </div>
                        <div v-if="!top_actos.length" style="text-align:center; padding:2rem; color:#94A3B8; font-size:13px;">Sin datos este período</div>
                    </div>
                </div>

                <!-- Últimos actos -->
                <div style="background:white; border:1px solid #E2E8F0; border-radius:14px; padding:1.25rem 1.5rem;">
                    <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 1rem;">Últimos actos registrados</p>
                    <div style="display:flex; flex-direction:column; gap:2px;">
                        <div v-for="acto in ultimos_actos" :key="acto.id"
                            style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #F1F5F9;">
                            <div>
                                <p style="font-size:12px; font-weight:700; color:#1E293B; margin:0;">{{ acto.numero_expediente }}</p>
                                <p style="font-size:11px; color:#94A3B8; margin:2px 0 0;">{{ acto.tipo_acto_label || acto.tipo_acto }} · {{ acto.cliente_nombre || '—' }}</p>
                            </div>
                            <div style="text-align:right;">
                                <span :style="{
                                    fontSize:'10px', padding:'2px 8px', borderRadius:'20px', fontWeight:'600', display:'block', marginBottom:'4px',
                                    background: acto.estado==='firmado'?'#D1FAE5':acto.estado==='proceso'?'#DBEAFE':acto.estado==='solicitado'?'#FEF3C7':'#F1F5F9',
                                    color: acto.estado==='firmado'?'#065F46':acto.estado==='proceso'?'#1E40AF':acto.estado==='solicitado'?'#92400E':'#475569',
                                }">{{ acto.estado }}</span>
                                <p style="font-size:12px; font-weight:700; color:#10B981; margin:0;">S/ {{ Number(acto.monto_cobrar || 0).toFixed(2) }}</p>
                            </div>
                        </div>
                        <div v-if="!ultimos_actos.length" style="text-align:center; padding:2rem; color:#94A3B8; font-size:13px;">Sin actos recientes</div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    industry_name:    { type: String, default: 'Notaría' },
    stats:            { type: Object, default: () => ({}) },
    creditos:         { type: Object, default: () => ({}) },
    ingresos_por_dia: { type: Array,  default: () => [] },
    top_actos:        { type: Array,  default: () => [] },
    ultimos_actos:    { type: Array,  default: () => [] },
})

const maxDia = computed(() => {
    if (!props.ingresos_por_dia.length) return 1
    return Math.max(...props.ingresos_por_dia.map(d => d.total)) || 1
})

const fechaHoy = computed(() => {
    return new Date().toLocaleDateString('es-PE', { weekday:'long', day:'numeric', month:'long', year:'numeric' })
})

const vencimientoProximo = computed(() => {
    if (!props.creditos.proxima_fecha) return false
    const diff = (new Date(props.creditos.proxima_fecha) - new Date()) / (1000 * 60 * 60 * 24)
    return diff <= 3
})
</script>
