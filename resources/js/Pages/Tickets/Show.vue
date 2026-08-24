<template>
    <AppLayout title="Ticket de Venta" subtitle="Cobro sin comprobante electrónico">

        <div style="max-width:480px; margin:0 auto;">

            <!-- Resumen -->
            <div style="background:white; border-radius:16px; padding:24px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">

                <div style="text-align:center; margin-bottom:20px;">
                    <img v-if="logoUrl" :src="logoUrl" style="max-width:100px; max-height:100px; margin:0 auto 8px; display:block;">
                    <h2 style="font-size:18px; font-weight:700; color:#1E293B; margin:0;">
                        {{ empresa?.nombre_comercial || empresa?.razon_social || 'Ticket de venta' }}
                    </h2>
                    <p style="font-size:13px; color:#94A3B8; margin:4px 0 0;">TICKET DE VENTA — sin comprobante electrónico</p>
                </div>

                <div style="border-top:2px solid #F1F5F9; padding-top:16px; display:flex; flex-direction:column; gap:10px;">
                    <div style="display:flex; justify-content:space-between; font-size:14px;">
                        <span style="color:#64748B;">Mesa</span>
                        <span style="font-weight:600; color:#1E293B;">{{ caja.mesa?.nombre || caja.mesa?.numero || '-' }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:14px;">
                        <span style="color:#64748B;">Fecha</span>
                        <span style="font-weight:600; color:#1E293B;">{{ formatFecha(caja.created_at) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:14px;">
                        <span style="color:#64748B;">Cajero</span>
                        <span style="font-weight:600; color:#1E293B;">{{ caja.user?.name || '-' }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:14px;">
                        <span style="color:#64748B;">Método de pago</span>
                        <span style="font-weight:600; color:#1E293B; text-transform:capitalize;">{{ caja.metodo_pago }}</span>
                    </div>
                </div>

                <div style="background:#F8FAFC; border-radius:12px; padding:16px; margin-top:16px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-size:14px; color:#64748B;">Pagado</span>
                        <span style="font-size:14px; font-weight:600; color:#1E293B;">S/ {{ formatNumber(caja.monto_pagado) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                        <span style="font-size:14px; color:#64748B;">Vuelto</span>
                        <span style="font-size:14px; font-weight:600; color:#1E293B;">S/ {{ formatNumber(caja.vuelto) }}</span>
                    </div>
                    <div style="border-top:2px solid #E2E8F0; padding-top:12px; display:flex; justify-content:space-between;">
                        <span style="font-size:18px; font-weight:700; color:#1E293B;">TOTAL:</span>
                        <span style="font-size:24px; font-weight:700; color:#14B8A6;">S/ {{ formatNumber(caja.total) }}</span>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <button @click="imprimir"
                    style="flex:1; padding:14px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                    🖨️ Imprimir
                </button>
                <button @click="enviarWhatsApp"
                    style="padding:14px 16px; background:#10B981; color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                    📱 WhatsApp
                </button>
                <Link href="/mesas"
                    style="flex:1; padding:14px; background:#1E293B; color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; text-align:center; text-decoration:none; display:block; box-sizing:border-box;">
                    🪑 Volver a Mesas
                </Link>
            </div>

            <!-- Modal WhatsApp -->
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

        </div>

    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
    caja:     Object,
    empresa:  Object,
    imprimir: { type: Boolean, default: false },
})

const logoUrl = computed(() => {
    const logo = props.empresa?.logo
    if (!logo) return ''
    return /^https?:\/\//.test(logo) ? logo : '/storage/' + logo
})

const formatFecha = (fecha) => {
    return new Date(fecha).toLocaleString('es-PE', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

const formatNumber = (num) => {
    return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(num || 0)
}

const imprimir = () => {
    const c = props.caja
    const e = props.empresa || {}
    const mesa = c.mesa || {}
    const cajero = c.user?.name || '-'
    const fecha = new Date(c.created_at)
    const fechaStr = fecha.toLocaleDateString('es-PE')
    const horaStr = fecha.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit' })

    const contenido = `
        <div style="text-align:center;margin-bottom:6px;">
            ${logoUrl.value ? `<img src="${logoUrl.value}" style="max-width:120px;max-height:120px;margin:0 auto 4px;display:block;">` : ''}
            <p style="font-size:16px;font-weight:bold;margin:0;font-family:'Georgia',serif;">${e.nombre_comercial || e.razon_social || 'RESTAURANTE'}</p>
            <p style="font-size:10px;margin:2px 0;">RUC: ${e.ruc || ''}</p>
            <p style="font-size:10px;margin:2px 0;">${e.direccion || ''}</p>
        </div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div style="text-align:center;margin-bottom:6px;">
            <p style="font-size:12px;font-weight:bold;margin:0;">TICKET DE VENTA</p>
            <p style="font-size:10px;margin:2px 0;">(sin comprobante electrónico)</p>
        </div>
        <div style="font-size:10px;margin-bottom:6px;">
            <div style="display:flex;justify-content:space-between;"><span>FECHA:</span><span>${fechaStr}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>HORA:</span><span>${horaStr}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>MESA:</span><span>${mesa.nombre || mesa.numero || '-'}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>CAJERO:</span><span>${cajero}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>MÉTODO PAGO:</span><span>${(c.metodo_pago || '-').toUpperCase()}</span></div>
        </div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div style="font-size:11px;">
            <div style="display:flex;justify-content:space-between;"><span>PAGADO:</span><span>S/ ${Number(c.monto_pagado || 0).toFixed(2)}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>VUELTO:</span><span>S/ ${Number(c.vuelto || 0).toFixed(2)}</span></div>
            <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:13px;margin-top:4px;">
                <span>TOTAL:</span><span>S/ ${Number(c.total || 0).toFixed(2)}</span>
            </div>
        </div>
        <div style="border-top:1px dashed #000;margin:8px 0;"></div>
        <div style="text-align:center;font-size:9px;">
            <p>Documento sin validez tributaria</p>
            <p style="margin-top:4px;">Sistema desarrollado por NEXPOS Solutions</p>
        </div>
    `
    const ventana = window.open('', '_blank')
    ventana.document.write('<html><head><title>Ticket</title><style>body{font-family:monospace;padding:4px;max-width:80mm;margin:0 auto;font-size:11px;line-height:1.3;}*{box-sizing:border-box;margin:0;padding:0;}p{margin:1px 0;}@media print{@page{margin:2mm;size:80mm auto;}}</style></head><body>' + contenido + '</body></html>')
    ventana.document.close()
    setTimeout(() => ventana.print(), 500)
}

const numeroWhatsApp = ref('')
const mostrarInputWhatsApp = ref(false)

const whatsappUrl = computed(() => {
    if (!numeroWhatsApp.value) return '#'
    const numero = numeroWhatsApp.value.replace(/[^0-9]/g, '').replace(/^0+/, '')
    const numeroFinal = numero.startsWith('51') ? numero : '51' + numero
    const c = props.caja
    const mesaNombre = c.mesa?.nombre || c.mesa?.numero || '-'
    const fechaStr = new Date(c.created_at).toLocaleDateString('es-PE')
    const mensaje = '*Ticket NEXPOS*\n\nMesa: ' + mesaNombre + '\nFecha: ' + fechaStr + '\nMétodo de pago: ' + (c.metodo_pago || '-') + '\n\n*TOTAL: S/ ' + Number(c.total).toFixed(2) + '*\n\nGracias por su visita'
    return 'https://wa.me/' + numeroFinal + '?text=' + encodeURIComponent(mensaje)
})

const enviarWhatsApp = () => {
    mostrarInputWhatsApp.value = true
}

onMounted(() => {
    if (props.imprimir) {
        setTimeout(() => imprimir(), 800)
    }
})
</script>
