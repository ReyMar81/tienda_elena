
<template>
    <AppLayout title="Ventas">
        <div class="container py-4">
            <div class="row mb-4">
                <div class="col-lg-8">
                    <h2 class="mb-0">Gestión de Ventas</h2>
                    <p class="text-muted">
                        Visualiza el historial de ventas realizadas
                    </p>
                </div>
                <div class="col-lg-4 text-end">
                    <!-- Sin botón de crear -->
                </div>
            </div>

            <!-- Filtros de origen -->
            <div class="d-flex gap-3 mb-4">
                <div class="btn-group" role="group">
                    <button
                        class="btn btn-outline-primary"
                        :class="{ active: filtroOrigen === 'tienda' }"
                        @click="cambiarFiltroOrigen('tienda')"
                    >
                        <i class="bi bi-shop me-1"></i> Tienda
                    </button>
                    <button
                        class="btn btn-outline-success"
                        :class="{ active: filtroOrigen === 'online' }"
                        @click="cambiarFiltroOrigen('online')"
                    >
                        <i class="bi bi-globe2 me-1"></i> Online
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Método Pago</th>
                                    <th>Tipo de Pago</th>
                                    <th v-if="filtroOrigen === 'online'">Dirección de Entrega</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="ventas.data.length === 0">
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No se encontraron ventas
                                    </td>
                                </tr>
                                <tr v-for="venta in ventas.data.filter(v => (filtroOrigen === 'tienda' ? v.estado === 'pagado' : v.estado === 'enviado'))" :key="venta.id">
                                    <td>
                                        <strong>#{{ venta.id }}</strong>
                                    </td>
                                    <td>
                                        {{ formatDate(venta.created_at) }}
                                    </td>
                                    <td>
                                        {{ venta.user?.nombre || "N/A" }}
                                    </td>
                                    <td>
                                        <i class="bi bi-credit-card me-1"></i>
                                        {{ venta.metodo_pago?.nombre || venta.metodoPago?.nombre || "N/A" }}
                                    </td>
                                    <td>
                                        <span :class="venta.tipo_pago === 'credito' ? 'badge bg-info' : 'badge bg-success'">
                                            {{ venta.tipo_pago === "credito" ? "A Crédito" : "Al Contado" }}
                                        </span>
                                    </td>
                                    <td v-if="filtroOrigen === 'online'">
                                        {{ venta.direccion_entrega || 'N/A' }}
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-primary">Bs. {{ parseFloat(venta.total || 0).toFixed(2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <template v-if="
                                            filtroOrigen === 'tienda' &&
                                            venta.estado === 'pagado' &&
                                            venta.tipo_pago === 'credito'
                                        ">
                                            <span v-if="venta.cuotas_pendientes > 0" class="badge bg-warning text-dark">
                                                Pagos en proceso
                                            </span>
                                            <span v-else class="badge bg-success">
                                                Pagado
                                            </span>
                                        </template>
                                        <template v-else>
                                            <span :class="getEstadoBadge(venta.estado)">
                                                {{ venta.estado }}
                                            </span>
                                        </template>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <Link
                                                :href="route('ventas.show', venta.id)"
                                                class="btn btn-outline-info"
                                                title="Ver detalles"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="ventas.links && ventas.links.length > 3" class="mt-4">
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li
                                    v-for="(link, index) in ventas.links"
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

<script setup>
import { computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    ventas: Object,
    filtro_origen: String,
});

const filtroOrigen = computed(() => props.filtro_origen || 'tienda');

const cambiarFiltroOrigen = (origen) => {
    router.get(
        route("ventas.index"),
        { origen },
        {
            preserveState: false,
            preserveScroll: false,
        }
    );
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("es-ES", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const getEstadoBadge = (estado) => {
    const badges = {
        pendiente: "badge bg-warning",
        pagado: "badge bg-success",
        enviado: "badge bg-info",
        anulado: "badge bg-danger",
    };
    return badges[estado] || "badge bg-secondary";
};
</script>
