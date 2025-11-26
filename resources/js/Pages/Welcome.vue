<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="Bienvenido - Tienda Elena" />

    <div class="min-vh-100 bg-light d-flex flex-column">
        <!-- Header con botones de autenticación -->
        <nav v-if="canLogin" class="navbar navbar-light bg-white border-bottom">
            <div class="container-fluid justify-content-end">
                <div class="d-flex gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="btn btn-outline-primary"
                    >
                        Dashboard
                    </Link>

                    <template v-else>
                        <Link :href="route('login')" class="btn btn-primary">
                            Iniciar Sesión
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="btn btn-outline-primary"
                        >
                            Registrarse
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Contenido principal -->
        <div class="container py-5 flex-grow-1">
            <div class="text-center mb-5">
                <h1 class="display-3 fw-bold text-primary">Tienda Elena</h1>
                <p class="lead text-muted">
                    Sistema de Gestión de Ventas y Créditos
                </p>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="48"
                                    height="48"
                                    fill="currentColor"
                                    class="bi bi-cart text-primary"
                                    viewBox="0 0 16 16"
                                >
                                    <path
                                        d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h8.17l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
                                    />
                                </svg>
                            </div>
                            <h5 class="card-title">Ventas</h5>
                            <p class="card-text small text-muted">
                                Gestión completa de ventas mayoristas y
                                minoristas
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="48"
                                    height="48"
                                    fill="currentColor"
                                    class="bi bi-credit-card text-success"
                                    viewBox="0 0 16 16"
                                >
                                    <path
                                        d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"
                                    />
                                    <path
                                        d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"
                                    />
                                </svg>
                            </div>
                            <h5 class="card-title">Créditos</h5>
                            <p class="card-text small text-muted">
                                Control de créditos, cuotas y pagos con cálculo
                                de mora
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="48"
                                    height="48"
                                    fill="currentColor"
                                    class="bi bi-box-seam text-warning"
                                    viewBox="0 0 16 16"
                                >
                                    <path
                                        d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"
                                    />
                                </svg>
                            </div>
                            <h5 class="card-title">Productos</h5>
                            <p class="card-text small text-muted">
                                Catálogo de productos con categorías y
                                promociones
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="48"
                                    height="48"
                                    fill="currentColor"
                                    class="bi bi-graph-up text-info"
                                    viewBox="0 0 16 16"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M0 0h1v15h15v1H0V0zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07z"
                                    />
                                </svg>
                            </div>
                            <h5 class="card-title">Reportes</h5>
                            <p class="card-text small text-muted">
                                Análisis y reportes detallados de ventas y
                                créditos
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-body p-5 text-center">
                            <h3 class="mb-4">Sistema de Gestión Integral</h3>
                            <p class="text-muted mb-4">
                                Tienda Elena es un sistema completo para la
                                gestión de ventas de ropa con soporte para:
                            </p>
                            <ul class="list-unstyled text-start">
                                <li class="mb-2">
                                    <span class="text-success">✓</span> Control
                                    de inventario y productos
                                </li>
                                <li class="mb-2">
                                    <span class="text-success">✓</span> Ventas
                                    mayoristas y minoristas
                                </li>
                                <li class="mb-2">
                                    <span class="text-success">✓</span> Sistema
                                    de créditos con cuotas
                                </li>
                                <li class="mb-2">
                                    <span class="text-success">✓</span> Cálculo
                                    automático de mora e intereses
                                </li>
                                <li class="mb-2">
                                    <span class="text-success">✓</span>
                                    Promociones por categoría o producto
                                </li>
                                <li class="mb-2">
                                    <span class="text-success">✓</span> Reportes
                                    y análisis de ventas
                                </li>
                                <li class="mb-2">
                                    <span class="text-success">✓</span> Gestión
                                    de roles y permisos
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white border-top py-3">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <small class="text-muted"
                            >&copy; 2025 Tienda Elena. Todos los derechos
                            reservados.</small
                        >
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <small class="text-muted"
                            >Laravel v{{ laravelVersion }} (PHP v{{
                                phpVersion
                            }})</small
                        >
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
