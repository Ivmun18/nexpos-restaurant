<template>
  <AppLayout title="Presupuestos" subtitle="">
  <div style="padding:24px; max-width:1200px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h1 style="font-size:22px; font-weight:700; margin:0;">Presupuestos</h1>
      <button @click="modalNuevo=true" style="background:#8B5CF6; color:white; padding:10px 20px; border:none; border-radius:8px; font-weight:600; font-size:14px; cursor:pointer;">+ Nuevo presupuesto</button>
    </div>

    <!-- Lista -->
    <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; overflow:hidden;">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="background:#F8FAFC;">
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">#</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Paciente</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Doctor</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Fecha</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Total</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Estado</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="presupuestos.data.length===0"><td colspan="7" style="padding:32px; text-align:center; color:#94A3B8;">Sin presupuestos</td></tr>
          <tr v-for="p in presupuestos.data" :key="p.id" style="border-top:1px solid #F1F5F9;">
            <td style="padding:12px 16px; font-size:13px; color:#64748B;">#{{ p.id }}</td>
            <td style="padding:12px 16px; font-size:13px; font-weight:600;">{{ p.paciente?.apellidos }}, {{ p.paciente?.nombres }}</td>
            <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ p.doctor?.nombre }}</td>
            <td style="padding:12px 16px; font-size:13px;">{{ p.fecha }}</td>
            <td style="padding:12px 16px; font-size:14px; font-weight:700; color:#10B981;">S/ {{ Number(p.total).toFixed(2) }}</td>
            <td style="padding:12px 16px;">
              <select @change="cambiarEstado(p.id, $event.target.value)" :value="p.estado" style="padding:5px 8px; border:1px solid #E2E8F0; border-radius:6px; font-size:12px;">
                <option value="borrador">Borrador</option>
                <option value="enviado">Enviado</option>
                <option value="aprobado">Aprobado</option>
                <option value="rechazado">Rechazado</option>
                <option value="completado">Completado</option>
              </select>
            </td>
            <td style="padding:12px 16px;">
              <button @click="editarPresupuesto(p)" style="color:#F59E0B; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; margin-right:8px;">Editar</button>
              <button @click="verDetalle(p)" style="color:#8B5CF6; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer;">Ver</button>
              <button @click="imprimirPDF(p.id)" style="color:#0F766E; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; margin-left:8px;">🖨️ PDF</button>
              <button @click="enviarWhatsApp(p)" style="color:#16A34A; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer; margin-left:8px;">📲 WA</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal nuevo presupuesto -->
    <div v-if="modalNuevo" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000; padding:20px; overflow-y:auto;">
      <div style="background:white; border-radius:16px; padding:28px; width:700px; max-width:100%;">
        <h2 style="margin:0 0 20px; font-size:18px;">{{ editandoId ? 'Editar presupuesto #' + editandoId : 'Nuevo presupuesto' }}</h2>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:20px;">
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">PACIENTE *</label>
            <input v-model="buscarPaciente" @input="searchPaciente" type="text" placeholder="Buscar paciente..." style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            <div v-if="resultadosPaciente.length" style="border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; background:white; position:relative; z-index:10;">
              <div v-for="p in resultadosPaciente" :key="p.id" @click="seleccionarPaciente(p)" style="padding:10px 12px; cursor:pointer; font-size:13px; border-bottom:1px solid #F1F5F9;">
                {{ p.apellidos }}, {{ p.nombres }} - {{ p.dni }}
              </div>
            </div>
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">DOCTOR *</label>
            <select v-model="form.doctor_id" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;"><option value="">Seleccionar doctor...</option><option v-for="d in doctores" :key="d.id" :value="Number(d.id)">{{ d.nombre }}</option>
              <option value="">Seleccionar...</option>
            </select>
          </div>
        </div>

        <!-- Items -->
        <div style="margin-bottom:16px;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <p style="margin:0; font-size:13px; font-weight:700;">Tratamientos</p>
            <button @click="agregarItem" style="padding:5px 12px; background:#EDE9FE; color:#7C3AED; border:none; border-radius:6px; font-size:12px; cursor:pointer;">+ Agregar</button>
          </div>
          <div v-for="(item,i) in form.items" :key="i" style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr auto; gap:8px; margin-bottom:8px; align-items:center;">
            <input v-model="item.descripcion" type="text" placeholder="Descripción" style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px;" />
            <input v-model="item.numero_pieza" type="number" placeholder="Pieza" style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px;" />
            <input v-model="item.precio" type="number" step="0.01" placeholder="Precio" style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px;" />
            <input v-model="item.cantidad" type="number" min="1" placeholder="Cant." style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px;" />
            <button @click="form.items.splice(i,1)" style="padding:7px; background:#FEF2F2; color:#B91C1C; border:none; border-radius:6px; cursor:pointer;">✕</button>
          </div>
          <div style="text-align:right; margin-top:8px; font-size:16px; font-weight:800; color:#10B981;">
            Total: S/ {{ totalPresupuesto.toFixed(2) }}
          </div>
        </div>

        <div>
          <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">OBSERVACIONES</label>
          <textarea v-model="form.observaciones" rows="2" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;"></textarea>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
          <button @click="cerrarModal" style="padding:10px 20px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; cursor:pointer; background:white;">Cancelar</button>
          <button @click="guardar" style="padding:10px 24px; background:#8B5CF6; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">Guardar</button>
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

const props = defineProps({ presupuestos: Object, doctores: Array, pacientes: Array })
const modalNuevo = ref(false)
const editandoId = ref(null)
const buscarPaciente = ref('')
const resultadosPaciente = ref([])
const form = ref({ paciente_id:'', doctor_id:'', items:[], observaciones:'' })

const totalPresupuesto = computed(() => form.value.items.reduce((s,i) => s + (Number(i.precio)||0) * (Number(i.cantidad)||1), 0))

const agregarItem = () => form.value.items.push({ descripcion:'', numero_pieza:'', precio:0, cantidad:1 })

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

const cambiarEstado = (id, estado) => router.put(`/odontologia/presupuestos/${id}`, { estado }, { preserveState: true })

const cerrarModal = () => {
  modalNuevo.value = false
  editandoId.value = null
  form.value = { paciente_id:'', doctor_id:'', items:[], observaciones:'' }
  buscarPaciente.value = ''
  resultadosPaciente.value = []
}

const editarPresupuesto = (p) => {
  editandoId.value = p.id
  form.value = {
    paciente_id: p.paciente_id,
    doctor_id: p.doctor_id,
    items: p.items.map(i => ({ descripcion: i.descripcion, numero_pieza: i.numero_pieza, precio: i.precio, cantidad: i.cantidad, tratamiento_id: i.tratamiento_id })),
    observaciones: p.observaciones
  }
  buscarPaciente.value = p.paciente.apellidos + ', ' + p.paciente.nombres
  modalNuevo.value = true
}

const guardar = () => {
  if (editandoId.value) {
    router.put(`/odontologia/presupuestos/${editandoId.value}`, form.value, {
      onSuccess: () => cerrarModal(),
      onError: (e) => { alert('Error: ' + JSON.stringify(e)) }
    })
  } else {
    router.post('/odontologia/presupuestos', form.value, {
      onSuccess: () => cerrarModal(),
      onError: (e) => { alert('Error: ' + JSON.stringify(e)) }
    })
  }
}

const verDetalle = (p) => router.get(`/odontologia/pacientes/${p.paciente_id}`)

const imprimirPDF = (id) => window.open(`/odontologia/presupuestos/${id}/pdf`, '_blank')

const enviarWhatsApp = (p) => {
  const paciente = p.paciente?.apellidos + ', ' + p.paciente?.nombres
  const doctor = p.doctor?.nombre || ''
  const items = p.items?.map(i => `  • ${i.descripcion} x${i.cantidad} = S/ ${(i.precio * i.cantidad).toFixed(2)}`).join('\n') || ''
  const msg = `🦷 *PRESUPUESTO DENTAL #${p.id}*\n\n*Paciente:* ${paciente}\n*Doctor:* ${doctor}\n*Fecha:* ${p.fecha}\n\n*Tratamientos:*\n${items}\n\n*TOTAL: S/ ${Number(p.total).toFixed(2)}*\n\n_Presupuesto válido por 30 días._`
  const tel = p.paciente?.telefono || ''
  window.open(`https://wa.me/51${tel}?text=${encodeURIComponent(msg)}`, '_blank')
}
</script>
