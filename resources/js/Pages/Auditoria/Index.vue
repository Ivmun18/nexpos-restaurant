<template>
    <AppLayout title="Auditoría" subtitle="Bitácora de cambios y actividades">
        <!-- Filtros -->
        <div style="background: white; border-radius: 10px; border: 1px solid #E2E8F0; padding: 1.5rem; margin-bottom: 1.5rem;">
            <h3 style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 0 0 1rem;">Filtros</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div>
                    <label style="font-size: 12px; color: #64748B; display: block; margin-bottom: 4px;">Módulo</label>
                    <select v-model="filtros.modulo" @change="aplicarFiltros"
                        style="width: 100%; padding: 10px; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 13px;">
                        <option value="">-- Todos --</option>
                        <option v-for="mod in modulos" :key="mod" :value="mod">{{ mod }}</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 12px; color: #64748B; display: block; margin-bottom: 4px;">Acción</label>
                    <select v-model="filtros.accion" @change="aplicarFiltros"
                        style="width: 100%; padding: 10px; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 13px;">
                        <option value="">-- Todos --</option>
                        <option v-for="acc in acciones" :key="acc" :value="acc">{{ acc }}</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 12px; color: #64748B; display: block; margin-bottom: 4px;">Desde</label>
                    <input v-model="filtros.desde" type="date" @change="aplicarFiltros"
                        style="width: 100%; padding: 10px; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 13px;" />
                </div>

                <div>
                    <label style="font-size: 12px; color: #64748B; display: block; margin-bottom: 4px;">Hasta</label>
                    <input v-model="filtros.hasta" type="date" @change="aplicarFiltros"
                        style="width: 100%; padding: 10px; border: 1px solid #E2E8F0; border-radius: 8px; font-size: 13px;" />
                </div>
            </div>
        </div>

        <!-- Tabla de Logs -->
        <div style="background: white; border-radius: 10px; border: 1px solid #E2E8F0;">
            <div v-if="logs.data.length === 0" style="padding: 2rem; text-align: center; color: #94A3B8;">
                No hay registros de auditoría
            </div>

            <div v-else style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                            <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 700; color: #64748B;">Usuario</th>
                            <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 700; color: #64748B;">Acción</th>
                            <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 700; color: #64748B;">Módulo</th>
                            <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 700; color: #64748B;">Detalles</th>
                            <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 700; color: #64748B;">Fecha/Hora</th>
                            <th style="padding: 12px; text-align: center; font-size: 12px; font-weight: 700; color: #64748B;">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="log in logs.data" :key="log.id" style="border-bottom: 1px solid #E2E8F0;">
                            <td style="padding: 12px; font-size: 13px; color: #1E293B;">
                                {{ log.usuario?.name || 'Sistema' }}
                            </td>
                            <td style="padding: 12px; font-size: 13px;">
                                <span :style="getAccionColor(log.accion)">
                                    {{ getAccionLabel(log.accion) }}
                                </span>
                            </td>
                            <td style="padding: 12px; font-size: 13px; color: #64748B;">
                                {{ log.modulo }}
                            </td>
                            <td style="padding: 12px; font-size: 13px; color: #1E293B;">
                                {{ log.detalles }}
                            </td>
                            <td style="padding: 12px; font-size: 12px; color: #64748B;">
                                {{ formatDateTime(log.created_at) }}
                            </td>
                            <td style="padding: 12px; font-size: 12px; text-align: center; color: #94A3B8;">
                                <span v-if="log.ip_address" title="Click para ver detalles"
                                    style="cursor: pointer; font-family: monospace;">{{ log.ip_address.substring(0, 12) }}...</span>
                                <span v-else>-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div v-if="logs.last_page > 1" style="padding: 1rem; display: flex; justify-content: center; gap: 8px; border-top: 1px solid #E2E8F0;">
                <a v-if="logs.current_page > 1" :href="`/auditoria?page=${logs.current_page - 1}`"
                    style="padding: 6px 12px; border: 1px solid #E2E8F0; border-radius: 6px; font-size: 12px; cursor: pointer;">
                    ← Anterior
                </a>
                <span style="padding: 6px 12px; font-size: 12px; color: #64748B;">
                    Página {{ logs.current_page }} de {{ logs.last_page }}
                </span>
                <a v-if="logs.current_page < logs.last_page" :href="`/auditoria?page=${logs.current_page + 1}`"
                    style="padding: 6px 12px; border: 1px solid #E2E8F0; border-radius: 6px; font-size: 12px; cursor: pointer;">
                    Siguiente →
                </a>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, defineProps } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    logs: Object,
    modulos: Array,
    acciones: Array,
})

const filtros = ref({
    modulo: '',
    accion: '',
    usuario_id: '',
    desde: '',
    hasta: '',
})

const aplicarFiltros = () => {
    const params = new URLSearchParams()
    if (filtros.value.modulo) params.append('modulo', filtros.value.modulo)
    if (filtros.value.accion) params.append('accion', filtros.value.accion)
    if (filtros.value.desde) params.append('desde', filtros.value.desde)
    if (filtros.value.hasta) params.append('hasta', filtros.value.hasta)
    
    window.location.href = `/auditoria?${params.toString()}`
}

const getAccionLabel = (accion) => {
    const labels = {
        'create': '✨ Creación',
        'update': '✏️ Actualización',
        'delete': '🗑️ Eliminación',
        'view': '👁️ Visualización'
    }
    return labels[accion] || accion
}

const getAccionColor = (accion) => {
    const colores = {
        'create': 'background: #DCFCE7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;',
        'update': 'background: #FEF3C7; color: #92400E; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;',
        'delete': 'background: #FEE2E2; color: #991B1B; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;',
        'view': 'background: #EFF6FF; color: #0C4A6E; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;'
    }
    return colores[accion] || ''
}

const formatDateTime = (date) => {
    return new Date(date).toLocaleDateString('es-PE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>