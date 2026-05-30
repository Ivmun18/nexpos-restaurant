<template>
    <AppLayout :title="cliente.razon_social">
        <div style="padding:24px; max-width:1200px; margin:0 auto;">
            <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:20px; margin-bottom:20px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
                    <div>
                        <a href="/notaria/clientes" style="font-size:12px; color:#64748B; text-decoration:none;">← Volver a clientes</a>
                        <h1 style="font-size:20px; font-weight:800; color:#1E293B; margin:6px 0 2px;">{{ cliente.razon_social }}</h1>
                        <p style="font-size:13px; color:#64748B; margin:0;">{{ tipoDoc }} {{ cliente.numero_documento }} {{ cliente.telefono ? '· ' + cliente.telefono : '' }}</p>
                    </div>
                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
                        <div style="text-align:center; background:#F8FAFC; border-radius:10px; padding:12px;">
                            <p style="font-size:10px; color:#64748B; margin:0 0 4px; text-transform:uppercase; font-weight:600;">Actos</p>
                            <p style="font-size:22px; font-weight:800; color:#4F46E5; margin:0;">{{ resumen.total_actos }}</p>
                        </div>
                        <div style="text-align:center; background:#F0FDF4; border-radius:10px; padding:12px;">
                            <p style="font-size:10px; color:#64748B; margin:0 0 4px; text-transform:uppercase; font-weight:600;">Pagado</p>
                            <p style="font-size:18px; font-weight:800; color:#16A34A; margin:0;">S/ {{ Number(resumen.total_pagado).toFixed(2) }}</p>
                        </div>
                        <div style="text-align:center; background:#FEF2F2; border-radius:10px; padding:12px;">
                            <p style="font-size:10px; color:#64748B; margin:0 0 4px; text-transform:uppercase; font-weight:600;">Saldo</p>
                            <p style="font-size:18px; font-weight:800; color:#DC2626; margin:0;">S/ {{ Number(resumen.saldo_pendiente).toFixed(2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div style="display:flex; gap:8px; margin-bottom:16px;">
                <button v-for="t in tabs" :key="t.key" @click="tabActivo=t.key"
                    :style="{ background: tabActivo===t.key ? '#4F46E5' : 'white', color: tabActivo===t.key ? 'white' : '#64748B', border:'1px solid #E2E8F0', padding:'8px 20px', borderRadius:'8px', fontWeight:'600', cursor:'pointer', fontSize:'13px' }">
                    {{ t.label }}
                </button>
            </div>
            <div v-show="tabActivo==='actos'" style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
                <table style="width:100%; border-collapse:collapse; font-size:12px;">
                    <thead style="background:#F8FAFC;">
                        <tr>
                            <th style="padding:10px 14px; text-align:left;">Expediente</th>
                            <th style="padding:10px 14px; text-align:left;">Fecha</th>
                            <th style="padding:10px 14px; text-align:left;">Tipo</th>
                            <th style="padding:10px 14px; text-align:left;">Asunto</th>
                            <th style="padding:10px 14px; text-align:right;">Monto</th>
                            <th style="padding:10px 14px; text-align:right;">Pagado</th>
                            <th style="padding:10px 14px; text-align:right;">Saldo</th>
                            <th style="padding:10px 14px; text-align:left;">Estado</th>
                            <th style="padding:10px 14px; text-align:left;">Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="a in actos" :key="a.id" style="border-top:1px solid #F1F5F9;">
                            <td style="padding:10px 14px; font-family:monospace; color:#4F46E5; font-weight:700;">{{ a.numero_expediente }}</td>
                            <td style="padding:10px 14px; color:#64748B;">{{ formatFecha(a.fecha_ingreso) }}</td>
                            <td style="padding:10px 14px; color:#374151; text-transform:capitalize;">{{ (a.tipo_acto||'').replace('_',' ') }}</td>
                            <td style="padding:10px 14px; color:#374151; max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ a.asunto }}</td>
                            <td style="padding:10px 14px; text-align:right;">S/ {{ Number(a.monto_cobrar).toFixed(2) }}</td>
                            <td style="padding:10px 14px; text-align:right; color:#16A34A;">S/ {{ Number(a.monto_pagado).toFixed(2) }}</td>
                            <td style="padding:10px 14px; text-align:right; font-weight:700;" :style="{ color: a.saldo > 0 ? '#DC2626' : '#16A34A' }">S/ {{ Number(a.saldo).toFixed(2) }}</td>
                            <td style="padding:10px 14px;"><span :style="badgeEstado(a.estado_pago)">{{ a.estado_pago }}</span></td>
                            <td style="padding:10px 14px; font-family:monospace; font-size:11px; color:#4F46E5;">{{ a.comprobante || '-' }}</td>
                        </tr>
                        <tr v-if="!actos.length"><td colspan="9" style="text-align:center; padding:30px; color:#94A3B8;">Sin actos</td></tr>
                    </tbody>
                </table>
            </div>
            <div v-show="tabActivo==='pagos'" style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
                <table style="width:100%; border-collapse:collapse; font-size:12px;">
                    <thead style="background:#F8FAFC;">
                        <tr>
                            <th style="padding:10px 14px; text-align:left;">Fecha</th>
                            <th style="padding:10px 14px; text-align:left;">Expediente</th>
                            <th style="padding:10px 14px; text-align:left;">Asunto</th>
                            <th style="padding:10px 14px; text-align:left;">Método</th>
                            <th style="padding:10px 14px; text-align:right;">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in pagos" :key="p.id" style="border-top:1px solid #F1F5F9;">
                            <td style="padding:10px 14px; color:#64748B;">{{ formatDatetime(p.created_at) }}</td>
                            <td style="padding:10px 14px; font-family:monospace; color:#4F46E5;">{{ p.numero_expediente }}</td>
                            <td style="padding:10px 14px; color:#374151;">{{ p.asunto }}</td>
                            <td style="padding:10px 14px; color:#374151; text-transform:capitalize;">{{ p.metodo_pago || 'efectivo' }}</td>
                            <td style="padding:10px 14px; text-align:right; font-weight:700; color:#16A34A;">S/ {{ Number(p.monto).toFixed(2) }}</td>
                        </tr>
                        <tr v-if="!pagos.length"><td colspan="5" style="text-align:center; padding:30px; color:#94A3B8;">Sin pagos</td></tr>
                    </tbody>
                </table>
            </div>
            <div v-show="tabActivo==='comprobantes'" style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
                <table style="width:100%; border-collapse:collapse; font-size:12px;">
                    <thead style="background:#F8FAFC;">
                        <tr>
                            <th style="padding:10px 14px; text-align:left;">Fecha</th>
                            <th style="padding:10px 14px; text-align:left;">Tipo</th>
                            <th style="padding:10px 14px; text-align:left;">Serie-Número</th>
                            <th style="padding:10px 14px; text-align:right;">Total</th>
                            <th style="padding:10px 14px; text-align:left;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in comprobantes" :key="c.id" style="border-top:1px solid #F1F5F9;">
                            <td style="padding:10px 14px; color:#64748B;">{{ formatFecha(c.fecha_emision) }}</td>
                            <td style="padding:10px 14px;">
                                <span :style="{ background: c.tipo_comprobante==='01'?'#DBEAFE':'#D1FAE5', color: c.tipo_comprobante==='01'?'#1D4ED8':'#065F46', padding:'2px 8px', borderRadius:'20px', fontSize:'11px', fontWeight:'700' }">
                                    {{ c.tipo_comprobante==='01' ? 'Factura' : 'Boleta' }}
                                </span>
                            </td>
                            <td style="padding:10px 14px; font-family:monospace;">{{ c.serie }}-{{ String(c.numero).padStart(8,'0') }}</td>
                            <td style="padding:10px 14px; text-align:right; font-weight:700;">S/ {{ Number(c.total).toFixed(2) }}</td>
                            <td style="padding:10px 14px;">
                                <span :style="{ background: c.estado==='aceptado'?'#D1FAE5':c.estado==='emitido'?'#FEF3C7':'#FEE2E2', color: c.estado==='aceptado'?'#065F46':c.estado==='emitido'?'#92400E':'#991B1B', padding:'2px 8px', borderRadius:'20px', fontSize:'11px', fontWeight:'700' }">{{ c.estado }}</span>
                            </td>
                        </tr>
                        <tr v-if="!comprobantes.length"><td colspan="5" style="text-align:center; padding:30px; color:#94A3B8;">Sin comprobantes</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed } from 'vue'
const props = defineProps({
    cliente:      { type: Object, default: () => ({}) },
    actos:        { type: Array,  default: () => [] },
    pagos:        { type: Array,  default: () => [] },
    comprobantes: { type: Array,  default: () => [] },
    resumen:      { type: Object, default: () => ({}) },
})
const tabActivo = ref('actos')
const tabs = [
    { key: 'actos',        label: '📋 Actos (' + props.actos.length + ')' },
    { key: 'pagos',        label: '💰 Pagos (' + props.pagos.length + ')' },
    { key: 'comprobantes', label: '🧾 Comprobantes (' + props.comprobantes.length + ')' },
]
const tipoDoc = computed(() => props.cliente.tipo_documento === '6' ? 'RUC:' : 'DNI:')
const formatFecha    = (f) => f ? new Date(f + 'T00:00:00').toLocaleDateString('es-PE') : '-'
const formatDatetime = (f) => f ? new Date(f).toLocaleString('es-PE') : '-'
const badgeEstado = (e) => ({ background: e==='pagado'?'#D1FAE5':e==='parcial'?'#FEF3C7':'#FEE2E2', color: e==='pagado'?'#065F46':e==='parcial'?'#92400E':'#991B1B', padding:'2px 8px', borderRadius:'20px', fontSize:'11px', fontWeight:'700' })
</script>
