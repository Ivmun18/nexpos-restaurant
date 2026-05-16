<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-6 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">🔐 Auditoría del Sistema</h1>
            <p class="mt-2 text-sm text-gray-600">Registro completo de todas las acciones en el sistema</p>
          </div>
          <button
            @click="exportarExcel"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Descargar Excel
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-8">
      <!-- Estadísticas -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm">Total de Registros</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">{{ estadisticas.total_registros }}</p>
            </div>
            <svg class="w-12 h-12 text-blue-500 opacity-20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M9 3H3v18h18V9h-12V3zm0 4h4v4H9V7zm6 0h4v4h-4V7zM9 15h4v4H9v-4zm6 0h4v4h-4v-4z"></path>
            </svg>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm">Hoy</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">{{ estadisticas.hoy }}</p>
            </div>
            <svg class="w-12 h-12 text-green-500 opacity-20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M7 10a2 2 0 11-4 0 2 2 0 014 0zM19 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm">Esta Semana</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">{{ estadisticas.esta_semana }}</p>
            </div>
            <svg class="w-12 h-12 text-yellow-500 opacity-20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 text-sm">Este Mes</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">{{ estadisticas.este_mes }}</p>
            </div>
            <svg class="w-12 h-12 text-purple-500 opacity-20" fill="currentColor" viewBox="0 0 24 24">
              <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
          </div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">🔍 Filtros</h2>
        <form @submit.prevent="aplicarFiltros" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Búsqueda -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
              <input
                v-model="formulario.buscar"
                type="text"
                placeholder="Usuario, email, IP, detalles..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <!-- Módulo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Módulo</label>
              <select
                v-model="formulario.modulo"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">-- Todos --</option>
                <option v-for="modulo in modulos" :key="modulo" :value="modulo">
                  {{ modulo }}
                </option>
              </select>
            </div>

            <!-- Acción -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Acción</label>
              <select
                v-model="formulario.accion"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">-- Todas --</option>
                <option v-for="accion in acciones" :key="accion" :value="accion">
                  {{ accion }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Usuario -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Usuario</label>
              <select
                v-model="formulario.usuario_id"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">-- Todos --</option>
                <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                  {{ usuario.name }}
                </option>
              </select>
            </div>

            <!-- Fecha Desde -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Desde</label>
              <input
                v-model="formulario.fecha_desde"
                type="date"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <!-- Fecha Hasta -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Hasta</label>
              <input
                v-model="formulario.fecha_hasta"
                type="date"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>

          <div class="flex gap-2">
            <button
              type="submit"
              class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition font-medium"
            >
              Aplicar Filtros
            </button>
            <button
              type="button"
              @click="limpiarFiltros"
              class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-2 rounded-lg transition font-medium"
            >
              Limpiar
            </button>
          </div>
        </form>
      </div>

      <!-- Tabla de Auditorías -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-100 border-b-2 border-gray-200">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Fecha</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Usuario</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Acción</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Módulo</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Detalles</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">IP</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="audit in audits.data"
              :key="audit.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                <div class="font-medium">{{ formatearFecha(audit.created_at) }}</div>
                <div class="text-gray-500 text-xs">{{ formatearHora(audit.created_at) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ audit.usuario?.name || 'Sistema' }}</div>
                <div class="text-xs text-gray-500">{{ audit.usuario?.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['px-3 py-1 rounded-full text-xs font-semibold', getColorAccion(audit.accion)]">
                  {{ audit.accion.toUpperCase() }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                  {{ audit.modulo }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                {{ audit.detalles }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                {{ audit.ip_address }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <Link
                  :href="`/admin/auditoria/${audit.id}`"
                  class="text-blue-600 hover:text-blue-900 font-medium transition"
                >
                  Ver Detalles →
                </Link>
              </td>
            </tr>
            <tr v-if="audits.data.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                No hay registros de auditoría
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginación -->
        <div v-if="audits.last_page > 1" class="bg-white px-6 py-4 border-t flex items-center justify-between">
          <div class="text-sm text-gray-600">
            Mostrando {{ audits.from }} a {{ audits.to }} de {{ audits.total }} registros
          </div>
          <div class="flex gap-2">
            <Link
              v-if="audits.prev_page_url"
              :href="audits.prev_page_url"
              class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition"
            >
              ← Anterior
            </Link>
            <div class="flex items-center gap-2">
              <span v-for="page in audits.last_page" :key="page" class="text-sm">
                <Link
                  v-if="page === audits.current_page"
                  class="px-3 py-2 bg-blue-600 text-white rounded-lg font-semibold"
                >
                  {{ page }}
                </Link>
                <Link
                  v-else
                  :href="`?page=${page}`"
                  class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                >
                  {{ page }}
                </Link>
              </span>
            </div>
            <Link
              v-if="audits.next_page_url"
              :href="audits.next_page_url"
              class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition"
            >
              Siguiente →
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3';

export default {
  components: {
    Head,
    Link,
  },
  props: {
    audits: Object,
    modulos: Array,
    acciones: Array,
    usuarios: Array,
    estadisticas: Object,
    filtros: Object,
  },
  data() {
    return {
      formulario: {
        buscar: this.filtros?.buscar || '',
        modulo: this.filtros?.modulo || '',
        accion: this.filtros?.accion || '',
        usuario_id: this.filtros?.usuario_id || '',
        fecha_desde: this.filtros?.fecha_desde || '',
        fecha_hasta: this.filtros?.fecha_hasta || '',
      },
    };
  },
  methods: {
    aplicarFiltros() {
      const params = new URLSearchParams();
      if (this.formulario.buscar) params.append('buscar', this.formulario.buscar);
      if (this.formulario.modulo) params.append('modulo', this.formulario.modulo);
      if (this.formulario.accion) params.append('accion', this.formulario.accion);
      if (this.formulario.usuario_id) params.append('usuario_id', this.formulario.usuario_id);
      if (this.formulario.fecha_desde) params.append('fecha_desde', this.formulario.fecha_desde);
      if (this.formulario.fecha_hasta) params.append('fecha_hasta', this.formulario.fecha_hasta);

      window.location.href = `/admin/auditoria?${params.toString()}`;
    },
    limpiarFiltros() {
      this.formulario = {
        buscar: '',
        modulo: '',
        accion: '',
        usuario_id: '',
        fecha_desde: '',
        fecha_hasta: '',
      };
      window.location.href = '/admin/auditoria';
    },
    formatearFecha(fecha) {
      return new Date(fecha).toLocaleDateString('es-PE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
      });
    },
    formatearHora(fecha) {
      return new Date(fecha).toLocaleTimeString('es-PE', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
      });
    },
    getColorAccion(accion) {
      const colores = {
        create: 'bg-green-100 text-green-800',
        view: 'bg-blue-100 text-blue-800',
        update: 'bg-yellow-100 text-yellow-800',
        delete: 'bg-red-100 text-red-800',
      };
      return colores[accion] || 'bg-gray-100 text-gray-800';
    },
    exportarExcel() {
      const params = new URLSearchParams();
      if (this.formulario.buscar) params.append('buscar', this.formulario.buscar);
      if (this.formulario.modulo) params.append('modulo', this.formulario.modulo);
      if (this.formulario.accion) params.append('accion', this.formulario.accion);
      if (this.formulario.usuario_id) params.append('usuario_id', this.formulario.usuario_id);
      if (this.formulario.fecha_desde) params.append('fecha_desde', this.formulario.fecha_desde);
      if (this.formulario.fecha_hasta) params.append('fecha_hasta', this.formulario.fecha_hasta);

      window.location.href = `/admin/auditoria/exportar?${params.toString()}`;
    },
  },
};
</script>
