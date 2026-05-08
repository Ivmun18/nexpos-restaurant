<template>
    <AppLayout title="Productos" subtitle="Gestión de productos ferretería">

        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <div style="display:flex; gap:12px; align-items:center;">
                <input v-model="busqueda" placeholder="🔍 Buscar por nombre o código..." 
                    style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; width:300px; outline:none;">
                <select v-model="filtroCategoria" style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none;">
                    <option value="">Todas las categorías</option>
                    <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                </select>
                <select v-model="filtroStock" style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none;">
                    <option value="">Todo el stock</option>
                    <option value="bajo">Stock bajo</option>
                    <option value="normal">Stock normal</option>
                </select>
            </div>
            <button @click="abrirModal()" style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                + Nuevo Producto
            </button>
        </div>

        <!-- Stats -->
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Total Productos</p>
                <p style="font-size:24px; font-weight:700; color:#1E293B; margin:0;">{{ productos.length }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Stock Bajo</p>
                <p style="font-size:24px; font-weight:700; color:#DC2626; margin:0;">{{ productosStockBajo }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Valor Inventario</p>
                <p style="font-size:24px; font-weight:700; color:#14B8A6; margin:0;">S/ {{ valorInventario }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Categorías</p>
                <p style="font-size:24px; font-weight:700; color:#1E293B; margin:0;">{{ categorias.length }}</p>
            </div>
        </div>

        <!-- Tabla -->
        <div style="background:white; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B; letter-spacing:0.5px;">PRODUCTO</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">CÓDIGO</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">UNIDAD</th>
                        <th style="padding:14px 20px; text-align:right; font-size:12px; font-weight:700; color:#64748B;">P. COMPRA</th>
                        <th style="padding:14px 20px; text-align:right; font-size:12px; font-weight:700; color:#64748B;">P. VENTA</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; font-weight:700; color:#64748B;">STOCK</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; font-weight:700; color:#64748B;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="productosFiltrados.length === 0">
                        <td colspan="7" style="padding:40px; text-align:center; color:#94A3B8; font-size:14px;">
                            No hay productos registrados
                        </td>
                    </tr>
                    <tr v-for="p in productosFiltrados" :key="p.id"
                        style="border-top:1px solid #F1F5F9;"
                        @mouseenter="e => e.currentTarget.style.background='#F8FAFC'"
                        @mouseleave="e => e.currentTarget.style.background='white'">
                        <td style="padding:14px 20px;">
                            <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ p.descripcion }}</p>
                            <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ p.codigo_barras || 'Sin código de barras' }}</p>
                            <span v-if="p.categoria" :style="{ padding: '2px 8px', borderRadius: '20px', fontSize: '11px', fontWeight: '600', background: (p.categoria.color||'#14B8A6') + '22', color: p.categoria.color||'#14B8A6' }">
                                {{ p.categoria.icono }} {{ p.categoria.nombre }}
                            </span>
                        </td>
                        <td style="padding:14px 20px; font-size:14px; color:#475569;">{{ p.codigo }}</td>
                        <td style="padding:14px 20px; font-size:14px; color:#475569;">{{ p.unidad_medida || 'UND' }}</td>
                        <td style="padding:14px 20px; text-align:right; font-size:14px; color:#475569;">S/ {{ Number(p.precio_compra).toFixed(2) }}</td>
                        <td style="padding:14px 20px; text-align:right; font-size:15px; font-weight:700; color:#14B8A6;">S/ {{ Number(p.precio_venta).toFixed(2) }}</td>
                        <td style="padding:14px 20px; text-align:center;">
                            <span :style="{
                                padding: '4px 12px', borderRadius: '20px', fontSize: '13px', fontWeight: '700',
                                background: p.stock_actual <= (p.stock_minimo || 0) ? '#FEF2F2' : '#F0FDF4',
                                color: p.stock_actual <= (p.stock_minimo || 0) ? '#991B1B' : '#166534',
                            }">{{ p.stock_actual }} {{ p.unidad_medida || 'UND' }}</span>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <div style="display:flex; gap:8px; justify-content:center;">
                                <button @click="editarProducto(p)" style="padding:6px 12px; background:#F0FDFA; color:#0F766E; border-radius:8px; font-size:12px; font-weight:600; border:1px solid #CCFBF1; cursor:pointer;">✏️ Editar</button>
                                <button @click="ajustarStock(p)" style="padding:6px 12px; background:#EFF6FF; color:#1D4ED8; border-radius:8px; font-size:12px; font-weight:600; border:1px solid #BFDBFE; cursor:pointer;">📦 Stock</button>
                                <button @click="eliminarProducto(p)" style="padding:6px 12px; background:#FEF2F2; color:#DC2626; border-radius:8px; font-size:12px; font-weight:600; border:1px solid #FECACA; cursor:pointer;">🗑️</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal Producto -->
        <div v-if="modal" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:16px; padding:28px; width:580px; margin:auto;">
                <h2 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 20px;">{{ editando ? 'Editar Producto' : 'Nuevo Producto' }}</h2>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">DESCRIPCIÓN *</label>
                        <input v-model="form.descripcion" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">CÓDIGO INTERNO</label>
                        <input v-model="form.codigo" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">CÓDIGO DE BARRAS</label>
                        <input v-model="form.codigo_barras" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">UNIDAD DE MEDIDA</label>
                        <select v-model="form.unidad_medida" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                            <option value="UND">Unidad (UND)</option>
                            <option value="MT">Metro (MT)</option>
                            <option value="KG">Kilogramo (KG)</option>
                            <option value="LT">Litro (LT)</option>
                            <option value="RLL">Rollo (RLL)</option>
                            <option value="CJA">Caja (CJA)</option>
                            <option value="PAR">Par (PAR)</option>
                            <option value="JGO">Juego (JGO)</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">CATEGORÍA</label>
                        <select v-model="form.categoria_id" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                            <option value="">Sin categoría</option>
                            <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">PRECIO COMPRA (S/)</label>
                        <input v-model="form.precio_compra" type="number" step="0.01" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">PRECIO VENTA (S/) *</label>
                        <input v-model="form.precio_venta" type="number" step="0.01" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">STOCK ACTUAL</label>
                        <input v-model="form.stock_actual" type="number" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">STOCK MÍNIMO</label>
                        <input v-model="form.stock_minimo" type="number" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">MARCA / PROVEEDOR</label>
                        <input v-model="form.marca" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                </div>

                <div style="display:flex; gap:12px; margin-top:24px; justify-content:flex-end;">
                    <button @click="modal=false" style="padding:10px 20px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; cursor:pointer; background:white;">Cancelar</button>
                    <button @click="guardar" style="padding:10px 24px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">
                        {{ editando ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Stock -->
        <div v-if="modalStock" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:16px; padding:28px; width:380px;">
                <h2 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 8px;">Ajustar Stock</h2>
                <p style="font-size:14px; color:#64748B; margin:0 0 20px;">{{ productoSeleccionado?.descripcion }}</p>
                <div style="display:flex; gap:12px; margin-bottom:16px;">
                    <button @click="tipoAjuste='entrada'" :style="{flex:1, padding:'10px', borderRadius:'8px', border:'2px solid', borderColor: tipoAjuste==='entrada' ? '#14B8A6' : '#E2E8F0', background: tipoAjuste==='entrada' ? '#F0FDFA' : 'white', color: tipoAjuste==='entrada' ? '#0F766E' : '#64748B', fontWeight:'600', cursor:'pointer'}">📥 Entrada</button>
                    <button @click="tipoAjuste='salida'" :style="{flex:1, padding:'10px', borderRadius:'8px', border:'2px solid', borderColor: tipoAjuste==='salida' ? '#DC2626' : '#E2E8F0', background: tipoAjuste==='salida' ? '#FEF2F2' : 'white', color: tipoAjuste==='salida' ? '#DC2626' : '#64748B', fontWeight:'600', cursor:'pointer'}">📤 Salida</button>
                </div>
                <input v-model="cantidadAjuste" type="number" placeholder="Cantidad" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box; margin-bottom:16px;">
                <div style="display:flex; gap:12px; justify-content:flex-end;">
                    <button @click="modalStock=false" style="padding:10px 20px; border:1px solid #E2E8F0; border-radius:8px; cursor:pointer; background:white;">Cancelar</button>
                    <button @click="guardarStock" style="padding:10px 24px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-weight:600; cursor:pointer;">Confirmar</button>
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
    productos:   { type: Array, default: () => [] },
    categorias:  { type: Array, default: () => [] },
})

const busqueda        = ref('')
const filtroCategoria = ref('')
const filtroStock     = ref('')
const modal           = ref(false)
const modalStock      = ref(false)
const editando        = ref(false)
const productoSeleccionado = ref(null)
const tipoAjuste      = ref('entrada')
const cantidadAjuste  = ref('')

const form = ref({
    descripcion: '', codigo: '', codigo_barras: '', unidad_medida: 'UND',
    categoria_id: '', precio_compra: '', precio_venta: '',
    stock_actual: 0, stock_minimo: 5, marca: ''
})

const productosFiltrados = computed(() => {
    return props.productos.filter(p => {
        const matchBusqueda = !busqueda.value ||
            p.descripcion?.toLowerCase().includes(busqueda.value.toLowerCase()) ||
            p.codigo?.toLowerCase().includes(busqueda.value.toLowerCase()) ||
            p.codigo_barras?.includes(busqueda.value)
        const matchCategoria = !filtroCategoria.value || p.categoria_id == filtroCategoria.value
        const matchStock = !filtroStock.value ||
            (filtroStock.value === 'bajo' && p.stock_actual <= (p.stock_minimo || 0)) ||
            (filtroStock.value === 'normal' && p.stock_actual > (p.stock_minimo || 0))
        return matchBusqueda && matchCategoria && matchStock
    })
})

const productosStockBajo = computed(() =>
    props.productos.filter(p => p.stock_actual <= (p.stock_minimo || 0)).length
)

const valorInventario = computed(() => {
    const total = props.productos.reduce((sum, p) => sum + (p.stock_actual * p.precio_compra), 0)
    return total.toFixed(2)
})

const abrirModal = () => {
    editando.value = false
    form.value = { descripcion: '', codigo: '', codigo_barras: '', unidad_medida: 'UND', categoria_id: '', precio_compra: '', precio_venta: '', stock_actual: 0, stock_minimo: 5, marca: '' }
    modal.value = true
}

const editarProducto = (p) => {
    editando.value = true
    form.value = { ...p, categoria_id: p.categoria_id || '' }
    modal.value = true
}

const guardar = () => {
    if (editando.value) {
        router.put(`/ferreteria/productos/${form.value.id}`, form.value, { onSuccess: () => modal.value = false })
    } else {
        router.post('/ferreteria/productos', form.value, { onSuccess: () => modal.value = false })
    }
}

const ajustarStock = (p) => {
    productoSeleccionado.value = p
    tipoAjuste.value = 'entrada'
    cantidadAjuste.value = ''
    modalStock.value = true
}

const guardarStock = () => {
    router.post(`/ferreteria/productos/${productoSeleccionado.value.id}/stock`, {
        tipo: tipoAjuste.value,
        cantidad: cantidadAjuste.value
    }, { onSuccess: () => modalStock.value = false })
}

const eliminarProducto = (p) => {
    if (confirm(`¿Eliminar ${p.descripcion}?`)) {
        router.delete(`/ferreteria/productos/${p.id}`)
    }
}
</script>
