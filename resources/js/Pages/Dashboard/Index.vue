<template>
    <AppLayout title="Dashboard" subtitle="Resumen del negocio">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <!-- KPI 1: Ventas Hoy -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 1.5rem; color: white;">
                <p style="font-size: 12px; opacity: 0.9; margin: 0;">Ventas Hoy</p>
                <h2 style="font-size: 28px; font-weight: 700; margin: 0.5rem 0;">S/ {{ Number(kpis.ventas_hoy).toFixed(2) }}</h2>
                <p style="font-size: 11px; opacity: 0.8; margin: 0;">Últimas 24 horas</p>
            </div>

            <!-- KPI 2: Compras Hoy -->
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; padding: 1.5rem; color: white;">
                <p style="font-size: 12px; opacity: 0.9; margin: 0;">Compras Hoy</p>
                <h2 style="font-size: 28px; font-weight: 700; margin: 0.5rem 0;">S/ {{ Number(kpis.compras_hoy).toFixed(2) }}</h2>
                <p style="font-size: 11px; opacity: 0.8; margin: 0;">Últimas 24 horas</p>
            </div>

            <!-- KPI 3: Cuentas Vencidas -->
            <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 12px; padding: 1.5rem; color: white;">
                <p style="font-size: 12px; opacity: 0.9; margin: 0;">⚠️ Cuentas Vencidas</p>
                <h2 style="font-size: 28px; font-weight: 700; margin: 0.5rem 0;">{{ kpis.cuentas_vencidas }}</h2>
                <p style="font-size: 11px; opacity: 0.8; margin: 0;">Requieren atención</p>
            </div>

            <!-- KPI 4: Stock Bajo -->
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 12px; padding: 1.5rem; color: white;">
                <p style="font-size: 12px; opacity: 0.9; margin: 0;">📦 Stock Bajo</p>
                <h2 style="font-size: 28px; font-weight: 700; margin: 0.5rem 0;">{{ kpis.stock_bajo }}</h2>
                <p style="font-size: 11px; opacity: 0.8; margin: 0;">Productos</p>
            </div>
        </div>

        <!-- Gráficos -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <!-- Gráfico Ventas -->
            <div style="background: white; border-radius: 12px; border: 1px solid #E2E8F0; padding: 1.5rem;">
                <h3 style="font-size: 14px; font-weight: 700; color: #1E293B; margin-top: 0;">Ventas - Últimos 30 días</h3>
                <div style="height: 300px; background: #F8FAFC; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94A3B8;">
                    📊 Gráfico de línea (integración próxima)
                </div>
            </div>

            <!-- Gráfico Compras -->
            <div style="background: white; border-radius: 12px; border: 1px solid #E2E8F0; padding: 1.5rem;">
                <h3 style="font-size: 14px; font-weight: 700; color: #1E293B; margin-top: 0;">Compras - Últimos 30 días</h3>
                <div style="height: 300px; background: #F8FAFC; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #94A3B8;">
                    📊 Gráfico de línea (integración próxima)
                </div>
            </div>
        </div>

        <!-- Sección 2: Datos Importantes -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <!-- Cuentas Próximas a Vencer -->
            <div style="background: white; border-radius: 12px; border: 1px solid #E2E8F0; padding: 1.5rem;">
                <h3 style="font-size: 14px; font-weight: 700; color: #1E293B; margin-top: 0; margin-bottom: 1rem;">⏰ Cuentas Próximas a Vencer</h3>
                
                <div v-if="cuentasProximas.length === 0" style="color: #94A3B8; font-size: 13px; text-align: center; padding: 1rem;">
                    ✓ Sin cuentas próximas a vencer
                </div>

                <div v-else style="display: flex; flex-direction: column; gap: 8px;">
                    <div v-for="cuenta in cuentasProximas" :key="cuenta.id"
                        style="padding: 12px; background: #FEF2F2; border-left: 4px solid #DC2626; border-radius: 6px;">
                        <p style="font-size: 13px; font-weight: 600; color: #1E293B; margin: 0;">
                            {{ cuenta.proveedor.razon_social }}
                        </p>
                        <p style="font-size: 12px; color: #64748B; margin: 4px 0 0;">
                            S/ {{ Number(cuenta.monto_pendiente).toFixed(2) }} - Vence: {{ formatDate(cuenta.fecha_vencimiento) }}
                        </p>
                    </div>
                </div>

                <a href="/cuentas-por-pagar"
                    style="display: inline-block; margin-top: 12px; font-size: 12px; color: #2563EB; text-decoration: none; font-weight: 600;">
                    Ver todas →
                </a>
            </div>

            <!-- Top Proveedores -->
            <div style="background: white; border-radius: 12px; border: 1px solid #E2E8F0; padding: 1.5rem;">
                <h3 style="font-size: 14px; font-weight: 700; color: #1E293B; margin-top: 0; margin-bottom: 1rem;">🏆 Top 5 Proveedores</h3>
                
                <div v-if="topProveedores.length === 0" style="color: #94A3B8; font-size: 13px; text-align: center; padding: 1rem;">
                    Sin compras registradas
                </div>

                <div v-else style="display: flex; flex-direction: column; gap: 10px;">
                    <div v-for="(proveedor, idx) in topProveedores" :key="proveedor.id"
                        style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #E2E8F0;">
                        <div>
                            <p style="font-size: 13px; font-weight: 600; color: #1E293B; margin: 0;">
                                {{ idx + 1 }}. {{ proveedor.proveedor.razon_social }}
                            </p>
                            <p style="font-size: 11px; color: #94A3B8; margin: 2px 0 0;">
                                {{ proveedor.cantidad }} compras
                            </p>
                        </div>
                        <p style="font-size: 13px; font-weight: 700; color: #2563EB; margin: 0;">
                            S/ {{ Number(proveedor.total_compras).toFixed(2) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos con Stock Bajo -->
        <div style="background: white; border-radius: 12px; border: 1px solid #E2E8F0; padding: 1.5rem; margin-top: 1.5rem;">
            <h3 style="font-size: 14px; font-weight: 700; color: #1E293B; margin-top: 0; margin-bottom: 1rem;">📦 Productos con Stock Bajo</h3>
            
            <div v-if="productosStockBajo.length === 0" style="color: #94A3B8; font-size: 13px; text-align: center; padding: 1rem;">
                ✓ Todos los productos tienen stock adecuado
            </div>

            <div v-else style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                            <th style="padding: 10px; text-align: left; color: #64748B; font-weight: 600;">Producto</th>
                            <th style="padding: 10px; text-align: right; color: #64748B; font-weight: 600;">Stock Actual</th>
                            <th style="padding: 10px; text-align: right; color: #64748B; font-weight: 600;">Stock Mínimo</th>
                            <th style="padding: 10px; text-align: center; color: #64748B; font-weight: 600;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="producto in productosStockBajo" :key="producto.id"
                            style="border-bottom: 1px solid #E2E8F0;">
                            <td style="padding: 10px;">{{ producto.descripcion }}</td>
                            <td style="padding: 10px; text-align: right; font-weight: 600;">{{ producto.stock_actual }}</td>
                            <td style="padding: 10px; text-align: right;">{{ producto.stock_minimo }}</td>
                            <td style="padding: 10px; text-align: center;">
                                <span style="background: #FEF2F2; color: #991B1B; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                    ⚠️ BAJO
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Actividad Reciente -->
        <div style="background: white; border-radius: 12px; border: 1px solid #E2E8F0; padding: 1.5rem; margin-top: 1.5rem;">
            <h3 style="font-size: 14px; font-weight: 700; color: #1E293B; margin-top: 0; margin-bottom: 1rem;">📋 Actividad Reciente</h3>
            
            <div v-if="actividadReciente.length === 0" style="color: #94A3B8; font-size: 13px; text-align: center; padding: 1rem;">
                Sin actividad registrada
            </div>

            <div v-else style="display: flex; flex-direction: column; gap: 10px;">
                <div v-for="log in actividadReciente" :key="log.id"
                    style="padding: 12px; background: #F8FAFC; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <p style="font-size: 13px; font-weight: 600; color: #1E293B; margin: 0;">
                            {{ log.usuario?.name || 'Sistema' }} - {{ log.modulo }}
                        </p>
                        <p style="font-size: 12px; color: #94A3B8; margin: 2px 0 0;">
                            {{ getAccionLabel(log.accion) }} - {{ log.detalles }}
                        </p>
                    </div>
                    <span style="font-size: 11px; color: #94A3B8;">{{ formatTimeAgo(log.created_at) }}</span>
                </div>
            </div>

            <a href="/auditoria"
                style="display: inline-block; margin-top: 12px; font-size: 12px; color: #2563EB; text-decoration: none; font-weight: 600;">
                Ver auditoría completa →
            </a>
        </div>
    </AppLayout>
</template>

<script setup>
import { defineProps } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    kpis: Object,
    ventasPorDia: Object,
    comprasPorDia: Object,
    cuentasProximas: Array,
    topProveedores: Array,
    productosStockBajo: Array,
    actividadReciente: Array,
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-PE', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    })
}

const formatTimeAgo = (date) => {
    const now = new Date()
    const diff = now - new Date(date)
    const minutes = Math.floor(diff / 60000)
    const hours = Math.floor(diff / 3600000)
    const days = Math.floor(diff / 86400000)

    if (minutes < 1) return 'Hace unos segundos'
    if (minutes < 60) return `Hace ${minutes}m`
    if (hours < 24) return `Hace ${hours}h`
    return `Hace ${days}d`
}

const getAccionLabel = (accion) => {
    const labels = {
        'create': '✨ Creó',
        'update': '✏️ Editó',
        'delete': '🗑️ Eliminó',
        'view': '👁️ Vio'
    }
    return labels[accion] || accion
}
</script>