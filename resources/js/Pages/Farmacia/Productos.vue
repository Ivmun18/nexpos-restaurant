<template>
    <AppLayout title="Productos" subtitle="Gestión de inventario">

        <!-- Header -->
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
            <div>
                <p style="font-size:24px; font-weight:800; color:#1E293B; margin:0;">📦 Productos</p>
                <p style="font-size:14px; color:#94A3B8; margin:4px 0 0;">{{ productos.length }} productos registrados</p>
            </div>
            <div style="display:flex; gap:12px;">
                <button @click="modalImportar = true"
                    style="padding:10px 20px; background:linear-gradient(135deg,#8B5CF6,#7C3AED); color:white; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                    📤 Importar Excel
                </button>
                <button @click="modalNuevo = true"
                    style="padding:10px 20px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                    + Nuevo Producto
                </button>
            </div>
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
            <input v-model="busqueda" id="buscador-productos" ref="inputBusqueda" placeholder="🔍 Buscar por nombre, código, código de barras, principio activo o laboratorio..."
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
                            <div v-if="p.stock_actual <= (p.stock_minimo || 3)" style="margin-top:4px; font-size:11px; font-weight:700; color:#991B1B;">⚠️ Stock bajo</div>
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
                                <button @click="verHistorial(p)"
                                    style="padding:6px 12px; background:#FDF4FF; color:#7C3AED; border-radius:8px; font-size:12px; font-weight:600; border:1px solid #E9D5FF; cursor:pointer;">
                                    📜 Historial
                                </button>
                                <button @click="verKardex(p)"
                                    style="padding:6px 12px; background:#FFF7ED; color:#C2410C; border-radius:8px; font-size:12px; font-weight:600; border:1px solid #FED7AA; cursor:pointer;">
                                    📊 Kardex
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

            <!-- Campos Farmacia -->
            <div style="background:#F0FDFA; border-radius:12px; padding:16px; border:1px solid #99F6E4; margin-bottom:16px;">
                <p style="font-size:13px; font-weight:700; color:#0F766E; margin:0 0 12px;">💊 Información Farmacéutica</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">📅 Fecha Vencimiento</label>
                        <input v-model="form.fecha_vencimiento" type="date" style="width:100%; padding:10px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">🏷️ Lote</label>
                        <input v-model="form.lote" type="text" placeholder="Ej: L-2024-001" style="width:100%; padding:10px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">🏭 Laboratorio</label>
                        <input v-model="form.laboratorio" type="text" placeholder="Ej: Bayer" style="width:100%; padding:10px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">🧪 Principio Activo</label>
                        <input v-model="form.principio_activo" type="text" placeholder="Ej: Amoxicilina" style="width:100%; padding:10px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">💊 Presentación</label>
                        <select v-model="form.presentacion" style="width:100%; padding:10px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:white;">
                            <option value="">Seleccionar...</option>
                            <option>Tabletas</option><option>Cápsulas</option><option>Jarabe</option><option>Ampolla</option><option>Crema</option><option>Gotas</option><option>Inyectable</option><option>Suspensión</option><option>Otro</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">⚗️ Concentración</label>
                        <input v-model="form.concentracion" type="text" placeholder="Ej: 500mg" style="width:100%; padding:10px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
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
                        <option value="entrada">📥 Entrada (sumar)</option>
                        <option value="salida">📤 Salida (restar)</option>
                        <option value="ajuste">🔧 Ajuste (establecer)</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:13px; font-weight:600; color:#64748B;">Cantidad</label>
                    <input v-model="formStock.cantidad" type="number" min="1"
                        style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:4px;" />
                </div>
            </div>

            <!-- Campos Farmacia -->
            <div style="background:#F0FDFA; border-radius:12px; padding:16px; border:1px solid #99F6E4; margin-bottom:16px;">
                <p style="font-size:13px; font-weight:700; color:#0F766E; margin:0 0 12px;">💊 Información Farmacéutica</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">📅 Fecha Vencimiento</label>
                        <input v-model="form.fecha_vencimiento" type="date" style="width:100%; padding:10px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">🏷️ Lote</label>
                        <input v-model="form.lote" type="text" placeholder="Ej: L-2024-001" style="width:100%; padding:10px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">🏭 Laboratorio</label>
                        <input v-model="form.laboratorio" type="text" placeholder="Ej: Bayer" style="width:100%; padding:10px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">🧪 Principio Activo</label>
                        <input v-model="form.principio_activo" type="text" placeholder="Ej: Amoxicilina" style="width:100%; padding:10px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">💊 Presentación</label>
                        <select v-model="form.presentacion" style="width:100%; padding:10px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:white;">
                            <option value="">Seleccionar...</option>
                            <option>Tabletas</option><option>Cápsulas</option><option>Jarabe</option><option>Ampolla</option><option>Crema</option><option>Gotas</option><option>Inyectable</option><option>Suspensión</option><option>Otro</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:4px;">⚗️ Concentración</label>
                        <input v-model="form.concentracion" type="text" placeholder="Ej: 500mg" style="width:100%; padding:10px; border:2px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
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

    </AppLayout>
        <!-- Modal Importar Excel -->
        <div v-if="modalImportar" style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:999;" @click.self="cerrarModalImportar">
            <div style="background:white; padding:32px; border-radius:20px; max-width:600px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
                    <div>
                        <h3 style="margin:0; font-size:22px; font-weight:800; color:#1E293B;">📤 Importar Productos desde Excel</h3>
                        <p style="margin:4px 0 0; font-size:14px; color:#94A3B8;">Carga masiva de inventario</p>
                    </div>
                    <button @click="cerrarModalImportar" style="background:transparent; border:none; font-size:24px; cursor:pointer; color:#94A3B8;">×</button>
                </div>

                <!-- Instrucciones -->
                <div style="background:#F0F9FF; border:1px solid #BFDBFE; border-radius:12px; padding:16px; margin-bottom:20px;">
                    <p style="margin:0 0 8px; font-size:13px; font-weight:600; color:#1E40AF;">📋 Instrucciones:</p>
                    <ol style="margin:0; padding-left:20px; font-size:13px; color:#475569; line-height:1.6;">
                        <li>Descarga la plantilla Excel (si no la tienes)</li>
                        <li>Llena el archivo con tus productos</li>
                        <li>Columnas obligatorias: código, descripción, precio_compra, precio_venta, stock</li>
                        <li>Sube el archivo aquí para importar</li>
                    </ol>
                </div>

                <!-- Selector de archivo -->
                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:13px; font-weight:600; color:#475569; margin-bottom:8px;">
                        Selecciona tu archivo Excel:
                    </label>
                    <input ref="fileInput" type="file" accept=".xlsx,.xls,.csv" @change="archivoSeleccionado"
                        style="width:100%; padding:12px; border:2px dashed #CBD5E1; border-radius:10px; font-size:14px; cursor:pointer; box-sizing:border-box;">
                    <p v-if="archivo" style="margin:8px 0 0; font-size:12px; color:#14B8A6; font-weight:600;">
                        ✓ Archivo seleccionado: {{ archivo.name }}
                    </p>
                </div>

                <!-- Resultados (si hay) -->
                <div v-if="resultadoImportacion" :style="{
                    background: resultadoImportacion.tipo === 'success' ? '#F0FDF4' : '#FEF2F2',
                    border: '1px solid ' + (resultadoImportacion.tipo === 'success' ? '#BBF7D0' : '#FECACA'),
                    borderRadius: '12px',
                    padding: '16px',
                    marginBottom: '20px'
                }">
                    <p :style="{
                        margin: '0',
                        fontSize: '14px',
                        fontWeight: '600',
                        color: resultadoImportacion.tipo === 'success' ? '#166534' : '#991B1B'
                    }">
                        {{ resultadoImportacion.mensaje }}
                    </p>
                    <div v-if="resultadoImportacion.errores && resultadoImportacion.errores.length" style="margin-top:12px;">
                        <p style="margin:0 0 8px; font-size:13px; font-weight:600; color:#991B1B;">Errores encontrados:</p>
                        <ul style="margin:0; padding-left:20px; font-size:12px; color:#DC2626;">
                            <li v-for="(error, idx) in resultadoImportacion.errores.slice(0, 5)" :key="idx">{{ error }}</li>
                            <li v-if="resultadoImportacion.errores.length > 5">... y {{ resultadoImportacion.errores.length - 5 }} errores más</li>
                        </ul>
                    </div>
                </div>

                <!-- Botones -->
                <div style="display:flex; gap:12px; justify-content:flex-end;">
                    <button @click="cerrarModalImportar"
                        style="padding:10px 20px; background:#F1F5F9; color:#475569; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                        Cancelar
                    </button>
                    <button @click="procesarImportacion" :disabled="!archivo || procesando"
                        :style="{
                            padding: '10px 20px',
                            background: (!archivo || procesando) ? '#CBD5E1' : 'linear-gradient(135deg,#8B5CF6,#7C3AED)',
                            color: 'white',
                            borderRadius: '10px',
                            fontSize: '14px',
                            fontWeight: '600',
                            border: 'none',
                            cursor: (!archivo || procesando) ? 'not-allowed' : 'pointer'
                        }">
                        {{ procesando ? '⏳ Procesando...' : '📤 Importar' }}
                    </button>
                </div>
            </div>
        </div>


<!-- Modal Historial de Compras -->
<Teleport to="body">
    <div v-if="modalHistorial" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:center; justify-content:center;">
        <div style="background:white; border-radius:20px; padding:32px; width:100%; max-width:600px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <div>
                    <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0;">📜 Historial de Compras</p>
                    <p style="font-size:13px; color:#64748B; margin:4px 0 0;">{{ productoHistorial?.descripcion }}</p>
                </div>
                <button @click="modalHistorial=false" style="background:none; border:none; font-size:20px; cursor:pointer; color:#94A3B8;">✕</button>
            </div>

            <div v-if="cargandoHistorial" style="text-align:center; padding:40px; color:#94A3B8;">Cargando...</div>

            <div v-else-if="historialData.length === 0" style="text-align:center; padding:40px; color:#94A3B8;">
                No hay compras registradas para este producto
            </div>

            <table v-else style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:10px 12px; text-align:left; color:#64748B; font-weight:600;">Fecha</th>
                        <th style="padding:10px 12px; text-align:left; color:#64748B; font-weight:600;">Comprobante</th>
                        <th style="padding:10px 12px; text-align:left; color:#64748B; font-weight:600;">Proveedor</th>
                        <th style="padding:10px 12px; text-align:center; color:#64748B; font-weight:600;">Cant.</th>
                        <th style="padding:10px 12px; text-align:right; color:#64748B; font-weight:600;">P. Compra</th>
                        <th style="padding:10px 12px; text-align:left; color:#64748B; font-weight:600;">Lote</th>
                        <th style="padding:10px 12px; text-align:left; color:#64748B; font-weight:600;">Vence</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(h, i) in historialData" :key="i" style="border-top:1px solid #F1F5F9;">
                        <td style="padding:10px 12px;">{{ h.fecha }}</td>
                        <td style="padding:10px 12px; color:#3B82F6;">{{ h.numero }}</td>
                        <td style="padding:10px 12px;">{{ h.proveedor || '—' }}</td>
                        <td style="padding:10px 12px; text-align:center; font-weight:600;">{{ h.cantidad }}</td>
                        <td style="padding:10px 12px; text-align:right; font-weight:600; color:#059669;">S/ {{ Number(h.precio_unitario).toFixed(2) }}</td>
                        <td style="padding:10px 12px; color:#7C3AED;">{{ h.lote || '—' }}</td>
                        <td style="padding:10px 12px;" :style="h.fecha_vencimiento && h.fecha_vencimiento < hoy ? 'color:#DC2626; font-weight:600;' : ''">
                            {{ h.fecha_vencimiento || '—' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</Teleport>

<!-- Modal Kardex -->
<Teleport to="body">
    <div v-if="modalKardex" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:center; justify-content:center;">
        <div style="background:white; border-radius:20px; padding:32px; width:100%; max-width:800px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <div>
                    <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0;">📊 Kardex</p>
                    <p style="font-size:13px; color:#64748B; margin:4px 0 0;">{{ kardexProducto?.descripcion }} — Stock actual: <strong>{{ kardexData?.producto?.stock_actual }}</strong></p>
                </div>
                <button @click="modalKardex=false" style="background:none; border:none; font-size:20px; cursor:pointer; color:#94A3B8;">✕</button>
            </div>

            <div v-if="cargandoKardex" style="text-align:center; padding:40px; color:#94A3B8;">Cargando...</div>

            <div v-else-if="!kardexData?.kardex?.length" style="text-align:center; padding:40px; color:#94A3B8;">
                No hay movimientos registrados
            </div>

            <table v-else style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:10px 12px; text-align:left; color:#64748B; font-weight:600;">Fecha</th>
                        <th style="padding:10px 12px; text-align:left; color:#64748B; font-weight:600;">Tipo</th>
                        <th style="padding:10px 12px; text-align:left; color:#64748B; font-weight:600;">Referencia</th>
                        <th style="padding:10px 12px; text-align:left; color:#64748B; font-weight:600;">Detalle</th>
                        <th style="padding:10px 12px; text-align:left; color:#64748B; font-weight:600;">Lote</th>
                        <th style="padding:10px 12px; text-align:center; color:#64748B; font-weight:600;">Entrada</th>
                        <th style="padding:10px 12px; text-align:center; color:#64748B; font-weight:600;">Salida</th>
                        <th style="padding:10px 12px; text-align:center; color:#64748B; font-weight:600;">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(m, i) in kardexData.kardex" :key="i"
                        :style="'border-top:1px solid #F1F5F9;' + (m.tipo==='ENTRADA' ? 'background:#F0FDF4;' : '')">
                        <td style="padding:10px 12px; white-space:nowrap;">{{ m.fecha }}</td>
                        <td style="padding:10px 12px;">
                            <span :style="m.tipo==='ENTRADA'
                                ? 'padding:2px 8px; background:#dcfce7; color:#166534; border-radius:20px; font-size:11px; font-weight:700;'
                                : 'padding:2px 8px; background:#fee2e2; color:#991b1b; border-radius:20px; font-size:11px; font-weight:700;'">
                                {{ m.tipo }}
                            </span>
                        </td>
                        <td style="padding:10px 12px; color:#3B82F6; font-size:12px;">{{ m.referencia }}</td>
                        <td style="padding:10px 12px; font-size:12px; color:#475569;">{{ m.detalle }}</td>
                        <td style="padding:10px 12px; font-size:12px; color:#7C3AED;">{{ m.lote || '—' }}</td>
                        <td style="padding:10px 12px; text-align:center; font-weight:600; color:#16A34A;">
                            {{ m.tipo==='ENTRADA' ? m.cantidad : '—' }}
                        </td>
                        <td style="padding:10px 12px; text-align:center; font-weight:600; color:#DC2626;">
                            {{ m.tipo==='SALIDA' ? m.cantidad : '—' }}
                        </td>
                        <td style="padding:10px 12px; text-align:center; font-weight:700; color:#1E293B;">{{ m.saldo }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</Teleport>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    productos: { type: Array, default: () => [] },
    categorias: { type: Array, default: () => [] },
})

const busqueda    = ref('')
const modalImportar = ref(false)
const archivo = ref(null)
const procesando = ref(false)
const resultadoImportacion = ref(null)

const inputBusqueda = ref(null)

onMounted(() => {
    nextTick(() => {
        if (inputBusqueda.value) {
            inputBusqueda.value.focus()
        }
    })
})
const modalNuevo  = ref(false)
const modalEditar = ref(false)
const modalStock  = ref(false)
const productoSeleccionado = ref(null)

const form = ref({
    descripcion: '', codigo: '', codigo_barras: '',
    precio_compra: '', precio_venta: '',
    stock_actual: 0, stock_minimo: 0,
})

const formStock = ref({ tipo: 'entrada', cantidad: 1 })
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
    // Permitido: mismo código de barras para distintos lotes del mismo medicamento
}

const productosFiltrados = computed(() => {
    if (!busqueda.value) return props.productos
    const q = busqueda.value.toLowerCase()
    const qRaw = busqueda.value.trim()
    return props.productos.filter(p =>
        p.descripcion.toLowerCase().includes(q) ||
        (p.codigo && p.codigo.toLowerCase().includes(q)) ||
        (p.codigo_barras && p.codigo_barras.includes(qRaw)) ||
        (p.principio_activo && p.principio_activo.toLowerCase().includes(q)) ||
        (p.laboratorio && p.laboratorio.toLowerCase().includes(q))
    )
})

const stockBajo = computed(() =>
    props.productos.filter(p => p.stock_actual <= (p.stock_minimo || 3))
)

const cerrarModales = () => {
    modalNuevo.value  = false
    modalEditar.value = false
    form.value = { descripcion: '', codigo: '', codigo_barras: '', precio_compra: '', precio_venta: '', stock_actual: 0, stock_minimo: 0, categoria_id: '' }
}

const editarProducto = (p) => {
    productoSeleccionado.value = p
    form.value = {
        descripcion:       p.descripcion,
        codigo:            p.codigo,
        codigo_barras:     p.codigo_barras,
        precio_compra:     p.precio_compra,
        precio_venta:      p.precio_venta,
        stock_minimo:      p.stock_minimo,
        categoria_id:      p.categoria_id,
        lote:              p.lote || '',
        fecha_vencimiento: p.fecha_vencimiento ? p.fecha_vencimiento.slice(0, 10) : '',
    }
    modalEditar.value = true
}

const modalHistorial    = ref(false)
const productoHistorial = ref(null)
const historialData     = ref([])
const cargandoHistorial = ref(false)
const hoy = new Date().toISOString().slice(0, 10)

const modalKardex    = ref(false)
const kardexProducto = ref(null)
const kardexData     = ref(null)
const cargandoKardex = ref(false)

const verKardex = async (p) => {
    kardexProducto.value = p
    modalKardex.value    = true
    cargandoKardex.value = true
    kardexData.value     = null
    try {
        const res = await fetch('/farmacia/productos/' + p.id + '/kardex')
        kardexData.value = await res.json()
    } catch (e) {
        kardexData.value = null
    } finally {
        cargandoKardex.value = false
    }
}

const verHistorial = async (p) => {
    productoHistorial.value = p
    modalHistorial.value    = true
    cargandoHistorial.value = true
    historialData.value     = []
    try {
        const res = await fetch('/farmacia/productos/' + p.id + '/historial')
        historialData.value = await res.json()
    } catch (e) {
        historialData.value = []
    } finally {
        cargandoHistorial.value = false
    }
}

const ajustarStock = (p) => {
    productoSeleccionado.value = p
    formStock.value = { tipo: 'entrada', cantidad: 1 }
    modalStock.value = true
}

const guardar = () => {
    if (modalEditar.value) {
        router.put(`/farmacia/productos/${productoSeleccionado.value.id}`, {
            descripcion:       form.value.descripcion,
            codigo:            form.value.codigo,
            codigo_barras:     form.value.codigo_barras,
            precio_compra:     form.value.precio_compra,
            precio_venta:      form.value.precio_venta,
            stock_minimo:      form.value.stock_minimo,
            categoria_id:      form.value.categoria_id,
            lote:              form.value.lote,
            fecha_vencimiento: form.value.fecha_vencimiento,
        }, {
            onSuccess: () => {
                modalNuevo.value = false
                modalEditar.value = false
                window.location.replace('/farmacia/productos')
            },
            onError: (errors) => {
                alert('Error: ' + JSON.stringify(errors))
            }
        })
    } else {
        router.post('/farmacia/productos', {
            descripcion:   form.value.descripcion,
            codigo:        form.value.codigo,
            codigo_barras: form.value.codigo_barras,
            precio_compra: form.value.precio_compra,
            precio_venta:  form.value.precio_venta,
            stock_actual:  form.value.stock_actual,
            stock_minimo:  form.value.stock_minimo,
            categoria_id:  form.value.categoria_id,
        }, {
            onSuccess: () => {
                modalNuevo.value = false
                modalEditar.value = false
                window.location.replace('/farmacia/productos')
            },
            onError: (errors) => {
                alert('Error: ' + JSON.stringify(errors))
            }
        })
    }
}

const guardarStock = () => {
    router.post(`/farmacia/productos/${productoSeleccionado.value.id}/stock`, formStock.value, {
        onSuccess: () => {
            modalStock.value = false
            window.location.href = '/farmacia/productos'
        }
    })
}

// ─────────────────────────────────────────────────────────
// IMPORTAR DESDE EXCEL
// ─────────────────────────────────────────────────────────

const archivoSeleccionado = (event) => {
    const file = event.target.files[0]
    if (file) {
        archivo.value = file
        resultadoImportacion.value = null
    }
}

const cerrarModalImportar = () => {
    modalImportar.value = false
    archivo.value = null
    procesando.value = false
    resultadoImportacion.value = null
    if (document.querySelector('input[type="file"]')) {
        document.querySelector('input[type="file"]').value = ''
    }
}

const procesarImportacion = async () => {
    if (!archivo.value) {
        alert('Selecciona un archivo primero')
        return
    }

    procesando.value = true
    resultadoImportacion.value = null

    const formData = new FormData()
    formData.append('archivo', archivo.value)
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content)

    try {
        await router.post('/farmacia/productos/importar', formData, {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page.props.flash
                if (flash.success) {
                    resultadoImportacion.value = {
                        tipo: 'success',
                        mensaje: flash.success,
                        errores: flash.estadisticas?.errores || []
                    }
                    // Cerrar modal después de 3 segundos si fue exitoso y sin errores
                    if (!flash.estadisticas?.errores?.length) {
                        setTimeout(() => {
                            cerrarModalImportar()
                            location.reload() // Recargar para ver los productos nuevos
                        }, 2000)
                    }
                } else if (flash.error) {
                    resultadoImportacion.value = {
                        tipo: 'error',
                        mensaje: flash.error,
                        errores: flash.errores || []
                    }
                }
            },
            onError: (errors) => {
                resultadoImportacion.value = {
                    tipo: 'error',
                    mensaje: 'Error al procesar el archivo',
                    errores: Object.values(errors)
                }
            },
            onFinish: () => {
                procesando.value = false
            }
        })
    } catch (error) {
        procesando.value = false
        resultadoImportacion.value = {
            tipo: 'error',
            mensaje: 'Error al subir el archivo: ' + error.message,
            errores: []
        }
    }
}


// ─────────────────────────────────────────────────────────
// IMPORTAR DESDE EXCEL


</script>