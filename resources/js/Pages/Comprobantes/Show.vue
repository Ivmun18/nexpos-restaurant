<template>
    <AppLayout title="Comprobante Electrónico" subtitle="Detalle del comprobante emitido">
        
        <div style="max-width:800px; margin:0 auto;">
            
            <!-- Estado SUNAT -->
            <div :style="alertStyle" style="border-radius:16px; padding:20px; margin-bottom:24px; display:flex; align-items:center; gap:16px;">
                <div style="font-size:48px;">
                    {{ comprobante.aceptada_por_sunat ? '✅' : (comprobante.enlace_pdf ? '⏳' : '📋') }}
                </div>
                <div style="flex:1;">
                    <h3 style="font-size:18px; font-weight:700; margin:0 0 4px;">
                        {{ comprobante.aceptada_por_sunat ? '¡Comprobante Aceptado por SUNAT!' : (comprobante.enlace_pdf ? 'Comprobante en Proceso' : 'Comprobante Local') }}
                    </h3>
                    <p style="font-size:14px; margin:0; opacity:0.9;">
                        {{ comprobante.aceptada_por_sunat ? 'El comprobante fue enviado y aceptado correctamente.' : (comprobante.enlace_pdf ? 'El comprobante está siendo procesado por SUNAT.' : 'Comprobante registrado localmente. Sin envío a SUNAT.') }}
                    </p>
                </div>
            </div>

            <!-- Información del comprobante -->
            <div style="background:white; border-radius:16px; padding:24px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                
                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:24px;">
                    <div>
                        <h2 style="font-size:24px; font-weight:700; color:#1E293B; margin:0 0 8px;">
                            {{ comprobante.tipo_comprobante_nombre }}
                        </h2>
                        <p style="font-size:16px; color:#64748B; margin:0;">
                            {{ comprobante.numero_completo }}
                        </p>
                    </div>
                    <div style="text-align:right;">
                        <p style="font-size:12px; color:#64748B; margin:0;">Fecha de Emisión</p>
                        <p style="font-size:16px; font-weight:600; color:#1E293B; margin:4px 0 0;">
                            {{ formatFecha(comprobante.fecha_emision) }}
                        </p>
                    </div>
                </div>

                <div style="border-top:2px solid #F1F5F9; padding-top:20px;">
                    
                    <!-- Cliente -->
                    <div style="margin-bottom:20px;">
                        <p style="font-size:12px; color:#64748B; font-weight:700; margin:0 0 8px;">CLIENTE</p>
                        <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">
                            {{ comprobante.cliente_nombre }}
                        </p>
                        <p style="font-size:14px; color:#64748B; margin:4px 0 0;">
                            {{ comprobante.cliente_tipo_documento === '6' ? 'RUC' : 'DNI' }}: {{ comprobante.cliente_numero_documento }}
                        </p>
                        <p v-if="comprobante.cliente_direccion" style="font-size:14px; color:#64748B; margin:4px 0 0;">
                            {{ comprobante.cliente_direccion }}
                        </p>
                        <p v-if="comprobante.cliente_email" style="font-size:14px; color:#64748B; margin:4px 0 0;">
                            📧 {{ comprobante.cliente_email }}
                        </p>
                    </div>

                    <!-- Desglose -->
                    <div style="background:#F8FAFC; border-radius:12px; padding:16px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="font-size:14px; color:#64748B;">Subtotal (Gravada):</span>
                            <span style="font-size:14px; font-weight:600; color:#1E293B;">S/ {{ formatNumber(comprobante.total_gravada) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                            <span style="font-size:14px; color:#64748B;">IGV (18%):</span>
                            <span style="font-size:14px; font-weight:600; color:#1E293B;">S/ {{ formatNumber(comprobante.total_igv) }}</span>
                        </div>
                        <div style="border-top:2px solid #E2E8F0; padding-top:12px; display:flex; justify-content:space-between;">
                            <span style="font-size:18px; font-weight:700; color:#1E293B;">TOTAL:</span>
                            <span style="font-size:24px; font-weight:700; color:#14B8A6;">S/ {{ formatNumber(comprobante.total) }}</span>
                        </div>
                    </div>

                    <!-- Hash -->
                    <div v-if="comprobante.codigo_hash" style="margin-top:16px; padding:12px; background:#FEF3C7; border-radius:8px;">
                        <p style="font-size:11px; color:#92400E; font-weight:700; margin:0 0 4px;">CÓDIGO HASH</p>
                        <p style="font-size:11px; color:#92400E; font-family:monospace; margin:0; word-break:break-all;">
                            {{ comprobante.codigo_hash }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Descargas -->
            <div style="background:white; border-radius:16px; padding:24px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 16px;">
                    📥 Descargar Archivos
                </h3>
                
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:12px;">
                    <a v-if="comprobante.enlace_pdf" :href="comprobante.enlace_pdf" target="_blank"
                        style="padding:16px; background:linear-gradient(135deg,#EF4444,#DC2626); color:white; border-radius:12px; text-decoration:none; text-align:center; font-weight:600; display:flex; align-items:center; justify-content:center; gap:8px;">
                        <span style="font-size:24px;">📄</span>
                        <span>Descargar PDF</span>
                    </a>
                    
                    <a v-if="comprobante.enlace_xml" :href="comprobante.enlace_xml" target="_blank"
                        style="padding:16px; background:linear-gradient(135deg,#10B981,#059669); color:white; border-radius:12px; text-decoration:none; text-align:center; font-weight:600; display:flex; align-items:center; justify-content:center; gap:8px;">
                        <span style="font-size:24px;">📋</span>
                        <span>Descargar XML</span>
                    </a>
                    
                    <a v-if="comprobante.enlace_cdr" :href="comprobante.enlace_cdr" target="_blank"
                        style="padding:16px; background:linear-gradient(135deg,#3B82F6,#2563EB); color:white; border-radius:12px; text-decoration:none; text-align:center; font-weight:600; display:flex; align-items:center; justify-content:center; gap:8px;">
                        <span style="font-size:24px;">📦</span>
                        <span>Descargar CDR</span>
                    </a>
                </div>
            </div>

            <!-- Botones -->
            <div style="display:flex; gap:12px;">
                <button @click="$inertia.visit('/comprobantes')"
                    style="flex:1; padding:14px; background:#F1F5F9; color:#64748B; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                    ← Volver a Lista
                </button>
                <button v-if="comprobante.caja" @click="$inertia.visit('/caja-restaurante/' + comprobante.caja.mesa_id)"
                    style="flex:1; padding:14px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                    Ver Mesa {{ comprobante.caja.mesa.numero }}
                </button>
            </div>

        </div>

    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { computed } from 'vue'

const props = defineProps({
    comprobante: Object,
})

const alertStyle = computed(() => {
    return {
        background: props.comprobante.aceptada_por_sunat ? '#D1FAE5' : (props.comprobante.enlace_pdf ? '#FEF3C7' : '#F1F5F9'),
            : 'linear-gradient(135deg,#FEF3C7,#FDE68A)',
        color: props.comprobante.aceptada_por_sunat ? '#065F46' : (props.comprobante.enlace_pdf ? '#92400E' : '#475569'),
    }
})

const formatFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString('es-PE', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    })
}

const formatNumber = (num) => {
    return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num || 0)
}
</script>
