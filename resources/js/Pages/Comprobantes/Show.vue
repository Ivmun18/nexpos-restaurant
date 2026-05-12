<template>
    <AppLayout title="Comprobante Electrónico" subtitle="Detalle del comprobante emitido">
        
        <div style="max-width:800px; margin:0 auto;">
            
            <!-- Estado SUNAT -->
            <div :style="alertStyle" style="border-radius:16px; padding:20px; margin-bottom:24px; display:flex; align-items:center; gap:16px;">
                <div style="font-size:48px;">
                    {{ comprobante.aceptada_por_sunat ? '✅' : (comprobante.enlace_pdf ? '⏳' : '📋') }}
                </div>
                <div style="flex:1;">
                    <h3 style="font-size:18px; font-weight:700; margin:0 0 4px;">
                        {{ comprobante.aceptada_por_sunat ? '¡Comprobante Aceptado por SUNAT!' : (comprobante.enlace_pdf ? 'Comprobante en Proceso' : 'Comprobante Local') }}
                    </h3>
                    <p style="font-size:14px; margin:0; opacity:0.9;">
                        {{ comprobante.aceptada_por_sunat ? 'El comprobante fue enviado y aceptado correctamente.' : (comprobante.enlace_pdf ? 'El comprobante está siendo procesado por SUNAT.' : 'Comprobante registrado localmente. Sin envío a SUNAT.') }}
                    </p>
                </div>
            </div>

            <!-- Información del comprobante -->
            <div id="comprobante-content" style="background:white; border-radius:16px; padding:24px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                
                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:24px;">
                    <div>
                        <h2 style="font-size:24px; font-weight:700; color:#1E293B; margin:0 0 8px;">
                            {{ comprobante.tipo_comprobante_nombre }}
                        </h2>
                        <p style="font-size:16px; color:#64748B; margin:0;">
                            {{ comprobante.numero_completo }}
                        </p>
                    </div>
                    <div style="text-align:right;">
                        <p style="font-size:12px; color:#64748B; margin:0;">Fecha de Emisión</p>
                        <p style="font-size:16px; font-weight:600; color:#1E293B; margin:4px 0 0;">
                            {{ formatFecha(comprobante.fecha_emision) }}
                        </p>
                    </div>
                </div>

                <div style="border-top:2px solid #F1F5F9; padding-top:20px;">
                    
                    <!-- Cliente -->
                    <div style="margin-bottom:20px;">
                        <p style="font-size:12px; color:#64748B; font-weight:700; margin:0 0 8px;">CLIENTE</p>
                        <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">
                            {{ comprobante.cliente_nombre }}
                        </p>
                        <p style="font-size:14px; color:#64748B; margin:4px 0 0;">
                            {{ comprobante.cliente_tipo_documento === '6' ? 'RUC' : 'DNI' }}: {{ comprobante.cliente_numero_documento }}
                        </p>
                        <p v-if="comprobante.cliente_direccion" style="font-size:14px; color:#64748B; margin:4px 0 0;">
                            {{ comprobante.cliente_direccion }}
                        </p>
                        <p v-if="comprobante.cliente_email" style="font-size:14px; color:#64748B; margin:4px 0 0;">
                            📧 {{ comprobante.cliente_email }}
                        </p>
                    </div>

                    <!-- Detalle productos -->
                    <div v-if="pedidos && pedidos.length" style="margin-bottom:20px;">
                        <p style="font-size:12px; color:#64748B; font-weight:700; margin:0 0 8px;">DETALLE DEL CONSUMO</p>
                        <table style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr style="background:#F8FAFC;">
                                    <th style="padding:8px; text-align:left; font-size:12px; color:#64748B;">Producto</th>
                                    <th style="padding:8px; text-align:center; font-size:12px; color:#64748B;">Cant.</th>
                                    <th style="padding:8px; text-align:right; font-size:12px; color:#64748B;">Precio</th>
                                    <th style="padding:8px; text-align:right; font-size:12px; color:#64748B;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-for="pedido in pedidos" :key="pedido.id">
                                    <tr v-for="item in pedido.detalles" :key="item.id" style="border-top:1px solid #F1F5F9;">
                                        <td style="padding:8px; font-size:13px; color:#1E293B;">{{ item.producto?.nombre || item.descripcion || item.nombre }}</td>
                                        <td style="padding:8px; text-align:center; font-size:13px; color:#475569;">{{ item.cantidad }}</td>
                                        <td style="padding:8px; text-align:right; font-size:13px; color:#475569;">S/ {{ Number(item.precio_unitario).toFixed(2) }}</td>
                                        <td style="padding:8px; text-align:right; font-size:13px; font-weight:600; color:#1E293B;">S/ {{ Number(item.total).toFixed(2) }}</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Desglose -->
                    <div style="background:#F8FAFC; border-radius:12px; padding:16px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="font-size:14px; color:#64748B;">Subtotal (Gravada):</span>
                            <span style="font-size:14px; font-weight:600; color:#1E293B;">S/ {{ formatNumber(comprobante.total_gravada) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                            <span style="font-size:14px; color:#64748B;">IGV (18%):</span>
                            <span style="font-size:14px; font-weight:600; color:#1E293B;">S/ {{ formatNumber(comprobante.total_igv) }}</span>
                        </div>
                        <div style="border-top:2px solid #E2E8F0; padding-top:12px; display:flex; justify-content:space-between;">
                            <span style="font-size:18px; font-weight:700; color:#1E293B;">TOTAL:</span>
                            <span style="font-size:24px; font-weight:700; color:#14B8A6;">S/ {{ formatNumber(comprobante.total) }}</span>
                        </div>
                    </div>

                    <!-- Hash -->
                    <div v-if="comprobante.codigo_hash" style="margin-top:16px; padding:12px; background:#FEF3C7; border-radius:8px;">
                        <p style="font-size:11px; color:#92400E; font-weight:700; margin:0 0 4px;">CÓDIGO HASH</p>
                        <p style="font-size:11px; color:#92400E; font-family:monospace; margin:0; word-break:break-all;">
                            {{ comprobante.codigo_hash }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Descargas -->
            <div style="background:white; border-radius:16px; padding:24px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 16px;">
                    📥 Descargar Archivos
                </h3>
                
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:12px;">
                    <a v-if="comprobante.enlace_pdf" :href="comprobante.enlace_pdf" target="_blank"
                        style="padding:16px; background:linear-gradient(135deg,#EF4444,#DC2626); color:white; border-radius:12px; text-decoration:none; text-align:center; font-weight:600; display:flex; align-items:center; justify-content:center; gap:8px;">
                        <span style="font-size:24px;">📄</span>
                        <span>Descargar PDF</span>
                    </a>
                    
                    <a v-if="comprobante.enlace_xml" :href="comprobante.enlace_xml" target="_blank"
                        style="padding:16px; background:linear-gradient(135deg,#10B981,#059669); color:white; border-radius:12px; text-decoration:none; text-align:center; font-weight:600; display:flex; align-items:center; justify-content:center; gap:8px;">
                        <span style="font-size:24px;">📋</span>
                        <span>Descargar XML</span>
                    </a>
                    
                    <a v-if="comprobante.enlace_cdr" :href="comprobante.enlace_cdr" target="_blank"
                        style="padding:16px; background:linear-gradient(135deg,#3B82F6,#2563EB); color:white; border-radius:12px; text-decoration:none; text-align:center; font-weight:600; display:flex; align-items:center; justify-content:center; gap:8px;">
                        <span style="font-size:24px;">📦</span>
                        <span>Descargar CDR</span>
                    </a>
                </div>
            </div>

            <!-- Botones -->
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <button @click="$inertia.visit('/comprobantes')"
                    style="flex:1; padding:14px; background:#F1F5F9; color:#64748B; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                    ← Volver a Lista
                </button>
                <button @click="imprimir"
                    style="flex:1; padding:14px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                    🖨️ Imprimir Ticket
                </button>
                <button @click="enviarWhatsApp"
                    style="flex:1; padding:14px; background:#dcfce7; color:#16a34a; border:1px solid #bbf7d0; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                    📱 WhatsApp
                </button>
                <button v-if="comprobante.caja" @click="$inertia.visit('/mesas')"
                    style="flex:1; padding:14px; background:#1E293B; color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                    🪑 Ver Mesas
                </button>
            </div>

        </div>

    </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { computed, onMounted } from 'vue'

const props = defineProps({
    comprobante: Object,
    imprimir: { type: Boolean, default: false },
})

const alertStyle = computed(() => {
    return {
        background: props.comprobante.aceptada_por_sunat ? '#D1FAE5' : (props.comprobante.enlace_pdf ? '#FEF3C7' : '#F1F5F9'),
        color: props.comprobante.aceptada_por_sunat ? '#065F46' : (props.comprobante.enlace_pdf ? '#92400E' : '#475569'),
    }
})

const imprimir = () => {
    const c = props.comprobante
    const e = c.empresa || {}
    const items = (props.pedidos || []).flatMap(p => p.detalles || [])
    const itemsHtml = items.map(i =>
        '<tr><td style="padding:2px 0;font-size:11px;">' + (i.producto?.nombre || i.descripcion || 'Producto') + '</td>' +
        '<td style="text-align:center;font-size:11px;">' + i.cantidad + '</td>' +
        '<td style="text-align:right;font-size:11px;">S/ ' + Number(i.precio_unitario||0).toFixed(2) + '</td>' +
        '<td style="text-align:right;font-size:11px;font-weight:bold;">S/ ' + Number(i.total||0).toFixed(2) + '</td></tr>'
    ).join('')
    const contenido = '<div style="text-align:center;margin-bottom:8px;">' +
        '<p style="font-size:14px;font-weight:bold;margin:0;">' + (e.nombre_comercial || e.razon_social || 'RESTAURANTE') + '</p>' +
        '<p style="font-size:11px;margin:2px 0;">RUC: ' + (e.ruc || '') + '</p>' +
        '<p style="font-size:11px;margin:2px 0;">' + (e.direccion || '') + '</p>' +
        '</div>' +
        '<div style="text-align:center;margin-bottom:8px;">' +
        '<span style="font-size:13px;font-weight:bold;border:1px solid #000;padding:2px 10px;">BOLETA DE VENTA</span>' +
        '<p style="font-size:12px;font-weight:bold;margin:4px 0;">' + (c.serie + '-' + String(c.numero).padStart(8,'0')) + '</p>' +
        '</div>' +
        '<div style="font-size:11px;margin-bottom:8px;">' +
        '<div style="display:flex;justify-content:space-between;"><span>Fecha:</span><span>' + c.fecha_emision + '</span></div>' +
        '<div style="display:flex;justify-content:space-between;"><span>Cliente:</span><span>' + c.cliente_nombre + '</span></div>' +
        '</div>' +
        '<table style="width:100%;border-collapse:collapse;margin-bottom:8px;">' +
        '<thead><tr style="border-bottom:1px solid #000;">' +
        '<th style="text-align:left;font-size:11px;">Producto</th>' +
        '<th style="text-align:center;font-size:11px;">Cant</th>' +
        '<th style="text-align:right;font-size:11px;">P.U.</th>' +
        '<th style="text-align:right;font-size:11px;">Total</th>' +
        '</tr></thead><tbody>' + itemsHtml + '</tbody></table>' +
        '<div style="border-top:1px solid #000;padding-top:4px;font-size:11px;">' +
        '<div style="display:flex;justify-content:space-between;font-weight:bold;font-size:14px;">' +
        '<span>TOTAL:</span><span>S/ ' + Number(c.total||0).toFixed(2) + '</span></div></div>' +
        '<div style="text-align:center;margin-top:8px;font-size:11px;"><p>¡Gracias por su visita!</p></div>'
    const ventana = window.open('', '_blank')
    ventana.document.write('<html><head><title>Comprobante</title><style>body{font-family:monospace;padding:4px;max-width:80mm;margin:0 auto;font-size:11px;line-height:1.2;}*{box-sizing:border-box;margin:0;padding:0;}p{margin:1px 0;}@media print{@page{margin:2mm;size:80mm auto;}}</style></head><body>' + contenido + '</body></html>')
    ventana.document.close()
    setTimeout(() => ventana.print(), 500)
}

const enviarWhatsApp = async () => {
    const raw = prompt('Número WhatsApp (9 dígitos):')
    if (!raw) return
    const numero = raw.replace(/\D/g, '').replace(/^0+/, '')
    const numeroFinal = numero.startsWith('51') ? numero : '51' + numero
    const c = props.comprobante
    const mensaje = '🧾 *Comprobante NEXPOS*\n\n📋 *' + (c.numero_completo || c.serie + '-' + c.numero) + '*\n📅 Fecha: ' + c.fecha_emision + '\n👤 Cliente: ' + c.cliente_nombre + '\n\n💰 *TOTAL: S/ ' + Number(c.total).toFixed(2) + '*\n\nGracias por su visita 🙏'
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
        alert('Error de conexión')
    }
}

const formatFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString('es-PE', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    })
}

onMounted(() => {
    if (props.imprimir) {
        setTimeout(() => imprimir(), 800)
    }
})

const formatNumber = (num) => {
    return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num || 0)
}
</script>
