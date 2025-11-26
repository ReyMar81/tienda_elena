<script setup>
import { ref, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router } from "@inertiajs/vue3";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import CreditoModal from "@/Components/CreditoModal.vue";

const props = defineProps({
    pedidos: Object,
    filtro: {
        type: String,
        default: "pendiente",
    },
});

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);

const getBadgeClass = (estado) => {
    const badges = {
        pendiente: "warning",
        pagado: "success",
        anulado: "danger",
    };
    return `bg-${badges[estado] || "secondary"}`;
};

// Modales de confirmación
const showConfirmModal = ref(false);
const showCancelModal = ref(false);
const mostrarModalCredito = ref(false);
const pedidoSeleccionado = ref(null);

const confirmarPedido = (pedido) => {
    pedidoSeleccionado.value = pedido;
    showConfirmModal.value = true;
};

const ejecutarConfirmacion = () => {
    if (!pedidoSeleccionado.value) return;

    const esCredito = pedidoSeleccionado.value.tipo_pago === "credito";

    // Si es crédito, mostrar modal para configurar cuotas
    if (esCredito) {
        mostrarModalCredito.value = true;
        return;
    }

    // Si es al contado, confirmar directamente
    router.patch(
        route("pedidos.accion", pedidoSeleccionado.value.id),
        {
            accion: "confirmar",
        },
        {
            onFinish: () => {
                cerrarModales();
            },
        }
    );
};

const confirmarConCuotas = (numeroCuotas) => {
    router.patch(
        route("pedidos.accion", pedidoSeleccionado.value.id),
        {
            accion: "confirmar",
            numero_cuotas: numeroCuotas,
        },
        {
            onFinish: () => {
                cerrarModales();
                mostrarModalCredito.value = false;
            },
        }
    );
};

const cancelarPedido = (pedido) => {
    pedidoSeleccionado.value = pedido;
    showCancelModal.value = true;
};

const ejecutarCancelacion = () => {
    if (!pedidoSeleccionado.value) return;

    router.patch(
        route("pedidos.accion", pedidoSeleccionado.value.id),
        {
            accion: "cancelar",
        },
        {
            onFinish: () => {
                cerrarModales();
            },
        }
    );
};

const cerrarModales = () => {
    showConfirmModal.value = false;
    showCancelModal.value = false;
    pedidoSeleccionado.value = null;
};

// Cambiar filtro de pestañas
const cambiarFiltro = (estado) => {
    router.get(
        route("pedidos.index"),
        { estado },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

// Calcular páginas visibles
const visiblePages = computed(() => {
    const current = props.pedidos.current_page;
    const last = props.pedidos.last_page;
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
    <AppLayout title="Gestión de Pedidos">
        <div class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>Gestión de Pedidos</h2>
                    <p class="text-muted mb-0">
                        Administra los pedidos realizados por clientes
                    </p>
                </div>
                <Link :href="route('pedidos.create')" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Nuevo Pedido
                </Link>
            </div>

            <!-- Pestañas de filtro -->
            <ul class="nav nav-pills mb-4">
                <li class="nav-item">
                    <button
                        class="nav-link"
                        :class="{ active: filtro === 'pendiente' }"
                        @click="cambiarFiltro('pendiente')"
                    >
                        <i class="bi bi-clock-history me-2"></i>
                        Pendientes
                    </button>
                </li>
                <li class="nav-item">
                    <button
                        class="nav-link"
                        :class="{ active: filtro === 'anulado' }"
                        @click="cambiarFiltro('anulado')"
                    >
                        <i class="bi bi-x-circle me-2"></i>
                        Anulados
                    </button>
                </li>
            </ul>

            <!-- Lista de Pedidos -->
            <div v-if="pedidos.data.length > 0" class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>N° Pedido</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Método de Pago</th>
                                    <th>Tipo de Pago</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="pedido in pedidos.data"
                                    :key="pedido.id"
                                >
                                    <td>
                                        <strong>#{{ pedido.id }}</strong>
                                    </td>
                                    <td>
                                        {{
                                            new Date(
                                                pedido.created_at
                                            ).toLocaleDateString("es-ES", {
                                                day: "2-digit",
                                                month: "short",
                                                year: "numeric",
                                                hour: "2-digit",
                                                minute: "2-digit",
                                            })
                                        }}
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{
                                                pedido.user?.nombre
                                            }}</strong>
                                            {{ pedido.user?.apellidos }}
                                        </div>
                                        <small class="text-muted">{{
                                            pedido.user?.email
                                        }}</small>
                                    </td>
                                    <td>
                                        <span
                                            class="badge"
                                            :class="
                                                (
                                                    pedido.metodo_pago
                                                        ?.nombre ||
                                                    pedido.metodoPago?.nombre ||
                                                    ''
                                                )?.toLowerCase() === 'crédito'
                                                    ? 'bg-info'
                                                    : 'bg-primary'
                                            "
                                        >
                                            {{
                                                pedido.metodo_pago?.nombre ||
                                                pedido.metodoPago?.nombre ||
                                                "N/A"
                                            }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            :class="
                                                pedido.tipo_pago === 'credito'
                                                    ? 'badge bg-warning'
                                                    : 'badge bg-success'
                                            "
                                        >
                                            {{
                                                pedido.tipo_pago === "credito"
                                                    ? "A Crédito"
                                                    : "Al Contado"
                                            }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong
                                            >Bs.
                                            {{
                                                formatMoney(pedido.total)
                                            }}</strong
                                        >
                                    </td>
                                    <td>
                                        <span
                                            class="badge"
                                            :class="
                                                getBadgeClass(pedido.estado)
                                            "
                                        >
                                            {{ pedido.estado }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <Link
                                                :href="
                                                    route(
                                                        'pedidos.show',
                                                        pedido.id
                                                    )
                                                "
                                                class="btn btn-outline-info"
                                                title="Ver detalles"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </Link>

                                            <Link
                                                v-if="
                                                    pedido.estado ===
                                                    'pendiente'
                                                "
                                                :href="
                                                    route(
                                                        'pedidos.edit',
                                                        pedido.id
                                                    )
                                                "
                                                class="btn btn-outline-warning"
                                                title="Editar pedido"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </Link>

                                            <button
                                                v-if="
                                                    pedido.estado ===
                                                    'pendiente'
                                                "
                                                @click="confirmarPedido(pedido)"
                                                class="btn btn-outline-success btn-sm"
                                                title="Confirmar pedido"
                                                type="button"
                                            >
                                                <i
                                                    class="bi bi-check-circle"
                                                ></i>
                                                Confirmar
                                            </button>
                                            <button
                                                v-if="
                                                    pedido.estado ===
                                                    'pendiente'
                                                "
                                                @click="cancelarPedido(pedido)"
                                                class="btn btn-outline-danger btn-sm"
                                                title="Cancelar pedido"
                                                type="button"
                                            >
                                                <i class="bi bi-x-circle"></i>
                                                Cancelar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <nav v-if="pedidos.last_page > 1" class="mt-3">
                        <ul class="pagination justify-content-center">
                            <li
                                class="page-item"
                                :class="{ disabled: !pedidos.prev_page_url }"
                            >
                                <Link
                                    class="page-link"
                                    :href="pedidos.prev_page_url || '#'"
                                    :disabled="!pedidos.prev_page_url"
                                >
                                    Anterior
                                </Link>
                            </li>

                            <li
                                v-for="page in visiblePages"
                                :key="page"
                                class="page-item"
                                :class="{
                                    active: page === pedidos.current_page,
                                    disabled: page === '...',
                                }"
                            >
                                <Link
                                    v-if="page !== '...'"
                                    class="page-link"
                                    :href="pedidos.links[page]?.url || '#'"
                                >
                                    {{ page }}
                                </Link>
                                <span v-else class="page-link">...</span>
                            </li>

                            <li
                                class="page-item"
                                :class="{ disabled: !pedidos.next_page_url }"
                            >
                                <Link
                                    class="page-link"
                                    :href="pedidos.next_page_url || '#'"
                                    :disabled="!pedidos.next_page_url"
                                >
                                    Siguiente
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <!-- Sin pedidos -->
            <div v-else class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                No hay pedidos
                {{ filtro === "pendiente" ? "pendientes" : "anulados" }}.
            </div>
        </div>

        <!-- Modal Confirmar -->
        <ConfirmationModal
            :show="showConfirmModal"
            @close="cerrarModales"
            max-width="sm"
        >
            <template #title> Confirmar Pedido </template>

            <template #content>
                <p class="mb-0">
                    ¿Está seguro de que desea confirmar el pedido
                    <strong>#{{ pedidoSeleccionado?.id }}</strong
                    >?
                </p>
                <p class="text-muted small mb-0 mt-2">
                    Esta acción convertirá el pedido en una venta.
                </p>
            </template>

            <template #footer>
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="cerrarModales"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="btn btn-success"
                    @click="ejecutarConfirmacion"
                >
                    <i class="bi bi-check-circle me-2"></i>
                    Confirmar
                </button>
            </template>
        </ConfirmationModal>

        <!-- Modal Cancelar -->
        <ConfirmationModal
            :show="showCancelModal"
            @close="cerrarModales"
            max-width="sm"
        >
            <template #title> Cancelar Pedido </template>

            <template #content>
                <p class="mb-0">
                    ¿Está seguro de que desea cancelar el pedido
                    <strong>#{{ pedidoSeleccionado?.id }}</strong
                    >?
                </p>
                <p class="text-muted small mb-0 mt-2">
                    El stock de los productos será devuelto automáticamente.
                </p>
            </template>

            <template #footer>
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="cerrarModales"
                >
                    No, volver
                </button>
                <button
                    type="button"
                    class="btn btn-danger"
                    @click="ejecutarCancelacion"
                >
                    <i class="bi bi-x-circle me-2"></i>
                    Sí, cancelar pedido
                </button>
            </template>
        </ConfirmationModal>

        <!-- Modal de configuración de crédito -->
        <CreditoModal
            :show="mostrarModalCredito"
            :total="pedidoSeleccionado?.total || 0"
            :cuotas-iniciales="3"
            @close="mostrarModalCredito = false"
            @confirmar="confirmarConCuotas"
        />
    </AppLayout>
</template>
