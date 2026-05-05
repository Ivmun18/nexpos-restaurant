<template>
    <AppLayout title="Notas de crédito" subtitle="Listado de notas de crédito emitidas">

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
            <input v-model="busqueda" type="text" placeholder="Buscar por número o cliente..."
                style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; color:#1E293B; background:white; outline:none; width:320px;"/>
        </div>

        <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Número</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Doc. referencia</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Cliente</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Motivo</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Fecha</th>
                        <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Total</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Estado</th>
                        <th style="padding:12px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="notasFiltradas.length === 0">
                        <td colspan="8" style="padding:3rem; text-align:center; color:#94A3B8; font-size:14px;">
                            No hay notas de crédito registradas
                        </td>
                    </tr>
                    <tr v-for="n in notasFiltradas" :key="n.id" style="border-top:1px solid #F1F5F9;">
                        <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#2563EB; font-family:monospace;">
                            {{ n.numero_completo }}
                        </td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B; font-family:monospace;">
                            {{ n.doc_ref_numero }}
                        </td>
                        <td style="padding:12px 16px; font-size:13px; color:#1E293B;">
                            {{ n.cliente_razon_social || 'Clientes varios' }}
                        </td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">
                            {{ n.motivo_codigo }} - {{ n.motivo_descripcion }}
                        </td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ n.fecha_emision }}</td>
                        <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#1E293B; text-align:right;">
                            S/ {{ Number(n.total).toFixed(2) }}
                        </td>
                        <td style="padding:12px 16px;">
                            <span :style="estadoStyle(n.estado)">{{ n.estado }}</span>
                        </td>
                        <td style="padding:12px 16px; text-align:center;">
                            <a :href="`/notas-credito/${n.id}`"
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

const props = defineProps({ notas: Object })

const busqueda = ref('')

const estadoStyle = (estado) => {
    const map = {
        emitido:   { background:'#EFF6FF', color:'#1D4ED8' },
        aceptado:  { background:'#F0FDF4', color:'#166534' },
        rechazado: { background:'#FEF2F2', color:'#991B1B' },
        anulado:   { background:'#F1F5F9', color:'#64748B' },
    }
    return { ...(map[estado] || map.emitido), fontSize:'11px', padding:'3px 10px', borderRadius:'20px' }
}

const notasFiltradas = computed(() => {
    const data = props.notas?.data || []
    if (!busqueda.value) return data
    const q = busqueda.value.toLowerCase()
    return data.filter(n =>
        n.numero_completo?.toLowerCase().includes(q) ||
        n.cliente_razon_social?.toLowerCase().includes(q) ||
        n.doc_ref_numero?.toLowerCase().includes(q)
    )
})
</script>