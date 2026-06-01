<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ tipos: Array })
const showForm = ref(false)
const editando = ref(null)
const form = ref({ nombre: '', descripcion: '', precio_noche: '', capacidad: 2, activo: true })

const abrir = (t = null) => {
    editando.value = t
    form.value = t ? { nombre: t.nombre, descripcion: t.descripcion, precio_noche: t.precio_noche, capacidad: t.capacidad, activo: t.activo } : { nombre: '', descripcion: '', precio_noche: '', capacidad: 2, activo: true }
    showForm.value = true
}

const guardar = async () => {
    const url = editando.value ? '/hotel/tipos/' + editando.value.id : '/hotel/tipos'
    const method = editando.value ? 'PUT' : 'POST'
    const res = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify(form.value) })
    if (res.ok) { showForm.value = false; router.reload() }
}
</script>
<template>
    <AppLayout title="Tipos de Habitación">
        <div style="padding:24px; font-family:'Inter',sans-serif;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <h1 style="font-size:24px; font-weight:700; color:#1E293B; margin:0;">🏷️ Tipos de Habitación</h1>
                <button @click="abrir()" style="background:#3B82F6; color:#fff; border:none; padding:10px 20px; border-radius:8px; font-weight:600; cursor:pointer;">➕ Nuevo Tipo</button>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px;">
                <div v-if="tipos.length === 0" style="grid-column:1/-1; background:#fff; border-radius:12px; padding:40px; text-align:center; color:#94A3B8;">
                    No hay tipos registrados. Agrega uno.
                </div>
                <div v-for="t in tipos" :key="t.id" style="background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                        <div style="font-size:16px; font-weight:700; color:#1E293B;">{{ t.nombre }}</div>
                        <span :style="{background: t.activo ? '#DCFCE7' : '#F1F5F9', color: t.activo ? '#16A34A' : '#6B7280', padding:'2px 8px', borderRadius:'20px', fontSize:'11px', fontWeight:'600'}">{{ t.activo ? 'Activo' : 'Inactivo' }}</span>
                    </div>
                    <div style="font-size:13px; color:#64748B; margin-bottom:8px;">{{ t.descripcion }}</div>
                    <div style="display:flex; gap:16px; margin-bottom:12px;">
                        <div><span style="font-size:11px; color:#94A3B8;">Precio/noche</span><div style="font-size:18px; font-weight:700; color:#10B981;">S/ {{ Number(t.precio_noche).toFixed(2) }}</div></div>
                        <div><span style="font-size:11px; color:#94A3B8;">Capacidad</span><div style="font-size:18px; font-weight:700; color:#1E293B;">{{ t.capacidad }} pax</div></div>
                    </div>
                    <button @click="abrir(t)" style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; padding:6px 16px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; width:100%;">✏️ Editar</button>
                </div>
            </div>
            <div v-if="showForm" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
                <div style="background:#fff; border-radius:16px; padding:28px; width:420px;">
                    <h2 style="font-size:18px; font-weight:700; margin:0 0 20px;">{{ editando ? '✏️ Editar' : '➕ Nuevo' }} Tipo</h2>
                    <div style="display:grid; gap:12px;">
                        <div><label style="font-size:12px; font-weight:600;">Nombre</label>
                            <input v-model="form.nombre" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" placeholder="Simple, Doble, Suite..." />
                        </div>
                        <div><label style="font-size:12px; font-weight:600;">Descripción</label>
                            <textarea v-model="form.descripcion" rows="2" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;"></textarea>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div><label style="font-size:12px; font-weight:600;">Precio/noche (S/)</label>
                                <input type="number" v-model="form.precio_noche" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" placeholder="150.00" />
                            </div>
                            <div><label style="font-size:12px; font-weight:600;">Capacidad (pax)</label>
                                <input type="number" v-model="form.capacidad" style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px;" />
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <input type="checkbox" v-model="form.activo" id="activo" />
                            <label for="activo" style="font-size:13px; font-weight:600;">Activo</label>
                        </div>
                    </div>
                    <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
                        <button @click="showForm = false" style="background:#F1F5F9; color:#374151; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600;">Cancelar</button>
                        <button @click="guardar" style="background:#3B82F6; color:#fff; border:none; padding:10px 24px; border-radius:8px; cursor:pointer; font-weight:600;">✅ Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
