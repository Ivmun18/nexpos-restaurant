<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    pagos: Array, desde: String, hasta: String, buscar: String,
    totalEfectivo: Number, totalYape: Number, totalTransferencia: Number, totalGeneral: Number,
    planes: Array,
})

const desde = ref(props.desde)
const hasta = ref(props.hasta)
const buscar = ref(props.buscar || '')
const filtrar = () => router.get('/gimnasio/pagos', { desde: desde.value, hasta: hasta.value, buscar: buscar.value }, { preserveState: true })

const money = (n) => 'S/ ' + Number(n||0).toFixed(2)
const fmt = (f) => f ? new Date(f+'T00:00:00').toLocaleDateString('es-PE') : '-'
const metodoBadge = (m) => ({
    efectivo: { bg:'#F0FDF4', color:'#16A34A', label:'💵 Efectivo' },
    yape:     { bg:'#F5F3FF', color:'#6D28D9', label:'📱 Yape' },
    plin:     { bg:'#EFF6FF', color:'#3B82F6', label:'📱 Plin' },
    transferencia: { bg:'#FEF9C3', color:'#CA8A04', label:'🏦 Transferencia' },
    tarjeta:  { bg:'#FEF2F2', color:'#DC2626', label:'💳 Tarjeta' },
}[m] || { bg:'#F1F5F9', color:'#64748B', label: m })

const exportarPDF = () => {
    const filas = props.pagos.map((p,i) => `
        <tr style="border-bottom:1px solid #eee;">
            <td>${i+1}</td>
            <td>${p.miembro?.nombre} ${p.miembro?.apellidos}</td>
            <td>${p.miembro?.dni || '-'}</td>
            <td>${p.plan?.nombre || '-'}</td>
            <td>${fmt(p.fecha_pago)}</td>
            <td>${p.metodo_pago}</td>
            <td style="font-weight:700;">S/ ${Number(p.monto).toFixed(2)}</td>
        </tr>`).join('')
    const html = `<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Reporte Pagos Gimnasio</title>
    <style>body{font-family:Arial,sans-serif;padding:20px;font-size:12px;}h2{text-align:center;color:#1E293B;}
    table{width:100%;border-collapse:collapse;}th{background:#1E293B;color:#fff;padding:8px;text-align:left;font-size:11px;}
    td{padding:7px 8px;}tr:nth-child(even){background:#F8FAFC;}.total{font-weight:700;background:#EFF6FF;}</style></head>
    <body><h2>💪 Reporte de Pagos — Gimnasio</h2>
    <p style="text-align:center;color:#64748B;">Del ${fmt(props.desde)} al ${fmt(props.hasta)}</p>
    <div style="display:flex;gap:20px;margin:16px 0;">
        <div style="border:1px solid #E2E8F0;border-radius:8px;padding:10px 16px;flex:1;text-align:center;">
            <div style="font-size:10px;color:#64748B;font-weight:700;">TOTAL</div>
            <div style="font-size:20px;font-weight:900;color:#1E293B;">S/ ${Number(props.totalGeneral).toFixed(2)}</div>
        </div>
        <div style="border:1px solid #E2E8F0;border-radius:8px;padding:10px 16px;flex:1;text-align:center;">
            <div style="font-size:10px;color:#64748B;font-weight:700;">EFECTIVO</div>
            <div style="font-size:18px;font-weight:900;color:#16A34A;">S/ ${Number(props.totalEfectivo).toFixed(2)}</div>
        </div>
        <div style="border:1px solid #E2E8F0;border-radius:8px;padding:10px 16px;flex:1;text-align:center;">
            <div style="font-size:10px;color:#64748B;font-weight:700;">YAPE/PLIN</div>
            <div style="font-size:18px;font-weight:900;color:#6D28D9;">S/ ${Number(props.totalYape + props.totalTransferencia).toFixed(2)}</div>
        </div>
    </div>
    <table><thead><tr><th>#</th><th>Miembro</th><th>DNI</th><th>Plan</th><th>Fecha</th><th>Método</th><th>Monto</th></tr></thead>
    <tbody>${filas}</tbody></table>
    <script>window.onload=()=>{window.print()}<\/script></body></html>`
    const w = window.open('','_blank','width=900,height=700')
    w.document.write(html)
    w.document.close()
}
</script>

<template>
    <AppLayout title="Pagos">
        <div style="padding:24px; font-family:'Inter',sans-serif; max-width:1200px;">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
                <div>
                    <h1 style="font-size:24px; font-weight:800; color:#1E293B; margin:0;">💰 Pagos</h1>
                    <p style="font-size:13px; color:#64748B; margin:4px 0 0;">Historial de cobros de membresías</p>
                </div>
                <button @click="exportarPDF"
                    style="background:#1E293B; color:#fff; border:none; padding:10px 20px; border-radius:10px; font-weight:600; cursor:pointer; font-size:13px;">
                    📄 Exportar PDF
                </button>
            </div>

            <!-- Filtros -->
            <div style="background:#fff; border-radius:12px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.08); margin-bottom:20px; display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
                <div>
                    <label style="font-size:11px; font-weight:700; color:#64748B; display:block; margin-bottom:4px;">DESDE</label>
                    <input type="date" v-model="desde" style="padding:8px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px;" />
                </div>
                <div>
                    <label style="font-size:11px; font-weight:700; color:#64748B; display:block; margin-bottom:4px;">HASTA</label>
                    <input type="date" v-model="hasta" style="padding:8px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px;" />
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="font-size:11px; font-weight:700; color:#64748B; display:block; margin-bottom:4px;">BUSCAR</label>
                    <input v-model="buscar" placeholder="Nombre o DNI..." style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px;" />
                </div>
                <button @click="filtrar"
                    style="padding:9px 20px; background:#6D28D9; color:#fff; border:none; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">
                    🔍 Filtrar
                </button>
            </div>

            <!-- Resumen -->
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:12px; margin-bottom:20px;">
                <div style="background:linear-gradient(135deg,#6D28D9,#4C1D95); border-radius:12px; padding:16px; color:#fff; text-align:center;">
                    <div style="font-size:11px; opacity:0.8; font-weight:700; text-transform:uppercase; margin-bottom:4px;">Total</div>
                    <div style="font-size:22px; font-weight:900;">{{ money(totalGeneral) }}</div>
                    <div style="font-size:11px; opacity:0.75; margin-top:2px;">{{ pagos.length }} pagos</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.08); text-align:center; border-top:3px solid #16A34A;">
                    <div style="font-size:11px; color:#64748B; font-weight:700; text-transform:uppercase; margin-bottom:4px;">💵 Efectivo</div>
                    <div style="font-size:20px; font-weight:900; color:#16A34A;">{{ money(totalEfectivo) }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.08); text-align:center; border-top:3px solid #6D28D9;">
                    <div style="font-size:11px; color:#64748B; font-weight:700; text-transform:uppercase; margin-bottom:4px;">📱 Yape</div>
                    <div style="font-size:20px; font-weight:900; color:#6D28D9;">{{ money(totalYape) }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.08); text-align:center; border-top:3px solid #CA8A04;">
                    <div style="font-size:11px; color:#64748B; font-weight:700; text-transform:uppercase; margin-bottom:4px;">🏦 Transferencia</div>
                    <div style="font-size:20px; font-weight:900; color:#CA8A04;">{{ money(totalTransferencia) }}</div>
                </div>
            </div>

            <!-- Tabla -->
            <div v-if="pagos.length === 0" style="background:#fff; border-radius:12px; padding:60px; text-align:center; color:#94A3B8; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                <div style="font-size:48px; margin-bottom:12px;">💰</div>
                <div style="font-size:16px; font-weight:600;">Sin pagos en el período seleccionado</div>
            </div>
            <div v-else style="background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.08); overflow:hidden;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#F8FAFC;">
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">MIEMBRO</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">PLAN</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">FECHA</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">MÉTODO</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">PERÍODO</th>
                            <th style="padding:12px 16px; text-align:right; font-size:11px; color:#64748B; font-weight:700;">MONTO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in pagos" :key="p.id" style="border-bottom:1px solid #F1F5F9;">
                            <td style="padding:12px 16px;">
                                <div style="font-size:13px; font-weight:600; color:#1E293B;">{{ p.miembro?.nombre }} {{ p.miembro?.apellidos }}</div>
                                <div style="font-size:11px; color:#94A3B8;">{{ p.miembro?.dni }}</div>
                            </td>
                            <td style="padding:12px 16px; font-size:13px; color:#374151;">{{ p.plan?.nombre || '-' }}</td>
                            <td style="padding:12px 16px; font-size:13px; color:#374151;">{{ fmt(p.fecha_pago) }}</td>
                            <td style="padding:12px 16px;">
                                <span :style="{background: metodoBadge(p.metodo_pago).bg, color: metodoBadge(p.metodo_pago).color, padding:'3px 8px', borderRadius:'20px', fontSize:'11px', fontWeight:'700'}">
                                    {{ metodoBadge(p.metodo_pago).label }}
                                </span>
                            </td>
                            <td style="padding:12px 16px; font-size:12px; color:#64748B;">
                                {{ p.periodo_inicio ? fmt(p.periodo_inicio) + ' → ' + fmt(p.periodo_fin) : '-' }}
                            </td>
                            <td style="padding:12px 16px; text-align:right; font-size:15px; font-weight:800; color:#1E293B;">
                                {{ money(p.monto) }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background:#F8FAFC; border-top:2px solid #E2E8F0;">
                            <td colspan="5" style="padding:12px 16px; font-size:13px; font-weight:700; color:#374151;">Total del período</td>
                            <td style="padding:12px 16px; text-align:right; font-size:16px; font-weight:900; color:#6D28D9;">{{ money(totalGeneral) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
