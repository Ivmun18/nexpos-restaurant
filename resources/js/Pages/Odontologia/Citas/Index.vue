<template>
  <AppLayout>
    <div style="padding:24px; max-width:1200px; margin:0 auto;">

      <!-- Header -->
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
          <h1 style="margin:0; font-size:22px; font-weight:700; color:#1E293B;">Agenda de citas</h1>
          <p style="margin:4px 0 0; font-size:13px; color:#94A3B8;">{{ fechaLabel }}</p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
          <button @click="mostrarLinkReserva=!mostrarLinkReserva"
            style="background:#0ea5e9;color:#fff;border:none;border-radius:10px;padding:10px 16px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;">
            🔗 Link de reservas
          </button>
          <button @click="abrirModalNueva()" style="background:#8B5CF6; color:white; border:none; border-radius:10px; padding:10px 20px; font-size:14px; font-weight:600; cursor:pointer;">+ Nueva cita</button>
        </div>
        <!-- Panel link reservas -->
        <div v-if="mostrarLinkReserva" style="position:absolute;top:60px;right:24px;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;width:380px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:100;">
          <div style="font-size:13px;font-weight:600;color:#1e293b;margin-bottom:8px;">🔗 Link público de reservas</div>
          <div style="font-size:11px;color:#64748b;margin-bottom:10px;">Comparte este link para que los pacientes agenden sus citas sin llamar.</div>
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px;font-size:12px;color:#374151;word-break:break-all;margin-bottom:10px;">
            {{ linkReserva }}
          </div>
          <div style="display:flex;gap:8px;">
            <button @click="copiarLink" style="flex:1;padding:8px;background:#4338ca;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:500;cursor:pointer;">
              {{ linkCopiado ? '✓ Copiado' : 'Copiar link' }}
            </button>
            <a :href="linkWA" target="_blank" style="flex:1;padding:8px;background:#25D366;color:#fff;border-radius:8px;font-size:12px;font-weight:500;text-decoration:none;text-align:center;">
              📱 WhatsApp
            </a>
            <a :href="linkReserva" target="_blank" style="padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;cursor:pointer;background:#fff;color:#374151;text-decoration:none;">
              Ver
            </a>
          </div>
        </div>
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success" style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:12px 16px; margin-bottom:16px; color:#15803D; font-size:14px;">
        ✓ {{ $page.props.flash.success }}
      </div>

      <!-- Filtros -->
      <div style="display:flex; gap:12px; align-items:center; margin-bottom:24px; background:white; padding:14px 16px; border-radius:12px; border:1px solid #E2E8F0;">
        <div style="display:flex; align-items:center; gap:8px;">
          <span style="font-size:13px; font-weight:600; color:#64748B;">FECHA</span>
          <input type="date" v-model="filtroFecha" @change="filtrar" style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;" />
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
          <span style="font-size:13px; font-weight:600; color:#64748B;">DOCTOR</span>
          <select v-model="filtroDoctor" @change="filtrar" style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;">
            <option value="">Todos</option>
            <option v-for="d in doctores" :key="d.id" :value="d.id">{{ d.nombre }}</option>
          </select>
        </div>
        <div style="margin-left:auto; display:flex; align-items:center; gap:8px;">
          <span style="font-size:13px; color:#64748B;">Horario:</span>
          <input type="number" v-model="horaInicio" min="0" max="23" style="width:52px; padding:7px 8px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; text-align:center;" />
          <span style="color:#94A3B8;">—</span>
          <input type="number" v-model="horaFin" min="0" max="23" style="width:52px; padding:7px 8px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; text-align:center;" />
          <span style="font-size:13px; color:#64748B;">hrs</span>
        </div>
      </div>

      <!-- Timeline -->
      <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
        <!-- Leyenda estados -->
        <div style="display:flex; gap:16px; padding:12px 20px; border-bottom:1px solid #F1F5F9; background:#FAFAFA;">
          <span v-for="e in estados" :key="e.key" style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; color:#64748B;">
            <span :style="{width:'10px',height:'10px',borderRadius:'50%',background:e.color,display:'inline-block'}"></span>
            {{ e.label }}
          </span>
          <span style="margin-left:auto; font-size:12px; color:#94A3B8; font-weight:600;">{{ citasFiltradas.length }} cita(s)</span>
        </div>

        <!-- Filas por hora -->
        <div v-for="hora in horasRango" :key="hora" style="display:flex; min-height:64px; border-bottom:1px solid #F8FAFC;">
          <!-- Columna hora -->
          <div style="width:72px; min-width:72px; padding:10px 0 0 16px; font-size:12px; font-weight:700; color:#94A3B8; border-right:1px solid #F1F5F9;">
            {{ String(hora).padStart(2,'0') }}:00
          </div>
          <!-- Columna citas -->
          <div style="flex:1; padding:6px 12px; display:flex; flex-direction:column; gap:6px;">
            <div v-if="citasPorHora(hora).length === 0 && hora === horaActual" style="height:2px; background:#8B5CF6; border-radius:2px; margin:4px 0; opacity:0.4;"></div>
            <div
              v-for="c in citasPorHora(hora)"
              :key="c.id"
              :style="{ background: colorEstado(c.estado).bg, borderLeft: '4px solid ' + colorEstado(c.estado).border, borderRadius:'8px', padding:'8px 12px', cursor:'pointer' }"
              @click="abrirDetalle(c)"
            >
              <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <p style="margin:0; font-size:14px; font-weight:700; color:#1E293B;">
                    {{ formatHora(c.fecha_hora) }} · {{ c.paciente?.apellidos }}, {{ c.paciente?.nombres }}
                  </p>
                  <p style="margin:2px 0 0; font-size:12px; color:#64748B;">
                    {{ c.doctor?.nombre }} · {{ c.motivo || 'Sin motivo' }} · {{ c.duracion_min }} min
                  </p>
                </div>
                <span :style="{ ...badgeEstado(c.estado), padding:'3px 10px', borderRadius:'20px', fontSize:'11px', fontWeight:'700' }">
                  {{ labelEstado(c.estado) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Sin citas -->
        <div v-if="citasFiltradas.length === 0" style="padding:48px; text-align:center; color:#94A3B8; font-size:14px;">
          No hay citas para esta fecha
        </div>
      </div>
    </div>

    <!-- Modal detalle/acciones -->
    <div v-if="modalDetalle" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;" @click.self="modalDetalle=false">
      <div style="background:white; border-radius:16px; padding:28px; width:440px; max-width:90vw;">
        <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:20px;">
          <h2 style="margin:0; font-size:18px; font-weight:700;">Detalle de cita</h2>
          <button @click="modalDetalle=false" style="background:none; border:none; font-size:20px; cursor:pointer; color:#94A3B8;">×</button>
        </div>
        <div v-if="citaSeleccionada" style="display:flex; flex-direction:column; gap:10px;">
          <div style="background:#F8FAFC; border-radius:10px; padding:14px;">
            <p style="margin:0; font-size:16px; font-weight:700;">{{ citaSeleccionada.paciente?.apellidos }}, {{ citaSeleccionada.paciente?.nombres }}</p>
            <p style="margin:4px 0 0; font-size:13px; color:#64748B;">{{ citaSeleccionada.doctor?.nombre }}</p>
          </div>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div style="background:#F8FAFC; border-radius:8px; padding:10px;">
              <p style="margin:0; font-size:11px; color:#94A3B8; font-weight:600;">FECHA Y HORA</p>
              <p style="margin:4px 0 0; font-size:14px; font-weight:600;">{{ formatFechaHora(citaSeleccionada.fecha_hora) }}</p>
            </div>
            <div style="background:#F8FAFC; border-radius:8px; padding:10px;">
              <p style="margin:0; font-size:11px; color:#94A3B8; font-weight:600;">DURACIÓN</p>
              <p style="margin:4px 0 0; font-size:14px; font-weight:600;">{{ citaSeleccionada.duracion_min }} min</p>
            </div>
          </div>
          <div style="background:#F8FAFC; border-radius:8px; padding:10px;">
            <p style="margin:0; font-size:11px; color:#94A3B8; font-weight:600;">MOTIVO</p>
            <p style="margin:4px 0 0; font-size:14px;">{{ citaSeleccionada.motivo || 'Sin motivo registrado' }}</p>
          </div>
          <!-- Cambiar estado -->
          <div style="margin-top:4px;">
            <p style="font-size:12px; font-weight:600; color:#64748B; margin:0 0 8px;">CAMBIAR ESTADO</p>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
              <button v-for="e in estados" :key="e.key"
                @click="cambiarEstadoModal(citaSeleccionada.id, e.key)"
                :style="{ background: citaSeleccionada.estado===e.key ? e.color : '#F1F5F9', color: citaSeleccionada.estado===e.key ? 'white' : '#64748B', border:'none', borderRadius:'8px', padding:'7px 14px', fontSize:'12px', fontWeight:'600', cursor:'pointer' }">
                {{ e.label }}
              </button>
            </div>
          </div>
          <!-- Recordatorio WhatsApp -->
          <div style="margin-top:12px;padding-top:12px;border-top:1px solid #f1f5f9;">
            <button @click="enviarRecordatorio(citaSeleccionada)"
              style="width:100%;padding:9px;background:#25D366;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
              <span style="font-size:16px;">📱</span> Enviar recordatorio WhatsApp
            </button>
            <div v-if="recordatorioEnviado" style="margin-top:6px;text-align:center;font-size:11px;color:#16a34a;">✓ WhatsApp abierto — mensaje listo para enviar</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal nueva cita -->
    <div v-if="modalNueva" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
      <div style="background:white; border-radius:16px; padding:28px; width:500px; max-width:90vw;">
        <h2 style="margin:0 0 20px; font-size:18px; font-weight:700;">Nueva cita</h2>
        <div style="display:flex; flex-direction:column; gap:14px;">
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">PACIENTE *</label>
            <input v-model="buscarPaciente" @input="searchPaciente" type="text" placeholder="Buscar paciente..." style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            <div v-if="resultadosPaciente.length" style="border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; background:white; z-index:10; position:relative;">
              <div v-for="p in resultadosPaciente" :key="p.id" @click="seleccionarPaciente(p)" style="padding:10px 12px; cursor:pointer; font-size:13px; border-bottom:1px solid #F1F5F9;">
                {{ p.apellidos }}, {{ p.nombres }} - {{ p.dni }}
              </div>
            </div>
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">DOCTOR *</label>
            <select v-model="nuevaCita.doctor_id" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;">
              <option value="">Seleccionar doctor...</option>
              <option v-for="d in doctores" :key="d.id" :value="Number(d.id)">{{ d.nombre }}</option>
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

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({ citas: Array, doctores: Array, fecha: String, doctor_id: [String,Number,null] })

const filtroFecha = ref(props.fecha)
const filtroDoctor = ref(props.doctor_id || '')
const horaInicio = ref(9)
const horaFin = ref(19)
const modalNueva = ref(false)
const modalDetalle = ref(false)
const citaSeleccionada = ref(null)
const buscarPaciente = ref('')
const resultadosPaciente = ref([])
const nuevaCita = ref({ paciente_id:'', doctor_id:'', fecha_hora:'', duracion_min:30, motivo:'' })

const mostrarLinkReserva = ref(false)
const linkCopiado = ref(false)
const empresaId = window.__inertia?.props?.auth?.user?.empresa_id || ''
const linkReserva = `${window.location.origin}/reservar/${empresaId}`
const linkWA = `https://wa.me/?text=${encodeURIComponent('Agenda tu cita en nuestra clínica: ' + window.location.origin + '/reservar/' + empresaId)}`
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

const recordatorioEnviado = ref(false)
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
  } catch(e) {
    alert('Error al generar recordatorio')
  }
}

const estados = [
  { key:'pendiente_confirmacion', label:'⏳ Por confirmar', color:'#f59e0b', bg:'#fffbeb', border:'#f59e0b' },
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

const citasPorHora = (hora) => citasFiltradas.value.filter(c => {
  const h = new Date(c.fecha_hora).getHours()
  return h === hora
})

const colorEstado = (estado) => {
  const e = estados.find(x => x.key === estado)
  return e ? { bg: e.bg, border: e.border } : { bg:'#F8FAFC', border:'#94A3B8' }
}

const badgeEstado = (estado) => {
  const e = estados.find(x => x.key === estado)
  return e ? { background: e.color, color:'white' } : { background:'#94A3B8', color:'white' }
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
  nuevaCita.value.fecha_hora = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`
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
</script>
