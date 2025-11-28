<template>
    <AppLayout title="Reporte de Mora">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">Reporte de Créditos en Mora</h2>
                    <p class="text-muted mb-0">
                        Listado de créditos con días de mora y cuotas vencidas
                    </p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div
                        v-if="creditosMora.length === 0"
                        class="alert alert-info"
                    >
                        No hay créditos en mora.
                    </div>

                    <div v-else class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th># Crédito</th>
                                    <th>Cliente</th>
                                    <th>Venta</th>
                                    <th class="text-end">Monto Pendiente</th>
                                    <th class="text-center">Días Mora</th>
                                    <th>Cuotas Vencidas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="credito in creditosMora"
                                    :key="credito.id"
                                >
                                    <td>
                                        <strong>#{{ credito.id }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{
                                                credito.venta?.user?.nombre
                                            }}</strong>
                                            {{ credito.venta?.user?.apellidos }}
                                        </div>
                                        <small class="text-muted"
                                            >CI:
                                            {{ credito.venta?.user?.ci }}</small
                                        >
                                    </td>
                                    <td>
                                        <Link
                                            :href="
                                                route(
                                                    'ventas.show',
                                                    credito.venta_id
                                                )
                                            "
                                            class="text-primary"
                                            >{{
                                                credito.venta?.numero_venta
                                            }}</Link
                                        >
                                    </td>
                                    <td class="text-end">
                                        {{
                                            formatearMoneda(
                                                credito.monto_pendiente
                                            )
                                        }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger"
                                            >{{ credito.dias_mora }} días</span
                                        >
                                    </td>
                                    <td>
                                        <ul class="mb-0">
                                            <li
                                                v-for="c in credito.cuotas"
                                                :key="c.id"
                                            >
                                                Cuota #{{ c.numero_cuota }} -
                                                {{
                                                    formatearFecha(
                                                        c.fecha_vencimiento
                                                    )
                                                }}
                                                -
                                                {{
                                                    formatearMoneda(
                                                        c.monto_pendiente
                                                    )
                                                }}
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex">
                <Link :href="route('creditos.index')" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver a Créditos
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    creditosMora: Array,
});

const formatearMoneda = (valor) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
        minimumFractionDigits: 2,
    }).format(valor || 0);
};

const formatearFecha = (fecha) => {
    if (!fecha) return "-";
    return new Date(fecha).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};
</script>
