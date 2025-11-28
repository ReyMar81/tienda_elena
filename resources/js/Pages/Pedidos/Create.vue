<template>
    <AppLayout title="Crear Pedido">
        <div class="container py-4">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <!-- Header -->
                    <div
                        class="d-flex justify-content-between align-items-center mb-4"
                    >
                        <div>
                            <h2 class="mb-0 text-primary fw-bold">
                                <i class="bi bi-plus-circle me-2"></i>
                                Crear Nuevo Pedido
                            </h2>
                            <p class="text-muted">
                                Registra un pedido realizado en tienda física
                            </p>
                        </div>
                    </div>

                    <form @submit.prevent="submitForm">
                        <!-- 1. Cliente -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-3 text-primary">
                                    <i class="bi bi-person-lines-fill me-2"></i>
                                    1. Seleccionar Cliente
                                </h5>

                                <div class="row">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold"
                                            >Cliente *</label
                                        >
                                        <select
                                            v-model="form.user_id"
                                            class="form-select"
                                            required
                                        >
                                            <option value="">
                                                -- Seleccione un cliente --
                                            </option>

                                            <option
                                                v-for="cliente in clientes"
                                                :key="cliente.id"
                                                :value="cliente.id"
                                            >
                                                {{ cliente.nombre }}
                                                {{ cliente.apellidos }} - CI:
                                                {{ cliente.ci }}
                                            </option>
                                        </select>

                                        <div v-if="form.user_id" class="mt-2">
                                            <small class="text-muted">
                                                <i
                                                    class="bi bi-info-circle me-1"
                                                ></i>
                                                {{
                                                    clienteSeleccionado.email
                                                }}
                                                |
                                                {{
                                                    clienteSeleccionado.telefono
                                                }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Productos -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4 text-primary">
                                    <i class="bi bi-bag-plus me-2"></i>
                                    2. Agregar Productos
                                </h5>

                                <!-- Buscador -->
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <input
                                            v-model="searchProducto"
                                            type="text"
                                            class="form-control"
                                            placeholder="Buscar producto por nombre o código..."
                                        />
                                    </div>
                                </div>

                                <!-- Tabla de productos disponibles -->
                                <div
                                    class="table-responsive mb-4"
                                    style="max-height: 300px; overflow-y: auto"
                                >
                                    <table
                                        class="table table-hover align-middle"
                                    >
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>Código</th>
                                                <th>Producto</th>
                                                <th>Categoría</th>
                                                <th>Precio</th>
                                                <th>Stock</th>
                                                <th>Descuento</th>
                                                <th class="text-center">
                                                    Acción
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr
                                                v-for="producto in productosFiltrados"
                                                :key="producto.id"
                                            >
                                                <td>{{ producto.codigo }}</td>
                                                <td class="fw-semibold">
                                                    {{ producto.nombre }}
                                                </td>
                                                <td>
                                                    {{
                                                        producto.categoria
                                                            ?.nombre || "N/A"
                                                    }}
                                                </td>

                                                <td>
                                                    {{
                                                        formatearMoneda(
                                                            producto.precio_venta
                                                        )
                                                    }}
                                                </td>

                                                <td>
                                                    <span
                                                        :class="
                                                            producto.stock_actual <
                                                            10
                                                                ? 'badge bg-warning'
                                                                : 'badge bg-success'
                                                        "
                                                    >
                                                        {{
                                                            producto.stock_actual
                                                        }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <span
                                                        v-if="
                                                            producto.promociones
                                                                ?.length
                                                        "
                                                        class="badge bg-danger"
                                                    >
                                                        -{{
                                                            producto
                                                                .promociones[0]
                                                                .valor_descuento_decimal
                                                        }}%
                                                    </span>
                                                    <span
                                                        v-else
                                                        class="text-muted"
                                                        >-</span
                                                    >
                                                </td>

                                                <td class="text-center">
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-primary"
                                                        @click="
                                                            agregarProducto(
                                                                producto
                                                            )
                                                        "
                                                        :disabled="
                                                            producto.stock_actual ===
                                                            0
                                                        "
                                                    >
                                                        <i
                                                            class="bi bi-plus-circle me-1"
                                                        ></i>
                                                        Agregar
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Productos seleccionados -->
                                <div v-if="form.detalles.length > 0">
                                    <h5 class="fw-bold mb-3 text-primary">
                                        <i class="bi bi-box-seam me-2"></i>
                                        Productos Seleccionados
                                    </h5>

                                    <div class="table-responsive">
                                        <table
                                            class="table table-bordered table-striped align-middle"
                                        >
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Producto</th>
                                                    <th width="150">
                                                        Cantidad
                                                    </th>
                                                    <th>Precio Unit.</th>
                                                    <th>Descuento</th>
                                                    <th>Subtotal</th>
                                                    <th width="80">Acción</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <tr
                                                    v-for="(
                                                        detalle, index
                                                    ) in form.detalles"
                                                    :key="detalle.producto_id"
                                                >
                                                    <td class="fw-semibold">
                                                        {{ detalle.nombre }}
                                                    </td>

                                                    <td>
                                                        <input
                                                            v-model.number="
                                                                detalle.cantidad
                                                            "
                                                            type="number"
                                                            min="1"
                                                            :max="
                                                                detalle.stock_disponible
                                                            "
                                                            class="form-control form-control-sm"
                                                            @input="
                                                                calcularTotales
                                                            "
                                                        />
                                                        <small
                                                            class="text-muted"
                                                            >Máx:
                                                            {{
                                                                detalle.stock_disponible
                                                            }}</small
                                                        >
                                                    </td>

                                                    <td>
                                                        {{
                                                            formatearMoneda(
                                                                detalle.precio_unitario
                                                            )
                                                        }}
                                                    </td>

                                                    <td>
                                                        <span
                                                            v-if="
                                                                detalle.descuento_porcentaje >
                                                                0
                                                            "
                                                            class="badge bg-danger"
                                                        >
                                                            -{{
                                                                detalle.descuento_porcentaje
                                                            }}%
                                                        </span>
                                                        <span v-else>-</span>
                                                    </td>

                                                    <td
                                                        class="fw-bold text-primary"
                                                    >
                                                        {{
                                                            formatearMoneda(
                                                                detalle.subtotal
                                                            )
                                                        }}
                                                    </td>

                                                    <td class="text-center">
                                                        <button
                                                            class="btn btn-sm btn-danger"
                                                            type="button"
                                                            @click="
                                                                quitarProducto(
                                                                    index
                                                                )
                                                            "
                                                        >
                                                            <i
                                                                class="bi bi-trash"
                                                            ></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>

                                            <tfoot class="table-light">
                                                <tr>
                                                    <td
                                                        colspan="4"
                                                        class="text-end fw-bold"
                                                    >
                                                        TOTAL:
                                                    </td>
                                                    <td
                                                        colspan="2"
                                                        class="fw-bold fs-5 text-primary"
                                                    >
                                                        {{
                                                            formatearMoneda(
                                                                totalGeneral
                                                            )
                                                        }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                <div v-else class="alert alert-info">
                                    <i class="bi bi-info-circle me-1"></i>
                                    No hay productos seleccionados. Agrega al
                                    menos 1 producto.
                                </div>
                            </div>
                        </div>

                        <!-- 3. Método de Pago -->
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4 text-primary">
                                    <i class="bi bi-credit-card me-2"></i>
                                    3. Método de Pago
                                </h5>

                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold"
                                            >Tipo de Pago *</label
                                        >
                                        <select
                                            v-model="form.tipo_pago"
                                            class="form-select"
                                            required
                                        >
                                            <option value="contado">
                                                Al Contado
                                            </option>
                                            <option value="credito">
                                                A Crédito
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold"
                                            >Método de Pago *</label
                                        >
                                        <select
                                            v-model="form.metodo_pago_id"
                                            class="form-select"
                                            required
                                        >
                                            <option value="">
                                                -- Seleccione método --
                                            </option>
                                            <option
                                                v-for="metodo in metodosPago"
                                                :key="metodo.id"
                                                :value="metodo.id"
                                            >
                                                {{ metodo.nombre }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label
                                            class="form-label d-block fw-semibold"
                                            >&nbsp;</label
                                        >
                                        <div class="form-check">
                                            <input
                                                v-model="
                                                    form.confirmar_inmediatamente
                                                "
                                                class="form-check-input"
                                                type="checkbox"
                                                id="confirmarInmediato"
                                            />
                                            <label
                                                class="form-check-label"
                                                for="confirmarInmediato"
                                            >
                                                <strong
                                                    >Confirmar
                                                    inmediatamente</strong
                                                >
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-if="form.tipo_pago === 'credito'"
                                    class="alert alert-warning mt-3"
                                >
                                    <i
                                        class="bi bi-exclamation-triangle me-2"
                                    ></i>
                                    <strong>Pago a Crédito:</strong> Se generará
                                    un crédito al confirmar el pedido.
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4 text-end">
                                <Link
                                    :href="route('pedidos.index')"
                                    class="btn btn-secondary"
                                >
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Cancelar
                                </Link>

                                <button
                                    type="submit"
                                    class="btn btn-success ms-2"
                                    :disabled="!formularioValido || processing"
                                >
                                    <i class="bi bi-save me-2"></i>
                                    {{
                                        form.confirmar_inmediatamente
                                            ? "Crear y Confirmar Venta"
                                            : "Crear Pedido"
                                    }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Créditos -->
        <CreditoModal
            :show="mostrarModalCredito"
            :total="totalGeneral"
            :cuotas-iniciales="form.numero_cuotas"
            @close="mostrarModalCredito = false"
            @confirmar="confirmarCredito"
        />
    </AppLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import CreditoModal from "@/Components/CreditoModal.vue";

const props = defineProps({
    clientes: Array,
    productos: Array,
    metodosPago: Array,
});

const form = ref({
    user_id: "",
    metodo_pago_id: "",
    tipo_pago: "contado",
    detalles: [],
    confirmar_inmediatamente: false,
    numero_cuotas: 3,
});

const searchProducto = ref("");
const processing = ref(false);
const mostrarModalCredito = ref(false);

const clienteSeleccionado = computed(
    () => props.clientes.find((c) => c.id === form.value.user_id) || {}
);

const productosFiltrados = computed(() => {
    if (!searchProducto.value) return props.productos;

    const s = searchProducto.value.toLowerCase();

    return props.productos.filter(
        (p) =>
            p.nombre.toLowerCase().includes(s) ||
            (p.codigo && p.codigo.toLowerCase().includes(s))
    );
});

const totalGeneral = computed(() =>
    form.value.detalles.reduce((sum, d) => sum + d.subtotal, 0)
);

const formularioValido = computed(
    () =>
        form.value.user_id &&
        form.value.metodo_pago_id &&
        form.value.detalles.length > 0
);

const agregarProducto = (producto) => {
    const existe = form.value.detalles.find(
        (d) => d.producto_id === producto.id
    );
    if (existe) return alert("Este producto ya está agregado");

    let des = 0;
    if (producto.promociones?.length)
        des = parseFloat(producto.promociones[0].valor_descuento_decimal);

    const precioConDescuento = producto.precio_venta * (1 - des / 100);

    form.value.detalles.push({
        producto_id: producto.id,
        nombre: producto.nombre,
        cantidad: 1,
        precio_unitario: parseFloat(producto.precio_venta),
        descuento_porcentaje: des,
        stock_disponible: producto.stock_actual,
        subtotal: precioConDescuento,
    });

    calcularTotales();
};

const quitarProducto = (index) => {
    form.value.detalles.splice(index, 1);
    calcularTotales();
};

const calcularTotales = () => {
    form.value.detalles.forEach((d) => {
        const precioConDescuento =
            d.precio_unitario * (1 - d.descuento_porcentaje / 100);
        d.subtotal = precioConDescuento * d.cantidad;
    });
};

const formatearMoneda = (valor) =>
    new Intl.NumberFormat("es-BO", {
        style: "currency",
        currency: "BOB",
    }).format(valor);

const submitForm = () => {
    if (!formularioValido.value)
        return alert("Complete los campos obligatorios");

    if (
        form.value.confirmar_inmediatamente &&
        form.value.tipo_pago === "credito"
    ) {
        mostrarModalCredito.value = true;
        return;
    }

    enviarFormulario();
};

const confirmarCredito = (data) => {
    // CreditoModal ahora devuelve un objeto con numero_cuotas, tasa_interes y descuento_percent
    form.value.numero_cuotas = data.numero_cuotas;
    form.value.tasa_interes = data.tasa_interes || 0;
    form.value.descuento_percent = data.descuento_percent || 0;
    enviarFormulario();
};

const enviarFormulario = () => {
    processing.value = true;

    router.post(route("pedidos.admin.store"), form.value, {
        onFinish: () => (processing.value = false),
    });
};
</script>

<style scoped>
.table-hover tbody tr:hover {
    background-color: #f7f7f7;
}
.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
}
</style>
