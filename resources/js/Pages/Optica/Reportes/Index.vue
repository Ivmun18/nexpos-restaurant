<template>
  <AppLayout title="Reportes">
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 py-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">📊 Reportes</h1>
        <a :href="`/optica/reportes/export?desde=${desde}&hasta=${hasta}`"
          class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">⬇ Exportar CSV</a>
      </div>

      <!-- Filtro fechas -->
      <div class="bg-white rounded-xl shadow p-4 mb-6 flex gap-4 flex-wrap items-end">
        <div>
          <label class="text-xs text-gray-500">Desde</label>
          <input v-model="desde" type="date" class="block border rounded-lg px-3 py-2 text-sm mt-1"/>
        </div>
        <div>
          <label class="text-xs text-gray-500">Hasta</label>
          <input v-model="hasta" type="date" class="block border rounded-lg px-3 py-2 text-sm mt-1"/>
        </div>
        <button @click="filtrar" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">Filtrar</button>
      </div>

      <!-- Tarjetas resumen -->
      <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-blue-500 text-center">
          <p class="text-xs text-gray-400">Total Ventas</p>
          <p class="text-2xl font-bold text-blue-700">{{ resumen?.total_ventas || 0 }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-green-500 text-center">
          <p class="text-xs text-gray-400">Ingresos</p>
          <p class="text-2xl font-bold text-green-700">S/ {{ fmt(resumen?.ingresos) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-yellow-500 text-center">
          <p class="text-xs text-gray-400">IGV</p>
          <p class="text-2xl font-bold text-yellow-600">S/ {{ fmt(resumen?.total_igv) }}</p>
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-6">
        <!-- Ventas por día -->
        <div class="bg-white rounded-xl shadow p-4">
          <h3 class="font-bold text-gray-700 mb-3">Ventas por día</h3>
          <div v-if="porDia.length===0" class="text-gray-400 text-sm text-center py-4">Sin datos</div>
          <div v-else class="space-y-2">
            <div v-for="d in porDia" :key="d.dia" class="flex items-center gap-2">
              <span class="text-xs text-gray-500 w-24">{{ d.dia }}</span>
              <div class="flex-1 bg-gray-100 rounded-full h-4">
                <div class="bg-blue-500 h-4 rounded-full" :style="{width: (d.total/maxDia*100)+'%'}"></div>
              </div>
              <span class="text-xs font-semibold w-20 text-right">S/ {{ fmt(d.total) }}</span>
            </div>
          </div>
        </div>

        <!-- Por método de pago -->
        <div class="bg-white rounded-xl shadow p-4">
          <h3 class="font-bold text-gray-700 mb-3">Por método de pago</h3>
          <div v-if="porMetodo.length===0" class="text-gray-400 text-sm text-center py-4">Sin datos</div>
          <div v-else class="space-y-2">
            <div v-for="m in porMetodo" :key="m.metodo_pago" class="flex justify-between items-center py-2 border-b last:border-0">
              <span class="capitalize text-gray-700">{{ m.metodo_pago }}</span>
              <div class="text-right">
                <span class="font-bold text-gray-800">S/ {{ fmt(m.total) }}</span>
                <span class="text-xs text-gray-400 ml-2">({{ m.cantidad }} ventas)</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Top productos -->
        <div class="bg-white rounded-xl shadow p-4 md:col-span-2">
          <h3 class="font-bold text-gray-700 mb-3">Top productos vendidos</h3>
          <table class="w-full text-sm">
            <thead class="text-xs text-gray-500 border-b">
              <tr>
                <th class="text-left py-2">#</th>
                <th class="text-left py-2">Producto</th>
                <th class="text-right py-2">Cantidad</th>
                <th class="text-right py-2">Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(p, i) in topProductos" :key="i" class="border-b last:border-0">
                <td class="py-2 text-gray-400">{{ i+1 }}</td>
                <td class="py-2 text-gray-700">{{ p.descripcion }}</td>
                <td class="py-2 text-right text-gray-600">{{ p.cantidad }}</td>
                <td class="py-2 text-right font-bold text-green-700">S/ {{ fmt(p.total) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Stock bajo -->
        <div v-if="stockBajo.length > 0" class="bg-red-50 rounded-xl border border-red-200 p-4 md:col-span-2">
          <h3 class="font-bold text-red-700 mb-2">⚠️ Productos con stock bajo</h3>
          <div class="flex flex-wrap gap-2">
            <span v-for="p in stockBajo" :key="p.id"
              class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium">
              {{ p.nombre }} ({{ p.stock }} und)
            </span>
          </div>
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

const props = defineProps({
  resumen: Object, porMetodo: Array, porDia: Array,
  topProductos: Array, stockBajo: Array, desde: String, hasta: String
})

const desde = ref(props.desde)
const hasta = ref(props.hasta)
const fmt = (n) => parseFloat(n||0).toFixed(2)
const maxDia = computed(() => Math.max(...(props.porDia||[]).map(d => parseFloat(d.total)), 1))
const filtrar = () => router.get('/optica/reportes', { desde: desde.value, hasta: hasta.value })
</script>
