<template>
    <AppLayout title="Garantías" subtitle="Control de garantías de productos">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <div style="display:flex; gap:12px;">
                <input v-model="busqueda" placeholder="🔍 Buscar por número, cliente o producto..."
                    style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; width:300px; outline:none;">
                <select v-model="filtroEstado" style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none;">
                    <option value="">Todos</option>
                    <option value="activa">Activa</option>
                    <option value="vencida">Vencida</option>
                    <option value="reclamada">Reclamada</option>
                    <option value="anulada">Anulada</option>
                </select>
            </div>
            <button @click="abrirModal()" style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                + Nueva Garantía
            </button>
        </div>

        <!-- Stats -->
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Total</p>
                <p style="font-size:24px; font-weight:700; color:#1E293B; margin:0;">{{ garantias.length }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Activas</p>
                <p style="font-size:24px; font-weight:700; color:#16A34A; margin:0;">{{ garantias.filter(g=>g.estado==='activa').length }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Vencidas</p>
                <p style="font-size:24px; font-weight:700; color:#DC2626; margin:0;">{{ garantias.filter(g=>g.estado==='vencida').length }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Reclamadas</p>
                <p style="font-size:24px; font-weight:700; color:#F59E0B; margin:0;">{{ garantias.filter(g=>g.estado==='reclamada').length }}</p>
            </div>
        </div>

        <!-- Tabla -->
        <div style="background:white; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">N° GARANTÍA</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">CLIENTE</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">PRODUCTO</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">COMPRA</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">VENCE</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; font-weight:700; color:#64748B;">ESTADO</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; font-weight:700; color:#64748B;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="garantiasFiltradas.length === 0">
                        <td colspan="7" style="padding:40px; text-align:center; color:#94A3B8;">No hay garantías registradas</td>
                    </tr>
                    <tr v-for="g in garantiasFiltradas" :key="g.id"
                        style="border-top:1px solid #F1F5F9;"
                        @mouseenter="e => e.currentTarget.style.background='#F8FAFC'"
                        @mouseleave="e => e.currentTarget.style.background='white'">
                        <td style="padding:14px 20px; font-size:14px; font-weight:700; color:#14B8A6;">{{ g.numero }}</td>
                        <td style="padding:14px 20px;">
                            <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ g.cliente_nombre }}</p>
                            <p v-if="g.cliente_telefono" style="font-size:12px; color:#94A3B8; margin:0;">{{ g.cliente_telefono }}</p>
                        </td>
                        <td style="padding:14px 20px;">
                            <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0;">{{ g.producto_descripcion }}</p>
                            <p style="font-size:12px; color:#94A3B8; margin:0;">{{ g.marca }} {{ g.modelo }} {{ g.serie ? '· S/N: '+g.serie : '' }}</p>
                        </td>
                        <td style="padding:14px 20px; font-size:13px; color:#475569;">{{ g.fecha_compra }}</td>
                        <td style="padding:14px 20px; font-size:13px;" :style="{color: g.estado==='vencida' ? '#DC2626' : '#475569'}">
                            {{ g.fecha_vencimiento }}
                            <p style="font-size:11px; color:#94A3B8; margin:0;">{{ g.meses_garantia }} meses</p>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <span :style="estadoStyle(g.estado)">{{ g.estado }}</span>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <div style="display:flex; gap:6px; justify-content:center;">
                                <button @click="abrirModal(g)" style="padding:6px 10px; background:#F0FDFA; color:#0F766E; border:1px solid #CCFBF1; border-radius:8px; font-size:12px; cursor:pointer;">✏️</button>
                                <button v-if="g.estado==='activa'" @click="cambiarEstado(g,'reclamada')" style="padding:6px 10px; background:#FEF3C7; color:#92400E; border:1px solid #FDE68A; border-radius:8px; font-size:12px; cursor:pointer;">⚠️</button>
                                <button @click="eliminar(g)" style="padding:6px 10px; background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; border-radius:8px; font-size:12px; cursor:pointer;">🗑️</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="modal" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:flex-start; justify-content:center; overflow-y:auto; padding:20px;">
            <div style="background:white; border-radius:16px; padding:28px; width:580px; margin:auto;">
                <h2 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 20px;">{{ editando ? 'Editar Garantía' : 'Nueva Garantía' }}</h2>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">CLIENTE *</label>
                        <input v-model="form.cliente_nombre" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">TELÉFONO</label>
                        <input v-model="form.cliente_telefono" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">PRODUCTO *</label>
                        <input v-model="form.producto_descripcion" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">MARCA</label>
                        <input v-model="form.marca" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">MODELO</label>
                        <input v-model="form.modelo" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">N° SERIE</label>
                        <input v-model="form.serie" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">FECHA COMPRA</label>
                        <input v-model="form.fecha_compra" type="date" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">MESES GARANTÍA</label>
                        <select v-model="form.meses_garantia" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                            <option :value="3">3 meses</option>
                            <option :value="6">6 meses</option>
                            <option :value="12">12 meses</option>
                            <option :value="24">24 meses</option>
                            <option :value="36">36 meses</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">FECHA VENCIMIENTO</label>
                        <input v-model="form.fecha_vencimiento" type="date" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">ESTADO</label>
                        <select v-model="form.estado" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                            <option value="activa">Activa</option>
                            <option value="vencida">Vencida</option>
                            <option value="reclamada">Reclamada</option>
                            <option value="anulada">Anulada</option>
                        </select>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">CONDICIONES</label>
                        <textarea v-model="form.condiciones" rows="2" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box; resize:none;"></textarea>
                    </div>
                </div>

                <div style="display:flex; gap:12px; margin-top:24px; justify-content:flex-end;">
                    <button @click="modal=false" style="padding:10px 20px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; cursor:pointer; background:white;">Cancelar</button>
                    <button @click="guardar" :disabled="procesando" style="padding:10px 24px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">
                        {{ procesando ? 'Guardando...' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ garantias: { type: Array, default: () => [] } })

const busqueda    = ref('')
const filtroEstado = ref('')
const modal       = ref(false)
const editando    = ref(null)
const procesando  = ref(false)

const hoy = new Date().toISOString().split('T')[0]

const form = ref({
    cliente_nombre: '', cliente_telefono: '', producto_descripcion: '',
    marca: '', modelo: '', serie: '', fecha_compra: hoy,
    meses_garantia: 12, fecha_vencimiento: '', estado: 'activa', condiciones: ''
})

// Auto calcular fecha vencimiento
watch([() => form.value.fecha_compra, () => form.value.meses_garantia], ([fecha, meses]) => {
    if (fecha && meses) {
        const d = new Date(fecha)
        d.setMonth(d.getMonth() + parseInt(meses))
        form.value.fecha_vencimiento = d.toISOString().split('T')[0]
    }
})

const garantiasFiltradas = computed(() => {
    return props.garantias.filter(g => {
        const q = busqueda.value.toLowerCase()
        const matchBus = !q || g.numero?.includes(q) || g.cliente_nombre?.toLowerCase().includes(q) || g.producto_descripcion?.toLowerCase().includes(q)
        const matchEst = !filtroEstado.value || g.estado === filtroEstado.value
        return matchBus && matchEst
    })
})

const estadoStyle = (estado) => {
    const e = {
        activa:    { padding:'4px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'700', background:'#F0FDF4', color:'#166534' },
        vencida:   { padding:'4px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'700', background:'#FEF2F2', color:'#991B1B' },
        reclamada: { padding:'4px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'700', background:'#FEF3C7', color:'#92400E' },
        anulada:   { padding:'4px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'700', background:'#F1F5F9', color:'#475569' },
    }
    return e[estado] || e.activa
}

const abrirModal = (g = null) => {
    editando.value = g
    form.value = g ? { ...g } : {
        cliente_nombre: '', cliente_telefono: '', producto_descripcion: '',
        marca: '', modelo: '', serie: '', fecha_compra: hoy,
        meses_garantia: 12, fecha_vencimiento: '', estado: 'activa', condiciones: ''
    }
    modal.value = true
}

const guardar = () => {
    procesando.value = true
    if (editando.value) {
        router.put(`/ferreteria/garantias/${editando.value.id}`, form.value, {
            onSuccess: () => { modal.value = false; procesando.value = false },
            onError:   () => { procesando.value = false }
        })
    } else {
        router.post('/ferreteria/garantias', form.value, {
            onSuccess: () => { modal.value = false; procesando.value = false },
            onError:   () => { procesando.value = false }
        })
    }
}

const cambiarEstado = (g, estado) => {
    router.patch(`/ferreteria/garantias/${g.id}/estado`, { estado })
}

const eliminar = (g) => {
    if (confirm(`¿Eliminar garantía ${g.numero}?`)) {
        router.delete(`/ferreteria/garantias/${g.id}`)
    }
}
</script>
