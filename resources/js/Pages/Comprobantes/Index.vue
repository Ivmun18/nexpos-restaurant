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
                        <option value="ticket">Tickets</option>
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

                <!-- Botón filtrar -->
                <div style="flex:1; min-width:180px; display:flex; align-items:flex-end;">
                    <button @click="aplicarFiltros"
                        style="width:100%; padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                        🔍 Filtrar
                    </button>
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
                            <th style="padding:12px; text-align:left; font-size:12px; color:#64748B; font-weight:700;">FECHA</th>
                            <th style="padding:12px; text-align:left; font-size:12px; color:#64748B; font-weight:700;">TIPO</th>
                            <th style="padding:12px; text-align:left; font-size:12px; color:#64748B; font-weight:700;">CLIENTE</th>
                            <th style="padding:12px; text-align:left; font-size:12px; color:#64748B; font-weight:700;">MESA</th>
                            <th style="padding:12px; text-align:left; font-size:12px; color:#64748B; font-weight:700;">CAJERO</th>
                            <th style="padding:12px; text-align:left; font-size:12px; color:#64748B; font-weight:700;">MÉTODO DE PAGO</th>
                            <th style="padding:12px; text-align:right; font-size:12px; color:#64748B; font-weight:700;">TOTAL</th>
                            <th style="padding:12px; text-align:center; font-size:12px; color:#64748B; font-weight:700;">ESTADO</th>
                            <th style="padding:12px; text-align:center; font-size:12px; color:#64748B; font-weight:700;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="comp in comprobantes.data" :key="comp.id"
                            :style="{ borderBottom:'1px solid #F1F5F9', cursor: comp.source === 'sunat' ? 'pointer' : 'default' }"
                            @click="verComprobante(comp)">

                            <td style="padding:14px 12px; font-size:14px; color:#64748B;">
                                {{ formatFecha(comp.fecha) }}
                            </td>

                            <td style="padding:14px 12px;">
                                <span :style="tipoStyle(comp.tipo)">
                                    {{ comp.tipo }}
                                </span>
                                <p v-if="comp.numero && comp.numero !== '—'" style="font-size:11px; color:#94A3B8; margin:4px 0 0;">
                                    {{ comp.numero }}
                                </p>
                            </td>

                            <td style="padding:14px 12px; font-size:14px; font-weight:600; color:#1E293B;">
                                {{ comp.source === 'ticket' ? 'Clientes Varios' : comp.cliente }}
                            </td>

                            <td style="padding:14px 12px; font-size:14px; color:#64748B;">
                                {{ comp.mesa || '-' }}
                            </td>

                            <td style="padding:14px 12px; font-size:14px; color:#64748B;">
                                {{ comp.cajero || '-' }}
                            </td>

                            <td style="padding:14px 12px; font-size:14px; color:#64748B; text-transform:capitalize;">
                                {{ comp.metodo_pago || '-' }}
                            </td>

                            <td style="padding:14px 12px; text-align:right; font-size:15px; font-weight:700; color:#14B8A6;">
                                S/ {{ formatNumber(comp.total) }}
                            </td>

                            <td style="padding:14px 12px; text-align:center;">
                                <span :style="{padding:'4px 10px', background:estadoInfo(comp.estado).bg, color:estadoInfo(comp.estado).color, borderRadius:'6px', fontSize:'12px', fontWeight:'700'}">
                                    {{ estadoInfo(comp.estado).label }}
                                </span>
                            </td>

                            <td style="padding:14px 12px; text-align:center;">
                                <div v-if="comp.source === 'ticket'" style="display:flex; gap:8px; justify-content:center;">
                                    <button @click.stop="verTicket(comp)"
                                        style="padding:6px 12px; background:#F1F5F9; color:#475569; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        👁️ Ver detalle
                                    </button>
                                    <button @click.stop="imprimirTicket(comp)"
                                        style="padding:6px 12px; background:#2563EB; color:white; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        🖨️ Imprimir
                                    </button>
                                </div>
                                <span v-else style="font-size:12px; color:#94A3B8;">Ver detalle →</span>
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
    empresa: Object,
})

const filtros = ref({
    tipo: props.filtros.tipo || '',
    desde: props.filtros.desde || '',
    hasta: props.filtros.hasta || '',
})

const tipoStyle = (tipo) => {
    const colors = {
        'Boleta':       { bg: '#D1FAE5', color: '#065F46' },
        'Factura':      { bg: '#DBEAFE', color: '#1E40AF' },
        'Nota Crédito': { bg: '#FEE2E2', color: '#991B1B' },
        'Ticket':       { bg: '#F3F4F6', color: '#6B7280' },
    }
    const style = colors[tipo] || { bg: '#F3F4F6', color: '#6B7280' }

    return {
        padding: '4px 10px',
        background: style.bg,
        color: style.color,
        borderRadius: '6px',
        fontSize: '12px',
        fontWeight: '700',
    }
}

const estadoInfo = (estado) => {
    const map = {
        aceptado:  { bg: '#D1FAE5', color: '#065F46', label: '✓ Aceptado' },
        emitido:   { bg: '#FEF3C7', color: '#92400E', label: '⏳ Pendiente' },
        pendiente: { bg: '#FEF3C7', color: '#92400E', label: '⏳ Pendiente' },
        rechazado: { bg: '#FEE2E2', color: '#991B1B', label: '✗ Rechazado' },
        anulado:   { bg: '#F3F4F6', color: '#6B7280', label: 'Anulado' },
        ticket:    { bg: '#EFF6FF', color: '#1E40AF', label: 'Ticket' },
    }
    return map[estado] || map.emitido
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
    if (comp.source !== 'sunat') return
    router.get(`/comprobantes/${comp.source_id}`)
}

const verTicket = (comp) => {
    router.get(`/tickets/${comp.source_id}`)
}

const imprimirTicket = (item) => {
    const e = props.empresa || {}
    const fecha = item.fecha ? new Date(item.fecha) : new Date()
    const fechaStr = fecha.toLocaleDateString('es-PE')
    const horaStr = fecha.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })

    const contenido = `
        <div style="text-align:center;margin-bottom:6px;">
            <p style="font-size:16px;font-weight:bold;margin:0;font-family:'Georgia',serif;">${e.nombre_comercial || e.razon_social || 'RESTAURANTE'}</p>
            <p style="font-size:10px;margin:2px 0;">RUC: ${e.ruc || ''}</p>
            <p style="font-size:10px;margin:2px 0;">${e.direccion || ''}</p>
        </div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div style="text-align:center;margin-bottom:6px;">
            <p style="font-size:12px;font-weight:bold;margin:0;">TICKET DE VENTA</p>
            <p style="font-size:10px;margin:2px 0;">(sin comprobante electrónico)</p>
        </div>
        <div style="font-size:10px;margin-bottom:6px;">
            <div style="display:flex;justify-content:space-between;"><span>FECHA:</span><span>${fechaStr}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>HORA:</span><span>${horaStr}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>MESA:</span><span>${item.mesa || '-'}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>CAJERO:</span><span>${item.cliente || '-'}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>MÉTODO PAGO:</span><span>${(item.metodo_pago || '-').toUpperCase()}</span></div>
        </div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div style="font-size:13px;font-weight:bold;display:flex;justify-content:space-between;">
            <span>TOTAL:</span><span>S/ ${Number(item.total || 0).toFixed(2)}</span>
        </div>
        <div style="border-top:1px dashed #000;margin:8px 0;"></div>
        <div style="text-align:center;font-size:9px;">
            <p>Documento sin validez tributaria</p>
            <p style="margin-top:4px;">Sistema desarrollado por NEXPOS Solutions</p>
        </div>
    `
    const ventana = window.open('', '_blank')
    ventana.document.write('<html><head><title>Ticket</title><style>body{font-family:monospace;padding:4px;max-width:80mm;margin:0 auto;font-size:11px;line-height:1.3;}*{box-sizing:border-box;margin:0;padding:0;}p{margin:1px 0;}@media print{@page{margin:2mm;size:80mm auto;}}</style></head><body>' + contenido + '</body></html>')
    ventana.document.close()
    setTimeout(() => ventana.print(), 500)
}

const irAPagina = (page) => {
    router.get('/comprobantes', { ...filtros.value, page }, {
        preserveState: true,
        preserveScroll: true,
    })
}
</script>
