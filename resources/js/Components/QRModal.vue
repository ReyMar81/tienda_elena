<template>
    <Teleport to="body">
        <div
            class="modal fade show d-block"
            tabindex="-1"
            style="background: rgba(0, 0, 0, 0.5)"
        >
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Pago con QR - Próximamente</h5>
                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            @click="$emit('close')"
                        ></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong
                                >Funcionalidad Próximamente Disponible</strong
                            >
                            <p class="mb-0 mt-2">
                                La integración con pasarelas de pago (QR y Pago
                                Fácil) estará disponible próximamente.
                            </p>
                        </div>

                        <div v-if="qrData" class="my-4">
                            <h6>Simulación de QR</h6>
                            <div class="border p-3 bg-light">
                                <img
                                    :src="qrData.qr_code"
                                    alt="QR Code"
                                    class="img-fluid"
                                    style="max-width: 200px"
                                />
                            </div>
                            <small class="text-muted d-block mt-2"
                                >ID de transacción:
                                {{ qrData.transaction_id }}</small
                            >
                            <small class="text-muted d-block"
                                >Monto: Bs.
                                {{ formatMoney(qrData.monto) }}</small
                            >
                        </div>

                        <div v-else class="my-4">
                            <button
                                class="btn btn-primary"
                                @click="generarQR"
                                :disabled="loading"
                            >
                                <span
                                    v-if="loading"
                                    class="spinner-border spinner-border-sm me-2"
                                ></span>
                                Generar QR Simulado
                            </button>
                        </div>

                        <div class="alert alert-info mt-3">
                            <small>
                                <strong>Nota:</strong> Este es un QR simulado
                                con fines demostrativos. Para realizar pagos
                                reales, contacte con un vendedor o propietario.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            @click="$emit('close')"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";

const props = defineProps({
    cuota: Object,
});

const emit = defineEmits(["close"]);

const qrData = ref(null);
const loading = ref(false);

const generarQR = async () => {
    loading.value = true;
    try {
        const response = await axios.post(route("pagos.generar-qr"), {
            cuota_id: props.cuota.id,
            monto: props.cuota.monto_pendiente + (props.cuota.mora || 0),
        });
        qrData.value = response.data;
    } catch (error) {
        console.error("Error generando QR:", error);
    } finally {
        loading.value = false;
    }
};

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);
</script>
