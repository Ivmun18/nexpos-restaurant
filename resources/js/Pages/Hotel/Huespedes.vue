<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ huespedes: Object, buscar: String })
const q = ref(props.buscar || '')
let timer = null
const buscar = () => { clearTimeout(timer); timer = setTimeout(() => router.get('/hotel/huespedes', { buscar: q.value }, { preserveState: true }), 400) }
const fmt = (f) => f ? new Date(f).toLocaleDateString('es-PE') : '-'
const money = (n) => 'S/ ' + Number(n||0).toFixed(2)
</script>
<template>
    <AppLayout title="Huéspedes">
        <div style="padding:24px; font-family:'Inter',sans-serif; max-width:1100px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
                <div>
                    <h1 style="font-size:24px; font-weight:800; color:#1E293B; margin:0;">👤 Huéspedes</h1>
                    <p style="font-size:13px; color:#64748B; margin:4px 0 0;">Historial de clientes del hotel</p>
                </div>
                <div style="position:relative;">
                    <input v-model="q" @input="buscar" placeholder="Buscar por nombre, documento o teléfono..."
                        style="width:320px; padding:10px 10px 10px 36px; border:1px solid #E2E8F0; border-radius:10px; font-size:13px; outline:none;" />
                    <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#94A3B8;">🔍</span>
                </div>
            </div>

            <div v-if="huespedes.data.length === 0" style="background:#fff; border-radius:12px; padding:60px; text-align:center; color:#94A3B8; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                <div style="font-size:48px; margin-bottom:12px;">👤</div>
                <div style="font-size:16px; font-weight:600;">{{ buscar ? 'Sin resultados para "' + buscar + '"' : 'Aún no hay huéspedes registrados' }}</div>
            </div>

            <div v-else style="background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,0.08); overflow:hidden;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#F8FAFC;">
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">HUÉSPED</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">DOCUMENTO</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; color:#64748B; font-weight:700;">CONTACTO</th>
                            <th style="padding:12px 16px; text-align:center; font-size:11px; color:#64748B; font-weight:700;">ESTADÍAS</th>
                            <th style="padding:12px 16px; text-align:right; font-size:11px; color:#64748B; font-weight:700;">TOTAL GASTADO</th>
                            <th style="padding:12px 16px; text-align:center; font-size:11px; color:#64748B; font-weight:700;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="h in huespedes.data" :key="h.id" style="border-bottom:1px solid #F1F5F9;">
                            <td style="padding:12px 16px;">
                                <div style="font-size:13px; font-weight:600; color:#1E293B;">{{ h.nombre_completo }}</div>
                                <div style="font-size:11px; color:#94A3B8;">{{ h.nacionalidad }}</div>
                            </td>
                            <td style="padding:12px 16px; font-size:13px; color:#374151;">
                                {{ h.tipo_documento === '1' ? 'DNI' : h.tipo_documento === '7' ? 'Pasaporte' : 'Doc.' }} {{ h.numero_documento }}
                            </td>
                            <td style="padding:12px 16px;">
                                <div v-if="h.telefono" style="font-size:12px; color:#374151;">📞 {{ h.telefono }}</div>
                                <div v-if="h.email" style="font-size:12px; color:#64748B;">✉️ {{ h.email }}</div>
                                <div v-if="!h.telefono && !h.email" style="font-size:12px; color:#94A3B8;">—</div>
                            </td>
                            <td style="padding:12px 16px; text-align:center;">
                                <span style="background:#EFF6FF; color:#3B82F6; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700;">
                                    {{ h.reservas_count }}
                                </span>
                            </td>
                            <td style="padding:12px 16px; text-align:right; font-size:14px; font-weight:700; color:#1E293B;">
                                {{ money(h.total_gastado) }}
                            </td>
                            <td style="padding:12px 16px; text-align:center;">
                                <button @click="router.visit('/hotel/huespedes/' + h.id)"
                                    style="padding:6px 14px; background:#EFF6FF; color:#3B82F6; border:1px solid #BFDBFE; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                                    📋 Ver historial
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div style="display:flex; justify-content:space-between; align-items:center; padding:14px 16px; border-top:1px solid #F1F5F9;">
                    <span style="font-size:12px; color:#64748B;">{{ huespedes.total }} huéspedes en total</span>
                    <div style="display:flex; gap:6px;">
                        <button v-if="huespedes.prev_page_url" @click="router.visit(huespedes.prev_page_url)"
                            style="padding:6px 12px; background:#F1F5F9; border:none; border-radius:6px; cursor:pointer; font-size:12px;">← Anterior</button>
                        <span style="padding:6px 12px; font-size:12px; color:#374151;">Pág. {{ huespedes.current_page }} / {{ huespedes.last_page }}</span>
                        <button v-if="huespedes.next_page_url" @click="router.visit(huespedes.next_page_url)"
                            style="padding:6px 12px; background:#F1F5F9; border:none; border-radius:6px; cursor:pointer; font-size:12px;">Siguiente →</button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
