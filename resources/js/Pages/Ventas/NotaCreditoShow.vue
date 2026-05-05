<template>
    <AppLayout :title="nota.numero_completo" subtitle="Detalle de nota de crédito">

        <div style="display:grid; grid-template-columns:1fr 300px; gap:1.5rem; align-items:start;">

            <!-- Panel izquierdo -->
            <div>
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem; margin-bottom:1rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem;">
                        <div>
                            <p style="font-size:20px; font-weight:700; color:#1E293B; margin:0; font-family:monospace;">{{ nota.numero_completo }}</p>
                            <p style="font-size:13px; color:#94A3B8; margin:4px 0 0;">Emitida el {{ nota.fecha_emision }}</p>
                        </div>
                        <span :style="estadoStyle(nota.estado)">{{ nota.estado }}</span>
                    </div>

                    <!-- Referencia -->
                    <div style="background:#EFF6FF; border-radius:8px; padding:12px 16px; margin-bottom:1rem;">
                        <p style="font-size:11px; color:#94A3B8; margin:0 0 4px; text-transform:uppercase;">Documento de referencia</p>
                        <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0; font-family:monospace;">{{ nota.doc_ref_numero }}</p>
                        <p style="font-size:12px; color:#64748B; margin:3px 0 0;">Motivo: {{ nota.motivo_codigo }} — {{ nota.motivo_descripcion }}</p>
                    </div>

                    <!-- Cliente -->
                    <div style="background:#F8FAFC; border-radius:8px; padding:12px 16px;">
                        <p style="font-size:11px; color:#94A3B8; margin:0 0 4px; text-transform:uppercase;">Cliente</p>
                        <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ nota.cliente_razon_social || 'Clientes varios' }}</p>
                        <p v-if="nota.cliente_num_doc" style="font-size:12px; color:#64748B; margin:3px 0 0;">
                            {{ nota.cliente_tipo_doc === '6' ? 'RUC' : 'DNI' }}: {{ nota.cliente_num_doc }}
                        </p>
                    </div>
                </div>

                <!-- Detalle -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#F8FAFC;">
                                <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">#</th>
                                <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Descripción</th>
                                <th style="padding:12px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Cant.</th>
                                <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">P. Unit.</th>
                                <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">IGV</th>
                                <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in nota.detalle" :key="item.id" style="border-top:1px solid #F1F5F9;">
                                <td style="padding:12px 16px; font-size:13px; color:#94A3B8;">{{ item.linea }}</td>
                                <td style="padding:12px 16px; font-size:13px; font-weight:500; color:#1E293B;">{{ item.descripcion }}</td>
                                <td style="padding:12px 16px; font-size:13px; color:#1E293B; text-align:center;">{{ Number(item.cantidad).toFixed(2) }}</td>
                                <td style="padding:12px 16px; font-size:13px; color:#1E293B; text-align:right;">S/ {{ Number(item.precio_unitario).toFixed(2) }}</td>
                                <td style="padding:12px 16px; font-size:13px; color:#1E293B; text-align:right;">S/ {{ Number(item.total_igv).toFixed(2) }}</td>
                                <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#1E293B; text-align:right;">S/ {{ Number(item.total).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Panel derecho -->
            <div style="position:sticky; top:80px;">
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem; margin-bottom:1rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Resumen</p>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Op. gravadas</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ Number(nota.total_gravado).toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Op. exoneradas</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ Number(nota.total_exonerado).toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">IGV (18%)</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ Number(nota.total_igv).toFixed(2) }}</span>
                    </div>
                    <div style="border-top:2px solid #E2E8F0; margin:12px 0; padding-top:12px; display:flex; justify-content:space-between;">
                        <span style="font-size:15px; font-weight:700; color:#1E293B;">Total NC</span>
                        <span style="font-size:22px; font-weight:700; color:#2563EB;">S/ {{ Number(nota.total).toFixed(2) }}</span>
                    </div>
                </div>

                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Acciones</p>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <a :href="`/ventas/${nota.venta_id}`"
                            style="padding:10px; background:#EFF6FF; color:#2563EB; border:none; border-radius:8px; font-size:13px; cursor:pointer; text-align:center; text-decoration:none; font-weight:500;">
                            Ver venta original
                        </a>
                        <a href="/notas-credito"
                            style="padding:10px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer; text-align:center; text-decoration:none;">
                            Volver a notas de crédito
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({ nota: Object })

const estadoStyle = (estado) => {
    const map = {
        emitido:   { background:'#EFF6FF', color:'#1D4ED8' },
        aceptado:  { background:'#F0FDF4', color:'#166534' },
        rechazado: { background:'#FEF2F2', color:'#991B1B' },
        anulado:   { background:'#F1F5F9', color:'#64748B' },
    }
    return { ...(map[estado] || map.emitido), fontSize:'12px', padding:'4px 12px', borderRadius:'20px' }
}
</script>