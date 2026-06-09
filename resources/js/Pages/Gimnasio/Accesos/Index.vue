<template>
    <AppLayout title="Control de Accesos">
        <div style="padding:24px; font-family:'Inter',sans-serif; max-width:1200px; background:#F1F5F9; min-height:100vh;">

            <!-- Header -->
            <div style="margin-bottom:24px;">
                <h1 style="font-size:28px; font-weight:900; color:#1E293B; margin:0;">🚪 Control de Accesos</h1>
                <p style="font-size:13px; color:#64748B; margin:4px 0 0;">{{ new Date().toLocaleDateString('es-PE', {weekday:'long', day:'numeric', month:'long'}) }}</p>
            </div>

            <!-- Buscador principal — grande y llamativo -->
            <div style="background:linear-gradient(135deg,#6D28D9,#4C1D95); border-radius:20px; padding:28px; margin-bottom:24px; box-shadow:0 8px 30px rgba(109,40,217,0.4);">
                <div style="font-size:16px; font-weight:700; color:#E9D5FF; margin-bottom:14px; text-align:center;">
                    💪 ¿Quién entra al gym hoy?
                </div>
                <div style="position:relative; max-width:600px; margin:0 auto;">
                    <input v-model="busqueda" @input="buscar" type="text"
                        placeholder="Buscar miembro por nombre o DNI..."
                        style="width:100%; padding:16px 20px; border:none; border-radius:14px; font-size:16px; font-weight:500; outline:none; box-sizing:border-box; background:#fff; box-shadow:0 4px 20px rgba(0,0,0,0.15);" />
                    <span style="position:absolute; right:16px; top:50%; transform:translateY(-50%); font-size:20px;">🔍</span>

                    <!-- Dropdown resultados -->
                    <div v-if="resultados.length" style="position:absolute; top:calc(100% + 8px); left:0; right:0; background:#fff; border-radius:14px; box-shadow:0 8px 30px rgba(0,0,0,0.2); z-index:99; overflow:hidden;">
                        <div v-for="m in resultados" :key="m.id" @click="seleccionar(m)"
                            style="padding:14px 18px; cursor:pointer; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #F1F5F9; transition:background 0.15s;"
                            @mouseover="e => e.currentTarget.style.background='#F5F3FF'"
                            @mouseleave="e => e.currentTarget.style.background='#fff'">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:40px; height:40px; background:linear-gradient(135deg,#6D28D9,#7C3AED); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; font-size:16px; flex-shrink:0;">
                                    {{ m.nombre_completo.charAt(0) }}
                                </div>
                                <div>
                                    <div style="font-size:14px; font-weight:700; color:#1E293B;">{{ m.nombre_completo }}</div>
                                    <div style="font-size:12px; color:#64748B;">{{ m.plan?.nombre || 'Sin plan' }} · {{ m.dni }}</div>
                                </div>
                            </div>
                            <span :style="{background: m.estado==='activo' ? '#DCFCE7' : '#FEE2E2', color: m.estado==='activo' ? '#16A34A' : '#DC2626', padding:'4px 12px', borderRadius:'20px', fontSize:'12px', fontWeight:'700'}">
                                {{ m.estado === 'activo' ? '✅ Activo' : '❌ Vencido' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Miembro seleccionado -->
                <div v-if="miembroSeleccionado" style="max-width:600px; margin:16px auto 0; background:rgba(255,255,255,0.15); border-radius:14px; padding:16px; display:flex; justify-content:space-between; align-items:center; backdrop-filter:blur(10px);">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div style="width:48px; height:48px; background:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:20px; color:#6D28D9; flex-shrink:0;">
                            {{ miembroSeleccionado.nombre_completo.charAt(0) }}
                        </div>
                        <div>
                            <div style="font-size:16px; font-weight:800; color:#fff;">{{ miembroSeleccionado.nombre_completo }}</div>
                            <div style="font-size:13px; color:#DDD6FE;">{{ miembroSeleccionado.plan?.nombre }} · {{ Math.ceil(miembroSeleccionado.dias_restantes) }} días restantes</div>
                        </div>
                    </div>
                    <button @click="registrarEntrada"
                        style="background:#fff; color:#6D28D9; border:none; padding:12px 24px; border-radius:12px; font-weight:800; font-size:15px; cursor:pointer; box-shadow:0 4px 15px rgba(0,0,0,0.2); white-space:nowrap;">
                        ✅ Registrar entrada
                    </button>
                </div>
            </div>

            <!-- Stats rápidas -->
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:24px;">
                <div style="background:#fff; border-radius:16px; padding:18px; box-shadow:0 2px 8px rgba(0,0,0,0.06); text-align:center; border-top:4px solid #6D28D9;">
                    <div style="font-size:36px; font-weight:900; color:#6D28D9;">{{ dentro_ahora.length }}</div>
                    <div style="font-size:13px; font-weight:700; color:#64748B; margin-top:4px;">Dentro ahora</div>
                    <div style="display:flex; justify-content:center; gap:4px; margin-top:8px;">
                        <span v-for="i in Math.min(dentro_ahora.length, 5)" :key="i"
                            style="width:8px; height:8px; background:#6D28D9; border-radius:50%; animation:pulse 1.5s infinite;"></span>
                    </div>
                </div>
                <div style="background:#fff; border-radius:16px; padding:18px; box-shadow:0 2px 8px rgba(0,0,0,0.06); text-align:center; border-top:4px solid #0EA5E9;">
                    <div style="font-size:36px; font-weight:900; color:#0EA5E9;">{{ accesos_hoy.length }}</div>
                    <div style="font-size:13px; font-weight:700; color:#64748B; margin-top:4px;">Accesos hoy</div>
                    <div style="font-size:11px; color:#94A3B8; margin-top:4px;">total del día</div>
                </div>
                <div style="background:#fff; border-radius:16px; padding:18px; box-shadow:0 2px 8px rgba(0,0,0,0.06); text-align:center; border-top:4px solid #10B981;">
                    <div style="font-size:36px; font-weight:900; color:#10B981;">{{ accesos_hoy.length - dentro_ahora.length }}</div>
                    <div style="font-size:13px; font-weight:700; color:#64748B; margin-top:4px;">Ya salieron</div>
                    <div style="font-size:11px; color:#94A3B8; margin-top:4px;">completaron visita</div>
                </div>
            </div>

            <!-- Dentro ahora + Historial -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

                <!-- Dentro ahora -->
                <div style="background:#fff; border-radius:16px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px;">
                        <div style="width:10px; height:10px; background:#16A34A; border-radius:50%; box-shadow:0 0 0 3px #DCFCE7;"></div>
                        <h3 style="font-size:15px; font-weight:800; color:#1E293B; margin:0;">En el gimnasio ahora</h3>
                        <span style="background:linear-gradient(135deg,#6D28D9,#7C3AED); color:#fff; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700; margin-left:auto;">{{ dentro_ahora.length }}</span>
                    </div>
                    <div v-if="dentro_ahora.length === 0" style="text-align:center; padding:30px 0; color:#94A3B8;">
                        <div style="font-size:40px; margin-bottom:8px;">🏋️</div>
                        <div style="font-size:13px;">Nadie dentro aún</div>
                    </div>
                    <div v-for="a in dentro_ahora" :key="a.id"
                        style="display:flex; align-items:center; gap:12px; padding:12px; border-radius:12px; background:#F8FAFC; margin-bottom:8px;">
                        <div style="width:44px; height:44px; background:linear-gradient(135deg,#6D28D9,#7C3AED); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; font-size:18px; flex-shrink:0;">
                            {{ a.miembro?.nombre?.charAt(0) }}
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:14px; font-weight:700; color:#1E293B;">{{ a.miembro?.nombre }} {{ a.miembro?.apellidos }}</div>
                            <div style="font-size:12px; color:#64748B;">Entró: {{ formatHora(a.entrada) }}</div>
                        </div>
                        <button @click="registrarSalida(a)"
                            style="background:linear-gradient(135deg,#DC2626,#EF4444); color:#fff; border:none; padding:8px 16px; border-radius:10px; font-weight:700; font-size:12px; cursor:pointer; box-shadow:0 2px 8px rgba(220,38,38,0.3); white-space:nowrap;">
                            👋 Salida
                        </button>
                    </div>
                </div>

                <!-- Historial hoy -->
                <div style="background:#fff; border-radius:16px; padding:20px; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px;">
                        <h3 style="font-size:15px; font-weight:800; color:#1E293B; margin:0;">📋 Historial de hoy</h3>
                        <span style="background:#EFF6FF; color:#3B82F6; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700; margin-left:auto;">{{ accesos_hoy.length }}</span>
                    </div>
                    <div v-if="accesos_hoy.length === 0" style="text-align:center; padding:30px 0; color:#94A3B8;">
                        <div style="font-size:40px; margin-bottom:8px;">📭</div>
                        <div style="font-size:13px;">Sin accesos hoy</div>
                    </div>
                    <div style="max-height:420px; overflow-y:auto;">
                        <div v-for="a in accesos_hoy" :key="a.id"
                            style="display:flex; align-items:center; gap:10px; padding:10px; border-radius:10px; margin-bottom:6px;"
                            :style="{background: !a.salida ? '#F5F3FF' : '#F8FAFC'}">
                            <div :style="{width:'36px', height:'36px', borderRadius:'50%', background: !a.salida ? 'linear-gradient(135deg,#6D28D9,#7C3AED)' : '#E2E8F0', display:'flex', alignItems:'center', justifyContent:'center', color: !a.salida ? '#fff' : '#64748B', fontWeight:'800', fontSize:'14px', flexShrink:0}">
                                {{ a.miembro?.nombre?.charAt(0) }}
                            </div>
                            <div style="flex:1; min-width:0;">
                                <div style="font-size:13px; font-weight:700; color:#1E293B; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ a.miembro?.nombre }} {{ a.miembro?.apellidos }}</div>
                                <div style="font-size:11px; color:#64748B;">
                                    {{ formatHora(a.entrada) }}
                                    <span v-if="a.salida"> → {{ formatHora(a.salida) }} · {{ duracion(a.entrada, a.salida) }}</span>
                                </div>
                            </div>
                            <span v-if="!a.salida" style="background:#EDE9FE; color:#6D28D9; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; white-space:nowrap;">Dentro</span>
                            <span v-else style="background:#F0FDF4; color:#16A34A; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; white-space:nowrap;">✓ Salió</span>
                        </div>
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

const props = defineProps({ accesos_hoy: Array, dentro_ahora: Array })

const busqueda = ref('')
const resultados = ref([])
const miembroSeleccionado = ref(null)
let timer = null

const buscar = () => {
    clearTimeout(timer)
    if (busqueda.value.length < 2) { resultados.value = []; return }
    timer = setTimeout(async () => {
        try {
            const res = await fetch('/gimnasio/accesos/buscar?q=' + encodeURIComponent(busqueda.value), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            resultados.value = await res.json()
        } catch(e) {
            resultados.value = []
        }
    }, 300)
}

const seleccionar = (m) => {
    miembroSeleccionado.value = m
    busqueda.value = m.nombre_completo
    resultados.value = []
}

const registrarEntrada = () => {
    if (!miembroSeleccionado.value) return
    router.post('/gimnasio/accesos/entrada', { miembro_id: miembroSeleccionado.value.id }, {
        onSuccess: () => { miembroSeleccionado.value = null; busqueda.value = '' }
    })
}

const registrarSalida = (acceso) => {
    router.post('/gimnasio/accesos/' + acceso.id + '/salida')
}

const formatHora = (dt) => dt ? new Date(dt).toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' }) : '-'
const duracion = (entrada, salida) => {
    const mins = Math.round((new Date(salida) - new Date(entrada)) / 60000)
    return mins >= 60 ? Math.floor(mins/60) + 'h ' + (mins%60) + 'min' : mins + ' min'
}
</script>
