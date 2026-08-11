<template>
  <AppLayout>
    <div style="max-width:800px;margin:0 auto;padding:32px 24px;">

      <!-- HEADER -->
      <div style="margin-bottom:28px;">
        <h1 style="font-size:20px;font-weight:600;color:#1e293b;margin:0;">Configuración de la clínica</h1>
        <p style="font-size:13px;color:#64748b;margin:6px 0 0;">Datos que aparecen en recetas, presupuestos y comprobantes</p>
      </div>

      <!-- ALERTA ÉXITO -->
      <div v-if="$page.props.flash?.success" style="background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#166534;">
        ✓ {{ $page.props.flash.success }}
      </div>

      <!-- LOGO -->
      <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;margin-bottom:16px;">
        <div style="font-size:13px;font-weight:500;color:#374151;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
          <span style="font-size:16px;">🖼️</span> Logo de la clínica
        </div>
        <div style="display:flex;align-items:center;gap:20px;">
          <div style="width:80px;height:80px;border:2px dashed #e2e8f0;border-radius:10px;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f8fafc;">
            <img v-if="logoPreview || empresa.logo" :src="logoPreview || '/storage/'+empresa.logo" style="width:100%;height:100%;object-fit:contain;" />
            <span v-else style="font-size:28px;">🦷</span>
          </div>
          <div>
            <input type="file" ref="logoInput" accept="image/*" style="display:none" @change="onLogoChange" />
            <button @click="$refs.logoInput.click()" style="padding:8px 16px;border:1px solid #e2e8f0;border-radius:8px;font-size:12px;cursor:pointer;background:#fff;color:#374151;">Cambiar logo</button>
            <p style="font-size:11px;color:#94a3b8;margin:6px 0 0;">PNG o JPG, máx. 2MB. Aparece en PDFs.</p>
            <button v-if="logoFile" @click="subirLogo" style="margin-top:8px;padding:6px 14px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-size:12px;cursor:pointer;">Subir logo</button>
          </div>
        </div>
      </div>

      <!-- DATOS GENERALES -->
      <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;margin-bottom:16px;">
        <div style="font-size:13px;font-weight:500;color:#374151;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
          <span style="font-size:16px;">🏥</span> Datos generales
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div style="grid-column:1/-1;">
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Razón social *</label>
            <input v-model="form.razon_social" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" />
          </div>
          <div>
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Nombre comercial</label>
            <input v-model="form.nombre_comercial" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" />
          </div>
          <div>
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">RUC *</label>
            <input v-model="form.ruc" maxlength="11" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" />
          </div>
          <div>
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Teléfono</label>
            <input v-model="form.telefono" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="999 999 999" />
          </div>
          <div>
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Email</label>
            <input v-model="form.email" type="email" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="clinica@ejemplo.com" />
          </div>
          <div style="grid-column:1/-1;">
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Dirección</label>
            <input v-model="form.direccion" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" />
          </div>
          <div>
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Distrito</label>
            <input v-model="form.distrito" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" />
          </div>
          <div>
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Provincia</label>
            <input v-model="form.provincia" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" />
          </div>
          <div>
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Departamento</label>
            <input v-model="form.departamento" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" />
          </div>
          <div>
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Régimen tributario</label>
            <select v-model="form.regimen_tributario" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;">
              <option value="General">Régimen General</option>
              <option value="Mype">Régimen Mype Tributario</option>
              <option value="Especial">Régimen Especial</option>
              <option value="Simple">Nuevo RUS</option>
            </select>
          </div>
        </div>
      </div>

      <!-- FACTURACIÓN -->
      <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;margin-bottom:24px;">
        <div style="font-size:13px;font-weight:500;color:#374151;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
          <span style="font-size:16px;">🧾</span> Series de comprobantes
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div>
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Serie boleta</label>
            <input v-model="form.serie_boleta" maxlength="4" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="B001" />
          </div>
          <div>
            <label style="font-size:12px;color:#64748b;display:block;margin-bottom:4px;">Serie factura</label>
            <input v-model="form.serie_factura" maxlength="4" style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box;" placeholder="F001" />
          </div>
          <div style="grid-column:1/-1;background:#fef9c3;border-radius:8px;padding:12px 14px;">
            <p style="font-size:12px;color:#854d0e;margin:0;">⚠️ Cambiar las series solo si SUNAT lo ha autorizado. Un cambio incorrecto invalida tus comprobantes.</p>
          </div>
        </div>
      </div>

      <!-- BOTÓN GUARDAR -->
      <div style="display:flex;justify-content:flex-end;gap:12px;">
        <button @click="guardar" style="padding:10px 28px;background:#8B5CF6;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;">Guardar cambios</button>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ empresa: Object })

const form = ref({
  razon_social:      props.empresa.razon_social      ?? '',
  nombre_comercial:  props.empresa.nombre_comercial  ?? '',
  ruc:               props.empresa.ruc               ?? '',
  direccion:         props.empresa.direccion          ?? '',
  distrito:          props.empresa.distrito           ?? '',
  provincia:         props.empresa.provincia          ?? '',
  departamento:      props.empresa.departamento       ?? '',
  telefono:          props.empresa.telefono           ?? '',
  email:             props.empresa.email              ?? '',
  serie_boleta:      props.empresa.serie_boleta       ?? 'B001',
  serie_factura:     props.empresa.serie_factura      ?? 'F001',
  regimen_tributario:props.empresa.regimen_tributario ?? 'General',
})

const logoPreview = ref(null)
const logoFile    = ref(null)

const onLogoChange = (e) => {
  const file = e.target.files[0]
  if (!file) return
  logoFile.value = file
  logoPreview.value = URL.createObjectURL(file)
}

const subirLogo = () => {
  const data = new FormData()
  data.append('logo', logoFile.value)
  data.append('_method', 'POST')
  router.post('/odontologia/configuracion/logo', data, {
    onSuccess: () => { logoFile.value = null }
  })
}

const guardar = () => {
  router.put('/odontologia/configuracion', form.value)
}
</script>
