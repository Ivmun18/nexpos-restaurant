<template>
    <AppLayout :title="'Compra ' + compra.numero_comprobante" subtitle="Detalle de compra">

        <div style="display:grid; grid-template-columns:1fr 300px; gap:1.5rem; align-items:start;">

            <div>
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem; margin-bottom:1rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem;">
                        <div>
                            <p style="font-size:20px; font-weight:700; color:#1E293B; margin:0; font-family:monospace;">{{ compra.numero_comprobante }}</p>
                            <p style="font-size:13px; color:#94A3B8; margin:4px 0 0;">Registrada el {{ compra.fecha_emision }}</p>
                        </div>
                        <span :style="estadoStyle(compra.estado)">{{ compra.estado }}</span>
                    </div>
                    <div style="background:#F8FAFC; border-radius:8px; padding:12px 16px;">
                        <p style="font-size:11px; color:#94A3B8; margin:0 0 4px; text-transform:uppercase;">Proveedor</p>
                        <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ compra.proveedor?.razon_social }}</p>
                        <p style="font-size:12px; color:#64748B; margin:3px 0 0;">RUC: {{ compra.proveedor?.numero_documento }}</p>
                    </div>
                </div>

                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#F8FAFC;">
                                <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Descripcion</th>
                                <th style="padding:12px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Cant.</th>
                                <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">P. Unit.</th>
                                <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">IGV</th>
                                <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in compra.detalle" :key="item.id" style="border-top:1px solid #F1F5F9;">
                                <td style="padding:12px 16px; font-size:13px; color:#1E293B;">{{ item.descripcion }}</td>
                                <td style="padding:12px 16px; font-size:13px; color:#1E293B; text-align:center;">{{ Number(item.cantidad).toFixed(2) }}</td>
                                <td style="padding:12px 16px; font-size:13px; color:#1E293B; text-align:right;">S/ {{ Number(item.precio_unitario).toFixed(2) }}</td>
                                <td style="padding:12px 16px; font-size:13px; color:#1E293B; text-align:right;">S/ {{ Number(item.total_igv).toFixed(2) }}</td>
                                <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#1E293B; text-align:right;">S/ {{ Number(item.total).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="position:sticky; top:80px;">
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem; margin-bottom:1rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Resumen</p>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Op. gravadas</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ Number(compra.total_gravado).toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">IGV (18%)</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ Number(compra.total_igv).toFixed(2) }}</span>
                    </div>
                    <div style="border-top:2px solid #E2E8F0; margin:12px 0; padding-top:12px; display:flex; justify-content:space-between;">
                        <span style="font-size:15px; font-weight:700; color:#1E293B;">Total</span>
                        <span style="font-size:22px; font-weight:700; color:#2563EB;">S/ {{ Number(compra.total).toFixed(2) }}</span>
                    </div>
                </div>

                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Acciones</p>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <a href="/compras"
                            style="padding:10px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer; text-align:center; text-decoration:none;">
                            Volver a compras
                        </a>
                        <button v-if="compra.estado !== 'anulado'" @click="anular"
                            style="padding:10px; background:#FEF2F2; color:#991B1B; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:500;">
                            Anular compra
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ compra: Object })

const estadoStyle = (estado) => {
    const map = {
        recibido:     { background:'#F0FDF4', color:'#166534' },
        pendiente:    { background:'#FFFBEB', color:'#92400E' },
        anulado:      { background:'#F1F5F9', color:'#64748B' },
        contabilizado:{ background:'#EFF6FF', color:'#1D4ED8' },
    }
    return { ...(map[estado] || map.recibido), fontSize:'12px', padding:'4px 12px', borderRadius:'20px' }
}

const anular = () => {
    if (confirm('Anular esta compra')) {
        router.post('/compras/' + props.compra.id + '/anular')
    }
}
</script>
