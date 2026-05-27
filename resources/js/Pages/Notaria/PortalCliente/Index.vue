<template>
    <div style="min-height:100vh; background:linear-gradient(135deg, #667eea 0%, #764ba2 100%); display:flex; align-items:center; justify-content:center; padding:20px;">
        
        <div style="background:#FFFFFF; border-radius:24px; padding:48px; max-width:500px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
            
            <!-- Logo / Header -->
            <div style="text-align:center; margin-bottom:32px;">
                <div style="width:80px; height:80px; background:linear-gradient(135deg, #14B8A6 0%, #0D9488 100%); border-radius:20px; margin:0 auto 20px; display:flex; align-items:center; justify-content:center; font-size:36px;">
                    📄
                </div>
                <h1 style="font-size:28px; font-weight:800; color:#1E293B; margin:0 0 8px;">Portal del Cliente</h1>
                <p style="font-size:14px; color:#64748B; margin:0;">Consulta el estado de tu trámite notarial</p>
            </div>

            <!-- Mensaje de bienvenida si vino desde QR -->
            <div v-if="desdeQR" style="background:#ECFDF5; border:2px solid #14B8A6; border-radius:12px; padding:16px; margin-bottom:24px; text-align:center;">
                <p style="color:#065F46; font-size:14px; margin:0; font-weight:600;">
                    ✓ Datos cargados desde el código QR
                </p>
                <p style="color:#0D9488; font-size:12px; margin:6px 0 0;">
                    Haz clic en "Consultar Trámite" para ver tu expediente
                </p>
            </div>

            <!-- Errores -->
            <div v-if="$page.props.errors.numero_documento || $page.props.errors.numero_expediente" 
                style="background:#FEE2E2; border:1px solid #FCA5A5; border-radius:12px; padding:16px; margin-bottom:24px;">
                <p style="color:#991B1B; font-size:14px; margin:0; font-weight:600;">
                    {{ $page.props.errors.numero_documento || $page.props.errors.numero_expediente }}
                </p>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="consultar">
                
                <div style="margin-bottom:24px;">
                    <label style="display:block; font-size:14px; font-weight:600; color:#1E293B; margin-bottom:8px;">
                        DNI / RUC
                    </label>
                    <input 
                        v-model="form.numero_documento"
                        type="text"
                        placeholder="Ingresa tu DNI o RUC"
                        required
                        style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:15px; transition:all 0.2s;"
                        @focus="$event.target.style.borderColor='#14B8A6'"
                        @blur="$event.target.style.borderColor='#E2E8F0'"
                    />
                    <p style="font-size:12px; color:#94A3B8; margin:6px 0 0;">8 dígitos para DNI, 11 para RUC</p>
                </div>

                <div style="margin-bottom:32px;">
                    <label style="display:block; font-size:14px; font-weight:600; color:#1E293B; margin-bottom:8px;">
                        Número de Expediente
                    </label>
                    <input 
                        v-model="form.numero_expediente"
                        type="text"
                        placeholder="EXP-2026-00001"
                        required
                        style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:15px; transition:all 0.2s;"
                        @focus="$event.target.style.borderColor='#14B8A6'"
                        @blur="$event.target.style.borderColor='#E2E8F0'"
                    />
                    <p style="font-size:12px; color:#94A3B8; margin:6px 0 0;">Formato: EXP-XXXX-XXXXX</p>
                </div>

                <button 
                    type="submit"
                    :disabled="loading"
                    style="width:100%; padding:16px; background:linear-gradient(135deg, #14B8A6 0%, #0D9488 100%); color:#FFFFFF; font-weight:700; font-size:16px; border:none; border-radius:12px; cursor:pointer; transition:all 0.2s;"
                    @mouseover="$event.target.style.transform='translateY(-2px)'; $event.target.style.boxShadow='0 8px 20px rgba(20,184,166,0.4)'"
                    @mouseout="$event.target.style.transform='translateY(0)'; $event.target.style.boxShadow='none'"
                >
                    {{ loading ? 'Consultando...' : 'Consultar Trámite' }}
                </button>

            </form>

            <!-- Footer -->
            <div style="margin-top:32px; padding-top:24px; border-top:1px solid #E2E8F0; text-align:center;">
                <p style="font-size:13px; color:#94A3B8; margin:0;">
                    ¿Tienes dudas? Comunícate con nosotros
                </p>
                <p style="font-size:14px; color:#14B8A6; font-weight:600; margin:8px 0 0;">
                    📞 (01) 123-4567 | 📧 info@notaria.com
                </p>
            </div>

        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const form = ref({
    numero_documento: '',
    numero_expediente: ''
})

const loading = ref(false)
const desdeQR = ref(false)

// Pre-llenar desde URL params (cuando viene del QR)
onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search)
    const doc = urlParams.get('doc')
    const exp = urlParams.get('exp')
    
    if (doc && exp) {
        form.value.numero_documento = doc
        form.value.numero_expediente = exp
        desdeQR.value = true
        
        // Auto-consultar después de 2 segundos si vino del QR
        setTimeout(() => {
            if (desdeQR.value) {
                consultar()
            }
        }, 2000)
    }
})

const consultar = () => {
    loading.value = true
    router.post('/portal-cliente/consultar', form.value, {
        onFinish: () => {
            loading.value = false
        }
    })
}
</script>
