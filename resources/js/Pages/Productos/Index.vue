<script setup>
import { ref, computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

const props = defineProps({
    productos: Object,
    filters: Object,
    rol: String,
});

const search = ref(props.filters.search || "");
const showDeleteModal = ref(false);
const productToDelete = ref(null);

// Modal para cantidad (solo clientes)
const showCantidadModal = ref(false);
const productoSeleccionado = ref(null);
const cantidadAgregar = ref(1);

const isCliente = computed(() => (props.rol || '').toLowerCase() === "cliente");

// Debug: verificar el rol
console.log('Rol actual:', props.rol);
console.log('Es cliente:', isCliente.value);

// Búsqueda reactiva
const performSearch = () => {
    router.get(
        route("productos.index"),
        { search: search.value },
        {
            preserveState: true,
            replace: true,
        }
    );
};

// Mostrar modal de confirmación
const confirmDelete = (producto) => {
    productToDelete.value = producto;
    showDeleteModal.value = true;
};

// Eliminar producto
const deleteProducto = () => {
    if (productToDelete.value) {
        router.delete(route("productos.destroy", productToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
                productToDelete.value = null;
            },
        });
    }
};

// Cancelar eliminación
const cancelDelete = () => {
    showDeleteModal.value = false;
    productToDelete.value = null;
};

// Formatear precio
const formatPrice = (price) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(price);
};

// URL de imagen (usar primera de product_images)
const getImageUrl = (producto) => {
    if (producto.imagenes && producto.imagenes.length > 0) {
        return `/storage/${producto.imagenes[0].url}`;
    }
    return "/images/no-image.png";
};

// Agregar al carrito (solo clientes)
const abrirModalCantidad = (producto) => {
    productoSeleccionado.value = producto;
    cantidadAgregar.value = 1;
    showCantidadModal.value = true;
};

const agregarAlCarrito = () => {
    if (!productoSeleccionado.value) return;
    router.post(
        route("carritos.store"),
        {
            producto_id: productoSeleccionado.value.id,
            cantidad: cantidadAgregar.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showCantidadModal.value = false;
                productoSeleccionado.value = null;
            },
        }
    );
};
</script>

<template>
    <Head title="Productos" />

    <AppLayout title="Productos">
        <FlashNotification />
        <div class="container py-4">
            <!-- Encabezado -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <h2 class="mb-0">Gestión de Productos</h2>
                    <p class="text-muted">
                        Administra tu catálogo de productos
                    </p>
                </div>
                <div class="col-lg-4 text-end">
                    <Link
                        v-if="$page.props.auth.permissions?.productos?.create"
                        :href="route('productos.create')"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-plus-circle me-2"></i>
                        Nuevo Producto
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
                                    placeholder="Buscar por nombre o código..."
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

            <!-- Tabla de productos -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 80px">Imagen</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-center">Stock</th>
                                    <th
                                        class="text-center"
                                        style="width: 180px"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="productos.data.length === 0">
                                    <td
                                        colspan="7"
                                        class="text-center text-muted py-4"
                                    >
                                        No se encontraron productos
                                    </td>
                                </tr>
                                <tr
                                    v-for="producto in productos.data"
                                    :key="producto.id"
                                >
                                    <td>
                                        <img
                                            :src="getImageUrl(producto)"
                                            :alt="producto.nombre"
                                            class="img-thumbnail"
                                            style="
                                                width: 60px;
                                                height: 60px;
                                                object-fit: cover;
                                            "
                                            loading="lazy"
                                        />
                                    </td>
                                    <td>
                                        <code>{{ producto.codigo }}</code>
                                    </td>
                                    <td>
                                        <strong>{{ producto.nombre }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{
                                                producto.categoria?.nombre ||
                                                "Sin categoría"
                                            }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <strong>{{
                                            formatPrice(producto.precio_venta)
                                        }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge"
                                            :class="
                                                producto.stock_actual >
                                                producto.stock_minimo
                                                    ? 'bg-success'
                                                    : producto.stock_actual > 0
                                                    ? 'bg-warning'
                                                    : 'bg-danger'
                                            "
                                        >
                                            {{ producto.stock_actual }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div
                                            class="btn-group btn-group-sm"
                                            role="group"
                                            aria-label="Acciones del producto"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'productos.show',
                                                        producto.id
                                                    )
                                                "
                                                class="btn btn-outline-info"
                                                title="Ver detalles del producto"
                                            >
                                                <i class="bi bi-eye-fill"></i>
                                            </Link>
                                            <button
                                                v-if="isCliente"
                                                @click="abrirModalCantidad(producto)"
                                                class="btn btn-outline-success"
                                                title="Agregar al carrito"
                                                :disabled="producto.stock_actual === 0"
                                            >
                                                <i class="bi bi-cart-plus"></i>
                                            </button>
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
                                                class="btn btn-outline-warning"
                                                title="Editar producto"
                                            >
                                                <i
                                                    class="bi bi-pencil-fill"
                                                ></i>
                                            </Link>
                                            <button
                                                v-if="
                                                    $page.props.auth.permissions
                                                        ?.productos?.delete
                                                "
                                                @click="confirmDelete(producto)"
                                                class="btn btn-outline-danger"
                                                title="Eliminar producto"
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
                    <div v-if="productos.links.length > 3" class="mt-4">
                        <nav>
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
            <template #title> Confirmar Eliminación </template>

            <template #content>
                <p class="mb-0">
                    ¿Está seguro de que desea eliminar el producto
                    <strong>{{ productToDelete?.nombre }}</strong
                    >?
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
                    @click="deleteProducto"
                >
                    <i class="bi bi-trash-fill me-2"></i>
                    Eliminar
                </button>
            </template>
        </ConfirmationModal>

        <!-- Modal para Agregar al Carrito (solo clientes) -->
        <ConfirmationModal :show="showCantidadModal" @close="showCantidadModal = false" max-width="sm">
            <template #title>Agregar al Carrito</template>
            <template #content>
                <div v-if="productoSeleccionado">
                    <p class="mb-2">¿Cuántas unidades de <strong>{{ productoSeleccionado.nombre }}</strong> deseas agregar?</p>
                    <input
                        type="number"
                        v-model="cantidadAgregar"
                        min="1"
                        :max="productoSeleccionado.stock_actual"
                        class="form-control w-50"
                    />
                    <small class="text-muted">Stock disponible: {{ productoSeleccionado.stock_actual }}</small>
                </div>
            </template>
            <template #footer>
                <button type="button" class="btn btn-secondary" @click="showCantidadModal = false">Cancelar</button>
                <button type="button" class="btn btn-primary" @click="agregarAlCarrito" :disabled="cantidadAgregar < 1 || cantidadAgregar > (productoSeleccionado?.stock_actual || 1)">
                    <i class="bi bi-cart-plus me-2"></i>Agregar
                </button>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
