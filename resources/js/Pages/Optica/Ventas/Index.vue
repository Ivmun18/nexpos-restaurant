<template>
  <AppLayout title="Ventas">
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">🧾 Ventas</h1>
        <Link :href="'/optica/ventas/pos'" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
          + Nueva Venta
        </Link>
      </div>
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">N° Venta</th>
              <th class="px-4 py-3 text-left">Paciente</th>
              <th class="px-4 py-3 text-left">Fecha</th>
              <th class="px-4 py-3 text-center">Comprobante</th>
              <th class="px-4 py-3 text-center">Método</th>
              <th class="px-4 py-3 text-right">Total</th>
              <th class="px-4 py-3 text-center">Estado</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="ventas.data.length===0">
              <td colspan="8" class="text-center text-gray-400 py-8">Sin ventas registradas</td>
            </tr>
            <tr v-for="v in ventas.data" :key="v.id" class="border-t hover:bg-gray-50">
              <td class="px-4 py-3 font-mono text-blue-600 font-semibold">{{ v.numero_venta }}</td>
              <td class="px-4 py-3 text-gray-700">{{ v.paciente ? v.paciente.nombre+' '+v.paciente.apellidos : '—' }}</td>
              <td class="px-4 py-3 text-gray-500">{{ fmtFecha(v.fecha) }}</td>
              <td class="px-4 py-3 text-center capitalize text-gray-600">{{ v.tipo_comprobante }}</td>
              <td class="px-4 py-3 text-center capitalize text-gray-600">{{ v.metodo_pago }}</td>
              <td class="px-4 py-3 text-right font-bold text-gray-800">S/ {{ fmt(v.total) }}</td>
              <td class="px-4 py-3 text-center">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                  :class="v.estado==='pagado' ? 'bg-green-100 text-green-700' : v.estado==='anulado' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700'">
                  {{ v.estado }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex justify-center gap-2">
                  <a :href="`/optica/ventas/${v.id}/comprobante`" target="_blank"
                    class="text-blue-600 hover:underline text-xs">PDF</a>
                  <button v-if="v.estado!=='anulado'" @click="anular(v.id)"
                    class="text-red-500 hover:underline text-xs">Anular</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="flex gap-2 mt-4 justify-end">
        <Link v-for="link in ventas.links" :key="link.label" :href="link.url||'#'"
          v-html="link.label" class="px-3 py-1 rounded text-sm border"
          :class="link.active?'bg-blue-600 text-white border-blue-600':'bg-white text-gray-600'"/>
      </div>
    </div>
  </div>

</AppLayout>
</template>

<script setup>
const fmtFecha = (f) => { if (!f) return "—"; const d = new Date(f); return d.toLocaleDateString("es-PE",{year:"numeric",month:"short",day:"numeric"}) }
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
const props = defineProps({ ventas: Object })
const fmt = (n) => parseFloat(n||0).toFixed(2)
const anular = (id) => { if (!confirm('¿Anular esta venta?')) return; router.post(`/optica/ventas/${id}/anular`) }
</script>
