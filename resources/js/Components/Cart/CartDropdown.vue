<script setup>
import { computed } from "vue";
import { useCart } from "@/composables/useCart";

const {
    cartItems,
    cartTotal,
    cartCount,
    removeFromCart,
    updateQuantity,
    isLoading,
} = useCart();

const formatPrice = (price) => `Bs. ${parseFloat(price).toFixed(2)}`;

const emit = defineEmits(["close"]);
</script>

<template>
    <div class="dropdown">
        <button
            class="btn btn-outline-primary position-relative"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
        >
            <i class="bi bi-cart3"></i>
            <span
                v-if="cartCount > 0"
                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
            >
                {{ cartCount }}
            </span>
        </button>

        <div
            class="dropdown-menu dropdown-menu-end p-3"
            style="min-width: 350px; max-height: 500px; overflow-y: auto"
        >
            <h6 class="dropdown-header">Mi Carrito</h6>

            <div v-if="isLoading" class="text-center py-3">
                <div class="spinner-border spinner-border-sm"></div>
                <span class="ms-2">Cargando...</span>
            </div>

            <div
                v-else-if="cartItems.length === 0"
                class="text-center text-muted py-3"
            >
                <i class="bi bi-cart-x fs-1"></i>
                <p class="mb-0">Tu carrito está vacío</p>
            </div>

            <div v-else>
                <!-- Items del carrito -->
                <div
                    v-for="item in cartItems"
                    :key="item.id"
                    class="mb-3 pb-3 border-bottom"
                >
                    <div class="d-flex align-items-start">
                        <img
                            :src="item.imagen || '/images/placeholder.png'"
                            :alt="item.nombre"
                            class="me-2"
                            style="
                                width: 50px;
                                height: 50px;
                                object-fit: cover;
                                border-radius: 4px;
                            "
                        />
                        <div class="flex-grow-1">
                            <h6 class="mb-1 small">{{ item.nombre }}</h6>
                            <div class="d-flex align-items-center mb-1">
                                <input
                                    type="number"
                                    :value="item.cantidad"
                                    @change="
                                        updateQuantity(
                                            item.id,
                                            $event.target.value
                                        )
                                    "
                                    class="form-control form-control-sm me-2"
                                    min="1"
                                    style="width: 60px"
                                />
                                <small class="text-muted"
                                    >×
                                    {{ formatPrice(item.precio_final) }}</small
                                >
                            </div>
                            <div
                                class="d-flex justify-content-between align-items-center"
                            >
                                <strong class="small">{{
                                    formatPrice(item.subtotal)
                                }}</strong>
                                <button
                                    @click="removeFromCart(item.id)"
                                    class="btn btn-sm btn-outline-danger"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div
                    class="d-flex justify-content-between align-items-center mb-3"
                >
                    <strong>Total:</strong>
                    <strong class="fs-5">{{ formatPrice(cartTotal) }}</strong>
                </div>

                <!-- Botones -->
                <div class="d-grid gap-2">
                    <a :href="route('cart.page')" class="btn btn-primary">
                        Ver carrito completo
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
