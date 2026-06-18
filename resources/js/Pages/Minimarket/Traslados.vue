<template>
    <AppLayout title="Traslados" subtitle="Traslado de mercaderia entre locales">

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
            <div>
                <p style="font-size:24px; font-weight:800; color:#1E293B; margin:0;">🚚 Traslado de Mercaderia</p>
                <p style="font-size:14px; color:#94A3B8; margin:4px 0 0;">Registra envios de productos a otros locales y consulta el historial</p>
            </div>
            <button @click="modalNuevo = true"
                style="padding:12px 20px; background:linear-gradient(135deg,#0EA5E9,#0369A1); color:white; border-radius:10px; font-size:14px; font-weight:700; border:none; cursor:pointer;">
                + Nuevo Traslado
            </button>
        </div>

        <div style="background:white; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC; border-bottom:1px solid #E2E8F0;">
                        <th style="text-align:left; padding:12px 16px; font-size:12px; color:#64748B; font-weight:700;">FECHA Y HORA</th>
                        <th style="text-align:left; padding:12px 16px; font-size:12px; color:#64748B; font-weight:700;">LOCAL DESTINO</th>
                        <th style="text-align:left; padding:12px 16px; font-size:12px; color:#64748B; font-weight:700;">REGISTRADO POR</th>
                        <th style="text-align:left; padding:12px 16px; font-size:12px; color:#64748B; font-weight:700;">TRANSPORTISTA</th>
                        <th style="text-align:left; padding:12px 16px; font-size:12px; color:#64748B; font-weight:700;">PLACA</th>
                        <th style="text-align:left; padding:12px 16px; font-size:12px; color:#64748B; font-weight:700;">PRODUCTOS</th>
                        <th style="text-align:left; padding:12px 16px; font-size:12px; color:#64748B; font-weight:700;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="t in traslados" :key="t.id" style="border-bottom:1px solid #F1F5F9;">
                        <td style="padding:12px 16px; font-size:13px; color:#1E293B;">{{ formatearFecha(t.created_at) }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#1E293B; font-weight:600;">{{ t.local_destino }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ t.usuario?.name || '-' }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ t.transportista || '-' }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ t.placa_vehiculo || '-' }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ t.detalle?.length || 0 }} producto(s)</td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ t.placa_vehiculo || '-' }}</td>
                        <td style="padding:12px 16px; font-size:13px; color:#64748B;">{{ t.detalle?.length || 0 }} producto(s)</td>
                        <td style="padding:12px 16px;">
                            <button @click="verDetalle(t)"
                                style="padding:6px 12px; background:#EFF6FF; color:#1D4ED8; border-radius:6px; font-size:12px; font-weight:600; border:1px solid #BFDBFE; cursor:pointer;">
                                Ver detalle
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!traslados.length">
                        <td colspan="7" style="padding:24px; text-align:center; color:#94A3B8; font-size:13px;">
                            Aun no se han registrado traslados.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal Nuevo Traslado -->
        <div v-if="modalNuevo" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:center; justify-content:center; padding:20px;">
            <div style="background:white; border-radius:16px; padding:24px; width:100%; max-width:600px; max-height:90vh; overflow-y:auto;">
                <p style="font-size:20px; font-weight:800; color:#1E293B; margin:0 0 16px;">🚚 Nuevo Traslado de Mercaderia</p>

                <div style="display:grid; gap:12px; margin-bottom:16px;">
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Local destino *</label>
                        <input v-model="form.local_destino" type="text" placeholder="Ej: Local Centro"
                            style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#64748B;">Quien lo lleva (opcional)</label>
                            <input v-model="form.transportista" type="text" placeholder="Nombre de la persona"
                                style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                        </div>
                        <div>
                            <label style="font-size:13px; font-weight:600; color:#64748B;">Placa vehiculo (opcional)</label>
                            <input v-model="form.placa_vehiculo" type="text" placeholder="Ej: ABC-123"
                                style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                        </div>
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Observaciones (opcional)</label>
                        <input v-model="form.observaciones" type="text" placeholder="Notas adicionales"
                            style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                    </div>
                </div>

                <div style="border-top:1px solid #E2E8F0; padding-top:16px;">
                    <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 10px;">Productos a trasladar</p>

                    <div style="display:flex; gap:8px; margin-bottom:10px;">
                        <input v-model="busqueda" type="text" placeholder="Buscar producto por nombre o codigo..."
                            style="flex:1; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box;" />
                    </div>

                    <div v-if="busqueda && productosFiltrados.length" style="max-height:160px; overflow-y:auto; border:1px solid #E2E8F0; border-radius:10px; margin-bottom:10px;">
                        <div v-for="p in productosFiltrados" :key="p.id" @click="agregarItem(p)"
                            style="padding:8px 12px; cursor:pointer; border-bottom:1px solid #F1F5F9; display:flex; justify-content:space-between;">
                            <span style="font-size:13px; color:#1E293B;">{{ p.descripcion }}</span>
                            <span style="font-size:12px; color:#94A3B8;">Stock: {{ p.stock_actual }}</span>
                        </div>
                    </div>

                    <div v-if="form.items.length" style="display:grid; gap:8px; margin-bottom:10px;">
                        <div v-for="(item, idx) in form.items" :key="item.producto_id"
                            style="display:flex; align-items:center; justify-content:space-between; padding:10px 12px; background:#F8FAFC; border-radius:8px; border:1px solid #E2E8F0;">
                            <p style="font-size:13px; color:#1E293B; margin:0; flex:1;">{{ item.descripcion }}</p>
                            <input v-model.number="item.cantidad" type="number" min="0.01" step="0.01"
                                style="width:80px; padding:6px 8px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; outline:none; margin-right:8px;" />
                            <button type="button" @click="quitarItem(idx)"
                                style="padding:4px 8px; background:#FEF2F2; color:#991B1B; border-radius:6px; font-size:11px; font-weight:600; border:1px solid #FECACA; cursor:pointer;">
                                Quitar
                            </button>
                        </div>
                    </div>
                    <p v-else style="font-size:12px; color:#94A3B8; margin:0 0 10px;">Busca y agrega los productos que vas a trasladar.</p>
                </div>

                <div style="display:flex; gap:12px; margin-top:20px;">
                    <button @click="cerrarModal"
                        style="flex:1; padding:12px; background:white; color:#64748B; border-radius:10px; font-size:14px; font-weight:600; border:2px solid #E2E8F0; cursor:pointer;">
                        Cancelar
                    </button>
                    <button @click="guardarTraslado"
                        style="flex:1; padding:12px; background:linear-gradient(135deg,#0EA5E9,#0369A1); color:white; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                        Confirmar Traslado
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Ver Detalle -->
        <div v-if="trasladoDetalle" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:center; justify-content:center; padding:20px;">
            <div style="background:white; border-radius:16px; padding:24px; width:100%; max-width:500px; max-height:90vh; overflow-y:auto;">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 4px;">🚚 Traslado a {{ trasladoDetalle.local_destino }}</p>
                <p style="font-size:13px; color:#94A3B8; margin:0 0 16px;">{{ formatearFecha(trasladoDetalle.created_at) }}</p>

                <div style="display:grid; gap:6px; margin-bottom:16px; font-size:13px; color:#475569;">
                    <p style="margin:0;"><strong>Registrado por:</strong> {{ trasladoDetalle.usuario?.name || '-' }}</p>
                    <p style="margin:0;"><strong>Transportista:</strong> {{ trasladoDetalle.transportista || '-' }}</p>
                    <p style="margin:0;"><strong>Placa:</strong> {{ trasladoDetalle.placa_vehiculo || '-' }}</p>
                    <p v-if="trasladoDetalle.observaciones" style="margin:0;"><strong>Notas:</strong> {{ trasladoDetalle.observaciones }}</p>
                </div>

                <div style="border-top:1px solid #E2E8F0; padding-top:12px;">
                    <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0 0 8px;">Productos trasladados</p>
                    <div v-for="d in trasladoDetalle.detalle" :key="d.id"
                        style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #F1F5F9; font-size:13px;">
                        <span style="color:#1E293B;">{{ d.producto?.descripcion || 'Producto eliminado' }}</span>
                        <span style="color:#64748B;">{{ d.cantidad }} unid.</span>
                    </div>
                </div>

                <button @click="trasladoDetalle = null"
                    style="width:100%; margin-top:20px; padding:12px; background:white; color:#64748B; border-radius:10px; font-size:14px; font-weight:600; border:2px solid #E2E8F0; cursor:pointer;">
                    Cerrar
                </button>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    productos: { type: Array, default: () => [] },
    traslados: { type: Array, default: () => [] },
})

const modalNuevo = ref(false)
const busqueda = ref('')
const trasladoDetalle = ref(null)

const form = ref({
    local_destino: '',
    transportista: '',
    placa_vehiculo: '',
    observaciones: '',
    items: [],
})

const productosFiltrados = computed(() => {
    if (!busqueda.value) return []
    const q = busqueda.value.toLowerCase()
    return props.productos
        .filter(p =>
            p.descripcion.toLowerCase().includes(q) ||
            (p.codigo && p.codigo.toLowerCase().includes(q)) ||
            (p.codigo_barras && p.codigo_barras.includes(q))
        )
        .filter(p => !form.value.items.some(i => i.producto_id === p.id))
        .slice(0, 8)
})

const agregarItem = (p) => {
    form.value.items.push({
        producto_id: p.id,
        descripcion: p.descripcion,
        cantidad: 1,
    })
    busqueda.value = ''
}

const quitarItem = (idx) => {
    form.value.items.splice(idx, 1)
}

const cerrarModal = () => {
    modalNuevo.value = false
    busqueda.value = ''
    form.value = { local_destino: '', transportista: '', placa_vehiculo: '', observaciones: '', items: [] }
}

const guardarTraslado = () => {
    if (!form.value.local_destino.trim()) {
        alert('Falta indicar el local destino')
        return
    }
    if (!form.value.items.length) {
        alert('Agrega al menos un producto para trasladar')
        return
    }
    for (const item of form.value.items) {
        if (!item.cantidad || item.cantidad <= 0) {
            alert('Revisa la cantidad de "' + item.descripcion + '", debe ser mayor a 0')
            return
        }
    }

    router.post('/minimarket/traslados', form.value, {
        onSuccess: () => {
            window.location.replace('/minimarket/traslados')
        },
        onError: (errors) => {
            alert('No se pudo registrar el traslado: ' + Object.values(errors).join(' '))
        }
    })
}

const verDetalle = (t) => {
    trasladoDetalle.value = t
}

const formatearFecha = (fecha) => {
    if (!fecha) return '-'
    const d = new Date(fecha)
    return d.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' +
        d.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })
}
</script>
