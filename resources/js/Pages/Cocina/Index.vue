<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    pedidos: Array,
})

// Auto-refresh cada 15 segundos
let intervalo = null
onMounted(() => {
    intervalo = setInterval(() => {
        router.reload({ only: ['pedidos'] })
    }, 15000)
})
onUnmounted(() => clearInterval(intervalo))

const tiempoTranscurrido = (fecha) => {
    const diff = Math.floor((new Date() - new Date(fecha)) / 1000 / 60)
    if (diff < 1) return 'Ahora mismo'
    if (diff === 1) return '1 min'
    return `${diff} min`
}

const colorTiempo = (fecha) => {
    const diff = Math.floor((new Date() - new Date(fecha)) / 1000 / 60)
    if (diff < 10) return { bg: '#F0FDF4', color: '#166534', border: '#DCFCE7' }
    if (diff < 20) return { bg: '#FFFBEB', color: '#92400E', border: '#FDE68A' }
    return { bg: '#FEF2F2', color: '#991B1B', border: '#FECACA' }
}

function marcarListo(pedido) {
    router.post(`/cocina/${pedido.id}/listo`, {}, {
        onSuccess: () => router.reload({ only: ['pedidos'] }),
        onError: (e) => console.log('Error:', e),
    })
}

const resumenPlatos = computed(() => {
    const resumen = {}
    props.pedidos.forEach(pedido => {
        pedido.detalles.forEach(det => {
            if (resumen[det.nombre_producto]) {
                resumen[det.nombre_producto] += det.cantidad
            } else {
                resumen[det.nombre_producto] = det.cantidad
            }
        })
    })
    return resumen
})

const actualizando = ref(false)
function recargar() {
    actualizando.value = true
    router.reload({ only: ['pedidos'], onFinish: () => { actualizando.value = false } })
}
</script>

<template>
    <AppLayout title="🍳 Pantalla de Cocina">
        <div style="max-width:1400px; margin:0 auto;">

            <!-- Header -->

            <!-- ══ RESUMEN POR PLATO ══ -->
<div v-if="pedidos.length" style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; margin-bottom:24px; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
    <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 16px;">📊 Resumen total de platos</p>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:12px;">
        <div
            v-for="(cantidad, nombre) in resumenPlatos"
            :key="nombre"
            style="display:flex; align-items:center; justify-content:space-between; background:#F8FAFC; border-radius:14px; padding:14px 18px; border:2px solid #E2E8F0;"
        >
            <p style="font-size:16px; font-weight:600; color:#1E293B; margin:0;">{{ nombre }}</p>
            <span style="font-size:24px; font-weight:800; color:white; background:linear-gradient(135deg,#14B8A6,#0F766E); padding:6px 14px; border-radius:10px; min-width:44px; text-align:center;">
                x{{ cantidad }}
            </span>
        </div>
    </div>
</div>
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:32px; font-weight:800; color:#1E293B; margin:0;">🍳 Cocina</h1>
                    <p style="font-size:16px; color:#94A3B8; margin:4px 0 0;">
                        {{ pedidos.length }} pedido{{ pedidos.length !== 1 ? 's' : '' }} pendiente{{ pedidos.length !== 1 ? 's' : '' }}
                        · Se actualiza cada 15 seg
                    </p>
                </div>
                <button
                    @click="recargar"
                    :style="{
                        display: 'flex',
                        alignItems: 'center',
                        gap: '8px',
                        background: actualizando ? '#F1F5F9' : 'linear-gradient(135deg,#14B8A6,#0F766E)',
                        color: actualizando ? '#94A3B8' : 'white',
                        border: 'none',
                        borderRadius: '14px',
                        padding: '14px 24px',
                        fontSize: '16px',
                        fontWeight: '700',
                        cursor: 'pointer',
                        boxShadow: actualizando ? 'none' : '0 4px 15px rgba(20,184,166,0.3)',
                    }"
                >
                    {{ actualizando ? '⏳ Actualizando...' : '🔄 Actualizar' }}
                </button>
            </div>

            <!-- Sin pedidos -->
            <div v-if="!pedidos.length"
                style="text-align:center; padding:100px 0; background:white; border-radius:24px; border:2px dashed #E2E8F0;">
                <p style="font-size:64px; margin:0 0 16px;">✅</p>
                <p style="font-size:24px; font-weight:700; color:#1E293B; margin:0 0 8px;">¡Todo listo!</p>
                <p style="font-size:16px; color:#94A3B8; margin:0;">No hay pedidos pendientes en cocina</p>
            </div>

            <!-- Grid de pedidos -->
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:16px;">
                <div
                    v-for="pedido in pedidos"
                    :key="pedido.id"
                    :style="{
                        background: 'white',
                        borderRadius: '20px',
                        overflow: 'hidden',
                        boxShadow: '0 4px 20px rgba(0,0,0,0.08)',
                        border: `2px solid ${colorTiempo(pedido.created_at).border}`,
                        display: 'flex',
                        flexDirection: 'column',
                    }"
                >
                    <!-- Header tarjeta -->
                    <div :style="{
                        background: colorTiempo(pedido.created_at).bg,
                        padding: '18px 20px',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        borderBottom: `2px solid ${colorTiempo(pedido.created_at).border}`,
                    }">
                        <div>
                            <p style="font-size:26px; font-weight:800; color:#1E293B; margin:0;">
                                🪑 Mesa {{ pedido.mesa?.numero ?? '?' }}
                            </p>
                            <p style="font-size:15px; color:#64748B; margin:4px 0 0;">
                                Ronda {{ pedido.numero_ronda }} · {{ pedido.detalles.length }} items
                            </p>
                        </div>
                        <div style="text-align:right;">
                            <p :style="{
                                fontSize: '22px',
                                fontWeight: '800',
                                color: colorTiempo(pedido.created_at).color,
                                margin: '0',
                            }">⏱ {{ tiempoTranscurrido(pedido.created_at) }}</p>
                            <p style="font-size:13px; color:#94A3B8; margin:4px 0 0;">desde el pedido</p>
                        </div>
                    </div>

                    <!-- Items -->
                    <div style="padding:16px 20px; flex:1;">
                        <div
                            v-for="det in pedido.detalles"
                            :key="det.id"
                            style="display:flex; align-items:center; gap:14px; padding:12px 0; border-bottom:1px solid #F1F5F9;"
                        >
                            <!-- Cantidad -->
                            <div style="width:44px; height:44px; background:#F1F5F9; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; font-weight:800; color:#1E293B; flex-shrink:0;">
                                {{ det.cantidad }}
                            </div>

                            <!-- Nombre -->
                            <div style="flex:1; min-width:0;">
                                <p style="font-size:17px; font-weight:700; color:#1E293B; margin:0; line-height:1.3;">{{ det.nombre_producto }}</p>
                                <p v-if="det.notas" style="font-size:13px; color:#F59E0B; margin:4px 0 0; font-weight:600;">📝 {{ det.notas }}</p>
                            </div>

                            <!-- Estado -->
                            <span :style="{
                                padding: '6px 12px',
                                borderRadius: '10px',
                                fontSize: '13px',
                                fontWeight: '700',
                                background: det.estado === 'listo' ? '#DCFCE7' : det.estado === 'en_preparacion' ? '#FEF3C7' : '#F1F5F9',
                                color: det.estado === 'listo' ? '#166534' : det.estado === 'en_preparacion' ? '#92400E' : '#64748B',
                            }">
                                {{ det.estado === 'listo' ? '✅ Listo' : det.estado === 'en_preparacion' ? '👨‍🍳 Prep.' : '⏳ Pendiente' }}
                            </span>
                        </div>

                        <!-- Notas del pedido -->
                        <div v-if="pedido.notas" style="margin-top:12px; padding:12px; background:#FFFBEB; border-radius:12px; border:1px solid #FDE68A;">
                            <p style="font-size:14px; color:#92400E; margin:0; font-weight:600;">📝 {{ pedido.notas }}</p>
                        </div>
                    </div>

                    <!-- Botón listo -->
                    <div style="padding:16px 20px; border-top:2px solid #F1F5F9;">
                        <button
                            @click="marcarListo(pedido)"
                            style="width:100%; padding:16px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:14px; font-size:18px; font-weight:800; cursor:pointer; box-shadow:0 4px 15px rgba(20,184,166,0.3); transition:transform 0.15s;"
                            @mousedown="$event.currentTarget.style.transform='scale(0.97)'"
                            @mouseup="$event.currentTarget.style.transform='scale(1)'"
                        >
                            ✅ Marcar como listo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>