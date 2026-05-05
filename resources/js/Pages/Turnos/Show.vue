<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    turno:  Object,
    cobros: Array,
})

const formatFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString('es-PE', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    })
}

const metodoPagoIcon = (metodo) => {
    const map = { efectivo: '💵', yape: '📱', plin: '📲', tarjeta: '💳' }
    return map[metodo] || '💵'
}

const totalPorMetodo = (metodo) => {
    return props.cobros
        .filter(c => c.metodo_pago === metodo)
        .reduce((sum, c) => sum + Number(c.total), 0)
}

const metodos = ['efectivo', 'yape', 'plin', 'tarjeta']
</script>

<template>
    <AppLayout :title="`Turno · ${turno.tipo}`">
        <div style="max-width:900px; margin:0 auto;">

            <!-- Header -->
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
                <a href="/turnos"
                    style="background:#F1F5F9; border-radius:12px; padding:12px 16px; font-size:15px; font-weight:600; color:#475569; text-decoration:none;">
                    ← Volver
                </a>
                <div>
                    <h1 style="font-size:26px; font-weight:800; color:#1E293B; margin:0;">
                        ⏰ {{ turno.tipo === 'personalizado' ? turno.nombre || 'Turno personalizado' : turno.tipo }}
                    </h1>
                    <p style="font-size:14px; color:#94A3B8; margin:4px 0 0;">
                        {{ turno.user?.name }} · {{ formatFecha(turno.apertura) }}
                        <span v-if="turno.cierre"> → {{ formatFecha(turno.cierre) }}</span>
                    </p>
                </div>
            </div>

            <!-- KPIs -->
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px;">
                <div style="background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:20px; padding:24px; color:white; box-shadow:0 6px 20px rgba(20,184,166,0.3);">
                    <p style="font-size:13px; opacity:0.8; margin:0 0 8px; text-transform:uppercase; letter-spacing:1px;">Total ventas</p>
                    <p style="font-size:32px; font-weight:800; margin:0;">S/ {{ Number(turno.total_ventas).toFixed(2) }}</p>
                </div>
                <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                    <p style="font-size:13px; color:#94A3B8; margin:0 0 8px; text-transform:uppercase; letter-spacing:1px;">Mesas atendidas</p>
                    <p style="font-size:32px; font-weight:800; color:#1E293B; margin:0;">{{ turno.total_mesas }}</p>
                </div>
                <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                    <p style="font-size:13px; color:#94A3B8; margin:0 0 8px; text-transform:uppercase; letter-spacing:1px;">Estado</p>
                    <p style="font-size:24px; font-weight:800; margin:0;" :style="{ color: turno.estado === 'abierto' ? '#14B8A6' : '#64748B' }">
                        {{ turno.estado === 'abierto' ? '🟢 Abierto' : '⭕ Cerrado' }}
                    </p>
                </div>
            </div>

            <!-- Resumen por método de pago -->
            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; margin-bottom:20px; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 16px;">💳 Por método de pago</p>
                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px;">
                    <div v-for="m in metodos" :key="m"
                        style="background:#F8FAFC; border-radius:14px; padding:16px; text-align:center; border:1px solid #E2E8F0;">
                        <p style="font-size:28px; margin:0 0 8px;">{{ metodoPagoIcon(m) }}</p>
                        <p style="font-size:13px; font-weight:600; color:#64748B; margin:0 0 6px; text-transform:capitalize;">{{ m }}</p>
                        <p style="font-size:20px; font-weight:800; color:#1E293B; margin:0;">S/ {{ totalPorMetodo(m).toFixed(2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Detalle de cobros -->
            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 20px;">🧾 Cobros del turno</p>

                <div v-if="!cobros.length" style="text-align:center; padding:40px 0; color:#CBD5E1;">
                    <p style="font-size:32px; margin:0 0 8px;">📭</p>
                    <p style="font-size:16px;">Sin cobros en este turno</p>
                </div>

                <div v-for="cobro in cobros" :key="cobro.id"
                    style="display:flex; align-items:center; justify-content:space-between; padding:14px 0; border-bottom:1px solid #F1F5F9;">
                    <div style="display:flex; align-items:center; gap:14px;">
                        <span style="font-size:28px;">{{ metodoPagoIcon(cobro.metodo_pago) }}</span>
                        <div>
                            <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">Mesa {{ cobro.mesa?.numero }}</p>
                            <p style="font-size:13px; color:#94A3B8; margin:4px 0 0;">{{ formatFecha(cobro.created_at) }} · {{ cobro.metodo_pago }}</p>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <p style="font-size:18px; font-weight:800; color:#14B8A6; margin:0;">S/ {{ Number(cobro.total).toFixed(2) }}</p>
                        <p v-if="cobro.vuelto > 0" style="font-size:13px; color:#94A3B8; margin:4px 0 0;">Vuelto: S/ {{ Number(cobro.vuelto).toFixed(2) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>