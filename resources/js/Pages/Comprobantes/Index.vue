<template>
    <AppLayout title="Comprobantes Electrónicos" subtitle="Boletas y facturas emitidas">
        
        <!-- Filtros -->
        <div style="background:white; border-radius:16px; padding:20px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
            <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                
                <!-- Tipo de comprobante -->
                <div style="flex:1; min-width:180px;">
                    <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:6px;">
                        📄 Tipo
                    </label>
                    <select v-model="filtros.tipo" @change="aplicarFiltros"
                        style="width:100%; padding:10px 14px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B; cursor:pointer;">
                        <option value="">Todos</option>
                        <option value="01">Facturas</option>
                        <option value="03">Boletas</option>
                        <option value="07">Notas de Crédito</option>
                    </select>
                </div>

                <!-- Fecha desde -->
                <div style="flex:1; min-width:180px;">
                    <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:6px;">
                        📅 Desde
                    </label>
                    <input type="date" v-model="filtros.desde" @change="aplicarFiltros"
                        style="width:100%; padding:10px 14px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B;">
                </div>

                <!-- Fecha hasta -->
                <div style="flex:1; min-width:180px;">
                    <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:6px;">
                        📅 Hasta
                    </label>
                    <input type="date" v-model="filtros.hasta" @change="aplicarFiltros"
                        style="width:100%; padding:10px 14px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B;">
                </div>

                <!-- Botón limpiar -->
                <div style="flex:1; min-width:180px; display:flex; align-items:flex-end;">
                    <button @click="limpiarFiltros"
                        style="width:100%; padding:10px 20px; background:#F1F5F9; color:#64748B; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                        🔄 Limpiar
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de comprobantes -->
        <div style="background:white; border-radius:16px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
            
            <div v-if="comprobantes.data.length === 0" style="text-align:center; padding:60px 20px;">
                <div style="font-size:48px; margin-bottom:16px;">📄</div>
                <p style="font-size:16px; color:#64748B; margin:0;">No hay comprobantes emitidos</p>
            </div>

            <div v-else style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:2px solid #F1F5F9;">
                            <th style="padding:12px; text-align:left; font-size:12px; color:#64748B; font-weight:700;">TIPO</th>
                            <th style="padding:12px; text-align:left; font-size:12px; color:#64748B; font-weight:700;">NÚMERO</th>
                            <th style="padding:12px; text-align:left; font-size:12px; color:#64748B; font-weight:700;">FECHA</th>
                            <th style="padding:12px; text-align:left; font-size:12px; color:#64748B; font-weight:700;">CLIENTE</th>
                            <th style="padding:12px; text-align:right; font-size:12px; color:#64748B; font-weight:700;">TOTAL</th>
                            <th style="padding:12px; text-align:center; font-size:12px; color:#64748B; font-weight:700;">ESTADO</th>
                            <th style="padding:12px; text-align:center; font-size:12px; color:#64748B; font-weight:700;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="comp in comprobantes.data" :key="comp.id" 
                            style="border-bottom:1px solid #F1F5F9; cursor:pointer;"
                            @click="verComprobante(comp)">
                            
                            <td style="padding:14px 12px;">
                                <span :style="tipoStyle(comp.tipo_comprobante)">
                                    {{ comp.tipo_comprobante_nombre }}
                                </span>
                            </td>
                            
                            <td style="padding:14px 12px; font-size:14px; font-weight:600; color:#1E293B;">
                                {{ comp.numero_completo }}
                            </td>
                            
                            <td style="padding:14px 12px; font-size:14px; color:#64748B;">
                                {{ formatFecha(comp.fecha_emision) }}
                            </td>
                            
                            <td style="padding:14px 12px;">
                                <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">
                                    {{ comp.cliente_nombre }}
                                </p>
                                <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">
                                    {{ comp.cliente_numero_documento }}
                                </p>
                            </td>
                            
                            <td style="padding:14px 12px; text-align:right; font-size:15px; font-weight:700; color:#14B8A6;">
                                S/ {{ formatNumber(comp.total) }}
                            </td>
                            
                            <td style="padding:14px 12px; text-align:center;">
                                <span :style="estadoStyle(comp)">
                                    {{ comp.aceptada_por_sunat ? '✓ SUNAT' : '⏳ Pendiente' }}
                                </span>
                            </td>
                            
                            <td style="padding:14px 12px; text-align:center;">
                                <div style="display:flex; gap:8px; justify-content:center;">
                                    <a v-if="comp.enlace_pdf" :href="comp.enlace_pdf" target="_blank"
                                        @click.stop
                                        style="padding:6px 12px; background:#EF4444; color:white; border-radius:6px; font-size:12px; font-weight:600; text-decoration:none;">
                                        📄 PDF
                                    </a>
                                    <a v-if="comp.enlace_xml" :href="comp.enlace_xml" target="_blank"
                                        @click.stop
                                        style="padding:6px 12px; background:#10B981; color:white; border-radius:6px; font-size:12px; font-weight:600; text-decoration:none;">
                                        📋 XML
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div v-if="comprobantes.last_page > 1" style="margin-top:24px; display:flex; justify-content:center; gap:8px;">
                    <button v-for="page in comprobantes.last_page" :key="page"
                        @click="irAPagina(page)"
                        :style="paginaStyle(page === comprobantes.current_page)"
                        style="padding:8px 14px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:1px solid #E2E8F0;">
                        {{ page }}
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    comprobantes: Object,
    filtros: Object,
})

const filtros = ref({
    tipo: props.filtros.tipo || '',
    desde: props.filtros.desde || '',
    hasta: props.filtros.hasta || '',
})

const tipoStyle = (tipo) => {
    const colors = {
        '01': { bg: '#DBEAFE', color: '#1E40AF', text: 'Factura' },
        '03': { bg: '#D1FAE5', color: '#065F46', text: 'Boleta' },
        '07': { bg: '#FEE2E2', color: '#991B1B', text: 'N/C' },
    }
    const style = colors[tipo] || { bg: '#F3F4F6', color: '#6B7280', text: 'N/A' }
    
    return {
        padding: '4px 10px',
        background: style.bg,
        color: style.color,
        borderRadius: '6px',
        fontSize: '12px',
        fontWeight: '700',
    }
}

const estadoStyle = (comp) => {
    return {
        padding: '4px 10px',
        background: comp.aceptada_por_sunat ? '#D1FAE5' : '#FEF3C7',
        color: comp.aceptada_por_sunat ? '#065F46' : '#92400E',
        borderRadius: '6px',
        fontSize: '12px',
        fontWeight: '700',
    }
}

const paginaStyle = (activa) => {
    return {
        background: activa ? 'linear-gradient(135deg,#14B8A6,#0F766E)' : 'white',
        color: activa ? 'white' : '#64748B',
    }
}

const formatFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString('es-PE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    })
}

const formatNumber = (num) => {
    return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num || 0)
}

const aplicarFiltros = () => {
    router.get('/comprobantes', filtros.value, {
        preserveState: true,
        preserveScroll: true,
    })
}

const limpiarFiltros = () => {
    filtros.value = { tipo: '', desde: '', hasta: '' }
    aplicarFiltros()
}

const verComprobante = (comp) => {
    router.get(`/comprobantes/${comp.id}`)
}

const irAPagina = (page) => {
    router.get('/comprobantes', { ...filtros.value, page }, {
        preserveState: true,
        preserveScroll: true,
    })
}
</script>
