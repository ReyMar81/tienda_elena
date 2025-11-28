<script setup>
import { ref, computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import Banner from "@/Components/Banner.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import NavDropdown from "@/Components/Navigation/NavDropdown.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import SearchBox from "@/Components/SearchBox.vue";
import { useTheme } from "@/composables/useTheme";

defineProps({
    title: String,
});

import { usePage } from "@inertiajs/vue3";

const showingNavigationDropdown = ref(false);
const mobileSubmenuOpen = ref(null);
const page = usePage();

// Menú dinámico desde props compartidos globalmente
const menuItems = computed(() => page.props.menuItems || []);

const toggleMobileSubmenu = (itemId) => {
    mobileSubmenuOpen.value =
        mobileSubmenuOpen.value === itemId ? null : itemId;
};

const {
    theme,
    mode,
    fontSize,
    contrast,
    setTheme,
    setMode,
    setFontSize,
    setContrast,
} = useTheme();

const switchToTeam = (team) => {
    router.put(
        router("current-team.update"),
        {
            team_id: team.id,
        },
        {
            preserveState: false,
        }
    );
};

const logout = () => {
    router.post(router("logout"));
};
</script>

<template>
    <div class="min-vh-100 d-flex flex-column">
        <Head :title="title" />

        <Banner />

        <div class="d-flex flex-column flex-grow-1 bg-light">
            <nav
                class="navbar navbar-expand-sm navbar-light bg-white border-bottom shadow-sm flex-shrink-0 position-relative"
            >
                <div class="container-fluid px-3 px-md-4 position-static">
                    <!-- Logo -->
                    <Link :href="route('dashboard')" class="navbar-brand me-3 me-md-4">
                        <ApplicationLogo
                            class="d-inline-block"
                            style="height: 2.5rem"
                        />
                    </Link>

                    <!-- Navigation Links (Desktop) -->
                    <div class="collapse navbar-collapse position-static" id="navbarNav">
                        <ul class="navbar-nav flex-wrap align-items-center w-100 position-static">
                            <template v-for="item in menuItems" :key="item.id">
                                <!-- Menú con submenús (dropdown) -->
                                <NavDropdown
                                    v-if="
                                        item.children &&
                                        item.children.length > 0
                                    "
                                    :item="item"
                                />

                                <!-- Menú simple (sin submenús) -->
                                <li v-else class="nav-item">
                                    <NavLink
                                        :href="route(item.route)"
                                        :active="route().current(item.route)"
                                    >
                                        <i
                                            :class="['bi', item.icon, 'me-1']"
                                        ></i>
                                        {{ item.label }}
                                    </NavLink>
                                </li>
                            </template>

                            <!-- Espaciador para empujar elementos a la derecha -->
                            <li class="nav-item ms-auto d-none d-sm-block"></li>

                            <!-- Search Box (Desktop) -->
                            <li class="nav-item d-none d-lg-flex align-items-center me-2 flex-shrink-0" style="flex: 0 1 auto; max-width: 400px;">
                                <SearchBox />
                            </li>

                            <!-- Teams Dropdown -->
                            <li
                                class="nav-item flex-shrink-0 position-static"
                                v-if="$page.props.jetstream.hasTeamFeatures"
                            >
                                <Dropdown align="right" width="60">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="btn btn-link nav-link dropdown-toggle"
                                        >
                                            {{
                                                $page.props.auth.user
                                                    .current_team.name
                                            }}
                                        </button>
                                    </template>

                                    <template #content>
                                        <li>
                                            <h6 class="dropdown-header">
                                                Gestionar Equipo
                                            </h6>
                                        </li>

                                        <DropdownLink
                                            :href="
                                                route(
                                                    'teams.show',
                                                    $page.props.auth.user
                                                        .current_team
                                                )
                                            "
                                        >
                                            Configuración del Equipo
                                        </DropdownLink>

                                        <DropdownLink
                                            v-if="
                                                $page.props.jetstream
                                                    .canCreateTeams
                                            "
                                            :href="route('teams.create')"
                                        >
                                            Crear Nuevo Equipo
                                        </DropdownLink>

                                        <template
                                            v-if="
                                                $page.props.auth.user.all_teams
                                                    .length > 1
                                            "
                                        >
                                            <li>
                                                <hr class="dropdown-divider" />
                                            </li>
                                            <li>
                                                <h6 class="dropdown-header">
                                                    Cambiar de Equipo
                                                </h6>
                                            </li>

                                            <template
                                                v-for="team in $page.props.auth
                                                    .user.all_teams"
                                                :key="team.id"
                                            >
                                                <li>
                                                    <form
                                                        @submit.prevent="
                                                            switchToTeam(team)
                                                        "
                                                    >
                                                        <DropdownLink
                                                            as="button"
                                                        >
                                                            <span
                                                                v-if="
                                                                    team.id ==
                                                                    $page.props
                                                                        .auth
                                                                        .user
                                                                        .current_team_id
                                                                "
                                                                >✓
                                                            </span>
                                                            {{ team.name }}
                                                        </DropdownLink>
                                                    </form>
                                                </li>
                                            </template>
                                        </template>
                                    </template>
                                </Dropdown>
                            </li>

                            <!-- Settings Dropdown -->
                            <li class="nav-item flex-shrink-0 position-static">
                                <Dropdown align="right" width="60">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="btn btn-link nav-link d-flex align-items-center"
                                            title="Accesibilidad"
                                        >
                                            <i class="bi bi-sliders fs-5"></i>
                                        </button>
                                    </template>

                                    <template #content>
                                        <li>
                                            <h6 class="dropdown-header">
                                                Tema Visual
                                            </h6>
                                        </li>
                                        <li>
                                            <button
                                                class="dropdown-item"
                                                :class="{
                                                    active: theme === 'ninos',
                                                }"
                                                @click="setTheme('ninos')"
                                            >
                                                🎨 Niños
                                            </button>
                                        </li>
                                        <li>
                                            <button
                                                class="dropdown-item"
                                                :class="{
                                                    active: theme === 'jovenes',
                                                }"
                                                @click="setTheme('jovenes')"
                                            >
                                                🚀 Jóvenes
                                            </button>
                                        </li>
                                        <li>
                                            <button
                                                class="dropdown-item"
                                                :class="{
                                                    active: theme === 'adultos',
                                                }"
                                                @click="setTheme('adultos')"
                                            >
                                                💼 Adultos
                                            </button>
                                        </li>

                                        <li><hr class="dropdown-divider" /></li>
                                        <li>
                                            <h6 class="dropdown-header">
                                                Modo Día/Noche
                                            </h6>
                                        </li>
                                        <li>
                                            <button
                                                class="dropdown-item"
                                                :class="{
                                                    active: mode === 'auto',
                                                }"
                                                @click="setMode('auto')"
                                            >
                                                🔄 Automático
                                            </button>
                                        </li>
                                        <li>
                                            <button
                                                class="dropdown-item"
                                                :class="{
                                                    active: mode === 'dia',
                                                }"
                                                @click="setMode('dia')"
                                            >
                                                ☀️ Día
                                            </button>
                                        </li>
                                        <li>
                                            <button
                                                class="dropdown-item"
                                                :class="{
                                                    active: mode === 'noche',
                                                }"
                                                @click="setMode('noche')"
                                            >
                                                🌙 Noche
                                            </button>
                                        </li>

                                        <li><hr class="dropdown-divider" /></li>
                                        <li>
                                            <h6 class="dropdown-header">
                                                Tamaño de Fuente
                                            </h6>
                                        </li>
                                        <li>
                                            <button
                                                class="dropdown-item"
                                                :class="{
                                                    active:
                                                        fontSize === 'small',
                                                }"
                                                @click="setFontSize('small')"
                                            >
                                                A- Pequeña
                                            </button>
                                        </li>
                                        <li>
                                            <button
                                                class="dropdown-item"
                                                :class="{
                                                    active:
                                                        fontSize === 'normal',
                                                }"
                                                @click="setFontSize('normal')"
                                            >
                                                A Normal
                                            </button>
                                        </li>
                                        <li>
                                            <button
                                                class="dropdown-item"
                                                :class="{
                                                    active:
                                                        fontSize === 'large',
                                                }"
                                                @click="setFontSize('large')"
                                            >
                                                A+ Grande
                                            </button>
                                        </li>

                                        <li><hr class="dropdown-divider" /></li>
                                        <li>
                                            <h6 class="dropdown-header">
                                                Contraste
                                            </h6>
                                        </li>
                                        <li>
                                            <button
                                                class="dropdown-item"
                                                :class="{
                                                    active:
                                                        contrast === 'normal',
                                                }"
                                                @click="setContrast('normal')"
                                            >
                                                Normal
                                            </button>
                                        </li>
                                        <li>
                                            <button
                                                class="dropdown-item"
                                                :class="{
                                                    active: contrast === 'high',
                                                }"
                                                @click="setContrast('high')"
                                            >
                                                Alto Contraste
                                            </button>
                                        </li>
                                    </template>
                                </Dropdown>
                            </li>

                            <!-- Accesibilidad Dropdown -->
                            <li class="nav-item flex-shrink-0 position-static">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button
                                            v-if="
                                                $page.props.jetstream
                                                    .managesProfilePhotos
                                            "
                                            class="btn btn-link p-0 d-flex align-items-center"
                                        >
                                            <img
                                                class="rounded-circle border border-2"
                                                style="
                                                    width: 2.5rem;
                                                    height: 2.5rem;
                                                    object-fit: cover;
                                                "
                                                :src="
                                                    $page.props.auth.user
                                                        .profile_photo_url
                                                "
                                                :alt="
                                                    $page.props.auth.user.name
                                                "
                                            />
                                        </button>

                                        <button
                                            v-else
                                            type="button"
                                            class="btn btn-link nav-link dropdown-toggle d-flex align-items-center"
                                        >
                                            <i class="bi bi-person-circle me-1 fs-5"></i>
                                            {{ $page.props.auth.user.name }}
                                        </button>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.show')"
                                        >
                                            Perfil
                                        </DropdownLink>

                                        <DropdownLink
                                            v-if="
                                                $page.props.jetstream
                                                    .hasApiFeatures
                                            "
                                            :href="route('api-tokens.index')"
                                        >
                                            Tokens API
                                        </DropdownLink>

                                        <li><hr class="dropdown-divider" /></li>

                                        <li>
                                            <form @submit.prevent="logout">
                                                <DropdownLink as="button">
                                                    Cerrar Sesión
                                                </DropdownLink>
                                            </form>
                                        </li>
                                    </template>
                                </Dropdown>
                            </li>


                        </ul>
                    </div>

                    <!-- Hamburger (Mobile) -->
                    <button
                        class="navbar-toggler ms-2 flex-shrink-0"
                        type="button"
                        @click="
                            showingNavigationDropdown =
                                !showingNavigationDropdown
                        "
                    >
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        'd-block': showingNavigationDropdown,
                        'd-none': !showingNavigationDropdown,
                    }"
                    class="d-sm-none border-top"
                >
                    <div class="mt-2">
                        <template v-for="item in menuItems" :key="item.id">
                            <!-- Item con submenús -->
                            <div
                                v-if="item.children && item.children.length > 0"
                                class="accordion-item border-0"
                            >
                                <div class="accordion-header">
                                    <button
                                        class="accordion-button collapsed bg-transparent text-dark px-3"
                                        type="button"
                                        :data-bs-toggle="'collapse' + item.id"
                                        @click="toggleMobileSubmenu(item.id)"
                                    >
                                        <i
                                            :class="['bi', item.icon, 'me-2']"
                                        ></i>
                                        {{ item.label }}
                                    </button>
                                </div>
                                <div
                                    :id="'collapse' + item.id"
                                    class="accordion-collapse collapse"
                                    :class="{
                                        show: mobileSubmenuOpen === item.id,
                                    }"
                                >
                                    <div class="accordion-body p-0">
                                        <ResponsiveNavLink
                                            v-for="child in item.children"
                                            :key="child.id"
                                            :href="route(child.route)"
                                            :active="
                                                route().current(child.route)
                                            "
                                            class="ps-5"
                                        >
                                            <i
                                                :class="[
                                                    'bi',
                                                    child.icon,
                                                    'me-2',
                                                ]"
                                            ></i>
                                            {{ child.label }}
                                        </ResponsiveNavLink>
                                    </div>
                                </div>
                            </div>

                            <!-- Item sin submenús -->
                            <ResponsiveNavLink
                                v-else
                                :href="route(item.route)"
                                :active="route().current(item.route)"
                            >
                                <i :class="['bi', item.icon, 'me-2']"></i>
                                {{ item.label }}
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-top">
                        <div class="d-flex align-items-center px-3">
                            <div
                                v-if="
                                    $page.props.jetstream.managesProfilePhotos
                                "
                                class="me-3"
                            >
                                <img
                                    class="rounded-circle"
                                    style="
                                        width: 2.5rem;
                                        height: 2.5rem;
                                        object-fit: cover;
                                    "
                                    :src="
                                        $page.props.auth.user.profile_photo_url
                                    "
                                    :alt="$page.props.auth.user.name"
                                />
                            </div>

                            <div>
                                <div class="fw-medium">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="small text-muted">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-2">
                            <ResponsiveNavLink
                                :href="route('profile.show')"
                                :active="route().current('profile.show')"
                            >
                                Perfil
                            </ResponsiveNavLink>

                            <ResponsiveNavLink
                                v-if="$page.props.jetstream.hasApiFeatures"
                                :href="route('api-tokens.index')"
                                :active="route().current('api-tokens.index')"
                            >
                                Tokens API
                            </ResponsiveNavLink>

                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Cerrar Sesión
                                </ResponsiveNavLink>
                            </form>

                            <!-- Team Management (Mobile) -->
                            <template
                                v-if="$page.props.jetstream.hasTeamFeatures"
                            >
                                <div class="border-top mt-2"></div>

                                <div class="px-3 py-2 small text-muted">
                                    Gestionar Equipo
                                </div>

                                <ResponsiveNavLink
                                    :href="
                                        route(
                                            'teams.show',
                                            $page.props.auth.user.current_team
                                        )
                                    "
                                    :active="route().current('teams.show')"
                                >
                                    Configuración del Equipo
                                </ResponsiveNavLink>

                                <ResponsiveNavLink
                                    v-if="$page.props.jetstream.canCreateTeams"
                                    :href="route('teams.create')"
                                    :active="route().current('teams.create')"
                                >
                                    Crear Nuevo Equipo
                                </ResponsiveNavLink>

                                <template
                                    v-if="
                                        $page.props.auth.user.all_teams.length >
                                        1
                                    "
                                >
                                    <div class="border-top mt-2"></div>

                                    <div class="px-3 py-2 small text-muted">
                                        Cambiar de Equipo
                                    </div>

                                    <template
                                        v-for="team in $page.props.auth.user
                                            .all_teams"
                                        :key="team.id"
                                    >
                                        <form
                                            @submit.prevent="switchToTeam(team)"
                                        >
                                            <ResponsiveNavLink as="button">
                                                <span
                                                    v-if="
                                                        team.id ==
                                                        $page.props.auth.user
                                                            .current_team_id
                                                    "
                                                    >✓
                                                </span>
                                                {{ team.name }}
                                            </ResponsiveNavLink>
                                        </form>
                                    </template>
                                </template>
                            </template>
                        </div>

                        <!-- Opciones de Accesibilidad (Móvil) -->
                        <div class="border-top mt-2"></div>
                        <div class="px-3 py-2 small text-muted">
                            Accesibilidad
                        </div>

                        <div class="px-3 pb-3">
                            <!-- Tema -->
                            <div class="mb-2">
                                <small class="d-block text-muted mb-1">Tema Visual</small>
                                <div class="btn-group w-100 btn-group-sm">
                                    <button class="btn btn-outline-secondary" :class="{ active: theme === 'ninos' }" @click="setTheme('ninos')">🎨</button>
                                    <button class="btn btn-outline-secondary" :class="{ active: theme === 'jovenes' }" @click="setTheme('jovenes')">🚀</button>
                                    <button class="btn btn-outline-secondary" :class="{ active: theme === 'adultos' }" @click="setTheme('adultos')">💼</button>
                                </div>
                            </div>

                            <!-- Modo -->
                            <div class="mb-2">
                                <small class="d-block text-muted mb-1">Modo</small>
                                <div class="btn-group w-100 btn-group-sm">
                                    <button class="btn btn-outline-secondary" :class="{ active: mode === 'auto' }" @click="setMode('auto')">🔄</button>
                                    <button class="btn btn-outline-secondary" :class="{ active: mode === 'dia' }" @click="setMode('dia')">☀️</button>
                                    <button class="btn btn-outline-secondary" :class="{ active: mode === 'noche' }" @click="setMode('noche')">🌙</button>
                                </div>
                            </div>

                            <!-- Fuente -->
                            <div>
                                <small class="d-block text-muted mb-1">Fuente</small>
                                <div class="btn-group w-100 btn-group-sm">
                                    <button class="btn btn-outline-secondary" :class="{ active: fontSize === 'small' }" @click="setFontSize('small')">A-</button>
                                    <button class="btn btn-outline-secondary" :class="{ active: fontSize === 'normal' }" @click="setFontSize('normal')">A</button>
                                    <button class="btn btn-outline-secondary" :class="{ active: fontSize === 'large' }" @click="setFontSize('large')">A+</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow-sm border-bottom flex-shrink-0">
                <div class="container-fluid px-3 px-md-4 py-3">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-grow-1 py-3 py-md-4 overflow-auto">
                <slot />
            </main>

            <!-- Footer con contador de visitas -->
            <footer class="mt-auto py-3 py-md-4 bg-white border-top shadow-sm flex-shrink-0">
                <div class="container-fluid px-3 px-md-4">
                    <div class="row align-items-center g-2">
                        <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                            <small class="text-muted">
                                <i class="bi bi-eye me-1"></i>
                                Visitas a esta página: 
                                <strong>{{ $page.props.pageVisits?.current?.toLocaleString() || 0 }}</strong>
                            </small>
                        </div>
                        <div class="col-md-6 text-center text-md-end">
                            <small class="text-muted">
                                <i class="bi bi-globe me-1"></i>
                                Visitas totales al sitio: 
                                <strong>{{ $page.props.pageVisits?.total?.toLocaleString() || 0 }}</strong>
                            </small>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
/* Asegurar que el layout no cause overflow horizontal */
.navbar {
    max-width: 100%;
    overflow: visible !important;
}

.navbar-collapse {
    max-width: 100%;
    overflow: visible !important;
}

.navbar-nav {
    max-width: 100%;
    overflow: visible !important;
}

/* Los dropdowns deben salir del contenedor del navbar */
:deep(.dropdown) {
    position: static !important;
}

:deep(.dropdown-menu) {
    position: absolute !important;
    z-index: 1050;
    max-width: 90vw;
    margin-top: 0.5rem;
}

/* Mejorar responsividad del menú móvil */
@media (max-width: 575.98px) {
    .container-fluid {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }
}

/* Prevenir desbordamiento en pantallas pequeñas */
.nav-item {
    white-space: nowrap;
}

/* Asegurar que los dropdowns no causen overflow */
.dropdown-menu {
    max-width: 90vw;
}

/* Ajustar el contenedor principal */
.min-vh-100 {
    min-height: 100vh;
    max-width: 100vw;
    overflow-x: hidden;
}

/* Mejorar el comportamiento del main */
main {
    max-width: 100%;
    overflow-x: hidden;
}

/* Espaciado proporcional según el boceto */
.navbar-brand {
    min-width: 150px;
}

/* Logo debe tener espacio fijo */
@media (min-width: 576px) {
    .navbar-brand {
        width: 200px;
        flex-shrink: 0;
    }
}

/* El área de menús debe tener espacio flexible */
.navbar-collapse {
    flex-grow: 1;
}

/* Elementos de la derecha (perfil, accesibilidad) con espacio fijo */
.nav-item.position-static {
    flex-shrink: 0;
}

/* El buscador debe tener ancho máximo */
:deep(.search-box) {
    max-width: 400px;
}

@media (min-width: 992px) {
    :deep(.search-box) {
        min-width: 250px;
    }
}
</style>
