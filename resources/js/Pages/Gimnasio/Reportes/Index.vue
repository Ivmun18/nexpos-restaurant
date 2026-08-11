<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    desde: String, hasta: String,
    totalIngresos: Number, totalPagos: Number, ticketPromedio: Number,
    porPlan: Array, porMetodo: Array, porDia: Array, porMes: Array,
    miembrosNuevos: Number, totalAccesos: Number,
    ingresosSesion: Number, ingresosMembresia: Number,
    ingresosHoy: Number, ingresosSemana: Number, ingresosMesAct: Number,
    pagos: Array,
})

const desde = ref(props.desde)
const hasta = ref(props.hasta)
const filtrar = () => router.get('/gimnasio/reportes', { desde: desde.value, hasta: hasta.value }, { preserveState: true })

const money = (n) => 'S/ ' + Number(n||0).toFixed(2)
const fmt = (f) => f ? new Date(f+'T00:00:00').toLocaleDateString('es-PE') : '-'
const metodoBadge = (m) => ({
    efectivo:      { bg:'#F0FDF4', color:'#16A34A', label:'💵 Efectivo' },
    yape:          { bg:'#F5F3FF', color:'#7C3AED', label:'📱 Yape' },
    plin:          { bg:'#EFF6FF', color:'#3B82F6', label:'📱 Plin' },
    transferencia: { bg:'#FEF9C3', color:'#CA8A04', label:'🏦 Transferencia' },
    tarjeta:       { bg:'#FEF2F2', color:'#DC2626', label:'💳 Tarjeta' },
}[m] || { bg:'#F1F5F9', color:'#64748B', label: m })

const imprimirRecibo = async (pago) => {
    try {
        const res = await fetch('/gimnasio/miembros/pago/' + pago.id + '/recibo', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        const d = await res.json()
        const logoHtml = d.empresa.logo ? `<img src="${d.empresa.logo}" style="height:50px;margin-bottom:6px;">` : ''
        const periodo = d.pago.periodo_inicio && d.pago.periodo_fin
            ? `<div class="fila"><span>Período:</span><b>${new Date(d.pago.periodo_inicio+'T00:00:00').toLocaleDateString('es-PE')} al ${new Date(d.pago.periodo_fin+'T00:00:00').toLocaleDateString('es-PE')}</b></div>`
            : ''
        const html = `<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Recibo #${d.pago.id}</title>
        <style>
            *{margin:0;padding:0;box-sizing:border-box;}
            body{font-family:'Courier New',monospace;font-size:12px;color:#1a1a1a;}
            .ticket{width:300px;margin:0 auto;padding:20px 16px;}
            .header{text-align:center;border-bottom:2px dashed #ccc;padding-bottom:12px;margin-bottom:12px;}
            .gym-name{font-size:16px;font-weight:900;text-transform:uppercase;}
            .titulo{text-align:center;font-size:13px;font-weight:700;background:#EA580C;color:#fff;padding:6px;border-radius:4px;margin-bottom:12px;}
            .fila{display:flex;justify-content:space-between;margin-bottom:4px;}
            .fila span{font-size:11px;color:#374151;}
            .fila b{font-size:11px;font-weight:700;}
            .total-box{background:#FFF7ED;border:2px solid #EA580C;border-radius:8px;padding:10px;margin:12px 0;text-align:center;}
            .total-valor{font-size:24px;font-weight:900;color:#EA580C;}
            .footer{text-align:center;border-top:2px dashed #ccc;padding-top:10px;margin-top:10px;font-size:10px;color:#9CA3AF;}
            @media print{body{-webkit-print-color-adjust:exact;print-color-adjust:exact;}}
        </style></head><body>
        <div class="ticket">
            <div class="header">${logoHtml}<div class="gym-name">${d.empresa.nombre}</div>
            <div style="font-size:10px;color:#555;">${d.empresa.direccion||''}</div>
            <div style="font-size:10px;color:#555;">RUC: ${d.empresa.ruc||''}</div></div>
            <div class="titulo">🧾 RECIBO DE PAGO</div>
            <div style="text-align:center;font-size:11px;color:#64748B;margin-bottom:12px;">N° ${String(d.pago.id).padStart(6,'0')}</div>
            <div class="fila"><span>Cliente:</span><b>${d.miembro.nombre}</b></div>
            <div class="fila"><span>DNI:</span><b>${d.miembro.dni||'-'}</b></div>
            <div class="fila"><span>Plan:</span><b>${d.pago.plan}</b></div>
            <div class="fila"><span>Fecha:</span><b>${new Date(d.pago.fecha_pago).toLocaleDateString('es-PE')}</b></div>
            <div class="fila"><span>Método:</span><b>${d.pago.metodo_pago?.toUpperCase()}</b></div>
            ${periodo}
            <div class="total-box"><div style="font-size:10px;color:#EA580C;font-weight:700;">TOTAL PAGADO</div>
            <div class="total-valor">S/ ${Number(d.pago.monto).toFixed(2)}</div></div>
            <div class="footer">¡Gracias por entrenar! 💪<br>${new Date().toLocaleString('es-PE')}</div>
        </div>
        <script>window.onload=()=>{window.print()}<\/script></body></html>`
        const w = window.open('','_blank','width=380,height=650')
        w.document.write(html)
        w.document.close()
    } catch(e) { alert('Error: ' + e.message) }
}
</script>

<template>
    <AppLayout title="Reportes Gimnasio">
        <div style="padding:24px; font-family:'Inter',sans-serif; max-width:1400px;">

            <!-- Header -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
                <div>
                    <h1 style="font-size:24px; font-weight:800; color:#1E293B; margin:0;">📊 Reportes Gimnasio</h1>
                    <p style="font-size:13px; color:#64748B; margin:4px 0 0;">Historial de ingresos y pagos</p>
                </div>
                <div style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
                    <div>
                        <label style="font-size:11px; font-weight:700; color:#64748B; display:block; margin-bottom:4px;">DESDE</label>
                        <input type="date" v-model="desde" style="padding:8px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px;" />
                    </div>
                    <div>
                        <label style="font-size:11px; font-weight:700; color:#64748B; display:block; margin-bottom:4px;">HASTA</label>
                        <input type="date" v-model="hasta" style="padding:8px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px;" />
                    </div>
                    <button @click="filtrar" style="padding:9px 20px; background:#EA580C; color:#fff; border:none; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">🔍 Filtrar</button>
                    <a :href="'/reportes/reporte-contador-pdf?desde=' + desde + '&hasta=' + hasta" target="_blank"
                        style="padding:9px 20px; background:#1E293B; color:#fff; border:none; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px; text-decoration:none; display:inline-block;">
                        📥 PDF Contable
                    </a>
                </div>
            </div>

            <!-- KPIs -->
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:14px; margin-bottom:24px;">
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-top:4px solid #EA580C;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Comprobantes</div>
                    <div style="font-size:36px; font-weight:900; color:#EA580C;">{{ totalPagos }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-top:4px solid #10B981;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Total Ventas</div>
                    <div style="font-size:24px; font-weight:900; color:#10B981;">{{ money(totalIngresos) }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-top:4px solid #F59E0B;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Ingresos Hoy</div>
                    <div style="font-size:24px; font-weight:900; color:#F59E0B;">{{ money(ingresosHoy) }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-top:4px solid #3B82F6;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Esta Semana</div>
                    <div style="font-size:24px; font-weight:900; color:#3B82F6;">{{ money(ingresosSemana) }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-top:4px solid #8B5CF6;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Membresías</div>
                    <div style="font-size:24px; font-weight:900; color:#8B5CF6;">{{ money(ingresosMembresia) }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-top:4px solid #0EA5E9;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Sesiones</div>
                    <div style="font-size:24px; font-weight:900; color:#0EA5E9;">{{ money(ingresosSesion) }}</div>
                </div>
            </div>

            <!-- Tabla de pagos -->
            <div style="background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.08); overflow:hidden;">
                <div style="padding:16px 20px; border-bottom:1px solid #F1F5F9; display:flex; justify-content:space-between; align-items:center;">
                    <h3 style="font-size:15px; font-weight:800; color:#1E293B; margin:0;">📋 Detalle de Pagos</h3>
                    <span style="font-size:12px; color:#64748B;">{{ fmt(desde) }} — {{ fmt(hasta) }}</span>
                </div>

                <div v-if="!pagos || pagos.length === 0" style="padding:60px; text-align:center; color:#94A3B8;">
                    <div style="font-size:48px; margin-bottom:12px;">📭</div>
                    <div style="font-size:16px; font-weight:600;">Sin pagos en el período</div>
                </div>

                <table v-else style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#F8FAFC;">
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">N°</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">FECHA</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">CLIENTE</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">DNI</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">PLAN</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">MÉTODO</th>
                            <th style="padding:12px 16px; text-align:right; font-size:11px; color:#64748B; font-weight:700;">TOTAL</th>
                            <th style="padding:12px 16px; text-align:center; font-size:11px; color:#64748B; font-weight:700;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(p, i) in pagos" :key="p.id" style="border-bottom:1px solid #F1F5F9;"
                            :style="{background: i % 2 === 0 ? '#fff' : '#FAFAFA'}">
                            <td style="padding:12px 16px; font-size:13px; color:#94A3B8; font-weight:600;">{{ i + 1 }}</td>
                            <td style="padding:12px 16px; font-size:13px; color:#374151;">{{ fmt(p.fecha_pago) }}</td>
                            <td style="padding:12px 16px;">
                                <div style="font-size:13px; font-weight:600; color:#1E293B;">{{ p.miembro?.nombre }} {{ p.miembro?.apellidos }}</div>
                            </td>
                            <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ p.miembro?.dni || '-' }}</td>
                            <td style="padding:12px 16px;">
                                <span :style="{background: p.plan ? '#FFF7ED' : '#F0FDF4', color: p.plan ? '#EA580C' : '#16A34A', padding:'3px 10px', borderRadius:'20px', fontSize:'11px', fontWeight:'700'}">
                                    {{ p.plan?.nombre || '🏃 Sesión' }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;">
                                <span :style="{background: metodoBadge(p.metodo_pago).bg, color: metodoBadge(p.metodo_pago).color, padding:'3px 8px', borderRadius:'20px', fontSize:'11px', fontWeight:'700'}">
                                    {{ metodoBadge(p.metodo_pago).label }}
                                </span>
                            </td>
                            <td style="padding:12px 16px; text-align:right; font-size:15px; font-weight:800; color:#1E293B;">
                                {{ money(p.monto) }}
                            </td>
                            <td style="padding:12px 16px; text-align:center;">
                                <button @click="imprimirRecibo(p)"
                                    style="background:#FFF7ED; color:#EA580C; border:1px solid #FED7AA; padding:6px 14px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer;">
                                    🖨️ Ticket
                                </button>
                            </td>
                        </tr>
                        <!-- Fila total -->
                        <tr style="background:#FFF7ED; border-top:2px solid #EA580C;">
                            <td colspan="6" style="padding:12px 16px; font-size:13px; font-weight:800; color:#1E293B;">TOTAL GENERAL</td>
                            <td style="padding:12px 16px; text-align:right; font-size:16px; font-weight:900; color:#EA580C;">{{ money(totalIngresos) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </AppLayout>
</template>
