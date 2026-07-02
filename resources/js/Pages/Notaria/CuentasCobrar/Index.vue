<template>
    <AppLayout title="Cuentas por cobrar" subtitle="Facturas a crédito pendientes">
        <!-- Filtros -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1rem; margin-bottom:1rem; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <input v-model="buscar" type="text" placeholder="🔍 Buscar por cliente, RUC o factura..."
                @input="filtrar"
                style="flex:1; min-width:200px; padding:10px 14px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
            <select v-model="estadoFiltro" @change="filtrar"
                style="padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; font-weight:600;">
                <option value="">Todos</option>
                <option value="pendiente">Pendientes</option>
                <option value="pagado">Pagados</option>
            </select>
        </div>

        <!-- Resumen -->
        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px; margin-bottom:1rem;">
            <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1rem; text-align:center;">
                <p style="font-size:11px; color:#64748B; font-weight:600; margin:0; text-transform:uppercase;">Total facturas</p>
                <p style="font-size:24px; font-weight:800; color:#1E293B; margin:4px 0 0;">{{ items.length }}</p>
            </div>
            <div style="background:white; border-radius:12px; border:1px solid #FECACA; padding:1rem; text-align:center;">
                <p style="font-size:11px; color:#991B1B; font-weight:600; margin:0; text-transform:uppercase;">Pendiente total</p>
                <p style="font-size:24px; font-weight:800; color:#DC2626; margin:4px 0 0;">S/ {{ totalPendiente.toFixed(2) }}</p>
            </div>
            <div style="background:white; border-radius:12px; border:1px solid #BBF7D0; padding:1rem; text-align:center;">
                <p style="font-size:11px; color:#166534; font-weight:600; margin:0; text-transform:uppercase;">Cobrado total</p>
                <p style="font-size:24px; font-weight:800; color:#16A34A; margin:4px 0 0;">S/ {{ totalCobrado.toFixed(2) }}</p>
            </div>
        </div>

        <!-- Lista de facturas -->
        <div v-if="!items.length" style="background:white; border-radius:12px; padding:3rem; text-align:center; color:#94A3B8;">
            No hay facturas a crédito registradas
        </div>

        <div v-for="f in items" :key="f.id" style="background:white; border-radius:12px; border:1px solid #E2E8F0; margin-bottom:12px; overflow:hidden;">
            <!-- Cabecera factura -->
            <div @click="toggle(f.id)" style="padding:14px 16px; cursor:pointer; display:flex; justify-content:space-between; align-items:center; gap:12px;">
                <div style="flex:1;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                        <span style="font-size:15px; font-weight:800; color:#1E293B;">📄 {{ f.documento }}</span>
                        <span :style="{
                            fontSize:'11px', padding:'3px 10px', borderRadius:'20px', fontWeight:'600',
                            background: f.total_pendiente > 0 ? '#FEF2F2' : '#F0FDF4',
                            color: f.total_pendiente > 0 ? '#DC2626' : '#16A34A',
                        }">
                            {{ f.total_pendiente > 0 ? 'Pendiente' : 'Pagado' }}
                        </span>
                    </div>
                    <p style="font-size:12px; color:#64748B; margin:0;">
                        {{ f.cliente_nombre }} · {{ f.cliente_numero_documento }} · {{ f.fecha_emision }}
                    </p>
                </div>
                <div style="text-align:right;">
                    <p style="font-size:11px; color:#64748B; margin:0;">Total</p>
                    <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0;">S/ {{ Number(f.total).toFixed(2) }}</p>
                </div>
                <span style="font-size:16px; color:#94A3B8;">{{ expandido.includes(f.id) ? '▲' : '▼' }}</span>
            </div>

            <!-- Detalle cuotas -->
            <div v-if="expandido.includes(f.id)" style="border-top:1px solid #F1F5F9; padding:12px 16px;">
                <div v-for="cuota in f.cuotas" :key="cuota.id"
                    :style="{
                        display:'flex', justifyContent:'space-between', alignItems:'center', padding:'10px 12px',
                        background: cuota.estado === 'pagada' ? '#F0FDF4' : '#FFFBEB',
                        borderRadius:'8px', marginBottom:'8px', gap:'10px',
                    }">
                    <div style="flex:1;">
                        <p style="font-size:13px; font-weight:700; margin:0;"
                           :style="{ color: cuota.estado === 'pagada' ? '#166534' : '#92400E' }">
                            Cuota {{ cuota.numero_cuota }}
                        </p>
                        <p style="font-size:11px; color:#64748B; margin:2px 0 0;">
                            Vence: {{ cuota.fecha_vencimiento }}
                            <span v-if="cuota.estado === 'pagada'"> · Pagada: {{ cuota.fecha_pago }} ({{ cuota.metodo_pago }})</span>
                        </p>
                    </div>
                    <div style="text-align:right; min-width:100px;">
                        <p style="font-size:15px; font-weight:800; margin:0;" :style="{ color: cuota.estado === 'pagada' ? '#16A34A' : '#1E293B' }">
                            S/ {{ Number(cuota.monto).toFixed(2) }}
                        </p>
                    </div>
                    <div v-if="cuota.estado === 'pendiente'" style="min-width:120px; text-align:right;">
                        <button @click="abrirPago(cuota, f)"
                            style="padding:8px 14px; background:#10B981; color:white; border:none; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer;">
                            💰 Registrar pago
                        </button>
                    </div>
                    <div v-else style="min-width:120px; text-align:right;">
                        <span style="font-size:12px; color:#16A34A; font-weight:600;">✅ Pagada</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Pago -->
        <div v-if="modalPago" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; display:flex; align-items:center; justify-content:center;" @click.self="modalPago=false">
            <div style="background:white; border-radius:16px; padding:1.5rem; width:380px; max-width:90vw;">
                <h3 style="font-size:16px; font-weight:800; margin:0 0 4px;">💰 Registrar pago</h3>
                <p style="font-size:12px; color:#64748B; margin:0 0 1rem;">
                    {{ pagoFactura }} · Cuota {{ pagoCuota?.numero_cuota }}
                </p>

                <label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">MONTO A PAGAR (S/)</label>
                <input v-model.number="formPago.monto" type="number" step="0.01"
                    style="width:100%; padding:12px; border:1px solid #E2E8F0; border-radius:8px; font-size:18px; font-weight:800; text-align:center; outline:none; box-sizing:border-box; margin-bottom:12px;" />

                <label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">MÉTODO DE PAGO</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-bottom:12px;">
                    <button v-for="m in ['efectivo','yape','plin','transferencia']" :key="m"
                        @click="formPago.metodo_pago=m"
                        :style="{ padding:'8px', border:'1px solid', borderRadius:'8px', cursor:'pointer', fontWeight:'600', fontSize:'12px', textTransform:'capitalize', background: formPago.metodo_pago===m ? '#10B981' : 'white', color: formPago.metodo_pago===m ? 'white' : '#374151', borderColor: formPago.metodo_pago===m ? '#10B981' : '#E2E8F0' }">
                        {{ m }}
                    </button>
                </div>

                <label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">REFERENCIA (opcional)</label>
                <input v-model="formPago.referencia" type="text" placeholder="N° operación"
                    style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; margin-bottom:16px;" />

                <button @click="confirmarPago" :disabled="procesando"
                    style="width:100%; padding:14px; background:linear-gradient(135deg,#10B981,#059669); color:white; border:none; border-radius:10px; font-size:15px; font-weight:800; cursor:pointer;">
                    {{ procesando ? '⏳ Procesando...' : '✅ Confirmar pago S/ ' + (formPago.monto||0).toFixed(2) }}
                </button>
                <button @click="modalPago=false" style="width:100%; margin-top:8px; padding:10px; background:transparent; border:none; color:#94A3B8; font-size:12px; cursor:pointer;">
                    Cancelar
                </button>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ facturas: Object, items: Array, filtros: Object })

const buscar = ref(props.filtros?.buscar || '')
const estadoFiltro = ref(props.filtros?.estado_filtro || '')
const expandido = ref([])
const modalPago = ref(false)
const pagoCuota = ref(null)
const pagoFactura = ref('')
const procesando = ref(false)
const formPago = ref({ monto: 0, metodo_pago: 'efectivo', referencia: '' })

const totalPendiente = computed(() => props.items.reduce((s, f) => s + Number(f.total_pendiente || 0), 0))
const totalCobrado = computed(() => props.items.reduce((s, f) => s + Number(f.total_pagado || 0), 0))

function toggle(id) {
    const idx = expandido.value.indexOf(id)
    if (idx >= 0) expandido.value.splice(idx, 1)
    else expandido.value.push(id)
}

let debounceTimer = null
function filtrar() {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
        router.visit('/notaria/cuentas-cobrar', {
            data: { buscar: buscar.value, estado_filtro: estadoFiltro.value },
            preserveScroll: true,
        })
    }, 400)
}

function abrirPago(cuota, factura) {
    pagoCuota.value = cuota
    pagoFactura.value = factura.documento
    formPago.value = { monto: Number(cuota.monto), metodo_pago: 'efectivo', referencia: '' }
    modalPago.value = true
}

function confirmarPago() {
    if (!pagoCuota.value || procesando.value) return
    procesando.value = true
    router.post('/notaria/cuentas-cobrar/' + pagoCuota.value.id + '/pagar', formPago.value, {
        preserveScroll: true,
        onSuccess: () => { modalPago.value = false; procesando.value = false },
        onError: () => { procesando.value = false },
    })
}
</script>
