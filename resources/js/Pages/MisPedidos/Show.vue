<script setup>
import { computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    pedido: Object,
});

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);

const getBadgeClass = (estado) => {
    const badges = {
        pendiente: "warning",
        completada: "success",
        pagado: "success",
        anulada: "danger",
        cancelada: "secondary",
    };
    return `bg-${badges[estado] || "secondary"}`;
};

const totalProductos = computed(() => {
    return props.pedido.detalles.reduce(
        (sum, detalle) => sum + detalle.cantidad,
        0
    );
});
</script>

<template>
    <AppLayout title="Detalle de Pedido">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Detalle del Pedido #{{ pedido.numero_venta }}</h2>
                <Link
                    :href="route('mis-pedidos.index')"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left"></i> Volver a Mis Pedidos
                </Link>
            </div>

            <div class="row g-4">
                <!-- Información del Pedido -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Información General</h5>
                        </div>
                        <div class="card-body">
                            <p>
                                <strong>Número:</strong> #{{
                                    pedido.numero_venta
                                }}
                            </p>
                            <p>
                                <strong>Fecha:</strong>
                                {{
                                    new Date(
                                        pedido.created_at
                                    ).toLocaleDateString("es-ES", {
                                        day: "2-digit",
                                        month: "long",
                                        year: "numeric",
                                        hour: "2-digit",
                                        minute: "2-digit",
                                    })
                                }}
                            </p>
                            <p>
                                <strong>Estado:</strong>
                                <span
                                    class="badge ms-2"
                                    :class="getBadgeClass(pedido.estado)"
                                >
                                    {{ pedido.estado }}
                                </span>
                            </p>
                            <p>
                                <strong>Método de Pago:</strong>
                                <span
                                    class="badge ms-2"
                                    :class="
                                        pedido.metodo_pago === 'credito'
                                            ? 'bg-info'
                                            : 'bg-primary'
                                    "
                                >
                                    {{
                                        pedido.metodo_pago === "credito"
                                            ? "Crédito"
                                            : "Contado"
                                    }}
                                </span>
                            </p>
                            <p v-if="pedido.vendedor">
                                <strong>Atendido por:</strong>
                                {{ pedido.vendedor.nombre }}
                                {{ pedido.vendedor.apellidos }}
                            </p>
                        </div>
                    </div>

                    <!-- Información de Crédito -->
                    <div v-if="pedido.credito" class="card mt-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Información de Crédito</h5>
                        </div>
                        <div class="card-body">
                            <p>
                                <strong>Monto Total:</strong> Bs.
                                {{ formatMoney(pedido.credito.monto_total) }}
                            </p>
                            <p>
                                <strong>Saldo Pendiente:</strong>
                                <span class="text-danger"
                                    >Bs.
                                    {{
                                        formatMoney(
                                            pedido.credito.monto_pendiente
                                        )
                                    }}</span
                                >
                            </p>
                            <p>
                                <strong>Cuotas:</strong>
                                {{ pedido.credito.numero_cuotas }}
                            </p>
                            <p>
                                <strong>Estado:</strong>
                                <span
                                    class="badge"
                                    :class="
                                        getBadgeClass(pedido.credito.estado)
                                    "
                                >
                                    {{ pedido.credito.estado }}
                                </span>
                            </p>

                            <!-- Listado de Cuotas -->
                            <div v-if="pedido.credito.cuotas" class="mt-3">
                                <h6>Cuotas:</h6>
                                <ul class="list-group list-group-flush">
                                    <li
                                        v-for="cuota in pedido.credito.cuotas"
                                        :key="cuota.id"
                                        class="list-group-item px-0"
                                    >
                                        <div
                                            class="d-flex justify-content-between"
                                        >
                                            <span>
                                                Cuota {{ cuota.numero_cuota }}
                                                <small class="text-muted">
                                                    ({{
                                                        new Date(
                                                            cuota.fecha_vencimiento
                                                        ).toLocaleDateString(
                                                            "es-ES"
                                                        )
                                                    }})
                                                </small>
                                            </span>
                                            <strong
                                                >Bs.
                                                {{
                                                    formatMoney(cuota.monto)
                                                }}</strong
                                            >
                                        </div>
                                        <span
                                            class="badge"
                                            :class="getBadgeClass(cuota.estado)"
                                        >
                                            {{ cuota.estado }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos del Pedido -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                Productos ({{ totalProductos }} items)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Código</th>
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
                                            v-for="detalle in pedido.detalles"
                                            :key="detalle.id"
                                        >
                                            <td>
                                                <strong>{{
                                                    detalle.producto.nombre
                                                }}</strong>
                                            </td>
                                            <td>
                                                <code>{{
                                                    detalle.producto.codigo
                                                }}</code>
                                            </td>
                                            <td class="text-center">
                                                {{ detalle.cantidad }}
                                            </td>
                                            <td class="text-end">
                                                Bs.
                                                {{
                                                    formatMoney(detalle.precio)
                                                }}
                                            </td>
                                            <td class="text-end">
                                                <strong
                                                    >Bs.
                                                    {{
                                                        formatMoney(
                                                            detalle.subtotal
                                                        )
                                                    }}</strong
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-end">
                                                <strong>TOTAL:</strong>
                                            </td>
                                            <td class="text-end">
                                                <h4 class="mb-0 text-primary">
                                                    Bs.
                                                    {{
                                                        formatMoney(
                                                            pedido.total
                                                        )
                                                    }}
                                                </h4>
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
