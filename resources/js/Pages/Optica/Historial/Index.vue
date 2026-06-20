<template>
  <AppLayout title="Historial Clínico">
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">🏥 Historial Clínico</h1>
        <button @click="showModal=true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">+ Nuevo Registro</button>
      </div>
      <div class="mb-4">
        <input v-model="busqueda" @keyup.enter="buscar" type="text" placeholder="Buscar por paciente..." class="w-full md:w-96 border rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"/>
      </div>
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-blue-50 text-blue-700 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">Fecha</th>
              <th class="px-4 py-3 text-left">Paciente</th>
              <th class="px-4 py-3 text-left">Doctor</th>
              <th class="px-4 py-3 text-left">Motivo</th>
              <th class="px-4 py-3 text-left">Diagnóstico</th>
              <th class="px-4 py-3 text-center">Próx. Cita</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="historiales.data.length===0"><td colspan="7" class="text-center text-gray-400 py-8">Sin registros</td></tr>
            <tr v-for="h in historiales.data" :key="h.id" class="border-t hover:bg-gray-50">
              <td class="px-4 py-3 text-gray-600">{{ h.fecha }}</td>
              <td class="px-4 py-3 font-semibold text-gray-800">{{ h.paciente ? h.paciente.nombre+' '+h.paciente.apellidos : '—' }}</td>
              <td class="px-4 py-3 text-gray-600">{{ h.doctor ? h.doctor.nombre : '—' }}</td>
              <td class="px-4 py-3 text-gray-600 text-xs max-w-[200px] truncate">{{ h.motivo_consulta || '—' }}</td>
              <td class="px-4 py-3 text-gray-600 text-xs max-w-[200px] truncate">{{ h.diagnostico || '—' }}</td>
              <td class="px-4 py-3 text-center text-xs">{{ h.proxima_cita || '—' }}</td>
              <td class="px-4 py-3 text-center">
                <button @click="editar(h)" class="text-yellow-600 hover:underline text-xs mr-2">Editar</button>
                <button @click="eliminar(h.id)" class="text-red-500 hover:underline text-xs">Eliminar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="flex gap-2 mt-4 justify-end">
        <a v-for="link in historiales.links" :key="link.label" :href="link.url||'#'" v-html="link.label" class="px-3 py-1 rounded text-sm border" :class="link.active?'bg-blue-600 text-white border-blue-600':'bg-white text-gray-600'"/>
      </div>
    </div>
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 max-h-screen overflow-y-auto">
        <h2 class="text-lg font-bold text-gray-800 mb-4">{{ editando ? 'Editar' : 'Nuevo' }} Registro Clínico</h2>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-xs text-gray-500">Paciente *</label>
            <select v-model="form.paciente_id" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
              <option value="">— Seleccionar —</option>
              <option v-for="p in pacientes" :key="p.id" :value="p.id">{{ p.apellidos }}, {{ p.nombre }}</option>
            </select>
          </div>
          <div>
            <label class="text-xs text-gray-500">Doctor</label>
            <select v-model="form.doctor_id" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
              <option value="">— Sin asignar —</option>
              <option v-for="d in doctores" :key="d.id" :value="d.id">{{ d.nombre }}</option>
            </select>
          </div>
          <div>
            <label class="text-xs text-gray-500">Fecha *</label>
            <input v-model="form.fecha" type="date" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Próxima cita</label>
            <input v-model="form.proxima_cita" type="date" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div class="col-span-2">
            <label class="text-xs text-gray-500">Motivo de consulta</label>
            <textarea v-model="form.motivo_consulta" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"></textarea>
          </div>
          <div class="col-span-2">
            <label class="text-xs text-gray-500">Antecedentes</label>
            <textarea v-model="form.antecedentes" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm mt-1" placeholder="Enfermedades, alergias, medicamentos, cirugías previas..."></textarea>
          </div>
          <div class="col-span-2">
            <label class="text-xs text-gray-500">Diagnóstico</label>
            <textarea v-model="form.diagnostico" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"></textarea>
          </div>
          <div class="col-span-2">
            <label class="text-xs text-gray-500">Tratamiento</label>
            <textarea v-model="form.tratamiento" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"></textarea>
          </div>
          <div class="col-span-2">
            <label class="text-xs text-gray-500">Observaciones</label>
            <textarea v-model="form.observaciones" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"></textarea>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
          <button @click="cerrarModal" class="px-4 py-2 border rounded-lg text-sm text-gray-600">Cancelar</button>
          <button @click="guardar" :disabled="guardando" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold disabled:opacity-50">{{ guardando ? 'Guardando...' : 'Guardar' }}</button>
        </div>
      </div>
    </div>
  </div>
  </AppLayout>
</template>
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
const props = defineProps({ historiales: Object, pacientes: Array, doctores: Array, q: String })
const showModal = ref(false)
const editando = ref(null)
const guardando = ref(false)
const busqueda = ref(props.q || '')
const form = reactive({ paciente_id:'', doctor_id:'', fecha: new Date().toISOString().slice(0,10), motivo_consulta:'', antecedentes:'', diagnostico:'', tratamiento:'', observaciones:'', proxima_cita:'' })
const buscar = () => router.get('/optica/historial', { q: busqueda.value }, { preserveState: true })
const editar = (h) => {
  editando.value = h.id
  Object.assign(form, { paciente_id: h.paciente_id, doctor_id: h.doctor_id||'', fecha: h.fecha, motivo_consulta: h.motivo_consulta||'', antecedentes: h.antecedentes||'', diagnostico: h.diagnostico||'', tratamiento: h.tratamiento||'', observaciones: h.observaciones||'', proxima_cita: h.proxima_cita||'' })
  showModal.value = true
}
const cerrarModal = () => { showModal.value = false; editando.value = null; Object.keys(form).forEach(k => form[k] = k==='fecha' ? new Date().toISOString().slice(0,10) : '') }
const guardar = () => {
  guardando.value = true
  if (editando.value) { router.put(`/optica/historial/${editando.value}`, form, { onFinish: () => { guardando.value = false; cerrarModal() } }) }
  else { router.post('/optica/historial', form, { onFinish: () => { guardando.value = false; cerrarModal() } }) }
}
const eliminar = (id) => { if (!confirm('Eliminar?')) return; router.delete(`/optica/historial/${id}`) }
</script>
