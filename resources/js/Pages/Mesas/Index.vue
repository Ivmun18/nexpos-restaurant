<template>
    <AppLayout title="Mapa de mesas" subtitle="Estado en tiempo real">

        <!-- KPIs -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(140px, 1fr)); gap:14px; margin-bottom:1.5rem;">
            <div style="background:white; border-radius:12px; padding:18px; border:1px solid #E2E8F0; text-align:center;">
                <p style="font-size:13px; color:#94A3B8; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.5px;">Total mesas</p>
                <p style="font-size:32px; font-weight:700; color:#1E293B; margin:0;">{{ resumen.total }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:18px; border:1px solid #DCFCE7; text-align:center;">
                <p style="font-size:13px; color:#94A3B8; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.5px;">Libres</p>
                <p style="font-size:32px; font-weight:700; color:#10B981; margin:0;">{{ resumen.libres }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:18px; border:1px solid #FECACA; text-align:center;">
                <p style="font-size:13px; color:#94A3B8; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.5px;">Ocupadas</p>
                <p style="font-size:32px; font-weight:700; color:#EF4444; margin:0;">{{ resumen.ocupadas }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:18px; border:1px solid #FDE68A; text-align:center;">
                <p style="font-size:13px; color:#94A3B8; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.5px;">Reservadas</p>
                <p style="font-size:32px; font-weight:700; color:#F59E0B; margin:0;">{{ resumen.reservadas }}</p>
            </div>
        </div>

        <!-- Filtro por zona -->
        <div style="display:flex; gap:8px; margin-bottom:1.5rem; flex-wrap:wrap;">
            <button type="button" @click="zonaActiva='todas'" :style="zonaActiva==='todas' ? btnZonaActivo : btnZona">Todas</button>
            <button type="button" @click="zonaActiva='salon'" :style="zonaActiva==='salon' ? btnZonaActivo : btnZona">Salon</button>
            <button type="button" @click="zonaActiva='terraza'" :style="zonaActiva==='terraza' ? btnZonaActivo : btnZona">Terraza</button>
            <button type="button" @click="zonaActiva='barra'" :style="zonaActiva==='barra' ? btnZonaActivo : btnZona">Barra</button>
            <button type="button" @click="zonaActiva='privado'" :style="zonaActiva==='privado' ? btnZonaActivo : btnZona">Privado</button>
            <button type="button" @click="zonaActiva='delivery'" :style="zonaActiva==='delivery' ? btnZonaActivo : btnZona">Delivery</button>
            <span v-if="(sucursales || []).length" style="width:1px; background:#E2E8F0; margin:4px 4px;"></span>
            <button v-if="(sucursales || []).length" type="button" @click="sucursalActiva='todas'" :style="sucursalActiva==='todas' ? btnZonaActivo : btnZona">Todas las sucursales</button>
            <button v-for="s in sucursales" :key="s.id" type="button" @click="sucursalActiva=String(s.id)" :style="sucursalActiva===String(s.id) ? btnZonaActivo : btnZona">{{ s.nombre }}</button>
            <button v-if="esAdmin || esCajero" type="button" @click="abrirModalCobroRapido"
                style="margin-left:auto; padding:12px 20px; background:linear-gradient(135deg,#F59E0B,#D97706); color:white; border:none; border-radius:10px; font-size:15px; font-weight:700; cursor:pointer;">
                ⚡ Cobro rápido
            </button>
            <button type="button" @click="abrirModalNueva"
                :style="{ padding:'12px 20px', background:'#2563EB', color:'white', border:'none', borderRadius:'10px', fontSize:'15px', fontWeight:'600', cursor:'pointer', marginLeft: (esAdmin || esCajero) ? '0' : 'auto' }">
                + Nueva mesa
            </button>
        </div>

        <!-- Mapa de mesas -->
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(90px, 1fr)); gap:8px; margin-bottom:2rem;">
            <div v-for="mesa in mesasFiltradas" :key="mesa.id"
                @click="abrirMesa(mesa)"
                :style="estiloMesa(mesa)"
                style="border-radius:10px; padding:8px; cursor:pointer; transition:transform 0.1s; user-select:none; min-height:70px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:4px;"
                @touchstart="$event.currentTarget.style.transform='scale(0.96)'"
                @touchend="$event.currentTarget.style.transform='scale(1)'"
                @mousedown="$event.currentTarget.style.transform='scale(0.96)'"
                @mouseup="$event.currentTarget.style.transform='scale(1)'">

                <svg width="20" height="20" fill="none" :stroke="mesa.estado === 'libre' ? '#10B981' : mesa.estado === 'ocupada' ? '#EF4444' : mesa.estado === 'reservada' ? '#F59E0B' : '#6B7280'" stroke-width="1.5" viewBox="0 0 24 24">
                    <rect x="2" y="7" width="20" height="10" rx="2"/>
                    <path d="M6 7V5M18 7V5M6 17v2M18 17v2"/>
                </svg>

                <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0;">{{ mesa.nombre }}</p>
                <p v-if="mesa.sucursal" style="font-size:9px; color:#94A3B8; margin:0; text-transform:uppercase; letter-spacing:0.5px; font-weight:600;">{{ mesa.sucursal.nombre }}</p>
                <p style="font-size:11px; color:#64748B; margin:0;">{{ mesa.capacidad }} personas</p>

                <span :style="badgeEstado(mesa.estado)">{{ mesa.estado }}</span>
            </div>
        </div>

        <!-- Leyenda -->
        <div style="display:flex; gap:20px; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:16px; height:16px; border-radius:4px; background:#DCFCE7; border:2px solid #10B981;"></div>
                <span style="font-size:14px; color:#64748B;">Libre</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:16px; height:16px; border-radius:4px; background:#FEE2E2; border:2px solid #EF4444;"></div>
                <span style="font-size:14px; color:#64748B;">Ocupada</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:16px; height:16px; border-radius:4px; background:#FEF3C7; border:2px solid #F59E0B;"></div>
                <span style="font-size:14px; color:#64748B;">Reservada</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:16px; height:16px; border-radius:4px; background:#F1F5F9; border:2px solid #6B7280;"></div>
                <span style="font-size:14px; color:#64748B;">Bloqueada</span>
            </div>
        </div>

        <!-- Modal de mesa -->
        <div v-show="modalMesa" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; display:flex; align-items:center; justify-content:center;" @click.self="modalMesa=false">
            <div v-if="mesaSeleccionada" style="background:white; border-radius:20px; padding:2rem; width:100%; max-width:400px; box-shadow:0 20px 60px rgba(0,0,0,0.3);">

    <div style="text-align:center; margin-bottom:1.5rem;">
        <p style="font-size:24px; font-weight:700; color:#1E293B; margin:0;">{{ mesaSeleccionada.nombre }}</p>
        <p style="font-size:15px; color:#64748B; margin:4px 0 0;">{{ mesaSeleccionada.capacidad }} personas - {{ mesaSeleccionada.zona }}</p>
    </div>

    <!-- Botón tomar pedido -->
      <div style="display:flex; justify-content:center; margin-bottom:1rem;">
    <a :href="`/pos/${mesaSeleccionada.id}`"
        style="padding:18px 32px; background:#14B8A6; color:white; border-radius:12px; font-size:18px; font-weight:700; cursor:pointer; text-align:center; text-decoration:none;">
        🍽️ Tomar pedido
    </a>
    </div>


    <p style="font-size:14px; font-weight:600; color:#64748B; margin:0 0 12px; text-transform:uppercase; letter-spacing:0.5px;">Cambiar estado</p>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:1.5rem;">
        <button type="button" @click="cambiarEstado('libre')"
            style="padding:16px; background:#DCFCE7; color:#166534; border:2px solid #10B981; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer;">
            Libre
        </button>
        <button type="button" @click="cambiarEstado('ocupada')"
            style="padding:16px; background:#FEE2E2; color:#991B1B; border:2px solid #EF4444; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer;">
            Ocupada
        </button>
        <button type="button" @click="cambiarEstado('reservada')"
            style="padding:16px; background:#FEF3C7; color:#92400E; border:2px solid #F59E0B; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer;">
            Reservada
        </button>
        <button type="button" @click="cambiarEstado('bloqueada')"
            style="padding:16px; background:#F1F5F9; color:#475569; border:2px solid #6B7280; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer;">
            Bloqueada
        </button>
    </div>

    <!-- Acciones de mesa: transferir / unir / separar -->
    <div v-if="mesaSeleccionada.estado === 'ocupada'" style="margin-bottom:16px;">
        <p style="font-size:14px; font-weight:600; color:#64748B; margin:0 0 12px; text-transform:uppercase; letter-spacing:0.5px;">Acciones</p>

        <!-- Transferir -->
        <div style="margin-bottom:10px;">
            <select v-model="destinoTransferir" style="width:100%; padding:12px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; margin-bottom:8px;">
                <option :value="null">Transferir a mesa libre...</option>
                <option v-for="m in mesasLibres" :key="m.id" :value="m.id">Mesa {{ m.numero }}</option>
            </select>
            <button type="button" @click="transferirMesa()" :disabled="!destinoTransferir"
                :style="{width:'100%', padding:'12px', background: destinoTransferir ? '#3B82F6' : '#E2E8F0', color: destinoTransferir ? 'white' : '#94A3B8', border:'none', borderRadius:'10px', fontSize:'14px', fontWeight:'700', cursor: destinoTransferir ? 'pointer' : 'not-allowed'}">
                ↗️ Transferir pedidos
            </button>
        </div>

        <!-- Unir -->
        <div style="margin-bottom:10px;">
            <select v-model="secundariaUnir" style="width:100%; padding:12px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; margin-bottom:8px;">
                <option :value="null">Unir con otra mesa...</option>
                <option v-for="m in mesasOcupadasOtras" :key="m.id" :value="m.id">Mesa {{ m.numero }}</option>
            </select>
            <button type="button" @click="unirMesa()" :disabled="!secundariaUnir"
                :style="{width:'100%', padding:'12px', background: secundariaUnir ? '#8B5CF6' : '#E2E8F0', color: secundariaUnir ? 'white' : '#94A3B8', border:'none', borderRadius:'10px', fontSize:'14px', fontWeight:'700', cursor: secundariaUnir ? 'pointer' : 'not-allowed'}">
                🔗 Unir mesas
            </button>
        </div>

        <!-- Separar (si esta unida) -->
        <button v-if="mesaSeleccionada.mesa_principal_id" type="button" @click="separarMesa()"
            style="width:100%; padding:12px; background:#FEF3C7; color:#92400E; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; margin-bottom:10px;">
            ✂️ Separar de su mesa principal
        </button>
    </div>

    <button type="button" @click="modalMesa=false"
        style="width:100%; padding:14px; background:#F1F5F9; color:#64748B; border:none; border-radius:12px; font-size:16px; cursor:pointer;">
        Cancelar
    </button>
</div>
        </div>

        <!-- Modal nueva mesa -->
        <div v-show="modalNueva" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; display:flex; align-items:center; justify-content:center;" @click.self="modalNueva=false">
            <div style="background:white; border-radius:20px; padding:2rem; width:100%; max-width:400px;">
                <p style="font-size:20px; font-weight:700; color:#1E293B; margin:0 0 1.5rem;">Nueva mesa</p>

                <form @submit.prevent="guardarMesa">
                    <div style="margin-bottom:1rem;">
                        <label style="font-size:14px; color:#64748B; display:block; margin-bottom:6px;">Numero *</label>
                        <input v-model="formMesa.numero" type="text"
                            :style="{width:'100%', padding:'14px', border: formMesa.errors.numero ? '2px solid #EF4444' : '2px solid #E2E8F0', borderRadius:'12px', fontSize:'16px', outline:'none', boxSizing:'border-box'}"/>
                        <p v-if="formMesa.errors.numero" style="font-size:13px; color:#EF4444; margin:6px 0 0;">{{ formMesa.errors.numero }}</p>
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label style="font-size:14px; color:#64748B; display:block; margin-bottom:6px;">Nombre</label>
                        <input v-model="formMesa.nombre" type="text" placeholder="Mesa 13"
                            style="width:100%; padding:14px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;"/>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
                        <div>
                            <label style="font-size:14px; color:#64748B; display:block; margin-bottom:6px;">Capacidad</label>
                            <input v-model="formMesa.capacidad" type="number" min="1" max="20"
                                style="width:100%; padding:14px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; box-sizing:border-box;"/>
                        </div>
                        <div>
                            <label style="font-size:14px; color:#64748B; display:block; margin-bottom:6px;">Zona</label>
                            <select v-model="formMesa.zona"
                                style="width:100%; padding:14px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; background:white; box-sizing:border-box;">
                                <option value="salon">Salon</option>
                                <option value="terraza">Terraza</option>
                                <option value="barra">Barra</option>
                                <option value="privado">Privado</option>
                                <option value="delivery">Delivery</option>
                            </select>
                        </div>
                    </div>
                    <div v-if="mostrarSelectorSucursal" style="margin-bottom:1.5rem;">
                        <label style="font-size:14px; color:#64748B; display:block; margin-bottom:6px;">Sucursal</label>
                        <select v-model="formMesa.sucursal_id"
                            style="width:100%; padding:14px; border:2px solid #E2E8F0; border-radius:12px; font-size:16px; outline:none; background:white; box-sizing:border-box;">
                            <option :value="null">Sin asignar</option>
                            <option v-for="s in sucursales" :key="s.id" :value="s.id">{{ s.nombre }}</option>
                        </select>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button type="button" @click="modalNueva=false"
                            style="flex:1; padding:14px; background:#F1F5F9; color:#64748B; border:none; border-radius:12px; font-size:16px; cursor:pointer;">
                            Cancelar
                        </button>
                        <button type="submit"
                            style="flex:1; padding:14px; background:#2563EB; color:white; border:none; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer;">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal cobro rápido -->
        <div v-show="modalCobroRapido" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; display:flex; align-items:center; justify-content:center; padding:16px;" @click.self="cerrarModalCobroRapido">
            <div style="background:white; border-radius:20px; padding:2rem; width:100%; max-width:440px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                <p style="font-size:20px; font-weight:800; color:#1E293B; margin:0 0 4px;">⚡ Cobro rápido</p>
                <p style="font-size:13px; color:#64748B; margin:0 0 1.5rem;">Emite un comprobante sin asociarlo a una mesa</p>

                <!-- Tipo de comprobante -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:1.25rem;">
                    <button type="button" @click="crTipoComprobante='boleta'"
                        :style="{padding:'14px', borderRadius:'12px', fontSize:'16px', fontWeight:'700', cursor:'pointer',
                            background: crTipoComprobante==='boleta' ? '#F0FDFA' : '#F8FAFC',
                            border: crTipoComprobante==='boleta' ? '2px solid #14B8A6' : '2px solid #E2E8F0',
                            color: crTipoComprobante==='boleta' ? '#0F766E' : '#64748B'}">
                        🧾 Boleta
                    </button>
                    <button type="button" @click="crTipoComprobante='factura'"
                        :style="{padding:'14px', borderRadius:'12px', fontSize:'16px', fontWeight:'700', cursor:'pointer',
                            background: crTipoComprobante==='factura' ? '#F0FDFA' : '#F8FAFC',
                            border: crTipoComprobante==='factura' ? '2px solid #14B8A6' : '2px solid #E2E8F0',
                            color: crTipoComprobante==='factura' ? '#0F766E' : '#64748B'}">
                        📄 Factura
                    </button>
                </div>

                <!-- Documento -->
                <div style="margin-bottom:1rem;">
                    <label style="font-size:13px; font-weight:600; color:#64748B; display:block; margin-bottom:6px;">
                        {{ crTipoComprobante === 'factura' ? 'RUC *' : 'DNI (opcional)' }}
                    </label>
                    <div style="position:relative;">
                        <input v-model="crDocumento" type="text" inputmode="numeric"
                            :maxlength="crTipoComprobante === 'factura' ? 11 : 8"
                            :placeholder="crTipoComprobante === 'factura' ? '11 dígitos' : '8 dígitos'"
                            @input="crDocumento = crDocumento.replace(/[^0-9]/g,'')"
                            :style="{width:'100%', padding:'12px 44px 12px 14px', borderRadius:'10px', fontSize:'15px', outline:'none', boxSizing:'border-box',
                                border: crDocEncontrado ? '2px solid #14B8A6' : crDocError ? '2px solid #EF4444' : '2px solid #E2E8F0'}"/>
                        <span style="position:absolute; right:14px; top:50%; transform:translateY(-50%); font-size:17px; pointer-events:none;">
                            <span v-if="crBuscando">⏳</span>
                            <span v-else-if="crDocEncontrado">✅</span>
                            <span v-else-if="crDocError">❌</span>
                        </span>
                    </div>
                    <p v-if="crDocEncontrado" style="font-size:12px; color:#0F766E; margin:6px 0 0;">✅ Encontrado</p>
                    <p v-if="crDocError" style="font-size:12px; color:#EF4444; margin:6px 0 0;">{{ crDocError }}</p>
                </div>

                <!-- Nombre / razón social -->
                <div style="margin-bottom:1rem;">
                    <label style="font-size:13px; font-weight:600; color:#64748B; display:block; margin-bottom:6px;">
                        {{ crTipoComprobante === 'factura' ? 'Razón social *' : 'Nombre *' }}
                    </label>
                    <input v-model="crNombre" type="text"
                        style="width:100%; padding:12px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:15px; outline:none; box-sizing:border-box;"/>
                </div>

                <!-- Descripción -->
                <div style="margin-bottom:1rem;">
                    <label style="font-size:13px; font-weight:600; color:#64748B; display:block; margin-bottom:6px;">Descripción</label>
                    <input v-model="crDescripcion" type="text"
                        style="width:100%; padding:12px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:15px; outline:none; box-sizing:border-box;"/>
                </div>

                <!-- Monto -->
                <div style="margin-bottom:1rem;">
                    <label style="font-size:13px; font-weight:600; color:#64748B; display:block; margin-bottom:6px;">Monto total (S/) *</label>
                    <input v-model="crMonto" type="number" step="0.01" min="0" placeholder="0.00"
                        style="width:100%; padding:12px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:20px; font-weight:700; outline:none; box-sizing:border-box;"/>
                </div>

                <!-- Desglose IGV -->
                <div style="background:#F8FAFC; border-radius:12px; padding:14px; margin-bottom:1.25rem;">
                    <div style="display:flex; justify-content:space-between; font-size:13px; color:#64748B; margin-bottom:4px;">
                        <span>Subtotal</span>
                        <span>S/ {{ crSubtotal.toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:13px; color:#64748B; margin-bottom:8px;">
                        <span>{{ crExonerada ? 'IGV (exonerado)' : 'IGV (18%)' }}</span>
                        <span>S/ {{ crIgv.toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:16px; font-weight:800; color:#1E293B; padding-top:8px; border-top:1px solid #E2E8F0;">
                        <span>Total</span>
                        <span>S/ {{ crTotal.toFixed(2) }}</span>
                    </div>
                </div>

                <p v-if="crError" style="font-size:13px; color:#EF4444; background:#FEF2F2; border-radius:8px; padding:10px 12px; margin:0 0 1rem;">⚠ {{ crError }}</p>

                <div style="display:flex; gap:10px;">
                    <button type="button" @click="cerrarModalCobroRapido"
                        style="flex:1; padding:14px; background:#F1F5F9; color:#64748B; border:none; border-radius:12px; font-size:16px; cursor:pointer;">
                        Cancelar
                    </button>
                    <button type="button" @click="emitirCobroRapido" :disabled="crEnviando"
                        style="flex:1; padding:14px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:12px; font-size:16px; font-weight:700; cursor:pointer;">
                        {{ crEnviando ? '⏳ Emitiendo...' : '🖨️ Emitir e imprimir' }}
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { router, usePage, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const page = usePage()
const esAdmin  = computed(() => page.props.auth?.user?.rol === 'admin')
const esCajero = computed(() => page.props.auth?.user?.rol === 'cajero')

let pollingInterval = null

onMounted(() => {
    pollingInterval = setInterval(() => {
        router.reload({ only: ['mesas'] })
    }, 10000)
})

onUnmounted(() => {
    clearInterval(pollingInterval)
})

const props = defineProps({
    mesas:      Array,
    resumen:    Object,
    sucursales: Array,
})

const mostrarSelectorSucursal = computed(() => esAdmin.value && (props.sucursales || []).length > 1)

const zonaActiva      = ref('todas')
const sucursalActiva  = ref(props.sucursales && props.sucursales.length > 0 ? String(props.sucursales[0].id) : 'todas')
const modalMesa       = ref(false)
const modalNueva      = ref(false)
const modalCobroRapido= ref(false)
const mesaSeleccionada= ref(null)

// Transferencia y union de mesas
const destinoTransferir = ref(null)
const secundariaUnir    = ref(null)

// Mesas libres (para transferir)
const mesasLibres = computed(() =>
    (props.mesas || []).filter(m => m.estado === 'libre' && m.id !== mesaSeleccionada.value?.id)
)
// Otras mesas ocupadas (para unir)
const mesasOcupadasOtras = computed(() =>
    (props.mesas || []).filter(m => m.estado === 'ocupada' && m.id !== mesaSeleccionada.value?.id && !m.mesa_principal_id)
)

function transferirMesa() {
    if (!destinoTransferir.value) return
    router.post('/mesas/' + mesaSeleccionada.value.id + '/transferir',
        { destino_id: destinoTransferir.value },
        { onSuccess: () => { modalMesa.value = false; destinoTransferir.value = null; router.visit('/mesas') } }
    )
}

function unirMesa() {
    if (!secundariaUnir.value) return
    router.post('/mesas/' + mesaSeleccionada.value.id + '/unir',
        { secundaria_id: secundariaUnir.value },
        { onSuccess: () => { modalMesa.value = false; secundariaUnir.value = null; router.visit('/mesas') } }
    )
}

function separarMesa() {
    router.post('/mesas/' + mesaSeleccionada.value.id + '/separar', {},
        { onSuccess: () => { modalMesa.value = false; router.visit('/mesas') } }
    )
}

const formMesa = useForm({ numero: '', nombre: '', capacidad: 4, zona: 'salon', sucursal_id: null })

const btnZona = { padding:'10px 18px', background:'white', color:'#64748B', border:'1px solid #E2E8F0', borderRadius:'10px', fontSize:'15px', cursor:'pointer', fontWeight:'400' }
const btnZonaActivo = { padding:'10px 18px', background:'#2563EB', color:'white', border:'none', borderRadius:'10px', fontSize:'15px', cursor:'pointer', fontWeight:'600' }

const mesasFiltradas = computed(() => {
    return (props.mesas || [])
        .filter(m => zonaActiva.value === 'todas' || m.zona === zonaActiva.value)
        .filter(m => sucursalActiva.value === 'todas' || String(m.sucursal_id) === sucursalActiva.value)
        .sort((a, b) => {
            if (a.zona === 'delivery' && b.zona !== 'delivery') return 1
            if (a.zona !== 'delivery' && b.zona === 'delivery') return -1
            const numA = parseInt(a.nombre.replace(/\D/g, '')) || 0
            const numB = parseInt(b.nombre.replace(/\D/g, '')) || 0
            return numA - numB
        })
})

const estiloMesa = (mesa) => {
    const colores = {
        libre:     { background:'#F0FDF4', border:'2px solid #10B981' },
        ocupada:   { background:'#FEF2F2', border:'2px solid #EF4444' },
        reservada: { background:'#FFFBEB', border:'2px solid #F59E0B' },
        bloqueada: { background:'#F8FAFC', border:'2px solid #6B7280' },
    }
    const base = colores[mesa.estado] || colores.libre
    if (mesa.zona === 'delivery') {
        return { ...base, border: '2px solid #3B82F6', background: mesa.estado === 'libre' ? '#EFF6FF' : base.background }
    }
    return base
}

const badgeEstado = (estado) => {
    const map = {
        libre:     { background:'#DCFCE7', color:'#166534' },
        ocupada:   { background:'#FEE2E2', color:'#991B1B' },
        reservada: { background:'#FEF3C7', color:'#92400E' },
        bloqueada: { background:'#F1F5F9', color:'#475569' },
    }
    return { ...(map[estado] || map.libre), fontSize:'10px', padding:'2px 8px', borderRadius:'20px', fontWeight:'600' }
}

const abrirMesa = (mesa) => {
    mesaSeleccionada.value = mesa
    modalMesa.value = true
}

const abrirModalNueva = () => {
    formMesa.reset()
    formMesa.clearErrors()
    modalNueva.value = true
}

const cambiarEstado = (estado) => {
    router.post('/mesas/' + mesaSeleccionada.value.id + '/estado', { estado }, {
        onSuccess: () => { modalMesa.value = false; router.visit('/mesas') }
    })
}

const guardarMesa = () => {
    formMesa.post('/mesas', {
        onSuccess: () => { modalNueva.value = false; router.visit('/mesas') }
    })
}

// ══ Cobro rápido (sin mesa) ══
const crTipoComprobante = ref('boleta')
const crDocumento       = ref('')
const crNombre          = ref('')
const crDescripcion     = ref('Consumo de alimentos')
const crMonto           = ref('')
const crBuscando        = ref(false)
const crDocEncontrado   = ref(false)
const crDocError        = ref('')
const crEnviando        = ref(false)
const crError           = ref('')

const crExonerada = computed(() => !!(page.props.empresa?.zona_exonerada))

const crTotal = computed(() => Number(crMonto.value) || 0)
const crSubtotal = computed(() => {
    if (crExonerada.value) return crTotal.value
    return Math.round((crTotal.value / 1.18) * 100) / 100
})
const crIgv = computed(() => {
    if (crExonerada.value) return 0
    return Math.round((crTotal.value - crSubtotal.value) * 100) / 100
})

function abrirModalCobroRapido() {
    crTipoComprobante.value = 'boleta'
    crDocumento.value       = ''
    crNombre.value          = ''
    crDescripcion.value     = 'Consumo de alimentos'
    crMonto.value           = ''
    crBuscando.value        = false
    crDocEncontrado.value   = false
    crDocError.value        = ''
    crEnviando.value        = false
    crError.value           = ''
    modalCobroRapido.value  = true
}

function cerrarModalCobroRapido() {
    modalCobroRapido.value = false
}

let crDocTimer = null

watch(crDocumento, (nuevo) => {
    crDocEncontrado.value = false
    crDocError.value = ''
    crBuscando.value = false
    clearTimeout(crDocTimer)

    const doc = (nuevo || '').trim()
    const esDni = doc.length === 8
    const esRuc = doc.length === 11
    if (!esDni && !esRuc) return

    crDocTimer = setTimeout(async () => {
        crBuscando.value = true
        try {
            const res = await fetch(`/api/consulta-documento?documento=${doc}`)
            const data = await res.json()
            if (esDni && data.nombre_completo) {
                crNombre.value = data.nombre_completo
                crDocEncontrado.value = true
            } else if (esRuc && data.razonSocial) {
                crNombre.value = data.razonSocial
                crDocEncontrado.value = true
            } else {
                crDocError.value = esDni ? 'DNI no encontrado' : 'RUC no encontrado'
            }
        } catch (e) {
            crDocError.value = 'Error al consultar'
        } finally {
            crBuscando.value = false
        }
    }, 500)
})

async function emitirCobroRapido() {
    crError.value = ''

    if (!crTotal.value || crTotal.value <= 0) {
        crError.value = 'El monto debe ser mayor a 0'
        return
    }
    if (!crNombre.value.trim()) {
        crError.value = crTipoComprobante.value === 'factura' ? 'La razón social es obligatoria' : 'El nombre es obligatorio'
        return
    }
    if (crTipoComprobante.value === 'factura' && crDocumento.value.trim().length !== 11) {
        crError.value = 'El RUC debe tener 11 dígitos'
        return
    }

    crEnviando.value = true
    try {
        const { data } = await axios.post('/caja/cobro-rapido', {
            tipo_comprobante:  crTipoComprobante.value,
            cliente_documento: crDocumento.value.trim() || null,
            cliente_nombre:    crNombre.value.trim(),
            descripcion:       crDescripcion.value.trim() || 'Consumo de alimentos',
            monto:             crTotal.value,
        })

        if (data.success) {
            imprimirCobroRapido(data)
            cerrarModalCobroRapido()
            router.reload({ only: ['mesas', 'resumen'] })
        }
    } catch (e) {
        crError.value = e.response?.data?.mensaje || 'Error al emitir el comprobante'
    } finally {
        crEnviando.value = false
    }
}

function imprimirCobroRapido(data) {
    const c = data.caja
    const comp = data.comprobante
    const e = data.empresa || {}
    const fecha = new Date(c.created_at)
    const fechaStr = fecha.toLocaleDateString('es-PE')
    const horaStr = fecha.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit' })

    const contenido = `
        <div style="text-align:center;margin-bottom:6px;">
            <p style="font-size:18px;font-weight:bold;margin:0;font-family:'Georgia',serif;">${e.nombre_comercial || e.razon_social || 'RESTAURANTE'}</p>
            <p style="font-size:12px;font-weight:600;margin:2px 0;">RUC: ${e.ruc || ''}</p>
            <p style="font-size:12px;font-weight:600;margin:2px 0;">${e.direccion || ''}</p>
        </div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div style="text-align:center;margin-bottom:6px;">
            <p style="font-size:14px;font-weight:bold;margin:0;">${comp.tipo_comprobante === '01' ? 'FACTURA' : 'BOLETA'} ${comp.serie}-${String(comp.numero).padStart(8,'0')}</p>
            <p style="font-size:10px;margin:2px 0;">Cobro rápido</p>
        </div>
        <div style="font-size:12px;font-weight:600;margin-bottom:6px;">
            <div style="display:flex;justify-content:space-between;"><span>FECHA:</span><span>${fechaStr}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>HORA:</span><span>${horaStr}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>CLIENTE:</span><span>${comp.cliente_nombre || '-'}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>DESCRIPCIÓN:</span><span>${c.notas || ''}</span></div>
        </div>
        <div style="border-top:1px dashed #000;margin:6px 0;"></div>
        <div style="font-size:13px;font-weight:700;">
            <div style="display:flex;justify-content:space-between;"><span>SUBTOTAL:</span><span>S/ ${Number(comp.total_gravada || 0).toFixed(2)}</span></div>
            <div style="display:flex;justify-content:space-between;"><span>IGV:</span><span>S/ ${Number(comp.total_igv || 0).toFixed(2)}</span></div>
            <div style="display:flex;justify-content:space-between;font-weight:bold;font-size:16px;margin-top:4px;">
                <span>TOTAL:</span><span>S/ ${Number(comp.total || 0).toFixed(2)}</span>
            </div>
        </div>
        <div style="border-top:1px dashed #000;margin:8px 0;"></div>
        <div style="text-align:center;font-size:10px;">
            <p>${comp.estado === 'aceptado' ? 'Comprobante aceptado por SUNAT' : 'Comprobante pendiente de validación SUNAT'}</p>
            <p style="margin-top:4px;">Sistema desarrollado por NEXPOS Solutions</p>
        </div>
    `
    const ventana = window.open('', '_blank')
    ventana.document.write('<html><head><title>Comprobante</title><style>body{font-family:\'Arial\',sans-serif;padding:4px;max-width:80mm;margin:0 auto;font-size:13px;font-weight:600;line-height:1.5;}*{box-sizing:border-box;margin:0;padding:0;}p{margin:2px 0;}@media print{@page{margin:2mm;size:80mm auto;}}</style></head><body>' + contenido + '</body></html>')
    ventana.document.close()
    setTimeout(() => ventana.print(), 500)
}
</script>
