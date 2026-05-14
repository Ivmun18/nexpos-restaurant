<template>
    <AppLayout title="Clientes" subtitle="Gestion de clientes">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem;">
            <input v-model="busqueda" type="text" placeholder="Buscar por nombre, DNI, RUC, email..."
                @keyup.enter="buscarServidor"
                style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; width:320px;"/>
            <button @click="buscarServidor" style="padding:10px 16px; background:#6366F1; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; margin-left:6px;">🔍 Buscar</button>
            <button type="button" @click="abrirModal(null)"
                style="padding:10px 20px; background:#2563EB; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">
                + Nuevo cliente
            </button>
        </div>
        <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600;">DOCUMENTO</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600;">RAZON SOCIAL</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600;">TELEFONO</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600;">EMAIL</th>
                        <th style="padding:12px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="clientesFiltrados.length === 0">
                        <td colspan="5" style="padding:3rem; text-align:center; color:#94A3B8;">No hay clientes</td>
                    </tr>
                    <tr v-for="c in clientesFiltrados" :key="c.id" style="border-top:1px solid #F1F5F9;">
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ c.numero_documento }}</td>
                        <td style="padding:12px 16px; font-size:13px; font-weight:500; color:#1E293B;">{{ c.razon_social }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ c.telefono || '-' }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ c.email || '-' }}</td>
                        <td style="padding:12px 16px; text-align:center;">
                            <button type="button" @click="abrirModal(c)" style="padding:6px 14px; background:#EFF6FF; color:#2563EB; border:none; border-radius:6px; font-size:12px; cursor:pointer; margin-right:6px;">Editar</button>
                            <button type="button" @click="eliminar(c)" style="padding:6px 14px; background:#FEF2F2; color:#991B1B; border:none; border-radius:6px; font-size:12px; cursor:pointer;">Eliminar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-show="modal" style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; display:flex; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:12px; padding:2rem; width:500px;">
                <p style="font-size:16px; font-weight:600; color:#1E293B; margin:0 0 1.5rem;">{{ form.id ? 'Editar cliente' : 'Nuevo cliente' }}</p>
                <form @submit.prevent="guardar">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Tipo documento</label>
                            <select v-model="form.tipo_documento" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; background:white;">
                                <option value="1">DNI</option>
                                <option value="6">RUC</option>
                                <option value="4">Carnet extranjeria</option>
                                <option value="7">Pasaporte</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Numero documento</label>
                            <input v-model="form.numero_documento" type="text" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                        </div>
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Razon social / Nombre *</label>
                        <input v-model="form.razon_social" type="text" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Telefono</label>
                            <input v-model="form.telefono" type="text" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Email</label>
                            <input v-model="form.email" type="email" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                        </div>
                    </div>
                    <div style="margin-bottom:1.5rem;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Direccion</label>
                        <input v-model="form.direccion" type="text" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                    </div>
                    <p v-if="error" style="color:#991B1B; font-size:12px; margin-bottom:1rem;">{{ error }}</p>
                    <div style="display:flex; gap:10px; justify-content:flex-end;">
                        <button type="button" @click="modal=false" style="padding:10px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Cancelar</button>
                        <button type="submit" style="padding:10px 20px; background:#2563EB; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">{{ form.id ? 'Actualizar' : 'Guardar' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
const props = defineProps({ clientes: Object, filtros: { type: Object, default: () => ({}) } })
const busqueda = ref(props.filtros?.buscar || '')

function buscarServidor() {
    const params = new URLSearchParams()
    if (busqueda.value) params.set('buscar', busqueda.value)
    router.visit('/clientes?' + params.toString(), { preserveScroll: true })
}
const modal = ref(false)
const error = ref('')
const formVacio = () => ({ id: null, tipo_documento: '1', numero_documento: '', razon_social: '', telefono: '', email: '', direccion: '' })
const form = ref(formVacio())
const clientesFiltrados = computed(() => {
    const data = props.clientes?.data || []
    if (!busqueda.value) return data
    const q = busqueda.value.toLowerCase()
    return data.filter(c => c.razon_social.toLowerCase().includes(q) || c.numero_documento.includes(q) || (c.email||'').toLowerCase().includes(q))
})
const abrirModal = (cliente) => {
    error.value = ''
    form.value = cliente ? { ...cliente } : formVacio()
    modal.value = true
}
const guardar = () => {
    error.value = ''
    if (!form.value.numero_documento || !form.value.razon_social) {
        error.value = 'El documento y razon social son obligatorios.'
        return
    }
    const opts = { onSuccess: () => { modal.value = false; router.visit('/clientes') } }
    if (form.value.id) {
        router.put('/clientes/' + form.value.id, form.value, opts)
    } else {
        router.post('/clientes', form.value, opts)
    }
}
const eliminar = (c) => {
    if (confirm('Eliminar a ' + c.razon_social)) {
        router.delete('/clientes/' + c.id)
    }
}
</script>
