<template>
    <AppLayout title="Gestión de Pagos">
        <div class="container py-4">
            <!-- Encabezado -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Gestión de Pagos</h2>
                    <p class="text-muted mb-0">
                        Historial de pagos registrados en el sistema
                    </p>
                </div>
            </div>

            <!-- Indicadores -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="bg-primary bg-opacity-10 rounded p-3"
                                    >
                                        <i
                                            class="bi bi-receipt text-primary"
                                            style="font-size: 1.5rem"
                                        ></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-muted small">
                                        Total Pagos
                                    </div>
                                    <h4 class="mb-0">
                                        {{ estadisticas.total_pagos }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="bg-success bg-opacity-10 rounded p-3"
                                    >
                                        <i
                                            class="bi bi-cash-stack text-success"
                                            style="font-size: 1.5rem"
                                        ></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-muted small">
                                        Total Recaudado
                                    </div>
                                    <h4 class="mb-0">
                                        Bs.
                                        {{
                                            formatMoney(
                                                estadisticas.total_monto
                                            )
                                        }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="bg-info bg-opacity-10 rounded p-3"
                                    >
                                        <i
                                            class="bi bi-calendar-month text-info"
                                            style="font-size: 1.5rem"
                                        ></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="text-muted small">
                                        Pagos del Mes
                                    </div>
                                    <h4 class="mb-0">
                                        Bs.
                                        {{
                                            formatMoney(estadisticas.pagos_mes)
                                        }}
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold"
                                >Buscar</label
                            >
                            <input
                                type="text"
                                class="form-control"
                                v-model="form.buscar"
                                @input="buscarConRetraso"
                                placeholder="Cliente o CI..."
                            />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold"
                                >Fecha Desde</label
                            >
                            <input
                                type="date"
                                class="form-control"
                                v-model="form.fecha_desde"
                                @change="aplicarFiltros"
                            />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold"
                                >Fecha Hasta</label
                            >
                            <input
                                type="date"
                                class="form-control"
                                v-model="form.fecha_hasta"
                                @change="aplicarFiltros"
                            />
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button
                                @click="limpiarFiltros"
                                class="btn btn-outline-secondary w-100"
                            >
                                <i class="bi bi-x-circle me-1"></i>
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Pagos -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Listado de Pagos</h5>
                </div>
                <div class="card-body p-0">
                    <div
                        v-if="pagos.data.length === 0"
                        class="text-center text-muted py-5"
                    >
                        <i class="bi bi-inbox" style="font-size: 3rem"></i>
                        <p class="mt-3 mb-0">No hay pagos registrados</p>
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Crédito</th>
                                    <th>Cuota</th>
                                    <th>Monto</th>
                                    <th>Método Pago</th>
                                    <th class="text-center pe-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="pago in pagos.data" :key="pago.id">
                                    <td class="ps-3">
                                        <span class="badge bg-secondary">{{
                                            pago.id
                                        }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{
                                            formatDate(pago.fecha)
                                        }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">
                                            {{
                                                pago.cuota?.credito?.venta
                                                    ?.user?.name || "N/A"
                                            }}
                                        </div>
                                        <small class="text-muted">
                                            CI:
                                            {{
                                                pago.cuota?.credito?.venta
                                                    ?.user?.ci || "N/A"
                                            }}
                                        </small>
                                    </td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ pago.cuota?.numero_cuota }}/{{
                                                pago.cuota?.credito
                                                    ?.cuotas_total
                                            }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">
                                            Bs. {{ formatMoney(pago.monto) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            {{
                                                pago.metodo_pago?.nombre ||
                                                "Efectivo"
                                            }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-3">
                                        <Link
                                            :href="route('pagos.show', pago.id)"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Ver detalles"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Paginación -->
                <div v-if="pagos.data.length > 0" class="card-footer bg-white">
                    <div
                        class="d-flex justify-content-between align-items-center"
                    >
                        <div class="text-muted small">
                            Mostrando {{ pagos.from }} a {{ pagos.to }} de
                            {{ pagos.total }} pagos
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <li
                                    class="page-item"
                                    :class="{ disabled: !pagos.prev_page_url }"
                                >
                                    <Link
                                        class="page-link"
                                        :href="pagos.prev_page_url || '#'"
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
                                        active: page === pagos.current_page,
                                    }"
                                >
                                    <Link
                                        class="page-link"
                                        :href="pagos.path + '?page=' + page"
                                        preserve-state
                                    >
                                        {{ page }}
                                    </Link>
                                </li>

                                <li
                                    class="page-item"
                                    :class="{ disabled: !pagos.next_page_url }"
                                >
                                    <Link
                                        class="page-link"
                                        :href="pagos.next_page_url || '#'"
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
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    pagos: Object,
    filters: Object,
    estadisticas: Object,
});

const form = ref({
    buscar: props.filters?.buscar || "",
    fecha_desde: props.filters?.fecha_desde || "",
    fecha_hasta: props.filters?.fecha_hasta || "",
});

let buscarTimeout = null;

const buscarConRetraso = () => {
    clearTimeout(buscarTimeout);
    buscarTimeout = setTimeout(() => {
        aplicarFiltros();
    }, 500);
};

const aplicarFiltros = () => {
    router.get(
        route("pagos.index"),
        {
            buscar: form.value.buscar || undefined,
            fecha_desde: form.value.fecha_desde || undefined,
            fecha_hasta: form.value.fecha_hasta || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const limpiarFiltros = () => {
    form.value = {
        buscar: "",
        fecha_desde: "",
        fecha_hasta: "",
    };
    router.get(route("pagos.index"));
};

const paginasVisibles = computed(() => {
    const paginas = [];
    const total = props.pagos.last_page;
    const actual = props.pagos.current_page;
    const rango = 2;

    let inicio = Math.max(1, actual - rango);
    let fin = Math.min(total, actual + rango);

    if (inicio > 1) {
        paginas.push(1);
        if (inicio > 2) paginas.push("...");
    }

    for (let i = inicio; i <= fin; i++) {
        paginas.push(i);
    }

    if (fin < total) {
        if (fin < total - 1) paginas.push("...");
        paginas.push(total);
    }

    return paginas;
});

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);
const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    });
};
</script>

<style scoped>
.table > :not(caption) > * > * {
    padding: 0.75rem;
}

.page-link {
    color: #0d6efd;
}

.page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
