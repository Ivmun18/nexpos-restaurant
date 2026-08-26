<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'
import { imprimirComandaIP, listarImpresorasQZ, imprimirComandaPrinter } from '@/qz-helper'

const page = usePage()
const props = defineProps({
    mesa:            Object,
    categorias:      Array,
    pedidosAbiertos: Array,
    siguienteRonda:  Number,
    impresoraCocinaIp: String,
})

const categoriaActiva = ref(props.categorias[0]?.id ?? null)
const carrito         = ref([])

// Modificadores por categoría (ej: "Sin ají", "Con chaufa")
const todosModificadores       = ref([])
const mostrarModalModificadores = ref(false)
const productoPendiente        = ref(null)
const modificadoresParaElegir  = ref([])
const modificadoresSeleccionados = ref([])

async function cargarModificadores() {
    try {
        const { data } = await axios.get('/api/modificadores')
        todosModificadores.value = data
    } catch (e) {
        // Silencioso: si falla, el POS sigue funcionando sin modificadores.
    }
}

// Variantes de combo (ej: elegir Acompañamiento y Bebida, obligatorio)
const mostrarModalVariantes   = ref(false)
const gruposVariantes         = ref([])
const variantesSeleccionadas  = ref({}) // grupo_id -> nombre de opción
const notaVariante            = ref('')

async function obtenerVariantes(productoId) {
    try {
        const { data } = await axios.get('/api/producto-variantes', { params: { producto_id: productoId } })
        return data
    } catch (e) {
        return []
    }
}

const variantesCompletas = computed(() =>
    gruposVariantes.value
        .filter(g => g.requerido)
        .every(g => !!variantesSeleccionadas.value[g.id])
)

function seleccionarVariante(grupoId, opcionNombre) {
    variantesSeleccionadas.value = { ...variantesSeleccionadas.value, [grupoId]: opcionNombre }
}

const productosGridWrapRef = ref(null)

// En móvil, categorías y productos son dos vistas separadas (no dos paneles superpuestos)
const showCategorias = ref(true)

function seleccionarCategoria(catId) {
    categoriaActiva.value = catId
    busqueda.value = ''
    showCategorias.value = false
    if (productosGridWrapRef.value) {
        productosGridWrapRef.value.scrollTop = 0
    }
}
const horaActual = new Date().toLocaleString('es-PE', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' })

const windowWidth = ref(window.innerWidth)
const onResize = () => { windowWidth.value = window.innerWidth }
onMounted(() => { window.addEventListener('resize', onResize); cargarModificadores() })
onUnmounted(() => window.removeEventListener('resize', onResize))

const isMobile  = computed(() => windowWidth.value < 768)
const isTablet  = computed(() => windowWidth.value >= 768 && windowWidth.value < 1100)
const isDesktop = computed(() => windowWidth.value >= 1100)

// En móvil y tablet usamos tabs
const tabMovil = ref('carta')
const isMobilePOS = computed(() => windowWidth.value < 1100)

// En celular (<768px) el pedido es un drawer fijo abajo en vez de un tab
const drawerAbierto = ref(false)

// Ancho del panel carta según pantalla
const cartaWidth = computed(() => {
    if (isMobile.value)  return '100%'
    if (isTablet.value)  return '55%'
    return '60%'
})
const pedidoWidth = computed(() => {
    if (isMobile.value)  return '100%'
    if (isTablet.value)  return '45%'
    return '40%'
})

function imprimirComanda() {
    if (!props.pedidosAbiertos || props.pedidosAbiertos.length === 0) {
        alert('No hay pedidos enviados para imprimir.')
        return
    }
    window.print()
}

function anularPlato(det) {
    if (det.anulado) return
    const motivo = window.prompt(`Anular "${det.nombre_producto}". Escribe el motivo:`)
    if (motivo === null) return
    if (!motivo.trim()) { alert('Debes escribir un motivo para anular.'); return }
    router.post(`/pos/detalle/${det.id}/anular`, { motivo: motivo.trim() }, { preserveScroll: true })
}

const notasPedido = ref('')
const busqueda    = ref('')

const categoriaSeleccionada = computed(() =>
    props.categorias.find(c => c.id === categoriaActiva.value)
)

const buscando = computed(() => busqueda.value.trim().length > 0)

const todosLosProductosActivos = computed(() =>
    props.categorias.flatMap(cat => cat.productos_activos || [])
)

const productosFiltrados = computed(() => {
    if (buscando.value) {
        const q = busqueda.value.toLowerCase()
        return todosLosProductosActivos.value.filter(p => p.nombre.toLowerCase().includes(q))
    }
    if (!categoriaSeleccionada.value) return []
    return categoriaSeleccionada.value.productos_activos
})

const puedeCobar = computed(() => {
    const rol = page.props.auth?.user?.rol
    return rol === 'admin' || rol === 'cajero' || rol === 'superadmin'
})

const totalCarrito = computed(() =>
    carrito.value.reduce((sum, item) => sum + item.subtotal, 0)
)

const totalGeneral = computed(() => {
    const totalPedidos = props.pedidosAbiertos.reduce((sum, p) => sum + Number(p.total), 0)
    return totalPedidos + totalCarrito.value
})

function modificadoresDeCategoria(prod) {
    return todosModificadores.value.filter(m =>
        m.categoria_id === prod.menu_categoria_id || m.categoria_id === null
    )
}

function agregarAlCarrito(prod, modificadores, variantes = [], notaVarianteTexto = '') {
    const existente = carrito.value.find(i =>
        i.menu_producto_id === prod.id &&
        JSON.stringify(i.modificadores || []) === JSON.stringify(modificadores) &&
        JSON.stringify(i.variantes || []) === JSON.stringify(variantes) &&
        (i.nota_variante || '') === notaVarianteTexto
    )
    if (existente) {
        existente.cantidad++
        existente.subtotal = existente.cantidad * existente.precio_unitario
    } else {
        carrito.value.push({
            menu_producto_id: prod.id,
            nombre_producto:  prod.nombre,
            cantidad:         1,
            precio_unitario:  Number(prod.precio),
            subtotal:         Number(prod.precio),
            notas:            '',
            modificadores:    modificadores,
            variantes:        variantes,
            nota_variante:    notaVarianteTexto || null,
        })
    }
}

async function agregarProducto(prod) {
    const grupos = await obtenerVariantes(prod.id)
    console.log('VARIANTES producto', prod.id, prod.nombre, grupos)
    if (grupos.length) {
        productoPendiente.value = prod
        gruposVariantes.value = grupos
        variantesSeleccionadas.value = {}
        notaVariante.value = ''
        mostrarModalVariantes.value = true
        return
    }
    continuarConModificadores(prod)
}

function continuarConModificadores(prod) {
    const mods = modificadoresDeCategoria(prod)
    if (mods.length) {
        productoPendiente.value = prod
        modificadoresParaElegir.value = mods
        modificadoresSeleccionados.value = []
        mostrarModalModificadores.value = true
        return
    }
    finalizarAgregado(prod, [])
}

function confirmarVariantes() {
    if (!variantesCompletas.value) return
    mostrarModalVariantes.value = false
    continuarConModificadores(productoPendiente.value)
}

function cancelarModalVariantes() {
    mostrarModalVariantes.value = false
    productoPendiente.value = null
    gruposVariantes.value = []
    variantesSeleccionadas.value = {}
    notaVariante.value = ''
}

function confirmarModificadores() {
    finalizarAgregado(productoPendiente.value, [...modificadoresSeleccionados.value])
}

function cancelarModalModificadores() {
    mostrarModalModificadores.value = false
    productoPendiente.value = null
    gruposVariantes.value = []
    variantesSeleccionadas.value = {}
    notaVariante.value = ''
}

function finalizarAgregado(prod, modificadores) {
    const variantes = gruposVariantes.value
        .map(g => ({ grupo: g.nombre, opcion: variantesSeleccionadas.value[g.id] }))
        .filter(v => !!v.opcion)

    agregarAlCarrito(prod, modificadores, variantes, notaVariante.value.trim())

    mostrarModalModificadores.value = false
    productoPendiente.value = null
    gruposVariantes.value = []
    variantesSeleccionadas.value = {}
    notaVariante.value = ''
}

function toggleModificador(nombre) {
    const i = modificadoresSeleccionados.value.indexOf(nombre)
    if (i === -1) {
        modificadoresSeleccionados.value.push(nombre)
    } else {
        modificadoresSeleccionados.value.splice(i, 1)
    }
}

function incrementarItem(index) {
    carrito.value[index].cantidad++
    carrito.value[index].subtotal = carrito.value[index].cantidad * carrito.value[index].precio_unitario
}

function quitarProducto(index) {
    if (carrito.value[index].cantidad > 1) {
        carrito.value[index].cantidad--
        carrito.value[index].subtotal = carrito.value[index].cantidad * carrito.value[index].precio_unitario
    } else {
        carrito.value.splice(index, 1)
    }
}

function eliminarItem(index) {
    carrito.value.splice(index, 1)
}

const enviando = ref(false)
const mostrarConfirmacion = ref(false)

function abrirConfirmacion() {
    if (!carrito.value.length) return
    mostrarConfirmacion.value = true
}

function cerrarConfirmacion() {
    mostrarConfirmacion.value = false
}

function construirComandaESC(mesaNumero, items, notas) {
    const ESC = String.fromCharCode(27)
    const GS  = String.fromCharCode(29)
    const sep = '-'.repeat(32) + String.fromCharCode(10)
    const lineas = items.map(i => {
        const nombre = (i.nombre_producto || '').substring(0, 28)
        return ESC + String.fromCharCode(33, 16) +
            i.cantidad + 'x ' + nombre + String.fromCharCode(10) +
            ESC + String.fromCharCode(33, 0)
    }).join('')
    return [
        ESC + String.fromCharCode(64),
        ESC + String.fromCharCode(97, 1),
        ESC + String.fromCharCode(33, 48),
        'COMANDA' + String.fromCharCode(10),
        ESC + String.fromCharCode(33, 16),
        'Mesa ' + mesaNumero + String.fromCharCode(10),
        ESC + String.fromCharCode(33, 0),
        ESC + String.fromCharCode(97, 0),
        sep,
        lineas,
        sep,
        notas ? ('Notas: ' + notas + String.fromCharCode(10)) : '',
        String.fromCharCode(10, 10, 10),
        GS + String.fromCharCode(86, 66, 0),
    ].join('')
}

const mostrarModalImpresoras = ref(false)
const listaImpresoras        = ref([])
const comandaParaImprimir    = ref('')
const buscandoImpresoras     = ref(false)

const IMPRESORA_STORAGE_KEY = 'pos_impresora_comanda'

function abrirSelectorImpresora(contenido) {
    comandaParaImprimir.value = contenido
    listaImpresoras.value = []
    buscandoImpresoras.value = true
    mostrarModalImpresoras.value = true
    listarImpresorasQZ().then(impresoras => {
        buscandoImpresoras.value = false
        listaImpresoras.value = impresoras
        const guardada = localStorage.getItem(IMPRESORA_STORAGE_KEY)
        if (guardada && impresoras.includes(guardada)) {
            mostrarModalImpresoras.value = false
            imprimirComandaPrinter(guardada, contenido)
        }
    })
}

function elegirImpresora(nombre) {
    mostrarModalImpresoras.value = false
    localStorage.setItem(IMPRESORA_STORAGE_KEY, nombre)
    imprimirComandaPrinter(nombre, comandaParaImprimir.value)
    enviarPedidoAlServidor()
}

function enviarACocina() {
    if (enviando.value) return
    if (!carrito.value.length) return
    if (props.impresoraCocinaIp) {
        abrirSelectorImpresora(construirComandaESC(props.mesa.numero, carrito.value, notasPedido.value))
        return
    }
    enviarPedidoAlServidor()
}

function enviarPedidoAlServidor() {
    enviando.value = true
    const form = useForm({ items: carrito.value, notas: notasPedido.value })
    form.post(`/pos/${props.mesa.id}`, {
        onSuccess: () => {
            mostrarConfirmacion.value = false
            setTimeout(() => { enviando.value = false }, 3000)
        },
        onError: () => {
            setTimeout(() => { enviando.value = false }, 3000)
        },
    })
}

function cerrarMesa() {
    window.location.href = `/caja-restaurante/${props.mesa.id}`
}
</script>

<template>
    <AppLayout :title="`🍽️ Mesa ${mesa.numero} · POS`">

        <!-- ══ TABS (solo tablet: el celular usa el drawer fijo de abajo) ══ -->
        <div v-if="isMobilePOS && !isMobile" class="pos-tabs">
            <button @click="tabMovil='carta'" :class="['pos-tab', tabMovil==='carta' ? 'pos-tab--active' : '']">
                🍽️ Carta
            </button>
            <button @click="tabMovil='pedido'" :class="['pos-tab', tabMovil==='pedido' ? 'pos-tab--active' : '']">
                🛒 Pedido
                <span v-if="carrito.length" class="pos-tab-badge">{{ carrito.length }}</span>
            </button>
        </div>

        <!-- ══ CONTENEDOR PRINCIPAL ══ -->
        <div class="pos-container" :class="{ 'pos-container--mobile': isMobilePOS }">

            <!-- ══ PANEL CARTA ══ -->
            <div class="panel-carta"
                :class="{ 'panel--hidden': isMobilePOS && !isMobile && tabMovil === 'pedido', 'panel-carta--movil': isMobile }"
                :style="{ width: isDesktop ? cartaWidth : undefined }">

                <!-- Búsqueda -->
                <div class="panel-search">
                    <input v-model="busqueda" type="text" placeholder="🔍 Buscar producto..." class="search-input" />
                </div>

                <!-- Categorías (en móvil: vista propia, oculta al ver productos o al buscar) -->
                <div v-if="!isMobile || (showCategorias && !buscando)" class="categorias-bar">
                    <button
                        v-for="cat in categorias" :key="cat.id"
                        @click="seleccionarCategoria(cat.id)"
                        class="cat-btn"
                        :class="{ 'cat-btn--active': categoriaActiva === cat.id }"
                        :style="categoriaActiva === cat.id ? { background: cat.color || '#14B8A6' } : {}"
                    >
                        <span class="cat-icon">{{ cat.icono }}</span>
                        <span class="cat-nombre">{{ cat.nombre }}</span>
                    </button>
                </div>

                <!-- Volver a categorías (solo móvil, viendo productos, sin búsqueda activa) -->
                <div v-if="isMobile && !showCategorias && !buscando" class="volver-categorias-wrap">
                    <button @click="showCategorias = true" class="volver-categorias-btn">← Categorías</button>
                </div>

                <!-- Grid productos (en móvil: si ya se eligió categoría o si hay búsqueda activa) -->
                <div v-if="!isMobile || !showCategorias || buscando" class="productos-grid-wrap" ref="productosGridWrapRef">
                    <div class="productos-grid">
                        <button
                            v-for="prod in productosFiltrados" :key="prod.id"
                            @click="agregarProducto(prod)"
                            class="prod-card"
                            :class="{ 'prod-card--disabled': !prod.disponible }"
                        >
                            <p class="prod-nombre">{{ prod.nombre }}</p>
                            <p v-if="prod.descripcion" class="prod-desc">{{ prod.descripcion }}</p>
                            <div class="prod-footer">
                                <div>
                                    <span class="prod-precio">S/ {{ Number(prod.precio).toFixed(2) }}</span>
                                    <span class="prod-tiempo">⏱ {{ prod.tiempo_preparacion }}min</span>
                                </div>
                                <span class="prod-add-btn" @click.stop="agregarProducto(prod)" aria-label="Agregar">+</span>
                            </div>
                        </button>
                    </div>
                    <div v-if="!productosFiltrados.length" class="productos-vacio">
                        <p style="font-size:40px; margin:0 0 10px;">🍽️</p>
                        <p>{{ buscando ? `Sin resultados para "${busqueda}"` : 'Sin productos en esta categoría' }}</p>
                    </div>
                </div>
            </div>

            <!-- ══ PANEL PEDIDO ══ -->
            <div class="panel-pedido"
                :class="{
                    'panel--hidden': isMobilePOS && !isMobile && tabMovil === 'carta',
                    'drawer-movil': isMobile,
                    'drawer-movil--abierto': isMobile && drawerAbierto,
                }"
                :style="{ width: isDesktop ? pedidoWidth : undefined }">

                <!-- Barra fija del drawer (solo celular): resumen + enviar, siempre visible -->
                <div v-if="isMobile" class="drawer-handle" @click="drawerAbierto = !drawerAbierto">
                    <div class="drawer-handle-bar"></div>
                    <div class="drawer-resumen">
                        <span>🛒 {{ carrito.length }} item{{ carrito.length !== 1 ? 's' : '' }} · Total mesa <strong>S/ {{ totalGeneral.toFixed(2) }}</strong></span>
                        <span class="drawer-chevron">{{ drawerAbierto ? '⌄' : '⌃' }}</span>
                    </div>
                    <button
                        @click.stop="abrirConfirmacion"
                        :disabled="!carrito.length || enviando"
                        class="drawer-enviar-btn"
                        :class="{ 'enviar-btn--disabled': !carrito.length }"
                    >
                        {{ enviando ? '⏳ Enviando...' : '🍳 ENVIAR A COCINA' }}
                    </button>
                </div>

                <!-- Header mesa -->
                <div class="mesa-header">
                    <div>
                        <p class="mesa-titulo">🪑 Mesa {{ mesa.numero }}</p>
                        <p class="mesa-sub">Ronda {{ siguienteRonda }} · {{ carrito.length }} productos</p>
                    </div>
                    <button v-if="$page.props.auth.user.rol !== 'mozo'" @click="cerrarMesa" class="cobrar-btn">
                        💳 Cobrar S/ {{ totalGeneral.toFixed(2) }}
                    </button>
                </div>

                <!-- Rondas anteriores -->
                <div v-if="pedidosAbiertos.length" class="rondas-card">
                    <div class="rondas-header">
                        <p class="rondas-titulo">Rondas anteriores</p>
                        <button @click="imprimirComanda" class="comanda-btn">🖨️ Comanda</button>
                    </div>
                    <div v-for="pedido in pedidosAbiertos" :key="pedido.id" class="ronda-grupo">
                        <p class="ronda-num">Ronda {{ pedido.numero_ronda }}</p>
                        <div v-for="det in pedido.detalles" :key="det.id" class="ronda-item">
                            <span class="ronda-item-nombre" :class="{ 'ronda-item--anulado': det.anulado }">
                                {{ det.cantidad }}× {{ det.nombre_producto }}
                                <span v-if="det.anulado" class="anulado-label">(anulado: {{ det.motivo_anulacion }})</span>
                                <span v-if="det.variantes?.length" class="ronda-item-variantes">{{ det.variantes.map(v => v.opcion).join(' · ') }}</span>
                                <span v-if="det.nota_variante" class="ronda-item-nota-variante">📝 {{ det.nota_variante }}</span>
                                <span v-if="det.modificadores?.length" class="ronda-item-mods">⚠ {{ det.modificadores.join(' · ') }}</span>
                            </span>
                            <span class="ronda-item-precio" :class="{ 'ronda-item--anulado': det.anulado }">
                                S/ {{ Number(det.subtotal).toFixed(2) }}
                            </span>
                            <button v-if="!det.anulado && !det.pagado" @click="anularPlato(det)" class="anular-btn">✕</button>
                        </div>
                    </div>
                </div>

                <!-- Carrito -->
                <div class="carrito-card">
                    <div class="carrito-header">
                        <p class="carrito-titulo">
                            🛒 Esta ronda
                            <span v-if="carrito.length" class="carrito-count">({{ carrito.length }} items)</span>
                        </p>
                    </div>

                    <div class="carrito-items">
                        <div v-if="!carrito.length" class="carrito-vacio">
                            <p style="font-size:36px; margin:0 0 8px;">🛒</p>
                            <p>Toca un producto para agregarlo</p>
                        </div>

                        <div v-for="(item, i) in carrito" :key="i" class="carrito-item">
                            <div class="carrito-item-info">
                                <p class="carrito-item-nombre">{{ item.nombre_producto }}</p>
                                <p v-if="item.variantes?.length" class="carrito-item-variantes">{{ item.variantes.map(v => v.opcion).join(' · ') }}</p>
                                <p v-if="item.nota_variante" class="carrito-item-nota-variante">📝 {{ item.nota_variante }}</p>
                                <p v-if="item.modificadores?.length" class="carrito-item-mods">⚠ {{ item.modificadores.join(' · ') }}</p>
                                <p class="carrito-item-precio">S/ {{ item.precio_unitario.toFixed(2) }} c/u</p>
                            </div>
                            <div class="cantidad-ctrl">
                                <button @click="quitarProducto(i)" class="qty-btn">−</button>
                                <span class="qty-num">{{ item.cantidad }}</span>
                                <button @click="incrementarItem(i)" class="qty-btn qty-btn--add">+</button>
                            </div>
                            <span class="carrito-item-subtotal">S/ {{ item.subtotal.toFixed(2) }}</span>
                            <button @click="eliminarItem(i)" class="eliminar-btn">✕</button>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="notas-wrap">
                        <input v-model="notasPedido" type="text" placeholder="📝 Notas para cocina..." class="notas-input" />
                    </div>

                    <!-- Totales -->
                    <div class="totales">
                        <div class="total-row">
                            <span>Esta ronda</span>
                            <span>S/ {{ totalCarrito.toFixed(2) }}</span>
                        </div>
                        <div class="total-row total-row--grande">
                            <span>Total mesa</span>
                            <span class="total-valor">S/ {{ totalGeneral.toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Enviar (en celular ya está el botón fijo del drawer arriba) -->
                    <div v-if="!isMobile" class="enviar-wrap">
                        <button @click="abrirConfirmacion" :disabled="!carrito.length || enviando" class="enviar-btn" :class="{ 'enviar-btn--disabled': !carrito.length }">
                            {{ enviando ? '⏳ Enviando...' : '🍳 Enviar a cocina' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón flotante carta → pedido (solo tablet: el celular usa el drawer fijo) -->
        <div v-if="isMobilePOS && !isMobile && carrito.length && tabMovil === 'carta'" class="fab-pedido">
            <button @click="tabMovil = 'pedido'" class="fab-btn">
                🛒 Ver pedido ({{ carrito.length }}) — S/ {{ totalCarrito.toFixed(2) }}
            </button>
        </div>

        <!-- Botones flotantes en tab Pedido (solo tablet) -->
        <div v-if="isMobilePOS && !isMobile && tabMovil === 'pedido'" class="fab-pedido-btns">
            <button @click="tabMovil = 'carta'" class="fab-volver">← Carta</button>
            <button v-if="puedeCobar" @click="cerrarMesa" class="fab-cobrar">
                💳 Cobrar S/ {{ totalGeneral.toFixed(2) }}
            </button>
        </div>

        <!-- MODAL CONFIRMACIÓN DE PEDIDO -->
        <div v-if="mostrarConfirmacion" class="confirmacion-overlay" @click.self="cerrarConfirmacion">
            <div class="confirmacion-modal">
                <h3 class="confirmacion-titulo">🍳 Confirmar pedido</h3>
                <p class="confirmacion-sub">Mesa {{ mesa.numero }} · Ronda {{ siguienteRonda }}</p>

                <div class="confirmacion-items">
                    <div v-for="(item, i) in carrito" :key="i" class="confirmacion-item">
                        <span class="confirmacion-item-cant">{{ item.cantidad }}×</span>
                        <div class="confirmacion-item-nombre">
                            {{ item.nombre_producto }}
                            <p v-if="item.variantes?.length" class="confirmacion-item-variantes">{{ item.variantes.map(v => v.opcion).join(' · ') }}</p>
                            <p v-if="item.nota_variante" class="confirmacion-item-nota-variante">📝 {{ item.nota_variante }}</p>
                            <p v-if="item.modificadores?.length" class="confirmacion-item-mods">⚠ {{ item.modificadores.join(' · ') }}</p>
                        </div>
                        <span class="confirmacion-item-subtotal">S/ {{ item.subtotal.toFixed(2) }}</span>
                    </div>
                </div>

                <p v-if="notasPedido" class="confirmacion-notas">📝 {{ notasPedido }}</p>

                <div class="confirmacion-total">
                    <span>Total esta ronda</span>
                    <span>S/ {{ totalCarrito.toFixed(2) }}</span>
                </div>

                <div class="confirmacion-btns">
                    <button @click="cerrarConfirmacion" class="confirmacion-btn confirmacion-btn--modificar">✏️ Modificar</button>
                    <button @click="enviarACocina" :disabled="enviando" class="confirmacion-btn confirmacion-btn--confirmar">
                        {{ enviando ? '⏳ Enviando...' : '✅ Confirmar y enviar' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="mostrarModalImpresoras" class="confirmacion-overlay" @click.self="mostrarModalImpresoras=false">
            <div class="confirmacion-modal">
                <h3 class="confirmacion-titulo">🖨️ Elegir impresora</h3>
                <p class="confirmacion-sub">Comanda enviada · Mesa {{ mesa.numero }}</p>
                <div v-if="buscandoImpresoras" style="padding:16px 0; text-align:center; color:#64748B;">
                    Buscando impresoras…
                </div>
                <div v-else-if="!listaImpresoras.length" style="padding:16px 0; text-align:center; color:#64748B;">
                    No se encontraron impresoras. Verifica que QZ Tray esté corriendo.
                </div>
                <div v-for="nombre in listaImpresoras" :key="nombre"
                    @click="elegirImpresora(nombre)"
                    style="padding:12px 16px; margin-bottom:8px; background:#F1F5F9; border-radius:10px; cursor:pointer; font-weight:600; color:#1E293B;">
                    {{ nombre }}
                </div>
                <button @click="mostrarModalImpresoras=false; enviarPedidoAlServidor()" class="confirmacion-btn confirmacion-btn--modificar" style="width:100%; margin-top:8px;">
                    Cancelar
                </button>
            </div>
        </div>

        <!-- MODAL VARIANTES DE COMBO (elección obligatoria) -->
        <Transition name="mods-sheet">
            <div v-if="mostrarModalVariantes" class="mods-overlay" @click.self="cancelarModalVariantes">
                <div class="mods-modal">
                    <div class="mods-header">
                        <div class="mods-header-text">
                            <p class="mods-titulo">{{ productoPendiente?.nombre }}</p>
                            <p class="mods-sub">Elige las opciones de tu combo</p>
                        </div>
                        <button @click="cancelarModalVariantes" class="mods-cerrar" aria-label="Cerrar">✕</button>
                    </div>

                    <div class="mods-lista">
                        <div v-for="g in gruposVariantes" :key="g.id" class="variantes-grupo">
                            <p class="variantes-grupo-titulo">
                                {{ g.nombre }}
                                <span v-if="g.requerido" class="variantes-requerido">Obligatorio</span>
                            </p>
                            <div
                                v-for="o in g.opciones" :key="o.id"
                                @click="seleccionarVariante(g.id, o.nombre)"
                                class="variantes-opcion"
                                :class="{ 'variantes-opcion--activa': variantesSeleccionadas[g.id] === o.nombre }"
                            >
                                <span class="variantes-opcion-radio" :class="{ 'variantes-opcion-radio--activo': variantesSeleccionadas[g.id] === o.nombre }"></span>
                                <span class="variantes-opcion-nombre">{{ o.nombre }}</span>
                            </div>
                        </div>

                        <div class="variantes-nota">
                            <p class="variantes-nota-titulo">¿Algún cambio en el combo?</p>
                            <textarea
                                v-model="notaVariante"
                                rows="2"
                                placeholder="Ej: en vez de ceviche → leche de tigre"
                                class="variantes-nota-textarea"
                            ></textarea>
                        </div>
                    </div>

                    <div class="mods-footer">
                        <button @click="confirmarVariantes" :disabled="!variantesCompletas" class="mods-confirmar" :class="{ 'mods-confirmar--disabled': !variantesCompletas }">
                            Agregar al pedido
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- MODAL MODIFICADORES -->
        <Transition name="mods-sheet">
            <div v-if="mostrarModalModificadores" class="mods-overlay" @click.self="cancelarModalModificadores">
                <div class="mods-modal">
                    <div class="mods-header">
                        <div class="mods-header-text">
                            <p class="mods-titulo">{{ productoPendiente?.nombre }}</p>
                            <p class="mods-sub">¿Alguna indicación?</p>
                        </div>
                        <button @click="cancelarModalModificadores" class="mods-cerrar" aria-label="Cerrar">✕</button>
                    </div>

                    <div class="mods-lista">
                        <div
                            v-for="m in modificadoresParaElegir" :key="m.id"
                            @click="toggleModificador(m.nombre)"
                            class="mods-opcion"
                            :class="{ 'mods-opcion--activa': modificadoresSeleccionados.includes(m.nombre) }"
                        >
                            <span class="mods-opcion-nombre">{{ m.nombre }}</span>
                            <span class="mods-opcion-check" :class="{ 'mods-opcion-check--activo': modificadoresSeleccionados.includes(m.nombre) }">
                                <svg v-if="modificadoresSeleccionados.includes(m.nombre)" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="mods-footer">
                        <button @click="confirmarModificadores" class="mods-confirmar">
                            Agregar al pedido<span v-if="modificadoresSeleccionados.length"> ({{ modificadoresSeleccionados.length }})</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- COMANDA IMPRIMIBLE -->
        <div id="comanda-print" class="comanda-print">
            <div style="text-align:center; border-bottom:1px dashed #000; padding-bottom:6px; margin-bottom:8px;">
                <div style="font-size:18px; font-weight:bold;">COMANDA</div>
                <div style="font-size:14px;">Mesa {{ mesa.numero }}</div>
                <div style="font-size:11px;">{{ horaActual }}</div>
            </div>
            <div v-for="pedido in pedidosAbiertos" :key="'pc'+pedido.id">
                <div style="font-size:11px; font-weight:bold; margin:6px 0 2px;">Ronda {{ pedido.numero_ronda }}</div>
                <div v-for="det in pedido.detalles.filter(d => !d.anulado)" :key="'dc'+det.id" style="font-size:13px; margin:3px 0;">
                    <span style="font-weight:bold;">{{ det.cantidad }}x</span> {{ det.nombre_producto }}
                    <div v-if="det.variantes?.length" style="font-size:11px; padding-left:14px; font-weight:bold;">🍽 {{ det.variantes.map(v => v.opcion).join(' · ') }}</div>
                    <div v-if="det.nota_variante" style="font-size:11px; padding-left:14px; font-style:italic;">📝 {{ det.nota_variante }}</div>
                    <div v-if="det.modificadores?.length" style="font-size:11px; padding-left:14px; font-weight:bold;">⚠ {{ det.modificadores.join(' · ') }}</div>
                    <div v-if="det.notas" style="font-size:11px; padding-left:14px; font-style:italic;">▸ {{ det.notas }}</div>
                </div>
            </div>
            <div style="border-top:1px dashed #000; margin-top:8px; padding-top:6px; text-align:center; font-size:10px;">- - - cocina - - -</div>
        </div>

    </AppLayout>
</template>

<style scoped>
/* ══ LAYOUT PRINCIPAL ══ */
.pos-tabs {
    display: flex;
    gap: 0;
    margin-bottom: 10px;
    background: white;
    border-radius: 12px;
    padding: 4px;
    border: 1px solid #E2E8F0;
}
.pos-tab {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    border: none;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    background: transparent;
    color: #64748B;
    position: relative;
    transition: all 0.15s;
}
.pos-tab--active {
    background: linear-gradient(135deg, #14B8A6, #0F766E);
    color: white;
}
.pos-tab-badge {
    position: absolute;
    top: 4px;
    right: 8px;
    background: #EF4444;
    color: white;
    border-radius: 999px;
    font-size: 11px;
    padding: 1px 6px;
    font-weight: 700;
}

.pos-container {
    display: flex;
    flex-direction: row;
    height: calc(100vh - 110px);
    gap: 12px;
    overflow: hidden;
}
.pos-container--mobile {
    flex-direction: column;
    height: calc(100vh - 160px);
}

.panel--hidden { display: none !important; }

/* ══ PANEL CARTA ══ */
.panel-carta {
    display: flex;
    flex-direction: column;
    flex: 1;
    background: white;
    border-radius: 20px;
    border: 1px solid #E2E8F0;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    min-width: 0;
}
.panel-search {
    padding: 12px 14px;
    border-bottom: 1px solid #F0F4F8;
    background: #FAFBFC;
}
.search-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    font-size: 15px;
    outline: none;
    box-sizing: border-box;
    background: white;
}
.categorias-bar {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
    padding: 10px 12px;
    border-bottom: 1px solid #F0F4F8;
    background: #FAFBFC;
}
@media (min-width: 480px) {
    .categorias-bar { grid-template-columns: repeat(4, 1fr); }
}
@media (min-width: 1100px) {
    .categorias-bar {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        scrollbar-width: none;
    }
}
.categorias-bar::-webkit-scrollbar { display: none; }
.cat-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 8px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    white-space: nowrap;
    background: #F1F5F9;
    color: #64748B;
    transition: all 0.15s;
    width: 100%;
}
.cat-btn--active { color: white; transform: scale(1.04); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.cat-icon { font-size: 18px; }
.cat-nombre { font-size: 13px; }

.productos-grid-wrap {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
}
.productos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 10px;
}
@media (min-width: 1100px) {
    .productos-grid { grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); }
}
@media (min-width: 1400px) {
    .productos-grid { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
}
.prod-card {
    background: white;
    border: 2px solid #E2E8F0;
    border-radius: 14px;
    padding: 14px;
    text-align: left;
    cursor: pointer;
    transition: all 0.15s;
    display: flex;
    flex-direction: column;
}
.prod-card:hover { border-color: #14B8A6; background: #F0FDFA; transform: scale(1.02); }
.prod-card:active { transform: scale(0.97); }
.prod-card--disabled { opacity: 0.4; pointer-events: none; }
.prod-nombre { font-size: 15px; font-weight: 700; color: #1E293B; margin: 0 0 4px; line-height: 1.3; }
.prod-desc { font-size: 12px; color: #94A3B8; margin: 0 0 10px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.prod-footer { display: flex; align-items: center; justify-content: space-between; margin-top: auto; gap: 8px; }
.prod-precio { font-size: 18px; font-weight: 800; color: #14B8A6; margin-right: 8px; }
.prod-tiempo { font-size: 12px; color: #CBD5E1; background: #F8FAFC; padding: 3px 8px; border-radius: 6px; }
.prod-add-btn {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #14B8A6;
    color: white;
    font-size: 18px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
.productos-vacio { text-align: center; padding: 50px 0; color: #CBD5E1; font-size: 16px; }

.volver-categorias-wrap { padding: 10px 12px; border-bottom: 1px solid #F0F4F8; background: #FAFBFC; }
.volver-categorias-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    min-height: 44px;
    background: #F1F5F9;
    color: #0F766E;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
}

/* ══ MÓVIL (<768px): categorías 2x2, cards grandes, botón + táctil ══ */
@media (max-width: 767px) {
    /* Sin tabs arriba en celular (los tabs solo se muestran en tablet) */
    .pos-container--mobile { height: calc(100vh - 80px); }

    .categorias-bar { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .cat-btn { min-height: 64px; padding: 12px; border-radius: 14px; }
    .cat-icon { font-size: 24px; }
    .cat-nombre { font-size: 15px; }

    .productos-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .prod-card { padding: 16px; border-radius: 16px; min-height: 96px; }
    .prod-nombre { font-size: 16px; }
    .prod-precio { font-size: 20px; }
    .prod-add-btn { width: 48px; height: 48px; font-size: 24px; }

    /* Espacio para que el drawer fijo (148px) no tape la ultima fila de productos */
    .panel-carta--movil .productos-grid-wrap { padding-bottom: 200px; }
}

/* ══ PANEL PEDIDO ══ */
.panel-pedido {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-height: 0;
    overflow-y: auto;
    flex-shrink: 0;
}
@media (max-width: 1099px) {
    .panel-pedido { flex: 1; overflow-y: auto; }
}

/* ══ DRAWER FIJO DE PEDIDO (celular, <768px) ══ */
@media (max-width: 767px) {
    .drawer-movil {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 300;
        background: white;
        border-radius: 20px 20px 0 0;
        box-shadow: 0 -8px 30px rgba(0,0,0,0.18);
        max-height: 148px;
        overflow: hidden;
        transition: max-height 0.25s ease;
        flex: none;
    }
    .drawer-movil--abierto {
        max-height: 88vh;
        overflow-y: auto;
    }
}

.drawer-handle {
    flex-shrink: 0;
    padding: 8px 16px 14px;
    cursor: pointer;
    background: white;
    border-bottom: 1px solid #F0F4F8;
}
.drawer-handle-bar {
    width: 40px;
    height: 4px;
    background: #E2E8F0;
    border-radius: 999px;
    margin: 0 auto 10px;
}
.drawer-resumen {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 14px;
    color: #64748B;
    font-weight: 600;
    margin-bottom: 10px;
}
.drawer-resumen strong { color: #1E293B; }
.drawer-chevron { color: #94A3B8; font-size: 16px; flex-shrink: 0; margin-left: 8px; }
.drawer-enviar-btn {
    width: 100%;
    min-height: 52px;
    background: linear-gradient(135deg, #22C55E, #16A34A);
    color: white;
    border: none;
    border-radius: 14px;
    font-size: 17px;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(34,197,94,0.4);
}

.mesa-header {
    background: linear-gradient(135deg, #14B8A6, #0F766E);
    border-radius: 16px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 20px rgba(20,184,166,0.3);
    flex-shrink: 0;
}
.mesa-titulo { font-size: 22px; font-weight: 800; color: white; margin: 0; }
.mesa-sub { font-size: 14px; color: rgba(255,255,255,0.8); margin: 2px 0 0; }
.cobrar-btn {
    background: rgba(255,255,255,0.2);
    color: white;
    border: 2px solid rgba(255,255,255,0.4);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
}

.rondas-card {
    background: white;
    border-radius: 14px;
    border: 1px solid #E2E8F0;
    padding: 14px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    flex-shrink: 0;
}
.rondas-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.rondas-titulo { font-size: 12px; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 1px; margin: 0; }
.comanda-btn { background: #F1F5F9; color: #0F766E; border: 1px solid #14B8A6; border-radius: 8px; padding: 5px 10px; font-size: 12px; font-weight: 700; cursor: pointer; }
.ronda-grupo { margin-bottom: 8px; }
.ronda-num { font-size: 13px; font-weight: 700; color: #475569; margin: 0 0 4px; }
.ronda-item { display: flex; align-items: center; justify-content: space-between; font-size: 14px; padding: 3px 0; gap: 6px; }
.ronda-item-nombre { flex: 1; color: #64748B; }
.ronda-item-precio { font-weight: 600; color: #64748B; }
.ronda-item--anulado { color: #CBD5E1 !important; text-decoration: line-through; }
.anulado-label { font-size: 11px; color: #EF4444; font-style: italic; }
.anular-btn { width: 24px; height: 24px; flex-shrink: 0; background: #FEF2F2; color: #EF4444; border: 1px solid #FECACA; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; }

.carrito-card {
    flex: 1;
    background: white;
    border-radius: 16px;
    border: 1px solid #E2E8F0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    min-height: 280px;
}
.carrito-header { padding: 14px 18px; border-bottom: 1px solid #F0F4F8; background: #FAFBFC; flex-shrink: 0; }
.carrito-titulo { font-size: 15px; font-weight: 700; color: #1E293B; margin: 0; }
.carrito-count { color: #14B8A6; font-size: 13px; }
.carrito-items { flex: 1; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 8px; min-height: 80px; }
.carrito-vacio { text-align: center; padding: 30px 0; color: #CBD5E1; font-size: 15px; }
.carrito-item { display: flex; align-items: center; gap: 10px; background: #F8FAFC; border-radius: 12px; padding: 10px 12px; border: 1px solid #E2E8F0; }
.carrito-item-info { flex: 1; min-width: 0; }
.carrito-item-nombre { font-size: 14px; font-weight: 700; color: #1E293B; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.carrito-item-precio { font-size: 12px; color: #94A3B8; margin: 2px 0 0; }
.cantidad-ctrl { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
.qty-btn { width: 32px; height: 32px; background: #E2E8F0; border: none; border-radius: 8px; font-size: 18px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.qty-btn--add { background: #14B8A6; color: white; }
.qty-num { font-size: 17px; font-weight: 800; color: #1E293B; width: 24px; text-align: center; }
.carrito-item-subtotal { font-size: 15px; font-weight: 800; color: #14B8A6; width: 64px; text-align: right; flex-shrink: 0; }
.eliminar-btn { background: #FEE2E2; border: none; border-radius: 8px; width: 28px; height: 28px; color: #EF4444; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

.notas-wrap { padding: 0 14px 10px; flex-shrink: 0; }
.notas-input { width: 100%; padding: 10px 14px; border: 2px solid #E2E8F0; border-radius: 10px; font-size: 14px; outline: none; box-sizing: border-box; }

.totales { padding: 12px 18px; border-top: 2px solid #F0F4F8; background: #FAFBFC; flex-shrink: 0; }
.total-row { display: flex; justify-content: space-between; font-size: 14px; color: #64748B; margin-bottom: 4px; }
.total-row--grande { font-size: 20px; font-weight: 800; color: #1E293B; }
.total-valor { color: #14B8A6; }

.enviar-wrap { padding: 12px 14px; flex-shrink: 0; }
.enviar-btn {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #14B8A6, #0F766E);
    color: white;
    border: none;
    border-radius: 14px;
    font-size: 17px;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(20,184,166,0.4);
    transition: all 0.2s;
}
.enviar-btn--disabled { background: #E2E8F0; color: #94A3B8; box-shadow: none; cursor: not-allowed; }

/* ══ FLOTANTES MÓVIL/TABLET ══ */
.fab-pedido { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 200; }
.fab-btn {
    padding: 14px 24px;
    background: linear-gradient(135deg,#14B8A6,#0F766E);
    color: white; border: none; border-radius: 50px;
    font-size: 15px; font-weight: 700; cursor: pointer;
    box-shadow: 0 8px 24px rgba(20,184,166,0.5);
    white-space: nowrap;
}
.fab-pedido-btns { position: fixed; bottom: 20px; left: 16px; right: 16px; z-index: 200; display: flex; gap: 10px; }
.fab-volver { padding: 13px 18px; background: white; color: #0F766E; border: 2px solid #14B8A6; border-radius: 50px; font-size: 14px; font-weight: 700; cursor: pointer; flex-shrink: 0; }
.fab-cobrar { flex: 1; padding: 13px 18px; background: linear-gradient(135deg,#14B8A6,#0F766E); color: white; border: none; border-radius: 50px; font-size: 14px; font-weight: 700; cursor: pointer; }

/* ══ MODAL CONFIRMACIÓN DE PEDIDO ══ */
.confirmacion-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 500;
    padding: 16px;
}
.confirmacion-modal {
    background: white;
    border-radius: 20px;
    padding: 22px;
    width: 100%;
    max-width: 420px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.confirmacion-titulo { font-size: 20px; font-weight: 800; color: #1E293B; margin: 0 0 2px; }
.confirmacion-sub { font-size: 13px; color: #64748B; margin: 0 0 16px; }
.confirmacion-items { display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; max-height: 50vh; overflow-y: auto; }
.confirmacion-item { display: flex; align-items: center; gap: 8px; background: #F8FAFC; border-radius: 10px; padding: 10px 12px; border: 1px solid #E2E8F0; }
.confirmacion-item-cant { font-weight: 800; color: #14B8A6; flex-shrink: 0; }
.confirmacion-item-nombre { flex: 1; font-size: 14px; font-weight: 600; color: #1E293B; }
.confirmacion-item-subtotal { font-size: 14px; font-weight: 700; color: #1E293B; flex-shrink: 0; }
.confirmacion-notas { font-size: 13px; color: #92400E; background: #FEF3C7; border-radius: 8px; padding: 8px 12px; margin: 0 0 12px; }
.confirmacion-total { display: flex; justify-content: space-between; font-size: 18px; font-weight: 800; color: #1E293B; padding-top: 12px; border-top: 2px solid #F0F4F8; margin-bottom: 18px; }
.confirmacion-btns { display: flex; gap: 10px; }
.confirmacion-btn { flex: 1; min-height: 52px; border-radius: 14px; font-size: 15px; font-weight: 800; cursor: pointer; border: none; }
.confirmacion-btn--modificar { background: #F1F5F9; color: #475569; }
.confirmacion-btn--confirmar { background: linear-gradient(135deg, #22C55E, #16A34A); color: white; box-shadow: 0 4px 16px rgba(34,197,94,0.4); }
.confirmacion-btn--confirmar:disabled { opacity: 0.6; cursor: not-allowed; }

/* ══ MODIFICADORES (carrito / rondas / confirmación) ══ */
.carrito-item-mods { font-size: 12px; color: #92400E; font-weight: 700; margin: 2px 0 0; }
.ronda-item-mods { display: block; font-size: 11px; color: #92400E; font-weight: 700; }
.confirmacion-item-mods { font-size: 12px; color: #92400E; font-weight: 700; margin: 2px 0 0; }

/* ══ VARIANTES DE COMBO (carrito / rondas / confirmación) ══ */
.carrito-item-variantes { font-size: 12px; color: #94A3B8; margin: 2px 0 0; }
.ronda-item-variantes { display: block; font-size: 11px; color: #94A3B8; }
.confirmacion-item-variantes { font-size: 12px; color: #94A3B8; margin: 2px 0 0; }
.carrito-item-nota-variante { font-size: 12px; color: #B45309; font-weight: 700; margin: 2px 0 0; }
.ronda-item-nota-variante { display: block; font-size: 11px; color: #B45309; font-weight: 700; }
.confirmacion-item-nota-variante { font-size: 12px; color: #B45309; font-weight: 700; margin: 2px 0 0; }

/* ══ MODAL MODIFICADORES ══ */
.mods-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 550;
    padding: 16px;
    transition: opacity 0.3s ease;
}
.mods-modal {
    background: white;
    border-radius: 20px;
    width: 100%;
    max-width: 380px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    transition: transform 0.3s ease;
}

.mods-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    padding: 20px 20px 16px;
    flex-shrink: 0;
}
.mods-header-text { min-width: 0; }
.mods-titulo { font-size: 19px; font-weight: 800; color: #1E293B; margin: 0; line-height: 1.3; }
.mods-sub { font-size: 13px; color: #64748B; margin: 4px 0 0; }
.mods-cerrar {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #F1F5F9;
    color: #64748B;
    border: none;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mods-lista {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 0 20px 20px;
    overflow-y: auto;
    flex: 1;
}
.mods-opcion {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    min-height: 52px;
    box-sizing: border-box;
    background: #F8FAFC;
    border: 2px solid #E2E8F0;
    border-radius: 14px;
    padding: 14px 16px;
    font-size: 15px;
    font-weight: 700;
    color: #1E293B;
    cursor: pointer;
    transition: all 0.15s;
}
.mods-opcion--activa {
    background: #F0FDF4;
    border-color: #22C55E;
    color: #166534;
}
.mods-opcion-nombre { flex: 1; }
.mods-opcion-check {
    flex-shrink: 0;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    border: 2px solid #CBD5E1;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    transition: all 0.15s;
}
.mods-opcion-check--activo {
    background: #22C55E;
    border-color: #22C55E;
}

.mods-footer {
    flex-shrink: 0;
    padding: 14px 20px calc(14px + env(safe-area-inset-bottom, 0px));
    border-top: 1px solid #F0F4F8;
    background: white;
}
.mods-confirmar {
    width: 100%;
    min-height: 54px;
    background: linear-gradient(135deg, #14B8A6, #0F766E);
    color: white;
    border: none;
    border-radius: 14px;
    font-size: 16px;
    font-weight: 800;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(20,184,166,0.4);
}
.mods-confirmar--disabled {
    background: #E2E8F0;
    color: #94A3B8;
    box-shadow: none;
    cursor: not-allowed;
}

/* Transición slide-up + fade */
.mods-sheet-enter-active,
.mods-sheet-leave-active {
    transition: opacity 0.3s ease;
}
.mods-sheet-enter-from,
.mods-sheet-leave-to {
    opacity: 0;
}
.mods-sheet-enter-from .mods-modal,
.mods-sheet-leave-to .mods-modal {
    transform: translateY(40px);
}

/* ══ MOBILE (<768px): bottom sheet a pantalla completa ══ */
@media (max-width: 767px) {
    .mods-overlay {
        align-items: flex-end;
        padding: 0;
    }
    .mods-modal {
        max-width: 100%;
        width: 100%;
        height: 85vh;
        max-height: 85vh;
        border-radius: 24px 24px 0 0;
    }
    .mods-sheet-enter-from .mods-modal,
    .mods-sheet-leave-to .mods-modal {
        transform: translateY(100%);
    }
}

/* ══ MODAL VARIANTES DE COMBO ══ */
.variantes-grupo { margin-bottom: 18px; }
.variantes-grupo:last-child { margin-bottom: 0; }
.variantes-grupo-titulo {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 800;
    color: #1E293B;
    margin: 0 0 10px;
}
.variantes-requerido {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #9A3412;
    background: #FFEDD5;
    padding: 2px 8px;
    border-radius: 999px;
}
.variantes-opcion {
    display: flex;
    align-items: center;
    gap: 12px;
    min-height: 52px;
    box-sizing: border-box;
    background: #F8FAFC;
    border: 2px solid #E2E8F0;
    border-radius: 14px;
    padding: 14px 16px;
    font-size: 15px;
    font-weight: 700;
    color: #1E293B;
    cursor: pointer;
    transition: all 0.15s;
    margin-bottom: 8px;
}
.variantes-opcion:last-child { margin-bottom: 0; }
.variantes-opcion--activa {
    background: #F0FDFA;
    border-color: #14B8A6;
    color: #0F766E;
}
.variantes-opcion-nombre { flex: 1; }
.variantes-opcion-radio {
    flex-shrink: 0;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid #CBD5E1;
    background: white;
    position: relative;
    transition: all 0.15s;
}
.variantes-opcion-radio--activo {
    border-color: #14B8A6;
}
.variantes-opcion-radio--activo::after {
    content: '';
    position: absolute;
    inset: 3px;
    border-radius: 50%;
    background: #14B8A6;
}

.variantes-nota { margin-top: 4px; }
.variantes-nota-titulo { font-size: 14px; font-weight: 800; color: #1E293B; margin: 0 0 10px; }
.variantes-nota-textarea {
    width: 100%;
    box-sizing: border-box;
    resize: none;
    background: #F8FAFC;
    border: 2px solid #E2E8F0;
    border-radius: 14px;
    padding: 12px 14px;
    font-size: 14px;
    font-family: inherit;
    color: #1E293B;
    outline: none;
}
.variantes-nota-textarea:focus { border-color: #14B8A6; }
.variantes-nota-textarea::placeholder { color: #94A3B8; }

/* ══ IMPRIMIR ══ */
.comanda-print { display: none; }
@media print {
    body * { visibility: hidden !important; }
    .comanda-print, .comanda-print * { visibility: visible !important; }
    .comanda-print {
        display: block !important;
        position: absolute; left: 0; top: 0;
        width: 280px; padding: 8px;
        font-family: 'Courier New', monospace;
        color: #000;
    }
    @page { margin: 4mm; }
}
</style>
