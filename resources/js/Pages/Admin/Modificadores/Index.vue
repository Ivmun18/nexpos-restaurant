<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    modificadores: Array,
    categorias: Array,
})

function nombreCategoria(modificador) {
    return modificador.categoria?.nombre ?? 'Todas las categorías'
}

const mostrarForm = ref(false)
const editando = ref(null)

const form = useForm({
    nombre: '',
    categoria_id: '',
})

function nuevoModificador() {
    editando.value = null
    form.reset()
    mostrarForm.value = true
}

function editarModificador(modificador) {
    editando.value = modificador
    form.nombre = modificador.nombre
    form.categoria_id = modificador.categoria_id ?? ''
    mostrarForm.value = true
}

function guardar() {
    if (editando.value) {
        form.put(`/admin/modificadores/${editando.value.id}`, {
            onSuccess: () => { mostrarForm.value = false },
        })
    } else {
        form.post('/admin/modificadores', {
            onSuccess: () => { mostrarForm.value = false },
        })
    }
}

function toggleActivo(modificador) {
    router.put(`/admin/modificadores/${modificador.id}`, {
        nombre: modificador.nombre,
        categoria_id: modificador.categoria_id,
        activo: !modificador.activo,
    })
}

function eliminar(modificador) {
    if (!confirm(`¿Eliminar "${modificador.nombre}"? Esta acción no se puede deshacer.`)) return
    router.delete(`/admin/modificadores/${modificador.id}`)
}
</script>

<template>
    <AppLayout title="🧂 Modificadores">
        <div style="max-width:900px; margin:0 auto;">

            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:32px; font-weight:800; color:#1E293B; margin:0;">🧂 Modificadores</h1>
                    <p style="font-size:16px; color:#94A3B8; margin:4px 0 0;">Opciones de pedido por categoría (ej: "Sin ají", "Con chaufa")</p>
                </div>
                <button
                    @click="nuevoModificador"
                    style="background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:14px; padding:14px 24px; font-size:16px; font-weight:700; cursor:pointer; box-shadow:0 4px 15px rgba(20,184,166,0.3);"
                >
                    + Nuevo modificador
                </button>
            </div>

            <!-- Listado -->
            <div style="display:flex; flex-direction:column; gap:10px;">
                <div
                    v-for="m in modificadores"
                    :key="m.id"
                    style="background:white; border-radius:16px; padding:16px 20px; display:flex; align-items:center; justify-content:space-between; gap:12px; box-shadow:0 2px 10px rgba(0,0,0,0.06); border:2px solid #E2E8F0;"
                >
                    <div>
                        <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">{{ m.nombre }}</p>
                        <p style="font-size:13px; color:#94A3B8; margin:2px 0 0;">{{ nombreCategoria(m) }}</p>
                    </div>

                    <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                        <span :style="{
                            padding: '4px 10px', borderRadius: '8px', fontSize: '12px', fontWeight: '700',
                            background: m.activo ? '#DCFCE7' : '#FEE2E2',
                            color: m.activo ? '#166534' : '#991B1B',
                        }">
                            {{ m.activo ? 'Activo' : 'Inactivo' }}
                        </span>
                        <button @click="editarModificador(m)" style="padding:8px 12px; background:#F1F5F9; color:#1E293B; border:none; border-radius:8px; font-weight:700; cursor:pointer;">
                            Editar
                        </button>
                        <button @click="toggleActivo(m)" style="padding:8px 12px; background:#F1F5F9; color:#1E293B; border:none; border-radius:8px; font-weight:700; cursor:pointer;">
                            {{ m.activo ? 'Desactivar' : 'Activar' }}
                        </button>
                        <button @click="eliminar(m)" style="padding:8px 10px; background:#FEF2F2; color:#991B1B; border:none; border-radius:8px; font-weight:700; cursor:pointer;">
                            🗑
                        </button>
                    </div>
                </div>

                <div v-if="!modificadores.length" style="text-align:center; padding:60px 0; color:#94A3B8;">
                    <p style="font-size:40px; margin:0 0 10px;">🧂</p>
                    <p>Aún no hay modificadores. Crea el primero.</p>
                </div>
            </div>

            <!-- Form crear/editar -->
            <div v-if="mostrarForm" style="position:fixed; inset:0; background:rgba(15,23,42,0.5); display:flex; align-items:center; justify-content:center; z-index:50;" @click.self="mostrarForm = false">
                <div style="background:white; border-radius:20px; padding:28px; width:400px; max-width:90vw;">
                    <p style="font-size:20px; font-weight:800; color:#1E293B; margin:0 0 20px;">
                        {{ editando ? 'Editar modificador' : 'Nuevo modificador' }}
                    </p>

                    <div style="margin-bottom:14px;">
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Nombre</label>
                        <input v-model="form.nombre" type="text" placeholder="Ej: Sin ají" style="width:100%; padding:10px 14px; border:1px solid #E2E8F0; border-radius:10px; margin-top:4px; box-sizing:border-box;" />
                        <p v-if="form.errors.nombre" style="color:#EF4444; font-size:13px; margin:4px 0 0;">{{ form.errors.nombre }}</p>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Categoría</label>
                        <select v-model="form.categoria_id" style="width:100%; padding:10px 14px; border:1px solid #E2E8F0; border-radius:10px; margin-top:4px; box-sizing:border-box; background:white;">
                            <option value="">Todas las categorías</option>
                            <option v-for="c in categorias" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                        </select>
                        <p v-if="form.errors.categoria_id" style="color:#EF4444; font-size:13px; margin:4px 0 0;">{{ form.errors.categoria_id }}</p>
                    </div>

                    <div style="display:flex; gap:10px;">
                        <button @click="mostrarForm = false" style="flex:1; padding:12px; background:#F1F5F9; color:#1E293B; border:none; border-radius:12px; font-weight:700; cursor:pointer;">
                            Cancelar
                        </button>
                        <button @click="guardar" :disabled="form.processing" style="flex:1; padding:12px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; font-weight:700; cursor:pointer;">
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
