<template>
    <div
        class="search-results position-absolute top-100 start-0 w-100 mt-2 card shadow-lg"
        style="z-index: 1050; max-height: 500px; overflow-y: auto"
    >
        <div class="card-body p-0">
            <!-- Productos -->
            <div v-if="productos.length > 0" class="border-bottom">
                <h6 class="px-3 pt-3 pb-2 mb-0 text-muted small">
                    <i class="bi bi-box-seam me-2"></i>Productos
                </h6>
                <div class="list-group list-group-flush">
                    <button
                        v-for="producto in productos"
                        :key="producto.id"
                        class="list-group-item list-group-item-action d-flex align-items-center"
                        @click="navigateTo('productos', producto.id)"
                    >
                        <div class="flex-shrink-0 me-3">
                            <div
                                class="rounded bg-light d-flex align-items-center justify-content-center"
                                style="width: 40px; height: 40px"
                            >
                                <i
                                    v-if="!producto.imagen"
                                    class="bi bi-image text-muted"
                                ></i>
                                <img
                                    v-else
                                    :src="producto.imagen"
                                    :alt="producto.nombre"
                                    class="rounded"
                                    style="
                                        width: 40px;
                                        height: 40px;
                                        object-fit: cover;
                                    "
                                />
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div
                                class="d-flex justify-content-between align-items-start"
                            >
                                <div>
                                    <h6 class="mb-0">{{ producto.nombre }}</h6>
                                    <small class="text-muted"
                                        >{{ producto.categoria }} • Cod:
                                        {{ producto.codigo }}</small
                                    >
                                </div>
                                <div class="text-end">
                                    <strong class="text-success"
                                        >Bs.
                                        {{
                                            formatMoney(producto.precio)
                                        }}</strong
                                    >
                                    <br />
                                    <small class="text-muted"
                                        >Stock: {{ producto.stock }}</small
                                    >
                                </div>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Promociones -->
            <div v-if="promociones.length > 0">
                <h6 class="px-3 pt-3 pb-2 mb-0 text-muted small">
                    <i class="bi bi-tags me-2"></i>Promociones
                </h6>
                <div class="list-group list-group-flush">
                    <button
                        v-for="promocion in promociones"
                        :key="promocion.id"
                        class="list-group-item list-group-item-action"
                        @click="navigateTo('promociones', promocion.id)"
                    >
                        <div
                            class="d-flex justify-content-between align-items-start"
                        >
                            <div class="flex-grow-1">
                                <h6 class="mb-1">🎉 {{ promocion.nombre }}</h6>
                                <p class="mb-1 small text-muted">
                                    {{ promocion.descripcion }}
                                </p>
                                <small class="text-muted">
                                    Válido hasta:
                                    {{ formatDate(promocion.fecha_fin) }}
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-danger">
                                    <span v-if="promocion.descuento_porcentaje">
                                        -{{ promocion.descuento_porcentaje }}%
                                    </span>
                                    <span v-else>
                                        -Bs.
                                        {{
                                            formatMoney(
                                                promocion.descuento_monto
                                            )
                                        }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from "@inertiajs/vue3";

const props = defineProps({
    productos: Array,
    promociones: Array,
});

const emit = defineEmits(["close"]);

const navigateTo = (type, id) => {
    // Por ahora solo cierra el dropdown
    // En el futuro se puede implementar navegación real
    emit("close");

    // Ejemplo de navegación (descomentar cuando existan las rutas)
    // if (type === 'productos') {
    //     router.visit(route('productos.show', id));
    // } else if (type === 'promociones') {
    //     router.visit(route('promociones.show', id));
    // }
};

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);
const formatDate = (date) => new Date(date).toLocaleDateString("es-ES");
</script>

<style scoped>
.search-results {
    background-color: var(--card-bg, #ffffff);
    color: var(--text-primary, #212121);
    border-color: var(--border-color, #dee2e6);
}

.list-group-item {
    background-color: var(--card-bg, #ffffff);
    color: var(--text-primary, #212121);
    border-color: var(--border-color, #dee2e6);
}

.list-group-item:hover {
    background-color: var(--bg-secondary, #f8f9fa);
}
</style>
