<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    usuario: Object,
    estadisticas: Object,
});

const eliminarUsuario = () => {
    if (
        confirm(
            "¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer."
        )
    ) {
        router.delete(route("usuarios.destroy", props.usuario.id));
    }
};

const toggleEstado = () => {
    router.post(
        route("usuarios.toggle-estado", props.usuario.id),
        {},
        {
            preserveScroll: true,
        }
    );
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(value);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-BO", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};
</script>

<template>
    <AppLayout title="Detalle de Usuario">
        <Head :title="`Usuario: ${usuario.name}`" />

        <div class="container py-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">
                        <i class="bi bi-person-circle me-2"></i>
                        Detalle de Usuario
                    </h2>
                    <p class="text-muted">Información completa del usuario</p>
                </div>
                <div class="d-flex gap-2">
                    <Link
                        :href="route('usuarios.index')"
                        class="btn btn-outline-secondary"
                    >
                        <i class="bi bi-arrow-left me-1"></i>
                        Volver
                    </Link>
                    <Link
                        :href="route('usuarios.edit', usuario.id)"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-pencil me-1"></i>
                        Editar
                    </Link>
                </div>
            </div>

            <div class="row">
                <!-- Información del Usuario -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <img
                                :src="usuario.profile_photo_url"
                                :alt="usuario.name"
                                class="rounded-circle mb-3"
                                width="120"
                                height="120"
                            />
                            <h4 class="mb-1">{{ usuario.name }}</h4>
                            <p class="text-muted mb-2">{{ usuario.email }}</p>
                            <span
                                :class="[
                                    'badge',
                                    usuario.estado
                                        ? 'bg-success'
                                        : 'bg-secondary',
                                    'mb-3',
                                ]"
                            >
                                {{ usuario.estado ? "Activo" : "Inactivo" }}
                            </span>
                            <span class="badge bg-info ms-2 mb-3">
                                {{ usuario.role?.nombre || "Sin rol" }}
                            </span>

                            <div class="d-grid gap-2 mt-3">
                                <button
                                    @click="toggleEstado"
                                    class="btn btn-outline-warning"
                                >
                                    <i
                                        :class="
                                            usuario.estado
                                                ? 'bi bi-pause-circle'
                                                : 'bi bi-play-circle'
                                        "
                                    ></i>
                                    {{
                                        usuario.estado
                                            ? "Desactivar"
                                            : "Activar"
                                    }}
                                </button>
                                <button
                                    @click="eliminarUsuario"
                                    class="btn btn-outline-danger"
                                >
                                    <i class="bi bi-trash"></i>
                                    Eliminar Usuario
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Contacto -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-telephone me-2"></i>
                                Contacto
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted small">CI</label>
                                <div>{{ usuario.ci }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Teléfono</label>
                                <div>{{ usuario.telefono }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small">Email</label>
                                <div>{{ usuario.email }}</div>
                            </div>
                            <div v-if="usuario.fecha_nacimiento">
                                <label class="text-muted small"
                                    >Fecha de Nacimiento</label
                                >
                                <div>
                                    {{ formatDate(usuario.fecha_nacimiento) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas y Actividad -->
                <div class="col-md-8">
                    <!-- Estadísticas -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="bg-primary bg-opacity-10 text-primary p-3 rounded"
                                            >
                                                <i
                                                    class="bi bi-cart-check fs-3"
                                                ></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1">
                                                Total Ventas
                                            </h6>
                                            <h3 class="mb-0">
                                                {{ estadisticas.total_ventas }}
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="bg-success bg-opacity-10 text-success p-3 rounded"
                                            >
                                                <i
                                                    class="bi bi-cash-stack fs-3"
                                                ></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1">
                                                Total Gastado
                                            </h6>
                                            <h3 class="mb-0">
                                                {{
                                                    formatCurrency(
                                                        estadisticas.total_gastado
                                                    )
                                                }}
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="bg-warning bg-opacity-10 text-warning p-3 rounded"
                                            >
                                                <i
                                                    class="bi bi-credit-card fs-3"
                                                ></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1">
                                                Créditos Activos
                                            </h6>
                                            <h3 class="mb-0">
                                                {{
                                                    estadisticas.creditos_activos
                                                }}
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="bg-danger bg-opacity-10 text-danger p-3 rounded"
                                            >
                                                <i
                                                    class="bi bi-exclamation-triangle fs-3"
                                                ></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1">
                                                Deuda Total
                                            </h6>
                                            <h3 class="mb-0">
                                                {{
                                                    formatCurrency(
                                                        estadisticas.total_credito
                                                    )
                                                }}
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Últimas Ventas -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history me-2"></i>
                                Últimas Ventas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div
                                v-if="
                                    usuario.ventas && usuario.ventas.length > 0
                                "
                                class="table-responsive"
                            >
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th class="text-end">Total</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="venta in usuario.ventas"
                                            :key="venta.id"
                                        >
                                            <td>#{{ venta.id }}</td>
                                            <td>
                                                {{
                                                    formatDate(venta.created_at)
                                                }}
                                            </td>
                                            <td>
                                                <span
                                                    :class="[
                                                        'badge',
                                                        venta.tipo_venta ===
                                                        'contado'
                                                            ? 'bg-success'
                                                            : 'bg-warning',
                                                    ]"
                                                >
                                                    {{ venta.tipo_venta }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                {{
                                                    formatCurrency(venta.total)
                                                }}
                                            </td>
                                            <td>
                                                <span
                                                    :class="[
                                                        'badge',
                                                        venta.estado ===
                                                        'completado'
                                                            ? 'bg-success'
                                                            : 'bg-warning',
                                                    ]"
                                                >
                                                    {{ venta.estado }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No hay ventas registradas
                            </div>
                        </div>
                    </div>

                    <!-- Información del Sistema -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Información del Sistema
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="text-muted small"
                                        >Fecha de Registro</label
                                    >
                                    <div class="mb-3">
                                        {{ formatDate(usuario.created_at) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small"
                                        >Última Actualización</label
                                    >
                                    <div class="mb-3">
                                        {{ formatDate(usuario.updated_at) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
