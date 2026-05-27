<template>
    <AppLayout>
        <div style="padding:24px; max-width:900px; margin:0 auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:20px; font-weight:700; color:#1E293B; margin:0;">🏋️ Instructores</h1>
                    <p style="color:#64748B; margin:4px 0 0;">{{ instructores.length }} registrados</p>
                </div>
                <button @click="showForm=true" style="background:#6D28D9; color:white; border:none; padding:10px 20px; border-radius:10px; font-weight:600; cursor:pointer;">+ Nuevo instructor</button>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px;">
                <div v-for="i in instructores" :key="i.id" style="background:white; border:1px solid #E2E8F0; border-radius:14px; padding:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:start;">
                        <div>
                            <p style="font-weight:700; color:#1E293B; margin:0; font-size:15px;">{{ i.nombre }} {{ i.apellidos }}</p>
                            <p style="color:#6D28D9; margin:4px 0 0; font-size:13px; font-weight:600;">{{ i.especialidad || 'Sin especialidad' }}</p>
                        </div>
                        <button @click="editar(i)" style="background:#F1F5F9; border:none; padding:6px 10px; border-radius:8px; cursor:pointer;">✏️</button>
                    </div>
                    <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
                        <span v-if="i.telefono" style="background:#F1F5F9; color:#374151; padding:4px 10px; border-radius:20px; font-size:12px;">📱 {{ i.telefono }}</span>
                        <span style="background:#EDE9FE; color:#6D28D9; padding:4px 10px; border-radius:20px; font-size:12px;">Clase: S/ {{ i.comision_clase }}</span>
                        <span style="background:#D1FAE5; color:#065F46; padding:4px 10px; border-radius:20px; font-size:12px;">PT: S/ {{ i.comision_pt }}</span>
                    </div>
                </div>
                <div v-if="instructores.length === 0" style="grid-column:1/-1; text-align:center; padding:40px; color:#94A3B8;">
                    No hay instructores registrados
                </div>
            </div>

            <!-- Modal -->
            <div v-if="showForm" style="position:fixed; inset:0; background:rgba(0,0,0,0.45); display:flex; align-items:center; justify-content:center; z-index:999;">
                <div style="background:white; border-radius:16px; padding:28px; width:460px; max-width:95vw;">
                    <h2 style="margin:0 0 20px; font-size:18px; font-weight:700;">{{ form.id ? 'Editar' : 'Nuevo' }} Instructor</h2>
                    <div style="display:grid; gap:14px;">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Nombre *</label>
                                <input v-model="form.nombre" type="text" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Apellidos *</label>
                                <input v-model="form.apellidos" type="text" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">DNI</label>
                                <input v-model="form.dni" type="text" maxlength="8" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Teléfono</label>
                                <input v-model="form.telefono" type="text" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                        </div>
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#374151;">Especialidad</label>
                            <input v-model="form.especialidad" type="text" placeholder="Yoga, Crossfit, Spinning..." style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Comisión por clase (S/)</label>
                                <input v-model="form.comision_clase" type="number" min="0" step="0.01" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Comisión PT (S/)</label>
                                <input v-model="form.comision_pt" type="number" min="0" step="0.01" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                        </div>
                    </div>
                    <div style="display:flex; gap:12px; margin-top:20px;">
                        <button @click="guardar" style="flex:1; background:#6D28D9; color:white; border:none; padding:12px; border-radius:10px; font-weight:700; cursor:pointer;">Guardar</button>
                        <button @click="cerrar" style="flex:1; background:#F1F5F9; border:none; padding:12px; border-radius:10px; font-weight:600; cursor:pointer;">Cancelar</button>
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

const props = defineProps({ instructores: Array })
const showForm = ref(false)
const formVacio = { id:null, nombre:'', apellidos:'', dni:'', telefono:'', especialidad:'', comision_clase:0, comision_pt:0 }
const form = ref({ ...formVacio })

const editar = (i) => { form.value = { ...i }; showForm.value = true }
const cerrar = () => { form.value = { ...formVacio }; showForm.value = false }

const guardar = () => {
    if (form.value.id) {
        router.put(route('gimnasio.instructores.update', form.value.id), form.value, { onSuccess: cerrar })
    } else {
        router.post(route('gimnasio.instructores.store'), form.value, { onSuccess: cerrar })
    }
}
</script>
