<template>
    <AppLayout title="Configuración de Facturación" subtitle="Conecta tu sistema con SUNAT">

        <div style="max-width:700px; margin:0 auto; padding:24px;">

            <!-- Header -->
            <div style="background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:16px; padding:24px; margin-bottom:24px; color:white;">
                <h2 style="margin:0 0 8px; font-size:22px;">🧾 Facturación Electrónica</h2>
                <p style="margin:0; opacity:0.85; font-size:14px;">Configura tu conexión con SUNAT para emitir boletas y facturas electrónicas</p>
            </div>

            <!-- Estado actual -->
            <div :style="{
                background: config.activo ? '#F0FDF4' : '#FFF7ED',
                border: '1px solid ' + (config.activo ? '#86EFAC' : '#FED7AA'),
                borderRadius: '12px', padding: '16px', marginBottom: '24px',
                display: 'flex', alignItems: 'center', gap: '12px'
            }">
                <span style="font-size:24px;">{{ config.activo ? '✅' : '⚠️' }}</span>
                <div>
                    <p style="margin:0; font-weight:700; color:#1E293B;">
                        {{ config.activo ? 'Facturación electrónica ACTIVA' : 'Facturación electrónica NO configurada' }}
                    </p>
                    <p style="margin:4px 0 0; font-size:13px; color:#64748B;">
                        {{ config.activo ? 'Tu sistema está conectado con SUNAT' : 'Completa los datos para activar' }}
                    </p>
                </div>
            </div>

            <!-- Formulario -->
            <div style="background:white; border-radius:16px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 2px 8px rgba(0,0,0,0.05);">

                <!-- Régimen tributario -->
                <div style="margin-bottom:20px;">
                    <label style="font-size:13px; font-weight:600; color:#64748B; display:block; margin-bottom:8px;">
                        📋 Régimen Tributario
                    </label>
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:8px;">
                        <button v-for="r in regimenes" :key="r.valor"
                            type="button"
                            @click="form.regimen_tributario = r.valor"
                            :style="{
                                padding: '10px 8px',
                                borderRadius: '10px',
                                border: form.regimen_tributario === r.valor ? '2px solid #14B8A6' : '2px solid #E2E8F0',
                                background: form.regimen_tributario === r.valor ? '#F0FDFA' : 'white',
                                cursor: 'pointer',
                                fontSize: '12px',
                                fontWeight: '700',
                                color: form.regimen_tributario === r.valor ? '#0F766E' : '#64748B',
                                textAlign: 'center',
                            }">
                            {{ r.icon }}<br>{{ r.label }}
                        </button>
                    </div>
                    <p v-if="form.regimen_tributario === 'RUS'" style="margin:8px 0 0; font-size:12px; color:#0F766E; background:#F0FDFA; padding:8px 12px; border-radius:8px;">
                        ✅ RUS: Las boletas se emitirán sin IGV (inafectas)
                    </p>
                </div>

                <!-- Proveedor -->
                <div style="margin-bottom:20px;">
                    <label style="font-size:13px; font-weight:600; color:#64748B; display:block; margin-bottom:8px;">
                        🔌 Proveedor de Facturación
                    </label>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                        <button v-for="p in proveedores" :key="p.valor"
                            type="button"
                            @click="form.proveedor = p.valor"
                            :style="{
                                padding: '12px',
                                borderRadius: '10px',
                                border: form.proveedor === p.valor ? '2px solid #14B8A6' : '2px solid #E2E8F0',
                                background: form.proveedor === p.valor ? '#F0FDFA' : 'white',
                                cursor: 'pointer',
                                fontSize: '13px',
                                fontWeight: '700',
                                color: form.proveedor === p.valor ? '#0F766E' : '#64748B',
                            }">
                            {{ p.icon }} {{ p.label }}
                            <span style="display:block; font-size:11px; font-weight:400; margin-top:2px; opacity:0.7;">{{ p.desc }}</span>
                        </button>
                    </div>
                </div>

                <!-- Token API -->
                <div style="margin-bottom:20px;">
                    <label style="font-size:13px; font-weight:600; color:#64748B; display:block; margin-bottom:8px;">
                        🔑 Token API
                        <a :href="tokenUrl" target="_blank"
                            style="margin-left:8px; font-size:11px; color:#14B8A6; text-decoration:none; font-weight:400;">
                            ¿Dónde lo encuentro? →
                        </a>
                    </label>
                    <div style="position:relative;">
                        <input
                            v-model="form.nubefact_token"
                            :type="verToken ? 'text' : 'password'"
                            placeholder="Pega tu token aquí..."
                            style="width:100%; padding:12px 44px 12px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; font-family:monospace;"
                            @focus="$event.target.style.borderColor='#14B8A6'"
                            @blur="$event.target.style.borderColor='#E2E8F0'"
                        />
                        <button type="button" @click="verToken = !verToken"
                            style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:16px;">
                            {{ verToken ? '🙈' : '👁️' }}
                        </button>
                    </div>
                </div>

                <!-- Series -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#64748B; display:block; margin-bottom:8px;">
                            🧾 Serie Boleta
                        </label>
                        <input v-model="form.serie_boleta" placeholder="B001"
                            style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; text-transform:uppercase;"
                            @focus="$event.target.style.borderColor='#14B8A6'"
                            @blur="$event.target.style.borderColor='#E2E8F0'"
                        />
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#64748B; display:block; margin-bottom:8px;">
                            📄 Serie Factura
                        </label>
                        <input v-model="form.serie_factura" placeholder="F001"
                            :disabled="form.regimen_tributario === 'RUS'"
                            :style="{
                                width: '100%', padding: '10px 14px',
                                border: '2px solid #E2E8F0', borderRadius: '10px',
                                fontSize: '14px', outline: 'none', boxSizing: 'border-box',
                                textTransform: 'uppercase',
                                opacity: form.regimen_tributario === 'RUS' ? '0.5' : '1',
                                background: form.regimen_tributario === 'RUS' ? '#F8FAFC' : 'white'
                            }"
                            @focus="$event.target.style.borderColor='#14B8A6'"
                            @blur="$event.target.style.borderColor='#E2E8F0'"
                        />
                    </div>
                </div>

                <!-- Modo demo -->
                <div style="margin-bottom:24px; padding:14px; background:#F8FAFC; border-radius:10px; display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <p style="margin:0; font-size:14px; font-weight:600; color:#1E293B;">🧪 Modo Pruebas</p>
                        <p style="margin:4px 0 0; font-size:12px; color:#64748B;">Los comprobantes no se envían a SUNAT (para testing)</p>
                    </div>
                    <button type="button" @click="form.nubefact_demo = !form.nubefact_demo"
                        :style="{
                            width: '48px', height: '26px', borderRadius: '13px',
                            border: 'none', cursor: 'pointer',
                            background: form.nubefact_demo ? '#14B8A6' : '#CBD5E1',
                            position: 'relative', transition: 'background 0.2s'
                        }">
                        <span :style="{
                            position: 'absolute', top: '3px',
                            left: form.nubefact_demo ? '24px' : '3px',
                            width: '20px', height: '20px', borderRadius: '50%',
                            background: 'white', transition: 'left 0.2s'
                        }"></span>
                    </button>
                </div>

                <!-- Botones -->
                <div style="display:flex; gap:12px;">
                    <button type="button" @click="probarConexion"
                        :disabled="!form.nubefact_token || probando"
                        style="flex:1; padding:12px; border-radius:10px; border:2px solid #14B8A6; background:white; color:#0F766E; font-size:14px; font-weight:700; cursor:pointer;">
                        {{ probando ? '⏳ Probando...' : '🔍 Probar conexión' }}
                    </button>
                    <button type="button" @click="guardar"
                        :disabled="guardando"
                        style="flex:2; padding:12px; border-radius:10px; border:none; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; font-size:14px; font-weight:700; cursor:pointer;">
                        {{ guardando ? '⏳ Guardando...' : '💾 Guardar configuración' }}
                    </button>
                </div>

                <!-- Resultado prueba -->
                <div v-if="resultadoPrueba" :style="{
                    marginTop: '16px', padding: '12px 16px', borderRadius: '10px',
                    background: resultadoPrueba.ok ? '#F0FDF4' : '#FEF2F2',
                    border: '1px solid ' + (resultadoPrueba.ok ? '#86EFAC' : '#FCA5A5'),
                    color: resultadoPrueba.ok ? '#166534' : '#991B1B',
                    fontSize: '13px', fontWeight: '600'
                }">
                    {{ resultadoPrueba.ok ? '✅' : '❌' }} {{ resultadoPrueba.mensaje }}
                </div>
            </div>

            <!-- Guía rápida -->
            <div style="margin-top:24px; background:white; border-radius:16px; padding:24px; border:1px solid #E2E8F0;">
                <p style="font-size:15px; font-weight:700; color:#1E293B; margin:0 0 16px;">📚 Guía rápida de activación</p>
                <div v-for="(paso, i) in pasos" :key="i"
                    style="display:flex; gap:12px; margin-bottom:12px;">
                    <div style="width:28px; height:28px; border-radius:50%; background:#F0FDFA; border:2px solid #14B8A6; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:800; color:#0F766E; flex-shrink:0;">
                        {{ i + 1 }}
                    </div>
                    <div>
                        <p style="margin:0; font-size:14px; font-weight:600; color:#1E293B;">{{ paso.titulo }}</p>
                        <p style="margin:2px 0 0; font-size:12px; color:#64748B;">{{ paso.desc }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
    empresa: { type: Object, required: true },
})

const form = ref({
    regimen_tributario: props.empresa.regimen_tributario || 'GENERAL',
    nubefact_token:     props.empresa.nubefact_token || '',
    nubefact_demo:      props.empresa.nubefact_demo || false,
    serie_boleta:       props.empresa.serie_boleta || 'B001',
    serie_factura:      props.empresa.serie_factura || 'F001',
    proveedor:          'apisunat',
})

const verToken    = ref(false)
const guardando   = ref(false)
const probando    = ref(false)
const resultadoPrueba = ref(null)

const config = computed(() => ({
    activo: !!props.empresa.nubefact_token && !!props.empresa.serie_boleta
}))

const regimenes = [
    { valor: 'GENERAL',   label: 'Régimen General',      icon: '🏢', desc: 'IGV 18% + IR 29.5%' },
    { valor: 'MYPE',      label: 'MYPE Tributario',      icon: '🏪', desc: 'IGV 18% + IR 10% hasta 15 UIT' },
    { valor: 'RER',       label: 'Régimen Especial RER', icon: '📋', desc: 'IGV 18% + IR 1.5%' },
    { valor: 'RUS',       label: 'Nuevo RUS (NRUS)',     icon: '🛒', desc: 'Sin IGV, cuota fija mensual' },
    { valor: 'EXONERADO', label: 'Zona Exonerada',       icon: '🌿', desc: 'Amazonia - Sin IGV' },
]

const proveedores = [
    { valor: 'apisunat', label: 'APISUNAT',  icon: '⚡', desc: '' },
    
]

const tokenUrl = computed(() =>
    form.value.proveedor === 'apisunat'
        ? 'https://apisunat.com/profile'
        : 'https://www.nubefact.com/'
)

const pasos = [
    { titulo: 'Crea tu cuenta en APISUNAT', desc: 'Regístrate en apisunat.com con el RUC del negocio' },
    { titulo: 'Copia tu token API', desc: 'Ve a tu perfil en APISUNAT y copia el token' },
    { titulo: 'Autoriza en SUNAT SOL', desc: 'En sunat.gob.pe > SOL > autoriza a APISUNAT como emisor' },
    { titulo: 'Pega el token aquí y guarda', desc: 'Selecciona RUS si aplica y guarda la configuración' },
    { titulo: 'Prueba la conexión', desc: 'Usa el botón "Probar conexión" para verificar que todo funciona' },
]

const guardar = () => {
    guardando.value = true
    router.post('/configuracion/facturacion', form.value, {
        onSuccess: () => { guardando.value = false },
        onError:   () => { guardando.value = false },
    })
}

const probarConexion = async () => {
    probando.value = true
    resultadoPrueba.value = null
    try {
        const res = await axios.post('/configuracion/facturacion/probar', {
            token: form.value.nubefact_token,
            proveedor: form.value.proveedor,
        })
        resultadoPrueba.value = { ok: true, mensaje: 'Conexión exitosa con ' + res.data.proveedor }
    } catch (e) {
        resultadoPrueba.value = { ok: false, mensaje: 'Token inválido o error de conexión' }
    } finally {
        probando.value = false
    }
}
</script>
