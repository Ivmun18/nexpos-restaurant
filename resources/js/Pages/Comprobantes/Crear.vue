<template>
    <AppLayout title="Emitir Comprobante" subtitle="Boleta o Factura Electrónica">
        
        <div style="max-width:800px; margin:0 auto;">
            
            <!-- Información de la venta -->
            <div style="background:white; border-radius:16px; padding:24px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 16px;">
                    📋 Detalle de la Venta
                </h3>
                
                <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:16px;">
                    <div>
                        <p style="font-size:12px; color:#64748B; margin:0;">Mesa</p>
                        <p style="font-size:18px; font-weight:700; color:#1E293B; margin:4px 0 0;">
                            Mesa {{ caja.mesa.numero }}
                        </p>
                    </div>
                    <div>
                        <p style="font-size:12px; color:#64748B; margin:0;">Total a Facturar</p>
                        <p style="font-size:24px; font-weight:700; color:#14B8A6; margin:4px 0 0;">
                            S/ {{ formatNumber(caja.total) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Selector de tipo -->
            <div style="background:white; border-radius:16px; padding:24px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 16px;">
                    📄 Tipo de Comprobante
                </h3>
                
                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">
                    <button @click="tipoComprobante = 'boleta'"
                        :style="tipoButtonStyle(tipoComprobante === 'boleta')">
                        <div style="font-size:32px; margin-bottom:8px;">🧾</div>
                        <div style="font-size:16px; font-weight:700;">BOLETA</div>
                        <div style="font-size:12px; margin-top:4px; opacity:0.8;">Con datos del cliente</div>
                    </button>
                    
                    <button @click="emitirSinDatos"
                        :style="tipoButtonStyle(tipoComprobante === 'sin_datos', '#F0FDFA', '#14B8A6')">
                        <div style="font-size:32px; margin-bottom:8px;">⚡</div>
                        <div style="font-size:16px; font-weight:700;">BOLETA RÁPIDA</div>
                        <div style="font-size:12px; margin-top:4px; opacity:0.8;">Sin datos del cliente</div>
                    </button>

                    <button @click="tipoComprobante = 'factura'"
                        :style="tipoButtonStyle(tipoComprobante === 'factura')">
                        <div style="font-size:32px; margin-bottom:8px;">📑</div>
                        <div style="font-size:16px; font-weight:700;">FACTURA</div>
                        <div style="font-size:12px; margin-top:4px; opacity:0.8;">Para empresas con RUC</div>
                    </button>
                </div>
            </div>

            <!-- Formulario Boleta -->
            <div v-if="tipoComprobante === 'boleta'" 
                style="background:white; border-radius:16px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px;">
                    👤 Datos del Cliente
                </h3>
                
                <form @submit.prevent="emitirBoleta">
                    <div style="display:flex; flex-direction:column; gap:16px;">
                        
                        <!-- Tipo documento -->
                        <div>
                            <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:8px;">
                                Tipo de Documento *
                            </label>
                            <select v-model="formBoleta.cliente_tipo_documento" required
                                style="width:100%; padding:12px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B;">
                                <option value="1">DNI</option>
                                <option value="6">RUC</option>
                            </select>
                        </div>

                        <!-- Número documento -->
                        <div>
                            <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:8px;">
                                Número de Documento *
                            </label>
                            <input type="text" v-model="formBoleta.cliente_documento" required
                                :maxlength="formBoleta.cliente_tipo_documento === '1' ? 8 : 11"
                                placeholder="Ej: 12345678"
                                style="width:100%; padding:12px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B;">
                        </div>

                        <!-- Nombre -->
                        <div>
                            <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:8px;">
                                Nombre Completo *
                            </label>
                            <input type="text" v-model="formBoleta.cliente_nombre" required
                                placeholder="Ej: Juan Pérez"
                                style="width:100%; padding:12px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B;">
                        </div>

                        <!-- Email -->
                        <div>
                            <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:8px;">
                                Email (opcional)
                            </label>
                            <input type="email" v-model="formBoleta.cliente_email"
                                placeholder="Ej: cliente@email.com"
                                style="width:100%; padding:12px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B;">
                            <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">
                                Si ingresas un email, el comprobante se enviará automáticamente
                            </p>
                        </div>

                        <!-- Botones -->
                        <div style="display:flex; gap:12px; margin-top:8px;">
                            <button type="button" @click="$inertia.visit('/caja-restaurante/' + caja.mesa_id)"
                                style="flex:1; padding:14px; background:#F1F5F9; color:#64748B; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="procesando"
                                style="flex:1; padding:14px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                                {{ procesando ? 'Emitiendo...' : '🧾 Emitir Boleta' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Formulario Factura -->
            <div v-if="tipoComprobante === 'factura'" 
                style="background:white; border-radius:16px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px;">
                    🏢 Datos de la Empresa
                </h3>
                
                <form @submit.prevent="emitirFactura">
                    <div style="display:flex; flex-direction:column; gap:16px;">
                        
                        <!-- RUC -->
                        <div>
                            <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:8px;">
                                RUC *
                            </label>
                            <input type="text" v-model="formFactura.cliente_ruc" required
                                maxlength="11"
                                placeholder="Ej: 20123456789"
                                style="width:100%; padding:12px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B;">
                        </div>

                        <!-- Razón Social -->
                        <div>
                            <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:8px;">
                                Razón Social *
                            </label>
                            <input type="text" v-model="formFactura.cliente_razon_social" required
                                placeholder="Ej: EMPRESA SAC"
                                style="width:100%; padding:12px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B;">
                        </div>

                        <!-- Dirección -->
                        <div>
                            <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:8px;">
                                Dirección Fiscal *
                            </label>
                            <input type="text" v-model="formFactura.cliente_direccion" required
                                placeholder="Ej: Av. Principal 123, Lima"
                                style="width:100%; padding:12px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B;">
                        </div>

                        <!-- Email -->
                        <div>
                            <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:8px;">
                                Email (opcional)
                            </label>
                            <input type="email" v-model="formFactura.cliente_email"
                                placeholder="Ej: empresa@email.com"
                                style="width:100%; padding:12px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B;">
                            <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">
                                Si ingresas un email, el comprobante se enviará automáticamente
                            </p>
                        </div>

                        <!-- Botones -->
                        <div style="display:flex; gap:12px; margin-top:8px;">
                            <button type="button" @click="$inertia.visit('/caja-restaurante/' + caja.mesa_id)"
                                style="flex:1; padding:14px; background:#F1F5F9; color:#64748B; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="procesando"
                                style="flex:1; padding:14px; background:linear-gradient(135deg,#3B82F6,#2563EB); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                                {{ procesando ? 'Emitiendo...' : '📑 Emitir Factura' }}
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
    caja: Object,
})

const tipoComprobante = ref('boleta')
const procesando = ref(false)

const formBoleta = ref({
    cliente_tipo_documento: '1',
    cliente_documento: '',
    cliente_nombre: '',
    cliente_email: '',
})

const formFactura = ref({
    cliente_ruc: '',
    cliente_razon_social: '',
    cliente_direccion: '',
    cliente_email: '',
})

const tipoButtonStyle = (activo) => {
    return {
        width: '100%',
        padding: '20px',
        background: activo ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : 'white',
        color: activo ? 'white' : '#64748B',
        border: activo ? 'none' : '2px solid #E2E8F0',
        borderRadius: '12px',
        cursor: 'pointer',
        transition: 'all 0.2s',
        textAlign: 'center',
    }
}

const formatNumber = (num) => {
    return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num || 0)
}

const emitirSinDatos = () => {
    router.post(`/comprobantes/caja/${props.caja.id}/boleta`, {
        cliente_tipo_documento: '1',
        cliente_documento: '00000000',
        cliente_nombre: 'CLIENTE VARIOS',
        cliente_email: '',
    }, {
        onSuccess: () => {},
        onError: (e) => alert('Error: ' + JSON.stringify(e))
    })
}

const emitirBoleta = () => {
    procesando.value = true
    router.post(`/comprobantes/caja/${props.caja.id}/boleta`, formBoleta.value, {
        onFinish: () => procesando.value = false,
    })
}

const emitirFactura = () => {
    procesando.value = true
    router.post(`/comprobantes/caja/${props.caja.id}/factura`, formFactura.value, {
        onFinish: () => procesando.value = false,
    })
}
</script>
