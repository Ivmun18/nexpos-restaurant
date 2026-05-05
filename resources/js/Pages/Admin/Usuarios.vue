<template>
    <AppLayout title="Usuarios" subtitle="Gestión de usuarios del sistema">

        <!-- Header -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
            <div>
                <p style="font-size:24px; font-weight:800; color:#1E293B; margin:0;">👥 Usuarios</p>
                <p style="font-size:14px; color:#94A3B8; margin:4px 0 0;">{{ usuarios.length }} usuarios registrados</p>
            </div>
            <button @click="abrirModal(null)"
                style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                + Nuevo Usuario
            </button>
        </div>

        <!-- Buscador -->
        <div style="margin-bottom:16px;">
            <input v-model="busqueda" placeholder="🔍 Buscar usuario..."
                style="width:100%; max-width:400px; padding:10px 16px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box;"
                @focus="$event.target.style.borderColor='#14B8A6'"
                @blur="$event.target.style.borderColor='#E2E8F0'" />
        </div>

        <!-- Tabla -->
        <div style="background:white; border-radius:20px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06); overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:14px 20px; text-align:left; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Usuario</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Email</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Rol</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Estado</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!usuariosFiltrados.length">
                        <td colspan="5" style="text-align:center; padding:60px; color:#CBD5E1;">
                            <p style="font-size:40px; margin:0 0 8px;">👥</p>
                            <p style="font-size:15px;">Sin usuarios</p>
                        </td>
                    </tr>
                    <tr v-for="u in usuariosFiltrados" :key="u.id"
                        style="border-top:1px solid #F1F5F9;"
                        @mouseenter="e => e.currentTarget.style.background='#F8FAFC'"
                        @mouseleave="e => e.currentTarget.style.background='white'">
                        <td style="padding:14px 20px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:38px; height:38px; background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; color:white; flex-shrink:0;">
                                    {{ u.name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase() }}
                                </div>
                                <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ u.name }}</p>
                            </div>
                        </td>
                        <td style="padding:14px 20px; font-size:14px; color:#475569;">{{ u.email }}</td>
                        <td style="padding:14px 20px; text-align:center;">
                            <span :style="rolStyle(u.rol)">{{ rolLabel(u.rol) }}</span>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <span :style="{
                                padding: '4px 12px', borderRadius: '20px', fontSize: '12px', fontWeight: '600',
                                background: u.activo ? '#F0FDF4' : '#FEF2F2',
                                color: u.activo ? '#166534' : '#991B1B',
                            }">{{ u.activo ? '✅ Activo' : '❌ Inactivo' }}</span>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <div style="display:flex; gap:8px; justify-content:center;">
                                <button @click="abrirModal(u)"
                                    style="padding:6px 12px; background:#F0FDFA; color:#0F766E; border-radius:8px; font-size:12px; font-weight:600; border:1px solid #CCFBF1; cursor:pointer;">
                                    ✏️ Editar
                                </button>
                                <button @click="toggleActivo(u)"
                                    :style="{
                                        padding: '6px 12px', borderRadius: '8px', fontSize: '12px', fontWeight: '600', cursor: 'pointer',
                                        background: u.activo ? '#FEF2F2' : '#F0FDF4',
                                        color: u.activo ? '#991B1B' : '#166534',
                                        border: u.activo ? '1px solid #FECACA' : '1px solid #DCFCE7',
                                    }">
                                    {{ u.activo ? '🔒 Desactivar' : '🔓 Activar' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="modal" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:center; justify-content:center;">
                <div style="background:white; border-radius:20px; padding:32px; width:100%; max-width:480px; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
                    <p style="font-size:20px; font-weight:800; color:#1E293B; margin:0 0 24px;">
                        {{ form.id ? '✏️ Editar Usuario' : '+ Nuevo Usuario' }}
                    </p>

                    <div style="display:grid; gap:14px;">
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#64748B;">Nombre completo *</label>
                            <input v-model="form.name" style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                        </div>
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#64748B;">Email *</label>
                            <input v-model="form.email" type="email" style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                        </div>
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#64748B;">{{ form.id ? 'Nueva contraseña (dejar vacío para no cambiar)' : 'Contraseña *' }}</label>
                            <input v-model="form.password" type="password" style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                        </div>
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#64748B;">Rol *</label>
                            <select v-model="form.rol" style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;">
                                <option value="admin">👑 Administrador</option>
                                <option value="cajero">💰 Cajero</option>
                                <option value="mozo">🍽️ Mozo</option>
                                <option value="cocinero">👨‍🍳 Cocinero</option>
                                <option value="vendedor">🛍️ Vendedor</option>
                            </select>
                        </div>
                    </div>

                    <p v-if="error" style="font-size:13px; color:#DC2626; background:#FEF2F2; border-radius:8px; padding:10px 14px; margin-top:14px;">{{ error }}</p>

                    <div style="display:flex; gap:12px; margin-top:24px;">
                        <button @click="modal = false"
                            style="flex:1; padding:12px; background:white; color:#64748B; border-radius:10px; font-size:14px; font-weight:600; border:2px solid #E2E8F0; cursor:pointer;">
                            Cancelar
                        </button>
                        <button @click="guardar"
                            style="flex:1; padding:12px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                            {{ form.id ? 'Actualizar' : 'Crear Usuario' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ usuarios: { type: Array, default: () => [] } })
const busqueda = ref('')
const modal = ref(false)
const error = ref('')

const formVacio = () => ({ id: null, name: '', email: '', password: '', rol: 'cajero' })
const form = ref(formVacio())

const usuariosFiltrados = computed(() => {
    if (!busqueda.value) return props.usuarios
    const q = busqueda.value.toLowerCase()
    return props.usuarios.filter(u =>
        u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q)
    )
})

const rolLabel = (rol) => ({
    admin:     '👑 Admin',
    cajero:    '💰 Cajero',
    mozo:      '🍽️ Mozo',
    cocinero:  '👨‍🍳 Cocinero',
    vendedor:  '🛍️ Vendedor',
})[rol] || rol

const rolStyle = (rol) => {
    const estilos = {
        admin:    { background: '#FEF3C7', color: '#92400E' },
        cajero:   { background: '#EFF6FF', color: '#1D4ED8' },
        mozo:     { background: '#F0FDF4', color: '#166534' },
        cocinero: { background: '#FFF7ED', color: '#C2410C' },
        vendedor: { background: '#F5F3FF', color: '#6D28D9' },
    }
    return { padding: '4px 12px', borderRadius: '20px', fontSize: '12px', fontWeight: '600', ...(estilos[rol] || {}) }
}

const abrirModal = (u) => {
    error.value = ''
    form.value = u
        ? { id: u.id, name: u.name, email: u.email, password: '', rol: u.rol }
        : formVacio()
    modal.value = true
}

const guardar = () => {
    error.value = ''
    if (!form.value.name || !form.value.email) { error.value = 'Nombre y email son obligatorios.'; return }
    if (!form.value.id && !form.value.password) { error.value = 'La contraseña es obligatoria.'; return }

    if (form.value.id) {
        router.put(`/usuarios/${form.value.id}`, form.value, {
            onSuccess: () => { modal.value = false; window.location.replace('/usuarios') },
            onError: (errors) => { error.value = Object.values(errors)[0] }
        })
    } else {
        router.post('/usuarios', form.value, {
            onSuccess: () => { modal.value = false; window.location.replace('/usuarios') },
            onError: (errors) => { error.value = Object.values(errors)[0] }
        })
    }
}

const toggleActivo = (u) => {
    if (confirm(`¿${u.activo ? 'Desactivar' : 'Activar'} a ${u.name}?`)) {
        router.patch(`/usuarios/${u.id}/toggle`, {}, {
            onSuccess: () => window.location.replace('/usuarios')
        })
    }
}
</script>