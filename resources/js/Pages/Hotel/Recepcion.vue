<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    reservas: Array,
    habitacionesDisponibles: Array,
    todasHabitaciones: Array,
    productos: Array,
    cargos: Array,
    huespedes: Array,
})

const showCheckin   = ref(false)
const showReservar  = ref(false)
const showCheckout  = ref(null)
const showExtender  = ref(null)
const showCambiarHab = ref(null)
const showPagoParcial = ref(null)
const cargando      = ref(false)
const ticket        = ref(null)

// ── FORM CHECK-IN / RESERVA ──
const form = ref({
    habitacion_id: '',
    tipo_documento: '1',
    numero_documento: '',
    nombre_completo: '',
    email: '',
    telefono: '',
    nacionalidad: 'Peruana',
    fecha_checkin: new Date(new Date().getTime() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0,16),
    fecha_checkout: (() => {
        const manana = new Date()
        manana.setDate(manana.getDate() + 1)
        manana.setHours(12, 0, 0, 0)
        return new Date(manana.getTime() - manana.getTimezoneOffset() * 60000).toISOString().slice(0,16)
    })(),
    num_huespedes: 1,
    observaciones: '',
    adelanto: 0,
    metodo_adelanto: 'efectivo',
})

// ── CONSULTA DNI/RUC RENIEC ──
const buscandoDoc   = ref(false)
const docEncontrado = ref(false)
const docError      = ref('')
let docTimer = null

async function onDocInput() {
    docEncontrado.value = false
    docError.value = ''
    form.value.nombre_completo = ''
    const doc = form.value.numero_documento.replace(/[^0-9]/g, '')
    form.value.numero_documento = doc
    const esDni = doc.length === 8
    const esRuc = doc.length === 11
    if (!esDni && !esRuc) return
    clearTimeout(docTimer)
    docTimer = setTimeout(async () => {
        buscandoDoc.value = true
        try {
            const res = await fetch(`/api/consulta-documento?documento=${doc}`)
            const data = await res.json()
            if (esDni && data.nombre_completo) {
                form.value.nombre_completo = data.nombre_completo
                docEncontrado.value = true
            } else if (esRuc && data.razon_social) {
                form.value.nombre_completo = data.razon_social
                docEncontrado.value = true
            } else {
                docError.value = esDni ? 'DNI no encontrado en RENIEC' : 'RUC no encontrado'
            }
        } catch(e) {
            docError.value = 'Error al consultar, ingresa el nombre manualmente'
        } finally {
            buscandoDoc.value = false
        }
    }, 600)
}

// ── CHECKOUT FORM ──
const pagoForm = ref({
    monto: 0,
    metodo_pago: 'efectivo',
    referencia: '',
    tipo_comprobante: 'ninguno',
    cliente_documento: '',
    cliente_nombre: '',
    cliente_email: '',
})

// Consulta DNI checkout
const docCheckoutEncontrado = ref(false)
const docCheckoutError = ref('')
const buscandoDocCheckout = ref(false)
let docCheckoutTimer = null

async function onDocCheckoutInput() {
    docCheckoutEncontrado.value = false
    docCheckoutError.value = ''
    const doc = pagoForm.value.cliente_documento.replace(/[^0-9]/g, '')
    pagoForm.value.cliente_documento = doc
    const esDni = doc.length === 8
    const esRuc = doc.length === 11
    if (!esDni && !esRuc) return
    clearTimeout(docCheckoutTimer)
    docCheckoutTimer = setTimeout(async () => {
        buscandoDocCheckout.value = true
        try {
            const res = await fetch(`/api/consulta-documento?documento=${doc}`)
            const data = await res.json()
            if (esDni && data.nombre_completo) {
                pagoForm.value.cliente_nombre = data.nombre_completo
                docCheckoutEncontrado.value = true
            } else if (esRuc && data.razon_social) {
                pagoForm.value.cliente_nombre = data.razon_social
                docCheckoutEncontrado.value = true
            } else {
                docCheckoutError.value = 'No encontrado'
            }
        } catch(e) {
            docCheckoutError.value = 'Error al consultar'
        } finally { buscandoDocCheckout.value = false }
    }, 600)
}

// ── CHECKIN ──
const hacerCheckin = async (esReserva = false) => {
    cargando.value = true
    try {
        const url = esReserva ? '/hotel/reservar' : '/hotel/checkin'
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify(form.value)
        })
        const data = await res.json()
        if (data.success) {
            alert('✅ ' + data.mensaje + (data.codigo ? ' — Código: ' + data.codigo : ''))
            showCheckin.value = false
            showReservar.value = false
            router.reload()
        } else {
            alert('❌ ' + (data.mensaje || 'Error'))
        }
    } finally { cargando.value = false }
}

// ── CONFIRMAR CHECKIN DE RESERVA ──
const confirmarCheckin = async (reserva) => {
    if (!confirm('¿Confirmar check-in para ' + reserva.huesped?.nombre_completo + '?')) return
    cargando.value = true
    try {
        const res = await fetch('/hotel/reservas/' + reserva.id + '/confirmar-checkin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({})
        })
        const data = await res.json()
        if (data.success) { alert('✅ ' + data.mensaje); router.reload() }
        else alert('❌ ' + data.mensaje)
    } finally { cargando.value = false }
}

// ── CHECKOUT ──
const hacerCheckout = async (reserva) => {
    cargando.value = true
    try {
        const res = await fetch('/hotel/checkout/' + reserva.id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify(pagoForm.value)
        })
        const data = await res.json()
        if (data.success) {
            showCheckout.value = null
            ticket.value = data.ticket
            if (data.comprobante_pdf) window.open(data.comprobante_pdf, '_blank')
            router.reload()
        } else {
            alert('❌ ' + (data.mensaje || 'Error'))
        }
    } finally { cargando.value = false }
}

// ── EXTENDER ESTADIA ──
const extenderForm = ref({ nueva_fecha_checkout: '' })
const extenderEstadia = async () => {
    cargando.value = true
    try {
        const res = await fetch('/hotel/reservas/' + showExtender.value.id + '/extender', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify(extenderForm.value)
        })
        const data = await res.json()
        if (data.success) { alert('✅ ' + data.mensaje); showExtender.value = null; router.reload() }
        else alert('❌ ' + data.mensaje)
    } finally { cargando.value = false }
}

// ── CAMBIAR HABITACION ──
const cambiarHabForm = ref({ nueva_habitacion_id: '' })
const cambiarHabitacion = async () => {
    cargando.value = true
    try {
        const res = await fetch('/hotel/reservas/' + showCambiarHab.value.id + '/cambiar-hab', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify(cambiarHabForm.value)
        })
        const data = await res.json()
        if (data.success) { alert('✅ ' + data.mensaje); showCambiarHab.value = null; router.reload() }
        else alert('❌ ' + data.mensaje)
    } finally { cargando.value = false }
}

// ── PAGO PARCIAL ──
const pagoParcialForm = ref({ monto: 0, metodo_pago: 'efectivo', referencia: '' })
const imprimirTicket = async (reserva) => {
    try {
        const res = await fetch('/hotel/reservas/' + reserva.id + '/ticket')
        const d = await res.json()
        const fmt = (f) => f ? new Date(f).toLocaleDateString('es-PE') : '-'
        const fmtMoney = (n) => 'S/ ' + Number(n).toFixed(2)
        const logoHtml = d.empresa.logo ? `<img src="${d.empresa.logo}" style="height:60px;margin-bottom:8px;">` : ''
        const html = `<!DOCTYPE html><html><head><meta charset="UTF-8">
        <title>Ticket Check-in ${d.reserva.codigo}</title>
        <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { font-family: 'Courier New', monospace; font-size: 12px; color: #1a1a1a; background: #fff; }
            .ticket { width: 320px; margin: 0 auto; padding: 20px 16px; }
            .header { text-align: center; border-bottom: 2px dashed #ccc; padding-bottom: 14px; margin-bottom: 14px; }
            .hotel-name { font-size: 16px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; }
            .hotel-sub { font-size: 10px; color: #555; margin-top: 3px; }
            .titulo { text-align: center; font-size: 13px; font-weight: 700; background: #1E293B; color: #fff; padding: 6px; border-radius: 4px; margin-bottom: 14px; letter-spacing: 1px; }
            .codigo { text-align: center; font-size: 22px; font-weight: 900; letter-spacing: 2px; margin-bottom: 14px; color: #1E293B; }
            .seccion { margin-bottom: 12px; }
            .seccion-titulo { font-size: 9px; font-weight: 700; text-transform: uppercase; color: #6B7280; border-bottom: 1px solid #E5E7EB; padding-bottom: 3px; margin-bottom: 6px; letter-spacing: 1px; }
            .fila { display: flex; justify-content: space-between; margin-bottom: 4px; }
            .fila span { font-size: 11px; color: #374151; }
            .fila b { font-size: 11px; font-weight: 700; color: #111; text-align: right; max-width: 55%; }
            .total-box { background: #F0FDF4; border: 1px solid #86EFAC; border-radius: 6px; padding: 10px; margin: 12px 0; text-align: center; }
            .total-label { font-size: 10px; color: #166534; font-weight: 600; }
            .total-valor { font-size: 20px; font-weight: 900; color: #15803D; }
            .saldo-box { background: #FEF2F2; border: 1px solid #FECACA; border-radius: 6px; padding: 8px; margin-bottom: 12px; text-align: center; }
            .saldo-label { font-size: 10px; color: #991B1B; font-weight: 600; }
            .saldo-valor { font-size: 16px; font-weight: 900; color: #DC2626; }
            .footer { text-align: center; border-top: 2px dashed #ccc; padding-top: 12px; margin-top: 12px; font-size: 10px; color: #9CA3AF; }
            .bienvenida { text-align: center; font-size: 13px; font-weight: 700; color: #1E293B; margin-bottom: 4px; }
            @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
        </style></head><body>
        <div class="ticket">
            <div class="header">
                ${logoHtml}
                <div class="hotel-name">${d.empresa.nombre}</div>
                <div class="hotel-sub">${d.empresa.direccion || ''}</div>
                <div class="hotel-sub">RUC: ${d.empresa.ruc || ''} ${d.empresa.telefono ? '| Tel: ' + d.empresa.telefono : ''}</div>
            </div>
            <div class="titulo">✅ COMPROBANTE DE CHECK-IN</div>
            <div class="codigo">${d.reserva.codigo}</div>
            <div class="seccion">
                <div class="seccion-titulo">👤 Datos del Huésped</div>
                <div class="fila"><span>Nombre:</span><b>${d.huesped.nombre}</b></div>
                <div class="fila"><span>Documento:</span><b>${d.huesped.tipo_documento === '1' ? 'DNI' : d.huesped.tipo_documento === '7' ? 'Pasaporte' : 'Doc.'} ${d.huesped.numero_documento}</b></div>
                <div class="fila"><span>Nacionalidad:</span><b>${d.huesped.nacionalidad || 'Peruana'}</b></div>
                ${d.huesped.telefono ? `<div class="fila"><span>Teléfono:</span><b>${d.huesped.telefono}</b></div>` : ''}
            </div>
            <div class="seccion">
                <div class="seccion-titulo">🛏️ Habitación</div>
                <div class="fila"><span>Número:</span><b>Hab. ${d.habitacion.numero} — Piso ${d.habitacion.piso}</b></div>
                <div class="fila"><span>Tipo:</span><b>${d.habitacion.tipo}</b></div>
                <div class="fila"><span>Precio/noche:</span><b>${fmtMoney(d.reserva.precio_noche)}</b></div>
            </div>
            <div class="seccion">
                <div class="seccion-titulo">📅 Estadía</div>
                <div class="fila"><span>Check-in:</span><b>${fmt(d.reserva.fecha_checkin)}</b></div>
                <div class="fila"><span>Check-out:</span><b>${fmt(d.reserva.fecha_checkout)}</b></div>
                <div class="fila"><span>Noches:</span><b>${d.reserva.num_noches}</b></div>
                <div class="fila"><span>Huéspedes:</span><b>${d.reserva.num_huespedes}</b></div>
                ${d.reserva.observaciones ? `<div class="fila"><span>Observ.:</span><b>${d.reserva.observaciones}</b></div>` : ''}
            </div>
            <div class="total-box">
                <div class="total-label">TOTAL DE ESTADÍA</div>
                <div class="total-valor">${fmtMoney(d.reserva.total)}</div>
            </div>
            ${d.reserva.saldo > 0 ? `
            <div class="saldo-box">
                <div class="saldo-label">SALDO PENDIENTE</div>
                <div class="saldo-valor">${fmtMoney(d.reserva.saldo)}</div>
            </div>` : '<div style="text-align:center;color:#15803D;font-weight:700;font-size:12px;margin-bottom:12px;">✅ PAGADO COMPLETO</div>'}
            <div class="bienvenida">¡Bienvenido a nuestro hotel!</div>
            <div class="footer">
                Emitido: ${new Date().toLocaleString('es-PE')}<br>
                Conserve este comprobante durante su estadía
            </div>
        </div>
        <script>window.onload = () => { window.print(); }<\/script>
        </body></html>`

        const win = window.open('', '_blank', 'width=400,height=700')
        win.document.write(html)
        win.document.close()
    } catch(e) {
        alert('Error al generar ticket: ' + e.message)
    }
}

const cancelarReserva = async (reserva) => {
    if (!confirm('¿Cancelar la reserva de ' + reserva.huesped?.nombre_completo + '?')) return
    cargando.value = true
    try {
        const res = await fetch('/hotel/reservas/' + reserva.id + '/cancelar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({})
        })
        const data = await res.json()
        if (data.success) { alert('✅ ' + data.mensaje); router.reload() }
        else alert('❌ ' + data.mensaje)
    } finally { cargando.value = false }
}

const registrarPago = async () => {
    cargando.value = true
    try {
        const res = await fetch('/hotel/reservas/' + showPagoParcial.value.id + '/pago', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify(pagoParcialForm.value)
        })
        const data = await res.json()
        if (data.success) {
            alert('✅ Pago registrado. Saldo: S/ ' + Number(data.saldo).toFixed(2))
            showPagoParcial.value = null
            router.reload()
        } else alert('❌ ' + data.mensaje)
    } finally { cargando.value = false }
}

const infoHuesped = ref(null)
const showCargo = ref(null)
const formCargo = ref({ producto_id: '', cantidad: 1 })

const agregarCargo = () => {
    if (!formCargo.value.producto_id) return
    router.post('/hotel/cargos', {
        reserva_id:  showCargo.value.id,
        producto_id: formCargo.value.producto_id,
        cantidad:    formCargo.value.cantidad,
    }, { onSuccess: () => { showCargo.value = null; formCargo.value = { producto_id: '', cantidad: 1 } } })
}

const eliminarCargo = (id) => {
    if (!confirm('¿Eliminar cargo?')) return
    router.delete('/hotel/cargos/' + id)
}

const clickHabitacion = (h) => {
    if (h.estado === 'disponible') {
        form.value.habitacion_id = h.id
        showCheckin.value = true
        infoHuesped.value = null
    } else if (h.estado === 'ocupada') {
        const reserva = props.reservas ? props.reservas.find(r => r.habitacion_id === h.id && r.estado === 'checkin') : null
        infoHuesped.value = reserva ? {
            reserva: reserva,
            habitacion: 'Hab. ' + h.numero + ' — ' + h.tipo?.nombre,
            huesped: reserva.huesped?.nombre_completo,
            documento: reserva.huesped?.numero_documento,
            checkin: new Date(reserva.fecha_checkin).toLocaleDateString('es-PE'),
            checkout: new Date(reserva.fecha_checkout_previsto).toLocaleDateString('es-PE'),
            total: reserva.total,
            pagado: reserva.monto_pagado,
        } : null
    }
}

const estadoBadge = (estado) => {
    const m = { reservado: '#3B82F6', checkin: '#16A34A', checkout: '#6B7280', cancelado: '#DC2626', reservada: '#8B5CF6' }
    return m[estado] || '#6B7280'
}
</script>

<template>
    <AppLayout title="Recepción">
        <div style="padding:24px; font-family:'Inter',sans-serif;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:24px; font-weight:700; color:#1E293B; margin:0;">🏁 Recepción</h1>
                    <p style="color:#64748B; font-size:13px; margin:4px 0 0;">Check-in y Check-out de huéspedes</p>
                </div>
                <button @click="showCheckin = true"
                    style="background:#3B82F6; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:14px;">
                    ➕ Nuevo Check-in
                </button>
                <button @click="showReservar = true" style="background:linear-gradient(135deg,#3B82F6,#1D4ED8); color:white; border:none; padding:10px 20px; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer;">
                    📅 Reservar
                </button>
            </div>

            <!-- Mapa rápido de habitaciones -->
            <div style="background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.08); padding:16px; margin-bottom:20px;">
                <div style="font-size:13px; font-weight:600; color:#374151; margin-bottom:12px;">🗺️ Estado de habitaciones</div>
                <div style="display:flex; flex-wrap:wrap; gap:10px;">
                    <div v-for="h in todasHabitaciones" :key="h.id"
                        @click="clickHabitacion(h)"
                        :style="{
                            padding:'10px 14px', borderRadius:'8px', fontSize:'12px', fontWeight:'600', minWidth:'110px', textAlign:'center',
                            cursor: h.estado==='disponible' || h.estado==='ocupada' ? 'pointer' : 'default',
                            background: h.estado==='disponible' ? '#DCFCE7' : h.estado==='ocupada' ? '#FEE2E2' : h.estado==='limpieza' ? '#FEF9C3' : '#F1F5F9',
                            color: h.estado==='disponible' ? '#16A34A' : h.estado==='ocupada' ? '#DC2626' : h.estado==='limpieza' ? '#CA8A04' : h.estado==='reservada' ? '#1D4ED8' : '#6B7280',
                            border: '2px solid',
                            borderColor: h.estado==='disponible' ? '#86EFAC' : h.estado==='ocupada' ? '#FCA5A5' : h.estado==='limpieza' ? '#FDE047' : h.estado==='reservada' ? '#93C5FD' : '#E2E8F0',
                            transform: h.estado==='disponible' || h.estado==='ocupada' ? 'scale(1)' : '',
                        }">
                        <div style="font-size:15px; font-weight:700;">Hab. {{ h.numero }}</div>
                        <div style="font-size:11px; opacity:0.8;">{{ h.tipo?.nombre }}</div>
                        <div style="font-size:12px; margin-top:4px;">
                            {{ h.estado==='disponible' ? '✅ Libre' : h.estado==='ocupada' ? '🔴 Ocupada' : h.estado==='limpieza' ? '🧹 Limpieza' : h.estado==='reservada' ? '📅 Reservada' : '🔧 Mant.' }}
                        </div>
                        <div style="font-size:11px;">S/ {{ h.tipo?.precio_noche }}/noche</div>
                        <div v-if="h.estado==='disponible'" style="font-size:10px; margin-top:4px; opacity:0.7;">Clic para check-in</div>
                        <div v-if="h.estado==='ocupada'" style="font-size:10px; margin-top:4px; opacity:0.7;">Clic para ver info</div>
                        <!-- Badge reserva futura -->
                        <div v-if="h.reservas_futuras?.length"
                            style="margin-top:6px; background:#1D4ED8; color:white; border-radius:6px; padding:3px 6px; font-size:10px; font-weight:700; line-height:1.3;">
                            📅 Reservada {{ new Date(h.reservas_futuras[0].fecha_checkin).toLocaleDateString('es-PE', {day:'2-digit', month:'2-digit'}) }}
                            <div style="font-size:9px; opacity:0.85; font-weight:400;">{{ h.reservas_futuras[0].huesped?.nombre_completo?.split(' ')[0] }}</div>
                        </div>
                    </div>
                </div>

                <!-- Info huésped al clic en habitación ocupada -->
                <div v-if="infoHuesped" style="margin-top:16px; background:#FEF2F2; border:1px solid #FCA5A5; border-radius:8px; padding:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div style="font-weight:700; color:#DC2626; font-size:13px;">🔴 {{ infoHuesped.habitacion }}</div>
                        <button @click="infoHuesped=null" style="background:none; border:none; cursor:pointer; color:#94A3B8; font-size:16px;">✕</button>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-top:10px; font-size:12px;">
                        <div><span style="color:#64748B;">Huésped:</span><br><b>{{ infoHuesped.huesped }}</b></div>
                        <div><span style="color:#64748B;">Documento:</span><br><b>{{ infoHuesped.documento }}</b></div>
                        <div><span style="color:#64748B;">Check-in:</span><br><b>{{ infoHuesped.checkin }}</b></div>
                        <div><span style="color:#64748B;">Check-out prev.:</span><br><b>{{ infoHuesped.checkout }}</b></div>
                        <div><span style="color:#64748B;">Total:</span><br><b>S/ {{ infoHuesped.total }}</b></div>
                        <div><span style="color:#64748B;">Pagado:</span><br><b>S/ {{ infoHuesped.pagado }}</b></div>
                    </div>
                    <div style="margin-top:14px; display:flex; justify-content:flex-end; gap:10px;">
                        <button @click="showCargo = infoHuesped.reserva; infoHuesped=null"
                            style="background:#7C3AED; color:#fff; border:none; padding:8px 20px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                            🛒 Agregar cargo
                        </button>
                        <button @click="showCheckout = infoHuesped.reserva; pagoForm.monto = infoHuesped.total - infoHuesped.pagado; infoHuesped=null"
                            style="background:#DC2626; color:#fff; border:none; padding:8px 20px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                            🚪 Hacer Check-out
                        </button>
                    </div>
                </div>

                <div style="display:flex; gap:16px; margin-top:12px; font-size:11px; color:#64748B;">
                    <span>✅ Disponible — clic para check-in</span><span>🔴 Ocupada — clic para ver huésped</span><span>🧹 Limpieza</span><span>🔧 Mantenimiento</span>
                </div>
            </div>

            <!-- Lista de reservas activas -->
            <div style="background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.08); overflow:hidden;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#F8FAFC;">
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">CÓDIGO</th>
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">HUÉSPED</th>
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">HABITACIÓN</th>
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">CHECK-IN</th>
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">CHECK-OUT</th>
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">TOTAL</th>
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">ESTADO</th>
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="reservas.length === 0">
                            <td colspan="8" style="padding:40px; text-align:center; color:#94A3B8;">No hay reservas activas</td>
                        </tr>
                        <tr v-for="r in reservas" :key="r.id" style="border-top:1px solid #F1F5F9;">
                            <td style="padding:12px 16px; font-weight:600; font-size:13px;">{{ r.codigo }}</td>
                            <td style="padding:12px 16px; font-size:13px;">
                                <div style="font-weight:600;">{{ r.huesped?.nombre_completo }}</div>
                                <div style="color:#64748B; font-size:11px;">{{ r.huesped?.numero_documento }}</div>
                            </td>
                            <td style="padding:12px 16px; font-size:13px;">
                                <div style="font-weight:600;">Hab. {{ r.habitacion?.numero }}</div>
                                <div style="color:#64748B; font-size:11px;">{{ r.habitacion?.tipo?.nombre }}</div>
                            </td>
                            <td style="padding:12px 16px; font-size:12px;">{{ new Date(r.fecha_checkin).toLocaleDateString('es-PE') }}</td>
                            <td style="padding:12px 16px; font-size:12px;">{{ new Date(r.fecha_checkout_previsto).toLocaleDateString('es-PE') }}</td>
                            <td style="padding:12px 16px; font-size:13px; font-weight:600;">S/ {{ Number(r.total).toFixed(2) }}</td>
                            <td style="padding:12px 16px;">
                                <span :style="{background: estadoBadge(r.estado)+'20', color: estadoBadge(r.estado), padding:'3px 10px', borderRadius:'20px', fontSize:'11px', fontWeight:'600'}">
                                    {{ r.estado.toUpperCase() }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;">
                                <div style="display:flex; flex-wrap:wrap; gap:6px; min-width:160px;">
                                    <button v-if="r.estado === 'reservado'" @click="confirmarCheckin(r)"
                                        style="padding:6px 12px; background:#3B82F6; color:white; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap;">
                                        ✅ Confirmar check-in
                                    </button>
                                    <button v-if="r.estado === 'reservado'" @click="cancelarReserva(r)"
                                        style="padding:6px 12px; background:#6B7280; color:white; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap;">
                                        ✕ Cancelar reserva
                                    </button>
                                    <button v-if="r.estado === 'checkin'" @click="showPagoParcial = r; pagoParcialForm.monto = r.total - r.monto_pagado"
                                        style="padding:6px 12px; background:#F59E0B; color:white; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap;">
                                        💰 Pago
                                    </button>
                                    <button v-if="r.estado === 'checkin'" @click="showExtender = r; extenderForm.nueva_fecha_checkout = r.fecha_checkout_previsto?.slice(0,10)"
                                        style="padding:6px 12px; background:#8B5CF6; color:white; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap;">
                                        📅 Extender
                                    </button>
                                    <button v-if="r.estado === 'checkin'" @click="showCambiarHab = r; cambiarHabForm.nueva_habitacion_id = ''"
                                        style="padding:6px 12px; background:#06B6D4; color:white; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap;">
                                        🔄 Cambiar hab.
                                    </button>
                                    <button v-if="r.estado === 'checkin'" @click="imprimirTicket(r)"
                                        style="padding:6px 12px; background:#0F766E; color:white; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap;">
                                        🖨️ Ticket
                                    </button>
                                    <button v-if="r.estado === 'checkin'" @click="showCheckout = r; pagoForm.monto = r.total - r.monto_pagado; pagoForm.cliente_documento = r.huesped?.numero_documento; pagoForm.cliente_nombre = r.huesped?.nombre_completo"
                                        style="padding:6px 12px; background:#DC2626; color:white; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap;">
                                        🚪 Check-out
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal Check-in -->
            <div v-if="showCheckin" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
                <div style="background:#fff; border-radius:16px; padding:28px; width:560px; max-height:90vh; overflow-y:auto;">
                    <h2 style="font-size:18px; font-weight:700; margin:0 0 20px;">🏁 Nuevo Check-in</h2>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                        <div style="grid-column:1/-1;">
                            <label style="font-size:12px; font-weight:600; color:#374151;">Habitación</label>
                            <select v-model="form.habitacion_id" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;">
                                <option value="">Seleccionar habitación</option>
                                <option v-for="h in habitacionesDisponibles" :key="h.id" :value="h.id">
                                    Hab. {{ h.numero }} — {{ h.tipo?.nombre }} (S/ {{ h.tipo?.precio_noche }}/noche)
                                </option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">Tipo Documento</label>
                            <select v-model="form.tipo_documento" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;">
                                <option value="1">DNI</option>
                                <option value="4">Carné Extranjería</option>
                                <option value="7">Pasaporte</option>
                                <option value="6">RUC</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">N° Documento</label>
                            <div style="position:relative;">
                                <input v-model="form.numero_documento" @input="onDocInput" maxlength="11"
                                    style="width:100%; padding:8px 36px 8px 8px; border:2px solid #E2E8F0; border-radius:8px; margin-top:4px; outline:none;"
                                    :style="{ borderColor: docEncontrado ? '#14B8A6' : docError ? '#EF4444' : '#E2E8F0' }"
                                    placeholder="DNI 8 dígitos o RUC 11 dígitos" />
                                <span style="position:absolute; right:8px; top:50%; transform:translateY(-50%);">
                                    <span v-if="buscandoDoc">⏳</span>
                                    <span v-else-if="docEncontrado">✅</span>
                                    <span v-else-if="docError">❌</span>
                                    <span v-else>🔍</span>
                                </span>
                            </div>
                            <p v-if="docError" style="font-size:11px; color:#EF4444; margin:2px 0 0;">{{ docError }}</p>
                        </div>
                        <div style="grid-column:1/-1;">
                            <label style="font-size:12px; font-weight:600; color:#374151;">Nombre Completo</label>
                            <input v-model="form.nombre_completo" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" placeholder="Nombre del huésped" />
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">Teléfono</label>
                            <input v-model="form.telefono" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" placeholder="999999999" />
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">Nacionalidad</label>
                            <input v-model="form.nacionalidad" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" />
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">Fecha Check-in</label>
                            <input type="datetime-local" v-model="form.fecha_checkin" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" />
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">Fecha Check-out</label>
                            <input type="datetime-local" v-model="form.fecha_checkout" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" />
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">N° Huéspedes</label>
                            <input type="number" v-model="form.num_huespedes" min="1" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" />
                        </div>
                        <div style="grid-column:1/-1;">
                            <label style="font-size:12px; font-weight:600; color:#374151;">Observaciones</label>
                            <textarea v-model="form.observaciones" rows="2" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;"></textarea>
                        </div>
                    </div>
                    <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
                        <button @click="showCheckin = false" style="background:#F1F5F9; color:#374151; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600;">Cancelar</button>
                        <button @click="hacerCheckin" :disabled="cargando" style="background:#3B82F6; color:#fff; border:none; padding:10px 24px; border-radius:8px; cursor:pointer; font-weight:600;">
                            {{ cargando ? '⏳...' : '✅ Confirmar Check-in' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Check-out -->
            <div v-if="showCheckout" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
                <div style="background:#fff; border-radius:16px; padding:28px; width:420px;">
                    <h2 style="font-size:18px; font-weight:700; margin:0 0 16px;">🚪 Check-out — {{ showCheckout.codigo }}</h2>
                    <div style="background:#F8FAFC; border-radius:8px; padding:14px; margin-bottom:16px;">
                        <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:6px;">
                            <span style="color:#64748B;">Total estadía:</span>
                            <span style="font-weight:600;">S/ {{ Number(showCheckout.total).toFixed(2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:6px;">
                            <span style="color:#64748B;">Pagado:</span>
                            <span style="color:#16A34A; font-weight:600;">S/ {{ Number(showCheckout.monto_pagado).toFixed(2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:14px; border-top:1px solid #E2E8F0; padding-top:8px; margin-top:8px;">
                            <span style="font-weight:600;">Saldo pendiente:</span>
                            <span style="font-weight:700; color:#DC2626;">S/ {{ Number(showCheckout.total - showCheckout.monto_pagado).toFixed(2) }}</span>
                        </div>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="font-size:12px; font-weight:600; color:#374151;">Monto a cobrar</label>
                        <input type="number" v-model="pagoForm.monto" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" />
                    </div>
                    <div style="margin-bottom:16px;">
                        <label style="font-size:12px; font-weight:600; color:#374151;">Método de pago</label>
                        <select v-model="pagoForm.metodo_pago" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;">
                            <option value="efectivo">Efectivo</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>
                    <!-- Comprobante -->
                    <div style="margin-bottom:14px;">
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">📄 Comprobante</label>
                        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:6px;">
                            <button @click="pagoForm.tipo_comprobante='boleta'"
                                :style="{padding:'8px', borderRadius:'8px', border: pagoForm.tipo_comprobante==='boleta' ? '2px solid #14B8A6' : '2px solid #E2E8F0', background: pagoForm.tipo_comprobante==='boleta' ? '#F0FDFA' : 'white', cursor:'pointer', fontSize:'12px', fontWeight:'700', color: pagoForm.tipo_comprobante==='boleta' ? '#0F766E' : '#64748B'}">
                                🧾 Boleta
                            </button>
                            <button @click="pagoForm.tipo_comprobante='factura'"
                                :style="{padding:'8px', borderRadius:'8px', border: pagoForm.tipo_comprobante==='factura' ? '2px solid #3B82F6' : '2px solid #E2E8F0', background: pagoForm.tipo_comprobante==='factura' ? '#EFF6FF' : 'white', cursor:'pointer', fontSize:'12px', fontWeight:'700', color: pagoForm.tipo_comprobante==='factura' ? '#1D4ED8' : '#64748B'}">
                                🏢 Factura
                            </button>
                            <button @click="pagoForm.tipo_comprobante='ninguno'"
                                :style="{padding:'8px', borderRadius:'8px', border: pagoForm.tipo_comprobante==='ninguno' ? '2px solid #94A3B8' : '2px solid #E2E8F0', background: pagoForm.tipo_comprobante==='ninguno' ? '#F8FAFC' : 'white', cursor:'pointer', fontSize:'12px', fontWeight:'700', color: pagoForm.tipo_comprobante==='ninguno' ? '#475569' : '#64748B'}">
                                🚫 Sin boleta
                            </button>
                        </div>
                        <!-- Datos cliente -->
                        <div v-if="pagoForm.tipo_comprobante !== 'ninguno'" style="margin-top:8px; display:grid; gap:8px;">
                            <div style="position:relative;">
                                <input v-model="pagoForm.cliente_documento" @input="onDocCheckoutInput" maxlength="11"
                                    :placeholder="pagoForm.tipo_comprobante==='factura' ? 'RUC (11 dígitos)' : 'DNI (8 dígitos)'"
                                    style="width:100%; padding:8px 36px 8px 8px; border:2px solid #E2E8F0; border-radius:8px; font-size:12px; box-sizing:border-box; outline:none;"
                                    :style="{ borderColor: docCheckoutEncontrado ? '#14B8A6' : docCheckoutError ? '#EF4444' : '#E2E8F0' }"/>
                                <span style="position:absolute; right:8px; top:50%; transform:translateY(-50%); font-size:13px;">
                                    <span v-if="buscandoDocCheckout">⏳</span>
                                    <span v-else-if="docCheckoutEncontrado">✅</span>
                                    <span v-else-if="docCheckoutError">❌</span>
                                    <span v-else>🔍</span>
                                </span>
                            </div>
                            <input v-model="pagoForm.cliente_nombre"
                                :placeholder="pagoForm.tipo_comprobante==='factura' ? 'Razón social' : 'Nombre completo'"
                                style="width:100%; padding:8px; border:2px solid #E2E8F0; border-radius:8px; font-size:12px; box-sizing:border-box; outline:none;"/>
                            <p v-if="docCheckoutEncontrado" style="margin:0; font-size:11px; color:#0F766E;">✅ Datos obtenidos automáticamente</p>
                        </div>
                    </div>

                    <div style="display:flex; gap:10px; justify-content:flex-end;">
                        <button @click="showCheckout = null" style="background:#F1F5F9; color:#374151; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600;">Cancelar</button>
                        <button @click="hacerCheckout(showCheckout)" :disabled="cargando" style="background:#DC2626; color:#fff; border:none; padding:10px 24px; border-radius:8px; cursor:pointer; font-weight:600;">
                            {{ cargando ? '⏳...' : '🚪 Confirmar Check-out' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

            <!-- Modal Ticket Checkout -->
            <div v-if="ticket" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:2000;">
                <div style="background:#fff; border-radius:16px; padding:32px; width:420px; font-family:monospace;">
                    <div style="text-align:center; margin-bottom:16px;">
                        <div style="font-size:20px; font-weight:700;">🏨 NEXPOS HOTEL</div>
                        <div style="font-size:12px; color:#64748B;">COMPROBANTE DE ESTADÍA</div>
                        <div style="border-top:2px dashed #E2E8F0; margin:12px 0;"></div>
                    </div>
                    <div style="font-size:13px; display:grid; gap:6px;">
                        <div style="display:flex; justify-content:space-between;"><span style="color:#64748B;">Código:</span><b>{{ ticket.codigo }}</b></div>
                        <div style="display:flex; justify-content:space-between;"><span style="color:#64748B;">Huésped:</span><b>{{ ticket.huesped }}</b></div>
                        <div style="display:flex; justify-content:space-between;"><span style="color:#64748B;">Documento:</span><b>{{ ticket.documento }}</b></div>
                        <div style="display:flex; justify-content:space-between;"><span style="color:#64748B;">Habitación:</span><b>{{ ticket.habitacion }}</b></div>
                        <div style="border-top:1px dashed #E2E8F0; margin:8px 0;"></div>
                        <div style="display:flex; justify-content:space-between;"><span style="color:#64748B;">Check-in:</span><b>{{ new Date(ticket.fecha_checkin).toLocaleString('es-PE') }}</b></div>
                        <div style="display:flex; justify-content:space-between;"><span style="color:#64748B;">Check-out:</span><b>{{ new Date(ticket.fecha_checkout).toLocaleString('es-PE') }}</b></div>
                        <div style="display:flex; justify-content:space-between;"><span style="color:#64748B;">Noches:</span><b>{{ ticket.noches }}</b></div>
                        <div style="display:flex; justify-content:space-between;"><span style="color:#64748B;">Precio/noche:</span><b>S/ {{ Number(ticket.precio_noche).toFixed(2) }}</b></div>
                        <div style="border-top:1px dashed #E2E8F0; margin:8px 0;"></div>
                        <div style="display:flex; justify-content:space-between; font-size:16px;"><span style="font-weight:700;">TOTAL:</span><b style="color:#16A34A;">S/ {{ Number(ticket.total).toFixed(2) }}</b></div>
                        <div style="display:flex; justify-content:space-between;"><span style="color:#64748B;">Método pago:</span><b>{{ ticket.metodo_pago?.toUpperCase() }}</b></div>
                        <div style="display:flex; justify-content:space-between;"><span style="color:#64748B;">Estado:</span><b :style="{color: ticket.estado_pago==='pagado' ? '#16A34A' : '#DC2626'}">{{ ticket.estado_pago?.toUpperCase() }}</b></div>
                        <div style="border-top:2px dashed #E2E8F0; margin:12px 0;"></div>
                        <div style="text-align:center; font-size:11px; color:#64748B;">¡Gracias por su visita!</div>
                    </div>
                    <div style="display:flex; gap:10px; margin-top:20px;">
                        <button @click="window.print()" style="flex:1; background:#3B82F6; color:#fff; border:none; padding:10px; border-radius:8px; font-weight:600; cursor:pointer;">🖨️ Imprimir</button>
                        <button @click="ticket=null" style="flex:1; background:#F1F5F9; color:#374151; border:none; padding:10px; border-radius:8px; font-weight:600; cursor:pointer;">✕ Cerrar</button>
                    </div>
                </div>
            </div>


            <!-- Modal Agregar Cargo -->
            <div v-if="showCargo" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
                <div style="background:#fff; border-radius:16px; padding:28px; width:520px; max-height:90vh; overflow-y:auto;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                        <h2 style="font-size:18px; font-weight:700; margin:0;">🛒 Agregar cargo a habitación</h2>
                        <button @click="showCargo=null" style="background:none; border:none; cursor:pointer; font-size:20px; color:#94A3B8;">✕</button>
                    </div>

                    <!-- Seleccionar producto -->
                    <div style="display:grid; gap:12px; margin-bottom:20px;">
                        <div>
                            <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">Producto</label>
                            <select v-model="formCargo.producto_id" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px;">
                                <option value="">Seleccionar producto...</option>
                                <option v-for="p in productos" :key="p.id" :value="p.id">
                                    {{ p.nombre }} — S/ {{ Number(p.precio).toFixed(2) }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">Cantidad</label>
                            <input type="number" v-model="formCargo.cantidad" min="1"
                                style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; box-sizing:border-box;" />
                        </div>
                        <button @click="agregarCargo"
                            style="background:#7C3AED; color:#fff; border:none; padding:10px; border-radius:8px; font-weight:600; cursor:pointer;">
                            ➕ Agregar cargo
                        </button>
                    </div>

                    <!-- Cargos actuales de esta reserva -->
                    <div style="border-top:1px solid #F1F5F9; padding-top:16px;">
                        <div style="font-size:13px; font-weight:700; margin-bottom:12px; color:#374151;">Cargos actuales</div>
                        <div v-if="cargos.filter(c => c.reserva_id === showCargo.id).length === 0"
                            style="text-align:center; color:#94A3B8; font-size:13px; padding:20px;">
                            No hay cargos aún
                        </div>
                        <div v-for="c in cargos.filter(c => c.reserva_id === showCargo.id)" :key="c.id"
                            style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #F8FAFC;">
                            <div>
                                <div style="font-size:13px; font-weight:600;">{{ c.descripcion }}</div>
                                <div style="font-size:11px; color:#64748B;">{{ c.cantidad }} x S/ {{ Number(c.precio_unitario).toFixed(2) }}</div>
                            </div>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="font-weight:700; color:#374151;">S/ {{ Number(c.subtotal).toFixed(2) }}</span>
                                <button @click="eliminarCargo(c.id)"
                                    style="background:#FEF2F2; color:#DC2626; border:none; padding:4px 10px; border-radius:6px; font-size:12px; cursor:pointer;">🗑️</button>
                            </div>
                        </div>
                        <div v-if="cargos.filter(c => c.reserva_id === showCargo.id).length > 0"
                            style="display:flex; justify-content:space-between; margin-top:10px; font-weight:700; font-size:14px;">
                            <span>Total cargos:</span>
                            <span style="color:#7C3AED;">S/ {{ Number(cargos.filter(c => c.reserva_id === showCargo.id).reduce((a,c) => a + Number(c.subtotal), 0)).toFixed(2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

    
    <!-- ══ MODAL EXTENDER ESTADIA ══ -->
    <div v-if="showExtender" style="position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:white;border-radius:16px;padding:24px;width:100%;max-width:380px;">
            <h3 style="margin:0 0 16px;font-size:16px;font-weight:700;">📅 Extender estadía</h3>
            <p style="font-size:13px;color:#64748B;margin:0 0 12px;">{{ showExtender.huesped?.nombre_completo }} — Hab. {{ showExtender.habitacion?.numero }}</p>
            <label style="font-size:12px;font-weight:600;color:#64748B;">Nueva fecha de checkout</label>
            <input type="date" v-model="extenderForm.nueva_fecha_checkout"
                style="width:100%;padding:10px;border:2px solid #E2E8F0;border-radius:8px;font-size:14px;margin-top:4px;box-sizing:border-box;"/>
            <div style="display:flex;gap:8px;margin-top:16px;">
                <button @click="showExtender=null" style="flex:1;padding:12px;background:#F1F5F9;border:none;border-radius:8px;cursor:pointer;font-weight:600;">Cancelar</button>
                <button @click="extenderEstadia" :disabled="cargando" style="flex:2;padding:12px;background:#8B5CF6;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:700;">
                    {{ cargando ? 'Guardando...' : '✅ Confirmar extensión' }}
                </button>
            </div>
        </div>
    </div>

    <!-- ══ MODAL CAMBIAR HABITACION ══ -->
    <div v-if="showCambiarHab" style="position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:white;border-radius:16px;padding:24px;width:100%;max-width:380px;">
            <h3 style="margin:0 0 16px;font-size:16px;font-weight:700;">🔄 Cambiar habitación</h3>
            <p style="font-size:13px;color:#64748B;margin:0 0 12px;">{{ showCambiarHab.huesped?.nombre_completo }} — Actual: Hab. {{ showCambiarHab.habitacion?.numero }}</p>
            <label style="font-size:12px;font-weight:600;color:#64748B;">Nueva habitación disponible</label>
            <select v-model="cambiarHabForm.nueva_habitacion_id"
                style="width:100%;padding:10px;border:2px solid #E2E8F0;border-radius:8px;font-size:14px;margin-top:4px;box-sizing:border-box;">
                <option value="">Seleccionar habitación...</option>
                <option v-for="h in props.habitacionesDisponibles" :key="h.id" :value="h.id">
                    Hab. {{ h.numero }} — {{ h.tipo?.nombre }} — S/ {{ h.tipo?.precio_noche }}/noche
                </option>
            </select>
            <div style="display:flex;gap:8px;margin-top:16px;">
                <button @click="showCambiarHab=null" style="flex:1;padding:12px;background:#F1F5F9;border:none;border-radius:8px;cursor:pointer;font-weight:600;">Cancelar</button>
                <button @click="cambiarHabitacion" :disabled="cargando || !cambiarHabForm.nueva_habitacion_id"
                    style="flex:2;padding:12px;background:#06B6D4;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:700;">
                    {{ cargando ? 'Cambiando...' : '✅ Confirmar cambio' }}
                </button>
            </div>
        </div>
    </div>

    <!-- ══ MODAL PAGO PARCIAL ══ -->
    <div v-if="showPagoParcial" style="position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;padding:16px;">
        <div style="background:white;border-radius:16px;padding:24px;width:100%;max-width:380px;">
            <h3 style="margin:0 0 4px;font-size:16px;font-weight:700;">💰 Registrar pago</h3>
            <p style="font-size:13px;color:#64748B;margin:0 0 16px;">
                {{ showPagoParcial.huesped?.nombre_completo }} —
                Saldo: <b style="color:#EF4444;">S/ {{ Number(showPagoParcial.total - showPagoParcial.monto_pagado).toFixed(2) }}</b>
            </p>
            <label style="font-size:12px;font-weight:600;color:#64748B;">Monto a pagar</label>
            <input type="number" v-model="pagoParcialForm.monto" step="0.01"
                style="width:100%;padding:10px;border:2px solid #E2E8F0;border-radius:8px;font-size:14px;margin-top:4px;box-sizing:border-box;"/>
            <label style="font-size:12px;font-weight:600;color:#64748B;display:block;margin-top:12px;">Método de pago</label>
            <select v-model="pagoParcialForm.metodo_pago"
                style="width:100%;padding:10px;border:2px solid #E2E8F0;border-radius:8px;font-size:14px;margin-top:4px;box-sizing:border-box;">
                <option value="efectivo">💵 Efectivo</option>
                <option value="yape">📱 Yape</option>
                <option value="plin">📲 Plin</option>
                <option value="tarjeta">💳 Tarjeta</option>
                <option value="transferencia">🏦 Transferencia</option>
            </select>
            <label style="font-size:12px;font-weight:600;color:#64748B;display:block;margin-top:12px;">Referencia (opcional)</label>
            <input type="text" v-model="pagoParcialForm.referencia" placeholder="Nro. operación..."
                style="width:100%;padding:10px;border:2px solid #E2E8F0;border-radius:8px;font-size:14px;margin-top:4px;box-sizing:border-box;"/>
            <div style="display:flex;gap:8px;margin-top:16px;">
                <button @click="showPagoParcial=null" style="flex:1;padding:12px;background:#F1F5F9;border:none;border-radius:8px;cursor:pointer;font-weight:600;">Cancelar</button>
                <button @click="registrarPago" :disabled="cargando || !pagoParcialForm.monto"
                    style="flex:2;padding:12px;background:#F59E0B;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:700;">
                    {{ cargando ? 'Guardando...' : '✅ Registrar pago' }}
                </button>
            </div>
        </div>
    </div>

    <!-- ══ MODAL RESERVA ANTICIPADA ══ -->
    <div v-if="showReservar" style="position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;padding:16px;overflow-y:auto;">
        <div style="background:white;border-radius:16px;padding:24px;width:100%;max-width:460px;margin:auto;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="margin:0;font-size:16px;font-weight:700;">📅 Reserva anticipada</h3>
                <button @click="showReservar=false" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94A3B8;">✕</button>
            </div>
            <div style="display:grid;gap:10px;">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#64748B;">Habitación</label>
                    <select v-model="form.habitacion_id" style="width:100%;padding:10px;border:2px solid #E2E8F0;border-radius:8px;font-size:13px;margin-top:4px;box-sizing:border-box;">
                        <option value="">Seleccionar...</option>
                        <option v-for="h in props.habitacionesDisponibles" :key="h.id" :value="h.id">
                            Hab. {{ h.numero }} — {{ h.tipo?.nombre }} — S/ {{ h.tipo?.precio_noche }}/noche
                        </option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#64748B;">DNI / RUC huésped</label>
                    <div style="position:relative;">
                        <input v-model="form.numero_documento" @input="onDocInput" maxlength="11"
                            style="width:100%;padding:10px 36px 10px 10px;border:2px solid #E2E8F0;border-radius:8px;font-size:13px;margin-top:4px;box-sizing:border-box;"
                            :style="{ borderColor: docEncontrado ? '#14B8A6' : docError ? '#EF4444' : '#E2E8F0' }"
                            placeholder="DNI 8 dígitos o RUC 11 dígitos"/>
                        <span style="position:absolute;right:8px;top:55%;transform:translateY(-50%);">
                            <span v-if="buscandoDoc">⏳</span>
                            <span v-else-if="docEncontrado">✅</span>
                            <span v-else-if="docError">❌</span>
                        </span>
                    </div>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#64748B;">Nombre completo</label>
                    <input v-model="form.nombre_completo" style="width:100%;padding:10px;border:2px solid #E2E8F0;border-radius:8px;font-size:13px;margin-top:4px;box-sizing:border-box;" placeholder="Nombre del huésped"/>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#64748B;">Check-in</label>
                        <input type="date" v-model="form.fecha_checkin" style="width:100%;padding:10px;border:2px solid #E2E8F0;border-radius:8px;font-size:13px;margin-top:4px;box-sizing:border-box;"/>
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#64748B;">Check-out</label>
                        <input type="date" v-model="form.fecha_checkout" style="width:100%;padding:10px;border:2px solid #E2E8F0;border-radius:8px;font-size:13px;margin-top:4px;box-sizing:border-box;"/>
                    </div>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#64748B;">Teléfono</label>
                    <input v-model="form.telefono" style="width:100%;padding:10px;border:2px solid #E2E8F0;border-radius:8px;font-size:13px;margin-top:4px;box-sizing:border-box;" placeholder="Opcional"/>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#64748B;">Adelanto (S/)</label>
                        <input type="number" v-model="form.adelanto" step="0.01" style="width:100%;padding:10px;border:2px solid #E2E8F0;border-radius:8px;font-size:13px;margin-top:4px;box-sizing:border-box;" placeholder="0.00"/>
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#64748B;">Método adelanto</label>
                        <select v-model="form.metodo_adelanto" style="width:100%;padding:10px;border:2px solid #E2E8F0;border-radius:8px;font-size:13px;margin-top:4px;box-sizing:border-box;">
                            <option value="efectivo">💵 Efectivo</option>
                            <option value="yape">📱 Yape</option>
                            <option value="tarjeta">💳 Tarjeta</option>
                            <option value="transferencia">🏦 Transferencia</option>
                        </select>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:8px;margin-top:16px;">
                <button @click="showReservar=false" style="flex:1;padding:12px;background:#F1F5F9;border:none;border-radius:8px;cursor:pointer;font-weight:600;">Cancelar</button>
                <button @click="hacerCheckin(true)" :disabled="cargando"
                    style="flex:2;padding:12px;background:linear-gradient(135deg,#3B82F6,#1D4ED8);color:white;border:none;border-radius:8px;cursor:pointer;font-weight:700;">
                    {{ cargando ? 'Guardando...' : '📅 Confirmar reserva' }}
                </button>
            </div>
        </div>
    </div>

</AppLayout>
</template>
