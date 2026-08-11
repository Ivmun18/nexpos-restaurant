<template>
  <AppLayout title="Categorías">
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">🏷️ Categorías</h1>
        <button @click="showModal=true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">+ Nueva Categoría</button>
      </div>
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">Color</th>
              <th class="px-4 py-3 text-left">Nombre</th>
              <th class="px-4 py-3 text-center">Productos</th>
              <th class="px-4 py-3 text-center">Estado</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="categorias.length===0"><td colspan="5" class="text-center text-gray-400 py-8">Sin categorías</td></tr>
            <tr v-for="c in categorias" :key="c.id" class="border-t hover:bg-gray-50">
              <td class="px-4 py-3"><span class="w-4 h-4 rounded-full inline-block" :class="'bg-'+c.color+'-500'"></span></td>
              <td class="px-4 py-3 font-semibold text-gray-800">{{ c.nombre }}</td>
              <td class="px-4 py-3 text-center"><span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">{{ c.productos_count }}</span></td>
              <td class="px-4 py-3 text-center">
                <span v-if="c.activo" class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">Activo</span>
                <span v-else class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full text-xs">Inactivo</span>
              </td>
              <td class="px-4 py-3 text-center">
                <button @click="editar(c)" class="text-yellow-600 hover:underline text-xs mr-2">Editar</button>
                <button @click="eliminar(c.id)" class="text-red-500 hover:underline text-xs">Eliminar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">{{ editando ? 'Editar' : 'Nueva' }} Categoría</h2>
        <div class="space-y-3">
          <div>
            <label class="text-xs text-gray-500">Nombre *</label>
            <input v-model="form.nombre" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Color</label>
            <div class="flex gap-2 mt-1 flex-wrap">
              <button v-for="col in colores" :key="col" @click="form.color=col" class="w-8 h-8 rounded-full border-2 transition" :class="['bg-'+col+'-500', form.color===col ? 'border-gray-800 scale-110' : 'border-transparent']"></button>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <input v-model="form.activo" type="checkbox" id="cat_activo" class="w-4 h-4"/>
            <label for="cat_activo" class="text-sm text-gray-600">Activo</label>
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
const props = defineProps({ categorias: Array })
const showModal = ref(false)
const editando = ref(null)
const guardando = ref(false)
const colores = ['blue','purple','green','yellow','red','pink','orange','gray','teal','indigo']
const form = reactive({ nombre: '', color: 'blue', activo: true })
const editar = (c) => { editando.value = c.id; Object.assign(form, { nombre: c.nombre, color: c.color, activo: c.activo }); showModal.value = true }
const cerrarModal = () => { showModal.value = false; editando.value = null; form.nombre = ''; form.color = 'blue'; form.activo = true }
const guardar = () => {
  guardando.value = true
  if (editando.value) {
    router.put(`/optica/categorias/${editando.value}`, form, { onFinish: () => { guardando.value = false; cerrarModal() } })
  } else {
    router.post('/optica/categorias', form, { onFinish: () => { guardando.value = false; cerrarModal() } })
  }
}
const eliminar = (id) => { if (!confirm('Eliminar?')) return; router.delete(`/optica/categorias/${id}`) }
</script>
