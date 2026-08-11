<template>
  <AppLayout>
    <div style="padding:24px; max-width:1100px; margin:0 auto;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <h1 style="margin:0; font-size:22px; font-weight:700; color:#1E293B;">Insumos</h1>
        <button @click="modalNuevo=true" style="background:#8B5CF6; color:white; border:none; border-radius:10px; padding:10px 20px; font-size:14px; font-weight:600; cursor:pointer;">+ Nuevo insumo</button>
      </div>

      <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse;">
          <thead>
            <tr style="background:#F8FAFC;">
              <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Nombre</th>
              <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Categoría</th>
              <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Stock</th>
              <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Unidad</th>
              <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Stock mín.</th>
              <th style="padding:12px 16px; text-align:left; font-size:12px; color:#64748B;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="insumos.length===0"><td colspan="6" style="padding:32px; text-align:center; color:#94A3B8;">Sin insumos registrados</td></tr>
            <tr v-for="i in insumos" :key="i.id" style="border-top:1px solid #F1F5F9;">
              <td style="padding:12px 16px; font-size:13px; font-weight:600;">{{ i.nombre }}</td>
              <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ i.categoria || '-' }}</td>
              <td style="padding:12px 16px; font-size:14px; font-weight:700;" :style="i.stock <= i.stock_minimo ? 'color:#DC2626' : 'color:#16A34A'">{{ i.stock }}</td>
              <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ i.unidad }}</td>
              <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ i.stock_minimo }}</td>
              <td style="padding:12px 16px;">
                <button @click="editar(i)" style="color:#8B5CF6; font-size:13px; font-weight:600; background:none; border:none; cursor:pointer;">Editar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal nuevo/editar -->
    <div v-if="modalNuevo" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
      <div style="background:white; border-radius:16px; padding:28px; width:460px; max-width:90vw;">
        <h2 style="margin:0 0 20px; font-size:18px; font-weight:700;">{{ editandoId ? 'Editar' : 'Nuevo' }} insumo</h2>
        <div style="display:flex; flex-direction:column; gap:12px;">
          <div>
            <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">NOMBRE *</label>
            <input v-model="form.nombre" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
          </div>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div>
              <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">CATEGORÍA</label>
              <input v-model="form.categoria" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            </div>
            <div>
              <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">UNIDAD</label>
              <input v-model="form.unidad" type="text" placeholder="unid, caja, ml..." style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            </div>
          </div>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
            <div>
              <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">STOCK INICIAL</label>
              <input v-model="form.stock" type="number" min="0" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            </div>
            <div>
              <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">STOCK MÍNIMO</label>
              <input v-model="form.stock_minimo" type="number" min="0" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            </div>
          </div>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
          <button @click="cerrar" style="padding:10px 20px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; cursor:pointer; background:white;">Cancelar</button>
          <button @click="guardar" style="padding:10px 24px; background:#8B5CF6; color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">Guardar</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ insumos: Array })
const modalNuevo = ref(false)
const editandoId = ref(null)
const form = ref({ nombre:'', categoria:'', unidad:'unid', stock:0, stock_minimo:5 })

const cerrar = () => { modalNuevo.value = false; editandoId.value = null; form.value = { nombre:'', categoria:'', unidad:'unid', stock:0, stock_minimo:5 } }

const editar = (i) => {
  editandoId.value = i.id
  form.value = { nombre: i.nombre, categoria: i.categoria||'', unidad: i.unidad, stock: i.stock, stock_minimo: i.stock_minimo }
  modalNuevo.value = true
}

const guardar = () => {
  if (editandoId.value) {
    router.put(`/odontologia/insumos/${editandoId.value}`, form.value, { onSuccess: cerrar })
  } else {
    router.post('/odontologia/insumos', form.value, { onSuccess: cerrar })
  }
}
</script>
