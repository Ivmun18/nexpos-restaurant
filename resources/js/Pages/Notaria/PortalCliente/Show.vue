<template>
    <div style="min-height:100vh; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding:40px 20px;">
        
        <div style="max-width:1000px; margin:0 auto;">
            
            <!-- Header con botón volver -->
            <div style="margin-bottom:30px;">
                <a href="/portal-cliente" style="display:inline-flex; align-items:center; gap:8px; color:#FFFFFF; font-weight:600; text-decoration:none; font-size:14px;">
                    ← Volver a consultar
                </a>
            </div>

            <!-- Card principal -->
            <div style="background:#FFFFFF; border-radius:24px; padding:40px; box-shadow:0 20px 60px rgba(0,0,0,0.3); margin-bottom:24px;">
                
                <!-- Header del expediente -->
                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:32px; flex-wrap:wrap; gap:20px;">
                    <div>
                        <h1 style="font-size:32px; font-weight:800; color:#1E293B; margin:0 0 8px;">{{ acto.numero_expediente }}</h1>
                        <p style="font-size:16px; color:#64748B; margin:0;">{{ acto.tipo_acto_label }}</p>
                    </div>
                    <div :style="{
                        padding:'12px 24px', borderRadius:'12px', fontSize:'14px', fontWeight:'700',
                        background: acto.estado==='solicitado'?'#FEF3C7':acto.estado==='proceso'?'#DBEAFE':acto.estado==='firmado'?'#D1FAE5':acto.estado==='entregado'?'#D1FAE5':'#F1F5F9',
                        color: acto.estado==='solicitado'?'#92400E':acto.estado==='proceso'?'#1E40AF':acto.estado==='firmado'?'#065F46':acto.estado==='entregado'?'#065F46':'#475569',
                    }">
                        {{ estadoLabel(acto.estado) }}
                    </div>
                </div>

                <!-- Información del cliente -->
                <div style="background:#F8FAFC; border-radius:16px; padding:24px; margin-bottom:32px;">
                    <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 16px;">Información del Cliente</h3>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
                        <div>
                            <p style="font-size:12px; color:#64748B; margin:0 0 4px; font-weight:600;">CLIENTE</p>
                            <p style="font-size:14px; color:#1E293B; margin:0; font-weight:600;">{{ cliente.razon_social }}</p>
                        </div>
                        <div>
                            <p style="font-size:12px; color:#64748B; margin:0 0 4px; font-weight:600;">DOCUMENTO</p>
                            <p style="font-size:14px; color:#1E293B; margin:0; font-weight:600;">{{ cliente.tipo_documento_label }} - {{ cliente.numero_documento }}</p>
                        </div>
                        <div v-if="acto.fecha_ingreso">
                            <p style="font-size:12px; color:#64748B; margin:0 0 4px; font-weight:600;">FECHA INGRESO</p>
                            <p style="font-size:14px; color:#1E293B; margin:0; font-weight:600;">{{ formatFecha(acto.fecha_ingreso) }}</p>
                        </div>
                        <div v-if="acto.fecha_entrega">
                            <p style="font-size:12px; color:#64748B; margin:0 0 4px; font-weight:600;">FECHA ENTREGA ESTIMADA</p>
                            <p style="font-size:14px; color:#1E293B; margin:0; font-weight:600;">{{ formatFecha(acto.fecha_entrega) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Barra de progreso -->
                <div style="margin-bottom:40px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">Progreso del Trámite</h3>
                        <span style="font-size:20px; font-weight:800; color:#14B8A6;">{{ porcentaje_completitud }}%</span>
                    </div>
                    <div style="background:#E2E8F0; border-radius:20px; height:12px; overflow:hidden;">
                        <div :style="{
                            width: porcentaje_completitud + '%',
                            height:'100%',
                            background:'linear-gradient(90deg, #14B8A6 0%, #0D9488 100%)',
                            transition:'width 0.5s ease'
                        }"></div>
                    </div>
                </div>

                <!-- Timeline de estados -->
                <div style="margin-bottom:40px;">
                    <h3 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 24px;">Historial del Trámite</h3>
                    <div style="position:relative;">
                        <div v-for="(seg, index) in acto.seguimientos" :key="seg.id" style="display:flex; gap:20px; margin-bottom:24px;">
                            <!-- Línea vertical -->
                            <div style="position:relative; width:40px; display:flex; flex-direction:column; align-items:center;">
                                <div :style="{
                                    width:'40px', height:'40px', borderRadius:'50%', 
                                    background: index===0 ? 'linear-gradient(135deg, #14B8A6 0%, #0D9488 100%)' : '#F1F5F9',
                                    display:'flex', alignItems:'center', justifyContent:'center',
                                    fontSize:'18px', fontWeight:'800',
                                    color: index===0 ? '#FFFFFF' : '#94A3B8',
                                    border: index===0 ? '3px solid #FFFFFF' : 'none',
                                    boxShadow: index===0 ? '0 4px 12px rgba(20,184,166,0.4)' : 'none',
                                    zIndex:2
                                }">
                                    {{ index===0 ? '✓' : '•' }}
                                </div>
                                <div v-if="index < acto.seguimientos.length - 1" style="flex:1; width:2px; background:#E2E8F0; margin-top:-8px; margin-bottom:-8px;"></div>
                            </div>
                            
                            <!-- Contenido -->
                            <div style="flex:1; padding-bottom:8px;">
                                <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 4px;">{{ seg.estado_label || seg.estado }}</p>
                                <p style="font-size:13px; color:#64748B; margin:0 0 8px;">{{ seg.observaciones || 'Sin observaciones' }}</p>
                                <p style="font-size:12px; color:#94A3B8; margin:0;">{{ formatFechaHora(seg.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requisitos pendientes -->
                <div v-if="acto.requisitos && acto.requisitos.length > 0" style="margin-bottom:40px;">
                    <h3 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 20px;">Requisitos del Trámite</h3>
                    <div style="display:grid; gap:12px;">
                        <div v-for="req in acto.requisitos" :key="req.id" 
                            style="display:flex; align-items:start; gap:12px; padding:16px; background:#F8FAFC; border-radius:12px; border-left:4px solid;"
                            :style="{ borderLeftColor: req.completado ? '#14B8A6' : '#FCD34D' }">
                            <div :style="{
                                width:'24px', height:'24px', borderRadius:'6px',
                                background: req.completado ? '#14B8A6' : '#FCD34D',
                                display:'flex', alignItems:'center', justifyContent:'center',
                                color:'#FFFFFF', fontSize:'14px', fontWeight:'800', flexShrink:0
                            }">
                                {{ req.completado ? '✓' : '!' }}
                            </div>
                            <div style="flex:1;">
                                <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ req.nombre }}</p>
                                <p v-if="req.descripcion" style="font-size:13px; color:#64748B; margin:4px 0 0;">{{ req.descripcion }}</p>
                            </div>
                            <span :style="{
                                padding:'4px 12px', borderRadius:'6px', fontSize:'11px', fontWeight:'700',
                                background: req.completado ? '#D1FAE5' : '#FEF3C7',
                                color: req.completado ? '#065F46' : '#92400E'
                            }">
                                {{ req.completado ? 'Completado' : 'Pendiente' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Documentos disponibles -->
                <div v-if="acto.documentos && acto.documentos.length > 0">
                    <h3 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 20px;">Documentos Disponibles</h3>
                    <div style="display:grid; gap:12px;">
                        <a v-for="doc in acto.documentos" :key="doc.id"
                            :href="`/portal-cliente/documento/${doc.id}/descargar`"
                            style="display:flex; align-items:center; justify-content:space-between; padding:16px 20px; background:#F8FAFC; border-radius:12px; text-decoration:none; transition:all 0.2s; border:2px solid transparent;"
                            @mouseover="$event.target.style.borderColor='#14B8A6'; $event.target.style.background='#F0FDFA'"
                            @mouseout="$event.target.style.borderColor='transparent'; $event.target.style.background='#F8FAFC'">
                            <div style="display:flex; align-items:center; gap:14px;">
                                <div style="width:44px; height:44px; background:#14B8A6; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px;">
                                    📄
                                </div>
                                <div>
                                    <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ doc.nombre }}</p>
                                    <p style="font-size:12px; color:#64748B; margin:4px 0 0;">{{ doc.tipo || 'Documento' }}</p>
                                </div>
                            </div>
                            <div style="padding:8px 16px; background:#14B8A6; color:#FFFFFF; border-radius:8px; font-size:13px; font-weight:700;">
                                Descargar
                            </div>
                        </a>
                    </div>
                </div>

            </div>

            <!-- Footer con contacto -->
            <div style="text-align:center; color:#FFFFFF;">
                <p style="font-size:14px; margin:0 0 8px;">¿Necesitas más información?</p>
                <p style="font-size:16px; font-weight:700; margin:0;">📞 (01) 123-4567 | 📧 info@notaria.com</p>
            </div>

        </div>

    </div>
</template>

<script setup>
import { defineProps } from 'vue'

const props = defineProps({
    acto: Object,
    cliente: Object,
    porcentaje_completitud: Number,
})

const estadoLabel = (estado) => {
    const labels = {
        'solicitado': 'Solicitado',
        'proceso': 'En Proceso',
        'firmado': 'Firmado',
        'entregado': 'Entregado',
        'pendiente': 'Pendiente',
    }
    return labels[estado] || estado
}

const formatFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-PE', { 
        day: '2-digit', 
        month: 'long', 
        year: 'numeric' 
    })
}

const formatFechaHora = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-PE', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
