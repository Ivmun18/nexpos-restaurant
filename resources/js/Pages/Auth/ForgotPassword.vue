<template>
    <div style="min-height:100vh; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#0F766E,#14B8A6); padding:20px;">
        <div style="background:white; border-radius:24px; padding:48px; width:100%; max-width:440px; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
            
            <!-- Logo/Título -->
            <div style="text-align:center; margin-bottom:32px;">
                <h1 style="font-size:32px; font-weight:800; color:#1E293B; margin:0 0 8px;">🔐</h1>
                <h2 style="font-size:24px; font-weight:800; color:#1E293B; margin:0;">¿Olvidaste tu contraseña?</h2>
                <p style="font-size:14px; color:#64748B; margin:8px 0 0;">No te preocupes, te enviaremos un enlace para restablecerla.</p>
            </div>

            <!-- Mensaje de éxito -->
            <div v-if="$page.props.flash.success" 
                style="background:#D1FAE5; border:2px solid #10B981; border-radius:12px; padding:16px; margin-bottom:24px;">
                <p style="color:#065F46; margin:0; font-size:14px; font-weight:600;">✅ {{ $page.props.flash.success }}</p>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="submit">
                
                <!-- Email -->
                <div style="margin-bottom:24px;">
                    <label style="display:block; font-size:14px; font-weight:600; color:#475569; margin-bottom:8px;">
                        Correo electrónico
                    </label>
                    <input 
                        v-model="form.email"
                        type="email" 
                        required
                        placeholder="tu@email.com"
                        style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;"
                        :style="form.errors.email ? { borderColor: '#EF4444' } : {}"
                    />
                    <p v-if="form.errors.email" style="color:#EF4444; font-size:12px; margin:6px 0 0;">
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- Botón enviar -->
                <button 
                    type="submit"
                    :disabled="form.processing"
                    style="width:100%; padding:16px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; font-size:16px; font-weight:700; cursor:pointer; margin-bottom:16px;"
                    :style="form.processing ? { opacity: 0.6 } : {}"
                >
                    {{ form.processing ? 'Enviando...' : '📧 Enviar enlace de recuperación' }}
                </button>

                <!-- Link volver -->
                <div style="text-align:center;">
                    <a href="/login" style="color:#14B8A6; text-decoration:none; font-size:14px; font-weight:600;">
                        ← Volver al inicio de sesión
                    </a>
                </div>

            </form>

        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    email: '',
})

function submit() {
    form.post('/forgot-password', {
        preserveScroll: true,
    })
}
</script>
