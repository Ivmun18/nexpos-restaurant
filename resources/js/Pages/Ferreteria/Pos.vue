<template>
    <AppLayout title="POS Ferretería" :subtitle="`Punto de venta — ${$page.props.auth.user.name}`">

        <div style="display:grid; grid-template-columns:1fr min(380px,40vw); gap:16px; height:calc(100vh - 140px); overflow:hidden;">

            <!-- PANEL IZQUIERDO -->
            <div style="display:flex; flex-direction:column; gap:16px; overflow:hidden; min-height:0;">

                <!-- Búsqueda / Lector -->
                <div style="background:white; border-radius:16px; padding:16px; border:1px solid #E2E8F0; flex-shrink:0;">
                    <div style="display:flex; gap:12px; align-items:center;">
                        <div style="flex:1; position:relative;">
                            <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:18px;">🔍</span>
                            <input ref="inputBusqueda" v-model="busqueda"
                                @keyup.enter="escanearCodigo" @input="autoEscanear"
                                placeholder="Escanea código de barras o busca por nombre..."
                                style="width:100%; padding:12px 12px 12px 42px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box;">
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button @click="vistaGrid=true" :style="{padding:'10px', borderRadius:'8px', border:'1px solid', borderColor: vistaGrid ? '#14B8A6' : '#E2E8F0', background: vistaGrid ? '#F0FDFA' : 'white', cursor:'pointer'}">⊞</button>
                            <button @click="vistaGrid=false" :style="{padding:'10px', borderRadius:'8px', border:'1px solid', borderColor: !vistaGrid ? '#14B8A6' : '#E2E8F0', background: !vistaGrid ? '#F0FDFA' : 'white', cursor:'pointer'}">☰</button>
                        </div>
                    </div>
                    <!-- Filtro categorías -->
                    <div style="display:flex; gap:8px; margin-top:12px; flex-wrap:wrap;">
                        <button @click="filtroCategoria=''" :style="{padding:'4px 12px', borderRadius:'20px', fontSize:'12px', fontWeight:'600', border:'1px solid', borderColor: filtroCategoria==='' ? '#14B8A6' : '#E2E8F0', background: filtroCategoria==='' ? '#F0FDFA' : 'white', color: filtroCategoria==='' ? '#0F766E' : '#64748B', cursor:'pointer'}">Todos</button>
                        <button v-for="cat in categorias" :key="cat.id" @click="filtroCategoria=cat.id"
                            :style="{padding:'4px 12px', borderRadius:'20px', fontSize:'12px', fontWeight:'600', border:'1px solid', borderColor: filtroCategoria===cat.id ? cat.color : '#E2E8F0', background: filtroCategoria===cat.id ? cat.color+'22' : 'white', color: filtroCategoria===cat.id ? cat.color : '#64748B', cursor:'pointer'}">
                            {{ cat.icono }} {{ cat.nombre }}
                        </button>
                    </div>
                </div>

                <!-- Catálogo -->
                <div style="flex:1; overflow-y:auto; background:white; border-radius:16px; padding:16px; border:1px solid #E2E8F0; min-height:0;">

                    <!-- Vista Grid -->
                    <div v-if="vistaGrid" style="display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:12px;">
                        <div v-for="p in productosFiltrados" :key="p.id"
                            @click="agregarAlCarrito(p)"
                            :style="{background: p.stock_actual<=0 ? '#F8FAFC' : 'white', border:'1px solid #E2E8F0', borderRadius:'12px', padding:'12px', cursor: p.stock_actual<=0 ? 'not-allowed' : 'pointer', opacity: p.stock_actual<=0 ? 0.5 : 1}">
                            <div style="font-size:28px; text-align:center; margin-bottom:8px;">🔧</div>
                            <p style="font-size:12px; font-weight:600; color:#1E293B; margin:0 0 4px; line-height:1.3;">{{ p.descripcion }}</p>
                            <p style="font-size:11px; color:#94A3B8; margin:0 0 6px;">{{ p.codigo }}</p>
                            <p style="font-size:14px; font-weight:700; color:#14B8A6; margin:0;">S/ {{ Number(p.precio_venta).toFixed(2) }}</p>
                            <p style="font-size:11px; color:#94A3B8; margin:2px 0 0;">Stock: {{ p.stock_actual }} {{ p.unidad_medida }}</p>
                        </div>
                    </div>

                    <!-- Vista Lista -->
                    <div v-else style="display:flex; flex-direction:column; gap:8px;">
                        <div v-for="p in productosFiltrados" :key="p.id"
                            @click="agregarAlCarrito(p)"
                            :style="{display:'flex', alignItems:'center', gap:'12px', padding:'10px 12px', border:'1px solid #E2E8F0', borderRadius:'10px', cursor: p.stock_actual<=0 ? 'not-allowed' : 'pointer', opacity: p.stock_actual<=0 ? 0.5 : 1, background:'white'}">
                            <div style="font-size:24px;">🔧</div>
                            <div style="flex:1; min-width:0;">
                                <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ p.descripcion }}</p>
                                <p style="font-size:11px; color:#94A3B8; margin:0;">{{ p.codigo }} · {{ p.unidad_medida }}</p>
                            </div>
                            <div style="text-align:right; flex-shrink:0;">
                                <p style="font-size:14px; font-weight:700; color:#14B8A6; margin:0;">S/ {{ Number(p.precio_venta).toFixed(2) }}</p>
                                <p style="font-size:11px; color:#94A3B8; margin:0;">Stock: {{ p.stock_actual }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- PANEL DERECHO: Carrito -->
            <div style="display:flex; flex-direction:column; gap:12px; overflow-y:auto; min-height:0;">

                <!-- Carrito items -->
                <div style="flex:1; background:white; border-radius:16px; padding:16px; border:1px solid #E2E8F0; overflow-y:auto; min-height:0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <p style="font-size:15px; font-weight:700; color:#1E293B; margin:0;">🛒 Carrito ({{ carrito.length }})</p>
                        <button v-if="carrito.length" @click="carrito=[]" style="font-size:12px; color:#DC2626; cursor:pointer; border:none; background:none;">Limpiar</button>
                    </div>
                    <div v-if="!carrito.length" style="text-align:center; padding:40px 0; color:#94A3B8; font-size:13px;">
                        Escanea o selecciona productos
                    </div>
                    <div v-for="(item,i) in carrito" :key="item.id" style="display:flex; flex-direction:column; gap:4px; padding:10px 0; border-bottom:1px solid #F1F5F9;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                            <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0; flex:1; padding-right:8px;">{{ item.descripcion }}</p>
                            <button @click="carrito.splice(i,1)" style="color:#DC2626; border:none; background:none; cursor:pointer; font-size:16px; line-height:1;">×</button>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div style="display:flex; align-items:center; gap:6px;">
                                <button @click="decrementar(i)" style="width:24px; height:24px; border-radius:6px; border:1px solid #E2E8F0; background:white; cursor:pointer; font-weight:700;">−</button>
                                <input v-model.number="item.cantidad" type="number" min="1"
                                    style="width:50px; text-align:center; border:1px solid #E2E8F0; border-radius:6px; padding:2px; font-size:13px;">
                                <button @click="incrementar(i)" style="width:24px; height:24px; border-radius:6px; border:1px solid #14B8A6; background:#F0FDFA; cursor:pointer; font-weight:700; color:#14B8A6;">+</button>
                            </div>
                            <p style="font-size:14px; font-weight:700; color:#14B8A6; margin:0;">S/ {{ (item.precio_venta * item.cantidad).toFixed(2) }}</p>
                        </div>
                        <p style="font-size:11px; color:#94A3B8; margin:0;">S/ {{ Number(item.precio_venta).toFixed(2) }} × {{ item.cantidad }} {{ item.unidad_medida }}</p>
                    </div>
                </div>

                <!-- Totales y cobro -->
                <div style="background:white; border-radius:16px; padding:16px; border:1px solid #E2E8F0; flex-shrink:0;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Subtotal</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ subtotal }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                        <span style="font-size:13px; color:#64748B;">IGV (18%)</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ igv }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:16px; padding-top:12px; border-top:2px solid #F0F2F5;">
                        <span style="font-size:16px; font-weight:700; color:#1E293B;">TOTAL</span>
                        <span style="font-size:20px; font-weight:700; color:#14B8A6;">S/ {{ total }}</span>
                    </div>

                    <!-- Comprobante -->
                    <div style="display:flex; gap:8px; margin-bottom:12px;">
                        <button @click="tipoComprobante='ninguno'" :style="{flex:1, padding:'8px', borderRadius:'8px', border:'1px solid', borderColor: tipoComprobante==='ninguno' ? '#14B8A6' : '#E2E8F0', background: tipoComprobante==='ninguno' ? '#F0FDFA' : 'white', fontSize:'11px', fontWeight:'600', color: tipoComprobante==='ninguno' ? '#0F766E' : '#64748B', cursor:'pointer'}">Ninguno</button>
                        <button @click="tipoComprobante='boleta'" :style="{flex:1, padding:'8px', borderRadius:'8px', border:'1px solid', borderColor: tipoComprobante==='boleta' ? '#14B8A6' : '#E2E8F0', background: tipoComprobante==='boleta' ? '#F0FDFA' : 'white', fontSize:'11px', fontWeight:'600', color: tipoComprobante==='boleta' ? '#0F766E' : '#64748B', cursor:'pointer'}">Boleta</button>
                        <button @click="tipoComprobante='factura'" :style="{flex:1, padding:'8px', borderRadius:'8px', border:'1px solid', borderColor: tipoComprobante==='factura' ? '#14B8A6' : '#E2E8F0', background: tipoComprobante==='factura' ? '#F0FDFA' : 'white', fontSize:'11px', fontWeight:'600', color: tipoComprobante==='factura' ? '#0F766E' : '#64748B', cursor:'pointer'}">Factura</button>
                    </div>

                    <!-- Método de pago -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:12px;">
                        <button @click="metodoPago='efectivo'" :style="{padding:'8px', borderRadius:'8px', border:'1px solid', borderColor: metodoPago==='efectivo' ? '#14B8A6' : '#E2E8F0', background: metodoPago==='efectivo' ? '#F0FDFA' : 'white', fontSize:'12px', fontWeight:'600', color: metodoPago==='efectivo' ? '#0F766E' : '#64748B', cursor:'pointer'}">💵 Efectivo</button>
                        <button @click="metodoPago='tarjeta'" :style="{padding:'8px', borderRadius:'8px', border:'1px solid', borderColor: metodoPago==='tarjeta' ? '#14B8A6' : '#E2E8F0', background: metodoPago==='tarjeta' ? '#F0FDFA' : 'white', fontSize:'12px', fontWeight:'600', color: metodoPago==='tarjeta' ? '#0F766E' : '#64748B', cursor:'pointer'}">💳 Tarjeta</button>
                        <button @click="metodoPago='yape'" :style="{padding:'8px', borderRadius:'8px', border:'1px solid', borderColor: metodoPago==='yape' ? '#14B8A6' : '#E2E8F0', background: metodoPago==='yape' ? '#F0FDFA' : 'white', fontSize:'12px', fontWeight:'600', color: metodoPago==='yape' ? '#0F766E' : '#64748B', cursor:'pointer'}">📱 Yape</button>
                        <button @click="metodoPago='plin'" :style="{padding:'8px', borderRadius:'8px', border:'1px solid', borderColor: metodoPago==='plin' ? '#14B8A6' : '#E2E8F0', background: metodoPago==='plin' ? '#F0FDFA' : 'white', fontSize:'12px', fontWeight:'600', color: metodoPago==='plin' ? '#0F766E' : '#64748B', cursor:'pointer'}">📲 Plin</button>
                    </div>

                    <!-- Monto pagado si efectivo -->
                    <div v-if="metodoPago==='efectivo'" style="margin-bottom:12px;">
                        <input v-model="montoPagado" type="number" placeholder="Monto pagado (S/)"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;">
                        <p v-if="vuelto >= 0" style="font-size:13px; color:#16A34A; font-weight:600; margin:6px 0 0; text-align:right;">
                            Vuelto: S/ {{ vuelto }}
                        </p>
                    </div>

                    <!-- Datos cliente -->
                    <div v-if="tipoComprobante !== 'ninguno'" style="margin-bottom:12px; display:flex; flex-direction:column; gap:8px;">
                        <input v-model="clienteDni" type="text"
                            :placeholder="tipoComprobante === 'factura' ? 'RUC del cliente *' : 'DNI del cliente (opcional)'"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                        />
                        <input v-if="tipoComprobante === 'factura'" v-model="clienteRazonSocial" type="text"
                            placeholder="Razón social del cliente *"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                        />
                        <input v-model="clienteEmail" type="email"
                            placeholder="Email (para enviar comprobante)"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                        />
                    </div>

                    <button @click="cobrar" :disabled="!carrito.length || procesando"
                        :style="{width:'100%', padding:'14px', borderRadius:'10px', border:'none', background: carrito.length ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : '#E2E8F0', color: carrito.length ? 'white' : '#94A3B8', fontSize:'15px', fontWeight:'700', cursor: carrito.length ? 'pointer' : 'not-allowed'}">
                        {{ procesando ? 'Procesando...' : `Cobrar S/ ${total}` }}
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    productos:  { type: Array, default: () => [] },
    categorias: { type: Array, default: () => [] },
})

const busqueda        = ref('')
const filtroCategoria = ref('')
const vistaGrid       = ref(false)
const carrito         = ref([])
const metodoPago      = ref('efectivo')
const montoPagado     = ref('')
const tipoComprobante    = ref('ninguno')
const clienteDni         = ref('')
const clienteRazonSocial = ref('')
const clienteEmail       = ref('')
const procesando         = ref(false)
const inputBusqueda   = ref(null)
const scanTimer        = ref(null)

const productosFiltrados = computed(() => {
    return props.productos.filter(p => {
        const matchCat = !filtroCategoria.value || p.categoria_id == filtroCategoria.value
        const matchBus = !busqueda.value ||
            p.descripcion?.toLowerCase().includes(busqueda.value.toLowerCase()) ||
            p.codigo?.toLowerCase().includes(busqueda.value.toLowerCase()) ||
            p.codigo_barras?.includes(busqueda.value)
        return matchCat && matchBus
    })
})

const subtotal = computed(() => {
    const s = carrito.value.reduce((sum, i) => sum + i.precio_venta * i.cantidad, 0)
    return (s / 1.18).toFixed(2)
})
const igv   = computed(() => (carrito.value.reduce((s,i) => s + i.precio_venta * i.cantidad, 0) - parseFloat(subtotal.value)).toFixed(2))
const total = computed(() => carrito.value.reduce((s,i) => s + i.precio_venta * i.cantidad, 0).toFixed(2))
const vuelto = computed(() => montoPagado.value ? (parseFloat(montoPagado.value) - parseFloat(total.value)).toFixed(2) : -1)

const autoEscanear = () => {
    clearTimeout(scanTimer.value)
    if (busqueda.value.length < 3) return
    scanTimer.value = setTimeout(() => {
        escanearCodigo()
    }, 300)
}

const escanearCodigo = () => {
    const codigo = busqueda.value.trim()
    if (!codigo) return
    const producto = props.productos.find(p => p.codigo_barras === codigo || p.codigo === codigo)
    if (producto) {
        agregarAlCarrito(producto)
        busqueda.value = ''
        if (inputBusqueda.value) {
            inputBusqueda.value.style.border = '2px solid #16A34A'
            setTimeout(() => { inputBusqueda.value.style.border = '2px solid #E2E8F0' }, 500)
        }
    } else {
        if (inputBusqueda.value) {
            inputBusqueda.value.style.border = '2px solid #DC2626'
            setTimeout(() => { inputBusqueda.value.style.border = '2px solid #E2E8F0' }, 800)
        }
    }
}

const agregarAlCarrito = (p) => {
    if (p.stock_actual <= 0) return
    const existe = carrito.value.find(i => i.id === p.id)
    if (existe) {
        if (existe.cantidad < p.stock_actual) existe.cantidad++
    } else {
        carrito.value.push({ ...p, cantidad: 1 })
    }
}

const incrementar = (i) => {
    const item = carrito.value[i]
    const prod = props.productos.find(p => p.id === item.id)
    if (item.cantidad < prod.stock_actual) item.cantidad++
}

const decrementar = (i) => {
    if (carrito.value[i].cantidad > 1) carrito.value[i].cantidad--
    else carrito.value.splice(i, 1)
}

const cobrar = () => {
    if (!carrito.value.length || procesando.value) return
    procesando.value = true
    router.post('/ferreteria/pos', {
        items:               carrito.value,
        metodo_pago:         metodoPago.value,
        total:               total.value,
        monto_pagado:        montoPagado.value || null,
        tipo_comprobante:    tipoComprobante.value,
        cliente_dni:         clienteDni.value,
        cliente_razon_social: clienteRazonSocial.value,
        cliente_email:       clienteEmail.value,
    }, {
        onSuccess: () => {
            carrito.value            = []
            montoPagado.value        = ''
            procesando.value         = false
            tipoComprobante.value    = 'ninguno'
            clienteDni.value         = ''
            clienteRazonSocial.value = ''
            clienteEmail.value       = ''
        },
        onError: () => { procesando.value = false }
    })
}
</script>
