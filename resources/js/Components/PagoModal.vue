<template>
    <DialogModal :show="show" @close="closeModal" max-width="md">
        <template #title>
            <i class="bi bi-cash-coin me-2"></i>
            Registrar Pago de Cuota
        </template>

        <template #content>
            <form @submit.prevent="confirmar">
                <div class="mb-3">
                    <label class="form-label fw-bold">Cuota a Pagar</label>
                    <select
                        v-model="form.cuota_id"
                        class="form-select"
                        :class="{ 'is-invalid': form.errors.cuota_id }"
                        :disabled="!!cuotaPreseleccionada"
                        required
                    >
                        <option value="">Seleccione una cuota...</option>
                        <option
                            v-for="cuota in cuotasPendientes"
                            :key="cuota.id"
                            :value="cuota.id"
                        >
                            Cuota #{{ cuota.numero_cuota }} - Pendiente:
                            {{ formatearMoneda(cuota.monto_pendiente) }}
                            (Vence:
                            {{ formatearFecha(cuota.fecha_vencimiento) }})
                        </option>
                    </select>
                    <div v-if="form.errors.cuota_id" class="invalid-feedback">
                        {{ form.errors.cuota_id }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"
                            >Monto a Pagar *</label
                        >
                        <input
                            v-model="form.monto"
                            type="number"
                            step="0.01"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.monto }"
                            :max="montoPendienteCuota"
                            required
                        />
                        <small v-if="cuotaSeleccionada" class="text-muted">
                            Máximo:
                            {{
                                formatearMoneda(
                                    cuotaSeleccionada.monto_pendiente
                                )
                            }}
                        </small>
                        <div v-if="form.errors.monto" class="invalid-feedback">
                            {{ form.errors.monto }}
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"
                            >Fecha de Pago *</label
                        >
                        <input
                            v-model="form.fecha"
                            type="date"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.fecha }"
                            :max="new Date().toISOString().split('T')[0]"
                            required
                        />
                        <div v-if="form.errors.fecha" class="invalid-feedback">
                            {{ form.errors.fecha }}
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Método de Pago *</label>
                    <select
                        v-model="form.metodo_pago_id"
                        class="form-select"
                        :class="{ 'is-invalid': form.errors.metodo_pago_id }"
                        required
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
            </form>
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal">
                <i class="bi bi-x-circle me-2"></i>
                Cancelar
            </SecondaryButton>

            <PrimaryButton
                class="ms-3"
                @click="confirmar"
                :disabled="form.processing"
            >
                <i class="bi bi-check-circle me-2"></i>
                Registrar Pago
            </PrimaryButton>
        </template>
    </DialogModal>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import DialogModal from "@/Components/DialogModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";

const props = defineProps({
    show: Boolean,
    cuotas: Array,
    metodosPago: Array,
    cuotaPreseleccionada: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(["close"]);

const form = useForm({
    cuota_id: "",
    metodo_pago_id: "",
    monto: "",
    fecha: new Date().toISOString().split("T")[0],
    // observaciones eliminado
});

// Preseleccionar cuota si se pasa
watch(
    () => props.cuotaPreseleccionada,
    (newVal) => {
        if (newVal) {
            form.cuota_id = newVal;
            const cuota = props.cuotas?.find((c) => c.id === newVal);
            if (cuota) {
                form.monto = cuota.monto_pendiente;
            }
        }
    },
    { immediate: true }
);

const cuotasPendientes = computed(() => {
    return (
        props.cuotas?.filter(
            (c) => c.estado === "pendiente" || c.estado === "vencido"
        ) || []
    );
});

const cuotaSeleccionada = computed(() => {
    return props.cuotas?.find((c) => c.id === form.cuota_id);
});

const montoPendienteCuota = computed(() => {
    return cuotaSeleccionada.value?.monto_pendiente || 0;
});

watch(
    () => form.cuota_id,
    (newVal) => {
        if (newVal) {
            const cuota = props.cuotas?.find((c) => c.id === newVal);
            if (cuota) {
                form.monto = cuota.monto_pendiente;
            }
        }
    }
);

const formatearMoneda = (valor) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
        minimumFractionDigits: 2,
    }).format(valor);
};

const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
};

const closeModal = () => {
    form.reset();
    form.clearErrors();
    emit("close");
};

const confirmar = () => {
    // Validación antes de enviar
    if (!form.cuota_id) {
        alert("Debe seleccionar una cuota");
        return;
    }
    if (!form.metodo_pago_id) {
        alert("Debe seleccionar un método de pago");
        return;
    }
    if (!form.monto || form.monto <= 0) {
        alert("Debe ingresar un monto válido");
        return;
    }
    if (!form.fecha) {
        alert("Debe seleccionar la fecha de pago");
        return;
    }

    form.post(route("creditos.registrar-pago"), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
        onError: (errors) => {
            console.error("Errores al registrar pago:", errors);
        },
    });
};
</script>
