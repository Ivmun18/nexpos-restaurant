<template>
  <div style="padding:24px; max-width:1200px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h1 style="font-size:22px; font-weight:700; margin:0;">Pagos</h1>
      <button @click="modalNuevo=true" style="background:#8B5CF6; color:white; padding:10px 20px; border:none; border-radius:8px; font-weight:600; font-size:14px; cursor:pointer;">+ Nuevo pago</button>
    </div>

    <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; overflow:hidden;">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="background:#F8FAFC;">
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Paciente</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Fecha</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Tipo</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Total</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Estado</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Cuotas</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="pagos.data.length===0"><td colspan="6" style="padding:32px; text-align:center; color:#94A3B8;">Sin pagos</td></tr>
          <template v-for="p in pagos.data" :key="p.id">
            <tr style="border-top:1px solid #F1F5F9; cursor:pointer;" @click="p._expandido=!p._expandido">
              <td style="padding:12px 16px; font-size:13px; font-weight:600;">{{ p.paciente?.apellidos }}, {{ p.paciente?.nombres }}</td>
              <td style="padding:12px 16px; font-size:13px;">{{ p.fecha }}</td>
              <td style="padding:12px 16px;">
                <span :style="p.tipo_pago==='contado' ? {background:'#F0FDF4',color:'#15803D'} : {background:'#EFF6FF',color:'#1D4ED8'}" style="padding:3px 8px; border-radius:6px; font-size:11px; font-weight:600;">{{ p.tipo_pago }}</span>
              </td>
              <td style="padding:12px 16px; font-size:14px; font-weight:800; color:#8B5CF6;">S/ {{ Number(p.monto_total).toFixed(2) }}</td>
              <td style="padding:12px 16px;">
                <span :style="estadoStyle(p.estado)">{{ p.estado }}</span>
              </td>
              <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ p.cuotas?.filter(c=>c.estado==='pagado').length }}/{{ p.cuotas?.length }}</td>
            </tr>
            <tr v-if="p._expandido" style="background:#F8FAFC;">
              <td colspan="6" style="padding:12px 16px;">
                <div v-for="c in p.cuotas" :key="c.id" style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #F1F5F9;">
                  <span style="font-size:13px;">Cuota {{ c.numero_cuota }} · Vence: {{ c.fecha_vencimiento }}</span>
                  <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-size:13px; font-weight:600;">S/ {{ Number(c.monto).toFixed(2) }}</span>
                    <span :style="c.estado==='pagado' ? {color:'#10B981'} : c.estado==='vencido' ? {color:'#EF4444'} : {color:'#F59E0B'}" style="font-size:12px; font-weight:600;">{{ c.estado }}</span>
                    <div v-if="c.estado!=='pagado'" style="display:flex; gap:6px; align-items:center;">
                      <select v-model="metodoPagoCuota[c.id]" style="padding:5px 8px; border:1px solid #E2E8F0; border-radius:6px; font-size:12px;">
                        <option value="efectivo">Efectivo</option>
                        <option value="yape">Yape</option>
                        <option value="plin">Plin</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                      </select>
                      <button @click="pagarCuota(c.id)" style="padding:5px 12px; background:#10B981; color:white; border:none; border-radius:6px; font-size:12px; cursor:pointer;">Pagar</button>
                    </div>
                    <span v-else style="font-size:12px; color:#64748B;">{{ c.metodo_pago }}</span>
                  </div>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- Modal nuevo pago -->
    <div v-if="modalNuevo" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000; padding:20px;">
      <div style="background:white; border-radius:16px; padding:28px; width:500px; max-width:100%;">
        <h2 style="margin:0 0 20px; font-size:18px;">Nuevo pago</h2>
        <div style="display:flex; flex-direction:column; gap:14px;">
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">PACIENTE *</label>
            <input v-model="buscarPaciente" @input="searchPaciente" type="text" placeholder="Buscar paciente..." style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            <div v-if="resultadosPaciente.length" style="border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;">
              <div v-for="p in resultadosPaciente" :key="p.id" @click="seleccionarPaciente(p)" style="padding:10px 12px; cursor:pointer; font-size:13px;">{{ p.apellidos }}, {{ p.nombres }}</div>
            </div>
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">MONTO TOTAL *</label>
            <input v-model="form.monto_total" type="number" step="0.01" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">TIPO DE PAGO</label>
            <div style="display:flex; gap:10px;">
              <button v-for="t in ['contado','cuotas']" :key="t" @click="form.tipo_pago=t"
                :style="form.tipo_pago===t ? {background:'#8B5CF6',color:'white'} : {background:'white',color:'#374151'}"
                style="flex:1; padding:9px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; cursor:pointer; font-weight:600;">
                {{ t }}
              </button>
            </div>
          </div>
          <div v-if="form.tipo_pago==='cuotas'">
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">N° DE CUOTAS</label>
            <input v-model="form.num_cuotas" type="number" min="2" max="24" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            <p style="font-size:12px; color:#64748B; margin:4px 0 0;">S/ {{ (Number(form.monto_total)/Number(form.num_cuotas)||0).toFixed(2) }} por cuota</p>
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">OBSERVACIONES</label>
            <textarea v-model="form.observaciones" rows="2" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;"></textarea>
          </div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
          <button @click="modalNuevo=false" style="padding:10px 20px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; cursor:pointer; background:white;">Cancelar</button>
          <button @click="guardar" style="padding:10px 24px; background:#8B5CF6; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">Guardar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ pagos: Object })
const modalNuevo = ref(false)
const buscarPaciente = ref('')
const resultadosPaciente = ref([])
const metodoPagoCuota = ref({})
const form = ref({ paciente_id:'', monto_total:'', tipo_pago:'contado', num_cuotas:2, observaciones:'' })

const estadoStyle = (e) => {
  const m = { pendiente:{background:'#FFFBEB',color:'#B45309'}, parcial:{background:'#EFF6FF',color:'#1D4ED8'}, pagado:{background:'#F0FDF4',color:'#15803D'}, anulado:{background:'#FEF2F2',color:'#B91C1C'} }
  return { ...(m[e]||{}), padding:'3px 8px', borderRadius:'6px', fontSize:'11px', fontWeight:'600' }
}

const searchPaciente = async () => {
  if (buscarPaciente.value.length < 2) { resultadosPaciente.value = []; return }
  const r = await fetch('/odontologia/pacientes/buscar?q=' + buscarPaciente.value)
  resultadosPaciente.value = await r.json()
}

const seleccionarPaciente = (p) => {
  form.value.paciente_id = p.id
  buscarPaciente.value = p.apellidos + ', ' + p.nombres
  resultadosPaciente.value = []
}

const pagarCuota = (id) => {
  router.post(`/odontologia/pagos/cuota/${id}`, { metodo_pago: metodoPagoCuota.value[id] || 'efectivo' }, { preserveState: true })
}

const guardar = () => {
  router.post('/odontologia/pagos', form.value, {
    onSuccess: () => { modalNuevo.value = false; form.value = { paciente_id:'', monto_total:'', tipo_pago:'contado', num_cuotas:2, observaciones:'' } }
  })
}
</script>
