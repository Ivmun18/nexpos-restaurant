<template>
    <AppLayout :title="cotizacion.numero" subtitle="Detalle de cotizacion">

        <div style="display:grid; grid-template-columns:1fr 300px; gap:1.5rem; align-items:start;">

            <div>
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem; margin-bottom:1rem;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem;">
                        <div>
                            <p style="font-size:20px; font-weight:700; color:#1E293B; margin:0; font-family:monospace;">{{ cotizacion.numero }}</p>
                            <p style="font-size:13px; color:#94A3B8; margin:4px 0 0;">Emitida el {{ cotizacion.fecha_emision }}</p>
                        </div>
                        <span :style="estadoStyle(cotizacion.estado)">{{ cotizacion.estado }}</span>
                    </div>
                    <div style="background:#F8FAFC; border-radius:8px; padding:12px 16px;">
                        <p style="font-size:11px; color:#94A3B8; margin:0 0 4px; text-transform:uppercase;">Cliente</p>
                        <p style="font-size:14px; font-weight:600; color:#1E293B; margin:0;">{{ cotizacion.cliente_razon_social }}</p>
                        <p v-if="cotizacion.cliente_num_doc" style="font-size:12px; color:#64748B; margin:3px 0 0;">Doc: {{ cotizacion.cliente_num_doc }}</p>
                        <p v-if="cotizacion.cliente_email" style="font-size:12px; color:#64748B; margin:3px 0 0;">Email: {{ cotizacion.cliente_email }}</p>
                        <p v-if="cotizacion.cliente_direccion" style="font-size:12px; color:#64748B; margin:3px 0 0;">Dir: {{ cotizacion.cliente_direccion }}</p>
                    </div>
                </div>

                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; overflow:hidden; margin-bottom:1rem;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr style="background:#F8FAFC;">
                                <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600;">#</th>
                                <th style="padding:12px 16px; text-align:left; font-size:11px; color:#94A3B8; font-weight:600;">DESCRIPCION</th>
                                <th style="padding:12px 16px; text-align:center; font-size:11px; color:#94A3B8; font-weight:600;">CANT.</th>
                                <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600;">P. UNIT.</th>
                                <th style="padding:12px 16px; text-align:right; font-size:11px; color:#94A3B8; font-weight:600;">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in cotizacion.detalle" :key="item.id" style="border-top:1px solid #F1F5F9;">
                                <td style="padding:12px 16px; font-size:13px; color:#94A3B8;">{{ item.linea }}</td>
                                <td style="padding:12px 16px; font-size:13px; color:#1E293B; font-weight:500;">{{ item.descripcion }}</td>
                                <td style="padding:12px 16px; font-size:13px; color:#1E293B; text-align:center;">{{ Number(item.cantidad).toFixed(2) }}</td>
                                <td style="padding:12px 16px; font-size:13px; color:#1E293B; text-align:right;">S/ {{ Number(item.precio_unitario).toFixed(2) }}</td>
                                <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#1E293B; text-align:right;">S/ {{ Number(item.total).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="cotizacion.terminos_condiciones" style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 8px;">Terminos y condiciones</p>
                    <p style="font-size:13px; color:#64748B; margin:0; white-space:pre-line;">{{ cotizacion.terminos_condiciones }}</p>
                </div>
            </div>

            <div style="position:sticky; top:80px;">
                <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem; margin-bottom:1rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Resumen</p>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">Op. gravadas</span>
                        <span style="font-size:13px;">S/ {{ Number(cotizacion.total_gravado).toFixed(2) }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span style="font-size:13px; color:#64748B;">IGV (18%)</span>
                        <span style="font-size:13px;">S/ {{ Number(cotizacion.total_igv).toFixed(2) }}</span>
                    </div>
                    <div style="border-top:2px solid #E2E8F0; margin:12px 0; padding-top:12px; display:flex; justify-content:space-between;">
                        <span style="font-size:15px; font-weight:700; color:#1E293B;">Total</span>
                        <span style="font-size:22px; font-weight:700; color:#2563EB;">S/ {{ Number(cotizacion.total).toFixed(2) }}</span>
                    </div>
                    <div v-if="cotizacion.fecha_vencimiento" style="background:#FFFBEB; border-radius:8px; padding:10px 14px; border:1px solid #FDE68A;">
                        <p style="font-size:12px; color:#92400E; margin:0;">Vence el {{ cotizacion.fecha_vencimiento }}</p>
                    </div>
                </div>

               <div style="background:white; border-radius:10px; border:1px solid #E2E8F0; padding:1.5rem;">
                    <p style="font-size:13px; font-weight:600; color:#1E293B; margin:0 0 1rem;">Acciones</p>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <a :href="'/cotizaciones/' + cotizacion.id + '/pdf'" target="_blank"
                            style="padding:10px; background:#2563EB; color:white; border:none; border-radius:8px; font-size:13px; cursor:pointer; text-align:center; text-decoration:none; font-weight:600;">
                            Imprimir cotizacion
                        </a>
                        <a :href="whatsappUrl" target="_blank"
                            style="padding:10px; background:#25D366; color:white; border:none; border-radius:8px; font-size:13px; cursor:pointer; text-align:center; text-decoration:none; font-weight:600;">
                            Enviar por WhatsApp
                        </a>
                        <button v-if="cotizacion.estado !== 'convertida'" type="button" @click="convertir"
                            style="padding:10px; background:#F0FDF4; color:#166534; border:1px solid #DCFCE7; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">
                            Convertir a venta
                        </button>
                        <a href="/cotizaciones"
                            style="padding:10px; background:#F1F5F9; color:#64748B; border:none; border-radius:8px; font-size:13px; cursor:pointer; text-align:center; text-decoration:none;">
                            Volver a cotizaciones
                        </a>
                        <div>
                            <label style="font-size:12px; color:#64748B; display:block; margin-bottom:4px;">Cambiar estado</label>
                            <select v-model="nuevoEstado" @change="cambiarEstado"
                                style="width:100%; padding:10px; border:1px solid #E2E8F0; border-radius:8px; font-size:13px; outline:none; background:white;">
                                <option value="borrador">Borrador</option>
                                <option value="enviada">Enviada</option>
                                <option value="aprobada">Aprobada</option>
                                <option value="rechazada">Rechazada</option>
                                <option value="vencida">Vencida</option>
                            </select>
                        </div>
                        <button v-if="cotizacion.estado === 'borrador'" type="button" @click="eliminar"
                            style="padding:10px; background:#FEF2F2; color:#991B1B; border:none; border-radius:8px; font-size:13px; cursor:pointer;">
                            Eliminar cotizacion
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed } from 'vue'

const props = defineProps({ cotizacion: Object })

const estadoStyle = (estado) => {
    const map = {
        borrador:   { background:'#F1F5F9', color:'#64748B' },
        enviada:    { background:'#EFF6FF', color:'#1D4ED8' },
        aprobada:   { background:'#F0FDF4', color:'#166534' },
        rechazada:  { background:'#FEF2F2', color:'#991B1B' },
        vencida:    { background:'#FFFBEB', color:'#92400E' },
        convertida: { background:'#F0FDF4', color:'#166534' },
    }
    return { ...(map[estado] || map.borrador), fontSize:'12px', padding:'4px 12px', borderRadius:'20px' }
}

const nuevoEstado = ref(props.cotizacion.estado)

const cambiarEstado = () => {
    router.post('/cotizaciones/' + props.cotizacion.id + '/estado', { estado: nuevoEstado.value })
}

const convertir = () => {
    if (confirm('Convertir esta cotizacion en una venta?')) {
        router.post('/cotizaciones/' + props.cotizacion.id + '/convertir')
    }
}

const eliminar = () => {
    if (confirm('Eliminar esta cotizacion?')) {
        router.delete('/cotizaciones/' + props.cotizacion.id)
    }
}

const whatsappUrl = computed(() => {
    const texto = 'Estimado(a) ' + props.cotizacion.cliente_razon_social + ',\n\nAdjunto nuestra cotizacion *' + props.cotizacion.numero + '*\nFecha: ' + props.cotizacion.fecha_emision + '\nTotal: S/ ' + Number(props.cotizacion.total).toFixed(2) + '\n\nGracias por su preferencia.'
    return 'https://wa.me/?text=' + encodeURIComponent(texto)
})

</script>
