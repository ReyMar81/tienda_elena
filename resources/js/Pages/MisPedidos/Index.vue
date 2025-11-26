<script setup>
import { computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    pedidos: Object,
    filtro: {
        type: String,
        default: "pendiente",
    },
});

// Cambiar filtro de pestañas
const cambiarFiltro = (estado) => {
    router.get(
        route("mis-pedidos.index"),
        { estado },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

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

const getMetodoPagoLabel = (metodo) => {
    const labels = {
        contado: "Contado",
        credito: "Crédito",
    };
    return labels[metodo] || metodo;
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
    <AppLayout title="Mis Pedidos">
            <div class="container py-4">

            <!-- Encabezado -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <h2 class="mb-0">Mis Pedidos</h2>
                    <p class="text-muted">
                        Consulta el historial de tus pedidos realizados
                    </p>
                </div>
            </div>

            <!-- Pestañas de filtro: Pendientes y Pagados-->
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
                        :class="{ active: filtro === 'pagado' }"
                        @click="cambiarFiltro('pagado')"
                    >
                        <i class="bi bi-check-circle me-2"></i>
                        Pagados
                    </button>
                </li>
            </ul>

            <!-- Tabla de pedidos -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>N° Pedido</th>
                                    <th>Fecha</th>
                                    <th>Método de Pago</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center" style="width: 140px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="pedidos.data.length === 0">
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-info-circle"></i> No tienes pedidos {{ filtro === 'pendiente' ? 'pendientes' : 'pagados' }}.<br>
                                        <Link :href="route('dashboard')" class="btn btn-outline-primary mt-2">
                                            <i class="bi bi-box-seam me-2"></i>Ver productos disponibles
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-for="pedido in pedidos.data" :key="pedido.id">
                                    <td>
                                        <strong>#{{ pedido.numero_venta }}</strong>
                                    </td>
                                    <td>
                                        {{ new Date(pedido.created_at).toLocaleDateString("es-ES", {
                                            day: "2-digit",
                                            month: "short",
                                            year: "numeric",
                                        }) }}
                                    </td>
                                    <td>
                                        <span class="badge" :class="(pedido.metodo_pago?.nombre || pedido.metodo_pago) === 'Crédito' || (pedido.metodo_pago?.nombre || pedido.metodo_pago) === 'credito' ? 'bg-info' : 'bg-primary'">
                                            {{ pedido.metodo_pago?.nombre || getMetodoPagoLabel(pedido.metodo_pago) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong>Bs. {{ formatMoney(pedido.total) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge" :class="getBadgeClass(pedido.estado)">
                                            {{ pedido.estado }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <Link
                                            :href="route('mis-pedidos.show', pedido.id)"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Ver Detalle"
                                        >
                                            <i class="bi bi-eye"></i> Ver
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="pedidos.links && pedidos.links.length > 3" class="mt-4">
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li
                                    v-for="(link, index) in pedidos.links"
                                    :key="index"
                                    class="page-item"
                                    :class="{
                                        active: link.active,
                                        disabled: !link.url,
                                    }"
                                >
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        class="page-link"
                                        v-html="link.label"
                                        preserve-state
                                    />
                                    <span
                                        v-else
                                        class="page-link"
                                        v-html="link.label"
                                    ></span>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
