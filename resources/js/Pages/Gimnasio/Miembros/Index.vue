<template>
    <AppLayout>
        <div style="padding:24px; max-width:1200px; margin:0 auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:20px; font-weight:700; color:#1E293B; margin:0;">👥 Miembros</h1>
                    <p style="color:#64748B; margin:4px 0 0;">{{ miembros.length }} registrados</p>
                </div>
                <button @click="showForm=true" style="background:#6D28D9; color:white; border:none; padding:10px 20px; border-radius:10px; font-weight:600; cursor:pointer;">+ Nuevo miembro</button>
            </div>

            <!-- Filtro -->
            <div style="display:flex; gap:12px; margin-bottom:20px;">
                <input v-model="buscar" placeholder="Buscar por nombre o DNI..." style="flex:1; padding:10px 14px; border:1.5px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none;" />
                <select v-model="filtroEstado" style="padding:10px 14px; border:1.5px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none;">
                    <option value="">Todos</option>
                    <option value="activo">Activos</option>
                    <option value="vencido">Vencidos</option>
                    <option value="suspendido">Suspendidos</option>
                </select>
            </div>

            <!-- Tabla -->
            <div style="background:white; border-radius:14px; border:1px solid #E2E8F0; overflow:hidden;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead style="background:#F8FAFC;">
                        <tr style="font-size:12px; color:#64748B; text-transform:uppercase; letter-spacing:1px;">
                            <th style="padding:12px 16px; text-align:left;">Miembro</th>
                            <th style="padding:12px 16px; text-align:left;">DNI / Tel</th>
                            <th style="padding:12px 16px; text-align:left;">Plan</th>
                            <th style="padding:12px 16px; text-align:left;">Vencimiento</th>
                            <th style="padding:12px 16px; text-align:left;">Estado</th>
                            <th style="padding:12px 16px; text-align:left;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="m in miembrosFiltrados" :key="m.id" style="border-top:1px solid #F1F5F9;" :style="{ background: m.dias_restantes !== null && m.dias_restantes <= 3 ? '#FFFBEB' : 'white' }">
                            <td style="padding:12px 16px;">
                                <p style="font-weight:600; color:#1E293B; margin:0;">{{ m.nombre }} {{ m.apellidos }}</p>
                            </td>
                            <td style="padding:12px 16px; color:#64748B; font-size:13px;">
                                <p style="margin:0;">{{ m.dni || '-' }}</p>
                                <p style="margin:0;">{{ m.telefono || '-' }}</p>
                            </td>
                            <td style="padding:12px 16px; color:#64748B;">{{ m.plan?.nombre || 'Sin plan' }}</td>
                            <td style="padding:12px 16px;">
                                <span v-if="m.membrecia_vencimiento">
                                    <p style="margin:0; font-size:13px; color:#374151;">{{ formatFecha(m.membrecia_vencimiento) }}</p>
                                    <p style="margin:0; font-size:11px;" :style="{ color: m.dias_restantes <= 3 ? '#DC2626' : m.dias_restantes <= 7 ? '#D97706' : '#16A34A' }">
                                        {{ m.dias_restantes >= 0 ? m.dias_restantes + ' días restantes' : 'Venció hace ' + Math.abs(m.dias_restantes) + ' días' }}
                                    </p>
                                </span>
                                <span v-else style="color:#94A3B8;">-</span>
                            </td>
                            <td style="padding:12px 16px;">
                                <span :style="estadoStyle(m.estado)">{{ m.estado }}</span>
                            </td>
                            <td style="padding:12px 16px;">
                                <button @click="abrirRenovar(m)" style="background:#6D28D9; color:white; border:none; padding:6px 12px; border-radius:8px; cursor:pointer; font-size:13px; margin-right:6px;">💳 Renovar</button>
                                <button @click="editar(m)" style="background:#F1F5F9; border:none; padding:6px 12px; border-radius:8px; cursor:pointer; font-size:13px;">✏️</button>
                            </td>
                        </tr>
                        <tr v-if="miembrosFiltrados.length === 0">
                            <td colspan="6" style="text-align:center; padding:40px; color:#94A3B8;">No hay miembros que coincidan</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal nuevo/editar miembro -->
            <div v-if="showForm" style="position:fixed; inset:0; background:rgba(0,0,0,0.45); display:flex; align-items:center; justify-content:center; z-index:999;">
                <div style="background:white; border-radius:16px; padding:28px; width:500px; max-width:95vw; max-height:90vh; overflow-y:auto;">
                    <h2 style="margin:0 0 20px; font-size:18px; font-weight:700;">{{ form.id ? 'Editar' : 'Nuevo' }} Miembro</h2>
                    <div style="display:grid; gap:14px;">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Nombre *</label>
                                <input v-model="form.nombre" type="text" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Apellidos *</label>
                                <input v-model="form.apellidos" type="text" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">DNI</label>
                                <input v-model="form.dni" type="text" maxlength="8" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Teléfono</label>
                                <input v-model="form.telefono" type="text" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                        </div>
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#374151;">Email</label>
                            <input v-model="form.email" type="email" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Fecha nacimiento</label>
                                <input v-model="form.fecha_nacimiento" type="date" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Sexo</label>
                                <select v-model="form.sexo" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;">
                                    <option value="">-</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                        </div>
                        <div v-if="!form.id">
                            <label style="font-size:13px; font-weight:600; color:#374151;">Plan inicial</label>
                            <select v-model="form.plan_id" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;">
                                <option value="">Sin plan por ahora</option>
                                <option v-for="p in planes" :key="p.id" :value="p.id">{{ p.nombre }} — S/ {{ p.precio }}</option>
                            </select>
                        </div>
                        <div v-if="!form.id && form.plan_id">
                            <label style="font-size:13px; font-weight:600; color:#374151;">Inicio membresía</label>
                            <input v-model="form.membrecia_inicio" type="date" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                        </div>
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#374151;">Observaciones</label>
                            <textarea v-model="form.observaciones" rows="2" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box; resize:none;"></textarea>
                        </div>
                    </div>
                    <div style="display:flex; gap:12px; margin-top:20px;">
                        <button @click="guardar" style="flex:1; background:#6D28D9; color:white; border:none; padding:12px; border-radius:10px; font-weight:700; cursor:pointer;">Guardar</button>
                        <button @click="cerrarForm" style="flex:1; background:#F1F5F9; border:none; padding:12px; border-radius:10px; font-weight:600; cursor:pointer;">Cancelar</button>
                    </div>
                </div>
            </div>

            <!-- Modal renovar -->
            <div v-if="showRenovar" style="position:fixed; inset:0; background:rgba(0,0,0,0.45); display:flex; align-items:center; justify-content:center; z-index:999;">
                <div style="background:white; border-radius:16px; padding:28px; width:440px; max-width:95vw;">
                    <h2 style="margin:0 0 6px; font-size:18px; font-weight:700;">💳 Renovar membresía</h2>
                    <p style="color:#64748B; margin:0 0 20px; font-size:14px;">{{ miembroSeleccionado?.nombre }} {{ miembroSeleccionado?.apellidos }}</p>
                    <div style="display:grid; gap:14px;">
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#374151;">Plan</label>
                            <select v-model="formRenovar.plan_id" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;">
                                <option v-for="p in planes" :key="p.id" :value="p.id">{{ p.nombre }} — S/ {{ p.precio }}</option>
                            </select>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Monto cobrado (S/)</label>
                                <input v-model="formRenovar.monto" type="number" min="0" step="0.01" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Fecha pago</label>
                                <input v-model="formRenovar.fecha_pago" type="date" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                        </div>
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#374151;">Método de pago</label>
                            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:6px;">
                                <button v-for="m in metodos" :key="m" @click="formRenovar.metodo_pago=m"
                                    :style="{ background: formRenovar.metodo_pago===m ? '#6D28D9' : '#F1F5F9', color: formRenovar.metodo_pago===m ? 'white' : '#374151', border:'none', padding:'8px 16px', borderRadius:'8px', cursor:'pointer', fontWeight:'600', fontSize:'13px' }">
                                    {{ m.charAt(0).toUpperCase() + m.slice(1) }}
                                </button>
                            </div>
                        </div>
                        <div v-if="['yape','plin','transferencia'].includes(formRenovar.metodo_pago)">
                            <label style="font-size:13px; font-weight:600; color:#374151;">N° operación / referencia</label>
                            <input v-model="formRenovar.referencia" type="text" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                        </div>
                    </div>
                    <div style="display:flex; gap:12px; margin-top:20px;">
                        <button @click="confirmarRenovar" style="flex:1; background:#6D28D9; color:white; border:none; padding:12px; border-radius:10px; font-weight:700; cursor:pointer;">✅ Confirmar pago</button>
                        <button @click="showRenovar=false" style="flex:1; background:#F1F5F9; border:none; padding:12px; border-radius:10px; font-weight:600; cursor:pointer;">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ miembros: Array, planes: Array })

const buscar = ref('')
const filtroEstado = ref('')
const showForm = ref(false)
const showRenovar = ref(false)
const miembroSeleccionado = ref(null)
const metodos = ['efectivo', 'yape', 'plin', 'transferencia', 'tarjeta']

const formVacio = { id:null, nombre:'', apellidos:'', dni:'', telefono:'', email:'', fecha_nacimiento:'', sexo:'', plan_id:'', membrecia_inicio: new Date().toISOString().split('T')[0], observaciones:'' }
const form = ref({ ...formVacio })
const formRenovar = ref({ plan_id:'', monto:0, metodo_pago:'efectivo', referencia:'', fecha_pago: new Date().toISOString().split('T')[0] })

const miembrosFiltrados = computed(() => props.miembros.filter(m => {
    const txt = buscar.value.toLowerCase()
    const coincide = !txt || (m.nombre + ' ' + m.apellidos).toLowerCase().includes(txt) || (m.dni || '').includes(txt)
    const estado = !filtroEstado.value || m.estado === filtroEstado.value
    return coincide && estado
}))

const formatFecha = (f) => f ? new Date(f + 'T00:00:00').toLocaleDateString('es-PE') : '-'

const estadoStyle = (e) => {
    const map = { activo: { background:'#D1FAE5', color:'#065F46' }, vencido: { background:'#FEE2E2', color:'#991B1B' }, suspendido: { background:'#FEF3C7', color:'#92400E' }, inactivo: { background:'#F1F5F9', color:'#64748B' } }
    return { ...map[e] || map.inactivo, padding:'3px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'700' }
}

const editar = (m) => { form.value = { ...m, plan_id: m.plan_id || '' }; showForm.value = true }
const cerrarForm = () => { form.value = { ...formVacio }; showForm.value = false }

const guardar = () => {
    if (form.value.id) {
        router.put('/gimnasio/miembros/' + form.value.id, form.value, { onSuccess: cerrarForm })
    } else {
        router.post('/gimnasio/miembros', form.value, { onSuccess: cerrarForm })
    }
}

const abrirRenovar = (m) => {
    miembroSeleccionado.value = m
    formRenovar.value = { plan_id: m.plan_id || props.planes[0]?.id || '', monto: m.plan?.precio || 0, metodo_pago:'efectivo', referencia:'', fecha_pago: new Date().toISOString().split('T')[0] }
    showRenovar.value = true
}

const confirmarRenovar = () => {
    router.post('/gimnasio/miembros/' + miembroSeleccionado.value.id + '/renovar', formRenovar.value, {
        onSuccess: () => { showRenovar.value = false }
    })
}
</script>
