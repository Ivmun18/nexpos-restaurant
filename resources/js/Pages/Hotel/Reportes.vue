<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    reservas: Array,
    totalIngresos: Number,
    totalReservas: Number,
    ocupacionPromedio: Number,
    desde: String,
    hasta: String,
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
                <a :href="'/hotel/reportes/pdf?desde=' + desde + '&hasta=' + hasta" target="_blank" style="background:#DC2626; color:#fff; border:none; padding:9px 20px; border-radius:8px; font-weight:600; cursor:pointer; text-decoration:none;">📄 Descargar PDF</a>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:24px;">
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #3B82F6;">
                    <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Total Reservas</div>
                    <div style="font-size:32px; font-weight:700; color:#1E293B;">{{ totalReservas }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #10B981;">
                    <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Ingresos</div>
                    <div style="font-size:24px; font-weight:700; color:#10B981;">S/ {{ Number(totalIngresos).toFixed(2) }}</div>
                </div>
                <div style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08); border-left:4px solid #8B5CF6;">
                    <div style="font-size:11px; color:#64748B; font-weight:600; text-transform:uppercase;">Ocupación</div>
                    <div style="font-size:32px; font-weight:700; color:#8B5CF6;">{{ ocupacionPromedio }}%</div>
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
</template>
