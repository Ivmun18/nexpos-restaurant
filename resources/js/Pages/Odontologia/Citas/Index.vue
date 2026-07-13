<template>
  <AppLayout title="Agenda de citas" subtitle="Gestión de citas odontológicas">
  <div style="padding:16px; background:#F0F4FF; min-height:100vh;">

    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
      <div>
        <h1 style="font-size:18px; font-weight:700; color:#1e293b; margin:0;">🗓️ Agenda de citas</h1>
        <p style="font-size:12px; color:#64748b; margin:2px 0 0;">{{ fechaLabel }}</p>
      </div>
      <div style="display:flex; gap:8px;">
        <button @click="mostrarLinkReserva=!mostrarLinkReserva" style="background:white; color:#6C8EBF; border:1.5px solid #6C8EBF; border-radius:8px; padding:8px 14px; font-size:13px; font-weight:600; cursor:pointer;">🔗 Link reserva</button>
        <button @click="abrirModalNueva()" style="background:#6C8EBF; color:white; border:none; border-radius:8px; padding:9px 18px; font-size:13px; font-weight:600; cursor:pointer;">+ Nueva cita</button>
      </div>
    </div>

    <!-- Link reserva -->
    <div v-if="mostrarLinkReserva" style="background:white; border-radius:12px; padding:14px 16px; margin-bottom:12px; border:1.5px solid #C7D7F0;">
      <p style="font-size:12px; font-weight:700; color:#64748b; margin:0 0 8px;">Comparte este link para que los pacientes agenden en línea:</p>
      <div style="display:flex; gap:8px;">
        <input :value="linkReserva" readonly style="flex:1; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:12px; background:#F8FAFC; color:#1e293b;" />
        <button @click="copiarLink" style="padding:8px 14px; background:#6C8EBF; color:white; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">{{ linkCopiado ? '✅ Copiado' : 'Copiar' }}</button>
        <a :href="linkWA" target="_blank" style="padding:8px 14px; background:#25D366; color:white; border:none; border-radius:8px; font-size:12px; font-weight:600; text-decoration:none; display:flex; align-items:center;">WhatsApp</a>
      </div>
    </div>

    <!-- Filtros -->
    <div style="background:white; border-radius:12px; padding:12px 16px; display:flex; align-items:center; gap:12px; margin-bottom:12px; flex-wrap:wrap;">
      <div style="display:flex; align-items:center; gap:8px;">
        <span style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Fecha</span>
        <input type="date" v-model="filtroFecha" @change="filtrar" style="border:1.5px solid #E2E8F0; border-radius:8px; padding:7px 10px; font-size:13px; color:#1e293b; background:#F8FAFC;" />
      </div>
      <div style="display:flex; align-items:center; gap:8px;">
        <span style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Doctor</span>
        <select v-model="filtroDoctor" @change="filtrar" style="border:1.5px solid #E2E8F0; border-radius:8px; padding:7px 10px; font-size:13px; color:#1e293b; background:#F8FAFC; min-width:150px;">
          <option value="">Todos</option>
          <option v-for="d in doctores" :key="d.id" :value="d.id">{{ d.nombre }}</option>
        </select>
      </div>
      <div style="display:flex; align-items:center; gap:8px;">
        <span style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase;">Horario</span>
        <input type="number" v-model="horaInicio" min="0" max="23" style="width:50px; border:1.5px solid #E2E8F0; border-radius:8px; padding:7px 8px; font-size:13px; text-align:center; background:#F8FAFC;" />
        <span style="color:#94a3b8;">—</span>
        <input type="number" v-model="horaFin" min="0" max="23" style="width:50px; border:1.5px solid #E2E8F0; border-radius:8px; padding:7px 8px; font-size:13px; text-align:center; background:#F8FAFC;" />
        <span style="font-size:12px; color:#94a3b8;">hrs</span>
      </div>
      <div style="margin-left:auto; display:flex; gap:10px; flex-wrap:wrap;">
        <span v-for="e in estados" :key="e.key" style="display:flex; align-items:center; gap:5px; font-size:11px; color:#64748b; font-weight:600;">
          <span :style="{width:'9px',height:'9px',borderRadius:'50%',background:e.color,display:'inline-block'}"></span>{{ e.label }}
        </span>
      </div>
    </div>

    <!-- Timeline -->
    <div style="background:white; border-radius:12px; overflow:hidden;">
      <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px; border-bottom:1px solid #F1F5F9; background:#FAFBFF;">
        <span style="font-size:13px; font-weight:700; color:#1e293b;">Timeline del día</span>
        <span style="font-size:12px; color:#6C8EBF; font-weight:600; background:#EEF2FF; padding:3px 10px; border-radius:20px;">{{ citasFiltradas.length }} cita(s)</span>
      </div>

      <div v-for="hora in horasRango" :key="hora">
        <!-- Línea hora actual -->
        <div v-if="hora === horaActual" style="height:2px; background:#6C8EBF; position:relative;">
          <div style="width:8px; height:8px; background:#6C8EBF; border-radius:50%; position:absolute; left:62px; top:-3px;"></div>
        </div>

        <div :style="{display:'flex', minHeight:'60px', borderBottom:'1px solid #F8FAFC', background: hora === horaActual ? '#FAFBFF' : 'white'}">
          <div :style="{width:'64px', minWidth:'64px', padding:'8px 0 0 14px', fontSize:'12px', fontWeight:'700', color: hora === horaActual ? '#6C8EBF' : '#94a3b8', borderRight:'1px solid #F1F5F9'}">
            {{ String(hora).padStart(2,'0') }}:00
          </div>
          <div style="flex:1; padding:6px 10px; display:flex; flex-wrap:wrap; gap:6px; align-items:flex-start;">
            <div v-if="citasPorHora(hora).length === 0" @click="abrirModalNuevaHora(hora)" style="padding:8px 10px; font-size:12px; color:#cbd5e1; font-style:italic; cursor:pointer; border-radius:6px; width:100%;">
              + Agregar cita
            </div>
            <div v-for="c in citasPorHora(hora)" :key="c.id"
              @click="abrirDetalle(c)"
              :style="{borderLeft:'3px solid '+colorEstado(c.estado).border, borderRadius:'0 8px 8px 0', padding:'6px 10px', background:colorEstado(c.estado).bg, minWidth:'160px', maxWidth:'220px', cursor:'pointer'}">
              <div style="font-size:11px; font-weight:700; color:#64748b;">{{ formatHora(c.fecha_hora) }} · {{ c.duracion_min || 30 }} min</div>
              <div style="font-size:12px; font-weight:700; color:#1e293b; margin:2px 0;">{{ c.paciente?.apellidos }}, {{ c.paciente?.nombres }}</div>
              <div style="font-size:11px; color:#64748b;">{{ c.doctor?.nombre }}</div>
              <span :style="{fontSize:'10px', fontWeight:'700', padding:'2px 7px', borderRadius:'20px', display:'inline-block', marginTop:'3px', background:colorEstado(c.estado).border, color:'white'}">{{ labelEstado(c.estado) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal detalle -->
    <div v-if="modalDetalle" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;" @click.self="modalDetalle=false">
      <div style="background:white; border-radius:16px; padding:24px; width:100%; max-width:460px; margin:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
          <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin:0;">Detalle de cita</h3>
          <button @click="modalDetalle=false" style="background:none; border:none; font-size:20px; cursor:pointer; color:#94A3B8;">×</button>
        </div>
        <div v-if="citaSeleccionada" style="background:#F8FAFC; border-radius:10px; padding:14px; margin-bottom:16px;">
          <p style="font-size:16px; font-weight:700; color:#1e293b; margin:0;">{{ citaSeleccionada.paciente?.nombres }} {{ citaSeleccionada.paciente?.apellidos }}</p>
          <p style="font-size:13px; color:#64748b; margin:4px 0;">{{ formatFechaHora(citaSeleccionada.fecha_hora) }} · {{ citaSeleccionada.duracion_min || 30 }} min</p>
          <p style="font-size:13px; color:#64748b; margin:2px 0;">{{ citaSeleccionada.doctor?.nombre }}</p>
          <p v-if="citaSeleccionada.motivo" style="font-size:13px; color:#1e293b; margin:6px 0 0; font-style:italic;">{{ citaSeleccionada.motivo }}</p>
        </div>
        <div style="margin-bottom:16px;">
          <p style="font-size:12px; font-weight:700; color:#64748b; margin:0 0 8px; text-transform:uppercase;">Cambiar estado</p>
          <div style="display:flex; flex-wrap:wrap; gap:6px;">
            <button v-for="e in estados" :key="e.key"
              @click="cambiarEstadoModal(citaSeleccionada.id, e.key)"
              :style="{padding:'6px 12px', border:'none', borderRadius:'8px', fontSize:'12px', fontWeight:'600', cursor:'pointer', background: citaSeleccionada?.estado === e.key ? e.color : '#F1F5F9', color: citaSeleccionada?.estado === e.key ? 'white' : '#64748b'}">
              {{ e.label }}
            </button>
          </div>
        </div>
        <button @click="enviarRecordatorio(citaSeleccionada)" style="width:100%; padding:10px; background:#25D366; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
          {{ recordatorioEnviado ? '✅ Recordatorio enviado' : '📱 Enviar recordatorio WhatsApp' }}
        </button>
      </div>
    </div>

    <!-- Modal nueva cita -->
    <div v-if="modalNueva" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
      <div style="background:white; border-radius:16px; padding:24px; width:100%; max-width:480px; margin:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
          <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin:0;">Nueva cita</h3>
          <button @click="modalNueva=false" style="background:none; border:none; font-size:20px; cursor:pointer; color:#94A3B8;">×</button>
        </div>
        <div style="display:flex; flex-direction:column; gap:14px;">
          <div>
            <label style="font-size:11px; font-weight:700; color:#64748b; display:block; margin-bottom:6px; text-transform:uppercase;">Paciente *</label>
            <input v-model="buscarPaciente" @input="searchPaciente" placeholder="Buscar por nombre o apellido..." style="width:100%; padding:9px 12px; border:1.5px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            <div v-if="resultadosPaciente.length > 0" style="border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; max-height:160px; overflow-y:auto;">
              <div v-for="p in resultadosPaciente" :key="p.id" @click="seleccionarPaciente(p)" style="padding:10px 12px; cursor:pointer; font-size:13px; border-bottom:1px solid #F1F5F9; color:#1e293b;">
                {{ p.apellidos }}, {{ p.nombres }}
              </div>
            </div>
          </div>
          <div>
            <label style="font-size:11px; font-weight:700; color:#64748b; display:block; margin-bottom:6px; text-transform:uppercase;">Doctor *</label>
            <select v-model="nuevaCita.doctor_id" style="width:100%; padding:9px 12px; border:1.5px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box; background:white;">
              <option value="">Seleccionar doctor</option>
              <option v-for="d in doctores" :key="d.id" :value="d.id">{{ d.nombre }}</option>
            </select>
          </div>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div>
              <label style="font-size:11px; font-weight:700; color:#64748b; display:block; margin-bottom:6px; text-transform:uppercase;">Fecha y hora *</label>
              <input v-model="nuevaCita.fecha_hora" type="datetime-local" style="width:100%; padding:9px 12px; border:1.5px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;" />
            </div>
            <div>
              <label style="font-size:11px; font-weight:700; color:#64748b; display:block; margin-bottom:6px; text-transform:uppercase;">Duración (min)</label>
              <input v-model="nuevaCita.duracion_min" type="number" min="15" step="15" style="width:100%; padding:9px 12px; border:1.5px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            </div>
          </div>
          <div>
            <label style="font-size:11px; font-weight:700; color:#64748b; display:block; margin-bottom:6px; text-transform:uppercase;">Motivo</label>
            <input v-model="nuevaCita.motivo" type="text" placeholder="Ej: Limpieza, Extracción, Control..." style="width:100%; padding:9px 12px; border:1.5px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
          </div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
          <button @click="modalNueva=false" style="padding:10px 20px; border:1.5px solid #E2E8F0; border-radius:8px; font-size:14px; cursor:pointer; background:white; color:#64748b;">Cancelar</button>
          <button @click="guardarCita" style="padding:10px 24px; background:#6C8EBF; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">Guardar cita</button>
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

const props = defineProps({ citas: Array, doctores: Array, fecha: String, doctor_id: [String,Number,null] })

const filtroFecha = ref(props.fecha)
const filtroDoctor = ref(props.doctor_id || '')
const horaInicio = ref(8)
const horaFin = ref(19)
const modalNueva = ref(false)
const modalDetalle = ref(false)
const citaSeleccionada = ref(null)
const buscarPaciente = ref('')
const resultadosPaciente = ref([])
const nuevaCita = ref({ paciente_id:'', doctor_id:'', fecha_hora:'', duracion_min:30, motivo:'' })
const mostrarLinkReserva = ref(false)
const linkCopiado = ref(false)
const recordatorioEnviado = ref(false)

const empresaId = window.__inertia?.props?.auth?.user?.empresa_id || ''
const linkReserva = `${window.location.origin}/reservar/${empresaId}`
const linkWA = `https://wa.me/?text=${encodeURIComponent('Agenda tu cita en nuestra clínica: ' + linkReserva)}`

const copiarLink = () => {
  try {
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(linkReserva)
    } else {
      const ta = document.createElement('textarea')
      ta.value = linkReserva
      ta.style.position = 'fixed'
      ta.style.opacity = '0'
      document.body.appendChild(ta)
      ta.focus(); ta.select()
      document.execCommand('copy')
      document.body.removeChild(ta)
    }
    linkCopiado.value = true
    setTimeout(() => linkCopiado.value = false, 2000)
  } catch(e) { alert('Link: ' + linkReserva) }
}

const estados = [
  { key:'pendiente_confirmacion', label:'Por confirmar', color:'#f59e0b', bg:'#fffbeb', border:'#f59e0b' },
  { key:'programada',  label:'Programada',  color:'#8B5CF6', bg:'#F5F3FF', border:'#8B5CF6' },
  { key:'confirmada',  label:'Confirmada',  color:'#2563EB', bg:'#EFF6FF', border:'#2563EB' },
  { key:'en_curso',    label:'En curso',    color:'#D97706', bg:'#FFFBEB', border:'#D97706' },
  { key:'completada',  label:'Completada',  color:'#16A34A', bg:'#F0FDF4', border:'#16A34A' },
  { key:'cancelada',   label:'Cancelada',   color:'#DC2626', bg:'#FEF2F2', border:'#DC2626' },
  { key:'no_asistio',  label:'No asistió',  color:'#94A3B8', bg:'#F8FAFC', border:'#94A3B8' },
]

const fechaLabel = computed(() => {
  const d = new Date(filtroFecha.value + 'T12:00:00')
  return d.toLocaleDateString('es-PE', { weekday:'long', year:'numeric', month:'long', day:'numeric' })
})

const horasRango = computed(() => {
  const hrs = []
  for (let h = Number(horaInicio.value); h <= Number(horaFin.value); h++) hrs.push(h)
  return hrs
})

const horaActual = computed(() => new Date().getHours())

const citasFiltradas = computed(() => {
  if (!filtroDoctor.value) return props.citas
  return props.citas.filter(c => c.doctor_id == filtroDoctor.value)
})

const citasPorHora = (hora) => citasFiltradas.value.filter(c => new Date(c.fecha_hora).getHours() === hora)

const colorEstado = (estado) => {
  const e = estados.find(x => x.key === estado)
  return e ? { bg: e.bg, border: e.border } : { bg:'#F8FAFC', border:'#94A3B8' }
}

const labelEstado = (estado) => {
  const e = estados.find(x => x.key === estado)
  return e ? e.label : estado
}

const formatHora = (dt) => {
  if (!dt) return ''
  const d = new Date(dt)
  return d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0')
}

const formatFechaHora = (dt) => {
  if (!dt) return ''
  const d = new Date(dt)
  return d.toLocaleDateString('es-PE') + ' ' + formatHora(dt)
}

const filtrar = () => router.get('/odontologia/citas', { fecha: filtroFecha.value, doctor_id: filtroDoctor.value || '' }, { preserveState: true })

const abrirDetalle = (c) => { citaSeleccionada.value = c; modalDetalle.value = true }

const cambiarEstadoModal = (id, estado) => {
  router.put(`/odontologia/citas/${id}`, { estado }, {
    preserveState: true,
    onSuccess: () => { if (citaSeleccionada.value) citaSeleccionada.value.estado = estado }
  })
}

const abrirModalNueva = () => {
  const now = new Date()
  const pad = n => String(n).padStart(2,'0')
  nuevaCita.value = { paciente_id:'', doctor_id:'', duracion_min:30, motivo:'',
    fecha_hora: `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}` }
  buscarPaciente.value = ''
  resultadosPaciente.value = []
  modalNueva.value = true
}

const abrirModalNuevaHora = (hora) => {
  const now = new Date()
  const pad = n => String(n).padStart(2,'0')
  nuevaCita.value = { paciente_id:'', doctor_id:'', duracion_min:30, motivo:'',
    fecha_hora: `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(hora)}:00` }
  buscarPaciente.value = ''
  resultadosPaciente.value = []
  modalNueva.value = true
}

const searchPaciente = async () => {
  if (buscarPaciente.value.length < 2) { resultadosPaciente.value = []; return }
  const r = await fetch('/odontologia/pacientes/buscar?q=' + buscarPaciente.value, { credentials: 'include' })
  resultadosPaciente.value = await r.json()
}

const seleccionarPaciente = (p) => {
  nuevaCita.value.paciente_id = p.id
  buscarPaciente.value = p.apellidos + ', ' + p.nombres
  resultadosPaciente.value = []
}

const guardarCita = () => {
  router.post('/odontologia/citas', nuevaCita.value, {
    onSuccess: () => { modalNueva.value = false; nuevaCita.value = { paciente_id:'', doctor_id:'', fecha_hora:'', duracion_min:30, motivo:'' } },
    onError: (e) => { alert('Error: ' + JSON.stringify(e)) }
  })
}

const enviarRecordatorio = async (cita) => {
  try {
    const res = await fetch(`/odontologia/citas/${cita.id}/recordatorio`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '', 'Accept': 'application/json' }
    })
    const data = await res.json()
    if (data.url) {
      window.open(data.url, '_blank')
      recordatorioEnviado.value = true
      setTimeout(() => { recordatorioEnviado.value = false }, 4000)
    } else {
      alert(data.error || 'El paciente no tiene teléfono registrado')
    }
  } catch(e) { alert('Error al generar recordatorio') }
}
</script>
