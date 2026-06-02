<template>
  <AppLayout title="Laboratorio" subtitle="">
  <div style="padding:24px; max-width:1200px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h1 style="font-size:22px; font-weight:700; margin:0;">Laboratorio</h1>
      <button @click="modalNuevo=true" style="background:#8B5CF6; color:white; padding:10px 20px; border:none; border-radius:8px; font-weight:600; font-size:14px; cursor:pointer;">+ Nuevo pedido</button>
    </div>

    <!-- Tabla pedidos -->
    <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; overflow:hidden;">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="background:#F8FAFC;">
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Paciente</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Trabajo</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Proveedor</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Pedido</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Entrega esp.</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Estado</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Costo</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="pedidos.data.length===0"><td colspan="7" style="padding:32px; text-align:center; color:#94A3B8;">Sin pedidos</td></tr>
          <tr v-for="p in pedidos.data" :key="p.id" style="border-top:1px solid #F1F5F9;">
            <td style="padding:12px 16px; font-size:13px; font-weight:600;">{{ p.paciente?.apellidos }}, {{ p.paciente?.nombres }}</td>
            <td style="padding:12px 16px; font-size:13px;">{{ p.tipo_trabajo }}</td>
            <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ p.proveedor?.nombre }}</td>
            <td style="padding:12px 16px; font-size:13px;">{{ p.fecha_pedido }}</td>
            <td style="padding:12px 16px; font-size:13px;">{{ p.fecha_entrega_esperada || '-' }}</td>
            <td style="padding:12px 16px;">
              <select @change="cambiarEstado(p.id, $event.target.value)" :value="p.estado" style="padding:5px 8px; border:1px solid #E2E8F0; border-radius:6px; font-size:12px;">
                <option value="pendiente">Pendiente</option>
                <option value="en_proceso">En proceso</option>
                <option value="listo">Listo</option>
                <option value="entregado">Entregado</option>
                <option value="cancelado">Cancelado</option>
              </select>
            </td>
            <td style="padding:12px 16px; font-size:13px; font-weight:600;">S/ {{ Number(p.costo).toFixed(2) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal nuevo pedido -->
    <div v-if="modalNuevo" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000; padding:20px;">
      <div style="background:white; border-radius:16px; padding:28px; width:560px; max-width:100%;">
        <h2 style="margin:0 0 20px; font-size:18px;">Nuevo pedido de laboratorio</h2>
        <div style="display:flex; flex-direction:column; gap:14px;">
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">PACIENTE *</label>
            <input v-model="buscarPaciente" @input="searchPaciente" type="text" placeholder="Buscar paciente..." style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            <div v-if="resultadosPaciente.length" style="border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;">
              <div v-for="p in resultadosPaciente" :key="p.id" @click="seleccionarPaciente(p)" style="padding:10px 12px; cursor:pointer; font-size:13px;">{{ p.apellidos }}, {{ p.nombres }}</div>
            </div>
          </div>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div>
              <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">PROVEEDOR *</label>
              <select v-model="form.proveedor_id" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;">
                <option value="">Seleccionar...</option>
                <option v-for="prov in proveedores" :key="prov.id" :value="prov.id">{{ prov.nombre }}</option>
              </select>
            </div>
            <div>
              <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">DOCTOR *</label>
              <select v-model="form.doctor_id" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;">
                <option value="">Seleccionar...</option>
              </select>
            </div>
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">TIPO DE TRABAJO *</label>
            <input v-model="form.tipo_trabajo" type="text" placeholder="Ej: Corona metal-porcelana, Prótesis parcial..." style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
          </div>
          <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
            <div>
              <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">COLOR PRÓTESIS</label>
              <input v-model="form.color_protesis" type="text" placeholder="Ej: A2, B1..." style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            </div>
            <div>
              <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">FECHA ENTREGA</label>
              <input v-model="form.fecha_entrega_esperada" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            </div>
            <div>
              <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">COSTO</label>
              <input v-model="form.costo" type="number" step="0.01" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            </div>
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">DESCRIPCIÓN</label>
            <textarea v-model="form.descripcion" rows="2" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;"></textarea>
          </div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
          <button @click="modalNuevo=false" style="padding:10px 20px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; cursor:pointer; background:white;">Cancelar</button>
          <button @click="guardar" style="padding:10px 24px; background:#8B5CF6; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">Guardar</button>
        </div>
      </div>
    </div>
  </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ pedidos: Object, proveedores: Array })
const modalNuevo = ref(false)
const buscarPaciente = ref('')
const resultadosPaciente = ref([])
const form = ref({ paciente_id:'', proveedor_id:'', doctor_id:'', tipo_trabajo:'', color_protesis:'', fecha_entrega_esperada:'', costo:0, descripcion:'' })

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

const cambiarEstado = (id, estado) => router.put(`/odontologia/laboratorio/pedidos/${id}`, { estado }, { preserveState: true })

const guardar = () => {
  router.post('/odontologia/laboratorio/pedidos', form.value, {
    onSuccess: () => { modalNuevo.value = false }
  })
}
</script>
