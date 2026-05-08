<template>
    <AppLayout title="Clientes" subtitle="Gestión de clientes ferretería">

        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <input v-model="busqueda" placeholder="🔍 Buscar por nombre, RUC o DNI..."
                style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; width:320px; outline:none;">
            <button @click="abrirModal()" style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                + Nuevo Cliente
            </button>
        </div>

        <!-- Stats -->
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px;">
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Total Clientes</p>
                <p style="font-size:24px; font-weight:700; color:#1E293B; margin:0;">{{ clientes.length }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Personas Naturales</p>
                <p style="font-size:24px; font-weight:700; color:#14B8A6; margin:0;">{{ clientes.filter(c=>c.tipo_documento==='DNI').length }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Empresas (RUC)</p>
                <p style="font-size:24px; font-weight:700; color:#3B82F6; margin:0;">{{ clientes.filter(c=>c.tipo_documento==='RUC').length }}</p>
            </div>
        </div>

        <!-- Tabla -->
        <div style="background:white; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">CLIENTE</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">DOCUMENTO</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">CONTACTO</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">DIRECCIÓN</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; font-weight:700; color:#64748B;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="clientesFiltrados.length === 0">
                        <td colspan="5" style="padding:40px; text-align:center; color:#94A3B8;">No hay clientes registrados</td>
                    </tr>
                    <tr v-for="c in clientesFiltrados" :key="c.id"
                        style="border-top:1px solid #F1F5F9;"
                        @mouseenter="e => e.currentTarget.style.background='#F8FAFC'"
                        @mouseleave="e => e.currentTarget.style.background='white'">
                        <td style="padding:14px 20px;">
                            <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ c.razon_social }}</p>
                            <p v-if="c.nombre_comercial" style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ c.nombre_comercial }}</p>
                        </td>
                        <td style="padding:14px 20px;">
                            <span :style="{padding:'2px 8px', borderRadius:'20px', fontSize:'11px', fontWeight:'700', background: c.tipo_documento==='RUC' ? '#EFF6FF' : '#F0FDF4', color: c.tipo_documento==='RUC' ? '#1D4ED8' : '#166534'}">
                                {{ c.tipo_documento }}
                            </span>
                            <p style="font-size:13px; color:#475569; margin:4px 0 0;">{{ c.numero_documento }}</p>
                        </td>
                        <td style="padding:14px 20px;">
                            <p v-if="c.celular" style="font-size:13px; color:#475569; margin:0;">📱 {{ c.celular }}</p>
                            <p v-if="c.email" style="font-size:12px; color:#94A3B8; margin:2px 0 0;">✉️ {{ c.email }}</p>
                        </td>
                        <td style="padding:14px 20px; font-size:13px; color:#475569; max-width:200px;">
                            <p style="margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ c.direccion || '—' }}</p>
                            <p v-if="c.distrito" style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ c.distrito }}, {{ c.provincia }}</p>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <div style="display:flex; gap:8px; justify-content:center;">
                                <button @click="abrirModal(c)" style="padding:6px 12px; background:#F0FDFA; color:#0F766E; border:1px solid #CCFBF1; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">✏️ Editar</button>
                                <button @click="eliminar(c)" style="padding:6px 12px; background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; border-radius:8px; font-size:12px; cursor:pointer;">🗑️</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="modal" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:flex-start; justify-content:center; overflow-y:auto; padding:20px;">
            <div style="background:white; border-radius:16px; padding:28px; width:560px; margin:auto;">
                <h2 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 20px;">{{ editando ? 'Editar Cliente' : 'Nuevo Cliente' }}</h2>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">TIPO DOCUMENTO</label>
                        <select v-model="form.tipo_documento" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                            <option value="DNI">DNI</option>
                            <option value="RUC">RUC</option>
                            <option value="CE">Carnet Extranjería</option>
                            <option value="PASAPORTE">Pasaporte</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">NÚMERO DOCUMENTO *</label>
                        <input v-model="form.numero_documento" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">RAZÓN SOCIAL / NOMBRE *</label>
                        <input v-model="form.razon_social" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">NOMBRE COMERCIAL</label>
                        <input v-model="form.nombre_comercial" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">CELULAR</label>
                        <input v-model="form.celular" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
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
                        <label style="font-size:12px; font-weight:600; color:#64748B;">PROVINCIA</label>
                        <input v-model="form.provincia" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">LÍMITE CRÉDITO (S/)</label>
                        <input v-model="form.limite_credito" type="number" step="0.01" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">DESCUENTO (%)</label>
                        <input v-model="form.descuento_porcentaje" type="number" step="0.01" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
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

const props = defineProps({ clientes: { type: Array, default: () => [] } })

const busqueda  = ref('')
const modal     = ref(false)
const editando  = ref(null)
const procesando = ref(false)

const form = ref({
    tipo_documento: 'DNI', numero_documento: '', razon_social: '',
    nombre_comercial: '', celular: '', email: '', direccion: '',
    distrito: '', provincia: '', limite_credito: 0, descuento_porcentaje: 0
})

const clientesFiltrados = computed(() => {
    console.log("Clientes:", props.clientes.map(c => ({doc: c.numero_documento, tipo: typeof c.numero_documento})))
    if (!busqueda.value) return props.clientes
    const q = busqueda.value.toLowerCase().trim()
    return props.clientes.filter(c =>
        c.razon_social?.toLowerCase().includes(q) ||
        String(c.numero_documento || '').toLowerCase().includes(q) ||
        String(c.celular || '').includes(q) ||
        c.nombre_comercial?.toLowerCase().includes(q)
    )
})

const abrirModal = (c = null) => {
    editando.value = c
    form.value = c ? { ...c } : {
        tipo_documento: 'DNI', numero_documento: '', razon_social: '',
        nombre_comercial: '', celular: '', email: '', direccion: '',
        distrito: '', provincia: '', limite_credito: 0, descuento_porcentaje: 0
    }
    modal.value = true
}

const guardar = () => {
    procesando.value = true
    if (editando.value) {
        router.put(`/ferreteria/clientes/${editando.value.id}`, form.value, {
            onSuccess: () => { modal.value = false; procesando.value = false },
            onError:   () => { procesando.value = false }
        })
    } else {
        router.post('/ferreteria/clientes', form.value, {
            onSuccess: () => { modal.value = false; procesando.value = false },
            onError:   () => { procesando.value = false }
        })
    }
}

const eliminar = (c) => {
    if (confirm(`¿Eliminar cliente "${c.razon_social}"?`)) {
        router.delete(`/ferreteria/clientes/${c.id}`)
    }
}
</script>
