<template>
    <AppLayout title="Cuentas por Pagar" subtitle="Control de pagos a proveedores">
        <!-- Resumen -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div style="background: white; border-radius: 10px; border: 1px solid #E2E8F0; padding: 1.5rem;">
                <p style="font-size: 12px; color: #64748B; margin: 0;">Total Pendiente</p>
                <h2 style="font-size: 24px; font-weight: 700; color: #DC2626; margin: 0.5rem 0;">S/ {{ calcularTotal('pendiente') }}</h2>
            </div>
            <div style="background: white; border-radius: 10px; border: 1px solid #E2E8F0; padding: 1.5rem;">
                <p style="font-size: 12px; color: #64748B; margin: 0;">Pagos Parciales</p>
                <h2 style="font-size: 24px; font-weight: 700; color: #F59E0B; margin: 0.5rem 0;">S/ {{ calcularTotal('parcial') }}</h2>
            </div>
            <div style="background: white; border-radius: 10px; border: 1px solid #E2E8F0; padding: 1.5rem;">
                <p style="font-size: 12px; color: #64748B; margin: 0;">Vencidas</p>
                <h2 style="font-size: 24px; font-weight: 700; color: #EF4444; margin: 0.5rem 0;">{{ totales.vencidas || 0 }}</h2>
            </div>
        </div>

        <!-- Tabla de Cuentas -->
        <div style="background: white; border-radius: 10px; border: 1px solid #E2E8F0;">
            <div style="padding: 1.5rem; border-bottom: 1px solid #E2E8F0;">
                <h3 style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 0;">Cuentas por Pagar</h3>
            </div>

            <div v-if="cuentas.data.length === 0" style="padding: 2rem; text-align: center; color: #94A3B8;">
                No hay cuentas por pagar
            </div>

            <div v-else style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                            <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 700; color: #64748B;">Proveedor</th>
                            <th style="padding: 12px; text-align: right; font-size: 12px; font-weight: 700; color: #64748B;">Monto Total</th>
                            <th style="padding: 12px; text-align: right; font-size: 12px; font-weight: 700; color: #64748B;">Pendiente</th>
                            <th style="padding: 12px; text-align: center; font-size: 12px; font-weight: 700; color: #64748B;">Vencimiento</th>
                            <th style="padding: 12px; text-align: center; font-size: 12px; font-weight: 700; color: #64748B;">Estado</th>
                            <th style="padding: 12px; text-align: center; font-size: 12px; font-weight: 700; color: #64748B;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="cuenta in cuentas.data" :key="cuenta.id"
                            style="border-bottom: 1px solid #E2E8F0;">
                            <td style="padding: 12px; font-size: 13px; color: #1E293B;">
                                {{ cuenta.proveedor.razon_social }}
                            </td>
                            <td style="padding: 12px; text-align: right; font-size: 13px; font-weight: 600; color: #1E293B;">
                                S/ {{ Number(cuenta.monto_total).toFixed(2) }}
                            </td>
                            <td style="padding: 12px; text-align: right; font-size: 13px; font-weight: 600; color: #DC2626;">
                                S/ {{ Number(cuenta.monto_pendiente).toFixed(2) }}
                            </td>
                            <td style="padding: 12px; text-align: center; font-size: 13px; color: #64748B;">
                                {{ formatDate(cuenta.fecha_vencimiento) }}
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <span :style="getEstadoStyle(cuenta.estado)">
                                    {{ cuenta.estado.toUpperCase() }}
                                </span>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                <a :href="`/cuentas-por-pagar/${cuenta.id}`"
                                    style="color: #2563EB; text-decoration: none; font-size: 12px; font-weight: 600;">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div v-if="cuentas.last_page > 1" style="padding: 1rem; display: flex; justify-content: center; gap: 8px; border-top: 1px solid #E2E8F0;">
                <a v-if="cuentas.current_page > 1" :href="`/cuentas-por-pagar?page=${cuentas.current_page - 1}`"
                    style="padding: 6px 12px; border: 1px solid #E2E8F0; border-radius: 6px; font-size: 12px; cursor: pointer;">
                    ← Anterior
                </a>
                <span style="padding: 6px 12px; font-size: 12px; color: #64748B;">
                    Página {{ cuentas.current_page }} de {{ cuentas.last_page }}
                </span>
                <a v-if="cuentas.current_page < cuentas.last_page" :href="`/cuentas-por-pagar?page=${cuentas.current_page + 1}`"
                    style="padding: 6px 12px; border: 1px solid #E2E8F0; border-radius: 6px; font-size: 12px; cursor: pointer;">
                    Siguiente →
                </a>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { defineProps } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    cuentas: Object,
    totales: Object,
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-PE')
}

const calcularTotal = (estado) => {
    const cuenta = props.cuentas.data.find(c => c.estado === estado)
    return cuenta ? Number(cuenta.monto_pendiente).toFixed(2) : '0.00'
}

const getEstadoStyle = (estado) => {
    const estilos = {
        'pendiente': 'background: #FEE2E2; color: #991B1B; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;',
        'parcial': 'background: #FEF3C7; color: #92400E; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;',
        'pagado': 'background: #DCFCE7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;',
        'vencido': 'background: #FEE2E2; color: #991B1B; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;',
    }
    return estilos[estado] || ''
}
</script>