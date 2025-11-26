<script setup>
import { ref, computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    usuarios: Object,
    roles: Array,
    filters: Object,
    estadisticas: Object,
});

const filtros = ref({
    buscar: props.filters.buscar || "",
    role_id: props.filters.role_id || "",
    estado: props.filters.estado || "",
});

const aplicarFiltros = () => {
    router.get(route("usuarios.index"), filtros.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const limpiarFiltros = () => {
    filtros.value = {
        buscar: "",
        role_id: "",
        estado: "",
    };
    aplicarFiltros();
};

const eliminarUsuario = (id) => {
    if (
        confirm(
            "¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer."
        )
    ) {
        router.delete(route("usuarios.destroy", id), {
            preserveScroll: true,
        });
    }
};

const toggleEstado = (id) => {
    router.post(
        route("usuarios.toggle-estado", id),
        {},
        {
            preserveScroll: true,
        }
    );
};

const getBadgeClass = (estado) => {
    return estado ? "bg-success" : "bg-secondary";
};

const getEstadoTexto = (estado) => {
    return estado ? "Activo" : "Inactivo";
};
</script>

<template>
    <AppLayout title="Gestión de Usuarios">
        <Head title="Usuarios" />

        <div class="container py-4">
            <FlashNotification />

            <!-- Header -->
            <div class="row mb-4">
                <div class="col">
                    <h2 class="mb-0">
                        <i class="bi bi-people me-2"></i>
                        Gestión de Usuarios
                    </h2>
                    <p class="text-muted">
                        Administra los usuarios del sistema
                    </p>
                </div>
                <div class="col-auto">
                    <Link
                        :href="route('usuarios.create')"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-plus-circle me-1"></i>
                        Nuevo Usuario
                    </Link>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div
                                        class="bg-primary bg-opacity-10 text-primary p-3 rounded"
                                    >
                                        <i class="bi bi-people fs-3"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">
                                        Total Usuarios
                                    </h6>
                                    <h3 class="mb-0">
                                        {{ estadisticas.total }}
                                    </h3>
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
                                        class="bg-success bg-opacity-10 text-success p-3 rounded"
                                    >
                                        <i class="bi bi-person-check fs-3"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">Activos</h6>
                                    <h3 class="mb-0">
                                        {{ estadisticas.activos }}
                                    </h3>
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
                                        class="bg-secondary bg-opacity-10 text-secondary p-3 rounded"
                                    >
                                        <i class="bi bi-person-x fs-3"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="text-muted mb-1">Inactivos</h6>
                                    <h3 class="mb-0">
                                        {{ estadisticas.inactivos }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form @submit.prevent="aplicarFiltros">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input
                                    type="text"
                                    v-model="filtros.buscar"
                                    class="form-control"
                                    placeholder="Nombre, CI, Email..."
                                />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Rol</label>
                                <select
                                    v-model="filtros.role_id"
                                    class="form-select"
                                >
                                    <option value="">Todos los roles</option>
                                    <option
                                        v-for="rol in roles"
                                        :key="rol.id"
                                        :value="rol.id"
                                    >
                                        {{ rol.nombre }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Estado</label>
                                <select
                                    v-model="filtros.estado"
                                    class="form-select"
                                >
                                    <option value="">Todos</option>
                                    <option value="activo">Activos</option>
                                    <option value="inactivo">Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button
                                    type="submit"
                                    class="btn btn-primary w-100 me-2"
                                >
                                    <i class="bi bi-search"></i>
                                </button>
                                <button
                                    type="button"
                                    @click="limpiarFiltros"
                                    class="btn btn-outline-secondary w-100"
                                >
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de Usuarios -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre Completo</th>
                                    <th>CI</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Fecha Registro</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="usuarios.data.length === 0">
                                    <td
                                        colspan="9"
                                        class="text-center text-muted py-4"
                                    >
                                        <i
                                            class="bi bi-inbox fs-1 d-block mb-2"
                                        ></i>
                                        No se encontraron usuarios
                                    </td>
                                </tr>
                                <tr
                                    v-for="usuario in usuarios.data"
                                    :key="usuario.id"
                                >
                                    <td>{{ usuario.id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img
                                                :src="usuario.profile_photo_url"
                                                :alt="usuario.name"
                                                class="rounded-circle me-2"
                                                width="32"
                                                height="32"
                                            />
                                            <strong>{{ usuario.name }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ usuario.ci }}</td>
                                    <td>{{ usuario.email }}</td>
                                    <td>{{ usuario.telefono }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{
                                                usuario.role?.nombre ||
                                                "Sin rol"
                                            }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            :class="[
                                                'badge',
                                                getBadgeClass(usuario.estado),
                                            ]"
                                        >
                                            {{ getEstadoTexto(usuario.estado) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{
                                            new Date(
                                                usuario.created_at
                                            ).toLocaleDateString("es-BO")
                                        }}
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <Link
                                                :href="
                                                    route(
                                                        'usuarios.show',
                                                        usuario.id
                                                    )
                                                "
                                                class="btn btn-sm btn-outline-info"
                                                title="Ver detalles"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </Link>
                                            <Link
                                                :href="
                                                    route(
                                                        'usuarios.edit',
                                                        usuario.id
                                                    )
                                                "
                                                class="btn btn-sm btn-outline-primary"
                                                title="Editar"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </Link>
                                            <button
                                                @click="
                                                    toggleEstado(usuario.id)
                                                "
                                                class="btn btn-sm btn-outline-warning"
                                                :title="
                                                    usuario.estado
                                                        ? 'Desactivar'
                                                        : 'Activar'
                                                "
                                            >
                                                <i
                                                    :class="
                                                        usuario.estado
                                                            ? 'bi bi-pause-circle'
                                                            : 'bi bi-play-circle'
                                                    "
                                                ></i>
                                            </button>
                                            <button
                                                @click="
                                                    eliminarUsuario(usuario.id)
                                                "
                                                class="btn btn-sm btn-outline-danger"
                                                title="Eliminar"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div
                        v-if="usuarios.data.length > 0"
                        class="d-flex justify-content-between align-items-center mt-3"
                    >
                        <div class="text-muted">
                            Mostrando {{ usuarios.from }} a {{ usuarios.to }} de
                            {{ usuarios.total }} usuarios
                        </div>
                        <nav>
                            <ul class="pagination mb-0">
                                <li
                                    v-for="link in usuarios.links"
                                    :key="link.label"
                                    :class="[
                                        'page-item',
                                        {
                                            active: link.active,
                                            disabled: !link.url,
                                        },
                                    ]"
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
