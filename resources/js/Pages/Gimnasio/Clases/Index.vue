<template>
    <AppLayout>
        <div style="padding:24px; max-width:1200px; margin:0 auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:20px; font-weight:700; color:#1E293B; margin:0;">🏃 Clases y Horarios</h1>
                    <p style="color:#64748B; margin:4px 0 0;">Gestión de clases grupales</p>
                </div>
                <div style="display:flex; gap:10px;">
                    <button @click="showFormClase=true" style="background:#6D28D9; color:white; border:none; padding:10px 20px; border-radius:10px; font-weight:600; cursor:pointer;">+ Nueva clase</button>
                    <button @click="showFormHorario=true" style="background:#0F766E; color:white; border:none; padding:10px 20px; border-radius:10px; font-weight:600; cursor:pointer;">+ Nuevo horario</button>
                </div>
            </div>

            <!-- Tabs -->
            <div style="display:flex; gap:4px; margin-bottom:20px; background:#F1F5F9; padding:4px; border-radius:10px; width:fit-content;">
                <button @click="tab='horarios'" :style="{ background: tab==='horarios' ? 'white' : 'transparent', color: tab==='horarios' ? '#1E293B' : '#64748B', border:'none', padding:'8px 20px', borderRadius:'8px', fontWeight:'600', cursor:'pointer' }">Horarios semanales</button>
                <button @click="tab='clases'" :style="{ background: tab==='clases' ? 'white' : 'transparent', color: tab==='clases' ? '#1E293B' : '#64748B', border:'none', padding:'8px 20px', borderRadius:'8px', fontWeight:'600', cursor:'pointer' }">Catálogo de clases</button>
            </div>

            <!-- HORARIOS SEMANALES -->
            <div v-if="tab==='horarios'">
                <div v-for="dia in dias" :key="dia">
                    <div v-if="horariosPorDia[dia]?.length" style="margin-bottom:20px;">
                        <p style="font-weight:700; color:#6D28D9; font-size:13px; text-transform:uppercase; letter-spacing:1px; margin:0 0 10px;">{{ dia }}</p>
                        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:12px;">
                            <div v-for="h in horariosPorDia[dia]" :key="h.id"
                                style="background:white; border:1px solid #E2E8F0; border-radius:12px; padding:16px; display:flex; justify-content:space-between; align-items:start;">
                                <div>
                                    <p style="font-weight:700; color:#1E293B; margin:0; font-size:15px;">{{ h.clase?.nombre }}</p>
                                    <p style="color:#6D28D9; margin:4px 0 0; font-size:13px;">🕐 {{ h.hora_inicio }} — {{ h.hora_fin }}</p>
                                    <p style="color:#64748B; margin:4px 0 0; font-size:13px;">👤 {{ h.instructor?.nombre }} {{ h.instructor?.apellidos }}</p>
                                    <div style="margin-top:8px; display:flex; gap:6px;">
                                        <span style="background:#EDE9FE; color:#6D28D9; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600;">
                                            {{ h.clase?.capacidad_max }} cupos
                                        </span>
                                        <span style="background:#F0FDF4; color:#166534; padding:2px 10px; border-radius:20px; font-size:11px; font-weight:600;">
                                            {{ h.clase?.duracion_min }} min
                                        </span>
                                    </div>
                                </div>
                                <button @click="eliminarHorario(h)" style="background:#FEE2E2; color:#991B1B; border:none; padding:6px 10px; border-radius:8px; cursor:pointer; font-size:12px;">✕</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="horarios.length === 0" style="text-align:center; padding:60px; color:#94A3B8;">
                    No hay horarios registrados. Agrega uno con el botón de arriba.
                </div>
            </div>

            <!-- CATÁLOGO DE CLASES -->
            <div v-if="tab==='clases'">
                <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px;">
                    <div v-for="c in clases" :key="c.id" style="background:white; border:1px solid #E2E8F0; border-radius:14px; padding:20px;">
                        <div style="display:flex; justify-content:space-between; align-items:start;">
                            <div>
                                <p style="font-weight:700; color:#1E293B; margin:0; font-size:15px;">{{ c.nombre }}</p>
                                <p style="color:#64748B; margin:4px 0 0; font-size:13px;">{{ c.tipo || 'Sin tipo' }}</p>
                            </div>
                            <button @click="editarClase(c)" style="background:#F1F5F9; border:none; padding:6px 10px; border-radius:8px; cursor:pointer;">✏️</button>
                        </div>
                        <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
                            <span style="background:#EDE9FE; color:#6D28D9; padding:4px 10px; border-radius:20px; font-size:12px;">{{ c.capacidad_max }} cupos</span>
                            <span style="background:#F0FDF4; color:#166534; padding:4px 10px; border-radius:20px; font-size:12px;">{{ c.duracion_min }} min</span>
                            <span style="background:#F1F5F9; color:#374151; padding:4px 10px; border-radius:20px; font-size:12px;">{{ c.horarios_count }} horario(s)</span>
                        </div>
                        <p v-if="c.descripcion" style="color:#64748B; font-size:13px; margin:10px 0 0;">{{ c.descripcion }}</p>
                    </div>
                    <div v-if="clases.length === 0" style="grid-column:1/-1; text-align:center; padding:60px; color:#94A3B8;">
                        No hay clases registradas.
                    </div>
                </div>
            </div>

            <!-- Modal nueva clase -->
            <div v-if="showFormClase" style="position:fixed; inset:0; background:rgba(0,0,0,0.45); display:flex; align-items:center; justify-content:center; z-index:999;">
                <div style="background:white; border-radius:16px; padding:28px; width:460px; max-width:95vw;">
                    <h2 style="margin:0 0 20px; font-size:18px; font-weight:700;">{{ formClase.id ? 'Editar' : 'Nueva' }} Clase</h2>
                    <div style="display:grid; gap:14px;">
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#374151;">Nombre *</label>
                            <input v-model="formClase.nombre" type="text" placeholder="Yoga, Spinning, Zumba..." style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                        </div>
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#374151;">Tipo</label>
                            <select v-model="formClase.tipo" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;">
                                <option value="">Sin tipo</option>
                                <option value="cardio">Cardio</option>
                                <option value="fuerza">Fuerza</option>
                                <option value="flexibilidad">Flexibilidad</option>
                                <option value="baile">Baile</option>
                                <option value="funcional">Funcional</option>
                            </select>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Capacidad máx.</label>
                                <input v-model="formClase.capacidad_max" type="number" min="1" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Duración (min)</label>
                                <input v-model="formClase.duracion_min" type="number" min="1" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                        </div>
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#374151;">Descripción</label>
                            <textarea v-model="formClase.descripcion" rows="2" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box; resize:none;"></textarea>
                        </div>
                    </div>
                    <div style="display:flex; gap:12px; margin-top:20px;">
                        <button @click="guardarClase" style="flex:1; background:#6D28D9; color:white; border:none; padding:12px; border-radius:10px; font-weight:700; cursor:pointer;">Guardar</button>
                        <button @click="cerrarClase" style="flex:1; background:#F1F5F9; border:none; padding:12px; border-radius:10px; font-weight:600; cursor:pointer;">Cancelar</button>
                    </div>
                </div>
            </div>

            <!-- Modal nuevo horario -->
            <div v-if="showFormHorario" style="position:fixed; inset:0; background:rgba(0,0,0,0.45); display:flex; align-items:center; justify-content:center; z-index:999;">
                <div style="background:white; border-radius:16px; padding:28px; width:460px; max-width:95vw;">
                    <h2 style="margin:0 0 20px; font-size:18px; font-weight:700;">Nuevo Horario</h2>
                    <div style="display:grid; gap:14px;">
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#374151;">Clase *</label>
                            <select v-model="formHorario.clase_id" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;">
                                <option value="">Seleccionar clase</option>
                                <option v-for="c in clases" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#374151;">Instructor *</label>
                            <select v-model="formHorario.instructor_id" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;">
                                <option value="">Seleccionar instructor</option>
                                <option v-for="i in instructores" :key="i.id" :value="i.id">{{ i.nombre }} {{ i.apellidos }}</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#374151;">Día *</label>
                            <select v-model="formHorario.dia" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;">
                                <option v-for="d in dias" :key="d" :value="d">{{ d.charAt(0).toUpperCase() + d.slice(1) }}</option>
                            </select>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Hora inicio *</label>
                                <input v-model="formHorario.hora_inicio" type="time" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Hora fin *</label>
                                <input v-model="formHorario.hora_fin" type="time" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; gap:12px; margin-top:20px;">
                        <button @click="guardarHorario" style="flex:1; background:#0F766E; color:white; border:none; padding:12px; border-radius:10px; font-weight:700; cursor:pointer;">Guardar</button>
                        <button @click="showFormHorario=false" style="flex:1; background:#F1F5F9; border:none; padding:12px; border-radius:10px; font-weight:600; cursor:pointer;">Cancelar</button>
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

const props = defineProps({ clases: Array, instructores: Array, horarios: Array })

const tab = ref('horarios')
const dias = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo']
const showFormClase = ref(false)
const showFormHorario = ref(false)

const formClaseVacio = { id:null, nombre:'', tipo:'', capacidad_max:20, duracion_min:60, descripcion:'' }
const formClase = ref({ ...formClaseVacio })
const formHorario = ref({ clase_id:'', instructor_id:'', dia:'lunes', hora_inicio:'', hora_fin:'' })

const horariosPorDia = computed(() => {
    const agrupado = {}
    dias.forEach(d => agrupado[d] = [])
    props.horarios.forEach(h => { if (agrupado[h.dia]) agrupado[h.dia].push(h) })
    return agrupado
})

const editarClase = (c) => { formClase.value = { ...c }; showFormClase.value = true }
const cerrarClase = () => { formClase.value = { ...formClaseVacio }; showFormClase.value = false }

const guardarClase = () => {
    if (formClase.value.id) {
        router.put('/gimnasio/clases/' + formClase.value.id, formClase.value, { onSuccess: cerrarClase })
    } else {
        router.post('/gimnasio/clases', formClase.value, { onSuccess: cerrarClase })
    }
}

const guardarHorario = () => {
    router.post('/gimnasio/horarios', formHorario.value, {
        onSuccess: () => { showFormHorario.value = false; formHorario.value = { clase_id:'', instructor_id:'', dia:'lunes', hora_inicio:'', hora_fin:'' } }
    })
}

const eliminarHorario = (h) => {
    if (confirm('¿Eliminar este horario?')) {
        router.delete('/gimnasio/horarios/' + h.id)
    }
}
</script>
