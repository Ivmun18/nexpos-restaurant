<template>
  <AppLayout title="Paciente">
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-6">
      <div class="flex items-center gap-4 mb-6">
        <a href="/optica/pacientes" class="text-gray-400 hover:text-gray-600 text-xl">←</a>
        <div class="flex-1">
          <h1 class="text-2xl font-bold text-gray-800">{{ paciente.nombre }} {{ paciente.apellidos }}</h1>
          <p class="text-sm text-gray-400">DNI: {{ paciente.dni || '—' }} | Tel: {{ paciente.telefono || '—' }}</p>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex gap-1 mb-4 border-b">
        <button v-for="t in tabs" :key="t.id" @click="tab=t.id" class="px-4 py-2 text-sm font-medium border-b-2 transition" :class="tab===t.id ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-500 hover:text-gray-700'">{{ t.label }}</button>
      </div>

      <!-- TAB: Historial -->
      <div v-if="tab==='historial'">
        <div class="flex justify-between items-center mb-4">
          <h2 class="font-bold text-blue-800 text-lg">🏥 Historial Clínico</h2>
          <button @click="abrirNuevaHC" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-semibold">+ Nueva Historia</button>
        </div>
        <div v-if="historial.length===0" class="bg-white rounded-xl shadow p-8 text-center text-gray-400">Sin registros clínicos</div>
        <div v-else class="space-y-3">
          <div v-for="h in historial" :key="h.id" class="bg-white rounded-xl shadow p-4">
            <div class="flex justify-between items-start mb-2">
              <div>
                <span class="font-mono text-blue-600 text-sm font-bold">{{ h.numero_historia }}</span>
                <span class="text-sm text-gray-600 ml-2">{{ fmtFecha(h.fecha) }}</span>
                <span v-if="h.doctor" class="text-xs text-gray-400 ml-2">{{ h.doctor.nombre }}</span>
                <span v-if="h.pronostico" class="ml-2 px-2 py-0.5 rounded-full text-xs" :class="{'bg-green-100 text-green-700':h.pronostico==='bueno','bg-yellow-100 text-yellow-700':h.pronostico==='reservado','bg-red-100 text-red-700':h.pronostico==='malo'}">{{ h.pronostico }}</span>
              </div>
              <div class="flex gap-2">
                <button @click="editarHC(h)" class="text-yellow-600 hover:underline text-xs">Editar</button>
                <button @click="eliminarHC(h.id)" class="text-red-500 hover:underline text-xs">Eliminar</button>
              </div>
            </div>
            <div class="grid md:grid-cols-2 gap-2 text-sm">
              <div v-if="h.motivo_consulta"><span class="text-gray-400 text-xs">Motivo:</span><br/>{{ h.motivo_consulta }}</div>
              <div v-if="h.diagnostico"><span class="text-gray-400 text-xs">Diagnóstico:</span><br/>{{ h.diagnostico }}</div>
              <div v-if="h.tratamiento"><span class="text-gray-400 text-xs">Tratamiento:</span><br/>{{ h.tratamiento }}</div>
              <div v-if="h.indicaciones"><span class="text-gray-400 text-xs">Indicaciones:</span><br/>{{ h.indicaciones }}</div>
            </div>
            <div v-if="h.datos_clinicos && h.datos_clinicos.sintomas" class="mt-2">
              <span class="text-gray-400 text-xs">Síntomas: </span>
              <span v-for="s in h.datos_clinicos.sintomas" :key="s" class="bg-red-50 text-red-600 px-1.5 py-0.5 rounded text-xs mr-1">{{ s }}</span>
            </div>
            <div v-if="h.proxima_cita" class="mt-2"><span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full text-xs">Próxima: {{ fmtFecha(h.proxima_cita) }}</span></div>
          </div>
        </div>
      </div>

      <!-- TAB: Fichas -->
      <div v-if="tab==='fichas'">
        <h2 class="font-bold text-purple-700 mb-3">👁️ Fichas Oftalmológicas ({{ fichas.length }})</h2>
        <div v-if="fichas.length===0" class="bg-white rounded-xl shadow p-8 text-center text-gray-400">Sin fichas</div>
        <div v-else class="bg-white rounded-xl shadow overflow-hidden">
          <table class="w-full text-sm">
            <thead class="text-xs text-gray-500 border-b"><tr><th class="px-4 py-2 text-left">Fecha</th><th class="px-4 py-2 text-center">OD Esf.</th><th class="px-4 py-2 text-center">OI Esf.</th><th class="px-4 py-2 text-center">DIV</th><th class="px-4 py-2 text-center">PDF</th></tr></thead>
            <tbody>
              <tr v-for="f in fichas" :key="f.id" class="border-b last:border-0 hover:bg-gray-50">
                <td class="px-4 py-2 text-gray-600">{{ fmtFecha(f.fecha) }}</td>
                <td class="px-4 py-2 text-center font-mono text-blue-700">{{ f.od_esfera }}</td>
                <td class="px-4 py-2 text-center font-mono text-green-700">{{ f.oi_esfera }}</td>
                <td class="px-4 py-2 text-center">{{ f.div || '—' }}</td>
                <td class="px-4 py-2 text-center"><a :href="`/optica/fichas/${f.id}/pdf`" target="_blank" class="text-purple-600 hover:underline text-xs">PDF</a></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB: Recetas -->
      <div v-if="tab==='recetas'">
        <h2 class="font-bold text-blue-700 mb-3">📋 Recetas ({{ recetas.length }})</h2>
        <div v-if="recetas.length===0" class="bg-white rounded-xl shadow p-8 text-center text-gray-400">Sin recetas</div>
        <div v-else class="space-y-2">
          <div v-for="r in recetas" :key="r.id" class="bg-white rounded-xl shadow p-3 flex justify-between items-center">
            <div>
              <span class="font-mono text-blue-600 text-sm font-bold">{{ r.numero_receta }}</span>
              <span class="text-gray-500 text-xs ml-2">{{ fmtFecha(r.fecha) }}</span>
              <span class="ml-2 px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">{{ r.tipo }}</span>
            </div>
            <a :href="`/optica/recetas/${r.id}/pdf`" target="_blank" class="text-purple-600 hover:underline text-xs">PDF</a>
          </div>
        </div>
      </div>

      <!-- TAB: Compras -->
      <div v-if="tab==='compras'">
        <h2 class="font-bold text-green-700 mb-3">🛒 Compras ({{ ventas.length }})</h2>
        <div v-if="ventas.length===0" class="bg-white rounded-xl shadow p-8 text-center text-gray-400">Sin compras</div>
        <div v-else class="bg-white rounded-xl shadow overflow-hidden">
          <table class="w-full text-sm">
            <thead class="text-xs text-gray-500 border-b"><tr><th class="px-4 py-2 text-left">N°</th><th class="px-4 py-2 text-left">Fecha</th><th class="px-4 py-2 text-right">Total</th></tr></thead>
            <tbody>
              <tr v-for="v in ventas" :key="v.id" class="border-b last:border-0">
                <td class="px-4 py-2 font-mono text-blue-600">{{ v.numero_venta }}</td>
                <td class="px-4 py-2 text-gray-600">{{ fmtFecha(v.fecha) }}</td>
                <td class="px-4 py-2 text-right font-semibold">S/ {{ parseFloat(v.total).toFixed(2) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal HC -->
    <div v-if="showHCModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl p-6 max-h-[90vh] overflow-y-auto">
        <h2 class="text-lg font-bold text-blue-800 mb-4">{{ editandoHC ? 'Editar' : 'Nueva' }} Historia Clínica Oftalmológica</h2>

        <!-- Sección I: General -->
        <div class="grid grid-cols-2 gap-3 mb-4">
          <div><label class="text-xs text-gray-500">Doctor</label>
            <select v-model="hc.doctor_id" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"><option value="">— Seleccionar —</option><option v-for="d in doctores" :key="d.id" :value="d.id">{{ d.nombre }}</option></select></div>
          <div><label class="text-xs text-gray-500">Fecha *</label><input v-model="hc.fecha" type="date" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/></div>
        </div>

        <!-- II: Motivo -->
        <div class="mb-4"><label class="text-xs text-gray-500 font-semibold">II. Motivo de Consulta</label>
          <textarea v-model="hc.motivo_consulta" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"></textarea></div>

        <!-- III: Enfermedad actual / Síntomas -->
        <div class="mb-4">
          <label class="text-xs text-gray-500 font-semibold">III. Síntomas</label>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-1 mt-1">
            <label v-for="s in opcionesSintomas" :key="s" class="flex items-center gap-1 text-sm text-gray-700">
              <input type="checkbox" :value="s" v-model="hc.datos_clinicos.sintomas" class="w-3.5 h-3.5"/>{{ s }}
            </label>
          </div>
          <input v-model="hc.datos_clinicos.sintomas_otros" placeholder="Otros síntomas..." class="w-full border rounded-lg px-3 py-2 text-sm mt-2"/>
          <div class="grid grid-cols-2 gap-3 mt-2">
            <div><label class="text-xs text-gray-400">Tiempo evolución</label><input v-model="hc.datos_clinicos.tiempo_evolucion" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/></div>
            <div><label class="text-xs text-gray-400">Inicio</label>
              <select v-model="hc.datos_clinicos.inicio" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"><option value="">—</option><option>Súbito</option><option>Gradual</option></select></div>
          </div>
        </div>

        <!-- IV: Antecedentes -->
        <div class="mb-4">
          <label class="text-xs text-gray-500 font-semibold">IV. Antecedentes Personales</label>
          <div class="grid grid-cols-2 gap-2 mt-1">
            <label v-for="a in opcionesAntPersonales" :key="a" class="flex items-center gap-1 text-sm text-gray-700">
              <input type="checkbox" :value="a" v-model="hc.datos_clinicos.ant_personales" class="w-3.5 h-3.5"/>{{ a }}
            </label>
          </div>
          <div class="grid grid-cols-2 gap-3 mt-2">
            <div><label class="text-xs text-gray-400">Uso lentes</label>
              <select v-model="hc.datos_clinicos.uso_lentes" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"><option value="">—</option><option>No</option><option>Cerca</option><option>Lejos</option><option>Bifocal</option><option>Progresivo</option></select></div>
            <div><label class="text-xs text-gray-400">Otros antecedentes</label><input v-model="hc.datos_clinicos.ant_otros" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/></div>
          </div>
        </div>

        <!-- V: Antecedentes Familiares -->
        <div class="mb-4">
          <label class="text-xs text-gray-500 font-semibold">V. Antecedentes Familiares</label>
          <div class="grid grid-cols-2 gap-2 mt-1">
            <label v-for="a in opcionesAntFam" :key="a" class="flex items-center gap-1 text-sm text-gray-700">
              <input type="checkbox" :value="a" v-model="hc.datos_clinicos.ant_familiares" class="w-3.5 h-3.5"/>{{ a }}
            </label>
          </div>
        </div>

        <!-- VI: Examen - AV y Refracción -->
        <div class="mb-4">
          <label class="text-xs text-gray-500 font-semibold">VI. Examen Oftalmológico</label>
          <table class="w-full text-sm mt-1 border rounded-lg overflow-hidden">
            <thead class="bg-gray-100 text-xs text-gray-500"><tr><th class="py-1 px-2">Ojo</th><th class="py-1 px-2">AV s/c</th><th class="py-1 px-2">AV c/c</th><th class="py-1 px-2">Esfera</th><th class="py-1 px-2">Cilindro</th><th class="py-1 px-2">Eje</th><th class="py-1 px-2">PIO</th></tr></thead>
            <tbody>
              <tr><td class="px-2 py-1 font-bold text-blue-700 text-center">OD</td>
                <td class="px-1 py-1"><input v-model="hc.datos_clinicos.od_av_sc" class="w-full border rounded px-2 py-1 text-xs text-center"/></td>
                <td class="px-1 py-1"><input v-model="hc.datos_clinicos.od_av_cc" class="w-full border rounded px-2 py-1 text-xs text-center"/></td>
                <td class="px-1 py-1"><input v-model="hc.datos_clinicos.od_esfera" type="number" step="0.25" class="w-full border rounded px-2 py-1 text-xs text-center"/></td>
                <td class="px-1 py-1"><input v-model="hc.datos_clinicos.od_cilindro" type="number" step="0.25" class="w-full border rounded px-2 py-1 text-xs text-center"/></td>
                <td class="px-1 py-1"><input v-model="hc.datos_clinicos.od_eje" type="number" class="w-full border rounded px-2 py-1 text-xs text-center"/></td>
                <td class="px-1 py-1"><input v-model="hc.datos_clinicos.od_pio" class="w-full border rounded px-2 py-1 text-xs text-center"/></td>
              </tr>
              <tr><td class="px-2 py-1 font-bold text-green-700 text-center">OI</td>
                <td class="px-1 py-1"><input v-model="hc.datos_clinicos.oi_av_sc" class="w-full border rounded px-2 py-1 text-xs text-center"/></td>
                <td class="px-1 py-1"><input v-model="hc.datos_clinicos.oi_av_cc" class="w-full border rounded px-2 py-1 text-xs text-center"/></td>
                <td class="px-1 py-1"><input v-model="hc.datos_clinicos.oi_esfera" type="number" step="0.25" class="w-full border rounded px-2 py-1 text-xs text-center"/></td>
                <td class="px-1 py-1"><input v-model="hc.datos_clinicos.oi_cilindro" type="number" step="0.25" class="w-full border rounded px-2 py-1 text-xs text-center"/></td>
                <td class="px-1 py-1"><input v-model="hc.datos_clinicos.oi_eje" type="number" class="w-full border rounded px-2 py-1 text-xs text-center"/></td>
                <td class="px-1 py-1"><input v-model="hc.datos_clinicos.oi_pio" class="w-full border rounded px-2 py-1 text-xs text-center"/></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- IX-XII: Diagnóstico, Tratamiento, etc -->
        <div class="space-y-3 mb-4">
          <div><label class="text-xs text-gray-500 font-semibold">IX. Diagnóstico</label><textarea v-model="hc.diagnostico" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"></textarea></div>
          <div><label class="text-xs text-gray-500 font-semibold">X. Tratamiento</label><textarea v-model="hc.tratamiento" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"></textarea></div>
          <div><label class="text-xs text-gray-500 font-semibold">XI. Indicaciones</label><textarea v-model="hc.indicaciones" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"></textarea></div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="text-xs text-gray-500 font-semibold">XII. Pronóstico</label>
              <select v-model="hc.pronostico" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"><option value="">—</option><option value="bueno">Bueno</option><option value="reservado">Reservado</option><option value="malo">Malo</option></select></div>
            <div><label class="text-xs text-gray-500">Próxima cita</label><input v-model="hc.proxima_cita" type="date" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"/></div>
          </div>
          <div><label class="text-xs text-gray-500">Observaciones</label><textarea v-model="hc.observaciones" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm mt-1"></textarea></div>
        </div>

        <div class="flex justify-end gap-2">
          <button @click="showHCModal=false" class="px-4 py-2 border rounded-lg text-sm text-gray-600">Cancelar</button>
          <button @click="guardarHC" :disabled="guardandoHC" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold disabled:opacity-50">{{ guardandoHC ? 'Guardando...' : 'Guardar' }}</button>
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

const props = defineProps({ paciente: Object, fichas: Array, recetas: Array, ventas: Array, historial: Array, doctores: Array })

const tab = ref('historial')
const tabs = [{ id:'historial', label:'Historial' },{ id:'fichas', label:'Fichas' },{ id:'recetas', label:'Recetas' },{ id:'compras', label:'Compras' }]

const showHCModal = ref(false)
const editandoHC = ref(null)
const guardandoHC = ref(false)

const opcionesSintomas = ['Disminución de visión','Ojo rojo','Dolor ocular','Lagrimeo','Secreción ocular','Cuerpo extraño','Fotofobia','Visión borrosa','Visión doble']
const opcionesAntPersonales = ['Cirugía ocular previa','Traumatismo ocular','Glaucoma','Catarata','Diabetes','Hipertensión','Enf. tiroidea','Alergias']
const opcionesAntFam = ['Glaucoma','Catarata','Retinopatía diabética','Ceguera hereditaria']

const datosVacios = () => ({ sintomas:[], sintomas_otros:'', tiempo_evolucion:'', inicio:'', ant_personales:[], uso_lentes:'', ant_otros:'', ant_familiares:[], od_av_sc:'', od_av_cc:'', od_esfera:'', od_cilindro:'', od_eje:'', od_pio:'', oi_av_sc:'', oi_av_cc:'', oi_esfera:'', oi_cilindro:'', oi_eje:'', oi_pio:'' })

const hc = reactive({ doctor_id:'', fecha: new Date().toISOString().slice(0,10), motivo_consulta:'', datos_clinicos: datosVacios(), diagnostico:'', tratamiento:'', indicaciones:'', pronostico:'', observaciones:'', proxima_cita:'' })

const abrirNuevaHC = () => { editandoHC.value = null; Object.assign(hc, { doctor_id:'', fecha: new Date().toISOString().slice(0,10), motivo_consulta:'', datos_clinicos: datosVacios(), diagnostico:'', tratamiento:'', indicaciones:'', pronostico:'', observaciones:'', proxima_cita:'' }); showHCModal.value = true }

const editarHC = (h) => {
  editandoHC.value = h.id
  Object.assign(hc, { doctor_id: h.doctor_id||'', fecha: h.fecha, motivo_consulta: h.motivo_consulta||'', datos_clinicos: h.datos_clinicos ? { ...datosVacios(), ...h.datos_clinicos } : datosVacios(), diagnostico: h.diagnostico||'', tratamiento: h.tratamiento||'', indicaciones: h.indicaciones||'', pronostico: h.pronostico||'', observaciones: h.observaciones||'', proxima_cita: h.proxima_cita||'' })
  showHCModal.value = true
}

const guardarHC = () => {
  guardandoHC.value = true
  const data = { ...hc, paciente_id: props.paciente.id }
  if (editandoHC.value) { router.put(`/optica/historial/${editandoHC.value}`, data, { onFinish: () => { guardandoHC.value = false; showHCModal.value = false } }) }
  else { router.post('/optica/historial', data, { onFinish: () => { guardandoHC.value = false; showHCModal.value = false } }) }
}
const fmtFecha = (f) => { if (!f) return '—'; return new Date(f).toLocaleDateString('es-PE', { year:'numeric', month:'short', day:'numeric' }) }

const eliminarHC = (id) => { if (!confirm('Eliminar?')) return; router.delete(`/optica/historial/${id}`) }
</script>
