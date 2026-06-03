<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ habitaciones: Array, tipos: Array })
const showForm = ref(false)
const editando = ref(null)
const form = ref({ tipo_id: '', numero: '', piso: 1, estado: 'disponible', observaciones: '' })

const abrir = (h = null) => {
    editando.value = h
    form.value = h ? { tipo_id: h.tipo_id, numero: h.numero, piso: h.piso, estado: h.estado, observaciones: h.observaciones } : { tipo_id: props.tipos?.[0]?.id ?? '', numero: '', piso: 1, estado: 'disponible', observaciones: '' }
    showForm.value = true
}

const errores = ref({})

const guardar = () => {
    errores.value = {}
    if (!form.value.tipo_id) { errores.value.tipo_id = 'Selecciona un tipo de habitación'; return }
    if (!form.value.numero) { errores.value.numero = 'Ingresa el número de habitación'; return }

    if (editando.value) {
        router.put('/hotel/habitaciones/' + editando.value.id, form.value, {
            onSuccess: () => { showForm.value = false },
            onError: (e) => { errores.value = e }
        })
    } else {
        router.post('/hotel/habitaciones', form.value, {
            onSuccess: () => { showForm.value = false },
            onError: (e) => { errores.value = e }
        })
    }
}

const eliminar = (id) => {
    if (!confirm('¿Eliminar habitación?')) return
    router.delete('/hotel/habitaciones/' + id)
}

const estadoColor = (e) => ({ disponible: '#16A34A', ocupada: '#DC2626', limpieza: '#D97706', mantenimiento: '#6B7280' }[e] || '#6B7280')
</script>
<template>
    <AppLayout title="Habitaciones">
        <div style="padding:24px; font-family:'Inter',sans-serif;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <h1 style="font-size:24px; font-weight:700; color:#1E293B; margin:0;">🛏️ Habitaciones</h1>
                <button @click="abrir()" style="background:#3B82F6; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer;">➕ Nueva Habitación</button>
            </div>
            <div style="background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.08); overflow:hidden;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead><tr style="background:#F8FAFC;">
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">N°</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">PISO</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">TIPO</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">PRECIO/NOCHE</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">ESTADO</th>
                        <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B; font-weight:600;">ACCIONES</th>
                    </tr></thead>
                    <tbody>
                        <tr v-if="habitaciones.length === 0"><td colspan="6" style="padding:40px; text-align:center; color:#94A3B8;">No hay habitaciones. Agrega una.</td></tr>
                        <tr v-for="h in habitaciones" :key="h.id" style="border-top:1px solid #F1F5F9;">
                            <td style="padding:12px 16px; font-weight:700; font-size:16px;">{{ h.numero }}</td>
                            <td style="padding:12px 16px; font-size:13px;">{{ h.piso }}</td>
                            <td style="padding:12px 16px; font-size:13px;">{{ h.tipo?.nombre }}</td>
                            <td style="padding:12px 16px; font-size:13px; font-weight:600;">S/ {{ Number(h.tipo?.precio_noche).toFixed(2) }}</td>
                            <td style="padding:12px 16px;">
                                <span :style="{background: estadoColor(h.estado)+'20', color: estadoColor(h.estado), padding:'3px 10px', borderRadius:'20px', fontSize:'11px', fontWeight:'600'}">{{ h.estado }}</span>
                            </td>
                            <td style="padding:12px 16px; display:flex; gap:8px;">
                                <button @click="abrir(h)" style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; padding:4px 12px; border-radius:6px; font-size:12px; cursor:pointer;">✏️ Editar</button>
                                <button @click="eliminar(h.id)" style="background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; padding:4px 12px; border-radius:6px; font-size:12px; cursor:pointer;">🗑️</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Modal -->
            <div v-if="showForm" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
                <div style="background:#fff; border-radius:16px; padding:28px; width:420px;">
                    <h2 style="font-size:18px; font-weight:700; margin:0 0 20px;">{{ editando ? '✏️ Editar' : '➕ Nueva' }} Habitación</h2>
                    <div style="display:grid; gap:12px;">
                        <div><label style="font-size:12px; font-weight:600;">Tipo</label>
                            <select v-model="form.tipo_id" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;">
                                <option value="">Seleccionar tipo</option>
                                <option v-for="t in tipos" :key="t.id" :value="t.id">{{ t.nombre }} — S/ {{ t.precio_noche }}/noche</option>
                            </select>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div><label style="font-size:12px; font-weight:600;">Número</label>
                                <input v-model="form.numero" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" placeholder="101" />
                            <p v-if="errores.numero" style="color:#DC2626; font-size:12px; margin:4px 0 0;">{{ errores.numero }}</p>
                            <p v-if="errores.tipo_id" style="color:#DC2626; font-size:12px; margin:4px 0 0;">{{ errores.tipo_id }}</p>
                            </div>
                            <div><label style="font-size:12px; font-weight:600;">Piso</label>
                                <input type="number" v-model="form.piso" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" />
                            </div>
                        </div>
                        <div><label style="font-size:12px; font-weight:600;">Estado</label>
                            <select v-model="form.estado" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;">
                                <option value="disponible">Disponible</option>
                                <option value="ocupada">Ocupada</option>
                                <option value="limpieza">Limpieza</option>
                                <option value="mantenimiento">Mantenimiento</option>
                            </select>
                        </div>
                        <div><label style="font-size:12px; font-weight:600;">Observaciones</label>
                            <textarea v-model="form.observaciones" rows="2" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;"></textarea>
                        </div>
                    </div>
                    <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
                        <button @click="showForm = false" style="background:#F1F5F9; color:#374151; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600;">Cancelar</button>
                        <button @click="guardar" style="background:#3B82F6; color:#fff; border:none; padding:10px 24px; border-radius:8px; cursor:pointer; font-weight:600;">✅ Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
