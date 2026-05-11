<template>
    <AppLayout title="Configuración" subtitle="Datos de la empresa y configuración del sistema">

        <form @submit.prevent="guardar" enctype="multipart/form-data">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; align-items:start;">

                <!-- Panel izquierdo -->
                <div style="display:flex; flex-direction:column; gap:1rem;">

                    <!-- Datos de la empresa -->
                    <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                        <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0 0 1.2rem;">Datos de la empresa</p>

                        <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; margin-bottom:1rem;">
                            <div>
                                <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">RUC *</label>
                                <input v-model="form.ruc" type="text" maxlength="11"
                                    style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box; font-family:monospace;"/>
                            </div>
                            <div>
                                <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Razón social *</label>
                                <input v-model="form.razon_social" type="text"
                                    style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                            </div>
                        </div>

                        <div style="margin-bottom:1rem;">
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Nombre comercial</label>
                            <input v-model="form.nombre_comercial" type="text"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                        </div>

                        <div style="margin-bottom:1rem;">
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Dirección</label>
                            <input v-model="form.direccion" type="text"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; margin-bottom:1rem;">
                            <div>
                                <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Distrito</label>
                                <input v-model="form.distrito" type="text"
                                    style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                            </div>
                            <div>
                                <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Provincia</label>
                                <input v-model="form.provincia" type="text"
                                    style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                            </div>
                            <div>
                                <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Departamento</label>
                                <input v-model="form.departamento" type="text"
                                    style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                            <div>
                                <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Teléfono</label>
                                <input v-model="form.telefono" type="text"
                                    style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                            </div>
                            <div>
                                <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Email</label>
                                <input v-model="form.email" type="email"
                                    style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                            </div>
                        </div>

                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Sitio web</label>
                            <input v-model="form.web" type="text" placeholder="https://www.miempresa.com"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                        </div>
                    </div>

                    <!-- Logo -->
                    <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                        <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Logo de la empresa</p>

                        <div v-if="empresa.logo_path" style="margin-bottom:1rem;">
                            <img :src="'/storage/' + empresa.logo_path"
                                style="max-height:80px; border-radius:8px; border:1px solid #E2E8F0;"/>
                        </div>

                        <div style="border:2px dashed #E2E8F0; border-radius:8px; padding:1.5rem; text-align:center;">
                            <input type="file" id="logo" accept="image/*" @change="onLogo"
                                style="display:none;"/>
                            <label for="logo" style="cursor:pointer;">
                                <svg width="32" height="32" fill="none" stroke="#94A3B8" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 8px; display:block;">
                                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>
                                </svg>
                                <p style="font-size:13px; color:#64748B; margin:0;">
                                    {{ logoNombre || 'Haz clic para subir el logo' }}
                                </p>
                                <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">PNG, JPG hasta 2MB</p>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Panel derecho -->
                <div style="display:flex; flex-direction:column; gap:1rem;">

                    <!-- Régimen tributario -->
                    <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                        <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Configuración tributaria</p>

                        <div style="margin-bottom:1rem;">
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Régimen tributario</label>
                            <select v-model="form.regimen_tributario"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; background:white;">
                                <option value="GENERAL">Régimen General</option>
                                <option value="RER">Régimen Especial (RER)</option>
                                <option value="MYPE">Régimen MYPE</option>
                                <option value="RUS">RUS</option>
                            </select>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:10px;">
                            <label style="display:flex; align-items:center; gap:10px; font-size:13px; color:#64748B; cursor:pointer;">
                                <input v-model="form.buen_contribuyente" type="checkbox" style="accent-color:#2563EB;"/>
                                Buen contribuyente
                            </label>
                            <label style="display:flex; align-items:center; gap:10px; font-size:13px; color:#64748B; cursor:pointer;">
                                <input v-model="form.agente_retencion" type="checkbox" style="accent-color:#2563EB;"/>
                                Agente de retención
                            </label>
                        </div>
                    </div>

                    <!-- Configuración SUNAT -->
                    <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                        <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Configuración SUNAT</p>

                        <div style="margin-bottom:1rem;">
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Ambiente</label>
                            <div style="display:flex; gap:8px;">
                                <button type="button" @click="form.ambiente='beta'"
                                    :style="form.ambiente==='beta' ? btnActivo : btnInactivo">
                                    Beta (pruebas)
                                </button>
                                <button type="button" @click="form.ambiente='produccion'"
                                    :style="form.ambiente==='produccion' ? btnProduccion : btnInactivo">
                                    Producción
                                </button>
                            </div>
                        </div>

                        <div style="margin-bottom:1rem;">
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Usuario SOL</label>
                            <input v-model="form.sol_usuario" type="text" placeholder="20123456789USUARIO"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box; font-family:monospace;"/>
                        </div>

                        <div style="margin-bottom:1rem;">
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Clave SOL</label>
                            <input v-model="form.sol_clave" type="password" placeholder="••••••••"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                        </div>

                        <div style="background:#FFFBEB; border-radius:8px; padding:10px 14px; border:1px solid #FDE68A;">
                            <p style="font-size:12px; color:#92400E; margin:0;">
                                ⚠️ En ambiente <strong>Beta</strong> los comprobantes no tienen validez tributaria. Cambia a <strong>Producción</strong> solo cuando tengas tu certificado digital real.
                            </p>
                        </div>
                    </div>

                    <!-- Certificado digital -->
                    <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                        <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Certificado digital</p>

                        <div v-if="empresa.cert_vencimiento" style="background:#F0FDF4; border-radius:8px; padding:10px 14px; margin-bottom:1rem; border:1px solid #DCFCE7;">
                            <p style="font-size:12px; color:#166534; margin:0;">
                                ✅ Certificado cargado · Vence: {{ empresa.cert_vencimiento }}
                            </p>
                        </div>

                        <div style="margin-bottom:1rem;">
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Archivo .pfx</label>
                            <div style="border:2px dashed #E2E8F0; border-radius:8px; padding:1rem; text-align:center;">
                                <input type="file" id="certificado" accept=".pfx,.p12" @change="onCertificado"
                                    style="display:none;"/>
                                <label for="certificado" style="cursor:pointer; font-size:13px; color:#64748B;">
                                    {{ certNombre || 'Subir certificado .pfx' }}
                                </label>
                            </div>
                        </div>

                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Contraseña del certificado</label>
                            <input v-model="form.cert_password" type="password" placeholder="••••••••"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                        </div>
                    </div>

                    <!-- Zona exonerada -->
                    <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem; margin-bottom:1rem;">
                        <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0 0 1rem;">🌿 Régimen Tributario</p>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <input type="checkbox" v-model="form.zona_exonerada" id="zona_exonerada" style="width:18px; height:18px; accent-color:#14B8A6;" />
                            <label for="zona_exonerada" style="font-size:14px; color:#374151; cursor:pointer;">
                                Zona exonerada de IGV (Selva)
                                <span style="font-size:12px; color:#94a3b8; display:block; margin-top:2px;">Loreto, Ucayali, Amazonas, Madre de Dios, San Martín</span>
                            </label>
                        </div>
                    </div>

                <!-- Botón guardar -->
                    <button type="submit" :disabled="procesando"
                        style="width:100%; padding:14px; background:#2563EB; color:white; border:none; border-radius:10px; font-size:15px; font-weight:700; cursor:pointer;">
                        {{ procesando ? 'Guardando...' : 'Guardar configuración' }}
                    </button>
                </div>
            </div>
        </form>

        <!-- Facturación Electrónica -->
        <div style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(0,0,0,0.05); margin-bottom:16px;">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:40px; height:40px; border-radius:10px; background:#F0FDFA; display:flex; align-items:center; justify-content:center; font-size:20px;">🧾</div>
                    <div>
                        <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0;">Facturación Electrónica</p>
                        <p style="font-size:12px; color:#64748B; margin:2px 0 0;">Configura APISUNAT y régimen tributario</p>
                    </div>
                </div>
                <a href="/configuracion/facturacion"
                    style="padding:10px 20px; border-radius:10px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; font-size:13px; font-weight:700; text-decoration:none; display:inline-block;">
                    ⚙️ Configurar
                </a>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ empresa: Object, es_superadmin: Boolean })

const procesando  = ref(false)
const logoNombre  = ref('')
const certNombre  = ref('')
const logoFile    = ref(null)
const certFile    = ref(null)

const form = ref({
    ruc:                props.empresa?.ruc ?? '',
    razon_social:       props.empresa?.razon_social ?? '',
    nombre_comercial:   props.empresa?.nombre_comercial ?? '',
    direccion:          props.empresa?.direccion ?? '',
    distrito:           props.empresa?.distrito ?? '',
    provincia:          props.empresa?.provincia ?? '',
    departamento:       props.empresa?.departamento ?? '',
    telefono:           props.empresa?.telefono ?? '',
    email:              props.empresa?.email ?? '',
    web:                props.empresa?.web ?? '',
    regimen_tributario: props.empresa?.regimen_tributario ?? 'GENERAL',
    buen_contribuyente: props.empresa?.buen_contribuyente ?? false,
    agente_retencion:   props.empresa?.agente_retencion ?? false,
    ambiente:           props.empresa?.ambiente ?? 'beta',
    zona_exonerada:     props.empresa?.zona_exonerada ?? false,
    sol_usuario:        props.empresa?.sol_usuario ?? '',
    sol_clave:          '',
    cert_password:      '',
})

const btnActivo     = { padding:'8px 16px', background:'#2563EB', color:'white', border:'none', borderRadius:'8px', fontSize:'13px', fontWeight:'600', cursor:'pointer' }
const btnProduccion = { padding:'8px 16px', background:'#166534', color:'white', border:'none', borderRadius:'8px', fontSize:'13px', fontWeight:'600', cursor:'pointer' }
const btnInactivo   = { padding:'8px 16px', background:'#F1F5F9', color:'#64748B', border:'1px solid #E2E8F0', borderRadius:'8px', fontSize:'13px', cursor:'pointer' }

const onLogo = (e) => {
    logoFile.value  = e.target.files[0]
    logoNombre.value = logoFile.value?.name || ''
}

const onCertificado = (e) => {
    certFile.value   = e.target.files[0]
    certNombre.value = certFile.value?.name || ''
}

// Facturación: ver /configuracion/facturacion

const guardar = () => {
    procesando.value = true
    const data = new FormData()

    Object.entries(form.value).forEach(([key, val]) => {
        if (val !== null && val !== undefined) {
            data.append(key, val)
        }
    })

    if (logoFile.value) data.append('logo', logoFile.value)
    if (certFile.value) data.append('certificado', certFile.value)

    router.post('/configuracion', data, {
        forceFormData: true,
        onSuccess: () => { procesando.value = false },
        onError:   () => { procesando.value = false },
    })
}
</script>