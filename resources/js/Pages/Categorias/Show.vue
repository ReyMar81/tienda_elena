<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    categoria: Object,
    productos: Object,
});
</script>

<template>
    <Head :title="categoria.nombre" />

    <AppLayout :title="categoria.nombre">
        <FlashNotification />

        <div class="container py-4">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div
                        class="d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <h2 class="mb-0">Detalle de Categoría</h2>
                        </div>
                        <Link
                            :href="route('categorias.index')"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-arrow-left me-2"></i>
                            Volver
                        </Link>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="mb-3">{{ categoria.nombre }}</h3>
                            <p class="text-muted">
                                {{ categoria.descripcion || "Sin descripción" }}
                            </p>

                            <hr />

                            <div class="mb-4">
                                <div
                                    class="d-flex justify-content-between align-items-center mb-3"
                                >
                                    <h5 class="mb-0">
                                        Productos en esta categoría
                                    </h5>
                                    <span class="badge bg-info fs-6">
                                        {{ categoria.productos_count }}
                                        producto{{
                                            categoria.productos_count !== 1
                                                ? "s"
                                                : ""
                                        }}
                                    </span>
                                </div>

                                <div class="d-flex gap-2 mb-4">
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
                                        class="btn btn-primary"
                                    >
                                        <i class="bi bi-pencil me-2"></i>
                                        Editar Categoría
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Productos -->
                    <div v-if="productos.data.length > 0" class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Lista de Productos</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div
                                    v-for="producto in productos.data"
                                    :key="producto.id"
                                    class="col-md-6 col-lg-4"
                                >
                                    <div class="card h-100">
                                        <img
                                            v-if="producto.imagenes?.length > 0"
                                            :src="`/storage/${producto.imagenes[0].url}`"
                                            class="card-img-top"
                                            :alt="producto.nombre"
                                            style="
                                                height: 200px;
                                                object-fit: cover;
                                            "
                                        />
                                        <div
                                            v-else
                                            class="card-img-top bg-secondary d-flex align-items-center justify-content-center"
                                            style="height: 200px"
                                        >
                                            <i
                                                class="bi bi-image text-white"
                                                style="font-size: 3rem"
                                            ></i>
                                        </div>
                                        <div class="card-body">
                                            <h6
                                                class="card-title text-truncate"
                                            >
                                                {{ producto.nombre }}
                                            </h6>
                                            <p class="card-text">
                                                <small class="text-muted"
                                                    >Código:
                                                    {{ producto.codigo }}</small
                                                ><br />
                                                <span
                                                    class="fw-bold text-success"
                                                    >Bs.
                                                    {{
                                                        producto.precio_venta
                                                    }}</span
                                                >
                                            </p>
                                            <Link
                                                :href="
                                                    route(
                                                        'productos.show',
                                                        producto.id
                                                    )
                                                "
                                                class="btn btn-sm btn-outline-primary w-100"
                                            >
                                                <i class="bi bi-eye me-1"></i>
                                                Ver Detalles
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Paginación -->
                            <nav v-if="productos.links.length > 3" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li
                                        v-for="(link, index) in productos.links"
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
                                        >
                                        </Link>
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

                    <!-- Sin productos -->
                    <div v-else class="card mt-4">
                        <div class="card-body text-center py-5">
                            <i
                                class="bi bi-inbox text-muted"
                                style="font-size: 3rem"
                            ></i>
                            <p class="text-muted mt-3 mb-0">
                                No hay productos en esta categoría
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
