<template>
  <AppLayout title="Centro Odontológico" subtitle="Resumen de operaciones">
  <div style="padding:20px; background:#F0F4FF; min-height:100vh;">

    <!-- Header -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <div>
        <h1 style="font-size:20px; font-weight:700; color:#1e293b; margin:0;">🦷 Centro Odontológico</h1>
        <p style="font-size:12px; color:#64748b; margin:2px 0 0;">{{ fechaHoy }}</p>
      </div>
      <a href="/odontologia/citas/crear" style="background:#6C8EBF; color:white; border:none; border-radius:8px; padding:8px 16px; font-size:13px; font-weight:600; text-decoration:none; display:inline-block;">+ Nueva cita</a>
    </div>

    <!-- Stats -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-bottom:16px;">
      <div v-for="s in statsCards" :key="s.label" style="background:white; border-radius:12px; padding:16px; border-left:4px solid;" :style="{'border-left-color': s.color}">
        <p style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; margin:0 0 6px;">{{ s.label }}</p>
        <p style="font-size:26px; font-weight:800; margin:0;" :style="{color: s.color, fontSize: s.small ? '18px' : '26px'}">{{ s.valor }}</p>
        <p style="font-size:11px; color:#94a3b8; margin:4px 0 0;">{{ s.sub }}</p>
      </div>
    </div>

    <!-- Chart + Próximas citas -->
    <div style="display:grid; grid-template-columns:1.4fr 1fr; gap:12px; margin-bottom:16px;">
      <div style="background:white; border-radius:12px; padding:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <h2 style="font-size:13px; font-weight:700; color:#1e293b; margin:0;">Ingresos últimos 6 meses</h2>
        </div>
        <canvas ref="chartIngresos" height="110"></canvas>
      </div>

      <div style="background:white; border-radius:12px; padding:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <h2 style="font-size:13px; font-weight:700; color:#1e293b; margin:0;">Próximas citas</h2>
          <a href="/odontologia/citas" style="font-size:11px; color:#6C8EBF; font-weight:500; text-decoration:none;">Ver todas</a>
        </div>
        <div v-if="proximas_citas.length === 0" style="text-align:center; padding:24px; color:#94A3B8; font-size:13px;">
          Sin citas próximas
        </div>
        <div v-for="c in proximas_citas" :key="c.id" style="padding:8px 0; border-bottom:1px solid #f1f5f9; display:flex; gap:10px; align-items:center;">
          <div style="width:32px; height:32px; border-radius:50%; background:#EEF2FF; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; color:#6C8EBF; flex-shrink:0;">
            {{ (c.paciente?.nombres || '?')[0] }}{{ (c.paciente?.apellidos || '')[0] }}
          </div>
          <div style="flex:1; min-width:0;">
            <p style="margin:0; font-size:12px; font-weight:600; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ c.paciente?.nombres }} {{ c.paciente?.apellidos }}</p>
            <p style="margin:2px 0 0; font-size:11px; color:#94a3b8;">{{ c.doctor?.nombre }}</p>
          </div>
          <div style="font-size:11px; font-weight:700; color:#6C8EBF; white-space:nowrap;">{{ formatHora(c.fecha_hora) }}</div>
        </div>
      </div>
    </div>

    <!-- Tabla citas hoy -->
    <div style="background:white; border-radius:12px; padding:16px;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <h2 style="font-size:13px; font-weight:700; color:#1e293b; margin:0;">Citas de hoy</h2>
        <span style="font-size:11px; color:#6C8EBF; font-weight:600;">{{ citas_hoy.length }} citas</span>
      </div>
      <div v-if="citas_hoy.length === 0" style="text-align:center; padding:32px; color:#94A3B8; font-size:13px;">
        No hay citas programadas para hoy
      </div>
      <div v-else>
        <div style="display:grid; grid-template-columns:90px 1fr 1fr 1fr 110px; gap:8px; padding:8px 12px; background:#F8FAFC; border-radius:8px; margin-bottom:4px;">
          <span style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Hora</span>
          <span style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Paciente</span>
          <span style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Doctor</span>
          <span style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Motivo</span>
          <span style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Estado</span>
        </div>
        <div v-for="c in citas_hoy" :key="c.id" style="display:grid; grid-template-columns:90px 1fr 1fr 1fr 110px; gap:8px; padding:10px 12px; border-bottom:1px solid #f8fafc; align-items:center;">
          <div style="background:#EEF2FF; color:#5570B0; font-size:12px; font-weight:700; padding:3px 8px; border-radius:6px; text-align:center;">{{ formatHora(c.fecha_hora) }}</div>
          <div>
            <p style="margin:0; font-size:13px; font-weight:600; color:#1e293b;">{{ c.paciente?.nombres }} {{ c.paciente?.apellidos }}</p>
          </div>
          <div style="font-size:13px; color:#64748b;">{{ c.doctor?.nombre }}</div>
          <div style="font-size:13px; color:#64748b;">{{ c.motivo || '-' }}</div>
          <div><span :style="estadoStyle(c.estado)" style="font-size:10px; font-weight:600; padding:3px 8px; border-radius:6px;">{{ c.estado }}</span></div>
        </div>
      </div>
    </div>

  </div>
  </AppLayout>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Chart from 'chart.js/auto'

const props = defineProps({ stats: Object, citas_hoy: Array, proximas_citas: Array, ingresosPorMes: Array })

const chartIngresos = ref(null)

const fechaHoy = new Date().toLocaleDateString('es-PE', { weekday:'long', year:'numeric', month:'long', day:'numeric' })

const statsCards = computed(() => [
  { label: 'Pacientes',    valor: props.stats.pacientes_total, color: '#8B5CF6', sub: 'total registrados' },
  { label: 'Citas hoy',   valor: props.stats.citas_hoy,       color: '#6C8EBF', sub: 'programadas' },
  { label: 'Por atender', valor: props.stats.citas_pendientes, color: '#F59E0B', sub: 'pendientes' },
  { label: 'Ingresos mes', valor: 'S/ ' + Number(props.stats.ingresos_mes || 0).toFixed(2), color: '#10B981', sub: 'este mes', small: true },
  { label: 'Doctores',    valor: props.stats.doctores_activos, color: '#6366F1', sub: 'activos' },
])

const formatHora = (fecha) => new Date(fecha).toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' })
const formatFechaHora = (fecha) => new Date(fecha).toLocaleString('es-PE', { weekday:'short', day:'numeric', month:'short', hour:'2-digit', minute:'2-digit' })

const estadoStyle = (estado) => {
  const map = {
    programada: { background:'#EFF6FF', color:'#1D4ED8' },
    confirmada:  { background:'#F0FDF4', color:'#15803D' },
    en_curso:    { background:'#FFFBEB', color:'#B45309' },
    completada:  { background:'#F0FDF4', color:'#15803D' },
    cancelada:   { background:'#FEF2F2', color:'#B91C1C' },
    no_asistio:  { background:'#F9FAFB', color:'#6B7280' },
  }
  return map[estado] || {}
}

onMounted(() => {
  new Chart(chartIngresos.value, {
    type: 'bar',
    data: {
      labels: props.ingresosPorMes.map(i => i.mes),
      datasets: [{
        label: 'Ingresos S/',
        data: props.ingresosPorMes.map(i => i.total),
        backgroundColor: '#6C8EBF',
        borderRadius: 6,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: { grid: { color: '#F1F5F9' }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
        x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94a3b8' } }
      }
    }
  })
})
</script>
