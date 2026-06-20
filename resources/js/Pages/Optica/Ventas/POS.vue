<template>
  <AppLayout title="Punto de Venta">
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-4">

      <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold text-blue-800">🛒 Punto de Venta — Óptica</h1>
        <Link :href="'/optica/ventas'" class="text-sm text-gray-500 hover:underline">← Ver ventas</Link>
      </div>

      <div v-if="!cajaAbierta" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
        <p class="text-red-600 font-semibold mb-2">⚠️ No hay caja abierta</p>
        <Link :href="'/optica/caja'" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm">Ir a Caja</Link>
      </div>

      <div v-else class="grid md:grid-cols-3 gap-4">

        <!-- Panel izquierdo: productos -->
        <div class="md:col-span-2 space-y-4">

          <!-- Buscador productos -->
          <div class="bg-white rounded-xl shadow p-4">
            <input v-model="busqProd" type="text" placeholder="Buscar producto por nombre o código..."
              class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"/>
            <!-- Filtro categoría -->
            <div class="flex gap-2 mt-2 flex-wrap">
              <button v-for="c in categorias" :key="c.value"
                @click="catActiva = catActiva===c.value ? '' : c.value"
                class="px-2 py-1 rounded-full text-xs border transition"
                :class="catActiva===c.value ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600'">
                {{ c.label }}
              </button>
            </div>
          </div>

          <!-- Grid productos -->
          <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
            <button v-for="p in productosFiltrados" :key="p.id"
              @click="agregarItem(p)"
              class="bg-white rounded-xl shadow p-3 text-left hover:shadow-md hover:border-blue-300 border-2 border-transparent transition">
              <div class="text-xs font-medium px-1.5 py-0.5 rounded-full mb-1 inline-block" :class="catColor(p.categoria)">
                {{ catLabel(p.categoria) }}
              </div>
              <div class="font-semibold text-gray-800 text-sm leading-tight">{{ p.nombre }}</div>
              <div class="text-xs text-gray-400 mt-0.5">{{ p.marca || '' }}</div>
              <div class="flex justify-between items-center mt-2">
                <span class="text-blue-700 font-bold">S/ {{ fmt(p.precio_venta) }}</span>
                <span class="text-xs text-gray-400">Stock: {{ p.stock }}</span>
              </div>
            </button>
            <div v-if="productosFiltrados.length===0" class="col-span-3 text-center text-gray-400 py-8 text-sm">
              Sin productos
            </div>
          </div>
        </div>

        <!-- Panel derecho: carrito -->
        <div class="space-y-4">
          <!-- Paciente -->
          <div class="bg-white rounded-xl shadow p-4">
            <label class="text-xs text-gray-500 font-semibold">Paciente (opcional)</label>
            <select v-model="venta.paciente_id" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
              <option value="">— Sin paciente —</option>
              <option v-for="p in pacientes" :key="p.id" :value="p.id">
                {{ p.apellidos }}, {{ p.nombre }}
              </option>
            </select>
          </div>

          <!-- Carrito -->
          <div class="bg-white rounded-xl shadow p-4">
            <h3 class="font-bold text-gray-700 mb-3">🧾 Carrito</h3>
            <div v-if="venta.items.length===0" class="text-gray-400 text-sm text-center py-4">
              Agrega productos al carrito
            </div>
            <div v-else class="space-y-2 max-h-64 overflow-y-auto">
              <div v-for="(item, i) in venta.items" :key="i"
                class="flex items-center gap-2 border-b pb-2">
                <div class="flex-1 min-w-0">
                  <div class="text-sm font-medium text-gray-800 truncate">{{ item.descripcion }}</div>
                  <div class="text-xs text-gray-400">S/ {{ fmt(item.precio_unitario) }} c/u</div>
                </div>
                <div class="flex items-center gap-1">
                  <button @click="item.cantidad > 1 ? item.cantidad-- : null"
                    class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-xs font-bold hover:bg-gray-200">−</button>
                  <span class="w-6 text-center text-sm font-semibold">{{ item.cantidad }}</span>
                  <button @click="item.cantidad++"
                    class="w-6 h-6 rounded-full bg-gray-100 text-gray-600 text-xs font-bold hover:bg-gray-200">+</button>
                </div>
                <div class="text-sm font-bold text-gray-800 w-16 text-right">
                  S/ {{ fmt(item.cantidad * item.precio_unitario) }}
                </div>
                <button @click="quitarItem(i)" class="text-red-400 hover:text-red-600 text-xs">✕</button>
              </div>
            </div>

            <!-- Totales -->
            <div class="border-t mt-3 pt-3 space-y-1 text-sm">
              <div class="flex justify-between text-gray-600">
                <span>Subtotal (sin IGV)</span>
                <span>S/ {{ fmt(subtotalSinIgv) }}</span>
              </div>
              <div class="flex justify-between text-gray-600">
                <span>IGV (18%)</span>
                <span>S/ {{ fmt(igv) }}</span>
              </div>
              <div class="flex justify-between font-bold text-lg text-blue-800 border-t pt-2">
                <span>TOTAL</span>
                <span>S/ {{ fmt(total) }}</span>
              </div>
            </div>
          </div>

          <!-- Pago -->
          <div class="bg-white rounded-xl shadow p-4 space-y-3">
            <div>
              <label class="text-xs text-gray-500">Comprobante</label>
              <select v-model="venta.tipo_comprobante" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
                <option value="ticket">Ticket</option>
                <option value="boleta">Boleta</option>
                <option value="factura">Factura</option>
              </select>
            </div>
            <div v-if="venta.tipo_comprobante==='factura'" class="space-y-2">
              <input v-model="venta.ruc_cliente" placeholder="RUC cliente" class="w-full border rounded-lg px-3 py-2 text-sm"/>
              <input v-model="venta.razon_social_cliente" placeholder="Razón social" class="w-full border rounded-lg px-3 py-2 text-sm"/>
            </div>
            <div>
              <label class="text-xs text-gray-500">Método de pago</label>
              <div class="grid grid-cols-3 gap-1 mt-1">
                <button v-for="m in metodos" :key="m.value" @click="venta.metodo_pago=m.value"
                  class="py-1.5 rounded-lg text-xs font-medium border transition"
                  :class="venta.metodo_pago===m.value ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600'">
                  {{ m.label }}
                </button>
              </div>
            </div>
            <div>
              <label class="text-xs text-gray-500">Monto recibido</label>
              <input v-model="venta.monto_pagado" type="number" step="0.10"
                class="w-full border rounded-lg px-3 py-2 text-sm mt-1 text-right font-bold text-lg"/>
            </div>
            <div class="flex justify-between text-sm font-semibold"
              :class="vuelto >= 0 ? 'text-green-700' : 'text-red-600'">
              <span>Vuelto</span>
              <span>S/ {{ fmt(vuelto) }}</span>
            </div>
            <button @click="procesarVenta" :disabled="procesando || venta.items.length===0 || total===0"
              class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-bold text-sm disabled:opacity-50 transition">
              {{ procesando ? 'Procesando...' : '✅ Confirmar Venta' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal éxito -->
    <div v-if="ventaExito" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl shadow-xl p-8 text-center max-w-sm w-full mx-4">
        <div class="text-5xl mb-3">✅</div>
        <h2 class="text-xl font-bold text-gray-800 mb-1">¡Venta registrada!</h2>
        <p class="text-gray-500 text-sm mb-1">{{ ventaExito.numero }}</p>
        <p class="text-2xl font-bold text-green-600 mb-1">Vuelto: S/ {{ fmt(ventaExito.vuelto) }}</p>
        <div class="flex gap-2 mt-4">
          <a :href="`/optica/ventas/${ventaExito.venta_id}/comprobante`" target="_blank"
            class="flex-1 bg-blue-600 text-white py-2 rounded-lg text-sm font-semibold">🖨️ Imprimir</a>
          <button @click="nuevaVenta"
            class="flex-1 bg-green-600 text-white py-2 rounded-lg text-sm font-semibold">+ Nueva Venta</button>
        </div>
      </div>
    </div>
  </div>

</AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, reactive, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({ productos: Array, pacientes: Array, cajaAbierta: Object })

const busqProd = ref('')
const catActiva = ref('')
const procesando = ref(false)
const ventaExito = ref(null)

const categorias = [
  { value: 'luna', label: 'Luna' },
  { value: 'montura', label: 'Montura' },
  { value: 'lente_contacto', label: 'L. Contacto' },
  { value: 'solucion', label: 'Solución' },
  { value: 'accesorio', label: 'Accesorio' },
]

const metodos = [
  { value: 'efectivo', label: 'Efectivo' },
  { value: 'yape', label: 'Yape' },
  { value: 'plin', label: 'Plin' },
  { value: 'tarjeta', label: 'Tarjeta' },
  { value: 'transferencia', label: 'Transfer.' },
]

const venta = reactive({
  paciente_id: '', tipo_comprobante: 'boleta', metodo_pago: 'efectivo',
  monto_pagado: 0, ruc_cliente: '', razon_social_cliente: '', items: []
})

const productosFiltrados = computed(() => props.productos.filter(p => {
  const q = busqProd.value.toLowerCase()
  const matchQ = !q || p.nombre.toLowerCase().includes(q) || (p.codigo||'[]').toLowerCase().includes(q) || (p.marca||'[]').toLowerCase().includes(q)
  const matchC = !catActiva.value || p.categoria === catActiva.value
  return matchQ && matchC
}))

const subtotalSinIgv = computed(() => venta.items.reduce((s,i) => s + i.cantidad * i.precio_unitario, 0) / 1.18)
const igv = computed(() => venta.items.reduce((s,i) => s + i.cantidad * i.precio_unitario, 0) - subtotalSinIgv.value)
const total = computed(() => venta.items.reduce((s,i) => s + i.cantidad * i.precio_unitario, 0))
const vuelto = computed(() => parseFloat(venta.monto_pagado || 0) - total.value)

const fmt = (n) => parseFloat(n||0).toFixed(2)
const catLabel = (v) => categorias.find(c=>c.value===v)?.label || v
const catColor = (v) => ({
  luna: 'bg-blue-100 text-blue-700',
  montura: 'bg-purple-100 text-purple-700',
  lente_contacto: 'bg-green-100 text-green-700',
  solucion: 'bg-yellow-100 text-yellow-700',
  accesorio: 'bg-gray-100 text-gray-600',
}[v] || 'bg-gray-100 text-gray-500')

const agregarItem = (p) => {
  const exist = venta.items.find(i => i.producto_id === p.id)
  if (exist) { exist.cantidad++; return }
  venta.items.push({ producto_id: p.id, descripcion: p.nombre, cantidad: 1, precio_unitario: parseFloat(p.precio_venta) })
  if (!venta.monto_pagado) venta.monto_pagado = total.value
}

const quitarItem = (i) => venta.items.splice(i, 1)

const procesarVenta = async () => {
  if (venta.items.length === 0) return
  procesando.value = true
  try {
    const res = await axios.post('/optica/ventas', {
      ...venta,
      monto_pagado: parseFloat(venta.monto_pagado)
    })
    if (res.data.success) {
      ventaExito.value = res.data
    } else {
      alert('Error: ' + res.data.error)
    }
  } catch(e) {
    alert('Error al procesar la venta')
  } finally {
    procesando.value = false
  }
}

const nuevaVenta = () => {
  ventaExito.value = null
  venta.items = []
  venta.paciente_id = ''
  venta.monto_pagado = 0
  venta.tipo_comprobante = 'boleta'
  venta.ruc_cliente = ''
  venta.razon_social_cliente = ''
}
</script>
