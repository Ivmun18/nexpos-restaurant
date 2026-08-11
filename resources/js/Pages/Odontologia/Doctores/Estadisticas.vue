<template>
  <AppLayout>
    <div style="padding:28px 32px;max-width:1100px;margin:0 auto;">

      <!-- HEADER + FILTROS -->
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;flex-wrap:wrap;">
        <div>
          <h1 style="font-size:20px;font-weight:600;color:#1e293b;margin:0;">Estadísticas por doctor</h1>
          <p style="font-size:13px;color:#64748b;margin:4px 0 0;">Producción y rendimiento del equipo médico</p>
        </div>
        <div style="margin-left:auto;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
          <input type="date" v-model="filtro.desde" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;" />
          <input type="date" v-model="filtro.hasta" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;" />
          <button @click="aplicar" style="padding:7px 18px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:500;cursor:pointer;">Aplicar</button>
          <button @click="setRango('mes')" :style="rangoActivo==='mes'?{background:'#ede9fe',color:'#6d28d9',border:'1px solid #c4b5fd'}:{background:'#fff',color:'#64748b',border:'1px solid #e2e8f0'}" style="padding:6px 14px;border-radius:8px;font-size:12px;cursor:pointer;">Este mes</button>
        </div>
      </div>

      <!-- RANKING CARDS -->
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;margin-bottom:24px;">
        <div v-for="(d,i) in stats" :key="d.id"
          @click="seleccionado = seleccionado?.id===d.id ? null : d"
          :style="{border: seleccionado?.id===d.id ? '2px solid #8B5CF6' : '1px solid #e2e8f0', cursor:'pointer'}"
          style="background:#fff;border-radius:14px;padding:20px;transition:all .2s;">

          <!-- Doctor info -->
          <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
            <div :style="{background: i===0?'#fef3c7':i===1?'#f1f5f9':'#fdf2f8'}"
              style="width:46px;height:46px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">
              {{ i===0?'🥇':i===1?'🥈':'🥉' }}
            </div>
            <div style="flex:1;">
              <div style="font-size:15px;font-weight:600;color:#1e293b;">{{ d.nombre }}</div>
              <div style="font-size:12px;color:#64748b;">{{ d.especialidad }}</div>
            </div>
            <div style="text-align:right;">
              <div style="font-size:20px;font-weight:700;color:#8B5CF6;">S/ {{ fmt(d.total_ingresos) }}</div>
              <div style="font-size:11px;color:#94a3b8;">ingresos</div>
            </div>
          </div>

          <!-- KPIs fila -->
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px;">
            <div style="background:#f8fafc;border-radius:8px;padding:8px;text-align:center;">
              <div style="font-size:18px;font-weight:600;color:#0ea5e9;">{{ d.total_citas }}</div>
              <div style="font-size:10px;color:#94a3b8;">Citas</div>
            </div>
            <div style="background:#f8fafc;border-radius:8px;padding:8px;text-align:center;">
              <div style="font-size:18px;font-weight:600;color:#10b981;">{{ d.citas_completadas }}</div>
              <div style="font-size:10px;color:#94a3b8;">Completadas</div>
            </div>
            <div style="background:#f8fafc;border-radius:8px;padding:8px;text-align:center;">
              <div style="font-size:18px;font-weight:600;color:#f59e0b;">{{ d.total_presupuestos }}</div>
              <div style="font-size:10px;color:#94a3b8;">Presupuestos</div>
            </div>
          </div>

          <!-- Tasa de asistencia -->
          <div style="margin-bottom:10px;">
            <div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-bottom:4px;">
              <span>Tasa de asistencia</span>
              <span style="font-weight:500;">{{ tasaAsistencia(d) }}%</span>
            </div>
            <div style="background:#f1f5f9;border-radius:20px;height:6px;overflow:hidden;">
              <div :style="{width:tasaAsistencia(d)+'%', background: tasaAsistencia(d)>=80?'#10b981':tasaAsistencia(d)>=50?'#f59e0b':'#ef4444'}"
                style="height:100%;border-radius:20px;transition:width .5s;"></div>
            </div>
          </div>

          <!-- Top tratamientos mini -->
          <div v-if="d.tratamientos_top?.length > 0">
            <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Top tratamientos</div>
            <div v-for="t in d.tratamientos_top.slice(0,3)" :key="t.nombre"
              style="display:flex;justify-content:space-between;font-size:12px;padding:3px 0;border-bottom:1px solid #f8fafc;">
              <span style="color:#374151;">{{ t.nombre }}</span>
              <span style="color:#8B5CF6;font-weight:500;">×{{ t.cantidad }}</span>
            </div>
          </div>

          <div v-if="seleccionado?.id===d.id" style="margin-top:10px;text-align:center;font-size:11px;color:#8B5CF6;">▲ Ver detalle arriba</div>
        </div>
      </div>

      <!-- DETALLE DOCTOR SELECCIONADO -->
      <div v-if="seleccionado" style="background:#fff;border:2px solid #8B5CF6;border-radius:14px;padding:24px;margin-bottom:24px;">
        <div style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:16px;">
          📊 Detalle completo — Dr. {{ seleccionado.nombre }}
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
          <!-- Tratamientos top completo -->
          <div>
            <div style="font-size:12px;font-weight:500;color:#374151;margin-bottom:10px;">Tratamientos realizados</div>
            <div v-if="seleccionado.tratamientos_top?.length===0" style="color:#94a3b8;font-size:13px;">Sin datos</div>
            <div v-for="(t,i) in seleccionado.tratamientos_top" :key="t.nombre" style="margin-bottom:8px;">
              <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:3px;">
                <span style="color:#374151;">{{ t.nombre }}</span>
                <span style="color:#8B5CF6;font-weight:500;">{{ t.cantidad }}x</span>
              </div>
              <div style="background:#f1f5f9;border-radius:20px;height:6px;overflow:hidden;">
                <div :style="{width: pctTrat(t.cantidad, seleccionado.tratamientos_top)+'%'}"
                  style="background:#8B5CF6;height:100%;border-radius:20px;"></div>
              </div>
            </div>
          </div>
          <!-- Resumen numérico -->
          <div>
            <div style="font-size:12px;font-weight:500;color:#374151;margin-bottom:10px;">Resumen del período</div>
            <div style="display:flex;flex-direction:column;gap:10px;">
              <div style="display:flex;justify-content:space-between;padding:10px 14px;background:#f8fafc;border-radius:8px;">
                <span style="font-size:13px;color:#64748b;">Total ingresos</span>
                <span style="font-size:14px;font-weight:600;color:#8B5CF6;">S/ {{ fmt(seleccionado.total_ingresos) }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;padding:10px 14px;background:#f8fafc;border-radius:8px;">
                <span style="font-size:13px;color:#64748b;">Citas totales</span>
                <span style="font-size:14px;font-weight:600;color:#0ea5e9;">{{ seleccionado.total_citas }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;padding:10px 14px;background:#f8fafc;border-radius:8px;">
                <span style="font-size:13px;color:#64748b;">Citas completadas</span>
                <span style="font-size:14px;font-weight:600;color:#10b981;">{{ seleccionado.citas_completadas }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;padding:10px 14px;background:#f8fafc;border-radius:8px;">
                <span style="font-size:13px;color:#64748b;">Citas canceladas</span>
                <span style="font-size:14px;font-weight:600;color:#ef4444;">{{ seleccionado.citas_canceladas }}</span>
              </div>
              <div style="display:flex;justify-content:space-between;padding:10px 14px;background:#f8fafc;border-radius:8px;">
                <span style="font-size:13px;color:#64748b;">Tasa de asistencia</span>
                <span style="font-size:14px;font-weight:600;" :style="{color:tasaAsistencia(seleccionado)>=80?'#10b981':tasaAsistencia(seleccionado)>=50?'#f59e0b':'#ef4444'}">{{ tasaAsistencia(seleccionado) }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ESTADO VACÍO -->
      <div v-if="stats.length===0" style="text-align:center;padding:60px;color:#94a3b8;">
        <div style="font-size:40px;margin-bottom:12px;">👨‍⚕️</div>
        <div style="font-size:14px;">No hay doctores registrados o sin actividad en el período</div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ stats: Array, desde: String, hasta: String, doctores: Array })

const filtro      = ref({ desde: props.desde, hasta: props.hasta })
const rangoActivo = ref('mes')
const seleccionado = ref(null)

const fmt = (v) => Number(v || 0).toFixed(2)

const tasaAsistencia = (d) => {
  if (!d.total_citas) return 0
  return Math.round((d.citas_completadas / d.total_citas) * 100)
}
const pctTrat = (cantidad, lista) => {
  const max = Math.max(...lista.map(t => t.cantidad))
  return max > 0 ? Math.round((cantidad / max) * 100) : 0
}
const setRango = (r) => {
  rangoActivo.value = r
  const hoy = new Date()
  const pad = (n) => String(n).padStart(2,'0')
  const fmt2 = (d) => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`
  filtro.value.desde = fmt2(new Date(hoy.getFullYear(), hoy.getMonth(), 1))
  filtro.value.hasta = fmt2(hoy)
  aplicar()
}
const aplicar = () => router.get('/odontologia/doctores/estadisticas', filtro.value, { preserveState: false })
</script>
