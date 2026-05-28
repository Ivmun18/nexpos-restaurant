<template>
    <AppLayout title="Reportes Notaría" subtitle="Comprobantes emitidos">
        <div style="padding:24px; max-width:1200px; margin:0 auto;">
            <h1 style="font-size:20px; font-weight:700; color:#1E293B; margin:0 0 20px;">📊 Reportes Notaría</h1>

            <!-- Filtros -->
            <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:16px 20px; margin-bottom:20px; display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
                <div>
                    <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Desde</label>
                    <input v-model="filtros.desde" type="date" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                </div>
                <div>
                    <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Hasta</label>
                    <input v-model="filtros.hasta" type="date" style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                </div>
                <button @click="buscar" style="padding:8px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                    🔍 Buscar
                </button>
                <a :href="'/reportes/reporte-contador-pdf?desde=' + filtros.desde + '&hasta=' + filtros.hasta" target="_blank"
                    style="padding:8px 20px; background:#991B1B; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none;">
                    📄 PDF
                </a>
            </div>

            <!-- KPIs -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:24px;">
                <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:14px; padding:16px;">
                    <p style="font-size:11px; font-weight:700; color:#1D4ED8; letter-spacing:1px; margin:0 0 6px;">COMPROBANTES</p>
                    <p style="font-size:24px; font-weight:800; color:#1D4ED8; margin:0;">{{ comprobantes.length }}</p>
                </div>
                <div style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:14px; padding:16px;">
                    <p style="font-size:11px; font-weight:700; color:#166534; letter-spacing:1px; margin:0 0 6px;">TOTAL VENTAS</p>
                    <p style="font-size:24px; font-weight:800; color:#166534; margin:0;">S/ {{ totalVentas.toFixed(2) }}</p>
                </div>
                <div style="background:#FFFBEB; border:1px solid #FCD34D; border-radius:14px; padding:16px;">
                    <p style="font-size:11px; font-weight:700; color:#92400E; letter-spacing:1px; margin:0 0 6px;">IGV TOTAL</p>
                    <p style="font-size:24px; font-weight:800; color:#92400E; margin:0;">S/ {{ totalIgv.toFixed(2) }}</p>
                </div>
                <div style="background:#F5F3FF; border:1px solid #DDD6FE; border-radius:14px; padding:16px;">
                    <p style="font-size:11px; font-weight:700; color:#6D28D9; letter-spacing:1px; margin:0 0 6px;">SUBTOTAL</p>
                    <p style="font-size:24px; font-weight:800; color:#6D28D9; margin:0;">S/ {{ totalGravada.toFixed(2) }}</p>
                </div>
            </div>

            <!-- Tabla -->
            <div style="background:white; border-radius:14px; border:1px solid #E2E8F0; overflow:hidden;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead style="background:#F8FAFC;">
                        <tr style="font-size:12px; color:#64748B; text-transform:uppercase; letter-spacing:1px;">
                            <th style="padding:12px 16px; text-align:left;">N°</th>
                            <th style="padding:12px 16px; text-align:left;">Fecha</th>
                            <th style="padding:12px 16px; text-align:left;">Cliente</th>
                            <th style="padding:12px 16px; text-align:left;">Comprobante</th>
                            <th style="padding:12px 16px; text-align:left;">Serie-Número</th>
                            <th style="padding:12px 16px; text-align:right;">Subtotal</th>
                            <th style="padding:12px 16px; text-align:right;">IGV</th>
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
                            <td style="padding:10px 16px; text-align:right; color:#64748B;">{{ Number(c.total_gravada).toFixed(2) }}</td>
                            <td style="padding:10px 16px; text-align:right; color:#64748B;">{{ Number(c.total_igv).toFixed(2) }}</td>
                            <td style="padding:10px 16px; text-align:right; font-weight:700; color:#1E293B;">S/ {{ Number(c.total).toFixed(2) }}</td>
                            <td style="padding:10px 16px;">
                                <span :style="{ background: c.estado==='aceptado' ? '#D1FAE5' : c.estado==='emitido' ? '#FEF3C7' : '#FEE2E2', color: c.estado==='aceptado' ? '#065F46' : c.estado==='emitido' ? '#92400E' : '#991B1B', padding:'2px 10px', borderRadius:'20px', fontSize:'11px', fontWeight:'700' }">
                                    {{ c.estado }}
                                </span>
                            </td>
                            <td style="padding:10px 16px;">
                                <button v-if="c.estado !== 'aceptado'" @click="reenviar(c)"
                                    :disabled="reenviando === c.id"
                                    style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; padding:4px 12px; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">
                                    {{ reenviando === c.id ? '⏳...' : '🔄 Reenviar' }}
                                </button>
                                <span v-else style="color:#16A34A; font-size:11px;">✅</span>
                            </td>
                        </tr>
                        <tr v-if="comprobantes.length === 0">
                            <td colspan="10" style="text-align:center; padding:40px; color:#94A3B8;">No hay comprobantes en este periodo</td>
                        </tr>
                    </tbody>
                </table>
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
    desde:        { type: String, default: '' },
    hasta:        { type: String, default: '' },
})

const filtros = ref({ desde: props.desde, hasta: props.hasta })

const totalVentas  = computed(() => props.comprobantes.reduce((s, c) => s + Number(c.total), 0))
const totalIgv     = computed(() => props.comprobantes.reduce((s, c) => s + Number(c.total_igv), 0))
const totalGravada = computed(() => props.comprobantes.reduce((s, c) => s + Number(c.total_gravada), 0))

const reenviando = ref(null)

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
