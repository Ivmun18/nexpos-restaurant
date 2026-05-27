<template>
    <AppLayout>
        <div style="padding:24px; max-width:1100px; margin:0 auto;">
            <div style="margin-bottom:24px;">
                <h1 style="font-size:20px; font-weight:700; color:#1E293B; margin:0;">🚪 Control de Accesos</h1>
                <p style="color:#64748B; margin:4px 0 0;">Registro de entrada y salida de miembros</p>
            </div>

            <!-- Buscador de miembro -->
            <div style="background:white; border:1px solid #E2E8F0; border-radius:14px; padding:24px; margin-bottom:24px;">
                <p style="font-weight:700; color:#1E293B; margin:0 0 14px; font-size:15px;">Registrar entrada</p>
                <div style="display:flex; gap:12px; align-items:start; flex-wrap:wrap;">
                    <div style="flex:1; min-width:240px; position:relative;">
                        <input v-model="busqueda" @input="buscar" type="text"
                            placeholder="Buscar por nombre o DNI..."
                            style="width:100%; padding:12px 16px; border:1.5px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box;" />
                        <!-- Resultados -->
                        <div v-if="resultados.length" style="position:absolute; top:100%; left:0; right:0; background:white; border:1px solid #E2E8F0; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.1); z-index:99; margin-top:4px;">
                            <div v-for="m in resultados" :key="m.id" @click="seleccionar(m)"
                                style="padding:12px 16px; cursor:pointer; border-bottom:1px solid #F1F5F9; display:flex; justify-content:space-between; align-items:center;"
                                @mouseover="e => e.currentTarget.style.background='#F8FAFC'"
                                @mouseleave="e => e.currentTarget.style.background='white'">
                                <div>
                                    <p style="margin:0; font-weight:600; color:#1E293B;">{{ m.nombre_completo }}</p>
                                    <p style="margin:0; font-size:12px; color:#64748B;">{{ m.dni || 'Sin DNI' }} · {{ m.plan?.nombre || 'Sin plan' }}</p>
                                </div>
                                <span :style="{ background: m.estado==='activo' ? '#D1FAE5' : '#FEE2E2', color: m.estado==='activo' ? '#065F46' : '#991B1B', padding:'2px 10px', borderRadius:'20px', fontSize:'11px', fontWeight:'700' }">
                                    {{ m.estado }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="miembroSeleccionado" style="flex:1; min-width:240px; background:#F0FDF4; border:1px solid #BBF7D0; border-radius:10px; padding:14px;">
                        <p style="margin:0; font-weight:700; color:#166534;">{{ miembroSeleccionado.nombre_completo }}</p>
                        <p style="margin:4px 0 0; font-size:13px; color:#166534;">{{ miembroSeleccionado.plan?.nombre }} · {{ miembroSeleccionado.dias_restantes }} días restantes</p>
                    </div>
                    <button @click="registrarEntrada" :disabled="!miembroSeleccionado"
                        style="background:#166534; color:white; border:none; padding:12px 24px; border-radius:10px; font-weight:700; cursor:pointer; font-size:15px; white-space:nowrap;"
                        :style="{ opacity: miembroSeleccionado ? 1 : 0.5 }">
                        ✅ Registrar entrada
                    </button>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                <!-- Dentro ahora -->
                <div style="background:white; border:1px solid #E2E8F0; border-radius:14px; padding:20px;">
                    <p style="font-weight:700; color:#1E293B; margin:0 0 16px; font-size:15px;">
                        🏋️ Dentro ahora
                        <span style="background:#D1FAE5; color:#065F46; padding:2px 10px; border-radius:20px; font-size:12px; margin-left:8px;">{{ dentro_ahora.length }}</span>
                    </p>
                    <div v-for="a in dentro_ahora" :key="a.id" style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #F1F5F9;">
                        <div>
                            <p style="margin:0; font-weight:600; color:#1E293B; font-size:14px;">{{ a.miembro?.nombre }} {{ a.miembro?.apellidos }}</p>
                            <p style="margin:0; font-size:12px; color:#64748B;">Entró: {{ formatHora(a.entrada) }}</p>
                        </div>
                        <button @click="registrarSalida(a)" style="background:#FEE2E2; color:#991B1B; border:none; padding:6px 14px; border-radius:8px; cursor:pointer; font-weight:600; font-size:13px;">
                            👋 Salida
                        </button>
                    </div>
                    <p v-if="dentro_ahora.length === 0" style="color:#94A3B8; text-align:center; padding:20px 0; margin:0;">Nadie dentro ahora</p>
                </div>

                <!-- Historial hoy -->
                <div style="background:white; border:1px solid #E2E8F0; border-radius:14px; padding:20px;">
                    <p style="font-weight:700; color:#1E293B; margin:0 0 16px; font-size:15px;">
                        📋 Historial de hoy
                        <span style="background:#DBEAFE; color:#1D4ED8; padding:2px 10px; border-radius:20px; font-size:12px; margin-left:8px;">{{ accesos_hoy.length }}</span>
                    </p>
                    <div style="max-height:400px; overflow-y:auto;">
                        <div v-for="a in accesos_hoy" :key="a.id" style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #F1F5F9;">
                            <div>
                                <p style="margin:0; font-weight:600; color:#1E293B; font-size:14px;">{{ a.miembro?.nombre }} {{ a.miembro?.apellidos }}</p>
                                <p style="margin:0; font-size:12px; color:#64748B;">
                                    🟢 {{ formatHora(a.entrada) }}
                                    <span v-if="a.salida"> · 🔴 {{ formatHora(a.salida) }}</span>
                                    <span v-else style="color:#D97706;"> · aún dentro</span>
                                </p>
                            </div>
                            <span v-if="a.salida" style="background:#F1F5F9; color:#374151; padding:2px 10px; border-radius:20px; font-size:11px;">
                                {{ duracion(a.entrada, a.salida) }}
                            </span>
                        </div>
                        <p v-if="accesos_hoy.length === 0" style="color:#94A3B8; text-align:center; padding:20px 0; margin:0;">Sin accesos hoy</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({ accesos_hoy: Array, dentro_ahora: Array })

const busqueda = ref('')
const resultados = ref([])
const miembroSeleccionado = ref(null)
let timer = null

const buscar = () => {
    clearTimeout(timer)
    if (busqueda.value.length < 2) { resultados.value = []; return }
    timer = setTimeout(async () => {
        const res = await axios.get(route('gimnasio.accesos.buscar'), { params: { q: busqueda.value } })
        resultados.value = res.data
    }, 300)
}

const seleccionar = (m) => {
    miembroSeleccionado.value = m
    busqueda.value = m.nombre_completo
    resultados.value = []
}

const registrarEntrada = () => {
    if (!miembroSeleccionado.value) return
    router.post(route('gimnasio.accesos.entrada'), { miembro_id: miembroSeleccionado.value.id }, {
        onSuccess: () => { miembroSeleccionado.value = null; busqueda.value = '' }
    })
}

const registrarSalida = (acceso) => {
    router.post(route('gimnasio.accesos.salida', acceso.id))
}

const formatHora = (dt) => dt ? new Date(dt).toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' }) : '-'

const duracion = (entrada, salida) => {
    const mins = Math.round((new Date(salida) - new Date(entrada)) / 60000)
    return mins >= 60 ? Math.floor(mins/60) + 'h ' + (mins%60) + 'min' : mins + ' min'
}
</script>
