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
                        {{ empresa.industry_type === 'restaurante' ? '🍽️' : empresa.industry_type === 'farmacia' ? '💊' : empresa.industry_type === 'minimarket' ? '🏪' : empresa.industry_type === 'ferreteria' ? '🔨' : empresa.industry_type === 'notaria' ? '⚖️' : empresa.industry_type === 'gimnasio' ? '💪' : empresa.industry_type === 'hotel' ? '🏨' : '🔧' }}
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
                    <span :style="iconWrapperStyle('PRINCIPAL', '/dashboard')">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="9" rx="1"/>
                            <rect x="14" y="3" width="7" height="5" rx="1"/>
                            <rect x="14" y="12" width="7" height="9" rx="1"/>
                            <rect x="3" y="16" width="7" height="5" rx="1"/>
                        </svg>
                    </span>
                    <span v-if="!collapsed">Dashboard</span>
                </a>

                <!-- Secciones dinámicas -->
                <template v-for="(items, sectionName) in menuSections" :key="sectionName">
                    <p v-if="!collapsed && sectionName && sectionName !== 'GENERAL' && sectionName !== '_default_'" style="font-size:11px; color:#CBD5E1; font-weight:700; letter-spacing:1px; padding:12px 12px 4px; margin:0;">
                        {{ sectionName }}
                    </p>
                    
                    <template v-for="item in items" :key="item.path">
                        <a v-if="item.path !== '/dashboard'" :href="item.path" :style="menuItem(item.path)">
                            <!-- Contenedor del ícono con color de sección -->
                            <span :style="iconWrapperStyle(item.section, item.path)">
                                <!-- MESAS -->
                                <svg v-if="item.icon === 'mesa'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 10h18M5 10v8M19 10v8M9 18v2M15 18v2M7 6h10l1 4H6z"/>
                                </svg>
                                <!-- COCINA -->
                                <svg v-else-if="item.icon === 'cocina'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M6 8a4 4 0 014-4h4a4 4 0 014 4v0H6v0z"/>
                                    <path d="M8 4V2M16 4V2M6 8v12a1 1 0 001 1h10a1 1 0 001-1V8"/>
                                    <line x1="10" y1="12" x2="10" y2="18"/>
                                    <line x1="14" y1="12" x2="14" y2="18"/>
                                </svg>
                                <!-- CARTA / MENÚ -->
                                <svg v-else-if="item.icon === 'carta'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/>
                                    <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>
                                </svg>
                                <!-- COMANDAS -->
                                <svg v-else-if="item.icon === 'comanda'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="6" y="3" width="12" height="18" rx="1"/>
                                    <path d="M9 7h6M9 11h6M9 15h4"/>
                                </svg>
                                <!-- TURNOS / RELOJ -->
                                <svg v-else-if="item.icon === 'turno'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                <!-- VENCIMIENTOS / ALERTA -->
                                <svg v-else-if="item.icon === 'alerta'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    <line x1="12" y1="9" x2="12" y2="13"/>
                                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                                </svg>
                                <!-- POS / CARRITO -->
                                <svg v-else-if="item.icon === 'pos'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="9" cy="21" r="1"/>
                                    <circle cx="20" cy="21" r="1"/>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/>
                                </svg>
                                <!-- VENTAS (tendencia subiendo) -->
                                <svg v-else-if="item.icon === 'ventas'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/>
                                    <polyline points="16 7 22 7 22 13"/>
                                </svg>
                                <!-- REPORTES (gráfico de barras) -->
                                <svg v-else-if="item.icon === 'reporte'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <line x1="12" y1="20" x2="12" y2="10"/>
                                    <line x1="18" y1="20" x2="18" y2="4"/>
                                    <line x1="6" y1="20" x2="6" y2="14"/>
                                    <line x1="3" y1="20" x2="21" y2="20"/>
                                </svg>
                                <!-- CAJA / WALLET -->
                                <svg v-else-if="item.icon === 'caja'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M20 12V8H6a2 2 0 010-4h12v4"/>
                                    <path d="M20 12v6a2 2 0 01-2 2H6a2 2 0 01-2-2V6"/>
                                    <circle cx="17" cy="14" r="1.5"/>
                                </svg>
                                <!-- PANEL CAJERO -->
                                <svg v-else-if="item.icon === 'cajero'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="2" y="6" width="20" height="12" rx="2"/>
                                    <circle cx="12" cy="12" r="2"/>
                                    <line x1="6" y1="12" x2="6" y2="12"/>
                                    <line x1="18" y1="12" x2="18" y2="12"/>
                                </svg>
                                <!-- COMPRAS / BOLSA -->
                                <svg v-else-if="item.icon === 'compras'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                                    <line x1="3" y1="6" x2="21" y2="6"/>
                                    <path d="M16 10a4 4 0 01-8 0"/>
                                </svg>
                                <!-- COMPROBANTES / FACTURA -->
                                <svg v-else-if="item.icon === 'comprobante'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="8" y1="13" x2="16" y2="13"/>
                                    <line x1="8" y1="17" x2="13" y2="17"/>
                                </svg>
                                <!-- COTIZACIONES -->
                                <svg v-else-if="item.icon === 'cotizacion'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <path d="M12 18v-6M9 15l3 3 3-3"/>
                                </svg>
                                <!-- ÓRDENES DE TRABAJO -->
                                <svg v-else-if="item.icon === 'orden'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M9 2h6a1 1 0 011 1v2H8V3a1 1 0 011-1z"/>
                                    <path d="M8 4H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2"/>
                                    <polyline points="9 14 11 16 15 12"/>
                                </svg>
                                <!-- GARANTÍAS / ESCUDO -->
                                <svg v-else-if="item.icon === 'garantia'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                    <polyline points="9 12 11 14 15 10"/>
                                </svg>
                                <!-- RECETAS (chef cocina) -->
                                <svg v-else-if="item.icon === 'receta'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M6 13.87A4 4 0 017.41 6a5.11 5.11 0 011.05-1.54 5 5 0 017.08 0A5.11 5.11 0 0116.59 6 4 4 0 0118 13.87V21H6z"/>
                                    <line x1="6" y1="17" x2="18" y2="17"/>
                                </svg>
                                <!-- INVENTARIO / CAJA 3D -->
                                <svg v-else-if="item.icon === 'inventario'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M20 7l-8-4-8 4m16 0v10l-8 4m0-14L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <!-- STOCK INICIAL / PAQUETE -->
                                <svg v-else-if="item.icon === 'paquete'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                                </svg>
                                <!-- PRODUCTOS FARMACIA / PASTILLA -->
                                <svg v-else-if="item.icon === 'pastilla'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M10.5 20.5l10-10a4.95 4.95 0 10-7-7l-10 10a4.95 4.95 0 107 7z"/>
                                    <line x1="8.5" y1="8.5" x2="15.5" y2="15.5"/>
                                </svg>
                                <!-- PRODUCTOS FERRETERÍA / HERRAMIENTA -->
                                <svg v-else-if="item.icon === 'herramienta'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
                                </svg>
                                <!-- PRODUCTOS MINIMARKET / CESTA -->
                                <svg v-else-if="item.icon === 'cesta'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <line x1="3" y1="6" x2="21" y2="6"/>
                                    <path d="M16 10a4 4 0 01-8 0"/>
                                    <path d="M5 6l2 14a2 2 0 002 2h6a2 2 0 002-2l2-14"/>
                                </svg>
                                <!-- CATEGORÍAS / TAGS -->
                                <svg v-else-if="item.icon === 'categoria'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                                </svg>
                                <!-- CLIENTES -->
                                <svg v-else-if="item.icon === 'cliente'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                                </svg>
                                <!-- PROVEEDORES / CAMIÓN -->
                                <svg v-else-if="item.icon === 'proveedor'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="1" y="6" width="14" height="12" rx="1"/>
                                    <path d="M15 9h4l3 3v6h-7V9z"/>
                                    <circle cx="5.5" cy="18.5" r="1.5"/>
                                    <circle cx="18.5" cy="18.5" r="1.5"/>
                                </svg>
                                <!-- USUARIOS DEL SISTEMA -->
                                <svg v-else-if="item.icon === 'usuario'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                <!-- MOZOS / CAMARERO -->
                                <svg v-else-if="item.icon === 'mozo'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="5" r="3"/>
                                    <path d="M12 8v3M8 14h8l-2 7h-4z"/>
                                    <path d="M5 11l3 1M19 11l-3 1"/>
                                </svg>
                                <!-- NOTARÍA / EXPEDIENTES -->
                                <svg v-else-if="item.icon === 'notaria'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M3 3h18v4H3zM5 7v14h14V7"/>
                                    <line x1="9" y1="12" x2="15" y2="12"/>
                                    <line x1="9" y1="16" x2="13" y2="16"/>
                                </svg>
                                <!-- SEGUIMIENTO / KANBAN -->
                                <svg v-else-if="item.icon === 'seguimiento'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="3" width="6" height="14" rx="1"/>
                                    <rect x="11" y="3" width="6" height="10" rx="1"/>
                                    <rect x="19" y="3" width="2" height="6" rx="1"/>
                                </svg>
                                <!-- AUDITORÍA / LUPA -->
                                <svg v-else-if="item.icon === 'auditoria'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="8"/>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                    <line x1="11" y1="8" x2="11" y2="14"/>
                                    <line x1="8" y1="11" x2="14" y2="11"/>
                                </svg>
                                <!-- CONFIGURACIÓN / ENGRANAJE -->
                                <svg v-else-if="item.icon === 'config'" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="3"/>
                                    <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
                                </svg>
                            </span>
                            
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

            <!-- Slot del contenido de la página -->
            <div style="flex:1; padding:24px 28px;">
                <slot />
            </div>

        </main>
    </div>
</template>

<script setup>
import { ref, computed, defineProps } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

defineProps({
    title:    { type: String, default: 'Dashboard' },
    subtitle: { type: String, default: 'Bienvenido al sistema' },
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
const modalidadCobro = computed(() => page.props.modalidad_cobro ?? 'directo')

const modulesEnabled = computed(() => {
    const m = empresa.value.modules_enabled
    if (!m) return []
    if (Array.isArray(m)) return m
    try { return JSON.parse(m) } catch { return [] }
})

// 🎨 PALETA DE COLORES POR SECCIÓN
const sectionColors = {
    'PRINCIPAL':    { bg: '#E6F1FB', icon: '#185FA5', text: '#0C447C' },
    'RESTAURANTE':  { bg: '#FAECE7', icon: '#993C1D', text: '#4A1B0C' },
    'FARMACIA':     { bg: '#E1F5EE', icon: '#0F6E56', text: '#04342C' },
    'FERRETERIA':   { bg: '#FAEEDA', icon: '#854F0B', text: '#412402' },
    'MINIMARKET':   { bg: '#EEEDFE', icon: '#534AB7', text: '#26215C' },
    'NOTARIA':      { bg: '#E6F1FB', icon: '#185FA5', text: '#0C447C' },
    'SISTEMA':      { bg: '#F1EFE8', icon: '#5F5E5A', text: '#2C2C2A' },
    'AJUSTES':      { bg: '#F1EFE8', icon: '#5F5E5A', text: '#2C2C2A' },
}

// Devuelve los colores según la sección, con fallback gris
const getSectionColor = (section) => {
    return sectionColors[section] || sectionColors['SISTEMA']
}

// Estilo del wrapper del ícono
const iconWrapperStyle = (section, path) => {
    const active = window.location.pathname === path
    const colors = getSectionColor(section)
    return {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        width: '32px',
        height: '32px',
        borderRadius: '8px',
        background: active ? colors.icon : colors.bg,
        color: active ? '#FFFFFF' : colors.icon,
        flexShrink: 0,
        transition: 'all 0.2s ease',
    }
}

const allMenuItems = [
    { path: '/dashboard', icon: 'dashboard', label: 'Dashboard', module: null, section: 'PRINCIPAL' },
    
    // RESTAURANTE
    { path: '/mesas',               icon: 'mesa',       label: 'Mesas',              module: 'pos_restaurante', section: 'RESTAURANTE' },
    { path: '/turnos',              icon: 'turno',      label: 'Turnos',             module: 'turnos',          section: 'RESTAURANTE' },
    { path: '/menu',                icon: 'carta',      label: 'Carta / Menú',       module: 'carta',           section: 'RESTAURANTE' },
    { path: '/cocina',              icon: 'cocina',     label: 'Cocina',             module: 'cocina',          section: 'RESTAURANTE' },
    { path: '/comandas',            icon: 'comanda',    label: 'Comandas',           module: 'comandas',        section: 'RESTAURANTE' },
    { path: '/reportes-restaurante',icon: 'ventas',     label: 'Reporte ventas',     module: 'pos_restaurante', section: 'RESTAURANTE' },
    { path: '/reportes/turnos',     icon: 'reporte',    label: 'Reporte mozos',      module: 'pos_restaurante', section: 'RESTAURANTE' },
    { path: '/compras',             icon: 'compras',    label: 'Compras',            module: 'pos_restaurante', section: 'RESTAURANTE' },
    { path: '/proveedores',         icon: 'proveedor',  label: 'Proveedores',        module: 'pos_restaurante', section: 'RESTAURANTE' },
    { path: '/insumos',             icon: 'inventario', label: 'Inventario',         module: 'pos_restaurante', section: 'RESTAURANTE' },
    { path: '/recetas',             icon: 'receta',     label: 'Recetas',            module: 'pos_restaurante', section: 'RESTAURANTE' },
    { path: '/restaurante/auditoria', icon: 'auditoria', label: '🔍 Auditoría', module: 'pos_restaurante', section: 'RESTAURANTE' },
    
    // SISTEMA
    { path: '/admin/mozos',         icon: 'mozo',         label: 'Mozos',         module: 'mozos',       section: 'SISTEMA' },
    { path: '/comprobantes',        icon: 'comprobante',  label: 'Comprobantes',  module: 'facturacion', section: 'SISTEMA' },
    { path: '/caja',                icon: 'caja',         label: 'Caja',          module: 'caja',        section: 'SISTEMA' },

    // NOTARIA
    { path: '/notaria/actos',       icon: 'notaria',      label: 'Expedientes',      module: 'actos',    section: 'NOTARIA' },
    { path: '/notaria/indice',      icon: 'book',         label: 'Índice Notarial',  module: 'indice',   section: 'NOTARIA' },
    { path: '/notaria/seguimiento', icon: 'seguimiento',  label: 'Seguimiento',      module: 'actos',    section: 'NOTARIA' },
    { path: '/notaria/caja',        icon: 'caja',         label: 'Caja notarial',    module: 'caja',     section: 'NOTARIA' },
    { path: '/notaria/clientes',    icon: 'cliente',      label: 'Clientes',         module: 'clientes', section: 'NOTARIA' },
    { path: '/notaria/reportes',    icon: 'reporte',      label: 'Reportes',         module: 'actos',    section: 'NOTARIA' },
    { path: '/notaria/auditoria', icon: 'auditoria', label: '🔍 Auditoría', module: 'actos', section: 'NOTARIA' },
    { path: '/gimnasio/dashboard',   icon: 'dashboard',  label: 'Dashboard',     module: 'gimnasio', section: 'GIMNASIO' },
    { path: '/gimnasio/miembros',    icon: 'users',      label: 'Miembros',      module: 'gimnasio', section: 'GIMNASIO' },
    { path: '/gimnasio/planes',      icon: 'card',       label: 'Planes',        module: 'gimnasio', section: 'GIMNASIO' },
    { path: '/gimnasio/instructores',icon: 'barbell',    label: 'Instructores',  module: 'gimnasio', section: 'GIMNASIO' },
    { path: '/gimnasio/clases',      icon: 'calendar',   label: 'Clases',        module: 'gimnasio', section: 'GIMNASIO' },
    { path: '/gimnasio/accesos',     icon: 'door-enter', label: 'Accesos',       module: 'gimnasio', section: 'GIMNASIO' },
    { path: '/gimnasio/auditoria', icon: 'auditoria', label: '🔍 Auditoría', module: 'gimnasio', section: 'GIMNASIO' },

    // HOTEL
    { path: '/hotel/dashboard',    icon: 'dashboard',  label: 'Dashboard',     module: 'hotel', section: 'HOTEL' },
    { path: '/hotel/recepcion',    icon: 'door-enter', label: 'Recepción',     module: 'hotel', section: 'HOTEL' },
    { path: '/hotel/habitaciones', icon: 'cama',       label: 'Habitaciones',  module: 'hotel', section: 'HOTEL' },
    { path: '/hotel/tipos',        icon: 'categoria',  label: 'Tipos',         module: 'hotel', section: 'HOTEL' },
    { path: '/hotel/housekeeping', icon: 'limpieza',   label: 'Housekeeping',  module: 'hotel', section: 'HOTEL' },
    { path: '/hotel/reportes',     icon: 'reporte',    label: 'Reportes',      module: 'hotel', section: 'HOTEL' },

    // MINIMARKET
    { path: '/minimarket/pos',        icon: 'pos',        label: 'POS Venta',     module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/ventas',     icon: 'ventas',     label: 'Ventas',        module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/productos',  icon: 'cesta',      label: 'Productos',     module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/categorias', icon: 'categoria',  label: 'Categorías',    module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/caja',       icon: 'caja',       label: 'Caja',          module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/reportes',   icon: 'reporte',    label: 'Reportes',      module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/cajero',     icon: 'cajero',     label: 'Panel Cajero',  module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/proveedores',icon: 'proveedor',  label: 'Proveedores',   module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/clientes',   icon: 'cliente',    label: 'Clientes',      module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/compras',               icon: 'compras',    label: 'Compras',       module: 'pos_minimarket', section: 'MINIMARKET' },
    { path: '/minimarket/auditoria', icon: 'auditoria', label: '🔍 Auditoría', module: 'pos_minimarket', section: 'MINIMARKET' },

    // FERRETERIA
    { path: '/ferreteria/pos',          icon: 'pos',           label: 'Punto de Venta',    module: 'pos_ferreteria', section: 'FERRETERIA' },
    { path: '/ferreteria/ventas',       icon: 'ventas',        label: 'Ventas',            module: 'pos_ferreteria', section: 'FERRETERIA' },
    { path: '/ferreteria/caja',         icon: 'caja',          label: 'Caja',              module: 'pos_ferreteria', section: 'FERRETERIA' },
    { path: '/ferreteria/cajero',       icon: 'cajero',        label: 'Panel Cajero',      module: 'pos_ferreteria', section: 'FERRETERIA' },
    { path: '/ferreteria/productos',    icon: 'herramienta',   label: 'Productos',         module: 'pos_ferreteria', section: 'FERRETERIA' },
    { path: '/ferreteria/categorias',   icon: 'categoria',     label: 'Categorías',        module: 'pos_ferreteria', section: 'FERRETERIA' },
    { path: '/ferreteria/clientes',     icon: 'cliente',       label: 'Clientes',          module: 'pos_ferreteria', section: 'FERRETERIA' },
    { path: '/ferreteria/cotizaciones', icon: 'cotizacion',    label: 'Cotizaciones',      module: 'pos_ferreteria', section: 'FERRETERIA' },
    { path: '/ferreteria/ordenes',      icon: 'orden',         label: 'Órdenes de Trabajo',module: 'pos_ferreteria', section: 'FERRETERIA' },
    { path: '/ferreteria/garantias',    icon: 'garantia',      label: 'Garantías',         module: 'pos_ferreteria', section: 'FERRETERIA' },
    { path: '/ferreteria/reportes',     icon: 'reporte',       label: 'Reportes',          module: 'pos_ferreteria', section: 'FERRETERIA' },
    { path: '/ferreteria/proveedores',  icon: 'proveedor',     label: 'Proveedores',       module: 'pos_ferreteria', section: 'FERRETERIA' },
    { path: '/ferreteria/auditoria', icon: 'auditoria', label: '🔍 Auditoría', module: 'pos_ferreteria', section: 'FERRETERIA' },

    // FARMACIA
    { path: '/farmacia/pos',                icon: 'pos',         label: 'Punto de Venta',  module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/farmacia/ventas',             icon: 'ventas',      label: 'Ventas',          module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/farmacia/caja',               icon: 'caja',        label: 'Caja',            module: 'pos_farmacia', section: 'FARMACIA', hide_if: 'centralizado' },
    { path: '/farmacia/cajero',             icon: 'cajero',      label: 'Panel Cajero',    module: 'pos_farmacia', section: 'FARMACIA', hide_if: 'directo' },
    { path: '/farmacia/productos',          icon: 'pastilla',    label: 'Productos',       module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/farmacia/inventario-inicial', icon: 'paquete',     label: '📦 Stock Inicial',module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/farmacia/vencimientos',       icon: 'alerta',      label: '⚠️ Vencimientos',  module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/compras',                     icon: 'compras',     label: 'Compras',         module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/proveedores',                 icon: 'proveedor',   label: 'Proveedores',     module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/clientes',                    icon: 'cliente',     label: 'Clientes',        module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/farmacia/categorias',         icon: 'categoria',   label: 'Categorías',      module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/farmacia/auditoria',          icon: 'auditoria',   label: '🔍 Auditoría',    module: 'pos_farmacia', section: 'FARMACIA' },
    { path: '/farmacia/reportes',           icon: 'reporte',     label: 'Reportes',        module: 'pos_farmacia', section: 'FARMACIA' },

    // AJUSTES
    { path: '/usuarios',      icon: 'usuario',  label: 'Usuarios',       module: 'admin', section: 'AJUSTES' },
    { path: '/configuracion', icon: 'config',   label: 'Configuración',  module: 'admin', section: 'AJUSTES' },
]

const menuItems = computed(() => {
    const industry = empresa.value.industry_type
    const rol = page.props.auth?.user?.rol

    // Módulos permitidos para CAJERO según industria
    const modulosCajeroPorIndustria = {
        restaurante: ['/dashboard', '/mesas', '/caja', '/reportes-restaurante'],
        farmacia:    ['/dashboard', '/farmacia/pos', '/farmacia/ventas', '/farmacia/caja', '/farmacia/cajero', '/farmacia/inventario-inicial', '/clientes'],
        minimarket:  ['/dashboard', '/minimarket/pos', '/minimarket/ventas', '/minimarket/caja', '/minimarket/cajero', '/clientes'],
        ferreteria:  ['/dashboard', '/ferreteria/pos', '/ferreteria/ventas', '/ferreteria/caja', '/ferreteria/cajero', '/clientes'],
    }
    const modulosCajero = modulosCajeroPorIndustria[industry] || modulosCajeroPorIndustria.restaurante
    const modulosVentanilla = ['/dashboard', '/notaria/actos', '/clientes']
    const modulosCajeroNotaria = ['/dashboard', '/notaria/caja', '/caja']
    const modulosMozo     = ['/dashboard', '/mesas']
    const modulosCocinero = ['/dashboard', '/cocina']

    return allMenuItems.filter(item => {
        if (rol === 'cajero')   return modulosCajero.includes(item.path)
        if (rol === 'vendedor' && industry === 'notaria') return modulosVentanilla.includes(item.path)
        if (rol === 'mozo')     return modulosMozo.includes(item.path)
        if (rol === 'cocinero') return modulosCocinero.includes(item.path)
        if (rol === 'cocina')    return modulosCocinero.includes(item.path)

        // Ocultar admin para no-admins
        if (item.module === 'admin' && rol !== 'admin' && rol !== 'superadmin') return false

        // CAJERO en FARMACIA: solo ver módulos permitidos
        if (rol === 'cajero' && industry === 'farmacia') {
            const pathsPermitidosFarmaciaCajero = [
                '/dashboard',
                '/farmacia/pos',
                '/farmacia/ventas',
                '/farmacia/caja',
                '/farmacia/cajero',
                '/farmacia/inventario-inicial',
                '/clientes',
            ]
            if (!pathsPermitidosFarmaciaCajero.includes(item.path)) return false
        }

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

        // En farmacia ocultar SISTEMA genérico (comprobantes, caja general)
        if (industry === 'farmacia') {
            if (item.section === 'SISTEMA') return false
            // Modalidad de cobro:
            //   'directo'  = vendedor cobra        -> mostrar Caja, ocultar Panel Cajero
            //   'cajero'   = cajero centralizado   -> mostrar Panel Cajero, ocultar Caja
            if (modalidadCobro.value === 'directo' && item.path === '/farmacia/cajero') return false
            if (modalidadCobro.value === 'cajero' && item.path === '/farmacia/caja') return false
        }

        // En ferreteria ocultar SISTEMA genérico
        if (industry === 'ferreteria') {
            if (item.section === 'SISTEMA') return false
            // Modalidad de cobro:
            //   'directo'  = vendedor cobra        -> mostrar Caja, ocultar Panel Cajero
            //   'cajero'   = cajero centralizado   -> mostrar Panel Cajero, ocultar Caja
            if (modalidadCobro.value === 'directo' && item.path === '/ferreteria/cajero') return false
            if (modalidadCobro.value === 'cajero' && item.path === '/ferreteria/caja') return false
        }

        // Ocultar módulos de restaurante si no es restaurante
        if (industry !== 'restaurante') {
            if (item.section === 'RESTAURANTE') return false
        }

        // Ocultar módulos de notaría si no es notaría
        if (industry !== 'notaria') {
            if (item.section === 'NOTARIA') return false
        }

        // Ocultar módulos de gimnasio si no es gimnasio
        if (industry !== 'gimnasio') {
            if (item.section === 'GIMNASIO') return false
        }

        // Ocultar módulos de hotel si no es hotel
        if (industry !== 'hotel') {
            if (item.section === 'HOTEL') return false
        }

        // En notaría ocultar caja general y reportes de otros módulos
        if (industry === 'notaria') {
            const ocultarEnNotaria = ['/caja', '/reportes-restaurante', '/reportes/turnos', '/mesas', '/compras', '/proveedores', '/insumos', '/recetas']
            if (ocultarEnNotaria.includes(item.path)) return false
        }

        // Ocultar Caja/Panel Cajero según modalidad de cobro (solo farmacia)
        if (item.hide_if && industry === 'farmacia') {
            if (item.hide_if === modalidadCobro.value) return false
        }

        if (!item.module) return true
        if (item.module === 'gimnasio') return industry === 'gimnasio'
        if (item.module === 'hotel') return industry === 'hotel'
        if (item.module === 'admin') return rol === 'admin' || rol === 'superadmin'
        return modulesEnabled.value.includes(item.module)
    })
})

const menuSections = computed(() => {
    const orden = ['_default_', 'NOTARIA', 'GIMNASIO', 'HOTEL', 'RESTAURANTE', 'SISTEMA', 'MINIMARKET', 'GENERAL', 'FERRETERIA', 'FARMACIA', 'AJUSTES']
    const sections = {}
    
    orden.forEach(s => {
        const key = s
        sections[key] = []
    })

    menuItems.value.forEach(item => {
        const section = item.section ?? '_default_'
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
        padding: '8px 10px',
        borderRadius: '10px',
        fontSize: '14px',
        fontWeight: active ? '700' : '500',
        color: active ? '#1E293B' : '#64748B',
        background: active ? '#F8FAFC' : 'transparent',
        textDecoration: 'none',
        cursor: 'pointer',
        borderLeft: active ? '3px solid #14B8A6' : '3px solid transparent',
    }
}

const logout = () => router.post('/logout')
</script>
