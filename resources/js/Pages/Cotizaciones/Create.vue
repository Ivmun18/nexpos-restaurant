<template>
    <AppLayout title="Nueva cotizacion" subtitle="Crear presupuesto para cliente">

        <div style="display:grid; grid-template-columns:1fr 300px; gap:1.5rem; align-items:start;">

            <div style="display:flex; flex-direction:column; gap:1rem;">

                <!-- Cliente -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Cliente</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Buscar cliente registrado</label>
                            <select @change="seleccionarCliente($event)"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; background:white;">
                                <option value="">-- Seleccionar --</option>
                                <option v-for="c in clientes" :key="c.id" :value="c.id">{{ c.razon_social }}</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Fecha vencimiento</label>
                            <input v-model="form.fecha_vencimiento" type="date"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Razon social *</label>
                            <input v-model="form.cliente_razon_social" type="text"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Email</label>
                            <input v-model="form.cliente_email" type="email"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                        </div>
                    </div>
                    <div>
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Direccion</label>
                        <input v-model="form.cliente_direccion" type="text"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"/>
                    </div>
                </div>

                <!-- Productos -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Productos</p>
                    <div style="margin-bottom:1rem;">
                        <select @change="agregarProducto($event)"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; background:white;">
                            <option value="">+ Agregar producto...</option>
                            <option v-for="p in productos" :key="p.id" :value="p.id">{{ p.codigo }} - {{ p.descripcion }}</option>
                        </select>
                    </div>

                    <div v-if="form.items.length === 0" style="text-align:center; padding:2rem; color:#94A3B8; font-size:13px;">
                        Agrega productos para continuar
                    </div>

                    <div v-for="(item, i) in form.items" :key="i"
                        style="border:1px solid #E2E8F0; border-radius:8px; padding:12px; margin-bottom:10px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <div>
                                <p style="font-size:13px; font-weight:500; color:#1E293B; margin:0;">{{ item.descripcion }}</p>
                                <p style="font-size:11px; color:#94A3B8; margin:2px 0 0;">{{ item.unidad_medida }}</p>
                            </div>
                            <button type="button" @click="quitarItem(i)"
                                style="background:#FEF2F2; color:#991B1B; border:none; border-radius:6px; padding:4px 10px; font-size:12px; cursor:pointer;">X</button>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
                            <div>
                                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px;">Cantidad</label>
                                <input v-model="item.cantidad" type="number" step="0.001" min="0.001" @input="calcularItem(i)"
                                    style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; outline:none; box-sizing:border-box;"/>
                            </div>
                            <div>
                                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px;">Precio unit. (S/)</label>
                                <input v-model="item.precio_unitario" type="number" step="0.01" @input="calcularItem(i)"
                                    style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; outline:none; box-sizing:border-box;"/>
                            </div>
                            <div>
                                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px;">Total (S/)</label>
                                <input :value="Number(item.total).toFixed(2)" readonly
                                    style="width:100%; padding:8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; font-weight:600; background:#F8FAFC; outline:none; box-sizing:border-box;"/>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terminos -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Terminos y condiciones</p>
                    <textarea v-model="form.terminos_condiciones" rows="4" placeholder="Ej: Validez 30 dias, Forma de pago: 50% adelanto..."
                        style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                </div>
            </div>

            <!-- Panel derecho -->
            <div style="position:sticky; top:80px;">
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Resumen</p>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Op. gravadas</span>
                        <span style="font-size:13px;">S/ {{ totales.gravado.toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Op. exoneradas</span>
                        <span style="font-size:13px;">S/ {{ totales.exonerado.toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">IGV (18%)</span>
                        <span style="font-size:13px;">S/ {{ totales.igv.toFixed(2) }}</span>
                    </div>
                    <div style="border-top:2px solid #E2E8F0; margin:12px 0; padding-top:12px; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:15px; font-weight:700; color:#1E293B;">Total</span>
                        <span style="font-size:22px; font-weight:700; color:#2563EB;">S/ {{ totales.total.toFixed(2) }}</span>
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Observaciones</label>
                        <textarea v-model="form.observaciones" rows="2"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                    </div>
                    <p v-if="error" style="color:#991B1B; font-size:12px; margin-bottom:10px;">{{ error }}</p>
                    <button type="button" @click="guardar" :disabled="procesando || form.items.length === 0"
                        style="width:100%; padding:13px; background:#2563EB; color:white; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer;">
                        {{ procesando ? 'Guardando...' : 'Guardar cotizacion' }}
                    </button>
                    <a href="/cotizaciones" style="display:block; text-align:center; margin-top:10px; font-size:13px; color:#94A3B8; text-decoration:none;">Cancelar</a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ clientes: Array, productos: Array })
const procesando = ref(false)
const error = ref('')

const form = ref({
    cliente_id: null, cliente_tipo_doc: '', cliente_num_doc: '',
    cliente_razon_social: '', cliente_direccion: '', cliente_email: '',
    fecha_vencimiento: '', observaciones: '', terminos_condiciones: '', items: [],
})

const seleccionarCliente = (event) => {
    const id = parseInt(event.target.value)
    if (!id) return
    const c = props.clientes.find(c => c.id === id)
    if (!c) return
    form.value.cliente_id           = c.id
    form.value.cliente_tipo_doc     = c.tipo_documento
    form.value.cliente_num_doc      = c.numero_documento
    form.value.cliente_razon_social = c.razon_social
    form.value.cliente_direccion    = c.direccion || ''
    form.value.cliente_email        = c.email || ''
}

const agregarProducto = (event) => {
    const id = parseInt(event.target.value)
    if (!id) return
    const p = props.productos.find(p => p.id === id)
    if (!p) return
    const existe = form.value.items.findIndex(i => i.producto_id === p.id)
    if (existe >= 0) { form.value.items[existe].cantidad++; calcularItem(existe); event.target.value = ''; return }
    const precio = parseFloat(p.precio_venta || 0)
    const afecto = p.tipo_afectacion_igv === '10'
    const val    = afecto ? round(precio / 1.18, 4) : precio
    form.value.items.push({
        producto_id: p.id, descripcion: p.descripcion,
        unidad_medida: p.unidad_medida, tipo_afectacion_igv: p.tipo_afectacion_igv,
        afecto_igv: afecto, cantidad: 1, precio_unitario: precio,
        valor_unitario: val, total_valor_venta: val,
        total_igv: afecto ? round(val * 0.18, 2) : 0, total: round(precio, 2),
    })
    event.target.value = ''
}

const calcularItem = (i) => {
    const item = form.value.items[i]
    const cant = parseFloat(item.cantidad) || 0
    const prec = parseFloat(item.precio_unitario) || 0
    item.valor_unitario    = item.afecto_igv ? round(prec / 1.18, 4) : prec
    item.total_valor_venta = round(item.valor_unitario * cant, 2)
    item.total_igv         = item.afecto_igv ? round(item.total_valor_venta * 0.18, 2) : 0
    item.total             = round(prec * cant, 2)
}

const quitarItem = (i) => form.value.items.splice(i, 1)
const round = (val, dec) => Math.round(val * Math.pow(10, dec)) / Math.pow(10, dec)

const totales = computed(() => {
    let gravado = 0, exonerado = 0, igv = 0, total = 0
    form.value.items.forEach(item => {
        if (item.afecto_igv) { gravado += parseFloat(item.total_valor_venta) || 0; igv += parseFloat(item.total_igv) || 0 }
        else { exonerado += parseFloat(item.total_valor_venta) || 0 }
        total += parseFloat(item.total) || 0
    })
    return { gravado: round(gravado,2), exonerado: round(exonerado,2), igv: round(igv,2), total: round(total,2) }
})

const guardar = () => {
    error.value = ''
    if (!form.value.cliente_razon_social) { error.value = 'Ingresa el nombre del cliente.'; return }
    if (form.value.items.length === 0) { error.value = 'Agrega al menos un producto.'; return }
    procesando.value = true
    router.post('/cotizaciones', {
        ...form.value,
        total_gravado:   totales.value.gravado,
        total_exonerado: totales.value.exonerado,
        total_igv:       totales.value.igv,
        total:           totales.value.total,
    }, {
        onError: () => { error.value = 'Error al guardar.'; procesando.value = false }
    })
}
</script>
