<template>
    <AppLayout title="🔍 Auditoría del sistema" subtitle="Registro de todas las acciones y eventos">
        <div style="padding:24px; max-width:1400px; margin:0 auto;">

            <!-- Stats -->
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
                <div style="background:linear-gradient(135deg,#0ea5e9,#0369a1); border-radius:12px; padding:16px; color:white;">
                    <p style="margin:0 0 4px; font-size:11px; opacity:0.85;">📊 Eventos hoy</p>
                    <p style="margin:0; font-size:24px; font-weight:800;">{{ stats.hoy }}</p>
                </div>
                <div style="background:linear-gradient(135deg,#dc2626,#7f1d1d); border-radius:12px; padding:16px; color:white;">
                    <p style="margin:0 0 4px; font-size:11px; opacity:0.85;">⚠️ Críticos (7d)</p>
                    <p style="margin:0; font-size:24px; font-weight:800;">{{ stats.criticos_semana }}</p>
                </div>
                <div style="background:linear-gradient(135deg,#16a34a,#14532d); border-radius:12px; padding:16px; color:white;">
                    <p style="margin:0 0 4px; font-size:11px; opacity:0.85;">👥 Usuarios activos hoy</p>
                    <p style="margin:0; font-size:24px; font-weight:800;">{{ stats.usuarios_activos }}</p>
                </div>
                <div style="background:linear-gradient(135deg,#7c3aed,#5b21b6); border-radius:12px; padding:16px; color:white;">
                    <p style="margin:0 0 4px; font-size:11px; opacity:0.85;">📈 Total histórico</p>
                    <p style="margin:0; font-size:24px; font-weight:800;">{{ stats.total }}</p>
                </div>
            </div>

            <!-- Filtros -->
            <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:16px; margin-bottom:16px;">
                <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr; gap:12px; margin-bottom:12px;">
                    <input v-model="filtros.q" placeholder="🔍 Buscar..." style="padding:9px 12px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; outline:none;" />
                    <select v-model="filtros.categoria" style="padding:9px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; outline:none;">
                        <option value="">Todas categorías</option>
                        <option value="venta">Ventas</option>
                        <option value="compra">Compras</option>
                        <option value="producto">Productos</option>
                        <option value="inventario">Inventario</option>
                        <option value="caja">Caja</option>
                        <option value="usuario">Usuarios</option>
                        <option value="auth">Login</option>
                        <option value="sistema">Sistema</option>
                    </select>
                    <select v-model="filtros.severidad" style="padding:9px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; outline:none;">
                        <option value="">Toda severidad</option>
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="critical">Critical</option>
                    </select>
                    <select v-model="filtros.usuario_id" style="padding:9px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; outline:none;">
                        <option value="">Todos usuarios</option>
                        <option v-for="u in usuarios" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                    <input v-model="filtros.desde" type="date" style="padding:9px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; outline:none;" />
                    <input v-model="filtros.hasta" type="date" style="padding:9px; border:1px solid #CBD5E1; border-radius:8px; font-size:13px; outline:none;" />
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div style="display:flex; gap:8px;">
                        <button @click="aplicarFiltros" style="padding:8px 16px; background:#0EA5E9; color:white; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">🔍 Filtrar</button>
                        <button @click="limpiarFiltros" style="padding:8px 16px; background:#F1F5F9; color:#475569; border:none; border-radius:6px; font-size:12px; cursor:pointer;">Limpiar</button>
                    </div>
                    <a :href="exportUrl" style="padding:8px 16px; background:#16A34A; color:white; border:none; border-radius:6px; font-size:12px; font-weight:600; text-decoration:none;">📥 Exportar CSV</a>
                </div>
            </div>

            <!-- Tabla -->
            <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
                <table style="width:100%; border-collapse:collapse; font-size:12px;">
                    <thead style="background:#F8FAFC;">
                        <tr>
                            <th style="text-align:left; padding:10px; font-size:10px; color:#64748B; border-bottom:2px solid #E2E8F0;">FECHA</th>
                            <th style="text-align:left; padding:10px; font-size:10px; color:#64748B; border-bottom:2px solid #E2E8F0;">USUARIO</th>
                            <th style="text-align:left; padding:10px; font-size:10px; color:#64748B; border-bottom:2px solid #E2E8F0;">CATEGORÍA</th>
                            <th style="text-align:left; padding:10px; font-size:10px; color:#64748B; border-bottom:2px solid #E2E8F0;">ACCIÓN</th>
                            <th style="text-align:left; padding:10px; font-size:10px; color:#64748B; border-bottom:2px solid #E2E8F0;">DESCRIPCIÓN</th>
                            <th style="text-align:left; padding:10px; font-size:10px; color:#64748B; border-bottom:2px solid #E2E8F0;">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="e in eventos.data" :key="e.id" style="border-bottom:1px solid #F1F5F9;">
                            <td style="padding:10px; color:#475569; font-family:monospace; white-space:nowrap;">{{ formato(e.created_at) }}</td>
                            <td style="padding:10px; color:#1E293B; font-weight:600;">{{ e.usuario_nombre || '—' }}</td>
                            <td style="padding:10px;">
                                <span :style="badgeCat(e.categoria)">{{ e.categoria }}</span>
                            </td>
                            <td style="padding:10px; color:#1E293B;">
                                <span :style="badgeSev(e.severidad)">{{ e.accion }}</span>
                            </td>
                            <td style="padding:10px; color:#475569;">
                                {{ e.descripcion || e.entidad_descripcion || '—' }}
                            </td>
                            <td style="padding:10px; color:#94A3B8; font-family:monospace; font-size:11px;">{{ e.ip || '—' }}</td>
                        </tr>
                        <tr v-if="!eventos.data?.length">
                            <td colspan="6" style="text-align:center; padding:40px; color:#94A3B8;">
                                Sin eventos registrados. Realiza acciones (ventas, ediciones) para empezar a generar registros.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div v-if="eventos.links?.length > 3" style="display:flex; gap:6px; justify-content:center; margin-top:16px; flex-wrap:wrap;">
                <a v-for="link in eventos.links" :key="link.label"
                    :href="link.url"
                    v-html="link.label"
                    :style="{
                        padding: '6px 12px',
                        background: link.active ? '#0EA5E9' : 'white',
                        color: link.active ? 'white' : '#475569',
                        border: '1px solid #E2E8F0',
                        borderRadius: '6px',
                        fontSize: '12px',
                        textDecoration: 'none',
                        pointerEvents: link.url ? 'auto' : 'none',
                        opacity: link.url ? 1 : 0.5,
                    }"></a>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    eventos:  { type: Object, default: () => ({ data:[], links:[] }) },
    usuarios: { type: Array,  default: () => [] },
    filtros:  { type: Object, default: () => ({}) },
    stats:    { type: Object, default: () => ({ hoy:0, criticos_semana:0, usuarios_activos:0, total:0 }) },
})

const filtros = ref({
    q:          props.filtros.q || '',
    categoria:  props.filtros.categoria || '',
    severidad:  props.filtros.severidad || '',
    usuario_id: props.filtros.usuario_id || '',
    desde:      props.filtros.desde || '',
    hasta:      props.filtros.hasta || '',
})

const exportUrl = computed(() => {
    const qs = new URLSearchParams()
    Object.entries(filtros.value).forEach(([k,v]) => { if (v) qs.set(k, v) })
    return '/farmacia/auditoria-exportar/csv?' + qs.toString()
})

const aplicarFiltros = () => {
    router.get('/farmacia/auditoria', filtros.value, { preserveState: true })
}

const limpiarFiltros = () => {
    filtros.value = { q:'', categoria:'', severidad:'', usuario_id:'', desde:'', hasta:'' }
    router.get('/farmacia/auditoria')
}

const formato = (d) => {
    if (!d) return '—'
    const dt = new Date(d)
    return dt.toLocaleDateString('es-PE') + ' ' + dt.toLocaleTimeString('es-PE', {hour:'2-digit', minute:'2-digit'})
}

const badgeCat = (cat) => {
    const colors = {
        venta:      { bg:'#DBEAFE', color:'#1E40AF' },
        compra:     { bg:'#D1FAE5', color:'#065F46' },
        producto:   { bg:'#FEF3C7', color:'#92400E' },
        inventario: { bg:'#E0E7FF', color:'#3730A3' },
        caja:       { bg:'#FCE7F3', color:'#9F1239' },
        usuario:    { bg:'#FDE68A', color:'#78350F' },
        auth:       { bg:'#F3E8FF', color:'#6B21A8' },
        sistema:    { bg:'#E5E7EB', color:'#374151' },
    }
    const c = colors[cat] || colors.sistema
    return `display:inline-block; padding:2px 8px; background:${c.bg}; color:${c.color}; border-radius:10px; font-size:10px; font-weight:600; text-transform:uppercase;`
}

const badgeSev = (sev) => {
    if (sev === 'critical') return 'color:#DC2626; font-weight:700;'
    if (sev === 'warning')  return 'color:#D97706; font-weight:600;'
    return 'color:#475569;'
}
</script>
