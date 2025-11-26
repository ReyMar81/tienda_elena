<template>
    <AppLayout title="Mis Créditos">
        <FlashNotification />
        <div class="container py-4">
            <div class="row mb-4">
                <div class="col-lg-8">
                    <h2 class="mb-0">
                        <i class="bi bi-credit-card me-2"></i>Mis Créditos
                    </h2>
                    <p class="text-muted">
                        Consulta y gestiona tus créditos personales
                    </p>
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
                                placeholder="N° Venta..."
                            />
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select
                                v-model="form.estado"
                                class="form-select"
                            >
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="pagado">Pagado</option>
                                <option value="vencido">Vencido</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button
                                type="submit"
                                class="btn btn-primary me-2"
                            >
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
                                    <td colspan="9" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No se encontraron créditos
                                    </td>
                                </tr>
                                <tr v-for="credito in creditos.data" :key="credito.id">
                                    <td>
                                        <strong>#{{ credito.id }}</strong>
                                    </td>
                                    <td>
                                        <Link
                                            :href="route('ventas.show', credito.venta_id)"
                                            class="text-primary"
                                        >
                                            {{ credito.venta?.numero_venta }}
                                        </Link>
                                    </td>
                                    <td class="text-end">
                                        {{ formatearMoneda(credito.monto_credito) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">
                                            {{ cuotasPagadas(credito) }}/{{ credito.cuotas_total }}
                                        </span>
                                    </td>
                                    <td class="text-end text-success">
                                        {{ formatearMoneda(credito.monto_pagado) }}
                                    </td>
                                    <td class="text-end text-danger">
                                        {{ formatearMoneda(credito.monto_pendiente) }}
                                    </td>
                                    <td class="text-center">
                                        <span v-if="credito.dias_mora > 0" class="badge bg-danger">
                                            {{ credito.dias_mora }} días
                                        </span>
                                        <span v-else class="text-muted">-</span>
                                    </td>
                                    <td class="text-center">
                                        <span :class="getEstadoBadge(credito.estado)">
                                            {{ credito.estado.toUpperCase() }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <Link
                                                :href="route('mis-creditos.show', credito.id)"
                                                class="btn btn-outline-info"
                                                title="Ver detalles"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </Link>
                                            <button
                                                class="btn btn-outline-success"
                                                @click="abrirModalPago(credito)"
                                                title="Pagar por QR"
                                            >
                                                <i class="bi bi-qr-code"></i>
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
                                    :class="{ disabled: !creditos.prev_page_url }"
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
                                    :class="{ active: page === creditos.current_page }"
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
                                    :class="{ disabled: !creditos.next_page_url }"
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

            <!-- Modal de Pago QR -->
            <div
                class="modal fade"
                tabindex="-1"
                v-if="mostrarModalPago"
            >
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-qr-code me-2"></i>Registrar Pago por QR
                            </h5>
                            <button
                                type="button"
                                class="btn-close"
                                @click="cerrarModalPago"
                                aria-label="Close"
                            ></button>
                        </div>
                        <div class="modal-body">
                            <div v-if="creditoSeleccionado" class="mb-4">
                                <h6>
                                    Crédito #{{ creditoSeleccionado.id }}
                                    <span
                                        v-if="cuotaSeleccionada"
                                        class="badge bg-info ms-2"
                                    >
                                        Cuota #{{ cuotaSeleccionada.numero_cuota }}
                                    </span>
                                </h6>
                                <p>
                                    <strong>Venta:</strong> {{ creditoSeleccionado.venta?.numero_venta }}<br />
                                    <strong>Monto Total:</strong> {{ formatearMoneda(creditoSeleccionado.monto_credito) }}<br />
                                    <strong>Pagado:</strong> {{ formatearMoneda(creditoSeleccionado.monto_pagado) }}<br />
                                    <strong>Pendiente:</strong> {{ formatearMoneda(creditoSeleccionado.monto_pendiente) }}
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Seleccionar Cuota</label>
                                <select
                                    v-model="cuotaSeleccionadaId"
                                    class="form-select"
                                    @change="seleccionarCuota($event.target.value)"
                                >
                                    <option value="">Seleccione una cuota</option>
                                    <option
                                        v-for="cuota in cuotasDisponibles"
                                        :key="cuota.id"
                                        :value="cuota.id"
                                    >
                                        Cuota #{{ cuota.numero_cuota }} - {{ formatearMoneda(cuota.monto_pendiente) }}
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Monto a Pagar</label>
                                <input
                                    v-model.number="montoPago"
                                    type="number"
                                    class="form-control"
                                    placeholder="Ingrese el monto"
                                    min="0.01"
                                    step="0.01"
                                />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Fecha de Pago</label>
                                <input
                                    v-model="fechaPago"
                                    type="date"
                                    class="form-control"
                                />
                            </div>

                            <div v-if="cargandoPago" class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-secondary"
                                @click="cerrarModalPago"
                            >
                                <i class="bi bi-x-circle"></i> Cancelar
                            </button>
                            <button
                                type="button"
                                class="btn btn-primary"
                                @click="pagarPorQR"
                                :disabled="cargandoPago"
                            >
                                <i class="bi bi-check-circle"></i> Registrar Pago
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- Modal de Pago por QR -->
    <template v-if="mostrarModalPago">
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.3)">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-qr-code me-2"></i>Pagar cuota por QR</h5>
                        <button type="button" class="btn-close" @click="cerrarModalPago"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Seleccionar cuota</label>
                            <select class="form-select" v-model="cuotaSeleccionadaId" @change="seleccionarCuota($event.target.value)">
                                <option value="">-- Seleccione una cuota --</option>
                                <option v-for="cuota in cuotasDisponibles" :key="cuota.id" :value="cuota.id">
                                    Cuota #{{ cuota.numero_cuota }} - Pendiente: {{ formatearMoneda(cuota.monto_pendiente) }}
                                </option>
                            </select>
                        </div>
                        <div v-if="cuotaSeleccionada">
                            <div class="mb-3">
                                <label class="form-label">Monto a pagar</label>
                                <input type="number" class="form-control" v-model="montoPago" :max="cuotaSeleccionada.monto_pendiente" min="0.01" step="0.01" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fecha de pago</label>
                                <input type="date" class="form-control" v-model="fechaPago" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Método de pago</label>
                                <input type="text" class="form-control" value="QR" disabled />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="cerrarModalPago" :disabled="cargandoPago">Cancelar</button>
                        <button type="button" class="btn btn-success" :disabled="!cuotaSeleccionada || !montoPago || montoPago < 0.01 || cargandoPago" @click="pagarPorQR">
                            <span v-if="cargandoPago" class="spinner-border spinner-border-sm me-2"></span>
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
    creditos: Object,
    filtros: Object,
});

const form = ref({
    search: props.filtros?.search || "",
    estado: props.filtros?.estado || "",
});

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
    return credito.cuotas?.filter((c) => c.estado === "pagado").length || 0;
};

const filtrar = () => {
    router.get(route("mis-creditos.index"), form.value, {
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

// --- Estado y lógica para el modal de pago QR ---
const mostrarModalPago = ref(false);
const creditoSeleccionado = ref(null);
const cuotaSeleccionada = ref(null);
const montoPago = ref(0);
const fechaPago = ref(new Date().toISOString().slice(0, 10));
const cuotaSeleccionadaId = ref("");
const cargandoPago = ref(false);

const abrirModalPago = (credito) => {
    creditoSeleccionado.value = credito;
    cuotaSeleccionada.value = null;
    cuotaSeleccionadaId.value = "";
    montoPago.value = 0;
    fechaPago.value = new Date().toISOString().slice(0, 10);
    mostrarModalPago.value = true;
};

const cerrarModalPago = () => {
    mostrarModalPago.value = false;
    creditoSeleccionado.value = null;
    cuotaSeleccionada.value = null;
    cuotaSeleccionadaId.value = "";
    montoPago.value = 0;
};

const cuotasDisponibles = computed(() => {
    if (!creditoSeleccionado.value) return [];
    return creditoSeleccionado.value.cuotas?.filter(c => c.estado !== 'pagado') || [];
});

const seleccionarCuota = (cuotaId) => {
    const cuota = cuotasDisponibles.value.find(c => c.id === parseInt(cuotaId));
    cuotaSeleccionada.value = cuota;
    montoPago.value = cuota ? cuota.monto_pendiente : 0;
};

const pagarPorQR = () => {
    if (!cuotaSeleccionada.value || !montoPago.value || montoPago.value < 0.01) return;
    cargandoPago.value = true;
    router.post(route('mis-creditos.registrar-pago'), {
        cuota_id: cuotaSeleccionada.value.id,
        monto: montoPago.value,
        fecha: fechaPago.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            cerrarModalPago();
        },
        onFinish: () => {
            cargandoPago.value = false;
        }
    });
};
</script>
