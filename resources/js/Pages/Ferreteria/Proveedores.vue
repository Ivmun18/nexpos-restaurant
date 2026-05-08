<template>
    <AppLayout title="Proveedores" subtitle="Gestión de proveedores ferretería">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <input v-model="busqueda" placeholder="🔍 Buscar por nombre o RUC..."
                style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; width:320px; outline:none;">
            <button @click="abrirModal()" style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                + Nuevo Proveedor
            </button>
        </div>

        <!-- Stats -->
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px;">
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Total Proveedores</p>
                <p style="font-size:24px; font-weight:700; color:#1E293B; margin:0;">{{ proveedores.length }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Con RUC</p>
                <p style="font-size:24px; font-weight:700; color:#14B8A6; margin:0;">{{ proveedores.filter(p=>p.tipo_documento==='RUC').length }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Ag. Retención</p>
                <p style="font-size:24px; font-weight:700; color:#F59E0B; margin:0;">{{ proveedores.filter(p=>p.agente_retencion).length }}</p>
            </div>
        </div>

        <!-- Tabla -->
        <div style="background:white; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">PROVEEDOR</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">RUC/DOC</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">CONTACTO</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">DIRECCIÓN</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; font-weight:700; color:#64748B;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="proveedoresFiltrados.length === 0">
                        <td colspan="5" style="padding:40px; text-align:center; color:#94A3B8;">No hay proveedores registrados</td>
                    </tr>
                    <tr v-for="p in proveedoresFiltrados" :key="p.id"
                        style="border-top:1px solid #F1F5F9;"
                        @mouseenter="e => e.currentTarget.style.background='#F8FAFC'"
                        @mouseleave="e => e.currentTarget.style.background='white'">
                        <td style="padding:14px 20px;">
                            <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ p.razon_social }}</p>
                            <p v-if="p.nombre_comercial" style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ p.nombre_comercial }}</p>
                            <span v-if="p.agente_retencion" style="font-size:11px; padding:2px 8px; borderRadius:20px; background:#FEF3C7; color:#92400E; border-radius:20px; font-weight:600;">Ag. Retención</span>
                        </td>
                        <td style="padding:14px 20px;">
                            <span style="padding:2px 8px; border-radius:20px; font-size:11px; font-weight:700; background:#EFF6FF; color:#1D4ED8;">{{ p.tipo_documento }}</span>
                            <p style="font-size:13px; color:#475569; margin:4px 0 0;">{{ p.numero_documento }}</p>
                        </td>
                        <td style="padding:14px 20px;">
                            <p v-if="p.contacto_nombre" style="font-size:13px; font-weight:600; color:#1E293B; margin:0;">{{ p.contacto_nombre }}</p>
                            <p v-if="p.telefono" style="font-size:12px; color:#94A3B8; margin:2px 0 0;">📞 {{ p.telefono }}</p>
                            <p v-if="p.email" style="font-size:12px; color:#94A3B8; margin:2px 0 0;">✉️ {{ p.email }}</p>
                        </td>
                        <td style="padding:14px 20px; font-size:13px; color:#475569;">
                            <p style="margin:0;">{{ p.direccion || '—' }}</p>
                            <p v-if="p.distrito" style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ p.distrito }}, {{ p.provincia }}</p>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <div style="display:flex; gap:8px; justify-content:center;">
                                <button @click="abrirModal(p)" style="padding:6px 12px; background:#F0FDFA; color:#0F766E; border:1px solid #CCFBF1; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">✏️ Editar</button>
                                <button @click="eliminar(p)" style="padding:6px 12px; background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; border-radius:8px; font-size:12px; cursor:pointer;">🗑️</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="modal" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:flex-start; justify-content:center; overflow-y:auto; padding:20px;">
            <div style="background:white; border-radius:16px; padding:28px; width:560px; margin:auto;">
                <h2 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 20px;">{{ editando ? 'Editar Proveedor' : 'Nuevo Proveedor' }}</h2>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">TIPO DOCUMENTO</label>
                        <select v-model="form.tipo_documento" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                            <option value="RUC">RUC</option>
                            <option value="DNI">DNI</option>
                            <option value="CE">Carnet Extranjería</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">NÚMERO DOCUMENTO *</label>
                        <input v-model="form.numero_documento" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">RAZÓN SOCIAL *</label>
                        <input v-model="form.razon_social" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">NOMBRE COMERCIAL</label>
                        <input v-model="form.nombre_comercial" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">CONTACTO</label>
                        <input v-model="form.contacto_nombre" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">TELÉFONO</label>
                        <input v-model="form.telefono" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">EMAIL</label>
                        <input v-model="form.email" type="email" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">DIRECCIÓN</label>
                        <input v-model="form.direccion" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">DISTRITO</label>
                        <input v-model="form.distrito" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">DÍAS CRÉDITO</label>
                        <input v-model="form.dias_credito" type="number" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div style="grid-column:1/-1; display:flex; align-items:center; gap:10px;">
                        <input type="checkbox" v-model="form.agente_retencion" id="agente" style="width:16px; height:16px;">
                        <label for="agente" style="font-size:14px; color:#1E293B; cursor:pointer;">Es agente de retención</label>
                    </div>
                </div>

                <div style="display:flex; gap:12px; margin-top:24px; justify-content:flex-end;">
                    <button @click="modal=false" style="padding:10px 20px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; cursor:pointer; background:white;">Cancelar</button>
                    <button @click="guardar" :disabled="procesando" style="padding:10px 24px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">
                        {{ procesando ? 'Guardando...' : 'Guardar' }}
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

const props = defineProps({ proveedores: { type: Array, default: () => [] } })

const busqueda  = ref('')
const modal     = ref(false)
const editando  = ref(null)
const procesando = ref(false)

const form = ref({
    tipo_documento: 'RUC', numero_documento: '', razon_social: '',
    nombre_comercial: '', contacto_nombre: '', telefono: '', email: '',
    direccion: '', distrito: '', dias_credito: 0, agente_retencion: false
})

const proveedoresFiltrados = computed(() => {
    const q = busqueda.value.trim()
    if (!q) return props.proveedores
    return props.proveedores.filter(p =>
        (p.razon_social || '').toLowerCase().includes(q.toLowerCase()) ||
        (p.numero_documento || '').includes(q) ||
        (p.contacto_nombre || '').toLowerCase().includes(q.toLowerCase())
    )
})

const abrirModal = (p = null) => {
    editando.value = p
    form.value = p ? { ...p } : {
        tipo_documento: 'RUC', numero_documento: '', razon_social: '',
        nombre_comercial: '', contacto_nombre: '', telefono: '', email: '',
        direccion: '', distrito: '', dias_credito: 0, agente_retencion: false
    }
    modal.value = true
}

const guardar = () => {
    procesando.value = true
    if (editando.value) {
        router.put(`/ferreteria/proveedores/${editando.value.id}`, form.value, {
            onSuccess: () => { modal.value = false; procesando.value = false },
            onError:   () => { procesando.value = false }
        })
    } else {
        router.post('/ferreteria/proveedores', form.value, {
            onSuccess: () => { modal.value = false; procesando.value = false },
            onError:   () => { procesando.value = false }
        })
    }
}

const eliminar = (p) => {
    if (confirm(`¿Eliminar proveedor "${p.razon_social}"?`)) {
        router.delete(`/ferreteria/proveedores/${p.id}`)
    }
}
</script>
