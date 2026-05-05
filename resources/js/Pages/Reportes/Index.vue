<template>
    <AppLayout title="Reportes de Ventas" subtitle="Análisis y estadísticas del restaurante">
        
        <!-- Filtros -->
        <div style="background:white; border-radius:16px; padding:24px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
            <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
                
                <!-- Tipo de reporte -->
                <div style="flex:1; min-width:200px;">
                    <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:8px;">
                        Período
                    </label>
                    <select v-model="filtros.tipo" @change="aplicarFiltro" 
                        style="width:100%; padding:12px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B; cursor:pointer;">
                        <option value="dia">Hoy</option>
                        <option value="semana">Esta semana</option>
                        <option value="mes">Este mes</option>
                        <option value="personalizado">Personalizado</option>
                    </select>
                </div>

                <!-- Fechas personalizadas -->
                <div v-if="filtros.tipo === 'personalizado'" style="flex:1; min-width:200px;">
                    <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:8px;">
                        Desde
                    </label>
                    <input type="date" v-model="filtros.fecha_inicio" @change="aplicarFiltro"
                        style="width:100%; padding:12px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B;">
                </div>

                <div v-if="filtros.tipo === 'personalizado'" style="flex:1; min-width:200px;">
                    <label style="display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:8px;">
                        Hasta
                    </label>
                    <input type="date" v-model="filtros.fecha_fin" @change="aplicarFiltro"
                        style="width:100%; padding:12px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; color:#1E293B;">
                </div>

                <!-- Botones de exportación -->
                <div style="flex:1; min-width:200px; display:flex; gap:12px; align-items:flex-end;">
                    <button @click="exportarPdf" 
                        style="flex:1; padding:12px 20px; background:linear-gradient(135deg,#EF4444,#DC2626); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                        📄 PDF
                    </button>
                    <button @click="exportarExcel" 
                        style="flex:1; padding:12px 20px; background:linear-gradient(135deg,#10B981,#059669); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                        📊 Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Cards de resumen -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:20px; margin-bottom:24px;">
            
            <!-- Total ingresos -->
            <div style="background:white; border-radius:16px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1); border-left:4px solid #14B8A6;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                    <span style="font-size:13px; color:#64748B; font-weight:600;">INGRESOS TOTALES</span>
                    <div style="width:40px; height:40px; background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px;">
                        💰
                    </div>
                </div>
                <p style="font-size:32px; font-weight:700; color:#1E293B; margin:0;">
                    S/ {{ formatNumber(resumen.total_ingresos) }}
                </p>
                <div v-if="comparativa.porcentaje !== 0" style="display:flex; align-items:center; gap:6px; margin-top:8px;">
                    <span v-if="comparativa.crecimiento" style="color:#10B981; font-size:13px; font-weight:600;">
                        ↑ {{ comparativa.porcentaje }}%
                    </span>
                    <span v-else style="color:#EF4444; font-size:13px; font-weight:600;">
                        ↓ {{ Math.abs(comparativa.porcentaje) }}%
                    </span>
                    <span style="color:#94A3B8; font-size:12px;">vs período anterior</span>
                </div>
            </div>

            <!-- Total pedidos -->
            <div style="background:white; border-radius:16px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1); border-left:4px solid #3B82F6;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                    <span style="font-size:13px; color:#64748B; font-weight:600;">PEDIDOS</span>
                    <div style="width:40px; height:40px; background:linear-gradient(135deg,#3B82F6,#2563EB); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px;">
                        🍽️
                    </div>
                </div>
                <p style="font-size:32px; font-weight:700; color:#1E293B; margin:0;">
                    {{ resumen.total_pedidos }}
                </p>
                <p style="color:#94A3B8; font-size:13px; margin:8px 0 0;">
                    {{ resumen.mesas_atendidas }} mesas atendidas
                </p>
            </div>

            <!-- Ticket promedio -->
            <div style="background:white; border-radius:16px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1); border-left:4px solid #F59E0B;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                    <span style="font-size:13px; color:#64748B; font-weight:600;">TICKET PROMEDIO</span>
                    <div style="width:40px; height:40px; background:linear-gradient(135deg,#F59E0B,#D97706); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px;">
                        🎫
                    </div>
                </div>
                <p style="font-size:32px; font-weight:700; color:#1E293B; margin:0;">
                    S/ {{ formatNumber(resumen.ticket_promedio) }}
                </p>
                <p style="color:#94A3B8; font-size:13px; margin:8px 0 0;">
                    Por pedido
                </p>
            </div>

            <!-- Turnos -->
            <div style="background:white; border-radius:16px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1); border-left:4px solid #8B5CF6;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                    <span style="font-size:13px; color:#64748B; font-weight:600;">TURNOS</span>
                    <div style="width:40px; height:40px; background:linear-gradient(135deg,#8B5CF6,#7C3AED); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px;">
                        ⏰
                    </div>
                </div>
                <p style="font-size:32px; font-weight:700; color:#1E293B; margin:0;">
                    {{ resumen.turnos_abiertos }}
                </p>
                <p style="color:#94A3B8; font-size:13px; margin:8px 0 0;">
                    Cajas abiertas
                </p>
            </div>
        </div>

        <!-- Gráficos -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(500px,1fr)); gap:24px; margin-bottom:24px;">
            
            <!-- Ventas por día -->
            <div style="background:white; border-radius:16px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px;">
                    📈 Ventas por día
                </h3>
                <canvas ref="chartVentasDia" style="max-height:300px;"></canvas>
            </div>

            <!-- Métodos de pago -->
            <div style="background:white; border-radius:16px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px;">
                    💳 Métodos de pago
                </h3>
                <canvas ref="chartMetodosPago" style="max-height:300px;"></canvas>
            </div>
        </div>

        <!-- Ventas por hora -->
        <div style="background:white; border-radius:16px; padding:24px; margin-bottom:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
            <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px;">
                🕐 Ventas por hora del día
            </h3>
            <canvas ref="chartVentasHora" style="max-height:280px;"></canvas>
        </div>

        <!-- Tablas -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(450px,1fr)); gap:24px;">
            
            <!-- Productos top -->
            <div style="background:white; border-radius:16px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px;">
                    🏆 Top 10 Productos
                </h3>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="border-bottom:2px solid #F1F5F9;">
                                <th style="padding:12px; text-align:left; font-size:12px; color:#64748B; font-weight:700;">#</th>
                                <th style="padding:12px; text-align:left; font-size:12px; color:#64748B; font-weight:700;">PRODUCTO</th>
                                <th style="padding:12px; text-align:right; font-size:12px; color:#64748B; font-weight:700;">CANT.</th>
                                <th style="padding:12px; text-align:right; font-size:12px; color:#64748B; font-weight:700;">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(producto, index) in productosTop" :key="index" 
                                style="border-bottom:1px solid #F1F5F9;">
                                <td style="padding:14px 12px;">
                                    <span style="display:inline-block; width:24px; height:24px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border-radius:6px; text-align:center; line-height:24px; font-size:12px; font-weight:700;">
                                        {{ index + 1 }}
                                    </span>
                                </td>
                                <td style="padding:14px 12px; font-size:14px; font-weight:600; color:#1E293B;">
                                    {{ producto.producto }}
                                </td>
                                <td style="padding:14px 12px; text-align:right; font-size:14px; font-weight:600; color:#64748B;">
                                    {{ producto.cantidad }}
                                </td>
                                <td style="padding:14px 12px; text-align:right; font-size:14px; font-weight:700; color:#14B8A6;">
                                    S/ {{ formatNumber(producto.total) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mozos top -->
            <div style="background:white; border-radius:16px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h3 style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 20px;">
                    👨‍🍳 Top 10 Mozos
                </h3>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="border-bottom:2px solid #F1F5F9;">
                                <th style="padding:12px; text-align:left; font-size:12px; color:#64748B; font-weight:700;">#</th>
                                <th style="padding:12px; text-align:left; font-size:12px; color:#64748B; font-weight:700;">MOZO</th>
                                <th style="padding:12px; text-align:right; font-size:12px; color:#64748B; font-weight:700;">PEDIDOS</th>
                                <th style="padding:12px; text-align:right; font-size:12px; color:#64748B; font-weight:700;">VENTAS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(mozo, index) in mozosTop" :key="index" 
                                style="border-bottom:1px solid #F1F5F9;">
                                <td style="padding:14px 12px;">
                                    <span style="display:inline-block; width:24px; height:24px; background:linear-gradient(135deg,#3B82F6,#2563EB); color:white; border-radius:6px; text-align:center; line-height:24px; font-size:12px; font-weight:700;">
                                        {{ index + 1 }}
                                    </span>
                                </td>
                                <td style="padding:14px 12px; font-size:14px; font-weight:600; color:#1E293B;">
                                    {{ mozo.mozo }}
                                </td>
                                <td style="padding:14px 12px; text-align:right; font-size:14px; font-weight:600; color:#64748B;">
                                    {{ mozo.pedidos }}
                                </td>
                                <td style="padding:14px 12px; text-align:right; font-size:14px; font-weight:700; color:#3B82F6;">
                                    S/ {{ formatNumber(mozo.ventas) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import Chart from 'chart.js/auto'

const props = defineProps({
    resumen: Object,
    ventasPorDia: Array,
    productosTop: Array,
    mozosTop: Array,
    metodosPago: Array,
    ventasPorHora: Array,
    comparativa: Object,
    filtros: Object,
})

const filtros = ref({
    tipo: props.filtros.tipo,
    fecha_inicio: props.filtros.fecha_inicio,
    fecha_fin: props.filtros.fecha_fin,
})

const chartVentasDia = ref(null)
const chartMetodosPago = ref(null)
const chartVentasHora = ref(null)

let chartVentasDiaInstance = null
let chartMetodosPagoInstance = null
let chartVentasHoraInstance = null

const formatNumber = (num) => {
    return new Intl.NumberFormat('es-PE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(num || 0)
}

const aplicarFiltro = () => {
    router.get('/reportes-restaurante', filtros.value, {
        preserveState: true,
        preserveScroll: true,
    })
}

const exportarPdf = () => {
    alert('Función de exportación PDF en desarrollo')
}

const exportarExcel = () => {
    alert('Función de exportación Excel en desarrollo')
}

const renderCharts = () => {
    // Gráfico de ventas por día
    if (chartVentasDiaInstance) chartVentasDiaInstance.destroy()
    
    chartVentasDiaInstance = new Chart(chartVentasDia.value, {
        type: 'line',
        data: {
            labels: props.ventasPorDia.map(v => v.fecha_corta),
            datasets: [{
                label: 'Ventas (S/)',
                data: props.ventasPorDia.map(v => v.total_ventas),
                borderColor: '#14B8A6',
                backgroundColor: 'rgba(20, 184, 166, 0.1)',
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => 'S/ ' + value
                    }
                }
            }
        }
    })

    // Gráfico de métodos de pago
    if (chartMetodosPagoInstance) chartMetodosPagoInstance.destroy()
    
    const totalMetodos = props.metodosPago.reduce((sum, m) => sum + m.total, 0)
    
    chartMetodosPagoInstance = new Chart(chartMetodosPago.value, {
        type: 'doughnut',
        data: {
            labels: props.metodosPago.map(m => m.metodo),
            datasets: [{
                data: props.metodosPago.map(m => m.total),
                backgroundColor: props.metodosPago.map(m => m.color),
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const value = context.parsed
                            const percentage = ((value / totalMetodos) * 100).toFixed(1)
                            return `S/ ${value.toFixed(2)} (${percentage}%)`
                        }
                    }
                }
            }
        }
    })

    // Gráfico de ventas por hora
    if (chartVentasHoraInstance) chartVentasHoraInstance.destroy()
    
    chartVentasHoraInstance = new Chart(chartVentasHora.value, {
        type: 'bar',
        data: {
            labels: props.ventasPorHora.map(v => v.hora),
            datasets: [{
                label: 'Ventas (S/)',
                data: props.ventasPorHora.map(v => v.ventas),
                backgroundColor: 'rgba(20, 184, 166, 0.8)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => 'S/ ' + value
                    }
                }
            }
        }
    })
}

onMounted(() => {
    renderCharts()
})

watch(() => props.ventasPorDia, () => {
    renderCharts()
}, { deep: true })
</script>
