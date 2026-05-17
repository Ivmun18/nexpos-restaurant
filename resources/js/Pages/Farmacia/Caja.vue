<template>
    <AppLayout title="Caja" subtitle="Apertura y cierre de caja">

        <!-- Fecha -->
        <div style="margin-bottom:24px;">
            <p style="font-size:14px; color:#94A3B8; margin:0;">📅 {{ fecha }}</p>
        </div>

        <!-- ══ CAJA CERRADA: Apertura ══ -->
        <div v-if="!caja_abierta" style="max-width:500px; margin:0 auto;">
            <div style="background:white; border-radius:20px; padding:32px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06); text-align:center;">
                <div style="font-size:60px; margin-bottom:16px;">🔒</div>
                <p style="font-size:24px; font-weight:800; color:#1E293B; margin:0 0 8px;">Caja cerrada</p>
                <p style="font-size:14px; color:#94A3B8; margin:0 0 32px;">Ingresa el monto inicial para abrir la caja</p>

                <div style="text-align:left; margin-bottom:20px;">
                    <label style="font-size:13px; font-weight:600; color:#64748B;">Monto inicial (efectivo)</label>
                    <input v-model="montoInicial" type="number" step="0.01" placeholder="0.00"
                        style="width:100%; padding:14px 16px; border:2px solid #E2E8F0; border-radius:12px; font-size:20px; font-weight:700; outline:none; box-sizing:border-box; margin-top:8px; text-align:center;"
                        @focus="$event.target.style.borderColor='#14B8A6'"
                        @blur="$event.target.style.borderColor='#E2E8F0'" />
                </div>

                <button @click="abrirCaja"
                    style="width:100%; padding:14px; background:linear-gradient(135deg,#14B8A6,#0F766E); color:white; border-radius:12px; font-size:16px; font-weight:700; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(20,184,166,0.3);">
                    🔓 Abrir Caja
                </button>
            </div>

            <!-- Historial -->
            <div v-if="historial_cajas.length" style="display:flex; justify-content:flex-end; margin-top:24px; margin-bottom:-8px;">
                <button @click="abrirModalCorreccion(historial_cajas[0])"
                    style="background:linear-gradient(135deg,#F59E0B,#D97706); color:white; border:none; padding:8px 18px; border-radius:10px; font-size:13px; font-weight:600; cursor:pointer; box-shadow:0 4px 12px rgba(245,158,11,0.3);">
                    ✏️ Corregir última caja
                </button>
            </div>
            <div v-if="historial_cajas.length" style="margin-top:24px; background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0;">
                <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 16px;">📋 Historial de cajas</p>
                <div v-for="c in historial_cajas" :key="c.id"
                    @click="cajaDetalle = cajaDetalle?.id === c.id ? null : c"
                    style="cursor:pointer; border-bottom:1px solid #F1F5F9;">
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0;">
                        <div>
                            <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ formatFecha(c.apertura_at) }}</p>
                            <p style="font-size:12px; color:#94A3B8; margin:2px 0 0;">{{ c.cantidad_ventas }} ventas</p>
                        </div>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="text-align:right;">
                                <p style="font-size:16px; font-weight:800; color:#14B8A6; margin:0;">S/ {{ Number(c.total_ventas).toFixed(2) }}</p>
                                <p :style="{ fontSize:'12px', margin:'2px 0 0', color: c.diferencia >= 0 ? '#166534' : '#991B1B', fontWeight:'600' }">
                                    {{ c.diferencia >= 0 ? '+' : '' }}{{ Number(c.diferencia).toFixed(2) }} diferencia
                                </p>
                            </div>
                            <span style="font-size:12px; color:#94A3B8;">{{ cajaDetalle?.id === c.id ? '▲' : '▼' }}</span>
                        </div>
                    </div>
                    <div v-if="cajaDetalle?.id === c.id" style="background:#F8FAFC; border-radius:10px; padding:14px; margin-bottom:10px;">
                        <p style="font-size:13px; font-weight:700; color:#1E293B; margin:0 0 10px;">📊 Detalle de caja</p>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:12px;">
                            <div style="background:white; border-radius:8px; padding:10px; border:1px solid #E2E8F0;">
                                <p style="font-size:11px; color:#94A3B8; margin:0;">Monto inicial</p>
                                <p style="font-size:15px; font-weight:700; color:#1E293B; margin:4px 0 0;">S/ {{ Number(c.monto_inicial).toFixed(2) }}</p>
                            </div>
                            <div style="background:white; border-radius:8px; padding:10px; border:1px solid #E2E8F0;">
                                <p style="font-size:11px; color:#94A3B8; margin:0;">Monto final</p>
                                <p style="font-size:15px; font-weight:700; color:#1E293B; margin:4px 0 0;">S/ {{ Number(c.monto_final || 0).toFixed(2) }}</p>
                            </div>
                            <div style="background:white; border-radius:8px; padding:10px; border:1px solid #E2E8F0;">
                                <p style="font-size:11px; color:#94A3B8; margin:0;">💵 Efectivo</p>
                                <p style="font-size:15px; font-weight:700; color:#1E293B; margin:4px 0 0;">S/ {{ Number(c.total_efectivo || 0).toFixed(2) }}</p>
                            </div>
                            <div style="background:white; border-radius:8px; padding:10px; border:1px solid #E2E8F0;">
                                <p style="font-size:11px; color:#94A3B8; margin:0;">📱 Yape/Plin</p>
                                <p style="font-size:15px; font-weight:700; color:#1E293B; margin:4px 0 0;">S/ {{ (Number(c.total_yape || 0) + Number(c.total_plin || 0)).toFixed(2) }}</p>
                            </div>
                            <div style="background:white; border-radius:8px; padding:10px; border:1px solid #E2E8F0;">
                                <p style="font-size:11px; color:#94A3B8; margin:0;">💳 Tarjeta</p>
                                <p style="font-size:15px; font-weight:700; color:#1E293B; margin:4px 0 0;">S/ {{ Number(c.total_tarjeta || 0).toFixed(2) }}</p>
                            </div>
                            <div style="background:#F0FDFA; border-radius:8px; padding:10px; border:1px solid #CCFBF1;">
                                <p style="font-size:11px; color:#0F766E; margin:0;">Total ventas</p>
                                <p style="font-size:15px; font-weight:700; color:#0F766E; margin:4px 0 0;">S/ {{ Number(c.total_ventas).toFixed(2) }}</p>
                            </div>
                        </div>
                        <p style="font-size:11px; color:#94A3B8; margin:0;">Apertura: {{ c.apertura_at }} | Cierre: {{ c.cierre_at || 'Abierta' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ CAJA ABIERTA ══ -->
        <div v-else>

            <!-- Estado caja -->
            <div style="background:linear-gradient(135deg,#14B8A6,#0F766E); border-radius:20px; padding:24px; color:white; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <p style="font-size:13px; font-weight:600; opacity:0.8; margin:0 0 4px; text-transform:uppercase; letter-spacing:1px;">🔓 Caja abierta</p>
                    <p style="font-size:14px; opacity:0.8; margin:0;">Desde {{ formatHora(caja_abierta.apertura_at) }} — Monto inicial: S/ {{ Number(caja_abierta.monto_inicial).toFixed(2) }}</p>
                </div>
                <button @click="modalCierre = true"
                    style="padding:12px 24px; background:white; color:#0F766E; border-radius:12px; font-size:14px; font-weight:700; border:none; cursor:pointer;">
                    🔒 Cerrar Caja
                </button>
            </div>

            <!-- KPIs -->
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
                <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06); grid-column:span 2;">
                    <p style="font-size:13px; font-weight:600; color:#94A3B8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">💰 Total del día</p>
                    <p style="font-size:48px; font-weight:900; color:#1E293B; margin:0; line-height:1;">S/ {{ Number(resumen.total_dia).toFixed(2) }}</p>
                    <p style="font-size:13px; color:#94A3B8; margin:10px 0 0;">{{ resumen.total_ventas }} transacciones</p>
                </div>
                <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                    <p style="font-size:13px; font-weight:600; color:#94A3B8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">💵 Efectivo</p>
                    <p style="font-size:28px; font-weight:800; color:#1E293B; margin:0;">S/ {{ Number(resumen.total_efectivo).toFixed(2) }}</p>
                </div>
                <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                    <p style="font-size:13px; font-weight:600; color:#94A3B8; margin:0 0 12px; text-transform:uppercase; letter-spacing:1px;">📱 Digital</p>
                    <p style="font-size:28px; font-weight:800; color:#1E293B; margin:0;">S/ {{ (Number(resumen.total_yape) + Number(resumen.total_plin) + Number(resumen.total_tarjeta)).toFixed(2) }}</p>
                </div>
            </div>

            <!-- Métodos de pago -->
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:24px;">
                <div style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0; display:flex; align-items:center; gap:16px;">
                    <div style="width:48px; height:48px; background:#EFF6FF; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px;">📱</div>
                    <div>
                        <p style="font-size:13px; color:#94A3B8; margin:0; font-weight:600;">Yape</p>
                        <p style="font-size:20px; font-weight:800; color:#1E293B; margin:4px 0 0;">S/ {{ Number(resumen.total_yape).toFixed(2) }}</p>
                    </div>
                </div>
                <div style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0; display:flex; align-items:center; gap:16px;">
                    <div style="width:48px; height:48px; background:#F5F3FF; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px;">📲</div>
                    <div>
                        <p style="font-size:13px; color:#94A3B8; margin:0; font-weight:600;">Plin</p>
                        <p style="font-size:20px; font-weight:800; color:#1E293B; margin:4px 0 0;">S/ {{ Number(resumen.total_plin).toFixed(2) }}</p>
                    </div>
                </div>
                <div style="background:white; border-radius:16px; padding:20px; border:1px solid #E2E8F0; display:flex; align-items:center; gap:16px;">
                    <div style="width:48px; height:48px; background:#FFF7ED; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px;">💳</div>
                    <div>
                        <p style="font-size:13px; color:#94A3B8; margin:0; font-weight:600;">Tarjeta</p>
                        <p style="font-size:20px; font-weight:800; color:#1E293B; margin:4px 0 0;">S/ {{ Number(resumen.total_tarjeta).toFixed(2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Ventas por hora + Últimas ventas -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                    <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0 0 20px;">⏰ Ventas por hora</p>
                    <div v-if="!ventas_por_hora.length" style="text-align:center; padding:30px 0; color:#CBD5E1;">
                        <p style="font-size:32px; margin:0 0 8px;">📊</p>
                        <p style="font-size:15px;">Sin ventas hoy</p>
                    </div>
                    <div v-for="h in ventas_por_hora" :key="h.hora" style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                        <span style="font-size:13px; font-weight:600; color:#94A3B8; min-width:50px;">{{ h.hora }}:00</span>
                        <div style="flex:1; background:#F1F5F9; border-radius:8px; height:10px; overflow:hidden;">
                            <div :style="{
                                width: maxHora > 0 ? (h.total / maxHora * 100) + '%' : '0%',
                                height: '100%',
                                background: 'linear-gradient(90deg,#14B8A6,#0F766E)',
                                borderRadius: '8px',
                            }"></div>
                        </div>
                        <span style="font-size:13px; font-weight:700; color:#1E293B; min-width:70px; text-align:right;">S/ {{ Number(h.total).toFixed(0) }}</span>
                    </div>
                </div>

                <div style="background:white; border-radius:20px; padding:24px; border:1px solid #E2E8F0; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
                        <p style="font-size:18px; font-weight:800; color:#1E293B; margin:0;">🧾 Últimas ventas</p>
                        <a href="/farmacia/ventas" style="font-size:13px; color:#14B8A6; font-weight:600; text-decoration:none;">Ver todas →</a>
                    </div>
                    <div v-if="!ultimas_ventas.length" style="text-align:center; padding:30px 0; color:#CBD5E1;">
                        <p style="font-size:32px; margin:0 0 8px;">📭</p>
                        <p style="font-size:15px;">Sin ventas hoy</p>
                    </div>
                    <div v-for="v in ultimas_ventas" :key="v.id"
                        style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid #F1F5F9;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="font-size:20px;">{{ iconMetodo(v.metodo_pago) }}</span>
                            <div>
                                <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ v.numero_completo }}</p>
                                <p style="font-size:12px; color:#94A3B8; margin:2px 0 0; text-transform:capitalize;">{{ v.metodo_pago || 'efectivo' }}</p>
                            </div>
                        </div>
                        <span style="font-size:15px; font-weight:800; color:#14B8A6;">S/ {{ Number(v.total_gravado).toFixed(2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Cierre de Caja -->
        <Teleport to="body">
            <div v-if="modalCierre" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:center; justify-content:center;">
                <div style="background:white; border-radius:20px; padding:32px; width:100%; max-width:500px; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
                    <p style="font-size:20px; font-weight:800; color:#1E293B; margin:0 0 8px;">🔒 Cerrar Caja</p>
                    <p style="font-size:14px; color:#94A3B8; margin:0 0 24px;">Ingresa el monto contado en caja</p>

                    <!-- Resumen -->
                    <div style="background:#F8FAFC; border-radius:12px; padding:16px; margin-bottom:20px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="font-size:13px; color:#64748B;">Monto inicial</span>
                            <span style="font-size:14px; font-weight:600; color:#1E293B;">S/ {{ Number(caja_abierta.monto_inicial).toFixed(2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                            <span style="font-size:13px; color:#64748B;">Ventas efectivo</span>
                            <span style="font-size:14px; font-weight:600; color:#1E293B;">S/ {{ Number(resumen.total_efectivo).toFixed(2) }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding-top:8px; border-top:1px solid #E2E8F0;">
                            <span style="font-size:13px; font-weight:700; color:#1E293B;">Efectivo esperado</span>
                            <span style="font-size:15px; font-weight:800; color:#14B8A6;">S/ {{ (Number(caja_abierta.monto_inicial) + Number(resumen.total_efectivo)).toFixed(2) }}</span>
                        </div>
                    </div>

                    <div style="margin-bottom:16px;">
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Monto contado en caja</label>
                        <input v-model="formCierre.monto_final" type="number" step="0.01" placeholder="0.00"
                            style="width:100%; padding:12px 16px; border:2px solid #E2E8F0; border-radius:10px; font-size:18px; font-weight:700; outline:none; box-sizing:border-box; margin-top:6px; text-align:center;"
                            @focus="$event.target.style.borderColor='#14B8A6'"
                            @blur="$event.target.style.borderColor='#E2E8F0'" />
                    </div>

                    <!-- Diferencia -->
                    <div v-if="formCierre.monto_final" :style="{
                        padding: '12px 16px', borderRadius: '10px', marginBottom: '16px',
                        background: diferenciaCierre >= 0 ? '#F0FDF4' : '#FEF2F2',
                        border: '1px solid ' + (diferenciaCierre >= 0 ? '#DCFCE7' : '#FECACA'),
                    }">
                        <div style="display:flex; justify-content:space-between;">
                            <span style="font-size:14px; font-weight:600;">Diferencia</span>
                            <span :style="{ fontSize: '16px', fontWeight: '800', color: diferenciaCierre >= 0 ? '#166534' : '#991B1B' }">
                                {{ diferenciaCierre >= 0 ? '+' : '' }}S/ {{ Number(diferenciaCierre).toFixed(2) }}
                            </span>
                        </div>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="font-size:13px; font-weight:600; color:#64748B;">Observaciones</label>
                        <textarea v-model="formCierre.observaciones" rows="2"
                            style="width:100%; padding:10px 14px; border:2px solid #E2E8F0; border-radius:10px; font-size:14px; outline:none; box-sizing:border-box; margin-top:6px; resize:none;"></textarea>
                    </div>

                    <div style="display:flex; gap:12px;">
                        <button @click="modalCierre = false"
                            style="flex:1; padding:12px; background:white; color:#64748B; border-radius:10px; font-size:14px; font-weight:600; border:2px solid #E2E8F0; cursor:pointer;">
                            Cancelar
                        </button>
                        <button @click="cerrarCaja"
                            style="flex:1; padding:12px; background:linear-gradient(135deg,#EF4444,#DC2626); color:white; border-radius:10px; font-size:14px; font-weight:600; border:none; cursor:pointer;">
                            🔒 Confirmar Cierre
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

    </AppLayout>

    <!-- ══ MODAL CORRECCIÓN DE CAJA ══ -->
    <div v-if="modalCorreccion" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; display:flex; align-items:center; justify-content:center; padding:16px;">
        <div style="background:white; border-radius:20px; padding:32px; width:100%; max-width:480px; box-shadow:0 20px 60px rgba(0,0,0,0.3);">

            <h3 style="font-size:18px; font-weight:700; color:#1E293B; margin:0 0 8px;">✏️ Corregir Caja</h3>
            <p style="font-size:13px; color:#64748B; margin:0 0 20px;">Esta acción quedará registrada en auditoría.</p>

            <div style="background:#FEF3C7; border-radius:10px; padding:12px; margin-bottom:20px; font-size:13px; color:#92400E;">
                ⚠️ Solo usa esta función si cometiste un error al digitar. El valor original se guardará como respaldo.
            </div>

            <!-- Monto inicial -->
            <div style="margin-bottom:16px;">
                <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">
                    Monto inicial correcto
                    <span style="font-weight:400; color:#94A3B8;"> (actual: S/ {{ Number(cajaACorregir?.monto_inicial).toFixed(2) }})</span>
                </label>
                <input v-model="formCorreccion.monto_inicial_nuevo" type="number" step="0.01" min="0"
                    style="width:100%; padding:10px 14px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; box-sizing:border-box;"
                    placeholder="0.00" />
            </div>

            <!-- Monto final (solo si la caja está cerrada) -->
            <div v-if="cajaACorregir?.estado === 'cerrada'" style="margin-bottom:16px;">
                <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">
                    Monto final correcto
                    <span style="font-weight:400; color:#94A3B8;"> (actual: S/ {{ Number(cajaACorregir?.monto_final).toFixed(2) }})</span>
                </label>
                <input v-model="formCorreccion.monto_final_nuevo" type="number" step="0.01" min="0"
                    style="width:100%; padding:10px 14px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; box-sizing:border-box;"
                    placeholder="0.00 (dejar vacío si no cambió)" />
            </div>

            <!-- Motivo -->
            <div style="margin-bottom:24px;">
                <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">
                    Motivo de la corrección <span style="color:#EF4444;">*</span>
                    <span style="font-weight:400; color:#94A3B8;"> (mínimo 10 caracteres)</span>
                </label>
                <textarea v-model="formCorreccion.motivo" rows="3"
                    style="width:100%; padding:10px 14px; border:1px solid #E2E8F0; border-radius:10px; font-size:14px; resize:vertical; box-sizing:border-box;"
                    placeholder="Ej: Me equivoqué al digitar el monto inicial, el real era S/ 100.00"></textarea>
                <p style="font-size:11px; color:#94A3B8; margin:4px 0 0;">{{ formCorreccion.motivo.length }}/1000 caracteres</p>
            </div>

            <!-- Botones -->
            <div style="display:flex; gap:12px;">
                <button @click="modalCorreccion = false"
                    style="flex:1; padding:12px; border:1px solid #E2E8F0; background:white; border-radius:12px; font-size:14px; font-weight:600; color:#64748B; cursor:pointer;">
                    Cancelar
                </button>
                <button @click="guardarCorreccion"
                    :disabled="formCorreccion.motivo.length < 10 || !formCorreccion.monto_inicial_nuevo"
                    style="flex:1; padding:12px; border:none; background:linear-gradient(135deg,#F59E0B,#D97706); color:white; border-radius:12px; font-size:14px; font-weight:700; cursor:pointer; opacity: (formCorreccion.motivo.length < 10 || !formCorreccion.monto_inicial_nuevo) ? '0.5' : '1'">
                    ✅ Guardar corrección
                </button>
            </div>
        </div>
    </div>

</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    user_rol:        { type: String, default: '' },
    caja_abierta:    { type: Object, default: null },
    resumen:         { type: Object, default: () => ({}) },
    ventas_por_hora: { type: Array,  default: () => [] },
    ultimas_ventas:  { type: Array,  default: () => [] },
    historial_cajas: { type: Array,  default: () => [] },
    fecha:           { type: String, default: '' },
})

const montoInicial = ref('')
const modalCierre  = ref(false)
const cajaDetalle  = ref(null)
const formCierre   = ref({ monto_final: '', observaciones: '' })

const maxHora = computed(() => {
    if (!props.ventas_por_hora.length) return 1
    return Math.max(...props.ventas_por_hora.map(h => h.total)) || 1
})

const diferenciaCierre = computed(() => {
    if (!formCierre.value.monto_final || !props.caja_abierta) return 0
    const esperado = Number(props.caja_abierta.monto_inicial) + Number(props.resumen.total_efectivo)
    return Number(formCierre.value.monto_final) - esperado
})

const iconMetodo = (m) => ({ efectivo: '💵', yape: '📱', plin: '📲', tarjeta: '💳' })[m] || '💵'

const formatFecha = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const formatHora = (fecha) => {
    if (!fecha) return '-'
    return new Date(fecha).toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })
}

const abrirCaja = () => {
    router.post('/farmacia/caja/abrir', {
        monto_inicial: montoInicial.value,
    }, {
        onSuccess: () => { window.location.replace('/farmacia/caja') },
        onError: (e) => alert('Error: ' + JSON.stringify(e))
    })
}

const cerrarCaja = () => {
    router.post(`/farmacia/caja/${props.caja_abierta.id}/cerrar`, formCierre.value, {
        onSuccess: () => { window.location.replace('/farmacia/caja') },
        onError: (e) => alert('Error: ' + JSON.stringify(e))
    })
}

const modalCorreccion  = ref(false)
const cajaACorregir    = ref(null)
const formCorreccion   = ref({ monto_inicial_nuevo: '', monto_final_nuevo: '', motivo: '' })

const abrirModalCorreccion = (caja) => {
    cajaACorregir.value = caja
    formCorreccion.value = {
        monto_inicial_nuevo: caja.monto_inicial,
        monto_final_nuevo: caja.monto_final ?? '',
        motivo: ''
    }
    modalCorreccion.value = true
}

const guardarCorreccion = () => {
    if (formCorreccion.value.motivo.length < 10) return
    if (!formCorreccion.value.monto_inicial_nuevo) return
    router.post(`/farmacia/caja/${cajaACorregir.value.id}/corregir-montos`, {
        monto_inicial_nuevo: formCorreccion.value.monto_inicial_nuevo,
        monto_final_nuevo: formCorreccion.value.monto_final_nuevo || null,
        motivo: formCorreccion.value.motivo,
    }, {
        onSuccess: () => {
            modalCorreccion.value = false
            window.location.replace('/farmacia/caja')
        },
        onError: (e) => alert('Error: ' + JSON.stringify(e))
    })
}

</script>