<template>
    <AppLayout title="Índice Notarial">
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">📚 Índice Notarial</h1>
                    <p class="mt-2 text-sm text-gray-600">Registro cronológico oficial de instrumentos públicos</p>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Total año {{ filtros.anio }}</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.total_anio }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Registros cerrados</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.total_cerrados }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                            </div>
                            <div class="ml-5">
                                <p class="text-sm font-medium text-gray-500">Último N° correlativo</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.ultimo_numero }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros y acciones -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <!-- Año -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Año</label>
                                <select v-model="filtros.anio" @change="buscar" class="w-full rounded-lg border-gray-300">
                                    <option v-for="anio in anios" :key="anio" :value="anio">{{ anio }}</option>
                                </select>
                            </div>

                            <!-- Tipo de acto -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de acto</label>
                                <select v-model="filtros.tipo" @change="buscar" class="w-full rounded-lg border-gray-300">
                                    <option value="">Todos</option>
                                    <option value="escritura_publica">Escritura pública</option>
                                    <option value="poder">Poder</option>
                                    <option value="testamento">Testamento</option>
                                    <option value="carta_notarial">Carta notarial</option>
                                    <option value="legalizacion">Legalización</option>
                                    <option value="protesto">Protesto</option>
                                    <option value="acta_notarial">Acta notarial</option>
                                </select>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                                <select v-model="filtros.cerrado" @change="buscar" class="w-full rounded-lg border-gray-300">
                                    <option value="">Todos</option>
                                    <option value="0">Abiertos</option>
                                    <option value="1">Cerrados</option>
                                </select>
                            </div>

                            <!-- Búsqueda -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                                <div class="flex gap-2">
                                    <input 
                                        v-model="filtros.buscar" 
                                        type="text" 
                                        placeholder="N° índice, asunto, partes..."
                                        class="flex-1 rounded-lg border-gray-300"
                                        @keyup.enter="buscar"
                                    />
                                    <button @click="buscar" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        Buscar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="mt-6 flex gap-3">
                            <button @click="registrarMasivo" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                📝 Registrar actos finalizados
                            </button>
                            <button @click="mostrarCerrarMes = true" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                                🔒 Cerrar mes
                            </button>
                            <button @click="exportar" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                📥 Exportar Excel
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Índice</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asunto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Partes</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="registro in registros.data" :key="registro.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono font-semibold text-blue-600">{{ registro.numero_indice }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatearFecha(registro.fecha_otorgamiento) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ formatearTipo(registro.tipo_acto) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 max-w-md truncate">
                                        {{ registro.asunto }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                        {{ registro.partes }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        S/ {{ Number(registro.monto).toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="registro.cerrado" class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                            🔒 Cerrado
                                        </span>
                                        <span v-else class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                            🔓 Abierto
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <Link :href="`/notaria/actos/${registro.acto_id}`" class="text-blue-600 hover:text-blue-800">
                                            Ver expediente →
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="registros.data.length === 0">
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                        No hay registros en el índice notarial
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="registros.data.length > 0" class="px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                Mostrando {{ registros.from }} a {{ registros.to }} de {{ registros.total }} registros
                            </div>
                            <div class="flex gap-2">
                                <Link 
                                    v-for="link in registros.links" 
                                    :key="link.label"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-2 text-sm rounded',
                                        link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Cerrar Mes -->
                <div v-if="mostrarCerrarMes" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                        <h3 class="text-lg font-semibold mb-4">Cerrar mes del índice</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Los registros cerrados no podrán ser editados. Esta acción es irreversible.
                        </p>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Año</label>
                                <input v-model="cierreForm.anio" type="number" class="w-full rounded-lg border-gray-300" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mes</label>
                                <select v-model="cierreForm.mes" class="w-full rounded-lg border-gray-300">
                                    <option value="1">Enero</option>
                                    <option value="2">Febrero</option>
                                    <option value="3">Marzo</option>
                                    <option value="4">Abril</option>
                                    <option value="5">Mayo</option>
                                    <option value="6">Junio</option>
                                    <option value="7">Julio</option>
                                    <option value="8">Agosto</option>
                                    <option value="9">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-6 flex gap-3">
                            <button @click="cerrarMes" class="flex-1 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                                Cerrar mes
                            </button>
                            <button @click="mostrarCerrarMes = false" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    registros: Object,
    stats: Object,
    anios: Array,
    filtros: Object,
})

const mostrarCerrarMes = ref(false)
const cierreForm = reactive({
    anio: new Date().getFullYear(),
    mes: new Date().getMonth() + 1,
})

const filtros = reactive({ ...props.filtros })

function buscar() {
    router.get('/notaria/indice', filtros, { preserveState: true })
}

function registrarMasivo() {
    if (confirm('¿Registrar todos los actos finalizados que no están en el índice?')) {
        router.post('/notaria/indice/registrar-masivo')
    }
}

function cerrarMes() {
    if (confirm(`¿Cerrar el mes ${cierreForm.mes}/${cierreForm.anio}? Esta acción es irreversible.`)) {
        router.post('/notaria/indice/cerrar-mes', cierreForm)
        mostrarCerrarMes.value = false
    }
}

function exportar() {
    window.location.href = `/notaria/indice/exportar?anio=${filtros.anio}`
}

function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-PE', { 
        day: '2-digit', 
        month: '2-digit', 
        year: 'numeric' 
    })
}

function formatearTipo(tipo) {
    const tipos = {
        'escritura_publica': 'Escritura pública',
        'poder': 'Poder',
        'testamento': 'Testamento',
        'carta_notarial': 'Carta notarial',
        'legalizacion': 'Legalización',
        'protesto': 'Protesto',
        'acta_notarial': 'Acta notarial',
    }
    return tipos[tipo] || tipo
}
</script>
