<template>
    <div style="min-height:100vh; background:#f0fdf4; display:flex; align-items:center; justify-content:center; padding:2rem;">
        <div style="width:100%; max-width:400px; background:white; border-radius:16px; border:1px solid #bbf7d0; padding:2.5rem; box-shadow:0 8px 32px rgba(22,163,74,0.08);">

            <!-- Logo -->
            <div style="text-align:center; margin-bottom:2rem;">
                <div style="width:72px; height:72px; background:#f0fdf4; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; border:1px solid #bbf7d0;">
                    <svg width="34" height="34" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
                    </svg>
                </div>
                <p style="font-size:22px; font-weight:600; margin:0; color:#1e293b;">
                    <span style="color:#16a34a;">Mini</span>Market
                </p>
                <p style="color:#94a3b8; font-size:13px; margin:4px 0 0;">Tu minimarket de confianza</p>
                <p style="font-size:17px; font-weight:600; color:#1e293b; margin:1.2rem 0 2px;">¡Bienvenido!</p>
                <p style="color:#94a3b8; font-size:13px; margin:0;">Inicia sesión para continuar</p>
            </div>

            <!-- Email -->
            <div style="margin-bottom:12px; position:relative;">
                <svg style="position:absolute; left:14px; top:50%; transform:translateY(-50%);" width="18" height="18" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                <input v-model="form.email" type="email" placeholder="Correo electrónico"
                    style="width:100%; padding:13px 14px 13px 44px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:14px; color:#1e293b; outline:none; box-sizing:border-box; background:#f8fafc;"
                    @focus="e => { e.target.style.borderColor='#16a34a'; e.target.style.background='white' }"
                    @blur="e => { e.target.style.borderColor='#e2e8f0'; e.target.style.background='#f8fafc' }"/>
            </div>

            <!-- Password -->
            <div style="margin-bottom:1.5rem; position:relative;">
                <svg style="position:absolute; left:14px; top:50%; transform:translateY(-50%);" width="18" height="18" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
                <input v-model="form.password" :type="mostrarPassword ? 'text' : 'password'" placeholder="••••••••"
                    style="width:100%; padding:13px 44px 13px 44px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:14px; color:#1e293b; outline:none; box-sizing:border-box; background:#f8fafc;"
                    @focus="e => { e.target.style.borderColor='#16a34a'; e.target.style.background='white' }"
                    @blur="e => { e.target.style.borderColor='#e2e8f0'; e.target.style.background='#f8fafc' }"/>
                <button type="button" @click="mostrarPassword=!mostrarPassword"
                    style="position:absolute; right:14px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; padding:0; color:#94A3B8;">
                    <svg v-if="!mostrarPassword" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                </button>
            </div>

            <!-- Error -->
            <div v-if="error" style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:12px 16px; margin-bottom:16px; display:flex; align-items:center; gap:10px;">
                <svg width="18" height="18" fill="none" stroke="#EF4444" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                <span style="font-size:13px; color:#dc2626; font-weight:500;">{{ error }}</span>
            </div>

            <!-- Botón -->
            <button @click="submit" :disabled="procesando"
                style="width:100%; padding:13px; background:#16a34a; color:white; border:none; border-radius:10px; font-size:15px; font-weight:600; cursor:pointer;">
                <span v-if="procesando">⏳ Ingresando...</span>
                <span v-else>Iniciar sesión</span>
            </button>

            <p style="text-align:center; color:#94a3b8; font-size:11px; margin-top:1.5rem; margin-bottom:0;">
                Powered by <strong style="color:#16a34a;">NEXPOS Solutions</strong>
            </p>
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
