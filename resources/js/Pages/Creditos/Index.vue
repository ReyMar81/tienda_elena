<template>
    <AppLayout title="Gestión de Créditos">
        <div class="container py-4">
            <div class="row mb-4">
                <div class="col-lg-8">
                    <h2 class="mb-0">
                        <i class="bi bi-credit-card me-2"></i>Gestión de
                        Créditos
                    </h2>
                    <p class="text-muted">
                        Administra y visualiza los créditos otorgados a clientes
                    </p>
                </div>
                <div class="col-lg-4 text-end">
                    <!-- Espacio para acciones futuras -->
                </div>
            </div>
            <!-- Mensajes de éxito/error -->
            <div
                v-if="$page.props.flash?.success"
                class="alert alert-success alert-dismissible fade show mb-4"
                role="alert"
            >
                <i class="bi bi-check-circle me-2"></i>
                {{ $page.props.flash.success }}
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>
            </div>
            <div
                v-if="$page.props.errors?.error"
                class="alert alert-danger alert-dismissible fade show mb-4"
                role="alert"
            >
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ $page.props.errors.error }}
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>
            </div>

            <!-- Indicadores -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="bg-white bg-opacity-10 rounded p-3"
                                    >
                                        <i
                                            class="bi bi-collection text-white"
                                            style="font-size: 1.5rem"
                                        ></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-white small">
                                        Total Créditos
                                    </div>
                                    <h3 class="mb-0">
                                        {{ indicadores.total_creditos }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="bg-white bg-opacity-10 rounded p-3"
                                    >
                                        <i
                                            class="bi bi-cash-coin text-warning"
                                            style="font-size: 1.5rem"
                                        ></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-dark small">
                                        Total Pendiente
                                    </div>
                                    <h3 class="mb-0">
                                        {{
                                            formatearMoneda(
                                                indicadores.total_pendiente
                                            )
                                        }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="bg-white bg-opacity-10 rounded p-3"
                                    >
                                        <i
                                            class="bi bi-exclamation-octagon text-danger"
                                            style="font-size: 1.5rem"
                                        ></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-white small">
                                        Créditos en Mora
                                    </div>
                                    <h3 class="mb-0">
                                        {{ indicadores.total_mora }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-dark text-white">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="bg-white bg-opacity-10 rounded p-3"
                                    >
                                        <i
                                            class="bi bi-currency-exchange text-white"
                                            style="font-size: 1.5rem"
                                        ></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-white small">
                                        Monto en Mora
                                    </div>
                                    <h3 class="mb-0">
                                        {{
                                            formatearMoneda(
                                                indicadores.monto_mora
                                            )
                                        }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form @submit.prevent="filtrar" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Buscar</label>
                            <input
                                v-model="form.search"
                                type="text"
                                class="form-control"
                                placeholder="Cliente, CI, N° Venta..."
                            />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select v-model="form.estado" class="form-select">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="pagado">Pagado</option>
                                <option value="vencido">Vencido</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                            <button
                                type="button"
                                @click="limpiarFiltros"
                                class="btn btn-secondary"
                            >
                                <i class="bi bi-x-circle"></i> Limpiar
                            </button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <Link
                                :href="route('creditos.reporte-mora')"
                                class="btn btn-danger w-100"
                            >
                                <i class="bi bi-file-earmark-text"></i>
                                Reporte Mora
                            </Link>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de Créditos -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>N° Crédito</th>
                                    <th>Cliente</th>
                                    <th>Venta</th>
                                    <th class="text-end">Monto Total</th>
                                    <th class="text-center">Cuotas</th>
                                    <th class="text-end">Pagado</th>
                                    <th class="text-end">Pendiente</th>
                                    <th class="text-center">Mora</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="creditos.data.length === 0">
                                    <td
                                        colspan="10"
                                        class="text-center text-muted py-4"
                                    >
                                        <i
                                            class="bi bi-inbox fs-1 d-block mb-2"
                                        ></i>
                                        No se encontraron créditos
                                    </td>
                                </tr>
                                <tr
                                    v-for="credito in creditos.data"
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
                                        >
                                            {{ credito.venta?.numero_venta }}
                                        </Link>
                                    </td>
                                    <td class="text-end">
                                        {{
                                            formatearMoneda(
                                                credito.monto_credito
                                            )
                                        }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">
                                            {{ cuotasPagadas(credito) }}/{{
                                                credito.cuotas_total
                                            }}
                                        </span>
                                    </td>
                                    <td class="text-end text-success">
                                        {{
                                            formatearMoneda(
                                                credito.monto_pagado
                                            )
                                        }}
                                    </td>
                                    <td class="text-end text-danger">
                                        {{
                                            formatearMoneda(
                                                credito.monto_pendiente
                                            )
                                        }}
                                    </td>
                                    <td class="text-center">
                                        <span
                                            v-if="credito.dias_mora > 0"
                                            class="badge bg-danger"
                                        >
                                            {{ credito.dias_mora }} días
                                        </span>
                                        <span v-else class="text-muted">-</span>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            :class="
                                                getEstadoBadge(credito.estado)
                                            "
                                        >
                                            {{ credito.estado.toUpperCase() }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <Link
                                                :href="
                                                    route(
                                                        'creditos.show',
                                                        credito.id
                                                    )
                                                "
                                                class="btn btn-outline-info"
                                                title="Ver detalles"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </Link>
                                            <button
                                                v-if="
                                                    credito.estado !== 'pagado'
                                                "
                                                @click="abrirModalPago(credito)"
                                                class="btn btn-outline-success"
                                                title="Registrar pago"
                                            >
                                                <i class="bi bi-cash-coin"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="creditos.last_page > 1" class="mt-4">
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li
                                    class="page-item"
                                    :class="{
                                        disabled: !creditos.prev_page_url,
                                    }"
                                >
                                    <Link
                                        class="page-link"
                                        :href="creditos.prev_page_url || '#'"
                                        preserve-state
                                    >
                                        Anterior
                                    </Link>
                                </li>
                                <li
                                    v-for="page in paginasVisibles"
                                    :key="page"
                                    class="page-item"
                                    :class="{
                                        active: page === creditos.current_page,
                                    }"
                                >
                                    <Link
                                        v-if="page !== '...'"
                                        class="page-link"
                                        :href="creditos.links[page]?.url || '#'"
                                        preserve-state
                                    >
                                        {{ page }}
                                    </Link>
                                    <span v-else class="page-link">...</span>
                                </li>
                                <li
                                    class="page-item"
                                    :class="{
                                        disabled: !creditos.next_page_url,
                                    }"
                                >
                                    <Link
                                        class="page-link"
                                        :href="creditos.next_page_url || '#'"
                                        preserve-state
                                    >
                                        Siguiente
                                    </Link>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Pago -->
        <PagoModal
            :show="mostrarModalPago"
            :cuotas="cuotasCredito"
            :metodosPago="metodosPago"
            @close="cerrarModalPago"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PagoModal from "@/Components/PagoModal.vue";

const props = defineProps({
    creditos: Object,
    indicadores: Object,
    filtros: Object,
    metodosPago: Array,
});

const form = ref({
    search: props.filtros?.search || "",
    estado: props.filtros?.estado || "",
});

const mostrarModalPago = ref(false);
const cuotasCredito = ref([]);

const abrirModalPago = (credito) => {
    cuotasCredito.value = credito.cuotas || [];
    mostrarModalPago.value = true;
};

const cerrarModalPago = () => {
    mostrarModalPago.value = false;
    cuotasCredito.value = [];
};

const formatearMoneda = (valor) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
        minimumFractionDigits: 2,
    }).format(valor);
};

const getEstadoBadge = (estado) => {
    const badges = {
        pendiente: "badge bg-warning",
        pagado: "badge bg-success",
        vencido: "badge bg-danger",
    };
    return badges[estado] || "badge bg-secondary";
};

const cuotasPagadas = (credito) => {
    return credito.cuotas?.filter((c) => c.estado === "pagada").length || 0;
};

const filtrar = () => {
    router.get(route("creditos.index"), form.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const limpiarFiltros = () => {
    form.value = {
        search: "",
        estado: "",
    };
    filtrar();
};

const paginasVisibles = computed(() => {
    const current = props.creditos.current_page;
    const last = props.creditos.last_page;
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
