<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

let pollingInterval = null

onMounted(() => {
    pollingInterval = setInterval(() => {
        router.reload({ only: ['mesa', 'pedidos', 'total'] })
    }, 15000) // cada 15 segundos
})

onUnmounted(() => {
    clearInterval(pollingInterval)
})

const props = defineProps({
    mesa:    Object,
    pedidos: Array,
    total:   Number,
    pagado_acumulado: { type: Number, default: 0 },
    saldo_pendiente:  { type: Number, default: null },
    total_pendiente:   { type: Number, default: null },
    platos_pendientes: { type: Number, default: 0 },
    platos_pagados:    { type: Number, default: 0 },
})

const form = useForm({
    metodo_pago:          'efectivo',
    monto_pagado:         props.total ? Number(props.total).toFixed(2) : '',
    tipo_comprobante:     'ninguno',
    notas:                '',
    partes_total:         1,
    parte_numero:         1,
    cliente_documento:    '',
    cliente_nombre:       '',
    cliente_email:        '',
    cliente_tipo_documento: '1',
})

const mostrarModalCliente = ref(false)
const buscandoDoc   = ref(false)
const docEncontrado = ref(false)
const docError      = ref('')
let docTimer = null

function onTipoComprobante(tipo) {
    form.tipo_comprobante = tipo
    if (tipo === 'boleta' || tipo === 'factura') {
        mostrarModalCliente.value = true
    }
}

function onDocInput() {
    docEncontrado.value = false
    docError.value = ''
    form.cliente_nombre = ''
    const doc = form.cliente_documento.replace(/[^0-9]/g, '')
    form.cliente_documento = doc
    const esDni = doc.length === 8
    const esRuc = doc.length === 11
    form.cliente_tipo_documento = esRuc ? '6' : '1'
    if (!esDni && !esRuc) return
    clearTimeout(docTimer)
    docTimer = setTimeout(async () => {
        buscandoDoc.value = true
        try {
            const res = await fetch(`/api/consulta-documento?documento=${doc}`)
            const data = await res.json()
            if (esDni && data.nombre_completo) {
                form.cliente_nombre = data.nombre_completo
                docEncontrado.value = true
            } else if (esRuc && data.razon_social) {
                form.cliente_nombre = data.razon_social
                docEncontrado.value = true
            } else {
                docError.value = esDni ? 'DNI no encontrado' : 'RUC no encontrado'
            }
        } catch(e) {
            docError.value = 'Error al consultar'
        } finally {
            buscandoDoc.value = false
        }
    }, 600)
}

// Saldo que realmente falta cobrar de la cuenta
const saldoReal = computed(() => {
    if (props.saldo_pendiente !== null && props.saldo_pendiente !== undefined) {
        return Number(props.saldo_pendiente)
    }
    return Number(props.total)
})

// Cuánto debe pagar cada parte (división en partes iguales)
const montoPorParte = computed(() => {
    const n = parseInt(form.partes_total) || 1
    if (n <= 1) return saldoReal.value
    return Math.ceil((Number(props.total) / n) * 100) / 100
})

const objetivoCobro = computed(() => {
    const n = parseInt(form.partes_total) || 1
    return n > 1 ? montoPorParte.value : saldoReal.value
})

const vuelto = computed(() => {
    const monto = parseFloat(form.monto_pagado) || 0
    // Solo hay vuelto si es el ultimo pago que salda la cuenta
    const diff = monto - saldoReal.value
    return (diff > 0 && (parseInt(form.partes_total) || 1) <= 1) ? diff : 0
})

const faltante = computed(() => {
    const monto = parseFloat(form.monto_pagado) || 0
    const diff  = objetivoCobro.value - monto
    return diff > 0 ? diff : 0
})

const metodos = [
    { key: 'efectivo', label: 'Efectivo', icon: '💵' },
    { key: 'yape',     label: 'Yape',     icon: '📱' },
    { key: 'plin',     label: 'Plin',     icon: '📲' },
    { key: 'tarjeta',  label: 'Tarjeta',  icon: '💳' },
]

const parteActual = computed(() => {
    // Cuantas partes ya se pagaron + 1
    const n = parseInt(form.partes_total) || 1
    if (n <= 1) return 1
    const pagadas = Number(props.pagado_acumulado) > 0
        ? Math.round(Number(props.pagado_acumulado) / montoPorParte.value)
        : 0
    return Math.min(pagadas + 1, n)
})

// === MODO DE COBRO: 'todo' | 'partes' | 'platos' ===
const modoCobro = ref('todo')

// Hora para impresion
const horaImpresion = new Date().toLocaleString('es-PE', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' })

// Modo de impresion: 'precuenta' (controla que ticket se ve al imprimir)
function imprimirPrecuenta() {
    document.body.classList.add('modo-precuenta')
    window.print()
    setTimeout(() => document.body.classList.remove('modo-precuenta'), 500)
}

// Lista plana de todos los platos de la mesa (de todos los pedidos)
const platos = computed(() => {
    const out = []
    for (const ped of (props.pedidos || [])) {
        for (const d of (ped.detalles || [])) {
            if (d.anulado) continue  // los anulados no se cobran
            out.push({
                id: d.id,
                nombre: d.nombre_producto,
                cantidad: d.cantidad,
                subtotal: Number(d.subtotal),
                pagado: !!d.pagado,
            })
        }
    }
    return out
})

// IDs de platos seleccionados para cobrar ahora
const seleccionados = ref([])

function togglePlato(id) {
    const i = seleccionados.value.indexOf(id)
    if (i === -1) seleccionados.value.push(id)
    else seleccionados.value.splice(i, 1)
    // Autocompletar el monto con el total de lo seleccionado
    form.monto_pagado = totalSeleccionado.value.toFixed(2)
}

// Suma de los platos seleccionados
const totalSeleccionado = computed(() => {
    return platos.value
        .filter(p => seleccionados.value.includes(p.id))
        .reduce((s, p) => s + p.subtotal, 0)
})

// Platos que aun faltan pagar (calculado local, respaldado por backend)
const platosPendientesLista = computed(() => platos.value.filter(p => !p.pagado))
const platosFaltan = computed(() => Number(props.platos_pendientes) || platosPendientesLista.value.length)

function cobrarPlatos() {
    if (seleccionados.value.length === 0) {
        alert('Selecciona al menos un plato para cobrar.')
        return
    }
    // El monto se cobra exacto por los platos seleccionados
    form.monto_pagado = totalSeleccionado.value.toFixed(2)
    form.transform(data => ({
        ...data,
        detalle_ids: seleccionados.value,
    })).post(`/caja-restaurante/${props.mesa.id}/platos`, {
        onSuccess: () => { seleccionados.value = [] },
    })
}

function cobrar() {
    const n = parseInt(form.partes_total) || 1
    const objetivo = objetivoCobro.value
    const monto = parseFloat(form.monto_pagado) || 0

    if (n > 1) {
        // Cobro por partes: el monto debe cubrir al menos la parte
        if (monto < objetivo - 0.01) {
            if (!confirm(`El monto es menor a la parte (S/ ${objetivo.toFixed(2)}). ¿Continuar?`)) return
        }
        form.parte_numero = parteActual.value
    } else {
        if (!form.monto_pagado || monto < saldoReal.value - 0.01) {
            if (!confirm('El monto es menor al total. ¿Desea continuar de todas formas?')) return
        }
        form.parte_numero = 1
    }
    form.post(`/caja-restaurante/${props.mesa.id}`)
}

</script>

<template>
    <AppLayout :title="`💳 Cobrar Mesa ${mesa.numero}`">
        <div style="max-width:900px; margin:0 auto;">

            <!-- Header -->
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
                <a href="/mesas" style="background:#F1F5F9; border:none; border-radius:12px; padding:12px 16px; font-size:16px; cursor:pointer; text-decoration:none; color:#475569; font-weight:600;">
                    ← Volver
                </a>
                <div>
                    <h1 style="font-size:28px; font-weight:800; color:#1E293B; margin:0;">💳 Cobrar Mesa {{ mesa.numero }}</h1>
                    <p style="font-size:15px; color:#94A3B8; margin:4px 0 0;">{{ pedidos.length }} ronda{{ pedidos.length !== 1 ? 's' : '' }} · {{ mesa.zona }}</p>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:20px;">

                <!-- ══ DETALLE PEDIDOS ══ -->
                <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                    <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 20px;">🧾 Detalle del consumo</p>

                    <div v-for="pedido in pedidos" :key="pedido.id" style="margin-bottom:20px;">
                        <p style="font-size:14px; font-weight:700; color:#94A3B8; text-transform:uppercase; letter-spacing:1px; margin:0 0 10px;">
                            Ronda {{ pedido.numero_ronda }}
                        </p>
                        <div v-for="det in pedido.detalles" :key="det.id"
                            style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #F1F5F9;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="background:#F1F5F9; border-radius:8px; padding:4px 10px; font-size:15px; font-weight:700; color:#1E293B;">
                                    x{{ det.cantidad }}
                                </span>
                                <span style="font-size:16px; color:#1E293B; font-weight:500;">{{ det.nombre_producto }}</span>
                            </div>
                            <span style="font-size:16px; font-weight:700; color:#1E293B;">S/ {{ Number(det.subtotal).toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 0; border-top:3px solid #E2E8F0; margin-top:8px;">
                        <span style="font-size:22px; font-weight:800; color:#1E293B;">TOTAL</span>
                        <span style="font-size:32px; font-weight:800; color:#14B8A6;">S/ {{ Number(total).toFixed(2) }}</span>
                    </div>
                    <button @click="imprimirPrecuenta" style="width:100%; padding:12px; background:#F1F5F9; color:#0F766E; border:1px solid #14B8A6; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; margin-top:8px;">🖨️ Imprimir pre-cuenta</button>
                </div>

                <!-- ══ COBRO ══ -->
                <div style="display:flex; flex-direction:column; gap:16px;">

                    <!-- Modo de cobro -->
                    <div v-if="$page.props.auth.user.rol !== 'mozo'" style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                        <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 16px;">🧾 ¿Cómo se cobra?</p>
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(90px, 1fr)); gap:8px;">
                            <button @click="modoCobro='todo'; form.partes_total=1; seleccionados=[]; form.monto_pagado=saldoReal.toFixed(2)"
                                :style="{padding:'14px 8px', borderRadius:'12px', border:'none', cursor:'pointer', fontSize:'13px', fontWeight:'800', background: modoCobro==='todo' ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : '#F1F5F9', color: modoCobro==='todo' ? 'white' : '#475569'}">
                                💳 Pagar todo
                            </button>
                            <button @click="modoCobro='partes'; seleccionados=[]; form.partes_total=2; form.monto_pagado=montoPorParte.toFixed(2)"
                                :style="{padding:'14px 8px', borderRadius:'12px', border:'none', cursor:'pointer', fontSize:'13px', fontWeight:'800', background: modoCobro==='partes' ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : '#F1F5F9', color: modoCobro==='partes' ? 'white' : '#475569'}">
                                🧮 Partes iguales
                            </button>
                            <button @click="modoCobro='platos'; form.partes_total=1; form.monto_pagado=''"
                                :style="{padding:'14px 8px', borderRadius:'12px', border:'none', cursor:'pointer', fontSize:'13px', fontWeight:'800', background: modoCobro==='platos' ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : '#F1F5F9', color: modoCobro==='platos' ? 'white' : '#475569'}">
                                🍽️ Por platos
                            </button>
                        </div>
                    </div>

                    <!-- Método de pago -->
                    <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                        <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 16px;">💳 Método de pago</p>
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(120px, 1fr)); gap:10px;">
                            <button
                                v-for="m in metodos"
                                :key="m.key"
                                @click="form.metodo_pago = m.key"
                                :style="{
                                    padding: '16px',
                                    borderRadius: '14px',
                                    border: 'none',
                                    cursor: 'pointer',
                                    fontSize: '16px',
                                    fontWeight: '700',
                                    transition: 'all 0.15s',
                                    background: form.metodo_pago === m.key ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : '#F1F5F9',
                                    color: form.metodo_pago === m.key ? 'white' : '#475569',
                                    boxShadow: form.metodo_pago === m.key ? '0 4px 15px rgba(20,184,166,0.3)' : 'none',
                                    transform: form.metodo_pago === m.key ? 'scale(1.03)' : 'scale(1)',
                                }"
                            >
                                {{ m.icon }} {{ m.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Dividir cuenta -->
                    <div v-if="$page.props.auth.user.rol !== 'mozo' && modoCobro==='partes'" style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                        <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 16px;">🧮 Dividir cuenta</p>
                        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                            <button
                                v-for="n in [1,2,3,4,5,6]"
                                :key="n"
                                @click="form.partes_total = n; form.monto_pagado = n > 1 ? montoPorParte.toFixed(2) : ''"
                                :style="{
                                    padding: '12px 0', width:'46px', borderRadius:'12px', border:'none', cursor:'pointer',
                                    fontSize:'16px', fontWeight:'800',
                                    background: (parseInt(form.partes_total)||1) === n ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : '#F1F5F9',
                                    color: (parseInt(form.partes_total)||1) === n ? 'white' : '#475569',
                                }"
                            >{{ n }}</button>
                        </div>
                        <div v-if="(parseInt(form.partes_total)||1) > 1" style="margin-top:14px; background:#F0FDFA; border:2px solid #CCFBF1; border-radius:12px; padding:14px;">
                            <p style="margin:0; font-size:14px; color:#0F766E; font-weight:600;">
                                Cada parte paga: <strong style="font-size:18px;">S/ {{ montoPorParte.toFixed(2) }}</strong>
                            </p>
                            <p style="margin:6px 0 0; font-size:13px; color:#0F766E;">
                                Cobrando parte <strong>{{ parteActual }}</strong> de <strong>{{ form.partes_total }}</strong>
                            </p>
                        </div>
                        <div v-if="Number(pagado_acumulado) > 0" style="margin-top:12px; background:#FFFBEB; border:2px solid #FEF3C7; border-radius:12px; padding:14px;">
                            <p style="margin:0; font-size:13px; color:#92400E; font-weight:600;">
                                Ya pagado: S/ {{ Number(pagado_acumulado).toFixed(2) }} · Falta: S/ {{ Number(saldo_pendiente).toFixed(2) }}
                            </p>
                        </div>
                    </div>

                    <!-- Lista de platos (modo por platos) -->
                    <div v-if="$page.props.auth.user.rol !== 'mozo' && modoCobro==='platos'" style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                        <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 16px;">🍽️ Selecciona los platos a cobrar</p>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <div v-for="p in platos.filter(x => !x.pagado)" :key="p.id"
                                @click="!p.pagado && togglePlato(p.id)"
                                :style="{
                                    display:'flex', alignItems:'center', justifyContent:'space-between',
                                    padding:'14px', borderRadius:'12px',
                                    border: p.pagado ? '2px solid #DCFCE7' : (seleccionados.includes(p.id) ? '2px solid #14B8A6' : '2px solid #E2E8F0'),
                                    background: p.pagado ? '#F0FDF4' : (seleccionados.includes(p.id) ? '#F0FDFA' : 'white'),
                                    cursor: p.pagado ? 'default' : 'pointer',
                                    opacity: p.pagado ? 0.6 : 1,
                                }">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <span style="font-size:20px;">{{ p.pagado ? '✅' : (seleccionados.includes(p.id) ? '☑️' : '⬜') }}</span>
                                    <div>
                                        <p style="margin:0; font-size:15px; font-weight:700; color:#1E293B;">{{ p.cantidad }}x {{ p.nombre }}</p>
                                        <p v-if="p.pagado" style="margin:2px 0 0; font-size:12px; color:#166534; font-weight:600;">Ya pagado</p>
                                    </div>
                                </div>
                                <span style="font-size:15px; font-weight:800; color:#0F766E;">S/ {{ p.subtotal.toFixed(2) }}</span>
                            </div>
                        </div>
                        <div v-if="seleccionados.length > 0" style="margin-top:16px; background:#F0FDFA; border:2px solid #CCFBF1; border-radius:12px; padding:16px; text-align:center;">
                            <p style="margin:0; font-size:14px; color:#0F766E; font-weight:600;">Total seleccionado</p>
                            <p style="margin:4px 0 0; font-size:30px; font-weight:800; color:#0F766E;">S/ {{ totalSeleccionado.toFixed(2) }}</p>
                        </div>
                        <div v-if="platosFaltan > 0 && platos_pagados > 0" style="margin-top:14px; background:#FFFBEB; border:2px solid #FEF3C7; border-radius:12px; padding:14px; text-align:center;">
                            <p style="margin:0; font-size:14px; color:#92400E; font-weight:700;">Faltan {{ platosFaltan }} plato(s) por cobrar</p>
                            <p style="margin:4px 0 0; font-size:13px; color:#92400E;">Pendiente: S/ {{ Number(total_pendiente).toFixed(2) }}</p>
                        </div>
                        <p v-if="platosFaltan === 0" style="margin-top:14px; text-align:center; color:#166534; font-weight:700;">Todos los platos estan pagados 🎉</p>
                    </div>

                    <!-- Monto y vuelto -->
                    <div v-if="$page.props.auth.user.rol !== 'mozo'" style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                        <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 16px;">💵 Monto recibido</p>

                        <input
                            v-model="form.monto_pagado"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            style="width:100%; padding:18px; border:3px solid #E2E8F0; border-radius:14px; font-size:28px; font-weight:800; outline:none; box-sizing:border-box; text-align:center; color:#1E293B;"
                            @focus="$event.target.style.borderColor='#14B8A6'"
                            @blur="$event.target.style.borderColor='#E2E8F0'"
                        />

                        <!-- Vuelto -->
                        <div v-if="form.monto_pagado" style="margin-top:16px;">
                            <div v-if="vuelto > 0"
                                style="background:#F0FDF4; border:2px solid #DCFCE7; border-radius:14px; padding:16px; text-align:center;">
                                <p style="font-size:14px; color:#166534; font-weight:600; margin:0 0 4px;">💚 Vuelto a dar</p>
                                <p style="font-size:36px; font-weight:800; color:#166534; margin:0;">S/ {{ vuelto.toFixed(2) }}</p>
                            </div>
                            <div v-else-if="faltante > 0"
                                style="background:#FEF2F2; border:2px solid #FECACA; border-radius:14px; padding:16px; text-align:center;">
                                <p style="font-size:14px; color:#991B1B; font-weight:600; margin:0 0 4px;">❌ Falta</p>
                                <p style="font-size:36px; font-weight:800; color:#991B1B; margin:0;">S/ {{ faltante.toFixed(2) }}</p>
                            </div>
                            <div v-else
                                style="background:#F0FDF4; border:2px solid #DCFCE7; border-radius:14px; padding:16px; text-align:center;">
                                <p style="font-size:20px; font-weight:800; color:#166534; margin:0;">✅ Monto exacto</p>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div style="margin-top:16px;">
                            <input
                                v-model="form.notas"
                                type="text"
                                placeholder="📝 Notas opcionales..."
                                style="width:100%; padding:14px; border:2px solid #E2E8F0; border-radius:12px; font-size:15px; outline:none; box-sizing:border-box;"
                            />
                        </div>
                    </div>

                    <!-- Botón cobrar -->
                    <button
                        @click="modoCobro==='platos' ? cobrarPlatos() : cobrar()"
                        v-if="$page.props.auth.user.rol !== 'mozo'"
                        :disabled="form.processing || (modoCobro==='platos' ? seleccionados.length===0 : !form.monto_pagado)"
                        :style="{
                            width: '100%',
                            padding: '22px',
                            background: (modoCobro==='platos' ? seleccionados.length>0 : form.monto_pagado) ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : '#E2E8F0',
                            color: (modoCobro==='platos' ? seleccionados.length>0 : form.monto_pagado) ? 'white' : '#94A3B8',
                            border: 'none',
                            borderRadius: '16px',
                            fontSize: '22px',
                            fontWeight: '800',
                            cursor: (modoCobro==='platos' ? seleccionados.length>0 : form.monto_pagado) ? 'pointer' : 'not-allowed',
                            boxShadow: (modoCobro==='platos' ? seleccionados.length>0 : form.monto_pagado) ? '0 6px 20px rgba(20,184,166,0.4)' : 'none',
                        }"
                    >
                        <template v-if="form.processing">⏳ Procesando...</template>
                        <template v-else-if="modoCobro==='platos'">✅ Cobrar platos S/ {{ totalSeleccionado.toFixed(2) }}</template>
                        <template v-else-if="modoCobro==='partes'">✅ Cobrar parte S/ {{ montoPorParte.toFixed(2) }}</template>
                        <template v-else>✅ Cobrar S/ {{ Number(total).toFixed(2) }}</template>
                    </button>

                    <!-- Tipo comprobante -->
                    <div style="margin-top:16px;">
                        <p style="font-size:14px; font-weight:600; color:#64748B; margin:0 0 10px;">📄 Tipo de comprobante</p>
                        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px;">
                            <button @click="onTipoComprobante('boleta')"
                                :style="{padding:'10px', borderRadius:'10px', border: form.tipo_comprobante==='boleta' ? '2px solid #14B8A6' : '2px solid #E2E8F0', background: form.tipo_comprobante==='boleta' ? '#F0FDFA' : 'white', cursor:'pointer', fontSize:'13px', fontWeight:'700', color: form.tipo_comprobante==='boleta' ? '#0F766E' : '#64748B'}">
                                🧾 Boleta
                            </button>
                            <button @click="onTipoComprobante('factura')"
                                :style="{padding:'10px', borderRadius:'10px', border: form.tipo_comprobante==='factura' ? '2px solid #3B82F6' : '2px solid #E2E8F0', background: form.tipo_comprobante==='factura' ? '#EFF6FF' : 'white', cursor:'pointer', fontSize:'13px', fontWeight:'700', color: form.tipo_comprobante==='factura' ? '#1D4ED8' : '#64748B'}">
                                🏢 Factura
                            </button>
                            <button @click="form.tipo_comprobante='ninguno'; mostrarModalCliente=false"
                                :style="{padding:'10px', borderRadius:'10px', border: form.tipo_comprobante==='ninguno' ? '2px solid #94A3B8' : '2px solid #E2E8F0', background: form.tipo_comprobante==='ninguno' ? '#F8FAFC' : 'white', cursor:'pointer', fontSize:'13px', fontWeight:'700', color: form.tipo_comprobante==='ninguno' ? '#475569' : '#64748B'}">
                                🚫 Sin boleta
                            </button>
                        </div>
                        <!-- Resumen cliente seleccionado -->
                        <div v-if="form.cliente_nombre" style="margin-top:8px; padding:8px 12px; background:#F0FDFA; border-radius:8px; border:1px solid #CCFBF1; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <p style="margin:0; font-size:13px; font-weight:600; color:#0F766E;">{{ form.cliente_nombre }}</p>
                                <p style="margin:0; font-size:11px; color:#64748B;">{{ form.cliente_tipo_documento === '6' ? 'RUC' : 'DNI' }}: {{ form.cliente_documento }}</p>
                            </div>
                            <button @click="mostrarModalCliente=true" style="background:none; border:none; color:#14B8A6; cursor:pointer; font-size:12px; font-weight:600;">✏️ Editar</button>
                        </div>
                    </div>

                    <!-- MODAL DATOS CLIENTE -->
                    <div v-if="mostrarModalCliente" style="position:fixed; inset:0; z-index:1000; display:flex; align-items:center; justify-content:center; padding:16px; background:rgba(0,0,0,0.5);">
                        <div style="background:white; border-radius:20px; padding:24px; width:100%; max-width:420px; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                                <h3 style="margin:0; font-size:18px; font-weight:700; color:#1E293B;">
                                    {{ form.tipo_comprobante === 'factura' ? '🏢 Datos para Factura' : '🧾 Datos para Boleta' }}
                                </h3>
                                <button @click="mostrarModalCliente=false" style="background:none; border:none; font-size:20px; cursor:pointer; color:#94A3B8;">✕</button>
                            </div>

                            <!-- DNI/RUC -->
                            <div style="margin-bottom:14px;">
                                <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:6px;">
                                    {{ form.tipo_comprobante === 'factura' ? 'RUC (11 dígitos)' : 'DNI (8 dígitos)' }}
                                </label>
                                <div style="position:relative;">
                                    <input v-model="form.cliente_documento" type="text"
                                        :maxlength="form.tipo_comprobante === 'factura' ? 11 : 8"
                                        :placeholder="form.tipo_comprobante === 'factura' ? 'Ej: 20123456789' : 'Ej: 12345678'"
                                        @input="onDocInput"
                                        style="width:100%; padding:12px 40px 12px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box;"
                                        :style="{ borderColor: docEncontrado ? '#14B8A6' : docError ? '#EF4444' : '#E2E8F0' }"/>
                                    <span style="position:absolute; right:12px; top:50%; transform:translateY(-50%);">
                                        <span v-if="buscandoDoc">⏳</span>
                                        <span v-else-if="docEncontrado">✅</span>
                                        <span v-else-if="docError">❌</span>
                                        <span v-else>🔍</span>
                                    </span>
                                </div>
                                <p v-if="docError" style="margin:4px 0 0; font-size:12px; color:#EF4444;">{{ docError }}</p>
                            </div>

                            <!-- Nombre -->
                            <div style="margin-bottom:14px;">
                                <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:6px;">
                                    {{ form.tipo_comprobante === 'factura' ? 'Razón Social' : 'Nombre completo' }}
                                </label>
                                <input v-model="form.cliente_nombre" type="text"
                                    :placeholder="form.tipo_comprobante === 'factura' ? 'Ej: Empresa SAC' : 'Ej: Juan Pérez'"
                                    style="width:100%; padding:12px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box;"/>
                                <p v-if="docEncontrado" style="margin:4px 0 0; font-size:11px; color:#0F766E;">✅ Obtenido automáticamente</p>
                            </div>

                            <!-- Email -->
                            <div style="margin-bottom:20px;">
                                <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:6px;">Email (opcional)</label>
                                <input v-model="form.cliente_email" type="email"
                                    placeholder="cliente@email.com"
                                    style="width:100%; padding:12px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box;"/>
                            </div>

                            <!-- Botones -->
                            <div style="display:flex; gap:10px;">
                                <button @click="mostrarModalCliente=false; form.tipo_comprobante='ninguno'; form.cliente_documento=''; form.cliente_nombre=''"
                                    style="flex:1; padding:14px; background:#F1F5F9; color:#64748B; border:none; border-radius:12px; font-size:14px; font-weight:600; cursor:pointer;">
                                    Cancelar
                                </button>
                                <button @click="mostrarModalCliente=false"
                                    :disabled="!form.cliente_nombre"
                                    style="flex:2; padding:14px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; font-size:14px; font-weight:700; cursor:pointer;"
                                    :style="{ opacity: form.cliente_nombre ? 1 : 0.5 }">
                                    ✅ Confirmar datos
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Botón emitir comprobante -->
                </div>
            </div>
        </div>
        <!-- PRE-CUENTA IMPRIMIBLE -->
        <div id="precuenta-print" class="precuenta-print">
            <div style="text-align:center; border-bottom:1px dashed #000; padding-bottom:6px; margin-bottom:8px;">
                <div style="font-size:16px; font-weight:bold;">PRE-CUENTA</div>
                <div style="font-size:13px;">Mesa {{ mesa.numero }}</div>
                <div style="font-size:10px;">{{ horaImpresion }}</div>
                <div style="font-size:10px; font-style:italic;">No es comprobante de pago</div>
            </div>
            <div v-for="p in platos" :key="'pp'+p.id" style="font-size:12px; display:flex; justify-content:space-between; margin:3px 0;">
                <span>{{ p.cantidad }}x {{ p.nombre }}</span>
                <span>S/ {{ p.subtotal.toFixed(2) }}</span>
            </div>
            <div style="border-top:1px dashed #000; margin-top:8px; padding-top:6px; display:flex; justify-content:space-between; font-size:15px; font-weight:bold;">
                <span>TOTAL</span>
                <span>S/ {{ Number(total).toFixed(2) }}</span>
            </div>
            <div style="text-align:center; font-size:10px; margin-top:10px;">¡Gracias por su visita!</div>
        </div>

    </AppLayout>
</template>

<style>
.precuenta-print { display: none; }

@media print {
    body * { visibility: hidden !important; }
    .precuenta-print, .precuenta-print * { visibility: visible !important; }
    .precuenta-print {
        display: block !important;
        position: absolute;
        left: 0; top: 0;
        width: 280px;
        padding: 8px;
        font-family: 'Courier New', monospace;
        color: #000;
    }
    @page { margin: 4mm; }
}
</style>
