<template>
    <AppLayout title="Caja notarial" subtitle="Cobro de expedientes">

        <!-- CAJA CERRADA -->
        <div v-if="!sesionAbierta" style="max-width:400px; margin:3rem auto; background:white; border-radius:16px; border:2px solid #FDE68A; padding:2rem; text-align:center;">
            <p style="font-size:40px; margin:0 0 8px;">🔒</p>
            <p style="font-size:18px; font-weight:800; color:#92400E; margin:0 0 4px;">Caja cerrada</p>
            <p style="font-size:13px; color:#B45309; margin:0 0 1.5rem;">Ingresa el fondo inicial para comenzar</p>
            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600; text-align:left;">Fondo inicial (S/)</label>
            <input v-model="fondoApertura" type="number" step="0.01" min="0" placeholder="0.00"
                style="width:100%; padding:14px; border:2px solid #FDE68A; border-radius:10px; font-size:22px; font-weight:800; outline:none; box-sizing:border-box; text-align:center; margin-bottom:1rem;" />
            <button @click="abrirCaja" style="width:100%; padding:14px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:10px; font-size:16px; font-weight:700; cursor:pointer;">
                🔓 Abrir caja
            </button>
        </div>

        <template v-if="sesionAbierta">

            <!-- BARRA ESTADO CAJA -->
            <div style="background:white; border-radius:12px; border:1px solid #BBF7D0; padding:.9rem 1.25rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:8px; flex:1;">
                    <span style="font-size:18px;">🟢</span>
                    <div>
                        <p style="font-size:13px; font-weight:700; color:#166534; margin:0;">Caja abierta</p>
                        <p style="font-size:11px; color:#94A3B8; margin:0;">Desde {{ formatFecha(resumenCaja?.apertura) }}</p>
                    </div>
                </div>
                <div style="display:flex; gap:20px; flex-wrap:wrap;">
                    <div style="text-align:center;">
                        <p style="font-size:10px; color:#94A3B8; margin:0; text-transform:uppercase; font-weight:600;">Fondo</p>
                        <p style="font-size:15px; font-weight:800; color:#1E293B; margin:0;">S/ {{ Number(resumenCaja?.fondo_inicial||0).toFixed(2) }}</p>
                    </div>
                    <div style="text-align:center;">
                        <p style="font-size:10px; color:#94A3B8; margin:0; text-transform:uppercase; font-weight:600;">Ingresos</p>
                        <p style="font-size:15px; font-weight:800; color:#10B981; margin:0;">S/ {{ Number(resumenCaja?.ingresos||0).toFixed(2) }}</p>
                    </div>
                    <div style="text-align:center;">
                        <p style="font-size:10px; color:#94A3B8; margin:0; text-transform:uppercase; font-weight:600;">Saldo</p>
                        <p style="font-size:15px; font-weight:800; color:#0F766E; margin:0;">S/ {{ Number(resumenCaja?.saldo_sistema||0).toFixed(2) }}</p>
                    </div>
                </div>
                <button @click="showVentaDirecta=true" style="padding:8px 16px; background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; margin-right:8px;">
                    🧾 Nueva venta directa
                </button>
                <button @click="modalCerrar=true" style="padding:8px 16px; background:#FEF2F2; color:#991B1B; border:1px solid #FECACA; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">
                    🔒 Cerrar caja
                </button>
            </div>

            <!-- LAYOUT PRINCIPAL -->
            <div style="display:grid; grid-template-columns:1fr 420px; gap:16px; align-items:start;">

                <!-- COLUMNA IZQUIERDA -->
                <div style="display:flex; flex-direction:column; gap:12px;">

                    <!-- BUSCADOR -->
                    <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1rem 1.25rem;">
                        <div style="display:flex; gap:8px;">
                            <input v-model="busqueda" type="text"
                                placeholder="🔍  N° expediente, nombre, DNI, asunto..."
                                @keyup.enter="buscarServidor"
                                style="flex:1; padding:12px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; transition:border .2s;"
                                @focus="e => e.target.style.borderColor='#6366F1'"
                                @blur="e => e.target.style.borderColor='#E2E8F0'" />
                            <button @click="buscarServidor"
                                style="padding:12px 20px; background:#6366F1; color:white; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer;">
                                Buscar
                            </button>
                        </div>
                    </div>

                    <!-- LISTA PENDIENTES -->
                    <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
                        <div style="padding:.9rem 1.25rem; border-bottom:1px solid #F1F5F9; display:flex; justify-content:space-between; align-items:center;">
                            <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0;">
                                Pendientes de cobro
                                <span style="background:#EEF2FF; color:#4F46E5; padding:2px 8px; border-radius:20px; font-size:12px; margin-left:6px;">{{ props.pendientes.length }}</span>
                            </p>
                            <span style="font-size:12px; color:#EF4444; font-weight:700;">S/ {{ totalPorCobrar.toFixed(2) }} por cobrar</span>
                        </div>
                        <div v-if="props.pendientes.length === 0" style="padding:2.5rem; text-align:center; color:#94A3B8;">
                            <p style="font-size:28px; margin:0 0 6px;">✅</p>
                            <p style="font-size:13px; margin:0;">No hay expedientes pendientes</p>
                        </div>
                        <div v-for="e in props.pendientes" :key="e.id" @click="seleccionar(e)"
                            :style="{
                                padding:'13px 16px', borderTop:'1px solid #F8FAFC', cursor:'pointer',
                                background: expedienteSeleccionado?.id === e.id ? '#EEF2FF' : 'white',
                                borderLeft: expedienteSeleccionado?.id === e.id ? '4px solid #6366F1' : '4px solid transparent',
                            }">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
                                <span style="font-family:monospace; font-size:13px; font-weight:800; color:#4F46E5;">{{ e.numero_expediente }}</span>
                                <span :style="estiloPago(e.estado_pago)">{{ labelPago(e.estado_pago) }}</span>
                            </div>
                            <p style="font-size:13px; color:#1E293B; font-weight:600; margin:0 0 2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ e.asunto }}</p>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:11px; color:#94A3B8;">{{ e.partes_intervinientes || '—' }}</span>
                                <span style="font-size:14px; font-weight:800; color:#EF4444;">S/ {{ Number(e.saldo).toFixed(2) }}</span>
                            </div>
                            <div style="background:#E2E8F0; border-radius:20px; height:3px; margin-top:6px;">
                                <div :style="{width:Math.min(100,(e.monto_pagado/e.monto_cobrar)*100)+'%', background:'#6366F1', borderRadius:'20px', height:'100%'}"></div>
                            </div>
                        </div>
                    </div>

                    <!-- PAGADOS HOY -->
                    <div v-if="props.pagadosHoy?.length > 0" style="background:white; border-radius:12px; border:1px solid #BBF7D0; overflow:hidden;">
                        <div style="padding:.9rem 1.25rem; border-bottom:1px solid #F0FDF4; background:#F0FDF4;">
                            <p style="font-size:13px; font-weight:700; color:#166534; margin:0;">✅ Cobrados hoy — pendientes de comprobante ({{ props.pagadosHoy.length }})</p>
                        </div>
                        <div v-for="e in props.pagadosHoy" :key="'p'+e.id" @click="seleccionar(e)"
                            :style="{
                                padding:'12px 16px', borderTop:'1px solid #F0FDF4', cursor:'pointer',
                                background: expedienteSeleccionado?.id === e.id ? '#ECFDF5' : 'white',
                                borderLeft: expedienteSeleccionado?.id === e.id ? '4px solid #10B981' : '4px solid transparent',
                            }">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-family:monospace; font-size:13px; font-weight:800; color:#059669;">{{ e.numero_expediente }}</span>
                                <span style="font-size:13px; font-weight:800; color:#059669;">S/ {{ Number(e.monto_cobrar).toFixed(2) }}</span>
                            </div>
                            <p style="font-size:13px; color:#1E293B; margin:2px 0 0; font-weight:500;">{{ e.asunto }}</p>
                        </div>
                    </div>

                </div>

                <!-- COLUMNA DERECHA: PANEL DE COBRO -->
                <div style="position:sticky; top:80px;">

                    <div v-if="!expedienteSeleccionado" style="background:white; border-radius:12px; border:2px dashed #E2E8F0; padding:3rem 2rem; text-align:center;">
                        <p style="font-size:48px; margin:0 0 10px;">👈</p>
                        <p style="font-size:14px; font-weight:600; margin:0 0 4px; color:#94A3B8;">Selecciona un expediente</p>
                        <p style="font-size:12px; margin:0; color:#CBD5E1;">para registrar el cobro</p>
                    </div>

                    <div v-else style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">

                        <!-- Cabecera -->
                        <div style="padding:1rem 1.25rem; background:linear-gradient(135deg,#EEF2FF,#E0E7FF); border-bottom:1px solid #C7D2FE;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                <div style="flex:1; min-width:0;">
                                    <p style="font-size:11px; color:#4F46E5; font-weight:700; margin:0 0 2px; text-transform:uppercase;">{{ labelTipo(expedienteSeleccionado.tipo_acto) }}</p>
                                    <p style="font-size:15px; font-weight:800; color:#1E293B; margin:0 0 3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ expedienteSeleccionado.asunto }}</p>
                                    <p style="font-size:12px; color:#6366F1; margin:0;">{{ expedienteSeleccionado.partes_intervinientes || '—' }}</p>
                                </div>
                                <span style="font-family:monospace; font-size:12px; font-weight:800; color:#4F46E5; background:white; padding:4px 10px; border-radius:20px; border:1px solid #C7D2FE; margin-left:8px; white-space:nowrap;">{{ expedienteSeleccionado.numero_expediente }}</span>
                            </div>
                        </div>

                        <div style="padding:1.25rem;">

                            <!-- Resumen financiero -->
                            <div style="background:#F8FAFC; border-radius:10px; padding:12px 14px; margin-bottom:1.25rem;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                                    <span style="font-size:12px; color:#64748B;">Total servicio</span>
                                    <span style="font-size:13px; font-weight:700;">S/ {{ Number(expedienteSeleccionado.monto_cobrar).toFixed(2) }}</span>
                                </div>
                                <div v-if="expedienteSeleccionado.monto_pagado > 0" style="display:flex; justify-content:space-between; margin-bottom:5px;">
                                    <span style="font-size:12px; color:#64748B;">Ya pagado</span>
                                    <span style="font-size:13px; font-weight:700; color:#10B981;">− S/ {{ Number(expedienteSeleccionado.monto_pagado).toFixed(2) }}</span>
                                </div>
                                <div style="border-top:1px solid #E2E8F0; padding-top:8px; display:flex; justify-content:space-between; align-items:center;">
                                    <span style="font-size:14px; font-weight:700; color:#1E293B;">Saldo a cobrar</span>
                                    <span style="font-size:22px; font-weight:900; color:#EF4444;">S/ {{ Number(expedienteSeleccionado.saldo ?? (expedienteSeleccionado.monto_cobrar - expedienteSeleccionado.monto_pagado)).toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- Monto -->
                            <div style="margin-bottom:1rem;">
                                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:6px; font-weight:700; text-transform:uppercase; letter-spacing:.5px;">Monto a cobrar (S/)</label>
                                <input v-model="formCobro.monto" type="number" step="0.01" min="0"
                                    style="width:100%; padding:14px; border:2px solid #6366F1; border-radius:10px; font-size:28px; font-weight:900; outline:none; box-sizing:border-box; text-align:center; color:#1E293B;" />
                            </div>

                            <!-- Método de pago -->
                            <div style="margin-bottom:1rem;">
                                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:6px; font-weight:700; text-transform:uppercase; letter-spacing:.5px;">Método de pago</label>
                                <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:6px;">
                                    <button v-for="m in metodos" :key="m.value" @click="formCobro.metodo_pago = m.value"
                                        :style="{
                                            padding:'10px 4px', border:'2px solid', borderRadius:'10px', cursor:'pointer', textAlign:'center',
                                            borderColor: formCobro.metodo_pago === m.value ? '#6366F1' : '#E2E8F0',
                                            background: formCobro.metodo_pago === m.value ? '#EEF2FF' : 'white',
                                            color: formCobro.metodo_pago === m.value ? '#4F46E5' : '#64748B',
                                            fontWeight:'700', fontSize:'11px',
                                        }">
                                        <div style="font-size:18px; margin-bottom:3px;">{{ m.icon }}</div>
                                        {{ m.label }}
                                    </button>
                                </div>
                                <input v-if="formCobro.metodo_pago !== 'efectivo'" v-model="formCobro.referencia"
                                    type="text" :placeholder="formCobro.metodo_pago === 'tarjeta' ? 'Últimos 4 dígitos' : 'N° operación / referencia'"
                                    style="width:100%; margin-top:8px; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                            </div>

                            <!-- Comprobante inline -->
                            <div style="border:1px solid #E2E8F0; border-radius:10px; overflow:hidden; margin-bottom:1.25rem;">
                                <div style="padding:10px 14px; background:#F8FAFC; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; gap:8px;">
                                    <span style="font-size:14px;">🧾</span>
                                    <p style="font-size:12px; font-weight:700; color:#1E293B; margin:0;">Comprobante de pago</p>
                                </div>
                                <div style="padding:12px 14px; display:flex; flex-direction:column; gap:10px;">
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                                        <button v-for="t in tiposComp" :key="t.value" @click="formComp.tipo_comprobante = t.value"
                                            :style="{
                                                padding:'10px', border:'2px solid', borderRadius:'8px', cursor:'pointer', textAlign:'center',
                                                borderColor: formComp.tipo_comprobante === t.value ? '#0F766E' : '#E2E8F0',
                                                background: formComp.tipo_comprobante === t.value ? '#F0FDFA' : 'white',
                                                color: formComp.tipo_comprobante === t.value ? '#0F766E' : '#64748B',
                                                fontWeight:'700', fontSize:'13px',
                                            }">
                                            {{ t.icon }} {{ t.label }}
                                        </button>
                                    </div>
                                    <div style="display:grid; grid-template-columns:100px 1fr; gap:8px;">
                                        <select v-model="formComp.cliente_tipo_documento"
                                            style="padding:9px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; font-weight:600;">
                                            <option value="1">DNI</option>
                                            <option value="6">RUC</option>
                                            <option value="0">Sin doc.</option>
                                        </select>
                                        <input v-model="formComp.cliente_numero_documento" type="text"
                                            :placeholder="formComp.cliente_tipo_documento==='6' ? '20xxxxxxxxx' : '12345678'"
                                            style="padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                                    </div>
                                    <input v-model="formComp.cliente_nombre" type="text" placeholder="Nombre completo del cliente *"
                                        style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                                    <input v-model="formComp.cliente_email" type="email" placeholder="Email (opcional)"
                                        style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                                    <div v-if="errorComp" style="background:#FEF2F2; border-radius:8px; padding:8px 12px; font-size:12px; color:#991B1B;">❌ {{ errorComp }}</div>
                                    <div v-if="pdfComp" style="background:#F0FDF4; border-radius:8px; padding:8px 12px; font-size:12px; color:#166534;">
                                        ✅ Comprobante emitido.
                                        <a :href="pdfComp" target="_blank" style="font-weight:700; color:#0F766E; margin-left:4px;">📥 Descargar PDF</a>
                                    </div>
                                </div>
                            </div>

                            <!-- BOTÓN COBRAR -->
                            <button @click="confirmarCobro" :disabled="!formCobro.monto || procesando"
                                :style="{
                                    width:'100%', padding:'16px', border:'none', borderRadius:'12px',
                                    fontSize:'17px', fontWeight:'800', transition:'all .2s',
                                    background: !formCobro.monto || procesando ? '#E2E8F0' : 'linear-gradient(135deg,#6366F1,#4F46E5)',
                                    color: !formCobro.monto || procesando ? '#94A3B8' : 'white',
                                    cursor: !formCobro.monto || procesando ? 'not-allowed' : 'pointer',
                                }">
                                {{ procesando ? '⏳ Procesando...' : '💰 Cobrar S/ ' + (Number(formCobro.monto)||0).toFixed(2) }}
                            </button>
                            <button @click="expedienteSeleccionado=null"
                                style="width:100%; margin-top:8px; padding:10px; background:transparent; border:none; color:#94A3B8; font-size:12px; cursor:pointer;">
                                Cancelar
                            </button>

                        </div>
                    </div>
                </div>

            </div>

        </template>

        <!-- MODAL CIERRE DE CAJA -->

        <!-- MODAL VENTA DIRECTA -->
        <div v-if="showVentaDirecta" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:999;">
            <div style="background:white; border-radius:16px; padding:28px; width:580px; max-width:95vw; max-height:90vh; overflow-y:auto;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h2 style="margin:0; font-size:18px; font-weight:700;">🧾 Nueva venta directa</h2>
                    <button @click="cerrarVentaDirecta" style="background:#F1F5F9; border:none; padding:6px 12px; border-radius:8px; cursor:pointer;">✕</button>
                </div>
                <div style="background:#F8FAFC; border-radius:10px; padding:16px; margin-bottom:16px;">
                    <p style="font-weight:700; color:#374151; margin:0 0 12px; font-size:13px;">Datos del cliente</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#64748B;">Tipo documento</label>
                            <select v-model="formVD.cliente_tipo_documento" style="width:100%; padding:9px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;">
                                <option value="1">DNI</option>
                                <option value="6">RUC</option>
                                <option value="4">Carnet extranjería</option>
                                <option value="7">Pasaporte</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px; font-weight:600; color:#64748B;">N° documento *</label>
                            <input v-model="formVD.cliente_numero_documento" type="text"
                                :placeholder="formVD.cliente_tipo_documento==='6' ? '20xxxxxxxxx' : '12345678'"
                                style="width:100%; padding:9px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                        </div>
                    </div>
                    <div style="margin-bottom:10px;">
                        <label style="font-size:12px; font-weight:600; color:#64748B;">Nombre / Razón social *</label>
                        <input v-model="formVD.cliente_nombre" type="text" placeholder="Nombre completo"
                            style="width:100%; padding:9px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:12px; font-weight:600; color:#64748B;">Email (opcional)</label>
                        <input v-model="formVD.cliente_email" type="email" placeholder="correo@ejemplo.com"
                            style="width:100%; padding:9px; border:1.5px solid #E2E8F0; border-radius:8px; margin-top:4px; box-sizing:border-box;" />
                    </div>
                </div>
                <div style="margin-bottom:16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <p style="font-weight:700; color:#374151; margin:0; font-size:13px;">Servicios</p>
                        <button @click="agregarItem" style="background:#6366F1; color:white; border:none; padding:6px 14px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">+ Agregar</button>
                    </div>
                    <div v-for="(item, i) in formVD.items" :key="i" style="display:flex; gap:8px; margin-bottom:8px; align-items:center;">
                        <input v-model="item.descripcion" type="text" placeholder="Descripción del servicio"
                            style="flex:1; padding:9px; border:1.5px solid #E2E8F0; border-radius:8px; box-sizing:border-box;" />
                        <input v-model="item.precio" type="number" min="0" step="0.01" placeholder="0.00"
                            style="width:110px; padding:9px; border:1.5px solid #E2E8F0; border-radius:8px; box-sizing:border-box;" />
                        <button @click="formVD.items.splice(i,1)" style="background:#FEE2E2; color:#991B1B; border:none; padding:8px 10px; border-radius:8px; cursor:pointer;">✕</button>
                    </div>
                    <div v-if="formVD.items.length" style="text-align:right; margin-top:8px;">
                        <span style="font-size:15px; font-weight:800; color:#1E293B;">Total: S/ {{ totalVD.toFixed(2) }}</span>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
                    <div>
                        <p style="font-weight:700; color:#374151; margin:0 0 8px; font-size:13px;">Método de pago</p>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            <button v-for="m in metodosVD" :key="m.v" @click="formVD.metodo_pago=m.v"
                                :style="{ background: formVD.metodo_pago===m.v ? '#6366F1' : '#F1F5F9', color: formVD.metodo_pago===m.v ? 'white' : '#374151', border:'none', padding:'6px 12px', borderRadius:'8px', cursor:'pointer', fontSize:'12px', fontWeight:'600' }">
                                {{ m.l }}
                            </button>
                        </div>
                    </div>
                    <div>
                        <p style="font-weight:700; color:#374151; margin:0 0 8px; font-size:13px;">Tipo comprobante</p>
                        <div style="display:flex; gap:6px;">
                            <button @click="formVD.tipo_comprobante='03'"
                                :style="{ background: formVD.tipo_comprobante==='03' ? '#6366F1' : '#F1F5F9', color: formVD.tipo_comprobante==='03' ? 'white' : '#374151', border:'none', padding:'6px 16px', borderRadius:'8px', cursor:'pointer', fontSize:'12px', fontWeight:'600' }">
                                Boleta
                            </button>
                            <button @click="formVD.tipo_comprobante='01'"
                                :style="{ background: formVD.tipo_comprobante==='01' ? '#6366F1' : '#F1F5F9', color: formVD.tipo_comprobante==='01' ? 'white' : '#374151', border:'none', padding:'6px 16px', borderRadius:'8px', cursor:'pointer', fontSize:'12px', fontWeight:'600' }">
                                Factura
                            </button>
                        </div>
                    </div>
                </div>
                <button @click="emitirVentaDirecta" :disabled="procesandoVD || !formVD.items.length || !formVD.cliente_nombre"
                    style="width:100%; padding:14px; background:linear-gradient(135deg,#6366F1,#4F46E5); color:white; border:none; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer;">
                    {{ procesandoVD ? 'Emitiendo...' : '🧾 Emitir comprobante' }}
                </button>
                <div v-if="mensajeVD" :style="{ background: mensajeVD.ok ? '#F0FDF4' : '#FEF2F2', color: mensajeVD.ok ? '#166534' : '#991B1B', padding:'12px', borderRadius:'10px', marginTop:'12px', fontWeight:'600', fontSize:'13px' }">
                    {{ mensajeVD.texto }}
                </div>
            </div>
        </div>

        <div v-if="modalCerrar" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:200; display:flex; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:16px; padding:1.5rem; width:400px; max-width:95vw;">
                <p style="font-size:16px; font-weight:800; color:#1E293B; margin:0 0 1.2rem;">🔒 Cerrar caja</p>
                <div style="background:#F8FAFC; border-radius:10px; padding:14px; margin-bottom:1rem;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Fondo inicial</span>
                        <span style="font-size:13px; font-weight:700;">S/ {{ Number(resumenCaja?.fondo_inicial||0).toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">+ Ingresos</span>
                        <span style="font-size:13px; font-weight:700; color:#10B981;">S/ {{ Number(resumenCaja?.ingresos||0).toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">− Egresos</span>
                        <span style="font-size:13px; font-weight:700; color:#EF4444;">S/ {{ Number(resumenCaja?.egresos||0).toFixed(2) }}</span>
                    </div>
                    <div style="border-top:1px solid #E2E8F0; padding-top:8px; display:flex; justify-content:space-between;">
                        <span style="font-size:14px; font-weight:700;">Saldo sistema</span>
                        <span style="font-size:18px; font-weight:900; color:#0F766E;">S/ {{ Number(resumenCaja?.saldo_sistema||0).toFixed(2) }}</span>
                    </div>
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">Monto contado físicamente (S/)</label>
                    <input v-model="formCierre.monto_real" type="number" step="0.01" min="0"
                        style="width:100%; padding:12px; border:2px solid #E2E8F0; border-radius:8px; font-size:20px; font-weight:800; outline:none; box-sizing:border-box; text-align:center;" />
                    <p v-if="formCierre.monto_real" style="font-size:12px; margin:4px 0 0; font-weight:600;"
                        :style="(Number(formCierre.monto_real)-Number(resumenCaja?.saldo_sistema||0))>=0?{color:'#166534'}:{color:'#991B1B'}">
                        Diferencia: {{ (Number(formCierre.monto_real)-Number(resumenCaja?.saldo_sistema||0))>=0?'+':'' }}S/ {{ (Number(formCierre.monto_real)-Number(resumenCaja?.saldo_sistema||0)).toFixed(2) }}
                    </p>
                </div>
                <div style="margin-bottom:1.2rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:6px; font-weight:600;">Observaciones</label>
                    <input v-model="formCierre.observaciones" type="text" placeholder="Notas del cierre..."
                        style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <button @click="modalCerrar=false" style="padding:10px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Cancelar</button>
                    <button @click="cerrarCaja" style="padding:10px 20px; background:#EF4444; color:white; border:none; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer;">🔒 Confirmar cierre</button>
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
    pendientes:    { type: Array,   default: () => [] },
    pagadosHoy:    { type: Array,   default: () => [] },
    sesionAbierta: { type: Boolean, default: false },
    resumenCaja:   { type: Object,  default: null },
})

const busqueda               = ref('')
const expedienteSeleccionado = ref(null)
const procesando             = ref(false)
const modalCerrar            = ref(false)

const showVentaDirecta = ref(false)
const procesandoVD     = ref(false)
const mensajeVD        = ref(null)
const metodosVD        = [
    { v: 'efectivo', l: 'Efectivo' },
    { v: 'yape',     l: 'Yape' },
    { v: 'plin',     l: 'Plin' },
    { v: 'transferencia', l: 'Transferencia' },
    { v: 'tarjeta',  l: 'Tarjeta' },
]
const formVD = ref({
    tipo_comprobante:         '03',
    cliente_tipo_documento:   '1',
    cliente_numero_documento: '',
    cliente_nombre:           '',
    cliente_email:            '',
    metodo_pago:              'efectivo',
    items:                    [],
})
const totalVD = computed(() => formVD.value.items.reduce((s, i) => s + Number(i.precio || 0), 0))

function agregarItem() {
    formVD.value.items.push({ descripcion: '', precio: '' })
}

function cerrarVentaDirecta() {
    showVentaDirecta.value = false
    mensajeVD.value        = null
    formVD.value = { tipo_comprobante:'03', cliente_tipo_documento:'1', cliente_numero_documento:'', cliente_nombre:'', cliente_email:'', metodo_pago:'efectivo', items:[] }
}

async function emitirVentaDirecta() {
    if (procesandoVD.value) return
    procesandoVD.value = true
    mensajeVD.value    = null
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        const res = await fetch('/notaria/caja/venta-directa', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body:    JSON.stringify(formVD.value),
        })
        const data = await res.json()
        if (data.success) {
            mensajeVD.value = { ok: true, texto: '✅ ' + data.mensaje }
            if (data.pdf) window.open(data.pdf, '_blank')
            setTimeout(cerrarVentaDirecta, 2000)
        } else {
            mensajeVD.value = { ok: false, texto: '❌ ' + data.mensaje }
        }
    } catch(e) {
        mensajeVD.value = { ok: false, texto: '❌ Error de conexión' }
    }
    procesandoVD.value = false
}

const fondoApertura          = ref(0)
const formCierre             = ref({ monto_real: '', observaciones: '' })
const errorComp              = ref('')
const pdfComp                = ref('')

const formCobro = ref({ monto: '', metodo_pago: 'efectivo', tipo: 'pago_final', referencia: '' })
const formComp  = ref({ tipo_comprobante: '03', cliente_tipo_documento: '1', cliente_numero_documento: '', cliente_nombre: '', cliente_email: '' })

const tiposComp = [
    { value: '03', label: 'Boleta',  icon: '🧾' },
    { value: '01', label: 'Factura', icon: '📄' },
]

const metodos = [
    { value: 'efectivo',      label: 'Efectivo', icon: '💵' },
    { value: 'yape',          label: 'Yape',     icon: '📱' },
    { value: 'plin',          label: 'Plin',     icon: '📲' },
    { value: 'tarjeta',       label: 'Tarjeta',  icon: '💳' },
    { value: 'transferencia', label: 'Transfer', icon: '🏦' },
]

const totalPorCobrar = computed(() => props.pendientes.reduce((s, e) => s + Number(e.saldo), 0))

function seleccionar(e) {
    expedienteSeleccionado.value = e
    errorComp.value = ''
    pdfComp.value   = ''
    formCobro.value = {
        monto:       Number(e.saldo ?? (e.monto_cobrar - e.monto_pagado)).toFixed(2),
        metodo_pago: 'efectivo',
        tipo:        e.monto_pagado > 0 ? 'pago_final' : 'adelanto',
        referencia:  ''
    }
    formComp.value = {
        tipo_comprobante:         '03',
        cliente_tipo_documento:   e.cliente?.tipo_documento || '1',
        cliente_numero_documento: e.cliente?.numero_documento || '',
        cliente_nombre:           e.cliente?.nombre || '',
        cliente_email:            e.cliente?.email || '',
    }
}

function abrirCaja() {
    router.post('/notaria/caja/abrir', { monto_apertura: fondoApertura.value }, { preserveScroll: true })
}

function cerrarCaja() {
    router.post('/notaria/caja/cerrar', formCierre.value, {
        preserveScroll: true,
        onSuccess: () => { modalCerrar.value = false }
    })
}

async function confirmarCobro() {
    if (!formCobro.value.monto || !expedienteSeleccionado.value || procesando.value) return
    procesando.value = true
    errorComp.value  = ''
    const actoId = expedienteSeleccionado.value.id

    await new Promise((resolve) => {
        router.post('/notaria/caja/' + actoId + '/cobrar', formCobro.value, {
            preserveScroll: true,
            onSuccess: resolve,
            onError:   resolve,
        })
    })

    if (formComp.value.cliente_nombre?.trim()) {
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content
            const res  = await fetch('/notaria/comprobantes/' + actoId + '/emitir', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(formComp.value)
            })
            const data = await res.json()
            if (data.success && data.pdf) {
                pdfComp.value = data.pdf
                window.open(data.pdf, '_blank')
            } else {
                window.open('/notaria/recibo/' + actoId + '/ultimo', '_blank')
            }
        } catch {
            window.open('/notaria/recibo/' + actoId + '/ultimo', '_blank')
        }
    } else {
        window.open('/notaria/recibo/' + actoId + '/ultimo', '_blank')
    }

    procesando.value             = false
    expedienteSeleccionado.value = null
    formCobro.value = { monto: '', metodo_pago: 'efectivo', tipo: 'pago_final', referencia: '' }
}

function buscarServidor() {
    const params = new URLSearchParams()
    if (busqueda.value) params.set('buscar', busqueda.value)
    router.visit('/notaria/caja?' + params.toString(), { preserveScroll: true })
}

function labelTipo(t) {
    const map = { escritura_publica:'📜 Escritura', poder:'✍️ Poder', testamento:'📋 Testamento', legalizacion:'🔏 Legalización', carta_notarial:'✉️ Carta', protesto:'⚖️ Protesto', acta_notarial:'📝 Acta', otro:'📁 Otro' }
    return map[t] ?? t
}

function labelPago(p) {
    return { pendiente:'⏳ Pendiente', parcial:'◑ Parcial', pagado:'✅ Pagado' }[p] ?? p
}

function estiloPago(p) {
    const map = {
        pendiente: { background:'#FEF2F2', color:'#991B1B' },
        parcial:   { background:'#FEF3C7', color:'#92400E' },
        pagado:    { background:'#F0FDF4', color:'#166534' },
    }
    return { ...(map[p]||{}), fontSize:'11px', padding:'3px 9px', borderRadius:'20px', fontWeight:'600' }
}

function formatFecha(f) {
    if (!f) return '—'
    return new Date(f).toLocaleString('es-PE', { day:'2-digit', month:'2-digit', year:'2-digit', hour:'2-digit', minute:'2-digit' })
}
</script>
