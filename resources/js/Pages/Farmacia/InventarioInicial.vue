<template>
    <AppLayout title="📦 Stock Inicial" subtitle="Escanea cada producto y ajusta stock, lote y vencimiento">
        <div style="padding:24px; max-width:900px; margin:0 auto;">

            <!-- Stats header -->
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px;">
                <div style="background:linear-gradient(135deg,#0ea5e9,#0369a1); border-radius:16px; padding:20px; color:white;">
                    <p style="margin:0 0 8px; font-size:13px; opacity:0.85;">📊 Productos en BD</p>
                    <p style="margin:0; font-size:32px; font-weight:800;">{{ total_productos }}</p>
                </div>
                <div style="background:linear-gradient(135deg,#16a34a,#14532d); border-radius:16px; padding:20px; color:white;">
                    <p style="margin:0 0 8px; font-size:13px; opacity:0.85;">✅ Ajustados hoy</p>
                    <p style="margin:0; font-size:32px; font-weight:800;">{{ ajustadosHoyLocal }}</p>
                </div>
                <div style="background:linear-gradient(135deg,#7c3aed,#5b21b6); border-radius:16px; padding:20px; color:white;">
                    <p style="margin:0 0 8px; font-size:13px; opacity:0.85;">⏳ Por ajustar</p>
                    <p style="margin:0; font-size:32px; font-weight:800;">{{ Math.max(0, total_productos - ajustadosHoyLocal) }}</p>
                </div>
            </div>

            <!-- Scanner -->
            <div style="background:white; border-radius:16px; border:2px solid #14B8A6; padding:24px; margin-bottom:24px;">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
                    <span style="font-size:28px;">🔍</span>
                    <div>
                        <p style="margin:0; font-size:16px; font-weight:700; color:#0F766E;">Escanea código de barras</p>
                        <p style="margin:2px 0 0; font-size:12px; color:#64748B;">El lector USB simula teclado. También puedes tipear y dar Enter.</p>
                    </div>
                </div>
                <input
                    ref="scannerInput"
                    v-model="codigoScan"
                    @keyup.enter="buscarProducto"
                    type="text"
                    placeholder="Apunta y escanea o tipea código..."
                    style="width:100%; padding:16px 20px; font-size:18px; border:2px solid #14B8A6; border-radius:12px; outline:none; font-family:monospace; background:#F0FDFA;"
                    autofocus
                />
                <p v-if="buscando" style="margin:8px 0 0; font-size:12px; color:#0EA5E9;">⏳ Buscando producto...</p>
                <p v-else-if="errorBusqueda" style="margin:8px 0 0; font-size:12px; color:#DC2626;">❌ {{ errorBusqueda }}</p>
            </div>

            <!-- Producto escaneado -->
            <div v-if="producto" style="background:white; border-radius:16px; border:1px solid #E2E8F0; overflow:hidden;">
                <div style="background:linear-gradient(135deg,#14B8A6,#0F766E); padding:20px 24px; color:white;">
                    <p style="margin:0; font-size:18px; font-weight:700;">{{ producto.descripcion }}</p>
                    <p style="margin:4px 0 0; font-size:12px; opacity:0.9;">
                        {{ producto.codigo_barras || 'Sin código' }}
                        <span v-if="producto.principio_activo"> · 💊 {{ producto.principio_activo }}</span>
                        <span v-if="producto.laboratorio"> · 🏭 {{ producto.laboratorio }}</span>
                    </p>
                </div>

                <div style="padding:24px;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                        <!-- Stock -->
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#64748B; margin-bottom:4px; display:block;">
                                📦 Stock actual: <strong style="color:#1E293B;">{{ producto.stock_actual }}</strong> →
                            </label>
                            <input
                                ref="stockInput"
                                v-model.number="form.stock_nuevo"
                                type="number"
                                min="0"
                                style="width:100%; padding:12px 14px; border:1px solid #CBD5E1; border-radius:10px; font-size:16px; font-weight:600; color:#1E293B; outline:none;"
                                @keyup.enter="enfocarLote"
                            />
                        </div>
                        <!-- Precio venta -->
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#64748B; margin-bottom:4px; display:block;">
                                💰 Precio venta: <strong style="color:#1E293B;">S/ {{ Number(producto.precio_venta).toFixed(2) }}</strong> →
                            </label>
                            <input
                                v-model.number="form.precio_venta"
                                type="number"
                                step="0.01"
                                min="0"
                                style="width:100%; padding:12px 14px; border:1px solid #CBD5E1; border-radius:10px; font-size:16px; color:#1E293B; outline:none;"
                            />
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                        <!-- Lote -->
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#64748B; margin-bottom:4px; display:block;">
                                🏷️ Lote: <strong style="color:#1E293B;">{{ producto.lote || '—' }}</strong> →
                            </label>
                            <input
                                ref="loteInput"
                                v-model="form.lote"
                                type="text"
                                placeholder="L-2026-XXX"
                                style="width:100%; padding:12px 14px; border:1px solid #CBD5E1; border-radius:10px; font-size:14px; color:#1E293B; outline:none; font-family:monospace;"
                            />
                        </div>
                        <!-- Vencimiento -->
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#64748B; margin-bottom:4px; display:block;">
                                📅 Vence: <strong style="color:#1E293B;">{{ producto.fecha_vencimiento?.slice(0,10) || '—' }}</strong> →
                            </label>
                            <input
                                v-model="form.fecha_vencimiento"
                                type="date"
                                style="width:100%; padding:12px 14px; border:1px solid #CBD5E1; border-radius:10px; font-size:14px; color:#1E293B; outline:none;"
                            />
                        </div>
                    </div>

                    <div style="display:flex; gap:12px;">
                        <button @click="cancelar"
                            style="flex:1; padding:14px; background:#F1F5F9; color:#475569; border:none; border-radius:10px; font-size:14px; font-weight:600; cursor:pointer;">
                            ✕ Cancelar
                        </button>
                        <button @click="guardar"
                            :disabled="guardando"
                            style="flex:2; padding:14px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer;">
                            {{ guardando ? '⏳ Guardando...' : '💾 Guardar y siguiente (Enter)' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sin producto: instrucciones -->
            <div v-else-if="!buscando && !errorBusqueda" style="text-align:center; padding:40px 0; color:#94A3B8;">
                <p style="font-size:60px; margin:0;">📦</p>
                <p style="font-size:14px; margin:8px 0 0;">Apunta el scanner a un código de barras o tipéalo arriba</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, nextTick, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    ajustados_hoy:   { type: Number, default: 0 },
    total_productos: { type: Number, default: 0 },
})

const ajustadosHoyLocal = ref(props.ajustados_hoy)
const codigoScan      = ref('')
const producto        = ref(null)
const buscando        = ref(false)
const errorBusqueda   = ref('')
const guardando       = ref(false)
const scannerInput    = ref(null)
const stockInput      = ref(null)
const loteInput       = ref(null)

const form = ref({
    stock_nuevo: 0,
    lote: '',
    fecha_vencimiento: '',
    precio_venta: 0,
    precio_compra: 0,
})

const getCsrf = () => {
    const meta = document.querySelector('meta[name="csrf-token"]')
    return meta ? meta.getAttribute('content') : ''
}

const enfocarScanner = () => {
    nextTick(() => scannerInput.value?.focus())
}

const enfocarLote = () => {
    nextTick(() => loteInput.value?.focus())
}

const buscarProducto = async () => {
    const cod = codigoScan.value.trim()
    if (!cod) return
    buscando.value = true
    errorBusqueda.value = ''
    producto.value = null

    try {
        const res = await fetch('/farmacia/inventario-inicial/buscar', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrf(),
                'X-XSRF-TOKEN': getCsrf()
            },
            body: JSON.stringify({ codigo: cod })
        })
        const data = await res.json()
        if (res.ok && data.success) {
            producto.value = data.producto
            form.value.stock_nuevo       = data.producto.stock_actual
            form.value.lote              = data.producto.lote || ''
            form.value.fecha_vencimiento = data.producto.fecha_vencimiento?.slice(0,10) || ''
            form.value.precio_venta      = Number(data.producto.precio_venta) || 0
            form.value.precio_compra     = Number(data.producto.precio_compra) || 0
            // Foco al stock para escribir cantidad directo
            nextTick(() => stockInput.value?.select())
        } else {
            errorBusqueda.value = data.message || 'Producto no encontrado'
            enfocarScanner()
        }
    } catch(e) {
        errorBusqueda.value = 'Error de conexión: ' + e.message
    }
    buscando.value = false
}

const guardar = async () => {
    if (!producto.value || guardando.value) return
    guardando.value = true

    try {
        const res = await fetch('/farmacia/inventario-inicial/actualizar', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrf(),
                'X-XSRF-TOKEN': getCsrf()
            },
            body: JSON.stringify({
                producto_id:       producto.value.id,
                stock_nuevo:       form.value.stock_nuevo,
                lote:              form.value.lote || null,
                fecha_vencimiento: form.value.fecha_vencimiento || null,
                precio_venta:      form.value.precio_venta || null,
                precio_compra:     form.value.precio_compra || null,
            })
        })
        const data = await res.json()
        if (res.ok && data.success) {
            ajustadosHoyLocal.value++
            // Reset y vuelve a scanner
            producto.value = null
            codigoScan.value = ''
            enfocarScanner()
        } else {
            alert('❌ Error: ' + (data.message || data.error || 'Desconocido'))
        }
    } catch(e) {
        alert('❌ Error: ' + e.message)
    }
    guardando.value = false
}

const cancelar = () => {
    producto.value = null
    codigoScan.value = ''
    enfocarScanner()
}

// Atajo: Enter en cualquier parte del producto guarda
watch(producto, (v) => {
    if (v) {
        const handler = (e) => {
            if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {
                if (e.target === stockInput.value || e.target === loteInput.value) return
                e.preventDefault()
                guardar()
            }
        }
        window.addEventListener('keydown', handler)
        return () => window.removeEventListener('keydown', handler)
    }
})

// Iniciar con foco al scanner
nextTick(() => scannerInput.value?.focus())
</script>
