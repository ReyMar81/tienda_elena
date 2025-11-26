<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const tipoReporte = ref("ventas-fecha");
const fechaInicio = ref("");
const fechaFin = ref("");
const limite = ref(20);
const stockMinimo = ref(10);

const reportes = [
    {
        value: "ventas-fecha",
        label: "Ventas por Fecha",
        icon: "bi-calendar-check",
        requireFechas: true,
    },
    {
        value: "ventas-metodo",
        label: "Ventas por Método de Pago",
        icon: "bi-credit-card",
        requireFechas: true,
    },
    {
        value: "creditos-estado",
        label: "Créditos por Estado",
        icon: "bi-cash-stack",
        requireFechas: true,
    },
    {
        value: "productos-vendidos",
        label: "Productos Más Vendidos",
        icon: "bi-bar-chart",
        requireFechas: true,
        hasLimite: true,
    },
    {
        value: "clientes-top",
        label: "Clientes Top",
        icon: "bi-people",
        requireFechas: true,
        hasLimite: true,
    },
    {
        value: "inventario-critico",
        label: "Inventario Crítico",
        icon: "bi-exclamation-triangle",
        requireFechas: false,
        hasStock: true,
    },
];

const reporteSeleccionado = ref(reportes[0]);

const seleccionarReporte = (reporte) => {
    reporteSeleccionado.value = reporte;
    tipoReporte.value = reporte.value;
};

const generarReporte = () => {
    if (!fechaInicio.value || !fechaFin.value) {
        alert("Por favor selecciona las fechas");
        return;
    }

    const params = {
        fecha_inicio: fechaInicio.value,
        fecha_fin: fechaFin.value,
    };

    if (reporteSeleccionado.value.hasLimite) {
        params.limite = limite.value;
    }

    if (reporteSeleccionado.value.hasStock) {
        params.stock_minimo = stockMinimo.value;
    }

    router.get(route("reportes.show", tipoReporte.value), params);
};

// Establecer fechas por defecto (último mes)
const hoy = new Date();
const haceUnMes = new Date();
haceUnMes.setMonth(haceUnMes.getMonth() - 1);

fechaFin.value = hoy.toISOString().split("T")[0];
fechaInicio.value = haceUnMes.toISOString().split("T")[0];
</script>

<template>
    <AppLayout title="Reportes y Estadísticas">
        <div class="container py-4">
            <h2 class="mb-4">Reportes y Estadísticas</h2>

            <div class="row">
                <!-- Menú de reportes -->
                <div class="col-lg-3 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Tipos de Reporte</h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <button
                                v-for="reporte in reportes"
                                :key="reporte.value"
                                @click="seleccionarReporte(reporte)"
                                class="list-group-item list-group-item-action"
                                :class="{
                                    active: tipoReporte === reporte.value,
                                }"
                            >
                                <i :class="['bi', reporte.icon, 'me-2']"></i>
                                {{ reporte.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Formulario de parámetros -->
                <div class="col-lg-9">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i
                                    :class="[
                                        'bi',
                                        reporteSeleccionado.icon,
                                        'me-2',
                                    ]"
                                ></i>
                                {{ reporteSeleccionado.label }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="generarReporte">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            >Fecha Inicio</label
                                        >
                                        <input
                                            v-model="fechaInicio"
                                            type="date"
                                            class="form-control"
                                            required
                                        />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            >Fecha Fin</label
                                        >
                                        <input
                                            v-model="fechaFin"
                                            type="date"
                                            class="form-control"
                                            required
                                        />
                                    </div>
                                </div>

                                <div
                                    v-if="reporteSeleccionado.hasLimite"
                                    class="row mb-3"
                                >
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            >Límite de registros</label
                                        >
                                        <select
                                            v-model.number="limite"
                                            class="form-select"
                                        >
                                            <option :value="10">10</option>
                                            <option :value="20">20</option>
                                            <option :value="50">50</option>
                                            <option :value="100">100</option>
                                        </select>
                                    </div>
                                </div>

                                <div
                                    v-if="reporteSeleccionado.hasStock"
                                    class="row mb-3"
                                >
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            >Stock mínimo</label
                                        >
                                        <input
                                            v-model.number="stockMinimo"
                                            type="number"
                                            class="form-control"
                                            min="0"
                                            max="100"
                                        />
                                        <small class="text-muted"
                                            >Productos con stock menor o igual a
                                            este valor</small
                                        >
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    El rango de fechas no puede ser mayor a 1
                                    año.
                                </div>

                                <button
                                    type="submit"
                                    class="btn btn-primary btn-lg"
                                >
                                    <i
                                        class="bi bi-file-earmark-bar-graph me-2"
                                    ></i>
                                    Generar Reporte
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <h6 class="mb-3">Descripción del Reporte</h6>
                            <p v-if="tipoReporte === 'ventas-fecha'">
                                Listado detallado de todas las ventas realizadas
                                en el período seleccionado, incluyendo cliente,
                                vendedor, método de pago y total.
                            </p>
                            <p v-else-if="tipoReporte === 'ventas-metodo'">
                                Resumen de ventas agrupadas por método de pago
                                (efectivo, tarjeta, transferencia, crédito),
                                mostrando cantidad y monto total por cada uno.
                            </p>
                            <p v-else-if="tipoReporte === 'creditos-estado'">
                                Estado de los créditos otorgados: activos,
                                pagados y vencidos, con montos totales y saldos
                                pendientes.
                            </p>
                            <p v-else-if="tipoReporte === 'productos-vendidos'">
                                Ranking de productos más vendidos en el período,
                                mostrando cantidad vendida, stock actual e
                                ingresos generados.
                            </p>
                            <p v-else-if="tipoReporte === 'clientes-top'">
                                Listado de clientes con mayor volumen de
                                compras, ordenados por monto total gastado en el
                                período.
                            </p>
                            <p v-else-if="tipoReporte === 'inventario-critico'">
                                Productos con stock bajo o agotado que requieren
                                reposición urgente.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
