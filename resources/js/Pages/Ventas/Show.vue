<template>
    <AppLayout :title="venta.numero_completo" subtitle="Detalle del comprobante">

        <div style="display:grid; grid-template-columns:1fr 340px; gap:1.5rem; align-items:start;">

            <!-- Panel izquierdo -->
            <div>
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem; margin-bottom:1rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem;">
                        <div>
                            <p style="font-size:20px; font-weight:700; color:#1E293B; margin:0; font-family:monospace;">{{ venta.numero_completo }}</p>
                            <p style="font-size:13px; color:#94A3B8; margin:4px 0 0;">Emitido el {{ venta.fecha_emision }} a las {{ venta.hora_emision }}</p>
                        </div>
                        <div style="display:flex; gap:8px; align-items:center;">
                            <span :style="estadoStyle(venta.estado)">{{ venta.estado }}</span>
                            <span :style="venta.tipo_comprobante === '01' ? tipoFactura : tipoBoleta">
                                {{ venta.tipo_comprobante === '01' ? 'Factura' : 'Boleta' }}
                            </span>
                        </div>
                    </div>
                    <div style="background:#F8FAFC; border-radius:8px; padding:12px 16px;">
                        <p style="font-size:11px; color:#94A3B8; margin:0 0 6px; text-transform:uppercase; letter-spacing:0.5px;">Cliente</p>
                        <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ venta.cliente_razon_social || 'Cliente varios' }}</p>
                        <p style="font-size:12px; color:#64748B; margin:4px 0 0;">
                            {{ venta.cliente_tipo_doc === '6' ? 'RUC' : 'DNI' }}: {{ venta.cliente_num_doc || '—' }}
                        </p>
                        <p v-if="venta.cliente_direccion" style="font-size:12px; color:#64748B; margin:2px 0 0;">{{ venta.cliente_direccion }}</p>
                    </div>
                </div>

                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#F8FAFC;">
                                <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">#</th>
                                <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Descripción</th>
                                <th style="padding:12px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Cant.</th>
                                <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">P. Unit.</th>
                                <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">IGV</th>
                                <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in venta.detalle" :key="item.id" style="border-top:1px solid #F1F5F9;">
                                <td style="padding:12px 16px; font-size:13px; color:#94A3B8;">{{ item.linea }}</td>
                                <td style="padding:12px 16px;">
                                    <p style="font-size:13px; font-weight:500; color:#1E293B; margin:0;">{{ item.descripcion }}</p>
                                    <p style="font-size:11px; color:#94A3B8; margin:2px 0 0;">{{ item.codigo_producto }} · {{ item.unidad_medida }}</p>
                                </td>
                                <td style="padding:12px 16px; font-size:13px; color:#1E293B; text-align:center;">{{ Number(item.cantidad).toFixed(2) }}</td>
                                <td style="padding:12px 16px; font-size:13px; color:#1E293B; text-align:right;">S/ {{ Number(item.precio_unitario).toFixed(2) }}</td>
                                <td style="padding:12px 16px; font-size:13px; color:#1E293B; text-align:right;">S/ {{ Number(item.total_igv).toFixed(2) }}</td>
                                <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#1E293B; text-align:right;">S/ {{ Number(item.total).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Panel derecho -->
            <div style="position:sticky; top:80px;">

                <!-- Resumen -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem; margin-bottom:1rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1.2rem;">Resumen</p>
                    <div v-if="Number(venta.total_gravado) > 0" style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-size:13px; color:#64748B;">Op. Gravada</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ Number(venta.total_gravado).toFixed(2) }}</span>
                    </div>
                    <div v-if="Number(venta.total_exonerado) > 0" style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-size:13px; color:#64748B;">Op. Exonerada</span>
                        <span style="font-size:13px; color:#16a34a;">S/ {{ Number(venta.total_exonerado).toFixed(2) }}</span>
                    </div>
                    <div v-if="Number(venta.total_inafecto) > 0" style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-size:13px; color:#64748B;">Op. Inafecta</span>
                        <span style="font-size:13px; color:#f59e0b;">S/ {{ Number(venta.total_inafecto).toFixed(2) }}</span>
                    </div>
                    <div v-if="Number(venta.total_igv) > 0" style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-size:13px; color:#64748B;">IGV (18%)</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ Number(venta.total_igv).toFixed(2) }}</span>
                    </div>
                    <div v-if="Number(venta.total_igv) === 0" style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-size:13px; color:#64748B;">IGV</span>
                        <span style="font-size:13px; color:#94a3b8;">S/ 0.00</span>
                    </div>
                    <div style="border-top:2px solid #E2E8F0; margin:12px 0; padding-top:12px; display:flex; justify-content:space-between;">
                        <span style="font-size:15px; font-weight:700; color:#1E293B;">Total</span>
                        <span style="font-size:20px; font-weight:700; color:#2563EB;">S/ {{ Number(venta.total).toFixed(2) }}</span>
                    </div>
                    <div style="background:#F8FAFC; border-radius:8px; padding:12px; margin-top:1rem;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-size:12px; color:#64748B;">Forma de pago</span>
                            <span style="font-size:12px; font-weight:500; color:#1E293B; text-transform:capitalize;">{{ venta.forma_pago }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-size:12px; color:#64748B;">Monto recibido</span>
                            <span style="font-size:12px; font-weight:500; color:#1E293B;">S/ {{ Number(venta.monto_pagado).toFixed(2) }}</span>
                        </div>
                        <div v-if="Number(venta.vuelto) > 0" style="display:flex; justify-content:space-between;">
                            <span style="font-size:12px; color:#166534;">Vuelto</span>
                            <span style="font-size:12px; font-weight:600; color:#166534;">S/ {{ Number(venta.vuelto).toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Acciones</p>
                    <div style="display:flex; flex-direction:column; gap:8px;">

                        <a :href="'/ventas/' + venta.id + '/pdf-a4'" target="_blank"
                            style="padding:10px; background:#2563EB; color:white; border:none; border-radius:8px; font-size:13px; cursor:pointer; text-align:center; text-decoration:none; font-weight:600;">
                            Imprimir A4
                        </a>

                        <a :href="'/ventas/' + venta.id + '/pdf-ticket'" target="_blank"
                            style="padding:10px; background:#1D4ED8; color:white; border:none; border-radius:8px; font-size:13px; cursor:pointer; text-align:center; text-decoration:none; font-weight:600;">
                            Imprimir Ticket 80mm
                        </a>

                        <a :href="whatsappUrl" target="_blank"
                            style="padding:10px; background:#25D366; color:white; border:none; border-radius:8px; font-size:13px; cursor:pointer; text-align:center; text-decoration:none; font-weight:600;">
                            Enviar por WhatsApp
                        </a>

                        <a :href="'/ventas/' + venta.id + '/nota-credito'"
                            style="padding:10px; background:#FFFBEB; color:#92400E; border:1px solid #FDE68A; border-radius:8px; font-size:13px; cursor:pointer; text-align:center; text-decoration:none; font-weight:500;">
                            Emitir nota de crédito
                        </a>

                        <button v-if="venta.estado === 'emitido' || venta.estado === 'rechazado'" @click="enviarSunat"
                            style="padding:10px; background:#F0FDF4; color:#166534; border:1px solid #DCFCE7; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">
                            Enviar a SUNAT
                        </button>

                        <a href="/ventas"
                            style="padding:10px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer; text-align:center; text-decoration:none; font-weight:500;">
                            Volver a ventas
                        </a>

                        <button v-if="venta.estado !== 'anulado'" @click="anular"
                            style="padding:10px; background:#FEF2F2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:13px; cursor:pointer; font-weight:500;">
                            Anular comprobante
                        </button>

                    </div>
                </div>

            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ venta: Object })

const tipoFactura = { fontSize:'12px', background:'#EFF6FF', color:'#1D4ED8', padding:'4px 12px', borderRadius:'20px' }
const tipoBoleta  = { fontSize:'12px', background:'#F0FDF4', color:'#166534', padding:'4px 12px', borderRadius:'20px' }

const estadoStyle = (estado) => {
    const map = {
        emitido:   { background:'#EFF6FF', color:'#1D4ED8' },
        aceptado:  { background:'#F0FDF4', color:'#166534' },
        rechazado: { background:'#FEF2F2', color:'#991B1B' },
        anulado:   { background:'#F1F5F9', color:'#64748B' },
    }
    return { ...(map[estado] || map.emitido), fontSize:'12px', padding:'4px 12px', borderRadius:'20px' }
}

const whatsappUrl = computed(() => {
    const texto = `Estimado(a) ${props.venta.cliente_razon_social || 'cliente'},\n\nSu comprobante:\n*${props.venta.numero_completo}*\nFecha: ${props.venta.fecha_emision}\nTotal: S/ ${Number(props.venta.total).toFixed(2)}\n\nGracias por su preferencia.`
    return `https://wa.me/?text=${encodeURIComponent(texto)}`
})

const enviarSunat = () => {
    if (confirm('¿Enviar este comprobante a SUNAT?')) {
        router.post('/ventas/' + props.venta.id + '/enviar-sunat')
    }
}

const anular = () => {
    if (confirm(`¿Anular el comprobante ${props.venta.numero_completo}?`)) {
        router.post('/ventas/' + props.venta.id + '/anular')
    }
}
</script>