<template>
    <AppLayout title="Expedientes notariales" subtitle="Gestión de actos y trámites notariales">

        <!-- MÉTRICAS -->
        <div style="display:grid; grid-template-columns:repeat(6,1fr); gap:12px; margin-bottom:1.5rem;">
            <div v-for="(card,i) in tarjetas" :key="i" style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1rem 1.1rem;">
                <p style="font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase; margin:0 0 5px;">{{ card.label }}</p>
                <p style="font-size:20px; font-weight:800; margin:0;" :style="{color: card.color || '#1E293B'}">{{ card.valor }}</p>
            </div>
        </div>

        <!-- FILTROS + BOTÓN NUEVO -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">Desde</label>
                <input v-model="filtros.desde" type="date" style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">Hasta</label>
                <input v-model="filtros.hasta" type="date" style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">Tipo acto</label>
                <select v-model="filtros.tipo" style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                    <option value="">Todos</option>
                    <option v-for="t in tiposActo" :key="t.value" :value="t.value">{{ t.label }}</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">Estado</label>
                <select v-model="filtros.estado" style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="en_proceso">En proceso</option>
                    <option value="finalizado">Finalizado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            <div>
                <label style="font-size:11px; color:#94A3B8; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">Buscar</label>
                <input v-model="filtros.buscar" @keyup.enter="buscar" placeholder="Expediente, asunto, partes..." style="padding:7px 10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; width:220px;" />
            </div>
            <button @click="buscar" style="padding:7px 18px; background:linear-gradient(135deg,#6366F1,#4F46E5); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">Buscar</button>
            <button @click="modalNuevo=true" style="margin-left:auto; padding:7px 18px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">+ Nuevo expediente</button>
        </div>

        <!-- TABLA -->
        <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden;">
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#F8FAFC;">
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Expediente</th>
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Tipo acto</th>
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Asunto</th>
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Partes</th>
                        <th style="padding:10px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Ingreso</th>
                        <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Estado</th>
                        <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Pago</th>
                        <th style="padding:10px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Monto</th>
                        <th style="padding:10px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600; text-transform:uppercase;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="actos.length === 0">
                        <td colspan="9" style="padding:2rem; text-align:center; color:#94A3B8;">Sin expedientes en este período</td>
                    </tr>
                    <tr v-for="a in actos" :key="a.id" style="border-top:1px solid #F1F5F9; cursor:pointer;"
                        @mouseover="e => e.currentTarget.style.background='#F8FAFC'"
                        @mouseleave="e => e.currentTarget.style.background='white'">
                        <td style="padding:11px 16px; font-family:monospace; font-size:12px; font-weight:700; color:#4F46E5;">{{ a.numero_expediente }}</td>
                        <td style="padding:11px 16px;">
                            <span :style="estiloTipo(a.tipo_acto)">{{ labelTipo(a.tipo_acto) }}</span>
                        </td>
                        <td style="padding:11px 16px; color:#1E293B; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ a.asunto }}</td>
                        <td style="padding:11px 16px; color:#64748B; font-size:12px; max-width:150px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ a.partes_intervinientes || '—' }}</td>
                        <td style="padding:11px 16px; color:#64748B; font-size:12px;">{{ formatFecha(a.fecha_ingreso) }}</td>
                        <td style="padding:11px 16px; text-align:center;">
                            <span :style="estiloEstado(a.estado)">{{ labelEstado(a.estado) }}</span>
                        </td>
                        <td style="padding:11px 16px; text-align:center;">
                            <span :style="estiloPago(a.estado_pago)">{{ labelPago(a.estado_pago) }}</span>
                        </td>
                        <td style="padding:11px 16px; text-align:right; font-weight:700; color:#0F766E;">S/ {{ Number(a.monto_cobrar).toFixed(2) }}</td>
                        <td style="padding:11px 16px; text-align:center; display:flex; gap:5px; justify-content:center;">
                            <button @click="verDetalle(a)" style="padding:4px 10px; background:#EEF2FF; color:#4F46E5; border:1px solid #C7D2FE; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">Ver</button>
                            <button v-if="$page.props.auth.user.rol === 'admin' || $page.props.auth.user.rol === 'cajero'" @click="abrirPago(a)" style="padding:4px 10px; background:#F0FDF4; color:#166534; border:1px solid #BBF7D0; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer;">💰 Pagar</button>
                
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- MODAL NUEVO EXPEDIENTE CON PLANTILLA DINÁMICA -->
        <div v-if="modalNuevo" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:100; display:flex; align-items:center; justify-content:center; padding:1rem;">
            <div style="background:white; border-radius:16px; width:700px; max-width:95vw; max-height:92vh; overflow-y:auto; display:flex; flex-direction:column;">

                <!-- Header modal -->
                <div style="padding:1.25rem 1.5rem; border-bottom:1px solid #E2E8F0; position:sticky; top:0; background:white; z-index:10; border-radius:16px 16px 0 0; display:flex; align-items:center; gap:12px;">
                    <button v-if="pasoNuevo===2" @click="pasoNuevo=1; formNuevo.tipo_acto=''; formDatosNuevo={}" style="background:#F1F5F9; border:none; padding:6px 10px; border-radius:8px; cursor:pointer; font-size:14px;">←</button>
                    <div>
                        <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0;">📄 Nuevo expediente notarial</p>
                        <p v-if="pasoNuevo===1" style="font-size:12px; color:#94A3B8; margin:2px 0 0;">Paso 1 — Selecciona el tipo de servicio</p>
                        <p v-if="pasoNuevo===2" style="font-size:12px; color:#6366F1; margin:2px 0 0; font-weight:600;">Paso 2 — {{ labelTipoActo(formNuevo.tipo_acto) }}</p>
                    </div>
                </div>

                <div style="padding:1.25rem 1.5rem; flex:1;">

                    <!-- PASO 1: Tipo de acto -->
                    <div v-if="pasoNuevo===1" style="margin-bottom:1.2rem;">
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:8px; font-weight:600; text-transform:uppercase; letter-spacing:.04em;">Tipo de servicio *</label>
                        <div>
                            <template v-for="grupo in gruposActo" :key="grupo.nombre">
                                <p style="font-size:10px; font-weight:700; color:#94A3B8; text-transform:uppercase; letter-spacing:.06em; margin:10px 0 6px;">{{ grupo.nombre }}</p>
                                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:6px; margin-bottom:4px;">
                                    <button v-for="t in grupo.tipos" :key="t.value" @click="seleccionarTipo(t.value); pasoNuevo=2"
                                        :style="{
                                            padding:'8px 6px', borderRadius:'8px', border:'2px solid', fontSize:'11px',
                                            fontWeight:'600', cursor:'pointer', textAlign:'center', lineHeight:'1.4',
                                            borderColor: formNuevo.tipo_acto===t.value ? '#6366F1' : '#E2E8F0',
                                            background: formNuevo.tipo_acto===t.value ? '#EEF2FF' : 'white',
                                            color: formNuevo.tipo_acto===t.value ? '#4F46E5' : '#64748B'
                                        }">
                                        {{ t.label }}
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- DATOS GENERALES -->
                    <div v-if="pasoNuevo===2" style="border-top:1px solid #F1F5F9; padding-top:1.2rem; margin-bottom:1.2rem;">
                        <p style="font-size:12px; font-weight:700; color:#6366F1; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">📋 Datos generales</p>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <div style="grid-column:1/-1;">
                                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Asunto / Descripción *</label>
                                <input v-model="formNuevo.asunto" type="text" placeholder="Descripción breve del acto"
                                    style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                            </div>
                            <div>
                                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha ingreso *</label>
                                <input v-model="formNuevo.fecha_ingreso" type="date"
                                    style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                            </div>
                            <div>
                                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Fecha entrega estimada</label>
                                <input v-model="formNuevo.fecha_entrega" type="date"
                                    style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                            </div>
                            <div>
                                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Monto a cobrar (S/) *</label>
                                <input v-model="formNuevo.monto_cobrar" type="number" step="0.01" min="0"
                                    style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                            </div>
                            <div style="grid-column:1/-1;">
                                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Cliente registrado</label>
                                <select v-model="formNuevo.cliente_id"
                                    style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                                    <option value="">Sin cliente registrado</option>
                                    <option v-for="c in listaClientes" :key="c.id" :value="c.id">{{ c.razon_social }}</option>
                                </select>
                                <button @click="modalCliente=true" type="button"
                                    style="margin-top:8px; padding:8px 14px; background:#14B8A6; color:#fff; border:none; border-radius:8px; font-size:12px; cursor:pointer; font-weight:600; display:flex; align-items:center; gap:6px;">
                                    <span style="font-size:16px;">+</span> Nuevo Cliente
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- PLANTILLA ESPECÍFICA POR TIPO -->
                    <div v-if="formNuevo.tipo_acto && camposPlantillaNuevo.length > 0"
                        style="border-top:1px solid #F1F5F9; padding-top:1.2rem; margin-bottom:1.2rem;">
                        <p style="font-size:12px; font-weight:700; color:#14B8A6; text-transform:uppercase; letter-spacing:.04em; margin:0 0 10px;">
                            📋 Datos específicos — {{ labelTipoActo(formNuevo.tipo_acto) }}
                        </p>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <template v-for="campo in camposPlantillaNuevo" :key="campo.key">
                                <div v-if="campo.tipo === 'seccion'" style="grid-column:1/-1; border-top:1px solid #E2E8F0; padding-top:10px; margin-top:4px;">
                                    <p style="font-size:11px; font-weight:700; color:#0F766E; margin:0; text-transform:uppercase; letter-spacing:.04em;">{{ campo.label }}</p>
                                </div>
                                <div v-else-if="campo.tipo === 'checkbox'" style="grid-column:1/-1;">
                                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:#374151;">
                                        <input type="checkbox" v-model="formDatosNuevo[campo.key]" style="width:16px; height:16px;">
                                        {{ campo.label }}
                                    </label>
                                </div>
                                <div v-else :style="campo.full ? 'grid-column:1/-1' : ''">
                                    <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">{{ campo.label }}</label>
                                    <textarea v-if="campo.tipo === 'textarea'" v-model="formDatosNuevo[campo.key]" :rows="campo.rows || 2"
                                        style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                                    <select v-else-if="campo.tipo === 'select'" v-model="formDatosNuevo[campo.key]"
                                        style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                                        <option value="">Seleccionar...</option>
                                        <option v-for="op in campo.opciones" :key="op" :value="op">{{ op }}</option>
                                    </select>
                                    <input v-else v-model="formDatosNuevo[campo.key]" :type="campo.tipo || 'text'"
                                        style="width:100%; padding:8px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                                </div>
                            

</template>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div v-if="formNuevo.tipo_acto" style="border-top:1px solid #F1F5F9; padding-top:1rem;">
                        <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600; text-transform:uppercase;">Observaciones adicionales</label>
                        <textarea v-model="formNuevo.observaciones" rows="2" placeholder="Notas opcionales..."
                            style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                    </div>
                </div>

                <!-- Footer modal -->
                <div style="padding:1rem 1.5rem; border-top:1px solid #E2E8F0; display:flex; gap:8px; justify-content:flex-end; position:sticky; bottom:0; background:white; border-radius:0 0 16px 16px;">
                    <button @click="modalNuevo=false; formDatosNuevo={}; pasoNuevo=1" style="padding:10px 20px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">Cancelar</button>
                    <button @click="guardarNuevo" :disabled="!formNuevo.tipo_acto || !formNuevo.asunto || !formNuevo.monto_cobrar"
                        :style="{padding:'10px 24px', background: formNuevo.tipo_acto && formNuevo.asunto && formNuevo.monto_cobrar ? 'linear-gradient(135deg,#6366F1,#4F46E5)' : '#E2E8F0',
                            color: formNuevo.tipo_acto && formNuevo.asunto && formNuevo.monto_cobrar ? 'white' : '#94A3B8',
                            border:'none', borderRadius:'8px', fontSize:'13px', fontWeight:'700', cursor:'pointer'}">
                        ✅ Crear expediente
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL PAGO -->
        <div v-if="modalPago" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:100; display:flex; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:16px; padding:1.5rem; width:380px; max-width:95vw;">
                <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 4px;">💰 Registrar pago</p>
                <p style="font-size:13px; color:#64748B; margin:0 0 1.2rem;">{{ actoSeleccionado?.numero_expediente }} — {{ actoSeleccionado?.asunto }}</p>
                <div style="background:#F8FAFC; border-radius:10px; padding:12px 16px; margin-bottom:1rem;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                        <span style="font-size:13px; color:#64748B;">Total a cobrar</span>
                        <span style="font-size:13px; font-weight:700;">S/ {{ Number(actoSeleccionado?.monto_cobrar).toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                        <span style="font-size:13px; color:#64748B;">Ya pagado</span>
                        <span style="font-size:13px; font-weight:700; color:#0F766E;">S/ {{ Number(actoSeleccionado?.monto_pagado).toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="font-size:13px; color:#64748B;">Saldo pendiente</span>
                        <span style="font-size:14px; font-weight:800; color:#EF4444;">S/ {{ (Number(actoSeleccionado?.monto_cobrar) - Number(actoSeleccionado?.monto_pagado)).toFixed(2) }}</span>
                    </div>
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Monto a pagar (S/) *</label>
                    <input v-model="formPago.monto" type="number" step="0.01" min="0.01"
                        style="width:100%; padding:12px; border:2px solid #E2E8F0; border-radius:10px; font-size:18px; font-weight:700; outline:none; box-sizing:border-box; text-align:right;" />
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <button @click="modalPago=false" style="padding:9px 18px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Cancelar</button>
                    <button @click="guardarPago" style="padding:9px 18px; background:#10B981; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">✅ Confirmar pago</button>
                </div>
            </div>
        </div>

        <!-- Modal Nuevo Cliente -->
        <div v-if="modalCliente" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:999;">
            <div style="background:white; border-radius:16px; padding:24px; width:420px; max-width:90vw;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="margin:0; font-size:16px; font-weight:700;">👤 Nuevo Cliente</h3>
                    <button @click="modalCliente=false" style="background:#F1F5F9; border:none; padding:6px 12px; border-radius:8px; cursor:pointer;">✕</button>
                </div>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <div style="display:flex; gap:8px;">
                        <select v-model="formCliente.tipo_documento" style="padding:9px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px;">
                            <option value="1">DNI</option>
                            <option value="6">RUC</option>
                        </select>
                        <input v-model="formCliente.numero_documento" type="text" :placeholder="formCliente.tipo_documento==='6' ? 'RUC 20xxxxxxxxx' : 'DNI 12345678'"
                            style="flex:1; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;" />
                    </div>
                    <input v-model="formCliente.razon_social" type="text" placeholder="Nombre o Razón Social *"
                        style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    <input v-model="formCliente.direccion" type="text" placeholder="Dirección (opcional)"
                        style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    <input v-model="formCliente.email" type="email" placeholder="Email (opcional)"
                        style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    <input v-model="formCliente.telefono" type="text" placeholder="Teléfono (opcional)"
                        style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                    <div v-if="errorCliente" style="background:#FEF2F2; padding:8px; border-radius:8px; font-size:12px; color:#991B1B;">{{ errorCliente }}</div>
                    <button @click="guardarCliente" :disabled="guardandoCliente"
                        style="width:100%; padding:12px; background:linear-gradient(135deg,#14B8A6,#0D9488); color:white; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer;">
                        {{ guardandoCliente ? 'Guardando...' : '💾 Guardar cliente' }}
                    </button>
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
    actos:    { type: Array,  default: () => [] },
    resumen:  { type: Object, default: () => ({}) },
    clientes: { type: Array,  default: () => [] },
    filtros:  { type: Object, default: () => ({}) },
})

const filtros         = ref({ ...props.filtros })
const modalNuevo      = ref(false)
const pasoNuevo       = ref(1)
const listaClientes   = ref([...props.clientes])
const modalCliente    = ref(false)
const formCliente     = ref({ tipo_documento: '1', numero_documento: '', razon_social: '', direccion: '', email: '', telefono: '' })
const errorCliente    = ref('')
const guardandoCliente = ref(false)

async function guardarCliente() {
    if (!formCliente.value.razon_social || !formCliente.value.numero_documento) {
        errorCliente.value = 'Ingrese documento y nombre del cliente'
        return
    }
    guardandoCliente.value = true
    errorCliente.value = ''
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        const res = await fetch('/notaria/clientes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(formCliente.value)
        })
        const data = await res.json()
        if (data.id || data.success) {
            // Agregar cliente a la lista sin recargar
            listaClientes.value.push({ id: data.id, razon_social: data.razon_social, numero_documento: formCliente.value.numero_documento })
            formNuevo.value.cliente_id = data.id
            modalCliente.value = false
            formCliente.value = { tipo_documento: '1', numero_documento: '', razon_social: '', direccion: '', email: '', telefono: '' }
        } else {
            errorCliente.value = data.message || 'Error al guardar'
        }
    } catch(e) {
        errorCliente.value = 'Error de conexión'
    }
    guardandoCliente.value = false
}
const modalMinuta     = ref(false)
const actoMinuta      = ref(null)
const generandoMinuta = ref(false)
const formMinuta      = ref({
    vendedor_tipo: 'empresa',
    vendedor_razon_social: '', vendedor_ruc: '', vendedor_domicilio: '', vendedor_partida_registral: '',
    representante_cargo: 'Gerente General', representante_nombre: '', representante_dni: '',
    representante_estado_civil: 'soltero', representante_profesion: '', representante_domicilio: '',
    vendedor_nombre: '', vendedor_dni: '', vendedor_estado_civil: '',
    comprador_nombre: '', comprador_dni: '', comprador_estado_civil: '', comprador_profesion: '', comprador_domicilio: '',
    es_bien_futuro: false,
    predio_descripcion: '', predio_partida: '', ciudad: 'Huánuco',
    proyecto_descripcion: '', proyecto_municipalidad: '', proyecto_expediente: '', proyecto_fecha: '', proyecto_arquitecto: '', plazo_anos: 'tres',
    lote_descripcion: '', lote_area: '', lote_area_letras: '',
    lindero_frente: '', medida_frente: '', lindero_derecha: '', medida_derecha: '',
    lindero_izquierda: '', medida_izquierda: '', lindero_fondo: '', medida_fondo: '',
    precio_total: '', precio_total_letras: '', forma_pago_detalle: '', fecha_minuta: '',
})

function abrirMinuta(acto) {
    actoMinuta.value = acto
    formMinuta.value.comprador_nombre = acto.partes_intervinientes || ''
    formMinuta.value.precio_total = acto.monto_cobrar || ''
    modalMinuta.value = true
}

async function generarMinuta() {
    generandoMinuta.value = true
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        const res = await fetch('/notaria/actos/' + actoMinuta.value.id + '/minuta-compraventa', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(formMinuta.value)
        })
        if (res.ok) {
            const blob = await res.blob()
            const url = URL.createObjectURL(blob)
            const a = document.createElement('a')
            a.href = url
            a.download = 'Minuta-CompraVenta-' + actoMinuta.value.numero_expediente + '.pdf'
            a.click()
            URL.revokeObjectURL(url)
            modalMinuta.value = false
        } else {
            alert('❌ Error al generar la minuta')
        }
    } catch(e) {
        alert('❌ Error: ' + e.message)
    }
    generandoMinuta.value = false
}

const modalPago       = ref(false)
const actoSeleccionado = ref(null)
const formNuevo = ref({ tipo_acto: '', asunto: '', partes_intervinientes: '', fecha_ingreso: new Date(new Date().getTime() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0,10), fecha_entrega: '', monto_cobrar: '', cliente_id: '', observaciones: '' })
const formPago  = ref({ monto: '' })

const tiposActo = [
    // Transferencias inmuebles
    { value: 'compra_venta',             label: '🏠 Compra Venta',                    grupo: 'Inmuebles' },
    { value: 'compra_venta_bien_futuro', label: '🏗️ CV Bien Futuro',                  grupo: 'Inmuebles' },
    { value: 'compra_venta_hipoteca',    label: '🏦 CV con Hipoteca',                 grupo: 'Inmuebles' },
    { value: 'compra_venta_alicuotas',   label: '📐 CV Acciones y Derechos',          grupo: 'Inmuebles' },
    { value: 'aclaracion_compra_venta',  label: '🔍 Aclaración CV',                   grupo: 'Inmuebles' },
    { value: 'ratificacion_compra_venta',label: '✅ Ratificación CV',                  grupo: 'Inmuebles' },
    { value: 'contrato_preparatorio',    label: '📝 Contrato Preparatorio CV',         grupo: 'Inmuebles' },
    { value: 'adjudicacion',             label: '🏛️ Adjudicación Inmueble',            grupo: 'Inmuebles' },
    { value: 'rectificacion_area',       label: '📏 Rectificación de Área',            grupo: 'Inmuebles' },
    { value: 'particion',                label: '✂️ Partición',                        grupo: 'Inmuebles' },
    // Donaciones
    { value: 'donacion_inmueble',        label: '🎁 Donación Inmueble',               grupo: 'Donaciones' },
    { value: 'donacion_alicuotas',       label: '🎁 Donación Alícuotas',              grupo: 'Donaciones' },
    { value: 'donacion_vehiculo',        label: '🚗 Donación Vehículo',               grupo: 'Donaciones' },
    // Vehículos
    { value: 'transferencia_vehicular',  label: '🚙 Transferencia Vehicular',         grupo: 'Vehículos' },
    // Hipotecas / Mutuos
    { value: 'hipoteca',                 label: '🏦 Hipoteca',                        grupo: 'Hipotecas' },
    { value: 'mutuo_hipoteca',           label: '💰 Mutuo con Hipoteca',              grupo: 'Hipotecas' },
    // Poderes
    { value: 'poder',                    label: '✍️ Poder General y Especial',        grupo: 'Poderes' },
    { value: 'ampliacion_poder',         label: '✍️ Ampliación de Poder',             grupo: 'Poderes' },
    { value: 'revocatoria_poder',        label: '🚫 Revocatoria de Poder',            grupo: 'Poderes' },
    // Empresas
    { value: 'constitucion_sac',         label: '🏢 Constitución SAC',               grupo: 'Empresas' },
    { value: 'constitucion_srl',         label: '🏢 Constitución SRL',               grupo: 'Empresas' },
    { value: 'constitucion_asociacion',  label: '🤝 Constitución Asociación',         grupo: 'Empresas' },
    { value: 'aumento_capital',          label: '📈 Aumento de Capital',              grupo: 'Empresas' },
    { value: 'transformacion_empresa',   label: '🔄 Transformación Empresa',          grupo: 'Empresas' },
    // Régimen patrimonial
    { value: 'sustitucion_regimen',      label: '💍 Sustitución Régimen Patrimonial', grupo: 'Familia' },
    { value: 'cese_regimen',             label: '💍 Cese Sustitución Régimen',        grupo: 'Familia' },
    // Familia / personal
    { value: 'testamento',               label: '📋 Testamento',                      grupo: 'Familia' },
    { value: 'reconocimiento_paternidad',label: '👶 Reconocimiento Paternidad',       grupo: 'Familia' },
    { value: 'autorizacion_viaje',       label: '✈️ Autorización Viaje Menor',        grupo: 'Familia' },
    { value: 'autorizacion_viaje_ext',   label: '🌎 Autorización Viaje Exterior',     grupo: 'Familia' },
    { value: 'divorcio',                 label: '⚖️ Divorcio Convencional',           grupo: 'Familia' },
    // No contenciosos / otros
    { value: 'sucesion_intestada',       label: '📜 Sucesión Intestada',              grupo: 'Otros' },
    { value: 'certificacion_notarial',   label: '🔏 Certificación Notarial',          grupo: 'Otros' },
    { value: 'arrendamiento',            label: '🔑 Arrendamiento',                   grupo: 'Otros' },
    { value: 'carta_notarial',           label: '✉️ Carta Notarial',                  grupo: 'Otros' },
    { value: 'otro',                     label: '📁 Otro',                            grupo: 'Otros' },
]

const gruposActo = computed(() => {
    const grupos = {}
    tiposActo.forEach(t => {
        if (!grupos[t.grupo]) grupos[t.grupo] = []
        grupos[t.grupo].push(t)
    })
    return Object.entries(grupos).map(([nombre, tipos]) => ({ nombre, tipos }))
})

const tarjetas = computed(() => [
    { label: 'Total expedientes', valor: props.resumen.total },
    { label: 'Pendientes',        valor: props.resumen.pendientes,  color: '#F59E0B' },
    { label: 'En proceso',        valor: props.resumen.en_proceso,  color: '#3B82F6' },
    { label: 'Finalizados',       valor: props.resumen.finalizados, color: '#10B981' },
    { label: 'Por cobrar',        valor: 'S/ ' + Number(props.resumen.por_cobrar).toFixed(2), color: '#EF4444' },
    { label: 'Cobrado',           valor: 'S/ ' + Number(props.resumen.cobrado).toFixed(2),    color: '#10B981' },
])

function buscar() {
    const params = new URLSearchParams()
    if (filtros.value.desde)  params.set('desde',  filtros.value.desde)
    if (filtros.value.hasta)  params.set('hasta',  filtros.value.hasta)
    if (filtros.value.tipo)   params.set('tipo',   filtros.value.tipo)
    if (filtros.value.estado) params.set('estado', filtros.value.estado)
    if (filtros.value.buscar) params.set('buscar', filtros.value.buscar)
    router.visit('/notaria/actos?' + params.toString(), { preserveScroll: true })
}

async function guardarNuevo() {
    if (!formNuevo.value.tipo_acto || !formNuevo.value.asunto || !formNuevo.value.monto_cobrar) {
        alert('Completa los campos obligatorios')
        return
    }

    const esEscritura = formNuevo.value.tipo_acto === 'escritura_publica'

    // Si es escritura pública, guardar y generar minuta en un solo paso
    if (esEscritura) {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        const res = await fetch('/notaria/actos/crear-con-minuta', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ ...formNuevo.value, datos: formDatosNuevo.value })
        })
        if (res.status === 419) { alert('Sesión expirada, recarga'); window.location.reload(); return }
        if (res.ok) {
            const blob = await res.blob()
            const url = URL.createObjectURL(blob)
            const a = document.createElement('a')
            a.href = url
            a.download = 'Minuta-CompraVenta.pdf'
            a.click()
            URL.revokeObjectURL(url)
            modalNuevo.value = false
            pasoNuevo.value = 1
            formNuevo.value = { tipo_acto: '', asunto: '', partes_intervinientes: '', fecha_ingreso: new Date(new Date().getTime() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0,10), fecha_entrega: '', monto_cobrar: '', cliente_id: '', observaciones: '' }
            formDatosNuevo.value = {}
            router.reload({ preserveScroll: true })
        } else {
            const err = await res.json().catch(() => ({}))
            alert('❌ Error: ' + (err.mensaje || 'No se pudo crear el expediente'))
        }
        return
    }

    router.post('/notaria/actos', { ...formNuevo.value, datos: formDatosNuevo.value }, {
        preserveScroll: true,
        onSuccess: () => {
            modalNuevo.value = false
            pasoNuevo.value = 1
            formNuevo.value = { tipo_acto: '', asunto: '', partes_intervinientes: '', fecha_ingreso: new Date(new Date().getTime() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0,10), fecha_entrega: '', monto_cobrar: '', cliente_id: '', observaciones: '' }
            formDatosNuevo.value = {}
        }
    })
}

function verDetalle(acto) {
    router.visit('/notaria/actos/' + acto.id)
}

function irACaja(acto) {
    router.visit('/notaria/caja?buscar=' + acto.numero_expediente)
}

function abrirPago(acto) {
    actoSeleccionado.value = acto
    formPago.value = { monto: (Number(acto.monto_cobrar) - Number(acto.monto_pagado)).toFixed(2) }
    modalPago.value = true
}

function guardarPago() {
    if (!formPago.value.monto) { alert('Ingresa el monto'); return }
    router.post('/notaria/actos/' + actoSeleccionado.value.id + '/pago', formPago.value, {
        preserveScroll: true,
        onSuccess: () => { modalPago.value = false }
    })
}

function formatFecha(f) {
    if (!f) return '—'
    const fecha = f.includes('T') || f.includes(' ') ? new Date(f) : new Date(f + 'T12:00:00')
    if (isNaN(fecha)) return '—'
    return fecha.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: '2-digit' })
}

function labelTipo(t) {
    return tiposActo.find(x => x.value === t)?.label ?? t
}

function estiloTipo(t) {
    const map = {
        escritura_publica: { background: '#EEF2FF', color: '#4338CA' },
        poder:             { background: '#FEF3C7', color: '#92400E' },
        testamento:        { background: '#F5F3FF', color: '#6D28D9' },
        legalizacion:      { background: '#ECFDF5', color: '#065F46' },
        carta_notarial:    { background: '#EFF6FF', color: '#1D4ED8' },
        protesto:          { background: '#FEF2F2', color: '#991B1B' },
        acta_notarial:     { background: '#F0FDF4', color: '#166534' },
        otro:              { background: '#F1F5F9', color: '#475569' },
    }
    return { ...(map[t] || map.otro), fontSize: '11px', padding: '3px 9px', borderRadius: '20px', fontWeight: '600', whiteSpace: 'nowrap' }
}

function labelEstado(e) {
    return { pendiente: '🕐 Pendiente', en_proceso: '🔄 En proceso', finalizado: '✅ Finalizado', cancelado: '❌ Cancelado' }[e] ?? e
}

function estiloEstado(e) {
    const map = {
        pendiente:  { background: '#FEF3C7', color: '#92400E' },
        en_proceso: { background: '#EFF6FF', color: '#1D4ED8' },
        finalizado: { background: '#F0FDF4', color: '#166534' },
        cancelado:  { background: '#FEF2F2', color: '#991B1B' },
    }
    return { ...(map[e] || {}), fontSize: '11px', padding: '3px 9px', borderRadius: '20px', fontWeight: '600', whiteSpace: 'nowrap' }
}

function labelPago(p) {
    return { pendiente: '⏳ Pendiente', parcial: '◑ Parcial', pagado: '✅ Pagado' }[p] ?? p
}

function estiloPago(p) {
    const map = {
        pendiente: { background: '#FEF2F2', color: '#991B1B' },
        parcial:   { background: '#FEF3C7', color: '#92400E' },
        pagado:    { background: '#F0FDF4', color: '#166534' },
    }
    return { ...(map[p] || {}), fontSize: '11px', padding: '3px 9px', borderRadius: '20px', fontWeight: '600', whiteSpace: 'nowrap' }
}

// ── Plantillas por tipo de acto ──
const formDatosNuevo = ref({})

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


function seleccionarTipo(tipo) {
    formNuevo.value.tipo_acto = tipo
    formDatosNuevo.value = {}
}

const camposPlantillaNuevo = computed(() => plantillas[formNuevo.value.tipo_acto] || [])

function labelTipoActo(t) {
    return tiposActo.find(x => x.value === t)?.label ?? t
}
</script>
