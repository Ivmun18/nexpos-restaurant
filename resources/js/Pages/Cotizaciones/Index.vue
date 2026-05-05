<template>
    <AppLayout title="Cotizaciones" subtitle="Presupuestos y proformas">

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
            <input v-model="busqueda" type="text" placeholder="Buscar por numero o cliente..."
                style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; width:300px;"/>
            <a href="/cotizaciones/crear"
                style="padding:10px 20px; background:#2563EB; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; text-decoration:none;">
                + Nueva cotizacion
            </a>
        </div>

        <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600;">NUMERO</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600;">CLIENTE</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600;">FECHA</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600;">VENCIMIENTO</th>
                        <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600;">TOTAL</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600;">ESTADO</th>
                        <th style="padding:12px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="cotizacionesFiltradas.length === 0">
                        <td colspan="7" style="padding:3rem; text-align:center; color:#94A3B8; font-size:14px;">No hay cotizaciones</td>
                    </tr>
                    <tr v-for="c in cotizacionesFiltradas" :key="c.id" style="border-top:1px solid #F1F5F9;">
                        <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#2563EB; font-family:monospace;">{{ c.numero }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#1E293B;">{{ c.cliente_razon_social }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ c.fecha_emision }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ c.fecha_vencimiento || '-' }}</td>
                        <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#1E293B; text-align:right;">S/ {{ Number(c.total).toFixed(2) }}</td>
                        <td style="padding:12px 16px;">
                            <span :style="estadoStyle(c.estado)">{{ c.estado }}</span>
                        </td>
                        <td style="padding:12px 16px; text-align:center;">
                            <a :href="'/cotizaciones/' + c.id"
                                style="padding:6px 14px; background:#EFF6FF; color:#2563EB; border:none; border-radius:6px; font-size:12px; cursor:pointer; font-weight:500; text-decoration:none;">
                                Ver
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ cotizaciones: Object })
const busqueda = ref('')

const estadoStyle = (estado) => {
    const map = {
        borrador:   { background:'#F1F5F9', color:'#64748B' },
        enviada:    { background:'#EFF6FF', color:'#1D4ED8' },
        aprobada:   { background:'#F0FDF4', color:'#166534' },
        rechazada:  { background:'#FEF2F2', color:'#991B1B' },
        vencida:    { background:'#FFFBEB', color:'#92400E' },
        convertida: { background:'#F0FDF4', color:'#166534' },
    }
    return { ...(map[estado] || map.borrador), fontSize:'11px', padding:'3px 10px', borderRadius:'20px' }
}

const cotizacionesFiltradas = computed(() => {
    const data = props.cotizaciones?.data || []
    if (!busqueda.value) return data
    const q = busqueda.value.toLowerCase()
    return data.filter(c =>
        c.numero.toLowerCase().includes(q) ||
        c.cliente_razon_social?.toLowerCase().includes(q)
    )
})
</script>
