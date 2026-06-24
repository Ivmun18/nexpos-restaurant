<template>
  <div style="min-height:100vh;background:#f8fafc;font-family:system-ui,sans-serif;">

    <!-- HEADER -->
    <div style="background:#1e1b4b;color:#fff;padding:20px 24px;text-align:center;">
      <div style="font-size:28px;margin-bottom:6px;">🦷</div>
      <div style="font-size:20px;font-weight:700;">{{ empresa.nombre_comercial || empresa.razon_social }}</div>
      <div style="font-size:13px;color:#a5b4fc;margin-top:4px;">{{ empresa.direccion }} · {{ empresa.telefono }}</div>
    </div>

    <div style="max-width:600px;margin:0 auto;padding:24px 16px;">

      <!-- ÉXITO -->
      <div v-if="citaConfirmada" style="background:#fff;border-radius:14px;padding:32px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.08);">
        <div style="font-size:48px;margin-bottom:12px;">✅</div>
        <div style="font-size:20px;font-weight:700;color:#166534;margin-bottom:8px;">¡Cita solicitada!</div>
        <div style="font-size:14px;color:#374151;line-height:1.7;margin-bottom:20px;">
          Hemos recibido tu solicitud.<br>
          La clínica te confirmará por WhatsApp o teléfono al número <strong>{{ form.telefono }}</strong>.
        </div>
        <div style="background:#f0fdf4;border-radius:10px;padding:16px;margin-bottom:20px;text-align:left;">
          <div style="font-size:13px;color:#166534;line-height:2;">
            👤 {{ form.nombres }} {{ form.apellidos }}<br>
            👨‍⚕️ Dr. {{ selDoctor?.nombre }}<br>
            📅 {{ formatFechaResumen(form.fecha) }} a las {{ form.hora }}<br>
            📝 {{ form.motivo || 'Sin motivo especificado' }}
          </div>
        </div>
        <button @click="resetear" style="padding:10px 28px;background:#4338ca;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;">Agendar otra cita</button>
      </div>

      <!-- FORMULARIO -->
      <div v-else style="background:#fff;border-radius:14px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,.08);">
        <h1 style="font-size:20px;font-weight:700;color:#1e293b;margin:0 0 6px;">Solicitar una cita</h1>
        <p style="font-size:13px;color:#64748b;margin:0 0 24px;">Completa el formulario y te confirmaremos por WhatsApp.</p>

        <!-- Datos personales -->
        <div style="margin-bottom:20px;">
          <div style="font-size:11px;font-weight:600;color:#4338ca;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">Tus datos</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
            <div>
              <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Nombres *</label>
              <input v-model="form.nombres" style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="Juan" />
            </div>
            <div>
              <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Apellidos *</label>
              <input v-model="form.apellidos" style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="Pérez" />
            </div>
          </div>
          <div>
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Teléfono / WhatsApp *</label>
            <input v-model="form.telefono" type="tel" style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="999 999 999" />
          </div>
        </div>

        <!-- Doctor -->
        <div style="margin-bottom:20px;">
          <div style="font-size:11px;font-weight:600;color:#4338ca;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">Selecciona el doctor</div>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <div v-for="d in doctores" :key="d.id" @click="selDoctor=d;form.hora='';horasDisp=[]"
              :style="{border: selDoctor?.id===d.id ? '2px solid #4338ca' : '1px solid #e2e8f0', background: selDoctor?.id===d.id ? '#eef2ff' : '#f8fafc'}"
              style="border-radius:10px;padding:14px 16px;cursor:pointer;display:flex;align-items:center;gap:12px;transition:all .2s;">
              <div style="width:40px;height:40px;background:#e0e7ff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">👨‍⚕️</div>
              <div>
                <div style="font-size:14px;font-weight:500;color:#1e293b;">Dr. {{ d.nombre }}</div>
                <div style="font-size:12px;color:#64748b;">{{ d.especialidad || 'Odontología General' }}</div>
              </div>
              <div v-if="selDoctor?.id===d.id" style="margin-left:auto;color:#4338ca;font-size:20px;">✓</div>
            </div>
          </div>
        </div>

        <!-- Fecha -->
        <div v-if="selDoctor" style="margin-bottom:20px;">
          <div style="font-size:11px;font-weight:600;color:#4338ca;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">Selecciona la fecha</div>
          <input type="date" v-model="form.fecha" :min="fechaMin" @change="cargarHoras"
            style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;box-sizing:border-box;" />
        </div>

        <!-- Horas -->
        <div v-if="form.fecha && horasDisp.length > 0" style="margin-bottom:20px;">
          <div style="font-size:11px;font-weight:600;color:#4338ca;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">Hora disponible</div>
          <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <button v-for="h in horasDisp" :key="h" @click="form.hora=h"
              :style="{background: form.hora===h ? '#4338ca':'#f8fafc', color: form.hora===h ? '#fff':'#374151', border: form.hora===h ? '2px solid #4338ca':'1px solid #e2e8f0'}"
              style="padding:10px 18px;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;">{{ h }}</button>
          </div>
        </div>
        <div v-else-if="form.fecha && !cargandoHoras && horasDisp.length===0 && selDoctor" style="background:#fef3c7;border-radius:8px;padding:12px;font-size:13px;color:#92400e;margin-bottom:20px;">
          ⚠️ No hay horarios disponibles para esta fecha. Prueba con otro día.
        </div>

        <!-- Motivo -->
        <div v-if="form.hora" style="margin-bottom:24px;">
          <div style="font-size:11px;font-weight:600;color:#4338ca;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">Motivo de consulta</div>
          <input v-model="form.motivo" style="width:100%;padding:10px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="Ej: Limpieza, dolor de muela, revisión..." />
        </div>

        <!-- Resumen y botón -->
        <div v-if="form.hora">
          <div style="background:#eef2ff;border-radius:10px;padding:16px;margin-bottom:16px;">
            <div style="font-size:12px;font-weight:600;color:#4338ca;margin-bottom:8px;">Resumen de tu cita:</div>
            <div style="font-size:13px;color:#374151;line-height:2;">
              👤 {{ form.nombres }} {{ form.apellidos }}<br>
              👨‍⚕️ Dr. {{ selDoctor?.nombre }}<br>
              📅 {{ formatFechaResumen(form.fecha) }} a las {{ form.hora }}<br>
              📞 {{ form.telefono }}
            </div>
          </div>
          <button @click="confirmar" :disabled="enviando || !form.nombres || !form.apellidos || !form.telefono"
            style="width:100%;padding:14px;background:#4338ca;color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;"
            :style="{opacity: (!form.nombres||!form.apellidos||!form.telefono||enviando) ? 0.5 : 1}">
            {{ enviando ? 'Enviando...' : '✅ Solicitar cita' }}
          </button>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props     = defineProps({ empresa: Object, doctores: Array })
const selDoctor = ref(null)
const horasDisp = ref([])
const enviando  = ref(false)
const cargandoHoras = ref(false)
const citaConfirmada = ref(false)
const fechaMin  = new Date(Date.now() + 86400000).toISOString().split('T')[0]
const slug      = props.empresa.id

const form = ref({ nombres:'', apellidos:'', telefono:'', fecha:'', hora:'', motivo:'' })

const formatFechaResumen = (f) => f ? new Date(f+'T12:00:00').toLocaleDateString('es-PE', { weekday:'long', day:'2-digit', month:'long', year:'numeric' }) : ''

const cargarHoras = async () => {
  if (!selDoctor.value || !form.value.fecha) return
  cargandoHoras.value = true
  horasDisp.value = []
  form.value.hora = ''
  const res = await fetch(`/reservar/${slug}/horas?doctor_id=${selDoctor.value.id}&fecha=${form.value.fecha}`)
  horasDisp.value = await res.json()
  cargandoHoras.value = false
}

const confirmar = async () => {
  if (!form.value.nombres || !form.value.apellidos || !form.value.telefono) return
  enviando.value = true
  const res = await fetch(`/reservar/${slug}/agendar`, {
    method: 'POST',
    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content||'' },
    body: JSON.stringify({ ...form.value, doctor_id: selDoctor.value.id })
  })
  const data = await res.json()
  enviando.value = false
  if (data.ok) {
    citaConfirmada.value = true
    if (data.notif_url) {
      // Abrir WhatsApp para notificar a la clínica
      setTimeout(() => window.open(data.notif_url, '_blank'), 1000)
    }
  }
}

const resetear = () => {
  citaConfirmada.value = false
  selDoctor.value = null
  horasDisp.value = []
  form.value = { nombres:'', apellidos:'', telefono:'', fecha:'', hora:'', motivo:'' }
}
</script>
