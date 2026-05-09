<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ categorias: Array })

const categoriaActiva   = ref(props.categorias[0]?.id ?? null)
const modalCategoria    = ref(false)
const modalProducto     = ref(false)
const modalProductoKey  = ref(0)
const editandoCategoria = ref(null)
const editandoProducto  = ref(null)

const formCategoria = useForm({
    nombre: '', descripcion: '', icono: '🍽️', color: '#14B8A6', orden: 0, activo: true,
})

const formProducto = useForm({
    menu_categoria_id: null, nombre: '', descripcion: '',
    precio: '', disponible: true, activo: true, orden: 0, tiempo_preparacion: 10,
})

const categoriaSeleccionada = computed(() =>
    props.categorias.find(c => c.id === categoriaActiva.value)
)

function abrirModalCategoria(cat = null) {
    editandoCategoria.value = cat
    if (cat) {
        formCategoria.nombre      = cat.nombre
        formCategoria.descripcion = cat.descripcion ?? ''
        formCategoria.icono       = cat.icono ?? '🍽️'
        formCategoria.color       = cat.color ?? '#14B8A6'
        formCategoria.orden       = cat.orden
        formCategoria.activo      = cat.activo
    } else {
        formCategoria.reset()
        formCategoria.icono  = '🍽️'
        formCategoria.color  = '#14B8A6'
        formCategoria.activo = true
    }
    modalCategoria.value = true
}

function abrirModalProducto(prod = null, catId = null) {
    editandoProducto.value = prod
    if (prod) {
        formProducto.menu_categoria_id  = prod.menu_categoria_id
        formProducto.nombre             = prod.nombre
        formProducto.descripcion        = prod.descripcion ?? ''
        formProducto.precio             = prod.precio
        formProducto.disponible         = prod.disponible
        formProducto.activo             = prod.activo
        formProducto.orden              = prod.orden
        formProducto.tiempo_preparacion = prod.tiempo_preparacion
    } else {
        formProducto.menu_categoria_id  = catId ?? categoriaActiva.value
        formProducto.nombre             = ''
        formProducto.descripcion        = ''
        formProducto.precio             = ''
        formProducto.disponible         = true
        formProducto.activo             = true
        formProducto.orden              = 0
        formProducto.tiempo_preparacion = 10
    }
    modalProductoKey.value++
    modalProducto.value = true
}

function guardarCategoria() {
    if (editandoCategoria.value) {
        formCategoria.put(`/menu/categorias/${editandoCategoria.value.id}`, {
            onSuccess: () => {
                modalCategoria.value = false
                formCategoria.nombre = ''
                formCategoria.descripcion = ''
                formCategoria.icono = '🍽️'
                formCategoria.color = '#14B8A6'
                formCategoria.orden = 0
                formCategoria.activo = true
            }
        })
    } else {
        formCategoria.post('/menu/categorias', {
            onSuccess: () => {
                modalCategoria.value = false
                formCategoria.nombre = ''
                formCategoria.descripcion = ''
                formCategoria.icono = '🍽️'
                formCategoria.color = '#14B8A6'
                formCategoria.orden = 0
                formCategoria.activo = true
            }
        })
    }
}

function guardarProducto() {
    if (editandoProducto.value) {
        formProducto.put(`/menu/productos/${editandoProducto.value.id}`, {
            onSuccess: () => {
                modalProducto.value = false
                formProducto.menu_categoria_id = null
                formProducto.nombre = ''
                formProducto.descripcion = ''
                formProducto.precio = ''
                formProducto.disponible = true
                formProducto.activo = true
                formProducto.orden = 0
                formProducto.tiempo_preparacion = 10
            }
        })
    } else {
        formProducto.post('/menu/productos', {
            onSuccess: () => {
                modalProducto.value = false
                formProducto.menu_categoria_id = null
                formProducto.nombre = ''
                formProducto.descripcion = ''
                formProducto.precio = ''
                formProducto.disponible = true
                formProducto.activo = true
                formProducto.orden = 0
                formProducto.tiempo_preparacion = 10
            }
        })
    }
}

function eliminarCategoria(cat) {
    if (confirm(`¿Eliminar categoría "${cat.nombre}"?`)) {
        router.delete(`/menu/categorias/${cat.id}`)
    }
}

function eliminarProducto(prod) {
    if (confirm(`¿Eliminar "${prod.nombre}"?`)) {
        router.delete(`/menu/productos/${prod.id}`)
    }
}

async function toggleDisponible(prod) {
    await fetch(`/menu/productos/${prod.id}/disponible`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    router.reload({ only: ['categorias'] })
}

const iconos = ['🍽️','🥗','🍖','🍔','🍕','🌮','🥩','🍜','🥤','🧃','🍷','☕','🍮','🍰','🧁','🥐','🫕','🥘']
</script>

<template>
    <AppLayout title="Carta / Menú">
        <div style="max-width:1400px; margin:0 auto;">

            <!-- ══ HEADER ══ -->
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:28px; font-weight:800; color:#1E293B; margin:0;">📋 Carta / Menú</h1>
                    <p style="font-size:15px; color:#94A3B8; margin:4px 0 0;">Gestiona categorías y productos de tu restaurante</p>
                </div>
                <button
                    @click="abrirModalCategoria()"
                    style="display:flex; align-items:center; gap:8px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:14px; padding:14px 24px; font-size:16px; font-weight:700; cursor:pointer; box-shadow:0 4px 15px rgba(20,184,166,0.3);"
                >
                    ➕ Nueva categoría
                </button>
            </div>

            <div style="display:flex; gap:20px;">

                <!-- ══ SIDEBAR CATEGORÍAS ══ -->
                <div style="width:240px; shrink:0; display:flex; flex-direction:column; gap:8px;">
                    <p style="font-size:12px; font-weight:700; color:#94A3B8; text-transform:uppercase; letter-spacing:1.5px; margin:0 0 8px;">Categorías</p>

                    <button
                        v-for="cat in categorias"
                        :key="cat.id"
                        @click="categoriaActiva = cat.id"
                        :style="{
                            display: 'flex',
                            alignItems: 'center',
                            gap: '12px',
                            padding: '14px 16px',
                            borderRadius: '14px',
                            border: 'none',
                            cursor: 'pointer',
                            textAlign: 'left',
                            transition: 'all 0.15s',
                            background: categoriaActiva === cat.id ? cat.color || '#14B8A6' : 'white',
                            color: categoriaActiva === cat.id ? 'white' : '#475569',
                            boxShadow: categoriaActiva === cat.id ? `0 4px 15px ${cat.color || '#14B8A6'}40` : '0 1px 4px rgba(0,0,0,0.06)',
                            transform: categoriaActiva === cat.id ? 'scale(1.02)' : 'scale(1)',
                        }"
                    >
                        <span style="font-size:26px;">{{ cat.icono }}</span>
                        <div style="flex:1; min-width:0;">
                            <p style="font-size:15px; font-weight:600; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ cat.nombre }}</p>
                            <p style="font-size:12px; margin:2px 0 0; opacity:0.7;">{{ cat.productos_activos?.length ?? 0 }} productos</p>
                        </div>
                    </button>

                    <!-- Sin categorías -->
                    <div v-if="!categorias.length" style="text-align:center; padding:30px 0; color:#CBD5E1;">
                        <p style="font-size:32px; margin:0 0 8px;">📂</p>
                        <p style="font-size:14px;">Sin categorías aún</p>
                    </div>
                </div>

                <!-- ══ PANEL PRODUCTOS ══ -->
                <div style="flex:1;">
                    <div v-if="categoriaSeleccionada">

                        <!-- Header categoría -->
                        <div :style="{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'space-between',
                            background: 'white',
                            borderRadius: '16px',
                            padding: '18px 24px',
                            marginBottom: '16px',
                            boxShadow: '0 2px 8px rgba(0,0,0,0.06)',
                            borderLeft: `5px solid ${categoriaSeleccionada.color || '#14B8A6'}`,
                        }">
                            <div style="display:flex; align-items:center; gap:14px;">
                                <span style="font-size:40px;">{{ categoriaSeleccionada.icono }}</span>
                                <div>
                                    <h2 style="font-size:22px; font-weight:800; color:#1E293B; margin:0;">{{ categoriaSeleccionada.nombre }}</h2>
                                    <p style="font-size:14px; color:#94A3B8; margin:2px 0 0;">{{ categoriaSeleccionada.productos_activos?.length ?? 0 }} productos · {{ categoriaSeleccionada.descripcion || 'Sin descripción' }}</p>
                                </div>
                            </div>
                            <div style="display:flex; gap:10px;">
                                <button
                                    @click="abrirModalProducto(null, categoriaSeleccionada.id)"
                                    style="display:flex; align-items:center; gap:6px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; padding:12px 20px; font-size:15px; font-weight:700; cursor:pointer; box-shadow:0 3px 12px rgba(20,184,166,0.25);"
                                >
                                    ➕ Producto
                                </button>
                                <button
                                    @click="abrirModalCategoria(categoriaSeleccionada)"
                                    style="background:#F1F5F9; color:#475569; border:none; border-radius:12px; padding:12px 16px; font-size:15px; cursor:pointer; font-weight:600;"
                                >
                                    ✏️ Editar
                                </button>
                                <button
                                    @click="eliminarCategoria(categoriaSeleccionada)"
                                    style="background:#FEE2E2; color:#EF4444; border:none; border-radius:12px; padding:12px 16px; font-size:15px; cursor:pointer;"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>

                        <!-- Grid productos -->
                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:14px;">
                            <div
                                v-for="prod in categoriaSeleccionada.productos_activos"
                                :key="prod.id"
                                :style="{
                                    background: 'white',
                                    borderRadius: '16px',
                                    padding: '20px',
                                    boxShadow: '0 2px 10px rgba(0,0,0,0.06)',
                                    display: 'flex',
                                    flexDirection: 'column',
                                    gap: '10px',
                                    opacity: prod.disponible ? '1' : '0.55',
                                    borderTop: `4px solid ${categoriaSeleccionada.color || '#14B8A6'}`,
                                    transition: 'transform 0.15s, box-shadow 0.15s',
                                }"
                            >
                                <!-- Nombre y precio -->
                                <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:10px;">
                                    <h3 style="font-size:18px; font-weight:700; color:#1E293B; margin:0; line-height:1.3;">{{ prod.nombre }}</h3>
                                    <span style="font-size:22px; font-weight:800; color:#14B8A6; white-space:nowrap;">S/ {{ Number(prod.precio).toFixed(2) }}</span>
                                </div>

                                <!-- Descripción -->
                                <p v-if="prod.descripcion" style="font-size:14px; color:#64748B; margin:0; line-height:1.5;">{{ prod.descripcion }}</p>

                                <!-- Tiempo -->
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <span style="background:#F0FDF4; color:#166534; font-size:13px; font-weight:600; padding:4px 10px; border-radius:8px;">⏱ {{ prod.tiempo_preparacion }} min</span>
                                </div>

                                <!-- Acciones -->
                                <div style="display:flex; gap:8px; margin-top:auto; padding-top:12px; border-top:1px solid #F1F5F9;">
                                    <button
                                        @click="toggleDisponible(prod)"
                                        :style="{
                                            flex: '1',
                                            padding: '10px',
                                            borderRadius: '10px',
                                            border: 'none',
                                            fontSize: '14px',
                                            fontWeight: '700',
                                            cursor: 'pointer',
                                            background: prod.disponible ? '#DCFCE7' : '#F1F5F9',
                                            color: prod.disponible ? '#166534' : '#94A3B8',
                                        }"
                                    >
                                        {{ prod.disponible ? '✅ Disponible' : '⏸ No disponible' }}
                                    </button>
                                    <button
                                        @click="abrirModalProducto(prod)"
                                        style="background:#F1F5F9; color:#475569; border:none; border-radius:10px; padding:10px 14px; font-size:15px; cursor:pointer;"
                                    >✏️</button>
                                    <button
                                        @click="eliminarProducto(prod)"
                                        style="background:#FEE2E2; color:#EF4444; border:none; border-radius:10px; padding:10px 14px; font-size:15px; cursor:pointer;"
                                    >🗑️</button>
                                </div>
                            </div>

                            <!-- Vacío -->
                            <div v-if="!categoriaSeleccionada.productos_activos?.length"
                                style="grid-column:1/-1; text-align:center; padding:60px 0; color:#CBD5E1;">
                                <p style="font-size:48px; margin:0 0 12px;">🍽️</p>
                                <p style="font-size:18px; margin:0 0 20px;">Sin productos en esta categoría</p>
                                <button
                                    @click="abrirModalProducto(null, categoriaSeleccionada.id)"
                                    style="background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; padding:14px 28px; font-size:16px; font-weight:700; cursor:pointer;"
                                >
                                    ➕ Agregar primer producto
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Sin categoría seleccionada -->
                    <div v-else style="text-align:center; padding:80px 0; color:#CBD5E1;">
                        <p style="font-size:56px; margin:0 0 16px;">📋</p>
                        <p style="font-size:20px; margin:0 0 24px;">Selecciona o crea una categoría para empezar</p>
                        <button
                            @click="abrirModalCategoria()"
                            style="background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; padding:14px 28px; font-size:16px; font-weight:700; cursor:pointer;"
                        >
                            ➕ Nueva categoría
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ MODAL CATEGORÍA ══ -->
        <Teleport to="body">
            <div v-if="modalCategoria"
                style="position:fixed; inset:0; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:9999; padding:20px;"
                @click.self="modalCategoria=false">
                <div style="background:white; border-radius:24px; padding:32px; width:100%; max-width:480px; box-shadow:0 25px 60px rgba(0,0,0,0.25);">
                    <h2 style="font-size:22px; font-weight:800; color:#1E293B; margin:0 0 24px;">
                        {{ editandoCategoria ? '✏️ Editar categoría' : '➕ Nueva categoría' }}
                    </h2>

                    <!-- Nombre -->
                    <div style="margin-bottom:16px;">
                        <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Nombre *</label>
                        <input v-model="formCategoria.nombre" type="text" placeholder="Ej: Entradas"
                            style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;" />
                    </div>

                    <!-- Descripción -->
                    <div style="margin-bottom:16px;">
                        <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Descripción</label>
                        <input v-model="formCategoria.descripcion" type="text"
                            style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;" />
                    </div>

                    <!-- Iconos -->
                    <div style="margin-bottom:16px;">
                        <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Icono</label>
                        <div style="display:flex; flex-wrap:wrap; gap:8px;">
                            <button
                                v-for="ic in iconos" :key="ic"
                                @click="formCategoria.icono = ic"
                                :style="{
                                    fontSize: '24px',
                                    padding: '8px',
                                    borderRadius: '10px',
                                    border: formCategoria.icono === ic ? '2px solid #14B8A6' : '2px solid transparent',
                                    background: formCategoria.icono === ic ? '#F0FDFA' : '#F8FAFC',
                                    cursor: 'pointer',
                                }"
                            >{{ ic }}</button>
                        </div>
                    </div>

                    <!-- Color + Orden -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
                        <div>
                            <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Color</label>
                            <input v-model="formCategoria.color" type="color"
                                style="width:100%; height:48px; border-radius:12px; border:2px solid #E2E8F0; cursor:pointer;" />
                        </div>
                        <div>
                            <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Orden</label>
                            <input v-model="formCategoria.orden" type="number" min="0"
                                style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;" />
                        </div>
                    </div>

                    <div style="display:flex; gap:12px;">
                        <button @click="modalCategoria=false"
                            style="flex:1; padding:14px; background:#F1F5F9; color:#475569; border:none; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer;">
                            Cancelar
                        </button>
                        <button @click="guardarCategoria" :disabled="formCategoria.processing"
                            style="flex:1; padding:14px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; font-size:16px; font-weight:700; cursor:pointer;">
                            {{ formCategoria.processing ? 'Guardando...' : 'Guardar' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- ══ MODAL PRODUCTO ══ -->
        <Teleport to="body">
            <div v-if="modalProducto"
                :key="modalProductoKey"
                style="position:fixed; inset:0; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:9999; padding:20px;"
                @click.self="modalProducto=false">
                <div style="background:white; border-radius:24px; padding:32px; width:100%; max-width:480px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 60px rgba(0,0,0,0.25);">
                    <h2 style="font-size:22px; font-weight:800; color:#1E293B; margin:0 0 24px;">
                        {{ editandoProducto ? '✏️ Editar producto' : '➕ Nuevo producto' }}
                    </h2>

                    <!-- Categoría -->
                    <div style="margin-bottom:16px;">
                        <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Categoría *</label>
                        <select v-model="formProducto.menu_categoria_id"
                            style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; background:white; box-sizing:border-box;">
                            <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
                                {{ cat.icono }} {{ cat.nombre }}
                            </option>
                        </select>
                    </div>

                    <!-- Nombre -->
                    <div style="margin-bottom:16px;">
                        <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Nombre *</label>
                        <input v-model="formProducto.nombre" type="text" placeholder="Ej: Lomo saltado"
                            style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;" />
                    </div>

                    <!-- Descripción -->
                    <div style="margin-bottom:16px;">
                        <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Descripción</label>
                        <textarea v-model="formProducto.descripcion" rows="2"
                            style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; resize:none; box-sizing:border-box;"></textarea>
                    </div>

                    <!-- Precio + Tiempo -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                        <div>
                            <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Precio S/ *</label>
                            <input v-model="formProducto.precio" type="number" step="0.01" min="0" placeholder="0.00"
                                style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;" />
                        </div>
                        <div>
                            <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Tiempo (min)</label>
                            <input v-model="formProducto.tiempo_preparacion" type="number" min="0"
                                style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;" />
                        </div>
                    </div>

                    <!-- Toggle disponible -->
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
                        <button
                            @click="formProducto.disponible = !formProducto.disponible"
                            :style="{
                                position: 'relative',
                                width: '52px',
                                height: '28px',
                                borderRadius: '14px',
                                border: 'none',
                                cursor: 'pointer',
                                background: formProducto.disponible ? '#14B8A6' : '#CBD5E1',
                                transition: 'background 0.2s',
                            }"
                        >
                            <span :style="{
                                position: 'absolute',
                                top: '3px',
                                left: formProducto.disponible ? '26px' : '3px',
                                width: '22px',
                                height: '22px',
                                background: 'white',
                                borderRadius: '50%',
                                boxShadow: '0 2px 4px rgba(0,0,0,0.2)',
                                transition: 'left 0.2s',
                            }"></span>
                        </button>
                        <span style="font-size:16px; font-weight:600; color:#475569;">Disponible para venta</span>
                    </div>

                    <div style="display:flex; gap:12px;">
                        <button @click="modalProducto=false"
                            style="flex:1; padding:14px; background:#F1F5F9; color:#475569; border:none; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer;">
                            Cancelar
                        </button>
                        <button @click="guardarProducto" :disabled="formProducto.processing"
                            style="flex:1; padding:14px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; font-size:16px; font-weight:700; cursor:pointer;">
                            {{ formProducto.processing ? 'Guardando...' : 'Guardar' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>