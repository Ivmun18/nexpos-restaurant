<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, Head } from '@inertiajs/vue3'

const props = defineProps({
    pedidos: Array,
    sucursalNombre: String,
})

const pedidos = ref(props.pedidos)
const horaActual = ref(new Date())

const horaFormateada = computed(() =>
    horaActual.value.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false })
)

let relojIntervalo = null
let refrescoIntervalo = null

async function refrescar() {
    try {
        const res = await fetch('/cocina/polling', {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
        if (res.ok) {
            const data = await res.json()
            pedidos.value = data.pedidos
        }
    } catch (e) {
        // Silencioso: se reintenta en el siguiente ciclo de 15s.
    }
}

onMounted(() => {
    relojIntervalo = setInterval(() => { horaActual.value = new Date() }, 1000)
    refrescoIntervalo = setInterval(refrescar, 15000)
})

onUnmounted(() => {
    clearInterval(relojIntervalo)
    clearInterval(refrescoIntervalo)
})

function minutosTranscurridos(fecha) {
    return Math.max(0, Math.floor((horaActual.value - new Date(fecha)) / 1000 / 60))
}

function colorTiempo(fecha) {
    const diff = minutosTranscurridos(fecha)
    if (diff < 10) return { texto: '#4ADE80', borde: '#166534', fondoHeader: '#0F2A1A' }
    if (diff < 20) return { texto: '#FBBF24', borde: '#92400E', fondoHeader: '#2E2408' }
    return { texto: '#F87171', borde: '#991B1B', fondoHeader: '#2E0F0F' }
}

function sucursalDePedido(pedido) {
    return pedido.sucursal?.nombre || pedido.mesa?.sucursal?.nombre || null
}

const resumenProduccion = computed(() => {
    const resumen = {}
    pedidos.value.forEach(pedido => {
        pedido.detalles.forEach(det => {
            if (det.estado === 'listo') return
            resumen[det.nombre_producto] = (resumen[det.nombre_producto] || 0) + det.cantidad
        })
    })
    return Object.entries(resumen)
})

function marcarListo(pedido) {
    router.post(`/cocina/${pedido.id}/listo`, {}, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => refrescar(),
    })
}
</script>

<template>
    <Head :title="`Cocina · ${sucursalNombre || ''}`" />

    <div class="kds-root">
        <!-- Header -->
        <header class="kds-header">
            <div>
                <p class="kds-header-title">🍳 {{ sucursalNombre || 'Cocina' }}</p>
                <p class="kds-header-sub">
                    {{ pedidos.length }} pedido{{ pedidos.length !== 1 ? 's' : '' }} pendiente{{ pedidos.length !== 1 ? 's' : '' }}
                </p>
            </div>
            <div class="kds-clock">{{ horaFormateada }}</div>
        </header>

        <!-- Resumen de producción -->
        <div v-if="resumenProduccion.length" class="kds-resumen">
            <span class="kds-resumen-label">📊 Resumen de producción</span>
            <span v-for="[nombre, cantidad] in resumenProduccion" :key="nombre" class="kds-chip">
                {{ nombre }}
                <span class="kds-chip-badge">x{{ cantidad }}</span>
            </span>
        </div>

        <!-- Sin pedidos -->
        <div v-if="!pedidos.length" class="kds-empty">
            <p style="font-size:96px; margin:0 0 16px;">✅</p>
            <p class="kds-empty-title">¡Todo listo!</p>
            <p class="kds-empty-sub">No hay pedidos pendientes en cocina</p>
        </div>

        <!-- Grid de pedidos -->
        <div v-else class="kds-grid">
            <div
                v-for="pedido in pedidos"
                :key="pedido.id"
                class="kds-card"
                :style="{ borderColor: colorTiempo(pedido.created_at).borde }"
            >
                <!-- Header tarjeta -->
                <div class="kds-card-header" :style="{ background: colorTiempo(pedido.created_at).fondoHeader }">
                    <div>
                        <p class="kds-mesa">🪑 Mesa {{ pedido.mesa?.numero ?? '?' }}</p>
                        <div style="display:flex; align-items:center; gap:10px; margin-top:6px; flex-wrap:wrap;">
                            <span class="kds-ronda">Ronda {{ pedido.numero_ronda }} · {{ pedido.detalles.length }} items</span>
                            <span v-if="sucursalDePedido(pedido)" class="kds-badge-sucursal">{{ sucursalDePedido(pedido) }}</span>
                        </div>
                    </div>
                    <div class="kds-tiempo" :style="{ color: colorTiempo(pedido.created_at).texto }">
                        ⏱ {{ minutosTranscurridos(pedido.created_at) }} min
                    </div>
                </div>

                <!-- Items -->
                <div class="kds-items">
                    <div v-for="det in pedido.detalles" :key="det.id" class="kds-item">
                        <div class="kds-item-cant">{{ det.cantidad }}</div>
                        <div style="flex:1; min-width:0;">
                            <p class="kds-item-nombre">{{ det.nombre_producto }}</p>
                            <p v-if="det.nota_variante" class="kds-item-nota-variante">📝 {{ det.nota_variante }}</p>
                            <p v-if="det.modificadores?.length" class="kds-item-mods">⚠ {{ det.modificadores.join(' · ') }}</p>
                            <p v-if="det.notas" class="kds-item-nota">📝 {{ det.notas }}</p>
                        </div>
                        <span class="kds-item-estado" :class="`kds-item-estado--${det.estado}`">
                            {{ det.estado === 'listo' ? '✅' : det.estado === 'en_preparacion' ? '👨‍🍳' : '⏳' }}
                        </span>
                    </div>

                    <div v-if="pedido.notas" class="kds-nota-pedido">📝 {{ pedido.notas }}</div>
                </div>

                <!-- Botón listo -->
                <button class="kds-btn-listo" @click="marcarListo(pedido)">
                    ✅ LISTO
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.kds-root {
    min-height: 100vh;
    background: #0B1220;
    color: #F1F5F9;
    padding: 16px;
    box-sizing: border-box;
}

.kds-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #111827;
    border: 1px solid #1F2937;
    border-radius: 14px;
    padding: 12px 20px;
    margin-bottom: 14px;
}

.kds-header-title {
    font-size: 19px;
    font-weight: 800;
    margin: 0;
    color: #F8FAFC;
}

.kds-header-sub {
    font-size: 13px;
    color: #94A3B8;
    margin: 2px 0 0;
    font-weight: 600;
}

.kds-clock {
    font-size: 22px;
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    color: #14B8A6;
}

.kds-resumen {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: nowrap;
    overflow-x: auto;
    min-height: 60px;
    background: #182238;
    border: 1px solid #253352;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 14px;
    box-sizing: border-box;
}

.kds-resumen-label {
    font-size: 13px;
    font-weight: 700;
    color: #F1F5F9;
    white-space: nowrap;
    margin-right: 2px;
    flex-shrink: 0;
}

.kds-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #253352;
    color: #F1F5F9;
    font-size: 18px;
    font-weight: 600;
    padding: 10px 16px;
    border-radius: 999px;
    white-space: nowrap;
    flex-shrink: 0;
}

.kds-chip-badge {
    background: #14B8A6;
    color: #0F172A;
    font-size: 16px;
    font-weight: 800;
    padding: 2px 10px;
    border-radius: 999px;
}

.kds-empty {
    text-align: center;
    padding: 140px 0;
    background: #111827;
    border: 2px dashed #1F2937;
    border-radius: 24px;
}

.kds-empty-title {
    font-size: 30px;
    font-weight: 800;
    color: #F8FAFC;
    margin: 0 0 8px;
}

.kds-empty-sub {
    font-size: 18px;
    color: #94A3B8;
    margin: 0;
}

.kds-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
}

@media (min-width: 1100px) {
    .kds-grid {
        grid-template-columns: repeat(3, minmax(280px, 1fr));
    }
}

.kds-card {
    background: #111827;
    border-radius: 14px;
    overflow: hidden;
    border: 2px solid #1F2937;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 16px rgba(0,0,0,0.3);
}

.kds-card-header {
    padding: 10px 14px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    border-bottom: 2px solid rgba(255,255,255,0.06);
}

.kds-mesa {
    font-size: 18px;
    font-weight: 800;
    color: #F8FAFC;
    margin: 0;
    line-height: 1.1;
}

.kds-ronda {
    font-size: 11px;
    color: #94A3B8;
    font-weight: 600;
}

.kds-badge-sucursal {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #0F172A;
    background: #14B8A6;
    padding: 2px 8px;
    border-radius: 999px;
}

.kds-tiempo {
    font-size: 15px;
    font-weight: 800;
    white-space: nowrap;
}

.kds-items {
    padding: 10px 14px;
    flex: 1;
}

.kds-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #1F2937;
}

.kds-item:last-child {
    border-bottom: none;
}

.kds-item-cant {
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    background: #1F2937;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 800;
    color: #F8FAFC;
}

.kds-item-nombre {
    font-size: 14px;
    font-weight: 700;
    color: #F1F5F9;
    margin: 0;
    line-height: 1.3;
}

.kds-item-nota-variante {
    font-size: 12px;
    color: #FBBF24;
    background: #2E2408;
    border: 1px solid #92400E;
    border-radius: 6px;
    padding: 3px 8px;
    margin: 3px 0 0;
    font-weight: 700;
    display: inline-block;
}

.kds-item-mods {
    font-size: 12px;
    color: #FCA5A5;
    margin: 2px 0 0;
    font-weight: 700;
}

.kds-item-nota {
    font-size: 12px;
    color: #FBBF24;
    margin: 2px 0 0;
    font-weight: 600;
}

.kds-item-estado {
    font-size: 15px;
    flex-shrink: 0;
}

.kds-nota-pedido {
    margin-top: 8px;
    padding: 8px 10px;
    background: #2E2408;
    border: 1px solid #92400E;
    border-radius: 8px;
    font-size: 12px;
    color: #FBBF24;
    font-weight: 600;
}

.kds-btn-listo {
    width: 100%;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #22C55E, #15803D);
    color: white;
    border: none;
    font-size: 16px;
    font-weight: 800;
    cursor: pointer;
    letter-spacing: 1px;
    transition: transform 0.1s;
}

.kds-btn-listo:active {
    transform: scale(0.97);
}
</style>
