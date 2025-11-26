<script setup>
import { ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    roles: Array,
});

const form = useForm({
    nombre: "",
    apellidos: "",
    ci: "",
    telefono: "",
    email: "",
    password: "",
    password_confirmation: "",
    role_id: "",
    estado: true,
    fecha_nacimiento: "",
});

const submit = () => {
    form.post(route("usuarios.store"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Crear Usuario">
        <Head title="Crear Usuario" />

        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Header -->
                    <div
                        class="d-flex justify-content-between align-items-center mb-4"
                    >
                        <div>
                            <h2 class="mb-0">
                                <i class="bi bi-person-plus me-2"></i>
                                Crear Nuevo Usuario
                            </h2>
                            <p class="text-muted">
                                Complete el formulario para crear un nuevo
                                usuario
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
                                            Email
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="email"
                                            id="email"
                                            v-model="form.email"
                                            class="form-control"
                                            :class="{
                                                'is-invalid': form.errors.email,
                                            }"
                                            required
                                        />
                                        <div
                                            v-if="form.errors.email"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.email }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Credenciales y Rol -->
                                <h5 class="border-bottom pb-2 mb-3">
                                    <i class="bi bi-shield-lock me-2"></i>
                                    Credenciales y Permisos
                                </h5>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label
                                            for="password"
                                            class="form-label"
                                        >
                                            Contraseña
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="password"
                                            id="password"
                                            v-model="form.password"
                                            class="form-control"
                                            :class="{
                                                'is-invalid':
                                                    form.errors.password,
                                            }"
                                            required
                                        />
                                        <div
                                            v-if="form.errors.password"
                                            class="invalid-feedback"
                                        >
                                            {{ form.errors.password }}
                                        </div>
                                        <small class="text-muted"
                                            >Mínimo 8 caracteres</small
                                        >
                                    </div>

                                    <div class="col-md-6">
                                        <label
                                            for="password_confirmation"
                                            class="form-label"
                                        >
                                            Confirmar Contraseña
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="password"
                                            id="password_confirmation"
                                            v-model="form.password_confirmation"
                                            class="form-control"
                                            required
                                        />
                                    </div>

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
                                            Creando...
                                        </span>
                                        <span v-else>
                                            <i
                                                class="bi bi-check-circle me-1"
                                            ></i>
                                            Crear Usuario
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
