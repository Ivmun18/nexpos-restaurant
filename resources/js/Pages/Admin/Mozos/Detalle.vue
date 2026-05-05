<template>
    <AppLayout :title="`Mozo: ${mozo.name}`" subtitle="Métricas y asignación de mesas">
        <!-- Botón volver -->
        <a href="/admin/mozos" style="display:inline-flex; align-items:center; gap:8px; padding:10px 18px; background:white; border:2px solid #E2E8F0; border-radius:10px; text-decoration:none; font-size:14px; font-weight:600; color:#475569; margin-bottom:20px;">
            ← Volver al listado
        </a>

        <!-- Header del mozo -->
        <div style="background:white; border-radius:20px; border:2px solid #E2E8F0; padding:28px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between;">
            <div>
                <h1 style="font-size:26px; font-weight:800; color:#1E293B; margin:0 0 8px;">{{ mozo.name }}</h1>
                <p style="font-size:15px; color:#64748B; margin:0 0 4px;">📧 {{ mozo.email }}</p>
                <p style="font-size:14px; color:#94A3B8; margin:0;">
                    DNI: {{ mozo.dni || '—' }} · 📞 {{ mozo.telefono || '—' }} · Ingreso: {{ mozo.fecha_ingreso || '—' }}
                </p>
            </div>
            <span :style="{
                padding: '10px 20px',
                borderRadius: '20px',
                fontSize: '15px',
                fontWeight: '700',
                background: mozo.activo ? '#F0FDF4' : '#F1F5F9',
                color: mozo.activo ? '#166534' : '#64748B',
            }">
                {{ mozo.activo ? '✅ Activo' : '⏸ Inactivo' }}
            </span>
        </div>

        <!-- Métricas -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:24px;">
            <div style="background:white; border-radius:16px; border:2px solid #E2E8F0; padding:24px;">
                <p style="font-size:13px; color:#64748B; text-transform:uppercase; margin:0 0 8px;">Ventas hoy</p>
                <p style="font-size:32px; font-weight:800; color:#14B8A6; margin:0 0 6px;">{{ fmt(metricas.ventas_hoy) }}</p>
                <p style="font-size:14px; color:#94A3B8; margin:0;">{{ metricas.pedidos_hoy }} pedidos</p>
            </div>
            <div style="background:white; border-radius:16px; border:2px solid #E2E8F0; padding:24px;">
                <p style="font-size:13px; color:#64748B; text-transform:uppercase; margin:0 0 8px;">Ventas del mes</p>
                <p style="font-size:32px; font-weight:800; color:#1E293B; margin:0 0 6px;">{{ fmt(metricas.ventas_mes) }}</p>
                <p style="font-size:14px; color:#94A3B8; margin:0;">{{ metricas.pedidos_mes }} pedidos</p>
            </div>
            <div style="background:white; border-radius:16px; border:2px solid #E2E8F0; padding:24px;">
                <p style="font-size:13px; color:#64748B; text-transform:uppercase; margin:0 0 8px;">Ticket promedio</p>
                <p style="font-size:32px; font-weight:800; color:#1E293B; margin:0;">{{ fmt(metricas.ticket_promedio) }}</p>
            </div>
            <div style="background:white; border-radius:16px; border:2px solid #E2E8F0; padding:24px;">
                <p style="font-size:13px; color:#64748B; text-transform:uppercase; margin:0 0 8px;">Mesas / Turnos</p>
                <p style="font-size:32px; font-weight:800; color:#1E293B; margin:0;">{{ metricas.mesas_asignadas }}</p>
                <p style="font-size:14px; color:#94A3B8; margin:0;">{{ metricas.turnos_mes }} turnos este mes</p>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
            <!-- Ventas últimos 14 días -->
            <div style="background:white; border-radius:20px; border:2px solid #E2E8F0; padding:28px;">
                <h3 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 20px;">📊 Ventas últimos 14 días</h3>
                
                <div v-if="ventas_diarias.length === 0" style="text-align:center; padding:40px 0; color:#CBD5E1;">
                    <p style="font-size:40px; margin:0 0 12px;">📉</p>
                    <p style="font-size:16px; margin:0;">Sin datos</p>
                </div>

                <div v-else style="display:flex; flex-direction:column; gap:12px;">
                    <div v-for="v in ventas_diarias" :key="v.fecha" style="display:flex; align-items:center; gap:12px;">
                        <span style="font-size:13px; color:#64748B; min-width:80px; font-weight:600;">{{ v.fecha }}</span>
                        <div style="flex:1; background:#F1F5F9; border-radius:20px; height:36px; position:relative; overflow:hidden;">
                            <div
                                :style="{
                                    height: '100%',
                                    background: 'linear-gradient(135deg,#14B8A6,#0F766E)',
                                    borderRadius: '20px',
                                    width: ((Number(v.total) / maxVenta) * 100) + '%',
                                    transition: 'width 0.3s',
                                }"
                            ></div>
                            <span style="position:absolute; inset:0; display:flex; align-items:center; padding:0 16px; font-size:14px; font-weight:700; color:#1E293B;">
                                {{ fmt(v.total) }} <span style="margin-left:auto; font-size:12px; color:#64748B;">{{ v.pedidos }}p</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asignación de mesas -->
            <div style="background:white; border-radius:20px; border:2px solid #E2E8F0; padding:28px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3 style="font-size:18px; font-weight:700; color:#1E293B; margin:0;">🪑 Mesas asignadas</h3>
                    <button
                        @click="guardarMesas"
                        :disabled="guardando"
                        style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer;"
                    >
                        {{ guardando ? '⏳' : '💾' }} Guardar
                    </button>
                </div>

                <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:12px;">
                    <button
                        v-for="mesa in todas_mesas"
                        :key="mesa.id"
                        type="button"
                        @click="toggleMesa(mesa.id)"
                        :style="{
                            aspectRatio: '1',
                            borderRadius: '16px',
                            border: '3px solid',
                            borderColor: seleccionadas.includes(mesa.id) ? '#14B8A6' : '#E2E8F0',
                            background: seleccionadas.includes(mesa.id) ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : 'white',
                            color: seleccionadas.includes(mesa.id) ? 'white' : '#64748B',
                            fontSize: '22px',
                            fontWeight: '800',
                            cursor: 'pointer',
                            transition: 'all 0.2s',
                        }"
                    >
                        {{ mesa.numero }}
                    </button>
                </div>

                <p style="font-size:13px; color:#94A3B8; margin:16px 0 0; text-align:center;">
                    {{ seleccionadas.length }} de {{ todas_mesas.length }} mesas seleccionadas
                </p>
            </div>
        </div>

        <!-- Observaciones -->
        <div v-if="mozo.observaciones" style="background:white; border-radius:20px; border:2px solid #E2E8F0; padding:28px; margin-top:24px;">
            <h3 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 12px;">📝 Observaciones</h3>
            <p style="font-size:15px; color:#475569; line-height:1.6; margin:0; white-space:pre-line;">{{ mozo.observaciones }}</p>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    mozo: Object,
    metricas: Object,
    ventas_diarias: Array,
    todas_mesas: Array,
    mesas_asignadas_ids: Array,
});

const seleccionadas = ref([...props.mesas_asignadas_ids]);
const guardando = ref(false);

const maxVenta = computed(() => Math.max(...props.ventas_diarias.map(v => Number(v.total)), 1));

function toggleMesa(id) {
    const i = seleccionadas.value.indexOf(id);
    i === -1 ? seleccionadas.value.push(id) : seleccionadas.value.splice(i, 1);
}

function guardarMesas() {
    guardando.value = true;
    router.post(`/admin/mozos/${props.mozo.id}/mesas`,
        { mesa_ids: seleccionadas.value },
        {
            preserveScroll: true,
            onFinish: () => (guardando.value = false),
        }
    );
}

function fmt(n) {
    return new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' }).format(n || 0);
}
</script>
