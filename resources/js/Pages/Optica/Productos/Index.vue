<template>
  <AppLayout title="Productos">
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6">

      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">📦 Productos / Inventario</h1>
        <button @click="showModal=true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
          + Nuevo Producto
        </button>
      </div>

      <!-- Filtros -->
      <div class="flex gap-3 mb-4 flex-wrap">
        <input v-model="busqueda" @keyup.enter="buscar" type="text" placeholder="Buscar..."
          class="border rounded-lg px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-blue-400"/>
        <select v-model="catFiltro" @change="buscar" class="border rounded-lg px-3 py-2 text-sm">
          <option value="">Todas las categorías</option>
          <option v-for="c in categorias" :key="c.value" :value="c.value">{{ c.label }}</option>
        </select>
      </div>

      <!-- Tabla -->
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">Código</th>
              <th class="px-4 py-3 text-left">Nombre</th>
              <th class="px-4 py-3 text-left">Categoría</th>
              <th class="px-4 py-3 text-left">Marca</th>
              <th class="px-4 py-3 text-right">P. Compra</th>
              <th class="px-4 py-3 text-right">P. Venta</th>
              <th class="px-4 py-3 text-center">Stock</th>
              <th class="px-4 py-3 text-center">Estado</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="productos.data.length===0">
              <td colspan="9" class="text-center text-gray-400 py-8">Sin productos</td>
            </tr>
            <tr v-for="p in productos.data" :key="p.id" class="border-t hover:bg-gray-50"
              :class="p.stock <= p.stock_minimo ? 'bg-red-50' : ''">
              <td class="px-4 py-3 font-mono text-gray-500 text-xs">{{ p.codigo || '—' }}</td>
              <td class="px-4 py-3 font-semibold text-gray-800">{{ p.nombre }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="catColor(p.categoria)">
                  {{ catLabel(p.categoria) }}
                </span>
              </td>
              <td class="px-4 py-3 text-gray-600">{{ p.marca || '—' }}</td>
              <td class="px-4 py-3 text-right text-gray-600">S/ {{ fmt(p.precio_compra) }}</td>
              <td class="px-4 py-3 text-right font-semibold text-green-700">S/ {{ fmt(p.precio_venta) }}</td>
              <td class="px-4 py-3 text-center">
                <span class="font-bold" :class="p.stock <= p.stock_minimo ? 'text-red-600' : 'text-gray-800'">
                  {{ p.stock }}
                </span>
                <span class="text-gray-400 text-xs"> / min {{ p.stock_minimo }}</span>
              </td>
              <td class="px-4 py-3 text-center">
                <span v-if="p.activo" class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">Activo</span>
                <span v-else class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full text-xs">Inactivo</span>
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center gap-2">
                  <button @click="editar(p)" class="text-yellow-600 hover:underline text-xs">Editar</button>
                  <button @click="eliminar(p.id)" class="text-red-500 hover:underline text-xs">Eliminar</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex gap-2 mt-4 justify-end">
        <Link v-for="link in productos.links" :key="link.label" :href="link.url||'#'"
          v-html="link.label" class="px-3 py-1 rounded text-sm border"
          :class="link.active?'bg-blue-600 text-white border-blue-600':'bg-white text-gray-600'"/>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4">{{ editando ? 'Editar' : 'Nuevo' }} Producto</h2>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-xs text-gray-500">Código</label>
            <input v-model="form.codigo" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Nombre *</label>
            <input v-model="form.nombre" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Categoría *</label>
            <select v-model="form.categoria" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
              <option v-for="c in categorias" :key="c.value" :value="c.value">{{ c.label }}</option>
            </select>
          </div>
          <div>
            <label class="text-xs text-gray-500">Marca</label>
            <input v-model="form.marca" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Precio Compra *</label>
            <input v-model="form.precio_compra" type="number" step="0.01" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Precio Venta *</label>
            <input v-model="form.precio_venta" type="number" step="0.01" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Stock *</label>
            <input v-model="form.stock" type="number" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Stock Mínimo</label>
            <input v-model="form.stock_minimo" type="number" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Unidad</label>
            <input v-model="form.unidad" placeholder="und" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div class="flex items-center gap-2 mt-4">
            <input v-model="form.activo" type="checkbox" id="activo" class="w-4 h-4"/>
            <label for="activo" class="text-sm text-gray-600">Activo</label>
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
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({ productos: Object, q: String, categoria: String })

const showModal = ref(false)
const editando = ref(null)
const guardando = ref(false)
const busqueda = ref(props.q || '')
const catFiltro = ref(props.categoria || '')

const categorias = [
  { value: 'luna', label: 'Luna' },
  { value: 'montura', label: 'Montura' },
  { value: 'lente_contacto', label: 'Lente de Contacto' },
  { value: 'solucion', label: 'Solución' },
  { value: 'accesorio', label: 'Accesorio' },
  { value: 'otro', label: 'Otro' },
]

const catLabel = (v) => categorias.find(c=>c.value===v)?.label || v
const catColor = (v) => ({
  luna: 'bg-blue-100 text-blue-700',
  montura: 'bg-purple-100 text-purple-700',
  lente_contacto: 'bg-green-100 text-green-700',
  solucion: 'bg-yellow-100 text-yellow-700',
  accesorio: 'bg-gray-100 text-gray-600',
  otro: 'bg-gray-100 text-gray-500',
}[v] || 'bg-gray-100 text-gray-500')

const fmt = (n) => parseFloat(n||0).toFixed(2)

const form = reactive({
  codigo: '', nombre: '', categoria: 'luna', marca: '',
  precio_compra: '', precio_venta: '', stock: 0, stock_minimo: 3, unidad: 'und', activo: true
})

const buscar = () => router.get('/optica/productos', { q: busqueda.value, categoria: catFiltro.value }, { preserveState: true })

const editar = (p) => {
  editando.value = p.id
  Object.assign(form, { codigo: p.codigo||'', nombre: p.nombre, categoria: p.categoria,
    marca: p.marca||'', precio_compra: p.precio_compra, precio_venta: p.precio_venta,
    stock: p.stock, stock_minimo: p.stock_minimo, unidad: p.unidad||'und', activo: p.activo })
  showModal.value = true
}

const cerrarModal = () => { showModal.value = false; editando.value = null }

const guardar = () => {
  guardando.value = true
  if (editando.value) {
    router.put(`/optica/productos/${editando.value}`, form, { onFinish: () => { guardando.value = false; cerrarModal() } })
  } else {
    router.post('/optica/productos', form, { onFinish: () => { guardando.value = false; cerrarModal() } })
  }
}

const eliminar = (id) => {
  if (!confirm('¿Eliminar este producto?')) return
  router.delete(`/optica/productos/${id}`)
}
</script>
