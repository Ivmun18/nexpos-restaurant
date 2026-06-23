<template>
  <AppLayout>
    <div style="padding:28px 32px;max-width:1100px;margin:0 auto;">

      <!-- HEADER + FILTROS -->
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;flex-wrap:wrap;">
        <div>
          <h1 style="font-size:20px;font-weight:600;color:#1e293b;margin:0;">Reportes</h1>
          <p style="font-size:13px;color:#64748b;margin:4px 0 0;">Resumen del período seleccionado</p>
        </div>
        <div style="margin-left:auto;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
          <div style="display:flex;align-items:center;gap:6px;">
            <label style="font-size:12px;color:#64748b;">Desde</label>
            <input type="date" v-model="filtro.desde" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;" />
          </div>
          <div style="display:flex;align-items:center;gap:6px;">
            <label style="font-size:12px;color:#64748b;">Hasta</label>
            <input type="date" v-model="filtro.hasta" style="padding:6px 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;" />
          </div>
          <button @click="aplicar" style="padding:7px 18px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:500;cursor:pointer;">Aplicar</button>
          <button @click="setRango('mes')" :style="rangoActivo==='mes'?{background:'#ede9fe',color:'#6d28d9',border:'1px solid #c4b5fd'}:{background:'#fff',color:'#64748b',border:'1px solid #e2e8f0'}" style="padding:6px 14px;border-radius:8px;font-size:12px;cursor:pointer;">Este mes</button>
          <button @click="setRango('semana')" :style="rangoActivo==='semana'?{background:'#ede9fe',color:'#6d28d9',border:'1px solid #c4b5fd'}:{background:'#fff',color:'#64748b',border:'1px solid #e2e8f0'}" style="padding:6px 14px;border-radius:8px;font-size:12px;cursor:pointer;">Esta semana</button>
          <button @click="setRango('hoy')" :style="rangoActivo==='hoy'?{background:'#ede9fe',color:'#6d28d9',border:'1px solid #c4b5fd'}:{background:'#fff',color:'#64748b',border:'1px solid #e2e8f0'}" style="padding:6px 14px;border-radius:8px;font-size:12px;cursor:pointer;">Hoy</button>
        </div>
      </div>

      <!-- KPIs -->
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px;">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 20px;">
          <div style="font-size:11px;color:#94a3b8;margin-bottom:6px;">Ingresos del período</div>
          <div style="font-size:24px;font-weight:600;color:#8B5CF6;">S/ {{ fmt(totalIngresos) }}</div>
          <div style="font-size:11px;color:#94a3b8;margin-top:4px;">cobrado</div>
        </div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 20px;">
          <div style="font-size:11px;color:#94a3b8;margin-bottom:6px;">Saldo pendiente</div>
          <div style="font-size:24px;font-weight:600;color:#ef4444;">S/ {{ fmt(totalPendiente) }}</div>
          <div style="font-size:11px;color:#94a3b8;margin-top:4px;">por cobrar</div>
        </div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 20px;">
          <div style="font-size:11px;color:#94a3b8;margin-bottom:6px;">Citas</div>
          <div style="font-size:24px;font-weight:600;color:#0ea5e9;">{{ totalCitas }}</div>
          <div style="font-size:11px;color:#94a3b8;margin-top:4px;">en el período</div>
        </div>
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 20px;">
          <div style="font-size:11px;color:#94a3b8;margin-bottom:6px;">Pacientes nuevos</div>
          <div style="font-size:24px;font-weight:600;color:#10b981;">{{ pacientesNuevos }}</div>
          <div style="font-size:11px;color:#94a3b8;margin-top:4px;">registrados</div>
        </div>
      </div>

      <!-- GRÁFICOS -->
      <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:24px;">

        <!-- Ingresos por día -->
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
          <div style="font-size:13px;font-weight:500;color:#374151;margin-bottom:16px;">Ingresos por día</div>
          <div style="height:200px;position:relative;">
            <canvas ref="chartDia"></canvas>
          </div>
        </div>

        <!-- Tipo de pago -->
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
          <div style="font-size:13px;font-weight:500;color:#374151;margin-bottom:16px;">Por tipo de pago</div>
          <div style="height:200px;position:relative;">
            <canvas ref="chartTipo"></canvas>
          </div>
        </div>
      </div>

      <!-- TABLA INGRESOS + CITAS -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

        <!-- Detalle ingresos por día -->
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
          <div style="font-size:13px;font-weight:500;color:#374151;margin-bottom:14px;">Detalle de ingresos</div>
          <div v-if="ingresosDia.length===0" style="text-align:center;padding:30px;color:#94a3b8;font-size:13px;">Sin ingresos en el período</div>
          <table v-else style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead>
              <tr style="border-bottom:1px solid #f1f5f9;">
                <th style="text-align:left;padding:6px 0;color:#94a3b8;font-weight:500;">Fecha</th>
                <th style="text-align:center;padding:6px 0;color:#94a3b8;font-weight:500;">Pagos</th>
                <th style="text-align:right;padding:6px 0;color:#94a3b8;font-weight:500;">Total S/</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in ingresosDia" :key="d.dia" style="border-bottom:1px solid #f8fafc;">
                <td style="padding:7px 0;color:#374151;">{{ formatFecha(d.dia) }}</td>
                <td style="padding:7px 0;text-align:center;color:#64748b;">{{ d.cantidad }}</td>
                <td style="padding:7px 0;text-align:right;font-weight:500;color:#8B5CF6;">{{ fmt(d.total) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr style="border-top:2px solid #e2e8f0;">
                <td style="padding:8px 0;font-weight:600;color:#1e293b;">Total</td>
                <td></td>
                <td style="padding:8px 0;text-align:right;font-weight:600;color:#8B5CF6;">{{ fmt(totalIngresos) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Estado de citas -->
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
          <div style="font-size:13px;font-weight:500;color:#374151;margin-bottom:14px;">Estado de citas</div>
          <div v-if="citasEstado.length===0" style="text-align:center;padding:30px;color:#94a3b8;font-size:13px;">Sin citas en el período</div>
          <div v-else style="display:flex;flex-direction:column;gap:10px;">
            <div v-for="c in citasEstado" :key="c.estado" style="display:flex;align-items:center;gap:10px;">
              <span :style="estadoBadge(c.estado)" style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:500;min-width:90px;text-align:center;">{{ c.estado }}</span>
              <div style="flex:1;background:#f1f5f9;border-radius:4px;height:8px;overflow:hidden;">
                <div :style="{width: pct(c.cantidad, totalCitas)+'%', background:'#8B5CF6', height:'100%', borderRadius:'4px', transition:'width .5s'}"></div>
              </div>
              <span style="font-size:12px;font-weight:500;color:#374151;min-width:24px;text-align:right;">{{ c.cantidad }}</span>
            </div>
          </div>
          <div style="margin-top:20px;padding-top:14px;border-top:1px solid #f1f5f9;">
            <div style="font-size:11px;color:#94a3b8;margin-bottom:4px;">Presupuestos generados</div>
            <div style="font-size:22px;font-weight:600;color:#374151;">{{ totalPresupuestos }}</div>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  desde: String, hasta: String,
  ingresosDia: Array, ingresosTipo: Array,
  citasEstado: Array, pacientesNuevos: Number,
  totalIngresos: Number, totalPendiente: Number,
  totalCitas: Number, totalPresupuestos: Number,
})

const filtro      = ref({ desde: props.desde, hasta: props.hasta })
const rangoActivo = ref('mes')
const chartDia    = ref(null)
const chartTipo   = ref(null)
let chartDiaInst  = null
let chartTipoInst = null

const fmt         = (v) => Number(v || 0).toFixed(2)
const pct         = (v, t) => t > 0 ? Math.round((v / t) * 100) : 0
const formatFecha = (d) => new Date(d + 'T12:00:00').toLocaleDateString('es-PE', { day:'2-digit', month:'short' })

const estadoBadge = (e) => {
  const m = { programada:{background:'#dbeafe',color:'#1e40af'}, confirmada:{background:'#dcfce7',color:'#166534'}, completada:{background:'#f3e8ff',color:'#6b21a8'}, cancelada:{background:'#fee2e2',color:'#991b1b'} }
  return m[e] || { background:'#f1f5f9', color:'#475569' }
}

const setRango = (r) => {
  rangoActivo.value = r
  const hoy = new Date()
  const pad = (n) => String(n).padStart(2,'0')
  const fmt2 = (d) => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`
  if (r === 'hoy') { filtro.value.desde = filtro.value.hasta = fmt2(hoy) }
  else if (r === 'semana') {
    const lun = new Date(hoy); lun.setDate(hoy.getDate() - hoy.getDay() + 1)
    filtro.value.desde = fmt2(lun); filtro.value.hasta = fmt2(hoy)
  } else {
    filtro.value.desde = fmt2(new Date(hoy.getFullYear(), hoy.getMonth(), 1))
    filtro.value.hasta = fmt2(hoy)
  }
  aplicar()
}

const aplicar = () => router.get('/odontologia/reportes', filtro.value, { preserveState: false })

const initCharts = () => {
  if (typeof Chart === 'undefined') return

  if (chartDiaInst) chartDiaInst.destroy()
  if (chartTipoInst) chartTipoInst.destroy()

  const labels = props.ingresosDia.map(d => formatFecha(d.dia))
  const data   = props.ingresosDia.map(d => Number(d.total))

  chartDiaInst = new Chart(chartDia.value, {
    type: 'bar',
    data: {
      labels,
      datasets: [{ label: 'S/', data, backgroundColor: '#c4b5fd', borderRadius: 6, borderSkipped: false }]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} }, scales:{ y:{ beginAtZero:true, ticks:{ callback: v => 'S/'+v } }, x:{ grid:{display:false} } } }
  })

  const tipoLabels = props.ingresosTipo.map(t => t.tipo_pago || 'otro')
  const tipoData   = props.ingresosTipo.map(t => Number(t.total))
  const colores    = ['#8B5CF6','#06b6d4','#10b981','#f59e0b','#ef4444']

  chartTipoInst = new Chart(chartTipo.value, {
    type: 'doughnut',
    data: { labels: tipoLabels, datasets: [{ data: tipoData, backgroundColor: colores, borderWidth: 0 }] },
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ font:{size:11}, padding:10 } } } }
  })
}

onMounted(() => {
  const s = document.createElement('script')
  s.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js'
  s.onload = initCharts
  document.head.appendChild(s)
})
</script>
