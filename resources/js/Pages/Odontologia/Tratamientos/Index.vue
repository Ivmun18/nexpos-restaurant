<template>
  <AppLayout>
    <div style="display:flex;height:calc(100vh - 60px);">

      <!-- LISTA IZQUIERDA -->
      <div style="width:55%;border-right:1px solid #e2e8f0;display:flex;flex-direction:column;">
        <div style="padding:20px 24px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;gap:12px;">
          <div>
            <h1 style="font-size:18px;font-weight:600;color:#1e293b;margin:0;">Catálogo de tratamientos</h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0;">{{ tratamientos.length }} tratamientos registrados</p>
          </div>
          <button @click="abrirNuevo" style="margin-left:auto;padding:8px 18px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;">+ Nuevo</button>
        </div>

        <!-- Filtro categoría -->
        <div style="padding:12px 24px;border-bottom:1px solid #e2e8f0;display:flex;gap:8px;flex-wrap:wrap;">
          <button @click="filtro=''" :style="filtroBtnStyle(filtro==='')" style="padding:4px 14px;border-radius:20px;font-size:12px;cursor:pointer;border:1px solid #e2e8f0;">Todos</button>
          <button v-for="cat in categorias" :key="cat" @click="filtro=cat" :style="filtroBtnStyle(filtro===cat)" style="padding:4px 14px;border-radius:20px;font-size:12px;cursor:pointer;border:1px solid #e2e8f0;">{{ cat }}</button>
        </div>

        <!-- Tabla -->
        <div style="flex:1;overflow-y:auto;">
          <table style="width:100%;border-collapse:collapse;">
            <thead style="position:sticky;top:0;background:#f8fafc;">
              <tr>
                <th style="padding:10px 24px;text-align:left;font-size:11px;color:#94a3b8;font-weight:500;">Tratamiento</th>
                <th style="padding:10px 12px;text-align:left;font-size:11px;color:#94a3b8;font-weight:500;">Categoría</th>
                <th style="padding:10px 12px;text-align:right;font-size:11px;color:#94a3b8;font-weight:500;">Precio S/</th>
                <th style="padding:10px 12px;text-align:center;font-size:11px;color:#94a3b8;font-weight:500;">Min.</th>
                <th style="padding:10px 12px;text-align:center;font-size:11px;color:#94a3b8;font-weight:500;">Estado</th>
                <th style="padding:10px 12px;"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="listafiltrada.length===0">
                <td colspan="6" style="padding:40px;text-align:center;color:#94a3b8;font-size:13px;">No hay tratamientos registrados</td>
              </tr>
              <tr v-for="t in listafiltrada" :key="t.id"
                  @click="seleccionar(t)"
                  :style="{background: seleccionado?.id===t.id ? '#f5f3ff' : 'white', cursor:'pointer'}"
                  style="border-bottom:1px solid #f1f5f9;transition:background .15s;">
                <td style="padding:12px 24px;">
                  <div style="font-size:13px;font-weight:500;color:#1e293b;">{{ t.nombre }}</div>
                  <div v-if="t.codigo" style="font-size:11px;color:#94a3b8;">{{ t.codigo }}</div>
                </td>
                <td style="padding:12px;">
                  <span :style="categoriaBadge(t.categoria)" style="font-size:11px;padding:2px 10px;border-radius:20px;font-weight:500;">{{ t.categoria }}</span>
                </td>
                <td style="padding:12px;text-align:right;font-size:13px;font-weight:500;color:#8B5CF6;">{{ formatPrecio(t.precio) }}</td>
                <td style="padding:12px;text-align:center;font-size:12px;color:#64748b;">{{ t.duracion_minutos }}'</td>
                <td style="padding:12px;text-align:center;">
                  <span :style="{background:t.activo?'#dcfce7':'#fee2e2',color:t.activo?'#166534':'#991b1b'}" style="font-size:11px;padding:2px 10px;border-radius:20px;">{{ t.activo?'Activo':'Inactivo' }}</span>
                </td>
                <td style="padding:12px;text-align:center;">
                  <button @click.stop="confirmarEliminar(t)" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:12px;">✕</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- PANEL DERECHO: FORMULARIO -->
      <div style="width:45%;display:flex;flex-direction:column;">
        <div style="padding:20px 24px;border-bottom:1px solid #e2e8f0;">
          <h2 style="font-size:15px;font-weight:600;color:#1e293b;margin:0;">{{ modoEdicion ? 'Editar tratamiento' : (seleccionado ? 'Detalle' : 'Nuevo tratamiento') }}</h2>
        </div>

        <div style="flex:1;overflow-y:auto;padding:24px;">

          <!-- ESTADO VACÍO -->
          <div v-if="!modoEdicion && !seleccionado" style="text-align:center;padding:60px 20px;color:#94a3b8;">
            <div style="font-size:40px;margin-bottom:12px;">🦷</div>
            <div style="font-size:14px;">Selecciona un tratamiento<br>o crea uno nuevo</div>
          </div>

          <!-- DETALLE (solo lectura) -->
          <div v-else-if="!modoEdicion && seleccionado">
            <div style="margin-bottom:20px;">
              <div style="font-size:20px;font-weight:600;color:#1e293b;">{{ seleccionado.nombre }}</div>
              <div v-if="seleccionado.codigo" style="font-size:12px;color:#94a3b8;margin-top:2px;">Código: {{ seleccionado.codigo }}</div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px;">
              <div style="background:#f8fafc;border-radius:8px;padding:14px;">
                <div style="font-size:11px;color:#94a3b8;margin-bottom:4px;">Precio</div>
                <div style="font-size:22px;font-weight:600;color:#8B5CF6;">S/ {{ formatPrecio(seleccionado.precio) }}</div>
              </div>
              <div style="background:#f8fafc;border-radius:8px;padding:14px;">
                <div style="font-size:11px;color:#94a3b8;margin-bottom:4px;">Duración</div>
                <div style="font-size:22px;font-weight:600;color:#1e293b;">{{ seleccionado.duracion_minutos }}<span style="font-size:14px;font-weight:400;color:#64748b;"> min</span></div>
              </div>
            </div>
            <div style="margin-bottom:14px;">
              <div style="font-size:11px;color:#94a3b8;margin-bottom:4px;">Categoría</div>
              <span :style="categoriaBadge(seleccionado.categoria)" style="font-size:12px;padding:3px 12px;border-radius:20px;font-weight:500;">{{ seleccionado.categoria }}</span>
            </div>
            <div v-if="seleccionado.descripcion" style="margin-bottom:20px;">
              <div style="font-size:11px;color:#94a3b8;margin-bottom:4px;">Descripción</div>
              <div style="font-size:13px;color:#475569;line-height:1.6;">{{ seleccionado.descripcion }}</div>
            </div>
            <div style="display:flex;gap:10px;">
              <button @click="abrirEditar(seleccionado)" style="flex:1;padding:10px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;">Editar</button>
              <button @click="seleccionado=null" style="padding:10px 18px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;cursor:pointer;background:#fff;">Cerrar</button>
            </div>
          </div>

          <!-- FORMULARIO CREAR / EDITAR -->
          <form v-else @submit.prevent="guardar" style="display:flex;flex-direction:column;gap:16px;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
              <div style="grid-column:1/-1;">
                <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Nombre del tratamiento *</label>
                <input v-model="form.nombre" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="Ej: Extracción simple" />
              </div>
              <div>
                <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Código (opcional)</label>
                <input v-model="form.codigo" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="Ej: EXT-01" />
              </div>
              <div>
                <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Categoría *</label>
                <input v-model="form.categoria" list="cats" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="Ej: Cirugía" />
                <datalist id="cats">
                  <option v-for="cat in categorias" :key="cat" :value="cat"/>
                  <option value="Preventivo"/>
                  <option value="Restaurativo"/>
                  <option value="Cirugía"/>
                  <option value="Ortodoncia"/>
                  <option value="Endodoncia"/>
                  <option value="Prótesis"/>
                  <option value="Estética"/>
                  <option value="Periodoncia"/>
                </datalist>
              </div>
              <div>
                <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Precio S/ *</label>
                <input v-model.number="form.precio" type="number" step="0.01" min="0" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="0.00" />
              </div>
              <div>
                <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Duración (minutos) *</label>
                <input v-model.number="form.duracion_minutos" type="number" min="5" required style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="30" />
              </div>
              <div v-if="modoEdicion">
                <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Estado</label>
                <select v-model="form.activo" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;">
                  <option :value="true">Activo</option>
                  <option :value="false">Inactivo</option>
                </select>
              </div>
              <div style="grid-column:1/-1;">
                <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Descripción</label>
                <textarea v-model="form.descripcion" rows="3" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;resize:vertical;" placeholder="Descripción del procedimiento..."></textarea>
              </div>
            </div>
            <div style="display:flex;gap:10px;padding-top:4px;">
              <button type="submit" style="flex:1;padding:10px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;">{{ modoEdicion ? 'Actualizar' : 'Guardar tratamiento' }}</button>
              <button type="button" @click="cancelar" style="padding:10px 18px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;cursor:pointer;background:#fff;">Cancelar</button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ tratamientos: Array, categorias: Array })

const seleccionado = ref(null)
const modoEdicion  = ref(false)
const filtro       = ref('')
const formVacio    = { codigo:'', nombre:'', categoria:'', descripcion:'', precio:0, duracion_minutos:30, activo:true }
const form         = ref({ ...formVacio })

const listafiltrada = computed(() =>
  filtro.value ? props.tratamientos.filter(t => t.categoria === filtro.value) : props.tratamientos
)

const formatPrecio = (v) => Number(v).toFixed(2)

const colores = {
  'Preventivo':   { background:'#dbeafe', color:'#1e40af' },
  'Restaurativo': { background:'#dcfce7', color:'#166534' },
  'Cirugía':      { background:'#fee2e2', color:'#991b1b' },
  'Ortodoncia':   { background:'#fef9c3', color:'#854d0e' },
  'Endodoncia':   { background:'#ffedd5', color:'#9a3412' },
  'Prótesis':     { background:'#f3e8ff', color:'#6b21a8' },
  'Estética':     { background:'#fce7f3', color:'#9d174d' },
  'Periodoncia':  { background:'#e0f2fe', color:'#075985' },
}
const categoriaBadge = (cat) => colores[cat] || { background:'#f1f5f9', color:'#475569' }
const filtroBtnStyle = (activo) => activo
  ? { background:'#8B5CF6', color:'#fff', borderColor:'#8B5CF6' }
  : { background:'#fff',    color:'#64748b' }

const seleccionar  = (t) => { seleccionado.value = t; modoEdicion.value = false }
const abrirNuevo   = () => { seleccionado.value = null; form.value = { ...formVacio }; modoEdicion.value = true }
const abrirEditar  = (t) => { form.value = { ...t }; modoEdicion.value = true }
const cancelar     = () => { modoEdicion.value = false; form.value = { ...formVacio } }

const guardar = () => {
  if (seleccionado.value && form.value.id) {
    router.put(`/odontologia/tratamientos/${form.value.id}`, form.value, {
      onSuccess: () => { modoEdicion.value = false }
    })
  } else {
    router.post('/odontologia/tratamientos', form.value, {
      onSuccess: () => { modoEdicion.value = false; form.value = { ...formVacio } }
    })
  }
}

const confirmarEliminar = (t) => {
  if (confirm(`¿Eliminar "${t.nombre}"?`)) {
    router.delete(`/odontologia/tratamientos/${t.id}`)
  }
}
</script>
