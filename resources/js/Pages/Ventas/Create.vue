<template>
    <AppLayout title="Nueva venta" subtitle="Punto de venta rapido">

        <!-- Layout responsive: columnas en desktop, apilado en movil -->
        <div class="pos-container">

            <!-- Panel izquierdo -->
            <div class="pos-left">

                <!-- Tipo comprobante -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1rem; margin-bottom:1rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <button type="button" @click="cambiarTipo('03')" :style="form.tipo_comprobante==='03' ? btnActivo : btnInactivo">Boleta B001</button>
                        <button type="button" @click="cambiarTipo('01')" :style="form.tipo_comprobante==='01' ? btnActivo : btnInactivo">Factura F001</button>
                    </div>
                    <span style="font-size:12px; color:#94A3B8;">{{ fechaHoy }}</span>
                </div>

                <!-- Buscador de productos -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1rem; margin-bottom:1rem;">
                    <input v-model="busquedaProducto" type="text"
                        placeholder="Buscar producto por nombre o codigo..."
                        @input="filtrarProductos" @keydown.enter="agregarPrimero"
                        style="width:100%; padding:12px 16px; border:2px solid #2563EB; border-radius:10px; font-size:14px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                    <div v-if="resultados.length > 0 && busquedaProducto"
                        style="margin-top:8px; border:1px solid #E2E8F0; border-radius:8px; overflow:hidden; max-height:200px; overflow-y:auto;">
                        <div v-for="p in resultados" :key="p.id" @click="agregarProducto(p)"
                            style="padding:12px 14px; cursor:pointer; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #F1F5F9; background:white;"
                            @mouseover="$event.currentTarget.style.background='#EFF6FF'"
                            @mouseleave="$event.currentTarget.style.background='white'">
                            <div>
                                <p style="font-size:14px; font-weight:500; color:#1E293B; margin:0;">{{ p.descripcion }}</p>
                                <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ p.codigo }}</p>
                            </div>
                            <span style="font-size:14px; font-weight:700; color:#2563EB; white-space:nowrap; margin-left:10px;">S/ {{ Number(p.precio_venta).toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden; margin-bottom:1rem;">
                    <div v-if="form.items.length === 0" style="padding:2rem; text-align:center; color:#CBD5E1; font-size:14px;">
                        Busca un producto para agregarlo
                    </div>
                    <div v-for="(item, i) in form.items" :key="i"
                        style="padding:12px; border-bottom:1px solid #F1F5F9;">
                        <div style="display:flex; align-items:start; justify-content:space-between; margin-bottom:8px;">
                            <div style="flex:1; min-width:0;">
                                <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0; word-break:break-word;">{{ item.descripcion }}</p>
                                <p style="font-size:12px; color:#94A3B8; margin:3px 0 0;">{{ item.unidad_medida }} - {{ item.afecto_igv ? 'IGV 18%' : 'Exonerado' }}</p>
                            </div>
                            <button type="button" @click="quitarItem(i)"
                                style="background:#FEF2F2; color:#991B1B; border:none; border-radius:6px; padding:6px 10px; font-size:13px; cursor:pointer; margin-left:8px; flex-shrink:0;">X</button>
                        </div>
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                            <div style="display:flex; align-items:center; gap:6px;">
                                <button type="button" @click="decrementar(i)"
                                    style="width:36px; height:36px; background:#F1F5F9; border:none; border-radius:8px; font-size:18px; cursor:pointer; color:#64748B; flex-shrink:0;">-</button>
                                <input v-model="item.cantidad" type="number" step="0.001" min="0.001"
                                    @input="calcularItem(i)"
                                    style="width:60px; text-align:center; padding:8px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; font-weight:600; color:#1E293B; outline:none;"/>
                                <button type="button" @click="incrementar(i)"
                                    style="width:36px; height:36px; background:#EFF6FF; border:none; border-radius:8px; font-size:18px; cursor:pointer; color:#2563EB; flex-shrink:0;">+</button>
                            </div>
                            <div style="text-align:right;">
                                <p style="font-size:12px; color:#94A3B8; margin:0;">S/ {{ Number(item.precio_unitario).toFixed(2) }} c/u</p>
                                <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">S/ {{ Number(item.total).toFixed(2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cliente -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1rem; margin-bottom:1rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                        <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">Cliente</p>
                        <span v-if="form.tipo_comprobante==='03'" style="font-size:11px; background:#F0FDF4; color:#166534; padding:3px 10px; border-radius:20px;">Opcional</span>
                        <span v-else style="font-size:11px; background:#EFF6FF; color:#1D4ED8; padding:3px 10px; border-radius:20px;">RUC requerido</span>
                    </div>

                    <!-- BOLETA -->
                    <div v-if="form.tipo_comprobante==='03'">
                        <div style="display:flex; gap:6px; margin-bottom:12px; flex-wrap:wrap;">
                            <button type="button" @click="modoCliente='varios'" :style="modoCliente==='varios' ? btnActivo : btnInactivo" style="font-size:12px; padding:8px 14px;">Varios</button>
                            <button type="button" @click="modoCliente='dni'" :style="modoCliente==='dni' ? btnActivo : btnInactivo" style="font-size:12px; padding:8px 14px;">Con DNI</button>
                            <button type="button" @click="modoCliente='buscar'" :style="modoCliente==='buscar' ? btnActivo : btnInactivo" style="font-size:12px; padding:8px 14px;">Buscar</button>
                        </div>

                        <div v-if="modoCliente==='varios'" style="background:#F8FAFC; border-radius:8px; padding:12px 16px;">
                            <p style="font-size:13px; color:#64748B; margin:0;">Se emitira a <strong>CLIENTES VARIOS</strong></p>
                        </div>

                        <div v-if="modoCliente==='dni'" style="display:flex; gap:8px; flex-wrap:wrap;">
                            <input v-model="form.cliente_num_doc" type="number" placeholder="DNI" maxlength="8"
                                style="width:120px; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; outline:none;"/>
                            <button type="button" @click="buscarDni"
                                style="padding:10px 14px; background:#EFF6FF; color:#2563EB; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">Buscar</button>
                            <input v-model="form.cliente_razon_social" type="text" placeholder="Nombre completo"
                                style="flex:1; min-width:150px; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;"/>
                        </div>

                        <div v-if="modoCliente==='buscar'">
                            <input v-model="busquedaCliente" type="text" placeholder="Buscar cliente..." @input="filtrarClientes"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                            <div v-if="clientesFiltrados.length > 0" style="margin-top:6px; border:1px solid #E2E8F0; border-radius:8px; overflow:hidden; max-height:160px; overflow-y:auto;">
                                <div v-for="c in clientesFiltrados" :key="c.id" @click="seleccionarCliente(c)"
                                    style="padding:12px 14px; cursor:pointer; border-bottom:1px solid #F1F5F9; background:white;"
                                    @mouseover="$event.currentTarget.style.background='#EFF6FF'"
                                    @mouseleave="$event.currentTarget.style.background='white'">
                                    <p style="font-size:13px; font-weight:500; color:#1E293B; margin:0;">{{ c.razon_social }}</p>
                                    <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ c.numero_documento }}</p>
                                </div>
                            </div>
                            <div v-if="clienteSeleccionado" style="margin-top:8px; background:#EFF6FF; border-radius:8px; padding:10px 14px; display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0;">{{ clienteSeleccionado.razon_social }}</p>
                                    <p style="font-size:12px; color:#64748B; margin:2px 0 0;">{{ clienteSeleccionado.numero_documento }}</p>
                                </div>
                                <button type="button" @click="clienteSeleccionado=null; busquedaCliente=''"
                                    style="background:#FEF2F2; color:#991B1B; border:none; border-radius:6px; padding:4px 10px; font-size:12px; cursor:pointer;">X</button>
                            </div>
                        </div>
                    </div>

                    <!-- FACTURA -->
                    <div v-else>
                        <div style="display:flex; gap:8px; margin-bottom:10px; flex-wrap:wrap;">
                            <input v-model="rucBusqueda" type="number" placeholder="RUC (11 digitos)" maxlength="11"
                                style="flex:1; min-width:150px; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; font-family:monospace;"/>
                            <button type="button" @click="buscarRuc"
                                style="padding:10px 16px; background:#2563EB; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; white-space:nowrap;">
                                Buscar RUC
                            </button>
                        </div>
                        <div v-if="clienteFactura" style="background:#EFF6FF; border-radius:8px; padding:12px; border:1px solid #BFDBFE; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0;">{{ clienteFactura.razon_social }}</p>
                                <p style="font-size:12px; color:#64748B; margin:3px 0 0;">RUC: {{ clienteFactura.numero_documento }}</p>
                            </div>
                            <button type="button" @click="clienteFactura=null; rucBusqueda=''"
                                style="background:#FEF2F2; color:#991B1B; border:none; border-radius:6px; padding:4px 10px; font-size:12px; cursor:pointer; flex-shrink:0; margin-left:8px;">X</button>
                        </div>
                        <div v-if="!clienteFactura">
                            <input v-model="form.cliente_razon_social" type="text" placeholder="Razon social del cliente"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; margin-bottom:8px;"/>
                            <input v-model="form.cliente_direccion" type="text" placeholder="Direccion (opcional)"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Panel derecho (resumen y cobro) -->
            <div class="pos-right">
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.2rem;">

                    <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Resumen</p>

                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Op. gravadas</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ totales.gravado.toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Op. exoneradas</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ totales.exonerado.toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">IGV (18%)</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ totales.igv.toFixed(2) }}</span>
                    </div>

                    <div style="border-top:2px solid #E2E8F0; margin:12px 0; padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:15px; font-weight:700; color:#1E293B;">Total</span>
                        <span style="font-size:28px; font-weight:700; color:#2563EB;">S/ {{ totales.total.toFixed(2) }}</span>
                    </div>

                    <div style="margin-bottom:10px;">
                        <label style="font-size:12px; color:#94A3B8; display:block; margin-bottom:4px; text-transform:uppercase;">Efectivo recibido</label>
                        <input v-model="form.monto_pagado" type="number" step="0.01" min="0" inputmode="decimal"
                            style="width:100%; padding:14px; border:2px solid #2563EB; border-radius:10px; font-size:22px; font-weight:700; color:#1E293B; outline:none; box-sizing:border-box; text-align:right;"/>
                    </div>

                    <!-- Botones de monto rapido -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-bottom:10px;">
                        <button type="button" @click="form.monto_pagado=10" style="padding:10px; background:#F1F5F9; border:none; border-radius:8px; font-size:14px; font-weight:600; color:#64748B; cursor:pointer;">S/ 10</button>
                        <button type="button" @click="form.monto_pagado=20" style="padding:10px; background:#F1F5F9; border:none; border-radius:8px; font-size:14px; font-weight:600; color:#64748B; cursor:pointer;">S/ 20</button>
                        <button type="button" @click="form.monto_pagado=50" style="padding:10px; background:#F1F5F9; border:none; border-radius:8px; font-size:14px; font-weight:600; color:#64748B; cursor:pointer;">S/ 50</button>
                        <button type="button" @click="form.monto_pagado=totales.total" style="padding:10px; background:#EFF6FF; border:none; border-radius:8px; font-size:14px; font-weight:600; color:#2563EB; cursor:pointer;">Exacto</button>
                    </div>

                    <div v-if="vuelto > 0" style="background:#F0FDF4; border-radius:8px; padding:12px 14px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:14px; color:#166534; font-weight:500;">Vuelto</span>
                        <span style="font-size:22px; font-weight:700; color:#166534;">S/ {{ vuelto.toFixed(2) }}</span>
                    </div>

                    <p v-if="error" style="color:#991B1B; font-size:13px; margin-bottom:10px;">{{ error }}</p>

                    <button type="button" @click="guardar" :disabled="procesando || form.items.length === 0"
                        style="width:100%; padding:16px; background:#2563EB; color:white; border:none; border-radius:10px; font-size:16px; font-weight:700; cursor:pointer;"
                        :style="form.items.length === 0 ? {opacity:'0.5', cursor:'not-allowed'} : {opacity:'1'}">
                        {{ procesando ? 'Procesando...' : 'Emitir comprobante' }}
                    </button>

                    <a href="/ventas" style="display:block; text-align:center; margin-top:10px; font-size:13px; color:#94A3B8; text-decoration:none; padding:8px;">
                        Cancelar
                    </a>
                </div>
            </div>

        </div>

        <!-- CSS Responsive -->
        <style>
        .pos-container {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 1.5rem;
            align-items: start;
        }
        .pos-left { min-width: 0; }
        .pos-right { position: sticky; top: 80px; }

        @media (max-width: 768px) {
            .pos-container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            .pos-right {
                position: static;
                order: -1;
            }
        }
        </style>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    clientes:   Array,
    productos:  Array,
    cotizacion: Object,
})

const procesando          = ref(false)
const error               = ref('')
const busquedaProducto    = ref('')
const resultados          = ref([])
const modoCliente         = ref('varios')
const busquedaCliente     = ref('')
const clientesFiltrados   = ref([])
const clienteSeleccionado = ref(null)
const clienteFactura      = ref(null)
const rucBusqueda         = ref('')

const form = ref({
    tipo_comprobante:    '03',
    cliente_id:          null,
    cliente_tipo_doc:    '0',
    cliente_num_doc:     '',
    cliente_razon_social:'CLIENTES VARIOS',
    cliente_direccion:   '',
    cliente_email:       '',
    forma_pago:          'contado',
    monto_pagado:        0,
    observaciones:       '',
    items:               [],
})

const btnActivo   = { padding:'8px 16px', background:'#2563EB', color:'white', border:'none', borderRadius:'8px', fontSize:'13px', fontWeight:'600', cursor:'pointer' }
const btnInactivo = { padding:'8px 16px', background:'#F1F5F9', color:'#64748B', border:'1px solid #E2E8F0', borderRadius:'8px', fontSize:'13px', cursor:'pointer' }

const fechaHoy = computed(() => new Date().toLocaleDateString('es-PE', { weekday:'long', year:'numeric', month:'long', day:'numeric' }))

const cambiarTipo = (tipo) => {
    form.value.tipo_comprobante = tipo
    if (tipo === '03') {
        modoCliente.value = 'varios'
        form.value.cliente_tipo_doc     = '0'
        form.value.cliente_num_doc      = ''
        form.value.cliente_razon_social = 'CLIENTES VARIOS'
    } else {
        form.value.cliente_tipo_doc     = '6'
        form.value.cliente_razon_social = ''
        clienteFactura.value = null
        rucBusqueda.value    = ''
    }
}

const filtrarProductos = () => {
    const q = busquedaProducto.value.toLowerCase().trim()
    if (!q) { resultados.value = []; return }
    resultados.value = props.productos.filter(p =>
        p.descripcion.toLowerCase().includes(q) || p.codigo.toLowerCase().includes(q)
    ).slice(0, 8)
}

const agregarProducto = (p) => {
    busquedaProducto.value = ''
    resultados.value = []
    const existe = form.value.items.findIndex(i => i.producto_id === p.id)
    if (existe >= 0) { form.value.items[existe].cantidad++; calcularItem(existe); return }
    const precio = parseFloat(p.precio_venta)
    const afecto = p.afecto_igv
    const val    = afecto ? round(precio / 1.18, 4) : precio
    form.value.items.push({
        producto_id: p.id, codigo: p.codigo, descripcion: p.descripcion,
        unidad_medida: p.unidad_medida, tipo_afectacion_igv: p.tipo_afectacion_igv,
        afecto_igv: afecto, cantidad: 1, precio_unitario: precio,
        valor_unitario: val, total_valor_venta: val,
        total_igv: afecto ? round(val * 0.18, 2) : 0, total: round(precio, 2),
    })
    form.value.monto_pagado = totales.value.total
}

const agregarPrimero = () => { if (resultados.value.length > 0) agregarProducto(resultados.value[0]) }
const incrementar = (i) => { form.value.items[i].cantidad++; calcularItem(i) }
const decrementar = (i) => { if (form.value.items[i].cantidad > 1) { form.value.items[i].cantidad--; calcularItem(i) } }

const calcularItem = (i) => {
    const item = form.value.items[i]
    const cant = parseFloat(item.cantidad) || 0
    const prec = parseFloat(item.precio_unitario) || 0
    item.valor_unitario    = item.afecto_igv ? round(prec / 1.18, 4) : prec
    item.total_valor_venta = round(item.valor_unitario * cant, 2)
    item.total_igv         = item.afecto_igv ? round(item.total_valor_venta * 0.18, 2) : 0
    item.total             = round(prec * cant, 2)
    form.value.monto_pagado = totales.value.total
}

const quitarItem = (i) => { form.value.items.splice(i, 1); form.value.monto_pagado = totales.value.total }

const filtrarClientes = () => {
    const q = busquedaCliente.value.toLowerCase().trim()
    if (!q) { clientesFiltrados.value = []; return }
    clientesFiltrados.value = props.clientes.filter(c =>
        c.razon_social.toLowerCase().includes(q) || c.numero_documento.includes(q)
    ).slice(0, 6)
}

const seleccionarCliente = (c) => {
    clienteSeleccionado.value       = c
    form.value.cliente_id           = c.id
    form.value.cliente_tipo_doc     = c.tipo_documento
    form.value.cliente_num_doc      = c.numero_documento
    form.value.cliente_razon_social = c.razon_social
    clientesFiltrados.value = []
    busquedaCliente.value   = ''
}

const buscarRuc = () => {
    const ruc = rucBusqueda.value.toString().trim()
    if (ruc.length !== 11) { error.value = 'El RUC debe tener 11 digitos.'; return }
    error.value = ''
    form.value.cliente_num_doc  = ruc
    form.value.cliente_tipo_doc = '6'
    const encontrado = props.clientes.find(c => c.numero_documento === ruc)
    if (encontrado) {
        clienteFactura.value            = encontrado
        form.value.cliente_id           = encontrado.id
        form.value.cliente_razon_social = encontrado.razon_social
        form.value.cliente_direccion    = encontrado.direccion || ''
    } else {
        clienteFactura.value = null
        form.value.cliente_razon_social = ''
        form.value.cliente_direccion    = ''
    }
}

const buscarDni = () => {
    const dni = form.value.cliente_num_doc.toString().trim()
    if (dni.length !== 8) { error.value = 'El DNI debe tener 8 digitos.'; return }
    error.value = ''
    form.value.cliente_tipo_doc = '1'
    const encontrado = props.clientes.find(c => c.numero_documento === dni)
    if (encontrado) { form.value.cliente_razon_social = encontrado.razon_social }
}

const round = (val, dec) => Math.round(val * Math.pow(10, dec)) / Math.pow(10, dec)

const totales = computed(() => {
    let gravado = 0, exonerado = 0, igv = 0, total = 0
    form.value.items.forEach(item => {
        if (item.afecto_igv) { gravado += parseFloat(item.total_valor_venta) || 0; igv += parseFloat(item.total_igv) || 0 }
        else { exonerado += parseFloat(item.total_valor_venta) || 0 }
        total += parseFloat(item.total) || 0
    })
    return { gravado: round(gravado,2), exonerado: round(exonerado,2), igv: round(igv,2), total: round(total,2) }
})

const vuelto = computed(() => {
    const v = parseFloat(form.value.monto_pagado) - totales.value.total
    return v > 0 ? round(v, 2) : 0
})

// Cargar cotizacion si viene de conversion
if (props.cotizacion) {
    form.value.cliente_id           = props.cotizacion.cliente_id
    form.value.cliente_tipo_doc     = props.cotizacion.cliente_tipo_doc || '6'
    form.value.cliente_num_doc      = props.cotizacion.cliente_num_doc || ''
    form.value.cliente_razon_social = props.cotizacion.cliente_razon_social || ''
    form.value.cliente_direccion    = props.cotizacion.cliente_direccion || ''
    form.value.tipo_comprobante     = props.cotizacion.cliente_tipo_doc === '6' ? '01' : '03'

    props.cotizacion.items.forEach(item => {
        const producto = props.productos.find(p => p.id === item.producto_id)
        form.value.items.push({
            producto_id:         item.producto_id,
            codigo:              producto?.codigo || '',
            descripcion:         item.descripcion,
            unidad_medida:       item.unidad_medida,
            tipo_afectacion_igv: item.tipo_afectacion_igv,
            afecto_igv:          item.tipo_afectacion_igv === '10',
            cantidad:            parseFloat(item.cantidad),
            precio_unitario:     parseFloat(item.precio_unitario),
            valor_unitario:      parseFloat(item.valor_unitario),
            total_valor_venta:   parseFloat(item.total_valor_venta),
            total_igv:           parseFloat(item.total_igv),
            total:               parseFloat(item.total),
        })
    })

    form.value.monto_pagado = totales.value.total

    if (props.cotizacion.cliente_tipo_doc === '6') {
        clienteFactura.value = {
            numero_documento: props.cotizacion.cliente_num_doc,
            razon_social:     props.cotizacion.cliente_razon_social,
        }
        rucBusqueda.value = props.cotizacion.cliente_num_doc || ''
    }
}

const guardar = () => {
    error.value = ''
    if (form.value.items.length === 0) { error.value = 'Agrega al menos un producto.'; return }
    if (form.value.tipo_comprobante === '01' && !form.value.cliente_num_doc) { error.value = 'La factura requiere RUC del cliente.'; return }
    if (modoCliente.value === 'dni' && form.value.tipo_comprobante === '03') { form.value.cliente_tipo_doc = '1' }
    procesando.value = true
    router.post('/ventas', {
        ...form.value,
        total_gravado:   totales.value.gravado,
        total_exonerado: totales.value.exonerado,
        total_igv:       totales.value.igv,
        total:           totales.value.total,
    }, {
        onError: () => { error.value = 'Error al guardar.'; procesando.value = false }
    })
}
</script>