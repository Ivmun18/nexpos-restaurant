<template>
    <AppLayout title="Reporte de mozos y turnos" subtitle="Rendimiento por mozo y turno trabajado">

        <!-- FILTROS -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.2rem 1.5rem; margin-bottom:1.5rem; display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap;">
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Desde</label>
                <input v-model="filtros.desde" type="date" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Hasta</label>
                <input v-model="filtros.hasta" type="date" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Mozo</label>
                <select v-model="filtros.user_id" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                    <option value="">Todos</option>
                    <option v-for="m in mozos" :key="m.id" :value="m.id">{{ m.name }}</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Tipo turno</label>
                <select v-model="filtros.tipo" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                    <option value="">Todos</option>
                    <option value="mañana">Mañana</option>
                    <option value="tarde">Tarde</option>
                    <option value="noche">Noche</option>
                    <option value="completo">Completo</option>
                </select>
            </div>
            <div style="display:flex; gap:8px;">
                <button @click="buscar" style="padding:8px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">Buscar</button>
                <button @click="hoy" style="padding:8px 14px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#64748B; cursor:pointer; background:white;">Hoy</button>
                <button @click="esteMes" style="padding:8px 14px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#64748B; cursor:pointer; background:white;">Este mes</button>
            </div>
        </div>

        <!-- MÉTRICAS -->
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:1.5rem;">
            <div style="background:var(--color-background-secondary,#F8FAFC); border-radius:10px; padding:1rem 1.2rem;">
                <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 6px;">Total vendido</p>
                <p style="font-size:22px; font-weight:800; color:#1E293B; margin:0;">S/ {{ resumen.total_ventas?.toFixed(2) }}</p>
                <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">{{ resumen.total_turnos }} turnos</p>
            </div>
            <div style="background:var(--color-background-secondary,#F8FAFC); border-radius:10px; padding:1rem 1.2rem;">
                <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 6px;">Promedio por turno</p>
                <p style="font-size:22px; font-weight:800; color:#1E293B; margin:0;">S/ {{ resumen.promedio_ventas?.toFixed(2) }}</p>
                <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">por turno trabajado</p>
            </div>
            <div style="background:var(--color-background-secondary,#F8FAFC); border-radius:10px; padding:1rem 1.2rem;">
                <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 6px;">Mesas atendidas</p>
                <p style="font-size:22px; font-weight:800; color:#1E293B; margin:0;">{{ resumen.total_mesas }}</p>
                <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">en el período</p>
            </div>
            <div style="background:var(--color-background-secondary,#F8FAFC); border-radius:10px; padding:1rem 1.2rem;">
                <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 6px;">Turnos trabajados</p>
                <p style="font-size:22px; font-weight:800; color:#1E293B; margin:0;">{{ resumen.total_turnos }}</p>
                <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">{{ mozos.length }} mozos activos</p>
            </div>
        </div>

        <!-- RANKING MOZOS -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.2rem 1.5rem; margin-bottom:1.5rem;">
            <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0 0 1rem;">🏆 Ranking de mozos</p>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                    <thead>
                        <tr style="background:#F8FAFC;">
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">#</th>
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Mozo</th>
                            <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Turnos</th>
                            <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Mesas</th>
                            <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Promedio/turno</th>
                            <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Total vendido</th>
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Horas trabajadas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="por_mozo.length === 0">
                            <td colspan="7" style="padding:2rem; text-align:center; color:#94A3B8;">Sin datos en este período</td>
                        </tr>
                        <tr v-for="(m, i) in por_mozo" :key="i" style="border-top:1px solid #F1F5F9;"
                            @mouseover="e => e.currentTarget.style.background='#F8FAFC'"
                            @mouseleave="e => e.currentTarget.style.background='white'">
                            <td style="padding:12px 16px;">
                                <div :style="{
                                    width:'28px', height:'28px', borderRadius:'50%',
                                    background: i===0 ? '#14B8A6' : i===1 ? '#0F766E' : i===2 ? '#5EEAD4' : '#E2E8F0',
                                    color: i < 3 ? 'white' : '#64748B',
                                    display:'flex', alignItems:'center', justifyContent:'center',
                                    fontSize:'12px', fontWeight:'700'
                                }">{{ i + 1 }}</div>
                            </td>
                            <td style="padding:12px 16px;">
                                <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ m.nombre }}</p>
                                <p style="font-size:11px; color:#94A3B8; margin:0; text-transform:capitalize;">{{ m.rol }}</p>
                            </td>
                            <td style="padding:12px 16px; text-align:center; color:#64748B;">{{ m.turnos }}</td>
                            <td style="padding:12px 16px; text-align:center; color:#64748B;">{{ m.total_mesas }}</td>
                            <td style="padding:12px 16px; text-align:right; color:#0F766E; font-weight:600;">S/ {{ m.promedio_ventas.toFixed(2) }}</td>
                            <td style="padding:12px 16px; text-align:right; font-weight:800; color:#0F766E; font-size:15px;">S/ {{ m.total_ventas.toFixed(2) }}</td>
                            <td style="padding:12px 16px;">
                                <span style="background:#F0FDFA; color:#0F766E; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                                    {{ Math.floor(m.duracion_min / 60) }}h {{ m.duracion_min % 60 }}min
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- POR TIPO DE TURNO -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.2rem 1.5rem; margin-bottom:1.5rem; display:flex; gap:12px; flex-wrap:wrap;">
            <div v-for="(v, tipo) in por_tipo" :key="tipo"
                style="flex:1; min-width:140px; background:#F8FAFC; border-radius:10px; padding:12px 16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#64748B; margin:0; text-transform:capitalize; font-weight:600;">{{ iconTurno(tipo) }} {{ tipo }}</p>
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:6px 0 2px;">S/ {{ v.total_ventas.toFixed(2) }}</p>
                <p style="font-size:11px; color:#94A3B8; margin:0;">{{ v.turnos }} turnos · {{ v.total_mesas }} mesas</p>
            </div>
        </div>

        <!-- DETALLE TURNOS -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
            <div style="padding:1rem 1.5rem; border-bottom:1px solid #F1F5F9; display:flex; align-items:center; justify-content:space-between;">
                <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0;">Detalle de turnos ({{ turnos.length }})</p>
                <input v-model="busqueda" placeholder="Buscar mozo..." style="padding:6px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; width:200px;" />
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                    <thead>
                        <tr style="background:#F8FAFC;">
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Apertura</th>
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Cierre</th>
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Mozo</th>
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Tipo</th>
                            <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Mesas</th>
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Duración</th>
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Estado</th>
                            <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="turnosFiltrados.length === 0">
                            <td colspan="8" style="padding:2rem; text-align:center; color:#94A3B8;">Sin turnos en este período</td>
                        </tr>
                        <tr v-for="t in turnosFiltrados" :key="t.id" style="border-top:1px solid #F1F5F9;"
                            @mouseover="e => e.currentTarget.style.background='#F8FAFC'"
                            @mouseleave="e => e.currentTarget.style.background='white'">
                            <td style="padding:12px 16px; font-size:12px; color:#64748B;">{{ formatFecha(t.apertura) }}</td>
                            <td style="padding:12px 16px; font-size:12px; color:#64748B;">{{ t.cierre ? formatFecha(t.cierre) : '—' }}</td>
                            <td style="padding:12px 16px; font-weight:600; color:#1E293B;">{{ t.user?.name ?? '—' }}</td>
                            <td style="padding:12px 16px;">
                                <span style="background:#F0FDFA; color:#0F766E; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; text-transform:capitalize;">
                                    {{ iconTurno(t.tipo) }} {{ t.tipo }}
                                </span>
                            </td>
                            <td style="padding:12px 16px; text-align:center; color:#64748B;">{{ t.total_mesas }}</td>
                            <td style="padding:12px 16px; font-size:12px; color:#64748B;">{{ t.duracion ?? '—' }}</td>
                            <td style="padding:12px 16px;">
                                <span :style="t.estado === 'abierto'
                                    ? {background:'#F0FDF4', color:'#166534', padding:'3px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'600'}
                                    : {background:'#F1F5F9', color:'#475569', padding:'3px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'600'}">
                                    {{ t.estado === 'abierto' ? '🟢 Activo' : '✅ Cerrado' }}
                                </span>
                            </td>
                            <td style="padding:12px 16px; text-align:right; font-weight:800; color:#0F766E;">S/ {{ Number(t.total_ventas).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    turnos:    { type: Array,  default: () => [] },
    resumen:   { type: Object, default: () => ({}) },
    por_mozo:  { type: Array,  default: () => [] },
    por_tipo:  { type: Object, default: () => ({}) },
    mozos:     { type: Array,  default: () => [] },
    filtros:   { type: Object, default: () => ({}) },
})

const filtros  = ref({ ...props.filtros })
const busqueda = ref('')

const turnosFiltrados = computed(() => {
    if (!busqueda.value) return props.turnos
    const q = busqueda.value.toLowerCase()
    return props.turnos.filter(t => t.user?.name?.toLowerCase().includes(q) || t.tipo?.includes(q))
})

function buscar() {
    const params = new URLSearchParams()
    params.set('desde',   filtros.value.desde   || '')
    params.set('hasta',   filtros.value.hasta   || '')
    params.set('user_id', filtros.value.user_id || '')
    params.set('tipo',    filtros.value.tipo    || '')
    router.visit('/reportes/turnos?' + params.toString(), { preserveScroll: true })
}

function hoy() {
    const d = new Date().toISOString().slice(0, 10)
    filtros.value.desde = d
    filtros.value.hasta = d
    buscar()
}

function esteMes() {
    const now = new Date()
    filtros.value.desde = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0, 10)
    filtros.value.hasta = now.toISOString().slice(0, 10)
    buscar()
}

function formatFecha(f) {
    if (!f) return '—'
    const d = new Date(f)
    return d.toLocaleDateString('es-PE', { day:'2-digit', month:'2-digit', year:'2-digit' }) +
           ' ' + d.toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' })
}

function iconTurno(t) {
    return { mañana: '🌅', tarde: '🌤️', noche: '🌙', completo: '⭐' }[t] ?? '📋'
}
</script>
