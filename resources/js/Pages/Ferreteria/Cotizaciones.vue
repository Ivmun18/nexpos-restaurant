<template>
    <AppLayout title="Cotizaciones" subtitle="Gestión de cotizaciones ferretería">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <div style="display:flex; gap:12px;">
                <input v-model="busqueda" placeholder="🔍 Buscar por número o cliente..."
                    style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; width:280px; outline:none;">
                <select v-model="filtroEstado" style="padding:10px 16px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none;">
                    <option value="">Todos los estados</option>
                    <option value="borrador">Borrador</option>
                    <option value="enviada">Enviada</option>
                    <option value="aprobada">Aprobada</option>
                    <option value="rechazada">Rechazada</option>
                </select>
            </div>
            <button @click="abrirNueva()" style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                + Nueva Cotización
            </button>
        </div>

        <!-- Stats -->
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Total</p>
                <p style="font-size:24px; font-weight:700; color:#1E293B; margin:0;">{{ cotizaciones.length }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Pendientes</p>
                <p style="font-size:24px; font-weight:700; color:#F59E0B; margin:0;">{{ cotizaciones.filter(c=>c.estado==='enviada').length }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Aprobadas</p>
                <p style="font-size:24px; font-weight:700; color:#16A34A; margin:0;">{{ cotizaciones.filter(c=>c.estado==='aprobada').length }}</p>
            </div>
            <div style="background:white; border-radius:12px; padding:16px; border:1px solid #E2E8F0;">
                <p style="font-size:12px; color:#94A3B8; margin:0 0 4px;">Monto Total</p>
                <p style="font-size:20px; font-weight:700; color:#14B8A6; margin:0;">S/ {{ totalGeneral }}</p>
            </div>
        </div>

        <!-- Tabla -->
        <div style="background:white; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">N° COTIZACIÓN</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">CLIENTE</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">FECHA</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; font-weight:700; color:#64748B;">VENCE</th>
                        <th style="padding:14px 20px; text-align:right; font-size:12px; font-weight:700; color:#64748B;">TOTAL</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; font-weight:700; color:#64748B;">ESTADO</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; font-weight:700; color:#64748B;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="cotizacionesFiltradas.length === 0">
                        <td colspan="7" style="padding:40px; text-align:center; color:#94A3B8;">No hay cotizaciones registradas</td>
                    </tr>
                    <tr v-for="c in cotizacionesFiltradas" :key="c.id"
                        style="border-top:1px solid #F1F5F9;"
                        @mouseenter="e => e.currentTarget.style.background='#F8FAFC'"
                        @mouseleave="e => e.currentTarget.style.background='white'">
                        <td style="padding:14px 20px; font-size:14px; font-weight:700; color:#14B8A6;">{{ c.numero }}</td>
                        <td style="padding:14px 20px;">
                            <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ c.cliente_razon_social }}</p>
                            <p style="font-size:12px; color:#94A3B8; margin:0;">{{ c.cliente_num_doc }}</p>
                        </td>
                        <td style="padding:14px 20px; font-size:13px; color:#475569;">{{ c.fecha_emision }}</td>
                        <td style="padding:14px 20px; font-size:13px; color:#475569;">{{ c.fecha_vencimiento }}</td>
                        <td style="padding:14px 20px; text-align:right; font-size:14px; font-weight:700; color:#1E293B;">S/ {{ Number(c.total).toFixed(2) }}</td>
                        <td style="padding:14px 20px; text-align:center;">
                            <span :style="estadoStyle(c.estado)">{{ c.estado }}</span>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <div style="display:flex; gap:6px; justify-content:center;">
                                <button @click="verDetalle(c)" style="padding:6px 10px; background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; border-radius:8px; font-size:12px; cursor:pointer;">👁️</button>
                                <button v-if="c.estado==='borrador'" @click="cambiarEstado(c,'enviada')" style="padding:6px 10px; background:#F0FDFA; color:#0F766E; border:1px solid #CCFBF1; border-radius:8px; font-size:12px; cursor:pointer;">📤</button>
                                <button v-if="c.estado==='enviada'" @click="cambiarEstado(c,'aprobada')" style="padding:6px 10px; background:#F0FDF4; color:#166534; border:1px solid #BBF7D0; border-radius:8px; font-size:12px; cursor:pointer;">✅</button>
                                <button v-if="c.estado==='enviada'" @click="cambiarEstado(c,'rechazada')" style="padding:6px 10px; background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; border-radius:8px; font-size:12px; cursor:pointer;">❌</button>
                                <button @click="eliminar(c)" style="padding:6px 10px; background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; border-radius:8px; font-size:12px; cursor:pointer;">🗑️</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal Nueva Cotización -->
        <div v-if="modalNueva" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:flex-start; justify-content:center; overflow-y:auto; padding:20px;">
            <div style="background:white; border-radius:16px; padding:28px; width:700px; margin:auto;">
                <h2 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 20px;">Nueva Cotización</h2>

                <!-- Datos cliente -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">CLIENTE</label>
                        <select v-model="form.cliente_id" @change="llenarCliente" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                            <option value="">Seleccionar cliente...</option>
                            <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.razon_social }} — {{ c.numero_documento }}</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">FECHA EMISIÓN</label>
                        <input v-model="form.fecha_emision" type="date" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">FECHA VENCIMIENTO</label>
                        <input v-model="form.fecha_vencimiento" type="date" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">OBSERVACIONES</label>
                        <textarea v-model="form.observaciones" rows="2" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; font-size:14px; box-sizing:border-box; resize:none;"></textarea>
                    </div>
                </div>

                <!-- Items -->
                <div style="margin-bottom:16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                        <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0;">Productos</p>
                        <button @click="agregarItem()" style="padding:6px 14px; background:#F0FDFA; color:#0F766E; border:1px solid #CCFBF1; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">+ Agregar</button>
                    </div>
                    <!-- Lector código barras -->
                    <div style="display:flex; gap:8px; margin-bottom:12px;">
                        <input ref="inputScan" v-model="codigoScan" @input="autoScan"
                            placeholder="📷 Escanea código de barras para agregar producto..."
                            style="flex:1; padding:10px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                    </div>
                    <div v-for="(item,i) in form.items" :key="i" style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr auto; gap:8px; align-items:center; margin-bottom:8px;">
                        <select v-model="item.producto_id" @change="e => llenarItem(e, i)" style="padding:8px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px;">
                            <option value="">Seleccionar...</option>
                            <option v-for="p in productos" :key="p.id" :value="p.id">{{ p.descripcion }}</option>
                        </select>
                        <input v-model.number="item.cantidad" type="number" min="1" placeholder="Cant." style="padding:8px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; text-align:center;">
                        <input v-model.number="item.precio_unitario" type="number" step="0.01" placeholder="Precio" style="padding:8px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; text-align:right;">
                        <p style="font-size:13px; font-weight:700; color:#14B8A6; margin:0; text-align:right;">S/ {{ (item.cantidad * item.precio_unitario).toFixed(2) }}</p>
                        <button @click="form.items.splice(i,1)" style="padding:6px 10px; background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; border-radius:8px; cursor:pointer;">×</button>
                    </div>
                </div>

                <!-- Total -->
                <div style="background:#F8FAFC; border-radius:10px; padding:16px; margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Subtotal</span>
                        <span style="font-size:13px;">S/ {{ subtotalForm }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">IGV (18%)</span>
                        <span style="font-size:13px;">S/ {{ igvForm }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; padding-top:8px; border-top:1px solid #E2E8F0;">
                        <span style="font-size:15px; font-weight:700;">TOTAL</span>
                        <span style="font-size:15px; font-weight:700; color:#14B8A6;">S/ {{ totalForm }}</span>
                    </div>
                </div>

                <div style="display:flex; gap:12px; justify-content:flex-end;">
                    <button @click="modalNueva=false" style="padding:10px 20px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; cursor:pointer; background:white;">Cancelar</button>
                    <button @click="guardar" :disabled="procesando" style="padding:10px 24px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">
                        {{ procesando ? 'Guardando...' : 'Guardar Cotización' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Detalle -->
        <div v-if="modalDetalle && cotizacionVer" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:flex-start; justify-content:center; overflow-y:auto; padding:20px;">
            <div style="background:white; border-radius:16px; padding:28px; width:600px; margin:auto;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h2 style="font-size:18px; font-weight:700; color:#1E293B; margin:0;">{{ cotizacionVer.numero }}</h2>
                    <button @click="modalDetalle=false" style="border:none; background:none; font-size:20px; cursor:pointer; color:#94A3B8;">×</button>
                </div>
                <div style="margin-bottom:16px;">
                    <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ cotizacionVer.cliente_razon_social }}</p>
                    <p style="font-size:13px; color:#94A3B8; margin:2px 0 0;">{{ cotizacionVer.cliente_num_doc }}</p>
                </div>
                <table style="width:100%; border-collapse:collapse; margin-bottom:16px;">
                    <thead>
                        <tr style="background:#F8FAFC;">
                            <th style="padding:10px; text-align:left; font-size:12px; color:#64748B;">PRODUCTO</th>
                            <th style="padding:10px; text-align:center; font-size:12px; color:#64748B;">CANT.</th>
                            <th style="padding:10px; text-align:right; font-size:12px; color:#64748B;">PRECIO</th>
                            <th style="padding:10px; text-align:right; font-size:12px; color:#64748B;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="d in cotizacionVer.detalles" :key="d.id" style="border-top:1px solid #F1F5F9;">
                            <td style="padding:10px; font-size:13px; color:#1E293B;">{{ d.descripcion }}</td>
                            <td style="padding:10px; text-align:center; font-size:13px; color:#475569;">{{ d.cantidad }} {{ d.unidad_medida }}</td>
                            <td style="padding:10px; text-align:right; font-size:13px; color:#475569;">S/ {{ Number(d.precio_unitario).toFixed(2) }}</td>
                            <td style="padding:10px; text-align:right; font-size:13px; font-weight:700; color:#14B8A6;">S/ {{ Number(d.total_valor_venta).toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>
                <div style="text-align:right;">
                    <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">Total: S/ {{ Number(cotizacionVer.total).toFixed(2) }}</p>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    cotizaciones: { type: Array, default: () => [] },
    clientes:     { type: Array, default: () => [] },
    productos:    { type: Array, default: () => [] },
})

const busqueda      = ref('')
const filtroEstado  = ref('')
const modalNueva    = ref(false)
const modalDetalle  = ref(false)
const cotizacionVer = ref(null)
const procesando    = ref(false)
const codigoScan    = ref('')
const inputScan     = ref(null)
const scanTimer     = ref(null)

const hoy = new Date().toISOString().split('T')[0]
const en30 = new Date(Date.now() + 30*24*60*60*1000).toISOString().split('T')[0]

const form = ref({
    cliente_id: '', fecha_emision: hoy, fecha_vencimiento: en30,
    observaciones: '', items: []
})

const cotizacionesFiltradas = computed(() => {
    return props.cotizaciones.filter(c => {
        const matchBus = !busqueda.value ||
            c.numero?.includes(busqueda.value) ||
            c.cliente_razon_social?.toLowerCase().includes(busqueda.value.toLowerCase())
        const matchEst = !filtroEstado.value || c.estado === filtroEstado.value
        return matchBus && matchEst
    })
})

const totalGeneral = computed(() =>
    props.cotizaciones.filter(c => c.estado === 'aprobada')
        .reduce((s, c) => s + parseFloat(c.total || 0), 0).toFixed(2)
)

const subtotalForm = computed(() => {
    const t = form.value.items.reduce((s, i) => s + i.cantidad * i.precio_unitario, 0)
    return (t / 1.18).toFixed(2)
})
const igvForm  = computed(() => (form.value.items.reduce((s,i) => s + i.cantidad * i.precio_unitario, 0) - parseFloat(subtotalForm.value)).toFixed(2))
const totalForm = computed(() => form.value.items.reduce((s,i) => s + i.cantidad * i.precio_unitario, 0).toFixed(2))

const estadoStyle = (estado) => {
    const estilos = {
        borrador:  { padding:'4px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'700', background:'#F1F5F9', color:'#475569' },
        enviada:   { padding:'4px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'700', background:'#FEF3C7', color:'#92400E' },
        aprobada:  { padding:'4px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'700', background:'#F0FDF4', color:'#166534' },
        rechazada: { padding:'4px 10px', borderRadius:'20px', fontSize:'12px', fontWeight:'700', background:'#FEF2F2', color:'#991B1B' },
    }
    return estilos[estado] || estilos.borrador
}

const autoScan = () => {
    clearTimeout(scanTimer.value)
    if (codigoScan.value.length < 3) return
    // 800ms para escritura manual, el lector envía todo de golpe
    scanTimer.value = setTimeout(() => {
        const p = props.productos.find(p => p.codigo_barras === codigoScan.value || p.codigo === codigoScan.value)
        if (p) {
            const existe = form.value.items.find(i => i.producto_id == p.id)
            if (existe) {
                existe.cantidad++
            } else {
                form.value.items.push({
                    producto_id: p.id,
                    descripcion: p.descripcion,
                    unidad_medida: p.unidad_medida || 'UND',
                    cantidad: 1,
                    precio_unitario: p.precio_venta
                })
            }
            if (inputScan.value) {
                inputScan.value.style.border = '2px solid #16A34A'
                setTimeout(() => { inputScan.value.style.border = '2px solid #E2E8F0' }, 500)
            }
        } else {
            if (inputScan.value) {
                inputScan.value.style.border = '2px solid #DC2626'
                setTimeout(() => { inputScan.value.style.border = '2px solid #E2E8F0' }, 800)
            }
        }
        codigoScan.value = ''
    }, 800)
}

const abrirNueva = () => {
    form.value = { cliente_id: '', fecha_emision: hoy, fecha_vencimiento: en30, observaciones: '', items: [] }
    modalNueva.value = true
}

const llenarCliente = () => {
    const c = props.clientes.find(c => c.id == form.value.cliente_id)
    if (c) {
        form.value.cliente_razon_social = c.razon_social
        form.value.cliente_num_doc = c.numero_documento
        form.value.cliente_tipo_doc = c.tipo_documento
        form.value.cliente_direccion = c.direccion
        form.value.cliente_email = c.email
    }
}

const agregarItem = () => {
    form.value.items.push({ producto_id: '', descripcion: '', unidad_medida: 'UND', cantidad: 1, precio_unitario: 0 })
}

const llenarItem = (e, i) => {
    const p = props.productos.find(p => p.id == form.value.items[i].producto_id)
    if (p) {
        form.value.items[i].descripcion    = p.descripcion
        form.value.items[i].unidad_medida  = p.unidad_medida || 'UND'
        form.value.items[i].precio_unitario = p.precio_venta
    }
}

const guardar = () => {
    procesando.value = true
    router.post('/ferreteria/cotizaciones', form.value, {
        onSuccess: () => { modalNueva.value = false; procesando.value = false },
        onError:   () => { procesando.value = false }
    })
}

const verDetalle = (c) => {
    cotizacionVer.value = c
    modalDetalle.value = true
}

const cambiarEstado = (c, estado) => {
    router.patch(`/ferreteria/cotizaciones/${c.id}/estado`, { estado })
}

const eliminar = (c) => {
    if (confirm(`¿Eliminar cotización ${c.numero}?`)) {
        router.delete(`/ferreteria/cotizaciones/${c.id}`)
    }
}
</script>
