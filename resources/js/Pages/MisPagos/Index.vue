<script setup>
import { computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    pagos: Object,
    cuotasPendientes: Array,
});

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);

const getBadgeClass = (estado) => {
    const badges = {
        pendiente: "warning",
        vencida: "danger",
        pagada: "success",
    };
    return `bg-${badges[estado] || "secondary"}`;
};

const totalPendiente = computed(() => {
    return props.cuotasPendientes.reduce((sum, cuota) => {
        const montoPagado = cuota.pagos?.reduce((s, p) => s + parseFloat(p.monto), 0) || 0;
        return sum + (parseFloat(cuota.monto) - montoPagado);
    }, 0);
});

// Calcular páginas visibles
const visiblePages = computed(() => {
    const current = props.pagos.current_page;
    const last = props.pagos.last_page;
    const delta = 2;
    const range = [];
    const rangeWithDots = [];

    for (
        let i = Math.max(2, current - delta);
        i <= Math.min(last - 1, current + delta);
        i++
    ) {
        range.push(i);
    }

    if (current - delta > 2) {
        rangeWithDots.push(1, "...");
    } else {
        rangeWithDots.push(1);
    }

    rangeWithDots.push(...range);

    if (current + delta < last - 1) {
        rangeWithDots.push("...", last);
    } else if (last > 1) {
        rangeWithDots.push(last);
    }

    return rangeWithDots;
});
</script>

<template>
    <AppLayout title="Mis Pagos">
        <div class="container py-4">
            <h2 class="mb-4">Mis Pagos</h2>

            <!-- Resumen de Cuotas Pendientes -->
            <div v-if="cuotasPendientes.length > 0" class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle-fill"></i> Cuotas
                        Pendientes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-3">
                        <strong>Total Pendiente:</strong> Bs.
                        {{ formatMoney(totalPendiente) }}
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>N° Cuota</th>
                                    <th>Venta</th>
                                    <th>Fecha Vencimiento</th>
                                    <th>Monto</th>
                                    <th>Pagado</th>
                                    <th>Saldo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="cuota in cuotasPendientes"
                                    :key="cuota.id"
                                >
                                    <td>{{ cuota.numero_cuota }}</td>
                                    <td>
                                        #{{ cuota.credito?.venta?.numero_venta }}
                                    </td>
                                    <td>
                                        {{
                                            new Date(
                                                cuota.fecha_vencimiento
                                            ).toLocaleDateString("es-ES")
                                        }}
                                    </td>
                                    <td>Bs. {{ formatMoney(cuota.monto) }}</td>
                                    <td>
                                        Bs.
                                        {{
                                            formatMoney(
                                                cuota.pagos?.reduce(
                                                    (sum, p) =>
                                                        sum + parseFloat(p.monto),
                                                    0
                                                ) || 0
                                            )
                                        }}
                                    </td>
                                    <td>
                                        <strong class="text-danger">
                                            Bs.
                                            {{
                                                formatMoney(
                                                    parseFloat(cuota.monto) -
                                                        (cuota.pagos?.reduce(
                                                            (sum, p) =>
                                                                sum +
                                                                parseFloat(p.monto),
                                                            0
                                                        ) || 0)
                                                )
                                            }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span
                                            class="badge"
                                            :class="getBadgeClass(cuota.estado)"
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

            <!-- Historial de Pagos -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Historial de Pagos</h5>
                </div>
                <div class="card-body">
                    <div v-if="pagos.data.length > 0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>N° Cuota</th>
                                        <th>Venta</th>
                                        <th>Método de Pago</th>
                                        <th>Monto</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="pago in pagos.data" :key="pago.id">
                                        <td>
                                            {{
                                                new Date(
                                                    pago.fecha
                                                ).toLocaleDateString("es-ES", {
                                                    day: "2-digit",
                                                    month: "short",
                                                    year: "numeric",
                                                })
                                            }}
                                        </td>
                                        <td>{{ pago.cuota?.numero_cuota }}</td>
                                        <td>
                                            <Link
                                                :href="
                                                    route(
                                                        'mis-pedidos.show',
                                                        pago.cuota?.credito?.venta
                                                            ?.id
                                                    )
                                                "
                                                class="text-decoration-none"
                                            >
                                                #{{
                                                    pago.cuota?.credito?.venta
                                                        ?.numero_venta
                                                }}
                                            </Link>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{
                                                    pago.metodo_pago?.nombre ||
                                                    "Efectivo"
                                                }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong
                                                >Bs.
                                                {{ formatMoney(pago.monto) }}</strong
                                            >
                                        </td>
                                        <td>
                                            <small class="text-muted">{{
                                                pago.observaciones || "-"
                                            }}</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <nav v-if="pagos.last_page > 1" class="mt-3">
                            <ul class="pagination justify-content-center">
                                <li
                                    class="page-item"
                                    :class="{ disabled: !pagos.prev_page_url }"
                                >
                                    <Link
                                        class="page-link"
                                        :href="pagos.prev_page_url || '#'"
                                        :disabled="!pagos.prev_page_url"
                                    >
                                        Anterior
                                    </Link>
                                </li>

                                <li
                                    v-for="page in visiblePages"
                                    :key="page"
                                    class="page-item"
                                    :class="{
                                        active: page === pagos.current_page,
                                        disabled: page === '...',
                                    }"
                                >
                                    <Link
                                        v-if="page !== '...'"
                                        class="page-link"
                                        :href="pagos.links[page]?.url || '#'"
                                    >
                                        {{ page }}
                                    </Link>
                                    <span v-else class="page-link">...</span>
                                </li>

                                <li
                                    class="page-item"
                                    :class="{ disabled: !pagos.next_page_url }"
                                >
                                    <Link
                                        class="page-link"
                                        :href="pagos.next_page_url || '#'"
                                        :disabled="!pagos.next_page_url"
                                    >
                                        Siguiente
                                    </Link>
                                </li>
                            </ul>
                        </nav>
                    </div>

                    <!-- Sin pagos -->
                    <div v-else class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No has realizado
                        ningún pago todavía.
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
