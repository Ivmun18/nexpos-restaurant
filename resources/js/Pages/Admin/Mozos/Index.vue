<template>
    <AppLayout title="Gestión de mozos" subtitle="Administra tu equipo de atención">
        <div style="display:flex; gap:24px; margin-bottom:24px; align-items:center; justify-content:space-between;">
            <div style="display:flex; gap:16px; flex:1;">
                <!-- Buscador -->
                <input
                    v-model="buscar"
                    type="text"
                    placeholder="🔍 Buscar por nombre, DNI o email..."
                    style="flex:1; padding:14px 18px; border:2px solid #E2E8F0; border-radius:12px; font-size:15px; outline:none; background:white;"
                />
                
                <!-- Filtro estado -->
                <select
                    v-model="estado"
                    style="padding:14px 18px; border:2px solid #E2E8F0; border-radius:12px; font-size:15px; outline:none; background:white; cursor:pointer; min-width:150px;"
                >
                    <option value="">Todos</option>
                    <option value="1">✅ Activos</option>
                    <option value="0">⏸ Inactivos</option>
                </select>
            </div>

            <!-- Botón nuevo -->
            <button
                @click="abrirNuevo"
                style="padding:14px 28px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; font-size:16px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(20,184,166,0.3);"
            >
                ➕ Nuevo mozo
            </button>
        </div>

        <!-- Stats rápidos -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px;">
            <div style="background:white; border-radius:16px; padding:20px; border:2px solid #E2E8F0;">
                <p style="font-size:13px; color:#64748B; margin:0 0 8px;">Total mozos</p>
                <p style="font-size:28px; font-weight:800; color:#1E293B; margin:0;">{{ mozos.total }}</p>
            </div>
            <div style="background:white; border-radius:16px; padding:20px; border:2px solid #E2E8F0;">
                <p style="font-size:13px; color:#64748B; margin:0 0 8px;">Activos</p>
                <p style="font-size:28px; font-weight:800; color:#14B8A6; margin:0;">{{ mozos.data.filter(m => m.activo).length }}</p>
            </div>
            <div style="background:white; border-radius:16px; padding:20px; border:2px solid #E2E8F0;">
                <p style="font-size:13px; color:#64748B; margin:0 0 8px;">Inactivos</p>
                <p style="font-size:28px; font-weight:800; color:#94A3B8; margin:0;">{{ mozos.data.filter(m => !m.activo).length }}</p>
            </div>
        </div>

        <!-- Tabla -->
        <div style="background:white; border-radius:20px; border:2px solid #E2E8F0; overflow:hidden;">
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#F8FAFC; border-bottom:2px solid #E2E8F0;">
                            <th style="padding:16px 20px; text-align:left; font-size:13px; font-weight:700; color:#64748B; text-transform:uppercase;">Mozo</th>
                            <th style="padding:16px 20px; text-align:left; font-size:13px; font-weight:700; color:#64748B; text-transform:uppercase;">Contacto</th>
                            <th style="padding:16px 20px; text-align:center; font-size:13px; font-weight:700; color:#64748B; text-transform:uppercase;">Mesas</th>
                            <th style="padding:16px 20px; text-align:center; font-size:13px; font-weight:700; color:#64748B; text-transform:uppercase;">Pedidos</th>
                            <th style="padding:16px 20px; text-align:center; font-size:13px; font-weight:700; color:#64748B; text-transform:uppercase;">Estado</th>
                            <th style="padding:16px 20px; text-align:right; font-size:13px; font-weight:700; color:#64748B; text-transform:uppercase;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="m in mozos.data" :key="m.id" style="border-bottom:1px solid #F1F5F9; transition:background 0.15s;" @mouseenter="$event.currentTarget.style.background='#F8FAFC'" @mouseleave="$event.currentTarget.style.background='white'">
                            <td style="padding:16px 20px;">
                                <a :href="`/admin/mozos/${m.id}`" style="text-decoration:none;">
                                    <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 4px; cursor:pointer;" @mouseenter="$event.currentTarget.style.color='#14B8A6'" @mouseleave="$event.currentTarget.style.color='#1E293B'">{{ m.name }}</p>
                                    <p style="font-size:13px; color:#94A3B8; margin:0;">{{ m.email }}</p>
                                </a>
                            </td>
                            <td style="padding:16px 20px;">
                                <p style="font-size:14px; color:#475569; margin:0 0 4px;">DNI: {{ m.dni || '—' }}</p>
                                <p style="font-size:13px; color:#94A3B8; margin:0;">📞 {{ m.telefono || '—' }}</p>
                            </td>
                            <td style="padding:16px 20px; text-align:center;">
                                <span style="display:inline-block; padding:6px 14px; background:#F0FDFA; color:#0F766E; border-radius:20px; font-size:14px; font-weight:700;">
                                    {{ m.mesas_count }}
                                </span>
                            </td>
                            <td style="padding:16px 20px; text-align:center;">
                                <span style="font-size:16px; font-weight:700; color:#475569;">{{ m.pedidos_count }}</span>
                            </td>
                            <td style="padding:16px 20px; text-align:center;">
                                <span :style="{
                                    display: 'inline-block',
                                    padding: '6px 14px',
                                    borderRadius: '20px',
                                    fontSize: '14px',
                                    fontWeight: '700',
                                    background: m.activo ? '#F0FDF4' : '#F1F5F9',
                                    color: m.activo ? '#166534' : '#64748B',
                                }">
                                    {{ m.activo ? '✅ Activo' : '⏸ Inactivo' }}
                                </span>
                            </td>
                            <td style="padding:16px 20px; text-align:right;">
                                <div style="display:flex; gap:8px; justify-content:flex-end;">
                                    <button
                                        @click="abrirEditar(m)"
                                        style="padding:10px 14px; background:#F1F5F9; color:#475569; border:none; border-radius:10px; font-size:15px; cursor:pointer; font-weight:600;"
                                    >✏️</button>
                                    <button
                                        @click="toggleActivo(m)"
                                        :style="{
                                            padding: '10px 14px',
                                            background: m.activo ? '#FEF2F2' : '#F0FDF4',
                                            color: m.activo ? '#DC2626' : '#16A34A',
                                            border: 'none',
                                            borderRadius: '10px',
                                            fontSize: '15px',
                                            cursor: 'pointer',
                                            fontWeight: '600',
                                        }"
                                    >
                                        {{ m.activo ? '🚫' : '✅' }}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Sin resultados -->
                        <tr v-if="mozos.data.length === 0">
                            <td colspan="6" style="padding:80px 20px; text-align:center;">
                                <p style="font-size:56px; margin:0 0 16px;">👨‍🍳</p>
                                <p style="font-size:20px; color:#CBD5E1; margin:0 0 24px;">No hay mozos registrados</p>
                                <button
                                    @click="abrirNuevo"
                                    style="padding:14px 28px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; font-size:16px; font-weight:700; cursor:pointer;"
                                >
                                    ➕ Registrar primer mozo
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div v-if="mozos.last_page > 1" style="padding:20px; border-top:2px solid #E2E8F0; display:flex; justify-content:center; gap:8px;">
                <a
                    v-for="link in mozos.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    v-html="link.label"
                    :style="{
                        padding: '10px 16px',
                        borderRadius: '10px',
                        fontSize: '14px',
                        fontWeight: '600',
                        textDecoration: 'none',
                        background: link.active ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : 'white',
                        color: link.active ? 'white' : '#64748B',
                        border: link.active ? 'none' : '2px solid #E2E8F0',
                        opacity: link.url ? '1' : '0.4',
                        pointerEvents: link.url ? 'auto' : 'none',
                        cursor: link.url ? 'pointer' : 'not-allowed',
                    }"
                />
            </div>
        </div>

        <!-- ══ MODAL MOZO ══ -->
        <Teleport to="body">
            <div v-if="showForm"
                style="position:fixed; inset:0; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:9999; padding:20px;"
                @click.self="showForm=false">
                <div style="background:white; border-radius:24px; padding:32px; width:100%; max-width:560px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 60px rgba(0,0,0,0.25);">
                    <h2 style="font-size:22px; font-weight:800; color:#1E293B; margin:0 0 24px;">
                        {{ mozoEditar ? '✏️ Editar mozo' : '➕ Nuevo mozo' }}
                    </h2>

                    <!-- Nombre -->
                    <div style="margin-bottom:16px;">
                        <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Nombre completo *</label>
                        <input v-model="form.name" type="text" required
                            style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;" />
                        <p v-if="form.errors.name" style="font-size:13px; color:#EF4444; margin:4px 0 0;">{{ form.errors.name }}</p>
                    </div>

                    <!-- DNI + Teléfono -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                        <div>
                            <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">DNI</label>
                            <input v-model="form.dni" type="text"
                                style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;" />
                        </div>
                        <div>
                            <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Teléfono</label>
                            <input v-model="form.telefono" type="text"
                                style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;" />
                        </div>
                    </div>

                    <!-- Email -->
                    <div style="margin-bottom:16px;">
                        <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Email *</label>
                        <input v-model="form.email" type="email" required
                            style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;" />
                        <p v-if="form.errors.email" style="font-size:13px; color:#EF4444; margin:4px 0 0;">{{ form.errors.email }}</p>
                    </div>

                    <!-- Contraseña -->
                    <div style="margin-bottom:16px;">
                        <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">
                            Contraseña {{ mozoEditar ? '(dejar vacío para no cambiar)' : '*' }}
                        </label>
                        <input v-model="form.password" type="password" :required="!mozoEditar"
                            style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;" />
                        <p v-if="form.errors.password" style="font-size:13px; color:#EF4444; margin:4px 0 0;">{{ form.errors.password }}</p>
                    </div>

                    <!-- Fecha ingreso -->
                    <div style="margin-bottom:16px;">
                        <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Fecha de ingreso</label>
                        <input v-model="form.fecha_ingreso" type="date"
                            style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;" />
                    </div>

                    <!-- Observaciones -->
                    <div style="margin-bottom:24px;">
                        <label style="font-size:14px; font-weight:600; color:#475569; display:block; margin-bottom:8px;">Observaciones</label>
                        <textarea v-model="form.observaciones" rows="3"
                            style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; resize:vertical; box-sizing:border-box;"></textarea>
                    </div>

                    <!-- Botones -->
                    <div style="display:flex; gap:12px;">
                        <button @click="showForm=false"
                            style="flex:1; padding:14px; background:#F1F5F9; color:#475569; border:none; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer;">
                            Cancelar
                        </button>
                        <button @click="guardar" :disabled="form.processing"
                            style="flex:1; padding:14px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; font-size:16px; font-weight:700; cursor:pointer;">
                            {{ form.processing ? 'Guardando...' : 'Guardar' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    mozos: Object,
    filtros: Object,
});

const buscar = ref(props.filtros.buscar ?? '');
const estado = ref(props.filtros.estado ?? '');
const showForm = ref(false);
const mozoEditar = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    dni: '',
    telefono: '',
    fecha_ingreso: '',
    observaciones: '',
});

const aplicarFiltros = debounce(() => {
    router.get('/admin/mozos',
        { buscar: buscar.value || undefined, estado: estado.value === '' ? undefined : estado.value },
        { preserveState: true, preserveScroll: true, replace: true }
    );
}, 300);

watch([buscar, estado], aplicarFiltros);

function abrirNuevo() {
    mozoEditar.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
}

function abrirEditar(m) {
    mozoEditar.value = m;
    form.name = m.name;
    form.email = m.email;
    form.password = '';
    form.dni = m.dni || '';
    form.telefono = m.telefono || '';
    form.fecha_ingreso = m.fecha_ingreso || '';
    form.observaciones = m.observaciones || '';
    form.clearErrors();
    showForm.value = true;
}

function guardar() {
    if (mozoEditar.value) {
        form.put(`/admin/mozos/${mozoEditar.value.id}`, {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; },
        });
    } else {
        form.post('/admin/mozos', {
            preserveScroll: true,
            onSuccess: () => { showForm.value = false; },
        });
    }
}

function toggleActivo(m) {
    if (!confirm(`¿${m.activo ? 'Desactivar' : 'Activar'} a ${m.name}?`)) return;
    router.patch(`/admin/mozos/${m.id}/toggle`, {}, { preserveScroll: true });
}
</script>
