<template>
    <AppLayout :title="'Expediente ' + acto.numero_expediente" subtitle="Detalle del acto notarial">

        <!-- HEADER -->
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:1.5rem;">
            <a href="/notaria/actos" style="background:#F1F5F9; border:none; border-radius:10px; padding:9px 14px; font-size:14px; cursor:pointer; text-decoration:none; color:#475569; font-weight:600;">← Volver</a>
            <div>
                <h2 style="font-size:20px; font-weight:800; color:#1E293B; margin:0;">{{ acto.numero_expediente }}</h2>
                <p style="font-size:13px; color:#94A3B8; margin:2px 0 0;">{{ labelTipo(acto.tipo_acto) }} · Ingresado {{ formatFecha(acto.fecha_ingreso) }}</p>
            </div>
            <div style="margin-left:auto; display:flex; gap:8px;">
                <span :style="estiloEstado(acto.estado)">{{ labelEstado(acto.estado) }}</span>
                <span :style="estiloPago(acto.estado_pago)">{{ labelPago(acto.estado_pago) }}</span>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 340px; gap:16px;">

            <!-- IZQUIERDA -->
            <div style="display:flex; flex-direction:column; gap:16px;">

                <!-- DATOS DEL EXPEDIENTE -->
                <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.25rem;">
                    <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0 0 1rem;">📄 Datos del expediente</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Tipo de acto</p>
                            <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ labelTipo(acto.tipo_acto) }}</p>
                        </div>
                        <div>
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Responsable</p>
                            <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ acto.usuario?.name ?? '—' }}</p>
                        </div>
                        <div style="grid-column:1/-1;">
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Asunto</p>
                            <p style="font-size:14px; color:#1E293B; margin:0;">{{ acto.asunto }}</p>
                        </div>
                        <div style="grid-column:1/-1;">
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Partes intervinientes</p>
                            <p style="font-size:14px; color:#1E293B; margin:0;">{{ acto.partes_intervinientes || '—' }}</p>
                        </div>
                        <div>
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Fecha ingreso</p>
                            <p style="font-size:14px; color:#1E293B; margin:0;">{{ formatFecha(acto.fecha_ingreso) }}</p>
                        </div>
                        <div>
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Fecha entrega</p>
                            <p style="font-size:14px; color:#1E293B; margin:0;">{{ acto.fecha_entrega ? formatFecha(acto.fecha_entrega) : 'Sin fecha' }}</p>
                        </div>
                        <div v-if="acto.observaciones" style="grid-column:1/-1;">
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Observaciones</p>
                            <p style="font-size:13px; color:#64748B; margin:0;">{{ acto.observaciones }}</p>
                        </div>
                    </div>
                </div>

                <!-- SEGUIMIENTO -->
                <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.25rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                        <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0;">🔄 Seguimiento del trámite</p>
                        <button @click="modalEstado=true" style="padding:5px 14px; background:#EEF2FF; color:#4F46E5; border:1px solid #C7D2FE; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer;">+ Actualizar estado</button>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <div v-for="s in acto.seguimientos" :key="s.id"
                            style="display:flex; gap:12px; align-items:flex-start; padding:10px 12px; background:#F8FAFC; border-radius:8px;">
                            <div :style="{width:'10px', height:'10px', borderRadius:'50%', marginTop:'4px', flexShrink:0,
                                background: s.estado_nuevo==='finalizado'?'#10B981':s.estado_nuevo==='en_proceso'?'#3B82F6':s.estado_nuevo==='cancelado'?'#EF4444':'#F59E0B'}"></div>
                            <div style="flex:1;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span :style="estiloEstado(s.estado_nuevo)" style="font-size:11px;">{{ labelEstado(s.estado_nuevo) }}</span>
                                    <span style="font-size:11px; color:#94A3B8;">{{ s.usuario?.name }}</span>
                                    <span style="font-size:11px; color:#94A3B8; margin-left:auto;">{{ formatFechaHora(s.created_at) }}</span>
                                </div>
                                <p v-if="s.comentario" style="font-size:12px; color:#64748B; margin:4px 0 0;">{{ s.comentario }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- DERECHA -->
            <div style="display:flex; flex-direction:column; gap:16px;">

                <!-- COBROS -->
                <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.25rem;">
                    <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0 0 1rem;">💰 Estado de cobro</p>
                    <div style="background:#F8FAFC; border-radius:10px; padding:12px 14px; margin-bottom:1rem;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-size:13px; color:#64748B;">Total a cobrar</span>
                            <span style="font-size:13px; font-weight:700; color:#1E293B;">S/ {{ Number(acto.monto_cobrar).toFixed(2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-size:13px; color:#64748B;">Pagado</span>
                            <span style="font-size:13px; font-weight:700; color:#10B981;">S/ {{ Number(acto.monto_pagado).toFixed(2) }}</span>
                        </div>
                        <div style="border-top:1px solid #E2E8F0; padding-top:6px; display:flex; justify-content:space-between;">
                            <span style="font-size:13px; font-weight:600; color:#1E293B;">Saldo pendiente</span>
                            <span style="font-size:15px; font-weight:800; color:#EF4444;">S/ {{ (Number(acto.monto_cobrar) - Number(acto.monto_pagado)).toFixed(2) }}</span>
                        </div>
                    </div>
                    <!-- Barra de progreso -->
                    <div style="background:#E2E8F0; border-radius:20px; height:8px; margin-bottom:12px;">
                        <div :style="{width: Math.min(100, (acto.monto_pagado/acto.monto_cobrar)*100) + '%', background:'#10B981', borderRadius:'20px', height:'100%', transition:'width .3s'}"></div>
                    </div>
                    <button v-if="acto.estado_pago !== 'pagado'" @click="modalPago=true"
                        style="width:100%; padding:10px; background:linear-gradient(135deg,#10B981,#059669); color:white; border:none; border-radius:9px; font-size:13px; font-weight:600; cursor:pointer;">
                        💵 Registrar pago
                    </button>
                    <p v-else style="text-align:center; color:#10B981; font-weight:600; font-size:13px; margin:0;">✅ Cobro completado</p>
                </div>

                <!-- CAMBIAR ESTADO RÁPIDO -->
                <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.25rem;">
                    <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0 0 10px;">⚡ Cambiar estado</p>
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        <button v-for="e in estados" :key="e.value"
                            @click="cambiarEstadoRapido(e.value)"
                            :disabled="acto.estado === e.value"
                            :style="{
                                padding:'8px 12px', borderRadius:'8px', border:'1.5px solid',
                                borderColor: acto.estado===e.value ? e.color : '#E2E8F0',
                                background: acto.estado===e.value ? e.bg : 'white',
                                color: acto.estado===e.value ? e.color : '#64748B',
                                fontSize:'13px', fontWeight:'600', cursor: acto.estado===e.value ? 'default' : 'pointer',
                                textAlign:'left'
                            }">
                            {{ e.icon }} {{ e.label }} {{ acto.estado===e.value ? '← actual' : '' }}
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- MODAL ACTUALIZAR ESTADO -->
        <div v-if="modalEstado" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:100; display:flex; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:16px; padding:1.5rem; width:400px; max-width:95vw;">
                <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 1.2rem;">🔄 Actualizar estado</p>
                <div style="margin-bottom:1rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Nuevo estado *</label>
                    <select v-model="formEstado.estado" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                        <option v-for="e in estados" :key="e.value" :value="e.value">{{ e.icon }} {{ e.label }}</option>
                    </select>
                </div>
                <div style="margin-bottom:1.2rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Comentario</label>
                    <textarea v-model="formEstado.comentario" rows="3" placeholder="Detalle del cambio de estado..." style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <button @click="modalEstado=false" style="padding:9px 18px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Cancelar</button>
                    <button @click="guardarEstado" style="padding:9px 18px; background:#4F46E5; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">Guardar</button>
                </div>
            </div>
        </div>

        <!-- MODAL PAGO -->
        <div v-if="modalPago" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:100; display:flex; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:16px; padding:1.5rem; width:360px; max-width:95vw;">
                <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 1.2rem;">💰 Registrar pago</p>
                <div style="margin-bottom:1rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Monto a pagar (S/) *</label>
                    <input v-model="formPago.monto" type="number" step="0.01" min="0.01"
                        style="width:100%; padding:12px; border:2px solid #E2E8F0; border-radius:10px; font-size:20px; font-weight:700; outline:none; box-sizing:border-box; text-align:right;" />
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <button @click="modalPago=false" style="padding:9px 18px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Cancelar</button>
                    <button @click="guardarPago" style="padding:9px 18px; background:#10B981; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">✅ Confirmar</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    acto: { type: Object, default: () => ({}) }
})

const modalEstado = ref(false)
const modalPago   = ref(false)
const formEstado  = ref({ estado: props.acto.estado, comentario: '' })
const formPago    = ref({ monto: (Number(props.acto.monto_cobrar) - Number(props.acto.monto_pagado)).toFixed(2) })

const tiposActo = [
    { value: 'escritura_publica', label: '📜 Escritura pública' },
    { value: 'poder',             label: '✍️ Poder' },
    { value: 'testamento',        label: '📋 Testamento' },
    { value: 'legalizacion',      label: '🔏 Legalización' },
    { value: 'carta_notarial',    label: '✉️ Carta notarial' },
    { value: 'protesto',          label: '⚖️ Protesto' },
    { value: 'acta_notarial',     label: '📝 Acta notarial' },
    { value: 'otro',              label: '📁 Otro' },
]

const estados = [
    { value: 'pendiente',  label: 'Pendiente',  icon: '🕐', color: '#92400E', bg: '#FEF3C7' },
    { value: 'en_proceso', label: 'En proceso', icon: '🔄', color: '#1D4ED8', bg: '#EFF6FF' },
    { value: 'finalizado', label: 'Finalizado', icon: '✅', color: '#166534', bg: '#F0FDF4' },
    { value: 'cancelado',  label: 'Cancelado',  icon: '❌', color: '#991B1B', bg: '#FEF2F2' },
]

function labelTipo(t) { return tiposActo.find(x => x.value === t)?.label ?? t }
function labelEstado(e) { return estados.find(x => x.value === e)?.label ?? e }
function labelPago(p) { return { pendiente: '⏳ Pendiente', parcial: '◑ Parcial', pagado: '✅ Pagado' }[p] ?? p }

function estiloEstado(e) {
    const map = { pendiente:{background:'#FEF3C7',color:'#92400E'}, en_proceso:{background:'#EFF6FF',color:'#1D4ED8'}, finalizado:{background:'#F0FDF4',color:'#166534'}, cancelado:{background:'#FEF2F2',color:'#991B1B'} }
    return { ...(map[e]||{}), fontSize:'12px', padding:'4px 10px', borderRadius:'20px', fontWeight:'600' }
}

function estiloPago(p) {
    const map = { pendiente:{background:'#FEF2F2',color:'#991B1B'}, parcial:{background:'#FEF3C7',color:'#92400E'}, pagado:{background:'#F0FDF4',color:'#166634'} }
    return { ...(map[p]||{}), fontSize:'12px', padding:'4px 10px', borderRadius:'20px', fontWeight:'600' }
}

function formatFecha(f) {
    if (!f) return '—'
    return new Date(f + 'T00:00:00').toLocaleDateString('es-PE', { day:'2-digit', month:'2-digit', year:'numeric' })
}

function formatFechaHora(f) {
    if (!f) return '—'
    const d = new Date(f)
    return d.toLocaleDateString('es-PE', { day:'2-digit', month:'2-digit', year:'2-digit' }) + ' ' + d.toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' })
}

function cambiarEstadoRapido(estado) {
    if (acto.value?.estado === estado) return
    router.post('/notaria/actos/' + props.acto.id + '/estado', { estado, comentario: '' }, { preserveScroll: true })
}

function guardarEstado() {
    router.post('/notaria/actos/' + props.acto.id + '/estado', formEstado.value, {
        preserveScroll: true,
        onSuccess: () => { modalEstado.value = false }
    })
}

function guardarPago() {
    if (!formPago.value.monto) { alert('Ingresa el monto'); return }
    router.post('/notaria/actos/' + props.acto.id + '/pago', formPago.value, {
        preserveScroll: true,
        onSuccess: () => { modalPago.value = false }
    })
}
</script>
