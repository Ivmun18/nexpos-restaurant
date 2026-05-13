<template>
    <AppLayout title="Recetas" subtitle="Vincula los productos del menú con los insumos del inventario">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

            <!-- LISTA DE PRODUCTOS -->
            <div>
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                    <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0;">📋 Productos del menú</p>
                    <input v-model="buscar" placeholder="Buscar..." style="padding:7px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; width:160px;" />
                </div>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <div v-for="p in productosFiltrados" :key="p.id"
                        @click="seleccionar(p)"
                        :style="{
                            background: productoSeleccionado?.id === p.id ? '#F0FDFA' : 'white',
                            border: productoSeleccionado?.id === p.id ? '2px solid #14B8A6' : '1px solid #E2E8F0',
                            borderRadius: '10px', padding: '12px 14px', cursor: 'pointer', transition: 'all .15s'
                        }">
                        <div style="display:flex; align-items:center; justify-content:space-between;">
                            <div>
                                <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0;">{{ p.nombre }}</p>
                                <p style="font-size:11px; color:#94A3B8; margin:2px 0 0;">S/ {{ Number(p.precio).toFixed(2) }}</p>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span v-if="p.recetas.length > 0" style="background:#F0FDFA; color:#0F766E; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:600;">
                                    {{ p.recetas.length }} insumo{{ p.recetas.length > 1 ? 's' : '' }}
                                </span>
                                <span v-else style="background:#FEF2F2; color:#991B1B; padding:3px 8px; border-radius:20px; font-size:11px; font-weight:600;">
                                    Sin receta
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RECETA DEL PRODUCTO SELECCIONADO -->
            <div>
                <div v-if="!productoSeleccionado" style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:3rem; text-align:center; color:#94A3B8;">
                    <p style="font-size:32px; margin:0 0 8px;">👈</p>
                    <p style="font-size:13px; margin:0;">Selecciona un producto para ver o editar su receta</p>
                </div>

                <div v-else style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
                    <div style="padding:1rem 1.25rem; border-bottom:1px solid #F1F5F9; background:#F8FAFC;">
                        <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0;">🍽️ {{ productoSeleccionado.nombre }}</p>
                        <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">S/ {{ Number(productoSeleccionado.precio).toFixed(2) }} · {{ productoSeleccionado.recetas.length }} insumos en receta</p>
                    </div>

                    <!-- Insumos actuales -->
                    <div style="padding:1rem 1.25rem;">
                        <p style="font-size:12px; font-weight:600; color:#64748B; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">Insumos de la receta</p>
                        <div v-if="productoSeleccionado.recetas.length === 0" style="text-align:center; color:#94A3B8; padding:1.5rem; font-size:13px;">
                            Sin insumos — agrega los ingredientes abajo
                        </div>
                        <div v-for="r in productoSeleccionado.recetas" :key="r.id"
                            style="display:flex; align-items:center; gap:10px; padding:8px 10px; background:#F8FAFC; border-radius:8px; margin-bottom:6px;">
                            <span style="font-size:13px; font-weight:600; color:#1E293B; flex:1;">{{ r.insumo_nombre }}</span>
                            <span style="font-size:12px; color:#64748B; background:#E2E8F0; padding:2px 8px; border-radius:20px;">{{ Number(r.cantidad).toFixed(3) }} {{ r.unidad }}</span>
                            <button @click="eliminarInsumo(r.id)" style="background:#FEE2E2; color:#991B1B; border:none; border-radius:6px; padding:3px 8px; font-size:11px; cursor:pointer; font-weight:600;">✕</button>
                        </div>
                    </div>

                    <!-- Agregar insumo -->
                    <div style="padding:1rem 1.25rem; border-top:1px solid #F1F5F9; background:#FAFAFA;">
                        <p style="font-size:12px; font-weight:600; color:#64748B; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">+ Agregar insumo</p>
                        <div style="display:grid; grid-template-columns:1fr auto auto; gap:8px; align-items:end;">
                            <div>
                                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px;">Insumo</label>
                                <select v-model="formAgregar.insumo_id" style="width:100%; padding:8px 10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                                    <option value="">Seleccionar...</option>
                                    <option v-for="i in insumos" :key="i.id" :value="i.id">{{ i.nombre }} ({{ i.unidad_medida }})</option>
                                </select>
                            </div>
                            <div>
                                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px;">Cantidad</label>
                                <input v-model="formAgregar.cantidad" type="number" step="0.001" min="0.001" placeholder="0.000"
                                    style="width:80px; padding:8px 10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                            </div>
                            <button @click="agregarInsumo" style="padding:8px 16px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; white-space:nowrap;">
                                + Agregar
                            </button>
                        </div>
                    </div>

                    <!-- Costo estimado -->
                    <div v-if="productoSeleccionado.recetas.length > 0" style="padding:1rem 1.25rem; border-top:1px solid #F1F5F9;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <p style="font-size:12px; color:#64748B; margin:0;">Costo estimado de ingredientes</p>
                            <p style="font-size:15px; font-weight:800; color:#0F766E; margin:0;">S/ {{ costoEstimado.toFixed(2) }}</p>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:4px;">
                            <p style="font-size:12px; color:#64748B; margin:0;">Margen estimado</p>
                            <p style="font-size:15px; font-weight:800; margin:0;"
                                :style="margen >= 0 ? {color:'#0F766E'} : {color:'#EF4444'}">
                                S/ {{ margen.toFixed(2) }} ({{ productoSeleccionado.precio > 0 ? Math.round(margen / productoSeleccionado.precio * 100) : 0 }}%)
                            </p>
                        </div>
                    </div>
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
    insumos:   { type: Array, default: () => [] },
})

const buscar              = ref('')
const productoSeleccionado = ref(null)
const formAgregar         = ref({ insumo_id: '', cantidad: '' })

const productosFiltrados = computed(() => {
    if (!buscar.value) return props.productos
    return props.productos.filter(p => p.nombre.toLowerCase().includes(buscar.value.toLowerCase()))
})

const costoEstimado = computed(() => {
    if (!productoSeleccionado.value) return 0
    return productoSeleccionado.value.recetas.reduce((sum, r) => {
        const insumo = props.insumos.find(i => i.id === r.insumo_id)
        return sum + (insumo ? Number(r.cantidad) * Number(insumo.stock_actual) * 0 : 0)
    }, 0)
})

const margen = computed(() => {
    if (!productoSeleccionado.value) return 0
    return Number(productoSeleccionado.value.precio) - costoEstimado.value
})

function seleccionar(producto) {
    productoSeleccionado.value = { ...producto }
    formAgregar.value = { insumo_id: '', cantidad: '' }
}

function agregarInsumo() {
    if (!formAgregar.value.insumo_id || !formAgregar.value.cantidad) {
        alert('Selecciona el insumo y la cantidad')
        return
    }
    router.post('/recetas', {
        menu_producto_id: productoSeleccionado.value.id,
        insumo_id:        formAgregar.value.insumo_id,
        cantidad:         formAgregar.value.cantidad,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            formAgregar.value = { insumo_id: '', cantidad: '' }
            // Actualizar producto seleccionado
            const updated = props.productos.find(p => p.id === productoSeleccionado.value.id)
            if (updated) productoSeleccionado.value = { ...updated }
        }
    })
}

function eliminarInsumo(recetaId) {
    if (!confirm('¿Eliminar este insumo de la receta?')) return
    router.delete(`/recetas/${recetaId}`, {
        preserveScroll: true,
        onSuccess: () => {
            const updated = props.productos.find(p => p.id === productoSeleccionado.value.id)
            if (updated) productoSeleccionado.value = { ...updated }
        }
    })
}
</script>
