<template>
    <AppLayout title="Compras" subtitle="Registro de compras a proveedores">

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
            <input v-model="busqueda" type="text" placeholder="Buscar por proveedor o numero..."
                style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; color:#1E293B; background:white; outline:none; width:320px;"/>
            <a href="/compras/crear"
                style="padding:10px 20px; background:#2563EB; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; text-decoration:none;">
                + Nueva compra
            </a>
        </div>

        <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Numero</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Proveedor</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Fecha</th>
                        <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Total</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Estado</th>
                        <th style="padding:12px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="comprasFiltradas.length === 0">
                        <td colspan="6" style="padding:3rem; text-align:center; color:#94A3B8; font-size:14px;">No hay compras registradas</td>
                    </tr>
                    <tr v-for="c in comprasFiltradas" :key="c.id" style="border-top:1px solid #F1F5F9;">
                        <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#2563EB; font-family:monospace;">{{ c.numero_comprobante }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#1E293B;">{{ c.proveedor?.razon_social }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ c.fecha_emision }}</td>
                        <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#1E293B; text-align:right;">S/ {{ Number(c.total).toFixed(2) }}</td>
                        <td style="padding:12px 16px;">
                            <span :style="estadoStyle(c.estado)">{{ c.estado }}</span>
                        </td>
                        <td style="padding:12px 16px; text-align:center;">
                            <a :href="'/compras/' + c.id"
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

const props = defineProps({ compras: Object })
const busqueda = ref('')

const estadoStyle = (estado) => {
    const map = {
        recibido:     { background:'#F0FDF4', color:'#166534' },
        pendiente:    { background:'#FFFBEB', color:'#92400E' },
        anulado:      { background:'#F1F5F9', color:'#64748B' },
        contabilizado:{ background:'#EFF6FF', color:'#1D4ED8' },
    }
    return { ...(map[estado] || map.recibido), fontSize:'11px', padding:'3px 10px', borderRadius:'20px' }
}

const comprasFiltradas = computed(() => {
    const data = props.compras?.data || []
    if (!busqueda.value) return data
    const q = busqueda.value.toLowerCase()
    return data.filter(c =>
        c.numero_comprobante?.toLowerCase().includes(q) ||
        c.proveedor?.razon_social?.toLowerCase().includes(q)
    )
})
</script>
