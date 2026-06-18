<template>
    <AppLayout title="Instituciones" subtitle="Instituciones con vales y su recargo">

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
            <div>
                <p style="font-size:24px; font-weight:800; color:#1E293B; margin:0;">🏢 Instituciones (Vales)</p>
                <p style="font-size:14px; color:#94A3B8; margin:4px 0 0;">Configura el recargo que se aplica al cobrar con vale de cada institucion</p>
            </div>
            <button @click="modalNuevo = true"
                style="padding:12px 20px; background:linear-gradient(135deg,#F59E0B,#B45309); color:white; border-radius:10px; font-size:14px; font-weight:700; border:none; cursor:pointer;">
                + Nueva Institucion
            </button>
        </div>

        <div style="background:white; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                        <th style="text-align:left; padding:12px 16px; font-size:12px; color:#64748B; font-weight:700;">INSTITUCION</th>
                        <th style="text-align:left; padding:12px 16px; font-size:12px; color:#64748B; font-weight:700;">RECARGO</th>
                        <th style="text-align:left; padding:12px 16px; font-size:12px; color:#64748B; font-weight:700;">ESTADO</th>
                        <th style="text-align:left; padding:12px 16px; font-size:12px; color:#64748B; font-weight:700;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="inst in instituciones" :key="inst.id" style="border-bottom:1px solid #F1F5F9;">
                        <td style="padding:12px 16px; font-size:13px; color:#1E293B; font-weight:600;">{{ inst.nombre }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#1E293B;">+{{ Number(inst.porcentaje_recargo).toFixed(2) }}%</td>
                        <td style="padding:12px 16px;">
                            <span :style="inst.activo ? 'background:#DCFCE7; color:#166534;' : 'background:#FEF2F2; color:#991B1B;'" style="padding:4px 10px; border-radius:8px; font-size:11px; font-weight:700;">
                                {{ inst.activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td style="padding:12px 16px; display:flex; gap:8px;">
                            <button @click="editar(inst)"
                                style="padding:6px 12px; background:#F0FDFA; color:#0F766E; border-radius:6px; font-size:12px; font-weight:600; border:1px solid #CCFBF1; cursor:pointer;">
                                Editar
                            </button>
                            <button @click="eliminar(inst)"
                                style="padding:6px 12px; background:#FEF2F2; color:#991B1B; border-radius:6px; font-size:12px; font-weight:600; border:1px solid #FECACA; cursor:pointer;">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!instituciones.length">
                        <td colspan="4" style="padding:24px; text-align:center; color:#94A3B8; font-size:13px;">
                            Aun no hay instituciones registradas.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="modalNuevo || modalEditar" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:center; justify-content:center; padding:20px;">
            <div style="background:white; border-radius:16px; padding:24px; width:100%; max-width:420px;">
                <p style="font-size:20px; font-weight:800; color:#1E293B; margin:0 0 16px;">{{ modalEditar ? 'Editar Institucion' : 'Nueva Institucion' }}</p>

                <div style="display:grid; gap:12px;">
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Nombre de la institucion</label>
                        <input v-model="form.nombre" type="text" placeholder="Ej: Colegio San Martin"
                            style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Recargo (%)</label>
                        <input v-model.number="form.porcentaje_recargo" type="number" min="0" max="100" step="0.01" placeholder="Ej: 5"
                            style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                        <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">Este porcentaje se suma automaticamente al total cuando se cobra con vale de esta institucion.</p>
                    </div>
                    <label v-if="modalEditar" style="display:flex; align-items:center; gap:6px; font-size:13px; color:#64748B;">
                        <input v-model="form.activo" type="checkbox" />
                        Institucion activa
                    </label>
                </div>

                <div style="display:flex; gap:12px; margin-top:20px;">
                    <button @click="cerrarModal"
                        style="flex:1; padding:12px; background:white; color:#64748B; border-radius:10px; font-size:14px; font-weight:600; border:2px solid #E2E8F0; cursor:pointer;">
                        Cancelar
                    </button>
                    <button @click="guardar"
                        style="flex:1; padding:12px; background:linear-gradient(135deg,#F59E0B,#B45309); color:white; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                        {{ modalEditar ? 'Actualizar' : 'Crear' }}
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    instituciones: { type: Array, default: () => [] },
})

const modalNuevo = ref(false)
const modalEditar = ref(false)
const institucionSeleccionada = ref(null)
const form = ref({ nombre: '', porcentaje_recargo: '', activo: true })

const editar = (inst) => {
    institucionSeleccionada.value = inst
    form.value = { nombre: inst.nombre, porcentaje_recargo: inst.porcentaje_recargo, activo: inst.activo }
    modalEditar.value = true
}

const cerrarModal = () => {
    modalNuevo.value = false
    modalEditar.value = false
    institucionSeleccionada.value = null
    form.value = { nombre: '', porcentaje_recargo: '', activo: true }
}

const guardar = () => {
    if (!form.value.nombre.trim()) {
        alert('Falta el nombre de la institucion')
        return
    }
    if (form.value.porcentaje_recargo === '' || form.value.porcentaje_recargo === null || Number(form.value.porcentaje_recargo) < 0) {
        alert('Falta indicar el porcentaje de recargo (puede ser 0 si no aplica recargo)')
        return
    }

    if (modalEditar.value) {
        router.put(`/minimarket/instituciones/${institucionSeleccionada.value.id}`, form.value, {
            onSuccess: () => window.location.replace('/minimarket/instituciones')
        })
    } else {
        router.post('/minimarket/instituciones', form.value, {
            onSuccess: () => window.location.replace('/minimarket/instituciones')
        })
    }
}

const eliminar = (inst) => {
    if (!confirm(`Eliminar la institucion "${inst.nombre}"?`)) return
    router.delete(`/minimarket/instituciones/${inst.id}`, {
        onSuccess: () => window.location.replace('/minimarket/instituciones')
    })
}
</script>
