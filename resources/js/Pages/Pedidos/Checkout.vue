<template>
    <AppLayout title="Checkout">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="bi bi-credit-card me-2"></i>Finalizar Compra
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Mensajes de Error -->
                <div
                    v-if="$page.props.errors.stock"
                    class="alert alert-danger alert-dismissible fade show mb-4"
                    role="alert"
                >
                    <i class="bi bi-x-circle me-2"></i
                    >{{ $page.props.errors.stock }}
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>
                </div>

                <div class="row">
                    <!-- Detalles del Pedido -->
                    <div class="col-lg-8">
                        <div
                            class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-4"
                        >
                            <div class="p-4 border-b border-gray-200">
                                <h4 class="font-semibold text-lg">
                                    Resumen de Productos
                                </h4>
                            </div>

                            <div class="p-4">
                                <div
                                    v-for="item in detalles"
                                    :key="item.producto.id"
                                    class="border-b pb-3 mb-3 last:border-b-0"
                                >
                                    <div class="row align-items-center">
                                        <div class="col-md-7">
                                            <h6 class="font-semibold mb-1">
                                                {{ item.producto.nombre }}
                                            </h6>
                                            <p
                                                class="text-sm text-gray-600 mb-0"
                                            >
                                                Cantidad: {{ item.cantidad }} ×
                                                ${{
                                                    item.precio_unitario.toFixed(
                                                        2
                                                    )
                                                }}
                                            </p>
                                            <span
                                                v-if="item.descuento > 0"
                                                class="badge bg-success"
                                            >
                                                Descuento: ${{
                                                    item.descuento.toFixed(2)
                                                }}
                                            </span>
                                        </div>
                                        <div class="col-md-5 text-end">
                                            <p class="font-semibold mb-0">
                                                ${{ item.subtotal.toFixed(2) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de Pago -->
                    <div class="col-lg-4">
                        <div
                            class="bg-white overflow-hidden shadow-xl sm:rounded-lg sticky-top"
                            style="top: 20px"
                        >
                            <div class="p-4 border-b border-gray-200">
                                <h4 class="font-semibold text-lg">
                                    Método de Pago
                                </h4>
                            </div>

                            <form @submit.prevent="procesarVenta" class="p-4">
                                <!-- Tipo de Venta -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold"
                                        >Tipo de Venta</label
                                    >
                                    <select
                                        v-model="form.tipo_venta"
                                        class="form-select"
                                        :class="{
                                            'is-invalid':
                                                form.errors.tipo_venta,
                                        }"
                                    >
                                        <option value="contado">Contado</option>
                                        <option value="credito">Crédito</option>
                                    </select>
                                    <div
                                        v-if="form.errors.tipo_venta"
                                        class="invalid-feedback"
                                    >
                                        {{ form.errors.tipo_venta }}
                                    </div>
                                </div>

                                <!-- Método de Pago -->
                                <div class="mb-3">
                                    <label class="form-label fw-semibold"
                                        >Método de Pago</label
                                    >
                                    <select
                                        v-model="form.metodo_pago_id"
                                        class="form-select"
                                        :class="{
                                            'is-invalid':
                                                form.errors.metodo_pago_id,
                                        }"
                                    >
                                        <option value="">Seleccione...</option>
                                        <option
                                            v-for="metodo in metodosPago"
                                            :key="metodo.id"
                                            :value="metodo.id"
                                        >
                                            {{ metodo.nombre }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.metodo_pago_id"
                                        class="invalid-feedback"
                                    >
                                        {{ form.errors.metodo_pago_id }}
                                    </div>
                                </div>

                                <!-- Campos adicionales para Crédito -->
                                <div
                                    v-if="form.tipo_venta === 'credito'"
                                    class="border-top pt-3 mt-3"
                                >
                                    <h6 class="font-semibold mb-3">
                                        Información del Crédito
                                    </h6>

                                    <!-- Meses de Plazo -->
                                    <div class="mb-3">
                                        <label class="form-label"
                                            >Meses de Plazo</label
                                        >
                                        <input
                                            type="number"
                                            v-model="form.meses_plazo"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.meses_plazo,
                                            }"
                                            min="1"
                                            max="36"
                                            @input="calcularCuota"
                                        />
                                        <div
                                            v-if="form.errors.meses_plazo"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.meses_plazo }}
                                        </div>
                                    </div>

                                    <!-- Tasa de Interés -->
                                    <div class="mb-3">
                                        <label class="form-label"
                                            >Tasa de Interés (%)</label
                                        >
                                        <input
                                            type="number"
                                            v-model="form.tasa_interes"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.tasa_interes,
                                            }"
                                            min="0"
                                            max="100"
                                            step="0.1"
                                            @input="calcularCuota"
                                        />
                                        <div
                                            v-if="form.errors.tasa_interes"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.tasa_interes }}
                                        </div>
                                    </div>

                                    <!-- Fecha Primer Pago -->
                                    <div class="mb-3">
                                        <label class="form-label"
                                            >Fecha Primer Pago</label
                                        >
                                        <input
                                            type="date"
                                            v-model="form.fecha_primer_pago"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors
                                                        .fecha_primer_pago,
                                            }"
                                            :min="minFechaPago"
                                        />
                                        <div
                                            v-if="form.errors.fecha_primer_pago"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.fecha_primer_pago }}
                                        </div>
                                    </div>

                                    <!-- Cálculo de Cuota -->
                                    <div
                                        v-if="cuotaCalculada > 0"
                                        class="alert alert-info"
                                    >
                                        <p class="mb-1">
                                            <strong>Cuota mensual:</strong> ${{
                                                cuotaCalculada.toFixed(2)
                                            }}
                                        </p>
                                        <p class="mb-0 text-sm">
                                            Total con interés: ${{
                                                totalConInteres.toFixed(2)
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <hr />

                                <!-- Resumen Total -->
                                <div class="mb-4">
                                    <div
                                        class="d-flex justify-content-between mb-2"
                                    >
                                        <span>Subtotal:</span>
                                        <span>${{ total.toFixed(2) }}</span>
                                    </div>
                                    <div
                                        v-if="
                                            form.tipo_venta === 'credito' &&
                                            totalConInteres > 0
                                        "
                                        class="d-flex justify-content-between mb-2 text-info"
                                    >
                                        <span>Interés:</span>
                                        <span
                                            >+${{
                                                (
                                                    totalConInteres - total
                                                ).toFixed(2)
                                            }}</span
                                        >
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <h5 class="font-semibold">
                                            Total a Pagar:
                                        </h5>
                                        <h5 class="font-semibold text-primary">
                                            ${{
                                                form.tipo_venta === "credito"
                                                    ? totalConInteres.toFixed(2)
                                                    : total.toFixed(2)
                                            }}
                                        </h5>
                                    </div>
                                </div>

                                <!-- Botones -->
                                <button
                                    type="submit"
                                    class="btn btn-success w-100 mb-2"
                                    :disabled="form.processing"
                                >
                                    <span v-if="form.processing">
                                        <span
                                            class="spinner-border spinner-border-sm me-2"
                                        ></span>
                                        Procesando...
                                    </span>
                                    <span v-else>
                                        <i class="bi bi-check-circle me-2"></i
                                        >Confirmar Compra
                                    </span>
                                </button>

                                <Link
                                    :href="route('carritos.index')"
                                    class="btn btn-outline-secondary w-100"
                                >
                                    <i class="bi bi-arrow-left me-2"></i>Volver
                                    al Carrito
                                </Link>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    detalles: Array,
    total: Number,
    metodosPago: Array,
});

// Fecha mínima para el primer pago (mañana)
const minFechaPago = computed(() => {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    return tomorrow.toISOString().split("T")[0];
});

// Form
const form = useForm({
    metodo_pago_id: "",
    tipo_venta: "contado",
    meses_plazo: 12,
    tasa_interes: 10,
    fecha_primer_pago: "",
});

// Cálculos
const cuotaCalculada = ref(0);
const totalConInteres = ref(0);

const calcularCuota = () => {
    if (
        form.tipo_venta === "credito" &&
        form.meses_plazo > 0 &&
        form.tasa_interes >= 0
    ) {
        const interes = (props.total * form.tasa_interes) / 100;
        totalConInteres.value = props.total + interes;
        cuotaCalculada.value = totalConInteres.value / form.meses_plazo;
    } else {
        cuotaCalculada.value = 0;
        totalConInteres.value = 0;
    }
};

// Procesar venta
const procesarVenta = () => {
    form.post(route("pedidos.store"), {
        preserveScroll: true,
    });
};
</script>

<style scoped>
.sticky-top {
    position: sticky;
}
</style>
