<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    mesa:            Object,
    categorias:      Array,
    pedidosAbiertos: Array,
    siguienteRonda:  Number,
})

const categoriaActiva = ref(props.categorias[0]?.id ?? null)
const carrito         = ref([])
const notasPedido     = ref('')
const busqueda        = ref('')

const categoriaSeleccionada = computed(() =>
    props.categorias.find(c => c.id === categoriaActiva.value)
)

const productosFiltrados = computed(() => {
    if (!categoriaSeleccionada.value) return []
    if (!busqueda.value) return categoriaSeleccionada.value.productos_activos
    return categoriaSeleccionada.value.productos_activos.filter(p =>
        p.nombre.toLowerCase().includes(busqueda.value.toLowerCase())
    )
})

const totalCarrito = computed(() =>
    carrito.value.reduce((sum, item) => sum + item.subtotal, 0)
)

const totalGeneral = computed(() => {
    const totalPedidos = props.pedidosAbiertos.reduce((sum, p) => sum + Number(p.total), 0)
    return totalPedidos + totalCarrito.value
})

function agregarProducto(prod) {
    const existente = carrito.value.find(i => i.menu_producto_id === prod.id)
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
        })
    }
}

function quitarProducto(index) {
    if (carrito.value[index].cantidad > 1) {
        carrito.value[index].cantidad--
        carrito.value[index].subtotal =
            carrito.value[index].cantidad * carrito.value[index].precio_unitario
    } else {
        carrito.value.splice(index, 1)
    }
}

function eliminarItem(index) {
    carrito.value.splice(index, 1)
}

const enviando = ref(false)

function enviarACocina() {
    if (!carrito.value.length) return
    enviando.value = true

    const form = useForm({
        items: carrito.value,
        notas: notasPedido.value,
    })

    form.post(`/pos/${props.mesa.id}`, {
        onSuccess: () => { enviando.value = false },
        onError:   () => { enviando.value = false },
    })
}

function cerrarMesa() {
    window.location.href = `/caja-restaurante/${props.mesa.id}`
}

</script>

<template>
    <AppLayout :title="`🍽️ Mesa ${mesa.numero} · POS`">
        <div style="display:flex; height:calc(100vh - 110px); gap:16px; overflow:hidden;">

            <!-- ══ PANEL IZQUIERDO: CARTA ══ -->
            <div style="display:flex; flex-direction:column; width:58%; background:white; border-radius:20px; border:1px solid #E2E8F0; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.06);">

                <!-- Búsqueda -->
                <div style="padding:14px; border-bottom:1px solid #F0F4F8; background:#FAFBFC;">
                    <input
                        v-model="busqueda"
                        type="text"
                        placeholder="🔍 Buscar producto..."
                        style="width:100%; padding:14px 18px; border:2px solid #E2E8F0; border-radius:14px; font-size:17px; outline:none; box-sizing:border-box; background:white;"
                    />
                </div>

                <!-- Tabs categorías -->
                <div style="display:flex; gap:10px; padding:12px 14px; border-bottom:1px solid #F0F4F8; overflow-x:auto; background:#FAFBFC;">
                    <button
                        v-for="cat in categorias"
                        :key="cat.id"
                        @click="categoriaActiva = cat.id; busqueda = ''"
                        :style="{
                            display: 'flex',
                            alignItems: 'center',
                            gap: '8px',
                            padding: '12px 20px',
                            borderRadius: '14px',
                            fontSize: '16px',
                            fontWeight: '600',
                            border: 'none',
                            cursor: 'pointer',
                            whiteSpace: 'nowrap',
                            transition: 'all 0.15s',
                            background: categoriaActiva === cat.id ? cat.color || '#14B8A6' : '#F1F5F9',
                            color: categoriaActiva === cat.id ? 'white' : '#64748B',
                            transform: categoriaActiva === cat.id ? 'scale(1.04)' : 'scale(1)',
                            boxShadow: categoriaActiva === cat.id ? '0 4px 12px rgba(0,0,0,0.15)' : 'none',
                        }"
                    >
                        <span style="font-size:22px;">{{ cat.icono }}</span>
                        {{ cat.nombre }}
                    </button>
                </div>

                <!-- Grid productos -->
                <div style="flex:1; overflow-y:auto; padding:14px;">
                    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:12px;">
                        <button
                            v-for="prod in productosFiltrados"
                            :key="prod.id"
                            @click="agregarProducto(prod)"
                            :style="{
                                background: 'white',
                                border: '2px solid #E2E8F0',
                                borderRadius: '16px',
                                padding: '18px',
                                textAlign: 'left',
                                cursor: 'pointer',
                                transition: 'all 0.15s',
                                opacity: prod.disponible ? '1' : '0.4',
                                pointerEvents: prod.disponible ? 'auto' : 'none',
                            }"
                            @mouseenter="$event.currentTarget.style.borderColor='#14B8A6'; $event.currentTarget.style.background='#F0FDFA'; $event.currentTarget.style.transform='scale(1.02)'"
                            @mouseleave="$event.currentTarget.style.borderColor='#E2E8F0'; $event.currentTarget.style.background='white'; $event.currentTarget.style.transform='scale(1)'"
                            @mousedown="$event.currentTarget.style.transform='scale(0.97)'"
                            @mouseup="$event.currentTarget.style.transform='scale(1.02)'"
                        >
                            <p style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 6px; line-height:1.3;">{{ prod.nombre }}</p>
                            <p v-if="prod.descripcion" style="font-size:13px; color:#94A3B8; margin:0 0 12px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">{{ prod.descripcion }}</p>
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:auto;">
                                <span style="font-size:22px; font-weight:800; color:#14B8A6;">S/ {{ Number(prod.precio).toFixed(2) }}</span>
                                <span style="font-size:13px; color:#CBD5E1; background:#F8FAFC; padding:4px 10px; border-radius:8px;">⏱ {{ prod.tiempo_preparacion }}min</span>
                            </div>
                        </button>
                    </div>

                    <!-- Vacío -->
                    <div v-if="!productosFiltrados.length" style="text-align:center; padding:60px 0; color:#CBD5E1;">
                        <p style="font-size:48px; margin:0 0 12px;">🍽️</p>
                        <p style="font-size:18px;">Sin productos en esta categoría</p>
                    </div>
                </div>
            </div>

            <!-- ══ PANEL DERECHO: PEDIDO ══ -->
            <div style="display:flex; flex-direction:column; width:42%; gap:12px;">

                <!-- Header mesa -->
                <div style="background:linear-gradient(135deg, #14B8A6, #0F766E); border-radius:20px; padding:20px 24px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 4px 20px rgba(20,184,166,0.3);">
                    <div>
                        <p style="font-size:28px; font-weight:800; color:white; margin:0;">🪑 Mesa {{ mesa.numero }}</p>
                        <p style="font-size:16px; color:rgba(255,255,255,0.8); margin:4px 0 0;">Ronda {{ siguienteRonda }} · {{ carrito.length }} productos</p>
                    </div>

                   <button
                        @click="cerrarMesa"
                         style="background:rgba(255,255,255,0.2); color:white; border:2px solid rgba(255,255,255,0.4); border-radius:12px; padding:12px 18px; font-size:15px; font-weight:600; cursor:pointer;"
                            >
                         💳 Cobrar S/ {{ totalGeneral.toFixed(2) }}
                        </button>
                </div>

                <!-- Pedidos anteriores -->
                <div v-if="pedidosAbiertos.length" style="background:white; border-radius:16px; border:1px solid #E2E8F0; padding:16px; box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                    <p style="font-size:13px; font-weight:700; color:#94A3B8; text-transform:uppercase; letter-spacing:1px; margin:0 0 12px;">Rondas anteriores</p>
                    <div v-for="pedido in pedidosAbiertos" :key="pedido.id" style="margin-bottom:10px;">
                        <p style="font-size:15px; font-weight:700; color:#475569; margin:0 0 6px;">Ronda {{ pedido.numero_ronda }}</p>
                        <div v-for="det in pedido.detalles" :key="det.id" style="display:flex; justify-content:space-between; font-size:15px; color:#64748B; padding:3px 0;">
                            <span>{{ det.cantidad }}× {{ det.nombre_producto }}</span>
                            <span style="font-weight:600;">S/ {{ Number(det.subtotal).toFixed(2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Carrito -->
                <div style="flex:1; background:white; border-radius:20px; border:1px solid #E2E8F0; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.06);">

                    <!-- Header carrito -->
                    <div style="padding:16px 20px; border-bottom:1px solid #F0F4F8; background:#FAFBFC;">
                        <p style="font-size:17px; font-weight:700; color:#1E293B; margin:0;">
                            🛒 Esta ronda
                            <span v-if="carrito.length" style="color:#14B8A6; font-size:15px;">({{ carrito.length }} items)</span>
                        </p>
                    </div>

                    <!-- Items -->
                    <div style="flex:1; overflow-y:auto; padding:14px; display:flex; flex-direction:column; gap:10px;">

                        <div v-if="!carrito.length" style="text-align:center; padding:40px 0; color:#CBD5E1;">
                            <p style="font-size:40px; margin:0 0 10px;">🛒</p>
                            <p style="font-size:17px;">Toca un producto para agregarlo</p>
                        </div>

                        <div
                            v-for="(item, i) in carrito"
                            :key="i"
                            style="display:flex; align-items:center; gap:12px; background:#F8FAFC; border-radius:14px; padding:12px 14px; border:1px solid #E2E8F0;"
                        >
                            <div style="flex:1; min-width:0;">
                                <p style="font-size:17px; font-weight:700; color:#1E293B; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ item.nombre_producto }}</p>
                                <p style="font-size:14px; color:#94A3B8; margin:2px 0 0;">S/ {{ item.precio_unitario.toFixed(2) }} c/u</p>
                            </div>

                            <!-- Controles cantidad -->
                            <div style="display:flex; align-items:center; gap:8px;">
                                <button @click="quitarProducto(i)"
                                    style="width:36px; height:36px; background:#E2E8F0; border:none; border-radius:10px; font-size:20px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center;">−</button>
                                <span style="font-size:20px; font-weight:800; color:#1E293B; width:28px; text-align:center;">{{ item.cantidad }}</span>
                                <button @click="agregarProducto({id: item.menu_producto_id, nombre: item.nombre_producto, precio: item.precio_unitario})"
                                    style="width:36px; height:36px; background:#14B8A6; border:none; border-radius:10px; font-size:20px; font-weight:700; color:white; cursor:pointer; display:flex; align-items:center; justify-content:center;">+</button>
                            </div>

                            <span style="font-size:18px; font-weight:800; color:#14B8A6; width:70px; text-align:right;">S/ {{ item.subtotal.toFixed(2) }}</span>

                            <button @click="eliminarItem(i)"
                                style="background:#FEE2E2; border:none; border-radius:8px; width:32px; height:32px; color:#EF4444; font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center;">✕</button>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div style="padding:0 16px 12px;">
                        <input
                            v-model="notasPedido"
                            type="text"
                            placeholder="📝 Notas para cocina..."
                            style="width:100%; padding:12px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;"
                        />
                    </div>

                    <!-- Totales -->
                    <div style="padding:14px 20px; border-top:2px solid #F0F4F8; background:#FAFBFC;">
                        <div style="display:flex; justify-content:space-between; font-size:16px; color:#64748B; margin-bottom:6px;">
                            <span>Esta ronda</span>
                            <span style="font-weight:600;">S/ {{ totalCarrito.toFixed(2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:22px; font-weight:800; color:#1E293B;">
                            <span>Total mesa</span>
                            <span style="color:#14B8A6;">S/ {{ totalGeneral.toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Botón enviar -->
                    <div style="padding:16px;">
                        <button
                            @click="enviarACocina"
                            :disabled="!carrito.length || enviando"
                            :style="{
                                width: '100%',
                                padding: '20px',
                                background: carrito.length ? 'linear-gradient(135deg, #14B8A6, #0F766E)' : '#E2E8F0',
                                color: carrito.length ? 'white' : '#94A3B8',
                                border: 'none',
                                borderRadius: '16px',
                                fontSize: '20px',
                                fontWeight: '800',
                                cursor: carrito.length ? 'pointer' : 'not-allowed',
                                boxShadow: carrito.length ? '0 4px 20px rgba(20,184,166,0.4)' : 'none',
                                transition: 'all 0.2s',
                            }"
                        >
                            {{ enviando ? '⏳ Enviando...' : '🍳 Enviar a cocina' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>