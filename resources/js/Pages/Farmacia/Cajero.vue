<template>
    <AppLayout title="Panel Cajero" subtitle="Ventas pendientes de cobro">

        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <div>
                <p style="font-size:24px; font-weight:800; color:#1E293B; margin:0;">💰 Ventas Pendientes</p>
                <p style="font-size:14px; color:#94A3B8; margin:4px 0 0;">{{ ventas_pendientes.length }} ventas por cobrar</p>
            </div>
        </div>

        <!-- Sin pendientes -->
        <div v-if="!ventas_pendientes.length"
            style="text-align:center; padding:80px; background:white; border-radius:20px; border:1px solid #E2E8F0;">
            <p style="font-size:48px; margin:0 0 12px;">✅</p>
            <p style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 8px;">No hay ventas pendientes</p>
            <p style="font-size:14px; color:#94A3B8;">Todas las ventas han sido cobradas</p>
        </div>

        <!-- Lista de ventas pendientes -->
        <div style="display:flex; flex-direction:column; gap:16px;">
            <div v-for="venta in ventas_pendientes" :key="venta.id"
                style="background:white; border-radius:16px; border:1px solid #E2E8F0; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.05);">

                <!-- Header venta -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <div>
                        <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">{{ venta.numero_completo }}</p>
                        <p style="font-size:13px; color:#94A3B8; margin:4px 0 0;">{{ venta.hora_emision }} — {{ venta.cliente_razon_social || 'Cliente varios' }}</p>
                    </div>
                    <div style="text-align:right;">
                        <p style="font-size:22px; font-weight:800; color:#14B8A6; margin:0;">S/ {{ Number(venta.total).toFixed(2) }}</p>
                        <span style="font-size:11px; font-weight:700; color:#F59E0B; background:#FEF3C7; padding:2px 8px; border-radius:20px;">⏳ PENDIENTE</span>
                    </div>
                </div>

                <!-- Detalle productos -->
                <div style="background:#F8FAFC; border-radius:10px; padding:12px; margin-bottom:16px;">
                    <div v-for="item in venta.detalle" :key="item.id"
                        style="display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px solid #F1F5F9;">
                        <span style="font-size:13px; color:#475569;">{{ item.descripcion }} x{{ item.cantidad }}</span>
                        <span style="font-size:13px; font-weight:600; color:#1E293B;">S/ {{ Number(item.total).toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Cobrar -->
                <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <select v-model="metodoPago[venta.id]"
                        style="padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; flex:1; min-width:150px;">
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="yape">📱 Yape</option>
                        <option value="plin">📲 Plin</option>
                        <option value="tarjeta">💳 Tarjeta</option>
                    </select>
                    <button @click="cobrar(venta)"
                        :disabled="procesando[venta.id]"
                        style="padding:10px 24px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; flex:1; min-width:150px;">
                        {{ procesando[venta.id] ? '⏳ Procesando...' : '✅ Cobrar S/ ' + Number(venta.total).toFixed(2) }}
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    ventas_pendientes: { type: Array, default: () => [] },
    caja_abierta:      { type: Object, default: () => ({}) },
})

const metodoPago = ref({})
const procesando = ref({})

// Inicializar método de pago por defecto
props.ventas_pendientes.forEach(v => {
    metodoPago.value[v.id] = 'efectivo'
})

const cobrar = (venta) => {
    procesando.value[venta.id] = true
    router.post(`/farmacia/cajero/${venta.id}/cobrar`, {
        metodo_pago: metodoPago.value[venta.id]
    }, {
        onSuccess: () => { procesando.value[venta.id] = false },
        onError:   () => { procesando.value[venta.id] = false }
    })
}
</script>
