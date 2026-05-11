<template>
    <AppLayout title="Vencimientos" subtitle="Control de medicamentos próximos a vencer">
        <div style="padding:24px; max-width:1200px; margin:0 auto;">

            <!-- Header stats -->
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
                <div style="background:linear-gradient(135deg,#dc2626,#991b1b); border-radius:16px; padding:20px; color:white;">
                    <p style="margin:0 0 8px; font-size:13px; opacity:0.85;">🚨 Vencidos</p>
                    <p style="margin:0; font-size:32px; font-weight:800;">{{ vencidos.length }}</p>
                    <p style="margin:4px 0 0; font-size:12px; opacity:0.75;">Retirar del inventario</p>
                </div>
                <div style="background:linear-gradient(135deg,#ea580c,#c2410c); border-radius:16px; padding:20px; color:white;">
                    <p style="margin:0 0 8px; font-size:13px; opacity:0.85;">⚠️ Por vencer (30 días)</p>
                    <p style="margin:0; font-size:32px; font-weight:800;">{{ porVencer.length }}</p>
                    <p style="margin:4px 0 0; font-size:12px; opacity:0.75;">Atención urgente</p>
                </div>
                <div style="background:linear-gradient(135deg,#ca8a04,#a16207); border-radius:16px; padding:20px; color:white;">
                    <p style="margin:0 0 8px; font-size:13px; opacity:0.85;">📅 Por vencer (90 días)</p>
                    <p style="margin:0; font-size:32px; font-weight:800;">{{ porVencer90.length }}</p>
                    <p style="margin:4px 0 0; font-size:12px; opacity:0.75;">Monitorear</p>
                </div>
                <div style="background:linear-gradient(135deg,#ea580c,#9a3412); border-radius:16px; padding:20px; color:white;">
                    <p style="margin:0 0 8px; font-size:13px; opacity:0.85;">📦 Stock Bajo</p>
                    <p style="margin:0; font-size:32px; font-weight:800;">{{ stockBajo.length }}</p>
                    <p style="margin:4px 0 0; font-size:12px; opacity:0.75;">Reabastecer</p>
                </div>
            </div>

            <!-- Vencidos -->
            <div v-if="vencidos.length" style="background:white; border-radius:16px; border:1px solid #fca5a5; margin-bottom:16px; overflow:hidden;">
                <div style="background:#fef2f2; padding:16px 24px; border-bottom:1px solid #fca5a5;">
                    <p style="margin:0; font-size:15px; font-weight:700; color:#dc2626;">🚨 Productos Vencidos — Retirar inmediatamente</p>
                </div>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="padding:12px 24px; text-align:left; font-size:12px; color:#64748B;">Producto</th>
                            <th style="padding:12px 24px; text-align:left; font-size:12px; color:#64748B;">Vencimiento</th>
                            <th style="padding:12px 24px; text-align:left; font-size:12px; color:#64748B;">Stock</th>
                            <th style="padding:12px 24px; text-align:left; font-size:12px; color:#64748B;">Días vencido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in vencidos" :key="p.id" style="border-bottom:1px solid #fef2f2;">
                            <td style="padding:12px 24px; font-weight:600; color:#1e293b;">{{ p.descripcion }}</td>
                            <td style="padding:12px 24px; color:#dc2626; font-weight:600;">{{ p.fecha_vencimiento }}</td>
                            <td style="padding:12px 24px; color:#64748b;">{{ p.stock_actual }}</td>
                            <td style="padding:12px 24px;">
                                <span style="background:#fee2e2; color:#dc2626; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                                    {{ diasVencido(p.fecha_vencimiento) }} días
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Por vencer 30 días -->
            <div v-if="porVencer.length" style="background:white; border-radius:16px; border:1px solid #fed7aa; margin-bottom:16px; overflow:hidden;">
                <div style="background:#fff7ed; padding:16px 24px; border-bottom:1px solid #fed7aa;">
                    <p style="margin:0; font-size:15px; font-weight:700; color:#ea580c;">⚠️ Vencen en menos de 30 días</p>
                </div>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="padding:12px 24px; text-align:left; font-size:12px; color:#64748B;">Producto</th>
                            <th style="padding:12px 24px; text-align:left; font-size:12px; color:#64748B;">Vencimiento</th>
                            <th style="padding:12px 24px; text-align:left; font-size:12px; color:#64748B;">Stock</th>
                            <th style="padding:12px 24px; text-align:left; font-size:12px; color:#64748B;">Días restantes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in porVencer" :key="p.id" style="border-bottom:1px solid #fff7ed;">
                            <td style="padding:12px 24px; font-weight:600; color:#1e293b;">{{ p.descripcion }}</td>
                            <td style="padding:12px 24px; color:#ea580c; font-weight:600;">{{ p.fecha_vencimiento }}</td>
                            <td style="padding:12px 24px; color:#64748b;">{{ p.stock_actual }}</td>
                            <td style="padding:12px 24px;">
                                <span style="background:#ffedd5; color:#ea580c; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                                    {{ diasRestantes(p.fecha_vencimiento) }} días
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Por vencer 90 días -->
            <div v-if="porVencer90.length" style="background:white; border-radius:16px; border:1px solid #fde68a; overflow:hidden;">
                <div style="background:#fffbeb; padding:16px 24px; border-bottom:1px solid #fde68a;">
                    <p style="margin:0; font-size:15px; font-weight:700; color:#ca8a04;">📅 Vencen entre 30 y 90 días</p>
                </div>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="padding:12px 24px; text-align:left; font-size:12px; color:#64748B;">Producto</th>
                            <th style="padding:12px 24px; text-align:left; font-size:12px; color:#64748B;">Vencimiento</th>
                            <th style="padding:12px 24px; text-align:left; font-size:12px; color:#64748B;">Stock</th>
                            <th style="padding:12px 24px; text-align:left; font-size:12px; color:#64748B;">Días restantes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in porVencer90" :key="p.id" style="border-bottom:1px solid #fffbeb;">
                            <td style="padding:12px 24px; font-weight:600; color:#1e293b;">{{ p.descripcion }}</td>
                            <td style="padding:12px 24px; color:#ca8a04; font-weight:600;">{{ p.fecha_vencimiento }}</td>
                            <td style="padding:12px 24px; color:#64748b;">{{ p.stock_actual }}</td>
                            <td style="padding:12px 24px;">
                                <span style="background:#fef9c3; color:#ca8a04; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;">
                                    {{ diasRestantes(p.fecha_vencimiento) }} días
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Stock Bajo -->
            <div v-if="stockBajo.length" style="background:white; border-radius:16px; border:1px solid #fed7aa; overflow:hidden;">
                <div style="background:linear-gradient(135deg,#ea580c,#c2410c); padding:16px 20px; display:flex; align-items:center; gap:10px;">
                    <span style="font-size:20px;">📦</span>
                    <h3 style="margin:0; color:white; font-size:15px; font-weight:700;">Stock Bajo ({{ stockBajo.length }} productos)</h3>
                </div>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#fff7ed;">
                            <th style="padding:10px 16px; text-align:left; font-size:12px; color:#9a3412; font-weight:600;">Producto</th>
                            <th style="padding:10px 16px; text-align:center; font-size:12px; color:#9a3412; font-weight:600;">Stock Actual</th>
                            <th style="padding:10px 16px; text-align:center; font-size:12px; color:#9a3412; font-weight:600;">Stock Mínimo</th>
                            <th style="padding:10px 16px; text-align:center; font-size:12px; color:#9a3412; font-weight:600;">Diferencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="p in stockBajo" :key="p.id" style="border-bottom:1px solid #ffedd5;">
                            <td style="padding:10px 16px; font-size:13px; color:#1E293B; font-weight:500;">{{ p.descripcion }}</td>
                            <td style="padding:10px 16px; text-align:center;">
                                <span :style="`font-size:13px; font-weight:700; color:${p.stock_actual === 0 ? '#EF4444' : '#ea580c'};`">
                                    {{ p.stock_actual }}
                                </span>
                            </td>
                            <td style="padding:10px 16px; text-align:center; font-size:13px; color:#64748B;">{{ p.stock_minimo }}</td>
                            <td style="padding:10px 16px; text-align:center;">
                                <span style="font-size:12px; font-weight:600; color:#EF4444;">
                                    -{{ p.stock_minimo - p.stock_actual }} unid.
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="!vencidos.length && !porVencer.length && !porVencer90.length && !stockBajo.length"
                style="text-align:center; padding:60px; background:white; border-radius:16px; border:1px solid #E2E8F0;">
                <p style="font-size:48px; margin:0 0 12px;">✅</p>
                <p style="font-size:16px; color:#64748b;">Todos los productos están en buen estado</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    vencidos:    { type: Array, default: () => [] },
    porVencer:   { type: Array, default: () => [] },
    porVencer90: { type: Array, default: () => [] },
    stockBajo:   { type: Array, default: () => [] },
})

const diasRestantes = (fecha) => {
    const hoy = new Date()
    const vence = new Date(fecha)
    return Math.ceil((vence - hoy) / (1000 * 60 * 60 * 24))
}

const diasVencido = (fecha) => {
    const hoy = new Date()
    const vence = new Date(fecha)
    return Math.abs(Math.ceil((vence - hoy) / (1000 * 60 * 60 * 24)))
}
</script>
