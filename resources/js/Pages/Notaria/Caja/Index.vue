<template>
    <AppLayout title="Caja notarial" subtitle="Cobro de expedientes y adelantos">

        <!-- ALERTA CAJA CERRADA -->
        <div v-if="!sesionAbierta" style="background:#FEF3C7; border:1px solid #FDE68A; border-radius:10px; padding:12px 16px; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px;">
            <span style="font-size:20px;">⚠️</span>
            <p style="font-size:13px; color:#92400E; font-weight:600; margin:0;">La caja está cerrada. Abre la caja antes de registrar cobros.</p>
            <a href="/caja" style="margin-left:auto; padding:6px 14px; background:#F59E0B; color:white; border-radius:7px; font-size:12px; font-weight:600; text-decoration:none;">Ir a caja →</a>
        </div>

        <!-- BUSCADOR -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.2rem 1.5rem; margin-bottom:1.5rem;">
            <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0 0 10px;">🔍 Buscar expediente para cobrar</p>
            <input v-model="busqueda" @input="filtrar" type="text" placeholder="Buscar por N° expediente, nombre, DNI, asunto..."
                style="width:100%; padding:12px 16px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; transition:border .2s;"
                @focus="e => e.target.style.borderColor='#6366F1'"
                @blur="e => e.target.style.borderColor='#E2E8F0'" />
        </div>

        <!-- RESULTADOS / LISTA PENDIENTES -->
        <div style="display:grid; grid-template-columns:1fr 380px; gap:16px;">

            <!-- LISTA -->
            <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
                <div style="padding:1rem 1.25rem; border-bottom:1px solid #F1F5F9; display:flex; align-items:center; justify-content:space-between;">
                    <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0;">Expedientes con saldo pendiente ({{ expedientesFiltrados.length }})</p>
                    <span style="font-size:12px; color:#94A3B8;">Total por cobrar: <strong style="color:#EF4444;">S/ {{ totalPorCobrar.toFixed(2) }}</strong></span>
                </div>
                <div v-if="expedientesFiltrados.length === 0" style="padding:3rem; text-align:center; color:#94A3B8;">
                    <p style="font-size:32px; margin:0 0 8px;">🔍</p>
                    <p style="font-size:13px; margin:0;">No hay expedientes con saldo pendiente</p>
                </div>
                <div v-for="e in expedientesFiltrados" :key="e.id"
                    @click="seleccionar(e)"
                    :style="{
                        padding:'14px 16px',
                        borderTop:'1px solid #F1F5F9',
                        cursor:'pointer',
                        background: expedienteSeleccionado?.id === e.id ? '#EEF2FF' : 'white',
                        borderLeft: expedienteSeleccionado?.id === e.id ? '4px solid #6366F1' : '4px solid transparent',
                        transition:'all .15s'
                    }">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                        <span style="font-family:monospace; font-size:13px; font-weight:700; color:#4F46E5;">{{ e.numero_expediente }}</span>
                        <span :style="estiloPago(e.estado_pago)">{{ labelPago(e.estado_pago) }}</span>
                    </div>
                    <p style="font-size:13px; color:#1E293B; font-weight:500; margin:0 0 2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ e.asunto }}</p>
                    <p style="font-size:12px; color:#94A3B8; margin:0 0 6px;">{{ e.partes_intervinientes || '—' }}</p>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:11px; color:#94A3B8;">{{ labelTipo(e.tipo_acto) }}</span>
                        <div style="text-align:right;">
                            <span style="font-size:11px; color:#94A3B8;">Pagado S/ {{ Number(e.monto_pagado).toFixed(2) }} / S/ {{ Number(e.monto_cobrar).toFixed(2) }} — </span>
                            <span style="font-size:13px; font-weight:800; color:#EF4444;">Saldo S/ {{ Number(e.saldo).toFixed(2) }}</span>
                        </div>
                    </div>
                    <!-- Barra progreso -->
                    <div style="background:#E2E8F0; border-radius:20px; height:4px; margin-top:8px;">
                        <div :style="{width: Math.min(100,(e.monto_pagado/e.monto_cobrar)*100)+'%', background:'#6366F1', borderRadius:'20px', height:'100%'}"></div>
                    </div>
                </div>
            </div>

            <!-- PANEL COBRO -->
            <div>
                <div v-if="!expedienteSeleccionado" style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:3rem; text-align:center; color:#94A3B8;">
                    <p style="font-size:32px; margin:0 0 8px;">👈</p>
                    <p style="font-size:13px; margin:0;">Selecciona un expediente para registrar el cobro</p>
                </div>

                <div v-else style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
                    <div style="padding:1rem 1.25rem; background:#EEF2FF; border-bottom:1px solid #C7D2FE;">
                        <p style="font-size:12px; color:#4F46E5; font-weight:600; margin:0 0 2px;">{{ expedienteSeleccionado.numero_expediente }}</p>
                        <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ expedienteSeleccionado.asunto }}</p>
                        <p style="font-size:12px; color:#6366F1; margin:2px 0 0;">{{ expedienteSeleccionado.partes_intervinientes || '—' }}</p>
                    </div>

                    <div style="padding:1rem 1.25rem;">
                        <!-- Resumen financiero -->
                        <div style="background:#F8FAFC; border-radius:10px; padding:12px 14px; margin-bottom:1rem;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                <span style="font-size:13px; color:#64748B;">Total servicio</span>
                                <span style="font-size:13px; font-weight:700;">S/ {{ Number(expedienteSeleccionado.monto_cobrar).toFixed(2) }}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                <span style="font-size:13px; color:#64748B;">Adelantos pagados</span>
                                <span style="font-size:13px; font-weight:700; color:#10B981;">S/ {{ Number(expedienteSeleccionado.monto_pagado).toFixed(2) }}</span>
                            </div>
                            <div style="border-top:1px solid #E2E8F0; padding-top:6px; display:flex; justify-content:space-between;">
                                <span style="font-size:14px; font-weight:700; color:#1E293B;">Saldo a cobrar</span>
                                <span style="font-size:18px; font-weight:800; color:#EF4444;">S/ {{ Number(expedienteSeleccionado.saldo).toFixed(2) }}</span>
                            </div>
                        </div>

                        <!-- Tipo de cobro -->
                        <div style="margin-bottom:12px;">
                            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:6px; font-weight:600; text-transform:uppercase;">Tipo de cobro</label>
                            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px;">
                                <button v-for="t in tiposCobro" :key="t.value" @click="formCobro.tipo = t.value"
                                    :style="{padding:'7px 4px', borderRadius:'8px', border:'1.5px solid', fontSize:'11px', fontWeight:'600', cursor:'pointer', textAlign:'center',
                                        borderColor: formCobro.tipo===t.value ? '#6366F1' : '#E2E8F0',
                                        background: formCobro.tipo===t.value ? '#EEF2FF' : 'white',
                                        color: formCobro.tipo===t.value ? '#4F46E5' : '#64748B'}">
                                    {{ t.icon }}<br>{{ t.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Método de pago -->
                        <div style="margin-bottom:12px;">
                            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:6px; font-weight:600; text-transform:uppercase;">Método de pago</label>
                            <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:5px;">
                                <button v-for="m in metodos" :key="m.value" @click="formCobro.metodo_pago = m.value"
                                    :style="{padding:'6px 2px', borderRadius:'7px', border:'1.5px solid', fontSize:'10px', fontWeight:'600', cursor:'pointer', textAlign:'center',
                                        borderColor: formCobro.metodo_pago===m.value ? '#10B981' : '#E2E8F0',
                                        background: formCobro.metodo_pago===m.value ? '#ECFDF5' : 'white',
                                        color: formCobro.metodo_pago===m.value ? '#065F46' : '#64748B'}">
                                    {{ m.icon }}<br>{{ m.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Monto -->
                        <div style="margin-bottom:12px;">
                            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Monto a cobrar (S/)</label>
                            <input v-model="formCobro.monto" type="number" step="0.01" min="0.01"
                                style="width:100%; padding:12px; border:2px solid #E2E8F0; border-radius:10px; font-size:22px; font-weight:800; outline:none; box-sizing:border-box; text-align:right; color:#1E293B;" />
                            <div style="display:flex; gap:6px; margin-top:6px;">
                                <button @click="formCobro.monto = expedienteSeleccionado.saldo" style="flex:1; padding:5px; background:#F0FDF4; color:#166534; border:1px solid #BBF7D0; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">
                                    Cobrar todo S/ {{ Number(expedienteSeleccionado.saldo).toFixed(2) }}
                                </button>
                            </div>
                        </div>

                        <!-- Referencia opcional -->
                        <div style="margin-bottom:16px;">
                            <label style="font-size:11px; color:#64748B; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">Referencia (opcional)</label>
                            <input v-model="formCobro.referencia" type="text" placeholder="N° operación, voucher..."
                                style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                        </div>

                        <button @click="confirmarCobro" :disabled="!sesionAbierta || !formCobro.monto"
                            :style="{width:'100%', padding:'14px', background: sesionAbierta && formCobro.monto ? 'linear-gradient(135deg,#6366F1,#4F46E5)' : '#E2E8F0',
                                color: sesionAbierta && formCobro.monto ? 'white' : '#94A3B8',
                                border:'none', borderRadius:'10px', fontSize:'15px', fontWeight:'700', cursor: sesionAbierta && formCobro.monto ? 'pointer' : 'not-allowed'}">
                            ✅ Registrar cobro S/ {{ Number(formCobro.monto || 0).toFixed(2) }}
                        </button>

                        <!-- Historial pagos -->
                        <div v-if="expedienteSeleccionado.pagos?.length > 0" style="margin-top:1rem; border-top:1px solid #F1F5F9; padding-top:1rem;">
                            <p style="font-size:12px; font-weight:600; color:#64748B; text-transform:uppercase; margin:0 0 8px;">Historial de cobros</p>
                            <div v-for="p in expedienteSeleccionado.pagos" :key="p.id"
                                style="display:flex; justify-content:space-between; align-items:center; padding:6px 0; border-bottom:1px solid #F8FAFC; font-size:12px;">
                                <div>
                                    <span style="color:#64748B;">{{ formatFecha(p.created_at) }}</span>
                                    <span style="margin-left:6px; background:#F1F5F9; color:#475569; padding:1px 6px; border-radius:10px; font-size:10px; text-transform:capitalize;">{{ p.tipo?.replace('_',' ') }}</span>
                                    <span style="margin-left:4px; background:#F0FDF4; color:#166534; padding:1px 6px; border-radius:10px; font-size:10px;">{{ p.metodo_pago }}</span>
                                </div>
                                <span style="font-weight:700; color:#0F766E;">S/ {{ Number(p.monto).toFixed(2) }}</span>
                            </div>
                        </div>
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
    pendientes:    { type: Array,   default: () => [] },
    sesionAbierta: { type: Boolean, default: false },
})

const busqueda              = ref('')
const expedienteSeleccionado = ref(null)
const formCobro = ref({ monto: '', metodo_pago: 'efectivo', tipo: 'pago_parcial', referencia: '' })

const tiposCobro = [
    { value: 'adelanto',    label: 'Adelanto',    icon: '💰' },
    { value: 'pago_parcial',label: 'Parcial',     icon: '◑' },
    { value: 'pago_final',  label: 'Pago final',  icon: '✅' },
]

const metodos = [
    { value: 'efectivo',     label: 'Efectivo',     icon: '💵' },
    { value: 'yape',         label: 'Yape',         icon: '📱' },
    { value: 'plin',         label: 'Plin',         icon: '📲' },
    { value: 'tarjeta',      label: 'Tarjeta',      icon: '💳' },
    { value: 'transferencia',label: 'Transfer',     icon: '🏦' },
]

const expedientesFiltrados = computed(() => {
    if (!busqueda.value) return props.pendientes
    const q = busqueda.value.toLowerCase()
    return props.pendientes.filter(e =>
        e.numero_expediente.toLowerCase().includes(q) ||
        e.asunto.toLowerCase().includes(q) ||
        (e.partes_intervinientes || '').toLowerCase().includes(q)
    )
})

const totalPorCobrar = computed(() => expedientesFiltrados.value.reduce((s, e) => s + Number(e.saldo), 0))

function seleccionar(e) {
    expedienteSeleccionado.value = e
    formCobro.value = {
        monto:       e.saldo,
        metodo_pago: 'efectivo',
        tipo:        e.monto_pagado > 0 ? 'pago_final' : 'pago_parcial',
        referencia:  ''
    }
}

function filtrar() {
    expedienteSeleccionado.value = null
}

function confirmarCobro() {
    if (!formCobro.value.monto || !expedienteSeleccionado.value) return
    router.post('/notaria/caja/' + expedienteSeleccionado.value.id + '/cobrar', formCobro.value, {
        preserveScroll: true,
        onSuccess: () => {
            expedienteSeleccionado.value = null
            formCobro.value = { monto: '', metodo_pago: 'efectivo', tipo: 'pago_parcial', referencia: '' }
        }
    })
}

function labelTipo(t) {
    const map = { escritura_publica:'📜 Escritura', poder:'✍️ Poder', testamento:'📋 Testamento', legalizacion:'🔏 Legalización', carta_notarial:'✉️ Carta notarial', protesto:'⚖️ Protesto', acta_notarial:'📝 Acta', otro:'📁 Otro' }
    return map[t] ?? t
}

function labelPago(p) {
    return { pendiente:'⏳ Pendiente', parcial:'◑ Parcial', pagado:'✅ Pagado' }[p] ?? p
}

function estiloPago(p) {
    const map = { pendiente:{background:'#FEF2F2',color:'#991B1B'}, parcial:{background:'#FEF3C7',color:'#92400E'}, pagado:{background:'#F0FDF4',color:'#166534'} }
    return { ...(map[p]||{}), fontSize:'11px', padding:'3px 9px', borderRadius:'20px', fontWeight:'600', whiteSpace:'nowrap' }
}

function formatFecha(f) {
    if (!f) return '—'
    const d = new Date(f)
    return d.toLocaleDateString('es-PE', { day:'2-digit', month:'2-digit', year:'2-digit' }) + ' ' + d.toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' })
}
</script>
