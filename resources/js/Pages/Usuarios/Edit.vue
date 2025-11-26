<script setup>
import { ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    usuario: Object,
    roles: Array,
});

const form = useForm({
    nombre: props.usuario.nombre,
    apellidos: props.usuario.apellidos,
    ci: props.usuario.ci,
    telefono: props.usuario.telefono,
    role_id: props.usuario.role_id,
    estado: props.usuario.estado,
    fecha_nacimiento: props.usuario.fecha_nacimiento || "",
});

const submit = () => {
    form.put(route("usuarios.update", props.usuario.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Editar Usuario">
        <Head title="Editar Usuario" />

        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Header -->
                    <div
                        class="d-flex justify-content-between align-items-center mb-4"
                    >
                        <div>
                            <h2 class="mb-0">
                                <i class="bi bi-pencil-square me-2"></i>
                                Editar Usuario
                            </h2>
                            <p class="text-muted">
                                Modifique los datos del usuario
                            </p>
                        </div>
                        <Link
                            :href="route('usuarios.index')"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-arrow-left me-1"></i>
                            Volver
                        </Link>
                    </div>

                    <!-- Formulario -->
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <form @submit.prevent="submit">
                                <!-- Información Personal -->
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-person-vcard me-2"></i>
                                    Información Personal
                                </h5>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="nombre" class="form-label">
                                            Nombre
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="nombre"
                                            v-model="form.nombre"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.nombre,
                                            }"
                                            required
                                        />
                                        <div
                                            v-if="form.errors.nombre"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.nombre }}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label
                                            for="apellidos"
                                            class="form-label"
                                        >
                                            Apellidos
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="apellidos"
                                            v-model="form.apellidos"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.apellidos,
                                            }"
                                            required
                                        />
                                        <div
                                            v-if="form.errors.apellidos"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.apellidos }}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="ci" class="form-label">
                                            CI
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="ci"
                                            v-model="form.ci"
                                            class="form-control"
                                            :class="{
                                                'is-invalid': form.errors.ci,
                                            }"
                                            required
                                        />
                                        <div
                                            v-if="form.errors.ci"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.ci }}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label
                                            for="fecha_nacimiento"
                                            class="form-label"
                                        >
                                            Fecha de Nacimiento
                                        </label>
                                        <input
                                            type="date"
                                            id="fecha_nacimiento"
                                            v-model="form.fecha_nacimiento"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors
                                                        .fecha_nacimiento,
                                            }"
                                        />
                                        <div
                                            v-if="form.errors.fecha_nacimiento"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.fecha_nacimiento }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Información de Contacto -->
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-telephone me-2"></i>
                                    Información de Contacto
                                </h5>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label
                                            for="telefono"
                                            class="form-label"
                                        >
                                            Teléfono
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="tel"
                                            id="telefono"
                                            v-model="form.telefono"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.telefono,
                                            }"
                                            required
                                        />
                                        <div
                                            v-if="form.errors.telefono"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.telefono }}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label">
                                            Email (Username)
                                        </label>
                                        <input
                                            type="email"
                                            id="email"
                                            :value="usuario.email"
                                            class="form-control"
                                            readonly
                                            disabled
                                        />
                                        <small class="text-muted"
                                            >El email no se puede
                                            modificar</small
                                        >
                                    </div>
                                </div>

                                <!-- Rol y Estado -->
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-gear me-2"></i>
                                    Configuración
                                </h5>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="role_id" class="form-label">
                                            Rol
                                            <span class="text-danger">*</span>
                                        </label>
                                        <select
                                            id="role_id"
                                            v-model="form.role_id"
                                            class="form-select"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.role_id,
                                            }"
                                            required
                                        >
                                            <option value="">
                                                Seleccione un rol
                                            </option>
                                            <option
                                                v-for="rol in roles"
                                                :key="rol.id"
                                                :value="rol.id"
                                            >
                                                {{ rol.nombre }}
                                            </option>
                                        </select>
                                        <div
                                            v-if="form.errors.role_id"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.role_id }}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="estado" class="form-label"
                                            >Estado</label
                                        >
                                        <div
                                            class="form-check form-switch mt-2"
                                        >
                                            <input
                                                type="checkbox"
                                                id="estado"
                                                v-model="form.estado"
                                                class="form-check-input"
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
                                    </div>
                                </div>

                                <!-- Botones -->
                                <div
                                    class="d-flex justify-content-end gap-2 mt-4"
                                >
                                    <Link
                                        :href="route('usuarios.index')"
                                        class="btn btn-outline-secondary"
                                    >
                                        <i class="bi bi-x-circle me-1"></i>
                                        Cancelar
                                    </Link>
                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                        :disabled="form.processing"
                                    >
                                        <span v-if="form.processing">
                                            <span
                                                class="spinner-border spinner-border-sm me-1"
                                            ></span>
                                            Guardando...
                                        </span>
                                        <span v-else>
                                            <i
                                                class="bi bi-check-circle me-1"
                                            ></i>
                                            Guardar Cambios
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
