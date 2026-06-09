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
})

const desde = ref(props.desde)
const hasta = ref(props.hasta)
const filtrar = () => router.get('/gimnasio/reportes', { desde: desde.value, hasta: hasta.value }, { preserveState: true })

const money = (n) => 'S/ ' + Number(n||0).toFixed(2)

const exportarPDF = () => {
    const filasPlan = (props.porPlan || []).map(p =>
        `<tr><td>${p.plan}</td><td style="text-align:right;font-weight:700;">S/ ${Number(p.total).toFixed(2)}</td><td style="text-align:center;">${p.count}</td></tr>`
    ).join('')
    const filasMetodo = (props.porMetodo || []).map(m =>
        `<tr><td style="text-transform:capitalize;">${m.metodo}</td><td style="text-align:right;font-weight:700;">S/ ${Number(m.total).toFixed(2)}</td><td style="text-align:center;">${m.count}</td><td style="text-align:center;">${props.totalIngresos > 0 ? Math.round(m.total/props.totalIngresos*100) : 0}%</td></tr>`
    ).join('')

    const html = `<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Reporte Gimnasio</title>
    <style>
        body{font-family:Arial,sans-serif;padding:24px;font-size:12px;color:#1a1a1a;}
        h1{color:#EA580C;margin:0 0 4px;}
        .sub{color:#64748B;font-size:13px;margin:0 0 20px;}
        .kpis{display:flex;gap:16px;margin-bottom:24px;}
        .kpi{border:2px solid #EA580C;border-radius:8px;padding:12px 20px;flex:1;text-align:center;}
        .kpi-val{font-size:22px;font-weight:900;color:#EA580C;}
        .kpi-lab{font-size:10px;color:#64748B;font-weight:700;text-transform:uppercase;margin-top:2px;}
        .section{margin-bottom:20px;}
        h3{font-size:14px;font-weight:700;color:#1E293B;border-bottom:2px solid #EA580C;padding-bottom:6px;margin-bottom:12px;}
        table{width:100%;border-collapse:collapse;}
        th{background:#1E293B;color:#fff;padding:8px;text-align:left;font-size:11px;}
        td{padding:7px 8px;border-bottom:1px solid #F1F5F9;}
        tr:nth-child(even){background:#FFF7ED;}
        .footer{text-align:center;color:#94A3B8;font-size:11px;margin-top:24px;border-top:1px solid #E2E8F0;padding-top:12px;}
    </style></head><body>
    <h1>💪 Reporte de Ingresos — Gimnasio</h1>
    <p class="sub">Período: ${props.desde} al ${props.hasta} · Generado: ${new Date().toLocaleString('es-PE')}</p>
    <div class="kpis">
        <div class="kpi"><div class="kpi-val">S/ ${Number(props.ingresosHoy||0).toFixed(2)}</div><div class="kpi-lab">Hoy</div></div>
        <div class="kpi"><div class="kpi-val">S/ ${Number(props.ingresosSemana||0).toFixed(2)}</div><div class="kpi-lab">Esta semana</div></div>
        <div class="kpi"><div class="kpi-val">S/ ${Number(props.ingresosMesAct||0).toFixed(2)}</div><div class="kpi-lab">Este mes</div></div>
        <div class="kpi"><div class="kpi-val">${props.totalPagos}</div><div class="kpi-lab">Total pagos</div></div>
        <div class="kpi"><div class="kpi-val">S/ ${Number(props.ticketPromedio||0).toFixed(2)}</div><div class="kpi-lab">Ticket promedio</div></div>
    </div>
    <div style="display:flex;gap:16px;margin-bottom:20px;">
        <div style="flex:1;border:1px solid #FED7AA;border-radius:8px;padding:12px;text-align:center;">
            <div style="font-size:18px;font-weight:900;color:#EA580C;">S/ ${Number(props.ingresosSesion||0).toFixed(2)}</div>
            <div style="font-size:10px;color:#64748B;font-weight:700;">SESIONES</div>
        </div>
        <div style="flex:1;border:1px solid #BBF7D0;border-radius:8px;padding:12px;text-align:center;">
            <div style="font-size:18px;font-weight:900;color:#10B981;">S/ ${Number(props.ingresosMembresia||0).toFixed(2)}</div>
            <div style="font-size:10px;color:#64748B;font-weight:700;">MEMBRESÍAS</div>
        </div>
    </div>
    <div class="section">
        <h3>🏆 Ingresos por Plan</h3>
        <table><thead><tr><th>Plan</th><th style="text-align:right;">Total</th><th style="text-align:center;">Pagos</th></tr></thead>
        <tbody>${filasPlan || '<tr><td colspan="3" style="text-align:center;color:#94A3B8;">Sin datos</td></tr>'}</tbody></table>
    </div>
    <div class="section">
        <h3>💳 Por Método de Pago</h3>
        <table><thead><tr><th>Método</th><th style="text-align:right;">Total</th><th style="text-align:center;">Pagos</th><th style="text-align:center;">%</th></tr></thead>
        <tbody>${filasMetodo || '<tr><td colspan="4" style="text-align:center;color:#94A3B8;">Sin datos</td></tr>'}</tbody></table>
    </div>
    <div class="footer">NEXPOS · Sistema de Gestión para Gimnasios · nexposolution.com</div>
    <script>window.onload=()=>{window.print()}<\/script>
    </body></html>`
    const w = window.open('','_blank','width=900,height=700')
    w.document.write(html)
    w.document.close()
}
const fmt = (f) => f ? new Date(f+'T00:00:00').toLocaleDateString('es-PE') : '-'
const maxMes = Math.max(...(props.porMes?.map(m => m.total) || [1]), 1)
const maxDia = Math.max(...(props.porDia?.map(d => d.total) || [1]), 1)
const colores = ['#EA580C','#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6']
const metodoBadge = (m) => ({
    efectivo:'💵', yape:'📱', plin:'📱', transferencia:'🏦', tarjeta:'💳'
}[m] || '💰')
</script>

<template>
    <AppLayout title="Reportes Gimnasio">
        <div style="padding:24px; font-family:'Inter',sans-serif; max-width:1200px;">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
                <div>
                    <h1 style="font-size:24px; font-weight:800; color:#1E293B; margin:0;">📊 Reportes</h1>
                    <p style="font-size:13px; color:#64748B; margin:4px 0 0;">Análisis de ingresos y actividad</p>
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
                    <button @click="filtrar" style="padding:9px 20px; background:#EA580C; color:#fff; border:none; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">🔍 Aplicar</button>
                    <button @click="exportarPDF" style="padding:9px 20px; background:#1E293B; color:#fff; border:none; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">🖨️ Imprimir</button>
                    <a :href="'/gimnasio/reportes/contable?desde=' + desde + '&hasta=' + hasta"
                        target="_blank"
                        style="padding:9px 20px; background:#EA580C; color:#fff; border:none; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px; text-decoration:none; display:inline-block;">
                        📥 PDF Contable
                    </a>
                </div>
            </div>

            <!-- KPIs -->
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:14px; margin-bottom:24px;">
                <div style="background:linear-gradient(135deg,#EA580C,#C2410C); border-radius:12px; padding:18px; color:#fff; box-shadow:0 2px 8px rgba(234,88,12,0.3);">
                    <div style="font-size:11px; opacity:0.8; font-weight:700; text-transform:uppercase; margin-bottom:6px;">💰 Total Ingresos</div>
                    <div style="font-size:26px; font-weight:900;">{{ money(totalIngresos) }}</div>
                    <div style="font-size:11px; opacity:0.75; margin-top:4px;">{{ fmt(desde) }} — {{ fmt(hasta) }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #3B82F6;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Pagos</div>
                    <div style="font-size:32px; font-weight:900; color:#3B82F6;">{{ totalPagos }}</div>
                    <div style="font-size:12px; color:#94A3B8; margin-top:4px;">transacciones</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #10B981;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Ticket Promedio</div>
                    <div style="font-size:26px; font-weight:900; color:#10B981;">{{ money(ticketPromedio) }}</div>
                    <div style="font-size:12px; color:#94A3B8; margin-top:4px;">por pago</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #F59E0B;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Miembros Nuevos</div>
                    <div style="font-size:32px; font-weight:900; color:#F59E0B;">{{ miembrosNuevos }}</div>
                    <div style="font-size:12px; color:#94A3B8; margin-top:4px;">en el período</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #0EA5E9;">
                    <div style="font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase; margin-bottom:6px;">Total Accesos</div>
                    <div style="font-size:32px; font-weight:900; color:#0EA5E9;">{{ totalAccesos }}</div>
                    <div style="font-size:12px; color:#94A3B8; margin-top:4px;">visitas al gym</div>
                </div>
            </div>

            <!-- Resumen rápido hoy / semana / mes -->
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:14px; margin-bottom:24px;">
                <div style="background:linear-gradient(135deg,#EA580C,#C2410C); border-radius:14px; padding:18px; color:#fff; box-shadow:0 4px 15px rgba(234,88,12,0.3); text-align:center;">
                    <div style="font-size:11px; opacity:0.8; font-weight:700; text-transform:uppercase; margin-bottom:6px;">💰 Hoy</div>
                    <div style="font-size:28px; font-weight:900;">{{ money(ingresosHoy) }}</div>
                </div>
                <div style="background:linear-gradient(135deg,#0EA5E9,#0284C7); border-radius:14px; padding:18px; color:#fff; box-shadow:0 4px 15px rgba(14,165,233,0.3); text-align:center;">
                    <div style="font-size:11px; opacity:0.8; font-weight:700; text-transform:uppercase; margin-bottom:6px;">📅 Esta semana</div>
                    <div style="font-size:28px; font-weight:900;">{{ money(ingresosSemana) }}</div>
                </div>
                <div style="background:linear-gradient(135deg,#10B981,#059669); border-radius:14px; padding:18px; color:#fff; box-shadow:0 4px 15px rgba(16,185,129,0.3); text-align:center;">
                    <div style="font-size:11px; opacity:0.8; font-weight:700; text-transform:uppercase; margin-bottom:6px;">🗓️ Este mes</div>
                    <div style="font-size:28px; font-weight:900;">{{ money(ingresosMesAct) }}</div>
                </div>
                <div style="background:#fff; border-radius:14px; padding:18px; box-shadow:0 2px 8px rgba(0,0,0,0.06); text-align:center; border-top:4px solid #EA580C;">
                    <div style="font-size:11px; color:#64748B; font-weight:700; text-transform:uppercase; margin-bottom:6px;">🏃 Sesiones</div>
                    <div style="font-size:28px; font-weight:900; color:#EA580C;">{{ money(ingresosSesion) }}</div>
                    <div style="font-size:11px; color:#94A3B8; margin-top:2px;">pago por sesión</div>
                </div>
                <div style="background:#fff; border-radius:14px; padding:18px; box-shadow:0 2px 8px rgba(0,0,0,0.06); text-align:center; border-top:4px solid #10B981;">
                    <div style="font-size:11px; color:#64748B; font-weight:700; text-transform:uppercase; margin-bottom:6px;">📋 Membresías</div>
                    <div style="font-size:28px; font-weight:900; color:#10B981;">{{ money(ingresosMembresia) }}</div>
                    <div style="font-size:11px; color:#94A3B8; margin-top:2px;">renovaciones</div>
                </div>
            </div>

            <!-- Gráfico ingresos por mes -->
            <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08); margin-bottom:20px;">
                <h3 style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 16px;">📈 Ingresos últimos 6 meses</h3>
                <div style="display:flex; align-items:flex-end; gap:10px; height:140px;">
                    <div v-for="m in porMes" :key="m.mes" style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;">
                        <div style="font-size:10px; color:#64748B; font-weight:600;">{{ m.total > 0 ? 'S/'+Math.round(m.total) : '' }}</div>
                        <div :style="{width:'100%', background: 'linear-gradient(180deg,#EA580C,#C2410C)', borderRadius:'4px 4px 0 0', height: Math.max((m.total/maxMes*110),4)+'px', transition:'height 0.3s'}"></div>
                        <div style="font-size:10px; color:#94A3B8; text-align:center;">{{ m.mes }}</div>
                    </div>
                </div>
            </div>

            <!-- Por plan y por método -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">

                <!-- Por plan -->
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <h3 style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 16px;">🏆 Ingresos por Plan</h3>
                    <div v-if="!porPlan || porPlan.length === 0" style="text-align:center; color:#94A3B8; font-size:13px; padding:20px 0;">Sin datos</div>
                    <div v-for="(p,i) in porPlan" :key="i" style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                        <div :style="{width:'10px', height:'10px', borderRadius:'50%', background:colores[i%colores.length], flexShrink:0}"></div>
                        <div style="flex:1;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                                <span style="font-size:13px; font-weight:600; color:#1E293B;">{{ p.plan }}</span>
                                <span style="font-size:13px; font-weight:800; color:#1E293B;">{{ money(p.total) }}</span>
                            </div>
                            <div style="background:#F1F5F9; border-radius:4px; height:6px;">
                                <div :style="{width: (porPlan[0]?.total > 0 ? p.total/porPlan[0].total*100 : 0)+'%', height:'6px', borderRadius:'4px', background:colores[i%colores.length]}"></div>
                            </div>
                            <div style="font-size:11px; color:#94A3B8; margin-top:2px;">{{ p.count }} pago{{ p.count !== 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Por método -->
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <h3 style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 16px;">💳 Por Método de Pago</h3>
                    <div v-if="!porMetodo || porMetodo.length === 0" style="text-align:center; color:#94A3B8; font-size:13px; padding:20px 0;">Sin datos</div>
                    <div v-for="(m,i) in porMetodo" :key="i" style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid #F1F5F9;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:20px;">{{ metodoBadge(m.metodo) }}</span>
                            <div>
                                <div style="font-size:13px; font-weight:600; color:#1E293B; text-transform:capitalize;">{{ m.metodo }}</div>
                                <div style="font-size:11px; color:#94A3B8;">{{ m.count }} pago{{ m.count !== 1 ? 's' : '' }}</div>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:15px; font-weight:800; color:#1E293B;">{{ money(m.total) }}</div>
                            <div style="font-size:11px; color:#94A3B8;">{{ totalIngresos > 0 ? Math.round(m.total/totalIngresos*100) : 0 }}%</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ingresos por día -->
            <div v-if="porDia && porDia.length > 0" style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                <h3 style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 16px;">📅 Ingresos por día</h3>
                <div style="display:flex; align-items:flex-end; gap:4px; height:100px; overflow-x:auto;">
                    <div v-for="d in porDia" :key="d.fecha" style="min-width:32px; flex:1; display:flex; flex-direction:column; align-items:center; gap:3px;">
                        <div style="font-size:9px; color:#64748B;">{{ d.total > 0 ? Math.round(d.total) : '' }}</div>
                        <div :style="{width:'100%', background:'#10B981', borderRadius:'3px 3px 0 0', height: Math.max((d.total/maxDia*70),3)+'px'}"></div>
                        <div style="font-size:9px; color:#94A3B8; text-align:center; white-space:nowrap;">{{ new Date(d.fecha+'T00:00:00').toLocaleDateString('es-PE',{day:'numeric',month:'numeric'}) }}</div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
