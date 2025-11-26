<template>
    <DialogModal :show="show" @close="closeModal">
        <template #title>
            <i class="bi bi-credit-card me-2"></i>
            Configurar Pago a Crédito
        </template>

        <template #content>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                Este pedido será dividido en cuotas mensuales. Cada cuota
                vencerá mensualmente desde la fecha de confirmación.
            </div>

            <div class="mb-3">
                <h5 class="fw-bold">Total del Pedido</h5>
                <p class="fs-4 text-primary mb-0">
                    {{ formatearMoneda(total) }}
                </p>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold"> Número de Cuotas * </label>
                <input
                    v-model.number="numeroCuotas"
                    type="number"
                    min="1"
                    max="12"
                    class="form-control form-control-lg"
                    @input="calcularMontoCuota"
                />
                <small class="text-muted"
                    >Entre 1 y 12 cuotas (cada cuota es mensual)</small
                >
            </div>

            <div class="bg-light p-3 rounded">
                <div class="row">
                    <div class="col-6">
                        <strong>Número de Cuotas:</strong>
                    </div>
                    <div class="col-6 text-end">
                        {{ numeroCuotas }} cuotas (mensuales)
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-6">
                        <strong>Monto por Cuota:</strong>
                    </div>
                    <div class="col-6 text-end fs-5 text-success fw-bold">
                        {{ formatearMoneda(montoCuota) }}
                    </div>
                </div>
            </div>

            <div v-if="errorMessage" class="alert alert-danger mt-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ errorMessage }}
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal">
                <i class="bi bi-x-circle me-2"></i>
                Cancelar
            </SecondaryButton>

            <PrimaryButton class="ms-3" @click="confirmar">
                <i class="bi bi-check-circle me-2"></i>
                Confirmar Crédito
            </PrimaryButton>
        </template>
    </DialogModal>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import DialogModal from "@/Components/DialogModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    total: {
        type: Number,
        required: true,
    },
    cuotasIniciales: {
        type: Number,
        default: 3,
    },
});

const emit = defineEmits(["close", "confirmar"]);

const numeroCuotas = ref(props.cuotasIniciales);
const montoCuota = ref(0);
const errorMessage = ref("");

watch(
    () => props.show,
    (newVal) => {
        if (newVal) {
            numeroCuotas.value = props.cuotasIniciales;
            calcularMontoCuota();
            errorMessage.value = "";
        }
    }
);

const calcularMontoCuota = () => {
    if (numeroCuotas.value > 0) {
        montoCuota.value = props.total / numeroCuotas.value;
        errorMessage.value = "";
    } else {
        montoCuota.value = 0;
    }
};

const formatearMoneda = (valor) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
        minimumFractionDigits: 2,
    }).format(valor);
};

const closeModal = () => {
    errorMessage.value = "";
    emit("close");
};

const confirmar = () => {
    if (
        !numeroCuotas.value ||
        numeroCuotas.value < 1 ||
        numeroCuotas.value > 12
    ) {
        errorMessage.value = "El número de cuotas debe ser entre 1 y 12";
        return;
    }

    emit("confirmar", numeroCuotas.value);
    closeModal();
};

// Calcular inicial
calcularMontoCuota();
</script>
