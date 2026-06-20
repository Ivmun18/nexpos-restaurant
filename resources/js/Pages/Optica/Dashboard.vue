<template>
  <AppLayout title="Dashboard Óptica">
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6">

      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-blue-800">🔭 Óptica — Dashboard</h1>
          <p class="text-gray-500 text-sm">Resumen del día</p>
        </div>
        <div v-if="cajaAbierta" class="bg-green-100 text-green-700 px-4 py-2 rounded-lg text-sm font-medium">
          ✅ Caja abierta
        </div>
        <div v-else class="bg-red-100 text-red-600 px-4 py-2 rounded-lg text-sm font-medium">
          🔴 Caja cerrada
        </div>
      </div>

      <!-- Tarjetas resumen -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-blue-500">
          <p class="text-xs text-gray-500 uppercase">Ventas hoy</p>
          <p class="text-2xl font-bold text-blue-700">S/ {{ formatNum(ventasHoy) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-green-500">
          <p class="text-xs text-gray-500 uppercase">Atenciones mes</p>
          <p class="text-2xl font-bold text-green-700">{{ atencionesMes }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-yellow-500">
          <p class="text-xs text-gray-500 uppercase">Stock bajo</p>
          <p class="text-2xl font-bold text-yellow-600">{{ stockBajo }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4 border-l-4 border-purple-500">
          <p class="text-xs text-gray-500 uppercase">Ventas semana</p>
          <p class="text-2xl font-bold text-purple-700">S/ {{ formatNum(totalSemana) }}</p>
        </div>
      </div>

      <!-- Accesos rápidos -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <Link :href="'/optica/ventas/pos'" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl p-4 text-center transition">
          <div class="text-2xl mb-1">🛒</div>
          <div class="text-sm font-semibold">Nueva Venta</div>
        </Link>
        <Link :href="'/optica/pacientes'" class="bg-green-600 hover:bg-green-700 text-white rounded-xl p-4 text-center transition">
          <div class="text-2xl mb-1">👤</div>
          <div class="text-sm font-semibold">Pacientes</div>
        </Link>
        <Link :href="'/optica/fichas'" class="bg-purple-600 hover:bg-purple-700 text-white rounded-xl p-4 text-center transition">
          <div class="text-2xl mb-1">👁️</div>
          <div class="text-sm font-semibold">Fichas</div>
        </Link>
        <Link :href="'/optica/caja'" class="bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl p-4 text-center transition">
          <div class="text-2xl mb-1">💰</div>
          <div class="text-sm font-semibold">Caja</div>
        </Link>
      </div>

      <div class="grid md:grid-cols-2 gap-6">
        <!-- Últimas ventas -->
        <div class="bg-white rounded-xl shadow p-4">
          <h2 class="font-bold text-gray-700 mb-3">Últimas ventas</h2>
          <div v-if="ultimasVentas.length === 0" class="text-gray-400 text-sm text-center py-4">Sin ventas aún</div>
          <table v-else class="w-full text-sm">
            <thead><tr class="text-gray-500 text-xs border-b">
              <th class="text-left py-1">N°</th>
              <th class="text-left py-1">Paciente</th>
              <th class="text-right py-1">Total</th>
            </tr></thead>
            <tbody>
              <tr v-for="v in ultimasVentas" :key="v.id" class="border-b last:border-0">
                <td class="py-1 text-blue-600 font-mono">{{ v.numero_venta }}</td>
                <td class="py-1 text-gray-700">{{ v.paciente ? v.paciente.nombre + ' ' + v.paciente.apellidos : '—' }}</td>
                <td class="py-1 text-right font-semibold">S/ {{ formatNum(v.total) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Ventas por día -->
        <div class="bg-white rounded-xl shadow p-4">
          <h2 class="font-bold text-gray-700 mb-3">Ventas esta semana</h2>
          <div v-if="ventasSemana.length === 0" class="text-gray-400 text-sm text-center py-4">Sin datos</div>
          <div v-else class="space-y-2">
            <div v-for="d in ventasSemana" :key="d.dia" class="flex items-center gap-2">
              <span class="text-xs text-gray-500 w-24">{{ formatFecha(d.dia) }}</span>
              <div class="flex-1 bg-gray-100 rounded-full h-4 overflow-hidden">
                <div class="bg-blue-500 h-4 rounded-full transition-all"
                  :style="{ width: (d.total / maxVenta * 100) + '%' }"></div>
              </div>
              <span class="text-xs font-semibold text-gray-700 w-16 text-right">S/ {{ formatNum(d.total) }}</span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

</AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  ventasHoy: Number,
  atencionesMes: Number,
  stockBajo: Number,
  cajaAbierta: Object,
  ventasSemana: Array,
  ultimasVentas: Array,
})

const totalSemana = computed(() => props.ventasSemana.reduce((s, d) => s + parseFloat(d.total), 0))
const maxVenta = computed(() => Math.max(...props.ventasSemana.map(d => parseFloat(d.total)), 1))

const formatNum = (n) => parseFloat(n || 0).toFixed(2)
const formatFecha = (f) => new Date(f + 'T12:00:00').toLocaleDateString('es-PE', { weekday: 'short', day: 'numeric' })
</script>
