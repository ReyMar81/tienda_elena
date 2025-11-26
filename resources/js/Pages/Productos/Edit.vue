<script setup>
import { useForm, Head, Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import DialogModal from "@/Components/DialogModal.vue";
import { ref, nextTick } from "vue";

const props = defineProps({
    producto: Object,
    categorias: Array,
});

const page = usePage();

const form = useForm({
    codigo: props.producto.codigo,
    nombre: props.producto.nombre,
    precio_compra: props.producto.precio_compra,
    precio_venta: props.producto.precio_venta,
    precio_venta_mayorista: props.producto.precio_venta_mayorista,
    stock_actual: props.producto.stock_actual,
    stock_minimo: props.producto.stock_minimo,
    marca: props.producto.marca,
    categoria_id: props.producto.categoria_id,
    estado: props.producto.estado,
    imagen: null,
});

// Imágenes existentes del producto
const imagenesExistentes = ref(props.producto.imagenes || []);

// Previews de nuevas imágenes
const imagenPreview = ref([]);

// Modal de confirmación para eliminar imagen
const showDeleteImageModal = ref(false);
const imageToDelete = ref(null);

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
                only: ["categorias"],
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

// Confirmar eliminación de imagen
const confirmDeleteImage = (imagenId) => {
    imageToDelete.value = imagenId;
    showDeleteImageModal.value = true;
};

// Eliminar imagen existente
const deleteImage = () => {
    if (imageToDelete.value) {
        router.delete(
            route("productos.deleteImage", {
                producto: props.producto.id,
                imagen: imageToDelete.value,
            }),
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Actualizar la lista de imágenes existentes
                    imagenesExistentes.value = imagenesExistentes.value.filter(
                        (img) => img.id !== imageToDelete.value
                    );
                    showDeleteImageModal.value = false;
                    imageToDelete.value = null;
                },
            }
        );
    }
};

// Cancelar eliminación de imagen
const cancelDeleteImage = () => {
    showDeleteImageModal.value = false;
    imageToDelete.value = null;
};

// Submit formulario
const submitForm = () => {
    form.transform((data) => ({
        ...data,
        _method: "PUT",
    })).post(route("productos.update", props.producto.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Editar: ${producto.nombre}`" />

    <AppLayout :title="`Editar: ${producto.nombre}`">
        <FlashNotification />
        <div class="container py-4">
            <!-- Encabezado -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div
                        class="d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <h2 class="mb-0">Editar Producto</h2>
                            <p class="text-muted">
                                Actualiza la información del producto
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
                                        />
                                        <div
                                            v-if="form.errors.nombre"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.nombre }}
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
                                            />
                                        </div>
                                        <div
                                            v-if="form.errors.precio_compra"
                                            class="invalid-feedback d-block"
                                        >
                                            {{ form.errors.precio_compra }}
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
                                            />
                                        </div>
                                        <div
                                            v-if="form.errors.precio_venta"
                                            class="invalid-feedback d-block"
                                        >
                                            {{ form.errors.precio_venta }}
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
                                            />
                                        </div>
                                        <div
                                            v-if="
                                                form.errors
                                                    .precio_venta_mayorista
                                            "
                                            class="invalid-feedback d-block"
                                        >
                                            {{
                                                form.errors
                                                    .precio_venta_mayorista
                                            }}
                                        </div>
                                    </div>

                                    <!-- Stock Actual -->
                                    <div class="col-md-4">
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
                                    <div class="col-md-4">
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
                                            min="0"
                                        />
                                        <div
                                            v-if="form.errors.stock_minimo"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.stock_minimo }}
                                        </div>
                                    </div>

                                    <!-- Marca -->
                                    <div class="col-md-4">
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
                                        />
                                        <div
                                            v-if="form.errors.marca"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.marca }}
                                        </div>
                                    </div>

                                    <!-- Estado -->
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input
                                                v-model="form.estado"
                                                class="form-check-input"
                                                type="checkbox"
                                                id="estado"
                                                :true-value="true"
                                                :false-value="false"
                                            />
                                            <label
                                                class="form-check-label"
                                                for="estado"
                                            >
                                                Producto Activo
                                            </label>
                                        </div>
                                        <div
                                            v-if="form.errors.estado"
                                            class="invalid-feedback d-block"
                                        >
                                            {{ form.errors.estado }}
                                        </div>
                                    </div>

                                    <!-- Imagen -->
                                    <div class="col-md-12">
                                        <label for="imagen" class="form-label"
                                            >Agregar Nuevas Imágenes</label
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
                                            por imagen. Puedes seleccionar
                                            múltiples imágenes</small
                                        >

                                        <!-- Imágenes existentes -->
                                        <div
                                            v-if="imagenesExistentes.length > 0"
                                            class="mt-3"
                                        >
                                            <p class="text-muted mb-2">
                                                <small
                                                    >Imágenes actuales:</small
                                                >
                                            </p>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <div
                                                    v-for="(
                                                        img, index
                                                    ) in imagenesExistentes"
                                                    :key="index"
                                                    class="position-relative"
                                                >
                                                    <img
                                                        :src="`/storage/${img.url}`"
                                                        alt="Imagen actual"
                                                        class="img-thumbnail"
                                                        style="
                                                            max-width: 150px;
                                                            max-height: 150px;
                                                            object-fit: cover;
                                                        "
                                                    />
                                                    <button
                                                        type="button"
                                                        @click="
                                                            confirmDeleteImage(
                                                                img.id
                                                            )
                                                        "
                                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                                                        style="
                                                            padding: 0.25rem
                                                                0.5rem;
                                                            font-size: 0.75rem;
                                                        "
                                                        title="Eliminar imagen"
                                                    >
                                                        <i
                                                            class="bi bi-x-lg"
                                                        ></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Preview de nuevas imágenes -->
                                        <div
                                            v-if="imagenPreview.length > 0"
                                            class="mt-3"
                                        >
                                            <p class="text-muted mb-2">
                                                <small
                                                    >Nuevas imágenes a
                                                    agregar:</small
                                                >
                                            </p>
                                            <div class="d-flex gap-2 flex-wrap">
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
                                                        max-height: 150px;
                                                        object-fit: cover;
                                                    "
                                                />
                                            </div>
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
                                                    Actualizar Producto
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

        <!-- Modal de Confirmación para Eliminar Imagen -->
        <ConfirmationModal
            :show="showDeleteImageModal"
            @close="cancelDeleteImage"
            max-width="sm"
        >
            <template #title> Confirmar Eliminación de Imagen </template>

            <template #content>
                <p class="mb-0">
                    ¿Está seguro de que desea eliminar esta imagen del producto?
                </p>
                <p class="text-muted small mb-0 mt-2">
                    Esta acción no se puede deshacer y el archivo será eliminado
                    permanentemente.
                </p>
            </template>

            <template #footer>
                <button
                    type="button"
                    class="btn btn-secondary"
                    @click="cancelDeleteImage"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="btn btn-danger"
                    @click="deleteImage"
                >
                    <i class="bi bi-trash-fill me-2"></i>
                    Eliminar Imagen
                </button>
            </template>
        </ConfirmationModal>

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
