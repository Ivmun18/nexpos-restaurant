<template>
    <AppLayout title="Proveedores" subtitle="Gestión de proveedores">

        <div style="display:flex; justify-content:flex-end; margin-bottom:1.5rem;">
            <button @click="abrirModal()"
                style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; font-size:14px; font-weight:600; cursor:pointer;">
                + Nuevo Proveedor
            </button>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" style="margin-bottom:1rem; padding:14px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; color:#166534; font-size:14px;">
            ✅ {{ $page.props.flash.success }}
        </div>

        <!-- Buscador -->
        <div style="margin-bottom:16px;">
            <input v-model="busqueda" placeholder="🔍 Buscar proveedor..."
                style="width:100%; max-width:400px; padding:10px 16px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box;"
                @focus="e => e.target.style.borderColor='#14B8A6'"
                @blur="e => e.target.style.borderColor='#E2E8F0'" />
        </div>

        <!-- Tabla -->
        <div style="background:white; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(0,0,0,0.05); overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead style="background:#F8FAFC;">
                    <tr>
                        <th style="padding:14px 20px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Proveedor</th>
                        <th style="padding:14px 20px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Documento</th>
                        <th style="padding:14px 20px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Contacto</th>
                        <th style="padding:14px 20px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Crédito</th>
                        <th style="padding:14px 20px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Estado</th>
                        <th style="padding:14px 20px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!proveedoresFiltrados.length">
                        <td colspan="6" style="text-align:center; padding:60px; color:#CBD5E1;">
                            <p style="font-size:40px; margin:0 0 8px;">📦</p>
                            <p style="font-size:15px;">No hay proveedores registrados</p>
                        </td>
                    </tr>
                    <tr v-for="p in proveedoresFiltrados" :key="p.id"
                        style="border-top:1px solid #F1F5F9;"
                        @mouseenter="e => e.currentTarget.style.background='#F8FAFC'"
                        @mouseleave="e => e.currentTarget.style.background='white'">
                        <td style="padding:14px 20px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg,#14B8A6,#0F766E); display:flex; align-items:center; justify-content:center; color:white; font-weight:bold; font-size:16px; flex-shrink:0;">
                                    {{ p.razon_social.charAt(0) }}
                                </div>
                                <div>
                                    <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ p.razon_social }}</p>
                                    <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ p.nombre_comercial || '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td style="padding:14px 20px;">
                            <p style="font-size:13px; color:#475569; margin:0; font-family:monospace;">{{ p.numero_documento }}</p>
                            <p style="font-size:11px; color:#94A3B8; margin:2px 0 0;">{{ p.tipo_documento === '6' ? 'RUC' : 'DNI' }}</p>
                        </td>
                        <td style="padding:14px 20px;">
                            <p style="font-size:13px; color:#475569; margin:0;">{{ p.contacto_nombre || '—' }}</p>
                            <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ p.telefono || p.email || '—' }}</p>
                        </td>
                        <td style="padding:14px 20px;">
                            <span v-if="p.dias_credito > 0" style="padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600; background:#e0f2fe; color:#0369a1;">
                                {{ p.dias_credito }} días
                            </span>
                            <span v-else style="font-size:13px; color:#94A3B8;">Al contado</span>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <span :style="p.activo ? 'padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; background:#dcfce7; color:#166534;' : 'padding:4px 12px; border-radius:20px; font-size:12px; font-weight:600; background:#fee2e2; color:#dc2626;'">
                                {{ p.activo ? '✅ Activo' : '❌ Inactivo' }}
                            </span>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <div style="display:flex; gap:8px; justify-content:center;">
                                <button @click="abrirModal(p)"
                                    style="padding:7px 14px; background:#0891b2; color:white; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                                    ✏️ Editar
                                </button>
                                <button @click="eliminar(p)"
                                    style="padding:7px 14px; background:#fee2e2; color:#dc2626; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                                    🗑
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="modal" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
            <div style="background:white; border-radius:16px; padding:28px; width:100%; max-width:560px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px;">
                    {{ editando ? '✏️ Editar Proveedor' : '➕ Nuevo Proveedor' }}
                </h3>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Razón Social *</label>
                        <input v-model="form.razon_social" required style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; outline:none; box-sizing:border-box;" placeholder="EMPRESA S.A.C." />
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Tipo Documento *</label>
                        <select v-model="form.tipo_documento" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; outline:none; box-sizing:border-box;">
                            <option value="6">RUC</option>
                            <option value="1">DNI</option>
                            <option value="0">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Número Documento *</label>
                        <input v-model="form.numero_documento" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; outline:none; box-sizing:border-box; font-family:monospace;" placeholder="20123456789" />
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Nombre Comercial</label>
                        <input v-model="form.nombre_comercial" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Teléfono</label>
                        <input v-model="form.telefono" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; outline:none; box-sizing:border-box;" placeholder="999 999 999" />
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Email</label>
                        <input v-model="form.email" type="email" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; outline:none; box-sizing:border-box;" placeholder="proveedor@empresa.com" />
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Dirección</label>
                        <input v-model="form.direccion" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Contacto</label>
                        <input v-model="form.contacto_nombre" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; outline:none; box-sizing:border-box;" placeholder="Nombre del contacto" />
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Días de Crédito</label>
                        <input v-model="form.dias_credito" type="number" min="0" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; outline:none; box-sizing:border-box;" placeholder="0" />
                    </div>
                    <div v-if="editando" style="grid-column:1/-1; display:flex; align-items:center; gap:10px;">
                        <input type="checkbox" v-model="form.activo" id="activo" style="width:16px; height:16px; accent-color:#14B8A6;" />
                        <label for="activo" style="font-size:13px; color:#374151;">Proveedor activo</label>
                    </div>
                </div>

                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button @click="modal=false" style="padding:10px 20px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#64748B; cursor:pointer; background:white;">
                        Cancelar
                    </button>
                    <button @click="guardar" :disabled="procesando" style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
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

const props = defineProps({ proveedores: Array })

const modal     = ref(false)
const editando  = ref(null)
const procesando = ref(false)
const busqueda  = ref('')

const form = ref({
    razon_social: '', tipo_documento: '6', numero_documento: '',
    nombre_comercial: '', telefono: '', email: '', direccion: '',
    contacto_nombre: '', dias_credito: 0, activo: true,
})

const proveedoresFiltrados = computed(() => {
    if (!busqueda.value) return props.proveedores
    const q = busqueda.value.toLowerCase()
    return props.proveedores.filter(p =>
        p.razon_social.toLowerCase().includes(q) ||
        p.numero_documento.includes(q) ||
        (p.contacto_nombre || '').toLowerCase().includes(q)
    )
})

const abrirModal = (p = null) => {
    editando.value = p
    form.value = p ? {
        razon_social: p.razon_social, tipo_documento: p.tipo_documento,
        numero_documento: p.numero_documento, nombre_comercial: p.nombre_comercial || '',
        telefono: p.telefono || '', email: p.email || '', direccion: p.direccion || '',
        contacto_nombre: p.contacto_nombre || '', dias_credito: p.dias_credito || 0,
        activo: p.activo,
    } : {
        razon_social: '', tipo_documento: '6', numero_documento: '',
        nombre_comercial: '', telefono: '', email: '', direccion: '',
        contacto_nombre: '', dias_credito: 0, activo: true,
    }
    modal.value = true
}

const guardar = () => {
    procesando.value = true
    if (editando.value) {
        router.put(`/proveedores/${editando.value.id}`, form.value, {
            onSuccess: () => { modal.value = false; procesando.value = false },
            onError:   () => { procesando.value = false },
        })
    } else {
        router.post('/proveedores', form.value, {
            onSuccess: () => { modal.value = false; procesando.value = false },
            onError:   () => { procesando.value = false },
        })
    }
}

const eliminar = (p) => {
    if (confirm(`¿Desactivar al proveedor "${p.razon_social}"?`)) {
        router.delete(`/proveedores/${p.id}`)
    }
}
</script>
