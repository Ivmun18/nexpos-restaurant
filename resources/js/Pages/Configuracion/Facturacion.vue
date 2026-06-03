<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ empresa: Object })

const form = ref({
    regimen_tributario:   props.empresa.regimen_tributario || 'GENERAL',
    serie_boleta:         props.empresa.serie_boleta || 'B001',
    serie_factura:        props.empresa.serie_factura || 'F001',
    apisunat_token:       props.empresa.apisunat_token || '',
    apisunat_usuario_sol: props.empresa.apisunat_usuario_sol || '',
    apisunat_clave_sol:   props.empresa.apisunat_clave_sol || '',
    apisunat_demo:        props.empresa.apisunat_demo ?? true,
})

const guardando = ref(false)
const probando = ref(false)
const resultadoPrueba = ref(null)
const success = ref(false)

const regimenes = [
    { valor: 'GENERAL', label: 'Régimen General', icon: '🏢' },
    { valor: 'MYPE',    label: 'MYPE Tributario', icon: '🏪' },
    { valor: 'RER',     label: 'RER',             icon: '📋' },
    { valor: 'RUS',     label: 'RUS',             icon: '🛵' },
]

const guardar = () => {
    guardando.value = true
    router.post('/configuracion/facturacion', form.value, {
        onSuccess: () => { guardando.value = false; success.value = true; setTimeout(() => success.value = false, 3000) },
        onError: () => { guardando.value = false }
    })
}

const probar = async () => {
    probando.value = true
    resultadoPrueba.value = null
    try {
        const res = await fetch('/configuracion/facturacion/probar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ token: form.value.apisunat_token })
        })
        const data = await res.json()
        resultadoPrueba.value = data.ok
            ? { ok: true,  mensaje: data.mensaje || 'Conexion exitosa con APISUNAT' }
            : { ok: false, mensaje: data.mensaje || 'Token invalido' }
    } catch(e) {
        resultadoPrueba.value = { ok: false, mensaje: 'Error de conexion' }
    } finally { probando.value = false }
}
</script>

<template>
    <AppLayout title="Configuracion Facturacion">
        <div style="padding:24px; max-width:700px; margin:0 auto;">
            <h1 style="font-size:22px; font-weight:700; margin:0 0 6px;">⚡ Facturación Electrónica</h1>
            <p style="color:#64748B; font-size:13px; margin:0 0 28px;">Configura tu cuenta APISUNAT para emitir boletas y facturas electrónicas a SUNAT.</p>

            <!-- Alerta exito -->
            <div v-if="success" style="background:#F0FDF4; border:1px solid #86EFAC; border-radius:10px; padding:12px 16px; margin-bottom:20px; color:#16A34A; font-weight:600;">
                ✅ Configuración guardada correctamente
            </div>

            <!-- Regimen tributario -->
            <div style="background:#fff; border-radius:12px; border:1px solid #E2E8F0; padding:20px; margin-bottom:16px;">
                <h2 style="font-size:15px; font-weight:700; margin:0 0 14px;">📋 Régimen Tributario</h2>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:8px;">
                    <button v-for="r in regimenes" :key="r.valor" type="button" @click="form.regimen_tributario = r.valor"
                        :style="{padding:'10px 8px', borderRadius:'10px', border: form.regimen_tributario===r.valor ? '2px solid #14B8A6' : '2px solid #E2E8F0',
                        background: form.regimen_tributario===r.valor ? '#F0FDFA' : '#fff', cursor:'pointer', fontSize:'12px', fontWeight:'700',
                        color: form.regimen_tributario===r.valor ? '#0F766E' : '#64748B', textAlign:'center'}">
                        {{ r.icon }}<br>{{ r.label }}
                    </button>
                </div>
                <p v-if="form.regimen_tributario === 'RUS'" style="margin:8px 0 0; font-size:12px; color:#0F766E; background:#F0FDFA; padding:8px 12px; border-radius:8px;">
                    ✅ RUS: Las boletas se emitirán sin IGV (inafectas)
                </p>
            </div>

            <!-- Series -->
            <div style="background:#fff; border-radius:12px; border:1px solid #E2E8F0; padding:20px; margin-bottom:16px;">
                <h2 style="font-size:15px; font-weight:700; margin:0 0 14px;">📄 Series de Comprobantes</h2>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Serie Boleta</label>
                        <input v-model="form.serie_boleta" placeholder="B001" maxlength="4"
                            style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Serie Factura</label>
                        <input v-model="form.serie_factura" placeholder="F001" maxlength="4"
                            :disabled="form.regimen_tributario === 'RUS'"
                            style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
                    </div>
                </div>
            </div>

            <!-- ApiSunat credenciales -->
            <div style="background:#fff; border-radius:12px; border:1px solid #E2E8F0; padding:20px; margin-bottom:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                    <h2 style="font-size:15px; font-weight:700; margin:0;">⚡ Credenciales APISUNAT</h2>
                    <a href="https://apisunat.com/profile" target="_blank"
                        style="font-size:12px; color:#14B8A6; text-decoration:none; font-weight:600;">
                        Obtener credenciales →
                    </a>
                </div>

                <div style="display:grid; gap:14px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Token API</label>
                        <input v-model="form.apisunat_token" placeholder="Pega tu token de APISUNAT aquí..."
                            style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box; font-family:monospace;" />
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Usuario SOL</label>
                            <input v-model="form.apisunat_usuario_sol" placeholder="RUCUSUARIO"
                                style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;" />
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Clave SOL</label>
                            <input v-model="form.apisunat_clave_sol" type="password" placeholder="••••••••"
                                style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;" />
                        </div>
                    </div>

                    <!-- Modo demo toggle -->
                    <div style="display:flex; align-items:center; justify-content:space-between; background:#F8FAFC; border-radius:10px; padding:12px 16px;">
                        <div>
                            <div style="font-size:13px; font-weight:600;">Modo Demo (Pruebas)</div>
                            <div style="font-size:11px; color:#64748B;">Activo: emite comprobantes de prueba sin valor tributario real</div>
                        </div>
                        <button type="button" @click="form.apisunat_demo = !form.apisunat_demo"
                            :style="{width:'48px', height:'26px', borderRadius:'13px', border:'none', cursor:'pointer', position:'relative', transition:'background 0.2s',
                            background: form.apisunat_demo ? '#14B8A6' : '#CBD5E1'}">
                            <span :style="{position:'absolute', top:'3px', width:'20px', height:'20px', borderRadius:'50%', background:'#fff', transition:'left 0.2s',
                                left: form.apisunat_demo ? '25px' : '3px'}"></span>
                        </button>
                    </div>

                    <!-- Probar conexion -->
                    <div>
                        <button @click="probar" :disabled="!form.apisunat_token || probando"
                            style="background:#0F766E; color:#fff; border:none; padding:9px 20px; border-radius:8px; font-weight:600; cursor:pointer; font-size:13px;">
                            {{ probando ? 'Probando...' : '🔌 Probar conexión' }}
                        </button>
                        <div v-if="resultadoPrueba" :style="{marginTop:'10px', padding:'10px 14px', borderRadius:'8px', fontSize:'13px', fontWeight:'600',
                            background: resultadoPrueba.ok ? '#F0FDF4' : '#FEF2F2',
                            color: resultadoPrueba.ok ? '#16A34A' : '#DC2626',
                            border: resultadoPrueba.ok ? '1px solid #86EFAC' : '1px solid #FCA5A5'}">
                            {{ resultadoPrueba.ok ? '✅' : '❌' }} {{ resultadoPrueba.mensaje }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pasos de configuracion -->
            <div style="background:#EFF6FF; border-radius:12px; border:1px solid #BFDBFE; padding:20px; margin-bottom:20px;">
                <h2 style="font-size:14px; font-weight:700; margin:0 0 12px; color:#1D4ED8;">📘 Pasos para activar facturación real</h2>
                <ol style="margin:0; padding-left:20px; font-size:13px; color:#1E40AF; line-height:1.8;">
                    <li>Crea tu cuenta en <a href="https://apisunat.com" target="_blank" style="color:#1D4ED8; font-weight:600;">apisunat.com</a> con el RUC del negocio</li>
                    <li>Ve a tu perfil y copia el <b>Token API</b></li>
                    <li>Ingresa tu <b>Usuario SOL</b> y <b>Clave SOL</b> de SUNAT</li>
                    <li>Pega el token arriba y presiona <b>Probar conexión</b></li>
                    <li>Desactiva el <b>Modo Demo</b> cuando estés listo para emitir real</li>
                    <li>Guarda la configuración</li>
                </ol>
            </div>

            <!-- Boton guardar -->
            <button @click="guardar" :disabled="guardando"
                style="width:100%; background:#14B8A6; color:#fff; border:none; padding:13px; border-radius:10px; font-size:15px; font-weight:700; cursor:pointer;">
                {{ guardando ? 'Guardando...' : '💾 Guardar configuración' }}
            </button>
        </div>
    </AppLayout>
</template>
