<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    producto: Object,
});

// Índice de la imagen actual en el carrusel
const currentImageIndex = ref(0);

// URL de imagen
const getImageUrl = (url) => {
    return url ? `/storage/${url}` : "/images/no-image.png";
};

// Imágenes del producto
const imagenes = computed(() => {
    return props.producto.imagenes && props.producto.imagenes.length > 0
        ? props.producto.imagenes
        : [{ url: null }];
});

// Formatear precio
const formatPrice = (price) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(price);
};

// Navegación del carrusel
const goToImage = (index) => {
    currentImageIndex.value = index;
};

const nextImage = () => {
    if (currentImageIndex.value < imagenes.value.length - 1) {
        currentImageIndex.value++;
    } else {
        currentImageIndex.value = 0;
    }
};

const prevImage = () => {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
    } else {
        currentImageIndex.value = imagenes.value.length - 1;
    }
};
</script>

<template>
    <Head :title="producto.nombre" />

    <AppLayout :title="producto.nombre">
        <FlashNotification />
        <div class="container py-4">
            <!-- Encabezado -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div
                        class="d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <h2 class="mb-0">Detalle del Producto</h2>
                        </div>
                        <Link
                            :href="route('productos.index')"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-arrow-left me-2"></i>
                            Volver
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Detalle -->
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <!-- Galería de Imágenes -->
                                <div class="col-md-5">
                                    <!-- Carrusel principal -->
                                    <div class="position-relative mb-3">
                                        <div
                                            id="productCarousel"
                                            class="carousel slide"
                                            data-bs-ride="false"
                                        >
                                            <div class="carousel-inner">
                                                <div
                                                    v-for="(
                                                        img, index
                                                    ) in imagenes"
                                                    :key="index"
                                                    class="carousel-item"
                                                    :class="{
                                                        active:
                                                            index ===
                                                            currentImageIndex,
                                                    }"
                                                >
                                                    <img
                                                        :src="
                                                            getImageUrl(img.url)
                                                        "
                                                        :alt="`${
                                                            producto.nombre
                                                        } - Imagen ${
                                                            index + 1
                                                        }`"
                                                        class="d-block w-100 rounded border"
                                                        style="
                                                            height: 350px;
                                                            object-fit: contain;
                                                        "
                                                    />
                                                </div>
                                            </div>
                                            <!-- Controles solo si hay múltiples imágenes -->
                                            <button
                                                v-if="imagenes.length > 1"
                                                class="carousel-control-prev"
                                                type="button"
                                                @click="prevImage"
                                            >
                                                <span
                                                    class="carousel-control-prev-icon bg-dark rounded-circle p-3"
                                                    aria-hidden="true"
                                                ></span>
                                                <span class="visually-hidden"
                                                    >Anterior</span
                                                >
                                            </button>
                                            <button
                                                v-if="imagenes.length > 1"
                                                class="carousel-control-next"
                                                type="button"
                                                @click="nextImage"
                                            >
                                                <span
                                                    class="carousel-control-next-icon bg-dark rounded-circle p-3"
                                                    aria-hidden="true"
                                                ></span>
                                                <span class="visually-hidden"
                                                    >Siguiente</span
                                                >
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Miniaturas interactivas -->
                                    <div
                                        v-if="imagenes.length > 1"
                                        class="d-flex gap-2 flex-wrap"
                                    >
                                        <img
                                            v-for="(img, index) in imagenes"
                                            :key="index"
                                            :src="getImageUrl(img.url)"
                                            :alt="`${producto.nombre} ${
                                                index + 1
                                            }`"
                                            class="img-thumbnail"
                                            :class="{
                                                'border-primary border-2':
                                                    index === currentImageIndex,
                                            }"
                                            style="
                                                width: 70px;
                                                height: 70px;
                                                object-fit: cover;
                                                cursor: pointer;
                                            "
                                            @click="goToImage(index)"
                                        />
                                    </div>
                                </div>

                                <!-- Información -->
                                <div class="col-md-7">
                                    <div class="mb-3">
                                        <span class="badge bg-secondary mb-2">
                                            {{
                                                producto.categoria?.nombre ||
                                                "Sin categoría"
                                            }}
                                        </span>
                                        <span
                                            v-if="producto.estado"
                                            class="badge bg-success ms-2"
                                            >Activo</span
                                        >
                                        <span
                                            v-else
                                            class="badge bg-danger ms-2"
                                            >Inactivo</span
                                        >
                                        <h3 class="mb-1 mt-2">
                                            {{ producto.nombre }}
                                        </h3>
                                        <p class="text-muted">
                                            <small
                                                >Código:
                                                <code>{{
                                                    producto.codigo
                                                }}</code></small
                                            >
                                            <span
                                                v-if="producto.marca"
                                                class="ms-3"
                                            >
                                                <small
                                                    >Marca:
                                                    <strong>{{
                                                        producto.marca
                                                    }}</strong></small
                                                >
                                            </span>
                                        </p>
                                    </div>

                                    <!-- Precios -->
                                    <div class="row g-2 mb-4">
                                        <div class="col-4">
                                            <div
                                                class="border rounded p-2 text-center"
                                            >
                                                <small
                                                    class="text-muted d-block"
                                                    >Compra</small
                                                >
                                                <strong
                                                    class="text-secondary"
                                                    >{{
                                                        formatPrice(
                                                            producto.precio_compra
                                                        )
                                                    }}</strong
                                                >
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div
                                                class="border rounded p-2 text-center bg-light"
                                            >
                                                <small
                                                    class="text-muted d-block"
                                                    >Venta</small
                                                >
                                                <strong
                                                    class="text-primary fs-5"
                                                    >{{
                                                        formatPrice(
                                                            producto.precio_venta
                                                        )
                                                    }}</strong
                                                >
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div
                                                class="border rounded p-2 text-center"
                                            >
                                                <small
                                                    class="text-muted d-block"
                                                    >Mayorista</small
                                                >
                                                <strong class="text-info">{{
                                                    formatPrice(
                                                        producto.precio_venta_mayorista
                                                    )
                                                }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stock -->
                                    <div class="row g-2 mb-4">
                                        <div class="col-6">
                                            <div class="border rounded p-3">
                                                <small
                                                    class="text-muted d-block mb-1"
                                                    >Stock Actual</small
                                                >
                                                <h4
                                                    class="mb-0"
                                                    :class="
                                                        producto.stock_actual >
                                                        producto.stock_minimo
                                                            ? 'text-success'
                                                            : producto.stock_actual >
                                                              0
                                                            ? 'text-warning'
                                                            : 'text-danger'
                                                    "
                                                >
                                                    {{ producto.stock_actual }}
                                                    unidades
                                                </h4>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-3">
                                                <small
                                                    class="text-muted d-block mb-1"
                                                    >Stock Mínimo</small
                                                >
                                                <h4 class="mb-0 text-muted">
                                                    {{ producto.stock_minimo }}
                                                    unidades
                                                </h4>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Acciones -->
                                    <div class="d-flex gap-2">
                                        <Link
                                            v-if="
                                                $page.props.auth.permissions
                                                    ?.productos?.update
                                            "
                                            :href="
                                                route(
                                                    'productos.edit',
                                                    producto.id
                                                )
                                            "
                                            class="btn btn-primary"
                                        >
                                            <i class="bi bi-pencil me-2"></i>
                                            Editar
                                        </Link>
                                        <Link
                                            :href="route('productos.index')"
                                            class="btn btn-outline-secondary"
                                        >
                                            <i
                                                class="bi bi-arrow-left me-2"
                                            ></i>
                                            Volver al Listado
                                        </Link>
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
