<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ productos: Array })

const showForm = ref(false)
const editando = ref(null)
const form = ref({ nombre: '', descripcion: '', categoria: 'minibar', precio: 0, stock: 0, controla_stock: false, activo: true })

const categorias = [
    { valor: 'minibar',      label: '🍷 Minibar' },
    { valor: 'room_service', label: '🍽️ Room Service' },
    { valor: 'lavanderia',   label: '👔 Lavandería' },
    { valor: 'otros',        label: '📦 Otros' },
]

const abrir = (p = null) => {
    editando.value = p
    form.value = p
        ? { nombre: p.nombre, descripcion: p.descripcion, categoria: p.categoria, precio: p.precio, stock: p.stock, controla_stock: p.controla_stock, activo: p.activo }
        : { nombre: '', descripcion: '', categoria: 'minibar', precio: 0, stock: 0, controla_stock: false, activo: true }
    showForm.value = true
}

const guardar = () => {
    if (editando.value) {
        router.put('/hotel/productos/' + editando.value.id, form.value, { onSuccess: () => { showForm.value = false } })
    } else {
        router.post('/hotel/productos', form.value, { onSuccess: () => { showForm.value = false } })
    }
}

const eliminar = (id) => {
    if (!confirm('¿Eliminar producto?')) return
    router.delete('/hotel/productos/' + id)
}

const colorCategoria = (c) => ({
    minibar: '#7C3AED', room_service: '#0891B2', lavanderia: '#0369A1', otros: '#374151'
}[c] || '#374151')
</script>

<template>
    <AppLayout title="Productos Hotel">
        <div style="padding:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:22px; font-weight:700; margin:0;">🛒 Productos del Hotel</h1>
                    <p style="color:#64748B; font-size:13px; margin:4px 0 0;">Minibar, room service, lavandería y más</p>
                </div>
                <button @click="abrir()" style="background:#3B82F6; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer;">+ Nuevo Producto</button>
            </div>

            <!-- Lista productos -->
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px,1fr)); gap:14px;">
                <div v-for="p in productos" :key="p.id"
                    style="background:#fff; border-radius:12px; border:1px solid #E2E8F0; padding:16px;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                        <div>
                            <div style="font-weight:700; font-size:14px;">{{ p.nombre }}</div>
                            <span :style="{fontSize:'11px', fontWeight:'600', color: colorCategoria(p.categoria),
                                background: colorCategoria(p.categoria)+'15', padding:'2px 8px', borderRadius:'20px'}">
                                {{ categorias.find(c=>c.valor===p.categoria)?.label || p.categoria }}
                            </span>
                        </div>
                        <div style="font-size:18px; font-weight:700; color:#16A34A;">S/ {{ Number(p.precio).toFixed(2) }}</div>
                    </div>
                    <div v-if="p.descripcion" style="font-size:12px; color:#64748B; margin-bottom:8px;">{{ p.descripcion }}</div>
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div v-if="p.controla_stock" style="font-size:12px; color:#374151;">
                            Stock: <b>{{ p.stock }}</b>
                        </div>
                        <div v-else style="font-size:12px; color:#94A3B8;">Sin control stock</div>
                        <div style="display:flex; gap:8px;">
                            <button @click="abrir(p)" style="background:#F1F5F9; border:none; padding:5px 12px; border-radius:6px; font-size:12px; cursor:pointer;">✏️ Editar</button>
                            <button @click="eliminar(p.id)" style="background:#FEF2F2; color:#DC2626; border:none; padding:5px 12px; border-radius:6px; font-size:12px; cursor:pointer;">🗑️</button>
                        </div>
                    </div>
                </div>
                <div v-if="productos.length === 0" style="grid-column:1/-1; text-align:center; padding:40px; color:#94A3B8;">
                    No hay productos. Agrega tu primer producto del hotel.
                </div>
            </div>

            <!-- Modal -->
            <div v-if="showForm" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
                <div style="background:#fff; border-radius:16px; padding:28px; width:480px; max-height:90vh; overflow-y:auto;">
                    <h2 style="font-size:18px; font-weight:700; margin:0 0 20px;">{{ editando ? "✏️ Editar" : "➕ Nuevo" }} Producto</h2>
                    <div style="display:grid; gap:14px;">
                        <div>
                            <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">Nombre *</label>
                            <input v-model="form.nombre" placeholder="Coca Cola 500ml, Vino tinto..." style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; box-sizing:border-box;" />
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">Categoría</label>
                            <select v-model="form.categoria" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px;">
                                <option v-for="c in categorias" :key="c.valor" :value="c.valor">{{ c.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">Descripción</label>
                            <input v-model="form.descripcion" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; box-sizing:border-box;" />
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div>
                                <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">Precio S/ *</label>
                                <input type="number" v-model="form.precio" min="0" step="0.50" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label style="font-size:12px; font-weight:600; display:block; margin-bottom:4px;">Stock inicial</label>
                                <input type="number" v-model="form.stock" min="0" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; box-sizing:border-box;" />
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <input type="checkbox" v-model="form.controla_stock" id="ctrl_stock" />
                            <label for="ctrl_stock" style="font-size:13px; font-weight:600;">Controlar stock</label>
                        </div>
                    </div>
                    <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
                        <button @click="showForm=false" style="background:#F1F5F9; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600;">Cancelar</button>
                        <button @click="guardar" style="background:#3B82F6; color:#fff; border:none; padding:10px 24px; border-radius:8px; cursor:pointer; font-weight:600;">✅ Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
