<template>
    <div style="display:flex; min-height:100vh; background:#F7F8FA; font-family:system-ui,sans-serif;">

        <!-- Overlay móvil -->
        <div v-if="mobileOpen" @click="mobileOpen=false"
            style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:99; display:none;"
            :style="{display: mobileOpen ? 'block' : 'none'}">
        </div>

        <!-- Sidebar -->
        <aside :style="{
            width: isMobile ? '230px' : (collapsed ? '64px' : '230px'),
            height:'100vh',
            background:'white',
            borderRight:'1px solid #E2E8F0',
            display:'flex',
            flexDirection:'column',
            position:'fixed',
            top:0,
            left: isMobile ? (mobileOpen ? '0' : '-230px') : '0',
            zIndex:100,
            overflow:'hidden',
            transition:'width 0.25s ease, left 0.25s ease'
        }">

            <!-- Logo -->
            <div style="padding:14px 16px; border-bottom:1px solid #F0F2F5; flex-shrink:0;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:40px; height:40px; background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px;">
                        {{ empresa.industry_type === 'restaurante' ? '🍽️' : empresa.industry_type === 'farmacia' ? '💊' : empresa.industry_type === 'minimarket' ? '🏪' : empresa.industry_type === 'ferreteria' ? '🔨' : '🔧' }}
                    </div>
                    <div v-if="!collapsed">
                        <p style="font-size:15px; font-weight:700; color:#1E293B; margin:0;">NEXPOS</p>
                        <p style="font-size:11px; color:#94A3B8; margin:0;">Sistema Multi-industry</p>
                    </div>
                </div>
            </div>

            <!-- Botón colapsar -->
            <div style="display:flex; justify-content:center; padding:6px 0; border-bottom:1px solid #F0F2F5; flex-shrink:0;">
                <button @click="collapsed = !collapsed" style="width:32px; height:32px; border-radius:8px; border:1px solid #E2E8F0; background:white; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#64748B;">
                    <svg :style="{transform: collapsed ? 'rotate(180deg)' : 'rotate(0deg)', transition:'transform 0.25s ease'}" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                </button>
            </div>

            <nav style="flex:1; min-height:0; padding:8px; display:flex; flex-direction:column; gap:2px; overflow-y:auto;">
    
                <!-- Dashboard siempre visible -->
                <a href="/dashboard" :style="menuItem('/dashboard')">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                    </svg>
                    Dashboard
                </a>

                <!-- Secciones dinámicas -->
                <template v-for="(items, sectionName) in menuSections" :key="sectionName">
                    <p v-if="!collapsed && sectionName && sectionName !== 'GENERAL'" style="font-size:11px; color:#CBD5E1; font-weight:700; letter-spacing:1px; padding:12px 12px 4px; margin:0;">
                        {{ sectionName }}
                    </p>
                    
                    <template v-for="item in items" :key="item.path">
                        <a v-if="item.path !== '/dashboard'" :href="item.path" :style="menuItem(item.path)">
                            <!-- Íconos SVG -->
                            <svg v-if="item.icon === 'table'" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="2" y="7" width="20" height="10" rx="2"/>
                                <path d="M6 7V5M18 7V5M6 17v2M18 17v2"/>
                            </svg>
                            <svg v-else-if="item.icon === 'clock'" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <svg v-else-if="item.icon === 'menu'" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <svg v-else-if="item.icon === 'chef'" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 2a10 10 0 100 20A10 10 0 0012 2zm-2 14.5v-9l6 4.5-6 4.5z"/>
                            </svg>
                            <svg v-else-if="item.icon === 'kanban'" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="7" height="9" rx="1"/>
                                <rect x="14" y="3" width="7" height="5" rx="1"/>
                                <rect x="14" y="12" width="7" height="9" rx="1"/>
                                <rect x="3" y="16" width="7" height="5" rx="1"/>
                            </svg>
                            <svg v-else-if="item.icon === 'users'" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                <circle cx="8.5" cy="7" r="4"/>
                                <path d="M20 8v6M23 11h-6"/>
                            </svg>
                            <svg v-else-if="item.icon === 'chart'" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="12" y1="20" x2="12" y2="10"/>
                                <line x1="18" y1="20" x2="18" y2="4"/>
                                <line x1="6" y1="20" x2="6" y2="16"/>
                            </svg>
                            <svg v-else-if="item.icon === 'receipt'" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <svg v-else-if="item.icon === 'settings'" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="3"/>
                                <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
                            </svg>
                            
                            <span v-if="!collapsed">{{ item.label }}</span>
                        </a>
                    </template>
                </template>

            </nav>

            <!-- Usuario -->
            <div :style="{padding: collapsed ? '12px 8px' : '16px 20px', borderTop:'1px solid #F0F2F5', display:'flex', alignItems:'center', justifyContent: collapsed ? 'center' : 'flex-start', gap:'12px'}">
                <div style="width:38px; height:38px; background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; color:white; flex-shrink:0;">
                    {{ initials }}
                </div>
                <div v-if="!collapsed" style="flex:1; min-width:0;">
                    <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $page.props.auth?.user?.name }}</p>
                    <p style="font-size:11px; color:#94A3B8; margin:0; text-transform:capitalize;">{{ rolLabel }}</p>
                </div>
                <svg @click="logout" style="cursor:pointer; flex-shrink:0;" width="16" height="16" fill="none" stroke="#94A3B8" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            </div>
        </aside>

        <!-- Contenido -->
        <main :style="{marginLeft: isMobile ? '0' : (collapsed ? '64px' : '230px'), flex:1, display:'flex', flexDirection:'column', minHeight:'100vh', transition:'margin-left 0.25s ease'}">

            <!-- Topbar -->
            <header style="background:white; padding:16px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <!-- Botón hamburguesa móvil -->
                    <button v-if="isMobile" @click="mobileOpen=!mobileOpen"
                        style="width:36px; height:36px; border-radius:8px; border:1px solid #E2E8F0; background:white; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="18" height="18" fill="none" stroke="#64748B" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M3 12h18M3 6h18M3 18h18"/>
                        </svg>
                    </button>
                    <div>
                        <p style="font-size:18px; font-weight:700; color:#1E293B; margin:0;">{{ title }}</p>
                        <p style="font-size:13px; color:#94A3B8; margin:0;">{{ subtitle }}</p>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    <span style="font-size:13px; color:#94A3B8;">{{ today }}</span>
                    <a v-if="empresa.industry_type === 'restaurante'" href="/mesas" style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border-radius:10px; font-size:14px; font-weight:600; text-decoration:none; box-shadow:0 4px 12px rgba(20,184,166,0.3);">
                    🪑 Ver mesas
                    </a>
                    <a v-else-if="empresa.industry_type === 'minimarket'" href="/minimarket/pos" style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border-radius:10px; font-size:14px; font-weight:600; text-decoration:none; box-shadow:0 4px 12px rgba(20,184,166,0.3);">
                     🛒 Nueva venta
                    </a>
                </div>
            </header>

            <!-- Notificaciones flash -->
            <div v-if="$page.props.flash?.success || $page.props.flash?.error"
                style="padding:0 28px; margin-top:16px;">
                <div v-if="$page.props.flash?.success"
                    style="background:#F0FDF4; border:1px solid #DCFCE7; border-radius:12px; padding:14px 20px; display:flex; align-items:center; gap:12px;">
                    <svg width="20" height="20" fill="none" stroke="#166534" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    <span style="font-size:15px; color:#166534; font-weight:600;">{{ $page.props.flash.success }}</span>
                </div>
                <div v-if="$page.props.flash?.error"
                    style="background:#FEF2F2; border:1px solid #FECACA; border-radius:12px; padding:14px 20px; display:flex; align-items:center; gap:12px;">
                    <svg width="20" height="20" fill="none" stroke="#991B1B" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    <span style="font-size:15px; color:#991B1B; font-weight:600;">{{ $page.props.flash.error }}</span>
                </div>
            </div>

            <!-- Slot -->
            <div style="padding:28px; flex:1;">
                <slot />
            </div>
        </main>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

defineProps({
    title:    { type: String, default: 'Dashboard' },
    subtitle: { type: String, default: '' },
})

const page = usePage()
const collapsed = ref(false)
const mobileOpen = ref(false)
const isMobile = ref(window.innerWidth < 768)

const handleResize = () => {
    isMobile.value = window.innerWidth < 768
    if (!isMobile.value) mobileOpen.value = false
}

if (typeof window !== 'undefined') {
    window.addEventListener('resize', handleResize)
}

const initials = computed(() => {
    const name = page.props.auth?.user?.name || 'U'
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
})

const rolLabel = computed(() => {
    const roles = {
        admin:      'Administrador',
        cajero:     'Cajero',
        mozo:       'Mozo',
        cocinero:   'Cocinero',
        vendedor:   'Vendedor',
        contador:   'Contador',
        almacenero: 'Almacenero',
    }
    return roles[page.props.auth?.user?.rol] || 'Usuario'
})

const today = computed(() => {
    return new Date().toLocaleDateString('es-PE', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    })
})

const empresa = computed(() => page.props.empresa || { industry_type: 'restaurante', modules_enabled: [] })
const modulesEnabled = computed(() => {
    const m = empresa.value.modules_enabled
    if (!m) return []
    if (Array.isArray(m)) return m
    try { return JSON.parse(m) } catch { return [] }
})

const allMenuItems = [
    { path: '/dashboard', icon: 'dashboard', label: 'Dashboard', module: null, section: null },
    
    // RESTAURANTE
    { path: '/mesas',    icon: 'table',  label: 'Mesas',        module: 'mesas',    section: 'RESTAURANTE' },
    { path: '/turnos',   icon: 'clock',  label: 'Turnos',       module: 'turnos',   section: 'RESTAURANTE' },
    { path: '/menu',     icon: 'menu',   label: 'Carta / Menú', module: 'carta',    section: 'RESTAURANTE' },
    { path: '/cocina',   icon: 'chef',   label: 'Cocina',       module: 'cocina',   section: 'RESTAURANTE' },
    { path: '/comandas', icon: 'kanban', label: 'Comandas',     module: 'comandas', section: 'RESTAURANTE' },
    
    // SISTEMA
    { path: '/admin/mozos',          icon: 'users',   label: 'Mozos',        module: 'mozos',       section: 'SISTEMA' },
    { path: '/reportes-restaurante', icon: 'chart',   label: 'Reportes',     module: 'reportes',    section: 'SISTEMA' },
    { path: '/comprobantes',         icon: 'receipt', label: 'Comprobantes', module: 'facturacion', section: 'SISTEMA' },
    { path: '/caja',                 icon: 'receipt', label: 'Caja',         module: 'caja',        section: 'SISTEMA' },

    // MINIMARKET
    { path: '/minimarket/pos',       icon: 'receipt', label: 'POS Venta',  module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/ventas',    icon: 'chart',   label: 'Ventas',     module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/productos',  icon: 'menu',    label: 'Productos',   module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/categorias', icon: 'menu',    label: 'Categorías',  module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/caja',      icon: 'clock',   label: 'Caja',       module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/reportes',  icon: 'chart',   label: 'Reportes',   module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/cajero',    icon: 'receipt', label: 'Panel Cajero', module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/proveedores', icon: 'users',   label: 'Proveedores', module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/clientes',    icon: 'users',   label: 'Clientes',    module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/compras',               icon: 'receipt', label: 'Compras',     module: 'pos_minimarket', section: 'MINIMARKET' },

    // GENERAL

    { path: '/ferreteria/pos',          icon: 'receipt',  label: 'Punto de Venta',    module: 'pos_ferreteria',    section: 'FERRETERIA' },
    { path: '/ferreteria/productos',    icon: 'menu',     label: 'Productos',          module: 'pos_ferreteria',    section: 'FERRETERIA' },
    { path: '/ferreteria/categorias',   icon: 'kanban',   label: 'Categorías',         module: 'pos_ferreteria',    section: 'FERRETERIA' },
    { path: '/ferreteria/proveedores',  icon: 'users',    label: 'Proveedores',        module: 'pos_ferreteria',    section: 'FERRETERIA' },
    { path: '/ferreteria/clientes',     icon: 'users',    label: 'Clientes',           module: 'pos_ferreteria',    section: 'FERRETERIA' },
    { path: '/ferreteria/cotizaciones', icon: 'receipt',  label: 'Cotizaciones',       module: 'pos_ferreteria',    section: 'FERRETERIA' },
    { path: '/ferreteria/ordenes',      icon: 'clock',    label: 'Órdenes de Trabajo', module: 'pos_ferreteria',    section: 'FERRETERIA' },
    { path: '/ferreteria/garantias',    icon: 'settings', label: 'Garantías',          module: 'pos_ferreteria',    section: 'FERRETERIA' },
    { path: '/ferreteria/caja',         icon: 'receipt',  label: 'Caja',               module: 'pos_ferreteria',    section: 'FERRETERIA' },
    { path: '/ferreteria/ventas',       icon: 'chart',    label: 'Ventas',             module: 'pos_ferreteria',    section: 'FERRETERIA' },
    { path: '/ferreteria/cajero',       icon: 'receipt',  label: 'Panel Cajero',       module: 'pos_ferreteria',    section: 'FERRETERIA' },
    { path: '/ferreteria/reportes',     icon: 'chart',    label: 'Reportes',           module: 'pos_ferreteria',    section: 'FERRETERIA' },
    { path: '/farmacia/pos',          icon: 'receipt',  label: 'Punto de Venta',    module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/farmacia/productos',    icon: 'menu',     label: 'Productos',          module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/farmacia/vencimientos', icon: 'clock',    label: '⚠️ Vencimientos',     module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/farmacia/caja',         icon: 'receipt',  label: 'Caja',               module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/farmacia/ventas',       icon: 'chart',    label: 'Ventas',             module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/farmacia/cajero',       icon: 'receipt',  label: 'Panel Cajero',       module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/farmacia/reportes',     icon: 'chart',    label: 'Reportes',           module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/compras',               icon: 'receipt',  label: 'Compras',            module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/proveedores',           icon: 'users',    label: 'Proveedores',        module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/farmacia/categorias',   icon: 'menu',     label: 'Categorías',         module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/clientes',              icon: 'users',    label: 'Clientes',           module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/usuarios',     icon: 'users',    label: 'Usuarios',      module: 'admin', section: 'AJUSTES' },
    { path: '/configuracion',icon: 'settings', label: 'Configuración', module: 'admin', section: 'AJUSTES' },
]

const menuItems = computed(() => {
    const industry = empresa.value.industry_type
    const rol = page.props.auth?.user?.rol

    const modulosCajero   = ['/dashboard', '/mesas', '/caja', '/reportes-restaurante']
    const modulosMozo     = ['/dashboard', '/mesas']
    const modulosCocinero = ['/dashboard', '/cocina']

    return allMenuItems.filter(item => {
        if (rol === 'cajero')   return modulosCajero.includes(item.path)
        if (rol === 'mozo')     return modulosMozo.includes(item.path)
        if (rol === 'cocinero') return modulosCocinero.includes(item.path)
        if (rol === 'cocina')    return modulosCocinero.includes(item.path)

        // Ocultar admin para no-admins
        if (item.module === 'admin' && rol !== 'admin' && rol !== 'superadmin') return false

        // Ocultar módulos de restaurante si es minimarket
        if (industry === 'minimarket') {
            const soloRestaurante = ['mozos', 'reportes', 'facturacion', 'mesas', 'carta', 'cocina', 'comandas', 'turnos', 'pos_restaurante']
            if (item.module && soloRestaurante.includes(item.module)) return false
            if (item.section === 'SISTEMA' && !['Usuarios', 'Configuración'].includes(item.label)) return false
        }

        // Ocultar módulos de minimarket si no es minimarket
        if (industry !== 'minimarket') {
            if (item.section === 'MINIMARKET') return false
        }

        // Ocultar módulos de ferretería si no es ferretería
        if (industry !== 'ferreteria') {
            if (item.section === 'FERRETERIA') return false
        }

        // Ocultar módulos de farmacia si no es farmacia
        if (industry !== 'farmacia') {
            if (item.section === 'FARMACIA') return false
        }

        // Ocultar módulos de restaurante si no es restaurante
        if (industry !== 'restaurante') {
            if (item.section === 'RESTAURANTE') return false
        }

        if (!item.module) return true
        if (item.module === 'admin') return rol === 'admin' || rol === 'superadmin'
        return modulesEnabled.value.includes(item.module)
    })
})

const menuSections = computed(() => {
    const orden = [null, 'RESTAURANTE', 'SISTEMA', 'MINIMARKET', 'GENERAL', 'FERRETERIA', 'FARMACIA', 'AJUSTES']
    const sections = {}
    
    orden.forEach(s => {
        const key = s || 'null'
        sections[key] = []
    })

    menuItems.value.forEach(item => {
        const section = item.section ?? 'null'
        if (!sections[section]) sections[section] = []
        sections[section].push(item)
    })

    Object.keys(sections).forEach(k => {
        if (!sections[k].length) delete sections[k]
    })

    return sections
})

const menuItem = (path) => {
    const active = window.location.pathname === path
    return {
        display: 'flex',
        alignItems: 'center',
        gap: '10px',
        padding: '11px 14px',
        borderRadius: '10px',
        fontSize: '14px',
        fontWeight: active ? '700' : '500',
        color: active ? '#0F766E' : '#64748B',
        background: active ? '#F0FDFA' : 'transparent',
        textDecoration: 'none',
        cursor: 'pointer',
        borderLeft: active ? '3px solid #14B8A6' : '3px solid transparent',
    }
}

const logout = () => router.post('/logout')
</script>