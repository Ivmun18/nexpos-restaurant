<template>
    <AppLayout title="Proveedores" subtitle="Gestión de proveedores">

        <!-- Barra superior -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
            <input v-model="busqueda" type="text" placeholder="Buscar por nombre o RUC..."
                style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; color:#1E293B; background:white; outline:none; width:320px;"/>
            <button @click="abrirModal(null)"
                style="padding:10px 20px; background:#2563EB; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">
                + Nuevo proveedor
            </button>
        </div>

        <!-- Tabla -->
        <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Documento</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Razón Social</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Contacto</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Teléfono</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Días crédito</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Estado</th>
                        <th style="padding:12px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="proveedoresFiltrados.length === 0">
                        <td colspan="7" style="padding:3rem; text-align:center; color:#94A3B8; font-size:14px;">
                            No hay proveedores registrados
                        </td>
                    </tr>
                    <tr v-for="p in proveedoresFiltrados" :key="p.id" style="border-top:1px solid #F1F5F9;">
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">
                            <span style="font-size:10px; background:#EFF6FF; color:#1D4ED8; padding:2px 8px; border-radius:20px; margin-right:6px;">
                                {{ p.tipo_documento === '6' ? 'RUC' : 'DNI' }}
                            </span>
                            {{ p.numero_documento }}
                        </td>
                        <td style="padding:12px 16px;">
                            <p style="font-size:13px; font-weight:500; color:#1E293B; margin:0;">{{ p.razon_social }}</p>
                            <p v-if="p.nombre_comercial" style="font-size:11px; color:#94A3B8; margin:2px 0 0;">{{ p.nombre_comercial }}</p>
                        </td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ p.contacto_nombre || '—' }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ p.telefono || '—' }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">
                            <span v-if="p.dias_credito > 0" style="font-size:11px; background:#FFFBEB; color:#92400E; padding:2px 8px; border-radius:20px;">
                                {{ p.dias_credito }} días
                            </span>
                            <span v-else style="font-size:11px; color:#94A3B8;">Contado</span>
                        </td>
                        <td style="padding:12px 16px;">
                            <span :style="p.activo ? estadoActivo : estadoInactivo">
                                {{ p.activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td style="padding:12px 16px; text-align:center;">
                            <div style="display:flex; gap:8px; justify-content:center;">
                                <button @click="abrirModal(p)"
                                    style="padding:6px 14px; background:#EFF6FF; color:#2563EB; border:none; border-radius:6px; font-size:12px; cursor:pointer; font-weight:500;">
                                    Editar
                                </button>
                                <button @click="eliminar(p)"
                                    style="padding:6px 14px; background:#FEF2F2; color:#991B1B; border:none; border-radius:6px; font-size:12px; cursor:pointer; font-weight:500;">
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="modal" style="position:fixed; inset:0; background:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; z-index:999;">
            <div style="background:white; border-radius:12px; padding:2rem; width:100%; max-width:560px; border:1px solid #E2E8F0; max-height:90vh; overflow-y:auto;">

                <p style="font-size:16px; font-weight:600; color:#1E293B; margin:0 0 1.5rem;">
                    {{ form.id ? 'Editar proveedor' : 'Nuevo proveedor' }}
                </p>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Tipo documento</label>
                        <select v-model="form.tipo_documento"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; background:white;">
                            <option value="6">RUC</option>
                            <option value="1">DNI</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Número documento *</label>
                        <input v-model="form.numero_documento" type="text"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                    </div>
                </div>

                <div style="margin-bottom:1rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Razón social *</label>
                    <input v-model="form.razon_social" type="text"
                        style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                </div>

                <div style="margin-bottom:1rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Nombre comercial</label>
                    <input v-model="form.nombre_comercial" type="text"
                        style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Teléfono</label>
                        <input v-model="form.telefono" type="text"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Email</label>
                        <input v-model="form.email" type="email"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Contacto</label>
                        <input v-model="form.contacto_nombre" type="text"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Días de crédito</label>
                        <input v-model="form.dias_credito" type="number" min="0"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                    </div>
                </div>

                <div style="margin-bottom:1rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Dirección</label>
                    <input v-model="form.direccion" type="text"
                        style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                </div>

                <div style="display:flex; align-items:center; gap:10px; margin-bottom:1.5rem;">
                    <input v-model="form.agente_retencion" type="checkbox" style="accent-color:#2563EB;"/>
                    <label style="font-size:13px; color:#64748B;">Es agente de retención</label>
                </div>

                <p v-if="error" style="color:#991B1B; font-size:12px; margin-bottom:1rem;">{{ error }}</p>

                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button @click="modal=false"
                        style="padding:10px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">
                        Cancelar
                    </button>
                    <button @click="guardar"
                        style="padding:10px 20px; background:#2563EB; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                        {{ form.id ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ proveedores: Object })

const busqueda = ref('')
const modal    = ref(false)
const error    = ref('')

const formVacio = () => ({
    id: null, tipo_documento: '6', numero_documento: '',
    razon_social: '', nombre_comercial: '', telefono: '',
    email: '', contacto_nombre: '', dias_credito: 0,
    direccion: '', agente_retencion: false, activo: true,
})

const form = ref(formVacio())

const estadoActivo   = { fontSize:'11px', background:'#F0FDF4', color:'#166534', padding:'3px 10px', borderRadius:'20px' }
const estadoInactivo = { fontSize:'11px', background:'#F1F5F9', color:'#64748B', padding:'3px 10px', borderRadius:'20px' }

const proveedoresFiltrados = computed(() => {
    const data = props.proveedores?.data || []
    if (!busqueda.value) return data
    const q = busqueda.value.toLowerCase()
    return data.filter(p =>
        p.razon_social.toLowerCase().includes(q) ||
        p.numero_documento.includes(q)
    )
})

const abrirModal = (proveedor) => {
    error.value = ''
    form.value  = proveedor ? { ...proveedor } : formVacio()
    modal.value = true
}

const guardar = () => {
    error.value = ''
    if (!form.value.numero_documento || !form.value.razon_social) {
        error.value = 'El documento y razón social son obligatorios.'
        return
    }
    if (form.value.id) {
        router.put(`/proveedores/${form.value.id}`, form.value, {
            onSuccess: () => { modal.value = false }
        })
    } else {
        router.post('/proveedores', form.value, {
            onSuccess: () => { modal.value = false }
        })
    }
}

const eliminar = (p) => {
    if (confirm(`¿Eliminar a ${p.razon_social}?`)) {
        router.delete(`/proveedores/${p.id}`)
    }
}
</script>