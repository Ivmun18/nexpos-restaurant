<template>
  <AppLayout title="Ficha del paciente" subtitle="">
  <div style="padding:24px; max-width:1200px; margin:0 auto;">
    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <div style="display:flex; align-items:center; gap:12px;">
        <a href="/odontologia/pacientes" style="color:#64748B; text-decoration:none;">← Pacientes</a>
        <h1 style="font-size:22px; font-weight:700; margin:0;">{{ paciente.apellidos }}, {{ paciente.nombres }}</h1>
        <span style="background:#EDE9FE; color:#7C3AED; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:600;">{{ paciente.dni || 'Sin DNI' }}</span>
      </div>
      <a :href="`/odontologia/pacientes/${paciente.id}/editar`" style="padding:8px 16px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; text-decoration:none; color:#374151;">Editar</a>
      <a :href="`/odontologia/pacientes/${paciente.id}/ficha-pdf`" target="_blank" style="padding:8px 16px;background:#1e1b4b;color:#fff;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">📄 Ficha PDF</a>
    </div>

    <!-- Info básica -->
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:24px;">
      <div style="background:white; border:1px solid #E2E8F0; border-radius:10px; padding:16px;">
        <p style="font-size:11px; color:#64748B; margin:0 0 4px; font-weight:600;">TELÉFONO</p>
        <p style="font-size:14px; margin:0; font-weight:600;">{{ paciente.telefono || '-' }}</p>
      </div>
      <div style="background:white; border:1px solid #E2E8F0; border-radius:10px; padding:16px;">
        <p style="font-size:11px; color:#64748B; margin:0 0 4px; font-weight:600;">FECHA NAC.</p>
        <p style="font-size:14px; margin:0; font-weight:600;">{{ paciente.fecha_nacimiento || '-' }}</p>
      </div>
      <div style="background:white; border:1px solid #E2E8F0; border-radius:10px; padding:16px;">
        <p style="font-size:11px; color:#64748B; margin:0 0 4px; font-weight:600;">GRUPO SANGUÍNEO</p>
        <p style="font-size:14px; margin:0; font-weight:600;">{{ paciente.grupo_sanguineo || '-' }}</p>
      </div>
    </div>

    <!-- Tabs -->
    <div style="display:flex; gap:4px; margin-bottom:20px; border-bottom:1px solid #E2E8F0;">
      <button v-for="t in tabs" :key="t.key" @click="tabActivo=t.key"
        :style="tabActivo===t.key ? {borderBottom:'2px solid #8B5CF6',color:'#7C3AED',fontWeight:'700'} : {color:'#64748B'}"
        style="padding:10px 16px; background:none; border:none; font-size:14px; cursor:pointer; margin-bottom:-1px;">
        {{ t.label }}
      </button>
    </div>

    <!-- Tab: Citas -->
    <div v-if="tabActivo==='citas'">
      <div v-if="citas.length===0" style="text-align:center; padding:32px; color:#94A3B8;">Sin citas registradas</div>
      <div v-for="c in citas" :key="c.id" style="background:white; border:1px solid #E2E8F0; border-radius:10px; padding:16px; margin-bottom:10px; display:flex; justify-content:space-between; align-items:center;">
        <div>
          <p style="margin:0; font-weight:600; font-size:14px;">{{ formatFecha(c.fecha_hora) }}</p>
          <p style="margin:2px 0 0; font-size:12px; color:#64748B;">Dr. {{ c.doctor?.nombre }} · {{ c.motivo || 'Sin motivo' }}</p>
        </div>
        <span :style="estadoStyle(c.estado)">{{ c.estado }}</span>
      </div>
    </div>

    <!-- Tab: Historia clínica -->
    <div v-if="tabActivo==='historia'">
      <div style="background:white; border:1px solid #E2E8F0; border-radius:10px; padding:16px; margin-bottom:16px;">
        <p style="margin:0 0 10px; font-size:13px; font-weight:600;">Nueva entrada</p>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
          <select v-model="formHistoria.doctor_id" style="padding:8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px;">
            <option value="">Doctor...</option>
            <option v-for="d in doctores" :key="d.id" :value="d.id">{{ d.nombre }}</option>
          </select>
          <input v-model="formHistoria.fecha" type="date" style="padding:8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; box-sizing:border-box;" />
        </div>
        <textarea v-model="formHistoria.anamnesis" placeholder="Motivo / anamnesis" rows="2" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; box-sizing:border-box; margin-bottom:8px;"></textarea>
        <textarea v-model="formHistoria.diagnostico" placeholder="Diagnostico" rows="2" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; box-sizing:border-box; margin-bottom:8px;"></textarea>
        <textarea v-model="formHistoria.tratamiento_realizado" placeholder="Tratamiento realizado" rows="2" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; box-sizing:border-box; margin-bottom:8px;"></textarea>
        <textarea v-model="formHistoria.observaciones" placeholder="Observaciones" rows="2" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; box-sizing:border-box; margin-bottom:10px;"></textarea>
        <button @click="guardarHistoria" :disabled="guardandoHistoria" style="padding:9px 18px; background:#8B5CF6; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
          {{ guardandoHistoria ? 'Guardando...' : 'Guardar entrada' }}
        </button>
      </div>

      <div style="background:white; border:1px solid #E2E8F0; border-radius:10px; padding:16px; margin-bottom:16px;">
        <p style="margin:0 0 10px; font-size:13px; font-weight:600;">Nueva receta</p>
        <div v-for="(it,idx) in formReceta.items" :key="idx" style="border:1px solid #F1F5F9; border-radius:8px; padding:10px; margin-bottom:8px;">
          <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:8px; margin-bottom:6px;">
            <input v-model="it.medicamento" placeholder="Medicamento" style="padding:7px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; box-sizing:border-box;" />
            <input v-model="it.dosis" placeholder="Dosis" style="padding:7px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; box-sizing:border-box;" />
            <input v-model="it.frecuencia" placeholder="Frecuencia" style="padding:7px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; box-sizing:border-box;" />
            <input v-model="it.duracion" placeholder="Duracion" style="padding:7px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; box-sizing:border-box;" />
          </div>
          <div style="display:flex; gap:8px;">
            <input v-model="it.indicaciones" placeholder="Indicaciones (ej. tomar con alimentos)" style="flex:1; padding:7px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; box-sizing:border-box;" />
            <button v-if="formReceta.items.length>1" @click="quitarItemReceta(idx)" style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:6px; background:white; cursor:pointer; font-size:12px;">x</button>
          </div>
        </div>
        <button @click="agregarItemReceta" style="padding:7px 14px; border:1px solid #8B5CF6; color:#8B5CF6; border-radius:6px; background:white; font-size:12px; cursor:pointer; margin-bottom:10px;">+ Agregar medicamento</button>
        <textarea v-model="formReceta.indicaciones" placeholder="Indicaciones generales" rows="2" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; box-sizing:border-box; margin-bottom:10px;"></textarea>
        <button @click="guardarReceta" :disabled="guardandoReceta" style="padding:9px 18px; background:#8B5CF6; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
          {{ guardandoReceta ? 'Guardando...' : 'Guardar receta' }}
        </button>
      </div>

      <div v-if="recetas.length" style="margin-bottom:16px;">
        <p style="font-size:12px; font-weight:700; color:#64748B; text-transform:uppercase; margin:0 0 8px;">Recetas emitidas</p>
        <div v-for="r in recetas" :key="'rec-'+r.id" style="background:white; border:1px solid #E2E8F0; border-radius:10px; padding:12px 16px; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center;">
          <div>
            <p style="margin:0; font-weight:600; font-size:13px;">{{ r.fecha }} - {{ r.items.length }} medicamento(s)</p>
            <p style="margin:2px 0 0; font-size:12px; color:#64748B;">{{ r.items.map(it => it.medicamento).join(', ') }}</p>
          </div>
          <a :href="`/odontologia/recetas/${r.id}/pdf`" target="_blank" style="padding:7px 14px; background:#8B5CF6; color:white; border-radius:6px; font-size:12px; font-weight:600; text-decoration:none;">Imprimir</a>
        </div>
      </div>

      <div v-if="historias.length===0" style="text-align:center; padding:32px; color:#94A3B8;">Sin historia clínica registrada</div>
      <div v-for="h in historias" :key="h.id" style="background:white; border:1px solid #E2E8F0; border-radius:10px; padding:16px; margin-bottom:10px;">
        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
          <p style="margin:0; font-weight:600; font-size:14px;">{{ h.fecha }}</p>
          <p style="margin:0; font-size:12px; color:#64748B;">Dr. {{ h.doctor?.nombre }}</p>
        </div>
        <p v-if="h.diagnostico" style="margin:0 0 4px; font-size:13px;"><strong>Diagnóstico:</strong> {{ h.diagnostico }}</p>
        <p v-if="h.tratamiento_realizado" style="margin:0; font-size:13px;"><strong>Tratamiento:</strong> {{ h.tratamiento_realizado }}</p>
      </div>

      <div v-if="odontogramaEventos.length" style="margin-top:16px;">
        <p style="font-size:12px; font-weight:700; color:#64748B; text-transform:uppercase; margin:0 0 8px;">Cambios en el odontograma</p>
        <div v-for="ev in odontogramaEventos" :key="'odo-'+ev.diente" style="background:white; border:1px solid #E2E8F0; border-radius:10px; padding:12px 16px; margin-bottom:8px; display:flex; justify-content:space-between; align-items:center;">
          <div>
            <p style="margin:0; font-weight:600; font-size:13px;">Pieza {{ ev.diente }} - {{ estadoOdontoLabel(ev.estado) }}</p>
            <p v-if="ev.notas" style="margin:2px 0 0; font-size:12px; color:#64748B;">{{ ev.notas }}</p>
          </div>
          <span style="font-size:11px; color:#94A3B8;">{{ formatFecha(ev.updated_at) }}</span>
        </div>
      </div>
    </div>

    <!-- Tab: Presupuestos -->
    <div v-if="tabActivo==='presupuestos'">
      <div v-if="presupuestos.length===0" style="text-align:center; padding:32px; color:#94A3B8;">Sin presupuestos</div>
      <div v-for="p in presupuestos" :key="p.id" style="background:white; border:1px solid #E2E8F0; border-radius:10px; padding:16px; margin-bottom:10px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
          <p style="margin:0; font-weight:600; font-size:14px;">Presupuesto #{{ p.id }} · {{ p.fecha }}</p>
          <div style="display:flex; align-items:center; gap:12px;">
            <span style="font-size:16px; font-weight:800; color:#10B981;">S/ {{ Number(p.total).toFixed(2) }}</span>
            <span :style="estadoPresupuesto(p.estado)">{{ p.estado }}</span>
          </div>
        </div>
        <div v-for="item in p.items" :key="item.id" style="display:flex; justify-content:space-between; padding:4px 0; border-top:1px solid #F1F5F9; font-size:13px;">
          <span>{{ item.descripcion }} {{ item.numero_pieza ? '(pieza '+item.numero_pieza+')' : '' }}</span>
          <span style="color:#64748B;">S/ {{ Number(item.subtotal).toFixed(2) }}</span>
        </div>
      </div>
    </div>

    <!-- Tab: Pagos -->
    <div v-if="tabActivo==='pagos'">
      <div v-if="pagos.length===0" style="text-align:center; padding:32px; color:#94A3B8;">Sin pagos registrados</div>
      <div v-for="p in pagos" :key="p.id" style="background:white; border:1px solid #E2E8F0; border-radius:10px; padding:16px; margin-bottom:10px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
          <p style="margin:0; font-weight:600; font-size:14px;">Pago #{{ p.id }} · {{ p.fecha }} · {{ p.tipo_pago }}</p>
          <span style="font-size:16px; font-weight:800; color:#8B5CF6;">S/ {{ Number(p.monto_total).toFixed(2) }}</span>
        </div>
        <div v-for="c in p.cuotas" :key="c.id" style="display:flex; justify-content:space-between; padding:6px 0; border-top:1px solid #F1F5F9; font-size:13px; align-items:center;">
          <span>Cuota {{ c.numero_cuota }} · Vence: {{ c.fecha_vencimiento }}</span>
          <div style="display:flex; align-items:center; gap:8px;">
            <span>S/ {{ Number(c.monto).toFixed(2) }}</span>
            <span :style="c.estado==='pagado' ? {color:'#10B981',fontWeight:'600'} : {color:'#F59E0B',fontWeight:'600'}">{{ c.estado }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

    <!-- Tab: Radiografías -->
    <div v-if="tabActivo==='radiografias'" style="padding:16px;">
      <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:16px;">
        <p style="margin:0 0 12px;font-size:13px;font-weight:600;color:#374151;">Subir imagen / radiografía</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
          <div>
            <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px;">Tipo</label>
            <select v-model="formRadio.tipo" style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
              <option value="panorámica">Panorámica</option>
              <option value="periapical">Periapical</option>
              <option value="bitewing">Bitewing</option>
              <option value="oclusal">Oclusal</option>
              <option value="foto clínica">Foto clínica</option>
              <option value="otro">Otro</option>
            </select>
          </div>
          <div>
            <label style="font-size:11px;color:#64748b;display:block;margin-bottom:3px;">Descripción</label>
            <input v-model="formRadio.descripcion" style="width:100%;padding:7px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;" placeholder="Opcional" />
          </div>
        </div>
        <input type="file" ref="radioInput" accept="image/*,.pdf" @change="onArchivoChange" style="display:none;" />
        <input type="file" ref="multiInput" accept="image/*,.pdf" multiple @change="onMultiChange" style="display:none;" />

        <!-- Opciones de captura -->
        <div v-if="!camaraActiva">

          <!-- Botones de método -->
          <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:14px;">
            <button @click="radioInput.click()"
              style="padding:10px 6px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;cursor:pointer;text-align:center;">
              <div style="font-size:22px;">📁</div>
              <div style="font-size:11px;color:#64748b;margin-top:4px;font-weight:500;">Desde PC</div>
            </button>
            <button @click="abrirCamara"
              style="padding:10px 6px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;cursor:pointer;text-align:center;">
              <div style="font-size:22px;">📷</div>
              <div style="font-size:11px;color:#64748b;margin-top:4px;font-weight:500;">Cámara web</div>
            </button>
            <button @click="mostrarQR=!mostrarQR"
              style="padding:10px 6px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;cursor:pointer;text-align:center;">
              <div style="font-size:22px;">📱</div>
              <div style="font-size:11px;color:#64748b;margin-top:4px;font-weight:500;">Celular</div>
            </button>
            <button @click="multiInput.click()"
              style="padding:10px 6px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;cursor:pointer;text-align:center;">
              <div style="font-size:22px;">🗂️</div>
              <div style="font-size:11px;color:#64748b;margin-top:4px;font-weight:500;">Múltiples</div>
            </button>
          </div>

          <!-- QR para celular -->
          <div v-if="mostrarQR" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px;margin-bottom:12px;display:flex;align-items:center;gap:16px;">
            <img :src="qrUrl" style="width:80px;height:80px;border-radius:6px;" />
            <div>
              <div style="font-size:13px;font-weight:500;color:#166534;margin-bottom:4px;">Escanea con tu celular</div>
              <div style="font-size:11px;color:#15803d;">Abre la cámara del celular y sube la foto directamente a este paciente</div>
              <div style="font-size:10px;color:#94a3b8;margin-top:4px;word-break:break-all;">{{ uploadUrl }}</div>
            </div>
          </div>

          <!-- Cola de archivos múltiples -->
          <div v-if="colaArchivos.length > 0" style="margin-bottom:12px;">
            <div style="font-size:12px;color:#64748b;margin-bottom:6px;">{{ colaArchivos.length }} archivo(s) seleccionado(s):</div>
            <div v-for="(a,i) in colaArchivos" :key="i" style="display:flex;align-items:center;gap:8px;padding:6px 10px;background:#f8fafc;border-radius:6px;margin-bottom:4px;">
              <span style="font-size:12px;flex:1;color:#374151;">{{ a.name }}</span>
              <span style="font-size:11px;color:#94a3b8;">{{ (a.size/1024/1024).toFixed(2) }}MB</span>
              <button @click="colaArchivos.splice(i,1)" style="color:#ef4444;background:none;border:none;cursor:pointer;font-size:14px;">✕</button>
            </div>
          </div>

          <!-- Archivo único seleccionado -->
          <div v-if="formRadio.archivo && colaArchivos.length===0"
            style="background:#f5f3ff;border:1px solid #c4b5fd;border-radius:10px;padding:12px 14px;margin-bottom:12px;display:flex;align-items:center;gap:10px;">
            <div style="font-size:20px;">✓</div>
            <div style="flex:1;">
              <div style="font-size:13px;font-weight:500;color:#6d28d9;">{{ formRadio.archivo.name }}</div>
              <div style="font-size:11px;color:#94a3b8;">{{ (formRadio.archivo.size/1024/1024).toFixed(2) }} MB</div>
            </div>
            <button @click="formRadio.archivo=null" style="color:#94a3b8;background:none;border:none;cursor:pointer;">✕</button>
          </div>

          <!-- Zona drag & drop general -->
          <div @dragover.prevent @drop.prevent="onDrop"
            style="border:2px dashed #e2e8f0;border-radius:10px;padding:12px;text-align:center;margin-bottom:12px;color:#94a3b8;font-size:12px;">
            También puedes arrastrar archivos aquí
          </div>

          <div style="display:flex;gap:8px;">
            <button @click="colaArchivos.length>0 ? subirMultiples() : subirRadiografia()"
              :disabled="!formRadio.archivo && colaArchivos.length===0"
              style="padding:9px 22px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;"
              :style="{opacity: (formRadio.archivo || colaArchivos.length>0) ? 1 : 0.4}">
              {{ colaArchivos.length>0 ? `Subir ${colaArchivos.length} archivos` : 'Subir archivo' }}
            </button>
          </div>
        </div>

        <!-- Vista de cámara -->
        <div v-if="camaraActiva" style="margin-bottom:12px;">
          <video ref="videoEl" autoplay playsinline style="width:100%;border-radius:10px;background:#000;max-height:280px;object-fit:cover;"></video>
          <canvas ref="canvasEl" style="display:none;"></canvas>
          <div style="display:flex;gap:8px;margin-top:10px;">
            <button @click="capturarFoto"
              style="flex:1;padding:10px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;">
              📸 Capturar foto
            </button>
            <button @click="cerrarCamara"
              style="padding:10px 16px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;cursor:pointer;background:#fff;color:#64748b;">
              Cancelar
            </button>
          </div>
        </div>
      </div>

      <div v-if="!radiografias || radiografias.length===0" style="text-align:center;padding:32px;color:#94a3b8;font-size:13px;">Sin imágenes registradas</div>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;">
        <div v-for="r in radiografias" :key="r.id" style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;">
          <a :href="'/storage/'+r.archivo_url" target="_blank">
            <img v-if="!r.archivo_url.endsWith('.pdf')" :src="'/storage/'+r.archivo_url" style="width:100%;height:120px;object-fit:cover;display:block;" />
            <div v-else style="height:120px;display:flex;align-items:center;justify-content:center;background:#f1f5f9;font-size:28px;">📄</div>
          </a>
          <div style="padding:8px 10px;">
            <div style="font-size:12px;font-weight:500;color:#374151;">{{ r.tipo }}</div>
            <div style="font-size:11px;color:#94a3b8;">{{ r.fecha }}</div>
            <div v-if="r.descripcion" style="font-size:11px;color:#64748b;margin-top:2px;">{{ r.descripcion }}</div>
            <button @click="eliminarRadio(r.id)" style="margin-top:6px;font-size:11px;color:#ef4444;background:none;border:none;cursor:pointer;padding:0;">Eliminar</button>
          </div>
        </div>
      </div>
    </div>

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ paciente:Object, citas:Array, historias:Array, presupuestos:Array, pagos:Array, odontogramaEventos:Array, doctores:Array, recetas:Array, radiografias:Array })
const tabActivo = ref('citas')
const tabs = [
  { key:'citas', label:'Citas' },
  { key:'historia', label:'Historia clínica' },
  { key:'presupuestos', label:'Presupuestos' },
  { key:'pagos', label:'Pagos' },
  { key:'radiografias', label:'Radiografías' },
]
const formatFecha = (f) => new Date(f).toLocaleString('es-PE', { dateStyle:'short', timeStyle:'short' })
const estadoOdontoLabel = (e) => {
  const m = { sano:'Sano', caries:'Caries', tratamiento:'Tratamiento', extraccion:'Extraccion', ausente:'Ausente', corona:'Corona', implante:'Implante', sellante:'Sellante' }
  return m[e] || e
}
const estadoStyle = (e) => {
  const m = { programada:{background:'#EFF6FF',color:'#1D4ED8'}, confirmada:{background:'#F0FDF4',color:'#15803D'}, completada:{background:'#F0FDF4',color:'#15803D'}, cancelada:{background:'#FEF2F2',color:'#B91C1C'} }
  return { ...(m[e]||{background:'#F9FAFB',color:'#6B7280'}), padding:'3px 8px', borderRadius:'6px', fontSize:'11px', fontWeight:'600' }
}
const estadoPresupuesto = (e) => {
  const m = { borrador:{background:'#F9FAFB',color:'#6B7280'}, aprobado:{background:'#F0FDF4',color:'#15803D'}, rechazado:{background:'#FEF2F2',color:'#B91C1C'}, completado:{background:'#EDE9FE',color:'#7C3AED'} }
  return { ...(m[e]||{}), padding:'3px 8px', borderRadius:'6px', fontSize:'11px', fontWeight:'600' }
}

const guardandoHistoria = ref(false)
const vacioHistoria = () => ({ doctor_id:'', fecha: new Date().toISOString().slice(0,10), anamnesis:'', diagnostico:'', tratamiento_realizado:'', observaciones:'' })
const formHistoria = ref(vacioHistoria())
const guardarHistoria = () => {
  guardandoHistoria.value = true
  router.post('/odontologia/historia-clinica', { ...formHistoria.value, paciente_id: props.paciente.id }, {
    onFinish: () => { guardandoHistoria.value = false },
    onSuccess: () => { formHistoria.value = vacioHistoria() }
  })
}

const guardandoReceta = ref(false)
const vacioReceta = () => ({ indicaciones:'', items:[{medicamento:'',dosis:'',frecuencia:'',duracion:'',indicaciones:''}] })
const formReceta = ref(vacioReceta())
const agregarItemReceta = () => { formReceta.value.items.push({medicamento:'',dosis:'',frecuencia:'',duracion:'',indicaciones:''}) }
const quitarItemReceta = (i) => { formReceta.value.items.splice(i,1) }
const guardarReceta = () => {
  guardandoReceta.value = true
  router.post('/odontologia/recetas', { ...formReceta.value, doctor_id: formHistoria.value.doctor_id, fecha: formHistoria.value.fecha, paciente_id: props.paciente.id }, {
    onFinish: () => { guardandoReceta.value = false },
    onSuccess: () => { formReceta.value = vacioReceta() }
  })
}

const formRadio = ref({ tipo:'panorámica', descripcion:'', archivo:null })
const radioInput = ref(null)
const onArchivoChange = (e) => { formRadio.value.archivo = e.target.files[0] || null }
const onDrop = (e) => { const file = e.dataTransfer.files[0]; if(file) formRadio.value.archivo = file }

const camaraActiva = ref(false)
const videoEl      = ref(null)
const canvasEl     = ref(null)
const mostrarQR    = ref(false)
const colaArchivos = ref([])
let streamActivo   = null

const uploadUrl = `${window.location.origin}/odontologia/pacientes/${props.paciente.id}?tab=radiografias`
const qrUrl     = `https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=${encodeURIComponent(uploadUrl)}`

const onMultiChange = (e) => {
  colaArchivos.value = Array.from(e.target.files)
  formRadio.value.archivo = null
}

const subirMultiples = async () => {
  for (const archivo of colaArchivos.value) {
    const data = new FormData()
    data.append('paciente_id', props.paciente.id)
    data.append('tipo', formRadio.value.tipo)
    data.append('descripcion', formRadio.value.descripcion)
    data.append('archivo', archivo)
    await new Promise(resolve => {
      router.post('/odontologia/radiografias', data, { onFinish: resolve })
    })
  }
  colaArchivos.value = []
}

const abrirCamara = async () => {
  try {
    streamActivo = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
    camaraActiva.value = true
    await new Promise(r => setTimeout(r, 100))
    if (videoEl.value) videoEl.value.srcObject = streamActivo
  } catch(e) {
    alert('No se pudo acceder a la cámara. Verifica los permisos del navegador.')
  }
}

const cerrarCamara = () => {
  if (streamActivo) { streamActivo.getTracks().forEach(t => t.stop()); streamActivo = null }
  camaraActiva.value = false
}

const capturarFoto = () => {
  const video  = videoEl.value
  const canvas = canvasEl.value
  canvas.width  = video.videoWidth
  canvas.height = video.videoHeight
  canvas.getContext('2d').drawImage(video, 0, 0)
  canvas.toBlob(blob => {
    const file = new File([blob], `foto_${Date.now()}.jpg`, { type: 'image/jpeg' })
    formRadio.value.archivo = file
    cerrarCamara()
  }, 'image/jpeg', 0.92)
}
const subirRadiografia = () => {
  if (!formRadio.value.archivo) return
  const data = new FormData()
  data.append('paciente_id', props.paciente.id)
  data.append('tipo', formRadio.value.tipo)
  data.append('descripcion', formRadio.value.descripcion)
  data.append('archivo', formRadio.value.archivo)
  router.post('/odontologia/radiografias', data, {
    onSuccess: () => { formRadio.value = { tipo:'panorámica', descripcion:'', archivo:null }; if(radioInput.value) radioInput.value.value='' }
  })
}
const eliminarRadio = (id) => {
  if (confirm('¿Eliminar esta imagen?')) router.delete(`/odontologia/radiografias/${id}`)
}
</script>
