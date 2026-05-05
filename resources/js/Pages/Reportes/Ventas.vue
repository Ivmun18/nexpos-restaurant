<template>
    <AppLayout title="Reporte de ventas" subtitle="Análisis de ventas por período">

        <!-- Filtros -->
        <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.2rem 1.5rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; text-transform:uppercase;">Desde</label>
                <input v-model="filtros.desde" type="date"
                    style="padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none;"/>
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; text-transform:uppercase;">Hasta</label>
                <input v-model="filtros.hasta" type="date"
                    style="padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none;"/>
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; text-transform:uppercase;">Tipo</label>
                <select v-model="filtros.tipo"
                    style="padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; background:white;">
                    <option value="">Todos</option>
                    <option value="01">Facturas</option>
                    <option value="03">Boletas</option>
                </select>
            </div>
            <div style="display:flex; gap:8px; margin-top:18px;">
                <button @click="buscar"
                    style="padding:9px 20px; background:#2563EB; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                    Buscar
                </button>
                <button @click="hoy" style="padding:9px 14px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Hoy</button>
                <button @click="esteMes" style="padding:9px 14px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Este mes</button>
		<a :href="urlExcel"
                    style="padding:9px 14px; background:#166534; color:white; border:none; border-radius:8px; font-size:13px; cursor:pointer; text-decoration:none; margin-top:18px; display:inline-block;">
                    Exportar Excel
                </a>
                <a :href="urlPle"
                    style="padding:9px 14px; background:#166534; color:white; border:none; border-radius:8px; font-size:13px; cursor:pointer; text-decoration:none; margin-top:18px; display:inline-block;">
                    Exportar PLE SUNAT
                </a>
            </div>
        </div>

        <!-- KPIs -->
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:1.5rem;">
            <div style="background:white; border-radius:10px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:11px; color:#94A3B8; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.5px;">Total ventas</p>
                <p style="font-size:22px; font-weight:700; color:#2563EB; margin:0;">S/ {{ Number(resumen.total_ventas).toFixed(2) }}</p>
                <p style="font-size:11px; color:#94A3B8; margin:6px 0 0;">{{ resumen.total_docs }} comprobantes</p>
            </div>
            <div style="background:white; border-radius:10px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:11px; color:#94A3B8; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.5px;">Op. gravadas</p>
                <p style="font-size:22px; font-weight:700; color:#1E293B; margin:0;">S/ {{ Number(resumen.total_gravado).toFixed(2) }}</p>
                <p style="font-size:11px; color:#94A3B8; margin:6px 0 0;">IGV: S/ {{ Number(resumen.total_igv).toFixed(2) }}</p>
            </div>
            <div style="background:white; border-radius:10px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:11px; color:#94A3B8; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.5px;">Facturas</p>
                <p style="font-size:22px; font-weight:700; color:#1E293B; margin:0;">{{ resumen.total_facturas }}</p>
                <p style="font-size:11px; color:#94A3B8; margin:6px 0 0;">En el período</p>
            </div>
            <div style="background:white; border-radius:10px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:11px; color:#94A3B8; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.5px;">Boletas</p>
                <p style="font-size:22px; font-weight:700; color:#1E293B; margin:0;">{{ resumen.total_boletas }}</p>
                <p style="font-size:11px; color:#94A3B8; margin:6px 0 0;">En el período</p>
            </div>
        </div>

        <!-- Gráfico por día -->
        <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem; margin-bottom:1.5rem;">
            <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Ventas por día</p>
            <div v-if="ventasPorDia.length === 0" style="text-align:center; padding:2rem; color:#94A3B8; font-size:13px;">
                No hay datos para mostrar
            </div>
            <div v-else style="display:flex; align-items:flex-end; gap:6px; height:160px; overflow-x:auto; padding-bottom:8px;">
                <div v-for="dia in ventasPorDia" :key="dia.fecha"
                    style="display:flex; flex-direction:column; align-items:center; gap:4px; min-width:50px;">
                    <span style="font-size:10px; color:#94A3B8;">S/ {{ Number(dia.total).toFixed(0) }}</span>
                    <div :style="{
                        height: (dia.total / maxDia * 120) + 'px',
                        background: '#2563EB',
                        borderRadius: '4px 4px 0 0',
                        width: '36px',
                        minHeight: '4px',
                        opacity: '0.85'
                    }"></div>
                    <span style="font-size:10px; color:#94A3B8; white-space:nowrap;">{{ formatFecha(dia.fecha) }}</span>
                </div>
            </div>
        </div>

        <!-- Tabla de ventas -->
        <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden;">
            <div style="padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between;">
                <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0;">Detalle de comprobantes</p>
                <span style="font-size:12px; color:#94A3B8;">{{ ventas.length }} registros</span>
            </div>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Fecha</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Número</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Tipo</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Cliente</th>
                        <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Gravado</th>
                        <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">IGV</th>
                        <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Total</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="ventas.length === 0">
                        <td colspan="8" style="padding:3rem; text-align:center; color:#94A3B8; font-size:14px;">No hay ventas en el período seleccionado</td>
                    </tr>
                    <tr v-for="v in ventas" :key="v.id" style="border-top:1px solid #F1F5F9;">
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ v.fecha_emision }}</td>
                        <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#2563EB; font-family:monospace;">
                            <a :href="`/ventas/${v.id}`" style="color:#2563EB; text-decoration:none;">{{ v.numero_completo }}</a>
                        </td>
                        <td style="padding:12px 16px;">
                            <span :style="v.tipo_comprobante==='01' ? tipoFactura : tipoBoleta">
                                {{ v.tipo_comprobante==='01' ? 'Factura' : 'Boleta' }}
                            </span>
                        </td>
                        <td style="padding:12px 16px; font-size:13px; color:#1E293B;">{{ v.cliente_razon_social || 'Clientes varios' }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#1E293B; text-align:right;">S/ {{ Number(v.total_gravado).toFixed(2) }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#1E293B; text-align:right;">S/ {{ Number(v.total_igv).toFixed(2) }}</td>
                        <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#1E293B; text-align:right;">S/ {{ Number(v.total).toFixed(2) }}</td>
                        <td style="padding:12px 16px;">
                            <span :style="estadoStyle(v.estado)">{{ v.estado }}</span>
                        </td>
                    </tr>
                </tbody>
                <!-- Totales -->
                <tfoot v-if="ventas.length > 0">
                    <tr style="background:#F8FAFC; border-top:2px solid #E2E8F0;">
                        <td colspan="4" style="padding:12px 16px; font-size:13px; font-weight:600; color:#1E293B;">TOTALES</td>
                        <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#1E293B; text-align:right;">S/ {{ Number(resumen.total_gravado).toFixed(2) }}</td>
                        <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#1E293B; text-align:right;">S/ {{ Number(resumen.total_igv).toFixed(2) }}</td>
                        <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#2563EB; text-align:right;">S/ {{ Number(resumen.total_ventas).toFixed(2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    ventas:       { type: Array,  default: () => [] },
    ventasPorDia: { type: Array,  default: () => [] },
    filtros:      { type: Object, default: () => ({}) },
    resumen:      { type: Object, default: () => ({}) },
})
const urlPle = computed(() => {
    const fecha = new Date(filtros.value.desde)
    return '/reportes/exportar-ple?mes=' + (fecha.getMonth()+1) + '&anio=' + fecha.getFullYear()
})

const filtros = ref({ ...props.filtros })

const tipoFactura = { fontSize:'11px', background:'#EFF6FF', color:'#1D4ED8', padding:'3px 10px', borderRadius:'20px' }
const tipoBoleta  = { fontSize:'11px', background:'#F0FDF4', color:'#166534', padding:'3px 10px', borderRadius:'20px' }

const estadoStyle = (estado) => {
    const map = {
        emitido:   { background:'#EFF6FF', color:'#1D4ED8' },
        aceptado:  { background:'#F0FDF4', color:'#166534' },
        rechazado: { background:'#FEF2F2', color:'#991B1B' },
        anulado:   { background:'#F1F5F9', color:'#64748B' },
    }
    return { ...(map[estado] || map.emitido), fontSize:'11px', padding:'3px 10px', borderRadius:'20px' }
}

const maxDia = computed(() => {
    if (props.ventasPorDia.length === 0) return 1
    return Math.max(...props.ventasPorDia.map(d => d.total))
})

const formatFecha = (fecha) => {
    if (!fecha) return ''
    const d = new Date(fecha + 'T00:00:00')
    return `${d.getDate()}/${d.getMonth()+1}`
}

const buscar = () => {
    router.get('/reportes', filtros.value, { preserveState: true })
}

const hoy = () => {
    const d = new Date().toISOString().split('T')[0]
    filtros.value.desde = d
    filtros.value.hasta = d
    buscar()
}

const esteMes = () => {
    const now  = new Date()
    const desde = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0]
    const hasta = new Date().toISOString().split('T')[0]
    filtros.value.desde = desde
    filtros.value.hasta = hasta
    buscar()
}
</script>