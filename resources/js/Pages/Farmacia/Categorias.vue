<template>
    <AppLayout title="Categorías" subtitle="Gestión de categorías de farmacia">

        <div style="display:flex; justify-content:flex-end; margin-bottom:1.5rem;">
            <button @click="abrirModal()"
                style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; font-size:14px; font-weight:600; cursor:pointer;">
                + Nueva Categoría
            </button>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" style="margin-bottom:1rem; padding:14px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; color:#166534; font-size:14px;">
            ✅ {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" style="margin-bottom:1rem; padding:14px; background:#fef2f2; border:1px solid #fecaca; border-radius:10px; color:#dc2626; font-size:14px;">
            ❌ {{ $page.props.flash.error }}
        </div>

        <!-- Grid categorías -->
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px,1fr)); gap:16px;">
            <div v-for="cat in categorias" :key="cat.id"
                style="background:white; border-radius:14px; border:1px solid #E2E8F0; padding:20px; display:flex; flex-direction:column; gap:12px; box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div :style="{ width:'48px', height:'48px', borderRadius:'12px', background:cat.color+'22', display:'flex', alignItems:'center', justifyContent:'center', fontSize:'24px' }">
                            {{ cat.icono }}
                        </div>
                        <div>
                            <p style="font-size:15px; font-weight:700; color:#1E293B; margin:0;">{{ cat.nombre }}</p>
                            <p style="font-size:12px; color:#94A3B8; margin:0;">{{ cat.productos_count }} productos</p>
                        </div>
                    </div>
                    <span :style="cat.activo ? 'padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#dcfce7; color:#166534;' : 'padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#fee2e2; color:#dc2626;'">
                        {{ cat.activo ? 'Activa' : 'Inactiva' }}
                    </span>
                </div>
                <div style="display:flex; gap:8px;">
                    <button @click="abrirModal(cat)"
                        style="flex:1; padding:8px; background:#f1f5f9; color:#475569; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                        ✏️ Editar
                    </button>
                    <button @click="eliminar(cat)"
                        style="padding:8px 12px; background:#fef2f2; color:#dc2626; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                        🗑
                    </button>
                </div>
            </div>

            <div v-if="categorias.length === 0"
                style="grid-column:1/-1; text-align:center; padding:64px; color:#94A3B8;">
                <p style="font-size:48px; margin-bottom:12px;">📦</p>
                <p style="font-size:15px;">No hay categorías registradas</p>
                <button @click="abrirModal()" style="margin-top:16px; padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; font-size:14px; cursor:pointer;">
                    + Crear primera categoría
                </button>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="modal" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
            <div style="background:white; border-radius:16px; padding:28px; width:100%; max-width:440px; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px;">
                    {{ editando ? '✏️ Editar Categoría' : '➕ Nueva Categoría' }}
                </h3>

                <div style="display:flex; flex-direction:column; gap:16px; margin-bottom:20px;">
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Nombre *</label>
                        <input v-model="form.nombre" required style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; color:#1E293B; outline:none; box-sizing:border-box;" placeholder="Ej: Analgésicos, Antibióticos, Vitaminas..." />
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Icono (emoji)</label>
                            <input v-model="form.icono" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:20px; text-align:center; outline:none; box-sizing:border-box;" placeholder="📦" maxlength="4" />
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Color</label>
                            <input v-model="form.color" type="color" style="width:100%; padding:4px; border:1px solid #E2E8F0; border-radius:8px; height:42px; cursor:pointer;" />
                        </div>
                    </div>
                    <div v-if="editando" style="display:flex; align-items:center; gap:10px;">
                        <input type="checkbox" v-model="form.activo" id="activo" style="width:16px; height:16px; accent-color:#14B8A6;" />
                        <label for="activo" style="font-size:13px; color:#374151;">Categoría activa</label>
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
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ categorias: Array })

const modal     = ref(false)
const editando  = ref(null)
const procesando = ref(false)

const form = ref({ nombre: '', icono: '📦', color: '#14B8A6', activo: true })

const abrirModal = (cat = null) => {
    editando.value = cat
    form.value = cat
        ? { nombre: cat.nombre, icono: cat.icono, color: cat.color, activo: cat.activo }
        : { nombre: '', icono: '📦', color: '#14B8A6', activo: true }
    modal.value = true
}

const guardar = () => {
    procesando.value = true
    if (editando.value) {
        router.put(`/farmacia/categorias/${editando.value.id}`, form.value, {
            onSuccess: () => { modal.value = false; procesando.value = false },
            onError:   () => { procesando.value = false },
        })
    } else {
        router.post('/farmacia/categorias', form.value, {
            onSuccess: () => { modal.value = false; procesando.value = false },
            onError:   () => { procesando.value = false },
        })
    }
}

const eliminar = (cat) => {
    if (confirm(`¿Eliminar la categoría "${cat.nombre}"?`)) {
        router.delete(`/farmacia/categorias/${cat.id}`)
    }
}
</script>
