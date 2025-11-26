<template>
    <AppLayout title="Mis Créditos">
        <div class="container py-4">
            <h2 class="mb-4">Mis Créditos y Cuotas</h2>

            <div
                v-if="creditos.length === 0"
                class="text-center text-muted py-5"
            >
                <i class="bi bi-credit-card" style="font-size: 3rem"></i>
                <p class="mt-3">No tienes créditos activos</p>
            </div>

            <div v-else>
                <div
                    v-for="credito in creditos"
                    :key="credito.id"
                    class="card mb-4"
                >
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <h5 class="mb-0">Crédito #{{ credito.id }}</h5>
                            <small class="text-muted"
                                >{{ credito.numero_cuotas }} cuotas</small
                            >
                        </div>
                        <span
                            class="badge"
                            :class="{
                                'bg-success': credito.estado === 'pagado',
                                'bg-primary': credito.estado === 'activo',
                                'bg-danger': credito.estado === 'vencido',
                            }"
                        >
                            {{ credito.estado }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Monto Total:</strong> Bs.
                                {{ formatMoney(credito.monto_total) }}
                            </div>
                            <div class="col-md-4">
                                <strong>Pagado:</strong> Bs.
                                {{ formatMoney(credito.monto_pagado) }}
                            </div>
                            <div class="col-md-4">
                                <strong>Saldo:</strong> Bs.
                                {{ formatMoney(credito.monto_pendiente) }}
                            </div>
                        </div>

                        <!-- Cuotas -->
                        <h6 class="mt-3">Cuotas</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Monto</th>
                                        <th>Vencimiento</th>
                                        <th>Estado</th>
                                        <th>Saldo</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="cuota in credito.cuotas"
                                        :key="cuota.id"
                                    >
                                        <td>{{ cuota.numero_cuota }}</td>
                                        <td>
                                            Bs. {{ formatMoney(cuota.monto) }}
                                        </td>
                                        <td>
                                            {{
                                                formatDate(
                                                    cuota.fecha_vencimiento
                                                )
                                            }}
                                        </td>
                                        <td>
                                            <span
                                                class="badge"
                                                :class="{
                                                    'bg-success':
                                                        cuota.estado ===
                                                        'pagada',
                                                    'bg-warning':
                                                        cuota.estado ===
                                                        'pendiente',
                                                    'bg-danger':
                                                        cuota.estado ===
                                                        'vencida',
                                                }"
                                            >
                                                {{ cuota.estado }}
                                            </span>
                                        </td>
                                        <td>
                                            Bs.
                                            {{
                                                formatMoney(
                                                    cuota.monto_pendiente
                                                )
                                            }}
                                        </td>
                                        <td>
                                            <button
                                                v-if="cuota.estado !== 'pagada'"
                                                class="btn btn-sm btn-primary"
                                                @click="abrirModalPago(cuota)"
                                            >
                                                Pagar con QR
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal QR -->
        <QRModal
            v-if="showQRModal"
            :cuota="cuotaParaPago"
            @close="showQRModal = false"
        />
    </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import QRModal from "@/Components/QRModal.vue";

const props = defineProps({
    creditos: Array,
});

const showQRModal = ref(false);
const cuotaParaPago = ref(null);

const abrirModalPago = (cuota) => {
    cuotaParaPago.value = cuota;
    showQRModal.value = true;
};

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);
const formatDate = (date) => new Date(date).toLocaleDateString("es-ES");
</script>
