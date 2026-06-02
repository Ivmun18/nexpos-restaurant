<template>
  <div style="padding:24px; max-width:800px; margin:0 auto;">
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
      <a href="/odontologia/pacientes" style="color:#64748B; text-decoration:none;">← Pacientes</a>
      <h1 style="font-size:22px; font-weight:700; margin:0;">Nuevo paciente</h1>
    </div>

    <form @submit.prevent="submit" style="background:white; border:1px solid #E2E8F0; border-radius:12px; padding:24px;">
      <h2 style="font-size:15px; font-weight:700; margin:0 0 16px; color:#8B5CF6;">Datos personales</h2>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
        <div>
          <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">NOMBRES *</label>
          <input v-model="form.nombres" type="text" required style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
        </div>
        <div>
          <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">APELLIDOS *</label>
          <input v-model="form.apellidos" type="text" required style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
        </div>
        <div>
          <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">DNI</label>
          <input v-model="form.dni" type="text" maxlength="8" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
        </div>
        <div>
          <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">FECHA DE NACIMIENTO</label>
          <input v-model="form.fecha_nacimiento" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
        </div>
        <div>
          <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">SEXO</label>
          <select v-model="form.sexo" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;">
            <option value="">Seleccionar...</option>
            <option value="M">Masculino</option>
            <option value="F">Femenino</option>
            <option value="otro">Otro</option>
          </select>
        </div>
        <div>
          <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">GRUPO SANGUÍNEO</label>
          <select v-model="form.grupo_sanguineo" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;">
            <option value="">Seleccionar...</option>
            <option v-for="g in grupos" :key="g" :value="g">{{ g }}</option>
          </select>
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
      <div style="margin-bottom:16px;">
        <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">DIRECCIÓN</label>
        <input v-model="form.direccion" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
      </div>

      <h2 style="font-size:15px; font-weight:700; margin:20px 0 16px; color:#8B5CF6;">Antecedentes médicos</h2>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
        <div>
          <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">ALERGIAS</label>
          <textarea v-model="form.alergias" rows="3" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;"></textarea>
        </div>
        <div>
          <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">ANTECEDENTES</label>
          <textarea v-model="form.antecedentes" rows="3" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;"></textarea>
        </div>
      </div>

      <h2 style="font-size:15px; font-weight:700; margin:20px 0 16px; color:#8B5CF6;">Contacto de emergencia</h2>
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
        <div>
          <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">NOMBRE</label>
          <input v-model="form.contacto_emergencia" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
        </div>
        <div>
          <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">TELÉFONO</label>
          <input v-model="form.telefono_emergencia" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
        </div>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:12px;">
        <a href="/odontologia/pacientes" style="padding:10px 20px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; text-decoration:none; color:#374151;">Cancelar</a>
        <button type="submit" :disabled="loading" style="padding:10px 24px; background:#8B5CF6; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">
          {{ loading ? 'Guardando...' : 'Guardar paciente' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const loading = ref(false)
const grupos = ['A+','A-','B+','B-','AB+','AB-','O+','O-']
const form = ref({
  nombres:'', apellidos:'', dni:'', fecha_nacimiento:'', sexo:'', grupo_sanguineo:'',
  telefono:'', email:'', direccion:'', alergias:'', antecedentes:'',
  contacto_emergencia:'', telefono_emergencia:''
})

const submit = () => {
  loading.value = true
  router.post('/odontologia/pacientes', form.value, {
    onFinish: () => loading.value = false
  })
}
</script>
