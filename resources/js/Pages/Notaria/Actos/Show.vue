<template>
    <AppLayout :title="'Expediente ' + acto.numero_expediente" subtitle="Detalle del acto notarial">

        <!-- HEADER -->
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:1.5rem;">
            <a href="/notaria/actos" style="background:#F1F5F9; border:none; border-radius:10px; padding:9px 14px; font-size:14px; cursor:pointer; text-decoration:none; color:#475569; font-weight:600;">← Volver</a>
            <a :href="`/notaria/actos/${acto.id}/imprimir`" target="_blank"
                style="background:#14B8A6; color:#fff; border:none; border-radius:10px; padding:9px 16px; font-size:14px; cursor:pointer; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
                🖨️ Imprimir Acto
            </a>
            <a :href="`/notaria/actos/${acto.id}/descargar`"
                style="background:#3B82F6; color:#fff; border:none; border-radius:10px; padding:9px 16px; font-size:14px; cursor:pointer; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
                📥 Descargar PDF
            </a>
            <button @click="abrirModalDoc"
                style="background:#1D4ED8; color:#fff; border:none; border-radius:10px; padding:9px 16px; font-size:14px; cursor:pointer; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
                📄 Generar Documento
            </button>
            <button @click="abrirEditar"
                style="background:#F59E0B; color:#fff; border:none; border-radius:10px; padding:9px 16px; font-size:14px; cursor:pointer; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
                ✏️ Editar
            </button>
            <div>
                <h2 style="font-size:20px; font-weight:800; color:#1E293B; margin:0;">{{ acto.numero_expediente }}</h2>
                <p style="font-size:13px; color:#94A3B8; margin:2px 0 0;">{{ labelTipo(acto.tipo_acto) }} · Ingresado {{ formatFecha(acto.fecha_ingreso) }}</p>
            </div>
            <div style="margin-left:auto; display:flex; gap:8px;">
                <span :style="estiloEstado(acto.estado)">{{ labelEstado(acto.estado) }}</span>
                <span :style="estiloPago(acto.estado_pago)">{{ labelPago(acto.estado_pago) }}</span>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 340px; gap:16px;">

            <!-- IZQUIERDA -->
            <div style="display:flex; flex-direction:column; gap:16px;">

                <!-- DATOS DEL EXPEDIENTE -->
                <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.25rem;">
                    <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0 0 1rem;">📄 Datos del expediente</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Tipo de acto</p>
                            <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ labelTipo(acto.tipo_acto) }}</p>
                        </div>
                        <div>
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Responsable</p>
                            <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ acto.usuario?.name ?? '—' }}</p>
                        </div>
                        <div style="grid-column:1/-1;">
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Asunto</p>
                            <p style="font-size:14px; color:#1E293B; margin:0;">{{ acto.asunto }}</p>
                        </div>
                        <div style="grid-column:1/-1;">
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Partes intervinientes</p>
                            <p style="font-size:14px; color:#1E293B; margin:0;">{{ acto.partes_intervinientes || '—' }}</p>
                        </div>
                        <div>
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Fecha ingreso</p>
                            <p style="font-size:14px; color:#1E293B; margin:0;">{{ formatFecha(acto.fecha_ingreso) }}</p>
                        </div>
                        <div>
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Fecha entrega</p>
                            <p style="font-size:14px; color:#1E293B; margin:0;">{{ acto.fecha_entrega ? formatFecha(acto.fecha_entrega) : 'Sin fecha' }}</p>
                        </div>
                        <div v-if="acto.observaciones" style="grid-column:1/-1;">
                            <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 3px;">Observaciones</p>
                            <p style="font-size:13px; color:#64748B; margin:0;">{{ acto.observaciones }}</p>
                        </div>
                    </div>
                </div>

                <!-- REQUISITOS / CHECKLIST DOCUMENTOS -->
                <div v-if="false" style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.25rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                        <div>
                            <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0;">📋 Requisitos y documentos</p>
                            <p style="font-size:11px; color:#94A3B8; margin:2px 0 0;">
                                {{ acto.requisitos?.filter(r => r.entregado).length || 0 }} de {{ acto.requisitos?.length || 0 }} entregados
                            </p>
                        </div>
                        <div style="display:flex; gap:6px; align-items:center;">
                            <button @click="mostrarAddReq=!mostrarAddReq" style="padding:5px 12px; background:#EEF2FF; color:#4F46E5; border:1px solid #C7D2FE; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer;">
                                + Agregar
                            </button>
                        </div>
                    </div>

                    <!-- Agregar requisito rápido -->
                    <div v-if="mostrarAddReq" style="display:flex; gap:8px; margin-bottom:1rem;">
                        <select v-model="nuevoRequisito" style="flex:1; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                            <option value="">Seleccionar documento...</option>
                            <option v-for="r in requisitosComunes" :key="r" :value="r">{{ r }}</option>
                            <option value="__otro__">Otro (escribir)</option>
                        </select>
                        <button @click="agregarRequisito" style="padding:8px 14px; background:#14B8A6; color:white; border:none; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer;">Agregar</button>
                    </div>
                    <div v-if="nuevoRequisito === '__otro__' && mostrarAddReq" style="margin-bottom:10px;">
                        <input v-model="otroRequisito" type="text" placeholder="Escribe el nombre del documento..."
                            style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>

                    <!-- Lista requisitos -->
                    <div v-if="!acto.requisitos || acto.requisitos.length === 0" style="text-align:center; color:#94A3B8; padding:1rem; font-size:13px;">
                        Sin requisitos registrados — agrega los documentos que necesita el cliente
                    </div>
                    <div v-for="r in acto.requisitos" :key="r.id"
                        style="display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:8px; margin-bottom:4px;"
                        :style="{background: r.entregado ? '#F0FDF4' : '#FEF2F2'}">
                        <input type="checkbox" :checked="r.entregado" @change="toggleRequisito(r)"
                            style="width:18px; height:18px; cursor:pointer; flex-shrink:0;" />
                        <div style="flex:1;">
                            <p :style="{fontSize:'13px', fontWeight:'600', margin:'0', color: r.entregado ? '#166534' : '#991B1B', textDecoration: r.entregado ? 'none' : 'none'}">
                                {{ r.entregado ? '✅' : '❌' }} {{ r.documento }}
                            </p>
                            <p v-if="r.observacion" style="font-size:11px; color:#64748B; margin:2px 0 0;">{{ r.observacion }}</p>
                        </div>
                        <button @click="eliminarRequisito(r.id)" style="background:none; border:none; color:#94A3B8; cursor:pointer; font-size:14px; padding:2px;">✕</button>
                    </div>

                    <!-- Alerta si hay pendientes -->
                    <div v-if="acto.requisitos?.length > 0 && acto.requisitos.some(r => !r.entregado)"
                        style="margin-top:10px; background:#FEF3C7; border-radius:8px; padding:8px 12px; font-size:12px; color:#92400E; font-weight:600;">
                        ⚠️ Faltan {{ acto.requisitos.filter(r => !r.entregado).length }} documento(s) por entregar
                    </div>
                </div>

                <!-- PLANTILLA DATOS ESPECÍFICOS -->
                <div v-if="false" style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.25rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                        <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0;">📋 Datos del {{ labelTipo(acto.tipo_acto) }}</p>
                        <button @click="guardandoDatos ? null : guardarDatos()" style="padding:5px 14px; background:#14B8A6; color:white; border:none; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer;">
                            💾 Guardar
                        </button>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <template v-for="campo in camposPlantilla" :key="campo.key">
                            <div :style="campo.full ? 'grid-column:1/-1' : ''">
                                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:4px; font-weight:600; text-transform:uppercase;">{{ campo.label }}</label>
                                <textarea v-if="campo.tipo === 'textarea'" v-model="formDatos[campo.key]" :rows="campo.rows || 2"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                                <select v-else-if="campo.tipo === 'select'" v-model="formDatos[campo.key]"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                                    <option value="">Seleccionar...</option>
                                    <option v-for="op in campo.opciones" :key="op" :value="op">{{ op }}</option>
                                </select>
                                <input v-else v-model="formDatos[campo.key]" :type="campo.tipo || 'text'"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                            </div>
                        
    <!-- MODAL EDITAR EXPEDIENTE -->
    <div v-if="modalEditar" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:300; display:flex; align-items:center; justify-content:center; padding:1rem;">
        <div style="background:white; border-radius:16px; width:700px; max-width:95vw; max-height:92vh; overflow-y:auto; display:flex; flex-direction:column;">
            <!-- Header -->
            <div style="padding:1.25rem 1.5rem; border-bottom:1px solid #E2E8F0; position:sticky; top:0; background:white; z-index:10; border-radius:16px 16px 0 0; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">✏️ Editar expediente</p>
                    <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ acto.numero_expediente }} — {{ labelTipo(acto.tipo_acto) }}</p>
                </div>
                <button @click="modalEditar=false" style="background:#F1F5F9; border:none; padding:6px 12px; border-radius:8px; cursor:pointer; font-size:16px;">✕</button>
            </div>

            <div style="padding:1.25rem 1.5rem; flex:1;">
                <!-- Datos generales -->
                <p style="font-size:12px; font-weight:700; color:#6366F1; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">📋 Datos generales</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:1.2rem;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Asunto *</label>
                        <input v-model="formEditar.asunto" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha ingreso</label>
                        <input v-model="formEditar.fecha_ingreso" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha entrega</label>
                        <input v-model="formEditar.fecha_entrega" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Monto a cobrar (S/)</label>
                        <input v-model="formEditar.monto_cobrar" type="number" step="0.01" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Observaciones</label>
                        <input v-model="formEditar.observaciones" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                </div>

                <!-- Datos específicos del tipo -->
                <div v-if="camposPlantillaEditar.length > 0" style="border-top:1px solid #F1F5F9; padding-top:1.2rem;">
                    <p style="font-size:12px; font-weight:700; color:#14B8A6; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">📋 Datos específicos</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <template v-for="campo in camposPlantillaEditar" :key="campo.key">
                            <div v-if="campo.tipo === 'seccion'" style="grid-column:1/-1; border-top:1px solid #E2E8F0; padding-top:10px; margin-top:4px;">
                                <p style="font-size:11px; font-weight:700; color:#0F766E; margin:0; text-transform:uppercase;">{{ campo.label }}</p>
                            </div>
                            <div v-else-if="campo.tipo === 'checkbox'" style="grid-column:1/-1;">
                                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:#374151;">
                                    <input type="checkbox" v-model="formDatosEditar[campo.key]" style="width:16px; height:16px;">
                                    {{ campo.label }}
                                </label>
                            </div>
                            <div v-else :style="campo.full ? 'grid-column:1/-1' : ''">
                                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">{{ campo.label }}</label>
                                <textarea v-if="campo.tipo === 'textarea'" v-model="formDatosEditar[campo.key]" :rows="campo.rows || 2"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                                <select v-else-if="campo.tipo === 'select'" v-model="formDatosEditar[campo.key]"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                                    <option value="">Seleccionar...</option>
                                    <option v-for="op in campo.opciones" :key="op" :value="op">{{ op }}</option>
                                </select>
                                <input v-else v-model="formDatosEditar[campo.key]" :type="campo.tipo || 'text'"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div style="padding:1rem 1.5rem; border-top:1px solid #E2E8F0; display:flex; justify-content:flex-end; gap:10px; position:sticky; bottom:0; background:white;">
                <button @click="modalEditar=false" style="padding:9px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">Cancelar</button>
                <button @click="guardarEdicion" style="padding:9px 20px; background:linear-gradient(135deg,#F59E0B,#D97706); color:white; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">💾 Guardar cambios</button>
            </div>
        </div>
    </div>

</template>
                    </div>
                </div>

                <!-- Partes Intervinientes -->
                <!-- <PartesIntervinientes :acto="acto" :partes="acto.partes || []" /> -->

                <!-- SEGUIMIENTO -->
                <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.25rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
                        <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0;">🔄 Seguimiento del trámite</p>
                        <button @click="modalEstado=true" style="padding:5px 14px; background:#EEF2FF; color:#4F46E5; border:1px solid #C7D2FE; border-radius:7px; font-size:12px; font-weight:600; cursor:pointer;">+ Actualizar estado</button>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <div v-for="s in acto.seguimientos" :key="s.id"
                            style="display:flex; gap:12px; align-items:flex-start; padding:10px 12px; background:#F8FAFC; border-radius:8px;">
                            <div :style="{width:'10px', height:'10px', borderRadius:'50%', marginTop:'4px', flexShrink:0,
                                background: s.estado_nuevo==='finalizado'?'#10B981':s.estado_nuevo==='en_proceso'?'#3B82F6':s.estado_nuevo==='cancelado'?'#EF4444':'#F59E0B'}"></div>
                            <div style="flex:1;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <span :style="estiloEstado(s.estado_nuevo)" style="font-size:11px;">{{ labelEstado(s.estado_nuevo) }}</span>
                                    <span style="font-size:11px; color:#94A3B8;">{{ s.usuario?.name }}</span>
                                    <span style="font-size:11px; color:#94A3B8; margin-left:auto;">{{ formatFechaHora(s.created_at) }}</span>
                                </div>
                                <p v-if="s.comentario" style="font-size:12px; color:#64748B; margin:4px 0 0;">{{ s.comentario }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- DERECHA -->
            <div style="display:flex; flex-direction:column; gap:16px;">

                <!-- COBROS -->
                <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.25rem;">
                    <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0 0 1rem;">💰 Estado de cobro</p>
                    <div style="background:#F8FAFC; border-radius:10px; padding:12px 14px; margin-bottom:1rem;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-size:13px; color:#64748B;">Total a cobrar</span>
                            <span style="font-size:13px; font-weight:700; color:#1E293B;">S/ {{ Number(acto.monto_cobrar).toFixed(2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                            <span style="font-size:13px; color:#64748B;">Pagado</span>
                            <span style="font-size:13px; font-weight:700; color:#10B981;">S/ {{ Number(acto.monto_pagado).toFixed(2) }}</span>
                        </div>
                        <div style="border-top:1px solid #E2E8F0; padding-top:6px; display:flex; justify-content:space-between;">
                            <span style="font-size:13px; font-weight:600; color:#1E293B;">Saldo pendiente</span>
                            <span style="font-size:15px; font-weight:800; color:#EF4444;">S/ {{ (Number(acto.monto_cobrar) - Number(acto.monto_pagado)).toFixed(2) }}</span>
                        </div>
                    </div>
                    <!-- Barra de progreso -->
                    <div style="background:#E2E8F0; border-radius:20px; height:8px; margin-bottom:12px;">
                        <div :style="{width: Math.min(100, (acto.monto_pagado/acto.monto_cobrar)*100) + '%', background:'#10B981', borderRadius:'20px', height:'100%', transition:'width .3s'}"></div>
                    </div>
                    <button v-if="acto.estado_pago !== 'pagado'" @click="modalPago=true"
                        style="width:100%; padding:10px; background:linear-gradient(135deg,#10B981,#059669); color:white; border:none; border-radius:9px; font-size:13px; font-weight:600; cursor:pointer;">
                        💵 Registrar pago
                    </button>
                    <div v-else-if="acto.estado_pago === 'pagado'" style="text-align:center;">
                        <p style="color:#10B981; font-weight:600; font-size:13px; margin:0;">✅ Cobro completado</p>
                    </div>
                </div>

                <!-- EMITIR COMPROBANTE -->
                <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.25rem;">
                    <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0 0 10px;">🧾 Comprobante electrónico</p>
                    <p style="font-size:12px; color:#64748B; margin:0 0 10px;">
                        {{ acto.estado_pago === 'pagado' ? 'Servicio cancelado — listo para emitir comprobante' : 'Podrá emitir el comprobante cuando el servicio esté cancelado completamente' }}
                    </p>
                    <button v-if="$page.props.auth.user.rol === 'admin' || $page.props.auth.user.rol === 'cajero'"
                        @click="abrirModalComprobante"
                        :style="{
                            width:'100%', padding:'10px', border:'none', borderRadius:'9px',
                            fontSize:'13px', fontWeight:'600', cursor: 'pointer',
                            background: acto.estado_pago==='pagado' ? 'linear-gradient(135deg,#6366F1,#4F46E5)' : '#F1F5F9',
                            color: acto.estado_pago==='pagado' ? 'white' : '#94A3B8'
                        }">
                        🧾 {{ acto.estado_pago==='pagado' ? 'Emitir Boleta / Factura' : 'Emitir comprobante (pendiente de pago)' }}
                    </button>
                </div>

                <!-- CAMBIAR ESTADO RÁPIDO -->
                <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.25rem;">
                    <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0 0 10px;">⚡ Cambiar estado</p>
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        <button v-for="e in estados" :key="e.value"
                            @click="cambiarEstadoRapido(e.value)"
                            :disabled="acto.estado === e.value"
                            :style="{
                                padding:'8px 12px', borderRadius:'8px', border:'1.5px solid',
                                borderColor: acto.estado===e.value ? e.color : '#E2E8F0',
                                background: acto.estado===e.value ? e.bg : 'white',
                                color: acto.estado===e.value ? e.color : '#64748B',
                                fontSize:'13px', fontWeight:'600', cursor: acto.estado===e.value ? 'default' : 'pointer',
                                textAlign:'left'
                            }">
                            {{ e.icon }} {{ e.label }} {{ acto.estado===e.value ? '← actual' : '' }}
                        </button>
                    </div>
                </div>

            </div>
        </div>

    <!-- MODAL GENERAR DOCUMENTO -->
    <div v-if="modalDoc" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:200; display:flex; align-items:center; justify-content:center; padding:1rem;">
        <div style="background:white; border-radius:16px; width:780px; max-width:95vw; max-height:92vh; overflow-y:auto;">
            <!-- HEADER -->
            <div style="padding:1.25rem 1.5rem; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; background:white; z-index:10; border-radius:16px 16px 0 0;">
                <div>
                    <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">📄 Generar Documento</p>
                    <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">Expediente: {{ props.acto.numero_expediente }}</p>
                </div>
                <button @click="modalDoc=false" style="background:#F1F5F9; border:none; padding:6px 12px; border-radius:8px; cursor:pointer; font-size:16px;">✕</button>
            </div>

            <!-- SELECTOR DE TIPO -->
            <div style="padding:1rem 1.5rem; border-bottom:1px solid #E2E8F0; display:flex; gap:8px; flex-wrap:wrap;">
                <button v-for="t in tiposDocumento" :key="t.value" @click="tipoDoc=t.value"
                    :style="{padding:'8px 16px', border:'2px solid', borderRadius:'8px', cursor:'pointer', fontSize:'13px', fontWeight:'600',
                        borderColor: tipoDoc===t.value ? t.color : '#E2E8F0',
                        background: tipoDoc===t.value ? t.bg : 'white',
                        color: tipoDoc===t.value ? t.color : '#64748B'}">
                    {{ t.icon }} {{ t.label }}
                </button>
            </div>

            <div style="padding:1.5rem;">

            <!-- CAMPOS COMUNES (siempre visibles) -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">N° Instrumento</label><input v-model="formDoc.num_instrumento" type="text" placeholder="0680" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">N° Minuta</label><input v-model="formDoc.num_minuta" type="text" placeholder="0602" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                <div style="grid-column:1/-1;"><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Fecha en letras *</label><input v-model="formDoc.fecha_letras" type="text" placeholder="DIECIOCHO DÍAS DEL MES DE JUNIO..." style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Fecha firma (dd-mm-aaaa)</label><input v-model="formDoc.fecha_firma" @input="formDoc.fecha_letras = fechaALetras(formDoc.fecha_firma) || formDoc.fecha_letras" type="text" placeholder="18-06-2026" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Fecha de la minuta</label><input v-model="formDoc.fecha_minuta" type="text" placeholder="18 de junio de 2026" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
            </div>

            <!-- VENDEDOR -->
            <div style="border-top:1px solid #E2E8F0; padding-top:1rem; margin-bottom:1rem;">
                <p style="font-size:12px; font-weight:700; color:#1D4ED8; margin:0 0 8px;">VENDEDOR</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <!-- Tipo vendedor (solo minuta) -->
                    <div v-if="tipoDoc==='minuta-compraventa'" style="grid-column:1/-1; display:flex; gap:12px; margin-bottom:4px;">
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:13px;"><input type="radio" v-model="formDoc.vendedor_tipo" value="empresa"> Empresa</label>
                        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:13px;"><input type="radio" v-model="formDoc.vendedor_tipo" value="persona"> Persona natural</label>
                    </div>
                    <template v-if="formDoc.vendedor_tipo==='empresa' && tipoDoc==='minuta-compraventa'">
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Razón Social *</label><input v-model="formDoc.vendedor_razon_social" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">RUC *</label><input v-model="formDoc.vendedor_ruc" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div style="grid-column:1/-1;"><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Domicilio *</label><input v-model="formDoc.vendedor_domicilio" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Partida registral</label><input v-model="formDoc.vendedor_partida_registral" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Cargo representante</label><input v-model="formDoc.representante_cargo" type="text" placeholder="Gerente General" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Nombre representante *</label><input v-model="formDoc.representante_nombre" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">DNI representante *</label><input v-model="formDoc.representante_dni" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Estado civil rep.</label><input v-model="formDoc.representante_estado_civil" type="text" placeholder="soltero" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Profesión rep.</label><input v-model="formDoc.representante_profesion" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div style="grid-column:1/-1;"><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Domicilio representante *</label><input v-model="formDoc.representante_domicilio" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    
    <!-- MODAL EDITAR EXPEDIENTE -->
    <div v-if="modalEditar" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:300; display:flex; align-items:center; justify-content:center; padding:1rem;">
        <div style="background:white; border-radius:16px; width:700px; max-width:95vw; max-height:92vh; overflow-y:auto; display:flex; flex-direction:column;">
            <!-- Header -->
            <div style="padding:1.25rem 1.5rem; border-bottom:1px solid #E2E8F0; position:sticky; top:0; background:white; z-index:10; border-radius:16px 16px 0 0; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">✏️ Editar expediente</p>
                    <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ acto.numero_expediente }} — {{ labelTipo(acto.tipo_acto) }}</p>
                </div>
                <button @click="modalEditar=false" style="background:#F1F5F9; border:none; padding:6px 12px; border-radius:8px; cursor:pointer; font-size:16px;">✕</button>
            </div>

            <div style="padding:1.25rem 1.5rem; flex:1;">
                <!-- Datos generales -->
                <p style="font-size:12px; font-weight:700; color:#6366F1; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">📋 Datos generales</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:1.2rem;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Asunto *</label>
                        <input v-model="formEditar.asunto" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha ingreso</label>
                        <input v-model="formEditar.fecha_ingreso" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha entrega</label>
                        <input v-model="formEditar.fecha_entrega" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Monto a cobrar (S/)</label>
                        <input v-model="formEditar.monto_cobrar" type="number" step="0.01" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Observaciones</label>
                        <input v-model="formEditar.observaciones" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                </div>

                <!-- Datos específicos del tipo -->
                <div v-if="camposPlantillaEditar.length > 0" style="border-top:1px solid #F1F5F9; padding-top:1.2rem;">
                    <p style="font-size:12px; font-weight:700; color:#14B8A6; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">📋 Datos específicos</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <template v-for="campo in camposPlantillaEditar" :key="campo.key">
                            <div v-if="campo.tipo === 'seccion'" style="grid-column:1/-1; border-top:1px solid #E2E8F0; padding-top:10px; margin-top:4px;">
                                <p style="font-size:11px; font-weight:700; color:#0F766E; margin:0; text-transform:uppercase;">{{ campo.label }}</p>
                            </div>
                            <div v-else-if="campo.tipo === 'checkbox'" style="grid-column:1/-1;">
                                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:#374151;">
                                    <input type="checkbox" v-model="formDatosEditar[campo.key]" style="width:16px; height:16px;">
                                    {{ campo.label }}
                                </label>
                            </div>
                            <div v-else :style="campo.full ? 'grid-column:1/-1' : ''">
                                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">{{ campo.label }}</label>
                                <textarea v-if="campo.tipo === 'textarea'" v-model="formDatosEditar[campo.key]" :rows="campo.rows || 2"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                                <select v-else-if="campo.tipo === 'select'" v-model="formDatosEditar[campo.key]"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                                    <option value="">Seleccionar...</option>
                                    <option v-for="op in campo.opciones" :key="op" :value="op">{{ op }}</option>
                                </select>
                                <input v-else v-model="formDatosEditar[campo.key]" :type="campo.tipo || 'text'"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div style="padding:1rem 1.5rem; border-top:1px solid #E2E8F0; display:flex; justify-content:flex-end; gap:10px; position:sticky; bottom:0; background:white;">
                <button @click="modalEditar=false" style="padding:9px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">Cancelar</button>
                <button @click="guardarEdicion" style="padding:9px 20px; background:linear-gradient(135deg,#F59E0B,#D97706); color:white; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">💾 Guardar cambios</button>
            </div>
        </div>
    </div>

</template>
                    <template v-else>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Nombre completo *</label><input v-model="formDoc.vendedor_nombre" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">DNI *</label><input v-model="formDoc.vendedor_dni" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Estado civil</label><input v-model="formDoc.vendedor_estado_civil" type="text" placeholder="soltero" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Profesión</label><input v-model="formDoc.vendedor_profesion" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div style="grid-column:1/-1;"><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Domicilio</label><input v-model="formDoc.vendedor_domicilio" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    
    <!-- MODAL EDITAR EXPEDIENTE -->
    <div v-if="modalEditar" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:300; display:flex; align-items:center; justify-content:center; padding:1rem;">
        <div style="background:white; border-radius:16px; width:700px; max-width:95vw; max-height:92vh; overflow-y:auto; display:flex; flex-direction:column;">
            <!-- Header -->
            <div style="padding:1.25rem 1.5rem; border-bottom:1px solid #E2E8F0; position:sticky; top:0; background:white; z-index:10; border-radius:16px 16px 0 0; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">✏️ Editar expediente</p>
                    <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ acto.numero_expediente }} — {{ labelTipo(acto.tipo_acto) }}</p>
                </div>
                <button @click="modalEditar=false" style="background:#F1F5F9; border:none; padding:6px 12px; border-radius:8px; cursor:pointer; font-size:16px;">✕</button>
            </div>

            <div style="padding:1.25rem 1.5rem; flex:1;">
                <!-- Datos generales -->
                <p style="font-size:12px; font-weight:700; color:#6366F1; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">📋 Datos generales</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:1.2rem;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Asunto *</label>
                        <input v-model="formEditar.asunto" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha ingreso</label>
                        <input v-model="formEditar.fecha_ingreso" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha entrega</label>
                        <input v-model="formEditar.fecha_entrega" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Monto a cobrar (S/)</label>
                        <input v-model="formEditar.monto_cobrar" type="number" step="0.01" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Observaciones</label>
                        <input v-model="formEditar.observaciones" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                </div>

                <!-- Datos específicos del tipo -->
                <div v-if="camposPlantillaEditar.length > 0" style="border-top:1px solid #F1F5F9; padding-top:1.2rem;">
                    <p style="font-size:12px; font-weight:700; color:#14B8A6; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">📋 Datos específicos</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <template v-for="campo in camposPlantillaEditar" :key="campo.key">
                            <div v-if="campo.tipo === 'seccion'" style="grid-column:1/-1; border-top:1px solid #E2E8F0; padding-top:10px; margin-top:4px;">
                                <p style="font-size:11px; font-weight:700; color:#0F766E; margin:0; text-transform:uppercase;">{{ campo.label }}</p>
                            </div>
                            <div v-else-if="campo.tipo === 'checkbox'" style="grid-column:1/-1;">
                                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:#374151;">
                                    <input type="checkbox" v-model="formDatosEditar[campo.key]" style="width:16px; height:16px;">
                                    {{ campo.label }}
                                </label>
                            </div>
                            <div v-else :style="campo.full ? 'grid-column:1/-1' : ''">
                                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">{{ campo.label }}</label>
                                <textarea v-if="campo.tipo === 'textarea'" v-model="formDatosEditar[campo.key]" :rows="campo.rows || 2"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                                <select v-else-if="campo.tipo === 'select'" v-model="formDatosEditar[campo.key]"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                                    <option value="">Seleccionar...</option>
                                    <option v-for="op in campo.opciones" :key="op" :value="op">{{ op }}</option>
                                </select>
                                <input v-else v-model="formDatosEditar[campo.key]" :type="campo.tipo || 'text'"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div style="padding:1rem 1.5rem; border-top:1px solid #E2E8F0; display:flex; justify-content:flex-end; gap:10px; position:sticky; bottom:0; background:white;">
                <button @click="modalEditar=false" style="padding:9px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">Cancelar</button>
                <button @click="guardarEdicion" style="padding:9px 20px; background:linear-gradient(135deg,#F59E0B,#D97706); color:white; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">💾 Guardar cambios</button>
            </div>
        </div>
    </div>

</template>
                </div>
            </div>

            <!-- COMPRADOR 1 -->
            <div style="border-top:1px solid #E2E8F0; padding-top:1rem; margin-bottom:1rem;">
                <p style="font-size:12px; font-weight:700; color:#1D4ED8; margin:0 0 8px;">COMPRADOR</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Nombre completo *</label><input v-model="formDoc.comprador_nombre" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">DNI *</label><input v-model="formDoc.comprador_dni" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Estado civil</label><input v-model="formDoc.comprador_estado_civil" type="text" placeholder="soltero" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Profesión</label><input v-model="formDoc.comprador_profesion" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div style="grid-column:1/-1;"><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Domicilio</label><input v-model="formDoc.comprador_domicilio" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                </div>
            </div>

            <!-- COMPRADOR 2 (solo parte notarial) -->
            <div v-if="tipoDoc==='parte-compraventa'" style="border-top:1px solid #E2E8F0; padding-top:1rem; margin-bottom:1rem;">
                <p style="font-size:12px; font-weight:700; color:#1D4ED8; margin:0 0 8px;">COMPRADOR 2 (opcional)</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Nombre</label><input v-model="formDoc.comprador2_nombre" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">DNI</label><input v-model="formDoc.comprador2_dni" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Estado civil</label><input v-model="formDoc.comprador2_estado_civil" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Profesión</label><input v-model="formDoc.comprador2_profesion" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                </div>
            </div>

            <!-- PREDIO -->
            <div style="border-top:1px solid #E2E8F0; padding-top:1rem; margin-bottom:1rem;">
                <p style="font-size:12px; font-weight:700; color:#1D4ED8; margin:0 0 8px;">PREDIO</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <div style="grid-column:1/-1;"><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Descripción del predio *</label><textarea v-model="formDoc.predio_descripcion" rows="2" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box; resize:vertical;"></textarea></div>
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Partida registral *</label><input v-model="formDoc.predio_partida" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Ciudad</label><input v-model="formDoc.ciudad" type="text" placeholder="Huánuco" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>

                    <!-- Campos solo para minuta con bien futuro -->
                    <template v-if="tipoDoc==='minuta-compraventa'">
                        <div style="grid-column:1/-1;"><label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px;"><input type="checkbox" v-model="formDoc.es_bien_futuro"> Es bien futuro (habilitación urbana en trámite)</label></div>
                        <template v-if="formDoc.es_bien_futuro">
                            <div style="grid-column:1/-1; background:#FFF7ED; border-radius:8px; padding:1rem; display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                                <p style="grid-column:1/-1; font-size:12px; font-weight:700; color:#C2410C; margin:0 0 4px;">PROYECTO DE HABILITACIÓN URBANA</p>
                                <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Descripción proyecto</label><input v-model="formDoc.proyecto_descripcion" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                                <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Municipalidad</label><input v-model="formDoc.proyecto_municipalidad" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                                <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">N° Expediente municipal</label><input v-model="formDoc.proyecto_expediente" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                                <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Fecha presentación</label><input v-model="formDoc.proyecto_fecha" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                                <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Arquitecto</label><input v-model="formDoc.proyecto_arquitecto" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                                <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Plazo (en letras)</label><input v-model="formDoc.plazo_anos" type="text" placeholder="tres" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                            </div>
                        
    <!-- MODAL EDITAR EXPEDIENTE -->
    <div v-if="modalEditar" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:300; display:flex; align-items:center; justify-content:center; padding:1rem;">
        <div style="background:white; border-radius:16px; width:700px; max-width:95vw; max-height:92vh; overflow-y:auto; display:flex; flex-direction:column;">
            <!-- Header -->
            <div style="padding:1.25rem 1.5rem; border-bottom:1px solid #E2E8F0; position:sticky; top:0; background:white; z-index:10; border-radius:16px 16px 0 0; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">✏️ Editar expediente</p>
                    <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ acto.numero_expediente }} — {{ labelTipo(acto.tipo_acto) }}</p>
                </div>
                <button @click="modalEditar=false" style="background:#F1F5F9; border:none; padding:6px 12px; border-radius:8px; cursor:pointer; font-size:16px;">✕</button>
            </div>

            <div style="padding:1.25rem 1.5rem; flex:1;">
                <!-- Datos generales -->
                <p style="font-size:12px; font-weight:700; color:#6366F1; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">📋 Datos generales</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:1.2rem;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Asunto *</label>
                        <input v-model="formEditar.asunto" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha ingreso</label>
                        <input v-model="formEditar.fecha_ingreso" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha entrega</label>
                        <input v-model="formEditar.fecha_entrega" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Monto a cobrar (S/)</label>
                        <input v-model="formEditar.monto_cobrar" type="number" step="0.01" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Observaciones</label>
                        <input v-model="formEditar.observaciones" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                </div>

                <!-- Datos específicos del tipo -->
                <div v-if="camposPlantillaEditar.length > 0" style="border-top:1px solid #F1F5F9; padding-top:1.2rem;">
                    <p style="font-size:12px; font-weight:700; color:#14B8A6; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">📋 Datos específicos</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <template v-for="campo in camposPlantillaEditar" :key="campo.key">
                            <div v-if="campo.tipo === 'seccion'" style="grid-column:1/-1; border-top:1px solid #E2E8F0; padding-top:10px; margin-top:4px;">
                                <p style="font-size:11px; font-weight:700; color:#0F766E; margin:0; text-transform:uppercase;">{{ campo.label }}</p>
                            </div>
                            <div v-else-if="campo.tipo === 'checkbox'" style="grid-column:1/-1;">
                                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:#374151;">
                                    <input type="checkbox" v-model="formDatosEditar[campo.key]" style="width:16px; height:16px;">
                                    {{ campo.label }}
                                </label>
                            </div>
                            <div v-else :style="campo.full ? 'grid-column:1/-1' : ''">
                                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">{{ campo.label }}</label>
                                <textarea v-if="campo.tipo === 'textarea'" v-model="formDatosEditar[campo.key]" :rows="campo.rows || 2"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                                <select v-else-if="campo.tipo === 'select'" v-model="formDatosEditar[campo.key]"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                                    <option value="">Seleccionar...</option>
                                    <option v-for="op in campo.opciones" :key="op" :value="op">{{ op }}</option>
                                </select>
                                <input v-else v-model="formDatosEditar[campo.key]" :type="campo.tipo || 'text'"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div style="padding:1rem 1.5rem; border-top:1px solid #E2E8F0; display:flex; justify-content:flex-end; gap:10px; position:sticky; bottom:0; background:white;">
                <button @click="modalEditar=false" style="padding:9px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">Cancelar</button>
                <button @click="guardarEdicion" style="padding:9px 20px; background:linear-gradient(135deg,#F59E0B,#D97706); color:white; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">💾 Guardar cambios</button>
            </div>
        </div>
    </div>

</template>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Descripción lote</label><input v-model="formDoc.lote_descripcion" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Área</label><input v-model="formDoc.lote_area" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div style="grid-column:1/-1;"><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Área en letras</label><input v-model="formDoc.lote_area_letras" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Frente colinda con</label><input v-model="formDoc.lindero_frente" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Medida frente (ml)</label><input v-model="formDoc.medida_frente" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Derecha colinda con</label><input v-model="formDoc.lindero_derecha" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Medida derecha (ml)</label><input v-model="formDoc.medida_derecha" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Izquierda colinda con</label><input v-model="formDoc.lindero_izquierda" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Medida izquierda (ml)</label><input v-model="formDoc.medida_izquierda" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Fondo colinda con</label><input v-model="formDoc.lindero_fondo" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Medida fondo (ml)</label><input v-model="formDoc.medida_fondo" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    
    <!-- MODAL EDITAR EXPEDIENTE -->
    <div v-if="modalEditar" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:300; display:flex; align-items:center; justify-content:center; padding:1rem;">
        <div style="background:white; border-radius:16px; width:700px; max-width:95vw; max-height:92vh; overflow-y:auto; display:flex; flex-direction:column;">
            <!-- Header -->
            <div style="padding:1.25rem 1.5rem; border-bottom:1px solid #E2E8F0; position:sticky; top:0; background:white; z-index:10; border-radius:16px 16px 0 0; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">✏️ Editar expediente</p>
                    <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ acto.numero_expediente }} — {{ labelTipo(acto.tipo_acto) }}</p>
                </div>
                <button @click="modalEditar=false" style="background:#F1F5F9; border:none; padding:6px 12px; border-radius:8px; cursor:pointer; font-size:16px;">✕</button>
            </div>

            <div style="padding:1.25rem 1.5rem; flex:1;">
                <!-- Datos generales -->
                <p style="font-size:12px; font-weight:700; color:#6366F1; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">📋 Datos generales</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:1.2rem;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Asunto *</label>
                        <input v-model="formEditar.asunto" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha ingreso</label>
                        <input v-model="formEditar.fecha_ingreso" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha entrega</label>
                        <input v-model="formEditar.fecha_entrega" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Monto a cobrar (S/)</label>
                        <input v-model="formEditar.monto_cobrar" type="number" step="0.01" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Observaciones</label>
                        <input v-model="formEditar.observaciones" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                </div>

                <!-- Datos específicos del tipo -->
                <div v-if="camposPlantillaEditar.length > 0" style="border-top:1px solid #F1F5F9; padding-top:1.2rem;">
                    <p style="font-size:12px; font-weight:700; color:#14B8A6; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">📋 Datos específicos</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <template v-for="campo in camposPlantillaEditar" :key="campo.key">
                            <div v-if="campo.tipo === 'seccion'" style="grid-column:1/-1; border-top:1px solid #E2E8F0; padding-top:10px; margin-top:4px;">
                                <p style="font-size:11px; font-weight:700; color:#0F766E; margin:0; text-transform:uppercase;">{{ campo.label }}</p>
                            </div>
                            <div v-else-if="campo.tipo === 'checkbox'" style="grid-column:1/-1;">
                                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:#374151;">
                                    <input type="checkbox" v-model="formDatosEditar[campo.key]" style="width:16px; height:16px;">
                                    {{ campo.label }}
                                </label>
                            </div>
                            <div v-else :style="campo.full ? 'grid-column:1/-1' : ''">
                                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">{{ campo.label }}</label>
                                <textarea v-if="campo.tipo === 'textarea'" v-model="formDatosEditar[campo.key]" :rows="campo.rows || 2"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                                <select v-else-if="campo.tipo === 'select'" v-model="formDatosEditar[campo.key]"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                                    <option value="">Seleccionar...</option>
                                    <option v-for="op in campo.opciones" :key="op" :value="op">{{ op }}</option>
                                </select>
                                <input v-else v-model="formDatosEditar[campo.key]" :type="campo.tipo || 'text'"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div style="padding:1rem 1.5rem; border-top:1px solid #E2E8F0; display:flex; justify-content:flex-end; gap:10px; position:sticky; bottom:0; background:white;">
                <button @click="modalEditar=false" style="padding:9px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">Cancelar</button>
                <button @click="guardarEdicion" style="padding:9px 20px; background:linear-gradient(135deg,#F59E0B,#D97706); color:white; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">💾 Guardar cambios</button>
            </div>
        </div>
    </div>

</template>

                    <!-- Antecedente registral (solo parte) -->
                    <div v-if="tipoDoc==='parte-compraventa'" style="grid-column:1/-1;"><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Antecedente registral (opcional)</label><textarea v-model="formDoc.antecedente_registral" rows="2" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box; resize:vertical;"></textarea></div>
                </div>
            </div>

            <!-- PRECIO Y PAGO -->
            <div style="border-top:1px solid #E2E8F0; padding-top:1rem; margin-bottom:1rem;">
                <p style="font-size:12px; font-weight:700; color:#1D4ED8; margin:0 0 8px;">PRECIO Y FORMA DE PAGO</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Precio total S/ *</label><input v-model="formDoc.precio_total" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Precio en letras *</label><input v-model="formDoc.precio_total_letras" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div style="grid-column:1/-1;"><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Detalle forma de pago *</label><textarea v-model="formDoc.forma_pago_detalle" rows="3" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box; resize:vertical;"></textarea></div>
                </div>
            </div>

            <!-- CAMPOS ESPECÍFICOS TESTIMONIO/PARTE -->
            <div v-if="tipoDoc!=='minuta-compraventa'" style="border-top:1px solid #E2E8F0; padding-top:1rem; margin-bottom:1rem;">
                <p style="font-size:12px; font-weight:700; color:#1D4ED8; margin:0 0 8px;">CONSTANCIAS Y CONCLUSIÓN</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Abogado nombre</label><input v-model="formDoc.abogado_nombre" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">CAU / Reg.</label><input v-model="formDoc.abogado_cau" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Fojas inicio</label><input v-model="formDoc.fojas_inicio" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Fojas fin</label><input v-model="formDoc.fojas_fin" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Serie papel inicio</label><input v-model="formDoc.papel_serie_inicio" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Serie papel fin</label><input v-model="formDoc.papel_serie_fin" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    <div style="grid-column:1/-1;"><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Tipo medio de pago</label><input v-model="formDoc.medios_pago_tipo" type="text" placeholder="DEPÓSITO EN CUENTA CÓDIGO 007" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>

                    <!-- Campos solo testimonio -->
                    <template v-if="tipoDoc==='testimonio-compraventa'">
                        <div style="grid-column:1/-1;"><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Medios de pago (descripción)</label><textarea v-model="formDoc.medios_pago_descripcion" rows="2" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box; resize:vertical;"></textarea></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Monto alcabala S/</label><input v-model="formDoc.alcabala_monto" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Fecha alcabala</label><input v-model="formDoc.alcabala_fecha" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                        <div style="grid-column:1/-1;"><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">N° Recibo alcabala</label><input v-model="formDoc.alcabala_recibo" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                    
    <!-- MODAL EDITAR EXPEDIENTE -->
    <div v-if="modalEditar" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:300; display:flex; align-items:center; justify-content:center; padding:1rem;">
        <div style="background:white; border-radius:16px; width:700px; max-width:95vw; max-height:92vh; overflow-y:auto; display:flex; flex-direction:column;">
            <!-- Header -->
            <div style="padding:1.25rem 1.5rem; border-bottom:1px solid #E2E8F0; position:sticky; top:0; background:white; z-index:10; border-radius:16px 16px 0 0; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">✏️ Editar expediente</p>
                    <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ acto.numero_expediente }} — {{ labelTipo(acto.tipo_acto) }}</p>
                </div>
                <button @click="modalEditar=false" style="background:#F1F5F9; border:none; padding:6px 12px; border-radius:8px; cursor:pointer; font-size:16px;">✕</button>
            </div>

            <div style="padding:1.25rem 1.5rem; flex:1;">
                <!-- Datos generales -->
                <p style="font-size:12px; font-weight:700; color:#6366F1; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">📋 Datos generales</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:1.2rem;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Asunto *</label>
                        <input v-model="formEditar.asunto" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha ingreso</label>
                        <input v-model="formEditar.fecha_ingreso" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha entrega</label>
                        <input v-model="formEditar.fecha_entrega" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Monto a cobrar (S/)</label>
                        <input v-model="formEditar.monto_cobrar" type="number" step="0.01" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Observaciones</label>
                        <input v-model="formEditar.observaciones" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                </div>

                <!-- Datos específicos del tipo -->
                <div v-if="camposPlantillaEditar.length > 0" style="border-top:1px solid #F1F5F9; padding-top:1.2rem;">
                    <p style="font-size:12px; font-weight:700; color:#14B8A6; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">📋 Datos específicos</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <template v-for="campo in camposPlantillaEditar" :key="campo.key">
                            <div v-if="campo.tipo === 'seccion'" style="grid-column:1/-1; border-top:1px solid #E2E8F0; padding-top:10px; margin-top:4px;">
                                <p style="font-size:11px; font-weight:700; color:#0F766E; margin:0; text-transform:uppercase;">{{ campo.label }}</p>
                            </div>
                            <div v-else-if="campo.tipo === 'checkbox'" style="grid-column:1/-1;">
                                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:#374151;">
                                    <input type="checkbox" v-model="formDatosEditar[campo.key]" style="width:16px; height:16px;">
                                    {{ campo.label }}
                                </label>
                            </div>
                            <div v-else :style="campo.full ? 'grid-column:1/-1' : ''">
                                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">{{ campo.label }}</label>
                                <textarea v-if="campo.tipo === 'textarea'" v-model="formDatosEditar[campo.key]" :rows="campo.rows || 2"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                                <select v-else-if="campo.tipo === 'select'" v-model="formDatosEditar[campo.key]"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                                    <option value="">Seleccionar...</option>
                                    <option v-for="op in campo.opciones" :key="op" :value="op">{{ op }}</option>
                                </select>
                                <input v-else v-model="formDatosEditar[campo.key]" :type="campo.tipo || 'text'"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div style="padding:1rem 1.5rem; border-top:1px solid #E2E8F0; display:flex; justify-content:flex-end; gap:10px; position:sticky; bottom:0; background:white;">
                <button @click="modalEditar=false" style="padding:9px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">Cancelar</button>
                <button @click="guardarEdicion" style="padding:9px 20px; background:linear-gradient(135deg,#F59E0B,#D97706); color:white; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">💾 Guardar cambios</button>
            </div>
        </div>
    </div>

</template>

                    <!-- Anotación solo parte -->
                    <div v-if="tipoDoc==='parte-compraventa'" style="grid-column:1/-1;"><label style="font-size:11px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">Anotación</label><input v-model="formDoc.anotacion" type="text" style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; box-sizing:border-box;"></div>
                </div>
            </div>

            </div><!-- end padding -->

            <!-- FOOTER -->
            <div style="padding:1rem 1.5rem; border-top:1px solid #E2E8F0; display:flex; justify-content:flex-end; gap:12px; position:sticky; bottom:0; background:white; border-radius:0 0 16px 16px;">
                <button @click="modalDoc=false" style="padding:10px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">Cancelar</button>
                <button @click="generarDoc" :disabled="generandoDoc"
                    :style="{padding:'10px 20px', border:'none', borderRadius:'8px', fontSize:'13px', fontWeight:'600', cursor:'pointer',
                        background: tiposDocumento.find(t=>t.value===tipoDoc)?.color || '#1D4ED8', color:'white'}">
                    {{ generandoDoc ? '⏳ Generando...' : '📄 Generar PDF' }}
                </button>
            </div>
        </div>
    </div>

    </AppLayout>

    <!-- MODAL EDITAR EXPEDIENTE -->
    <div v-if="modalEditar" style="position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:300; display:flex; align-items:center; justify-content:center; padding:1rem;">
        <div style="background:white; border-radius:16px; width:700px; max-width:95vw; max-height:92vh; overflow-y:auto; display:flex; flex-direction:column;">
            <!-- Header -->
            <div style="padding:1.25rem 1.5rem; border-bottom:1px solid #E2E8F0; position:sticky; top:0; background:white; z-index:10; border-radius:16px 16px 0 0; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">✏️ Editar expediente</p>
                    <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ acto.numero_expediente }} — {{ labelTipo(acto.tipo_acto) }}</p>
                </div>
                <button @click="modalEditar=false" style="background:#F1F5F9; border:none; padding:6px 12px; border-radius:8px; cursor:pointer; font-size:16px;">✕</button>
            </div>

            <div style="padding:1.25rem 1.5rem; flex:1;">
                <!-- Datos generales -->
                <p style="font-size:12px; font-weight:700; color:#6366F1; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">📋 Datos generales</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:1.2rem;">
                    <div style="grid-column:1/-1;">
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Asunto *</label>
                        <input v-model="formEditar.asunto" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha ingreso</label>
                        <input v-model="formEditar.fecha_ingreso" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha entrega</label>
                        <input v-model="formEditar.fecha_entrega" type="date" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Monto a cobrar (S/)</label>
                        <input v-model="formEditar.monto_cobrar" type="number" step="0.01" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                    <div>
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Observaciones</label>
                        <input v-model="formEditar.observaciones" type="text" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    </div>
                </div>

                <!-- Datos específicos del tipo -->
                <div v-if="camposPlantillaEditar.length > 0" style="border-top:1px solid #F1F5F9; padding-top:1.2rem;">
                    <p style="font-size:12px; font-weight:700; color:#14B8A6; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">📋 Datos específicos</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <template v-for="campo in camposPlantillaEditar" :key="campo.key">
                            <div v-if="campo.tipo === 'seccion'" style="grid-column:1/-1; border-top:1px solid #E2E8F0; padding-top:10px; margin-top:4px;">
                                <p style="font-size:11px; font-weight:700; color:#0F766E; margin:0; text-transform:uppercase;">{{ campo.label }}</p>
                            </div>
                            <div v-else-if="campo.tipo === 'checkbox'" style="grid-column:1/-1;">
                                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:#374151;">
                                    <input type="checkbox" v-model="formDatosEditar[campo.key]" style="width:16px; height:16px;">
                                    {{ campo.label }}
                                </label>
                            </div>
                            <div v-else :style="campo.full ? 'grid-column:1/-1' : ''">
                                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">{{ campo.label }}</label>
                                <textarea v-if="campo.tipo === 'textarea'" v-model="formDatosEditar[campo.key]" :rows="campo.rows || 2"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                                <select v-else-if="campo.tipo === 'select'" v-model="formDatosEditar[campo.key]"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                                    <option value="">Seleccionar...</option>
                                    <option v-for="op in campo.opciones" :key="op" :value="op">{{ op }}</option>
                                </select>
                                <input v-else v-model="formDatosEditar[campo.key]" :type="campo.tipo || 'text'"
                                    style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div style="padding:1rem 1.5rem; border-top:1px solid #E2E8F0; display:flex; justify-content:flex-end; gap:10px; position:sticky; bottom:0; background:white;">
                <button @click="modalEditar=false" style="padding:9px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">Cancelar</button>
                <button @click="guardarEdicion" style="padding:9px 20px; background:linear-gradient(135deg,#F59E0B,#D97706); color:white; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">💾 Guardar cambios</button>
            </div>
        </div>
    </div>

</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PartesIntervinientes from '@/Components/Notaria/PartesIntervinientes.vue'

const props = defineProps({
    acto:  { type: Object, default: () => ({}) },
    datos: { type: Object, default: () => ({}) },
    vendedor:{ type: Object, default: () => ({}) },
})

const modalDoc    = ref(false)
const generandoDoc = ref(false)
const tipoDoc     = ref('minuta-compraventa')

const tiposDocumento = [
    { value: 'minuta-compraventa',    label: 'Minuta',    icon: '📄', color: '#F97316', bg: '#FFF7ED' },
    { value: 'testimonio-compraventa', label: 'Testimonio', icon: '📜', color: '#0F766E', bg: '#F0FDFA' },
    { value: 'parte-compraventa',     label: 'Parte Notarial', icon: '📋', color: '#7C3AED', bg: '#F5F3FF' },
]

const formDoc = ref({
    // Comunes
    num_instrumento: '', num_minuta: '', fecha_letras: '', fecha_minuta: '', fecha_firma: '', ciudad: 'Huánuco',
    // Vendedor empresa
    vendedor_tipo: 'empresa',
    vendedor_razon_social: '', vendedor_ruc: '', vendedor_domicilio: '', vendedor_partida_registral: '',
    representante_cargo: 'Gerente General', representante_nombre: '', representante_dni: '',
    representante_estado_civil: 'soltero', representante_profesion: '', representante_domicilio: '',
    // Vendedor persona
    vendedor_nombre: '', vendedor_dni: '', vendedor_estado_civil: '', vendedor_profesion: '',
    // Comprador 1
    comprador_nombre: '', comprador_dni: '', comprador_estado_civil: '', comprador_profesion: '', comprador_domicilio: '',
    // Comprador 2
    comprador2_nombre: '', comprador2_dni: '', comprador2_estado_civil: '', comprador2_profesion: '', comprador2_domicilio: '',
    // Predio
    predio_descripcion: '', predio_partida: '', antecedente_registral: '',
    // Bien futuro (minuta)
    es_bien_futuro: false,
    proyecto_descripcion: '', proyecto_municipalidad: '', proyecto_expediente: '', proyecto_fecha: '', proyecto_arquitecto: '', plazo_anos: 'tres',
    lote_descripcion: '', lote_area: '', lote_area_letras: '',
    lindero_frente: '', medida_frente: '', lindero_derecha: '', medida_derecha: '',
    lindero_izquierda: '', medida_izquierda: '', lindero_fondo: '', medida_fondo: '',
    // Precio
    precio_total: '', precio_total_letras: '', forma_pago_detalle: '',
    // Constancias (testimonio/parte)
    abogado_nombre: '', abogado_cau: '',
    fojas_inicio: '', fojas_fin: '', papel_serie_inicio: '', papel_serie_fin: '',
    medios_pago_tipo: 'DEPÓSITO EN CUENTA',
    // Solo testimonio
    medios_pago_descripcion: '', alcabala_monto: '', alcabala_fecha: '', alcabala_recibo: '',
    // Solo parte
    anotacion: '',
})

function fechaALetras(fecha) {
    const unidades = ['','UNO','DOS','TRES','CUATRO','CINCO','SEIS','SIETE','OCHO','NUEVE','DIEZ',
        'ONCE','DOCE','TRECE','CATORCE','QUINCE','DIECISÉIS','DIECISIETE','DIECIOCHO','DIECINUEVE',
        'VEINTE','VEINTIÚN','VEINTIDÓS','VEINTITRÉS','VEINTICUATRO','VEINTICINCO','VEINTISÉIS',
        'VEINTISIETE','VEINTIOCHO','VEINTINUEVE','TREINTA','TREINTA Y UNO']
    const meses = ['','ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO',
        'JULIO','AGOSTO','SETIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE']
    const anios = {
        2024:'DOS MIL VEINTICUATRO', 2025:'DOS MIL VEINTICINCO',
        2026:'DOS MIL VEINTISÉIS', 2027:'DOS MIL VEINTISIETE',
        2028:'DOS MIL VEINTIOCHO', 2029:'DOS MIL VEINTINUEVE',
        2030:'DOS MIL TREINTA'
    }
    if (!fecha) return ''
    const partes = fecha.split(/[-\/]/)
    if (partes.length !== 3) return ''
    const dia = parseInt(partes[0])
    const mes = parseInt(partes[1])
    const anio = parseInt(partes[2])
    if (!dia || !mes || !anio) return ''
    const diaLetras = unidades[dia] || ''
    const mesLetras = meses[mes] || ''
    const anioLetras = anios[anio] || String(anio)
    return diaLetras + ' DÍAS DEL MES DE ' + mesLetras + ' DEL ' + anioLetras
}

function abrirModalDoc() {
    const d = props.datos || {}
    const v = props.vendedor || {}

    // Cargar todos los datos guardados del expediente
    Object.keys(formDoc.value).forEach(k => {
        if (d[k] !== undefined && d[k] !== '') formDoc.value[k] = d[k]
    })

    // Detectar si el vendedor es persona natural por los datos guardados
    if (d.vendedor_nombre && d.vendedor_nombre !== '') {
        formDoc.value.vendedor_tipo = 'persona'
        formDoc.value.vendedor_nombre = d.vendedor_nombre
        formDoc.value.vendedor_dni = d.vendedor_dni || ''
        formDoc.value.vendedor_estado_civil = d.vendedor_estado_civil || ''
        formDoc.value.vendedor_profesion = d.vendedor_profesion || ''
        formDoc.value.vendedor_domicilio = d.vendedor_domicilio || ''
    } else {
        // Usar datos de empresa desde config
        if (v.vendedor_tipo) formDoc.value.vendedor_tipo = v.vendedor_tipo
        if (v.vendedor_razon_social) formDoc.value.vendedor_razon_social = v.vendedor_razon_social
        if (v.vendedor_ruc) formDoc.value.vendedor_ruc = v.vendedor_ruc
        if (v.vendedor_domicilio) formDoc.value.vendedor_domicilio = v.vendedor_domicilio
        if (v.representante_nombre) formDoc.value.representante_nombre = v.representante_nombre
        if (v.representante_dni) formDoc.value.representante_dni = v.representante_dni
        if (v.representante_cargo) formDoc.value.representante_cargo = v.representante_cargo
    }

    // Mapear campos con nombres distintos
    if (d.predio_descripcion && !formDoc.value.lote_descripcion) formDoc.value.lote_descripcion = d.predio_descripcion
    if (d.conyuge_vendedor) formDoc.value.conyuge_vendedor = d.conyuge_vendedor
    if (d.conyuge_vendedor_dni) formDoc.value.conyuge_vendedor_dni = d.conyuge_vendedor_dni

    // Pre-cargar precio desde monto del acto si no está guardado
    if (!formDoc.value.precio_total) formDoc.value.precio_total = String(props.acto.monto_cobrar || '')

    // Fecha de hoy por defecto
    const hoy = new Date()
    const dd = String(hoy.getDate()).padStart(2,'0')
    const mm = String(hoy.getMonth()+1).padStart(2,'0')
    const yyyy = hoy.getFullYear()
    if (!formDoc.value.fecha_firma) {
        formDoc.value.fecha_firma = dd + '-' + mm + '-' + yyyy
        formDoc.value.fecha_letras = fechaALetras(formDoc.value.fecha_firma) || ''
        formDoc.value.fecha_minuta = dd + ' de ' + ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'][hoy.getMonth()] + ' de ' + yyyy
    }

    modalDoc.value = true
}

async function generarDoc() {
    generandoDoc.value = true
    try {
        const csrf = await fetch('/sanctum/csrf-cookie').then(() => document.querySelector('meta[name="csrf-token"]')?.content)
        const res = await fetch('/notaria/actos/' + props.acto.id + '/generar/' + tipoDoc.value, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ ...formDoc.value, guardar_datos: true })
        })
        if (res.status === 419) { alert('Sesión expirada'); window.location.reload(); return }
        if (res.ok) {
            const blob = await res.blob()
            const url = URL.createObjectURL(blob)
            const a = document.createElement('a')
            a.href = url
            const nombres = { 'minuta-compraventa': 'Minuta', 'testimonio-compraventa': 'Testimonio', 'parte-compraventa': 'Parte' }
            a.download = (nombres[tipoDoc.value] || 'Documento') + '-' + props.acto.numero_expediente + '.pdf'
            a.click()
            URL.revokeObjectURL(url)
            modalDoc.value = false
        } else { alert('Error al generar el documento') }
    } catch(e) { alert('Error: ' + e.message) }
    generandoDoc.value = false
}

// Plantillas por tipo de acto
const plantillas = {
    // ── CAMPOS COMUNES REUTILIZABLES ──
    // comprador simple
    _comprador: [
        { key: 'sec_comprador', label: '👤 DATOS DEL COMPRADOR', tipo: 'seccion' },
        { key: 'comprador_nombre',       label: 'Nombre completo *',   tipo: 'text', full: true },
        { key: 'comprador_dni',          label: 'DNI *',               tipo: 'text' },
        { key: 'comprador_estado_civil', label: 'Estado civil',        tipo: 'select', opciones: ['soltero','casado','viudo','divorciado'] },
        { key: 'comprador_profesion',    label: 'Profesión/Ocupación', tipo: 'text' },
        { key: 'comprador_domicilio',    label: 'Domicilio *',         tipo: 'text', full: true },
    ],

    // ── TRANSFERENCIAS INMUEBLES ──
    compra_venta: [
        { key: 'sec_vendedor', label: '👤 DATOS DEL VENDEDOR', tipo: 'seccion' },
        { key: 'vendedor_nombre',        label: 'Nombre completo vendedor *', tipo: 'text', full: true },
        { key: 'vendedor_dni',           label: 'DNI vendedor *',             tipo: 'text' },
        { key: 'vendedor_estado_civil',  label: 'Estado civil',               tipo: 'select', opciones: ['soltero','casado','viudo','divorciado'] },
        { key: 'vendedor_profesion',     label: 'Profesión/Ocupación',        tipo: 'text' },
        { key: 'vendedor_domicilio',     label: 'Domicilio vendedor *',       tipo: 'text', full: true },
        { key: 'conyuge_vendedor',       label: 'Cónyuge (si aplica)',        tipo: 'text', full: true },
        { key: 'conyuge_vendedor_dni',   label: 'DNI cónyuge vendedor',       tipo: 'text' },
        { key: 'sec_comprador', label: '👤 DATOS DEL COMPRADOR', tipo: 'seccion' },
        { key: 'comprador_nombre',       label: 'Nombre completo comprador *',tipo: 'text', full: true },
        { key: 'comprador_dni',          label: 'DNI comprador *',            tipo: 'text' },
        { key: 'comprador_estado_civil', label: 'Estado civil',               tipo: 'select', opciones: ['soltero','casado','viudo','divorciado'] },
        { key: 'comprador_profesion',    label: 'Profesión/Ocupación',        tipo: 'text' },
        { key: 'comprador_domicilio',    label: 'Domicilio comprador *',      tipo: 'text', full: true },
        { key: 'sec_predio', label: '🏠 DATOS DEL PREDIO', tipo: 'seccion' },
        { key: 'predio_descripcion',     label: 'Descripción del predio *',   tipo: 'textarea', full: true, rows: 2 },
        { key: 'predio_partida',         label: 'Partida registral *',        tipo: 'text' },
        { key: 'lote_area',              label: 'Área (m2)',                  tipo: 'text' },
        { key: 'lote_area_letras',       label: 'Área en letras',             tipo: 'text', full: true },
        { key: 'lindero_frente',         label: 'Frente colinda con',         tipo: 'text' },
        { key: 'medida_frente',          label: 'Medida frente (ml)',         tipo: 'text' },
        { key: 'lindero_derecha',        label: 'Derecha colinda con',        tipo: 'text' },
        { key: 'medida_derecha',         label: 'Medida derecha (ml)',        tipo: 'text' },
        { key: 'lindero_izquierda',      label: 'Izquierda colinda con',      tipo: 'text' },
        { key: 'medida_izquierda',       label: 'Medida izquierda (ml)',      tipo: 'text' },
        { key: 'lindero_fondo',          label: 'Fondo colinda con',          tipo: 'text' },
        { key: 'medida_fondo',           label: 'Medida fondo (ml)',          tipo: 'text' },
        { key: 'sec_precio', label: '💰 PRECIO Y PAGO', tipo: 'seccion' },
        { key: 'precio_total',           label: 'Precio total (S/) *',        tipo: 'number' },
        { key: 'precio_total_letras',    label: 'Precio en letras *',         tipo: 'text', full: true },
        { key: 'forma_pago_detalle',     label: 'Detalle forma de pago *',    tipo: 'textarea', full: true, rows: 2 },
    ],
    compra_venta_bien_futuro: [
        { key: 'sec_vendedor', label: '👤 DATOS DEL VENDEDOR (EMPRESA)', tipo: 'seccion' },
        { key: 'vendedor_razon_social',  label: 'Razón social vendedor *',    tipo: 'text', full: true },
        { key: 'vendedor_ruc',           label: 'RUC vendedor *',             tipo: 'text' },
        { key: 'vendedor_domicilio',     label: 'Domicilio vendedor',         tipo: 'text', full: true },
        { key: 'vendedor_partida',       label: 'Partida registral empresa',  tipo: 'text' },
        { key: 'rep_nombre',             label: 'Representante legal *',      tipo: 'text', full: true },
        { key: 'rep_dni',                label: 'DNI representante',          tipo: 'text' },
        { key: 'sec_comprador', label: '👤 DATOS DEL COMPRADOR', tipo: 'seccion' },
        { key: 'comprador_nombre',       label: 'Nombre completo *',          tipo: 'text', full: true },
        { key: 'comprador_dni',          label: 'DNI *',                      tipo: 'text' },
        { key: 'comprador_estado_civil', label: 'Estado civil',               tipo: 'select', opciones: ['soltero','casado','viudo','divorciado'] },
        { key: 'comprador_domicilio',    label: 'Domicilio *',                tipo: 'text', full: true },
        { key: 'comprador2_nombre',      label: 'Comprador 2 (si aplica)',    tipo: 'text', full: true },
        { key: 'comprador2_dni',         label: 'DNI comprador 2',            tipo: 'text' },
        { key: 'sec_predio', label: '🏗️ DATOS DEL PROYECTO / LOTE', tipo: 'seccion' },
        { key: 'proyecto_nombre',        label: 'Nombre del proyecto *',      tipo: 'text', full: true },
        { key: 'expediente_hab',         label: 'Expediente habilitación',    tipo: 'text' },
        { key: 'lote_manzana',           label: 'Manzana',                    tipo: 'text' },
        { key: 'lote_numero',            label: 'N° de lote',                 tipo: 'text' },
        { key: 'lote_area',              label: 'Área (m2)',                  tipo: 'text' },
        { key: 'lindero_frente',         label: 'Frente colinda con',         tipo: 'text' },
        { key: 'medida_frente',          label: 'Medida frente (ml)',         tipo: 'text' },
        { key: 'lindero_derecha',        label: 'Derecha colinda con',        tipo: 'text' },
        { key: 'medida_derecha',         label: 'Medida derecha (ml)',        tipo: 'text' },
        { key: 'lindero_izquierda',      label: 'Izquierda colinda con',      tipo: 'text' },
        { key: 'medida_izquierda',       label: 'Medida izquierda (ml)',      tipo: 'text' },
        { key: 'lindero_fondo',          label: 'Fondo colinda con',          tipo: 'text' },
        { key: 'medida_fondo',           label: 'Medida fondo (ml)',          tipo: 'text' },
        { key: 'sec_precio', label: '💰 PRECIO Y PAGO', tipo: 'seccion' },
        { key: 'precio_comprador1',      label: 'Precio comprador 1 (S/)',    tipo: 'number' },
        { key: 'precio_comprador2',      label: 'Precio comprador 2 (S/)',    tipo: 'number' },
        { key: 'forma_pago_detalle',     label: 'Detalle depósitos bancarios',tipo: 'textarea', full: true, rows: 3 },
        { key: 'plazo_habilitacion',     label: 'Plazo obtención licencia',   tipo: 'text' },
    ],
    compra_venta_hipoteca: [
        { key: 'sec_vendedor', label: '👤 DATOS DEL VENDEDOR', tipo: 'seccion' },
        { key: 'vendedor_nombre',        label: 'Nombre completo vendedor *', tipo: 'text', full: true },
        { key: 'vendedor_dni',           label: 'DNI vendedor *',             tipo: 'text' },
        { key: 'vendedor_domicilio',     label: 'Domicilio vendedor',         tipo: 'text', full: true },
        { key: 'sec_comprador', label: '👤 DATOS DEL COMPRADOR', tipo: 'seccion' },
        { key: 'comprador_nombre',       label: 'Nombre completo comprador *',tipo: 'text', full: true },
        { key: 'comprador_dni',          label: 'DNI comprador *',            tipo: 'text' },
        { key: 'comprador_domicilio',    label: 'Domicilio comprador',        tipo: 'text', full: true },
        { key: 'sec_predio', label: '🏠 DATOS DEL PREDIO', tipo: 'seccion' },
        { key: 'predio_descripcion',     label: 'Descripción del predio *',   tipo: 'textarea', full: true, rows: 2 },
        { key: 'predio_partida',         label: 'Partida registral *',        tipo: 'text' },
        { key: 'lote_area',              label: 'Área (m2)',                  tipo: 'text' },
        { key: 'lindero_frente',         label: 'Frente colinda con',         tipo: 'text' },
        { key: 'medida_frente',          label: 'Medida frente (ml)',         tipo: 'text' },
        { key: 'lindero_derecha',        label: 'Derecha colinda con',        tipo: 'text' },
        { key: 'medida_derecha',         label: 'Medida derecha (ml)',        tipo: 'text' },
        { key: 'lindero_izquierda',      label: 'Izquierda colinda con',      tipo: 'text' },
        { key: 'medida_izquierda',       label: 'Medida izquierda (ml)',      tipo: 'text' },
        { key: 'lindero_fondo',          label: 'Fondo colinda con',          tipo: 'text' },
        { key: 'medida_fondo',           label: 'Medida fondo (ml)',          tipo: 'text' },
        { key: 'sec_precio', label: '💰 PRECIO Y PAGO', tipo: 'seccion' },
        { key: 'precio_total',           label: 'Precio total (S/) *',        tipo: 'number' },
        { key: 'precio_total_letras',    label: 'Precio en letras',           tipo: 'text', full: true },
        { key: 'forma_pago_detalle',     label: 'Detalle forma de pago',      tipo: 'textarea', full: true, rows: 2 },
        { key: 'sec_hipoteca', label: '🏦 DATOS DE LA HIPOTECA', tipo: 'seccion' },
        { key: 'monto_hipoteca',         label: 'Monto hipoteca (S/) *',      tipo: 'number' },
        { key: 'monto_hipoteca_letras',  label: 'Monto en letras',            tipo: 'text', full: true },
        { key: 'plazo_hipoteca',         label: 'Plazo de pago',              tipo: 'text' },
        { key: 'tasa_interes',           label: 'Tasa de interés',            tipo: 'text' },
    ],
    compra_venta_alicuotas: [
        { key: 'sec_vendedor', label: '👤 DATOS DEL VENDEDOR', tipo: 'seccion' },
        { key: 'vendedor_nombre',        label: 'Nombre completo vendedor *', tipo: 'text', full: true },
        { key: 'vendedor_dni',           label: 'DNI vendedor *',             tipo: 'text' },
        { key: 'vendedor_domicilio',     label: 'Domicilio vendedor',         tipo: 'text', full: true },
        { key: 'sec_comprador', label: '👤 DATOS DEL COMPRADOR', tipo: 'seccion' },
        { key: 'comprador_nombre',       label: 'Nombre completo comprador *',tipo: 'text', full: true },
        { key: 'comprador_dni',          label: 'DNI comprador *',            tipo: 'text' },
        { key: 'comprador_domicilio',    label: 'Domicilio comprador',        tipo: 'text', full: true },
        { key: 'sec_predio', label: '📐 DATOS DE LAS ALÍCUOTAS', tipo: 'seccion' },
        { key: 'porcentaje_alicuota',    label: 'Porcentaje alícuota *',      tipo: 'text' },
        { key: 'predio_descripcion',     label: 'Descripción del predio *',   tipo: 'textarea', full: true, rows: 2 },
        { key: 'predio_partida',         label: 'Partida registral *',        tipo: 'text' },
        { key: 'area_representada',      label: 'Área representada (m2)',     tipo: 'text' },
        { key: 'sec_precio', label: '💰 PRECIO Y PAGO', tipo: 'seccion' },
        { key: 'precio_total',           label: 'Precio total (S/) *',        tipo: 'number' },
        { key: 'forma_pago_detalle',     label: 'Detalle depósito bancario',  tipo: 'textarea', full: true, rows: 2 },
    ],
    aclaracion_compra_venta: [
        { key: 'sec_vendedor', label: '👤 DATOS DEL VENDEDOR', tipo: 'seccion' },
        { key: 'vendedor_nombre',        label: 'Nombre completo vendedor *', tipo: 'text', full: true },
        { key: 'vendedor_dni',           label: 'DNI vendedor *',             tipo: 'text' },
        { key: 'sec_comprador', label: '👤 DATOS DEL COMPRADOR', tipo: 'seccion' },
        { key: 'comprador_nombre',       label: 'Nombre completo comprador *',tipo: 'text', full: true },
        { key: 'comprador_dni',          label: 'DNI comprador *',            tipo: 'text' },
        { key: 'sec_aclaracion', label: '🔍 DATOS DE LA ACLARACIÓN', tipo: 'seccion' },
        { key: 'escritura_original',     label: 'N° escritura original *',    tipo: 'text' },
        { key: 'fecha_escritura_orig',   label: 'Fecha escritura original',   tipo: 'text' },
        { key: 'motivo_aclaracion',      label: 'Motivo de la aclaración *',  tipo: 'textarea', full: true, rows: 3 },
        { key: 'predio_partida',         label: 'Partida registral predio',   tipo: 'text' },
    ],
    ratificacion_compra_venta: [
        { key: 'sec_otorgante', label: '👤 DATOS DEL OTORGANTE', tipo: 'seccion' },
        { key: 'otorgante_nombre',       label: 'Nombre otorgante *',         tipo: 'text', full: true },
        { key: 'otorgante_dni',          label: 'DNI otorgante *',            tipo: 'text' },
        { key: 'sec_adquiriente', label: '👤 DATOS DEL ADQUIRIENTE', tipo: 'seccion' },
        { key: 'adquiriente_nombre',     label: 'Nombre adquiriente *',       tipo: 'text', full: true },
        { key: 'adquiriente_dni',        label: 'DNI adquiriente *',          tipo: 'text' },
        { key: 'sec_ratificacion', label: '✅ DATOS DE LA RATIFICACIÓN', tipo: 'seccion' },
        { key: 'escritura_original',     label: 'N° escritura a ratificar *', tipo: 'text' },
        { key: 'fecha_escritura_orig',   label: 'Fecha escritura original',   tipo: 'text' },
        { key: 'predio_descripcion',     label: 'Descripción del predio *',   tipo: 'textarea', full: true, rows: 2 },
        { key: 'predio_partida',         label: 'Partida registral',          tipo: 'text' },
        { key: 'interviniente_nombre',   label: 'Interviniente (si aplica)',  tipo: 'text', full: true },
        { key: 'interviniente_dni',      label: 'DNI interviniente',          tipo: 'text' },
    ],
    contrato_preparatorio: [
        { key: 'sec_vendedor', label: '👤 PROMITENTE VENDEDOR', tipo: 'seccion' },
        { key: 'vendedor_razon_social',  label: 'Razón social / Nombre *',    tipo: 'text', full: true },
        { key: 'vendedor_ruc',           label: 'RUC (si empresa)',           tipo: 'text' },
        { key: 'rep_nombre',             label: 'Representante legal',        tipo: 'text', full: true },
        { key: 'rep_dni',                label: 'DNI representante',          tipo: 'text' },
        { key: 'sec_comprador', label: '👤 PROMITENTE COMPRADOR', tipo: 'seccion' },
        { key: 'comprador_nombre',       label: 'Nombre completo *',          tipo: 'text', full: true },
        { key: 'comprador_dni',          label: 'DNI *',                      tipo: 'text' },
        { key: 'comprador_domicilio',    label: 'Domicilio',                  tipo: 'text', full: true },
        { key: 'sec_predio', label: '🏠 DATOS DEL PREDIO / LOTE', tipo: 'seccion' },
        { key: 'proyecto_nombre',        label: 'Nombre proyecto/predio *',   tipo: 'text', full: true },
        { key: 'lote_manzana',           label: 'Manzana',                    tipo: 'text' },
        { key: 'lote_numero',            label: 'N° de lote',                 tipo: 'text' },
        { key: 'lote_area',              label: 'Área (m2)',                  tipo: 'text' },
        { key: 'sec_precio', label: '💰 PRECIO Y CONDICIONES', tipo: 'seccion' },
        { key: 'precio_total',           label: 'Precio total (S/) *',        tipo: 'number' },
        { key: 'forma_pago_detalle',     label: 'Detalle de pagos/cuotas',    tipo: 'textarea', full: true, rows: 3 },
        { key: 'plazo_formalizacion',    label: 'Plazo para formalizar',      tipo: 'text' },
    ],
    adjudicacion: [
        { key: 'sec_adjudicante', label: '🏛️ DATOS DEL ADJUDICANTE', tipo: 'seccion' },
        { key: 'adjudicante_nombre',     label: 'Nombre/Razón social *',      tipo: 'text', full: true },
        { key: 'adjudicante_partida',    label: 'Partida registral entidad',  tipo: 'text' },
        { key: 'rep_nombre',             label: 'Representante / Presidente *',tipo: 'text', full: true },
        { key: 'rep_dni',                label: 'DNI representante *',        tipo: 'text' },
        { key: 'sec_adjudicatario', label: '👤 DATOS DEL ADJUDICATARIO', tipo: 'seccion' },
        { key: 'adjudicatario_nombre',   label: 'Nombre adjudicatario *',     tipo: 'text', full: true },
        { key: 'adjudicatario_dni',      label: 'DNI adjudicatario *',        tipo: 'text' },
        { key: 'adjudicatario_domicilio',label: 'Domicilio adjudicatario',    tipo: 'text', full: true },
        { key: 'sec_predio', label: '🏠 DATOS DEL PREDIO', tipo: 'seccion' },
        { key: 'predio_descripcion',     label: 'Descripción del predio *',   tipo: 'textarea', full: true, rows: 2 },
        { key: 'predio_partida',         label: 'Partida registral predio *', tipo: 'text' },
        { key: 'valor_predio',           label: 'Valor del predio (S/)',      tipo: 'number' },
        { key: 'tipo_adjudicacion',      label: 'Tipo de adjudicación',       tipo: 'select', opciones: ['Gratuita','Onerosa'] },
    ],
    rectificacion_area: [
        { key: 'sec_otorgante', label: '👤 DATOS DEL PROPIETARIO', tipo: 'seccion' },
        { key: 'propietario_nombre',     label: 'Nombre propietario *',       tipo: 'text', full: true },
        { key: 'propietario_dni',        label: 'DNI propietario *',          tipo: 'text' },
        { key: 'propietario_domicilio',  label: 'Domicilio',                  tipo: 'text', full: true },
        { key: 'sec_predio', label: '📏 DATOS DE LA RECTIFICACIÓN', tipo: 'seccion' },
        { key: 'predio_descripcion',     label: 'Descripción del predio *',   tipo: 'textarea', full: true, rows: 2 },
        { key: 'predio_partida',         label: 'Partida registral *',        tipo: 'text' },
        { key: 'area_registral',         label: 'Área registral actual (m2)', tipo: 'text' },
        { key: 'area_real',              label: 'Área real corregida (m2) *', tipo: 'text' },
        { key: 'perimetro_real',         label: 'Perímetro real (ml)',        tipo: 'text' },
        { key: 'lindero_frente',         label: 'Frente colinda con',         tipo: 'text' },
        { key: 'medida_frente',          label: 'Medida frente (ml)',         tipo: 'text' },
        { key: 'lindero_derecha',        label: 'Derecha colinda con',        tipo: 'text' },
        { key: 'medida_derecha',         label: 'Medida derecha (ml)',        tipo: 'text' },
        { key: 'lindero_izquierda',      label: 'Izquierda colinda con',      tipo: 'text' },
        { key: 'medida_izquierda',       label: 'Medida izquierda (ml)',      tipo: 'text' },
        { key: 'lindero_fondo',          label: 'Fondo colinda con',          tipo: 'text' },
        { key: 'medida_fondo',           label: 'Medida fondo (ml)',          tipo: 'text' },
        { key: 'motivo_rectificacion',   label: 'Motivo rectificación *',     tipo: 'textarea', full: true, rows: 2 },
    ],
    particion: [
        { key: 'sec_copropietarios', label: '👥 COPROPIETARIOS', tipo: 'seccion' },
        { key: 'copropietarios',         label: 'Lista de copropietarios *',  tipo: 'textarea', full: true, rows: 4 },
        { key: 'sec_predio', label: '🏠 DATOS DEL PREDIO A PARTIR', tipo: 'seccion' },
        { key: 'predio_descripcion',     label: 'Descripción del predio *',   tipo: 'textarea', full: true, rows: 2 },
        { key: 'predio_partida',         label: 'Partida registral *',        tipo: 'text' },
        { key: 'lote_area_total',        label: 'Área total (m2)',            tipo: 'text' },
        { key: 'sec_lotes', label: '✂️ LOTES RESULTANTES', tipo: 'seccion' },
        { key: 'lote1_descripcion',      label: 'Lote 1 - descripción',       tipo: 'textarea', full: true, rows: 2 },
        { key: 'lote1_adjudicado_a',     label: 'Lote 1 adjudicado a',        tipo: 'text', full: true },
        { key: 'lote2_descripcion',      label: 'Lote 2 - descripción',       tipo: 'textarea', full: true, rows: 2 },
        { key: 'lote2_adjudicado_a',     label: 'Lote 2 adjudicado a',        tipo: 'text', full: true },
        { key: 'observaciones_particion',label: 'Observaciones',              tipo: 'textarea', full: true, rows: 2 },
    ],

    // ── DONACIONES ──
    donacion_inmueble: [
        { key: 'sec_donante', label: '👤 DATOS DEL DONANTE', tipo: 'seccion' },
        { key: 'donante_nombre',         label: 'Nombre donante *',           tipo: 'text', full: true },
        { key: 'donante_dni',            label: 'DNI donante *',              tipo: 'text' },
        { key: 'donante_estado_civil',   label: 'Estado civil',               tipo: 'select', opciones: ['soltero','casado','viudo','divorciado'] },
        { key: 'donante_domicilio',      label: 'Domicilio donante',          tipo: 'text', full: true },
        { key: 'conyuge_donante',        label: 'Cónyuge donante (si aplica)',tipo: 'text', full: true },
        { key: 'conyuge_donante_dni',    label: 'DNI cónyuge donante',        tipo: 'text' },
        { key: 'sec_donatario', label: '👤 DATOS DEL DONATARIO', tipo: 'seccion' },
        { key: 'donatario_nombre',       label: 'Nombre donatario *',         tipo: 'text', full: true },
        { key: 'donatario_dni',          label: 'DNI donatario *',            tipo: 'text' },
        { key: 'donatario_domicilio',    label: 'Domicilio donatario',        tipo: 'text', full: true },
        { key: 'sec_predio', label: '🏠 DATOS DEL BIEN DONADO', tipo: 'seccion' },
        { key: 'predio_descripcion',     label: 'Descripción del predio *',   tipo: 'textarea', full: true, rows: 2 },
        { key: 'predio_partida',         label: 'Partida registral *',        tipo: 'text' },
        { key: 'valor_donacion',         label: 'Valor de la donación (S/)',  tipo: 'number' },
    ],
    donacion_alicuotas: [
        { key: 'sec_donante', label: '👤 DATOS DEL DONANTE', tipo: 'seccion' },
        { key: 'donante_nombre',         label: 'Nombre donante *',           tipo: 'text', full: true },
        { key: 'donante_dni',            label: 'DNI donante *',              tipo: 'text' },
        { key: 'donante_domicilio',      label: 'Domicilio donante',          tipo: 'text', full: true },
        { key: 'sec_donatario', label: '👤 DATOS DEL DONATARIO', tipo: 'seccion' },
        { key: 'donatario_nombre',       label: 'Nombre donatario *',         tipo: 'text', full: true },
        { key: 'donatario_dni',          label: 'DNI donatario *',            tipo: 'text' },
        { key: 'donatario_domicilio',    label: 'Domicilio donatario',        tipo: 'text', full: true },
        { key: 'sec_predio', label: '📐 DATOS DE LAS ALÍCUOTAS', tipo: 'seccion' },
        { key: 'porcentaje_alicuota',    label: 'Porcentaje/alícuota *',      tipo: 'text' },
        { key: 'predio_descripcion',     label: 'Descripción del predio *',   tipo: 'textarea', full: true, rows: 2 },
        { key: 'predio_partida',         label: 'Partida registral *',        tipo: 'text' },
        { key: 'valor_donacion',         label: 'Valor donación (S/)',        tipo: 'number' },
    ],
    donacion_vehiculo: [
        { key: 'sec_donante', label: '👤 DATOS DEL/LOS DONANTE(S)', tipo: 'seccion' },
        { key: 'donante_nombre',         label: 'Nombre(s) donante(s) *',     tipo: 'textarea', full: true, rows: 2 },
        { key: 'sec_donatario', label: '👤 DATOS DEL DONATARIO', tipo: 'seccion' },
        { key: 'donatario_nombre',       label: 'Nombre donatario *',         tipo: 'text', full: true },
        { key: 'donatario_dni',          label: 'DNI donatario *',            tipo: 'text' },
        { key: 'sec_vehiculo', label: '🚗 DATOS DEL VEHÍCULO', tipo: 'seccion' },
        { key: 'placa',                  label: 'Placa *',                    tipo: 'text' },
        { key: 'valor_vehiculo',         label: 'Valor del vehículo (S/)',    tipo: 'number' },
    ],

    // ── VEHÍCULOS ──
    transferencia_vehicular: [
        { key: 'sec_vendedor', label: '👤 DATOS DEL VENDEDOR', tipo: 'seccion' },
        { key: 'vendedor_nombre',        label: 'Nombre vendedor *',          tipo: 'text', full: true },
        { key: 'vendedor_dni',           label: 'DNI vendedor *',             tipo: 'text' },
        { key: 'vendedor_estado_civil',  label: 'Estado civil',               tipo: 'select', opciones: ['soltero','casado','viudo','divorciado'] },
        { key: 'vendedor_ocupacion',     label: 'Ocupación',                  tipo: 'text' },
        { key: 'vendedor_domicilio',     label: 'Domicilio vendedor',         tipo: 'text', full: true },
        { key: 'sec_comprador', label: '👤 DATOS DEL COMPRADOR', tipo: 'seccion' },
        { key: 'comprador_nombre',       label: 'Nombre comprador *',         tipo: 'text', full: true },
        { key: 'comprador_dni',          label: 'DNI comprador *',            tipo: 'text' },
        { key: 'comprador_estado_civil', label: 'Estado civil',               tipo: 'select', opciones: ['soltero','casado','viudo','divorciado'] },
        { key: 'comprador_ocupacion',    label: 'Ocupación',                  tipo: 'text' },
        { key: 'comprador_domicilio',    label: 'Domicilio comprador',        tipo: 'text', full: true },
        { key: 'sec_vehiculo', label: '🚙 DATOS DEL VEHÍCULO', tipo: 'seccion' },
        { key: 'placa',                  label: 'Placa *',                    tipo: 'text' },
        { key: 'precio_total',           label: 'Precio de venta (S/) *',     tipo: 'number' },
        { key: 'precio_total_letras',    label: 'Precio en letras',           tipo: 'text', full: true },
        { key: 'forma_pago_detalle',     label: 'Forma de pago',              tipo: 'text', full: true },
    ],

    // ── HIPOTECAS / MUTUOS ──
    hipoteca: [
        { key: 'sec_deudor', label: '👤 DATOS DEL DEUDOR', tipo: 'seccion' },
        { key: 'deudor_nombre',          label: 'Nombre deudor *',            tipo: 'text', full: true },
        { key: 'deudor_dni',             label: 'DNI deudor *',               tipo: 'text' },
        { key: 'deudor_domicilio',       label: 'Domicilio deudor',           tipo: 'text', full: true },
        { key: 'sec_acreedor', label: '👤 DATOS DEL ACREEDOR', tipo: 'seccion' },
        { key: 'acreedor_nombre',        label: 'Nombre acreedor *',          tipo: 'text', full: true },
        { key: 'acreedor_dni',           label: 'DNI acreedor *',             tipo: 'text' },
        { key: 'sec_hipoteca', label: '🏦 DATOS DE LA HIPOTECA', tipo: 'seccion' },
        { key: 'monto_hipoteca',         label: 'Monto (S/) *',               tipo: 'number' },
        { key: 'monto_hipoteca_letras',  label: 'Monto en letras',            tipo: 'text', full: true },
        { key: 'plazo_hipoteca',         label: 'Plazo de pago',              tipo: 'text' },
        { key: 'tasa_interes',           label: 'Tasa de interés',            tipo: 'text' },
        { key: 'predio_partida',         label: 'Partida registral garantía *',tipo: 'text' },
        { key: 'predio_descripcion',     label: 'Descripción bien garantía',  tipo: 'textarea', full: true, rows: 2 },
        { key: 'detalle_desembolsos',    label: 'Detalle desembolsos',        tipo: 'textarea', full: true, rows: 3 },
    ],
    mutuo_hipoteca: [
        { key: 'sec_mutuante', label: '👤 DATOS DEL MUTUANTE (PRESTAMISTA)', tipo: 'seccion' },
        { key: 'mutuante_nombre',        label: 'Nombre mutuante *',          tipo: 'text', full: true },
        { key: 'mutuante_dni',           label: 'DNI mutuante *',             tipo: 'text' },
        { key: 'mutuante_domicilio',     label: 'Domicilio mutuante',         tipo: 'text', full: true },
        { key: 'sec_mutuatario', label: '👤 DATOS DEL MUTUATARIO (DEUDOR)', tipo: 'seccion' },
        { key: 'mutuatario_nombre',      label: 'Nombre mutuatario *',        tipo: 'text', full: true },
        { key: 'mutuatario_dni',         label: 'DNI mutuatario *',           tipo: 'text' },
        { key: 'mutuatario_domicilio',   label: 'Domicilio mutuatario',       tipo: 'text', full: true },
        { key: 'sec_mutuo', label: '💰 DATOS DEL MUTUO', tipo: 'seccion' },
        { key: 'monto_mutuo',            label: 'Monto del mutuo (S/) *',     tipo: 'number' },
        { key: 'monto_mutuo_letras',     label: 'Monto en letras',            tipo: 'text', full: true },
        { key: 'detalle_desembolsos',    label: 'Detalle desembolsos *',      tipo: 'textarea', full: true, rows: 3 },
        { key: 'plazo_pago',             label: 'Plazo de pago *',            tipo: 'text' },
        { key: 'tasa_interes',           label: 'Tasa de interés',            tipo: 'text' },
        { key: 'sec_garantia', label: '🏦 BIEN EN GARANTÍA', tipo: 'seccion' },
        { key: 'predio_descripcion',     label: 'Descripción bien garantía *',tipo: 'textarea', full: true, rows: 2 },
        { key: 'predio_partida',         label: 'Partida registral *',        tipo: 'text' },
    ],

    // ── PODERES ──
    poder: [
        { key: 'sec_poderdante', label: '👤 DATOS DEL PODERDANTE', tipo: 'seccion' },
        { key: 'poderdante_nombre',      label: 'Nombre poderdante *',        tipo: 'text', full: true },
        { key: 'poderdante_dni',         label: 'DNI poderdante *',           tipo: 'text' },
        { key: 'poderdante_estado_civil',label: 'Estado civil',               tipo: 'select', opciones: ['soltero','casado','viudo','divorciado'] },
        { key: 'poderdante_domicilio',   label: 'Domicilio poderdante',       tipo: 'text', full: true },
        { key: 'conyuge_poderdante',     label: 'Cónyuge (si aplica)',        tipo: 'text', full: true },
        { key: 'conyuge_poderdante_dni', label: 'DNI cónyuge',               tipo: 'text' },
        { key: 'sec_apoderado', label: '👤 DATOS DEL APODERADO', tipo: 'seccion' },
        { key: 'apoderado_nombre',       label: 'Nombre apoderado *',         tipo: 'text', full: true },
        { key: 'apoderado_dni',          label: 'DNI apoderado *',            tipo: 'text' },
        { key: 'sec_facultades', label: '📋 FACULTADES', tipo: 'seccion' },
        { key: 'tipo_poder',             label: 'Tipo de poder',              tipo: 'select', opciones: ['General y Especial','Amplio y General','Especial','Irrevocable'] },
        { key: 'facultades',             label: 'Facultades específicas',     tipo: 'textarea', full: true, rows: 4 },
        { key: 'vigencia',               label: 'Vigencia',                   tipo: 'select', opciones: ['Sin límite','1 año','2 años','3 años','Hasta revocación'] },
    ],
    ampliacion_poder: [
        { key: 'sec_poderdante', label: '👤 DATOS DEL PODERDANTE', tipo: 'seccion' },
        { key: 'poderdante_nombre',      label: 'Nombre poderdante *',        tipo: 'text', full: true },
        { key: 'poderdante_dni',         label: 'DNI poderdante *',           tipo: 'text' },
        { key: 'sec_apoderado', label: '👤 DATOS DEL APODERADO', tipo: 'seccion' },
        { key: 'apoderado_nombre',       label: 'Nombre apoderado *',         tipo: 'text', full: true },
        { key: 'apoderado_dni',          label: 'DNI apoderado *',            tipo: 'text' },
        { key: 'sec_ampliacion', label: '📋 AMPLIACIÓN', tipo: 'seccion' },
        { key: 'poder_original_numero',  label: 'N° escritura poder original',tipo: 'text' },
        { key: 'facultades_nuevas',      label: 'Facultades a ampliar *',     tipo: 'textarea', full: true, rows: 4 },
    ],
    revocatoria_poder: [
        { key: 'sec_poderdante', label: '👤 DATOS DEL PODERDANTE', tipo: 'seccion' },
        { key: 'poderdante_nombre',      label: 'Nombre poderdante *',        tipo: 'text', full: true },
        { key: 'poderdante_dni',         label: 'DNI poderdante *',           tipo: 'text' },
        { key: 'conyuge_poderdante',     label: 'Cónyuge (si aplica)',        tipo: 'text', full: true },
        { key: 'conyuge_poderdante_dni', label: 'DNI cónyuge',               tipo: 'text' },
        { key: 'sec_apoderado', label: '👤 DATOS DEL APODERADO A REVOCAR', tipo: 'seccion' },
        { key: 'apoderado_nombre',       label: 'Nombre apoderado *',         tipo: 'text', full: true },
        { key: 'apoderado_dni',          label: 'DNI apoderado *',            tipo: 'text' },
        { key: 'sec_revocatoria', label: '🚫 DATOS DE LA REVOCATORIA', tipo: 'seccion' },
        { key: 'poder_original_numero',  label: 'N° escritura poder original',tipo: 'text' },
        { key: 'fecha_poder_original',   label: 'Fecha poder original',       tipo: 'text' },
        { key: 'partida_poder',          label: 'Partida registral poder',    tipo: 'text' },
    ],

    // ── EMPRESAS ──
    constitucion_sac: [
        { key: 'sec_empresa', label: '🏢 DATOS DE LA EMPRESA', tipo: 'seccion' },
        { key: 'razon_social',           label: 'Razón social *',             tipo: 'text', full: true },
        { key: 'objeto_social',          label: 'Objeto social *',            tipo: 'textarea', full: true, rows: 3 },
        { key: 'domicilio_empresa',      label: 'Domicilio empresa *',        tipo: 'text', full: true },
        { key: 'capital_total',          label: 'Capital social total (S/) *',tipo: 'number' },
        { key: 'sec_socios', label: '👥 DATOS DE LOS SOCIOS', tipo: 'seccion' },
        { key: 'socios',                 label: 'Socios (nombre, DNI, acciones, aporte)',tipo: 'textarea', full: true, rows: 5 },
        { key: 'gerente_nombre',         label: 'Gerente General *',          tipo: 'text', full: true },
        { key: 'gerente_dni',            label: 'DNI gerente *',              tipo: 'text' },
        { key: 'duracion',               label: 'Duración',                   tipo: 'select', opciones: ['Indeterminada','1 año','2 años','5 años','10 años'] },
    ],
    constitucion_srl: [
        { key: 'sec_empresa', label: '🏢 DATOS DE LA EMPRESA', tipo: 'seccion' },
        { key: 'razon_social',           label: 'Denominación social *',      tipo: 'text', full: true },
        { key: 'objeto_social',          label: 'Objeto social *',            tipo: 'textarea', full: true, rows: 3 },
        { key: 'domicilio_empresa',      label: 'Domicilio empresa *',        tipo: 'text', full: true },
        { key: 'capital_total',          label: 'Capital social total (S/) *',tipo: 'number' },
        { key: 'sec_socios', label: '👥 DATOS DE LOS SOCIOS', tipo: 'seccion' },
        { key: 'socios',                 label: 'Socios (nombre, DNI, participaciones, aporte)',tipo: 'textarea', full: true, rows: 5 },
        { key: 'gerente_nombre',         label: 'Gerente General *',          tipo: 'text', full: true },
        { key: 'gerente_dni',            label: 'DNI gerente *',              tipo: 'text' },
    ],
    constitucion_asociacion: [
        { key: 'sec_asociacion', label: '🤝 DATOS DE LA ASOCIACIÓN', tipo: 'seccion' },
        { key: 'razon_social',           label: 'Denominación asociación *',  tipo: 'text', full: true },
        { key: 'objeto_social',          label: 'Finalidad/Objeto *',         tipo: 'textarea', full: true, rows: 3 },
        { key: 'domicilio_empresa',      label: 'Domicilio asociación *',     tipo: 'text', full: true },
        { key: 'sec_directiva', label: '👥 CONSEJO DIRECTIVO', tipo: 'seccion' },
        { key: 'presidente_nombre',      label: 'Presidente *',               tipo: 'text', full: true },
        { key: 'presidente_dni',         label: 'DNI presidente *',           tipo: 'text' },
        { key: 'representante_nombre',   label: 'Otorgante/Representante *',  tipo: 'text', full: true },
        { key: 'representante_dni',      label: 'DNI representante',          tipo: 'text' },
        { key: 'fecha_acta_const',       label: 'Fecha acta de constitución', tipo: 'text' },
    ],
    aumento_capital: [
        { key: 'sec_empresa', label: '🏢 DATOS DE LA EMPRESA', tipo: 'seccion' },
        { key: 'razon_social',           label: 'Razón social *',             tipo: 'text', full: true },
        { key: 'partida_empresa',        label: 'Partida registral empresa *',tipo: 'text' },
        { key: 'tipo_empresa',           label: 'Tipo empresa',               tipo: 'select', opciones: ['SAC','SRL','EIRL','SA'] },
        { key: 'representante_nombre',   label: 'Representante/Titular *',    tipo: 'text', full: true },
        { key: 'representante_dni',      label: 'DNI representante *',        tipo: 'text' },
        { key: 'sec_aumento', label: '📈 DATOS DEL AUMENTO', tipo: 'seccion' },
        { key: 'capital_anterior',       label: 'Capital anterior (S/)',      tipo: 'number' },
        { key: 'monto_aumento',          label: 'Monto aumento (S/) *',       tipo: 'number' },
        { key: 'capital_nuevo',          label: 'Capital nuevo total (S/) *', tipo: 'number' },
        { key: 'tipo_aporte',            label: 'Tipo de aporte',             tipo: 'select', opciones: ['Dinerario','No dinerario','Mixto'] },
        { key: 'detalle_aporte',         label: 'Detalle del aporte',         tipo: 'textarea', full: true, rows: 2 },
        { key: 'fecha_junta',            label: 'Fecha de junta/acta',        tipo: 'text' },
        { key: 'articulos_modificados',  label: 'Artículos del estatuto modificados',tipo: 'text', full: true },
    ],
    transformacion_empresa: [
        { key: 'sec_empresa', label: '🔄 EMPRESA A TRANSFORMAR', tipo: 'seccion' },
        { key: 'razon_social_anterior',  label: 'Denominación anterior *',    tipo: 'text', full: true },
        { key: 'tipo_anterior',          label: 'Tipo empresa anterior',      tipo: 'select', opciones: ['EIRL','SRL','SAC','SA'] },
        { key: 'partida_empresa',        label: 'Partida registral',          tipo: 'text' },
        { key: 'titular_nombre',         label: 'Titular/Representante *',    tipo: 'text', full: true },
        { key: 'titular_dni',            label: 'DNI titular *',              tipo: 'text' },
        { key: 'sec_nueva', label: '🏢 NUEVA EMPRESA', tipo: 'seccion' },
        { key: 'razon_social',           label: 'Nueva denominación *',       tipo: 'text', full: true },
        { key: 'tipo_nuevo',             label: 'Nuevo tipo empresa',         tipo: 'select', opciones: ['SAC','SRL','SA'] },
        { key: 'capital_total',          label: 'Capital nuevo total (S/) *', tipo: 'number' },
        { key: 'socios',                 label: 'Socios/Accionistas (detalle)',tipo: 'textarea', full: true, rows: 4 },
        { key: 'gerente_nombre',         label: 'Gerente General *',          tipo: 'text', full: true },
        { key: 'gerente_dni',            label: 'DNI gerente',                tipo: 'text' },
        { key: 'fecha_acta',             label: 'Fecha acta de transformación',tipo: 'text' },
    ],

    // ── RÉGIMEN PATRIMONIAL ──
    sustitucion_regimen: [
        { key: 'sec_conyuges', label: '💍 DATOS DE LOS CÓNYUGES', tipo: 'seccion' },
        { key: 'conyuge1_nombre',        label: 'Nombre cónyuge 1 *',         tipo: 'text', full: true },
        { key: 'conyuge1_dni',           label: 'DNI cónyuge 1 *',            tipo: 'text' },
        { key: 'conyuge2_nombre',        label: 'Nombre cónyuge 2 *',         tipo: 'text', full: true },
        { key: 'conyuge2_dni',           label: 'DNI cónyuge 2 *',            tipo: 'text' },
        { key: 'domicilio_conyugal',     label: 'Domicilio conyugal',         tipo: 'text', full: true },
        { key: 'sec_regimen', label: '📋 DATOS DEL RÉGIMEN', tipo: 'seccion' },
        { key: 'regimen_anterior',       label: 'Régimen anterior',           tipo: 'select', opciones: ['Sociedad de gananciales','Separación de patrimonios'] },
        { key: 'regimen_nuevo',          label: 'Nuevo régimen',              tipo: 'select', opciones: ['Separación de patrimonios','Sociedad de gananciales'] },
        { key: 'bienes_adjudicados',     label: 'Bienes adjudicados (detalle)',tipo: 'textarea', full: true, rows: 4 },
    ],
    cese_regimen: [
        { key: 'sec_conyuges', label: '💍 DATOS DE LOS CÓNYUGES', tipo: 'seccion' },
        { key: 'conyuge1_nombre',        label: 'Nombre cónyuge 1 *',         tipo: 'text', full: true },
        { key: 'conyuge1_dni',           label: 'DNI cónyuge 1 *',            tipo: 'text' },
        { key: 'conyuge2_nombre',        label: 'Nombre cónyuge 2 *',         tipo: 'text', full: true },
        { key: 'conyuge2_dni',           label: 'DNI cónyuge 2 *',            tipo: 'text' },
        { key: 'sec_cese', label: '📋 DATOS DEL CESE', tipo: 'seccion' },
        { key: 'escritura_sustitucion',  label: 'N° escritura de sustitución',tipo: 'text' },
        { key: 'fecha_sustitucion',      label: 'Fecha de sustitución',       tipo: 'text' },
        { key: 'motivo_cese',            label: 'Motivo del cese',            tipo: 'textarea', full: true, rows: 2 },
    ],

    // ── FAMILIA / PERSONAL ──
    testamento: [
        { key: 'sec_testador', label: '👤 DATOS DEL TESTADOR', tipo: 'seccion' },
        { key: 'testador_nombre',        label: 'Nombre testador *',          tipo: 'text', full: true },
        { key: 'testador_dni',           label: 'DNI testador *',             tipo: 'text' },
        { key: 'testador_estado_civil',  label: 'Estado civil',               tipo: 'select', opciones: ['Soltero','Casado','Viudo','Divorciado'] },
        { key: 'testador_domicilio',     label: 'Domicilio testador',         tipo: 'text', full: true },
        { key: 'testador_conyuge',       label: 'Nombre cónyuge (si casado)', tipo: 'text', full: true },
        { key: 'testador_conyuge_dni',   label: 'DNI cónyuge',               tipo: 'text' },
        { key: 'sec_testigos', label: '👥 TESTIGOS TESTAMENTARIOS', tipo: 'seccion' },
        { key: 'testigo1_nombre',        label: 'Testigo 1 nombre *',         tipo: 'text', full: true },
        { key: 'testigo1_dni',           label: 'DNI testigo 1 *',            tipo: 'text' },
        { key: 'testigo2_nombre',        label: 'Testigo 2 nombre *',         tipo: 'text', full: true },
        { key: 'testigo2_dni',           label: 'DNI testigo 2 *',            tipo: 'text' },
        { key: 'sec_disposiciones', label: '📋 DISPOSICIONES TESTAMENTARIAS', tipo: 'seccion' },
        { key: 'herederos',              label: 'Herederos (nombre y DNI) *', tipo: 'textarea', full: true, rows: 3 },
        { key: 'bienes',                 label: 'Bienes a legar (descripción)',tipo: 'textarea', full: true, rows: 4 },
    ],
    reconocimiento_paternidad: [
        { key: 'sec_padre', label: '👤 DATOS DEL PADRE', tipo: 'seccion' },
        { key: 'padre_nombre',           label: 'Nombre del padre *',         tipo: 'text', full: true },
        { key: 'padre_dni',              label: 'DNI del padre *',            tipo: 'text' },
        { key: 'padre_domicilio',        label: 'Domicilio del padre',        tipo: 'text', full: true },
        { key: 'sec_madre', label: '👤 DATOS DE LA MADRE', tipo: 'seccion' },
        { key: 'madre_nombre',           label: 'Nombre de la madre *',       tipo: 'text', full: true },
        { key: 'madre_dni',              label: 'DNI de la madre *',          tipo: 'text' },
        { key: 'sec_hijo', label: '👶 DATOS DEL HIJO/CONCEBIDO', tipo: 'seccion' },
        { key: 'tipo_reconocimiento',    label: 'Tipo',                       tipo: 'select', opciones: ['Hijo nacido','Concebido (en gestación)'] },
        { key: 'hijo_nombre',            label: 'Nombre del hijo (si nació)', tipo: 'text', full: true },
        { key: 'semanas_gestacion',      label: 'Semanas de gestación (si concebido)',tipo: 'text' },
        { key: 'fecha_ecografia',        label: 'Fecha de ecografía',         tipo: 'text' },
    ],
    autorizacion_viaje: [
        { key: 'sec_padre', label: '👤 DATOS DEL PADRE/MADRE AUTORIZANTE', tipo: 'seccion' },
        { key: 'padre_nombre',           label: 'Nombre del autorizante *',   tipo: 'text', full: true },
        { key: 'padre_dni',              label: 'DNI del autorizante *',      tipo: 'text' },
        { key: 'padre_domicilio',        label: 'Domicilio autorizante',      tipo: 'text', full: true },
        { key: 'sec_menor', label: '👶 DATOS DEL MENOR', tipo: 'seccion' },
        { key: 'menor_nombre',           label: 'Nombre del menor *',         tipo: 'text', full: true },
        { key: 'menor_dni',              label: 'DNI del menor *',            tipo: 'text' },
        { key: 'menor_edad',             label: 'Edad del menor',             tipo: 'text' },
        { key: 'sec_viaje', label: '✈️ DATOS DEL VIAJE', tipo: 'seccion' },
        { key: 'ciudad_origen',          label: 'Ciudad de origen *',         tipo: 'text' },
        { key: 'ciudad_destino',         label: 'Ciudad de destino *',        tipo: 'text' },
        { key: 'fecha_viaje',            label: 'Fecha de viaje',             tipo: 'text' },
        { key: 'medio_transporte',       label: 'Medio de transporte',        tipo: 'select', opciones: ['Terrestre','Aéreo','Fluvial','Marítimo'] },
        { key: 'acompanante_nombre',     label: 'Acompañante nombre *',       tipo: 'text', full: true },
        { key: 'acompanante_dni',        label: 'DNI acompañante *',          tipo: 'text' },
        { key: 'incluye_retorno',        label: 'Incluye retorno',            tipo: 'checkbox' },
    ],
    autorizacion_viaje_ext: [
        { key: 'sec_padre', label: '👤 DATOS DEL PADRE/MADRE AUTORIZANTE', tipo: 'seccion' },
        { key: 'padre_nombre',           label: 'Nombre del autorizante *',   tipo: 'text', full: true },
        { key: 'padre_dni',              label: 'DNI del autorizante *',      tipo: 'text' },
        { key: 'lugar_comparecencia',    label: 'Lugar de comparecencia (si penal/especial)',tipo: 'text', full: true },
        { key: 'testigo_identif_nombre', label: 'Testigo de identificación (si aplica)',tipo: 'text', full: true },
        { key: 'testigo_identif_dni',    label: 'DNI testigo identificación', tipo: 'text' },
        { key: 'sec_menor', label: '👶 DATOS DEL MENOR', tipo: 'seccion' },
        { key: 'menor_nombre',           label: 'Nombre del menor *',         tipo: 'text', full: true },
        { key: 'menor_dni',              label: 'DNI del menor *',            tipo: 'text' },
        { key: 'menor_edad',             label: 'Edad del menor',             tipo: 'text' },
        { key: 'sec_viaje', label: '🌎 DATOS DEL VIAJE AL EXTERIOR', tipo: 'seccion' },
        { key: 'pais_destino',           label: 'País de destino *',          tipo: 'text' },
        { key: 'fecha_viaje',            label: 'Fecha probable de viaje',    tipo: 'text' },
        { key: 'medio_transporte',       label: 'Medio de transporte',        tipo: 'select', opciones: ['Aéreo','Terrestre','Marítimo'] },
        { key: 'acompanante_nombre',     label: 'Acompañante nombre *',       tipo: 'text', full: true },
        { key: 'acompanante_dni',        label: 'DNI acompañante *',          tipo: 'text' },
        { key: 'acompanante_pasaporte',  label: 'Pasaporte acompañante',      tipo: 'text' },
        { key: 'vigencia',               label: 'Vigencia autorización',      tipo: 'text' },
        { key: 'incluye_retorno',        label: 'Incluye retorno',            tipo: 'checkbox' },
    ],
    divorcio: [
        { key: 'sec_conyuge1', label: '👤 DATOS CÓNYUGE 1', tipo: 'seccion' },
        { key: 'conyuge1_nombre',        label: 'Nombre cónyuge 1 *',         tipo: 'text', full: true },
        { key: 'conyuge1_dni',           label: 'DNI cónyuge 1 *',            tipo: 'text' },
        { key: 'conyuge1_domicilio',     label: 'Domicilio cónyuge 1',        tipo: 'text', full: true },
        { key: 'sec_conyuge2', label: '👤 DATOS CÓNYUGE 2', tipo: 'seccion' },
        { key: 'conyuge2_nombre',        label: 'Nombre cónyuge 2 *',         tipo: 'text', full: true },
        { key: 'conyuge2_dni',           label: 'DNI cónyuge 2 *',            tipo: 'text' },
        { key: 'conyuge2_domicilio',     label: 'Domicilio cónyuge 2',        tipo: 'text', full: true },
        { key: 'sec_matrimonio', label: '💍 DATOS DEL MATRIMONIO', tipo: 'seccion' },
        { key: 'fecha_matrimonio',       label: 'Fecha de matrimonio *',      tipo: 'text' },
        { key: 'municipalidad_matrimonio',label: 'Municipalidad/lugar boda', tipo: 'text', full: true },
        { key: 'domicilio_conyugal',     label: 'Último domicilio conyugal',  tipo: 'text', full: true },
        { key: 'hijos_menores',          label: '¿Hijos menores de edad?',    tipo: 'select', opciones: ['No','Sí'] },
        { key: 'bienes_conyugales',      label: '¿Bienes conyugales?',        tipo: 'select', opciones: ['No','Sí - detallar'] },
        { key: 'detalle_bienes',         label: 'Detalle bienes (si aplica)', tipo: 'textarea', full: true, rows: 2 },
        { key: 'representante_nombre',   label: 'Apoderado/Representante (si aplica)',tipo: 'text', full: true },
        { key: 'representante_dni',      label: 'DNI representante',          tipo: 'text' },
    ],

    // ── NO CONTENCIOSOS / OTROS ──
    sucesion_intestada: [
        { key: 'sec_causante', label: '⚰️ DATOS DEL CAUSANTE', tipo: 'seccion' },
        { key: 'causante_nombre',        label: 'Nombre del causante *',      tipo: 'text', full: true },
        { key: 'causante_dni',           label: 'DNI del causante *',         tipo: 'text' },
        { key: 'fecha_fallecimiento',    label: 'Fecha de fallecimiento *',   tipo: 'text' },
        { key: 'ultimo_domicilio',       label: 'Último domicilio',           tipo: 'text', full: true },
        { key: 'sec_solicitante', label: '👤 DATOS DEL SOLICITANTE', tipo: 'seccion' },
        { key: 'solicitante_nombre',     label: 'Nombre solicitante *',       tipo: 'text', full: true },
        { key: 'solicitante_dni',        label: 'DNI solicitante *',          tipo: 'text' },
        { key: 'parentesco',             label: 'Parentesco con causante',    tipo: 'text' },
        { key: 'sec_herederos', label: '👥 HEREDEROS DECLARADOS', tipo: 'seccion' },
        { key: 'herederos',              label: 'Lista de herederos (nombre, DNI, parentesco) *',tipo: 'textarea', full: true, rows: 4 },
        { key: 'publicaciones',          label: 'Publicaciones realizadas',   tipo: 'textarea', full: true, rows: 2 },
    ],
    certificacion_notarial: [
        { key: 'sec_solicitante', label: '👤 DATOS DEL SOLICITANTE', tipo: 'seccion' },
        { key: 'solicitante_nombre',     label: 'Nombre solicitante *',       tipo: 'text', full: true },
        { key: 'solicitante_dni',        label: 'DNI solicitante *',          tipo: 'text' },
        { key: 'sec_certificacion', label: '🔏 DATOS DE LA CERTIFICACIÓN', tipo: 'seccion' },
        { key: 'tipo_certificacion',     label: 'Tipo de certificación',      tipo: 'select', opciones: ['Domicilio','Supervivencia','Soltería','Existencia','Otro'] },
        { key: 'domicilio_certificado',  label: 'Domicilio a certificar',     tipo: 'text', full: true },
        { key: 'documento_adjunto',      label: 'Documento adjunto (recibo luz, etc.)',tipo: 'text', full: true },
        { key: 'finalidad',              label: 'Finalidad de la certificación',tipo: 'textarea', full: true, rows: 2 },
    ],
    arrendamiento: [
        { key: 'sec_arrendador', label: '👤 DATOS DEL ARRENDADOR', tipo: 'seccion' },
        { key: 'arrendador_nombre',      label: 'Nombre arrendador(es) *',   tipo: 'textarea', full: true, rows: 2 },
        { key: 'sec_arrendatario', label: '👤 DATOS DEL ARRENDATARIO', tipo: 'seccion' },
        { key: 'arrendatario_nombre',    label: 'Nombre arrendatario *',      tipo: 'text', full: true },
        { key: 'arrendatario_dni',       label: 'DNI arrendatario *',         tipo: 'text' },
        { key: 'conyuge_arrendatario',   label: 'Cónyuge arrendatario (si aplica)',tipo: 'text', full: true },
        { key: 'sec_predio', label: '🏠 DATOS DEL BIEN ARRENDADO', tipo: 'seccion' },
        { key: 'predio_descripcion',     label: 'Descripción del bien *',     tipo: 'textarea', full: true, rows: 2 },
        { key: 'predio_partida',         label: 'Partida registral',          tipo: 'text' },
        { key: 'sec_contrato', label: '📋 CONDICIONES DEL CONTRATO', tipo: 'seccion' },
        { key: 'monto_mensual',          label: 'Renta mensual (S/) *',       tipo: 'number' },
        { key: 'plazo_arrendamiento',    label: 'Plazo de arrendamiento *',   tipo: 'text' },
        { key: 'fecha_inicio',           label: 'Fecha de inicio',            tipo: 'text' },
        { key: 'garantia_meses',         label: 'Garantía (meses)',           tipo: 'text' },
        { key: 'es_ampliacion',          label: '¿Es ampliación de contrato?',tipo: 'checkbox' },
        { key: 'contrato_original_num',  label: 'N° escritura original (si ampliación)',tipo: 'text' },
    ],
    carta_notarial: [
        { key: 'remitente_nombre',       label: 'Nombre remitente *',         tipo: 'text', full: true },
        { key: 'remitente_dni',          label: 'DNI/RUC remitente *',        tipo: 'text' },
        { key: 'destinatario_nombre',    label: 'Nombre destinatario *',      tipo: 'text', full: true },
        { key: 'destinatario_domicilio', label: 'Dirección destinatario *',   tipo: 'text', full: true },
        { key: 'asunto_carta',           label: 'Asunto *',                   tipo: 'textarea', full: true, rows: 2 },
        { key: 'contenido',              label: 'Contenido de la carta *',    tipo: 'textarea', full: true, rows: 5 },
        { key: 'plazo_respuesta',        label: 'Plazo de respuesta',         tipo: 'select', opciones: ['Sin plazo','24 horas','3 días','5 días','7 días','15 días','30 días'] },
    ],
    escritura_publica: [
        { key: 'sec_comprador', label: '👤 DATOS DEL COMPRADOR', tipo: 'seccion' },
        { key: 'comprador_nombre',       label: 'Nombre completo comprador *',tipo: 'text', full: true },
        { key: 'comprador_dni',          label: 'DNI comprador *',            tipo: 'text' },
        { key: 'comprador_estado_civil', label: 'Estado civil',               tipo: 'select', opciones: ['soltero','casado','viudo','divorciado'] },
        { key: 'comprador_profesion',    label: 'Profesión',                  tipo: 'text' },
        { key: 'comprador_domicilio',    label: 'Domicilio comprador *',      tipo: 'text', full: true },
        { key: 'sec_predio', label: '🏠 DATOS DEL PREDIO', tipo: 'seccion' },
        { key: 'es_bien_futuro',         label: '¿Es bien futuro?',           tipo: 'checkbox' },
        { key: 'predio_descripcion',     label: 'Descripción del predio *',   tipo: 'textarea', full: true, rows: 2 },
        { key: 'predio_partida',         label: 'Partida registral predio *', tipo: 'text' },
        { key: 'lote_descripcion',       label: 'Descripción del lote *',     tipo: 'text', full: true },
        { key: 'lote_area',              label: 'Área (m2)',                  tipo: 'text' },
        { key: 'lote_area_letras',       label: 'Área en letras',             tipo: 'text', full: true },
        { key: 'lindero_frente',         label: 'Frente colinda con',         tipo: 'text' },
        { key: 'medida_frente',          label: 'Medida frente (ml)',         tipo: 'text' },
        { key: 'lindero_derecha',        label: 'Derecha colinda con',        tipo: 'text' },
        { key: 'medida_derecha',         label: 'Medida derecha (ml)',        tipo: 'text' },
        { key: 'lindero_izquierda',      label: 'Izquierda colinda con',      tipo: 'text' },
        { key: 'medida_izquierda',       label: 'Medida izquierda (ml)',      tipo: 'text' },
        { key: 'lindero_fondo',          label: 'Fondo colinda con',          tipo: 'text' },
        { key: 'medida_fondo',           label: 'Medida fondo (ml)',          tipo: 'text' },
        { key: 'sec_precio', label: '💰 PRECIO Y PAGO', tipo: 'seccion' },
        { key: 'precio_total',           label: 'Precio total (S/) *',        tipo: 'number' },
        { key: 'precio_total_letras',    label: 'Precio en letras *',         tipo: 'text', full: true },
        { key: 'forma_pago_detalle',     label: 'Detalle de forma de pago *', tipo: 'textarea', full: true, rows: 3 },
        { key: 'fecha_minuta',           label: 'Fecha de la minuta *',       tipo: 'text' },
    ],
    otro: [
        { key: 'descripcion',            label: 'Descripción del acto *',     tipo: 'textarea', full: true, rows: 3 },
        { key: 'partes',                 label: 'Partes intervinientes',       tipo: 'textarea', full: true, rows: 2 },
        { key: 'detalles',               label: 'Detalles adicionales',        tipo: 'textarea', full: true, rows: 3 },
    ],
}


const camposPlantilla = computed(() => plantillas[props.acto.tipo_acto] || plantillas.otro)
const formDatos = ref({ ...props.datos })
const guardandoDatos = ref(false)

function guardarDatos() {
    guardandoDatos.value = true
    router.post('/notaria/actos/' + props.acto.id + '/datos', { datos: formDatos.value }, {
        preserveScroll: true,
        onSuccess: () => { guardandoDatos.value = false },
        onError: () => { guardandoDatos.value = false },
    })
}

// Requisitos
const mostrarAddReq  = ref(false)
const nuevoRequisito = ref('')
const otroRequisito  = ref('')

const requisitosComunes = [
    'DNI del solicitante', 'DNI del cónyuge', 'DNI del vendedor', 'DNI del comprador',
    'DNI del poderdante', 'DNI del apoderado', 'RUC de la empresa',
    'Partida registral', 'Título de propiedad', 'Minuta notarial',
    'Certificado de matrimonio', 'Certificado de defunción',
    'Poder vigente', 'Declaración jurada', 'Contrato de compraventa',
    'Letra de cambio', 'Cheque original', 'Factura negociable',
]

function agregarRequisito() {
    const doc = nuevoRequisito.value === '__otro__' ? otroRequisito.value : nuevoRequisito.value
    if (!doc) { alert('Selecciona o escribe el documento'); return }
    router.post('/notaria/actos/' + props.acto.id + '/requisitos', { documento: doc }, {
        preserveScroll: true,
        onSuccess: () => { nuevoRequisito.value = ''; otroRequisito.value = ''; mostrarAddReq.value = false }
    })
}

function toggleRequisito(r) {
    router.patch('/notaria/requisitos/' + r.id + '/toggle', {}, { preserveScroll: true })
}

function eliminarRequisito(id) {
    if (!confirm('¿Eliminar este requisito?')) return
    router.delete('/notaria/requisitos/' + id, { preserveScroll: true })
}

const modalComprobante = ref(false)
const emitiendo        = ref(false)
const errorComp        = ref('')
const pdfComp          = ref('')

// Variables del nuevo modal comprobante
const compDocumento  = ref('')
const compNombre     = ref('CLIENTES VARIOS')
const compEmail      = ref('')
const compItems      = ref([])
const compMetodoPago = ref('efectivo')
const compFormaPago  = ref('Contado')
const compCuotas     = ref([])
const compItemDesc   = ref('')
const compItemCant   = ref(1)
const compItemPrecio = ref('')
const buscandoComp   = ref(false)
const compTotal      = computed(() => compItems.value.reduce((s, i) => s + (i.cantidad * i.precio_unitario), 0))

function abrirModalComprobante() {
    compDocumento.value  = ''
    compNombre.value     = 'CLIENTES VARIOS'
    compEmail.value      = ''
    compMetodoPago.value = 'efectivo'
    compFormaPago.value  = 'Contado'
    compCuotas.value     = []
    compItemDesc.value   = ''
    compItemCant.value   = 1
    compItemPrecio.value = ''
    // Pre-cargar el servicio del expediente
    compItems.value = [{
        descripcion: props.acto.asunto || 'Servicio notarial',
        cantidad: 1,
        precio_unitario: Number(props.acto.monto_cobrar) || 0,
        _esHuella: false
    }]
    recalcularBiometricoComp()
    modalComprobante.value = true
}

function recalcularBiometricoComp() {
    // Restaurar precio original si existe
    const primero = compItems.value.find(i => !i._esHuella && i._precioOriginal !== undefined)
    if (primero) { primero.precio_unitario = primero._precioOriginal; delete primero._precioOriginal }
    // Quitar biométrico existente
    compItems.value = compItems.value.filter(i => !i._esHuella)
    const totalReal = compItems.value.reduce((s, i) => s + (i.cantidad * i.precio_unitario), 0)
    const esTramite = compItems.value.some(i => (i.descripcion||'').toLowerCase().includes('tramite registral') || (i.descripcion||'').toLowerCase().includes('trámite registral'))
    if (!esTramite && totalReal >= 10) {
        const p = compItems.value.find(i => !i._esHuella)
        if (p && Number(p.precio_unitario) > 1.50) {
            p._precioOriginal = Number(p.precio_unitario)
            p.precio_unitario = Number((p.precio_unitario - 1.50).toFixed(2))
        }
        compItems.value.push({ descripcion: 'Verificación biométrica RENIEC', cantidad: 1, precio_unitario: 1.50, _esHuella: true })
    }
}

function agregarItemComp() {
    if (!compItemDesc.value) { alert('Ingresa la descripción'); return }
    if (!compItemPrecio.value || Number(compItemPrecio.value) <= 0) { alert('Ingresa el precio'); return }
    compItems.value.push({ descripcion: compItemDesc.value, cantidad: Number(compItemCant.value)||1, precio_unitario: Number(compItemPrecio.value), _esHuella: false })
    compItemDesc.value = ''; compItemCant.value = 1; compItemPrecio.value = ''
    recalcularBiometricoComp()
}

function quitarItemComp(idx) {
    if (compItems.value[idx]?._esHuella) return
    compItems.value.splice(idx, 1)
    recalcularBiometricoComp()
}

const buscarClienteComp = async () => {
    const doc = (compDocumento.value||'').replace(/\D/g,'')
    if (doc.length !== 8 && doc.length !== 11) return
    buscandoComp.value = true
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        // 1. Buscar en clientes registrados
        const res = await fetch('/notaria/clientes/buscar?documento=' + doc, {
            headers: { 'X-CSRF-TOKEN': csrf }
        })
        if (res.ok) {
            const data = await res.json()
            if (data.nombre) { compNombre.value = data.nombre; buscandoComp.value = false; return }
        }
        // 2. Fallback a API externa
        const r = await fetch('/api/consulta-documento?documento=' + doc, {
            headers: { 'X-CSRF-TOKEN': csrf }
        })
        if (r.ok) {
            const d = await r.json()
            if (d.nombres) compNombre.value = d.nombres + ' ' + d.apellidoPaterno + ' ' + d.apellidoMaterno
            else if (d.razonSocial) compNombre.value = d.razonSocial
        }
    } catch(e) { console.error(e) }
    buscandoComp.value = false
}

async function emitirComprobanteExp() {
    if (!compItems.value.length) return
    emitiendo.value = true
    errorComp.value = ''
    pdfComp.value   = ''
    try {
        const csrf = await fetch('/sanctum/csrf-cookie').then(() => document.querySelector('meta[name="csrf-token"]')?.content)
        const docClean = (compDocumento.value||'').replace(/\D/g,'')
        const items = compItems.value.map(it => ({
            tipo_servicio: it.descripcion,
            descripcion:   it.descripcion,
            cantidad:      it.cantidad,
            precio_unitario: it.precio_unitario,
            precio: it.precio_unitario,
            monto: it.cantidad * it.precio_unitario,
        }))
        const payload = {
            cliente_nombre:    compNombre.value || 'CLIENTES VARIOS',
            cliente_documento: docClean || '00000000',
            metodo_pago:       compMetodoPago.value,
            forma_pago:        compFormaPago.value,
            cuotas:            compCuotas.value,
            items,
            monto:             compTotal.value,
            tipo_comprobante:  docClean.length === 11 ? '01' : '03',
            skip_caja_registro: true,
            acto_id:           props.acto.id,
        }
        const res = await fetch('/notaria/caja/servicio-rapido', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(payload)
        })
        if (res.status === 419) { alert('Sesión expirada, recargando...'); window.location.reload(); return }
        const data = await res.json()
        if (data.success) {
            pdfComp.value = data.pdf || ''
            if (data.pdf) window.open(data.pdf, '_blank')
        } else {
            errorComp.value = data.mensaje || 'Error al emitir'
        }
    } catch(e) {
        errorComp.value = 'Error: ' + e.message
    }
    emitiendo.value = false
}

const modalEstado = ref(false)
const modalPago   = ref(false)
const formEstado  = ref({ estado: props.acto.estado, comentario: '' })
const formPago    = ref({ monto: (Number(props.acto.monto_cobrar) - Number(props.acto.monto_pagado)).toFixed(2) })

const tiposActo = [
    { value: 'escritura_publica', label: '📜 Escritura pública' },
    { value: 'poder',             label: '✍️ Poder' },
    { value: 'testamento',        label: '📋 Testamento' },
    { value: 'legalizacion',      label: '🔏 Legalización' },
    { value: 'carta_notarial',    label: '✉️ Carta notarial' },
    { value: 'protesto',          label: '⚖️ Protesto' },
    { value: 'acta_notarial',     label: '📝 Acta notarial' },
    { value: 'otro',              label: '📁 Otro' },
]

const estados = [
    { value: 'pendiente',  label: 'Pendiente',  icon: '🕐', color: '#92400E', bg: '#FEF3C7' },
    { value: 'en_proceso', label: 'En proceso', icon: '🔄', color: '#1D4ED8', bg: '#EFF6FF' },
    { value: 'finalizado', label: 'Finalizado', icon: '✅', color: '#166534', bg: '#F0FDF4' },
    { value: 'cancelado',  label: 'Cancelado',  icon: '❌', color: '#991B1B', bg: '#FEF2F2' },
]

function labelTipo(t) { return tiposActo.find(x => x.value === t)?.label ?? t }
function labelEstado(e) { return estados.find(x => x.value === e)?.label ?? e }
function labelPago(p) { return { pendiente: '⏳ Pendiente', parcial: '◑ Parcial', pagado: '✅ Pagado' }[p] ?? p }

function estiloEstado(e) {
    const map = { pendiente:{background:'#FEF3C7',color:'#92400E'}, en_proceso:{background:'#EFF6FF',color:'#1D4ED8'}, finalizado:{background:'#F0FDF4',color:'#166534'}, cancelado:{background:'#FEF2F2',color:'#991B1B'} }
    return { ...(map[e]||{}), fontSize:'12px', padding:'4px 10px', borderRadius:'20px', fontWeight:'600' }
}

function estiloPago(p) {
    const map = { pendiente:{background:'#FEF2F2',color:'#991B1B'}, parcial:{background:'#FEF3C7',color:'#92400E'}, pagado:{background:'#F0FDF4',color:'#166634'} }
    return { ...(map[p]||{}), fontSize:'12px', padding:'4px 10px', borderRadius:'20px', fontWeight:'600' }
}

function formatFecha(f) {
    if (!f) return '—'
    const fecha = f.includes('T') || f.includes(' ') ? new Date(f) : new Date(f + 'T12:00:00')
    if (isNaN(fecha)) return '—'
    return fecha.toLocaleDateString('es-PE', { day:'2-digit', month:'2-digit', year:'numeric' })
}

function formatFechaHora(f) {
    if (!f) return '—'
    const d = new Date(f)
    return d.toLocaleDateString('es-PE', { day:'2-digit', month:'2-digit', year:'2-digit' }) + ' ' + d.toLocaleTimeString('es-PE', { hour:'2-digit', minute:'2-digit' })
}

function cambiarEstadoRapido(estado) {
    if (props.acto.estado === estado) return
    router.post('/notaria/actos/' + props.acto.id + '/estado', { estado, comentario: '' }, { preserveScroll: true })
}

function guardarEstado() {
    router.post('/notaria/actos/' + props.acto.id + '/estado', formEstado.value, {
        preserveScroll: true,
        onSuccess: () => { modalEstado.value = false }
    })
}

function guardarPago() {
    if (!formPago.value.monto) { alert('Ingresa el monto'); return }
    router.post('/notaria/actos/' + props.acto.id + '/pago', formPago.value, {
        preserveScroll: true,
        onSuccess: () => { modalPago.value = false }
    })
}

// ── EDITAR EXPEDIENTE ──
const modalEditar = ref(false)
const formEditar = ref({})
const formDatosEditar = ref({})

const camposPlantillaEditar = computed(() => plantillas[props.acto.tipo_acto] || [])

function abrirEditar() {
    formEditar.value = {
        asunto: props.acto.asunto,
        fecha_ingreso: props.acto.fecha_ingreso?.slice(0, 10),
        fecha_entrega: props.acto.fecha_entrega?.slice(0, 10) || '',
        monto_cobrar: props.acto.monto_cobrar,
        observaciones: props.acto.observaciones || '',
    }
    formDatosEditar.value = { ...props.datos }
    modalEditar.value = true
}

async function guardarEdicion() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
    const res = await fetch(`/notaria/actos/${props.acto.id}/editar`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ ...formEditar.value, datos: formDatosEditar.value })
    })
    if (res.status === 419) { alert('Sesión expirada, recarga'); window.location.reload(); return }
    if (res.ok) {
        modalEditar.value = false
        window.location.reload()
    } else {
        alert('Error al guardar, intenta de nuevo')
    }
}
</script>
