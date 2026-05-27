<template>
    <AppLayout>
        <div style="padding:24px; max-width:900px; margin:0 auto;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:20px; font-weight:700; color:#1E293B; margin:0;">📋 Planes de membresía</h1>
                    <p style="color:#64748B; margin:4px 0 0;">{{ planes.length }} planes registrados</p>
                </div>
                <button @click="showForm=true" style="background:#6D28D9; color:white; border:none; padding:10px 20px; border-radius:10px; font-weight:600; cursor:pointer;">+ Nuevo plan</button>
            </div>

            <!-- Tabla -->
            <div style="background:white; border-radius:14px; border:1px solid #E2E8F0; overflow:hidden;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead style="background:#F8FAFC;">
                        <tr style="font-size:12px; color:#64748B; text-transform:uppercase; letter-spacing:1px;">
                            <th style="padding:12px 16px; text-align:left;">Nombre</th>
                            <th style="padding:12px 16px; text-align:left;">Duración</th>
                            <th style="padding:12px 16px; text-align:left;">Precio</th>
                            <th style="padding:12px 16px; text-align:left;">Incluye</th>
                            <th style="padding:12px 16px; text-align:left;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in planes" :key="p.id" style="border-top:1px solid #F1F5F9;">
                            <td style="padding:12px 16px; font-weight:600; color:#1E293B;">{{ p.nombre }}</td>
                            <td style="padding:12px 16px; color:#64748B;">{{ p.duracion_dias }} días</td>
                            <td style="padding:12px 16px; font-weight:700; color:#6D28D9;">S/ {{ Number(p.precio).toFixed(2) }}</td>
                            <td style="padding:12px 16px;">
                                <span v-if="p.incluye_clases" style="background:#DBEAFE; color:#1D4ED8; padding:2px 8px; border-radius:20px; font-size:11px; margin-right:4px;">Clases</span>
                                <span v-if="p.incluye_pt" style="background:#D1FAE5; color:#065F46; padding:2px 8px; border-radius:20px; font-size:11px;">PT x{{ p.sesiones_pt }}</span>
                            </td>
                            <td style="padding:12px 16px;">
                                <button @click="editar(p)" style="background:#F1F5F9; border:none; padding:6px 12px; border-radius:8px; cursor:pointer; margin-right:6px; font-size:13px;">✏️ Editar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal -->
            <div v-if="showForm" style="position:fixed; inset:0; background:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; z-index:999;">
                <div style="background:white; border-radius:16px; padding:28px; width:460px; max-width:95vw;">
                    <h2 style="margin:0 0 20px; font-size:18px; font-weight:700;">{{ form.id ? 'Editar' : 'Nuevo' }} Plan</h2>
                    <div style="display:grid; gap:14px;">
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#374151;">Nombre</label>
                            <input v-model="form.nombre" type="text" placeholder="Ej: Mensual, Trimestral..." style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Precio (S/)</label>
                                <input v-model="form.precio" type="number" min="0" step="0.01" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label style="font-size:13px; font-weight:600; color:#374151;">Duración (días)</label>
                                <input v-model="form.duracion_dias" type="number" min="1" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                            </div>
                        </div>
                        <div style="display:flex; gap:20px;">
                            <label style="display:flex; align-items:center; gap:8px; font-size:14px; cursor:pointer;">
                                <input v-model="form.incluye_clases" type="checkbox" /> Incluye clases grupales
                            </label>
                            <label style="display:flex; align-items:center; gap:8px; font-size:14px; cursor:pointer;">
                                <input v-model="form.incluye_pt" type="checkbox" /> Incluye PT
                            </label>
                        </div>
                        <div v-if="form.incluye_pt">
                            <label style="font-size:13px; font-weight:600; color:#374151;">Sesiones PT incluidas</label>
                            <input v-model="form.sesiones_pt" type="number" min="0" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                        </div>
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#374151;">Descripción (opcional)</label>
                            <textarea v-model="form.descripcion" rows="2" style="width:100%; padding:10px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box; resize:none;"></textarea>
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

const props = defineProps({ planes: Array })
const showForm = ref(false)
const form = ref({ id:null, nombre:'', precio:0, duracion_dias:30, incluye_clases:false, incluye_pt:false, sesiones_pt:0, descripcion:'' })

const editar = (p) => { form.value = { ...p }; showForm.value = true }
const cerrar = () => { showForm.value = false; form.value = { id:null, nombre:'', precio:0, duracion_dias:30, incluye_clases:false, incluye_pt:false, sesiones_pt:0, descripcion:'' } }

const guardar = () => {
    if (form.value.id) {
        router.put(route('gimnasio.planes.update', form.value.id), form.value, { onSuccess: cerrar })
    } else {
        router.post(route('gimnasio.planes.store'), form.value, { onSuccess: cerrar })
    }
}
</script>
