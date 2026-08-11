<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    totalHabitaciones: Number, disponibles: Number, ocupadas: Number,
    limpieza: Number, mantenimiento: Number, ocupacionPct: Number,
    checkinsHoy: Number, checkoutsHoy: Number,
    ingresosHoy: Number, ingresosMes: Number,
    saldoPendiente: Number, reservasPendientesPago: Number,
    proximosCheckouts: Array, proximosCheckins: Array,
    housekeepingPendiente: Number, habitaciones: Array,
})

const estadoColor = (e) => ({ disponible:'#16A34A', ocupada:'#DC2626', limpieza:'#D97706', mantenimiento:'#EA580C', reservada:'#3B82F6' }[e] || '#6B7280')
const estadoLabel = (e) => ({ disponible:'✅ Libre', ocupada:'🔴 Ocupada', limpieza:'🧹 Limpieza', mantenimiento:'🔧 Mant.', reservada:'📅 Reservada' }[e] || e)
const fmt = (f) => f ? new Date(f).toLocaleDateString('es-PE') : '-'
const money = (n) => 'S/ ' + Number(n||0).toFixed(2)
</script>

<template>
    <AppLayout title="Dashboard Hotel">
        <div style="padding:24px; font-family:'Inter',sans-serif; max-width:1400px;">

            <!-- Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
                <div>
                    <h1 style="font-size:26px; font-weight:800; color:#1E293B; margin:0;">🏨 Dashboard Hotel</h1>
                    <p style="font-size:13px; color:#64748B; margin:4px 0 0;">{{ new Date().toLocaleDateString('es-PE', {weekday:'long', year:'numeric', month:'long', day:'numeric'}) }}</p>
                </div>
                <button @click="router.visit('/hotel/recepcion')"
                    style="background:linear-gradient(135deg,#3B82F6,#1D4ED8); color:#fff; border:none; padding:12px 24px; border-radius:10px; font-weight:700; cursor:pointer; font-size:14px;">
                    🏁 Ir a Recepción
                </button>
            </div>

            <!-- KPIs fila 1: Ocupación -->
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:14px; margin-bottom:20px;">
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #3B82F6;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Ocupación</div>
                    <div style="font-size:32px; font-weight:900; color:#3B82F6;">{{ ocupacionPct }}%</div>
                    <div style="font-size:12px; color:#94A3B8; margin-top:4px;">{{ ocupadas }}/{{ totalHabitaciones }} hab.</div>
                    <div style="background:#E2E8F0; border-radius:4px; height:6px; margin-top:8px;">
                        <div :style="{width: ocupacionPct+'%', background:'#3B82F6', borderRadius:'4px', height:'6px', transition:'width 0.5s'}"></div>
                    </div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #16A34A;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Disponibles</div>
                    <div style="font-size:32px; font-weight:900; color:#16A34A;">{{ disponibles }}</div>
                    <div style="font-size:12px; color:#94A3B8; margin-top:4px;">habitaciones libres</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #DC2626;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Ocupadas</div>
                    <div style="font-size:32px; font-weight:900; color:#DC2626;">{{ ocupadas }}</div>
                    <div style="font-size:12px; color:#94A3B8; margin-top:4px;">con huéspedes</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #D97706;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Limpieza</div>
                    <div style="font-size:32px; font-weight:900; color:#D97706;">{{ limpieza }}</div>
                    <div style="font-size:12px; color:#94A3B8; margin-top:4px;">en proceso</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #EA580C;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Mantenimiento</div>
                    <div style="font-size:32px; font-weight:900; color:#EA580C;">{{ mantenimiento }}</div>
                    <div style="font-size:12px; color:#94A3B8; margin-top:4px;">bloqueadas</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #8B5CF6;" v-if="housekeepingPendiente > 0">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Housekeeping</div>
                    <div style="font-size:32px; font-weight:900; color:#8B5CF6;">{{ housekeepingPendiente }}</div>
                    <div style="font-size:12px; color:#94A3B8; margin-top:4px;">tareas pendientes</div>
                </div>
            </div>

            <!-- KPIs fila 2: Finanzas -->
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:14px; margin-bottom:24px;">
                <div style="background:linear-gradient(135deg,#10B981,#059669); border-radius:12px; padding:20px; color:#fff; box-shadow:0 2px 8px rgba(16,185,129,0.3);">
                    <div style="font-size:11px; font-weight:700; opacity:0.85; text-transform:uppercase; margin-bottom:6px;">💰 Ingresos Hoy</div>
                    <div style="font-size:28px; font-weight:900;">{{ money(ingresosHoy) }}</div>
                    <div style="font-size:12px; opacity:0.75; margin-top:4px;">pagos recibidos hoy</div>
                </div>
                <div style="background:linear-gradient(135deg,#3B82F6,#1D4ED8); border-radius:12px; padding:20px; color:#fff; box-shadow:0 2px 8px rgba(59,130,246,0.3);">
                    <div style="font-size:11px; font-weight:700; opacity:0.85; text-transform:uppercase; margin-bottom:6px;">📅 Ingresos del Mes</div>
                    <div style="font-size:28px; font-weight:900;">{{ money(ingresosMes) }}</div>
                    <div style="font-size:12px; opacity:0.75; margin-top:4px;">{{ new Date().toLocaleDateString('es-PE',{month:'long'}) }}</div>
                </div>
                <div v-if="saldoPendiente > 0" style="background:linear-gradient(135deg,#EF4444,#DC2626); border-radius:12px; padding:20px; color:#fff; box-shadow:0 2px 8px rgba(239,68,68,0.3);">
                    <div style="font-size:11px; font-weight:700; opacity:0.85; text-transform:uppercase; margin-bottom:6px;">⚠️ Saldo por Cobrar</div>
                    <div style="font-size:28px; font-weight:900;">{{ money(saldoPendiente) }}</div>
                    <div style="font-size:12px; opacity:0.75; margin-top:4px;">{{ reservasPendientesPago }} reserva{{ reservasPendientesPago !== 1 ? 's' : '' }} con deuda</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border:2px solid #E2E8F0;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">📊 Actividad Hoy</div>
                    <div style="display:flex; gap:16px; margin-top:8px;">
                        <div style="text-align:center;">
                            <div style="font-size:24px; font-weight:900; color:#16A34A;">{{ checkinsHoy }}</div>
                            <div style="font-size:11px; color:#64748B;">Check-ins</div>
                        </div>
                        <div style="width:1px; background:#E2E8F0;"></div>
                        <div style="text-align:center;">
                            <div style="font-size:24px; font-weight:900; color:#DC2626;">{{ checkoutsHoy }}</div>
                            <div style="font-size:11px; color:#64748B;">Check-outs</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas: próximos checkouts y checkins -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
                <!-- Próximos checkouts -->
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <h3 style="font-size:14px; font-weight:700; color:#DC2626; margin:0 0 14px;">🚪 Checkouts pendientes (hoy/mañana)</h3>
                    <div v-if="proximosCheckouts.length === 0" style="color:#94A3B8; font-size:13px; text-align:center; padding:20px 0;">Sin checkouts próximos</div>
                    <div v-for="r in proximosCheckouts" :key="r.id"
                        style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #F1F5F9;">
                        <div>
                            <div style="font-size:13px; font-weight:600; color:#1E293B;">{{ r.huesped?.nombre_completo }}</div>
                            <div style="font-size:11px; color:#64748B;">Hab. {{ r.habitacion?.numero }} — {{ r.codigo }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:12px; font-weight:700;" :style="{color: new Date(r.fecha_checkout_previsto) <= new Date() ? '#DC2626' : '#D97706'}">
                                {{ fmt(r.fecha_checkout_previsto) }}
                            </div>
                            <div style="font-size:11px; color:#94A3B8;">{{ money(r.total - r.monto_pagado) }} pendiente</div>
                        </div>
                    </div>
                </div>

                <!-- Próximos checkins -->
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <h3 style="font-size:14px; font-weight:700; color:#3B82F6; margin:0 0 14px;">📅 Checkins esperados (hoy/mañana)</h3>
                    <div v-if="proximosCheckins.length === 0" style="color:#94A3B8; font-size:13px; text-align:center; padding:20px 0;">Sin checkins próximos</div>
                    <div v-for="r in proximosCheckins" :key="r.id"
                        style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #F1F5F9;">
                        <div>
                            <div style="font-size:13px; font-weight:600; color:#1E293B;">{{ r.huesped?.nombre_completo }}</div>
                            <div style="font-size:11px; color:#64748B;">Hab. {{ r.habitacion?.numero }} — {{ r.codigo }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:12px; font-weight:700; color:#3B82F6;">{{ fmt(r.fecha_checkin) }}</div>
                            <div style="font-size:11px; color:#94A3B8;">{{ r.num_noches }} noche{{ r.num_noches !== 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mapa de habitaciones -->
            <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08); margin-bottom:20px;">
                <h3 style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 14px;">🗺️ Mapa de habitaciones</h3>
                <div v-if="habitaciones.length === 0" style="text-align:center; color:#94A3B8; padding:40px;">No hay habitaciones registradas.</div>
                <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(130px,1fr)); gap:10px;">
                    <div v-for="h in habitaciones" :key="h.id"
                        :style="{background: estadoColor(h.estado)+'15', border:'2px solid '+estadoColor(h.estado), borderRadius:'10px', padding:'12px'}">
                        <div style="font-size:20px; font-weight:800; color:#1E293B;">{{ h.numero }}</div>
                        <div style="font-size:10px; color:#64748B; margin:2px 0;">Piso {{ h.piso }} · {{ h.tipo?.nombre }}</div>
                        <div :style="{fontSize:'11px', fontWeight:'700', color: estadoColor(h.estado), marginTop:'6px'}">{{ estadoLabel(h.estado) }}</div>
                        <div v-if="h.reserva_actual" style="font-size:10px; color:#475569; margin-top:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ h.reserva_actual.huesped?.nombre_completo?.split(' ')[0] }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accesos rápidos -->
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button @click="router.visit('/hotel/recepcion')" style="background:#3B82F6; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">🏁 Recepción</button>
                <button @click="router.visit('/hotel/housekeeping')" style="background:#D97706; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">🧹 Housekeeping</button>
                <button @click="router.visit('/hotel/habitaciones')" style="background:#6B7280; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">🛏️ Habitaciones</button>
                <button @click="router.visit('/hotel/reportes')" style="background:#10B981; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">📊 Reportes</button>
                <button @click="router.visit('/hotel/caja')" style="background:#1E293B; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">💵 Caja</button>
            </div>

        </div>
    </AppLayout>
</template>
