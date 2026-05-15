<template>
    <AppLayout title="Nueva compra" subtitle="Registrar factura de proveedor">

        <div style="display:grid; grid-template-columns:1fr 320px; gap:1.5rem; align-items:start;">

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

                    <!-- Lector de código de barras -->
                    <div style="margin-bottom:12px; display:flex; gap:8px;">
                        <input
                            id="campo-scan"
                            type="text"
                            placeholder="📷 Escribe código de barras..."
                            style="flex:1; padding:10px 14px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                        />
                        <button type="button" @click="buscarPorCodigo"
                            style="padding:10px 16px; background:#14B8A6; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                            📷 Agregar
                        </button>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <select @change="agregarProducto($event)"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; background:white;">
                            <option value="">+ Agregar producto por nombre...</option>
                            <option v-for="p in productos" :key="p.id" :value="p.id">
                                {{ p.codigo }} - {{ p.descripcion }}
                            </option>
                        </select>
                    </div>

                    <div v-if="form.items.length === 0" style="text-align:center; padding:2rem; color:#94A3B8; font-size:13px;">
                        Agrega productos para continuar
                    </div>

                    <div v-for="(item, i) in form.items" :key="i"
                        style="border:1px solid #E2E8F0; border-radius:8px; padding:12px; margin-bottom:10px;">
                        <div style="display:flex; align-items:start; justify-content:space-between; margin-bottom:8px;">
                            <div style="flex:1;">
                                <p style="font-size:13px; font-weight:500; color:#1E293B; margin:0;">{{ item.descripcion }}</p>
                                <p style="font-size:11px; color:#94A3B8; margin:2px 0 0;">{{ item.unidad_medida }}</p>
                            </div>
                            <button type="button" @click="quitarItem(i)"
                                style="background:#FEF2F2; color:#991B1B; border:none; border-radius:6px; padding:4px 10px; font-size:12px; cursor:pointer;">X</button>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px; margin-bottom:8px;">
                            <div>
                                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px;">Cantidad</label>
                                <input v-model="item.cantidad" type="number" step="0.001" min="0.001"
                                    @input="calcularItem(i)"
                                    style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; outline:none; box-sizing:border-box;"/>
                            </div>
                            <div>
                                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px;">Precio unit. (S/)</label>
                                <input v-model="item.precio_unitario" type="number" step="0.01" min="0"
                                    @input="calcularItem(i)"
                                    style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; outline:none; box-sizing:border-box;"/>
                            </div>
                            <div>
                                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px;">Total (S/)</label>
                                <input :value="Number(item.total).toFixed(2)" readonly
                                    style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; font-weight:600; color:#1E293B; background:#F8FAFC; outline:none; box-sizing:border-box;"/>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; padding-top:8px; border-top:1px dashed #E2E8F0;">
                            <div>
                                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px;">🏷️ Lote (opcional)</label>
                                <input v-model="item.lote" type="text" placeholder="Ej: L-2026-001"
                                    style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; outline:none; box-sizing:border-box; font-family:monospace;"/>
                            </div>
                            <div>
                                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px;">📅 Vencimiento</label>
                                <input v-model="item.fecha_vencimiento" type="date"
                                    style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; outline:none; box-sizing:border-box;"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel derecho -->
            <div style="position:sticky; top:80px;">
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Resumen</p>

                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
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
                    <div style="border-top:2px solid #E2E8F0; margin:12px 0; padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:15px; font-weight:700; color:#1E293B;">Total</span>
                        <span style="font-size:22px; font-weight:700; color:#2563EB;">S/ {{ totales.total.toFixed(2) }}</span>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Observaciones</label>
                        <textarea v-model="form.observaciones" rows="2"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box; resize:none;"></textarea>
                    </div>

                    <p v-if="error" style="color:#991B1B; font-size:12px; margin-bottom:10px;">{{ error }}</p>

                    <button type="button" @click="guardar" :disabled="procesando || form.items.length === 0"
                        style="width:100%; padding:13px; background:#2563EB; color:white; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer;">
                        {{ procesando ? 'Guardando...' : 'Registrar compra' }}
                    </button>

                    <a href="/compras" style="display:block; text-align:center; margin-top:10px; font-size:13px; color:#94A3B8; text-decoration:none;">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>

    </AppLayout>

        <!-- Modal: Producto nuevo -->
        <div v-if="modalProductoNuevo.visible"
            style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; display:flex; align-items:center; justify-content:center; padding:1rem;">
            <div style="background:white; border-radius:12px; max-width:600px; width:100%; max-height:90vh; overflow-y:auto; padding:1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; padding-bottom:12px; border-bottom:2px solid #E2E8F0;">
                    <div>
                        <h3 style="font-size:18px; font-weight:700; color:#1E293B; margin:0;">🆕 Producto Nuevo</h3>
                        <p style="font-size:12px; color:#64748B; margin:4px 0 0;">No se encontró este código. Crea el producto y se agregará a la compra.</p>
                    </div>
                    <button type="button" @click="modalProductoNuevo.visible = false"
                        style="background:#F1F5F9; border:none; border-radius:8px; width:32px; height:32px; cursor:pointer; font-size:16px;">✕</button>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div style="grid-column:1 / -1;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px; font-weight:600;">Código de barras</label>
                        <input v-model="modalProductoNuevo.codigo_barras" type="text" readonly
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; background:#F8FAFC; font-family:monospace; box-sizing:border-box;"/>
                    </div>
                    <div style="grid-column:1 / -1;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px; font-weight:600;">Descripción *</label>
                        <input v-model="modalProductoNuevo.descripcion" type="text" placeholder="Ej: Mucosolvan jarabe 120ml"
                            style="width:100%; padding:10px; border:2px solid #14B8A6; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px; font-weight:600;">Precio compra (S/) *</label>
                        <input v-model="modalProductoNuevo.precio_compra" type="number" step="0.01" min="0" placeholder="0.00"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px; font-weight:600;">Precio venta (S/) *</label>
                        <input v-model="modalProductoNuevo.precio_venta" type="number" step="0.01" min="0" placeholder="0.00"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">🏢 Laboratorio</label>
                        <input v-model="modalProductoNuevo.laboratorio" type="text" placeholder="Ej: Medifarma"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Unidad</label>
                        <select v-model="modalProductoNuevo.unidad_medida"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; background:white; box-sizing:border-box;">
                            <option value="NIU">Unidad</option>
                            <option value="BX">Caja</option>
                            <option value="ZZ">Servicio</option>
                            <option value="KGM">Kilogramo</option>
                            <option value="LTR">Litro</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">🏷️ Lote</label>
                        <input v-model="modalProductoNuevo.lote" type="text" placeholder="Ej: L-2026-001"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; font-family:monospace;"/>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">📅 Vencimiento</label>
                        <input v-model="modalProductoNuevo.fecha_vencimiento" type="date"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">IGV</label>
                        <select v-model="modalProductoNuevo.tipo_afectacion_igv"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; background:white; box-sizing:border-box;">
                            <option value="10">Gravado (18%)</option>
                            <option value="20">Exonerado</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex; gap:10px; margin-top:1.5rem; justify-content:flex-end;">
                    <button type="button" @click="modalProductoNuevo.visible = false"
                        style="padding:10px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">Cancelar</button>
                    <button type="button" @click="crearProductoYAgregar"
                        style="padding:10px 20px; background:#14B8A6; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">✓ Crear y agregar</button>
                </div>
            </div>
        </div>

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
const error      = ref('')

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

const scanCodigo = ref('')
const modalProductoNuevo = ref({
    visible: false,
    codigo_barras: '',
    descripcion: '',
    precio_compra: '',
    precio_venta: '',
    laboratorio: '',
    lote: '',
    fecha_vencimiento: '',
    unidad_medida: 'NIU',
    tipo_afectacion_igv: '10',
})

const crearProductoYAgregar = async () => {
    const m = modalProductoNuevo.value
    if (!m.descripcion || !m.precio_compra || !m.precio_venta) {
        alert('Descripcion, precio compra y precio venta son obligatorios')
        return
    }
    try {
        const response = await fetch('/compras/producto-rapido', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                descripcion: m.descripcion,
                codigo_barras: m.codigo_barras,
                precio_compra: parseFloat(m.precio_compra),
                precio_venta: parseFloat(m.precio_venta),
                laboratorio: m.laboratorio,
                lote: m.lote,
                fecha_vencimiento: m.fecha_vencimiento,
                unidad_medida: m.unidad_medida,
                tipo_afectacion_igv: m.tipo_afectacion_igv,
            })
        })
        const data = await response.json()
        if (data.success && data.producto) {
            // Agregar al inicio de productos disponibles
            props.productos.push(data.producto)
            // Agregar al carrito
            const p = data.producto
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
                total_igv: afecto ? Math.round(valorUnitario * 0.18 * 100) / 100 : 0,
                total: Math.round(precio * 100) / 100,
                lote: p.lote || '',
                fecha_vencimiento: p.fecha_vencimiento || '',
            })
            // Cerrar modal y limpiar
            modalProductoNuevo.value = {
                visible: false, codigo_barras: '', descripcion: '',
                precio_compra: '', precio_venta: '', laboratorio: '',
                lote: '', fecha_vencimiento: '', unidad_medida: 'NIU',
                tipo_afectacion_igv: '10'
            }
        } else {
            alert('Error al crear producto: ' + (data.message || 'desconocido'))
        }
    } catch (err) {
        alert('Error de conexion: ' + err.message)
    }
}
const codigoScan = ref('')

const buscarPorCodigo = () => {
    const inputEl = document.getElementById('campo-scan')
    const codigo = inputEl ? inputEl.value.trim() : ''
    if (!codigo) return
    if (inputEl) { inputEl.value = ''; setTimeout(() => inputEl.focus(), 100) }
    const p = props.productos.find(p =>
        p.codigo_barras === codigo || p.codigo === codigo
    )
    if (!p) {
        // Producto no encontrado - abrir modal para crearlo
        modalProductoNuevo.value.codigo_barras = codigo
        modalProductoNuevo.value.visible = true
        return
    }
    const existe = form.value.items.findIndex(i => i.producto_id === p.id)
    if (existe >= 0) {
        form.value.items[existe].cantidad++
        calcularItem(existe)
    } else {
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
            total_igv: afecto ? Math.round(valorUnitario * 0.18 * 100) / 100 : 0,
            total: Math.round(precio * 100) / 100,
            lote: p.lote || '',
            fecha_vencimiento: p.fecha_vencimiento || '',
        })
    }
}

onMounted(() => {
    const inp = document.getElementById('scan-input')
    if (inp) {
        inp.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault()
                const codigo = inp.value.trim()
                if (!codigo) return
                const producto = props.productos.find(p =>
                    p.codigo_barras === codigo || p.codigo === codigo
                )
                if (producto) {
                    const p = producto
                    const existe = form.value.items.findIndex(i => i.producto_id === p.id)
                    if (existe >= 0) {
                        form.value.items[existe].cantidad++
                        calcularItem(existe)
                    } else {
                        const precio = parseFloat(p.precio_compra || 0)
                        const afecto = p.tipo_afectacion_igv === "10"
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
                            total_igv: afecto ? Math.round(valorUnitario * 0.18 * 100) / 100 : 0,
                            total: Math.round(precio * 100) / 100,
                        })
                    }
                    inp.value = ""
                    inp.style.border = "2px solid #16A34A"
                    setTimeout(() => { inp.style.border = "2px solid #E2E8F0" }, 500)
                } else {
                    inp.style.border = "2px solid #DC2626"
                    setTimeout(() => { inp.style.border = "2px solid #E2E8F0" }, 800)
                }
            }
        })
    }
})
const scanActivo = ref(false)
const inputScanCompra = ref(null)

const activarScanCompra = () => {
    scanActivo.value = true
    if (inputScanCompra.value) inputScanCompra.value.focus()
}

const escanearDesdeInput = (event) => {
    const codigo = event.target.value.trim()
    if (!codigo) return
    const producto = props.productos.find(p =>
        p.codigo_barras === codigo || p.codigo === codigo
    )
    if (producto) {
        const p = producto
        const existe = form.value.items.findIndex(i => i.producto_id === p.id)
        if (existe >= 0) {
            form.value.items[existe].cantidad++
            calcularItem(existe)
        } else {
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
                total_igv: afecto ? Math.round(valorUnitario * 0.18 * 100) / 100 : 0,
                total: Math.round(precio * 100) / 100,
            })
        }
        event.target.value = ''
        if (inputScanCompra.value) {
            inputScanCompra.value.style.border = '2px solid #16A34A'
            setTimeout(() => { inputScanCompra.value.style.border = '2px solid #E2E8F0' }, 500)
        }
    } else {
        if (inputScanCompra.value) {
            inputScanCompra.value.style.border = '2px solid #DC2626'
            setTimeout(() => { inputScanCompra.value.style.border = '2px solid #E2E8F0' }, 800)
        }
    }
}

const agregarProducto = (event) => {
    const id = parseInt(event.target.value)
    if (!id) return
    const p = props.productos.find(p => p.id === id)
    if (!p) return
    const existe = form.value.items.findIndex(i => i.producto_id === p.id)
    if (existe >= 0) { form.value.items[existe].cantidad++; calcularItem(existe); return }
    const precio = parseFloat(p.precio_compra || 0)
    const afecto = p.tipo_afectacion_igv === '10'
    const valorUnitario = afecto ? round(precio / 1.18, 4) : precio
    form.value.items.push({
        producto_id:         p.id,
        descripcion:         p.descripcion,
        unidad_medida:       p.unidad_medida,
        tipo_afectacion_igv: p.tipo_afectacion_igv,
        afecto_igv:          afecto,
        cantidad:            1,
        precio_unitario:     precio,
        valor_unitario:      valorUnitario,
        total_igv:           afecto ? round(valorUnitario * 0.18, 2) : 0,
        total:               round(precio, 2),
    })
    event.target.value = ''
}

const calcularItem = (i) => {
    const item    = form.value.items[i]
    const cant    = parseFloat(item.cantidad) || 0
    const precio  = parseFloat(item.precio_unitario) || 0
    const afecto  = item.afecto_igv
    item.valor_unitario = afecto ? round(precio / 1.18, 4) : precio
    item.total_igv      = afecto ? round(item.valor_unitario * cant * 0.18, 2) : 0
    item.total          = round(precio * cant, 2)
}

const quitarItem = (i) => form.value.items.splice(i, 1)

const round = (val, dec) => Math.round(val * Math.pow(10, dec)) / Math.pow(10, dec)

const totales = computed(() => {
    let gravado = 0, exonerado = 0, igv = 0, total = 0
    form.value.items.forEach(item => {
        const cant   = parseFloat(item.cantidad) || 0
        const precio = parseFloat(item.precio_unitario) || 0
        const val    = item.afecto_igv ? round(precio / 1.18, 4) * cant : precio * cant
        if (item.afecto_igv) { gravado += val; igv += round(val * 0.18, 2) }
        else { exonerado += val }
        total += round(precio * cant, 2)
    })
    return { gravado: round(gravado,2), exonerado: round(exonerado,2), igv: round(igv,2), total: round(total,2) }
})

const guardar = () => {
    error.value = ''
    if (!form.value.proveedor_id) { error.value = 'Selecciona un proveedor.'; return }
    if (!form.value.serie_proveedor) { error.value = 'Ingresa la serie del comprobante.'; return }
    if (form.value.items.length === 0) { error.value = 'Agrega al menos un producto.'; return }
    procesando.value = true
    router.post('/compras', form.value, {
        onError: () => { error.value = 'Error al guardar.'; procesando.value = false }
    })
}
</script>
