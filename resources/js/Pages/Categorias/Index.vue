<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

const props = defineProps({
    categorias: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const showDeleteModal = ref(false);
const categoryToDelete = ref(null);

const performSearch = () => {
    router.get(
        route("categorias.index"),
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const confirmDelete = (categoria) => {
    categoryToDelete.value = categoria;
    showDeleteModal.value = true;
};

const deleteCategoria = () => {
    if (categoryToDelete.value) {
        router.delete(route("categorias.destroy", categoryToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                categoryToDelete.value = null;
            },
        });
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    categoryToDelete.value = null;
};
</script>

<template>
    <Head title="Categorías" />

    <AppLayout title="Categorías">
        <FlashNotification />

        <div class="container py-4">
            <!-- Encabezado -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <h2 class="mb-0">Gestión de Categorías</h2>
                    <p class="text-muted">
                        Organiza tus productos por categorías
                    </p>
                </div>
                <div class="col-lg-4 text-end">
                    <Link
                        v-if="$page.props.auth.permissions?.categorias?.create"
                        :href="route('categorias.create')"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-plus-circle me-2"></i>
                        Nueva Categoría
                    </Link>
                </div>
            </div>

            <!-- Barra de búsqueda -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="input-group">
                                <input
                                    v-model="search"
                                    type="text"
                                    class="form-control"
                                    placeholder="Buscar categoría..."
                                    @keyup.enter="performSearch"
                                />
                                <button
                                    class="btn btn-outline-secondary"
                                    type="button"
                                    @click="performSearch"
                                >
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de categorías -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Productos</th>
                                    <th
                                        class="text-center"
                                        style="width: 180px"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="categorias.data.length === 0">
                                    <td
                                        colspan="4"
                                        class="text-center text-muted py-4"
                                    >
                                        No se encontraron categorías
                                    </td>
                                </tr>
                                <tr
                                    v-for="categoria in categorias.data"
                                    :key="categoria.id"
                                >
                                    <td>
                                        <strong>{{ categoria.nombre }}</strong>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{
                                            categoria.descripcion ||
                                            "Sin descripción"
                                        }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">
                                            {{ categoria.productos_count }}
                                            productos
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div
                                            class="btn-group btn-group-sm"
                                            role="group"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'categorias.show',
                                                        categoria.id
                                                    )
                                                "
                                                class="btn btn-outline-info"
                                                title="Ver detalles"
                                            >
                                                <i class="bi bi-eye-fill"></i>
                                            </Link>
                                            <Link
                                                v-if="
                                                    $page.props.auth.permissions
                                                        ?.categorias?.update
                                                "
                                                :href="
                                                    route(
                                                        'categorias.edit',
                                                        categoria.id
                                                    )
                                                "
                                                class="btn btn-outline-warning"
                                                title="Editar"
                                            >
                                                <i
                                                    class="bi bi-pencil-fill"
                                                ></i>
                                            </Link>
                                            <button
                                                v-if="
                                                    $page.props.auth.permissions
                                                        ?.categorias?.delete
                                                "
                                                @click="
                                                    confirmDelete(categoria)
                                                "
                                                class="btn btn-outline-danger"
                                                title="Eliminar"
                                            >
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="categorias.links.length > 3" class="mt-4">
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li
                                    v-for="(link, index) in categorias.links"
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

        <!-- Modal de Confirmación -->
        <ConfirmationModal :show="showDeleteModal" @close="cancelDelete" max-width="sm">
            <template #title>
                Confirmar Eliminación de Categoría
            </template>

            <template #content>
                <p class="mb-0">
                    ¿Está seguro de que desea eliminar la categoría
                    <strong>{{ categoryToDelete?.nombre }}</strong>?
                </p>
                <p class="text-warning small mb-0 mt-2" v-if="categoryToDelete?.productos_count > 0">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Esta categoría tiene <strong>{{ categoryToDelete.productos_count }}</strong> producto(s) asociado(s).
                    Al eliminarla, estos productos quedarán sin categoría asignada.
                </p>
                <p class="text-muted small mb-0 mt-2">
                    Esta acción no se puede deshacer.
                </p>
            </template>

            <template #footer>
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="cancelDelete"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="btn btn-danger"
                    @click="deleteCategoria"
                >
                    <i class="bi bi-trash-fill me-2"></i>
                    Eliminar
                </button>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
