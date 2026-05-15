<template>
    <AppLayout title="Nueva compra" subtitle="Registrar factura de proveedor">

        <div style="display:grid; grid-template-columns:1fr 350px; gap:1.5rem; align-items:start;">

            <div style="display:flex; flex-direction:column; gap:1rem;">

                <!-- Proveedor -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Proveedor</p>
                    <div style="margin-bottom:1rem;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Seleccionar proveedor *</label>
                        <select v-model="form.proveedor_id"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; background:white;">
                            <option value="">-- Seleccionar --</option>
                            <option v-for="p in proveedores" :key="p.id" :value="p.id">
                                {{ p.razon_social }} ({{ p.numero_documento }})
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Datos del comprobante -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Datos del comprobante</p>

                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; margin-bottom:1rem;">
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Tipo</label>
                            <select v-model="form.tipo_comprobante"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; background:white;">
                                <option value="01">Factura</option>
                                <option value="03">Boleta</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Serie *</label>
                            <input v-model="form.serie_proveedor" type="text" maxlength="4" placeholder="F001"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box; font-family:monospace;"/>
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Correlativo *</label>
                            <input v-model="form.correlativo_proveedor" type="number" min="1"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem;">
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Fecha emision *</label>
                            <input v-model="form.fecha_emision" type="date"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Fecha vencimiento</label>
                            <input v-model="form.fecha_vencimiento" type="date"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Forma de pago</label>
                            <select v-model="form.forma_pago"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; background:white;">
                                <option value="contado">Contado</option>
                                <option value="credito">Credito</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Productos -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Productos</p>

                    <!-- Lector de código de barras mejorado -->
                    <div style="margin-bottom:12px;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">📷 Escanear código de barras</label>
                        <div style="display:flex; gap:8px;">
                            <input
                                ref="scanInput"
                                type="text"
                                placeholder="Pega o escribe código..."
                                @keydown.enter="procesarScan"
                                @blur="limpiarScan"
                                style="flex:1; padding:10px 14px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; transition:border-color 0.3s;"
                            />
                            <button type="button" @click="procesarScan"
                                style="padding:10px 16px; background:#14B8A6; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                                ✓ Buscar
                            </button>
                        </div>
                        <p v-if="mensajeScan" :style="{ color: scanExitoso ? '#16A34A' : '#DC2626', fontSize: '12px', marginTop: '4px', marginBottom: '0' }">
                            {{ mensajeScan }}
                        </p>
                    </div>

                    <!-- Selector por nombre -->
                    <div style="margin-bottom:1rem;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">O selecciona por nombre</label>
                        <select @change="agregarProducto($event)" style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; background:white;">
                            <option value="">+ Agregar producto por nombre...</option>
                            <option v-for="p in productos" :key="p.id" :value="p.id">
                                {{ p.codigo }} - {{ p.descripcion }}
                            </option>
                        </select>
                    </div>

                    <!-- Grid de productos agregados -->
                    <div v-if="form.items.length === 0" style="text-align:center; padding:2rem; color:#94A3B8; font-size:13px; background:#F8FAFC; border-radius:8px;">
                        📦 Agrega productos para continuar
                    </div>

                    <div v-else style="border:1px solid #E2E8F0; border-radius:8px; overflow:hidden;">
                        <!-- Encabezado del grid -->
                        <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 1fr 0.8fr; gap:8px; padding:12px; background:#F8FAFC; font-weight:600; font-size:12px; color:#64748B; border-bottom:2px solid #E2E8F0;">
                            <div>Producto</div>
                            <div style="text-align:right;">Cant.</div>
                            <div style="text-align:right;">P.Unit.</div>
                            <div style="text-align:right;">Desc.</div>
                            <div style="text-align:right;">Total</div>
                            <div style="text-align:center;">Acc.</div>
                        </div>

                        <!-- Filas de productos -->
                        <div v-for="(item, i) in form.items" :key="i"
                            style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 1fr 0.8fr; gap:8px; padding:12px; border-bottom:1px solid #E2E8F0; align-items:center;">
                            
                            <!-- Descripción -->
                            <div>
                                <p style="font-size:13px; font-weight:500; color:#1E293B; margin:0;">{{ item.descripcion }}</p>
                                <p style="font-size:11px; color:#94A3B8; margin:2px 0 0;">{{ item.unidad_medida }}</p>
                            </div>

                            <!-- Cantidad -->
                            <input v-model.number="item.cantidad" type="number" step="0.001" min="0.001" @input="calcularItem(i)"
                                style="padding:6px; border:1px solid #E2E8F0; border-radius:4px; font-size:12px; text-align:right;"/>

                            <!-- Precio unitario -->
                            <input v-model.number="item.precio_unitario" type="number" step="0.01" min="0" @input="calcularItem(i)"
                                style="padding:6px; border:1px solid #E2E8F0; border-radius:4px; font-size:12px; text-align:right;"/>

                            <!-- Descuento -->
                            <div style="display:flex; gap:4px; align-items:center;">
                                <input v-model.number="item.descuento_porcentaje" type="number" step="0.01" min="0" max="100" @input="calcularItem(i)"
                                    placeholder="0%" style="flex:1; padding:6px; border:1px solid #E2E8F0; border-radius:4px; font-size:12px; text-align:right;"/>
                                <span style="font-size:11px; color:#94A3B8;">%</span>
                            </div>

                            <!-- Total -->
                            <div style="text-align:right; font-weight:600; color:#1E293B; font-size:13px;">
                                S/ {{ Number(item.total).toFixed(2) }}
                            </div>

                            <!-- Acción eliminar -->
                            <button type="button" @click="quitarItem(i)"
                                style="background:#FEF2F2; color:#991B1B; border:none; border-radius:4px; padding:6px 8px; font-size:12px; cursor:pointer; font-weight:600;">
                                🗑️
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel derecho - Resumen -->
            <div style="position:sticky; top:80px;">
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Resumen</p>

                    <!-- Totales sin descuento -->
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:11px; color:#94A3B8;">
                        <span>Subtotal</span>
                        <span>S/ {{ totales.subtotal.toFixed(2) }}</span>
                    </div>

                    <!-- Descuento global -->
                    <div v-if="totales.descuentoTotal > 0" style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:11px; color:#DC2626;">
                        <span>Descuentos</span>
                        <span>-S/ {{ totales.descuentoTotal.toFixed(2) }}</span>
                    </div>

                    <!-- Bases tributarias -->
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px; border-top:1px solid #E2E8F0; padding-top:8px;">
                        <span style="font-size:13px; color:#64748B;">Op. gravadas</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ totales.gravado.toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Op. exoneradas</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ totales.exonerado.toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">IGV (18%)</span>
                        <span style="font-size:13px; color:#1E293B;">S/ {{ totales.igv.toFixed(2) }}</span>
                    </div>

                    <!-- Total -->
                    <div style="border-top:2px solid #E2E8F0; margin:12px 0; padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:15px; font-weight:700; color:#1E293B;">Total</span>
                        <span style="font-size:22px; font-weight:700; color:#2563EB;">S/ {{ totales.total.toFixed(2) }}</span>
                    </div>

                    <!-- Observaciones -->
                    <div style="margin-bottom:1rem;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Observaciones</label>
                        <textarea v-model="form.observaciones" rows="2"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box; resize:none;"></textarea>
                    </div>

                    <!-- Mensajes de error -->
                    <p v-if="error" style="color:#991B1B; font-size:12px; margin-bottom:10px; background:#FEF2F2; padding:8px; border-radius:6px;">
                        ⚠️ {{ error }}
                    </p>

                    <!-- Botones -->
                    <button type="button" @click="guardar" :disabled="procesando || form.items.length === 0"
                        style="width:100%; padding:13px; background:#2563EB; color:white; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; margin-bottom:8px;"
                        :style="{ opacity: procesando || form.items.length === 0 ? 0.6 : 1 }">
                        {{ procesando ? '⏳ Guardando...' : '✓ Registrar compra' }}
                    </button>

                    <a href="/compras" style="display:block; text-align:center; font-size:13px; color:#94A3B8; text-decoration:none;">
                        ✕ Cancelar
                    </a>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    proveedores: Array,
    productos:   Array,
})

const procesando = ref(false)
const error = ref('')
const scanInput = ref(null)
const mensajeScan = ref('')
const scanExitoso = ref(false)

const hoy = new Date().toISOString().split('T')[0]

const form = ref({
    proveedor_id:          '',
    tipo_comprobante:      '01',
    serie_proveedor:       '',
    correlativo_proveedor: '',
    fecha_emision:         hoy,
    fecha_vencimiento:     '',
    forma_pago:            'contado',
    observaciones:         '',
    items:                 [],
})

// Procesar escaneo de código de barras
const procesarScan = () => {
    if (!scanInput.value) return
    const codigo = scanInput.value.value.trim()
    if (!codigo) return

    const producto = props.productos.find(p =>
        p.codigo_barras === codigo || p.codigo === codigo
    )

    if (!producto) {
        mensajeScan.value = `❌ Producto no encontrado: ${codigo}`
        scanExitoso.value = false
        scanInput.value.style.borderColor = '#DC2626'
        setTimeout(() => {
            scanInput.value.style.borderColor = '#E2E8F0'
            mensajeScan.value = ''
        }, 2000)
        return
    }

    // Agregar o incrementar producto
    const existe = form.value.items.findIndex(i => i.producto_id === producto.id)
    if (existe >= 0) {
        form.value.items[existe].cantidad++
        calcularItem(existe)
        mensajeScan.value = `✓ Cantidad incrementada: ${producto.descripcion}`
    } else {
        agregarProductoDirecto(producto)
        mensajeScan.value = `✓ Agregado: ${producto.descripcion}`
    }

    scanExitoso.value = true
    scanInput.value.style.borderColor = '#16A34A'
    scanInput.value.value = ''
    scanInput.value.focus()

    setTimeout(() => {
        scanInput.value.style.borderColor = '#E2E8F0'
        mensajeScan.value = ''
    }, 2000)
}

const limpiarScan = () => {
    if (scanInput.value && !scanInput.value.value.trim()) {
        mensajeScan.value = ''
    }
}

// Agregar producto directo (desde scan o selector)
const agregarProductoDirecto = (p) => {
    const precio = parseFloat(p.precio_compra || 0)
    const afecto = p.tipo_afectacion_igv === '10'
    const valorUnitario = afecto ? Math.round(precio / 1.18 * 10000) / 10000 : precio

    form.value.items.push({
        producto_id: p.id,
        descripcion: p.descripcion,
        unidad_medida: p.unidad_medida,
        tipo_afectacion_igv: p.tipo_afectacion_igv,
        afecto_igv: afecto,
        cantidad: 1,
        precio_unitario: precio,
        valor_unitario: valorUnitario,
        descuento_porcentaje: 0,
        descuento_monto: 0,
        total_igv: afecto ? Math.round(valorUnitario * 0.18 * 100) / 100 : 0,
        total: Math.round(precio * 100) / 100,
    })
}

// Agregar producto desde selector
const agregarProducto = (event) => {
    const id = parseInt(event.target.value)
    if (!id) return
    const p = props.productos.find(p => p.id === id)
    if (!p) return

    const existe = form.value.items.findIndex(i => i.producto_id === p.id)
    if (existe >= 0) {
        form.value.items[existe].cantidad++
        calcularItem(existe)
        event.target.value = ''
        return
    }

    agregarProductoDirecto(p)
    event.target.value = ''
}

// Calcular totales del item (cantidad, precio, descuento)
const calcularItem = (i) => {
    const item = form.value.items[i]
    const cant = parseFloat(item.cantidad) || 0
    const precio = parseFloat(item.precio_unitario) || 0
    const descPorcentaje = parseFloat(item.descuento_porcentaje) || 0
    const afecto = item.afecto_igv

    // Subtotal antes de descuento
    const subtotalItem = round(precio * cant, 2)

    // Descuento
    item.descuento_monto = round(subtotalItem * (descPorcentaje / 100), 2)

    // Total después de descuento
    item.total = round(subtotalItem - item.descuento_monto, 2)

    // Valor unitario sin IGV
    item.valor_unitario = afecto ? round(precio / 1.18, 4) : precio

    // IGV solo sobre el valor después de descuento
    item.total_igv = afecto ? round((item.total * 0.18 / 1.18), 2) : 0
}

// Quitar item
const quitarItem = (i) => form.value.items.splice(i, 1)

// Helper para redondeo
const round = (val, dec) => Math.round(val * Math.pow(10, dec)) / Math.pow(10, dec)

// Totales calculados
const totales = computed(() => {
    let gravado = 0, exonerado = 0, igv = 0, total = 0, subtotal = 0, descuentoTotal = 0

    form.value.items.forEach(item => {
        const cant = parseFloat(item.cantidad) || 0
        const precio = parseFloat(item.precio_unitario) || 0
        const descMonto = parseFloat(item.descuento_monto) || 0

        // Subtotal
        const subtotalLinea = round(precio * cant, 2)
        subtotal += subtotalLinea

        // Descuento
        descuentoTotal += descMonto

        // Total por línea (después de descuento)
        const totalLinea = round(subtotalLinea - descMonto, 2)
        total += totalLinea

        // Bases tributarias
        if (item.afecto_igv) {
            const valorSinIgv = round(totalLinea / 1.18, 4)
            gravado += valorSinIgv
            igv += round(totalLinea - valorSinIgv, 2)
        } else {
            exonerado += totalLinea
        }
    })

    return {
        subtotal: round(subtotal, 2),
        descuentoTotal: round(descuentoTotal, 2),
        gravado: round(gravado, 2),
        exonerado: round(exonerado, 2),
        igv: round(igv, 2),
        total: round(total, 2)
    }
})

// Enfocar input de escaneo al montar
onMounted(() => {
    if (scanInput.value) {
        setTimeout(() => scanInput.value.focus(), 200)
    }
})

// Guardar compra
const guardar = () => {
    error.value = ''

    if (!form.value.proveedor_id) {
        error.value = 'Selecciona un proveedor.'
        return
    }
    if (!form.value.serie_proveedor) {
        error.value = 'Ingresa la serie del comprobante.'
        return
    }
    if (form.value.items.length === 0) {
        error.value = 'Agrega al menos un producto.'
        return
    }

    procesando.value = true
    router.post('/compras', form.value, {
        onError: () => {
            error.value = 'Error al guardar. Intenta nuevamente.'
            procesando.value = false
        }
    })
}
</script>