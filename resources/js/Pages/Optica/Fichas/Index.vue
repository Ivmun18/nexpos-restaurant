<template>
  <AppLayout title="Fichas Oftalmológicas">
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6">

      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-purple-800">👁️ Fichas Oftalmológicas</h1>
        <button @click="showModal=true" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
          + Nueva Ficha
        </button>
      </div>

      <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-purple-50 text-purple-700 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">Paciente</th>
              <th class="px-4 py-3 text-left">Fecha</th>
              <th class="px-4 py-3 text-center">OD Esfera</th>
              <th class="px-4 py-3 text-center">OI Esfera</th>
              <th class="px-4 py-3 text-center">DIV</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="fichas.data.length===0">
              <td colspan="6" class="text-center text-gray-400 py-8">Sin fichas registradas</td>
            </tr>
            <tr v-for="f in fichas.data" :key="f.id" class="border-t hover:bg-gray-50">
              <td class="px-4 py-3 font-semibold text-gray-800">
                {{ f.paciente ? f.paciente.nombre+' '+f.paciente.apellidos : '—' }}
              </td>
              <td class="px-4 py-3 text-gray-600">{{ f.fecha }}</td>
              <td class="px-4 py-3 text-center font-mono text-blue-700">{{ signo(f.od_esfera) }}</td>
              <td class="px-4 py-3 text-center font-mono text-blue-700">{{ signo(f.oi_esfera) }}</td>
              <td class="px-4 py-3 text-center text-gray-600">{{ f.div || '—' }}</td>
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center gap-2">
                  <a :href="`/optica/fichas/${f.id}/pdf`" target="_blank"
                    class="text-purple-600 hover:underline text-xs">PDF</a>
                  <button @click="editar(f)" class="text-yellow-600 hover:underline text-xs">Editar</button>
                  <button @click="eliminar(f.id)" class="text-red-500 hover:underline text-xs">Eliminar</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex gap-2 mt-4 justify-end">
        <Link v-for="link in fichas.links" :key="link.label" :href="link.url||'#'"
          v-html="link.label" class="px-3 py-1 rounded text-sm border"
          :class="link.active?'bg-purple-600 text-white border-purple-600':'bg-white text-gray-600'"/>
      </div>
    </div>

    <!-- Modal Ficha -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 max-h-screen overflow-y-auto">
        <h2 class="text-lg font-bold text-gray-800 mb-4">{{ editando ? 'Editar' : 'Nueva' }} Ficha Oftalmológica</h2>

        <div class="mb-4">
          <label class="text-xs text-gray-500">Paciente *</label>
          <select v-model="form.paciente_id" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
            <option value="">— Seleccionar paciente —</option>
            <option v-for="p in pacientes" :key="p.id" :value="p.id">
              {{ p.apellidos }}, {{ p.nombre }} {{ p.dni ? '('+p.dni+')' : '' }}
            </option>
          </select>
        </div>

        <div class="mb-4">
          <label class="text-xs text-gray-500">Fecha *</label>
          <input v-model="form.fecha" type="date" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
        </div>

        <!-- Tabla graduación -->
        <div class="border rounded-xl overflow-hidden mb-4">
          <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-600 text-xs">
              <tr>
                <th class="px-3 py-2 text-left">Ojo</th>
                <th class="px-3 py-2 text-center">Esfera</th>
                <th class="px-3 py-2 text-center">Cilindro</th>
                <th class="px-3 py-2 text-center">Eje</th>
                <th class="px-3 py-2 text-center">Adición</th>
                <th class="px-3 py-2 text-center">AV</th>
              </tr>
            </thead>
            <tbody>
              <tr class="border-t">
                <td class="px-3 py-2 font-semibold text-blue-700">OD</td>
                <td class="px-2 py-1"><input v-model="form.od_esfera" type="number" step="0.25" class="w-full border rounded px-2 py-1 text-center text-sm"/></td>
                <td class="px-2 py-1"><input v-model="form.od_cilindro" type="number" step="0.25" class="w-full border rounded px-2 py-1 text-center text-sm"/></td>
                <td class="px-2 py-1"><input v-model="form.od_eje" type="number" min="0" max="180" class="w-full border rounded px-2 py-1 text-center text-sm"/></td>
                <td class="px-2 py-1"><input v-model="form.od_adicion" type="number" step="0.25" class="w-full border rounded px-2 py-1 text-center text-sm"/></td>
                <td class="px-2 py-1"><input v-model="form.od_av" type="text" placeholder="20/20" class="w-full border rounded px-2 py-1 text-center text-sm"/></td>
              </tr>
              <tr class="border-t">
                <td class="px-3 py-2 font-semibold text-green-700">OI</td>
                <td class="px-2 py-1"><input v-model="form.oi_esfera" type="number" step="0.25" class="w-full border rounded px-2 py-1 text-center text-sm"/></td>
                <td class="px-2 py-1"><input v-model="form.oi_cilindro" type="number" step="0.25" class="w-full border rounded px-2 py-1 text-center text-sm"/></td>
                <td class="px-2 py-1"><input v-model="form.oi_eje" type="number" min="0" max="180" class="w-full border rounded px-2 py-1 text-center text-sm"/></td>
                <td class="px-2 py-1"><input v-model="form.oi_adicion" type="number" step="0.25" class="w-full border rounded px-2 py-1 text-center text-sm"/></td>
                <td class="px-2 py-1"><input v-model="form.oi_av" type="text" placeholder="20/20" class="w-full border rounded px-2 py-1 text-center text-sm"/></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-4">
          <div>
            <label class="text-xs text-gray-500">DIV (distancia interpupilar mm)</label>
            <input v-model="form.div" type="number" step="0.5" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Observaciones</label>
            <input v-model="form.observaciones" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
        </div>

        <div class="flex justify-end gap-2">
          <button @click="cerrarModal" class="px-4 py-2 border rounded-lg text-sm text-gray-600">Cancelar</button>
          <button @click="guardar" :disabled="guardando"
            class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-semibold disabled:opacity-50">
            {{ guardando ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>
  </div>

</AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({ fichas: Object, pacientes: Array })

const showModal = ref(false)
const editando = ref(null)
const guardando = ref(false)

const form = reactive({
  paciente_id: '', fecha: new Date().toISOString().slice(0,10),
  od_esfera: '', od_cilindro: '', od_eje: '', od_adicion: '', od_av: '',
  oi_esfera: '', oi_cilindro: '', oi_eje: '', oi_adicion: '', oi_av: '',
  div: '', observaciones: ''
})

const signo = (v) => {
  if (v === null || v === undefined || v === '') return '—'
  return parseFloat(v) >= 0 ? '+'+parseFloat(v).toFixed(2) : parseFloat(v).toFixed(2)
}

const editar = (f) => {
  editando.value = f.id
  Object.assign(form, {
    paciente_id: f.paciente_id, fecha: f.fecha,
    od_esfera: f.od_esfera||''  , od_cilindro: f.od_cilindro||'', od_eje: f.od_eje||'', od_adicion: f.od_adicion||'', od_av: f.od_av||'',
    oi_esfera: f.oi_esfera||'', oi_cilindro: f.oi_cilindro||'', oi_eje: f.oi_eje||'', oi_adicion: f.oi_adicion||'', oi_av: f.oi_av||'',
    div: f.div||'', observaciones: f.observaciones||''
  })
  showModal.value = true
}

const cerrarModal = () => { showModal.value = false; editando.value = null }

const guardar = () => {
  guardando.value = true
  if (editando.value) {
    router.put(`/optica/fichas/${editando.value}`, form, { onFinish: () => { guardando.value = false; cerrarModal() } })
  } else {
    router.post('/optica/fichas', form, { onFinish: () => { guardando.value = false; cerrarModal() } })
  }
}

const eliminar = (id) => {
  if (!confirm('¿Eliminar esta ficha?')) return
  router.delete(`/optica/fichas/${id}`)
}
</script>
