<template>
  <div style="padding:24px; max-width:1200px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h1 style="font-size:22px; font-weight:700; margin:0;">Agenda de citas</h1>
      <button @click="modalNueva=true" style="background:#8B5CF6; color:white; padding:10px 20px; border:none; border-radius:8px; font-weight:600; font-size:14px; cursor:pointer;">+ Nueva cita</button>
    </div>

    <!-- Filtros -->
    <div style="display:flex; gap:12px; margin-bottom:20px; align-items:center;">
      <input v-model="fecha" type="date" @change="filtrar" style="padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;" />
      <select v-model="doctorFiltro" @change="filtrar" style="padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;">
        <option value="">Todos los doctores</option>
        <option v-for="d in doctores" :key="d.id" :value="d.id">{{ d.nombre }}</option>
      </select>
    </div>

    <!-- Lista de citas -->
    <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; overflow:hidden;">
      <div v-if="citas.length===0" style="padding:48px; text-align:center; color:#94A3B8;">No hay citas para esta fecha</div>
      <div v-for="c in citas" :key="c.id" style="display:flex; align-items:center; padding:16px; border-bottom:1px solid #F1F5F9; gap:16px;">
        <div style="width:70px; text-align:center;">
          <p style="margin:0; font-size:18px; font-weight:800; color:#8B5CF6;">{{ formatHora(c.fecha_hora) }}</p>
          <p style="margin:0; font-size:11px; color:#94A3B8;">{{ c.duracion_min }} min</p>
        </div>
        <div style="flex:1;">
          <p style="margin:0; font-weight:700; font-size:15px;">{{ c.paciente?.apellidos }}, {{ c.paciente?.nombres }}</p>
          <p style="margin:2px 0 0; font-size:13px; color:#64748B;">Dr. {{ c.doctor?.nombre }} · {{ c.motivo || 'Sin motivo' }}</p>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
          <span :style="estadoStyle(c.estado)">{{ c.estado }}</span>
          <select @change="cambiarEstado(c.id, $event.target.value)" :value="c.estado" style="padding:5px 8px; border:1px solid #E2E8F0; border-radius:6px; font-size:12px;">
            <option value="programada">Programada</option>
            <option value="confirmada">Confirmada</option>
            <option value="en_curso">En curso</option>
            <option value="completada">Completada</option>
            <option value="cancelada">Cancelada</option>
            <option value="no_asistio">No asistió</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Modal nueva cita -->
    <div v-if="modalNueva" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
      <div style="background:white; border-radius:16px; padding:28px; width:500px; max-width:90vw;">
        <h2 style="margin:0 0 20px; font-size:18px;">Nueva cita</h2>
        <div style="display:flex; flex-direction:column; gap:14px;">
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">PACIENTE *</label>
            <input v-model="buscarPaciente" @input="searchPaciente" type="text" placeholder="Buscar paciente..." style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            <div v-if="resultadosPaciente.length" style="border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;">
              <div v-for="p in resultadosPaciente" :key="p.id" @click="seleccionarPaciente(p)" style="padding:10px 12px; cursor:pointer; font-size:13px; border-bottom:1px solid #F1F5F9;">
                {{ p.apellidos }}, {{ p.nombres }} - {{ p.dni }}
              </div>
            </div>
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">DOCTOR *</label>
            <select v-model="nuevaCita.doctor_id" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;">
              <option value="">Seleccionar doctor...</option>
              <option v-for="d in doctores" :key="d.id" :value="d.id">{{ d.nombre }}</option>
            </select>
          </div>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div>
              <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">FECHA Y HORA *</label>
              <input v-model="nuevaCita.fecha_hora" type="datetime-local" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            </div>
            <div>
              <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">DURACIÓN (min)</label>
              <input v-model="nuevaCita.duracion_min" type="number" min="15" step="15" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            </div>
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">MOTIVO</label>
            <input v-model="nuevaCita.motivo" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
          </div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
          <button @click="modalNueva=false" style="padding:10px 20px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; cursor:pointer; background:white;">Cancelar</button>
          <button @click="guardarCita" style="padding:10px 24px; background:#8B5CF6; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">Guardar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ citas: Array, doctores: Array, fecha: String, doctor_id: [String, Number] })

const fecha = ref(props.fecha)
const doctorFiltro = ref(props.doctor_id || '')
const modalNueva = ref(false)
const buscarPaciente = ref('')
const resultadosPaciente = ref([])
const nuevaCita = ref({ paciente_id:'', doctor_id:'', fecha_hora:'', duracion_min:30, motivo:'' })

const filtrar = () => router.get('/odontologia/citas', { fecha: fecha.value, doctor_id: doctorFiltro.value }, { preserveState: true })

const formatHora = (f) => new Date(f).toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' })

const estadoStyle = (e) => {
  const m = { programada:{background:'#EFF6FF',color:'#1D4ED8'}, confirmada:{background:'#F0FDF4',color:'#15803D'}, en_curso:{background:'#FFFBEB',color:'#B45309'}, completada:{background:'#F0FDF4',color:'#15803D'}, cancelada:{background:'#FEF2F2',color:'#B91C1C'}, no_asistio:{background:'#F9FAFB',color:'#6B7280'} }
  return { ...(m[e]||{}), padding:'3px 8px', borderRadius:'6px', fontSize:'11px', fontWeight:'600' }
}

const searchPaciente = async () => {
  if (buscarPaciente.value.length < 2) { resultadosPaciente.value = []; return }
  const r = await fetch('/odontologia/pacientes/buscar?q=' + buscarPaciente.value)
  resultadosPaciente.value = await r.json()
}

const seleccionarPaciente = (p) => {
  nuevaCita.value.paciente_id = p.id
  buscarPaciente.value = p.apellidos + ', ' + p.nombres
  resultadosPaciente.value = []
}

const cambiarEstado = (id, estado) => router.put(`/odontologia/citas/${id}`, { estado }, { preserveState: true })

const guardarCita = () => {
  router.post('/odontologia/citas', nuevaCita.value, {
    onSuccess: () => { modalNueva.value = false; nuevaCita.value = { paciente_id:'', doctor_id:'', fecha_hora:'', duracion_min:30, motivo:'' } }
  })
}
</script>
