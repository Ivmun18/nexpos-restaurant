<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    mesa:    Object,
    pedidos: Array,
    total:   Number,
})

const form = useForm({
    metodo_pago:  'efectivo',
    monto_pagado: '',
    notas:        '',
})

const vuelto = computed(() => {
    const monto = parseFloat(form.monto_pagado) || 0
    const diff  = monto - props.total
    return diff > 0 ? diff : 0
})

const faltante = computed(() => {
    const monto = parseFloat(form.monto_pagado) || 0
    const diff  = props.total - monto
    return diff > 0 ? diff : 0
})

const metodos = [
    { key: 'efectivo', label: 'Efectivo', icon: '💵' },
    { key: 'yape',     label: 'Yape',     icon: '📱' },
    { key: 'plin',     label: 'Plin',     icon: '📲' },
    { key: 'tarjeta',  label: 'Tarjeta',  icon: '💳' },
]

function cobrar() {
    if (!form.monto_pagado || parseFloat(form.monto_pagado) < props.total) {
        if (!confirm('El monto es menor al total. ¿Desea continuar de todas formas?')) return
    }
    form.post(`/caja-restaurante/${props.mesa.id}`)
}

</script>

<template>
    <AppLayout :title="`💳 Cobrar Mesa ${mesa.numero}`">
        <div style="max-width:900px; margin:0 auto;">

            <!-- Header -->
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
                <a href="/mesas" style="background:#F1F5F9; border:none; border-radius:12px; padding:12px 16px; font-size:16px; cursor:pointer; text-decoration:none; color:#475569; font-weight:600;">
                    ← Volver
                </a>
                <div>
                    <h1 style="font-size:28px; font-weight:800; color:#1E293B; margin:0;">💳 Cobrar Mesa {{ mesa.numero }}</h1>
                    <p style="font-size:15px; color:#94A3B8; margin:4px 0 0;">{{ pedidos.length }} ronda{{ pedidos.length !== 1 ? 's' : '' }} · {{ mesa.zona }}</p>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

                <!-- ══ DETALLE PEDIDOS ══ -->
                <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                    <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 20px;">🧾 Detalle del consumo</p>

                    <div v-for="pedido in pedidos" :key="pedido.id" style="margin-bottom:20px;">
                        <p style="font-size:14px; font-weight:700; color:#94A3B8; text-transform:uppercase; letter-spacing:1px; margin:0 0 10px;">
                            Ronda {{ pedido.numero_ronda }}
                        </p>
                        <div v-for="det in pedido.detalles" :key="det.id"
                            style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #F1F5F9;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="background:#F1F5F9; border-radius:8px; padding:4px 10px; font-size:15px; font-weight:700; color:#1E293B;">
                                    x{{ det.cantidad }}
                                </span>
                                <span style="font-size:16px; color:#1E293B; font-weight:500;">{{ det.nombre_producto }}</span>
                            </div>
                            <span style="font-size:16px; font-weight:700; color:#1E293B;">S/ {{ Number(det.subtotal).toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 0; border-top:3px solid #E2E8F0; margin-top:8px;">
                        <span style="font-size:22px; font-weight:800; color:#1E293B;">TOTAL</span>
                        <span style="font-size:32px; font-weight:800; color:#14B8A6;">S/ {{ Number(total).toFixed(2) }}</span>
                    </div>
                </div>

                <!-- ══ COBRO ══ -->
                <div style="display:flex; flex-direction:column; gap:16px;">

                    <!-- Método de pago -->
                    <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                        <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 16px;">💳 Método de pago</p>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <button
                                v-for="m in metodos"
                                :key="m.key"
                                @click="form.metodo_pago = m.key"
                                :style="{
                                    padding: '16px',
                                    borderRadius: '14px',
                                    border: 'none',
                                    cursor: 'pointer',
                                    fontSize: '16px',
                                    fontWeight: '700',
                                    transition: 'all 0.15s',
                                    background: form.metodo_pago === m.key ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : '#F1F5F9',
                                    color: form.metodo_pago === m.key ? 'white' : '#475569',
                                    boxShadow: form.metodo_pago === m.key ? '0 4px 15px rgba(20,184,166,0.3)' : 'none',
                                    transform: form.metodo_pago === m.key ? 'scale(1.03)' : 'scale(1)',
                                }"
                            >
                                {{ m.icon }} {{ m.label }}
                            </button>
                        </div>
                    </div>

                    <!-- Monto y vuelto -->
                    <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                        <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 16px;">💵 Monto recibido</p>

                        <input
                            v-model="form.monto_pagado"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            style="width:100%; padding:18px; border:3px solid #E2E8F0; border-radius:14px; font-size:28px; font-weight:800; outline:none; box-sizing:border-box; text-align:center; color:#1E293B;"
                            @focus="$event.target.style.borderColor='#14B8A6'"
                            @blur="$event.target.style.borderColor='#E2E8F0'"
                        />

                        <!-- Vuelto -->
                        <div v-if="form.monto_pagado" style="margin-top:16px;">
                            <div v-if="vuelto > 0"
                                style="background:#F0FDF4; border:2px solid #DCFCE7; border-radius:14px; padding:16px; text-align:center;">
                                <p style="font-size:14px; color:#166534; font-weight:600; margin:0 0 4px;">💚 Vuelto a dar</p>
                                <p style="font-size:36px; font-weight:800; color:#166534; margin:0;">S/ {{ vuelto.toFixed(2) }}</p>
                            </div>
                            <div v-else-if="faltante > 0"
                                style="background:#FEF2F2; border:2px solid #FECACA; border-radius:14px; padding:16px; text-align:center;">
                                <p style="font-size:14px; color:#991B1B; font-weight:600; margin:0 0 4px;">❌ Falta</p>
                                <p style="font-size:36px; font-weight:800; color:#991B1B; margin:0;">S/ {{ faltante.toFixed(2) }}</p>
                            </div>
                            <div v-else
                                style="background:#F0FDF4; border:2px solid #DCFCE7; border-radius:14px; padding:16px; text-align:center;">
                                <p style="font-size:20px; font-weight:800; color:#166534; margin:0;">✅ Monto exacto</p>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div style="margin-top:16px;">
                            <input
                                v-model="form.notas"
                                type="text"
                                placeholder="📝 Notas opcionales..."
                                style="width:100%; padding:14px; border:2px solid #E2E8F0; border-radius:12px; font-size:15px; outline:none; box-sizing:border-box;"
                            />
                        </div>
                    </div>

                    <!-- Botón cobrar -->
                    <button
                        @click="cobrar"
                        :disabled="form.processing || !form.monto_pagado"
                        :style="{
                            width: '100%',
                            padding: '22px',
                            background: form.monto_pagado ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : '#E2E8F0',
                            color: form.monto_pagado ? 'white' : '#94A3B8',
                            border: 'none',
                            borderRadius: '16px',
                            fontSize: '22px',
                            fontWeight: '800',
                            cursor: form.monto_pagado ? 'pointer' : 'not-allowed',
                            boxShadow: form.monto_pagado ? '0 6px 20px rgba(20,184,166,0.4)' : 'none',
                        }"
                    >
                        {{ form.processing ? '⏳ Procesando...' : `✅ Cobrar S/ ${Number(total).toFixed(2)}` }}
                    </button>

                    <!-- Botón emitir comprobante -->
                </div>
            </div>
        </div>
    </AppLayout>
</template>