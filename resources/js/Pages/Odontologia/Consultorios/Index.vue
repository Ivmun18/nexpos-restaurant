<template>
  <AppLayout>
    <div style="padding:20px 24px;">

      <!-- HEADER -->
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        <div>
          <h1 style="font-size:20px;font-weight:600;color:#1e293b;margin:0;">Consultorios</h1>
          <p style="font-size:13px;color:#64748b;margin:4px 0 0;">Vista de agenda por consultorio</p>
        </div>
        <div style="margin-left:auto;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
          <input type="date" v-model="fecha" @change="aplicar"
            style="padding:7px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;" />
          <button @click="setHoy" style="padding:7px 14px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;background:#fff;cursor:pointer;">Hoy</button>
          <button @click="modalConsultorio=true" style="padding:7px 16px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;">+ Consultorio</button>
        </div>
      </div>

      <!-- ESTADO VACÍO - SIN CONSULTORIOS -->
      <div v-if="consultorios.length===0" style="text-align:center;padding:60px;background:#fff;border-radius:14px;border:1px solid #e2e8f0;">
        <div style="font-size:48px;margin-bottom:12px;">🏥</div>
        <div style="font-size:16px;font-weight:500;color:#1e293b;margin-bottom:6px;">Sin consultorios configurados</div>
        <div style="font-size:13px;color:#64748b;margin-bottom:20px;">Agrega los consultorios de tu clínica para ver la agenda por silla</div>
        <button @click="modalConsultorio=true" style="padding:10px 24px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;">+ Agregar consultorio</button>
      </div>

      <!-- VISTA DE AGENDA POR CONSULTORIOS -->
      <div v-else style="overflow-x:auto;">
        <div :style="{gridTemplateColumns: `120px repeat(${consultorios.length}, 1fr)`}"
          style="display:grid;gap:1px;background:#e2e8f0;border-radius:12px;overflow:hidden;min-width:600px;">

          <!-- ENCABEZADOS -->
          <div style="background:#f8fafc;padding:12px;font-size:11px;color:#94a3b8;font-weight:500;text-transform:uppercase;">Hora</div>
          <div v-for="c in consultorios" :key="c.id"
            style="background:#f8fafc;padding:12px;display:flex;align-items:center;gap:8px;">
            <div :style="{background:c.color}" style="width:12px;height:12px;border-radius:50%;flex-shrink:0;"></div>
            <div>
              <div style="font-size:13px;font-weight:600;color:#1e293b;">{{ c.nombre }}</div>
            </div>
            <button @click="editarConsultorio(c)" style="margin-left:auto;background:none;border:none;color:#94a3b8;cursor:pointer;font-size:14px;">⚙️</button>
          </div>

          <!-- FILAS DE HORAS -->
          <template v-for="hora in horasDelDia" :key="hora">
            <!-- Columna hora -->
            <div style="background:#fff;padding:8px 12px;font-size:12px;color:#94a3b8;font-weight:500;border-top:1px solid #f1f5f9;">{{ hora }}</div>

            <!-- Columna por consultorio -->
            <div v-for="c in consultorios" :key="c.id"
              style="background:#fff;padding:4px;border-top:1px solid #f1f5f9;min-height:52px;position:relative;"
              @click="abrirNuevaCita(hora, c.id)">

              <!-- Cita en este slot -->
              <div v-for="cita in citasEnSlot(c.id, hora)" :key="cita.id"
                :style="{background: c.color + '20', borderLeft: `3px solid ${c.color}`}"
                style="border-radius:6px;padding:6px 8px;margin:2px;cursor:pointer;"
                @click.stop="selCita=cita;modalDetalle=true">
                <div style="font-size:12px;font-weight:600;color:#1e293b;">{{ cita.paciente?.nombres }} {{ cita.paciente?.apellidos }}</div>
                <div style="font-size:11px;color:#64748b;">Dr. {{ cita.doctor?.nombre }}</div>
                <span :style="badgeEstado(cita.estado)" style="font-size:10px;padding:1px 6px;border-radius:10px;font-weight:500;">{{ cita.estado }}</span>
              </div>

              <!-- Slot vacío hover -->
              <div v-if="citasEnSlot(c.id, hora).length===0"
                style="height:100%;display:flex;align-items:center;justify-content:center;color:#e2e8f0;font-size:18px;opacity:0;transition:opacity .2s;"
                onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">+</div>
            </div>
          </template>
        </div>
      </div>

      <!-- MODAL NUEVA CITA DESDE CONSULTORIO -->
      <div v-if="modalNuevaCita" style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:1000;padding:20px;">
        <div style="background:#fff;border-radius:16px;padding:28px;width:440px;max-width:95vw;">
          <div style="display:flex;justify-content:space-between;margin-bottom:20px;">
            <h2 style="margin:0;font-size:17px;font-weight:600;">Nueva cita</h2>
            <button @click="modalNuevaCita=false" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
          </div>
          <div style="display:flex;flex-direction:column;gap:12px;">
            <div style="background:#f0f0ff;border-radius:8px;padding:10px 14px;font-size:13px;color:#4338ca;">
              📅 {{ fecha }} a las {{ formCita.hora }} — {{ consultorios.find(c=>c.id===formCita.consultorio_id)?.nombre }}
            </div>
            <div>
              <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Doctor</label>
              <select v-model="formCita.doctor_id" style="width:100%;padding:9px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                <option value="">Seleccionar...</option>
                <option v-for="d in doctores" :key="d.id" :value="d.id">Dr. {{ d.nombre }}</option>
              </select>
            </div>
            <div>
              <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Paciente (nombre)</label>
              <input v-model="formCita.paciente_nombre" style="width:100%;padding:9px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="Buscar paciente..." />
            </div>
            <div>
              <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Motivo</label>
              <input v-model="formCita.motivo" style="width:100%;padding:9px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="Opcional" />
            </div>
            <button @click="guardarCita" style="padding:10px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;">Guardar cita</button>
          </div>
        </div>
      </div>

      <!-- MODAL CONSULTORIO -->
      <div v-if="modalConsultorio" style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:1000;padding:20px;">
        <div style="background:#fff;border-radius:16px;padding:28px;width:380px;max-width:95vw;">
          <div style="display:flex;justify-content:space-between;margin-bottom:20px;">
            <h2 style="margin:0;font-size:17px;font-weight:600;">{{ editandoConsultorio ? 'Editar' : 'Nuevo' }} consultorio</h2>
            <button @click="cerrarModalConsultorio" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
          </div>
          <div style="display:flex;flex-direction:column;gap:12px;">
            <div>
              <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Nombre *</label>
              <input v-model="formConsultorio.nombre" style="width:100%;padding:9px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="Ej: Consultorio 1, Sala A..." />
            </div>
            <div>
              <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Color</label>
              <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <div v-for="col in colores" :key="col" @click="formConsultorio.color=col"
                  :style="{background:col, outline: formConsultorio.color===col ? '3px solid #1e293b' : 'none', outlineOffset:'2px'}"
                  style="width:28px;height:28px;border-radius:50%;cursor:pointer;"></div>
              </div>
            </div>
            <div style="display:flex;gap:8px;">
              <button @click="guardarConsultorio" style="flex:1;padding:10px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;">Guardar</button>
              <button v-if="editandoConsultorio" @click="eliminarConsultorio" style="padding:10px 16px;background:#fee2e2;color:#991b1b;border:none;border-radius:8px;font-size:13px;cursor:pointer;">Eliminar</button>
            </div>
          </div>
        </div>
      </div>

      <!-- MODAL DETALLE CITA -->
      <div v-if="modalDetalle && selCita" style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:1000;padding:20px;">
        <div style="background:#fff;border-radius:16px;padding:28px;width:380px;max-width:95vw;">
          <div style="display:flex;justify-content:space-between;margin-bottom:16px;">
            <h2 style="margin:0;font-size:17px;font-weight:600;">Detalle de cita</h2>
            <button @click="modalDetalle=false" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
          </div>
          <div style="display:flex;flex-direction:column;gap:10px;font-size:13px;color:#374151;">
            <div><span style="color:#94a3b8;">Paciente:</span> {{ selCita.paciente?.nombres }} {{ selCita.paciente?.apellidos }}</div>
            <div><span style="color:#94a3b8;">Doctor:</span> Dr. {{ selCita.doctor?.nombre }}</div>
            <div><span style="color:#94a3b8;">Hora:</span> {{ formatHora(selCita.fecha_hora) }}</div>
            <div><span style="color:#94a3b8;">Consultorio:</span> {{ selCita.consultorio?.nombre || 'Sin asignar' }}</div>
            <div><span style="color:#94a3b8;">Motivo:</span> {{ selCita.motivo || '—' }}</div>
            <div><span style="color:#94a3b8;">Estado:</span> <span :style="badgeEstado(selCita.estado)" style="padding:2px 8px;border-radius:20px;font-size:11px;">{{ selCita.estado }}</span></div>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ consultorios:Array, doctores:Array, citas:Array, fecha:String })

const fecha          = ref(props.fecha)
const modalConsultorio = ref(false)
const modalNuevaCita = ref(false)
const modalDetalle   = ref(false)
const selCita        = ref(null)
const editandoConsultorio = ref(null)

const colores = ['#8B5CF6','#0ea5e9','#10b981','#f59e0b','#ef4444','#ec4899','#6366f1','#14b8a6']

const formConsultorio = ref({ nombre:'', color:'#8B5CF6' })
const formCita = ref({ hora:'', consultorio_id:null, doctor_id:'', paciente_nombre:'', motivo:'' })

// Horas de 8am a 8pm
const horasDelDia = computed(() => {
  const h = []
  for (let i = 8; i <= 19; i++) {
    h.push(`${String(i).padStart(2,'0')}:00`)
    h.push(`${String(i).padStart(2,'0')}:30`)
  }
  return h
})

const citasEnSlot = (consultorioId, hora) => {
  return props.citas.filter(c => {
    if (c.consultorio_id !== consultorioId) return false
    const h = new Date(c.fecha_hora).toLocaleTimeString('es-PE', {hour:'2-digit',minute:'2-digit',hour12:false})
    return h === hora
  })
}

const badgeEstado = (e) => {
  const m = { programada:{background:'#ede9fe',color:'#6d28d9'}, confirmada:{background:'#dcfce7',color:'#166534'}, completada:{background:'#dbeafe',color:'#1e40af'}, cancelada:{background:'#fee2e2',color:'#991b1b'}, pendiente_confirmacion:{background:'#fef3c7',color:'#92400e'} }
  return m[e] || {background:'#f1f5f9',color:'#475569'}
}

const formatHora = (f) => new Date(f).toLocaleTimeString('es-PE', {hour:'2-digit',minute:'2-digit'})

const aplicar = () => router.get('/odontologia/consultorios', { fecha: fecha.value }, { preserveState: false })
const setHoy  = () => { fecha.value = new Date().toISOString().split('T')[0]; aplicar() }

const abrirNuevaCita = (hora, consultorioId) => {
  formCita.value = { hora, consultorio_id: consultorioId, doctor_id:'', paciente_nombre:'', motivo:'' }
  modalNuevaCita.value = true
}

const guardarCita = () => {
  router.post('/odontologia/citas', {
    fecha_hora: `${fecha.value} ${formCita.value.hora}:00`,
    consultorio_id: formCita.value.consultorio_id,
    doctor_id: formCita.value.doctor_id,
    motivo: formCita.value.motivo,
    duracion_min: 30,
    paciente_id: 1, // simplificado - mejorar con búsqueda
  }, { onSuccess: () => { modalNuevaCita.value = false; aplicar() } })
}

const editarConsultorio = (c) => {
  editandoConsultorio.value = c.id
  formConsultorio.value = { nombre: c.nombre, color: c.color }
  modalConsultorio.value = true
}

const cerrarModalConsultorio = () => {
  modalConsultorio.value = false
  editandoConsultorio.value = null
  formConsultorio.value = { nombre:'', color:'#8B5CF6' }
}

const guardarConsultorio = () => {
  if (editandoConsultorio.value) {
    router.put(`/odontologia/consultorios/${editandoConsultorio.value}`, formConsultorio.value, { onSuccess: cerrarModalConsultorio })
  } else {
    router.post('/odontologia/consultorios', formConsultorio.value, { onSuccess: cerrarModalConsultorio })
  }
}

const eliminarConsultorio = () => {
  if (confirm('¿Eliminar este consultorio?'))
    router.delete(`/odontologia/consultorios/${editandoConsultorio.value}`, { onSuccess: cerrarModalConsultorio })
}
</script>
