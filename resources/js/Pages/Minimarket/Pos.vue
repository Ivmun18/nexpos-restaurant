<template>
    <AppLayout title="POS Minimarket" :subtitle="`Punto de venta — ${$page.props.auth.user.name}`">

        <div style="display:grid; grid-template-columns:1fr min(380px, 40vw); gap:16px; height:calc(100vh - 140px); overflow:hidden;">

            <!-- ══ PANEL IZQUIERDO: Búsqueda + Catálogo ══ -->
            <div style="display:flex; flex-direction:column; gap:16px; overflow:hidden; min-height:0;">

                <!-- Búsqueda -->
                <div style="background:white; border-radius:16px; padding:16px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                    <div style="position:relative;">
                        <input
                            ref="inputBusqueda"
                            v-model="busqueda"
                            placeholder="🔍 Buscar por nombre o código de barras..."
                            style="width:100%; padding:12px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:15px; outline:none; box-sizing:border-box; transition:border 0.2s;"
                            @focus="$event.target.style.border='2px solid #14B8A6'"
                            @blur="$event.target.style.border='2px solid #E2E8F0'"
                            @keyup.enter="escanearCodigo"
                        />
                        <span style="position:absolute; right:14px; top:50%; transform:translateY(-50%); font-size:18px;" title="Listo para lector de barras">📷</span>
                    </div>
                </div>

                <!-- Catálogo -->
                <div style="flex:1; overflow-y:auto; background:white; border-radius:16px; padding:16px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                    <p style="font-size:13px; color:#94A3B8; margin:0 0 12px;">{{ productosFiltrados.length }} productos</p>
                    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:12px;">
                        <div
                            v-for="p in productosFiltrados" :key="p.id"
                            @click="agregarAlCarrito(p)"
                            :style="{
                                border: '2px solid ' + (p.stock_actual <= 0 ? '#FEE2E2' : '#E2E8F0'),
                                borderRadius: '12px',
                                padding: '14px',
                                cursor: p.stock_actual <= 0 ? 'not-allowed' : 'pointer',
                                opacity: p.stock_actual <= 0 ? '0.5' : '1',
                                transition: 'all 0.15s',
                                background: 'white',
                            }"
                            @mouseenter="e => { if(p.stock_actual > 0) e.currentTarget.style.borderColor='#14B8A6'; e.currentTarget.style.boxShadow='0 4px 12px rgba(20,184,166,0.15)' }"
                            @mouseleave="e => { e.currentTarget.style.borderColor = p.stock_actual <= 0 ? '#FEE2E2' : '#E2E8F0'; e.currentTarget.style.boxShadow='none' }"
                        >
                            <div style="font-size:32px; text-align:center; margin-bottom:8px;">
                                {{ iconProducto(p.categoria) }}
                            </div>
                            <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 4px; line-height:1.3;">{{ p.descripcion }}</p>
                            <p style="font-size:11px; color:#94A3B8; margin:0 0 6px;">{{ p.codigo_barras || 'Sin código' }}</p>
                            <p style="font-size:15px; font-weight:800; color:#14B8A6; margin:0;">S/ {{ Number(p.precio_venta).toFixed(2) }}</p>
                            <p :style="{ fontSize:'11px', margin:'4px 0 0', color: p.stock_actual <= 5 ? '#EF4444' : '#94A3B8' }">
                                Stock: {{ p.stock_actual }}
                            </p>
                        </div>
                    </div>
                    <div v-if="!productosFiltrados.length" style="text-align:center; padding:40px 0; color:#CBD5E1;">
                        <p style="font-size:40px; margin:0 0 8px;">📦</p>
                        <p style="font-size:15px;">No se encontraron productos</p>
                    </div>
                </div>
            </div>

            <!-- ══ PANEL DERECHO: Carrito + Cobro ══ -->
            <div style="display:flex; flex-direction:column; gap:16px; overflow-y:auto; min-height:0;">

                <!-- Carrito -->
                <div style="flex:1; background:white; border-radius:16px; padding:16px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(0,0,0,0.05); overflow-y:auto;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                        <p style="font-size:16px; font-weight:800; color:#1E293B; margin:0;">🛒 Carrito</p>
                        <button v-if="carrito.length" @click="carrito = []"
                            style="font-size:12px; color:#EF4444; background:none; border:none; cursor:pointer; font-weight:600;">
                            Limpiar
                        </button>
                    </div>

                    <div v-if="!carrito.length" style="text-align:center; padding:40px 0; color:#CBD5E1;">
                        <p style="font-size:40px; margin:0 0 8px;">🛒</p>
                        <p style="font-size:14px;">Carrito vacío</p>
                    </div>

                    <div v-for="(item, i) in carrito" :key="item.id"
                        style="display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #F1F5F9;">
                        <div style="flex:1; min-width:0;">
                            <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ item.descripcion }}</p>
                            <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">S/ {{ Number(item.precio_venta).toFixed(2) }} c/u</p>
                        </div>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <button @click="decrementar(i)"
                                style="width:26px; height:26px; border-radius:8px; border:1px solid #E2E8F0; background:white; cursor:pointer; font-size:14px; font-weight:700; color:#64748B;">−</button>
                            <span style="font-size:14px; font-weight:700; color:#1E293B; min-width:20px; text-align:center;">{{ item.cantidad }}</span>
                            <button @click="incrementar(i)"
                                style="width:26px; height:26px; border-radius:8px; border:1px solid #14B8A6; background:#F0FDFA; cursor:pointer; font-size:14px; font-weight:700; color:#14B8A6;">+</button>
                        </div>
                        <div style="text-align:right; min-width:60px;">
                            <p style="font-size:14px; font-weight:800; color:#14B8A6; margin:0;">S/ {{ (item.precio_venta * item.cantidad).toFixed(2) }}</p>
                        </div>
                        <button @click="carrito.splice(i, 1)"
                            style="color:#CBD5E1; background:none; border:none; cursor:pointer; font-size:16px;">✕</button>
                    </div>
                </div>

                <!-- Cobro -->
                <div style="background:white; border-radius:16px; padding:16px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(0,0,0,0.05);">

                    <!-- Total -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; padding:12px 16px; background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:12px;">
                        <p style="font-size:15px; font-weight:700; color:white; margin:0;">TOTAL</p>
                        <p style="font-size:28px; font-weight:900; color:white; margin:0;">S/ {{ total.toFixed(2) }}</p>
                    </div>

                    <!-- Método de pago -->
                    <p style="font-size:13px; font-weight:600; color:#64748B; margin:0 0 10px;">Método de pago</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:14px;">
                        <button v-for="m in metodos" :key="m.valor" @click="metodoPago = m.valor"
                            :style="{
                                padding: '10px',
                                borderRadius: '10px',
                                border: metodoPago === m.valor ? '2px solid #14B8A6' : '2px solid #E2E8F0',
                                background: metodoPago === m.valor ? '#F0FDFA' : 'white',
                                cursor: 'pointer',
                                fontSize: '13px',
                                fontWeight: '700',
                                color: metodoPago === m.valor ? '#0F766E' : '#64748B',
                            }">
                            {{ m.icon }} {{ m.label }}
                        </button>
                    </div>

                    <!-- Monto pagado (solo efectivo) -->
                    <div v-if="metodoPago === 'efectivo'" style="margin-bottom:14px;">
                        <p style="font-size:13px; font-weight:600; color:#64748B; margin:0 0 6px;">Monto recibido</p>
                        <input v-model="montoPagado" type="number" placeholder="0.00"
                            style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:16px; font-weight:700; box-sizing:border-box; outline:none;"
                            @focus="$event.target.style.borderColor='#14B8A6'"
                            @blur="$event.target.style.borderColor='#E2E8F0'"
                        />
                        <div v-if="montoPagado >= total" style="margin-top:8px; padding:10px 14px; background:#F0FDF4; border-radius:10px; display:flex; justify-content:space-between;">
                            <span style="font-size:14px; font-weight:600; color:#166534;">Vuelto</span>
                            <span style="font-size:16px; font-weight:800; color:#166534;">S/ {{ (montoPagado - total).toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Comprobante -->
                    <div style="margin-bottom:12px;">
                        <p style="font-size:12px; font-weight:600; color:#64748B; margin:0 0 8px; text-transform:uppercase; letter-spacing:1px;">Comprobante</p>
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; margin-bottom:10px;">
                            <button type="button" @click="tipoComprobante = 'ninguno'"
                                :style="tipoComprobante === 'ninguno' ? 'padding:8px; border-radius:8px; border:2px solid #14B8A6; background:#F0FDFA; color:#0F766E; font-size:12px; font-weight:600; cursor:pointer;' : 'padding:8px; border-radius:8px; border:2px solid #E2E8F0; background:white; color:#64748B; font-size:12px; cursor:pointer;'">
                                🚫 Ninguno
                            </button>
                            <button type="button" @click="tipoComprobante = 'boleta'"
                                :style="tipoComprobante === 'boleta' ? 'padding:8px; border-radius:8px; border:2px solid #14B8A6; background:#F0FDFA; color:#0F766E; font-size:12px; font-weight:600; cursor:pointer;' : 'padding:8px; border-radius:8px; border:2px solid #E2E8F0; background:white; color:#64748B; font-size:12px; cursor:pointer;'">
                                🧾 Boleta
                            </button>
                            <button type="button" @click="tipoComprobante = 'factura'"
                                :style="tipoComprobante === 'factura' ? 'padding:8px; border-radius:8px; border:2px solid #14B8A6; background:#F0FDFA; color:#0F766E; font-size:12px; font-weight:600; cursor:pointer;' : 'padding:8px; border-radius:8px; border:2px solid #E2E8F0; background:white; color:#64748B; font-size:12px; cursor:pointer;'">
                                📄 Factura
                            </button>
                        </div>

                        <!-- Datos cliente para boleta/factura -->
                        <div v-if="tipoComprobante !== 'ninguno'" style="display:flex; flex-direction:column; gap:8px;">
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
                    </div>

                    <!-- Botón cobrar -->
                    <button @click="cobrar"
                        :disabled="!carrito.length || procesando"
                        :style="{
                            width: '100%',
                            padding: '14px',
                            borderRadius: '12px',
                            border: 'none',
                            background: carrito.length ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : '#E2E8F0',
                            color: carrito.length ? 'white' : '#94A3B8',
                            fontSize: '16px',
                            fontWeight: '800',
                            cursor: carrito.length ? 'pointer' : 'not-allowed',
                            boxShadow: carrito.length ? '0 4px 12px rgba(20,184,166,0.3)' : 'none',
                        }">
                        {{ procesando ? '⏳ Procesando...' : '💳 Cobrar S/ ' + total.toFixed(2) }}
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
    productos: { type: Array, default: () => [] },
})

const busqueda   = ref('')
const carrito    = ref([])
const metodoPago = ref('efectivo')
const montoPagado = ref('')
const procesando  = ref(false)

const metodos = [
    { valor: 'efectivo', label: 'Efectivo', icon: '💵' },
    { valor: 'yape',     label: 'Yape',     icon: '📱' },
    { valor: 'plin',     label: 'Plin',     icon: '📲' },
    { valor: 'tarjeta',  label: 'Tarjeta',  icon: '💳' },
]

const productosFiltrados = computed(() => {
    if (!busqueda.value) return props.productos
    const q = busqueda.value.toLowerCase()
    return props.productos.filter(p =>
        p.descripcion.toLowerCase().includes(q) ||
        (p.codigo_barras && p.codigo_barras.toLowerCase().includes(q))
    )
})

const tipoComprobante = ref('ninguno')
const clienteDni = ref('')
const clienteRazonSocial = ref('')
const clienteEmail = ref('')

const total = computed(() =>
    carrito.value.reduce((sum, i) => sum + i.precio_venta * i.cantidad, 0)
)

const iconProducto = (categoria) => {
    const map = {
        bebidas: '🥤', lacteos: '🥛', panaderia: '🍞', carnes: '🥩',
        frutas: '🍎', verduras: '🥦', limpieza: '🧼', snacks: '🍿',
        conservas: '🥫', higiene: '🧴',
    }
    return map[categoria?.toLowerCase()] || '📦'
}

const inputBusqueda = ref(null)

const escanearCodigo = () => {
    const codigo = busqueda.value.trim()
    if (!codigo) return

    const producto = props.productos.find(p =>
        p.codigo_barras === codigo || p.codigo === codigo
    )

    if (producto) {
        agregarAlCarrito(producto)
        busqueda.value = ''
        const input = inputBusqueda.value
        if (input) {
            input.style.border = '2px solid #16A34A'
            setTimeout(() => { input.style.border = '2px solid #E2E8F0' }, 500)
        }
    } else {
        const input = inputBusqueda.value
        if (input) {
            input.style.border = '2px solid #DC2626'
            setTimeout(() => { input.style.border = '2px solid #E2E8F0' }, 800)
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
    if (item.cantidad < prod.stock) item.cantidad++
}

const decrementar = (i) => {
    if (carrito.value[i].cantidad > 1) carrito.value[i].cantidad--
    else carrito.value.splice(i, 1)
}

const cobrar = () => {
    if (!carrito.value.length || procesando.value) return
    procesando.value = true

    router.post('/minimarket/pos', {
        items:              carrito.value,
        metodo_pago:        metodoPago.value,
        total:              total.value,
        monto_pagado:       montoPagado.value || null,
        tipo_comprobante:   tipoComprobante.value,
        cliente_dni:        clienteDni.value,
        cliente_razon_social: clienteRazonSocial.value,
        cliente_email:      clienteEmail.value,
    }, {
        onSuccess: () => {
            carrito.value        = []
            montoPagado.value    = ''
            procesando.value     = false
            tipoComprobante.value = 'ninguno'
            clienteDni.value     = ''
            clienteRazonSocial.value = ''
            clienteEmail.value   = ''
        },
        onError: () => { procesando.value = false }
    })
}
</script>