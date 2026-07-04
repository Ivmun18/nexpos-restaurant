<template>
    <!-- Notaría: diseño compacto azul -->
    <div v-if="esNotaria" style="min-height:100vh; background:#3B5998; display:flex; align-items:center; justify-content:center; padding:1rem;">
        <div style="background:white; border-radius:16px; padding:2.5rem 2rem; width:100%; max-width:360px;">
            <div style="text-align:center; margin-bottom:2rem;">
                <div style="display:inline-flex; align-items:center; justify-content:center; background:#3B5998; border-radius:14px; width:56px; height:56px; margin-bottom:1rem;">
                    <span style="font-size:28px;">⚡</span>
                </div>
                <p style="font-size:22px; font-weight:900; color:#3B5998; margin:0; letter-spacing:1px;">NEXPOS</p>
                <p style="font-size:11px; color:#6B7280; letter-spacing:3px; margin:4px 0 0; text-transform:uppercase;">Notaría Herrera</p>
            </div>
            <div style="margin-bottom:1rem;">
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Correo electrónico</label>
                <div style="display:flex; align-items:center; border:1.5px solid #D1D5DB; border-radius:8px; padding:0 12px; height:44px; background:#F9FAFB;">
                    <svg style="margin-right:8px; flex-shrink:0;" width="16" height="16" fill="none" stroke="#9CA3AF" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <input v-model="form.email" type="email" placeholder="correo@empresa.com" style="flex:1; border:none; background:transparent; font-size:13px; color:#1E293B; outline:none; width:100%;" />
                </div>
            </div>
            <div style="margin-bottom:1.5rem;">
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Contraseña</label>
                <div style="display:flex; align-items:center; border:1.5px solid #D1D5DB; border-radius:8px; padding:0 12px; height:44px; background:#F9FAFB;">
                    <svg style="margin-right:8px; flex-shrink:0;" width="16" height="16" fill="none" stroke="#9CA3AF" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input v-model="form.password" :type="mostrarPassword ? 'text' : 'password'" placeholder="••••••••••" style="flex:1; border:none; background:transparent; font-size:13px; color:#1E293B; outline:none; width:100%;" />
                    <span @click="mostrarPassword=!mostrarPassword" style="cursor:pointer;">
                        <svg width="16" height="16" fill="none" stroke="#9CA3AF" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </span>
                </div>
            </div>
            <div v-if="error" style="background:#FEF2F2; border:1px solid #FECACA; border-radius:8px; padding:10px 14px; margin-bottom:16px; display:flex; align-items:center; gap:8px;">
                <svg width="16" height="16" fill="none" stroke="#EF4444" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                <span style="font-size:12px; color:#DC2626; font-weight:500;">{{ error }}</span>
            </div>
            <button @click="submit" :disabled="procesando" style="width:100%; background:#3B5998; color:white; border:none; border-radius:8px; height:44px; font-size:14px; font-weight:600; cursor:pointer;">
                <span v-if="procesando">⏳ Ingresando...</span>
                <span v-else>🚀 Ingresar al sistema</span>
            </button>
            <div style="border-top:1px solid #F3F4F6; margin-top:1.5rem; padding-top:1rem; text-align:center;">
                <span style="font-size:10px; color:#9CA3AF; letter-spacing:1px;">NEXPOS v2.0 — notaria.nexposolution.com</span>
            </div>
        </div>
    </div>

    <!-- Otras industrias: diseño original -->
    <div v-else<div style="min-height:100vh; display:flex; overflow:hidden; font-family:system-ui,sans-serif;">

        <!-- Panel izquierdo - Branding NEXPOS -->
        <div style="flex:1; position:relative; overflow:hidden; display:flex; flex-direction:column; align-items:center; justify-content:center; background:linear-gradient(135deg, #0F766E 0%, #134E4A 100%);">

            <!-- Círculos decorativos -->
            <div style="position:absolute; inset:0; overflow:hidden;">
                <div style="position:absolute; width:600px; height:600px; border-radius:50%; background:rgba(255,255,255,0.04); top:-200px; left:-200px;"></div>
                <div style="position:absolute; width:400px; height:400px; border-radius:50%; background:rgba(255,255,255,0.06); bottom:-100px; right:-100px;"></div>
                <div style="position:absolute; width:200px; height:200px; border-radius:50%; background:rgba(255,255,255,0.04); top:40%; left:10%;"></div>
            </div>

            <div style="position:relative; z-index:10; text-align:center; padding:3rem;">

                <!-- Icono -->
                <div style="width:100px; height:100px; background:rgba(255,255,255,0.15); border-radius:28px; display:flex; align-items:center; justify-content:center; margin:0 auto 2rem; backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.2);">
                    <span style="font-size:52px;">⚡</span>
                </div>

                <!-- Nombre -->
                <p style="font-size:64px; font-weight:900; color:white; margin:0; letter-spacing:-3px; line-height:1;">NEXPOS</p>
                <p style="font-size:14px; color:rgba(255,255,255,0.7); margin:8px 0 20px; letter-spacing:4px; text-transform:uppercase; font-weight:500;">Software Solutions</p>

                <!-- Separador -->
                <div style="width:60px; height:3px; background:rgba(255,255,255,0.3); border-radius:2px; margin:0 auto 20px;"></div>

                <!-- Descripción -->
                <p style="font-size:18px; color:rgba(255,255,255,0.75); margin:0; font-weight:300; line-height:1.6; max-width:320px;">
                    Sistema de gestión multi-industria para tu negocio
                </p>

                 <p style="font-size:12px; color:rgba(255,255,255,0.35); margin-top:2.5rem;">
                    v2.0 — Tecnología de última generación
                </p>
            </div>
        </div>

        <!-- Panel derecho - formulario -->
        <div style="width:500px; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:3rem; background:white; position:relative;">

            <!-- Decoración -->
            <div style="position:absolute; top:0; right:0; width:150px; height:150px; background:linear-gradient(135deg,#F0FDFA,#CCFBF1); border-radius:0 0 0 100%;"></div>
            <div style="position:absolute; bottom:0; left:0; width:100px; height:100px; background:linear-gradient(135deg,#F0FDFA,#CCFBF1); border-radius:0 100% 0 0;"></div>

            <div style="width:100%; max-width:380px; position:relative; z-index:10;">

                <!-- Logo -->
                <div style="text-align:center; margin-bottom:2.5rem;">
                    <div style="display:inline-flex; align-items:center; gap:14px; margin-bottom:1.5rem;">
                        <div style="width:56px; height:56px; background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:16px; display:flex; align-items:center; justify-content:center; box-shadow:0 8px 24px rgba(20,184,166,0.3);">
                            <span style="font-size:28px;">⚡</span>
                        </div>
                        <div style="text-align:left;">
                            <p style="font-size:28px; font-weight:900; color:#0F766E; margin:0; letter-spacing:-1px;">NEXPOS</p>
                            <p style="font-size:11px; color:#94A3B8; margin:0; font-weight:500; letter-spacing:2px; text-transform:uppercase;">Sistema Multi-Industry</p>
                        </div>
                    </div>
                    <h2 style="font-size:22px; font-weight:700; color:#1E293B; margin:0 0 6px;">Bienvenido de vuelta 👋</h2>
                    <p style="font-size:14px; color:#94A3B8; margin:0;">Ingresa tus credenciales para continuar</p>
                </div>

                <!-- Formulario -->
                <div style="display:flex; flex-direction:column; gap:16px; margin-bottom:24px;">

                    <!-- Email -->
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Correo electrónico</label>
                        <div style="position:relative;">
                            <svg style="position:absolute; left:14px; top:50%; transform:translateY(-50%);" width="18" height="18" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <input v-model="form.email" type="email" placeholder="correo@empresa.com"
                                style="width:100%; padding:13px 14px 13px 44px; border:2px solid #E2E8F0; border-radius:12px; font-size:14px; color:#1E293B; outline:none; box-sizing:border-box; background:#F8FAFC;"
                                @focus="e => { e.target.style.borderColor='#14B8A6'; e.target.style.background='white' }"
                                @blur="e => { e.target.style.borderColor='#E2E8F0'; e.target.style.background='#F8FAFC' }"/>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Contraseña</label>
                        <div style="position:relative;">
                            <svg style="position:absolute; left:14px; top:50%; transform:translateY(-50%);" width="18" height="18" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                            <input v-model="form.password" :type="mostrarPassword ? 'text' : 'password'" placeholder="••••••••"
                                style="width:100%; padding:13px 44px 13px 44px; border:2px solid #E2E8F0; border-radius:12px; font-size:14px; color:#1E293B; outline:none; box-sizing:border-box; background:#F8FAFC;"
                                @focus="e => { e.target.style.borderColor='#14B8A6'; e.target.style.background='white' }"
                                @blur="e => { e.target.style.borderColor='#E2E8F0'; e.target.style.background='#F8FAFC' }"/>
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
                    </div>
                </div>

                <!-- Olvidé contraseña -->
                <div style="text-align:right; margin-bottom:20px; margin-top:-8px;">
                    <a href="/forgot-password" style="color:#14B8A6; text-decoration:none; font-size:13px; font-weight:600;">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <!-- Error -->
                <div v-if="error" style="background:#FEF2F2; border:1px solid #FECACA; border-radius:10px; padding:12px 16px; margin-bottom:16px; display:flex; align-items:center; gap:10px;">
                    <svg width="18" height="18" fill="none" stroke="#EF4444" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    <span style="font-size:13px; color:#DC2626; font-weight:500;">{{ error }}</span>
                </div>

                <!-- Botón -->
                <button @click="submit" :disabled="procesando"
                    style="width:100%; padding:15px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer; box-shadow:0 8px 24px rgba(20,184,166,0.3); display:flex; align-items:center; justify-content:center; gap:8px;">
                    <span v-if="procesando">⏳ Ingresando...</span>
                    <span v-else>🚀 Ingresar al sistema</span>
                </button>

                <p style="text-align:center; font-size:11px; color:#CBD5E1; margin-top:2rem;">
                    NEXPOS v2.0 — Powered by Anthropic Claude
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const form = ref({ email: '', password: '' })
const error = ref('')
const procesando = ref(false)
const mostrarPassword = ref(false)

const esNotaria = computed(() => window.location.hostname === 'notaria.nexposolution.com')

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