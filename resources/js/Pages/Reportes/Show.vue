<script setup>
import { computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    tipo: String,
    titulo: String,
    datos: [Array, Object],
    parametros: Object,
});

const formatPrice = (price) => `Bs. ${parseFloat(price).toFixed(2)}`;
const formatDate = (date) => new Date(date).toLocaleDateString("es-ES");

const descargarPDF = () => {
    const params = new URLSearchParams(props.parametros);
    window.open(
        route("reportes.pdf", props.tipo) + "?" + params.toString(),
        "_blank"
    );
};

const hayDatos = computed(() => {
    return Array.isArray(props.datos)
        ? props.datos.length > 0
        : Object.keys(props.datos).length > 0;
});
</script>

<template>
    <AppLayout :title="titulo">
        <div class="container py-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>{{ titulo }}</h2>
                    <p class="text-muted mb-0">
                        Período: {{ parametros.fecha_inicio }} -
                        {{ parametros.fecha_fin }}
                    </p>
                </div>
                <div>
                    <a
                        :href="route('reportes.index')"
                        class="btn btn-outline-secondary me-2"
                    >
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <button
                        @click="descargarPDF"
                        class="btn btn-danger"
                        :disabled="!hayDatos"
                    >
                        <i class="bi bi-file-pdf"></i> Descargar PDF
                    </button>
                </div>
            </div>

            <!-- Sin datos -->
            <div v-if="!hayDatos" class="alert alert-warning text-center">
                <i class="bi bi-info-circle fs-1 d-block mb-3"></i>
                <h5>Sin datos</h5>
                <p>
                    No se encontraron resultados para los parámetros
                    seleccionados.
                </p>
            </div>

            <!-- Reporte: Ventas por Fecha -->
            <div v-else-if="tipo === 'ventas-fecha'" class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Número</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th>Método</th>
                                    <th class="text-end">Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(venta, index) in datos"
                                    :key="venta.id"
                                >
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ venta.numero_venta || "N/A" }}</td>
                                    <td>{{ formatDate(venta.created_at) }}</td>
                                    <td>{{ venta.user?.name || "N/A" }}</td>
                                    <td>
                                        {{ venta.vendedor?.name || "Sistema" }}
                                    </td>
                                    <td>{{ venta.metodo_pago }}</td>
                                    <td class="text-end">
                                        {{ formatPrice(venta.total) }}
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{
                                            venta.estado
                                        }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Reporte: Ventas por Método -->
            <div v-else-if="tipo === 'ventas-metodo'" class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Método de Pago</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Monto Total</th>
                                    <th class="text-end">Promedio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="metodo in datos"
                                    :key="metodo.metodo_pago"
                                >
                                    <td>{{ metodo.metodo_pago }}</td>
                                    <td class="text-center">
                                        {{ metodo.cantidad }}
                                    </td>
                                    <td class="text-end">
                                        {{ formatPrice(metodo.monto_total) }}
                                    </td>
                                    <td class="text-end">
                                        {{
                                            formatPrice(
                                                metodo.monto_total /
                                                    metodo.cantidad
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Reporte: Créditos por Estado -->
            <div v-else-if="tipo === 'creditos-estado'" class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Estado</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Monto Total</th>
                                    <th class="text-end">Saldo Pendiente</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="credito in datos"
                                    :key="credito.estado"
                                >
                                    <td>{{ credito.estado }}</td>
                                    <td class="text-center">
                                        {{ credito.cantidad }}
                                    </td>
                                    <td class="text-end">
                                        {{ formatPrice(credito.monto_total) }}
                                    </td>
                                    <td class="text-end">
                                        {{
                                            formatPrice(credito.monto_pendiente)
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Reporte: Productos Más Vendidos -->
            <div v-else-if="tipo === 'productos-vendidos'" class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th>Código</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-center">Vendido</th>
                                    <th class="text-end">Ingresos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(producto, index) in datos"
                                    :key="producto.codigo"
                                >
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ producto.nombre }}</td>
                                    <td>{{ producto.codigo }}</td>
                                    <td class="text-center">
                                        {{ producto.stock }}
                                    </td>
                                    <td class="text-center">
                                        {{ producto.total_vendido }}
                                    </td>
                                    <td class="text-end">
                                        {{ formatPrice(producto.ingresos) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Reporte: Clientes Top -->
            <div v-else-if="tipo === 'clientes-top'" class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cliente</th>
                                    <th>Email</th>
                                    <th class="text-center">Compras</th>
                                    <th class="text-end">Monto Total</th>
                                    <th class="text-end">Promedio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(cliente, index) in datos"
                                    :key="cliente.email"
                                >
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ cliente.name }}</td>
                                    <td>{{ cliente.email }}</td>
                                    <td class="text-center">
                                        {{ cliente.total_compras }}
                                    </td>
                                    <td class="text-end">
                                        {{ formatPrice(cliente.monto_total) }}
                                    </td>
                                    <td class="text-end">
                                        {{
                                            formatPrice(
                                                cliente.monto_total /
                                                    cliente.total_compras
                                            )
                                        }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Reporte: Inventario Crítico -->
            <div v-else-if="tipo === 'inventario-critico'" class="card">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Productos con stock bajo o agotado
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Producto</th>
                                    <th>Código</th>
                                    <th>Categoría</th>
                                    <th class="text-center">Stock</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(producto, index) in datos"
                                    :key="producto.id"
                                    :class="{
                                        'table-danger': producto.stock === 0,
                                    }"
                                >
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ producto.nombre }}</td>
                                    <td>{{ producto.codigo }}</td>
                                    <td>
                                        {{
                                            producto.categoria?.nombre || "N/A"
                                        }}
                                    </td>
                                    <td class="text-center">
                                        {{ producto.stock }}
                                    </td>
                                    <td>
                                        <span
                                            v-if="producto.stock === 0"
                                            class="badge bg-danger"
                                            >AGOTADO</span
                                        >
                                        <span v-else class="badge bg-warning"
                                            >Stock Bajo</span
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
