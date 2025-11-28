<template>
    <AppLayout title="Detalle de Mi Crédito">
        <FlashNotification />
        <div class="container py-4">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div
                        class="d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <h2 class="mb-0">
                                <i class="bi bi-credit-card me-2"></i>
                                Detalle del Crédito #{{ credito.id }}
                            </h2>
                        </div>
                    </div>
                </div>
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
                                        route('ventas.show', credito.venta_id)
                                    "
                                    class="text-primary"
                                >
                                    {{ credito.venta?.numero_venta }}
                                </Link>
                            </p>
                            <p>
                                <strong>Fecha Otorgamiento:</strong>
                                {{ formatearFecha(credito.fecha_otorgamiento) }}
                            </p>
                            <p>
                                <strong>Fecha Vencimiento:</strong>
                                {{ formatearFecha(credito.fecha_vencimiento) }}
                            </p>
                            <p>
                                <strong>Estado:</strong>
                                <span :class="getEstadoBadge(credito.estado)">
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
                                {{ formatearMoneda(credito.monto_pendiente) }}
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
                        class="btn btn-success btn-sm"
                        @click="abrirModalPago"
                    >
                        <i class="bi bi-qr-code me-2"></i>Pagar por QR
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
                                    <th class="text-end">Monto Pendiente</th>
                                    <th class="text-center">Mora</th>
                                    <th class="text-center">Estado</th>
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
                                            formatearMoneda(cuota.monto_pagado)
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
                                        <span v-else class="text-muted">-</span>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            :class="getCuotaBadge(cuota.estado)"
                                        >
                                            {{ cuota.estado.toUpperCase() }}
                                        </span>
                                        <button
                                            v-if="cuota.estado !== 'pagada'"
                                            class="btn btn-outline-success btn-sm ms-2"
                                            @click="abrirModalPagoCuota(cuota)"
                                        >
                                            <i class="bi bi-qr-code"></i>
                                        </button>
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
                    :href="route('mis-creditos.index')"
                    class="btn btn-secondary"
                >
                    <i class="bi bi-arrow-left me-2"></i>
                    Volver a Mis Créditos
                </Link>
            </div>
        </div>
    </AppLayout>

    <!-- Modal de Pago por QR -->
    <template v-if="mostrarModalPago">
        <div
            class="modal fade show d-block"
            tabindex="-1"
            style="background: rgba(0, 0, 0, 0.3)"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-qr-code me-2"></i>Pagar cuota por QR
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            @click="cerrarModalPago"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Seleccionar cuota</label>
                            <select
                                class="form-select"
                                v-model="cuotaSeleccionadaId"
                                @change="seleccionarCuota($event.target.value)"
                            >
                                <option value="">
                                    -- Seleccione una cuota --
                                </option>
                                <option
                                    v-for="cuota in cuotasDisponibles"
                                    :key="cuota.id"
                                    :value="cuota.id"
                                >
                                    Cuota #{{ cuota.numero_cuota }} - Pendiente:
                                    {{ formatearMoneda(cuota.monto_pendiente) }}
                                </option>
                            </select>
                        </div>
                        <div v-if="cuotaSeleccionada">
                            <div class="mb-3">
                                <label class="form-label">Monto a pagar</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="montoPago"
                                    :max="cuotaSeleccionada.monto_pendiente"
                                    min="0.01"
                                    step="0.01"
                                />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fecha de pago</label>
                                <input
                                    type="date"
                                    class="form-control"
                                    v-model="fechaPago"
                                />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Método de pago</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    value="QR"
                                    disabled
                                />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            @click="cerrarModalPago"
                            :disabled="cargandoPago"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="btn btn-success"
                            :disabled="
                                !cuotaSeleccionada ||
                                !montoPago ||
                                montoPago < 0.01 ||
                                cargandoPago
                            "
                            @click="pagarPorQR"
                        >
                            <span
                                v-if="cargandoPago"
                                class="spinner-border spinner-border-sm me-2"
                            ></span>
                            <i class="bi bi-qr-code me-2"></i>Pagar por QR
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    credito: Object,
});

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
        pagada: "badge bg-success",
        vencido: "badge bg-danger",
    };
    return badges[estado] || "badge bg-secondary";
};

const porcentajePagado = computed(() => {
    if (props.credito.monto_credito === 0) return 0;
    return (props.credito.monto_pagado / props.credito.monto_credito) * 100;
});

// --- Estado y lógica para el modal de pago QR ---
const mostrarModalPago = ref(false);
const cuotaSeleccionada = ref(null);
const montoPago = ref(0);
const fechaPago = ref(new Date().toISOString().slice(0, 10));
const cuotaSeleccionadaId = ref("");
const cargandoPago = ref(false);

const abrirModalPago = () => {
    cuotaSeleccionada.value = null;
    cuotaSeleccionadaId.value = "";
    montoPago.value = 0;
    fechaPago.value = new Date().toISOString().slice(0, 10);
    mostrarModalPago.value = true;
};

const abrirModalPagoCuota = (cuota) => {
    cuotaSeleccionada.value = cuota;
    cuotaSeleccionadaId.value = cuota.id.toString();
    montoPago.value = cuota.monto_pendiente;
    fechaPago.value = new Date().toISOString().slice(0, 10);
    mostrarModalPago.value = true;
};

const cerrarModalPago = () => {
    mostrarModalPago.value = false;
    cuotaSeleccionada.value = null;
    cuotaSeleccionadaId.value = "";
    montoPago.value = 0;
};

const cuotasDisponibles = computed(() => {
    return props.credito.cuotas?.filter((c) => c.estado !== "pagada") || [];
});

const seleccionarCuota = (cuotaId) => {
    const cuota = cuotasDisponibles.value.find(
        (c) => c.id === parseInt(cuotaId)
    );
    cuotaSeleccionada.value = cuota;
    montoPago.value = cuota ? cuota.monto_pendiente : 0;
};

const pagarPorQR = () => {
    if (!cuotaSeleccionada.value || !montoPago.value || montoPago.value < 0.01)
        return;
    cargandoPago.value = true;
    router.post(
        route("mis-creditos.registrar-pago"),
        {
            cuota_id: cuotaSeleccionada.value.id,
            monto: montoPago.value,
            fecha: fechaPago.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                cerrarModalPago();
                // Recargar la página de detalle para actualizar cuotas y estado
                router.reload({ only: ["credito"] });
            },
            onFinish: () => {
                cargandoPago.value = false;
            },
        }
    );
};
</script>
