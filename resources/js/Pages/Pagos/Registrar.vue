<template>
    <AppLayout title="Registrar Pago">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0">Registrar Pago Manual</h4>
                        </div>
                        <div class="card-body">
                            <!-- Buscar crédito -->
                            <div class="mb-4" v-if="!cuota">
                                <label class="form-label fw-bold"
                                    >Buscar Crédito</label
                                >
                                <input
                                    type="number"
                                    class="form-control"
                                    placeholder="Ingrese ID del crédito..."
                                    v-model="creditoId"
                                    @change="buscarCuotas"
                                />
                            </div>

                            <!-- Cuotas disponibles -->
                            <div
                                v-if="
                                    cuotasDisponibles.length > 0 &&
                                    !cuotaSeleccionada
                                "
                                class="mb-4"
                            >
                                <label class="form-label fw-bold"
                                    >Seleccionar Cuota</label
                                >
                                <div class="list-group">
                                    <button
                                        v-for="c in cuotasDisponibles"
                                        :key="c.id"
                                        class="list-group-item list-group-item-action d-flex justify-content-between"
                                        @click="seleccionarCuota(c)"
                                    >
                                        <div>
                                            <strong
                                                >Cuota
                                                {{ c.numero_cuota }}</strong
                                            >
                                            <span
                                                class="badge ms-2"
                                                :class="
                                                    c.estado === 'vencida'
                                                        ? 'bg-danger'
                                                        : 'bg-warning'
                                                "
                                            >
                                                {{ c.estado }}
                                            </span>
                                            <br />
                                            <small
                                                >Vence:
                                                {{
                                                    formatDate(
                                                        c.fecha_vencimiento
                                                    )
                                                }}</small
                                            >
                                        </div>
                                        <div class="text-end">
                                            <div>
                                                Saldo: Bs.
                                                {{
                                                    formatMoney(
                                                        c.monto_pendiente
                                                    )
                                                }}
                                            </div>
                                            <div
                                                v-if="c.mora_calculada > 0"
                                                class="text-danger small"
                                            >
                                                Mora: Bs.
                                                {{
                                                    formatMoney(
                                                        c.mora_calculada
                                                    )
                                                }}
                                            </div>
                                            <strong
                                                >Total: Bs.
                                                {{
                                                    formatMoney(c.total_pagar)
                                                }}</strong
                                            >
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Información de la cuota seleccionada -->
                            <div v-if="cuotaSeleccionada || cuota" class="mb-4">
                                <div class="alert alert-info">
                                    <h5>
                                        Cuota
                                        {{
                                            (cuotaSeleccionada || cuota)
                                                .numero_cuota
                                        }}
                                    </h5>
                                    <p class="mb-1">
                                        <strong>Cliente:</strong>
                                        {{
                                            (cuotaSeleccionada || cuota).credito
                                                ?.venta?.cliente?.name
                                        }}
                                    </p>
                                    <p class="mb-1">
                                        <strong>Saldo pendiente:</strong> Bs.
                                        {{
                                            formatMoney(
                                                (cuotaSeleccionada || cuota)
                                                    .monto_pendiente
                                            )
                                        }}
                                    </p>
                                    <p class="mb-1" v-if="moraCalculada > 0">
                                        <strong class="text-danger"
                                            >Mora:</strong
                                        >
                                        Bs. {{ formatMoney(moraCalculada) }}
                                    </p>
                                    <p class="mb-0">
                                        <strong>Total a pagar:</strong> Bs.
                                        {{ formatMoney(totalDeuda) }}
                                    </p>
                                </div>

                                <!-- Formulario de pago -->
                                <form @submit.prevent="registrarPago">
                                    <div class="mb-3">
                                        <label class="form-label"
                                            >Monto del Pago *</label
                                        >
                                        <input
                                            type="number"
                                            step="0.01"
                                            class="form-control"
                                            v-model="form.monto"
                                            :max="totalDeuda"
                                            required
                                        />
                                        <small class="text-muted"
                                            >Máximo: Bs.
                                            {{ formatMoney(totalDeuda) }}</small
                                        >
                                        <div
                                            v-if="errors.monto"
                                            class="text-danger small mt-1"
                                        >
                                            {{ errors.monto }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label"
                                            >Método de Pago *</label
                                        >
                                        <select
                                            class="form-select"
                                            v-model="form.metodo_pago_id"
                                            required
                                        >
                                            <option value="">
                                                Seleccione...
                                            </option>
                                            <option
                                                v-for="metodo in metodosPago"
                                                :key="metodo.id"
                                                :value="metodo.id"
                                            >
                                                {{ metodo.nombre }}
                                            </option>
                                        </select>
                                        <div
                                            v-if="errors.metodo_pago_id"
                                            class="text-danger small mt-1"
                                        >
                                            {{ errors.metodo_pago_id }}
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label"
                                            >Fecha de Pago</label
                                        >
                                        <input
                                            type="date"
                                            class="form-control"
                                            v-model="form.fecha"
                                        />
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label"
                                            >Número de Comprobante</label
                                        >
                                        <input
                                            type="text"
                                            class="form-control"
                                            v-model="form.comprobante"
                                        />
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Notas</label>
                                        <textarea
                                            class="form-control"
                                            v-model="form.notas"
                                            rows="2"
                                        ></textarea>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <Link
                                            :href="route('pagos.index')"
                                            class="btn btn-secondary"
                                            >Cancelar</Link
                                        >
                                        <button
                                            type="submit"
                                            class="btn btn-success"
                                            :disabled="processing"
                                        >
                                            <span
                                                v-if="processing"
                                                class="spinner-border spinner-border-sm me-2"
                                            ></span>
                                            Registrar Pago
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import axios from "axios";

const props = defineProps({
    cuota: Object,
    metodosPago: Array,
});

const creditoId = ref("");
const cuotasDisponibles = ref([]);
const cuotaSeleccionada = ref(null);
const processing = ref(false);
const errors = ref({});

const form = ref({
    cuota_id: props.cuota?.id || "",
    monto: "",
    metodo_pago_id: "",
    fecha: new Date().toISOString().split("T")[0],
    comprobante: "",
    notas: "",
});

const moraCalculada = computed(() => {
    const c = cuotaSeleccionada.value || props.cuota;
    return c?.mora_calculada || c?.mora || 0;
});

const totalDeuda = computed(() => {
    const c = cuotaSeleccionada.value || props.cuota;
    return (c?.monto_pendiente || 0) + moraCalculada.value;
});

const buscarCuotas = async () => {
    if (!creditoId.value) return;

    try {
        const response = await axios.get(route("pagos.buscar-cuotas"), {
            params: { credito_id: creditoId.value },
        });
        cuotasDisponibles.value = response.data;
    } catch (error) {
        console.error("Error buscando cuotas:", error);
    }
};

const seleccionarCuota = (cuota) => {
    cuotaSeleccionada.value = cuota;
    form.value.cuota_id = cuota.id;
    form.value.monto = cuota.total_pagar;
};

const registrarPago = () => {
    processing.value = true;
    errors.value = {};

    router.post(route("pagos.store"), form.value, {
        onError: (err) => {
            errors.value = err;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};

const formatMoney = (amount) => parseFloat(amount || 0).toFixed(2);
const formatDate = (date) => new Date(date).toLocaleDateString("es-ES");
</script>
