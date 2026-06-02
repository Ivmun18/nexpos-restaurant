<template>
  <div style="padding:24px; max-width:1200px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
      <h1 style="font-size:22px; font-weight:700; margin:0;">Pacientes</h1>
      <a href="/odontologia/pacientes/crear" style="background:#8B5CF6; color:white; padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px;">+ Nuevo paciente</a>
    </div>

    <!-- Buscador -->
    <div style="margin-bottom:20px;">
      <input v-model="busqueda" @input="buscar" type="text" placeholder="Buscar por nombre, apellido o DNI..."
        style="width:100%; padding:10px 14px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
    </div>

    <!-- Tabla -->
    <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; overflow:hidden;">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="background:#F8FAFC;">
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Paciente</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">DNI</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Teléfono</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Edad</th>
            <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="pacientes.data.length === 0">
            <td colspan="5" style="padding:32px; text-align:center; color:#94A3B8;">No se encontraron pacientes</td>
          </tr>
          <tr v-for="p in pacientes.data" :key="p.id" style="border-top:1px solid #F1F5F9;">
            <td style="padding:12px 16px;">
              <p style="margin:0; font-weight:600; font-size:14px;">{{ p.apellidos }}, {{ p.nombres }}</p>
              <p style="margin:2px 0 0; font-size:12px; color:#64748B;">{{ p.email || '-' }}</p>
            </td>
            <td style="padding:12px 16px; font-size:13px;">{{ p.dni || '-' }}</td>
            <td style="padding:12px 16px; font-size:13px;">{{ p.telefono || '-' }}</td>
            <td style="padding:12px 16px; font-size:13px;">{{ calcularEdad(p.fecha_nacimiento) }}</td>
            <td style="padding:12px 16px;">
              <a :href="`/odontologia/pacientes/${p.id}`" style="color:#8B5CF6; font-size:13px; font-weight:600; text-decoration:none; margin-right:12px;">Ver</a>
              <a :href="`/odontologia/pacientes/${p.id}/editar`" style="color:#64748B; font-size:13px; text-decoration:none;">Editar</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px;">
      <p style="font-size:13px; color:#64748B;">{{ pacientes.total }} pacientes</p>
      <div style="display:flex; gap:8px;">
        <a v-if="pacientes.prev_page_url" :href="pacientes.prev_page_url" style="padding:6px 14px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; text-decoration:none; color:#374151;">← Anterior</a>
        <a v-if="pacientes.next_page_url" :href="pacientes.next_page_url" style="padding:6px 14px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; text-decoration:none; color:#374151;">Siguiente →</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ pacientes: Object, buscar: String })
const busqueda = ref(props.buscar || '')

const buscar = () => {
  router.get('/odontologia/pacientes', { buscar: busqueda.value }, { preserveState: true, replace: true })
}

const calcularEdad = (fechaNac) => {
  if (!fechaNac) return '-'
  const hoy = new Date()
  const nac = new Date(fechaNac)
  let edad = hoy.getFullYear() - nac.getFullYear()
  if (hoy.getMonth() < nac.getMonth() || (hoy.getMonth() === nac.getMonth() && hoy.getDate() < nac.getDate())) edad--
  return edad + ' años'
}
</script>
