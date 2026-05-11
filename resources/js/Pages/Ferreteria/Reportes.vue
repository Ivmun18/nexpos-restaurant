<template>
    <AppLayout title="Reportes" subtitle="Análisis y estadísticas ferretería">

        <!-- Filtro fechas -->
        <div style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0; margin-bottom:24px; display:flex; gap:16px; align-items:flex-end;">
            <div>
                <label style="font-size:12px; font-weight:600; color:#64748B;">DESDE</label>
                <input v-model="desde" type="date" style="display:block; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px;">
            </div>
            <div>
                <label style="font-size:12px; font-weight:600; color:#64748B;">HASTA</label>
                <input v-model="hasta" type="date" style="display:block; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px;">
            </div>
            <button @click="filtrar" style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">
                🔍 Filtrar
            </button>
        </div>

        <!-- Resumen Caja -->
        <p style="font-size:14px; font-weight:700; color:#64748B; margin:0 0 12px; letter-spacing:1px;">💰 RESUMEN DE CAJA</p>
        <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:16px; margin-bottom:24px;">
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Total Ventas</p>
                <p style="font-size:20px; font-weight:700; color:#14B8A6; margin:0;">S/ {{ resumenCaja.total_ventas }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">💵 Efectivo</p>
                <p style="font-size:20px; font-weight:700; color:#1E293B; margin:0;">S/ {{ resumenCaja.total_efectivo }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">📱 Yape</p>
                <p style="font-size:20px; font-weight:700; color:#1E293B; margin:0;">S/ {{ resumenCaja.total_yape }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">💳 Tarjeta</p>
                <p style="font-size:20px; font-weight:700; color:#1E293B; margin:0;">S/ {{ resumenCaja.total_tarjeta }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Aperturas</p>
                <p style="font-size:20px; font-weight:700; color:#1E293B; margin:0;">{{ resumenCaja.aperturas }}</p>
            </div>
        </div>

        <!-- Cotizaciones y Órdenes -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
            <div style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0;">
                <p style="font-size:14px; font-weight:700; color:#64748B; margin:0 0 16px; letter-spacing:1px;">📋 COTIZACIONES</p>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-size:14px; color:#64748B;">Total cotizaciones</span>
                        <span style="font-size:14px; font-weight:700; color:#1E293B;">{{ resumenCotizaciones.total }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-size:14px; color:#64748B;">Aprobadas</span>
                        <span style="font-size:14px; font-weight:700; color:#16A34A;">{{ resumenCotizaciones.aprobadas }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding-top:12px; border-top:1px solid #F0F2F5;">
                        <span style="font-size:14px; font-weight:700; color:#1E293B;">Monto aprobado</span>
                        <span style="font-size:16px; font-weight:700; color:#14B8A6;">S/ {{ resumenCotizaciones.monto }}</span>
                    </div>
                </div>
            </div>
            <div style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0;">
                <p style="font-size:14px; font-weight:700; color:#64748B; margin:0 0 16px; letter-spacing:1px;">🔧 ÓRDENES DE TRABAJO</p>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-size:14px; color:#64748B;">Total órdenes</span>
                        <span style="font-size:14px; font-weight:700; color:#1E293B;">{{ resumenOrdenes.total }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-size:14px; color:#64748B;">Completadas</span>
                        <span style="font-size:14px; font-weight:700; color:#16A34A;">{{ resumenOrdenes.completadas }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding-top:12px; border-top:1px solid #F0F2F5;">
                        <span style="font-size:14px; font-weight:700; color:#1E293B;">Monto total</span>
                        <span style="font-size:16px; font-weight:700; color:#14B8A6;">S/ {{ resumenOrdenes.monto }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Garantías -->
        <p style="font-size:14px; font-weight:700; color:#64748B; margin:0 0 12px; letter-spacing:1px;">🛡️ GARANTÍAS</p>
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px;">
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Activas</p>
                <p style="font-size:24px; font-weight:700; color:#16A34A; margin:0;">{{ resumenGarantias.activas }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Vencidas</p>
                <p style="font-size:24px; font-weight:700; color:#DC2626; margin:0;">{{ resumenGarantias.vencidas }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Reclamadas</p>
                <p style="font-size:24px; font-weight:700; color:#F59E0B; margin:0;">{{ resumenGarantias.reclamadas }}</p>
            </div>
        </div>

        <!-- Stock Bajo -->
        <div style="background:white; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden;">
            <div style="padding:16px 20px; border-bottom:1px solid #F0F2F5; display:flex; justify-content:space-between; align-items:center;">
                <p style="font-size:15px; font-weight:700; color:#1E293B; margin:0;">⚠️ Productos con Stock Bajo</p>
                <span style="font-size:13px; color:#DC2626; font-weight:600;">{{ stockBajo.length }} productos</span>
            </div>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:12px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">PRODUCTO</th>
                        <th style="padding:12px 20px; text-align:center; font-size:12px; font-weight:700; color:#64748B;">STOCK ACTUAL</th>
                        <th style="padding:12px 20px; text-align:center; font-size:12px; font-weight:700; color:#64748B;">STOCK MÍNIMO</th>
                        <th style="padding:12px 20px; text-align:right; font-size:12px; font-weight:700; color:#64748B;">VALOR COMPRA</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="stockBajo.length === 0">
                        <td colspan="4" style="padding:30px; text-align:center; color:#94A3B8;">✅ Todos los productos tienen stock suficiente</td>
                    </tr>
                    <tr v-for="p in stockBajo" :key="p.id" style="border-top:1px solid #F1F5F9;">
                        <td style="padding:12px 20px;">
                            <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ p.descripcion }}</p>
                            <p style="font-size:12px; color:#94A3B8; margin:0;">{{ p.marca }} · {{ p.unidad_medida }}</p>
                        </td>
                        <td style="padding:12px 20px; text-align:center;">
                            <span style="padding:4px 12px; border-radius:20px; font-size:13px; font-weight:700; background:#FEF2F2; color:#DC2626;">{{ p.stock_actual }}</span>
                        </td>
                        <td style="padding:12px 20px; text-align:center; font-size:13px; color:#475569;">{{ p.stock_minimo }}</td>
                        <td style="padding:12px 20px; text-align:right; font-size:13px; font-weight:700; color:#14B8A6;">S/ {{ Number(p.precio_compra * p.stock_actual).toFixed(2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Valor inventario -->
        <div style="background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:16px; padding:24px; margin-top:24px; color:white; display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px;">
            <div>
                <p style="font-size:13px; opacity:0.8; margin:0;">Total Productos</p>
                <p style="font-size:28px; font-weight:700; margin:4px 0 0;">{{ valorInventario.total || 0 }}</p>
            </div>
            <div>
                <p style="font-size:13px; opacity:0.8; margin:0;">Valor a Precio Compra</p>
                <p style="font-size:28px; font-weight:700; margin:4px 0 0;">S/ {{ Number(valorInventario.valor || 0).toFixed(2) }}</p>
            </div>
            <div>
                <p style="font-size:13px; opacity:0.8; margin:0;">Valor a Precio Venta</p>
                <p style="font-size:28px; font-weight:700; margin:4px 0 0;">S/ {{ Number(valorInventario.valor_venta || 0).toFixed(2) }}</p>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    desde:                 { type: String, default: '' },
    hasta:                 { type: String, default: '' },
    stock_bajo:            { type: Array,  default: () => [] },
    valor_inventario:      { type: Object, default: () => ({}) },
    resumen_cotizaciones:  { type: Object, default: () => ({}) },
    resumen_ordenes:       { type: Object, default: () => ({}) },
    resumen_garantias:     { type: Object, default: () => ({}) },
    resumen_caja:          { type: Object, default: () => ({}) },
})

const desde = ref(props.desde)
const hasta = ref(props.hasta)

const stockBajo            = computed(() => props.stock_bajo)
const valorInventario      = computed(() => props.valor_inventario)
const resumenCotizaciones  = computed(() => props.resumen_cotizaciones)
const resumenOrdenes       = computed(() => props.resumen_ordenes)
const resumenGarantias     = computed(() => props.resumen_garantias)
const resumenCaja          = computed(() => props.resumen_caja)

const filtrar = () => {
    router.get('/ferreteria/reportes', { desde: desde.value, hasta: hasta.value }, { preserveState: true })
}
</script>
