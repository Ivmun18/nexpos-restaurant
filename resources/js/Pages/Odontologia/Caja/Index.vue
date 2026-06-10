<template>
  <AppLayout>
    <div style="padding:24px; max-width:1300px; margin:0 auto;">

      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 style="margin:0; font-size:22px; font-weight:700; color:#1E293B;">Caja Dental</h1>
        <span style="font-size:13px; color:#94A3B8;">{{ fechaHoy }}</span>
      </div>

      <div v-if="mensaje" style="background:#F0FDF4; border:1px solid #BBF7D0; border-radius:8px; padding:12px 16px; margin-bottom:16px; color:#15803D; font-size:14px;">
        ✓ {{ mensaje }}
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

        <!-- PANEL IZQUIERDO -->
        <div style="display:flex; flex-direction:column; gap:12px;">

          <!-- Buscar paciente -->
          <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; padding:16px;">
            <p style="margin:0 0 8px; font-size:11px; font-weight:600; color:#64748B; text-transform:uppercase;">Paciente (opcional)</p>
            <input v-model="buscarPaciente" @input="searchPaciente" type="text" placeholder="Buscar por nombre o DNI..." style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
            <div v-if="resultadosPaciente.length" style="border:1px solid #E2E8F0; border-radius:8px; margin-top:4px; background:white; position:relative; z-index:10;">
              <div v-for="p in resultadosPaciente" :key="p.id" @mousedown.prevent="seleccionarPaciente(p)" style="padding:10px 12px; cursor:pointer; font-size:13px; border-bottom:1px solid #F1F5F9; display:flex; justify-content:space-between;">
                <span>{{ p.apellidos }}, {{ p.nombres }}</span>
                <span style="color:#94A3B8;">{{ p.dni }}</span>
              </div>
            </div>
            <div v-if="pacienteSeleccionado" style="display:flex; align-items:center; gap:10px; margin-top:10px; padding:10px 12px; background:#F8FAFC; border-radius:8px;">
              <div style="width:34px; height:34px; border-radius:50%; background:#EDE9FE; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:#7C3AED; flex-shrink:0;">
                {{ pacienteSeleccionado.nombres[0] }}{{ pacienteSeleccionado.apellidos[0] }}
              </div>
              <div style="flex:1;">
                <p style="margin:0; font-size:13px; font-weight:700;">{{ pacienteSeleccionado.apellidos }}, {{ pacienteSeleccionado.nombres }}</p>
                <p style="margin:0; font-size:11px; color:#94A3B8;">DNI {{ pacienteSeleccionado.dni }} · {{ pacienteSeleccionado.telefono }}</p>
              </div>
              <button @click="limpiarPaciente" style="background:none; border:none; color:#94A3B8; cursor:pointer; font-size:18px;">×</button>
            </div>
          </div>

          <!-- Presupuestos del paciente -->
          <div v-if="pacienteSeleccionado && presupuestosPaciente.length" style="background:white; border:1px solid #E2E8F0; border-radius:12px; padding:16px;">
            <p style="margin:0 0 10px; font-size:11px; font-weight:600; color:#64748B; text-transform:uppercase;">Presupuestos pendientes</p>
            <div v-for="pres in presupuestosPaciente" :key="pres.id" style="border:1px solid #E2E8F0; border-radius:8px; margin-bottom:8px; overflow:hidden;">
              <div style="padding:10px 12px; background:#FAFAFA; display:flex; justify-content:space-between; align-items:center;">
                <div style="flex:1; cursor:pointer;" @click="pres._expand = !pres._expand">
                  <p style="margin:0; font-size:13px; font-weight:700;">Presupuesto #{{ pres.id }}</p>
                  <p style="margin:0; font-size:11px; color:#94A3B8;">{{ pres.items?.map(i=>i.descripcion).join(', ') }}</p>
                </div>
                <div style="text-align:right; display:flex; align-items:center; gap:10px;">
                  <div>
                    <p style="margin:0; font-size:14px; font-weight:700; color:#8B5CF6;">S/ {{ Number(pres.total).toFixed(2) }}</p>
                    <p style="margin:0; font-size:11px; color:#94A3B8;">{{ cuotasPendientes(pres) }} cuota(s) pendiente</p>
                    <p v-if="pres.total_adelantado>0" style="margin:0; font-size:11px; color:#16A34A;">Adelantado: S/ {{ Number(pres.total_adelantado).toFixed(2) }}</p>
                    <p v-if="pres.saldo_pendiente>=0" style="margin:0; font-size:12px; font-weight:700; color:#DC2626;">Saldo: S/ {{ Number(pres.saldo_pendiente).toFixed(2) }}</p>
                  </div>
                  <button @click.stop="abrirModalAdelanto(pres)"
                    style="background:#F59E0B; color:white; border:none; border-radius:8px; padding:6px 12px; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap;">
                    + Adelanto
                  </button>
                  <button @click.stop="cobrarTodo(pres)"
                    style="background:#8B5CF6; color:white; border:none; border-radius:8px; padding:6px 12px; font-size:12px; font-weight:600; cursor:pointer; white-space:nowrap;">
                    Cobrar todo
                  </button>
                </div>
              </div>
              <div v-if="pres._expand" style="padding:8px 12px;">
                <div v-for="c in pres.cuotas?.filter(c=>c.estado!=='pagado')" :key="c.id"
                  style="display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid #F8FAFC; cursor:pointer;"
                  @click="toggleCuota(c, pres)">
                  <input type="checkbox" :checked="cuotaSeleccionada(c.id)" style="accent-color:#8B5CF6; width:16px; height:16px;" @click.stop="toggleCuota(c, pres)" />
                  <div style="flex:1;">
                    <p style="margin:0; font-size:13px;">Cuota {{ c.numero_cuota }}</p>
                    <p style="margin:0; font-size:11px; color:#94A3B8;">Vence: {{ c.fecha_vencimiento }}</p>
                  </div>
                  <span style="font-size:14px; font-weight:700; color:#1E293B;">S/ {{ Number(c.monto).toFixed(2) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Cobro rápido -->
          <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; padding:16px;">
            <p style="margin:0 0 10px; font-size:11px; font-weight:600; color:#64748B; text-transform:uppercase;">Cobro rápido (sin presupuesto)</p>
            <div style="display:flex; gap:8px;">
              <input v-model="itemRapido.descripcion" type="text" placeholder="Descripción del servicio..." style="flex:1; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px;" />
              <input v-model="itemRapido.monto" type="number" step="0.01" min="0" placeholder="S/ 0.00" style="width:90px; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px;" />
              <button @click="agregarItemRapido" style="background:#8B5CF6; color:white; border:none; border-radius:8px; padding:9px 14px; font-size:18px; cursor:pointer;">+</button>
            </div>
          </div>
        </div>

        <!-- PANEL DERECHO -->
        <div style="background:white; border:1px solid #E2E8F0; border-radius:12px; padding:20px; display:flex; flex-direction:column; gap:14px;">
          <p style="margin:0; font-size:11px; font-weight:600; color:#64748B; text-transform:uppercase;">Resumen de cobro</p>

          <!-- Items -->
          <div style="min-height:80px;">
            <div v-if="itemsCobro.length === 0" style="text-align:center; color:#CBD5E1; padding:32px 0; font-size:13px;">
              Selecciona cuotas o agrega un cobro rápido
            </div>
            <div v-for="(item, idx) in itemsCobro" :key="idx" style="display:flex; justify-content:space-between; align-items:center; padding:10px 12px; background:#F8FAFC; border-radius:8px; margin-bottom:6px;">
              <div>
                <p style="margin:0; font-size:13px; font-weight:600;">{{ item.descripcion }}</p>
                <p style="margin:0; font-size:11px; color:#94A3B8;">{{ item.tipo }}</p>
              </div>
              <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-size:14px; font-weight:700;">S/ {{ Number(item.monto).toFixed(2) }}</span>
                <button @click="quitarItem(idx)" style="background:none; border:none; color:#94A3B8; cursor:pointer; font-size:18px; line-height:1;">×</button>
              </div>
            </div>
          </div>

          <div style="height:1px; background:#F1F5F9;"></div>

          <!-- Total -->
          <div style="display:flex; justify-content:space-between; align-items:center;">
            <span style="font-size:14px; color:#64748B;">Total a cobrar</span>
            <span style="font-size:26px; font-weight:800; color:#8B5CF6;">S/ {{ totalCobro.toFixed(2) }}</span>
          </div>

          <div style="height:1px; background:#F1F5F9;"></div>

          <!-- Método de pago -->
          <div>
            <p style="margin:0 0 8px; font-size:11px; font-weight:600; color:#64748B; text-transform:uppercase;">Método de pago</p>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-bottom:6px;">
              <button v-for="m in metodos" :key="m.key" @click="metodoPago=m.key"
                :style="metodoPago===m.key ? 'background:#EDE9FE; border:1.5px solid #8B5CF6; color:#7C3AED; font-weight:600;' : 'background:white; border:1px solid #E2E8F0; color:#64748B;'"
                style="padding:9px; border-radius:8px; font-size:13px; cursor:pointer;">
                {{ m.label }}
              </button>
            </div>
          </div>

          <!-- Vuelto (solo efectivo) -->
          <div v-if="metodoPago==='efectivo'" style="background:#F8FAFC; border-radius:8px; padding:12px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
              <span style="font-size:13px; color:#64748B;">Monto recibido</span>
              <input v-model="montoRecibido" type="number" step="0.50" :min="totalCobro" style="width:110px; padding:6px 10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; text-align:right;" />
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <span style="font-size:13px; color:#64748B;">Vuelto</span>
              <span style="font-size:18px; font-weight:700; color:#16A34A;">S/ {{ vuelto.toFixed(2) }}</span>
            </div>
          </div>

          <!-- Botón cobrar -->
          <div style="margin-top:auto;">
            <button @click="cobrar" :disabled="itemsCobro.length===0"
              :style="itemsCobro.length===0 ? 'opacity:0.4; cursor:not-allowed;' : 'cursor:pointer;'"
              style="width:100%; padding:14px; background:#8B5CF6; color:white; border:none; border-radius:10px; font-size:16px; font-weight:700;">
              Cobrar S/ {{ totalCobro.toFixed(2) }}
            </button>
            <p style="text-align:center; margin:8px 0 0; font-size:11px; color:#94A3B8;">🖨️ Imprime recibo automáticamente al cobrar</p>
          </div>
        </div>
      </div>
    </div>
  <!-- Modal adelanto -->
  <div v-if="modalAdelanto" style="position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000;">
    <div style="background:white; border-radius:16px; padding:28px; width:420px; max-width:90vw;">
      <h2 style="margin:0 0 20px; font-size:18px; font-weight:700;">Registrar adelanto</h2>
      <div v-if="presupuestoAdelanto" style="background:#F8FAFC; border-radius:8px; padding:12px; margin-bottom:16px;">
        <p style="margin:0; font-size:13px; font-weight:700;">Presupuesto #{{ presupuestoAdelanto.id }}</p>
        <p style="margin:0; font-size:13px; color:#64748B;">Total: S/ {{ Number(presupuestoAdelanto.total).toFixed(2) }}</p>
        <p style="margin:0; font-size:13px; color:#DC2626; font-weight:700;">Saldo pendiente: S/ {{ Number(presupuestoAdelanto.saldo_pendiente).toFixed(2) }}</p>
      </div>
      <div style="display:flex; flex-direction:column; gap:12px;">
        <div>
          <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">MONTO ADELANTO *</label>
          <input v-model="adelantoForm.monto" type="number" step="0.01" min="0.01" style="width:100%; padding:9px 12px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; box-sizing:border-box;" />
        </div>
        <div>
          <label style="font-size:12px; font-weight:600; color:#64748B; display:block; margin-bottom:4px;">MÉTODO DE PAGO</label>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px;">
            <button v-for="m in metodos" :key="m.key" @click="adelantoForm.metodo=m.key"
              :style="adelantoForm.metodo===m.key ? 'background:#FEF3C7; border:1.5px solid #F59E0B; color:#92400E; font-weight:600;' : 'background:white; border:1px solid #E2E8F0; color:#64748B;'"
              style="padding:8px; border-radius:8px; font-size:12px; cursor:pointer;">
              {{ m.label }}
            </button>
          </div>
        </div>
      </div>
      <div style="display:flex; gap:12px; margin-top:20px;">
        <button @click="modalAdelanto=false" style="flex:1; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; cursor:pointer; background:white;">Cancelar</button>
        <button @click="guardarAdelanto" style="flex:1; padding:10px; background:#F59E0B; color:white; border:none; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer;">Registrar adelanto</button>
      </div>
    </div>
  </div>

  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({ pacientes: Array })

const fechaHoy = new Date().toLocaleDateString('es-PE', { weekday:'long', year:'numeric', month:'long', day:'numeric' })
const mensaje = ref(usePage().props.flash?.success || '')

const buscarPaciente = ref('')
const resultadosPaciente = ref([])
const pacienteSeleccionado = ref(null)
const presupuestosPaciente = ref([])
const itemsCobro = ref([])
const cuotasEnCobro = ref([])
const metodoPago = ref('efectivo')
const montoRecibido = ref(0)
const itemRapido = ref({ descripcion: '', monto: '' })

const modalAdelanto = ref(false)
const presupuestoAdelanto = ref(null)
const adelantoForm = ref({ monto: '', metodo: 'efectivo' })

const abrirModalAdelanto = (pres) => {
  presupuestoAdelanto.value = pres
  adelantoForm.value = { monto: '', metodo: 'efectivo' }
  modalAdelanto.value = true
}

const guardarAdelanto = async () => {
  if (!adelantoForm.value.monto || !pacienteSeleccionado.value) return
  const r = await fetch('/odontologia/pagos/adelanto', {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
    body: JSON.stringify({
      paciente_id: pacienteSeleccionado.value.id,
      presupuesto_id: presupuestoAdelanto.value.id,
      monto: adelantoForm.value.monto,
      metodo_pago: adelantoForm.value.metodo,
    })
  })
  const data = await r.json()
  if (data.success) {
    presupuestoAdelanto.value.total_adelantado = (Number(presupuestoAdelanto.value.total_adelantado)||0) + Number(adelantoForm.value.monto)
    presupuestoAdelanto.value.saldo_pendiente = data.saldo
    modalAdelanto.value = false
    mensaje.value = `Adelanto de S/ ${Number(adelantoForm.value.monto).toFixed(2)} registrado. Saldo pendiente: S/ ${data.saldo.toFixed(2)}`
    setTimeout(() => mensaje.value = '', 5000)
    window.open(`/odontologia/pagos/${data.pago_id}/ticket-adelanto`, '_blank')
  }
}

const metodos = [
  { key:'efectivo', label:'💵 Efectivo' },
  { key:'yape',     label:'📱 Yape' },
  { key:'plin',     label:'📱 Plin' },
  { key:'tarjeta',  label:'💳 Tarjeta' },
  { key:'transferencia', label:'🏦 Transferencia' },
]

const totalCobro = computed(() => itemsCobro.value.reduce((s, i) => s + Number(i.monto), 0))
const vuelto = computed(() => Math.max(0, Number(montoRecibido.value) - totalCobro.value))

const searchPaciente = async () => {
  if (buscarPaciente.value.length < 2) { resultadosPaciente.value = []; return }
  const r = await fetch('/odontologia/pacientes/buscar?q=' + buscarPaciente.value, { credentials: 'include' })
  resultadosPaciente.value = await r.json()
}

const seleccionarPaciente = async (p) => {
  pacienteSeleccionado.value = p
  buscarPaciente.value = ''
  resultadosPaciente.value = []
  console.log('Fetching presupuestos para paciente id:', p.id)
  const r = await fetch(`/odontologia/pagos/presupuestos-paciente/${p.id}`, { credentials: 'include' })
  const data = await r.json()
  presupuestosPaciente.value = data.map(pres => ({ ...pres, _expand: true }))
}

const limpiarPaciente = () => {
  pacienteSeleccionado.value = null
  presupuestosPaciente.value = []
  itemsCobro.value = itemsCobro.value.filter(i => i.tipo !== 'cuota')
  cuotasEnCobro.value = []
}

const cuotasPendientes = (pres) => pres.cuotas?.filter(c => c.estado !== 'pagado').length || 0
const cuotaSeleccionada = (cuotaId) => cuotasEnCobro.value.includes(cuotaId)

const toggleCuota = (cuota, pres) => {
  if (cuotaSeleccionada(cuota.id)) {
    cuotasEnCobro.value = cuotasEnCobro.value.filter(id => id !== cuota.id)
    itemsCobro.value = itemsCobro.value.filter(i => i.cuotaId !== cuota.id)
  } else {
    cuotasEnCobro.value.push(cuota.id)
    itemsCobro.value.push({
      descripcion: `Cuota ${cuota.numero_cuota} — Presupuesto #${pres.id}`,
      tipo: 'cuota',
      monto: cuota.monto,
      cuotaId: cuota.id,
    })
  }
}

const agregarItemRapido = () => {
  if (!itemRapido.value.descripcion || !itemRapido.value.monto) return
  itemsCobro.value.push({
    descripcion: itemRapido.value.descripcion,
    tipo: 'cobro rápido',
    monto: itemRapido.value.monto,
    cuotaId: null,
  })
  itemRapido.value = { descripcion: '', monto: '' }
}

const cobrarTodo = (pres) => {
  // Si tiene cuotas pendientes, seleccionarlas todas
  if (pres.cuotas && pres.cuotas.length > 0) {
    pres.cuotas.forEach(c => {
      if (!cuotaSeleccionada(c.id)) toggleCuota(c, pres)
    })
  } else {
    // Sin cuotas — cobro directo del total del presupuesto
    const yaExiste = itemsCobro.value.find(i => i.presupuestoId === pres.id && i.tipo === 'contado')
    if (!yaExiste) {
      itemsCobro.value.push({
        descripcion: `Presupuesto #${pres.id} — ${pres.items?.map(i=>i.descripcion).join(', ')}`,
        tipo: 'contado',
        monto: pres.total,
        cuotaId: null,
        presupuestoId: pres.id,
      })
    }
  }
  pres._expand = true
}

const quitarItem = (idx) => {
  const item = itemsCobro.value[idx]
  if (item.cuotaId) cuotasEnCobro.value = cuotasEnCobro.value.filter(id => id !== item.cuotaId)
  itemsCobro.value.splice(idx, 1)
}

const cobrar = () => {
  if (itemsCobro.value.length === 0) return
  const cuotas = itemsCobro.value.filter(i => i.cuotaId).map(i => i.cuotaId)
  const rapidos = itemsCobro.value.filter(i => !i.cuotaId)

  const promises = []

  cuotas.forEach(cuotaId => {
    promises.push(new Promise(resolve => {
      router.post(`/odontologia/pagos/cuota/${cuotaId}`, { metodo_pago: metodoPago.value }, {
        preserveState: true, onSuccess: resolve, onError: resolve
      })
    }))
  })

  rapidos.forEach(item => {
    promises.push(new Promise(resolve => {
      router.post('/odontologia/pagos/cobro-rapido', {
        descripcion: item.descripcion,
        monto: item.monto,
        metodo_pago: metodoPago.value,
        paciente_id: pacienteSeleccionado.value?.id || null,
      }, { preserveState: true, onSuccess: resolve, onError: resolve })
    }))
  })

  Promise.all(promises).then(() => {
    itemsCobro.value = []
    cuotasEnCobro.value = []
    montoRecibido.value = 0
    if (pacienteSeleccionado.value) seleccionarPaciente(pacienteSeleccionado.value)
    mensaje.value = 'Cobro registrado correctamente.'
    setTimeout(() => mensaje.value = '', 4000)
    window.print()
  })
}
</script>
