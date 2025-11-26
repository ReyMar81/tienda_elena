<template>
    <AppLayout title="Confirmación de Compra">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="bi bi-check-circle-fill text-success me-2"></i>Compra
                Confirmada
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Mensaje de Éxito -->
                <div class="alert alert-success mb-4 text-center">
                    <i
                        class="bi bi-check-circle-fill"
                        style="font-size: 3rem"
                    ></i>
                    <h3 class="mt-3">
                        ¡Tu compra ha sido procesada exitosamente!
                    </h3>
                    <p class="mb-0">
                        Número de venta:
                        <strong>{{ venta.numero_venta }}</strong>
                    </p>
                </div>

                <!-- Información de la Venta -->
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-4"
                >
                    <div class="p-4 border-b border-gray-200">
                        <h4 class="font-semibold text-lg">
                            Detalles de la Venta
                        </h4>
                    </div>

                    <div class="p-4">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Fecha:</strong>
                                    {{
                                        new Date(
                                            venta.created_at
                                        ).toLocaleDateString("es-ES")
                                    }}
                                </p>
                                <p class="mb-2">
                                    <strong>Método de Pago:</strong>
                                    {{ venta.metodo_pago?.nombre }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Estado:</strong>
                                    <span
                                        :class="
                                            getEstadoBadgeClass(venta.estado)
                                        "
                                    >
                                        {{ venta.estado }}
                                    </span>
                                </p>
                                <p class="mb-2">
                                    <strong>Total:</strong>
                                    <span class="text-primary fw-bold"
                                        >${{ venta.total.toFixed(2) }}</span
                                    >
                                </p>
                            </div>
                        </div>

                        <!-- Productos -->
                        <h6 class="font-semibold mb-3">Productos:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio</th>
                                        <th class="text-end">Descuento</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="detalle in venta.detalles"
                                        :key="detalle.id"
                                    >
                                        <td>{{ detalle.producto?.nombre }}</td>
                                        <td class="text-center">
                                            {{ detalle.cantidad }}
                                        </td>
                                        <td class="text-end">
                                            ${{
                                                detalle.precio_unitario.toFixed(
                                                    2
                                                )
                                            }}
                                        </td>
                                        <td class="text-end">
                                            ${{ detalle.descuento.toFixed(2) }}
                                        </td>
                                        <td class="text-end fw-semibold">
                                            ${{ detalle.subtotal.toFixed(2) }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td
                                            colspan="4"
                                            class="text-end fw-bold"
                                        >
                                            Total:
                                        </td>
                                        <td
                                            class="text-end fw-bold text-primary"
                                        >
                                            ${{ venta.total.toFixed(2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Información de Crédito (si aplica) -->
                <div
                    v-if="venta.credito"
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-4"
                >
                    <div class="p-4 border-b border-gray-200">
                        <h4 class="font-semibold text-lg">
                            <i class="bi bi-credit-card me-2"></i>Información
                            del Crédito
                        </h4>
                    </div>

                    <div class="p-4">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <p class="mb-2">
                                    <strong>Monto del Crédito:</strong><br />
                                    ${{
                                        venta.credito.monto_credito.toFixed(2)
                                    }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-2">
                                    <strong>Interés:</strong><br />
                                    ${{ venta.credito.interes.toFixed(2) }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-2">
                                    <strong>Total Cuotas:</strong><br />
                                    {{ venta.credito.cuotas_total }} cuotas
                                </p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Fecha Otorgamiento:</strong><br />
                                    {{
                                        new Date(
                                            venta.credito.fecha_otorgamiento
                                        ).toLocaleDateString("es-ES")
                                    }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Fecha Vencimiento Final:</strong
                                    ><br />
                                    {{
                                        new Date(
                                            venta.credito.fecha_vencimiento
                                        ).toLocaleDateString("es-ES")
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Plan de Cuotas -->
                        <h6 class="font-semibold mb-3">Plan de Cuotas:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha Vencimiento</th>
                                        <th class="text-end">Monto Cuota</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="cuota in venta.credito.cuotas"
                                        :key="cuota.id"
                                    >
                                        <td>{{ cuota.numero_cuota }}</td>
                                        <td>
                                            {{
                                                new Date(
                                                    cuota.fecha_vencimiento
                                                ).toLocaleDateString("es-ES")
                                            }}
                                        </td>
                                        <td class="text-end">
                                            ${{ cuota.monto.toFixed(2) }}
                                        </td>
                                        <td class="text-center">
                                            <span
                                                :class="
                                                    getEstadoBadgeClass(
                                                        cuota.estado
                                                    )
                                                "
                                            >
                                                {{ cuota.estado }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="text-center">
                    <Link
                        :href="route('dashboard')"
                        class="btn btn-primary btn-lg me-2"
                    >
                        <i class="bi bi-house-door me-2"></i>Ir al Dashboard
                    </Link>
                    <Link
                        :href="route('carritos.index')"
                        class="btn btn-outline-secondary btn-lg"
                    >
                        <i class="bi bi-cart me-2"></i>Continuar Comprando
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    venta: Object,
});

// Función para obtener clases de badge según estado
const getEstadoBadgeClass = (estado) => {
    const clases = {
        pendiente: "badge bg-warning",
        pagado: "badge bg-success",
        anulado: "badge bg-danger",
        vencido: "badge bg-danger",
    };
    return clases[estado] || "badge bg-secondary";
};
</script>
