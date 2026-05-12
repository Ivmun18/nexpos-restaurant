<template>
    <AppLayout title="Ticket de Venta" subtitle="Detalle del comprobante">

        <div style="max-width:1100px; margin:0 auto;">

            <!-- Botones -->
            <div style="display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap;">
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
                <button @click="enviarWhatsApp"
                    style="padding:10px 20px; background:#dcfce7; color:#16a34a; border-radius:10px; font-size:14px; font-weight:600; border:1px solid #bbf7d0; cursor:pointer;">
                    📱 WhatsApp
                </button>
            </div>

            <!-- Layout 2 columnas -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start;">

            <!-- Columna izquierda: info empresa + comprobante + totales -->
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

                    <!-- Estado SUNAT -->
                    <div v-if="venta.nubefact_estado" style="margin-top:10px;">
                        <span v-if="venta.nubefact_estado === 'aceptado'"
                            style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:#dcfce7; color:#166534; border-radius:20px; font-size:12px; font-weight:700;">
                            ✅ Aceptado por SUNAT
                        </span>
                        <span v-else-if="venta.nubefact_estado === 'rechazado'"
                            style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:#fee2e2; color:#dc2626; border-radius:20px; font-size:12px; font-weight:700;">
                            ❌ Rechazado por SUNAT
                        </span>
                        <span v-else
                            style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:#fef3c7; color:#92400e; border-radius:20px; font-size:12px; font-weight:700;">
                            ⏳ Pendiente SUNAT
                        </span>
                    </div>
                    <div v-else-if="venta.tipo_comprobante !== '00'"
                        style="margin-top:10px;">
                        <span style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; background:#f1f5f9; color:#64748b; border-radius:20px; font-size:12px; font-weight:600;">
                            📋 Sin envío a SUNAT
                        </span>
                    </div>

                    <!-- Botones SUNAT -->
                    <div v-if="venta.nubefact_estado" style="display:flex; gap:8px; margin-top:12px; justify-content:center;">
                        <button v-if="venta.nubefact_estado === 'rechazado'" @click="reintentar"
                            style="padding:8px 16px; background:#0891b2; color:white; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                            🔄 Reintentar envío
                        </button>
                        <a v-if="venta.nubefact_id" :href="venta.nubefact_id" target="_blank"
                            style="padding:8px 16px; background:#16a34a; color:white; border-radius:8px; font-size:12px; font-weight:600;">
                            📄 Ver PDF
                        </a>

                    </div>
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

            </div><!-- fin columna izquierda -->

            <!-- Columna derecha: productos -->
            <div id="ticket-productos" style="background:white; border-radius:20px; padding:32px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px; padding-bottom:12px; border-bottom:2px solid #E2E8F0;">🛒 Productos ({{ venta.detalle?.length || 0 }} items)</h3>
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
                        <span style="font-size:13px; color:#1E293B;">S/ {{ Number(venta.total || (Number(venta.total_gravado) + Number(venta.total_igv))).toFixed(2) }}</span>
                    </div>
                    <!-- Op. Gravada -->
                    <div v-if="Number(venta.total_gravado) > 0" style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Op. Gravada</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ Number(venta.total_gravado).toFixed(2) }}</span>
                    </div>
                    
                    <div v-if="Number(venta.total_exonerado) > 0" style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Op. Exonerada</span>
                        <span style="font-size:13px; color:#16a34a;">S/ {{ Number(venta.total_exonerado).toFixed(2) }}</span>
                    </div>
                    <div v-if="Number(venta.total_inafecto) > 0" style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Op. Inafecta</span>
                        <span style="font-size:13px; color:#f59e0b;">S/ {{ Number(venta.total_inafecto).toFixed(2) }}</span>
                    </div>
                    
                    <div v-if="Number(venta.total_igv) > 0" style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">IGV (18%)</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ Number(venta.total_igv).toFixed(2) }}</span>
                    </div>
                    <div v-if="Number(venta.total_igv) === 0" style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">IGV</span>
                        <span style="font-size:13px; color:#94a3b8;">S/ 0.00</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:10px 16px; background:#1E293B; border-radius:10px; margin-top:8px;">
                        <span style="font-size:16px; font-weight:700; color:white;">TOTAL</span>
                        <span style="font-size:24px; font-weight:900; color:white;">S/ {{ Number(venta.total || (Number(venta.total_gravado) + Number(venta.total_igv))).toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Pie -->
                <div style="text-align:center; padding-top:16px; border-top:2px dashed #E2E8F0; margin-top:20px;">
                    <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">¡Gracias por su compra!</p>
                    <p style="font-size:12px; color:#94A3B8; margin:4px 0 0;">Vuelva pronto 😊</p>
                    <p style="font-size:11px; color:#CBD5E1; margin:8px 0 0;">Powered by NEXPOS</p>
                </div>
            </div><!-- fin columna derecha -->
            </div><!-- fin grid -->
        </div>

    </AppLayout>
</template>

<script setup>
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const reintentar = () => {
    if (confirm('¿Reintentar envío a SUNAT?')) {
        router.post(`/minimarket/ventas/${props.venta.id}/reintentar`)
    }
}

const anular = () => {
    if (confirm('¿Anular este comprobante?')) {
        router.post(`/minimarket/ventas/${props.venta.id}/anular`)
    }
}

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
    const v = props.venta
    const e = props.empresa
    const items = (v.detalle || []).map(d => `
        <tr>
            <td style="padding:4px 0; font-size:11px;">${d.descripcion}</td>
            <td style="padding:4px 0; text-align:center; font-size:11px;">${d.cantidad}</td>
            <td style="padding:4px 0; text-align:right; font-size:11px;">S/ ${Number(d.precio_unitario).toFixed(2)}</td>
            <td style="padding:4px 0; text-align:right; font-size:11px; font-weight:bold;">S/ ${Number(d.total).toFixed(2)}</td>
        </tr>`).join('')
    const contenido = `
        <div style="text-align:center; margin-bottom:12px;">
            <p style="font-size:14px; font-weight:bold; margin:0;">${e.nombre_comercial || e.razon_social}</p>
            <p style="font-size:11px; margin:2px 0;">RUC: ${e.ruc}</p>
            <p style="font-size:11px; margin:2px 0;">${e.direccion || ''}</p>
        </div>
        <div style="text-align:center; margin-bottom:8px;">
            <span style="font-size:13px; font-weight:bold; border:1px solid #000; padding:2px 10px;">
                ${v.tipo_comprobante === '03' ? 'BOLETA DE VENTA' : 'FACTURA'}
            </span>
            <p style="font-size:12px; font-weight:bold; margin:4px 0;">${v.numero_completo}</p>
        </div>
        <div style="margin-bottom:8px; font-size:11px;">
            <div style="display:flex; justify-content:space-between;"><span>Fecha:</span><span>${v.fecha_emision}</span></div>
            <div style="display:flex; justify-content:space-between;"><span>Hora:</span><span>${v.hora_emision}</span></div>
            <div style="display:flex; justify-content:space-between;"><span>Pago:</span><span>${v.metodo_pago || 'efectivo'}</span></div>
        </div>
        <table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
            <thead>
                <tr style="border-bottom:1px solid #000;">
                    <th style="text-align:left; font-size:11px; padding:3px 0;">Producto</th>
                    <th style="text-align:center; font-size:11px; padding:3px 0;">Cant.</th>
                    <th style="text-align:right; font-size:11px; padding:3px 0;">P.U.</th>
                    <th style="text-align:right; font-size:11px; padding:3px 0;">Total</th>
                </tr>
            </thead>
            <tbody>${items}</tbody>
        </table>
        <div style="border-top:1px solid #000; padding-top:6px; font-size:11px;">
            <div style="display:flex; justify-content:space-between;"><span>Op. Gravada:</span><span>S/ ${Number(v.total_gravado).toFixed(2)}</span></div>
            <div style="display:flex; justify-content:space-between;"><span>IGV (18%):</span><span>S/ ${Number(v.total_igv).toFixed(2)}</span></div>
            <div style="display:flex; justify-content:space-between; font-weight:bold; font-size:14px; margin-top:4px; border-top:1px solid #000; padding-top:4px;">
                <span>TOTAL:</span><span>S/ ${Number(v.total || (Number(v.total_gravado)+Number(v.total_igv))).toFixed(2)}</span>
            </div>
        </div>
        <div style="text-align:center; margin-top:12px; font-size:11px;">
            <p style="margin:0;">¡Gracias por su compra!</p>
        </div>`
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
                    padding: 4px; 
                    max-width: 80mm; 
                    margin: 0 auto;
                    font-size: 11px;
                    line-height: 1.2;
                }
                * { box-sizing: border-box; margin: 0; padding: 0; }
                table { width: 100%; border-collapse: collapse; }
                p { margin: 1px 0; }
                div { margin-bottom: 2px; }
            </style>
        </head>
        <body>${contenido}</body>
        </html>
    `)
    ventana.document.close()
    setTimeout(() => ventana.print(), 500)
}

const imprimirA4 = () => {
    const izq = document.getElementById('ticket')?.innerHTML || ''
    const der = document.getElementById('ticket-productos')?.innerHTML || ''
    const contenido = `<div style="display:flex;gap:0;flex-direction:column;">${izq}<div style="margin-top:16px;">${der}</div></div>`
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
                div { page-break-inside: avoid; }
            </style>
        </head>
        <body>${contenido}</body>
        </html>
    `)
    ventana.document.close()
    setTimeout(() => ventana.print(), 500)
}

const enviarWhatsApp = async () => {
    const raw = prompt('Numero WhatsApp (9 digitos):')
    if (!raw) return
    const numero = raw.replace(/\D/g, '').replace(/^0+/, '')
    const numeroFinal = numero.startsWith('51') ? numero : '51' + numero
    const total = Number(props.venta.total || 0).toFixed(2)
    const items = (props.venta.detalle || []).map(d => '  - ' + d.descripcion + ' x' + d.cantidad + ' = S/ ' + Number(d.total).toFixed(2)).join('\n')
    const mensaje = '🧾 *Comprobante NEXPOS*\n\n📋 *' + props.venta.numero_completo + '*\n📅 Fecha: ' + props.venta.fecha_emision + '\n\n🛒 *Productos:*\n' + items + '\n\n💰 *TOTAL: S/ ' + total + '*\n\nGracias por su compra 🙏'
    try {
        const token = document.cookie.split(';').find(c => c.trim().startsWith('XSRF-TOKEN='))
        const csrfToken = token ? decodeURIComponent(token.split('=')[1]) : ''
        const res = await fetch('/api/whatsapp/enviar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-XSRF-TOKEN': csrfToken },
            body: JSON.stringify({ telefono: numeroFinal, mensaje })
        })
        const data = await res.json()
        alert(data.ok ? 'Mensaje enviado' : 'Error: ' + data.error)
    } catch(e) {
        alert('Error de conexion')
    }
}


onMounted(() => {
    if (props.imprimir) {
        setTimeout(() => imprimir(), 800)
    }
})
</script>