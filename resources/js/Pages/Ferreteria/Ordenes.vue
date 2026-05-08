<template>
    <AppLayout title="Órdenes de Trabajo" subtitle="Gestión de servicios e instalaciones">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <div style="display:flex; gap:12px;">
                <input v-model="busqueda" placeholder="🔍 Buscar por número o cliente..."
                    style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; width:260px; outline:none;">
                <select v-model="filtroEstado" style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none;">
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="en_proceso">En Proceso</option>
                    <option value="completada">Completada</option>
                    <option value="entregada">Entregada</option>
                    <option value="cancelada">Cancelada</option>
                </select>
            </div>
            <button @click="abrirModal()" style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                + Nueva Orden
            </button>
        </div>

        <!-- Stats -->
        <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:16px; margin-bottom:24px;">
            <div v-for="(s, key) in stats" :key="key" style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">{{ s.label }}</p>
                <p :style="{fontSize:'24px', fontWeight:'700', color: s.color, margin:0}">{{ s.count }}</p>
            </div>
        </div>

        <!-- Kanban / Tabla -->
        <div style="background:white; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">N° ORDEN</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">CLIENTE</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">TÍTULO</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; font-weight:700; color:#64748B;">PRIORIDAD</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">FECHA EST.</th>
                        <th style="padding:14px 20px; text-align:right; font-size:12px; font-weight:700; color:#64748B;">TOTAL</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; font-weight:700; color:#64748B;">ESTADO</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; font-weight:700; color:#64748B;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="ordenesFiltradas.length === 0">
                        <td colspan="8" style="padding:40px; text-align:center; color:#94A3B8;">No hay órdenes registradas</td>
                    </tr>
                    <tr v-for="o in ordenesFiltradas" :key="o.id"
                        style="border-top:1px solid #F1F5F9;"
                        @mouseenter="e => e.currentTarget.style.background='#F8FAFC'"
                        @mouseleave="e => e.currentTarget.style.background='white'">
                        <td style="padding:14px 20px; font-size:14px; font-weight:700; color:#14B8A6;">{{ o.numero }}</td>
                        <td style="padding:14px 20px;">
                            <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ o.cliente_nombre }}</p>
                            <p v-if="o.cliente_telefono" style="font-size:12px; color:#94A3B8; margin:0;">{{ o.cliente_telefono }}</p>
                        </td>
                        <td style="padding:14px 20px; font-size:13px; color:#475569; max-width:200px;">
                            <p style="margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ o.titulo }}</p>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <span :style="prioridadStyle(o.prioridad)">{{ o.prioridad }}</span>
                        </td>
                        <td style="padding:14px 20px; font-size:13px; color:#475569;">{{ o.fecha_estimada || '—' }}</td>
                        <td style="padding:14px 20px; text-align:right; font-size:14px; font-weight:700; color:#14B8A6;">S/ {{ Number(o.total).toFixed(2) }}</td>
                        <td style="padding:14px 20px; text-align:center;">
                            <select @change="e => cambiarEstado(o, e.target.value)" :value="o.estado"
                                :style="{ ...estadoStyle(o.estado), border:'none', cursor:'pointer', outline:'none' }">
                                <option value="pendiente">pendiente</option>
                                <option value="en_proceso">en_proceso</option>
                                <option value="completada">completada</option>
                                <option value="entregada">entregada</option>
                                <option value="cancelada">cancelada</option>
                            </select>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <div style="display:flex; gap:6px; justify-content:center;">
                                <button @click="abrirModal(o)" style="padding:6px 10px; background:#F0FDFA; color:#0F766E; border:1px solid #CCFBF1; border-radius:8px; font-size:12px; cursor:pointer;">✏️</button>
                                <button @click="eliminar(o)" style="padding:6px 10px; background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; border-radius:8px; font-size:12px; cursor:pointer;">🗑️</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="modal" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:flex-start; justify-content:center; overflow-y:auto; padding:20px;">
            <div style="background:white; border-radius:16px; padding:28px; width:620px; margin:auto;">
                <h2 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 20px;">{{ editando ? 'Editar Orden' : 'Nueva Orden de Trabajo' }}</h2>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">TÍTULO / SERVICIO *</label>
                        <input v-model="form.titulo" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">CLIENTE *</label>
                        <input v-model="form.cliente_nombre" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">TELÉFONO</label>
                        <input v-model="form.cliente_telefono" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">PRIORIDAD</label>
                        <select v-model="form.prioridad" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                            <option value="baja">Baja</option>
                            <option value="normal">Normal</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">ESTADO</label>
                        <select v-model="form.estado" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="completada">Completada</option>
                            <option value="entregada">Entregada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">FECHA INGRESO</label>
                        <input v-model="form.fecha_ingreso" type="date" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">FECHA ESTIMADA</label>
                        <input v-model="form.fecha_estimada" type="date" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">DESCRIPCIÓN / PROBLEMA</label>
                        <textarea v-model="form.descripcion" rows="2" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box; resize:none;"></textarea>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">DIAGNÓSTICO</label>
                        <textarea v-model="form.diagnostico" rows="2" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box; resize:none;"></textarea>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">TRABAJOS REALIZADOS</label>
                        <textarea v-model="form.trabajos_realizados" rows="2" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box; resize:none;"></textarea>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">COSTO MANO DE OBRA (S/)</label>
                        <input v-model.number="form.costo_mano_obra" type="number" step="0.01" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">COSTO MATERIALES (S/)</label>
                        <input v-model.number="form.costo_materiales" type="number" step="0.01" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div style="grid-column:1/-1; background:#F0FDFA; border-radius:8px; padding:12px; text-align:right;">
                        <span style="font-size:15px; font-weight:700; color:#0F766E;">Total: S/ {{ totalOrden }}</span>
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
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ ordenes: { type: Array, default: () => [] } })

const busqueda    = ref('')
const filtroEstado = ref('')
const modal       = ref(false)
const editando    = ref(null)
const procesando  = ref(false)

const hoy = new Date().toISOString().split('T')[0]

const form = ref({
    titulo: '', cliente_nombre: '', cliente_telefono: '',
    prioridad: 'normal', estado: 'pendiente',
    fecha_ingreso: hoy, fecha_estimada: '',
    descripcion: '', diagnostico: '', trabajos_realizados: '',
    costo_mano_obra: 0, costo_materiales: 0
})

const totalOrden = computed(() => (parseFloat(form.value.costo_mano_obra || 0) + parseFloat(form.value.costo_materiales || 0)).toFixed(2))

const stats = computed(() => ({
    pendiente:  { label: 'Pendientes',  color: '#F59E0B', count: props.ordenes.filter(o=>o.estado==='pendiente').length },
    en_proceso: { label: 'En Proceso',  color: '#3B82F6', count: props.ordenes.filter(o=>o.estado==='en_proceso').length },
    completada: { label: 'Completadas', color: '#16A34A', count: props.ordenes.filter(o=>o.estado==='completada').length },
    entregada:  { label: 'Entregadas',  color: '#14B8A6', count: props.ordenes.filter(o=>o.estado==='entregada').length },
    cancelada:  { label: 'Canceladas',  color: '#DC2626', count: props.ordenes.filter(o=>o.estado==='cancelada').length },
}))

const ordenesFiltradas = computed(() => {
    return props.ordenes.filter(o => {
        const q = busqueda.value.toLowerCase()
        const matchBus = !q || o.numero?.includes(q) || o.cliente_nombre?.toLowerCase().includes(q) || o.titulo?.toLowerCase().includes(q)
        const matchEst = !filtroEstado.value || o.estado === filtroEstado.value
        return matchBus && matchEst
    })
})

const estadoStyle = (estado) => {
    const e = {
        pendiente:  { padding:'4px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'700', background:'#FEF3C7', color:'#92400E' },
        en_proceso: { padding:'4px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'700', background:'#EFF6FF', color:'#1D4ED8' },
        completada: { padding:'4px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'700', background:'#F0FDF4', color:'#166534' },
        entregada:  { padding:'4px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'700', background:'#F0FDFA', color:'#0F766E' },
        cancelada:  { padding:'4px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'700', background:'#FEF2F2', color:'#991B1B' },
    }
    return e[estado] || e.pendiente
}

const prioridadStyle = (p) => {
    const s = {
        baja:    { padding:'2px 8px', borderRadius:'20px', fontSize:'11px', fontWeight:'700', background:'#F1F5F9', color:'#64748B' },
        normal:  { padding:'2px 8px', borderRadius:'20px', fontSize:'11px', fontWeight:'700', background:'#EFF6FF', color:'#1D4ED8' },
        alta:    { padding:'2px 8px', borderRadius:'20px', fontSize:'11px', fontWeight:'700', background:'#FEF3C7', color:'#92400E' },
        urgente: { padding:'2px 8px', borderRadius:'20px', fontSize:'11px', fontWeight:'700', background:'#FEF2F2', color:'#DC2626' },
    }
    return s[p] || s.normal
}

const abrirModal = (o = null) => {
    editando.value = o
    form.value = o ? { ...o } : {
        titulo: '', cliente_nombre: '', cliente_telefono: '',
        prioridad: 'normal', estado: 'pendiente',
        fecha_ingreso: hoy, fecha_estimada: '',
        descripcion: '', diagnostico: '', trabajos_realizados: '',
        costo_mano_obra: 0, costo_materiales: 0
    }
    modal.value = true
}

const guardar = () => {
    procesando.value = true
    const total = parseFloat(form.value.costo_mano_obra || 0) + parseFloat(form.value.costo_materiales || 0)
    const data = { ...form.value, total }
    if (editando.value) {
        router.put(`/ferreteria/ordenes/${editando.value.id}`, data, {
            onSuccess: () => { modal.value = false; procesando.value = false },
            onError:   () => { procesando.value = false }
        })
    } else {
        router.post('/ferreteria/ordenes', data, {
            onSuccess: () => { modal.value = false; procesando.value = false },
            onError:   () => { procesando.value = false }
        })
    }
}

const cambiarEstado = (o, estado) => {
    router.patch(`/ferreteria/ordenes/${o.id}/estado`, { estado })
}

const eliminar = (o) => {
    if (confirm(`¿Eliminar orden ${o.numero}?`)) {
        router.delete(`/ferreteria/ordenes/${o.id}`)
    }
}
</script>
