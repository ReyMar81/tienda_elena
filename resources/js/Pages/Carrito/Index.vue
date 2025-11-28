<template>
    <AppLayout title="Mi Carrito">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="bi bi-cart-fill me-2"></i>Mi Carrito
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Mensajes de éxito/error -->
                <div
                    v-if="$page.props.flash.success"
                    class="alert alert-success alert-dismissible fade show mb-4"
                    role="alert"
                >
                    <i class="bi bi-check-circle me-2"></i
                    >{{ $page.props.flash.success }}
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>
                </div>

                <div
                    v-if="$page.props.flash.error"
                    class="alert alert-danger alert-dismissible fade show mb-4"
                    role="alert"
                >
                    <i class="bi bi-x-circle me-2"></i
                    >{{ $page.props.flash.error }}
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>
                </div>

                <!-- Carrito Vacío -->
                <div
                    v-if="!carrito || detalles.length === 0"
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg"
                >
                    <div class="p-6 text-center">
                        <i
                            class="bi bi-cart-x"
                            style="font-size: 5rem; color: #6c757d"
                        ></i>
                        <h3 class="mt-3 text-xl font-semibold text-gray-700">
                            Tu carrito está vacío
                        </h3>
                        <p class="mt-2 text-gray-600">
                            Agrega productos para comenzar tu compra
                        </p>
                        <Link
                            :href="route('dashboard')"
                            class="btn btn-primary mt-4"
                        >
                            <i class="bi bi-box-seam me-2"></i>Ver Productos
                        </Link>
                    </div>
                </div>

                <!-- Carrito con Items -->
                <div v-else class="row">
                    <!-- Lista de Productos -->
                    <div class="col-lg-8">
                        <div
                            class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-4"
                        >
                            <div class="p-4 border-b border-gray-200">
                                <h4 class="font-semibold text-lg">
                                    Productos ({{ detalles.length }})
                                </h4>
                            </div>

                            <div class="p-4">
                                <div
                                    v-for="item in detalles"
                                    :key="item.id"
                                    class="border-b pb-4 mb-4 last:border-b-0"
                                >
                                    <div class="row align-items-center">
                                        <!-- Imagen -->
                                        <div class="col-md-2">
                                            <img
                                                :src="
                                                    item.producto.imagenes &&
                                                    item.producto.imagenes
                                                        .length > 0
                                                        ? `/storage/${item.producto.imagenes[0].url}`
                                                        : '/images/no-image.png'
                                                "
                                                :alt="item.producto.nombre"
                                                class="img-fluid rounded"
                                                style="
                                                    max-height: 100px;
                                                    object-fit: cover;
                                                "
                                            />
                                        </div>

                                        <!-- Información -->
                                        <div class="col-md-4">
                                            <h5 class="font-semibold">
                                                {{ item.producto.nombre }}
                                            </h5>
                                            <p class="text-sm text-gray-600">
                                                <span
                                                    v-if="
                                                        item.producto.categoria
                                                    "
                                                >
                                                    <i
                                                        class="bi bi-tag me-1"
                                                    ></i
                                                    >{{
                                                        item.producto.categoria
                                                            .nombre
                                                    }}
                                                </span>
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                Stock:
                                                {{ item.producto.stock_actual }}
                                            </p>
                                        </div>

                                        <!-- Precio y Descuento -->
                                        <div class="col-md-2">
                                            <div
                                                v-if="
                                                    item.descuento_porcentaje >
                                                    0
                                                "
                                            >
                                                <p
                                                    class="text-sm text-decoration-line-through text-gray-500"
                                                >
                                                    ${{
                                                        Number(
                                                            item.precio_unitario
                                                        ).toFixed(2)
                                                    }}
                                                </p>
                                                <p
                                                    class="font-semibold text-success"
                                                >
                                                    ${{
                                                        Number(
                                                            item.precio_con_descuento
                                                        ).toFixed(2)
                                                    }}
                                                </p>
                                                <span class="badge bg-danger">
                                                    -{{
                                                        item.descuento_porcentaje
                                                    }}%
                                                </span>
                                            </div>
                                            <div v-else>
                                                <p class="font-semibold">
                                                    ${{
                                                        Number(
                                                            item.precio_unitario
                                                        ).toFixed(2)
                                                    }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Cantidad -->
                                        <div class="col-md-2">
                                            <div class="input-group">
                                                <button
                                                    class="btn btn-outline-secondary btn-sm"
                                                    type="button"
                                                    @click="
                                                        actualizarCantidad(
                                                            item,
                                                            item.cantidad - 1
                                                        )
                                                    "
                                                    :disabled="
                                                        item.cantidad <= 1
                                                    "
                                                >
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input
                                                    type="number"
                                                    class="form-control form-control-sm text-center"
                                                    :value="item.cantidad"
                                                    @change="
                                                        actualizarCantidad(
                                                            item,
                                                            $event.target.value
                                                        )
                                                    "
                                                    min="1"
                                                    :max="
                                                        item.producto
                                                            .stock_actual
                                                    "
                                                />
                                                <button
                                                    class="btn btn-outline-secondary btn-sm"
                                                    type="button"
                                                    @click="
                                                        actualizarCantidad(
                                                            item,
                                                            item.cantidad + 1
                                                        )
                                                    "
                                                    :disabled="
                                                        item.cantidad >=
                                                        item.producto.stock
                                                    "
                                                >
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Subtotal y Eliminar -->
                                        <div class="col-md-2 text-end">
                                            <p
                                                class="font-semibold text-lg mb-2"
                                            >
                                                ${{
                                                    Number(
                                                        item.subtotal
                                                    ).toFixed(2)
                                                }}
                                            </p>
                                            <button
                                                class="btn btn-danger btn-sm"
                                                @click="eliminarItem(item.id)"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón Vaciar Carrito -->
                        <button
                            class="btn btn-outline-danger"
                            @click="vaciarCarrito"
                        >
                            <i class="bi bi-trash me-2"></i>Vaciar Carrito
                        </button>
                    </div>

                    <!-- Resumen del Pedido -->
                    <div class="col-lg-4">
                        <div
                            class="bg-white overflow-hidden shadow-xl sm:rounded-lg sticky-top"
                            style="top: 20px"
                        >
                            <div class="p-4 border-b border-gray-200">
                                <h4 class="font-semibold text-lg">
                                    Resumen del Pedido
                                </h4>
                            </div>

                            <div class="p-4">
                                <!-- Desglose -->
                                <div class="mb-3">
                                    <div
                                        class="d-flex justify-content-between mb-2"
                                    >
                                        <span>Productos:</span>
                                        <span>{{ detalles.length }}</span>
                                    </div>
                                    <div
                                        class="d-flex justify-content-between mb-2"
                                    >
                                        <span>Subtotal:</span>
                                        <span>
                                            ${{
                                                Number(
                                                    calcularSubtotal()
                                                ).toFixed(2)
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        v-if="calcularDescuentoTotal() > 0"
                                        class="d-flex justify-content-between mb-2 text-success"
                                    >
                                        <span>Descuentos:</span>
                                        <span>
                                            -${{
                                                Number(
                                                    calcularDescuentoTotal()
                                                ).toFixed(2)
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <hr />

                                <!-- Total -->
                                <div
                                    class="d-flex justify-content-between mb-4"
                                >
                                    <h5 class="font-semibold">Total:</h5>
                                    <h5 class="font-semibold text-primary">
                                        ${{ Number(total).toFixed(2) }}
                                    </h5>
                                </div>

                                <!-- Botón Proceder al Pedido -->
                                <button
                                    @click="abrirModalDireccion"
                                    class="btn btn-primary w-100 mb-2"
                                    type="button"
                                >
                                    <i class="bi bi-credit-card me-2"></i>
                                    Realizar Pedido
                                </button>

                                <Link
                                    :href="route('dashboard')"
                                    class="btn btn-outline-secondary w-100"
                                >
                                    <i class="bi bi-arrow-left me-2"></i
                                    >Continuar Comprando
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Dirección de Entrega -->
        <div
            v-if="showDireccionModal"
            class="modal fade show d-block"
            tabindex="-1"
            style="background-color: rgba(0, 0, 0, 0.5)"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-geo-alt me-2"></i>
                            Dirección de Entrega
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            @click="showDireccionModal = false"
                        ></button>
                    </div>
                    <form @submit.prevent="realizarPedido">
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <small>
                                    Ingresa la dirección donde deseas recibir tu
                                    pedido. El pago se realizará mediante código
                                    QR.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="direccion" class="form-label">
                                    Dirección Completa
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea
                                    id="direccion"
                                    v-model="formDireccion.direccion_entrega"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Ej: Calle Los Pinos #123, Zona Sur, La Paz - Referencias: Frente al mercado"
                                    required
                                    minlength="10"
                                    maxlength="500"
                                ></textarea>
                                <small class="text-muted">
                                    Mínimo 10 caracteres. Incluye referencias
                                    para facilitar la entrega.
                                </small>
                                <div
                                    v-if="
                                        formDireccion.errors.direccion_entrega
                                    "
                                    class="text-danger small mt-1"
                                >
                                    {{ formDireccion.errors.direccion_entrega }}
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <h6 class="mb-2">
                                    <i class="bi bi-cash-coin me-2"></i>
                                    Total a Pagar:
                                    <strong
                                        >Bs.
                                        {{ Number(total).toFixed(2) }}</strong
                                    >
                                </h6>
                                <small>
                                    Se generará un código QR para realizar el
                                    pago.
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button
                                type="button"
                                class="btn btn-secondary"
                                @click="showDireccionModal = false"
                                :disabled="formDireccion.processing"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                :disabled="
                                    formDireccion.processing ||
                                    !formDireccion.direccion_entrega
                                "
                            >
                                <span v-if="formDireccion.processing">
                                    <i class="bi bi-hourglass-split me-2"></i>
                                    Procesando...
                                </span>
                                <span v-else>
                                    <i class="bi bi-check-circle me-2"></i>
                                    Confirmar Pedido
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    carrito: Object,
    detalles: Array,
    total: Number,
});

// Modal para dirección de entrega
const showDireccionModal = ref(false);
const formDireccion = useForm({
    direccion_entrega: "",
});

// Calcular subtotal sin descuentos
const calcularSubtotal = () => {
    return props.detalles.reduce((sum, item) => {
        return sum + item.precio_unitario * item.cantidad;
    }, 0);
};

// Calcular total de descuentos
const calcularDescuentoTotal = () => {
    return props.detalles.reduce((sum, item) => {
        return sum + item.descuento_monto * item.cantidad;
    }, 0);
};

// Actualizar cantidad
const actualizarCantidad = (item, nuevaCantidad) => {
    const cantidad = parseInt(nuevaCantidad);

    if (cantidad < 1 || cantidad > item.producto.stock_actual) {
        return;
    }

    router.put(
        route("carritos.update", item.id),
        {
            cantidad: cantidad,
        },
        {
            preserveScroll: true,
        }
    );
};

// Eliminar item
const eliminarItem = (itemId) => {
    if (confirm("¿Estás seguro de eliminar este producto del carrito?")) {
        router.delete(route("carritos.destroy", itemId), {
            preserveScroll: true,
        });
    }
};

// Vaciar carrito
const vaciarCarrito = () => {
    if (confirm("¿Estás seguro de vaciar todo el carrito?")) {
        router.delete(route("carritos.vaciar"), {
            preserveScroll: true,
        });
    }
};

// Realizar pedido online
const abrirModalDireccion = () => {
    showDireccionModal.value = true;
};

const realizarPedido = () => {
    formDireccion.post(route("carrito.realizar-pedido"), {
        preserveScroll: false,
        onSuccess: () => {
            showDireccionModal.value = false;
            formDireccion.reset();
        },
    });
};
</script>

<style scoped>
.sticky-top {
    position: sticky;
}
</style>
