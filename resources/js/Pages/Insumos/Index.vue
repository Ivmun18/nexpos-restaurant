<template>
    <AppLayout title="Inventario de insumos" subtitle="Control de stock de ingredientes y materiales">

        <!-- ALERTAS DE STOCK BAJO -->
        <div v-if="alertas > 0" style="background:#FEF2F2; border:1px solid #FECACA; border-radius:10px; padding:12px 16px; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px;">
            <span style="font-size:20px;">⚠️</span>
            <p style="font-size:13px; color:#991B1B; font-weight:600; margin:0;">{{ alertas }} insumo{{ alertas > 1 ? 's' : '' }} con stock bajo o agotado</p>
            <button @click="filtros.alerta = filtros.alerta ? '' : '1'; buscar()" style="margin-left:auto; padding:5px 14px; background:#EF4444; color:white; border:none; border-radius:6px; font-size:12px; cursor:pointer; font-weight:600;">
                {{ filtros.alerta ? 'Ver todos' : 'Ver alertas' }}
            </button>
        </div>

        <!-- HEADER ACCIONES -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <input v-model="filtros.buscar" @keyup.enter="buscar" placeholder="🔍 Buscar insumo..." style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; width:200px;" />
                <select v-model="filtros.categoria" @change="buscar" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                    <option value="">Todas las categorías</option>
                    <option v-for="c in categorias" :key="c" :value="c">{{ c }}</option>
                </select>
            </div>
            <button @click="modalNuevo = true" style="padding:9px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                + Nuevo insumo
            </button>
        </div>

        <!-- TABLA INSUMOS -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Insumo</th>
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Categoría</th>
                        <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Unidad</th>
                        <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Stock actual</th>
                        <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Stock mínimo</th>
                        <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Costo prom.</th>
                        <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Estado</th>
                        <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="insumos.length === 0">
                        <td colspan="8" style="padding:2rem; text-align:center; color:#94A3B8;">Sin insumos registrados</td>
                    </tr>
                    <tr v-for="i in insumos" :key="i.id" style="border-top:1px solid #F1F5F9;"
                        @mouseover="e => e.currentTarget.style.background='#F8FAFC'"
                        @mouseleave="e => e.currentTarget.style.background='white'">
                        <td style="padding:12px 16px; font-weight:600; color:#1E293B;">{{ i.nombre }}</td>
                        <td style="padding:12px 16px; color:#64748B;">{{ i.categoria ?? '—' }}</td>
                        <td style="padding:12px 16px; text-align:center;">
                            <span style="background:#F1F5F9; color:#475569; padding:2px 8px; border-radius:20px; font-size:11px; font-weight:600;">{{ i.unidad_medida }}</span>
                        </td>
                        <td style="padding:12px 16px; text-align:right; font-weight:700;" :style="Number(i.stock_actual) <= Number(i.stock_minimo) ? {color:'#EF4444'} : {color:'#0F766E'}">
                            {{ Number(i.stock_actual).toFixed(2) }}
                        </td>
                        <td style="padding:12px 16px; text-align:right; color:#64748B;">{{ Number(i.stock_minimo).toFixed(2) }}</td>
                        <td style="padding:12px 16px; text-align:right; color:#64748B;">S/ {{ Number(i.precio_promedio).toFixed(2) }}</td>
                        <td style="padding:12px 16px; text-align:center;">
                            <span v-if="Number(i.stock_actual) <= 0" style="background:#FEE2E2; color:#991B1B; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;">⛔ Agotado</span>
                            <span v-else-if="Number(i.stock_actual) <= Number(i.stock_minimo)" style="background:#FEF3C7; color:#92400E; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;">⚠️ Bajo</span>
                            <span v-else style="background:#F0FDF4; color:#166534; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;">✅ OK</span>
                        </td>
                        <td style="padding:12px 16px; text-align:center; display:flex; gap:6px; justify-content:center;">
                            <button @click="abrirMovimiento(i)" style="padding:4px 10px; background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">📦 Mov.</button>
                            <button @click="abrirEditar(i)" style="padding:4px 10px; background:#F0FDFA; color:#0F766E; border:1px solid #99F6E4; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">✏️ Editar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- MODAL NUEVO INSUMO -->
        <div v-if="modalNuevo" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:100; display:flex; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:16px; padding:1.5rem; width:440px; max-width:95vw;">
                <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 1.2rem;">+ Nuevo insumo</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Nombre *</label>
                        <input v-model="formNuevo.nombre" type="text" placeholder="Ej: Pollo entero" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Categoría</label>
                        <input v-model="formNuevo.categoria" type="text" placeholder="Carnes, Verduras..." style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Unidad de medida *</label>
                        <select v-model="formNuevo.unidad_medida" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                            <option value="kg">kg</option>
                            <option value="lt">lt</option>
                            <option value="und">und</option>
                            <option value="caja">caja</option>
                            <option value="bolsa">bolsa</option>
                            <option value="botella">botella</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Stock inicial</label>
                        <input v-model="formNuevo.stock_actual" type="number" step="0.01" min="0" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Stock mínimo *</label>
                        <input v-model="formNuevo.stock_minimo" type="number" step="0.01" min="0" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:1rem;">
                    <button @click="modalNuevo = false" style="padding:9px 18px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Cancelar</button>
                    <button @click="guardarNuevo" style="padding:9px 18px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">Guardar</button>
                </div>
            </div>
        </div>

        <!-- MODAL MOVIMIENTO -->
        <div v-if="modalMovimiento" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:100; display:flex; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:16px; padding:1.5rem; width:400px; max-width:95vw;">
                <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 4px;">📦 Movimiento de stock</p>
                <p style="font-size:13px; color:#64748B; margin:0 0 1.2rem;">{{ insumoSeleccionado?.nombre }} — Stock actual: <strong>{{ Number(insumoSeleccionado?.stock_actual).toFixed(2) }} {{ insumoSeleccionado?.unidad_medida }}</strong></p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px;">Tipo de movimiento</label>
                        <div style="display:flex; gap:8px;">
                            <button v-for="t in ['entrada','salida','ajuste']" :key="t" @click="formMov.tipo = t"
                                :style="{padding:'7px 14px', borderRadius:'8px', border:'1.5px solid', fontSize:'12px', fontWeight:'600', cursor:'pointer',
                                    borderColor: formMov.tipo===t ? (t==='entrada'?'#14B8A6':t==='salida'?'#EF4444':'#F59E0B') : '#E2E8F0',
                                    background: formMov.tipo===t ? (t==='entrada'?'#F0FDFA':t==='salida'?'#FEF2F2':'#FFFBEB') : 'white',
                                    color: formMov.tipo===t ? (t==='entrada'?'#0F766E':t==='salida'?'#991B1B':'#92400E') : '#64748B'}">
                                {{ t==='entrada'?'⬆️ Entrada':t==='salida'?'⬇️ Salida':'🔄 Ajuste' }}
                            </button>
                        </div>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Cantidad *</label>
                        <input v-model="formMov.cantidad" type="number" step="0.01" min="0.001" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Costo unitario (S/)</label>
                        <input v-model="formMov.costo_unitario" type="number" step="0.01" min="0" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Motivo *</label>
                        <input v-model="formMov.motivo" type="text" placeholder="Ej: Compra, Merma, Inventario..." style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:1rem;">
                    <button @click="modalMovimiento = false" style="padding:9px 18px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Cancelar</button>
                    <button @click="guardarMovimiento" style="padding:9px 18px; background:#2563EB; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">Registrar</button>
                </div>
            </div>
        </div>

        <!-- MODAL EDITAR -->
        <div v-if="modalEditar" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:100; display:flex; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:16px; padding:1.5rem; width:400px; max-width:95vw;">
                <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 1.2rem;">✏️ Editar insumo</p>
                <div style="display:grid; gap:10px;">
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Nombre *</label>
                        <input v-model="formEditar.nombre" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Categoría</label>
                            <input v-model="formEditar.categoria" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Unidad</label>
                            <select v-model="formEditar.unidad_medida" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                                <option value="kg">kg</option>
                                <option value="lt">lt</option>
                                <option value="und">und</option>
                                <option value="caja">caja</option>
                                <option value="bolsa">bolsa</option>
                                <option value="botella">botella</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Stock mínimo *</label>
                        <input v-model="formEditar.stock_minimo" type="number" step="0.01" min="0" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:1rem;">
                    <button @click="modalEditar = false" style="padding:9px 18px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Cancelar</button>
                    <button @click="guardarEditar" style="padding:9px 18px; background:#14B8A6; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">Guardar</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    insumos:    { type: Array,  default: () => [] },
    categorias: { type: Array,  default: () => [] },
    alertas:    { type: Number, default: 0 },
    filtros:    { type: Object, default: () => ({}) },
})

const filtros           = ref({ ...props.filtros })
const modalNuevo        = ref(false)
const modalMovimiento   = ref(false)
const modalEditar       = ref(false)
const insumoSeleccionado = ref(null)

const formNuevo  = ref({ nombre: '', categoria: '', unidad_medida: 'kg', stock_actual: 0, stock_minimo: 0 })
const formMov    = ref({ tipo: 'entrada', cantidad: '', costo_unitario: '', motivo: '' })
const formEditar = ref({ nombre: '', categoria: '', unidad_medida: 'kg', stock_minimo: 0 })

function buscar() {
    const params = new URLSearchParams()
    if (filtros.value.buscar)    params.set('buscar', filtros.value.buscar)
    if (filtros.value.categoria) params.set('categoria', filtros.value.categoria)
    if (filtros.value.alerta)    params.set('alerta', filtros.value.alerta)
    router.visit('/insumos?' + params.toString(), { preserveScroll: true })
}

function guardarNuevo() {
    if (!formNuevo.value.nombre) { alert('Ingresa el nombre'); return }
    router.post('/insumos', formNuevo.value, {
        preserveScroll: true,
        onSuccess: () => { modalNuevo.value = false; formNuevo.value = { nombre: '', categoria: '', unidad_medida: 'kg', stock_actual: 0, stock_minimo: 0 } }
    })
}

function abrirMovimiento(insumo) {
    insumoSeleccionado.value = insumo
    formMov.value = { tipo: 'entrada', cantidad: '', costo_unitario: '', motivo: '' }
    modalMovimiento.value = true
}

function guardarMovimiento() {
    if (!formMov.value.cantidad || !formMov.value.motivo) { alert('Completa todos los campos'); return }
    router.post(`/insumos/${insumoSeleccionado.value.id}/movimiento`, formMov.value, {
        preserveScroll: true,
        onSuccess: () => { modalMovimiento.value = false }
    })
}

function abrirEditar(insumo) {
    insumoSeleccionado.value = insumo
    formEditar.value = { nombre: insumo.nombre, categoria: insumo.categoria, unidad_medida: insumo.unidad_medida, stock_minimo: insumo.stock_minimo }
    modalEditar.value = true
}

function guardarEditar() {
    router.put(`/insumos/${insumoSeleccionado.value.id}`, formEditar.value, {
        preserveScroll: true,
        onSuccess: () => { modalEditar.value = false }
    })
}
</script>
