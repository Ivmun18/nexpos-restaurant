<template>
  <AppLayout>
    <div style="padding:28px 32px;max-width:1100px;margin:0 auto;">

      <!-- HEADER -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <div>
          <h1 style="font-size:20px;font-weight:600;color:#1e293b;margin:0;">Galería antes / después</h1>
          <p style="font-size:13px;color:#64748b;margin:4px 0 0;">{{ galeria.length }} casos registrados</p>
        </div>
        <button @click="modalAgregar=true" style="padding:9px 20px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;">+ Agregar caso</button>
      </div>

      <!-- GRID GALERÍA -->
      <div v-if="galeria.length===0" style="text-align:center;padding:60px;color:#94a3b8;">
        <div style="font-size:48px;margin-bottom:12px;">📸</div>
        <div style="font-size:15px;font-weight:500;margin-bottom:6px;">Sin casos en la galería</div>
        <div style="font-size:13px;">Agrega fotos antes/después de tus tratamientos</div>
      </div>

      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;">
        <div v-for="item in galeria" :key="item.id"
          style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;">

          <!-- SLIDER ANTES/DESPUÉS -->
          <div style="position:relative;height:220px;overflow:hidden;cursor:col-resize;user-select:none;"
            @mousedown="startDrag($event, item.id)"
            @touchstart="startDragTouch($event, item.id)">

            <!-- Foto después (fondo completo) -->
            <img :src="'/storage/'+item.foto_despues" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;" />

            <!-- Foto antes (clip dinámico) -->
            <div :style="{width: (sliderPos[item.id] ?? 50)+'%'}"
              style="position:absolute;inset:0;overflow:hidden;height:100%;">
              <img :src="'/storage/'+item.foto_antes" style="width:100%;height:100%;object-fit:cover;min-width:320px;" />
            </div>

            <!-- Línea divisora -->
            <div :style="{left: (sliderPos[item.id] ?? 50)+'%'}"
              style="position:absolute;top:0;bottom:0;width:3px;background:#fff;box-shadow:0 0 6px rgba(0,0,0,.4);cursor:col-resize;">
              <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:32px;height:32px;background:#fff;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;font-size:14px;">⟺</div>
            </div>

            <!-- Labels -->
            <div style="position:absolute;top:8px;left:8px;background:rgba(0,0,0,.6);color:#fff;font-size:11px;font-weight:600;padding:3px 8px;border-radius:20px;">ANTES</div>
            <div style="position:absolute;top:8px;right:8px;background:rgba(67,56,202,.8);color:#fff;font-size:11px;font-weight:600;padding:3px 8px;border-radius:20px;">DESPUÉS</div>

            <!-- Badge público -->
            <div v-if="item.publica" style="position:absolute;bottom:8px;right:8px;background:#10b981;color:#fff;font-size:10px;padding:2px 8px;border-radius:20px;">🌐 Público</div>
          </div>

          <!-- INFO -->
          <div style="padding:14px 16px;">
            <div style="font-size:14px;font-weight:500;color:#1e293b;margin-bottom:4px;">{{ item.titulo || item.tratamiento || 'Sin título' }}</div>
            <div v-if="item.tratamiento" style="font-size:12px;color:#8B5CF6;margin-bottom:6px;">{{ item.tratamiento }}</div>
            <div v-if="item.descripcion" style="font-size:12px;color:#64748b;margin-bottom:10px;line-height:1.5;">{{ item.descripcion }}</div>
            <div style="display:flex;gap:8px;">
              <button @click="togglePublica(item.id)"
                :style="{background: item.publica ? '#fef3c7' : '#f0fdf4', color: item.publica ? '#92400e' : '#166534'}"
                style="flex:1;padding:6px;border:none;border-radius:6px;font-size:11px;font-weight:500;cursor:pointer;">
                {{ item.publica ? '🔒 Ocultar' : '🌐 Publicar' }}
              </button>
              <button @click="confirmarEliminar(item.id)"
                style="padding:6px 12px;background:#fee2e2;color:#991b1b;border:none;border-radius:6px;font-size:11px;cursor:pointer;">Eliminar</button>
            </div>
          </div>
        </div>
      </div>

      <!-- MODAL AGREGAR -->
      <div v-if="modalAgregar" style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:1000;padding:20px;">
        <div style="background:#fff;border-radius:16px;padding:28px;width:500px;max-width:95vw;max-height:90vh;overflow-y:auto;">
          <div style="display:flex;justify-content:space-between;margin-bottom:20px;">
            <h2 style="margin:0;font-size:18px;font-weight:600;">Agregar caso</h2>
            <button @click="cerrarModal" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
          </div>

          <div style="display:flex;flex-direction:column;gap:14px;">
            <div>
              <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Título</label>
              <input v-model="form.titulo" style="width:100%;padding:9px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="Ej: Blanqueamiento exitoso" />
            </div>
            <div>
              <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Tratamiento</label>
              <input v-model="form.tratamiento" style="width:100%;padding:9px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="Ej: Blanqueamiento, Ortodoncia..." />
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
              <div>
                <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">📷 Foto ANTES *</label>
                <input type="file" ref="inputAntes" accept="image/*" @change="onAntes" style="font-size:12px;width:100%;" />
                <img v-if="previewAntes" :src="previewAntes" style="width:100%;height:80px;object-fit:cover;border-radius:6px;margin-top:6px;" />
              </div>
              <div>
                <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">📷 Foto DESPUÉS *</label>
                <input type="file" ref="inputDespues" accept="image/*" @change="onDespues" style="font-size:12px;width:100%;" />
                <img v-if="previewDespues" :src="previewDespues" style="width:100%;height:80px;object-fit:cover;border-radius:6px;margin-top:6px;" />
              </div>
            </div>
            <div>
              <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Descripción</label>
              <textarea v-model="form.descripcion" rows="2" style="width:100%;padding:9px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;resize:vertical;"></textarea>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <input type="checkbox" v-model="form.publica" id="publica" />
              <label for="publica" style="font-size:13px;color:#374151;cursor:pointer;">Mostrar en portal público</label>
            </div>
            <button @click="guardar" :disabled="!form.fotoAntes || !form.fotoDespues"
              style="padding:10px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;"
              :style="{opacity: (!form.fotoAntes||!form.fotoDespues) ? 0.4 : 1}">
              Guardar caso
            </button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ galeria: Array })

const modalAgregar  = ref(false)
const previewAntes  = ref(null)
const previewDespues = ref(null)
const inputAntes    = ref(null)
const inputDespues  = ref(null)
const sliderPos     = reactive({})

const form = ref({ titulo:'', tratamiento:'', descripcion:'', publica:false, fotoAntes:null, fotoDespues:null })

const onAntes = (e) => {
  form.value.fotoAntes = e.target.files[0]
  previewAntes.value = URL.createObjectURL(e.target.files[0])
}
const onDespues = (e) => {
  form.value.fotoDespues = e.target.files[0]
  previewDespues.value = URL.createObjectURL(e.target.files[0])
}

const cerrarModal = () => {
  modalAgregar.value = false
  form.value = { titulo:'', tratamiento:'', descripcion:'', publica:false, fotoAntes:null, fotoDespues:null }
  previewAntes.value = null; previewDespues.value = null
}

const guardar = () => {
  const data = new FormData()
  data.append('foto_antes', form.value.fotoAntes)
  data.append('foto_despues', form.value.fotoDespues)
  data.append('titulo', form.value.titulo)
  data.append('tratamiento', form.value.tratamiento)
  data.append('descripcion', form.value.descripcion)
  data.append('publica', form.value.publica ? '1' : '0')
  router.post('/odontologia/galeria', data, { onSuccess: cerrarModal })
}

const togglePublica = (id) => router.patch(`/odontologia/galeria/${id}/publica`)
const confirmarEliminar = (id) => { if(confirm('¿Eliminar este caso?')) router.delete(`/odontologia/galeria/${id}`) }

// Slider drag
const startDrag = (e, id) => {
  const rect = e.currentTarget.getBoundingClientRect()
  const move = (ev) => { sliderPos[id] = Math.min(95, Math.max(5, ((ev.clientX - rect.left) / rect.width) * 100)) }
  const up   = () => { document.removeEventListener('mousemove', move); document.removeEventListener('mouseup', up) }
  document.addEventListener('mousemove', move)
  document.addEventListener('mouseup', up)
}
const startDragTouch = (e, id) => {
  const rect = e.currentTarget.getBoundingClientRect()
  const move = (ev) => { const t = ev.touches[0]; sliderPos[id] = Math.min(95, Math.max(5, ((t.clientX - rect.left) / rect.width) * 100)) }
  const up   = () => { document.removeEventListener('touchmove', move); document.removeEventListener('touchend', up) }
  document.addEventListener('touchmove', move)
  document.addEventListener('touchend', up)
}
</script>
