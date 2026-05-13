<template>
    <AppLayout title="Reporte de ventas" subtitle="Historial y métricas de ventas del restaurante">

        <!-- FILTROS -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.2rem 1.5rem; margin-bottom:1.5rem; display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap;">
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Desde</label>
                <input v-model="filtros.desde" type="date" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none;" />
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Hasta</label>
                <input v-model="filtros.hasta" type="date" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none;" />
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Método de pago</label>
                <select v-model="filtros.metodo" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none;">
                    <option value="">Todos</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta">Tarjeta</option>
                    <option value="yape">Yape</option>
                    <option value="plin">Plin</option>
                    <option value="transferencia">Transferencia</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Mozo / Cajero</label>
                <select v-model="filtros.mozo_id" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none;">
                    <option value="">Todos</option>
                    <option v-for="m in mozos" :key="m.id" :value="m.id">{{ m.name }}</option>
                </select>
            </div>
            <div style="display:flex; gap:8px;">
                <button @click="buscar" style="padding:8px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                    Buscar
                </button>
                <button @click="hoy" style="padding:8px 14px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#64748B; cursor:pointer; background:white;">Hoy</button>
                <button @click="esteMes" style="padding:8px 14px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#64748B; cursor:pointer; background:white;">Este mes</button>
            </div>
        </div>

        <!-- MÉTRICAS -->
        <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:12px; margin-bottom:1.5rem;">
            <div v-for="(card, i) in tarjetas" :key="i"
                style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.1rem 1.2rem;">
                <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 6px;">{{ card.label }}</p>
                <p style="font-size:22px; font-weight:800; color:#1E293B; margin:0;">{{ card.valor }}</p>
                <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">{{ card.sub }}</p>
            </div>
        </div>

        <!-- CUADRE DE CAJA -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.2rem 1.5rem; margin-bottom:1.5rem;">
            <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0 0 1rem;">📊 Cuadre de caja</p>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

                <!-- Por método de pago -->
                <div>
                    <p style="font-size:11px; font-weight:600; color:#94A3B8; text-transform:uppercase; margin:0 0 10px;">Por método de pago</p>
                    <table style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr style="background:#F8FAFC;">
                                <th style="padding:8px 12px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600;">Método</th>
                                <th style="padding:8px 12px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600;">Ventas</th>
                                <th style="padding:8px 12px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(v, metodo) in por_metodo" :key="metodo" style="border-top:1px solid #F1F5F9;">
                                <td style="padding:10px 12px;">
                                    <span :style="estiloMetodo(metodo)">{{ iconMetodo(metodo) }} {{ metodo }}</span>
                                </td>
                                <td style="padding:10px 12px; text-align:center; color:#64748B;">{{ v.cantidad }}</td>
                                <td style="padding:10px 12px; text-align:right; font-weight:700; color:#0F766E;">S/ {{ v.total.toFixed(2) }}</td>
                            </tr>
                            <tr style="border-top:2px solid #E2E8F0; background:#F8FAFC;">
                                <td style="padding:10px 12px; font-weight:700; color:#1E293B;">TOTAL</td>
                                <td style="padding:10px 12px; text-align:center; font-weight:700; color:#1E293B;">{{ resumen.cantidad_ventas }}</td>
                                <td style="padding:10px 12px; text-align:right; font-weight:800; color:#0F766E; font-size:15px;">S/ {{ resumen.total_ventas?.toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Por tipo de comprobante -->
                <div>
                    <p style="font-size:11px; font-weight:600; color:#94A3B8; text-transform:uppercase; margin:0 0 10px;">Por tipo de comprobante</p>
                    <table style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr style="background:#F8FAFC;">
                                <th style="padding:8px 12px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600;">Comprobante</th>
                                <th style="padding:8px 12px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600;">Ventas</th>
                                <th style="padding:8px 12px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(v, comp) in por_comprobante" :key="comp" style="border-top:1px solid #F1F5F9;">
                                <td style="padding:10px 12px;">
                                    <span :style="estiloComprobante(comp)">{{ iconComprobante(comp) }} {{ comp }}</span>
                                </td>
                                <td style="padding:10px 12px; text-align:center; color:#64748B;">{{ v.cantidad }}</td>
                                <td style="padding:10px 12px; text-align:right; font-weight:700; color:#0F766E;">S/ {{ v.total.toFixed(2) }}</td>
                            </tr>
                            <tr style="border-top:2px solid #E2E8F0; background:#F8FAFC;">
                                <td style="padding:10px 12px; font-weight:700; color:#1E293B;">TOTAL</td>
                                <td style="padding:10px 12px; text-align:center; font-weight:700; color:#1E293B;">{{ resumen.cantidad_ventas }}</td>
                                <td style="padding:10px 12px; text-align:right; font-weight:800; color:#0F766E; font-size:15px;">S/ {{ resumen.total_ventas?.toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- GRÁFICO + TOP MOZOS -->
        <div style="display:grid; grid-template-columns:1fr 300px; gap:16px; margin-bottom:1.5rem;">

            <!-- Gráfico por día -->
            <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.2rem 1.5rem;">
                <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0 0 1rem;">Ventas por día</p>
                <div v-if="por_dia.length === 0" style="text-align:center; color:#94A3B8; padding:2rem; font-size:13px;">Sin datos en este período</div>
                <div v-else style="display:flex; align-items:flex-end; gap:6px; height:140px;">
                    <div v-for="(d, i) in por_dia" :key="i"
                        style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px; height:100%;">
                        <span style="font-size:10px; color:#94A3B8;">S/ {{ d.total }}</span>
                        <div :style="{
                            width:'100%',
                            height: maxDia > 0 ? (d.total / maxDia * 100) + '%' : '4px',
                            background:'linear-gradient(180deg,#14B8A6,#0F766E)',
                            borderRadius:'4px 4px 0 0',
                            minHeight:'4px'
                        }"></div>
                        <span style="font-size:9px; color:#94A3B8; text-align:center;">{{ d.fecha }}</span>
                    </div>
                </div>
            </div>

            <!-- Top mozos -->
            <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.2rem 1.5rem;">
                <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0 0 1rem;">Top mozos / cajeros</p>
                <div v-if="top_mozos.length === 0" style="text-align:center; color:#94A3B8; padding:1rem; font-size:13px;">Sin datos</div>
                <div v-for="(m, i) in top_mozos" :key="i"
                    style="display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid #F1F5F9;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div :style="{
                            width:'28px', height:'28px', borderRadius:'50%',
                            background: i===0 ? '#14B8A6' : i===1 ? '#0F766E' : '#E2E8F0',
                            color: i < 2 ? 'white' : '#64748B',
                            display:'flex', alignItems:'center', justifyContent:'center',
                            fontSize:'11px', fontWeight:'700'
                        }">{{ i + 1 }}</div>
                        <div>
                            <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0;">{{ m.nombre }}</p>
                            <p style="font-size:11px; color:#94A3B8; margin:0;">{{ m.cantidad }} ventas</p>
                        </div>
                    </div>
                    <span style="font-size:14px; font-weight:800; color:#0F766E;">S/ {{ m.total }}</span>
                </div>
            </div>
        </div>

        <!-- MÉTODOS DE PAGO -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.2rem 1.5rem; margin-bottom:1.5rem; display:flex; gap:12px; flex-wrap:wrap;">
            <div v-for="(v, metodo) in por_metodo" :key="metodo"
                style="display:flex; align-items:center; gap:10px; padding:10px 16px; background:#F8FAFC; border-radius:10px; border:1px solid #E2E8F0;">
                <span style="font-size:18px;">{{ iconMetodo(metodo) }}</span>
                <div>
                    <p style="font-size:12px; color:#64748B; margin:0; text-transform:capitalize;">{{ metodo }}</p>
                    <p style="font-size:15px; font-weight:800; color:#1E293B; margin:0;">S/ {{ v.total }}</p>
                    <p style="font-size:10px; color:#94A3B8; margin:0;">{{ v.cantidad }} cobros</p>
                </div>
            </div>
        </div>

        <!-- TABLA VENTAS -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
            <div style="padding:1rem 1.5rem; border-bottom:1px solid #F1F5F9; display:flex; align-items:center; justify-content:space-between;">
                <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0;">Detalle de ventas ({{ ventas.length }})</p>
                <input v-model="busqueda" placeholder="Buscar mesa, mozo..." style="padding:6px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; width:220px;" />
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                    <thead>
                        <tr style="background:#F8FAFC;">
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Fecha / Hora</th>
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Mesa</th>
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Mozo / Cajero</th>
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Método</th>
                            <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Comprobante</th>
                            <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Total</th>
                            <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Pagó</th>
                            <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Vuelto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="ventasFiltradas.length === 0">
                            <td colspan="7" style="padding:2rem; text-align:center; color:#94A3B8;">Sin ventas en este período</td>
                        </tr>
                        <tr v-for="v in ventasFiltradas" :key="v.id"
                            style="border-top:1px solid #F1F5F9; transition:background .15s;"
                            @mouseover="e => e.currentTarget.style.background='#F8FAFC'"
                            @mouseleave="e => e.currentTarget.style.background='white'">
                            <td style="padding:12px 16px; color:#64748B;">{{ formatFecha(v.created_at) }}</td>
                            <td style="padding:12px 16px;">
                                <span style="background:#F0FDF4; color:#166534; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                                    Mesa {{ v.mesa?.numero ?? '—' }}
                                </span>
                                <span style="font-size:11px; color:#94A3B8; margin-left:6px;">{{ v.mesa?.zona ?? '' }}</span>
                            </td>
                            <td style="padding:12px 16px; font-weight:600; color:#1E293B;">{{ v.user?.name ?? '—' }}</td>
                            <td style="padding:12px 16px;">
                                <span :style="estiloMetodo(v.metodo_pago)">
                                    {{ iconMetodo(v.metodo_pago) }} {{ v.metodo_pago }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;">
                                <span :style="estiloComprobante(v.tipo_comprobante)">
                                    {{ iconComprobante(v.tipo_comprobante) }} {{ v.tipo_comprobante || 'ninguno' }}
                                </span>
                            </td>
                            <td style="padding:12px 16px; text-align:right; font-weight:800; color:#0F766E; font-size:14px;">S/ {{ Number(v.total).toFixed(2) }}</td>
                            <td style="padding:12px 16px; text-align:right; color:#1E293B;">S/ {{ Number(v.monto_pagado).toFixed(2) }}</td>
                            <td style="padding:12px 16px; text-align:right; color:#94A3B8;">S/ {{ Number(v.vuelto).toFixed(2) }}</td>
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
    ventas:     { type: Array,  default: () => [] },
    resumen:    { type: Object, default: () => ({}) },
    por_metodo:      { type: Object, default: () => ({}) },
    por_comprobante: { type: Object, default: () => ({}) },
    por_dia:    { type: Array,  default: () => [] },
    top_mozos:  { type: Array,  default: () => [] },
    mozos:      { type: Array,  default: () => [] },
    filtros:    { type: Object, default: () => ({}) },
})

const filtros = ref({ ...props.filtros })
const busqueda = ref('')

const tarjetas = computed(() => [
    { label: 'Total vendido',   valor: 'S/ ' + props.resumen.total_ventas?.toFixed(2),    sub: props.resumen.cantidad_ventas + ' ventas' },
    { label: 'Total cobrado',   valor: 'S/ ' + props.resumen.total_cobrado?.toFixed(2),   sub: 'Monto recibido' },
    { label: 'Total vuelto',    valor: 'S/ ' + props.resumen.total_vuelto?.toFixed(2),    sub: 'Devuelto a clientes' },
    { label: 'Ticket promedio', valor: 'S/ ' + props.resumen.ticket_promedio?.toFixed(2), sub: 'Por venta' },
    { label: 'N° de ventas',    valor: props.resumen.cantidad_ventas,                     sub: 'En el período' },
])

const maxDia = computed(() => Math.max(...props.por_dia.map(d => d.total), 0))

const ventasFiltradas = computed(() => {
    if (!busqueda.value) return props.ventas
    const q = busqueda.value.toLowerCase()
    return props.ventas.filter(v =>
        v.mesa?.numero?.toString().includes(q) ||
        v.user?.name?.toLowerCase().includes(q) ||
        v.metodo_pago?.includes(q)
    )
})

function buscar() {
    const params = new URLSearchParams()
    params.set('desde',   filtros.value.desde   || '')
    params.set('hasta',   filtros.value.hasta   || '')
    params.set('metodo',  filtros.value.metodo  || '')
    params.set('mozo_id', filtros.value.mozo_id || '')
    const url = '/reportes-restaurante?' + params.toString()
    router.visit(url, { preserveScroll: true })
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
    return d.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: '2-digit' }) +
           ' ' + d.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })
}

function iconMetodo(m) {
    return { efectivo: '💵', tarjeta: '💳', yape: '📱', plin: '📲', transferencia: '🏦' }[m] ?? '💰'
}

function iconComprobante(c) {
    return { boleta: '🧾', factura: '📄', ninguno: '—' }[c] ?? '—'
}

function estiloComprobante(c) {
    const map = {
        boleta:  { background: '#EFF6FF', color: '#1D4ED8' },
        factura: { background: '#F0FDF4', color: '#166534' },
        ninguno: { background: '#F1F5F9', color: '#64748B' },
    }
    return { ...(map[c] || map.ninguno), fontSize: '12px', padding: '3px 10px', borderRadius: '20px', fontWeight: '600', textTransform: 'capitalize' }
}

function estiloMetodo(m) {
    const map = {
        efectivo:      { background: '#F0FDF4', color: '#166534' },
        tarjeta:       { background: '#EFF6FF', color: '#1D4ED8' },
        yape:          { background: '#F5F3FF', color: '#6D28D9' },
        plin:          { background: '#ECFDF5', color: '#065F46' },
        transferencia: { background: '#FFF7ED', color: '#9A3412' },
    }
    return { ...(map[m] || { background: '#F1F5F9', color: '#475569' }), fontSize: '12px', padding: '3px 10px', borderRadius: '20px', fontWeight: '600', textTransform: 'capitalize' }
}
</script>
