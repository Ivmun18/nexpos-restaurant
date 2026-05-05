<template>
    <AppLayout title="Ventas" subtitle="Listado de comprobantes emitidos">

        <!-- Barra superior -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
            <div style="display:flex; gap:10px;">
                <input v-model="busqueda" type="text" placeholder="Buscar por número o cliente..."
                    style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; color:#1E293B; background:white; outline:none; width:280px;"/>
                <select v-model="filtroTipo"
                    style="padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; background:white; outline:none;">
                    <option value="">Todos</option>
                    <option value="01">Facturas</option>
                    <option value="03">Boletas</option>
                </select>
            </div>
            <a href="/ventas/crear"
                style="padding:10px 20px; background:#2563EB; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; text-decoration:none;">
                + Nueva venta
            </a>
        </div>

        <!-- Tabla -->
        <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Número</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Tipo</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Cliente</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Fecha</th>
                        <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Total</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Estado</th>
                        <th style="padding:12px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="ventasFiltradas.length === 0">
                        <td colspan="7" style="padding:3rem; text-align:center; color:#94A3B8; font-size:14px;">
                            No hay ventas registradas
                        </td>
                    </tr>
                    <tr v-for="v in ventasFiltradas" :key="v.id" style="border-top:1px solid #F1F5F9;">
                        <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#2563EB; font-family:monospace;">
                            {{ v.numero_completo }}
                        </td>
                        <td style="padding:12px 16px;">
                            <span :style="v.tipo_comprobante === '01' ? tipoFactura : tipoBoleta">
                                {{ v.tipo_comprobante === '01' ? 'Factura' : 'Boleta' }}
                            </span>
                        </td>
                        <td style="padding:12px 16px; font-size:13px; color:#1E293B;">
                            {{ v.cliente_razon_social || 'Cliente varios' }}
                            <p style="font-size:11px; color:#94A3B8; margin:2px 0 0;">{{ v.cliente_num_doc }}</p>
                        </td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ v.fecha_emision }}</td>
                        <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#1E293B; text-align:right;">
                            S/ {{ Number(v.total).toFixed(2) }}
                        </td>
                        <td style="padding:12px 16px;">
                            <span :style="estadoStyle(v.estado)">{{ v.estado }}</span>
                        </td>
                        <td style="padding:12px 16px; text-align:center;">
                            <div style="display:flex; gap:8px; justify-content:center;">
                                <a :href="`/ventas/${v.id}`"
                                    style="padding:6px 14px; background:#EFF6FF; color:#2563EB; border:none; border-radius:6px; font-size:12px; cursor:pointer; font-weight:500; text-decoration:none;">
                                    Ver
                                </a>
                                <button v-if="v.estado !== 'anulado'" @click="anular(v)"
                                    style="padding:6px 14px; background:#FEF2F2; color:#991B1B; border:none; border-radius:6px; font-size:12px; cursor:pointer; font-weight:500;">
                                    Anular
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ ventas: Object })

const busqueda   = ref('')
const filtroTipo = ref('')

const tipoFactura = { fontSize:'11px', background:'#EFF6FF', color:'#1D4ED8', padding:'3px 10px', borderRadius:'20px' }
const tipoBoleta  = { fontSize:'11px', background:'#F0FDF4', color:'#166534', padding:'3px 10px', borderRadius:'20px' }

const estadoStyle = (estado) => {
    const map = {
        emitido:   { background:'#EFF6FF', color:'#1D4ED8' },
        aceptado:  { background:'#F0FDF4', color:'#166534' },
        rechazado: { background:'#FEF2F2', color:'#991B1B' },
        anulado:   { background:'#F1F5F9', color:'#64748B' },
        borrador:  { background:'#FFFBEB', color:'#92400E' },
    }
    return { ...(map[estado] || map.emitido), fontSize:'11px', padding:'3px 10px', borderRadius:'20px' }
}

const ventasFiltradas = computed(() => {
    const data = props.ventas?.data || []
    return data.filter(v => {
        const matchBusqueda = !busqueda.value ||
            v.numero_completo?.toLowerCase().includes(busqueda.value.toLowerCase()) ||
            v.cliente_razon_social?.toLowerCase().includes(busqueda.value.toLowerCase())
        const matchTipo = !filtroTipo.value || v.tipo_comprobante === filtroTipo.value
        return matchBusqueda && matchTipo
    })
})

const anular = (v) => {
    if (confirm(`¿Anular el comprobante ${v.numero_completo}?`)) {
        router.post(`/ventas/${v.id}/anular`)
    }
}
</script>