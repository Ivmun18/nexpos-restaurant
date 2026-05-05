<template>
    <AppLayout title="Productos" subtitle="Catálogo de productos y servicios">

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
            <input v-model="busqueda" type="text" placeholder="Buscar por código o descripción..."
                style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; color:#1E293B; background:white; outline:none; width:320px;"/>
            <button @click="abrirModal(null)"
                style="padding:10px 20px; background:#2563EB; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">
                + Nuevo producto
            </button>
        </div>
	<!-- Alerta stock bajo -->
        <div v-if="stockBajo.length > 0"
            style="background:#FEF2F2; border:1px solid #FECACA; border-radius:10px; padding:14px 16px; margin-bottom:1rem; display:flex; align-items:center; gap:12px;">
            <svg width="20" height="20" fill="none" stroke="#EF4444" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
            </svg>
            <div>
                <p style="font-size:13px; font-weight:600; color:#991B1B; margin:0;">Stock bajo — {{ stockBajo.length }} producto(s) por reabastecer</p>
                <p style="font-size:12px; color:#B91C1C; margin:4px 0 0;">
                    {{ stockBajo.map(p => p.descripcion).join(', ') }}
                </p>
            </div>
        </div>

        <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Código</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Descripción</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Tipo</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Unidad</th>
                        <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Precio</th>
                        <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Stock</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">IGV</th>
                        <th style="padding:12px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="productosFiltrados.length === 0">
                        <td colspan="8" style="padding:3rem; text-align:center; color:#94A3B8; font-size:14px;">No hay productos registrados</td>
                    </tr>
          	    <tr v-for="p in productosFiltrados" :key="p.id" :style="Number(p.stock_actual) <= Number(p.stock_minimo) && p.controla_stock ? 'border-top:1px solid #FECACA; background:#FEF2F2;' : 'border-top:1px solid #F1F5F9;'">
                        <td style="padding:12px 16px; font-size:13px; color:#64748B; font-family:monospace;">{{ p.codigo }}</td>
                        <td style="padding:12px 16px; font-size:13px; font-weight:500; color:#1E293B;">{{ p.descripcion }}</td>
                        <td style="padding:12px 16px;">
                            <span :style="tipoStyle(p.tipo)">{{ p.tipo }}</span>
                        </td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ p.unidad_medida }}</td>
                        <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#1E293B; text-align:right;">S/ {{ Number(p.precio_venta).toFixed(2) }}</td>
                        <td style="padding:12px 16px; font-size:13px; font-weight:600; text-align:right;" :style="Number(p.stock_actual) <= Number(p.stock_minimo) ? {color:'#EF4444'} : {color:'#166534'}">
                            {{ Number(p.stock_actual).toFixed(0) }}
                        </td>
                        <td style="padding:12px 16px;">
                            <span v-if="p.afecto_igv" style="font-size:11px; background:#EFF6FF; color:#1D4ED8; padding:2px 8px; border-radius:20px;">18%</span>
                            <span v-else style="font-size:11px; background:#F1F5F9; color:#64748B; padding:2px 8px; border-radius:20px;">Exonerado</span>
                        </td>
                        <td style="padding:12px 16px; text-align:center;">
                            <div style="display:flex; gap:8px; justify-content:center;">
                                <button @click="abrirModal(p)" style="padding:6px 14px; background:#EFF6FF; color:#2563EB; border:none; border-radius:6px; font-size:12px; cursor:pointer; font-weight:500;">Editar</button>
                                <button @click="eliminar(p)" style="padding:6px 14px; background:#FEF2F2; color:#991B1B; border:none; border-radius:6px; font-size:12px; cursor:pointer; font-weight:500;">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="modal" style="position:fixed; inset:0; background:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; z-index:999;">
            <div style="background:white; border-radius:12px; padding:2rem; width:100%; max-width:560px; border:1px solid #E2E8F0;">
                
<p style="font-size:16px; font-weight:600; color:#1E293B; margin:0 0 1.5rem;">{{ form.id ? 'Editar producto' : 'Nuevo producto' }}</p>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Código *</label>
                        <input v-model="form.codigo" type="text" :disabled="!!form.id"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Tipo</label>
                        <select v-model="form.tipo" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; background:white;">
                            <option value="producto">Producto</option>
                            <option value="servicio">Servicio</option>
                            <option value="combo">Combo</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom:1rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Descripción *</label>
                    <input v-model="form.descripcion" type="text"
                        style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; margin-bottom:1rem;">
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Unidad</label>
                        <select v-model="form.unidad_medida" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; background:white;">
                            <option value="NIU">NIU - Unidad</option>
                            <option value="ZZ">ZZ - Servicio</option>
                            <option value="KGM">KGM - Kg</option>
                            <option value="LTR">LTR - Litro</option>
                            <option value="MTR">MTR - Metro</option>
                            <option value="BX">BX - Caja</option>
                            <option value="DZN">DZN - Docena</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Precio venta (S/) *</label>
                        <input v-model="form.precio_venta" type="number" step="0.01" min="0"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Precio compra (S/)</label>
                        <input v-model="form.precio_compra" type="number" step="0.01" min="0"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; margin-bottom:1rem;">
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Afectación IGV</label>
                        <select v-model="form.tipo_afectacion_igv" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; background:white;">
                            <option value="10">10 - Gravado</option>
                            <option value="20">20 - Exonerado</option>
                            <option value="30">30 - Inafecto</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Stock actual</label>
                        <input v-model="form.stock_actual" type="number" step="1" min="0"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Stock mínimo</label>
                        <input v-model="form.stock_minimo" type="number" step="1" min="0"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                    </div>
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Código de barras</label>
                    <input v-model="form.codigo_barras" type="text" placeholder="Ej: 7750395001030"
                        style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box; font-family:monospace;"/>
                </div>



                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button @click="modal=false" style="padding:10px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Cancelar</button>
                    <button @click="guardar" style="padding:10px 20px; background:#2563EB; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">{{ form.id ? 'Actualizar' : 'Guardar' }}</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ productos: Object })

const busqueda = ref('')
const modal = ref(false)
const error = ref('')

const formVacio = () => ({
    id: null, codigo: '', descripcion: '', tipo: 'producto',
    unidad_medida: 'NIU', precio_venta: '', precio_compra: '',
    tipo_afectacion_igv: '10', controla_stock: true,
    stock_actual: 0, stock_minimo: 0, codigo_barras: '', activo: true,
})

const form = ref(formVacio())

const tipoStyle = (tipo) => {
    const map = {
        producto: { background:'#EFF6FF', color:'#1D4ED8' },
        servicio: { background:'#F0FDF4', color:'#166534' },
        combo:    { background:'#FFF7ED', color:'#9A3412' },
    }
    return { ...(map[tipo] || map.producto), fontSize:'11px', padding:'2px 8px', borderRadius:'20px' }
}

const productosFiltrados = computed(() => {
    const data = props.productos?.data || []
    if (!busqueda.value) return data
    const q = busqueda.value.toLowerCase()
    return data.filter(p => p.descripcion.toLowerCase().includes(q) || p.codigo.toLowerCase().includes(q))
})

const stockBajo = computed(() => {
    const data = props.productos?.data || []
    return data.filter(p => p.controla_stock && Number(p.stock_actual) <= 10)
})

const abrirModal = (producto) => {
    error.value = ''
    form.value = producto ? { ...producto } : formVacio()
    modal.value = true
}

const guardar = () => {
    error.value = ''
    if (!form.value.codigo || !form.value.descripcion || !form.value.precio_venta) {
        error.value = 'Código, descripción y precio son obligatorios.'
        return
    }
    if (form.value.id) {
        router.put(`/productos/${form.value.id}`, form.value, { onSuccess: () => { modal.value = false } })
    } else {
        router.post('/productos', form.value, { onSuccess: () => { modal.value = false } })
    }
}

const eliminar = (p) => {
    if (confirm(`¿Eliminar "${p.descripcion}"?`)) {
        router.delete(`/productos/${p.id}`)
    }
}
</script>