<template>
    <AppLayout title="Reportes Notaría">
        <div style="padding:24px; max-width:1400px; margin:0 auto;">
            <div style="margin-bottom:20px;">
                <h1 style="font-size:22px; font-weight:700; color:#1E293B;">📊 Reportes Notaría</h1>
                <p style="color:#64748B; font-size:13px;">Comprobantes emitidos</p>
            </div>

            <!-- FILTROS -->
            <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:16px 20px; margin-bottom:20px; display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
                <div>
                    <label style="font-size:11px; font-weight:600; color:#64748B; text-transform:uppercase; display:block; margin-bottom:4px;">DESDE</label>
                    <input type="date" v-model="filtros.desde" style="border:1px solid #E2E8F0; border-radius:8px; padding:8px 12px; font-size:13px;" />
                </div>
                <div>
                    <label style="font-size:11px; font-weight:600; color:#64748B; text-transform:uppercase; display:block; margin-bottom:4px;">HASTA</label>
                    <input type="date" v-model="filtros.hasta" style="border:1px solid #E2E8F0; border-radius:8px; padding:8px 12px; font-size:13px;" />
                </div>
                <button @click="buscar" style="background:#10B981; color:white; border:none; padding:8px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">🔍 Buscar</button>
                <a :href="'/reportes/reporte-contador-pdf?desde=' + filtros.desde + '&hasta=' + filtros.hasta" target="_blank"
                    style="background:#DC2626; color:white; border:none; padding:8px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px; text-decoration:none;">
                    📄 PDF
                </a>
            </div>

            <!-- STATS -->
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:20px;">
                <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:20px;">
                    <p style="font-size:11px; font-weight:600; color:#64748B; text-transform:uppercase; margin:0 0 8px;">COMPROBANTES</p>
                    <p style="font-size:28px; font-weight:800; color:#4F46E5; margin:0;">{{ comprobantes.filter(c => c.estado === 'aceptado' || c.estado === 'emitido').length }}</p>
                </div>
                <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:20px;">
                    <p style="font-size:11px; font-weight:600; color:#64748B; text-transform:uppercase; margin:0 0 8px;">TOTAL VENTAS</p>
                    <p style="font-size:28px; font-weight:800; color:#10B981; margin:0;">S/ {{ totalVentas.toFixed(2) }}</p>
                </div>
                <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:20px;">
                    <p style="font-size:11px; font-weight:600; color:#64748B; text-transform:uppercase; margin:0 0 8px;">INGRESOS HOY</p>
                    <p style="font-size:28px; font-weight:800; color:#F59E0B; margin:0;">S/ {{ ingresosHoy.toFixed(2) }}</p>
                </div>
            </div>

            <!-- TABS -->
            <div style="display:flex; gap:8px; margin-bottom:16px;">
                <button @click="tabActivo='comprobantes'"
                    :style="{ background: tabActivo==='comprobantes' ? '#4F46E5' : 'white', color: tabActivo==='comprobantes' ? 'white' : '#64748B', border:'1px solid #E2E8F0', padding:'8px 20px', borderRadius:'8px', fontWeight:'600', cursor:'pointer', fontSize:'13px' }">
                    🧾 Comprobantes
                </button>
                <button @click="tabActivo='actos'"
                    :style="{ background: tabActivo==='actos' ? '#4F46E5' : 'white', color: tabActivo==='actos' ? 'white' : '#64748B', border:'1px solid #E2E8F0', padding:'8px 20px', borderRadius:'8px', fontWeight:'600', cursor:'pointer', fontSize:'13px' }">
                    📋 Actos / Expedientes
                </button>
            </div>

            <!-- TAB COMPROBANTES -->
            <div v-show="tabActivo==='comprobantes'">
                <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
                    <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:12px;">
                        <thead style="background:#F8FAFC;">
                            <tr>
                                <th style="padding:12px 16px; text-align:left;">N°</th>
                                <th style="padding:12px 16px; text-align:left;">Fecha</th>
                                <th style="padding:12px 16px; text-align:left;">Cliente</th>
                                <th style="padding:12px 16px; text-align:left;">Comprobante</th>
                                <th style="padding:12px 16px; text-align:left;">Serie-Número</th>

                                <th style="padding:12px 16px; text-align:right;">Total</th>
                                <th style="padding:12px 16px; text-align:left;">Estado</th>
                                <th style="padding:12px 16px; text-align:left;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(c, i) in comprobantes" :key="c.id" style="border-top:1px solid #F1F5F9;">
                                <td style="padding:10px 16px; color:#64748B;">{{ i + 1 }}</td>
                                <td style="padding:10px 16px; color:#374151;">{{ formatFecha(c.fecha_emision) }}</td>
                                <td style="padding:10px 16px; font-weight:600; color:#1E293B;">{{ c.cliente_nombre || '-' }}</td>
                                <td style="padding:10px 16px;">
                                    <span :style="{ background: c.tipo_comprobante==='01' ? '#DBEAFE' : '#D1FAE5', color: c.tipo_comprobante==='01' ? '#1D4ED8' : '#065F46', padding:'2px 10px', borderRadius:'20px', fontSize:'11px', fontWeight:'700' }">
                                        {{ c.tipo_comprobante === '01' ? 'Factura' : 'Boleta' }}
                                    </span>
                                </td>
                                <td style="padding:10px 16px; color:#374151; font-family:monospace;">{{ c.serie }}-{{ String(c.numero).padStart(8, '0') }}</td>

                                <td style="padding:10px 16px; text-align:right; font-weight:700; color:#1E293B;">S/ {{ Number(c.total).toFixed(2) }}</td>
                                <td style="padding:10px 16px;">
                                    <span :style="{ background: c.estado==='aceptado' ? '#D1FAE5' : c.estado==='emitido' ? '#FEF3C7' : '#FEE2E2', color: c.estado==='aceptado' ? '#065F46' : c.estado==='emitido' ? '#92400E' : '#991B1B', padding:'2px 10px', borderRadius:'20px', fontSize:'11px', fontWeight:'700' }">
                                        {{ c.estado }}
                                    </span>
                                </td>
                                <td style="padding:10px 16px;">
                                    <button v-if="c.estado !== 'aceptado' && c.estado !== 'anulado'" @click="reenviar(c)"
                                        :disabled="reenviando === c.id"
                                        style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; padding:4px 12px; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">
                                        {{ reenviando === c.id ? '⏳...' : '🔄 Reenviar' }}
                                    </button>
                                    <button v-if="c.estado === 'emitido' || c.estado === 'aceptado'" @click="anular(c)"
                                        :disabled="anulando === c.id"
                                        style="background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; padding:4px 12px; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer; margin-left:4px;">
                                        {{ anulando === c.id ? '⏳...' : '🚫 Anular' }}
                                    </button>
                                    <span v-if="c.estado === 'aceptado'" style="color:#16A34A; font-size:11px;">✅</span>
                                    <span v-if="c.estado === 'anulado'" style="color:#DC2626; font-size:11px;">❌ Anulado</span>
                                    <a :href="'https://wa.me/?text=' + encodeURIComponent('Estimado cliente, adjuntamos su comprobante ' + c.serie + '-' + String(c.numero).padStart(8,'0') + ' por S/ ' + c.total + '. Descargue su ticket en: http://161.35.5.40/notaria/comprobantes/' + c.id + '/recibo-ticket')" target="_blank"
                                        style="background:#25D366; color:white; border:none; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer; text-decoration:none; margin-left:4px;">
                                        💬 WhatsApp
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="comprobantes.length === 0">
                                <td colspan="10" style="text-align:center; padding:40px; color:#94A3B8;">No hay comprobantes en este periodo</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

            <!-- BTN PDF ACTOS (solo visible en tab actos) -->
            <div v-show="tabActivo==='actos'" style="margin-bottom:12px; text-align:right;">
                <a :href="'/notaria/reportes/actos-pdf?desde=' + filtros.desde + '&hasta=' + filtros.hasta" target="_blank"
                    style="background:#DC2626; color:white; padding:8px 20px; border-radius:8px; font-weight:600; font-size:13px; text-decoration:none;">
                    📄 PDF Actos
                </a>
            </div>

            <!-- TAB ACTOS -->
            <div v-show="tabActivo==='actos'">
                <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
                    <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:12px;">
                        <thead style="background:#F8FAFC;">
                            <tr>
                                <th style="padding:12px 10px; text-align:left;">N°</th>
                                <th style="padding:12px 10px; text-align:left;">Expediente</th>
                                <th style="padding:12px 10px; text-align:left;">Fecha</th>
                                <th style="padding:12px 10px; text-align:left;">Tipo Acto</th>
                                <th style="padding:12px 10px; text-align:left;">Asunto</th>
                                <th style="padding:12px 10px; text-align:left;">DNI/RUC</th>
                                <th style="padding:12px 10px; text-align:left;">Cliente</th>
                                <th style="padding:12px 10px; text-align:right;">Monto</th>
                                <th style="padding:12px 10px; text-align:right;">Pagado</th>
                                <th style="padding:12px 10px; text-align:right;">Saldo</th>
                                <th style="padding:12px 10px; text-align:left;">Pago</th>
                                <th style="padding:12px 10px; text-align:left;">Estado</th>
                                <th style="padding:12px 10px; text-align:left;">Comprobante</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(a, i) in actos" :key="a.id" style="border-top:1px solid #F1F5F9;">
                                <td style="padding:8px 10px; color:#64748B;">{{ i + 1 }}</td>
                                <td style="padding:8px 10px; font-weight:600; color:#4F46E5; font-family:monospace;">{{ a.numero_expediente }}</td>
                                <td style="padding:8px 10px; color:#64748B;">{{ formatFecha(a.fecha_ingreso) }}</td>
                                <td style="padding:8px 10px; color:#374151; text-transform:capitalize;">{{ (a.tipo_acto || '').replace('_', ' ') }}</td>
                                <td style="padding:8px 10px; color:#374151; max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ a.asunto }}</td>
                                <td style="padding:8px 10px; font-family:monospace; color:#374151;">{{ a.cliente_documento || '-' }}</td>
                                <td style="padding:8px 10px; font-weight:600; color:#1E293B;">{{ a.cliente_nombre || '-' }}</td>
                                <td style="padding:8px 10px; text-align:right; color:#374151;">S/ {{ Number(a.monto_cobrar).toFixed(2) }}</td>
                                <td style="padding:8px 10px; text-align:right; color:#16A34A;">S/ {{ Number(a.monto_pagado).toFixed(2) }}</td>
                                <td style="padding:8px 10px; text-align:right;" :style="{ color: a.saldo > 0 ? '#DC2626' : '#16A34A' }">S/ {{ Number(a.saldo).toFixed(2) }}</td>
                                <td style="padding:8px 10px;">
                                    <span :style="{ background: a.estado_pago==='pagado' ? '#D1FAE5' : a.estado_pago==='parcial' ? '#FEF3C7' : '#FEE2E2', color: a.estado_pago==='pagado' ? '#065F46' : a.estado_pago==='parcial' ? '#92400E' : '#991B1B', padding:'2px 8px', borderRadius:'20px', fontSize:'10px', fontWeight:'700' }">
                                        {{ a.estado_pago }}
                                    </span>
                                </td>
                                <td style="padding:8px 10px;">
                                    <span :style="{ background: a.estado==='finalizado' ? '#D1FAE5' : a.estado==='en_proceso' ? '#EFF6FF' : '#FEE2E2', color: a.estado==='finalizado' ? '#065F46' : a.estado==='en_proceso' ? '#1D4ED8' : '#991B1B', padding:'2px 8px', borderRadius:'20px', fontSize:'10px', fontWeight:'700' }">
                                        {{ a.estado }}
                                    </span>
                                </td>
                                <td style="padding:8px 10px; font-family:monospace; font-size:11px; color:#4F46E5;">{{ a.comprobante || '-' }}</td>
                            </tr>
                            <tr v-if="actos.length === 0">
                                <td colspan="13" style="text-align:center; padding:40px; color:#94A3B8;">No hay actos en este periodo</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    comprobantes: { type: Array, default: () => [] },
    actos:        { type: Array, default: () => [] },
    desde:        { type: String, default: '' },
    hasta:        { type: String, default: '' },
})

const filtros = ref({ desde: props.desde, hasta: props.hasta })
const tabActivo = ref('comprobantes')

const totalVentas  = computed(() => props.comprobantes.filter(c => c.estado === 'aceptado' || c.estado === 'emitido').reduce((s, c) => s + Number(c.total), 0))
const ingresosHoy  = computed(() => {
    const now = new Date()
    const hoy = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0') + '-' + String(now.getDate()).padStart(2,'0')
    return props.comprobantes.filter(c => (c.estado === 'aceptado' || c.estado === 'emitido') && c.fecha_emision && c.fecha_emision.startsWith(hoy)).reduce((s,c) => s + Number(c.total), 0)
})
const totalIgv     = computed(() => props.comprobantes.filter(c => c.estado === 'aceptado' || c.estado === 'emitido').reduce((s, c) => s + Number(c.total_igv), 0))
const totalGravada = computed(() => props.comprobantes.filter(c => c.estado === 'aceptado' || c.estado === 'emitido').reduce((s, c) => s + Number(c.total_gravada), 0))

const reenviando = ref(null)

const anulando = ref(null)

const anular = async (comp) => {
    const motivo = prompt('Motivo de anulación:', 'Error en emisión')
    if (!motivo) return
    if (!confirm('¿Anular ' + comp.serie + '-' + String(comp.numero).padStart(8,'0') + ' ante SUNAT?')) return
    anulando.value = comp.id
    try {
        const res = await fetch('/notaria/comprobantes/' + comp.id + '/anular', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ motivo })
        })
        const data = await res.json()
        if (data.success) {
            comp.estado = 'anulado'
            alert('✅ Comprobante anulado correctamente')
        } else {
            alert('❌ Error: ' + data.mensaje)
        }
    } catch(e) {
        alert('❌ Error de conexión')
    } finally {
        anulando.value = null
    }
}

const reenviar = async (comp) => {
    reenviando.value = comp.id
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        const res = await fetch('/notaria/comprobantes/' + comp.id + '/reenviar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        })
        const data = await res.json()
        if (data.success) {
            comp.estado = 'aceptado'
            alert('✅ ' + data.mensaje)
        } else {
            alert('❌ ' + data.mensaje)
        }
    } catch(e) {
        alert('❌ Error de conexión')
    }
    reenviando.value = null
}

const formatFecha = (f) => f ? new Date(f + 'T00:00:00').toLocaleDateString('es-PE') : '-'

const buscar = () => {
    router.get('/notaria/reportes', filtros.value, { preserveState: false })
}
</script>
