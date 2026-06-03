<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    reservas: Array,
    habitacionesDisponibles: Array,
    todasHabitaciones: Array,
    huespedes: Array,
})

const showCheckin = ref(false)
const showCheckout = ref(null)
const cargando = ref(false)

const form = ref({
    habitacion_id: '',
    tipo_documento: '1',
    numero_documento: '',
    nombre_completo: '',
    email: '',
    telefono: '',
    nacionalidad: 'Peruana',
    fecha_checkin: new Date(new Date().getTime() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0,16),
    fecha_checkout: '',
    num_huespedes: 1,
    observaciones: '',
})

const pagoForm = ref({ monto: 0, metodo_pago: 'efectivo', referencia: '' })

const hacerCheckin = async () => {
    cargando.value = true
    try {
        const res = await fetch('/hotel/checkin', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify(form.value)
        })
        const data = await res.json()
        if (data.success) {
            alert('✅ ' + data.mensaje)
            showCheckin.value = false
            router.reload()
        } else {
            alert('❌ ' + (data.mensaje || 'Error'))
        }
    } finally { cargando.value = false }
}

const hacerCheckout = async (reserva) => {
    cargando.value = true
    try {
        const res = await fetch('/hotel/checkout/' + reserva.id, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify(pagoForm.value)
        })
        const data = await res.json()
        if (data.success) {
            alert('✅ ' + data.mensaje)
            showCheckout.value = null
            router.reload()
        } else {
            alert('❌ ' + (data.mensaje || 'Error'))
        }
    } finally { cargando.value = false }
}

const infoHuesped = ref(null)

const clickHabitacion = (h) => {
    if (h.estado === 'disponible') {
        form.value.habitacion_id = h.id
        showCheckin.value = true
        infoHuesped.value = null
    } else if (h.estado === 'ocupada') {
        const reserva = reservas.value ? reservas.value.find(r => r.habitacion_id === h.id && r.estado === 'checkin') : null
        infoHuesped.value = reserva ? {
            habitacion: 'Hab. ' + h.numero + ' — ' + h.tipo?.nombre,
            huesped: reserva.huesped?.nombre_completo,
            documento: reserva.huesped?.numero_documento,
            checkin: new Date(reserva.fecha_checkin).toLocaleDateString('es-PE'),
            checkout: new Date(reserva.fecha_checkout_previsto).toLocaleDateString('es-PE'),
            total: reserva.total,
            pagado: reserva.monto_pagado,
        } : null
    }
}

const estadoBadge = (estado) => {
    const m = { reservado: '#3B82F6', checkin: '#16A34A', checkout: '#6B7280', cancelado: '#DC2626' }
    return m[estado] || '#6B7280'
}
</script>

<template>
    <AppLayout title="Recepción">
        <div style="padding:24px; font-family:'Inter',sans-serif;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:24px; font-weight:700; color:#1E293B; margin:0;">🏁 Recepción</h1>
                    <p style="color:#64748B; font-size:13px; margin:4px 0 0;">Check-in y Check-out de huéspedes</p>
                </div>
                <button @click="showCheckin = true"
                    style="background:#3B82F6; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:14px;">
                    ➕ Nuevo Check-in
                </button>
            </div>

            <!-- Mapa rápido de habitaciones -->
            <div style="background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.08); padding:16px; margin-bottom:20px;">
                <div style="font-size:13px; font-weight:600; color:#374151; margin-bottom:12px;">🗺️ Estado de habitaciones</div>
                <div style="display:flex; flex-wrap:wrap; gap:10px;">
                    <div v-for="h in todasHabitaciones" :key="h.id"
                        @click="clickHabitacion(h)"
                        :style="{
                            padding:'10px 14px', borderRadius:'8px', fontSize:'12px', fontWeight:'600', minWidth:'110px', textAlign:'center',
                            cursor: h.estado==='disponible' || h.estado==='ocupada' ? 'pointer' : 'default',
                            background: h.estado==='disponible' ? '#DCFCE7' : h.estado==='ocupada' ? '#FEE2E2' : h.estado==='limpieza' ? '#FEF9C3' : '#F1F5F9',
                            color: h.estado==='disponible' ? '#16A34A' : h.estado==='ocupada' ? '#DC2626' : h.estado==='limpieza' ? '#CA8A04' : '#6B7280',
                            border: '2px solid',
                            borderColor: h.estado==='disponible' ? '#86EFAC' : h.estado==='ocupada' ? '#FCA5A5' : h.estado==='limpieza' ? '#FDE047' : '#E2E8F0',
                            transform: h.estado==='disponible' || h.estado==='ocupada' ? 'scale(1)' : '',
                        }">
                        <div style="font-size:15px; font-weight:700;">Hab. {{ h.numero }}</div>
                        <div style="font-size:11px; opacity:0.8;">{{ h.tipo?.nombre }}</div>
                        <div style="font-size:12px; margin-top:4px;">
                            {{ h.estado==='disponible' ? '✅ Libre' : h.estado==='ocupada' ? '🔴 Ocupada' : h.estado==='limpieza' ? '🧹 Limpieza' : '🔧 Mant.' }}
                        </div>
                        <div style="font-size:11px;">S/ {{ h.tipo?.precio_noche }}/noche</div>
                        <div v-if="h.estado==='disponible'" style="font-size:10px; margin-top:4px; opacity:0.7;">Clic para check-in</div>
                        <div v-if="h.estado==='ocupada'" style="font-size:10px; margin-top:4px; opacity:0.7;">Clic para ver info</div>
                    </div>
                </div>

                <!-- Info huésped al clic en habitación ocupada -->
                <div v-if="infoHuesped" style="margin-top:16px; background:#FEF2F2; border:1px solid #FCA5A5; border-radius:8px; padding:14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div style="font-weight:700; color:#DC2626; font-size:13px;">🔴 {{ infoHuesped.habitacion }}</div>
                        <button @click="infoHuesped=null" style="background:none; border:none; cursor:pointer; color:#94A3B8; font-size:16px;">✕</button>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-top:10px; font-size:12px;">
                        <div><span style="color:#64748B;">Huésped:</span><br><b>{{ infoHuesped.huesped }}</b></div>
                        <div><span style="color:#64748B;">Documento:</span><br><b>{{ infoHuesped.documento }}</b></div>
                        <div><span style="color:#64748B;">Check-in:</span><br><b>{{ infoHuesped.checkin }}</b></div>
                        <div><span style="color:#64748B;">Check-out prev.:</span><br><b>{{ infoHuesped.checkout }}</b></div>
                        <div><span style="color:#64748B;">Total:</span><br><b>S/ {{ infoHuesped.total }}</b></div>
                        <div><span style="color:#64748B;">Pagado:</span><br><b>S/ {{ infoHuesped.pagado }}</b></div>
                    </div>
                </div>

                <div style="display:flex; gap:16px; margin-top:12px; font-size:11px; color:#64748B;">
                    <span>✅ Disponible — clic para check-in</span><span>🔴 Ocupada — clic para ver huésped</span><span>🧹 Limpieza</span><span>🔧 Mantenimiento</span>
                </div>
            </div>

            <!-- Lista de reservas activas -->
            <div style="background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.08); overflow:hidden;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#F8FAFC;">
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">CÓDIGO</th>
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">HUÉSPED</th>
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">HABITACIÓN</th>
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">CHECK-IN</th>
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">CHECK-OUT</th>
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">TOTAL</th>
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">ESTADO</th>
                            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="reservas.length === 0">
                            <td colspan="8" style="padding:40px; text-align:center; color:#94A3B8;">No hay reservas activas</td>
                        </tr>
                        <tr v-for="r in reservas" :key="r.id" style="border-top:1px solid #F1F5F9;">
                            <td style="padding:12px 16px; font-weight:600; font-size:13px;">{{ r.codigo }}</td>
                            <td style="padding:12px 16px; font-size:13px;">
                                <div style="font-weight:600;">{{ r.huesped?.nombre_completo }}</div>
                                <div style="color:#64748B; font-size:11px;">{{ r.huesped?.numero_documento }}</div>
                            </td>
                            <td style="padding:12px 16px; font-size:13px;">
                                <div style="font-weight:600;">Hab. {{ r.habitacion?.numero }}</div>
                                <div style="color:#64748B; font-size:11px;">{{ r.habitacion?.tipo?.nombre }}</div>
                            </td>
                            <td style="padding:12px 16px; font-size:12px;">{{ new Date(r.fecha_checkin).toLocaleDateString('es-PE') }}</td>
                            <td style="padding:12px 16px; font-size:12px;">{{ new Date(r.fecha_checkout_previsto).toLocaleDateString('es-PE') }}</td>
                            <td style="padding:12px 16px; font-size:13px; font-weight:600;">S/ {{ Number(r.total).toFixed(2) }}</td>
                            <td style="padding:12px 16px;">
                                <span :style="{background: estadoBadge(r.estado)+'20', color: estadoBadge(r.estado), padding:'3px 10px', borderRadius:'20px', fontSize:'11px', fontWeight:'600'}">
                                    {{ r.estado.toUpperCase() }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;">
                                <button v-if="r.estado === 'checkin'" @click="showCheckout = r; pagoForm.monto = r.total - r.monto_pagado"
                                    style="background:#DC2626; color:#fff; border:none; padding:5px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                    🚪 Check-out
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal Check-in -->
            <div v-if="showCheckin" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
                <div style="background:#fff; border-radius:16px; padding:28px; width:560px; max-height:90vh; overflow-y:auto;">
                    <h2 style="font-size:18px; font-weight:700; margin:0 0 20px;">🏁 Nuevo Check-in</h2>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                        <div style="grid-column:1/-1;">
                            <label style="font-size:12px; font-weight:600; color:#374151;">Habitación</label>
                            <select v-model="form.habitacion_id" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;">
                                <option value="">Seleccionar habitación</option>
                                <option v-for="h in habitacionesDisponibles" :key="h.id" :value="h.id">
                                    Hab. {{ h.numero }} — {{ h.tipo?.nombre }} (S/ {{ h.tipo?.precio_noche }}/noche)
                                </option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">Tipo Documento</label>
                            <select v-model="form.tipo_documento" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;">
                                <option value="1">DNI</option>
                                <option value="4">Carné Extranjería</option>
                                <option value="7">Pasaporte</option>
                                <option value="6">RUC</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">N° Documento</label>
                            <input v-model="form.numero_documento" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" placeholder="12345678" />
                        </div>
                        <div style="grid-column:1/-1;">
                            <label style="font-size:12px; font-weight:600; color:#374151;">Nombre Completo</label>
                            <input v-model="form.nombre_completo" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" placeholder="Nombre del huésped" />
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">Teléfono</label>
                            <input v-model="form.telefono" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" placeholder="999999999" />
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">Nacionalidad</label>
                            <input v-model="form.nacionalidad" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" />
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">Fecha Check-in</label>
                            <input type="datetime-local" v-model="form.fecha_checkin" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" />
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">Fecha Check-out</label>
                            <input type="datetime-local" v-model="form.fecha_checkout" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" />
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">N° Huéspedes</label>
                            <input type="number" v-model="form.num_huespedes" min="1" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" />
                        </div>
                        <div style="grid-column:1/-1;">
                            <label style="font-size:12px; font-weight:600; color:#374151;">Observaciones</label>
                            <textarea v-model="form.observaciones" rows="2" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;"></textarea>
                        </div>
                    </div>
                    <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
                        <button @click="showCheckin = false" style="background:#F1F5F9; color:#374151; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600;">Cancelar</button>
                        <button @click="hacerCheckin" :disabled="cargando" style="background:#3B82F6; color:#fff; border:none; padding:10px 24px; border-radius:8px; cursor:pointer; font-weight:600;">
                            {{ cargando ? '⏳...' : '✅ Confirmar Check-in' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Check-out -->
            <div v-if="showCheckout" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
                <div style="background:#fff; border-radius:16px; padding:28px; width:420px;">
                    <h2 style="font-size:18px; font-weight:700; margin:0 0 16px;">🚪 Check-out — {{ showCheckout.codigo }}</h2>
                    <div style="background:#F8FAFC; border-radius:8px; padding:14px; margin-bottom:16px;">
                        <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:6px;">
                            <span style="color:#64748B;">Total estadía:</span>
                            <span style="font-weight:600;">S/ {{ Number(showCheckout.total).toFixed(2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:6px;">
                            <span style="color:#64748B;">Pagado:</span>
                            <span style="color:#16A34A; font-weight:600;">S/ {{ Number(showCheckout.monto_pagado).toFixed(2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:14px; border-top:1px solid #E2E8F0; padding-top:8px; margin-top:8px;">
                            <span style="font-weight:600;">Saldo pendiente:</span>
                            <span style="font-weight:700; color:#DC2626;">S/ {{ Number(showCheckout.total - showCheckout.monto_pagado).toFixed(2) }}</span>
                        </div>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="font-size:12px; font-weight:600; color:#374151;">Monto a cobrar</label>
                        <input type="number" v-model="pagoForm.monto" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" />
                    </div>
                    <div style="margin-bottom:16px;">
                        <label style="font-size:12px; font-weight:600; color:#374151;">Método de pago</label>
                        <select v-model="pagoForm.metodo_pago" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;">
                            <option value="efectivo">Efectivo</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>
                    <div style="display:flex; gap:10px; justify-content:flex-end;">
                        <button @click="showCheckout = null" style="background:#F1F5F9; color:#374151; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600;">Cancelar</button>
                        <button @click="hacerCheckout(showCheckout)" :disabled="cargando" style="background:#DC2626; color:#fff; border:none; padding:10px 24px; border-radius:8px; cursor:pointer; font-weight:600;">
                            {{ cargando ? '⏳...' : '🚪 Confirmar Check-out' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
