<template>
  <AppLayout title="Centro Odontológico" subtitle="Resumen de operaciones">
  <div style="padding:24px; max-width:1200px; margin:0 auto;">
    <div style="margin-bottom:24px;">
      <h1 style="font-size:24px; font-weight:700; margin:0;">Centro Odontológico</h1>
      <p style="color:#64748B; margin:4px 0 0;">{{ fechaHoy }}</p>
    </div>

    <!-- Stats -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-bottom:28px;">
      <div v-for="s in statsCards" :key="s.label" style="background:white; border:1px solid #E2E8F0; border-radius:12px; padding:20px;">
        <p style="font-size:12px; color:#64748B; margin:0 0 8px; text-transform:uppercase; font-weight:600;">{{ s.label }}</p>
        <p style="font-size:28px; font-weight:800; margin:0;" :style="{color: s.color}">{{ s.valor }}</p>
      </div>
    </div>

    <!-- Ingresos y proximas citas -->
    <div style="display:grid; grid-template-columns:1.3fr 1fr; gap:16px; margin-bottom:24px;">
      <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; padding:20px;">
        <h2 style="font-size:16px; font-weight:700; margin:0 0 16px;">Ingresos ultimos 6 meses</h2>
        <canvas ref="chartIngresos" height="200"></canvas>
      </div>
      <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; padding:20px;">
        <h2 style="font-size:16px; font-weight:700; margin:0 0 16px;">Proximas citas</h2>
        <div v-if="proximas_citas.length === 0" style="text-align:center; padding:32px; color:#94A3B8; font-size:13px;">
          Sin citas en los proximos dias
        </div>
        <div v-for="c in proximas_citas" :key="c.id" style="padding:8px 0; border-bottom:1px solid #F1F5F9;">
          <p style="margin:0; font-size:13px; font-weight:600;">{{ c.paciente?.nombres }} {{ c.paciente?.apellidos }}</p>
          <p style="margin:2px 0 0; font-size:12px; color:#64748B;">{{ formatFechaHora(c.fecha_hora) }} - {{ c.doctor?.nombre }}</p>
        </div>
      </div>
    </div>

    <!-- Citas de hoy -->
    <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; padding:20px;">
      <h2 style="font-size:16px; font-weight:700; margin:0 0 16px;">Citas de hoy</h2>
      <div v-if="citas_hoy.length === 0" style="text-align:center; padding:32px; color:#94A3B8;">
        No hay citas programadas para hoy
      </div>
      <table v-else style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="background:#F8FAFC;">
            <th style="padding:10px 12px; text-align:left; font-size:12px; color:#64748B;">Hora</th>
            <th style="padding:10px 12px; text-align:left; font-size:12px; color:#64748B;">Paciente</th>
            <th style="padding:10px 12px; text-align:left; font-size:12px; color:#64748B;">Doctor</th>
            <th style="padding:10px 12px; text-align:left; font-size:12px; color:#64748B;">Motivo</th>
            <th style="padding:10px 12px; text-align:left; font-size:12px; color:#64748B;">Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in citas_hoy" :key="c.id" style="border-top:1px solid #F1F5F9;">
            <td style="padding:10px 12px; font-size:13px; font-weight:600;">{{ formatHora(c.fecha_hora) }}</td>
            <td style="padding:10px 12px; font-size:13px;">{{ c.paciente?.nombres }} {{ c.paciente?.apellidos }}</td>
            <td style="padding:10px 12px; font-size:13px; color:#64748B;">{{ c.doctor?.nombre }}</td>
            <td style="padding:10px 12px; font-size:13px; color:#64748B;">{{ c.motivo || '-' }}</td>
            <td style="padding:10px 12px;">
              <span :style="estadoStyle(c.estado)">{{ c.estado }}</span>
            </td>
          </tr>
        </tbody>
      </table>
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
  { label: 'Pacientes', valor: props.stats.pacientes_total, color: '#8B5CF6' },
  { label: 'Citas hoy', valor: props.stats.citas_hoy, color: '#0EA5E9' },
  { label: 'Por atender', valor: props.stats.citas_pendientes, color: '#F59E0B' },
  { label: 'Ingresos mes', valor: 'S/ ' + Number(props.stats.ingresos_mes || 0).toFixed(2), color: '#10B981' },
  { label: 'Doctores', valor: props.stats.doctores_activos, color: '#6366F1' },
])

const formatHora = (fecha) => new Date(fecha).toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' })

const estadoStyle = (estado) => {
  const map = {
    programada:  { background:'#EFF6FF', color:'#1D4ED8', padding:'3px 8px', borderRadius:'6px', fontSize:'11px', fontWeight:'600' },
    confirmada:  { background:'#F0FDF4', color:'#15803D', padding:'3px 8px', borderRadius:'6px', fontSize:'11px', fontWeight:'600' },
    en_curso:    { background:'#FFFBEB', color:'#B45309', padding:'3px 8px', borderRadius:'6px', fontSize:'11px', fontWeight:'600' },
    completada:  { background:'#F0FDF4', color:'#15803D', padding:'3px 8px', borderRadius:'6px', fontSize:'11px', fontWeight:'600' },
    cancelada:   { background:'#FEF2F2', color:'#B91C1C', padding:'3px 8px', borderRadius:'6px', fontSize:'11px', fontWeight:'600' },
    no_asistio:  { background:'#F9FAFB', color:'#6B7280', padding:'3px 8px', borderRadius:'6px', fontSize:'11px', fontWeight:'600' },
  }
  return map[estado] || {}
}

const formatFechaHora = (fecha) => new Date(fecha).toLocaleString('es-PE', { weekday:'short', day:'numeric', month:'short', hour:'2-digit', minute:'2-digit' })

onMounted(() => {
  new Chart(chartIngresos.value, {
    type: 'bar',
    data: {
      labels: props.ingresosPorMes.map(i => i.mes),
      datasets: [{ label: 'Ingresos S/', data: props.ingresosPorMes.map(i => i.total), backgroundColor: '#8B5CF6', borderRadius: 6 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
  })
})
</script>
