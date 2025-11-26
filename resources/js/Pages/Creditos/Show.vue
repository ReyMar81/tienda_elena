<template>
    <AppLayout title="Detalle del Crédito">
        <div class="container py-4">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">
                                <i class="bi bi-credit-card me-2"></i>
                                Detalle del Crédito #{{ credito.id }}
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Mensajes de éxito/error -->
            <div v-if="$page.props.flash?.success" class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ $page.props.flash.success }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <div v-if="$page.props.errors?.error" class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ $page.props.errors.error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

                <!-- Información General -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Información del Crédito</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Cliente</h6>
                                <p>
                                    <strong>Nombre:</strong>
                                    {{ credito.venta?.user?.nombre }}
                                    {{ credito.venta?.user?.apellidos }}
                                </p>
                                <p>
                                    <strong>CI:</strong>
                                    {{ credito.venta?.user?.ci }}
                                </p>
                                <p>
                                    <strong>Teléfono:</strong>
                                    {{ credito.venta?.user?.telefono }}
                                </p>
                                <p>
                                    <strong>Email:</strong>
                                    {{ credito.venta?.user?.email }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">
                                    Información del Crédito
                                </h6>
                                <p>
                                    <strong>Venta:</strong>
                                    <Link
                                        :href="
                                            route(
                                                'ventas.show',
                                                credito.venta_id
                                            )
                                        "
                                        class="text-primary"
                                    >
                                        {{ credito.venta?.numero_venta }}
                                    </Link>
                                </p>
                                <p>
                                    <strong>Fecha Otorgamiento:</strong>
                                    {{
                                        formatearFecha(
                                            credito.fecha_otorgamiento
                                        )
                                    }}
                                </p>
                                <p>
                                    <strong>Fecha Vencimiento:</strong>
                                    {{
                                        formatearFecha(
                                            credito.fecha_vencimiento
                                        )
                                    }}
                                </p>
                                <p>
                                    <strong>Estado:</strong>
                                    <span
                                        :class="getEstadoBadge(credito.estado)"
                                    >
                                        {{ credito.estado.toUpperCase() }}
                                    </span>
                                </p>
                                <p v-if="credito.dias_mora > 0">
                                    <strong class="text-danger"
                                        >Días de Mora:</strong
                                    >
                                    <span class="badge bg-danger"
                                        >{{ credito.dias_mora }} días</span
                                    >
                                </p>
                            </div>
                        </div>

                        <hr />

                        <div class="row">
                            <div class="col-md-3">
                                <h6 class="fw-bold">Monto Total</h6>
                                <h4 class="text-primary">
                                    {{ formatearMoneda(credito.monto_credito) }}
                                </h4>
                            </div>
                            <div class="col-md-3">
                                <h6 class="fw-bold">Total Cuotas</h6>
                                <h4>{{ credito.cuotas_total }} cuotas</h4>
                            </div>
                            <div class="col-md-3">
                                <h6 class="fw-bold">Monto Pagado</h6>
                                <h4 class="text-success">
                                    {{ formatearMoneda(credito.monto_pagado) }}
                                </h4>
                            </div>
                            <div class="col-md-3">
                                <h6 class="fw-bold">Monto Pendiente</h6>
                                <h4 class="text-danger">
                                    {{
                                        formatearMoneda(credito.monto_pendiente)
                                    }}
                                </h4>
                            </div>
                        </div>

                        <!-- Barra de progreso -->
                        <div class="mt-3">
                            <div class="progress" style="height: 30px">
                                <div
                                    class="progress-bar bg-success"
                                    :style="{ width: porcentajePagado + '%' }"
                                >
                                    {{ porcentajePagado.toFixed(1) }}% Pagado
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Plan de Cuotas -->
                <div class="card mb-4">
                    <div
                        class="card-header bg-warning text-dark d-flex justify-content-between align-items-center"
                    >
                        <h5 class="mb-0">Plan de Cuotas</h5>
                        <button
                            v-if="tieneCuotasPendientes"
                            @click="abrirModalPago()"
                            class="btn btn-sm btn-success"
                        >
                            <i class="bi bi-cash-coin me-2"></i>
                            Registrar Pago
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th class="text-end">Monto</th>
                                        <th>Fecha Vencimiento</th>
                                        <th class="text-end">Monto Pagado</th>
                                        <th class="text-end">
                                            Monto Pendiente
                                        </th>
                                        <th class="text-center">Mora</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="cuota in credito.cuotas"
                                        :key="cuota.id"
                                    >
                                        <td>Cuota {{ cuota.numero_cuota }}</td>
                                        <td class="text-end">
                                            {{ formatearMoneda(cuota.monto) }}
                                        </td>
                                        <td>
                                            {{
                                                formatearFecha(
                                                    cuota.fecha_vencimiento
                                                )
                                            }}
                                        </td>
                                        <td class="text-end text-success">
                                            {{
                                                formatearMoneda(
                                                    cuota.monto_pagado
                                                )
                                            }}
                                        </td>
                                        <td class="text-end text-danger">
                                            {{
                                                formatearMoneda(
                                                    cuota.monto_pendiente
                                                )
                                            }}
                                        </td>
                                        <td class="text-center">
                                            <span
                                                v-if="cuota.dias_mora > 0"
                                                class="badge bg-danger"
                                            >
                                                {{ cuota.dias_mora }} días
                                            </span>
                                            <span v-else class="text-muted"
                                                >-</span
                                            >
                                        </td>
                                        <td class="text-center">
                                            <span
                                                :class="
                                                    getCuotaBadge(cuota.estado)
                                                "
                                            >
                                                {{ cuota.estado.toUpperCase() }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button
                                                v-if="cuota.estado !== 'pagado'"
                                                @click="
                                                    abrirModalPago(cuota.id)
                                                "
                                                class="btn btn-sm btn-success"
                                                title="Pagar cuota"
                                            >
                                                <i class="bi bi-cash-coin"></i>
                                                Pagar
                                            </button>
                                            <button
                                                v-if="
                                                    cuota.pagos &&
                                                    cuota.pagos.length > 0
                                                "
                                                @click="togglePagos(cuota.id)"
                                                class="btn btn-sm btn-info ms-1"
                                                title="Ver pagos"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Pagos de la cuota (expandible) -->
                                    <tr
                                        v-for="cuota in credito.cuotas"
                                        v-show="
                                            cuotaExpandida === cuota.id &&
                                            cuota.pagos?.length > 0
                                        "
                                        :key="'pagos-' + cuota.id"
                                    >
                                        <td colspan="8" class="bg-light">
                                            <div class="p-3">
                                                <h6 class="fw-bold mb-3">
                                                    Historial de Pagos - Cuota
                                                    #{{ cuota.numero_cuota }}
                                                </h6>
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Fecha</th>
                                                            <th>Monto</th>
                                                            <th>Método</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr
                                                            v-for="pago in cuota.pagos"
                                                            :key="pago.id"
                                                        >
                                                            <td>
                                                                {{
                                                                    formatearFecha(
                                                                        pago.fecha
                                                                    )
                                                                }}
                                                            </td>
                                                            <td>
                                                                {{
                                                                    formatearMoneda(
                                                                        pago.monto
                                                                    )
                                                                }}
                                                            </td>
                                                            <td>
                                                                {{
                                                                    pago
                                                                        .metodoPago
                                                                        ?.nombre ||
                                                                    pago
                                                                        .metodo_pago
                                                                        ?.nombre
                                                                }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="d-flex gap-3">
                    <Link
                        :href="route('creditos.index')"
                        class="btn btn-secondary"
                    >
                        <i class="bi bi-arrow-left me-2"></i>
                        Volver a Créditos
                    </Link>
                </div>
            </div>

        <!-- Modal de Pago -->
        <PagoModal
            :show="mostrarModalPago"
            :cuotas="credito.cuotas"
            :metodosPago="metodosPago"
            :cuotaPreseleccionada="cuotaSeleccionadaId"
            @close="cerrarModalPago"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PagoModal from "@/Components/PagoModal.vue";

const props = defineProps({
    credito: Object,
    metodosPago: Array,
});

const mostrarModalPago = ref(false);
const cuotaExpandida = ref(null);
const cuotaSeleccionadaId = ref(null);

const formatearMoneda = (valor) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
        minimumFractionDigits: 2,
    }).format(valor);
};

const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const getEstadoBadge = (estado) => {
    const badges = {
        pendiente: "badge bg-warning",
        pagado: "badge bg-success",
        vencido: "badge bg-danger",
    };
    return badges[estado] || "badge bg-secondary";
};

const getCuotaBadge = (estado) => {
    const badges = {
        pendiente: "badge bg-warning",
        pagado: "badge bg-success",
        vencido: "badge bg-danger",
    };
    return badges[estado] || "badge bg-secondary";
};

const porcentajePagado = computed(() => {
    if (props.credito.monto_credito === 0) return 0;
    return (props.credito.monto_pagado / props.credito.monto_credito) * 100;
});

const tieneCuotasPendientes = computed(() => {
    return props.credito.cuotas?.some(
        (c) => c.estado === "pendiente" || c.estado === "vencido"
    );
});

const abrirModalPago = (cuotaId = null) => {
    cuotaSeleccionadaId.value = cuotaId;
    mostrarModalPago.value = true;
};

const cerrarModalPago = () => {
    mostrarModalPago.value = false;
    cuotaSeleccionadaId.value = null;
};

const togglePagos = (cuotaId) => {
    cuotaExpandida.value = cuotaExpandida.value === cuotaId ? null : cuotaId;
};
</script>
