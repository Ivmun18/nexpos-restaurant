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
                                <option value="1">Régimen General</option>
                                <option value="2">Régimen Especial</option>
                                <option value="3">Régimen MYPE</option>
                                <option value="4">RUS</option>
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

        <!-- Sección Nubefact - Solo Superadmin -->
        <div v-if="es_superadmin" style="margin-top:1.5rem; background:white; border-radius:10px; border:2px solid #0891b2; padding:1.5rem;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:1.2rem;">
                <div style="width:36px; height:36px; background:linear-gradient(135deg,#0891b2,#0e7490); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px;">⚡</div>
                <div>
                    <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0;">Configuración Nubefact</p>
                    <p style="font-size:11px; color:#64748B; margin:0;">Solo visible para SuperAdmin</p>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div style="grid-column:1/-1;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Token Nubefact</label>
                    <input v-model="nubefact.token" type="text" placeholder="Token de tu cuenta Nubefact"
                        style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box; font-family:monospace;"/>
                </div>
                <div>
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Serie Boleta</label>
                    <input v-model="nubefact.serie_boleta" type="text" maxlength="4" placeholder="B001"
                        style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box; text-transform:uppercase;"/>
                </div>
                <div>
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Serie Factura</label>
                    <input v-model="nubefact.serie_factura" type="text" maxlength="4" placeholder="F001"
                        style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box; text-transform:uppercase;"/>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:10px; margin-bottom:1rem;">
                <input type="checkbox" v-model="nubefact.demo" id="nubefact_demo" style="width:16px; height:16px; accent-color:#0891b2;"/>
                <label for="nubefact_demo" style="font-size:13px; color:#374151;">Modo Demo (pruebas)</label>
                <span style="padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; margin-left:8px;" :style="nubefact.demo ? 'background:#fef3c7; color:#92400e;' : 'background:#dcfce7; color:#166534;'">
                    {{ nubefact.demo ? "🧪 Demo" : "🚀 Producción" }}
                </span>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="button" @click="guardarNubefact" :disabled="procesandoNubefact"
                    style="flex:1; padding:12px; background:linear-gradient(135deg,#0891b2,#0e7490); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                    {{ procesandoNubefact ? "Guardando..." : "💾 Guardar Nubefact" }}
                </button>
                <button type="button" @click="testNubefact"
                    style="padding:12px 20px; background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                    🔌 Probar conexión
                </button>
            </div>

            <div v-if="testResultado" :style="testResultado.success ? 'margin-top:12px; padding:12px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; color:#166534; font-size:13px;' : 'margin-top:12px; padding:12px; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; color:#dc2626; font-size:13px;'">
                {{ testResultado.success ? testResultado.mensaje : testResultado.error }}
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
    regimen_tributario: props.empresa?.regimen_tributario ?? 1,
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

// Nubefact
const nubefact = ref({
    token: props.empresa?.nubefact_token ?? '',
    demo: props.empresa?.nubefact_demo ?? true,
    zona_exonerada: props.empresa?.zona_exonerada ?? false,
    serie_boleta: props.empresa?.serie_boleta ?? 'B001',
    serie_factura: props.empresa?.serie_factura ?? 'F001',
})
const procesandoNubefact = ref(false)
const testResultado = ref(null)

const guardarNubefact = () => {
    procesandoNubefact.value = true
    router.post('/configuracion/nubefact', {
        nubefact_token: nubefact.value.token,
        nubefact_demo: nubefact.value.demo,
        zona_exonerada: nubefact.value.zona_exonerada,
        serie_boleta: nubefact.value.serie_boleta,
        serie_factura: nubefact.value.serie_factura,
    }, {
        onSuccess: () => { procesandoNubefact.value = false },
        onError: () => { procesandoNubefact.value = false },
    })
}

const testNubefact = async () => {
    testResultado.value = null
    try {
        const res = await fetch('/configuracion/nubefact/test')
        testResultado.value = await res.json()
    } catch (e) {
        testResultado.value = { error: 'Error de conexión' }
    }
}

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