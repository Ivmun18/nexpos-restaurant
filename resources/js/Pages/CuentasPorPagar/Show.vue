<template>
    <AppLayout :title="`Cuenta #${cuenta.id}`" :subtitle="cuenta.proveedor.razon_social">
        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 1.5rem; align-items: start;">
            <!-- Detalles -->
            <div style="background: white; border-radius: 10px; border: 1px solid #E2E8F0; padding: 1.5rem;">
                <h3 style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 0 0 1.5rem;">Información General</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <p style="font-size: 11px; color: #94A3B8; margin: 0;">Proveedor</p>
                        <p style="font-size: 13px; font-weight: 600; color: #1E293B; margin: 4px 0 0;">{{ cuenta.proveedor.razon_social }}</p>
                    </div>
                    <div>
                        <p style="font-size: 11px; color: #94A3B8; margin: 0;">Estado</p>
                        <p :style="getEstadoStyle(cuenta.estado)" style="margin: 4px 0 0; display: inline-block;">{{ cuenta.estado.toUpperCase() }}</p>
                    </div>
                    <div>
                        <p style="font-size: 11px; color: #94A3B8; margin: 0;">Fecha Emisión</p>
                        <p style="font-size: 13px; font-weight: 600; color: #1E293B; margin: 4px 0 0;">{{ formatDate(cuenta.fecha_emision) }}</p>
                    </div>
                    <div>
                        <p style="font-size: 11px; color: #94A3B8; margin: 0;">Fecha Vencimiento</p>
                        <p style="font-size: 13px; font-weight: 600; color: #1E293B; margin: 4px 0 0;">{{ formatDate(cuenta.fecha_vencimiento) }}</p>
                    </div>
                </div>

                <!-- Totales -->
                <div style="background: #F8FAFC; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 13px; color: #64748B;">Monto Total</span>
                        <span style="font-size: 13px; font-weight: 700; color: #1E293B;">S/ {{ Number(cuenta.monto_total).toFixed(2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 13px; color: #64748B;">Monto Pagado</span>
                        <span style="font-size: 13px; font-weight: 700; color: #16A34A;">S/ {{ Number(cuenta.monto_pagado).toFixed(2) }}</span>
                    </div>
                    <div style="border-top: 1px solid #E2E8F0; padding-top: 8px; display: flex; justify-content: space-between;">
                        <span style="font-size: 13px; font-weight: 700; color: #1E293B;">Pendiente</span>
                        <span style="font-size: 13px; font-weight: 700; color: #DC2626;">S/ {{ Number(cuenta.monto_pendiente).toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Progreso de pago -->
                <div style="margin-bottom: 1.5rem;">
                    <p style="font-size: 12px; color: #64748B; margin: 0 0 8px;">Progreso de Pago</p>
                    <div style="width: 100%; height: 8px; background: #E2E8F0; border-radius: 4px; overflow: hidden;">
                        <div :style="{
                            width: (cuenta.monto_pagado / cuenta.monto_total * 100) + '%',
                            height: '100%',
                            background: '#16A34A',
                            transition: 'width 0.3s'
                        }"></div>
                    </div>
                    <p style="font-size: 11px; color: #94A3B8; margin: 4px 0 0;">{{ Math.round(cuenta.monto_pagado / cuenta.monto_total * 100) }}% pagado</p>
                </div>

                <!-- Historial de pagos -->
                <h3 style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 2rem 0 1rem;">Historial de Pagos</h3>
                
                <div v-if="cuenta.pagos.length === 0" style="padding: 1rem; text-align: center; color: #94A3B8; font-size: 13px;">
                    Sin pagos registrados
                </div>

                <div v-else style="display: flex; flex-direction: column; gap: 8px;">
                    <div v-for="pago in cuenta.pagos" :key="pago.id"
                        style="padding: 12px; background: #F8FAFC; border-radius: 8px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <p style="font-size: 13px; font-weight: 600; color: #1E293B; margin: 0;">
                                S/ {{ Number(pago.monto).toFixed(2) }}
                            </p>
                            <p style="font-size: 11px; color: #94A3B8; margin: 0;;">{{ formatDate(pago.fecha_pago) }}</p>
                        </div>
                        <p style="font-size: 12px; color: #64748B; margin: 0;">
                            {{ pago.forma_pago }} {{ pago.numero_comprobante ? '- ' + pago.numero_comprobante : '' }}
                        </p>
                        <p v-if="pago.observaciones" style="font-size: 11px; color: #94A3B8; margin: 4px 0 0;">{{ pago.observaciones }}</p>
                    </div>
                </div>
            </div>

            <!-- Panel de Pago -->
            <div style="background: white; border-radius: 10px; border: 1px solid #E2E8F0; padding: 1.5rem; position: sticky; top: 80px;">
                <h3 style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 0 0 1rem;">Registrar Pago</h3>
                
                <form @submit.prevent="guardarPago" style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <label style="font-size: 12px; color: #64748B; display: block; margin-bottom: 4px;">Monto *</label>
                        <input v-model.number="formularioPago.monto" type="number" step="0.01" min="0.01" :max="Number(cuenta.monto_pendiente)"
                            style="width: 100%; padding: 10px; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 13px;" required />
                        <p style="font-size: 11px; color: #94A3B8; margin: 4px 0 0;">Máximo: S/ {{ Number(cuenta.monto_pendiente).toFixed(2) }}</p>
                    </div>

                    <div>
                        <label style="font-size: 12px; color: #64748B; display: block; margin-bottom: 4px;">Fecha de Pago *</label>
                        <input v-model="formularioPago.fecha_pago" type="date"
                            style="width: 100%; padding: 10px; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 13px;" required />
                    </div>

                    <div>
                        <label style="font-size: 12px; color: #64748B; display: block; margin-bottom: 4px;">Forma de Pago *</label>
                        <select v-model="formularioPago.forma_pago"
                            style="width: 100%; padding: 10px; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 13px;" required>
                            <option value="">-- Seleccionar --</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="cheque">Cheque</option>
                            <option value="tarjeta">Tarjeta</option>
                        </select>
                    </div>

                    <div>
                        <label style="font-size: 12px; color: #64748B; display: block; margin-bottom: 4px;">Comprobante</label>
                        <input v-model="formularioPago.numero_comprobante" type="text" placeholder="N° cheque, transferencia, etc."
                            style="width: 100%; padding: 10px; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 13px;" />
                    </div>

                    <div>
                        <label style="font-size: 12px; color: #64748B; display: block; margin-bottom: 4px;">Observaciones</label>
                        <textarea v-model="formularioPago.observaciones" rows="2"
                            style="width: 100%; padding: 10px; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 13px; resize: none;"></textarea>
                    </div>

                    <button type="submit" :disabled="procesando"
                        style="width: 100%; padding: 10px; background: #2563EB; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer;">
                        {{ procesando ? '⏳ Guardando...' : '✓ Registrar Pago' }}
                    </button>
                </form>

                <p v-if="error" style="color: #DC2626; font-size: 12px; margin-top: 1rem; background: #FEE2E2; padding: 8px; border-radius: 6px;">
                    {{ error }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, defineProps } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    cuenta: Object,
})

const procesando = ref(false)
const error = ref('')

const formularioPago = ref({
    monto: '',
    fecha_pago: new Date().toISOString().split('T')[0],
    forma_pago: '',
    numero_comprobante: '',
    observaciones: '',
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-PE')
}

const getEstadoStyle = (estado) => {
    const estilos = {
        'pendiente': 'background: #FEE2E2; color: #991B1B; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;',
        'parcial': 'background: #FEF3C7; color: #92400E; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;',
        'pagado': 'background: #DCFCE7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;',
        'vencido': 'background: #FEE2E2; color: #991B1B; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;',
    }
    return estilos[estado] || ''
}

const guardarPago = () => {
    error.value = ''
    
    if (!formularioPago.value.monto) {
        error.value = 'Ingresa el monto del pago.'
        return
    }
    
    if (!formularioPago.value.forma_pago) {
        error.value = 'Selecciona una forma de pago.'
        return
    }

    procesando.value = true
    router.post(`/cuentas-por-pagar/${props.cuenta.id}/pago`, formularioPago.value, {
        onError: () => {
            error.value = 'Error al registrar el pago.'
            procesando.value = false
        }
    })
}
</script>