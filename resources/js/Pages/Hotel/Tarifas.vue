<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ tarifas: Array, tipos: Array })

const showForm = ref(false)
const editando = ref(null)
const form = ref({ nombre: '', tipo_id: '', fecha_inicio: '', fecha_fin: '', precio_noche: '', color: '#3B82F6', descripcion: '' })

const abrirNueva = () => {
    editando.value = null
    form.value = { nombre: '', tipo_id: '', fecha_inicio: '', fecha_fin: '', precio_noche: '', color: '#3B82F6', descripcion: '' }
    showForm.value = true
}

const abrirEditar = (t) => {
    editando.value = t
    form.value = {
        nombre: t.nombre, tipo_id: t.tipo_id ?? '',
        fecha_inicio: t.fecha_inicio?.slice(0,10),
        fecha_fin: t.fecha_fin?.slice(0,10),
        precio_noche: t.precio_noche, color: t.color ?? '#3B82F6',
        descripcion: t.descripcion ?? ''
    }
    showForm.value = true
}

const guardar = () => {
    if (editando.value) {
        router.put('/hotel/tarifas/' + editando.value.id, form.value, { onSuccess: () => showForm.value = false })
    } else {
        router.post('/hotel/tarifas', form.value, { onSuccess: () => showForm.value = false })
    }
}

const eliminar = (id) => {
    if (confirm('¿Eliminar esta tarifa?')) router.delete('/hotel/tarifas/' + id)
}

const toggleActivo = (t) => {
    router.put('/hotel/tarifas/' + t.id, { ...t, activo: !t.activo, tipo_id: t.tipo_id ?? '' })
}

const fmt = (f) => f ? new Date(f+'T00:00:00').toLocaleDateString('es-PE') : '-'
const estaActiva = (t) => {
    const hoy = new Date().toISOString().slice(0,10)
    return t.activo && t.fecha_inicio <= hoy && t.fecha_fin >= hoy
}
</script>

<template>
    <AppLayout title="Tarifas por Temporada">
        <div style="padding:24px; font-family:'Inter',sans-serif; max-width:1000px;">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:24px; font-weight:800; color:#1E293B; margin:0;">🗓️ Tarifas por Temporada</h1>
                    <p style="font-size:13px; color:#64748B; margin:4px 0 0;">Define precios especiales para fechas específicas</p>
                </div>
                <button @click="abrirNueva"
                    style="background:linear-gradient(135deg,#3B82F6,#1D4ED8); color:#fff; border:none; padding:10px 20px; border-radius:10px; font-weight:700; cursor:pointer; font-size:14px;">
                    + Nueva Tarifa
                </button>
            </div>

            <!-- Info -->
            <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:10px; padding:14px 16px; margin-bottom:20px; font-size:13px; color:#1D4ED8;">
                💡 Las tarifas de temporada <b>sobreescriben el precio base</b> del tipo de habitación al hacer check-in. Si defines una tarifa para un tipo específico, tiene prioridad sobre la tarifa general.
            </div>

            <!-- Lista vacía -->
            <div v-if="tarifas.length === 0"
                style="background:#fff; border-radius:12px; padding:60px; text-align:center; color:#94A3B8; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                <div style="font-size:48px; margin-bottom:12px;">🗓️</div>
                <div style="font-size:16px; font-weight:600; margin-bottom:6px;">Sin tarifas configuradas</div>
                <div style="font-size:13px;">Crea tu primera tarifa para fiestas patrias, temporada alta, etc.</div>
            </div>

            <!-- Tabla tarifas -->
            <div v-else style="background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.08); overflow:hidden;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#F8FAFC;">
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">TEMPORADA</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">TIPO HAB.</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">DESDE</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">HASTA</th>
                            <th style="padding:12px 16px; text-align:right; font-size:11px; color:#64748B; font-weight:700;">PRECIO/NOCHE</th>
                            <th style="padding:12px 16px; text-align:center; font-size:11px; color:#64748B; font-weight:700;">ESTADO</th>
                            <th style="padding:12px 16px; text-align:center; font-size:11px; color:#64748B; font-weight:700;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="t in tarifas" :key="t.id" style="border-bottom:1px solid #F1F5F9;">
                            <td style="padding:12px 16px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div :style="{width:'12px', height:'12px', borderRadius:'50%', background: t.color, flexShrink:0}"></div>
                                    <div>
                                        <div style="font-size:13px; font-weight:600; color:#1E293B;">{{ t.nombre }}</div>
                                        <div v-if="t.descripcion" style="font-size:11px; color:#94A3B8;">{{ t.descripcion }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:12px 16px; font-size:13px; color:#374151;">
                                {{ t.tipo ? t.tipo.nombre : '🌐 Todos los tipos' }}
                            </td>
                            <td style="padding:12px 16px; font-size:13px; color:#374151;">{{ fmt(t.fecha_inicio) }}</td>
                            <td style="padding:12px 16px; font-size:13px; color:#374151;">{{ fmt(t.fecha_fin) }}</td>
                            <td style="padding:12px 16px; text-align:right; font-size:15px; font-weight:800; color:#1E293B;">
                                S/ {{ Number(t.precio_noche).toFixed(2) }}
                            </td>
                            <td style="padding:12px 16px; text-align:center;">
                                <span v-if="estaActiva(t)"
                                    style="background:#DCFCE7; color:#16A34A; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;">
                                    🟢 Activa ahora
                                </span>
                                <span v-else-if="t.activo"
                                    style="background:#FEF9C3; color:#CA8A04; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;">
                                    ⏳ Programada
                                </span>
                                <span v-else
                                    style="background:#F1F5F9; color:#94A3B8; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;">
                                    ⏸ Inactiva
                                </span>
                            </td>
                            <td style="padding:12px 16px; text-align:center;">
                                <div style="display:flex; gap:6px; justify-content:center;">
                                    <button @click="abrirEditar(t)"
                                        style="padding:5px 12px; background:#EFF6FF; color:#3B82F6; border:1px solid #BFDBFE; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        ✏️ Editar
                                    </button>
                                    <button @click="toggleActivo(t)"
                                        style="padding:5px 12px; background:#F8FAFC; color:#64748B; border:1px solid #E2E8F0; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        {{ t.activo ? '⏸ Desactivar' : '▶ Activar' }}
                                    </button>
                                    <button @click="eliminar(t.id)"
                                        style="padding:5px 12px; background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        🗑️
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal form -->
            <div v-if="showForm" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
                <div style="background:#fff; border-radius:16px; padding:28px; width:500px; max-height:90vh; overflow-y:auto;">
                    <h2 style="font-size:18px; font-weight:700; margin:0 0 20px;">{{ editando ? '✏️ Editar Tarifa' : '+ Nueva Tarifa' }}</h2>
                    <div style="display:grid; gap:14px;">
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">Nombre de la temporada</label>
                            <input v-model="form.nombre" placeholder="Ej: Fiestas Patrias, Temporada Alta..."
                                style="width:100%; padding:9px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:13px;" />
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">Tipo de habitación</label>
                            <select v-model="form.tipo_id" style="width:100%; padding:9px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:13px;">
                                <option value="">🌐 Todos los tipos</option>
                                <option v-for="t in tipos" :key="t.id" :value="t.id">{{ t.nombre }}</option>
                            </select>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div>
                                <label style="font-size:12px; font-weight:600; color:#374151;">Fecha inicio</label>
                                <input type="date" v-model="form.fecha_inicio"
                                    style="width:100%; padding:9px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:13px;" />
                            </div>
                            <div>
                                <label style="font-size:12px; font-weight:600; color:#374151;">Fecha fin</label>
                                <input type="date" v-model="form.fecha_fin"
                                    style="width:100%; padding:9px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:13px;" />
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr auto; gap:12px; align-items:end;">
                            <div>
                                <label style="font-size:12px; font-weight:600; color:#374151;">Precio por noche (S/)</label>
                                <input type="number" step="0.01" min="0" v-model="form.precio_noche"
                                    placeholder="0.00"
                                    style="width:100%; padding:9px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:16px; font-weight:700;" />
                            </div>
                            <div>
                                <label style="font-size:12px; font-weight:600; color:#374151;">Color</label>
                                <input type="color" v-model="form.color"
                                    style="width:48px; height:40px; padding:2px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; cursor:pointer;" />
                            </div>
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151;">Descripción (opcional)</label>
                            <input v-model="form.descripcion" placeholder="Notas sobre esta temporada..."
                                style="width:100%; padding:9px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:13px;" />
                        </div>
                    </div>
                    <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
                        <button @click="showForm = false"
                            style="padding:10px 20px; background:#F1F5F9; color:#374151; border:none; border-radius:8px; font-weight:600; cursor:pointer;">
                            Cancelar
                        </button>
                        <button @click="guardar"
                            style="padding:10px 24px; background:#3B82F6; color:#fff; border:none; border-radius:8px; font-weight:700; cursor:pointer;">
                            ✅ Guardar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
