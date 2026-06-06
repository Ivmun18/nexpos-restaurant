<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    cajaAbierta: Object,
    historial: Array,
    pagosHoy: Array,
})

const montoInicial = ref('')
const montoFinal   = ref('')
const observaciones = ref('')
const abriendo     = ref(false)
const cerrando     = ref(false)

const totalEfectivo = () => props.pagosHoy?.filter(p => p.metodo_pago === 'efectivo').reduce((s, p) => s + Number(p.monto), 0) ?? 0
const totalYape     = () => props.pagosHoy?.filter(p => p.metodo_pago === 'yape').reduce((s, p) => s + Number(p.monto), 0) ?? 0
const totalPlin     = () => props.pagosHoy?.filter(p => p.metodo_pago === 'plin').reduce((s, p) => s + Number(p.monto), 0) ?? 0
const totalTarjeta  = () => props.pagosHoy?.filter(p => p.metodo_pago === 'tarjeta').reduce((s, p) => s + Number(p.monto), 0) ?? 0
const totalTransf   = () => props.pagosHoy?.filter(p => p.metodo_pago === 'transferencia').reduce((s, p) => s + Number(p.monto), 0) ?? 0
const totalDia      = () => props.pagosHoy?.reduce((s, p) => s + Number(p.monto), 0) ?? 0
const efectivoEsperado = () => Number(props.cajaAbierta?.monto_inicial ?? 0) + totalEfectivo()
const diferencia    = () => Number(montoFinal.value || 0) - efectivoEsperado()

function abrirCaja() {
    abriendo.value = true
    router.post('/hotel/caja/abrir', { monto_inicial: montoInicial.value }, {
        onFinish: () => abriendo.value = false
    })
}

function cerrarCaja() {
    if (!confirm('¿Confirmar cierre de caja?')) return
    cerrando.value = true
    router.post('/hotel/caja/' + props.cajaAbierta.id + '/cerrar', {
        monto_final: montoFinal.value,
        observaciones: observaciones.value,
    }, { onFinish: () => cerrando.value = false })
}

const fmt = (n) => Number(n || 0).toFixed(2)
</script>

<template>
    <AppLayout title="🏨 Caja Hotel">
        <div style="max-width:860px; margin:0 auto;">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
                <div>
                    <h2 style="font-size:22px; font-weight:700; color:#1E293B; margin:0;">💰 Caja Hotel</h2>
                    <p style="color:#64748B; margin:4px 0 0; font-size:14px;">Apertura y cierre de caja diaria</p>
                </div>
                <div :style="{padding:'8px 16px', borderRadius:'10px', fontWeight:'700', fontSize:'14px',
                    background: cajaAbierta ? '#D1FAE5' : '#FEE2E2',
                    color: cajaAbierta ? '#065F46' : '#991B1B'}">
                    {{ cajaAbierta ? '🟢 Caja abierta' : '🔴 Caja cerrada' }}
                </div>
            </div>

            <!-- SIN CAJA ABIERTA -->
            <div v-if="!cajaAbierta" style="background:white; border-radius:16px; padding:28px; border:1px solid #E2E8F0; margin-bottom:20px; text-align:center;">
                <p style="font-size:48px; margin:0 0 12px;">🏦</p>
                <h3 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 8px;">No hay caja abierta</h3>
                <p style="color:#64748B; margin:0 0 20px;">Ingresa el monto inicial para abrir la caja del día</p>
                <div style="display:flex; gap:12px; justify-content:center; align-items:center; flex-wrap:wrap;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <label style="font-size:14px; font-weight:600; color:#374151;">Monto inicial (S/)</label>
                        <input v-model="montoInicial" type="number" step="0.01" min="0" placeholder="0.00"
                            style="padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; width:140px; outline:none;"/>
                    </div>
                    <button @click="abrirCaja" :disabled="abriendo"
                        style="padding:12px 24px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer;">
                        {{ abriendo ? 'Abriendo...' : '✅ Abrir caja' }}
                    </button>
                </div>
            </div>

            <!-- CAJA ABIERTA -->
            <div v-if="cajaAbierta">

                <!-- Info apertura -->
                <div style="background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:16px; padding:20px 24px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                    <div>
                        <p style="color:rgba(255,255,255,0.8); font-size:13px; margin:0 0 4px;">Abierta por {{ cajaAbierta.usuario?.name }}</p>
                        <p style="color:white; font-size:15px; font-weight:700; margin:0;">
                            {{ new Date(cajaAbierta.apertura_at).toLocaleString('es-PE') }}
                        </p>
                    </div>
                    <div style="text-align:right;">
                        <p style="color:rgba(255,255,255,0.8); font-size:13px; margin:0 0 4px;">Monto inicial</p>
                        <p style="color:white; font-size:22px; font-weight:800; margin:0;">S/ {{ fmt(cajaAbierta.monto_inicial) }}</p>
                    </div>
                </div>

                <!-- Resumen ingresos del día -->
                <div style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0; margin-bottom:16px;">
                    <h3 style="font-size:15px; font-weight:700; color:#1E293B; margin:0 0 14px;">📊 Ingresos del día</h3>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px,1fr)); gap:10px; margin-bottom:14px;">
                        <div style="background:#F0FDFA; border-radius:10px; padding:12px; text-align:center;">
                            <p style="font-size:11px; color:#0F766E; margin:0 0 4px; font-weight:600;">💵 Efectivo</p>
                            <p style="font-size:16px; font-weight:800; color:#0F766E; margin:0;">S/ {{ fmt(totalEfectivo()) }}</p>
                        </div>
                        <div style="background:#EFF6FF; border-radius:10px; padding:12px; text-align:center;">
                            <p style="font-size:11px; color:#1D4ED8; margin:0 0 4px; font-weight:600;">📱 Yape</p>
                            <p style="font-size:16px; font-weight:800; color:#1D4ED8; margin:0;">S/ {{ fmt(totalYape()) }}</p>
                        </div>
                        <div style="background:#F5F3FF; border-radius:10px; padding:12px; text-align:center;">
                            <p style="font-size:11px; color:#6D28D9; margin:0 0 4px; font-weight:600;">📲 Plin</p>
                            <p style="font-size:16px; font-weight:800; color:#6D28D9; margin:0;">S/ {{ fmt(totalPlin()) }}</p>
                        </div>
                        <div style="background:#FFF7ED; border-radius:10px; padding:12px; text-align:center;">
                            <p style="font-size:11px; color:#C2410C; margin:0 0 4px; font-weight:600;">💳 Tarjeta</p>
                            <p style="font-size:16px; font-weight:800; color:#C2410C; margin:0;">S/ {{ fmt(totalTarjeta()) }}</p>
                        </div>
                        <div style="background:#F0FDF4; border-radius:10px; padding:12px; text-align:center;">
                            <p style="font-size:11px; color:#15803D; margin:0 0 4px; font-weight:600;">🏦 Transfer.</p>
                            <p style="font-size:16px; font-weight:800; color:#15803D; margin:0;">S/ {{ fmt(totalTransf()) }}</p>
                        </div>
                        <div style="background:#1E293B; border-radius:10px; padding:12px; text-align:center;">
                            <p style="font-size:11px; color:rgba(255,255,255,0.7); margin:0 0 4px; font-weight:600;">TOTAL</p>
                            <p style="font-size:16px; font-weight:800; color:white; margin:0;">S/ {{ fmt(totalDia()) }}</p>
                        </div>
                    </div>

                    <!-- Movimientos -->
                    <div v-if="pagosHoy?.length" style="border-top:1px solid #F1F5F9; padding-top:12px;">
                        <p style="font-size:12px; font-weight:600; color:#64748B; margin:0 0 8px;">Detalle de pagos</p>
                        <div style="max-height:200px; overflow-y:auto; display:flex; flex-direction:column; gap:4px;">
                            <div v-for="p in pagosHoy" :key="p.id"
                                style="display:flex; justify-content:space-between; align-items:center; padding:6px 10px; background:#F8FAFC; border-radius:8px; font-size:12px;">
                                <span style="color:#475569; font-weight:500; text-transform:capitalize;">{{ p.metodo_pago }}</span>
                                <span style="color:#1E293B; font-weight:700;">S/ {{ fmt(p.monto) }}</span>
                            </div>
                        </div>
                    </div>
                    <p v-else style="text-align:center; color:#94A3B8; font-size:13px; margin:0;">Sin pagos registrados hoy</p>
                </div>

                <!-- Cierre de caja -->
                <div style="background:white; border-radius:16px; padding:20px; border:2px solid #FECACA;">
                    <h3 style="font-size:15px; font-weight:700; color:#DC2626; margin:0 0 14px;">🔐 Cerrar caja</h3>
                    <div style="display:grid; gap:12px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:#F8FAFC; border-radius:10px;">
                            <span style="font-size:13px; color:#64748B; font-weight:600;">Monto inicial</span>
                            <span style="font-size:14px; font-weight:700; color:#1E293B;">S/ {{ fmt(cajaAbierta.monto_inicial) }}</span>
                        </div>
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:#F8FAFC; border-radius:10px;">
                            <span style="font-size:13px; color:#64748B; font-weight:600;">+ Efectivo cobrado</span>
                            <span style="font-size:14px; font-weight:700; color:#16A34A;">S/ {{ fmt(totalEfectivo()) }}</span>
                        </div>
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:#EFF6FF; border-radius:10px;">
                            <span style="font-size:13px; color:#1D4ED8; font-weight:600;">= Efectivo esperado en caja</span>
                            <span style="font-size:16px; font-weight:800; color:#1D4ED8;">S/ {{ fmt(efectivoEsperado()) }}</span>
                        </div>

                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Monto real en caja (S/)</label>
                            <input v-model="montoFinal" type="number" step="0.01" min="0" placeholder="Contar el dinero físico..."
                                style="width:100%; padding:12px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; box-sizing:border-box; outline:none;"/>
                        </div>

                        <div v-if="montoFinal" :style="{
                            padding:'12px 14px', borderRadius:'10px', display:'flex', justifyContent:'space-between', alignItems:'center',
                            background: diferencia() >= 0 ? '#D1FAE5' : '#FEE2E2',
                            border: '1px solid ' + (diferencia() >= 0 ? '#6EE7B7' : '#FECACA')
                        }">
                            <span style="font-size:13px; font-weight:600;">Diferencia</span>
                            <span :style="{fontSize:'18px', fontWeight:'800', color: diferencia() >= 0 ? '#065F46' : '#DC2626'}">
                                {{ diferencia() >= 0 ? '+' : '' }}S/ {{ fmt(diferencia()) }}
                            </span>
                        </div>

                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Observaciones (opcional)</label>
                            <textarea v-model="observaciones" rows="2" placeholder="Notas del cierre..."
                                style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:13px; box-sizing:border-box; outline:none; resize:none;"></textarea>
                        </div>

                        <button @click="cerrarCaja" :disabled="cerrando || !montoFinal"
                            style="width:100%; padding:14px; background:#DC2626; color:white; border:none; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer;"
                            :style="{ opacity: !montoFinal ? 0.5 : 1 }">
                            {{ cerrando ? 'Cerrando...' : '🔐 Confirmar cierre de caja' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Historial estilo farmacia -->
            <div v-if="historial?.length" style="margin-top:20px; background:white; border-radius:16px; padding:24px; border:1px solid #E2E8F0;">
                <p style="font-size:17px; font-weight:800; color:#1E293B; margin:0 0 16px;">📋 Historial de cajas</p>
                <div v-for="c in historial" :key="c.id"
                    style="border:1px solid #E2E8F0; border-radius:12px; padding:16px; margin-bottom:12px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                        <div>
                            <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0;">
                                {{ new Date(c.apertura_at).toLocaleDateString('es-PE', {weekday:'long', day:'2-digit', month:'long'}) }}
                            </p>
                            <p style="font-size:12px; color:#64748B; margin:2px 0 0;">{{ c.usuario?.name }}</p>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span :style="{
                                padding:'4px 10px', borderRadius:'8px', fontSize:'12px', fontWeight:'700',
                                background: Number(c.diferencia) === 0 ? '#D1FAE5' : Number(c.diferencia) > 0 ? '#DBEAFE' : '#FEE2E2',
                                color: Number(c.diferencia) === 0 ? '#065F46' : Number(c.diferencia) > 0 ? '#1D4ED8' : '#DC2626'
                            }">
                                {{ Number(c.diferencia) >= 0 ? '+' : '' }}S/ {{ fmt(c.diferencia) }}
                            </span>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(110px,1fr)); gap:8px; margin-bottom:10px;">
                        <div style="background:#F8FAFC; border-radius:8px; padding:10px; border:1px solid #E2E8F0;">
                            <p style="font-size:11px; color:#94A3B8; margin:0;">💵 Efectivo</p>
                            <p style="font-size:14px; font-weight:700; color:#1E293B; margin:4px 0 0;">S/ {{ fmt(c.total_efectivo) }}</p>
                        </div>
                        <div style="background:#F8FAFC; border-radius:8px; padding:10px; border:1px solid #E2E8F0;">
                            <p style="font-size:11px; color:#94A3B8; margin:0;">📱 Yape/Plin</p>
                            <p style="font-size:14px; font-weight:700; color:#1E293B; margin:4px 0 0;">S/ {{ fmt(Number(c.total_yape||0) + Number(c.total_plin||0)) }}</p>
                        </div>
                        <div style="background:#F8FAFC; border-radius:8px; padding:10px; border:1px solid #E2E8F0;">
                            <p style="font-size:11px; color:#94A3B8; margin:0;">💳 Tarjeta</p>
                            <p style="font-size:14px; font-weight:700; color:#1E293B; margin:4px 0 0;">S/ {{ fmt(c.total_tarjeta) }}</p>
                        </div>
                        <div style="background:#F8FAFC; border-radius:8px; padding:10px; border:1px solid #E2E8F0;">
                            <p style="font-size:11px; color:#94A3B8; margin:0;">🏦 Transfer.</p>
                            <p style="font-size:14px; font-weight:700; color:#1E293B; margin:4px 0 0;">S/ {{ fmt(c.total_tarjeta) }}</p>
                        </div>
                        <div style="background:#F0FDFA; border-radius:8px; padding:10px; border:1px solid #CCFBF1;">
                            <p style="font-size:11px; color:#0F766E; margin:0;">Total cobrado</p>
                            <p style="font-size:14px; font-weight:700; color:#0F766E; margin:4px 0 0;">S/ {{ fmt(c.total_ventas) }}</p>
                        </div>
                        <div style="background:#F8FAFC; border-radius:8px; padding:10px; border:1px solid #E2E8F0;">
                            <p style="font-size:11px; color:#94A3B8; margin:0;">Monto inicial</p>
                            <p style="font-size:14px; font-weight:700; color:#1E293B; margin:4px 0 0;">S/ {{ fmt(c.monto_inicial) }}</p>
                        </div>
                        <div style="background:#F8FAFC; border-radius:8px; padding:10px; border:1px solid #E2E8F0;">
                            <p style="font-size:11px; color:#94A3B8; margin:0;">Monto final</p>
                            <p style="font-size:14px; font-weight:700; color:#1E293B; margin:4px 0 0;">S/ {{ fmt(c.monto_final) }}</p>
                        </div>
                    </div>
                    <p style="font-size:11px; color:#94A3B8; margin:0;">
                        Apertura: {{ c.apertura_at }} | Cierre: {{ c.cierre_at || 'Abierta' }}
                    </p>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
