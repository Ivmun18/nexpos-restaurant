<template>
    <AppLayout title="Caja" subtitle="Control de caja ferretería">

        <!-- Caja cerrada -->
        <div v-if="!cajaAbierta" style="max-width:500px; margin:60px auto; text-align:center;">
            <div style="background:white; border-radius:20px; padding:40px; border:1px solid #E2E8F0;">
                <div style="font-size:60px; margin-bottom:16px;">🏪</div>
                <h2 style="font-size:22px; font-weight:700; color:#1E293B; margin:0 0 8px;">Caja Cerrada</h2>
                <p style="font-size:14px; color:#94A3B8; margin:0 0 24px;">Ingresa el monto inicial para abrir la caja</p>
                <div style="margin-bottom:20px;">
                    <label style="font-size:12px; font-weight:600; color:#64748B;">MONTO INICIAL (S/)</label>
                    <input v-model="montoInicial" type="number" step="0.01" placeholder="0.00"
                        style="width:100%; padding:14px; border:2px solid #E2E8F0; border-radius:10px; font-size:18px; text-align:center; margin-top:8px; box-sizing:border-box; outline:none;">
                </div>
                <button @click="abrirCaja" :disabled="procesando"
                    style="width:100%; padding:14px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:16px; font-weight:700; cursor:pointer;">
                    {{ procesando ? 'Abriendo...' : '🔓 Abrir Caja' }}
                </button>
            </div>
        </div>

        <!-- Caja abierta -->
        <div v-else>
            <!-- Header caja -->
            <div style="background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:16px; padding:24px; margin-bottom:24px; color:white;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <p style="font-size:13px; opacity:0.8; margin:0;">Caja abierta desde</p>
                        <p style="font-size:18px; font-weight:700; margin:4px 0 0;">{{ cajaAbierta.apertura_at }}</p>
                    </div>
                    <div style="text-align:center;">
                        <p style="font-size:13px; opacity:0.8; margin:0;">Monto inicial</p>
                        <p style="font-size:24px; font-weight:700; margin:4px 0 0;">S/ {{ Number(cajaAbierta.monto_inicial).toFixed(2) }}</p>
                    </div>
                    <div style="text-align:right;">
                        <p style="font-size:13px; opacity:0.8; margin:0;">Total ventas hoy</p>
                        <p style="font-size:24px; font-weight:700; margin:4px 0 0;">S/ {{ Number(cajaAbierta.total_ventas || 0).toFixed(2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Stats ventas -->
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
                <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                    <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">💵 Efectivo</p>
                    <p style="font-size:20px; font-weight:700; color:#1E293B; margin:0;">S/ {{ Number(cajaAbierta.total_efectivo || 0).toFixed(2) }}</p>
                </div>
                <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                    <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">📱 Yape/Plin</p>
                    <p style="font-size:20px; font-weight:700; color:#1E293B; margin:0;">S/ {{ (Number(cajaAbierta.total_yape || 0) + Number(cajaAbierta.total_plin || 0)).toFixed(2) }}</p>
                </div>
                <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                    <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">💳 Tarjeta</p>
                    <p style="font-size:20px; font-weight:700; color:#1E293B; margin:0;">S/ {{ Number(cajaAbierta.total_tarjeta || 0).toFixed(2) }}</p>
                </div>
                <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                    <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">🧾 Ventas</p>
                    <p style="font-size:20px; font-weight:700; color:#14B8A6; margin:0;">{{ cajaAbierta.cantidad_ventas || 0 }}</p>
                </div>
            </div>

            <!-- Cierre de caja -->
            <div style="background:white; border-radius:16px; padding:24px; border:1px solid #E2E8F0;">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px;">Cerrar Caja</h3>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">MONTO FINAL EN CAJA (S/)</label>
                        <input v-model="montoCierre" type="number" step="0.01" placeholder="0.00"
                            style="width:100%; padding:12px; border:2px solid #E2E8F0; border-radius:10px; font-size:16px; margin-top:8px; box-sizing:border-box; outline:none;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">DIFERENCIA</label>
                        <div :style="{padding:'12px', borderRadius:'10px', marginTop:'8px', fontSize:'16px', fontWeight:'700', background: diferencia >= 0 ? '#F0FDF4' : '#FEF2F2', color: diferencia >= 0 ? '#166534' : '#DC2626'}">
                            {{ diferencia >= 0 ? '+' : '' }}S/ {{ diferencia.toFixed(2) }}
                        </div>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">OBSERVACIONES</label>
                        <textarea v-model="observacionesCierre" rows="2"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:8px; font-size:14px; box-sizing:border-box; resize:none;"></textarea>
                    </div>
                </div>
                <div style="margin-top:20px; text-align:right;">
                    <button @click="cerrarCaja" :disabled="procesando"
                        style="padding:12px 28px; background:#DC2626; color:white; border:none; border-radius:10px; font-size:15px; font-weight:700; cursor:pointer;">
                        {{ procesando ? 'Cerrando...' : '🔒 Cerrar Caja' }}
                    </button>
                </div>
            </div>

            <!-- Historial -->
            <div v-if="historial.length" style="background:white; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden; margin-top:24px;">
                <div style="padding:16px 20px; border-bottom:1px solid #F0F2F5;">
                    <p style="font-size:15px; font-weight:700; color:#1E293B; margin:0;">Historial de Cajas</p>
                </div>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#F8FAFC;">
                            <th style="padding:12px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">APERTURA</th>
                            <th style="padding:12px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">CIERRE</th>
                            <th style="padding:12px 20px; text-align:right; font-size:12px; font-weight:700; color:#64748B;">INICIAL</th>
                            <th style="padding:12px 20px; text-align:right; font-size:12px; font-weight:700; color:#64748B;">VENTAS</th>
                            <th style="padding:12px 20px; text-align:right; font-size:12px; font-weight:700; color:#64748B;">DIFERENCIA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="h in historial" :key="h.id" style="border-top:1px solid #F1F5F9;">
                            <td style="padding:12px 20px; font-size:13px; color:#475569;">{{ h.apertura_at }}</td>
                            <td style="padding:12px 20px; font-size:13px; color:#475569;">{{ h.cierre_at || '—' }}</td>
                            <td style="padding:12px 20px; text-align:right; font-size:13px; color:#475569;">S/ {{ Number(h.monto_inicial).toFixed(2) }}</td>
                            <td style="padding:12px 20px; text-align:right; font-size:13px; font-weight:700; color:#14B8A6;">S/ {{ Number(h.total_ventas || 0).toFixed(2) }}</td>
                            <td style="padding:12px 20px; text-align:right; font-size:13px; font-weight:700;" :style="{color: h.diferencia >= 0 ? '#166534' : '#DC2626'}">
                                {{ h.diferencia >= 0 ? '+' : '' }}S/ {{ Number(h.diferencia || 0).toFixed(2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    cajaAbierta: { type: Object, default: null },
    historial:   { type: Array,  default: () => [] },
})

const montoInicial       = ref('')
const montoCierre        = ref('')
const observacionesCierre = ref('')
const procesando         = ref(false)

const efectivoEsperado = computed(() => {
    if (!props.cajaAbierta) return 0
    return parseFloat(props.cajaAbierta.monto_inicial || 0) + parseFloat(props.cajaAbierta.total_efectivo || 0)
})

const diferencia = computed(() => {
    if (!montoCierre.value) return 0
    return parseFloat(montoCierre.value) - efectivoEsperado.value
})

const abrirCaja = () => {
    procesando.value = true
    router.post('/ferreteria/caja/abrir', { monto_inicial: montoInicial.value }, {
        onSuccess: () => { procesando.value = false },
        onError:   () => { procesando.value = false }
    })
}

const cerrarCaja = () => {
    if (!confirm('¿Confirmas el cierre de caja?')) return
    procesando.value = true
    router.post(`/ferreteria/caja/${props.cajaAbierta.id}/cerrar`, {
        monto_final:    montoCierre.value,
        observaciones:  observacionesCierre.value,
    }, {
        onSuccess: () => { procesando.value = false },
        onError:   () => { procesando.value = false }
    })
}
</script>
