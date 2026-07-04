<template>
    <div style="min-height:100vh; background:#3B5998; display:flex; align-items:center; justify-content:center; padding:1rem;">
        <div style="background:white; border-radius:16px; padding:2.5rem 2rem; width:100%; max-width:360px;">

            <!-- Logo -->
            <div style="text-align:center; margin-bottom:2rem;">
                <div style="display:inline-flex; align-items:center; justify-content:center; background:#3B5998; border-radius:14px; width:56px; height:56px; margin-bottom:1rem;">
                    <span style="font-size:28px;">⚡</span>
                </div>
                <p style="font-size:22px; font-weight:900; color:#3B5998; margin:0; letter-spacing:1px;">NEXPOS</p>
                <p style="font-size:11px; color:#6B7280; letter-spacing:3px; margin:4px 0 0; text-transform:uppercase;">Notaría Herrera</p>
            </div>

            <!-- Email -->
            <div style="margin-bottom:1rem;">
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Correo electrónico</label>
                <div style="display:flex; align-items:center; border:1.5px solid #D1D5DB; border-radius:8px; padding:0 12px; height:44px; background:#F9FAFB;">
                    <svg style="margin-right:8px; flex-shrink:0;" width="16" height="16" fill="none" stroke="#9CA3AF" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <input v-model="form.email" type="email" placeholder="correo@empresa.com"
                        style="flex:1; border:none; background:transparent; font-size:13px; color:#1E293B; outline:none; width:100%;" />
                </div>
            </div>

            <!-- Password -->
            <div style="margin-bottom:1.5rem;">
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Contraseña</label>
                <div style="display:flex; align-items:center; border:1.5px solid #D1D5DB; border-radius:8px; padding:0 12px; height:44px; background:#F9FAFB;">
                    <svg style="margin-right:8px; flex-shrink:0;" width="16" height="16" fill="none" stroke="#9CA3AF" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input v-model="form.password" :type="mostrarPassword ? 'text' : 'password'" placeholder="••••••••••"
                        style="flex:1; border:none; background:transparent; font-size:13px; color:#1E293B; outline:none; width:100%;" />
                    <span @click="mostrarPassword=!mostrarPassword" style="cursor:pointer;">
                        <svg width="16" height="16" fill="none" stroke="#9CA3AF" stroke-width="2" viewBox="0 0 24 24">
                            <path v-if="!mostrarPassword" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle v-if="!mostrarPassword" cx="12" cy="12" r="3"/>
                            <path v-if="mostrarPassword" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line v-if="mostrarPassword" x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Error -->
            <div v-if="error" style="background:#FEF2F2; border:1px solid #FECACA; border-radius:8px; padding:10px 14px; margin-bottom:16px; display:flex; align-items:center; gap:8px;">
                <svg width="16" height="16" fill="none" stroke="#EF4444" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                <span style="font-size:12px; color:#DC2626; font-weight:500;">{{ error }}</span>
            </div>

            <!-- Botón -->
            <button @click="submit" :disabled="procesando"
                style="width:100%; background:#3B5998; color:white; border:none; border-radius:8px; height:44px; font-size:14px; font-weight:600; cursor:pointer; letter-spacing:0.5px;">
                <span v-if="procesando">⏳ Ingresando...</span>
                <span v-else>🚀 Ingresar al sistema</span>
            </button>

            <div style="border-top:1px solid #F3F4F6; margin-top:1.5rem; padding-top:1rem; text-align:center;">
                <span style="font-size:10px; color:#9CA3AF; letter-spacing:1px;">NEXPOS v2.0 — notaria.nexposolution.com</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const form = ref({ email: '', password: '' })
const error = ref('')
const procesando = ref(false)
const mostrarPassword = ref(false)

const submit = () => {
    error.value = ''
    procesando.value = true
    router.post('/login', form.value, {
        onError: (errors) => {
            error.value = errors.email || errors.password || 'Credenciales incorrectas.'
            procesando.value = false
        },
        onSuccess: () => { procesando.value = false }
    })
}
</script>
