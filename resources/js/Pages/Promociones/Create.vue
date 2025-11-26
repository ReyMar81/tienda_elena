<script setup>
import { useForm, Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import { ref, computed } from "vue";

const props = defineProps({
    productos: Array,
    categorias: Array,
});

const form = useForm({
    nombre: "",
    descripcion: "",
    valor_descuento_decimal: "",
    fecha_inicio: "",
    fecha_fin: "",
    estado: true,
    productos: [],
    categorias: [],
});

// Búsqueda de productos
const searchProducto = ref("");
const productosFiltrados = computed(() => {
    if (!searchProducto.value) return props.productos;
    return props.productos.filter(
        (p) =>
            p.nombre
                .toLowerCase()
                .includes(searchProducto.value.toLowerCase()) ||
            p.codigo.toLowerCase().includes(searchProducto.value.toLowerCase())
    );
});

// Productos seleccionados con sus checkboxes
const productosSeleccionados = ref([]);

const agregarProducto = (producto) => {
    if (!productosSeleccionados.value.find((p) => p.id === producto.id)) {
        productosSeleccionados.value.push({
            id: producto.id,
            nombre: producto.nombre,
            codigo: producto.codigo,
            aplica_mayorista: false,
            aplica_minorista: false,
        });
    }
    searchProducto.value = "";
};

const eliminarProducto = (index) => {
    productosSeleccionados.value.splice(index, 1);
};

// Categorías seleccionadas con sus checkboxes
const categoriasSeleccionadas = ref([]);

const toggleCategoria = (categoria) => {
    const index = categoriasSeleccionadas.value.findIndex(
        (c) => c.id === categoria.id
    );
    if (index > -1) {
        categoriasSeleccionadas.value.splice(index, 1);
    } else {
        categoriasSeleccionadas.value.push({
            id: categoria.id,
            nombre: categoria.nombre,
            aplica_mayorista: false,
            aplica_minorista: false,
        });
    }
};

const isCategoriaSelected = (categoriaId) => {
    return categoriasSeleccionadas.value.some((c) => c.id === categoriaId);
};

const submitForm = () => {
    // Preparar datos con pivot
    form.productos = productosSeleccionados.value.map((p) => ({
        id: p.id,
        aplica_mayorista: p.aplica_mayorista,
        aplica_minorista: p.aplica_minorista,
    }));

    form.categorias = categoriasSeleccionadas.value.map((c) => ({
        id: c.id,
        aplica_mayorista: c.aplica_mayorista,
        aplica_minorista: c.aplica_minorista,
    }));

    form.post(route("promociones.store"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Crear Promoción" />

    <AppLayout title="Crear Promoción">
        <FlashNotification />

        <div class="container py-4">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div
                        class="d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <h2 class="mb-0">Crear Promoción</h2>
                            <p class="text-muted">
                                Registra una nueva oferta o descuento
                            </p>
                        </div>
                        <Link
                            :href="route('promociones.index')"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-arrow-left me-2"></i>
                            Volver
                        </Link>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submitForm">
                <div class="row">
                    <!-- Información General -->
                    <div class="col-lg-8 mx-auto">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Información General</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label"
                                            >Nombre
                                            <span class="text-danger"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.nombre"
                                            type="text"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.nombre,
                                            }"
                                            placeholder="Ej: Descuento Navidad 2025"
                                        />
                                        <div
                                            v-if="form.errors.nombre"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.nombre }}
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label"
                                            >Descuento (%)
                                            <span class="text-danger"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="
                                                form.valor_descuento_decimal
                                            "
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors
                                                        .valor_descuento_decimal,
                                            }"
                                            placeholder="15"
                                        />
                                        <div
                                            v-if="
                                                form.errors
                                                    .valor_descuento_decimal
                                            "
                                            class="invalid-feedback"
                                        >
                                            {{
                                                form.errors
                                                    .valor_descuento_decimal
                                            }}
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label"
                                            >Descripción</label
                                        >
                                        <textarea
                                            v-model="form.descripcion"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.descripcion,
                                            }"
                                            rows="2"
                                            placeholder="Descripción opcional de la promoción"
                                        ></textarea>
                                        <div
                                            v-if="form.errors.descripcion"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.descripcion }}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label"
                                            >Fecha Inicio
                                            <span class="text-danger"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.fecha_inicio"
                                            type="date"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.fecha_inicio,
                                            }"
                                        />
                                        <div
                                            v-if="form.errors.fecha_inicio"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.fecha_inicio }}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label"
                                            >Fecha Fin
                                            <span class="text-danger"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.fecha_fin"
                                            type="date"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.fecha_fin,
                                            }"
                                        />
                                        <div
                                            v-if="form.errors.fecha_fin"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.fecha_fin }}
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input
                                                v-model="form.estado"
                                                class="form-check-input"
                                                type="checkbox"
                                                id="estado"
                                                role="switch"
                                            />
                                            <label
                                                class="form-check-label"
                                                for="estado"
                                            >
                                                {{
                                                    form.estado
                                                        ? "Activa"
                                                        : "Inactiva"
                                                }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Productos Específicos -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    Aplicar a Productos Específicos
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label"
                                        >Buscar Producto</label
                                    >
                                    <input
                                        v-model="searchProducto"
                                        type="text"
                                        class="form-control"
                                        placeholder="Buscar por nombre o código..."
                                    />
                                </div>

                                <div class="row g-2 mb-3">
                                    <div
                                        v-for="producto in productosFiltrados.slice(
                                            0,
                                            10
                                        )"
                                        :key="producto.id"
                                        class="col-md-6"
                                    >
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary w-100 text-start"
                                            @click="agregarProducto(producto)"
                                        >
                                            <i
                                                class="bi bi-plus-circle me-1"
                                            ></i>
                                            {{ producto.codigo }} -
                                            {{ producto.nombre }}
                                        </button>
                                    </div>
                                </div>

                                <hr v-if="productosSeleccionados.length > 0" />

                                <div v-if="productosSeleccionados.length > 0">
                                    <label class="form-label fw-bold"
                                        >Productos Seleccionados:</label
                                    >
                                    <div
                                        v-for="(
                                            producto, index
                                        ) in productosSeleccionados"
                                        :key="producto.id"
                                        class="border rounded p-3 mb-2"
                                    >
                                        <div
                                            class="d-flex justify-content-between align-items-start mb-2"
                                        >
                                            <div>
                                                <strong>{{
                                                    producto.codigo
                                                }}</strong>
                                                - {{ producto.nombre }}
                                            </div>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-danger"
                                                @click="eliminarProducto(index)"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input
                                                    v-model="
                                                        producto.aplica_minorista
                                                    "
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    :id="`prod-minorista-${producto.id}`"
                                                />
                                                <label
                                                    class="form-check-label"
                                                    :for="`prod-minorista-${producto.id}`"
                                                >
                                                    Precio Minorista
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input
                                                    v-model="
                                                        producto.aplica_mayorista
                                                    "
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    :id="`prod-mayorista-${producto.id}`"
                                                />
                                                <label
                                                    class="form-check-label"
                                                    :for="`prod-mayorista-${producto.id}`"
                                                >
                                                    Precio Mayorista
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-muted text-center mb-0">
                                    <i class="bi bi-inbox"></i> No hay productos
                                    seleccionados
                                </p>
                            </div>
                        </div>

                        <!-- Categorías Completas -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    Aplicar a Categorías Completas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 mb-3">
                                    <div
                                        v-for="categoria in categorias"
                                        :key="categoria.id"
                                        class="col-md-6"
                                    >
                                        <button
                                            type="button"
                                            class="btn btn-sm w-100 text-start"
                                            :class="
                                                isCategoriaSelected(
                                                    categoria.id
                                                )
                                                    ? 'btn-success'
                                                    : 'btn-outline-secondary'
                                            "
                                            @click="toggleCategoria(categoria)"
                                        >
                                            <i
                                                class="bi me-1"
                                                :class="
                                                    isCategoriaSelected(
                                                        categoria.id
                                                    )
                                                        ? 'bi-check-circle-fill'
                                                        : 'bi-circle'
                                                "
                                            ></i>
                                            {{ categoria.nombre }}
                                        </button>
                                    </div>
                                </div>

                                <hr v-if="categoriasSeleccionadas.length > 0" />

                                <div v-if="categoriasSeleccionadas.length > 0">
                                    <label class="form-label fw-bold"
                                        >Categorías Seleccionadas:</label
                                    >
                                    <div
                                        v-for="categoria in categoriasSeleccionadas"
                                        :key="categoria.id"
                                        class="border rounded p-3 mb-2 bg-light"
                                    >
                                        <div class="fw-bold mb-2">
                                            {{ categoria.nombre }}
                                        </div>
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input
                                                    v-model="
                                                        categoria.aplica_minorista
                                                    "
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    :id="`cat-minorista-${categoria.id}`"
                                                />
                                                <label
                                                    class="form-check-label"
                                                    :for="`cat-minorista-${categoria.id}`"
                                                >
                                                    Precio Minorista
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input
                                                    v-model="
                                                        categoria.aplica_mayorista
                                                    "
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    :id="`cat-mayorista-${categoria.id}`"
                                                />
                                                <label
                                                    class="form-check-label"
                                                    :for="`cat-mayorista-${categoria.id}`"
                                                >
                                                    Precio Mayorista
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-muted text-center mb-0">
                                    <i class="bi bi-inbox"></i> No hay
                                    categorías seleccionadas
                                </p>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex gap-2 justify-content-end">
                                    <Link
                                        :href="route('promociones.index')"
                                        class="btn btn-secondary"
                                    >
                                        Cancelar
                                    </Link>
                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                        :disabled="form.processing"
                                    >
                                        <span v-if="form.processing">
                                            <i
                                                class="bi bi-hourglass-split me-2"
                                            ></i>
                                            Guardando...
                                        </span>
                                        <span v-else>
                                            <i class="bi bi-save me-2"></i>
                                            Guardar Promoción
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
