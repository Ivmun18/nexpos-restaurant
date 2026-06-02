<template>
  <AppLayout title="Facturación" subtitle="Comprobantes electrónicos">
  <div style="padding:24px; display:flex; gap:20px; align-items:flex-start;">

    <!-- Lista izquierda -->
    <div style="flex:1; min-width:0;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 style="font-size:20px; font-weight:700; margin:0;">Comprobantes</h1>
        <div style="background:#F0FDF4; padding:8px 16px; border-radius:8px;">
          <span style="font-size:12px; color:#64748B;">Total: </span>
          <span style="font-size:16px; font-weight:800; color:#10B981;">S/ {{ Number(totalVentas).toFixed(2) }}</span>
        </div>
      </div>

      <!-- Filtros fecha -->
      <div style="display:flex; gap:10px; margin-bottom:16px; align-items:center;">
        <input v-model="desdeF" type="date" @change="filtrar" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px;" />
        <span style="color:#94A3B8; font-size:13px;">→</span>
        <input v-model="hastaF" type="date" @change="filtrar" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px;" />
      </div>

      <!-- Tabla -->
      <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr style="background:#F8FAFC;">
              <th style="padding:10px 14px; text-align:left; font-size:11px; color:#64748B; font-weight:600;">Comprobante</th>
              <th style="padding:10px 14px; text-align:left; font-size:11px; color:#64748B; font-weight:600;">Paciente</th>
              <th style="padding:10px 14px; text-align:left; font-size:11px; color:#64748B; font-weight:600;">Fecha</th>
              <th style="padding:10px 14px; text-align:left; font-size:11px; color:#64748B; font-weight:600;">Total</th>
              <th style="padding:10px 14px; text-align:left; font-size:11px; color:#64748B; font-weight:600;">Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="comprobantes.length===0">
              <td colspan="5" style="padding:32px; text-align:center; color:#94A3B8; font-size:13px;">Sin comprobantes en este período</td>
            </tr>
            <tr v-for="c in comprobantes" :key="c.id" style="border-top:1px solid #F1F5F9;">
              <td style="padding:10px 14px;">
                <span :style="c.tipo_comprobante==='01' ? {background:'#EFF6FF',color:'#1D4ED8'} : {background:'#F0FDF4',color:'#15803D'}" style="padding:2px 7px; border-radius:5px; font-size:11px; font-weight:600; margin-right:6px;">{{ c.tipo_comprobante==='01' ? 'F' : 'B' }}</span>
                <span style="font-size:13px; font-weight:600;">{{ c.serie }}-{{ String(c.numero).padStart(8,'0') }}</span>
              </td>
              <td style="padding:10px 14px; font-size:13px;">{{ c.paciente_nombre }}</td>
              <td style="padding:10px 14px; font-size:12px; color:#64748B;">{{ c.fecha_emision }}</td>
              <td style="padding:10px 14px; font-size:13px; font-weight:700; color:#10B981;">S/ {{ Number(c.total).toFixed(2) }}</td>
              <td style="padding:10px 14px;">
                <span :style="estadoStyle(c.estado)">{{ c.estado }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Panel derecho fijo -->
    <div style="width:340px; flex-shrink:0; background:white; border:1px solid #E2E8F0; border-radius:14px; padding:20px; position:sticky; top:24px;">
      <h2 style="font-size:16px; font-weight:700; margin:0 0 18px; color:#8B5CF6;">Emitir comprobante</h2>

      <!-- Tipo -->
      <div style="margin-bottom:14px;">
        <label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:6px;">TIPO</label>
        <div style="display:flex; gap:8px;">
          <button v-for="t in [{v:'03',l:'Boleta'},{v:'01',l:'Factura'}]" :key="t.v" @click="form.tipo_comprobante=t.v"
            :style="form.tipo_comprobante===t.v ? {background:'#8B5CF6',color:'white',borderColor:'#8B5CF6'} : {background:'white',color:'#374151',borderColor:'#E2E8F0'}"
            style="flex:1; padding:8px; border:1px solid; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">
            {{ t.l }}
          </button>
        </div>
      </div>

      <!-- Paciente -->
      <div style="margin-bottom:14px; position:relative;">
        <label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:6px;">PACIENTE *</label>
        <input v-model="buscarPaciente" @input="searchPaciente" @focus="mostrarResultados=true" @blur="setTimeout(()=>mostrarResultados=false,200)" type="text" placeholder="Buscar paciente..."
          style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;" />
        <div v-if="mostrarResultados && resultadosPaciente.length" style="position:absolute; z-index:999; background:white; border:1px solid #E2E8F0; border-radius:8px; width:100%; max-height:180px; overflow-y:auto; box-shadow:0 4px 12px rgba(0,0,0,0.1); top:100%; left:0;">
          <div v-for="p in resultadosPaciente" :key="p.id" @mousedown.prevent="seleccionarPaciente(p)" style="padding:9px 12px; cursor:pointer; font-size:13px; border-bottom:1px solid #F1F5F9;">
            {{ p.apellidos }}, {{ p.nombres }} <span style="color:#94A3B8;">{{ p.dni }}</span>
          </div>
        </div>
      </div>

      <!-- Documento -->
      <div style="margin-bottom:14px;">
        <label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:6px;">{{ form.tipo_comprobante==='01' ? 'RUC *' : 'DNI' }}</label>
        <input v-model="form.cliente_documento" type="text" :maxlength="form.tipo_comprobante==='01' ? 11 : 8" :placeholder="form.tipo_comprobante==='01' ? '20xxxxxxxxx' : '########'"
          style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;" />
      </div>

      <!-- Descripción -->
      <div style="margin-bottom:14px;">
        <label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:6px;">DESCRIPCIÓN *</label>
        <input v-model="form.descripcion" type="text" placeholder="Ej: Servicio odontológico"
          style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;" />
      </div>

      <!-- Monto -->
      <div style="margin-bottom:20px;">
        <label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:6px;">MONTO TOTAL *</label>
        <input v-model="form.total" type="number" step="0.01" placeholder="0.00"
          style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:18px; font-weight:700; text-align:right; box-sizing:border-box;" />
      </div>

      <button @click="emitir" :disabled="loading || !form.paciente_id || !form.total"
        style="width:100%; padding:12px; background:#8B5CF6; color:white; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer;"
        :style="loading || !form.paciente_id || !form.total ? {opacity:'0.6',cursor:'not-allowed'} : {}">
        {{ loading ? '⏳ Emitiendo...' : '🧾 Emitir comprobante' }}
      </button>

      <p v-if="mensaje" :style="mensaje.includes('✅') ? {color:'#10B981'} : {color:'#EF4444'}" style="margin:12px 0 0; font-size:13px; text-align:center; font-weight:600;">{{ mensaje }}</p>
    </div>

  </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ comprobantes: Array, totalVentas: Number, desde: String, hasta: String })
const desdeF = ref(props.desde)
const hastaF = ref(props.hasta)
const loading = ref(false)
const mensaje = ref('')
const buscarPaciente = ref('')
const mostrarResultados = ref(false)
const resultadosPaciente = ref([])
const form = ref({ paciente_id:'', cliente_nombre:'', cliente_documento:'', tipo_comprobante:'03', descripcion:'Servicio odontológico', total:'' })

const filtrar = () => router.get('/odontologia/facturacion', { desde: desdeF.value, hasta: hastaF.value }, { preserveState: true })

const estadoStyle = (e) => {
  const m = { aceptado:{background:'#F0FDF4',color:'#15803D'}, pendiente:{background:'#FFFBEB',color:'#B45309'}, rechazado:{background:'#FEF2F2',color:'#B91C1C'}, anulado:{background:'#F9FAFB',color:'#6B7280'} }
  return { ...(m[e]||{}), padding:'3px 8px', borderRadius:'6px', fontSize:'11px', fontWeight:'600' }
}

const searchPaciente = async () => {
  if (buscarPaciente.value.length < 2) { resultadosPaciente.value = []; return }
  const r = await fetch('/odontologia/pacientes/buscar?q=' + buscarPaciente.value)
  resultadosPaciente.value = await r.json()
}

const seleccionarPaciente = (p) => {
  form.value.paciente_id = p.id
  form.value.cliente_nombre = p.apellidos + ' ' + p.nombres
  form.value.cliente_documento = p.dni || ''
  buscarPaciente.value = p.apellidos + ', ' + p.nombres
  resultadosPaciente.value = []
}

const emitir = async () => {
  loading.value = true
  mensaje.value = ''
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
    const res = await fetch('/odontologia/facturacion/emitir', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify(form.value)
    })
    const data = await res.json()
    if (data.success) {
      mensaje.value = '✅ ' + data.mensaje
      form.value = { paciente_id:'', cliente_nombre:'', cliente_documento:'', tipo_comprobante:'03', descripcion:'Servicio odontológico', total:'' }
      buscarPaciente.value = ''
      setTimeout(() => { mensaje.value = ''; router.reload() }, 2000)
    } else {
      mensaje.value = '❌ ' + data.mensaje
    }
  } finally {
    loading.value = false
  }
}
</script>
