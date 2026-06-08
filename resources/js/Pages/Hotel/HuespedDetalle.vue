<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    huesped: Object, reservas: Array,
    totalGastado: Number, totalEstadias: Number, totalNoches: Number
})
const fmt = (f) => f ? new Date(f).toLocaleDateString('es-PE') : '-'
const money = (n) => 'S/ ' + Number(n||0).toFixed(2)
const estadoColor = (e) => ({ reservado:'#3B82F6', checkin:'#16A34A', checkout:'#6B7280', cancelado:'#DC2626' }[e] || '#6B7280')
const estadoLabel = (e) => ({ reservado:'📅 Reservado', checkin:'🏠 En hotel', checkout:'✅ Completado', cancelado:'❌ Cancelado' }[e] || e)
</script>
<template>
    <AppLayout :title="huesped.nombre_completo">
        <div style="padding:24px; font-family:'Inter',sans-serif; max-width:900px;">

            <!-- Botón volver -->
            <button @click="router.visit('/hotel/huespedes')"
                style="background:#F1F5F9; border:none; padding:8px 16px; border-radius:8px; cursor:pointer; font-size:13px; font-weight:600; color:#374151; margin-bottom:20px;">
                ← Volver a Huéspedes
            </button>

            <!-- Datos huésped -->
            <div style="background:#fff; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.08); margin-bottom:20px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px;">
                    <div>
                        <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                            <div style="width:52px; height:52px; background:#EFF6FF; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px;">👤</div>
                            <div>
                                <h2 style="font-size:20px; font-weight:800; color:#1E293B; margin:0;">{{ huesped.nombre_completo }}</h2>
                                <div style="font-size:13px; color:#64748B;">{{ huesped.tipo_documento === '1' ? 'DNI' : huesped.tipo_documento === '7' ? 'Pasaporte' : 'Doc.' }} {{ huesped.numero_documento }}</div>
                            </div>
                        </div>
                        <div style="display:flex; gap:20px; flex-wrap:wrap;">
                            <div v-if="huesped.telefono" style="font-size:13px; color:#374151;">📞 {{ huesped.telefono }}</div>
                            <div v-if="huesped.email" style="font-size:13px; color:#374151;">✉️ {{ huesped.email }}</div>
                            <div style="font-size:13px; color:#374151;">🌎 {{ huesped.nacionalidad || 'Peruana' }}</div>
                            <div v-if="huesped.procedencia" style="font-size:13px; color:#374151;">📍 {{ huesped.procedencia }}</div>
                        </div>
                    </div>
                    <!-- KPIs -->
                    <div style="display:flex; gap:12px; flex-wrap:wrap;">
                        <div style="background:#EFF6FF; border-radius:10px; padding:14px 18px; text-align:center; min-width:90px;">
                            <div style="font-size:22px; font-weight:900; color:#3B82F6;">{{ totalEstadias }}</div>
                            <div style="font-size:11px; color:#64748B; margin-top:2px;">Estadías</div>
                        </div>
                        <div style="background:#F0FDF4; border-radius:10px; padding:14px 18px; text-align:center; min-width:90px;">
                            <div style="font-size:22px; font-weight:900; color:#16A34A;">{{ totalNoches }}</div>
                            <div style="font-size:11px; color:#64748B; margin-top:2px;">Noches</div>
                        </div>
                        <div style="background:#FEF9C3; border-radius:10px; padding:14px 18px; text-align:center; min-width:110px;">
                            <div style="font-size:18px; font-weight:900; color:#CA8A04;">{{ money(totalGastado) }}</div>
                            <div style="font-size:11px; color:#64748B; margin-top:2px;">Total gastado</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historial de reservas -->
            <div style="background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.08); overflow:hidden;">
                <div style="padding:16px 20px; border-bottom:1px solid #F1F5F9;">
                    <h3 style="font-size:15px; font-weight:700; color:#1E293B; margin:0;">📋 Historial de Estadías</h3>
                </div>
                <div v-if="reservas.length === 0" style="padding:40px; text-align:center; color:#94A3B8; font-size:13px;">
                    Sin estadías registradas
                </div>
                <div v-for="r in reservas" :key="r.id" style="padding:18px 20px; border-bottom:1px solid #F1F5F9;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
                        <div>
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                                <span style="font-size:13px; font-weight:700; color:#1E293B; font-family:monospace;">{{ r.codigo }}</span>
                                <span :style="{background: estadoColor(r.estado)+'20', color: estadoColor(r.estado), padding:'2px 8px', borderRadius:'20px', fontSize:'11px', fontWeight:'700'}">
                                    {{ estadoLabel(r.estado) }}
                                </span>
                            </div>
                            <div style="display:flex; gap:16px; flex-wrap:wrap;">
                                <div style="font-size:12px; color:#374151;">🛏️ Hab. {{ r.habitacion?.numero }} — {{ r.habitacion?.tipo?.nombre }}</div>
                                <div style="font-size:12px; color:#374151;">📅 {{ fmt(r.fecha_checkin) }} → {{ fmt(r.fecha_checkout_previsto) }}</div>
                                <div style="font-size:12px; color:#374151;">🌙 {{ r.num_noches }} noche{{ r.num_noches !== 1 ? 's' : '' }}</div>
                                <div style="font-size:12px; color:#374151;">👥 {{ r.num_huespedes }} huésped{{ r.num_huespedes !== 1 ? 'es' : '' }}</div>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:16px; font-weight:800; color:#1E293B;">{{ money(r.total) }}</div>
                            <div style="font-size:12px; color:#16A34A; font-weight:600;">Pagado: {{ money(r.monto_pagado) }}</div>
                            <div v-if="r.total - r.monto_pagado > 0" style="font-size:12px; color:#DC2626; font-weight:600;">
                                Saldo: {{ money(r.total - r.monto_pagado) }}
                            </div>
                        </div>
                    </div>
                    <!-- Pagos de esta reserva -->
                    <div v-if="r.pagos && r.pagos.length > 0" style="margin-top:10px; padding:10px 12px; background:#F8FAFC; border-radius:8px;">
                        <div style="font-size:11px; font-weight:700; color:#64748B; margin-bottom:6px; text-transform:uppercase;">Pagos</div>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <span v-for="p in r.pagos" :key="p.id"
                                style="font-size:11px; background:#fff; border:1px solid #E2E8F0; border-radius:6px; padding:3px 8px; color:#374151;">
                                {{ fmt(p.created_at) }} · {{ p.metodo_pago }} · <b>{{ money(p.monto) }}</b>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
