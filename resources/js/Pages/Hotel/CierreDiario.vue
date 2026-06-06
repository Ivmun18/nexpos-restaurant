<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    fecha: String,
    reservas: Array,
    checkins: Array,
    pagosHoy: Array,
    totalEfectivo: Number,
    totalYape: Number,
    totalPlin: Number,
    totalTarjeta: Number,
    totalTransferencia: Number,
    totalDia: Number,
    habitacionesOcupadas: Number,
    habitacionesDisponibles: Number,
    habitacionesLimpieza: Number,
    totalHabitaciones: Number,
})

const fechaSeleccionada = ref(props.fecha)

function cambiarFecha() {
    router.get('/hotel/cierre-diario', { fecha: fechaSeleccionada.value })
}

function imprimir() {
    window.print()
}

const fmt = (n) => Number(n || 0).toFixed(2)
</script>

<template>
    <AppLayout title="🏨 Cierre Diario Hotel">
        <div style="max-width:900px; margin:0 auto;">

            <!-- Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
                <div>
                    <h2 style="font-size:22px; font-weight:700; color:#1E293B; margin:0;">📊 Cierre Diario</h2>
                    <p style="color:#64748B; margin:4px 0 0; font-size:14px;">Resumen de operaciones del día</p>
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <input type="date" v-model="fechaSeleccionada" @change="cambiarFecha"
                        style="padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none;"/>
                    <button @click="imprimir"
                        style="padding:10px 18px; background:#1E293B; color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                        🖨️ Imprimir
                    </button>
                </div>
            </div>

            <!-- Estado habitaciones -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px,1fr)); gap:12px; margin-bottom:20px;">
                <div style="background:white; border-radius:14px; padding:16px; border:1px solid #E2E8F0; text-align:center;">
                    <p style="font-size:12px; color:#64748B; margin:0 0 6px; font-weight:600; text-transform:uppercase;">Total hab.</p>
                    <p style="font-size:28px; font-weight:800; color:#1E293B; margin:0;">{{ totalHabitaciones }}</p>
                </div>
                <div style="background:#F0FDFA; border-radius:14px; padding:16px; border:1px solid #CCFBF1; text-align:center;">
                    <p style="font-size:12px; color:#0F766E; margin:0 0 6px; font-weight:600; text-transform:uppercase;">Disponibles</p>
                    <p style="font-size:28px; font-weight:800; color:#0F766E; margin:0;">{{ habitacionesDisponibles }}</p>
                </div>
                <div style="background:#FEF2F2; border-radius:14px; padding:16px; border:1px solid #FECACA; text-align:center;">
                    <p style="font-size:12px; color:#DC2626; margin:0 0 6px; font-weight:600; text-transform:uppercase;">Ocupadas</p>
                    <p style="font-size:28px; font-weight:800; color:#DC2626; margin:0;">{{ habitacionesOcupadas }}</p>
                </div>
                <div style="background:#FFFBEB; border-radius:14px; padding:16px; border:1px solid #FDE68A; text-align:center;">
                    <p style="font-size:12px; color:#D97706; margin:0 0 6px; font-weight:600; text-transform:uppercase;">Limpieza</p>
                    <p style="font-size:28px; font-weight:800; color:#D97706; margin:0;">{{ habitacionesLimpieza }}</p>
                </div>
                <div style="background:white; border-radius:14px; padding:16px; border:1px solid #E2E8F0; text-align:center;">
                    <p style="font-size:12px; color:#64748B; margin:0 0 6px; font-weight:600; text-transform:uppercase;">Check-ins hoy</p>
                    <p style="font-size:28px; font-weight:800; color:#3B82F6; margin:0;">{{ checkins.length }}</p>
                </div>
                <div style="background:white; border-radius:14px; padding:16px; border:1px solid #E2E8F0; text-align:center;">
                    <p style="font-size:12px; color:#64748B; margin:0 0 6px; font-weight:600; text-transform:uppercase;">Check-outs hoy</p>
                    <p style="font-size:28px; font-weight:800; color:#8B5CF6; margin:0;">{{ reservas.length }}</p>
                </div>
            </div>

            <!-- Ingresos por método -->
            <div style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0; margin-bottom:20px;">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 16px;">💰 Ingresos del día</h3>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px,1fr)); gap:10px; margin-bottom:16px;">
                    <div style="background:#F0FDFA; border-radius:10px; padding:12px; text-align:center;">
                        <p style="font-size:12px; color:#0F766E; margin:0 0 4px; font-weight:600;">💵 Efectivo</p>
                        <p style="font-size:18px; font-weight:800; color:#0F766E; margin:0;">S/ {{ fmt(totalEfectivo) }}</p>
                    </div>
                    <div style="background:#EFF6FF; border-radius:10px; padding:12px; text-align:center;">
                        <p style="font-size:12px; color:#1D4ED8; margin:0 0 4px; font-weight:600;">📱 Yape</p>
                        <p style="font-size:18px; font-weight:800; color:#1D4ED8; margin:0;">S/ {{ fmt(totalYape) }}</p>
                    </div>
                    <div style="background:#F5F3FF; border-radius:10px; padding:12px; text-align:center;">
                        <p style="font-size:12px; color:#6D28D9; margin:0 0 4px; font-weight:600;">📲 Plin</p>
                        <p style="font-size:18px; font-weight:800; color:#6D28D9; margin:0;">S/ {{ fmt(totalPlin) }}</p>
                    </div>
                    <div style="background:#FFF7ED; border-radius:10px; padding:12px; text-align:center;">
                        <p style="font-size:12px; color:#C2410C; margin:0 0 4px; font-weight:600;">💳 Tarjeta</p>
                        <p style="font-size:18px; font-weight:800; color:#C2410C; margin:0;">S/ {{ fmt(totalTarjeta) }}</p>
                    </div>
                    <div style="background:#F0FDF4; border-radius:10px; padding:12px; text-align:center;">
                        <p style="font-size:12px; color:#15803D; margin:0 0 4px; font-weight:600;">🏦 Transferencia</p>
                        <p style="font-size:18px; font-weight:800; color:#15803D; margin:0;">S/ {{ fmt(totalTransferencia) }}</p>
                    </div>
                </div>
                <div style="background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:12px; padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:16px; font-weight:700; color:white;">TOTAL DEL DÍA</span>
                    <span style="font-size:26px; font-weight:800; color:white;">S/ {{ fmt(totalDia) }}</span>
                </div>
            </div>

            <!-- Check-outs del día -->
            <div v-if="reservas.length" style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0; margin-bottom:20px;">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 14px;">🚪 Check-outs del día</h3>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#F8FAFC;">
                            <th style="padding:10px 12px; font-size:12px; color:#64748B; text-align:left; font-weight:600;">Huésped</th>
                            <th style="padding:10px 12px; font-size:12px; color:#64748B; text-align:left; font-weight:600;">Habitación</th>
                            <th style="padding:10px 12px; font-size:12px; color:#64748B; text-align:center; font-weight:600;">Noches</th>
                            <th style="padding:10px 12px; font-size:12px; color:#64748B; text-align:right; font-weight:600;">Total</th>
                            <th style="padding:10px 12px; font-size:12px; color:#64748B; text-align:right; font-weight:600;">Pagado</th>
                            <th style="padding:10px 12px; font-size:12px; color:#64748B; text-align:center; font-weight:600;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in reservas" :key="r.id" style="border-top:1px solid #F1F5F9;">
                            <td style="padding:10px 12px; font-size:13px; font-weight:600;">{{ r.huesped?.nombre_completo }}</td>
                            <td style="padding:10px 12px; font-size:13px;">Hab. {{ r.habitacion?.numero }}</td>
                            <td style="padding:10px 12px; font-size:13px; text-align:center;">{{ r.num_noches }}</td>
                            <td style="padding:10px 12px; font-size:13px; text-align:right; font-weight:600;">S/ {{ fmt(r.total) }}</td>
                            <td style="padding:10px 12px; font-size:13px; text-align:right; color:#16A34A; font-weight:600;">S/ {{ fmt(r.monto_pagado) }}</td>
                            <td style="padding:10px 12px; text-align:center;">
                                <span :style="{
                                    padding:'3px 8px', borderRadius:'6px', fontSize:'11px', fontWeight:'700',
                                    background: r.estado_pago==='pagado' ? '#D1FAE5' : '#FEF3C7',
                                    color: r.estado_pago==='pagado' ? '#065F46' : '#92400E'
                                }">{{ r.estado_pago?.toUpperCase() }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Check-ins del día -->
            <div v-if="checkins.length" style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0;">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 14px;">🏨 Check-ins del día</h3>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#F8FAFC;">
                            <th style="padding:10px 12px; font-size:12px; color:#64748B; text-align:left; font-weight:600;">Código</th>
                            <th style="padding:10px 12px; font-size:12px; color:#64748B; text-align:left; font-weight:600;">Huésped</th>
                            <th style="padding:10px 12px; font-size:12px; color:#64748B; text-align:left; font-weight:600;">Habitación</th>
                            <th style="padding:10px 12px; font-size:12px; color:#64748B; text-align:center; font-weight:600;">Checkout prev.</th>
                            <th style="padding:10px 12px; font-size:12px; color:#64748B; text-align:right; font-weight:600;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in checkins" :key="r.id" style="border-top:1px solid #F1F5F9;">
                            <td style="padding:10px 12px; font-size:12px; color:#64748B;">{{ r.codigo }}</td>
                            <td style="padding:10px 12px; font-size:13px; font-weight:600;">{{ r.huesped?.nombre_completo }}</td>
                            <td style="padding:10px 12px; font-size:13px;">Hab. {{ r.habitacion?.numero }}</td>
                            <td style="padding:10px 12px; font-size:12px; text-align:center;">{{ new Date(r.fecha_checkout_previsto).toLocaleDateString('es-PE') }}</td>
                            <td style="padding:10px 12px; font-size:13px; text-align:right; font-weight:600;">S/ {{ fmt(r.total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="!reservas.length && !checkins.length" style="text-align:center; padding:60px; color:#94A3B8;">
                <p style="font-size:48px; margin:0 0 12px;">📭</p>
                <p style="font-size:16px;">No hay movimientos para esta fecha</p>
            </div>
        </div>
    </AppLayout>
</template>

<style>
@media print {
    nav, aside, header, .no-print { display: none !important; }
    body { font-size: 12px; }
}
</style>
