<template>
  <AppLayout title="Pacientes">
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6">

      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-blue-800">👤 Pacientes</h1>
        <button @click="showModal=true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
          + Nuevo Paciente
        </button>
      </div>

      <!-- Buscador -->
      <div class="mb-4">
        <input v-model="busqueda" @keyup.enter="buscar" type="text" placeholder="Buscar por nombre, apellido o DNI..."
          class="w-full md:w-96 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"/>
      </div>

      <!-- Tabla -->
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-blue-50 text-blue-700 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">Paciente</th>
              <th class="px-4 py-3 text-left">DNI</th>
              <th class="px-4 py-3 text-left">Teléfono</th>
              <th class="px-4 py-3 text-center">Fichas</th>
              <th class="px-4 py-3 text-center">Compras</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="pacientes.data.length === 0">
              <td colspan="6" class="text-center text-gray-400 py-8">No se encontraron pacientes</td>
            </tr>
            <tr v-for="p in pacientes.data" :key="p.id" class="border-t hover:bg-gray-50">
              <td class="px-4 py-3">
                <div class="font-semibold text-gray-800">{{ p.nombre }} {{ p.apellidos }}</div>
                <div class="text-xs text-gray-400">{{ p.email || '' }}</div>
              </td>
              <td class="px-4 py-3 text-gray-600">{{ p.dni || '—' }}</td>
              <td class="px-4 py-3 text-gray-600">{{ p.telefono || '—' }}</td>
              <td class="px-4 py-3 text-center">
                <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs">{{ p.fichas_count }}</span>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">{{ p.ventas_count }}</span>
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center gap-2">
                  <Link :href="`/optica/pacientes/${p.id}`"
                    class="text-blue-600 hover:underline text-xs">Ver</Link>
                  <button @click="editar(p)" class="text-yellow-600 hover:underline text-xs">Editar</button>
                  <button @click="eliminar(p.id)" class="text-red-500 hover:underline text-xs">Eliminar</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="flex gap-2 mt-4 justify-end">
        <Link v-for="link in pacientes.links" :key="link.label"
          :href="link.url || '#'"
          v-html="link.label"
          class="px-3 py-1 rounded text-sm border"
          :class="link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600'"/>
      </div>

    </div>

    <!-- Modal Nuevo/Editar Paciente -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">{{ editando ? 'Editar' : 'Nuevo' }} Paciente</h2>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-xs text-gray-500">Nombre *</label>
            <input v-model="form.nombre" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Apellidos *</label>
            <input v-model="form.apellidos" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">DNI</label>
            <input v-model="form.dni" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Teléfono</label>
            <input v-model="form.telefono" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Email</label>
            <input v-model="form.email" type="email" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Fecha Nacimiento</label>
            <input v-model="form.fecha_nacimiento" type="date" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Sexo</label>
            <select v-model="form.sexo" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
              <option value="">— Seleccionar —</option>
              <option value="M">Masculino</option>
              <option value="F">Femenino</option>
            </select>
          </div>
          <div>
            <label class="text-xs text-gray-500">Dirección</label>
            <input v-model="form.direccion" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div class="col-span-2">
            <label class="text-xs text-gray-500">Observaciones</label>
            <textarea v-model="form.observaciones" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
        </div>
        <div class="flex justify-end gap-2 mt-4">
          <button @click="cerrarModal" class="px-4 py-2 border rounded-lg text-sm text-gray-600">Cancelar</button>
          <button @click="guardar" :disabled="guardando"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold disabled:opacity-50">
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
import { Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({ pacientes: Object, q: String })

const showModal = ref(false)
const editando = ref(null)
const guardando = ref(false)
const busqueda = ref(props.q || '')

const form = reactive({
  nombre: '', apellidos: '', dni: '', telefono: '',
  email: '', fecha_nacimiento: '', sexo: '', direccion: '', observaciones: ''
})

const buscar = () => router.get('/optica/pacientes', { q: busqueda.value }, { preserveState: true })

const editar = (p) => {
  editando.value = p.id
  Object.assign(form, { nombre: p.nombre, apellidos: p.apellidos, dni: p.dni || '',
    telefono: p.telefono || '', email: p.email || '', fecha_nacimiento: p.fecha_nacimiento || '',
    sexo: p.sexo || '', direccion: p.direccion || '', observaciones: p.observaciones || '' })
  showModal.value = true
}

const cerrarModal = () => { showModal.value = false; editando.value = null; Object.keys(form).forEach(k => form[k] = '') }

const guardar = () => {
  guardando.value = true
  if (editando.value) {
    router.put(`/optica/pacientes/${editando.value}`, form, {
      onFinish: () => { guardando.value = false; cerrarModal() }
    })
  } else {
    router.post('/optica/pacientes', form, {
      onFinish: () => { guardando.value = false; cerrarModal() }
    })
  }
}

const eliminar = (id) => {
  if (!confirm('¿Eliminar este paciente?')) return
  router.delete(`/optica/pacientes/${id}`)
}
</script>
