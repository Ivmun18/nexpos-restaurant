<template>
    <AppLayout title="Reporte de Mozos" subtitle="Ventas cobradas por mozo (pedidos pagados, sin anulados)">

        <!-- FILTROS -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.2rem 1.5rem; margin-bottom:1.5rem; display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap;">
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Desde</label>
                <input v-model="filtros.fecha_desde" type="date" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Hasta</label>
                <input v-model="filtros.fecha_hasta" type="date" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Local</label>
                <select v-model="filtros.sucursal_id" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                    <option value="">Todos los locales</option>
                    <option v-for="s in sucursales" :key="s.id" :value="s.id">{{ s.nombre }}</option>
                </select>
            </div>
            <div style="display:flex; gap:8px;">
                <button @click="buscar" style="padding:8px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">Buscar</button>
                <button @click="hoy" style="padding:8px 14px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#64748B; cursor:pointer; background:white;">Hoy</button>
                <button @click="esteMes" style="padding:8px 14px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#64748B; cursor:pointer; background:white;">Este mes</button>
            </div>
        </div>

        <!-- MÉTRICAS -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-bottom:1.5rem;">
            <div style="background:var(--color-background-secondary,#F8FAFC); border-radius:10px; padding:1rem 1.2rem;">
                <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 6px;">Total vendido</p>
                <p style="font-size:22px; font-weight:800; color:#1E293B; margin:0;">S/ {{ resumen.total_vendido?.toFixed(2) }}</p>
                <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">en el período</p>
            </div>
            <div style="background:var(--color-background-secondary,#F8FAFC); border-radius:10px; padding:1rem 1.2rem;">
                <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 6px;">Pedidos cobrados</p>
                <p style="font-size:22px; font-weight:800; color:#1E293B; margin:0;">{{ resumen.num_pedidos }}</p>
                <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">{{ reporte.length }} mozos con ventas</p>
            </div>
            <div style="background:var(--color-background-secondary,#F8FAFC); border-radius:10px; padding:1rem 1.2rem;">
                <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 6px;">Mozo top</p>
                <p style="font-size:22px; font-weight:800; color:#1E293B; margin:0;">{{ resumen.mozo_top ?? '—' }}</p>
                <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">mayor venta del período</p>
            </div>
        </div>

        <!-- TABLA POR MOZO -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
            <div style="padding:1rem 1.5rem; border-bottom:1px solid #F1F5F9;">
                <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0;">Ranking de mozos ({{ reporte.length }})</p>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                    <thead>
                        <tr style="background:#F8FAFC;">
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">#</th>
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Mozo</th>
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Local</th>
                            <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Pedidos</th>
                            <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Mesas</th>
                            <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Ticket prom.</th>
                            <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Total vendido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="reporte.length === 0">
                            <td colspan="7" style="padding:2rem; text-align:center; color:#94A3B8;">Sin ventas cobradas en este período</td>
                        </tr>
                        <tr v-for="(m, i) in reporte" :key="m.mozo_id" style="border-top:1px solid #F1F5F9; cursor:pointer;"
                            @click="verDetalle(m)"
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
                            <td style="padding:12px 16px; font-weight:600; color:#1E293B;">{{ m.nombre }}</td>
                            <td style="padding:12px 16px; color:#64748B;">{{ m.local }}</td>
                            <td style="padding:12px 16px; text-align:center; color:#64748B;">{{ m.num_pedidos }}</td>
                            <td style="padding:12px 16px; text-align:center; color:#64748B;">{{ m.num_mesas }}</td>
                            <td style="padding:12px 16px; text-align:right; color:#0F766E; font-weight:600;">S/ {{ m.ticket_promedio.toFixed(2) }}</td>
                            <td style="padding:12px 16px; text-align:right; font-weight:800; color:#0F766E; font-size:15px;">S/ {{ m.total_vendido.toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL DETALLE -->
        <div v-if="detalleAbierto" style="position:fixed; inset:0; background:rgba(15,23,42,0.5); display:flex; align-items:flex-start; justify-content:center; padding:2rem 1rem; z-index:100; overflow-y:auto;" @click.self="cerrarDetalle">
            <div style="background:white; border-radius:12px; width:100%; max-width:720px; max-height:90vh; display:flex; flex-direction:column;">
                <div style="padding:1rem 1.5rem; border-bottom:1px solid #F1F5F9; display:flex; align-items:center; justify-content:space-between;">
                    <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0;">Pedidos de {{ detalle?.mozo?.nombre }}</p>
                    <button @click="cerrarDetalle" style="background:none; border:none; font-size:20px; color:#94A3B8; cursor:pointer; line-height:1;">&times;</button>
                </div>
                <div style="overflow:auto; padding:0.5rem 0;">
                    <div v-if="cargandoDetalle" style="padding:2rem; text-align:center; color:#94A3B8;">Cargando...</div>
                    <table v-else style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr style="background:#F8FAFC;">
                                <th style="padding:8px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Fecha/hora</th>
                                <th style="padding:8px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Mesa</th>
                                <th style="padding:8px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Estado</th>
                                <th style="padding:8px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Total cobrado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="detalle?.pedidos?.length === 0">
                                <td colspan="4" style="padding:2rem; text-align:center; color:#94A3B8;">Sin pedidos en este período</td>
                            </tr>
                            <tr v-for="p in detalle?.pedidos" :key="p.id" style="border-top:1px solid #F1F5F9;">
                                <td style="padding:10px 16px; color:#64748B;">{{ formatFecha(p.fecha) }}</td>
                                <td style="padding:10px 16px; color:#1E293B;">Mesa {{ p.mesa }}</td>
                                <td style="padding:10px 16px;">
                                    <span style="background:#F0FDFA; color:#0F766E; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; text-transform:capitalize;">{{ p.estado }}</span>
                                </td>
                                <td style="padding:10px 16px; text-align:right; font-weight:700; color:#0F766E;">S/ {{ p.total.toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    reporte:    { type: Array,  default: () => [] },
    resumen:    { type: Object, default: () => ({}) },
    sucursales: { type: Array,  default: () => [] },
    filtros:    { type: Object, default: () => ({}) },
})

const filtros = ref({ ...props.filtros })

const detalleAbierto  = ref(false)
const cargandoDetalle = ref(false)
const detalle          = ref(null)

function buscar() {
    const params = new URLSearchParams()
    params.set('fecha_desde', filtros.value.fecha_desde || '')
    params.set('fecha_hasta', filtros.value.fecha_hasta || '')
    params.set('sucursal_id', filtros.value.sucursal_id || '')
    router.visit('/reportes/mozos?' + params.toString(), { preserveScroll: true })
}

function hoy() {
    const d = new Date().toISOString().slice(0, 10)
    filtros.value.fecha_desde = d
    filtros.value.fecha_hasta = d
    buscar()
}

function esteMes() {
    const now = new Date()
    filtros.value.fecha_desde = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0, 10)
    filtros.value.fecha_hasta = now.toISOString().slice(0, 10)
    buscar()
}

async function verDetalle(mozo) {
    detalleAbierto.value  = true
    cargandoDetalle.value = true
    detalle.value          = null
    try {
        const { data } = await axios.get(`/reportes/mozos/${mozo.mozo_id}`, {
            params: {
                fecha_desde: filtros.value.fecha_desde,
                fecha_hasta: filtros.value.fecha_hasta,
                sucursal_id: filtros.value.sucursal_id,
            },
        })
        detalle.value = data
    } finally {
        cargandoDetalle.value = false
    }
}

function cerrarDetalle() {
    detalleAbierto.value = false
    detalle.value = null
}

function formatFecha(f) {
    if (!f) return '—'
    const d = new Date(f)
    return d.toLocaleDateString('es-PE', { day:'2-digit', month:'2-digit', year:'2-digit' }) +
           ' ' + d.toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' })
}
</script>
