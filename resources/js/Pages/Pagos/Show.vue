<template>
    <AppLayout title="Detalle del Pago">
        <div class="container py-4">
            <!-- Botón Volver -->
            <div class="mb-4">
                <Link
                    :href="route('pagos.index')"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left me-1"></i>
                    Volver al Listado
                </Link>
            </div>

            <div class="row">
                <!-- Información del Pago -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-receipt me-2"></i>
                                Información del Pago
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="text-muted small"
                                        >ID Pago</label
                                    >
                                    <div class="fw-bold">#{{ pago.id }}</div>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small"
                                        >Fecha de Pago</label
                                    >
                                    <div class="fw-bold">
                                        {{ formatDate(pago.fecha) }}
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="text-muted small"
                                        >Monto Pagado</label
                                    >
                                    <div class="fw-bold text-success h4 mb-0">
                                        Bs. {{ formatMoney(pago.monto) }}
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted small"
                                        >Método de Pago</label
                                    >
                                    <div>
                                        <span class="badge bg-primary">
                                            {{
                                                pago.metodo_pago?.nombre ||
                                                "Efectivo"
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="pago.recargo_extra > 0" class="row mb-3">
                                <div class="col-12">
                                    <label class="text-muted small"
                                        >Recargo Extra</label
                                    >
                                    <div class="text-warning fw-bold">
                                        Bs.
                                        {{ formatMoney(pago.recargo_extra) }}
                                    </div>
                                </div>
                            </div>
                            <div
                                v-if="pago.interes_mora_cobrado > 0"
                                class="row mb-3"
                            >
                                <div class="col-12">
                                    <label class="text-muted small"
                                        >Interés por Mora Cobrado</label
                                    >
                                    <div class="text-danger fw-bold">
                                        Bs.
                                        {{
                                            formatMoney(
                                                pago.interes_mora_cobrado
                                            )
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Cliente -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-person me-2"></i>
                                Información del Cliente
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted small"
                                    >Nombre Completo</label
                                >
                                <div class="fw-bold">
                                    {{
                                        pago.cuota?.credito?.venta?.user
                                            ?.name || "N/A"
                                    }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">CI</label>
                                <div>
                                    {{
                                        pago.cuota?.credito?.venta?.user?.ci ||
                                        "N/A"
                                    }}
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Teléfono</label>
                                <div>
                                    {{
                                        pago.cuota?.credito?.venta?.user
                                            ?.telefono || "N/A"
                                    }}
                                </div>
                            </div>
                            <div>
                                <label class="text-muted small">Email</label>
                                <div>
                                    {{
                                        pago.cuota?.credito?.venta?.user
                                            ?.email || "N/A"
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Crédito y Cuota -->
                <div class="col-md-12 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-credit-card me-2"></i>
                                Información del Crédito y Cuota
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small"
                                        >Crédito N°</label
                                    >
                                    <div>
                                        <Link
                                            :href="
                                                route(
                                                    'creditos.show',
                                                    pago.cuota?.credito?.id
                                                )
                                            "
                                            class="text-decoration-none"
                                        >
                                            <span class="badge bg-info">
                                                #{{ pago.cuota?.credito?.id }}
                                            </span>
                                        </Link>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small"
                                        >Cuota N°</label
                                    >
                                    <div class="fw-bold">
                                        {{ pago.cuota?.numero_cuota }} /
                                        {{ pago.cuota?.credito?.cuotas_total }}
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small"
                                        >Monto Cuota</label
                                    >
                                    <div class="fw-bold">
                                        Bs. {{ formatMoney(pago.cuota?.monto) }}
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small"
                                        >Estado Cuota</label
                                    >
                                    <div>
                                        <span
                                            class="badge"
                                            :class="{
                                                'bg-success':
                                                    pago.cuota?.estado ===
                                                    'pagada',
                                                'bg-warning':
                                                    pago.cuota?.estado ===
                                                    'pendiente',
                                                'bg-danger':
                                                    pago.cuota?.estado ===
                                                    'vencida',
                                            }"
                                        >
                                            {{
                                                pago.cuota?.estado?.toUpperCase()
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small"
                                        >Monto Total del Crédito</label
                                    >
                                    <div class="fw-bold">
                                        Bs.
                                        {{
                                            formatMoney(
                                                pago.cuota?.credito
                                                    ?.monto_credito
                                            )
                                        }}
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small"
                                        >Monto Pagado del Crédito</label
                                    >
                                    <div class="fw-bold text-success">
                                        Bs.
                                        {{
                                            formatMoney(
                                                pago.cuota?.credito
                                                    ?.monto_pagado
                                            )
                                        }}
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small"
                                        >Saldo Pendiente del Crédito</label
                                    >
                                    <div class="fw-bold text-warning">
                                        Bs.
                                        {{
                                            formatMoney(
                                                pago.cuota?.credito
                                                    ?.monto_pendiente
                                            )
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de la Venta -->
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-cart me-2"></i>
                                Productos de la Venta
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th class="text-center">
                                                Cantidad
                                            </th>
                                            <th class="text-end">
                                                Precio Unit.
                                            </th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="detalle in pago.cuota
                                                ?.credito?.venta?.detalles"
                                            :key="detalle.id"
                                        >
                                            <td>
                                                {{ detalle.producto?.nombre }}
                                            </td>
                                            <td class="text-center">
                                                {{ detalle.cantidad }}
                                            </td>
                                            <td class="text-end">
                                                Bs.
                                                {{
                                                    formatMoney(
                                                        detalle.precio_unitario
                                                    )
                                                }}
                                            </td>
                                            <td class="text-end">
                                                Bs.
                                                {{
                                                    formatMoney(
                                                        detalle.subtotal
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold">
                                            <td colspan="3" class="text-end">
                                                Total Venta:
                                            </td>
                                            <td class="text-end">
                                                Bs.
                                                {{
                                                    formatMoney(
                                                        pago.cuota?.credito
                                                            ?.venta?.total
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    pago: Object,
});

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);
const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>
