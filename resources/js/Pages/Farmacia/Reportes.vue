<template>
    <AppLayout title="Reportes" subtitle="Análisis de ventas">

        <div style="display:flex; gap:8px; margin-bottom:20px;">
            <button @click="tab='ventas'" :style="{padding:'10px 24px', borderRadius:'10px', border:'none', cursor:'pointer', fontSize:'14px', fontWeight:'700', background: tab==='ventas' ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : '#F1F5F9', color: tab==='ventas' ? 'white' : '#475569'}">📊 Ventas</button>
            <button @click="tab='ganancias'" :style="{padding:'10px 24px', borderRadius:'10px', border:'none', cursor:'pointer', fontSize:'14px', fontWeight:'700', background: tab==='ganancias' ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : '#F1F5F9', color: tab==='ganancias' ? 'white' : '#475569'}">💰 Ganancias</button>
        </div>

        <div v-show="tab==='ventas'">

        <!-- Filtros de fecha -->
        <div style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:24px;">
            <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                <div>
                    <label style="font-size:13px; font-weight:600; color:#64748B;">Desde</label>
                    <input v-model="filtros.desde" type="date"
                        style="display:block; padding:8px 12px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; margin-top:4px;"
                        @focus="$event.target.style.borderColor='#14B8A6'"
                        @blur="$event.target.style.borderColor='#E2E8F0'" />
                </div>
                <div>
                    <label style="font-size:13px; font-weight:600; color:#64748B;">Hasta</label>
                    <input v-model="filtros.hasta" type="date"
                        style="display:block; padding:8px 12px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; margin-top:4px;"
                        @focus="$event.target.style.borderColor='#14B8A6'"
                        @blur="$event.target.style.borderColor='#E2E8F0'" />
                </div>
                <div style="margin-top:20px;">
                    <button @click="filtrar"
                        style="padding:10px 24px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                        🔍 Filtrar
                    </button>
                </div>
                <div style="margin-top:20px;">
                    <button @click="setMesActual"
                        style="padding:10px 16px; background:#F0FDFA; color:#0F766E; border-radius:10px; font-size:14px; font-weight:600; border:1px solid #CCFBF1; cursor:pointer;">
                        Este mes
                    </button>
                </div>
                <div style="margin-top:20px;">
                    <button @click="setHoy"
                        style="padding:10px 16px; background:#F0FDFA; color:#0F766E; border-radius:10px; font-size:14px; font-weight:600; border:1px solid #CCFBF1; cursor:pointer;">
                        Hoy
                    </button>
                </div>
                <div style="margin-top:20px;">
                    <a :href="urlExcel" target="_blank"
                        style="display:inline-block; padding:10px 16px; background:linear-gradient(135deg,#10B981,#059669); color:white; border-radius:10px; font-size:14px; font-weight:600; text-decoration:none; cursor:pointer;">
                        📊 Excel
                    </a>
                </div>
                <div style="margin-top:20px;">
                    <a :href="urlPle" target="_blank"
                        style="display:inline-block; padding:10px 16px; background:linear-gradient(135deg,#7C3AED,#6D28D9); color:white; border-radius:10px; font-size:14px; font-weight:600; text-decoration:none; cursor:pointer;">
                        📄 PLE SUNAT
                    </a>
                </div>
            </div>
        </div>

        <!-- KPIs -->
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
            <div style="background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:20px; padding:24px; color:white; box-shadow:0 8px 24px rgba(20,184,166,0.3);">
                <p style="font-size:13px; font-weight:600; opacity:0.8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">💰 Total período</p>
                <p style="font-size:32px; font-weight:900; margin:0; line-height:1;">S/ {{ Number(resumen.total_periodo).toFixed(2) }}</p>
                <p style="font-size:13px; opacity:0.7; margin:10px 0 0;">{{ resumen.total_ventas }} ventas</p>
            </div>
            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <p style="font-size:13px; font-weight:600; color:#94A3B8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">🎫 Ticket promedio</p>
                <p style="font-size:32px; font-weight:900; color:#1E293B; margin:0; line-height:1;">S/ {{ Number(resumen.ticket_promedio).toFixed(2) }}</p>
            </div>
            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <p style="font-size:13px; font-weight:600; color:#94A3B8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">💵 Efectivo</p>
                <p style="font-size:32px; font-weight:900; color:#1E293B; margin:0; line-height:1;">S/ {{ Number(resumen.total_efectivo).toFixed(2) }}</p>
            </div>
            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <p style="font-size:13px; font-weight:600; color:#94A3B8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">📱 Digital</p>
                <p style="font-size:32px; font-weight:900; color:#1E293B; margin:0; line-height:1;">S/ {{ (Number(resumen.total_yape) + Number(resumen.total_plin) + Number(resumen.total_tarjeta)).toFixed(2) }}</p>
            </div>
        </div>

        <!-- Gráfico ventas por día + Métodos de pago -->
        <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:24px;">

            <!-- Ventas por día -->
            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 24px;">📈 Ventas por día</p>
                <div v-if="!ventas_por_dia.length" style="text-align:center; padding:40px 0; color:#CBD5E1;">
                    <p style="font-size:40px; margin:0 0 8px;">📊</p>
                    <p style="font-size:15px;">Sin datos en este período</p>
                </div>
                <div v-else style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#F8FAFC;">
                                <th style="padding:10px 16px; text-align:left; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Fecha</th>
                                <th style="padding:10px 16px; text-align:center; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Ventas</th>
                                <th style="padding:10px 16px; text-align:right; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Total</th>
                                <th style="padding:10px 16px; text-align:left; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Barra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="d in ventas_por_dia" :key="d.fecha" style="border-top:1px solid #F1F5F9;">
                                <td style="padding:10px 16px; font-size:14px; color:#475569; font-weight:500;">{{ formatFecha(d.fecha) }}</td>
                                <td style="padding:10px 16px; text-align:center; font-size:14px; color:#475569;">{{ d.cantidad }}</td>
                                <td style="padding:10px 16px; text-align:right; font-size:14px; font-weight:700; color:#14B8A6;">S/ {{ Number(d.total).toFixed(2) }}</td>
                                <td style="padding:10px 16px; min-width:120px;">
                                    <div style="background:#F1F5F9; border-radius:6px; height:8px; overflow:hidden;">
                                        <div :style="{
                                            width: maxDia > 0 ? (d.total / maxDia * 100) + '%' : '0%',
                                            height: '100%',
                                            background: 'linear-gradient(90deg,#14B8A6,#0F766E)',
                                            borderRadius: '6px',
                                        }"></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Métodos de pago -->
            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 20px;">💳 Métodos de pago</p>
                <div v-if="!metodos_pago.length" style="text-align:center; padding:30px 0; color:#CBD5E1;">
                    <p style="font-size:32px; margin:0 0 8px;">💳</p>
                    <p style="font-size:15px;">Sin datos</p>
                </div>
                <div v-for="m in metodos_pago" :key="m.metodo_pago"
                    style="margin-bottom:16px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:14px; font-weight:600; color:#1E293B; text-transform:capitalize;">
                            {{ iconMetodo(m.metodo_pago) }} {{ m.metodo_pago || 'efectivo' }}
                        </span>
                        <span style="font-size:14px; font-weight:800; color:#14B8A6;">S/ {{ Number(m.total).toFixed(2) }}</span>
                    </div>
                    <div style="background:#F1F5F9; border-radius:8px; height:10px; overflow:hidden;">
                        <div :style="{
                            width: resumen.total_periodo > 0 ? (m.total / resumen.total_periodo * 100) + '%' : '0%',
                            height: '100%',
                            background: 'linear-gradient(90deg,#14B8A6,#0F766E)',
                            borderRadius: '8px',
                        }"></div>
                    </div>
                    <p style="font-size:12px; color:#94A3B8; margin:4px 0 0;">{{ m.cantidad }} transacciones</p>
                </div>
            </div>
        </div>

        <!-- Top productos -->
        <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
            <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 20px;">🏆 Top 10 productos más vendidos</p>
            <div v-if="!top_productos.length" style="text-align:center; padding:40px 0; color:#CBD5E1;">
                <p style="font-size:40px; margin:0 0 8px;">📦</p>
                <p style="font-size:15px;">Sin datos en este período</p>
            </div>
            <table v-else style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">#</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Producto</th>
                        <th style="padding:12px 16px; text-align:center; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Unidades</th>
                        <th style="padding:12px 16px; text-align:right; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(p, i) in top_productos" :key="i" style="border-top:1px solid #F1F5F9;">
                        <td style="padding:12px 16px;">
                            <div :style="{
                                width:'32px', height:'32px', borderRadius:'10px', display:'flex', alignItems:'center', justifyContent:'center',
                                fontSize:'14px', fontWeight:'800',
                                background: i===0?'#FEF3C7':i===1?'#F1F5F9':i===2?'#FEE2E2':'#F0FDF4',
                                color: i===0?'#92400E':i===1?'#475569':i===2?'#991B1B':'#166534',
                            }">{{ i+1 }}</div>
                        </td>
                        <td style="padding:12px 16px; font-size:14px; font-weight:600; color:#1E293B;">{{ p.descripcion }}</td>
                        <td style="padding:12px 16px; text-align:center; font-size:14px; color:#475569;">{{ Number(p.total_cantidad).toFixed(0) }}</td>
                        <td style="padding:12px 16px; text-align:right; font-size:15px; font-weight:800; color:#14B8A6;">S/ {{ Number(p.total_monto).toFixed(2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Ventas por Vendedor -->
        <div v-if="ventas_por_vendedor.length" style="background:white; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden; margin-top:24px;">
            <div style="background:linear-gradient(135deg,#14B8A6,#0F766E); padding:16px 20px; display:flex; align-items:center; gap:10px;">
                <span style="font-size:20px;">👥</span>
                <h3 style="margin:0; color:white; font-size:15px; font-weight:700;">Ventas por Vendedor</h3>
            </div>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">Vendedor</th>
                        <th style="padding:12px 16px; text-align:center; font-size:12px; color:#64748B; font-weight:600;">Rol</th>
                        <th style="padding:12px 16px; text-align:center; font-size:12px; color:#64748B; font-weight:600;">Ventas</th>
                        <th style="padding:12px 16px; text-align:right; font-size:12px; color:#64748B; font-weight:600;">💵 Efectivo</th>
                        <th style="padding:12px 16px; text-align:right; font-size:12px; color:#64748B; font-weight:600;">📱 Yape/Plin</th>
                        <th style="padding:12px 16px; text-align:right; font-size:12px; color:#64748B; font-weight:600;">💳 Tarjeta</th>
                        <th style="padding:12px 16px; text-align:right; font-size:12px; color:#14B8A6; font-weight:700;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="v in ventas_por_vendedor" :key="v.usuario_id" style="border-top:1px solid #F1F5F9;">
                        <td style="padding:12px 16px; font-size:14px; font-weight:600; color:#1E293B;">{{ v.nombre }}</td>
                        <td style="padding:12px 16px; text-align:center;">
                            <span style="font-size:11px; font-weight:600; padding:2px 8px; border-radius:20px; background:#F0FDFA; color:#0F766E;">{{ v.rol }}</span>
                        </td>
                        <td style="padding:12px 16px; text-align:center; font-size:14px; color:#475569;">{{ v.cantidad }}</td>
                        <td style="padding:12px 16px; text-align:right; font-size:13px; color:#475569;">S/ {{ Number(v.efectivo).toFixed(2) }}</td>
                        <td style="padding:12px 16px; text-align:right; font-size:13px; color:#475569;">S/ {{ (Number(v.yape) + Number(v.plin)).toFixed(2) }}</td>
                        <td style="padding:12px 16px; text-align:right; font-size:13px; color:#475569;">S/ {{ Number(v.tarjeta).toFixed(2) }}</td>
                        <td style="padding:12px 16px; text-align:right; font-size:15px; font-weight:800; color:#14B8A6;">S/ {{ Number(v.total).toFixed(2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        </div>

        <div v-show="tab==='ganancias'" v-if="ganancias">
            <div style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap;">
                <button v-for="v in ['dia','semana','mes']" :key="v" @click="cambiarVistaGanancia(v)" :style="{padding:'9px 18px', borderRadius:'10px', border:'none', cursor:'pointer', fontSize:'13px', fontWeight:'700', background: ganancias.vista===v ? '#0F766E' : '#F1F5F9', color: ganancias.vista===v ? 'white' : '#475569'}">{{ v === 'dia' ? 'Por día' : v === 'semana' ? 'Por semana' : 'Por mes' }}</button>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(190px, 1fr)); gap:16px; margin-bottom:24px;">
                <div style="background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:14px; padding:1.2rem 1.4rem; color:white;">
                    <div style="font-size:12px; opacity:.85; text-transform:uppercase;">Ventas</div>
                    <div style="font-size:24px; font-weight:700; margin-top:6px;">S/ {{ fmtG(ganancias.resumen.ventas) }}</div>
                </div>
                <div style="background:white; border:1px solid #FEF3C7; border-radius:14px; padding:1.2rem 1.4rem;">
                    <div style="font-size:12px; color:#94A3B8; text-transform:uppercase;">Costo</div>
                    <div style="font-size:24px; font-weight:700; margin-top:6px; color:#D97706;">S/ {{ fmtG(ganancias.resumen.costo) }}</div>
                </div>
                <div style="background:white; border:1px solid #E2E8F0; border-radius:14px; padding:1.2rem 1.4rem;">
                    <div style="font-size:12px; color:#94A3B8; text-transform:uppercase;">Ganancia</div>
                    <div :style="{fontSize:'24px', fontWeight:700, marginTop:'6px', color: ganancias.resumen.ganancia >= 0 ? '#059669' : '#DC2626'}">S/ {{ fmtG(ganancias.resumen.ganancia) }}</div>
                </div>
            </div>
            <div style="background:white; border:1px solid #E2E8F0; border-radius:14px; padding:1.2rem; overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                    <thead>
                        <tr style="border-bottom:2px solid #F1F5F9; text-align:left; color:#94A3B8; text-transform:uppercase; font-size:11px;">
                            <th style="padding:10px 8px;">Período</th>
                            <th style="padding:10px 8px; text-align:right;">Ventas</th>
                            <th style="padding:10px 8px; text-align:right;">Costo</th>
                            <th style="padding:10px 8px; text-align:right;">Ganancia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="r in ganancias.filas" :key="r.periodo" style="border-bottom:1px solid #F1F5F9;">
                            <td style="padding:11px 8px; font-weight:600; color:#1E293B;">{{ r.periodo }}</td>
                            <td style="padding:11px 8px; text-align:right; color:#0F766E;">S/ {{ fmtG(r.ventas) }}</td>
                            <td style="padding:11px 8px; text-align:right; color:#D97706;">S/ {{ fmtG(r.costo) }}</td>
                            <td :style="{padding:'11px 8px', textAlign:'right', fontWeight:600, color: r.ganancia >= 0 ? '#059669' : '#DC2626'}">S/ {{ fmtG(r.ganancia) }}</td>
                        </tr>
                        <tr v-if="ganancias.filas.length === 0"><td colspan="4" style="padding:20px; text-align:center; color:#94A3B8;">Sin datos</td></tr>
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
    resumen:         { type: Object, default: () => ({}) },
    ventas_por_dia:  { type: Array,  default: () => [] },
    top_productos:   { type: Array,  default: () => [] },
    metodos_pago:    { type: Array,  default: () => [] },
    desde:           { type: String, default: '' },
    ventas_por_vendedor: { type: Array, default: () => [] },
    hasta:           { type: String, default: '' },
    ganancias:       { type: Object, default: null },
})

const filtros = ref({
    desde: props.desde,
    hasta: props.hasta,
})

const urlExcel = computed(() => 
    `/reportes/exportar-excel?desde=${filtros.value.desde}&hasta=${filtros.value.hasta}`
)

const urlPle = computed(() => {
    const fecha = new Date(filtros.value.desde)
    return `/reportes/exportar-ple?mes=${fecha.getMonth()+1}&anio=${fecha.getFullYear()}`
})


const maxDia = computed(() => {
    if (!props.ventas_por_dia.length) return 1
    return Math.max(...props.ventas_por_dia.map(d => d.total)) || 1
})

const iconMetodo = (m) => ({ efectivo: '💵', yape: '📱', plin: '📲', tarjeta: '💳' })[m] || '💵'

const formatFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha + 'T00:00:00').toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const filtrar = () => {
    router.get('/farmacia/reportes', filtros.value, { preserveState: false })
}

const setHoy = () => {
    const hoy = new Date().toISOString().split('T')[0]
    filtros.value = { desde: hoy, hasta: hoy }
    filtrar()
}

const setMesActual = () => {
    const now = new Date()
    const desde = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0]
    const hasta = now.toISOString().split('T')[0]
    filtros.value = { desde, hasta }
    filtrar()
}

// ===== Pestañas Ventas / Ganancias =====
const tab = ref('ventas')
const fmtG = (n) => Number(n || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const cambiarVistaGanancia = (v) => {
    router.get('/farmacia/reportes', { desde: filtros.value.desde, hasta: filtros.value.hasta, vista_ganancia: v }, { preserveState: true, preserveScroll: true, onSuccess: () => { tab.value = 'ganancias' } })
}
</script>