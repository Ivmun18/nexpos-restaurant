<template>
  <AppLayout title="Paciente">
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-6">
      <div class="flex items-center gap-4 mb-6">
        <a href="/optica/pacientes" class="text-gray-400 hover:text-gray-600 text-xl">←</a>
        <div>
          <h1 class="text-2xl font-bold text-gray-800">{{ paciente.nombre }} {{ paciente.apellidos }}</h1>
          <p class="text-sm text-gray-400">DNI: {{ paciente.dni || '—' }} | Tel: {{ paciente.telefono || '—' }}</p>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow p-4 mb-6">
        <h2 class="font-bold text-purple-700 mb-3">Fichas Oftalmológicas ({{ fichas.length }})</h2>
        <div v-if="fichas.length===0" class="text-gray-400 text-sm text-center py-4">Sin fichas</div>
        <table v-else class="w-full text-sm">
          <thead class="text-xs text-gray-500 border-b"><tr><th class="text-left py-2">Fecha</th><th class="text-center py-2">OD Esf.</th><th class="text-center py-2">OI Esf.</th><th class="text-center py-2">DIV</th><th class="text-center py-2">PDF</th></tr></thead>
          <tbody>
            <tr v-for="f in fichas" :key="f.id" class="border-b last:border-0">
              <td class="py-2 text-gray-600">{{ f.fecha }}</td>
              <td class="py-2 text-center font-mono text-blue-700">{{ f.od_esfera }}</td>
              <td class="py-2 text-center font-mono text-green-700">{{ f.oi_esfera }}</td>
              <td class="py-2 text-center">{{ f.div || '—' }}</td>
              <td class="py-2 text-center"><a :href="`/optica/fichas/${f.id}/pdf`" target="_blank" class="text-purple-600 hover:underline text-xs">PDF</a></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="bg-white rounded-xl shadow p-4 mb-6">
        <h2 class="font-bold text-blue-700 mb-3">Recetas ({{ recetas.length }})</h2>
        <div v-if="recetas.length===0" class="text-gray-400 text-sm text-center py-4">Sin recetas</div>
        <div v-else class="space-y-2">
          <div v-for="r in recetas" :key="r.id" class="flex justify-between items-center border-b pb-2">
            <div>
              <span class="font-mono text-blue-600 text-sm">{{ r.numero_receta }}</span>
              <span class="text-gray-500 text-xs ml-2">{{ r.fecha }}</span>
              <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">{{ r.tipo }}</span>
            </div>
            <a :href="`/optica/recetas/${r.id}/pdf`" target="_blank" class="text-purple-600 hover:underline text-xs">PDF</a>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow p-4">
        <h2 class="font-bold text-green-700 mb-3">Compras ({{ ventas.length }})</h2>
        <div v-if="ventas.length===0" class="text-gray-400 text-sm text-center py-4">Sin compras</div>
        <table v-else class="w-full text-sm">
          <thead class="text-xs text-gray-500 border-b"><tr><th class="text-left py-2">N°</th><th class="text-left py-2">Fecha</th><th class="text-right py-2">Total</th></tr></thead>
          <tbody>
            <tr v-for="v in ventas" :key="v.id" class="border-b last:border-0">
              <td class="py-2 font-mono text-blue-600">{{ v.numero_venta }}</td>
              <td class="py-2 text-gray-600">{{ v.fecha }}</td>
              <td class="py-2 text-right font-semibold">S/ {{ parseFloat(v.total).toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  </AppLayout>
</template>
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
defineProps({ paciente: Object, fichas: Array, recetas: Array, ventas: Array })
</script>
