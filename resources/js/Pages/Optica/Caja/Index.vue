<template>
  <AppLayout title="Caja">
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-6">
      <h1 class="text-2xl font-bold text-yellow-700 mb-6">💰 Caja</h1>

      <!-- Caja cerrada -->
      <div v-if="!cajaAbierta" class="bg-white rounded-xl shadow p-6 text-center">
        <p class="text-gray-500 mb-4">No hay caja abierta hoy.</p>
        <div class="max-w-xs mx-auto space-y-3">
          <div>
            <label class="text-xs text-gray-500">Monto inicial (S/)</label>
            <input v-model="montoInicial" type="number" step="0.01"
              class="w-full border rounded-lg px-3 py-2 text-sm mt-1 text-center text-lg font-bold"/>
          </div>
          <button @click="abrirCaja"
            class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-3 rounded-xl font-bold">
            🔓 Abrir Caja
          </button>
        </div>
      </div>

      <!-- Caja abierta -->
      <div v-else class="space-y-4">
        <!-- Resumen -->
        <div class="grid grid-cols-3 gap-4">
          <div class="bg-white rounded-xl shadow p-4 text-center border-l-4 border-yellow-400">
            <p class="text-xs text-gray-400">Monto inicial</p>
            <p class="text-xl font-bold text-gray-700">S/ {{ fmt(cajaAbierta.monto_inicial) }}</p>
          </div>
          <div class="bg-white rounded-xl shadow p-4 text-center border-l-4 border-green-400">
            <p class="text-xs text-gray-400">Total ventas</p>
            <p class="text-xl font-bold text-green-600">S/ {{ fmt(cajaAbierta.total_ventas) }}</p>
          </div>
          <div class="bg-white rounded-xl shadow p-4 text-center border-l-4 border-red-400">
            <p class="text-xs text-gray-400">Total egresos</p>
            <p class="text-xl font-bold text-red-600">S/ {{ fmt(cajaAbierta.total_egresos) }}</p>
          </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
          <p class="text-sm text-gray-500">Saldo estimado en caja</p>
          <p class="text-3xl font-bold text-green-700">
            S/ {{ fmt(parseFloat(cajaAbierta.monto_inicial) + parseFloat(cajaAbierta.total_ventas) - parseFloat(cajaAbierta.total_egresos)) }}
          </p>
        </div>

        <!-- Movimientos -->
        <div class="bg-white rounded-xl shadow p-4">
          <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold text-gray-700">Movimientos</h3>
            <button @click="showMovModal=true" class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-lg">
              + Movimiento
            </button>
          </div>
          <div v-if="!cajaAbierta.movimientos || cajaAbierta.movimientos.length===0"
            class="text-gray-400 text-sm text-center py-4">Sin movimientos</div>
          <div v-else class="space-y-1 max-h-48 overflow-y-auto">
            <div v-for="m in cajaAbierta.movimientos" :key="m.id"
              class="flex justify-between items-center py-2 border-b last:border-0 text-sm">
              <div>
                <span class="font-medium text-gray-700">{{ m.concepto }}</span>
                <span class="text-xs text-gray-400 ml-2">{{ m.created_at }}</span>
              </div>
              <span :class="m.tipo==='ingreso' ? 'text-green-600' : 'text-red-600'" class="font-bold">
                {{ m.tipo==='ingreso' ? '+' : '-' }} S/ {{ fmt(m.monto) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Cerrar caja -->
        <div class="bg-white rounded-xl shadow p-4">
          <h3 class="font-bold text-gray-700 mb-3">Cerrar Caja</h3>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-xs text-gray-500">Monto final contado (S/)</label>
              <input v-model="cierreFrm.monto_final" type="number" step="0.01"
                class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
            </div>
            <div>
              <label class="text-xs text-gray-500">Observaciones</label>
              <input v-model="cierreFrm.observaciones" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
            </div>
          </div>
          <button @click="cerrarCaja" class="mt-3 w-full bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-xl font-bold text-sm">
            🔒 Cerrar Caja
          </button>
        </div>
      </div>

      <!-- Historial -->
      <div v-if="historial.length > 0" class="bg-white rounded-xl shadow p-4 mt-4">
        <h3 class="font-bold text-gray-700 mb-3">Historial de cajas</h3>
        <table class="w-full text-sm">
          <thead class="text-xs text-gray-500 border-b">
            <tr>
              <th class="text-left py-2">Fecha</th>
              <th class="text-right py-2">Inicial</th>
              <th class="text-right py-2">Ventas</th>
              <th class="text-right py-2">Egresos</th>
              <th class="text-right py-2">Final</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in historial" :key="c.id" class="border-b last:border-0">
              <td class="py-2 text-gray-600">{{ c.fecha }}</td>
              <td class="py-2 text-right">S/ {{ fmt(c.monto_inicial) }}</td>
              <td class="py-2 text-right text-green-600">S/ {{ fmt(c.total_ventas) }}</td>
              <td class="py-2 text-right text-red-500">S/ {{ fmt(c.total_egresos) }}</td>
              <td class="py-2 text-right font-bold">S/ {{ fmt(c.monto_final) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal movimiento -->
    <div v-if="showMovModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4">
        <h3 class="font-bold text-gray-800 mb-4">Nuevo Movimiento</h3>
        <div class="space-y-3">
          <div>
            <label class="text-xs text-gray-500">Tipo</label>
            <select v-model="movFrm.tipo" class="w-full border rounded-lg px-3 py-2 text-sm mt-1">
              <option value="ingreso">Ingreso</option>
              <option value="egreso">Egreso</option>
            </select>
          </div>
          <div>
            <label class="text-xs text-gray-500">Concepto</label>
            <input v-model="movFrm.concepto" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
          <div>
            <label class="text-xs text-gray-500">Monto</label>
            <input v-model="movFrm.monto" type="number" step="0.01" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/>
          </div>
        </div>
        <div class="flex gap-2 mt-4">
          <button @click="showMovModal=false" class="flex-1 border rounded-lg py-2 text-sm text-gray-600">Cancelar</button>
          <button @click="guardarMovimiento" class="flex-1 bg-yellow-500 text-white rounded-lg py-2 text-sm font-bold">Guardar</button>
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

const props = defineProps({ cajaAbierta: Object, historial: Array })

const montoInicial = ref(0)
const showMovModal = ref(false)
const fmt = (n) => parseFloat(n||0).toFixed(2)

const cierreFrm = reactive({ monto_final: '', observaciones: '' })
const movFrm = reactive({ tipo: 'egreso', concepto: '', monto: '' })

const abrirCaja = () => router.post('/optica/caja/abrir', { monto_inicial: montoInicial.value })
const cerrarCaja = () => {
  if (!confirm('¿Cerrar la caja?')) return
  router.post('/optica/caja/cerrar', cierreFrm)
}
const guardarMovimiento = () => {
  router.post('/optica/caja/movimiento', movFrm, { onFinish: () => { showMovModal.value = false; movFrm.concepto=''; movFrm.monto='' } })
}
</script>
