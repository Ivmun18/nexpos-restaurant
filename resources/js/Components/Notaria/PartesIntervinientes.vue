<template>
    <div style="background:#fff; border-radius:12px; padding:24px; border:1px solid #E2E8F0; margin-bottom:20px;">
        
        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div>
                <h3 style="font-size:18px; font-weight:700; color:#1E293B; margin:0;">👥 Partes Intervinientes</h3>
                <p style="font-size:13px; color:#64748B; margin:4px 0 0;">Personas que participan en este acto notarial</p>
            </div>
            <button @click="abrirModal" type="button"
                style="padding:10px 16px; background:#14B8A6; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px;">
                <span style="font-size:16px;">+</span> Agregar Parte
            </button>
        </div>

        <!-- Lista de partes -->
        <div v-if="partes.length > 0" style="display:grid; gap:12px;">
            <div v-for="parte in partes" :key="parte.id"
                style="border:1px solid #E2E8F0; border-radius:10px; padding:16px; position:relative;">
                
                <!-- Badge de orden y rol -->
                <div :style="{
                    position:'absolute', top:'-10px', left:'12px', 
                    background:'#14B8A6', color:'#fff', padding:'4px 12px', 
                    borderRadius:'6px', fontSize:'11px', fontWeight:'700'
                }">
                    {{ parte.orden }}° - {{ parte.rol ? parte.rol.toUpperCase() : 'SIN ROL' }}
                </div>

                <!-- Datos principales -->
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:12px; margin-top:8px;">
                    <div>
                        <p style="font-size:11px; color:#64748B; margin:0 0 2px; font-weight:600;">NOMBRE COMPLETO</p>
                        <p style="font-size:14px; color:#1E293B; margin:0; font-weight:600;">{{ parte.nombre_completo }}</p>
                    </div>
                    <div>
                        <p style="font-size:11px; color:#64748B; margin:0 0 2px; font-weight:600;">{{ parte.tipo_documento_label }}</p>
                        <p style="font-size:14px; color:#1E293B; margin:0;">{{ parte.numero_documento }}</p>
                    </div>
                    <div v-if="parte.estado_civil">
                        <p style="font-size:11px; color:#64748B; margin:0 0 2px; font-weight:600;">ESTADO CIVIL</p>
                        <p style="font-size:14px; color:#1E293B; margin:0; text-transform:capitalize;">{{ parte.estado_civil }}</p>
                    </div>
                    <div v-if="parte.domicilio">
                        <p style="font-size:11px; color:#64748B; margin:0 0 2px; font-weight:600;">DOMICILIO</p>
                        <p style="font-size:13px; color:#64748B; margin:0;">{{ parte.domicilio }}</p>
                    </div>
                </div>

                <!-- Cónyuge -->
                <div v-if="parte.nombre_conyuge" 
                    style="margin-top:12px; padding-top:12px; border-top:1px dashed #E2E8F0;">
                    <p style="font-size:11px; color:#64748B; margin:0 0 6px; font-weight:600;">💑 CÓNYUGE</p>
                    <div style="display:grid; grid-template-columns:2fr 1fr 1fr; gap:8px;">
                        <p style="font-size:13px; color:#1E293B; margin:0;">{{ parte.nombre_conyuge }}</p>
                        <p style="font-size:13px; color:#64748B; margin:0;">DNI: {{ parte.dni_conyuge }}</p>
                        <p v-if="parte.regimen_patrimonial" style="font-size:12px; color:#64748B; margin:0;">
                            {{ parte.regimen_patrimonial === 'sociedad_gananciales' ? 'Soc. Gananciales' : 'Sep. Bienes' }}
                        </p>
                    </div>
                </div>

                <!-- Acciones -->
                <div style="position:absolute; top:12px; right:12px; display:flex; gap:6px;">
                    <button @click="editarParte(parte)" type="button"
                        style="padding:6px 10px; background:#F1F5F9; color:#64748B; border:none; border-radius:6px; font-size:12px; cursor:pointer; font-weight:600;">
                        ✏️ Editar
                    </button>
                    <button @click="eliminarParte(parte.id)" type="button"
                        style="padding:6px 10px; background:#FEE2E2; color:#991B1B; border:none; border-radius:6px; font-size:12px; cursor:pointer; font-weight:600;">
                        🗑️
                    </button>
                </div>

            </div>
        </div>

        <!-- Sin partes -->
        <div v-else style="text-align:center; padding:40px 20px; background:#F8FAFC; border-radius:10px; border:2px dashed #E2E8F0;">
            <p style="font-size:48px; margin:0 0 12px;">👥</p>
            <p style="font-size:14px; color:#64748B; margin:0;">No hay partes intervinientes registradas</p>
            <p style="font-size:13px; color:#94A3B8; margin:6px 0 0;">Haz clic en "Agregar Parte" para comenzar</p>
        </div>

        <!-- Modal -->
        <div v-if="modalAbierto" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:100; display:flex; align-items:center; justify-content:center; padding:20px;">
            <div style="background:#fff; border-radius:16px; max-width:800px; width:100%; max-height:90vh; overflow-y:auto;">
                
                <!-- Header Modal -->
                <div style="padding:24px; border-bottom:1px solid #E2E8F0; position:sticky; top:0; background:#fff; z-index:1;">
                    <h3 style="font-size:20px; font-weight:800; color:#1E293B; margin:0;">
                        {{ parteEditando ? 'Editar' : 'Agregar' }} Parte Interviniente
                    </h3>
                </div>

                <!-- Formulario -->
                <form @submit.prevent="guardarParte" style="padding:24px;">
                    
                    <!-- Tipo de persona y rol -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">TIPO DE PERSONA *</label>
                            <select v-model="formParte.tipo_persona" required
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;">
                                <option value="natural">Persona Natural</option>
                                <option value="juridica">Persona Jurídica</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">ROL EN EL ACTO *</label>
                            <select v-model="formParte.rol" required
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;">
                                <option value="">Seleccionar...</option>
                                <option value="vendedor">Vendedor</option>
                                <option value="comprador">Comprador</option>
                                <option value="otorgante">Otorgante</option>
                                <option value="apoderado">Apoderado</option>
                                <option value="testador">Testador</option>
                                <option value="heredero">Heredero</option>
                                <option value="compareciente">Compareciente</option>
                                <option value="testigo">Testigo</option>
                            </select>
                        </div>
                    </div>

                    <!-- Documento -->
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:16px; margin-bottom:16px;">
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">TIPO DOC *</label>
                            <select v-model="formParte.tipo_documento" required
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;">
                                <option value="1">DNI</option>
                                <option value="6">RUC</option>
                                <option value="4">C.E.</option>
                                <option value="7">Pasaporte</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">NÚMERO *</label>
                            <input v-model="formParte.numero_documento" type="text" required
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;" />
                        </div>
                    </div>

                    <!-- Persona Natural -->
                    <div v-if="formParte.tipo_persona === 'natural'" style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-bottom:16px;">
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">NOMBRES *</label>
                            <input v-model="formParte.nombres" type="text" :required="formParte.tipo_persona === 'natural'"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;" />
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">AP. PATERNO</label>
                            <input v-model="formParte.apellido_paterno" type="text"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;" />
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">AP. MATERNO</label>
                            <input v-model="formParte.apellido_materno" type="text"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;" />
                        </div>
                    </div>

                    <!-- Persona Jurídica -->
                    <div v-if="formParte.tipo_persona === 'juridica'" style="margin-bottom:16px;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">RAZÓN SOCIAL *</label>
                        <input v-model="formParte.razon_social" type="text" :required="formParte.tipo_persona === 'juridica'"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;" />
                    </div>

                    <!-- Estado civil (solo persona natural) -->
                    <div v-if="formParte.tipo_persona === 'natural'" style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">ESTADO CIVIL</label>
                            <select v-model="formParte.estado_civil"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;">
                                <option value="">Sin especificar</option>
                                <option value="soltero">Soltero(a)</option>
                                <option value="casado">Casado(a)</option>
                                <option value="viudo">Viudo(a)</option>
                                <option value="divorciado">Divorciado(a)</option>
                                <option value="conviviente">Conviviente</option>
                            </select>
                        </div>
                        <div v-if="['casado', 'conviviente'].includes(formParte.estado_civil)">
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">RÉGIMEN</label>
                            <select v-model="formParte.regimen_patrimonial"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;">
                                <option value="">Sin especificar</option>
                                <option value="sociedad_gananciales">Sociedad de Gananciales</option>
                                <option value="separacion_bienes">Separación de Bienes</option>
                            </select>
                        </div>
                    </div>

                    <!-- Cónyuge (si casado) -->
                    <div v-if="['casado', 'conviviente'].includes(formParte.estado_civil)" 
                        style="background:#F8FAFC; padding:16px; border-radius:8px; margin-bottom:16px;">
                        <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0 0 12px;">💑 Datos del Cónyuge</p>
                        <div style="display:grid; grid-template-columns:2fr 1fr; gap:12px;">
                            <div>
                                <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">NOMBRE COMPLETO</label>
                                <input v-model="formParte.nombre_conyuge" type="text"
                                    style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;" />
                            </div>
                            <div>
                                <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">DNI</label>
                                <input v-model="formParte.dni_conyuge" type="text" maxlength="8"
                                    style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;" />
                            </div>
                        </div>
                    </div>

                    <!-- Domicilio -->
                    <div style="margin-bottom:16px;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">DOMICILIO</label>
                        <input v-model="formParte.domicilio" type="text"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;" />
                    </div>

                    <!-- Contacto -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">TELÉFONO</label>
                            <input v-model="formParte.telefono" type="text"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;" />
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">EMAIL</label>
                            <input v-model="formParte.email" type="email"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px;" />
                        </div>
                    </div>

                    <!-- Botones -->
                    <div style="display:flex; gap:10px; justify-content:flex-end; padding-top:16px; border-top:1px solid #E2E8F0;">
                        <button type="button" @click="cerrarModal"
                            style="padding:10px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="guardando"
                            style="padding:10px 24px; background:#14B8A6; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                            {{ guardando ? 'Guardando...' : (parteEditando ? 'Actualizar' : 'Guardar') }}
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    acto: Object,
    partes: { type: Array, default: () => [] }
})

const modalAbierto = ref(false)
const parteEditando = ref(null)
const guardando = ref(false)

const formParte = ref({
    tipo_persona: 'natural',
    tipo_documento: '1',
    numero_documento: '',
    nombres: '',
    apellido_paterno: '',
    apellido_materno: '',
    razon_social: '',
    rol: '',
    estado_civil: '',
    regimen_patrimonial: '',
    nombre_conyuge: '',
    dni_conyuge: '',
    domicilio: '',
    telefono: '',
    email: '',
})

function abrirModal() {
    resetForm()
    modalAbierto.value = true
}

function guardarParte() {
    guardando.value = true
    
    const url = parteEditando.value 
        ? `/notaria/partes/${parteEditando.value.id}`
        : `/notaria/actos/${props.acto.id}/partes`
    
    const method = parteEditando.value ? 'put' : 'post'

    router[method](url, formParte.value, {
        onSuccess: () => {
            cerrarModal()
        },
        onFinish: () => {
            guardando.value = false
        }
    })
}

function editarParte(parte) {
    parteEditando.value = parte
    formParte.value = { ...parte }
    modalAbierto.value = true
}

function eliminarParte(parteId) {
    if (confirm('¿Eliminar esta parte interviniente?')) {
        router.delete(`/notaria/partes/${parteId}`)
    }
}

function cerrarModal() {
    modalAbierto.value = false
    resetForm()
}

function resetForm() {
    parteEditando.value = null
    formParte.value = {
        tipo_persona: 'natural',
        tipo_documento: '1',
        numero_documento: '',
        nombres: '',
        apellido_paterno: '',
        apellido_materno: '',
        razon_social: '',
        rol: '',
        estado_civil: '',
        regimen_patrimonial: '',
        nombre_conyuge: '',
        dni_conyuge: '',
        domicilio: '',
        telefono: '',
        email: '',
    }
}
</script>
