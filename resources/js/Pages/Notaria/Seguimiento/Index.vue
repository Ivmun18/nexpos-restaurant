<template>
    <AppLayout title="Seguimiento de trámites" subtitle="Consulta el estado de un expediente notarial">

        <!-- BUSCADOR PRINCIPAL -->
        <div style="max-width:700px; margin:0 auto;">

            <div style="background:white; border-radius:16px; border:1px solid #E2E8F0; padding:2rem; margin-bottom:1.5rem; text-align:center;">
                <p style="font-size:32px; margin:0 0 8px;">🔍</p>
                <p style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 4px;">Consulta tu trámite</p>
                <p style="font-size:13px; color:#94A3B8; margin:0 0 1.5rem;">Ingresa el número de expediente, nombre o DNI para ver el estado</p>
                <div style="display:flex; gap:8px;">
                    <input v-model="busqueda" @keyup.enter="buscar" type="text"
                        placeholder="Ej: EXP-2026-00001, Juan Pérez, 23456789..."
                        style="flex:1; padding:12px 16px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; transition:border .2s;"
                        @focus="e => e.target.style.borderColor='#6366F1'"
                        @blur="e => e.target.style.borderColor='#E2E8F0'" />
                    <button @click="buscar"
                        style="padding:12px 24px; background:linear-gradient(135deg,#6366F1,#4F46E5); color:white; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer;">
                        Buscar
                    </button>
                </div>
            </div>

            <!-- RESULTADOS -->
            <div v-if="buscado && resultados.length === 0" style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:2rem; text-align:center; color:#94A3B8;">
                <p style="font-size:24px; margin:0 0 8px;">😕</p>
                <p style="font-size:14px; margin:0;">No se encontraron expedientes con ese criterio</p>
            </div>

            <div v-for="a in resultados" :key="a.id" style="background:white; border-radius:12px; border:1px solid #E2E8F0; margin-bottom:12px; overflow:hidden;">

                <!-- Header expediente -->
                <div :style="{padding:'1rem 1.25rem', borderBottom:'1px solid #F1F5F9', background: bgEstado(a.estado)}">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div>
                            <span style="font-family:monospace; font-size:14px; font-weight:700; color:#4F46E5;">{{ a.numero_expediente }}</span>
                            <span style="margin-left:10px; font-size:12px; color:#64748B;">{{ labelTipo(a.tipo_acto) }}</span>
                        </div>
                        <span :style="estiloEstado(a.estado)">{{ labelEstado(a.estado) }}</span>
                    </div>
                    <p style="font-size:14px; font-weight:600; color:#1E293B; margin:4px 0 0;">{{ a.asunto }}</p>
                    <p style="font-size:12px; color:#64748B; margin:2px 0 0;">{{ a.partes_intervinientes || '—' }}</p>
                </div>

                <div style="padding:1rem 1.25rem;">
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:1rem;">
                        <div>
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Fecha ingreso</p>
                            <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0;">{{ formatFecha(a.fecha_ingreso) }}</p>
                        </div>
                        <div>
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Fecha entrega</p>
                            <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0;">{{ a.fecha_entrega ? formatFecha(a.fecha_entrega) : 'Por definir' }}</p>
                        </div>
                        <div>
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Estado de pago</p>
                            <span :style="estiloPago(a.estado_pago)">{{ labelPago(a.estado_pago) }}</span>
                        </div>
                    </div>

                    <!-- Documentos pendientes -->
                    <div v-if="a.requisitos_pendientes > 0"
                        style="background:#FEF2F2; border:1px solid #FECACA; border-radius:10px; padding:12px 14px; margin-bottom:1rem;">
                        <p style="font-size:12px; font-weight:700; color:#991B1B; margin:0 0 8px;">⚠️ Documentos pendientes de entrega</p>
                        <div v-for="r in a.requisitos_faltantes" :key="r.id" style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                            <span style="color:#EF4444; font-size:14px;">❌</span>
                            <span style="font-size:13px; color:#991B1B;">{{ r.documento }}</span>
                        </div>
                    </div>

                    <!-- Documentos entregados -->
                    <div v-if="a.requisitos_entregados > 0"
                        style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:10px; padding:12px 14px; margin-bottom:1rem;">
                        <p style="font-size:12px; font-weight:700; color:#166534; margin:0 0 8px;">✅ Documentos recibidos</p>
                        <div v-for="r in a.requisitos_ok" :key="r.id" style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                            <span style="color:#10B981; font-size:14px;">✅</span>
                            <span style="font-size:13px; color:#166534;">{{ r.documento }}</span>
                        </div>
                    </div>

                    <!-- Último seguimiento -->
                    <div v-if="a.ultimo_seguimiento" style="background:#F8FAFC; border-radius:10px; padding:10px 14px;">
                        <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 4px;">Última actualización</p>
                        <p style="font-size:13px; color:#1E293B; margin:0;">
                            <span :style="estiloEstado(a.ultimo_seguimiento.estado_nuevo)">{{ labelEstado(a.ultimo_seguimiento.estado_nuevo) }}</span>
                            <span v-if="a.ultimo_seguimiento.comentario" style="margin-left:8px; color:#64748B;">— {{ a.ultimo_seguimiento.comentario }}</span>
                        </p>
                        <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">{{ formatFechaHora(a.ultimo_seguimiento.created_at) }} · {{ a.ultimo_seguimiento.usuario }}</p>
                    </div>
                </div>
            </div>

            <!-- LISTA COMPLETA (filtro por estado) -->
            <div v-if="!buscado" style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
                <div style="padding:1rem 1.25rem; border-bottom:1px solid #F1F5F9; display:flex; gap:8px; flex-wrap:wrap;">
                    <button v-for="f in filtros" :key="f.value" @click="filtroActivo=f.value"
                        :style="{padding:'5px 14px', borderRadius:'20px', border:'1.5px solid', fontSize:'12px', fontWeight:'600', cursor:'pointer',
                            borderColor: filtroActivo===f.value ? f.color : '#E2E8F0',
                            background: filtroActivo===f.value ? f.bg : 'white',
                            color: filtroActivo===f.value ? f.color : '#64748B'}">
                        {{ f.label }} ({{ contarEstado(f.value) }})
                    </button>
                </div>
                <div v-if="listaActiva.length === 0" style="padding:2rem; text-align:center; color:#94A3B8; font-size:13px;">
                    Sin expedientes en este estado
                </div>
                <div v-for="a in listaActiva" :key="a.id"
                    style="padding:12px 16px; border-top:1px solid #F1F5F9; display:flex; align-items:center; gap:12px; cursor:pointer;"
                    @click="router.visit('/notaria/actos/' + a.id)"
                    @mouseover="e => e.currentTarget.style.background='#F8FAFC'"
                    @mouseleave="e => e.currentTarget.style.background='white'">
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:2px;">
                            <span style="font-family:monospace; font-size:12px; font-weight:700; color:#4F46E5;">{{ a.numero_expediente }}</span>
                            <span style="font-size:11px; color:#94A3B8;">{{ labelTipo(a.tipo_acto) }}</span>
                            <span v-if="a.requisitos_pendientes > 0" style="background:#FEF2F2; color:#991B1B; padding:1px 6px; border-radius:10px; font-size:10px; font-weight:600;">❌ {{ a.requisitos_pendientes }} docs</span>
                        </div>
                        <p style="font-size:13px; color:#1E293B; font-weight:500; margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ a.asunto }}</p>
                        <p style="font-size:11px; color:#94A3B8; margin:2px 0 0;">{{ a.partes_intervinientes || '—' }}</p>
                    </div>
                    <div style="text-align:right; flex-shrink:0;">
                        <span :style="estiloPago(a.estado_pago)">{{ labelPago(a.estado_pago) }}</span>
                        <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">{{ a.fecha_entrega ? formatFecha(a.fecha_entrega) : '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    pendientes:  { type: Array, default: () => [] },
    en_proceso:  { type: Array, default: () => [] },
    finalizados: { type: Array, default: () => [] },
    todos:       { type: Array, default: () => [] },
})

const busqueda    = ref('')
const buscado     = ref(false)
const filtroActivo = ref('todos')

const filtros = [
    { value: 'todos',      label: 'Todos',      color: '#6366F1', bg: '#EEF2FF' },
    { value: 'pendiente',  label: 'Pendiente',  color: '#F59E0B', bg: '#FEF3C7' },
    { value: 'en_proceso', label: 'En proceso', color: '#3B82F6', bg: '#EFF6FF' },
    { value: 'finalizado', label: 'Finalizado', color: '#10B981', bg: '#F0FDF4' },
]

const resultados = ref([])

function buscar() {
    if (!busqueda.value.trim()) { buscado.value = false; return }
    buscado.value = true
    const q = busqueda.value.toLowerCase()
    resultados.value = props.todos.filter(a =>
        a.numero_expediente.toLowerCase().includes(q) ||
        a.asunto.toLowerCase().includes(q) ||
        (a.partes_intervinientes || '').toLowerCase().includes(q)
    )
}

const listaActiva = computed(() => {
    const base = filtroActivo.value === 'todos' ? props.todos
        : filtroActivo.value === 'pendiente'  ? props.pendientes
        : filtroActivo.value === 'en_proceso' ? props.en_proceso
        : props.finalizados
    return base
})

function contarEstado(e) {
    return e === 'todos' ? props.todos.length
        : e === 'pendiente'  ? props.pendientes.length
        : e === 'en_proceso' ? props.en_proceso.length
        : props.finalizados.length
}

function formatFecha(f) {
    if (!f) return '—'
    const fecha = f.includes('T') || f.includes(' ') ? new Date(f) : new Date(f + 'T12:00:00')
    if (isNaN(fecha)) return '—'
    return fecha.toLocaleDateString('es-PE', { day:'2-digit', month:'2-digit', year:'2-digit' })
}

function formatFechaHora(f) {
    if (!f) return '—'
    const d = new Date(f)
    return d.toLocaleDateString('es-PE', { day:'2-digit', month:'2-digit', year:'2-digit' }) + ' ' +
           d.toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' })
}

const tiposActo = { escritura_publica:'📜 Escritura', poder:'✍️ Poder', testamento:'📋 Testamento', legalizacion:'🔏 Legalización', carta_notarial:'✉️ Carta notarial', protesto:'⚖️ Protesto', acta_notarial:'📝 Acta', otro:'📁 Otro' }
function labelTipo(t) { return tiposActo[t] ?? t }
function labelEstado(e) { return {pendiente:'🕐 Pendiente', en_proceso:'🔄 En proceso', finalizado:'✅ Finalizado', cancelado:'❌ Cancelado'}[e] ?? e }
function estiloEstado(e) {
    const m = {pendiente:{background:'#FEF3C7',color:'#92400E'}, en_proceso:{background:'#EFF6FF',color:'#1D4ED8'}, finalizado:{background:'#F0FDF4',color:'#166534'}, cancelado:{background:'#FEF2F2',color:'#991B1B'}}
    return {...(m[e]||{}), fontSize:'12px', padding:'4px 10px', borderRadius:'20px', fontWeight:'600'}
}
function labelPago(p) { return {pendiente:'⏳ Pendiente', parcial:'◑ Parcial', pagado:'✅ Pagado'}[p] ?? p }
function estiloPago(p) {
    const m = {pendiente:{background:'#FEF2F2',color:'#991B1B'}, parcial:{background:'#FEF3C7',color:'#92400E'}, pagado:{background:'#F0FDF4',color:'#166534'}}
    return {...(m[p]||{}), fontSize:'11px', padding:'3px 9px', borderRadius:'20px', fontWeight:'600'}
}
function bgEstado(e) { return {pendiente:'#FFFBEB', en_proceso:'#EFF6FF', finalizado:'#F0FDF4', cancelado:'#FEF2F2'}[e] ?? '#F8FAFC' }
</script>
