<template>
  <AppLayout>
    <div style="padding:24px; max-width:900px; margin:0 auto;">

      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <div>
          <h1 style="margin:0; font-size:22px; font-weight:700; color:#1E293B;">Odontograma</h1>
          <p style="margin:4px 0 0; font-size:14px; color:#64748B;">{{ paciente.apellidos }}, {{ paciente.nombres }}</p>
        </div>
        <div style="display:flex; gap:8px;">
          <span v-if="guardando" style="font-size:12px; color:#94A3B8; padding:8px;">Guardando...</span>
          <span v-if="guardado" style="font-size:12px; color:#16A34A; padding:8px;">✓ Guardado</span>
          <a :href="'/odontologia/pacientes'" style="padding:9px 16px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#64748B; text-decoration:none;">← Pacientes</a>
        </div>
      </div>

      <!-- Selector de estado -->
      <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; padding:14px 16px; margin-bottom:16px;">
        <p style="margin:0 0 10px; font-size:11px; font-weight:600; color:#64748B; text-transform:uppercase;">Estado activo — clic en diente para aplicar</p>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
          <button v-for="e in estados" :key="e.key" @click="estadoActivo=e.key"
            :style="estadoActivo===e.key ? `background:${e.fill}; border:2px solid ${e.stroke}; color:${e.dark}; font-weight:700;` : `background:white; border:1.5px solid #E2E8F0; color:#64748B;`"
            style="padding:7px 14px; border-radius:20px; font-size:12px; cursor:pointer;">
            {{ e.label }}
          </button>
        </div>
      </div>

      <!-- Odontograma SVG -->
      <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; padding:20px;">
        <p style="margin:0 0 8px; font-size:11px; color:#94A3B8; text-align:center;">Superior (maxilar)</p>
        <svg :viewBox="`0 0 660 280`" xmlns="http://www.w3.org/2000/svg" style="width:100%; display:block;">
          <line x1="330" y1="0" x2="330" y2="280" stroke="#F1F5F9" stroke-width="1" stroke-dasharray="4,3"/>
          <line x1="20" y1="140" x2="640" y2="140" stroke="#F1F5F9" stroke-width="0.5"/>

          <!-- Dientes superiores -->
          <g v-for="(t,i) in superiores" :key="t.fdi" @click="clickDiente(t.fdi)" style="cursor:pointer;">
            <!-- Raíces -->
            <path v-for="(r,ri) in getRoots(t.cx, t.cy, t.type, true)" :key="ri"
              :d="r" fill="none" :stroke="getColor(t.fdi).stroke" stroke-width="2" stroke-linecap="round"/>
            <!-- Corona -->
            <path :d="getCrown(t.cx, t.cy, t.type)"
              :fill="getColor(t.fdi).fill"
              :stroke="getColor(t.fdi).stroke"
              stroke-width="1.5"/>
            <!-- Número FDI -->
            <text :x="t.cx" :y="t.cy - 38" text-anchor="middle" font-size="8" fill="#94A3B8" font-family="sans-serif">{{ t.fdi }}</text>
            <!-- X si ausente -->
            <line v-if="getEstado(t.fdi)==='ausente'" :x1="t.cx-8" :y1="t.cy-8" :x2="t.cx+8" :y2="t.cy+8" stroke="#888" stroke-width="2"/>
            <line v-if="getEstado(t.fdi)==='ausente'" :x1="t.cx+8" :y1="t.cy-8" :x2="t.cx-8" :y2="t.cy+8" stroke="#888" stroke-width="2"/>
          </g>

          <!-- Dientes inferiores -->
          <g v-for="(t,i) in inferiores" :key="t.fdi" @click="clickDiente(t.fdi)" style="cursor:pointer;">
            <path v-for="(r,ri) in getRoots(t.cx, t.cy, t.type, false)" :key="ri"
              :d="r" fill="none" :stroke="getColor(t.fdi).stroke" stroke-width="2" stroke-linecap="round"/>
            <path :d="getCrownInf(t.cx, t.cy, t.type)"
              :fill="getColor(t.fdi).fill"
              :stroke="getColor(t.fdi).stroke"
              stroke-width="1.5"/>
            <text :x="t.cx" :y="t.cy + 48" text-anchor="middle" font-size="8" fill="#94A3B8" font-family="sans-serif">{{ t.fdi }}</text>
            <line v-if="getEstado(t.fdi)==='ausente'" :x1="t.cx-8" :y1="t.cy-8" :x2="t.cx+8" :y2="t.cy+8" stroke="#888" stroke-width="2"/>
            <line v-if="getEstado(t.fdi)==='ausente'" :x1="t.cx+8" :y1="t.cy-8" :x2="t.cx-8" :y2="t.cy+8" stroke="#888" stroke-width="2"/>
          </g>
        </svg>
        <p style="margin:8px 0 0; font-size:11px; color:#94A3B8; text-align:center;">Inferior (mandibular)</p>
      </div>

      <!-- Leyenda -->
      <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:12px;">
        <span v-for="e in estados" :key="e.key" style="display:flex; align-items:center; gap:5px; font-size:11px; color:#64748B;">
          <span :style="`width:12px;height:12px;border-radius:3px;background:${e.fill};border:1.5px solid ${e.stroke};display:inline-block;`"></span>
          {{ e.label }}
        </span>
      </div>

      <!-- Panel de nota -->
      <div v-if="dienteSeleccionado" style="margin-top:14px; background:white; border:1px solid #E2E8F0; border-radius:12px; padding:16px;">
        <p style="margin:0 0 8px; font-size:13px; font-weight:600;">Diente {{ dienteSeleccionado }} — Notas</p>
        <textarea v-model="notaActual" @blur="guardarNota" rows="2"
          placeholder="Observaciones del tratamiento..."
          style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; resize:none; box-sizing:border-box;"></textarea>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed } from 'vue'

const props = defineProps({ paciente: Object, odontograma: Object })

const estadoActivo = ref('sano')
const dienteStates = ref({})
const dienteSeleccionado = ref(null)
const notaActual = ref('')
const guardando = ref(false)
const guardado = ref(false)

// Inicializar estados desde BD
Object.entries(props.odontograma).forEach(([fdi, data]) => {
  dienteStates.value[fdi] = { estado: data.estado, notas: data.notas || '' }
})

const estados = [
  { key:'sano',        label:'Sano',        fill:'#9FE1CB', stroke:'#0F6E56', dark:'#085041' },
  { key:'caries',      label:'Caries',      fill:'#F09595', stroke:'#791F1F', dark:'#501313' },
  { key:'tratamiento', label:'Tratamiento',  fill:'#FAC775', stroke:'#854F0B', dark:'#412402' },
  { key:'extraccion',  label:'Extracción',   fill:'#AFA9EC', stroke:'#3C3489', dark:'#26215C' },
  { key:'ausente',     label:'Ausente',      fill:'#B4B2A9', stroke:'#444441', dark:'#2C2C2A' },
  { key:'corona',      label:'Corona',       fill:'#85B7EB', stroke:'#0C447C', dark:'#042C53' },
  { key:'implante',    label:'Implante',     fill:'#97C459', stroke:'#27500A', dark:'#173404' },
  { key:'sellante',    label:'Sellante',     fill:'#ED93B1', stroke:'#72243E', dark:'#4B1528' },
]

const types = ['molar','molar','molar','premolar','premolar','canino','incisivo','incisivo','incisivo','incisivo','canino','premolar','premolar','molar','molar','molar']
const supFDI = [18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28]
const infFDI = [48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38]

const startX = 30; const gapX = 38; const yTop = 95; const yBot = 185

const superiores = supFDI.map((fdi,i) => ({ fdi, cx: startX+i*gapX, cy: yTop, type: types[i] }))
const inferiores = infFDI.map((fdi,i) => ({ fdi, cx: startX+i*gapX, cy: yBot, type: types[i] }))

const getEstado = (fdi) => dienteStates.value[fdi]?.estado || 'sano'
const getColor = (fdi) => {
  const e = estados.find(x => x.key === getEstado(fdi))
  return e || estados[0]
}

const cw = { molar:24, premolar:18, canino:14, incisivo:13 }
const ch = 26

const getCrown = (cx, cy, type) => {
  const w = cw[type]; const h = ch
  return `M${cx},${cy-h/2} Q${cx+w/2},${cy-h*0.3} ${cx+w/2},${cy+h*0.1} Q${cx+w*0.4},${cy+h/2} ${cx},${cy+h*0.55} Q${cx-w*0.4},${cy+h/2} ${cx-w/2},${cy+h*0.1} Q${cx-w/2},${cy-h*0.3} ${cx},${cy-h/2}Z`
}
const getCrownInf = (cx, cy, type) => {
  const w = cw[type]; const h = ch
  return `M${cx},${cy+h/2} Q${cx+w/2},${cy+h*0.3} ${cx+w/2},${cy-h*0.1} Q${cx+w*0.4},${cy-h/2} ${cx},${cy-h*0.55} Q${cx-w*0.4},${cy-h/2} ${cx-w/2},${cy-h*0.1} Q${cx-w/2},${cy+h*0.3} ${cx},${cy+h/2}Z`
}

const getRoots = (cx, cy, type, isTop) => {
  const dir = isTop ? 1 : -1
  const base = isTop ? cy + ch/2 : cy - ch/2
  const rh = { molar:28, premolar:26, canino:30, incisivo:24 }[type]
  if (type === 'molar') return [
    `M${cx-7},${base} Q${cx-9},${base+dir*rh*0.8} ${cx-5},${base+dir*rh}`,
    `M${cx+7},${base} Q${cx+9},${base+dir*rh*0.8} ${cx+5},${base+dir*rh}`,
    `M${cx},${base} Q${cx},${base+dir*rh*0.65} ${cx},${base+dir*rh*0.85}`,
  ]
  if (type === 'premolar') return [
    `M${cx-4},${base} Q${cx-6},${base+dir*rh*0.8} ${cx-3},${base+dir*rh}`,
    `M${cx+4},${base} Q${cx+6},${base+dir*rh*0.8} ${cx+3},${base+dir*rh}`,
  ]
  return [`M${cx},${base} Q${cx},${base+dir*rh*0.7} ${cx},${base+dir*rh}`]
}

const clickDiente = async (fdi) => {
  if (!dienteStates.value[fdi]) dienteStates.value[fdi] = { estado:'sano', notas:'' }
  dienteStates.value[fdi].estado = estadoActivo.value
  dienteSeleccionado.value = fdi
  notaActual.value = dienteStates.value[fdi].notas || ''
  await guardar(fdi)
}

const guardarNota = async () => {
  if (!dienteSeleccionado.value) return
  dienteStates.value[dienteSeleccionado.value].notas = notaActual.value
  await guardar(dienteSeleccionado.value)
}

const guardar = async (fdi) => {
  guardando.value = true
  guardado.value = false
  try {
    await fetch(`/odontologia/odontograma/${props.paciente.id}`, {
      method: 'PUT',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
      },
      body: JSON.stringify({
        diente: fdi,
        estado: dienteStates.value[fdi].estado,
        notas: dienteStates.value[fdi].notas || ''
      })
    })
    guardado.value = true
    setTimeout(() => guardado.value = false, 2000)
  } finally {
    guardando.value = false
  }
}
</script>
