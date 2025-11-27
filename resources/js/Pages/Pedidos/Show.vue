<template>
    <AppLayout title="Detalle del Pedido">
        <div class="container py-4">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">
                        <i class="bi bi-bag me-2"></i>
                        Detalle del Pedido #{{ pedido.id }}
                    </h2>
                    <p class="text-muted">Información completa del pedido</p>
                </div>
            </div>

            <!-- Información General + Cliente -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 pb-1">
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="bi bi-info-circle me-2"></i>
                        Información General del Pedido
                    </h5>
                </div>

                <div class="card-body pt-3">
                    <div class="row">
                        <!-- Información General -->
                        <div class="col-md-6 border-end">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold">N° Pedido:</td>
                                        <td>#{{ pedido.id }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Fecha:</td>
                                        <td>{{ formatearFecha(pedido.created_at) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Estado:</td>
                                        <td>
                                            <span 
                                                :class="
                                                    pedido.tipo_pago === 'credito' && pedido.credito && pedido.credito.estado === 'pendiente'
                                                        ? 'badge bg-warning text-dark'
                                                        : getBadgeClass(pedido.estado)
                                                " 
                                                class="badge"
                                            >
                                                {{ 
                                                    pedido.tipo_pago === 'credito' && pedido.credito && pedido.credito.estado === 'pendiente'
                                                        ? `Crédito (${pedido.credito.cuotas.filter(c => c.estado === 'pagado').length}/${pedido.credito.cuotas.length})`
                                                        : pedido.estado.toUpperCase() 
                                                }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Tipo de Pago:</td>
                                        <td>
                                            <span
                                                :class="pedido.tipo_pago === 'credito'
                                                    ? 'badge bg-info'
                                                    : 'badge bg-success'"
                                            >
                                                {{ pedido.tipo_pago === 'credito' ? 'A Crédito' : 'Al Contado' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Método de Pago:</td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ pedido.metodo_pago?.nombre || pedido.metodoPago?.nombre || 'N/A' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="pedido.observaciones">
                                        <td class="fw-bold">Observaciones:</td>
                                        <td>{{ pedido.observaciones }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Información del Cliente -->
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold">Cliente:</td>
                                        <td>{{ pedido.user?.nombre || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email:</td>
                                        <td>{{ pedido.user?.email || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Teléfono:</td>
                                        <td>{{ pedido.user?.telefono || 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Dirección:</td>
                                        <td>{{ pedido.user?.direccion || 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Código QR para Pago (al contado: QR del pedido, a crédito: QR de la primera cuota) -->
            <div v-if="pedido.estado === 'pendiente' && (qrCuota || pedido.pago_facil_qr_image)" class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-warning text-dark border-0">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-qr-code me-2"></i>
                        Escanea el QR para Pagar
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <QRPayment
                                v-if="qrCuota"
                                :qr-image="qrCuota.qr_image"
                                :transaction-id="qrCuota.transaction_id"
                                :monto="qrCuota.monto"
                                :descripcion="`Pago de la primera cuota del pedido #${pedido.numero_venta}`"
                                :status="qrCuota.status || 'pending'"
                            />
                            <QRPayment
                                v-else
                                :qr-image="pedido.pago_facil_qr_image"
                                :transaction-id="pedido.pago_facil_transaction_id"
                                :monto="pedido.total"
                                :descripcion="`Pedido #${pedido.numero_venta}`"
                                :status="pedido.pago_facil_status || 'pending'"
                            />
                        </div>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Instrucciones:</strong> Escanea el código QR con tu app bancaria para completar el pago. 
                        <span v-if="qrCuota">Este QR corresponde a la primera cuota. Una vez pagada, se registrará como cuota pagada en el crédito.</span>
                        <span v-else>Una vez confirmado el pago, el pedido pasará automáticamente a estado "Pagado".</span>
                    </div>
                </div>
            </div>

            <!-- Productos del Pedido -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-box-seam me-2"></i>
                        Productos del Pedido
                    </h5>
                </div>

                <div class="card-body pt-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th class="text-end">Precio Unit.</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Descuento</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="detalle in pedido.detalles"
                                    :key="detalle.id"
                                >
                                    <td>
                                        <div class="fw-bold">
                                            {{ detalle.producto?.nombre || "Producto no disponible" }}
                                        </div>
                                        <small class="text-muted">
                                            {{ detalle.producto?.descripcion || '' }}
                                        </small>
                                    </td>
                                    <td>
                                        {{ detalle.producto?.categoria?.nombre || "N/A" }}
                                    </td>
                                    <td class="text-end">
                                        {{ formatearMoneda(detalle.precio_unitario) }}
                                    </td>
                                    <td class="text-center">
                                        {{ detalle.cantidad }}
                                    </td>
                                    <td class="text-end">
                                        {{ formatearMoneda(detalle.descuento * detalle.cantidad) }}
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ formatearMoneda(detalle.subtotal) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">TOTAL:</td>
                                    <td class="text-end fw-bold fs-5">
                                        {{ formatearMoneda(pedido.total) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div
                v-if="pedido.estado === 'pendiente' || (pedido.estado === 'pagado' && pedido.origen === 'online')"
                class="card shadow-sm border-0 mb-4"
            >
                <div class="card-header bg-white border-0">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-gear me-2"></i>
                        Acciones del Pedido
                    </h5>
                </div>

                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3">

                        <button v-if="pedido.estado === 'pendiente'" @click="confirmarPedido" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>
                            Confirmar Pedido
                        </button>

                        <button v-if="pedido.estado === 'pagado' && pedido.origen === 'online'" @click="marcarEnviado" class="btn btn-primary">
                            <i class="bi bi-truck me-1"></i>
                            Marcar como Enviado
                        </button>

                        <button v-if="pedido.estado === 'pendiente'" @click="cancelarPedido" class="btn btn-danger">
                            <i class="bi bi-x-circle me-1"></i>
                            Cancelar Pedido
                        </button>

                        <Link
                            v-if="pedido.estado === 'pendiente'"
                            :href="route('pedidos.edit', pedido.id)"
                            class="btn btn-warning"
                        >
                            <i class="bi bi-pencil me-1"></i>
                            Editar Pedido
                        </Link>

                        <Link
                            :href="route('pedidos.index')"
                            class="btn btn-secondary"
                        >
                            <i class="bi bi-arrow-left me-1"></i>
                            Volver a Pedidos
                        </Link>
                    </div>

                    <div
                        v-if="pedido.tipo_pago === 'credito'"
                        class="alert alert-info mt-4"
                    >
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Nota:</strong> Al confirmar este pedido se generará automáticamente un crédito.
                    </div>
                </div>
            </div>


            <!-- Ya procesado -->
            <div v-else class="card shadow-sm border-0">
                <div class="card-body">
                    <div
                        :class="
                            pedido.tipo_pago === 'credito' && pedido.credito && pedido.credito.estado === 'pendiente'
                                ? 'alert alert-info'
                                : (pedido.estado === 'pagado' || pedido.estado === 'enviado' || pedido.estado === 'completada'
                                    ? 'alert alert-success'
                                    : 'alert alert-warning')
                        "
                    >
                        <i
                            :class="
                                pedido.tipo_pago === 'credito' && pedido.credito && pedido.credito.estado === 'pendiente'
                                    ? 'bi bi-clock-history'
                                    : (pedido.estado === 'pagado' || pedido.estado === 'enviado' || pedido.estado === 'completada'
                                        ? 'bi bi-check-circle'
                                        : 'bi bi-exclamation-triangle')
                            "
                            class="me-2"
                        ></i>

                        <strong>
                            {{
                                pedido.tipo_pago === 'credito' && pedido.credito && pedido.credito.estado === 'pendiente'
                                    ? `Crédito en Curso (${pedido.credito.cuotas.filter(c => c.estado === 'pagado').length}/${pedido.credito.cuotas.length} cuotas pagadas)`
                                    : (pedido.estado === 'pagado' ? 'Pedido Pagado'
                                    : pedido.estado === 'enviado' ? 'Pedido Enviado'
                                    : pedido.estado === 'completada' ? 'Pedido Confirmado'
                                    : pedido.estado === 'anulado' ? 'Pedido Cancelado'
                                    : 'Pedido Procesado')
                            }}
                        </strong>
                        - {{ 
                            pedido.tipo_pago === 'credito' && pedido.credito && pedido.credito.estado === 'pendiente'
                                ? 'El pedido fue entregado pero el crédito sigue pendiente de pago.'
                                : 'Este pedido ya fue procesado y no se puede modificar.'
                        }}
                    </div>

                    <div class="d-flex gap-2">
                        <Link
                            :href="route('pedidos.index')"
                            class="btn btn-secondary"
                        >
                            <i class="bi bi-arrow-left me-1"></i>
                            Volver a Pedidos
                        </Link>

                        <Link
                            v-if="pedido.tipo_pago === 'credito' && pedido.credito"
                            :href="route('creditos.show', pedido.credito.id)"
                            class="btn btn-info text-white"
                        >
                            <i class="bi bi-credit-card me-1"></i>
                            Ver Detalle del Crédito
                        </Link>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal -->
        <CreditoModal
            :show="mostrarModalCredito"
            :total="pedido.total"
            :cuotas-iniciales="3"
            @close="mostrarModalCredito = false"
            @confirmar="confirmarConCuotas"
        />
    </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import CreditoModal from "@/Components/CreditoModal.vue";
import QRPayment from "@/Components/QRPayment.vue";

const props = defineProps({
    pedido: {
        type: Object,
        required: true,
    },
    qrCuota: {
        type: Object,
        required: false,
        default: null,
    },
});

const mostrarModalCredito = ref(false);

const formatearFecha = (fecha) => {
    return new Date(fecha).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatearMoneda = (valor) => {
    return new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
        minimumFractionDigits: 2,
    }).format(valor);
};

const getBadgeClass = (estado) => {
    const clases = {
        pendiente: "bg-warning",
        pagado: "bg-success",
        anulado: "bg-danger",
    };
    return `badge ${clases[estado] || "bg-secondary"}`;
};

const confirmarPedido = () => {
    const esCredito = props.pedido.tipo_pago === "credito";

    if (esCredito) {
        mostrarModalCredito.value = true;
        return;
    }

    if (!confirm("¿Está seguro de confirmar este pedido?")) return;

    router.patch(
        route("pedidos.accion", props.pedido.id),
        { accion: "confirmar" },
        { onError: (errors) => alert(Object.values(errors).join(", ")) }
    );
};

const confirmarConCuotas = (numeroCuotas) => {
    router.patch(
        route("pedidos.accion", props.pedido.id),
        {
            accion: "confirmar",
            numero_cuotas: numeroCuotas,
        },
        {
            onError: (errors) => alert(Object.values(errors).join(", ")),
            onFinish: () => (mostrarModalCredito.value = false),
        }
    );
};

const cancelarPedido = () => {
    if (!confirm("¿Está seguro de que desea cancelar este pedido?")) return;

    router.patch(
        route("pedidos.accion", props.pedido.id),
        { accion: "cancelar" },
        {
            onError: (errors) => alert(Object.values(errors).join(", ")),
        }
    );
};

const marcarEnviado = () => {
    if (!confirm("¿Está seguro de marcar este pedido como enviado?")) return;

    router.patch(
        route("pedidos.marcar-enviado", props.pedido.id),
        {},
        {
            onError: (errors) => alert(Object.values(errors).join(", ")),
            onSuccess: () => alert("Pedido marcado como enviado exitosamente"),
        }
    );
};
</script>

<style scoped>
.table-borderless td {
    padding: 0.5rem 0;
}
</style>
