<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const page = usePage()
const props = defineProps({
    mesa:            Object,
    categorias:      Array,
    pedidosAbiertos: Array,
    siguienteRonda:  Number,
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

function agregarAlCarrito(prod, modificadores) {
    const existente = carrito.value.find(i =>
        i.menu_producto_id === prod.id &&
        JSON.stringify(i.modificadores || []) === JSON.stringify(modificadores)
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
        })
    }
}

function agregarProducto(prod) {
    const mods = modificadoresDeCategoria(prod)
    if (mods.length) {
        productoPendiente.value = prod
        modificadoresParaElegir.value = mods
        modificadoresSeleccionados.value = []
        mostrarModalModificadores.value = true
        return
    }
    agregarAlCarrito(prod, [])
}

function confirmarModificadores() {
    agregarAlCarrito(productoPendiente.value, [...modificadoresSeleccionados.value])
    mostrarModalModificadores.value = false
    productoPendiente.value = null
}

function cancelarModalModificadores() {
    mostrarModalModificadores.value = false
    productoPendiente.value = null
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

function enviarACocina() {
    if (!carrito.value.length) return
    enviando.value = true
    const form = useForm({ items: carrito.value, notas: notasPedido.value })
    form.post(`/pos/${props.mesa.id}`, {
        onSuccess: () => { enviando.value = false; mostrarConfirmacion.value = false },
        onError:   () => { enviando.value = false },
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

        <!-- MODAL MODIFICADORES -->
        <div v-if="mostrarModalModificadores" class="mods-overlay" @click.self="cancelarModalModificadores">
            <div class="mods-modal">
                <h3 class="mods-titulo">🧂 {{ productoPendiente?.nombre }}</h3>
                <p class="mods-sub">Elige modificadores (opcional)</p>

                <div class="mods-lista">
                    <label v-for="m in modificadoresParaElegir" :key="m.id" class="mods-check">
                        <input type="checkbox" :value="m.nombre" v-model="modificadoresSeleccionados" />
                        <span>{{ m.nombre }}</span>
                    </label>
                </div>

                <div class="mods-btns">
                    <button @click="cancelarModalModificadores" class="mods-btn mods-btn--cancelar">Cancelar</button>
                    <button @click="confirmarModificadores" class="mods-btn mods-btn--confirmar">Agregar al pedido</button>
                </div>
            </div>
        </div>

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
}
.mods-modal {
    background: white;
    border-radius: 20px;
    padding: 22px;
    width: 100%;
    max-width: 380px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.mods-titulo { font-size: 18px; font-weight: 800; color: #1E293B; margin: 0 0 2px; }
.mods-sub { font-size: 13px; color: #64748B; margin: 0 0 16px; }
.mods-lista { display: flex; flex-direction: column; gap: 8px; margin-bottom: 18px; }
.mods-check {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 14px;
    font-weight: 600;
    color: #1E293B;
    cursor: pointer;
}
.mods-check input { width: 18px; height: 18px; cursor: pointer; }
.mods-btns { display: flex; gap: 10px; }
.mods-btn { flex: 1; min-height: 48px; border-radius: 14px; font-size: 14px; font-weight: 800; cursor: pointer; border: none; }
.mods-btn--cancelar { background: #F1F5F9; color: #475569; }
.mods-btn--confirmar { background: linear-gradient(135deg, #14B8A6, #0F766E); color: white; box-shadow: 0 4px 16px rgba(20,184,166,0.4); }

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
