<template>
    <!-- Notaría: diseño compacto azul -->
    <div v-if="esNotaria" style="min-height:100vh; background:#3B5998; display:flex; align-items:center; justify-content:center; padding:1rem;">
        <div style="background:white; border-radius:16px; padding:2.5rem 2rem; width:100%; max-width:360px; box-shadow:0 8px 32px rgba(108,142,191,0.15);">
            <div style="text-align:center; margin-bottom:2rem;">
                <div style="display:inline-flex; align-items:center; justify-content:center; background:#3B5998; border-radius:14px; width:56px; height:56px; margin-bottom:1rem;">
                    <span style="font-size:28px;">⚡</span>
                </div>
                <p style="font-size:22px; font-weight:900; color:#3B5998; margin:0; letter-spacing:1px;">NEXPOS</p>
                <p style="font-size:11px; color:#6B7280; letter-spacing:3px; margin:4px 0 0; text-transform:uppercase;">Sistema Notarial</p>
            </div>
            <div style="margin-bottom:1rem;">
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Correo o usuario</label>
                <div style="display:flex; align-items:center; border:1.5px solid #D1D5DB; border-radius:8px; padding:0 12px; height:44px; background:#F9FAFB;">
                    <svg style="margin-right:8px; flex-shrink:0;" width="16" height="16" fill="none" stroke="#9CA3AF" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <input v-model="form.email" type="text" placeholder="Correo o usuario" style="flex:1; border:none; background:transparent; font-size:13px; color:#1E293B; outline:none; width:100%;" />
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


    <!-- Dental: diseño compacto celeste -->
    <div v-else-if="esDental" style="min-height:100vh; background:#F0F4FF; display:flex; align-items:center; justify-content:center; padding:1rem;">
        <div style="background:white; border-radius:16px; padding:2.5rem 2rem; width:100%; max-width:360px; box-shadow:0 8px 32px rgba(108,142,191,0.15);">
            <div style="text-align:center; margin-bottom:2rem;">
                <div style="display:inline-flex; align-items:center; justify-content:center; background:#6C8EBF; border-radius:14px; width:56px; height:56px; margin-bottom:1rem;">
                    <span style="font-size:28px;">🦷</span>
                </div>
                <p style="font-size:22px; font-weight:900; color:#5570B0; margin:0; letter-spacing:1px;">NEXPOS</p>
                <p style="font-size:11px; color:#6B7280; letter-spacing:3px; margin:4px 0 0; text-transform:uppercase;">Sistema Dental</p>
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
            <button @click="submit" :disabled="procesando" style="width:100%; background:#6C8EBF; color:white; border:none; border-radius:8px; height:44px; font-size:14px; font-weight:600; cursor:pointer;">
                <span v-if="procesando">⏳ Ingresando...</span>
                <span v-else>🦷 Ingresar al sistema</span>
            </button>
            <div style="border-top:1px solid #F3F4F6; margin-top:1.5rem; padding-top:1rem; text-align:center;">
                <span style="font-size:10px; color:#9CA3AF; letter-spacing:1px;">NEXPOS v2.0 — dental.nexposolution.com</span>
            </div>
        </div>
    </div>

    <!-- Otras industrias: diseño océano / cevichería -->
    <div v-else class="login-ocean-bg" style="min-height:100vh; position:relative; display:flex; align-items:center; justify-content:center; padding:1.5rem; overflow:hidden; font-family:system-ui,sans-serif; background:linear-gradient(135deg, #0a3d62 0%, #1e6091 100%);">

        <!-- Overlay oscuro semitransparente -->
        <div style="position:absolute; inset:0; background:rgba(0,0,0,0.28);"></div>

        <!-- Círculos decorativos -->
        <div style="position:absolute; inset:0; overflow:hidden; pointer-events:none;">
            <div style="position:absolute; width:500px; height:500px; border-radius:50%; background:rgba(255,255,255,0.05); top:-180px; left:-160px;"></div>
            <div style="position:absolute; width:380px; height:380px; border-radius:50%; background:rgba(255,255,255,0.06); bottom:-140px; right:-120px;"></div>
        </div>

        <!-- Ola decorativa -->
        <svg style="position:absolute; bottom:0; left:0; width:100%; height:120px; opacity:0.15; pointer-events:none;" viewBox="0 0 1440 120" preserveAspectRatio="none">
            <path fill="#ffffff" d="M0,64 C240,120 480,0 720,32 C960,64 1200,120 1440,64 L1440,120 L0,120 Z"></path>
        </svg>

        <!-- Card -->
        <div class="login-card-animate" style="position:relative; z-index:10; width:100%; max-width:420px; background:rgba(255,255,255,0.88); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); border-radius:28px; padding:2.75rem 2.25rem; box-shadow:0 25px 60px rgba(10,61,98,0.35), 0 2px 8px rgba(10,61,98,0.15);">

            <!-- Logo / icono -->
            <div style="text-align:center; margin-bottom:1.75rem;">
                <img v-if="empresa?.logo" :src="logoUrl" alt="Logo"
                    style="width:68px; height:68px; border-radius:18px; object-fit:cover; margin:0 auto 1rem; display:block; box-shadow:0 8px 20px rgba(10,61,98,0.25);" />
                <div v-else style="width:68px; height:68px; border-radius:18px; background:linear-gradient(135deg,#1e6091,#2980b9); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; box-shadow:0 8px 20px rgba(30,96,145,0.35);">
                    <span style="font-size:32px;">🍴</span>
                </div>

                <h2 style="font-size:23px; font-weight:800; color:#0a3d62; margin:0 0 4px; letter-spacing:-0.5px;">
                    {{ tituloEmpresa }}
                </h2>
                <p style="font-size:12px; color:#64748B; margin:0; font-weight:500; letter-spacing:1.5px; text-transform:uppercase;">
                    Sistema de Gestión
                </p>
            </div>

            <!-- Formulario -->
            <div style="display:flex; flex-direction:column; gap:16px; margin-bottom:20px;">

                <!-- Email -->
                <div>
                    <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Correo electrónico</label>
                    <div style="position:relative;">
                        <svg style="position:absolute; left:14px; top:50%; transform:translateY(-50%);" width="18" height="18" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <input v-model="form.email" type="email" placeholder="correo@empresa.com"
                            style="width:100%; padding:13px 14px 13px 44px; border:2px solid #DCE7EF; border-radius:12px; font-size:14px; color:#0a3d62; outline:none; box-sizing:border-box; background:#F5F9FB;"
                            @focus="e => { e.target.style.borderColor='#1e6091'; e.target.style.background='white' }"
                            @blur="e => { e.target.style.borderColor='#DCE7EF'; e.target.style.background='#F5F9FB' }"/>
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
                            style="width:100%; padding:13px 44px 13px 44px; border:2px solid #DCE7EF; border-radius:12px; font-size:14px; color:#0a3d62; outline:none; box-sizing:border-box; background:#F5F9FB;"
                            @focus="e => { e.target.style.borderColor='#1e6091'; e.target.style.background='white' }"
                            @blur="e => { e.target.style.borderColor='#DCE7EF'; e.target.style.background='#F5F9FB' }"/>
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
            <div style="text-align:right; margin-bottom:18px; margin-top:-6px;">
                <a href="/forgot-password" style="color:#1e6091; text-decoration:none; font-size:13px; font-weight:600;">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <!-- Error -->
            <div v-if="error" style="background:#FEF2F2; border:1px solid #FECACA; border-radius:10px; padding:12px 16px; margin-bottom:16px; display:flex; align-items:center; gap:10px;">
                <svg width="18" height="18" fill="none" stroke="#EF4444" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                <span style="font-size:13px; color:#DC2626; font-weight:500;">{{ error }}</span>
            </div>

            <!-- Botón -->
            <button @click="submit" :disabled="procesando" class="ocean-btn"
                style="width:100%; padding:15px; background:linear-gradient(135deg,#1e6091,#2980b9); color:white; border:none; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer; box-shadow:0 8px 24px rgba(30,96,145,0.35); display:flex; align-items:center; justify-content:center; gap:8px;">
                <span v-if="procesando">⏳ Ingresando...</span>
                <span v-else>🚀 Ingresar al sistema</span>
            </button>

            <p style="text-align:center; font-size:11px; color:#94A3B8; margin-top:1.75rem;">
                NEXPOS v2.0 — Powered by Anthropic Claude
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    empresa: { type: Object, default: null },
})

const form = ref({ email: '', password: '' })
const error = ref('')
const procesando = ref(false)
const mostrarPassword = ref(false)

const esNotaria = computed(() => window.location.hostname === 'notaria.nexposolution.com')
const esDental  = computed(() => window.location.hostname === 'dental.nexposolution.com')

const tituloEmpresa = computed(() => props.empresa?.nombre_comercial || props.empresa?.razon_social || 'Bienvenido')

const logoUrl = computed(() => {
    const logo = props.empresa?.logo
    if (!logo) return ''
    return /^https?:\/\//.test(logo) ? logo : '/storage/' + logo
})

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

<style scoped>
@keyframes fadeSlideUp {
    from { opacity: 0; transform: translateY(28px); }
    to   { opacity: 1; transform: translateY(0); }
}
.login-card-animate {
    animation: fadeSlideUp 0.65s cubic-bezier(0.16, 1, 0.3, 1);
}
.ocean-btn {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.ocean-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 14px 32px rgba(30,96,145,0.5);
}
@media (max-width: 480px) {
    .login-card-animate {
        padding: 2.25rem 1.5rem !important;
        border-radius: 22px !important;
    }
}
</style>