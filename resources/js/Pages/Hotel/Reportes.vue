<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    reservas: Array,
    totalIngresos: Number,
    totalReservas: Number,
    ocupacionPromedio: Number,
    desde: String,
    hasta: String,
    totalNoches: Number,
    promedioNoche: Number,
    pagos: Array,
    ingresosPorMes: Array,
    ocupacionPorMes: Array,
    totalHabitaciones: Number,
    ingresosHospedaje: Number,
    ingresosRoomService: Number,
    topProductos: Array,
})

const maxIngreso = computed(() => {
    if (!props.ingresosPorMes?.length) return 1
    return Math.max(...props.ingresosPorMes.map(m => m.ingresos), 1)
})

const desde = ref(props.desde)
const hasta = ref(props.hasta)
const buscar = () => router.get('/hotel/reportes', { desde: desde.value, hasta: hasta.value })

const exportarPDF = () => {
    const t = props
    const filas = props.reservas.map((r, i) => `
        <tr style="border-bottom:1px solid #eee;">
            <td>${i+1}</td>
            <td>${r.codigo}</td>
            <td>${r.huesped?.nombre_completo || '-'}</td>
            <td>${r.huesped?.numero_documento || '-'}</td>
            <td>Hab. ${r.habitacion?.numero} — ${r.habitacion?.tipo?.nombre}</td>
            <td>${new Date(r.fecha_checkin).toLocaleDateString('es-PE')}</td>
            <td>${new Date(r.fecha_checkout_previsto).toLocaleDateString('es-PE')}</td>
            <td>${r.num_noches}</td>
            <td>S/ ${Number(r.precio_noche).toFixed(2)}</td>
            <td>S/ ${Number(r.total).toFixed(2)}</td>
            <td>S/ ${Number(r.monto_pagado).toFixed(2)}</td>
            <td>${r.estado_pago?.toUpperCase()}</td>
        </tr>`).join('')

    const html = `<html><head><title>Reporte Hotel</title>
    <style>
        body{font-family:Arial,sans-serif;padding:20px;font-size:12px;}
        h2{text-align:center;color:#1E293B;}
        .subtitulo{text-align:center;color:#64748B;margin-bottom:20px;}
        .resumen{display:flex;gap:20px;margin-bottom:20px;}
        .card{border:1px solid #E2E8F0;border-radius:8px;padding:12px 20px;flex:1;text-align:center;}
        .card-valor{font-size:20px;font-weight:700;color:#3B82F6;}
        table{width:100%;border-collapse:collapse;}
        th{background:#1E293B;color:#fff;padding:8px;text-align:left;font-size:11px;}
        td{padding:7px 8px;font-size:11px;}
        tr:nth-child(even){background:#F8FAFC;}
        .total-row{font-weight:700;background:#EFF6FF;}
        @media print{button{display:none}}
    </style></head><body>
    <h2>🏨 NEXPOS HOTEL — Reporte de Estadías</h2>
    <p class="subtitulo">Período: ${props.desde} al ${props.hasta}</p>
    <div class="resumen">
        <div class="card"><div>Total Reservas</div><div class="card-valor">${props.totalReservas}</div></div>
        <div class="card"><div>Ingresos Totales</div><div class="card-valor">S/ ${Number(props.totalIngresos).toFixed(2)}</div></div>
        <div class="card"><div>Ocupación</div><div class="card-valor">${props.ocupacionPromedio}%</div></div>
    </div>
    <table>
        <thead><tr>
            <th>N°</th><th>Código</th><th>Huésped</th><th>Documento</th><th>Habitación</th>
            <th>Check-in</th><th>Check-out</th><th>Noches</th><th>Precio/noche</th><th>Total</th><th>Pagado</th><th>Estado</th>
        </tr></thead>
        <tbody>${filas}</tbody>
        <tfoot><tr class="total-row">
            <td colspan="9" style="text-align:right;">TOTAL:</td>
            <td>S/ ${Number(props.reservas.reduce((a,r)=>a+Number(r.total),0)).toFixed(2)}</td>
            <td>S/ ${Number(props.totalIngresos).toFixed(2)}</td>
            <td></td>
        </tr></tfoot>
    </table>
    <p style="margin-top:30px;font-size:10px;color:#94A3B8;text-align:center;">Generado el ${new Date().toLocaleString('es-PE')}</p>
    </body></html>`

    const ventana = window.open('', '_blank', 'width=1000,height=700')
    ventana.document.write(html)
    ventana.document.close()
    ventana.focus()
    setTimeout(() => ventana.print(), 500)
}
</script>
<template>
    <AppLayout title="Reportes Hotel">
        <div style="padding:24px; font-family:'Inter',sans-serif;">
            <h1 style="font-size:24px; font-weight:700; color:#1E293B; margin:0 0 24px;">📊 Reportes Hotel</h1>
            <div style="display:flex; gap:12px; align-items:flex-end; margin-bottom:24px; flex-wrap:wrap;">
                <div><label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Desde</label>
                    <input type="date" v-model="desde" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px;" />
                </div>
                <div><label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Hasta</label>
                    <input type="date" v-model="hasta" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px;" />
                </div>
                <button @click="buscar" style="background:#3B82F6; color:#fff; border:none; padding:9px 20px; border-radius:8px; font-weight:600; cursor:pointer;">🔍 Buscar</button>
                <a :href="'/hotel/reportes/pdf?desde=' + desde + '&hasta=' + hasta" style="background:#DC2626; color:#fff; border:none; padding:9px 20px; border-radius:8px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block;">📄 Descargar PDF</a>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:12px; margin-bottom:16px;">
                <div style="background:#fff; border-radius:12px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #3B82F6;">
                    <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Reservas</div>
                    <div style="font-size:28px; font-weight:700; color:#1E293B;">{{ totalReservas }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #10B981;">
                    <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Ingresos</div>
                    <div style="font-size:22px; font-weight:700; color:#10B981;">S/ {{ Number(totalIngresos).toFixed(2) }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #8B5CF6;">
                    <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Ocupación</div>
                    <div style="font-size:28px; font-weight:700; color:#8B5CF6;">{{ ocupacionPromedio }}%</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #F59E0B;">
                    <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Noches</div>
                    <div style="font-size:28px; font-weight:700; color:#D97706;">{{ totalNoches }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #14B8A6;">
                    <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Prom/noche</div>
                    <div style="font-size:20px; font-weight:700; color:#0F766E;">S/ {{ Number(promedioNoche).toFixed(2) }}</div>
                </div>
            </div>

            <!-- Gráfico ingresos por mes -->
            <div style="background:white; border-radius:14px; padding:18px; border:1px solid #E2E8F0; margin-bottom:14px;">
                <div style="font-size:14px; font-weight:700; color:#1E293B; margin-bottom:14px;">📈 Ingresos últimos 6 meses</div>
                <div style="display:flex; align-items:flex-end; gap:8px; height:120px;">
                    <div v-for="m in ingresosPorMes" :key="m.mes"
                        style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px; height:100%;">
                        <span style="font-size:9px; color:#64748B; font-weight:600; white-space:nowrap;">{{ Number(m.ingresos).toFixed(0) }}</span>
                        <div style="flex:1; width:100%; display:flex; align-items:flex-end;">
                            <div :style="{
                                width:'100%',
                                height: maxIngreso > 0 ? Math.max((m.ingresos / maxIngreso * 90), 3) + 'px' : '3px',
                                background:'linear-gradient(180deg,#14B8A6,#0F766E)',
                                borderRadius:'4px 4px 0 0'
                            }"></div>
                        </div>
                        <span style="font-size:9px; color:#94A3B8; white-space:nowrap;">{{ m.mes }}</span>
                    </div>
                </div>
            </div>

            <!-- Gráfico ocupación por mes -->
            <div style="background:white; border-radius:14px; padding:18px; border:1px solid #E2E8F0; margin-bottom:14px;">
                <div style="font-size:14px; font-weight:700; color:#1E293B; margin-bottom:14px;">🏨 Ocupación últimos 6 meses</div>
                <div style="display:flex; align-items:flex-end; gap:8px; height:120px;">
                    <div v-for="m in ocupacionPorMes" :key="m.mes"
                        style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px; height:100%;">
                        <span style="font-size:9px; color:#64748B; font-weight:600;">{{ m.ocupacion }}%</span>
                        <div style="flex:1; width:100%; display:flex; align-items:flex-end;">
                            <div :style="{
                                width:'100%',
                                height: Math.max(m.ocupacion, 3) + 'px',
                                background: m.ocupacion > 70 ? 'linear-gradient(180deg,#EF4444,#DC2626)' : m.ocupacion > 40 ? 'linear-gradient(180deg,#F59E0B,#D97706)' : 'linear-gradient(180deg,#3B82F6,#1D4ED8)',
                                borderRadius:'4px 4px 0 0'
                            }"></div>
                        </div>
                        <span style="font-size:9px; color:#94A3B8; white-space:nowrap;">{{ m.mes }}</span>
                    </div>
                </div>
                <div style="display:flex; gap:12px; margin-top:8px; font-size:10px; color:#64748B;">
                    <span>🔵 Baja</span><span>🟡 Media</span><span>🔴 Alta</span>
                </div>
            </div>

            <!-- Por método de pago -->
            <div v-if="pagos?.length" style="background:white; border-radius:14px; padding:18px; border:1px solid #E2E8F0; margin-bottom:14px;">
                <div style="font-size:14px; font-weight:700; color:#1E293B; margin-bottom:12px;">💳 Por método de pago</div>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <div v-for="p in pagos" :key="p.metodo_pago" style="display:flex; align-items:center; gap:10px;">
                        <span style="font-size:12px; font-weight:600; color:#1E293B; width:90px; flex-shrink:0; text-transform:capitalize;">{{ p.metodo_pago }}</span>
                        <div style="flex:1; background:#F1F5F9; border-radius:6px; height:18px; overflow:hidden;">
                            <div :style="{
                                width: totalIngresos > 0 ? (p.total / totalIngresos * 100) + '%' : '0%',
                                height:'100%',
                                background:'linear-gradient(90deg,#14B8A6,#0F766E)',
                                borderRadius:'6px'
                            }"></div>
                        </div>
                        <span style="font-size:12px; font-weight:700; color:#0F766E; width:75px; text-align:right; flex-shrink:0;">S/ {{ Number(p.total).toFixed(2) }}</span>
                    </div>
                </div>
            </div>
            <div style="background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.08); overflow:hidden;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead><tr style="background:#F8FAFC;">
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">CÓDIGO</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">HUÉSPED</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">HABITACIÓN</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">CHECK-IN</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">NOCHES</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">TOTAL</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">ESTADO</th>
                    </tr></thead>
                    <tbody>
                        <tr v-if="reservas.length === 0"><td colspan="7" style="padding:40px; text-align:center; color:#94A3B8;">Sin reservas en el período</td></tr>
                        <tr v-for="r in reservas" :key="r.id" style="border-top:1px solid #F1F5F9;">
                            <td style="padding:12px 16px; font-weight:600; font-size:13px;">{{ r.codigo }}</td>
                            <td style="padding:12px 16px; font-size:13px;">{{ r.huesped?.nombre_completo }}</td>
                            <td style="padding:12px 16px; font-size:13px;">Hab. {{ r.habitacion?.numero }}</td>
                            <td style="padding:12px 16px; font-size:12px;">{{ new Date(r.fecha_checkin).toLocaleDateString('es-PE') }}</td>
                            <td style="padding:12px 16px; font-size:13px; text-align:center;">{{ r.num_noches }}</td>
                            <td style="padding:12px 16px; font-size:13px; font-weight:600;">S/ {{ Number(r.total).toFixed(2) }}</td>
                            <td style="padding:12px 16px; font-size:12px;">{{ r.estado }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>

            <!-- Desglose hospedaje vs room service -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">

                <!-- Dona de ingresos -->
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <h3 style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 16px;">🍩 Desglose de Ingresos</h3>
                    <div style="display:flex; align-items:center; gap:20px;">
                        <div style="position:relative; width:100px; height:100px; flex-shrink:0;">
                            <svg viewBox="0 0 36 36" style="width:100px; height:100px; transform:rotate(-90deg);">
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#E2E8F0" stroke-width="3.8"/>
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#3B82F6" stroke-width="3.8"
                                    :stroke-dasharray="(ingresosHospedaje + ingresosRoomService) > 0 ? (ingresosHospedaje / (ingresosHospedaje + ingresosRoomService) * 100) + ' 100' : '0 100'"
                                    stroke-linecap="round"/>
                                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#F59E0B" stroke-width="3.8"
                                    :stroke-dasharray="(ingresosHospedaje + ingresosRoomService) > 0 ? (ingresosRoomService / (ingresosHospedaje + ingresosRoomService) * 100) + ' 100' : '0 100'"
                                    :stroke-dashoffset="(ingresosHospedaje + ingresosRoomService) > 0 ? -(ingresosHospedaje / (ingresosHospedaje + ingresosRoomService) * 100) : 0"
                                    stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div style="flex:1;">
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                                <div style="width:12px; height:12px; border-radius:3px; background:#3B82F6; flex-shrink:0;"></div>
                                <div style="flex:1;">
                                    <div style="font-size:12px; color:#374151; font-weight:600;">🛏️ Hospedaje</div>
                                    <div style="font-size:14px; font-weight:800; color:#1E293B;">S/ {{ Number(ingresosHospedaje||0).toFixed(2) }}</div>
                                </div>
                                <div style="font-size:12px; font-weight:700; color:#3B82F6;">
                                    {{ (ingresosHospedaje + ingresosRoomService) > 0 ? Math.round(ingresosHospedaje / (ingresosHospedaje + ingresosRoomService) * 100) : 0 }}%
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <div style="width:12px; height:12px; border-radius:3px; background:#F59E0B; flex-shrink:0;"></div>
                                <div style="flex:1;">
                                    <div style="font-size:12px; color:#374151; font-weight:600;">🛒 Room Service</div>
                                    <div style="font-size:14px; font-weight:800; color:#1E293B;">S/ {{ Number(ingresosRoomService||0).toFixed(2) }}</div>
                                </div>
                                <div style="font-size:12px; font-weight:700; color:#F59E0B;">
                                    {{ (ingresosHospedaje + ingresosRoomService) > 0 ? Math.round(ingresosRoomService / (ingresosHospedaje + ingresosRoomService) * 100) : 0 }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top productos -->
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <h3 style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 16px;">🏆 Top Productos Room Service</h3>
                    <div v-if="!topProductos || topProductos.length === 0" style="text-align:center; color:#94A3B8; font-size:13px; padding:20px 0;">
                        Sin cargos de room service en el período
                    </div>
                    <div v-for="(p, i) in topProductos" :key="p.producto_id" style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                        <div :style="{width:'22px', height:'22px', borderRadius:'50%', background: ['#3B82F6','#10B981','#F59E0B','#8B5CF6','#EF4444'][i]+'20', color: ['#3B82F6','#10B981','#F59E0B','#8B5CF6','#EF4444'][i], display:'flex', alignItems:'center', justifyContent:'center', fontSize:'11px', fontWeight:'900', flexShrink:0}">
                            {{ i+1 }}
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:12px; font-weight:600; color:#1E293B; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ p.producto?.nombre || 'Producto' }}</div>
                            <div style="background:#F1F5F9; border-radius:4px; height:5px; margin-top:4px;">
                                <div :style="{width: (topProductos[0].total_monto > 0 ? p.total_monto/topProductos[0].total_monto*100 : 0)+'%', height:'5px', borderRadius:'4px', background: ['#3B82F6','#10B981','#F59E0B','#8B5CF6','#EF4444'][i]}"></div>
                            </div>
                        </div>
                        <div style="text-align:right; flex-shrink:0;">
                            <div style="font-size:12px; font-weight:700; color:#1E293B;">S/ {{ Number(p.total_monto).toFixed(2) }}</div>
                            <div style="font-size:10px; color:#94A3B8;">{{ p.total_cantidad }} unid.</div>
                        </div>
                    </div>
                </div>
            </div>

</template>
