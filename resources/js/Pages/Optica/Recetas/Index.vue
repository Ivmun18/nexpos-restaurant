<template>
  <AppLayout title="Recetas">
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6">
      <h1 class="text-2xl font-bold text-blue-800 mb-6">📋 Recetas Oftalmológicas</h1>
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-blue-50 text-blue-700 text-xs uppercase">
            <tr>
              <th class="px-4 py-3 text-left">N° Receta</th>
              <th class="px-4 py-3 text-left">Paciente</th>
              <th class="px-4 py-3 text-left">Fecha</th>
              <th class="px-4 py-3 text-center">Tipo</th>
              <th class="px-4 py-3 text-left">Indicaciones</th>
              <th class="px-4 py-3 text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="recetas.data.length===0">
              <td colspan="6" class="text-center text-gray-400 py-8">Sin recetas</td>
            </tr>
            <tr v-for="r in recetas.data" :key="r.id" class="border-t hover:bg-gray-50">
              <td class="px-4 py-3 font-mono text-blue-600 font-semibold">{{ r.numero_receta }}</td>
              <td class="px-4 py-3 text-gray-800">{{ r.paciente ? r.paciente.nombre+' '+r.paciente.apellidos : '—' }}</td>
              <td class="px-4 py-3 text-gray-600">{{ fmtFecha(r.fecha) }}</td>
              <td class="px-4 py-3 text-center">
                <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="{'bg-blue-100 text-blue-700':r.tipo==='lejos','bg-purple-100 text-purple-700':r.tipo==='progresivo','bg-green-100 text-green-700':r.tipo==='cerca','bg-yellow-100 text-yellow-700':r.tipo==='bifocal'}">{{ r.tipo }}</span>
              </td>
              <td class="px-4 py-3 text-gray-500 text-xs">{{ r.indicaciones || '—' }}</td>
              <td class="px-4 py-3 text-center">
                <a :href="`/optica/recetas/${r.id}/pdf`" target="_blank" class="text-blue-600 hover:underline text-xs mr-2">PDF</a>
                <button @click="eliminar(r.id)" class="text-red-500 hover:underline text-xs">Eliminar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="flex gap-2 mt-4 justify-end">
        <a v-for="link in recetas.links" :key="link.label" :href="link.url||'#'" v-html="link.label" class="px-3 py-1 rounded text-sm border" :class="link.active?'bg-blue-600 text-white border-blue-600':'bg-white text-gray-600'"/>
      </div>
    </div>
  </div>
  </AppLayout>
</template>
<script setup>
const fmtFecha = (f) => { if (!f) return "—"; const d = new Date(f); return d.toLocaleDateString("es-PE",{year:"numeric",month:"short",day:"numeric"}) }
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
const props = defineProps({ recetas: Object })
const eliminar = (id) => { if (!confirm('Eliminar?')) return; router.delete(`/optica/recetas/${id}`) }
</script>
