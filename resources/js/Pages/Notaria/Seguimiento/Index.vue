<template>
    <AppLayout title="Seguimiento de trámites" subtitle="Estado en tiempo real de todos los expedientes">

        <!-- TOGGLE VISTA -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
            <div style="display:flex; gap:6px;">
                <button @click="vista='kanban'"
                    :style="{padding:'8px 16px', borderRadius:'8px', border:'1.5px solid', fontSize:'13px', fontWeight:'600', cursor:'pointer',
                        borderColor: vista==='kanban'?'#6366F1':'#E2E8F0',
                        background: vista==='kanban'?'#EEF2FF':'white',
                        color: vista==='kanban'?'#4F46E5':'#64748B'}">
                    🗂️ Kanban
                </button>
                <button @click="vista='lista'"
                    :style="{padding:'8px 16px', borderRadius:'8px', border:'1.5px solid', fontSize:'13px', fontWeight:'600', cursor:'pointer',
                        borderColor: vista==='lista'?'#6366F1':'#E2E8F0',
                        background: vista==='lista'?'#EEF2FF':'white',
                        color: vista==='lista'?'#4F46E5':'#64748B'}">
                    📋 Lista
                </button>
            </div>
            <div style="display:flex; gap:8px;">
                <input v-model="busqueda" placeholder="Buscar expediente..." style="padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; width:220px;" />
                <button @click="router.reload()" style="padding:8px 14px; background:#F1F5F9; border:none; border-radius:8px; font-size:13px; cursor:pointer; color:#64748B;">🔄 Actualizar</button>
            </div>
        </div>

        <!-- VISTA KANBAN -->
        <div v-if="vista === 'kanban'" style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">

            <!-- PENDIENTE -->
            <div style="background:#FFFBEB; border-radius:14px; padding:1rem; border:2px solid #FDE68A; min-height:400px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:1rem;">
                    <div style="width:40px; height:40px; background:linear-gradient(135deg,#F59E0B,#D97706); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px;">🕐</div>
                    <div>
                        <p style="font-size:14px; font-weight:700; color:#92400E; margin:0;">PENDIENTE</p>
                        <p style="font-size:12px; color:#B45309; margin:0;">{{ pendientesFiltrados.length }} expedientes</p>
                    </div>
                </div>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <div v-if="pendientesFiltrados.length === 0" style="text-align:center; color:#B45309; font-size:13px; padding:2rem;">Sin expedientes pendientes</div>
                    <div v-for="a in pendientesFiltrados" :key="a.id" @click="verExpediente(a.id)"
                        style="background:white; border-radius:10px; padding:12px; cursor:pointer; border:1px solid #FDE68A; transition:all .15s;"
                        @mouseover="e => e.currentTarget.style.boxShadow='0 4px 12px rgba(245,158,11,.15)'"
                        @mouseleave="e => e.currentTarget.style.boxShadow='none'">
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-family:monospace; font-size:12px; font-weight:700; color:#4F46E5;">{{ a.numero_expediente }}</span>
                            <span v-if="a.requisitos_pendientes > 0" style="background:#FEF2F2; color:#991B1B; padding:2px 7px; border-radius:20px; font-size:10px; font-weight:600;">❌ {{ a.requisitos_pendientes }} docs</span>
                        </div>
                        <p style="font-size:12px; font-weight:600; color:#1E293B; margin:0 0 3px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ a.asunto }}</p>
                        <p style="font-size:11px; color:#94A3B8; margin:0 0 6px;">{{ labelTipo(a.tipo_acto) }}</p>
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:11px; color:#64748B;">{{ a.partes_intervinientes?.split('/')[0]?.trim() || '—' }}</span>
                            <span :style="estiloPago(a.estado_pago)">{{ labelPago(a.estado_pago) }}</span>
                        </div>
                        <div v-if="a.fecha_entrega" style="margin-top:6px; font-size:10px; color:#94A3B8;">
                            📅 Entrega: {{ formatFecha(a.fecha_entrega) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- EN PROCESO -->
            <div style="background:#EFF6FF; border-radius:14px; padding:1rem; border:2px solid #BFDBFE; min-height:400px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:1rem;">
                    <div style="width:40px; height:40px; background:linear-gradient(135deg,#3B82F6,#1D4ED8); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px;">🔄</div>
                    <div>
                        <p style="font-size:14px; font-weight:700; color:#1D4ED8; margin:0;">EN PROCESO</p>
                        <p style="font-size:12px; color:#3B82F6; margin:0;">{{ enProcesoFiltrados.length }} expedientes</p>
                    </div>
                </div>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <div v-if="enProcesoFiltrados.length === 0" style="text-align:center; color:#3B82F6; font-size:13px; padding:2rem;">Sin expedientes en proceso</div>
                    <div v-for="a in enProcesoFiltrados" :key="a.id" @click="verExpediente(a.id)"
                        style="background:white; border-radius:10px; padding:12px; cursor:pointer; border:1px solid #BFDBFE; transition:all .15s;"
                        @mouseover="e => e.currentTarget.style.boxShadow='0 4px 12px rgba(59,130,246,.15)'"
                        @mouseleave="e => e.currentTarget.style.boxShadow='none'">
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-family:monospace; font-size:12px; font-weight:700; color:#4F46E5;">{{ a.numero_expediente }}</span>
                            <span v-if="a.requisitos_pendientes > 0" style="background:#FEF2F2; color:#991B1B; padding:2px 7px; border-radius:20px; font-size:10px; font-weight:600;">❌ {{ a.requisitos_pendientes }} docs</span>
                        </div>
                        <p style="font-size:12px; font-weight:600; color:#1E293B; margin:0 0 3px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ a.asunto }}</p>
                        <p style="font-size:11px; color:#94A3B8; margin:0 0 6px;">{{ labelTipo(a.tipo_acto) }}</p>
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:11px; color:#64748B;">{{ a.partes_intervinientes?.split('/')[0]?.trim() || '—' }}</span>
                            <span :style="estiloPago(a.estado_pago)">{{ labelPago(a.estado_pago) }}</span>
                        </div>
                        <div v-if="a.fecha_entrega" style="margin-top:6px; font-size:10px; color:#94A3B8;">
                            📅 Entrega: {{ formatFecha(a.fecha_entrega) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- FINALIZADO -->
            <div style="background:#F0FDF4; border-radius:14px; padding:1rem; border:2px solid #BBF7D0; min-height:400px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:1rem;">
                    <div style="width:40px; height:40px; background:linear-gradient(135deg,#10B981,#059669); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px;">✅</div>
                    <div>
                        <p style="font-size:14px; font-weight:700; color:#065F46; margin:0;">FINALIZADO</p>
                        <p style="font-size:12px; color:#059669; margin:0;">{{ finalizadosFiltrados.length }} expedientes</p>
                    </div>
                </div>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <div v-if="finalizadosFiltrados.length === 0" style="text-align:center; color:#059669; font-size:13px; padding:2rem;">Sin expedientes finalizados</div>
                    <div v-for="a in finalizadosFiltrados" :key="a.id" @click="verExpediente(a.id)"
                        style="background:white; border-radius:10px; padding:12px; cursor:pointer; border:1px solid #BBF7D0; transition:all .15s;"
                        @mouseover="e => e.currentTarget.style.boxShadow='0 4px 12px rgba(16,185,129,.15)'"
                        @mouseleave="e => e.currentTarget.style.boxShadow='none'">
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-family:monospace; font-size:12px; font-weight:700; color:#4F46E5;">{{ a.numero_expediente }}</span>
                            <span :style="estiloPago(a.estado_pago)">{{ labelPago(a.estado_pago) }}</span>
                        </div>
                        <p style="font-size:12px; font-weight:600; color:#1E293B; margin:0 0 3px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ a.asunto }}</p>
                        <p style="font-size:11px; color:#94A3B8; margin:0 0 6px;">{{ labelTipo(a.tipo_acto) }}</p>
                        <span style="font-size:11px; color:#64748B;">{{ a.partes_intervinientes?.split('/')[0]?.trim() || '—' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- VISTA LISTA -->
        <div v-if="vista === 'lista'" style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
            <div style="padding:1rem 1.5rem; border-bottom:1px solid #F1F5F9; display:flex; gap:8px;">
                <button v-for="f in ['todos','pendiente','en_proceso','finalizado']" :key="f" @click="filtroEstado=f"
                    :style="{padding:'5px 14px', borderRadius:'20px', border:'1.5px solid', fontSize:'12px', fontWeight:'600', cursor:'pointer',
                        borderColor: filtroEstado===f ? colorEstado(f) : '#E2E8F0',
                        background: filtroEstado===f ? bgEstado(f) : 'white',
                        color: filtroEstado===f ? colorEstado(f) : '#64748B'}">
                    {{ f === 'todos' ? 'Todos' : f === 'en_proceso' ? 'En proceso' : f.charAt(0).toUpperCase() + f.slice(1) }}
                    ({{ contarEstado(f) }})
                </button>
            </div>
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Expediente</th>
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Tipo</th>
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Asunto</th>
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Partes</th>
                        <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Estado</th>
                        <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Docs</th>
                        <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Pago</th>
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Entrega</th>
                        <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="listaFiltrada.length === 0">
                        <td colspan="9" style="padding:2rem; text-align:center; color:#94A3B8;">Sin expedientes</td>
                    </tr>
                    <tr v-for="a in listaFiltrada" :key="a.id" style="border-top:1px solid #F1F5F9; cursor:pointer;"
                        @mouseover="e => e.currentTarget.style.background='#F8FAFC'"
                        @mouseleave="e => e.currentTarget.style.background='white'">
                        <td style="padding:11px 16px; font-family:monospace; font-size:12px; font-weight:700; color:#4F46E5;">{{ a.numero_expediente }}</td>
                        <td style="padding:11px 16px; font-size:12px;">{{ labelTipo(a.tipo_acto) }}</td>
                        <td style="padding:11px 16px; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ a.asunto }}</td>
                        <td style="padding:11px 16px; font-size:12px; color:#64748B; max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ a.partes_intervinientes || '—' }}</td>
                        <td style="padding:11px 16px; text-align:center;">
                            <span :style="estiloEstado(a.estado)">{{ labelEstado(a.estado) }}</span>
                        </td>
                        <td style="padding:11px 16px; text-align:center;">
                            <span v-if="a.requisitos_total > 0"
                                :style="{fontSize:'11px', padding:'3px 8px', borderRadius:'20px', fontWeight:'600',
                                    background: a.requisitos_pendientes > 0 ? '#FEF2F2' : '#F0FDF4',
                                    color: a.requisitos_pendientes > 0 ? '#991B1B' : '#166534'}">
                                {{ a.requisitos_total - a.requisitos_pendientes }}/{{ a.requisitos_total }}
                            </span>
                            <span v-else style="color:#94A3B8; font-size:12px;">—</span>
                        </td>
                        <td style="padding:11px 16px; text-align:center;">
                            <span :style="estiloPago(a.estado_pago)">{{ labelPago(a.estado_pago) }}</span>
                        </td>
                        <td style="padding:11px 16px; font-size:12px; color:#64748B;">{{ a.fecha_entrega ? formatFecha(a.fecha_entrega) : '—' }}</td>
                        <td style="padding:11px 16px; text-align:center;">
                            <button @click="verExpediente(a.id)" style="padding:4px 12px; background:#EEF2FF; color:#4F46E5; border:1px solid #C7D2FE; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">Ver →</button>
                        </td>
                    </tr>
                </tbody>
            </table>
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

const vista        = ref('kanban')
const busqueda     = ref('')
const filtroEstado = ref('todos')

function filtrar(lista) {
    if (!busqueda.value) return lista
    const q = busqueda.value.toLowerCase()
    return lista.filter(a =>
        a.numero_expediente.toLowerCase().includes(q) ||
        a.asunto.toLowerCase().includes(q) ||
        (a.partes_intervinientes || '').toLowerCase().includes(q)
    )
}

const pendientesFiltrados  = computed(() => filtrar(props.pendientes))
const enProcesoFiltrados   = computed(() => filtrar(props.en_proceso))
const finalizadosFiltrados = computed(() => filtrar(props.finalizados))

const listaFiltrada = computed(() => {
    const base = filtroEstado.value === 'todos' ? props.todos
        : filtroEstado.value === 'pendiente'  ? props.pendientes
        : filtroEstado.value === 'en_proceso' ? props.en_proceso
        : props.finalizados
    return filtrar(base)
})

function contarEstado(e) {
    return e === 'todos' ? props.todos.length
        : e === 'pendiente'  ? props.pendientes.length
        : e === 'en_proceso' ? props.en_proceso.length
        : props.finalizados.length
}

function verExpediente(id) { router.visit('/notaria/actos/' + id) }

function formatFecha(f) {
    if (!f) return '—'
    const fecha = f.includes('T') || f.includes(' ') ? new Date(f) : new Date(f + 'T12:00:00')
    if (isNaN(fecha)) return '—'
    return fecha.toLocaleDateString('es-PE', { day:'2-digit', month:'2-digit', year:'2-digit' })
}

const tiposActo = {
    escritura_publica:'📜 Escritura', poder:'✍️ Poder', testamento:'📋 Testamento',
    legalizacion:'🔏 Legalización', carta_notarial:'✉️ Carta notarial',
    protesto:'⚖️ Protesto', acta_notarial:'📝 Acta', otro:'📁 Otro'
}
function labelTipo(t) { return tiposActo[t] ?? t }

function labelEstado(e) { return {pendiente:'🕐 Pendiente', en_proceso:'🔄 En proceso', finalizado:'✅ Finalizado', cancelado:'❌ Cancelado'}[e] ?? e }
function estiloEstado(e) {
    const m = {pendiente:{background:'#FEF3C7',color:'#92400E'}, en_proceso:{background:'#EFF6FF',color:'#1D4ED8'}, finalizado:{background:'#F0FDF4',color:'#166534'}, cancelado:{background:'#FEF2F2',color:'#991B1B'}}
    return {...(m[e]||{}), fontSize:'11px', padding:'3px 9px', borderRadius:'20px', fontWeight:'600', whiteSpace:'nowrap'}
}
function labelPago(p) { return {pendiente:'⏳ Pendiente', parcial:'◑ Parcial', pagado:'✅ Pagado'}[p] ?? p }
function estiloPago(p) {
    const m = {pendiente:{background:'#FEF2F2',color:'#991B1B'}, parcial:{background:'#FEF3C7',color:'#92400E'}, pagado:{background:'#F0FDF4',color:'#166534'}}
    return {...(m[p]||{}), fontSize:'10px', padding:'2px 7px', borderRadius:'20px', fontWeight:'600', whiteSpace:'nowrap'}
}
function colorEstado(e) { return {todos:'#6366F1', pendiente:'#F59E0B', en_proceso:'#3B82F6', finalizado:'#10B981'}[e] ?? '#94A3B8' }
function bgEstado(e) { return {todos:'#EEF2FF', pendiente:'#FEF3C7', en_proceso:'#EFF6FF', finalizado:'#F0FDF4'}[e] ?? '#F1F5F9' }
</script>
