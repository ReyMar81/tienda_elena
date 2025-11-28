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
                <!-- Integración PagoFácil QR -->
                <div v-if="isMetodoQrSelected" class="mb-3">
                    <div v-if="!qrImage">
                        <button
                            type="button"
                            class="btn btn-outline-primary"
                            @click="generarQr"
                            :disabled="generatingQr"
                        >
                            <i class="bi bi-qr-code me-2"></i>
                            {{
                                generatingQr
                                    ? "Generando QR..."
                                    : "Generar QR (PagoFácil)"
                            }}
                        </button>
                    </div>

                    <div v-else class="mt-3">
                        <p class="mb-2">
                            Escanee este código QR con su app bancaria:
                        </p>
                        <div class="d-flex justify-content-center mb-2">
                            <img
                                :src="qrImage"
                                alt="QR Pago"
                                style="max-width: 260px"
                            />
                        </div>
                        <div class="d-flex gap-2 justify-content-center">
                            <button
                                type="button"
                                class="btn btn-success"
                                @click="verificarEstado(true)"
                                :disabled="verifying"
                            >
                                <i class="bi bi-check2-circle me-2"></i>
                                {{
                                    verifying
                                        ? "Verificando..."
                                        : "Verificar pago"
                                }}
                            </button>
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                @click="
                                    qrImage = null;
                                    if (pollInterval) {
                                        clearInterval(pollInterval);
                                        pollInterval = null;
                                    }
                                "
                            >
                                <i class="bi bi-x-circle me-2"></i>
                                Cancelar QR
                            </button>
                        </div>
                        <p class="text-muted mt-2">
                            Estado: {{ verifyStatus || "pendiente" }}
                        </p>
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
import axios from "axios";
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

// Estado para integración con PagoFácil QR
const qrImage = ref(null);
const qrPagoId = ref(null);
const qrTransactionId = ref(null);
const generatingQr = ref(false);
const verifying = ref(false);
const verifyStatus = ref(null);
let pollInterval = null;

const isMetodoQrSelected = computed(() => {
    const metodo = props.metodosPago?.find(
        (m) => String(m.id) === String(form.metodo_pago_id)
    );
    return metodo && String(metodo.nombre).toLowerCase().includes("qr");
});

const getCsrfToken = () =>
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") || "";

const generarQr = async () => {
    if (!form.cuota_id || !form.monto) {
        alert("Seleccione una cuota y monto antes de generar el QR");
        return;
    }

    generatingQr.value = true;
    qrImage.value = null;
    qrPagoId.value = null;
    qrTransactionId.value = null;

    try {
        const cuotaId = form.cuota_id;
        // route is declared inside a `prefix('carrito')->name('cart.')` group,
        // so the actual route name is `cart.cuotas.generar-qr`.
        const url = route("cart.cuotas.generar-qr", cuotaId);

        const resp = await axios.post(url, { monto: form.monto });

        const data = resp.data;

        qrImage.value = data.qr_image;
        qrPagoId.value = data.pago_id;
        qrTransactionId.value = data.transaction_id;
        verifyStatus.value = "pending";

        // empezar polling automático cada 5s
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(() => verificarEstado(), 5000);
    } catch (e) {
        console.error("Error generando QR:", e);
        // Manejar errores de axios y errores de validación
        const msg =
            e.response?.data?.error || e.response?.data?.message || e.message;
        alert("Error generando QR: " + (msg || e));
    } finally {
        generatingQr.value = false;
    }
};

const verificarEstado = async (manual = false) => {
    if (!qrPagoId.value) return;
    verifying.value = true;

    try {
        // the named route is prefixed with `cart.` (see routes/web.php)
        const url = route("cart.pagos.verificar-estado", qrPagoId.value);
        const resp = await axios.get(url);
        const data = resp.data;
        verifyStatus.value = data.status;

        if (data.status === "completed") {
            // Detener polling
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
            // Cerrar modal y refrescar página para reflejar cambios
            closeModal();
            // Recargar la página para mostrar el pago aplicado (si la app espera esto)
            setTimeout(() => location.reload(), 300);
        } else if (manual) {
            alert("Estado actual: " + data.status);
        }
    } catch (e) {
        console.error("Error verificando estado:", e);
    } finally {
        verifying.value = false;
    }
};

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
    // limpiar estado de QR si existe
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
    qrImage.value = null;
    qrPagoId.value = null;
    qrTransactionId.value = null;
    verifyStatus.value = null;
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
