<script setup>
import { computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    venta: Object,
    qrData: Object,
});

const formatPrice = (price) => `Bs. ${parseFloat(price).toFixed(2)}`;
const formatDate = (date) =>
    new Date(date).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });

const estadoBadgeClass = computed(() => {
    const classes = {
        completada: "bg-success",
        pendiente: "bg-warning",
        anulado: "bg-danger",
    };
    return classes[props.venta.estado] || "bg-secondary";
});

const descargarPDF = () => {
    window.open(route("invoices.pdf", props.venta.id), "_blank");
};

const descargarTicket = () => {
    window.open(route("invoices.ticket", props.venta.id), "_blank");
};
</script>

<template>
    <AppLayout title="Boleta de Venta">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Header -->
                    <div
                        class="d-flex justify-content-between align-items-center mb-4"
                    >
                        <div>
                            <h2>Boleta de Venta</h2>
                            <p class="text-muted mb-0">
                                {{ venta.numero_venta }}
                            </p>
                        </div>
                        <div>
                            <button
                                @click="descargarPDF"
                                class="btn btn-primary me-2"
                            >
                                <i class="bi bi-file-pdf"></i> Descargar PDF
                                (A4)
                            </button>
                            <button
                                @click="descargarTicket"
                                class="btn btn-outline-primary"
                            >
                                <i class="bi bi-receipt"></i> Imprimir Ticket
                                (A6)
                            </button>
                        </div>
                    </div>

                    <!-- Información de la venta -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Información General</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p>
                                        <strong>Cliente:</strong>
                                        {{ venta.user.name }}
                                    </p>
                                    <p>
                                        <strong>Email:</strong>
                                        {{ venta.user.email }}
                                    </p>
                                    <p v-if="venta.user.ci">
                                        <strong>CI/NIT:</strong>
                                        {{ venta.user.ci }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p>
                                        <strong>Fecha:</strong>
                                        {{ formatDate(venta.created_at) }}
                                    </p>
                                    <p v-if="venta.vendedor">
                                        <strong>Vendedor:</strong>
                                        {{ venta.vendedor.nombre }}
                                        {{ venta.vendedor.apellidos }}
                                    </p>
                                    <p>
                                        <strong>Tipo de Pago:</strong>
                                        <span
                                            :class="
                                                venta.tipo_pago === 'credito'
                                                    ? 'badge bg-warning'
                                                    : 'badge bg-success'
                                            "
                                        >
                                            {{
                                                venta.tipo_pago === "credito"
                                                    ? "A Crédito"
                                                    : "Al Contado"
                                            }}
                                        </span>
                                    </p>
                                    <p>
                                        <strong>Método de pago:</strong>
                                        <span class="badge bg-primary">
                                            {{
                                                venta.metodoPago?.nombre ||
                                                venta.metodo_pago?.nombre ||
                                                venta.metodo_pago ||
                                                "N/A"
                                            }}
                                        </span>
                                    </p>
                                    <p>
                                        <strong>Estado:</strong>
                                        <span
                                            :class="['badge', estadoBadgeClass]"
                                            >{{ venta.estado }}</span
                                        >
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalle de productos -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Detalle de Productos</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Código</th>
                                            <th class="text-center">
                                                Cantidad
                                            </th>
                                            <th class="text-end">
                                                P. Unitario
                                            </th>
                                            <th class="text-center">Desc.</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="detalle in venta.detalles"
                                            :key="detalle.id"
                                        >
                                            <td>
                                                {{ detalle.producto.nombre }}
                                            </td>
                                            <td>
                                                {{ detalle.producto.codigo }}
                                            </td>
                                            <td class="text-center">
                                                {{ detalle.cantidad }}
                                            </td>
                                            <td class="text-end">
                                                {{
                                                    formatPrice(
                                                        detalle.precio_unitario
                                                    )
                                                }}
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    v-if="detalle.descuento > 0"
                                                    class="badge bg-danger"
                                                >
                                                    -{{ formatPrice(detalle.descuento) }}
                                                </span>
                                                <span v-else>-</span>
                                            </td>
                                            <td class="text-end">
                                                {{
                                                    formatPrice(
                                                        detalle.subtotal
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Crédito (si aplica) -->
                    <div
                        v-if="venta.tipo_pago === 'credito' && venta.credito"
                        class="card mb-4"
                    >
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="bi bi-credit-card me-2"></i>
                                Información del Crédito
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <p>
                                        <strong>Monto Total:</strong><br />
                                        {{
                                            formatPrice(
                                                venta.credito.monto_credito
                                            )
                                        }}
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <p>
                                        <strong>Total Cuotas:</strong><br />
                                        {{ venta.credito.cuotas_total }} cuotas
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <p>
                                        <strong>Monto Pagado:</strong><br />
                                        <span class="text-success">{{
                                            formatPrice(
                                                venta.credito.monto_pagado
                                            )
                                        }}</span>
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <p>
                                        <strong>Monto Pendiente:</strong><br />
                                        <span class="text-danger">{{
                                            formatPrice(
                                                venta.credito.monto_pendiente
                                            )
                                        }}</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Plan de Cuotas -->
                            <h6 class="mb-3">Plan de Cuotas:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Monto</th>
                                            <th>Fecha Vencimiento</th>
                                            <th>Estado</th>
                                            <th>Monto Pagado</th>
                                            <th>Pendiente</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="cuota in venta.credito
                                                .cuotas"
                                            :key="cuota.id"
                                        >
                                            <td>
                                                Cuota {{ cuota.numero_cuota }}
                                            </td>
                                            <td>
                                                {{ formatPrice(cuota.monto) }}
                                            </td>
                                            <td>
                                                {{
                                                    formatDate(
                                                        cuota.fecha_vencimiento
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <span
                                                    :class="
                                                        cuota.estado ===
                                                        'pagada'
                                                            ? 'badge bg-success'
                                                            : cuota.estado ===
                                                              'vencida'
                                                            ? 'badge bg-danger'
                                                            : 'badge bg-warning'
                                                    "
                                                >
                                                    {{ cuota.estado }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-success">
                                                    {{
                                                        formatPrice(
                                                            cuota.monto_pagado
                                                        )
                                                    }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-danger">
                                                    {{
                                                        formatPrice(
                                                            cuota.monto_pendiente
                                                        )
                                                    }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Totales -->
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Código QR -->
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Código de Verificación</h6>
                                </div>
                                <div class="card-body text-center">
                                    <p class="small text-muted mb-2">
                                        UUID de transacción:
                                    </p>
                                    <code
                                        style="
                                            word-wrap: break-word;
                                            font-size: 11px;
                                        "
                                        >{{ qrData.uuid }}</code
                                    >
                                    <p class="mt-3 small text-muted">
                                        <i class="bi bi-info-circle"></i>
                                        QR simulado - Funcionalidad disponible
                                        próximamente
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Resumen de Pago</h6>
                                </div>
                                <div class="card-body">
                                    <div
                                        class="d-flex justify-content-between mb-2"
                                    >
                                        <span>Subtotal:</span>
                                        <strong>{{
                                            formatPrice(
                                                venta.subtotal || venta.total
                                            )
                                        }}</strong>
                                    </div>
                                    <div
                                        class="d-flex justify-content-between mb-2"
                                    >
                                        <span>Descuento:</span>
                                        <strong class="text-danger">{{
                                            formatPrice(venta.descuento || 0)
                                        }}</strong>
                                    </div>
                                    <hr />
                                    <div class="d-flex justify-content-between">
                                        <strong class="fs-5">TOTAL:</strong>
                                        <strong class="fs-4 text-primary">{{
                                            formatPrice(venta.total)
                                        }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="text-center mt-4">
                        <p class="text-muted">¡Gracias por su compra!</p>
                        <p class="small text-muted">
                            Este documento es una representación de la boleta
                            electrónica
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
