<template>
    <AppLayout :title="'Expediente ' + acto.numero_expediente" subtitle="Detalle del acto notarial">

        <!-- HEADER -->
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:1.5rem;">
            <a href="/notaria/actos" style="background:#F1F5F9; border:none; border-radius:10px; padding:9px 14px; font-size:14px; cursor:pointer; text-decoration:none; color:#475569; font-weight:600;">← Volver</a>
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

                <!-- PLANTILLA DATOS ESPECÍFICOS -->
                <div style="background:white; border-radius:12px; border:1px solid #E2E8F0; padding:1.25rem;">
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
                        </template>
                    </div>
                </div>

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
                        @click="modalComprobante=true"
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

        <!-- MODAL ACTUALIZAR ESTADO -->
        <div v-if="modalEstado" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:100; display:flex; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:16px; padding:1.5rem; width:400px; max-width:95vw;">
                <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 1.2rem;">🔄 Actualizar estado</p>
                <div style="margin-bottom:1rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Nuevo estado *</label>
                    <select v-model="formEstado.estado" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none;">
                        <option v-for="e in estados" :key="e.value" :value="e.value">{{ e.icon }} {{ e.label }}</option>
                    </select>
                </div>
                <div style="margin-bottom:1.2rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Comentario</label>
                    <textarea v-model="formEstado.comentario" rows="3" placeholder="Detalle del cambio de estado..." style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; resize:none;"></textarea>
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <button @click="modalEstado=false" style="padding:9px 18px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Cancelar</button>
                    <button @click="guardarEstado" style="padding:9px 18px; background:#4F46E5; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">Guardar</button>
                </div>
            </div>
        </div>

        <!-- MODAL COMPROBANTE ELECTRÓNICO -->
    <div v-if="modalComprobante" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:100; display:flex; align-items:center; justify-content:center;">
        <div style="background:white; border-radius:16px; padding:1.5rem; width:460px; max-width:95vw;">
            <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 4px;">🧾 Emitir comprobante electrónico</p>
            <p style="font-size:12px; color:#94A3B8; margin:0 0 1.2rem;">{{ acto.numero_expediente }} — {{ acto.asunto }}</p>

            <!-- Tipo comprobante -->
            <div style="margin-bottom:1rem;">
                <label style="font-size:11px; color:#64748B; display:block; margin-bottom:6px; font-weight:600; text-transform:uppercase;">Tipo de comprobante</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <button @click="formComp.tipo_comprobante='03'; formComp.cliente_tipo_documento='1'"
                        :style="{padding:'10px', borderRadius:'8px', border:'2px solid', fontSize:'13px', fontWeight:'700', cursor:'pointer',
                            borderColor: formComp.tipo_comprobante==='03'?'#14B8A6':'#E2E8F0',
                            background: formComp.tipo_comprobante==='03'?'#F0FDFA':'white',
                            color: formComp.tipo_comprobante==='03'?'#0F766E':'#64748B'}">
                        🧾 Boleta<br><span style="font-size:10px; font-weight:400;">Para personas naturales</span>
                    </button>
                    <button @click="formComp.tipo_comprobante='01'; formComp.cliente_tipo_documento='6'"
                        :style="{padding:'10px', borderRadius:'8px', border:'2px solid', fontSize:'13px', fontWeight:'700', cursor:'pointer',
                            borderColor: formComp.tipo_comprobante==='01'?'#6366F1':'#E2E8F0',
                            background: formComp.tipo_comprobante==='01'?'#EEF2FF':'white',
                            color: formComp.tipo_comprobante==='01'?'#4F46E5':'#64748B'}">
                        📄 Factura<br><span style="font-size:10px; font-weight:400;">Para empresas con RUC</span>
                    </button>
                </div>
            </div>

            <!-- Datos cliente -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:1rem;">
                <div>
                    <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">{{ formComp.tipo_comprobante==='01' ? 'RUC' : 'DNI' }} *</label>
                    <input v-model="formComp.cliente_numero_documento" type="text"
                        :placeholder="formComp.tipo_comprobante==='01' ? '20xxxxxxxxx' : '12345678'"
                        :maxlength="formComp.tipo_comprobante==='01' ? 11 : 8"
                        style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                </div>
                <div>
                    <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">{{ formComp.tipo_comprobante==='01' ? 'Razón social' : 'Nombre completo' }} *</label>
                    <input v-model="formComp.cliente_nombre" type="text" placeholder="Nombre o razón social"
                        style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                </div>
                <div style="grid-column:1/-1;">
                    <label style="font-size:11px; color:#64748B; display:block; margin-bottom:3px; font-weight:600;">Email (para envío)</label>
                    <input v-model="formComp.cliente_email" type="email" placeholder="correo@ejemplo.com"
                        style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;" />
                </div>
            </div>

            <!-- Resumen monto -->
            <div style="background:#F8FAFC; border-radius:10px; padding:12px 14px; margin-bottom:1rem;">
                <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                    <span style="font-size:12px; color:#64748B;">Subtotal</span>
                    <span style="font-size:12px;">S/ {{ (Number(acto.monto_cobrar) / 1.18).toFixed(2) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                    <span style="font-size:12px; color:#64748B;">IGV (18%)</span>
                    <span style="font-size:12px;">S/ {{ (Number(acto.monto_cobrar) - Number(acto.monto_cobrar) / 1.18).toFixed(2) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; border-top:1px solid #E2E8F0; padding-top:6px; margin-top:4px;">
                    <span style="font-size:14px; font-weight:700;">Total</span>
                    <span style="font-size:16px; font-weight:800; color:#0F766E;">S/ {{ Number(acto.monto_cobrar).toFixed(2) }}</span>
                </div>
            </div>

            <!-- Error -->
            <div v-if="errorComp" style="background:#FEF2F2; border:1px solid #FECACA; border-radius:8px; padding:10px 12px; margin-bottom:1rem; font-size:12px; color:#991B1B;">
                ❌ {{ errorComp }}
            </div>

            <!-- Success -->
            <div v-if="pdfComp" style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:10px 12px; margin-bottom:1rem; font-size:12px; color:#166534;">
                ✅ Comprobante emitido correctamente.
                <a :href="pdfComp" target="_blank" style="font-weight:700; color:#0F766E; margin-left:6px;">📥 Descargar PDF</a>
            </div>

            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button @click="modalComprobante=false; errorComp=''; pdfComp=''" style="padding:9px 18px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Cerrar</button>
                <button @click="emitirComprobante" :disabled="emitiendo"
                    style="padding:9px 20px; background:linear-gradient(135deg,#6366F1,#4F46E5); color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">
                    {{ emitiendo ? '⏳ Emitiendo...' : '🧾 Emitir a SUNAT' }}
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL PAGO -->
        <div v-if="modalPago" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:100; display:flex; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:16px; padding:1.5rem; width:360px; max-width:95vw;">
                <p style="font-size:16px; font-weight:700; color:#1E293B; margin:0 0 1.2rem;">💰 Registrar pago</p>
                <div style="margin-bottom:1rem;">
                    <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Monto a pagar (S/) *</label>
                    <input v-model="formPago.monto" type="number" step="0.01" min="0.01"
                        style="width:100%; padding:12px; border:2px solid #E2E8F0; border-radius:10px; font-size:20px; font-weight:700; outline:none; box-sizing:border-box; text-align:right;" />
                </div>
                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <button @click="modalPago=false" style="padding:9px 18px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">Cancelar</button>
                    <button @click="guardarPago" style="padding:9px 18px; background:#10B981; color:white; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">✅ Confirmar</button>
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
    acto:  { type: Object, default: () => ({}) },
    datos: { type: Object, default: () => ({}) },
})

// Plantillas por tipo de acto
const plantillas = {
    escritura_publica: [
        { key: 'vendedor',        label: 'Vendedor(es)',          tipo: 'text',     full: true },
        { key: 'comprador',       label: 'Comprador(es)',         tipo: 'text',     full: true },
        { key: 'bien_inmueble',   label: 'Descripción del bien',  tipo: 'textarea', full: true, rows: 3 },
        { key: 'partida_registral',label: 'Partida registral',    tipo: 'text' },
        { key: 'valor_venta',     label: 'Valor de venta (S/)',   tipo: 'number' },
        { key: 'forma_pago',      label: 'Forma de pago',         tipo: 'select', opciones: ['Contado','Crédito','Mixto'] },
        { key: 'cargas',          label: 'Cargas y gravámenes',   tipo: 'textarea', full: true, rows: 2 },
        { key: 'observaciones_escritura', label: 'Observaciones', tipo: 'textarea', full: true, rows: 2 },
    ],
    poder: [
        { key: 'poderdante',      label: 'Poderdante',            tipo: 'text',     full: true },
        { key: 'apoderado',       label: 'Apoderado',             tipo: 'text',     full: true },
        { key: 'tipo_poder',      label: 'Tipo de poder',         tipo: 'select', opciones: ['Poder especial','Poder general','Poder específico','Poder irrevocable'] },
        { key: 'facultades',      label: 'Facultades otorgadas',  tipo: 'textarea', full: true, rows: 3 },
        { key: 'vigencia',        label: 'Vigencia',              tipo: 'select', opciones: ['Sin límite','1 año','2 años','3 años','Hasta revocación'] },
        { key: 'limitaciones',    label: 'Limitaciones',          tipo: 'textarea', full: true, rows: 2 },
    ],
    testamento: [
        { key: 'testador',        label: 'Testador',              tipo: 'text',     full: true },
        { key: 'dni_testador',    label: 'DNI del testador',      tipo: 'text' },
        { key: 'estado_civil',    label: 'Estado civil',          tipo: 'select', opciones: ['Soltero','Casado','Viudo','Divorciado'] },
        { key: 'herederos',       label: 'Herederos',             tipo: 'textarea', full: true, rows: 3 },
        { key: 'bienes',          label: 'Bienes a legar',        tipo: 'textarea', full: true, rows: 3 },
        { key: 'albaceas',        label: 'Albaceas',              tipo: 'text',     full: true },
        { key: 'disposiciones',   label: 'Disposiciones especiales', tipo: 'textarea', full: true, rows: 2 },
    ],
    legalizacion: [
        { key: 'tipo_doc',        label: 'Tipo de documento',     tipo: 'select', opciones: ['Contrato','Acta','Carta','Solicitud','DNI','Otro'] },
        { key: 'num_hojas',       label: 'N° de hojas',           tipo: 'number' },
        { key: 'num_copias',      label: 'N° de copias',          tipo: 'number' },
        { key: 'firmante',        label: 'Firmante',              tipo: 'text',     full: true },
        { key: 'dni_firmante',    label: 'DNI del firmante',      tipo: 'text' },
        { key: 'finalidad',       label: 'Finalidad del documento', tipo: 'textarea', full: true, rows: 2 },
    ],
    carta_notarial: [
        { key: 'remitente',       label: 'Remitente',             tipo: 'text',     full: true },
        { key: 'dni_remitente',   label: 'DNI/RUC remitente',     tipo: 'text' },
        { key: 'destinatario',    label: 'Destinatario',          tipo: 'text',     full: true },
        { key: 'dir_destinatario',label: 'Dirección destinatario',tipo: 'text',     full: true },
        { key: 'asunto_carta',    label: 'Asunto de la carta',    tipo: 'textarea', full: true, rows: 2 },
        { key: 'plazo_respuesta', label: 'Plazo de respuesta',    tipo: 'select', opciones: ['24 horas','3 días','5 días','7 días','15 días','30 días','Sin plazo'] },
        { key: 'contenido',       label: 'Contenido de la carta', tipo: 'textarea', full: true, rows: 4 },
    ],
    protesto: [
        { key: 'tipo_titulo',     label: 'Tipo de título valor',  tipo: 'select', opciones: ['Cheque','Pagaré','Letra de cambio','Factura negociable'] },
        { key: 'num_documento',   label: 'N° de documento',       tipo: 'text' },
        { key: 'monto_titulo',    label: 'Monto (S/)',             tipo: 'number' },
        { key: 'fecha_venc',      label: 'Fecha de vencimiento',  tipo: 'date' },
        { key: 'girador',         label: 'Girador/Emisor',        tipo: 'text',     full: true },
        { key: 'aceptante',       label: 'Aceptante/Deudor',      tipo: 'text',     full: true },
        { key: 'tenedor',         label: 'Tenedor/Acreedor',      tipo: 'text',     full: true },
        { key: 'motivo_protesto', label: 'Motivo del protesto',   tipo: 'select', opciones: ['Falta de pago','Falta de aceptación','Falta de fecha de aceptación'] },
    ],
    acta_notarial: [
        { key: 'tipo_acta',       label: 'Tipo de acta',          tipo: 'select', opciones: ['Acta de constatación','Acta de destrucción','Acta de transferencia','Acta de entrega','Otro'] },
        { key: 'lugar',           label: 'Lugar de realización',  tipo: 'text',     full: true },
        { key: 'fecha_acta',      label: 'Fecha del acta',        tipo: 'date' },
        { key: 'comparecientes',  label: 'Comparecientes',        tipo: 'textarea', full: true, rows: 3 },
        { key: 'hechos',          label: 'Hechos constatados',    tipo: 'textarea', full: true, rows: 4 },
    ],
    otro: [
        { key: 'descripcion',     label: 'Descripción del acto',  tipo: 'textarea', full: true, rows: 3 },
        { key: 'partes',          label: 'Partes intervinientes', tipo: 'textarea', full: true, rows: 2 },
        { key: 'detalles',        label: 'Detalles adicionales',  tipo: 'textarea', full: true, rows: 3 },
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

const modalComprobante = ref(false)
const emitiendo        = ref(false)
const errorComp        = ref('')
const pdfComp          = ref('')
const formComp = ref({
    tipo_comprobante:         '03',
    cliente_tipo_documento:   '1',
    cliente_numero_documento: '',
    cliente_nombre:           '',
    cliente_email:            '',
})

async function emitirComprobante() {
    if (!formComp.value.cliente_numero_documento || !formComp.value.cliente_nombre) {
        errorComp.value = 'Completa el documento y nombre del cliente'
        return
    }
    emitiendo.value = true
    errorComp.value = ''
    pdfComp.value   = ''
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        const res = await fetch('/notaria/comprobantes/' + props.acto.id + '/emitir', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(formComp.value)
        })
        const data = await res.json()
        if (data.success) {
            pdfComp.value = data.pdf
            if (data.pdf) window.open(data.pdf, '_blank')
        } else {
            errorComp.value = data.mensaje || 'Error al emitir'
        }
    } catch(e) {
        errorComp.value = 'Error de conexión: ' + e.message
    } finally {
        emitiendo.value = false
    }
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
    // Manejar tanto fechas con y sin hora
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
</script>
