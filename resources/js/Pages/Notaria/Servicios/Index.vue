<template>
  <AppLayout title="Servicios Notariales" subtitle="Administra los servicios y precios">
    <div style="max-width:900px;margin:0 auto;padding:24px 16px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
        <div>
          <h1 style="margin:0;font-size:22px;font-weight:700;color:#1E293B;">Servicios Notariales</h1>
          <p style="margin:4px 0 0;font-size:13px;color:#64748B;">Administra los servicios y sus precios para la facturacion rapida</p>
        </div>
        <button @click="mostrarFormNuevo=true" style="background:#10B981;color:white;border:none;border-radius:8px;padding:10px 18px;font-size:14px;font-weight:600;cursor:pointer;">+ Nuevo Servicio</button>
      </div>
      <div v-if="mostrarFormNuevo" style="background:#F8FAFC;border:2px solid #10B981;border-radius:12px;padding:20px;margin-bottom:24px;">
        <h3 style="margin:0 0 16px;font-size:15px;font-weight:700;">Nuevo Servicio</h3>
        <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
          <div style="flex:1;min-width:200px;">
            <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Nombre</label>
            <input v-model="formNuevo.nombre" type="text" placeholder="Ej: Legalizacion de Firma" style="width:100%;padding:9px 12px;border:1px solid #D1D5DB;border-radius:8px;font-size:14px;box-sizing:border-box;" />
          </div>
          <div style="width:140px;">
            <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Precio (S/)</label>
            <input v-model="formNuevo.precio" type="number" step="0.01" min="0" placeholder="0.00" style="width:100%;padding:9px 12px;border:1px solid #D1D5DB;border-radius:8px;font-size:14px;box-sizing:border-box;" />
          </div>
          <div style="display:flex;gap:8px;">
            <button @click="guardarNuevo" style="background:#10B981;color:white;border:none;border-radius:8px;padding:9px 16px;font-size:14px;font-weight:600;cursor:pointer;">Guardar</button>
            <button @click="mostrarFormNuevo=false" style="background:#F1F5F9;color:#64748B;border:none;border-radius:8px;padding:9px 16px;font-size:14px;cursor:pointer;">Cancelar</button>
          </div>
        </div>
      </div>
      <input v-model="buscar" type="text" placeholder="Buscar servicio..." style="width:100%;padding:10px 14px;border:1px solid #D1D5DB;border-radius:8px;font-size:14px;margin-bottom:16px;box-sizing:border-box;" />
      <div style="background:white;border-radius:12px;border:1px solid #E2E8F0;overflow:hidden;">
        <div style="display:grid;grid-template-columns:1fr 130px 80px 110px;padding:10px 16px;background:#F8FAFC;border-bottom:1px solid #E2E8F0;">
          <span style="font-size:12px;font-weight:700;color:#64748B;">SERVICIO</span>
          <span style="font-size:12px;font-weight:700;color:#64748B;text-align:right;">PRECIO</span>
          <span style="font-size:12px;font-weight:700;color:#64748B;text-align:center;">ESTADO</span>
          <span style="font-size:12px;font-weight:700;color:#64748B;text-align:center;">ACCIONES</span>
        </div>
        <div v-if="!serviciosFiltrados.length" style="padding:32px;text-align:center;color:#94A3B8;font-size:14px;">No hay servicios registrados</div>
        <div v-for="s in serviciosFiltrados" :key="s.id" style="display:grid;grid-template-columns:1fr 130px 80px 110px;padding:12px 16px;border-bottom:1px solid #F1F5F9;align-items:center;">
          <div>
            <input v-if="editando && editando.id===s.id" v-model="editando.nombre" type="text" style="width:100%;padding:6px 10px;border:1px solid #10B981;border-radius:6px;font-size:13px;box-sizing:border-box;" />
            <span v-else style="font-size:14px;color:#1E293B;font-weight:500;">{{ s.nombre }}</span>
          </div>
          <div style="text-align:right;">
            <input v-if="editando && editando.id===s.id" v-model="editando.precio" type="number" step="0.01" min="0" style="width:90px;padding:6px 8px;border:1px solid #10B981;border-radius:6px;font-size:13px;text-align:right;" />
            <span v-else style="font-size:14px;font-weight:600;color:#10B981;">S/ {{ Number(s.precio).toFixed(2) }}</span>
          </div>
          <div style="text-align:center;">
            <span :style="s.activo ? 'background:#F0FDF4;color:#166534;font-size:11px;padding:3px 8px;border-radius:20px;font-weight:600;' : 'background:#FEF2F2;color:#991B1B;font-size:11px;padding:3px 8px;border-radius:20px;font-weight:600;'">{{ s.activo ? 'Activo' : 'Inactivo' }}</span>
          </div>
          <div style="display:flex;gap:6px;justify-content:center;">
            <template v-if="editando && editando.id===s.id">
              <button @click="guardarEdicion" style="background:#10B981;color:white;border:none;border-radius:6px;padding:5px 10px;font-size:12px;cursor:pointer;font-weight:600;">OK</button>
              <button @click="editando=null" style="background:#F1F5F9;color:#64748B;border:none;border-radius:6px;padding:5px 10px;font-size:12px;cursor:pointer;">X</button>
            </template>
            <template v-else>
              <button @click="iniciarEdicion(s)" style="background:#EFF6FF;color:#2563EB;border:none;border-radius:6px;padding:5px 10px;font-size:12px;cursor:pointer;">Editar</button>
              <button @click="toggleActivo(s)" :style="s.activo ? 'background:#FEF2F2;color:#DC2626;border:none;border-radius:6px;padding:5px 10px;font-size:12px;cursor:pointer;' : 'background:#F0FDF4;color:#16A34A;border:none;border-radius:6px;padding:5px 10px;font-size:12px;cursor:pointer;'">{{ s.activo ? 'Off' : 'On' }}</button>
              <button @click="eliminar(s)" style="background:#FEF2F2;color:#DC2626;border:none;border-radius:6px;padding:5px 10px;font-size:12px;cursor:pointer;">Del</button>
            </template>
          </div>
        </div>
      </div>
      <p style="margin-top:12px;font-size:12px;color:#94A3B8;text-align:right;">{{ serviciosFiltrados.length }} de {{ servicios.length }} servicios</p>
    </div>
  </AppLayout>
</template>
<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
const props = defineProps({ servicios: Array })
const buscar = ref('')
const mostrarFormNuevo = ref(false)
const editando = ref(null)
const formNuevo = ref({ nombre: '', precio: '' })
const serviciosFiltrados = computed(() => {
    if (!buscar.value) return props.servicios
    return props.servicios.filter(s => s.nombre.toLowerCase().includes(buscar.value.toLowerCase()))
})
function iniciarEdicion(s) { editando.value = { id: s.id, nombre: s.nombre, precio: s.precio, activo: s.activo } }
function guardarNuevo() {
    if (!formNuevo.value.nombre) return
    router.post('/notaria/servicios', formNuevo.value, {
        onSuccess: () => { mostrarFormNuevo.value = false; formNuevo.value = { nombre: '', precio: '' } },
        preserveScroll: true,
    })
}
function guardarEdicion() {
    if (!editando.value) return
    router.put('/notaria/servicios/' + editando.value.id, { nombre: editando.value.nombre, precio: editando.value.precio, activo: editando.value.activo }, { onSuccess: () => editando.value = null, preserveScroll: true })
}
function toggleActivo(s) { router.put('/notaria/servicios/' + s.id, { nombre: s.nombre, precio: s.precio, activo: !s.activo }, { preserveScroll: true }) }
function eliminar(s) {
    if (!confirm('Eliminar "' + s.nombre + '"?')) return
    router.delete('/notaria/servicios/' + s.id, { preserveScroll: true })
}
</script>
