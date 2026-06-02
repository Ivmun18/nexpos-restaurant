<template>
  <AppLayout title="Doctores" subtitle="">
  <div style="padding:24px; max-width:1000px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h1 style="font-size:22px; font-weight:700; margin:0;">Doctores</h1>
      <button @click="modalNuevo=true" style="background:#8B5CF6; color:white; padding:10px 20px; border:none; border-radius:8px; font-weight:600; font-size:14px; cursor:pointer;">+ Nuevo doctor</button>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px;">
      <div v-for="d in doctores" :key="d.id" style="background:white; border:1px solid #E2E8F0; border-radius:12px; padding:20px;">
        <div style="display:flex; justify-content:space-between; align-items:start;">
          <div>
            <p style="margin:0; font-weight:700; font-size:16px;">{{ d.nombre }}</p>
            <p style="margin:4px 0 0; font-size:13px; color:#8B5CF6; font-weight:600;">{{ d.especialidad || 'Odontología general' }}</p>
          </div>
          <span :style="d.activo ? {background:'#F0FDF4',color:'#15803D'} : {background:'#FEF2F2',color:'#B91C1C'}" style="padding:3px 8px; border-radius:6px; font-size:11px; font-weight:600;">{{ d.activo ? 'Activo' : 'Inactivo' }}</span>
        </div>
        <div style="margin-top:12px; font-size:13px; color:#64748B; display:flex; flex-direction:column; gap:4px;">
          <p style="margin:0;" v-if="d.colegiatura">COP: {{ d.colegiatura }}</p>
          <p style="margin:0;" v-if="d.telefono">📞 {{ d.telefono }}</p>
          <p style="margin:0;" v-if="d.email">✉ {{ d.email }}</p>
        </div>
        <div v-if="d.horarios?.length" style="margin-top:12px; padding-top:12px; border-top:1px solid #F1F5F9;">
          <p style="font-size:11px; font-weight:600; color:#64748B; margin:0 0 6px;">HORARIOS</p>
          <div v-for="h in d.horarios" :key="h.id" style="font-size:12px; color:#374151; margin-bottom:2px;">
            {{ h.dia_semana }}: {{ h.hora_inicio }} - {{ h.hora_fin }}
          </div>
        </div>
        <div style="display:flex; gap:8px; margin-top:14px;">
          <button @click="editarDoctor(d)" style="flex:1; padding:7px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; cursor:pointer; background:white;">Editar</button>
          <button @click="toggleActivo(d)" style="padding:7px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; cursor:pointer; background:white; color:#64748B;">{{ d.activo ? 'Desactivar' : 'Activar' }}</button>
        </div>
      </div>
    </div>

    <!-- Modal nuevo/editar doctor -->
    <div v-if="modalNuevo" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000; overflow-y:auto; padding:20px;">
      <div style="background:white; border-radius:16px; padding:28px; width:560px; max-width:100%;">
        <h2 style="margin:0 0 20px; font-size:18px;">{{ editando ? 'Editar doctor' : 'Nuevo doctor' }}</h2>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
          <div style="grid-column:1/-1;">
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">NOMBRE COMPLETO *</label>
            <input v-model="form.nombre" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">ESPECIALIDAD</label>
            <input v-model="form.especialidad" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">N° COLEGIATURA (COP)</label>
            <input v-model="form.colegiatura" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">TELÉFONO</label>
            <input v-model="form.telefono" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
          </div>
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">EMAIL</label>
            <input v-model="form.email" type="email" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
          </div>
        </div>

        <!-- Horarios -->
        <div style="margin-top:20px;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <p style="margin:0; font-size:13px; font-weight:700;">Horarios de atención</p>
            <button @click="agregarHorario" style="padding:5px 12px; background:#EDE9FE; color:#7C3AED; border:none; border-radius:6px; font-size:12px; cursor:pointer;">+ Agregar</button>
          </div>
          <div v-for="(h,i) in form.horarios" :key="i" style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:8px; margin-bottom:8px; align-items:center;">
            <select v-model="h.dia_semana" style="padding:7px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px;">
              <option v-for="d in dias" :key="d" :value="d">{{ d }}</option>
            </select>
            <input v-model="h.hora_inicio" type="time" style="padding:7px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px;" />
            <input v-model="h.hora_fin" type="time" style="padding:7px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px;" />
            <button @click="form.horarios.splice(i,1)" style="padding:7px; background:#FEF2F2; color:#B91C1C; border:none; border-radius:6px; cursor:pointer;">✕</button>
          </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
          <button @click="cerrarModal" style="padding:10px 20px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; cursor:pointer; background:white;">Cancelar</button>
          <button @click="guardar" style="padding:10px 24px; background:#8B5CF6; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">Guardar</button>
        </div>
      </div>
    </div>
  </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ doctores: Array })
const modalNuevo = ref(false)
const editando = ref(null)
const dias = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo']
const form = ref({ nombre:'', especialidad:'', colegiatura:'', telefono:'', email:'', horarios:[] })

const agregarHorario = () => form.value.horarios.push({ dia_semana:'lunes', hora_inicio:'08:00', hora_fin:'17:00' })

const editarDoctor = (d) => {
  editando.value = d.id
  form.value = { nombre:d.nombre, especialidad:d.especialidad||'', colegiatura:d.colegiatura||'', telefono:d.telefono||'', email:d.email||'', horarios: d.horarios?.map(h=>({...h})) || [] }
  modalNuevo.value = true
}

const cerrarModal = () => { modalNuevo.value = false; editando.value = null; form.value = { nombre:'', especialidad:'', colegiatura:'', telefono:'', email:'', horarios:[] } }

const guardar = () => {
  if (editando.value) {
    router.put(`/odontologia/doctores/${editando.value}`, form.value, { onSuccess: cerrarModal })
  } else {
    router.post('/odontologia/doctores', form.value, { onSuccess: cerrarModal })
  }
}

const toggleActivo = (d) => router.put(`/odontologia/doctores/${d.id}`, { activo: !d.activo }, { preserveState: true })
</script>
