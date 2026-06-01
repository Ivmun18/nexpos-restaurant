<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    totalHabitaciones: Number,
    disponibles: Number,
    ocupadas: Number,
    limpieza: Number,
    checkinsHoy: Number,
    checkoutsHoy: Number,
    ingresosMes: Number,
    habitaciones: Array,
})

const estadoColor = (estado) => {
    const m = { disponible: '#16A34A', ocupada: '#DC2626', limpieza: '#D97706', mantenimiento: '#6B7280' }
    return m[estado] || '#6B7280'
}
const estadoLabel = (estado) => {
    const m = { disponible: '✅ Disponible', ocupada: '🔴 Ocupada', limpieza: '🧹 Limpieza', mantenimiento: '🔧 Mantenimiento' }
    return m[estado] || estado
}
</script>

<template>
    <AppLayout title="Hotel - Dashboard">
        <div style="padding:24px; font-family:'Inter',sans-serif;">
            <!-- Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:24px; font-weight:700; color:#1E293B; margin:0;">🏨 Dashboard Hotel</h1>
                    <p style="color:#64748B; margin:4px 0 0; font-size:13px;">Panel de control en tiempo real</p>
                </div>
                <div style="font-size:13px; color:#64748B;">{{ new Date().toLocaleDateString('es-PE', {weekday:'long', year:'numeric', month:'long', day:'numeric'}) }}</div>
            </div>

            <!-- KPIs -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:16px; margin-bottom:28px;">
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #3B82F6;">
                    <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Total Habitaciones</div>
                    <div style="font-size:32px; font-weight:700; color:#1E293B;">{{ totalHabitaciones }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #16A34A;">
                    <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Disponibles</div>
                    <div style="font-size:32px; font-weight:700; color:#16A34A;">{{ disponibles }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #DC2626;">
                    <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Ocupadas</div>
                    <div style="font-size:32px; font-weight:700; color:#DC2626;">{{ ocupadas }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #D97706;">
                    <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">En Limpieza</div>
                    <div style="font-size:32px; font-weight:700; color:#D97706;">{{ limpieza }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #8B5CF6;">
                    <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Check-ins Hoy</div>
                    <div style="font-size:32px; font-weight:700; color:#8B5CF6;">{{ checkinsHoy }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #0EA5E9;">
                    <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Check-outs Hoy</div>
                    <div style="font-size:32px; font-weight:700; color:#0EA5E9;">{{ checkoutsHoy }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #10B981;">
                    <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Ingresos del Mes</div>
                    <div style="font-size:24px; font-weight:700; color:#10B981;">S/ {{ Number(ingresosMes).toFixed(2) }}</div>
                </div>
            </div>

            <!-- Mapa de habitaciones -->
            <div style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                <h2 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px;">🗺️ Mapa de Habitaciones</h2>
                <div v-if="habitaciones.length === 0" style="text-align:center; color:#94A3B8; padding:40px;">
                    No hay habitaciones registradas aún.
                </div>
                <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:12px;">
                    <div v-for="h in habitaciones" :key="h.id"
                        :style="{background: estadoColor(h.estado) + '15', border: '2px solid ' + estadoColor(h.estado), borderRadius:'10px', padding:'14px', cursor:'pointer'}">
                        <div style="font-size:20px; font-weight:700; color:#1E293B;">{{ h.numero }}</div>
                        <div style="font-size:11px; color:#64748B; margin:2px 0;">Piso {{ h.piso }}</div>
                        <div style="font-size:11px; color:#64748B;">{{ h.tipo?.nombre }}</div>
                        <div :style="{fontSize:'11px', fontWeight:'600', color: estadoColor(h.estado), marginTop:'6px'}">{{ estadoLabel(h.estado) }}</div>
                        <div v-if="h.reserva_actual" style="font-size:10px; color:#64748B; margin-top:4px;">{{ h.reserva_actual.huesped?.nombre_completo }}</div>
                    </div>
                </div>
            </div>

            <!-- Accesos rápidos -->
            <div style="display:flex; gap:12px; margin-top:20px; flex-wrap:wrap;">
                <button @click="router.visit('/hotel/recepcion')"
                    style="background:#3B82F6; color:#fff; border:none; padding:12px 24px; border-radius:8px; font-weight:600; cursor:pointer; font-size:14px;">
                    🏁 Recepción / Check-in
                </button>
                <button @click="router.visit('/hotel/housekeeping')"
                    style="background:#D97706; color:#fff; border:none; padding:12px 24px; border-radius:8px; font-weight:600; cursor:pointer; font-size:14px;">
                    🧹 Housekeeping
                </button>
                <button @click="router.visit('/hotel/habitaciones')"
                    style="background:#6B7280; color:#fff; border:none; padding:12px 24px; border-radius:8px; font-weight:600; cursor:pointer; font-size:14px;">
                    🛏️ Habitaciones
                </button>
                <button @click="router.visit('/hotel/reportes')"
                    style="background:#10B981; color:#fff; border:none; padding:12px 24px; border-radius:8px; font-weight:600; cursor:pointer; font-size:14px;">
                    📊 Reportes
                </button>
            </div>
        </div>
    </AppLayout>
</template>
