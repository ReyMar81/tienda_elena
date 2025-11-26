<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    promocion: Object,
});

const formatDate = (date) => new Date(date).toLocaleDateString("es-BO");

const isActive = () => {
    const now = new Date();
    const inicio = new Date(props.promocion.fecha_inicio);
    const fin = new Date(props.promocion.fecha_fin);
    return now >= inicio && now <= fin && props.promocion.estado;
};
</script>

<template>
    <Head :title="promocion.nombre" />

    <AppLayout :title="promocion.nombre">
        <FlashNotification />

        <div class="container py-4">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div
                        class="d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <h2 class="mb-1">{{ promocion.nombre }}</h2>
                            <span
                                class="badge fs-6"
                                :class="
                                    isActive() ? 'bg-success' : 'bg-secondary'
                                "
                            >
                                {{ isActive() ? "Activa" : "Inactiva" }}
                            </span>
                        </div>
                        <Link
                            :href="route('promociones.index')"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-arrow-left me-2"></i> Volver
                        </Link>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <!-- Información General -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Información General</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">
                                {{ promocion.descripcion || "Sin descripción" }}
                            </p>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold"
                                        >Descuento:</label
                                    >
                                    <div>
                                        <span class="badge bg-success fs-5"
                                            >{{
                                                promocion.valor_descuento_decimal
                                            }}%</span
                                        >
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold"
                                        >Fecha Inicio:</label
                                    >
                                    <div>
                                        {{ formatDate(promocion.fecha_inicio) }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold"
                                        >Fecha Fin:</label
                                    >
                                    <div>
                                        {{ formatDate(promocion.fecha_fin) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Productos Aplicados -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                Productos Aplicados
                                <span class="badge bg-primary ms-2">
                                    {{ promocion.productos?.length || 0 }}
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div
                                v-if="
                                    promocion.productos &&
                                    promocion.productos.length > 0
                                "
                            >
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Código</th>
                                                <th>Nombre</th>
                                                <th class="text-center">
                                                    Minorista
                                                </th>
                                                <th class="text-center">
                                                    Mayorista
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="producto in promocion.productos"
                                                :key="producto.id"
                                            >
                                                <td>
                                                    <code>{{
                                                        producto.codigo
                                                    }}</code>
                                                </td>
                                                <td>{{ producto.nombre }}</td>
                                                <td class="text-center">
                                                    <i
                                                        v-if="
                                                            producto.pivot
                                                                ?.aplica_minorista
                                                        "
                                                        class="bi bi-check-circle-fill text-success fs-5"
                                                    ></i>
                                                    <i
                                                        v-else
                                                        class="bi bi-x-circle text-muted fs-5"
                                                    ></i>
                                                </td>
                                                <td class="text-center">
                                                    <i
                                                        v-if="
                                                            producto.pivot
                                                                ?.aplica_mayorista
                                                        "
                                                        class="bi bi-check-circle-fill text-success fs-5"
                                                    ></i>
                                                    <i
                                                        v-else
                                                        class="bi bi-x-circle text-muted fs-5"
                                                    ></i>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <p v-else class="text-muted text-center mb-0">
                                <i class="bi bi-inbox"></i> No hay productos
                                aplicados
                            </p>
                        </div>
                    </div>

                    <!-- Categorías Aplicadas -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                Categorías Aplicadas
                                <span class="badge bg-primary ms-2">
                                    {{ promocion.categorias?.length || 0 }}
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div
                                v-if="
                                    promocion.categorias &&
                                    promocion.categorias.length > 0
                                "
                            >
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th class="text-center">
                                                    Minorista
                                                </th>
                                                <th class="text-center">
                                                    Mayorista
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="categoria in promocion.categorias"
                                                :key="categoria.id"
                                            >
                                                <td>{{ categoria.nombre }}</td>
                                                <td class="text-center">
                                                    <i
                                                        v-if="
                                                            categoria.pivot
                                                                ?.aplica_minorista
                                                        "
                                                        class="bi bi-check-circle-fill text-success fs-5"
                                                    ></i>
                                                    <i
                                                        v-else
                                                        class="bi bi-x-circle text-muted fs-5"
                                                    ></i>
                                                </td>
                                                <td class="text-center">
                                                    <i
                                                        v-if="
                                                            categoria.pivot
                                                                ?.aplica_mayorista
                                                        "
                                                        class="bi bi-check-circle-fill text-success fs-5"
                                                    ></i>
                                                    <i
                                                        v-else
                                                        class="bi bi-x-circle text-muted fs-5"
                                                    ></i>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <p v-else class="text-muted text-center mb-0">
                                <i class="bi bi-inbox"></i> No hay categorías
                                aplicadas
                            </p>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                <Link
                                    v-if="
                                        $page.props.auth.permissions
                                            ?.promociones?.update
                                    "
                                    :href="
                                        route('promociones.edit', promocion.id)
                                    "
                                    class="btn btn-primary"
                                >
                                    <i class="bi bi-pencil me-2"></i> Editar
                                    Promoción
                                </Link>
                                <Link
                                    :href="route('promociones.index')"
                                    class="btn btn-secondary"
                                >
                                    <i class="bi bi-list me-2"></i> Ver Todas
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
