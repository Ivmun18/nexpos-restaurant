<template>
  <AppLayout title="Facturación" subtitle="Comprobantes electrónicos">
  <div style="padding:24px; max-width:1200px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h1 style="font-size:22px; font-weight:700; margin:0;">Facturación</h1>
      <button @click="modalNuevo=true" style="background:#8B5CF6; color:white; padding:10px 20px; border:none; border-radius:8px; font-weight:600; font-size:14px; cursor:pointer;">+ Emitir comprobante</button>
    </div>

    <!-- Filtros -->
    <div style="display:flex; gap:12px; margin-bottom:16px; align-items:center;">
      <input v-model="desde" type="date" @change="filtrar" style="padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;" />
      <span style="color:#64748B;">hasta</span>
      <input v-model="hasta" type="date" @change="filtrar" style="padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;" />
      <div style="margin-left:auto; background:#F0FDF4; padding:10px 20px; border-radius:8px;">
        <span style="font-size:13px; color:#64748B;">Total: </span>
        <span style="font-size:18px; font-weight:800; color:#10B981;">S/ {{ Number(totalVentas).toFixed(2) }}</span>
      </div>
    </div>

    <!-- Tabla -->
    <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; overflow:hidden;">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="background:#F8FAFC;">
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Comprobante</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Paciente</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Fecha</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Total</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="comprobantes.length===0"><td colspan="5" style="padding:32px; text-align:center; color:#94A3B8;">Sin comprobantes</td></tr>
          <tr v-for="c in comprobantes" :key="c.id" style="border-top:1px solid #F1F5F9;">
            <td style="padding:12px 16px;">
              <span :style="c.tipo_comprobante==='01' ? {background:'#EFF6FF',color:'#1D4ED8'} : {background:'#F0FDF4',color:'#15803D'}" style="padding:3px 8px; border-radius:6px; font-size:11px; font-weight:600; margin-right:6px;">{{ c.tipo_comprobante==='01' ? 'Factura' : 'Boleta' }}</span>
              <span style="font-size:13px; font-weight:600;">{{ c.serie }}-{{ String(c.numero).padStart(8,'0') }}</span>
            </td>
            <td style="padding:12px 16px; font-size:13px;">{{ c.paciente_nombre }}</td>
            <td style="padding:12px 16px; font-size:13px;">{{ c.fecha_emision }}</td>
            <td style="padding:12px 16px; font-size:14px; font-weight:700; color:#10B981;">S/ {{ Number(c.total).toFixed(2) }}</td>
            <td style="padding:12px 16px;">
              <span :style="estadoStyle(c.estado)">{{ c.estado }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal emitir -->
    <div v-if="modalNuevo" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000; padding:20px;">
      <div style="background:white; border-radius:16px; padding:28px; width:520px; max-width:100%;">
        <h2 style="margin:0 0 20px; font-size:18px;">Emitir comprobante</h2>
        <div style="display:flex; flex-direction:column; gap:14px;">
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">TIPO</label>
            <div style="display:flex; gap:10px;">
              <button v-for="t in [{v:'03',l:'Boleta'},{v:'01',l:'Factura'}]" :key="t.v" @click="form.tipo_comprobante=t.v"
                :style="form.tipo_comprobante===t.v ? {background:'#8B5CF6',color:'white'} : {background:'white',color:'#374151'}"
                style="flex:1; padding:9px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; cursor:pointer; font-weight:600;">
                {{ t.l }}
              </button>
            </div>
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">PACIENTE *</label>
            <input v-model="buscarPaciente" @input="searchPaciente" type="text" placeholder="Buscar paciente..." style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            <div v-if="resultadosPaciente.length" style="border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; background:white;">
              <div v-for="p in resultadosPaciente" :key="p.id" @click="seleccionarPaciente(p)" style="padding:10px 12px; cursor:pointer; font-size:13px; border-bottom:1px solid #F1F5F9;">
                {{ p.apellidos }}, {{ p.nombres }} - {{ p.dni }}
              </div>
            </div>
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">{{ form.tipo_comprobante==='01' ? 'RUC *' : 'DNI' }}</label>
            <input v-model="form.cliente_documento" type="text" :maxlength="form.tipo_comprobante==='01' ? 11 : 8" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">DESCRIPCIÓN *</label>
            <input v-model="form.descripcion" type="text" placeholder="Ej: Servicio odontológico" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">MONTO TOTAL *</label>
            <input v-model="form.total" type="number" step="0.01" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
          </div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
          <button @click="modalNuevo=false" style="padding:10px 20px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; cursor:pointer; background:white;">Cancelar</button>
          <button @click="emitir" :disabled="loading" style="padding:10px 24px; background:#8B5CF6; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">{{ loading ? 'Emitiendo...' : 'Emitir' }}</button>
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

const props = defineProps({ comprobantes: Array, totalVentas: Number, desde: String, hasta: String })
const modalNuevo = ref(false)
const loading = ref(false)
const buscarPaciente = ref('')
const resultadosPaciente = ref([])
const desde = ref(props.desde)
const hasta = ref(props.hasta)
const form = ref({ paciente_id:'', cliente_nombre:'', cliente_documento:'', tipo_comprobante:'03', descripcion:'Servicio odontológico', total:'' })

const filtrar = () => router.get('/odontologia/facturacion', { desde: desde.value, hasta: hasta.value }, { preserveState: true })

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
  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
    const res = await fetch('/odontologia/facturacion/emitir', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify(form.value)
    })
    const data = await res.json()
    if (data.success) {
      alert('✅ ' + data.mensaje)
      modalNuevo.value = false
      router.reload()
    } else {
      alert('❌ ' + data.mensaje)
    }
  } finally {
    loading.value = false
  }
}
</script>
