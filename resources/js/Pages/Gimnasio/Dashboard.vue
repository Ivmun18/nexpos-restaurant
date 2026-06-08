<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    stats: Object,
    vencen_pronto: Array,
    ingresosPorMes: Array,
    accesosPorDia: Array,
    ingresosPorPlan: Array,
    ultimosAccesos: Array,
})

const money = (n) => 'S/ ' + Number(n||0).toFixed(2)
const fmt = (f) => f ? new Date(f).toLocaleDateString('es-PE') : '-'
const diasRestantes = (m) => m.membrecia_vencimiento
    ? Math.ceil((new Date(m.membrecia_vencimiento) - new Date()) / 86400000) : 0
const maxIngreso = Math.max(...(props.ingresosPorMes?.map(m => m.ingresos) || [1]), 1)
const maxAcceso  = Math.max(...(props.accesosPorDia?.map(d => d.accesos) || [1]), 1)
const colores = ['#6D28D9','#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6']
</script>

<template>
    <AppLayout title="Dashboard Gimnasio">
        <div style="padding:24px; font-family:'Inter',sans-serif; max-width:1400px;">

            <!-- Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
                <div>
                    <h1 style="font-size:26px; font-weight:800; color:#1E293B; margin:0;">💪 Dashboard Gimnasio</h1>
                    <p style="font-size:13px; color:#64748B; margin:4px 0 0;">{{ new Date().toLocaleDateString('es-PE', {weekday:'long', year:'numeric', month:'long', day:'numeric'}) }}</p>
                </div>
                <div style="display:flex; gap:10px;">
                    <button @click="router.visit('/gimnasio/accesos')"
                        style="background:linear-gradient(135deg,#6D28D9,#4C1D95); color:#fff; border:none; padding:10px 20px; border-radius:10px; font-weight:700; cursor:pointer; font-size:13px;">
                        🚪 Registrar Acceso
                    </button>
                    <button @click="router.visit('/gimnasio/miembros')"
                        style="background:#F1F5F9; color:#374151; border:none; padding:10px 20px; border-radius:10px; font-weight:700; cursor:pointer; font-size:13px;">
                        👥 Miembros
                    </button>
                </div>
            </div>

            <!-- KPIs fila 1 -->
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:14px; margin-bottom:20px;">
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #6D28D9;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Total Miembros</div>
                    <div style="font-size:32px; font-weight:900; color:#6D28D9;">{{ stats.total_miembros }}</div>
                    <div style="font-size:12px; color:#94A3B8; margin-top:4px;">registrados</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #16A34A;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Activos</div>
                    <div style="font-size:32px; font-weight:900; color:#16A34A;">{{ stats.activos }}</div>
                    <div style="font-size:12px; color:#94A3B8; margin-top:4px;">membresía vigente</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #DC2626;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Vencidos</div>
                    <div style="font-size:32px; font-weight:900; color:#DC2626;">{{ stats.vencidos }}</div>
                    <div style="font-size:12px; color:#94A3B8; margin-top:4px;">por renovar</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #D97706;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Por Vencer</div>
                    <div style="font-size:32px; font-weight:900; color:#D97706;">{{ stats.por_vencer }}</div>
                    <div style="font-size:12px; color:#94A3B8; margin-top:4px;">en 7 días</div>
                </div>
                <div style="background:linear-gradient(135deg,#10B981,#059669); border-radius:12px; padding:18px; box-shadow:0 2px 8px rgba(16,185,129,0.3); color:#fff;">
                    <div style="font-size:11px; font-weight:700; opacity:0.85; text-transform:uppercase; margin-bottom:6px;">💰 Ingresos Hoy</div>
                    <div style="font-size:24px; font-weight:900;">{{ money(stats.ingresos_hoy) }}</div>
                    <div style="font-size:12px; opacity:0.75; margin-top:4px;">cobros del día</div>
                </div>
                <div style="background:linear-gradient(135deg,#6D28D9,#4C1D95); border-radius:12px; padding:18px; box-shadow:0 2px 8px rgba(109,40,217,0.3); color:#fff;">
                    <div style="font-size:11px; font-weight:700; opacity:0.85; text-transform:uppercase; margin-bottom:6px;">📅 Ingresos Mes</div>
                    <div style="font-size:24px; font-weight:900;">{{ money(stats.ingresos_mes) }}</div>
                    <div style="font-size:12px; opacity:0.75; margin-top:4px;">{{ new Date().toLocaleDateString('es-PE',{month:'long'}) }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #0EA5E9;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Accesos Hoy</div>
                    <div style="font-size:32px; font-weight:900; color:#0EA5E9;">{{ stats.accesos_hoy }}</div>
                    <div style="font-size:12px; color:#94A3B8; margin-top:4px;">{{ stats.dentro_ahora }} dentro ahora</div>
                </div>
            </div>

            <!-- Gráficos -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">

                <!-- Ingresos por mes -->
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <h3 style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 16px;">📈 Ingresos últimos 6 meses</h3>
                    <div style="display:flex; align-items:flex-end; gap:8px; height:120px;">
                        <div v-for="m in ingresosPorMes" :key="m.mes" style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;">
                            <div style="font-size:10px; color:#64748B; font-weight:600;">{{ m.ingresos > 0 ? 'S/'+Math.round(m.ingresos) : '' }}</div>
                            <div :style="{width:'100%', background:'#6D28D9', borderRadius:'4px 4px 0 0', height: Math.max((m.ingresos/maxIngreso*90),4)+'px', transition:'height 0.3s'}"></div>
                            <div style="font-size:10px; color:#94A3B8; text-align:center;">{{ m.mes }}</div>
                        </div>
                    </div>
                </div>

                <!-- Accesos por día -->
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <h3 style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 16px;">🚪 Accesos últimos 7 días</h3>
                    <div style="display:flex; align-items:flex-end; gap:8px; height:120px;">
                        <div v-for="d in accesosPorDia" :key="d.dia" style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;">
                            <div style="font-size:10px; color:#64748B; font-weight:600;">{{ d.accesos || '' }}</div>
                            <div :style="{width:'100%', background:'#0EA5E9', borderRadius:'4px 4px 0 0', height: Math.max((d.accesos/maxAcceso*90),4)+'px', transition:'height 0.3s'}"></div>
                            <div style="font-size:10px; color:#94A3B8; text-align:center;">{{ d.dia }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ingresos por plan + últimos accesos -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">

                <!-- Por plan -->
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <h3 style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 14px;">🏆 Ingresos por plan (mes)</h3>
                    <div v-if="!ingresosPorPlan || ingresosPorPlan.length === 0" style="text-align:center; color:#94A3B8; font-size:13px; padding:20px 0;">Sin pagos este mes</div>
                    <div v-for="(p,i) in ingresosPorPlan" :key="i" style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                        <div :style="{width:'10px', height:'10px', borderRadius:'50%', background:colores[i%colores.length], flexShrink:0}"></div>
                        <div style="flex:1;">
                            <div style="font-size:13px; font-weight:600; color:#1E293B;">{{ p.plan }}</div>
                            <div style="background:#F1F5F9; border-radius:4px; height:5px; margin-top:3px;">
                                <div :style="{width: (ingresosPorPlan[0]?.total > 0 ? p.total/ingresosPorPlan[0].total*100 : 0)+'%', height:'5px', borderRadius:'4px', background:colores[i%colores.length]}"></div>
                            </div>
                        </div>
                        <div style="text-align:right; flex-shrink:0;">
                            <div style="font-size:13px; font-weight:700; color:#1E293B;">{{ money(p.total) }}</div>
                            <div style="font-size:10px; color:#94A3B8;">{{ p.count }} pago{{ p.count !== 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Últimos accesos hoy -->
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <h3 style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 14px;">🚶 Últimos accesos hoy</h3>
                    <div v-if="!ultimosAccesos || ultimosAccesos.length === 0" style="text-align:center; color:#94A3B8; font-size:13px; padding:20px 0;">Sin accesos hoy</div>
                    <div v-for="a in ultimosAccesos" :key="a.id" style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #F1F5F9;">
                        <div>
                            <div style="font-size:13px; font-weight:600; color:#1E293B;">{{ a.miembro?.nombre }} {{ a.miembro?.apellidos }}</div>
                            <div style="font-size:11px; color:#64748B;">{{ a.miembro?.plan?.nombre || 'Sin plan' }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:12px; font-weight:700; color:#0EA5E9;">{{ new Date(a.entrada).toLocaleTimeString('es-PE', {hour:'2-digit',minute:'2-digit'}) }}</div>
                            <div v-if="!a.salida" style="font-size:10px; background:#DCFCE7; color:#16A34A; padding:1px 6px; border-radius:10px; font-weight:700;">Dentro</div>
                            <div v-else style="font-size:10px; color:#94A3B8;">Salió {{ new Date(a.salida).toLocaleTimeString('es-PE', {hour:'2-digit',minute:'2-digit'}) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas por vencer -->
            <div v-if="vencen_pronto.length" style="background:#FFFBEB; border:1px solid #FCD34D; border-radius:12px; padding:20px; margin-bottom:20px;">
                <h3 style="font-size:14px; font-weight:700; color:#92400E; margin:0 0 14px;">⚠️ Membresías por vencer (próximos 7 días)</h3>
                <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:10px;">
                    <div v-for="m in vencen_pronto" :key="m.id"
                        style="background:#fff; border-radius:8px; padding:12px; border:1px solid #FDE68A; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-size:13px; font-weight:700; color:#1E293B;">{{ m.nombre }} {{ m.apellidos }}</div>
                            <div style="font-size:11px; color:#64748B;">{{ m.plan?.nombre }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div :style="{background: diasRestantes(m) <= 2 ? '#FEE2E2' : '#FEF3C7', color: diasRestantes(m) <= 2 ? '#991B1B' : '#92400E', padding:'3px 8px', borderRadius:'20px', fontSize:'11px', fontWeight:'700'}">
                                {{ diasRestantes(m) }} día{{ diasRestantes(m) !== 1 ? 's' : '' }}
                            </div>
                            <div style="font-size:10px; color:#94A3B8; margin-top:2px;">{{ fmt(m.membrecia_vencimiento) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accesos rápidos -->
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button @click="router.visit('/gimnasio/accesos')" style="background:#6D28D9; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">🚪 Accesos</button>
                <button @click="router.visit('/gimnasio/miembros')" style="background:#3B82F6; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">👥 Miembros</button>
                <button @click="router.visit('/gimnasio/planes')" style="background:#10B981; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">📋 Planes</button>
                <button @click="router.visit('/gimnasio/clases')" style="background:#F59E0B; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">🏃 Clases</button>
                <button @click="router.visit('/gimnasio/instructores')" style="background:#6B7280; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">👨‍🏫 Instructores</button>
            </div>

        </div>
    </AppLayout>
</template>
