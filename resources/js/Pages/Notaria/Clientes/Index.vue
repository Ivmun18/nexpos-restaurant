<template>
    <AppLayout title="Clientes Notaría">
        <div style="padding:24px; max-width:1200px; margin:0 auto;">
            <div style="margin-bottom:20px;">
                <h1 style="font-size:22px; font-weight:700; color:#1E293B; margin:0 0 4px;">👥 Clientes</h1>
                <p style="color:#64748B; font-size:13px; margin:0;">Historial de clientes de la notaría</p>
            </div>
            <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:16px 20px; margin-bottom:20px; display:flex; gap:12px;">
                <input v-model="busqueda" @keyup.enter="buscar" type="text" placeholder="Buscar por nombre o DNI/RUC..."
                    style="flex:1; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                <button @click="buscar" style="padding:9px 20px; background:#4F46E5; color:white; border:none; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer;">🔍 Buscar</button>
            </div>
            <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
                <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                    <thead style="background:#F8FAFC;">
                        <tr>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Cliente</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">DNI/RUC</th>
                            <th style="padding:12px 16px; text-align:center; font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Actos</th>
                            <th style="padding:12px 16px; text-align:right; font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Facturado</th>
                            <th style="padding:12px 16px; text-align:right; font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Pagado</th>
                            <th style="padding:12px 16px; text-align:right; font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Saldo</th>
                            <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:700; color:#64748B; text-transform:uppercase;">Último acto</th>
                            <th style="padding:12px 16px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in clientes.data" :key="c.id" style="border-top:1px solid #F1F5F9;">
                            <td style="padding:12px 16px;">
                                <p style="font-weight:700; color:#1E293B; margin:0;">{{ c.razon_social }}</p>
                                <p style="font-size:11px; color:#94A3B8; margin:0;">{{ c.email || c.telefono || '-' }}</p>
                            </td>
                            <td style="padding:12px 16px; font-family:monospace; color:#374151;">{{ c.numero_documento }}</td>
                            <td style="padding:12px 16px; text-align:center;">
                                <span style="background:#EEF2FF; color:#4F46E5; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:700;">{{ c.total_actos }}</span>
                            </td>
                            <td style="padding:12px 16px; text-align:right; color:#374151;">S/ {{ Number(c.total_facturado||0).toFixed(2) }}</td>
                            <td style="padding:12px 16px; text-align:right; color:#16A34A; font-weight:600;">S/ {{ Number(c.total_pagado||0).toFixed(2) }}</td>
                            <td style="padding:12px 16px; text-align:right; font-weight:700;" :style="{ color: (c.total_facturado - c.total_pagado) > 0 ? '#DC2626' : '#16A34A' }">
                                S/ {{ Number((c.total_facturado||0) - (c.total_pagado||0)).toFixed(2) }}
                            </td>
                            <td style="padding:12px 16px; color:#64748B; font-size:12px;">{{ c.ultimo_acto ? formatFecha(c.ultimo_acto) : '-' }}</td>
                            <td style="padding:12px 16px;">
                                <a :href="'/notaria/clientes/' + c.id" style="background:#EEF2FF; color:#4F46E5; padding:6px 14px; border-radius:8px; font-size:12px; font-weight:600; text-decoration:none;">Ver historial →</a>
                            </td>
                        </tr>
                        <tr v-if="!clientes.data?.length">
                            <td colspan="8" style="text-align:center; padding:40px; color:#94A3B8;">No hay clientes registrados</td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
            <div style="display:flex; justify-content:center; gap:8px; margin-top:16px;">
                <a v-if="clientes.prev_page_url" :href="clientes.prev_page_url" style="padding:6px 16px; background:white; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; text-decoration:none; color:#374151;">← Anterior</a>
                <span style="padding:6px 16px; background:#4F46E5; color:white; border-radius:8px; font-size:13px;">{{ clientes.current_page }}</span>
                <a v-if="clientes.next_page_url" :href="clientes.next_page_url" style="padding:6px 16px; background:white; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; text-decoration:none; color:#374151;">Siguiente →</a>
            </div>
        </div>
    </AppLayout>
</template>
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
const props = defineProps({
    clientes: { type: Object, default: () => ({}) },
    buscar:   { type: String, default: '' },
})
const busqueda = ref(props.buscar || '')
const formatFecha = (f) => f ? new Date(f + 'T00:00:00').toLocaleDateString('es-PE') : '-'
const buscar = () => { window.location.href = '/notaria/clientes?buscar=' + busqueda.value }
</script>
