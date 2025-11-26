<script setup>
import { ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

const props = defineProps({
    promociones: Object,
    filters: Object,
});

const search = ref(props.filters.search || "");
const showDeleteModal = ref(false);
const promocionToDelete = ref(null);

const performSearch = () => {
    router.get(
        route("promociones.index"),
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const confirmDelete = (promocion) => {
    promocionToDelete.value = promocion;
    showDeleteModal.value = true;
};

const deletePromocion = () => {
    if (promocionToDelete.value) {
        router.delete(
            route("promociones.destroy", promocionToDelete.value.id),
            {
                preserveScroll: true,
                onSuccess: () => {
                    showDeleteModal.value = false;
                    promocionToDelete.value = null;
                },
            }
        );
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString("es-BO");
};

const isActive = (promocion) => {
    const now = new Date();
    const inicio = new Date(promocion.fecha_inicio);
    const fin = new Date(promocion.fecha_fin);
    return now >= inicio && now <= fin && promocion.estado;
};
</script>

<template>
    <Head title="Promociones" />

    <AppLayout title="Promociones">
        <FlashNotification />

        <div class="container py-4">
            <div class="row mb-4">
                <div class="col-lg-8">
                    <h2 class="mb-0">Gestión de Promociones</h2>
                    <p class="text-muted">
                        Administra descuentos y ofertas especiales
                    </p>
                </div>
                <div class="col-lg-4 text-end">
                    <Link
                        v-if="$page.props.auth.permissions?.promociones?.create"
                        :href="route('promociones.create')"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-plus-circle me-2"></i>
                        Nueva Promoción
                    </Link>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="input-group" style="max-width: 400px">
                        <input
                            v-model="search"
                            type="text"
                            class="form-control"
                            placeholder="Buscar promoción..."
                            @keyup.enter="performSearch"
                        />
                        <button
                            class="btn btn-outline-secondary"
                            @click="performSearch"
                        >
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th class="text-center">Descuento</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="promociones.data.length === 0">
                                    <td
                                        colspan="6"
                                        class="text-center text-muted py-4"
                                    >
                                        No se encontraron promociones
                                    </td>
                                </tr>
                                <tr
                                    v-for="promocion in promociones.data"
                                    :key="promocion.id"
                                >
                                    <td>
                                        <strong>{{ promocion.nombre }}</strong>
                                        <br />
                                        <small class="text-muted">{{
                                            promocion.descripcion
                                        }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success fs-6"
                                            >{{
                                                promocion.valor_descuento_decimal
                                            }}%</span
                                        >
                                    </td>
                                    <td>
                                        {{ formatDate(promocion.fecha_inicio) }}
                                    </td>
                                    <td>
                                        {{ formatDate(promocion.fecha_fin) }}
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge"
                                            :class="
                                                isActive(promocion)
                                                    ? 'bg-success'
                                                    : 'bg-secondary'
                                            "
                                        >
                                            {{
                                                isActive(promocion)
                                                    ? "Activa"
                                                    : "Inactiva"
                                            }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <Link
                                                :href="
                                                    route(
                                                        'promociones.show',
                                                        promocion.id
                                                    )
                                                "
                                                class="btn btn-outline-info"
                                                title="Ver Detalles"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </Link>
                                            <Link
                                                v-if="
                                                    $page.props.auth.permissions
                                                        ?.promociones?.update
                                                "
                                                :href="
                                                    route(
                                                        'promociones.edit',
                                                        promocion.id
                                                    )
                                                "
                                                class="btn btn-outline-primary"
                                                title="Editar"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </Link>
                                            <button
                                                v-if="
                                                    $page.props.auth.permissions
                                                        ?.promociones?.delete
                                                "
                                                @click="
                                                    confirmDelete(promocion)
                                                "
                                                class="btn btn-outline-danger"
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

                    <div v-if="promociones.links.length > 3" class="mt-4">
                        <nav>
                            <ul class="pagination justify-content-center">
                                <li
                                    v-for="(link, index) in promociones.links"
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
        <ConfirmationModal
            :show="showDeleteModal"
            @close="showDeleteModal = false"
            max-width="sm"
        >
            <template #title>Confirmar Eliminación</template>
            <template #content>
                <p v-if="promocionToDelete">
                    ¿Está seguro de eliminar la promoción
                    <strong>"{{ promocionToDelete.nombre }}"</strong>?
                </p>
                <p class="text-danger mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Esta acción no se puede deshacer.
                </p>
            </template>
            <template #footer>
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="showDeleteModal = false"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="btn btn-danger"
                    @click="deletePromocion"
                >
                    <i class="bi bi-trash me-2"></i>
                    Eliminar
                </button>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
