<template>
    <AppLayout title="Categorías" subtitle="Categorías de productos ferretería">

        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <p style="font-size:14px; color:#64748B; margin:0;">{{ categorias.length }} categorías registradas</p>
            <button @click="abrirModal()" style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                + Nueva Categoría
            </button>
        </div>

        <!-- Grid categorías -->
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px,1fr)); gap:16px;">
            <div v-if="categorias.length === 0" style="grid-column:1/-1; text-align:center; padding:60px; color:#94A3B8;">
                No hay categorías registradas
            </div>
            <div v-for="cat in categorias" :key="cat.id"
                style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0; display:flex; flex-direction:column; gap:12px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div :style="{width:'48px', height:'48px', borderRadius:'12px', background: cat.color+'22', display:'flex', alignItems:'center', justifyContent:'center', fontSize:'24px'}">
                        {{ cat.icono }}
                    </div>
                    <div>
                        <p style="font-size:15px; font-weight:700; color:#1E293B; margin:0;">{{ cat.nombre }}</p>
                        <span :style="{fontSize:'11px', fontWeight:'600', padding:'2px 8px', borderRadius:'20px', background: cat.color+'22', color: cat.color}">
                            {{ cat.productos_count || 0 }} productos
                        </span>
                    </div>
                </div>
                <div style="display:flex; gap:8px;">
                    <button @click="abrirModal(cat)" style="flex:1; padding:8px; background:#F0FDFA; color:#0F766E; border:1px solid #CCFBF1; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">✏️ Editar</button>
                    <button @click="eliminar(cat)" style="padding:8px 12px; background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; border-radius:8px; font-size:12px; cursor:pointer;">🗑️</button>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="modal" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:16px; padding:28px; width:420px;">
                <h2 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 20px;">{{ editando ? 'Editar Categoría' : 'Nueva Categoría' }}</h2>

                <div style="display:flex; flex-direction:column; gap:16px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">NOMBRE *</label>
                        <input v-model="form.nombre" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">ÍCONO</label>
                        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:8px;">
                            <button v-for="ic in iconos" :key="ic" @click="form.icono=ic"
                                :style="{padding:'8px', borderRadius:'8px', border:'2px solid', borderColor: form.icono===ic ? '#14B8A6' : '#E2E8F0', background: form.icono===ic ? '#F0FDFA' : 'white', fontSize:'20px', cursor:'pointer'}">
                                {{ ic }}
                            </button>
                        </div>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">COLOR</label>
                        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:8px;">
                            <button v-for="c in colores" :key="c" @click="form.color=c"
                                :style="{width:'32px', height:'32px', borderRadius:'8px', background:c, border: form.color===c ? '3px solid #1E293B' : '2px solid transparent', cursor:'pointer'}">
                            </button>
                        </div>
                    </div>
                </div>

                <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:24px;">
                    <button @click="modal=false" style="padding:10px 20px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#64748B; cursor:pointer; background:white;">Cancelar</button>
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

const props = defineProps({ categorias: { type: Array, default: () => [] } })

const modal      = ref(false)
const editando   = ref(null)
const procesando = ref(false)
const form       = ref({ nombre: '', icono: '🔧', color: '#14B8A6' })

const iconos = ['🔧', '🔨', '⚡', '🪚', '🔩', '🪛', '🪜', '🎨', '🚪', '🪟', '💡', '🔌', '🪣', '🧱', '🔑', '⚙️', '🛠️', '📦']
const colores = ['#14B8A6', '#0F766E', '#3B82F6', '#8B5CF6', '#F59E0B', '#EF4444', '#10B981', '#6366F1', '#EC4899', '#64748B']

const abrirModal = (cat = null) => {
    editando.value = cat
    form.value = cat
        ? { nombre: cat.nombre, icono: cat.icono, color: cat.color }
        : { nombre: '', icono: '🔧', color: '#14B8A6' }
    modal.value = true
}

const guardar = () => {
    procesando.value = true
    if (editando.value) {
        router.put(`/ferreteria/categorias/${editando.value.id}`, form.value, {
            onSuccess: () => { modal.value = false; procesando.value = false },
            onError:   () => { procesando.value = false },
        })
    } else {
        router.post('/ferreteria/categorias', form.value, {
            onSuccess: () => { modal.value = false; procesando.value = false },
            onError:   () => { procesando.value = false },
        })
    }
}

const eliminar = (cat) => {
    if (confirm(`¿Eliminar la categoría "${cat.nombre}"?`)) {
        router.delete(`/ferreteria/categorias/${cat.id}`)
    }
}
</script>
