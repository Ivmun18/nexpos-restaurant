<template>
    <AppLayout title="Comandas" subtitle="Gestión de pedidos en cocina">
        
        <!-- Filtros -->
        <div style="background:white; border-radius:16px; padding:20px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
            <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                
                <!-- Filtro por mesa -->
                <div style="flex:1; min-width:180px;">
                    <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:6px;">
                        🪑 Filtrar por Mesa
                    </label>
                    <select v-model="filtros.mesa" @change="aplicarFiltros"
                        style="width:100%; padding:10px 14px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B; cursor:pointer;">
                        <option value="">Todas las mesas</option>
                        <option v-for="n in 12" :key="n" :value="n">Mesa {{ n }}</option>
                    </select>
                </div>

                <!-- Filtro por mozo -->
                <div style="flex:1; min-width:180px;">
                    <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:6px;">
                        👨‍🍳 Filtrar por Mozo
                    </label>
                    <select v-model="filtros.mozo" @change="aplicarFiltros"
                        style="width:100%; padding:10px 14px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B; cursor:pointer;">
                        <option value="">Todos los mozos</option>
                        <option v-for="mozo in mozos" :key="mozo.id" :value="mozo.id">{{ mozo.name }}</option>
                    </select>
                </div>

                <!-- Contador y auto-refresh -->
                <div style="flex:1; min-width:220px; display:flex; align-items:center; gap:12px;">
                    <div style="flex:1; padding:12px; background:#F8FAFC; border-radius:10px; text-align:center;">
                        <p style="font-size:11px; color:#64748B; margin:0;">TOTAL COMANDAS</p>
                        <p style="font-size:20px; font-weight:700; color:#14B8A6; margin:4px 0 0;">
                            {{ totalComandas }}
                        </p>
                    </div>
                    <button @click="refrescar" :disabled="cargando"
                        style="padding:12px 16px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:13px; font-weight:600; cursor:pointer; white-space:nowrap;">
                        🔄 {{ cargando ? 'Cargando...' : 'Refrescar' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Tablero Kanban -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(340px,1fr)); gap:20px;">
            
            <!-- Columna: PENDIENTE -->
            <div style="background:#FFFBEB; border-radius:16px; padding:20px; min-height:500px; border:2px solid #FDE68A;">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
                    <div style="width:48px; height:48px; background:linear-gradient(135deg,#F59E0B,#D97706); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:24px;">
                        📝
                    </div>
                    <div>
                        <h3 style="font-size:16px; font-weight:700; color:#92400E; margin:0;">PENDIENTE</h3>
                        <p style="font-size:12px; color:#B45309; margin:0;">{{ pendientes.length }} comandas</p>
                    </div>
                </div>

                <!-- Cards de comandas pendientes -->
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div v-for="comanda in pendientes" :key="comanda.id"
                        :style="cardStyle(comanda, '#FEF3C7')"
                        @click="verDetalle(comanda)">
                        
                        <!-- Header -->
                        <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:12px;">
                            <div>
                                <p style="font-size:18px; font-weight:700; color:#92400E; margin:0;">
                                    Mesa {{ comanda.mesa.numero }}
                                </p>
                                <p style="font-size:12px; color:#B45309; margin:2px 0 0;">
                                    {{ comanda.mozo }} • Ronda {{ comanda.numero_ronda }}
                                </p>
                            </div>
                            <span :style="tiempoStyle(comanda)">
                                ⏱️ {{ comanda.tiempo_transcurrido }}min
                            </span>
                        </div>

                        <!-- Productos -->
                        <div style="background:white; border-radius:8px; padding:12px; margin-bottom:10px;">
                            <div v-for="detalle in comanda.detalles" :key="detalle.id"
                                style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #FEF3C7;">
                                <div style="flex:1;">
                                    <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">
                                        {{ detalle.cantidad }}x {{ detalle.producto }}
                                    </p>
                                    <p v-if="detalle.notas" style="font-size:11px; color:#64748B; margin:2px 0 0; font-style:italic;">
                                        📝 {{ detalle.notas }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <button @click.stop="iniciarPreparacion(comanda)"
                            style="width:100%; padding:10px; background:linear-gradient(135deg,#F59E0B,#D97706); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                            🔥 Iniciar Preparación
                        </button>
                    </div>

                    <p v-if="pendientes.length === 0" style="text-align:center; color:#B45309; font-size:14px; padding:40px 20px;">
                        ✅ No hay comandas pendientes
                    </p>
                </div>
            </div>

            <!-- Columna: EN PREPARACIÓN -->
            <div style="background:#FEF2F2; border-radius:16px; padding:20px; min-height:500px; border:2px solid #FECACA;">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
                    <div style="width:48px; height:48px; background:linear-gradient(135deg,#EF4444,#DC2626); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:24px;">
                        🔥
                    </div>
                    <div>
                        <h3 style="font-size:16px; font-weight:700; color:#991B1B; margin:0;">EN PREPARACIÓN</h3>
                        <p style="font-size:12px; color:#B91C1C; margin:0;">{{ enPreparacion.length }} comandas</p>
                    </div>
                </div>

                <!-- Cards de comandas en preparación -->
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div v-for="comanda in enPreparacion" :key="comanda.id"
                        :style="cardStyle(comanda, '#FEE2E2')"
                        @click="verDetalle(comanda)">
                        
                        <!-- Header -->
                        <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:12px;">
                            <div>
                                <p style="font-size:18px; font-weight:700; color:#991B1B; margin:0;">
                                    Mesa {{ comanda.mesa.numero }}
                                </p>
                                <p style="font-size:12px; color:#B91C1C; margin:2px 0 0;">
                                    {{ comanda.mozo }} • Ronda {{ comanda.numero_ronda }}
                                </p>
                            </div>
                            <span :style="tiempoStyle(comanda)">
                                ⏱️ {{ comanda.tiempo_transcurrido }}min
                            </span>
                        </div>

                        <!-- Productos con checkboxes -->
                        <div style="background:white; border-radius:8px; padding:12px; margin-bottom:10px;">
                            <div v-for="detalle in comanda.detalles" :key="detalle.id"
                                style="display:flex; align-items:center; gap:10px; padding:6px 0; border-bottom:1px solid #FEE2E2;">
                                <input type="checkbox" 
                                    :checked="detalle.estado === 'listo'"
                                    @change="togglePlato(detalle, comanda)"
                                    style="width:18px; height:18px; cursor:pointer;">
                                <div style="flex:1;">
                                    <p :style="{fontSize:'14px', fontWeight:'600', color:'#1E293B', margin:'0', textDecoration: detalle.estado === 'listo' ? 'line-through' : 'none', opacity: detalle.estado === 'listo' ? '0.5' : '1'}">
                                        {{ detalle.cantidad }}x {{ detalle.producto }}
                                    </p>
                                    <p v-if="detalle.notas" style="font-size:11px; color:#64748B; margin:2px 0 0; font-style:italic;">
                                        📝 {{ detalle.notas }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <button @click.stop="marcarListo(comanda)"
                            style="width:100%; padding:10px; background:linear-gradient(135deg,#10B981,#059669); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                            ✅ Marcar Listo
                        </button>
                    </div>

                    <p v-if="enPreparacion.length === 0" style="text-align:center; color:#B91C1C; font-size:14px; padding:40px 20px;">
                        🔥 No hay comandas en preparación
                    </p>
                </div>
            </div>

            <!-- Columna: LISTO -->
            <div style="background:#F0FDF4; border-radius:16px; padding:20px; min-height:500px; border:2px solid #BBF7D0;">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
                    <div style="width:48px; height:48px; background:linear-gradient(135deg,#10B981,#059669); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:24px;">
                        ✅
                    </div>
                    <div>
                        <h3 style="font-size:16px; font-weight:700; color:#065F46; margin:0;">LISTO</h3>
                        <p style="font-size:12px; color:#047857; margin:0;">{{ listas.length }} comandas</p>
                    </div>
                </div>

                <!-- Cards de comandas listas -->
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div v-for="comanda in listas" :key="comanda.id"
                        :style="cardStyle(comanda, '#DCFCE7')"
                        @click="verDetalle(comanda)">
                        
                        <!-- Header -->
                        <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:12px;">
                            <div>
                                <p style="font-size:18px; font-weight:700; color:#065F46; margin:0;">
                                    Mesa {{ comanda.mesa.numero }}
                                </p>
                                <p style="font-size:12px; color:#047857; margin:2px 0 0;">
                                    {{ comanda.mozo }} • Ronda {{ comanda.numero_ronda }}
                                </p>
                            </div>
                            <span style="padding:4px 10px; background:#10B981; color:white; border-radius:6px; font-size:11px; font-weight:700;">
                                ✅ LISTO
                            </span>
                        </div>

                        <!-- Productos -->
                        <div style="background:white; border-radius:8px; padding:12px;">
                            <div v-for="detalle in comanda.detalles" :key="detalle.id"
                                style="display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #DCFCE7;">
                                <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">
                                    {{ detalle.cantidad }}x {{ detalle.producto }}
                                </p>
                                <span style="color:#10B981; font-size:16px;">✓</span>
                            </div>
                        </div>
                    </div>

                    <p v-if="listas.length === 0" style="text-align:center; color:#047857; font-size:14px; padding:40px 20px;">
                        ⏳ No hay comandas listas
                    </p>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    pendientes: Array,
    en_preparacion: Array,
    listas: Array,
    mozos: Array,
    filtros: Object,
})

const filtros = ref({
    mesa: props.filtros.mesa || '',
    mozo: props.filtros.mozo || '',
})

const pendientes = ref(props.pendientes)
const enPreparacion = ref(props.en_preparacion)
const listas = ref(props.listas)
const cargando = ref(false)

let pollingInterval = null

const totalComandas = computed(() => {
    return pendientes.value.length + enPreparacion.value.length + listas.value.length
})

const cardStyle = (comanda, bgColor) => {
    return {
        background: bgColor,
        borderRadius: '12px',
        padding: '16px',
        cursor: 'pointer',
        transition: 'all 0.2s',
        border: comanda.prioridad === 'alta' ? '2px solid #EF4444' : '1px solid rgba(0,0,0,0.1)',
        boxShadow: comanda.prioridad === 'alta' ? '0 4px 12px rgba(239, 68, 68, 0.3)' : '0 2px 6px rgba(0,0,0,0.08)',
    }
}

const tiempoStyle = (comanda) => {
    let bg = '#10B981'
    let color = 'white'
    
    if (comanda.prioridad === 'alta') {
        bg = '#EF4444'
    } else if (comanda.prioridad === 'media') {
        bg = '#F59E0B'
    }
    
    return {
        padding: '4px 10px',
        background: bg,
        color: color,
        borderRadius: '6px',
        fontSize: '11px',
        fontWeight: '700',
        whiteSpace: 'nowrap',
    }
}

const aplicarFiltros = () => {
    router.get('/comandas', filtros.value, {
        preserveState: true,
        preserveScroll: true,
    })
}

const refrescar = async () => {
    cargando.value = true
    
    try {
        const response = await fetch(`/comandas/polling?mesa=${filtros.value.mesa}&mozo=${filtros.value.mozo}`)
        const data = await response.json()
        
        pendientes.value = data.pendientes
        enPreparacion.value = data.en_preparacion
        listas.value = data.listas
    } catch (error) {
        console.error('Error al refrescar:', error)
    } finally {
        cargando.value = false
    }
}

const iniciarPreparacion = (comanda) => {
    router.post(`/comandas/${comanda.id}/estado`, {
        estado: 'en_preparacion'
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => setTimeout(() => refrescar(), 300)
    })
}

const marcarListo = (comanda) => {
    router.post(`/comandas/${comanda.id}/estado`, {
        estado: 'listo'
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => setTimeout(() => refrescar(), 300)
    })
}

const togglePlato = async (detalle, comanda) => {
    try {
        const response = await fetch(`/comandas/detalle/${detalle.id}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        
        const data = await response.json()
        
        if (data.success) {
            refrescar()
        }
    } catch (error) {
        console.error('Error al marcar plato:', error)
    }
}

const verDetalle = (comanda) => {
    console.log('Ver detalle de comanda:', comanda)
}

// Auto-refresh cada 10 segundos
onMounted(() => {
    pollingInterval = setInterval(() => {
        refrescar()
    }, 10000)
})

onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval)
    }
})
</script>
