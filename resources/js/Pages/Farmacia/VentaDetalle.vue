<template>
    <AppLayout title="Ticket de Venta" subtitle="Detalle del comprobante">

        <div style="max-width:600px; margin:0 auto;">

            <!-- Botones -->
            <div style="display:flex; gap:12px; margin-bottom:24px;">
                <a href="/farmacia/ventas" style="padding:10px 20px; background:white; color:#64748B; border-radius:10px; font-size:14px; font-weight:600; text-decoration:none; border:1px solid #E2E8F0;">
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
                    style="padding:10px 20px; background:linear-gradient(135deg,#25D366,#128C7E); color:white; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                    📱 Enviar WhatsApp
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
                        <span style="font-size:13px; color:#1E293B;">S/ {{ (Number(venta.total_gravado) + Number(venta.total_igv) + Number(venta.total_inafecto) + Number(venta.total_exonerado)).toFixed(2) }}</span>
                    </div>
                    <!-- Operación Gravada -->
                    <div v-if="Number(venta.total_gravado) > 0" style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Op. Gravada</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ Number(venta.total_gravado).toFixed(2) }}</span>
                    </div>
                    <!-- Operación Exonerada (código 20) -->
                    <div v-if="Number(venta.total_exonerado) > 0" style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Op. Exonerada</span>
                        <span style="font-size:13px; color:#16a34a;">S/ {{ Number(venta.total_exonerado).toFixed(2) }}</span>
                    </div>
                    <!-- Operación Inafecta (código 30 - RUS) -->
                    <div v-if="Number(venta.total_inafecto) > 0" style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Op. Inafecta</span>
                        <span style="font-size:13px; color:#f59e0b;">S/ {{ Number(venta.total_inafecto).toFixed(2) }}</span>
                    </div>
                    <!-- IGV solo si hay operación gravada -->
                    <div v-if="Number(venta.total_igv) > 0" style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">IGV (18%)</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ Number(venta.total_igv).toFixed(2) }}</span>
                    </div>
                    <!-- Si no hay IGV, mostrar leyenda -->
                    <div v-if="Number(venta.total_igv) === 0" style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">IGV</span>
                        <span style="font-size:13px; color:#94a3b8;">S/ 0.00</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding:10px 16px; background:#1E293B; border-radius:10px; margin-top:8px;">
                        <span style="font-size:16px; font-weight:700; color:white;">TOTAL</span>
                        <span style="font-size:24px; font-weight:900; color:white;">S/ {{ (Number(venta.total_gravado) + Number(venta.total_igv) + Number(venta.total_inafecto) + Number(venta.total_exonerado)).toFixed(2) }}</span>
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
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const reintentar = () => {
    if (confirm('¿Reintentar envío a SUNAT?')) {
        router.post(`/farmacia/ventas/${props.venta.id}/reintentar`)
    }
}

const anular = () => {
    if (confirm('¿Anular este comprobante?')) {
        router.post(`/farmacia/ventas/${props.venta.id}/anular`)
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
    const emp = props.empresa
    const total = Number(v.total).toFixed(2)
    const items = v.detalle?.map(d => {
        const nom = d.descripcion.substring(0, 20).padEnd(20)
        const cant = String(d.cantidad).padStart(3)
        const precio = ('S/'+Number(d.precio_unitario).toFixed(2)).padStart(8)
        const subtotal = ('S/'+Number(d.total).toFixed(2)).padStart(8)
        return `<tr>
            <td colspan="2" style="padding:1px 0; font-size:11px;">${d.descripcion}</td>
        </tr>
        <tr>
            <td style="padding:1px 0; font-size:11px;">${cant} x ${Number(d.precio_unitario).toFixed(2)}</td>
            <td style="text-align:right; padding:1px 0; font-size:11px;">S/ ${Number(d.total).toFixed(2)}</td>
        </tr>`
    }).join('') || ''

    const ventana = window.open('', '_blank')
    ventana.document.write(`
        <html>
        <head>
            <title>Ticket - ${v.numero_completo}</title>
            <style>
                @media print {
                    body { margin: 0; }
                    @page { margin: 2mm; size: 80mm auto; }
                    .no-print { display: none; }
                }
                body {
                    font-family: 'Courier New', monospace;
                    width: 76mm;
                    margin: 0 auto;
                    padding: 4px;
                    font-size: 11px;
                    color: #000;
                }
                .center { text-align: center; }
                .right { text-align: right; }
                .bold { font-weight: bold; }
                .line { border-top: 1px dashed #000; margin: 4px 0; }
                table { width: 100%; border-collapse: collapse; }
                .total-row td { font-size: 13px; font-weight: bold; border-top: 1px solid #000; padding-top: 4px; }
            </style>
        </head>
        <body>
            <div class="center bold" style="font-size:13px;">${emp?.nombre_comercial || 'FARMACIA'}</div>
            <div class="center">RUC: ${emp?.ruc || ''}</div>
            <div class="center">${emp?.direccion || ''}</div>
            ${emp?.telefono ? `<div class="center">Tel: ${emp.telefono}</div>` : ''}
            <div class="line"></div>
            <div class="center bold" style="font-size:12px;">${v.tipo_comprobante === '03' ? 'BOLETA DE VENTA' : 'FACTURA'}</div>
            <div class="center bold">${v.numero_completo}</div>
            <div class="center">${v.fecha_emision?.substring(0,10) || ''}</div>
            <div class="line"></div>
            ${v.cliente_razon_social ? `<div>Cliente: ${v.cliente_razon_social}</div>` : ''}
            ${v.cliente_num_doc ? `<div>Doc: ${v.cliente_num_doc}</div>` : ''}
            <div class="line"></div>
            <table>${items}</table>
            <div class="line"></div>
            <table>
                ${Number(v.total_gravado) > 0 ? `<tr><td>Op. Gravada</td><td class="right">S/ ${Number(v.total_gravado).toFixed(2)}</td></tr>` : ''}
                ${Number(v.total_igv) > 0 ? `<tr><td>IGV (18%)</td><td class="right">S/ ${Number(v.total_igv).toFixed(2)}</td></tr>` : ''}
                ${Number(v.total_exonerado) > 0 ? `<tr><td>Op. Exonerada</td><td class="right">S/ ${Number(v.total_exonerado).toFixed(2)}</td></tr>` : ''}
                ${Number(v.total_inafecto) > 0 ? `<tr><td>Op. Inafecta</td><td class="right">S/ ${Number(v.total_inafecto).toFixed(2)}</td></tr>` : ''}
                <tr class="total-row"><td class="bold">TOTAL</td><td class="right bold">S/ ${total}</td></tr>
                <tr><td>Método de pago</td><td class="right">${v.metodo_pago || ''}</td></tr>
            </table>
            <div class="line"></div>
            <div class="center" style="font-size:10px;">Gracias por su compra</div>
            <div class="center" style="font-size:10px;">Representación impresa de comprobante electrónico</div>
            <div class="line"></div>
            <button class="no-print" onclick="window.print()" style="width:100%; padding:8px; margin-top:8px; cursor:pointer;">🖨️ Imprimir</button>
        </body>
        </html>
    `)
    ventana.document.close()
    setTimeout(() => ventana.print(), 500)
}

const enviarWhatsApp = async () => {
    const tel = prompt('📱 Ingresa el número de WhatsApp del cliente (ej: 987654321):')
    if (!tel) return
    const numero = '51' + tel.replace(/[^0-9]/g, '').slice(-9)
    
    const items = props.venta.detalle?.map(d => 
        `• ${d.descripcion} x${d.cantidad} = S/ ${Number(d.total).toFixed(2)}`
    ).join('\n') || ''

    const mensaje = `🧾 *Comprobante NEXPOS*\n\n` +
        `📋 *${props.venta.numero_completo}*\n` +
        `📅 Fecha: ${props.venta.created_at?.slice(0,10)}\n\n` +
        `${items}\n\n` +
        `💰 *Total: S/ ${(Number(props.venta.total_gravado) + Number(props.venta.total_igv) + Number(props.venta.total_inafecto) + Number(props.venta.total_exonerado)).toFixed(2)}*\n\n` +
        `Gracias por su compra 🙏`

    try {
        const token = document.cookie.split(';').find(c => c.trim().startsWith('XSRF-TOKEN='))
        const csrfToken = token ? decodeURIComponent(token.split('=')[1]) : ''
        const res = await fetch('/api/whatsapp/enviar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ telefono: numero, mensaje })
        })
        const data = await res.json()
        if (data.ok) {
            alert('✅ Mensaje enviado correctamente')
        } else {
            alert('❌ Error: ' + (data.error || 'No se pudo enviar'))
        }
    } catch(e) {
        alert('❌ Error de conexión')
    }
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