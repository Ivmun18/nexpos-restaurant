<template>
    <AppLayout title="Emitir Nota de Crédito" subtitle="Anular, descontar o devolver un comprobante">

        <div style="max-width:800px; margin:0 auto;">

            <!-- Información del comprobante original -->
            <div style="background:white; border-radius:16px; padding:24px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 16px;">
                    📋 Comprobante a Afectar
                </h3>

                <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:16px;">
                    <div>
                        <p style="font-size:12px; color:#64748B; margin:0;">Comprobante</p>
                        <p style="font-size:18px; font-weight:700; color:#1E293B; margin:4px 0 0;">
                            {{ comprobante.tipo_comprobante_nombre }} {{ comprobante.numero_completo }}
                        </p>
                    </div>
                    <div>
                        <p style="font-size:12px; color:#64748B; margin:0;">Total</p>
                        <p style="font-size:24px; font-weight:700; color:#14B8A6; margin:4px 0 0;">
                            S/ {{ formatNumber(comprobante.total) }}
                        </p>
                    </div>
                    <div>
                        <p style="font-size:12px; color:#64748B; margin:0;">Cliente</p>
                        <p style="font-size:14px; font-weight:600; color:#1E293B; margin:4px 0 0;">
                            {{ comprobante.cliente_nombre }}
                        </p>
                    </div>
                    <div v-if="comprobante.caja && comprobante.caja.mesa">
                        <p style="font-size:12px; color:#64748B; margin:0;">Mesa</p>
                        <p style="font-size:14px; font-weight:600; color:#1E293B; margin:4px 0 0;">
                            Mesa {{ comprobante.caja.mesa.numero }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Formulario Nota de Crédito -->
            <div style="background:white; border-radius:16px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px;">
                    🧾 Datos de la Nota de Crédito
                </h3>

                <form @submit.prevent="emitirNotaCredito">
                    <div style="display:flex; flex-direction:column; gap:16px;">

                        <!-- Motivo -->
                        <div>
                            <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:8px;">
                                Motivo *
                            </label>
                            <select v-model="form.motivo_codigo" required
                                style="width:100%; padding:12px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B;">
                                <option value="01">Anulación de la operación</option>
                                <option value="02">Descuento</option>
                                <option value="03">Devolución</option>
                            </select>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:8px;">
                                Descripción del Motivo *
                            </label>
                            <textarea v-model="form.motivo_descripcion" required maxlength="200" rows="4"
                                placeholder="Ej: El cliente solicitó la anulación del comprobante por error en el pedido"
                                style="width:100%; padding:12px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B; resize:vertical; box-sizing:border-box; font-family:inherit;"></textarea>
                        </div>

                        <!-- Botones -->
                        <div style="display:flex; gap:12px; margin-top:8px;">
                            <button type="button" @click="$inertia.visit(`/comprobantes/${comprobante.id}`)"
                                style="flex:1; padding:14px; background:#F1F5F9; color:#64748B; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="procesando"
                                style="flex:1; padding:14px; background:linear-gradient(135deg,#F59E0B,#D97706); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                                {{ procesando ? 'Emitiendo...' : '🧾 Emitir Nota de Crédito' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>

    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    comprobante: Object,
})

const procesando = ref(false)

const form = ref({
    motivo_codigo: '01',
    motivo_descripcion: '',
})

const formatNumber = (num) => {
    return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num || 0)
}

const emitirNotaCredito = () => {
    procesando.value = true
    router.post(`/comprobantes/${props.comprobante.id}/nota-credito`, form.value, {
        onFinish: () => procesando.value = false,
    })
}
</script>
