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
        <div style="padding:24px; font-family:'Inter',sans-serif; max-width:1400px; background:#F1F5F9; min-height:100vh;">

            <!-- Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px; flex-wrap:wrap; gap:12px;">
                <div>
                    <h1 style="font-size:28px; font-weight:900; color:#1E293B; margin:0;">💪 FitZone Dashboard</h1>
                    <p style="font-size:13px; color:#64748B; margin:4px 0 0;">{{ new Date().toLocaleDateString('es-PE', {weekday:'long', year:'numeric', month:'long', day:'numeric'}) }}</p>
                </div>
                <button @click="router.visit('/gimnasio/accesos')"
                    style="background:linear-gradient(135deg,#6D28D9,#4C1D95); color:#fff; border:none; padding:12px 24px; border-radius:12px; font-weight:700; cursor:pointer; font-size:14px; box-shadow:0 4px 15px rgba(109,40,217,0.4);">
                    🚪 Registrar Acceso
                </button>
            </div>

            <!-- KPIs fila 1 — grandes y coloridos -->
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:16px; margin-bottom:24px;">

                <div style="background:linear-gradient(135deg,#6D28D9,#7C3AED); border-radius:16px; padding:22px; color:#fff; box-shadow:0 4px 20px rgba(109,40,217,0.3); position:relative; overflow:hidden;">
                    <div style="position:absolute; right:-10px; top:-10px; font-size:60px; opacity:0.15;">👥</div>
                    <div style="font-size:11px; font-weight:700; opacity:0.8; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Total Miembros</div>
                    <div style="font-size:42px; font-weight:900; line-height:1;">{{ stats.total_miembros }}</div>
                    <div style="font-size:12px; opacity:0.75; margin-top:6px;">registrados activos</div>
                </div>

                <div style="background:linear-gradient(135deg,#059669,#10B981); border-radius:16px; padding:22px; color:#fff; box-shadow:0 4px 20px rgba(16,185,129,0.3); position:relative; overflow:hidden;">
                    <div style="position:absolute; right:-10px; top:-10px; font-size:60px; opacity:0.15;">✅</div>
                    <div style="font-size:11px; font-weight:700; opacity:0.8; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Activos</div>
                    <div style="font-size:42px; font-weight:900; line-height:1;">{{ stats.activos }}</div>
                    <div style="font-size:12px; opacity:0.75; margin-top:6px;">membresía vigente</div>
                </div>

                <div style="background:linear-gradient(135deg,#DC2626,#EF4444); border-radius:16px; padding:22px; color:#fff; box-shadow:0 4px 20px rgba(220,38,38,0.3); position:relative; overflow:hidden;">
                    <div style="position:absolute; right:-10px; top:-10px; font-size:60px; opacity:0.15;">⚠️</div>
                    <div style="font-size:11px; font-weight:700; opacity:0.8; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Vencidos</div>
                    <div style="font-size:42px; font-weight:900; line-height:1;">{{ stats.vencidos }}</div>
                    <div style="font-size:12px; opacity:0.75; margin-top:6px;">por renovar</div>
                </div>

                <div style="background:linear-gradient(135deg,#D97706,#F59E0B); border-radius:16px; padding:22px; color:#fff; box-shadow:0 4px 20px rgba(217,119,6,0.3); position:relative; overflow:hidden;">
                    <div style="position:absolute; right:-10px; top:-10px; font-size:60px; opacity:0.15;">⏰</div>
                    <div style="font-size:11px; font-weight:700; opacity:0.8; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Por Vencer</div>
                    <div style="font-size:42px; font-weight:900; line-height:1;">{{ stats.por_vencer }}</div>
                    <div style="font-size:12px; opacity:0.75; margin-top:6px;">en 7 días</div>
                </div>

                <div style="background:linear-gradient(135deg,#0EA5E9,#38BDF8); border-radius:16px; padding:22px; color:#fff; box-shadow:0 4px 20px rgba(14,165,233,0.3); position:relative; overflow:hidden;">
                    <div style="position:absolute; right:-10px; top:-10px; font-size:60px; opacity:0.15;">🚪</div>
                    <div style="font-size:11px; font-weight:700; opacity:0.8; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Accesos Hoy</div>
                    <div style="font-size:42px; font-weight:900; line-height:1;">{{ stats.accesos_hoy }}</div>
                    <div style="font-size:12px; opacity:0.75; margin-top:6px;">{{ stats.dentro_ahora }} dentro ahora 🟢</div>
                </div>

                <div style="background:linear-gradient(135deg,#0F766E,#14B8A6); border-radius:16px; padding:22px; color:#fff; box-shadow:0 4px 20px rgba(20,184,166,0.3); position:relative; overflow:hidden;">
                    <div style="position:absolute; right:-10px; top:-10px; font-size:60px; opacity:0.15;">💰</div>
                    <div style="font-size:11px; font-weight:700; opacity:0.8; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">Ingresos Mes</div>
                    <div style="font-size:28px; font-weight:900; line-height:1;">{{ money(stats.ingresos_mes) }}</div>
                    <div style="font-size:12px; opacity:0.75; margin-top:6px;">Hoy: {{ money(stats.ingresos_hoy) }}</div>
                </div>

            </div>

            <!-- Gráficos -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">

                <!-- Ingresos por mes -->
                <div style="background:#fff; border-radius:16px; padding:22px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
                        <h3 style="font-size:15px; font-weight:800; color:#1E293B; margin:0;">📈 Ingresos por mes</h3>
                        <span style="font-size:11px; background:#F5F3FF; color:#6D28D9; padding:3px 10px; border-radius:20px; font-weight:700;">Últimos 6 meses</span>
                    </div>
                    <div style="display:flex; align-items:flex-end; gap:8px; height:130px;">
                        <div v-for="m in ingresosPorMes" :key="m.mes" style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;">
                            <div style="font-size:10px; color:#6D28D9; font-weight:700;">{{ m.ingresos > 0 ? 'S/'+Math.round(m.ingresos) : '' }}</div>
                            <div :style="{width:'100%', background:'linear-gradient(180deg,#7C3AED,#6D28D9)', borderRadius:'6px 6px 0 0', height: Math.max((m.ingresos/maxIngreso*100),4)+'px', transition:'height 0.5s', boxShadow:'0 2px 8px rgba(109,40,217,0.3)'}"></div>
                            <div style="font-size:10px; color:#94A3B8; text-align:center;">{{ m.mes }}</div>
                        </div>
                    </div>
                </div>

                <!-- Accesos por día -->
                <div style="background:#fff; border-radius:16px; padding:22px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
                        <h3 style="font-size:15px; font-weight:800; color:#1E293B; margin:0;">🚪 Accesos por día</h3>
                        <span style="font-size:11px; background:#EFF6FF; color:#3B82F6; padding:3px 10px; border-radius:20px; font-weight:700;">Últimos 7 días</span>
                    </div>
                    <div style="display:flex; align-items:flex-end; gap:8px; height:130px;">
                        <div v-for="d in accesosPorDia" :key="d.dia" style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;">
                            <div style="font-size:10px; color:#0EA5E9; font-weight:700;">{{ d.accesos || '' }}</div>
                            <div :style="{width:'100%', background:'linear-gradient(180deg,#38BDF8,#0EA5E9)', borderRadius:'6px 6px 0 0', height: Math.max((d.accesos/maxAcceso*100),4)+'px', transition:'height 0.5s', boxShadow:'0 2px 8px rgba(14,165,233,0.3)'}"></div>
                            <div style="font-size:10px; color:#94A3B8; text-align:center;">{{ d.dia }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ingresos por plan + últimos accesos -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">

                <!-- Por plan -->
                <div style="background:#fff; border-radius:16px; padding:22px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    <h3 style="font-size:15px; font-weight:800; color:#1E293B; margin:0 0 16px;">🏆 Ingresos por plan</h3>
                    <div v-if="!ingresosPorPlan || ingresosPorPlan.length === 0" style="text-align:center; color:#94A3B8; font-size:13px; padding:20px 0;">Sin pagos este mes</div>
                    <div v-for="(p,i) in ingresosPorPlan" :key="i" style="display:flex; align-items:center; gap:12px; margin-bottom:14px;">
                        <div :style="{width:'40px', height:'40px', borderRadius:'10px', background:colores[i%colores.length]+'20', display:'flex', alignItems:'center', justifyContent:'center', fontSize:'18px', flexShrink:0}">
                            {{ ['🥇','🥈','🥉','🏅','🎖️'][i] || '🏅' }}
                        </div>
                        <div style="flex:1;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                <span style="font-size:13px; font-weight:700; color:#1E293B;">{{ p.plan }}</span>
                                <span style="font-size:13px; font-weight:800;" :style="{color:colores[i%colores.length]}">{{ money(p.total) }}</span>
                            </div>
                            <div style="background:#F1F5F9; border-radius:6px; height:7px;">
                                <div :style="{width: (ingresosPorPlan[0]?.total > 0 ? p.total/ingresosPorPlan[0].total*100 : 0)+'%', height:'7px', borderRadius:'6px', background:colores[i%colores.length], transition:'width 0.5s'}"></div>
                            </div>
                            <div style="font-size:11px; color:#94A3B8; margin-top:2px;">{{ p.count }} pago{{ p.count !== 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Últimos accesos -->
                <div style="background:#fff; border-radius:16px; padding:22px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    <h3 style="font-size:15px; font-weight:800; color:#1E293B; margin:0 0 16px;">🏃 En el gimnasio ahora</h3>
                    <div v-if="!ultimosAccesos || ultimosAccesos.length === 0" style="text-align:center; color:#94A3B8; font-size:13px; padding:20px 0;">Sin accesos hoy</div>
                    <div v-for="a in ultimosAccesos" :key="a.id"
                        style="display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #F8FAFC;">
                        <div :style="{width:'38px', height:'38px', borderRadius:'50%', background: !a.salida ? 'linear-gradient(135deg,#059669,#10B981)' : '#F1F5F9', display:'flex', alignItems:'center', justifyContent:'center', fontSize:'16px', flexShrink:0}">
                            {{ !a.salida ? '🟢' : '⚪' }}
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:13px; font-weight:700; color:#1E293B;">{{ a.miembro?.nombre }} {{ a.miembro?.apellidos }}</div>
                            <div style="font-size:11px; color:#64748B;">{{ a.miembro?.plan?.nombre || 'Sin plan' }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:12px; font-weight:700; color:#0EA5E9;">{{ new Date(a.entrada).toLocaleTimeString('es-PE', {hour:'2-digit',minute:'2-digit'}) }}</div>
                            <div v-if="!a.salida" style="font-size:10px; background:#DCFCE7; color:#16A34A; padding:2px 7px; border-radius:10px; font-weight:700;">Dentro</div>
                            <div v-else style="font-size:10px; color:#94A3B8;">Salió</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas membresías por vencer -->
            <div v-if="vencen_pronto.length" style="background:linear-gradient(135deg,#FFFBEB,#FEF9C3); border:2px solid #FCD34D; border-radius:16px; padding:20px; margin-bottom:20px;">
                <h3 style="font-size:15px; font-weight:800; color:#92400E; margin:0 0 14px;">⚠️ Membresías por vencer — próximos 7 días</h3>
                <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:10px;">
                    <div v-for="m in vencen_pronto" :key="m.id"
                        style="background:#fff; border-radius:12px; padding:14px; border:1px solid #FDE68A; display:flex; justify-content:space-between; align-items:center; box-shadow:0 1px 4px rgba(0,0,0,0.05);">
                        <div>
                            <div style="font-size:13px; font-weight:700; color:#1E293B;">{{ m.nombre }} {{ m.apellidos }}</div>
                            <div style="font-size:11px; color:#64748B;">{{ m.plan?.nombre }}</div>
                            <div style="font-size:11px; color:#94A3B8;">Vence: {{ fmt(m.membrecia_vencimiento) }}</div>
                        </div>
                        <div :style="{background: diasRestantes(m) <= 2 ? 'linear-gradient(135deg,#DC2626,#EF4444)' : 'linear-gradient(135deg,#D97706,#F59E0B)', color:'#fff', padding:'6px 12px', borderRadius:'20px', fontSize:'12px', fontWeight:'900', textAlign:'center', minWidth:'50px'}">
                            {{ diasRestantes(m) }}d
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accesos rápidos -->
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button @click="router.visit('/gimnasio/accesos')" style="background:linear-gradient(135deg,#6D28D9,#7C3AED); color:#fff; border:none; padding:12px 22px; border-radius:12px; font-weight:700; cursor:pointer; font-size:13px; box-shadow:0 3px 10px rgba(109,40,217,0.3);">🚪 Accesos</button>
                <button @click="router.visit('/gimnasio/miembros')" style="background:linear-gradient(135deg,#0EA5E9,#38BDF8); color:#fff; border:none; padding:12px 22px; border-radius:12px; font-weight:700; cursor:pointer; font-size:13px; box-shadow:0 3px 10px rgba(14,165,233,0.3);">👥 Miembros</button>
                <button @click="router.visit('/gimnasio/planes')" style="background:linear-gradient(135deg,#059669,#10B981); color:#fff; border:none; padding:12px 22px; border-radius:12px; font-weight:700; cursor:pointer; font-size:13px; box-shadow:0 3px 10px rgba(16,185,129,0.3);">📋 Planes</button>
                <button @click="router.visit('/gimnasio/clases')" style="background:linear-gradient(135deg,#D97706,#F59E0B); color:#fff; border:none; padding:12px 22px; border-radius:12px; font-weight:700; cursor:pointer; font-size:13px; box-shadow:0 3px 10px rgba(217,119,6,0.3);">🏃 Clases</button>
                <button @click="router.visit('/gimnasio/pagos')" style="background:linear-gradient(135deg,#0F766E,#14B8A6); color:#fff; border:none; padding:12px 22px; border-radius:12px; font-weight:700; cursor:pointer; font-size:13px; box-shadow:0 3px 10px rgba(20,184,166,0.3);">💰 Pagos</button>
                <button @click="router.visit('/gimnasio/reportes')" style="background:linear-gradient(135deg,#1E293B,#334155); color:#fff; border:none; padding:12px 22px; border-radius:12px; font-weight:700; cursor:pointer; font-size:13px;">📊 Reportes</button>
            </div>

        </div>
    </AppLayout>
</template>
