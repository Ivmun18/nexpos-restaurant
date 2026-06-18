<template>
    <AppLayout title="Productos" subtitle="Gestión de inventario">

        <!-- Header -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
            <div>
                <p style="font-size:24px; font-weight:800; color:#1E293B; margin:0;">📦 Productos</p>
                <p style="font-size:14px; color:#94A3B8; margin:4px 0 0;">{{ productos.length }} productos registrados</p>
            </div>
            <button @click="modalNuevo = true"
                style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                + Nuevo Producto
            </button>
        </div>

        <!-- Alertas stock bajo -->
        <div v-if="stockBajo.length" style="background:#FEF2F2; border:1px solid #FECACA; border-radius:14px; padding:16px 20px; margin-bottom:20px; display:flex; align-items:center; gap:12px;">
            <span style="font-size:24px;">⚠️</span>
            <p style="font-size:14px; font-weight:600; color:#991B1B; margin:0;">
                {{ stockBajo.length }} producto(s) con stock bajo: {{ stockBajo.map(p => p.descripcion).join(', ') }}
            </p>
        </div>

        <!-- Buscador -->
        <div style="margin-bottom:16px;">
            <input v-model="busqueda" placeholder="🔍 Buscar producto..."
                style="width:100%; max-width:400px; padding:10px 16px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box;"
                @focus="$event.target.style.borderColor='#14B8A6'"
                @blur="$event.target.style.borderColor='#E2E8F0'" />
        </div>

        <!-- Tabla -->
        <div style="background:white; border-radius:20px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06); overflow:hidden;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:14px 20px; text-align:left; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Producto</th>
                        <th style="padding:14px 20px; text-align:left; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Código</th>
                        <th style="padding:14px 20px; text-align:right; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">P. Compra</th>
                        <th style="padding:14px 20px; text-align:right; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">P. Venta</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Stock</th>
                        <th style="padding:14px 20px; text-align:center; font-size:12px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!productosFiltrados.length">
                        <td colspan="6" style="text-align:center; padding:60px; color:#CBD5E1;">
                            <p style="font-size:40px; margin:0 0 8px;">📦</p>
                            <p style="font-size:15px;">Sin productos</p>
                        </td>
                    </tr>
                    <tr v-for="p in productosFiltrados" :key="p.id"
                        style="border-top:1px solid #F1F5F9;"
                        @mouseenter="e => e.currentTarget.style.background='#F8FAFC'"
                        @mouseleave="e => e.currentTarget.style.background='white'">
                        <td style="padding:14px 20px;">
                            <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ p.descripcion }}</p>
                            <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ p.codigo_barras || 'Sin código de barras' }}</p>
                            <span v-if="p.categoria" :style="{ padding: '2px 8px', borderRadius: '20px', fontSize: '11px', fontWeight: '600', background: p.categoria.color + '22', color: p.categoria.color }">{{ p.categoria.icono }} {{ p.categoria.nombre }}</span>
                        </td>
                        <td style="padding:14px 20px; font-size:14px; color:#475569;">{{ p.codigo }}</td>
                        <td style="padding:14px 20px; text-align:right; font-size:14px; color:#475569;">S/ {{ Number(p.precio_compra).toFixed(2) }}</td>
                        <td style="padding:14px 20px; text-align:right; font-size:15px; font-weight:700; color:#14B8A6;">S/ {{ Number(p.precio_venta).toFixed(2) }}</td>
                        <td style="padding:14px 20px; text-align:center;">
                            <span :style="{
                                padding: '4px 12px', borderRadius: '20px', fontSize: '13px', fontWeight: '700',
                                background: p.stock_actual <= (p.stock_minimo || 3) ? '#FEF2F2' : '#F0FDF4',
                                color: p.stock_actual <= (p.stock_minimo || 3) ? '#991B1B' : '#166534',
                            }">{{ p.stock_actual }}</span>
                        </td>
                        <td style="padding:14px 20px; text-align:center;">
                            <div style="display:flex; gap:8px; justify-content:center;">
                                <button @click="editarProducto(p)"
                                    style="padding:6px 12px; background:#F0FDFA; color:#0F766E; border-radius:8px; font-size:12px; font-weight:600; border:1px solid #CCFBF1; cursor:pointer;">
                                    ✏️ Editar
                                </button>
                                <button @click="ajustarStock(p)"
                                    style="padding:6px 12px; background:#EFF6FF; color:#1D4ED8; border-radius:8px; font-size:12px; font-weight:600; border:1px solid #BFDBFE; cursor:pointer;">
                                    📦 Stock
                                </button>
                                <button @click="abrirPresentaciones(p)"
                                    style="padding:6px 12px; background:#FDF4FF; color:#A21CAF; border-radius:8px; font-size:12px; font-weight:600; border:1px solid #F5D0FE; cursor:pointer;">
                                    📐 Presentaciones
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

       <!-- Modal Nuevo/Editar Producto -->
<Teleport to="body">
    <div v-if="modalNuevo || modalEditar" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:center; justify-content:center;">
        <div style="background:white; border-radius:20px; padding:32px; width:100%; max-width:500px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <p style="font-size:20px; font-weight:800; color:#1E293B; margin:0 0 24px;">
                {{ modalEditar ? '✏️ Editar Producto' : '+ Nuevo Producto' }}
            </p>

            <div style="display:grid; gap:14px;">
                <div>
                    <label style="font-size:13px; font-weight:600; color:#64748B;">Descripción *</label>
                    <input v-model="form.descripcion" style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                <div>
                    <label style="font-size:13px; font-weight:600; color:#64748B;">Categoría</label>
                    <select v-model="form.categoria_id" style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;">
                        <option value="">Sin categoría</option>
                        <option v-for="cat in categorias" :key="cat.id" :value="cat.id">{{ cat.icono }} {{ cat.nombre }}</option>
                    </select>
                </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Código *</label>
                        <input v-model="form.codigo" style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#64748B;">
                            Código de barras
                            <span v-if="escaneando" style="margin-left:8px; color:#14B8A6; font-size:11px;">📷 Esperando escaneo...</span>
                        </label>
                        <div style="position:relative;">
                            <input
                                ref="inputCodigoBarras"
                                v-model="form.codigo_barras"
                                style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;"
                                :style="escaneando ? {border: '2px solid #14B8A6'} : {}"
                                @keyup.enter="onCodigoBarrasEnter"
                                placeholder="Escanea o escribe el código"
                            />
                            <button
                                type="button"
                                @click="activarEscaneo"
                                :style="escaneando
                                    ? 'position:absolute; right:8px; top:50%; transform:translateY(-50%); background:#14B8A6; color:white; border:none; border-radius:6px; padding:4px 8px; font-size:12px; cursor:pointer; margin-top:2px;'
                                    : 'position:absolute; right:8px; top:50%; transform:translateY(-50%); background:#f1f5f9; color:#475569; border:none; border-radius:6px; padding:4px 8px; font-size:12px; cursor:pointer; margin-top:2px;'"
                                title="Activar lector de barras">
                                📷
                            </button>
                        </div>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Precio compra</label>
                        <input v-model="form.precio_compra" type="number" step="0.01" style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Precio venta *</label>
                        <input v-model="form.precio_venta" type="number" step="0.01" style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div v-if="!modalEditar">
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Stock inicial *</label>
                        <input v-model="form.stock_actual" type="number" style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Stock mínimo</label>
                        <input v-model="form.stock_minimo" type="number" style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                    </div>
                </div>
            </div>

            <div style="border:2px solid #F3E8FF; background:#FAF5FF; border-radius:12px; margin-top:16px; padding:16px;">
                <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 4px;">📐 Presentaciones (paquetes/packs)</p>
                <p style="font-size:12px; color:#94A3B8; margin:0 0 12px;">Para vender este producto tambien en paquetes, packs o cajas. Si solo lo vendes suelto, no agregues nada aqui. Esta seccion tiene su propio boton de guardado, separado del boton "Actualizar" del producto.</p>

                <div v-if="modalEditar && productoSeleccionado?.presentaciones?.length" style="display:grid; gap:8px; margin-bottom:14px;">
                    <div v-for="pres in productoSeleccionado.presentaciones" :key="pres.id"
                        style="display:flex; align-items:center; justify-content:space-between; padding:10px 12px; background:#F8FAFC; border-radius:8px; border:1px solid #E2E8F0;">
                        <p style="font-size:13px; color:#1E293B; margin:0;">
                            <strong>{{ pres.nombre }}</strong> — 1 {{ pres.nombre }} = {{ pres.factor_conversion }} unidad base · S/ {{ Number(pres.precio_venta).toFixed(2) }}
                            <span v-if="pres.es_default" style="font-size:10px; background:#DCFCE7; color:#166534; padding:2px 6px; border-radius:8px; margin-left:6px;">default</span>
                        </p>
                        <div style="display:flex; gap:6px;">
                            <button type="button" @click="editarPresentacionLocal(pres)"
                                style="padding:4px 8px; background:#F0FDFA; color:#0F766E; border-radius:6px; font-size:11px; font-weight:600; border:1px solid #CCFBF1; cursor:pointer;">
                                Editar
                            </button>
                            <button type="button" @click="eliminarPresentacion(pres)"
                                style="padding:4px 8px; background:#FEF2F2; color:#991B1B; border-radius:6px; font-size:11px; font-weight:600; border:1px solid #FECACA; cursor:pointer;">
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="!modalEditar && form.presentaciones.length" style="display:grid; gap:8px; margin-bottom:14px;">
                    <div v-for="(p, idx) in form.presentaciones" :key="idx"
                        style="display:flex; align-items:center; justify-content:space-between; padding:10px 12px; background:#F8FAFC; border-radius:8px; border:1px solid #E2E8F0;">
                        <p style="font-size:13px; color:#1E293B; margin:0;">
                            <strong>{{ p.nombre }}</strong> — 1 {{ p.nombre }} = {{ p.factor_conversion }} unidad base · S/ {{ Number(p.precio_venta).toFixed(2) }}
                            <span v-if="p.es_default" style="font-size:10px; background:#DCFCE7; color:#166534; padding:2px 6px; border-radius:8px; margin-left:6px;">default</span>
                        </p>
                        <button type="button" @click="quitarPresentacionLocal(idx)"
                            style="padding:4px 8px; background:#FEF2F2; color:#991B1B; border-radius:6px; font-size:11px; font-weight:600; border:1px solid #FECACA; cursor:pointer;">
                            Quitar
                        </button>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <input v-model="nuevaPresentacion.nombre" placeholder="Nombre (ej: Pack x6, Saco 50kg)"
                        style="padding:8px 12px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    <select v-model="nuevaPresentacion.unidad_sunat"
                        style="padding:8px 12px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
                        <option value="NIU">Unidad (NIU)</option>
                        <option value="KGM">Kilogramo (KGM)</option>
                        <option value="SCO">Saco (SCO)</option>
                        <option value="CJ">Caja (CJ)</option>
                        <option value="DZN">Docena (DZN)</option>
                        <option value="BG">Bolsa (BG)</option>
                        <option value="LTR">Litro (LTR)</option>
                    </select>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <input v-model="nuevaPresentacion.factor_conversion" type="number" step="0.0001" placeholder="Cuantas unidades trae (ej: 6)"
                        style="padding:8px 12px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    <input v-model="nuevaPresentacion.precio_venta" type="number" step="0.01" placeholder="Precio venta"
                        style="padding:8px 12px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                </div>
                <p style="font-size:11px; color:#94A3B8; margin:0 0 10px;">Ejemplo: si vendes Coca Cola por unidad y quieres tambien venderla en pack de 6, escribe "Pack x6" y pon 6 aqui. Al vender 1 pack, se descontaran 6 unidades del stock.</p>
                <label style="display:flex; align-items:center; gap:6px; font-size:12px; color:#64748B; margin-bottom:10px;">
                    <input v-model="nuevaPresentacion.es_default" type="checkbox" />
                    Que se venda asi por defecto (en vez de por unidad)
                </label>
                <p v-if="presentacionEditandoId" style="font-size:11px; color:#A21CAF; font-weight:700; margin:0 0 6px; background:#F3E8FF; padding:6px 10px; border-radius:6px;">
                    ✏️ Editando "{{ nuevaPresentacion.nombre }}" — usa este boton morado para guardar el cambio (no el boton "Actualizar" de abajo)
                </p>
                <div style="display:flex; gap:8px;">
                    <button type="button" @click="agregarPresentacionLocal"
                        style="flex:1; padding:10px; background:linear-gradient(135deg,#A21CAF,#701A75); color:white; border-radius:8px; font-size:13px; font-weight:600; border:none; cursor:pointer;">
                        {{ presentacionEditandoId ? 'Guardar cambios del paquete' : '+ Agregar presentacion' }}
                    </button>
                    <button v-if="presentacionEditandoId" type="button" @click="cancelarEdicionPresentacion"
                        style="padding:10px 16px; background:white; color:#64748B; border-radius:8px; font-size:13px; font-weight:600; border:2px solid #E2E8F0; cursor:pointer;">
                        Cancelar
                    </button>
                </div>
            </div>

            <div style="display:flex; gap:12px; margin-top:24px;">
                <button @click="cerrarModales"
                    style="flex:1; padding:12px; background:white; color:#64748B; border-radius:10px; font-size:14px; font-weight:600; border:2px solid #E2E8F0; cursor:pointer;">
                    Cancelar
                </button>
               <button type="button" @click="guardar" style="flex:1; padding:12px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
    {{ modalEditar ? 'Actualizar' : 'Crear Producto' }}
</button>
            </div>
        </div>
    </div>
</Teleport>

<!-- Modal Ajustar Stock -->
<Teleport to="body">
    <div v-if="modalStock" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:center; justify-content:center;">
        <div style="background:white; border-radius:20px; padding:32px; width:100%; max-width:400px;">
            <p style="font-size:20px; font-weight:800; color:#1E293B; margin:0 0 8px;">📦 Ajustar Stock</p>
            <p style="font-size:14px; color:#94A3B8; margin:0 0 24px;">{{ productoSeleccionado?.descripcion }}</p>

            <div style="display:grid; gap:14px;">
                <div>
                    <label style="font-size:13px; font-weight:600; color:#64748B;">Tipo de movimiento</label>
                    <select v-model="formStock.tipo" style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;">
                        <option value="entrada">📥 Entrada (ej: compra nueva, devolucion de cliente)</option>
                        <option value="salida">📤 Salida (ej: traslado a otro local, producto dañado o vencido)</option>
                        <option value="ajuste">🔧 Ajuste (corregir el stock a un numero exacto)</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:13px; font-weight:600; color:#64748B;">Cantidad</label>
                    <input v-model="formStock.cantidad" type="number" min="1"
                        style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                </div>
                <div>
                    <label style="font-size:13px; font-weight:600; color:#64748B;">Motivo (recomendado)</label>
                    <input v-model="formStock.observaciones" type="text" placeholder="Ej: Traslado a Local Centro, Producto vencido, etc."
                        style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                    <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">Esto queda guardado en el historial para que puedas explicarle a tu jefe o socio por que cambio el stock.</p>
                </div>
            </div>

            <div style="display:flex; gap:12px; margin-top:24px;">
                <button @click="modalStock = false"
                    style="flex:1; padding:12px; background:white; color:#64748B; border-radius:10px; font-size:14px; font-weight:600; border:2px solid #E2E8F0; cursor:pointer;">
                    Cancelar
                </button>
                <button @click="guardarStock"
                    style="flex:1; padding:12px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</Teleport>

<!-- Modal Presentaciones -->
<Teleport to="body">
    <div v-if="modalPresentaciones" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:center; justify-content:center;">
        <div style="background:white; border-radius:20px; padding:32px; width:100%; max-width:560px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <p style="font-size:20px; font-weight:800; color:#1E293B; margin:0 0 4px;">📐 Presentaciones</p>
            <p style="font-size:13px; color:#94A3B8; margin:0 0 20px;">{{ productoSeleccionado?.descripcion }} — stock base: {{ productoSeleccionado?.stock_actual }}</p>

            <div v-if="productoSeleccionado?.presentaciones?.length" style="display:grid; gap:10px; margin-bottom:20px;">
                <div v-for="pres in productoSeleccionado.presentaciones" :key="pres.id"
                    style="display:flex; align-items:center; justify-content:space-between; padding:12px 14px; background:#F8FAFC; border-radius:10px; border:1px solid #E2E8F0;">
                    <div>
                        <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0;">
                            {{ pres.nombre }}
                            <span v-if="pres.es_default" style="font-size:10px; background:#DCFCE7; color:#166534; padding:2px 6px; border-radius:8px; margin-left:6px;">default</span>
                        </p>
                        <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">1 {{ pres.nombre }} = {{ pres.factor_conversion }} unidad base · S/ {{ Number(pres.precio_venta).toFixed(2) }}</p>
                    </div>
                    <button @click="eliminarPresentacion(pres)"
                        style="padding:6px 10px; background:#FEF2F2; color:#991B1B; border-radius:8px; font-size:12px; font-weight:600; border:1px solid #FECACA; cursor:pointer;">
                        Eliminar
                    </button>
                </div>
            </div>
            <p v-else style="font-size:13px; color:#94A3B8; margin:0 0 20px;">Sin presentaciones adicionales. El producto se vende solo por unidad base.</p>

            <div style="border-top:1px solid #E2E8F0; padding-top:16px;">
                <p style="font-size:14px; font-weight:700; color:#1E293B; margin:0 0 12px;">+ Agregar presentación</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Nombre</label>
                        <input v-model="formPresentacion.nombre" placeholder="Ej: Saco 50kg"
                            style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Unidad SUNAT</label>
                        <select v-model="formPresentacion.unidad_sunat"
                            style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;">
                            <option value="NIU">Unidad (NIU)</option>
                            <option value="KGM">Kilogramo (KGM)</option>
                            <option value="SCO">Saco (SCO)</option>
                            <option value="CJ">Caja (CJ)</option>
                            <option value="DZN">Docena (DZN)</option>
                            <option value="BG">Bolsa (BG)</option>
                            <option value="LTR">Litro (LTR)</option>
                        </select>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Equivale a (en unidad base)</label>
                        <input v-model="formPresentacion.factor_conversion" type="number" step="0.0001" placeholder="Ej: 50"
                            style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Precio de venta</label>
                        <input v-model="formPresentacion.precio_venta" type="number" step="0.01" placeholder="0.00"
                            style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                    </div>
                </div>
                <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#64748B; margin-bottom:16px;">
                    <input v-model="formPresentacion.es_default" type="checkbox" />
                    Usar como presentacion por defecto en el POS
                </label>
                <button @click="guardarPresentacion"
                    style="width:100%; padding:12px; background:linear-gradient(135deg,#A21CAF,#701A75); color:white; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                    Agregar presentacion
                </button>
            </div>

            <div style="display:flex; margin-top:20px;">
                <button @click="modalPresentaciones = false"
                    style="flex:1; padding:12px; background:white; color:#64748B; border-radius:10px; font-size:14px; font-weight:600; border:2px solid #E2E8F0; cursor:pointer;">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</Teleport>

    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    productos: { type: Array, default: () => [] },
    categorias: { type: Array, default: () => [] },
})

const busqueda    = ref('')
const modalNuevo  = ref(false)
const modalEditar = ref(false)
const modalStock  = ref(false)
const modalPresentaciones = ref(false)
const formPresentacion = ref({ nombre: '', unidad_sunat: 'NIU', factor_conversion: 1, precio_venta: '', es_default: false })
const productoSeleccionado = ref(null)

const form = ref({
    descripcion: '', codigo: '', codigo_barras: '',
    precio_compra: '', precio_venta: '',
    stock_actual: 0, stock_minimo: 0,
    presentaciones: [],
})

const nuevaPresentacion = ref({ nombre: '', unidad_sunat: 'NIU', factor_conversion: '', precio_venta: '', es_default: false })
const presentacionEditandoId = ref(null)

const editarPresentacionLocal = (pres) => {
    presentacionEditandoId.value = pres.id
    nuevaPresentacion.value = {
        nombre: pres.nombre,
        unidad_sunat: pres.unidad_sunat,
        factor_conversion: pres.factor_conversion,
        precio_venta: pres.precio_venta,
        es_default: pres.es_default,
    }
}

const cancelarEdicionPresentacion = () => {
    presentacionEditandoId.value = null
    nuevaPresentacion.value = { nombre: '', unidad_sunat: 'NIU', factor_conversion: '', precio_venta: '', es_default: false }
}

const agregarPresentacionLocal = () => {
    if (!nuevaPresentacion.value.nombre.trim()) {
        alert('Falta el nombre del paquete (ej: Pack x6)')
        return
    }
    const cantidad = Number(nuevaPresentacion.value.factor_conversion)
    if (!cantidad || cantidad <= 0) {
        alert('Falta indicar cuantas unidades trae el paquete (debe ser mayor a 0)')
        return
    }
    const precio = Number(nuevaPresentacion.value.precio_venta)
    if (nuevaPresentacion.value.precio_venta === '' || isNaN(precio) || precio < 0) {
        alert('Falta el precio de venta del paquete')
        return
    }
    if (modalEditar.value) {
        if (presentacionEditandoId.value) {
            router.put(`/minimarket/presentaciones/${presentacionEditandoId.value}`, nuevaPresentacion.value, {
                onSuccess: () => {
                    window.location.replace('/minimarket/productos')
                },
                onError: (errors) => {
                    alert('No se pudo actualizar: ' + Object.values(errors).join(' '))
                }
            })
            return
        }
        router.post(`/minimarket/productos/${productoSeleccionado.value.id}/presentaciones`, nuevaPresentacion.value, {
            onSuccess: () => {
                window.location.replace('/minimarket/productos')
            },
            onError: (errors) => {
                alert('No se pudo guardar: ' + Object.values(errors).join(' '))
            }
        })
        return
    }
    form.value.presentaciones.push({ ...nuevaPresentacion.value })
    nuevaPresentacion.value = { nombre: '', unidad_sunat: 'NIU', factor_conversion: '', precio_venta: '', es_default: false }
}

const quitarPresentacionLocal = (index) => {
    form.value.presentaciones.splice(index, 1)
}

const formStock = ref({ tipo: 'entrada', cantidad: 1, observaciones: '' })
const escaneando = ref(false)
const inputCodigoBarras = ref(null)

const activarEscaneo = () => {
    escaneando.value = true
    if (inputCodigoBarras.value) {
        inputCodigoBarras.value.focus()
    }
    setTimeout(() => { escaneando.value = false }, 10000)
}

const onCodigoBarrasEnter = () => {
    escaneando.value = false
    const existe = props.productos.find(p =>
        p.codigo_barras === form.value.codigo_barras && p.id !== (productoSeleccionado.value ? productoSeleccionado.value.id : null)
    )
    if (existe) {
        alert("Este codigo ya esta asignado a: " + existe.descripcion)
        form.value.codigo_barras = ""
    }
}

const productosFiltrados = computed(() => {
    if (!busqueda.value) return props.productos
    const q = busqueda.value.toLowerCase()
    return props.productos.filter(p =>
        p.descripcion.toLowerCase().includes(q) ||
        (p.codigo && p.codigo.toLowerCase().includes(q))
    )
})

const stockBajo = computed(() =>
    props.productos.filter(p => p.stock_actual <= (p.stock_minimo || 3))
)

const cerrarModales = () => {
    modalNuevo.value  = false
    modalEditar.value = false
    form.value = { descripcion: '', codigo: '', codigo_barras: '', precio_compra: '', precio_venta: '', stock_actual: 0, stock_minimo: 0, categoria_id: '', presentaciones: [] }
    nuevaPresentacion.value = { nombre: '', unidad_sunat: 'NIU', factor_conversion: '', precio_venta: '', es_default: false }
    presentacionEditandoId.value = null
}

const editarProducto = (p) => {
    productoSeleccionado.value = p
    form.value = {
        descripcion:   p.descripcion,
        codigo:        p.codigo,
        codigo_barras: p.codigo_barras,
        precio_compra: p.precio_compra,
        precio_venta:  p.precio_venta,
        stock_minimo:  p.stock_minimo,
        categoria_id:  p.categoria_id,
    }
    modalEditar.value = true
}

const ajustarStock = (p) => {
    productoSeleccionado.value = p
    formStock.value = { tipo: 'entrada', cantidad: 1 }
    modalStock.value = true
}

const abrirPresentaciones = (p) => {
    productoSeleccionado.value = p
    formPresentacion.value = { nombre: '', unidad_sunat: 'NIU', factor_conversion: 1, precio_venta: '', es_default: false }
    modalPresentaciones.value = true
}

const guardarPresentacion = () => {
    if (!formPresentacion.value.nombre.trim()) {
        alert('Falta el nombre del paquete (ej: Pack x6)')
        return
    }
    const cantidad = Number(formPresentacion.value.factor_conversion)
    if (!cantidad || cantidad <= 0) {
        alert('Falta indicar cuantas unidades trae el paquete (debe ser mayor a 0)')
        return
    }
    const precio = Number(formPresentacion.value.precio_venta)
    if (formPresentacion.value.precio_venta === '' || isNaN(precio) || precio < 0) {
        alert('Falta el precio de venta del paquete')
        return
    }
    router.post(`/minimarket/productos/${productoSeleccionado.value.id}/presentaciones`, formPresentacion.value, {
        onSuccess: () => {
            window.location.replace('/minimarket/productos')
        },
        onError: (errors) => {
            alert('No se pudo guardar: ' + Object.values(errors).join(' '))
        }
    })
}

const eliminarPresentacion = (pres) => {
    if (!confirm(`Eliminar la presentacion "${pres.nombre}"?`)) return
    router.delete(`/minimarket/presentaciones/${pres.id}`, {
        onSuccess: () => {
            window.location.replace('/minimarket/productos')
        }
    })
}

const guardar = () => {
    if (modalEditar.value) {
        router.put(`/minimarket/productos/${productoSeleccionado.value.id}`, {
            descripcion:   form.value.descripcion,
            codigo:        form.value.codigo,
            codigo_barras: form.value.codigo_barras,
            precio_compra: form.value.precio_compra,
            precio_venta:  form.value.precio_venta,
            stock_minimo:  form.value.stock_minimo,
            categoria_id:  form.value.categoria_id,
        }, {
            onSuccess: () => {
                modalNuevo.value = false
                modalEditar.value = false
                window.location.replace('/minimarket/productos')
            },
            onError: (errors) => {
                alert('Error: ' + JSON.stringify(errors))
            }
        })
    } else {
        router.post('/minimarket/productos', {
            descripcion:   form.value.descripcion,
            codigo:        form.value.codigo,
            codigo_barras: form.value.codigo_barras,
            precio_compra: form.value.precio_compra,
            precio_venta:  form.value.precio_venta,
            stock_actual:  form.value.stock_actual,
            stock_minimo:  form.value.stock_minimo,
            categoria_id:  form.value.categoria_id,
            presentaciones: form.value.presentaciones,
        }, {
            onSuccess: () => {
                modalNuevo.value = false
                modalEditar.value = false
                window.location.replace('/minimarket/productos')
            },
            onError: (errors) => {
                alert('Error: ' + JSON.stringify(errors))
            }
        })
    }
}

const guardarStock = () => {
    router.post(`/minimarket/productos/${productoSeleccionado.value.id}/stock`, formStock.value, {
        onSuccess: () => {
            modalStock.value = false
            window.location.href = '/minimarket/productos'
        }
    })
}




</script>