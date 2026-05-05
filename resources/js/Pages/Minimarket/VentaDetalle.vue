<template>
    <AppLayout title="Ticket de Venta" subtitle="Detalle del comprobante">

        <div style="max-width:600px; margin:0 auto;">

            <!-- Botones -->
            <div style="display:flex; gap:12px; margin-bottom:24px;">
                <a href="/minimarket/ventas" style="padding:10px 20px; background:white; color:#64748B; border-radius:10px; font-size:14px; font-weight:600; text-decoration:none; border:1px solid #E2E8F0;">
                    ← Volver
                </a>
                <button @click="imprimir"
                    style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                    🖨️ Imprimir Ticket
                </button>
                <button @click="imprimirA4"
                    style="padding:10px 20px; background:white; color:#0F766E; border-radius:10px; font-size:14px; font-weight:600; border:1px solid #CCFBF1; cursor:pointer;">
                    📄 Imprimir A4
                </button>
            </div>

            <!-- Ticket -->
            <div id="ticket" style="background:white; border-radius:20px; padding:32px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">

                <!-- Encabezado -->
                <div style="text-align:center; margin-bottom:24px; padding-bottom:20px; border-bottom:2px dashed #E2E8F0;">
                    <!-- Logo -->
                    <div v-if="empresa.logo_path" style="margin:0 auto 12px;">
                        <img :src="'/storage/' + empresa.logo_path" style="max-width:120px; max-height:80px; object-fit:contain;" />
                    </div>
                    <div v-else style="width:60px; height:60px; background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:28px; margin:0 auto 12px;">
                        🏪
                    </div>
                    <p style="font-size:20px; font-weight:800; color:#1E293B; margin:0;">{{ empresa.nombre_comercial }}</p>
                    <p style="font-size:13px; color:#94A3B8; margin:4px 0 0;">RUC: {{ empresa.ruc }}</p>
                    <p style="font-size:13px; color:#94A3B8; margin:2px 0 0;">{{ empresa.direccion }}</p>
                    <p v-if="empresa.telefono" style="font-size:13px; color:#94A3B8; margin:2px 0 0;">Tel: {{ empresa.telefono }}</p>
                    <p v-if="empresa.email" style="font-size:13px; color:#94A3B8; margin:2px 0 0;">{{ empresa.email }}</p>
                </div>

                <!-- Tipo comprobante -->
                <div style="text-align:center; margin-bottom:16px;">
                    <span style="font-size:16px; font-weight:800; color:#1E293B; border:2px solid #1E293B; padding:4px 20px; border-radius:6px;">
                        {{ venta.tipo_comprobante === '03' ? 'BOLETA DE VENTA' : 'FACTURA' }}
                    </span>
                    <p style="font-size:15px; font-weight:700; color:#1E293B; margin:8px 0 0;">{{ venta.numero_completo }}</p>
                </div>

                <!-- Info comprobante -->
                <div style="margin-bottom:20px; padding:14px 16px; background:#F8FAFC; border-radius:12px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#94A3B8;">Fecha</span>
                        <span style="font-size:14px; font-weight:600; color:#1E293B;">{{ formatFecha(venta.fecha_emision) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#94A3B8;">Hora</span>
                        <span style="font-size:14px; font-weight:600; color:#1E293B;">{{ venta.hora_emision }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-size:13px; color:#94A3B8;">Método de pago</span>
                        <span style="font-size:14px; font-weight:600; color:#1E293B; text-transform:capitalize;">
                            {{ iconMetodo(venta.metodo_pago) }} {{ venta.metodo_pago || 'efectivo' }}
                        </span>
                    </div>
                </div>

                <!-- Productos -->
                <table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
                    <thead>
                        <tr style="border-bottom:2px solid #1E293B;">
                            <th style="padding:8px 0; text-align:left; font-size:12px; color:#1E293B; font-weight:700; text-transform:uppercase;">Producto</th>
                            <th style="padding:8px 0; text-align:center; font-size:12px; color:#1E293B; font-weight:700; text-transform:uppercase;">Cant.</th>
                            <th style="padding:8px 0; text-align:right; font-size:12px; color:#1E293B; font-weight:700; text-transform:uppercase;">P.Unit</th>
                            <th style="padding:8px 0; text-align:right; font-size:12px; color:#1E293B; font-weight:700; text-transform:uppercase;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="d in venta.detalle" :key="d.id" style="border-bottom:1px dashed #E2E8F0;">
                            <td style="padding:10px 0; font-size:13px; color:#1E293B; font-weight:500;">{{ d.descripcion }}</td>
                            <td style="padding:10px 0; text-align:center; font-size:13px; color:#475569;">{{ d.cantidad }}</td>
                            <td style="padding:10px 0; text-align:right; font-size:13px; color:#475569;">S/ {{ Number(d.precio_unitario).toFixed(2) }}</td>
                            <td style="padding:10px 0; text-align:right; font-size:13px; font-weight:700; color:#1E293B;">S/ {{ Number(d.total).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Totales -->
                <div style="border-top:2px solid #1E293B; padding-top:12px; margin-bottom:16px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Subtotal</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ Number(venta.total_gravado).toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">IGV (18%)</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ (Number(venta.total_gravado) * 0.18 / 1.18).toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:10px 16px; background:#1E293B; border-radius:10px; margin-top:8px;">
                        <span style="font-size:16px; font-weight:700; color:white;">TOTAL</span>
                        <span style="font-size:24px; font-weight:900; color:white;">S/ {{ Number(venta.total_gravado).toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Pie -->
                <div style="text-align:center; padding-top:16px; border-top:2px dashed #E2E8F0;">
                    <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">¡Gracias por su compra!</p>
                    <p style="font-size:12px; color:#94A3B8; margin:4px 0 0;">Vuelva pronto 😊</p>
                    <p style="font-size:11px; color:#CBD5E1; margin:8px 0 0;">Powered by NEXPOS</p>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    venta:    { type: Object,  default: () => ({}) },
    empresa:  { type: Object,  default: () => ({}) },
    imprimir: { type: Boolean, default: false },
})

const formatFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const iconMetodo = (m) => ({ efectivo: '💵', yape: '📱', plin: '📲', tarjeta: '💳' })[m] || '💵'

const imprimir = () => {
    const contenido = document.getElementById('ticket').innerHTML
    const ventana = window.open('', '_blank')
    ventana.document.write(`
        <html>
        <head>
            <title>Ticket - ${props.venta.numero_completo}</title>
            <style>
                @media print {
                    body { margin: 0; }
                    @page { margin: 5mm; size: 80mm auto; }
                }
                body { 
                    font-family: 'Courier New', monospace; 
                    padding: 10px; 
                    max-width: 80mm; 
                    margin: 0 auto;
                    font-size: 12px;
                }
                * { box-sizing: border-box; }
                table { width: 100%; border-collapse: collapse; }
                img { max-width: 100%; }
            </style>
        </head>
        <body>${contenido}</body>
        </html>
    `)
    ventana.document.close()
    setTimeout(() => ventana.print(), 500)
}

const imprimirA4 = () => {
    const contenido = document.getElementById('ticket').innerHTML
    const ventana = window.open('', '_blank')
    ventana.document.write(`
        <html>
        <head>
            <title>Boleta - ${props.venta.numero_completo}</title>
            <style>
                @media print {
                    body { margin: 0; }
                    @page { margin: 20mm; size: A4; }
                }
                body { 
                    font-family: system-ui, sans-serif; 
                    padding: 40px; 
                    max-width: 600px; 
                    margin: 0 auto;
                }
                * { box-sizing: border-box; }
                table { width: 100%; border-collapse: collapse; }
                img { max-width: 100%; }
            </style>
        </head>
        <body>${contenido}</body>
        </html>
    `)
    ventana.document.close()
    setTimeout(() => ventana.print(), 500)
}

onMounted(() => {
    if (props.imprimir) {
        setTimeout(() => imprimir(), 800)
    }
})
</script>