<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    sucursales: Array,
})

const tabActiva = ref('todos')

const sucursalesVisibles = computed(() => {
    if (tabActiva.value === 'todos') return props.sucursales
    return props.sucursales.filter(s => String(s.id) === tabActiva.value)
})

const totales = computed(() => ({
    mesas_ocupadas: props.sucursales.reduce((sum, s) => sum + s.mesas_ocupadas, 0),
    pedidos_pendientes: props.sucursales.reduce((sum, s) => sum + s.pedidos_pendientes, 0),
    ventas_dia: props.sucursales.reduce((sum, s) => sum + s.ventas_dia, 0),
}))

const formatearMonto = (n) => `S/ ${Number(n).toFixed(2)}`

const mostrarForm = ref(false)
const editando = ref(null)

const form = useForm({
    nombre: '',
    direccion: '',
    telefono: '',
})

function nuevaSucursal() {
    editando.value = null
    form.reset()
    mostrarForm.value = true
}

function editarSucursal(sucursal) {
    editando.value = sucursal
    form.nombre = sucursal.nombre
    form.direccion = sucursal.direccion ?? ''
    form.telefono = sucursal.telefono ?? ''
    mostrarForm.value = true
}

function guardar() {
    if (editando.value) {
        form.put(`/admin/sucursales/${editando.value.id}`, {
            onSuccess: () => { mostrarForm.value = false },
        })
    } else {
        form.post('/admin/sucursales', {
            onSuccess: () => { mostrarForm.value = false },
        })
    }
}

function toggleActivo(sucursal) {
    router.put(`/admin/sucursales/${sucursal.id}`, {
        nombre: sucursal.nombre,
        direccion: sucursal.direccion,
        telefono: sucursal.telefono,
        activo: !sucursal.activo,
    })
}

function eliminar(sucursal) {
    if (!confirm(`¿Eliminar ${sucursal.nombre}? Esta acción no se puede deshacer.`)) return
    router.delete(`/admin/sucursales/${sucursal.id}`)
}
</script>

<template>
    <AppLayout title="🏬 Sucursales">
        <div style="max-width:1200px; margin:0 auto;">

            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:32px; font-weight:800; color:#1E293B; margin:0;">🏬 Sucursales</h1>
                    <p style="font-size:16px; color:#94A3B8; margin:4px 0 0;">Panel consolidado por sucursal</p>
                </div>
                <button
                    @click="nuevaSucursal"
                    style="background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:14px; padding:14px 24px; font-size:16px; font-weight:700; cursor:pointer; box-shadow:0 4px 15px rgba(20,184,166,0.3);"
                >
                    + Nueva sucursal
                </button>
            </div>

            <!-- Tabs -->
            <div style="display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap;">
                <button
                    @click="tabActiva = 'todos'"
                    :style="{
                        padding: '10px 20px', borderRadius: '12px', border: 'none', fontWeight: '700', fontSize: '15px', cursor: 'pointer',
                        background: tabActiva === 'todos' ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : '#F1F5F9',
                        color: tabActiva === 'todos' ? 'white' : '#64748B',
                    }"
                >
                    Todos
                </button>
                <button
                    v-for="s in sucursales"
                    :key="s.id"
                    @click="tabActiva = String(s.id)"
                    :style="{
                        padding: '10px 20px', borderRadius: '12px', border: 'none', fontWeight: '700', fontSize: '15px', cursor: 'pointer',
                        background: tabActiva === String(s.id) ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : '#F1F5F9',
                        color: tabActiva === String(s.id) ? 'white' : '#64748B',
                    }"
                >
                    {{ s.nombre }}
                </button>
            </div>

            <!-- Resumen (solo en tab "Todos") -->
            <div v-if="tabActiva === 'todos'" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:24px;">
                <div style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0;">
                    <p style="font-size:14px; color:#94A3B8; margin:0 0 6px; font-weight:600;">🪑 Mesas ocupadas (total)</p>
                    <p style="font-size:28px; font-weight:800; color:#1E293B; margin:0;">{{ totales.mesas_ocupadas }}</p>
                </div>
                <div style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0;">
                    <p style="font-size:14px; color:#94A3B8; margin:0 0 6px; font-weight:600;">⏳ Pedidos pendientes (total)</p>
                    <p style="font-size:28px; font-weight:800; color:#1E293B; margin:0;">{{ totales.pedidos_pendientes }}</p>
                </div>
                <div style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0;">
                    <p style="font-size:14px; color:#94A3B8; margin:0 0 6px; font-weight:600;">💰 Ventas del día (total)</p>
                    <p style="font-size:28px; font-weight:800; color:#166534; margin:0;">{{ formatearMonto(totales.ventas_dia) }}</p>
                </div>
            </div>

            <!-- Tarjetas por sucursal -->
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:16px;">
                <div
                    v-for="s in sucursalesVisibles"
                    :key="s.id"
                    style="background:white; border-radius:20px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08); border:2px solid #E2E8F0;"
                >
                    <div style="padding:18px 20px; display:flex; align-items:center; justify-content:space-between; border-bottom:2px solid #F1F5F9;">
                        <div>
                            <p style="font-size:20px; font-weight:800; color:#1E293B; margin:0;">{{ s.nombre }}</p>
                            <p v-if="s.direccion" style="font-size:13px; color:#94A3B8; margin:4px 0 0;">{{ s.direccion }}</p>
                        </div>
                        <span :style="{
                            padding: '4px 10px', borderRadius: '8px', fontSize: '12px', fontWeight: '700',
                            background: s.activo ? '#DCFCE7' : '#FEE2E2',
                            color: s.activo ? '#166534' : '#991B1B',
                        }">
                            {{ s.activo ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>

                    <div style="padding:20px; display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
                        <div>
                            <p style="font-size:12px; color:#94A3B8; margin:0 0 4px; font-weight:600;">Mesas ocup.</p>
                            <p style="font-size:20px; font-weight:800; color:#1E293B; margin:0;">{{ s.mesas_ocupadas }}</p>
                        </div>
                        <div>
                            <p style="font-size:12px; color:#94A3B8; margin:0 0 4px; font-weight:600;">Pendientes</p>
                            <p style="font-size:20px; font-weight:800; color:#1E293B; margin:0;">{{ s.pedidos_pendientes }}</p>
                        </div>
                        <div>
                            <p style="font-size:12px; color:#94A3B8; margin:0 0 4px; font-weight:600;">Ventas hoy</p>
                            <p style="font-size:18px; font-weight:800; color:#166534; margin:0;">{{ formatearMonto(s.ventas_dia) }}</p>
                        </div>
                    </div>

                    <div style="padding:14px 20px; border-top:1px solid #F1F5F9; display:flex; gap:8px;">
                        <button @click="editarSucursal(s)" style="flex:1; padding:10px; background:#F1F5F9; color:#1E293B; border:none; border-radius:10px; font-weight:700; cursor:pointer;">
                            Editar
                        </button>
                        <button @click="toggleActivo(s)" style="flex:1; padding:10px; background:#F1F5F9; color:#1E293B; border:none; border-radius:10px; font-weight:700; cursor:pointer;">
                            {{ s.activo ? 'Desactivar' : 'Activar' }}
                        </button>
                        <button @click="eliminar(s)" style="padding:10px 14px; background:#FEF2F2; color:#991B1B; border:none; border-radius:10px; font-weight:700; cursor:pointer;">
                            🗑
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form crear/editar -->
            <div v-if="mostrarForm" style="position:fixed; inset:0; background:rgba(15,23,42,0.5); display:flex; align-items:center; justify-content:center; z-index:50;" @click.self="mostrarForm = false">
                <div style="background:white; border-radius:20px; padding:28px; width:400px; max-width:90vw;">
                    <p style="font-size:20px; font-weight:800; color:#1E293B; margin:0 0 20px;">
                        {{ editando ? 'Editar sucursal' : 'Nueva sucursal' }}
                    </p>

                    <div style="margin-bottom:14px;">
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Nombre</label>
                        <input v-model="form.nombre" type="text" style="width:100%; padding:10px 14px; border:1px solid #E2E8F0; border-radius:10px; margin-top:4px;" />
                        <p v-if="form.errors.nombre" style="color:#EF4444; font-size:13px; margin:4px 0 0;">{{ form.errors.nombre }}</p>
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Dirección</label>
                        <input v-model="form.direccion" type="text" style="width:100%; padding:10px 14px; border:1px solid #E2E8F0; border-radius:10px; margin-top:4px;" />
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Teléfono</label>
                        <input v-model="form.telefono" type="text" style="width:100%; padding:10px 14px; border:1px solid #E2E8F0; border-radius:10px; margin-top:4px;" />
                    </div>

                    <div style="display:flex; gap:10px;">
                        <button @click="mostrarForm = false" style="flex:1; padding:12px; background:#F1F5F9; color:#1E293B; border:none; border-radius:12px; font-weight:700; cursor:pointer;">
                            Cancelar
                        </button>
                        <button @click="guardar" :disabled="form.processing" style="flex:1; padding:12px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; font-weight:700; cursor:pointer;">
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
