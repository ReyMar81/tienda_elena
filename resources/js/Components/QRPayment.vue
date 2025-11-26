<template>
    <div class="qr-payment-container">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <h5 class="mb-0">
                    <i class="bi bi-qr-code me-2"></i>
                    Escanea para Pagar
                </h5>
            </div>
            <div class="card-body text-center p-4">
                <!-- QR Code Image -->
                <div class="qr-image-container mb-3">
                    <img 
                        :src="qrImage" 
                        alt="Código QR para pago" 
                        class="qr-image"
                        style="max-width: 280px; width: 100%; height: auto; border: 3px solid #f0f0f0; border-radius: 8px; padding: 10px;"
                    />
                </div>

                <!-- Payment Info -->
                <div class="payment-info mb-3">
                    <div class="alert alert-info">
                        <h4 class="mb-2">
                            <strong>Bs. {{ formatMoney(monto) }}</strong>
                        </h4>
                        <p class="mb-0 small text-muted">
                            {{ descripcion || 'Escanea el código QR con tu app bancaria para completar el pago' }}
                        </p>
                    </div>
                </div>

                <!-- Transaction Info -->
                <div class="transaction-info text-muted small">
                    <p class="mb-1">
                        <strong>ID de Transacción:</strong><br>
                        <code class="text-dark">{{ transactionId }}</code>
                    </p>
                    <p v-if="expiration" class="mb-0">
                        <i class="bi bi-clock me-1"></i>
                        Expira: {{ formatExpiration(expiration) }}
                    </p>
                </div>

                <!-- Status Badge -->
                <div class="mt-3">
                    <span 
                        class="badge" 
                        :class="statusBadgeClass"
                    >
                        <i class="bi" :class="statusIcon"></i>
                        {{ statusText }}
                    </span>
                </div>

                <!-- Refresh Button (if pending) -->
                <div v-if="status === 'pending' && showRefreshButton" class="mt-3">
                    <button 
                        @click="checkPaymentStatus"
                        class="btn btn-outline-primary btn-sm"
                        :disabled="checking"
                    >
                        <i class="bi bi-arrow-clockwise me-1" :class="{ 'spin': checking }"></i>
                        {{ checking ? 'Verificando...' : 'Verificar Pago' }}
                    </button>
                </div>

                <!-- Simulate Payment Button (ONLY FOR DEVELOPMENT) -->
                <div v-if="showSimulateButton && status === 'pending'" class="mt-3">
                    <button 
                        @click="simulatePayment"
                        class="btn btn-warning btn-sm"
                        :disabled="simulating"
                    >
                        <i class="bi bi-lightning-fill me-1"></i>
                        {{ simulating ? 'Simulando...' : 'Simular Pago (DEV)' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    qrImage: {
        type: String,
        required: true
    },
    transactionId: {
        type: String,
        required: true
    },
    monto: {
        type: [Number, String],
        required: true
    },
    descripcion: {
        type: String,
        default: ''
    },
    status: {
        type: String,
        default: 'pending' // 'pending', 'completed', 'failed'
    },
    expiration: {
        type: String,
        default: null
    },
    showRefreshButton: {
        type: Boolean,
        default: true
    },
    showSimulateButton: {
        type: Boolean,
        default: true // Cambiar a false en producción
    },
    onStatusChange: {
        type: Function,
        default: null
    }
});

const checking = ref(false);
const simulating = ref(false);

const statusBadgeClass = computed(() => {
    const classes = {
        pending: 'bg-warning text-dark',
        completed: 'bg-success',
        failed: 'bg-danger'
    };
    return classes[props.status] || 'bg-secondary';
});

const statusIcon = computed(() => {
    const icons = {
        pending: 'bi-hourglass-split',
        completed: 'bi-check-circle-fill',
        failed: 'bi-x-circle-fill'
    };
    return icons[props.status] || 'bi-question-circle';
});

const statusText = computed(() => {
    const texts = {
        pending: 'Pendiente de Pago',
        completed: 'Pago Confirmado',
        failed: 'Pago Fallido'
    };
    return texts[props.status] || 'Desconocido';
});

const formatMoney = (amount) => {
    return parseFloat(amount || 0).toFixed(2);
};

const formatExpiration = (datetime) => {
    if (!datetime) return '';
    const date = new Date(datetime);
    return date.toLocaleString('es-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const checkPaymentStatus = async () => {
    checking.value = true;
    try {
        // Aquí llamarías a tu endpoint para verificar el estado
        // Por ahora solo recargamos la página
        window.location.reload();
    } catch (error) {
        console.error('Error al verificar estado:', error);
    } finally {
        checking.value = false;
    }
};

const simulatePayment = async () => {
    if (!confirm('¿Simular pago completado? (Solo para desarrollo)')) {
        return;
    }

    simulating.value = true;
    try {
        const response = await axios.post('/pagofacil-simulado/confirmar-pago', {
            transaction_id: props.transactionId
        });

        if (response.data.success) {
            alert('Pago simulado exitosamente. Recargando página...');
            window.location.reload();
        }
    } catch (error) {
        console.error('Error al simular pago:', error);
        alert('Error al simular pago: ' + (error.response?.data?.error || error.message));
    } finally {
        simulating.value = false;
    }
};
</script>

<style scoped>
.qr-payment-container {
    max-width: 400px;
    margin: 0 auto;
}

.qr-image-container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

code {
    font-size: 0.75rem;
    word-break: break-all;
}
</style>
