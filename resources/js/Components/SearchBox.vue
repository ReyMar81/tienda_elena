<template>
    <div class="search-box position-relative" style="min-width: 300px">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search"></i>
            </span>
            <input
                type="text"
                class="form-control border-start-0 ps-0"
                placeholder="Buscar productos, promociones..."
                v-model="searchQuery"
                @input="handleInput"
                @focus="handleFocus"
                @keydown.esc="clearSearch"
            />
            <button
                v-if="searchQuery"
                class="btn btn-link text-muted"
                @click="clearSearch"
                type="button"
            >
                <i class="bi bi-x-circle"></i>
            </button>
        </div>

        <!-- Spinner de carga -->
        <div
            v-if="isSearching"
            class="position-absolute end-0 top-50 translate-middle-y me-5"
        >
            <div
                class="spinner-border spinner-border-sm text-primary"
                role="status"
            >
                <span class="visually-hidden">Buscando...</span>
            </div>
        </div>

        <!-- Resultados -->
        <SearchResults
            v-if="
                showResults &&
                (searchResults.productos.length > 0 ||
                    searchResults.promociones.length > 0)
            "
            :productos="searchResults.productos"
            :promociones="searchResults.promociones"
            @close="closeResults"
        />

        <!-- Sin resultados -->
        <div
            v-else-if="showResults && searchQuery.length >= 2 && !isSearching"
            class="position-absolute top-100 start-0 w-100 mt-2 card shadow-lg"
            style="z-index: 1050"
        >
            <div class="card-body text-center text-muted py-4">
                <i class="bi bi-inbox" style="font-size: 2rem"></i>
                <p class="mb-0 mt-2">No se encontraron resultados</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { watch } from "vue";
import { useSearch } from "@/composables/useSearch";
import SearchResults from "@/Components/SearchResults.vue";

const {
    searchQuery,
    searchResults,
    isSearching,
    showResults,
    search,
    clearSearch,
    closeResults,
} = useSearch();

const handleInput = (e) => {
    search(e.target.value);
};

const handleFocus = () => {
    if (searchQuery.value.length >= 2) {
        search(searchQuery.value);
    }
};

// Cerrar al hacer click fuera
if (typeof window !== "undefined") {
    document.addEventListener("click", (e) => {
        if (!e.target.closest(".search-box")) {
            closeResults();
        }
    });
}
</script>

<style scoped>
.search-box .form-control:focus {
    box-shadow: none;
    border-color: var(--border-color, #dee2e6);
}

.search-box .input-group-text {
    background-color: var(--card-bg, #ffffff);
    border-color: var(--border-color, #dee2e6);
}

.search-box .form-control {
    background-color: var(--card-bg, #ffffff);
    border-color: var(--border-color, #dee2e6);
    color: var(--text-primary, #212121);
}
</style>
