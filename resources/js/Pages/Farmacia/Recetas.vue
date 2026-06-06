<template>
    <AppLayout title="Recetas Médicas">
        <div style="padding:24px; max-width:1400px; margin:0 auto;">

            <!-- Encabezado -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:24px; font-weight:800; color:#1E293B; margin:0;">📋 Recetas Médicas</h1>
                    <p style="color:#64748B; margin:4px 0 0; font-size:14px;">Registro y despacho de recetas</p>
                </div>
                <button @click="abrirFormulario" style="padding:10px 20px; background:#14B8A6; color:white; border:none; border-radius:10px; font-weight:600; cursor:pointer; font-size:14px;">
                    + Nueva Receta
                </button>
            </div>

            <!-- Panel split: Lista izquierda | Formulario derecha -->
            <div style="display:grid; grid-template-columns:1fr 420px; gap:24px; align-items:start;">

                <!-- Lista de recetas -->
                <div style="background:white; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden;">

                    <!-- Filtros -->
                    <div style="padding:16px; border-bottom:1px solid #E2E8F0; display:flex; gap:12px;">
                        <input v-model="buscar" @input="filtrar" type="text" placeholder="Buscar paciente, DNI, médico..."
                            style="flex:1; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;" />
                        <select v-model="estadoFiltro" @change="filtrar"
                            style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;">
                            <option value="">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="despachada">Despachada</option>
                            <option value="parcial">Parcial</option>
                        </select>
                    </div>

                    <!-- Tabla -->
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#F8FAFC;">
                                <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">PACIENTE</th>
                                <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">MÉDICO</th>
                                <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">FECHA</th>
                                <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">PRODUCTOS</th>
                                <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">ESTADO</th>
                                <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="recetas.data.length === 0">
                                <td colspan="6" style="padding:40px; text-align:center; color:#94A3B8;">No hay recetas registradas</td>
                            </tr>
                            <tr v-for="r in recetas.data" :key="r.id"
                                style="border-top:1px solid #F1F5F9; transition:background 0.15s;"
                                @mouseover="$event.currentTarget.style.background='#F8FAFC'"
                                @mouseleave="$event.currentTarget.style.background='white'">
                                <td style="padding:12px 16px;">
                                    <div style="font-weight:600; color:#1E293B; font-size:14px;">{{ r.paciente_nombre }}</div>
                                    <div v-if="r.paciente_dni" style="font-size:12px; color:#94A3B8;">DNI: {{ r.paciente_dni }}</div>
                                </td>
                                <td style="padding:12px 16px; font-size:14px; color:#475569;">
                                    <div>{{ r.medico || '—' }}</div>
                                    <div v-if="r.especialidad" style="font-size:12px; color:#94A3B8;">{{ r.especialidad }}</div>
                                </td>
                                <td style="padding:12px 16px; font-size:13px; color:#475569;">{{ r.fecha }}</td>
                                <td style="padding:12px 16px; font-size:13px; color:#475569;">{{ r.items?.length || 0 }} producto(s)</td>
                                <td style="padding:12px 16px;">
                                    <span :style="badgeEstado(r.estado)">{{ r.estado }}</span>
                                </td>
                                <td style="padding:12px 16px;">
                                    <button @click="verReceta(r)"
                                        style="padding:4px 10px; background:#EFF6FF; color:#3B82F6; border:none; border-radius:6px; font-size:12px; cursor:pointer; font-weight:600;">
                                        Ver
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Paginación -->
                    <div v-if="recetas.last_page > 1" style="padding:16px; display:flex; gap:8px; justify-content:center; border-top:1px solid #F1F5F9;">
                        <button v-for="p in recetas.last_page" :key="p" @click="irPagina(p)"
                            :style="p === recetas.current_page
                                ? 'padding:6px 12px; background:#14B8A6; color:white; border:none; border-radius:6px; cursor:pointer; font-weight:600;'
                                : 'padding:6px 12px; background:#F1F5F9; color:#475569; border:none; border-radius:6px; cursor:pointer;'">
                            {{ p }}
                        </button>
                    </div>
                </div>

                <!-- Panel derecho: Formulario o Detalle -->
                <div style="background:white; border-radius:16px; border:1px solid #E2E8F0; padding:24px;">

                    <!-- Ver detalle -->
                    <div v-if="recetaVista">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                            <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">Detalle de Receta</h3>
                            <button @click="recetaVista=null" style="background:none; border:none; color:#94A3B8; cursor:pointer; font-size:18px;">✕</button>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:10px; font-size:14px;">
                            <div><span style="color:#64748B;">Paciente:</span> <strong>{{ recetaVista.paciente_nombre }}</strong></div>
                            <div v-if="recetaVista.paciente_dni"><span style="color:#64748B;">DNI:</span> {{ recetaVista.paciente_dni }}</div>
                            <div v-if="recetaVista.medico"><span style="color:#64748B;">Médico:</span> {{ recetaVista.medico }}</div>
                            <div v-if="recetaVista.especialidad"><span style="color:#64748B;">Especialidad:</span> {{ recetaVista.especialidad }}</div>
                            <div v-if="recetaVista.establecimiento"><span style="color:#64748B;">Centro:</span> {{ recetaVista.establecimiento }}</div>
                            <div v-if="recetaVista.numero_receta"><span style="color:#64748B;">N° Receta:</span> {{ recetaVista.numero_receta }}</div>
                            <div><span style="color:#64748B;">Fecha:</span> {{ recetaVista.fecha }}</div>
                            <div><span style="color:#64748B;">Estado:</span> <span :style="badgeEstado(recetaVista.estado)">{{ recetaVista.estado }}</span></div>
                            <div v-if="recetaVista.observaciones"><span style="color:#64748B;">Obs:</span> {{ recetaVista.observaciones }}</div>

                            <div style="margin-top:8px;">
                                <div style="font-weight:600; color:#1E293B; margin-bottom:8px;">Productos despachados:</div>
                                <div v-for="(it, i) in recetaVista.items" :key="i"
                                    style="padding:8px 12px; background:#F8FAFC; border-radius:8px; margin-bottom:6px; font-size:13px;">
                                    <div style="font-weight:600;">{{ it.descripcion }}</div>
                                    <div style="color:#64748B;">Cantidad: {{ it.cantidad }}</div>
                                </div>
                            </div>

                            <button @click="eliminar(recetaVista)"
                                style="margin-top:8px; padding:8px; background:#FEF2F2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; cursor:pointer; font-size:13px; font-weight:600;">
                                🗑 Eliminar receta
                            </button>
                        </div>
                    </div>

                    <!-- Formulario nueva receta -->
                    <div v-else-if="mostrarForm">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                            <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">Nueva Receta</h3>
                            <button @click="mostrarForm=false" style="background:none; border:none; color:#94A3B8; cursor:pointer; font-size:18px;">✕</button>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:12px;">
                            <div>
                                <label style="font-size:12px; color:#64748B; font-weight:600;">Paciente *</label>
                                <input v-model="form.paciente_nombre" type="text" placeholder="Nombre completo"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box; margin-top:4px;" />
                            </div>
                            <div>
                                <label style="font-size:12px; color:#64748B; font-weight:600;">DNI Paciente</label>
                                <input v-model="form.paciente_dni" type="text" placeholder="12345678"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box; margin-top:4px;" />
                            </div>
                            <div>
                                <label style="font-size:12px; color:#64748B; font-weight:600;">Médico</label>
                                <input v-model="form.medico" type="text" placeholder="Dr. Nombre Apellido"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box; margin-top:4px;" />
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                                <div>
                                    <label style="font-size:12px; color:#64748B; font-weight:600;">Especialidad</label>
                                    <input v-model="form.especialidad" type="text" placeholder="Ej: Pediatría"
                                        style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box; margin-top:4px;" />
                                </div>
                                <div>
                                    <label style="font-size:12px; color:#64748B; font-weight:600;">Fecha *</label>
                                    <input v-model="form.fecha" type="date"
                                        style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box; margin-top:4px;" />
                                </div>
                            </div>
                            <div>
                                <label style="font-size:12px; color:#64748B; font-weight:600;">Centro de Salud</label>
                                <input v-model="form.establecimiento" type="text" placeholder="Hospital / Clínica"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box; margin-top:4px;" />
                            </div>
                            <div>
                                <label style="font-size:12px; color:#64748B; font-weight:600;">N° Receta</label>
                                <input v-model="form.numero_receta" type="text" placeholder="Número de la receta"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box; margin-top:4px;" />
                            </div>

                            <!-- Productos -->
                            <div>
                                <label style="font-size:12px; color:#64748B; font-weight:600;">Productos despachados *</label>
                                <div style="margin-top:8px; display:flex; gap:8px;">
                                    <select v-model="productoSeleccionado"
                                        style="flex:1; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px;">
                                        <option value="">Seleccionar producto...</option>
                                        <option v-for="p in productos" :key="p.id" :value="p">{{ p.descripcion }}</option>
                                    </select>
                                    <button @click="agregarProducto" style="padding:8px 12px; background:#14B8A6; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600;">+</button>
                                </div>
                                <div v-for="(it, i) in form.items" :key="i"
                                    style="margin-top:6px; padding:8px 12px; background:#F0FDF9; border-radius:8px; display:flex; align-items:center; gap:8px;">
                                    <span style="flex:1; font-size:13px; font-weight:600; color:#1E293B;">{{ it.descripcion }}</span>
                                    <input v-model.number="it.cantidad" type="number" min="1"
                                        style="width:60px; padding:4px 8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; text-align:center;" />
                                    <button @click="form.items.splice(i,1)" style="background:none; border:none; color:#EF4444; cursor:pointer; font-size:16px;">✕</button>
                                </div>
                            </div>

                            <div>
                                <label style="font-size:12px; color:#64748B; font-weight:600;">Observaciones</label>
                                <textarea v-model="form.observaciones" rows="2" placeholder="Indicaciones adicionales..."
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box; margin-top:4px; resize:vertical;"></textarea>
                            </div>

                            <button @click="guardar" :disabled="guardando"
                                style="padding:10px; background:#14B8A6; color:white; border:none; border-radius:10px; font-weight:700; cursor:pointer; font-size:14px;">
                                {{ guardando ? 'Guardando...' : '💾 Registrar Receta' }}
                            </button>
                        </div>
                    </div>

                    <!-- Placeholder -->
                    <div v-else style="text-align:center; padding:40px 20px; color:#94A3B8;">
                        <div style="font-size:48px; margin-bottom:12px;">📋</div>
                        <div style="font-size:14px;">Selecciona una receta para ver el detalle<br>o crea una nueva</div>
                    </div>
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
    recetas:   Object,
    productos: Array,
    filtros:   Object,
})

const buscar      = ref(props.filtros?.buscar || '')
const estadoFiltro = ref(props.filtros?.estado || '')
const mostrarForm = ref(false)
const recetaVista = ref(null)
const guardando   = ref(false)
const productoSeleccionado = ref('')

const form = ref({
    paciente_nombre: '',
    paciente_dni:    '',
    medico:          '',
    especialidad:    '',
    establecimiento: '',
    numero_receta:   '',
    fecha:           new Date().toISOString().slice(0, 10),
    items:           [],
    observaciones:   '',
})

const badgeEstado = (estado) => {
    const estilos = {
        despachada: 'display:inline-block; padding:2px 10px; background:#dcfce7; color:#166534; border-radius:20px; font-size:12px; font-weight:600;',
        pendiente:  'display:inline-block; padding:2px 10px; background:#fef9c3; color:#854d0e; border-radius:20px; font-size:12px; font-weight:600;',
        parcial:    'display:inline-block; padding:2px 10px; background:#dbeafe; color:#1e40af; border-radius:20px; font-size:12px; font-weight:600;',
    }
    return estilos[estado] || estilos.pendiente
}

const abrirFormulario = () => {
    recetaVista.value = null
    mostrarForm.value = true
}

const verReceta = (r) => {
    mostrarForm.value = false
    recetaVista.value = r
}

const agregarProducto = () => {
    if (!productoSeleccionado.value) return
    const p = productoSeleccionado.value
    const existe = form.value.items.find(i => i.producto_id === p.id)
    if (existe) { existe.cantidad++; return }
    form.value.items.push({ producto_id: p.id, descripcion: p.descripcion, cantidad: 1 })
    productoSeleccionado.value = ''
}

const guardar = () => {
    if (!form.value.paciente_nombre) { alert('Ingresa el nombre del paciente'); return }
    if (form.value.items.length === 0) { alert('Agrega al menos un producto'); return }
    guardando.value = true
    router.post('/farmacia/recetas', form.value, {
        onSuccess: () => {
            mostrarForm.value = false
            guardando.value = false
            form.value = { paciente_nombre: '', paciente_dni: '', medico: '', especialidad: '',
                establecimiento: '', numero_receta: '', fecha: new Date().toISOString().slice(0,10),
                items: [], observaciones: '' }
        },
        onError: () => { guardando.value = false }
    })
}

const eliminar = (r) => {
    if (!confirm('¿Eliminar esta receta?')) return
    router.delete('/farmacia/recetas/' + r.id, {
        onSuccess: () => { recetaVista.value = null }
    })
}

const filtrar = () => {
    router.get('/farmacia/recetas', { buscar: buscar.value, estado: estadoFiltro.value }, { preserveState: true, replace: true })
}

const irPagina = (p) => {
    router.get('/farmacia/recetas', { page: p, buscar: buscar.value, estado: estadoFiltro.value })
}
</script>
