<script setup>
import { useForm, Head, Link, usePage, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import DialogModal from "@/Components/DialogModal.vue";
import { ref, nextTick } from "vue";

const props = defineProps({
    categorias: Array,
});

const page = usePage();

const form = useForm({
    codigo: "",
    nombre: "",
    precio_compra: "",
    precio_venta: "",
    precio_venta_mayorista: "",
    stock_actual: 0,
    stock_minimo: 0,
    marca: "",
    categoria_id: "",
    estado: true,
    imagen: null,
});

const imagenPreview = ref([]);

// Modal crear categoría
const showCategoriaModal = ref(false);
const categoriaForm = useForm({
    nombre: "",
    descripcion: "",
});

const categoriasLocal = ref([...props.categorias]);

// Abrir modal crear categoría
const openCategoriaModal = () => {
    categoriaForm.reset();
    categoriaForm.clearErrors();
    showCategoriaModal.value = true;
};

// Cerrar modal crear categoría
const closeCategoriaModal = () => {
    showCategoriaModal.value = false;
    categoriaForm.reset();
};

// Guardar nueva categoría
const saveCategoria = () => {
    categoriaForm.post(route("categorias.store"), {
        preserveScroll: true,
        onSuccess: () => {
            // Recargar solo las categorías desde la BD
            router.reload({
                only: ['categorias'],
                preserveScroll: true,
                onSuccess: () => {
                    // Actualizar lista local con las categorías recargadas
                    categoriasLocal.value = [...page.props.categorias];
                    
                    // Seleccionar la nueva categoría (la última creada)
                    const nuevaCategoria = page.props.flash?.categoria;
                    if (nuevaCategoria) {
                        form.categoria_id = nuevaCategoria.id;
                    }
                    closeCategoriaModal();
                },
            });
        },
    });
};

// Manejar selección de múltiples imágenes
const handleImageChange = (event) => {
    const files = Array.from(event.target.files);
    form.imagen = files;

    // Generar previews
    imagenPreview.value = [];
    files.forEach((file) => {
        imagenPreview.value.push(URL.createObjectURL(file));
    });
};

// Submit formulario
const submitForm = () => {
    form.post(route("productos.store"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Crear Producto" />

    <AppLayout title="Crear Producto">
        <FlashNotification />
        <div class="container py-4">
            <!-- Encabezado -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div
                        class="d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <h2 class="mb-0">Crear Producto</h2>
                            <p class="text-muted">
                                Registra un nuevo producto en el catálogo
                            </p>
                        </div>
                        <Link
                            :href="route('productos.index')"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-arrow-left me-2"></i>
                            Volver
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Formulario -->
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <form @submit.prevent="submitForm">
                                <div class="row g-3">
                                    <!-- Código -->
                                    <div class="col-md-6">
                                        <label for="codigo" class="form-label"
                                            >Código
                                            <span class="text-danger"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.codigo"
                                            type="text"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.codigo,
                                            }"
                                            id="codigo"
                                            placeholder="Ej: PROD-001"
                                        />
                                        <div
                                            v-if="form.errors.codigo"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.codigo }}
                                        </div>
                                    </div>

                                    <!-- Categoría -->
                                    <div class="col-md-6">
                                        <label
                                            for="categoria_id"
                                            class="form-label"
                                            >Categoría</label
                                        >
                                        <div class="input-group">
                                            <select
                                                v-model="form.categoria_id"
                                                class="form-select"
                                                :class="{
                                                    'is-invalid':
                                                        form.errors
                                                            .categoria_id,
                                                }"
                                                id="categoria_id"
                                            >
                                                <option value="">
                                                    Sin categoría
                                                </option>
                                                <option
                                                    v-for="cat in categoriasLocal"
                                                    :key="cat.id"
                                                    :value="cat.id"
                                                >
                                                    {{ cat.nombre }}
                                                </option>
                                            </select>
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary"
                                                @click="openCategoriaModal"
                                                title="Crear nueva categoría"
                                            >
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                        <div
                                            v-if="form.errors.categoria_id"
                                            class="invalid-feedback d-block"
                                        >
                                            {{ form.errors.categoria_id }}
                                        </div>
                                        <small class="text-muted"
                                            >Opcional. Puedes crear una nueva
                                            categoría con el botón +</small
                                        >
                                    </div>

                                    <!-- Nombre -->
                                    <div class="col-md-12">
                                        <label for="nombre" class="form-label"
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
                                            id="nombre"
                                            placeholder="Ej: Laptop HP ProBook 450 G8"
                                        />
                                        <div
                                            v-if="form.errors.nombre"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.nombre }}
                                        </div>
                                    </div>

                                    <!-- Marca -->
                                    <div class="col-md-6">
                                        <label for="marca" class="form-label"
                                            >Marca</label
                                        >
                                        <input
                                            v-model="form.marca"
                                            type="text"
                                            class="form-control"
                                            :class="{
                                                'is-invalid': form.errors.marca,
                                            }"
                                            id="marca"
                                            placeholder="Ej: HP, Samsung, Sony"
                                        />
                                        <div
                                            v-if="form.errors.marca"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.marca }}
                                        </div>
                                    </div>

                                    <!-- Estado -->
                                    <div class="col-md-6">
                                        <label class="form-label d-block"
                                            >Estado</label
                                        >
                                        <div class="form-check form-switch">
                                            <input
                                                v-model="form.estado"
                                                type="checkbox"
                                                class="form-check-input"
                                                :class="{
                                                    'is-invalid':
                                                        form.errors.estado,
                                                }"
                                                id="estado"
                                                role="switch"
                                            />
                                            <label
                                                class="form-check-label"
                                                for="estado"
                                            >
                                                {{
                                                    form.estado
                                                        ? "Activo"
                                                        : "Inactivo"
                                                }}
                                            </label>
                                        </div>
                                        <div
                                            v-if="form.errors.estado"
                                            class="invalid-feedback d-block"
                                        >
                                            {{ form.errors.estado }}
                                        </div>
                                    </div>

                                    <!-- Precio Compra -->
                                    <div class="col-md-4">
                                        <label
                                            for="precio_compra"
                                            class="form-label"
                                            >Precio Compra (Bs)
                                            <span class="text-danger"
                                                >*</span
                                            ></label
                                        >
                                        <div class="input-group">
                                            <span class="input-group-text"
                                                >Bs.</span
                                            >
                                            <input
                                                v-model="form.precio_compra"
                                                type="number"
                                                step="0.01"
                                                class="form-control"
                                                :class="{
                                                    'is-invalid':
                                                        form.errors
                                                            .precio_compra,
                                                }"
                                                id="precio_compra"
                                                placeholder="0.00"
                                            />
                                            <div
                                                v-if="form.errors.precio_compra"
                                                class="invalid-feedback"
                                            >
                                                {{ form.errors.precio_compra }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Precio Venta -->
                                    <div class="col-md-4">
                                        <label
                                            for="precio_venta"
                                            class="form-label"
                                            >Precio Venta (Bs)
                                            <span class="text-danger"
                                                >*</span
                                            ></label
                                        >
                                        <div class="input-group">
                                            <span class="input-group-text"
                                                >Bs.</span
                                            >
                                            <input
                                                v-model="form.precio_venta"
                                                type="number"
                                                step="0.01"
                                                class="form-control"
                                                :class="{
                                                    'is-invalid':
                                                        form.errors
                                                            .precio_venta,
                                                }"
                                                id="precio_venta"
                                                placeholder="0.00"
                                            />
                                            <div
                                                v-if="form.errors.precio_venta"
                                                class="invalid-feedback"
                                            >
                                                {{ form.errors.precio_venta }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Precio Venta Mayorista -->
                                    <div class="col-md-4">
                                        <label
                                            for="precio_venta_mayorista"
                                            class="form-label"
                                            >Precio Mayorista (Bs)
                                            <span class="text-danger"
                                                >*</span
                                            ></label
                                        >
                                        <div class="input-group">
                                            <span class="input-group-text"
                                                >Bs.</span
                                            >
                                            <input
                                                v-model="
                                                    form.precio_venta_mayorista
                                                "
                                                type="number"
                                                step="0.01"
                                                class="form-control"
                                                :class="{
                                                    'is-invalid':
                                                        form.errors
                                                            .precio_venta_mayorista,
                                                }"
                                                id="precio_venta_mayorista"
                                                placeholder="0.00"
                                            />
                                            <div
                                                v-if="
                                                    form.errors
                                                        .precio_venta_mayorista
                                                "
                                                class="invalid-feedback"
                                            >
                                                {{
                                                    form.errors
                                                        .precio_venta_mayorista
                                                }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stock Actual -->
                                    <div class="col-md-6">
                                        <label
                                            for="stock_actual"
                                            class="form-label"
                                            >Stock Actual
                                            <span class="text-danger"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.stock_actual"
                                            type="number"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.stock_actual,
                                            }"
                                            id="stock_actual"
                                            placeholder="0"
                                            min="0"
                                        />
                                        <div
                                            v-if="form.errors.stock_actual"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.stock_actual }}
                                        </div>
                                    </div>

                                    <!-- Stock Mínimo -->
                                    <div class="col-md-6">
                                        <label
                                            for="stock_minimo"
                                            class="form-label"
                                            >Stock Mínimo
                                            <span class="text-danger"
                                                >*</span
                                            ></label
                                        >
                                        <input
                                            v-model="form.stock_minimo"
                                            type="number"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.stock_minimo,
                                            }"
                                            id="stock_minimo"
                                            placeholder="0"
                                            min="0"
                                        />
                                        <div
                                            v-if="form.errors.stock_minimo"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.stock_minimo }}
                                        </div>
                                    </div>

                                    <!-- Imágenes -->
                                    <div class="col-md-12">
                                        <label for="imagen" class="form-label"
                                            >Imágenes del Producto</label
                                        >
                                        <input
                                            type="file"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.imagen,
                                            }"
                                            id="imagen"
                                            accept="image/*"
                                            multiple
                                            @change="handleImageChange"
                                        />
                                        <div
                                            v-if="form.errors.imagen"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.imagen }}
                                        </div>
                                        <small class="text-muted"
                                            >Formatos: JPG, PNG, WEBP. Máx: 2MB
                                            c/u. Puedes seleccionar múltiples
                                            imágenes</small
                                        >

                                        <!-- Preview imágenes -->
                                        <div
                                            v-if="imagenPreview.length > 0"
                                            class="mt-3 d-flex flex-wrap gap-2"
                                        >
                                            <img
                                                v-for="(
                                                    preview, index
                                                ) in imagenPreview"
                                                :key="index"
                                                :src="preview"
                                                alt="Preview"
                                                class="img-thumbnail"
                                                style="
                                                    max-width: 150px;
                                                    height: 150px;
                                                    object-fit: cover;
                                                "
                                            />
                                        </div>
                                    </div>

                                    <!-- Botones -->
                                    <div class="col-md-12 mt-4">
                                        <div
                                            class="d-flex gap-2 justify-content-end"
                                        >
                                            <Link
                                                :href="route('productos.index')"
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
                                                    <i
                                                        class="bi bi-save me-2"
                                                    ></i>
                                                    Guardar Producto
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Crear Categoría -->
        <DialogModal
            :show="showCategoriaModal"
            @close="closeCategoriaModal"
            max-width="sm"
        >
            <template #title>
                <i class="bi bi-folder-plus me-2"></i>
                Crear Categoría Rápida
            </template>

            <template #content>
                <form @submit.prevent="saveCategoria">
                    <div class="mb-3">
                        <label for="categoria_nombre" class="form-label"
                            >Nombre de la Categoría
                            <span class="text-danger">*</span></label
                        >
                        <input
                            v-model="categoriaForm.nombre"
                            type="text"
                            class="form-control"
                            :class="{
                                'is-invalid': categoriaForm.errors.nombre,
                            }"
                            id="categoria_nombre"
                            placeholder="Ej: Electrónica"
                            autofocus
                        />
                        <div
                            v-if="categoriaForm.errors.nombre"
                            class="invalid-feedback"
                        >
                            {{ categoriaForm.errors.nombre }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="categoria_descripcion" class="form-label"
                            >Descripción</label
                        >
                        <textarea
                            v-model="categoriaForm.descripcion"
                            class="form-control"
                            :class="{
                                'is-invalid': categoriaForm.errors.descripcion,
                            }"
                            id="categoria_descripcion"
                            rows="3"
                            placeholder="Breve descripción de la categoría (opcional)"
                        ></textarea>
                        <div
                            v-if="categoriaForm.errors.descripcion"
                            class="invalid-feedback"
                        >
                            {{ categoriaForm.errors.descripcion }}
                        </div>
                    </div>
                </form>
            </template>

            <template #footer>
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="closeCategoriaModal"
                    :disabled="categoriaForm.processing"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="btn btn-primary"
                    @click="saveCategoria"
                    :disabled="categoriaForm.processing"
                >
                    <span v-if="categoriaForm.processing">
                        <i class="bi bi-hourglass-split me-2"></i>
                        Guardando...
                    </span>
                    <span v-else>
                        <i class="bi bi-save me-2"></i>
                        Crear Categoría
                    </span>
                </button>
            </template>
        </DialogModal>
    </AppLayout>
</template>
