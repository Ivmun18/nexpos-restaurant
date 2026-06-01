<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ tareas: Array, habitaciones: Array })
const cargando = ref(null)

const actualizar = async (tarea, estado) => {
    cargando.value = tarea.id
    const res = await fetch('/hotel/housekeeping/' + tarea.id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ estado })
    })
    if (res.ok) router.reload()
    cargando.value = null
}

const estadoColor = (e) => ({ pendiente: '#DC2626', en_proceso: '#D97706', completado: '#16A34A' }[e] || '#6B7280')
</script>
<template>
    <AppLayout title="Housekeeping">
        <div style="padding:24px; font-family:'Inter',sans-serif;">
            <h1 style="font-size:24px; font-weight:700; color:#1E293B; margin:0 0 24px;">🧹 Housekeeping</h1>
            <div v-if="tareas.length === 0" style="background:#fff; border-radius:12px; padding:40px; text-align:center; color:#94A3B8; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                ✅ No hay tareas pendientes de limpieza
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px;">
                <div v-for="t in tareas" :key="t.id" style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <div style="font-size:20px; font-weight:700;">Hab. {{ t.habitacion?.numero }}</div>
                        <span :style="{background: estadoColor(t.estado)+'20', color: estadoColor(t.estado), padding:'3px 10px', borderRadius:'20px', fontSize:'11px', fontWeight:'600'}">{{ t.estado }}</span>
                    </div>
                    <div style="font-size:13px; color:#64748B; margin-bottom:4px;">{{ t.habitacion?.tipo?.nombre }}</div>
                    <div style="font-size:12px; color:#94A3B8; margin-bottom:16px;">{{ new Date(t.created_at).toLocaleString('es-PE') }}</div>
                    <div style="display:flex; gap:8px;">
                        <button v-if="t.estado === 'pendiente'" @click="actualizar(t, 'en_proceso')" :disabled="cargando === t.id"
                            style="flex:1; background:#FEF3C7; color:#D97706; border:1px solid #FDE68A; padding:8px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                            🧹 Iniciar
                        </button>
                        <button v-if="t.estado === 'en_proceso'" @click="actualizar(t, 'completado')" :disabled="cargando === t.id"
                            style="flex:1; background:#DCFCE7; color:#16A34A; border:1px solid #BBF7D0; padding:8px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                            ✅ Completar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
