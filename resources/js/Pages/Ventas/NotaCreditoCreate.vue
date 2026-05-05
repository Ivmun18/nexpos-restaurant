<template>
    <AppLayout :title="`Nota de crédito — ${venta.numero_completo}`" subtitle="Emitir nota de crédito">

        <div style="display:grid; grid-template-columns:1fr 320px; gap:1.5rem; align-items:start;">

            <!-- Panel izquierdo -->
            <div>

                <!-- Documento de referencia -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem; margin-bottom:1rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Documento de referencia</p>
                    <div style="background:#F8FAFC; border-radius:8px; padding:12px 16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between;">
                            <div>
                                <p style="font-size:15px; font-weight:700; color:#1E293B; margin:0; font-family:monospace;">{{ venta.numero_completo }}</p>
                                <p style="font-size:12px; color:#94A3B8; margin:4px 0 0;">
                                    {{ venta.cliente_razon_social || 'Clientes varios' }} ·
                                    {{ venta.fecha_emision }}
                                </p>
                            </div>
                            <p style="font-size:18px; font-weight:700; color:#2563EB; margin:0;">S/ {{ Number(venta.total).toFixed(2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Motivo -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem; margin-bottom:1rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Motivo de la nota de crédito</p>

                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem;">
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Código motivo *</label>
                            <select v-model="form.motivo_codigo" @change="actualizarMotivo"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; background:white;">
                                <option v-for="(desc, cod) in motivos" :key="cod" :value="cod">
                                    {{ cod }} — {{ desc }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Descripción del motivo *</label>
                            <input v-model="form.motivo_descripcion" type="text"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box;"/>
                        </div>
                    </div>
                </div>

                <!-- Items -->
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden;">
                    <div style="padding:14px 20px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between;">
                        <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0;">Ítems a acreditar</p>
                        <button @click="cargarTodosItems"
                            style="padding:7px 14px; background:#EFF6FF; color:#2563EB; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                            Cargar todos
                        </button>
                    </div>

                    <div v-if="form.items.length === 0" style="padding:2rem; text-align:center; color:#94A3B8; font-size:13px;">
                        Haz clic en "Cargar todos" o agrega ítems manualmente
                    </div>

                    <div v-for="(item, i) in form.items" :key="i"
                        style="padding:12px 16px; border-bottom:1px solid #F1F5F9; display:flex; align-items:center; gap:12px;">
                        <div style="flex:1;">
                            <p style="font-size:13px; font-weight:500; color:#1E293B; margin:0;">{{ item.descripcion }}</p>
                            <p style="font-size:11px; color:#94A3B8; margin:3px 0 0;">{{ item.unidad_medida }} · {{ item.tipo_afectacion_igv === '10' ? 'Gravado' : 'Exonerado' }}</p>
                        </div>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <div>
                                <label style="font-size:10px; color:#94A3B8; display:block; margin-bottom:2px;">Cantidad</label>
                                <input v-model="item.cantidad" type="number" step="0.001" min="0.001"
                                    @input="calcularItem(i)"
                                    style="width:80px; padding:7px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; font-weight:600; color:#1E293B; outline:none; text-align:center;"/>
                            </div>
                            <div>
                                <label style="font-size:10px; color:#94A3B8; display:block; margin-bottom:2px;">Precio</label>
                                <input v-model="item.precio_unitario" type="number" step="0.01"
                                    @input="calcularItem(i)"
                                    style="width:90px; padding:7px; border:1px solid #E2E8F0; border-radius:6px; font-size:13px; color:#1E293B; outline:none; text-align:right;"/>
                            </div>
                        </div>
                        <div style="text-align:right; min-width:80px;">
                            <p style="font-size:15px; font-weight:700; color:#1E293B; margin:0;">S/ {{ Number(item.total).toFixed(2) }}</p>
                        </div>
                        <button @click="quitarItem(i)"
                            style="background:#FEF2F2; color:#991B1B; border:none; border-radius:6px; padding:6px 10px; font-size:12px; cursor:pointer;">✕</button>
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
                        <span style="font-size:15px; font-weight:700; color:#1E293B;">Total NC</span>
                        <span style="font-size:22px; font-weight:700; color:#2563EB;">S/ {{ totales.total.toFixed(2) }}</span>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Observaciones</label>
                        <textarea v-model="form.observaciones" rows="2"
                            style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; color:#1E293B; outline:none; box-sizing:border-box; resize:none;"></textarea>
                    </div>

                    <p v-if="error" style="color:#991B1B; font-size:12px; margin-bottom:10px;">{{ error }}</p>

                    <button @click="guardar" :disabled="procesando || form.items.length === 0"
                        style="width:100%; padding:13px; background:#2563EB; color:white; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer;"
                        :style="form.items.length === 0 ? {opacity:'0.5'} : {opacity:'1'}">
                        {{ procesando ? 'Procesando...' : 'Emitir nota de crédito' }}
                    </button>

                    <a href="/notas-credito"
                        style="display:block; text-align:center; margin-top:10px; font-size:13px; color:#94A3B8; text-decoration:none;">
                        Cancelar
                    </a>
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
    venta:   Object,
    motivos: Object,
})

const procesando = ref(false)
const error      = ref('')

const form = ref({
    motivo_codigo:      '01',
    motivo_descripcion: 'Anulación de la operación',
    observaciones:      '',
    items:              [],
})

const actualizarMotivo = () => {
    form.value.motivo_descripcion = props.motivos[form.value.motivo_codigo] || ''
}

const cargarTodosItems = () => {
    form.value.items = props.venta.detalle.map(item => ({
        producto_id:         item.producto_id,
        descripcion:         item.descripcion,
        unidad_medida:       item.unidad_medida,
        tipo_afectacion_igv: item.tipo_afectacion_igv,
        cantidad:            parseFloat(item.cantidad),
        precio_unitario:     parseFloat(item.precio_unitario),
        valor_unitario:      parseFloat(item.valor_unitario),
        total_valor_venta:   parseFloat(item.total_valor_venta),
        total_igv:           parseFloat(item.total_igv),
        total:               parseFloat(item.total),
    }))
}

const calcularItem = (i) => {
    const item     = form.value.items[i]
    const cantidad = parseFloat(item.cantidad) || 0
    const precio   = parseFloat(item.precio_unitario) || 0
    const afecto   = item.tipo_afectacion_igv === '10'

    item.valor_unitario    = afecto ? round(precio / 1.18, 4) : precio
    item.total_valor_venta = round(item.valor_unitario * cantidad, 2)
    item.total_igv         = afecto ? round(item.total_valor_venta * 0.18, 2) : 0
    item.total             = round(precio * cantidad, 2)
}

const quitarItem = (i) => form.value.items.splice(i, 1)

const round = (val, dec) => Math.round(val * Math.pow(10, dec)) / Math.pow(10, dec)

const totales = computed(() => {
    let gravado = 0, exonerado = 0, igv = 0, total = 0
    form.value.items.forEach(item => {
        if (item.tipo_afectacion_igv === '10') {
            gravado += parseFloat(item.total_valor_venta) || 0
            igv     += parseFloat(item.total_igv) || 0
        } else {
            exonerado += parseFloat(item.total_valor_venta) || 0
        }
        total += parseFloat(item.total) || 0
    })
    return {
        gravado:   round(gravado, 2),
        exonerado: round(exonerado, 2),
        igv:       round(igv, 2),
        total:     round(total, 2),
    }
})

const guardar = () => {
    error.value = ''
    if (form.value.items.length === 0) {
        error.value = 'Agrega al menos un ítem.'
        return
    }
    procesando.value = true
    router.post(`/ventas/${props.venta.id}/nota-credito`, form.value, {
        onError: () => {
            error.value = 'Error al emitir. Verifica los datos.'
            procesando.value = false
        }
    })
}
</script>