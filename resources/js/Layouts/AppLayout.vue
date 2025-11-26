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
import CartDropdown from "@/Components/Cart/CartDropdown.vue";
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
        route("current-team.update"),
        {
            team_id: team.id,
        },
        {
            preserveState: false,
        }
    );
};

const logout = () => {
    router.post(route("logout"));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-100vh bg-light">
            <nav
                class="navbar navbar-expand-sm navbar-light bg-white border-bottom"
            >
                <div class="container-fluid">
                    <!-- Logo -->
                    <Link :href="route('dashboard')" class="navbar-brand">
                        <ApplicationLogo
                            class="d-inline-block"
                            style="height: 2.25rem"
                        />
                    </Link>

                    <!-- Navigation Links (Desktop) -->
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav me-auto">
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
                        </ul>

                        <!-- Search Box (Desktop) -->
                        <div class="d-none d-md-block mx-3">
                            <SearchBox />
                        </div>

                        <!-- Cart Dropdown (Desktop) -->
                        <div class="d-none d-md-block me-2">
                            <CartDropdown />
                        </div>

                        <!-- Right Side -->
                        <ul class="navbar-nav ms-auto">
                            <!-- Teams Dropdown -->
                            <li
                                class="nav-item"
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
                            <li class="nav-item">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button
                                            v-if="
                                                $page.props.jetstream
                                                    .managesProfilePhotos
                                            "
                                            class="btn btn-link p-0"
                                        >
                                            <img
                                                class="rounded-circle"
                                                style="
                                                    width: 2rem;
                                                    height: 2rem;
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
                                            class="btn btn-link nav-link dropdown-toggle"
                                        >
                                            {{ $page.props.auth.user.name }}
                                        </button>
                                    </template>

                                    <template #content>
                                        <li>
                                            <h6 class="dropdown-header">
                                                Gestionar Cuenta
                                            </h6>
                                        </li>

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

                            <!-- Accesibilidad Dropdown -->
                            <li class="nav-item">
                                <Dropdown align="right" width="60">
                                    <template #trigger>
                                        <button
                                            type="button"
                                            class="btn btn-link nav-link"
                                            title="Accesibilidad"
                                        >
                                            <i
                                                class="bi bi-universal-access"
                                            ></i>
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
                        </ul>
                    </div>

                    <!-- Hamburger (Mobile) -->
                    <button
                        class="navbar-toggler"
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
                    class="d-sm-none"
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
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow-sm">
                <div class="container py-3">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="py-4">
                <slot />
            </main>
        </div>
    </div>
</template>
