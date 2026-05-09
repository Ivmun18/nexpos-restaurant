<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

let pollingInterval = null

onMounted(() => {
    pollingInterval = setInterval(() => {
        router.reload({ only: ['mesas', 'turno'] })
    }, 10000) // cada 10 segundos
})

onUnmounted(() => {
    clearInterval(pollingInterval)
})

const props = defineProps({
    turnoActivo: Object,
    turnos:      Array,
})

const modalAbrir = ref(false)

const form = useForm({
    tipo:   'personalizado',
    nombre: '',
    notas:  '',
})

const tiposturno = [
    { key: 'mañana',       label: 'Mañana',       icon: '🌅', hora: '6:00 - 14:00' },
    { key: 'tarde',        label: 'Tarde',         icon: '☀️', hora: '14:00 - 22:00' },
    { key: 'noche',        label: 'Noche',         icon: '🌙', hora: '22:00 - 6:00' },
    { key: 'personalizado',label: 'Personalizado', icon: '⚙️', hora: 'Configurable' },
]

function abrirTurno() {
    form.post('/turnos/abrir', {
        onSuccess: () => { modalAbrir.value = false }
    })
}

function cerrarTurno(turno) {
    if (confirm('¿Cerrar tu turno actual? Se calculará el resumen de ventas.')) {
        router.post(`/turnos/${turno.id}/cerrar`)
    }
}

const duracion = (apertura) => {
    const diff = Math.floor((new Date() - new Date(apertura)) / 1000 / 60)
    const h = Math.floor(diff / 60)
    const m = diff % 60
    return h > 0 ? `${h}h ${m}min` : `${m}min`
}

const formatFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString('es-PE', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    })
}
</script>

<template>
    <AppLayout title="Turnos">
        <div style="max-width:1000px; margin:0 auto;">

            <!-- Header -->
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:28px; font-weight:800; color:#1E293B; margin:0;">⏰ Turnos</h1>
                    <p style="font-size:15px; color:#94A3B8; margin:4px 0 0;">Gestión de turnos por mozo</p>
                </div>
                <button
                    v-if="!turnoActivo"
                    @click="modalAbrir = true"
                    style="background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:14px; padding:14px 24px; font-size:16px; font-weight:700; cursor:pointer; box-shadow:0 4px 15px rgba(20,184,166,0.3);"
                >
                    ▶️ Abrir turno
                </button>
            </div>

            <!-- Turno activo -->
            <div v-if="turnoActivo"
                style="background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:20px; padding:28px; margin-bottom:24px; color:white; box-shadow:0 8px 24px rgba(20,184,166,0.3);">
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <p style="font-size:14px; opacity:0.8; margin:0 0 8px; text-transform:uppercase; letter-spacing:1px;">Turno activo</p>
                        <p style="font-size:32px; font-weight:800; margin:0;">
                            {{ turnoActivo.tipo === 'personalizado' ? turnoActivo.nombre || 'Turno personalizado' : turnoActivo.tipo }}
                        </p>
                        <p style="font-size:16px; opacity:0.8; margin:8px 0 0;">
                            ⏱ {{ duracion(turnoActivo.apertura) }} · Desde {{ formatFecha(turnoActivo.apertura) }}
                        </p>
                    </div>
                    <button
                        @click="cerrarTurno(turnoActivo)"
                        style="background:rgba(255,255,255,0.2); color:white; border:2px solid rgba(255,255,255,0.4); border-radius:14px; padding:16px 24px; font-size:16px; font-weight:700; cursor:pointer;"
                    >
                        ⏹ Cerrar turno
                    </button>
                </div>
            </div>

            <!-- Sin turno activo -->
            <div v-else
                style="background:white; border-radius:20px; padding:40px; text-align:center; margin-bottom:24px; border:2px dashed #E2E8F0;">
                <p style="font-size:48px; margin:0 0 12px;">⏰</p>
                <p style="font-size:20px; font-weight:700; color:#1E293B; margin:0 0 8px;">Sin turno activo</p>
                <p style="font-size:15px; color:#94A3B8; margin:0 0 20px;">Abre un turno para comenzar a trabajar</p>
                <button
                    @click="modalAbrir = true"
                    style="background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:14px; padding:14px 28px; font-size:16px; font-weight:700; cursor:pointer;"
                >
                    ▶️ Abrir turno
                </button>
            </div>

            <!-- Historial de turnos -->
            <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 20px;">📋 Historial de turnos</p>

                <div v-if="!turnos.length" style="text-align:center; padding:40px 0; color:#CBD5E1;">
                    <p style="font-size:32px; margin:0 0 8px;">📭</p>
                    <p style="font-size:16px;">Sin turnos registrados</p>
                </div>

                <div v-for="turno in turnos" :key="turno.id"
                    style="display:flex; align-items:center; justify-content:space-between; padding:16px 0; border-bottom:1px solid #F1F5F9;">
                    <div style="display:flex; align-items:center; gap:16px;">
                        <div :style="{
                            width: '48px',
                            height: '48px',
                            borderRadius: '14px',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            fontSize: '22px',
                            background: turno.estado === 'abierto' ? '#F0FDFA' : '#F1F5F9',
                        }">
                            {{ turno.estado === 'abierto' ? '🟢' : '⭕' }}
                        </div>
                        <div>
                            <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">
                                {{ turno.tipo === 'personalizado' ? turno.nombre || 'Personalizado' : turno.tipo }}
                                <span :style="{
                                    fontSize: '12px',
                                    padding: '2px 10px',
                                    borderRadius: '20px',
                                    fontWeight: '600',
                                    marginLeft: '8px',
                                    background: turno.estado === 'abierto' ? '#F0FDFA' : '#F1F5F9',
                                    color: turno.estado === 'abierto' ? '#0F766E' : '#64748B',
                                }">{{ turno.estado }}</span>
                            </p>
                            <p style="font-size:13px; color:#94A3B8; margin:4px 0 0;">
                                {{ turno.user?.name }} · {{ formatFecha(turno.apertura) }}
                            </p>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:16px;">
                        <div style="text-align:right;">
                            <p style="font-size:18px; font-weight:800; color:#14B8A6; margin:0;">S/ {{ Number(turno.total_ventas).toFixed(2) }}</p>
                            <p style="font-size:13px; color:#94A3B8; margin:4px 0 0;">{{ turno.total_mesas }} mesas</p>
                        </div>
                        <a :href="`/turnos/${turno.id}`"
                            style="background:#F1F5F9; color:#475569; border-radius:10px; padding:10px 16px; font-size:14px; font-weight:600; text-decoration:none;">
                            Ver →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal abrir turno -->
        <Teleport to="body">
            <div v-if="modalAbrir"
                style="position:fixed; inset:0; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:9999; padding:20px;"
                @click.self="modalAbrir=false">
                <div style="background:white; border-radius:24px; padding:32px; width:100%; max-width:480px; box-shadow:0 25px 60px rgba(0,0,0,0.25);">
                    <h2 style="font-size:22px; font-weight:800; color:#1E293B; margin:0 0 24px;">▶️ Abrir turno</h2>

                    <!-- Tipo de turno -->
                    <div style="margin-bottom:20px;">
                        <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:10px;">Tipo de turno</label>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <button
                                v-for="t in tiposturno"
                                :key="t.key"
                                @click="form.tipo = t.key"
                                :style="{
                                    padding: '14px',
                                    borderRadius: '12px',
                                    border: 'none',
                                    cursor: 'pointer',
                                    textAlign: 'left',
                                    background: form.tipo === t.key ? '#F0FDFA' : '#F8FAFC',
                                    outline: form.tipo === t.key ? '2px solid #14B8A6' : '2px solid transparent',
                                }"
                            >
                                <p style="font-size:20px; margin:0 0 4px;">{{ t.icon }}</p>
                                <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0;">{{ t.label }}</p>
                                <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ t.hora }}</p>
                            </button>
                        </div>
                    </div>

                    <!-- Nombre personalizado -->
                    <div v-if="form.tipo === 'personalizado'" style="margin-bottom:16px;">
                        <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Nombre del turno</label>
                        <input v-model="form.nombre" type="text" placeholder="Ej: Turno Ivan"
                            style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;" />
                    </div>

                    <!-- Notas -->
                    <div style="margin-bottom:24px;">
                        <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Notas (opcional)</label>
                        <input v-model="form.notas" type="text" placeholder="Ej: Turno completo"
                            style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;" />
                    </div>

                    <div style="display:flex; gap:12px;">
                        <button @click="modalAbrir=false"
                            style="flex:1; padding:14px; background:#F1F5F9; color:#475569; border:none; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer;">
                            Cancelar
                        </button>
                        <button @click="abrirTurno" :disabled="form.processing"
                            style="flex:1; padding:14px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; font-size:16px; font-weight:700; cursor:pointer;">
                            {{ form.processing ? 'Abriendo...' : '▶️ Abrir turno' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>