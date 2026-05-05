<template>
    <AppLayout title="Mapa de mesas" subtitle="Estado en tiempo real">

        <!-- KPIs -->
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:1.5rem;">
            <div style="background:white; border-radius:12px; padding:18px; border:1px solid #E2E8F0; text-align:center;">
                <p style="font-size:13px; color:#94A3B8; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.5px;">Total mesas</p>
                <p style="font-size:32px; font-weight:700; color:#1E293B; margin:0;">{{ resumen.total }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:18px; border:1px solid #DCFCE7; text-align:center;">
                <p style="font-size:13px; color:#94A3B8; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.5px;">Libres</p>
                <p style="font-size:32px; font-weight:700; color:#10B981; margin:0;">{{ resumen.libres }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:18px; border:1px solid #FECACA; text-align:center;">
                <p style="font-size:13px; color:#94A3B8; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.5px;">Ocupadas</p>
                <p style="font-size:32px; font-weight:700; color:#EF4444; margin:0;">{{ resumen.ocupadas }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:18px; border:1px solid #FDE68A; text-align:center;">
                <p style="font-size:13px; color:#94A3B8; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.5px;">Reservadas</p>
                <p style="font-size:32px; font-weight:700; color:#F59E0B; margin:0;">{{ resumen.reservadas }}</p>
            </div>
        </div>

        <!-- Filtro por zona -->
        <div style="display:flex; gap:8px; margin-bottom:1.5rem; flex-wrap:wrap;">
            <button type="button" @click="zonaActiva='todas'" :style="zonaActiva==='todas' ? btnZonaActivo : btnZona">Todas</button>
            <button type="button" @click="zonaActiva='salon'" :style="zonaActiva==='salon' ? btnZonaActivo : btnZona">Salon</button>
            <button type="button" @click="zonaActiva='terraza'" :style="zonaActiva==='terraza' ? btnZonaActivo : btnZona">Terraza</button>
            <button type="button" @click="zonaActiva='barra'" :style="zonaActiva==='barra' ? btnZonaActivo : btnZona">Barra</button>
            <button type="button" @click="zonaActiva='privado'" :style="zonaActiva==='privado' ? btnZonaActivo : btnZona">Privado</button>
            <button type="button" @click="zonaActiva='delivery'" :style="zonaActiva==='delivery' ? btnZonaActivo : btnZona">Delivery</button>
            <button type="button" @click="abrirModalNueva"
                style="margin-left:auto; padding:12px 20px; background:#2563EB; color:white; border:none; border-radius:10px; font-size:15px; font-weight:600; cursor:pointer;">
                + Nueva mesa
            </button>
        </div>

        <!-- Mapa de mesas -->
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:16px; margin-bottom:2rem;">
            <div v-for="mesa in mesasFiltradas" :key="mesa.id"
                @click="abrirMesa(mesa)"
                :style="estiloMesa(mesa)"
                style="border-radius:16px; padding:20px; cursor:pointer; transition:transform 0.1s; user-select:none; min-height:140px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px;"
                @touchstart="$event.currentTarget.style.transform='scale(0.96)'"
                @touchend="$event.currentTarget.style.transform='scale(1)'"
                @mousedown="$event.currentTarget.style.transform='scale(0.96)'"
                @mouseup="$event.currentTarget.style.transform='scale(1)'">

                <svg width="36" height="36" fill="none" :stroke="mesa.estado === 'libre' ? '#10B981' : mesa.estado === 'ocupada' ? '#EF4444' : mesa.estado === 'reservada' ? '#F59E0B' : '#6B7280'" stroke-width="1.5" viewBox="0 0 24 24">
                    <rect x="2" y="7" width="20" height="10" rx="2"/>
                    <path d="M6 7V5M18 7V5M6 17v2M18 17v2"/>
                </svg>

                <p style="font-size:22px; font-weight:700; color:#1E293B; margin:0;">{{ mesa.nombre }}</p>
                <p style="font-size:13px; color:#64748B; margin:0;">{{ mesa.capacidad }} personas</p>

                <span :style="badgeEstado(mesa.estado)">{{ mesa.estado }}</span>
            </div>
        </div>

        <!-- Leyenda -->
        <div style="display:flex; gap:20px; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:16px; height:16px; border-radius:4px; background:#DCFCE7; border:2px solid #10B981;"></div>
                <span style="font-size:14px; color:#64748B;">Libre</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:16px; height:16px; border-radius:4px; background:#FEE2E2; border:2px solid #EF4444;"></div>
                <span style="font-size:14px; color:#64748B;">Ocupada</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:16px; height:16px; border-radius:4px; background:#FEF3C7; border:2px solid #F59E0B;"></div>
                <span style="font-size:14px; color:#64748B;">Reservada</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:16px; height:16px; border-radius:4px; background:#F1F5F9; border:2px solid #6B7280;"></div>
                <span style="font-size:14px; color:#64748B;">Bloqueada</span>
            </div>
        </div>

        <!-- Modal de mesa -->
        <div v-show="modalMesa" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; display:flex; align-items:center; justify-content:center;" @click.self="modalMesa=false">
            <div v-if="mesaSeleccionada" style="background:white; border-radius:20px; padding:2rem; width:100%; max-width:400px; box-shadow:0 20px 60px rgba(0,0,0,0.3);">

    <div style="text-align:center; margin-bottom:1.5rem;">
        <p style="font-size:24px; font-weight:700; color:#1E293B; margin:0;">{{ mesaSeleccionada.nombre }}</p>
        <p style="font-size:15px; color:#64748B; margin:4px 0 0;">{{ mesaSeleccionada.capacidad }} personas - {{ mesaSeleccionada.zona }}</p>
    </div>

    <!-- Botón tomar pedido -->
      <div style="display:flex; justify-content:center; margin-bottom:1rem;">
    <a :href="`/pos/${mesaSeleccionada.id}`"
        style="padding:18px 32px; background:#14B8A6; color:white; border-radius:12px; font-size:18px; font-weight:700; cursor:pointer; text-align:center; text-decoration:none;">
        🍽️ Tomar pedido
    </a>
    </div>


    <p style="font-size:14px; font-weight:600; color:#64748B; margin:0 0 12px; text-transform:uppercase; letter-spacing:0.5px;">Cambiar estado</p>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:1.5rem;">
        <button type="button" @click="cambiarEstado('libre')"
            style="padding:16px; background:#DCFCE7; color:#166534; border:2px solid #10B981; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer;">
            Libre
        </button>
        <button type="button" @click="cambiarEstado('ocupada')"
            style="padding:16px; background:#FEE2E2; color:#991B1B; border:2px solid #EF4444; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer;">
            Ocupada
        </button>
        <button type="button" @click="cambiarEstado('reservada')"
            style="padding:16px; background:#FEF3C7; color:#92400E; border:2px solid #F59E0B; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer;">
            Reservada
        </button>
        <button type="button" @click="cambiarEstado('bloqueada')"
            style="padding:16px; background:#F1F5F9; color:#475569; border:2px solid #6B7280; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer;">
            Bloqueada
        </button>
    </div>

    <button type="button" @click="modalMesa=false"
        style="width:100%; padding:14px; background:#F1F5F9; color:#64748B; border:none; border-radius:12px; font-size:16px; cursor:pointer;">
        Cancelar
    </button>
</div>
        </div>

        <!-- Modal nueva mesa -->
        <div v-show="modalNueva" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; display:flex; align-items:center; justify-content:center;" @click.self="modalNueva=false">
            <div style="background:white; border-radius:20px; padding:2rem; width:100%; max-width:400px;">
                <p style="font-size:20px; font-weight:700; color:#1E293B; margin:0 0 1.5rem;">Nueva mesa</p>

                <form @submit.prevent="guardarMesa">
                    <div style="margin-bottom:1rem;">
                        <label style="font-size:14px; color:#64748B; display:block; margin-bottom:6px;">Numero *</label>
                        <input v-model="formMesa.numero" type="text"
                            style="width:100%; padding:14px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;"/>
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label style="font-size:14px; color:#64748B; display:block; margin-bottom:6px;">Nombre</label>
                        <input v-model="formMesa.nombre" type="text" placeholder="Mesa 13"
                            style="width:100%; padding:14px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;"/>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
                        <div>
                            <label style="font-size:14px; color:#64748B; display:block; margin-bottom:6px;">Capacidad</label>
                            <input v-model="formMesa.capacidad" type="number" min="1" max="20"
                                style="width:100%; padding:14px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;"/>
                        </div>
                        <div>
                            <label style="font-size:14px; color:#64748B; display:block; margin-bottom:6px;">Zona</label>
                            <select v-model="formMesa.zona"
                                style="width:100%; padding:14px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; background:white; box-sizing:border-box;">
                                <option value="salon">Salon</option>
                                <option value="terraza">Terraza</option>
                                <option value="barra">Barra</option>
                                <option value="privado">Privado</option>
                                <option value="delivery">Delivery</option>
                            </select>
                        </div>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button type="button" @click="modalNueva=false"
                            style="flex:1; padding:14px; background:#F1F5F9; color:#64748B; border:none; border-radius:12px; font-size:16px; cursor:pointer;">
                            Cancelar
                        </button>
                        <button type="submit"
                            style="flex:1; padding:14px; background:#2563EB; color:white; border:none; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer;">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    mesas:   Array,
    resumen: Object,
})

const zonaActiva      = ref('todas')
const modalMesa       = ref(false)
const modalNueva      = ref(false)
const mesaSeleccionada= ref(null)

const formMesa = ref({ numero: '', nombre: '', capacidad: 4, zona: 'salon' })

const btnZona = { padding:'10px 18px', background:'white', color:'#64748B', border:'1px solid #E2E8F0', borderRadius:'10px', fontSize:'15px', cursor:'pointer', fontWeight:'400' }
const btnZonaActivo = { padding:'10px 18px', background:'#2563EB', color:'white', border:'none', borderRadius:'10px', fontSize:'15px', cursor:'pointer', fontWeight:'600' }

const mesasFiltradas = computed(() => {
    if (zonaActiva.value === 'todas') return props.mesas || []
    return (props.mesas || []).filter(m => m.zona === zonaActiva.value)
})

const estiloMesa = (mesa) => {
    const colores = {
        libre:     { background:'#F0FDF4', border:'2px solid #10B981' },
        ocupada:   { background:'#FEF2F2', border:'2px solid #EF4444' },
        reservada: { background:'#FFFBEB', border:'2px solid #F59E0B' },
        bloqueada: { background:'#F8FAFC', border:'2px solid #6B7280' },
    }
    return colores[mesa.estado] || colores.libre
}

const badgeEstado = (estado) => {
    const map = {
        libre:     { background:'#DCFCE7', color:'#166534' },
        ocupada:   { background:'#FEE2E2', color:'#991B1B' },
        reservada: { background:'#FEF3C7', color:'#92400E' },
        bloqueada: { background:'#F1F5F9', color:'#475569' },
    }
    return { ...(map[estado] || map.libre), fontSize:'13px', padding:'4px 12px', borderRadius:'20px', fontWeight:'600' }
}

const abrirMesa = (mesa) => {
    mesaSeleccionada.value = mesa
    modalMesa.value = true
}

const abrirModalNueva = () => {
    formMesa.value = { numero: '', nombre: '', capacidad: 4, zona: 'salon' }
    modalNueva.value = true
}

const cambiarEstado = (estado) => {
    router.post('/mesas/' + mesaSeleccionada.value.id + '/estado', { estado }, {
        onSuccess: () => { modalMesa.value = false; router.visit('/mesas') }
    })
}

const guardarMesa = () => {
    router.post('/mesas', formMesa.value, {
        onSuccess: () => { modalNueva.value = false; router.visit('/mesas') }
    })
}
</script>
