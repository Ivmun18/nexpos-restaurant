<template>
  <AppLayout title="Doctores">
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">👨‍⚕️ Doctores / Optometristas</h1>
        <button @click="showModal=true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">+ Nuevo Doctor</button>
      </div>
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">Nombre</th>
              <th class="px-4 py-3 text-left">Especialidad</th>
              <th class="px-4 py-3 text-left">Colegiatura</th>
              <th class="px-4 py-3 text-left">Teléfono</th>
              <th class="px-4 py-3 text-center">Fichas</th>
              <th class="px-4 py-3 text-center">Estado</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="doctores.length===0"><td colspan="7" class="text-center text-gray-400 py-8">Sin doctores registrados</td></tr>
            <tr v-for="d in doctores" :key="d.id" class="border-t hover:bg-gray-50">
              <td class="px-4 py-3 font-semibold text-gray-800">{{ d.nombre }}</td>
              <td class="px-4 py-3 text-gray-600">{{ d.especialidad || '—' }}</td>
              <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ d.colegiatura || '—' }}</td>
              <td class="px-4 py-3 text-gray-600">{{ d.telefono || '—' }}</td>
              <td class="px-4 py-3 text-center"><span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs">{{ d.fichas_count }}</span></td>
              <td class="px-4 py-3 text-center">
                <span v-if="d.activo" class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">Activo</span>
                <span v-else class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full text-xs">Inactivo</span>
              </td>
              <td class="px-4 py-3 text-center">
                <button @click="editar(d)" class="text-yellow-600 hover:underline text-xs mr-2">Editar</button>
                <button @click="desactivar(d.id)" class="text-red-500 hover:underline text-xs">Desactivar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">{{ editando ? 'Editar' : 'Nuevo' }} Doctor</h2>
        <div class="space-y-3">
          <div><label class="text-xs text-gray-500">Nombre *</label><input v-model="form.nombre" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/></div>
          <div><label class="text-xs text-gray-500">Especialidad</label><input v-model="form.especialidad" placeholder="Optometría, Oftalmología..." class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/></div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="text-xs text-gray-500">N° Colegiatura</label><input v-model="form.colegiatura" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/></div>
            <div><label class="text-xs text-gray-500">Teléfono</label><input v-model="form.telefono" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/></div>
          </div>
          <div><label class="text-xs text-gray-500">Email</label><input v-model="form.email" type="email" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/></div>
          <div v-if="editando" class="flex items-center gap-2">
            <input v-model="form.activo" type="checkbox" id="doc_activo" class="w-4 h-4"/>
            <label for="doc_activo" class="text-sm text-gray-600">Activo</label>
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
const fmtFecha = (f) => { if (!f) return "—"; const d = new Date(f); return d.toLocaleDateString("es-PE",{year:"numeric",month:"short",day:"numeric"}) }
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
const props = defineProps({ doctores: Array })
const showModal = ref(false)
const editando = ref(null)
const guardando = ref(false)
const form = reactive({ nombre: '', especialidad: '', colegiatura: '', telefono: '', email: '', activo: true })
const editar = (d) => { editando.value = d.id; Object.assign(form, { nombre: d.nombre, especialidad: d.especialidad||'', colegiatura: d.colegiatura||'', telefono: d.telefono||'', email: d.email||'', activo: d.activo }); showModal.value = true }
const cerrarModal = () => { showModal.value = false; editando.value = null; Object.keys(form).forEach(k => form[k] = k==='activo' ? true : '') }
const guardar = () => {
  guardando.value = true
  if (editando.value) { router.put(`/optica/doctores/${editando.value}`, form, { onFinish: () => { guardando.value = false; cerrarModal() } }) }
  else { router.post('/optica/doctores', form, { onFinish: () => { guardando.value = false; cerrarModal() } }) }
}
const desactivar = (id) => { if (!confirm('Desactivar?')) return; router.delete(`/optica/doctores/${id}`) }
</script>
