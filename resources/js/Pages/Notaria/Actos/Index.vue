<template>
    <AppLayout title="Expedientes notariales" subtitle="Gestión de actos y trámites notariales">

        <!-- MÉTRICAS -->
        <div style="display:grid; grid-template-columns:repeat(6,1fr); gap:12px; margin-bottom:1.5rem;">
            <div v-for="(card,i) in tarjetas" :key="i" style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1rem 1.1rem;">
                <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 5px;">{{ card.label }}</p>
                <p style="font-size:20px; font-weight:800; margin:0;" :style="{color: card.color || '#1E293B'}">{{ card.valor }}</p>
            </div>
        </div>

        <!-- FILTROS + BOTÓN NUEVO -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">Desde</label>
                <input v-model="filtros.desde" type="date" style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">Hasta</label>
                <input v-model="filtros.hasta" type="date" style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">Tipo acto</label>
                <select v-model="filtros.tipo" style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                    <option value="">Todos</option>
                    <option v-for="t in tiposActo" :key="t.value" :value="t.value">{{ t.label }}</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">Estado</label>
                <select v-model="filtros.estado" style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="en_proceso">En proceso</option>
                    <option value="finalizado">Finalizado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">Buscar</label>
                <input v-model="filtros.buscar" @keyup.enter="buscar" placeholder="Expediente, asunto, partes..." style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; width:220px;" />
            </div>
            <button @click="buscar" style="padding:7px 18px; background:linear-gradient(135deg,#6366F1,#4F46E5); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">Buscar</button>
            <button @click="modalNuevo=true" style="margin-left:auto; padding:7px 18px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">+ Nuevo expediente</button>
        </div>

        <!-- TABLA -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Expediente</th>
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Tipo acto</th>
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Asunto</th>
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Partes</th>
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Ingreso</th>
                        <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Estado</th>
                        <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Pago</th>
                        <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Monto</th>
                        <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="actos.length === 0">
                        <td colspan="9" style="padding:2rem; text-align:center; color:#94A3B8;">Sin expedientes en este período</td>
                    </tr>
                    <tr v-for="a in actos" :key="a.id" style="border-top:1px solid #F1F5F9; cursor:pointer;"
                        @mouseover="e => e.currentTarget.style.background='#F8FAFC'"
                        @mouseleave="e => e.currentTarget.style.background='white'">
                        <td style="padding:11px 16px; font-family:monospace; font-size:12px; font-weight:700; color:#4F46E5;">{{ a.numero_expediente }}</td>
                        <td style="padding:11px 16px;">
                            <span :style="estiloTipo(a.tipo_acto)">{{ labelTipo(a.tipo_acto) }}</span>
                        </td>
                        <td style="padding:11px 16px; color:#1E293B; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ a.asunto }}</td>
                        <td style="padding:11px 16px; color:#64748B; font-size:12px; max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ a.partes_intervinientes || '—' }}</td>
                        <td style="padding:11px 16px; color:#64748B; font-size:12px;">{{ formatFecha(a.fecha_ingreso) }}</td>
                        <td style="padding:11px 16px; text-align:center;">
                            <span :style="estiloEstado(a.estado)">{{ labelEstado(a.estado) }}</span>
                        </td>
                        <td style="padding:11px 16px; text-align:center;">
                            <span :style="estiloPago(a.estado_pago)">{{ labelPago(a.estado_pago) }}</span>
                        </td>
                        <td style="padding:11px 16px; text-align:right; font-weight:700; color:#0F766E;">S/ {{ Number(a.monto_cobrar).toFixed(2) }}</td>
                        <td style="padding:11px 16px; text-align:center; display:flex; gap:5px; justify-content:center;">
                            <button @click="verDetalle(a)" style="padding:4px 10px; background:#EEF2FF; color:#4F46E5; border:1px solid #C7D2FE; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">Ver</button>
                            <button @click="abrirPago(a)" style="padding:4px 10px; background:#F0FDF4; color:#166534; border:1px solid #BBF7D0; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">Pagar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- MODAL NUEVO EXPEDIENTE -->
        <div v-if="modalNuevo" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:100; display:flex; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:16px; padding:1.5rem; width:560px; max-width:95vw; max-height:90vh; overflow-y:auto;">
                <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 1.2rem;">📄 Nuevo expediente notarial</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Tipo de acto *</label>
                        <select v-model="formNuevo.tipo_acto" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                            <option value="">Seleccionar...</option>
                            <option v-for="t in tiposActo" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Asunto *</label>
                        <input v-model="formNuevo.asunto" type="text" placeholder="Descripción del acto notarial" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Partes intervinientes</label>
                        <textarea v-model="formNuevo.partes_intervinientes" placeholder="Nombres de las partes involucradas..." rows="2" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Fecha de ingreso *</label>
                        <input v-model="formNuevo.fecha_ingreso" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Fecha entrega estimada</label>
                        <input v-model="formNuevo.fecha_entrega" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Monto a cobrar (S/) *</label>
                        <input v-model="formNuevo.monto_cobrar" type="number" step="0.01" min="0" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Cliente</label>
                        <select v-model="formNuevo.cliente_id" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                            <option value="">Sin cliente registrado</option>
                            <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                        </select>
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Observaciones</label>
                        <textarea v-model="formNuevo.observaciones" rows="2" placeholder="Notas adicionales..." style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                    </div>
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:1.2rem;">
                    <button @click="modalNuevo=false" style="padding:9px 18px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Cancelar</button>
                    <button @click="guardarNuevo" style="padding:9px 18px; background:linear-gradient(135deg,#6366F1,#4F46E5); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">Crear expediente</button>
                </div>
            </div>
        </div>

        <!-- MODAL PAGO -->
        <div v-if="modalPago" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:100; display:flex; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:16px; padding:1.5rem; width:380px; max-width:95vw;">
                <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 4px;">💰 Registrar pago</p>
                <p style="font-size:13px; color:#64748B; margin:0 0 1.2rem;">{{ actoSeleccionado?.numero_expediente }} — {{ actoSeleccionado?.asunto }}</p>
                <div style="background:#F8FAFC; border-radius:10px; padding:12px 16px; margin-bottom:1rem;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                        <span style="font-size:13px; color:#64748B;">Total a cobrar</span>
                        <span style="font-size:13px; font-weight:700;">S/ {{ Number(actoSeleccionado?.monto_cobrar).toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                        <span style="font-size:13px; color:#64748B;">Ya pagado</span>
                        <span style="font-size:13px; font-weight:700; color:#0F766E;">S/ {{ Number(actoSeleccionado?.monto_pagado).toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-size:13px; color:#64748B;">Saldo pendiente</span>
                        <span style="font-size:14px; font-weight:800; color:#EF4444;">S/ {{ (Number(actoSeleccionado?.monto_cobrar) - Number(actoSeleccionado?.monto_pagado)).toFixed(2) }}</span>
                    </div>
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Monto a pagar (S/) *</label>
                    <input v-model="formPago.monto" type="number" step="0.01" min="0.01"
                        style="width:100%; padding:12px; border:2px solid #E2E8F0; border-radius:10px; font-size:18px; font-weight:700; outline:none; box-sizing:border-box; text-align:right;" />
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <button @click="modalPago=false" style="padding:9px 18px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Cancelar</button>
                    <button @click="guardarPago" style="padding:9px 18px; background:#10B981; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">✅ Confirmar pago</button>
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
    actos:    { type: Array,  default: () => [] },
    resumen:  { type: Object, default: () => ({}) },
    clientes: { type: Array,  default: () => [] },
    filtros:  { type: Object, default: () => ({}) },
})

const filtros         = ref({ ...props.filtros })
const modalNuevo      = ref(false)
const modalPago       = ref(false)
const actoSeleccionado = ref(null)
const formNuevo = ref({ tipo_acto: '', asunto: '', partes_intervinientes: '', fecha_ingreso: new Date().toISOString().slice(0,10), fecha_entrega: '', monto_cobrar: '', cliente_id: '', observaciones: '' })
const formPago  = ref({ monto: '' })

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

const tarjetas = computed(() => [
    { label: 'Total expedientes', valor: props.resumen.total },
    { label: 'Pendientes',        valor: props.resumen.pendientes,  color: '#F59E0B' },
    { label: 'En proceso',        valor: props.resumen.en_proceso,  color: '#3B82F6' },
    { label: 'Finalizados',       valor: props.resumen.finalizados, color: '#10B981' },
    { label: 'Por cobrar',        valor: 'S/ ' + Number(props.resumen.por_cobrar).toFixed(2), color: '#EF4444' },
    { label: 'Cobrado',           valor: 'S/ ' + Number(props.resumen.cobrado).toFixed(2),    color: '#10B981' },
])

function buscar() {
    const params = new URLSearchParams()
    if (filtros.value.desde)  params.set('desde',  filtros.value.desde)
    if (filtros.value.hasta)  params.set('hasta',  filtros.value.hasta)
    if (filtros.value.tipo)   params.set('tipo',   filtros.value.tipo)
    if (filtros.value.estado) params.set('estado', filtros.value.estado)
    if (filtros.value.buscar) params.set('buscar', filtros.value.buscar)
    router.visit('/notaria/actos?' + params.toString(), { preserveScroll: true })
}

function guardarNuevo() {
    if (!formNuevo.value.tipo_acto || !formNuevo.value.asunto || !formNuevo.value.monto_cobrar) {
        alert('Completa los campos obligatorios')
        return
    }
    router.post('/notaria/actos', formNuevo.value, {
        preserveScroll: true,
        onSuccess: () => {
            modalNuevo.value = false
            formNuevo.value = { tipo_acto: '', asunto: '', partes_intervinientes: '', fecha_ingreso: new Date().toISOString().slice(0,10), fecha_entrega: '', monto_cobrar: '', cliente_id: '', observaciones: '' }
        }
    })
}

function verDetalle(acto) {
    router.visit('/notaria/actos/' + acto.id)
}

function abrirPago(acto) {
    actoSeleccionado.value = acto
    formPago.value = { monto: (Number(acto.monto_cobrar) - Number(acto.monto_pagado)).toFixed(2) }
    modalPago.value = true
}

function guardarPago() {
    if (!formPago.value.monto) { alert('Ingresa el monto'); return }
    router.post('/notaria/actos/' + actoSeleccionado.value.id + '/pago', formPago.value, {
        preserveScroll: true,
        onSuccess: () => { modalPago.value = false }
    })
}

function formatFecha(f) {
    if (!f) return '—'
    return new Date(f + 'T00:00:00').toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: '2-digit' })
}

function labelTipo(t) {
    return tiposActo.find(x => x.value === t)?.label ?? t
}

function estiloTipo(t) {
    const map = {
        escritura_publica: { background: '#EEF2FF', color: '#4338CA' },
        poder:             { background: '#FEF3C7', color: '#92400E' },
        testamento:        { background: '#F5F3FF', color: '#6D28D9' },
        legalizacion:      { background: '#ECFDF5', color: '#065F46' },
        carta_notarial:    { background: '#EFF6FF', color: '#1D4ED8' },
        protesto:          { background: '#FEF2F2', color: '#991B1B' },
        acta_notarial:     { background: '#F0FDF4', color: '#166534' },
        otro:              { background: '#F1F5F9', color: '#475569' },
    }
    return { ...(map[t] || map.otro), fontSize: '11px', padding: '3px 9px', borderRadius: '20px', fontWeight: '600', whiteSpace: 'nowrap' }
}

function labelEstado(e) {
    return { pendiente: '🕐 Pendiente', en_proceso: '🔄 En proceso', finalizado: '✅ Finalizado', cancelado: '❌ Cancelado' }[e] ?? e
}

function estiloEstado(e) {
    const map = {
        pendiente:  { background: '#FEF3C7', color: '#92400E' },
        en_proceso: { background: '#EFF6FF', color: '#1D4ED8' },
        finalizado: { background: '#F0FDF4', color: '#166534' },
        cancelado:  { background: '#FEF2F2', color: '#991B1B' },
    }
    return { ...(map[e] || {}), fontSize: '11px', padding: '3px 9px', borderRadius: '20px', fontWeight: '600', whiteSpace: 'nowrap' }
}

function labelPago(p) {
    return { pendiente: '⏳ Pendiente', parcial: '◑ Parcial', pagado: '✅ Pagado' }[p] ?? p
}

function estiloPago(p) {
    const map = {
        pendiente: { background: '#FEF2F2', color: '#991B1B' },
        parcial:   { background: '#FEF3C7', color: '#92400E' },
        pagado:    { background: '#F0FDF4', color: '#166534' },
    }
    return { ...(map[p] || {}), fontSize: '11px', padding: '3px 9px', borderRadius: '20px', fontWeight: '600', whiteSpace: 'nowrap' }
}
</script>
