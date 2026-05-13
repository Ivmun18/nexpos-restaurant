<template>
    <AppLayout title="Caja" subtitle="Control de apertura y cierre de caja">

        <div style="display:grid; grid-template-columns:1fr 360px; gap:1.5rem; align-items:start;">

            <!-- Panel izquierdo -->
            <div>

                <!-- Sin sesión activa -->
                <div v-if="!sesionActiva" style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:2rem; text-align:center; margin-bottom:1rem;">
                    <div style="width:64px; height:64px; background:#EFF6FF; border-radius:16px; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                        <svg width="32" height="32" fill="none" stroke="#2563EB" stroke-width="1.5" viewBox="0 0 24 24">
                            <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
                        </svg>
                    </div>
                    <p style="font-size:16px; font-weight:600; color:#1E293B; margin:0 0 8px;">Caja cerrada</p>
                    <p style="font-size:13px; color:#94A3B8; margin:0 0 1.5rem;">No hay ninguna sesión activa. Abre la caja para comenzar a operar.</p>

                    <div style="max-width:300px; margin:0 auto;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; text-align:left;">Monto de apertura (S/)</label>
                        <input v-model="montoApertura" type="number" step="0.01" min="0" placeholder="0.00"
                            style="width:100%; padding:12px; border:2px solid #2563EB; border-radius:10px; font-size:18px; font-weight:700; color:#1E293B; outline:none; box-sizing:border-box; text-align:right; margin-bottom:10px;"/>
                        <button @click="abrirCaja"
                            style="width:100%; padding:13px; background:#2563EB; color:white; border:none; border-radius:10px; font-size:15px; font-weight:700; cursor:pointer;">
                            Abrir caja
                        </button>
                    </div>
                </div>

                <!-- Sesión activa -->
                <div v-if="sesionActiva">

                    <!-- Estado de la sesión -->
                    <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem; margin-bottom:1rem;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                            <div>
                                <p style="font-size:15px; font-weight:600; color:#1E293B; margin:0;">Caja abierta</p>
                                <p style="font-size:12px; color:#94A3B8; margin:4px 0 0;">
                                    Apertura: {{ formatFecha(sesionActiva.fecha_apertura) }} · {{ sesionActiva.usuario?.name }}
                                </p>
                            </div>
                            <span style="background:#F0FDF4; color:#166534; font-size:12px; font-weight:600; padding:4px 12px; border-radius:20px; border:1px solid #DCFCE7;">
                                ABIERTA
                            </span>
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
                            <div style="background:#F8FAFC; border-radius:8px; padding:12px; text-align:center;">
                                <p style="font-size:11px; color:#94A3B8; margin:0 0 4px; text-transform:uppercase;">Fondo inicial</p>
                                <p style="font-size:18px; font-weight:700; color:#1E293B; margin:0;">S/ {{ Number(sesionActiva.monto_apertura).toFixed(2) }}</p>
                            </div>
                            <div style="background:#F0FDF4; border-radius:8px; padding:12px; text-align:center;">
                                <p style="font-size:11px; color:#94A3B8; margin:0 0 4px; text-transform:uppercase;">Ingresos</p>
                                <p style="font-size:18px; font-weight:700; color:#166534; margin:0;">S/ {{ totalIngresos.toFixed(2) }}</p>
                            </div>
                            <div style="background:#FEF2F2; border-radius:8px; padding:12px; text-align:center;">
                                <p style="font-size:11px; color:#94A3B8; margin:0 0 4px; text-transform:uppercase;">Egresos</p>
                                <p style="font-size:18px; font-weight:700; color:#991B1B; margin:0;">S/ {{ totalEgresos.toFixed(2) }}</p>
                            </div>
                        </div>

                        <div style="margin-top:12px; padding-top:12px; border-top:2px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:14px; font-weight:600; color:#1E293B;">Saldo actual en caja</span>
                            <span style="font-size:24px; font-weight:700; color:#2563EB;">S/ {{ saldoActual.toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Movimientos -->
                    <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden; margin-bottom:1rem;">
                        <div style="padding:14px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between;">
                            <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0;">Movimientos de la sesión</p>
                            <button @click="modalMovimiento=true"
                                style="padding:7px 14px; background:#EFF6FF; color:#2563EB; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                                + Agregar movimiento
                            </button>
                        </div>
                        <table style="width:100%; border-collapse:collapse;">
                            <thead>
                                <tr style="background:#F8FAFC;">
                                    <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Hora</th>
                                    <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Concepto</th>
                                    <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Tipo</th>
                                    <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="!sesionActiva.movimientos || sesionActiva.movimientos.length === 0">
                                    <td colspan="4" style="padding:2rem; text-align:center; color:#94A3B8; font-size:13px;">Sin movimientos</td>
                                </tr>
                                <tr v-for="m in sesionActiva.movimientos" :key="m.id" style="border-top:1px solid #F1F5F9;">
                                    <td style="padding:10px 16px; font-size:12px; color:#64748B;">{{ formatHora(m.created_at) }}</td>
                                    <td style="padding:10px 16px; font-size:13px; color:#1E293B;">{{ m.concepto }}</td>
                                    <td style="padding:10px 16px;">
                                        <span :style="m.tipo==='ingreso' ? tipoIngreso : tipoEgreso">{{ m.tipo }}</span>
                                    </td>
                                    <td style="padding:10px 16px; font-size:13px; font-weight:700; text-align:right;"
                                        :style="m.tipo==='ingreso' ? {color:'#166534'} : {color:'#991B1B'}">
                                        {{ m.tipo==='ingreso' ? '+' : '-' }} S/ {{ Number(m.monto).toFixed(2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Historial -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden;">
                    <div style="padding:14px 20px; border-bottom:1px solid #E2E8F0;">
                        <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0;">Historial de sesiones</p>
                    </div>
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#F8FAFC;">
                                <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Apertura</th>
                                <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Cierre</th>
                                <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Usuario</th>
                                <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Sistema</th>
                                <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Real</th>
                                <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="historial.length === 0">
                                <td colspan="6" style="padding:2rem; text-align:center; color:#94A3B8; font-size:13px;">Sin historial</td>
                            </tr>
                            <tr v-for="s in historial" :key="s.id" style="border-top:1px solid #F1F5F9;">
                                <td style="padding:10px 16px; font-size:12px; color:#64748B;">{{ formatFecha(s.fecha_apertura) }}</td>
                                <td style="padding:10px 16px; font-size:12px; color:#64748B;">{{ formatFecha(s.fecha_cierre) }}</td>
                                <td style="padding:10px 16px; font-size:13px; color:#1E293B;">{{ s.usuario?.name }}</td>
                                <td style="padding:10px 16px; font-size:13px; font-weight:600; color:#1E293B; text-align:right;">S/ {{ Number(s.monto_cierre_sistema).toFixed(2) }}</td>
                                <td style="padding:10px 16px; font-size:13px; font-weight:600; color:#1E293B; text-align:right;">S/ {{ Number(s.monto_cierre_real).toFixed(2) }}</td>
                                <td style="padding:10px 16px; font-size:13px; font-weight:700; text-align:right;"
                                    :style="Number(s.diferencia) >= 0 ? {color:'#166534'} : {color:'#991B1B'}">
                                    {{ Number(s.diferencia) >= 0 ? '+' : '' }}S/ {{ Number(s.diferencia).toFixed(2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Panel derecho: cierre de caja -->
            <div style="position:sticky; top:80px;">
                <div v-if="sesionActiva" style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Cerrar caja</p>

                    <div style="background:#F8FAFC; border-radius:8px; padding:12px; margin-bottom:1rem;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-size:12px; color:#64748B;">Fondo inicial</span>
                            <span style="font-size:12px; color:#1E293B;">S/ {{ Number(sesionActiva.monto_apertura).toFixed(2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-size:12px; color:#166534;">+ Ingresos</span>
                            <span style="font-size:12px; color:#166534;">S/ {{ totalIngresos.toFixed(2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-size:12px; color:#991B1B;">- Egresos</span>
                            <span style="font-size:12px; color:#991B1B;">S/ {{ totalEgresos.toFixed(2) }}</span>
                        </div>
                        <div style="border-top:1px solid #E2E8F0; padding-top:8px; display:flex; justify-content:space-between;">
                            <span style="font-size:13px; font-weight:600; color:#1E293B;">Saldo sistema</span>
                            <span style="font-size:15px; font-weight:700; color:#2563EB;">S/ {{ saldoActual.toFixed(2) }}</span>
                        </div>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Monto contado físicamente (S/)</label>
                        <input v-model="montoCierreReal" type="number" step="0.01" min="0"
                            style="width:100%; padding:12px; border:2px solid #E2E8F0; border-radius:10px; font-size:18px; font-weight:700; color:#1E293B; outline:none; box-sizing:border-box; text-align:right;"/>
                    </div>

                    <div v-if="montoCierreReal !== ''" style="border-radius:8px; padding:10px 14px; margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center;"
                        :style="diferenciaCierre >= 0 ? {background:'#F0FDF4', border:'1px solid #DCFCE7'} : {background:'#FEF2F2', border:'1px solid #FECACA'}">
                        <span style="font-size:13px; font-weight:500;" :style="diferenciaCierre >= 0 ? {color:'#166534'} : {color:'#991B1B'}">
                            {{ diferenciaCierre >= 0 ? 'Sobrante' : 'Faltante' }}
                        </span>
                        <span style="font-size:18px; font-weight:700;" :style="diferenciaCierre >= 0 ? {color:'#166534'} : {color:'#991B1B'}">
                            S/ {{ Math.abs(diferenciaCierre).toFixed(2) }}
                        </span>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Observaciones</label>
                        <textarea v-model="observacionesCierre" rows="2"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box; resize:none;"></textarea>
                    </div>

                    <button @click="cerrarCaja"
                        style="width:100%; padding:13px; background:#991B1B; color:white; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer;">
                        Cerrar caja
                    </button>
                </div>

                <div v-else style="background:#F8FAFC; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem; text-align:center;">
                    <p style="font-size:13px; color:#94A3B8; margin:0;">Abre la caja para ver las opciones de cierre</p>
                </div>
            </div>
        </div>

        <!-- Modal movimiento -->
        <div v-if="modalMovimiento" style="position:fixed; inset:0; background:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; z-index:999;">
            <div style="background:white; border-radius:12px; padding:2rem; width:100%; max-width:420px; border:1px solid #E2E8F0;">
                <p style="font-size:16px; font-weight:600; color:#1E293B; margin:0 0 1.5rem;">Agregar movimiento</p>

                <div style="display:flex; gap:8px; margin-bottom:1rem;">
                    <button @click="movForm.tipo='ingreso'" :style="movForm.tipo==='ingreso' ? btnIngreso : btnInactivo">Ingreso</button>
                    <button @click="movForm.tipo='egreso'" :style="movForm.tipo==='egreso' ? btnEgreso : btnInactivo">Egreso</button>
                </div>

                <div style="margin-bottom:1rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Concepto rápido</label>
                    <div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:8px;">
                        <button v-for="c in conceptosRapidos" :key="c"
                            @click="movForm.concepto = c"
                            :style="{
                                padding:'5px 10px', borderRadius:'20px', fontSize:'11px', cursor:'pointer', fontWeight:'600',
                                border: movForm.concepto === c ? '2px solid #2563EB' : '1px solid #E2E8F0',
                                background: movForm.concepto === c ? '#EFF6FF' : '#F8FAFC',
                                color: movForm.concepto === c ? '#1D4ED8' : '#64748B'
                            }">{{ c }}</button>
                    </div>
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">O escribe un concepto *</label>
                    <input v-model="movForm.concepto" type="text" placeholder="Ej: Pago de servicios, Venta efectivo..."
                        style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                </div>

                <div style="margin-bottom:1rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Monto (S/) *</label>
                    <input v-model="movForm.monto" type="number" step="0.01" min="0.01"
                        style="width:100%; padding:12px; border:2px solid #E2E8F0; border-radius:10px; font-size:18px; font-weight:700; color:#1E293B; outline:none; box-sizing:border-box; text-align:right;"/>
                </div>

                <div style="margin-bottom:1.5rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Observaciones</label>
                    <input v-model="movForm.observaciones" type="text"
                        style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                </div>

                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button @click="modalMovimiento=false"
                        style="padding:10px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">
                        Cancelar
                    </button>
                    <button @click="guardarMovimiento"
                        style="padding:10px 20px; background:#2563EB; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                        Guardar
                    </button>
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
    caja:         Object,
    sesionActiva: Object,
    historial:    Array,
})

const montoApertura      = ref(0)
const montoCierreReal    = ref('')
const observacionesCierre= ref('')
const modalMovimiento    = ref(false)

const movForm = ref({
    tipo:         'ingreso',
    concepto:     '',
    monto:        '',
    observaciones:'',
})

const tipoIngreso = { padding:'8px 20px', background:'#F0FDF4', color:'#166534', border:'2px solid #DCFCE7', borderRadius:'8px', fontSize:'13px', fontWeight:'600', cursor:'pointer' }
const tipoEgreso  = { padding:'8px 20px', background:'#FEF2F2', color:'#991B1B', border:'2px solid #FECACA', borderRadius:'8px', fontSize:'13px', fontWeight:'600', cursor:'pointer' }
const btnInactivo = { padding:'8px 20px', background:'#F1F5F9', color:'#64748B', border:'1px solid #E2E8F0', borderRadius:'8px', fontSize:'13px', cursor:'pointer' }
const btnIngreso  = tipoIngreso
const btnEgreso   = tipoEgreso

const tipoIngresoBadge = { fontSize:'11px', background:'#F0FDF4', color:'#166534', padding:'2px 8px', borderRadius:'20px' }
const tipoEgresoBadge  = { fontSize:'11px', background:'#FEF2F2', color:'#991B1B', padding:'2px 8px', borderRadius:'20px' }

const totalIngresos = computed(() => {
    if (!props.sesionActiva?.movimientos) return 0
    return props.sesionActiva.movimientos.filter(m => m.concepto !== 'Apertura de caja')
        .filter(m => m.tipo === 'ingreso')
        .reduce((sum, m) => sum + parseFloat(m.monto), 0)
})

const totalEgresos = computed(() => {
    if (!props.sesionActiva?.movimientos) return 0
    return props.sesionActiva.movimientos
        .filter(m => m.tipo === 'egreso')
        .reduce((sum, m) => sum + parseFloat(m.monto), 0)
})

const saldoActual = computed(() => {
    if (!props.sesionActiva) return 0
    return parseFloat(props.sesionActiva.monto_apertura) + totalIngresos.value - totalEgresos.value
})

const diferenciaCierre = computed(() => {
    if (montoCierreReal.value === '') return 0
    return parseFloat(montoCierreReal.value) - saldoActual.value
})

const formatFecha = (fecha) => {
    if (!fecha) return '—'
    return new Date(fecha).toLocaleDateString('es-PE', {
        day:'2-digit', month:'2-digit', year:'numeric',
        hour:'2-digit', minute:'2-digit'
    })
}

const formatHora = (fecha) => {
    if (!fecha) return '—'
    return new Date(fecha).toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' })
}

const abrirCaja = () => {
    if (!montoApertura.value && montoApertura.value !== 0) return
    router.post('/caja/abrir', { monto_apertura: montoApertura.value })
}

const guardarMovimiento = () => {
    if (!movForm.value.concepto || !movForm.value.monto) return
    const conceptosRapidos = [
        'Ajuste por error de cobro',
        'Corrección de monto',
        'Ingreso no registrado',
        'Devolución a cliente',
        'Gasto operativo',
        'Fondo de cambio',
    ]

    router.post('/caja/movimiento', movForm.value, {
        onSuccess: () => {
            modalMovimiento.value = false
            movForm.value = { tipo:'ingreso', concepto:'', monto:'', observaciones:'' }
        }
    })
}

const cerrarCaja = () => {
    if (!confirm('¿Estás seguro de cerrar la caja?')) return
    router.post('/caja/cerrar', {
        monto_cierre_real: montoCierreReal.value || saldoActual.value,
        observaciones:     observacionesCierre.value,
    })
}
</script>