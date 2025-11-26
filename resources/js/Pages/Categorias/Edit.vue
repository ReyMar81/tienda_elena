<script setup>
import { useForm, Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import FlashNotification from "@/Components/FlashNotification.vue";

const props = defineProps({
    categoria: Object,
});

const form = useForm({
    nombre: props.categoria.nombre,
    descripcion: props.categoria.descripcion,
});

const submitForm = () => {
    form.put(route("categorias.update", props.categoria.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Editar: ${categoria.nombre}`" />

    <AppLayout :title="`Editar: ${categoria.nombre}`">
        <FlashNotification />

        <div class="container py-4">
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div
                        class="d-flex justify-content-between align-items-center"
                    >
                        <div>
                            <h2 class="mb-0">Editar Categoría</h2>
                            <p class="text-muted">
                                Actualiza la información de la categoría
                            </p>
                        </div>
                        <Link
                            :href="route('categorias.index')"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-arrow-left me-2"></i>
                            Volver
                        </Link>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <form @submit.prevent="submitForm">
                                <div class="mb-3">
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
                                            'is-invalid': form.errors.nombre,
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

                                <div class="mb-4">
                                    <label for="descripcion" class="form-label"
                                        >Descripción</label
                                    >
                                    <textarea
                                        v-model="form.descripcion"
                                        class="form-control"
                                        :class="{
                                            'is-invalid':
                                                form.errors.descripcion,
                                        }"
                                        id="descripcion"
                                        rows="3"
                                    ></textarea>
                                    <div
                                        v-if="form.errors.descripcion"
                                        class="invalid-feedback"
                                    >
                                        {{ form.errors.descripcion }}
                                    </div>
                                </div>

                                <div class="d-flex gap-2 justify-content-end">
                                    <Link
                                        :href="route('categorias.index')"
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
                                            Actualizar Categoría
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
