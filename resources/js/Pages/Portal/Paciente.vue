<template>
  <div style="min-height:100vh;background:#f8fafc;font-family:system-ui,sans-serif;">

    <!-- HEADER -->
    <div style="background:#1e1b4b;color:#fff;padding:20px 24px;">
      <div style="max-width:700px;margin:0 auto;display:flex;align-items:center;gap:14px;">
        <div style="width:44px;height:44px;background:#4338ca;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:22px;">🦷</div>
        <div>
          <div style="font-size:16px;font-weight:600;">{{ empresa?.nombre_comercial || empresa?.razon_social }}</div>
          <div style="font-size:12px;color:#a5b4fc;">{{ empresa?.direccion }} · {{ empresa?.telefono }}</div>
        </div>
      </div>
    </div>

    <!-- BIENVENIDA -->
    <div style="background:#4338ca;color:#fff;padding:20px 24px;">
      <div style="max-width:700px;margin:0 auto;">
        <div style="font-size:13px;color:#c7d2fe;margin-bottom:4px;">Bienvenido/a</div>
        <div style="font-size:22px;font-weight:700;">{{ paciente.nombres }} {{ paciente.apellidos }}</div>
        <div style="font-size:12px;color:#a5b4fc;margin-top:4px;">DNI: {{ paciente.dni || '—' }} · {{ paciente.telefono || '' }}</div>
      </div>
    </div>

    <div style="max-width:700px;margin:0 auto;padding:24px 16px;display:flex;flex-direction:column;gap:20px;">

      <!-- PRÓXIMAS CITAS -->
      <div style="background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.07);">
        <div style="font-size:13px;font-weight:600;color:#4338ca;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
          <span>📅</span> Mis citas
        </div>
        <div v-if="citas.length===0" style="text-align:center;padding:20px;color:#94a3b8;font-size:13px;">Sin citas registradas</div>
        <div v-for="c in citas" :key="c.id"
          :style="{borderLeft: `4px solid ${colorCita(c.estado)}`}"
          style="padding:12px 14px;background:#f8fafc;border-radius:0 8px 8px 0;margin-bottom:8px;">
          <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
              <div style="font-size:14px;font-weight:500;color:#1e293b;">{{ formatFecha(c.fecha_hora) }}</div>
              <div style="font-size:12px;color:#64748b;margin-top:2px;">{{ c.motivo || 'Consulta general' }} · Dr. {{ c.doctor?.nombre }}</div>
            </div>
            <span :style="{background:colorCitaBg(c.estado),color:colorCita(c.estado)}" style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:500;">{{ c.estado }}</span>
          </div>
        </div>
      </div>

      <!-- PRESUPUESTOS -->
      <div style="background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.07);">
        <div style="font-size:13px;font-weight:600;color:#4338ca;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
          <span>💰</span> Mis presupuestos
        </div>
        <div v-if="presupuestos.length===0" style="text-align:center;padding:20px;color:#94a3b8;font-size:13px;">Sin presupuestos</div>
        <div v-for="p in presupuestos" :key="p.id" style="border:1px solid #e2e8f0;border-radius:10px;padding:14px;margin-bottom:10px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
            <div>
              <div style="font-size:13px;font-weight:500;color:#1e293b;">Presupuesto #{{ p.id }}</div>
              <div style="font-size:11px;color:#64748b;">{{ p.fecha }} · Dr. {{ p.doctor?.nombre }}</div>
            </div>
            <div style="text-align:right;">
              <div style="font-size:16px;font-weight:700;color:#8B5CF6;">S/ {{ Number(p.total).toFixed(2) }}</div>
              <span :style="{background:p.estado==='aprobado'?'#dcfce7':p.estado==='completado'?'#dbeafe':'#fef3c7',color:p.estado==='aprobado'?'#166534':p.estado==='completado'?'#1e40af':'#92400e'}" style="font-size:10px;padding:2px 8px;border-radius:20px;">{{ p.estado }}</span>
            </div>
          </div>
          <!-- Progreso items -->
          <div v-if="p.items?.length > 0">
            <div style="background:#f1f5f9;border-radius:20px;height:6px;overflow:hidden;margin-bottom:6px;">
              <div :style="{width: pctItems(p)+'%'}" style="background:#8B5CF6;height:100%;border-radius:20px;"></div>
            </div>
            <div style="font-size:11px;color:#94a3b8;">{{ itemsCompletados(p) }}/{{ p.items.length }} tratamientos completados</div>
            <div v-for="item in p.items" :key="item.id" style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid #f8fafc;">
              <span style="font-size:14px;">{{ item.estado_item==='completado' ? '✅' : '⏳' }}</span>
              <span :style="{textDecoration:item.estado_item==='completado'?'line-through':'none',color:item.estado_item==='completado'?'#94a3b8':'#374151'}" style="font-size:12px;flex:1;">{{ item.descripcion }}</span>
              <span style="font-size:12px;font-weight:500;color:#8B5CF6;">S/ {{ Number(item.subtotal).toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- RADIOGRAFÍAS -->
      <div v-if="radiografias.length > 0" style="background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.07);">
        <div style="font-size:13px;font-weight:600;color:#4338ca;text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
          <span>🩻</span> Mis imágenes
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;">
          <a v-for="r in radiografias" :key="r.id" :href="'/storage/'+r.archivo_url" target="_blank" style="text-decoration:none;">
            <div style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
              <img v-if="!r.archivo_url?.endsWith('.pdf')" :src="'/storage/'+r.archivo_url" style="width:100%;height:90px;object-fit:cover;display:block;" />
              <div v-else style="height:90px;background:#f8fafc;display:flex;align-items:center;justify-content:center;font-size:24px;">📄</div>
              <div style="padding:6px 8px;">
                <div style="font-size:11px;font-weight:500;color:#374151;">{{ r.tipo }}</div>
                <div style="font-size:10px;color:#94a3b8;">{{ r.fecha }}</div>
              </div>
            </div>
          </a>
        </div>
      </div>

      <!-- AGENDAR CITA -->
      <div style="background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.07);">
        <div style="font-size:13px;font-weight:600;color:#4338ca;text-transform:uppercase;letter-spacing:.06em;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
          <span>📅</span> Agendar nueva cita
        </div>

        <div v-if="citaConfirmada" style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:16px;text-align:center;">
          <div style="font-size:24px;margin-bottom:8px;">✅</div>
          <div style="font-size:15px;font-weight:600;color:#166534;">¡Cita solicitada!</div>
          <div style="font-size:13px;color:#15803d;margin-top:4px;">La clínica confirmará su cita pronto por WhatsApp o teléfono.</div>
          <button @click="citaConfirmada=false;resetForm()" style="margin-top:12px;padding:8px 20px;background:#10b981;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer;">Agendar otra cita</button>
        </div>

        <div v-else style="display:flex;flex-direction:column;gap:14px;">
          <!-- Paso 1: Doctor -->
          <div>
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:6px;">👨‍⚕️ Doctor</label>
            <div v-if="cargandoDoctores" style="color:#94a3b8;font-size:13px;">Cargando...</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px;">
              <div v-for="d in doctores" :key="d.id" @click="selDoctor=d;selFecha='';selHora='';horasDisp=[]"
                :style="{border: selDoctor?.id===d.id ? '2px solid #4338ca' : '1px solid #e2e8f0', background: selDoctor?.id===d.id ? '#eef2ff' : '#f8fafc'}"
                style="border-radius:10px;padding:12px;cursor:pointer;transition:all .2s;">
                <div style="font-size:13px;font-weight:500;color:#1e293b;">Dr. {{ d.nombre }}</div>
                <div style="font-size:11px;color:#64748b;margin-top:2px;">{{ d.especialidad || 'General' }}</div>
              </div>
            </div>
          </div>

          <!-- Paso 2: Fecha -->
          <div v-if="selDoctor">
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:6px;">📆 Fecha</label>
            <input type="date" v-model="selFecha" :min="fechaMin" @change="cargarHoras"
              style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" />
          </div>

          <!-- Paso 3: Hora -->
          <div v-if="selFecha && horasDisp.length > 0">
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:6px;">🕐 Hora disponible</label>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
              <button v-for="h in horasDisp" :key="h" @click="selHora=h"
                :style="{background: selHora===h ? '#4338ca' : '#f8fafc', color: selHora===h ? '#fff' : '#374151', border: selHora===h ? '2px solid #4338ca' : '1px solid #e2e8f0'}"
                style="padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;">{{ h }}</button>
            </div>
          </div>
          <div v-else-if="selFecha && horasDisp.length === 0 && !cargandoHoras" style="background:#fef3c7;border-radius:8px;padding:12px;font-size:13px;color:#92400e;">
            ⚠️ No hay horarios disponibles para esta fecha. Prueba otro día.
          </div>

          <!-- Paso 4: Motivo -->
          <div v-if="selHora">
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:6px;">📝 Motivo (opcional)</label>
            <input v-model="motivo" style="width:100%;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="Ej: Limpieza dental, dolor muela..." />
          </div>

          <!-- Resumen y confirmar -->
          <div v-if="selHora" style="background:#eef2ff;border-radius:10px;padding:14px;">
            <div style="font-size:13px;font-weight:500;color:#4338ca;margin-bottom:8px;">Resumen de tu cita:</div>
            <div style="font-size:13px;color:#374151;line-height:1.8;">
              👨‍⚕️ Dr. {{ selDoctor?.nombre }}<br>
              📅 {{ formatFechaResumen(selFecha) }} a las {{ selHora }}<br>
              📝 {{ motivo || 'Sin motivo especificado' }}
            </div>
            <button @click="confirmarCita" :disabled="enviando"
              style="width:100%;margin-top:12px;padding:12px;background:#4338ca;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
              {{ enviando ? 'Enviando...' : '✅ Confirmar cita' }}
            </button>
          </div>
        </div>
      </div>

      <!-- FOOTER -->
      <div style="text-align:center;padding:16px;color:#94a3b8;font-size:11px;">
        {{ empresa?.razon_social }} · RUC {{ empresa?.ruc }}<br>
        {{ empresa?.direccion }}<br>
        📞 {{ empresa?.telefono }}
      </div>

    </div>
  </div>
</template>

<script setup>
const props = defineProps({ paciente:Object, empresa:Object, citas:Array, presupuestos:Array, radiografias:Array })

// Agendamiento
const doctores       = ref([])
const selDoctor      = ref(null)
const selFecha       = ref('')
const selHora        = ref('')
const horasDisp      = ref([])
const motivo         = ref('')
const citaConfirmada = ref(false)
const enviando       = ref(false)
const cargandoHoras  = ref(false)
const cargandoDoctores = ref(false)
const fechaMin       = new Date(Date.now() + 86400000).toISOString().split('T')[0]
const token          = window.location.pathname.split('/')[2]

const cargarDoctores = async () => {
  cargandoDoctores.value = true
  const res = await fetch(`/portal/${token}/doctores`)
  doctores.value = await res.json()
  cargandoDoctores.value = false
}

const cargarHoras = async () => {
  if (!selDoctor.value || !selFecha.value) return
  cargandoHoras.value = true
  horasDisp.value = []
  selHora.value = ''
  const res = await fetch(`/portal/${token}/horas?doctor_id=${selDoctor.value.id}&fecha=${selFecha.value}`)
  horasDisp.value = await res.json()
  cargandoHoras.value = false
}

const confirmarCita = async () => {
  enviando.value = true
  const res = await fetch(`/portal/${token}/agendar`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
    body: JSON.stringify({ doctor_id: selDoctor.value.id, fecha: selFecha.value, hora: selHora.value, motivo: motivo.value })
  })
  const data = await res.json()
  enviando.value = false
  if (data.ok) citaConfirmada.value = true
}

const resetForm = () => { selDoctor.value=null; selFecha.value=''; selHora.value=''; horasDisp.value=[]; motivo.value='' }
const formatFechaResumen = (f) => new Date(f+'T12:00:00').toLocaleDateString('es-PE', { weekday:'long', day:'2-digit', month:'long' })

import { onMounted } from 'vue'
onMounted(cargarDoctores)

const formatFecha = (f) => new Date(f).toLocaleDateString('es-PE', { weekday:'short', day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' })
const colorCita = (e) => ({ programada:'#8B5CF6', confirmada:'#10b981', completada:'#0ea5e9', cancelada:'#ef4444' }[e] || '#94a3b8')
const colorCitaBg = (e) => ({ programada:'#f5f3ff', confirmada:'#f0fdf4', completada:'#e0f2fe', cancelada:'#fee2e2' }[e] || '#f8fafc')
const itemsCompletados = (p) => p.items?.filter(i => i.estado_item==='completado').length || 0
const pctItems = (p) => p.items?.length ? Math.round((itemsCompletados(p)/p.items.length)*100) : 0
</script>
