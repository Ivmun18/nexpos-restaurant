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
                                        <td style="padding:8px; font-size:13px; color:#1E293B;">{{ item.producto?.nombre || item.descripcion || item.nombre || 'Producto' }}</td>
                                        <td style="padding:8px; text-align:center; font-size:13px; color:#475569;">{{ item.cantidad }}</td>
                                        <td style="padding:8px; text-align:right; font-size:13px; color:#475569;">S/ {{ Number(item.precio_unitario).toFixed(2) }}</td>
                                        <td style="padding:8px; text-align:right; font-size:13px; font-weight:600; color:#1E293B;">S/ {{ (Number(item.total) || Number(item.precio_unitario||0)*Number(item.cantidad||1)).toFixed(2) }}</td>
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
                <button @click="enviarWhatsApp" style="padding:8px 16px; background:#10B981; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600;">
                    📱 WhatsApp
                </button>
                <button v-if="comprobante.estado === 'rechazado' || comprobante.estado === 'pendiente'"
                    @click="reenviarSunat" :disabled="reenviando"
                    style="flex:1; padding:14px; background:linear-gradient(135deg,#F59E0B,#D97706); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                    {{ reenviando ? '⏳ Reenviando...' : '🔄 Reenviar a SUNAT' }}
                </button>
                <button v-if="comprobante.estado === 'aceptado'"
                    @click="anularComprobante" :disabled="anulando"
                    style="flex:1; padding:14px; background:linear-gradient(135deg,#EF4444,#DC2626); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                    {{ anulando ? '⏳ Anulando...' : '🗑️ Anular' }}
                </button>
                <div v-if="mostrarInputWhatsApp" style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:999;" @click.self="mostrarInputWhatsApp=false">
                    <div style="background:white; padding:24px; border-radius:12px; max-width:400px; width:90%;">
                        <h3 style="margin:0 0 16px; font-size:18px; font-weight:700;">📱 Enviar por WhatsApp</h3>
                        <p style="margin:0 0 12px; color:#64748B; font-size:14px;">Número del cliente (9 dígitos):</p>
                        <input v-model="numeroWhatsApp" type="tel" placeholder="987654321" style="width:100%; padding:12px; border:1px solid #E2E8F0; border-radius:8px; font-size:16px; margin-bottom:16px; box-sizing:border-box;" />
                        <div style="display:flex; gap:8px; justify-content:flex-end;">
                            <button @click="mostrarInputWhatsApp=false" style="padding:10px 16px; background:#E2E8F0; border:none; border-radius:8px; cursor:pointer;">Cancelar</button>
                            <a :href="whatsappUrl" target="_blank" rel="noopener" @click="mostrarInputWhatsApp=false" :style="{padding:'10px 16px', background: numeroWhatsApp ? '#25D366' : '#94A3B8', color:'white', borderRadius:'8px', textDecoration:'none', fontWeight:'600', pointerEvents: numeroWhatsApp ? 'auto' : 'none'}">Abrir WhatsApp</a>
                        </div>
                    </div>
                </div>
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
import { ref, computed, onMounted } from 'vue'
import QRCode from 'qrcode'
import axios from 'axios'

const props = defineProps({
    comprobante: Object,
    pedidos:  { type: Array, default: () => [] },
    imprimir: { type: Boolean, default: false },
})

const alertStyle = computed(() => {
    return {
        background: props.comprobante.aceptada_por_sunat ? '#D1FAE5' : (props.comprobante.enlace_pdf ? '#FEF3C7' : '#F1F5F9'),
        color: props.comprobante.aceptada_por_sunat ? '#065F46' : (props.comprobante.enlace_pdf ? '#92400E' : '#475569'),
    }
})

const tipoComprobanteTexto = (tipo) => ({
    '01': 'FACTURA ELECTRÓNICA',
    '03': 'BOLETA DE VENTA ELECTRÓNICA',
    '07': 'NOTA DE CRÉDITO ELECTRÓNICA',
}[tipo] || 'COMPROBANTE ELECTRÓNICO')

const representacionTexto = (tipo) => ({
    '01': 'Representación Impresa de la Factura Electrónica',
    '07': 'Representación Impresa de la Nota de Crédito Electrónica',
}[tipo] || 'Representación Impresa de la Boleta Electrónica')

const enLetras = (n) => {
    const u = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE', 'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE']
    const d = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA']
    const c = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS']
    if (n === 0) return 'CERO'
    if (n === 100) return 'CIEN'
    if (n < 16) return u[n]
    if (n < 20) return 'DIECI' + u[n - 10]
    if (n === 20) return 'VEINTE'
    if (n < 30) return 'VEINTI' + u[n - 20]
    if (n < 100) return d[Math.floor(n / 10)] + (n % 10 ? ' Y ' + u[n % 10] : '')
    if (n < 1000) return c[Math.floor(n / 100)] + (n % 100 ? ' ' + enLetras(n % 100) : '')
    if (n < 2000) return 'MIL' + (n % 1000 ? ' ' + enLetras(n % 1000) : '')
    if (n < 1000000) return enLetras(Math.floor(n / 1000)) + ' MIL' + (n % 1000 ? ' ' + enLetras(n % 1000) : '')
    return String(n)
}

const numeroALetras = (numero) => {
    const entero = Math.floor(numero)
    const decimal = Math.round((numero - entero) * 100)
    return enLetras(entero) + ' CON ' + String(decimal).padStart(2, '0') + '/100 SOLES'
}

const imprimir = async () => {
    const c = props.comprobante
    const e = c.empresa || {}
    const caja = c.caja || {}

    let itemsJson = []
    if (c.items_json) {
        try {
            itemsJson = typeof c.items_json === 'string' ? JSON.parse(c.items_json) : c.items_json
        } catch (err) {
            itemsJson = []
        }
    }

    const items = (itemsJson && itemsJson.length)
        ? itemsJson.map(i => ({
            cantidad: i.cantidad ?? 1,
            descripcion: i.descripcion || i.nombre || 'Producto',
            precio_unitario: Number(i.precio_unitario || 0),
            importe: Number(i.total ?? i.importe ?? (Number(i.precio_unitario || 0) * Number(i.cantidad || 1))),
        }))
        : (props.pedidos || []).flatMap(p => p.detalles || []).map(i => ({
            cantidad: i.cantidad ?? 1,
            descripcion: i.producto?.nombre || i.descripcion || i.nombre || 'Producto',
            precio_unitario: Number(i.precio_unitario || 0),
            importe: Number(i.total || 0) || (Number(i.precio_unitario || 0) * Number(i.cantidad || 1)),
        }))

    const itemsHtml = items.map(i => `
        <tr>
            <td style="text-align:center;font-size:10px;">${i.cantidad}</td>
            <td style="text-align:center;font-size:10px;">UND</td>
            <td style="font-size:10px;">${i.descripcion}</td>
            <td style="text-align:right;font-size:10px;">${i.precio_unitario.toFixed(2)}</td>
            <td style="text-align:right;font-size:10px;">${i.importe.toFixed(2)}</td>
        </tr>
    `).join('')

    const ahora = new Date()
    const fecha = c.fecha_emision ? new Date(c.fecha_emision).toLocaleDateString('es-PE') : ahora.toLocaleDateString('es-PE')
    const hora = ahora.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
    const numeroCompleto = c.numero_completo || (c.serie + '-' + String(c.numero).padStart(8, '0'))
    const vendedor = caja.user?.name || '-'
    const mesaNombre = caja.mesa?.nombre || caja.mesa?.numero || 'S/N'

    const qrContenido = c.codigo_hash || numeroCompleto
    let qrImg = ''
    try {
        const qrDataUrl = await QRCode.toDataURL(qrContenido, { width: 120, margin: 1 })
        qrImg = `<img src="${qrDataUrl}" style="width:90px;height:90px;margin:8px auto;display:block;">`
    } catch (err) {
        qrImg = ''
    }

    const amazoniaTexto = e.zona_exonerada
        ? '<p style="text-align:center;margin-top:4px;">Bienes transferidos en la Amazonía para ser consumidos en la misma</p>'
        : ''

    const esNotaCredito = c.tipo_comprobante === '07'
    const pagoHtml = esNotaCredito ? '' : `
            <div style="display:flex;justify-content:space-between;"><span>EFECTIVO:</span><span>S/ ${Number(caja.monto_pagado || 0).toFixed(2)}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>VUELTO:</span><span>S/ ${Number(caja.vuelto || 0).toFixed(2)}</span></div>`

    const logoUrl = e.logo ? (/^https?:\/\//.test(e.logo) ? e.logo : '/storage/' + e.logo) : ''

    const contenido = `
        <div style="text-align:center;margin-bottom:6px;">
            ${logoUrl ? `<img src="${logoUrl}" style="max-width:120px;max-height:120px;margin:0 auto 4px;display:block;">` : ''}
            <p style="font-size:16px;font-weight:bold;margin:0;font-family:'Georgia',serif;">${e.nombre_comercial || e.razon_social || 'RESTAURANTE'}</p>
            <p style="font-size:10px;margin:2px 0;">De: ${e.razon_social || ''}</p>
            <p style="font-size:10px;margin:2px 0;">RUC: ${e.ruc || ''}</p>
            <p style="font-size:10px;margin:2px 0;">${e.direccion || ''}</p>
        </div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div style="text-align:center;margin-bottom:6px;">
            <p style="font-size:12px;font-weight:bold;margin:0;">${tipoComprobanteTexto(c.tipo_comprobante)}</p>
            <p style="font-size:12px;font-weight:bold;margin:2px 0;">${numeroCompleto}</p>
        </div>
        <div style="font-size:10px;margin-bottom:6px;">
            <div style="display:flex;justify-content:space-between;"><span>FECHA:</span><span>${fecha}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>HORA:</span><span>${hora}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>PUNTO DE VENTA:</span><span>PRINCIPAL</span></div>
            <div style="display:flex;justify-content:space-between;"><span>MESA:</span><span>${mesaNombre}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>VENDEDOR:</span><span>${vendedor}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>CONDICIÓN:</span><span>CONTADO</span></div>
        </div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div style="font-size:10px;margin-bottom:6px;">
            <p style="margin:1px 0;">SEÑORES: ${c.cliente_nombre || ''}</p>
            <p style="margin:1px 0;">DIRECCIÓN: ${c.cliente_direccion || '-'}</p>
            <p style="margin:1px 0;">DNI/RUC: ${c.cliente_numero_documento || ''}</p>
        </div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:6px;">
            <thead>
                <tr style="border-bottom:1px solid #000;">
                    <th style="text-align:center;font-size:10px;">Cant.</th>
                    <th style="text-align:center;font-size:10px;">U/Med.</th>
                    <th style="text-align:left;font-size:10px;">Descripción</th>
                    <th style="text-align:right;font-size:10px;">Prec.Unit.</th>
                    <th style="text-align:right;font-size:10px;">Importe</th>
                </tr>
            </thead>
            <tbody>${itemsHtml}</tbody>
        </table>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div style="font-size:11px;">
            <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:13px;">
                <span>IMPORTE TOTAL:</span><span>S/ ${Number(c.total || 0).toFixed(2)}</span>
            </div>
            ${pagoHtml}
            <p style="margin-top:6px;font-size:10px;">SON: ${numeroALetras(Number(c.total || 0))}</p>
        </div>
        <div style="border-top:1px dashed #000;margin:8px 0;"></div>
        <div style="text-align:center;font-size:9px;">
            ${qrImg}
            <p>${representacionTexto(c.tipo_comprobante)}</p>
            ${c.codigo_hash ? `<p style="word-break:break-all;">Código Hash: ${c.codigo_hash}</p>` : ''}
            ${amazoniaTexto}
            <p style="margin-top:4px;">Consulte su Comprobante en https://cpe.appfact.pe</p>
            <p style="margin-top:4px;">Sistema desarrollado por NEXPOS Solutions</p>
        </div>
    `
    const ventana = window.open('', '_blank')
    ventana.document.write('<html><head><title>Comprobante</title><style>body{font-family:monospace;padding:4px;max-width:80mm;margin:0 auto;font-size:11px;line-height:1.3;}*{box-sizing:border-box;margin:0;padding:0;}p{margin:1px 0;}@media print{@page{margin:2mm;size:80mm auto;}}</style></head><body>' + contenido + '</body></html>')
    ventana.document.close()
    setTimeout(() => ventana.print(), 500)
}

const numeroWhatsApp = ref('')
const mostrarInputWhatsApp = ref(false)

const whatsappUrl = computed(() => {
    if (!numeroWhatsApp.value) return '#'
    const numero = numeroWhatsApp.value.replace(/[^0-9]/g, '').replace(/^0+/, '')
    const numeroFinal = numero.startsWith('51') ? numero : '51' + numero
    const c = props.comprobante
    const mensaje = '*Comprobante NEXPOS*\n\n*' + (c.numero_completo || c.serie + '-' + c.numero) + '*\nFecha: ' + c.fecha_emision + '\nCliente: ' + c.cliente_nombre + '\n\n*TOTAL: S/ ' + Number(c.total).toFixed(2) + '*\n\nGracias por su visita'
    return 'https://wa.me/' + numeroFinal + '?text=' + encodeURIComponent(mensaje)
})

const enviarWhatsApp = () => {
    mostrarInputWhatsApp.value = true
}

const reenviando = ref(false)

const reenviarSunat = async () => {
    reenviando.value = true
    try {
        const res = await axios.post(`/comprobantes/${props.comprobante.id}/reenviar`)
        alert(res.data.mensaje)
        if (res.data.success) {
            router.reload()
        }
    } catch (err) {
        alert('Error al reenviar: ' + (err.response?.data?.mensaje || err.message))
    } finally {
        reenviando.value = false
    }
}

const anulando = ref(false)

const anularComprobante = async () => {
    if (!confirm('¿Estás seguro de anular este comprobante? Esta acción no se puede deshacer.')) {
        return
    }
    anulando.value = true
    try {
        const res = await axios.post(`/comprobantes/${props.comprobante.id}/anular`)
        alert(res.data.mensaje)
        if (res.data.success) {
            router.reload()
        }
    } catch (err) {
        alert('Error al anular: ' + (err.response?.data?.mensaje || err.message))
    } finally {
        anulando.value = false
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
