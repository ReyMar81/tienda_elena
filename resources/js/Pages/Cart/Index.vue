<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useCart } from "@/composables/useCart";

const {
    cartItems,
    cartTotal,
    removeFromCart,
    updateQuantity,
    clearCart,
    isLoading,
} = useCart();

const metodoPago = ref("efectivo");
const processingPago = ref(false);

const formatPrice = (price) => `Bs. ${parseFloat(price).toFixed(2)}`;

const finalizarCompra = async () => {
    if (cartItems.value.length === 0) return;

    processingPago.value = true;

    try {
        const response = await fetch(route("ventas.contado"), {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
            },
            body: JSON.stringify({
                items: cartItems.value.map((item) => ({
                    producto_id: item.producto_id,
                    cantidad: item.cantidad,
                })),
                metodo_pago: metodoPago.value,
            }),
        });

        const data = await response.json();

        if (response.ok) {
            clearCart();
            alert(
                `¡Compra realizada exitosamente! Total: ${formatPrice(
                    data.total
                )}`
            );
            router.visit(route("dashboard"));
        } else {
            alert(data.error || "Error al procesar la compra");
        }
    } catch (error) {
        alert("Error de conexión");
    } finally {
        processingPago.value = false;
    }
};

const solicitarCredito = () => {
    router.visit(route("creditos.create"));
};
</script>

<template>
    <AppLayout title="Mi Carrito">
        <div class="container py-4">
            <h2 class="mb-4">Mi Carrito de Compras</h2>

            <div v-if="isLoading" class="text-center py-5">
                <div class="spinner-border"></div>
                <p class="mt-3">Cargando carrito...</p>
            </div>

            <div v-else-if="cartItems.length === 0" class="text-center py-5">
                <i
                    class="bi bi-cart-x"
                    style="font-size: 5rem; color: #ccc"
                ></i>
                <h4 class="mt-3">Tu carrito está vacío</h4>
                <a :href="route('catalog.index')" class="btn btn-primary mt-3">
                    Ir al catálogo
                </a>
            </div>

            <div v-else class="row">
                <!-- Lista de productos -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between">
                            <span>Productos ({{ cartItems.length }})</span>
                            <button
                                @click="clearCart"
                                class="btn btn-sm btn-outline-danger"
                            >
                                <i class="bi bi-trash"></i> Vaciar carrito
                            </button>
                        </div>
                        <div class="card-body">
                            <div
                                v-for="item in cartItems"
                                :key="item.id"
                                class="row mb-3 pb-3 border-bottom align-items-center"
                            >
                                <div class="col-md-2">
                                    <img
                                        :src="
                                            item.imagen ||
                                            '/images/placeholder.png'
                                        "
                                        :alt="item.nombre"
                                        class="img-fluid rounded"
                                    />
                                </div>
                                <div class="col-md-4">
                                    <h6>{{ item.nombre }}</h6>
                                    <p class="text-muted small mb-0">
                                        {{ formatPrice(item.precio_unitario) }}
                                    </p>
                                    <p
                                        v-if="item.descuento > 0"
                                        class="text-success small mb-0"
                                    >
                                        Descuento:
                                        {{ formatPrice(item.descuento) }}
                                    </p>
                                </div>
                                <div class="col-md-2">
                                    <input
                                        type="number"
                                        :value="item.cantidad"
                                        @change="
                                            updateQuantity(
                                                item.id,
                                                $event.target.value
                                            )
                                        "
                                        class="form-control"
                                        min="1"
                                    />
                                </div>
                                <div class="col-md-2">
                                    <strong>{{
                                        formatPrice(item.precio_final)
                                    }}</strong>
                                </div>
                                <div class="col-md-2 text-end">
                                    <strong>{{
                                        formatPrice(item.subtotal)
                                    }}</strong>
                                    <button
                                        @click="removeFromCart(item.id)"
                                        class="btn btn-sm btn-outline-danger d-block w-100 mt-2"
                                    >
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen y pago -->
                <div class="col-lg-4">
                    <div class="card sticky-top" style="top: 20px">
                        <div class="card-header">
                            <h5 class="mb-0">Resumen de compra</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Subtotal:</span>
                                <strong>{{ formatPrice(cartTotal) }}</strong>
                            </div>
                            <hr />
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong class="fs-4">{{
                                    formatPrice(cartTotal)
                                }}</strong>
                            </div>

                            <!-- Método de pago -->
                            <div class="mb-3">
                                <label class="form-label"
                                    >Método de pago:</label
                                >
                                <select
                                    v-model="metodoPago"
                                    class="form-select"
                                >
                                    <option value="efectivo">Efectivo</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="transferencia">
                                        Transferencia
                                    </option>
                                </select>
                            </div>

                            <!-- Botones -->
                            <div class="d-grid gap-2">
                                <button
                                    @click="finalizarCompra"
                                    class="btn btn-primary btn-lg"
                                    :disabled="processingPago"
                                >
                                    <span v-if="processingPago">
                                        <span
                                            class="spinner-border spinner-border-sm me-2"
                                        ></span>
                                        Procesando...
                                    </span>
                                    <span v-else>
                                        <i class="bi bi-credit-card"></i> Pagar
                                        ahora
                                    </span>
                                </button>

                                <button
                                    @click="solicitarCredito"
                                    class="btn btn-outline-secondary"
                                >
                                    <i class="bi bi-clock-history"></i>
                                    Solicitar crédito
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
